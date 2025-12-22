<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 13/11/2015 - criado por bcu
*
*
*/

try {
  require_once dirname(__FILE__).'/../SEI.php';

  session_start();


  //////////////////////////////////////////////////////////////////////////////
  //InfraDebug::getInstance()->setBolLigado(false);
  //InfraDebug::getInstance()->setBolDebugInfra(false);
  //InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);
  PaginaSEI::getInstance()->setBolAutoRedimensionar(false);

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  switch($_GET['acao']) {

    case 'documento_versao_comparar':

      $strTitulo = 'Documento';


      $objEditorDTO = new EditorDTO();
      $objEditorDTO->setDblIdDocumento($_GET['id_documento']);
      $objEditorDTO->setNumIdBaseConhecimento(null);
      $objEditorDTO->setStrSinCabecalho('S');
      $objEditorDTO->setStrSinRodape('S');
      $objEditorDTO->setStrSinCarimboPublicacao('N');
      $objEditorDTO->setStrSinIdentificacaoVersao('N');
      $objEditorDTO->setStrSinProcessarLinks('N');

      $arr=PaginaSEI::getInstance()->getArrStrItensSelecionados();
      if(count($arr)!=2){
        throw new InfraException('Versões para comparação não informadas.');
      }
      if($arr[0]>$arr[1]){
        $arr=array_reverse($arr);
      }

      $objEditorDTO->setNumVersao($arr[0]);
      $objEditorDTO->setNumVersaoComparacao($arr[1]);


      $objEditorRN = new EditorRN();
      $strResultado = $objEditorRN->compararHtmlVersao($objEditorDTO);
      echo $strResultado;
      die;
      //echo $diff->getDifference();

      break;

    default:
      throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
  }

  $strDisplayArvore = '';
  $strLinkArvore = '';
  //nao vindo da árvore e documento sigiloso publicado nao mostra arvore de processo


}catch(Exception $e){
  PaginaSEI::getInstance()->processarExcecao($e);
}

//PaginaSEI::getInstance()->montarDocType();
//PaginaSEI::getInstance()->abrirHtml();
//PaginaSEI::getInstance()->abrirHead();
//PaginaSEI::getInstance()->montarMeta();
//PaginaSEI::getInstance()->montarTitle($strTitulo);
//PaginaSEI::getInstance()->montarStyle();
//PaginaSEI::getInstance()->abrirStyle();
?>
  ins.diffmod { color:blue; }
  del.diffmod { color:red; }
  ins.mod { color:blue; }
  del.mod { color:red; }
  .diffins { color:blue; }
  .diffdel { color:red; }


<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
//<script type="text/javascript">

function redimensionar() {
  if (document.getElementById('ifrEditor') != null) {
    hAjuste = document.getElementById('divCabecalho').offsetHeight;
    var h = infraClientHeight() - document.getElementById('divCabecalho').offsetHeight - hAjuste;
    if (h > 0) {
      document.getElementById('ifrEditor').style.height = h + 'px';
    }
  }
}

function inicializar() {
  redimensionar();
  infraAdicionarEvento(window, 'resize', redimensionar);
}
//</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();

?>
<body onload="inicializar();">

<?





if (!PaginaSEI::getInstance()->isBolArvore()){?>
  <div id="divCabecalho" style="display:table;width:99.5%;border-bottom:.1em solid #008EBD;">
    <div id="divTitulo" class="infraBarraLocalizacao" style="float:left;padding-bottom:.1em;">Titulo</div>
    <div id="divLinkArvore" style="float:right;"></div>
    <br />
  </div>

  <?if ($strLinkVisualizarEdoc != ''){ ?>
    <iframe id="ifrEditor" frameborder="no" src="<?=$strLinkVisualizarEdoc?>"></iframe>
  <?}?>

<?}?>
</body>
<?
PaginaSEI::getInstance()->fecharHtml();

