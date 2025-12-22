<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 28/09/2010 - criado por jonatas_db
*
* Versão no CVS: $Id$
*/

try {
  require_once dirname(__FILE__).'/SEI.php';

  session_start();

  
	//PaginaSEI::getInstance()->setBolAutoRedimensionar(false);
  //////////////////////////////////////////////////////////////////////////////
  
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(true);
  InfraDebug::getInstance()->limpar();

  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $arrComandos = array();
  $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" name="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';
  
  $strTitulo = 'Estatisticas de Arquivamento da Unidade';
  
  $bolAcervo= $_POST['hdnAcervo']=='acervo';
  switch($_GET['acao']) {

    case 'gerar_estatisticas_arquivamento':

      $strAcaoDetalhar = 'estatisticas_detalhar_arquivamento';

      if (isset($_POST['txtPeriodoDe']) && isset($_POST['txtPeriodoA'])) {

        $objEstatisticasArquivamentoDTO = new EstatisticasArquivamentoDTO();

        $dtaPeriodoDe = $_POST['txtPeriodoDe'];
        $objEstatisticasArquivamentoDTO->setDtaInicio($dtaPeriodoDe);

        $dtaPeriodoA = $_POST['txtPeriodoA'];
        $objEstatisticasArquivamentoDTO->setDtaFim($dtaPeriodoA.' 23:59:59');
        $strParametros='&de='.$dtaPeriodoDe.'&ate='.$dtaPeriodoA;

        try {

          $objEstatisticasRN = new EstatisticasRN();
          if ($bolAcervo){
            $objEstatisticasArquivamentoDTOret = $objEstatisticasRN->gerarArquivamentoAcervo($objEstatisticasArquivamentoDTO);
          } else {
            $objEstatisticasArquivamentoDTOret = $objEstatisticasRN->gerarArquivamento($objEstatisticasArquivamentoDTO);
          }


        } catch (Exception $e) {
          PaginaSEI::getInstance()->processarExcecao($e);
        }

        if ($objEstatisticasArquivamentoDTOret != null) {
          $arrCores = $objEstatisticasRN->getArrCores();
          $numCores = count($arrCores);
          $numCorAtual = 0;
          $arrCoresTipoProcedimento = $arrCores[0];
        }
      }

      break;

    default:
      throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
  }


  if ($objEstatisticasArquivamentoDTOret != null) {


    //Tipos de Documento
    $objSerieDTO = new SerieDTO();
    $objSerieDTO->setBolExclusaoLogica(false);
    $objSerieDTO->retNumIdSerie();
    $objSerieDTO->retStrNome();

    $objSerieRN = new SerieRN();
    $arrObjSerieDTO = InfraArray::indexarArrInfraDTO($objSerieRN->listarRN0646($objSerieDTO), 'IdSerie');

    //Tipos de Localizadores
    $objTipoLocalizadorDTO = new TipoLocalizadorDTO();
    $objTipoLocalizadorDTO->setBolExclusaoLogica(false);
    $objTipoLocalizadorDTO->retNumIdTipoLocalizador();
    $objTipoLocalizadorDTO->retStrNome();

    $objTipoLocalizadorRN = new TipoLocalizadorRN();
    $arrObjTipoLocalizadorDTO = InfraArray::indexarArrInfraDTO($objTipoLocalizadorRN->listarRN0610($objTipoLocalizadorDTO), 'IdTipoLocalizador');


    $bolAcaoImprimir = false;

    if ($bolAcaoImprimir) {
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirDiv(\'divTabelas\');" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
    }


    $arrRotuloEixoX = array();
    $numTamanhoTabela = 55;
    if (!$bolAcervo) {
      // Arquivados Tipo X Mes
      $mesInicial = substr($objEstatisticasArquivamentoDTO->getDtaInicio(), 3, 2);
      $anoInicial = substr($objEstatisticasArquivamentoDTO->getDtaInicio(), 6, 4);
      $mesFinal = substr($objEstatisticasArquivamentoDTO->getDtaFim(), 3, 2);
      $anoFinal = substr($objEstatisticasArquivamentoDTO->getDtaFim(), 6, 4);

      $numMeses = ($anoFinal * 12 + $mesFinal - 1) - ($anoInicial * 12 + $mesInicial - 1) + 1;
      $numTamanhoColuna = floor(75 / ($numMeses + 1));
      $numTamanhoTabela = floor(($numMeses + 1) * 100 / 13);
      if ($numTamanhoTabela > 100) $numTamanhoTabela = 100;

      if ($numTamanhoTabela < 55) {
        $numTamanhoTabela = 55;
      }

      $colSpanAnoInicial = 0;
      $colSpanAnoFinal = 0;

      if ($anoFinal - $anoInicial > 0) {
        $colSpanAnoInicial = 13 - $mesInicial;
        $colSpanAnoFinal = $mesFinal;
      } else {
        $colSpanAnoInicial = $mesFinal - $mesInicial + 1;
      }

      if ($colSpanAnoInicial) {
        $strHeaderMesAno = '<th class="infraTh" colspan="' . $colSpanAnoInicial . '">' . $anoInicial . '</th>';
      }
      for ($i = $anoInicial + 1; $i < $anoFinal; $i++) {
        $strHeaderMesAno .= '<th class="infraTh" colspan="12">' . $i . '</th>';
      }
      if ($colSpanAnoFinal) {
        $strHeaderMesAno .= '<th class="infraTh" colspan="' . ($colSpanAnoFinal) . '">' . $anoFinal . '</th>';
      }
      $strHeaderMesAno .= '<th class="infraTh" rowspan="2">Total</th>' . "\n";
      if ($numMeses>1) $strHeaderMesAno .= '<th class="infraTh" rowspan="2" width="5%">Ações</th>' . "\n";
      $strHeaderMesAno .= '</tr>';
      $strHeaderMesAno .= '<tr>';

      $mes = $mesInicial;
      $ano = $anoInicial;
      $n = 0;
      while ($n < $numMeses) {
        $strHeaderMesAno .= '<th class="infraTh">' . InfraData::obterMesSiglaBR($mes) . '</th>';
        $arrRotuloEixoX[] = InfraData::obterMesSiglaBR($mes) . '/' . $ano;
        $mes++;
        if ($mes == 13) {
          $mes = 1;
          $ano++;
        }
        $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);
        $n++;
      }
      $strHeaderMesAno .= '</tr>' . "\n";

    }

    $numGrafico=0;
    //----------- Arquivados por mes
    $arrArquivados = $objEstatisticasArquivamentoDTOret->getArrArquivados();
    $strCssTr = '';
    $contadorTabelaArquivados = 0;
    $total = 0;
    $contadorMes = array();
    $idArrayGrafico = 0;
    $arrJsArquivados = array();
    $arrJsArquivados['titulo'] = 'Documentos Arquivados';
    $arrJsArquivados['dados'] = array();
    $arrJsArquivados['maximos'] = array();
    $arrJsArquivados['rotulos'] = array();
    if (count($arrArquivados)>0) {
      $numGrafico++;
    }
    foreach ($arrArquivados as $keySerie => $arrMeses) {

      if ($arrObjSerieDTO[$keySerie] != null) {
        $strNomeSerie = $arrObjSerieDTO[$keySerie]->getStrNome();
      } else {
        $strNomeSerie = '';
      }

      $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
      $strResultadoTabelaArquivados .= $strCssTr;
      $strResultadoTabelaArquivados .= '<td align="left">' . PaginaSEI::tratarHTML($strNomeSerie) . '</td>';
      $arrJsArquivados['rotulos'][] = utf8_encode(PaginaSEI::tratarHTML($strNomeSerie));
      $arrDadosTemp = array();
      if (!$bolAcervo) {
        $mes = $mesInicial;
        $ano = $anoInicial;
        $n = 0;
        $maximo = 0;

        $totalTipo = 0;

        while ($n < $numMeses) {

          $strResultadoTabelaArquivados .= '<td align="center" width="' . $numTamanhoColuna . '%">';

          if (isset($arrMeses[$ano][$mes])) {

            $strResultadoTabelaArquivados .= '<a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDetalhar . '&tipo=arquivados&id_serie=' . $keySerie . '&ano=' . $ano . '&mes=' . substr($mes, 0, 2) . $strParametros) . '\');" class="ancoraPadraoAzul">' . $arrMeses[$ano][$mes] . '</a>';

            $valor = $arrMeses[$ano][$mes];
            if ($valor > $maximo) {
              $maximo = $valor;
            }
            $total += $valor;
            $totalTipo += $valor;
            $contadorMes[$ano][$mes] += $valor;
            $arrDadosTemp[] = $valor;

          } else {
            $strResultadoTabelaArquivados .= '&nbsp;';
            $arrDadosTemp[] = 0;
          }

          $strResultadoTabelaArquivados .= '</td>';

          $mes++;
          if ($mes == 13) {
            $mes = 1;
            $ano++;
          }
          $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);

          $n++;
        }
        $arrJsArquivados['dados'][] = $arrDadosTemp;
        $arrJsArquivados['maximos'][] = $maximo;
      } else {
        $totalTipo = $arrMeses;
        $total += $totalTipo;
      }

      $strLink = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDetalhar . '&tipo=arquivados&id_serie=' . $keySerie . $strParametros);

      $strResultadoTabelaArquivados .= '<td align="center" width="' . $numTamanhoColuna . '%" class="totalEstatisticas"><a href="javascript:void(0);" onclick="abrirDetalhe(\'' . $strLink . '\');" class="ancoraPadraoAzul">' . InfraUtil::formatarMilhares($totalTipo) . '</a></td>';
      if (!$bolAcervo && $numMeses>1) {
        $strLinkGrafico=SessaoSEI::getInstance()->assinarLink('controlador.php?acao=estatisticas_grafico_exibir&num_grafico='.$numGrafico.'&num_linha='.$idArrayGrafico++);
        $strResultadoTabelaArquivados .= '<td align="center"><a href="#divGrf'.$numGrafico.'" onclick="updateGrafico(\''.$strLinkGrafico.'\',this);"><img src="imagens/ver_grafico.gif" title="Gráficos" alt="Gráficos" class="infraImg"></a></td>';
      }
      $strResultadoTabelaArquivados .= '</tr>';

      $strTituloGraficoArquivados = $bolAcervo?'Documentos arquivados':'Documentos arquivados no período';
      $arrayGraficoArquivados[] = array($strNomeSerie, InfraUtil::formatarMilhares($totalTipo), $totalTipo, $strLink);

      $contadorTabelaArquivados++;


    }
    $strResultadoArquivados = '';
    $strResultadoArquivados .= '<table id="tblArquivados" width="' . $numTamanhoTabela . '%" class="infraTable" summary="Tabela de ' . $strTituloGraficoArquivados . '">' . "\n";
    $strResultadoArquivados .= '<caption class="infraCaption">' . $strTituloGraficoArquivados . ':</caption>';
    $strResultadoArquivados .= '<thead><tr>';
    if ($bolAcervo) {
      $strResultadoArquivados .= '<th class="infraTh" width="70%">Tipo do Documento</th>' . "\n";
      $strResultadoArquivados .= '<th class="infraTh" width="30%">Quantidade</th>' . "\n";
    } else {
      $strResultadoArquivados .= '<th class="infraTh" rowspan="2" width="20%">Tipo do Documento</th>' . "\n";
      $strResultadoArquivados .= $strHeaderMesAno;
    }
    $strResultadoArquivados .= '</thead><tbody>';
    $strResultadoArquivados .= $strResultadoTabelaArquivados;
    $strResultadoArquivados .= '<tr>';
    $strResultadoArquivados .= '<td align="right" class="totalEstatisticas"><b>TOTAL:</b></td>';

    $mes = $mesInicial;
    $ano = $anoInicial;
    $n = 0;
    $maximo = 0;
    $arrDadosTemp = array();
    $arrJsArquivados['rotulos'][] = 'Total';
    while ($n < $numMeses) {
      $strResultadoArquivados .= '<td align="center" class="totalEstatisticas">';
      $valor = $contadorMes[$ano][$mes];
      if ($valor>0){
        $strResultadoArquivados .= '<a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDetalhar . '&tipo=arquivados&ano=' . $ano . '&mes=' . substr($mes, 0, 2) . $strParametros) . '\');" class="ancoraPadraoAzul">' . InfraUtil::formatarMilhares($valor) . '</a>';
      }
      $strResultadoArquivados .='</td>';
      if ($valor > $maximo) {
        $maximo = $valor;
      }
      $arrDadosTemp[] = $valor == null ? 0 : $valor;
      $mes++;
      if ($mes == 13) {
        $mes = 1;
        $ano++;
      }
      $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);
      $n++;
    }
    $arrJsArquivados['dados'][] = $arrDadosTemp;
    $arrJsArquivados['maximos'][] = $maximo;
    $strResultadoArquivados .= '<td align="center" class="totalEstatisticas"><a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDetalhar . '&tipo=arquivados' . $strParametros) . '\');" class="ancoraPadraoAzul">' . InfraUtil::formatarMilhares($total) . '</a></td>';
    if (!$bolAcervo && $numMeses>1) {
      $strLinkGrafico=SessaoSEI::getInstance()->assinarLink('controlador.php?acao=estatisticas_grafico_exibir&num_grafico='.$numGrafico.'&num_linha='.$idArrayGrafico++);
      $strResultadoArquivados .= '<td align="center" class="totalEstatisticas"><a href="#divGrf'.$numGrafico.'" onclick="updateGrafico(\''.$strLinkGrafico.'\',this);"><img src="imagens/ver_grafico.gif" title="Gráficos" alt="Gráficos" class="infraImg"></a></td>';
    }
    $strResultadoArquivados .= '</tr></tbody>';
    $strResultadoArquivados .= '</table>';


    //----------- Desarquivados por mes
    $arrDesarquivados = $objEstatisticasArquivamentoDTOret->getArrDesarquivados();
    $strCssTr = '';
    $contadorTabelaDesarquivados = 0;
    $total = 0;
    $contadorMes = array();
    $idArrayGrafico = 0;
    $arrJsDesarquivados = array();
    $arrJsDesarquivados['titulo'] = 'Documentos Desarquivados';
    $arrJsDesarquivados['dados'] = array();
    $arrJsDesarquivados['maximos'] = array();
    $arrJsDesarquivados['rotulos'] = array();
    if (count($arrDesarquivados)>0) {
      $numGrafico++;
    }
    foreach ($arrDesarquivados as $keySerie => $arrMeses) {

      if ($arrObjSerieDTO[$keySerie] != null) {
        $strNomeSerie = $arrObjSerieDTO[$keySerie]->getStrNome();
      } else {
        $strNomeSerie = '';
      }

      $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
      $strResultadoTabelaDesarquivados .= $strCssTr;
      $strResultadoTabelaDesarquivados .= '<td align="left">' . PaginaSEI::tratarHTML($strNomeSerie) . '</td>';
      $arrJsDesarquivados['rotulos'][] = utf8_encode(PaginaSEI::tratarHTML($strNomeSerie));
      $arrDadosTemp = array();
      if (!$bolAcervo) {
        $mes = $mesInicial;
        $ano = $anoInicial;
        $n = 0;
        $maximo = 0;

        $totalTipo = 0;

        while ($n < $numMeses) {

          $strResultadoTabelaDesarquivados .= '<td align="center" width="' . $numTamanhoColuna . '%">';

          if (isset($arrMeses[$ano][$mes])) {

            $strResultadoTabelaDesarquivados .= '<a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDetalhar . '&tipo=desarquivados&id_serie=' . $keySerie . '&ano=' . $ano . '&mes=' . substr($mes, 0, 2) . $strParametros) . '\');" class="ancoraPadraoAzul">' . $arrMeses[$ano][$mes] . '</a>';

            $valor = $arrMeses[$ano][$mes];
            if ($valor > $maximo) {
              $maximo = $valor;
            }
            $total += $valor;
            $totalTipo += $valor;
            $contadorMes[$ano][$mes] += $valor;
            $arrDadosTemp[] = $valor;

          } else {
            $strResultadoTabelaDesarquivados .= '&nbsp;';
            $arrDadosTemp[] = 0;
          }

          $strResultadoTabelaDesarquivados .= '</td>';

          $mes++;
          if ($mes == 13) {
            $mes = 1;
            $ano++;
          }
          $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);

          $n++;
        }
        $arrJsDesarquivados['dados'][] = $arrDadosTemp;
        $arrJsDesarquivados['maximos'][] = $maximo;
      } else {
        $totalTipo = $arrMeses;
        $total += $totalTipo;
      }

      $strLink = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDetalhar . '&tipo=desarquivados&id_serie=' . $keySerie . $strParametros);

      $strResultadoTabelaDesarquivados .= '<td align="center" width="' . $numTamanhoColuna . '%" class="totalEstatisticas"><a href="javascript:void(0);" onclick="abrirDetalhe(\'' . $strLink . '\');" class="ancoraPadraoAzul">' . InfraUtil::formatarMilhares($totalTipo) . '</a></td>';
      if (!$bolAcervo && $numMeses>1) {
        $strLinkGrafico=SessaoSEI::getInstance()->assinarLink('controlador.php?acao=estatisticas_grafico_exibir&num_grafico='.$numGrafico.'&num_linha='.$idArrayGrafico++);
        $strResultadoTabelaDesarquivados .= '<td align="center"><a href="#divGrf'.$numGrafico.'" onclick="updateGrafico(\''.$strLinkGrafico.'\',this);"><img src="imagens/ver_grafico.gif" title="Gráficos" alt="Gráficos" class="infraImg"></a></td>';
      }
      $strResultadoTabelaDesarquivados .= '</tr>';

      $strTituloGraficoDesarquivados = $bolAcervo?'Documentos desarquivados e não devolvidos':'Documentos desarquivados no período';
      $arrayGraficoDesarquivados[] = array($strNomeSerie, InfraUtil::formatarMilhares($totalTipo), $totalTipo, $strLink);

      $contadorTabelaDesarquivados++;


    }
    $strResultadoDesarquivados = '';
    $strResultadoDesarquivados .= '<table id="tblDesarquivados" width="' . $numTamanhoTabela . '%" class="infraTable" summary="'.$strTituloGraficoDesarquivados.'">' . "\n";
    $strResultadoDesarquivados .= '<caption class="infraCaption">'.$strTituloGraficoDesarquivados.':</caption>';
    $strResultadoDesarquivados .= '<thead><tr>';
    if ($bolAcervo) {
      $strResultadoDesarquivados .= '<th class="infraTh" width="70%">Tipo do Documento</th>' . "\n";
      $strResultadoDesarquivados .= '<th class="infraTh" width="30%">Quantidade</th>' . "\n";
    } else {
      $strResultadoDesarquivados .= '<th class="infraTh" rowspan="2" width="20%">Tipo do Documento</th>' . "\n";
      $strResultadoDesarquivados .= $strHeaderMesAno;
    }
    $strResultadoDesarquivados .= '</thead><tbody>';
    $strResultadoDesarquivados .= $strResultadoTabelaDesarquivados;
    $strResultadoDesarquivados .= '<tr>';
    $strResultadoDesarquivados .= '<td align="right" class="totalEstatisticas"><b>TOTAL:</b></td>';

    $mes = $mesInicial;
    $ano = $anoInicial;
    $n = 0;
    $maximo = 0;
    $arrDadosTemp = array();
    $arrJsDesarquivados['rotulos'][] = 'Total';
    while ($n < $numMeses) {
      $strResultadoDesarquivados .= '<td align="center" class="totalEstatisticas">';
      $valor = $contadorMes[$ano][$mes];
      if ($valor>0){
        $strResultadoDesarquivados .= '<a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDetalhar . '&tipo=desarquivados&ano=' . $ano . '&mes=' . substr($mes, 0, 2) . $strParametros) . '\');" class="ancoraPadraoAzul">' . InfraUtil::formatarMilhares($valor) . '</a>';
      }
      $strResultadoDesarquivados .='</td>';
      if ($valor > $maximo) {
        $maximo = $valor;
      }
      $arrDadosTemp[] = $valor == null ? 0 : $valor;
      $mes++;
      if ($mes == 13) {
        $mes = 1;
        $ano++;
      }
      $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);
      $n++;
    }
    $arrJsDesarquivados['dados'][] = $arrDadosTemp;
    $arrJsDesarquivados['maximos'][] = $maximo;
    $strResultadoDesarquivados .= '<td align="center" class="totalEstatisticas"><a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDetalhar . '&tipo=desarquivados' . $strParametros) . '\');" class="ancoraPadraoAzul">' . InfraUtil::formatarMilhares($total) . '</a></td>';
    if (!$bolAcervo && $numMeses>1) {
      $strLinkGrafico=SessaoSEI::getInstance()->assinarLink('controlador.php?acao=estatisticas_grafico_exibir&num_grafico='.$numGrafico.'&num_linha='.$idArrayGrafico++);
      $strResultadoDesarquivados .= '<td align="center" class="totalEstatisticas"><a href="#divGrf'.$numGrafico.'" onclick="updateGrafico(\''.$strLinkGrafico.'\',this);"><img src="imagens/ver_grafico.gif" title="Gráficos" alt="Gráficos" class="infraImg"></a></td>';
    }
    $strResultadoDesarquivados .= '</tr>';
    $strResultadoDesarquivados .= '</table>';
