<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 03/12/2009 - criado por mga
*
* Versão do Gerador de Código: 1.29.1
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class ContextoRN extends InfraRN {

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }

  private function validarNumIdOrgao(ContextoDTO $objContextoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContextoDTO->getNumIdOrgao())){
      $objInfraException->adicionarValidacao('Órgão não informado.');
    }
  }

  private function validarStrNome(ContextoDTO $objContextoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContextoDTO->getStrNome())){
      $objInfraException->adicionarValidacao('Nome não informado.');
    }else{
      $objContextoDTO->setStrNome(trim($objContextoDTO->getStrNome()));

      if (strlen($objContextoDTO->getStrNome())>50){
        $objInfraException->adicionarValidacao('Nome possui tamanho superior a 50 caracteres.');
      }
    }
  }

  private function validarStrDescricao(ContextoDTO $objContextoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContextoDTO->getStrDescricao())){
      $objContextoDTO->setStrDescricao(null);
    }else{
      $objContextoDTO->setStrDescricao(trim($objContextoDTO->getStrDescricao()));

      if (strlen($objContextoDTO->getStrDescricao())>200){
        $objInfraException->adicionarValidacao('Descrição possui tamanho superior a 200 caracteres.');
      }
    }
  }

  private function validarStrBaseDnLdap(ContextoDTO $objContextoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContextoDTO->getStrBaseDnLdap())){
      $objInfraException->adicionarValidacao('Base DN LDAP não informada.');
    }else{
      $objContextoDTO->setStrBaseDnLdap(trim($objContextoDTO->getStrBaseDnLdap()));

      if (strlen($objContextoDTO->getStrBaseDnLdap())>50){
        $objInfraException->adicionarValidacao('Base DN LDAP possui tamanho superior a 50 caracteres.');
      }
    }
  }

  private function validarStrSinAtivo(ContextoDTO $objContextoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContextoDTO->getStrSinAtivo())){
      $objInfraException->adicionarValidacao('Sinalizador de Exclusão Lógica não informado.');
    }else{
      if (!InfraUtil::isBolSinalizadorValido($objContextoDTO->getStrSinAtivo())){
        $objInfraException->adicionarValidacao('Sinalizador de Exclusão Lógica inválido.');
      }
    }
  }

  protected function cadastrarControlado(ContextoDTO $objContextoDTO) {
    try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('contexto_cadastrar',__METHOD__,$objContextoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarNumIdOrgao($objContextoDTO, $objInfraException);
      $this->validarStrNome($objContextoDTO, $objInfraException);
      $this->validarStrDescricao($objContextoDTO, $objInfraException);
      $this->validarStrBaseDnLdap($objContextoDTO, $objInfraException);
      $this->validarStrSinAtivo($objContextoDTO, $objInfraException);

      $objInfraException->lancarValidacoes();

      $objContextoBD = new ContextoBD($this->getObjInfraIBanco());
      $ret = $objContextoBD->cadastrar($objContextoDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando Contexto.',$e);
    }
  }

  protected function alterarControlado(ContextoDTO $objContextoDTO){
    try {

      //Valida Permissao
  	   SessaoSEI::getInstance()->validarAuditarPermissao('contexto_alterar',__METHOD__,$objContextoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      if ($objContextoDTO->isSetNumIdOrgao()){
        $this->validarNumIdOrgao($objContextoDTO, $objInfraException);
      }
      if ($objContextoDTO->isSetStrNome()){
        $this->validarStrNome($objContextoDTO, $objInfraException);
      }
      if ($objContextoDTO->isSetStrDescricao()){
        $this->validarStrDescricao($objContextoDTO, $objInfraException);
      }
      if ($objContextoDTO->isSetStrBaseDnLdap()){
        $this->validarStrBaseDnLdap($objContextoDTO, $objInfraException);
      }
      if ($objContextoDTO->isSetStrSinAtivo()){
        $this->validarStrSinAtivo($objContextoDTO, $objInfraException);
      }

      $objInfraException->lancarValidacoes();

      $objContextoBD = new ContextoBD($this->getObjInfraIBanco());
      $objContextoBD->alterar($objContextoDTO);

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando Contexto.',$e);
    }
  }

  protected function excluirControlado($arrObjContextoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('contexto_excluir',__METHOD__,$arrObjContextoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objContextoBD = new ContextoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjContextoDTO);$i++){
        $objContextoBD->excluir($arrObjContextoDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo Contexto.',$e);
    }
  }

  protected function consultarConectado(ContextoDTO $objContextoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('contexto_consultar',__METHOD__,$objContextoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objContextoBD = new ContextoBD($this->getObjInfraIBanco());
      $ret = $objContextoBD->consultar($objContextoDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando Contexto.',$e);
    }
  }

  protected function listarConectado(ContextoDTO $objContextoDTO) {
    try {

      //Valida Permissao
      //SessaoSEI::getInstance()->validarAuditarPermissao('contexto_listar',__METHOD__,$objContextoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objContextoBD = new ContextoBD($this->getObjInfraIBanco());
      $ret = $objContextoBD->listar($objContextoDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Contextos.',$e);
    }
  }

  protected function contarConectado(ContextoDTO $objContextoDTO){
    try {

      //Valida Permissao
      //SessaoSEI::getInstance()->validarAuditarPermissao('contexto_listar',__METHOD__,$objContextoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objContextoBD = new ContextoBD($this->getObjInfraIBanco());
      $ret = $objContextoBD->contar($objContextoDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro contando Contextos.',$e);
    }
  }

  protected function desativarControlado($arrObjContextoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('contexto_desativar',__METHOD__,$arrObjContextoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objContextoBD = new ContextoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjContextoDTO);$i++){
        $objContextoBD->desativar($arrObjContextoDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando Contexto.',$e);
    }
  }

  protected function reativarControlado($arrObjContextoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('contexto_reativar',__METHOD__,$arrObjContextoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objContextoBD = new ContextoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjContextoDTO);$i++){
        $objContextoBD->reativar($arrObjContextoDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando Contexto.',$e);
    }
  }

  protected function bloquearControlado(ContextoDTO $objContextoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('contexto_consultar',__METHOD__,$objContextoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objContextoBD = new ContextoBD($this->getObjInfraIBanco());
      $ret = $objContextoBD->bloquear($objContextoDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro bloqueando Contexto.',$e);
    }
  }


}
?>