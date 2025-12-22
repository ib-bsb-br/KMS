<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 20/08/2009 - criado por mga
*
* Versão do Gerador de Código: 1.28.0
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../Sip.php';

class RelGrupoRedeUnidadeRN extends InfraRN {

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSip::getInstance();
  }

  private function validarNumIdGrupoRede(RelGrupoRedeUnidadeDTO $objRelGrupoRedeUnidadeDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objRelGrupoRedeUnidadeDTO->getNumIdGrupoRede())){
      $objInfraException->adicionarValidacao('Grupo de Rede não informado.');
    }else{
      
      $dto = new GrupoRedeDTO();
      $dto->retStrSinExcecao();
      $dto->setNumIdGrupoRede($objRelGrupoRedeUnidadeDTO->getNumIdGrupoRede());
      
      $objGrupoRedeRN = new GrupoRedeRN();
      $dto = $objGrupoRedeRN->consultar($dto);
      
      if ($dto->getStrSinExcecao()=='S'){
        $objInfraException->adicionarValidacao('O grupo de rede não pode ser utilizado porque é uma exceção.');				  
      }
      
      
			$dto = new RelGrupoRedeUnidadeDTO();
			$dto->setNumIdGrupoRede($objRelGrupoRedeUnidadeDTO->getNumIdGrupoRede());
			$dto->setNumIdUnidade($objRelGrupoRedeUnidadeDTO->getNumIdUnidade());
			$objRelGrupoRedeUnidadeRN = new RelGrupoRedeUnidadeRN();
			if ($objRelGrupoRedeUnidadeRN->contar($dto)>0){
        $objInfraException->adicionarValidacao('O grupo de rede já está associado com esta unidade.');				  
			}

			$dto = new RelGrupoRedeUnidadeDTO();
			$dto->retStrSiglaUnidade();
			$dto->setNumIdGrupoRede($objRelGrupoRedeUnidadeDTO->getNumIdGrupoRede());
			$objRelGrupoRedeUnidadeRN = new RelGrupoRedeUnidadeRN();
			$dto = $objRelGrupoRedeUnidadeRN->consultar($dto);
			if ($dto!=null){
			  $objInfraException->adicionarValidacao('O grupo de rede já está associado com a unidade '.$dto->getStrSiglaUnidade().'.');
			}
    }
  }

  private function validarNumIdUnidade(RelGrupoRedeUnidadeDTO $objRelGrupoRedeUnidadeDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objRelGrupoRedeUnidadeDTO->getNumIdUnidade())){
      $objInfraException->adicionarValidacao('Unidade não informada.');
    }
  }

  private function validarOrgaoGrupoRedeUnidade(RelGrupoRedeUnidadeDTO $objRelGrupoRedeUnidadeDTO, InfraException $objInfraException){
    $objUnidadeDTO = new UnidadeDTO();
    $objUnidadeDTO->retNumIdOrgao();
    $objUnidadeDTO->setNumIdUnidade($objRelGrupoRedeUnidadeDTO->getNumIdUnidade());
    
    $objUnidadeRN = new UnidadeRN();
    $objUnidadeDTO = $objUnidadeRN->consultar($objUnidadeDTO);
    
    $objGrupoRedeDTO = new GrupoRedeDTO();
    $objGrupoRedeDTO->retNumIdOrgao();
    $objGrupoRedeDTO->setNumIdGrupoRede($objRelGrupoRedeUnidadeDTO->getNumIdGrupoRede());
    
    $objGrupoRedeRN = new GrupoRedeRN();
    $objGrupoRedeDTO = $objGrupoRedeRN->consultar($objGrupoRedeDTO);
    
    if ($objUnidadeDTO->getNumIdOrgao() != $objGrupoRedeDTO->getNumIdOrgao()){
      $objInfraException->adicionarValidacao('Órgãos do grupo de rede e da unidade são diferentes.');
    }
    
  }
  
  protected function cadastrarControlado(RelGrupoRedeUnidadeDTO $objRelGrupoRedeUnidadeDTO) {
    try{

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('rel_grupo_rede_unidade_cadastrar',__METHOD__,$objRelGrupoRedeUnidadeDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarNumIdGrupoRede($objRelGrupoRedeUnidadeDTO, $objInfraException);
      $this->validarNumIdUnidade($objRelGrupoRedeUnidadeDTO, $objInfraException);
      $this->validarOrgaoGrupoRedeUnidade($objRelGrupoRedeUnidadeDTO, $objInfraException);

      $objInfraException->lancarValidacoes();

      $objRelGrupoRedeUnidadeBD = new RelGrupoRedeUnidadeBD($this->getObjInfraIBanco());
      $ret = $objRelGrupoRedeUnidadeBD->cadastrar($objRelGrupoRedeUnidadeDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando Mapeamento de Grupo de Rede.',$e);
    }
  }
  /*
  protected function alterarControlado(RelGrupoRedeUnidadeDTO $objRelGrupoRedeUnidadeDTO){
    try {

      //Valida Permissao
  	   SessaoSip::getInstance()->validarAuditarPermissao('rel_grupo_rede_unidade_alterar',__METHOD__,$objRelGrupoRedeUnidadeDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      if ($objRelGrupoRedeUnidadeDTO->isSetNumIdGrupoRede()){
        $this->validarNumIdGrupoRede($objRelGrupoRedeUnidadeDTO, $objInfraException);
      }
      if ($objRelGrupoRedeUnidadeDTO->isSetNumIdUnidade()){
        $this->validarNumIdUnidade($objRelGrupoRedeUnidadeDTO, $objInfraException);
      }

      $objInfraException->lancarValidacoes();

      $objRelGrupoRedeUnidadeBD = new RelGrupoRedeUnidadeBD($this->getObjInfraIBanco());
      $objRelGrupoRedeUnidadeBD->alterar($objRelGrupoRedeUnidadeDTO);

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando Mapeamento de Grupo de Rede.',$e);
    }
  }
  */
  protected function excluirControlado($arrObjRelGrupoRedeUnidadeDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('rel_grupo_rede_unidade_excluir',__METHOD__,$arrObjRelGrupoRedeUnidadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelGrupoRedeUnidadeBD = new RelGrupoRedeUnidadeBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjRelGrupoRedeUnidadeDTO);$i++){
        $objRelGrupoRedeUnidadeBD->excluir($arrObjRelGrupoRedeUnidadeDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo Mapeamento de Grupo de Rede.',$e);
    }
  }

  protected function consultarConectado(RelGrupoRedeUnidadeDTO $objRelGrupoRedeUnidadeDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('rel_grupo_rede_unidade_consultar',__METHOD__,$objRelGrupoRedeUnidadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelGrupoRedeUnidadeBD = new RelGrupoRedeUnidadeBD($this->getObjInfraIBanco());
      $ret = $objRelGrupoRedeUnidadeBD->consultar($objRelGrupoRedeUnidadeDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando Mapeamento de Grupo de Rede.',$e);
    }
  }

  protected function listarConectado(RelGrupoRedeUnidadeDTO $objRelGrupoRedeUnidadeDTO) {
    try {

      //Valida Permissao
      //SessaoSip::getInstance()->validarAuditarPermissao('rel_grupo_rede_unidade_listar',__METHOD__,$objRelGrupoRedeUnidadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelGrupoRedeUnidadeBD = new RelGrupoRedeUnidadeBD($this->getObjInfraIBanco());
      $ret = $objRelGrupoRedeUnidadeBD->listar($objRelGrupoRedeUnidadeDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Mapeamentos de Grupos de Rede.',$e);
    }
  }

  protected function contarConectado(RelGrupoRedeUnidadeDTO $objRelGrupoRedeUnidadeDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('rel_grupo_rede_unidade_listar',__METHOD__,$objRelGrupoRedeUnidadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelGrupoRedeUnidadeBD = new RelGrupoRedeUnidadeBD($this->getObjInfraIBanco());
      $ret = $objRelGrupoRedeUnidadeBD->contar($objRelGrupoRedeUnidadeDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro contando Mapeamentos de Grupos de Rede.',$e);
    }
  }
/* 
  protected function desativarControlado($arrObjRelGrupoRedeUnidadeDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('rel_grupo_rede_unidade_desativar',__METHOD__,$arrObjRelGrupoRedeUnidadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelGrupoRedeUnidadeBD = new RelGrupoRedeUnidadeBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjRelGrupoRedeUnidadeDTO);$i++){
        $objRelGrupoRedeUnidadeBD->desativar($arrObjRelGrupoRedeUnidadeDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando Mapeamento de Grupo de Rede.',$e);
    }
  }

  protected function reativarControlado($arrObjRelGrupoRedeUnidadeDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('rel_grupo_rede_unidade_reativar',__METHOD__,$arrObjRelGrupoRedeUnidadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelGrupoRedeUnidadeBD = new RelGrupoRedeUnidadeBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjRelGrupoRedeUnidadeDTO);$i++){
        $objRelGrupoRedeUnidadeBD->reativar($arrObjRelGrupoRedeUnidadeDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando Mapeamento de Grupo de Rede.',$e);
    }
  }

  protected function bloquearControlado(RelGrupoRedeUnidadeDTO $objRelGrupoRedeUnidadeDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('rel_grupo_rede_unidade_consultar',__METHOD__,$objRelGrupoRedeUnidadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelGrupoRedeUnidadeBD = new RelGrupoRedeUnidadeBD($this->getObjInfraIBanco());
      $ret = $objRelGrupoRedeUnidadeBD->bloquear($objRelGrupoRedeUnidadeDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro bloqueando Mapeamento de Grupo de Rede.',$e);
    }
  }

 */
}
?>