//----------- Recebidos por mes
    $arrRecebidos = $objEstatisticasArquivamentoDTOret->getArrRecebidos();
    $strCssTr = '';
    $contadorTabelaRecebidos = 0;
    $total = 0;
    $contadorMes = array();
    $idArrayGrafico = 0;
    $arrJsRecebidos = array();
    $arrJsRecebidos['titulo'] = 'Documentos Recebidos';
    $arrJsRecebidos['dados'] = array();
    $arrJsRecebidos['maximos'] = array();
    $arrJsRecebidos['rotulos'] = array();
    if (count($arrRecebidos)>0) {
      $numGrafico++;
    }
    foreach ($arrRecebidos as $keySerie => $arrMeses) {

      if ($arrObjSerieDTO[$keySerie] != null) {
        $strNomeSerie = $arrObjSerieDTO[$keySerie]->getStrNome();
      } else {
        $strNomeSerie = '';
      }

      $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
      $strResultadoTabelaRecebidos .= $strCssTr;
      $strResultadoTabelaRecebidos .= '<td align="left">' . PaginaSEI::tratarHTML($strNomeSerie) . '</td>';
      $arrJsRecebidos['rotulos'][] = utf8_encode(PaginaSEI::tratarHTML($strNomeSerie));
      $arrDadosTemp = array();
      if ($bolAcervo) {
        $totalTipo = $arrMeses;
        $total += $totalTipo;
      } else {
        $mes = $mesInicial;
        $ano = $anoInicial;
        $n = 0;
        $maximo = 0;

        $totalTipo = 0;

        while ($n < $numMeses) {

          $strResultadoTabelaRecebidos .= '<td align="center" width="' . $numTamanhoColuna . '%">';

          if (isset($arrMeses[$ano][$mes])) {

            $strResultadoTabelaRecebidos .= '<a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDetalhar . '&tipo=recebidos&id_serie=' . $keySerie . '&ano=' . $ano . '&mes=' . substr($mes, 0, 2) . $strParametros) . '\');" class="ancoraPadraoAzul">' . $arrMeses[$ano][$mes] . '</a>';
            $valor = $arrMeses[$ano][$mes];
            if ($valor > $maximo) {
              $maximo = $valor;
            }
            $total += $valor;
            $totalTipo += $valor;
            $contadorMes[$ano][$mes] += $valor;
            $arrDadosTemp[] = $valor;

          } else {
            $strResultadoTabelaRecebidos .= '&nbsp;';
            $arrDadosTemp[] = 0;
          }

          $strResultadoTabelaRecebidos .= '</td>';

          $mes++;
          if ($mes == 13) {
            $mes = 1;
            $ano++;
          }
          $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);

          $n++;
        }
        $arrJsRecebidos['dados'][] = $arrDadosTemp;
        $arrJsRecebidos['maximos'][] = $maximo;
      }

      $strLink = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDetalhar . '&tipo=recebidos&id_serie=' . $keySerie . $strParametros);

      $strResultadoTabelaRecebidos .= '<td align="center" width="' . $numTamanhoColuna . '%" class="totalEstatisticas"><a href="javascript:void(0);" onclick="abrirDetalhe(\'' . $strLink . '\');" class="ancoraPadraoAzul">' . InfraUtil::formatarMilhares($totalTipo) . '</a></td>';
      if (!$bolAcervo && $numMeses>1) {
        $strLinkGrafico=SessaoSEI::getInstance()->assinarLink('controlador.php?acao=estatisticas_grafico_exibir&num_grafico='.$numGrafico.'&num_linha='.$idArrayGrafico++);
        $strResultadoTabelaRecebidos .= '<td align="center"><a href="#divGrf'.$numGrafico.'" onclick="updateGrafico(\''.$strLinkGrafico.'\',this);"><img src="imagens/ver_grafico.gif" title="Gráficos" alt="Gráficos" class="infraImg"></a></td>';
      }
      $strResultadoTabelaRecebidos .= '</tr>';

      $strTituloGraficoRecebidos = $bolAcervo?'Documentos recebidos':'Documentos recebidos no período';
      $arrayGraficoRecebidos[] = array($strNomeSerie, InfraUtil::formatarMilhares($totalTipo), $totalTipo, $strLink);

      $contadorTabelaRecebidos++;


    }
    $strResultadoRecebidos = '';
    $strResultadoRecebidos .= '<table id="tblRecebidos" width="' . $numTamanhoTabela . '%" class="infraTable" summary="Tabela de ' . $strTituloGraficoRecebidos . '">' . "\n";
    $strResultadoRecebidos .= '<caption class="infraCaption">' . $strTituloGraficoRecebidos . ':</caption>';
    $strResultadoRecebidos .= '<thead><tr>';
    if ($bolAcervo) {
      $strResultadoRecebidos .= '<th class="infraTh" width="70%">Tipo do Documento</th>' . "\n";
      $strResultadoRecebidos .= '<th class="infraTh" width="30%">Quantidade</th>' . "\n";
    } else {
      $strResultadoRecebidos .= '<th class="infraTh" rowspan="2" width="20%">Tipo do Documento</th>' . "\n";
      $strResultadoRecebidos .= $strHeaderMesAno;
    }
    $strResultadoRecebidos .= '</thead><tbody>';
    $strResultadoRecebidos .= $strResultadoTabelaRecebidos;
    $strResultadoRecebidos .= '<tr>';
    $strResultadoRecebidos .= '<td align="right" class="totalEstatisticas"><b>TOTAL:</b></td>';


    $mes = $mesInicial;
    $ano = $anoInicial;
    $n = 0;
    $maximo = 0;
    $arrDadosTemp = array();
    $arrJsRecebidos['rotulos'][] = 'Total';
    while ($n < $numMeses) {
      $strResultadoRecebidos .= '<td align="center" class="totalEstatisticas">';
      $valor = $contadorMes[$ano][$mes];
      if ($valor>0){
        $strResultadoRecebidos .= '<a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDetalhar . '&tipo=recebidos&ano=' . $ano . '&mes=' . substr($mes, 0, 2) . $strParametros) . '\');" class="ancoraPadraoAzul">' . InfraUtil::formatarMilhares($valor) . '</a>';
      }
      $strResultadoRecebidos .='</td>';

      if ($valor > $maximo) {
        $maximo = $valor;
      }
      $arrDadosTemp[] = $valor == null ? 0 : $valor;
      $mes++;
      if ($mes == 13) {
        $mes = 1;
        $ano++;
      }
      $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);
      $n++;
    }
    $arrJsRecebidos['dados'][] = $arrDadosTemp;
    $arrJsRecebidos['maximos'][] = $maximo;

    $strResultadoRecebidos .= '<td align="center" class="totalEstatisticas"><a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDetalhar . '&tipo=recebidos' . $strParametros) . '\');" class="ancoraPadraoAzul">' . InfraUtil::formatarMilhares($total) . '</a></td>';
    if (!$bolAcervo && $numMeses>1) {
      $strLinkGrafico=SessaoSEI::getInstance()->assinarLink('controlador.php?acao=estatisticas_grafico_exibir&num_grafico='.$numGrafico.'&num_linha='.$idArrayGrafico++);
      $strResultadoRecebidos .= '<td align="center" class="totalEstatisticas"><a href="#divGrf'.$numGrafico.'" onclick="updateGrafico(\''.$strLinkGrafico.'\',this);"><img src="imagens/ver_grafico.gif" title="Gráficos" alt="Gráficos" class="infraImg"></a></td>';
    }
    $strResultadoRecebidos .= '</tr>';
    $strResultadoRecebidos .= '</table>';


    //----------- Localizadores utilizados no período
    $arrLocalizadores = $objEstatisticasArquivamentoDTOret->getArrLocalizadores();
    $contadorTabelaLocalizadores = 0;
    $strCssTr = '';
    $totalAbertos = 0;
    $totalFechados = 0;
    $total = 0;
    foreach ($arrLocalizadores as $keyTipoLocalizador => $arrStaEstado) {

      if ($arrObjTipoLocalizadorDTO[$keyTipoLocalizador] != null) {
        $strNomeTipoLocalizador = $arrObjTipoLocalizadorDTO[$keyTipoLocalizador]->getStrNome();
      } else {
        $strNomeTipoLocalizador = '';
      }

      $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
      $strResultadoTabelaLocalizadores .= $strCssTr;
      $strResultadoTabelaLocalizadores .= '<td align="left">' . PaginaSEI::tratarHTML($strNomeTipoLocalizador) . '</td>';

      $totalTipo = 0;


      $strResultadoTabelaLocalizadores .= '<td align="center">';
      if (isset($arrStaEstado[LocalizadorRN::$EA_ABERTO])) {
        $strResultadoTabelaLocalizadores .= '<a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDetalhar . '&tipo=localizadores&id_tipo_localizador=' . $keyTipoLocalizador . '&sta_estado=' . LocalizadorRN::$EA_ABERTO . $strParametros) . '\');" class="ancoraPadraoAzul">' . $arrStaEstado[LocalizadorRN::$EA_ABERTO] . '</a>';
        $totalTipo += $arrStaEstado[LocalizadorRN::$EA_ABERTO];
        $totalAbertos += $arrStaEstado[LocalizadorRN::$EA_ABERTO];
      } else {
        $strResultadoTabelaLocalizadores .= '&nbsp;';
      }
      $strResultadoTabelaLocalizadores .= '</td>';

      $strResultadoTabelaLocalizadores .= '<td align="center">';
      if (isset($arrStaEstado[LocalizadorRN::$EA_FECHADO])) {
        $strResultadoTabelaLocalizadores .= '<a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDetalhar . '&tipo=localizadores&id_tipo_localizador=' . $keyTipoLocalizador . '&sta_estado=' . LocalizadorRN::$EA_FECHADO . $strParametros) . '\');" class="ancoraPadraoAzul">' . $arrStaEstado[LocalizadorRN::$EA_FECHADO] . '</a>';
        $totalTipo += $arrStaEstado[LocalizadorRN::$EA_FECHADO];
        $totalFechados += $arrStaEstado[LocalizadorRN::$EA_FECHADO];
      } else {
        $strResultadoTabelaLocalizadores .= '&nbsp;';
      }
      $strResultadoTabelaLocalizadores .= '</td>';

      $strLink = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDetalhar . '&tipo=localizadores&id_tipo_localizador=' . $keyTipoLocalizador . $strParametros);

      $strResultadoTabelaLocalizadores .= '<td align="center" class="totalEstatisticas"><a href="javascript:void(0);" onclick="abrirDetalhe(\'' . $strLink . '\');" class="ancoraPadraoAzul">' . InfraUtil::formatarMilhares($totalTipo) . '</a></td>';
      $strResultadoTabelaLocalizadores .= '</tr>';

      $strTituloGraficoLocalizadores = 'Localizadores utilizados';
      $arrayGraficoLocalizadores[] = array($strNomeTipoLocalizador, InfraUtil::formatarMilhares($totalTipo), $totalTipo, $strLink);

      $contadorTabelaLocalizadores++;
    }
    $strResultadoLocalzadores = '';
    $strResultadoLocalzadores .= '<table width="55%" class="infraTable" summary="Tabela de Localizadores utilizados">' . "\n";
    $strResultadoLocalzadores .= '<caption class="infraCaption">Localizadores utilizados:</caption>';
    $strResultadoLocalzadores .= '<thead><tr>';
    $strResultadoLocalzadores .= '<th width="70%" class="infraTh">Tipo de Localizador</th>' . "\n";
    $strResultadoLocalzadores .= '<th width="10%" class="infraTh">Abertos</th>' . "\n";
    $strResultadoLocalzadores .= '<th width="10%" class="infraTh">Fechados</th>' . "\n";
    $strResultadoLocalzadores .= '<th class="infraTh"></th>' . "\n";
    $strResultadoLocalzadores .= '</tr></thead><tbody>';
    $strResultadoLocalzadores .= $strResultadoTabelaLocalizadores;
    $strResultadoLocalzadores .= '<tr>';
    $strResultadoLocalzadores .= '<td align="right" class="totalEstatisticas"><b>TOTAL:</b></td>';

    if ($totalAbertos>0){
      $strResultadoLocalzadores .= '<td align="center" class="totalEstatisticas"><a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDetalhar . '&tipo=localizadores' . '&sta_estado=' . LocalizadorRN::$EA_ABERTO . $strParametros) . '\');" class="ancoraPadraoAzul">' . InfraUtil::formatarMilhares($totalAbertos) . '</a></td>';
    } else {
      $strResultadoLocalzadores .= '<td align="center" class="totalEstatisticas"></td>';
    }
    if ($totalFechados>0) {
      $strResultadoLocalzadores .= '<td align="center" class="totalEstatisticas"><a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDetalhar . '&tipo=localizadores' . '&sta_estado=' . LocalizadorRN::$EA_FECHADO . $strParametros) . '\');" class="ancoraPadraoAzul">' . InfraUtil::formatarMilhares($totalFechados) . '</a></td>';
    } else {
      $strResultadoLocalzadores .= '<td align="center" class="totalEstatisticas"></td>';
    }
    if (($totalAbertos+$totalFechados)>0) {
      $strResultadoLocalzadores .= '<td align="center" class="totalEstatisticas"><a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDetalhar . '&tipo=localizadores' . $strParametros) . '\');" class="ancoraPadraoAzul">' . InfraUtil::formatarMilhares($totalAbertos + $totalFechados) . '</a></td>';
    } else {
      $strResultadoLocalzadores .= '<td align="center" class="totalEstatisticas"></td>';
    }

    $strResultadoLocalzadores .= '</tr></tbody>';
    $strResultadoLocalzadores .= '</table>';

  }

  $numGrafico=0;
  $objEstatisticasRN = new EstatisticasRN();

  $strResultadoGraficoArquivados = '';
  if ($contadorTabelaArquivados > 0){
    $strResultadoGraficoArquivados .= '<div id="divGrf'.(++$numGrafico).'" class="divAreaGrafico">';
    if ($bolAcervo) {
      $strResultadoGraficoArquivados .= $objEstatisticasRN->gerarGraficoBarrasSimples($numGrafico,$strTituloGraficoArquivados,null,$arrayGraficoArquivados, 150, $arrCoresOrgaos);
    }
    $strResultadoGraficoArquivados .= '</div>';
  }

  $strResultadoGraficoDesarquivados = '';
  if ($contadorTabelaDesarquivados > 0){
    $strResultadoGraficoDesarquivados .= '<div id="divGrf'.(++$numGrafico).'" class="divAreaGrafico">';
    if ($bolAcervo) {
      $strResultadoGraficoDesarquivados .= $objEstatisticasRN->gerarGraficoBarrasSimples($numGrafico,$strTituloGraficoDesarquivados,null,$arrayGraficoDesarquivados, 150, $arrCoresOrgaos);
    }
    $strResultadoGraficoDesarquivados .= '</div>';
  }
  
  $strResultadoGraficoRecebidos = '';
  if ($contadorTabelaRecebidos > 0){
    $strResultadoGraficoRecebidos .= '<div id="divGrf'.(++$numGrafico).'" class="divAreaGrafico">';
    if ($bolAcervo) {
      $strResultadoGraficoRecebidos .= $objEstatisticasRN->gerarGraficoBarrasSimples($numGrafico,$strTituloGraficoRecebidos,null,$arrayGraficoRecebidos, 150, $arrCoresOrgaos);
    }
    $strResultadoGraficoRecebidos .= '</div>';
  }

  $strResultadoGraficoLocalizadores = '';
  if ($contadorTabelaLocalizadores > 0){
    $strResultadoGraficoLocalizadores .= '<div id="divGrf'.(++$numGrafico).'" class="divAreaGrafico">';
    $strResultadoGraficoLocalizadores .= $objEstatisticasRN->gerarGraficoBarrasSimples($numGrafico,$strTituloGraficoLocalizadores,null,$arrayGraficoLocalizadores, 150, $arrCoresOrgaos);
    $strResultadoGraficoLocalizadores .= '</div>';
  }



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
.divAreaGrafico DIV { margin:0px; }

