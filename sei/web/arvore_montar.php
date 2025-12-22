<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
*/

try {
  require_once dirname(__FILE__).'/SEI.php';

  session_start();

  //if (SessaoSEI::getInstance()->getStrSiglaOrgaoUsuario()=='XXXX' && SessaoSEI::getInstance()->getStrSiglaUsuario()=='xxxx'){
  //////////////////////////////////////////////////////////////////////////////
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(false);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////
  //}

  PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);

  SessaoSEI::getInstance()->validarLink();

  //SessaoSEI::getInstance()->validarAuditarPermissao($_GET['acao']);

  //$numSegOrig = $numSeg = InfraUtil::verificarTempoProcessamento();

  switch($_GET['acao']){

    case 'procedimento_paginar':
      try{

        if (!isset($_POST['hdnProtocolos'])){
          die;
        }

        $strNos = '';
        $strNosAcao = '';

        $numNo = 0;
        $numNoAcao = 0;

        if (md5($_POST['hdnProtocolos']) != $_GET['pagina_hash']){
          throw new InfraException('Conjunto de protocolos inválido ['.substr($_POST['hdnProtocolos'],0,10).'...].');
        }

        ProtocoloINT::montarAcoesArvore($_GET['id_procedimento'],
                                        $_GET['id_unidade'],
                                        $_GET['flag_aberto'],
                                        $_GET['flag_anexado'],
                                        $_GET['flag_aberto_anexado'],
                                        $_GET['flag_protocolo'],
                                        $_GET['flag_arquivo'],
                                        $_GET['flag_tramitacao'],
                                        $_GET['flag_sobrestado'],
                                        $_GET['flag_bloqueado'],
                                        $_GET['codigo_acesso'],
                                        $_GET['no_pai'],
                                        explode(',',$_POST['hdnProtocolos']),
                                        $numNo, $strNos,
                                        $numNoAcao, $strNosAcao);

        die('OK <!--//--><![CDATA[//><!--'."\n".$strNos."\n".$strNosAcao."\n".'//--><!]]>');

      }catch(Exception $e){

        if ($e instanceof InfraException && $e->contemValidacoes()){
          die("INFRA_VALIDACAO\n".$e->__toString()); //retorna para o iframe exibir o alert
        }

        PaginaSEI::getInstance()->processarExcecao($e); //vai para a página de erro padrão
      }

      break;

    case 'procedimento_visualizar':

      $strTitulo = 'Árvore Montar';

      $objAuditoriaProtocoloDTO = new AuditoriaProtocoloDTO();
      $objAuditoriaProtocoloDTO->setStrRecurso($_GET['acao']);
      $objAuditoriaProtocoloDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
      $objAuditoriaProtocoloDTO->setDblIdProtocolo($_GET['id_procedimento']);
      $objAuditoriaProtocoloDTO->setNumIdAnexo(null);
      $objAuditoriaProtocoloDTO->setDtaAuditoria(InfraData::getStrDataAtual());
      $objAuditoriaProtocoloDTO->setNumVersao(null);

      $objAuditoriaProtocoloRN = new AuditoriaProtocoloRN();
      $objAuditoriaProtocoloRN->auditarVisualizacao($objAuditoriaProtocoloDTO);

      /*
      if ($_GET['acao_origem']!='procedimento_trabalhar' &&
          $_GET['acao_origem']!='procedimento_visualizar' &&
          $_GET['acao_origem']!='arvore_visualizar' &&
          $_GET['acao_origem']!='documento_assinar' &&
          $_GET['acao_origem']!='editor_montar' &&
      		$_GET['acao_origem']!='distribuicao_gerar' &&
          $_GET['acao_origem']!='item_sessao_julgamento_cadastrar' &&
        	$_GET['acao_origem']!='procedimento_relacionar' &&
        	$_GET['acao_origem']!='procedimento_anexar' &&
        	$_GET['acao_origem']!='documento_mover' &&
        	$_GET['acao_origem']!='procedimento_excluir_relacionamento' &&
        	$_GET['acao_origem']!='publicacao_cancelar_agendamento' &&
        	$_GET['acao_origem']!='procedimento_credencial_gerenciar' &&
        	$_GET['acao_origem']!='procedimento_credencial_conceder'){
      	throw new InfraException('Erro no acesso ao processo ['.$_GET['acao_origem'].'].');
      }
      */

      $numIdUnidadeAtual = SessaoSEI::getInstance()->getNumIdUnidadeAtual();

      $bolAcaoProcedimentoReceber = SessaoSEI::getInstance()->verificarPermissao('procedimento_receber');

      $dblIdProcedimento = $_GET['id_procedimento'];

      $dblIdProtocoloPosicionar = '';
      if (isset($_GET['id_documento']) && $_GET['id_documento']!=''){
        $dblIdProtocoloPosicionar = $_GET['id_documento'];
      }else if(isset($_GET['id_procedimento_anexado']) && $_GET['id_procedimento_anexado']!=''){
        $dblIdProtocoloPosicionar = $_GET['id_procedimento_anexado'];
      }

      $strNos = '';
      $strNosAcao = '';
      $strJsArrPastas = '';
      $numNo = 0;
      $numNoAcao = 0;

      $strOcultarAbrirFechar = '';
      $strNumPastasAbertas = '';

      $bolFlagAberto = false;
      $bolFlagAnexado = false;
      $bolFlagAbertoAnexado = false;
      $bolFlagProtocolo = false;
      $bolFlagArquivo = false;
      $bolFlagTramitacao = false;
      $bolFlagSobrestado = false;
      $bolFlagBloqueado = false;
      $bolErro = false;
      $numCodigoAcesso = 0;

      $objProcedimentoDTO = ProcedimentoINT::montarAcoesArvore($dblIdProcedimento,
                                                               $numIdUnidadeAtual,
                                                               $bolFlagAberto,
                                                               $bolFlagAnexado,
                                                               $bolFlagAbertoAnexado,
                                                               $bolFlagProtocolo,
                                                               $bolFlagArquivo,
                                                               $bolFlagTramitacao,
                                                               $bolFlagSobrestado,
                                                               $bolFlagBloqueado,
                                                               $numCodigoAcesso,
                                                               $numNo, $strNos,
                                                               $numNoAcao, $strNosAcao,
                                                               $bolErro);

      $arrPastas = array();
      $arrPastasAbertas = array();

      if (!$bolErro && $objProcedimentoDTO!=null){

        $arrObjRelProtocoloProtocoloDTO = $objProcedimentoDTO->getArrObjRelProtocoloProtocoloDTO();

      	$numTotalProtocolos = count($arrObjRelProtocoloProtocoloDTO);

      	$objInfraParametro = new InfraParametro(BancoSEI::getInstance());
      	$numMaxDocPasta = $objInfraParametro->getValor('SEI_NUM_MAX_DOCS_PASTA');

      	if ($numMaxDocPasta == ''){
      	  $numMaxDocPasta = $numTotalProtocolos;
      	}

      	$bolAbrirPastas = (isset($_GET['abrir_pastas']) && $_GET['abrir_pastas']=='1');
      	$bolFecharPastas = (isset($_GET['fechar_pastas']) && $_GET['fechar_pastas']=='1');


      	if ($numTotalProtocolos > $numMaxDocPasta){

      	  $numPastaAtual = 0;

      	  for($i=0;$i<$numTotalProtocolos;$i++){

      	    if ($i==0 || ($i>=$numMaxDocPasta && $i%$numMaxDocPasta==0)){

      	      $strAberto = 'false';

      	      $numPastaAtual++;

      	      if (!$bolFecharPastas){
        	      if ($bolAbrirPastas || ($numPastaAtual*$numMaxDocPasta)>=$numTotalProtocolos){
        	        $strAberto = 'true';
        	        $arrPastasAbertas[] = $numPastaAtual;
        	      }
      	      }

      	      if ($dblIdProtocoloPosicionar!='' && $strAberto=='false'){

      	        $k = $i + $numMaxDocPasta;

      	        if ($k > $numTotalProtocolos){
      	          $k = $numTotalProtocolos;
      	        }

      	        //se posicionando em um documento/processo de uma pasta intermediária
      	        for($j=$i;$j<$k;$j++){
      	          if ($arrObjRelProtocoloProtocoloDTO[$j]->getDblIdProtocolo2()==$dblIdProtocoloPosicionar){
      	            $strAberto = 'true';
      	            $arrPastasAbertas[] = $numPastaAtual;
      	            break;
      	          }
      	        }
      	      }

      	      $strNos .= 'Nos['.$numNo.'] = new infraArvoreNo("PASTA",'.
      	          '"PASTA'.$numPastaAtual.'",'.
      	          '"'.$dblIdProcedimento.'",'.
      	          '"javascript:abrirFecharPasta(\'PASTA'.$numPastaAtual.'\');",'.
      	          'null,'.
      	          '"'.InfraUtil::converterNumeroDecimalParaRomano($numPastaAtual).'",'.
      	          '"'.$numPastaAtual.'",'.
      	          'null,'.
      	          'null,'.
      	          'null,'.
      	          $strAberto.','.
      	          'true,'.
      	          'null,'.
      	          'null,'.
      	          'null);'."\n";

      	      $strNos .= 'Nos['.$numNo.'].carregado = '.$strAberto.';'."\n";
      	      $numNo++;

      	      if ($strAberto=='false'){
      	        $strNos .= 'Nos['.$numNo++.'] = new infraArvoreNo("AGUARDE",'.
      	            '"AGUARDE'.$numPastaAtual.'",'.
      	            '"PASTA'.$numPastaAtual.'",'.
      	            'null,'.
      	            'null,'.
      	            '"Aguarde...",'.
      	            '"Aguarde...",'.
      	            '"'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/aguarde_pequeno.gif",'.
      	            'null,'.
      	            'null,'.
      	            'false,'.
      	            'true,'.
      	            'null,'.
      	            'null,'.
      	            'null);'."\n";
      	      }
      	    }

            $arrPastas[$numPastaAtual][] = $arrObjRelProtocoloProtocoloDTO[$i]->getDblIdRelProtocoloProtocolo();
	      	}

      	  $strNosAcao .= 'NosAcoes['.$numNoAcao++.'] = new infraArvoreAcao("ABRIR_PASTAS",'.
                                                                  	       '"AP'.$dblIdProcedimento.'",'.
                                                                  	       '"'.$dblIdProcedimento.'",'.
                                                                  	       '"'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_visualizar&acao_origem=procedimento_visualizar&id_procedimento='.$dblIdProcedimento.'&abrir_pastas=1').'",'.
                                                                  	       'null,'.
                                                                  	       '"Abrir todas as Pastas",'.
                                                                  	       '"'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/mais.gif",'.
                                                                  	       'true);'."\n";

      	  $strNosAcao .= 'NosAcoes['.$numNoAcao++.'] = new infraArvoreAcao("FECHAR_PASTAS",'.
                                                                  	       '"FP'.$dblIdProcedimento.'",'.
                                                                  	       '"'.$dblIdProcedimento.'",'.
                                                                  	       '"'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_visualizar&acao_origem=procedimento_visualizar&id_procedimento='.$dblIdProcedimento.'&fechar_pastas=1').'",'.
                                                                  	       'null,'.
                                                                  	       '"Fechar todas as Pastas",'.
                                                                  	       '"'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/menos.gif",'.
                                                                  	       'true);'."\n";


      	  if ($bolAbrirPastas){
      	    $strOcultarAbrirFechar = 'document.getElementById(\'anchorAP'.$dblIdProcedimento.'\').style.display=\'none\';';
      	  }else if ($bolFecharPastas){
      	    $strOcultarAbrirFechar = 'document.getElementById(\'anchorFP'.$dblIdProcedimento.'\').style.display=\'none\';';
      	  }

      	  $strNumPastasAbertas = 'objArvore.numPastasAbertas='.count($arrPastasAbertas).';';


      	  foreach($arrPastasAbertas as $numPastaAberta){
      	    ProtocoloINT::montarAcoesArvore($dblIdProcedimento,
      	                                    $numIdUnidadeAtual,
      	                                    $bolFlagAberto,
      	                                    $bolFlagAnexado,
      	                                    $bolFlagAbertoAnexado,
      	                                    $bolFlagProtocolo,
                                            $bolFlagArquivo,
                                            $bolFlagTramitacao,
                                            $bolFlagSobrestado,
                                            $bolFlagBloqueado,
                                            $numCodigoAcesso,
      	                                    'PASTA'.$numPastaAberta,
      	                                    $arrPastas[$numPastaAberta],
      	                                    $numNo, $strNos,
      	                                    $numNoAcao, $strNosAcao);
      	  }

      	}else{
      	  ProtocoloINT::montarAcoesArvore($dblIdProcedimento,
      	                                  $numIdUnidadeAtual,
      	                                  $bolFlagAberto,
      	                                  $bolFlagAnexado,
                                          $bolFlagAbertoAnexado,
      	                                  $bolFlagProtocolo,
                                          $bolFlagArquivo,
                                          $bolFlagTramitacao,
                                          $bolFlagSobrestado,
                                          $bolFlagBloqueado,
                                          $numCodigoAcesso,
      	                                  $dblIdProcedimento,
      	                                  InfraArray::converterArrInfraDTO($arrObjRelProtocoloProtocoloDTO,'IdRelProtocoloProtocolo'),
      	                                  $numNo, $strNos,
      	                                  $numNoAcao, $strNosAcao);
      	}

        //Ação de consulta de andamento
        $bolAcaoHistoricoProcedimento = SessaoSEI::getInstance()->verificarPermissao('procedimento_consultar_historico');

      	if ($bolAcaoHistoricoProcedimento){
      	  $strConsultarAndamento = '<a style="cursor:pointer;" onclick="consultarAndamento();"><img src="/infra_css/imagens/lupa.gif" alt="Consultar Andamento" title="Consultar Andamento" class="infraImg" /><span style="font-size:1.2em"> Consultar Andamento</span></a>'."\n";
      		$strLinkHistorio = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_consultar_historico&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$dblIdProcedimento.'&arvore=1');
        }

        //Relacionamentos
        $objProcedimentoDTORelacionado = new ProcedimentoDTO();
        $objProcedimentoDTORelacionado->setDblIdProcedimento($_GET['id_procedimento']);

        $objProcedimentoRN = new ProcedimentoRN();
        $arrObjRelProtocoloProtocoloDTO = $objProcedimentoRN->listarRelacionados($objProcedimentoDTORelacionado);

        $strRelacionamentosTitulo = '';
        $strRelacionamentos = '';

        if (count($arrObjRelProtocoloProtocoloDTO)){
          $arrRelacionamentos = array();
          foreach($arrObjRelProtocoloProtocoloDTO as $objRelProtocoloProtocoloDTO){

            if ($objRelProtocoloProtocoloDTO->getObjProtocoloDTO1()!=null){
              $objProcedimentoDTORelacionado = $objRelProtocoloProtocoloDTO->getObjProtocoloDTO1();
            }else{
              $objProcedimentoDTORelacionado = $objRelProtocoloProtocoloDTO->getObjProtocoloDTO2();
            }

            $strClassRelacionamento = '';
            if ($objProcedimentoDTORelacionado->getStrSinAberto()=='S'){
              $strClassRelacionamento = 'protocoloAberto';
            }else{
              $strClassRelacionamento = 'protocoloFechado';
            }

            $arrRelacionamentos[$objProcedimentoDTORelacionado->getStrNomeTipoProcedimento()][] = '<a target="_blank" href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=procedimento_visualizar&id_procedimento='.$objProcedimentoDTORelacionado->getDblIdProcedimento()).'" '.PaginaSEI::montarTitleTooltip($objProcedimentoDTORelacionado->getStrDescricaoProtocolo()).' class="'.$strClassRelacionamento.'">'.$objProcedimentoDTORelacionado->getStrProtocoloProcedimentoFormatado().'</a><br />'."\n";
          }

          $numRelacionado = 0;

          $strRelacionamentos .= '<div id="divRelacionadosParciais">'."\n";
          foreach($arrRelacionamentos as $strIdentificacaoRelacionado => $arrLinksRelacionados){
            $strRelacionamentos .= '<a href="javascript:void(0);" onclick="visualizacaoRelacionados('.$numRelacionado.');" class="ancoraRelacionadosParcial">'.PaginaSEI::tratarHTML($strIdentificacaoRelacionado).' ('.count($arrLinksRelacionados).')</a><br />';
            $strRelacionamentos .= '<div id="divRelacionadosParcial'.$numRelacionado.'" class="divRelacionadosParcial">'."\n";
            foreach($arrLinksRelacionados as $strLinkRelacionado){
              $strRelacionamentos .= $strLinkRelacionado;
            }
            $strRelacionamentos .= '</div>'."\n";
            $numRelacionado++;
          }
          $strRelacionamentos .= '</div>'."\n";

          if ($strRelacionamentos != ''){
            $strRelacionamentosTitulo = '<label>Processos Relacionados:</label> <br />';
          }
        }

	    	if ($bolFlagAberto && $bolAcaoProcedimentoReceber){
	        $objProcedimentoRN->receber($objProcedimentoDTO);
	    	}

	    	if (count($arrPastas)){
	    	  $strJsArrPastas = '  var Pastas = [];'."\n\n";
	    	  foreach($arrPastas as $numPasta => $arrIdRelProtocoloProtocolo){
	    	    $strIdRelProtocoloProtocolo = implode(',',$arrIdRelProtocoloProtocolo);
	    	    $strJsArrPastas .= '  Pastas['.$numPasta.'] = [];'."\n";
	    	    $strJsArrPastas .= '  Pastas['.$numPasta.'][\'link\'] = \''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_paginar&id_procedimento='.$dblIdProcedimento.'&id_unidade='.$numIdUnidadeAtual.'&flag_aberto='.$bolFlagAberto.'&flag_anexado='.$bolFlagAnexado.'&flag_aberto_anexado='.$bolFlagAbertoAnexado.'&flag_protocolo='.$bolFlagProtocolo.'&flag_arquivo='.$bolFlagArquivo.'&flag_tramitacao='.$bolFlagTramitacao.'&flag_sobrestado='.$bolFlagSobrestado.'&flag_bloqueado='.$bolFlagBloqueado.'&codigo_acesso='.$numCodigoAcesso.'&no_pai=PASTA'.$numPasta.'&pagina_hash='.md5($strIdRelProtocoloProtocolo)).'\';'."\n";
	    	    $strJsArrPastas .= '  Pastas['.$numPasta.'][\'protocolos\'] = \''.$strIdRelProtocoloProtocolo.'\';'."\n\n";
	    	  }
	    	}
      }

      $strLinkAtualizarArvore = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_visualizar&acao_origem=procedimento_visualizar&id_procedimento='.$dblIdProcedimento);
      $strLinkControleProcessos = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_controlar&acao_origem='.$_GET['acao']);

      break;

    default:

      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }


  //$numSeg = InfraUtil::verificarTempoProcessamento($numSegOrig);
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
body{overflow:visible;}

