<?

try {
	
	require_once dirname(__FILE__).'/../SEI.php';

	session_start();
	
	//////////////////////////////////////////////////////////////////////////////
	InfraDebug::getInstance()->setBolLigado(false);
	InfraDebug::getInstance()->setBolDebugInfra(false);
	InfraDebug::getInstance()->limpar();
	//////////////////////////////////////////////////////////////////////////////

	SessaoPublicacoes::getInstance()->validarLink();
	
	SessaoPublicacoes::getInstance()->validarPermissao($_GET['acao']);

  $arrNumIdOrgao = array();
  $objResultadoPesquisaSolrDTO = null;

	switch($_GET['acao']){

	  case 'publicacao_pesquisar':

	   $strTitulo = 'Publicações Eletrônicas';
	    	    
      if(isset($_POST['selOrgao'])){
        $arrNumIdOrgao = $_POST['selOrgao'];
        if (!is_array($arrNumIdOrgao)){
          $arrNumIdOrgao = array($arrNumIdOrgao);
        }
      }

	    $strResultado = '';

	    $arrComandos = array();
	    $arrComandos[] = '<button type="submit" id="sbmPesquisar" name="sbmPesquisar" value="Pesquisar" class="infraButton">Pesquisar</button>';	    
	    	    
      $objPesquisaPublicacaoSolrDTO = new PesquisaPublicacaoSolrDTO();
      $objPesquisaPublicacaoSolrDTO->setArrNumIdOrgao($arrNumIdOrgao);
      $objPesquisaPublicacaoSolrDTO->setStrPalavrasChave($_POST['txtInteiroTeor']);
      $objPesquisaPublicacaoSolrDTO->setStrResumo($_POST['txtResumo']);
      $objPesquisaPublicacaoSolrDTO->setNumIdUnidadeResponsavel($_POST['selUnidadeResponsavel']);
      $objPesquisaPublicacaoSolrDTO->setNumIdSerie($_POST['selSerie']);
      $objPesquisaPublicacaoSolrDTO->setStrNumero($_POST['txtNumero']);
      $objPesquisaPublicacaoSolrDTO->setStrProtocoloPesquisa($_POST['txtProtocoloPesquisa']);
      $objPesquisaPublicacaoSolrDTO->setNumIdVeiculoPublicacao($_POST['selVeiculoPublicacao']);
      $objPesquisaPublicacaoSolrDTO->setDtaGeracao($_POST['txtDataDocumento']);
      $objPesquisaPublicacaoSolrDTO->setStrStaTipoData($_POST['rdoDataPublicacao']);
      $objPesquisaPublicacaoSolrDTO->setDtaInicio($_POST['txtDataInicio']);
      $objPesquisaPublicacaoSolrDTO->setDtaFim($_POST['txtDataFim']);
      $objPesquisaPublicacaoSolrDTO->setNumInicioPaginacao($_POST['hdnInicio']);

	    if (isset($_POST['hdnInicio'])){
				try{

          $objResultadoPesquisaSolrDTO = SolrPublicacao::executar($objPesquisaPublicacaoSolrDTO);

				}catch(Exception $e){
          PaginaPublicacoes::getInstance()->setStrMensagem(SolrUtil::$MSG_ERRO_PESQUISA, InfraPagina::$TIPO_MSG_AVISO);
          LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
				}
	    }

	    break;

	  default:
	    throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
	}

  if ($objResultadoPesquisaSolrDTO!=null) {
    $arrObjResultadoPublicacaoSolrDTO = $objResultadoPesquisaSolrDTO->getArrObjInfraDTO();

    $strResultado = '';

    $numRegistros = count($arrObjResultadoPublicacaoSolrDTO);

    if ($numRegistros == 0) {

      if (isset($_POST['hdnFlagPesquisa'])) {
        $strResultado .= '<br/><div id="divSemResultado">';
        $strResultado .= "Sua pesquisa não encontrou nenhuma publicação correspondente.";
        $strResultado .= "<br/>";
        $strResultado .= "<br/>";
        $strResultado .= "Sugestões:";
        $strResultado .= "<ul>";
        $strResultado .= "<li>Certifique-se de que todas as palavras estejam escritas corretamente.</li>";
        $strResultado .= "<li>Tente palavras-chave diferentes.</li>";
        $strResultado .= "<li>Tente palavras-chave mais genéricas.</li>";
        $strResultado .= "</ul>";
        $strResultado .= "</div>";
      }

    } else {


      $arrIdOrgao = array();
      $arrIdUnidadeResponsavel = array();
      $arrIdSerie = array();
      $arrIdVeiculoPublicacao = array();
      $arrIdVeiculoImprensaNacional = array();
      $arrIdSecaoImprensaNacional = array();

      foreach ($arrObjResultadoPublicacaoSolrDTO as $objResultadoPublicacaoSolrDTO) {
        $arrIdOrgao[$objResultadoPublicacaoSolrDTO->getNumIdOrgaoResponsavel()] = 0;
        $arrIdUnidadeResponsavel[$objResultadoPublicacaoSolrDTO->getNumIdUnidadeResponsavel()] = 0;
        $arrIdSerie[$objResultadoPublicacaoSolrDTO->getNumIdSerie()] = 0;
        $arrIdVeiculoPublicacao[$objResultadoPublicacaoSolrDTO->getNumIdVeiculoPublicacao()] = 0;
        $arrIdVeiculoImprensaNacional[$objResultadoPublicacaoSolrDTO->getNumIdVeiculoIO()] = 0;
        $arrIdSecaoImprensaNacional[$objResultadoPublicacaoSolrDTO->getNumIdSecaoIO()] = 0;
      }

      $arrObjOrgaoDTO = null;
      if (count($arrIdOrgao)) {
        $objOrgaoDTO = new OrgaoDTO();
        $objOrgaoDTO->setBolExclusaoLogica(false);
        $objOrgaoDTO->retNumIdOrgao();
        $objOrgaoDTO->retStrSigla();
        $objOrgaoDTO->retStrDescricao();
        $objOrgaoDTO->setNumIdOrgao(array_keys($arrIdOrgao), InfraDTO::$OPER_IN);

        $objOrgaoRN = new OrgaoRN();
        $arrObjOrgaoDTO = InfraArray::indexarArrInfraDTO($objOrgaoRN->listarRN1353($objOrgaoDTO), 'IdOrgao');
      }

      $arrObjUnidadeDTOResponsavel = null;
      if (count($arrIdUnidadeResponsavel)) {
        $objUnidadeDTO = new UnidadeDTO();
        $objUnidadeDTO->setBolExclusaoLogica(false);
        $objUnidadeDTO->retNumIdUnidade();
        $objUnidadeDTO->retStrSigla();
        $objUnidadeDTO->retStrDescricao();
        $objUnidadeDTO->setNumIdUnidade(array_keys($arrIdUnidadeResponsavel),InfraDTO::$OPER_IN);

        $objUnidadeRN = new UnidadeRN();
        $arrObjUnidadeDTOResponsavel = InfraArray::indexarArrInfraDTO($objUnidadeRN->listarRN0127($objUnidadeDTO),'IdUnidade');
      }
      
      $arrObjSerieDTO = null;
      if (count($arrIdSerie)) {
        $objSerieDTO = new SerieDTO();
        $objSerieDTO->setBolExclusaoLogica(false);
        $objSerieDTO->retNumIdSerie();
        $objSerieDTO->retStrNome();
        $objSerieDTO->setNumIdSerie(array_keys($arrIdSerie),InfraDTO::$OPER_IN);

        $objSerieRN = new SerieRN();
        $arrObjSerieDTO = InfraArray::indexarArrInfraDTO($objSerieRN->listarRN0646($objSerieDTO),'IdSerie');
      }

      $arrObjVeiculoPublicacaoDTO = null;
      if (count($arrIdVeiculoPublicacao)) {
        $objVeiculoPublicacaoDTO = new VeiculoPublicacaoDTO();
        $objVeiculoPublicacaoDTO->setBolExclusaoLogica(false);
        $objVeiculoPublicacaoDTO->retNumIdVeiculoPublicacao();
        $objVeiculoPublicacaoDTO->retStrNome();
        $objVeiculoPublicacaoDTO->setNumIdVeiculoPublicacao(array_keys($arrIdVeiculoPublicacao),InfraDTO::$OPER_IN);

        $objVeiculoPublicacaoRN = new VeiculoPublicacaoRN();
        $arrObjVeiculoPublicacaoDTO = InfraArray::indexarArrInfraDTO($objVeiculoPublicacaoRN->listar($objVeiculoPublicacaoDTO),'IdVeiculoPublicacao');
      }

      $arrObjVeiculoImprensaNacionalDTO = null;
      if (count($arrIdVeiculoImprensaNacional)) {
        $objVeiculoImprensaNacionalDTO = new VeiculoImprensaNacionalDTO();
        $objVeiculoImprensaNacionalDTO->setBolExclusaoLogica(false);
        $objVeiculoImprensaNacionalDTO->retNumIdVeiculoImprensaNacional();
        $objVeiculoImprensaNacionalDTO->retStrSigla();
        $objVeiculoImprensaNacionalDTO->retStrDescricao();
        $objVeiculoImprensaNacionalDTO->setNumIdVeiculoImprensaNacional(array_keys($arrIdVeiculoImprensaNacional),InfraDTO::$OPER_IN);

        $objVeiculoImprensaNacionalRN = new VeiculoImprensaNacionalRN();
        $arrObjVeiculoImprensaNacionalDTO = InfraArray::indexarArrInfraDTO($objVeiculoImprensaNacionalRN->listar($objVeiculoImprensaNacionalDTO),'IdVeiculoImprensaNacional');
      }

      $arrObjSecaoImprensaNacionalDTO = null;
      if (count($arrIdSecaoImprensaNacional)) {
        $objSecaoImprensaNacionalDTO = new SecaoImprensaNacionalDTO();
        $objSecaoImprensaNacionalDTO->setBolExclusaoLogica(false);
        $objSecaoImprensaNacionalDTO->retNumIdSecaoImprensaNacional();
        $objSecaoImprensaNacionalDTO->retStrNome();
        $objSecaoImprensaNacionalDTO->setNumIdSecaoImprensaNacional(array_keys($arrIdSecaoImprensaNacional),InfraDTO::$OPER_IN);

        $objSecaoImprensaNacionalRN = new SecaoImprensaNacionalRN();
        $arrObjSecaoImprensaNacionalDTO = InfraArray::indexarArrInfraDTO($objSecaoImprensaNacionalRN->listar($objSecaoImprensaNacionalDTO),'IdSecaoImprensaNacional');
      }
      
      $strSumarioTabela = 'Tabela de Publicações Eletrônicas.';
      $strResultado .= '<table id="tblPublicacoes" width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n";

      $strResultado .= '<caption class="infraCaption">';
      $strResultado .= $objResultadoPesquisaSolrDTO->getStrCabecalho();
      $strResultado .= '</caption>';

      $strResultado .= '<tr>';
      $strResultado .= '<th class="infraTh" width="1%" valign="center">' . PaginaPublicacoes::getInstance()->getThCheck() . '</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="1%">Protocolo</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="15%">Descrição</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="8%">Veículo</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="8%">Data de Publicação</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="5%">Unidade</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="5%">Órgão</th>' . "\n";
      $strResultado .= '<th class="infraTh">Resumo</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="8%">Imprensa Nacional</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="5%">Ações</th>' . "\n";
      $strResultado .= '</tr>' . "\n";

      $strResultado .= '</tr>' . "\n";
      $strCssTr = '';

      $i = 0;

      $arrObjPublicacaoDTO = array();
      $arrObjPublicacaoLegadoDTO = array();
      foreach ($arrObjResultadoPublicacaoSolrDTO as $objResultadoPublicacaoSolrDTO) {
        if ($objResultadoPublicacaoSolrDTO->getNumIdPublicacao() != null) {
          $objPublicacaoDTO = new PublicacaoDTO();
          $objPublicacaoDTO->setDblIdDocumento($objResultadoPublicacaoSolrDTO->getDblIdDocumento());
          $arrObjPublicacaoDTO[] = $objPublicacaoDTO;
        } else {
          $objPublicacaoLegadoDTO = new PublicacaoLegadoDTO();
          $objPublicacaoLegadoDTO->setNumIdPublicacaoLegado($objResultadoPublicacaoSolrDTO->getNumIdPublicacaoLegado());
          $arrObjPublicacaoLegadoDTO[] = $objPublicacaoLegadoDTO;
        }
      }

      $objPublicacaoRN = new PublicacaoRN();
      $objPublicacaoLegadoRN = new PublicacaoLegadoRN();
      $arrIdPublicacaoRelacionada = InfraArray::converterArrInfraDTO($objPublicacaoRN->retornarPublicacoesRelacionadas($arrObjPublicacaoDTO), 'IdPublicacao');
      $arrIdPublicacaoLegadoRelacionada = InfraArray::converterArrInfraDTO($objPublicacaoLegadoRN->retornarPublicacoesRelacionadasLegado($arrObjPublicacaoLegadoDTO), 'IdPublicacaoLegado');


      foreach ($arrObjResultadoPublicacaoSolrDTO as $objResultadoPublicacaoSolrDTO) {

        //die($objResultadoPublicacaoSolrDTO->__toString());

        if (isset($arrObjOrgaoDTO[$objResultadoPublicacaoSolrDTO->getNumIdOrgaoResponsavel()])) {
          $strSiglaOrgaoResponsavel = PaginaPublicacoes::tratarHTML($arrObjOrgaoDTO[$objResultadoPublicacaoSolrDTO->getNumIdOrgaoResponsavel()]->getStrSigla());
          $strDescricaoOrgaoResponsavel = PaginaPublicacoes::tratarHTML($arrObjOrgaoDTO[$objResultadoPublicacaoSolrDTO->getNumIdOrgaoResponsavel()]->getStrDescricao());
        } else {
          $strSiglaOrgaoResponsavel = '[órgão não encontrado]';
          $strDescricaoOrgaoResponsavel = '[órgão não encontrado]';
        }

        if (isset($arrObjUnidadeDTOResponsavel[$objResultadoPublicacaoSolrDTO->getNumIdUnidadeResponsavel()])){
          $strSiglaUnidadeResponsavel = PaginaPublicacoes::tratarHTML($arrObjUnidadeDTOResponsavel[$objResultadoPublicacaoSolrDTO->getNumIdUnidadeResponsavel()]->getStrSigla());
          $strDescricaoUnidadeResponsavel = PaginaPublicacoes::tratarHTML($arrObjUnidadeDTOResponsavel[$objResultadoPublicacaoSolrDTO->getNumIdUnidadeResponsavel()]->getStrDescricao());
        }else{
          $strSiglaUnidadeResponsavel = '[unidade não encontrada]';
          $strDescricaoUnidadeResponsavel = '[unidade não encontrada]';
        }

        $strNomeSerie = '';
        if (isset($arrObjSerieDTO[$objResultadoPublicacaoSolrDTO->getNumIdSerie()])){
          $strNomeSerie = PaginaPublicacoes::tratarHTML($arrObjSerieDTO[$objResultadoPublicacaoSolrDTO->getNumIdSerie()]->getStrNome());
        }else{
          $strNomeSerie = '[tipo de documento não encontrado]';
        }

        $strNomeVeiculoPublicacao = '';
        if (isset($arrObjVeiculoPublicacaoDTO[$objResultadoPublicacaoSolrDTO->getNumIdVeiculoPublicacao()])){
          $strNomeVeiculoPublicacao = PaginaPublicacoes::tratarHTML($arrObjVeiculoPublicacaoDTO[$objResultadoPublicacaoSolrDTO->getNumIdVeiculoPublicacao()]->getStrNome());
        }else{
          $strNomeVeiculoPublicacao = '[veículo de publicação não encontrado]';
        }

        $strSiglaVeiculoImprensaNacional = '';
        $strDescricaoVeiculoImprensaNacional = '';
        if ($objResultadoPublicacaoSolrDTO->getNumIdVeiculoIO()!=null) {
          if (isset($arrObjVeiculoImprensaNacionalDTO[$objResultadoPublicacaoSolrDTO->getNumIdVeiculoIO()])) {
            $strSiglaVeiculoImprensaNacional = PaginaPublicacoes::tratarHTML($arrObjVeiculoImprensaNacionalDTO[$objResultadoPublicacaoSolrDTO->getNumIdVeiculoIO()]->getStrSigla());
            $strDescricaoVeiculoImprensaNacional = PaginaPublicacoes::tratarHTML($arrObjVeiculoImprensaNacionalDTO[$objResultadoPublicacaoSolrDTO->getNumIdVeiculoIO()]->getStrDescricao());
          } else {
            $strSiglaVeiculoPublicacao = '[veículo de publicação nacional não encontrado]';
            $strDescricaoVeiculoImprensaNacional = '[veículo de publicação nacional não encontrado]';
          }
        }

        if ($objResultadoPublicacaoSolrDTO->getNumIdSecaoIO()!=null) {
          $strNomeSecaoImprensaNacional = '';
          if (isset($arrObjSecaoImprensaNacionalDTO[$objResultadoPublicacaoSolrDTO->getNumIdSecaoIO()])) {
            $strNomeSecaoImprensaNacional = PaginaPublicacoes::tratarHTML($arrObjSecaoImprensaNacionalDTO[$objResultadoPublicacaoSolrDTO->getNumIdSecaoIO()]->getStrNome());
          } else {
            $strNomeSecaoImprensaNacional = '[seção do veículo de publicação nacional não encontrada.]';
          }
        }

        $strTrClass = ($strTrClass == 'infraTrClara') ? 'infraTrEscura' : 'infraTrClara';
        $strResultado .= '<tr id="trPublicacaoA' . $i . '" class="' . $strTrClass . '">';

        if ($objResultadoPublicacaoSolrDTO->isSetStrSnippet()) {
          $strRowSpanCheck = 'rowspan="2"';
        }

        $numIdTabela = $objResultadoPublicacaoSolrDTO->getNumIdPublicacaoLegado() != null ? 'legado-' . $objResultadoPublicacaoSolrDTO->getNumIdPublicacaoLegado() : 'sei-' . $objResultadoPublicacaoSolrDTO->getDblIdDocumento();
        $strResultado .= '<td ' . $strRowSpanCheck . ' valign="center" class="tdCheck">' . PaginaPublicacoes::getInstance()->getTrCheck($i, $numIdTabela, $objResultadoPublicacaoSolrDTO->getStrProtocoloDocumentoFormatado()) . '</td>';
        $strResultado .= '<td align="center" class="tdDados"><a href="' . SessaoPublicacoes::getInstance()->assinarLink('controlador_publicacoes.php?acao=publicacao_visualizar&id_documento=' . $objResultadoPublicacaoSolrDTO->getDblIdDocumento()) . ($objResultadoPublicacaoSolrDTO->getNumIdPublicacaoLegado() != null ? '&id_publicacao_legado=' . $objResultadoPublicacaoSolrDTO->getNumIdPublicacaoLegado() : '') . '" target="_blank" alt="' . $strNomeSerie . '" title="' . $strNomeSerie . '" class="ancoraPadraoAzul">' . $objResultadoPublicacaoSolrDTO->getStrProtocoloDocumentoFormatado() . '</a></td>';
        $strResultado .= '<td align="center" class="tdDados">' . $strNomeSerie . ' ' . $objResultadoPublicacaoSolrDTO->getStrNumero() . '</td>';

        $strResultado .= '<td align="center" class="tdDados">' . $strNomeVeiculoPublicacao;
        if (!InfraString::isBolVazia($objResultadoPublicacaoSolrDTO->getNumNumeroPublicacao())) {
          $strResultado .= ' Nº ' . $objResultadoPublicacaoSolrDTO->getNumNumeroPublicacao();
        }
        $strResultado .= '</td>';


        $strResultado .= '<td align="center" class="tdDados">' . $objResultadoPublicacaoSolrDTO->getDtaPublicacao() . '</td>';
        $strResultado .= '<td align="center" class="tdDados"><a alt="' . $strDescricaoUnidadeResponsavel . '" title="' . $strDescricaoUnidadeResponsavel . '" class="ancoraSigla">' . $strSiglaUnidadeResponsavel . '</a></td>';
        $strResultado .= '<td align="center" class="tdDados"><a alt="' . $strDescricaoOrgaoResponsavel . '" title="' . $strDescricaoOrgaoResponsavel . '" class="ancoraSigla">' . $strSiglaOrgaoResponsavel . '</a></td>';

        $strResultado .= '<td align="left" class="tdDados">' . $objResultadoPublicacaoSolrDTO->getStrResumo() . '</td>';
        $strResultado .= '<td align="center" class="tdDados">&nbsp;';

        $strResultado .= PublicacaoINT::montarDadosImprensaNacional($strSiglaVeiculoImprensaNacional,
            $strDescricaoVeiculoImprensaNacional,
            $objResultadoPublicacaoSolrDTO->getDtaPublicacaoIO(),
            $strNomeSecaoImprensaNacional,
            $objResultadoPublicacaoSolrDTO->getStrPaginaIO());

        $strResultado .= '</td>';
        $strResultado .= '<td align="center" class="tdDados">&nbsp;';

        if ($objResultadoPublicacaoSolrDTO->getNumIdPublicacao() != null && in_array($objResultadoPublicacaoSolrDTO->getNumIdPublicacao(), $arrIdPublicacaoRelacionada) ||
            $objResultadoPublicacaoSolrDTO->getNumIdPublicacaoLegado() != null && in_array($objResultadoPublicacaoSolrDTO->getNumIdPublicacaoLegado(), $arrIdPublicacaoLegadoRelacionada)
        ) {
          $strResultado .= '<a onclick="visualizarPublicacoesRelacionadas(\'' . SessaoPublicacoes::getInstance()->assinarLink('controlador_publicacoes.php?acao=publicacao_relacionada_visualizar&id_publicacao=' . $objResultadoPublicacaoSolrDTO->getNumIdPublicacao() . '&id_publicacao_legado=' . $objResultadoPublicacaoSolrDTO->getNumIdPublicacaoLegado() . '&id_documento=' . $objResultadoPublicacaoSolrDTO->getDblIdDocumento()) . '\');" tabindex="' . PaginaPublicacoes::getInstance()->getProxTabTabela() . '"><img src="../imagens/relacionada.gif" title="Consultar Publicações Relacionadas" alt="Consultar Publicações Relacionadas" class="infraImg" /></a>';
        }

        $strResultado .= '</td>' . "\n";
        $strResultado .= '</tr>' . "\n";

        if ($objResultadoPublicacaoSolrDTO->isSetStrSnippet()) {
          $strResultado .= '<tr id="trPublicacaoB' . $i . '" class="' . $strTrClass . ' trSnippet">' . "\n";
          $strResultado .= '<td colspan="9">' . $objResultadoPublicacaoSolrDTO->getStrSnippet() . '</td>';
          $strResultado .= '</tr>' . "\n";

          $strResultado .= '<tr class="trEspaco"><td colspan="10">&nbsp;</td></tr>' . "\n";
        }

        $i++;
      }
      $strResultado .= '</table>';
      $strResultado .= '<div id="divRodape">' . $objResultadoPesquisaSolrDTO->getStrRodape() . '</div>';
    }
  }

  $objOrgaoDTO = new OrgaoDTO();
  $objOrgaoDTO->retNumIdOrgao();
  $objOrgaoDTO->retStrSigla();
  $objOrgaoDTO->retStrDescricao();
  $objOrgaoDTO->setStrSinPublicacao('S');
  $objOrgaoDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

  $objOrgaoRN = new OrgaoRN();
  $arrObjOrgaoDTO = $objOrgaoRN->listarRN1353($objOrgaoDTO);

  $strVisibilityOrgao = '';
  if (count($arrObjOrgaoDTO)==0){
    throw new InfraException('Nenhum órgão configurado para publicação de documentos.');
  }else if (count($arrObjOrgaoDTO)==1){
    $strVisibilityOrgao = 'visibility:hidden;';
  }

  $strOptionsOrgaos='';
  foreach($arrObjOrgaoDTO as $objOrgaoDTO){
    $strOptionsOrgaos.='<option value="'.$objOrgaoDTO->getNumIdOrgao().'"';
    if (isset($_POST['selOrgao'])){
      if (in_array($objOrgaoDTO->getNumIdOrgao(), $arrNumIdOrgao)) {
        $strOptionsOrgaos .= ' selected="selected"';
      }
    }else{
      $strOptionsOrgaos .= ' selected="selected"';
    }
    $strOptionsOrgaos.='>'.PaginaPublicacoes::tratarHTML($objOrgaoDTO->getStrSigla()).'</option>'."\n";
  }

	$strLinkAjaxUnidade = SessaoPublicacoes::getInstance()->assinarLink('controlador_ajax_publicacoes.php?acao_ajax=montar_unidades_pesquisa');
	$strLinkAjaxSerie = SessaoPublicacoes::getInstance()->assinarLink('controlador_ajax_publicacoes.php?acao_ajax=montar_series_pesquisa');

	$strItensSelUnidades = UnidadeINT::montarSelectSiglaDescricaoPesquisaPublicacao('null','&nbsp',$_POST['selUnidadeResponsavel'], $arrIdOrgaosSelecionados);
	$strItensSelSeries = SerieINT::montarSelectNomeDescricaoPesquisaPublicacao('null','&nbsp',$_POST['selSerie'], $arrIdOrgaosSelecionados);
	$strItensSelVeiculoPublicacao = VeiculoPublicacaoINT::montarSelectNomePesquisa('null','&nbsp;',$_POST['selVeiculoPublicacao']);

  $strLinkAjuda = ConfiguracaoSEI::getInstance()->getValor('SEI','URL').'/ajuda/ajuda_solr.html';

} catch(Exception $e) { 
	PaginaPublicacoes::getInstance()->processarExcecao($e);
}

