<?
require_once dirname(__FILE__).'/../SEI.php';

class SolrProtocolo {
	
	public static function executar(PesquisaProtocoloSolrDTO $objPesquisaProtocoloSolrDTO)
  {

    //die($objPesquisaProtocoloSolrDTO->__toString());

    $partialfields = '';
    $bolArvore = $objPesquisaProtocoloSolrDTO->getBolArvore();

    //somente adiciona filtro por sta_prot se buscou apenas gerados ou recebidos
    if ($objPesquisaProtocoloSolrDTO->getStrSinDocumentosGerados() != $objPesquisaProtocoloSolrDTO->getStrSinDocumentosRecebidos()) {

      $arrStaProtocolo = array();

      array_push($arrStaProtocolo, "sta_prot:" . ProtocoloRN::$TP_PROCEDIMENTO);

      if ($objPesquisaProtocoloSolrDTO->getStrSinDocumentosGerados() == 'S') {
        array_push($arrStaProtocolo, "sta_prot:" . ProtocoloRN::$TP_DOCUMENTO_GERADO);
      }

      if ($objPesquisaProtocoloSolrDTO->getStrSinDocumentosRecebidos() == 'S') {
        array_push($arrStaProtocolo, "sta_prot:" . ProtocoloRN::$TP_DOCUMENTO_RECEBIDO);
      }

      if (count($arrStaProtocolo) > 0) {
        $partialfields .= '(' . implode(" OR ", $arrStaProtocolo) . ')';
      }

    }

    if ($objPesquisaProtocoloSolrDTO->getStrSinProcessosTramitacao() == 'S') {
      if ($partialfields != '') {
        $partialfields .= ' AND ';
      }

      $partialfields .= '(id_uni_tram:*;' . SessaoSEI::getInstance()->getNumIdUnidadeAtual() . ';*)';
    }

    if ($objPesquisaProtocoloSolrDTO->getNumIdUnidadeGeradora() == null) {

      $arr = array();
      foreach ($objPesquisaProtocoloSolrDTO->getArrNumIdOrgao() as $numIdOrgao) {
        array_push($arr, "id_org_ger:" . $numIdOrgao);
      }

      if (count($arr) > 0) {
        if ($partialfields != '') {
          $partialfields .= ' AND ';
        }
        $partialfields .= '(' . implode(" OR ", $arr) . ')';
      }

    } else {

      if ($partialfields != '') {
        $partialfields .= ' AND ';
      }

      $partialfields .= '(id_uni_ger:' . $objPesquisaProtocoloSolrDTO->getNumIdUnidadeGeradora() . ')';
    }


    if ($objPesquisaProtocoloSolrDTO->getNumIdContato() != null) {

      $objUsuarioDTO = new UsuarioDTO();
      $objUsuarioDTO->setNumIdContato($objPesquisaProtocoloSolrDTO->getNumIdContato());

      $objUsuarioRN = new UsuarioRN();
      $arrIdContato = array_unique(InfraArray::converterArrInfraDTO($objUsuarioRN->obterUsuariosRelacionados($objUsuarioDTO),'IdContato'));

      if (count($arrIdContato) == 0) {
        $arrIdContato[] = $objPesquisaProtocoloSolrDTO->getNumIdContato();
      }

      $arrContatos = array();

      foreach($arrIdContato as $numIdParticpante) {
        if ($objPesquisaProtocoloSolrDTO->getStrSinInteressado() == 'S') {
          array_push($arrContatos, 'id_int:*;' . $numIdParticpante . ';*');
        }
      }

      foreach($arrIdContato as $numIdParticpante) {
        if ($objPesquisaProtocoloSolrDTO->getStrSinRemetente() == 'S') {
          array_push($arrContatos, 'id_rem:*;' . $numIdParticpante . ';*');
        }
      }

      foreach($arrIdContato as $numIdParticpante){
        if ($objPesquisaProtocoloSolrDTO->getStrSinDestinatario() == 'S') {
          array_push($arrContatos, 'id_dest:*;' . $numIdParticpante . ';*');
        }
      }

      if (count($arrContatos) > 0) {

        if ($partialfields != '') {
          $partialfields .= ' AND ';
        }

        $partialfields .= '(' . implode(" OR ", $arrContatos) . ')';
      }
    }

    if ($objPesquisaProtocoloSolrDTO->getNumIdAssinante() != null) {

      $objUsuarioDTO = new UsuarioDTO();
      $objUsuarioDTO->setNumIdContato($objPesquisaProtocoloSolrDTO->getNumIdAssinante());

      $objUsuarioRN = new UsuarioRN();
      $arrIdUsuario = array_unique(InfraArray::converterArrInfraDTO($objUsuarioRN->obterUsuariosRelacionados($objUsuarioDTO),'IdUsuario'));

      if (count($arrIdUsuario)) {

        $arrContatos = array();

        foreach ($arrIdUsuario as $numIdAssinante) {
          array_push($arrContatos, 'id_assin:*;' . $numIdAssinante . ';*');
        }

        if (count($arrContatos) > 0) {

          if ($partialfields != '') {
            $partialfields .= ' AND ';
          }

          $partialfields .= '(' . implode(" OR ", $arrContatos) . ')';
        }
      }
    }

    if ($objPesquisaProtocoloSolrDTO->getStrDescricao() != null) {
      if ($partialfields != '') {
        $partialfields .= ' AND ';
      }
      $partialfields .= '(' . SolrUtil::formatarOperadores($objPesquisaProtocoloSolrDTO->getStrDescricao(), 'desc') . ')';
    }

    if ($objPesquisaProtocoloSolrDTO->getStrObservacao() != null) {
      if ($partialfields != '') {
        $partialfields .= ' AND ';
      }
      $partialfields .= '(' . SolrUtil::formatarOperadores($objPesquisaProtocoloSolrDTO->getStrObservacao(), 'obs_' . SessaoSEI::getInstance()->getNumIdUnidadeAtual()) . ')';
    }

    if ($objPesquisaProtocoloSolrDTO->getDblIdProcedimento() != null) {
      if ($partialfields != '') {
        $partialfields .= ' AND ';
      }

      $objRelProtocoloProtocoloDTO 	= new RelProtocoloProtocoloDTO();
      $objRelProtocoloProtocoloDTO->retDblIdProtocolo2();
      $objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);
      $objRelProtocoloProtocoloDTO->setDblIdProtocolo1($objPesquisaProtocoloSolrDTO->getDblIdProcedimento());

      $objRelProtocoloProtocoloRN 	= new RelProtocoloProtocoloRN();
      $arrIdProcessosAnexados = InfraArray::converterArrInfraDTO($objRelProtocoloProtocoloRN->listarRN0187($objRelProtocoloProtocoloDTO),'IdProtocolo2');

      if (count($arrIdProcessosAnexados)==0) {
        $partialfields .= '(id_proc:' . $objPesquisaProtocoloSolrDTO->getDblIdProcedimento() . ')';
      }else{

        $strProcessos = 'id_proc:' . $objPesquisaProtocoloSolrDTO->getDblIdProcedimento();
        foreach($arrIdProcessosAnexados as $dblIdProcessoAnexado){
          $strProcessos .= ' OR id_proc:'.$dblIdProcessoAnexado;
        }

        $partialfields .= '('.$strProcessos.')';
      }
    }

