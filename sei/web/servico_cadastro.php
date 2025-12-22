<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 16/09/2011 - criado por mga
*
* Versão do Gerador de Código: 1.31.0
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

  PaginaSEI::getInstance()->verificarSelecao('servico_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  //PaginaSEI::getInstance()->salvarCamposPost(array(''));

  $strParametros = '&id_usuario='.$_GET['id_usuario'];
  
  $objServicoDTO = new ServicoDTO();

  $strDesabilitar = '';

  $arrComandos = array();

  switch($_GET['acao']){
    case 'servico_cadastrar':
      $strTitulo = 'Novo Serviço';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarServico" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].$strParametros).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      $objServicoDTO->setNumIdServico(null);
      $objServicoDTO->setNumIdUsuario($_GET['id_usuario']);
      $objServicoDTO->setStrIdentificacao($_POST['txtIdentificacao']);
      $objServicoDTO->setStrDescricao($_POST['txtDescricao']);
      $objServicoDTO->setStrServidor(implode(',',PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnServidores'])));      
      $objServicoDTO->setStrSinLinkExterno(PaginaSEI::getInstance()->getCheckbox($_POST['chkSinLinkExterno']));
      $objServicoDTO->setStrSinAtivo('S');

      if (isset($_POST['sbmCadastrarServico'])) {
        try{
          $objServicoRN = new ServicoRN();
          $objServicoDTO = $objServicoRN->cadastrar($objServicoDTO);
          PaginaSEI::getInstance()->setStrMensagem('Serviço "'.$objServicoDTO->getStrIdentificacao().'" cadastrado com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'&id_servico='.$objServicoDTO->getNumIdServico().$strParametros.PaginaSEI::getInstance()->montarAncora($objServicoDTO->getNumIdServico())));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'servico_alterar':
      $strTitulo = 'Alterar Serviço';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarServico" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $strDesabilitar = 'disabled="disabled"';

      if (isset($_GET['id_servico'])){
        $objServicoDTO->setNumIdServico($_GET['id_servico']);
        $objServicoDTO->retTodos(true);
        $objServicoRN = new ServicoRN();
        $objServicoDTO = $objServicoRN->consultar($objServicoDTO);
        if ($objServicoDTO==null){
          throw new InfraException("Registro não encontrado.");
        }
      } else {
        
        $objServicoDTO->setNumIdServico($_POST['hdnIdServico']);
        $objServicoDTO->setNumIdUsuario($_GET['id_usuario']);
        $objServicoDTO->setStrIdentificacao($_POST['txtIdentificacao']);
        $objServicoDTO->setStrDescricao($_POST['txtDescricao']);
        $objServicoDTO->setStrServidor(implode(',',PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnServidores'])));
        $objServicoDTO->setStrSinLinkExterno(PaginaSEI::getInstance()->getCheckbox($_POST['chkSinLinkExterno']));
        $objServicoDTO->setStrSinAtivo('S');
      }

      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].$strParametros.PaginaSEI::getInstance()->montarAncora($objServicoDTO->getNumIdServico())).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      if (isset($_POST['sbmAlterarServico'])) {
        try{
          $objServicoRN = new ServicoRN();
          $objServicoRN->alterar($objServicoDTO);
          PaginaSEI::getInstance()->setStrMensagem('Serviço "'.$objServicoDTO->getStrIdentificacao().'" alterado com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].$strParametros.PaginaSEI::getInstance()->montarAncora($objServicoDTO->getNumIdServico())));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'servico_consultar':
      $strTitulo = 'Consultar Serviço';
      $arrComandos[] = '<button type="button" accesskey="F" name="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].$strParametros.PaginaSEI::getInstance()->montarAncora($_GET['id_servico'])).'\';" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
      $objServicoDTO->setNumIdServico($_GET['id_servico']);
      $objServicoDTO->setBolExclusaoLogica(false);
      $objServicoDTO->retTodos(true);
      $objServicoRN = new ServicoRN();
      $objServicoDTO = $objServicoRN->consultar($objServicoDTO);
      if ($objServicoDTO===null){
        throw new InfraException("Registro não encontrado.");
      }
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }
  
  $arrServidores = array();
  if (!InfraString::isBolVazia($objServicoDTO->getStrServidor())){
    foreach(explode(',',$objServicoDTO->getStrServidor()) as $strServidor){
      $arrServidores[$strServidor] = $strServidor;
    }
    ksort($arrServidores);
  }
  $strItensSelServidores = InfraINT::montarSelectArray(null, null, null, $arrServidores);

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

#lblIdentificacao {position:absolute;left:0%;top:0%;width:40%;}
#txtIdentificacao {position:absolute;left:0%;top:6%;width:40%;}

#lblDescricao {position:absolute;left:0%;top:16%;width:70%;}
#txtDescricao {position:absolute;left:0%;top:22%;width:70%;}

#lblServidores {position:absolute;left:0%;top:32%;}
#txtServidor {position:absolute;left:0%;top:38%;width:39.5%;}
#selServidores {position:absolute;left:0%;top:47%;width:40%;}
#imgExcluirServidores {position:absolute;left:41%;top:47.5%;}

#divSinLinkExterno {position:absolute;left:0%;top:90%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
var objLupaServidores = null;

function inicializar(){
  if ('<?=$_GET['acao']?>'=='servico_cadastrar'){
    document.getElementById('txtIdentificacao').focus();
  } else if ('<?=$_GET['acao']?>'=='servico_consultar'){
    infraDesabilitarCamposAreaDados();
  }else{
    document.getElementById('btnCancelar').focus();
  }
  
  objLupaServidores = new infraLupaSelect('selServidores','hdnServidores',null);
  
  infraEfeitoTabelas();
}

function validarCadastro() {

  if (infraTrim(document.getElementById('txtIdentificacao').value)=='') {
    alert('Informe a Identificação.');
    document.getElementById('txtIdentificacao').focus();
    return false;
  }

  if (document.getElementById('selServidores').options.length==0) {
    alert('Informe pelo menos um Servidor.');
    document.getElementById('txtServidor').focus();
    return false;
  }

  return true;
}

function OnSubmitForm() {
  return validarCadastro();
}

function adicionarServidor(obj, ev){
  if (infraGetCodigoTecla(ev)==13){
    
    obj.value = infraTrim(obj.value); 
     
    if (obj.value==''){
      alert('Servidor não informado.');
      return false;
    }

    objLupaServidores.adicionar(obj.value,obj.value);

    document.getElementById('txtServidor').value = '';
    document.getElementById('txtServidor').focus();
    
    return false;
  }
  
  return true;
}

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmServicoCadastro" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>">
<?
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
//PaginaSEI::getInstance()->montarAreaValidacao();
PaginaSEI::getInstance()->abrirAreaDados('30em');
?>
  <label id="lblIdentificacao" for="txtIdentificacao" accesskey="" class="infraLabelObrigatorio">Identificação:</label>
  <input type="text" id="txtIdentificacao" name="txtIdentificacao" class="infraText" value="<?=PaginaSEI::tratarHTML($objServicoDTO->getStrIdentificacao());?>" onkeypress="return infraMascaraTexto(this,event,50);" maxlength="50" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />

  <label id="lblDescricao" for="txtDescricao" accesskey="" class="infraLabelOpcional">Descrição:</label>
  <input type="text" id="txtDescricao" name="txtDescricao" class="infraText" value="<?=PaginaSEI::tratarHTML($objServicoDTO->getStrDescricao());?>" onkeypress="return infraMascaraTexto(this,event,250);" maxlength="250" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />

 	<label id="lblServidores" for="selServidores" class="infraLabelObrigatorio">Servidores:</label>
  <input type="text" id="txtServidor" name="txtServidor" class="infraText" value="" onkeypress="return adicionarServidor(this,event);" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
  <input type="hidden" id="hdnServidores" name="hdnServidores" class="infraText" value="" />
  <select id="selServidores" name="selServidores" size="7" multiple="multiple" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
  <?=$strItensSelServidores?>
  </select>
  <img id="imgExcluirServidores" onclick="objLupaServidores.remover();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/remover.gif" alt="Remover Servidores Selecionados" title="Remover Servidores Selecionados" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
  
  <div id="divSinLinkExterno" class="infraDivCheckbox">
    <input type="checkbox" id="chkSinLinkExterno" name="chkSinLinkExterno" class="infraCheckbox" <?=PaginaSEI::getInstance()->setCheckbox($objServicoDTO->getStrSinLinkExterno())?>  tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
    <label id="lblSinLinkExterno" for="chkSinLinkExterno" accesskey="" class="infraLabelCheckbox">Gerar links de acesso externos</label>
  </div>
    
  <input type="hidden" id="hdnIdServico" name="hdnIdServico" value="<?=$objServicoDTO->getNumIdServico();?>" />
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