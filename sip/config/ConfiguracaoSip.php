<?

class ConfiguracaoSip extends InfraConfiguracao  {

        private static $instance = null;

        public static function getInstance(){
          if (ConfiguracaoSip::$instance == null) {
            ConfiguracaoSip::$instance = new ConfiguracaoSip();
          }
          return ConfiguracaoSip::$instance;
        }

        public function getArrConfiguracoes(){
          return array(
              'Sip' => array(
                  'URL' => 'http://localhost/sip',
                  'Producao' => true),

              'PaginaSip' => array('NomeSistema' => 'SIP'),

              'SessaoSip' => array(
                  'SiglaOrgaoSistema' => 'ABC',
                  'SiglaSistema' => 'SIP',
                  'PaginaLogin' => 'http://localhost/sip/login.php',
                  'SipWsdl' => 'http://localhost/sip/controlador_ws.php?servico=wsdl',
                  'https' => false),

              'BancoSip'  => array(
                  'Servidor' => 'localhost',
                  'Porta' => '3306',
                  'Banco' => 'sip',
                  'Usuario' => 'sei_app',
                  'Senha' => 'sei_app_password',
                  'Tipo' => 'MySql'), //MySql, SqlServer ou Oracle

                                'CacheSip' => array('Servidor' => '127.0.0.1',
                                                                'Porta' => '11211'),

              'HostWebService' => array(
                  'Replicacao' => array('localhost'), //endereço ou IP da máquina que implementa o serviço de replicação de usuários
                  'Pesquisa' => array('localhost'), //endereços/IPs das máquinas do SEI
                  'Autenticacao' => array('localhost')), //endereços/IPs das máquinas do SEI

                                'InfraMail' => array(
                                                'Tipo' => '1', //1 = sendmail (neste caso no  necessrio configurar os atributos abaixo), 2 = SMTP
                                                'Servidor' => 'localhost',
                                                'Porta' => '25',
                                                'Codificacao' => '8bit', //8bit, 7bit, binary, base64, quoted-printable
                                                'MaxDestinatarios' => 999, //numero maximo de destinatarios por mensagem
                                                'MaxTamAnexosMb' => 999, //tamanho maximo dos anexos em Mb por mensagem
                                                'Seguranca' => 'TLS', //TLS, SSL ou vazio
                                                'Autenticar' => false, //se true ento informar Usuario e Senha
                                                'Usuario' => 'sei_notifier',
                                                'Senha' => 'sei_notifier_password',
                                                'Protegido' => '' //campo usado em desenvolvimento, se tiver um email preenchido entao todos os emails enviados terao o destinatario ignorado e substitudo por este valor (evita envio incorreto de email)
                                )
          );
        }
}
?>
