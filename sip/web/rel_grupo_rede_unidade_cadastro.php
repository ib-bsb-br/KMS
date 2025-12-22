<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 20/08/2009 - criado por mga
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

  PaginaSip::getInstance()->verificarSelecao('rel_grupo_rede_unidade_selecionar');

  SessaoSip::getInstance()->validarPermissao($_GET['acao']);

  //PaginaSip::getInstance()->salvarCamposPost(array('selOrgao', 'selGrupoRede', 'selUnidade'));
  PaginaSip::getInstance()->salvarCamposPost(array('selOrgao'));

  $objRelGrupoRedeUnidadeDTO = new RelGrupoRedeUnidadeDTO();

  $strDesabilitar = '';

  $arrComandos = array();

  switch($_GET['acao']){
    case 'rel_grupo_rede_unidade_cadastrar':
      $strTitulo = 'Novo Mapeamento de Grupo de Rede';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarRelGrupoRedeUnidade" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      $numIdOrgao = PaginaSip::getInstance()->recuperarCampo('selOrgao');
      if ($numIdOrgao!==''){
        $objRelGrupoRedeUnidadeDTO->setNumIdOrgaoGrupoRede($numIdOrgao);
        $objRelGrupoRedeUnidadeDTO->setNumIdOrgaoUnidade($numIdOrgao);
      }else{
        $objRelGrupoRedeUnidadeDTO->setNumIdOrgaoGrupoRede(null);
        $objRelGrupoRedeUnidadeDTO->setNumIdOrgaoUnidade(null);
      }
      
      //$numIdGrupoRede = PaginaSip::getInstance()->recuperarCampo('selGrupoRede');
      $numIdGrupoRede = $_POST['selGrupoRede'];
      if ($numIdGrupoRede!==''){
        $objRelGrupoRedeUnidadeDTO->setNumIdGrupoRede($numIdGrupoRede);
      }else{
        $objRelGrupoRedeUnidadeDTO->setNumIdGrupoRede(null);
      }

      //$numIdUnidade = PaginaSip::getInstance()->recuperarCampo('selUnidade');
      $numIdUnidade = $_POST['selUnidade'];
      if ($numIdUnidade!==''){
        $objRelGrupoRedeUnidadeDTO->setNumIdUnidade($numIdUnidade);
      }else{
        $objRelGrupoRedeUnidadeDTO->setNumIdUnidade(null);
      }


      if (isset($_POST['sbmCadastrarRelGrupoRedeUnidade'])) {
        try{
          $objRelGrupoRedeUnidadeRN = new RelGrupoRedeUnidadeRN();
          $objRelGrupoRedeUnidadeDTO = $objRelGrupoRedeUnidadeRN->cadastrar($objRelGrupoRedeUnidadeDTO);
          PaginaSip::getInstance()->setStrMensagem('Mapeamento de Grupo de Rede cadastrado com sucesso.');
          header('Location: '.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'&id_grupo_rede='.$objRelGrupoRedeUnidadeDTO->getNumIdGrupoRede().'&id_unidade='.$objRelGrupoRedeUnidadeDTO->getNumIdUnidade().PaginaSip::getInstance()->montarAncora($objRelGrupoRedeUnidadeDTO->getNumIdGrupoRede().'-'.$objRelGrupoRedeUnidadeDTO->getNumIdUnidade())));
          die;
        }catch(Exception $e){
          PaginaSip::getInstance()->processarExcecao($e);
        }
      }
      break;

    /*
    case 'rel_grupo_rede_unidade_alterar':
      $strTitulo = 'Alterar Mapeamento de Grupo de Rede';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarRelGrupoRedeUnidade" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $strDesabilitar = 'disabled="disabled"';

      if (isset($_GET['id_grupo_rede']) && isset($_GET['id_unidade'])){
        $objRelGrupoRedeUnidadeDTO->setNumIdGrupoRede($_GET['id_grupo_rede']);
        $objRelGrupoRedeUnidadeDTO->setNumIdUnidade($_GET['id_unidade']);
        $objRelGrupoRedeUnidadeDTO->retTodos();
        $objRelGrupoRedeUnidadeRN = new RelGrupoRedeUnidadeRN();
        $objRelGrupoRedeUnidadeDTO = $objRelGrupoRedeUnidadeRN->consultar($objRelGrupoRedeUnidadeDTO);
        if ($objRelGrupoRedeUnidadeDTO==null){
          throw new InfraException("Registro não encontrado.");
        }
      } else {
        $objRelGrupoRedeUnidadeDTO->setNumIdGrupoRede($_POST['hdnIdGrupoRede']);
        $objRelGrupoRedeUnidadeDTO->setNumIdUnidade($_POST['hdnIdUnidade']);
      }

      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSip::getInstance()->montarAncora($objRelGrupoRedeUnidadeDTO->getNumIdGrupoRede().'-'.$objRelGrupoRedeUnidadeDTO->getNumIdUnidade()))).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      if (isset($_POST['sbmAlterarRelGrupoRedeUnidade'])) {
        try{
          $objRelGrupoRedeUnidadeRN = new RelGrupoRedeUnidadeRN();
          $objRelGrupoRedeUnidadeRN->alterar($objRelGrupoRedeUnidadeDTO);
          PaginaSip::getInstance()->setStrMensagem('Mapeamento de Grupo de Rede "'.$objRelGrupoRedeUnidadeDTO->getNumIdUnidade().'" alterado com sucesso.');
          header('Location: '.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSip::getInstance()->montarAncora($objRelGrupoRedeUnidadeDTO->getNumIdGrupoRede().'-'.$objRelGrupoRedeUnidadeDTO->getNumIdUnidade())));
          die;
        }catch(Exception $e){
          PaginaSip::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'rel_grupo_rede_unidade_consultar':
      $strTitulo = 'Consultar Mapeamento de Grupo de Rede';
      $arrComandos[] = '<button type="button" accesskey="F" name="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSip::getInstance()->montarAncora($_GET['id_grupo_rede'].'-'.$_GET['id_unidade']))).'\';" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
      $objRelGrupoRedeUnidadeDTO->setNumIdGrupoRede($_GET['id_grupo_rede']);
      $objRelGrupoRedeUnidadeDTO->setNumIdUnidade($_GET['id_unidade']);
      $objRelGrupoRedeUnidadeDTO->setBolExclusaoLogica(false);
      $objRelGrupoRedeUnidadeDTO->retTodos();
      $objRelGrupoRedeUnidadeRN = new RelGrupoRedeUnidadeRN();
      $objRelGrupoRedeUnidadeDTO = $objRelGrupoRedeUnidadeRN->consultar($objRelGrupoRedeUnidadeDTO);
      if ($objRelGrupoRedeUnidadeDTO===null){
        throw new InfraException("Registro não encontrado.");
      }
      break;
    */
    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }
  
  $strItensSelOrgao = OrgaoINT::montarSelectSiglaTodos('null','&nbsp;',$numIdOrgao);
  $strItensSelGrupoRede = GrupoRedeINT::montarSelectOuLdapNaoExcecao('null','&nbsp;',$objRelGrupoRedeUnidadeDTO->getNumIdGrupoRede(),$objRelGrupoRedeUnidadeDTO->getNumIdOrgaoGrupoRede());
  $strItensSelUnidade = UnidadeINT::montarSelectSigla('null','&nbsp;',$objRelGrupoRedeUnidadeDTO->getNumIdUnidade(),$objRelGrupoRedeUnidadeDTO->getNumIdOrgaoUnidade());

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