    if ($objPesquisaProtocoloSolrDTO->getNumIdAssunto() != null) {

      $objAssuntoProxyDTO = new AssuntoProxyDTO();
      $objAssuntoProxyDTO->retNumIdAssuntoProxy();
      $objAssuntoProxyDTO->setNumIdAssunto($objPesquisaProtocoloSolrDTO->getNumIdAssunto());

      $objAssuntoProxyRN = new AssuntoProxyRN();
      $arrObjAssuntoProxyDTO = $objAssuntoProxyRN->listar($objAssuntoProxyDTO);

      if ($partialfields != '') {
        $partialfields .= ' AND ';
      }

      $arrAssuntos = array();
      foreach($arrObjAssuntoProxyDTO as $objAssuntoProxyDTO){
        array_push($arrAssuntos, 'id_assun:*;' . $objAssuntoProxyDTO->getNumIdAssuntoProxy() . ';*');
      }

      $partialfields .= '(' . implode(" OR ", $arrAssuntos) . ')';
    }

    if ($objPesquisaProtocoloSolrDTO->getStrProtocoloPesquisa() != null) {
      if ($partialfields != '') {
        $partialfields .= ' AND ';
      }
      $partialfields .= '(prot_pesq:*' . InfraUtil::retirarFormatacao($objPesquisaProtocoloSolrDTO->getStrProtocoloPesquisa(),false) . '*)';
    }

