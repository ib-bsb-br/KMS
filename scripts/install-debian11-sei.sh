#!/bin/bash
set -euo pipefail

# Automated installer for SEI/SIP on Debian 11 (aarch64-friendly)
# Default installation location can be overridden via SEI_BASE_DIR env var.
# Database root password is expected via SEI_DB_ROOT_PASSWORD when MariaDB root uses one.

BASE_DIR=${SEI_BASE_DIR:-/opt/sei}
DB_SEI="sei"
DB_SIP="sip"
DB_USER="sei_app"
DB_PASS="sei_app_password"
DB_ROOT_PASS=${SEI_DB_ROOT_PASSWORD:-}
PHP_VERSION="5.6"
PHP_INI="/etc/php/${PHP_VERSION}/apache2/php.ini"
MEMCACHED_CONF="/etc/memcached.conf"
SITE_CONF="/etc/apache2/sites-available/sei.conf"
SQL_DIR="$(cd "$(dirname "$0")/.." && pwd)/mysql"
SRC_ROOT="$(cd "$(dirname "$0")/.." && pwd)"

require_root() {
  if [[ $EUID -ne 0 ]]; then
    echo "This installer must run as root." >&2
    exit 1
  fi
}

detect_arch() {
  ARCH=$(dpkg --print-architecture)
  if [[ "$ARCH" != "arm64" && "$ARCH" != "amd64" ]]; then
    echo "Unsupported architecture: ${ARCH}. Proceeding anyway." >&2
  fi
}

add_php_repo() {
  if ! apt-key list 2>/dev/null | grep -q "packages.sury.org"; then
    echo "Adding Sury PHP repository for PHP ${PHP_VERSION}..."
    apt-get update
    apt-get install -y ca-certificates apt-transport-https lsb-release wget gnupg
    wget -qO /etc/apt/trusted.gpg.d/sury-php.gpg https://packages.sury.org/php/apt.gpg
    echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" >/etc/apt/sources.list.d/sury-php.list
  fi
}

install_packages() {
  echo "Installing Apache, PHP ${PHP_VERSION}, MariaDB, Memcached and utilities..."
  apt-get update
  DEBIAN_FRONTEND=noninteractive apt-get install -y \
    apache2 mariadb-server memcached rsync curl \
    libapache2-mod-php${PHP_VERSION} php${PHP_VERSION} php${PHP_VERSION}-cli php${PHP_VERSION}-common \
    php${PHP_VERSION}-curl php${PHP_VERSION}-gd php${PHP_VERSION}-imap php${PHP_VERSION}-ldap \
    php${PHP_VERSION}-mbstring php${PHP_VERSION}-mysql php${PHP_VERSION}-odbc php${PHP_VERSION}-soap \
    php${PHP_VERSION}-xml php${PHP_VERSION}-zip php${PHP_VERSION}-bcmath php${PHP_VERSION}-gmp \
    php${PHP_VERSION}-dev php${PHP_VERSION}-imagick php${PHP_VERSION}-intl php${PHP_VERSION}-snmp \
    php${PHP_VERSION}-mcrypt php${PHP_VERSION}-memcached php-pear unzip
}

sync_sources() {
  echo "Copying SEI/SIP sources to ${BASE_DIR}..."
  mkdir -p "${BASE_DIR}"
  rsync -a "${SRC_ROOT}/infra/" "${BASE_DIR}/infra/"
  rsync -a "${SRC_ROOT}/sei/" "${BASE_DIR}/sei/"
  rsync -a "${SRC_ROOT}/sip/" "${BASE_DIR}/sip/"
  rsync -a "${SRC_ROOT}/docs/" "${BASE_DIR}/docs/"
  rsync -a "${SRC_ROOT}/mysql/" "${BASE_DIR}/mysql/"
  chown -R www-data:www-data "${BASE_DIR}"
}

configure_memcached() {
  echo "Tuning Memcached..."
  sed -i "s/^\-m .*/-m 1028/" "${MEMCACHED_CONF}" || true
  sed -i "s/^\-c .*/-c 4096/" "${MEMCACHED_CONF}" || true
  if ! grep -q "^-l" "${MEMCACHED_CONF}"; then
    echo "-l 127.0.0.1" >>"${MEMCACHED_CONF}"
  else
    sed -i "s/^\-l .*/-l 127.0.0.1/" "${MEMCACHED_CONF}" || true
  fi
  systemctl enable memcached
  systemctl restart memcached
}

