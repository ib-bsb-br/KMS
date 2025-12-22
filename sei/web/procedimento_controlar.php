<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4º REGIÃO
*
* 31/01/2008 - criado por marcio_db
*
* Versão do Gerador de Código: 1.13.1
*
* Versão no CVS: $Id$
*/

try {
  require_once dirname(__FILE__) . '/SEI.php';

  session_start();

  //////////////////////////////////////////////////////////////////////////////
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(true);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  //PaginaSEI::getInstance()->prepararSelecao('procedimento_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  //$numSeg = InfraUtil::verificarTempoProcessamento();

  global $SEI_MODULOS;

  $strIdHdnMarcador = 'hdnIdMarcador'.SessaoSEI::getInstance()->getNumIdUnidadeAtual();

  PaginaSEI::getInstance()->salvarCamposPost(array($strIdHdnMarcador, 'hdnMeusProcessos', 'hdnTipoVisualizacao'));

  $arrComandos = array();

  switch ($_GET['acao']) {

    case 'procedimento_concluir':

      $objProcedimentoRN = new ProcedimentoRN();

      $arr = array_merge(PaginaSEI::getInstance()->getArrStrItensSelecionados('Gerados'), PaginaSEI::getInstance()->getArrStrItensSelecionados('Recebidos'), PaginaSEI::getInstance()->getArrStrItensSelecionados('Detalhado'));

      $arrObjProcedimentoDTO = array();
      foreach ($arr as $dblIdProcedimento) {
        $objProcedimentoDTO = new ProcedimentoDTO();
        $objProcedimentoDTO->setDblIdProcedimento($dblIdProcedimento);
        $arrObjProcedimentoDTO[] = $objProcedimentoDTO;
      }

      try {
        $objProcedimentoRN->concluir($arrObjProcedimentoDTO);

        PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');

      } catch (Exception $e) {
        PaginaSEI::getInstance()->processarExcecao($e);
      }

      header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao']));
      die;


    case 'procedimento_controlar':
      //Título
      $strTitulo = 'Controle de Processos';

      $strLinkNovidades = '';

      //sessão carrega na entrada do sistema
      if (isset($_GET['inicializando'])) {
        $objNovidadeDTO = new NovidadeDTO();
        $objNovidadeDTO->retNumIdNovidade();

        $objInfraDadoUsuario = new InfraDadoUsuario(SessaoSEI::getInstance());
        $dthUltimaNovidadeExibida = $objInfraDadoUsuario->getValor('NOVIDADE_ULTIMA');

        if ($dthUltimaNovidadeExibida == null) {

          $dthUltimaNovidadeExibida = $_COOKIE[PaginaSEI::getInstance()->getStrPrefixoCookie() . '_ultima_novidade'];

          if ($dthUltimaNovidadeExibida != null) {
            $objInfraDadoUsuario->setValor('NOVIDADE_ULTIMA', $dthUltimaNovidadeExibida);
          }
        }

        //se não existe data para utilizar no cookie
        if (!InfraString::isBolVazia($dthUltimaNovidadeExibida) && InfraData::validarDataHora($dthUltimaNovidadeExibida)) {

          $objNovidadeDTO->adicionarCriterio(array('Liberacao', 'Liberacao'),
              array(InfraDTO::$OPER_MAIOR, InfraDTO::$OPER_DIFERENTE),
              array($dthUltimaNovidadeExibida, NovidadeRN::$DATA_NAO_LIBERADO),
              InfraDTO::$OPER_LOGICO_AND);
        } else {
          $objNovidadeDTO->setDthLiberacao(NovidadeRN::$DATA_NAO_LIBERADO, InfraDTO::$OPER_DIFERENTE);
        }
        $objNovidadeDTO->setNumMaxRegistrosRetorno(1);

        $objNovidadeRN = new NovidadeRN();

        if ($objNovidadeRN->consultar($objNovidadeDTO) != null) {
          $strLinkNovidades = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=novidade_mostrar');
        }
      }

      break;

    default:
      throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
  }

  $strTipoVisualizacao = PaginaSEI::getInstance()->recuperarCampo('hdnTipoVisualizacao', 'R');
  $strLinkIncluirEmBloco = '';
  $strLinkCredencialAcessar = '';

  $strResultadoRecebidos = '';
  $strResultadoGerados = '';
  $strResultadoDetalhado = '';
  $strResultadoMarcadores = '';

  $objPesquisaPendenciaDTO = new PesquisaPendenciaDTO();
  $objPesquisaPendenciaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
  $objPesquisaPendenciaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
  $objPesquisaPendenciaDTO->setStrStaTipoAtribuicao(PaginaSEI::getInstance()->recuperarCampo('hdnMeusProcessos'));

  $numIdMarcadorFiltro = null;
  $objMarcadorDTOFiltro = null;

  if (isset($_GET['ver_por_marcadores'])) {

    $objAtividadeRN = new AtividadeRN();
    $arrObjMarcadorDTO = $objAtividadeRN->listarPendenciasPorMarcadores($objPesquisaPendenciaDTO);

    $numRegistrosMarcadores = count($arrObjMarcadorDTO);

    if ($numRegistrosMarcadores) {

      $strResultadoMarcadores .= '<table id="tblMarcadores" border="0" cellspacing="0" cellpadding="1" width="95%" class="infraTable tabelaControle" summary="Tabela de Quantidade de Processos por Marcador.">' . "\n";
      //$strResultadoMarcadores .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela('', $numRegistrosMarcadores, '') . '</caption>';
      $strResultadoMarcadores .= '<tr>';
      $strResultadoMarcadores .= '<th class="tituloControle" style="display:none">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
      $strResultadoMarcadores .= '<th class="tituloControle">Processos</th>' . "\n";
      $strResultadoMarcadores .= '<th class="tituloControle" colspan="2">Marcador</th>' . "\n";
      $strResultadoMarcadores .= '</tr>' . "\n";

      for ($i = 0; $i < $numRegistrosMarcadores; $i++) {
        $strResultadoMarcadores .= '<tr class="infraTrClara" style="border:0px solid white;">' . "\n";
        $strResultadoMarcadores .= '<td align="center" width="10%"><a href="javascript:void(0);" onclick="filtrarMarcador('.$arrObjMarcadorDTO[$i]->getNumIdMarcador().')" class="ancoraPadraoAzul">'.InfraUtil::formatarMilhares($arrObjMarcadorDTO[$i]->getNumQuantidade()).'</a></td>' . "\n";
        $strResultadoMarcadores .= '<td align="right"><img src='.PaginaSEI::getInstance()->getDiretorioImagensLocal().'/'.$arrObjMarcadorDTO[$i]->getStrArquivoIcone().' class="InfraImg" /></td>' . "\n";
        $strResultadoMarcadores .= '<td align="left">' . PaginaSEI::tratarHTML($arrObjMarcadorDTO[$i]->getStrNome()) . '</td>' . "\n";
        $strResultadoMarcadores .= '</tr>' . "\n";
      }
      $strResultadoMarcadores .= '</table>';
    }

  }else{

    $numIdMarcadorFiltro = PaginaSEI::getInstance()->recuperarCampo($strIdHdnMarcador);

    if (!InfraString::isBolVazia($numIdMarcadorFiltro)) {

      $objMarcadorDTOFiltro = new MarcadorDTO();
      $objMarcadorDTOFiltro->setBolExclusaoLogica(false);
      $objMarcadorDTOFiltro->retStrNome();
      $objMarcadorDTOFiltro->retStrStaIcone();
      $objMarcadorDTOFiltro->setNumIdMarcador($numIdMarcadorFiltro);
      $objMarcadorDTOFiltro->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

      $objMarcadorRN = new MarcadorRN();
      $objMarcadorDTOFiltro = $objMarcadorRN->consultar($objMarcadorDTOFiltro);

      if ($objMarcadorDTOFiltro!=null){
        $objPesquisaPendenciaDTO->setNumIdMarcador($numIdMarcadorFiltro);
      }else{
        $numIdMarcadorFiltro = null;
      }

    }

    $objPesquisaPendenciaDTO->setStrStaEstadoProcedimento(array(ProtocoloRN::$TE_NORMAL,ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO));
    $objPesquisaPendenciaDTO->setStrSinAnotacoes('S');
    $objPesquisaPendenciaDTO->setStrSinRetornoProgramado('S');
    $objPesquisaPendenciaDTO->setStrSinCredenciais('S');
    $objPesquisaPendenciaDTO->setStrSinSituacoes('S');
    $objPesquisaPendenciaDTO->setStrSinMarcadores('S');

    if ($strTipoVisualizacao == 'R') {
      $objPesquisaPendenciaDTO->setStrSinInteressados('N');
    } else {
      $objPesquisaPendenciaDTO->setStrSinInteressados('S');
    }

    $objAtividadeRN = new AtividadeRN();

    //$numSeg = InfraUtil::verificarTempoProcessamento();

    $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
    $numPaginacaoControle = $objInfraParametro->getValor('SEI_NUM_PAGINACAO_CONTROLE_PROCESSOS');

    if ($strTipoVisualizacao == 'D') {

      if ($numPaginacaoControle > 0) {
        PaginaSEI::getInstance()->prepararPaginacao($objPesquisaPendenciaDTO, $numPaginacaoControle);
      }

      $arrObjProcedimentoDTO = $objAtividadeRN->listarPendenciasRN0754($objPesquisaPendenciaDTO);

      if ($numPaginacaoControle > 0) {
        PaginaSEI::getInstance()->processarPaginacao($objPesquisaPendenciaDTO);
      }

    } else {

      $objPesquisaPendenciaDTORecebidos = clone($objPesquisaPendenciaDTO);
      $objPesquisaPendenciaDTORecebidos->setStrSinInicial('N');

      if ($numPaginacaoControle > 0) {
        PaginaSEI::getInstance()->prepararPaginacao($objPesquisaPendenciaDTORecebidos, $numPaginacaoControle, false, null, 'Recebidos');
      }

      $arrObjProcedimentoDTORecebidos = $objAtividadeRN->listarPendenciasRN0754($objPesquisaPendenciaDTORecebidos);

      if ($numPaginacaoControle > 0) {
        PaginaSEI::getInstance()->processarPaginacao($objPesquisaPendenciaDTORecebidos, 'Recebidos');
      }

      $objPesquisaPendenciaDTOGerados = clone($objPesquisaPendenciaDTO);
      $objPesquisaPendenciaDTOGerados->setStrSinInicial('S');

      if ($numPaginacaoControle > 0) {
        PaginaSEI::getInstance()->prepararPaginacao($objPesquisaPendenciaDTOGerados, $numPaginacaoControle, false, null, 'Gerados');
      }

      $arrObjProcedimentoDTOGerados = $objAtividadeRN->listarPendenciasRN0754($objPesquisaPendenciaDTOGerados);

      if ($numPaginacaoControle > 0) {
        PaginaSEI::getInstance()->processarPaginacao($objPesquisaPendenciaDTOGerados, 'Gerados');
      }

      $numRegistrosRecebidos = count($arrObjProcedimentoDTORecebidos);
      $numRegistrosGerados = count($arrObjProcedimentoDTOGerados);

      $arrObjProcedimentoDTO = array_merge($arrObjProcedimentoDTORecebidos, $arrObjProcedimentoDTOGerados);

    }

    $numRegistros = count($arrObjProcedimentoDTO);

    //$numSeg = InfraUtil::verificarTempoProcessamento($numSeg);
    //InfraDebug::getInstance()->gravar('#'.$numSeg.' s');

    $bolAcaoAtribuicaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('procedimento_atribuicao_cadastrar');
    $bolAcaoDefinirAtividade = SessaoSEI::getInstance()->verificarPermissao('procedimento_atualizar_andamento');
    $bolAcaoGerarPendencia = SessaoSEI::getInstance()->verificarPermissao('procedimento_enviar');
    $bolAcaoSobrestarProcesso = SessaoSEI::getInstance()->verificarPermissao('procedimento_sobrestar');
    $bolAcaoConcluirProcesso = SessaoSEI::getInstance()->verificarPermissao('procedimento_concluir');
    $bolAcaoIncluirEmBloco = SessaoSEI::getInstance()->verificarPermissao('rel_bloco_protocolo_cadastrar');
    $bolAcaoRegistrarAnotacao = SessaoSEI::getInstance()->verificarPermissao('anotacao_registrar');
    $bolAcaoDocumentoGerarMultiplo = SessaoSEI::getInstance()->verificarPermissao('documento_gerar_multiplo');
    $bolAcaoAndamentoSituacaoGerenciar = SessaoSEI::getInstance()->verificarPermissao('andamento_situacao_gerenciar');
    $bolAcaoAndamentoMarcadorGerenciar = SessaoSEI::getInstance()->verificarPermissao('andamento_marcador_gerenciar');
    $bolAcaoUsuarioValidarAcesso  = SessaoSEI::getInstance()->verificarPermissao('usuario_validar_acesso');

    if ($numRegistros > 0) {

      $numTabBotao = PaginaSEI::getInstance()->getProxTabBarraComandosSuperior();
      if ($bolAcaoGerarPendencia) {
        $arrComandos[] = '<a href="#" onclick="return acaoControleProcessos(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_enviar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao']) . '\', true, false);" tabindex="' . $numTabBotao . '" class="botaoSEI"><img src="imagens/sei_enviar_processo.gif" class="infraCorBarraSistema" alt="Enviar Processo" title="Enviar Processo"/></a>';
      }

      if ($bolAcaoDefinirAtividade) {
        $arrComandos[] = '<a href="#" onclick="return acaoControleProcessos(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_atualizar_andamento&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao']) . '\', true, true);" tabindex="' . $numTabBotao . '" class="botaoSEI"><img src="imagens/sei_atualizar_andamento.gif" class="infraCorBarraSistema" alt="Atualizar Andamento" title="Atualizar Andamento"/></a>';

      }

      if ($bolAcaoAtribuicaoCadastrar) {
        $arrComandos[] = '<a href="#" onclick="return acaoControleProcessos(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_atribuicao_cadastrar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao']) . '\', true, false);" tabindex="' . $numTabBotao . '" class="botaoSEI"><img src="imagens/sei_atribuir_processo.gif" class="infraCorBarraSistema" alt="Atribuição de Processos" title="Atribuição de Processos"/></a>';
      }

      if ($bolAcaoIncluirEmBloco) {
        $strLinkIncluirEmBloco = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=rel_bloco_protocolo_cadastrar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao']);
        $arrComandos[] = '<a href="#" onclick="return acaoBlocoProcessar();" tabindex="' . $numTabBotao . '" class="botaoSEI"><img src="imagens/sei_incluir_em_bloco.gif" class="infraCorBarraSistema" alt="Incluir em Bloco" title="Incluir em Bloco"/></a>';
      }

      if ($bolAcaoSobrestarProcesso) {
        $arrComandos[] = '<a href="#" onclick="return acaoControleProcessos(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_sobrestar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao']) . '\', true, false);" tabindex="' . $numTabBotao . '" class="botaoSEI"><img src="imagens/sei_sobrestar_processo.gif" class="infraCorBarraSistema" alt="Sobrestar Processo" title="Sobrestar Processo"/></a>';
      }

      if ($bolAcaoConcluirProcesso) {
        $arrComandos[] = '<a href="#" onclick="return acaoControleProcessos(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_concluir&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao']) . '\', true, true);" tabindex="' . $numTabBotao . '" class="botaoSEI"><img src="imagens/sei_concluir_processo.gif" class="infraCorBarraSistema" alt="Concluir Processo nesta Unidade" title="Concluir Processo nesta Unidade"/></a>';
      }

      if ($bolAcaoRegistrarAnotacao) {
        $arrComandos[] = '<a href="#" onclick="return acaoControleProcessos(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=anotacao_registrar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao']) . '\', true, true);" tabindex="' . $numTabBotao . '" class="botaoSEI"><img src="imagens/sei_anotacao.gif" class="infraCorBarraSistema" alt="Anotações" title="Anotações"/></a>';
      }

      if ($bolAcaoDocumentoGerarMultiplo) {
        $arrComandos[] = '<a href="#" onclick="return acaoControleProcessos(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_gerar_multiplo&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao']) . '\', true, true);" tabindex="' . $numTabBotao . '" class="botaoSEI"><img src="imagens/sei_documento_gerar_multiplo.gif" class="infraCorBarraSistema" alt="Incluir Documento" title="Incluir Documento"/></a>';
      }

      if ($bolAcaoAndamentoSituacaoGerenciar) {
        $objRelSituacaoUnidadeDTO = new RelSituacaoUnidadeDTO();
        $objRelSituacaoUnidadeDTO->retNumIdSituacao();
        $objRelSituacaoUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objRelSituacaoUnidadeDTO->setStrSinAtivoSituacao('S');
        $objRelSituacaoUnidadeDTO->setNumMaxRegistrosRetorno(1);

        $objRelSituacaoUnidadeRN = new RelSituacaoUnidadeRN();

        if ($objRelSituacaoUnidadeRN->consultar($objRelSituacaoUnidadeDTO) != null) {
          $arrComandos[] = '<a href="#" onclick="return acaoControleProcessos(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=andamento_situacao_gerenciar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao']) . '\', true, false);" tabindex="' . $numTabBotao . '" class="botaoSEI"><img src="imagens/sei_situacao.png" class="infraCorBarraSistema" alt="Gerenciar Ponto de Controle" title="Gerenciar Ponto de Controle"/></a>';
        }
      }

      if ($bolAcaoAndamentoMarcadorGerenciar) {
        $objMarcadorDTO = new MarcadorDTO();
        $objMarcadorDTO->retNumIdMarcador();
        $objMarcadorDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objMarcadorDTO->setNumMaxRegistrosRetorno(1);

        $objMarcadorRN = new MarcadorRN();
        if ($objMarcadorRN->consultar($objMarcadorDTO) != null) {
          $arrComandos[] = '<a href="#" onclick="return acaoControleProcessos(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=andamento_marcador_gerenciar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao']) . '\', true, true);" tabindex="' . $numTabBotao . '" class="botaoSEI"><img src="imagens/sei_marcador.png" class="infraCorBarraSistema" alt="Gerenciar Marcador" title="Gerenciar Marcador"/></a>';
        }
      }
    }

    if ($bolAcaoUsuarioValidarAcesso) {
      $objAcessoDTO = new AcessoDTO();
      $objAcessoDTO->retNumIdAcesso();
      $objAcessoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      $objAcessoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
      $objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_PROCESSO);
      $objAcessoDTO->setNumMaxRegistrosRetorno(1);

      $objAcessoRN = new AcessoRN();
      if ($objAcessoRN->consultar($objAcessoDTO) != null) {
        $strLinkCredencialAcessar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=usuario_validar_acesso&acao_origem='.$_GET['acao'].'&acao_destino=procedimento_credencial_listar');
        $arrComandos[] = '<a href="#" onclick="return listarCredenciais();" tabindex="' . $numTabBotao . '" class="botaoSEI"><img src="imagens/sei_credenciais.gif" class="infraCorBarraSistema" alt="Processos com Credencial de Acesso nesta Unidade" title="Processos com Credencial de Acesso nesta Unidade"/></a>';
      }
    }
    
    foreach ($SEI_MODULOS as $seiModulo) {
      if (($arrRetBotaoIntegracao = $seiModulo->executar('montarBotaoControleProcessos')) != null) {
        foreach ($arrRetBotaoIntegracao as $strBotaoIntegracao) {
          $arrComandos[] = $strBotaoIntegracao;
        }
      }
    }

    $arrRetIconeIntegracao = ProcedimentoINT::montarIconesIntegracaoControleProcessos($arrObjProcedimentoDTO);

    if ($numRegistros > 0) {
      
      $strIdSigilosos = '';
      foreach ($arrObjProcedimentoDTO as $objProcedimentoDTO) {
        if ($objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_SIGILOSO) {
          if ($strIdSigilosos != '') {
            $strIdSigilosos .= ',';
          }
          $strIdSigilosos .= $objProcedimentoDTO->getDblIdProcedimento();
        }
      }

      $arrProcessosVisitados = SessaoSEI::getInstance()->getAtributo('PROCESSOS_VISITADOS_' . SessaoSEI::getInstance()->getStrSiglaUnidadeAtual());

      if ($strTipoVisualizacao == 'R') {

        $numCheckRecebidos = 0;
        $numCheckGerados = 0;

        $strRecebidos = '';
        $strGerados = '';


        $strResultadoRecebidos .= '<table id="tblProcessosRecebidos" border="0" cellspacing="0" cellpadding="1" class="infraTable tabelaControle" summary="Tabela de Processos Recebidos.">' . "\n";
        $strResultadoRecebidos .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela('', $numRegistrosRecebidos, '', 'Recebidos') . '</caption>';
        $strResultadoRecebidos .= '<tr>';
        $strResultadoRecebidos .= '<th class="tituloControle" width="5%" align="center">' . PaginaSEI::getInstance()->getThCheck('', 'Recebidos', '', false) . '</th>' . "\n";
        $strResultadoRecebidos .= '<th class="tituloControle">&nbsp;</th>' . "\n";
        $strResultadoRecebidos .= '<th class="tituloControle">Recebidos</th>' . "\n";
        $strResultadoRecebidos .= '<th class="tituloControle">&nbsp;</th>' . "\n";
        $strResultadoRecebidos .= '</tr>' . "\n";

        for ($i = 0; $i < $numRegistrosRecebidos; $i++) {

          $objProcedimentoDTO = $arrObjProcedimentoDTORecebidos[$i];

          $strImagemStatus = '';
          $strLinkUsuarioAtribuicao = '&nbsp;';
          $dblIdProcedimento = $objProcedimentoDTO->getDblIdProcedimento();

          ProcedimentoINT::processarControleProcessos($objProcedimentoDTO, $bolAcaoRegistrarAnotacao, $bolAcaoAndamentoSituacaoGerenciar, $bolAcaoAndamentoMarcadorGerenciar, $arrProcessosVisitados, $arrRetIconeIntegracao, $strImagemStatus, $strLinkUsuarioAtribuicao, $strLinkProcesso, $strTextoCheckBox);

          $strRecebidos .= '<tr id="P' . $dblIdProcedimento . '" class="infraTrClara" style="border:0px solid white;">' . "\n";

          $strRecebidos .= '<td align="center">';
          $strRecebidos .= PaginaSEI::getInstance()->getTrCheck($numCheckRecebidos++, $dblIdProcedimento, $strTextoCheckBox, 'N', 'Recebidos');
          $strRecebidos .= '</td>' . "\n";

          $strRecebidos .= '<td align="center">' . $strImagemStatus . '</td>' . "\n";
          $strRecebidos .= '<td align="center">' . $strLinkProcesso . '</td>' . "\n";
          $strRecebidos .= '<td align="center" width="10%">' . $strLinkUsuarioAtribuicao . '</td>' . "\n";
          $strRecebidos .= '</tr>' . "\n";
        }

        $strResultadoRecebidos .= $strRecebidos;
        $strResultadoRecebidos .= '</table>';

        $strResultadoGerados .= '<table id="tblProcessosGerados" border="0" cellspacing="0" cellpadding="1" class="infraTable tabelaControle" summary="Tabela de Processos Gerados.">' . "\n";
        $strResultadoGerados .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela('', $numRegistrosGerados, '', 'Gerados') . '</caption>';
        $strResultadoGerados .= '<tr>';
        $strResultadoGerados .= '<th class="tituloControle" width="5%" align="center">' . PaginaSEI::getInstance()->getThCheck('', 'Gerados', '', false) . '</th>' . "\n";
        $strResultadoGerados .= '<th class="tituloControle">&nbsp;</th>' . "\n";
        $strResultadoGerados .= '<th class="tituloControle">Gerados</th>' . "\n";
        $strResultadoGerados .= '<th class="tituloControle">&nbsp;</th>' . "\n";
        $strResultadoGerados .= '</tr>' . "\n";

        for ($i = 0; $i < $numRegistrosGerados; $i++) {

          $objProcedimentoDTO = $arrObjProcedimentoDTOGerados[$i];

          $strImagemStatus = '';
          $strLinkUsuarioAtribuicao = '';
          $dblIdProcedimento = $objProcedimentoDTO->getDblIdProcedimento();

          ProcedimentoINT::processarControleProcessos($objProcedimentoDTO, $bolAcaoRegistrarAnotacao, $bolAcaoAndamentoSituacaoGerenciar, $bolAcaoAndamentoMarcadorGerenciar, $arrProcessosVisitados, $arrRetIconeIntegracao, $strImagemStatus, $strLinkUsuarioAtribuicao, $strLinkProcesso, $strTextoCheckBox);

          $strGerados .= '<tr id="P' . $dblIdProcedimento . '" class="infraTrClara" style="border:0px solid white;">' . "\n";

          $strGerados .= '<td align="center">';
          $strGerados .= PaginaSEI::getInstance()->getTrCheck($numCheckGerados++, $dblIdProcedimento, $strTextoCheckBox, 'N', 'Gerados');
          $strGerados .= '</td>' . "\n";

          $strGerados .= '<td align="center">' . $strImagemStatus . '</td>' . "\n";
          $strGerados .= '<td align="center">' . $strLinkProcesso . '</td>' . "\n";
          $strGerados .= '<td align="center" width="10%">' . $strLinkUsuarioAtribuicao . '</td>' . "\n";
          $strGerados .= '</tr>' . "\n";
        }

        $strResultadoGerados .= $strGerados;
        $strResultadoGerados .= '</table>';

      } else {

        $numRegistrosDetalhado = $numRegistros;


        $strResultadoDetalhado .= '<table id="tblProcessosDetalhado" border="0" cellspacing="0" cellpadding="1" width="99%" class="infraTable tabelaControle" summary="Tabela de Processos.">' . "\n";

        $strResultadoDetalhado .= '<caption style="padding-bottom:.4em;" class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela('Processos', $numRegistrosDetalhado) . '</caption>';

        $strResultadoDetalhado .= '<tr>';
        $strResultadoDetalhado .= '<th class="tituloControle" width="5%" align="center">' . PaginaSEI::getInstance()->getThCheck('', 'Detalhado', '', false) . '</th>' . "\n";
        $strResultadoDetalhado .= '<th class="tituloControle">&nbsp;</th>' . "\n";
        $strResultadoDetalhado .= '<th class="tituloControle" width="18%">Processo</th>' . "\n";
        $strResultadoDetalhado .= '<th class="tituloControle" width="8%">&nbsp;</th>' . "\n";
        $strResultadoDetalhado .= '<th class="tituloControle" width="30%">Tipo</th>' . "\n";
        $strResultadoDetalhado .= '<th class="tituloControle">Interessados</th>' . "\n";

        $strResultadoDetalhado .= '</tr>' . "\n";

        $numCheck = 0;

        for ($i = 0; $i < $numRegistrosDetalhado; $i++) {

          $objProcedimentoDTO = $arrObjProcedimentoDTO[$i];
          $strImagemStatus = '';
          $strLinkUsuarioAtribuicao = '';
          $dblIdProcedimento = $objProcedimentoDTO->getDblIdProcedimento();

          ProcedimentoINT::processarControleProcessos($objProcedimentoDTO, $bolAcaoRegistrarAnotacao, $bolAcaoAndamentoSituacaoGerenciar, $bolAcaoAndamentoMarcadorGerenciar, $arrProcessosVisitados, $arrRetIconeIntegracao, $strImagemStatus, $strLinkUsuarioAtribuicao, $strLinkProcesso, $strTextoCheckBox);

          $strResultadoDetalhado .= '<tr id="P' . $dblIdProcedimento . '" class="infraTrClara">';

          $strResultadoDetalhado .= '<td align="center" valign="top">';
          $strResultadoDetalhado .= PaginaSEI::getInstance()->getTrCheck($numCheck++, $dblIdProcedimento, $strTextoCheckBox, 'N', 'Detalhado');
          $strResultadoDetalhado .= '</td>' . "\n";

          $strResultadoDetalhado .= '<td align="center" valign="top">' . $strImagemStatus . '</td>' . "\n";
          $strResultadoDetalhado .= '<td align="center" valign="top">' . $strLinkProcesso . '</td>' . "\n";
          $strResultadoDetalhado .= '<td align="left" valign="top">' . $strLinkUsuarioAtribuicao . '</td>' . "\n";
          $strResultadoDetalhado .= '<td align="left" valign="top">' . $objProcedimentoDTO->getStrNomeTipoProcedimento() . '</td>';

          $arrObjParticipanteDTO = $objProcedimentoDTO->getArrObjParticipanteDTO();

          $strParticipantes = '';
          $strParticipantesOcultos = '';
          if ($arrObjParticipanteDTO != null && $objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() != ProtocoloRN::$NA_SIGILOSO) {
            $numParticipantes = count($arrObjParticipanteDTO);
            for ($j = 0; $j < $numParticipantes; $j++) {
              if ($j > 0) {
                $strParticipantesOcultos .= '<div class="divItemCelula"><div class="divDiamante">&diams;&nbsp;&nbsp;' . '</div><div>' . $arrObjParticipanteDTO[$j]->getStrNomeContato();
                $strParticipantesOcultos .= '</div></div>';
              } else {
                $strParticipantes .= '<div class="divItemCelula"><div class="divDiamante">&diams;&nbsp;&nbsp;' . '</div>';
                $strParticipantes .= '<div><span class="spanItemCelula">' . $arrObjParticipanteDTO[$j]->getStrNomeContato() . '</span>';

                if ($numParticipantes > 1) {
                  $strParticipantes .= '&nbsp;<img src="imagens/ver_tudo.gif" id="imgVerTudo' . $i . '" onclick="exibirOcultarParticipantes(' . $i . ')" title="Ver Todos" />';
                }

                $strParticipantes .= '</div>';
                $strParticipantes .= '</div>';
              }
            }

            if ($j > 1) {
              $strParticipantes .= '<div>';
              $strParticipantes .= '<div id="divParticipantesOcultos' . $i . '" style="display:none;">';
              $strParticipantes .= $strParticipantesOcultos;
              $strParticipantes .= '<img src="imagens/ver_resumo.gif" id="imgVerResumo' . $i . '" onclick="exibirOcultarParticipantes(' . $i . ')" title="Ver Resumo"/>';
              $strParticipantes .= '</div>';
              $strParticipantes .= '</div>';
            }
          } else {
            $strParticipantes = '&nbsp;';
          }
          $strResultadoDetalhado .= '<td align="left"  valign="top">' . '<div>' . $strParticipantes . '</div>' . '</td>';
          $strResultadoDetalhado .= '</tr>' . "\n";
        }

        $strResultadoDetalhado .= '</table>';

        $strResultadoDetalhado = '<div id="divTabelaDetalhado">' . $strResultadoDetalhado . '</div>';
      }

    }
  }

  
  //$arrComandos[] = '<button type="button" accesskey="F" name="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'])).'\';" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';

  $strLinkMinhasPendencias = '<div id="divMeusProcessos">';
  if ($objPesquisaPendenciaDTO->getStrStaTipoAtribuicao()==AtividadeRN::$TA_MINHAS){
    $strLinkMinhasPendencias .= '<a id="ancVisualizacao1" href="javascript:void(0);" onclick="verProcessos(\''.AtividadeRN::$TA_TODAS.'\');" class="ancoraPadraoPreta" tabindex="'.PaginaSEI::getInstance()->getProxTabBarraComandosSuperior().'">Ver todos os processos</a>';
  }else{
    $strLinkMinhasPendencias .= '<a id="ancVisualizacao1" href="javascript:void(0);" onclick="verProcessos(\''.AtividadeRN::$TA_MINHAS.'\');" class="ancoraPadraoPreta" tabindex="'.PaginaSEI::getInstance()->getProxTabBarraComandosSuperior().'">Ver processos atribuídos a mim</a>';
  }
  $strLinkMinhasPendencias .= '</div>';

  $strLinkVerPorMarcadores = '<div id="divVerPorMarcadores">';

  if (!isset($_GET['ver_por_marcadores'])) {
    $strLinkVerPorMarcadores .= '<a id="ancVisualizacao2" href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_controlar&ver_por_marcadores=1').'" class="ancoraPadraoPreta" tabindex="'.PaginaSEI::getInstance()->getProxTabBarraComandosSuperior().'">Ver por marcadores</a>';
  }else{
    if ($objPesquisaPendenciaDTO->getStrStaTipoAtribuicao()==AtividadeRN::$TA_MINHAS){
      $strLinkVerPorMarcadores .= '<a id="ancVisualizacao2" href="javascript:void(0);" onclick="verProcessos(\''.AtividadeRN::$TA_MINHAS.'\');" class="ancoraPadraoPreta" tabindex="'.PaginaSEI::getInstance()->getProxTabBarraComandosSuperior().'">Ver processos atribuídos a mim</a>';
    }else{
      $strLinkVerPorMarcadores .= '<a id="ancVisualizacao2" href="javascript:void(0);" onclick="verProcessos(\''.AtividadeRN::$TA_TODAS.'\');" class="ancoraPadraoPreta" tabindex="'.PaginaSEI::getInstance()->getProxTabBarraComandosSuperior().'">Ver todos os processos</a>';
    }
  }

  if ($objMarcadorDTOFiltro!=null){
    $arrObjIconeMarcadorDTO = InfraArray::indexarArrInfraDTO($objMarcadorRN->listarValoresIcone(),'StaIcone');
    $strLinkVerPorMarcadores .= '&nbsp;<button type="button" id="btnLiberarMarcador" name="btnLiberarMarcador" onclick="filtrarMarcador(null)"  title="'.PaginaSEI::tratarHTML('Remover filtro pelo marcador "'.$objMarcadorDTOFiltro->getStrNome().'"').'" class="infraButton" tabindex="'.PaginaSEI::getInstance()->getProxTabBarraComandosSuperior().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensLocal().'/'.$arrObjIconeMarcadorDTO[$objMarcadorDTOFiltro->getStrStaIcone()]->getStrArquivo().'" class="infraImg" /><sup>&nbsp;x</sup></button>';
  }
  $strLinkVerPorMarcadores .= '</div>';

  $strDisplayTipoVisualizacao = '';
  if (!SessaoSEI::getInstance()->verificarPermissao('procedimento_controlar_visualizacao')){
    $strDisplayTipoVisualizacao = 'display:none;';
  }

  $strLinkTipoVisualizacao = '<div id="divTipoVisualizacao" style="'.$strDisplayTipoVisualizacao.'">';
  if ($strTipoVisualizacao=='R'){
    $strLinkTipoVisualizacao .= '<a id="ancTipoVisualizacao" href="javascript:void(0);" onclick="mudarVisualizacao(\'D\');" class="ancoraPadraoPreta" tabindex="'.PaginaSEI::getInstance()->getProxTabBarraComandosSuperior().'">Visualização detalhada</a>';;
  }else{
    $strLinkTipoVisualizacao .= '<a id="ancTipoVisualizacao" href="javascript:void(0);" onclick="mudarVisualizacao(\'R\');" class="ancoraPadraoPreta" tabindex="'.PaginaSEI::getInstance()->getProxTabBarraComandosSuperior().'">Visualização resumida</a>';;
  }
  $strLinkTipoVisualizacao .= '</div>';
  
  $strLinkBlocoPesquisaSelecao = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=bloco_selecionar_processo&tipo_selecao=1&id_object=objLupaBlocoPesquisa');

  //$numSeg = InfraUtil::verificarTempoProcessamento($numSeg);
  //InfraDebug::getInstance()->gravar($numSeg.' s');
  
}catch(Exception $e){
  PaginaSEI::getInstance()->processarExcecao($e);
} 

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema().' - '.$strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
?>

