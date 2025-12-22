<?
/*
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 * 
 * 17/05/2006 - criado por MGA
 *
 */

require_once dirname(__FILE__).'/Sip.php';

try{
  if (ConfiguracaoSip::getInstance()->getValor('BancoSip','Tipo') == 'MySql'){

    class BancoSip extends InfraMySqli {
     	private static $instance = null;
     	
     	public static function getInstance() 
    	{ 
    	    if (self::$instance == null) { 
            self::$instance = new BancoSip();
    	    } 
    	    return self::$instance; 
    	} 
    	
    	public static function setBanco($objInfraIBanco){
    	  self::$instance = $objInfraIBanco;
    	}
     	 
      public function getServidor() {
        return ConfiguracaoSip::getInstance()->getValor('BancoSip','Servidor');
     	}
    
     	public function getPorta() {
        return ConfiguracaoSip::getInstance()->getValor('BancoSip','Porta');
     	}
     	 
      public function getBanco() {
        return ConfiguracaoSip::getInstance()->getValor('BancoSip','Banco');
      }
     	 
      public function getUsuario(){
        return ConfiguracaoSip::getInstance()->getValor('BancoSip','Usuario');
      }
     	 
      public function getSenha(){
        return ConfiguracaoSip::getInstance()->getValor('BancoSip','Senha');
      }
      
      public function isBolValidarISO88591(){
        return true;
      }

      public function isBolManterConexaoAberta(){
        return true;
      }

      public function isBolForcarPesquisaCaseInsensitive(){
        return !ConfiguracaoSip::getInstance()->getValor('BancoSip', 'PesquisaCaseInsensitive', false, false);
      }

      public function isBolConsultaRetornoAssociativo(){
        return true;
      }

    }
  
  }else if (ConfiguracaoSip::getInstance()->getValor('BancoSip','Tipo') == 'SqlServer'){
  
    class BancoSip extends InfraSqlServer {
     	private static $instance = null;
    
     	public static function getInstance()
     	{
     	  if (self::$instance == null) {
     	    self::$instance = new BancoSip();
     	  }
     	  return self::$instance;
     	}
    
     	public static function setBanco($objInfraIBanco){
     	  self::$instance = $objInfraIBanco;
     	}
     		
      public function getServidor() {
        return ConfiguracaoSip::getInstance()->getValor('BancoSip','Servidor');
     	}
    
     	public function getPorta() {
        return ConfiguracaoSip::getInstance()->getValor('BancoSip','Porta');
     	}
     		
      public function getBanco() {
        return ConfiguracaoSip::getInstance()->getValor('BancoSip','Banco');
      }
      	
      public function getUsuario(){
        return ConfiguracaoSip::getInstance()->getValor('BancoSip','Usuario');
      }
      	
      public function getSenha(){
        return ConfiguracaoSip::getInstance()->getValor('BancoSip','Senha');
      }
      
      public function isBolValidarISO88591(){
        return true;
      }

      public function isBolManterConexaoAberta(){
        return true;
      }

      public function isBolForcarPesquisaCaseInsensitive(){
        return !ConfiguracaoSip::getInstance()->getValor('BancoSip', 'PesquisaCaseInsensitive', false, false);
      }

      public function isBolConsultaRetornoAssociativo(){
        return true;
      }

    }
    
  }else if (ConfiguracaoSip::getInstance()->getValor('BancoSip','Tipo') == 'Oracle'){
    
      class BancoSip extends InfraOracle {
        private static $instance = null;
    
        public static function getInstance()
        {
          if (self::$instance == null) {
            self::$instance = new BancoSip();
          }
          return self::$instance;
        }
    
        public static function setBanco($objInfraIBanco){
          self::$instance = $objInfraIBanco;
        }
    
        public function getServidor() {
          return ConfiguracaoSip::getInstance()->getValor('BancoSip','Servidor');
        }
    
        public function getPorta() {
          return ConfiguracaoSip::getInstance()->getValor('BancoSip','Porta');
        }
    
        public function getBanco() {
          return ConfiguracaoSip::getInstance()->getValor('BancoSip','Banco');
        }
    
        public function getUsuario(){
          return ConfiguracaoSip::getInstance()->getValor('BancoSip','Usuario');
        }
    
        public function getSenha(){
          return ConfiguracaoSip::getInstance()->getValor('BancoSip','Senha');
        }
    
        public function isBolValidarISO88591(){
          return true;
        }

        public function isBolManterConexaoAberta(){
          return true;
        }

        public function isBolForcarPesquisaCaseInsensitive(){
          return !ConfiguracaoSip::getInstance()->getValor('BancoSip', 'PesquisaCaseInsensitive', false, false);
        }
      }
    
  }else{
    if (InfraString::isBolVazia(ConfiguracaoSip::getInstance()->getValor('BancoSip','Tipo'))){
      die('Configuração do tipo de banco de dados do SIP vazia.');
    }else{
      die('Configuração do tipo de banco de dados do SIP inválida.');
    }
  }
}catch(Exception $e){
  if (!ConfiguracaoSip::getInstance()->isSetValor('BancoSip','Tipo')){
    die('Tipo do banco de dados do SIP não configurado.');
  }
}
?>