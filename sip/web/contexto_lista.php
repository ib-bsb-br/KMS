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
	
  switch($_GET['acao']){
    case 'contexto_excluir':
		  try{
        $arrObjContextoDTO = array();
        $arrStrId = PaginaSip::getInstance()->getArrStrItensSelecionados();
        for ($i=0;$i<count($arrStrId);$i++){
          $objContextoDTO = new ContextoDTO();
          $objContextoDTO->setNumIdContexto($arrStrId[$i]);
          $arrObjContextoDTO[] = $objContextoDTO;
        }
        $objContextoRN = new ContextoRN();
        $objContextoRN->excluir($arrObjContextoDTO);
			}catch(Exception $e){
				PaginaSip::getInstance()->processarExcecao($e);
			}
      break;

    case 'contexto_desativar':
		  try{

        $arrObjContextoDTO = array();
        $arrStrId = PaginaSip::getInstance()->getArrStrItensSelecionados();
        for ($i=0;$i<count($arrStrId);$i++){
          $objContextoDTO = new ContextoDTO();
          $objContextoDTO->setNumIdContexto($arrStrId[$i]);
          $arrObjContextoDTO[] = $objContextoDTO;
        }
        $objContextoRN = new ContextoRN();
        $objContextoRN->desativar($arrObjContextoDTO);

			}catch(Exception $e){
				PaginaSip::getInstance()->processarExcecao($e);
			}
      break;

    case 'contexto_reativar':
      $strTitulo = 'Reativar Contextos';
	    if ($_GET['acao_confirmada']=='sim'){
	      try{

          $arrObjContextoDTO = array();
          $arrStrId = PaginaSip::getInstance()->getArrStrItensSelecionados();
          for ($i=0;$i<count($arrStrId);$i++){
            $objContextoDTO = new ContextoDTO();
            $objContextoDTO->setNumIdContexto($arrStrId[$i]);
            $arrObjContextoDTO[] = $objContextoDTO;
          }
          $objContextoRN = new ContextoRN();
          $objContextoRN->reativar($arrObjContextoDTO);

  			}catch(Exception $e){
  				PaginaSip::getInstance()->processarExcecao($e);
  			}
  			header('Location: '.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
        die;
		  }	
		  break;
      
    case 'contexto_listar':
      $strTitulo = 'Contextos';
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $arrComandos = array();
  if (SessaoSip::getInstance()->verificarPermissao('contexto_cadastrar')){
    $arrComandos[] = '<input type="button" id="btnNovo" value="Novo" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao=contexto_cadastrar').'\';" class="infraButton" />';
  }
  $objContextoDTO = new ContextoDTO(true);
  $objContextoDTO->retTodos();
	
	$numIdOrgao = PaginaSip::getInstance()->recuperarCampo('selOrgao');
	if ($numIdOrgao!==''){
		$objContextoDTO->setNumIdOrgao($numIdOrgao);
	}
	
  if ($_GET['acao'] == 'contexto_reativar'){
    //Lista somente inativos
    $objContextoDTO->setBolExclusaoLogica(false);
    $objContextoDTO->setStrSinAtivo('N');
  }
	
	
  PaginaSip::getInstance()->prepararOrdenacao($objContextoDTO, 'Nome', InfraDTO::$TIPO_ORDENACAO_ASC);
  $objContextoRN = new ContextoRN();
  $arrObjContextoDTO = $objContextoRN->listar($objContextoDTO);

  $numRegistros = count($arrObjContextoDTO);

  if ($numRegistros > 0){
    
    
    if ($_GET['acao']=='contexto_reativar'){
      $bolAcaoConsultar = SessaoSip::getInstance()->verificarPermissao('contexto_consultar');
      $bolAcaoAlterar = SessaoSip::getInstance()->verificarPermissao('contexto_alterar');
      $bolAcaoExcluir = SessaoSip::getInstance()->verificarPermissao('contexto_excluir');
      $bolAcaoDesativar = false;
      $bolAcaoReativar = SessaoSip::getInstance()->verificarPermissao('contexto_reativar');
    }else{
      $bolAcaoConsultar = SessaoSip::getInstance()->verificarPermissao('contexto_consultar');
      $bolAcaoAlterar = SessaoSip::getInstance()->verificarPermissao('contexto_alterar');
      $bolAcaoExcluir = SessaoSip::getInstance()->verificarPermissao('contexto_excluir');
      $bolAcaoDesativar = SessaoSip::getInstance()->verificarPermissao('contexto_desativar');
      $bolAcaoReativar = false;
    } 
    
    

    //Montar ações múltiplas
    $bolCheck = false;
    if ($bolAcaoExcluir){
      $bolCheck = true;
      //$arrComandos[] = '<input type="button" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton" />';
      $strLinkExcluir = SessaoSip::getInstance()->assinarLink('contexto_lista.php?acao=contexto_excluir&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']);
    }

    if ($bolAcaoDesativar){
      $bolCheck = true;
      //$arrComandos[] = '<input type="button" id="btnDesativar" value="Desativar" onclick="acaoDesativacaoMultipla();" class="infraButton" />';
      $strLinkDesativar = SessaoSip::getInstance()->assinarLink('contexto_lista.php?acao=contexto_desativar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']);
    }
    
    
    if ($bolAcaoReativar){
      //$bolCheck = true;
      //$arrComandos[] = '<button type="button" accesskey="R" id="btnReativar" value="Reativar" onclick="acaoReativacaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">R</span>eativar</button>';
      $strLinkReativar = SessaoSip::getInstance()->assinarLink('controlador.php?acao=contexto_reativar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&acao_confirmada=sim');
    }
    

 		$arrComandos[] = '<input type="button" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton" />';
    
		$strResultado = '';
    if ($_GET['acao']!='contexto_reativar'){
      $strSumarioTabela = 'Tabela de Contextos.';
      $strCaptionTabela = 'Contextos';
    }else{
      $strSumarioTabela = 'Tabela de Contextos Inativos.';
      $strCaptionTabela = 'Contextos Inativos';
    }

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSip::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
 		
    $strResultado .= '<tr>';
    if ($bolCheck) {
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaSip::getInstance()->getThCheck().'</th>';
    }
    $strResultado .= '<th class="infraTh">'.PaginaSip::getInstance()->getThOrdenacao($objContextoDTO,'Nome','Nome',$arrObjContextoDTO).'</th>';
    $strResultado .= '<th class="infraTh">'.PaginaSip::getInstance()->getThOrdenacao($objContextoDTO,'Base DN LDAP','BaseDnLdap',$arrObjContextoDTO).'</th>';
    $strResultado .= '<th class="infraTh">'.PaginaSip::getInstance()->getThOrdenacao($objContextoDTO,'Órgão','SiglaOrgao',$arrObjContextoDTO).'</th>';
    $strResultado .= '<th class="infraTh" width="20%">Ações</th>';
    $strResultado .= '</tr>'."\n";
    for($i = 0;$i < $numRegistros; $i++){
      if ( ($i+2) % 2 ) {
        $strResultado .= '<tr class="infraTrEscura">';
      } else {
        $strResultado .= '<tr class="infraTrClara">';
      }
      if ($bolCheck){
        $strResultado .= '<td valign="top">'.PaginaSip::getInstance()->getTrCheck($i,$arrObjContextoDTO[$i]->getNumIdContexto(),$arrObjContextoDTO[$i]->getStrNome()).'</td>';
      }
      $strResultado .= '<td>'.PaginaSip::tratarHTML($arrObjContextoDTO[$i]->getStrNome()).'</td>';
      $strResultado .= '<td>'.PaginaSip::tratarHTML($arrObjContextoDTO[$i]->getStrBaseDnLdap()).'</td>';
      $strResultado .= '<td align="center"><a alt="'.PaginaSip::tratarHTML($arrObjContextoDTO[$i]->getStrDescricaoOrgao()).'" title="'.PaginaSip::tratarHTML($arrObjContextoDTO[$i]->getStrDescricaoOrgao()).'" class="ancoraSigla">'.PaginaSip::tratarHTML($arrObjContextoDTO[$i]->getStrSiglaOrgao()).'</a></td>';
      $strResultado .= '<td align="center">';
      if ($bolAcaoConsultar){
        $strResultado .= '<a href="'.SessaoSip::getInstance()->assinarLink('controlador.php?acao=contexto_consultar&acao_retorno='.$_GET['acao'].'&id_contexto='.$arrObjContextoDTO[$i]->getNumIdContexto()).'" tabindex="'.PaginaSip::getInstance()->getProxTabDados().'"><img src="imagens/consultar.gif" title="Consultar Contexto" alt="Consultar Contexto" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoDesativar || $bolAcaoReativar || $bolAcaoExcluir){
        $strId = $arrObjContextoDTO[$i]->getNumIdContexto();
        $strDescricao = PaginaSip::formatarParametrosJavaScript($arrObjContextoDTO[$i]->getStrNome());
      }

      if ($bolAcaoAlterar){
        $strResultado .= '<a href="'.SessaoSip::getInstance()->assinarLink('controlador.php?acao=contexto_alterar&acao_retorno='.$_GET['acao'].'&id_contexto='.$arrObjContextoDTO[$i]->getNumIdContexto()).'" tabindex="'.PaginaSip::getInstance()->getProxTabDados().'"><img src="imagens/alterar.gif" title="Alterar Contexto" alt="Alterar Contexto" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoDesativar){
        $strResultado .= '<a onclick="acaoDesativar(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSip::getInstance()->getProxTabDados().'"><img src="imagens/desativar.gif" title="Desativar Contexto" alt="Desativar Contexto" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoReativar){
        $strResultado .= '<a onclick="acaoReativar(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSip::getInstance()->getProxTabDados().'"><img src="imagens/reativar.gif" title="Reativar Contexto" alt="Reativar Contexto" class="infraImg" /></a>&nbsp;';
      }
      
      if ($bolAcaoExcluir){
        $strResultado .= '<a onclick="acaoExcluir(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSip::getInstance()->getProxTabDados().'"><img src="imagens/excluir.gif" title="Excluir Contexto" alt="Excluir Contexto" class="infraImg" /></a>&nbsp;';
      }
      
      $strResultado .= '</td></tr>'."\n";
    }
    $strResultado .= '</table>';
  }
  $arrComandos[] = '<input type="button" id="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'\'" class="infraButton" />';
 
  $strItensSelOrgao = OrgaoINT::montarSelectSiglaTodos('','Todos',$numIdOrgao);
}catch(Exception $e){
  PaginaSip::getInstance()->processarExcecao($e);
} 