//MONTAGEM DA PÁGINA
PaginaPublicacoes::getInstance()->montarDocType();
PaginaPublicacoes::getInstance()->abrirHtml();
PaginaPublicacoes::getInstance()->abrirHead();
PaginaPublicacoes::getInstance()->montarMeta();
PaginaPublicacoes::getInstance()->montarTitle('SEI - Publicações Eletrônicas');
PaginaPublicacoes::getInstance()->montarStyle();
PaginaPublicacoes::getInstance()->abrirStyle();
?>

div.barra {
  font-size: 1em;
  padding: 0 0 .5em 0;
  text-align: right;
}

#tblPublicacoes {
	border-spacing: 0px !important;
}

#tblPublicacoes tr {
}

#tblPublicacoes td {
border-right:1px solid #ccc; 
border-bottom:1px solid #ccc;
padding:.4em !important;
}

td.tdCheck {
border-top:1px solid #ccc;
border-left:1px solid #ccc;
}

td.tdDados {
border-top:1px solid #ccc;
}
tr.trSnippet td b {
  font-weight:bold;
}

tr.trEspaco{
 background-color:white;
}

tr.trEspaco td{
 border:0 !important;  
}

#lblOrgao {position:absolute;left:0%;top:0%;width:9%;<?=$strVisibilityOrgao?>}
#selOrgao, .multipleSelect {position:absolute;left:20%;top:0%;width:35%;<?=$strVisibilityOrgao?>}

