<?
/*
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 * 
 * 26/10/2006 - criado por MGA
 *
 */
 
 require_once dirname(__FILE__).'/Sip.php';
 
 class PaginaLogin extends InfraPaginaEsquema2 {
	 
	private static $instance = null;
 	
 	public static function getInstance() 
	{ 
	    if (self::$instance == null) { 
        self::$instance = new PaginaLogin();
	    } 
	    return self::$instance; 
	} 

	public function __construct(){
	  parent::configurarHttps(ConfiguracaoSip::getInstance()->getValor('SessaoSip','https'));
	  parent::__construct();
	}
	
	public function getStrNomeSistema(){
		return "Sistema de Permissões";
	}
	
	public function isBolProducao(){
		return ConfiguracaoSip::getInstance()->getValor('Sip','Producao');
	}

  public function validarHashTabelas(){
    return true;
  }

	public function getStrMenuSistema(){
		return null;
	}
	
	public function getArrStrAcoesSistema(){
		return null;
	}
	
	public function getObjInfraSessao(){
	  return null;
	}
	
	public function getObjInfraLog(){
	  return LogSip::getInstance();
	}
	
  public function getStrEsquemaCores(){
    //return 'vermelho';
    return 'azul_celeste';
  }	
  
  public function getStrTextoBarraSuperior(){
  	try{
  		
  	  $ret = '';
  	  
  		$objOrgaoDTO = new OrgaoDTO();
  		$objOrgaoDTO->retStrDescricao();
  		$objOrgaoDTO->setStrSigla($_GET['sigla_orgao_sistema']); 
  		
  		$objOrgaoRN = new OrgaoRN();
  		$objOrgaoDTO = $objOrgaoRN->consultar($objOrgaoDTO);
 
  		if ($objOrgaoDTO!=null){
  		  $ret = $objOrgaoDTO->getStrDescricao();
  		}
  		
  		return $ret;
  		
  	}catch(Exception $e){
  		try{
  		  LogSip::getInstance()->gravar($e);
  		}catch(Exception $e2){}
  	}
  	return $_GET['sigla_orgao_sistema'];
  }
  
  public function getStrTextoBarraSistema(){
  	try{
  		
  		$objSistemaDTO = new SistemaDTO();
  		$objSistemaDTO->retStrDescricao();
  		$objSistemaDTO->setStrSigla($_GET['sigla_sistema']);
  		$objSistemaDTO->setStrSiglaOrgao($_GET['sigla_orgao_sistema']);
  		
  		$objSistemaRN = new SistemaRN();
  		$objSistemaDTO = $objSistemaRN->consultar($objSistemaDTO);
  		
  		if ($objSistemaDTO!=null){
  		  $ret = $objSistemaDTO->getStrDescricao();
  		}
  		
  		return $ret;
  		
  	}catch(Exception $e){
  		try{
  		  LogSip::getInstance()->gravar($e);
  		}catch(Exception $e2){}
  	}
  	return $_GET['sigla_orgao_sistema'];
  }

	 public function permitirXHTML(){
		 return false;
	 }

  /*
	public function getDiretorioJavaScriptGlobal(){
	  return '/infra/infra_js';
	}
  
	public function getDiretorioEsquemas(){
	  return '/infra/infra_css/esquemas'; 
	}
	
	public function getDiretorioCssGlobal(){
		return '/infra/infra_css';
	}
  */
 }
?>