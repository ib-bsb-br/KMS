<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 06/12/2006 - criado por mga
*
*
*/

try {
  require_once dirname(__FILE__).'/Sip.php';

  session_start();
	
  //////////////////////////////////////////////////////////////////////////////
  //InfraDebug::getInstance()->setBolLigado(false);
  //InfraDebug::getInstance()->setBolDebugInfra(true);
  //InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  //SessaoSip::getInstance()->validarSessao();
  SessaoSip::getInstance()->validarLink();

  SessaoSip::getInstance()->validarPermissao($_GET['acao']);

  $objImportarSistemaDTO = new ImportarSistemaDTO();

  $arrComandos = array();
  
  switch($_GET['acao']){
    case 'sistema_importar':
      
      $strTitulo = 'Importar Sistema';
      $arrComandos[] = '<input type="submit" name="sbmImportarSistema" value="Importar" class="infraButton" />';
      $arrComandos[] = '<input type="button" name="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao=sistema_listar').'\';" class="infraButton" />';
			
			$objImportarSistemaDTO->setStrSiglaOrgaoSistemaOrigem($_POST['selOrgaoSistemaOrigem']);
			$objImportarSistemaDTO->setStrSiglaOrigem($_POST['txtSiglaOrigem']);
			$objImportarSistemaDTO->setNumIdOrgaoSistemaDestino($_POST['selOrgaoSistemaDestino']);
			$objImportarSistemaDTO->setNumIdHierarquiaDestino($_POST['selHierarquiaDestino']);
			$objImportarSistemaDTO->setStrSiglaDestino($_POST['txtSiglaDestino']);
			
			$objImportarSistemaDTO->setStrBancoServidor($_POST['txtBancoServidor']);
		  $objImportarSistemaDTO->setStrBancoPorta($_POST['txtBancoPorta']);
		  $objImportarSistemaDTO->setStrBancoNome($_POST['txtBancoNome']);
		  $objImportarSistemaDTO->setStrBancoUsuario($_POST['txtBancoUsuario']);
		  $objImportarSistemaDTO->setStrBancoSenha($_POST['txtBancoSenha']);
      $objImportarSistemaDTO->setStrStaTipoBanco($_POST['selTipoBanco']);
			
      if (isset($_POST['sbmImportarSistema'])) {
        try{
          $objSistemaRN = new SistemaRN();
          $objSistemaDTO = $objSistemaRN->importar($objImportarSistemaDTO);
          header('Location: '.SessaoSip::getInstance()->assinarLink('controlador.php?acao=sistema_listar'.PaginaSip::getInstance()->montarAncora($objSistemaDTO->getNumIdSistema())));
          die;
        }catch(Exception $e){
          PaginaSip::getInstance()->processarExcecao($e);
        }
			}
      break;
      
    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  //$strItensSelOrgaoSistemaOrigem = OrgaoINT::montarSelectSiglaAdministrados('null','&nbsp;', $_POST['selOrgaoSistemaOrigem']);
  //$strItensSelOrgaoSistemaDestino = OrgaoINT::montarSelectSiglaAdministrados('null','&nbsp;', $_POST['selOrgaoSistemaDestino']);

  $strItensSelOrgaoSistemaOrigem = OrgaoINT::montarSelectSiglaSigla('null','&nbsp;', $_POST['selOrgaoSistemaOrigem']);
  $strItensSelOrgaoSistemaDestino = OrgaoINT::montarSelectSigla('null','&nbsp;', $_POST['selOrgaoSistemaDestino']);
  
  $strItensSelHierarquia = HierarquiaINT::montarSelectNome('null','&nbsp;',$_POST['selHierarquiaDestino']);

  $strItensSelTipoBanco = SistemaINT::montarSelectTipoBanco('null','',$objImportarSistemaDTO->getStrStaTipoBanco());

	
}catch(Exception $e){
  PaginaSip::getInstance()->processarExcecao($e);
} 

PaginaSip::getInstance()->montarDocType();
PaginaSip::getInstance()->abrirHtml();
PaginaSip::getInstance()->abrirHead();
PaginaSip::getInstance()->montarMeta();
PaginaSip::getInstance()->montarTitle(PaginaSip::getInstance()->getStrNomeSistema().' - Sistemas');
PaginaSip::getInstance()->montarStyle();
PaginaSip::getInstance()->abrirStyle();
?>

#lblOrgaoSistemaOrigem {position:absolute;left:0%;top:0%;width:20%;}
#selOrgaoSistemaOrigem {position:absolute;left:0%;top:5%;width:20%;}

#lblSiglaOrigem {position:absolute;left:0%;top:14%;width:20%;}
#txtSiglaOrigem {position:absolute;left:0%;top:19%;width:20%;}

