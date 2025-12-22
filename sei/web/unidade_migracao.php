<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 01/10/2010 - criado por alexandre_db
 *
 * Versão do Gerador de Código: 1.29.1
 *
 * Versão no CVS: $Id$
 */

try {
	require_once dirname(__FILE__).'/SEI.php';

	session_start();
	//////////////////////////////////////////////////////////////////////////////
	InfraDebug::getInstance()->setBolLigado(false);
	InfraDebug::getInstance()->setBolDebugInfra(false);
	InfraDebug::getInstance()->limpar();
	//////////////////////////////////////////////////////////////////////////////

	SessaoSEI::getInstance()->validarLink();

	SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

	$strParametros = '';

	if (isset($_GET['id_unidade'])){
		$strParametros .= '&id_unidade='.$_GET['id_unidade'];
	}

	$arrComandos = array();

	$objUnidadeRN = new UnidadeRN();

	switch($_GET['acao']){
		case 'unidade_migrar':
			
			$strTitulo = 'Migrar Dados da Unidade';
			
			//$arrComandos[] = '<button type="button" accesskey="M" name="btnMigrar" id="btnMigrar" value="Migrar" onclick="infraAbrirBarraProgresso(this.form, \''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao_origem'].$strParametros.'&executar=1').'\', 600, 200);" class="infraButton">&nbsp;&nbsp;<span class="infraTeclaAtalho">M</span>igrar&nbsp;&nbsp;</button>';
      $arrComandos[] = '<button type="button" accesskey="M" name="btnMigrar" id="btnMigrar" value="Migrar" onclick="migrar()" class="infraButton">&nbsp;&nbsp;<span class="infraTeclaAtalho">M</span>igrar&nbsp;&nbsp;</button>';
			$arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($_GET['id_unidade'])).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      $numIdUnidadeOrigem = $_POST['hdnIdUnidadeOrigem'];
      $strDescricaoUnidadeOrigem = $_POST['txtUnidadeOrigem'];

      $numIdUnidadeDestino = $_POST['hdnIdUnidadeDestino'];
      $strDescricaoUnidadeDestino = $_POST['txtUnidadeDestino'];

			$objMigracaoUnidadeDTO = new MigracaoUnidadeDTO();
			$objMigracaoUnidadeDTO->setNumIdUnidadeOrigem($numIdUnidadeOrigem);
			$objMigracaoUnidadeDTO->setNumIdUnidadeDestino($numIdUnidadeDestino);
      $objMigracaoUnidadeDTO->setStrSinAcompanhamentoEspecial(PaginaSEI::getInstance()->getCheckbox($_POST['chkSinAcompanhamentoEspecial']));
      $objMigracaoUnidadeDTO->setStrSinAssinatura(PaginaSEI::getInstance()->getCheckbox($_POST['chkSinAssinatura']));
      $objMigracaoUnidadeDTO->setStrSinBlocoInterno(PaginaSEI::getInstance()->getCheckbox($_POST['chkSinBlocoInterno']));
      $objMigracaoUnidadeDTO->setStrSinGrupoContato(PaginaSEI::getInstance()->getCheckbox($_POST['chkSinGrupoContato']));
      $objMigracaoUnidadeDTO->setStrSinGrupoEmail(PaginaSEI::getInstance()->getCheckbox($_POST['chkSinGrupoEmail']));
      $objMigracaoUnidadeDTO->setStrSinGrupoUnidade(PaginaSEI::getInstance()->getCheckbox($_POST['chkSinGrupoUnidade']));
      $objMigracaoUnidadeDTO->setStrSinModelo(PaginaSEI::getInstance()->getCheckbox($_POST['chkSinModelo']));
      $objMigracaoUnidadeDTO->setStrSinTextoPadrao(PaginaSEI::getInstance()->getCheckbox($_POST['chkSinTextoPadrao']));

      $objUnidadeRN = new UnidadeRN();

			if ($_GET['executar']=='1') {
        PaginaSEI::getInstance()->prepararBarraProgresso2($strTitulo);
				try{
					$objUnidadeRN->migrar($objMigracaoUnidadeDTO);
				}catch(Exception $e){
					PaginaSEI::getInstance()->processarExcecao($e);
				}
        PaginaSEI::getInstance()->finalizarBarraProgresso2(null,false);
        die;
			}
			break;

		default:
			throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
	}

  $strLinkAjaxUnidade = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=unidade_auto_completar_todas');

  if (!isset($_POST['hdnFlag'])){
    $objMigracaoUnidadeDTO->setStrSinAcompanhamentoEspecial('S');
    $objMigracaoUnidadeDTO->setStrSinAssinatura('S');
    $objMigracaoUnidadeDTO->setStrSinBlocoInterno('S');
    $objMigracaoUnidadeDTO->setStrSinGrupoContato('S');
    $objMigracaoUnidadeDTO->setStrSinGrupoEmail('S');
    $objMigracaoUnidadeDTO->setStrSinGrupoUnidade('S');
    $objMigracaoUnidadeDTO->setStrSinModelo('S');
    $objMigracaoUnidadeDTO->setStrSinTextoPadrao('S');
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

#lblUnidadeOrigem {position:absolute;left:0%;top:0%;}
#txtUnidadeOrigem {position:absolute;left:0%;top:6%;width:70%;}

#lblUnidadeDestino {position:absolute;left:0%;top:16%;}
#txtUnidadeDestino {position:absolute;left:0%;top:22%;width:70%;}

#divSinAcompanhamentoEspecial {position:absolute;left:0%;top:35%;}
#divSinAssinatura {position:absolute;left:0%;top:43%;}
#divSinBlocoInterno {position:absolute;left:0%;top:51%;}
#divSinGrupoContato {position:absolute;left:0%;top:59%;}
#divSinGrupoEmail {position:absolute;left:0%;top:67%;}
#divSinGrupoUnidade {position:absolute;left:0%;top:75%;}
#divSinModelo {position:absolute;left:0%;top:83%;}
#divSinTextoPadrao {position:absolute;left:0%;top:91%;}

#ifrMigracao {width:99%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
//<script>

var objAutoCompletarUnidadeOrigem = null;
var objAutoCompletarUnidadeDestino = null;

function inicializar(){

  objAutoCompletarUnidadeOrigem = new infraAjaxAutoCompletar('hdnIdUnidadeOrigem','txtUnidadeOrigem','<?=$strLinkAjaxUnidade?>');
  objAutoCompletarUnidadeOrigem.limparCampo = true;
  objAutoCompletarUnidadeOrigem.prepararExecucao = function(){
  return 'palavras_pesquisa='+document.getElementById('txtUnidadeOrigem').value;
  };
  objAutoCompletarUnidadeOrigem.selecionar('<?=$numIdUnidadeOrigem;?>','<?=PaginaSEI::getInstance()->formatarParametrosJavaScript($strDescricaoUnidadeOrigem)?>');

  objAutoCompletarUnidadeDestino = new infraAjaxAutoCompletar('hdnIdUnidadeDestino','txtUnidadeDestino','<?=$strLinkAjaxUnidade?>');
  objAutoCompletarUnidadeDestino.limparCampo = true;
  objAutoCompletarUnidadeDestino.prepararExecucao = function(){
    return 'palavras_pesquisa='+document.getElementById('txtUnidadeDestino').value;
  };
  objAutoCompletarUnidadeDestino.selecionar('<?=$numIdUnidadeDestino;?>','<?=PaginaSEI::getInstance()->formatarParametrosJavaScript($strDescricaoUnidadeDestino)?>');

  document.getElementById('txtUnidadeOrigem').focus();
}

function OnSubmitForm() {
 
  if (infraTrim(document.getElementById('hdnIdUnidadeOrigem').value)==''){
    alert('Selecione a Unidade Origem.');
    document.getElementById('txtUnidadeOrigem').focus();
    return false;
  }

  if (infraTrim(document.getElementById('hdnIdUnidadeDestino').value)==''){
    alert('Selecione a Unidade Destino.');
    document.getElementById('txtUnidadeDestino').focus();
    return false;
  }

  if (!document.getElementById('chkSinAcompanhamentoEspecial').checked &&
      !document.getElementById('chkSinAssinatura').checked &&
      !document.getElementById('chkSinBlocoInterno').checked &&
      !document.getElementById('chkSinGrupoContato').checked &&
      !document.getElementById('chkSinGrupoEmail').checked &&
      !document.getElementById('chkSinGrupoUnidade').checked &&
      !document.getElementById('chkSinModelo').checked &&
      !document.getElementById('chkSinTextoPadrao').checked){
    alert('Nenhuma opção selecionada.');
    return false;
  }


  if (!confirm("ATENÇÃO: Confirma migração dos dados selecionados?")){
    return false;
  }
  
  //infraExibirAviso(false);
   
  return true; 
}

function migrar(){
  if (OnSubmitForm()) {

    var ifr = document.getElementById('ifrMigracao');
    if (ifr != null) {
      document.getElementById('divInfraAreaTelaD').removeChild(ifr);
    }

    var ifr = document.createElement('iframe');
    ifr.id = 'ifrMigracao';
    ifr.name = 'ifrMigracao';
    ifr.setAttribute('frameBorder', '0');
    ifr.setAttribute('scrolling', 'no');

    document.getElementById('divInfraAreaTelaD').appendChild(ifr);

    document.getElementById('frmMigracaoUnidade').target = 'ifrMigracao';
    document.getElementById('frmMigracaoUnidade').submit();
  }
}

//</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmMigracaoUnidade" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].'&executar=1'.$strParametros)?>">
<?
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
//PaginaSEI::getInstance()->montarAreaValidacao();
PaginaSEI::getInstance()->abrirAreaDados('30em');
?>

  <label id="lblUnidadeOrigem" for="txtUnidadeOrigem" class="infraLabelObrigatorio">Unidade Origem:</label>
  <input type="text" id="txtUnidadeOrigem" name="txtUnidadeOrigem" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" value="<?=PaginaSEI::tratarHTML($strDescricaoUnidadeOrigem)?>" />
  <input type="hidden" id="hdnIdUnidadeOrigem" name="hdnIdUnidadeOrigem" class="infraText" value="<?=$numIdUnidadeOrigem?>" />

  <label id="lblUnidadeDestino" for="txtUnidadeDestino" class="infraLabelObrigatorio">Unidade Destino:</label>
  <input type="text" id="txtUnidadeDestino" name="txtUnidadeDestino" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" value="<?=PaginaSEI::tratarHTML($strDescricaoUnidadeDestino)?>" />
  <input type="hidden" id="hdnIdUnidadeDestino" name="hdnIdUnidadeDestino" class="infraText" value="<?=$numIdUnidadeDestino?>" />

  <div id="divSinAcompanhamentoEspecial" class="infraDivCheckbox">
    <input type="checkbox" id="chkSinAcompanhamentoEspecial" name="chkSinAcompanhamentoEspecial" class="infraCheckbox" <?=PaginaSEI::getInstance()->setCheckbox($objMigracaoUnidadeDTO->getStrSinAcompanhamentoEspecial())?>  tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
    <label id="lblSinAcompanhamentoEspecial" for="chkSinAcompanhamentoEspecial" accesskey="" class="infraLabelCheckbox">Acompanhamentos Especiais</label>
  </div>
  
  <div id="divSinAssinatura" class="infraDivCheckbox">
    <input type="checkbox" id="chkSinAssinatura" name="chkSinAssinatura" class="infraCheckbox" <?=PaginaSEI::getInstance()->setCheckbox($objMigracaoUnidadeDTO->getStrSinAssinatura())?>  tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
    <label id="lblSinAssinatura" for="chkSinAssinatura" accesskey="" class="infraLabelCheckbox">Assinaturas da Unidade</label>
  </div>

  <div id="divSinBlocoInterno" class="infraDivCheckbox">
    <input type="checkbox" id="chkSinBlocoInterno" name="chkSinBlocoInterno" class="infraCheckbox" <?=PaginaSEI::getInstance()->setCheckbox($objMigracaoUnidadeDTO->getStrSinBlocoInterno())?>  tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
    <label id="lblSinBlocoInterno" for="chkSinBlocoInterno" accesskey="" class="infraLabelCheckbox">Blocos Internos</label>
  </div>

  <div id="divSinGrupoContato" class="infraDivCheckbox">
    <input type="checkbox" id="chkSinGrupoContato" name="chkSinGrupoContato" class="infraCheckbox" <?=PaginaSEI::getInstance()->setCheckbox($objMigracaoUnidadeDTO->getStrSinGrupoContato())?>  tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
    <label id="lblSinGrupoContato" for="chkSinGrupoContato" accesskey="" class="infraLabelCheckbox">Grupos de Contatos</label>
  </div>

  <div id="divSinGrupoEmail" class="infraDivCheckbox">
    <input type="checkbox" id="chkSinGrupoEmail" name="chkSinGrupoEmail" class="infraCheckbox" <?=PaginaSEI::getInstance()->setCheckbox($objMigracaoUnidadeDTO->getStrSinGrupoEmail())?>  tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
    <label id="lblSinGrupoEmail" for="chkSinGrupoEmail" accesskey="" class="infraLabelCheckbox">Grupos de E-mail</label>
  </div>

  <div id="divSinGrupoUnidade" class="infraDivCheckbox">
    <input type="checkbox" id="chkSinGrupoUnidade" name="chkSinGrupoUnidade" class="infraCheckbox" <?=PaginaSEI::getInstance()->setCheckbox($objMigracaoUnidadeDTO->getStrSinGrupoUnidade())?>  tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
    <label id="lblSinGrupoUnidade" for="chkSinGrupoUnidade" accesskey="" class="infraLabelCheckbox">Grupos de Envio</label>
  </div>

  <div id="divSinModelo" class="infraDivCheckbox">
    <input type="checkbox" id="chkSinModelo" name="chkSinModelo" class="infraCheckbox" <?=PaginaSEI::getInstance()->setCheckbox($objMigracaoUnidadeDTO->getStrSinModelo())?>  tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
    <label id="lblSinModelo" for="chkSinModelo" accesskey="" class="infraLabelCheckbox">Modelos Favoritos</label>
  </div>
  
  <div id="divSinTextoPadrao" class="infraDivCheckbox">
    <input type="checkbox" id="chkSinTextoPadrao" name="chkSinTextoPadrao" class="infraCheckbox" <?=PaginaSEI::getInstance()->setCheckbox($objMigracaoUnidadeDTO->getStrSinTextoPadrao())?>  tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
    <label id="lblSinTextoPadrao" for="chkSinTextoPadrao" accesskey="" class="infraLabelCheckbox">Textos Padrão</label>
  </div>

<?  
	PaginaSEI::getInstance()->fecharAreaDados();
	PaginaSEI::getInstance()->montarAreaDebug();
	//PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
	?>
  <input type="hidden" id="hdnFlag" name="hdnFlag" value="1" />
</form>
<!-- <iframe id="ifrMigracao" name="ifrMigracao" frameborder="0" scrolling="no"></iframe> -->
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>