#lblPeriodoDe {position:absolute;left:0%;top:55%;width:9%;}
#txtPeriodoDe {position:absolute;left:7%;top:50%;width:9%;}
#imgCalPeriodoD {position:absolute;left:16.5%;top:55%;}

#lblPeriodoA 	{position:relative;left:19%;top:55%;width:9%;}
#txtPeriodoA 	{position:absolute;left:20.5%;top:50%;width:9%;}
#imgCalPeriodoA {position:absolute;left:30%;top:55%;}

#ancAcervo {position:absolute;left:35%;top:55%;}
<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
?>

<script type="text/javascript" src="/infra_js/raphaeljs/raphael-min.js"></script>
<script type="text/javascript" src="/infra_js/raphaeljs/g.raphael-min.js"></script>
<script type="text/javascript" src="/infra_js/raphaeljs/g.bar-min.js"></script>
<script type="text/javascript" src="/infra_js/raphaeljs/g.line-min.js"></script>
<?
PaginaSEI::getInstance()->abrirJavaScript();
?>
//<script>

  function labelBarChart(r, bc, labels, attrs) {
    // Label a bar chart bc that is part of a Raphael object r
    // Labels is an array of strings. Attrs is a dictionary
    // that provides attributes such as fill (text color)
    // and font (text font, font-size, font-weight, etc) for the
    // label text.

    for (var i = 0; i< bc.bars[0].length; i++) {
        var bar = bc.bars[0][i];
        var gutter_y = bar.w * 0.4;
        var label_x = bar.x;
        var label_y = bar.y  - gutter_y;
        var label_text = bar.value;
        var label_attr = { fill:  "#2f69bf", font: "11px sans-serif" };

        r.text(label_x, label_y, label_text).attr(label_attr);
    }

}