#divFiltro {height:3.5em;}

#divMeusProcessos {position:absolute;left:0%;top:20%;}
#divVerPorMarcadores {position:absolute;left:30%;top:20%;}

#tblMarcadores img {cursor:default;}

#btnLiberarMarcador {
  height:2em;
  cursor:pointer;
  -moz-border-radius: 6px;
  border-radius: 6px;
  -webkit-border-radius: 6px;
  border: 1px solid #CAD8F3;
  background-color: #DEE7F8;
}

#btnLiberarMarcador sup {
  font-size:1.2em;
  font-weight:bold;
}

#btnLiberarMarcador img {float:left}

#divTipoVisualizacao {position:absolute;left:60%;top:20%;}

#divMeusProcessos a,#divVerPorMarcadores a,#divTipoVisualizacao a {line-height:2.2em}

#tblMarcadores th {line-height:1.4em}

#imgRecebidosCheck, #imgGeradosCheck {height:16px;width:16px;}


table.tabelaControle {
background-color:white;
border:0px solid white;
border-spacing:0;
}

table.tabelaControle tr{
margin:0;
border:0;
padding:0;
}

table.tabelaControle a{
text-decoration:none;
vertical-align:middle;
}

table.tabelaControle a:hover{
text-decoration:underline;
}

th.tituloControle{
font-size:1em;
font-weight: bold;
text-align: center;
color: #000;
background-color: #dfdfdf;
border-spacing: 0;
}