    if ($objPesquisaProtocoloSolrDTO->getNumIdTipoProcedimento() != null) {
      if ($partialfields != '') {
        $partialfields .= ' AND ';
      }
      $partialfields .= '(id_tipo_proc:' . $objPesquisaProtocoloSolrDTO->getNumIdTipoProcedimento() . ')';
    }

    if ($objPesquisaProtocoloSolrDTO->getNumIdSerie() != null) {
      if ($partialfields != '') {
        $partialfields .= ' AND ';
      }

      $arrSeriesPesquisa = $objPesquisaProtocoloSolrDTO->getNumIdSerie();
      if (!is_array($arrSeriesPesquisa)){
        $arrSeriesPesquisa = array($arrSeriesPesquisa);
      }

      $arrSeriesFiltro = array();
      foreach($arrSeriesPesquisa as $numIdSerie){
        array_push($arrSeriesFiltro, '(id_serie:' . $numIdSerie . ')');
      }

      $partialfields .= '(' . implode(" OR ", $arrSeriesFiltro) . ')';
    }

    if ($objPesquisaProtocoloSolrDTO->getStrNumero() != null) {
      if ($partialfields != '') {
        $partialfields .= ' AND ';
      }
      $partialfields .= '(numero:*' . $objPesquisaProtocoloSolrDTO->getStrNumero() . '*)';
    }

    $dtaInicio = null;
    $dtaFim = null;
    if ($objPesquisaProtocoloSolrDTO->getStrStaTipoData() == '0') {
      $dtaInicio = $objPesquisaProtocoloSolrDTO->getDtaInicio();
      $dtaFim = $objPesquisaProtocoloSolrDTO->getDtaFim();
    } else if ($objPesquisaProtocoloSolrDTO->getStrStaTipoData() == '30') {
      $dtaInicio = InfraData::calcularData(30, InfraData::$UNIDADE_DIAS, InfraData::$SENTIDO_ATRAS);
      $dtaFim = InfraData::getStrDataAtual();
    } else if ($objPesquisaProtocoloSolrDTO->getStrStaTipoData() == '60') {
      $dtaInicio = InfraData::calcularData(60, InfraData::$UNIDADE_DIAS, InfraData::$SENTIDO_ATRAS);
      $dtaFim = InfraData::getStrDataAtual();
    }


    if ($dtaInicio != null && $dtaFim != null) {
      $dia1 = substr($dtaInicio, 0, 2);
      $mes1 = substr($dtaInicio, 3, 2);
      $ano1 = substr($dtaInicio, 6, 4);

      $dia2 = substr($dtaFim, 0, 2);
      $mes2 = substr($dtaFim, 3, 2);
      $ano2 = substr($dtaFim, 6, 4);

      if ($partialfields != '') {
        $partialfields .= ' AND ';
      }

      $partialfields .= 'dta_ger:[' . $ano1 . '-' . $mes1 . '-' . $dia1 . 'T00:00:00Z TO ' . $ano2 . '-' . $mes2 . '-' . $dia2 . 'T00:00:00Z]';
    }


    $arrUsuarioGerador = array();

    if ($objPesquisaProtocoloSolrDTO->getNumIdUsuarioGerador1() != null) {
      array_push($arrUsuarioGerador, "id_usu_ger:" . $objPesquisaProtocoloSolrDTO->getNumIdUsuarioGerador1());
    }

    if ($objPesquisaProtocoloSolrDTO->getNumIdUsuarioGerador2() != null) {
      array_push($arrUsuarioGerador, "id_usu_ger:" . $objPesquisaProtocoloSolrDTO->getNumIdUsuarioGerador2());
    }

    if ($objPesquisaProtocoloSolrDTO->getNumIdUsuarioGerador3() != null) {
      array_push($arrUsuarioGerador, "id_usu_ger:" . $objPesquisaProtocoloSolrDTO->getNumIdUsuarioGerador3());
    }

    if (count($arrUsuarioGerador) > 0) {
      if ($partialfields != '') {
        $partialfields .= ' AND ';
      }

      $partialfields .= '(' . implode(" OR ", $arrUsuarioGerador) . ')';
    }

    $objUnidadeDTO = new UnidadeDTO();
    $objUnidadeDTO->setBolExclusaoLogica(false);
    $objUnidadeDTO->retStrSinProtocolo();
    $objUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

