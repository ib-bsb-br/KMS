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
  //InfraDebug::getInstance()->setBolLigado(false);
  //InfraDebug::getInstance()->setBolDebugInfra(true);
  //InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  PaginaSEI::getInstance()->verificarSelecao('acompanhamento_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $strParametros = '';
	if(isset($_GET['arvore'])){
		PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
    $strParametros .= '&arvore='.$_GET['arvore'];
   }  

	$numIdProcedimento = '';   
	if(isset($_GET['id_procedimento'])){
  	$numIdProcedimento = $_GET['id_procedimento'];
  	$strParametros .= '&id_procedimento='.$_GET['id_procedimento'];
  }    

	$numIdGrupoAcompanhamento = '';   
	if(isset($_GET['id_grupo_acompanhamento'])){
    $numIdGrupoAcompanhamento = $_GET['id_grupo_acompanhamento'];
   }    
  
  $objAcompanhamentoDTO = new AcompanhamentoDTO();

  $strDesabilitar = '';

  $arrComandos = array();
  
  $bolAcaoExcluir = false;

  switch($_GET['acao']){
    case 'acompanhamento_cadastrar':


      if (PaginaSEI::getInstance()->isBolArvore() && !isset($_POST['hdnIdProcedimento'])){
        $dto = new AcompanhamentoDTO();
        $dto->retNumIdAcompanhamento();
        $dto->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $dto->setDblIdProtocolo($numIdProcedimento);
        
        $objAcompanhamentoRN = new AcompanhamentoRN();
        $dto = $objAcompanhamentoRN->consultar($dto);
        
        if ($dto!=null){
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=acompanhamento_alterar&acao_origem='.$_GET['acao'].'&acao_retorno='.PaginaSEI::getInstance()->getAcaoRetorno().'&id_acompanhamento='.$dto->getNumIdAcompanhamento().$strParametros));
          die;
        }
      }

      $strTitulo = 'Novo Acompanhamento Especial';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarAcompanhamento" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $arrComandos[] = '<button type="button" accesskey="N" name="btnNovoGrupo" id="btnNovoGrupo" value="Novo Grupo" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=grupo_acompanhamento_cadastrar&acao_origem='.$_GET['acao'].'$&acao_retorno='.$_GET['acao'].$strParametros).'\';" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo Grupo</button>';
      
      if (!PaginaSEI::getInstance()->isBolArvore()){
        $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].$strParametros).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';
      }

      $objAcompanhamentoDTO->setNumIdAcompanhamento(null);
      $objAcompanhamentoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      $objAcompanhamentoDTO->setNumIdGrupoAcompanhamento($_POST['selGrupoAcompanhamento']);
      $objAcompanhamentoDTO->setDblIdProtocolo($_POST['hdnIdProcedimento']);
      $objAcompanhamentoDTO->setNumIdUsuarioGerador(SessaoSEI::getInstance()->getNumIdUsuario());
      $objAcompanhamentoDTO->setDthGeracao(InfraData::getStrDataHoraAtual());
      $objAcompanhamentoDTO->setStrObservacao($_POST['txaObservacao']);

      if (isset($_POST['sbmCadastrarAcompanhamento'])) {
        try{
          $objAcompanhamentoRN = new AcompanhamentoRN();
          $objAcompanhamentoDTO = $objAcompanhamentoRN->cadastrar($objAcompanhamentoDTO);
          PaginaSEI::getInstance()->setStrMensagem('Acompanhamento "'.$objAcompanhamentoDTO->getNumIdAcompanhamento().'" cadastrado com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'&id_acompanhamento='.$objAcompanhamentoDTO->getNumIdAcompanhamento().$strParametros.'&atualizar_arvore=1'.PaginaSEI::getInstance()->montarAncora($objAcompanhamentoDTO->getNumIdAcompanhamento())));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'acompanhamento_alterar':
      
      if (PaginaSEI::getInstance()->isBolArvore()){
        $strTitulo = 'Acompanhamento Especial';
      }else{
        $strTitulo = 'Alterar Acompanhamento Especial';
      }
      
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarAcompanhamento" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $strDesabilitar = 'disabled="disabled"';

      if (isset($_GET['id_acompanhamento'])){
        $objAcompanhamentoDTO->setNumIdAcompanhamento($_GET['id_acompanhamento']);
        $objAcompanhamentoDTO->retTodos();
        $objAcompanhamentoRN = new AcompanhamentoRN();
        $objAcompanhamentoDTO = $objAcompanhamentoRN->consultar($objAcompanhamentoDTO);
        
        if ($objAcompanhamentoDTO==null){
          throw new InfraException("Registro não encontrado.");
        }
        $numIdGrupoAcompanhamento = $objAcompanhamentoDTO->getNumIdGrupoAcompanhamento();
        $numIdProcedimento = $objAcompanhamentoDTO->getDblIdProtocolo();
        
      } else {
        $objAcompanhamentoDTO->setNumIdAcompanhamento($_POST['hdnIdAcompanhamento']);
        $objAcompanhamentoDTO->setNumIdGrupoAcompanhamento($_POST['selGrupoAcompanhamento']);
        $objAcompanhamentoDTO->setStrObservacao($_POST['txaObservacao']);
      }

      
      if (PaginaSEI::getInstance()->isBolArvore()){
        $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('acompanhamento_excluir');
        $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=acompanhamento_excluir&acao_origem='.$_GET['acao'].'&acao_retorno=arvore_visualizar&id_acompanhamento='.$objAcompanhamentoDTO->getNumIdAcompanhamento().$strParametros); 
        $arrComandos[] = '<button type="button" accesskey="E" name="btnExcluir" id="btnExcluir" value="Excluir" onclick="acaoExcluir()" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
      }else{
        $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].$strParametros.PaginaSEI::getInstance()->montarAncora($_GET['id_acompanhamento'])).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';
      }

      if (isset($_POST['sbmAlterarAcompanhamento'])) {
        try{
          $objAcompanhamentoRN = new AcompanhamentoRN();
          $objAcompanhamentoRN->alterar($objAcompanhamentoDTO);
          PaginaSEI::getInstance()->setStrMensagem('Acompanhamento "'.$objAcompanhamentoDTO->getNumIdAcompanhamento().'" alterado com sucesso.');

          //die('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].$strParametros.PaginaSEI::getInstance()->montarAncora($objAcompanhamentoDTO->getNumIdAcompanhamento()));

          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].$strParametros.PaginaSEI::getInstance()->montarAncora($objAcompanhamentoDTO->getNumIdAcompanhamento())));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'acompanhamento_excluir':
      try{
        
        $objAcompanhamentoDTO = new AcompanhamentoDTO();
        $objAcompanhamentoDTO->setNumIdAcompanhamento($_GET['id_acompanhamento']);
                
        $objAcompanhamentoRN = new AcompanhamentoRN();
        $objAcompanhamentoRN->excluir(array($objAcompanhamentoDTO));
        PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
        header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].$strParametros.'&atualizar_arvore=1'));
        die;
        
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=acompanhamento_alterar&acao_origem='.$_GET['acao'].'&id_acompanhamento='.$_GET['id_acompanhamento'].$strParametros));
      die;
      

      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $strItensSelGrupoAcompanhamento = GrupoAcompanhamentoINT::montarSelectIdGrupoAcompanhamentoRI0012('null','&nbsp;', $numIdGrupoAcompanhamento, SessaoSEI::getInstance()->getNumIdUnidadeAtual());
  

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
#lblSelGrupoAcompanhamento {position:absolute;left:0%;top:0%;width:50%;}
#selGrupoAcompanhamento {position:absolute;left:0%;top:10%;width:50%;}

