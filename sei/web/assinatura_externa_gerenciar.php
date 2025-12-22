<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 13/07/2011 - criado por mga
 *
 * Versão do Gerador de Código: 1.13.1
 *
 * Versão no CVS: $Id$
 */

try {
  require_once dirname(__FILE__) . '/SEI.php';

  session_start();

  //////////////////////////////////////////////////////////////////////////////
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(true);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  $strParametros = '';
  if (isset($_GET['arvore'])) {
      PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
      $strParametros .= '&arvore=' . $_GET['arvore'];
  }

  if (isset($_GET['id_procedimento'])) {
      $strParametros .= '&id_procedimento=' . $_GET['id_procedimento'];
  }

  if (isset($_GET['id_documento'])) {
      $strParametros .= '&id_documento=' . $_GET['id_documento'];
  }

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $arrComandos = array();


  switch ($_GET['acao']) {

      case 'assinatura_externa_liberar':

          $strTitulo = 'Liberação de Assinatura Externa';

          try {

              $objAcessoExternoDTO = new AcessoExternoDTO();
              $objAcessoExternoDTO->setStrStaTipo(AcessoExternoRN::$TA_ASSINATURA_EXTERNA);
              $objAcessoExternoDTO->setStrEmailUnidade($_POST['selEmailUnidade']);
              $objAcessoExternoDTO->setDblIdProtocoloAtividade($_GET['id_procedimento']);
              $objAcessoExternoDTO->setDblIdDocumento($_GET['id_documento']);
              $objAcessoExternoDTO->setNumIdUsuarioExterno($_POST['hdnIdUsuario']);
              $objAcessoExternoDTO->setStrSinProcesso(PaginaSEI::getInstance()->getCheckbox($_POST['chkSinProcesso']));

              $arr = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnProtocolos']);

              $arrObjRelAcessoExtProtocoloDTO = array();
              foreach($arr as $dblIdProtocolo){
                $objRelAcessoExtProtocoloDTO = new RelAcessoExtProtocoloDTO();
                $objRelAcessoExtProtocoloDTO->setDblIdProtocolo($dblIdProtocolo);
                $arrObjRelAcessoExtProtocoloDTO[] = $objRelAcessoExtProtocoloDTO;
              }
              $objAcessoExternoDTO->setArrObjRelAcessoExtProtocoloDTO($arrObjRelAcessoExtProtocoloDTO);
            
            
              $objAcessoExternoRN = new AcessoExternoRN();
              $ret = $objAcessoExternoRN->cadastrar($objAcessoExternoDTO);

              PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');

              header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=assinatura_externa_gerenciar&acao_origem=' . $_GET['acao'] . $strParametros . PaginaSEI::getInstance()->montarAncora($ret->getNumIdAtividade())));
              die;

          } catch (Exception $e) {
              PaginaSEI::getInstance()->processarExcecao($e);
          }

          break;

      case 'assinatura_externa_gerenciar':
          $strTitulo = 'Gerenciar Assinaturas Externas';
          break;

      default:
          throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
  }


  $arrComandos = array();


  $objAcessoExternoDTO = new AcessoExternoDTO();
  $objAcessoExternoDTO->setDblIdDocumento($_GET['id_documento']);

  $objAcessoExternoRN = new AcessoExternoRN();
  $arrObjAcessoExternoDTO = $objAcessoExternoRN->listarLiberacoesAssinaturaExterna($objAcessoExternoDTO);

  $numRegistros = count($arrObjAcessoExternoDTO);

  $bolAcaoLiberar = SessaoSEI::getInstance()->verificarPermissao('assinatura_externa_liberar');
  $bolAcaoCancelarLiberacao = SessaoSEI::getInstance()->verificarPermissao('assinatura_externa_cancelar');

  if ($bolAcaoLiberar) {
      $strLinkLiberar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=assinatura_externa_liberar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . $strParametros);
  }

  if ($numRegistros > 0) {

      if ($bolAcaoCancelarLiberacao) {
          //$arrComandos[] = '<button type="submit" accesskey="a" name="sbmCancelarLiberacao" id="sbmCancelarLiberacao" onclick="acaoCassacaoMultipla();" value="Cancelar Liberação" class="infraButton">C<span class="infraTeclaAtalho">a</span>ssar</button>';
          $strLinkCancelarLiberacao = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=assinatura_externa_cancelar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . $strParametros);
      }

      //$arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';

      $strResultado = '';

      $strSumarioTabela = 'Tabela de Liberações de Assinaturas Externas.';
      $strCaptionTabela = 'Liberações de Assinatura Externa';

      $strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n"; //90
      $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
      $strResultado .= '<tr>';
      $strResultado .= '<th class="infraTh" width="1%" style="display:none;">' . PaginaSEI::getInstance()->getThCheck('', 'Infra', 'style="display:none;"') . '</th>' . "\n";
      $strResultado .= '<th class="infraTh" >Usuário</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="10%">Visualização Processo</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="10%">Unidade</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="17%">Liberação</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="17%">Utilização</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="17%">Cancelamento</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="10%">Ações</th>' . "\n";
      //$strResultado .= '<th class="infraTh">Ações</th>'."\n";
      $strResultado .= '</tr>' . "\n";
      $strCssTr = '';

      $n = 0;
      foreach ($arrObjAcessoExternoDTO as $objAcessoExternoDTO) {

          $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
          $strResultado .= $strCssTr;

          $strResultado .= "\n" . '<td valign="top" style="display:none;">';
          $strResultado .= PaginaSEI::getInstance()->getTrCheck($n++, $objAcessoExternoDTO->getNumIdAcessoExterno(), $objAcessoExternoDTO->getStrSiglaContato() . '/' . $objAcessoExternoDTO->getStrSiglaUnidade(), 'N', 'Infra', 'style="visibility:hidden;"');
          $strResultado .= '</td>';

          $strResultado .= "\n" . '<td align="center"  valign="top">';
          $strResultado .= '<a alt="' . PaginaSEI::tratarHTML($objAcessoExternoDTO->getStrNomeContato()) . '" title="' . PaginaSEI::tratarHTML($objAcessoExternoDTO->getStrNomeContato()) . '" class="ancoraSigla">' . PaginaSEI::tratarHTML($objAcessoExternoDTO->getStrSiglaContato()) . '</a>';
          $strResultado .= '</td>';

          $strResultado .= "\n" . '<td align="center"  valign="top">';
          if ($objAcessoExternoDTO->getStrSinProcesso() == 'S') {
              $strResultado .= 'Sim';
          } else {
              $strResultado .= 'Não';
          }
          $strResultado .= '</td>';

          $strResultado .= "\n" . '<td align="center"  valign="top">';
          $strResultado .= '<a alt="' . PaginaSEI::tratarHTML($objAcessoExternoDTO->getStrDescricaoUnidade()) . '" title="' . PaginaSEI::tratarHTML($objAcessoExternoDTO->getStrDescricaoUnidade()) . '" class="ancoraSigla">' . PaginaSEI::tratarHTML($objAcessoExternoDTO->getStrSiglaUnidade()) . '</a>';
          $strResultado .= '</td>' . "\n";

          $strResultado .= '<td align="center" valign="top">' . substr($objAcessoExternoDTO->getDthAberturaAtividade(), 0, 16) . '</td>' . "\n";

          $strResultado .= '<td align="center" valign="top">';
          if ($objAcessoExternoDTO->getDthUtilizacao() != null) {
              $strResultado .= substr($objAcessoExternoDTO->getDthUtilizacao(), 0, 16);
          } else {
              $strResultado .= '&nbsp;';
          }
          $strResultado .= '</td>' . "\n";

          $strResultado .= '<td align="center" valign="top">';
          if ($objAcessoExternoDTO->getDthCancelamento() != null) {
              $strResultado .= substr($objAcessoExternoDTO->getDthCancelamento(), 0, 16);
          } else {
              $strResultado .= '&nbsp;';
          }
          $strResultado .= '</td>' . "\n";

          $strResultado .= '<td align="center" valign="top">';

          $strDetalhes = '';
          $strOnClick = '';
          $arrObjRelAcessoExtProtocoloDTO = $objAcessoExternoDTO->getArrObjRelAcessoExtProtocoloDTO();

          if (count($arrObjRelAcessoExtProtocoloDTO) == 0){
            if ($objAcessoExternoDTO->getStrSinProcesso()=='S') {
              $strDetalhes = 'Visualização integral do processo';
            }else{
              $strDetalhes = 'Sem acesso ao processo';
            }

          }else{
            $strDetalhes = 'Visualização parcial do processo (clique aqui para ver os protocolos disponibilizados)';
            $strOnClick = 'onclick="infraLimparFormatarTrAcessada(this.parentNode.parentNode);visualizarDetalhes(\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=acesso_externo_protocolo_detalhe&acao_origem='.$_GET['acao'].'&id_acesso_externo='.$objAcessoExternoDTO->getNumIdAcessoExterno().'&id_procedimento='.$objAcessoExternoDTO->getDblIdProtocoloAtividade()).'\')"';
          }

          $strResultado .= '<a href="javascript:void(0)" '.$strOnClick.' '.PaginaSEI::montarTitleTooltip($strDetalhes) . '><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/consultar.gif" class="infraImg" /></a>'."\n";


          if ($bolAcaoCancelarLiberacao && $objAcessoExternoDTO->getStrSinAtivo() == 'S' && ($objAcessoExternoDTO->getDthUtilizacao()==null || $objAcessoExternoDTO->getStrSinProcesso() == 'S')) {
              $strResultado .= '<a href="#ID-' . $objAcessoExternoDTO->getNumIdAcessoExterno() . '"  onclick="acaoCancelarLiberacao(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=assinatura_externa_cancelar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . $strParametros . '&id_acesso_externo=' . $objAcessoExternoDTO->getNumIdAcessoExterno()) . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioImagensGlobal() . '/remover.gif" title="Cancelar Liberação de Assinatura Externa" alt="Cancelar Liberação de Assinatura Externa" class="infraImg" /></a>&nbsp;';
          } else {
              $strResultado .= '<span style="line-height:1.5em">&nbsp;</span>';
          }
          $strResultado .= '</td>';


          $strResultado .= '</tr>' . "\n";
      }
      $strResultado .= '</table>';
  }

  //$arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'])).'\'" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

  $strItensSelEmailUnidade = EmailUnidadeINT::montarSelectEmail('null', '&nbsp;', $_POST['selEmailUnidade']);

  $strLinkAjaxUsuario = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=usuario_externo_auto_completar');

} catch (Exception $e) {
    PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
?>
#lblEmailUnidade {position:absolute;left:0%;top:0%;}
#selEmailUnidade {position:absolute;left:0%;top:20%;width:50%;}

#lblUsuario {position:absolute;left:0%;top:50%;}
#txtUsuario {position:absolute;left:0%;top:70%;width:50%}

#divSinProcesso {position:absolute;left:55%;top:70%;}

#lblProtocolos {position:absolute;left:0%;top:0%;}
#selProtocolos {position:absolute;left:0%;top:18%;width:92%;}
#divOpcoesProtocolos {position:absolute;left:93%;top:20%;}

#btnLiberar {position:absolute;left:0%;top:0%;}
<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

var objAutoCompletarUsuario = null;
var objLupaProtocolos = null;

function inicializar(){

  objAutoCompletarUsuario = new infraAjaxAutoCompletar('hdnIdUsuario','txtUsuario','<?= $strLinkAjaxUsuario ?>');
  objAutoCompletarUsuario.limparCampo = true;

  objAutoCompletarUsuario.prepararExecucao = function(){
    return 'palavras_pesquisa='+document.getElementById('txtUsuario').value;
  };

  objLupaProtocolos	= new infraLupaSelect('selProtocolos','hdnProtocolos','<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao=acesso_externo_protocolo_selecionar&tipo_selecao=2&id_object=objLupaProtocolos&id_procedimento='.$_GET['id_procedimento'].'&id_documento=' . $_GET['id_documento'])?>');

<? if ($_GET['acao'] == 'assinatura_externa_liberar') { ?>
    objAutoCompletarUsuario.selecionar('<?= $_POST['hdnIdUsuario'] ?>','<?= $_POST['txtUsuario'] ?>');
<? } ?>

  document.getElementById('selEmailUnidade').focus();

  infraEfeitoTabelas();

  trocarVisualizacaoProcesso();
}

<? if ($bolAcaoLiberar) { ?>

function liberar(){

  if (document.getElementById('selEmailUnidade').value == 'null' || document.getElementById('selEmailUnidade').value == '') {
    alert('E-mail da unidade não informado.');
    document.getElementById('selEmailUnidade').focus();
    return;
  }

  if (infraTrim(document.getElementById('hdnIdUsuario').value)==''){
    alert('Informe um Usuário Externo.');
    document.getElementById('txtUsuario').focus();
    return;
  }
  document.getElementById('frmGerenciarAssinaturaExterna').target = '_self';
  document.getElementById('frmGerenciarAssinaturaExterna').action = '<?= $strLinkLiberar ?>';
  document.getElementById('frmGerenciarAssinaturaExterna').submit();
}

<? } ?>

<? if ($bolAcaoCancelarLiberacao) { ?>
function acaoCancelarLiberacao(link){
  infraAbrirJanela(link,'janelaCancelarAssinaturaExterna',600,250,'location=0,status=1,resizable=1,scrollbars=1');
}

function acaoCancelamentoLiberacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhuma liberação de assinatura externa selecionada.');
    return;
  }
  acaoCancelarLiberacao(null);
}
<? } ?>

