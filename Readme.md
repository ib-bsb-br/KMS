# SEI - Sistema Eletrônico de Informação (Refactored for Debian 11 / RK3588)

This repository contains the source code and installation scripts for deploying SEI on **Debian 11 (Bullseye)**, specifically targeted for **Rockchip RK3588** hardware.

## Installation

### Prerequisites
- Debian 11 (Bullseye)
- Root privileges
- Internet access

### Installation Steps

1. Clone this repository to your machine.
2. Run the installation script:

   ```bash
   sudo ./install_debian.sh
   ```

   This script will:
   - Install PHP 5.6 (via Sury repository).
   - Install Apache, MariaDB, and Memcached.
   - Configure the database and import schemas.
   - Deploy the application to `/var/www/html`.
   - Configure the application settings.

### Verification

To verify the installation, run:

```bash
./verify_install.sh
```

## Architecture

- **Web Server**: Apache 2.4
- **Database**: MariaDB
- **Language**: PHP 5.6
- **Cache**: Memcached

## Configuration

The main configuration files are located at:
- `/var/www/html/sei/config/ConfiguracaoSEI.php`
- `/var/www/html/sip/config/ConfiguracaoSip.php`

The installation script automatically configures these with default values:
- **DB User**: `sei_user`
- **DB Password**: `sei_password`
- **Host**: `localhost`

## Notes for RK3588

This deployment leverages native system packages.
- **PHP 5.6**: Installed from `deb.sury.org`, which provides ARM64 binaries.
- **Storage**: The script defaults to `/var/www/html` for code and `/mnt/mSATA/sei-dados` for file storage (if `/mnt/mSATA` exists, otherwise ensure path is valid).