#divArvore {width:100%;padding:1em 0 1em 0;border-bottom:.1em dotted black;}

#divArvore a {
font-size:1.2em;
}

#divConsultarAndamento {width:100%;padding:1em 0.2em 1em 0.2em;}
#divConsultarAndamento a {
text-decoration:none;
font-size:1.2em;
}


#divRelacionados {width:100%;padding:1em 0 .5em 0;border-top:.1em dotted black;}

#divRelacionados label{
width:100%;
font-size:1.4em;
}

#divRelacionadosParciais{
white-space: nowrap;
}

.divRelacionadosParcial{
padding-left:2em;
display:none;
}

a.ancoraRelacionadosParcial{
padding-left:1.2em;
text-decoration:none;
font-size:1.2em;
color: black;
}

a.ancoraRelacionadosParcial:hover{
text-decoration:underline;
}

.noVisitado {
  background-color:white;
  color:#0000cc;
}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->adicionarJavaScript('js/clipboard/clipboard.min.js');
PaginaSEI::getInstance()->abrirJavaScript();
?>

var objArvore = null;
var objNoSelecionado = null;
var processandoPasta = false;
var processarIframe = false;

function inicializar(){

  if ('<?=$bolErro?>'=='1'){
    parent.parent.document.location.href = '<?=$strLinkControleProcessos?>';
  }

  var Nos = Array();
  var NosAcoes = Array();

<?=$strJsArrPastas?>
<?=$strNos?>
<?=$strNosAcao?>

  objArvore = new infraArvore('divArvore', Nos, NosAcoes, 'hdnArvore');

<?=$strOcultarAbrirFechar?>
<?=$strNumPastasAbertas?>


  if (Nos.length){

    associarNosClipboard(Nos);

    var clipboard = new Clipboard('.clipboard', {
      text: function (trigger) {
        var no = objArvore.getNo(trigger.id.replace('anchorImg',''));
        if (no != null){
          return no.aux;
        }
        return null;
      }
    });

    clipboard.on('success', function (e) {
      //console.info('Action:', e.action);
      //console.info('Text:', e.text);
      //console.info('Trigger:', e.trigger);

      var img = document.getElementById(e.trigger.id);

      if (img != null) {

        p = infraObterPosicao(img)

        var div = document.getElementById('divMsgClipboard');
        var criou = false;

        if (div==null) {
          var div = document.createElement("div");
          div.id = 'divMsgClipboard';
          criou = true;
        }
        div.className = 'msgGeral msgSucesso';
        div.innerHTML = 'Número ' + e.text + ' copiado.';
        div.style.position = "fixed";  // Prevent scrolling to bottom of page in MS Edge.


        div.style.top = (p.y + 15) + 'px';
        div.style.left = (p.x + 15) + 'px';

        if (criou) {
          document.body.appendChild(div);
        }

        $("#divMsgClipboard").fadeIn(300).delay(1500).fadeOut(400);
      }

      e.clearSelection();
    });

    clipboard.on('error', function (e) {
      console.error('Action:', e.action);
      console.error('Trigger:', e.trigger);
      alert('Não foi possível copiar o número do protocolo.');
    });

    Nos[0].processar = function (){
      document.location = '<?=$strLinkAtualizarArvore?>';
      return false;
    }
  }

  objArvore.processarAbertura = function(no){

    processarIframe = true;

    if (!processandoPasta){
      if (!no.carregado){
        document.getElementById('hdnPastaAtual').value = no.id;
        document.getElementById('hdnProtocolos').value = Pastas[no.id.substr(5)]['protocolos'];
        document.getElementById('frmArvore').action = Pastas[no.id.substr(5)]['link'];
        document.getElementById('frmArvore').submit();
      }

      document.getElementById('anchorFP<?=$_GET['id_procedimento']?>').style.display='';

      objArvore.numPastasAbertas = objArvore.numPastasAbertas + 1;
      if (objArvore.numPastasAbertas == <?=count($arrPastas)?>){
        document.getElementById('anchorAP<?=$_GET['id_procedimento']?>').style.display='none';
      }

      return true;
    }

    return false;
  }

  objArvore.processarFechamento = function(no){
    document.getElementById('hdnPastaAtual').value = no.id;
    atualizarMensagemPasta('AGUARDE');
    document.getElementById('anchorAP<?=$_GET['id_procedimento']?>').style.display='';
    objArvore.numPastasAbertas = objArvore.numPastasAbertas - 1;
    if (objArvore.numPastasAbertas == 0){
      document.getElementById('anchorFP<?=$_GET['id_procedimento']?>').style.display='none';
    }
    return true;
  }

  objNoSelecionado = null;
  if ('<?=$dblIdProtocoloPosicionar?>' != ''){
    objNoSelecionado = objArvore.getNo('<?=$dblIdProtocoloPosicionar?>');
  }else{
    objNoSelecionado = objArvore.getNo('<?=$_GET['id_procedimento']?>');
  }

  if (objNoSelecionado != null){

    objArvore.setNoSelecionado(objNoSelecionado);

    <? if (isset($_GET['procedimento_visualizar_ciencias']) && $_GET['procedimento_visualizar_ciencias'] == '1'){ ?>
      consultarProcedimentoCiencias();
    <? }else if (isset($_GET['documento_visualizar_ciencias']) && $_GET['documento_visualizar_ciencias'] == '1'){ ?>
      consultarDocumentoCiencias();
    <?}else if (!isset($_GET['montar_visualizacao']) || $_GET['montar_visualizacao']=='1'){ ?>
      self.setTimeout('atualizarVisualizacao()',100);
    <? } ?>
  }
}