    $objUnidadeRN = new UnidadeRN();
    $objUnidadeDTOAtual = $objUnidadeRN->consultarRN0125($objUnidadeDTO);

    if ($objUnidadeDTOAtual->getStrSinProtocolo() == 'N') {

      if ($partialfields != '') {
        $partialfields .= ' AND ';
      }

      $partialfields .= '(tipo_aces:P OR id_uni_aces:*;' . SessaoSEI::getInstance()->getNumIdUnidadeAtual() . ';*)';
    }

    $parametros = new stdClass();
    $parametros->q = SolrUtil::formatarOperadores($objPesquisaProtocoloSolrDTO->getStrPalavrasChave());

    if (is_numeric($objPesquisaProtocoloSolrDTO->getStrPalavrasChave()) && $objPesquisaProtocoloSolrDTO->getStrProtocoloPesquisa()==null){
      $parametros->q = '('.$parametros->q.' OR prot_pesq:*'.$objPesquisaProtocoloSolrDTO->getStrPalavrasChave().'*)';
    }

    if ($parametros->q != '' && $partialfields != '') {
      $parametros->q = '(' . $parametros->q . ') AND ' . $partialfields;
    } else if ($partialfields != '') {
      $parametros->q = $partialfields;
    }

    $parametros->q = utf8_encode($parametros->q);
    $parametros->start = $objPesquisaProtocoloSolrDTO->getNumInicioPaginacao();
    $parametros->rows = 10;
    $parametros->sort = 'dta_ger desc, id_prot desc';

    $urlBusca = ConfiguracaoSEI::getInstance()->getValor('Solr', 'Servidor') . '/' . ConfiguracaoSEI::getInstance()->getValor('Solr', 'CoreProtocolos') . '/select?' . http_build_query($parametros) . '&hl=true&hl.snippets=2&hl.fl=content&hl.fragsize=100&hl.maxAnalyzedChars=1048576&hl.alternateField=content&hl.maxAlternateFieldLength=100&fl=id,id_proc,id_doc,id_tipo_proc,id_serie,id_anexo,id_uni_ger,prot_doc,prot_proc,numero,id_usu_ger,dta_ger';

    //InfraDebug::getInstance()->setBolLigado(true);
    //InfraDebug::getInstance()->gravar('URL:'.$urlBusca);
    //InfraDebug::getInstance()->gravar("PARÂMETROS: " . print_r($parametros, true));

    try {
      $resultados = file_get_contents($urlBusca, false);
    }catch(Exception $e){
      throw new InfraException('Erro realizando pesquisa.',$e, urldecode($urlBusca),false);
    }

    if ($resultados == '') {
      throw new InfraException('Nenhum retorno encontrado no resultado da pesquisa.');
    }

    $xml = simplexml_load_string($resultados);

    $html = '';

    $arrRet = $xml->xpath('/response/result/@numFound');

    $itens = array_shift($arrRet);