#lblOrgaoSistemaDestino {position:absolute;left:0%;top:28%;width:20%;}
#selOrgaoSistemaDestino {position:absolute;left:0%;top:33%;width:20%;}

#lblHierarquiaDestino {position:absolute;left:0%;top:42%;width:20%;}
#selHierarquiaDestino {position:absolute;left:0%;top:47%;width:20%;}

#lblSiglaDestino {position:absolute;left:0%;top:56%;width:15%;}
#txtSiglaDestino {position:absolute;left:0%;top:61%;width:15%;}

#fldBancoOrigem {position:absolute;left:25%;top:0%;height:90%;width:30%;}

#lblBancoServidor {position:absolute;left:10%;top:6%;width:70%;}
#txtBancoServidor {position:absolute;left:10%;top:12%;width:70%;}

#lblBancoPorta {position:absolute;left:10%;top:21%;width:70%;}
#txtBancoPorta {position:absolute;left:10%;top:27%;width:70%;}

#lblBancoNome {position:absolute;left:10%;top:36%;width:70%;}
#txtBancoNome {position:absolute;left:10%;top:42%;width:70%;}

#lblBancoUsuario {position:absolute;left:10%;top:51%;width:70%;}
#txtBancoUsuario {position:absolute;left:10%;top:57%;width:70%;}

#lblBancoSenha {position:absolute;left:10%;top:66%;width:70%;}
#txtBancoSenha {position:absolute;left:10%;top:72%;width:70%;}

#lblTipoBanco {position:absolute;left:10%;top:81%;width:70%;}
#selTipoBanco {position:absolute;left:10%;top:87%;width:70%;}


<?
PaginaSip::getInstance()->fecharStyle();
PaginaSip::getInstance()->montarJavaScript();
PaginaSip::getInstance()->abrirJavaScript();
?>

//<script>

function trocarOrgaoSistema(obj){
	document.getElementById('selOrgaoSistemaOrigem').value='null';
	obj.form.submit();
}

function OnSubmitForm() {
  if(validarForm()){
    infraExibirAviso(false);
    return true;
  }
  return false;
}

function validarForm() {
  if (!infraSelectSelecionado(document.getElementById('selOrgaoSistemaOrigem'))) {
    alert('Selecione Órgão do Sistema Origem.');
    document.getElementById('selOrgaoSistemaOrigem').focus();
    return false;
  }

  if (!infraSelectSelecionado(document.getElementById('txtSiglaOrigem'))) {
    alert('Informe Sigla do Sistema Origem.');
    document.getElementById('txtSiglaOrigem').focus();
    return false;
  }

  if (!infraSelectSelecionado(document.getElementById('selOrgaoSistemaDestino'))) {
    alert('Selecione Órgão do Sistema Destino.');
    document.getElementById('selOrgaoSistemaDestino').focus();
    return false;
  }

  if (!infraSelectSelecionado(document.getElementById('selHierarquiaDestino'))) {
    alert('Selecione Hierarquia de Destino.');
    document.getElementById('selHierarquiaDestino').focus();
    return false;
  }
	
  if (infraTrim(document.getElementById('txtSiglaDestino').value)=='') {
    alert('Informe Sigla de Destino.');
    document.getElementById('txtSiglaDestino').focus();
    return false;
  }

  if (infraTrim(document.getElementById('txtBancoServidor').value)=='') {
    alert('Informe Servidor do Banco de Dados Origem.');
    document.getElementById('txtBancoServidor').focus();
    return false;
  }

  if (infraTrim(document.getElementById('txtBancoPorta').value)=='') {
    alert('Informe Porta do Banco de Dados Origem.');
    document.getElementById('txtBancoPorta').focus();
    return false;
  }

  if (infraTrim(document.getElementById('txtBancoNome').value)=='') {
    alert('Informe Nome do Banco de Dados Origem.');
    document.getElementById('txtBancoNome').focus();
    return false;
  }

  if (infraTrim(document.getElementById('txtBancoUsuario').value)=='') {
    alert('Informe Usuário do Banco de Dados Origem.');
    document.getElementById('txtBancoUsuario').focus();
    return false;
  }

  if (infraTrim(document.getElementById('txtBancoSenha').value)=='') {
    alert('Informe Senha do Banco de Dados Origem.');
    document.getElementById('txtBancoSenha').focus();
    return false;
  }

  if (!infraSelectSelecionado(document.getElementById('selTipoBanco'))) {
    alert('Selecione Tipo do Banco de Dados Origem.');
    document.getElementById('selTipoBanco').focus();
    return false;
  }

  return true;
}

//</script>

