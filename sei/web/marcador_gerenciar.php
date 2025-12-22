<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 12/11/2015 - criado por mga
 *
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

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $numIdSituacao = null;

  $strDesabilitar = '';

  SessaoSEI::getInstance()->setArrParametrosRepasseLink(array('arvore','id_procedimento','id_acompanhamento','id_usuario_atribuicao'));

  if(isset($_GET['arvore'])){
    PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
  }

  $bolMultiplo = false;

  $arrComandos = array();

  $objAndamentoMarcadorRN = new AndamentoMarcadorRN();

  switch ($_GET['acao']) {
    case 'andamento_marcador_gerenciar':
      $strTitulo = 'Gerenciar Marcador';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmGerenciarMarcador" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';

      $objAndamentoMarcadorDTO = new AndamentoMarcadorDTO();

      if (isset($_GET['id_procedimento'])) {
        $arrIdProtocolo = array($_GET['id_procedimento']);
      } else if ($_GET['acao_origem'] == 'procedimento_controlar') {
        $arrItensControleProcesso = array_merge(PaginaSEI::getInstance()->getArrStrItensSelecionados('Gerados'), PaginaSEI::getInstance()->getArrStrItensSelecionados('Recebidos'), PaginaSEI::getInstance()->getArrStrItensSelecionados('Detalhado'));
        $arrIdProtocolo = $arrItensControleProcesso;
      } else {
        $arrIdProtocolo = explode(',',$_POST['hdnIdProtocolo']);
      }

      if ($_GET['id_acompanhamento']!='') {
        $strAncora = $_GET['id_acompanhamento'];
      }else{
        $strAncora = $arrIdProtocolo;
      }

      if (SessaoSEI::getInstance()->verificarPermissao('marcador_cadastrar')){
        $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="cadastrarMarcador();" class="infraButton" style="width:5em;"><span class="infraTeclaAtalho">N</span>ovo</button>';
        $strLinkCadastrarMarcador = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=marcador_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']);
      }

      if (!PaginaSEI::getInstance()->isBolArvore()) {
        $arrComandos[] = '<button type="button" accesskey="V" name="btnVoltar" id="btnVoltar" value="Voltar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao']) . PaginaSEI::getInstance()->montarAncora($strAncora) . '\';" class="infraButton"><span class="infraTeclaAtalho">V</span>oltar</button>';
      }

      if (count($arrIdProtocolo)>1){
        $bolMultiplo = true;
      }

      $objAndamentoMarcadorDTO->setDblIdProcedimento($arrIdProtocolo);
      $objAndamentoMarcadorDTO->setNumIdMarcador($_POST['hdnIdMarcador']);
      $objAndamentoMarcadorDTO->setStrTexto($_POST['txaTexto']);

      if (isset($_POST['sbmGerenciarMarcador'])) {

        try{
          $ret = $objAndamentoMarcadorRN->gerenciar($objAndamentoMarcadorDTO);
          //PaginaSEI::getInstance()->adicionarMensagem('Marcador "'.$objRelProcedSituacaoUnidadeDTO->getNumIdSituacao().'" definido com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'&atualizar_arvore=1'.PaginaSEI::getInstance()->montarAncora($strAncora)));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }

      break;

    default:
      throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
  }

  if ($_GET['acao_origem']=='andamento_marcador_gerenciar') {
    $numIdMarcador = $_POST['hdnIdMarcador'];
    $strTextoMarcador = $_POST['txaTexto'];
  }else if ($_GET['acao_origem']=='marcador_cadastrar') {
    $numIdMarcador = $_GET['id_marcador'];
    $strTextoMarcador = '';
  }else{
    $objAndamentoMarcadorDTO = new AndamentoMarcadorDTO();
    $objAndamentoMarcadorDTO->setDistinct(true);
    $objAndamentoMarcadorDTO->retNumIdMarcador();
    $objAndamentoMarcadorDTO->retStrTexto();
    $objAndamentoMarcadorDTO->setDblIdProcedimento($arrIdProtocolo,InfraDTO::$OPER_IN);
    $objAndamentoMarcadorDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
    $objAndamentoMarcadorDTO->setStrSinUltimo('S');
    $arrObjAndamentoMarcadorDTO = $objAndamentoMarcadorRN->listar($objAndamentoMarcadorDTO);

    if (count($arrObjAndamentoMarcadorDTO) == 1) {
      $numIdMarcador = $arrObjAndamentoMarcadorDTO[0]->getNumIdMarcador();
      $strTextoMarcador = $arrObjAndamentoMarcadorDTO[0]->getStrTexto();
    }
  }

  $strResultado = '';
  $numRegistrosAndamento = 0;

  if (!$bolMultiplo) {

    $objProcedimentoDTO = new ProcedimentoDTO();
    $objProcedimentoDTO->setDblIdProcedimento($arrIdProtocolo[0]);
    $objProcedimentoDTO->retNumIdTipoProcedimento();

    $objProcedimentoRN = new ProcedimentoRN();
    $objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);

    if ($objProcedimentoDTO == null) {
      throw new InfraException("Processo não encontrado.");
    }


    $objAndamentoMarcadorDTO = new AndamentoMarcadorDTO();
    $objAndamentoMarcadorDTO->retNumIdMarcador();
    $objAndamentoMarcadorDTO->retStrNomeMarcador();
    $objAndamentoMarcadorDTO->retStrSinAtivoMarcador();
    $objAndamentoMarcadorDTO->retStrTexto();
    $objAndamentoMarcadorDTO->retDthExecucao();
    $objAndamentoMarcadorDTO->retNumIdUsuario();
    $objAndamentoMarcadorDTO->retStrSiglaUsuario();
    $objAndamentoMarcadorDTO->retStrNomeUsuario();
    $objAndamentoMarcadorDTO->retNumIdAndamentoMarcador();
    $objAndamentoMarcadorDTO->setDblIdProcedimento($arrIdProtocolo[0]);
    $objAndamentoMarcadorDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
    $objAndamentoMarcadorDTO->setOrdNumIdAndamentoMarcador(InfraDTO::$TIPO_ORDENACAO_DESC);

    PaginaSEI::getInstance()->prepararPaginacao($objAndamentoMarcadorDTO, 100);

    $objAndamentoMarcadorRN = new AndamentoMarcadorRN();
    $arrObjAndamentoMarcadorDTO = $objAndamentoMarcadorRN->listar($objAndamentoMarcadorDTO);

    PaginaSEI::getInstance()->processarPaginacao($objAndamentoMarcadorDTO);

    $numRegistrosAndamento = count($arrObjAndamentoMarcadorDTO);

    if ($numRegistrosAndamento > 0) {

      $bolCheck = false;

      $strResultado = '';

      $strResultado .= '<table id="tblHistorico" width="99%" class="infraTable" summary="Histórico de Marcadores">' . "\n";
      $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela('Histórico de Marcadores', $numRegistrosAndamento, '') . '</caption>';
      $strResultado .= '<tr>';
      $strResultado .= '<th class="infraTh" width="15%">Data/Hora</th>';
      //$strResultado .= '<th class="infraTh" width="15%">Unidade</th>';
      $strResultado .= '<th class="infraTh" width="10%">Usuário</th>';
      $strResultado .= '<th class="infraTh" width="25%">Marcador</th>';
      $strResultado .= '<th class="infraTh">Texto</th>';
      $strResultado .= '</tr>' . "\n";

      $strQuebraLinha = '<span style="line-height:.5em"><br /></span>';

      foreach ($arrObjAndamentoMarcadorDTO as $objAndamentoMarcadorDTO) {

        $strResultado .= '<tr class="infraTrClara">';
        $strResultado .= '<td align="center" valign="top">'.substr($objAndamentoMarcadorDTO->getDthExecucao(), 0, 16).'</td>'."\n";
        /*
        $strResultado .= "\n".'<td align="center"  valign="top">';
        $strResultado .= '<a alt="'.$objAndamentoMarcadorDTO->getStrDescricaoUnidade().'" title="'.$objAndamentoMarcadorDTO->getStrDescricaoUnidade().'" class="ancoraSigla">'.$objAndamentoMarcadorDTO->getStrSiglaUnidade().'</a>';
        $strResultado .= '</td>';
        */
        $strResultado .= '<td align="center"  valign="top">';
        $strResultado .= '<a alt="' . PaginaSEI::tratarHTML($objAndamentoMarcadorDTO->getStrNomeUsuario()) . '" title="' . PaginaSEI::tratarHTML($objAndamentoMarcadorDTO->getStrNomeUsuario()) . '" class="ancoraSigla">' . PaginaSEI::tratarHTML($objAndamentoMarcadorDTO->getStrSiglaUsuario()) . '</a>';
        $strResultado .= '</td>';
        $strResultado .= '<td align="center" valign="top">';

        if ($objAndamentoMarcadorDTO->getNumIdMarcador()!=null) {
          $strResultado .= PaginaSEI::tratarHTML(MarcadorINT::formatarMarcadorDesativado($objAndamentoMarcadorDTO->getStrNomeMarcador(),$objAndamentoMarcadorDTO->getStrSinAtivoMarcador()));
        }else{
          $strResultado .= '[REMOVIDO]';
        }

        $strResultado .= '</td>'."\n";
        $strResultado .= '<td valign="top">'.PaginaSEI::tratarHTML($objAndamentoMarcadorDTO->getStrTexto()).'</td>'."\n";
        $strResultado .= '</tr>';
      }
      $strResultado .= '</table>';
    }
  }

  $strItensSelMarcador = MarcadorINT::montarSelectMarcador('null','&nbsp;',$numIdMarcador);

  $strLinkAjaxMarcadores = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=marcador_montar_opcoes');

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

