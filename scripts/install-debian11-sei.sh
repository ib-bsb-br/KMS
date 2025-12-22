#!/usr/bin/env bash
set -Eeuo pipefail
trap 'rc=$?; echo "ERROR (rc=$rc) at line $LINENO: $BASH_COMMAND" >&2; exit $rc' ERR

# -----------------------------
# /opt-first prefix layout
# -----------------------------
PREFIX="${SEI_PREFIX:-/opt/sei-stack}"

APP_DIR="${SEI_BASE_DIR:-${PREFIX}/app}"
DATA_DIR="${SEI_DATA_DIR:-${PREFIX}/data}"
OPT_VAR="${SEI_OPT_VAR:-${PREFIX}/var}"
SECRETS_DIR="${SEI_SECRETS_DIR:-${PREFIX}/secrets}"
SEI_SECRETS_FILE="${SEI_SECRETS_FILE:-${SECRETS_DIR}/sei-install.env}"
TMPROOT="${SEI_TMPROOT:-${PREFIX}/tmp}"
COMPOSER_HOME_DIR="${SEI_COMPOSER_HOME:-${PREFIX}/composer-cache}"

# Relocation toggle (enabled by default for low-space root)
RELOCATE_VAR="${SEI_RELOCATE_VAR:-1}"

# Optional Solr (strictly opt-in; no invented version)
INSTALL_SOLR="${INSTALL_SOLR:-0}"
SOLR_TGZ_URL="${SOLR_TGZ_URL:-}"
SOLR_SHA512="${SOLR_SHA512:-}"
SOLR_INSTALL_DIR="${SOLR_INSTALL_DIR:-${PREFIX}/solr}"
SOLR_DATA_DIR="${SOLR_DATA_DIR:-${PREFIX}/solr-data}"
SOLR_PORT="${SOLR_PORT:-8983}"

DB_SEI="sei"
DB_SIP="sip"

PHP_APACHE_VERSION="5.6"
PHP_COMPOSER_VERSION="7.4"

PHP_INI="/etc/php/${PHP_APACHE_VERSION}/apache2/php.ini"
MEMCACHED_CONF="/etc/memcached.conf"
SITE_CONF="/etc/apache2/sites-available/sei.conf"
SURY_KEYRING="/usr/share/keyrings/sury-php.gpg"
SURY_LIST="/etc/apt/sources.list.d/sury-php.list"
SURY_EXPECTED_FPR="15058500A0235D97F5D10063B188E2B695BD4743"
APACHE_LOG_DIR="${APACHE_LOG_DIR:-/var/log/apache2}"

SRC_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SQL_DIR="${SRC_ROOT}/mysql"

log() { echo "==> $*"; }
die() { echo "ERROR: $*" >&2; exit 1; }
require_root() { [[ ${EUID:-0} -eq 0 ]] || die "Run as root."; }

detect_arch() {
  local arch
  arch="$(dpkg --print-architecture 2>/dev/null || true)"
  if [[ -n "$arch" && "$arch" != "arm64" && "$arch" != "amd64" ]]; then
    echo "WARN: architecture=$arch (continuing)." >&2
  fi
}

ensure_exec_under_opt() {
  local tdir="${TMPROOT}/exec-test"
  mkdir -p "$tdir"
  local f="${tdir}/t.sh"
  printf '#!/usr/bin/env bash\necho ok\n' >"$f"
  chmod +x "$f"
  if ! "$f" >/dev/null 2>&1; then
    die "Execution under ${TMPROOT} failed. Check mount options (e.g., /opt noexec) or set SEI_TMPROOT to an exec-capable path."
  fi
}

# -----------------------------
# APT/DPKG lock handling
# -----------------------------
wait_for_apt_locks() {
  local tries=60
  local lock1="/var/lib/dpkg/lock-frontend"
  local lock2="/var/lib/dpkg/lock"
  local lock3="/var/lib/apt/lists/lock"
  local lock4="/var/cache/apt/archives/lock"

  log "Waiting for apt/dpkg locks (if any)..."
  while (( tries > 0 )); do
    if flock -n "$lock1" true 2>/dev/null && flock -n "$lock2" true 2>/dev/null \
       && flock -n "$lock3" true 2>/dev/null && flock -n "$lock4" true 2>/dev/null; then
      return 0
    fi
    sleep 2
    tries=$((tries - 1))
  done
  die "Timed out waiting for apt/dpkg locks. Stop unattended upgrades or any running apt process and retry."
}

