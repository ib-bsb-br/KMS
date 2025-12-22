<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 23/04/2012 - criado por bcu
*/

try {
  require_once dirname(__FILE__).'/SEI.php';

  session_start();
  
  //////////////////////////////////////////////////////////////////////////////
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(false);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////
	
  SessaoSEIExterna::getInstance()->validarLink();

  $numTamSenhaUsuarioExterno = ConfiguracaoSEI::getInstance()->getValor('SEI', 'TamSenhaUsuarioExterno', false, TAM_SENHA_USUARIO_EXTERNO);

  PaginaSEIExterna::getInstance()->setTipoPagina(PaginaSEIExterna::$TIPO_PAGINA_SEM_MENU);
  PaginaSEIExterna::getInstance()->salvarCamposPost(array('selUf','selCidade'));
  
  $strDisplayMensagem = '';
  $strDisplayCadastro = '';
  $strTextoFormulario = '';

  switch($_GET['acao']){

    case 'usuario_externo_avisar_cadastro':
           
      $strTitulo = 'Cadastro de Usuário Externo';

      $strDisplayMensagem = '';
      $strDisplayCadastro = 'display:none;';
      
      $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
      $strTextoFormulario = $objInfraParametro->getValor('SEI_MSG_AVISO_CADASTRO_USUARIO_EXTERNO');
      
      if ($strTextoFormulario==''){
        header('Location: '.SessaoSEIExterna::getInstance()->assinarLink('controlador_externo.php?acao=usuario_externo_enviar_cadastro&acao_origem='.$_GET['acao']));
        die;
      }

      $strTextoFormulario .= '<br /><br /><a id="lnkCadastro" href="'.SessaoSEIExterna::getInstance()->assinarLink('controlador_externo.php?acao=usuario_externo_enviar_cadastro&acao_origem='.$_GET['acao']).'">Clique aqui para continuar</a>';
      
      break;
    
    case 'usuario_externo_enviar_cadastro':
          
      $strTitulo = 'Cadastro de Usuário Externo';

      $strCodigoParaGeracaoCaptcha = InfraCaptcha::obterCodigo();
      
      $strDisplayMensagem = 'display:none;';
      $strDisplayCadastro = '';
      
      //SessaoSEIExterna::getInstance()->validarPermissao($_GET['acao']);
      if (isset($_POST['sbmEnviar'])) {
        
        if (hash('SHA512',$_POST['txtCaptcha']) != $_POST['hdnCaptcha']){
          PaginaSEIExterna::getInstance()->setStrMensagem('Código de confirmação inválido.');
        }else{
        
          try {
            $objUsuarioDTO = new UsuarioDTO();
            $objUsuarioDTO->setStrSigla($_POST['txtEmail']);
            $objUsuarioDTO->retNumIdUsuario();
            $objUsuarioDTO->retStrStaTipo();
            $objUsuarioDTO->setStrStaTipo(array(UsuarioRN::$TU_EXTERNO_PENDENTE,UsuarioRN::$TU_EXTERNO),InfraDTO::$OPER_IN);
            $objUsuarioRN=new UsuarioRN();
            $objUsuarioDTO=$objUsuarioRN->consultarRN0489($objUsuarioDTO);
            $objInfraException = new InfraException();
            if ($objUsuarioDTO!=null) {
              if ($objUsuarioDTO->getStrStaTipo()==UsuarioRN::$TU_EXTERNO_PENDENTE){
                $objInfraException->lancarValidacao('Já existe cadastro pendente relacionado com este email.');
              }
              if ($objUsuarioDTO->getStrStaTipo()==UsuarioRN::$TU_EXTERNO) {
                $objInfraException->lancarValidacao('Já existe usuário cadastrado com este email.');
              }
            } else {
              $objUsuarioDTO = new UsuarioDTO();
              $objUsuarioDTO->setStrSigla($_POST['txtEmail']);
              $objUsuarioDTO->setNumIdUsuario(null);
              $objUsuarioDTO->setNumIdOrgao($_GET['id_orgao_acesso_externo']);
              $objUsuarioDTO->setStrIdOrigem(null);
              $objUsuarioDTO->setStrNome($_POST['txtNome']);
              $objUsuarioDTO->setDblCpfContato(InfraUtil::retirarFormatacao($_POST['txtCpf']));
              $objUsuarioDTO->setStrStaTipo(UsuarioRN::$TU_EXTERNO_PENDENTE);
              $objUsuarioDTO->setStrSenha($_POST['pwdSenha']);
              $objUsuarioDTO->setStrEnderecoContato($_POST['txtEndereco']);
              $objUsuarioDTO->setStrComplementoContato($_POST['txtComplemento']);
              $objUsuarioDTO->setStrCepContato($_POST['txtCep']);
              $objUsuarioDTO->setDblRgContato($_POST['txtRg']);
              $objUsuarioDTO->setStrOrgaoExpedidorContato($_POST['txtExpedidor']);
              $objUsuarioDTO->setStrBairroContato($_POST['txtBairro']);
              $objUsuarioDTO->setStrTelefoneFixoContato($_POST['txtTelefoneFixo']);
              $objUsuarioDTO->setStrTelefoneCelularContato($_POST['txtTelefoneCelular']);
              $objUsuarioDTO->setNumIdCidadeContato($_POST['selCidade']);
              $objUsuarioDTO->setNumIdUfContato($_POST['selUf']);
              $objUsuarioDTO->setNumIdPaisContato(null);

              $objPaisDTO = new PaisDTO();
              $objPaisDTO->retNumIdPais();
              $objPaisDTO->retStrNome();

              $objPaisRN = new PaisRN();
              $arrObjPaisDTO = $objPaisRN->listar($objPaisDTO);

              foreach($arrObjPaisDTO as $objPaisDTO){
                if (InfraString::transformarCaixaAlta($objPaisDTO->getStrNome())=='BRASIL'){
                  $objUsuarioDTO->setNumIdPaisContato($objPaisDTO->getNumIdPais());
                }
              }

              $objUsuarioDTO->setStrSinAcessibilidade('N');
              $objUsuarioDTO->setStrSinAtivo('S');
               
              $objUsuarioRN->cadastrarExterno($objUsuarioDTO);
              PaginaSEIExterna::getInstance()->adicionarMensagem('IMPORTANTE: As instruções para ativar o seu cadastro foram encaminhadas para o seu e-mail.');
              header('Location: '.SessaoSEIExterna::getInstance()->assinarLink('controlador_externo.php?acao=usuario_externo_logar'));
              die;
            }
          } catch (Exception $e) {
            PaginaSEIExterna::getInstance()->processarExcecao($e, true);
          }
        }      	
      }
      
      $strItensSelUf = UfINT::montarSelectSiglaRI0416('null','&nbsp;',$_POST['selUf']);
      $strLinkAjaxCidade = SessaoSEIExterna::getInstance()->assinarLink('controlador_ajax_externo.php?acao_ajax=cidade_montar_select_id_cidade_nome');
      $strItensSelCidade = CidadeINT::montarSelectIdCidadeNome('null','&nbsp;',$_POST['selCidade'],$_POST['selUf']);
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }
  

}catch(Exception $e){

  PaginaSEIExterna::getInstance()->processarExcecao($e);
} 