#divDados {height:13em;overflow:visible !important;}

#lblMarcador {position:absolute;left:0%;top:0%;}
#selMarcador {position:absolute;left:0%;top:14%;}

#lblTexto {position:absolute;left:0%;top:36%;width:95%;}
#txaTexto {position:absolute;left:0%;top:49%;width:95%;}

#tblHistorico td{
  padding:.2em;
}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
//<script type="javascript">

var objAjaxMarcadores = null;

function inicializar(){

  <?if(isset($_GET['id_marcador'])){?>
    document.getElementById('txaTexto').focus();
  <?}else{?>
    document.getElementById('selMarcador').focus();
  <?}?>


  $('#selMarcador').ddslick({width: 400,
     onSelected: function(data){
       if(data.selectedIndex > 0) {
         document.getElementById('hdnIdMarcador').value = data.selectedData.value;
       }else{
         document.getElementById('hdnIdMarcador').value = '';
         document.getElementById('txaTexto').innerHTML = '';
       }
     }
  });

  objAjaxMarcadores = new infraAjaxComplementar(null,'<?=$strLinkAjaxMarcadores?>');
  objAjaxMarcadores.limparCampo = false;
  objAjaxMarcadores.mostrarAviso = false;
  objAjaxMarcadores.tempoAviso = 1000;

  objAjaxMarcadores.prepararExecucao = function(){
    return infraAjaxMontarPostPadraoSelect('null','',document.getElementById('hdnIdMarcador').value);
  };

  objAjaxMarcadores.processarResultado = function(arr){

    $('#selMarcador').ddslick('destroy');

    var base64=new infraBase64();
    document.getElementById('selMarcador').innerHTML = base64.decodificar(arr['marcadores']);

    $('#selMarcador').ddslick({width: 400,
      onSelected: function(data){
        if(data.selectedIndex > 0) {
          document.getElementById('hdnIdMarcador').value = data.selectedData.value;
        }else{
          document.getElementById('hdnIdMarcador').value = '';
          document.getElementById('txaTexto').innerHTML = '';
        }
      }
    });

  };

  infraEfeitoTabelas();
}