td a{
  font-size:1.2em;
}

#divRecebidos, #divGerados {
width:48% !important;
}

#divRecebidos {
float:left;
}

#divGerados {
float:right;
}

#divRecebidos table, #divGerados table {
 width:92%;
}

#divTabelaDetalhado {
padding:0;
margin:0;
margin:0;
float:left;
display:inline;
width:99%;
}

#divTabelaDetalhado table{
width:100%;
}

#tblProcessosDetalhado td{
border-bottom:1px dotted #666;
padding:.3em;
}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
//PaginaSEI::getInstance()->abrirJavaScript();
?>
<script type="text/javascript" charset="iso-8859-1" >
<!--//--><![CDATA[//><!--

var objLupaBlocoPesquisa = null;
var bolCarregando = true;

function inicializar(){

  infraExibirMenuSistemaEsquema();

  if ('<?=$strLinkNovidades?>' != ''){
    objJanelaNovidadesSEI = infraAbrirJanela('<?=$strLinkNovidades?>','JanelaNovidades<?=str_replace('-','_',SessaoSEI::getInstance()->getStrSiglaSistema())?>',500,300,'location=0,status=0,resizable=1,scrollbars=1',false);
    objJanelaNovidadesSEI.focus();
  }
  
	objLupaBlocoPesquisa = new infraLupaText('txtBloco','hdnIdBloco','<?=$strLinkBlocoPesquisaSelecao?>');
	objLupaBlocoPesquisa.finalizarSelecao = function(){
    document.getElementById('frmProcedimentoControlar').action = '<?=$strLinkIncluirEmBloco?>';
    document.getElementById('frmProcedimentoControlar').submit();
	};

  infraEfeitoTabelas();
}