PaginaSEIExterna::getInstance()->montarDocType();
PaginaSEIExterna::getInstance()->abrirHtml();
PaginaSEIExterna::getInstance()->abrirHead();
PaginaSEIExterna::getInstance()->montarMeta();
PaginaSEIExterna::getInstance()->montarTitle(PaginaSEIExterna::getInstance()->getStrNomeSistema().' - '.$strTitulo);
PaginaSEIExterna::getInstance()->montarStyle();
PaginaSEIExterna::getInstance()->abrirStyle();
?>

div.infraBarraSistemaE {width:80%;}
div.infraBarraSistemaD {width:15%;}

#lblNome {position:absolute;left:0%;top:10%;width:58%;}
#txtNome {position:absolute;left:0%;top:16%;width:58%;}

#lblCpf {position:absolute;left:0%;top:25%;width:19%;}
#txtCpf {position:absolute;left:0%;top:31%;width:19%;}
#lblRg {position:absolute;left:21%;top:25%;width:20%;}
#txtRg {position:absolute;left:21%;top:31%;width:20%;}
#lblExpedidor {position:absolute;left:43%;top:25%;width:15%;}
#txtExpedidor {position:absolute;left:43%;top:31%;width:15%;}

#lblTelefoneFixo {position:absolute;left:0%;top:40%;width:19%;}
#txtTelefoneFixo {position:absolute;left:0%;top:46%;width:19%;}
#lblTelefoneCelular {position:absolute;left:21%;top:40%;width:20%;}
#txtTelefoneCelular {position:absolute;left:21%;top:46%;width:20%;}