function validarCadastro() {
  return true;
}

function OnSubmitForm() {
  return validarCadastro();
}

function cadastrarMarcador(){
  infraAbrirJanela('<?=$strLinkCadastrarMarcador?>','janelaMarcador',700,450,'location=0,status=1,resizable=1,scrollbars=1');
}

function recarregarMarcadores(idMarcador){
  document.getElementById('hdnIdMarcador').value = idMarcador;
  objAjaxMarcadores.executar();
}

//</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
  <form id="frmGerenciarMarcador" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
    <?
    PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
    //PaginaSEI::getInstance()->montarAreaValidacao();
    ?>
    <div id="divDados" class="infraAreaDados">

      <label id="lblMarcador" for="selMarcador" accesskey="" class="infraLabelOpcional">Marcador:</label>
      <select id="selMarcador" name="selMarcador" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
        <?=$strItensSelMarcador?>
      </select>

      <label id="lblTexto" for="txaTexto" class="infraLabelOpcional">Texto:</label>
      <textarea id="txaTexto" name="txaTexto" rows="<?=PaginaSEI::getInstance()->isBolNavegadorFirefox()?'3':'4'?>" onkeypress="return infraLimitarTexto(this,event,250);" class="infraTextarea" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"><?=PaginaSEI::tratarHTML($strTextoMarcador);?></textarea>
      
      <input type="hidden" id="hdnIdMarcador" name="hdnIdMarcador" value="<?=$numIdMarcador?>" />
      <input type="hidden" id="hdnIdProtocolo" name="hdnIdProtocolo" value="<?=implode(',',$arrIdProtocolo)?>" />
    </div>
    <?
    if (!$bolMultiplo) {
      PaginaSEI::getInstance()->montarAreaTabela($strResultado, $numRegistrosAndamento);
    }
    PaginaSEI::getInstance()->montarAreaDebug();
    //PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
    ?>
  </form>

<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>