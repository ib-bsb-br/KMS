<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 05/11/2010 - criado por jonatas_db
*
* Versão do Gerador de Código: 1.30.0
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class AcompanhamentoRN extends InfraRN {

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }

  private function validarNumIdUnidade(AcompanhamentoDTO $objAcompanhamentoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAcompanhamentoDTO->getNumIdUnidade())){
      $objInfraException->adicionarValidacao('Unidade não informada.');
    }
  }

  private function validarNumIdGrupoAcompanhamento(AcompanhamentoDTO $objAcompanhamentoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAcompanhamentoDTO->getNumIdGrupoAcompanhamento())){
      $objAcompanhamentoDTO->setNumIdGrupoAcompanhamento(null);
    }
  }

  private function validarDblIdProtocolo(AcompanhamentoDTO $objAcompanhamentoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAcompanhamentoDTO->getDblIdProtocolo())){
      $objInfraException->adicionarValidacao('Protocolo não informado.');
    }
    
    $dto = new AcompanhamentoDTO();
    $dto->retNumIdAcompanhamento();
    $dto->setNumIdAcompanhamento($objAcompanhamentoDTO->getNumIdAcompanhamento(),InfraDTO::$OPER_DIFERENTE);
    $dto->setDblIdProtocolo($objAcompanhamentoDTO->getDblIdProtocolo());
    $dto->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
    $dto->setNumMaxRegistrosRetorno(1);
    
    if ($this->consultar($dto) != null){
    	$objInfraException->lancarValidacao('Processo já consta na lista de Acompanhamentos Especiais da unidade.');
    }
  }

  private function validarNumIdUsuarioGerador(AcompanhamentoDTO $objAcompanhamentoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAcompanhamentoDTO->getNumIdUsuarioGerador())){
      $objInfraException->adicionarValidacao('Usuário Gerador não informado.');
    }
  }

  private function validarDthGeracao(AcompanhamentoDTO $objAcompanhamentoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAcompanhamentoDTO->getDthGeracao())){
      $objInfraException->adicionarValidacao('Data de Geração não informado.');
    }else{
      if (!InfraData::validarDataHora($objAcompanhamentoDTO->getDthGeracao())){
        $objInfraException->adicionarValidacao('Data de Geração inválido.');
      }
    }
  }

  private function validarStrObservacao(AcompanhamentoDTO $objAcompanhamentoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAcompanhamentoDTO->getStrObservacao())){
      $objAcompanhamentoDTO->setStrObservacao(null);
    }else{
      $objAcompanhamentoDTO->setStrObservacao(trim($objAcompanhamentoDTO->getStrObservacao()));
      $objAcompanhamentoDTO->setStrObservacao(InfraUtil::filtrarISO88591($objAcompanhamentoDTO->getStrObservacao()));

      if (strlen($objAcompanhamentoDTO->getStrObservacao())>$this->getNumMaxTamanhoObservacao()){
        $objInfraException->adicionarValidacao('Observação possui tamanho superior a '.$this->getNumMaxTamanhoObservacao().' caracteres.');
      }
    }
  }

  public function getNumMaxTamanhoObservacao(){
    return 250;
  }

  private function validarNumTipoVisualizacao(AcompanhamentoDTO $objAcompanhamentoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAcompanhamentoDTO->getNumTipoVisualizacao())){
      $objInfraException->adicionarValidacao('Tipo de visualização não informado.');
    }
  }

  protected function cadastrarControlado(AcompanhamentoDTO $objAcompanhamentoDTO) {
    try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acompanhamento_cadastrar',__METHOD__,$objAcompanhamentoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarNumIdUnidade($objAcompanhamentoDTO, $objInfraException);
      $this->validarNumIdGrupoAcompanhamento($objAcompanhamentoDTO, $objInfraException);
      $this->validarDblIdProtocolo($objAcompanhamentoDTO, $objInfraException);
      $this->validarNumIdUsuarioGerador($objAcompanhamentoDTO, $objInfraException);
      $this->validarDthGeracao($objAcompanhamentoDTO, $objInfraException);
      $this->validarStrObservacao($objAcompanhamentoDTO, $objInfraException);
      $objAcompanhamentoDTO->setNumTipoVisualizacao(AtividadeRN::$TV_VISUALIZADO);

	    $objProtocoloDTO = new ProtocoloDTO();
	    $objProtocoloDTO->retStrProtocoloFormatado();
      $objProtocoloDTO->retStrStaNivelAcessoGlobal();
	    $objProtocoloDTO->setDblIdProtocolo($objAcompanhamentoDTO->getDblIdProtocolo());
	      
	    $objProtocoloRN = new ProtocoloRN();
	    $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);
	    
	    if ($objProtocoloDTO==null){
	    	$objInfraException->lancarValidacao('Processo não encontrado.');
	    }

     	if ($objProtocoloDTO->getStrStaNivelAcessoGlobal()==ProtocoloRN::$NA_SIGILOSO){
     		$objInfraException->adicionarValidacao('Processo sigiloso '.$objProtocoloDTO->getStrProtocoloFormatado().' não pode ser adicionado no Acompanhamento Especial.');
     	}
      
      $objInfraException->lancarValidacoes();

      $objAcompanhamentoBD = new AcompanhamentoBD($this->getObjInfraIBanco());
      $ret = $objAcompanhamentoBD->cadastrar($objAcompanhamentoDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando Acompanhamento.',$e);
    }
  }

  protected function alterarControlado(AcompanhamentoDTO $objAcompanhamentoDTO){
    try {
    	

      //Valida Permissao
  	   SessaoSEI::getInstance()->validarAuditarPermissao('acompanhamento_alterar',__METHOD__,$objAcompanhamentoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      if ($objAcompanhamentoDTO->isSetNumIdUnidade()){
        $objAcompanhamentoDTO->unSetNumIdUnidade();
      }

      if ($objAcompanhamentoDTO->isSetNumIdGrupoAcompanhamento()){
      	$this->validarNumIdGrupoAcompanhamento($objAcompanhamentoDTO, $objInfraException);
      }

      if ($objAcompanhamentoDTO->isSetDblIdProtocolo()){
        $objAcompanhamentoDTO->unSetDblIdProtocolo();
      }

      if ($objAcompanhamentoDTO->isSetNumIdUsuarioGerador()){
        $objAcompanhamentoDTO->unSetNumIdUsuarioGerador();
      }

      if ($objAcompanhamentoDTO->isSetDthGeracao()){
        $objAcompanhamentoDTO->unSetDthGeracao();
      }

      if ($objAcompanhamentoDTO->isSetStrObservacao()){
        $this->validarStrObservacao($objAcompanhamentoDTO, $objInfraException);
      }

      if ($objAcompanhamentoDTO->isSetNumTipoVisualizacao()){
        $objAcompanhamentoDTO->unSetTipoVisualizacao();
      }

      $objInfraException->lancarValidacoes();

      $objAcompanhamentoBD = new AcompanhamentoBD($this->getObjInfraIBanco());
      $objAcompanhamentoBD->alterar($objAcompanhamentoDTO);

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando Acompanhamento.',$e);
    }
  }

  protected function excluirControlado($arrObjAcompanhamentoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acompanhamento_excluir',__METHOD__,$arrObjAcompanhamentoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();
      
      //$objInfraException->lancarValidacoes();

      $objAcompanhamentoBD = new AcompanhamentoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjAcompanhamentoDTO);$i++){
        $objAcompanhamentoBD->excluir($arrObjAcompanhamentoDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo Acompanhamento.',$e);
    }
  }

  protected function consultarConectado(AcompanhamentoDTO $objAcompanhamentoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acompanhamento_consultar',__METHOD__,$objAcompanhamentoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAcompanhamentoBD = new AcompanhamentoBD($this->getObjInfraIBanco());
      $ret = $objAcompanhamentoBD->consultar($objAcompanhamentoDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando Acompanhamento.',$e);
    }
  }

  protected function listarConectado(AcompanhamentoDTO $objAcompanhamentoDTO) {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acompanhamento_listar',__METHOD__,$objAcompanhamentoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAcompanhamentoBD = new AcompanhamentoBD($this->getObjInfraIBanco());
      $ret = $objAcompanhamentoBD->listar($objAcompanhamentoDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Acompanhamentos.',$e);
    }
  }

  protected function contarConectado(AcompanhamentoDTO $objAcompanhamentoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acompanhamento_listar',__METHOD__,$objAcompanhamentoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAcompanhamentoBD = new AcompanhamentoBD($this->getObjInfraIBanco());
      $ret = $objAcompanhamentoBD->contar($objAcompanhamentoDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro contando Acompanhamentos.',$e);
    }
  }
