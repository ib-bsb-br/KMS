<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 19/08/2009 - criado por mga
*
* Versão do Gerador de Código: 1.28.0
*
* Versão no CVS: $Id$
*/

try {
  require_once dirname(__FILE__).'/Sip.php';

  session_start();

  //////////////////////////////////////////////////////////////////////////////
  //InfraDebug::getInstance()->setBolLigado(false);
  //InfraDebug::getInstance()->setBolDebugInfra(true);
  //InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSip::getInstance()->validarLink();

  PaginaSip::getInstance()->verificarSelecao('grupo_rede_selecionar');

  SessaoSip::getInstance()->validarPermissao($_GET['acao']);

  PaginaSip::getInstance()->salvarCamposPost(array('selOrgao'));

  $objGrupoRedeDTO = new GrupoRedeDTO();

  $strDesabilitar = '';

  $arrComandos = array();

  switch($_GET['acao']){
    case 'grupo_rede_cadastrar':
      $strTitulo = 'Novo Grupo de Rede';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarGrupoRede" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      $objGrupoRedeDTO->setNumIdGrupoRede(null);
      
			//ORGAO UNIDADE
			$numIdOrgao = PaginaSip::getInstance()->recuperarCampo('selOrgao');
			if ($numIdOrgao!==''){
				$objGrupoRedeDTO->setNumIdOrgao($numIdOrgao);
			}else{
				$objGrupoRedeDTO->setNumIdOrgao(null);
			}
      
      $objGrupoRedeDTO->setStrOuLdap($_POST['txtOuLdap']);
      $objGrupoRedeDTO->setStrDescricao($_POST['txaDescricao']);
      $objGrupoRedeDTO->setStrSinExcecao(PaginaSip::getInstance()->getCheckbox($_POST['chkExcecao']));

      if (isset($_POST['sbmCadastrarGrupoRede'])) {
        try{
          $objGrupoRedeRN = new GrupoRedeRN();
          $objGrupoRedeDTO = $objGrupoRedeRN->cadastrar($objGrupoRedeDTO);
          PaginaSip::getInstance()->setStrMensagem('Grupo de Rede "'.$objGrupoRedeDTO->getStrOuLdap().'" cadastrado com sucesso.');
          header('Location: '.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'&id_grupo_rede='.$objGrupoRedeDTO->getNumIdGrupoRede().PaginaSip::getInstance()->montarAncora($objGrupoRedeDTO->getNumIdGrupoRede())));
          die;
        }catch(Exception $e){
          PaginaSip::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'grupo_rede_alterar':
      $strTitulo = 'Alterar Grupo de Rede';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarGrupoRede" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $strDesabilitar = 'disabled="disabled"';

      if (isset($_GET['id_grupo_rede'])){
        $objGrupoRedeDTO->setNumIdGrupoRede($_GET['id_grupo_rede']);
        $objGrupoRedeDTO->retTodos(true);
        $objGrupoRedeRN = new GrupoRedeRN();
        $objGrupoRedeDTO = $objGrupoRedeRN->consultar($objGrupoRedeDTO);
        if ($objGrupoRedeDTO==null){
          throw new InfraException("Registro não encontrado.");
        }
      } else {
        $objGrupoRedeDTO->setNumIdGrupoRede($_POST['hdnIdGrupoRede']);
        $objGrupoRedeDTO->setNumIdOrgao($_POST['hdnIdOrgao']);
        $objGrupoRedeDTO->setStrOuLdap($_POST['txtOuLdap']);
        $objGrupoRedeDTO->setStrDescricao($_POST['txaDescricao']);
        $objGrupoRedeDTO->setStrSinExcecao(PaginaSip::getInstance()->getCheckbox($_POST['chkExcecao']));
      }

      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSip::getInstance()->montarAncora($objGrupoRedeDTO->getNumIdGrupoRede())).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      if (isset($_POST['sbmAlterarGrupoRede'])) {
        try{
          $objGrupoRedeRN = new GrupoRedeRN();
          $objGrupoRedeRN->alterar($objGrupoRedeDTO);
          PaginaSip::getInstance()->setStrMensagem('Grupo de Rede "'.$objGrupoRedeDTO->getStrOuLdap().'" alterado com sucesso.');
          header('Location: '.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSip::getInstance()->montarAncora($objGrupoRedeDTO->getNumIdGrupoRede())));
          die;
        }catch(Exception $e){
          PaginaSip::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'grupo_rede_consultar':
      $strTitulo = 'Consultar Grupo de Rede';
      $arrComandos[] = '<button type="button" accesskey="F" name="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSip::getInstance()->montarAncora($_GET['id_grupo_rede'])).'\';" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
      $objGrupoRedeDTO->setNumIdGrupoRede($_GET['id_grupo_rede']);
      $objGrupoRedeDTO->setBolExclusaoLogica(false);
      $objGrupoRedeDTO->retTodos(true);
      $objGrupoRedeRN = new GrupoRedeRN();
      $objGrupoRedeDTO = $objGrupoRedeRN->consultar($objGrupoRedeDTO);
      if ($objGrupoRedeDTO===null){
        throw new InfraException("Registro não encontrado.");
      }
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }
  $strItensSelOrgao = OrgaoINT::montarSelectSiglaTodos('null','&nbsp;',$objGrupoRedeDTO->getNumIdOrgao());	

}catch(Exception $e){
  PaginaSip::getInstance()->processarExcecao($e);
}

PaginaSip::getInstance()->montarDocType();
PaginaSip::getInstance()->abrirHtml();
PaginaSip::getInstance()->abrirHead();
PaginaSip::getInstance()->montarMeta();
PaginaSip::getInstance()->montarTitle(PaginaSip::getInstance()->getStrNomeSistema().' - '.$strTitulo);
PaginaSip::getInstance()->montarStyle();
PaginaSip::getInstance()->abrirStyle();
?>
#lblOrgao {position:absolute;left:0%;top:0%;width:25%;}
#selOrgao {position:absolute;left:0%;top:6%;width:25%;}

#lblOuLdap {position:absolute;left:0%;top:16%;width:50%;}
#txtOuLdap {position:absolute;left:0%;top:22%;width:50%;}

#lblDescricao {position:absolute;left:0%;top:32%;width:95%;}
#txaDescricao {position:absolute;left:0%;top:38%;width:77%;}

#divSinExcecao {position:absolute;left:0%;top:65%;}

<?
PaginaSip::getInstance()->fecharStyle();
PaginaSip::getInstance()->montarJavaScript();
PaginaSip::getInstance()->abrirJavaScript();
?>
function inicializar(){
  if ('<?=$_GET['acao']?>'=='grupo_rede_cadastrar'){
    document.getElementById('selOrgao').focus();
  } else if ('<?=$_GET['acao']?>'=='grupo_rede_consultar'){
    infraDesabilitarCamposAreaDados();
  }else{
    document.getElementById('btnCancelar').focus();
  }
  infraEfeitoTabelas();
}

function validarCadastro() {

  if (!infraSelectSelecionado(document.getElementById('selOrgao'))) {
    alert('Selecione Órgão do Grupo de Rede.');
    document.getElementById('selOrgao').focus();
    return false;
  }

  if (infraTrim(document.getElementById('txtOuLdap').value)=='') {
    alert('Informe a Unidade Organizacional LDAP.');
    document.getElementById('txtOuLdap').focus();
    return false;
  }
  

  return true;
}

function OnSubmitForm() {
  return validarCadastro();
}


<?
PaginaSip::getInstance()->fecharJavaScript();
PaginaSip::getInstance()->fecharHead();
PaginaSip::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmGrupoRedeCadastro" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSip::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
<?
PaginaSip::getInstance()->montarBarraComandosSuperior($arrComandos);
//PaginaSip::getInstance()->montarAreaValidacao();
PaginaSip::getInstance()->abrirAreaDados('30em');
?>
  <label id="lblOrgao" for="selOrgao" accesskey="o" class="infraLabelObrigatorio">Ór<span class="infraTeclaAtalho">g</span>ão:</label>
  <select id="selOrgao" name="selOrgao" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" <?=$strDesabilitar?> >
  <?=$strItensSelOrgao?>
  </select>

  <label id="lblOuLdap" for="txtOuLdap" accesskey="" class="infraLabelOpcional">Unidade Organizacional LDAP:</label>
  <input type="text" id="txtOuLdap" name="txtOuLdap" class="infraText" value="<?=PaginaSip::tratarHTML($objGrupoRedeDTO->getStrOuLdap());?>" onkeypress="return infraMascaraTexto(this,event,256);" maxlength="256" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />

  <label id="lblDescricao" for="txaDescricao" accesskey="" class="infraLabelOpcional">Descrição:</label>
  <textarea id="txaDescricao" name="txaDescricao" rows="3" class="infraTextArea" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>"><?=PaginaSip::tratarHTML($objGrupoRedeDTO->getStrDescricao());?></textarea>
 
  <div id="divSinExcecao" class="infraDivCheckbox">
    <input type="checkbox" id="chkExcecao" name="chkExcecao" <?=PaginaSip::getInstance()->setCheckbox($objGrupoRedeDTO->getStrSinExcecao())?> class="infraCheckbox" />
  	<label id="lblExcecao" accesskey="" for="chkExcecao" class="infraLabelCheckbox">Exceção</label>			
  </div>

  <input type="hidden" id="hdnIdOrgao" name="hdnIdOrgao" value="<?=$objGrupoRedeDTO->getNumIdOrgao();?>" />
  <input type="hidden" id="hdnIdGrupoRede" name="hdnIdGrupoRede" value="<?=$objGrupoRedeDTO->getNumIdGrupoRede();?>" />
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