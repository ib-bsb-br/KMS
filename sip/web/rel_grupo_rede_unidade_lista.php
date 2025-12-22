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

  PaginaSip::getInstance()->prepararSelecao('rel_grupo_rede_unidade_selecionar');

  SessaoSip::getInstance()->validarPermissao($_GET['acao']);

  PaginaSip::getInstance()->salvarCamposPost(array('selOrgaoGrupoRede'));

  switch($_GET['acao']){
    case 'rel_grupo_rede_unidade_excluir':
      try{
        $arrStrIds = PaginaSip::getInstance()->getArrStrItensSelecionados();
        $arrObjRelGrupoRedeUnidadeDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $arrStrIdComposto = explode('-',$arrStrIds[$i]);
          $objRelGrupoRedeUnidadeDTO = new RelGrupoRedeUnidadeDTO();
          $objRelGrupoRedeUnidadeDTO->setNumIdGrupoRede($arrStrIdComposto[0]);
          $objRelGrupoRedeUnidadeDTO->setNumIdUnidade($arrStrIdComposto[1]);
          $arrObjRelGrupoRedeUnidadeDTO[] = $objRelGrupoRedeUnidadeDTO;
        }
        $objRelGrupoRedeUnidadeRN = new RelGrupoRedeUnidadeRN();
        $objRelGrupoRedeUnidadeRN->excluir($arrObjRelGrupoRedeUnidadeDTO);
        PaginaSip::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSip::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

/* 
    case 'rel_grupo_rede_unidade_desativar':
      try{
        $arrStrIds = PaginaSip::getInstance()->getArrStrItensSelecionados();
        $arrObjRelGrupoRedeUnidadeDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $arrStrIdComposto = explode('-',$arrStrIds[$i]);
          $objRelGrupoRedeUnidadeDTO = new RelGrupoRedeUnidadeDTO();
          $objRelGrupoRedeUnidadeDTO->setNumIdGrupoRede($arrStrIdComposto[0]);
          $objRelGrupoRedeUnidadeDTO->setNumIdUnidade($arrStrIdComposto[1]);
          $arrObjRelGrupoRedeUnidadeDTO[] = $objRelGrupoRedeUnidadeDTO;
        }
        $objRelGrupoRedeUnidadeRN = new RelGrupoRedeUnidadeRN();
        $objRelGrupoRedeUnidadeRN->desativar($arrObjRelGrupoRedeUnidadeDTO);
        PaginaSip::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSip::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'rel_grupo_rede_unidade_reativar':
      $strTitulo = 'Reativar Mapeamentos de Grupos de Rede';
      if ($_GET['acao_confirmada']=='sim'){
        try{
          $arrStrIds = PaginaSip::getInstance()->getArrStrItensSelecionados();
          $arrObjRelGrupoRedeUnidadeDTO = array();
          for ($i=0;$i<count($arrStrIds);$i++){
            $arrStrIdComposto = explode('-',$arrStrIds[$i]);
            $objRelGrupoRedeUnidadeDTO = new RelGrupoRedeUnidadeDTO();
            $objRelGrupoRedeUnidadeDTO->setNumIdGrupoRede($arrStrIdComposto[0]);
            $objRelGrupoRedeUnidadeDTO->setNumIdUnidade($arrStrIdComposto[1]);
            $arrObjRelGrupoRedeUnidadeDTO[] = $objRelGrupoRedeUnidadeDTO;
          }
          $objRelGrupoRedeUnidadeRN = new RelGrupoRedeUnidadeRN();
          $objRelGrupoRedeUnidadeRN->reativar($arrObjRelGrupoRedeUnidadeDTO);
          PaginaSip::getInstance()->setStrMensagem('Operação realizada com sucesso.');
        }catch(Exception $e){
          PaginaSip::getInstance()->processarExcecao($e);
        } 
        header('Location: '.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
        die;
      } 
      break;

 */
    case 'rel_grupo_rede_unidade_selecionar':
      $strTitulo = PaginaSip::getInstance()->getTituloSelecao('Selecionar Mapeamento de Grupo de Rede','Selecionar Mapeamentos de Grupos de Rede');

      //Se cadastrou alguem
      if ($_GET['acao_origem']=='rel_grupo_rede_unidade_cadastrar'){
        if (isset($_GET['id_grupo_rede']) && isset($_GET['id_unidade'])){
          PaginaSip::getInstance()->adicionarSelecionado($_GET['id_grupo_rede'].'-'.$_GET['id_unidade']);
        }
      }
      break;

    case 'rel_grupo_rede_unidade_listar':
      $strTitulo = 'Mapeamentos de Grupos de Rede';
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $arrComandos = array();
  if ($_GET['acao'] == 'rel_grupo_rede_unidade_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
  }

  /* if ($_GET['acao'] == 'rel_grupo_rede_unidade_listar' || $_GET['acao'] == 'rel_grupo_rede_unidade_selecionar'){ */
    $bolAcaoCadastrar = SessaoSip::getInstance()->verificarPermissao('rel_grupo_rede_unidade_cadastrar');
    if ($bolAcaoCadastrar){
      $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao=rel_grupo_rede_unidade_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
    }
  /* } */

  $objRelGrupoRedeUnidadeDTO = new RelGrupoRedeUnidadeDTO(true);
  $objRelGrupoRedeUnidadeDTO->retStrSiglaOrgaoGrupoRede();
  $objRelGrupoRedeUnidadeDTO->retNumIdGrupoRede();
  $objRelGrupoRedeUnidadeDTO->retStrOuLdapGrupoRede();
  $objRelGrupoRedeUnidadeDTO->retNumIdUnidade();
  $objRelGrupoRedeUnidadeDTO->retStrSiglaUnidade();
  $objRelGrupoRedeUnidadeDTO->retStrDescricaoUnidade();
  
	//ORGAO
	$numIdOrgaoGrupoRede = PaginaSip::getInstance()->recuperarCampo('selOrgaoGrupoRede');
	//if ($numIdOrgaoGrupoRede!==''){
	  $objRelGrupoRedeUnidadeDTO->setNumIdOrgaoGrupoRede($numIdOrgaoGrupoRede);
	//} 

/* 
  if ($_GET['acao'] == 'rel_grupo_rede_unidade_reativar'){
    //Lista somente inativos
    $objRelGrupoRedeUnidadeDTO->setBolExclusaoLogica(false);
    $objRelGrupoRedeUnidadeDTO->setStrSinAtivo('N');
  }
 */
  PaginaSip::getInstance()->prepararOrdenacao($objRelGrupoRedeUnidadeDTO, 'OuLdapGrupoRede', InfraDTO::$TIPO_ORDENACAO_ASC);
  //PaginaSip::getInstance()->prepararPaginacao($objRelGrupoRedeUnidadeDTO);

  $objRelGrupoRedeUnidadeRN = new RelGrupoRedeUnidadeRN();
  $arrObjRelGrupoRedeUnidadeDTO = $objRelGrupoRedeUnidadeRN->listar($objRelGrupoRedeUnidadeDTO);

  //PaginaSip::getInstance()->processarPaginacao($objRelGrupoRedeUnidadeDTO);
  $numRegistros = count($arrObjRelGrupoRedeUnidadeDTO);

  if ($numRegistros > 0){

    $bolCheck = false;

    if ($_GET['acao']=='rel_grupo_rede_unidade_selecionar'){
      $bolAcaoReativar = false;
      $bolAcaoConsultar = false;
      $bolAcaoAlterar = false;
      $bolAcaoImprimir = false;
      $bolAcaoExcluir = false;
      $bolAcaoDesativar = false;
      $bolCheck = true;
/*     }else if ($_GET['acao']=='rel_grupo_rede_unidade_reativar'){
      $bolAcaoReativar = SessaoSip::getInstance()->verificarPermissao('rel_grupo_rede_unidade_reativar');
      $bolAcaoConsultar = SessaoSip::getInstance()->verificarPermissao('rel_grupo_rede_unidade_consultar');
      $bolAcaoAlterar = false;
      $bolAcaoImprimir = true;
      $bolAcaoExcluir = SessaoSip::getInstance()->verificarPermissao('rel_grupo_rede_unidade_excluir');
      $bolAcaoDesativar = false;
 */    }else{
      $bolAcaoReativar = false;
      $bolAcaoConsultar = false;
      $bolAcaoAlterar = false;
      $bolAcaoImprimir = true;
      $bolAcaoExcluir = SessaoSip::getInstance()->verificarPermissao('rel_grupo_rede_unidade_excluir');
      $bolAcaoDesativar = false;
    }

    /* 
    if ($bolAcaoDesativar){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="t" id="btnDesativar" value="Desativar" onclick="acaoDesativacaoMultipla();" class="infraButton">Desa<span class="infraTeclaAtalho">t</span>ivar</button>';
      $strLinkDesativar = SessaoSip::getInstance()->assinarLink('controlador.php?acao=rel_grupo_rede_unidade_desativar&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoReativar){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="R" id="btnReativar" value="Reativar" onclick="acaoReativacaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">R</span>eativar</button>';
      $strLinkReativar = SessaoSip::getInstance()->assinarLink('controlador.php?acao=rel_grupo_rede_unidade_reativar&acao_origem='.$_GET['acao'].'&acao_confirmada=sim');
    }
     */

    if ($bolAcaoExcluir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
      $strLinkExcluir = SessaoSip::getInstance()->assinarLink('controlador.php?acao=rel_grupo_rede_unidade_excluir&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoImprimir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';

    }

    $strResultado = '';

    /* if ($_GET['acao']!='rel_grupo_rede_unidade_reativar'){ */
      $strSumarioTabela = 'Tabela de Mapeamentos de Grupos de Rede.';
      $strCaptionTabela = 'Mapeamentos de Grupos de Rede';
    /* }else{
      $strSumarioTabela = 'Tabela de Mapeamentos de Grupos de Rede Inativos.';
      $strCaptionTabela = 'Mapeamentos de Grupos de Rede Inativos';
    } */

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSip::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    if ($bolCheck) {
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaSip::getInstance()->getThCheck().'</th>'."\n";
    }
    
    //$strResultado .= '<th class="infraTh" width="10%">'.PaginaSip::getInstance()->getThOrdenacao($objRelGrupoRedeUnidadeDTO,'Órgão','SiglaOrgaoGrupoRede',$arrObjRelGrupoRedeUnidadeDTO).'</th>'."\n";
		$strResultado .= '<th class="infraTh">'.PaginaSip::getInstance()->getThOrdenacao($objRelGrupoRedeUnidadeDTO,'Grupo de Rede','OuLdapGrupoRede',$arrObjRelGrupoRedeUnidadeDTO).'</th>'."\n";
		$strResultado .= '<th class="infraTh" width="20%">'.PaginaSip::getInstance()->getThOrdenacao($objRelGrupoRedeUnidadeDTO,'Unidade','SiglaUnidade',$arrObjRelGrupoRedeUnidadeDTO).'</th>'."\n";
    
    $strResultado .= '<th class="infraTh" width="10%">Ações</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistros; $i++){

      $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      $strResultado .= $strCssTr;

      if ($bolCheck){
        $strResultado .= '<td valign="top">'.PaginaSip::getInstance()->getTrCheck($i,$arrObjRelGrupoRedeUnidadeDTO[$i]->getNumIdGrupoRede().'-'.$arrObjRelGrupoRedeUnidadeDTO[$i]->getNumIdUnidade(),$arrObjRelGrupoRedeUnidadeDTO[$i]->getNumIdUnidade()).'</td>';
      }
      //$strResultado .= '<td align="center">'.$arrObjRelGrupoRedeUnidadeDTO[$i]->getStrSiglaOrgaoGrupoRede().'</td>';
			$strResultado .= '<td align="left">'.PaginaSip::tratarHTML($arrObjRelGrupoRedeUnidadeDTO[$i]->getStrOuLdapGrupoRede()).'</td>';
			
			$strResultado .= '<td align="center">';
			$strResultado .= '<a alt="'.PaginaSip::tratarHTML($arrObjRelGrupoRedeUnidadeDTO[$i]->getStrDescricaoUnidade()).'" title="'.PaginaSip::tratarHTML($arrObjRelGrupoRedeUnidadeDTO[$i]->getStrDescricaoUnidade()).'" class="ancoraSigla">'.PaginaSip::tratarHTML($arrObjRelGrupoRedeUnidadeDTO[$i]->getStrSiglaUnidade()).'</a>';
			$strResultado .= '</td>';
      
      $strResultado .= '<td align="center">';

      $strResultado .= PaginaSip::getInstance()->getAcaoTransportarItem($i,$arrObjRelGrupoRedeUnidadeDTO[$i]->getNumIdGrupoRede().'-'.$arrObjRelGrupoRedeUnidadeDTO[$i]->getNumIdUnidade());

      if ($bolAcaoConsultar){
        $strResultado .= '<a href="'.SessaoSip::getInstance()->assinarLink('controlador.php?acao=rel_grupo_rede_unidade_consultar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_grupo_rede='.$arrObjRelGrupoRedeUnidadeDTO[$i]->getNumIdGrupoRede().'&id_unidade='.$arrObjRelGrupoRedeUnidadeDTO[$i]->getNumIdUnidade()).'" tabindex="'.PaginaSip::getInstance()->getProxTabTabela().'"><img src="'.PaginaSip::getInstance()->getDiretorioImagensGlobal().'/consultar.gif" title="Consultar Mapeamento de Grupo de Rede" alt="Consultar Mapeamento de Grupo de Rede" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoAlterar){
        $strResultado .= '<a href="'.SessaoSip::getInstance()->assinarLink('controlador.php?acao=rel_grupo_rede_unidade_alterar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_grupo_rede='.$arrObjRelGrupoRedeUnidadeDTO[$i]->getNumIdGrupoRede().'&id_unidade='.$arrObjRelGrupoRedeUnidadeDTO[$i]->getNumIdUnidade()).'" tabindex="'.PaginaSip::getInstance()->getProxTabTabela().'"><img src="'.PaginaSip::getInstance()->getDiretorioImagensGlobal().'/alterar.gif" title="Alterar Mapeamento de Grupo de Rede" alt="Alterar Mapeamento de Grupo de Rede" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoDesativar || $bolAcaoReativar || $bolAcaoExcluir){
        $strId = $arrObjRelGrupoRedeUnidadeDTO[$i]->getNumIdGrupoRede().'-'.$arrObjRelGrupoRedeUnidadeDTO[$i]->getNumIdUnidade();
        $strDescricao = PaginaSip::getInstance()->formatarParametrosJavaScript($arrObjRelGrupoRedeUnidadeDTO[$i]->getStrOuLdapGrupoRede().' e '.$arrObjRelGrupoRedeUnidadeDTO[$i]->getStrSiglaUnidade());
      }
/* 
      if ($bolAcaoDesativar){
        $strResultado .= '<a href="'.PaginaSip::getInstance()->montarAncora($strId).'" onclick="acaoDesativar(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSip::getInstance()->getProxTabTabela().'"><img src="'.PaginaSip::getInstance()->getDiretorioImagensGlobal().'/desativar.gif" title="Desativar Mapeamento de Grupo de Rede" alt="Desativar Mapeamento de Grupo de Rede" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoReativar){
        $strResultado .= '<a href="'.PaginaSip::getInstance()->montarAncora($strId).'" onclick="acaoReativar(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSip::getInstance()->getProxTabTabela().'"><img src="'.PaginaSip::getInstance()->getDiretorioImagensGlobal().'/reativar.gif" title="Reativar Mapeamento de Grupo de Rede" alt="Reativar Mapeamento de Grupo de Rede" class="infraImg" /></a>&nbsp;';
      }
 */

      if ($bolAcaoExcluir){
        $strResultado .= '<a href="'.PaginaSip::getInstance()->montarAncora($strId).'" onclick="acaoExcluir(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSip::getInstance()->getProxTabTabela().'"><img src="'.PaginaSip::getInstance()->getDiretorioImagensGlobal().'/excluir.gif" title="Excluir Mapeamento de Grupo de Rede" alt="Excluir Mapeamento de Grupo de Rede" class="infraImg" /></a>&nbsp;';
      }

      $strResultado .= '</td></tr>'."\n";
    }
    $strResultado .= '</table>';
  }
  if ($_GET['acao'] == 'rel_grupo_rede_unidade_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }else{
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }

  $strItensSelOrgaoGrupoRede = OrgaoINT::montarSelectSiglaTodos('null','&nbsp;',$numIdOrgaoGrupoRede);
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
#lblOrgaoGrupoRede {position:absolute;left:0%;top:0%;width:25%;}
#selOrgaoGrupoRede {position:absolute;left:0%;top:40%;width:25%;}
<?
PaginaSip::getInstance()->fecharStyle();
PaginaSip::getInstance()->montarJavaScript();
PaginaSip::getInstance()->abrirJavaScript();
?>

function inicializar(){
  if ('<?=$_GET['acao']?>'=='rel_grupo_rede_unidade_selecionar'){
    infraReceberSelecao();
    document.getElementById('btnFecharSelecao').focus();
  }else{
    document.getElementById('btnFechar').focus();
  }
  infraEfeitoTabelas();
}

<? if ($bolAcaoDesativar){ ?>
function acaoDesativar(id,desc){
  if (confirm("Confirma desativação do Mapeamento de Grupo de Rede \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmRelGrupoRedeUnidadeLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmRelGrupoRedeUnidadeLista').submit();
  }
}

function acaoDesativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Mapeamento de Grupo de Rede selecionado.');
    return;
  }
  if (confirm("Confirma desativação dos Mapeamentos de Grupos de Rede selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmRelGrupoRedeUnidadeLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmRelGrupoRedeUnidadeLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoReativar){ ?>
function acaoReativar(id,desc){
  if (confirm("Confirma reativação do Mapeamento de Grupo de Rede \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmRelGrupoRedeUnidadeLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmRelGrupoRedeUnidadeLista').submit();
  }
}

function acaoReativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Mapeamento de Grupo de Rede selecionado.');
    return;
  }
  if (confirm("Confirma reativação dos Mapeamentos de Grupos de Rede selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmRelGrupoRedeUnidadeLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmRelGrupoRedeUnidadeLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoExcluir){ ?>
function acaoExcluir(id,desc){
  if (confirm("Confirma exclusão do Mapeamento de Grupo de Rede \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmRelGrupoRedeUnidadeLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmRelGrupoRedeUnidadeLista').submit();
  }
}

function acaoExclusaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Mapeamento de Grupo de Rede selecionado.');
    return;
  }
  if (confirm("Confirma exclusão dos Mapeamentos de Grupos de Rede selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmRelGrupoRedeUnidadeLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmRelGrupoRedeUnidadeLista').submit();
  }
}
<? } ?>

<?
PaginaSip::getInstance()->fecharJavaScript();
PaginaSip::getInstance()->fecharHead();
PaginaSip::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmRelGrupoRedeUnidadeLista" method="post" action="<?=SessaoSip::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?
  PaginaSip::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSip::getInstance()->abrirAreaDados('5em');
  ?>
  <label id="lblOrgaoGrupoRede" for="selOrgaoGrupoRede" accesskey="" class="infraLabelOpcional">Órgão:</label>
  <select id="selOrgaoGrupoRede" name="selOrgaoGrupoRede" onchange="this.form.submit();" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" >
  <?=$strItensSelOrgaoGrupoRede?>
  </select>
  <?
  PaginaSip::getInstance()->fecharAreaDados();
  PaginaSip::getInstance()->montarAreaTabela($strResultado,$numRegistros);
  //PaginaSip::getInstance()->montarAreaDebug();
  PaginaSip::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSip::getInstance()->fecharBody();
PaginaSip::getInstance()->fecharHtml();
?>