function acaoPendenciaMultipla(bolMsg){
  if (document.getElementById('hdnTipoVisualizacao').value == 'R'){
    if (document.getElementById('hdnGeradosItensSelecionados').value=='' && document.getElementById('hdnRecebidosItensSelecionados').value==''){
      if (bolMsg){
        alert('Nenhum processo selecionado.');
      }
      return false;
    }
    document.getElementById('hdnGeradosItemId').value = ''; 
    document.getElementById('hdnRecebidosItemId').value = ''; 
    
  }else{
    if (document.getElementById('hdnDetalhadoItensSelecionados').value==''){
      if (bolMsg){
        alert('Nenhum processo selecionado.');
      }
      return false;
    }
    document.getElementById('hdnDetalhadoItemId').value = ''; 
  }
  
  return true;
}

function acaoControleProcessos(link, requerSelecionado, aceitaSigiloso){
  if ((!requerSelecionado || acaoPendenciaMultipla(true)) && (aceitaSigiloso || !bloquearSigilosoSelecionado())){
    document.getElementById('frmProcedimentoControlar').action = link;
    document.getElementById('frmProcedimentoControlar').submit();
  }
}

function acaoBlocoProcessar(){
  if (acaoPendenciaMultipla(true) && !bloquearSigilosoSelecionado()){
    document.getElementById('txtBloco').value = '';
    document.getElementById('hdnIdBloco').value = '';
    objLupaBlocoPesquisa.selecionar(700,500);
  }
}