var r,lines;
var arrGraficos = [];
  <?
  if (!$bolAcervo){
  ?>
  var arrEixo =<?=json_encode($arrRotuloEixoX);?>;

  <?
    if (count($arrArquivados) > 0) {
      echo 'arrGraficos.push(' . json_encode($arrJsArquivados) . ');' . "\n";
    }
    if (count($arrDesarquivados) > 0) {
      echo 'arrGraficos.push(' . json_encode($arrJsDesarquivados) . ');' . "\n";
    }
    if (count($arrRecebidos) > 0) {
      echo 'arrGraficos.push(' . json_encode($arrJsRecebidos) . ');' . "\n";
    }


  }
  ?>
  var grafico=[];

  function updateGrafico(link,cell){
    var windowname=$(cell).closest('table').attr('id').substr(0,6) + $(cell).closest('tr').prop('rowIndex');
    var janela=infraAbrirJanela('',windowname,830,300,'',false);
    if (janela.location == 'about:blank'){
      janela.location.href = link;
    }
    janela.focus();
  }
function inicializar(){

  if ('<?=$_GET['acao_origem']?>'!='gerar_estatisticas_unidade' && '<?=$_GET['acao_origem']?>'!='gerar_estatisticas_ouvidoria'){
    infraOcultarMenuSistemaEsquema();
  }

  infraAdicionarEvento(window,'resize',seiRedimensionarGraficos);
  infraProcessarResize();
  infraAviso();
  infraEfeitoTabelas();
  seiRedimensionarGraficos();
  $('[id^="btnOcultar"]').click();



}