# -----------------------------
# Secrets handling (persisted under /opt prefix)
# -----------------------------
umask 077
load_secrets_if_present() { [[ -f "$SEI_SECRETS_FILE" ]] && source "$SEI_SECRETS_FILE"; }

persist_kv() {
  local k="$1" v="$2"
  mkdir -p "$SECRETS_DIR"
  chmod 700 "$SECRETS_DIR"
  install -m 600 /dev/null "$SEI_SECRETS_FILE" 2>/dev/null || true
  if ! grep -q "^${k}=" "$SEI_SECRETS_FILE"; then
    printf '%s=%q\n' "$k" "$v" >>"$SEI_SECRETS_FILE"
  fi
}

gen_password() {
  if command -v openssl >/dev/null 2>&1; then
    openssl rand -base64 24
  else
    tr -dc 'A-Za-z0-9' </dev/urandom | head -c 32
  fi
}

init_secrets() {
  load_secrets_if_present
  SEI_DB_APP_USER="${SEI_DB_APP_USER:-sei_app}"
  SEI_DB_APP_PASS="${SEI_DB_APP_PASS:-}"
  SEI_DB_ROOT_PASSWORD="${SEI_DB_ROOT_PASSWORD:-}"

  [[ -n "$SEI_DB_APP_PASS" ]] || SEI_DB_APP_PASS="$(gen_password)"
  [[ -n "$SEI_DB_ROOT_PASSWORD" ]] || SEI_DB_ROOT_PASSWORD="$(gen_password)"

  persist_kv "SEI_DB_APP_USER" "$SEI_DB_APP_USER"
  persist_kv "SEI_DB_APP_PASS" "$SEI_DB_APP_PASS"
  persist_kv "SEI_DB_ROOT_PASSWORD" "$SEI_DB_ROOT_PASSWORD"
  persist_kv "SEI_PREFIX" "$PREFIX"
}

# -----------------------------
# Robust relocation helper: move /var-heavy dirs -> ${OPT_VAR} and symlink back
# Ensures destination exists and creates required apt subdirs.
# -----------------------------
relocate_dir_to_optvar() {
  local src="$1" dst="$2" owner="${3:-root:root}" mode="${4:-755}"

  mkdir -p "$dst"
  chmod "$mode" "$dst" || true
  chown -R "$owner" "$dst" || true

  if [[ -L "$src" ]]; then
    return 0
  fi

  if [[ -e "$src" && ! -d "$src" ]]; then
    die "Cannot relocate $src: exists but is not a directory"
  fi

  if [[ -d "$src" ]]; then
    rsync -a "$src/" "$dst/"
  fi

  rm -rf "$src"
  ln -s "$dst" "$src"
}

prepare_apt_dirs() {
  mkdir -p /var/cache/apt/archives/partial /var/lib/apt/lists/partial
  chmod 755 /var/cache/apt/archives /var/lib/apt/lists || true
}

relocate_var_heavy_dirs() {
  [[ "$RELOCATE_VAR" == "1" ]] || { log "Skipping /var relocation (SEI_RELOCATE_VAR!=1)"; return 0; }

  wait_for_apt_locks
  log "Relocating apt caches/lists under ${OPT_VAR} (symlink-back)"
  relocate_dir_to_optvar "/var/cache/apt/archives" "${OPT_VAR}/cache/apt/archives" "root:root" "755"
  relocate_dir_to_optvar "/var/lib/apt/lists"       "${OPT_VAR}/lib/apt/lists"       "root:root" "755"
  prepare_apt_dirs

  if systemctl list-unit-files | grep -q '^apache2\.service'; then
    systemctl stop apache2 >/dev/null 2>&1 || true
  fi
  relocate_dir_to_optvar "/var/log/apache2" "${OPT_VAR}/log/apache2" "root:adm" "750"
  if systemctl list-unit-files | grep -q '^apache2\.service'; then
    systemctl start apache2 >/dev/null 2>&1 || true
  fi
}

