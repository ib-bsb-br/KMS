<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 19/08/2009 - criado por mga
*
* Versão do Gerador de Código: 1.28.0
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../Sip.php';

class GrupoRedeRN extends InfraRN {

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSip::getInstance();
  }

  private function validarStrOuLdap(GrupoRedeDTO $objGrupoRedeDTO, InfraException $objInfraException){
    
    if (InfraString::isBolVazia($objGrupoRedeDTO->getStrOuLdap())){
      $objInfraException->adicionarValidacao('Unidade Organizacional LDAP não informada.');
    }else{
      
      $objGrupoRedeDTO->setStrOuLdap(InfraLDAP::formatarContexto($objGrupoRedeDTO->getStrOuLdap()));

      if (strlen($objGrupoRedeDTO->getStrOuLdap())>256){
        $objInfraException->adicionarValidacao('Unidade Organizacional LDAP possui tamanho superior a 256 caracteres.');
      }
      
      $dto = new GrupoRedeDTO();
      $dto->retStrSiglaOrgao();
      $dto->setNumIdGrupoRede($objGrupoRedeDTO->getNumIdGrupoRede(),InfraDTO::$OPER_DIFERENTE);
      $dto->setStrOuLdap($objGrupoRedeDTO->getStrOuLdap(),InfraDTO::$OPER_IGUAL);
      $dto->setNumIdOrgao($objGrupoRedeDTO->getNumIdOrgao());
          
      $dto = $this->consultar($dto);
      
      if ($dto != null){
        $objInfraException->adicionarValidacao('Esta Unidade Organizacional do LDAP já está sendo utilizada em '.$dto->getStrSiglaOrgao().'.');
      }
    }
  }

  private function validarStrDescricao(GrupoRedeDTO $objGrupoRedeDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objGrupoRedeDTO->getStrDescricao())){
      $objGrupoRedeDTO->setStrDescricao(null);
    }
  }
  
  private function validarStrSinExcecao(GrupoRedeDTO $objGrupoRedeDTO, InfraException $objInfraException){
    if ($objGrupoRedeDTO->getStrSinExcecao()===null || ($objGrupoRedeDTO->getStrSinExcecao()!=='S' && $objGrupoRedeDTO->getStrSinExcecao()!=='N')){
      $objInfraException->adicionarValidacao('Sinalizador de Exceção inválido.');
    }else{
      if ($objGrupoRedeDTO->getStrSinExcecao()=='S'){
  			$objRelGrupoRedeUnidadeDTO = new RelGrupoRedeUnidadeDTO();
  			$objRelGrupoRedeUnidadeDTO->setNumIdGrupoRede($objGrupoRedeDTO->getNumIdGrupoRede());
  			$objRelGrupoRedeUnidadeRN = new RelGrupoRedeUnidadeRN();
  			if ($objRelGrupoRedeUnidadeRN->contar($objRelGrupoRedeUnidadeDTO)>0){
          $objInfraException->adicionarValidacao('O grupo de rede não pode ser configurado como exceção porque possui unidade associada.');				  
  			}
      }
    }
  }
  
  protected function cadastrarControlado(GrupoRedeDTO $objGrupoRedeDTO) {
    try{

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('grupo_rede_cadastrar',__METHOD__,$objGrupoRedeDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarStrOuLdap($objGrupoRedeDTO, $objInfraException);
      $this->validarStrDescricao($objGrupoRedeDTO, $objInfraException);
      $this->validarStrSinExcecao($objGrupoRedeDTO, $objInfraException);

      $objInfraException->lancarValidacoes();

      $objGrupoRedeBD = new GrupoRedeBD($this->getObjInfraIBanco());
      $ret = $objGrupoRedeBD->cadastrar($objGrupoRedeDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando Grupo de Rede.',$e);
    }
  }

  protected function alterarControlado(GrupoRedeDTO $objGrupoRedeDTO){
    try {

      //Valida Permissao
  	   SessaoSip::getInstance()->validarAuditarPermissao('grupo_rede_alterar',__METHOD__,$objGrupoRedeDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      if ($objGrupoRedeDTO->isSetStrOuLdap()){
        $this->validarStrOuLdap($objGrupoRedeDTO, $objInfraException);
      }

      if ($objGrupoRedeDTO->isSetStrDescricao()){
        $this->validarStrDescricao($objGrupoRedeDTO, $objInfraException);
      }
      
      if ($objGrupoRedeDTO->isSetStrSinExcecao()){
        $this->validarStrSinExcecao($objGrupoRedeDTO, $objInfraException);
      }
      
      $objInfraException->lancarValidacoes();

      $objGrupoRedeBD = new GrupoRedeBD($this->getObjInfraIBanco());
      $objGrupoRedeBD->alterar($objGrupoRedeDTO);

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando Grupo de Rede.',$e);
    }
  }

  protected function excluirControlado($arrObjGrupoRedeDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('grupo_rede_excluir',__METHOD__,$arrObjGrupoRedeDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();
      
      for($i=0;$i<count($arrObjGrupoRedeDTO);$i++){

        $dto = new GrupoRedeDTO();
			  $dto->retStrOuLdap();
			  $dto->setNumIdGrupoRede($arrObjGrupoRedeDTO[$i]->getNumIdGrupoRede());
			  $dto = $this->consultar($dto);

  			$objRelGrupoRedeUnidadeDTO = new RelGrupoRedeUnidadeDTO();
  			$objRelGrupoRedeUnidadeDTO->setNumIdGrupoRede($arrObjGrupoRedeDTO[$i]->getNumIdGrupoRede());
  			$objRelGrupoRedeUnidadeRN = new RelGrupoRedeUnidadeRN();
  			if ($objRelGrupoRedeUnidadeRN->contar($objRelGrupoRedeUnidadeDTO)>0){
          $objInfraException->adicionarValidacao('O grupo de rede "'.$dto->getStrOuLdap().'" possui unidade associada e não pode ser excluído.');				  
  			}
      }
      
      $objInfraException->lancarValidacoes();

      $objLoginRN = new LoginRN();
      
      $objGrupoRedeBD = new GrupoRedeBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjGrupoRedeDTO);$i++){
        
        $objLoginDTO = new LoginDTO();
        $objLoginDTO->retStrIdLogin();
        $objLoginDTO->retNumIdUsuario();
        $objLoginDTO->retNumIdSistema();
        $objLoginDTO->setNumIdGrupoRede($arrObjGrupoRedeDTO[$i]->getNumIdGrupoRede());
        $objLoginRN->excluir($objLoginRN->listar($objLoginDTO));
        
        $objGrupoRedeBD->excluir($arrObjGrupoRedeDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo Grupo de Rede.',$e);
    }
  }

  protected function consultarConectado(GrupoRedeDTO $objGrupoRedeDTO){
    try {

      //Valida Permissao
      //SessaoSip::getInstance()->validarAuditarPermissao('grupo_rede_consultar',__METHOD__,$objGrupoRedeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objGrupoRedeBD = new GrupoRedeBD($this->getObjInfraIBanco());
      $ret = $objGrupoRedeBD->consultar($objGrupoRedeDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando Grupo de Rede.',$e);
    }
  }

  protected function listarConectado(GrupoRedeDTO $objGrupoRedeDTO) {
    try {

      //Valida Permissao
      //SessaoSip::getInstance()->validarAuditarPermissao('grupo_rede_listar',__METHOD__,$objGrupoRedeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objGrupoRedeBD = new GrupoRedeBD($this->getObjInfraIBanco());
      $ret = $objGrupoRedeBD->listar($objGrupoRedeDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Grupos de Rede.',$e);
    }
  }

  protected function contarConectado(GrupoRedeDTO $objGrupoRedeDTO){
    try {

      //Valida Permissao
      //SessaoSip::getInstance()->validarAuditarPermissao('grupo_rede_listar',__METHOD__,$objGrupoRedeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objGrupoRedeBD = new GrupoRedeBD($this->getObjInfraIBanco());
      $ret = $objGrupoRedeBD->contar($objGrupoRedeDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro contando Grupos de Rede.',$e);
    }
  }
/* 
  protected function desativarControlado($arrObjGrupoRedeDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('grupo_rede_desativar',__METHOD__,$arrObjGrupoRedeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objGrupoRedeBD = new GrupoRedeBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjGrupoRedeDTO);$i++){
        $objGrupoRedeBD->desativar($arrObjGrupoRedeDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando Grupo de Rede.',$e);
    }
  }

  protected function reativarControlado($arrObjGrupoRedeDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('grupo_rede_reativar',__METHOD__,$arrObjGrupoRedeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objGrupoRedeBD = new GrupoRedeBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjGrupoRedeDTO);$i++){
        $objGrupoRedeBD->reativar($arrObjGrupoRedeDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando Grupo de Rede.',$e);
    }
  }

  protected function bloquearControlado(GrupoRedeDTO $objGrupoRedeDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('grupo_rede_consultar',__METHOD__,$objGrupoRedeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objGrupoRedeBD = new GrupoRedeBD($this->getObjInfraIBanco());
      $ret = $objGrupoRedeBD->bloquear($objGrupoRedeDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro bloqueando Grupo de Rede.',$e);
    }
  }

 */
}
?>