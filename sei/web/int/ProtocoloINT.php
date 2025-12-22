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

require_once dirname(__FILE__) . '/../SEI.php';

class ProtocoloINT extends InfraINT
{
  public static function buscarProtocoloFormatadoRI1010($dblIdProtocolo)
  {

    $ret = '';

    if ($dblIdProtocolo != null) {
      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->retStrProtocoloFormatado();
      $objProtocoloDTO->setDblIdProtocolo($dblIdProtocolo);

      $objProtocoloRN = new ProtocoloRN();
      $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

      $ret = $objProtocoloDTO->getStrProtocoloFormatado();

    }

    return $ret;
  }

  public static function pesquisarLinkEditor($dblIdProcedimento, $dlbIdDocumento, $strIdProtocolo)
  {

    $objInfraException = new InfraException();

    if (InfraString::isBolVazia($strIdProtocolo)) {
      $objInfraException->lancarValidacao('Protocolo para pesquisa não informado.');
    }

    $strIdProtocolo = InfraUtil::retirarFormatacao(trim($strIdProtocolo));

    $objProtocoloDTO=new ProtocoloDTO();
    $objProtocoloDTO->setStrProtocoloFormatadoPesquisa($strIdProtocolo);
    $objProtocoloRN = new ProtocoloRN();
    $arrObjProtocoloDTOPesquisado = $objProtocoloRN->pesquisarProtocoloFormatado($objProtocoloDTO);

    if (count($arrObjProtocoloDTOPesquisado)==0) {
      $objInfraException->lancarValidacao('Protocolo não encontrado.');
    }

    $objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();

    if ($arrObjProtocoloDTOPesquisado[0]->getStrStaProtocolo()==ProtocoloRN::$TP_PROCEDIMENTO){
      $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_PROCEDIMENTOS);
    }else if ($arrObjProtocoloDTOPesquisado[0]->getStrStaProtocolo()==ProtocoloRN::$TP_DOCUMENTO_GERADO){
      $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_DOCUMENTOS_GERADOS);
    }else if ($arrObjProtocoloDTOPesquisado[0]->getStrStaProtocolo()==ProtocoloRN::$TP_DOCUMENTO_RECEBIDO){
      $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_DOCUMENTOS_RECEBIDOS);
    }else{
      $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_TODOS);
    }

    $objPesquisaProtocoloDTO->setDblIdProtocolo($arrObjProtocoloDTOPesquisado[0]->getDblIdProtocolo());
    $objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_AUTORIZADO);

    $arrObjProtocoloDTO = $objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO);

    if (count($arrObjProtocoloDTO) == 0) {
      $objInfraException->lancarValidacao('Protocolo não encontrado.');
    }

    //só permite referenciar documento sigiloso
    if ($arrObjProtocoloDTO[0]->getStrStaNivelAcessoGlobal() == ProtocoloRN::$NA_SIGILOSO &&
       (($arrObjProtocoloDTO[0]->getStrStaProtocolo()==ProtocoloRN::$TP_DOCUMENTO_GERADO || $arrObjProtocoloDTO[0]->getStrStaProtocolo()==ProtocoloRN::$TP_DOCUMENTO_RECEBIDO) && $arrObjProtocoloDTO[0]->getDblIdProcedimentoDocumento() != $dblIdProcedimento)
    ) {
      $objInfraException->lancarValidacao('Protocolo não encontrado.');
    }

    return array('IdProtocolo' => $arrObjProtocoloDTO[0]->getDblIdProtocolo(),
      'ProtocoloFormatado' => $arrObjProtocoloDTO[0]->getStrProtocoloFormatado(),
      'Identificacao' => self::formatarIdentificacao($arrObjProtocoloDTO[0]));
  }

  public static function formatarIdentificacao($objProtocoloDTO){
    if ($objProtocoloDTO->getStrStaProtocolo()==ProtocoloRN::$TP_PROCEDIMENTO){
      return $objProtocoloDTO->getStrNomeTipoProcedimentoProcedimento();
    }else{
      return $objProtocoloDTO->getStrNomeSerieDocumento().' '.$objProtocoloDTO->getStrNumeroDocumento();
    }
  }

  public static function montarSelectStaNivelAcesso($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado)
  {
    $objProtocoloRN = new ProtocoloRN();
    $arrObjNivelAcessoDTO = $objProtocoloRN->listarNiveisAcessoRN0878();

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjNivelAcessoDTO, 'StaNivel', 'Descricao');
  }

  public static function montarStaNivelAcesso($strValor)
  {
    $objProtocoloRN = new ProtocoloRN();
    $arrObjNivelAcessoDTO = $objProtocoloRN->listarNiveisAcessoRN0878();

    foreach ($arrObjNivelAcessoDTO as $objNivelAcessoDTO) {
      if ($objNivelAcessoDTO->getStrStaNivel() == $strValor) {
        return $objNivelAcessoDTO->getStrDescricao();
      }

    }

  }

  public static function calcularDataInicial($numDias)
  {
    return date("d/m/Y", mktime(0, 0, 0, date('m'), date('d') - $numDias, date('Y')));
  }

  public static function montarSelectUnidadesSolicitantesDesarquivamento($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){

    $objArquivamentoDTO = new ArquivamentoDTO();
    $objArquivamentoDTO->setNumTipoFkSolicitacao(InfraDTO::$TIPO_FK_OBRIGATORIA);
    $objArquivamentoDTO->setNumTipoFkLocalizador(InfraDTO::$TIPO_FK_OBRIGATORIA);
    $objArquivamentoDTO->setDistinct(true);
    $objArquivamentoDTO->retNumIdUnidadeSolicitacao();
    $objArquivamentoDTO->retStrSiglaUnidadeSolicitacao();
    $objArquivamentoDTO->setStrStaArquivamento(ArquivamentoRN::$TA_SOLICITADO_DESARQUIVAMENTO);
    $objArquivamentoDTO->setNumIdUnidadeLocalizador(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

    $objArquivamentoRN = new ArquivamentoRN();
    $arrObjArquivamentoDTO = $objArquivamentoRN->listar($objArquivamentoDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjArquivamentoDTO, 'IdUnidadeSolicitacao', 'SiglaUnidadeSolicitacao');
  }

  public static function montarSelectGrauSigilo($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado)
  {
    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, ProtocoloRN::listarGrausSigiloso(), 'StaGrau', 'Descricao');
  }

  public static function montarAcoesArvore($dblIdProcedimento,
                                           $numIdUnidadeAtual,
                                           $bolFlagAberto,
                                           $bolFlagAnexado,
                                           $bolFlagAbertoAnexado,
                                           $bolFlagProtocolo,
                                           $bolFlagArquivo,
                                           $bolFlagTramitacao,
                                           $bolFlagSobrestado,
                                           $bolFlagBloqueado,
                                           $numCodigoAcessoProcedimento,
                                           $strNoPai,
                                           $arrIdRelProtocoloProtocolo,
                                           &$numNo,
                                           &$strNos,
                                           &$numNoAcao,
                                           &$strNosAcao)
  {

    try {

      global $SEI_MODULOS;

      if (count($arrIdRelProtocoloProtocolo)) {

        $objSessaoSEI = SessaoSEI::getInstance();
        $objPaginaSEI = PaginaSEI::getInstance();

        $bolAcaoEscolherBloco = $objSessaoSEI->verificarPermissao('bloco_escolher');
        $bolAcaoDefinirAtividade = $objSessaoSEI->verificarPermissao('procedimento_atualizar_andamento');
        $bolAcaoProcedimentoEnviar = $objSessaoSEI->verificarPermissao('procedimento_enviar');
        $bolAcaoAcompanhamentoCadastrar = $objSessaoSEI->verificarPermissao('acompanhamento_cadastrar');
        $bolAcaoAssinarDocumento = $objSessaoSEI->verificarPermissao('documento_assinar');
        $bolAcaoListarPublicacoes = $objSessaoSEI->verificarPermissao('publicacao_listar');
        $bolAcaoAgendarPublicacao = $objSessaoSEI->verificarPermissao('publicacao_agendar');
        $bolAcaoAlterarDocumento = $objSessaoSEI->verificarPermissao('documento_alterar');
        $bolAcaoAlterarDocumentoRecebido = $objSessaoSEI->verificarPermissao('documento_alterar_recebido');
        $bolAcaoAlterarFormulario = $objSessaoSEI->verificarPermissao('formulario_alterar');
        $bolAcaoImprimirDocumentoWeb = $objSessaoSEI->verificarPermissao('documento_imprimir_web');
        $bolAcaoGerarPublicacaoRelacionada = $objSessaoSEI->verificarPermissao('publicacao_gerar_relacionada');
        $bolAcaoConsultarDocumento = $objSessaoSEI->verificarPermissao('documento_consultar');
        $bolAcaoConsultarDocumentoRecebido = $objSessaoSEI->verificarPermissao('documento_consultar_recebido');
        $bolAcaoDocumentoEnviarEmail = $objSessaoSEI->verificarPermissao('documento_enviar_email');
        $bolAcaoResponderFormularioOuvidoria = $objSessaoSEI->verificarPermissao('responder_formulario_ouvidoria');
        $bolAcaoDownload = $objSessaoSEI->verificarPermissao('documento_download_anexo');
        $bolAcaoDocumentoVersaoListar = $objSessaoSEI->verificarPermissao('documento_versao_listar');
        $bolAcaoExcluirDocumento = $objSessaoSEI->verificarPermissao('documento_excluir');
        $bolAcaoDocumentoCancelar = $objSessaoSEI->verificarPermissao('documento_cancelar');
        $bolAcaoProtocoloSolicitarDesarquivamento = $objSessaoSEI->verificarPermissao('arquivamento_solicitar_desarquivamento');
        $bolAcaoCredencialAssinaturaGerenciar = $objSessaoSEI->verificarPermissao('credencial_assinatura_gerenciar');
        $bolAcaoDocumentoCiencia = $objSessaoSEI->verificarPermissao('documento_ciencia');
        $bolAcaoDocumentoMover = $objSessaoSEI->verificarPermissao('documento_mover');
        $bolAcaoAssinaturaExternaGerenciar = $objSessaoSEI->verificarPermissao('assinatura_externa_gerenciar');
        $bolAcaoAssinaturaVerificar = $objSessaoSEI->verificarPermissao('assinatura_verificar');
        $bolAcaoConcluirProcedimento = $objSessaoSEI->verificarPermissao('procedimento_concluir');
        $bolAcaoReabrirProcedimento = $objSessaoSEI->verificarPermissao('procedimento_reabrir');
        $bolAcaoProtocoloModeloCadastrar = $objSessaoSEI->verificarPermissao('protocolo_modelo_cadastrar');
        $bolAcaoEmailEncaminhar = $objSessaoSEI->verificarPermissao('email_encaminhar');
        $bolAcaoAlterarProcedimento = $objSessaoSEI->verificarPermissao('procedimento_alterar');
        $bolAcaoConsultarProcedimento = $objSessaoSEI->verificarPermissao('procedimento_consultar');
        $bolAcaoProcedimentoDesanexar = $objSessaoSEI->verificarPermissao('procedimento_desanexar');
        $bolAcaoProcedimentoAnexadoCiencia = $objSessaoSEI->verificarPermissao('procedimento_anexado_ciencia');
        $bolAcaoLocalizadorListar = $objSessaoSEI->verificarPermissao('localizador_protocolos_listar');
        $bolAcaoDocumentoGerarCircular = $objSessaoSEI->verificarPermissao('documento_gerar_circular');

        $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
        $arrParametros = $objInfraParametro->listarValores(array('SEI_HABILITAR_AUTENTICACAO_DOCUMENTO_EXTERNO',
                                                                 'SEI_HABILITAR_MOVER_DOCUMENTO',
                                                                 'SEI_NUM_FATOR_DOWNLOAD_AUTOMATICO',
                                                                 'SEI_ACESSO_FORMULARIO_OUVIDORIA'));

        $bolHabilitarAutenticacaoDocumentoExterno = $arrParametros['SEI_HABILITAR_AUTENTICACAO_DOCUMENTO_EXTERNO'];
        $bolHabilitarMoverDocumento = $arrParametros['SEI_HABILITAR_MOVER_DOCUMENTO'];
        $bolAcessoRestritoOuvidoria = ($arrParametros['SEI_ACESSO_FORMULARIO_OUVIDORIA']=='1');

        $numTamDocExternoLink = null;
        if (is_numeric($arrParametros['SEI_NUM_FATOR_DOWNLOAD_AUTOMATICO']) && $arrParametros['SEI_NUM_FATOR_DOWNLOAD_AUTOMATICO'] > 0){
          $objVelocidadeTransferenciaDTO = new VelocidadeTransferenciaDTO();
          $objVelocidadeTransferenciaDTO->retDblVelocidade();
          $objVelocidadeTransferenciaDTO->setNumIdUsuario($objSessaoSEI->getNumIdUsuario());

          $objVelocidadeTransferenciaRN = new VelocidadeTransferenciaRN();
          $objVelocidadeTransferenciaDTO = $objVelocidadeTransferenciaRN->consultar($objVelocidadeTransferenciaDTO);

          if ($objVelocidadeTransferenciaDTO!=null && $objVelocidadeTransferenciaDTO->getDblVelocidade() > 0){
            $numTamDocExternoLink = $arrParametros['SEI_NUM_FATOR_DOWNLOAD_AUTOMATICO'] * $objVelocidadeTransferenciaDTO->getDblVelocidade() * 1024;
          }
        }

        $arrSeriesFormularios = $objInfraParametro->listarValores(array('ID_SERIE_EMAIL','ID_SERIE_OUVIDORIA'), false);

        $numIdSerieEmail = isset($arrSeriesFormularios['ID_SERIE_EMAIL']) ? $arrSeriesFormularios['ID_SERIE_EMAIL'] : null;
        $numIdSerieOuvidoria = isset($arrSeriesFormularios['ID_SERIE_OUVIDORIA']) ? $arrSeriesFormularios['ID_SERIE_OUVIDORIA'] : null;

        $arrExtensoes = array('html' => 0, 'htm' => 0, 'txt' => 0, 'png' => 0, 'jpeg' => 0, 'jpg' => 0, 'gif' => 0 );

        if (!$objPaginaSEI->isBolIpad() && !$objPaginaSEI->isBolIphone() && !$objPaginaSEI->isBolAndroid()) {
          $arrExtensoes = array_merge($arrExtensoes, array('pdf' => 0, 'xls' => 0, 'xlsx' => 0, 'doc' => 0, 'docx' => 0, 'mht' => 0, 'bmp' => 0));
        }

        $objProtocoloRN = new ProtocoloRN();

        $dto = new ProcedimentoDTO();
        $dto->setDblIdProcedimento($dblIdProcedimento);
        $dto->setArrObjRelProtocoloProtocoloDTO(InfraArray::gerarArrInfraDTO('RelProtocoloProtocoloDTO','IdRelProtocoloProtocolo',$arrIdRelProtocoloProtocolo));
        $dto->setStrSinDocTodos('S');
        $dto->setStrSinDocAnexos('S');
        $dto->setStrSinConteudoEmail('S');
        $dto->setStrSinProcAnexados('S');
        $dto->setStrSinDocCircular('S');
        $dto->setStrSinArquivamento('S');

        if ($bolFlagArquivo){
          $objArquivamentoRN = new ArquivamentoRN();
          $arrObjArquivamentoProtocoloDTO = InfraArray::indexarArrInfraDTO($objArquivamentoRN->listarValoresArquivamentoRN1119(),'StaArquivamento');
        }

        $objProcedimentoRN = new ProcedimentoRN();
        $arrObjProcedimentoDTO = $objProcedimentoRN->listarCompleto($dto);

        if (count($arrObjProcedimentoDTO) == 0) {
          $objInfraException = new InfraException();
          $objInfraException->lancarValidacao('Processo não encontrado.');
        }

        $objProcedimentoDTO = $arrObjProcedimentoDTO[0];
        
        $arrObjRelProtocoloProtocoloDTO = $objProcedimentoDTO->getArrObjRelProtocoloProtocoloDTO();

        $arrObjGrauSigiloDTO = InfraArray::indexarArrInfraDTO(ProtocoloRN::listarGrausSigiloso(),'StaGrau');

        $objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();
        $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_TODOS);
        $objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_TODOS);
        $objPesquisaProtocoloDTO->setDblIdProtocolo(InfraArray::converterArrInfraDTO($arrObjRelProtocoloProtocoloDTO,'IdProtocolo2'));

        $arrObjProtocoloDTO = InfraArray::indexarArrInfraDTO($objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO), 'IdProtocolo');

        $objOrgaoDTO = new OrgaoDTO();
        $objOrgaoDTO->retStrSinPublicacao();
        $objOrgaoDTO->setNumIdOrgao($objSessaoSEI->getNumIdOrgaoUnidadeAtual());

        $objOrgaoRN = new OrgaoRN();
        $objOrgaoDTO = $objOrgaoRN->consultarRN1352($objOrgaoDTO);

        $arrDocumentoIntegracao = array();

        $numTabBotao = $objPaginaSEI->getProxTabBarraComandosSuperior();

        foreach ($arrObjRelProtocoloProtocoloDTO as $objRelProtocoloProtocoloDTO) {

          if ($objRelProtocoloProtocoloDTO->getStrStaAssociacao() == RelProtocoloProtocoloRN::$TA_DOCUMENTO_ASSOCIADO) {

            $objDocumentoDTO = $objRelProtocoloProtocoloDTO->getObjProtocoloDTO2();
            $dblIdDocumento = $objDocumentoDTO->getDblIdDocumento();

            //documento excluído durante montagem da árvore
            if (!isset($arrObjProtocoloDTO[$dblIdDocumento])) {
              continue;
            }

            $objProtocoloDTODocumento = $arrObjProtocoloDTO[$dblIdDocumento];

            $strStaDocumento = $objDocumentoDTO->getStrStaDocumento();
            $numIdSerie = $objDocumentoDTO->getNumIdSerie();
            $strNomeSerie = $objDocumentoDTO->getStrNomeSerie();
            $strStaProtocoloProtocolo = $objDocumentoDTO->getStrStaProtocoloProtocolo();
            $numIdUnidadeGeradoraProtocolo = $objDocumentoDTO->getNumIdUnidadeGeradoraProtocolo();
            $numIdOrgaoUnidadeGeradoraProtocolo = $objDocumentoDTO->getNumIdOrgaoUnidadeGeradoraProtocolo();
            $strStaNivelAcessoGlobalProtocolo = $objDocumentoDTO->getStrStaNivelAcessoGlobalProtocolo();
            $strSinAssinado = $objDocumentoDTO->getStrSinAssinado();
            $strSinPublicado = $objDocumentoDTO->getStrSinPublicado();
            $strSinBloqueado = $objDocumentoDTO->getStrSinBloqueado();
            $strSinAssinadoPorOutraUnidade = $objDocumentoDTO->getStrSinAssinadoPorOutraUnidade();
            $strProtocoloDocumentoFormatado = $objDocumentoDTO->getStrProtocoloDocumentoFormatado();

            $numCodigoAcessoDocumento = $objProtocoloDTODocumento->getNumCodigoAcesso();
            $strSinAcessoAssinaturaBloco = $objProtocoloDTODocumento->getStrSinAcessoAssinaturaBloco();
            $strSinCredencialAssinatura = $objProtocoloDTODocumento->getStrSinCredencialAssinatura();
            $strSinDisponibilizadoParaOutraUnidade = $objProtocoloDTODocumento->getStrSinDisponibilizadoParaOutraUnidade();

            $objArquivamentoDTO = null;
            $strStaArquivamento = null;
            if ($strStaProtocoloProtocolo==ProtocoloRN::$TP_DOCUMENTO_RECEBIDO && $objDocumentoDTO->getObjArquivamentoDTO()!=null) {
              $objArquivamentoDTO = $objDocumentoDTO->getObjArquivamentoDTO();
              $strStaArquivamento = $objArquivamentoDTO->getStrStaArquivamento();
            }

            $strIdentificacaoDocumento = DocumentoINT::montarIdentificacaoArvore($objDocumentoDTO);

            $bolFlagCCO = false;
            if ($strStaDocumento == DocumentoRN::$TD_FORMULARIO_AUTOMATICO && $numIdSerie == $numIdSerieEmail) {
              $strTooltipDocumento = $objPaginaSEI->formatarParametrosJavaScript(DocumentoINT::montarTooltipEmail($objDocumentoDTO, $bolFlagCCO),false);
            } else {
              $strTooltipDocumento = $objPaginaSEI->formatarParametrosJavaScript($strIdentificacaoDocumento, false);
            }

            $strIdentificacaoDocumento = $objPaginaSEI->formatarParametrosJavaScript($strIdentificacaoDocumento);

            $flagAnexo = false;

            if ($strStaProtocoloProtocolo == ProtocoloRN::$TP_DOCUMENTO_RECEBIDO) {

              $arrObjAnexoDTO = $objDocumentoDTO->getObjProtocoloDTO()->getArrObjAnexoDTO();

              if (count($arrObjAnexoDTO) > 1) {
                throw new InfraException('Encontrado mais de um anexo associado ao documento.');
              }

              if (count($arrObjAnexoDTO) == 1) {

                $strIcone = DocumentoINT::selecionarIconeAnexo($arrObjAnexoDTO[0]->getStrNome());

                if ($strIcone != null) {
                  $strIcone = $objPaginaSEI->getDiretorioImagensGlobal() . '/' . $strIcone;
                } else {
                  $strIcone = 'imagens/documento.gif';
                }

                $flagAnexo = true;

              } else {
                $strIcone = 'imagens/documento.gif';
              }

            } else {

              $strIcone = 'imagens/documento.gif';
              if ($strStaDocumento == DocumentoRN::$TD_EDITOR_EDOC){
                if ($objDocumentoDTO->getDblIdDocumentoEdoc() != null) {
                  $strIcone = 'imagens/word.gif';
                }
              } else if ($strStaDocumento == DocumentoRN::$TD_EDITOR_INTERNO) {

                if ($objDocumentoDTO->getStrSinCircular()=='N') {
                  $strIcone = 'imagens/sei_documento_interno.gif';
                }else{
                  $strIcone = 'imagens/sei_documento_interno_circular.gif';
                }

              } else if ($numIdSerie == $numIdSerieEmail) {
                if (!$bolFlagCCO) {
                  $strIcone = 'imagens/email.gif';
                }else{
                  $strIcone = 'imagens/email_cco.gif';
                }
              } else{

                if ($strStaDocumento == DocumentoRN::$TD_FORMULARIO_GERADO){
                  $strIcone = 'imagens/sei_formulario1.gif';
                }else if ($strStaDocumento == DocumentoRN::$TD_FORMULARIO_AUTOMATICO) {
                  $strIcone = 'imagens/sei_formulario2.gif';
                }
              }
            }

            $strLinkDocumento = 'about:blank';

            if ($numCodigoAcessoDocumento > 0) {

              $strBuscarTarjas = '';
              if ($strStaProtocoloProtocolo == ProtocoloRN::$TP_DOCUMENTO_RECEBIDO && $strSinAssinado == 'S') {
                $strBuscarTarjas = '&buscar_tarjas=S';
              }

              $strLinkDocumento = $objSessaoSEI->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem=procedimento_visualizar&id_procedimento=' . $dblIdProcedimento . '&id_documento=' . $dblIdDocumento . $strBuscarTarjas);
            }

            if ($objDocumentoDTO->getStrStaEstadoProtocolo() == ProtocoloRN::$TE_DOCUMENTO_CANCELADO) {
              $strIcone = 'imagens/protocolo_cancelado.gif';
              $strLinkDocumento = 'about:blank';

              $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
              $objAtributoAndamentoDTO->retStrValor();
              $objAtributoAndamentoDTO->setStrIdOrigem($dblIdDocumento);
              $objAtributoAndamentoDTO->setNumIdTarefaAtividade(TarefaRN::$TI_CANCELAMENTO_DOCUMENTO);
              $objAtributoAndamentoDTO->setStrNome("MOTIVO");
              $objAtributoAndamentoDTO->setNumMaxRegistrosRetorno(1);

              $objAtributoAndamentoRN = new AtributoAndamentoRN();
              $objAtributoAndamentoDTO = $objAtributoAndamentoRN->consultarRN1366($objAtributoAndamentoDTO);

              $strNos .= "\n\n".'//CA='.$numCodigoAcessoDocumento."\n";
              $strNos .= 'Nos[' . $numNo . '] = new infraArvoreNo("DOCUMENTO",' .
                '"' . $dblIdDocumento . '",' .
                '"' . $strNoPai . '",' .
                '"' . $strLinkDocumento . '",' .
                '"ifrVisualizacao",' .
                '"' . $strIdentificacaoDocumento . '",' .
                '"'.DocumentoINT::montarTooltipAndamento('Documento Cancelado: '.$objAtributoAndamentoDTO->getStrValor()) . '",' .
                '"' . $strIcone . '",' .
                '"' . $strIcone . '",' .
                '"' . $strIcone . '",' .
                'true,' .
                (($strLinkDocumento != 'about:blank') ? 'true,' : 'false,') .
                'null,' .
                'null,' .
                'null,'.
                '"'.$strProtocoloDocumentoFormatado.'");' . "\n";
            } else {
              $strNos .= "\n\n".'//CA='.$numCodigoAcessoDocumento."\n";
              $strNos .= 'Nos[' . $numNo . '] = new infraArvoreNo("DOCUMENTO",' .
                '"' . $dblIdDocumento . '",' .
                '"' . $strNoPai . '",' .
                '"' . $strLinkDocumento . '",' .
                '"ifrVisualizacao",' .
                '"' . $strIdentificacaoDocumento . '",' .
                '"' . $strTooltipDocumento . '",' .
                '"' . $strIcone . '",' .
                '"' . $strIcone . '",' .
                '"' . $strIcone . '",' .
                'true,' .
                (($strLinkDocumento != 'about:blank') ? 'true,' : 'false,') .
                'null,' .
                'null,' .
                'null,'.
                '"'.$strProtocoloDocumentoFormatado.'");' . "\n";
            }

            if ($strSinCredencialAssinatura == 'S') {
              $strNosAcao .= 'NosAcoes[' . $numNoAcao++ . '] = new infraArvoreAcao("PARA_ASSINATURA",' .
                '"PA' . $dblIdDocumento . '",' .
                '"' . $dblIdDocumento . '",' .
                '"javascript:alert(\'Documento com Credencial de Assinatura\');",' .
                'null,' .
                '"Documento com Credencial de Assinatura",' .
                '"imagens/sei_credencial_assinatura.gif",' .
                'true);' . "\n";
            }

            if ($objDocumentoDTO->getStrStaNivelAcessoLocalProtocolo() != ProtocoloRN::$NA_PUBLICO) {
              $strNosAcao .= ProtocoloINT::montarNoAcaoAcesso($dblIdDocumento, $numNoAcao++, $objDocumentoDTO->getStrStaNivelAcessoLocalProtocolo(), $objDocumentoDTO->getStrStaGrauSigiloProtocolo(), $objDocumentoDTO->getStrNomeHipoteseLegal(), $objDocumentoDTO->getStrBaseLegalHipoteseLegal(), $arrObjGrauSigiloDTO);
            }

            if ($objProtocoloDTODocumento->getArrAcessoModulos() != null){
              $strNosAcao .= ProtocoloINT::montarNoAcaoAcessoModulos($dblIdDocumento,$numNoAcao++,$objProtocoloDTODocumento->getArrAcessoModulos());
            }

            if ($bolAcessoRestritoOuvidoria && $strStaDocumento==DocumentoRN::$TD_FORMULARIO_AUTOMATICO && $numIdSerie==$numIdSerieOuvidoria){
                $strNosAcao .= 'NosAcoes[' . $numNoAcao++ . '] = new infraArvoreAcao("ACESSO_OUVIDORIA",' .
                  '"AO' . $dblIdDocumento . '",' .
                  '"' . $dblIdDocumento . '",' .
                  '"javascript:alert(\'Somente para Ouvidoria\');",' .
                  'null,' .
                  '"Somente para Ouvidoria",' .
                  '"imagens/sei_acesso_restrito.png",' .
                  'true);' . "\n";
            }

            if ($strSinAssinado == 'S') {
              $strTextoAssinatura = DocumentoINT::montarTooltipAssinatura($objDocumentoDTO);

              if ($strSinBloqueado == 'N' && ($numIdUnidadeGeradoraProtocolo == $numIdUnidadeAtual || $strSinAcessoAssinaturaBloco == 'S')) {
                $strImagemAssinatura = ($strStaDocumento==DocumentoRN::$TD_EXTERNO) ? 'imagens/sei_autenticar_pequeno_nao_bloqueado.gif' : 'imagens/sei_assinar_pequeno_nao_bloqueado.gif';
              } else {
                $strImagemAssinatura = ($strStaDocumento==DocumentoRN::$TD_EXTERNO) ? 'imagens/sei_autenticar_pequeno.gif' : 'imagens/sei_assinar_pequeno.gif';
              }

              $strNosAcao .= 'NosAcoes[' . $numNoAcao++ . '] = new infraArvoreAcao("ASSINATURA",' .
                '"A' . $dblIdDocumento . '",' .
                '"' . $dblIdDocumento . '",' .
                '"javascript:alert(\'' . $objPaginaSEI->formatarParametrosJavaScript(str_replace("\n",'\\\n',$strTextoAssinatura)) . '\');",' .
                'null,' .
                '"' . str_replace("\n",'\n',$strTextoAssinatura) . '",' .
                '"' . $strImagemAssinatura . '",' .
                'true);' . "\n";
            }

            if ($strSinPublicado == 'S') {

              $strTextoPublicacao = PublicacaoINT::obterTextoInformativoPublicacao($objDocumentoDTO);
              $strNosAcao .= 'NosAcoes[' . $numNoAcao++ . '] = new infraArvoreAcao("PUBLICACAO",' .
                '"P' . $dblIdDocumento . '",' .
                '"' . $dblIdDocumento . '",' .
                '"javascript:alert(\'' . $objPaginaSEI->formatarParametrosJavaScript(str_replace("\n",'\\\n',$strTextoPublicacao)) . '\');",' .
                'null,' .
                '"' . str_replace("\n", '\n', $strTextoPublicacao) . '",' .
                '"imagens/sei_publicacao_pequeno.gif",' .
                'true);' . "\n";
            }

            if ($numCodigoAcessoDocumento > 0) {
              if ($objRelProtocoloProtocoloDTO->getStrSinCiencia() == 'S') {

                $strNosAcao .= 'NosAcoes[' . $numNoAcao++ . '] = new infraArvoreAcao("CIENCIAS",' .
                  '"CD' . $dblIdDocumento . '",' .
                  '"' . $dblIdDocumento . '",' .
                  '"' . $objSessaoSEI->assinarLink('controlador.php?acao=protocolo_ciencia_listar&acao_origem=procedimento_visualizar&id_procedimento=' . $dblIdProcedimento . '&id_documento=' . $dblIdDocumento . '&arvore=1') . '",' .
                  '"ifrVisualizacao",' .
                  '"Visualizar Ciências no Documento",' .
                  '"imagens/sei_ciencia_pequeno.gif",' .
                  'true);' . "\n";
              }
            }

            if ($bolAcaoLocalizadorListar &&
                $bolFlagArquivo &&
                $strStaProtocoloProtocolo == ProtocoloRN::$TP_DOCUMENTO_RECEBIDO &&
                $objArquivamentoDTO!=null &&
                $objArquivamentoDTO->getNumIdUnidadeLocalizador() == $numIdUnidadeAtual) {

              $strTooltipLocalizador = 'Localizador '.LocalizadorINT::montarIdentificacaoRI1132($objArquivamentoDTO->getStrSiglaTipoLocalizador(),$objArquivamentoDTO->getNumSeqLocalizadorLocalizador()) . ' ('.PaginaSEI::tratarHTML($arrObjArquivamentoProtocoloDTO[$objArquivamentoDTO->getStrStaArquivamento()]->getStrDescricao()).')';

              $strNosAcao .= 'NosAcoes[' . $numNoAcao++ . '] = new infraArvoreAcao("LOCALIZADOR",' .
                  '"LD' . $dblIdDocumento . '",' .
                  '"' . $dblIdDocumento . '",' .
                  '"javascript:alert(\'' . $objPaginaSEI->formatarParametrosJavaScript($strTooltipLocalizador,false) . '\');",' .
                  '"ifrVisualizacao",' .
                  '"'.$strTooltipLocalizador.'",' .
                  '"imagens/arquivo.png",' .
                  'true);' . "\n";
            }

            $strAcoesDocumento = '';
            $strSrc = '';
            $strHtml = '';

            //não monta ações e links por segurança
            if ($strLinkDocumento != 'about:blank') {

              if ($strStaProtocoloProtocolo == ProtocoloRN::$TP_DOCUMENTO_GERADO) {

                if ($bolAcaoAlterarDocumento && !$bolFlagBloqueado &&
                    (($bolFlagAberto || $bolFlagAbertoAnexado || ($bolFlagProtocolo && $numIdUnidadeGeradoraProtocolo == $numIdUnidadeAtual)) ||
                        (($strSinAcessoAssinaturaBloco == 'S' || $strSinCredencialAssinatura == 'S') && $strSinAssinadoPorOutraUnidade == 'N')) &&
                    $strSinPublicado == 'N'
                ) {
                  $strAcoesDocumento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=documento_alterar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&id_documento=' . $dblIdDocumento . '&arvore=1') . '" tabindex="' . $numTabBotao . '" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_consultar_alterar_protocolo.gif" alt="Consultar/Alterar Documento" title="Consultar/Alterar Documento"/></a>';
                } else if ($bolAcaoConsultarDocumento) {
                  $strAcoesDocumento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=documento_consultar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&id_documento=' . $dblIdDocumento . '&arvore=1') . '" tabindex="' . $numTabBotao . '" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_consultar_alterar_protocolo.gif" alt="Consultar Documento" title="Consultar Documento" /></a>';
                }
              }

              if ($strStaProtocoloProtocolo == ProtocoloRN::$TP_DOCUMENTO_RECEBIDO) {

                if ($bolAcaoAlterarDocumentoRecebido && !$bolFlagBloqueado && ($bolFlagAberto || $bolFlagAbertoAnexado || ($bolFlagProtocolo && $numIdUnidadeGeradoraProtocolo == $numIdUnidadeAtual))) {
                  $strAcoesDocumento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=documento_alterar_recebido&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&id_documento=' . $dblIdDocumento . '&arvore=1') . '" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_consultar_alterar_protocolo.gif" alt="Consultar/Alterar Documento Externo" title="Consultar/Alterar Documento Externo" /></a>';
                } else if ($bolAcaoConsultarDocumentoRecebido) {
                  $strAcoesDocumento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=documento_consultar_recebido&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&id_documento=' . $dblIdDocumento . '&arvore=1') . '" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_consultar_alterar_protocolo.gif" alt="Consultar Documento Externo" title="Consultar Documento Externo" /></a>';
                }
              }

              if ($bolAcaoAcompanhamentoCadastrar && !$bolFlagAnexado && $strStaNivelAcessoGlobalProtocolo!=ProtocoloRN::$NA_SIGILOSO){
                $strAcoesDocumento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=acompanhamento_cadastrar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_acompanhamento_especial.gif" alt="Acompanhamento Especial" title="Acompanhamento Especial"/></a>';
              }

              if ($bolFlagAberto && $bolAcaoDocumentoCiencia && ($strStaDocumento == DocumentoRN::$TD_EXTERNO || $strStaDocumento == DocumentoRN::$TD_FORMULARIO_AUTOMATICO || $strSinAssinado == 'S')) {
                $strAcoesDocumento .= '<a href="#" onclick="cienciaDocumento();" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_ciencia.gif" alt="Ciência" title="Ciência" /></a>';
              }

              if ($bolFlagAberto && !$bolFlagBloqueado && $bolAcaoProcedimentoEnviar && $strStaNivelAcessoGlobalProtocolo!=ProtocoloRN::$NA_SIGILOSO){
                $strAcoesDocumento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_enviar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.(($strSinAssinado=='S' && $strSinBloqueado=='N')?'&id_documento_assinado='.$dblIdDocumento:'').'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_enviar_processo.gif" alt="Enviar Processo" title="Enviar Processo" /></a>';
              }

              if ($bolFlagAberto && $bolAcaoDefinirAtividade) {
                $strAcoesDocumento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=procedimento_atualizar_andamento&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&arvore=1') . '" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_atualizar_andamento.gif" alt="Atualizar Andamento" title="Atualizar Andamento" /></a>';
              }


              if ($strStaProtocoloProtocolo == ProtocoloRN::$TP_DOCUMENTO_GERADO) {

                if ($bolAcaoAlterarDocumento && !$bolFlagBloqueado && $strStaDocumento==DocumentoRN::$TD_EDITOR_INTERNO &&
                    $strSinBloqueado == 'N' &&
                  (($bolFlagAberto && $numIdUnidadeAtual == $numIdUnidadeGeradoraProtocolo && $strSinDisponibilizadoParaOutraUnidade == 'N') ||
                    (($strSinAcessoAssinaturaBloco == 'S' || $strSinCredencialAssinatura == 'S') && $strSinAssinadoPorOutraUnidade == 'N')) &&
                    $strSinPublicado == 'N') {
                  $strAcoesDocumento .= '<a href="#" onclick="editarConteudo(\\\'' . $strSinAssinado . '\\\');" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_editar_conteudo.gif" alt="Editar Conteúdo" title="Editar Conteúdo" /></a>';
                }

                if ($strStaDocumento == DocumentoRN::$TD_FORMULARIO_GERADO) {
                  if ($bolAcaoAlterarFormulario && !$bolFlagBloqueado && $strSinBloqueado == 'N' &&
                      (($bolFlagAberto && $numIdUnidadeAtual == $numIdUnidadeGeradoraProtocolo && $strSinDisponibilizadoParaOutraUnidade == 'N') ||
                          (($strSinAcessoAssinaturaBloco == 'S' || $strSinCredencialAssinatura == 'S') && $strSinAssinadoPorOutraUnidade == 'N')) &&
                      $strSinPublicado == 'N') {
                    $strAcoesDocumento .= '<a href="#" onclick="alterarFormulario(\\\'' . $strSinAssinado . '\\\');" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_formulario.gif" alt="Alterar Formulário" title="Alterar Formulário" /></a>';
                  }
                }

                if (($bolFlagAberto || $bolFlagAnexado) && !$bolFlagBloqueado && $bolAcaoDocumentoEnviarEmail && ($strSinAssinado == 'S' || $strSinPublicado == 'S')) {
                  $strAcoesDocumento .= '<a href="#" onclick="enviarEmailDocumento();" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_email.gif" alt="Enviar Documento por Correio Eletrônico" title="Enviar Documento por Correio Eletrônico"/></a>';
                }

                if ($bolFlagAberto && !$bolFlagBloqueado && $strStaDocumento == DocumentoRN::$TD_FORMULARIO_AUTOMATICO) {
                  if ($bolAcaoResponderFormularioOuvidoria && $numIdSerie == $numIdSerieOuvidoria) {
                    $strAcoesDocumento .= '<a href="#" onclick="abrirJanela(\\\'janelaEmailOuvidoria_'.SessaoSEI::getInstance()->getNumIdUsuario().'_'.$dblIdDocumento.'\\\',\\\''.$objSessaoSEI->assinarLink('controlador.php?acao=responder_formulario&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&id_documento='.$dblIdDocumento.'&arvore=1').'\\\')" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_email_responder.gif" alt="Responder Formulário" title="Responder Formulário"/></a>';
                  }

                  if ($bolAcaoEmailEncaminhar && $numIdSerie == $numIdSerieEmail) {
                    $strAcoesDocumento .= '<a href="#" onclick="encaminharEmail();" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_email_encaminhar.gif" alt="Encaminhar / Reenviar Correspondência Eletrônica" title="Encaminhar / Reenviar Correspondência Eletrônica"/></a>';
                  }

                }

                if ($bolFlagAberto && $bolAcaoListarPublicacoes && $numIdUnidadeGeradoraProtocolo == $numIdUnidadeAtual && ($objDocumentoDTO->getStrSinPublicacaoAgendada() == 'S' || $strSinPublicado == 'S')) {
                  $strAcoesDocumento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=publicacao_listar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&id_documento=' . $dblIdDocumento . '&arvore=1') . '" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/publicacoes.gif" alt="Visualizar Publicações/Agendamentos" title="Visualizar Publicações/Agendamentos" /></a>';
                }

                if ($bolFlagAberto && !$bolFlagBloqueado && $bolAcaoAgendarPublicacao && $numIdUnidadeGeradoraProtocolo == $numIdUnidadeAtual && $objOrgaoDTO->getStrSinPublicacao() == 'S' && $objDocumentoDTO->getStrSinPublicavel() == 'S') {
                  $strAcoesDocumento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=publicacao_agendar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&id_documento=' . $dblIdDocumento . '&arvore=1') . '" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_publicar_protocolo.gif" alt="Agendar Publicação" title="Agendar Publicação"/></a>';
                }

                if ($bolFlagAberto && !$bolFlagBloqueado && $bolAcaoGerarPublicacaoRelacionada && $numIdUnidadeGeradoraProtocolo == $numIdUnidadeAtual && $strSinPublicado == 'S') {
                  $strAcoesDocumento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=publicacao_gerar_relacionada&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&id_documento=' . $dblIdDocumento . '&arvore=1') . '" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_publicacao_relacionada.gif" alt="Gerar Publicação Relacionada" title="Gerar Publicação Relacionada"/></a>';
                }

                if ($bolFlagAberto &&
                    !$bolFlagBloqueado &&
                    $bolAcaoCredencialAssinaturaGerenciar &&
                    $strStaNivelAcessoGlobalProtocolo == ProtocoloRN::$NA_SIGILOSO &&
                    ($strStaDocumento == DocumentoRN::$TD_EDITOR_INTERNO || $strStaDocumento == DocumentoRN::$TD_FORMULARIO_GERADO) &&
                    $numIdUnidadeGeradoraProtocolo == $numIdUnidadeAtual &&
                    $strSinPublicado == 'N'
                ) {
                  $strAcoesDocumento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=credencial_assinatura_gerenciar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&id_documento=' . $dblIdDocumento . '&arvore=1') . '" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_conceder_credencial_assinatura.gif" alt="Gerenciar Credenciais de Assinatura" title="Gerenciar Credenciais de Assinatura" /></a>';
                }


                if ($bolAcaoAssinarDocumento &&
                     !$bolFlagBloqueado &&
                    ($strStaDocumento==DocumentoRN::$TD_EDITOR_INTERNO || $strStaDocumento==DocumentoRN::$TD_FORMULARIO_GERADO) &&
                    (($bolFlagAberto && $numIdUnidadeGeradoraProtocolo == $numIdUnidadeAtual && $strSinDisponibilizadoParaOutraUnidade == 'N')
                      || $strSinAcessoAssinaturaBloco == 'S' || $strSinCredencialAssinatura == 'S') &&
                      $strSinPublicado == 'N') {
                  $strAcoesDocumento .= '<a href="#" onclick="assinarDocumento();" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_assinar.gif" alt="Assinar Documento" title="Assinar Documento"/></a>';
                }

                if ($bolAcaoAssinaturaExternaGerenciar && $bolFlagAberto && !$bolFlagBloqueado &&
                    ($strStaDocumento==DocumentoRN::$TD_EDITOR_INTERNO || $strStaDocumento==DocumentoRN::$TD_FORMULARIO_GERADO) &&
                    $numIdUnidadeGeradoraProtocolo == $numIdUnidadeAtual &&
                    $strSinPublicado == 'N'
                ) {
                  $strAcoesDocumento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=assinatura_externa_gerenciar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&id_documento=' . $dblIdDocumento . '&arvore=1') . '" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_gerenciar_assinatura_externa.gif" alt="Gerenciar Liberações para Assinatura Externa" title="Gerenciar Liberações para Assinatura Externa" /></a>';
                }

              }

              if ($strStaProtocoloProtocolo == ProtocoloRN::$TP_DOCUMENTO_RECEBIDO) {

                if ($bolAcaoAssinarDocumento && !$bolFlagBloqueado &&
                    ($bolFlagAberto || $bolFlagProtocolo) &&
                    $numIdUnidadeGeradoraProtocolo == $numIdUnidadeAtual &&
                    $objDocumentoDTO->getNumIdTipoConferencia() != null &&
                    (($bolHabilitarAutenticacaoDocumentoExterno=='1' && $bolFlagProtocolo) || $bolHabilitarAutenticacaoDocumentoExterno=='2')) {
                  $strAcoesDocumento .= '<a href="#" onclick="assinarDocumento();" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_autenticar.gif" alt="Autenticar Documento" title="Autenticar Documento"/></a>';
                }

                if (($bolFlagAberto || $bolFlagAnexado) && !$bolFlagBloqueado && $bolAcaoDocumentoEnviarEmail) {
                  $strAcoesDocumento .= '<a href="#" onclick="enviarEmailDocumento();" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_email.gif" alt="Enviar Documento por Correio Eletrônico" title="Enviar Documento por Correio Eletrônico"/></a>';
                }

                if (($bolFlagAberto || $bolFlagProtocolo) && !$bolFlagBloqueado &&
                    $bolAcaoDocumentoMover &&
                    //$numIdUnidadeGeradoraProtocolo == $numIdUnidadeAtual &&
                    ((($bolHabilitarMoverDocumento=='1' || $bolHabilitarMoverDocumento=='4') && $bolFlagProtocolo) || $bolHabilitarMoverDocumento=='2' || (($bolHabilitarMoverDocumento=='3' || $bolHabilitarMoverDocumento=='4') && $objProtocoloDTODocumento->getStrSinUnidadeGeradoraProtocolo()=='S'))){
                  $strAcoesDocumento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=documento_mover&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&id_documento=' . $dblIdDocumento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_mover_documento.gif" alt="Mover Documento para outro Processo" title="Mover Documento para outro Processo" /></a>';
                }
              }

              if ($bolFlagAberto &&
                  !$bolFlagBloqueado &&
                  $bolAcaoEscolherBloco &&
                  $strStaNivelAcessoGlobalProtocolo != ProtocoloRN::$NA_SIGILOSO &&
                  $numIdUnidadeGeradoraProtocolo == $numIdUnidadeAtual &&
                  ($strStaDocumento == DocumentoRN::$TD_EDITOR_INTERNO || $strStaDocumento == DocumentoRN::$TD_FORMULARIO_GERADO) &&
                  $strSinPublicado == 'N'
              ) {
                $strAcoesDocumento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=bloco_escolher&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&id_documento=' . $dblIdDocumento . '&arvore=1') . '" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_incluir_em_bloco.gif"  alt="Incluir em Bloco de Assinatura" title="Incluir em Bloco de Assinatura"/></a>';
              }

              if ($bolAcaoDocumentoCancelar && !$bolFlagBloqueado &&
                  ($bolFlagAberto || $bolFlagAnexado || ($bolFlagProtocolo && $strStaProtocoloProtocolo == ProtocoloRN::$TP_DOCUMENTO_RECEBIDO)) &&
                  $numIdUnidadeGeradoraProtocolo == $numIdUnidadeAtual &&
                  $strStaDocumento != DocumentoRN::$TD_FORMULARIO_AUTOMATICO &&
                  $strStaArquivamento != ArquivamentoRN::$TA_ARQUIVADO &&
                  $strStaArquivamento != ArquivamentoRN::$TA_SOLICITADO_DESARQUIVAMENTO &&
                  $strSinPublicado == 'N'
              ) {
                $strAcoesDocumento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=documento_cancelar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&id_documento=' . $dblIdDocumento . '&arvore=1') . '" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_cancelar_protocolo.gif" alt="Cancelar Documento" title="Cancelar Documento"/></a>';
              }

              if ($bolAcaoProtocoloModeloCadastrar &&
                  $strStaDocumento == DocumentoRN::$TD_EDITOR_INTERNO &&
                  $strStaNivelAcessoGlobalProtocolo != ProtocoloRN::$NA_SIGILOSO) {
                $strAcoesDocumento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=protocolo_modelo_cadastrar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_protocolo=' . $dblIdDocumento . '&arvore=1') . '" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_documento_modelo.gif" alt="Adicionar aos Modelos Favoritos" title="Adicionar aos Modelos Favoritos"/></a>';
              }

              if ($bolAcaoProtocoloSolicitarDesarquivamento &&
                  $strStaArquivamento == ArquivamentoRN::$TA_ARQUIVADO &&
                  $strStaProtocoloProtocolo == ProtocoloRN::$TP_DOCUMENTO_RECEBIDO
              ) {
                $strAcoesDocumento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=arquivamento_solicitar_desarquivamento&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&id_documento=' . $dblIdDocumento . '&arvore=1') . '" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_solicitar_desarquivamento.gif" alt="Solicitar Desarquivamento" title="Solicitar Desarquivamento"/></a>';
              }


              if ($bolAcaoDocumentoVersaoListar &&
                ((($bolFlagAberto || $bolFlagAnexado) && $numIdUnidadeAtual == $numIdUnidadeGeradoraProtocolo) ||
                  (($strSinAcessoAssinaturaBloco == 'S' || $strSinCredencialAssinatura == 'S') && $strSinAssinadoPorOutraUnidade == 'N')) &&
                  $strSinPublicado == 'N' &&
                  $strStaDocumento == DocumentoRN::$TD_EDITOR_INTERNO
              ) {
                $strAcoesDocumento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=documento_versao_listar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&id_documento=' . $dblIdDocumento . '&arvore=1') . '" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_documento_versoes.gif" alt="Versões do Documento" title="Versões do Documento"/></a>';
              }

              if ($bolAcaoDocumentoGerarCircular && !$bolFlagBloqueado &&
                  $bolFlagAberto && $numIdUnidadeAtual == $numIdUnidadeGeradoraProtocolo &&
                  $objDocumentoDTO->getStrSinDestinatarioSerie()=='S' &&
                  $strStaDocumento == DocumentoRN::$TD_EDITOR_INTERNO) {
                $strAcoesDocumento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=documento_gerar_circular&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&id_documento=' . $dblIdDocumento . '&arvore=1') . '" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_documento_gerar_circular.gif" alt="Gerar Circular" title="Gerar Circular"/></a>';
              }

              if ($bolAcaoImprimirDocumentoWeb && $strStaProtocoloProtocolo == ProtocoloRN::$TP_DOCUMENTO_GERADO) {
                $strAcoesDocumento .= '<a href="#" onclick="window.open(\\\'' . $objSessaoSEI->assinarLink('controlador.php?acao=documento_imprimir_web&acao_origem=arvore_visualizar&id_documento=' . $dblIdDocumento) . '\\\');" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_imprimir_web.gif" alt="Imprimir Web" title="Imprimir Web" /></a>';
              }

              if ($bolAcaoExcluirDocumento && !$bolFlagBloqueado &&
                  ($bolFlagAberto || $bolFlagProtocolo) &&
                  $strSinBloqueado == 'N' &&
                  $strStaDocumento != DocumentoRN::$TD_FORMULARIO_AUTOMATICO &&
                  $numIdUnidadeGeradoraProtocolo == $numIdUnidadeAtual &&
                  $strSinPublicado == 'N' &&
                  $objDocumentoDTO->getStrSinPublicacaoAgendada() == 'N' &&
                  $strStaArquivamento == null
              ) {
                $strAcoesDocumento .= '<a href="#" onclick="excluirDocumento();" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_lixeira.png" alt="Excluir" title="Excluir" /></a>';
              }

              if ($bolAcaoAssinaturaVerificar && $strSinAssinado == 'S') {
                $strAcoesDocumento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=assinatura_verificar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&id_documento=' . $dblIdDocumento . '&arvore=1') . '" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_assinaturas.gif" alt="Consultar Assinaturas" title="Consultar Assinaturas" /></a>';
              }

              if (!$bolFlagAberto && $bolAcaoReabrirProcedimento && $bolFlagTramitacao && !$bolFlagSobrestado && !$bolFlagAnexado){
                $strAcoesDocumento .= '<a href="#" onclick="reabrirProcesso();" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema"  tabindex="'.$numTabBotao.'" src="imagens/sei_reabrir_processo.gif" alt="Reabrir Processo" title="Reabrir Processo" />';
              }

              if ($bolFlagAberto && $bolAcaoConcluirProcedimento){
                $strAcoesDocumento .= '<a href="#" onclick="concluirProcesso();" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema"  tabindex="'.$numTabBotao.'" src="imagens/sei_concluir_processo.gif" alt="Concluir Processo" title="Concluir Processo" />';
              }

              if (!$flagAnexo) {
                $strSrc = $objSessaoSEI->assinarLink('controlador.php?acao=documento_visualizar&acao_origem=procedimento_visualizar&id_documento=' . $dblIdDocumento . '&arvore=1');
              } else if ($bolAcaoDownload) {
                $arrExtensaoAnexo = explode('.', $arrObjAnexoDTO[0]->getStrNome());

                $strExtensaoAnexo = null;

                if (count($arrExtensaoAnexo) > 1) {
                  $strExtensaoAnexo = strtolower($arrExtensaoAnexo[count($arrExtensaoAnexo) - 1]);
                }

                if ($strSinAssinado == 'S') {
                  $strNos .= 'Nos[' . $numNo . '].assinatura = \'<button type="button" id="btnVisualizarAssinaturas" onclick="visualizarAssinaturas();" class="infraButton" value="Visualizar Autenticações">Visualizar Autenticações</button>\';' . "\n";
                } else {
                  $strNos .= 'Nos[' . $numNo . '].assinatura = \'\';' . "\n";
                }

                $strTagDestino = 'target="_blank"';
                /*
                if ($objPaginaSEI->isBolAndroid()){
                  $strTagDestino = 'download="'.InfraUtil::formatarNomeArquivo($arrObjAnexoDTO[0]->getStrNome()).'"';
                }
                */

                if (isset($arrExtensoes[$strExtensaoAnexo])) {
                  if ($numTamDocExternoLink==null || $arrObjAnexoDTO[0]->getNumTamanho() <= $numTamDocExternoLink){
                    $strSrc = $objSessaoSEI->assinarLink('controlador.php?acao=documento_download_anexo&acao_origem=procedimento_visualizar&id_anexo=' . $arrObjAnexoDTO[0]->getNumIdAnexo() . '&arvore=1');
                    $strHtml = 'Clique <a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=documento_download_anexo&acao_origem=procedimento_visualizar&id_anexo=' . $arrObjAnexoDTO[0]->getNumIdAnexo()) . '" '.$strTagDestino.' class="ancoraArvoreDownload">aqui</a> para visualizar o conteúdo deste documento em uma nova janela.';
                  }else{
                    $strHtml = 'Documento possui '.InfraUtil::formatarTamanhoBytes($arrObjAnexoDTO[0]->getNumTamanho()).' e sua visualização pode levar alguns instantes.<br /><br />Clique <a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=documento_download_anexo&acao_origem=procedimento_visualizar&id_anexo=' . $arrObjAnexoDTO[0]->getNumIdAnexo()) . '" '.$strTagDestino.' class="ancoraArvoreDownload">aqui</a> para continuar.';
                  }

                } else {
                  $strHtml = 'Clique <a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=documento_download_anexo&acao_origem=procedimento_visualizar&id_anexo=' . $arrObjAnexoDTO[0]->getNumIdAnexo().'&download=1') . '" '.$strTagDestino.' class="ancoraArvoreDownload">aqui</a> para visualizar o conteúdo deste documento ('.InfraUtil::formatarTamanhoBytes($arrObjAnexoDTO[0]->getNumTamanho()).').';
                }
              }
            }

            $strNos .= 'Nos[' . $numNo . '].acoes = \'' . $strAcoesDocumento . '\';' . "\n";
            $strNos .= 'Nos[' . $numNo . '].src = \'' . $strSrc . '\';' . "\n";
            $strNos .= 'Nos[' . $numNo . '].html = \'' . $strHtml . '\';' . "\n";

            if (count($SEI_MODULOS)) {
              $objDocumentoAPI = new DocumentoAPI();
              $objDocumentoAPI->setIdDocumento($dblIdDocumento);
              $objDocumentoAPI->setIdSerie($numIdSerie);
              $objDocumentoAPI->setNomeSerie($strNomeSerie);
              $objDocumentoAPI->setIdUnidadeGeradora($numIdUnidadeGeradoraProtocolo);
              $objDocumentoAPI->setIdOrgaoUnidadeGeradora($numIdOrgaoUnidadeGeradoraProtocolo);
              $objDocumentoAPI->setTipo($strStaProtocoloProtocolo);
              $objDocumentoAPI->setSinAssinado($strSinAssinado);
              $objDocumentoAPI->setSinPublicado($strSinPublicado);
              $objDocumentoAPI->setSinBloqueado($strSinBloqueado);
              $objDocumentoAPI->setCodigoAcesso($numCodigoAcessoDocumento);
              $objDocumentoAPI->setSubTipo($strStaDocumento);
              $objDocumentoAPI->setNumeroProtocolo($strProtocoloDocumentoFormatado);

              $arrDocumentoIntegracao[$dblIdDocumento] = array();
              $arrDocumentoIntegracao[$dblIdDocumento][0] = $numNo;
              $arrDocumentoIntegracao[$dblIdDocumento][1] = $objDocumentoAPI;
            }

            $numNo++;

          }else if ($objRelProtocoloProtocoloDTO->getStrStaAssociacao() == RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO) {

            $objProcedimentoDTOAnexado = $objRelProtocoloProtocoloDTO->getObjProtocoloDTO2();

            $dblIdProcedimentoAnexado = $objProcedimentoDTOAnexado->getDblIdProcedimento();
            $strIdentificacaoProcedimentoAnexado = $objProcedimentoDTOAnexado->getStrProtocoloProcedimentoFormatado();
            $strTooltipProcedimentoAnexado = $objProcedimentoDTOAnexado->getStrNomeTipoProcedimento();
            $strIcone = 'imagens/procedimento_anexado.gif';

            $strLinkProcedimentoAnexado = 'about:blank';
            $strSrc = '';
            $strHtml = '';
            if ($arrObjProtocoloDTO[$dblIdProcedimentoAnexado]->getNumCodigoAcesso() > 0 || $bolFlagProtocolo) {
              $strLinkProcedimentoAnexado = $objSessaoSEI->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem=procedimento_visualizar&id_procedimento='.$dblIdProcedimento.'&id_procedimento_anexado='.$dblIdProcedimentoAnexado);
              $strHtml = '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=arvore_visualizar&id_procedimento=' . $dblIdProcedimentoAnexado) . '" target="_blank">Clique aqui para visualizar este processo em uma nova janela.</a>';
            }

            $strNos .= "\n\n".'//CA='.$arrObjProtocoloDTO[$dblIdProcedimentoAnexado]->getNumCodigoAcesso()."\n";
            $strNos .= 'Nos[' . $numNo . '] = new infraArvoreNo("PROCEDIMENTO_ANEXADO",' .
                '"' . $dblIdProcedimentoAnexado . '",' .
                '"' . $strNoPai . '",' .
                '"' . $strLinkProcedimentoAnexado . '",' .
                '"ifrVisualizacao",' .
                '"' . $strIdentificacaoProcedimentoAnexado . '",' .
                '"' . $strTooltipProcedimentoAnexado . '",' .
                '"' . $strIcone . '",' .
                '"' . $strIcone . '",' .
                '"' . $strIcone . '",' .
                'true,' .
                (($strLinkProcedimentoAnexado != 'about:blank') ? 'true,' : 'false,') .
                'null,' .
                'null,' .
                'null,'.
                '"'.$strIdentificacaoProcedimentoAnexado.'");' . "\n";

            if ($objProcedimentoDTOAnexado->getStrStaNivelAcessoOriginalProtocolo() != ProtocoloRN::$NA_PUBLICO) {
              $strNosAcao .= ProtocoloINT::montarNoAcaoAcesso($dblIdProcedimentoAnexado, $numNoAcao++, $objProcedimentoDTOAnexado->getStrStaNivelAcessoOriginalProtocolo(), $objProcedimentoDTOAnexado->getStrStaGrauSigiloProtocolo(), $objProcedimentoDTOAnexado->getStrNomeHipoteseLegal(), $objProcedimentoDTOAnexado->getStrBaseLegalHipoteseLegal(), $arrObjGrauSigiloDTO);
            }

            if ($arrObjProtocoloDTO[$dblIdProcedimentoAnexado]->getNumCodigoAcesso() > 0 && $objRelProtocoloProtocoloDTO->getStrSinCiencia() == 'S') {
              $strNosAcao .= 'NosAcoes[' . $numNoAcao++ . '] = new infraArvoreAcao("CIENCIAS",' .
                  '"CP' . $dblIdProcedimentoAnexado . '",' .
                  '"' . $dblIdProcedimentoAnexado . '",' .
                  '"' . $objSessaoSEI->assinarLink('controlador.php?acao=protocolo_ciencia_listar&acao_origem=procedimento_visualizar&id_procedimento=' . $dblIdProcedimento.'&id_procedimento_anexado='.$dblIdProcedimentoAnexado.'&arvore=1') . '",' .
                  '"ifrVisualizacao",' .
                  '"Visualizar Ciências no Processo Anexado",' .
                  '"imagens/sei_ciencia_pequeno.gif",' .
                  'true);' . "\n";
            }

            $strAcoesProcedimento = '';

            if ($bolAcaoAlterarProcedimento && !$bolFlagBloqueado && ($bolFlagAberto || $bolFlagAbertoAnexado || ($bolFlagProtocolo && $objProcedimentoDTO->getNumIdUnidadeGeradoraProtocolo() == $numIdUnidadeAtual))) {
              $strAcoesProcedimento .= '<a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_alterar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimentoAnexado.'&id_procedimento_retorno='.$dblIdProcedimento.'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_consultar_alterar_protocolo.gif" alt="Consultar/Alterar Processo Anexado" title="Consultar/Alterar Processo Anexado"/></a>';
            } else if ($bolAcaoConsultarProcedimento) {
              $strAcoesProcedimento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=procedimento_consultar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimentoAnexado . '&arvore=1') . '" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_consultar_alterar_protocolo.gif" alt="Consultar Processo Anexado" title="Consultar Processo Anexado"/></a>';
            }

            if ($bolFlagAberto && $bolAcaoProcedimentoAnexadoCiencia) {
              $strAcoesProcedimento .= '<a href="#" onclick="cienciaProcessoAnexado();" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_ciencia.gif" alt="Ciência" title="Ciência" /></a>';
            }

            if ($bolFlagAberto && !$bolFlagBloqueado && $bolAcaoProcedimentoDesanexar) {
              $strAcoesProcedimento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=procedimento_desanexar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&id_procedimento_anexado=' . $dblIdProcedimentoAnexado . '&arvore=1') . '" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema" src="imagens/sei_desanexar_processo.gif" alt="Desanexar Processo" title="Desanexar Processo"/></a>';
            }


            $strNos .= 'Nos[' . $numNo . '].acoes = \'' . $strAcoesProcedimento . '\';' . "\n";
            $strNos .= 'Nos[' . $numNo . '].src = \'' . $strSrc . '\';' . "\n";
            $strNos .= 'Nos[' . $numNo . '].html = \'' . $strHtml . '\';' . "\n";
            $numNo++;

          } else if ($objRelProtocoloProtocoloDTO->getStrStaAssociacao() == RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_DESANEXADO) {

            $objProcedimentoDTODesanexado = $objRelProtocoloProtocoloDTO->getObjProtocoloDTO2();

            $dblIdProcedimentoDesanexado = $objProcedimentoDTODesanexado->getDblIdProcedimento();
            $strIdentificacaoProcedimentoDesanexado = $objProcedimentoDTODesanexado->getStrProtocoloProcedimentoFormatado();
            $strIcone = 'imagens/procedimento_desanexado.gif';

            $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
            $objAtributoAndamentoDTO->retStrValor();
            $objAtributoAndamentoDTO->setDblIdProtocoloAtividade($dblIdProcedimentoDesanexado);
            $objAtributoAndamentoDTO->setNumIdTarefaAtividade(TarefaRN::$TI_DESANEXADO_DO_PROCESSO);
            $objAtributoAndamentoDTO->setStrNome("MOTIVO");
            $objAtributoAndamentoDTO->setStrIdOrigem($objRelProtocoloProtocoloDTO->getDblIdRelProtocoloProtocolo());

            $objAtributoAndamentoRN = new AtributoAndamentoRN();
            $objAtributoAndamentoDTO = $objAtributoAndamentoRN->consultarRN1366($objAtributoAndamentoDTO);

            $strLinkProcessoDesanexado = 'about:blank';
            $strSrc = '';
            $strHtml = '';
            $strAcoesProcedimento = '';

            /*
            //adiciona também acesso ao protocolo para permitir inclusão de documentos
            if ($arrObjProtocoloDTO[$dblIdProcedimentoDesanexado]->getNumCodigoAcesso() > 0 || $bolFlagProtocolo){
              $strLinkProcessoDesanexado = $objSessaoSEI->assinarLink('controlador.php?acao=procedimento_trabalhar&id_procedimento='.$dblIdProcedimentoDesanexado);
            }
            */

            $strNos .= "\n\n".'//CA='.$arrObjProtocoloDTO[$dblIdProcedimentoDesanexado]->getNumCodigoAcesso()."\n";
            $strNos .= 'Nos[' . $numNo . '] = new infraArvoreNo("PROCEDIMENTO_DESANEXADO",' .
                '"' . $dblIdProcedimentoDesanexado.'-'.$objRelProtocoloProtocoloDTO->getDblIdRelProtocoloProtocolo().'",' .
                '"' . $strNoPai . '",' .
                '"' . $strLinkProcessoDesanexado . '",' .
                '"_blank",' .
                '"' . $strIdentificacaoProcedimentoDesanexado . '",' .
                '"'.DocumentoINT::montarTooltipAndamento('Processo desanexado: '.$objAtributoAndamentoDTO->getStrValor()) . '",' .
                '"' . $strIcone . '",' .
                '"' . $strIcone . '",' .
                '"' . $strIcone . '",' .
                'true,' .
                (($strLinkProcessoDesanexado != 'about:blank') ? 'true,' : 'false,') .
                'null,' .
                'null,' .
                'null,' .
                '"'.$strIdentificacaoProcedimentoDesanexado.'");' . "\n";


              $strNos .= 'Nos[' . $numNo . '].acoes = \'' . $strAcoesProcedimento . '\';' . "\n";
              $strNos .= 'Nos[' . $numNo . '].src = \'' . $strSrc . '\';' . "\n";
              $strNos .= 'Nos[' . $numNo . '].html = \'' . $strHtml . '\';' . "\n";
              $numNo++;

            } else if ($objRelProtocoloProtocoloDTO->getStrStaAssociacao() == RelProtocoloProtocoloRN::$TA_DOCUMENTO_MOVIDO) {


              $objDocumentoMovido = $objRelProtocoloProtocoloDTO->getObjProtocoloDTO2();

              $dblIdDocumentoMovido = $objDocumentoMovido->getDblIdDocumento();
              $strIdentificacaoDocumentoMovido = DocumentoINT::montarIdentificacaoArvore($objDocumentoMovido);
              $strIdentificacaoDocumentoMovido = InfraString::formatarXML($strIdentificacaoDocumentoMovido);

              $strIcone = 'imagens/sei_documento_movido.gif';

              $objAtributoAndamentoRN = new AtributoAndamentoRN();

              $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
              $objAtributoAndamentoDTO->retNumIdAtividade();
              $objAtributoAndamentoDTO->retStrValor();
              $objAtributoAndamentoDTO->setDblIdProtocoloAtividade($objRelProtocoloProtocoloDTO->getDblIdProtocolo1());
              $objAtributoAndamentoDTO->setNumIdTarefaAtividade(TarefaRN::$TI_DOCUMENTO_MOVIDO_PARA_PROCESSO);
              $objAtributoAndamentoDTO->setStrNome("MOTIVO");
              $objAtributoAndamentoDTO->setStrIdOrigem($objRelProtocoloProtocoloDTO->getDblIdRelProtocoloProtocolo());

              $objAtributoAndamentoDTOMotivo = $objAtributoAndamentoRN->consultarRN1366($objAtributoAndamentoDTO);

              $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
              $objAtributoAndamentoDTO->retStrValor();
              $objAtributoAndamentoDTO->retStrIdOrigem();
              $objAtributoAndamentoDTO->setNumIdAtividade($objAtributoAndamentoDTOMotivo->getNumIdAtividade());
              $objAtributoAndamentoDTO->setStrNome("PROCESSO");

              $objAtributoAndamentoDTOProcesso = $objAtributoAndamentoRN->consultarRN1366($objAtributoAndamentoDTO);


              $strLinkDocumentoMovido = 'about:blank';
              $strSrc = '';
              $strHtml = '';
              $strAcoesProcedimento = '';

              $strToolTipDocumentoMovido = '';
              if ($arrObjProtocoloDTO[$dblIdDocumentoMovido]->getNumCodigoAcesso() > 0){
                $strLinkDocumentoMovido = $objSessaoSEI->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem=procedimento_visualizar&id_procedimento='.$dblIdProcedimento);
                $strHtml = 'Documento movido para o processo <a href="'.$objSessaoSEI->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=arvore_visualizar&id_procedimento='.$objAtributoAndamentoDTOProcesso->getStrIdOrigem().'&id_documento='.$dblIdDocumentoMovido).'" target="_blank" style="font-size:1em;">'.$objAtributoAndamentoDTOProcesso->getStrValor().'</a>';
                $strToolTipDocumentoMovido = 'Documento movido para o processo '.$objAtributoAndamentoDTOProcesso->getStrValor().': ' . $objAtributoAndamentoDTOMotivo->getStrValor();
              }else{
                $strToolTipDocumentoMovido = 'Documento movido para outro processo';
              }

              $strNos .= "\n\n".'//CA='.$arrObjProtocoloDTO[$dblIdDocumentoMovido]->getNumCodigoAcesso()."\n";
              $strNos .= 'Nos[' . $numNo . '] = new infraArvoreNo("DOCUMENTO_MOVIDO",' .
                  '"' . $dblIdDocumentoMovido.'-'.$objRelProtocoloProtocoloDTO->getDblIdRelProtocoloProtocolo().'",' .
                  '"' . $strNoPai . '",' .
                  '"' . $strLinkDocumentoMovido . '",' .
                  '"ifrVisualizacao",' .
                  '"' . $strIdentificacaoDocumentoMovido . '",' .
                  '"'. DocumentoINT::montarTooltipAndamento($strToolTipDocumentoMovido) . '",' .
                  '"' . $strIcone . '",' .
                  '"' . $strIcone . '",' .
                  '"' . $strIcone . '",' .
                  'true,' .
                  (($strLinkDocumentoMovido != 'about:blank') ? 'true,' : 'false,') .
                  'null,' .
                  'null,' .
                  'null,' .
                  '"'.$objDocumentoMovido->getStrProtocoloDocumentoFormatado().'");' . "\n";


              $strNos .= 'Nos[' . $numNo . '].acoes = \'' . $strAcoesProcedimento . '\';' . "\n";
              $strNos .= 'Nos[' . $numNo . '].src = \'' . $strSrc . '\';' . "\n";
              $strNos .= 'Nos[' . $numNo . '].html = \'' . $strHtml . '\';' . "\n";
              $numNo++;

          } else {
            throw new InfraException('Tipo de associação do protocolo inválido.');
          }
        }

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
          $objProcedimentoAPI->setCodigoAcesso($numCodigoAcessoProcedimento);
          $objProcedimentoAPI->setSinAberto($bolFlagAberto?'S':'N');

          $arrObjDocumentoDTOIntegracao = array();
          foreach($arrDocumentoIntegracao as $arrItemDocumentoIntegracao){
            $arrObjDocumentoDTOIntegracao[] = $arrItemDocumentoIntegracao[1];
          }

          $strNosAcao .= "\n\n";
          $strNos .= "\n\n";

          foreach ($SEI_MODULOS as $seiModulo) {

            $strIcone = null;
            if (($arrRetIntegracao = $seiModulo->executar('alterarIconeArvoreDocumento', $objProcedimentoAPI, $arrObjDocumentoDTOIntegracao)) != null) {
              foreach ($arrRetIntegracao as $dblIdDocumento => $strIcone) {
                $strNos .= 'Nos[' . $arrDocumentoIntegracao[$dblIdDocumento][0] . '].icone = \''.$strIcone.'\';' . "\n";
              }
            }

            $strNos .= "\n";

            if (($arrRetIntegracao = $seiModulo->executar('montarBotaoDocumento', $objProcedimentoAPI, $arrObjDocumentoDTOIntegracao)) != null) {
              foreach ($arrRetIntegracao as $dblIdDocumento => $arrAcoesDocumento) {
                $strNos .= 'Nos[' . $arrDocumentoIntegracao[$dblIdDocumento][0] . '].acoes = Nos[' . $arrDocumentoIntegracao[$dblIdDocumento][0] . '].acoes.concat(\'' . implode('',$arrAcoesDocumento) . '\');' . "\n";
              }
            }

            $strNos .= "\n";

            if (($arrRetIntegracao = $seiModulo->executar('montarIconeDocumento', $objProcedimentoAPI, $arrObjDocumentoDTOIntegracao)) != null) {
              foreach ($arrRetIntegracao as $dblIdDocumento => $arrObjArvoreAcaoItemAPI) {
                foreach($arrObjArvoreAcaoItemAPI as $objArvoreAcaoItemAPI) {
                  $strNosAcao .= 'NosAcoes[' . $numNoAcao++ . '] = new infraArvoreAcao("' . $objArvoreAcaoItemAPI->getTipo() . '",' .
                      '"' . $objArvoreAcaoItemAPI->getId() . '",' .
                      '"' . $objArvoreAcaoItemAPI->getIdPai() . '",' .
                      '"' . $objArvoreAcaoItemAPI->getHref() . '",' .
                      '"' . $objArvoreAcaoItemAPI->getTarget() . '",' .
                      '"' . $objArvoreAcaoItemAPI->getTitle() . '",' .
                      '"' . $objArvoreAcaoItemAPI->getIcone() . '",' .
                      ($objArvoreAcaoItemAPI->getSinHabilitado()=='S' ? 'true' : 'false') . ');' . "\n";
                }
              }
            }
          }
        }
      }
    } catch (Exception $e) {
      throw new InfraException('Erro montando ações para documentos.', $e);
    }
  }

  public static function montarNivelAcesso($arrIdTipoProcedimento, $objProtocoloDTO, $bolConsultar, &$strCss, &$strHtml, &$strJsGlobal, &$strJsInicializar, &$strJsValidacoes){

    $bolHabilitarSigiloso = false;
    $bolHabilitarRestrito = false;
    $bolHabilitarPublico = false;
    $bolMarcarSigiloso = false;
    $bolMarcarRestrito = false;
    $bolMarcarPublico = false;
    $strCss = '';
    $strHtml = '';
    $strJsGlobal = '';
    $strJsInicializar = '';
    $strJsValidacoes = '';
    $strLabelHipoteseLegal = '';

    $strStaNivelAcesso =  $objProtocoloDTO->getStrStaNivelAcessoLocal();

    if ($bolConsultar){
      if ($strStaNivelAcesso==ProtocoloRN::$NA_SIGILOSO){
        $bolMarcarSigiloso = true;
      }else if ($strStaNivelAcesso==ProtocoloRN::$NA_RESTRITO){
        $bolMarcarRestrito = true;
      }else if ($strStaNivelAcesso==ProtocoloRN::$NA_PUBLICO){
        $bolMarcarPublico = true;
      }
    }else{

      if ($strStaNivelAcesso==ProtocoloRN::$NA_SIGILOSO){
        $bolHabilitarSigiloso = true;
      }else if ($strStaNivelAcesso==ProtocoloRN::$NA_RESTRITO){
        $bolHabilitarRestrito = true;
      }else if ($strStaNivelAcesso==ProtocoloRN::$NA_PUBLICO){
        $bolHabilitarPublico = true;
      }

      $objNivelAcessoPermitidoDTO = new NivelAcessoPermitidoDTO();
      $objNivelAcessoPermitidoDTO->setDistinct(true);
      $objNivelAcessoPermitidoDTO->retStrStaNivelAcesso();
      $objNivelAcessoPermitidoDTO->setNumIdTipoProcedimento($arrIdTipoProcedimento,InfraDTO::$OPER_IN);

      $objNivelAcessoPermitidoRN = new NivelAcessoPermitidoRN();
      $arrObjNivelAcessoPermitidoDTO = $objNivelAcessoPermitidoRN->listar($objNivelAcessoPermitidoDTO);

      foreach($arrObjNivelAcessoPermitidoDTO as $objNivelAcessoPermitidoDTO){
        if ($objNivelAcessoPermitidoDTO->getStrStaNivelAcesso()==ProtocoloRN::$NA_SIGILOSO){
          $bolHabilitarSigiloso = true;
        }else if ($objNivelAcessoPermitidoDTO->getStrStaNivelAcesso()==ProtocoloRN::$NA_RESTRITO){
          $bolHabilitarRestrito = true;
        }else if ($objNivelAcessoPermitidoDTO->getStrStaNivelAcesso()==ProtocoloRN::$NA_PUBLICO){
          $bolHabilitarPublico = true;
        }
      }

      if ($bolHabilitarSigiloso && ($strStaNivelAcesso==ProtocoloRN::$NA_SIGILOSO || (!$bolHabilitarRestrito && !$bolHabilitarPublico))){
        $bolMarcarSigiloso = true;

        if ($strStaNivelAcesso!=ProtocoloRN::$NA_SIGILOSO){
          $objProtocoloDTO->unSetStrStaGrauSigilo();
          $objProtocoloDTO->unSetNumIdHipoteseLegal();
        }

        $strStaNivelAcesso = ProtocoloRN::$NA_SIGILOSO;

      }else if ($bolHabilitarRestrito && ($strStaNivelAcesso==ProtocoloRN::$NA_RESTRITO || (!$bolHabilitarSigiloso && !$bolHabilitarPublico))){
        $bolMarcarRestrito = true;

        if ($strStaNivelAcesso!=ProtocoloRN::$NA_RESTRITO){
          $objProtocoloDTO->unSetStrStaGrauSigilo();
          $objProtocoloDTO->unSetNumIdHipoteseLegal();
        }

        $strStaNivelAcesso = ProtocoloRN::$NA_RESTRITO;

      }else if ($bolHabilitarPublico && ($strStaNivelAcesso==ProtocoloRN::$NA_PUBLICO || (!$bolHabilitarSigiloso && !$bolHabilitarRestrito))){
        $bolMarcarPublico = true;

        if ($strStaNivelAcesso!=ProtocoloRN::$NA_PUBLICO){
          $objProtocoloDTO->unSetStrStaGrauSigilo();
          $objProtocoloDTO->unSetNumIdHipoteseLegal();
        }

        $strStaNivelAcesso = ProtocoloRN::$NA_PUBLICO;
      }
    }

    if (!$objProtocoloDTO->isSetStrStaGrauSigilo() || !$objProtocoloDTO->isSetNumIdHipoteseLegal()){

      $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
      $objTipoProcedimentoDTO->setBolExclusaoLogica(false);
      $objTipoProcedimentoDTO->setDistinct(true);
      $objTipoProcedimentoDTO->retStrStaGrauSigiloSugestao();
      $objTipoProcedimentoDTO->retNumIdHipoteseLegalSugestao();
      $objTipoProcedimentoDTO->setNumIdTipoProcedimento($arrIdTipoProcedimento,InfraDTO::$OPER_IN);

      $objTipoProcedimentoRN = new TipoProcedimentoRN();
      $arrObjTipoProcedimentoDTO = $objTipoProcedimentoRN->listarRN0244($objTipoProcedimentoDTO);

      if (count($arrObjTipoProcedimentoDTO)==1) {

        $objTipoProcedimentoDTO = $arrObjTipoProcedimentoDTO[0];

        if (!$objProtocoloDTO->isSetStrStaGrauSigilo()) {
          $objProtocoloDTO->setStrStaGrauSigilo($objTipoProcedimentoDTO->getStrStaGrauSigiloSugestao());
        }

        if (!$objProtocoloDTO->isSetNumIdHipoteseLegal()) {
          $objProtocoloDTO->setNumIdHipoteseLegal($objTipoProcedimentoDTO->getNumIdHipoteseLegalSugestao());
        }
      }
    }

    $strItensSelGrauSigilo = ProtocoloINT::montarSelectGrauSigilo('null','&nbsp;', $objProtocoloDTO->getStrStaGrauSigilo());
    $strItensSelHipoteseLegal = HipoteseLegalINT::montarSelectNomeBaseLegal('null','&nbsp;', $objProtocoloDTO->getNumIdHipoteseLegal(),$strStaNivelAcesso);

    $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
    $numHabilitarHipoteseLegal = $objInfraParametro->getValor('SEI_HABILITAR_HIPOTESE_LEGAL');
    $numHabilitarGrauSigilo = $objInfraParametro->getValor('SEI_HABILITAR_GRAU_SIGILO');

    if ($strStaNivelAcesso==ProtocoloRN::$NA_PUBLICO){
      $strHeightDivNivelAcesso = '7em';
      $strTopOptionsNivelAcesso = (PaginaSEI::getInstance()->isBolNavegadorFirefox()?'25%':'45%');
      $strDisplayGrauSigilo = 'display:none';
      $strDisplayHipoteseLegal = 'display:none';
    }else if ($strStaNivelAcesso==ProtocoloRN::$NA_RESTRITO || $strStaNivelAcesso==ProtocoloRN::$NA_SIGILOSO){

      if ($numHabilitarHipoteseLegal){
        $strHeightDivNivelAcesso = '13em';
        $strTopOptionsNivelAcesso = (PaginaSEI::getInstance()->isBolNavegadorFirefox()?'14%':'26%');
        $strDisplayHipoteseLegal = '';
        $strTopLabelHipoteseLegal = (PaginaSEI::getInstance()->isBolNavegadorFirefox()?'46%':'54%');
        $strTopSelectHipoteseLegal = (PaginaSEI::getInstance()->isBolNavegadorFirefox()?'65%':'70%');
      }else{
        $strHeightDivNivelAcesso = '7em';
        $strTopOptionsNivelAcesso = (PaginaSEI::getInstance()->isBolNavegadorFirefox()?'25%':'45%');
        $strDisplayHipoteseLegal = 'display:none';
      }

      if ($numHabilitarGrauSigilo && $strStaNivelAcesso==ProtocoloRN::$NA_SIGILOSO){
        $strDisplayGrauSigilo = '';
      }else{
        $strDisplayGrauSigilo = 'display:none';
      }
    }else{
      $strHeightDivNivelAcesso = '7em';
      $strTopOptionsNivelAcesso = (PaginaSEI::getInstance()->isBolNavegadorFirefox()?'25%':'45%');
      $strDisplayGrauSigilo = 'display:none';
      $strDisplayHipoteseLegal = 'display:none';
    }

    $strCss = '';
    $strCss .= '#divNivelAcesso {height:'.$strHeightDivNivelAcesso.';}'."\n";
    $strCss .= '#fldNivelAcesso {position:absolute;left:0%;top:0%;height:80%;width:88%;}'."\n";
    $strCss .= '#divOptSigiloso  {position:absolute;left:13%;top:'.$strTopOptionsNivelAcesso.';width:30%;}'."\n";
    $strCss .= '#selGrauSigilo {'.$strDisplayGrauSigilo.'}'."\n";
    $strCss .= '#divOptRestrito {position:absolute;left:43%;top:'.$strTopOptionsNivelAcesso.';}'."\n";
    $strCss .= '#divOptPublico   {position:absolute;left:73%;top:'.$strTopOptionsNivelAcesso.';}'."\n";
    $strCss .= '#lblHipoteseLegal {position:absolute;left:5%;width:90%;top:'.$strTopLabelHipoteseLegal.';'.$strDisplayHipoteseLegal.'}'."\n";
    $strCss .= '#selHipoteseLegal {position:absolute;left:5%;width:90%;top:'.$strTopSelectHipoteseLegal.';'.$strDisplayHipoteseLegal.'}';

    if ($numHabilitarHipoteseLegal==1){
      $strLabelHipoteseLegal = 'infraLabelOpcional';
    }else if ($numHabilitarHipoteseLegal==2){
      $strLabelHipoteseLegal = 'infraLabelObrigatorio';
    }

    $strHtml = '';
    $strHtml .= '<div id="divNivelAcesso" class="infraAreaDados">'."\n";
    $strHtml .= '<fieldset id="fldNivelAcesso" class="infraFieldset">'."\n";
    $strHtml .= '<legend class="infraLegend">&nbsp;Nível de Acesso&nbsp;</legend>'."\n\n";

    $strHtml .= '<div id="divOptSigiloso" class="infraDivRadio">'."\n";
    $strHtml .= '  <input '.($bolHabilitarSigiloso?'':'disabled="disabled"').' type="radio" name="rdoNivelAcesso" id="optSigiloso" onchange="alterarNivelAcesso()" value="'.ProtocoloRN::$NA_SIGILOSO.'" '.($bolMarcarSigiloso?'checked="checked"':'').' class="infraRadio"/>'."\n";
    $strHtml .= '  <span '.($bolHabilitarSigiloso?'':'disabled="disabled"').' id="spnSigiloso"><label id="lblSigiloso" for="optSigiloso" class="infraLabelRadio">Sigiloso</label><label>&nbsp;</label></span>'."\n";
    $strHtml .= '  <select id="selGrauSigilo" name="selGrauSigilo" class="infraSelect">'."\n";
    $strHtml .= $strItensSelGrauSigilo;
    $strHtml .= '  </select>'."\n";
    $strHtml .= '</div>'."\n\n";

    $strHtml .= '<div id="divOptRestrito" class="infraDivRadio">'."\n";
    $strHtml .= '  <input '.($bolHabilitarRestrito?'':'disabled="disabled"').' type="radio" name="rdoNivelAcesso" id="optRestrito" onchange="alterarNivelAcesso()" value="'.ProtocoloRN::$NA_RESTRITO.'" '.($bolMarcarRestrito?'checked="checked"':'').' class="infraRadio"/>'."\n";
    $strHtml .= '  <span '.($bolHabilitarRestrito?'':'disabled="disabled"').' id="spnRestrito"><label id="lblRestrito" for="optRestrito" class="infraLabelRadio">Restrito</label></span>'."\n";
    $strHtml .= '</div>'."\n\n";

    $strHtml .= '<div id="divOptPublico" class="infraDivRadio">'."\n";
    $strHtml .= '  <input '.($bolHabilitarPublico?'':'disabled="disabled"').' type="radio" name="rdoNivelAcesso" id="optPublico" onchange="alterarNivelAcesso()" value="'.ProtocoloRN::$NA_PUBLICO.'" '.($bolMarcarPublico?'checked="checked"':'').' class="infraRadio"/>'."\n";
    $strHtml .= '  <span '.($bolHabilitarPublico?'':'disabled="disabled"').' id="spnPublico"><label id="lblPublico" for="optPublico" class="infraLabelRadio">Público</label></span>'."\n";
    $strHtml .= '</div>'."\n\n";

    $strHtml .= '<label id="lblHipoteseLegal" for="selHipoteseLegal" accesskey="" class="'.$strLabelHipoteseLegal.'">Hipótese Legal:</label>'."\n";
    $strHtml .= '<select id="selHipoteseLegal" name="selHipoteseLegal" class="infraSelect">'."\n";
    $strHtml .= $strItensSelHipoteseLegal;
    $strHtml .= '</select>'."\n\n";

    $strHtml .= '</fieldset>'."\n";
    $strHtml .= '</div>'."\n\n";

    $strJsValidacoes = '';

    $strJsValidacoes .= 'if (!document.getElementById(\'optSigiloso\').checked && !document.getElementById(\'optRestrito\').checked && !document.getElementById(\'optPublico\').checked) {'."\n";
    $strJsValidacoes .= '  alert(\'Informe o nível de acesso.\');'."\n";
    $strJsValidacoes .= '  return false;'."\n";
    $strJsValidacoes .= '}'."\n\n";

    if ($numHabilitarGrauSigilo==2){
      $strJsValidacoes .= 'if (document.getElementById(\'optSigiloso\').checked){'."\n";
      $strJsValidacoes .= '  if (!infraSelectSelecionado(\'selGrauSigilo\')){'."\n";
      $strJsValidacoes .= '    alert(\'Informe o grau de sigilo.\');'."\n";
      $strJsValidacoes .= '    document.getElementById(\'selGrauSigilo\').focus();'."\n";
      $strJsValidacoes .= '    return false;'."\n";
      $strJsValidacoes .= '  }'."\n";
      $strJsValidacoes .= '}'."\n\n";
    }


    if ($numHabilitarHipoteseLegal==2){
      $strJsValidacoes .= 'if (document.getElementById(\'optSigiloso\').checked || document.getElementById(\'optRestrito\').checked){'."\n";
      $strJsValidacoes .= '  if (!infraSelectSelecionado(\'selHipoteseLegal\')){'."\n";
      $strJsValidacoes .= '    alert(\'Informe a Hipótese Legal.\');'."\n";
      $strJsValidacoes .= '    document.getElementById(\'selHipoteseLegal\').focus();'."\n";
      $strJsValidacoes .= '    return false;'."\n";
      $strJsValidacoes .= '  }'."\n";
      $strJsValidacoes .= '}'."\n\n";
    }

    $strJsGlobal = '';

    $strJsGlobal .= 'var objAjaxHipoteseLegal = null;'."\n";
    $strJsGlobal .= 'var objAjaxTipoProcedimentoSugestoes = null;'."\n\n";


    $strJsGlobal .= 'function alterarNivelAcesso(){'."\n";

    $strJsGlobal .= '  infraSelectSelecionarItem(\'selGrauSigilo\',\'null\');'."\n";
    $strJsGlobal .= '  infraSelectSelecionarItem(\'selHipoteseLegal\',\'null\');'."\n\n";

    $strJsGlobal .= '  if (document.getElementById(\'optPublico\').checked){'."\n";
    $strJsGlobal .= '    document.getElementById(\'divNivelAcesso\').style.height = \'7em\';'."\n";
    $strJsGlobal .= '    document.getElementById(\'divOptSigiloso\').style.top = \''.(PaginaSEI::getInstance()->isBolNavegadorFirefox()?'25%':'45%').'\';'."\n";
    $strJsGlobal .= '    document.getElementById(\'divOptRestrito\').style.top = \''.(PaginaSEI::getInstance()->isBolNavegadorFirefox()?'25%':'45%').'\';'."\n";
    $strJsGlobal .= '    document.getElementById(\'divOptPublico\').style.top = \''.(PaginaSEI::getInstance()->isBolNavegadorFirefox()?'25%':'45%').'\';'."\n";
    $strJsGlobal .= '    document.getElementById(\'lblHipoteseLegal\').style.display = \'none\';'."\n";
    $strJsGlobal .= '    document.getElementById(\'selHipoteseLegal\').style.display = \'none\';'."\n";
    $strJsGlobal .= '    document.getElementById(\'selGrauSigilo\').style.display = \'none\';'."\n";
    $strJsGlobal .= '  }else if (document.getElementById(\'optRestrito\').checked || document.getElementById(\'optSigiloso\').checked){'."\n";
    if ($numHabilitarHipoteseLegal){
      $strJsGlobal .= '    document.getElementById(\'divNivelAcesso\').style.height = \'13em\';'."\n";
      $strJsGlobal .= '    document.getElementById(\'divOptSigiloso\').style.top = \''.(PaginaSEI::getInstance()->isBolNavegadorFirefox()?'14%':'26%').'\';'."\n";
      $strJsGlobal .= '    document.getElementById(\'divOptRestrito\').style.top = \''.(PaginaSEI::getInstance()->isBolNavegadorFirefox()?'14%':'26%').'\';'."\n";
      $strJsGlobal .= '    document.getElementById(\'divOptPublico\').style.top = \''.(PaginaSEI::getInstance()->isBolNavegadorFirefox()?'14%':'26%').'\';'."\n";
      $strJsGlobal .= '    document.getElementById(\'lblHipoteseLegal\').style.top = \''.(PaginaSEI::getInstance()->isBolNavegadorFirefox()?'46%':'54%').'\';'."\n";
      $strJsGlobal .= '    document.getElementById(\'selHipoteseLegal\').style.top = \''.(PaginaSEI::getInstance()->isBolNavegadorFirefox()?'65%':'70%').'\';'."\n";
      $strJsGlobal .= '    document.getElementById(\'lblHipoteseLegal\').style.display = \'block\';'."\n";
      $strJsGlobal .= '    document.getElementById(\'selHipoteseLegal\').style.display = \'block\';'."\n";
      $strJsGlobal .= "\n";
    }

    if ($numHabilitarGrauSigilo){
      $strJsGlobal .= '    if (document.getElementById(\'optSigiloso\').checked){'."\n";
      $strJsGlobal .= '      document.getElementById(\'selGrauSigilo\').style.display = \'block\';'."\n";
      $strJsGlobal .= '    }else{'."\n";
      $strJsGlobal .= '      document.getElementById(\'selGrauSigilo\').style.display = \'none\';'."\n";
      $strJsGlobal .= '    }'."\n";
      $strJsGlobal .= "\n";
    }

    if ($numHabilitarHipoteseLegal || $numHabilitarGrauSigilo){
      $strJsGlobal .= '    objAjaxTipoProcedimentoSugestoes.executar();'."\n";
    }

    $strJsGlobal .= '  }'."\n";
    $strJsGlobal .= '}'."\n\n";

    $strJsInicializar = '';

    $strJsInicializar .= 'objAjaxHipoteseLegal = new infraAjaxMontarSelect(\'selHipoteseLegal\',\''.SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=hipotese_legal_select_nome_base_legal').'\');'."\n";
    $strJsInicializar .= 'objAjaxHipoteseLegal.prepararExecucao = function(){'."\n";
    $strJsInicializar .= '  if (document.getElementById(\'optSigiloso\').checked){'."\n";
    $strJsInicializar .= '    staNivelAcesso = \''.ProtocoloRN::$NA_SIGILOSO.'\';'."\n";
    $strJsInicializar .= '  }else if (document.getElementById(\'optRestrito\').checked){'."\n";
    $strJsInicializar .= '    staNivelAcesso = \''.ProtocoloRN::$NA_RESTRITO.'\';'."\n";
    $strJsInicializar .= '  }else if (document.getElementById(\'optPublico\').checked){'."\n";
    $strJsInicializar .= '    staNivelAcesso = \''.ProtocoloRN::$NA_PUBLICO.'\';'."\n";
    $strJsInicializar .= '  }'."\n";
    $strJsInicializar .= '  return infraAjaxMontarPostPadraoSelect(\'null\',\'\',document.getElementById(\'hdnIdHipoteseLegalSugestao\').value) + \'&staNivelAcesso=\' + staNivelAcesso;'."\n";
    $strJsInicializar .= '};'."\n\n";

    $strJsInicializar .= 'objAjaxTipoProcedimentoSugestoes = new infraAjaxComplementar(null,\''.SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=tipo_procedimento_obter_sugestoes').'\');'."\n";
    $strJsInicializar .= 'objAjaxTipoProcedimentoSugestoes.prepararExecucao = function(){'."\n";
    $strJsInicializar .= '  return \'idTipoProcedimento=\'+document.getElementById(\'hdnIdTipoProcedimento\').value;'."\n";
    $strJsInicializar .= '}'."\n";
    $strJsInicializar .= 'objAjaxTipoProcedimentoSugestoes.processarResultado = function(arr){'."\n";

    $strJsInicializar .= '  if(arr!=null){'."\n";
    $strJsInicializar .= '    if (document.getElementById(\'optSigiloso\').checked){'."\n";
    $strJsInicializar .= '      for(var i=0; i < document.getElementById(\'selGrauSigilo\').options.length;i++){'."\n";
    $strJsInicializar .= '        if (document.getElementById(\'selGrauSigilo\').options[i].value == arr[\'StaGrauSigiloSugestao\']){'."\n";
    $strJsInicializar .= '          document.getElementById(\'selGrauSigilo\').options[i].selected = true;'."\n";
    $strJsInicializar .= '          break;'."\n";
    $strJsInicializar .= '        }'."\n";
    $strJsInicializar .= '      }'."\n";
    $strJsInicializar .= '    }'."\n";

    $strJsInicializar .= '    if (arr[\'IdHipoteseLegalSugestao\']!=undefined){'."\n";
    $strJsInicializar .= '      document.getElementById(\'hdnIdHipoteseLegalSugestao\').value = arr[\'IdHipoteseLegalSugestao\'];'."\n";
    $strJsInicializar .= '    }'."\n";
    $strJsInicializar .= '  }'."\n";
    $strJsInicializar .= '  objAjaxHipoteseLegal.executar();'."\n";
    $strJsInicializar .= '}'."\n\n";
  }

  public static function montarNoAcaoAcesso($dblIdProtocolo, $numNoAcao, $strStaNivelAcesso, $staGrauSigilo, $strNomeHipoteseLegal, $strBaseLegalHipoteseLegal, $arrObjGrauSigiloDTO){

    $strTexto = '';
    $strImagem = '';

    if ($strStaNivelAcesso==ProtocoloRN::$NA_RESTRITO) {
      $strTexto = 'Acesso Restrito';
      $strImagem = 'sei_chave_restrito.gif';
    }else if ($strStaNivelAcesso==ProtocoloRN::$NA_SIGILOSO) {
      $strTexto = 'Acesso Sigiloso';
      $strImagem = 'sei_chave_sigiloso.gif';

      if ($staGrauSigilo!=''){
        $strTexto .= ' ('. $arrObjGrauSigiloDTO[$staGrauSigilo]->getStrDescricao().')';
      }
    }

    if ($strNomeHipoteseLegal!=''){
      $strTexto .= '\n'.$strNomeHipoteseLegal.' ('.$strBaseLegalHipoteseLegal.')';
      $strTexto = PaginaSEI::formatarParametrosJavaScript($strTexto, false);
    }

    return 'NosAcoes['.$numNoAcao.'] = new infraArvoreAcao("NIVEL_ACESSO",'.
                                                            '"NA'.$dblIdProtocolo.'",'.
                                                            '"'.$dblIdProtocolo.'",'.
                                                            '"javascript:alert(\''.str_replace('\n','\\\n',$strTexto).'\');",'.
                                                            'null,'.
                                                            '"'.$strTexto.'",'.
                                                            '"imagens/'.$strImagem.'",'.
                                                            'true);'."\n";
  }

  public static function montarNoAcaoAcessoModulos($dblIdProtocolo, $numNoAcao, $arrAcessoModulos){

    global $SEI_MODULOS;

    if (isset($arrAcessoModulos[SeiIntegracao::$TAM_PERMITIDO])) {
      $arrModulos = $arrAcessoModulos[SeiIntegracao::$TAM_PERMITIDO];
      $strTipo = 'concedido';
      $strIcone = 'sei_cadeado_aberto.png';
    }else {
      $arrModulos = $arrAcessoModulos[SeiIntegracao::$TAM_NEGADO];
      $strTipo = 'negado';
      $strIcone = 'sei_cadeado_fechado.png';
    }

    if (count($arrModulos) == 1) {
      $strAcessoModulos = 'Acesso '.$strTipo.' pelo módulo "' . $SEI_MODULOS[$arrModulos[0]]->getNome() . '"';
    } else {
      $strAcessoModulos = '';
      foreach ($arrModulos as $strModulo) {

        if ($strAcessoModulos != '') {
          $strAcessoModulos .= ',\n';
        }

        $strAcessoModulos .= '"' . $SEI_MODULOS[$strModulo]->getNome() . '"';
      }
      $strAcessoModulos = 'Acesso '.$strTipo.' pelos módulos:\n' . $strAcessoModulos;
    }

    $strAcessoModulos = PaginaSEI::formatarParametrosJavaScript($strAcessoModulos, false);

    return 'NosAcoes[' . $numNoAcao . '] = new infraArvoreAcao("ACESSO_MODULO",' .
                                          '"AM' . $dblIdProtocolo . '",' .
                                          '"' . $dblIdProtocolo . '",' .
                                          '"javascript:alert(\'' . str_replace('\n', '\\\n', $strAcessoModulos) . '\');",' .
                                          'null,' .
                                          '"' . $strAcessoModulos . '",' .
                                          '"imagens/'.$strIcone.'",' .
                                          'true);' . "\n";
  }
  
}
?>