<?
try {

  require_once dirname(__FILE__).'/Sip.php';

	session_start();

  /////////////////////////////////////////////////////////////////////////////
  //InfraDebug::getInstance()->setBolLigado(false);
  //InfraDebug::getInstance()->setBolDebugInfra(true);
  //InfraDebug::getInstance()->limpar();
  /////////////////////////////////////////////////////////////////////////////

  foreach($_POST as $item){
    if (is_array($item)){
      throw new InfraException('Dados inválidos.', null, null, false);
    }
  }

  $numLoginSemCaptcha = ConfiguracaoSip::getInstance()->getValor('Sip', 'NumLoginSemCaptcha', false, 3);
  
  if (!isset($_SESSION['SIP_NUM_FALHA_LOGIN'])){
    $_SESSION['SIP_NUM_FALHA_LOGIN'] = 0;
  }

	$strParametros = '';

	if (isset($_GET['modulo_sistema'])){
	  $strParametros .= '&modulo_sistema='.$_GET['modulo_sistema'];
	}

	if (isset($_GET['menu_sistema'])){
	  $strParametros .= '&menu_sistema='.$_GET['menu_sistema'];
	}

  if (isset($_GET['infra_url'])){
    $strParametros .= '&infra_url='.$_GET['infra_url'];
  }

  //Monta campos da pagina 
  //(a) se primeira vez, ou 
  //(b) se retornou validacoes (em caso de erros não faz recarga porque 
  //    vai para pagina de erro)

  $strSiglaSistema = $_GET['sigla_sistema'];
  $strSiglaOrgaoSistema = $_GET['sigla_orgao_sistema'];

  $objSistemaDTO = new SistemaDTO();
  $objSistemaDTO->retNumIdSistema();
  $objSistemaDTO->retStrSigla();
  $objSistemaDTO->retStrSiglaOrgao();

  $objSistemaRN = new SistemaRN();
  $arrObjSistemaDTO = $objSistemaRN->listar($objSistemaDTO);

  $objSistemaDTO = null;
  foreach($arrObjSistemaDTO as $dto){
    if ($dto->getStrSigla()==$strSiglaSistema && $dto->getStrSiglaOrgao()==$strSiglaOrgaoSistema){
      $dto2 = new SistemaDTO();
      $dto2->retNumIdSistema();
      $dto2->retStrSigla();
      $dto2->retStrSiglaOrgao();
      $dto2->retStrLogo();
      $dto2->retStrPaginaInicial();
      $dto2->setNumIdSistema($dto->getNumIdSistema());
      $objSistemaDTO = $objSistemaRN->consultar($dto2);
      break;
    }
  }

  if ($objSistemaDTO==null){
    throw new InfraException('Sistema \''.$strSiglaSistema.'/'.$strSiglaOrgaoSistema.'\' inválido.',null, null, false);
  }

  $strChaveCookie = str_replace(' ','_',$strSiglaOrgaoSistema.'_'.$strSiglaSistema.'_dados_login');

  if (count($_POST)==0 && $_COOKIE[$strChaveCookie] != '' && $_COOKIE[$strChaveCookie] != 'deleted') {
    $arrCookieLogin = explode('/', $_COOKIE[$strChaveCookie]);
    $strSiglaUsuario = $arrCookieLogin[0];
    $numIdOrgao = $arrCookieLogin[1];
    $numIdContexto = $arrCookieLogin[2];
    $strCheckLembrar = 'on';
  }else{
    $strSiglaUsuario = $_POST['txtUsuario'];
    $numIdOrgao = $_POST['selOrgao'];
    $numIdContexto = $_POST['selContexto'];
    $strCheckLembrar = $_POST['chkLembrar'];
  }

  //Se fez POST
	if (isset($_POST['sbmLogin'])){

  	//Deve tratar a exceção pois sem o tratamento no caso de uma validação como
  	//"Senha inválida" não executaria o código abaixo que monta o array em 
  	//javascript.
  	//Validações serão armazenadas pela PaginaLogin para exibição posterior
  	//no caso de erros inesperados serão exibidos imediatamente em uma página
  	//de erros
  	try {


      if ($_SESSION['SIP_NUM_FALHA_LOGIN'] >= $numLoginSemCaptcha && hash('SHA512',$_POST['txtCaptcha']) != $_POST['hdnCaptcha']) {
        $objInfraException = new InfraException();
        $objInfraException->lancarValidacao('Código de confirmação inválido.');
      }else {

        $objLoginDTO = new LoginDTO(true);

        $objLoginDTO->setStrSiglaOrgaoSistema($objSistemaDTO->getStrSiglaOrgao());
        $objLoginDTO->setStrSiglaSistema($objSistemaDTO->getStrSigla());
        $objLoginDTO->setNumIdContexto($_POST['selContexto']);
        $objLoginDTO->setNumIdOrgaoUsuario($_POST['selOrgao']);
        $objLoginDTO->setStrSiglaUsuario($_POST['txtUsuario']);
        $objLoginDTO->setStrSenhaUsuario($_POST['pwdSenha']);

        $objLoginRN = new LoginRN();

        //Autenticação LDAP
        $objLoginRN->autenticar($objLoginDTO);

        $_SESSION['SIP_NUM_FALHA_LOGIN'] = 0;

        //Carrega Dados do usuario
        $objLoginDTO = $objLoginRN->cadastrar($objLoginDTO);

        $strPaginaInicial = $objLoginDTO->getStrPaginaInicialSistema();

        if (strpos($strPaginaInicial, '?') === false) {
          $strPar = '?';
        } else {
          $strPar = '&';
        }

        $strPar .= 'infra_sip=true';
        $strPar .= '&id_sistema=' . $objLoginDTO->getNumIdSistema();
        $strPar .= '&id_contexto=' . $objLoginDTO->getNumIdContexto();
        $strPar .= '&id_usuario=' . $objLoginDTO->getNumIdUsuario();
        $strPar .= '&id_login=' . $objLoginDTO->getStrIdLogin();

        if ($_GET['modulo_sistema'] != '') {
          $strPar .= '&modulo_sistema=' . $_GET['modulo_sistema'];
        }

        if ($_GET['menu_sistema'] != '') {
          $strPar .= '&menu_sistema=' . $_GET['menu_sistema'];
        }

        if ($_GET['infra_url'] != ''){
          $strPar .= '&infra_url=' . $_GET['infra_url'];
        }

        header('Location: ' . $strPaginaInicial . $strPar);
        die;
      }
  	} catch(Exception $e){

      if (strpos($e->__toString(), InfraLDAP::$MSG_USUARIO_SENHA_INVALIDA)!==false) {
        $_SESSION['SIP_NUM_FALHA_LOGIN'] = $_SESSION['SIP_NUM_FALHA_LOGIN'] + 1;
      }

      PaginaLogin::getInstance()->processarExcecao($e);			
  	}
	}


  $strDisplayOrgao = '';
  $strDeslocarUsuario = '';
  $strDeslocarSenha = '';
  $strLarguraAreaRestrita = '45em';
  $strLarguraDivUsuario = '62%';
  $strDisplayCaptcha = 'display:none;';
  $strLarguraDivOpcoes = '62%';

  if ($_SESSION['SIP_NUM_FALHA_LOGIN'] >= $numLoginSemCaptcha){
    $strLarguraAreaRestrita = '55em';
    $strLarguraDivUsuario = '42%';
    $strLarguraDivOpcoes = '69%';
    $strDisplayCaptcha = '';
  }

  $objOrgaoDTO = new OrgaoDTO();
	$objOrgaoDTO->retNumIdOrgao();

	$objOrgaoRN = new OrgaoRN();
	$arrObjOrgaoDTO = $objOrgaoRN->listar($objOrgaoDTO);
	
	//apenas um orgao
	if (count($arrObjOrgaoDTO)==1){

	  $numIdOrgao = $arrObjOrgaoDTO[0]->getNumIdOrgao();

	  $objContextoDTO = new ContextoDTO();
	  $objContextoDTO->setNumIdOrgao($numIdOrgao);
	  
	  $objContextoRN = new ContextoRN();
	  
	  //orgao sem contextos
	  if ($objContextoRN->contar($objContextoDTO)==0){
      $strDisplayOrgao = 'display:none !important;';
      $strDeslocarUsuario = '<br /><br />';
      $strDeslocarSenha = '<br />';
	  }
	}
	
	//Monta itens do Select de orgãos
  $strItensSelOrgao = OrgaoINT::montarSelectLogin('null','&nbsp;',$numIdOrgao);
  $strLinkAjaxContexto = 'controlador_ajax.php?acao_ajax=contexto_carregar_nome';
  $strItensSelContexto = ContextoINT::montarSelectNome('null','&nbsp;',$numIdContexto,$numIdOrgao);

  $strCodigoParaGeracaoCaptcha = InfraCaptcha::obterCodigo();

} catch (Exception $e){
  PaginaLogin::getInstance()->processarExcecao($e);
}