allow_mariadb_apparmor_path() {
  local local_file="/etc/apparmor.d/local/usr.sbin.mysqld"
  mkdir -p "$(dirname "$local_file")"
  touch "$local_file"
  if ! grep -qF "${OPT_VAR}/lib/mysql/" "$local_file"; then
    {
      echo ""
      echo "# Allow MariaDB datadir under /opt prefix"
      echo "${OPT_VAR}/lib/mysql/ r,"
      echo "${OPT_VAR}/lib/mysql/** rwk,"
    } >>"$local_file"
  fi
  if command -v apparmor_parser >/dev/null 2>&1 && [[ -f /etc/apparmor.d/usr.sbin.mysqld ]]; then
    apparmor_parser -r /etc/apparmor.d/usr.sbin.mysqld || true
  fi
}

relocate_mariadb_datadir_to_opt() {
  [[ "$RELOCATE_VAR" == "1" ]] || return 0
  if ! systemctl list-unit-files | grep -q '^mariadb\.service'; then
    return 0
  fi

  log "Relocating MariaDB datadir under ${OPT_VAR}"
  systemctl stop mariadb >/dev/null 2>&1 || true
  relocate_dir_to_optvar "/var/lib/mysql" "${OPT_VAR}/lib/mysql" "mysql:mysql" "750"
  allow_mariadb_apparmor_path
  systemctl start mariadb
}

# -----------------------------
# Sury repo (fingerprint enforced)
# -----------------------------
add_sury_repo() {
  log "Adding Sury PHP repo (fingerprint enforced)"
  wait_for_apt_locks
  apt-get update -y
  DEBIAN_FRONTEND=noninteractive apt-get install -y ca-certificates apt-transport-https lsb-release wget gnupg

  if [[ ! -f "$SURY_KEYRING" ]]; then
    wget -qO "$SURY_KEYRING" https://packages.sury.org/php/apt.gpg
    chmod 644 "$SURY_KEYRING"
  fi

  local got_fpr
  got_fpr="$(gpg --show-keys --with-colons "$SURY_KEYRING" 2>/dev/null | awk -F: '$1=="fpr"{print toupper($10); exit}')"
  [[ -n "$got_fpr" ]] || die "Could not read fingerprint from $SURY_KEYRING"
  [[ "$got_fpr" == "${SURY_EXPECTED_FPR^^}" ]] || die "Sury key fingerprint mismatch. expected=${SURY_EXPECTED_FPR^^} got=$got_fpr"

  echo "deb [signed-by=${SURY_KEYRING}] https://packages.sury.org/php/ $(lsb_release -sc) main" >"$SURY_LIST"
}

install_packages() {
  log "Installing packages (note: binaries install to /usr; caches are relocated to /opt prefix)"
  wait_for_apt_locks
  apt-get update -y
  DEBIAN_FRONTEND=noninteractive apt-get install -y \
    apache2 mariadb-server memcached rsync curl unzip git \
    libapache2-mod-php${PHP_APACHE_VERSION} php${PHP_APACHE_VERSION} php${PHP_APACHE_VERSION}-cli php${PHP_APACHE_VERSION}-common \
    php${PHP_APACHE_VERSION}-curl php${PHP_APACHE_VERSION}-gd php${PHP_APACHE_VERSION}-imap php${PHP_APACHE_VERSION}-ldap \
    php${PHP_APACHE_VERSION}-mbstring php${PHP_APACHE_VERSION}-mysql php${PHP_APACHE_VERSION}-odbc php${PHP_APACHE_VERSION}-soap \
    php${PHP_APACHE_VERSION}-xml php${PHP_APACHE_VERSION}-zip php${PHP_APACHE_VERSION}-bcmath php${PHP_APACHE_VERSION}-gmp \
    php${PHP_APACHE_VERSION}-dev php${PHP_APACHE_VERSION}-imagick php${PHP_APACHE_VERSION}-intl php${PHP_APACHE_VERSION}-snmp \
    php${PHP_APACHE_VERSION}-mcrypt php${PHP_APACHE_VERSION}-memcached php-pear \
    php${PHP_COMPOSER_VERSION}-cli composer

  command -v "php${PHP_APACHE_VERSION}" >/dev/null 2>&1 || die "php${PHP_APACHE_VERSION} not found after install"
  command -v "php${PHP_COMPOSER_VERSION}" >/dev/null 2>&1 || die "php${PHP_COMPOSER_VERSION} not found after install"
  command -v composer >/dev/null 2>&1 || die "composer not found after install"
}