function bloquearSigilosoSelecionado(){

  var sigilosos = document.getElementById('hdnIdSigilosos').value;

  if (sigilosos!='') {

    selecionados = '';

    if (document.getElementById('hdnTipoVisualizacao').value=='R') {

      if (document.getElementById('hdnGeradosItensSelecionados').value!='') {
        selecionados = document.getElementById('hdnGeradosItensSelecionados').value;
      }

      if (document.getElementById('hdnRecebidosItensSelecionados').value!='') {
        if (selecionados!='') {
          selecionados += ',';
        }
        selecionados += document.getElementById('hdnRecebidosItensSelecionados').value;
      }

    } else {
      selecionados = document.getElementById('hdnDetalhadoItensSelecionados').value;
    }

    if (selecionados!='') {

      sigilosos = sigilosos.split(',');
      selecionados = selecionados.split(',');

      for (var i = 0; i<sigilosos.length; i++) {
        for (var j = 0; j<selecionados.length; j++) {
          if (sigilosos[i]==selecionados[j]) {
            alert('Operação não aplicável em processo sigiloso.');
            return true;
          }
        }
      }
    }
  }
  return false;
}

function listarCredenciais(){
  infraAbrirJanela('<?=$strLinkCredencialAcessar?>','janelaAcessoCredencial',500,350,'location=0,status=1,resizable=1,scrollbars=1');
}

