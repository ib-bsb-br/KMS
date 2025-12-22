<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 31/01/2008 - criado por marcio_db
*
* Versão do Gerador de Código: 1.13.1
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

  $strParametros = '';
  if(isset($_GET['arvore'])){
    PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
    $strParametros .= '&arvore='.$_GET['arvore'];
  }
  
  if (isset($_GET['id_procedimento'])){
    $strParametros .= '&id_procedimento='.$_GET['id_procedimento'];
  }

  if (isset($_GET['id_documento_assinado'])){
    $strParametros .= '&id_documento_assinado='.$_GET['id_documento_assinado'];
  }

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);
  
  $arrComandos = array();
  
  switch($_GET['acao']){
    
    case 'procedimento_enviar':
    	
    	$strVisualizar = 'style="visibility:hidden;"';
      $strTitulo = 'Enviar Processo';
      
      $objEnviarProcessoDTO = new EnviarProcessoDTO();

  	  $arrProtocolosOrigem = array();
  	  $arrAtividadesOrigem = array();
      
      if ($_GET['acao_origem']=='arvore_visualizar' || $_GET['acao_origem']=='procedimento_controlar'){
            
      	if ($_GET['acao_origem']=='arvore_visualizar'){
          $arrProtocolosOrigem[] = $_GET['id_procedimento'];
      	}else{
          $arrProtocolosOrigem = array_merge(PaginaSEI::getInstance()->getArrStrItensSelecionados('Gerados'),PaginaSEI::getInstance()->getArrStrItensSelecionados('Recebidos'),PaginaSEI::getInstance()->getArrStrItensSelecionados('Detalhado'));

          if (count($arrProtocolosOrigem)==0){
            throw new InfraException('Nenhum processo selecionado.');
          }
          
      	}

      	$objAtividadeRN = new AtividadeRN();
      	
        $objPesquisaPendenciaDTO = new PesquisaPendenciaDTO();
        $objPesquisaPendenciaDTO->setDblIdProtocolo($arrProtocolosOrigem);
        $objPesquisaPendenciaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
        $objPesquisaPendenciaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $arrObjProcedimentoDTO = $objAtividadeRN->listarPendenciasRN0754($objPesquisaPendenciaDTO);

        $arrObjAtividadeDTO = array();
        foreach($arrObjProcedimentoDTO as $objProcedimentoDTO){
          $arrObjAtividadeDTO = array_merge($arrObjAtividadeDTO,$objProcedimentoDTO->getArrObjAtividadeDTO()); 
        }
        
        $arrAtividadesOrigem = InfraArray::converterArrInfraDTO($arrObjAtividadeDTO,'IdAtividade');
        
     	}else {
     	  
     	  if ($_POST['hdnIdProtocolos']!=''){
     	    $arrProtocolosOrigem = explode(',',$_POST['hdnIdProtocolos']);
     	  }
     	  
     	  if ($_POST['hdnIdAtividades']!=''){
     	  $arrAtividadesOrigem = explode(',',$_POST['hdnIdAtividades']);
     	  }
     	  
     	}

     	$objAtividadeRN = new AtividadeRN();

     	//Monta atividades de origem uma atividade para cada recebida
      $arrObjAtividadeDTOOrigem = array();
      foreach($arrAtividadesOrigem as $numIdAtividade){
      	$objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->setNumIdAtividade($numIdAtividade);
        $arrObjAtividadeDTOOrigem[] = $objAtividadeDTO;         
      }
      $objEnviarProcessoDTO->setArrAtividadesOrigem($arrObjAtividadeDTOOrigem);
      
      //Monta atividades que serão lançadas
      //uma atividade por protocolo/unidade ou protocolo/usuario/unidade
      $arrObjAtividadeDTO = array(); 	      
     		
      $arrUnidades = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnUnidades']);

      foreach($arrProtocolosOrigem as $dblIdProtocolo){
      	foreach($arrUnidades as $numIdUnidade){
      		$objAtividadeDTO = new AtividadeDTO();
      		$objAtividadeDTO->setDblIdProtocolo($dblIdProtocolo);
      		$objAtividadeDTO->setNumIdUsuario(null);
      		$objAtividadeDTO->setNumIdUsuarioOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
      		$objAtividadeDTO->setNumIdUnidade($numIdUnidade);
      		$objAtividadeDTO->setNumIdUnidadeOrigem(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      		$arrObjAtividadeDTO[] = $objAtividadeDTO;
      	}
      }
     	
      $objEnviarProcessoDTO->setArrAtividades($arrObjAtividadeDTO);
      $objEnviarProcessoDTO->setStrSinManterAberto(PaginaSEI::getInstance()->getCheckbox($_POST['chkSinManterAberto']));
      $objEnviarProcessoDTO->setStrSinEnviarEmailNotificacao(PaginaSEI::getInstance()->getCheckbox($_POST['chkSinEnviarEmailNotificacao']));		        
      $objEnviarProcessoDTO->setStrSinRemoverAnotacoes(PaginaSEI::getInstance()->getCheckbox($_POST['chkSinRemoverAnotacoes']));
      $objEnviarProcessoDTO->setDtaPrazo($_POST['txtPrazo']);
      $objEnviarProcessoDTO->setNumDias($_POST['txtDias']);
      $objEnviarProcessoDTO->setStrSinDiasUteis(PaginaSEI::getInstance()->getCheckbox($_POST['chkSinDiasUteis']));

	    if (isset($_POST['sbmEnviar'])){
	      try{	      		        
	      	
	        $objAtividadeRN->enviarRN0023($objEnviarProcessoDTO);
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'&atualizar_arvore=1'.$strParametros));
          die;

	      }catch(Exception $e){
	        PaginaSEI::getInstance()->processarExcecao($e);
	      }
	    }
	    
      $arrComandos[] = '<button type="submit" accesskey="E" name="sbmEnviar" id="sbmEnviar" value="Enviar" class="infraButton" style="width:8%;"><span class="infraTeclaAtalho">E</span>nviar</button>';
      //$arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'&resultado=0'.$strParametros.PaginaSEI::getInstance()->montarAncora($arrProtocolosOrigem))).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

	    break;
	
	    default:
	      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $strLinkGrupo = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=grupo_unidade_selecionar&tipo_selecao=2&id_object=objLupaGrupo');
  $strLinkTramitacao = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_tramitacao_selecionar&tipo_selecao=2&id_object=objLupaGrupo&id_procedimento='.$arrProtocolosOrigem[0]);
  $strLinkAjaxUnidade = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=unidade_auto_completar_envio_processo');     	 
  $strLinkUnidadeSelecao = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_selecionar_envio_processo&tipo_selecao=2&id_object=objLupaUnidades');
  $strItensSelProcedimentos = ProcedimentoINT::conjuntoCompletoFormatadoRI0903($arrProtocolosOrigem);
  
 	$strIdProtocolos = implode(',',$arrProtocolosOrigem);
 	$strIdAtividades = implode(',',$arrAtividadesOrigem);

  $strLinkUnidadesTramitacao = '';
  if(count($arrProtocolosOrigem)==1){
    $strLinkUnidadesTramitacao = '<a id="ancUnidadesTramitacao" href="javascript:void(0);" onclick="selecionarTramitacao();" class="ancoraPadraoPreta">Mostrar unidades por onde tramitou</a>';
  }

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

