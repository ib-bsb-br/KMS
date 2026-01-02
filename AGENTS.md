Refactor each and every necessary file, script, configuration, etc., enclosed within my `ib-bsb-br/KMS` github repository in order for everything within it to comply to the following limitations and dependencies, like, for example, the `Solr` version, which must be `Solr 6.1.0`, and `Java runtime` version, which must be `Java runtime 1.8`, etc.

```dependencies
<root>
<source>
txt/SEI-Instalacao-v3.0.txt
</source>
<contents>
# Instalação 

1 Baixar Arquivos ................................ 

2 Servidores ................................ 

3 Código Fonte ................................ 

4 Bases de Dados ................................ 

5 Configuração SIP ................................ 

6 Configuração SEI ................................ 

7 Configuração de Acesso aos Web Services 

8 Acesso aos Sistemas ................................ 

9 Integração LDAP/AD ................................ 

10 Carga e Sincronização de Usuários e Unidades 

11 Tabela de Parâmetros SIP ................................ 

12 Tabela de Parâmetros SEI ................................ 

13 Agendamentos ................................ 

14 Scripts ................................ 

15 Backup ................................ 

16 Auditoria ................................ 

17 Tabelas de Log ................................ 

18 HTTPS ................................ 

19 Formulário de Ouvidoria ................................ 

20 Login de Usuários Externos 

21 Pesquisa de Publicações ................................ 

22 Conferência de Documentos 

23 Config uração Máquinas Cliente 

24 Solr................................ ................................ 

25 Problemas Conhecidos e Soluções 

# 1 Baixar Arquivos 

Os arquivos fonte e backups das bases de dados estão disponíveis no servidor de SFTP do TRF4 

através do endereço sftp.trf4.jus.br (apenas a porta 22 deste servidor está liberada). O servidor somente 

estará acessível ao IP de acesso informado previamente ao 

Arquivos disponíveis para download: 

• SEI-Fontes-v3.0.0.zip 

• SEI-BD-MySQL-v3.0.0.zip 

• SEI-BD-SqlServer-v3.0.0.zip 

• SEI-BD-Oracle-v3.0.0.zip 

• SEI-Novidades-v3.0.pdf 

• SEI-Instalacao-v3.0.pdf 

• SEI-Atualizacao-v3.0.pdf 

• SEI-Web-Services-v3.0.pdf 

# Instalação - Versão 3.0 

................................ ................................ ................................ 

................................ ................................................................ ................................ 

................................ ................................ ................................ 

................................ ................................ ................................ 

................................ ................................ ................................ 

................................ ................................ ................................ 

Configuração de Acesso aos Web Services ................................ ................................ 

................................ ................................ ................................ 

................................ ................................ ................................ 

Carga e Sincronização de Usuários e Unidades ................................ ................................ 

................................ ................................ ................................ 

................................ ................................ ................................ 

................................ ................................ ................................ 

................................ ................................................................ ................................ 

................................ ................................................................ ................................ 

................................ ................................................................ ................................ 

................................ ................................ ................................ 

................................ ................................................................ ................................ 

................................ ................................ ................................ 

Login de Usuários Externos ................................................................ ................................ 

................................ ................................ ................................ 

Conferência de Documentos ................................................................ ................................ 

uração Máquinas Cliente ................................ ................................ 

................................ ................................ ................................ 

Problemas Conhecidos e Soluções ................................ ................................ 

s arquivos fonte e backups das bases de dados estão disponíveis no servidor de SFTP do TRF4 

através do endereço sftp.trf4.jus.br (apenas a porta 22 deste servidor está liberada). O servidor somente 

estará acessível ao IP de acesso informado previamente ao TRF4. Utilizar usuário e senha fornecidos. 

Arquivos disponíveis para download: 

v3.0.0.zip 

v3.0.0.zip 

v3.0.0.zip 

v3.0.pdf 

v3.0.pdf 

1

................................ ............................... 1 

................................ ........ 2 

................................ ................................... 7 

................................ ................................ 8 

................................ ............................. 9 

................................ .......................... 10 

................................ ................... 13 

................................ ...................... 14 

................................ .................... 14 

................................ ............ 16 

................................ .............. 18 

................................ .............. 18 

................................ ............................... 20 

................................ ............ 21 

................................ ........... 22 

................................ ........ 22 

................................ ............................... 23 

................................ ........... 23 

................................ ............... 24 

................................ .......... 25 

................................ ................ 25 

................................ .......... 26 

................................ .................................... 26 

................................ ................. 27 

................................ ................................ 30 

s arquivos fonte e backups das bases de dados estão disponíveis no servidor de SFTP do TRF4 

através do endereço sftp.trf4.jus.br (apenas a porta 22 deste servidor está liberada). O servidor somente 

TRF4. Utilizar usuário e senha fornecidos. 2

# 2 Servidores 

A figura abaixo ilustra a instalação existente no TRF4 servindo apenas como referência pois 

cada instituição pode montar a sua de acordo com os recursos disponíveis: 

No âmbito do TRF4 os servidores SIP e MySQL são compartilhados com outros sistemas. 

2012 2013 2014 2015 2016 

Usuários 3.569 4.045 4.634 4.796 5.170 

Processos 87.918 125.719 168.388 182.833 228.110 

Documentos Gerados 479.930 715.830 994.273 1.100.061 1.451.472 

Tamanho da Base (Gb) 15 55 98 117 159 

Documento Externos 493.796 715.830 1.032.705 1.141.716 1.498.641 

Tamanho do Repositório (Gb) 365 623 871 964 1.410 

> Todas as instalações devem ser realizadas em plataforma 64 bits.

Balanceador de Aplicação 

Tipo Máquina Virtual (VMWare) 

Sistema Operacional Red Hat Enterprise Linux 7.1 

Memória 2 Gb 

Disco 13 Gb 

CPUs 13Serviços/Extensões/Módulos • Apache 2.4.6 

• mod_proxy_balancer 

Observações Opcionalmente recomendamos também que seja instalado o módulo mod_evasive que bloqueia o cliente temporariamente em caso de várias tentativas de conexão com o servidor evitando ataques de negação de serviço. 

Nós de Aplicação (8) 

Tipo Máquina Virtual (VMWare) 

Sistema Operacional Red Hat Enterprise Linux 7.1 

Memória 8 Gb 

Disco 50 Gb 

CPUs 2

Serviços/Extensões/Módulos • Apache 2.4.6 

• PHP 5.6.5 

• Framework InfraPHP 

• Aplicação SEI 

• Se utilizando MySql: MySQLi 5 

• Se utilizando SQL Server 2012 (ou superior) MSSQL/FreeTDS 0.95 (alterar no arquivo freetds.conf a opção global "tds version" para 7.0) 

• Se utilizando Oracle instalar OCI8 versão 2.0.5 

• OpenSSL, SOAP, Curl 7.29.0, Dom 2.9.1, GD 2.4.11, DOM/XML 2.9.1, iConv 2.17, SimpleXML , Phar 2.0.2, libXML 2.9.1, BCMath, BZip2, calendar, ctype, ereg, exif, filter, GetText, hash, Zip 1.12.4, zlib 1.2.7, LDAP, json 1.3.6, FileInfo 1.0.5, intl 1.1.0 

• Uploadprogress 1.0.3.1 (https://pecl.php.net/package/uploadprogress) 

• Memcache 3.0.8 (extensão no PHP) 

• Java Runtime 1.8 

• Pacotes de fontes True Type instaladas no servidor 

Observações • Otimizações opcionais realizadas no arquivo httd.conf:              

> KeepAlive On MaxKeepAliveRequests 100 KeepAliveTimeout 15 <IfModule prefork.c> StartServers 20 MinSpareServers 10 MaxSpareServers 30 ServerLimit 2000 MaxClients 2000 MaxRequestsPerChild 0</IfModule> <IfModule worker.c> StartServers 3MaxClients 150 MinSpareThreads 25 MaxSpareThreads 75 ThreadsPerChild 25 MaxRequestsPerChild 10000 </IfModule>

4

Sistema de Permissões 

Tipo Máquina Virtual (VMWare) 

Sistema Operacional Red Hat Enterprise Linux 7.1 

Memória 8 Gb 

Disco 50 Gb 

CPUs 1

Serviços/Extensões/Módulos • Apache 2.4.6 

• PHP 5.6.5 

• Framework InfraPHP 

• Aplicação SIP 

• Se utilizando MySql: MySQLi 5 

• Se utilizando SQL Server 2012 (ou superior) MSSQL/FreeTDS 0.95 (alterar no arquivo freetds.conf a opção global "tds version" para 7.0) 

• Se utilizando Oracle instalar OCI8 versão 2.0.5 

• OpenSSL, SOAP, Curl 7.29.0, Dom 2.9.1, GD 2.4.11, DOM/XML 2.9.1, iConv 2.17, SimpleXML , Phar 2.0.2, libXML 2.9.1, BCMath, BZip2, calendar, ctype, ereg, exif, filter, GetText, hash, Zip 1.12.4, zlib 1.2.7, LDAP, json 1.3.6, FileInfo 1.0.5, intl 1.1.0 

• Uploadprogress 1.0.3.1 (https://pecl.php.net/package/uploadprogress) 

• Memcache 3.0.8 (extensão no PHP) 

Observações • Opcionalmente recomendamos também que seja instalado o módulo mod_evasive que bloqueia o cliente temporariamente em caso de várias tentativas de conexão com o servidor evitando ataques de negação de serviço. 

• Para evitar erros nos log do SIP (menu Infra/Log) identificados por “Microsoft Data Access Internet Publishing Provider Protocol Discovery” (gerados quando o Microsoft Office varre links que foram inseridos nos documentos) colocar no httpd.conf:             

> SetEnvIfNoCase user-agent "Mic rosoft Data Access Internet
> Publishing Provider Protocol Discovery" bad_bot=1 <FilesMatch "(.*)"> Order Allow,Deny Allow from all Deny from env=bad_bot </FilesMatch>

Memcache 

Tipo Máquina Virtual (VMWare) 

Sistema Operacional Red Hat Enterprise Linux 7.1 

Memória 4 Gb 

Disco 24 Gb 

CPUs 1

Serviços/Extensões/Módulos • Apache 2.4.6 

• Memcache 3.0.8 (serviço memcached) 5Observações • Também é possível, ao invés de ter uma máquina dedicada para o Memcache, instalar o serviço na máquina do SIP. 

• Se necessário o número máximo de conexões e o tamanho da cache podem ser ajustados no arquivo /etc/sysconfig/memcached, ex.: 

> MAXCONN="4096" CACHESIZE="2048"

Repositório de Arquivos 

Tipo Máquina Física 

Sistema Operacional Red Hat Enterprise Linux 7.1 

Memória 48 Gb 

Disco 1.2 Tb 

CPUs 2 Quad-Core Intel Xeon, 2667 MHz 

Serviços/Extensões/Módulos • NFS 

Mecanismo de Busca (Solr) 

Tipo Máquina Virtual (VMWare) 

Sistema Operacional Red Hat Enterprise Linux 7.1 

Memória 8 Gb 

Disco 50 Gb (diretório /tmp com no mínimo 2Gb livres) 

CPUs 2

Serviços/Extensões/Módulos • Solr 6.1.0 

• Java runtime 1.8 

JOD Converter (Geração de PDFs) 

Tipo Máquina Virtual (VMWare) 

Sistema Operacional Red Hat Enterprise Linux 7.1 

Memória 8 Gb 

Disco 50 Gb 

CPUs 1

Serviços/Extensões/Módulos • Java runtime 1.7 

• LibreOffice 

• Tomcat 6 

Observações Este servidor é opcional sendo utilizado exclusivamente na conversão de documentos externos nos formatos OpenOffice para PDF (ver chave JODConverter na seção "Configuração SEI"). Arquivo para instalação disponível em sei/bin/jodconverter-tomcat-2.2.2.zip. Após a descompactação ver detalhes no arquivo README.txt. 

MySQL Master 

Tipo Máquina Física 

Sistema Operacional Red Hat Enterprise Linux 7.2 

Memória 128 Gb 

Disco -

CPUs 2 x 10 núcleos 6Serviços/Extensões/Módulos • MySQL Enterprise Edition 5.6 

MySQL Slave 

Tipo Máquina Física 

Sistema Operacional Red Hat Enterprise Linux 7.1 

Memória 48 Gb 

Disco -

CPUs 2 x 4 núcleos 

Serviços/Extensões/Módulos • MySQL Enterprise Edition 5.6 

Cliente 

• Browsers suportados: Internet Explorer 9+, Chrome 8+, Firefox 10+ ou Safari 3+; 

• Java Runtime 1.7 ou superior (se utilizando assinatura digital) 

## Configuração do PHP 

Verificar os itens abaixo no arquivo php.ini dos servidores que rodam o SEI/SIP: 

Diretiva Valor Observação   

> include_path /opt/infra/infra_php

Adicionar o diretório infra_php    

> default_charset ISO-8859-1
> session.gc_maxlifetime 28800

Tempo de sessão (ex.: 28800 = 8 horas)        

> short_open_tag 1
> default_socket_timeout 60
> max_input_vars 2000
> magic-quotes-gpc 0
> magic_quotes_runtime 0
> magic_quotes_sybase 0
> html_errors 0

Além disso, após definir o tamanho máximo que será permitido para os arquivos externos (PDF, planilhas, imagens, vídeos,...) é necessário configurar os valores post_max_size e upload_max_filesize 

nos servidores que rodam o SEI. Sendo que post_max_size deve ser ligeiramente maior que 

> upload_max_filesize

. Além de fazer esta configuração no php.ini também é necessário alterar o parâmetro SEI_TAM_MB_DOC_EXTERNO na tabela de parâmetros do SEI (ver seção “Configuração SEI”). Uma configuração adicional de segurança consiste em definir a diretiva session.cookie_secure com o valor "1". Esta diretiva indica que o cookie de sessão somente poderá trafegar em conexão https. Entretanto antes é necessário garantir que todos os links para o SEI utilizam o prefixo "https://" (ex.: intranet, atalhos na área de trabalho, acessos externos em processos/documentos gravados em outros sistemas, ...). Se o usuário estiver logado e clicar em um link com o prefixo "http://" perderá a sessão. 7

## SELinux (Security-Enhanced Linux) 

Se o SELinux estiver ativo verificar os parâmetros abaixo nos servidores SEI/SIP:           

> setsebool –P httpd_can_network_connect 1setsebool –P httpd_can_network_memcache 1setsebool –P httpd_execmem 1 setsebool –P httpd_can_connect_ldap 1

OBS: eventualmente poderá ser necessária alteração de outros parâmetros. 

# 3 Código Fonte 

O arquivo SEI-Fontes-v3.0.0.zip contém os fontes PHP do SEI (Sistema Eletrônico de Informações), do SIP (Sistema de Permissões) e do framework InfraPHP. Após a descompactação copiar o conteúdo para um diretório que NÃO esteja localizado abaixo do DocumentRoot do apache. No linux recomendamos utilizar o diretório /opt . A estrutura deverá ficar como abaixo: 

/opt 

/sei 

/bin 

/config 

/scripts 

/temp 

/web 

/sip 

/bin 

/config 

/scripts 

/temp 

/web 

/infra 

/infra_php 

/infra_js 

/infra_css 

Abaixo sugestão de permissões de acesso aos diretórios e arquivos do Sistema Operacional: 

#SEI 

chown -R root.apache /opt/sei 

find /opt/sei -type d -exec chmod 2750 {} \; 

find /opt/sei -type f -exec chmod 0640 {} \; 

find /opt/sei/temp -type d -exec chmod 2570 {} \; 

chmod 0750 /opt/sei/bin/wkhtmltopdf-amd64 

#SIP 

chown -R root.apache /opt/sip 

find /opt/sip -type d -exec chmod 2750 {} \; 

find /opt/sip -type f -exec chmod 0640 {} \; 

find /opt/sip/temp -type d -exec chmod 2570 {} \; 

#Infra PHP 

chown -R root.apache /opt/infra 

find /opt/infra -type d -exec chmod 2750 {} \; 

find /opt/infra -type f -exec chmod 0640 {} \; 8

É necessário que os diretórios temp tenham permissão de escrita para o usuário do apache pois são utilizados pelo sistema em diversas funcionalidades como upload de arquivos, geração de arquivos PDF/ZIP e assinatura digital de documentos. Recomenda-se que seja adicionada no servidor SIP e nos servidores de aplicação SEI uma rotina para excluir durante a noite todos os arquivos destes diretórios. Sugestão de configuração da crontab: 

00 01 * * * root rm -rf /opt/sei/temp/* 

00 01 * * * root rm -rf /opt/sip/temp/* 

Criar arquivo de configurações sei.conf no diretório do apache (localizado normalmente em 

/etc/httpd/conf.d ). Este arquivo irá mapear os diretórios web, infra_css e infra_js para acesso via URL do sistema além de restringir o acesso aos demais arquivos. Se utilizando balanceador então este arquivo não deve ser adicionado nesta máquina (apenas nos nós de aplicação). Abaixo sugestão de conteúdo para o arquivo de configurações:            

> Alias "/sei" "/opt/sei/web"
> Alias "/sip" "/opt/sip/web"
> Alias "/infra_css" "/opt/infra/infra_css"
> Alias "/infra_js" "/opt/infra/infra_js"
> <Directory />
> AllowOverride None
> Require all denied
> </Directory>
> <Directory ~ "(/opt/sei/web|/opt/sip/web|/opt/infra/infra_css|/opt/infra/infra_js)" >
> AllowOverride None
> Options None
> Require all granted
> </Directory>

Para máquinas que vão executar apenas um dos sistemas (SEI ou SIP) é recomendado editar o arquivo removendo as referências para diretórios do outro sistema. 

# 4 Bases de Dados 

As bases do SEI e do SIP estão disponíveis em 3 formatos: MySQL Enterprise Edition 5.6 (SEI-BD-MySQL-v3.0.0.zip), Microsoft SQL Server 2014 (SEI-BD-SqlServer-v3.0.0.zip) e Oracle 11g (SEI-BD-Oracle-v3.0.0.zip). Após a restauração criar um usuário e senha para acesso e, com um aplicativo cliente do banco abrir a base do SIP e atualizar as tabelas de órgãos e sistemas com a sigla e descrição da instituição:         

> update orgao set sigla='ABC', descricao='Aaa Bbb Ccc' where id_orgao=0;
> update sistema set pagina_inicial='http://[servidor_sip]/sip' where sigla='SIP'; update sistema set pagina_inicial='http://[servidor_sei]/sei/inicializar.php', web_service='http://[servidor_sei]/sei/controlador_ws.php?servico=sip' where sigla='SEI';

Abrir a base do SEI e atualizar a tabela de órgão:       

> update orgao set sigla='ABC', descricao='Aaa Bbb Ccc' where id_orgao=0;

9

A sigla do órgão utilizada na tabela de órgãos do SIP DEVE ser a mesma utilizada na tabela correspondente do SEI. 

OBS: O formato de datas do banco deve estar no padrão “aaaa-mm-dd hh:mm:ss”. 

# 5 Configuração SIP 

Abrir o arquivo /opt/sip/config/ConfiguracaoSip.php e atualizar os parâmetros: 

Sip 

URL http://[servidor_sip]/sip 

Producao true (se o valor for false o sistema exibirá detalhes de erros para o usuário final e não fará cache de javascript/css degradando o desempenho, por isso, para o servidor de produção DEVE ter valor true). 

NumLoginSemCaptcha Opcional (valor padrão 3). Indica quantas vezes a tela de login permitirá que o usuário erre a senha antes de exibir o captcha. 

TempoLimiteValidacaoLogin Opcional (valor padrão 60). Define o tempo em segundos que o SIP aguardará a chamada de validação de login pelo sistema cliente. 

Modulos Opcional. Caminho para módulos de código específicos da instituição. 

PaginaSip 

NomeSistema Usado nos títulos das janelas 

NomeSistemaComplemento Opcional. Texto exibido ao lado do ícone do sistema na barra superior (ex.: “Teste”, “Homologação”, vazio em produção). 

SessaoSip 

SiglaOrgaoSistema Utilizar a mesma sigla dos updates realizados nas tabelas de orgao 

SiglaSistema SIP 

PaginaLogin http://[servidor_sip]/sip/login.php 

SipWsdl http://[servidor_sip]/sip/controlador_ws.php?servico=wsdl 

https true/false - se habilitado então todas as páginas utilizarão o protocolo 

BancoSip 

Servidor [servidor_bd] 

Porta [número da porta de conexão] 

Banco [banco sip] 

Usuario [usuário banco sip] 

Senha [senha banco sip] 

Tipo MySql, SqlServer ou Oracle 

PesquisaCaseInsensitive Opcional (valor padrão false). Indica se o servidor do banco de dados está configurado para não fazer distinção nas pesquisas (LIKE) entre letras maiúsculas e minúsculas o que pode trazer ganho de desempenho. 

> CacheSip

Servidor Endereço do servidor memcache 

Porta Porta do memcache 

Timeout Opcional (valor padrão 1). Tempo em segundos para obter resposta do servidor memcache. 10 

Tempo Opcional (valor padrão 3600). Tempo em segundos que algumas 

informações serão retidas na cache de memória. 

> HostWebService (

ver detalhes de configuração na seção Configuração de Acesso de Web Services )

Replicacao Referências (IP, nome na rede) das máquinas que podem chamar o serviço de replicação de usuários. 

Pesquisa Referências (IP, nome na rede) das máquinas que podem chamar os serviços de pesquisa de dados no SIP. Colocar todas as máquinas que rodam o SEI. 

Autenticacao Referências (IP, nome na rede) das máquinas que podem chamar o serviço de autenticação de usuários do SIP. Colocar todas as máquinas que rodam o SEI. 

InfraMail (ver descrição na seção "Configuracao SEI") 

# 6 Configuração SEI 

Abrir o arquivo /opt/sei/config/ConfiguracaoSEI.php e atualizar os parâmetros: 

SEI 

URL http://[servidor_sei]/sei 

Producao true (se colocar o valor false o sistema exibirá detalhes de erros para o usuário final e não fará cache de javascript/css degradando odesempenho, por isso, para o servidor de produção DEVE ter valor true) 

DigitosDocumento Opcional (valor padrão 7). Informa a quantidade de dígitos para os números de documento. 

NumLoginUsuarioExternoS emCaptcha Opcional (valor padrão 3). Indica quantas vezes a tela de login para usuários externos permitirá que o usuário erre a senha antes de exibir o captcha. 

TamSenhaUsuarioExterno Opcional (valor padrão 8). Indica o número mínimo de caracteres para o cadastramento de senha de usuário externo. O sistema obriga que a senha contenha pelo menos um número e uma letra. 

DebugWebServices Opcional (valor padrão 0). Permite ativar a gravação do processamento dos web services na tabela de log do sistema. Valores disponíveis: 0 - nenhuma gravação 1 - grava apenas os parâmetros recebidos 2 - grava parâmetros e acessos ao banco (pode falhar em caso de erro fatal) 

RepositorioArquivos Indica o local para gravação e consulta de documentos externos, ex.: /sei-nfs/dados. Este diretório costuma ser mapeado em um Storage e NÃO deve ser criado dentro da pasta raiz do apache (normalmente /srv/www/htdocs ou /var/www/html). O usuário do apache deverá ter permissão de escrita neste diretório. 

Modulos Opcional. Caminho para módulos de código específicos da instituição. 

SessaoSEI 

SiglaOrgaoSistema Utilizar a mesma sigla dos updates realizados nas tabelas de orgao 

SiglaSistema SEI 

PaginaLogin http://[servidor_sip]/sip/login.php 

SipWsdl http://[servidor_sip]/sip/controlador_ws.php?servico=wsdl 11 

https true/false - se habilitado então todas as páginas utilizarão o protocolo (ver seção HTTPS) 

PaginaSEI 

NomeSistema Usado nos títulos das janelas 

NomeSistemaComplemento Opcional. Texto exibido ao lado do ícone do sistema na barra superior (ex.: “Teste”, “Homologação”, vazio em produção) 

LogoMenu Opcional. Permite exibir um logo abaixo do menu principal. Deve conter o código HTML correspondente. 

OrgaoTopoJanela Opcional (valor padrão S). Indica qual a descrição do órgão que aparecerá no topo da janela onde: S = órgão do sistema e U = órgão do usuário logado. 

BancoSEI 

Servidor [servidor_bd] 

Porta [número da porta de conexão] 

Banco [banco sei] 

Usuario [usuário banco sei] 

Senha [senha banco sei] 

Tipo MySql, SqlServer ou Oracle 

PesquisaCaseInsensitive Opcional (valor padrão false). Indica se o servidor do banco de dados está configurado para não fazer distinção nas pesquisas (LIKE) entre letras maiúsculas e minúsculas o que pode trazer ganho de desempenho. 

CacheSEI 

Servidor Endereço do servidor memcache 

Porta Porta do memcache 

Timeout Opcional (valor padrão 1). Tempo em segundos para obter resposta do servidor memcache. 

Tempo Opcional (valor padrão 3600). Tempo em segundos que algumas informações serão retidas na cache de memória. 

RH 

CargoFuncao Endereço para o serviço de recuperação de Cargos/Funções para assinatura de documentos (opcional). 

Solr 

Servidor Indica a máquina onde está instalado o mecanismo de indexação, exemplo: http://[servidor_solr]:8080/solr 

CoreProtocolos sei-protocolos 

TempoCommitProtocolos Opcional (valor padrão 300). Tempo máximo em segundos que o Solr levará para indexar os protocolos. 

CoreBasesConhecimento sei-bases-conhecimento 

TempoCommitBasesConhe cimento Opcional (valor padrão 60). Tempo máximo em segundos que o Solr levará para indexar as bases de conhecimento. 

CorePublicacoes sei-publicacoes 

TempoCommitPublicacoes Opcional (valor padrão 60). Tempo máximo em segundos que o Solr levará para indexar as publicações. 

JODConverter 12 

Servidor Esta chave é opcional, caso ela não exista apenas não será possível marcar documentos externos nos formatos OpenOffice (doc, xls, pps, etc.) para geração do PDF da árvore de processo. Nenhum erro será gerado pois o sistema irá bloquear automaticamente a seleção destes documentos. Informar o endereço do serviço,ex: http://[servidor_jod]:8080/converter/service 

HostWebService (ver detalhes de configuração na seção Configuração de Acesso de Web Services )

Edoc [servidor .net] Referências (IP, nome na rede) para as máquinas do repositório de arquivos eDoc (descontinuado) 

Sip [servidor_sip] Indicar todas as referências (IP e nome na rede) da máquina que executa o SIP, para o devido acesso ao serviço de sincronização de usuários/unidades/órgãos. O SIP tentará replicar estes dados para o SEI e caso esta chave não esteja correta será gerado um erro de Acesso Negado. 

Publicacao Referências (IP, nome na rede) dos veículos de publicação externos cadastrados. 

Ouvidoria Referências (IP, nome na rede) da máquina que hospeda o formulário de Ouvidoria personalizado. Se utilizando o formulário de ouvidoria padrão disponibilizado pelo SEI então configurar com as máquinas que rodam o SEI. 

InfraMail 

Tipo 1 – utiliza configuração do Sistema Operacional através do aplicativo sendmail 2 – permite configurar um servidor SMTP (neste caso os campos abaixo deverão ser preenchidos) 

Servidor Servidor SMTP 

Porta Porta SMTP 

Codificacao Codificação para envio da mensagem e anexos: 8bit, 7bit, binary, base64, quoted-printable 

Autenticar true/false - Indica se o servidor SMTP requer autenticação 

Usuário Obrigatório se Autenticar igual true. Usuário para autenticação 

Senha Obrigatório se Autenticar igual true. Senha do usuário para autenticação. 

Seguranca Opcional (valor padrão TLS). Indica se a comunicação entre osistema e o servidor de email deve ser criptografada. Valores possíveis: TLS, SSL e vazio para nenhum. 

MaxDestinatarios Opcional. Número máximo de destinatários permitido pelo servidor de email. O sistema fará uma validação prévia antes de submeter ao servidor. 

MaxTamAnexosMb Opcional. Tamanho máximo dos anexos permitido pelo servidor de email. O sistema fará uma validação prévia antes de submeter ao servidor. 

Protegido Evita envio incorreto de email no ambiente de desenvolvimento, se for preenchido com um ou mais endereços de email então todos os emails enviados terão o destinatário ignorado e substituído por este valor 13 Dominios Opcional. Permite especificar o conjunto de atributos acima individualmente para cada domínio de conta remetente. Se não existir um domínio mapeado então utilizará os atributos gerais da chave InfraMail (em negrito no exemplo):                             

> array( 'Tipo' => '2',
> 'Servidor' => '10.1.6.15',
> 'Porta' => '25',
> 'Codificacao' => '8bit',
> 'MaxDestinatarios' => 20,
> 'MaxTamAnexosMb' => 10,
> 'Seguranca' => 'TLS',
> 'Autenticar' => false,
> 'Usuario' => '',
> 'Senha' => '',
> 'Protegido' => ''
> 'Dominios' => array(
> 'abc.jus.br' => array('Tipo' => '2',
> 'Servidor' => '10.1.3.12',
> 'Porta' => '25',
> 'Codificacao' => '8bit',
> 'MaxDestinatarios' => 25,
> 'MaxTamAnexosMb' => 15,
> 'Seguranca' => 'TLS',
> 'Autenticar' => false,
> 'Usuario' => '',
> 'Senha' => '',
> 'Protegido' => '')
> )
> )

Caso todos os domínios estejam mapeados individualmente então os atributos gerais podem ser omitidos:      

> 'InfraMail' => array(
> 'Dominios' => array(
> 'abc.jus.br' => array(...),
> 'def.gov.br' => array(...)
> )
> )

# 7 Configuração de Acesso aos Web Services 

A comunicação entre o SEI e o SIP ocorre via Web Services. Como estes sistemas costumam estar disponíveis na Web existe uma configuração que permite especificar quais máquinas possuem permissão para chamar os serviços. Esta configuração é feita através da chave HostWebService existente nos arquivos ConfiguracaoSEI.php e ConfiguracaoSip.php. Cada sub-chave de HostWebService representa um conjunto de serviços disponíveis e pode conter mais de um valor pois muitas vezes a máquina de origem (requisitante do serviço) é identificada no PHP pelo IP, outras vezes pelo nome da máquina. Já ocorreram casos em que a identificação ocorria de forma alternada: em uma chamada era o IP e na próxima o nome da máquina. Esta resolução de nome é diretamente influenciada pela configuração do ambiente de rede. A configuração dos valores para as sub-chaves pode ser feita utilizando o caractere curinga “*”, ex.: 14                    

> 'HostWebService' => array( 'Replicacao' => array('10.100.10.5'), //sistema de RH ou deixar vazio '' para nenhum 'Pesquisa' => array('*'), //qualquer máquina (NÃO RECOMENDADO), colocar os nós do SEI 'Autenticacao' => array('10.100.200.*','no*.trf4.jus.br') //IPs enós do SEI )

Será processado apenas um curinga por valor cadastrado, por exemplo, o valor “*.100.50.*” não é válido. Se uma máquina tentar chamar um serviço e não estiver autorizada então um erro de “Acesso Negado” será lançado e gravado na tabela de logs informando qual máquina tentou acesso (menu Infra/Log). 

# 8 Acesso aos Sistemas 

Neste ponto SIP e SEI já estarão configurados e o acesso aos sistemas poderá ser realizado utilizando as URLs abaixo (informar o valor teste para usuário e senha): SIP - http://[servidor_sip]/sip 

SEI - http://[servidor_sei]/sei 

# 9 Integração LDAP/AD 

A autenticação de usuários internos é realizada por meio de integração entre o SIP e os servidores OpenLDAP e/ou ActiveDirectory da instituição. Seguir os passos abaixo para realizar a configuração: 1) logar no SIP com o usuário e senha teste ;2) criar um usuário que será o administrador através do menu "Usuários" (a Sigla deve ser o login de rede do usuário utilizado normalmente); 3) acessar o menu Sistemas/Administradores e através do botão Novo definir o usuário criado como Administrador do Sistema SIP, após repita este passo mas agora para o sistema SEI; 4) acessar o menu Servidores de Autenticação e através do botão Novo cadastrar o servidor de autenticação: 15 

• Informar um nome para o servidor, o tipo (Active Directory ou OpenLDAP), a versão (2 ou 3), o endereço e porta da máquina; 

• Campo Sufixo - a sigla dos usuários cadastrados no SIP deve ser a mesma existente no servidor de autenticação. Se o atributo utilizado para busca no servidor contiver a sigla acrescida de um sufixo comum (normalmente o domínio) então ele pode ser informado no campo Sufixo. Assim a sigla do usuário não precisará conter este sufixo ficando mais curta para digitação/exibição nas telas dos sistemas; 

• Os campos Usuário de Pesquisa e Senha de Pesquisa devem ser preenchidos caso o servidor não permita que conexões anônimas façam pesquisas; 

• Os campos Contexto de Pesquisa, Atributo Filtro e Atributo Retorno serão utilizados para busca da identificação completa do usuário que está tentando autenticação. O valor recuperado será utilizado para validação da senha. Os valores mais comuns para Atributo Filtro são cn ou userPrincipalName e para o Atributo Retorno são distinguishedName ou 

aliasedObjectName . Se estes campos não forem informados o sistema tentará autenticar o usuário mesmo sem ter o contexto completo ao qual ele pertence. Na tela de exemplo se a sigla do usuário for “abc” o sistema tentará recuperar o valor do atributo distinguishedName pesquisando no contexto “ou=TRF4,dc=trf4,dc=jus,dc=br” por “userPrincipalName=abc@trf4.jus.br ”; 

• Preencher os campos Usuário e Senha de Teste e pressionar o botão Testar. ATENÇÃO: em caso de erro serão exibidas todas as senhas envolvidas (de pesquisa e do usuário de teste) .A trilha de processamento feita pelo sistema será exibida na tela permitindo identificar qual foi o problema. Quando a configuração estiver correta será apresentada a mensagem “Autenticação realizada com sucesso.”. Neste caso, se o usuário preencher a senha errada deve exibir um erro “Usuário ou Senha inválida”. Posteriormente, se ocorrer um erro ao autenticar na tela de login do sistema então será exibido apenas o texto “Erro desconhecido validando usuário.” (os detalhes do erro nunca serão exibidos na tela de login porque podem revelar a senha do usuário);  

> •

Após configurar o servidor de autenticação escolha Salvar; 

5) acessar o menu Órgãos/Listar e alterar o cadastro do órgão: 16  

> •

Marcar a opção Autenticar Usuários neste Órgão;  

> •

Clicar na lupa existente no campo “Servidores de Autenticação Associados” e transportar o servidor de autenticação cadastrado. É possível transportar mais de um servidor de autenticação, neste caso, o sistema tentará autenticar na ordem em que eles aparecem na lista (no primeiro que funcionar será assumido que a autenticação foi bem sucedida). 

Se, por algum motivo, a sessão com o SIP for fechada com os parâmetros de autenticação salvos de maneira incorreta então não será mais possível logar novamente para corrigir (dará erro de autenticação). Neste caso será necessário reverter desligando a autenticação do órgão através da execução do comando abaixo na base do SIP:  

> update orgao set sin_autenticar='N' where id_orgao=0;

# 10 Carga e Sincronização de Usuários e Unidades 

A carga inicial de usuários e unidades deve ser feita exclusivamente na base do SIP. Os usuários serão replicados quando ganharem permissão no sistema e as unidades quando forem adicionadas na hierarquia utilizada pelo SEI. Também é possível replicar todos os usuários e unidades executando os agendamentos “replicarTodosUsuariosSEI” e “replicarUnidadesHierarquiaSEI” através do menu Infra/Agendamentos ação “Executar Agendamento”; Verificar no menu Infra/Sequências do SIP o valor atual para as seqüências "usuario" e “unidade”. Utilizar os respectivos IDs como iniciais para geração dos inserts nas tabelas usuario e unidade. Após rodar as inserções acessar novamente a tela de seqüências e atualizar o valor atual para o último gerado.            

> insert into usuario (id_usuario, id_origem, id_orgao, sigla, nome, sin_ativo) values (100000355, '192332453', 0, 'fss', 'Fulano da Silva Soares', 'S');

onde: id_usuario Sequencial iniciando com o valor atual para a seqüência usuario 

id_origem ID do usuário no sistema de origem da instituição. Poderá ser utilizado posteriormente em integrações como no serviço do SEI que busca automaticamente o cargo para assinatura e o serviço do SIP para replicação de permissões. 

id_orgao ID correspondente ao órgão do usuário (ver tabela orgao) 

sigla Sigla do usuário (não deve haver siglas repetidas no mesmo id_orgao) 

nome Nome do usuário 

sin_ativo S

OBS: O SEI possui algumas faixas de valores reservadas na tabela de usuários então o valor atual do seqüencial de usuários no SIP não deve ser reiniciado (deve ser obrigatoriamente maior ou igual a 100000000).          

> insert into unidade (id_unidade, id_origem, id_orgao, sigla, descricao, sin_global, sin_ativo) values (9999, '10981', 0, 'PRES', 'Presidência', 'N', 'S');

onde: 17 id_unidade Sequencial iniciando com o valor atual para a seqüência unidade 

id_origem ID da unidade no sistema de origem da instituição. Poderá ser utilizado posteriormente em integrações como no serviço do SIP para replicação de permissões. 

id_orgao ID correspondente ao órgão da unidade (ver tabela orgao) 

sigla Sigla da unidade (não deve haver siglas repetidas no mesmo id_orgao) 

descricao Descrição da unidade 

sin_global N (atributo reservado) 

sin_ativo S

OBS 1: A unidade de TESTE enviada junto com a base de dados NÃO deve ser excluída porque é utilizada temporariamente pelo SEI em algumas chamadas de Web Services. Se ela for excluída é necessário recriar, adicionar na hierarquia e atualizar o parâmetro ID_UNIDADE_TESTE através do menu Infra/Parâmetros no SEI; 

OBS 2: Em cada órgão é necessário que exista uma unidade "global" (com o campo sin_global=S). A sigla utilizada normalmente é "*". Um usuário com permissão nesta unidade ganhará automaticamente permissão em todas as unidades do respectivo órgão. Ou seja, ao logar no sistema todas as unidades estarão disponíveis para escolha. Esta unidade normalmente é utilizada apenas por usuários da informática ou gestores do sistema. 

É possível também fazer uma carga inicial da hierarquia de unidades. Embora o script seja um pouco mais complexo devido à necessidade de tratamento das precedências de inserção nas relações pai/filho.           

> insert into rel_hierarquia_unidade (id_hierarquia, id_unidade, id_hierarquia_pai, id_unidade_pai, dta_inicio, dta_fim, sin_ativo) values (100000018, 999, 100000018, 222, '2014-03-10', null, 'S');

onde: id_hierarquia ID da hierarquia associada com o sistema SEI (ver campo id_hierarquia na tabela sistema) 

id_unidade ID da unidade que está sendo adicionada na hierarquia 

id_hierarquia_pai ID da hierarquia associada com o sistema SEI (passar null se a unidade que está sendo adicionada for raiz) 

id_unidade_pai ID da unidade hierarquicamente superior (passar null se a unidade que está sendo adicionada for raiz) 

dta_inicio Data inicial de uso da unidade 

dta_fim Data final de uso da unidade (passar null se não tiver um prazo) 

sin_ativo S

O SIP possui um Web Service para replicação de usuários, ver detalhes no documento SEI-Web-Services-v3.0.0.pdf seção Serviços Disponibilizados pelo SIP. É possível utilizar este serviço em conjunto com uma execução diária do agendamento “replicarTodosUsuariosSEI” para manter o sincronismo entre todos os usuários do RH e os usuários disponíveis no SEI. 

OBS: O agendamento replicarTodosUsuariosSEI considera apenas usuários com o campo id_origem da tabela usuario preenchido. 18 

# 11 Tabela de Parâmetros SIP 

As configurações da tabela de parâmetros podem ser realizadas através do menu Infra/Parâmetros (o usuário deve ser administrador do sistema SIP – menu Sistemas/Administradores): 

EMAIL_SISTEMA naoresponder@..... (endereço utilizado por mensagens enviadas pelo sistema) 

EMAIL_ADMINISTRADOR Endereço para envio de emails informando erro em agendamentos de tarefas do sistema (mais de um email pode ser informado utilizando ponto e vírgula como separador) 

ID_SISTEMA_SIP Valor do campo sistema.id_sistema referente ao sistema SIP 

ID_USUARIO_SIP Valor do campo usuario.id_usuario referente ao usuário SIP 

ID_PERFIL_SIP_ADMINISTRADOR_SIP ID_PERFIL_SIP_ADMINISTRADOR_SISTEMA ID_PERFIL_SIP_BASICO ID_PERFIL_SIP_COORDENADOR_PERFIL ID_PERFIL_SIP_COORDENADOR_UNIDADE Apontamentos para os perfis reservados do SIP 

SIP_VERSAO Indica a versão instalada 

OBS: o sistema poderá automaticamente alterar/adicionar parâmetros nesta tabela. 

# 12 Tabela de Parâmetros SEI 

As configurações da tabela de parâmetros podem ser realizadas através do menu Infra/Parâmetros do SEI (necessária permissão no perfil Informática): 

ID_MODELO_INTERNO_BASE_CONH ECIMENTO Modelo de documento utilizado pelo editor web para geração de Bases de Conhecimento 

ID_SERIE_EMAIL ID do tipo de documento email (valor serie.id_serie correspondente) 

ID_SERIE_OUVIDORIA ID do tipo de documento ouvidoria (valor serie.id_serie correspondente) 

ID_UNIDADE_TESTE Identificador da unidade de teste do sistema (unidade.id_unidade). Esta unidade deve existir pois é utilizada temporariamente em algumas chamadas de Web Services. 

SEI_ACESSO_FORMULARIO_OUVIDO RIA 0 - os formulários de contato terão as mesmas regras de acesso que qualquer outro documento 1 - os formulários de contato somente poderão vistos na unidade de ouvidoria onde foram gerados 

SEI_EMAIL_ADMINISTRADOR Endereço para envio de emails informando erro em agendamentos de tarefas do sistema (mais de um email pode ser informado utilizando vírgula como separador) 

SEI_EMAIL_SISTEMA naoresponder@..... (endereço utilizado por mensagens enviadas pelo sistema) 

SEI_HABILITAR_AUTENTICACAO_D OCUMENTO_EXTERNO 0 - desabilitado 1 - habilitado somente para unidades de protocolo 2 - habilitado para todos os usuários 

SEI_HABILITAR_GRAU_SIGILO 0 – desabilitado 1 – opcional 2 – obrigatório 

SEI_HABILITAR_HIPOTESE_LEGAL 0 – desabilitado 1 – opcional 2 – obrigatório 19 

SEI_HABILITAR_MOVER_DOCUMEN TO 0 - desabilitado 1 - habilitado somente para unidades de protocolo 2 - habilitado para todos os usuários 3 - habilitado somente para documentos externos incluídos por unidades de protocolo 4 - equivalente as opções 1 e 3, unidades de protocolo podem mover qualquer documento externo e as demais unidades apenas documentos incluídos por unidades de protocolo 

SEI_HABILITAR_NUMERO_PROCESS O_INFORMADO Ao gerar um processo exibe campo para digitação do número e da data de autuação: 0 - desabilitado 1 - habilitado somente para unidades de protocolo 2 - habilitado para todos os usuários 

SEI_HABILITAR_VALIDACAO_CPF_C ERTIFICADO_DIGITAL 0 – desabilitado 1 – habilitado (o CPF do certificado deverá ser igual ao do usuário assinante) 

SEI_HABILITAR_VALIDACAO_EXTE NSAO_ARQUIVOS 0 – desabilitado 1 – habilitado (somente serão aceitos arquivos contendo as extensões cadastradas através do menu Administração/Extensões de Arquivos Permitidas) 

SEI_ID_SISTEMA Valor do campo sistema.id_sistema referente ao sistema SEI na base de dados do SIP 

SEI_MASCARA_ASSUNTO Para montagem da máscara podem ser utilizados os caracteres: # - número, A - letra maiúscula a - letra minúscula L - letras maiúsculas ou minúsculas Ex.: ##.##.## 

SEI_MASCARA_NUMERO_PROCESSO _INFORMADO Opcional. Para montagem da máscara podem ser utilizados os caracteres: # - número, A - letra maiúscula a - letra minúscula L - letras maiúsculas ou minúsculas Ex.: ####.######.## 

SEI_MAX_TAM_MENSAGEM_OUVID ORIA 2000 (permite limitar o número de caracteres da mensagem de texto no formulário de ouvidoria) 

SEI_MSG_AVISO_CADASTRO_USUA RIO_EXTERNO Permite exibir um aviso para os usuários externos antes que eles efetuem o cadastro no sistema. Se este campo estiver vazio nenhuma mensagem será apresentada e o usuário será direcionado diretamente para o formulário de cadastro. O conteúdo da mensagem pode estar no formato texto ou HTML. 

SEI_MSG_FORMULARIO_OUVIDORIA Permite exibir um texto no topo da página do formulário de ouvidoria. O conteúdo da mensagem pode estar no formato texto ou HTML. 

SEI_NUM_FATOR_DOWNLOAD_AUT OMATICO Opcional. Permite limitar o download automático de arquivos externos de acordo com a velocidade de transferência de dados do usuário. Se a velocidade do usuário for 150kb/s e o fator for configurado com 5 então para arquivos maiores que 750kb (150 x 5) será exibido um link para o usuário (ao invés de iniciar automaticamente o download). As velocidades de transferência dos usuários podem ser consultadas através do menu Infra/Velocidades de Transferência de Dados. OBS: A velocidade somente será atualizada quando o usuário visualizar um documento externo maior que 256kb. 

SEI_NUM_MAX_DOCS_PASTA 20 - informa o número de documentos para agrupamento em pastas na árvore de processo. Deixar vazio para não realizar agrupamento. 20 

SEI_NUM_PAGINACAO_CONTROLE_ PROCESSOS 100 - informa o número de processos para realizar paginação na tela de Controle de Processos. O número informado é aplicado individualmente nas colunas Recebidos e Gerados então se for deixado o valor padrão 100 serão exibidos na tela até 200 processos (100 em cada coluna). Deixar vazio para não realizar paginação. 

SEI_SUFIXO_EMAIL .jus.br - sufixo adicionado em emails enviados pelo sistema, corresponde ao valor da variável @sufixo_email@ referenciada no cadastro de e-mails do sistema (menu Administração/E-mails do Sistema) 

SEI_TAM_MB_DOC_EXTERNO 200 (valor em Mb), é necessário também configurar no php.ini as variáveis: post_max_size 256M upload_max_filesize 200M 

SEI_TIPO_ASSINATURA_INTERNA Permite controlar as opções exibidas para assinatura interna de documentos: 1 - login/senha e certificado digital 2 - somente login/senha 3 - somente certificado/digital 

SEI_TIPO_AUTENTICACAO_INTERNA Permite controlar as opções exibidas para autenticação interna de documentos: 1 - login/senha e certificado digital 2 - somente login/senha 3 - somente certificado/digital 

SEI_WS_NUM_MAX_DOCS 5 (número máximo de documentos que podem ser gerados simultaneamente em um processo através da API de Web Services do SEI) 

OBS: o sistema poderá automaticamente alterar/adicionar parâmetros nesta tabela. 

# 13 Agendamentos 

Os sistemas possuem alguns agendamentos de tarefas para manutenção. No SEI (menu Infra/Agendamentos) as tarefas agendadas são: (1) remoção de dados temporários de estatísticas (2) remoção de dados temporários de auditoria (3) remoção de arquivos associados com documentos externos excluídos (4) remoção de arquivos criados pelo serviço adicionarArquivo e não utilizados a mais de 24 horas (5) envio de comando de otimização de índices para o Solr (6) agendamento de teste para auxílio na configuração (7) confirmação de publicações do veículo interno E no SIP (menu Infra/Agendamentos): (1) remoção de dados temporários de login (2) agendamento de teste para auxílio na configuração (3) replicação de todos os usuários para o SEI (desativado por padrão) (4) replicação de todas as unidades da hierarquia para o SEI (desativado por padrão) Se necessário, os horários de execução podem ser alterados através do menu Infra/Agendamentos evitando conflito com o horário de backup; É necessário criar agendamentos nos servidores do SEI e do SIP para execução de hora em hora dos arquivos /opt/sei/scripts/AgendamentoTarefaSEI.php e /opt/sip/scripts/AgendamentoTarefaSip.php. Exemplo crontab Linux: 21                       

> 00 ****root /usr/bin/php -c /etc/php.ini /opt/sei/scripts/AgendamentoTarefaSEI.php 2>&1 >> /root/infra_agendamento_sei.log 00 ****root /usr/bin/php -c /etc/php.ini /opt/sip/scripts/AgendamentoTarefaSip.php 2>&1 >> /root/infra_agendamento_sip.log

As tarefas agendadas nos sistemas podem ser visualizadas através do menu Infra/Agendamentos. É possível disparar um agendamento manualmente através da ação “Executar Agendamento”. Todos os agendamentos, quando executados, gravam um registro na tabela de logs (menu Infra/Log). Caso ocorra um erro os detalhes serão gravados no log e um email será enviado para os endereços cadastrados no parâmetro EMAIL_ADMINISTRADOR (menu Infra/Parâmetros). Em ambos os sistemas serão configurados agendamentos de teste. As únicas operações realizadas por estes agendamentos são a gravação de um registro no log e o envio de um email para os administradores. Após o funcionamento automático destes agendamentos (programados para executar de hora em hora) eles podem ser desativados através da ação “Desativar Agendamento” (menu Infra/Agendamentos no SEI e SIP). 

# 14 Scripts 

O diretório sei/scripts contém arquivos que podem ser executados diretamente em um console sem a necessidade de login. Por isso é muito importante que este diretório não esteja localizado abaixo do DocumentRoot do apache (conforme orientação constante na seção Código Fonte). Abaixo uma descrição dos scripts disponíveis: 

AgendamentoTarefaSEI.php Utilizado para executar os agendamentos diários (ver seção Agendamentos) 

atualizar_versao.php Deve ser utilizado apenas quando a instituição estiver atualizando uma versão do sistema 

atualizar_sequencias.php Refaz o sincronismo entre as tabelas e suas respectivas seqüencias de controle. Pode ser necessário, por exemplo, se ao restaurar um backup os seqüenciais foram reiniciados ou apresentam inconsistência provocando erros de chave primária duplicada. 

indexacao_protocolos_completa.php [dd/mm/aaaa] *

Indexação de processos/documentos por meio do console. A indexação começará pelos protocolos mais recentes. O parâmetro data é opcional e indica a data a partir da qual deverá ser iniciada a indexação sendo útil para reiniciar o procedimento após algum imprevisto. Este procedimento, dependendo do número de protocolos, poderá levar dias. 

indexacao_bases_conhecimento.php * Permite disparar a indexação de bases de conhecimento por meio do console. 

indexacao_publicacoes.php * Permite disparar a indexação de publicações por meio do console. 

indexacao_parcial.php [dd/mm/aaaa hh:mm] [dd/mm/aaaa hh:mm] *

Este procedimento verifica tudo que foi alterado em determinado período atualizando os índices de pesquisa (alteração em níveis de acesso, protocolos gerados, protocolos excluídos, publicações,...). Os dois parâmetros são obrigatórios indicando a data/hora de início e a data/hora final. Deve ser utilizado após o servidor de indexação ficar indisponível temporariamente. 

indexacao_processo.php [protocolo] * Força a indexação do processo informado com seus documentos. 

indexacao_controle_interno.php * Realiza a indexação de todos os protocolos afetados pelos Critérios de Controle Interno definidos (menu Administração/Critérios de Controle Interno). Deve ser utilizado caso ocorra algum erro no cadastro/alteração de critérios. 

verificacao_repositorio_arquivos.php Lê todo o conteúdo do repositório de arquivos verificando se houve alteração no hash de conteúdo registrado na base de dados. Esta operação pode ser demorada e recomenda-se que seja executada eventualmente em dias com uso menor do sistema como sábados ou domingos. 22          

> importacao_pctt_abril_2016.php Realiza carga do Plano de Classificação e Tabela de Temporalidade dos Documentos Administrativos da Justiça Federal (versão abril/2016). Será criada uma tabela de assuntos com o nome "PCTT abril/2016".
> migracao_edoc.php [dd/mm/aaaa] Permite importar os dados do antigo editor eDoc/Word para dentro do SEI. O parâmetro data é opcional e indica a data dos documentos a partir da qual deverá ser iniciada a migração. Não é necessário parar o sistema para executar este script. Após a finalização o servidor eDoc poderá ser desativado. É necessário criar previamente no arquivo ConfiguracaoSEI.php uma chave BancoEdoc com os dados da conexão direta a base de dados:
> 'BancoEdoc' => array( 'Servidor' => '', 'Porta' => '', 'Banco' => '', 'Usuario' => '', 'Senha' => '' ),
> *Estas operações também podem ser realizadas por meio do menu Infra/Indexação do SEI (embora possam ocorrer problemas de timeout do navegador).

O SIP também possui um diretório sip/scripts mas com apenas dois arquivos AgendamentoTarefaSip.php e atualizar_versao_sei.php. Ambos são aplicados nas mesmas situações dos equivalentes existentes no SEI. 

# 15 Backup 

O backup dos dados deve ser feito em dois passos: 1º) realizar o backup das bases de dados (SEI e SIP); 2º) realizar o backup do diretório de arquivos externos (valor informado no arquivo ConfiguracaoSEI.php – chave SEI/RepositorioArquivos). IMPORTANTE: - o backup do diretório somente deve iniciar APÓS o término do backup das bases de dados; - para restaurar um backup devem ser utilizados os backups das bases e do diretório realizados no mesmo dia (ou seja, todos os backups devem ser vistos como um só). 

# 16 Auditoria 

O SEI e o SIP armazenam os dados de auditoria em uma tabela isolada chamada infra_auditoria que pode ser consultada por meio do menu Infra/Auditoria. Entretanto no SEI, para evitar redundância de informações e tempo de processamento desnecessário, a maioria das operações que são registradas no histórico do processo não são registradas na auditoria. Esta tabela também pode ser utilizada para a recuperação do conteúdo de documentos internos cancelados ou excluídos. Para isso é necessário informar na tela Infra/Auditoria: Recurso = [documento_cancelar ou documento_excluir] Período = [filtrar preferencialmente pelo dia do evento] Operação = [informar os 7 dígitos do número do documento SEI] No resultado será possível selecionar o conteúdo HTML associado com o documento no momento do cancelamento ou exclusão. 23 

# 17 Tabelas de Log 

As tabelas de log (menu Infra/Log) do SEI e do SIP não devem conter registros de erro. Registros de erro nestas tabelas indicam algum problema de configuração ou do sistema, sendo assim, recomendamos o monitoramento e a limpeza freqüentes destes registros. A limpeza pode ser feita diretamente pela interface do sistema, ou, no caso de uma quantidade muito grande de registros, através do comando:   

> delete from infra_log;

# 18 HTTPS 

Após instalação dos certificados nos servidores é possível ativar/desativar o HTTPS via arquivo de configuração. Para o SIP alterar a chave SessaoSip/https no arquivo ConfiguracaoSip.php e para o SEI mudar o valor da chave SessaoSEI/https no arquivo ConfiguracaoSEI.php. Com estas alterações todas as páginas nos dois sistemas utilizarão HTTPS. Alterar o apontamento para a página inicial do SEI no cadastro do sistema existente no SIP. Assim após a autenticação o SIP já redirecionará para o SEI usando HTTPS. Para alterar o apontamento acessar no SIP o menu Sistemas/Listar, abrir a tela de alteração do sistema SEI e modificar o campo “Página Inicial” para: 

> https ://[servidor_sei]/sei/inicializar.php

Opcionalmente, para que a comunicação SIP → SEI, via Web Services, utilize HTTPS é necessário mudar também o campo “Web Service” existente nesta tela para: 

> https ://[servidor_sei]/sei/controlador_ws.php?servico=sip

E para que a comunicação SEI → SIP, via Web Services, utilize HTTPS é necessário mudar no arquivo ConfiguracaoSEI.php o valor da chave SessaoSEI/SipWsdl para: 

> https ://[servidor_sip]/sip/controlador_ws.php?servico=wsdl

Em ambos os casos é necessário instalar nos servidores as cadeias de certificados utilizadas. Um teste simples pode ser feito no linux realizando um wget do WSDL no prompt dos servidores:       

> Servidor SEI> wget https://[servidor_sip]/sip/controlador_ws.php?servico=wsdl Servidor SIP> wget https://[servidor_sei]/sei/controlador_ws.php?servico=sip

Avisamos que na comunicação SEI → SIP, via Web Services, ocorre envio da senha do usuário (por exemplo, na assinatura de documento por sigla/senha). A senha não trafega totalmente aberta mas o HTTPS é muito bem vindo neste caso. 

Para que um sistema cliente utilize HTTPS nas chamadas de serviços do SEI basta invocar o WSDL utilizando o prefixo “https”. Para detalhes dos serviços disponíveis ver documento SEI-Web-Services-v3.0.0.pdf. 

Se utilizando uma arquitetura com balanceador e nós então é necessário criar uma configuração separada no balanceador para HTTPS. Caso contrário, a comunicação entre o cliente e o balanceador 24 

será HTTPS, mas entre o balanceador e os nós será HTTP. Com isso o sistema não conseguirá identificar que a comunicação com o cliente já utiliza HTTPS e tentará redirecionar provocando um “loop infinito”. 

# 19 Formulário de Ouvidoria 

O SEI possui um formulário de ouvidoria padrão que pode ser acessado através do endereço: 

> http://[servidor_sei]/sei/controlador_externo.php?acao=ouvidoria&id_orgao_acesso_externo=0

O parâmetro id_orgao_acesso_externo indica em qual órgão será gerado o processo (verificar campo id_orgao da tabela orgao). Também é necessário configurar a chave HostWebServices/Ouvidoria do arquivo ConfiguracaoSEI.php com o endereço dos servidores habilitados para chamada do serviço (normalmente os nós de aplicação do SEI). 

No SEI é permitida apenas uma unidade de ouvidoria por órgão que pode ser configurada através do menu Administração/Unidades/Listar – ação Alterar Unidade – opção “Unidade de ouvidoria”. Os tipos de processo exibidos são aqueles marcados como de ouvidoria através do menu Administração/Tipos de Processo/Listar – ação Alterar Tipo de Processo – opção “Exclusivo da Ouvidoria”. 

É necessário criar também um tipo de documento denominado "Ouvidoria" e associar o seu identificador interno por meio do parâmetro ID_SERIE_OUVIDORIA (ver seção Tabela de Parâmetros SEI). O identificador interno é exibido na coluna ID da lista de Tipos de Documento (menu Administração/Tipos de Documento/Listar). Opcionalmente através do parâmetro SEI_MSG_FORMULARIO_OUVIDORIA (menu Infra/Parâmetros) é possível adicionar um texto no formulário que será posicionado abaixo do título "Ouvidoria". É possível criar outro formulário com a identidade visual da instituição pois os dados montados na página (Estado, Cidade e Tipo) bem como a geração do processo são realizados através de Web Services. Neste caso os servidores que hospedarem este formulário precisarão constar na chave HostWebServices/Ouvidoria do arquivo ConfiguracaoSEI.php. 25 

# 20 Login de Usuários Externos 

Usuários externos podem acessar o SEI através do endereço: 

> http://[servidor_sei]/sei/controlador_externo.php?acao=usuario_externo_logar&id_orgao_acess o_externo=0

O parâmetro id_orgao_acesso_externo indica qual órgão que será exibido no topo da tela (verificar campo id_orgao da tabela orgao). Opcionalmente através do parâmetro SEI_MSG_AVISO_CADASTRO_USUARIO_EXTERNO (menu Infra/Parâmetros) é possível exibir uma tela com um texto informativo após o usuário clicar no link “Clique aqui se você ainda não está cadastrado”. 

# 21 Pesquisa de Publicações 

A tela para pesquisa de publicações existente no SEI pode ser acessada através do endereço: 

> http://[servidor_sei]/sei/publicacoes/controlador_publicacoes.php?acao=publicacao_pesquisa r&id_orgao_publicacao=0

O parâmetro id_orgao_publicacao indica qual órgão será exibido no topo da tela e também qual caixa de seleção, disponível no campo “Órgão”, vira marcada como padrão (verificar coluna ID por meio do menu Administração/Órgãos). O campo “Órgão” somente será exibido se existir mais de um órgão configurado para publicação. 26 

# 22 Conferência de Documentos 

A tela para conferência de documentos no SEI pode ser acessada através do endereço:  

> http:// [servidor_sei]/sei/controlador_externo.php?acao=documento_conferir&id_orgao_aces so_externo=0

O parâmetro id_orgao_acesso_externo indica qual órgão será exibido no topo da tela. 

# 23 Configuração Máquinas Cliente 

• O sistema suporta os navegadores Internet Explorer 9+, Chrome 8+, Firefox 10+ ou Safari 3+; 

• Em estações Windows, apesar do suporte ao Internet Explorer, é recomendado o uso do Firefox ou Chrome pois o desempenho no processamento de javascript destes navegadores éconsideravelmente superior (principalmente no uso do editor web); 

• Configurar o desbloqueio de pop-ups;  

> •

Corretor Ortográfico: No sistema é possível configurar o uso do corretor WebSpellChecker (versão licenciada ou de avaliação) ou outro instalado no navegador do usuário (opção Nativo do Navegador) por meio do menu Administração/Órgãos – ação Alterar Órgão. Se a opção Nativo do Navegador for utilizada então o editor web tentará utilizar o corretor instalado no navegador mas não há garantias de que funcione com todos os corretores. Se funcionar o acesso ao menu do corretor contendo as sugestões de palavras poderá ser feito usando "CTRL + botão direito do mouse" sobre a palavra grifada; 

• Atalhos para endereços do SEI no Internet Explorer devem utilizar a versão 32 bits (Arquivos de Programas x86); 

• Em computadores antigos o uso do editor no Internet Explorer pode resultar na mensagem: 

"Um script desta página está tornando o Internet Explorer lento. Se ele continuar sendo executado, seu computador poderá parar de responder. Deseja anular o script?" 

Se o usuário anular o script o editor ficará em estado inconsistente e o conteúdo poderá ser perdido. A Microsoft permite desabilitar esta mensagem seguindo os procedimentos descritos em 

http://support.microsoft.com/kb/175500 .27 

• Assinatura Digital: 

• a applet de assinatura do SEI funciona com certificados padrão ICP-Brasil; 

• o navegador Chrome deixou de suportar applets e não pode ser utilizado para realizar assinatura digital; 

• para acesso ao certificado em smart card ou token é necessária a instalação no computador dos drivers adequados (os detalhes de instalação variam de fabricante para fabricante). O Java Runtime 1.8 ou superior também deverá ser instalado. 

• no java é necessário (1) rebaixar o nível de segurança para Médio (não recomendado) ou (2) instalar o certificado da Autoridade Certificadora SERASA utilizado na assinatura do código da applet (opção mais segura e recomendada). Uma possibilidade é automatizar a inserção do certificado da Autoridade Certificadora SERASA no Painel de Controle Java; 

• A tela de assinatura de documentos contém um link para uma página com instruções de configuração detalhadas. 

# 24 Solr 

O Solr é um servidor de buscas Open Source que possibilita a pesquisa no conteúdo de documentos externos (pdf, doc, xls,...). Definições:          

> /tmp – diretório temporário no servidor /opt/solr - diretório de instalação do solr /dados -diretório que conterá os índices

Todos os passos abaixo devem ser executados como root no servidor. 

1 - Instalar na máquina destinada ao Solr o Java 1.8; 

2 - Baixar o arquivo solr-6.1.0.tgz no diretório /tmp 

3 - Copiar os arquivos de configuração localizados no diretório de fontes do SEI sei/config/solr 

para o diretório /tmp: 

> sei-protocolos-config.xml sei-protocolos-schema.xml sei-bases-conhecimento-config.xml sei-bases-conhecimento-schema.xml sei-publicacoes-config.xml sei-publicacoes-schema.xml log4j.properties sei-solr-6.1.0.sh

4 - Executar o arquivo /tmp/sei-solr-6.1.0.sh (verificar se não ocorreram erros) 

5 - Iniciar o serviço do Solr (assumindo um servidor com 8Gb de memória):     

> /opt/solr/bin/solr start -p 8983 -a "-Xms6144m -Xmx6144m"

Já deve ser possível acessar o console pelo navegador em http://[servidor_solr]:8983/solr: 28 

Não devem existir erros na tela de log: 

6 - Usando um navegador criar os índices no Solr executando os 3 comandos abaixo em seqüência: 

Copiar os links previamente em um editor de texto para eliminar eventuais espaços em branco e quebras de linha. Substituir também o trecho " [servidor_solr] " pelo nome do servidor SOLR que está sendo configurado. 

http://[servidor_solr]:8983/solr/admin/cores?action=CREATE&name=sei-protocolos& instanceDir=/dados/sei-protocolos&config=sei-protocolos-config. xml&schema=sei-protocolos-schema.xml&dataDir=/dados/sei-protocolos/conteudo http://[servidor_solr]:8983/solr/admin/cores?action=CREATE&name=sei-bases-conhecimento& instanceDir=/dados/sei-bases-conhecimento&config=sei-bases-conhecimento-config. xml&schema=sei-bases-conhecimento-schema.xml&dataDir=/dados/sei-bases-conhecimento/ conteudo http://[servidor_solr]:8983/solr/admin/cores?action=CREATE&name=sei-publicacoes& instanceDir=/dados/sei-publicacoes&config=sei-publicacoes-config. xml&schema=sei-publicacoes-schema.xml&dataDir=/dados/sei-publicacoes/conteudo 29 

7 - Neste ponto os novos índices já devem estar visíveis no Solr na caixa “Core Selector”: 

Não devem existir erros na tela de log: 

8 - Configurar as chaves de pesquisa no arquivo ConfiguracaoSEI.php para ativar o uso do Solr: 

'Solr' => array('Servidor' => 'http://[servidor_solr]:8983/solr', 'CoreProtocolos' => 'sei-protocolos', 'TempoCommitProtocolos' => 300, 'CoreBasesConhecimento' => 'sei-bases-conhecimento', 'TempoCommitBasesConhecimento' => 60, 'CorePublicacoes' => 'sei-publicacoes', 'TempoCommitPublicacoes' => 60), 

As chaves TempoCommit* são opcionais e indicam o tempo máximo em segundos que o Solr deve levar para refletir as alterações nos índices (valores muito baixos podem ocasionar sobrecarga no servidor). Para mais detalhes consultar seção "Configuração SEI". 

9 - Limitar os IPs de acesso ao servidor de indexação editando o arquivo /opt/solr/server/etc/jetty.xml (trecho em negrito):  

> <!-- =========================================================== --> <!-- Set handler Collection Structure --> <!-- =========================================================== --> <Set name="handler"> <New id="Handlers" class="org.eclipse.jetty.server.handler.HandlerCollection"> <Set name="handlers">

30 

<Array type="org.eclipse.jetty.server.Handler"> 

<Item> <New class="org.eclipse.jetty.server.handler.IPAccessHandler"> <Call name="addWhite"> <Arg>127.0.0.1</Arg> <!-- Loopback interface --> </Call> <Call name="addWhite"> <Arg>10.1.3.47</Arg> <!-- IP do proprio servidor Solr --> </Call> <Call name="addWhite"> <Arg>10.100.57.242</Arg> <!-- IP da maquina do usuario administrador --> </Call> <Call name="addWhite"> <Arg>10.1.3.171,178</Arg> <!-- Faixa de IPs dos nós de aplicação SEI --> </Call> <Set name="handler"> <New id="Contexts" class="org.eclipse.jetty.server.handler.ContextHandlerCollection"/> </Set> </New> </Item> 

...outras ocorrências de <Item> </Item> já existentes no arquivo... </Array> </Set> </New> </Set> 

10 - Se já existirem dados na base disparar a indexação de protocolos, bases de conhecimento e publicações (ver detalhes na seção Scripts): 

/usr/bin/php -c /etc/php.ini /opt/sei/scripts/indexacao_protocolos_completa.php /usr/bin/php -c /etc/php.ini /opt/sei/scripts/indexacao_publicacoes.php /usr/bin/php -c /etc/php.ini /opt/sei/scripts/indexacao_bases_conhecimento.php 

Se utilizando um ambiente com várias máquinas virtuais é aconselhável retirar uma máquina do balanceador e disparar o processo desta máquina isoladamente. Desta forma, o desempenho não será comprometido para usuários finais que porventura estejam compartilhando o mesmo nó do processo de indexação. Caso, no futuro, seja preciso reindexar todos os dados é aconselhável limpar antes os índices usando os comandos abaixo: 

http://[servidor_solr]:8983/solr/sei-protocolos/update?stream.body=<delete><query>*:*</query></delete>&commit=true http://[servidor_solr]:8983/solr/sei-bases-conhecimento/update?stream.body=<delete><query>*:*</query></delete>&commit=true 

http://[servidor_solr]:8983/solr/sei-publicacoes/update?stream.body=<delete><query>*:*</query></delete>&commit=true 

# 25 Problemas Conhecidos e Soluções 

1 - Ao acessar o sistema ao invés de montar a página é exibido o código PHP Verificar no php.ini se a diretiva "short_open_tag" esta ativada (ver seção Servidores/Configuração do PHP). 31 

2 - Não carrega o CSS e o Javascript nas telas 

Verificar se o arquivo sei.conf está localizado no diretório correto (ver seção Código Fonte). Adicionar os diretórios infra_css e infra_js no proxy reverso. Se os mapeamentos estiverem corretos as URLs abaixo não devem gerar erro de acesso: 

> http://[servidor]/infra_css/infra-global-esquema.css http://[servidor]/infra_js/InfraUtil.js

3 - Monta a tela de login mas ao tentar logar exibe o erro "Erro acessando arquivo WSDL." 

Verificar nos arquivos /opt/sip/config/ConfiguracaoSip.php e /opt/sei/config/ConfiguracaoSEI.php se o apontamento na chave "SipWsdl" está correto. Outra possibilidade é que o servidor não esteja conseguindo se auto-referenciar (verificar configuração do proxy reverso). 

4 - Acentuação errada nas telas 

Verificar se o charset do apache e se o valor da chave default_charset do php.ini estão configurados como ISO-8859-1. Se for base MySql executar nas bases SEI e SIP o comando:      

> SHOW VARIABLES WHERE VARIABLE_NAME IN ('character_set_client', 'character_set_server', 'character_set_database', 'character_set_connection');

Os valores retornados para as variáveis devem ser todos " latin ". 

5 - A página de login é montada mas ao tentar logar aparece "Erro efetuando login...." Verificar se o servidor memcache está funcionando corretamente (porta padrão 11211) :                              

> [root@seicache root]# netstat -an | grep 11211 tcp 00 0.0.0.0:11211 0.0.0.0:* LISTEN tcp6 00 :::11211 :::* LISTEN udp 00 0.0.0.0:11211 0.0.0.0:* udp6 00 :::11211 :::* Verificar se as chaves CacheSEI eCacheSip estão configuradas corretamente (ver seções “Configuração SEI” e “Configuração Sip”).

Verificar se o SELinux está habilitado e bloqueando o acesso ao memcache. 

6 - A página de login é montada mas ao tentar logar aparece o erro "SoapFault exception: [Client] looks like we got no XML document..." 

Falta alguma dependência do PHP para a execução de Web Services. Verificar log de erros do apache e conteúdo das tabelas infra_log das bases SEI e SIP. Verificar o servidor memcache (item 5 desta seção). 

7 - No SIP ao cadastrar permissão para um usuário ou adicionar uma unidade na hierarquia aparece a mensagem “Erro: Falha na chamada ao Web Service do sistema SEI.” 

Acessar o menu Infra/Log do SEI e verificar se tem algum erro “ SoapFault exception: [SOAP-ENV:Client] Acesso negado. ” registrado. Se existir significa que o SEI bloqueou a tentativa de replicação de dados do SIP. Neste caso obter o nome da máquina do SIP que tentou o acesso 32 verificando o próximo registro no log do SEI (ele deve existir informando a identificação da máquina). Adicionar o nome encontrado na chave “HostWebService/Sip” do arquivo ConfiguracaoSEI.php.  

> 8 -

No SEI ao gerar um documento é lançado "Erro obtendo hierarquia da unidade." 

Acessar o menu Infra/Log do SIP e verificar se tem algum erro “ SoapFault exception: [SOAP-ENV:Client] Acesso negado. ” registrado. Se existir significa que o SIP bloqueou a tentativa de consulta da hierarquia pelo SEI. Neste caso obter o nome da máquina do SEI que tentou o acesso verificando o próximo registro no log do SIP (ele deve existir informando a identificação da máquina). Adicionar o nome encontrado na chave “HostWebService/Pesquisa” do arquivo ConfiguracaoSip.php .  

> 9 -

No SEI em telas que solicitam a senha do usuário (como na assinatura de documento) é lançado 

"Erro autenticando usuário. "

Acessar o menu Infra/Log do SIP e verificar se tem algum erro “ SoapFault exception: [SOAP-ENV:Client] Acesso negado. ” registrado. Se existir significa que o SIP bloqueou a tentativa de autenticação solicitada pelo SEI. Neste caso obter o nome da máquina do SEI que tentou o acesso verificando o próximo registro no log do SIP (ele deve existir informando a identificação da máquina). Adicionar o nome encontrado na chave “HostWebService/Autenticacao” do arquivo ConfiguracaoSip.php .  

> 10 -

No SEI a troca de unidade de trabalho não funciona voltando para a unidade anterior após recarregar a tela Consultar no cadastro do sistema SEI no SIP (menu Sistemas/Listar) se a referência para a página inicial está sendo realizada pelo IP ou pelo nome da máquina. Após verificar se no arquivo ConfiguracaoSEI.php a chave SEI/URL está utilizando a mesma denominação.  

> 11 -

Pesquisando no Solr nada é retornado 

Verificar se a servidor SEI utilizado está liberado na rede para acesso ao Solr. Verificar se foi realizada a limitação de IPs de acesso no arquivo /opt/solr/server/etc/jetty.xml (ver seção Solr) e se o IP do servidor SEI consta na lista.  

> 12 -

PDF não é gerado Verificar se o usuário do apache tem permissão de execução sobre o arquivo wkhtmltopdf-amd64. Verificar se o usuário do apache tem permissão de escrita no diretório sei/temp. Verificar se as instalações foram realizadas em plataforma 64 bits. Verificar se existe alguma restrição no SELinux .Verificar se a versão do Java instalada no servidor é 1.8.  

> 13 -

PDF é gerado mas contém caracteres estranhos Verificar se o pacote de fontes True Type está instalado no servidor.  

> 14 -

Gerando PDF para arquivos do OpenOffice ocorre erro conectando no serviço. Verificar no servidor que roda o JODConverter se o serviço do OpenOffice foi inicializado corretamente na porta 8100:   

> soffice –headless -accept="socket,host=127.0.0.1,port=8100;urp;" –nofirststartwizard

33 

15 - Erro: Classe Memcache não encontrada. 

Falta instalar a extensão do memcache no PHP. 

16 - Utilizando SQL Server ao tentar entrar na página de login a URL é redirecionada normalmente para a página login.php da pasta sip porém a página aparece em branco, sem qualquer código-fonte As tabelas da base do SIP são exportadas pertencendo ao esquema “sip”. O usuário que está acessando o banco pode não ter permissão sobre este esquema. 

17 - Utilizando SQL Server ao tentar fazer login no SIP é lançada a exceção “...Não é possível inserir o valor NULL na coluna '', tabela ''; a coluna não permite nulos. Falha em INSERT...” 

Utilizando SQL Server 2012 (ou superior) é necessário instalar o MSSQL/FreeTDS 0.95 e alterar no arquivo freetds.conf a opção global "tds version" para “7.0”. Verificar também as configurações das bases SIP e SEI executando o comando " dbcc useroptions " e verificando se os valores conferem com as opções abaixo:                

> textsize 2147483647
> language us_english dateformat mdy datefirst 7lock_timeout -1 quoted_identifier SET arithabort SET ansi_null_dflt_on SET ansi_warnings SET ansi_padding SET ansi_nulls SET concat_null_yields_null SET isolation level read committed

18 - Ao gerar um processo a tela do navegador fica em branco Verificar a instalação da biblioteca gráfica GD pois na criação do processo ela é utilizada para gerar o código de barras. Outra possibilidade é a falta da extensão bcmath do PHP. Verificar também se o diretório sei/temp está com permissão de escrita para o usuário do apache. Verificar log do apache. 

19 - Ao criar um novo documento em um processo o editor não abre. No console do navegador é exibido Erro 500 (Internal Server Error) 

Verificar a instalação da biblioteca gráfica GD pois na criação do documento ela é utilizada para gerar o código de barras e QRCode. Outra possibilidade é a falta da extensão bcmath do PHP. Verificar também se o diretório sei/temp está com permissão de escrita para o usuário do apache. Verificar log do apache. 

20 - Ao enviar e-mail o mesmo não é encaminhado e é registrado no menu Infra/Log do SEI o erro 

“...SMTP: Data not accepted...” 

Habilitar o servidor do SEI para relay no servidor de email. Se utilizando sendmail (chave InfraMail/Tipo = 1 no arquivo de configuração) configurar o endereço do servidor de email na chave relayhost no arquivo main.cf do postfix. Se utilizando Exchange adicionar o servidor do SEI 34 na lista de permissões. 

21 - Ao incluir um documento externo apresenta “Erro cadastrando Anexo.” Verificar se o usuário do apache tem permissão no diretório sei/temp e também no repositório de arquivos informado no arquivo ConfiguracaoSEI.php na chave SEI/RepositorioArquivos. Verificar menu Infra/Log do SEI. 

22 - Erro salvando ou assinando documento no editor Web ("Número de seções do documento inconsistente." ou "Got a packet bigger than 'max_allowed_packet' bytes") Pode acontecer em documentos muito grandes ou quando o usuário tenta adicionar uma imagem no conteúdo. Se utilizando mod_suhosin no Apache então o tamanho padrão para POST de formulários será 1Mb. Neste caso ajustar os parâmetros: 

> suhosin.post.max_value_length suhosin.request.max_value_length

Se utilizando base MySQL o problema pode ser no parâmetro max_allowed_packet do banco de dados, cujo valor padrão também é 1Mb. O valor atual pode ser consultado através do comando: 

> show variables like 'max_allowed_packet';

Outra possibilidade é que a variável post_max_size do php.ini esteja configurada com um valor muito baixo. 

23 - Ao gerar PDFs contendo links https são lançados erros de acesso a funções SSL                     

> QSslSocket: cannot call unresolved function SSLv3_client_method QSslSocket: cannot call unresolved function SSL_CTX_new QSslSocket: cannot call unresolved function SSL_library_init QSslSocket: cannot call unresolved function ERR_get_error QSslSocket: cannot call unresolved function ERR_error_string

Neste caso adicionar links simbólicos para as bibliotecas abaixo:      

> cd /usr/lib64 ln -s libssl.so.10 libssl.so ln -s libcrypto.so.10 libcrypto.so

24 - Problemas com pesquisa com o caractere / (barra) Verificar se as variáveis do php.ini magic-quotes-gpc , magic_quotes_runtime e 

> magic_quotes_sybase

estão desligadas. 

25 - Ao enviar email ocorre erro obtendo certificado        

> stream_socket_enable_crypto(): SSL operation failed with code 1. OpenSSL Error messages: error:14090086:SSL routines:SSL3_GET_SERVER_CERTIFICATE:certificate verify failed

Diferentemente das versões anteriores do PHP a versão 5.6 verifica os certificados nas conexões SSL. Se houver algum problema de configuração no acesso ao servidor de email é possível desabilitar temporariamente esta verificação adicionando a chave InfraMail/Seguranca e deixando o valor vazio (se não informada o valor padrão considerado é TLS). Ver seção Configuração SEI chave InfraMail. 35 

26 - Erros aleatórios em qualquer funcionalidade indicando timeout do memcache      

> Memcache::connect(): Can't connect to [servidor]:11211, Connection timed out (110)

O timeout padrão para acesso ao memcache é de 1s. Se for necessário é possível alterar este valor adicionando as chaves CacheSip/Timeout e CacheSEI/Timeout nos arquivos de configuração. Erros muito freqüentes de timeout acessando o servidor memcache podem significar problema na configuração do ambiente ou congestionamento da rede. 

27 - No log do SIP erros de tempo esgotado     

> Tempo limite para validação do login esgotado.

Após o usuário autenticar no SIP ele é redirecionado para a página de entrada do sistema. Neste momento o sistema destino deve confirmar o login para o SIP. O tempo padrão para esta confirmação é de 60 segundos podendo ser alterado por meio do parâmetro TempoLimiteValidacaoLogin (ver seção Configuração SIP). Erros deste tipo muito freqüentes podem indicar sobrecarga dos servidores que não conseguem levantar a sessão dos usuários.
</contents>
<source>
txt/SEI-v3.0.11.txt
</source>
<contents>
# Atualização 3.0.11 

Esta atualização abrange os itens abaixo: 

1. XSS (verificação avançada): diminuição no tempo de processamento, melhorias na 

filtragem de conteúdo e na detecção para evitar falsos positivos. Passa a exibir no 

log de erros os trechos do conteúdo que foram recusados (apenas para documentos 

públicos); 

2. XSS (verificação básica): adicionada gravação no log de todas as palavras restritas 

existentes no conteúdo (antes registrava somente a primeira encontrada); 

Inclui também todos os itens das atualizações anteriores. 

# Requisitos 

Versão 3.0.x instalada (verificar valor da constante de versão no arquivo 

sei/SEI.php). 

# Instruções :

1. Atenção: desde a atualização 3.0.10 é necessária a instalação do componente 

mbstring no PHP; 

2. fazer backup dos diretórios "sei", "sip" e "infra" do servidor PHP; 

3. descompactar o arquivo sei-v3.0.11.zip; 

4. copiar os diretórios descompactados "sei", "sip" e "infra" para os servidores so -

brescrevendo os arquivos existentes; 

Versão 3.0.10 

 Correções de segurança para evitar ataques de XSS; 

 Corrige erro na inclusão de links para documentos e na localização de documentos 

modelo quando o número de dígitos configurado para documentos é maior que 7; 

 Corrige inclusão de links para processos/documentos quando editando textos 

padrão e seções de modelos de documento; 

 Interface de Módulos: adicionado novo evento obterAcoesExternasSemLogin. 

Versão 3.0.9 

 Atualizada assinatura do componente de assinatura digital. É necessário também 

adicionar o novo certificado na relação de Autoridades Certificadoras de 

Signatário do Java (mais detalhes no ícone de instruções exibido na tela de 

assinatura); 

18/04/2018 1 No resultado da pesquisa a linha acessada ao clicar na árvore ou no link para o 

documento será sinalizada em amarelo; 

 Passa a registrar na auditoria de cancelamento e exclusão de documentos externos 

o nome do arquivo original e a sua localização no repositório (facilitando a 

recuperação de conteúdo); 

 Adicionada validação de inclusão de imagens e de elementos não permitidos no 

cadastro de Texto Padrão; 

 Corrigida paginação da tela de Ciências (contribuição Ministério do 

Planejamento); 

 Adicionada paginação na tela de protocolos do bloco (contribuição Ministério do 

Planejamento); 

 Em algumas situações apresentava erro ao desativar unidade: "Existem grupos de 

contato utilizando este contato."; 

 Interface de Módulos: adicionado novo evento obterAcoesExternasSemLogin 

possibilitando a implementação de telas onde não é necessário que o usuário esteja 

logado (como no formulário de Ouvidoria e na tela de Conferência de 

Autenticidade de Documentos); 

 Interface de Módulos: adicionados novos eventos cadastrarContato, 

alterarContato, excluirContato, desativarContato e reativarContato. 

Versão 3.0.8 

 Adicionada cópia automática do número de protocolo para a área de transferência 

(ao clicar no ícone do processo/documento na árvore de processo); 

 Otimizações no tamanho e processamento da sessão de usuário no servidor 

melhorando o desempenho (principalmente em permissões sinalizadas com a 

opção estender às subunidades); 

 Corrige erro desanexando processo que era originalmente sigiloso e foi anexado 

após ter seu nível de acesso alterado para restrito; 

 Corrige erro de senha não informada quando usada assinatura com certificado 

digital; 

 Em algumas situações apresentava erro ao conceder acesso externo parcial quando 

o primeiro item da árvore era um processo anexado; 

 Corrige erro abrindo a ajuda do campo conteúdo na tela de cadastro de seções em 

modelos; 

 Interface de Módulos: alterados parâmetros AcessoExternoAPI.Procedimento e 

AcessoExternoAPI.Documento nos eventos 

cancelarDisponibilizacaoAcessoExterno e cancelarLiberacaoAssinaturaExterna 

que estavam recebendo os identificadores do processo e do documento no lugar de 

instâncias de ProcedimentoAPI e DocumentoAPI; 

 Interface de Módulos: adicionados novos eventos darCienciaProcesso e 

darCienciaDocumento; 

 Interface de Módulos: adicionados na classe UnidadeAPI os campos SinProtocolo, 

SinArquivamento e SinOuvidoria para retorno pela operação listarUnidades; 

 Web Services: adicionados na estrutura Unidade os campos SinProtocolo, 

SinArquivamento e SinOuvidoria para retorno pelo serviço listarUnidades; 

 Web Services: otimização no tempo de processamento para inclusão de 

18/04/2018 2documentos externos. 

Versão 3.0.7 

 Adicionado filtro por tipo de documento na funcionalidade de pesquisa no 

processo; 

 Agora é possível alterar os metadados (assuntos, interessados, nível de acesso,...) 

em protocolos de processos anexados sem precisar desanexar; 

 Na comparação de documentos alguns caracteres não eram identificados 

corretamente exibindo no lugar deles o caractere interrogação "?"; Em outras 

situações poderia acontecer um erro deixando a tela em branco; 

 O ícone de atenção passa a ser exibido também em processos que receberam 

documento movido; 

 Em algumas situações impedia o cadastramento de usuários externos exibindo a 

mensagem "Já existe um usuário externo cadastrado com este CPF."; 

 Adicionado botão "Assinar" na tela de listagem de blocos de assinatura 

possibilitando assinar vários blocos simultaneamente; 

 Coordenador de Acervo de Sigilosos agora pode ativar credenciais em processos 

que já tenham credenciais ativas; 

 No "Relatório de Processos Sigilosos" e no "Acervo de Sigilosos da Unidade" 

algumas operações verificavam se o usuário tinha permissão no perfil "Básico" 

padrão do sistema podendo não funcionar em instituições que clonaram o perfil 

padrão; 

 Na tela de Pesquisa foi alterado o rótulo "Data" para "Data do Processo / 

Documento"; 

 Corrige falha no acesso aos blocos de assinatura por meio dos links existentes na 

consulta de andamento do processo; 

 Na tela de cadastro de Tipo de Processo foi alterado o rótulo "Nível de Acesso 

Sugerido" para "Nível de Acesso Sugerido (Serviços e Módulos)" pois esta opção 

têm efeito apenas nestas integrações; 

 Na tela de atribuições de processos do usuário agora são os exibidos ao lado do 

número do processo os mesmos ícones da tela de Controle de Processos; 

 Na geração de PDF foi adicionado tratamento para alguns caracteres especiais; 

 Em algumas situações, abrindo um documento para edição, poderia não exibir a 

mensagem de cancelamento de assinatura; 

 Corrige erro "Hipótese Legal não aplicável ao protocolo" quando gerando 

publicação relacionada e o documento original não era público e continha hipótese 

sinalizada; 

 Estava permitindo salvar o conteúdo de um documento com o processo fechado na 

unidade (quando o processo era enviado ou concluído com documento aberto no 

editor); 

 Ao visualizar um documento assinado na árvore e solicitar o envio do processo 

agora será exibido um aviso se o documento teve sua assinatura cancelada; 

 A digitação de alguns caracteres especiais em campos do tipo auto-completar 

podia provocar travamento do navegador; 

 Em algumas situações não era possível retirar a formatação negrito de textos 

colados no editor; 

18/04/2018 3 Interface de Módulos: adicionados novos eventos desativarUnidade, 

reativarUnidade, excluirTipoDocumento, desativarTipoDocumento, 

reativarTipoDocumento, excluirTipoProcesso, desativarTipoProcesso, 

reativarTipoProcesso, montarAcaoDocumentoAcessoExternoNegado, 

cancelarLiberacaoAssinaturaExterna, 

montarAcaoProcessoAnexadoAcessoExternoNegado, 

cancelarDisponibilizacaoAcessoExterno; 

 Interface de Módulos: corrige erro na operação gerarProcedimento "Não é possível 

informar o número do processo."; 

 Interface de Módulos: adicionados na classe "DocumentoAPI" os campos 

IdProcedimento, IdUsuarioGerador e IdOrgaoUnidadeGeradora que estão agora 

disponíveis no evento assinarDocumento complementando dados do documento 

assinado; 

 Interface de Módulos: corrigido erro no tratamento dos eventos 

montarAcaoControleAcessoExterno, 

montarAcaoDocumentoAcessoExternoAutorizado 

e montarAcaoProcessoAnexadoAcessoExternoAutorizado. Quando mais de um 

módulo tratava estes eventos apenas o primeiro era considerado (colaboração 

ANATEL); 

 Web Services: corrige erro no serviço consultarDocumento quando pesquisando 

por um documento movido e ao mesmo tempo solicitando o andamento de 

geração; 

 Web Services: corrige erro no serviço incluirDocumento quando solicitada 

inclusão em processo cujo tipo foi desativado (colaboração ANATEL); 

 Web Services e Interface de Módulos: alteração no serviço e na operação listar -

Contatos 

(a) filtro IdTipoContato passa a ser opcional; 

(b) adicionado filtro opcional IdContato que permite um ou mais valores; 

(c) removidos do objeto de Contato os atributos de endereço associado Endereco -

Associado, ComplementoAssociado, BairroAssociado, IdCidadeAssociado, 

NomeCidadeAssociado, IdEstadoAssociado, SiglaEstadoAssociado, IdPai -

sAssociado, NomePaisAssociado e CepAssociado; 

(d) agora o endereço do contato estará sempre disponível nos atributos Endereco, 

Complemento, Bairro, IdCidade, NomeCidade, IdEstado, SiglaEstado, IdPais, No -

mePais e Cep não sendo mais necessário verificar o campo SinEnderecoAssociado 

para definir qual conjunto de atributos utilizar. 

Versão 3.0.6 

 No cadastro de processos e documentos não estava permitindo remover assuntos 

adicionados pela própria unidade durante o procedimento de alteração; 

 Processos enviados mais de uma vez para uma unidade sem que esta tenha dado 

recebimento eram contabilizados de forma errada no Controle de Processos da 

unidade de destino. Os processos que atualmente se encontram nesta situação terão 

a quantidade corrigida automaticamente após o recebimento; 

 Não estava permitindo salvar documentos externos se o tipo escolhido 

apresentasse restrição de uso no órgão ou unidade; 

18/04/2018 4 Corrige erro no cadastro automático de interessados/destinatários/remetentes 

contendo o caractere "&" no nome; 

 No cadastro de órgãos do SEI foi retirada do campo "Corretor Ortográfico" a 

opção "Avaliação Gratuita (webspellchecker.net)". O site removeu o idioma 

português da versão de avaliação deixando disponível apenas para a versão 

licenciada. O sistema assumirá automaticamente a opção "Nativo do Navegador"; 

 Na tela de Pesquisa no Processo a busca por números não estava funcionando 

corretamente; 

 Correção de segurança - estava permitindo que um usuário desativado na base do 

SEI (mas ainda ativo e com permissão na base do SIP) realizasse login no sistema; 

 Corrige erro na montagem da árvore de processo quando utilizado o caractere "\" 

(barra invertida) no campo "Número/Nome na Árvore" de documentos; 

 Corrige erro na Inspeção Administrativa ao utilizar o tipo "Processos em 

tramitação por órgão" (bases Oracle); 

 Na geração de PDF do processo não estava criando corretamente os marcadores de 

navegação para os documentos; 

 Atualizadas cadeias de certificados do componente de assinatura digital; 

 Atualizada assinatura de código do componente de assinatura digital devido a nova 

versão Java 8 update 131 que passou a recusar a assinatura anterior; 

 Corrigido erro "Nenhuma autoridade informada faz parte da cadeia de certificados 

do certificado informado." para certificados digitais da cadeia v5. 

Versão 3.0.5 

 Adicionado botão "Voltar", paginação de registros e ordenação de colunas na tela 

de "Documentos do Localizador" no módulo de arquivamento; 

 Na tela de cadastro de documento não carregava o campo "Tipo do Documento" 

para documentos externos se o tipo estivesse sinalizado como "Interno do 

Sistema"; 

 Adicionado parâmetro 

SEI_EMAIL_CONVERTER_ANEXO_HTML_PARA_PDF para controlar a 

conversão automática de anexos de email de HTML para PDF: 

0 = nenhuma conversão é realizada (valor padrão) 

1 = o sistema converterá todos os anexos HTML para PDF (ex.: documentos do 

editor interno, documentos externos cujo arquivo anexo esteja no formato HTML, 

documentos HTML individuais inseridos pelo usuário na tela de elaboração do email) 

A conversão ajuda a evitar problemas no envio e/ou recebimento pois anexos no 

formato HTML podem provocar o bloqueio da mensagem. 

 Não estava permitindo alterar a numeração de documentos internos de tipos 

sinalizados com numeração "Informada" apresentando a mensagem "Não é 

possível alterar o número do documento."; 

 Em algumas situações a anexação de processos restritos provocava perda do 

acesso em algumas unidades sendo necessário reenviar o processo principal para 

correção; 

 Nos formulários corrige exibição de campos longos do tipo "Informação" que 

18/04/2018 5estavam sem quebra de linha; 

 Na assinatura de Usuários Externos foi adicionado o campo "Cargo / Função" que 

aparecerá na tela de assinatura externa apenas se existir um cargo associado com o 

contato do usuário externo (será possível escolher entre o cargo associado com o 

usuário externo e o valor padrão "Usuário Externo"); 

 Adicionados na estrutura "Assinatura" dos Web Services e na classe 

"AssinaturaAPI" da API de módulos os campos IdUsuario, IdOrigem, IdOrgao e 

Sigla complementando dados do usuário assinante. Estes componentes são 

retornados pelos serviços e métodos de consulta de documento e consulta de 

blocos; 

 Agora é possível alterar os dados de Resumo e Imprensa Nacional em publicações 

já confirmadas; 

 Na pesquisa foi adicionado filtro por parte de Palavras ou Números (caractere 

"*"): 

embarg* retornará documentos contendo embarg ar, embarg o, embarg ou, 

embarg ante, ... 

201.7* retornará documentos contendo 201.7 98.988-00, 201.7 19,43, 201.7 1, ... 

 Restrições de uso para tipos de documentos por órgãos e/ou unidades agora são 

aplicadas apenas para documentos gerados (antes restringia também para 

documentos externos); 

 Adicionado certificado Serpro v5 nas cadeias de certificados do componente de 

assinatura digital. 

Versão 3.0.4 

 Em algumas situações apresentava erro ao gerar PDFs com tipos de processo ou 

documentos contendo caracteres acentuados. Também pode ser necessário confi -

gurar no Sistema Operacional o local e a codificação de caracteres "localedef 

pt_BR -i pt_BR -f ISO-8859-1" (contribuição Ministério do Planejamento); 

 Corrige erro ao ativar uma Tabela de Assuntos quando não existe outra tabela ati -

va; 

 Corrige erro no script de atualização ao processar Pontos de Controle. Afeta so -

mente instituições nas quais o script não executou até o final exibindo o erro "Call 

to a member function setStrSinUltimo() on null". Neste caso aplicar a 3.0.4 e se -

guir o roteiro de atualização da versão 3.0.0; 

 Na geração de estatísticas de Desempenho de Processos não estava gravando o 

detalhamento se o número de registros fosse menor que 50 (ao clicar no número 

informava que não haviam registros); 

 Ao entrar na tela de "Pesquisa" estava selecionando automaticamente para filtro no 

campo "Órgão Gerador" o órgão que tivesse identificador interno igual "0" (zero); 

 Adicionado tratamento para links não assinados que direcionam para a árvore do 

processo: 

[servidor]/controlador.php?acao=procedimento_trabalhar&id_procedimento=[ID 

interno do processo] 

[servidor]/controlador.php?acao=procedimento_trabalhar&id_procedimento=[ID 

interno do processo]&id_documento=[ID interno do documento] 

18/04/2018 6Estes links também são retornados pelos Web Services quando a opção "Gerar 

links de acesso externos" está desmarcada no cadastro do serviço. 

Os links somente poderão ser acessados por usuários que possuem login no SEI. 

Se o usuário já estiver logado irá posicionar automaticamente na árvore do proces -

so. Caso não esteja logado será direcionado para o login no SIP e após a autentica -

ção abrirá o SEI diretamente na árvore do processo. Entretanto se o processo não 

for público a visualização dependerá da unidade na qual o usuário está posiciona -

do no sistema. 

Versão 3.0.3 

 Liberada alteração dos dados cadastrais do processo e de seus documentos para to -

das as unidades de tramitação do processo (alterações que antes estavam restritas à 

unidade geradora); 

 Adicionado parâmetro SEI_EXIBIR_ARVORE_RESTRITO_SEM_ACESSO 

para controlar a exibição da árvore de processo quando pesquisando por número 

de protocolo restrito que a unidade não tem acesso: 

0 = não exibe informando apenas que o acesso foi negado (valor padrão) 

1 = exibe a árvore com os itens desabilitados possibilitando também a consulta do 

andamento 

Para unidades sinalizadas como "protocolo" sempre será exibida a árvore indepen -

dente do valor configurado no parâmetro. 

 Corrige erro na indexação parcial de processos/documentos "ORDER BY items 

must appear in the select list if SELECT DISTINCT is specified" (bases SQL 

Server). 

Versão 3.0.2 

SEI 

 Atualizadas as cadeias de certificados do componente de assinatura digital; 

 Adicionada escolha do "Nível de Acesso" na tela "Incluir Documento em Proces -

sos"; 

 Quando uma unidade de protocolo utilizava a pesquisa rápida por um número de 

protocolo restrito no qual ela não tinha acesso o sistema não posicionava na árvore 

do processo; 

 Em algumas situações utilizando a pesquisa rápida informando apenas parte do nú -

mero de protocolo era exibida apenas a tela de pesquisa avançada sem resultados; 

 Variável @telefone_unidade@ que havia sido removida foi adicionada novamente 

com o mesmo valor de @telefone_fixo_unidade@; 

 Não estava sendo possível editar os números de telefone nos contatos de unidades; 

 Na tela de "Publicações Eletrônicas" foi aplicada ordenação no campo "Unidade 

Responsável" e o campo "Órgão" agora é inicializado com todos os itens selecio -

nados; 

 Estava exibindo o ícone de "Autenticação de Documento" para documentos nato-

digitais; 

18/04/2018 7 Adicionada na geração de documento a substituição de variáveis no conteúdo de 

documentos utilizados como modelo ou para geração de circular; 

 Corrige erro de CNPJ inválido no cadastro de contatos; 

 Corrige erro ao gerar bloco por meio de Web Services; 

 Corrige erro cadastrando registro em estatísticas (bases Oracle); 

 Corrige erro listando operações de serviços (bases SQL Server). 

 Corrige erro na atualização da versão 3.0.0 ao processar dados de contatos. Afeta 

somente instituições nas quais o script não executou até o final exibindo o erro 

"Cannot change column 'id_contato': used in a foreign key constraint 

'fk_usuario_contato'". Neste caso aplicar a 3.0.2 e seguir o roteiro de atualização 

da versão 3.0.0. 

 Otimização de acesso ao servidor e banco de dados em campos com autocomple -

tar; 

 Melhorias no uso da cache em memória (servidor memcache); 

SIP 

 Corrige erro ao executar agendamento "removerDadosLogin" que elimina dados 

temporários (bases Oracle); 

 Em algumas situações apresentava erro ao clonar hierarquia. 

Versão 3.0.1 

 Parâmetro SEI_MASCARA_NUMERO_PROCESSO_INFORMADO agora acei -

ta mais de um valor utilizando como separador o caractere barra vertical "|" (ex.: 

####.######-##|######-###-##). A primeira máscara será utilizada como padrão 

para digitação na tela "Iniciar Processo" e o número informado (digitado ou cola -

do) deverá atender a uma das máscaras informadas no parâmetro; 

 Possibilidade de verificar a integridade dos arquivos do repositório a cada acesso 

aos documentos externos. A verificação pode ser habilitada através do cadastra -

mento do parâmetro SEI_HABILITAR_VERIFICACAO_REPOSITORIO com o 

valor "1" (menu Infra/Parâmetros). Esta funcionalidade provoca um ligeiro aumen -

to na carga de processamento dos servidores de aplicação, do repositório de arqui -

vos e da rede; 

 Atualização da assinatura dos componentes da applet de Assinatura Digital (assi -

natura atual expira em 10/01/2017). Se esta atualização não for aplicada então será 

necessário adicionar o endereço do SEI da instituição na "Lista de Exceções de Si -

tes" do painel de controle Java. Além disso, durante a realização de assinaturas di -

gitais alguns avisos de segurança poderão ser exibidos para o usuário. Para que o 

componente não seja bloqueado é necessário importar a nova "CA de Signatário" 

no painel de controle Java (ver ajuda no ícone de instruções existente na tela de as -

sinatura ao lado da opção "Certificado Digital"); 

 Correção de segurança no acesso externo para disponibilização de documentos 

(usuário externo pode ter acesso indevido ao conteúdo de documentos); 

 Usuários externos não estavam conseguindo assinar documentos em processos si -

gilosos; 

18/04/2018 8 Formulário de ouvidoria não estava aceitando telefones com nove digitos; 

 Corrige erro no registro de documento externo se o parâmetro 

SEI_HABILITAR_VALIDACAO_EXTENSAO_ARQUIVOS estiver configura -

do com vazio; 

 Corrige erro ao montar árvore de processo se o parâmetro SEI_ACESSO_FOR -

MULARIO_OUVIDORIA estiver configurado com "1". 

 Corrige erro na atualização da versão 3.0.0 ao processar dados de pontos de con -

trole que foram excluídos. Afeta somente instituições que usam esta funcionalida -

de e nas quais o script não executou até o final (exibindo erro ao criar a chave es -

trangeira "fk_andam_situacao_situacao" da tabela "andamento_situacao"). Neste 

caso aplicar a 3.0.1 e seguir o roteiro de atualização da versão 3.0.0. 

18/04/2018 9
</contents>
</root>
```