function verProcessos(valor){
  document.getElementById('hdnMeusProcessos').value = valor;
  document.getElementById('frmProcedimentoControlar').submit();
}

function mudarVisualizacao(valor){
  document.getElementById('hdnTipoVisualizacao').value = valor;
  document.getElementById('frmProcedimentoControlar').submit();
}

function filtrarMarcador(marcador){
  document.getElementById('<?=$strIdHdnMarcador?>').value = marcador;
  document.getElementById('frmProcedimentoControlar').submit();
}

function exibirOcultarParticipantes(id){ 
	if(document.getElementById('divParticipantesOcultos'+id).style.display != "block"){ 
		
		document.getElementById('divParticipantesOcultos'+id).style.display = "block"; 
		document.getElementById('imgVerTudo'+id).style.display = "none"; 
		document.getElementById('imgVerResumo'+id).style.display = "block"; 
		
	}else{ 
		document.getElementById('divParticipantesOcultos'+id).style.display = "none";
		document.getElementById('imgVerTudo'+id).style.display = "inline"; 
		document.getElementById('imgVerResumo'+id).style.display = "none"; 
	} 
	infraProcessarResize();
}

//--><!]]>
</script>
<?
//PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmProcedimentoControlar" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <div id="divComandos" class="infraBarraComandos" style="text-align:left;">
<?
  foreach($arrComandos as $comando){
    echo $comando;
  }