#lblProcedimentos {position:absolute;left:0%;top:0%;}
#selProcedimentos {position:absolute;left:0%;top:21%;width:81%;}

#lblUnidades {position:absolute;left:0%;top:10%;}
#txtUnidade {position:absolute;left:0%;top:25%;width:50%;}
#ancUnidadesTramitacao {position:absolute;left:51%;top:27%;}
#selUnidades {position:absolute;left:0%;top:43%;width:86%;}
#divOpcoesUnidades {position:absolute;left:87%;top:43%;}

#divSinManterAberto {position:absolute;left:0%;top:5%;}
#divSinRemoverAnotacoes {position:absolute;left:0%;top:17%;}
#divSinEnviarEmailNotificacao {position:absolute;left:0%;top:29%;}

#fldPrazo {position:absolute;height:40%;left:0;top:47%;width:40%;}
#divOptDataCerta {position:absolute;left:5%;top:30%;}
#divOptDias {position:absolute;left:5%;top:65%;}

#txtPrazo {position:absolute;left:50%;top:30%;width:30%;}
#imgCalDataDecisao {position:absolute;left:83%;top:30%;}

#txtDias {position:absolute;left:50%;top:65%;width:15%;}
#divSinDiasUteis {position:absolute;left:68%;top:65%;width:25%}

