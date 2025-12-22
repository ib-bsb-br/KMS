<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 24/10/2011 - criado por mga
*
* Versão do Gerador de Código: 1.32.1
*
* Versão no CVS: $Id$
*/

try {
  //require_once dirname(__FILE__).'/Infra.php';

  session_start();

  //////////////////////////////////////////////////////////////////////////////
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(true);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoInfra::getInstance()->validarLink();

  PaginaInfra::getInstance()->prepararSelecao('infra_auditoria_selecionar');

  SessaoInfra::getInstance()->validarPermissao($_GET['acao']);

  PaginaInfra::getInstance()->salvarCamposPost(array('txtSiglaUsuario','txtNomeUsuario','txtSiglaUnidade','txtDescricaoUnidade','txtDthInicial','txtDthFinal','txtIp','txtServidor','txtRecurso','txtRequisicao','txtOperacao'));

  switch($_GET['acao']){
    case 'infra_auditoria_excluir':
      try{
        $arrStrIds = PaginaInfra::getInstance()->getArrStrItensSelecionados();
        $arrObjInfraAuditoriaDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objInfraAuditoriaDTO = new InfraAuditoriaDTO();
          $objInfraAuditoriaDTO->setDblIdInfraAuditoria($arrStrIds[$i]);
          $arrObjInfraAuditoriaDTO[] = $objInfraAuditoriaDTO;
        }
        $objInfraAuditoriaRN = new InfraAuditoriaRN();
        $objInfraAuditoriaRN->excluir($arrObjInfraAuditoriaDTO);
        PaginaInfra::getInstance()->adicionarMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaInfra::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoInfra::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'infra_auditoria_listar':
      $strTitulo = 'Auditoria';
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $arrComandos = array();
  $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" name="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';
  $arrComandos[] = '<button type="button" accesskey="L" id="btnLimpar" name="btnLimpar" onclick="limpar();" value="Limpar Critérios" class="infraButton"><span class="infraTeclaAtalho">L</span>impar Critérios</button>';
  
  $objInfraAuditoriaDTO = new InfraAuditoriaDTO();
  $objInfraAuditoriaDTO->retDblIdInfraAuditoria();
  $objInfraAuditoriaDTO->retStrSiglaUsuario();
  $objInfraAuditoriaDTO->retStrNomeUsuario();
  $objInfraAuditoriaDTO->retStrSiglaOrgaoUsuario();
  $objInfraAuditoriaDTO->retStrSiglaUnidade();
  $objInfraAuditoriaDTO->retStrDescricaoUnidade();
  $objInfraAuditoriaDTO->retStrSiglaOrgaoUnidade();
  $objInfraAuditoriaDTO->retDthAcesso();
  $objInfraAuditoriaDTO->retStrRecurso();
  $objInfraAuditoriaDTO->retStrIp();
  $objInfraAuditoriaDTO->retStrUserAgent();
  $objInfraAuditoriaDTO->retStrServidor();
  $objInfraAuditoriaDTO->retStrRequisicao();
  $objInfraAuditoriaDTO->retStrOperacao();
  $objInfraAuditoriaDTO->retNumIdUsuarioEmulador();
  $objInfraAuditoriaDTO->retStrSiglaUsuarioEmulador();
  $objInfraAuditoriaDTO->retStrNomeUsuarioEmulador();
  $objInfraAuditoriaDTO->retStrSiglaOrgaoUsuarioEmulador();
  
  
  $strSiglaUsuario = PaginaInfra::getInstance()->recuperarCampo('txtSiglaUsuario');
  if (!InfraString::isBolVazia($strSiglaUsuario)){
    $objInfraAuditoriaDTO->setStrSiglaUsuario($strSiglaUsuario);
  }

  $strNomeUsuario = PaginaInfra::getInstance()->recuperarCampo('txtNomeUsuario');
  if (!InfraString::isBolVazia($strNomeUsuario)){
    $objInfraAuditoriaDTO->setStrNomeUsuario($strNomeUsuario);
  }
  
  $strSiglaUnidade = PaginaInfra::getInstance()->recuperarCampo('txtSiglaUnidade');
  if (!InfraString::isBolVazia($strSiglaUnidade)){
    $objInfraAuditoriaDTO->setStrSiglaUnidade($strSiglaUnidade);
  }

  $strDescricaoUnidade = PaginaInfra::getInstance()->recuperarCampo('txtDescricaoUnidade');
  if (!InfraString::isBolVazia($strDescricaoUnidade)){
    $objInfraAuditoriaDTO->setStrDescricaoUnidade($strDescricaoUnidade);
  }
  
  $dthInicial = PaginaInfra::getInstance()->recuperarCampo('txtDthInicial');
  if (!InfraString::isBolVazia($dthInicial)){
    $objInfraAuditoriaDTO->setDthInicial($dthInicial);
  }
  
  $dthFinal = PaginaInfra::getInstance()->recuperarCampo('txtDthFinal');
  if (!InfraString::isBolVazia($dthFinal)){
    $objInfraAuditoriaDTO->setDthFinal($dthFinal);	
  }
  
  $strIp = PaginaInfra::getInstance()->recuperarCampo('txtIp');
  if (!InfraString::isBolVazia($strIp)){
    $objInfraAuditoriaDTO->setStrIp($strIp);	
  }

  $strServidor = PaginaInfra::getInstance()->recuperarCampo('txtServidor');
  if (!InfraString::isBolVazia($strServidor)){
    $objInfraAuditoriaDTO->setStrServidor($strServidor);	
  }
  
  $strRecurso = PaginaInfra::getInstance()->recuperarCampo('txtRecurso');
  if (!InfraString::isBolVazia($strRecurso)){
    $objInfraAuditoriaDTO->setStrRecurso($strRecurso);	
  }

  $strRequisicao = PaginaInfra::getInstance()->recuperarCampo('txtRequisicao');
  if (!InfraString::isBolVazia($strRequisicao)){
    $objInfraAuditoriaDTO->setStrRequisicao($strRequisicao);	
  }

  $strOperacao = PaginaInfra::getInstance()->recuperarCampo('txtOperacao');
  if (!InfraString::isBolVazia($strOperacao)){
    $objInfraAuditoriaDTO->setStrOperacao($strOperacao);	
  }
  
  PaginaInfra::getInstance()->prepararOrdenacao($objInfraAuditoriaDTO, 'Acesso', InfraDTO::$TIPO_ORDENACAO_DESC);
  PaginaInfra::getInstance()->prepararPaginacao($objInfraAuditoriaDTO);

  $arrObjInfraAuditoriaDTO = array();
  
  if (isset($_POST['hdnFlagAuditoria'])){
    $objInfraAuditoriaRN = new InfraAuditoriaRN();
    $arrObjInfraAuditoriaDTO = $objInfraAuditoriaRN->pesquisar($objInfraAuditoriaDTO);
  }

  PaginaInfra::getInstance()->processarPaginacao($objInfraAuditoriaDTO);
  $numRegistros = count($arrObjInfraAuditoriaDTO);

  if ($numRegistros > 0){

    $bolCheck = false;

    $bolAcaoConsultar = SessaoInfra::getInstance()->verificarPermissao('infra_auditoria_consultar');
    $bolAcaoAlterar = SessaoInfra::getInstance()->verificarPermissao('infra_auditoria_alterar');
    $bolAcaoImprimir = true;
    //$bolAcaoGerarPlanilha = SessaoInfra::getInstance()->verificarPermissao('infra_gerar_planilha_tabela');
    $bolAcaoExcluir = SessaoInfra::getInstance()->verificarPermissao('infra_auditoria_excluir');


    if ($bolAcaoExcluir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
      $strLinkExcluir = SessaoInfra::getInstance()->assinarLink('controlador.php?acao=infra_auditoria_excluir&acao_origem='.$_GET['acao']);
    }

    /*
    if ($bolAcaoGerarPlanilha){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="P" id="btnGerarPlanilha" value="Gerar Planilha" onclick="infraGerarPlanilhaTabela(\''.SessaoInfra::getInstance()->assinarLink('controlador.php?acao=infra_gerar_planilha_tabela')).'\');" class="infraButton">Gerar <span class="infraTeclaAtalho">P</span>lanilha</button>';
    }
    */
    
    if ($bolAcaoImprimir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
    }

    $strResultado = '';

    $strSumarioTabela = 'Tabela de Dados de Auditoria.';
    $strCaptionTabela = 'Dados de Auditoria';

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaInfra::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    if ($bolCheck) {
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaInfra::getInstance()->getThCheck().'</th>'."\n";
    }
    
    $strResultado .= '<th class="infraTh">Dados de Auditoria</th>'."\n";
    //$strResultado .= '<th class="infraTh">Ações</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistros; $i++){

      $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      $strResultado .= $strCssTr;

      if ($bolCheck){
        $strResultado .= '<td valign="top">'.PaginaInfra::getInstance()->getTrCheck($i,$arrObjInfraAuditoriaDTO[$i]->getDblIdInfraAuditoria(),$arrObjInfraAuditoriaDTO[$i]->getDblIdInfraAuditoria()).'</td>';
      }

      $strResultado .= '<td valign="top">';
      $strResultado .= '<b>Usuário: </b>'.PaginaInfra::getInstance()->tratarHTML($arrObjInfraAuditoriaDTO[$i]->getStrSiglaUsuario().' / '.$arrObjInfraAuditoriaDTO[$i]->getStrSiglaOrgaoUsuario().' - '.$arrObjInfraAuditoriaDTO[$i]->getStrNomeUsuario());
      if ($arrObjInfraAuditoriaDTO[$i]->getNumIdUsuarioEmulador()!=null){
        $strResultado .= ' (emulado por '.PaginaInfra::getInstance()->tratarHTML($arrObjInfraAuditoriaDTO[$i]->getStrSiglaUsuarioEmulador().' / '.$arrObjInfraAuditoriaDTO[$i]->getStrSiglaOrgaoUsuarioEmulador().' - '.$arrObjInfraAuditoriaDTO[$i]->getStrNomeUsuarioEmulador()).')';
      }
      
      $strResultado .= '<br /><b>Unidade: </b>'.PaginaInfra::getInstance()->tratarHTML($arrObjInfraAuditoriaDTO[$i]->getStrSiglaUnidade().' / '.$arrObjInfraAuditoriaDTO[$i]->getStrSiglaOrgaoUnidade().' - '.$arrObjInfraAuditoriaDTO[$i]->getStrDescricaoUnidade());
      $strResultado .= '<br /><b>Data/Hora: </b>'.PaginaInfra::getInstance()->tratarHTML($arrObjInfraAuditoriaDTO[$i]->getDthAcesso());
      $strResultado .= '<br /><b>IP de Acesso: </b>'.PaginaInfra::getInstance()->tratarHTML($arrObjInfraAuditoriaDTO[$i]->getStrIp());
      $strResultado .= '<br /><b>Navegador: </b>'.PaginaInfra::getInstance()->tratarHTML($arrObjInfraAuditoriaDTO[$i]->getStrUserAgent());
      $strResultado .= '<br /><b>Servidor: </b>'.PaginaInfra::getInstance()->tratarHTML($arrObjInfraAuditoriaDTO[$i]->getStrServidor());
      $strResultado .= '<br /><b>Recurso: </b>'.PaginaInfra::getInstance()->tratarHTML($arrObjInfraAuditoriaDTO[$i]->getStrRecurso());


      $strTemp = preg_replace("/(\S{80})/s", "$1&#8203;", $arrObjInfraAuditoriaDTO[$i]->getStrRequisicao());
      $strTemp = PaginaInfra::getInstance()->tratarHTML($strTemp);
      $strTemp = str_replace('&amp;#8203;','&#8203;',$strTemp);
      //$strTemp = str_replace('/',' / ',$strTemp);
      //$strTemp = str_replace(',',', ',$strTemp);
      $strTemp = str_replace('\n', '',$strTemp);
      $strTemp = str_replace("\n", '<br />',$strTemp);
      $strTemp = str_replace('&lt;br /&gt;','<br />',$strTemp);
      $strResultado .= '<br /><b>Requisição: </b><br />'.$strTemp;

      $strTemp = preg_replace("/(\S{80})/s", "$1&#8203;", $arrObjInfraAuditoriaDTO[$i]->getStrOperacao());
      $strTemp = PaginaInfra::getInstance()->tratarHTML($strTemp);
      $strTemp = str_replace('&amp;#8203;','&#8203;',$strTemp);
      //$strTemp = str_replace('/',' / ',$strTemp);
      //$strTemp = str_replace(',',', ',$strTemp);
      $strTemp = str_replace('\n', '',$strTemp);
      $strTemp = str_replace("\n", '<br />',$strTemp);
      $strTemp = str_replace('&lt;br /&gt;','<br />',$strTemp);
      $strResultado .= '<br /><b>Operação: </b><br />'.$strTemp;
      
      $strResultado .= '</td>';
      
      $strResultado .= '</tr>'."\n";
    }
    $strResultado .= '</table>';
  }
  if ($_GET['acao'] == 'infra_auditoria_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }else{
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\''.SessaoInfra::getInstance()->assinarLink('controlador.php?acao='.PaginaInfra::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }
  
}catch(Exception $e){
  PaginaInfra::getInstance()->processarExcecao($e);
} 

PaginaInfra::getInstance()->montarDocType();
PaginaInfra::getInstance()->abrirHtml();
PaginaInfra::getInstance()->abrirHead();
PaginaInfra::getInstance()->montarMeta();
PaginaInfra::getInstance()->montarTitle(PaginaInfra::getInstance()->getStrNomeSistema().' - '.$strTitulo);
PaginaInfra::getInstance()->montarStyle();
PaginaInfra::getInstance()->abrirStyle();
?>

#lblAviso {position:absolute;top:0%;left:20%;font-size:1.4em;color:red}

#lblSiglaUsuario {position:absolute;left:0%;top:0%;}
#txtSiglaUsuario {position:absolute;left:20%;top:0%;width:40%;}

#lblNomeUsuario {position:absolute;left:0%;top:10%;}
#txtNomeUsuario {position:absolute;left:20%;top:10%;width:60%;}

#lblSiglaUnidade {position:absolute;left:0%;top:20%;}
#txtSiglaUnidade {position:absolute;left:20%;top:20%;width:40%;}

#lblDescricaoUnidade {position:absolute;left:0%;top:30%;}
#txtDescricaoUnidade {position:absolute;left:20%;top:30%;width:60%;}

#lblRecurso {position:absolute;left:0%;top:40%;}
#txtRecurso {position:absolute;left:20%;top:40%;width:40%;}

#lblDthInicial {position:absolute;left:0%;top:50%;}
#txtDthInicial {position:absolute;left:20%;top:50%;width:15%;}
#imgCalDthInicial {position:absolute;left:36%;top:50%;}

#lblDthFinal {position:absolute;left:39.5%;top:50%;}
#txtDthFinal {position:absolute;left:42%;top:50%;width:15%;}
#imgCalDthFinal {position:absolute;left:58%;top:50%;}

#lblIp {position:absolute;left:0%;top:60%;}
#txtIp {position:absolute;left:20%;top:60%;width:40%;}

#lblServidor {position:absolute;left:0%;top:70%;}
#txtServidor {position:absolute;left:20%;top:70%;width:40%;}

#lblRequisicao {position:absolute;left:0%;top:80%;}
#txtRequisicao {position:absolute;left:20%;top:80%;width:60%;}

#lblOperacao {position:absolute;left:0%;top:90%;}
#txtOperacao {position:absolute;left:20%;top:90%;width:60%;}

<?
PaginaInfra::getInstance()->fecharStyle();
PaginaInfra::getInstance()->montarJavaScript();
PaginaInfra::getInstance()->abrirJavaScript();
?>

function inicializar(){
  if ('<?=$_GET['acao']?>'=='infra_auditoria_selecionar'){
    infraReceberSelecao();
    document.getElementById('btnFecharSelecao').focus();
  }else{
    document.getElementById('btnFechar').focus();
  }
  //infraEfeitoTabelas();
}

<? if ($bolAcaoExcluir){ ?>
function acaoExcluir(id,desc){
  if (confirm("Confirma exclusão do Dado de Auditoria \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmInfraAuditoriaLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmInfraAuditoriaLista').submit();
  }
}

function acaoExclusaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Dado de Auditoria selecionado.');
    return;
  }
  if (confirm("Confirma exclusão dos Dados de Auditoria selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmInfraAuditoriaLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmInfraAuditoriaLista').submit();
  }
}
<? } ?>

function validarForm(){
  
  if (infraTrim(document.getElementById('txtDthInicial').value)!=''){
    if (!infraValidarDataHora(document.getElementById('txtDthInicial'))){
      document.getElementById('txtDthInicial').focus();
      return false;
    }
  }

  if (infraTrim(document.getElementById('txtDthFinal').value)!=''){
    if (!infraValidarDataHora(document.getElementById('txtDthFinal'))){
      document.getElementById('txtDthFinal').focus();
      return false;
    }
  }
   
  infraExibirAviso();
  
  return true;
}

function limpar(){
  document.getElementById('txtSiglaUsuario').value = '';
  document.getElementById('txtNomeUsuario').value = '';
  document.getElementById('txtSiglaUnidade').value = '';
  document.getElementById('txtDescricaoUnidade').value = '';
  document.getElementById('txtDthInicial').value = '';
  document.getElementById('txtDthFinal').value = '';
  document.getElementById('txtIp').value = '';
  document.getElementById('txtServidor').value = '';
  document.getElementById('txtRecurso').value = '';
  document.getElementById('txtRequisicao').value = '';
  document.getElementById('txtOperacao').value = '';
}

<?
PaginaInfra::getInstance()->fecharJavaScript();
PaginaInfra::getInstance()->fecharHead();
PaginaInfra::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmInfraAuditoriaLista" method="post" onsubmit="return validarForm();" action="<?=SessaoInfra::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?
  PaginaInfra::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaInfra::getInstance()->abrirAreaDados('3em');
  ?>
  <label id="lblAviso" name="lblAviso">ATENÇÃO: Informar o maior número possível de critérios antes de realizar a pesquisa!</label>
  <?
  PaginaInfra::getInstance()->fecharAreaDados();
  PaginaInfra::getInstance()->abrirAreaDados('25em');
  ?>

  <label id="lblSiglaUsuario" for="txtSiglaUsuario" accesskey="" class="infraLabelOpcional">Sigla do Usuário:</label>
  <input type="text" id="txtSiglaUsuario" name="txtSiglaUsuario" class="infraText" value="<?=$strSiglaUsuario?>"  tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />

  <label id="lblNomeUsuario" for="txtNomeUsuario" accesskey="" class="infraLabelOpcional">Nome do Usuário:</label>
  <input type="text" id="txtNomeUsuario" name="txtNomeUsuario" class="infraText" value="<?=$strNomeUsuario?>"  tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />

  <label id="lblSiglaUnidade" for="txtSiglaUnidade" accesskey="" class="infraLabelOpcional">Sigla da Unidade:</label>
  <input type="text" id="txtSiglaUnidade" name="txtSiglaUnidade" class="infraText" value="<?=$strSiglaUnidade?>"  tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />

  <label id="lblDescricaoUnidade" for="txtDescricaoUnidade" accesskey="" class="infraLabelOpcional">Descrição da Unidade:</label>
  <input type="text" id="txtDescricaoUnidade" name="txtDescricaoUnidade" class="infraText" value="<?=$strDescricaoUnidade?>"  tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />

  <label id="lblRecurso" for="txtRecurso" accesskey="" class="infraLabelOpcional">Recurso:</label>
  <input type="text" id="txtRecurso" name="txtRecurso" class="infraText" value="<?=$strRecurso?>"  tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />
  
  <label id="lblDthInicial" for="txtDthInicial" accesskey="" class="infraLabelOpcional" >Período:</label>
  <input type="text" id="txtDthInicial" name="txtDthInicial" onkeypress="return infraMascara(this, event,'##/##/#### ##:##')" class="infraText" value="<?=$dthInicial?>" tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />
  <img src="<?=PaginaInfra::getInstance()->getDiretorioImagensGlobal()?>/calendario.gif" id="imgCalDthInicial" title="Selecionar Data/Hora Inicial" alt="Selecionar Data/Hora Inicial" class="infraImg" onclick="infraCalendario('txtDthInicial',this,true,'<?=InfraData::getStrDataAtual().' 00:00'?>');" />
  
  <label id="lblDthFinal" for="txtDthFinal" accesskey="" class="infraLabelOpcional" >a</label>
  <input type="text" id="txtDthFinal" name="txtDthFinal" onkeypress="return infraMascara(this, event,'##/##/#### ##:##')" class="infraText" value="<?=$dthFinal?>" tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />
  <img src="<?=PaginaInfra::getInstance()->getDiretorioImagensGlobal()?>/calendario.gif" id="imgCalDthFinal" title="Selecionar Data/Hora Final" alt="Selecionar Data/Hora Final" class="infraImg" onclick="infraCalendario('txtDthFinal',this,true,'<?=InfraData::getStrDataAtual().' 23:59'?>');" />

  <label id="lblIp" for="txtIp" accesskey="" class="infraLabelOpcional">IP:</label>
  <input type="text" id="txtIp" name="txtIp" class="infraText" value="<?=$strIp?>" onkeypress="return infraMascaraNumero(this,event,16,'.');" maxlength="16" tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />

  <label id="lblServidor" for="txtServidor" accesskey="" class="infraLabelOpcional">Servidor:</label>
  <input type="text" id="txtServidor" name="txtServidor" class="infraText" value="<?=$strServidor?>" tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />

  <label id="lblRequisicao" for="txtRequisicao" accesskey="" class="infraLabelOpcional">Requisição:</label>
  <input type="text" id="txtRequisicao" name="txtRequisicao" class="infraText" value="<?=$strRequisicao?>" tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />

  <label id="lblOperacao" for="txtOperacao" accesskey="" class="infraLabelOpcional">Operação:</label>
  <input type="text" id="txtOperacao" name="txtOperacao" class="infraText" value="<?=$strOperacao?>" tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />

  <input type="hidden" id="hdnFlagAuditoria" name="hdnFlagAuditoria" value="1" />
  
  <?
  PaginaInfra::getInstance()->fecharAreaDados();
  PaginaInfra::getInstance()->montarAreaTabela($strResultado,$numRegistros);
  PaginaInfra::getInstance()->montarAreaDebug();
  PaginaInfra::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaInfra::getInstance()->fecharBody();
PaginaInfra::getInstance()->fecharHtml();
?>