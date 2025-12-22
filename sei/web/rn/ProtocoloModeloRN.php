<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 16/08/2012 - criado por mkr@trf4.jus.br
*
* Versão do Gerador de Código: 1.33.0
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class ProtocoloModeloRN extends InfraRN {

  public static $TF_TODOS = 'T';
  public static $TF_MEUS = 'M';
  
  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }

  private function validarNumIdGrupoProtocoloModelo(ProtocoloModeloDTO $objProtocoloModeloDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objProtocoloModeloDTO->getNumIdGrupoProtocoloModelo())){
      $objProtocoloModeloDTO->setNumIdGrupoProtocoloModelo(null);
    }
  }

  private function validarNumIdUnidade(ProtocoloModeloDTO $objProtocoloModeloDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objProtocoloModeloDTO->getNumIdUnidade())){
      $objInfraException->adicionarValidacao('Unidade não informada.');
    }
  }

  private function validarNumIdUsuario(ProtocoloModeloDTO $objProtocoloModeloDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objProtocoloModeloDTO->getNumIdUsuario())){
      $objInfraException->adicionarValidacao('Usuário não informado.');
    }
  }

  private function validarDblIdProtocolo(ProtocoloModeloDTO $objProtocoloModeloDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objProtocoloModeloDTO->getDblIdProtocolo())){
      $objInfraException->adicionarValidacao('Documento não informado.');
    }
  }

  private function validarStrDescricao(ProtocoloModeloDTO $objProtocoloModeloDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objProtocoloModeloDTO->getStrDescricao())){
      $objInfraException->adicionarValidacao('Descrição não informada.');
    }else{
      $objProtocoloModeloDTO->setStrDescricao(trim($objProtocoloModeloDTO->getStrDescricao()));

      if (strlen($objProtocoloModeloDTO->getStrDescricao())>$this->getNumMaxTamanhoDescricao()){
        $objInfraException->adicionarValidacao('Descrição possui tamanho superior a '.$this->getNumMaxTamanhoDescricao().' caracteres.');
      }
    }
  }

  public function getNumMaxTamanhoDescricao(){
    return 250;
  }

  protected function cadastrarControlado(ProtocoloModeloDTO $objProtocoloModeloDTO) {
    try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('protocolo_modelo_cadastrar',__METHOD__,$objProtocoloModeloDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarNumIdGrupoProtocoloModelo($objProtocoloModeloDTO, $objInfraException);
      $this->validarNumIdUnidade($objProtocoloModeloDTO, $objInfraException);
      $this->validarNumIdUsuario($objProtocoloModeloDTO, $objInfraException);
      $this->validarDblIdProtocolo($objProtocoloModeloDTO, $objInfraException);
      $this->validarStrDescricao($objProtocoloModeloDTO, $objInfraException);

      $objInfraException->lancarValidacoes();

///
	    $objProtocoloDTO = new ProtocoloDTO();
	    $objProtocoloDTO->retStrStaProtocolo();
	    $objProtocoloDTO->retStrProtocoloFormatado();
      $objProtocoloDTO->retStrStaNivelAcessoGlobal();
	    $objProtocoloDTO->setDblIdProtocolo($objProtocoloModeloDTO->getDblIdProtocolo());
	      
	    $objProtocoloRN = new ProtocoloRN();
	    $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);
	    
	    if ($objProtocoloDTO==null){
	    	$objInfraException->lancarValidacao('Protocolo não encontrado.');
	    }

	    if ($objProtocoloDTO->getStrStaProtocolo()!=ProtocoloRN::$TP_DOCUMENTO_GERADO){
	      $objInfraException->adicionarValidacao('Protocolo '.$objProtocoloDTO->getStrProtocoloFormatado().' não é um documento gerado.');
	    }
	    
     	if ($objProtocoloDTO->getStrStaNivelAcessoGlobal()==ProtocoloRN::$NA_SIGILOSO){
     		$objInfraException->adicionarValidacao('Documento sigiloso '.$objProtocoloDTO->getStrProtocoloFormatado().' não pode ser adicionado como modelo.');
     	} 
      
     	$objDocumentoDTO = new DocumentoDTO();
     	$objDocumentoDTO->retStrStaDocumento();
     	$objDocumentoDTO->setDblIdDocumento($objProtocoloModeloDTO->getDblIdProtocolo());
     	
     	$objDocumentoRN = new DocumentoRN();
     	$objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO); 
     	
     	if ($objDocumentoDTO->getStrStaDocumento()!=DocumentoRN::$TD_EDITOR_INTERNO){
     		$objInfraException->adicionarValidacao('Documento '.$objProtocoloDTO->getStrProtocoloFormatado().' não foi gerado com o editor interno.');
     	}
     	
     	$objInfraException->lancarValidacoes();
      
     	$objProtocoloModeloDTO->setDthGeracao(InfraData::getStrDataHoraAtual());
     	
      $objProtocoloModeloBD = new ProtocoloModeloBD($this->getObjInfraIBanco());
      $ret = $objProtocoloModeloBD->cadastrar($objProtocoloModeloDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando Modelo.',$e);
    }
  }

  protected function alterarControlado(ProtocoloModeloDTO $objProtocoloModeloDTO){
    try {

      //Valida Permissao
  	   SessaoSEI::getInstance()->validarAuditarPermissao('protocolo_modelo_alterar',__METHOD__,$objProtocoloModeloDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      if ($objProtocoloModeloDTO->isSetNumIdGrupoProtocoloModelo()){
        $this->validarNumIdGrupoProtocoloModelo($objProtocoloModeloDTO, $objInfraException);
      }
      if ($objProtocoloModeloDTO->isSetNumIdUnidade()){
        $this->validarNumIdUnidade($objProtocoloModeloDTO, $objInfraException);
      }
      if ($objProtocoloModeloDTO->isSetNumIdUsuario()){
        $this->validarNumIdUsuario($objProtocoloModeloDTO, $objInfraException);
      }
      if ($objProtocoloModeloDTO->isSetDblIdProtocolo()){
        $this->validarDblIdProtocolo($objProtocoloModeloDTO, $objInfraException);
      }
      if ($objProtocoloModeloDTO->isSetStrDescricao()){
        $this->validarStrDescricao($objProtocoloModeloDTO, $objInfraException);
      }

      $objInfraException->lancarValidacoes();

      $objProtocoloModeloBD = new ProtocoloModeloBD($this->getObjInfraIBanco());
      $objProtocoloModeloBD->alterar($objProtocoloModeloDTO);

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando Modelo.',$e);
    }
  }

  protected function excluirControlado($arrObjProtocoloModeloDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('protocolo_modelo_excluir',__METHOD__,$arrObjProtocoloModeloDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objProtocoloModeloBD = new ProtocoloModeloBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjProtocoloModeloDTO);$i++){
        $objProtocoloModeloBD->excluir($arrObjProtocoloModeloDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo Modelo.',$e);
    }
  }

  protected function consultarConectado(ProtocoloModeloDTO $objProtocoloModeloDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('protocolo_modelo_consultar',__METHOD__,$objProtocoloModeloDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objProtocoloModeloBD = new ProtocoloModeloBD($this->getObjInfraIBanco());
      $ret = $objProtocoloModeloBD->consultar($objProtocoloModeloDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando Modelo.',$e);
    }
  }

  protected function listarConectado(ProtocoloModeloDTO $objProtocoloModeloDTO) {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('protocolo_modelo_listar',__METHOD__,$objProtocoloModeloDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objProtocoloModeloBD = new ProtocoloModeloBD($this->getObjInfraIBanco());
      $ret = $objProtocoloModeloBD->listar($objProtocoloModeloDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Modelos.',$e);
    }
  }
  
  protected function listarModelosUnidadeControlado(ProtocoloModeloDTO $objProtocoloModeloDTO){
    try {
  
      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('protocolo_modelo_listar',__METHOD__,$objProtocoloModeloDTO);
  
      //Regras de Negocio
      //$objInfraException = new InfraException();
  
      //$objInfraException->lancarValidacoes();
  
      $objProtocoloModeloDTO->retDblIdProtocoloModelo();
      $objProtocoloModeloDTO->retNumIdUnidade();
      $objProtocoloModeloDTO->retNumIdGrupoProtocoloModelo();
      $objProtocoloModeloDTO->retDblIdProtocolo();
      $objProtocoloModeloDTO->retNumIdUsuario();      
      $objProtocoloModeloDTO->retStrDescricao();      
      $objProtocoloModeloDTO->retStrNomeGrupoProtocoloModelo();
      $objProtocoloModeloDTO->retStrNomeUsuario();
      $objProtocoloModeloDTO->retStrSiglaUsuario();
      $objProtocoloModeloDTO->retStrProtocoloFormatado();
      $objProtocoloModeloDTO->retStrNomeSerie();
      $objProtocoloModeloDTO->retDthGeracao();
  
      $objProtocoloModeloDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
  
      if ($objProtocoloModeloDTO->getStrStaTipoFiltro()==ProtocoloModeloRN::$TF_MEUS){
        $objProtocoloModeloDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
      }
      
  
      $objProtocoloModeloRN = new ProtocoloModeloRN();
      $arrObjProtocoloModeloDTO = $objProtocoloModeloRN->listar($objProtocoloModeloDTO);
  
  
      if (count($arrObjProtocoloModeloDTO)>0){
  
        $objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();
        $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_DOCUMENTOS_GERADOS);
        $objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_TODOS_EXCETO_SIGILOSOS_SEM_ACESSO);
        $objPesquisaProtocoloDTO->setDblIdProtocolo(InfraArray::converterArrInfraDTO($arrObjProtocoloModeloDTO,'IdProtocolo'));
  
        $objProtocoloRN = new ProtocoloRN();
        $arrObjProtocoloDTO = InfraArray::indexarArrInfraDTO($objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO),'IdProtocolo');
      }
       
      $arrRet = array();
      foreach($arrObjProtocoloModeloDTO as $dto){
        //se tem acesso
        if (isset($arrObjProtocoloDTO[$dto->getDblIdProtocolo()])){
          $arrRet[] = $dto;
        }
      }
      
      return $arrRet;
  
  
    }catch(Exception $e){
      throw new InfraException('Erro listando modelos da unidade.',$e);
    }
  }

  protected function contarConectado(ProtocoloModeloDTO $objProtocoloModeloDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('protocolo_modelo_listar',__METHOD__,$objProtocoloModeloDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objProtocoloModeloBD = new ProtocoloModeloBD($this->getObjInfraIBanco());
      $ret = $objProtocoloModeloBD->contar($objProtocoloModeloDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro contando Modelos.',$e);
    }
  }
/* 
  protected function desativarControlado($arrObjProtocoloModeloDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('protocolo_modelo_desativar');

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objProtocoloModeloBD = new ProtocoloModeloBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjProtocoloModeloDTO);$i++){
        $objProtocoloModeloBD->desativar($arrObjProtocoloModeloDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando Modelo.',$e);
    }
  }

  protected function reativarControlado($arrObjProtocoloModeloDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('protocolo_modelo_reativar');

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objProtocoloModeloBD = new ProtocoloModeloBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjProtocoloModeloDTO);$i++){
        $objProtocoloModeloBD->reativar($arrObjProtocoloModeloDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando Modelo.',$e);
    }
  }

  protected function bloquearControlado(ProtocoloModeloDTO $objProtocoloModeloDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('protocolo_modelo_consultar');

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objProtocoloModeloBD = new ProtocoloModeloBD($this->getObjInfraIBanco());
      $ret = $objProtocoloModeloBD->bloquear($objProtocoloModeloDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro bloqueando Modelo.',$e);
    }
  }

 */
}
?>