#lblInteiroTeor {position:absolute;left:0%;top:9%;width:29%;visibility:hidden;}
#txtInteiroTeor {position:absolute;left:20%;top:9%;width:50%;}
#ancAjuda {position:absolute;left:72%;top:9%;}

#lblResumo {position:absolute;left:0%;top:18%;width:29%;}
#txtResumo {position:absolute;left:20%;top:18%;width:50%;}

#lblUnidadeResponsavel {position:absolute;left:0%;top:27%;width:29%;}
#selUnidadeResponsavel {position:absolute;left:20%;top:27%;width:65%;}

#lblSerie {position:absolute;left:0%;top:36%;width:29%;}
#selSerie {position:absolute;left:20%;top:36%;width:35%;}

#lblNumero {position:absolute;left:0%;top:45%;width:29%;}
#txtNumero {position:absolute;left:20%;top:45%;width:10%;}

#lblProtocoloPesquisa {position:absolute;left:0%;top:54%;width:29%;}
#txtProtocoloPesquisa {position:absolute;left:20%;top:54%;width:15%;}

#lblVeiculoPublicacao {position:absolute;left:0%;top:63%;width:29%;}
#selVeiculoPublicacao {position:absolute;left:20%;top:63%;width:35%;}

#lblDataDocumento {position:absolute;left:0%;top:72%;width:20%;}
#txtDataDocumento {position:absolute;left:20%;top:72%;width:12%;}
#imgDataDocumento {position:absolute;left:33%;top:74%;}