# -----------------------------
# /opt prefix directories and deployment
# -----------------------------
ensure_prefix_layout() {
  log "Preparing prefix layout under ${PREFIX}"
  mkdir -p "$APP_DIR" "$DATA_DIR" "$OPT_VAR" "$TMPROOT" "$COMPOSER_HOME_DIR"
  chmod 755 "$APP_DIR" "$DATA_DIR" "$OPT_VAR" "$TMPROOT"
  chown -R www-data:www-data "$DATA_DIR" "$COMPOSER_HOME_DIR" || true

  mkdir -p "${DATA_DIR}/repositorio"
  chown -R www-data:www-data "${DATA_DIR}/repositorio"

  if [[ -e "${APP_DIR}/repositorio" && ! -L "${APP_DIR}/repositorio" ]]; then
    die "${APP_DIR}/repositorio exists and is not a symlink; move it aside first."
  fi
  [[ -e "${APP_DIR}/repositorio" ]] || ln -s "${DATA_DIR}/repositorio" "${APP_DIR}/repositorio"
}

sync_sources() {
  log "Deploying sources to ${APP_DIR} (rsync --delete)"
  rsync -a --delete \
    "${SRC_ROOT}/infra" "${SRC_ROOT}/sei" "${SRC_ROOT}/sip" "${SRC_ROOT}/docs" "${SRC_ROOT}/mysql" \
    "${APP_DIR}/"
  chown -R www-data:www-data "${APP_DIR}"
}

set_ini_kv() {
  local file="$1" key="$2" value="$3"
  [[ -f "$file" ]] || die "php.ini not found at $file"
  if grep -qE "^\s*;?\s*${key}\s*=" "$file"; then
    sed -i -E "s|^\s*;?\s*${key}\s*=.*|${key} = ${value}|" "$file"
  else
    echo "${key} = ${value}" >>"$file"
  fi
}

configure_memcached() {
  log "Configuring Memcached"
  sed -i 's/^-m .*/-m 1024/' "$MEMCACHED_CONF" || true
  sed -i 's/^-c .*/-c 4096/' "$MEMCACHED_CONF" || true
  if grep -qE '^-l ' "$MEMCACHED_CONF"; then
    sed -i 's/^-l .*/-l 127.0.0.1/' "$MEMCACHED_CONF" || true
  else
    echo "-l 127.0.0.1" >>"$MEMCACHED_CONF"
  fi
  systemctl enable memcached
  systemctl restart memcached
}

configure_php_ini() {
  log "Configuring PHP ${PHP_APACHE_VERSION} (Apache) include_path -> ${APP_DIR}"
  set_ini_kv "$PHP_INI" "short_open_tag" "On"
  set_ini_kv "$PHP_INI" "date.timezone" "\"America/Sao_Paulo\""
  set_ini_kv "$PHP_INI" "max_input_vars" "200"
  set_ini_kv "$PHP_INI" "session.gc_maxlifetime" "2880"
  set_ini_kv "$PHP_INI" "post_max_size" "40M"
  set_ini_kv "$PHP_INI" "upload_max_filesize" "20M"
  set_ini_kv "$PHP_INI" "include_path" "\".:${APP_DIR}/infra/infra_php\""
}

configure_apache() {
  log "Configuring Apache vhost to serve from ${APP_DIR}"
  a2dissite 000-default.conf >/dev/null 2>&1 || true

  cat >"$SITE_CONF" <<EOF
<VirtualHost *:80>
  ServerName localhost
  DocumentRoot ${APP_DIR}/sei/web

  <Directory ${APP_DIR}/sei/web>
    AllowOverride All
    Require all granted
  </Directory>

  Alias /sei ${APP_DIR}/sei/web
  <Directory ${APP_DIR}/sei/web>
    AllowOverride All
    Require all granted
  </Directory>

  Alias /sip ${APP_DIR}/sip/web
  <Directory ${APP_DIR}/sip/web>
    AllowOverride All
    Require all granted
  </Directory>

  ErrorLog ${APACHE_LOG_DIR}/sei-error.log
  CustomLog ${APACHE_LOG_DIR}/sei-access.log combined
</VirtualHost>
EOF

  a2enmod rewrite headers expires deflate >/dev/null
  a2ensite sei.conf >/dev/null
  apache2ctl -t
  systemctl enable apache2
  systemctl restart apache2
}