    if ($itens == 0) {

      $html .= "<div class=\"sem-resultado\">";
      $html .= "Sua pesquisa pelo termo <b>" . PaginaSEI::tratarHTML($_POST["q"]) . "</b> não encontrou nenhum protocolo correspondente.";
      $html .= "<br/>";
      $html .= "<br/>";
      $html .= "Sugestões:";
      $html .= "<ul>";
      $html .= "<li>Certifique-se de que todas as palavras estejam escritas corretamente.</li>";
      $html .= "<li>Tente palavras-chave diferentes.</li>";
      $html .= "<li>Tente palavras-chave mais genéricas.</li>";
      $html .= "</ul>";
      $html .= "</div>";

    } else if ($itens == 1) {

      $dblIdProcedimento = $xml->xpath("//long[@name='id_proc']");
      if (is_array($dblIdProcedimento)) {
        $dblIdProcedimento = $dblIdProcedimento[0];

        $strLinkArvore = 'controlador.php?acao=procedimento_trabalhar&acao_origem=protocolo_pesquisar&id_procedimento=' . $dblIdProcedimento;

        $dblIdDocumento = $xml->xpath("//long[@name='id_doc']");
        if (is_array($dblIdDocumento)) {
          $dblIdDocumento = $dblIdDocumento[0];
          $strLinkArvore .= '&id_documento=' . $dblIdDocumento;
        }

        if (!$bolArvore) {
          header("Location: " . SessaoSEI::getInstance()->assinarLink($strLinkArvore));
          die;
        } else {
          $strParametros = '&id_procedimento=' . $dblIdProcedimento . '&id_documento=' . $dblIdDocumento;
          $strRetorno = '<script type="text/javascript" charset="iso-8859-1">';
          $strRetorno .= 'window.parent.document.getElementById("ifrArvore").src = "' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_visualizar&acao_origem=' . $_GET['acao'] . $strParametros . '&montar_visualizacao=1') . '";';
          $strRetorno .= '</script>';
          return $strRetorno;
        }

      }

    } else {

      $html = SolrUtil::criarBarraEstatisticas($itens, $parametros->start, ($parametros->start + $parametros->rows));

      $registros = $xml->xpath('/response/result/doc');

      $numRegistros = sizeof($registros);

      $arrRegistros = array();
      $arrIdTipoProcedimento = array();
      $arrIdUnidadeGeradora = array();
      $arrIdUsuarioGerador = array();
      $arrIdSerie = array();

      for ($i = 0; $i < $numRegistros; $i++) {

        $regResultado = $registros[$i];

        $arrRegistros[$i] = array(
            'id' => SolrUtil::obterTag($regResultado, 'id', 'str'),
            'id_proc' => SolrUtil::obterTag($regResultado, 'id_proc', 'long'),
            'id_doc' => SolrUtil::obterTag($regResultado, 'id_doc', 'long'),
            'id_anexo' => SolrUtil::obterTag($regResultado, 'id_anexo', 'int'),
            'id_uni_ger' => SolrUtil::obterTag($regResultado, 'id_uni_ger', 'int'),
            'id_usu_ger' => SolrUtil::obterTag($regResultado, 'id_usu_ger', 'int'),
            'id_tipo_proc' => SolrUtil::obterTag($regResultado, 'id_tipo_proc', 'int'),
            'id_serie' => SolrUtil::obterTag($regResultado, 'id_serie', 'int'),
            'numero' => SolrUtil::obterTag($regResultado, 'numero', 'str'),
            'prot_doc' => SolrUtil::obterTag($regResultado, 'prot_doc', 'str'),
            'prot_proc' => SolrUtil::obterTag($regResultado, 'prot_proc', 'str')
        );

        $arrIdTipoProcedimento[$arrRegistros[$i]["id_tipo_proc"]] = 0;

        if ($arrRegistros[$i]["id_serie"] != null) {
          $arrIdSerie[$arrRegistros[$i]["id_serie"]] = 0;
        }

        $arrIdUnidadeGeradora[$arrRegistros[$i]["id_uni_ger"]] = 0;
        $arrIdUsuarioGerador[$arrRegistros[$i]["id_usu_ger"]] = 0;
      }

      $arrObjTipoProcedimentoDTO = null;
      if (count($arrIdTipoProcedimento)) {
        $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
        $objTipoProcedimentoDTO->setBolExclusaoLogica(false);
        $objTipoProcedimentoDTO->retNumIdTipoProcedimento();
        $objTipoProcedimentoDTO->retStrNome();
        $objTipoProcedimentoDTO->setNumIdTipoProcedimento(array_keys($arrIdTipoProcedimento), InfraDTO::$OPER_IN);

        $objTipoProcedimentoRN = new TipoProcedimentoRN();
        $arrObjTipoProcedimentoDTO = InfraArray::indexarArrInfraDTO($objTipoProcedimentoRN->listarRN0244($objTipoProcedimentoDTO), 'IdTipoProcedimento');
      }

      $arrObjSerieDTO = null;
      if (count($arrIdSerie)) {
        $objSerieDTO = new SerieDTO();
        $objSerieDTO->setBolExclusaoLogica(false);
        $objSerieDTO->retNumIdSerie();
        $objSerieDTO->retStrNome();
        $objSerieDTO->setNumIdSerie(array_keys($arrIdSerie), InfraDTO::$OPER_IN);

        $objSerieRN = new SerieRN();
        $arrObjSerieDTO = InfraArray::indexarArrInfraDTO($objSerieRN->listarRN0646($objSerieDTO), 'IdSerie');
      }

      $arrObjUnidadeDTOGeradora = null;
      if (count($arrIdUnidadeGeradora)) {
        $objUnidadeDTO = new UnidadeDTO();
        $objUnidadeDTO->setBolExclusaoLogica(false);
        $objUnidadeDTO->retNumIdUnidade();
        $objUnidadeDTO->retStrSigla();
        $objUnidadeDTO->retStrDescricao();
        $objUnidadeDTO->setNumIdUnidade(array_keys($arrIdUnidadeGeradora), InfraDTO::$OPER_IN);

        $objUnidadeRN = new UnidadeRN();
        $arrObjUnidadeDTOGeradora = InfraArray::indexarArrInfraDTO($objUnidadeRN->listarRN0127($objUnidadeDTO), 'IdUnidade');
      }

      $arrObjUsuarioDTOGerador = null;
      if (count($arrIdUsuarioGerador)) {
        $objUsuarioDTO = new UsuarioDTO();
        $objUsuarioDTO->setBolExclusaoLogica(false);
        $objUsuarioDTO->retNumIdUsuario();
        $objUsuarioDTO->retStrSigla();
        $objUsuarioDTO->retStrNome();
        $objUsuarioDTO->setNumIdUsuario(array_keys($arrIdUsuarioGerador), InfraDTO::$OPER_IN);

        $objUsuarioRN = new UsuarioRN();
        $arrObjUsuarioDTOGerador = InfraArray::indexarArrInfraDTO($objUsuarioRN->listarRN0490($objUsuarioDTO), 'IdUsuario');
      }


      for ($i = 0; $i < $numRegistros; $i++) {

        $dados = $arrRegistros[$i];

        $strNomeTipoProcedimento = '';
        if (isset($arrObjTipoProcedimentoDTO[$dados['id_tipo_proc']])) {
          $strNomeTipoProcedimento = $arrObjTipoProcedimentoDTO[$dados['id_tipo_proc']]->getStrNome();
        } else {
          $strNomeTipoProcedimento = '[tipo de processo não encontrado]';
        }

        $strNomeSerie = '';
        if (isset($arrObjSerieDTO[$dados['id_serie']])) {
          $strNomeSerie = $arrObjSerieDTO[$dados['id_serie']]->getStrNome();
        } else {
          $strNomeSerie = '[tipo de documento não encontrado]';
        }

        if (isset($arrObjUnidadeDTOGeradora[$dados['id_uni_ger']])) {
          $strSiglaUnidadeGeradora = $arrObjUnidadeDTOGeradora[$dados['id_uni_ger']]->getStrSigla();
          $strDescricaoUnidadeGeradora = $arrObjUnidadeDTOGeradora[$dados['id_uni_ger']]->getStrDescricao();
        } else {
          $strSiglaUnidadeGeradora = '[unidade não encontrada]';
          $strDescricaoUnidadeGeradora = '[unidade não encontrada]';
        }

        if (isset($arrObjUsuarioDTOGerador[$dados['id_usu_ger']])) {
          $strSiglaUsuarioGerador = $arrObjUsuarioDTOGerador[$dados['id_usu_ger']]->getStrSigla();
          $strNomeUsuarioGerador = $arrObjUsuarioDTOGerador[$dados['id_usu_ger']]->getStrNome();
        } else {
          $strSiglaUsuarioGerador = '[usuário não encontrado]';
          $strNomeUsuarioGerador = '[usuário não encontrado]';
        }

        $titulo = PaginaSEI::tratarHTML($strNomeTipoProcedimento . " Nº " . $dados["prot_proc"]);

        if ($dados['id_doc'] != null) {

          $titulo .= ' (<a onclick="infraLimparFormatarTrAcessada(this.parentNode.parentNode);" target="_blank" href="';

          if ($dados['id_anexo'] == null) {
            $titulo .= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_visualizar&acao_origem=protocolo_pesquisar&id_documento=' . $dados['id_doc']);
          } else {
            $titulo .= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_download_anexo&acao_origem=protocolo_pesquisar&id_anexo=' . $dados['id_anexo']);
          }

          $strIdendificacaoDocumento = $strNomeSerie . ($dados['numero'] != null ? ' ' . $dados['numero'] : '');

          $titulo .= '" title="' . PaginaSEI::tratarHTML($strIdendificacaoDocumento) . '" class="protocoloNormal">';
          $titulo .= PaginaSEI::tratarHTML($strIdendificacaoDocumento);
          $titulo .= "</a>)";
        }

        $tituloProtocolo = $dados["prot_doc"];

        $arrMetatags = array();
        $arrMetatags['Unidade Geradora'] = '<a alt="' . PaginaSEI::tratarHTML($strDescricaoUnidadeGeradora) . '" title="' . PaginaSEI::tratarHTML($strDescricaoUnidadeGeradora) . '" class="ancoraSigla">' . PaginaSEI::tratarHTML($strSiglaUnidadeGeradora) . '</a>';
        $arrMetatags['Usuário'] = '<a alt="' . PaginaSEI::tratarHTML($strNomeUsuarioGerador) . '" title="' . PaginaSEI::tratarHTML($strNomeUsuarioGerador) . '" class="ancoraSigla">' . PaginaSEI::tratarHTML($strSiglaUsuarioGerador) . '</a>';

        $dtaGeracao = SolrUtil::obterTag($registros[$i], 'dta_ger', 'date');
        $dtaGeracao = preg_replace("/(\\d{4})-(\\d{2})-(\\d{2})(.*)/", "$3/$2/$1", $dtaGeracao);

        $arrMetatags['Data'] = $dtaGeracao;

        $temp = $xml->xpath("/response/lst[@name='highlighting']/lst[@name='" . $dados['id'] . "']/arr[@name='content']/str");
        $snippet = '';
        for ($j = 0; $j < count($temp); $j++) {
          $snippetTemp = utf8_decode($temp[$j]);
          $snippetTemp = strtoupper(trim(strip_tags($snippetTemp))) == "NULL" ? null : $snippetTemp;
          $snippetTemp = preg_replace("/<br>/i", "<br />", $snippetTemp);
          $snippetTemp = preg_replace("/&lt;.*?&gt;/", "", $snippetTemp);
          $snippet .= $snippetTemp . '<b>&nbsp;&nbsp;...&nbsp;&nbsp;</b>';
        }

        // ÁRVORE

        $strLinkArvore = 'controlador.php?acao=procedimento_trabalhar&acao_origem=protocolo_pesquisar&id_procedimento=' . $dados['id_proc'];
        if ($dados['id_doc'] != null) {
          $strLinkArvore .= '&id_documento=' . $dados['id_doc'];
        }

        $tituloCompleto = '';
        if (!($bolArvore && $dados['id_proc']==$objPesquisaProtocoloSolrDTO->getDblIdProcedimento())) {
          $tituloCompleto .= "<a onclick=\"infraLimparFormatarTrAcessada(this.parentNode.parentNode);\" href=\"".SessaoSEI::getInstance()->assinarLink($strLinkArvore)."\" target=\"_blank\" class=\"arvore\">";
          $tituloCompleto .= "<img border=\"0\" src=\"".PaginaSEI::getInstance()->getDiretorioImagensLocal()."/icone-arvore.png\" alt=\"\" title=\"Visualizar árvore\" width=\"14\" height=\"16\" class=\"arvore\" />";
          $tituloCompleto .= "</a>";
        }

        $tituloCompleto .= $titulo;

        // REMOVE TAGS DO TÍTULO
        $tituloCompleto = preg_replace("/&lt;.*?&gt;/", "", $tituloCompleto);

        $html .= "<table border=\"0\" class=\"resultado\">\n";
        $html .= "<tr class=\"resTituloRegistro\">\n";
        $html .= "<td class=\"resTituloEsquerda\">";
        $html .= $tituloCompleto;
        $html .= "</td>\n";
        $html .= "<td class=\"resTituloDireita\">";
        $html .= $tituloProtocolo;
        $html .= "</td>\n";
        $html .= "</tr>\n";

        if (empty($snippet) == false)
          $html .= "<tr>\n
    							<td colspan=\"2\" class=\"resSnippet\">
    								" . $snippet . "
    							</td>\n
    							</tr>\n";

        if (count($arrMetatags)) {
          $html .= "<tr>\n";
          $html .= "<td colspan=\"2\" class=\"metatag\">\n";
          $html .= "<table>\n";
          $html .= "<tbody>\n";
          $html .= "<tr>\n";

          foreach ($arrMetatags as $nomeMetaTag => $valorMetaTag) {
            $html .= "<td>";
            $html .= "<b>" . $nomeMetaTag . ":</b> " . $valorMetaTag;
            $html .= "</td>\n";
          }

          $html .= "</tr>\n";
          $html .= "</tbody>\n";
          $html .= "</table>\n";
          $html .= "</td>\n";
          $html .= "</tr>\n";
        }

        $html .= "</table>\n";
      }

      $html .= SolrUtil::criarBarraNavegacao($itens, $parametros->start, $parametros->rows);
    }

    return $html;
  }
}
?>