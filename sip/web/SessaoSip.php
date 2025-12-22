<?
/*
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 * 
 * 08/11/2006 - criado por MGA
 *
 */
 
require_once dirname(__FILE__).'/Sip.php';
 
 class SessaoSip extends InfraSessao {
 	 
 	private static $instance = null;
 	
 	
 	//inicio - atributos para simular login	
 	private $numIdOrgaoSistema = null;
 	private $numIdSistema = null;
 	private $strSiglaOrgaoUsuario = null;
 	private $numIdOrgaoUsuario = null;
 	private $numIdContextoUsuario = null;
 	private $numIdUsuario = null;
 	private $strSiglaUsuario = null;
 	private $strNomeUsuario = null;
 	private $numIdUnidadeAtual = null;
 	//fim - atributos para simular login	

 	
 	public static function getInstance($bolHabilitada=true){ 
	    if (self::$instance == null) {
        self::$instance = new SessaoSip($bolHabilitada);
	    } 
	    return self::$instance; 
	}
	
	public function __construct($bolHabilitada){
	  parent::__construct($bolHabilitada);
	}

	public function getStrSiglaOrgaoSistema(){
		return ConfiguracaoSip::getInstance()->getValor('SessaoSip','SiglaOrgaoSistema');
	}
	
	public function getStrSiglaSistema(){
		return ConfiguracaoSip::getInstance()->getValor('SessaoSip','SiglaSistema');
	}
	
	public function getStrPaginaLogin(){
		return ConfiguracaoSip::getInstance()->getValor('SessaoSip','PaginaLogin');
	}
	
  public function getStrSipWsdl(){
		return ConfiguracaoSip::getInstance()->getValor('SessaoSip','SipWsdl');
  }	
	  
  //inicio - funções para simular um login
  public function setNumIdOrgaoSistema($numIdOrgaoSistema){
    $this->numIdOrgaoSistema = $numIdOrgaoSistema;
  }
  
  public function setNumIdSistema($numIdSistema){
    $this->numIdSistema = $numIdSistema;
  }
  
  public function setStrSiglaOrgaoUsuario($strSiglaOrgaoUsuario){
    $this->strSiglaOrgaoUsuario = $strSiglaOrgaoUsuario;
  }
  
  public function setNumIdOrgaoUsuario($numIdOrgaoUsuario){
    $this->numIdOrgaoUsuario = $numIdOrgaoUsuario;
  }
  
  public function setNumIdContextoUsuario($numIdContextoUsuario){
    $this->numIdContextoUsuario = $numIdContextoUsuario;
  }
  
  public function setNumIdUsuario($numIdUsuario){
    $this->numIdUsuario = $numIdUsuario;
  }
  
  public function setStrSiglaUsuario($strSiglaUsuario){
    $this->strSiglaUsuario = $strSiglaUsuario;
  }
  
  public function setStrNomeUsuario($strNomeUsuario){
    $this->strNomeUsuario = $strNomeUsuario;
  }
  
  public function setNumIdUnidadeAtual($numIdUnidadeAtual){
    $this->numIdUnidadeAtual = $numIdUnidadeAtual;
  }

  public function getNumIdOrgaoSistema(){
     return (!$this->isBolHabilitada()) ? $this->numIdOrgaoSistema : parent::getNumIdOrgaoSistema();    
  }
  
  public function getNumIdSistema(){
     return (!$this->isBolHabilitada()) ? $this->numIdSistema : parent::getNumIdSistema();        
  }
  
  public function getStrSiglaOrgaoUsuario(){
     return (!$this->isBolHabilitada()) ? $this->strSiglaOrgaoUsuario : parent::getStrSiglaOrgaoUsuario();        
  }
  
  public function getNumIdOrgaoUsuario(){
     return (!$this->isBolHabilitada()) ? $this->numIdOrgaoUsuario : parent::getNumIdOrgaoUsuario();        
  }
  
  public function getNumIdContextoUsuario(){
     return (!$this->isBolHabilitada()) ? $this->numIdContextoUsuario : parent::getNumIdContextoUsuario();        
  }
  
  public function getNumIdUsuario(){
     return (!$this->isBolHabilitada()) ? $this->numIdUsuario : parent::getNumIdUsuario();        
  }
  
  public function getStrSiglaUsuario(){
     return (!$this->isBolHabilitada()) ? $this->strSiglaUsuario : parent::getStrSiglaUsuario();        
  }
  
  public function getStrNomeUsuario(){
     return (!$this->isBolHabilitada()) ? $this->strNomeUsuario : parent::getStrNomeUsuario();        
  }
  
  public function getNumIdUnidadeAtual(){
    return (!$this->isBolHabilitada()) ? $this->numIdUnidadeAtual : parent::getNumIdUnidadeAtual();
  }
  //fim - funções para simular um login
  
   public function getObjInfraAuditoria(){
    return AuditoriaSip::getInstance();
  }    
 }
?>