PaginaLogin::getInstance()->montarDocType();
PaginaLogin::getInstance()->abrirHtml();
PaginaLogin::getInstance()->abrirHead();
PaginaLogin::getInstance()->montarMeta();
PaginaLogin::getInstance()->montarTitle($strSiglaSistema.' / '.$strSiglaOrgaoSistema);
PaginaLogin::getInstance()->montarStyle();
PaginaLogin::getInstance()->abrirStyle();
?>

#divInfraAreaGlobal {background-color: #f5f5f5 !important;}
div.infraBarraSistema {padding:.2em 0 .3em 0;}
div.infraBarraSistemaE {width:90%;}
div.infraBarraSistemaD {width:5%;}
div.infraAreaTelaD {border:0;}
div.infraAreaDados {border:0}

#divAreaRestrita {
position:absolute;
top:5%;
left:29%;
height:200px;
width:<?=$strLarguraAreaRestrita?>;
border:1px solid #666;
overflow:hidden;
background-color: white !important; 
}
 
#divSistema {
border:0px solid red;
float:left;
width:150px;
height:200px;
padding:0;
margin:0;
overflow:hidden !important;
background-color: #0494c7 !important;
}

<? if ($objSistemaDTO->getStrLogo()!=null && (PaginaLogin::getInstance()->getNumVersaoInternetExplorer()==null || PaginaLogin::getInstance()->getNumVersaoInternetExplorer()>7)){ ?>
#divSistema {
background:url('data:image/png;base64,<?=$objSistemaDTO->getStrLogo()?>') no-repeat center center;
}

