<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 08/04/2011 - criado por mga
*
*
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
	
  PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);
  
  $strParametros = '';
  
  if (isset($_GET['id_procedimento'])){
    $strParametros .= '&id_procedimento='.$_GET['id_procedimento'];
  }

  if (isset($_GET['id_documento'])){
    $strParametros .= '&id_documento='.$_GET['id_documento'];
  }
  
  if (isset($_GET['id_procedimento_anexado'])){
    $strParametros .= '&id_procedimento_anexado='.$_GET['id_procedimento_anexado'];
  }
  
  $bolAcesso = true;
  $bolValidado = false;
  
  $strLinkDestino = '';
  
  switch($_GET['acao']){
    
    case 'usuario_validar_acesso':

      $strTitulo = 'Identificação de Acesso';

      if ($_GET['acao_destino']!='procedimento_trabalhar' && $_GET['acao_destino']!='procedimento_credencial_listar' && $_GET['acao_destino']!='procedimento_acervo_sigilosos'){
        throw new InfraException('Acão destino ['.$_GET['acao_destino'].'] não reconhecida.');
      }

      if ($_GET['acao_destino']=='procedimento_trabalhar') {

        if (!isset($_POST['pwdSenha'])) {

          //verifica permissão de acesso ao documento
          $objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();
          $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_PROCEDIMENTOS);
          $objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_AUTORIZADO);
          $objPesquisaProtocoloDTO->setDblIdProtocolo($_GET['id_procedimento']);

          $objProtocoloRN = new ProtocoloRN();
          $arrObjProtocoloDTO = $objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO);

          if (count($arrObjProtocoloDTO) == 0) {
            $bolAcesso = false;
          }
        }
      }

      if (isset($_POST['pwdSenha'])){

        try{

          $objInfraSip = new InfraSip(SessaoSEI::getInstance());
          $objInfraSip->autenticar(SessaoSEI::getInstance()->getNumIdOrgaoUsuario(), null, SessaoSEI::getInstance()->getStrSiglaUsuario(), $_POST['pwdSenha']);
        	
          AuditoriaSEI::getInstance()->auditar($_GET['acao']);

          $bolValidado = true;
          
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e, true);
        }
      }

      $strLinkDestino = SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_destino'].'&acao_origem='.$_GET['acao'].'&acesso=1'.$strParametros);
      $strLinkNegado = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_controlar&acao_origem='.$_GET['acao'].$strParametros.PaginaSEI::getInstance()->montarAncora($_GET['id_procedimento']));
      
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
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

#lblUsuario {position:absolute;left:0%;top:0%;}
#txtUsuario {position:absolute;left:0%;top:20%;width:90%;}

#lblSenha {position:absolute;left:0%;top:50%;}
#pwdSenha {position:absolute;left:0%;top:70%;width:20%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

var bolProcessando = false;

function inicializar(){
  
  <?if (!$bolAcesso){ ?>
    window.close();
  <?}else if ($bolValidado){ ?>
    bolProcessando = true;
    window.opener.infraFecharJanelaModal();
    window.opener.location = '<?=$strLinkDestino?>';
    self.setTimeout('window.close()',500);
  <?}else{?>
    document.getElementById('pwdSenha').focus();
  <?}?>
    
}

function OnSubmitForm() {
  return true;
}

function tratarSenha(obj, ev){
  if (infraGetCodigoTecla(ev)==13){
    if (infraTrim(obj.value)==''){
      alert('Senha não informada.');
      return false;
    }
    if (OnSubmitForm()){
      bolProcessando = true;
      document.getElementById('frmIdentificacaoAcesso').submit();
      return true;
    }
  }
}

function finalizar(){
  if (!bolProcessando && '<?=$bolValidado?>'!='1'){
    window.opener.location = '<?=$strLinkNegado?>';
  }
}

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();" onunload="finalizar();"');
?>
<form id="frmIdentificacaoAcesso" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].'&acao_destino='.$_GET['acao_destino'].$strParametros)?>">
  
	<?
	//PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
	PaginaSEI::getInstance()->montarBarraComandosSuperior(array());
	//PaginaSEI::getInstance()->montarAreaValidacao();
	PaginaSEI::getInstance()->abrirAreaDados('10em');
  ?>
  <label id="lblUsuario" for="txtUsuario" accesskey="" class="infraLabelObrigatorio">Usuário:</label>
  <input type="text" id="txtUsuario" name="txtUsuario" class="infraText infraReadOnly" readonly="readonly" value="<?=PaginaSEI::tratarHTML(SessaoSEI::getInstance()->getStrNomeUsuario())?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
  
  <label id="lblSenha" for="pwdSenha" accesskey="" class="infraLabelObrigatorio">Senha:</label>
	<input type="password" id="pwdSenha" name="pwdSenha" autocomplete="off" class="infraText" onkeypress="return tratarSenha(this,event);" value="" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
	
	<?
	PaginaSEI::getInstance()->fecharAreaDados();
	PaginaSEI::getInstance()->montarAreaDebug();
	//PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>