#lblEndereco {position:absolute;left:0%;top:55%;width:58%;}
#txtEndereco {position:absolute;left:0%;top:61%;width:58%;}

#lblComplemento {position:absolute;left:0%;top:70%;width:41%;}
#txtComplemento {position:absolute;left:0%;top:76%;width:41%;}
#lblBairro {position:absolute;left:43%;top:70%;width:15%;}
#txtBairro {position:absolute;left:43%;top:76%;width:15%;}

#lblIdUf {position:absolute;left:0%;top:85%;width:10%;}
#selUf {position:absolute;left:0%;top:91%;width:10%;}
#lblIdCidade {position:absolute;left:12%;top:85%;width:29%;}
#selCidade {position:absolute;left:12%;top:91%;width:29%;}
#lblCep {position:absolute;left:43%;top:85%;width:15%;}
#txtCep {position:absolute;left:43%;top:91%;width:15%;}


#lblEmail {position:absolute;left:0%;top:12%;width:33%;}
#txtEmail {position:absolute;left:0%;top:19%;width:33%;}
#lblSenha {position:absolute;left:0%;top:30%;}
#pwdSenha {position:absolute;left:0%;top:37%;width:19%;}
#lblSenhaConfirma {position:absolute;left:0%;top:48%;}
#pwdSenhaConfirma {position:absolute;left:0%;top:55%;width:19%;}

#lblCaptcha {position:absolute;left:0%;top:67%;}
#lblCodigo  {position:absolute;left:35%;top:72%;}
#txtCaptcha {position:absolute;left:21%;top:67%;width:12%;height:15%;text-align:center;font-size:3em !important;}

#sbmEnviar {position:absolute;left:0%;top:89%;width:8%;}
#btnVoltar {position:absolute;left:9%;top:89%;width:8%;}

.infraLabelTitulo{
  position:absolute;
  left:0%;
  width:58% !important;
}

<?
PaginaSEIExterna::getInstance()->fecharStyle();
PaginaSEIExterna::getInstance()->montarJavaScript();
PaginaSEIExterna::getInstance()->abrirJavaScript();
?>

function inicializar(){

  <?if ($_GET['acao']=='usuario_externo_enviar_cadastro'){?>
    document.getElementById('txtNome').focus();
  <?}?>
  
    //Ajax para carregar as cidades na escolha do estado
  objAjaxCidade = new infraAjaxMontarSelectDependente('selUf','selCidade','<?=$strLinkAjaxCidade?>');
  objAjaxCidade.prepararExecucao = function(){
    return infraAjaxMontarPostPadraoSelect('null','','null') + '&idUf='+document.getElementById('selUf').value;
  }
  objAjaxCidade.processarResultado = function(){
    //alert('terminou carregamento');
  }
  
  
  infraEfeitoTabelas();
}

function OnSubmitForm() {
  return validarForm();
}

