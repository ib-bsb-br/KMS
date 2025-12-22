<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 05/11/2010 - criado por jonatas_db
*
* Versão do Gerador de Código: 1.30.0
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

  PaginaSEI::getInstance()->prepararSelecao('acompanhamento_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);
  
  PaginaSEI::getInstance()->salvarCamposPost(array('selGrupoAcompanhamento'));

  switch($_GET['acao']){
    case 'acompanhamento_excluir':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjAcompanhamentoDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objAcompanhamentoDTO = new AcompanhamentoDTO();
          $objAcompanhamentoDTO->setNumIdAcompanhamento($arrStrIds[$i]);
          $arrObjAcompanhamentoDTO[] = $objAcompanhamentoDTO;
        }
        $objAcompanhamentoRN = new AcompanhamentoRN();
        $objAcompanhamentoRN->excluir($arrObjAcompanhamentoDTO);
        PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;


    case 'acompanhamento_selecionar':
      $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Acompanhamento Especial','Selecionar Acompanhamentos Especiais');

      //Se cadastrou alguem
      if ($_GET['acao_origem']=='acompanhamento_cadastrar'){
        if (isset($_GET['id_acompanhamento'])){
          PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_acompanhamento']);
        }
      }
      break;

    case 'acompanhamento_listar':
      $strTitulo = 'Acompanhamento Especial';
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $arrComandos = array();
  if ($_GET['acao'] == 'acompanhamento_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
  }

  /* if ($_GET['acao'] == 'acompanhamento_listar' || $_GET['acao'] == 'acompanhamento_selecionar'){ */
    $bolAcaoGrupos = true;
    if ($bolAcaoGrupos){
      $arrComandos[] = '<button type="button" accesskey="G" id="btnGrupo" value="Grupos" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=grupo_acompanhamento_listar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">G</span>rupos</button>';
    }
  /* } */
    
	    

  $objAcompanhamentoDTO = new AcompanhamentoDTO();
  
  $numIdGrupoAcompanhamento = PaginaSEI::getInstance()->recuperarCampo('selGrupoAcompanhamento');
  if ($numIdGrupoAcompanhamento!='null' && $numIdGrupoAcompanhamento!=''){
		$objAcompanhamentoDTO->setNumIdGrupoAcompanhamento($numIdGrupoAcompanhamento);  		
  }  
  
  PaginaSEI::getInstance()->prepararOrdenacao($objAcompanhamentoDTO, 'IdProtocolo', InfraDTO::$TIPO_ORDENACAO_DESC);
  PaginaSEI::getInstance()->prepararPaginacao($objAcompanhamentoDTO);

  $objAcompanhamentoRN = new AcompanhamentoRN();
  $arrObjAcompanhamentoDTO = $objAcompanhamentoRN->listarAcompanhamentosUnidade($objAcompanhamentoDTO);

  PaginaSEI::getInstance()->processarPaginacao($objAcompanhamentoDTO);
  $numRegistros = count($arrObjAcompanhamentoDTO);

  if ($numRegistros > 0){

    $arrRetIconeIntegracao = null;

    if (count($SEI_MODULOS)) {

      $arrObjProcedimentoAPI = array();
      foreach($arrObjAcompanhamentoDTO as $objAcompanhamentoDTO){

        $objProcedimentoDTO = $objAcompanhamentoDTO->getObjProcedimentoDTO();

        $dto = new ProcedimentoAPI();
        $dto->setIdProcedimento($objProcedimentoDTO->getDblIdProcedimento());
        $dto->setNumeroProtocolo($objProcedimentoDTO->getStrProtocoloProcedimentoFormatado());
        $dto->setIdTipoProcedimento($objProcedimentoDTO->getNumIdTipoProcedimento());
        $dto->setNomeTipoProcedimento($objProcedimentoDTO->getStrNomeTipoProcedimento());
        $dto->setNivelAcesso($objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo());
        $dto->setIdUnidadeGeradora($objProcedimentoDTO->getNumIdUnidadeGeradoraProtocolo());
        $dto->setIdOrgaoUnidadeGeradora($objProcedimentoDTO->getNumIdOrgaoUnidadeGeradoraProtocolo());
        $dto->setIdHipoteseLegal($objProcedimentoDTO->getNumIdHipoteseLegalProtocolo());
        $dto->setGrauSigilo($objProcedimentoDTO->getStrStaGrauSigiloProtocolo());

        $arrObjProcedimentoAPI[] = $dto;
      }

      foreach ($SEI_MODULOS as $seiModulo) {
        if (($arrRetIconeIntegracaoModulo = $seiModulo->executar('montarIconeAcompanhamentoEspecial', $arrObjProcedimentoAPI))!=null){
          foreach($arrRetIconeIntegracaoModulo as $dblIdProcedimento => $arrIcone){
            if (!isset($arrRetIconeIntegracao[$dblIdProcedimento])){
              $arrRetIconeIntegracao[$dblIdProcedimento] = $arrIcone;
            }else{
              $arrRetIconeIntegracao[$dblIdProcedimento] = array_merge($arrRetIconeIntegracao[$dblIdProcedimento], $arrIcone);
            }
          }
        }
      }

    }

    if ($_GET['acao']=='acompanhamento_selecionar'){
      $bolAcaoReativar = false;
      $bolAcaoConsultar = false; //SessaoSEI::getInstance()->verificarPermissao('acompanhamento_consultar');
      $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('acompanhamento_alterar');
      $bolAcaoImprimir = false;
      $bolAcaoExcluir = false;
      $bolAcaoDesativar = false;
      $bolAcaoRegistrarAnotacao = false;
      $bolAcaoAndamentoSituacaoGerenciar = false;
      $bolAcaoAndamentoMarcadorGerenciar = false;
    }else{
      $bolAcaoReativar = false;
      $bolAcaoConsultar = false; //SessaoSEI::getInstance()->verificarPermissao('acompanhamento_consultar');
      $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('acompanhamento_alterar');
      $bolAcaoImprimir = true;
      $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('acompanhamento_excluir');
      $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('acompanhamento_desativar');
      $bolAcaoRegistrarAnotacao = SessaoSEI::getInstance()->verificarPermissao('anotacao_registrar');
      $bolAcaoAndamentoSituacaoGerenciar = SessaoSEI::getInstance()->verificarPermissao('andamento_situacao_gerenciar');
      $bolAcaoAndamentoMarcadorGerenciar = SessaoSEI::getInstance()->verificarPermissao('andamento_marcador_gerenciar');
    }

    if ($bolAcaoExcluir){
      $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
      $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=acompanhamento_excluir&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoImprimir){
      $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
    }

    $strResultado = '';

    /* if ($_GET['acao']!='acompanhamento_reativar'){ */
      $strSumarioTabela = 'Tabela de Acompanhamentos.';
      $strCaptionTabela = 'Acompanhamentos';
    /* }else{
      $strSumarioTabela = 'Tabela de Acompanhamentos Inativos.';
      $strCaptionTabela = 'Acompanhamentos Inativos';
    } */

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    $strResultado .= '<th class="infraTh" width="1%">'.PaginaSEI::getInstance()->getThCheck('', 'Infra', '', false).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="6%">&nbsp;</th>'."\n";
    $strResultado .= '<th class="infraTh" width="20%">'.PaginaSEI::getInstance()->getThOrdenacao($objAcompanhamentoDTO,'Processo','IdProtocolo',$arrObjAcompanhamentoDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="10%">'.PaginaSEI::getInstance()->getThOrdenacao($objAcompanhamentoDTO,'Usuário','IdUsuarioGerador',$arrObjAcompanhamentoDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="10%">'.PaginaSEI::getInstance()->getThOrdenacao($objAcompanhamentoDTO,'Data','Geracao',$arrObjAcompanhamentoDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="10%">'.PaginaSEI::getInstance()->getThOrdenacao($objAcompanhamentoDTO,'Grupo','NomeGrupo',$arrObjAcompanhamentoDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThOrdenacao($objAcompanhamentoDTO,'Observação','Observacao',$arrObjAcompanhamentoDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="10%">Ações</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistros; $i++){

      $objProcedimentoDTO = $arrObjAcompanhamentoDTO[$i]->getObjProcedimentoDTO();

      $strCssTr = ($strCssTr=='class="infraTrClara"')?'class="infraTrEscura"':'class="infraTrClara"';
      $strResultado .= '<tr '.$strCssTr.'>';

      $strResultado .= '<td valign="top" class="tdAcompanhamento">'.PaginaSEI::getInstance()->getTrCheck($i,$arrObjAcompanhamentoDTO[$i]->getNumIdAcompanhamento(),$objProcedimentoDTO->getStrProtocoloProcedimentoFormatado(),'N','Infra','',false ).'</td>';
      $strResultado .= '<td align="center" valign="top" class="tdAcompanhamento">';
      $strResultado .= AnotacaoINT::montarIconeAnotacao($objProcedimentoDTO->getObjAnotacaoDTO(),$bolAcaoRegistrarAnotacao,$arrObjAcompanhamentoDTO[$i]->getDblIdProtocolo(),'&id_acompanhamento='.$arrObjAcompanhamentoDTO[$i]->getNumIdAcompanhamento());
      $strResultado .= ProcedimentoINT::montarIconeVisualizacao($arrObjAcompanhamentoDTO[$i]->getNumTipoVisualizacao(), $objProcedimentoDTO, $arrRetIconeIntegracao,$bolAcaoAndamentoSituacaoGerenciar,$bolAcaoAndamentoMarcadorGerenciar,'&id_acompanhamento='.$arrObjAcompanhamentoDTO[$i]->getNumIdAcompanhamento());
      $strResultado .= '</td>';
      $strResultado .= '<td align="center" valign="top" class=""><a onclick="infraLimparFormatarTrAcessada(this.parentNode.parentNode);" href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_procedimento='.$arrObjAcompanhamentoDTO[$i]->getDblIdProtocolo()).'" target="_blank" class="protocoloNormal" title="'.PaginaSEI::tratarHTML($objProcedimentoDTO->getStrNomeTipoProcedimento()).'">'.$objProcedimentoDTO->getStrProtocoloProcedimentoFormatado().'</a></td>';
      $strResultado .= '<td align="center" valign="top" class="tdAcompanhamento"><a alt="'.PaginaSEI::tratarHTML($arrObjAcompanhamentoDTO[$i]->getStrNomeUsuario()).'" title="'.PaginaSEI::tratarHTML($arrObjAcompanhamentoDTO[$i]->getStrNomeUsuario()).'" class="ancoraSigla">'.PaginaSEI::tratarHTML($arrObjAcompanhamentoDTO[$i]->getStrSiglaUsuario()).'</a></td>';
      $strResultado .= '<td align="center" valign="top" class="tdAcompanhamento">'.$arrObjAcompanhamentoDTO[$i]->getDthGeracao().'</td>';
      $strResultado .= '<td align="center" valign="top" class="tdAcompanhamento">'.PaginaSEI::tratarHTML($arrObjAcompanhamentoDTO[$i]->getStrNomeGrupo()).'</td>';

      $strResultado .= '<td valign="top" class="tdAcompanhamento">';
      $strObservacao = PaginaSEI::tratarHTML($arrObjAcompanhamentoDTO[$i]->getStrObservacao());
      $strObservacao = str_replace('&lt;b&gt;','<b>', $strObservacao);
      $strObservacao = str_replace('&lt;/b&gt;','</b>', $strObservacao);
      $strResultado .= $strObservacao;
      $strResultado .= '</td>';
      
      
      $strResultado .= '<td align="center" valign="top" class="tdAcompanhamento tdAcompanhamentoUltima">';

      $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i,$arrObjAcompanhamentoDTO[$i]->getNumIdAcompanhamento());

      if ($bolAcaoConsultar){
        $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=acompanhamento_consultar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_acompanhamento='.$arrObjAcompanhamentoDTO[$i]->getNumIdAcompanhamento()).'" ><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/consultar.gif" title="Consultar Acompanhamento" alt="Consultar Acompanhamento" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoAlterar){
        $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=acompanhamento_alterar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_acompanhamento='.$arrObjAcompanhamentoDTO[$i]->getNumIdAcompanhamento()).'" ><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/alterar.gif" title="Alterar Acompanhamento" alt="Alterar Acompanhamento" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoDesativar || $bolAcaoReativar || $bolAcaoExcluir){
        $strId = $arrObjAcompanhamentoDTO[$i]->getNumIdAcompanhamento();
        $strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript($arrObjAcompanhamentoDTO[$i]->getStrObservacao());
      }
/* 
      if ($bolAcaoDesativar){
        $strResultado .= '<a href="'.PaginaSEI::getInstance()->montarAncora($strId).'" onclick="acaoDesativar(\''.$strId.'\',\''.$strDescricao.'\');" ><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/desativar.gif" title="Desativar Acompanhamento" alt="Desativar Acompanhamento" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoReativar){
        $strResultado .= '<a href="'.PaginaSEI::getInstance()->montarAncora($strId).'" onclick="acaoReativar(\''.$strId.'\',\''.$strDescricao.'\');" ><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/reativar.gif" title="Reativar Acompanhamento" alt="Reativar Acompanhamento" class="infraImg" /></a>&nbsp;';
      }
 */

      if ($bolAcaoExcluir){
        $strResultado .= '<a href="'.PaginaSEI::getInstance()->montarAncora($strId).'" onclick="acaoExcluir(\''.$strId.'\',\''.$strDescricao.'\');" ><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/excluir.gif" title="Excluir Acompanhamento" alt="Excluir Acompanhamento" class="infraImg" /></a>&nbsp;';
      }

      $strResultado .= '</td></tr>'."\n";
    }
    $strResultado .= '</table>';
  }
  if ($_GET['acao'] == 'acompanhamento_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }else{
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }

	$strItensSelGrupoAcompanhamento = GrupoAcompanhamentoINT::montarSelectIdGrupoAcompanhamentoRI0012('null','Todos', $numIdGrupoAcompanhamento, SessaoSEI::getInstance()->getNumIdUnidadeAtual());  
  
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

  table.infraTable {
  background-color:white;
  border:0;
  border-spacing:0;
  border-bottom: 1px solid #c0c0c0;
  }

  .tdAcompanhamento{
    border-left:1px solid #c0c0c0;
  }

  .tdAcompanhamentoUltima{
    border-right:1px solid #c0c0c0;
  }

  #divInfraAreaTabela > table > tbody > tr > th:first-child{
    border-left:1px solid #c0c0c0;
  }

  #divInfraAreaTabela > table > tbody > tr > th:last-child  {
    border-right:1px solid #c0c0c0;
  }