PaginaSip::getInstance()->montarDocType();
PaginaSip::getInstance()->abrirHtml();
PaginaSip::getInstance()->abrirHead();
PaginaSip::getInstance()->montarMeta();
PaginaSip::getInstance()->montarTitle(PaginaSip::getInstance()->getStrNomeSistema().' - Contextos');
PaginaSip::getInstance()->montarStyle();
PaginaSip::getInstance()->abrirStyle();
?>
#lblOrgao {position:absolute;left:0%;top:0%;width:20%;}
#selOrgao {position:absolute;left:0%;top:40%;width:20%;}

<?
PaginaSip::getInstance()->fecharStyle();
PaginaSip::getInstance()->montarJavaScript();
PaginaSip::getInstance()->abrirJavaScript();
?>

function inicializar(){
  if ('<?=$_GET['acao']?>'=='contexto_selecionar'){
    infraReceberSelecao();
  }
  infraEfeitoTabelas();
}

<? if ($bolAcaoExcluir){ ?>
     function acaoExcluir(id,desc){
       if (confirm("Confirma exclusão do Contexto \""+desc+"\"?")){
         document.getElementById('hdnInfraItensSelecionados').value=id;
         document.getElementById('frmContextoLista').action='<?=$strLinkExcluir?>';
         document.getElementById('frmContextoLista').submit();
       }
     }

     function acaoExclusaoMultipla(){
       if (document.getElementById('hdnInfraItensSelecionados').value==''){
         alert('Nenhum Contexto selecionado.');
         return;
       }
       if (confirm("Confirma exclusão dos Contextos selecionados?")){
         document.getElementById('frmContextoLista').action='<?=$strLinkExcluir?>';
         document.getElementById('frmContextoLista').submit();
       }
     }
<? } ?>