function validarForm() {

  if (infraTrim(document.getElementById('txtNome').value)=='') {
    alert('Informe o Nome do Representante.');
    document.getElementById('txtNome').focus();
    return false;
  }
  
  if (infraTrim(document.getElementById('txtCpf').value)=='') {
    alert('Informe o CPF.');
    document.getElementById('txtCpf').focus();
    return false;
  }

  if (!infraValidarCpf(infraTrim(document.getElementById('txtCpf').value))){
		alert('CPF Inválido.');
		document.getElementById('txtCpf').focus();
		return false;
	}

	if (infraTrim(document.getElementById('txtRg').value)=='') {
    alert('Informe o RG.');
    document.getElementById('txtRg').focus();
    return false;
  }

  if (infraTrim(document.getElementById('txtExpedidor').value)=='') {
    alert('Informe o Órgão Expedidor.');
    document.getElementById('txtExpedidor').focus();
    return false;    
  }
  
	if (infraTrim(document.getElementById('txtTelefoneFixo').value)=='' && infraTrim(document.getElementById('txtTelefoneCelular').value)=='') {
    alert('É necessário informar pelo menos um número de telefone (fixo ou celular).');
    document.getElementById('txtTelefoneFixo').focus();
    return false;
  }
  
  if (infraTrim(document.getElementById('txtEndereco').value)=='') {
    alert('Informe o Endereço Residencial.');
    document.getElementById('txtEndereco').focus();
    return false;
  }

  if (!infraSelectSelecionado('selUf')) {
    alert('Selecione um Estado.');
    document.getElementById('selUf').focus();
    return false;
  }
  
  if (!infraSelectSelecionado('selCidade')) {
    alert('Selecione uma Cidade.');
    document.getElementById('selCidade').focus();
    return false;
  }
  
  if (infraTrim(document.getElementById('txtCep').value)=='') {
    alert('Informe o CEP.');
    document.getElementById('txtCep').focus();
    return false;
  }
  
  if (infraTrim(document.getElementById('txtEmail').value)=='') {
    alert('Informe o E-mail.');
    document.getElementById('txtEmail').focus();
    return false;
  }

  if (!infraValidarEmail(infraTrim(document.getElementById('txtEmail').value))){
		alert('E-mail Inválido.');
		document.getElementById('txtEmail').focus();
		return false;
	}

  if (infraTrim(document.getElementById('pwdSenha').value)=='') {
    alert('Informe a Senha.');
    document.getElementById('pwdSenha').focus();
    return false;
  }

  if (infraTrim(document.getElementById('pwdSenha').value).length < <?=$numTamSenhaUsuarioExterno?>) {
    alert('A Senha deve ter pelo menos <?=$numTamSenhaUsuarioExterno?> caracteres.');
    document.getElementById('pwdSenha').focus();
    return false;
  }
  
  if (infraTrim(document.getElementById('pwdSenhaConfirma').value)=='') {
    alert('Repita a Senha.');
    document.getElementById('pwdSenhaConfirma').focus();
    return false;
  }

  if (infraTrim(document.getElementById('pwdSenha').value)!=infraTrim(document.getElementById('pwdSenhaConfirma').value)) {
    alert('Confirmação de Senha não confere.');
    document.getElementById('pwdSenhaConfirma').focus();
    return false;
  }
  
  if (infraTrim(document.getElementById('txtCaptcha').value)=='') {
    alert('Informe o código de confirmação.'); 
    document.getElementById('txtCaptcha').focus();
    return false; 
  }

  return true;
}

<?
PaginaSEIExterna::getInstance()->fecharJavaScript();
PaginaSEIExterna::getInstance()->fecharHead();
PaginaSEIExterna::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmUsuarioExterno" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEIExterna::getInstance()->assinarLink('controlador_externo.php?acao='.$_GET['acao'])?>">

<div class="formularioTexto"><?=$strTextoFormulario?></div>

