<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 31/01/2008 - criado por marcio_db
*
* Versão do Gerador de Código: 1.13.1
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class ProcedimentoINT extends InfraINT {

  public static function pesquisarDigitadoRI1023($strIdProcedimento) {

    $objInfraException = new InfraException();

    if (InfraString::isBolVazia($strIdProcedimento)) {
      $objInfraException->lancarValidacao('Protocolo para pesquisa não informado.');
    }

    $objProtocoloDTO = new ProtocoloDTO();
    $objProtocoloDTO->setStrProtocoloFormatadoPesquisa(InfraUtil::retirarFormatacao($strIdProcedimento,false));

    $objProtocoloRN = new ProtocoloRN();
    $arrObjProtocoloDTOPesquisado = $objProtocoloRN->pesquisarProtocoloFormatado($objProtocoloDTO);

    if (count($arrObjProtocoloDTOPesquisado)==0 || $arrObjProtocoloDTOPesquisado[0]->getStrStaProtocolo()!=ProtocoloRN::$TP_PROCEDIMENTO){
      $objInfraException->lancarValidacao('Processo não encontrado.');
    }

    $objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();
    $objPesquisaProtocoloDTO->setDblIdProtocolo($arrObjProtocoloDTOPesquisado[0]->getDblIdProtocolo());
    $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_PROCEDIMENTOS);
    $objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_AUTORIZADO);

    $objProtocoloRN = new ProtocoloRN();
    $arrObjProtocoloDTO = $objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO);

    if (count($arrObjProtocoloDTO) == 0) {
      $objInfraException->lancarValidacao('Processo não encontrado.');
    }

    return array('IdProcedimento' => $arrObjProtocoloDTO[0]->getDblIdProtocolo(),
        'ProtocoloProcedimentoFormatado' => $arrObjProtocoloDTO[0]->getStrProtocoloFormatado(),
        'NomeTipoProcedimento' => $arrObjProtocoloDTO[0]->getStrNomeTipoProcedimentoProcedimento());
  }

  public static function montarSelectArvoreOrdenacao($dblIdProcedimento){

    $objProcedimentoDTO = new ProcedimentoDTO();
    
    $objProcedimentoDTO->setDblIdProcedimento($dblIdProcedimento);
    $objProcedimentoDTO->setStrSinDocTodos('S');
    $objProcedimentoDTO->setStrSinProcAnexados('S');
    
    $objProcedimentoRN = new ProcedimentoRN();
    
    $arr = $objProcedimentoRN->listarCompleto($objProcedimentoDTO);
    
    $arrObjRelProtocoloProtocoloDTO = $arr[0]->getArrObjRelProtocoloProtocoloDTO();
    
    foreach($arrObjRelProtocoloProtocoloDTO as $objRelProtocoloProtocoloDTO){
      if ($objRelProtocoloProtocoloDTO->getStrStaAssociacao()==RelProtocoloProtocoloRN::$TA_DOCUMENTO_ASSOCIADO){
        $objRelProtocoloProtocoloDTO->setStrIdentificacaoProtocolo2(DocumentoINT::montarIdentificacaoArvore($objRelProtocoloProtocoloDTO->getObjProtocoloDTO2()));
      }else if ($objRelProtocoloProtocoloDTO->getStrStaAssociacao()==RelProtocoloProtocoloRN::$TA_DOCUMENTO_MOVIDO){
        $objRelProtocoloProtocoloDTO->setStrIdentificacaoProtocolo2(DocumentoINT::montarIdentificacaoArvore($objRelProtocoloProtocoloDTO->getObjProtocoloDTO2()). ' (movido)');
      }else if ($objRelProtocoloProtocoloDTO->getStrStaAssociacao()==RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO){
        $objRelProtocoloProtocoloDTO->setStrIdentificacaoProtocolo2(ProcedimentoINT::montarIdentificacaoArvore($objRelProtocoloProtocoloDTO->getObjProtocoloDTO2()).' (anexado)');
      }else if ($objRelProtocoloProtocoloDTO->getStrStaAssociacao()==RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_DESANEXADO){
        $objRelProtocoloProtocoloDTO->setStrIdentificacaoProtocolo2(ProcedimentoINT::montarIdentificacaoArvore($objRelProtocoloProtocoloDTO->getObjProtocoloDTO2()).' (desanexado)');
      }
    }

    return parent::montarSelectArrInfraDTO(null, null, null, $arrObjRelProtocoloProtocoloDTO, 'IdRelProtocoloProtocolo', 'IdentificacaoProtocolo2');
  }

  public static function formatarProtocoloTipoRI0200($strProtocoloFormatado, $strNomeTipoProcedimento){
    return $strProtocoloFormatado.' - '.$strNomeTipoProcedimento;
  }
  
  public static function conjuntoCompletoFormatadoRI0903($arrProcedimentos){
    
  	if (count($arrProcedimentos)){
	    $objProcedimentoDTO = new ProcedimentoDTO();
	    $objProcedimentoDTO->retDblIdProcedimento();
	    $objProcedimentoDTO->retStrProtocoloProcedimentoFormatado();
	    $objProcedimentoDTO->retNumIdTipoProcedimento();
	    $objProcedimentoDTO->retStrNomeTipoProcedimento();
	    $objProcedimentoDTO->setDblIdProcedimento($arrProcedimentos,InfraDTO::$OPER_IN);
	
	    $objProcedimentoRN = new ProcedimentoRN();
	    $arrObjProcedimentoDTO = $objProcedimentoRN->listarRN0278($objProcedimentoDTO);
	
			foreach($arrObjProcedimentoDTO as $objProcedimentoDTO){
					$objProcedimentoDTO->setStrNomeTipoProcedimento(ProcedimentoINT::formatarProtocoloTipoRI0200($objProcedimentoDTO->getStrProtocoloProcedimentoFormatado(),$objProcedimentoDTO->getStrNomeTipoProcedimento()));
			}
  	}else{
  		$arrObjProcedimentoDTO = array();
  	}

		return parent::montarSelectArrInfraDTO(null, null, null, $arrObjProcedimentoDTO, 'IdProcedimento','NomeTipoProcedimento');
  }

  public static function montarIconeVisualizacao($numTipoVisualizacao, $objProcedimentoDTO, $arrIconeIntegracao = null, $bolAcaoAndamentoSituacaoGerenciar, $bolAcaoAndamentoMarcadorGerenciar, $strParametros = ''){

    $dblIdProcedimento = $objProcedimentoDTO->getDblIdProcedimento();
    $objAndamentoSituacaoDTO = $objProcedimentoDTO->getObjAndamentoSituacaoDTO();
    $objAndamentoMarcadorDTO = $objProcedimentoDTO->getObjAndamentoMarcadorDTO();

    $strImagemStatus = '';

    if ($objProcedimentoDTO->getStrStaEstadoProtocolo()==ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO){
      $strImagemStatus .= '<a href="javascript:void(0);" '.PaginaSEI::montarTitleTooltip('Processo bloqueado').'><img src="imagens/sei_bloqueado.png" class="imagemStatus" /></a>';
    }

    if ($numTipoVisualizacao & AtividadeRN::$TV_REMOCAO_SOBRESTAMENTO){
      $strImagemStatus .= '<a href="javascript:void(0);" '.PaginaSEI::montarTitleTooltip('Processo deixou de estar sobrestado').'><img src="imagens/sei_remover_sobrestamento_processo_pequeno.gif" class="imagemStatus" /></a>';
    }

    if ($numTipoVisualizacao & AtividadeRN::$TV_ATENCAO){
      $strImagemStatus .= '<a href="javascript:void(0);" '.PaginaSEI::montarTitleTooltip('Um documento foi incluído ou assinado neste processo').'><img src="imagens/exclamacao.png" class="imagemStatus" /></a>';
    }

    if ($numTipoVisualizacao & AtividadeRN::$TV_PUBLICACAO){
      $strImagemStatus .= '<a href="javascript:void(0);" '.PaginaSEI::montarTitleTooltip('Um documento do processo foi publicado').'><img src="imagens/sei_publicacao_pequeno.gif" class="imagemStatus" /></a>';
    }

    if ($objProcedimentoDTO->getArrObjRetornoProgramadoDTO()!=null){
      RetornoProgramadoINT::montarIconeRetornoProgramado($objProcedimentoDTO->getArrObjRetornoProgramadoDTO(),$strIconeRetornoProgramado,$strRetornoProgramado);
      $strImagemStatus .= '<a href="javascript:void(0);" '.PaginaSEI::montarTitleTooltip($strRetornoProgramado,'Retorno Programado').'><img src="imagens/'.$strIconeRetornoProgramado.'" class="imagemStatus" /></a>';
    }

    if ($objAndamentoSituacaoDTO!=null) {

      if ($bolAcaoAndamentoSituacaoGerenciar) {
        $strLink = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=andamento_situacao_gerenciar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_procedimento=' . $dblIdProcedimento.$strParametros);
      }else{
        $strLink = 'javascript:void(0);';
      }

      $strSituacao = SituacaoINT::formatarSituacaoDesativada($objAndamentoSituacaoDTO->getStrNomeSituacao(), $objAndamentoSituacaoDTO->getStrSinAtivoSituacao());

      $strAcao = '<a href="'.$strLink.'" '.PaginaSEI::montarTitleTooltip($strSituacao).'><img src="imagens/sei_situacao_pequeno.png" class="imagemStatus" /></a>';

      $strImagemStatus .= $strAcao;
    }

    if ($objAndamentoMarcadorDTO!=null) {

      if ($bolAcaoAndamentoMarcadorGerenciar){
        $strLink = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=andamento_marcador_gerenciar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_procedimento=' . $dblIdProcedimento.$strParametros);
      }else{
        $strLink = 'javascript:void(0);';
      }

      $strMarcador = MarcadorINT::formatarMarcadorDesativado($objAndamentoMarcadorDTO->getStrNomeMarcador(), $objAndamentoMarcadorDTO->getStrSinAtivoMarcador());

      $strAcao = '<a href="'.$strLink.'" '.PaginaSEI::montarTitleTooltip($objAndamentoMarcadorDTO->getStrTexto(),$strMarcador).'><img src="imagens/'.$objAndamentoMarcadorDTO->getStrArquivoIconeMarcador().'" class="imagemStatus" /></a>';

      $strImagemStatus .= $strAcao;
    }

    if ($arrIconeIntegracao!=null && isset($arrIconeIntegracao[$dblIdProcedimento])) {
      foreach ($arrIconeIntegracao[$dblIdProcedimento] as $strIconeIntegracao) {
        $strImagemStatus .= $strIconeIntegracao . '&nbsp;&nbsp;';
      }
    }

    return $strImagemStatus;
  }

  public static function adicionarProcedimentoVisitado($dblIdProcedimento){
    $arr = SessaoSEI::getInstance()->getAtributo('PROCESSOS_VISITADOS_'.SessaoSEI::getInstance()->getStrSiglaUnidadeAtual());
    if (!is_array($arr)){ 
      $arr = array($dblIdProcedimento => 0);
    }else{
      if (!isset($arr[$dblIdProcedimento])){
        $arr[$dblIdProcedimento] = 0;
      }
    }
    SessaoSEI::getInstance()->setAtributo('PROCESSOS_VISITADOS_'.SessaoSEI::getInstance()->getStrSiglaUnidadeAtual(), $arr);
  }

  public static function removerProcedimentoVisitado($dblIdProcedimento){
    $arr = SessaoSEI::getInstance()->getAtributo('PROCESSOS_VISITADOS_'.SessaoSEI::getInstance()->getStrSiglaUnidadeAtual());
    if (is_array($arr)){
      if (isset($arr[$dblIdProcedimento])){
        unset($arr[$dblIdProcedimento]);
      }
      SessaoSEI::getInstance()->setAtributo('PROCESSOS_VISITADOS_'.SessaoSEI::getInstance()->getStrSiglaUnidadeAtual(), $arr);
    }  
  }
  
  public static function montarAcoesArvore($dblIdProcedimento,
                                           $numIdUnidadeAtual,
                                           &$bolFlagAberto,
                                           &$bolFlagAnexado,
                                           &$bolFlagAbertoAnexado,
                                           &$bolFlagProtocolo,
                                           &$bolFlagArquivo,
                                           &$bolFlagTramitacao,
                                           &$bolFlagSobrestado,
                                           &$bolFlagBloqueado,
                                           &$numCodigoAcesso,
                                           &$numNo,
                                           &$strNoProc,
                                           &$numNoAcao,
                                           &$strNosAcaoProc,
                                           &$bolErro){
    try{
      
      global $SEI_MODULOS;

      $objSessaoSEI = SessaoSEI::getInstance();
      $objPaginaSEI = PaginaSEI::getInstance();
      
      $objPesquisaPendenciaDTO = new PesquisaPendenciaDTO();
      $objPesquisaPendenciaDTO->setDblIdProtocolo($dblIdProcedimento);
      $objPesquisaPendenciaDTO->setNumIdUsuario($objSessaoSEI->getNumIdUsuario());
      $objPesquisaPendenciaDTO->setNumIdUnidade($numIdUnidadeAtual);
      $objPesquisaPendenciaDTO->setStrSinMontandoArvore('S');
      $objPesquisaPendenciaDTO->setStrSinRetornoProgramado('S');
      
      $objAtividadeRN = new AtividadeRN();
      $arrObjProcedimentoDTO = $objAtividadeRN->listarPendenciasRN0754($objPesquisaPendenciaDTO);

      $numRegistrosProcedimento = count($arrObjProcedimentoDTO);
      
      $bolFlagAberto = false;
      $bolFlagAbertoAnexado = false;
      $bolFlagTramitacao = false;
      $bolFlagSobrestado = false;
      $bolUnidadeSobrestamento = false;
      $bolFlagAnexado = false;
      $objProcedimentoDTO = null;
      
      if ($numRegistrosProcedimento == 1){
         
        $objProcedimentoDTO = $arrObjProcedimentoDTO[0];
        $bolFlagAberto = true;
        $bolFlagTramitacao = true;
      
      }else{
      
        $dto = new ProcedimentoDTO();
        $dto->setDblIdProcedimento($dblIdProcedimento);
        $dto->setStrSinMontandoArvore('S');
      
        $objProcedimentoRN = new ProcedimentoRN();
        $arr = $objProcedimentoRN->listarCompleto($dto);
      
        if (count($arr) == 1){
      
          $objProcedimentoDTO = $arr[0];
      
          if ($objProcedimentoDTO->getNumIdUnidadeGeradoraProtocolo()==$numIdUnidadeAtual){
            $bolFlagTramitacao = true;
          }else{
            $objAtividadeDTO = new AtividadeDTO();
            $objAtividadeDTO->retNumIdAtividade();
            $objAtividadeDTO->setNumIdUnidadeOrigem($numIdUnidadeAtual,InfraDTO::$OPER_DIFERENTE);
            $objAtividadeDTO->setNumIdUnidade($numIdUnidadeAtual);
            $objAtividadeDTO->setDblIdProtocolo($dblIdProcedimento);
            $objAtividadeDTO->setNumMaxRegistrosRetorno(1);
      
            //se teve andamento enviado para a unidade
            if ($objAtividadeRN->consultarRN0033($objAtividadeDTO)!=null){
              $bolFlagTramitacao = true;
            }
          }
        }
      }


      if ($objProcedimentoDTO == null){
        $objPaginaSEI->setStrMensagem('Processo não encontrado.',PaginaSEI::$TIPO_MSG_AVISO);
        $bolErro = true;
      }else{
      
        if ($objProcedimentoDTO->getStrStaEstadoProtocolo()==ProtocoloRN::$TE_PROCEDIMENTO_SOBRESTADO){
      
          //se o processo esta aberto entao foi a unidade atual que o sobrestou
          if ($bolFlagAberto){
            $bolUnidadeSobrestamento = true;
      
            //tratar como um processo concluido
            $bolFlagAberto = false;
          }
      
          $bolFlagSobrestado = true;
          
        }else if ($objProcedimentoDTO->getStrStaEstadoProtocolo()==ProtocoloRN::$TE_PROCEDIMENTO_ANEXADO){
          $bolFlagAnexado = true;

          $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
          $objRelProtocoloProtocoloDTO->retDblIdProtocolo1();
          $objRelProtocoloProtocoloDTO->setDblIdProtocolo2($dblIdProcedimento);
          $objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);

          $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
          $objProcedimentoDTOPai = $objRelProtocoloProtocoloRN->consultarRN0841($objRelProtocoloProtocoloDTO);

          $objPesquisaPendenciaDTO = new PesquisaPendenciaDTO();
          $objPesquisaPendenciaDTO->setDblIdProtocolo($objProcedimentoDTOPai->getDblIdProtocolo1());
          $objPesquisaPendenciaDTO->setNumIdUsuario($objSessaoSEI->getNumIdUsuario());
          $objPesquisaPendenciaDTO->setNumIdUnidade($numIdUnidadeAtual);

          $arrObjProcedimentoDTOPai = $objAtividadeRN->listarPendenciasRN0754($objPesquisaPendenciaDTO);

          if (count($arrObjProcedimentoDTOPai)){
            $bolFlagAbertoAnexado = true;
          }

        }else if ($objProcedimentoDTO->getStrStaEstadoProtocolo()==ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO){
          
          //tratar como um processo concluido
          //$bolFlagAberto = false;
          
          $bolFlagBloqueado = true;
        }
      
        if ($bolFlagAberto || $bolFlagSobrestado){
          ProcedimentoINT::adicionarProcedimentoVisitado($dblIdProcedimento);
        }
      
        $numProtocolosAssociados = count($objProcedimentoDTO->getArrObjRelProtocoloProtocoloDTO());

        $objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();
        $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_PROCEDIMENTOS);
        $objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_TODOS);
        $objPesquisaProtocoloDTO->setDblIdProtocolo($dblIdProcedimento);
         
        $objProtocoloRN = new ProtocoloRN();
        $arrObjProtocoloDTO = InfraArray::indexarArrInfraDTO($objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO),'IdProtocolo');
      
        if(!isset($arrObjProtocoloDTO[$dblIdProcedimento])){

          $objPaginaSEI->setStrMensagem('Acesso negado ao processo.',PaginaSEI::$TIPO_MSG_AVISO);
          $bolErro = true;
      
        }else {

          $numCodigoAcesso = $arrObjProtocoloDTO[$dblIdProcedimento]->getNumCodigoAcesso();

          $objUnidadeDTO = new UnidadeDTO();
          $objUnidadeDTO->setBolExclusaoLogica(false);
          $objUnidadeDTO->retStrSinProtocolo();
          $objUnidadeDTO->retStrSinOuvidoria();
          $objUnidadeDTO->retStrSinArquivamento();
          $objUnidadeDTO->setNumIdUnidade($numIdUnidadeAtual);

          $objUnidadeRN = new UnidadeRN();
          $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);

          if ($objUnidadeDTO == null) {
            throw new InfraException('Unidade '.$objSessaoSEI->getStrSiglaUnidadeAtual().' não encontrada.');
          }

          $bolFlagProtocolo = ($objUnidadeDTO->getStrSinProtocolo() == 'S');
          $bolFlagArquivo = ($objUnidadeDTO->getStrSinArquivamento() == 'S');

          $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
          $numTipoPesquisaRestrito = $objInfraParametro->getValor('SEI_EXIBIR_ARVORE_RESTRITO_SEM_ACESSO', false);

          if ($objProcedimentoDTO->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_RESTRITO && $numCodigoAcesso < 0 && !$bolFlagProtocolo && $numTipoPesquisaRestrito != '1') {

            $objPaginaSEI->setStrMensagem('Unidade atual não possui acesso ao processo restrito '.$objProcedimentoDTO->getStrProtocoloProcedimentoFormatado().'.', PaginaSEI::$TIPO_MSG_AVISO);
            $bolErro = true;

          }else{

            //processos sigilosos somente com credencial de assinatura
            if ($objProcedimentoDTO->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_SIGILOSO && $arrObjProtocoloDTO[$dblIdProcedimento]->getStrSinCredencialProcesso() == 'N') {
              $bolFlagAberto = false;
              $bolFlagTramitacao = false;
            }

            $bolAcaoProcedimentoEnviar = $objSessaoSEI->verificarPermissao('procedimento_enviar');
            $bolAcaoProcedimentoCredencialGerenciar = $objSessaoSEI->verificarPermissao('procedimento_credencial_gerenciar');
            $bolAcaoProcedimentoCredencialRenunciar = $objSessaoSEI->verificarPermissao('procedimento_credencial_renunciar');
            $bolAcaoDefinirAtividade = $objSessaoSEI->verificarPermissao('procedimento_atualizar_andamento');
            $bolAcaoAtribuirProcesso = $objSessaoSEI->verificarPermissao('procedimento_atribuicao_cadastrar');
            $bolAcaoConsultarProcedimento = $objSessaoSEI->verificarPermissao('procedimento_consultar');
            $bolAcaoAlterarProcedimento = $objSessaoSEI->verificarPermissao('procedimento_alterar');
            $bolAcaoDuplicarProcedimento = $objSessaoSEI->verificarPermissao('procedimento_duplicar');
            $bolAcaoProcedimentoEnviarEmail = $objSessaoSEI->verificarPermissao('procedimento_enviar_email');
            $bolAcaoProcedimentoRelacionar = $objSessaoSEI->verificarPermissao('procedimento_relacionar');
            $bolAcaoEscolherTipo = $objSessaoSEI->verificarPermissao('documento_escolher_tipo');
            $bolAcaoDocumentoReceber = $objSessaoSEI->verificarPermissao('documento_receber');
            $bolAcaoExcluirProcedimento = $objSessaoSEI->verificarPermissao('procedimento_excluir');
            $bolAcaoIncluirEmBloco = $objSessaoSEI->verificarPermissao('rel_bloco_protocolo_cadastrar');
            $bolAcaoConcluirProcedimento = $objSessaoSEI->verificarPermissao('procedimento_concluir');
            $bolAcaoReabrirProcedimento = $objSessaoSEI->verificarPermissao('procedimento_reabrir');
            $bolAcaoSobrestarProcesso = $objSessaoSEI->verificarPermissao('procedimento_sobrestar');
            $bolAcaoAnexarProcesso = $objSessaoSEI->verificarPermissao('procedimento_anexar');
            $bolAcaoRemoverSobrestamento = $objSessaoSEI->verificarPermissao('procedimento_remover_sobrestamento');
            $bolAcaoRegistrarAnotacao = $objSessaoSEI->verificarPermissao('anotacao_registrar');
            $bolAcaoProcedimentoControlar = $objSessaoSEI->verificarPermissao('procedimento_controlar');
            $bolAcaoArvoreOrdenar = $objSessaoSEI->verificarPermissao('arvore_ordenar');
            $bolAcaoAcessoExternoGerenciar = $objSessaoSEI->verificarPermissao('acesso_externo_gerenciar');
            $bolAcaoAcompanhamentoCadastrar = $objSessaoSEI->verificarPermissao('acompanhamento_cadastrar');
            $bolAcaoProcedimentoCiencia = $objSessaoSEI->verificarPermissao('procedimento_ciencia');
            $bolAcaoProcedimentoGerarPdf = $objSessaoSEI->verificarPermissao('procedimento_gerar_pdf');
            $bolAcaoProcedimentoGerarZip = $objSessaoSEI->verificarPermissao('procedimento_gerar_zip');
            $bolAcaoReencaminharOuvidoria = $objSessaoSEI->verificarPermissao('procedimento_reencaminhar_ouvidoria');
            $bolAcaoFinalizarOuvidoria = $objSessaoSEI->verificarPermissao('procedimento_finalizar_ouvidoria');
            $bolAcaoAndamentoSituacaoGerenciar = $objSessaoSEI->verificarPermissao('andamento_situacao_gerenciar');
            $bolAcaoProcedimentoPesquisar = $objSessaoSEI->verificarPermissao('procedimento_pesquisar');
            $bolAcaoProcedimentoEscolherTipoRelacionado = $objSessaoSEI->verificarPermissao('procedimento_escolher_tipo_relacionado');
            $bolAcaoAndamentoMarcadorGerenciar = $objSessaoSEI->verificarPermissao('andamento_marcador_gerenciar');

            $strLinkProcesso = 'about:blank';

            //adiciona também acesso ao protocolo para permitir inclusão de documentos
            if ($numCodigoAcesso > 0 || $bolFlagProtocolo) {
              $strLinkProcesso = $objSessaoSEI->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem=procedimento_visualizar&id_procedimento='.$dblIdProcedimento);
            }

            if ($objProcedimentoDTO->getStrStaEstadoProtocolo() == ProtocoloRN::$TE_PROCEDIMENTO_ANEXADO) {
              $strIcone = 'imagens/procedimento_anexado.gif';
            } else {
              $strIcone = 'imagens/procedimento.gif';
            }


            $strNoProc .= "\n";
            $strNoProc .= "\n\n".'//CA='.$numCodigoAcesso;
            $strNoProc .= "\n";

            $strNoProc .= 'Nos['.$numNo.'] = new infraArvoreNo("PROCESSO",'.
                '"'.$dblIdProcedimento.'",'.
                'null,'.
                '"'.$strLinkProcesso.'",'.
                '"ifrVisualizacao",'.
                '"'.$objProcedimentoDTO->getStrProtocoloProcedimentoFormatado().'",'.
                '"'.$objPaginaSEI->formatarParametrosJavaScript($objProcedimentoDTO->getStrNomeTipoProcedimento()).'",'.
                '"'.$strIcone.'",'.
                '"'.$strIcone.'",'.
                '"'.$strIcone.'",'.
                'true,'.
                (($strLinkProcesso != 'about:blank') ? 'true,' : 'false,').
                'null,'.
                'null,'.
                'null,'.
                '"'.$objProcedimentoDTO->getStrProtocoloProcedimentoFormatado().'");'."\n";

            if ($objProcedimentoDTO->getStrStaNivelAcessoLocalProtocolo() != ProtocoloRN::$NA_PUBLICO) {

              $arrObjGrauSigiloDTO = null;
              if ($objProcedimentoDTO->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_SIGILOSO) {
                $arrObjGrauSigiloDTO = InfraArray::indexarArrInfraDTO(ProtocoloRN::listarGrausSigiloso(), 'StaGrau');
              }

              $strNosAcaoProc .= ProtocoloINT::montarNoAcaoAcesso($dblIdProcedimento, $numNoAcao++, $objProcedimentoDTO->getStrStaNivelAcessoLocalProtocolo(), $objProcedimentoDTO->getStrStaGrauSigiloProtocolo(), $objProcedimentoDTO->getStrNomeHipoteseLegal(), $objProcedimentoDTO->getStrBaseLegalHipoteseLegal(), $arrObjGrauSigiloDTO);
            }

            if ($bolFlagBloqueado) {
              $strNosAcaoProc .= 'NosAcoes['.$numNoAcao++.'] = new infraArvoreAcao("BLOQUEIO",'.
                  '"BL'.$dblIdProcedimento.'",'.
                  '"'.$dblIdProcedimento.'",'.
                  '"javascript:alert(\'Processo Bloqueado\');",'.
                  'null,'.
                  '"Processo Bloqueado",'.
                  '"imagens/sei_bloqueado.png",'.
                  'true);'."\n";
            }

            if ($arrObjProtocoloDTO[$dblIdProcedimento]->getArrAcessoModulos() != null) {
              $strNosAcaoProc .= ProtocoloINT::montarNoAcaoAcessoModulos($dblIdProcedimento, $numNoAcao++, $arrObjProtocoloDTO[$dblIdProcedimento]->getArrAcessoModulos());
            }

            if ($objProcedimentoDTO->isSetArrObjRetornoProgramadoDTO() && $objProcedimentoDTO->getArrObjRetornoProgramadoDTO() != null) {

              RetornoProgramadoINT::montarIconeRetornoProgramado($objProcedimentoDTO->getArrObjRetornoProgramadoDTO(), $strIconeRetornoProgramado, $strTextoRetornoProgramado);

              $strTextoRetornoProgramado = 'Retorno Programado:'."\n".$strTextoRetornoProgramado;

              $strNosAcaoProc .= 'NosAcoes['.$numNoAcao++.'] = new infraArvoreAcao("RETORNO",'.
                  '"RET'.$dblIdProcedimento.'",'.
                  '"'.$dblIdProcedimento.'",'.
                  '"javascript:alert(\''.PaginaSEI::formatarParametrosJavaScript(str_replace("\n", '\\\n', $strTextoRetornoProgramado)).'\');",'.
                  'null,'.
                  '"'.PaginaSEI::formatarParametrosJavaScript($strTextoRetornoProgramado).'",'.
                  '"imagens/'.$strIconeRetornoProgramado.'",'.
                  'true);'."\n";
            }

            if ($objUnidadeDTO->getStrSinOuvidoria() == 'S' && $objProcedimentoDTO->getStrSinOuvidoriaTipoProcedimento() == 'S') {
              if ($objProcedimentoDTO->getStrStaOuvidoria() == ProcedimentoRN::$TFO_SIM) {
                $strNosAcaoProc .= 'NosAcoes['.$numNoAcao++.'] = new infraArvoreAcao("SOLICITACAO",'.
                    '"SO'.$dblIdProcedimento.'",'.
                    '"'.$dblIdProcedimento.'",'.
                    '"javascript:alert(\'Solicitação Atendida\');",'.
                    'null,'.
                    '"Solicitação Atendida",'.
                    '"imagens/sei_solicitacao_atendida.png",'.
                    'true);'."\n";
              } else if ($objProcedimentoDTO->getStrStaOuvidoria() == ProcedimentoRN::$TFO_NAO) {
                $strNosAcaoProc .= 'NosAcoes['.$numNoAcao++.'] = new infraArvoreAcao("SOLICITACAO",'.
                    '"SO'.$dblIdProcedimento.'",'.
                    '"'.$dblIdProcedimento.'",'.
                    '"javascript:alert(\'Solicitação não Atendida\');",'.
                    'null,'.
                    '"Solicitação não Atendida",'.
                    '"imagens/sei_solicitacao_nao_atendida.png",'.
                    'true);'."\n";
              }
            }

            $bolFlagSituacao = false;

            $strStaNivelAcessoGlobal = $objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo();

            if ($strStaNivelAcessoGlobal != ProtocoloRN::$NA_SIGILOSO) {

              $objRelSituacaoUnidadeDTO = new RelSituacaoUnidadeDTO();
              $objRelSituacaoUnidadeDTO->retNumIdSituacao();
              $objRelSituacaoUnidadeDTO->setNumIdUnidade($numIdUnidadeAtual);
              $objRelSituacaoUnidadeDTO->setStrSinAtivoSituacao('S');
              $objRelSituacaoUnidadeDTO->setNumMaxRegistrosRetorno(1);

              $objRelSituacaoUnidadeRN = new RelSituacaoUnidadeRN();
              $bolFlagSituacao = ($objRelSituacaoUnidadeRN->consultar($objRelSituacaoUnidadeDTO) != null);

              $objAndamentoSituacaoDTO = $objProcedimentoDTO->getObjAndamentoSituacaoDTO();

              if ($objAndamentoSituacaoDTO != null) {

                if ($bolAcaoAndamentoSituacaoGerenciar) {
                  $strLinkControleUnidadeGerenciar = $objSessaoSEI->assinarLink('controlador.php?acao=andamento_situacao_gerenciar&acao_origem=procedimento_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1');
                  $strTargetControleUnidadeGerenciar = '"ifrVisualizacao"';
                } else {
                  $strLinkControleUnidadeGerenciar = 'javascript:alert(\''.$objPaginaSEI->formatarParametrosJavaScript(SituacaoINT::formatarSituacaoDesativada($objAndamentoSituacaoDTO->getStrNomeSituacao(), $objAndamentoSituacaoDTO->getStrSinAtivoSituacao())).'\');';
                  $strTargetControleUnidadeGerenciar = 'null';
                }

                $strNosAcaoProc .= 'NosAcoes['.$numNoAcao++.'] = new infraArvoreAcao("SITUACAO",'.
                    '"SIT'.$dblIdProcedimento.'",'.
                    '"'.$dblIdProcedimento.'",'.
                    '"'.$strLinkControleUnidadeGerenciar.'",'.
                    $strTargetControleUnidadeGerenciar.','.
                    '"'.$objPaginaSEI->formatarParametrosJavaScript('Ponto de Controle'."\n".SituacaoINT::formatarSituacaoDesativada($objAndamentoSituacaoDTO->getStrNomeSituacao(), $objAndamentoSituacaoDTO->getStrSinAtivoSituacao())).'",'.
                    '"imagens/sei_situacao_pequeno.png",'.
                    'true);'."\n";
              }

              $objAcompanhamentoDTO = new AcompanhamentoDTO();
              $objAcompanhamentoDTO->retNumIdAcompanhamento();
              $objAcompanhamentoDTO->retStrNomeGrupo();
              $objAcompanhamentoDTO->retNumTipoVisualizacao();
              $objAcompanhamentoDTO->setNumIdUnidade($objSessaoSEI->getNumIdUnidadeAtual());
              $objAcompanhamentoDTO->setDblIdProtocolo($dblIdProcedimento);
              $objAcompanhamentoRN = new AcompanhamentoRN();
              $objAcompanhamentoDTO = $objAcompanhamentoRN->consultar($objAcompanhamentoDTO);

              if ($objAcompanhamentoDTO != null) {

                if ($objAcompanhamentoDTO->getNumTipoVisualizacao() != AtividadeRN::$TV_VISUALIZADO) {
                  $objAcompanhamentoRN->marcarVisualizado($objAcompanhamentoDTO);
                }

                if (!$bolFlagAnexado) {
                  if ($bolAcaoAcompanhamentoCadastrar) {
                    $strLinkAcompanhamentoCadastrar = $objSessaoSEI->assinarLink('controlador.php?acao=acompanhamento_cadastrar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_acompanhamento='.$objAcompanhamentoDTO->getNumIdAcompanhamento().'&id_procedimento='.$dblIdProcedimento.'&arvore=1');
                    $strTargetAcompanhamentoCadastrar = '"ifrVisualizacao"';
                  } else {
                    $strLinkAcompanhamentoCadastrar = 'javascript:alert(\'Acompanhamento Especial\');';
                    $strTargetAcompanhamentoCadastrar = 'null';
                  }

                  $strNosAcaoProc .= 'NosAcoes['.$numNoAcao++.'] = new infraArvoreAcao("ACOMPANHAMENTO",'.
                      '"AC'.$dblIdProcedimento.'",'.
                      '"'.$dblIdProcedimento.'",'.
                      '"'.$strLinkAcompanhamentoCadastrar.'",'.
                      $strTargetAcompanhamentoCadastrar.','.
                      '"'.$objPaginaSEI->formatarParametrosJavaScript('Acompanhamento Especial'."\n".$objAcompanhamentoDTO->getStrNomeGrupo()).'",'.
                      '"imagens/sei_acompanhamento_especial_pequeno.png",'.
                      'true);'."\n";
                }
              }
            }

            $objAndamentoMarcadorDTO = $objProcedimentoDTO->getObjAndamentoMarcadorDTO();

            if ($objAndamentoMarcadorDTO != null) {

              if ($bolAcaoAndamentoMarcadorGerenciar) {
                $strLinkAcompanhamentoCadastrar = $objSessaoSEI->assinarLink('controlador.php?acao=andamento_marcador_gerenciar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1');
                $strTargetAcompanhamentoCadastrar = '"ifrVisualizacao"';
              } else {
                $strLinkAcompanhamentoCadastrar = 'javascript:alert(\'Marcador\');';
                $strTargetAcompanhamentoCadastrar = 'null';
              }

              $strNosAcaoProc .= 'NosAcoes['.$numNoAcao++.'] = new infraArvoreAcao("MARCADOR",'.
                  '"MC'.$dblIdProcedimento.'",'.
                  '"'.$dblIdProcedimento.'",'.
                  '"'.$strLinkAcompanhamentoCadastrar.'",'.
                  $strTargetAcompanhamentoCadastrar.','.
                  '"'.$objPaginaSEI->formatarParametrosJavaScript('Marcador'."\n".MarcadorINT::formatarMarcadorDesativado($objAndamentoMarcadorDTO->getStrNomeMarcador(), $objAndamentoMarcadorDTO->getStrSinAtivoMarcador())).'",'.
                  '"imagens/'.$objAndamentoMarcadorDTO->getStrArquivoIconeMarcador().'",'.
                  'true);'."\n";

            }

            $strAcoesProcedimento = '';
            $strHtmlProcesso = '';
            $numTabBotao = $objPaginaSEI->getProxTabBarraComandosSuperior();


            if (count($SEI_MODULOS)) {
              $objProcedimentoAPI = new ProcedimentoAPI();
              $objProcedimentoAPI->setIdProcedimento($objProcedimentoDTO->getDblIdProcedimento());
              $objProcedimentoAPI->setNumeroProtocolo($objProcedimentoDTO->getStrProtocoloProcedimentoFormatado());
              $objProcedimentoAPI->setIdTipoProcedimento($objProcedimentoDTO->getNumIdTipoProcedimento());
              $objProcedimentoAPI->setNomeTipoProcedimento($objProcedimentoDTO->getStrNomeTipoProcedimento());
              $objProcedimentoAPI->setNivelAcesso($objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo());
              $objProcedimentoAPI->setIdUnidadeGeradora($objProcedimentoDTO->getNumIdUnidadeGeradoraProtocolo());
              $objProcedimentoAPI->setIdOrgaoUnidadeGeradora($objProcedimentoDTO->getNumIdOrgaoUnidadeGeradoraProtocolo());
              $objProcedimentoAPI->setIdHipoteseLegal($objProcedimentoDTO->getNumIdHipoteseLegalProtocolo());
              $objProcedimentoAPI->setGrauSigilo($objProcedimentoDTO->getStrStaGrauSigiloProtocolo());
              $objProcedimentoAPI->setCodigoAcesso($numCodigoAcesso);
              $objProcedimentoAPI->setSinAberto($bolFlagAberto ? 'S' : 'N');
            }

            //não monta links e html se não tem acesso
            if ($strLinkProcesso != 'about:blank') {

              if (!$bolFlagBloqueado) {
                if ($bolFlagAberto) {
                  if ($bolAcaoEscolherTipo) {
                    $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=documento_escolher_tipo&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema"  src="imagens/sei_incluir_documento.gif" alt="Incluir Documento" title="Incluir Documento"/></a>';
                  }
                } else {
                  if ($bolFlagProtocolo && $bolAcaoDocumentoReceber && !$bolFlagAnexado && !$bolFlagSobrestado) {
                    $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=documento_receber&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1&flag_protocolo=S').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_incluir_documento.gif" alt="Registrar Documento Externo" title="Registrar Documento Externo"/></a>';
                  }
                }
              }

              if ($bolAcaoProcedimentoEscolherTipoRelacionado) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_escolher_tipo_relacionado&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento_destino='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_gerar_processo_relacionado.gif" alt="Iniciar Processo Relacionado" title="Iniciar Processo Relacionado"/></a>';
              }

              if ($bolAcaoAlterarProcedimento && !$bolFlagBloqueado && ($bolFlagAberto || $bolFlagAbertoAnexado || ($bolFlagProtocolo && $objProcedimentoDTO->getNumIdUnidadeGeradoraProtocolo() == $numIdUnidadeAtual))) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_alterar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_consultar_alterar_protocolo.gif" alt="Consultar/Alterar Processo" title="Consultar/Alterar Processo"/></a>';
              } else if ($bolAcaoConsultarProcedimento) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_consultar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_consultar_alterar_protocolo.gif" alt="Consultar Processo" title="Consultar Processo"/></a>';
              }

              if ($bolAcaoAcompanhamentoCadastrar && !$bolFlagAnexado && $strStaNivelAcessoGlobal != ProtocoloRN::$NA_SIGILOSO) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=acompanhamento_cadastrar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_acompanhamento_especial.gif" alt="Acompanhamento Especial" title="Acompanhamento Especial"/></a>';
              }

              if ($bolFlagAberto && $bolAcaoProcedimentoCiencia) {
                $strAcoesProcedimento .= '<a href="#" onclick="cienciaProcesso();" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema"  tabindex="'.$numTabBotao.'" src="imagens/sei_ciencia.gif" alt="Ciência" title="Ciência" />';
              }

              if ($bolFlagAberto && !$bolFlagBloqueado && $bolAcaoProcedimentoEnviar && $strStaNivelAcessoGlobal != ProtocoloRN::$NA_SIGILOSO) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_enviar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_enviar_processo.gif" alt="Enviar Processo" title="Enviar Processo" /></a>';
              }

              if ($bolFlagAberto && $bolAcaoProcedimentoCredencialGerenciar && $strStaNivelAcessoGlobal == ProtocoloRN::$NA_SIGILOSO) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_credencial_gerenciar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_gerenciar_credenciais.gif" alt="Gerenciar Credenciais de Acesso" title="Gerenciar Credenciais de Acesso" /></a>';
              }

              if ($bolFlagAberto &&
                  $bolAcaoProcedimentoCredencialRenunciar &&
                  $strStaNivelAcessoGlobal == ProtocoloRN::$NA_SIGILOSO &&
                  $arrObjProtocoloDTO[$dblIdProcedimento]->getStrSinCredencialProcesso() == 'S'
              ) {
                $strAcoesProcedimento .= '<a href="#" onclick="renunciarCredencial();" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema"  tabindex="'.$numTabBotao.'" src="imagens/sei_renunciar_credencial.gif" alt="Renunciar Credenciais de Acesso" title="Renunciar Credenciais de Acesso" />';
              }

              if ($bolFlagAberto && $bolAcaoDefinirAtividade) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_atualizar_andamento&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_atualizar_andamento.gif" alt="Atualizar Andamento" title="Atualizar Andamento" /></a>';
              }

              if ($bolFlagAberto && $bolAcaoAtribuirProcesso && $strStaNivelAcessoGlobal != ProtocoloRN::$NA_SIGILOSO) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_atribuicao_cadastrar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_atribuir_processo.gif" alt="Atribuir Processo" title="Atribuir Processo" /></a>';
              }

              if ($bolAcaoDuplicarProcedimento && ($bolFlagAberto || $bolFlagAnexado) && $strStaNivelAcessoGlobal != ProtocoloRN::$NA_SIGILOSO) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_duplicar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_duplicar_procedimento.gif" alt="Duplicar Processo" title="Duplicar Processo"/></a>';
              }

              if (($bolFlagAberto || $bolFlagAnexado) && !$bolFlagBloqueado && $bolAcaoProcedimentoEnviarEmail) {
                $strAcoesProcedimento .= '<a href="#" onclick="enviarEmailProcedimento();" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema"  tabindex="'.$numTabBotao.'" src="imagens/sei_email.gif" alt="Enviar Correspondência Eletrônica" title="Enviar Correspondência Eletrônica"/>';
              }

              if ($bolFlagAberto && $bolAcaoProcedimentoRelacionar) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_relacionar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_relacionados.png" alt="Relacionamentos do Processo" title="Relacionamentos do Processo"/></a>';
              }

              if ($bolFlagAberto && $bolAcaoIncluirEmBloco && $strStaNivelAcessoGlobal != ProtocoloRN::$NA_SIGILOSO) {
                $strAcoesProcedimento .= '<a href="#" onclick="incluirEmBloco();" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema"  tabindex="'.$numTabBotao.'" src="imagens/sei_incluir_em_bloco.gif" alt="Incluir em Bloco" title="Incluir em Bloco"/>';
              }

              if ($bolFlagAberto && !$bolFlagBloqueado && $bolAcaoArvoreOrdenar && $numProtocolosAssociados > 1) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=arvore_ordenar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_ordenar_arvore.gif" alt="Ordenar Árvore do Processo" title="Ordenar Árvore do Processo"/></a>';
              }

              if ($bolFlagAberto && $bolAcaoAcessoExternoGerenciar) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=acesso_externo_gerenciar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_acesso_externo.gif" alt="Gerenciar Disponibilizações de Acesso Externo" title="Gerenciar Disponibilizações de Acesso Externo"/></a>';
              }

              if ($bolFlagTramitacao && $bolAcaoRegistrarAnotacao) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=anotacao_registrar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_anotacao.gif" alt="Anotações" title="Anotações" /></a>';
              }

              if ($bolFlagAberto && !$bolFlagBloqueado && $bolAcaoSobrestarProcesso && !$bolFlagSobrestado && $strStaNivelAcessoGlobal != ProtocoloRN::$NA_SIGILOSO) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_sobrestar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_sobrestar_processo.gif" alt="Sobrestar Processo" title="Sobrestar Processo" /></a>';
              }

              if ($bolFlagAberto && !$bolFlagBloqueado && $bolAcaoAnexarProcesso && !$bolFlagAnexado && $strStaNivelAcessoGlobal != ProtocoloRN::$NA_SIGILOSO) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_anexar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_anexar_processo.gif" alt="Anexar Processo" title="Anexar Processo" /></a>';
              }

              if ($bolAcaoRemoverSobrestamento && !$bolFlagBloqueado && $bolFlagSobrestado && $bolUnidadeSobrestamento) {
                $strAcoesProcedimento .= '<a href="#" onclick="removerSobrestamentoProcesso();" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema"  tabindex="'.$numTabBotao.'" src="imagens/sei_remover_sobrestamento_processo.gif" alt="Remover Sobrestamento do Processo" title="Remover Sobrestamento do Processo" />';
              }

              if (!$bolFlagAberto && $bolAcaoReabrirProcedimento && $bolFlagTramitacao && !$bolFlagSobrestado && !$bolFlagAnexado) {
                $strAcoesProcedimento .= '<a href="#" onclick="reabrirProcesso();" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema"  tabindex="'.$numTabBotao.'" src="imagens/sei_reabrir_processo.gif" alt="Reabrir Processo" title="Reabrir Processo" />';
              }

              if ($bolFlagAberto && $bolAcaoConcluirProcedimento) {
                $strAcoesProcedimento .= '<a href="#" onclick="concluirProcesso();" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema"  tabindex="'.$numTabBotao.'" src="imagens/sei_concluir_processo.gif" alt="Concluir Processo" title="Concluir Processo" />';
              }

              if ($bolAcaoProcedimentoGerarPdf && $numProtocolosAssociados > 0) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_gerar_pdf&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_gerar_arquivo_processo.gif" alt="Gerar Arquivo PDF do Processo" title="Gerar Arquivo PDF do Processo"/></a>';
              }

              if ($bolAcaoProcedimentoGerarZip && $numProtocolosAssociados > 0) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_gerar_zip&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_gerar_zip_processo.png" alt="Gerar Arquivo ZIP do Processo" title="Gerar Arquivo ZIP do Processo"/></a>';
              }

              if ($bolFlagAberto && !$bolFlagBloqueado && !$bolFlagSobrestado && $bolAcaoExcluirProcedimento && $objProcedimentoDTO->getNumIdUnidadeGeradoraProtocolo() == $numIdUnidadeAtual && $numProtocolosAssociados == 0) {
                $strAcoesProcedimento .= '<a href="#" onclick="excluirProcesso();" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema"  tabindex="'.$numTabBotao.'" src="imagens/sei_lixeira.png" alt="Excluir" title="Excluir" />';
              }

              if ($bolFlagAberto && $bolAcaoReencaminharOuvidoria && $objUnidadeDTO->getStrSinOuvidoria() == 'S' && $objProcedimentoDTO->getStrSinOuvidoriaTipoProcedimento() == 'S') {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_reencaminhar_ouvidoria&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_reencaminhar_ouvidoria.gif" alt="Correção de Encaminhamento" title="Correção de Encaminhamento" /></a>';
              }

              if ($bolAcaoFinalizarOuvidoria && $objUnidadeDTO->getStrSinOuvidoria() == 'S' && $objProcedimentoDTO->getStrSinOuvidoriaTipoProcedimento() == 'S' /* && $objProcedimentoDTO->getNumIdUnidadeGeradoraProtocolo()==$numIdUnidadeAtual*/) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_finalizar_ouvidoria&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_finalizar_ouvidoria.png" alt="Registro do Atendimento" title="Registro do Atendimento" /></a>';
              }

              if ($bolAcaoAndamentoSituacaoGerenciar && $bolFlagSituacao && $strStaNivelAcessoGlobal != ProtocoloRN::$NA_SIGILOSO) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=andamento_situacao_gerenciar&acao_origem=procedimento_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_situacao.png" alt="Gerenciar Ponto de Controle" title="Gerenciar Ponto de Controle" /></a>';
              }

              if ($bolFlagAberto && $bolAcaoAndamentoMarcadorGerenciar) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=andamento_marcador_gerenciar&acao_origem=procedimento_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_marcador.png" alt="Gerenciar Marcador" title="Gerenciar Marcador" /></a>';
              }

              if ($bolFlagTramitacao && $bolAcaoProcedimentoControlar && !$bolFlagAnexado) {
                $strAcoesProcedimento .= '<a href="#" onclick="parent.parent.document.location.href=\\\''.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_controlar&acao_origem=procedimento_visualizar&acao_retorno=principal'.$objPaginaSEI->montarAncora($dblIdProcedimento)).'\\\';" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_controle_processos.gif" alt="Controle de Processos" title="Controle de Processos" /></a>';
              }

              if ($bolAcaoProcedimentoPesquisar && $strStaNivelAcessoGlobal != ProtocoloRN::$NA_SIGILOSO && $numCodigoAcesso!=ProtocoloRN::$CA_BLOCO) {
                $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_pesquisar&acao_origem=procedimento_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_pesquisa.png" alt="Pesquisar no Processo" title="Pesquisar no Processo" /></a>';
              }

              foreach ($SEI_MODULOS as $seiModulo) {
                if (($arrRetIntegracao = $seiModulo->executar('montarBotaoProcesso', $objProcedimentoAPI)) != null) {
                  foreach ($arrRetIntegracao as $strAcaoProcedimento) {
                    $strAcoesProcedimento .= $strAcaoProcedimento;
                  }
                }
              }

              if ($objProcedimentoDTO->getStrStaEstadoProtocolo() == ProtocoloRN::$TE_PROCEDIMENTO_ANEXADO) {

                $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
                $objRelProtocoloProtocoloDTO->retDblIdProtocolo1();
                $objRelProtocoloProtocoloDTO->retStrProtocoloFormatadoProtocolo1();
                $objRelProtocoloProtocoloDTO->setDblIdProtocolo2($objProcedimentoDTO->getDblIdProcedimento());
                $objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);

                $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
                $objRelProtocoloProtocoloDTO = $objRelProtocoloProtocoloRN->consultarRN0841($objRelProtocoloProtocoloDTO);

                $strHtmlProcesso = '<div style="font-size:1.2em;display:inline;">Processo anexado ao processo <a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=arvore_visualizar&id_procedimento='.$objRelProtocoloProtocoloDTO->getDblIdProtocolo1().'&id_procedimento_anexado='.$objProcedimentoDTO->getDblIdProcedimento()).'" target="_blank" style="font-size:1em;">'.$objRelProtocoloProtocoloDTO->getStrProtocoloFormatadoProtocolo1().'</a></div>';

              } else {

                $objAtividadeDTO = new AtividadeDTO();
                $objAtividadeDTO->setDistinct(true);
                $objAtividadeDTO->retStrSiglaUnidade();
                $objAtividadeDTO->retStrDescricaoUnidade();

                $objAtividadeDTO->setOrdStrSiglaUnidade(InfraDTO::$TIPO_ORDENACAO_ASC);

                if ($strStaNivelAcessoGlobal == ProtocoloRN::$NA_SIGILOSO) {
                  $objAtividadeDTO->retNumIdUsuario();
                  $objAtividadeDTO->retStrSiglaUsuario();
                  $objAtividadeDTO->retStrNomeUsuario();
                } else {
                  $objAtividadeDTO->retNumIdUsuarioAtribuicao();
                  $objAtividadeDTO->retStrSiglaUsuarioAtribuicao();
                  $objAtividadeDTO->retStrNomeUsuarioAtribuicao();

                  //ordena descendente pois no envio de processo que já existe na unidade e está atribuído ficará com mais de um andamento em aberto
                  //desta forma os andamentos com usuário nulo (envios do processo) serão listados depois
                  $objAtividadeDTO->setOrdStrSiglaUsuarioAtribuicao(InfraDTO::$TIPO_ORDENACAO_DESC);

                }
                $objAtividadeDTO->setDblIdProtocolo($dblIdProcedimento);
                $objAtividadeDTO->setDthConclusao(null);

                //sigiloso sem credencial nao considera o usuario atual
                if ($strStaNivelAcessoGlobal == ProtocoloRN::$NA_SIGILOSO) {

                  $objAcessoDTO = new AcessoDTO();
                  $objAcessoDTO->setDistinct(true);
                  $objAcessoDTO->retNumIdUsuario();
                  $objAcessoDTO->setDblIdProtocolo($dblIdProcedimento);
                  $objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_PROCESSO);

                  $objAcessoRN = new AcessoRN();
                  $arrObjAcessoDTO = $objAcessoRN->listar($objAcessoDTO);

                  $objAtividadeDTO->setNumIdUsuario(InfraArray::converterArrInfraDTO($arrObjAcessoDTO, 'IdUsuario'), InfraDTO::$OPER_IN);
                }

                $arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);

                if ($strStaNivelAcessoGlobal != ProtocoloRN::$NA_SIGILOSO) {
                  //filtra andamentos com indicação de usuário atribuído
                  $arrObjAtividadeDTO = InfraArray::distinctArrInfraDTO($arrObjAtividadeDTO, 'SiglaUnidade');
                }

                if (count($arrObjAtividadeDTO) == 0) {
                  $strHtmlProcesso .= 'Processo não possui andamentos abertos.';
                } else {

                  foreach ($arrObjAtividadeDTO as $objAtividadeDTO) {

                    $objAtividadeDTO->setStrSiglaUnidade($objPaginaSEI->formatarParametrosJavaScript($objAtividadeDTO->getStrSiglaUnidade()));
                    $objAtividadeDTO->setStrDescricaoUnidade($objPaginaSEI->formatarParametrosJavaScript($objAtividadeDTO->getStrDescricaoUnidade()));

                    if ($objAtividadeDTO->isSetNumIdUsuarioAtribuicao()) {
                      $objAtividadeDTO->setStrSiglaUsuarioAtribuicao($objPaginaSEI->formatarParametrosJavaScript($objAtividadeDTO->getStrSiglaUsuarioAtribuicao()));
                      $objAtividadeDTO->setStrNomeUsuarioAtribuicao($objPaginaSEI->formatarParametrosJavaScript($objAtividadeDTO->getStrNomeUsuarioAtribuicao()));
                    }

                    if ($objAtividadeDTO->isSetNumIdUsuario()) {
                      $objAtividadeDTO->setStrSiglaUsuario($objPaginaSEI->formatarParametrosJavaScript($objAtividadeDTO->getStrSiglaUsuario()));
                      $objAtividadeDTO->setStrNomeUsuario($objPaginaSEI->formatarParametrosJavaScript($objAtividadeDTO->getStrNomeUsuario()));
                    }

                  }

                  if (count($arrObjAtividadeDTO) == 1) {
                    if ($strStaNivelAcessoGlobal != ProtocoloRN::$NA_SIGILOSO) {
                      $strHtmlProcesso .= 'Processo '.(!$bolFlagSobrestado ? 'aberto somente' : 'sobrestado').' na unidade ';
                      $objAtividadeDTO = $arrObjAtividadeDTO[0];
                      $strHtmlProcesso .= '<a alt="'.$objAtividadeDTO->getStrDescricaoUnidade().'" title="'.$objAtividadeDTO->getStrDescricaoUnidade().'" class="ancoraSigla">'.$objAtividadeDTO->getStrSiglaUnidade().'</a>';
                      if ($objAtividadeDTO->getNumIdUsuarioAtribuicao() != null) {
                        $strHtmlProcesso .= ' (atribuído para <a alt="'.$objAtividadeDTO->getStrNomeUsuarioAtribuicao().'" title="'.$objAtividadeDTO->getStrNomeUsuarioAtribuicao().'" class="ancoraSigla">'.$objAtividadeDTO->getStrSiglaUsuarioAtribuicao().'</a>)';
                      }
                      $strHtmlProcesso .= '.';
                    } else {
                      $strHtmlProcesso .= 'Processo '.(!$bolFlagSobrestado ? 'aberto somente' : 'sobrestado').' com o usuário ';
                      $objAtividadeDTO = $arrObjAtividadeDTO[0];
                      $strHtmlProcesso .= '<a alt="'.$objAtividadeDTO->getStrNomeUsuario().'" title="'.$objAtividadeDTO->getStrNomeUsuario().'" class="ancoraSigla">'.$objAtividadeDTO->getStrSiglaUsuario().'</a>';
                      $strHtmlProcesso .= '&nbsp;/&nbsp;';
                      $strHtmlProcesso .= '<a alt="'.$objAtividadeDTO->getStrDescricaoUnidade().'" title="'.$objAtividadeDTO->getStrDescricaoUnidade().'" class="ancoraSigla">'.$objAtividadeDTO->getStrSiglaUnidade().'</a>';
                      $strHtmlProcesso .= '.';
                    }
                  } else {
                    if ($strStaNivelAcessoGlobal != ProtocoloRN::$NA_SIGILOSO) {
                      $strHtmlProcesso .= 'Processo aberto nas unidades:<br />';
                      foreach ($arrObjAtividadeDTO as $objAtividadeDTO) {
                        $strHtmlProcesso .= '<a alt="'.$objAtividadeDTO->getStrDescricaoUnidade().'" title="'.$objAtividadeDTO->getStrDescricaoUnidade().'" class="ancoraSigla">'.$objAtividadeDTO->getStrSiglaUnidade().'</a>';
                        if ($objAtividadeDTO->getNumIdUsuarioAtribuicao() != null) {
                          $strHtmlProcesso .= ' (atribuído para <a alt="'.$objAtividadeDTO->getStrNomeUsuarioAtribuicao().'" title="'.$objAtividadeDTO->getStrNomeUsuarioAtribuicao().'" class="ancoraSigla">'.$objAtividadeDTO->getStrSiglaUsuarioAtribuicao().'</a>)';
                        }
                        $strHtmlProcesso .= '<br />';
                      }
                    } else {
                      $strHtmlProcesso .= 'Processo aberto com os usuários:<br />';
                      foreach ($arrObjAtividadeDTO as $objAtividadeDTO) {
                        $strHtmlProcesso .= '<a alt="'.$objAtividadeDTO->getStrNomeUsuario().'" title="'.$objAtividadeDTO->getStrNomeUsuario().'" class="ancoraSigla">'.$objAtividadeDTO->getStrSiglaUsuario().'</a>';
                        $strHtmlProcesso .= '&nbsp;/&nbsp;';
                        $strHtmlProcesso .= '<a alt="'.$objAtividadeDTO->getStrDescricaoUnidade().'" title="'.$objAtividadeDTO->getStrDescricaoUnidade().'" class="ancoraSigla">'.$objAtividadeDTO->getStrSiglaUnidade().'</a>';
                        $strHtmlProcesso .= '<br />';
                      }
                    }
                  }
                }
                $strHtmlProcesso .= '<br />';
              }

              foreach ($SEI_MODULOS as $seiModulo) {
                if (($strMensagemModulo = $seiModulo->executar('montarMensagemProcesso', $objProcedimentoAPI)) != null) {
                  $strHtmlProcesso .= '<br />'.$strMensagemModulo.'<br />';
                }
              }
            }

            $strNoProc .= 'Nos['.$numNo.'].acoes = \''.$strAcoesProcedimento.'\';'."\n";
            $strNoProc .= 'Nos['.$numNo.'].src = \'\';'."\n";
            $strNoProc .= 'Nos['.$numNo.'].html = \''.$strHtmlProcesso.'\';';
            $numNo++;

            $objRelBaseConhecTipoProcedDTO = new RelBaseConhecTipoProcedDTO();
            $objRelBaseConhecTipoProcedDTO->retNumIdBaseConhecimento();
            $objRelBaseConhecTipoProcedDTO->retStrDescricaoBaseConhecimento();
            $objRelBaseConhecTipoProcedDTO->retStrSiglaUnidadeBaseConhecimento();
            $objRelBaseConhecTipoProcedDTO->setNumIdTipoProcedimento($objProcedimentoDTO->getNumIdTipoProcedimento());
            $objRelBaseConhecTipoProcedDTO->setStrStaEstadoBaseConhecimento(BaseConhecimentoRN::$TE_LIBERADO);

            $objRelBaseConhecTipoProcedRN = new RelBaseConhecTipoProcedRN();
            $arrObjRelBaseConhecTipoProcedDTO = $objRelBaseConhecTipoProcedRN->listar($objRelBaseConhecTipoProcedDTO);

            if (count($arrObjRelBaseConhecTipoProcedDTO)) {

              if ($objSessaoSEI->verificarPermissao('base_conhecimento_listar_associadas')) {

                $strNosAcaoProc .= 'NosAcoes['.$numNoAcao++.'] = new infraArvoreAcao("BASE_CONHECIMENTO",'.
                    '"BC",'.
                    '"'.$dblIdProcedimento.'",'.
                    '"'.$objSessaoSEI->assinarLink('controlador.php?acao=base_conhecimento_listar_associadas&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&id_tipo_procedimento='.$objProcedimentoDTO->getNumIdTipoProcedimento().'&arvore=1').'",'.
                    '"ifrVisualizacao",'.
                    '"Visualizar Bases de Conhecimento Associadas",'.
                    '"imagens/base_conhecimento.gif",'.
                    'true);'."\n";
              }
            }

            if ($objProcedimentoDTO->getStrSinCiencia() == 'S' && $numCodigoAcesso > 0) {

              $strNosAcaoProc .= 'NosAcoes['.$numNoAcao++.'] = new infraArvoreAcao("CIENCIAS",'.
                  '"C",'.
                  '"'.$dblIdProcedimento.'",'.
                  '"'.$objSessaoSEI->assinarLink('controlador.php?acao=protocolo_ciencia_listar&acao_origem=procedimento_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'",'.
                  '"ifrVisualizacao",'.
                  '"Visualizar Ciências",'.
                  '"imagens/sei_ciencia_pequeno.gif",'.
                  'true);'."\n";
            }

            foreach ($SEI_MODULOS as $seiModulo) {
              if (($arrRetIntegracao = $seiModulo->executar('montarIconeProcesso', $objProcedimentoAPI)) != null) {
                foreach ($arrRetIntegracao as $objArvoreAcaoItemAPI) {
                  $strNosAcaoProc .= 'NosAcoes['.$numNoAcao++.'] = new infraArvoreAcao("'.$objArvoreAcaoItemAPI->getTipo().'",'.
                      '"'.$objArvoreAcaoItemAPI->getId().'",'.
                      '"'.$objArvoreAcaoItemAPI->getIdPai().'",'.
                      '"'.$objArvoreAcaoItemAPI->getHref().'",'.
                      '"'.$objArvoreAcaoItemAPI->getTarget().'",'.
                      '"'.$objArvoreAcaoItemAPI->getTitle().'",'.
                      '"'.$objArvoreAcaoItemAPI->getIcone().'",'.
                      ($objArvoreAcaoItemAPI->getSinHabilitado() == 'S' ? 'true' : 'false').');'."\n";
                }
              }
            }
          }
        }
      }
      
      return $objProcedimentoDTO;
    }catch(Exception $e){
      throw new InfraException('Erro montando ações para processo.',$e);
    }
  }
  
  public static function montarIdentificacaoArvore($objProcedimentoDTO){
    return $objProcedimentoDTO->getStrProtocoloProcedimentoFormatado();
  }

  public static function processarControleProcessos($objProcedimentoDTO, $bolAcaoRegistrarAnotacao, $bolAcaoAndamentoSituacaoGerenciar, $bolAcaoAndamentoMarcadorGerenciar, $arrProcessosVisitados, $arrRetIconeIntegracao, &$strImagemStatus, &$strLinkUsuarioAtribuicao, &$strLinkProcesso, &$strTextoCheckBox){

    $strLinkUsuarioAtribuicao = '&nbsp;';

    $dblIdProcedimento = $objProcedimentoDTO->getDblIdProcedimento();

    if ($objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo()==ProtocoloRN::$NA_SIGILOSO){
      $objProcedimentoDTO->setStrDescricaoProtocolo(null);
    }

    $arrObjAtividadeDTO = $objProcedimentoDTO->getArrObjAtividadeDTO();
    $strCssProcesso='';
    foreach($arrObjAtividadeDTO as $objAtividadeDTO){

      $strImagemStatus = AnotacaoINT::montarIconeAnotacao($objProcedimentoDTO->getObjAnotacaoDTO(),$bolAcaoRegistrarAnotacao, $dblIdProcedimento, '');
      $strCssProcesso = 'class="';
      $numTipoVisualizacao=$objAtividadeDTO->getNumTipoVisualizacao();

      if ($objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo()!=ProtocoloRN::$NA_SIGILOSO){
        if ($numTipoVisualizacao & AtividadeRN::$TV_NAO_VISUALIZADO){
          $strCssProcesso .= 'processoNaoVisualizado';
        }else{
          $strCssProcesso .= 'processoVisualizado';
          if ($arrProcessosVisitados != null && isset($arrProcessosVisitados[$objProcedimentoDTO->getDblIdProcedimento()])){
            $strCssProcesso .= ' processoVisitado';
          }
        }
      }else{

        if ($objProcedimentoDTO->getStrSinCredencialProcesso()=='S'){
          if ($numTipoVisualizacao & AtividadeRN::$TV_NAO_VISUALIZADO){
            $strCssProcesso .= 'processoNaoVisualizadoSigiloso';
          }else{
            $strCssProcesso .= 'processoVisualizadoSigiloso';
            if ($arrProcessosVisitados != null && isset($arrProcessosVisitados[$objProcedimentoDTO->getDblIdProcedimento()])){
              $strCssProcesso .= ' processoVisitadoSigiloso';
            }
          }
          if ($objProcedimentoDTO->getStrSinCredencialAssinatura()=='S'){
            $strImagemStatus .= '<a href="javascript:void(0);" '.PaginaSEI::montarTitleTooltip('Processo possui um documento com Credencial para Assinatura').'><img src="imagens/sei_credencial_assinatura.gif" class="imagemStatus" /></a>';
          }
        }else{
          if ($objProcedimentoDTO->getStrSinCredencialAssinatura()=='S'){
            $strCssProcesso .= 'processoCredencialAssinaturaSigiloso';
            $strImagemStatus .= '<a href="javascript:void(0);" '.PaginaSEI::montarTitleTooltip('Processo possui um documento com Credencial para Assinatura').'><img src="imagens/sei_credencial_assinatura.gif" class="imagemStatus" /></a>';
          }
          if ($arrProcessosVisitados != null && isset($arrProcessosVisitados[$objProcedimentoDTO->getDblIdProcedimento()])){
            $strCssProcesso .= ' processoVisitadoCredencialAssinatura';
          }
        }
      }
      $strCssProcesso .= '"';

      $strImagemStatus .= self::montarIconeVisualizacao($numTipoVisualizacao, $objProcedimentoDTO, $arrRetIconeIntegracao, $bolAcaoAndamentoSituacaoGerenciar, $bolAcaoAndamentoMarcadorGerenciar, '');

      if ($objAtividadeDTO->getNumIdUsuarioAtribuicao()!=null){
        $strLinkUsuarioAtribuicao = '(<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_atribuicao_listar&acao_retorno='.$_GET['acao'].'&id_usuario_atribuicao='.$objAtividadeDTO->getNumIdUsuarioAtribuicao().'&id_procedimento='.$dblIdProcedimento).'" title="Atribuído para '.PaginaSEI::tratarHTML($objAtividadeDTO->getStrNomeUsuarioAtribuicao()).'" class="ancoraSigla">'.PaginaSEI::tratarHTML($objAtividadeDTO->getStrSiglaUsuarioAtribuicao()).'</a>)';
      }

      //pega somente do primeiro andamento, se remetido por outra unidade volta a ficar vermelho pois vem como não visualizado
      break;
    }

    if (SessaoSEI::getInstance()->getStrSinAcessibilidade()=='S'){
      $strTextoCheckBox = PaginaSEI::getInstance()->tratarHTML($objProcedimentoDTO->getStrDescricaoProtocolo().' / '.$objProcedimentoDTO->getStrNomeTipoProcedimento().' / '.$objProcedimentoDTO->getStrProtocoloProcedimentoFormatado());
    }else{
      $strTextoCheckBox = $objProcedimentoDTO->getStrProtocoloProcedimentoFormatado();
    }

    $strLinkProcesso = '<a '.$strCssProcesso.' href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_procedimento='.$dblIdProcedimento).'" '.PaginaSEI::montarTitleTooltip($objProcedimentoDTO->getStrDescricaoProtocolo(),$objProcedimentoDTO->getStrNomeTipoProcedimento()).' >'.$objProcedimentoDTO->getStrProtocoloProcedimentoFormatado().'</a>';
  }

  public static function montarCamposPesquisaSigiloso(PesquisaSigilosoDTO &$objPesquisaSigilosoDTO, &$strCss,&$strJs,&$strJsInicializar,&$strHtml,$bolPesquisarCredenciais=false)
  {

    $strProtocoloSigiloso = $_POST['txtProtocoloSigiloso'];
    $numIdTipoProcedimentoSigiloso = $_POST['selTipoProcedimentoSigiloso'];
    $strIdInteressadoSigiloso = $_POST['hdnIdInteressadoSigiloso'];
    $strNomeInteressadoSigiloso = $_POST['txtInteressadoSigiloso'];
    $strObservacoesSigiloso = $_POST['txtObservacoesSigiloso'];

    if(!InfraString::isBolVazia($strProtocoloSigiloso)) {
      $objPesquisaSigilosoDTO->setStrProtocoloFormatadoPesquisa($strProtocoloSigiloso);
    }

    if(!InfraString::isBolVazia($numIdTipoProcedimentoSigiloso) && $numIdTipoProcedimentoSigiloso!='null') {
      $objPesquisaSigilosoDTO->setNumIdTipoProcedimento($numIdTipoProcedimentoSigiloso);
    }

    if(!InfraString::isBolVazia($strIdInteressadoSigiloso)) {
      $objPesquisaSigilosoDTO->setNumIdContatoParticipante($strIdInteressadoSigiloso);
    }

    if(!InfraString::isBolVazia($strObservacoesSigiloso)) {
      $objPesquisaSigilosoDTO->setStrIdxObservacao($strObservacoesSigiloso);
    }

    $strCss .= "#lblProtocoloSigiloso {position:absolute;left:0%;width:17%;}\n";
    $strCss .= "#txtProtocoloSigiloso {position:absolute;left:18%;width:21%}\n";

    $strCss .= "#lblTipoProcedimentoSigiloso {position:absolute;left:0%;width:17%;}\n";
    $strCss .= "#selTipoProcedimentoSigiloso {position:absolute;left:18%;width:20%;width:60%;}\n";

    $strCss .= "#lblInteressadoSigiloso {position:absolute;left:0%;width:17%;}\n";
    $strCss .= "#txtInteressadoSigiloso {position:absolute;left:18%;width:20%;width:60%;}\n";

    $strCss .= "#lblObservacoesSigiloso {position:absolute;left:0%;width:17%;}\n";
    $strCss .= "#txtObservacoesSigiloso {position:absolute;left:18%;width:20%;width:60%;}\n";

    $strCss .= "#lblUsuarioSigiloso {position:absolute;left:0%;width:17%;}\n";
    $strCss .= "#txtUsuarioSigiloso {position:absolute;left:18%;width:20%;width:60%;}\n";

    $strLinkAjaxContatos = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=contato_auto_completar_pesquisa');
    $strItensSelTipoProcedimento 	= TipoProcedimentoINT::montarSelectNome('null','&nbsp;',$numIdTipoProcedimentoSigiloso);

    $strJs = 'function limpar(){'."\n";
    $strJs .= '  document.getElementById(\'txtProtocoloSigiloso\').value=\'\''."\n";
    $strJs .= '  document.getElementById(\'selTipoProcedimentoSigiloso\').selectedIndex=0'."\n";
    $strJs .= '  objAutoCompletarInteressado.limpar();'."\n";
    $strJs .= '  document.getElementById(\'txtObservacoesSigiloso\').value=\'\''."\n";

    if ($bolPesquisarCredenciais){
      $strJs .= '  objAutoCompletarUsuario.limpar();'."\n";
    }

    $strJs .= '}'."\n\n";

    $strJs .= 'function trocarFiltroUsuarioSigiloso(){'."\n";
    $strJs .= '  objAutoCompletarInteressado.limpar();'."\n";
    $strJs .= '}'."\n\n";

    $strJs .= "var objAutoCompletarInteressado=null;\n";

    $strJsInicializar="objAutoCompletarInteressado = new infraAjaxAutoCompletar('hdnIdInteressadoSigiloso','txtInteressadoSigiloso','".$strLinkAjaxContatos."');\n";
    $strJsInicializar.="objAutoCompletarInteressado.limparCampo = true;\n";
    $strJsInicializar.="objAutoCompletarInteressado.prepararExecucao = function(){\n";
    $strJsInicializar.="return 'palavras_pesquisa='+document.getElementById('txtInteressadoSigiloso').value;\n";
    $strJsInicializar.="};\n";
    $strJsInicializar.="objAutoCompletarInteressado.selecionar('".$strIdInteressadoSigiloso."','".PaginaSEI::getInstance()->formatarParametrosJavaScript($strNomeInteressadoSigiloso)."');\n";
    
    $strHtml.='<div id="divProtocoloSigiloso" class="infraAreaDados" style="height:3em">'."\n";
    $strHtml.='<label id="lblProtocoloSigiloso" for="txtProtocoloSigiloso" accesskey="" class="infraLabelOpcional">Nº do Processo:</label>'."\n";
    $strHtml.='<input type="text" id="txtProtocoloSigiloso" name="txtProtocoloSigiloso" class="infraText" value="'.PaginaSEI::tratarHTML($strProtocoloSigiloso).'" tabindex="'.PaginaSEI::getInstance()->getProxTabDados().'" />'."\n";
    $strHtml.='</div>'."\n";

    $strHtml.='<div id="divTipoProcedimentoSigiloso" class="infraAreaDados" style="height:3em">'."\n";
    $strHtml.='<label id="lblTipoProcedimentoSigiloso" for="selTipoProcedimentoSigiloso" accesskey="" class="infraLabelOpcional">Tipo do Processo:</label>'."\n";
    $strHtml.='<select id="selTipoProcedimentoSigiloso" name="selTipoProcedimentoSigiloso" class="infraSelect" tabindex="'.PaginaSEI::getInstance()->getProxTabDados().'" >'."\n";
    $strHtml.=$strItensSelTipoProcedimento;
    $strHtml.='</select>'."\n";
    $strHtml.='</div>'."\n";

    $strHtml.='<div id="divInteressadoSigiloso" class="infraAreaDados" style="height:3em">'."\n";
    $strHtml.='<label id="lblInteressadoSigiloso" for="txtInteressadoSigiloso" accesskey=""  class="infraLabelOpcional">Interessado:</label>'."\n";
    $strHtml.='<input type="text" id="txtInteressadoSigiloso" name="txtInteressadoSigiloso" class="infraText" value="'.PaginaSEI::tratarHTML($strNomeInteressadoSigiloso).'" tabindex="'.PaginaSEI::getInstance()->getProxTabDados().'" />'."\n";
    $strHtml.='<input type="hidden" id="hdnIdInteressadoSigiloso" name="hdnIdInteressadoSigiloso" class="infraText" value="'.$strIdInteressadoSigiloso.'" />'."\n";
    $strHtml.='</div>'."\n";

    $strHtml.='<div id="divObservacoesSigiloso" class="infraAreaDados" style="height:3em">'."\n";
    $strHtml.='<label id="lblObservacoesSigiloso" for="txtObservacoesSigiloso" accesskey=""  class="infraLabelOpcional">Obs. desta Unidade:</label>'."\n";
    $strHtml.='<input type="text" id="txtObservacoesSigiloso" name="txtObservacoesSigiloso" class="infraText" value="'.PaginaSEI::tratarHTML($strObservacoesSigiloso).'" tabindex="'.PaginaSEI::getInstance()->getProxTabDados().'" />'."\n";
    $strHtml.='</div>'."\n";

    if ($bolPesquisarCredenciais){

      $numIdContato = $_POST['hdnIdUsuarioSigiloso'];
      $strNomeUsuario = $_POST['txtUsuarioSigiloso'];

      $strLinkAjaxUsuario = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=contato_auto_completar_usuario_pesquisa');

      $strJs.="var objAutoCompletarUsuario = null;\n";

      $strJsInicializar.="objAutoCompletarUsuario = new infraAjaxAutoCompletar('hdnIdUsuarioSigiloso','txtUsuarioSigiloso','".$strLinkAjaxUsuario."');\n";
      $strJsInicializar.="objAutoCompletarUsuario.limparCampo = true;\n";
      $strJsInicializar.="objAutoCompletarUsuario.prepararExecucao = function(){\n";
      $strJsInicializar.="  return 'palavras_pesquisa='+document.getElementById('txtUsuarioSigiloso').value+'&sin_usuario_interno=S&sin_usuario_externo=N';\n";
      $strJsInicializar.="};\n";
      $strJsInicializar.="objAutoCompletarUsuario.selecionar('".$numIdContato."','".PaginaSEI::getInstance()->formatarParametrosJavaScript($strNomeUsuario)."');\n";

      if(!InfraString::isBolVazia($numIdContato)) {
        $objPesquisaSigilosoDTO->setNumIdContatoUsuario($numIdContato);
      }

      $strHtml.='<div id="divUsuarioSigiloso" class="infraAreaDados" style="height:3em">'."\n";
      $strHtml.='<label id="lblUsuarioSigiloso" for="txtUsuarioSigiloso" class="infraLabelOpcional">Credencial na Unidade:</label>'."\n";
      $strHtml.='<input type="text" id="txtUsuarioSigiloso" name="txtUsuarioSigiloso" class="infraText" value="'.PaginaSEI::tratarHTML($strNomeUsuario).'" tabindex="'.PaginaSEI::getInstance()->getProxTabDados().'" />'."\n";
      $strHtml.='<input type="hidden" id="hdnIdUsuarioSigiloso" name="hdnIdUsuarioSigiloso" class="infraText" value="'.$numIdContato.'" />'."\n";
      $strHtml.='</div>'."\n";
    }
  }

  public static function montarIconesIntegracaoControleProcessos($arrObjProcedimentoDTO){

    global $SEI_MODULOS;

    $arrRetIconeIntegracao = array();

    if (count($SEI_MODULOS)) {

      $arrObjProcedimentoAPI = array();
      foreach ($arrObjProcedimentoDTO as $objProcedimentoDTO) {

        $dto = new ProcedimentoAPI();
        $dto->setIdProcedimento($objProcedimentoDTO->getDblIdProcedimento());
        $dto->setNumeroProtocolo($objProcedimentoDTO->getStrProtocoloProcedimentoFormatado());
        $dto->setIdTipoProcedimento($objProcedimentoDTO->getNumIdTipoProcedimento());
        $dto->setNomeTipoProcedimento($objProcedimentoDTO->getStrNomeTipoProcedimento());
        $dto->setNivelAcesso($objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo());
        $dto->setIdUnidadeGeradora($objProcedimentoDTO->getNumIdUnidadeGeradoraProtocolo());
        $dto->setIdOrgaoUnidadeGeradora($objProcedimentoDTO->getNumIdOrgaoUnidadeGeradoraProtocolo());
        $dto->setIdHipoteseLegal($objProcedimentoDTO->getNumIdHipoteseLegalProtocolo());
        $dto->setGrauSigilo($objProcedimentoDTO->getStrStaGrauSigiloProtocolo());
        $arrObjProcedimentoAPI[] = $dto;
      }

      if (count($arrObjProcedimentoAPI)) {

        foreach ($SEI_MODULOS as $seiModulo) {
          if (($arrRetIconeIntegracaoModulo = $seiModulo->executar('montarIconeControleProcessos', $arrObjProcedimentoAPI)) != null) {
            foreach ($arrRetIconeIntegracaoModulo as $dblIdProcedimento => $arrIcone) {
              if (!isset($arrRetIconeIntegracao[$dblIdProcedimento])) {
                $arrRetIconeIntegracao[$dblIdProcedimento] = $arrIcone;
              } else {
                $arrRetIconeIntegracao[$dblIdProcedimento] = array_merge($arrRetIconeIntegracao[$dblIdProcedimento], $arrIcone);
              }
            }
          }
        }
      }
    }
    return $arrRetIconeIntegracao;
  }
}
?>