#lblObservacao {position:absolute;left:0%;top:25%;width:95%;}
#txaObservacao {position:absolute;left:0%;top:35%;width:95%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
function inicializar(){
  //if ('<?=$_GET['acao']?>'=='acompanhamento_cadastrar'){
  //  document.getElementById('txtIdUnidade').focus();
  //} else if ('<?=$_GET['acao']?>'=='acompanhamento_consultar'){
  //  infraDesabilitarCamposAreaDados();
  //}else{
  //  document.getElementById('btnCancelar').focus();
  //}
  infraEfeitoTabelas();
}

function validarCadastroRI0017() {
  /*
  if (infraTrim(document.getElementById('txaObservacao').value)=='') {
    alert('Informe a Observação.');
    document.getElementById('txaObservacao').focus();
    return false;
  }
  */
  return true;
}

function OnSubmitForm() {
  return validarCadastroRI0017();
}

<? if ($bolAcaoExcluir){ ?>
function acaoExcluir(){
  if (confirm("Confirma exclusão do Acompanhamento Especial?")){
    document.getElementById('frmAcompanhamentoCadastro').action='<?=$strLinkExcluir?>';
    document.getElementById('frmAcompanhamentoCadastro').submit();
  }
}
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmAcompanhamentoCadastro" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>">
<?
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
//PaginaSEI::getInstance()->montarAreaValidacao();
PaginaSEI::getInstance()->abrirAreaDados('20em');
?>
  <label id="lblSelGrupoAcompanhamento" for="selGrupoAcompanhamento" accesskey="G" class="infraLabel"><span class="infraTeclaAtalho">G</span>rupo:</label>
  <select id="selGrupoAcompanhamento" name="selGrupoAcompanhamento" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" >
  <?=$strItensSelGrupoAcompanhamento?>
  </select>

  <label id="lblObservacao" for="txaObservacao" accesskey="O" class="infraLabelOpcionals"><span class="infraTeclaAtalho">O</span>bservação:</label>
  <textarea rows="4" name="txaObservacao" id="txaObservacao" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"><?=PaginaSEI::tratarHTML($objAcompanhamentoDTO->getStrObservacao());?></textarea>

  <input type="hidden" id="hdnIdAcompanhamento" name="hdnIdAcompanhamento" value="<?=$objAcompanhamentoDTO->getNumIdAcompanhamento();?>" />
  <input type="hidden" id="hdnIdProcedimento" name="hdnIdProcedimento" value="<?=$numIdProcedimento;?>" />
  <?
  PaginaSEI::getInstance()->fecharAreaDados();
  //PaginaSEI::getInstance()->montarAreaDebug();
  //PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>