<div id="divDadosCadastrais" class="infraAreaDados" style="height:30em;<?=$strDisplayCadastro?>">
   
	<label id="lblDadosUnidade"  accesskey="" class="infraLabelTitulo">&nbsp;&nbsp;Dados Cadastrais</label>

  <label id="lblNome" for="txtNome" accesskey="" class="infraLabelObrigatorio">Nome do Representante:</label>
  <input type="text" id="txtNome" name="txtNome" onkeypress="return infraMascaraTexto(this,event,250);" maxlength="250" class="infraText" value="<?=PaginaSEIExterna::tratarHTML($_POST['txtNome'])?>" tabindex="<?=PaginaSEIExterna::getInstance()->getProxTabDados()?>" />

  <label id="lblCpf" for="txtCpf" accesskey="" class="infraLabelObrigatorio">CPF:</label>
  <input type="text" id="txtCpf" name="txtCpf" onkeypress="return infraMascaraCpf(this,event);" maxlength="15" class="infraText" value="<?=PaginaSEIExterna::tratarHTML($_POST['txtCpf'])?>" tabindex="<?=PaginaSEIExterna::getInstance()->getProxTabDados()?>" />

  <label id="lblRg" for="txtRg" accesskey="" class="infraLabelObrigatorio">RG:</label>
  <input type="text" id="txtRg" name="txtRg" onkeypress="return infraMascaraNumero(this,event,15);" maxlength="15" class="infraText" value="<?=PaginaSEIExterna::tratarHTML($_POST['txtRg'])?>" tabindex="<?=PaginaSEIExterna::getInstance()->getProxTabDados()?>" />
  
  <label id="lblExpedidor" for="txtExpedidor" accesskey="" class="infraLabelObrigatorio">Órgão Expedidor:</label>
  <input type="text" id="txtExpedidor" name="txtExpedidor" onkeypress="return infraMascaraTexto(this,event,50);" maxlength="50" class="infraText" value="<?=PaginaSEIExterna::tratarHTML($_POST['txtExpedidor'])?>" tabindex="<?=PaginaSEIExterna::getInstance()->getProxTabDados()?>" />
  
  <label id="lblTelefoneFixo" for="txtTelefoneFixo" accesskey="" class="infraLabelOpcional">Telefone Fixo:</label>
  <input type="text" id="txtTelefoneFixo" name="txtTelefoneFixo" class="infraText" value="<?=PaginaSEIExterna::tratarHTML($_POST['txtTelefoneFixo'])?>" onkeypress="return infraMascaraTelefone(this,event);" maxlength="25" tabindex="<?=PaginaSEIExterna::getInstance()->getProxTabDados()?>" />

  <label id="lblTelefoneCelular" for="txtTelefoneCelular" accesskey="" class="infraLabelOpcional">Telefone Celular:</label>
  <input type="text" id="txtTelefoneCelular" name="txtTelefoneCelular" class="infraText" value="<?=PaginaSEIExterna::tratarHTML($_POST['txtTelefoneCelular'])?>" onkeypress="return infraMascaraTelefone(this,event);" maxlength="25" tabindex="<?=PaginaSEIExterna::getInstance()->getProxTabDados()?>" />

  <label id="lblEndereco" for="txtEndereco" accesskey="" class="infraLabelObrigatorio">Endereço Residencial:</label>
  <input type="text" id="txtEndereco" name="txtEndereco" class="infraText" value="<?=PaginaSEIExterna::tratarHTML($_POST['txtEndereco'])?>" onkeypress="return infraMascaraTexto(this,event,130);" maxlength="130" tabindex="<?=PaginaSEIExterna::getInstance()->getProxTabDados()?>" />

  <label id="lblComplemento" for="txtComplemento" accesskey="" class="infraLabelOpcional">Complemento:</label>
  <input type="text" id="txtComplemento" name="txtComplemento" class="infraText" value="<?=PaginaSEIExterna::tratarHTML($_POST['txtComplemento'])?>" onkeypress="return infraMascaraTexto(this,event,130);" maxlength="130" tabindex="<?=PaginaSEIExterna::getInstance()->getProxTabDados()?>" />

  <label id="lblBairro" for="txtBairro" accesskey="" class="infraLabelOpcional">Bairro:</label>
  <input type="text" id="txtBairro" name="txtBairro" class="infraText" value="<?=PaginaSEIExterna::tratarHTML($_POST['txtBairro'])?>" onkeypress="return infraMascaraTexto(this,event,130);" maxlength="130" tabindex="<?=PaginaSEIExterna::getInstance()->getProxTabDados()?>" />

  <label id="lblIdUf" for="selUf" accesskey="" class="infraLabelObrigatorio">Estado:</label>
  <select id="selUf" name="selUf" class="infraSelect" tabindex="<?=PaginaSEIExterna::getInstance()->getProxTabDados()?>">
    <?=$strItensSelUf?>
  </select>

  <label id="lblIdCidade" for="selCidade" accesskey="" class="infraLabelObrigatorio">Cidade:</label>
  <select id="selCidade"  name="selCidade" class="infraSelect" tabindex="<?=PaginaSEIExterna::getInstance()->getProxTabDados()?>">
    <?=$strItensSelCidade?>
  </select>
   
  <label id="lblCep" for="txtCep" accesskey="" class="infraLabelObrigatorio">CEP:</label>
  <input type="text" id="txtCep" name="txtCep" class="infraText" value="<?=PaginaSEIExterna::tratarHTML($_POST['txtCep'])?>" onkeypress="return infraMascaraCEP(this,event);" maxlength="9" tabindex="<?=PaginaSEIExterna::getInstance()->getProxTabDados()?>" />


