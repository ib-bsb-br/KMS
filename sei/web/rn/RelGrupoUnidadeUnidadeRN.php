<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 22/09/2014 - criado por bcu
*
* Versão do Gerador de Código: 1.33.1
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class RelGrupoUnidadeUnidadeRN extends InfraRN {

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }

  private function validarNumIdUnidade(RelGrupoUnidadeUnidadeDTO $objRelGrupoUnidadeUnidadeDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objRelGrupoUnidadeUnidadeDTO->getNumIdUnidade())){
      $objInfraException->adicionarValidacao(' não informad.');
    }
  }

  private function validarNumIdGrupoUnidade(RelGrupoUnidadeUnidadeDTO $objRelGrupoUnidadeUnidadeDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objRelGrupoUnidadeUnidadeDTO->getNumIdGrupoUnidade())){
      $objInfraException->adicionarValidacao(' não informad.');
    }
  }

  protected function cadastrarControlado(RelGrupoUnidadeUnidadeDTO $objRelGrupoUnidadeUnidadeDTO) {
    try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('rel_grupo_unidade_unidade_cadastrar',__METHOD__,$objRelGrupoUnidadeUnidadeDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarNumIdUnidade($objRelGrupoUnidadeUnidadeDTO, $objInfraException);
      $this->validarNumIdGrupoUnidade($objRelGrupoUnidadeUnidadeDTO, $objInfraException);

      $objInfraException->lancarValidacoes();

      $objRelGrupoUnidadeUnidadeBD = new RelGrupoUnidadeUnidadeBD($this->getObjInfraIBanco());
      $ret = $objRelGrupoUnidadeUnidadeBD->cadastrar($objRelGrupoUnidadeUnidadeDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando .',$e);
    }
  }

  protected function alterarControlado(RelGrupoUnidadeUnidadeDTO $objRelGrupoUnidadeUnidadeDTO){
    try {

      //Valida Permissao
  	   SessaoSEI::getInstance()->validarAuditarPermissao('rel_grupo_unidade_unidade_alterar',__METHOD__,$objRelGrupoUnidadeUnidadeDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      if ($objRelGrupoUnidadeUnidadeDTO->isSetNumIdUnidade()){
        $this->validarNumIdUnidade($objRelGrupoUnidadeUnidadeDTO, $objInfraException);
      }
      if ($objRelGrupoUnidadeUnidadeDTO->isSetNumIdGrupoUnidade()){
        $this->validarNumIdGrupoUnidade($objRelGrupoUnidadeUnidadeDTO, $objInfraException);
      }

      $objInfraException->lancarValidacoes();

      $objRelGrupoUnidadeUnidadeBD = new RelGrupoUnidadeUnidadeBD($this->getObjInfraIBanco());
      $objRelGrupoUnidadeUnidadeBD->alterar($objRelGrupoUnidadeUnidadeDTO);

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando .',$e);
    }
  }

  protected function excluirControlado($arrObjRelGrupoUnidadeUnidadeDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('rel_grupo_unidade_unidade_excluir',__METHOD__,$arrObjRelGrupoUnidadeUnidadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelGrupoUnidadeUnidadeBD = new RelGrupoUnidadeUnidadeBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjRelGrupoUnidadeUnidadeDTO);$i++){
        $objRelGrupoUnidadeUnidadeBD->excluir($arrObjRelGrupoUnidadeUnidadeDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo .',$e);
    }
  }

  protected function consultarConectado(RelGrupoUnidadeUnidadeDTO $objRelGrupoUnidadeUnidadeDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('rel_grupo_unidade_unidade_consultar',__METHOD__,$objRelGrupoUnidadeUnidadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelGrupoUnidadeUnidadeBD = new RelGrupoUnidadeUnidadeBD($this->getObjInfraIBanco());
      $ret = $objRelGrupoUnidadeUnidadeBD->consultar($objRelGrupoUnidadeUnidadeDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando .',$e);
    }
  }

  protected function listarConectado(RelGrupoUnidadeUnidadeDTO $objRelGrupoUnidadeUnidadeDTO) {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('rel_grupo_unidade_unidade_listar',__METHOD__,$objRelGrupoUnidadeUnidadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelGrupoUnidadeUnidadeBD = new RelGrupoUnidadeUnidadeBD($this->getObjInfraIBanco());
      $ret = $objRelGrupoUnidadeUnidadeBD->listar($objRelGrupoUnidadeUnidadeDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando .',$e);
    }
  }

  protected function contarConectado(RelGrupoUnidadeUnidadeDTO $objRelGrupoUnidadeUnidadeDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('rel_grupo_unidade_unidade_listar',__METHOD__,$objRelGrupoUnidadeUnidadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelGrupoUnidadeUnidadeBD = new RelGrupoUnidadeUnidadeBD($this->getObjInfraIBanco());
      $ret = $objRelGrupoUnidadeUnidadeBD->contar($objRelGrupoUnidadeUnidadeDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro contando .',$e);
    }
  }
/* 
  protected function desativarControlado($arrObjRelGrupoUnidadeUnidadeDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('rel_grupo_unidade_unidade_desativar',__METHOD__,$arrObjRelGrupoUnidadeUnidadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelGrupoUnidadeUnidadeBD = new RelGrupoUnidadeUnidadeBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjRelGrupoUnidadeUnidadeDTO);$i++){
        $objRelGrupoUnidadeUnidadeBD->desativar($arrObjRelGrupoUnidadeUnidadeDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando .',$e);
    }
  }

  protected function reativarControlado($arrObjRelGrupoUnidadeUnidadeDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('rel_grupo_unidade_unidade_reativar',__METHOD__,$arrObjRelGrupoUnidadeUnidadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelGrupoUnidadeUnidadeBD = new RelGrupoUnidadeUnidadeBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjRelGrupoUnidadeUnidadeDTO);$i++){
        $objRelGrupoUnidadeUnidadeBD->reativar($arrObjRelGrupoUnidadeUnidadeDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando .',$e);
    }
  }

  protected function bloquearControlado(RelGrupoUnidadeUnidadeDTO $objRelGrupoUnidadeUnidadeDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('rel_grupo_unidade_unidade_consultar',__METHOD__,$objRelGrupoUnidadeUnidadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelGrupoUnidadeUnidadeBD = new RelGrupoUnidadeUnidadeBD($this->getObjInfraIBanco());
      $ret = $objRelGrupoUnidadeUnidadeBD->bloquear($objRelGrupoUnidadeUnidadeDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro bloqueando .',$e);
    }
  }

 */

  protected function pesquisarConectado(RelGrupoUnidadeUnidadeDTO $objRelGrupoUnidadeUnidadeDTO) {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('rel_grupo_unidade_unidade_listar',__METHOD__,$objRelGrupoUnidadeUnidadeDTO);

      if ($objRelGrupoUnidadeUnidadeDTO->isSetStrPalavrasPesquisa()){

        if (!InfraString::isBolVazia($objRelGrupoUnidadeUnidadeDTO->getStrPalavrasPesquisa())){

          $strPalavrasPesquisa = InfraString::prepararIndexacao($objRelGrupoUnidadeUnidadeDTO->getStrPalavrasPesquisa(),false);

          $arrPalavrasPesquisa = explode(' ',$strPalavrasPesquisa);

          $numPalavrasPesquisa = count($arrPalavrasPesquisa);

          if ($numPalavrasPesquisa){

            for($i=0;$i<$numPalavrasPesquisa;$i++){
              $arrPalavrasPesquisa[$i] = '%'.$arrPalavrasPesquisa[$i].'%';
            }

            if ($numPalavrasPesquisa==1){
              $objRelGrupoUnidadeUnidadeDTO->setStrIdxUnidadeUnidade($arrPalavrasPesquisa[0],InfraDTO::$OPER_LIKE);
            }else{
              $a = array_fill(0,$numPalavrasPesquisa,'IdxUnidadeUnidade');
              $b = array_fill(0,$numPalavrasPesquisa,InfraDTO::$OPER_LIKE);
              $d = array_fill(0,$numPalavrasPesquisa-1,InfraDTO::$OPER_LOGICO_AND);
              $objRelGrupoUnidadeUnidadeDTO->adicionarCriterio($a,$b,$arrPalavrasPesquisa,$d);
            }
          }
        }
      }

      return $this->listar($objRelGrupoUnidadeUnidadeDTO);

    }catch(Exception $e){
      throw new InfraException('Erro pesquisando Grupo de Envio.',$e);
    }
  }

}
?>