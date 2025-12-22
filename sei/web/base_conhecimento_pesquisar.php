<?php
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 17/06/2010 - criado por jonatas_db
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
  InfraDebug::getInstance()->setBolDebugInfra(true);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

	PaginaSEI::getInstance()->salvarCamposPost(array('q'));

	$strResultado = '';

  switch($_GET['acao']){

    case 'base_conhecimento_pesquisar':
    	
      $strTitulo = 'Base de Conhecimento';

			$q = PaginaSEI::getInstance()->recuperarCampo('q');

			if (isset($_POST['q'])){
				try{

					$objPesquisaBaseConhecimentoSolrDTO = new PesquisaBaseConhecimentoSolrDTO();
					$objPesquisaBaseConhecimentoSolrDTO->setStrPalavrasChave($q);
					$objPesquisaBaseConhecimentoSolrDTO->setNumInicioPaginacao($_POST['hdnInicio']);
					$strResultado = SolrBaseConhecimento::executar($objPesquisaBaseConhecimentoSolrDTO);

				}catch(Exception $e){
					PaginaSEI::getInstance()->setStrMensagem(SolrUtil::$MSG_ERRO_PESQUISA, InfraPagina::$TIPO_MSG_AVISO);
					LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
				}
			}

			break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $arrComandos = array();

  
  $arrComandos[] = '<button type="submit" accesskey="P" id="btnPesquisar" value="Pesquisar" class="infraButton" style="width:10em;"><span class="infraTeclaAtalho">P</span>esquisar</button>';

  if (SessaoSEI::getInstance()->verificarPermissao('base_conhecimento_cadastrar')){
    $arrComandos[] = '<button type="button" accesskey="N" id="btnNova" value="Nova" style="width:10em;" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=base_conhecimento_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ova</button>';
  }

  if (SessaoSEI::getInstance()->verificarPermissao('base_conhecimento_listar')){
	  $arrComandos[] = '<button type="button" accesskey="M" id="btnMinhaBase" value="MinhaBase" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=base_conhecimento_listar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']).'\'" class="infraButton" style="width:10em;"><span class="infraTeclaAtalho">M</span>inha Base</button>';
  }

  $strLinkAjuda = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=pesquisa_solr_ajuda&acao_origem='.$_GET['acao']);

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

#lblPesquisa 	{position:absolute;left:0%;top:0%;}
#txtPesquisa 	{position:absolute;left:0%;top:40%;width:50%;}
#ancAjuda {position:absolute;left:51%;top:40%;}

.linkAnexo 		{color:#006600;}
.linkUnidade 	{color:#006600;position:absolute;right:3%;}
.resTitulo 		{background-color: #EEEEEE;}


*.esquerda {
	text-align: left !important;
}

*.direita {
	text-align: right !important;
}

.sugestao{
  font-size: 1.2em;
}

div#conteudo div.protocolo-documento {
	float: right;
}

div#conteudo > div.barra {
	border-bottom: .1em solid #909090;
	font-size: 1.2em;
	margin: 0 0 .5em 0;
	padding: 0 0 .5em 0;
	text-align: right;
}

div#conteudo > div.paginas {
	border-top: .1em solid #909090;
	margin: 0 0 5em;
	padding: .5em 0 0 0;
	text-align: center;
	font-size: 1.2em;
}

div#conteudo > div.sem-resultado {
  font-size:1.2em;
	margin: .5em 0 0 0;
}

div#conteudo table {
	border-collapse: collapse;
	border-spacing: 0px;
}

div#conteudo > table {
	margin: 0 0 .5em;
	width: 100%;
}

div#conteudo > table > tbody > tr > td {
	background: #f0f0f0;
	padding: .3em .5em;
}

div#conteudo > table > tbody > tr:first-child > td {
	background: #e0e0e0;
}

div#conteudo a.protocoloAberto,
div#conteudo a.protocoloNormal{
	font-size:1.1em !important;
}

div#conteudo a.protocoloAberto:hover,
div#conteudo a.protocoloNormal:hover{
  text-decoration:underline !important;
}

div#conteudo td.metatag > table {
	border-collapse: collapse;
	margin: 0px auto;
	white-space: nowrap;
}

/*
div#conteudo td.metatag > table {
	text-align: left;
	width:70%;
}
*/

div#conteudo td.metatag > table > tbody > tr > td {
	color: #333333;
	font-size: .9em;
	padding: 0 2em;
	width:25%;
}

div#conteudo td.metatag > table > tbody > tr > td:first-child {
	width:50%;
}

div#conteudo td.metatag > table > tbody > tr > td > b {
	color: #006600;
	font-weight: normal;
}

div#conteudo > table > tbody > tr > td > div.protocolo-documento {
	float: right;
	margin: -16px 0px 0px 0px;
}

div#conteudo a {
	border-bottom: 1px solid transparent;
	text-decoration: none;
}

div#conteudo a:hover {
	border-bottom: 1px solid #000000;
}

div#conteudo a.arvore {
	border: none;
}

span.pequeno {
	font-size: .9em;
}

div#mensagem {
	background: #e0e0e0;
	border-color: #c0c0c0;
	border-style: solid;
	border-width: .1em;
	margin: 4em auto 0;
	padding: 2em;
}

div#mensagem > span.pequeno {
	color: #909090;
	font-size: .9em;
}

td.resTitulo img.arvore {
	margin: 0px 5px -3px 0px;
}

div.paginas, div.paginas * {
	font-size: 12px;
}

div.paginas b {
	font-weight: bold;
}

div.paginas a {
	border-bottom: 1px solid transparent;
	color: #000080;
	text-decoration: none;
}

div.paginas a:hover {
	border-bottom: 1px solid #000000;
	color: #800000;
}

td.resSnippet b {
font-weight:bold;
}

a.ancoraLink{
 text-decoration:none;
 font-size:1em;
}

a.ancoraLink:hover{
 text-decoration:underline;
}

#divInfraAreaTabela tr.infraTrClara td {padding:.3em;}
#divInfraAreaTabela table.infraTable {border-spacing:0;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->adicionarJavaScript('solr/js/sistema.js');
PaginaSEI::getInstance()->abrirJavaScript();
?>
function inicializar(){
  infraEfeitoTabelas();
  document.getElementById('txtPesquisa').focus();
}

function navegar(inicio) {
	document.getElementById('hdnInicio').value = inicio;
	if (typeof(window.onSubmitForm)=='function' && !window.onSubmitForm()) {
	  return;
	}
	document.getElementById('frmPesquisaBaseConhecimento').submit();
}

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar(); "');
?>

<form id="frmPesquisaBaseConhecimento" name="frmPesquisaBaseConhecimento" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>">
<?
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->abrirAreaDados('5em');
?>
  <label id="lblPesquisa" class="infraLabel" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">Palavras-chave:</label>
  <input type="text" name="q" id="txtPesquisa" class="infraText" value="<?=PaginaSEI::tratarHTML($q)?>"/>
  <a id="ancAjuda" href="<?=$strLinkAjuda?>" target="janAjuda" title="Ajuda para Pesquisa" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"><img src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/ajuda.gif" class="infraImg"/></a>
  
  <input id="partialfields" name="partialfields" type="hidden" value="" />

<?  
  PaginaSEI::getInstance()->fecharAreaDados();
  //PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
	echo '<div id="conteudo" style="width:99%;" class="infraAreaTabela">';
	echo $strResultado;
	echo '</div>';
  PaginaSEI::getInstance()->montarAreaDebug();
?>
	<input type="hidden" id="hdnInicio" name="hdnInicio" value="0" />
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>