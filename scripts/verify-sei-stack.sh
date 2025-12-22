#!/bin/bash
set -euo pipefail

BASE_DIR=${SEI_BASE_DIR:-/opt/sei}
PHP_VERSION=${PHP_VERSION:-5.6}
LOG_FILE=${LOG_FILE:-/tmp/sei-stack-check.log}

run_check() {
  local description="$1"
  shift
  echo "[CHECK] ${description}" | tee -a "$LOG_FILE"
  if "$@" >>"$LOG_FILE" 2>&1; then
    echo "        OK"
  else
    echo "        FAIL (see $LOG_FILE)"
    return 1
  fi
}

main() {
  : >"$LOG_FILE"
  run_check "Apache is active" systemctl is-active --quiet apache2
  run_check "Memcached is active" systemctl is-active --quiet memcached
  run_check "MariaDB is active" systemctl is-active --quiet mariadb
  run_check "PHP ${PHP_VERSION} modules" php${PHP_VERSION} -m
  run_check "SEI document root exists" test -d "${BASE_DIR}/sei/web"
  run_check "SIP document root exists" test -d "${BASE_DIR}/sip/web"
  run_check "Database 'sei' reachable" mysql -usei_app -psei_app_password -e "USE sei; SHOW TABLES LIMIT 1;" >/dev/null
  run_check "Database 'sip' reachable" mysql -usei_app -psei_app_password -e "USE sip; SHOW TABLES LIMIT 1;" >/dev/null

  echo "\nLog written to $LOG_FILE"
}

main "$@"
