<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 19/12/2006 - criado por mga
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

  PaginaSip::getInstance()->salvarCamposPost(array('selOrgao'));
	
  $objContextoDTO = new ContextoDTO();

  $arrComandos = array();

  switch($_GET['acao']){
    case 'contexto_cadastrar':
      $strTitulo = 'Novo Contexto';
      $arrComandos[] = '<input type="submit" name="sbmCadastrarContexto" value="Salvar" class="infraButton" />';
      $arrComandos[] = '<input type="button" name="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao=contexto_listar').'\';" class="infraButton" />';
			
			$objContextoDTO->setNumIdContexto(null);
			
			$numIdOrgao = PaginaSip::getInstance()->recuperarCampo('selOrgao');
			if ($numIdOrgao!==''){
				$objContextoDTO->setNumIdOrgao($numIdOrgao);
			}else {
				$objContextoDTO->setNumIdOrgao(null);
			}
			
			$objContextoDTO->setStrNome($_POST['txtNome']);
			$objContextoDTO->setStrDescricao($_POST['txtDescricao']);
			$objContextoDTO->setStrBaseDnLdap($_POST['txtBaseDnLdap']);
			$objContextoDTO->setStrSinAtivo("S");

      if (isset($_POST['sbmCadastrarContexto'])) {
				try{
					$objContextoRN = new ContextoRN();
					$objContextoDTO = $objContextoRN->cadastrar($objContextoDTO);
					header('Location: '.SessaoSip::getInstance()->assinarLink('controlador.php?acao=contexto_listar'));
					die;
				}catch(Exception $e){
					PaginaSip::getInstance()->processarExcecao($e);
				}
      }
      break;

    case 'contexto_alterar':
      $strTitulo = 'Alterar Contexto';
      $arrComandos[] = '<input type="submit" name="sbmAlterarContexto" value="Salvar" class="infraButton" />';

			if (isset($_GET['id_contexto'])){
			  $objContextoDTO->setBolExclusaoLogica(false);
        $objContextoDTO->setNumIdContexto($_GET['id_contexto']);
        $objContextoDTO->retTodos();
        $objContextoRN = new ContextoRN();
        $objContextoDTO = $objContextoRN->consultar($objContextoDTO);
        if ($objContextoDTO==null){
          throw new InfraException("Registro não encontrado.");
        }
			} else {
				$objContextoDTO->setNumIdContexto($_POST['hdnIdContexto']);
				$objContextoDTO->setNumIdOrgao($_POST['selOrgao']);
				$objContextoDTO->setStrNome($_POST['txtNome']);
				$objContextoDTO->setStrDescricao($_POST['txtDescricao']);
				$objContextoDTO->setStrBaseDnLdap($_POST['txtBaseDnLdap']);
			}

			$arrComandos[] = '<input type="button" name="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().PaginaSip::getInstance()->montarAncora($objContextoDTO->getNumIdContexto())).'\';" class="infraButton" />';
			
      if (isset($_POST['sbmAlterarContexto'])) {
				try{
					$objContextoRN = new ContextoRN();
					$objContextoRN->alterar($objContextoDTO);
					header('Location: '.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().'&msg=Contexto "'.$objContextoDTO->getStrNome().'" alterado com sucesso.'.PaginaSip::getInstance()->montarAncora($objContextoDTO->getNumIdContexto())));
					die;
				}catch(Exception $e){
					PaginaSip::getInstance()->processarExcecao($e);
				}
      }
      break;

    case 'contexto_consultar':
      $strTitulo = "Consultar Contexto";
      $arrComandos[] = '<input type="button" name="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().PaginaSip::getInstance()->montarAncora($_GET['id_contexto'])).'\';" class="infraButton" />';
      $objContextoDTO->setBolExclusaoLogica(false);
      $objContextoDTO->setNumIdContexto($_GET['id_contexto']);
      $objContextoDTO->retTodos();
      $objContextoRN = new ContextoRN();
      $objContextoDTO = $objContextoRN->consultar($objContextoDTO);
      if ($objContextoDTO==null){
        throw new InfraException("Registro não encontrado.");
      }
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $strItensSelOrgao = OrgaoINT::montarSelectSiglaTodos('null','&nbsp;',$objContextoDTO->getNumIdOrgao());

}catch(Exception $e){
  PaginaSip::getInstance()->processarExcecao($e);
}