<?
if (PaginaSEI::getInstance()->isBolNavegadorFirefox()){
?>

#divOptDataCerta {top:20%;}
#txtPrazo {top:20%;}
#imgCalDataDecisao {top:20%;}

#divOptDias {top:55%;}
#txtDias {top:55%;}
#divSinDiasUteis {top:55%;}

<?
}
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>


var objLupaUnidades = null;
var objAutoCompletarUnidade = null;
var objLupaGrupo = null;
var objLupaTramitacao = null;
var objAjaxVerificacaoAssinatura = null;

function inicializar(){
  
  objLupaUnidades = new infraLupaSelect('selUnidades','hdnUnidades','<?=$strLinkUnidadeSelecao?>');
	  
  objAutoCompletarUnidade = new infraAjaxAutoCompletar('hdnIdUnidade','txtUnidade','<?=$strLinkAjaxUnidade?>');
  //objAutoCompletarUnidade.maiusculas = true;
  //objAutoCompletarUnidade.mostrarAviso = true;
  //objAutoCompletarUnidade.tempoAviso = 1000;
  //objAutoCompletarUnidade.tamanhoMinimo = 3;
  objAutoCompletarUnidade.limparCampo = true;
  //objAutoCompletarUnidade.bolExecucaoAutomatica = false;

  objLupaGrupo = new infraLupaSelect('selUnidades','hdnUnidades','<?=$strLinkGrupo?>');
  objLupaTramitacao = new infraLupaSelect('selUnidades','hdnUnidades','<?=$strLinkTramitacao?>');

  objLupaGrupo.finalizarSelecao = function(){
    var arrUnidades=[];
    $('#selUnidades option').each(function(){
      var unidade=$(this).val();
      if (unidade!="") arrUnidades.push(unidade);
    });
    //$('#hdnDestinatario').val(arrUnidades.join(';'));

  };

  objAutoCompletarUnidade.prepararExecucao = function(){
    return 'palavras_pesquisa='+document.getElementById('txtUnidade').value;
  };
	  
  objAutoCompletarUnidade.processarResultado = function(id,descricao,complemento){
    if (id!=''){
      objLupaUnidades.adicionar(id,descricao,document.getElementById('txtUnidade'));
    }
  };

  configurarPrazo();

<? if (isset($_GET['id_documento_assinado'])){ ?>
  objAjaxVerificacaoAssinatura = new infraAjaxComplementar(null,'<?=SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=documento_verificar_assinatura&id_documento='.$_GET['id_documento_assinado'])?>');
  objAjaxVerificacaoAssinatura.async = false;
  objAjaxVerificacaoAssinatura.bolAssinado = false;
  objAjaxVerificacaoAssinatura.processarResultado = function(arr){
    if (arr!=null) {
      this.bolAssinado = false;
      if (arr['SinAssinado']!=undefined && arr['SinAssinado']=='S') {
        this.bolAssinado = true;
      }
    }
  };
<?}?>

  document.getElementById('txtUnidade').focus();
}
  function selecionarGrupo(){
    objLupaGrupo.selecionar(700,500);
  }
function selecionarTramitacao(){
  objLupaTramitacao.selecionar(700,500);
}

