<?
/*
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 * 
 * 09/04/2013 - criado por MGA
 *
 */
 
 require_once dirname(__FILE__).'/SEI.php';
 
 class AuditoriaSEI extends InfraAuditoria {
	 
 	private static $instance = null;
 	
 	public static function getInstance() 
	{ 
	    if (self::$instance == null) { 
        self::$instance = new AuditoriaSEI(BancoSEI::getInstance(),SessaoSEI::getInstance(),CacheSEI::getInstance());
	    } 
	    return self::$instance; 
	} 
	
	//public function getArrExcecoesGet(){
	//  return null;
	//}
	
	public function getArrExcecoesPost(){
	  return array('pwdSenha','pwdSenhaAtual','pwdSenhaNova','pwdSenhaConfirma');
	}

	public function getTempoCache(){
	  return CacheSEI::getInstance()->getNumTempo();
	}
}
?>