PaginaSip::getInstance()->montarDocType();
PaginaSip::getInstance()->abrirHtml();
PaginaSip::getInstance()->abrirHead();
PaginaSip::getInstance()->montarMeta();
PaginaSip::getInstance()->montarTitle(PaginaSip::getInstance()->getStrNomeSistema().' - Contexto');
PaginaSip::getInstance()->montarStyle();
PaginaSip::getInstance()->abrirStyle();
?>
#lblOrgao {position:absolute;left:0%;top:0%;width:20%;}
#selOrgao {position:absolute;left:0%;top:6%;width:20%;}

#lblNome {position:absolute;left:0%;top:16%;width:80%;}
#txtNome {position:absolute;left:0%;top:22%;width:80%;}

#lblDescricao {position:absolute;left:0%;top:32%;width:80%;}
#txtDescricao {position:absolute;left:0%;top:38%;width:80%;}

#lblBaseDnLdap {position:absolute;left:0%;top:48%;width:50%;}
#txtBaseDnLdap {position:absolute;left:0%;top:54%;width:50%;}

<?
PaginaSip::getInstance()->fecharStyle();
PaginaSip::getInstance()->montarJavaScript();
PaginaSip::getInstance()->abrirJavaScript();
?>
function inicializar(){
  if ('<?=$_GET['acao']?>'=='contexto_cadastrar'){
    document.getElementById('selOrgao').focus();
  } else if ('<?=$_GET['acao']?>'=='contexto_consultar'){
    infraDesabilitarCamposAreaDados();
  }
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

  if (infraTrim(document.getElementById('txtNome').value)=='') {
    alert('Informe Nome.');
    document.getElementById('txtNome').focus();
    return false;
  }

  /*
  if (infraTrim(document.getElementById('txtDescricao').value)=='') {
    alert('Informe a Descrição.');
    document.getElementById('txtDescricao').focus();
    return false;
  }
  */

  if (infraTrim(document.getElementById('txtBaseDnLdap').value)=='') {
    alert('Informe Base DN LDAP.');
    document.getElementById('txtBaseDnLdap').focus();
    return false;
  }

  return true;
}
<?
PaginaSip::getInstance()->fecharJavaScript();
PaginaSip::getInstance()->fecharHead();
PaginaSip::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmContextoCadastro" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSip::getInstance()->assinarLink(basename(__FILE__).'?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
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

  <label id="lblNome" for="txtNome" accesskey="N" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">N</span>ome:</label>
  <input type="text" id="txtNome" name="txtNome" class="infraText" value="<?=PaginaSip::tratarHTML($objContextoDTO->getStrNome());?>" maxlength="50" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />
  
  <label id="lblDescricao" for="txtDescricao" accesskey="D" class="infraLabelOpcional"><span class="infraTeclaAtalho">D</span>escrição:</label>
  <input type="text" id="txtDescricao" name="txtDescricao" class="infraText" value="<?=PaginaSip::tratarHTML($objContextoDTO->getStrDescricao());?>" maxlength="200" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />

  <label id="lblBaseDnLdap" for="txtBaseDnLdap" accesskey="B" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">B</span>ase DN LDAP:</label>
  <input type="text" id="txtBaseDnLdap" name="txtBaseDnLdap" class="infraText" value="<?=PaginaSip::tratarHTML($objContextoDTO->getStrBaseDnLdap());?>" maxlength="50" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />

  <input type="hidden" id="hdnIdContexto" name="hdnIdContexto" value="<?=$objContextoDTO->getNumIdContexto();?>" />
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