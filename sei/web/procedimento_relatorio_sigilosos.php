<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 09/01/2008 - criado por marcio_db
*
* Versão do Gerador de Código: 1.12.0
*
* Versão no CVS: $Id$
*/

try {
  require_once dirname(__FILE__).'/SEI.php';

  session_start();

  //////////////////////////////////////////////////////////////////////////////
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(true);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  PaginaSEI::getInstance()->salvarCamposPost(array('selOrgao'));
  
  switch($_GET['acao']){

    case 'procedimento_relatorio_sigilosos':
      $strTitulo = 'Inventário de Processos Sigilosos sem Credencial Ativa';
      
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $arrComandos = array();  
  $arrComandos[] = '<button type="button" accesskey="P" id="btnPesquisar" name="btnPesquisar" onclick="pesquisar();" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';          	
  
  $objProcedimentoRelatorioSigilososDTO = new ProcedimentoRelatorioSigilososDTO();

  $objProcedimentoRelatorioSigilososDTO->setNumIdOrgao($_POST['selOrgao']);

  $numRegistros = 0;
  
  if ($_GET['acao_origem']=='procedimento_relatorio_sigilosos'){
  
    PaginaSEI::getInstance()->prepararOrdenacao($objProcedimentoRelatorioSigilososDTO, 'Abertura', InfraDTO::$TIPO_ORDENACAO_DESC);
    
    //PaginaSEI::getInstance()->prepararPaginacao($objProcedimentoRelatorioSigilososDTO);
    
    $objProcedimentoRN = new ProcedimentoRN();
    $arrObjProcedimentoRelatorioSigilososDTO = $objProcedimentoRN->relatorioSigilosos($objProcedimentoRelatorioSigilososDTO);
    
    //PaginaSEI::getInstance()->processarPaginacao($objProcedimentoRelatorioSigilososDTO);
    
    $numRegistros = count($arrObjProcedimentoRelatorioSigilososDTO);
  }

  if ($numRegistros >0){

    
    $bolAcaoImprimir = true;

    if ($bolAcaoImprimir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
    }
    

    $strResultado = '';
    
    $strSumarioTabela = 'Tabela de Processos.';
    $strCaptionTabela = 'Processos Sigilosos';

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    if ($bolCheck) {
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
    }
    $strResultado .= '<th class="infraTh" width="16%">'.PaginaSEI::getInstance()->getThOrdenacao($objProcedimentoRelatorioSigilososDTO,'Processo','IdProtocolo',$arrObjProcedimentoRelatorioSigilososDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" >'.PaginaSEI::getInstance()->getThOrdenacao($objProcedimentoRelatorioSigilososDTO,'Tipo','IdentificacaoProtocolo',$arrObjProcedimentoRelatorioSigilososDTO).'</th>'."\n";  
    $strResultado .= '<th class="infraTh" width="16%">'.PaginaSEI::getInstance()->getThOrdenacao($objProcedimentoRelatorioSigilososDTO,'Usuário','NomeUsuario',$arrObjProcedimentoRelatorioSigilososDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="16%">'.PaginaSEI::getInstance()->getThOrdenacao($objProcedimentoRelatorioSigilososDTO,'Unidade','DescricaoUnidade',$arrObjProcedimentoRelatorioSigilososDTO).'</th>'."\n";
    //$strResultado .= '<th class="infraTh" width="16%">'.PaginaSEI::getInstance()->getThOrdenacao($objProcedimentoRelatorioSigilososDTO,'Última Movimentação','Abertura',$arrObjProcedimentoRelatorioSigilososDTO).'</th>'."\n";
    //$strResultado .= '<th class="infraTh" width="10%">Ações</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistros; $i++){

      $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      $strResultado .= $strCssTr;

      if ($bolCheck){
        $strResultado .= '<td valign="top">'.PaginaSEI::getInstance()->getTrCheck($i,$arrObjProcedimentoRelatorioSigilososDTO[$i]->getDblIdProtocolo(),$arrObjProcedimentoRelatorioSigilososDTO[$i]->getStrProtocoloFormatadoProtocolo()).'</td>';
      }
      
      
      $strResultado .= '<td align="center"><a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_procedimento='.$arrObjProcedimentoRelatorioSigilososDTO[$i]->getDblIdProtocolo()).'" target="_blank" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'" alt="'.PaginaSEI::tratarHTML($arrObjProcedimentoRelatorioSigilososDTO[$i]->getStrIdentificacaoProtocolo()).'" title="'.PaginaSEI::tratarHTML($arrObjProcedimentoRelatorioSigilososDTO[$i]->getStrIdentificacaoProtocolo()).'" class="protocoloNormal">'.PaginaSEI::tratarHTML($arrObjProcedimentoRelatorioSigilososDTO[$i]->getStrProtocoloFormatadoProtocolo()).'</a></td>';
      $strResultado .= '<td align="center">'.PaginaSEI::tratarHTML($arrObjProcedimentoRelatorioSigilososDTO[$i]->getStrIdentificacaoProtocolo()).'</td>';
      $strResultado .= '<td align="center"><a alt="'.PaginaSEI::tratarHTML($arrObjProcedimentoRelatorioSigilososDTO[$i]->getStrNomeUsuario()).'" title="'.PaginaSEI::tratarHTML($arrObjProcedimentoRelatorioSigilososDTO[$i]->getStrNomeUsuario()).'" class="ancoraSigla">'.PaginaSEI::tratarHTML($arrObjProcedimentoRelatorioSigilososDTO[$i]->getStrSiglaUsuario()).'</a></td>';
      $strResultado .= '<td align="center"><a alt="'.PaginaSEI::tratarHTML($arrObjProcedimentoRelatorioSigilososDTO[$i]->getStrDescricaoUnidade()).'" title="'.PaginaSEI::tratarHTML($arrObjProcedimentoRelatorioSigilososDTO[$i]->getStrDescricaoUnidade()).'" class="ancoraSigla">'.PaginaSEI::tratarHTML($arrObjProcedimentoRelatorioSigilososDTO[$i]->getStrSiglaUnidade()).'</a></td>';
     // $strResultado .= '<td align="center">'.$arrObjProcedimentoRelatorioSigilososDTO[$i]->getDthAbertura().'</td>';
      //$strResultado .= '<td align="center"></td>';
      
      $strResultado .= '</tr>'."\n";
    }
    $strResultado .= '</table>';
  }
  $strItensSelOrgao = OrgaoINT::montarSelectSiglaRI1358('', 'Todos', $objProcedimentoRelatorioSigilososDTO->getNumIdOrgao());      	 
  
  
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
#lblOrgao {position:absolute;left:0%;top:0%;width:25%;}
#selOrgao {position:absolute;left:0%;top:40%;width:25%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
function inicializar(){
  infraEfeitoTabelas();
}

function pesquisar(){
  if (onSubmitForm()){
    document.getElementById('frmProcedimentoRelatorioSigilosos').submit();
  }
}

function onSubmitForm(){
  infraExibirAviso(true);
  return true;
}

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmProcedimentoRelatorioSigilosos" onsubmit="return onSubmitForm();" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?
  //PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->abrirAreaDados('5em');
  ?>
    <label id="lblOrgao" for="selOrgao" accesskey="r" class="infraLabelOpcional">Ó<span class="infraTeclaAtalho">r</span>gão:</label>
    <select id="selOrgao" name="selOrgao" onchange="pesquisar();" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
    <?=$strItensSelOrgao?>
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