/* 
  protected function desativarControlado($arrObjAcompanhamentoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acompanhamento_desativar',__METHOD__,$arrObjAcompanhamentoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAcompanhamentoBD = new AcompanhamentoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjAcompanhamentoDTO);$i++){
        $objAcompanhamentoBD->desativar($arrObjAcompanhamentoDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando Acompanhamento.',$e);
    }
  }

  protected function reativarControlado($arrObjAcompanhamentoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acompanhamento_reativar',__METHOD__,$arrObjAcompanhamentoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAcompanhamentoBD = new AcompanhamentoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjAcompanhamentoDTO);$i++){
        $objAcompanhamentoBD->reativar($arrObjAcompanhamentoDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando Acompanhamento.',$e);
    }
  }

  protected function bloquearControlado(AcompanhamentoDTO $objAcompanhamentoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acompanhamento_consultar',__METHOD__,$objAcompanhamentoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAcompanhamentoBD = new AcompanhamentoBD($this->getObjInfraIBanco());
      $ret = $objAcompanhamentoBD->bloquear($objAcompanhamentoDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro bloqueando Acompanhamento.',$e);
    }
  }

 */
  
  protected function listarAcompanhamentosUnidadeConectado(AcompanhamentoDTO $objAcompanhamentoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acompanhamento_listar',__METHOD__,$objAcompanhamentoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

		  $objAcompanhamentoDTO->retNumIdAcompanhamento();
		  $objAcompanhamentoDTO->retNumIdUnidade();
		  $objAcompanhamentoDTO->retNumIdGrupoAcompanhamento();
		  $objAcompanhamentoDTO->retDblIdProtocolo();
		  $objAcompanhamentoDTO->retNumIdUsuarioGerador();
		  $objAcompanhamentoDTO->retDthGeracao();
		  $objAcompanhamentoDTO->retStrObservacao();
		  $objAcompanhamentoDTO->retStrSiglaUsuario();
		  $objAcompanhamentoDTO->retStrSiglaUnidade();
		  $objAcompanhamentoDTO->retStrDescricaoUnidade();
		  $objAcompanhamentoDTO->retStrNomeGrupo();
		  $objAcompanhamentoDTO->retStrNomeUsuario();
		  $objAcompanhamentoDTO->retStrSiglaUsuario();
		  //$objAcompanhamentoDTO->retStrProtocoloFormatado();
      $objAcompanhamentoDTO->retNumTipoVisualizacao();
      //$objAcompanhamentoDTO->retNumIdTipoProcedimentoProcedimento();
      //$objAcompanhamentoDTO->retStrNomeTipoProcedimento();
      //$objAcompanhamentoDTO->retStrStaNivelAcessoGlobalProtocolo();
		  
		  $objAcompanhamentoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());


		  $objAcompanhamentoRN = new AcompanhamentoRN();
      $arrObjAcompanhamentoDTO = $objAcompanhamentoRN->listar($objAcompanhamentoDTO);
		  
      
			if (count($arrObjAcompanhamentoDTO)>0){
				
				$objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();
        $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_PROCEDIMENTOS);
				$objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_TODOS_EXCETO_SIGILOSOS_SEM_ACESSO);
				$objPesquisaProtocoloDTO->setDblIdProtocolo(InfraArray::converterArrInfraDTO($arrObjAcompanhamentoDTO,'IdProtocolo'));
				
				$objProtocoloRN = new ProtocoloRN();
				$arrObjProtocoloDTO = InfraArray::indexarArrInfraDTO($objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO),'IdProtocolo');
			}

			$arrRet = array();
			foreach($arrObjAcompanhamentoDTO as $dto){
				//se tem acesso
				if (isset($arrObjProtocoloDTO[$dto->getDblIdProtocolo()])){
					$arrRet[] = $dto;
				}
			}

      if (count($arrRet)) {

        $arrIdProtocolos = InfraArray::converterArrInfraDTO($arrRet,'IdProtocolo');

        $objPesquisaPendenciaDTO = new PesquisaPendenciaDTO();
        $objPesquisaPendenciaDTO->setDblIdProtocolo($arrIdProtocolos);
        $objPesquisaPendenciaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
        $objPesquisaPendenciaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objPesquisaPendenciaDTO->setStrSinRetornoProgramado('S');
        $objPesquisaPendenciaDTO->setStrSinAnotacoes('S');
        $objPesquisaPendenciaDTO->setStrSinSituacoes('S');
        $objPesquisaPendenciaDTO->setStrSinMarcadores('S');

        $objAtividadeRN = new AtividadeRN();
        $arrObjProcedimentoDTOComPendencias = InfraArray::indexarArrInfraDTO($objAtividadeRN->listarPendenciasRN0754($objPesquisaPendenciaDTO),'IdProcedimento');

        $arrIdProtocolos = array_diff($arrIdProtocolos, array_keys($arrObjProcedimentoDTOComPendencias));

        if (count($arrIdProtocolos)) {

          $objProcedimentoDTO = new ProcedimentoDTO();
          $objProcedimentoDTO->setDblIdProcedimento($arrIdProtocolos, InfraDTO::$OPER_IN);
          $objProcedimentoDTO->setStrSinAnotacoes('S');
          $objProcedimentoDTO->setStrSinSituacoes('S');
          $objProcedimentoDTO->setStrSinMarcadores('S');

          $objProcedimentoRN = new ProcedimentoRN();
          $arrObjProcedimentoDTOSemPendencias = InfraArray::indexarArrInfraDTO($objProcedimentoRN->listarCompleto($objProcedimentoDTO), 'IdProcedimento');

        }else{
          $arrObjProcedimentoDTOSemPendencias = array();
        }

        foreach($arrRet as $objAcompanhamentoDTO){

          $dblIdProtocolo = $objAcompanhamentoDTO->getDblIdProtocolo();

          if (isset($arrObjProcedimentoDTOComPendencias[$dblIdProtocolo])){
            $objAcompanhamentoDTO->setObjProcedimentoDTO($arrObjProcedimentoDTOComPendencias[$dblIdProtocolo]);
          }else{
            $arrObjProcedimentoDTOSemPendencias[$dblIdProtocolo]->setArrObjRetornoProgramadoDTO(null);
            $objAcompanhamentoDTO->setObjProcedimentoDTO($arrObjProcedimentoDTOSemPendencias[$dblIdProtocolo]);
          }

        }

      }

      return $arrRet;

    }catch(Exception $e){
      throw new InfraException('Erro listando acompanhamentos da unidade.',$e);
    }
  }

  protected function atualizarVisualizacaoControlado(AcompanhamentoDTO $parObjAcompanhamentoDTO){
    try{

      $objAcompanhamentoDTO = new AcompanhamentoDTO();
      $objAcompanhamentoDTO->retNumIdAcompanhamento();
      $objAcompanhamentoDTO->retNumTipoVisualizacao();
      $objAcompanhamentoDTO->setDblIdProtocolo($parObjAcompanhamentoDTO->getDblIdProtocolo());

      //se alguma unidade que não deve ser atualizada
      if ($parObjAcompanhamentoDTO->isSetNumIdUnidade() && !InfraString::isBolVazia($parObjAcompanhamentoDTO->getNumIdUnidade())){
        $objAcompanhamentoDTO->setNumIdUnidade($parObjAcompanhamentoDTO->getNumIdUnidade(),InfraDTO::$OPER_DIFERENTE);
      }

      $arrObjAcompanhamentoDTO = $this->listar($objAcompanhamentoDTO);

      $objAcompanhamentoBD = new AcompanhamentoBD($this->getObjInfraIBanco());

      foreach($arrObjAcompanhamentoDTO as $objAcompanhamentoDTO){
        $objAcompanhamentoDTO->setNumTipoVisualizacao($objAcompanhamentoDTO->getNumTipoVisualizacao() | $parObjAcompanhamentoDTO->getNumTipoVisualizacao());
        $objAcompanhamentoBD->alterar($objAcompanhamentoDTO);
      }

    }catch(Exception $e){
      throw new InfraException('Erro atualizando visualização de Acompanhamento Especial.',$e);
    }
  }

  protected function atualizarVisualizacaoUnidadeControlado(AcompanhamentoDTO $parObjAcompanhamentoDTO){
    try{

      $objAcompanhamentoDTO = new AcompanhamentoDTO();
      $objAcompanhamentoDTO->retNumIdAcompanhamento();
      $objAcompanhamentoDTO->retNumTipoVisualizacao();
      $objAcompanhamentoDTO->setDblIdProtocolo($parObjAcompanhamentoDTO->getDblIdProtocolo());
      $objAcompanhamentoDTO->setNumIdUnidade($parObjAcompanhamentoDTO->getNumIdUnidade());

      $arrObjAcompanhamentoDTO = $this->listar($objAcompanhamentoDTO);

      $objAcompanhamentoBD = new AcompanhamentoBD($this->getObjInfraIBanco());

      foreach($arrObjAcompanhamentoDTO as $objAcompanhamentoDTO){
        $objAcompanhamentoDTO->setNumTipoVisualizacao($objAcompanhamentoDTO->getNumTipoVisualizacao() | $parObjAcompanhamentoDTO->getNumTipoVisualizacao());
        $objAcompanhamentoBD->alterar($objAcompanhamentoDTO);
      }

    }catch(Exception $e){
      throw new InfraException('Erro atualizando visualização da unidade no Acompanhamento Especial.',$e);
    }
  }

  protected function marcarVisualizadoControlado(AcompanhamentoDTO $parObjAcompanhamentoDTO){
    try{

      $objAcompanhamentoDTO = new AcompanhamentoDTO();
      $objAcompanhamentoDTO->setNumIdAcompanhamento($parObjAcompanhamentoDTO->getNumIdAcompanhamento());
      $objAcompanhamentoDTO->setNumTipoVisualizacao(AtividadeRN::$TV_VISUALIZADO);
      $objAcompanhamentoBD = new AcompanhamentoBD($this->getObjInfraIBanco());
      $objAcompanhamentoBD->alterar($objAcompanhamentoDTO);

    }catch(Exception $e){
      throw new InfraException('Erro marcando visualização de Acompanhamento Especial.',$e);
    }
  }

}
?>