function atualizarVisualizacao(){
  parent.document.getElementById('ifrVisualizacao').src = objNoSelecionado.href;
}

function consultarAndamento(){
  parent.document.getElementById('ifrVisualizacao').src = '<?=$strLinkHistorio?>';
}

function consultarProcedimentoCiencias(){
  <? if (isset($_GET['procedimento_visualizar_ciencias']) && $_GET['procedimento_visualizar_ciencias'] == '1'){ ?>
    parent.document.getElementById('ifrVisualizacao').src = '<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao=protocolo_ciencia_listar&acao_origem=procedimento_visualizar&id_procedimento='.$_GET['id_procedimento'].'&arvore=1')?>#' + infraGetAnchor();
  <? } ?>
}

function consultarDocumentoCiencias(){
  <? if (isset($_GET['documento_visualizar_ciencias']) && $_GET['documento_visualizar_ciencias'] == '1'){ ?>
    parent.document.getElementById('ifrVisualizacao').src = '<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao=protocolo_ciencia_listar&acao_origem=procedimento_visualizar&id_procedimento='.$_GET['id_procedimento'].'&id_documento='.$_GET['id_documento'].'&arvore=1')?>#' + infraGetAnchor();
  <? } ?>
}


function processarPasta(){

  if (processarIframe){

    var ie = infraVersaoIE();

    try{
    	if (!ie){
        docIframe = document.getElementById('ifrPasta').contentWindow.document;
      }else{
    	  docIframe = window.frames['ifrPasta'].document;
      }
    }catch(e){
  	  alert('Não foi possível recuperar os protocolos.');
  	  return;
  	}

    ret = docIframe.body.innerHTML;

    if (ret != ''){

    	if (ret.substring(0,2) != 'OK'){

    		var prefixoValidacao = 'INFRA_VALIDACAO';

    		if (ret.substr(0,15) == prefixoValidacao){

    		  atualizarMensagemPasta('AVISO');

    			var msg = ret.substr(prefixoValidacao.length+1);
    			msg = msg.infraReplaceAll("\\n", "\n");
          msg = decodeURIComponent(msg);
   			  alert(msg);

    		}else{

          try{

            atualizarMensagemPasta('ERRO');

          	if (docIframe.getElementById('divInfraExcecao')==null){
              alert('Erro recuperando protocolos.');
          	}else{

          		document.getElementById("ifrPasta").style.display = 'block';
          		document.getElementById('frmArvore').style.display = 'none';

          		resizeIframe();

          		docIframe.getElementById('btnInfraFecharExcecao').value = 'Voltar';
          		if (!ie){
         		    docIframe.getElementById('btnInfraFecharExcecao').innerHTML = 'Voltar';
          		}
          		docIframe.getElementById('btnInfraFecharExcecao').onclick = function() {
          		  document.getElementById("ifrPasta").style.display = 'none';
          		  document.getElementById('frmArvore').style.display = 'block';
              }

      	    }

          }catch(e){alert(e);}
    		}
    	}else{

    	  if (objArvore != null){

      		var Nos = [];
      		var NosAcoes = [];

      		var arrComandos = ret.substr(3).split("\n");
      		for(var i=0; i < arrComandos.length; i++){
      		  if (arrComandos[i].substr(0,3)=='Nos'){
      		    eval(arrComandos[i]);
      		  }
      		}

      		if (Nos.length==0){
      		  atualizarMensagemPasta('NAO ENCONTRADO');
      		}else{
        		processandoPasta = true;
        		try{
        		  var noPasta = objArvore.getNo(document.getElementById('hdnPastaAtual').value);

        		  var div = document.getElementById('div' + noPasta.id);
        		  div.innerHTML = '';

        		  objArvore.adicionarFilhos(noPasta, Nos, NosAcoes);

              associarNosClipboard(Nos);

        		  noPasta.carregado = true;

        		}catch(e){
        		  alert(e);
        		}
        		processandoPasta = false;
          }
      	}

        if (INFRA_IE){
          window.status='Finalizado.';
        }
      }
    }
  }
}