#lblSelGrupoAcompanhamento {position:absolute;left:0%;top:0%;}
#selGrupoAcompanhamento {position:absolute;left:0%;top:40%;width:50%;}
<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){

  //infraOcultarMenuSistemaEsquema();

  if ('<?=$_GET['acao']?>'=='acompanhamento_selecionar'){
    infraReceberSelecao();
    document.getElementById('btnFecharSelecao').focus();
  }else{
    document.getElementById('btnFechar').focus();
  }
  infraEfeitoTabelas();
}

<? if ($bolAcaoDesativar){ ?>
function acaoDesativar(id,desc){
  if (confirm("Confirma desativação do Acompanhamento Especial no processo \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmAcompanhamentoLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmAcompanhamentoLista').submit();
  }
}

function acaoDesativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Acompanhamento selecionado.');
    return;
  }
  if (confirm("Confirma desativação dos Acompanhamentos selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmAcompanhamentoLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmAcompanhamentoLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoReativar){ ?>
function acaoReativar(id,desc){
  if (confirm("Confirma reativação do Acompanhamento Especial no processo \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmAcompanhamentoLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmAcompanhamentoLista').submit();
  }
}

function acaoReativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Acompanhamento selecionado.');
    return;
  }
  if (confirm("Confirma reativação dos Acompanhamentos selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmAcompanhamentoLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmAcompanhamentoLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoExcluir){ ?>
function acaoExcluir(id,desc){
  if (confirm("Confirma exclusão do Acompanhamento Especial no processo \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmAcompanhamentoLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmAcompanhamentoLista').submit();
  }
}

function acaoExclusaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Acompanhamento selecionado.');
    return;
  }
  if (confirm("Confirma exclusão dos Acompanhamentos selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmAcompanhamentoLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmAcompanhamentoLista').submit();
  }
}
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmAcompanhamentoLista" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?php   
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->abrirAreaDados('5em');
  ?>
  <label id="lblSelGrupoAcompanhamento" for="selGrupoAcompanhamento" accesskey="G" class="infraLabel"><span class="infraTeclaAtalho">G</span>rupo:</label>
  <select id="selGrupoAcompanhamento" name="selGrupoAcompanhamento" onchange="this.form.submit();" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" >
  <?=$strItensSelGrupoAcompanhamento?>
  </select>
  <?php   
  PaginaSEI::getInstance()->fecharAreaDados();
  PaginaSEI::getInstance()->montarAreaTabela($strResultado,$numRegistros);
  PaginaSEI::getInstance()->montarAreaDebug();
  //PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>