function visualizarDetalhes(link){
  infraAbrirJanela(link,'janelaDetalhesAssinaturaExterna',700,400,'location=0,status=1,resizable=1,scrollbars=1');
}

function OnSubmitForm() {
  return true;
}

function trocarVisualizacaoProcesso(){
  if (document.getElementById('chkSinProcesso').checked){
    document.getElementById('divRestricao').style.display = 'none';
    document.getElementById('selProtocolos').options.length = 0;
  }else{
    document.getElementById('divRestricao').style.display = '';
  }
}

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmGerenciarAssinaturaExterna" method="post" onsubmit="return OnSubmitForm();"
      action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'] . $strParametros) ?>">
    <?
    //PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
    PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
    //PaginaSEI::getInstance()->montarAreaValidacao();
    ?>

    <div id="divGeral" class="infraAreaDados" style="height:10em;">
      <label id="lblEmailUnidade" for="selEmailUnidade" accesskey="" class="infraLabelObrigatorio">E-mail da Unidade:</label>
      <select id="selEmailUnidade" name="selEmailUnidade" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
          <?= $strItensSelEmailUnidade ?>
      </select>

      <label id="lblUsuario" for="selUsuario" class="infraLabelObrigatorio">Liberar Assinatura Externa para:</label>
      <input type="text" id="txtUsuario" name="txtUsuario" class="infraText" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
      <input type="hidden" id="hdnIdUsuario" name="hdnIdUsuario" class="infraText" value=""/>

      <div id="divSinProcesso" class="infraDivCheckbox">
        <input type="checkbox" id="chkSinProcesso" name="chkSinProcesso" onchange="trocarVisualizacaoProcesso()" class="infraCheckbox" <?= PaginaSEI::getInstance()->setCheckbox(PaginaSEI::getInstance()->getCheckbox($_POST['chkSinProcesso']))?> tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
        <label id="lblSinProcesso" for="chkSinProcesso" accesskey="" class="infraLabelCheckbox">Com visualização integral do processo</label>
      </div>
    </div>

    <div id="divRestricao" class="infraAreaDados" style="height:11em;">
      <label id="lblProtocolos" for="selProtocolos" class="infraLabelOpcional">Protocolos adicionais disponibilizados para consulta (clique na lupa para selecionar):</label>
      <select id="selProtocolos" name="selProtocolos" size="5" class="infraSelect" ></select>
      <div id="divOpcoesProtocolos">
        <img id="imgLupaProtocolos" onclick="objLupaProtocolos.selecionar(700,500);" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/lupa.gif" alt="Selecionar Protocolos" title="Selecionar Protocolos" class="infraImg"  />
        <br />
        <img id="imgExcluirProtocolos" onclick="objLupaProtocolos.remover();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/remover.gif" alt="Remover Protocolos Selecionados" title="Remover Protocolos Selecionados" class="infraImgNormal"  />
      </div>
      <input type="hidden" id="hdnProtocolos" name="hdnProtocolos" value="<?=$_POST['hdnProtocolos']?>" />
    </div>

    <div id="divBotao" class="infraAreaDados" style="height:2em;">
      <button type="button" name="btnLiberar" id="btnLiberar" onclick="liberar();" accesskey="L" value="Liberar" class="infraButton">&nbsp;&nbsp;<span class="infraTeclaAtalho">L</span>iberar&nbsp;&nbsp;</button>
    </div>
    <br />
    <?
    PaginaSEI::getInstance()->montarAreaTabela($strResultado, $numRegistros);
    PaginaSEI::getInstance()->montarAreaDebug();
    PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
    ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>