function associarNosClipboard(nos){
  var icone = null;
  for(var i=0;i<nos.length;i++){
    if (nos[i].tipo != 'PASTA' && nos[i].tipo != 'AGUARDE') {
      icone = document.getElementById('anchorImg' + nos[i].id);
      icone.className = 'clipboard';
      icone.title = 'Clique para copiar o número do protocolo para a área de transferência';
    }
  }
}

function atualizarMensagemPasta(tipo){

  var pastaAtual = document.getElementById('hdnPastaAtual');

  if (pastaAtual != null){

    var idAguarde = pastaAtual.value.replace('PASTA','AGUARDE');

    var spanAguarde = document.getElementById('span' + idAguarde);
    var imgAguarde = document.getElementById('icon' + idAguarde);

    if (spanAguarde != null && imgAguarde != null){
      if (tipo == 'AVISO'){
        spanAguarde.innerHTML = spanAguarde.title = 'Não foi possível carregar os protocolos.';
        imgAguarde.src = '<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/remover.gif';
      }else if (tipo == 'ERRO'){
        spanAguarde.innerHTML = spanAguarde.title = 'Erro carregando protocolos.';
        imgAguarde.src = '<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/remover.gif';
      }else if (tipo == 'NAO ENCONTRADO'){
        spanAguarde.innerHTML = spanAguarde.title = 'Nenhum protocolo encontrado.';
        imgAguarde.src = '<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/remover.gif';
      }else if (tipo == 'AGUARDE'){
        spanAguarde.innerHTML = spanAguarde.title = 'Aguarde...';
        imgAguarde.src = '<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/aguarde_pequeno.gif';
      }
    }
  }
}