#lblDataPublicacao {position:absolute;left:0%;top:81%;width:20%;}
#optHoje {position:absolute;left:20%;top:81%;}
#lblHoje {position:absolute;left:23%;top:81%;width:20%;}
#optIndeterminada {position:absolute;left:20%;top:87%;}
#lblIndeterminada {position:absolute;left:23%;top:87%;width:20%;}
#optPeriodoExplicito {position:absolute;left:20%;top:93%;}
#lblPeriodoExplicito {position:absolute;left:23%;top:93%;width:20%;}

#txtDataInicio {position:absolute;left:20%;top:0%;width:12%;}
#imgDataInicio {position:absolute;left:33%;top:10%;}
#lblDataAte {position:absolute;left:37%;top:10%;width:1%;}
#txtDataFim {position:absolute;left:42%;top:0%;width:12%;}
#imgDataFim {position:absolute;left:55%;top:10%;}


#divRodape {
	margin-top:.5em;
	width:99%;
}

#divRodape div{
 text-align:center;
 font-size: 1.2em;
}

#divRodape b {
	font-weight: bold;
}

#divRodape a {
	border-bottom: 1px solid transparent;
	color: #000080;
	text-decoration: none;
}

#divRodape a:hover {
	border-bottom: 1px solid #000000;
	color: #800000;
}

