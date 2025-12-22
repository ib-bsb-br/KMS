<?
require_once dirname(__FILE__).'/../SEI.php';

class SolrPublicacao {

  public static function executar(PesquisaPublicacaoSolrDTO $objPesquisaPublicacaoSolrDTO) {

    //die($objPesquisaPublicacaoSolrDTO->__toString());

    $partialfields = '';

    $arr = array();
    foreach($objPesquisaPublicacaoSolrDTO->getArrNumIdOrgao() as $numIdOrgao){
      array_push($arr, "id_org_resp:".$numIdOrgao);
    }
    if (count($arr)>0){
      $partialfields .= '('.implode(" OR ", $arr).')';
    }


    if ($objPesquisaPublicacaoSolrDTO->getStrResumo()!=null){
      if ($partialfields!=''){
        $partialfields .= ' AND ';
      }
      $partialfields .= '('.SolrUtil::formatarOperadores($objPesquisaPublicacaoSolrDTO->getStrResumo(),'resumo').')';
    }

    if ($objPesquisaPublicacaoSolrDTO->getNumIdUnidadeResponsavel()!=null){
      if ($partialfields!=''){
        $partialfields .= ' AND ';
      }
      $partialfields .= '(id_uni_resp:'.$objPesquisaPublicacaoSolrDTO->getNumIdUnidadeResponsavel().')';
    }

    if ($objPesquisaPublicacaoSolrDTO->getNumIdSerie()!=null){
      if ($partialfields!=''){
        $partialfields .= ' AND ';
      }
      $partialfields .= '(id_serie:'.$objPesquisaPublicacaoSolrDTO->getNumIdSerie().')';
    }

    if ($objPesquisaPublicacaoSolrDTO->getStrNumero()!=null){
      if ($partialfields!=''){
        $partialfields .= ' AND ';
      }
      $partialfields .= '(numero:*'.$objPesquisaPublicacaoSolrDTO->getStrNumero().'*)';
    }

    if ($objPesquisaPublicacaoSolrDTO->getStrProtocoloPesquisa()!=null){
      if ($partialfields!=''){
        $partialfields .= ' AND ';
      }
      $partialfields .= '(prot_pesq:*'.InfraUtil::retirarFormatacao($objPesquisaPublicacaoSolrDTO->getStrProtocoloPesquisa(),false).'*)';
    }

    if ($objPesquisaPublicacaoSolrDTO->getNumIdVeiculoPublicacao()!=null){
      if ($partialfields!=''){
        $partialfields .= ' AND ';
      }
      $partialfields .= '(id_veic_pub:'.$objPesquisaPublicacaoSolrDTO->getNumIdVeiculoPublicacao().')';
    }

    if ($objPesquisaPublicacaoSolrDTO->getDtaGeracao()!=null){
      if ($partialfields!=''){
        $partialfields .= ' AND ';
      }

      $dia = substr($objPesquisaPublicacaoSolrDTO->getDtaGeracao(), 0, 2);
      $mes = substr($objPesquisaPublicacaoSolrDTO->getDtaGeracao(), 3, 2);
      $ano = substr($objPesquisaPublicacaoSolrDTO->getDtaGeracao(), 6, 4);

      $partialfields .=	'dta_doc:"' . $ano . '-' . $mes . '-' . $dia . 'T00:00:00Z"';
    }

    if ($objPesquisaPublicacaoSolrDTO->getStrStaTipoData()=='H') {
      if ($partialfields != '') {
        $partialfields .= ' AND ';
      }

      $dia = substr(InfraData::getStrDataAtual(), 0, 2);
      $mes = substr(InfraData::getStrDataAtual(), 3, 2);
      $ano = substr(InfraData::getStrDataAtual(), 6, 4);

      $partialfields .= 'dta_pub:"' . $ano . '-' . $mes . '-' . $dia . 'T00:00:00Z"';
    }

    if ($objPesquisaPublicacaoSolrDTO->getStrStaTipoData()=='E'){
      $dtaInicio = $objPesquisaPublicacaoSolrDTO->getDtaInicio();
      $dtaFim = $objPesquisaPublicacaoSolrDTO->getDtaFim();

      if ($dtaInicio!=null && $dtaFim!=null) {
        $dia1 = substr($dtaInicio, 0, 2);
        $mes1 = substr($dtaInicio, 3, 2);
        $ano1 = substr($dtaInicio, 6, 4);

        $dia2 = substr($dtaFim, 0, 2);
        $mes2 = substr($dtaFim, 3, 2);
        $ano2 = substr($dtaFim, 6, 4);

        if ($partialfields != '') {
          $partialfields .= ' AND ';
        }

        $partialfields .= 'dta_pub:[' . $ano1 . '-' . $mes1 . '-' . $dia1 . 'T00:00:00Z TO ' . $ano2 . '-' . $mes2 . '-' . $dia2 . 'T00:00:00Z]';
      }
    }

    //die($partialfields);

    $parametros = new stdClass();
    $parametros->q = SolrUtil::formatarOperadores($objPesquisaPublicacaoSolrDTO->getStrPalavrasChave());

    if ($parametros->q != '' && $partialfields != ''){
      $parametros->q = '('.$parametros->q.') AND '.$partialfields;
    }else if ($partialfields != ''){
      $parametros->q = $partialfields;
    }

    $parametros->q = utf8_encode($parametros->q);
    $parametros->start = $objPesquisaPublicacaoSolrDTO->getNumInicioPaginacao();
    $parametros->rows = 20;
    $parametros->sort =  'id_pub desc';

    $urlBusca = ConfiguracaoSEI::getInstance()->getValor('Solr','Servidor') . '/'.ConfiguracaoSEI::getInstance()->getValor('Solr','CorePublicacoes') .'/select?' . http_build_query($parametros).'&hl=true&hl.snippets=2&hl.fl=content&hl.fragsize=100&hl.maxAnalyzedChars=1048576&hl.alternateField=content&hl.maxAlternateFieldLength=100&fl=id,id_doc,id_pub,id_pub_leg,id_prot_agrup,dta_doc,id_org_resp,id_serie,id_uni_resp,numero,prot_doc,dta_pub,num_pub,id_veic_pub,resumo,id_veic_io,dta_pub_io,id_sec_io,pag_io';

    //InfraDebug::getInstance()->gravar('URL:'.$urlBusca);
    //InfraDebug::getInstance()->gravar("PARÂMETROS: " . print_r($parametros, true));

    try{
      $resultados = file_get_contents($urlBusca, false);
    }catch(Exception $e){
      throw new InfraException('Erro realizando pesquisa.',$e, urldecode($urlBusca),false);
    }

    if ($resultados == ''){
      throw new InfraException('Nenhum retorno encontrado no resultado da pesquisa.');
    }
    
    $xml = simplexml_load_string($resultados);

    $arrRet = $xml->xpath('/response/result/@numFound');
    
    $itens = array_shift($arrRet);

    $arrObjResultadoPublicacaoSolrDTO = array();

    if ($itens > 0) {

      $registros = $xml->xpath('/response/result/doc');

      $numRegistros = count($registros);

      for ($i = 0; $i < $numRegistros; $i++) {

        $id = SolrUtil::obterTag($registros[$i], 'id', 'str');

        $objResultadoPublicacaoSolrDTO = new ResultadoPublicacaoSolrDTO();
        $objResultadoPublicacaoSolrDTO->setDblIdDocumento(SolrUtil::obterTag($registros[$i], "id_doc", 'long'));
        $objResultadoPublicacaoSolrDTO->setNumIdPublicacao(SolrUtil::obterTag($registros[$i], "id_pub", 'int'));
        $objResultadoPublicacaoSolrDTO->setNumIdPublicacaoLegado(SolrUtil::obterTag($registros[$i], "id_pub_leg", 'int'));
        $objResultadoPublicacaoSolrDTO->setDblIdProtocoloAgrupador(SolrUtil::obterTag($registros[$i], "id_prot_agrup", 'long'));
        $objResultadoPublicacaoSolrDTO->setNumIdOrgaoResponsavel(SolrUtil::obterTag($registros[$i], "id_org_resp", 'int'));
        $objResultadoPublicacaoSolrDTO->setNumIdUnidadeResponsavel(SolrUtil::obterTag($registros[$i], "id_uni_resp", 'int'));
        $objResultadoPublicacaoSolrDTO->setNumIdSerie(SolrUtil::obterTag($registros[$i], "id_serie", 'int'));
        $objResultadoPublicacaoSolrDTO->setStrNumero(SolrUtil::obterTag($registros[$i], "numero", 'str'));
        $objResultadoPublicacaoSolrDTO->setStrProtocoloDocumentoFormatado(SolrUtil::obterTag($registros[$i], "prot_doc", 'str'));
        $objResultadoPublicacaoSolrDTO->setDtaDocumento(preg_replace("/(\d{4})-(\d{2})-(\d{2})(.*)/", "$3/$2/$1", SolrUtil::obterTag($registros[$i], "dta_doc", 'date')));
        $objResultadoPublicacaoSolrDTO->setDtaPublicacao(preg_replace("/(\d{4})-(\d{2})-(\d{2})(.*)/", "$3/$2/$1", SolrUtil::obterTag($registros[$i], "dta_pub", 'date')));
        $objResultadoPublicacaoSolrDTO->setNumNumeroPublicacao(SolrUtil::obterTag($registros[$i], "num_pub", 'str'));
        $objResultadoPublicacaoSolrDTO->setNumIdVeiculoPublicacao(SolrUtil::obterTag($registros[$i], "id_veic_pub", 'int'));
        $objResultadoPublicacaoSolrDTO->setStrResumo(SolrUtil::obterTag($registros[$i], "resumo", 'str'));
        $objResultadoPublicacaoSolrDTO->setNumIdVeiculoIO(SolrUtil::obterTag($registros[$i], "id_veic_io", 'int'));
        $objResultadoPublicacaoSolrDTO->setDtaPublicacaoIO(preg_replace("/(\d{4})-(\d{2})-(\d{2})(.*)/", "$3/$2/$1", SolrUtil::obterTag($registros[$i], "dta_pub_io", 'date')));
        $objResultadoPublicacaoSolrDTO->setNumIdSecaoIO(SolrUtil::obterTag($registros[$i], "id_sec_io", 'int'));
        $objResultadoPublicacaoSolrDTO->setStrPaginaIO(SolrUtil::obterTag($registros[$i], "pag_io", 'str'));

        // SNIPPET
        $temp = $xml->xpath("/response/lst[@name='highlighting']/lst[@name='" . $id . "']/arr[@name='content']/str");

        $snippet = '';
        for ($j = 0; $j < count($temp); $j++) {
          $snippetTemp = utf8_decode($temp[$j]);
          $snippetTemp = strtoupper(trim(strip_tags($snippetTemp))) == "NULL" ? null : $snippetTemp;
          $snippetTemp = preg_replace("/<br>/i", "<br />", $snippetTemp);
          $snippetTemp = preg_replace("/&lt;.*?&gt;/", "", $snippetTemp);
          $snippet .= $snippetTemp . '<b>&nbsp;&nbsp;...&nbsp;&nbsp;</b>';
        }

        $objResultadoPublicacaoSolrDTO->setStrSnippet($snippet);
        $arrObjResultadoPublicacaoSolrDTO[] = $objResultadoPublicacaoSolrDTO;
      }
    }

    $objResultadoPesquisaSolrDTO = new ResultadoPesquisaSolrDTO();
    $objResultadoPesquisaSolrDTO->setStrCabecalho(SolrUtil::criarBarraEstatisticas($itens,$parametros->start,($parametros->start+$parametros->rows)));
    $objResultadoPesquisaSolrDTO->setArrObjInfraDTO($arrObjResultadoPublicacaoSolrDTO);
    $objResultadoPesquisaSolrDTO->setStrRodape(SolrUtil::criarBarraNavegacao($itens, $parametros->start, $parametros->rows));

    return $objResultadoPesquisaSolrDTO;
  }
}
?>