configure_php_ini() {
  echo "Configuring php.ini at ${PHP_INI}..."
  sed -i "s/^short_open_tag = .*/short_open_tag = On/" "${PHP_INI}"
  sed -i "s/^;\?date.timezone =.*/date.timezone = America\/Sao_Paulo/" "${PHP_INI}"
  sed -i "s/^max_input_vars = .*/max_input_vars = 200/" "${PHP_INI}" || echo "max_input_vars = 200" >>"${PHP_INI}"
  sed -i "s/^session.gc_maxlifetime = .*/session.gc_maxlifetime = 2880/" "${PHP_INI}"
  sed -i "s/^post_max_size = .*/post_max_size = 40M/" "${PHP_INI}"
  sed -i "s/^upload_max_filesize = .*/upload_max_filesize = 20M/" "${PHP_INI}"
  sed -i "s/^;\?include_path =.*/include_path = .:${BASE_DIR//\//\/}\/infra\/infra_php/" "${PHP_INI}"
}

configure_apache() {
  echo "Configuring Apache site..."
  a2dissite 000-default.conf >/dev/null 2>&1 || true
  cat >"${SITE_CONF}" <<EOF_CONF
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot ${BASE_DIR}/sei/web

    <Directory ${BASE_DIR}/sei/web>
        AllowOverride All
        Require all granted
    </Directory>

    Alias /sei ${BASE_DIR}/sei/web
    <Directory ${BASE_DIR}/sei/web>
        AllowOverride All
        Require all granted
    </Directory>

    Alias /sip ${BASE_DIR}/sip/web
    <Directory ${BASE_DIR}/sip/web>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/sei-error.log
    CustomLog ${APACHE_LOG_DIR}/sei-access.log combined
</VirtualHost>
EOF_CONF

  a2enmod rewrite headers expires deflate >/dev/null
  a2ensite sei.conf >/dev/null
  systemctl enable apache2
  systemctl restart apache2
}

start_mariadb() {
  systemctl enable mariadb
  systemctl start mariadb
}

mysql_exec() {
  if [[ -n "${DB_ROOT_PASS}" ]]; then
    mysql -uroot -p"${DB_ROOT_PASS}" "$@"
  else
    mysql -uroot "$@"
  fi
}

import_databases() {
  echo "Preparing MariaDB databases..."
  start_mariadb

  mysql_exec -e "CREATE DATABASE IF NOT EXISTS \`${DB_SEI}\` CHARACTER SET utf8 COLLATE utf8_general_ci;"
  mysql_exec -e "CREATE DATABASE IF NOT EXISTS \`${DB_SIP}\` CHARACTER SET utf8 COLLATE utf8_general_ci;"
  mysql_exec -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
  mysql_exec -e "GRANT ALL PRIVILEGES ON \`${DB_SEI}\`.* TO '${DB_USER}'@'localhost';"
  mysql_exec -e "GRANT ALL PRIVILEGES ON \`${DB_SIP}\`.* TO '${DB_USER}'@'localhost';"
  mysql_exec -e "FLUSH PRIVILEGES;"

  if ! mysql_exec -D "${DB_SEI}" -e "SHOW TABLES;" | grep -q "infra_parametro"; then
    echo "Importing SEI schema..."
    mysql_exec "${DB_SEI}" <"${SQL_DIR}/sei_3_0_0_BD_Ref_Exec.sql"
  fi

  if ! mysql_exec -D "${DB_SIP}" -e "SHOW TABLES;" | grep -q "sip_usuario"; then
    echo "Importing SIP schema..."
    mysql_exec "${DB_SIP}" <"${SQL_DIR}/sip_3_0_0_BD_Ref_Exec.sql"
  fi
}

summarize() {
  echo "\nInstallation summary"
  echo "--------------------"
  echo "Base directory : ${BASE_DIR}"
  echo "SEI URL        : http://localhost/sei"
  echo "SIP URL        : http://localhost/sip"
  echo "DB user        : ${DB_USER}"
  echo "DB password    : ${DB_PASS}"
  echo "DB names       : ${DB_SEI}, ${DB_SIP}"
}

main() {
  require_root
  detect_arch
  add_php_repo
  install_packages
  sync_sources
  configure_memcached
  configure_php_ini
  configure_apache
  import_databases
  summarize
}

main "$@"
