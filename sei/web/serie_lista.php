<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 01/07/2008 - criado por fbv
*
* Versão do Gerador de Código: 1.19.0
*
* Versão no CVS: $Id$
*/

try {
  require_once dirname(__FILE__).'/SEI.php';

  session_start();

  //////////////////////////////////////////////////////////////////////////////
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(false);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  PaginaSEI::getInstance()->prepararSelecao('serie_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

	if (isset($_GET['id_grupo_serie'])){
    PaginaSEI::getInstance()->salvarCampo('selGrupoSerie',$_GET['id_grupo_serie']);
    //$_POST['hdnInfraTotalRegistros'] = 0;
	}else{
	  PaginaSEI::getInstance()->salvarCamposPost(array('selGrupoSerie'));
	}
  
  PaginaSEI::getInstance()->salvarCamposPost(array('selModeloPesquisa', 'txtNomeSeriePesquisa'));

  switch($_GET['acao']){
    case 'serie_excluir':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjSerieDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objSerieDTO = new SerieDTO();
          $objSerieDTO->setNumIdSerie($arrStrIds[$i]);
          $arrObjSerieDTO[] = $objSerieDTO;
        }
        $objSerieRN = new SerieRN();
        $objSerieRN->excluirRN0645($arrObjSerieDTO);
        PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;


    case 'serie_desativar':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjSerieDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objSerieDTO = new SerieDTO();
          $objSerieDTO->setNumIdSerie($arrStrIds[$i]);
          $arrObjSerieDTO[] = $objSerieDTO;
        }
        $objSerieRN = new SerieRN();
        $objSerieRN->desativarRN0648($arrObjSerieDTO);
        PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'serie_reativar':
      $strTitulo = 'Reativar Tipos de Documento';
      if ($_GET['acao_confirmada']=='sim'){
        try{
          $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
          $arrObjSerieDTO = array();
          for ($i=0;$i<count($arrStrIds);$i++){
            $objSerieDTO = new SerieDTO();
            $objSerieDTO->setNumIdSerie($arrStrIds[$i]);
            $arrObjSerieDTO[] = $objSerieDTO;
          }
          $objSerieRN = new SerieRN();
          $objSerieRN->reativarRN0649($arrObjSerieDTO);
          PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        } 
        header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
        die;
      } 
      break;


    case 'serie_selecionar':
      $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Tipo de Documento','Selecionar Tipos de Documento');

      //Se cadastrou alguem
      if ($_GET['acao_origem']=='serie_cadastrar'){
        if (isset($_GET['id_serie'])){
          PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_serie']);
        }
      }
      break;

    case 'serie_listar':
      $strTitulo = 'Tipos de Documento';
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $arrComandos = array();
  
  $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';
  
  if ($_GET['acao'] == 'serie_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
  }

  if ($_GET['acao'] == 'serie_listar' || $_GET['acao'] == 'serie_selecionar'){
    $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('serie_cadastrar');
    if ($bolAcaoCadastrar){
      $arrComandos[] = '<button type="button" accesskey="N" id="btnNova" value="Nova" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=serie_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo Tipo de Documento</button>';
    }
    
    $bolAcaoCadastrarGrupoSerie = SessaoSEI::getInstance()->verificarPermissao('grupo_serie_cadastrar');
    if ($bolAcaoCadastrarGrupoSerie){
      $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=grupo_serie_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']).'\'" class="infraButton">Novo <span class="infraTeclaAtalho">G</span>rupo</button>';
    }
    
  }
  
  $objSerieDTO = new SerieDTO(true);
  $objSerieDTO->retNumIdSerie();
  $objSerieDTO->retStrNome();
  //$objSerieDTO->retStrDescricao();
  $objSerieDTO->retStrNomeGrupoSerie();

  $numIdGrupoSerie = PaginaSEI::getInstance()->recuperarCampo('selGrupoSerie');
  if ($numIdGrupoSerie!==''){
    $objSerieDTO->setNumIdGrupoSerie($numIdGrupoSerie);
  }

  $strNomeSeriePesquisa = PaginaSEI::getInstance()->recuperarCampo('txtNomeSeriePesquisa');
  if (trim($strNomeSeriePesquisa) != ''){
    $objSerieDTO->setStrNome('%'.trim($strNomeSeriePesquisa.'%'),InfraDTO::$OPER_LIKE);
  }

  $numIdModelo = PaginaSEI::getInstance()->recuperarCampo('selModeloPesquisa','null');
  if ($numIdModelo!=='null'){
    $objSerieDTO->setNumIdModelo($numIdModelo);
  }
  
  if ($_GET['acao'] == 'serie_reativar'){
    //Lista somente inativos
    $objSerieDTO->setBolExclusaoLogica(false);
    $objSerieDTO->setStrSinAtivo('N');
  }

  PaginaSEI::getInstance()->prepararOrdenacao($objSerieDTO, 'Nome', InfraDTO::$TIPO_ORDENACAO_ASC);
  PaginaSEI::getInstance()->prepararPaginacao($objSerieDTO);

  $objSerieRN = new SerieRN();
  $arrObjSerieDTO = $objSerieRN->listarRN0646($objSerieDTO);
  
  PaginaSEI::getInstance()->processarPaginacao($objSerieDTO);
  $numRegistros = count($arrObjSerieDTO);

  if ($numRegistros > 0){

    $bolCheck = false;

    if ($_GET['acao']=='serie_selecionar'){
      $bolAcaoReativar = false;
      $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('serie_consultar');
      $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('serie_alterar');
      $bolAcaoImprimir = false;
      $bolAcaoExcluir = false;
      $bolAcaoDesativar = false;
      $bolCheck = true;
    }else if ($_GET['acao']=='serie_reativar'){
      $bolAcaoReativar = SessaoSEI::getInstance()->verificarPermissao('serie_reativar');
      $bolAcaoConsultar = false;
      $bolAcaoAlterar = false;
      $bolAcaoImprimir = true;
      $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('serie_excluir');
      $bolAcaoDesativar = false;
    }else{
      $bolAcaoReativar = false;
      $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('serie_consultar');
      $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('serie_alterar');
      $bolAcaoImprimir = true;
      $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('serie_excluir');
      $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('serie_desativar');
    }

    
    if ($bolAcaoDesativar){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="t" id="btnDesativar" value="Desativar" onclick="acaoDesativacaoMultipla();" class="infraButton">Desa<span class="infraTeclaAtalho">t</span>ivar</button>';
      $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=serie_desativar&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoReativar){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="R" id="btnReativar" value="Reativar" onclick="acaoReativacaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">R</span>eativar</button>';
      $strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=serie_reativar&acao_origem='.$_GET['acao'].'&acao_confirmada=sim');
    }
    

    if ($bolAcaoExcluir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
      $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=serie_excluir&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoImprimir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';

    }

    $strResultado = '';

    if ($_GET['acao']!='serie_reativar'){
      $strSumarioTabela = 'Tabela de Tipos de Documento.';
      $strCaptionTabela = 'Tipos de Documento';
    }else{
      $strSumarioTabela = 'Tabela de Tipos de Documento Inativos.';
      $strCaptionTabela = 'Tipos de Documento Inativos';
    }

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    if ($bolCheck) {
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
    }
    $strResultado .= '<th class="infraTh" width="10%">ID</th>'."\n";
    $strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThOrdenacao($objSerieDTO,'Nome','Nome',$arrObjSerieDTO).'</th>'."\n";
    //$strResultado .= '<th class="infraTh">Modelo</th>'."\n";
    $strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThOrdenacao($objSerieDTO,'Grupo','NomeGrupoSerie',$arrObjSerieDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="15%">Ações</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistros; $i++){

      $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      $strResultado .= $strCssTr;

      if ($bolCheck){
        $strResultado .= '<td valign="top">'.PaginaSEI::getInstance()->getTrCheck($i,$arrObjSerieDTO[$i]->getNumIdSerie(),$arrObjSerieDTO[$i]->getStrNome()).'</td>';
      }
      $strResultado .= '<td align="center">'.$arrObjSerieDTO[$i]->getNumIdSerie().'</td>';
      $strResultado .= '<td>'.PaginaSEI::tratarHTML($arrObjSerieDTO[$i]->getStrNome()).'</td>';
      $strResultado .= '<td>'.PaginaSEI::tratarHTML($arrObjSerieDTO[$i]->getStrNomeGrupoSerie()).'</td>';
      $strResultado .= '<td align="center">';
      
      $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i,$arrObjSerieDTO[$i]->getNumIdSerie());
      
      if ($bolAcaoConsultar){
        $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=serie_consultar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_serie='.$arrObjSerieDTO[$i]->getNumIdSerie()).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/consultar.gif" title="Consultar Tipo de Documento" alt="Consultar Tipo de Documento" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoAlterar){
        $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=serie_alterar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_serie='.$arrObjSerieDTO[$i]->getNumIdSerie()).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/alterar.gif" title="Alterar Tipo de Documento" alt="Alterar Tipo de Documento" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoDesativar || $bolAcaoReativar || $bolAcaoExcluir){
        $strId = $arrObjSerieDTO[$i]->getNumIdSerie();
        $strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript($arrObjSerieDTO[$i]->getStrNome());
      }

      if ($bolAcaoDesativar){
        $strResultado .= '<a href="#ID-'.$strId.'" onclick="acaoDesativar(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/desativar.gif" title="Desativar Tipo de Documento" alt="Desativar Tipo de Documento" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoReativar){
        $strResultado .= '<a href="#ID-'.$strId.'" onclick="acaoReativar(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/reativar.gif" title="Reativar Tipo de Documento" alt="Reativar Tipo de Documento" class="infraImg" /></a>&nbsp;';
      }


      if ($bolAcaoExcluir){
        $strResultado .= '<a href="#ID-'.$strId.'" onclick="acaoExcluir(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/excluir.gif" title="Excluir Tipo de Documento" alt="Excluir Tipo de Documento" class="infraImg" /></a>&nbsp;';
      }

      $strResultado .= '</td></tr>'."\n";
    }
    $strResultado .= '</table>';
  }
  if ($_GET['acao'] == 'serie_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }else{
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($numIdGrupoSerie)).'\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }

  $strItensSelGrupoSerie = GrupoSerieINT::montarSelectNomeRI0801('','Todos',$numIdGrupoSerie);
  $strItensSelModelo = ModeloINT::montarSelectNome('null','Todos',$numIdModelo);

}catch(Exception $e){
  PaginaSEI::getInstance()->processarExcecao($e);
} 

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema().' - '.$strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
?>

