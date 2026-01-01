SEI - Sistema Eletrônico de Informação

Repositório com automação de instalação do SEI/SIP para Debian 11 com foco em arm64 (RK3588), priorizando que todo o estado mutável e caches fiquem sob `/opt` para aliviar partições raiz pequenas.

### Automação principal

* **Instalador**: `scripts/install-debian11-sei.sh`
  * Prefixo padrão: `/mnt/mSATA/SEI` (sobreponha com `SEI_PREFIX` ou `SEI_BASE_DIR`).
  * Reloca caches do APT, logs do Apache e datadir do MariaDB para `${PREFIX}/var` e cria symlinks de volta para `/var`.
  * Coloca fontes em `${PREFIX}/app` e dados persistentes (repositório/uploads) em `${PREFIX}/data`.
  * Gera e persiste segredos em `${PREFIX}/secrets/sei-install.env` (sem credenciais hardcoded no código); senha de root só é persistida se fornecida explicitamente (Debian usa `unix_socket` por padrão).
  * Configura PHP 5.6 (Sury), Apache, Memcached (1024MB), MariaDB, Composer (rodando em PHP 7.4) e importa os bancos `sei`/`sip`.
  * Instala **obrigatoriamente** o Solr: defina `SOLR_TGZ_URL` e `SOLR_SHA512` (checksum exigido) para baixar o artefato. Instalação, serviço systemd e dados ficam sob `${PREFIX}/solr*`.
  * Executa verificação ao final; pode ser pulada com `SEI_RUN_VERIFY=0` ou tornada não fatal com `SEI_VERIFY_STRICT=0`.

* **Verificador**: `scripts/verify-sei-stack.sh`
  * Reaproveita credenciais do arquivo de segredos e grava log em `${PREFIX}/var/log/sei/verify.log`.
  * Checa status de Apache/Memcached/MariaDB/Solr, módulos PHP (incluindo MySQL), docroots, Composer autoload e rotas HTTP /sei, /sip e /solr.

### Uso rápido

```bash
# como root
SEI_PREFIX=/mnt/mSATA/SEI \
SEI_DB_ROOT_PASSWORD=<senha-root-mysql> \
bash scripts/install-debian11-sei.sh

# Solr obrigatório: forneça URL e SHA-512 do artefato
SOLR_TGZ_URL="https://.../solr.tgz" SOLR_SHA512="<sha512>" \
bash scripts/install-debian11-sei.sh

# verificação (já executada pelo instalador; use SEI_RUN_VERIFY=0 para pular)
bash scripts/verify-sei-stack.sh
```

### Estrutura esperada (padrão)

* Código: `/mnt/mSATA/SEI/app`
* Dados persistentes (repositorio/uploads): `/mnt/mSATA/SEI/data`
* Logs/caches/datadir MariaDB e caches APT: `/mnt/mSATA/SEI/var`
* Segredos: `/mnt/mSATA/SEI/secrets/sei-install.env`
* Cache do Composer: `/mnt/mSATA/SEI/composer-cache`

### Observações

* Binários instalados via `apt` continuam em `/usr`; o script apenas realoca caches e dados pesados.
* Ajuste os endpoints de Solr/JODConverter/SMTP conforme o ambiente final (Solr já sobe em `http://localhost:8983/solr`).
* A checagem de fingerprint do repositório Sury gera aviso por padrão; defina `SURY_STRICT_FPR=1` para bloquear em caso de rotação de chave.

### Upstream SEI NGINX
```
    upstream homolog-sei.com.br {
            ip_hash;
            server 10.1.0.133;
            server 10.1.0.130;
            keepalive 32;
        }

        server {
            listen 80;
            listen [::]:80;
            server_name homolog-sei.com.br;

    #file-size

            client_max_body_size 6144M;

    #ssl
            listen 443 ssl;
            listen [::]:443 ssl;
            ssl_certificate     /etc/nginx/ssl/fullchain1.pem;
            ssl_certificate_key /etc/nginx/ssl/privkey1.pem;
            ssl_protocols TLSv1 TLSv1.1 TLSv1.2 TLSv1.3;
            ssl_prefer_server_ciphers on;
            ssl_ciphers                 ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES128-GCM-SHA256;
    #logs
            access_log      /var/log/nginx/homolog-sei.access.log main;
            error_log       /var/log/nginx/homolog-sei.error.log warn;

    # force https-redirects
        if ($scheme = http) {
            return 301 https://$server_name$request_uri;
    }

            location / {

                    proxy_next_upstream     error timeout invalid_header http_500;
                    proxy_connect_timeout   3;
                    proxy_pass              http://homolog-sei.com.br;
                   proxy_set_header           X-Real-IP   $remote_addr;
                   proxy_set_header           X-Forwarded-For  \$proxy_add_x_forwarded_for;
                   proxy_set_header           X-Forwarded-Proto  $scheme;
                   proxy_set_header           X-Forwarded-Server  $host;
                   proxy_set_header           X-Forwarded-Host  $host;
                   proxy_redirect http:// https://;
           }
    }
```