#divSistema div {
display:none;
}

<? } else { ?>

#divSistema{
display: inline-table;
}

.linhaSistema {
width: 100%;
display: table-row;
border:0px solid yellow;
}

.colunaSistema{
width: 100%; 
height: 100%;
text-align: center;
vertical-align: middle;
display: table-cell;
}

.caixaColuna {
display: inline-block;
width: 100%;
text-align: center;
vertical-align: middle;
border:0px solid yellow;
}

.caixaRotulo {
border:0px inset black;
text-align: center;
vertical-align: middle;
color: white;
overflow: hidden;
margin: 2%;
width: 96%;
height: 96%;
padding: .5em 0;
font-weight:bold;
font-size:1.6em; 
}
<? } ?> 
 
#lblSiglaSistemaValor {font-size:1.6em;font-weight:bold;}

#divUsuario {border:0px solid red;float:left;height:80%;width:<?=$strLarguraDivUsuario?>;padding:.4em .4em .4em 1em;}
#divUsuario label {display:block;padding:.2em 0 .01em 0;}
#divUsuario input {display:block}

#lblOrgao {<?=$strDisplayOrgao?>}
#selOrgao {<?=$strDisplayOrgao?>}
#lblContexto {visibility:hidden;}
#selContexto {visibility:hidden;}

#divCaptcha {<?=$strDisplayCaptcha?>}
#divCaptcha {border:0px solid blue;height:80%;float:left;width:14em;padding:.4em;border-left:1px;}

#divCaptcha label {display:block;}
#divCaptcha input {display:block}

#lblCodigo  {padding:1.2em 0 .01em 0;}
#lblCaptcha {padding-top:1.5em}
#txtCaptcha {font-size:2.8em;text-align:center;}

#divOpcoes {border:0px solid green;clear:right;float:left;height:10%;width:<?=$strLarguraDivOpcoes?>;padding:.4em .7em 0 .7em;}
#divLembrar {float:left;}
#divBotoes {float:right;}

<? if (PaginaSip::getInstance()->getNumVersaoSafariIpad()==null && PaginaSip::getInstance()->getNumVersaoSafari()==null) { ?>
  #txtUsuario {width:97%}
  #pwdSenha {width:97%}
  #selOrgao {width:98.5%;}
  #selContexto {width:98.5%;}
  #txtCaptcha {width:4.8em !important;}
<? }else{ ?>
  #txtUsuario {width:93%}
  #pwdSenha {width:93%}
  #selOrgao {width:98.5%;}
  #selContexto {width:98.5%;}
  #txtCaptcha {width:3.8em !important;}

  #lblCaptcha {padding-top:1.7em}
  #lblCodigo  {padding-top:1.3em}

<? } ?>

<? if (PaginaSip::getInstance()->getNumVersaoChrome()!=null || PaginaSip::getInstance()->getNumVersaoInternetExplorer()!=null){ ?>
  #lblCodigo  {padding-top:1em}
  #lblCaptcha {padding-top:1em}
<? } ?>