#lblNomeSeriePesquisa {position:absolute;left:0%;top:0%;width:30%;}
#txtNomeSeriePesquisa {position:absolute;left:0%;top:40%;width:30%;}

#lblGrupoSerie {position:absolute;left:32%;top:0%;width:30%;}
#selGrupoSerie {position:absolute;left:32%;top:40%;width:30%;}

#lblModeloPesquisa {position:absolute;left:63%;top:0%;width:35%;}
#selModeloPesquisa {position:absolute;left:63%;top:40%;width:35%;}


<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){
  if ('<?=$_GET['acao']?>'=='serie_selecionar'){
    infraReceberSelecao();
    document.getElementById('btnFecharSelecao').focus();
  }else{
    document.getElementById('btnFechar').focus();
  }
  
  infraEfeitoTabelas();
}

<? if ($bolAcaoDesativar){ ?>
function acaoDesativar(id,desc){
  if (confirm("Confirma desativação do Tipo de Documento \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmSerieLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmSerieLista').submit();
  }
}

function acaoDesativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Tipo de Documento selecionado.');
    return;
  }
  if (confirm("Confirma desativação dos Tipos de Documento selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmSerieLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmSerieLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoReativar){ ?>
function acaoReativar(id,desc){
  if (confirm("Confirma reativação do Tipo de Documento \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmSerieLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmSerieLista').submit();
  }
}

function acaoReativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Tipo de Documento selecionado.');
    return;
  }
  if (confirm("Confirma reativação dos Tipos de Documento selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmSerieLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmSerieLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoExcluir){ ?>
function acaoExcluir(id,desc){
  if (confirm("Confirma exclusão do Tipo de Documento \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmSerieLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmSerieLista').submit();
  }
}

function acaoExclusaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Tipo de Documento selecionado.');
    return;
  }
  if (confirm("Confirma exclusão dos Tipos de Documento selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmSerieLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmSerieLista').submit();
  }
}
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmSerieLista" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?
  //PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->abrirAreaDados('4.5em');
  ?>

  <label id="lblNomeSeriePesquisa" for="txtNomeSeriePesquisa" accesskey="" class="infraLabelOpcional">Nome:</label>
  <input type="text" id="txtNomeSeriePesquisa" name="txtNomeSeriePesquisa" value="<?=$strNomeSeriePesquisa?>" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />

  <label id="lblGrupoSerie" for="selGrupoSerie" accesskey="" class="infraLabelOpcional">Grupo:</label>
  <select id="selGrupoSerie" name="selGrupoSerie" onchange="this.form.submit();" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" >
  <?=$strItensSelGrupoSerie?>
  </select>

  <label id="lblModeloPesquisa" for="selModeloPesquisa" accesskey="" class="infraLabelOpcional">Modelo:</label>
  <select id="selModeloPesquisa" name="selModeloPesquisa" onchange="this.form.submit();" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" >
  <?=$strItensSelModelo?>
  </select>

  <?
  PaginaSEI::getInstance()->fecharAreaDados();
  PaginaSEI::getInstance()->montarAreaTabela($strResultado,$numRegistros);
  PaginaSEI::getInstance()->montarAreaDebug();
  PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>