<? if ($bolAcaoDesativar){ ?>
     function acaoDesativar(id,desc){
       if (confirm("Confirma desativação do Contexto \""+desc+"\"?")){
         document.getElementById('hdnInfraItensSelecionados').value=id;
         document.getElementById('frmContextoLista').action='<?=$strLinkDesativar?>';
         document.getElementById('frmContextoLista').submit();
       }
     }

     function acaoDesativacaoMultipla(){
       if (document.getElementById('hdnInfraItensSelecionados').value==''){
         alert('Nenhum Contexto selecionado.');
         return;
       }
       if (confirm("Confirma desativação dos Contextos selecionados?")){
         document.getElementById('frmContextoLista').action='<?=$strLinkDesativar?>';
         document.getElementById('frmContextoLista').submit();
       }
     }
<? } ?>

<? if ($bolAcaoReativar){ ?>
     function acaoReativar(id,desc){
       if (confirm("Confirma reativação do Contexto \""+desc+"\"?")){
         document.getElementById('hdnInfraItensSelecionados').value=id;
         document.getElementById('frmContextoLista').action='<?=$strLinkReativar?>';
         document.getElementById('frmContextoLista').submit();
       }
     }

     function acaoDesativacaoMultipla(){
       if (document.getElementById('hdnInfraItensSelecionados').value==''){
         alert('Nenhum Contexto selecionado.');
         return;
       }
       if (confirm("Confirma reativação dos Contextos selecionados?")){
         document.getElementById('frmContextoLista').action='<?=$strLinkReativar?>';
         document.getElementById('frmContextoLista').submit();
       }
     }
<? } ?>

<?
PaginaSip::getInstance()->fecharJavaScript();
PaginaSip::getInstance()->fecharHead();
PaginaSip::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmContextoLista" method="post" action="<?=SessaoSip::getInstance()->assinarLink(basename(__FILE__).'?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?
  //PaginaSip::getInstance()->montarBarraLocalizacao('Contextos');
  PaginaSip::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSip::getInstance()->abrirAreaDados('5em');
  ?>
  <label id="lblOrgao" for="selOrgao" accesskey="o" class="infraLabelOpcional">Órgã<span class="infraTeclaAtalho">o</span>:</label>
  <select id="selOrgao" name="selOrgao" onchange="this.form.submit();" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" >
  <?=$strItensSelOrgao?>
  </select>

  <?
  PaginaSip::getInstance()->fecharAreaDados();
  PaginaSip::getInstance()->montarAreaTabela($strResultado,$numRegistros,true);
  //PaginaSip::getInstance()->montarAreaDebug();
  PaginaSip::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSip::getInstance()->fecharBody();
PaginaSip::getInstance()->fecharHtml();
?>