</div>
<br />
<div id="divCadastroAutenticacao" class="infraAreaDados" style="height:25em;<?=$strDisplayCadastro?>">
  	
	<label id="lblTituloAutenticacao" accesskey="" class="infraLabelTitulo">&nbsp;&nbsp;Dados de Autenticação</label>
	
  <label id="lblEmail" for="txtEmail" accesskey="" class="infraLabelObrigatorio">E-mail:</label>
  <input type="email" id="txtEmail" name="txtEmail" class="infraText" value="<?=PaginaSEIExterna::tratarHTML($_POST['txtEmail'])?>" onkeypress="return infraMascaraTexto(this,event,100);" maxlength="100" tabindex="<?=PaginaSEIExterna::getInstance()->getProxTabDados()?>" />

  <label id="lblSenha" for="pwdSenha" accesskey="" class="infraLabelObrigatorio">Senha (no mínimo <?=$numTamSenhaUsuarioExterno?> caracteres com letras e números):</label>
  <input type="password" id="pwdSenha" name="pwdSenha" autocomplete="off" class="infraText" value="" tabindex="<?=PaginaSEIExterna::getInstance()->getProxTabDados()?>" />

  <label id="lblSenhaConfirma" for="pwdSenhaConfirma" accesskey="" class="infraLabelObrigatorio">Confirmar Senha:</label>  
  <input type="password" id="pwdSenhaConfirma" name="pwdSenhaConfirma" autocomplete="off" class="infraText" value="" tabindex="<?=PaginaSEIExterna::getInstance()->getProxTabDados()?>" />
    	
  <label id="lblCodigo" for="txtCaptcha" accesskey="" class="labelOpcional">Digite o código da imagem ao lado</label>
  <label id="lblCaptcha" accesskey="" class="infraLabelObrigatorio"><img src="/infra_js/infra_gerar_captcha.php?codetorandom=<?=$strCodigoParaGeracaoCaptcha;?>" alt="Não foi possível carregar a imagem de confirmação" /></label>
  <input type="text" id="txtCaptcha" name="txtCaptcha" class="infraText" maxlength="4" value="" />
    	
  <button type="submit" accesskey="" id="sbmEnviar" class="infraButton" name="sbmEnviar" value="Enviar" title="Enviar" >Enviar</button>
  <button type="button" accesskey="" id="btnVoltar" name="btnVoltar" value="Voltar" onclick="location.href='<?=SessaoSEIExterna::getInstance()->assinarLink('controlador_externo.php?acao=usuario_externo_logar&acao_origem='.$_GET['acao'])?>';" class="infraButton">Voltar</button>
</div>

<input type="hidden" id="hdnCaptcha" name="hdnCaptcha" value="<?=hash('SHA512',InfraCaptcha::gerar($strCodigoParaGeracaoCaptcha));?>" />
</form>
<?
PaginaSEIExterna::getInstance()->montarAreaDebug();
PaginaSEIExterna::getInstance()->fecharBody();
PaginaSEIExterna::getInstance()->fecharHtml();
?>