make_mysql_defaults_file() {
  local user="$1" pass="$2" host="${3:-localhost}"
  local f
  f="$(mktemp)"
  chmod 600 "$f"
  {
    echo "[client]"
    echo "user=${user}"
    [[ -n "$pass" ]] && echo "password=${pass}"
    echo "host=${host}"
  } >"$f"
  echo "$f"
}

mysql_root_exec() {
  if mysql -uroot -e "SELECT 1" >/dev/null 2>&1; then
    mysql -uroot "$@"
    return
  fi
  local df
  df="$(make_mysql_defaults_file root "$SEI_DB_ROOT_PASSWORD")"
  trap 'rm -f "$df"' RETURN
  mysql --defaults-extra-file="$df" "$@"
}

bootstrap_database() {
  log "Bootstrapping MariaDB"
  systemctl enable mariadb
  systemctl start mariadb

  mysql_root_exec -e "SELECT 1" >/dev/null 2>&1 || die "Cannot connect to MariaDB as root."

  mysql_root_exec -e "CREATE DATABASE IF NOT EXISTS \`${DB_SEI}\` CHARACTER SET utf8 COLLATE utf8_general_ci;"
  mysql_root_exec -e "CREATE DATABASE IF NOT EXISTS \`${DB_SIP}\` CHARACTER SET utf8 COLLATE utf8_general_ci;"
  mysql_root_exec -e "CREATE USER IF NOT EXISTS '${SEI_DB_APP_USER}'@'localhost' IDENTIFIED BY '${SEI_DB_APP_PASS}';"
  mysql_root_exec -e "GRANT ALL PRIVILEGES ON \`${DB_SEI}\`.* TO '${SEI_DB_APP_USER}'@'localhost';"
  mysql_root_exec -e "GRANT ALL PRIVILEGES ON \`${DB_SIP}\`.* TO '${SEI_DB_APP_USER}'@'localhost';"
  mysql_root_exec -e "FLUSH PRIVILEGES;"

  if ! mysql_root_exec -D "${DB_SEI}" -e "SHOW TABLES;" | grep -q "infra_parametro"; then
    log "Importing SEI schema"
    mysql_root_exec "${DB_SEI}" <"${SQL_DIR}/sei_3_0_0_BD_Ref_Exec.sql"
  fi
  if ! mysql_root_exec -D "${DB_SIP}" -e "SHOW TABLES;" | grep -q "sip_usuario"; then
    log "Importing SIP schema"
    mysql_root_exec "${DB_SIP}" <"${SQL_DIR}/sip_3_0_0_BD_Ref_Exec.sql"
  fi
}

install_composer_deps() {
  local infra_dir="${APP_DIR}/infra/infra_php"
  [[ -f "${infra_dir}/composer.json" ]] || { log "No composer.json; skipping Composer"; return 0; }

  log "Installing Composer deps (solve as PHP 5.6; Composer runs under php${PHP_COMPOSER_VERSION})"
  mkdir -p "$COMPOSER_HOME_DIR"
  chown -R www-data:www-data "$COMPOSER_HOME_DIR"

  pushd "$infra_dir" >/dev/null
  sudo -u www-data COMPOSER_HOME="$COMPOSER_HOME_DIR" \
    "php${PHP_COMPOSER_VERSION}" /usr/bin/composer config platform.php 5.6.40 --no-interaction
  sudo -u www-data COMPOSER_HOME="$COMPOSER_HOME_DIR" \
    "php${PHP_COMPOSER_VERSION}" /usr/bin/composer install \
      --no-dev --no-interaction --prefer-dist --optimize-autoloader
  popd >/dev/null

  local autoload="${infra_dir}/vendor/autoload.php"
  [[ -f "$autoload" ]] || die "Composer vendor missing: $autoload"
  "php${PHP_APACHE_VERSION}" -r "require '${autoload}';" >/dev/null 2>&1 \
    || die "Composer autoload failed under php${PHP_APACHE_VERSION}"
}