a.ancoraSigla{
font-size:1em;
}

a.ancoraSigla:hover{
text-decoration:underline !important;
}

#divSemResultado{
  font-size:1.2em;
	margin: .5em 0 0 0;
}

<?
PaginaPublicacoes::getInstance()->fecharStyle();
PaginaPublicacoes::getInstance()->montarJavaScript();
PaginaPublicacoes::getInstance()->abrirJavaScript();
?>
//<script>

var objAjaxUnidade = null;

function inicializar(){

  $("#selOrgao").multipleSelect({
  filter: false,
  minimumCountSelected: 1,
  selectAll: true,
  });

	objAjaxUnidade = new infraAjaxMontarSelect('selUnidadeResponsavel','<?=$strLinkAjaxUnidade?>');
  objAjaxUnidade.limparSelect = true;
  objAjaxUnidade.prepararExecucao = function(){
     return infraAjaxMontarPostPadraoSelect('null','','<?=$_POST['selUnidadeResponsavel']?>')+'&idOrgao=' + obterOrgaosSelecionados();
  }
  objAjaxUnidade.processarResultado = function(){}
  //objAjaxUnidade.executar();
  
  objAjaxSerie = new infraAjaxMontarSelect('selSerie','<?=$strLinkAjaxSerie?>');
  objAjaxSerie.limparSelect = true;
  objAjaxSerie.prepararExecucao = function(){
     return infraAjaxMontarPostPadraoSelect('null','','<?=$_POST['selSerie']?>')+'&idOrgao=' + obterOrgaosSelecionados();
  }
  objAjaxSerie.processarResultado = function(){}
  //objAjaxSerie.executar();


  tratarSelecaoOrgao(false);
  
	tratarPeriodo();	
	
	infraProcessarResize();

  prepararTrs();

  infraExibirMenuSistemaEsquema();
}