<? if (PaginaSip::getInstance()->getNumVersaoInternetExplorer() > 0 && PaginaSip::getInstance()->getNumVersaoInternetExplorer() < 8) { ?>
  #divOpcoes {float:none;}
<? } ?>

<?
PaginaLogin::getInstance()->fecharStyle();
PaginaLogin::getInstance()->montarJavaScript();
PaginaLogin::getInstance()->abrirJavaScript();
?>

var objAjaxContexto = null;
function inicializar(){

  objAjaxContexto = new infraAjaxMontarSelect('selContexto','<?=$strLinkAjaxContexto?>');
  objAjaxContexto.mostrarAviso = false;
  objAjaxContexto.prepararExecucao = function(){
    return 'idOrgao=' + document.getElementById('selOrgao').value;
  }
  objAjaxContexto.processarResultado = function(numItens){
  
    if (numItens){
      document.getElementById('lblContexto').style.visibility = 'visible';
      document.getElementById('selContexto').style.visibility = 'visible';
      
      if (numItens == 2){
        document.getElementById('selContexto').options[1].selected = true;
      }
      
    }else{
      document.getElementById('lblContexto').style.visibility = 'hidden';
      document.getElementById('selContexto').style.visibility = 'hidden';
    }
  }

  if (document.getElementById('selContexto').options.length > 0){
    document.getElementById('lblContexto').style.visibility = 'visible';
    document.getElementById('selContexto').style.visibility = 'visible';
  }

	if (document.getElementById('txtUsuario').value == ''){
	  self.setTimeout('document.getElementById(\'txtUsuario\').focus()',100);
	}else{
    self.setTimeout('document.getElementById(\'pwdSenha\').focus()',100);	  
	}
	
	infraAdicionarEvento(window,'resize',posicionar);
	
	posicionar();
}

var numSubmit = 0;

function validarForm() {
  
  if (numSubmit++ > 0){
    return false;
  }

  if (!validarCampos()){
    numSubmit = 0;
    return false;
  }
	
	return true;
}

function validarCampos(){

	if (infraTrim(document.getElementById('txtUsuario').value)==''){
		alert('Informe o Usuário.');
		document.getElementById('txtUsuario').focus();
		return false;
	}
	
	if (infraTrim(document.getElementById('pwdSenha').value)==''){
		alert('Informe a Senha.');
		document.getElementById('pwdSenha').focus();
		return false;
	}

	if (document.getElementById('selOrgao').value=='null'){
		alert('Escolha um Órgão.');
		document.getElementById('selOrgao').focus();
		return false;
	}

	if (document.getElementById('selContexto').options.length > 0 && !infraSelectSelecionado(document.getElementById('selContexto'))){
		alert('Escolha um Contexto.');
		document.getElementById('selContexto').focus();
		return false;
	}

  <? if ($_SESSION['SIP_NUM_FALHA_LOGIN'] >= $numLoginSemCaptcha){ ?>
  if (infraTrim(document.getElementById('txtCaptcha').value)=='') {
    alert('Informe o código de confirmação.');
    document.getElementById('txtCaptcha').focus();
    return false;
  }
  <? } ?>

  if (document.getElementById('chkLembrar').checked){
    infraCriarCookie('<?=$strChaveCookie?>', document.getElementById('txtUsuario').value + '/' + document.getElementById('selOrgao').value + '/' + document.getElementById('selContexto').value, 3650);
  }else{
    infraRemoverCookie('<?=$strChaveCookie?>');
  }

	return true;
}

function posicionar(){

  var fator = 1.7;
  
  if (INFRA_IE && INFRA_IE < 8){
    fator = 2.2;
  }
  
  var hDados = (infraClientHeight()-(document.getElementById('divInfraBarraSuperior').offsetHeight+document.getElementById('divInfraBarraSistema').offsetHeight)*fator);
  
  if (hDados > 0){
    document.getElementById('divInfraAreaDados').style.height = hDados + 'px';
  
    var f = document.getElementById('divAreaRestrita');
    
    var p = (infraClientWidth()-f.offsetWidth)/2;
    f.style.left = (p>0?p:1) + 'px'; 
    
    p = (hDados-f.offsetHeight)/2.3;
    f.style.top = (p>0?p:1) + 'px'; 
  }
}

<?
PaginaLogin::getInstance()->fecharJavaScript();
PaginaLogin::getInstance()->fecharHead();
PaginaLogin::getInstance()->abrirBody('','onload="inicializar();"');
PaginaLogin::getInstance()->abrirAreaDados("50em");
?>

