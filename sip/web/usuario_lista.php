<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 06/05/2009 - criado por mga
*
* Versão do Gerador de Código: 1.26.0
*
* Versão no CVS: $Id$
*/

try {
  require_once dirname(__FILE__).'/Sip.php';

  session_start();

  //////////////////////////////////////////////////////////////////////////////
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(true);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSip::getInstance()->validarLink();

  PaginaSip::getInstance()->prepararSelecao('usuario_selecionar');

  SessaoSip::getInstance()->validarPermissao($_GET['acao']);

  PaginaSip::getInstance()->salvarCamposPost(array('selOrgaoUsuario','txtSiglaUsuario','txtNomeUsuario','txtIdOrigemUsuario'));

  switch($_GET['acao']){
    case 'usuario_excluir':
      try{
        $arrStrIds = PaginaSip::getInstance()->getArrStrItensSelecionados();
        $arrObjUsuarioDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objUsuarioDTO = new UsuarioDTO();
          $objUsuarioDTO->setNumIdUsuario($arrStrIds[$i]);
          $arrObjUsuarioDTO[] = $objUsuarioDTO;
        }
        $objUsuarioRN = new UsuarioRN();
        $objUsuarioRN->excluir($arrObjUsuarioDTO);
        PaginaSip::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSip::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;


    case 'usuario_desativar':
      try{
        $arrStrIds = PaginaSip::getInstance()->getArrStrItensSelecionados();
        $arrObjUsuarioDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objUsuarioDTO = new UsuarioDTO();
          $objUsuarioDTO->setNumIdUsuario($arrStrIds[$i]);
          $arrObjUsuarioDTO[] = $objUsuarioDTO;
        }
        $objUsuarioRN = new UsuarioRN();
        $objUsuarioRN->desativar($arrObjUsuarioDTO);
        PaginaSip::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSip::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'usuario_reativar':
      $strTitulo = 'Reativar Usuários';
      if ($_GET['acao_confirmada']=='sim'){
        try{
          $arrStrIds = PaginaSip::getInstance()->getArrStrItensSelecionados();
          $arrObjUsuarioDTO = array();
          for ($i=0;$i<count($arrStrIds);$i++){
            $objUsuarioDTO = new UsuarioDTO();
            $objUsuarioDTO->setNumIdUsuario($arrStrIds[$i]);
            $arrObjUsuarioDTO[] = $objUsuarioDTO;
          }
          $objUsuarioRN = new UsuarioRN();
          $objUsuarioRN->reativar($arrObjUsuarioDTO);
          PaginaSip::getInstance()->setStrMensagem('Operação realizada com sucesso.');
        }catch(Exception $e){
          PaginaSip::getInstance()->processarExcecao($e);
        } 
        header('Location: '.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
        die;
      } 
      break;


    case 'usuario_selecionar':
      $strTitulo = PaginaSip::getInstance()->getTituloSelecao('Selecionar Usuário','Selecionar Usuários');

      //Se cadastrou alguem
      if ($_GET['acao_origem']=='usuario_cadastrar'){
        if (isset($_GET['id_usuario'])){
          PaginaSip::getInstance()->adicionarSelecionado($_GET['id_usuario']);
        }
      }
      break;

    case 'usuario_listar':
      $strTitulo = 'Usuários';
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $arrComandos = array();
  
  $arrComandos[] = '<input type="submit" id="btnPesquisar" value="Pesquisar" class="infraButton" />';  

    
  if ($_GET['acao'] == 'usuario_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
  }

  if ($_GET['acao'] == 'usuario_listar' || $_GET['acao'] == 'usuario_selecionar'){
    $bolAcaoCadastrar = SessaoSip::getInstance()->verificarPermissao('usuario_cadastrar');
    if ($bolAcaoCadastrar){
      $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao=usuario_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
    }
  }

  $objUsuarioDTO = new UsuarioDTO(true);
  $objUsuarioDTO->retNumIdUsuario();
  $objUsuarioDTO->retStrIdOrigem();
  $objUsuarioDTO->retStrSigla();
  $objUsuarioDTO->retStrNome();
  $objUsuarioDTO->retStrSiglaOrgao();
  $objUsuarioDTO->retStrDescricaoOrgao();
  
  $numIdOrgao = PaginaSip::getInstance()->recuperarCampo('selOrgaoUsuario');
  if ($numIdOrgao!==''){
    $objUsuarioDTO->setNumIdOrgao($numIdOrgao);
  }

  $strSiglaPesquisa = trim(PaginaSip::getInstance()->recuperarCampo('txtSiglaUsuario'));
  if ($strSiglaPesquisa!==''){
    $objUsuarioDTO->setStrSigla($strSiglaPesquisa);
  }
  
  $strNomePesquisa = PaginaSip::getInstance()->recuperarCampo('txtNomeUsuario');
  if ($strNomePesquisa!==''){
    $objUsuarioDTO->setStrNome($strNomePesquisa);
  }

  $strIdOrigemPesquisa = PaginaSip::getInstance()->recuperarCampo('txtIdOrigemUsuario');
  if ($strIdOrigemPesquisa!==''){
    $objUsuarioDTO->setStrIdOrigem($strIdOrigemPesquisa);
  }


  if ($_GET['acao'] == 'usuario_reativar'){
    //Lista somente inativos
    $objUsuarioDTO->setBolExclusaoLogica(false);
    $objUsuarioDTO->setStrSinAtivo('N');
  }

  PaginaSip::getInstance()->prepararOrdenacao($objUsuarioDTO, 'Sigla', InfraDTO::$TIPO_ORDENACAO_ASC);
  PaginaSip::getInstance()->prepararPaginacao($objUsuarioDTO);

  $objUsuarioRN = new UsuarioRN();
  $arrObjUsuarioDTO = $objUsuarioRN->pesquisar($objUsuarioDTO);

  PaginaSip::getInstance()->processarPaginacao($objUsuarioDTO);
  $numRegistros = count($arrObjUsuarioDTO);

  if ($numRegistros > 0){

    $bolCheck = false;

    if ($_GET['acao']=='usuario_selecionar'){
      $bolAcaoReativar = false;
      $bolAcaoConsultar = SessaoSip::getInstance()->verificarPermissao('usuario_consultar');
      $bolAcaoAlterar = SessaoSip::getInstance()->verificarPermissao('usuario_alterar');
      $bolAcaoImprimir = false;
      $bolAcaoExcluir = false;
      $bolAcaoDesativar = false;
      $bolCheck = true;
    }else if ($_GET['acao']=='usuario_reativar'){
      $bolAcaoReativar = SessaoSip::getInstance()->verificarPermissao('usuario_reativar');
      $bolAcaoConsultar = SessaoSip::getInstance()->verificarPermissao('usuario_consultar');
      $bolAcaoAlterar = false;
      $bolAcaoImprimir = true;
      $bolAcaoExcluir = SessaoSip::getInstance()->verificarPermissao('usuario_excluir');
      $bolAcaoDesativar = false;
    }else{
      $bolAcaoReativar = false;
      $bolAcaoConsultar = SessaoSip::getInstance()->verificarPermissao('usuario_consultar');
      $bolAcaoAlterar = SessaoSip::getInstance()->verificarPermissao('usuario_alterar');
      $bolAcaoImprimir = true;
      $bolAcaoExcluir = SessaoSip::getInstance()->verificarPermissao('usuario_excluir');
      $bolAcaoDesativar = SessaoSip::getInstance()->verificarPermissao('usuario_desativar');
    }

    
    if ($bolAcaoDesativar){
      //$bolCheck = true;
      //$arrComandos[] = '<button type="button" accesskey="t" id="btnDesativar" value="Desativar" onclick="acaoDesativacaoMultipla();" class="infraButton">Desa<span class="infraTeclaAtalho">t</span>ivar</button>';
      $strLinkDesativar = SessaoSip::getInstance()->assinarLink('controlador.php?acao=usuario_desativar&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoReativar){
      //$bolCheck = true;
      //$arrComandos[] = '<button type="button" accesskey="R" id="btnReativar" value="Reativar" onclick="acaoReativacaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">R</span>eativar</button>';
      $strLinkReativar = SessaoSip::getInstance()->assinarLink('controlador.php?acao=usuario_reativar&acao_origem='.$_GET['acao'].'&acao_confirmada=sim');
    }
    

    if ($bolAcaoExcluir){
      //$bolCheck = true;
      //$arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
      $strLinkExcluir = SessaoSip::getInstance()->assinarLink('controlador.php?acao=usuario_excluir&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoImprimir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';

    }

    $strResultado = '';

    if ($_GET['acao']!='usuario_reativar'){
      $strSumarioTabela = 'Tabela de Usuários.';
      $strCaptionTabela = 'Usuários';
    }else{
      $strSumarioTabela = 'Tabela de Usuários Inativos.';
      $strCaptionTabela = 'Usuários Inativos';
    }

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSip::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    if ($bolCheck) {
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaSip::getInstance()->getThCheck().'</th>'."\n";
    }
    $strResultado .= '<th class="infraTh" width="8%">'.PaginaSip::getInstance()->getThOrdenacao($objUsuarioDTO,'ID SIP','IdUsuario',$arrObjUsuarioDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="8%">'.PaginaSip::getInstance()->getThOrdenacao($objUsuarioDTO,'ID Origem','IdOrigem',$arrObjUsuarioDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="15%">'.PaginaSip::getInstance()->getThOrdenacao($objUsuarioDTO,'Sigla','Sigla',$arrObjUsuarioDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh">'.PaginaSip::getInstance()->getThOrdenacao($objUsuarioDTO,'Nome','Nome',$arrObjUsuarioDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="10%">'.PaginaSip::getInstance()->getThOrdenacao($objUsuarioDTO,'Órgão','SiglaOrgao',$arrObjUsuarioDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="15%">Ações</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistros; $i++){

      $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      $strResultado .= $strCssTr;

      if ($bolCheck){
        $strResultado .= '<td valign="top">'.PaginaSip::getInstance()->getTrCheck($i,$arrObjUsuarioDTO[$i]->getNumIdUsuario(),$arrObjUsuarioDTO[$i]->getStrSigla()).'</td>';
      }
      $strResultado .= '<td align="center">'.PaginaSip::tratarHTML($arrObjUsuarioDTO[$i]->getNumIdUsuario()).'</td>';
      $strResultado .= '<td align="center">'.PaginaSip::tratarHTML($arrObjUsuarioDTO[$i]->getStrIdOrigem()).'</td>';
      $strResultado .= '<td>'.PaginaSip::tratarHTML($arrObjUsuarioDTO[$i]->getStrSigla()).'</td>';
      $strResultado .= '<td>'.PaginaSip::tratarHTML($arrObjUsuarioDTO[$i]->getStrNome()).'</td>';
      $strResultado .= '<td align="center"><a alt="'.PaginaSip::tratarHTML($arrObjUsuarioDTO[$i]->getStrDescricaoOrgao()).'" title="'.PaginaSip::tratarHTML($arrObjUsuarioDTO[$i]->getStrDescricaoOrgao()).'" class="ancoraSigla">'.PaginaSip::tratarHTML($arrObjUsuarioDTO[$i]->getStrSiglaOrgao()).'</a></td>';
      $strResultado .= '<td align="center">';

      $strResultado .= PaginaSip::getInstance()->getAcaoTransportarItem($i,$arrObjUsuarioDTO[$i]->getNumIdUsuario());

      if ($bolAcaoConsultar){
        $strResultado .= '<a href="'.SessaoSip::getInstance()->assinarLink('controlador.php?acao=usuario_consultar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_usuario='.$arrObjUsuarioDTO[$i]->getNumIdUsuario()).'" tabindex="'.PaginaSip::getInstance()->getProxTabTabela().'"><img src="'.PaginaSip::getInstance()->getDiretorioImagensGlobal().'/consultar.gif" title="Consultar Usuário" alt="Consultar Usuário" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoAlterar){
        $strResultado .= '<a href="'.SessaoSip::getInstance()->assinarLink('controlador.php?acao=usuario_alterar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_usuario='.$arrObjUsuarioDTO[$i]->getNumIdUsuario()).'" tabindex="'.PaginaSip::getInstance()->getProxTabTabela().'"><img src="'.PaginaSip::getInstance()->getDiretorioImagensGlobal().'/alterar.gif" title="Alterar Usuário" alt="Alterar Usuário" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoDesativar || $bolAcaoReativar || $bolAcaoExcluir){
        $strId = $arrObjUsuarioDTO[$i]->getNumIdUsuario();
        $strDescricao = PaginaSip::getInstance()->formatarParametrosJavaScript($arrObjUsuarioDTO[$i]->getStrSigla());
      }

      if ($bolAcaoDesativar){
        $strResultado .= '<a href="#ID-'.$strId.'" onclick="acaoDesativar(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSip::getInstance()->getProxTabTabela().'"><img src="'.PaginaSip::getInstance()->getDiretorioImagensGlobal().'/desativar.gif" title="Desativar Usuário" alt="Desativar Usuário" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoReativar){
        $strResultado .= '<a href="#ID-'.$strId.'" onclick="acaoReativar(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSip::getInstance()->getProxTabTabela().'"><img src="'.PaginaSip::getInstance()->getDiretorioImagensGlobal().'/reativar.gif" title="Reativar Usuário" alt="Reativar Usuário" class="infraImg" /></a>&nbsp;';
      }


      if ($bolAcaoExcluir){
        $strResultado .= '<a href="#ID-'.$strId.'" onclick="acaoExcluir(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSip::getInstance()->getProxTabTabela().'"><img src="'.PaginaSip::getInstance()->getDiretorioImagensGlobal().'/excluir.gif" title="Excluir Usuário" alt="Excluir Usuário" class="infraImg" /></a>&nbsp;';
      }

      $strResultado .= '</td></tr>'."\n";
    }
    $strResultado .= '</table>';
  }
  if ($_GET['acao'] == 'usuario_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }else{
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }
  
  $strItensSelOrgao = OrgaoINT::montarSelectSiglaTodos('','Todos',$numIdOrgao);

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

#lblOrgaoUsuario {position:absolute;left:0%;top:0%;width:20%;}
#selOrgaoUsuario {position:absolute;left:0%;top:40%;width:20%;}

#lblSiglaUsuario {position:absolute;left:22%;top:0%;width:15%;}
#txtSiglaUsuario {position:absolute;left:22%;top:40%;width:15%;}

#lblNomeUsuario {position:absolute;left:39%;top:0%;width:40%;}
#txtNomeUsuario {position:absolute;left:39%;top:40%;width:40%;}

#lblIdOrigemUsuario {position:absolute;left:81%;top:0%;width:15%;}
#txtIdOrigemUsuario {position:absolute;left:81%;top:40%;width:15%;}

<?
PaginaSip::getInstance()->fecharStyle();
PaginaSip::getInstance()->montarJavaScript();
PaginaSip::getInstance()->abrirJavaScript();
?>

function inicializar(){
  if ('<?=$_GET['acao']?>'=='usuario_selecionar'){
    infraReceberSelecao();
    document.getElementById('btnFecharSelecao').focus();
  }else{
    document.getElementById('btnFechar').focus();
  }
  infraEfeitoTabelas();
}

<? if ($bolAcaoDesativar){ ?>
function acaoDesativar(id,desc){
  if (confirm("Confirma desativação do Usuário \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmUsuarioLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmUsuarioLista').submit();
  }
}

function acaoDesativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Usuário selecionado.');
    return;
  }
  if (confirm("Confirma desativação dos Usuários selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmUsuarioLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmUsuarioLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoReativar){ ?>
function acaoReativar(id,desc){
  if (confirm("Confirma reativação do Usuário \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmUsuarioLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmUsuarioLista').submit();
  }
}

function acaoReativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Usuário selecionado.');
    return;
  }
  if (confirm("Confirma reativação dos Usuários selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmUsuarioLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmUsuarioLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoExcluir){ ?>
function acaoExcluir(id,desc){
  if (confirm("Confirma exclusão do Usuário \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmUsuarioLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmUsuarioLista').submit();
  }
}

function acaoExclusaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Usuário selecionado.');
    return;
  }
  if (confirm("Confirma exclusão dos Usuários selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmUsuarioLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmUsuarioLista').submit();
  }
}
<? } ?>

<?
PaginaSip::getInstance()->fecharJavaScript();
PaginaSip::getInstance()->fecharHead();
PaginaSip::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmUsuarioLista" method="post" action="<?=SessaoSip::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?
  PaginaSip::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSip::getInstance()->abrirAreaDados('5em');
  ?>
  <label id="lblOrgaoUsuario" for="selOrgaoUsuario" accesskey="o" class="infraLabelOpcional">Órgã<span class="infraTeclaAtalho">o</span>:</label>
  <select id="selOrgaoUsuario" name="selOrgaoUsuario" onchange="this.form.submit();" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" >
  <?=$strItensSelOrgao?>
  </select>
  
  <label id="lblSiglaUsuario" for="txtSiglaUsuario" accesskey="S" class="infraLabelOpcional"><span class="infraTeclaAtalho">S</span>igla:</label>
  <input type="text" id="txtSiglaUsuario" name="txtSiglaUsuario" class="infraText" value="<?=PaginaSip::tratarHTML($strSiglaPesquisa)?>" maxlength="100" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />
  
  <label id="lblNomeUsuario" for="txtNomeUsuario" accesskey="N" class="infraLabelOpcional"><span class="infraTeclaAtalho">N</span>ome:</label>
  <input type="text" id="txtNomeUsuario" name="txtNomeUsuario" class="infraText" value="<?=PaginaSip::tratarHTML($strNomePesquisa)?>" maxlength="50" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />

  <label id="lblIdOrigemUsuario" for="txtIdOrigemUsuario" accesskey="" class="infraLabelOpcional">ID Origem:</label>
  <input type="text" id="txtIdOrigemUsuario" name="txtIdOrigemUsuario" class="infraText" value="<?=PaginaSip::tratarHTML($strIdOrigemPesquisa);?>" maxlength="50" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />

  <?
  PaginaSip::getInstance()->fecharAreaDados();
  PaginaSip::getInstance()->montarAreaTabela($strResultado,$numRegistros);
  PaginaSip::getInstance()->montarAreaDebug();
  PaginaSip::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSip::getInstance()->fecharBody();
PaginaSip::getInstance()->fecharHtml();
?>