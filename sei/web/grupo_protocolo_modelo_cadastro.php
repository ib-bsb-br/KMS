<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 16/08/2012 - criado por mkr@trf4.jus.br
*
* Versão do Gerador de Código: 1.33.0
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

  PaginaSEI::getInstance()->verificarSelecao('grupo_protocolo_modelo_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $strParametros = '';
  if(isset($_GET['arvore'])){
    PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
    $strParametros .= '&arvore='.$_GET['arvore'];
  }
   
  $numIdProcedimento = '';
  if(isset($_GET['id_protocolo'])){
    $numIdProcedimento = $_GET['id_protocolo'];
    $strParametros .= '&id_protocolo='.$_GET['id_protocolo'];
  }  

  $objGrupoProtocoloModeloDTO = new GrupoProtocoloModeloDTO();

  $strDesabilitar = '';

  $arrComandos = array();

  switch($_GET['acao']){
    case 'grupo_protocolo_modelo_cadastrar':
      $strTitulo = 'Novo Grupo de Modelo';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarGrupoProtocoloModelo" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].$strParametros).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      $objGrupoProtocoloModeloDTO->setNumIdGrupoProtocoloModelo(null);
      $objGrupoProtocoloModeloDTO->setStrNome($_POST['txtNome']);
      $numIdUnidade = SessaoSEI::getInstance()->getNumIdUnidadeAtual();
      if ($numIdUnidade!==''){
        $objGrupoProtocoloModeloDTO->setNumIdUnidade($numIdUnidade);
      }else{
        $objGrupoProtocoloModeloDTO->setNumIdUnidade(null);
      }

      if (isset($_POST['sbmCadastrarGrupoProtocoloModelo'])) {
        try{
          $objGrupoProtocoloModeloRN = new GrupoProtocoloModeloRN();
          $objGrupoProtocoloModeloDTO = $objGrupoProtocoloModeloRN->cadastrar($objGrupoProtocoloModeloDTO);
          PaginaSEI::getInstance()->adicionarMensagem('Grupo de Modelo "'.$objGrupoProtocoloModeloDTO->getStrNome().'" cadastrado com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].$strParametros.'&id_grupo_protocolo_modelo='.$objGrupoProtocoloModeloDTO->getNumIdGrupoProtocoloModelo().PaginaSEI::getInstance()->montarAncora($objGrupoProtocoloModeloDTO->getNumIdGrupoProtocoloModelo())));          
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'grupo_protocolo_modelo_alterar':
      $strTitulo = 'Alterar Grupo de Modelo';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarGrupoProtocoloModelo" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $strDesabilitar = 'disabled="disabled"';

      if (isset($_GET['id_grupo_protocolo_modelo'])){
        $objGrupoProtocoloModeloDTO->setNumIdGrupoProtocoloModelo($_GET['id_grupo_protocolo_modelo']);
        $objGrupoProtocoloModeloDTO->retTodos();
        $objGrupoProtocoloModeloRN = new GrupoProtocoloModeloRN();
        $objGrupoProtocoloModeloDTO = $objGrupoProtocoloModeloRN->consultar($objGrupoProtocoloModeloDTO);
        if ($objGrupoProtocoloModeloDTO==null){
          throw new InfraException("Registro não encontrado.");
        }
      } else {
        $objGrupoProtocoloModeloDTO->setNumIdGrupoProtocoloModelo($_POST['hdnIdGrupoProtocoloModelo']);
        $objGrupoProtocoloModeloDTO->setStrNome($_POST['txtNome']);
        $objGrupoProtocoloModeloDTO->setNumIdUnidade($_POST['selUnidade']);
      }

      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($objGrupoProtocoloModeloDTO->getNumIdGrupoProtocoloModelo())).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      if (isset($_POST['sbmAlterarGrupoProtocoloModelo'])) {
        try{                              
          $objGrupoProtocoloModeloRN = new GrupoProtocoloModeloRN();
          $numIdUnidade = SessaoSEI::getInstance()->getNumIdUnidadeAtual();
          if ($numIdUnidade!==''){
            $objGrupoProtocoloModeloDTO->setNumIdUnidade($numIdUnidade);
          }else{
            $objGrupoProtocoloModeloDTO->setNumIdUnidade(null);
          }
          $objGrupoProtocoloModeloRN->alterar($objGrupoProtocoloModeloDTO);
          PaginaSEI::getInstance()->adicionarMensagem('Grupo de Modelo "'.$objGrupoProtocoloModeloDTO->getStrNome().'" alterado com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].$strParametros.'&id_grupo_protocolo_modelo='.$objGrupoProtocoloModeloDTO->getNumIdGrupoProtocoloModelo().PaginaSEI::getInstance()->montarAncora($objGrupoProtocoloModeloDTO->getNumIdGrupoProtocoloModelo())));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;
    
    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
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
#lblNome {position:absolute;left:0%;top:0%;width:50%;}
#txtNome {position:absolute;left:0%;top:10%;width:50%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
function inicializar(){
  if ('<?=$_GET['acao']?>'=='grupo_protocolo_modelo_cadastrar'){
    document.getElementById('txtNome').focus();
  } else if ('<?=$_GET['acao']?>'=='grupo_protocolo_modelo_consultar'){
    infraDesabilitarCamposAreaDados();
  }else{
    document.getElementById('btnCancelar').focus();
  }
  infraEfeitoTabelas();
}

function validarCadastro() {
  if (infraTrim(document.getElementById('txtNome').value)=='') {
    alert('Informe o Nome.');
    document.getElementById('txtNome').focus();
    return false;
  }
 
  return true;
}

function OnSubmitForm() {
  return validarCadastro();
}

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmGrupoProtocoloModeloCadastro" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>">
<?
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
//PaginaSEI::getInstance()->montarAreaValidacao();
PaginaSEI::getInstance()->abrirAreaDados('20em');
?>
  <label id="lblNome" for="txtNome" accesskey="N" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">N</span>ome:</label>
  <input type="text" id="txtNome" name="txtNome" class="infraText" value="<?=PaginaSEI::tratarHTML($objGrupoProtocoloModeloDTO->getStrNome());?>" onkeypress="return infraMascaraTexto(this,event,50);" maxlength="50" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
 
  <input type="hidden" id="hdnIdGrupoProtocoloModelo" name="hdnIdGrupoProtocoloModelo" value="<?=$objGrupoProtocoloModeloDTO->getNumIdGrupoProtocoloModelo();?>" />
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