install_solr_opt_in() {
  [[ "$INSTALL_SOLR" == "1" ]] || return 0
  [[ -n "$SOLR_TGZ_URL" ]] || die "INSTALL_SOLR=1 requires SOLR_TGZ_URL to be provided (no default assumed)."

  log "Installing Solr under prefix (opt-in)"
  wait_for_apt_locks
  apt-get update -y
  DEBIAN_FRONTEND=noninteractive apt-get install -y wget openjdk-11-jre-headless

  mkdir -p "$SOLR_INSTALL_DIR" "$SOLR_DATA_DIR"
  if ! id -u solr >/dev/null 2>&1; then
    useradd --system --home "$SOLR_INSTALL_DIR" --shell /usr/sbin/nologin solr
  fi
  chown -R solr:solr "$SOLR_INSTALL_DIR" "$SOLR_DATA_DIR"

  local tgz="${TMPROOT}/solr.tgz"
  wget -O "$tgz" "$SOLR_TGZ_URL"

  if [[ -n "$SOLR_SHA512" ]]; then
    echo "${SOLR_SHA512}  ${tgz}" | sha512sum -c -
  else
    echo "WARN: SOLR_SHA512 not provided; tarball integrity not verified." >&2
  fi

  mkdir -p "${TMPROOT}/solr-extract"
  tar -xzf "$tgz" -C "${TMPROOT}/solr-extract"
  local extracted
  extracted="$(find "${TMPROOT}/solr-extract" -maxdepth 1 -type d -name 'solr-*' | head -n1)"
  [[ -n "$extracted" ]] || die "Could not find extracted solr-* directory."

  rsync -a --delete "${extracted}/" "${SOLR_INSTALL_DIR}/"
  chown -R solr:solr "${SOLR_INSTALL_DIR}"

  cat >/etc/systemd/system/solr.service <<EOF
[Unit]
Description=Apache Solr (prefix install)
After=network.target

[Service]
Type=forking
User=solr
Group=solr
Environment=SOLR_HOME=${SOLR_DATA_DIR}
Environment=SOLR_PORT=${SOLR_PORT}
ExecStart=${SOLR_INSTALL_DIR}/bin/solr start -p ${SOLR_PORT} -s ${SOLR_DATA_DIR}
ExecStop=${SOLR_INSTALL_DIR}/bin/solr stop -p ${SOLR_PORT}
Restart=on-failure
LimitNOFILE=65000

[Install]
WantedBy=multi-user.target
EOF

  systemctl daemon-reload
  systemctl enable solr
  systemctl restart solr
}

summarize() {
  log "Summary"
  echo "PREFIX        : ${PREFIX}"
  echo "APP_DIR       : ${APP_DIR}"
  echo "DATA_DIR      : ${DATA_DIR}"
  echo "OPT_VAR       : ${OPT_VAR}"
  echo "SECRETS_FILE  : ${SEI_SECRETS_FILE} (0600)"
  echo "TMPROOT       : ${TMPROOT}"
  echo "SEI URL       : http://localhost/sei"
  echo "SIP URL       : http://localhost/sip"
  if [[ "$INSTALL_SOLR" == "1" ]]; then
    echo "Solr URL      : http://localhost:${SOLR_PORT}/solr"
  else
    echo "Solr          : not installed by script (INSTALL_SOLR=0)"
  fi
  echo
  echo "Note: apt-installed binaries (PHP/Apache/Java) still go to /usr; this script relocates large mutable state under PREFIX."
}

main() {
  require_root
  detect_arch

  mkdir -p "$PREFIX" "$TMPROOT"
  export TMPDIR="$TMPROOT"
  ensure_exec_under_opt

  init_secrets
  relocate_var_heavy_dirs

  add_sury_repo
  install_packages
  relocate_mariadb_datadir_to_opt

  ensure_prefix_layout
  sync_sources
  configure_memcached
  configure_php_ini
  configure_apache
  bootstrap_database
  install_composer_deps
  install_solr_opt_in

  summarize
  bash "${SRC_ROOT}/scripts/verify-sei-stack.sh"
}

main "$@"
