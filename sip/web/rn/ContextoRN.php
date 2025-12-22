<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 27/11/2006 - criado por mga
*
*
*/

require_once dirname(__FILE__).'/../Sip.php';

class ContextoRN extends InfraRN {

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSip::getInstance();
  }

  protected function cadastrarControlado(ContextoDTO $objContextoDTO) {
    try{

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('contexto_cadastrar',__METHOD__,$objContextoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

			$this->validarNumIdOrgao($objContextoDTO, $objInfraException);
			$this->validarStrNome($objContextoDTO, $objInfraException);
			$this->validarStrDescricao($objContextoDTO, $objInfraException);
			$this->validarStrBaseDnLdap($objContextoDTO, $objInfraException);
			$this->validarStrSinAtivo($objContextoDTO,$objInfraException);

      $dto = new ContextoDTO();
      $dto->setNumIdOrgao($objContextoDTO->getNumIdOrgao());
      $dto->setStrNome($objContextoDTO->getStrNome());
      if ($this->contar($dto)>0){
        $objInfraException->adicionarValidacao('Já existe um contexto neste órgão com este nome.');        
      }
			
      $objInfraException->lancarValidacoes();

      $objContextoBD = new ContextoBD($this->getObjInfraIBanco());
      $ret = $objContextoBD->cadastrar($objContextoDTO);

      $objReplicacaoContextoDTO = new ReplicacaoContextoDTO();
      $objReplicacaoContextoDTO->setStrStaOperacao('C');
      $objReplicacaoContextoDTO->setNumIdContexto($ret->getNumIdContexto());
      
      $objSistemaRN = new SistemaRN();
      $objSistemaRN->replicarContexto($objReplicacaoContextoDTO);
      
      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando Contexto.',$e);
    }
  }

  protected function alterarControlado(ContextoDTO $objContextoDTO){
    try {

      //Valida Permissao
  	   SessaoSip::getInstance()->validarAuditarPermissao('contexto_alterar',__METHOD__,$objContextoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

	    $this->validarNumIdOrgao($objContextoDTO, $objInfraException);
	    $this->validarStrNome($objContextoDTO, $objInfraException);

      $dto = new ContextoDTO();
      $dto->setNumIdContexto($objContextoDTO->getNumIdContexto(),InfraDTO::$OPER_DIFERENTE);
      $dto->setNumIdOrgao($objContextoDTO->getNumIdOrgao());
      $dto->setStrNome($objContextoDTO->getStrNome());
      if ($this->contar($dto)>0){
        $objInfraException->adicionarValidacao('Existe outro contexto neste órgão com este nome.');        
      }
	    
      if ($objContextoDTO->isSetStrDescricao()){
			  $this->validarStrDescricao($objContextoDTO, $objInfraException);
      }
      
      if ($objContextoDTO->isSetStrBaseDnLdap()){
			  $this->validarStrBaseDnLdap($objContextoDTO, $objInfraException);
      }
      
      if ($objContextoDTO->isSetStrSinAtivo()){
			  $this->validarStrSinAtivo($objContextoDTO,$objInfraException);
      }

			
      $objInfraException->lancarValidacoes();

      $objContextoBD = new ContextoBD($this->getObjInfraIBanco());
      $objContextoBD->alterar($objContextoDTO);

      $objReplicacaoContextoDTO = new ReplicacaoContextoDTO();
      $objReplicacaoContextoDTO->setStrStaOperacao('A');
      $objReplicacaoContextoDTO->setNumIdContexto($objContextoDTO->getNumIdContexto());
      
      $objSistemaRN = new SistemaRN();
      $objSistemaRN->replicarContexto($objReplicacaoContextoDTO);
      
      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando Contexto.',$e);
    }
  }

  protected function excluirControlado($arrObjContextoDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('contexto_excluir',__METHOD__,$arrObjContextoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objLoginRN = new LoginRN();
      $objSistemaRN = new SistemaRN();
      
      for($i=0;$i<count($arrObjContextoDTO);$i++){
      	$objLoginDTO = new LoginDTO();
      	$objLoginDTO->retStrIdLogin();
      	$objLoginDTO->retNumIdUsuario();
      	$objLoginDTO->retNumIdContexto();
      	$objLoginDTO->retNumIdSistema();

      	$objLoginDTO->setNumIdContexto($arrObjContextoDTO[$i]->getNumIdContexto());
      	$objLoginRN->excluir($objLoginRN->listar($objLoginDTO));

      	$objReplicacaoContextoDTO = new ReplicacaoContextoDTO();
        $objReplicacaoContextoDTO->setStrStaOperacao('E');
        $objReplicacaoContextoDTO->setNumIdContexto($arrObjContextoDTO[$i]->getNumIdContexto());
        $objSistemaRN->replicarContexto($objReplicacaoContextoDTO);
      }
      
      $objContextoBD = new ContextoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjContextoDTO);$i++){
        $objContextoBD->excluir($arrObjContextoDTO[$i]);
      }
      
      
      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo Contexto.',$e);
    }
  }

  protected function desativarControlado($arrObjContextoDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('contexto_desativar',__METHOD__,$arrObjContextoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objContextoBD = new ContextoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjContextoDTO);$i++){
        $objContextoBD->desativar($arrObjContextoDTO[$i]);
      }

      $objSistemaRN = new SistemaRN();
      for($i=0;$i<count($arrObjContextoDTO);$i++){
      	$objReplicacaoContextoDTO = new ReplicacaoContextoDTO();
        $objReplicacaoContextoDTO->setStrStaOperacao('D');
        $objReplicacaoContextoDTO->setNumIdContexto($arrObjContextoDTO[$i]->getNumIdContexto());
        $objSistemaRN->replicarContexto($objReplicacaoContextoDTO);
      }
            
      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando Contexto.',$e);
    }
  }

  protected function reativarControlado($arrObjContextoDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('contexto_reativar',__METHOD__,$arrObjContextoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objContextoBD = new ContextoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjContextoDTO);$i++){
        $objContextoBD->reativar($arrObjContextoDTO[$i]);
      }

      $objSistemaRN = new SistemaRN();
      for($i=0;$i<count($arrObjContextoDTO);$i++){
      	$objReplicacaoContextoDTO = new ReplicacaoContextoDTO();
        $objReplicacaoContextoDTO->setStrStaOperacao('R');
        $objReplicacaoContextoDTO->setNumIdContexto($arrObjContextoDTO[$i]->getNumIdContexto());
        $objSistemaRN->replicarContexto($objReplicacaoContextoDTO);
      }
            
      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando Contexto.',$e);
    }
  }
  
  protected function consultarConectado(ContextoDTO $objContextoDTO){
    try {

			/////////////////////////////////////////////////////////////////
			//SessaoSip::getInstance()->validarAuditarPermissao('contexto_consultar',__METHOD__,$objContextoDTO);
			/////////////////////////////////////////////////////////////////

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

			/////////////////////////////////////////////////////////////////
      //SessaoSip::getInstance()->validarAuditarPermissao('contexto_listar',__METHOD__,$objContextoDTO);
			/////////////////////////////////////////////////////////////////

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

  protected function contarConectado(ContextoDTO $objContextoDTO) {
    try {
      ////////////////////////////////////////////////////////////////////// 
      //SessaoSip::getInstance()->validarAuditarPermissao('contexto_contar',__METHOD__,$objContextoDTO);
			//////////////////////////////////////////////////////////////////////


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
  
  private function validarNumIdOrgao(ContextoDTO $objContextoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContextoDTO->getNumIdOrgao())){
      $objInfraException->adicionarValidacao('Órgão não informado.');
    }
  }

  private function validarStrNome(ContextoDTO $objContextoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContextoDTO->getStrNome())){
      $objInfraException->adicionarValidacao('Nome não informado.');
    }
  }
  
  private function validarStrDescricao(ContextoDTO $objContextoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContextoDTO->getStrDescricao())){
      $objContextoDTO->setStrDescricao(null);
    }
  }
	
  private function validarStrBaseDnLdap(ContextoDTO $objContextoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContextoDTO->getStrBaseDnLdap())){
      $objInfraException->adicionarValidacao('Base DN LDAP não informada.');
    }
  }
	
  private function validarStrSinAtivo(ContextoDTO $objContextoDTO, InfraException $objInfraException){
    if ($objContextoDTO->getStrSinAtivo()===null || ($objContextoDTO->getStrSinAtivo()!=='S' && $objContextoDTO->getStrSinAtivo()!=='N')){
      $objInfraException->adicionarValidacao('Sinalizador de Exclusão Lógica inválido.');
    }
  }
	
}
?>