function tratarPeriodo(){
  
  if (document.getElementById('optPeriodoExplicito').checked){  
    document.getElementById('divPeriodoExplicito').style.display='block';    
  }else{
  	document.getElementById('divPeriodoExplicito').style.display='none';
  }
}

function visualizarPublicacoes() {

  var publicacoes_sei = '';
  var publicacoes_legado = '';
  
  var publicacoes = document.getElementById('hdnInfraItensSelecionados').value;
  
  if (publicacoes == ''){ 
    alert('Nenhum registro selecionado.');
  }else{    
      
    arrPublicacao = publicacoes.split(',');
     
    for (var i = 0; i < arrPublicacao.length; i++) {         
      if (arrPublicacao[i].indexOf("sei-") != -1){      
        if (publicacoes_sei != ''){
          publicacoes_sei += ',';
        }        
        publicacoes_sei += arrPublicacao[i].substr(4);
                     
      }else{        
        if (arrPublicacao[i].indexOf("legado-") != -1){        
          if (publicacoes_legado != ''){
            publicacoes_legado += ',';
          }          
          publicacoes_legado += arrPublicacao[i].substr(7);
        }
      }
    }
    infraAbrirJanela('<?=SessaoPublicacoes::getInstance()->assinarLink('controlador_publicacoes.php?acao=publicacao_visualizar')?>&id_documento='+publicacoes_sei+'&id_publicacao_legado='+publicacoes_legado,'janelaVisualizarPublicacoes',1024,768,'location=0,status=0,resizable=1,scrollbars=0');        
  }  
}

function visualizarPublicacoesRelacionadas(link) {
  infraAbrirJanela(link,'janelaVisualizarPublicacoesRelacionadas',740,400,'location=0,status=0,resizable=1,scrollbars=0');           
}

