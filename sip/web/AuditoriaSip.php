<?
/*
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 * 
 * 08/04/2013 - criado por MGA
 *
 */
 
 require_once dirname(__FILE__).'/Sip.php';
 
 class AuditoriaSip extends InfraAuditoria {
	 
 	private static $instance = null;
 	
 	public static function getInstance() 
	{ 
	    if (self::$instance == null) { 
        self::$instance = new AuditoriaSip(BancoSip::getInstance(),SessaoSip::getInstance(),CacheSip::getInstance());
	    } 
	    return self::$instance; 
	}

	 public function getTempoCache(){
		 return CacheSip::getInstance()->getNumTempo();
	 }

   public function getArrExcecoesPost(){
     return array('pwdSenha','pwdSenhaPesquisa','pwdSenhaTeste');
   }
 }
?>