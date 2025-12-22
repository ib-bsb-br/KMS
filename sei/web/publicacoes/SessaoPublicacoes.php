<?
  /*
  * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
  * 17/04/2007 - CRIADO POR cle@trf4.gov.br
  */
   require_once dirname(__FILE__).'/../SEI.php';
   
  class SessaoPublicacoes extends InfraSessao {
    
    private static $instance = null;
    
    //SERVE A INSTÂNCIA ATIVA DA CLASSE OU UMA NOVA (SE NÃO EXISTIR)
 	  public static function getInstance() {
	    if (self::$instance == null) {
	      SessaoSEI::getInstance(false,false);
        self::$instance = new SessaoPublicacoes();
	    }
	    return self::$instance; 
	  }
	  
    //MESMO CADASTRADO NO SISTEMA DE PERMISSÕES
    public function getStrSiglaOrgaoSistema() {
  		return ConfiguracaoSEI::getInstance()->getValor('SessaoSEI','SiglaOrgaoSistema');
	  }
	  
	  //MESMA CADASTRADA NO SISTEMA DE PERMISSÕES
    public function getStrSiglaSistema() {
		  return ConfiguracaoSEI::getInstance()->getValor('SessaoSEI','SiglaSistema');
	  }
    
    //USUÁRIO É REDIRECIONADO PARA ESTE URL QUANDO A SESSÃO É ENCERRADA OU O USUÁRIO ALTEROU O URL (I.E.: QUERYSTRING)
	  public function getStrPaginaLogin(){
			return null;
		}	
		
  	public function getStrSipWsdl(){
			return null;
  	}
  	
    //PASSANDO SEMPRE TRUE EM TODOS, A VALIDAÇÃO DO SIP ESTÁ DESATIVADA (ESTES MÉTODOS SOBRESCRITOS EXISTEM NA Infra.php)
    public function validarSessao(){
      return true;
    }
    
    public function assinarLink($strLink){
      
      if (strpos($strLink,'id_orgao_publicacao=')===false){
        if (isset($_GET['id_orgao_publicacao']) && $_GET['id_orgao_publicacao']!=''){
          if (strpos($strLink,'?')===false){
            $strLink .= '?';
          }else{
            $strLink .= '&';
          }
          $strLink .= 'id_orgao_publicacao='.$_GET['id_orgao_publicacao'];
        }
      }
      
      return $strLink;
    }
    
    public function validarLink($strLink=null){
      
      if (trim($_GET['id_orgao_publicacao'])==''){
        throw new InfraException('Link de publicação incompleto.',null,null,false);
      }
      
      if (!is_numeric($_GET['id_orgao_publicacao'])){
        throw new InfraException('Link de publicação inválido.',null,null,false);
      }
      
      $objOrgaoDTO = new OrgaoDTO();
      $objOrgaoDTO->setNumIdOrgao($_GET['id_orgao_publicacao']);
       
      $objOrgaoRN = new OrgaoRN();
      if ($objOrgaoRN->contarRN1354($objOrgaoDTO)==0){
        throw new InfraException('Dados do link de publicação inválidos.');
      }
            
      return true;
    }
    
    public function validarPermissao($strRecurso,$strUnidade=null) {
      return true;
    }
    
    public function verificarPermissao($strRecurso,$strUnidade=null) {
      return true;
    }
  }
?>