function onSubmitForm(){

  if (obterOrgaosSelecionados()==''){
    alert('Selecione pelo menos um órgão para pesquisa.');
    document.getElementById('selOrgao').focus();
    return false;          
  }

  if (!infraValidarData(document.getElementById('txtDataDocumento'))) {
    return false;
  }

  if (document.getElementById('optPeriodoExplicito').checked){

    if ((infraTrim(document.getElementById('txtDataInicio').value)=='') ^ (infraTrim(document.getElementById('txtDataFim').value)=='')){
      alert('Período incompleto.');
      document.getElementById('txtDataInicio').focus()
      return false;
    }

    if (infraTrim(document.getElementById('txtDataInicio').value)!='' && infraTrim(document.getElementById('txtDataFim').value)!='') {
      if (!infraValidarData(document.getElementById('txtDataInicio'))) {
        return false;
      }

      if (!infraValidarData(document.getElementById('txtDataFim'))) {
        return false;
      }

      if (infraCompararDatas(document.getElementById('txtDataInicio').value, document.getElementById('txtDataFim').value)<0) {
        alert('Período de datas inválido.');
        document.getElementById('txtDataInicio').focus();
        return false;
      }
    }
  }

  return true;
}

function tratarSelecaoOrgao(executar){
  if (obterOrgaosSelecionados()==''){
    document.getElementById('selUnidadeResponsavel').disabled = true;
    document.getElementById('selUnidadeResponsavel').options.length = 0;
    
    document.getElementById('selSerie').disabled = true;
    document.getElementById('selSerie').options.length = 0;
  }else{
    document.getElementById('selUnidadeResponsavel').disabled = false;
    if (executar){
      objAjaxUnidade.executar();
    }
    
    document.getElementById('selSerie').disabled = false;
    if (executar){
      objAjaxSerie.executar();
    }    
  }
}

function obterOrgaosSelecionados(){
  return $("#selOrgao").multipleSelect("getSelects");
}

function prepararTrs(){
  
  var i;
  var tab = document.getElementById('tblPublicacoes');
  
  if (tab != null){
    
    //Adiciona eventos para modificar a linha com o passar do mouse
    var trs = tab.getElementsByTagName("tr");
      
    for(i=0;i < trs.length;i++){
      if (trs[i].id.search('trPublicacaoA')==0){
      
        trs[i].onmarcada=function(){
          var trDependente = document.getElementById(this.id.replace('A','B'));
          if (trDependente!=null){
            infraFormatarTrMarcada(trDependente);
          }
        };
        
        trs[i].ondesmarcada=function(){
          var trDependente = document.getElementById(this.id.replace('A','B'));
          if (trDependente!=null){
            infraFormatarTrDesmarcada(trDependente);
          }
        };
      }
    }
  }
}

function navegar(inicio) {
  document.getElementById('hdnInicio').value = inicio;
  if (typeof(window.onSubmitForm)=='function' && !window.onSubmitForm()) {
    return;
  }
  document.getElementById('frmPublicacaoPesquisa').submit();
}