function validarCadastroAbrirRI0825(){
	if (!infraSelectSelecionado('selUnidades')) {
	  alert('Informe as Unidades de Destino.');
	  document.getElementById('selUnidades').focus();
	  return false;
	}

<? if (isset($_GET['id_documento_assinado'])){ ?>

  objAjaxVerificacaoAssinatura.executar();

  if (!objAjaxVerificacaoAssinatura.bolAssinado){
    if (!confirm('A assinatura no documento foi cancelada.\n\n Confirma envio do processo?')) {
      return false;
    }
  }

<?}?>
		
	return true;
}

function OnSubmitForm() {
	return validarCadastroAbrirRI0825();
}

function configurarPrazo(){
  if (document.getElementById('optDataCerta').checked){
    document.getElementById('txtPrazo').style.visibility = 'visible';
    document.getElementById('imgCalDataDecisao').style.visibility = 'visible';
    document.getElementById('txtDias').value = '';
    document.getElementById('txtDias').style.visibility = 'hidden';
    document.getElementById('divSinDiasUteis').style.visibility = 'hidden';
  }else if (document.getElementById('optDias').checked){
    document.getElementById('txtPrazo').value = '';
    document.getElementById('txtPrazo').style.visibility = 'hidden';
    document.getElementById('imgCalDataDecisao').style.visibility = 'hidden';
    document.getElementById('txtDias').style.visibility = 'visible';
    document.getElementById('divSinDiasUteis').style.visibility = 'visible';
  }else{
    document.getElementById('txtPrazo').value = '';
    document.getElementById('txtPrazo').style.visibility = 'hidden';
    document.getElementById('imgCalDataDecisao').style.visibility = 'hidden';
    document.getElementById('txtDias').value = '';
    document.getElementById('txtDias').style.visibility = 'hidden';
    document.getElementById('divSinDiasUteis').style.visibility = 'hidden';
    document.getElementById('chkSinDiasUteis').checked = false;
  }
}