?>
  </div>
  <div id="divFiltro" class="infraAreaDados">
    <?=$strLinkMinhasPendencias?>

    <?=$strLinkVerPorMarcadores?>

    <?=$strLinkTipoVisualizacao?>
  </div>
  <?

  if (isset($_GET['ver_por_marcadores'])){

    echo '<br /><br />';
    PaginaSEI::getInstance()->montarAreaTabela($strResultadoMarcadores, $numRegistrosMarcadores, true);

  }else {


    if ($strTipoVisualizacao == 'R') {

      echo '<div id="divRecebidos" style="width:50%">' . "\n";
      PaginaSEI::getInstance()->montarAreaTabela($strResultadoRecebidos, $numRegistrosRecebidos, false, '', null, 'Recebidos');
      echo '</div>' . "\n";

      echo '<div id="divGerados" style="width:50%">' . "\n";
      PaginaSEI::getInstance()->montarAreaTabela($strResultadoGerados, $numRegistrosGerados, false, '', null, 'Gerados');
      echo '</div>' . "\n";

    } else {
      PaginaSEI::getInstance()->montarAreaTabela($strResultadoDetalhado, $numRegistros);
    }

  }
  PaginaSEI::getInstance()->montarAreaDebug();
  //PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
  
  <input type="hidden" id="hdnMeusProcessos" name="hdnMeusProcessos" value="<?=$objPesquisaPendenciaDTO->getStrStaTipoAtribuicao()?>" />
  <input type="hidden" id="hdnTipoVisualizacao" name="hdnTipoVisualizacao" value="<?=$strTipoVisualizacao?>" />
  <input type="hidden" id="hdnIdBloco" name="hdnIdBloco" value="" />
  <input type="text" id="txtBloco" name="txtBloco" value=""  style="display:none"/>
  <input type="hidden" id="hdnIdSigilosos" value="<?=$strIdSigilosos?>" />
  <input type="hidden" id="<?=$strIdHdnMarcador?>" name="<?=$strIdHdnMarcador?>" value="<?=$numIdMarcadorFiltro?>" />
  <br />
  <br />
</form>
<? 
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>