<form name="frmLogin" action="<?='login.php?sigla_orgao_sistema='.$strSiglaOrgaoSistema.'&sigla_sistema='.$strSiglaSistema.$strParametros?>" method="post" onsubmit="return validarForm();">
  
  <div id="divAreaRestrita">
    <div id="divSistema">
      <div class="linhaSistema">
        <div class="colunaSistema">
          <div class="caixaColuna">
            <div class="caixaRotulo"><?=PaginaSip::tratarHTML($strSiglaSistema.($_GET['modulo_sistema']!=''?' / '.$_GET['modulo_sistema']:''))?></div>
          </div>
        </div>
      </div>
    </div>

    <div id="divUsuario">

      <?=$strDeslocarUsuario?>
      <label id="lblUsuario" accesskey="U" for="txtUsuario" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">U</span>suário:</label>
      <input type="text" id="txtUsuario" name="txtUsuario" class="infraText" value="<?=PaginaSip::tratarHTML($strSiglaUsuario)?>" tabindex="<?=PaginaLogin::getInstance()->getProxTabDados()?>" />
      <?=$strDeslocarSenha?>
      <label id="lblSenha" accesskey="e" for="pwdSenha" class="infraLabelObrigatorio">S<span class="infraTeclaAtalho">e</span>nha:</label>
      <input type="password" id="pwdSenha" name="pwdSenha" class="infraText" autocomplete="off" value="" tabindex="<?=PaginaLogin::getInstance()->getProxTabDados()?>" />

      <label id="lblOrgao" accesskey="r" for="selOrgao" class="infraLabelObrigatorio">Ó<span class="infraTeclaAtalho">r</span>gão:</label>
      <select id="selOrgao" name="selOrgao" onchange="objAjaxContexto.executar();" class="infraSelect" tabindex="<?=PaginaLogin::getInstance()->getProxTabDados()?>">
      <?=$strItensSelOrgao?>
      </select>

      <label id="lblContexto" accesskey="C" for="selContexto" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">C</span>ontexto:</label>
      <select id="selContexto" name="selContexto" class="infraSelect" tabindex="<?=PaginaLogin::getInstance()->getProxTabDados()?>">
      <?=$strItensSelContexto?>
      </select>

    </div>

    <div id="divCaptcha">
      <? if ($_SESSION['SIP_NUM_FALHA_LOGIN'] >= $numLoginSemCaptcha){ ?>
      <label id="lblCaptcha" accesskey="" class="infraLabelObrigatorio"><img src="/infra_js/infra_gerar_captcha.php?codetorandom=<?=$strCodigoParaGeracaoCaptcha;?>" alt="Não foi possível carregar imagem de confirmação" /></label>
      <label id="lblCodigo" for="txtCaptcha" accesskey="" class="infraLabelObrigatorio">Código de confirmação:</label>
      <input type="text" id="txtCaptcha" name="txtCaptcha" class="infraText" maxlength="4" value="" tabindex="<?=PaginaLogin::getInstance()->getProxTabDados()?>"/>
      <? } ?>
    </div>

    <div id="divOpcoes">

      <div id="divLembrar" class="infraDivCheckbox">
        <input type="checkbox" id="chkLembrar" name="chkLembrar" <?=($strCheckLembrar=='on'?'checked="checked"':'')?> class="infraCheckbox" tabindex="<?=PaginaLogin::getInstance()->getProxTabDados()?>" />
        <label id="lblLembrar" accesskey="m" for="chkLembrar" class="infraLabelCheckbox">Le<span class="infraTeclaAtalho">m</span>brar</label>
      </div>

      <div id="divBotoes">
        <button type="submit" accesskey="a" id="sbmLogin" name="sbmLogin" value="Acessar" class="infraButton" tabindex="<?=PaginaLogin::getInstance()->getProxTabDados()?>"><span class="infraTeclaAtalho">A</span>cessar</button>
      </div>

    </div>

  </div>

  <input type="hidden" id="hdnCaptcha" name="hdnCaptcha" class="infraText" value="<?=hash('SHA512',InfraCaptcha::gerar($strCodigoParaGeracaoCaptcha))?>" />
	
</form>
<?
PaginaLogin::getInstance()->fecharAreaDados();
PaginaLogin::getInstance()->montarAreaDebug();
PaginaLogin::getInstance()->fecharBody();
PaginaLogin::getInstance()->fecharHtml();
?>