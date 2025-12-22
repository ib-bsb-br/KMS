#!/bin/bash

# Install Script for SEI/SIP on Debian 11 (Bullseye) / RK3588
# Adapts legacy CentOS instructions to Debian.

set -e

# Configuration Variables
DB_ROOT_PASS="root_password" # Change this!
DB_SEI_USER="sei_user"
DB_SEI_PASS="sei_password"
DB_SIP_USER="sip_user" # Usually same user for simplicity in dev, but let's stick to one
SEI_DB_NAME="sei"
SIP_DB_NAME="sip"
DOMAIN="localhost" # Or your IP
INSTALL_DIR="/var/www/html"
DATA_DIR="/mnt/mSATA/sei-dados"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}Starting SEI/SIP Installation for Debian 11 on RK3588...${NC}"

# 1. System Update and Dependencies
echo -e "${GREEN}Updating system and installing base dependencies...${NC}"
apt-get update
apt-get install -y software-properties-common ca-certificates lsb-release apt-transport-https curl wget git zip unzip gnupg2

# 2. Add Sury PHP Repository (for PHP 5.6 on Debian 11)
echo -e "${GREEN}Adding Sury PHP repository...${NC}"
if [ ! -f /etc/apt/sources.list.d/php.list ]; then
    curl -sSLo /usr/share/keyrings/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg
    echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list
    apt-get update
fi

# 3. Install PHP 5.6 and Extensions
echo -e "${GREEN}Installing PHP 5.6 and extensions...${NC}"
apt-get install -y php5.6 php5.6-cli php5.6-fpm php5.6-mysql php5.6-xml php5.6-mbstring \
    php5.6-curl php5.6-gd php5.6-intl php5.6-soap php5.6-zip php5.6-ldap \
    php5.6-bcmath php5.6-imap php5.6-xmlrpc php5.6-gmp php5.6-memcached \
    php5.6-imagick libapache2-mod-php5.6

# 4. Install Database (MariaDB), Web Server (Apache), Caching (Memcached)
echo -e "${GREEN}Installing MariaDB, Apache, and Memcached...${NC}"
apt-get install -y mariadb-server mariadb-client apache2 memcached

# 5. Configure PHP
echo -e "${GREEN}Configuring PHP...${NC}"
PHP_INI="/etc/php/5.6/apache2/php.ini"

# Backup original php.ini
cp $PHP_INI ${PHP_INI}.bak

# Apply settings
sed -i 's/^short_open_tag = .*/short_open_tag = On/' $PHP_INI
sed -i 's/^max_input_vars = .*/max_input_vars = 10000/' $PHP_INI
sed -i 's/^memory_limit = .*/memory_limit = 1024M/' $PHP_INI
sed -i 's/^post_max_size = .*/post_max_size = 100M/' $PHP_INI
sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 100M/' $PHP_INI
sed -i 's/^;*default_charset = .*/default_charset = "iso-8859-1"/' $PHP_INI
sed -i 's/^session.gc_maxlifetime = .*/session.gc_maxlifetime = 2880/' $PHP_INI

# Ensure max_input_vars is uncommented or added if missing
if ! grep -q "^max_input_vars" $PHP_INI; then
    echo "max_input_vars = 10000" >> $PHP_INI
fi

# Add include_path for InfraPHP
# This is critical for SEI to function
if ! grep -q "include_path = .*/var/www/html/infra/infra_php" $PHP_INI; then
    # If include_path is commented out (default), uncomment and set it
    # If active, append. For simplicity in this script, we append a new line or uncomment the existing one.
    # PHP uses the last occurrence.
    echo 'include_path = ".:/var/www/html/infra/infra_php"' >> $PHP_INI
fi

# 6. Configure Memcached
echo -e "${GREEN}Configuring Memcached...${NC}"
sed -i 's/^-m 64/-m 1024/' /etc/memcached.conf
service memcached restart

# 7. Setup Database
echo -e "${GREEN}Setting up MariaDB...${NC}"
service mariadb start

# Secure installation (programmatic)
mysql -e "UPDATE mysql.user SET Password = PASSWORD('${DB_ROOT_PASS}') WHERE User = 'root'" || true
mysql -e "DROP USER IF EXISTS ''@'localhost'" || true
mysql -e "DROP DATABASE IF EXISTS test" || true
mysql -e "FLUSH PRIVILEGES"

# Create SEI/SIP DBs and User
mysql -u root -p"${DB_ROOT_PASS}" -e "CREATE DATABASE IF NOT EXISTS ${SEI_DB_NAME} CHARACTER SET latin1 COLLATE latin1_swedish_ci;"
mysql -u root -p"${DB_ROOT_PASS}" -e "CREATE DATABASE IF NOT EXISTS ${SIP_DB_NAME} CHARACTER SET latin1 COLLATE latin1_swedish_ci;"
mysql -u root -p"${DB_ROOT_PASS}" -e "GRANT ALL PRIVILEGES ON *.* TO '${DB_SEI_USER}'@'localhost' IDENTIFIED BY '${DB_SEI_PASS}';"
mysql -u root -p"${DB_ROOT_PASS}" -e "FLUSH PRIVILEGES;"

# Import Schemas
# Note: Assuming script is run from repo root
echo -e "${GREEN}Importing Database Schemas...${NC}"
if [ -f "mysql/sei_3_0_0_BD_Ref_Exec.sql" ]; then
    mysql -u root -p"${DB_ROOT_PASS}" ${SEI_DB_NAME} < mysql/sei_3_0_0_BD_Ref_Exec.sql
else
    echo -e "${RED}Error: mysql/sei_3_0_0_BD_Ref_Exec.sql not found!${NC}"
fi