#lblGrupoRede {position:absolute;left:0%;top:16%;width:50%;}
#selGrupoRede {position:absolute;left:0%;top:22%;width:50%;}

#lblUnidade {position:absolute;left:0%;top:32%;width:25%;}
#selUnidade {position:absolute;left:0%;top:38%;width:25%;}

<?
PaginaSip::getInstance()->fecharStyle();
PaginaSip::getInstance()->montarJavaScript();
PaginaSip::getInstance()->abrirJavaScript();
?>
function inicializar(){
  if ('<?=$_GET['acao']?>'=='rel_grupo_rede_unidade_cadastrar'){
    document.getElementById('selOrgao').focus();
  } else if ('<?=$_GET['acao']?>'=='rel_grupo_rede_unidade_consultar'){
    infraDesabilitarCamposAreaDados();
  }else{
    document.getElementById('btnCancelar').focus();
  }
  infraEfeitoTabelas();
}

function validarCadastro() {

  if (!infraSelectSelecionado('selOrgao')) {
    alert('Selecione um Órgão.');
    document.getElementById('selOrgao').focus();
    return false;
  }

  if (!infraSelectSelecionado('selGrupoRede')) {
    alert('Selecione um Grupo de Rede.');
    document.getElementById('selGrupoRede').focus();
    return false;
  }

  if (!infraSelectSelecionado('selUnidade')) {
    alert('Selecione uma Unidade.');
    document.getElementById('selUnidade').focus();
    return false;
  }

  return true;
}

function OnSubmitForm() {
  return validarCadastro();
}

function trocarOrgao(obj){
	document.getElementById('selGrupoRede').value='null';
	document.getElementById('selUnidade').value='null';
	obj.form.submit();
}

<?
PaginaSip::getInstance()->fecharJavaScript();
PaginaSip::getInstance()->fecharHead();
PaginaSip::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmRelGrupoRedeUnidadeCadastro" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSip::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
<?
PaginaSip::getInstance()->montarBarraComandosSuperior($arrComandos);
//PaginaSip::getInstance()->montarAreaValidacao();
PaginaSip::getInstance()->abrirAreaDados('30em');
?>
  <label id="lblOrgao" for="selOrgao" accesskey="" class="infraLabelObrigatorio">Órgão:</label>
  <select id="selOrgao" name="selOrgao" onchange="trocarOrgao(this);" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" <?=$strDesabilitar?>>
  <?=$strItensSelOrgao?>
  </select>
  
  <label id="lblGrupoRede" for="selGrupoRede" accesskey="" class="infraLabelObrigatorio">Grupo de Rede:</label>
  <select id="selGrupoRede" name="selGrupoRede" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" <?=$strDesabilitar?>>
  <?=$strItensSelGrupoRede?>
  </select>
  
  <label id="lblUnidade" for="selUnidade" accesskey="" class="infraLabelObrigatorio">Unidade:</label>
  <select id="selUnidade" name="selUnidade" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" <?=$strDesabilitar?>>
  <?=$strItensSelUnidade?>
  </select>
  
  <input type="hidden" id="hdnIdGrupoRede" name="hdnIdGrupoRede" value="<?=$objRelGrupoRedeUnidadeDTO->getNumIdGrupoRede();?>" />
  <input type="hidden" id="hdnIdUnidade" name="hdnIdUnidade" value="<?=$objRelGrupoRedeUnidadeDTO->getNumIdUnidade();?>" />
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