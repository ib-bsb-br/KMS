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

  PaginaSip::getInstance()->prepararSelecao('grupo_rede_selecionar');

  SessaoSip::getInstance()->validarPermissao($_GET['acao']);

  if (isset($_GET['id_orgao'])){
    PaginaSip::getInstance()->salvarCampo('selOrgao',$_GET['id_orgao']);
  } else {  
    PaginaSip::getInstance()->salvarCamposPost(array('selOrgao'));
  }

  switch($_GET['acao']){
    case 'grupo_rede_excluir':
      try{
        $arrStrIds = PaginaSip::getInstance()->getArrStrItensSelecionados();
        $arrObjGrupoRedeDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objGrupoRedeDTO = new GrupoRedeDTO();
          $objGrupoRedeDTO->setNumIdGrupoRede($arrStrIds[$i]);
          $arrObjGrupoRedeDTO[] = $objGrupoRedeDTO;
        }
        $objGrupoRedeRN = new GrupoRedeRN();
        $objGrupoRedeRN->excluir($arrObjGrupoRedeDTO);
        PaginaSip::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSip::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

/* 
    case 'grupo_rede_desativar':
      try{
        $arrStrIds = PaginaSip::getInstance()->getArrStrItensSelecionados();
        $arrObjGrupoRedeDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objGrupoRedeDTO = new GrupoRedeDTO();
          $objGrupoRedeDTO->setNumIdGrupoRede($arrStrIds[$i]);
          $arrObjGrupoRedeDTO[] = $objGrupoRedeDTO;
        }
        $objGrupoRedeRN = new GrupoRedeRN();
        $objGrupoRedeRN->desativar($arrObjGrupoRedeDTO);
        PaginaSip::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSip::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'grupo_rede_reativar':
      $strTitulo = 'Reativar Grupos de Rede';
      if ($_GET['acao_confirmada']=='sim'){
        try{
          $arrStrIds = PaginaSip::getInstance()->getArrStrItensSelecionados();
          $arrObjGrupoRedeDTO = array();
          for ($i=0;$i<count($arrStrIds);$i++){
            $objGrupoRedeDTO = new GrupoRedeDTO();
            $objGrupoRedeDTO->setNumIdGrupoRede($arrStrIds[$i]);
            $arrObjGrupoRedeDTO[] = $objGrupoRedeDTO;
          }
          $objGrupoRedeRN = new GrupoRedeRN();
          $objGrupoRedeRN->reativar($arrObjGrupoRedeDTO);
          PaginaSip::getInstance()->setStrMensagem('Operação realizada com sucesso.');
        }catch(Exception $e){
          PaginaSip::getInstance()->processarExcecao($e);
        } 
        header('Location: '.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
        die;
      } 
      break;

 */
    case 'grupo_rede_selecionar':
      $strTitulo = PaginaSip::getInstance()->getTituloSelecao('Selecionar Grupo de Rede','Selecionar Grupos de Rede');

      //Se cadastrou alguem
      if ($_GET['acao_origem']=='grupo_rede_cadastrar'){
        if (isset($_GET['id_grupo_rede'])){
          PaginaSip::getInstance()->adicionarSelecionado($_GET['id_grupo_rede']);
        }
      }
      break;

    case 'grupo_rede_listar':
      $strTitulo = 'Grupos de Rede';
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $arrComandos = array();
  if ($_GET['acao'] == 'grupo_rede_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
  }

  /* if ($_GET['acao'] == 'grupo_rede_listar' || $_GET['acao'] == 'grupo_rede_selecionar'){ */
    $bolAcaoCadastrar = SessaoSip::getInstance()->verificarPermissao('grupo_rede_cadastrar');
    if ($bolAcaoCadastrar){
      $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao=grupo_rede_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
    }
  /* } */

  $objGrupoRedeDTO = new GrupoRedeDTO(true);
  $objGrupoRedeDTO->retNumIdGrupoRede();
  $objGrupoRedeDTO->retStrSiglaOrgao();
  $objGrupoRedeDTO->retStrOuLdap();
  $objGrupoRedeDTO->retStrDescricao();
  $objGrupoRedeDTO->retStrSinExcecao();
  
  
  
	//ORGAO 
	$numIdOrgao = PaginaSip::getInstance()->recuperarCampo('selOrgao');
	if ($numIdOrgao!==''){
	  $objGrupoRedeDTO->setNumIdOrgao($numIdOrgao);
	}else{
	  $objGrupoRedeDTO->setNumIdOrgao(null);
	}

/* 
  if ($_GET['acao'] == 'grupo_rede_reativar'){
    //Lista somente inativos
    $objGrupoRedeDTO->setBolExclusaoLogica(false);
    $objGrupoRedeDTO->setStrSinAtivo('N');
  }
 */
  PaginaSip::getInstance()->prepararOrdenacao($objGrupoRedeDTO, 'OuLdap', InfraDTO::$TIPO_ORDENACAO_ASC);
  //PaginaSip::getInstance()->prepararPaginacao($objGrupoRedeDTO);

  $objGrupoRedeRN = new GrupoRedeRN();
  $arrObjGrupoRedeDTO = $objGrupoRedeRN->listar($objGrupoRedeDTO);

  //PaginaSip::getInstance()->processarPaginacao($objGrupoRedeDTO);
  $numRegistros = count($arrObjGrupoRedeDTO);

  if ($numRegistros > 0){

    $bolCheck = false;

    if ($_GET['acao']=='grupo_rede_selecionar'){
      $bolAcaoReativar = false;
      $bolAcaoConsultar = SessaoSip::getInstance()->verificarPermissao('grupo_rede_consultar');
      $bolAcaoAlterar = SessaoSip::getInstance()->verificarPermissao('grupo_rede_alterar');
      $bolAcaoImprimir = false;
      $bolAcaoExcluir = false;
      $bolAcaoDesativar = false;
      $bolCheck = true;
/*     }else if ($_GET['acao']=='grupo_rede_reativar'){
      $bolAcaoReativar = SessaoSip::getInstance()->verificarPermissao('grupo_rede_reativar');
      $bolAcaoConsultar = SessaoSip::getInstance()->verificarPermissao('grupo_rede_consultar');
      $bolAcaoAlterar = false;
      $bolAcaoImprimir = true;
      $bolAcaoExcluir = SessaoSip::getInstance()->verificarPermissao('grupo_rede_excluir');
      $bolAcaoDesativar = false;
 */    }else{
      $bolAcaoReativar = false;
      $bolAcaoConsultar = SessaoSip::getInstance()->verificarPermissao('grupo_rede_consultar');
      $bolAcaoAlterar = SessaoSip::getInstance()->verificarPermissao('grupo_rede_alterar');
      $bolAcaoImprimir = true;
      $bolAcaoExcluir = SessaoSip::getInstance()->verificarPermissao('grupo_rede_excluir');
      $bolAcaoDesativar = SessaoSip::getInstance()->verificarPermissao('grupo_rede_desativar');
    }

    /* 
    if ($bolAcaoDesativar){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="t" id="btnDesativar" value="Desativar" onclick="acaoDesativacaoMultipla();" class="infraButton">Desa<span class="infraTeclaAtalho">t</span>ivar</button>';
      $strLinkDesativar = SessaoSip::getInstance()->assinarLink('controlador.php?acao=grupo_rede_desativar&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoReativar){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="R" id="btnReativar" value="Reativar" onclick="acaoReativacaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">R</span>eativar</button>';
      $strLinkReativar = SessaoSip::getInstance()->assinarLink('controlador.php?acao=grupo_rede_reativar&acao_origem='.$_GET['acao'].'&acao_confirmada=sim');
    }
     */

    if ($bolAcaoExcluir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
      $strLinkExcluir = SessaoSip::getInstance()->assinarLink('controlador.php?acao=grupo_rede_excluir&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoImprimir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';

    }

    $strResultado = '';

    /* if ($_GET['acao']!='grupo_rede_reativar'){ */
      $strSumarioTabela = 'Tabela de Grupos de Rede.';
      $strCaptionTabela = 'Grupos de Rede';
    /* }else{
      $strSumarioTabela = 'Tabela de Grupos de Rede Inativos.';
      $strCaptionTabela = 'Grupos de Rede Inativos';
    } */

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSip::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    if ($bolCheck) {
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaSip::getInstance()->getThCheck().'</th>'."\n";
    }
    //$strResultado .= '<th class="infraTh" width="10%">'.PaginaSip::getInstance()->getThOrdenacao($objGrupoRedeDTO,'Órgão','SiglaOrgao',$arrObjGrupoRedeDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh">'.PaginaSip::getInstance()->getThOrdenacao($objGrupoRedeDTO,'Unidade Organizacional LDAP','OuLdap',$arrObjGrupoRedeDTO).'</th>'."\n";
    //$strResultado .= '<th class="infraTh" width="20%">Descrição</th>'."\n";
    $strResultado .= '<th class="infraTh" width="10%">'.PaginaSip::getInstance()->getThOrdenacao($objGrupoRedeDTO,'Exceção','SinExcecao',$arrObjGrupoRedeDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="15%">Ações</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistros; $i++){

      $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      $strResultado .= $strCssTr;

      if ($bolCheck){
        $strResultado .= '<td valign="top">'.PaginaSip::getInstance()->getTrCheck($i,$arrObjGrupoRedeDTO[$i]->getNumIdGrupoRede(),$arrObjGrupoRedeDTO[$i]->getStrOuLdap()).'</td>';
      }
      //$strResultado .= '<td align="center" valign="top">'.$arrObjGrupoRedeDTO[$i]->getStrSiglaOrgao().'</td>';
      $strResultado .= '<td valign="top">'.PaginaSip::tratarHTML($arrObjGrupoRedeDTO[$i]->getStrOuLdap()).'</td>';
      //$strResultado .= '<td>'.nl2br($arrObjGrupoRedeDTO[$i]->getStrDescricao()).'</td>';
      $strResultado .= '<td align="center" valign="top">'.($arrObjGrupoRedeDTO[$i]->getStrSinExcecao()=='S'?'S':'').'</td>';
      $strResultado .= '<td align="center" valign="top">';

      $strResultado .= PaginaSip::getInstance()->getAcaoTransportarItem($i,$arrObjGrupoRedeDTO[$i]->getNumIdGrupoRede());

      if ($bolAcaoConsultar){
        $strResultado .= '<a href="'.SessaoSip::getInstance()->assinarLink('controlador.php?acao=grupo_rede_consultar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_grupo_rede='.$arrObjGrupoRedeDTO[$i]->getNumIdGrupoRede()).'" tabindex="'.PaginaSip::getInstance()->getProxTabTabela().'"><img src="'.PaginaSip::getInstance()->getDiretorioImagensGlobal().'/consultar.gif" title="Consultar Grupo de Rede" alt="Consultar Grupo de Rede" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoAlterar){
        $strResultado .= '<a href="'.SessaoSip::getInstance()->assinarLink('controlador.php?acao=grupo_rede_alterar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_grupo_rede='.$arrObjGrupoRedeDTO[$i]->getNumIdGrupoRede()).'" tabindex="'.PaginaSip::getInstance()->getProxTabTabela().'"><img src="'.PaginaSip::getInstance()->getDiretorioImagensGlobal().'/alterar.gif" title="Alterar Grupo de Rede" alt="Alterar Grupo de Rede" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoDesativar || $bolAcaoReativar || $bolAcaoExcluir){
        $strId = $arrObjGrupoRedeDTO[$i]->getNumIdGrupoRede();
        $strDescricao = PaginaSip::getInstance()->formatarParametrosJavaScript($arrObjGrupoRedeDTO[$i]->getStrOuLdap());
      }
/* 
      if ($bolAcaoDesativar){
        $strResultado .= '<a href="'.PaginaSip::getInstance()->montarAncora($strId).'" onclick="acaoDesativar(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSip::getInstance()->getProxTabTabela().'"><img src="'.PaginaSip::getInstance()->getDiretorioImagensGlobal().'/desativar.gif" title="Desativar Grupo de Rede" alt="Desativar Grupo de Rede" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoReativar){
        $strResultado .= '<a href="'.PaginaSip::getInstance()->montarAncora($strId).'" onclick="acaoReativar(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSip::getInstance()->getProxTabTabela().'"><img src="'.PaginaSip::getInstance()->getDiretorioImagensGlobal().'/reativar.gif" title="Reativar Grupo de Rede" alt="Reativar Grupo de Rede" class="infraImg" /></a>&nbsp;';
      }
 */

      if ($bolAcaoExcluir){
        $strResultado .= '<a href="'.PaginaSip::getInstance()->montarAncora($strId).'" onclick="acaoExcluir(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSip::getInstance()->getProxTabTabela().'"><img src="'.PaginaSip::getInstance()->getDiretorioImagensGlobal().'/excluir.gif" title="Excluir Grupo de Rede" alt="Excluir Grupo de Rede" class="infraImg" /></a>&nbsp;';
      }

      $strResultado .= '</td></tr>'."\n";
    }
    $strResultado .= '</table>';
  }
  if ($_GET['acao'] == 'grupo_rede_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }else{
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }
  
  $strItensSelOrgao = OrgaoINT::montarSelectSiglaTodos('null','&nbsp;', $numIdOrgao);
  
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
#selOrgao {position:absolute;left:0%;top:40%;width:25%;}

<?
PaginaSip::getInstance()->fecharStyle();
PaginaSip::getInstance()->montarJavaScript();
PaginaSip::getInstance()->abrirJavaScript();
?>

function inicializar(){
  if ('<?=$_GET['acao']?>'=='grupo_rede_selecionar'){
    infraReceberSelecao();
    document.getElementById('btnFecharSelecao').focus();
  }else{
    document.getElementById('btnFechar').focus();
  }
  infraEfeitoTabelas();
}

<? if ($bolAcaoDesativar){ ?>
function acaoDesativar(id,desc){
  if (confirm("Confirma desativação do Grupo de Rede \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmGrupoRedeLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmGrupoRedeLista').submit();
  }
}

function acaoDesativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Grupo de Rede selecionado.');
    return;
  }
  if (confirm("Confirma desativação dos Grupos de Rede selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmGrupoRedeLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmGrupoRedeLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoReativar){ ?>
function acaoReativar(id,desc){
  if (confirm("Confirma reativação do Grupo de Rede \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmGrupoRedeLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmGrupoRedeLista').submit();
  }
}

function acaoReativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Grupo de Rede selecionado.');
    return;
  }
  if (confirm("Confirma reativação dos Grupos de Rede selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmGrupoRedeLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmGrupoRedeLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoExcluir){ ?>
function acaoExcluir(id,desc){
  if (confirm("Confirma exclusão do Grupo de Rede \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmGrupoRedeLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmGrupoRedeLista').submit();
  }
}

function acaoExclusaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Grupo de Rede selecionado.');
    return;
  }
  if (confirm("Confirma exclusão dos Grupos de Rede selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmGrupoRedeLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmGrupoRedeLista').submit();
  }
}
<? } ?>


<?
PaginaSip::getInstance()->fecharJavaScript();
PaginaSip::getInstance()->fecharHead();
PaginaSip::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmGrupoRedeLista" method="post" action="<?=SessaoSip::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?
  PaginaSip::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSip::getInstance()->abrirAreaDados('5em');
  ?>

  <label id="lblOrgao" for="selOrgao" accesskey="o" class="infraLabelObrigatorio">Ór<span class="infraTeclaAtalho">g</span>ão:</label>
  <select id="selOrgao" name="selOrgao" onchange="this.form.submit();" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" >
  <?=$strItensSelOrgao?>
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