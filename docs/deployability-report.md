# Deployability Assessment (Debian 11 / RK3588)

## Scope and Method
- Static review plus creation of Debian Bullseye automation for aarch64.
- No runtime validation executed here, but installer and verifier scripts are provided to exercise the full stack on-target.

## Observations
- **Platform now Debian-based**: automation installs Apache2, PHP 5.6 (Sury repository), Memcached, and MariaDB using `apt` with arm64-compatible packages.
- **Filesystem layout standardized**: sources and docs are synchronized to `/opt/sei` by default, with an override via `SEI_BASE_DIR`.
- **Configuration defaults are concrete**: SEI/SIP config files now point to localhost services, real database names/users (`sei`, `sip`, `sei_app`/`sei_app_password`), memcached at `127.0.0.1:11211`, and localhost integrations (Solr, JODConverter, mail).
- **Database bootstrapping automated**: SEI and SIP schemas are imported automatically from bundled SQL scripts; user and privileges are created.
- **Verification available**: a health-check script validates service status, PHP module loading, document roots, and DB connectivity.

## How to deploy on the target host
1. (Optional) Export env vars: `export SEI_BASE_DIR=/opt/sei` and `export SEI_DB_ROOT_PASSWORD=<senha-root-mysql>`.
2. Run as root: `bash scripts/install-debian11-sei.sh`.
3. After completion, run `bash scripts/verify-sei-stack.sh` to confirm services and database connectivity; review `/tmp/sei-stack-check.log`.

## Files introduced
- `scripts/install-debian11-sei.sh` – end-to-end installer for Debian 11 aarch64.
- `scripts/verify-sei-stack.sh` – post-install validation.
- Updated `Readme.md`, `sei/config/ConfiguracaoSEI.php`, and `sip/config/ConfiguracaoSip.php` with Debian-focused defaults.

## Remaining manual considerations
- Provide Solr and JODConverter services at the configured localhost endpoints or adjust URLs accordingly.
- Configure outbound SMTP if sendmail is insufficient; defaults use localhost without authentication.
- Ensure sufficient storage at `/opt/sei/repositorio` for SEI attachments.
