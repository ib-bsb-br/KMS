#!/bin/bash

# Verification script for SEI/SIP deployment

GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

echo "Verifying SEI/SIP Installation..."

# Check Services
check_service() {
    if systemctl is-active --quiet $1; then
        echo -e "[${GREEN}OK${NC}] Service $1 is running."
    else
        echo -e "[${RED}FAIL${NC}] Service $1 is NOT running."
    fi
}

check_service apache2
check_service mariadb
check_service memcached

# Check PHP Version
PHP_VERSION=$(php -v | head -n 1)
if [[ $PHP_VERSION == *"PHP 5.6"* ]]; then
    echo -e "[${GREEN}OK${NC}] PHP 5.6 detected: $PHP_VERSION"
else
    echo -e "[${RED}FAIL${NC}] Incorrect PHP version: $PHP_VERSION"
fi

# Check HTTP Response
echo "Checking HTTP Response..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/sei/)
if [ "$HTTP_CODE" -eq 200 ] || [ "$HTTP_CODE" -eq 302 ]; then
    echo -e "[${GREEN}OK${NC}] SEI Web Interface is reachable (HTTP $HTTP_CODE)."
else
    echo -e "[${RED}FAIL${NC}] SEI Web Interface unreachable (HTTP $HTTP_CODE)."
fi

# Check Database Connection
echo "Checking Database..."
if mysql -u sei_user -psei_password -e "use sei;" 2>/dev/null; then
    echo -e "[${GREEN}OK${NC}] Database 'sei' is accessible."
else
    echo -e "[${RED}FAIL${NC}] Database 'sei' connection failed."
fi
