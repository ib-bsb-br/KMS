<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 04/01/2007 - criado por mga
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

  PaginaSip::getInstance()->salvarCamposPost(array('selOrgao','selHierarquia'));
	
  $objSistemaDTO = new SistemaDTO();

  $arrComandos = array();

  switch($_GET['acao']){
    
    case 'sistema_upload':
      //Trata do campo file que é postado para a mesma ação
      if (isset($_FILES['filArquivo'])){
        PaginaSip::getInstance()->processarUpload('filArquivo', DIR_SIP_TEMP, false);
      }
      die;
    
    case 'sistema_cadastrar':
      $strTitulo = 'Novo Sistema';
      $arrComandos[] = '<input type="submit" name="sbmCadastrarSistema" value="Salvar" class="infraButton" />';
      $arrComandos[] = '<input type="button" name="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno()).'\';" class="infraButton" />';
			
			$objSistemaDTO->setNumIdSistema(null);
			
			$numIdOrgao = PaginaSip::getInstance()->recuperarCampo('selOrgao');
			if ($numIdOrgao!==''){
				$objSistemaDTO->setNumIdOrgao($numIdOrgao);
			}else{
				$objSistemaDTO->setNumIdOrgao(null);
			}

			$numIdHierarquia = PaginaSip::getInstance()->recuperarCampo('selHierarquia');
			if ($numIdHierarquia!==''){
				$objSistemaDTO->setNumIdHierarquia($numIdHierarquia);
			}else{
				$objSistemaDTO->setNumIdHierarquia(null);
			}
			
			$objSistemaDTO->setStrSigla($_POST['txtSigla']);
			$objSistemaDTO->setStrDescricao($_POST['txtDescricao']);
			$objSistemaDTO->setStrPaginaInicial($_POST['txtPaginaInicial']);
			$objSistemaDTO->setStrWebService($_POST['txtWebService']);
			$objSistemaDTO->setStrLogo(null);
			$objSistemaDTO->setStrNomeArquivo($_POST['hdnNomeArquivo']);
			$objSistemaDTO->setStrSinAtivo("S");
			
      if (isset($_POST['sbmCadastrarSistema'])) {
        try{
          $objSistemaRN = new SistemaRN();
          $objSistemaDTO = $objSistemaRN->cadastrar($objSistemaDTO);
          PaginaSip::getInstance()->setStrMensagem('Sistema "'.$objSistemaDTO->getStrSigla().'" cadastrado com sucesso.');
          header('Location: '.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSip::getInstance()->montarAncora($objSistemaDTO->getNumIdSistema())));
          die;
        }catch(Exception $e){
          PaginaSip::getInstance()->processarExcecao($e);
        }
			}

      break;

    case 'sistema_alterar':
      $strTitulo = 'Alterar Sistema';
      $arrComandos[] = '<input type="submit" name="sbmAlterarSistema" value="Salvar" class="infraButton" />';

			if (isset($_GET['id_sistema'])){
        $objSistemaDTO->setNumIdSistema($_GET['id_sistema']);
        $objSistemaDTO->retTodos();
        $objSistemaRN = new SistemaRN();
        $objSistemaDTO = $objSistemaRN->consultar($objSistemaDTO);
        if ($objSistemaDTO==null){
          throw new InfraException("Registro não encontrado.");
        }
			}else{
				$objSistemaDTO->setNumIdSistema($_POST['hdnIdSistema']);
				$objSistemaDTO->setNumIdOrgao($_POST['selOrgao']);
				$objSistemaDTO->setNumIdHierarquia($_POST['selHierarquia']);
				$objSistemaDTO->setStrSigla($_POST['txtSigla']);
				$objSistemaDTO->setStrDescricao($_POST['txtDescricao']);
				$objSistemaDTO->setStrPaginaInicial($_POST['txtPaginaInicial']);
				$objSistemaDTO->setStrWebService($_POST['txtWebService']);
				$objSistemaDTO->setStrLogo($_POST['hdnLogo']);
				$objSistemaDTO->setStrNomeArquivo($_POST['hdnNomeArquivo']);
				$objSistemaDTO->setStrSinAtivo("S");
			}

      $arrComandos[] = '<input type="button" name="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao=sistema_listar&acao_origem='.$_GET['acao'].PaginaSip::getInstance()->montarAncora($objSistemaDTO->getNumIdSistema())).'\';" class="infraButton" />';
			
      if (isset($_POST['sbmAlterarSistema'])) {
        try{
          $objSistemaRN = new SistemaRN();
          $objSistemaRN->alterar($objSistemaDTO);
          PaginaSip::getInstance()->setStrMensagem('Sistema "'.$objSistemaDTO->getStrSigla().'" alterado com sucesso.');
          header('Location: '.SessaoSip::getInstance()->assinarLink('controlador.php?acao=sistema_listar'.PaginaSip::getInstance()->montarAncora($objSistemaDTO->getNumIdSistema())));
          die;
        }catch(Exception $e){
          PaginaSip::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'sistema_consultar':
      $strTitulo = "Consultar Sistema";
      $arrComandos[] = '<input type="button" name="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().PaginaSip::getInstance()->montarAncora($_GET['id_sistema'])).'\';" class="infraButton" />';
      $objSistemaDTO->setBolExclusaoLogica(false);
      $objSistemaDTO->setNumIdSistema($_GET['id_sistema']);
      $objSistemaDTO->retTodos();
      $objSistemaRN = new SistemaRN();
      $objSistemaDTO = $objSistemaRN->consultar($objSistemaDTO);
      if ($objSistemaDTO==null){
        throw new InfraException("Registro não encontrado.");
      }
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $strItensSelOrgao = OrgaoINT::montarSelectSiglaTodos('null','&nbsp;',$objSistemaDTO->getNumIdOrgao());
  $strItensSelHierarquia = HierarquiaINT::montarSelectNome('null','&nbsp;',$objSistemaDTO->getNumIdHierarquia());
  $strLinkUpload = SessaoSip::getInstance()->assinarLink('controlador.php?acao=sistema_upload&acao_origem='.$_GET['acao']);

  $strDisplayRemover = '';
  if ($_GET['acao']=='sistema_consultar' || InfraString::isBolVazia($objSistemaDTO->getStrLogo())){
    $strDisplayRemover = 'display:none;';
  }
  
  $strDisplayLogo = '';
  if (InfraString::isBolVazia($objSistemaDTO->getStrLogo())){
    $strDisplayLogo = 'display:none;';
  }
  
  
}catch(Exception $e){
  PaginaSip::getInstance()->processarExcecao($e);
}

PaginaSip::getInstance()->montarDocType();
PaginaSip::getInstance()->abrirHtml();
PaginaSip::getInstance()->abrirHead();
PaginaSip::getInstance()->montarMeta();
PaginaSip::getInstance()->montarTitle(PaginaSip::getInstance()->getStrNomeSistema().' - Sistema');
PaginaSip::getInstance()->montarStyle();
PaginaSip::getInstance()->abrirStyle();
?>
#lblOrgao {position:absolute;left:0%;top:0%;width:20%;}
#selOrgao {position:absolute;left:0%;top:6%;width:20%;}

#lblHierarquia {position:absolute;left:0%;top:16%;width:40%;}
#selHierarquia {position:absolute;left:0%;top:22%;width:40%;}

#lblSigla {position:absolute;left:0%;top:32%;width:15%;}
#txtSigla {position:absolute;left:0%;top:38%;width:15%;}

#lblDescricao {position:absolute;left:0%;top:48%;width:80%;}
#txtDescricao {position:absolute;left:0%;top:54%;width:80%;}

#lblPaginaInicial {position:absolute;left:0%;top:64%;width:95%;}
#txtPaginaInicial {position:absolute;left:0%;top:70%;width:95%;font-family: Courier, Courier New, monospace;}

#lblWebService {position:absolute;left:0%;top:80%;width:95%;}
#txtWebService {position:absolute;left:0%;top:86%;width:95%;font-family: Courier, Courier New, monospace;}

#lblArquivo {position:absolute;left:0%;top:0%;}
#filArquivo {position:absolute;left:0%;top:40%;}
#imgRemover {width:1.6em; height:1.6em}

<?
PaginaSip::getInstance()->fecharStyle();
PaginaSip::getInstance()->montarJavaScript();
PaginaSip::getInstance()->abrirJavaScript();
?>

var objUpload = null;

function inicializar(){
  if ('<?=$_GET['acao']?>'=='sistema_cadastrar'){
    document.getElementById('selOrgao').focus();
  } else if ('<?=$_GET['acao']?>'=='sistema_consultar'){
    infraDesabilitarCamposAreaDados();
  }
  
  if ('<?=$_GET['acao']?>'!='sistema_consultar'){
    objUpload = new infraUpload('frmUpload','<?=$strLinkUpload?>');
    objUpload.validar = function() {
      nomeArquivo=document.getElementById('filArquivo').value;
      if (nomeArquivo.substr(nomeArquivo.length-4,4)!='.png') {
        alert ("Imagem do logo deve ser no formato PNG.");
        return false;
      } else return true;
    }
    objUpload.finalizou = function(arr){
      removerLogo();
      if (arr!=null){
        document.getElementById('hdnNomeArquivo').value = arr['nome_upload'];
      }
    }
  }
  
}

function removerLogo(){
  document.getElementById('hdnNomeArquivo').value="*REMOVER*";
  document.getElementById('imgLogo').style.display='none';
  document.getElementById('imgRemover').style.display='none';
}

function OnSubmitForm() {
  return validarForm();
}

function validarForm() {
  if (!infraSelectSelecionado(document.getElementById('selOrgao'))) {
    alert('Selecione um Órgão.');
    document.getElementById('selOrgao').focus();
    return false;
  }

  if (!infraSelectSelecionado(document.getElementById('selHierarquia'))) {
    alert('Selecione uma Hierarquia.');
    document.getElementById('selHierarquia').focus();
    return false;
  }

  if (infraTrim(document.getElementById('txtSigla').value)=='') {
    alert('Informe Sigla.');
    document.getElementById('txtSigla').focus();
    return false;
  }

  return true;
}
<?
PaginaSip::getInstance()->fecharJavaScript();
PaginaSip::getInstance()->fecharHead();
PaginaSip::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmSistemaCadastro" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSip::getInstance()->assinarLink(basename(__FILE__).'?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
<?
//PaginaSip::getInstance()->montarBarraLocalizacao($strTitulo);
PaginaSip::getInstance()->montarBarraComandosSuperior($arrComandos);
//PaginaSip::getInstance()->montarAreaValidacao();
PaginaSip::getInstance()->abrirAreaDados('30em');
?>
  <label id="lblOrgao" for="selOrgao" accesskey="o" class="infraLabelObrigatorio">Órgã<span class="infraTeclaAtalho">o</span>:</label>
  <select id="selOrgao" name="selOrgao" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>">
  <?=$strItensSelOrgao?>
  </select>

  <label id="lblHierarquia" for="selHierarquia" accesskey="H" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">H</span>ierarquia:</label>
  <select id="selHierarquia" name="selHierarquia" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>">
  <?=$strItensSelHierarquia?>
  </select>

  <label id="lblSigla" for="txtSigla" accesskey="S" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">S</span>igla:</label>
  <input type="text" id="txtSigla" name="txtSigla" class="infraText" value="<?=PaginaSip::tratarHTML($objSistemaDTO->getStrSigla());?>" maxlength="15" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />

  <label id="lblDescricao" for="txtDescricao" accesskey="D" class="infraLabelOpcional"><span class="infraTeclaAtalho">D</span>escrição:</label>
  <input type="text" id="txtDescricao" name="txtDescricao" class="infraText" value="<?=PaginaSip::tratarHTML($objSistemaDTO->getStrDescricao());?>" maxlength="200" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />

  <label id="lblPaginaInicial" for="txtPaginaInicial" accesskey="P" class="infraLabelOpcional"><span class="infraTeclaAtalho">P</span>ágina Inicial:</label>
  <input type="text" id="txtPaginaInicial" name="txtPaginaInicial" class="infraText" value="<?=PaginaSip::tratarHTML($objSistemaDTO->getStrPaginaInicial());?>" maxlength="255" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />

  <label id="lblWebService" for="txtWebService" accesskey="W" class="infraLabelOpcional"><span class="infraTeclaAtalho">W</span>eb Service:</label>
  <input type="text" id="txtWebService" name="txtWebService" class="infraText" value="<?=PaginaSip::tratarHTML($objSistemaDTO->getStrWebService());?>" maxlength="255" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />
  
  <input type="hidden" id="hdnIdSistema" name="hdnIdSistema" value="<?=$objSistemaDTO->getNumIdSistema();?>" />
  <input type="hidden" id="hdnLogo" name="hdnLogo" value="<?=PaginaSip::tratarHTML($objSistemaDTO->getStrLogo());?>" />
  <input type="hidden" id="hdnNomeArquivo" name="hdnNomeArquivo" value="" />
<?
PaginaSip::getInstance()->fecharAreaDados();
//PaginaSip::getInstance()->montarAreaDebug();
//PaginaSip::getInstance()->montarBarraComandosInferior($arrComandos);
?>
</form>

<form id="frmUpload">
  <div id="divUpload" class="infraAreaDados" style="height:5em">
    <label id="lblArquivo" for="filArquivo" accesskey="" class="infraLabelOpcional">Logo:</label>
<? if ($_GET['acao']=='sistema_cadastrar' || $_GET['acao']=='sistema_alterar') { ?>    
    <input type="file" id="filArquivo" accept="image/png" name="filArquivo" size="50" onchange="objUpload.executar();" /><br />
<? }
?>
    </div>
  <img id="imgLogo" style="border:1px dotted #c0c0c0;float:left;<?=$strDisplayLogo?>" src="data:image/png;base64,<?=PaginaSip::tratarHTML($objSistemaDTO->getStrLogo());?>" />
  &nbsp;&nbsp;
  <img id="imgRemover" src="<?=PaginaSip::getInstance()->getDiretorioImagensGlobal()?>/remover.gif" alt="Remover Logo" title="Remover Logo" style="<?=$strDisplayRemover?>" class="infraImg" onclick="removerLogo();" />  
    
</form>
<?
PaginaSip::getInstance()->fecharBody();
PaginaSip::getInstance()->fecharHtml();
?>