function abrirDetalhe(link){
 infraAbrirJanela(link,'janelaEstatisticasDetalhe',750,550,'location=0,status=1,resizable=1,scrollbars=1');
}
function verAcervo(){
  var hdn=$('#hdnAcervo');
  hdn.val('acervo');
  $('#txtPeriodoDe').val('');
  $('#txtPeriodoA').val('');
  $('#frmEstatisticas').attr('onsubmit','').submit();
  hdn.val('');

}
function validarFormulario(){
  if ($('#hdnAcervo').val()=='acervo'){
    return true;
  }

	if(infraTrim(document.getElementById('txtPeriodoDe').value) == "" || infraTrim(document.getElementById('txtPeriodoA').value)=="") {
		
		alert("Informe o período de datas.");
	  
	  if(infraTrim(document.getElementById('txtPeriodoDe').value) == ""){
	    document.getElementById('txtPeriodoDe').focus();
	  }else{
	    document.getElementById('txtPeriodoA').focus();
	  }
		
		return false;
	}
	
	infraExibirAviso();	
	return true;
}



//</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmEstatisticas" onsubmit="return validarFormulario();" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?
  //PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->abrirAreaDados('5em');
  ?>

    <label id="lblPeriodoDe" for="txtPeriodoDe" accesskey="" class="infraLabelObrigatorio">Período:</label>
    <input type="text" id="txtPeriodoDe" name="txtPeriodoDe" class="infraText" value="<?=PaginaSEI::tratarHTML($dtaPeriodoDe)?>" onkeypress="return infraMascaraData(this, event)" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
  
		<img id="imgCalPeriodoD" title="Selecionar Data Inicial" alt="Selecionar Data Inicial" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/calendario.gif" class="infraImg" onclick="infraCalendario('txtPeriodoDe',this);" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />    
    
    <label id="lblPeriodoA" for="txtPeriodoA" accesskey="" class="infraLabelObrigatorio">a</label>
    <input type="text" id="txtPeriodoA" name="txtPeriodoA" class="infraText" value="<?=PaginaSEI::tratarHTML($dtaPeriodoA)?>" onkeypress="return infraMascaraData(this, event)" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
    
    <img id="imgCalPeriodoA" title="Selecionar Data Final" alt="Selecionar Data Final" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/calendario.gif" class="infraImg" onclick="infraCalendario('txtPeriodoA',this);" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
    <a id="ancAcervo" href="javascript:void(0);" onclick="verAcervo();" class="ancoraPadraoPreta" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">Ver acervo completo</a>
    <input type="hidden" id="hdnAcervo" name="hdnAcervo" />
  <?
  PaginaSEI::getInstance()->fecharAreaDados();
  
  ?>
  <div id="divTabelas">
  <?

  echo '<div id="divSeparador" style="float:left;padding:1em"></div>';
  

    
  if ($contadorTabelaArquivados > 0) {
  	echo '<br /><br />';
		PaginaSEI::getInstance()->montarAreaTabela($strResultadoArquivados,$contadorTabelaArquivados);
    if($bolAcervo) EstatisticasINT::montarGrafico('Arquivados',$strResultadoGraficoArquivados);
  }

  if ($contadorTabelaDesarquivados > 0) {
    echo '<br /><br />';
    PaginaSEI::getInstance()->montarAreaTabela($strResultadoDesarquivados,$contadorTabelaDesarquivados);
    if($bolAcervo) EstatisticasINT::montarGrafico('Desarquivados',$strResultadoGraficoDesarquivados);
  }

  if ($contadorTabelaRecebidos > 0) {
    echo '<br /><br />';
    PaginaSEI::getInstance()->montarAreaTabela($strResultadoRecebidos,$contadorTabelaRecebidos);
    if($bolAcervo) EstatisticasINT::montarGrafico('Recebidos',$strResultadoGraficoRecebidos);
  }

  if (count($arrLocalizadores) > 0) {
  	echo '<br /><br />';
		PaginaSEI::getInstance()->montarAreaTabela($strResultadoLocalzadores,count($arrLocalizadores));
		EstatisticasINT::montarGrafico('Localizadores',$strResultadoGraficoLocalizadores);
  }

  ?>
  </div>
  <?
  PaginaSEI::getInstance()->montarAreaDebug();
  //PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);

  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>