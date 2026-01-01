#!/usr/bin/env bash
set -Eeuo pipefail

PREFIX="${SEI_PREFIX:-/opt/sei-stack}"
APP_DIR="${SEI_BASE_DIR:-${PREFIX}/app}"
SEI_SECRETS_FILE="${SEI_SECRETS_FILE:-${PREFIX}/secrets/sei-install.env}"
PHP_VERSION="${PHP_VERSION:-5.6}"
LOG_FILE="${LOG_FILE:-${PREFIX}/var/log/sei/verify.log}"

umask 077

load_secrets_if_present() { [[ -f "$SEI_SECRETS_FILE" ]] && source "$SEI_SECRETS_FILE"; }

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

run_check() {
  local description="$1"; shift
  echo "[CHECK] ${description}" | tee -a "$LOG_FILE"
  if "$@" >>"$LOG_FILE" 2>&1; then
    echo "        OK"
  else
    echo "        FAIL (see $LOG_FILE)"
    return 1
  fi
}

main() {
  mkdir -p "$(dirname "$LOG_FILE")"
  : >"$LOG_FILE"

  load_secrets_if_present
  local db_user="${SEI_DB_APP_USER:-sei_app}"
  local db_pass="${SEI_DB_APP_PASS:-}"
  local df
  df="$(make_mysql_defaults_file "$db_user" "$db_pass")"
  trap 'rm -f "$df"' EXIT

  run_check "Apache configtest" apache2ctl -t
  run_check "SEI site enabled" test -e /etc/apache2/sites-enabled/sei.conf

  run_check "Apache is active" systemctl is-active --quiet apache2
  run_check "Memcached is active" systemctl is-active --quiet memcached
  run_check "MariaDB is active" systemctl is-active --quiet mariadb

  run_check "PHP ${PHP_VERSION} modules" "php${PHP_VERSION}" -m
  run_check "PHP ${PHP_VERSION} mysql extension" bash -c "php${PHP_VERSION} -m | grep -qi 'mysql'"
  run_check "SEI docroot exists" test -d "${APP_DIR}/sei/web"
  run_check "SIP docroot exists" test -d "${APP_DIR}/sip/web"

  run_check "Composer autoload present" test -f "${APP_DIR}/infra/infra_php/vendor/autoload.php"
  run_check "Composer autoload loads under php${PHP_VERSION}" "php${PHP_VERSION}" -r "require '${APP_DIR}/infra/infra_php/vendor/autoload.php';"

  run_check "HTTP /sei responds" curl -fsS --max-time 5 http://localhost/sei/
  run_check "HTTP /sip responds" curl -fsS --max-time 5 http://localhost/sip/ >/dev/null

  run_check "DB sei reachable" mysql --defaults-extra-file="$df" -e "USE sei; SHOW TABLES LIMIT 1;" >/dev/null
  run_check "DB sip reachable" mysql --defaults-extra-file="$df" -e "USE sip; SHOW TABLES LIMIT 1;" >/dev/null

  printf '\nVerification log: %s\n' "$LOG_FILE"
  printf 'Secrets file used: %s\n' "$SEI_SECRETS_FILE"
}

main "$@"
