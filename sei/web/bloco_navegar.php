<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 06/11/2015 - criado por bcu
 */

try {
  require_once dirname(__FILE__).'/SEI.php';

  session_start();

  //////////////////////////////////////////////////////////////////////////////
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(true);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);

//  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);


  $strParametros = '';
  $strParametros .= '&id_bloco='.$_GET['id_bloco'];

  switch($_GET['acao']){

    case 'bloco_navegar':

      $objBlocoDTO = new BlocoDTO();
      $objBlocoDTO->setStrStaTipo(BlocoRN::$TB_ASSINATURA);
      $objBlocoDTO->retStrStaEstado();
      $objBlocoDTO->retNumIdUnidade();
      $objBlocoDTO->setNumIdBloco($_GET['id_bloco']);

      $objBlocoRN = new BlocoRN();
      $objBlocoDTO = $objBlocoRN->consultarRN1276($objBlocoDTO);

      if ($objBlocoDTO==null){
        $strTitulo = 'Documentos do Bloco '.$_GET['id_bloco'];

        $arrComandos = array('<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>');

        $objInfraException =  new InfraException();
        $objInfraException->lancarValidacao('Bloco '.$_GET['id_bloco'].' não encontrado.');
      }

      $strTitulo = 'Documentos do Bloco de Assinatura '.$_GET['id_bloco'];


      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $arrComandos = array();


  $objRelBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
  $objRelBlocoProtocoloDTO->retDblIdProtocolo();
  $objRelBlocoProtocoloDTO->retNumIdBloco();
  $objRelBlocoProtocoloDTO->setNumSequencia($_GET['seq']);
  $objRelBlocoProtocoloDTO->retNumIdUnidadeBloco();
  $objRelBlocoProtocoloDTO->retStrProtocoloFormatadoProtocolo();
  $objRelBlocoProtocoloDTO->retStrStaProtocoloProtocolo();
  $objRelBlocoProtocoloDTO->retStrAnotacao();
  $objRelBlocoProtocoloDTO->setNumIdBloco($_GET['id_bloco']);
  $objRelBlocoProtocoloDTO->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);

  $objRelBlocoProtocoloRN = new RelBlocoProtocoloRN();
  $objRelBlocoProtocoloDTO = $objRelBlocoProtocoloRN->consultarRN1290($objRelBlocoProtocoloDTO);

  $strLinkAjaxAssinaturas=SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=assinaturas_documento');

    $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$strAcaoDestino.'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($_GET['id_bloco'])).'\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';

}catch(Exception $e){
  PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
echo '<meta name="viewport" content="width=980">';
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema().' - '.$strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
?>
#divNavegacaoBloco {position:fixed;width:100%;height:40px;z-index:9000;}
body {margin:0;overflow:hidden}

#divDocumento {box-sizing: border-box; -webkit-box-sizing: border-box; -moz-box-sizing: border-box;}
#ifrDocumento {width:100%;border:0;top:40px;position:absolute;overflow:auto;}
#lblSeq {color:white;font-size:20px; position:absolute;left:1%; top:7px;}

#divAcoes {float:right;}
#imgArvore {float:left;}
#imgAssinatura {float:left;}

#divSelecionar {float:left;font-size:11px;color:white;text-align:center;padding:7px 50px 0 10px;}
#lblSelecionado {font-size:11px;color:white;text-align:center;padding-top:2px;}
#chkSelecionado {transform:scale(1.2);-webkit-transform:scale(1.2);-ms-transform:scale(1.3);width:16px;height:16px;}

#imgEsquerda {float:left;display:none;padding:.5em 1.5em 0 0 !important;}
#imgDireita {float:left;display:none;padding:.5em 1.5em 0 0 !important;}

#divAcoes img{
border:0;
padding: 3px 5px 0 0;
}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
//<script type="text/javascript">

  var idBloco=<?=$_GET['id_bloco'];?>;
  var idatual=<?=$_GET['seq'];?>;
  var janelaPai=window.opener;
  var trAtual=$(janelaPai.document.getElementById('trSeq'+idatual));
  var checkAtual=trAtual.find('[type=checkbox]');
  var idAnterior,idProximo;

  objAjaxAssinaturas = new infraAjaxComplementar(null,'<?=$strLinkAjaxAssinaturas?>');
  objAjaxAssinaturas.limparCampo = false;
  objAjaxAssinaturas.mostrarAviso = false;
  objAjaxAssinaturas.tempoAviso = 1000;

  objAjaxAssinaturas.prepararExecucao = function(){
    var re = /&id_documento=([^&]*)/;
    var match=re.exec(janelaPai.arrLinkDocumentos[idatual]);
    return '&idDocumento='+match[1];
  };
  objAjaxAssinaturas.processarResultado = function(arr){
    var base64=new infraBase64();
    trAtual.find('td:eq(6)').html(base64.decodificar(arr['assinaturas']));
    if (checkAtual.prop('checked')==true) {
      checkAtual.click();
      exibirCheckbox();
    }
  };

  function inicializar() {
    processarDocumento(idatual);
    infraAdicionarEvento(window,'resize',redimensionar);
    infraEfeitoTabelas();
    redimensionar();
  }
  function redimensionar() {
    setTimeout(function(){

      var tamDivNavegacao=document.getElementById('divNavegacaoBloco').offsetHeight;
      var ifrDocumento=document.getElementById('ifrDocumento');
      if (tamDivNavegacao>ifrDocumento.offsetHeight) tamDivNavegacao-=ifrDocumento.offsetHeight;
      var tamEditor=infraClientHeight()- tamDivNavegacao;
      ifrDocumento.style.height = (tamEditor>0?tamEditor:1) +'px';
    },0);
  }
  function exibirCheckbox(){
    $('#chkSelecionado').prop('checked',checkAtual.prop('checked'));
  }
  function processarClick(){
    checkAtual.click();
    trAtual.addClass('infraTrAcessada');
    exibirCheckbox();
  }
  function exibirSetas(){
    var idPrev=trAtual.prev().attr('id');
    if (idPrev && idPrev.substr(0,5)=='trSeq'){
      idAnterior=idPrev.substr(5);
      $('#imgEsquerda').show();
    } else {
      idAnterior=null;
      $('#imgEsquerda').hide();
    }
    var idNext=trAtual.next().attr('id');
    if (idNext && idNext.substr(0,5)=='trSeq'){
      idProximo=idNext.substr(5);
      $('#imgDireita').show();
    } else {
      idProximo=null;
      $('#imgDireita').hide();
    }
  }
  function processarDocumento(id){
    if (id==null) return;
    idatual=id;
    $('#lblSeq').html('Bloco de Assinatura '+idBloco+' - Sequencial&nbsp;'+id+'&nbsp;&nbsp;');
    trAtual.parent().find('.infraTrAcessada').removeClass('infraTrAcessada');
    trAtual=$(janelaPai.document.getElementById('trSeq'+idatual));
    checkAtual=trAtual.addClass('infraTrAcessada').find('[type=checkbox]');
    if (!janelaPai.arrDocumentosVisualizados.hasOwnProperty(id)){
      janelaPai.arrDocumentosVisualizados[id]=true;
      //if (checkAtual.prop('checked')==false) {
      //  checkAtual.click();
      //}
    }
    document.getElementById('ifrDocumento').src=janelaPai.arrLinkDocumentos[idatual];
    exibirCheckbox();
    exibirSetas();
  }
  function abrirArvore(){
    infraAbrirJanela(janelaPai.arrLinkProcedimentos[idatual],'arvore',900,600);
  }
  function assinar(){
    infraAbrirJanela(janelaPai.arrLinkAssinaturas[idatual],'janelaAssinatura',700,450,'location=0,status=1,resizable=1,scrollbars=1');
  }

  //</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
//PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<body onload="inicializar()">
  <div id="divNavegacaoBloco" class="infraCorBarraSistema">
    <label id="lblSeq"></label>

    <div id="divAcoes">
      <a href="javascript:void(0)" onclick="abrirArvore()"  tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
        <img id="imgArvore" src="imagens/sei_arvore_32.png" alt="Visualizar Árvore do Processo" title="Visualizar Árvore do Processo">
      </a>
      <a href="javascript:void(0)" onclick="assinar()" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
        <img id="imgAssinatura" src="imagens/sei_assinar_32.png" alt="Assinar Documento" title="Assinar Documento">
      </a>

      <div id="divSelecionar" class="infraDivCheckbox">
        <input id="chkSelecionado" type="checkbox" onclick="processarClick();" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" class="infraCorBarraSistema">
        <label id="lblSelecionado" for="chkSelecionado">&nbsp;Selecionar para Assinatura</label>
      </div>

      <a href="javascript:void(0)" onclick="processarDocumento(window.idAnterior);" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
        <img id="imgEsquerda" src="imagens/seta_bloco_esq_24.png" alt="Documento Anterior" title="Documento Anterior">
      </a>
      <a href="javascript:void(0)" onclick="processarDocumento(window.idProximo);" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
        <img id="imgDireita" src="imagens/seta_bloco_dir_24.png" alt="Próximo Documento" title="Próximo Documento">
      </a>

    </div>

</div>
  <?
//  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->montarAreaDebug();
//  PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
  <div id="divDocumento">
    <iframe id="ifrDocumento" src="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_visualizar&id_documento='.$objRelBlocoProtocoloDTO->getDblIdProtocolo()); ?>">

    </iframe>
  </div>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>