//</script>
<?
PaginaPublicacoes::getInstance()->fecharJavaScript();
PaginaPublicacoes::getInstance()->fecharHead();
PaginaPublicacoes::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmPublicacaoPesquisa" name="frmPublicacaoPesquisa" method="post" onsubmit="return onSubmitForm();" action="<?=SessaoPublicacoes::getInstance()->assinarLink('controlador_publicacoes.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>">
<?
if ($numRegistros > 0){
  $arrComandos[] = '<button type="button" accesskey="V" id="btnVisualizar" value="Visualizar Selecionados" onclick="visualizarPublicacoes();" class="infraButton"><span class="infraTeclaAtalho">V</span>isualizar Selecionados</button>';
  //$arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
}
PaginaPublicacoes::getInstance()->montarBarraComandosSuperior($arrComandos);
?>

<div id="divPrincipal" class="infraAreaDados" style="height:30em;">

  <label id="lblOrgao" for="selOrgao" accesskey="" class="infraLabelObrigatorio">Órgão:</label>
  <select multiple id="selOrgao" name="selOrgao[]" onchange="tratarSelecaoOrgao(true)" class="infraSelect multipleSelect" tabindex="<?=PaginaPublicacoes::getInstance()->getProxTabDados()?>">
    <?=$strOptionsOrgaos;?>
  </select>

  <label id="lblInteiroTeor" for="txtInteiroTeor" accesskey="" class="infraLabelOpcional">Inteiro Teor:</label>
	<input type="text" id="txtInteiroTeor" name="txtInteiroTeor" class="infraText" value="<?=str_replace('\\','',str_replace('"','&quot;',$_POST['txtInteiroTeor']))?>" maxlength="100" tabindex="<?=PaginaPublicacoes::getInstance()->getProxTabDados()?>" />
  <a id="ancAjuda" href="<?=$strLinkAjuda?>" target="_blank" title="Ajuda para Pesquisa" tabindex="<?=PaginaPublicacoes::getInstance()->getProxTabDados()?>"><img src="<?=PaginaPublicacoes::getInstance()->getDiretorioImagensGlobal()?>/ajuda.gif" class="infraImg"/></a>

	<label id="lblResumo" for="txtResumo" accesskey="" class="infraLabelOpcional">Resumo:</label>
	<input type="text" id="txtResumo" name="txtResumo" class="infraText" value="<?=$_POST['txtResumo'];?>" onkeypress="return infraMascaraTexto(this,event,50);" maxlength="100" tabindex="<?=PaginaPublicacoes::getInstance()->getProxTabDados()?>" />
	
  <label id="lblUnidadeResponsavel" for="selUnidadeResponsavel" accesskey="" class="infraLabelOpcional">Unidade Responsável:</label>
  <select id="selUnidadeResponsavel" name="selUnidadeResponsavel" class="infraSelect" tabindex="<?=PaginaPublicacoes::getInstance()->getProxTabDados()?>">
  <?=$strItensSelUnidades?>
  </select>

  <label id="lblSerie" for="selSerie" accesskey="" class="infraLabelOpcional">Tipo do Documento:</label>
  <select id="selSerie" name="selSerie" class="infraSelect" tabindex="<?=PaginaPublicacoes::getInstance()->getProxTabDados()?>">
  <?=$strItensSelSeries?>
  </select>

  <label id="lblNumero" for="txtNumero" accesskey="" class="infraLabelOpcional">Número:</label>
  <input type="text" id="txtNumero" name="txtNumero" class="infraText" value="<?=$_POST['txtNumero'];?>" onkeypress="return infraMascaraTexto(this,event,50);" maxlength="15" tabindex="<?=PaginaPublicacoes::getInstance()->getProxTabDados()?>" />

  <label id="lblProtocoloPesquisa" for="txtProtocoloPesquisa" accesskey="" class="infraLabelOpcional">Protocolo:</label> 
  <input type="text" id="txtProtocoloPesquisa" name="txtProtocoloPesquisa" class="infraText" value="<?=$_POST['txtProtocoloPesquisa'];?>" tabindex="<?=PaginaPublicacoes::getInstance()->getProxTabDados()?>" />    

  <label id="lblVeiculoPublicacao" for="selVeiculoPublicacao" accesskey="" class="infraLabelOpcional">Veículo:</label>
	<select id="selVeiculoPublicacao" name="selVeiculoPublicacao" class="infraSelect" tabindex="<?=PaginaPublicacoes::getInstance()->getProxTabDados()?>">
	<?=$strItensSelVeiculoPublicacao?>
	</select>
  
  <label id="lblDataDocumento" for="txtDataDocumento" accesskey="" class="infraLabelOpcional">Data do Documento:</label>        
  <input type="text" id="txtDataDocumento" name="txtDataDocumento" onkeypress="return infraMascaraData(this, event)" class="infraText" value="<?echo $_POST['txtDataDocumento'];?>" tabindex="<?=PaginaPublicacoes::getInstance()->getProxTabDados()?>" />
  <img id="imgDataDocumento" src="/infra_css/imagens/calendario.gif" onclick="infraCalendario('txtDataDocumento',this);" alt="Selecionar Data do Documento" title="Selecionar Data do Documento" class="infraImg" tabindex="<?=PaginaPublicacoes::getInstance()->getProxTabDados()?>" />

  <label id="lblDataPublicacao" class="infraLabelObrigatorio">Data de Publicação:</label>
	<input type="radio" id="optHoje" name="rdoDataPublicacao" value="H" onclick="tratarPeriodo();" <?=($_POST['rdoDataPublicacao']=='H'  ? 'checked="checked"':'')?> class="infraRadio"/>
  <label id="lblHoje" accesskey="" for="optHoje" class="infraLabelRadio" tabindex="<?=PaginaPublicacoes::getInstance()->getProxTabDados()?>">Hoje</label>

  <input type="radio" id="optIndeterminada" name="rdoDataPublicacao" value="I" onclick="tratarPeriodo();" <?=($_POST['rdoDataPublicacao']=='I' || !$_POST['rdoDataPublicacao'] ? 'checked="checked"':'')?> class="infraRadio"/>
  <label id="lblIndeterminada" accesskey="" for="optIndeterminada" class="infraLabelRadio" tabindex="<?=PaginaPublicacoes::getInstance()->getProxTabDados()?>">Indeterminada</label>
   
  <input type="radio" id="optPeriodoExplicito" name="rdoDataPublicacao" value="E" onclick="tratarPeriodo();" <?=($_POST['rdoDataPublicacao']=='E' ? 'checked="checked"':'')?> class="infraRadio" /> 
  <label id="lblPeriodoExplicito" accesskey="" for="optPeriodoExplicito" class="infraLabelRadio" tabindex="<?=PaginaPublicacoes::getInstance()->getProxTabDados()?>">Período explícito</label>
</div>
  
<div id="divPeriodoExplicito" class="infraAreaDados" style="height:2.5em;">
  <input type="text" id="txtDataInicio" name="txtDataInicio" onkeypress="return infraMascaraData(this, event)" class="infraText" value="<?=$_POST['txtDataInicio'];?>" tabindex="<?=PaginaPublicacoes::getInstance()->getProxTabDados()?>" />
  <img id="imgDataInicio" src="/infra_css/imagens/calendario.gif" onclick="infraCalendario('txtDataInicio',this);" alt="Selecionar Data Inicial" title="Selecionar Data Inicial" class="infraImg" tabindex="<?=PaginaPublicacoes::getInstance()->getProxTabDados()?>" />	
  <label id="lblDataAte" class="infraLabelOpcional">&nbsp;até&nbsp;</label>
  <input type="text" id="txtDataFim" name="txtDataFim" onkeypress="return infraMascaraData(this, event)" class="infraText" value="<?=$_POST['txtDataFim'];?>" tabindex="<?=PaginaPublicacoes::getInstance()->getProxTabDados()?>" />
  <img id="imgDataFim" src="/infra_css/imagens/calendario.gif" onclick="infraCalendario('txtDataFim',this);" alt="Selecionar Data Final" title="Selecionar Data Final" class="infraImg" tabindex="<?=PaginaPublicacoes::getInstance()->getProxTabDados()?>" />
</div>

<?
if ($numRegistros){
  PaginaPublicacoes::getInstance()->montarAreaTabela($strResultado,$numRegistros,false);
}else{
  echo $strResultado;
}
?>
  <input type="hidden" id="hdnInicio" name="hdnInicio" value="0" />
</form>
<?
PaginaPublicacoes::getInstance()->montarAreaDebug();
PaginaPublicacoes::getInstance()->fecharBody();
PaginaPublicacoes::getInstance()->fecharHtml();
?>