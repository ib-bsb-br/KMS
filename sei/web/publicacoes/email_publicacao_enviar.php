<?
/*
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 * 19/04/2007 - CRIADO POR cle@trf4.gov.br
 */
try {
	require_once dirname(__FILE__).'/../SEI.php';
	session_start();
	//////////////////////////////////////////////////////////////////////////////
	InfraDebug::getInstance()->setBolLigado(false);
	InfraDebug::getInstance()->setBolDebugInfra(true);
	InfraDebug::getInstance()->limpar();
	//////////////////////////////////////////////////////////////////////////////
	SessaoPublicacoes::getInstance()->validarLink();
	//die($_POST['hdnConteudo']).
	/* PARA ENVIAR POR ANEXO:
	 *
	 * $strNomeArquivo = InfraUtil::formatarNomeArquivo('publicacao_interna_anexo.html');
		$numTimestamp = time();
		$strDataHora = date('d/m/Y H:i:s',$numTimestamp);
		$strNomeArquivoUpload = date('dmY-His',$numTimeStamp);
			
		$fp = fopen(DIR_SEI_TEMP.'/'.$strNomeArquivoUpload,'w');
		fwrite($fp,$_POST['hdnConteudo']);
		fclose($fp);

		$numTamanho = filesize(DIR_SEI_TEMP.'/'.$strNomeArquivoUpload);
		$arrAnexos[$strNomeArquivo] = DIR_SEI_TEMP.'/'.$strNomeArquivoUpload;
		//ENVIA O EMAIL
		$strAssunto = 'Envio de Inteiro Teor - Publicacoes Eletrônicas';
		$strMensagem = 'Em anexo.';

		if (!InfraMail::enviar($_POST['remetente'],$_POST['destinatario'],$strAssunto,$strMensagem,$arrAnexos)){
		throw new InfraException('Erro enviando e-mail do Inteiro Teor');
		}
	 */
	
	
	PaginaPublicacoes::getInstance(); // entre outras coisas, o construtor trata os espcaes corretamente no Post
	if (isset($_POST['sbmEnviar']) && ($_POST['remetente'] != "") && ($_POST['destinatario'] != "")) {
		//MONTA O EMAIL
		$corpo_email = "<html>" .$_POST['hdnConteudo'] ."</html>";
		//ENVIA O EMAIL
		$strAssunto = "Envio de Publicação Eletrônica";
		InfraMail::enviar($_POST['remetente'],$_POST['destinatario'],$strAssunto,$corpo_email,null,'text/html');
		echo "<script>alert('E-mail enviado.');window.close();</script>";
		die;
	}
} catch(Exception $e) {
	PaginaPublicacoes::getInstance()->processarExcecao($e);
}
//MONTAGEM DA PÁGINA
PaginaPublicacoes::getInstance()->montarDocType();
PaginaPublicacoes::getInstance()->abrirHtml();
PaginaPublicacoes::getInstance()->abrirHead();
PaginaPublicacoes::getInstance()->montarMeta();
PaginaPublicacoes::getInstance()->montarTitle(PaginaPublicacoes::getInstance()->getStrNomeSistema().' - Publicações Eletrônicas');
PaginaPublicacoes::getInstance()->montarStyle();
PaginaPublicacoes::getInstance()->abrirJavaScript();
?>
function obterConteudoPesquisa() {
	//document.getElementById('hdnConteudo').value =
	window.opener.frames['ifrEdoc'].document.innerHTML;
	document.getElementById('hdnConteudo').value = window.opener.document.getElementById('ifrEdoc').contentDocument.getElementsByTagName('html')[0].innerHTML;
}
<?
PaginaPublicacoes::getInstance()->fecharJavaScript();
PaginaPublicacoes::getInstance()->montarJavaScript();
PaginaPublicacoes::getInstance()->fecharHead();
echo "<body>";
PaginaPublicacoes::getInstance()->abrirAreaDados();
?>
<form id="frmEnviarPublicacaoEmail" method="post" onsubmit="obterConteudoPesquisa();" action="<?=SessaoPublicacoes::getInstance()->assinarLink('controlador_publicacoes.php?acao=email_publicacao_enviar&acao_origem='.$_GET['acao'])?>">

	<table border="0" width="100%">
		<tr>
			<td colspan="2"><b>Enviar Publicação por e-mail</b><br/><br/></td>
		</tr>
		<tr>
			<td>E-mail do remetente:</td>
			<td><input type="text" name="remetente" size="20" /></td>
		</tr>
		<tr>
			<td>E-mail do destinatário:</td>
			<td><input type="text" name="destinatario" size="20" /></td>
		</tr>
		<tr>
			<td></td>
			<td colspan="2">
			<button type="submit" accesskey="E" name="sbmEnviar" value="Enviar" class="infraButton"><span class="infraTeclaAtalho">E</span>nviar</button>			
			</td>
		</tr>
	</table>
</form>
<input type="hidden" id="hdnConteudo" name="hdnConteudo" value="" />
<input type="hidden" id="hdnIdDocumento" name="hdnIdDocumento" value="<?=$_GET['id_documento']?>" /> 	
<?
echo "<script>";
if (isset($_POST['sbmEnviar']) && (($_POST['remetente'] == "") || ($_POST['destinatario'] == ""))) {
	echo "alert('Os campos Remetente e Destinatário não podem estar em branco.');";
}
echo "</script>";
PaginaPublicacoes::getInstance()->fecharAreaDados();
echo "</body>";
PaginaPublicacoes::getInstance()->fecharHtml();
?>