<?
PaginaSip::getInstance()->fecharJavaScript();
PaginaSip::getInstance()->fecharHead();
PaginaSip::getInstance()->abrirBody('Importar Sistema');
?>
<form id="frmRecursoLista" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSip::getInstance()->assinarLink('sistema_importar.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?
  //PaginaSip::getInstance()->montarBarraLocalizacao('Importar Sistema');
  PaginaSip::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSip::getInstance()->abrirAreaDados('35em');
  ?>
	
  <label id="lblOrgaoSistemaOrigem" for="selOrgaoSistemaOrigem" accesskey="o" class="infraLabelObrigatorio">Órgã<span class="infraTeclaAtalho">o</span> Origem:</label>
  <select id="selOrgaoSistemaOrigem" name="selOrgaoSistemaOrigem" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" >
  <?=$strItensSelOrgaoSistemaOrigem?>
  </select>

  <label id="lblSiglaOrigem" for="txtSiglaOrigem" accesskey="S" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">S</span>igla Origem:</label>
  <input type="text" id="txtSiglaOrigem" name="txtSiglaOrigem" maxlength="15" class="infraText" value="<?=PaginaSip::tratarHTML($objImportarSistemaDTO->getStrSiglaOrigem());?>" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />

  <label id="lblOrgaoSistemaDestino" for="selOrgaoSistemaDestino" accesskey="D" class="infraLabelObrigatorio">Órgão <span class="infraTeclaAtalho">D</span>estino:</label>
  <select id="selOrgaoSistemaDestino" name="selOrgaoSistemaDestino" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" >
  <?=$strItensSelOrgaoSistemaDestino?>
  </select>

  <label id="lblHierarquiaDestino" for="selHierarquiaDestino" accesskey="H" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">H</span>ierarquia Destino:</label>
  <select id="selHierarquiaDestino" name="selHierarquiaDestino" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>">
  <?=$strItensSelHierarquia?>
  </select>
	
  <label id="lblSiglaDestino" for="txtSiglaDestino" accesskey="i" class="infraLabelObrigatorio">S<span class="infraTeclaAtalho">i</span>gla Destino:</label>
  <input type="text" id="txtSiglaDestino" name="txtSiglaDestino" maxlength="15" class="infraText" value="<?=PaginaSip::tratarHTML($objImportarSistemaDTO->getStrSiglaDestino());?>" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />


	<fieldset id="fldBancoOrigem">
	  <legend style="font-weight: bold;background-color:#e0e0e0;">&nbsp;Banco de Dados Origem&nbsp;</legend>

		<label id="lblBancoServidor" for="txtBancoServidor" accesskey="S" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">S</span>ervidor:</label>
		<input type="text" id="txtBancoServidor" name="txtBancoServidor" class="infraText" value="<?=PaginaSip::tratarHTML($objImportarSistemaDTO->getStrBancoServidor());?>" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />

		<label id="lblBancoPorta" for="txtBancoPorta" accesskey="P" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">P</span>orta:</label>
		<input type="text" id="txtBancoPorta" name="txtBancoPorta" class="infraText" value="<?=PaginaSip::tratarHTML($objImportarSistemaDTO->getStrBancoPorta());?>" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />
		
		<label id="lblBancoNome" for="txtBancoNome" accesskey="i" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">N</span>ome:</label>
		<input type="text" id="txtBancoNome" name="txtBancoNome" class="infraText" value="<?=PaginaSip::tratarHTML($objImportarSistemaDTO->getStrBancoNome());?>" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />

		<label id="lblBancoUsuario" for="txtBancoUsuario" accesskey="i" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">U</span>suário:</label>
		<input type="text" id="txtBancoUsuario" name="txtBancoUsuario" class="infraText" value="<?=PaginaSip::tratarHTML($objImportarSistemaDTO->getStrBancoUsuario());?>" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />
		
		<label id="lblBancoSenha" for="txtBancoSenha" accesskey="i" class="infraLabelObrigatorio">S<span class="infraTeclaAtalho">e</span>nha:</label>
		<input type="password" id="txtBancoSenha" name="txtBancoSenha" autocomplete="off" class="infraText" value="<?=PaginaSip::tratarHTML($objImportarSistemaDTO->getStrBancoSenha());?>" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />

    <label id="lblTipoBanco" for="selTipoBanco" accesskey="" class="infraLabelObrigatorio">Tipo:</label>
    <select id="selTipoBanco" name="selTipoBanco" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>">
      <?=$strItensSelTipoBanco?>
    </select>

	</fieldset>

  <?
  PaginaSip::getInstance()->fecharAreaDados();
  //PaginaSip::getInstance()->montarAreaDebug();
  //PaginaSip::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSip::getInstance()->fecharBody();
PaginaSip::getInstance()->fecharHtml();
?>