if [ -f "mysql/sip_3_0_0_BD_Ref_Exec.sql" ]; then
    mysql -u root -p"${DB_ROOT_PASS}" ${SIP_DB_NAME} < mysql/sip_3_0_0_BD_Ref_Exec.sql
else
    echo -e "${RED}Error: mysql/sip_3_0_0_BD_Ref_Exec.sql not found!${NC}"
fi

# 8. Deploy Application Code
echo -e "${GREEN}Deploying Application Code...${NC}"
mkdir -p ${INSTALL_DIR}/sei
mkdir -p ${INSTALL_DIR}/sip
mkdir -p ${INSTALL_DIR}/infra
mkdir -p ${DATA_DIR}/arquivos

# Copy files
# Use cp -a to preserve attributes and copy hidden files if any
cp -a sei/* ${INSTALL_DIR}/sei/
cp -a sip/* ${INSTALL_DIR}/sip/
cp -a infra/* ${INSTALL_DIR}/infra/

# Permissions
chown -R www-data:www-data ${INSTALL_DIR}/sei
chown -R www-data:www-data ${INSTALL_DIR}/sip
chown -R www-data:www-data ${INSTALL_DIR}/infra
chown -R www-data:www-data ${DATA_DIR}
chmod -R 755 ${INSTALL_DIR}/sei
chmod -R 755 ${INSTALL_DIR}/sip
chmod -R 755 ${INSTALL_DIR}/infra

# 9. Configure SEI/SIP Config Files
echo -e "${GREEN}Configuring SEI/SIP settings...${NC}"

CONF_SEI="${INSTALL_DIR}/sei/config/ConfiguracaoSEI.php"
CONF_SIP="${INSTALL_DIR}/sip/config/ConfiguracaoSip.php"

# Helper to escape for sed
escape_sed() {
    echo "$1" | sed -e 's/[]\/$*.^[]/\\&/g'
}

# Replace placeholders in ConfiguracaoSEI.php
# Placeholders: [Servidor PHP], [servidor BD], [Servidor Memcache], [Servidor JODConverter], [Servidor .NET], [Servidor Solr], [Servidor E-Mail]
# Note: We use ISO-8859-1 encoding, so we must be careful.

# Convert to UTF-8 for editing
iconv -f ISO-8859-1 -t UTF-8 $CONF_SEI > ${CONF_SEI}.utf8

sed -i "s|\[Servidor PHP\]|${DOMAIN}|g" ${CONF_SEI}.utf8
sed -i "s|\[servidor BD\]|localhost|g" ${CONF_SEI}.utf8
sed -i "s|\[Servidor Memcache\]|localhost|g" ${CONF_SEI}.utf8
sed -i "s|'Usuario' => ''|'Usuario' => '${DB_SEI_USER}'|g" ${CONF_SEI}.utf8
sed -i "s|'Senha' => ''|'Senha' => '${DB_SEI_PASS}'|g" ${CONF_SEI}.utf8
sed -i "s|'Banco' => ''|'Banco' => '${SEI_DB_NAME}'|g" ${CONF_SEI}.utf8
sed -i "s|'Tipo' => ''|'Tipo' => 'MySql'|g" ${CONF_SEI}.utf8
sed -i "s|'RepositorioArquivos' => '/dados'|'RepositorioArquivos' => '${DATA_DIR}/arquivos'|g" ${CONF_SEI}.utf8

# Convert back to ISO-8859-1
iconv -f UTF-8 -t ISO-8859-1 ${CONF_SEI}.utf8 > $CONF_SEI
rm ${CONF_SEI}.utf8

# Replace placeholders in ConfiguracaoSip.php
iconv -f ISO-8859-1 -t UTF-8 $CONF_SIP > ${CONF_SIP}.utf8

sed -i "s|\[Servidor PHP\]|${DOMAIN}|g" ${CONF_SIP}.utf8
sed -i "s|\[Servidor BD\]|localhost|g" ${CONF_SIP}.utf8
sed -i "s|\[Servidor Memcache\]|localhost|g" ${CONF_SIP}.utf8
sed -i "s|'Usuario' => ''|'Usuario' => '${DB_SEI_USER}'|g" ${CONF_SIP}.utf8
sed -i "s|'Senha' => ''|'Senha' => '${DB_SEI_PASS}'|g" ${CONF_SIP}.utf8
sed -i "s|'Banco' => ''|'Banco' => '${SIP_DB_NAME}'|g" ${CONF_SIP}.utf8
sed -i "s|'Tipo' => ''|'Tipo' => 'MySql'|g" ${CONF_SIP}.utf8

iconv -f UTF-8 -t ISO-8859-1 ${CONF_SIP}.utf8 > $CONF_SIP
rm ${CONF_SIP}.utf8

# 10. Configure Apache Virtual Host
echo -e "${GREEN}Configuring Apache...${NC}"
cat > /etc/apache2/sites-available/sei.conf <<EOF
<VirtualHost *:80>
    ServerName ${DOMAIN}
    DocumentRoot ${INSTALL_DIR}/sei/web

    Alias /sei ${INSTALL_DIR}/sei/web
    Alias /sip ${INSTALL_DIR}/sip/web

    <Directory ${INSTALL_DIR}/sei/web>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Order allow,deny
        allow from all
        Require all granted
    </Directory>

    <Directory ${INSTALL_DIR}/sip/web>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Order allow,deny
        allow from all
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/sei_error.log
    CustomLog \${APACHE_LOG_DIR}/sei_access.log combined
</VirtualHost>
EOF

a2dissite 000-default.conf
a2ensite sei.conf
a2enmod rewrite

service apache2 restart

echo -e "${GREEN}Installation Complete!${NC}"
echo -e "Access SEI at http://${DOMAIN}/sei"
echo -e "Access SIP at http://${DOMAIN}/sip"