<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmAtividadeListar" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>">
<?
//PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
//PaginaSEI::getInstance()->montarAreaValidacao();
?>
  <div id="divProcedimentos" class="infraAreaDados" style="height:8em;">
	 	<label id="lblProcedimentos" for="selProcedimentos" class="infraLabelObrigatorio">Processos:</label>
	  <select id="selProcedimentos" name="selProcedimentos" size="3" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
	  <?=$strItensSelProcedimentos?>
	  </select>
  </div>
  
  <div id="divUnidades" class="infraAreaDados" style="height:12em;">
	 	<label id="lblUnidades" for="selUnidades" class="infraLabelObrigatorio">Unidades:</label>
	  <input type="text" id="txtUnidade" name="txtUnidade" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
    <?=$strLinkUnidadesTramitacao?>
	  <input type="hidden" id="hdnIdUnidade" name="hdnIdUnidade" class="infraText" value="" />
	  <select id="selUnidades" name="selUnidades" size="4" multiple="multiple" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
	  </select>
    <div id="divOpcoesUnidades">
      <img id="imgLupaUnidades" onclick="objLupaUnidades.selecionar(700,500);" src="/infra_css/imagens/lupa.gif" alt="Selecionar Unidades" title="Selecionar Unidades" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <img id="imgSelecionarGrupo" onclick="selecionarGrupo();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/grupo.gif" title="Selecionar Grupos de Envio" alt="Selecionar Grupos de Envio" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <br />
      <img id="imgExcluirUnidades" onclick="objLupaUnidades.remover();" src="/infra_css/imagens/remover.gif" alt="Remover Unidades Selecionadas" title="Remover Unidades Selecionadas" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
    </div>
  </div>

  <div id="divGeral" class="infraAreaDados" style="height:20em;">
  
    <div id="divSinManterAberto" class="infraDivCheckbox">
      <input type="checkbox" id="chkSinManterAberto" name="chkSinManterAberto" class="infraCheckbox" <?=PaginaSEI::getInstance()->setCheckbox($objEnviarProcessoDTO->getStrSinManterAberto())?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
  	  <label id="lblSinManterAberto" for="chkSinManterAberto" accesskey="" class="infraLabelCheckbox" >Manter processo aberto na unidade atual</label>
  	</div>      
	
	  <div id="divSinRemoverAnotacoes" class="infraDivCheckbox">
      <input type="checkbox" id="chkSinRemoverAnotacoes" name="chkSinRemoverAnotacoes" class="infraCheckbox" <?=PaginaSEI::getInstance()->setCheckbox($objEnviarProcessoDTO->getStrSinRemoverAnotacoes())?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />	  
	    <label id="lblSinRemoverAnotacoes" for="chkSinRemoverAnotacoes" accesskey="" class="infraLabelCheckbox" >Remover anotação</label>
	  </div>      
	
	  <div id="divSinEnviarEmailNotificacao" class="infraDivCheckbox">
	    <input type="checkbox" id="chkSinEnviarEmailNotificacao" name="chkSinEnviarEmailNotificacao" class="infraCheckbox" <?=PaginaSEI::getInstance()->setCheckbox($objEnviarProcessoDTO->getStrSinEnviarEmailNotificacao())?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
	    <label id="lblSinEnviarEmailNotificacao" for="chkSinEnviarEmailNotificacao" accesskey="" class="infraLabelCheckbox" >Enviar e-mail de notificação</label>
	  </div>

    <fieldset id="fldPrazo" class="infraFieldset">
      <legend class="infraLegend">&nbsp;Retorno Programado&nbsp;</legend>

      <div id="divOptDataCerta" class="infraDivRadio">
        <input type="radio" name="rdoPrazo" id="optDataCerta" onclick="configurarPrazo();" <?=$_POST['rdoPrazo']=='1'?'checked="checked"':''?> value="1" class="infraRadio"/>
        <span id="spnDataCerta"><label id="lblDataCerta" for="optDataCerta" class="infraLabelRadio" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">Data certa</label></span>
      </div>

      <input type="text" id="txtPrazo" name="txtPrazo" onkeypress="return infraMascaraData(this, event)" class="infraText" value="<?=PaginaSEI::tratarHTML($_POST['txtPrazo'])?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <img src="/infra_css/imagens/calendario.gif" id="imgCalDataDecisao" title="Selecionar Prazo" alt="Selecionar Prazo"  class="infraImg" onclick="infraCalendario('txtPrazo',this);" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />

      <div id="divOptDias" class="infraDivRadio">
        <input type="radio" name="rdoPrazo" id="optDias" onclick="configurarPrazo();" <?=$_POST['rdoPrazo']=='2'?'checked="checked"':''?> value="2" class="infraRadio"/>
        <span id="spnDias"><label id="lblDias" for="optDias" class="infraLabelRadio" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">Prazo em dias</label></span>
      </div>

      <input type="text" id="txtDias" name="txtDias" class="infraText" value="<?=PaginaSEI::tratarHTML($_POST['txtDias'])?>" onkeypress="return infraMascaraNumero(this,event);" maxlength="3" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />

      <div id="divSinDiasUteis" class="infraDivCheckbox">
        <input type="checkbox" id="chkSinDiasUteis" name="chkSinDiasUteis" class="infraCheckbox" <?=PaginaSEI::getInstance()->setCheckbox($objEnviarProcessoDTO->getStrSinDiasUteis())?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <label id="lblSinDiasUteis" for="chkSinDiasUteis" accesskey="" class="infraLabelCheckbox" >Úteis</label>
      </div>


    </fieldset>

  </div>
  
  <input type="hidden" id="hdnIdProtocolos" name="hdnIdProtocolos" value="<?=$strIdProtocolos;?>" />
  <input type="hidden" id="hdnIdAtividades" name="hdnIdAtividades" value="<?=$strIdAtividades;?>" />
  <input type="hidden" id="hdnTipoEnvio" name="hdnTipoEnvio" value="<?=$strTipoEnvio;?>" />
  <input type="hidden" id="hdnUnidades" name="hdnUnidades" value="<?=$_POST['hdnUnidades']?>" />
  
  <?
  //PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSEI::getInstance()->montarAreaDebug();
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>