function resizeIframe(){
	document.getElementById("ifrPasta").style.height = (infraClientHeight()-30) + 'px';
}

function abrirFecharPasta(id){
  objArvore.processarNoJuncao(id);
}

function visualizacaoRelacionados(n){
  var div = document.getElementById('divRelacionadosParcial'+n);
  if (div != null){
    if (div.style.display=='block'){
      div.style.display = 'none';
    }else{
      div.style.display = 'block';
    }
  }
}

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
//PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<body onload="inicializar();">

<form id="frmArvore" method="post" target="ifrPasta">

<div id="divArvore">
</div>

<div id="divConsultarAndamento">
<?=$strConsultarAndamento?>
</div>

<div id="divRelacionados">
<?=$strRelacionamentosTitulo?>
</div>
<?=$strRelacionamentos?>

<input type="hidden" id="hdnArvore" name="hdnArvore" value="<?=$_POST['hdnArvore']?>" />
<input type="hidden" id="hdnPastaAtual" name="hdnPastaAtual" value="<?=$_POST['hdnPastaAtual']?>" />
<input type="hidden" id="hdnProtocolos" name="hdnProtocolos" value="<?=$_POST['hdnProtocolos']?>" />
</form>
<?
//PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
?>
<iframe id="ifrPasta" name="ifrPasta" onload="processarPasta();" width="100%" height="100%" frameborder="0" style="display:none;"></iframe>
<?
PaginaSEI::getInstance()->montarAreaDebug();
?>
</body>
<?
//PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>