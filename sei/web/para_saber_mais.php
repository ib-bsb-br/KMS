<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 01/06/2011 - criado por mga
*
* Versão do Gerador de Código: 1.13.1
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

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  
 	$strTitulo = '';
    	
  $strSumarioTabela = 'Tabela de Ajuda.';
  $strCaptionTabela = 'Ajuda';
      
  $strResultado = '';
  $strResultado .= '<table class="infraTable" style="background-color:white;" summary="'.$strSumarioTabela.'">'."\n";
      
  $strResultado .= '<tr style="display:none">';
  $strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
  $strResultado .= '</tr>';
      
  $arr = array();
  $arr[] = array('Atribuição de Processo','sei_atribuicao_processo.swf',false);
  $arr[] = array('Incluir Documento em Bloco','sei_incluir_documento_bloco.swf',false);
  $arr[] = array('Controle de Processos','sei_controle_processos.swf',false);
  $arr[] = array('Iniciar Processo','sei_iniciar_processo.swf',false);
  $arr[] = array('Incluir Documento em Processo','sei_incluir_documento_processo.swf',false);
  $arr[] = array('Enviar Correspondência Eletrônica','sei_enviar_email.swf',false);
  $arr[] = array('Enviar Processo','sei_enviar_processo.swf',false);
  $arr[] = array('Disponibilizar Acesso Externo','sei_disponibilizar_acesso_externo.swf',false);
  $arr[] = array('Retorno Programado','sei_retorno_programado.swf',false);
  $arr[] = array('Sobrestamento','sei_sobrestamento.swf',false);
  $arr[] = array('Acompanhamento Especial','sei_acompanhamento_especial.swf',false);
  $arr[] = array('Texto Padrão - Como Criar','sei_criar_texto_padrao.swf',false);
  $arr[] = array('Texto Padrão - Como Usar','sei_usar_texto_padrao.swf',false);
  $arr[] = array('Ciência','sei_ciencia.swf',false);
  $arr[] = array('Credencial de Assinatura','sei_credencial_assinatura.swf',false);
  $arr[] = array('Grupos de E-mail - Como Criar','sei_criar_grupos_email.swf',false);
  $arr[] = array('Grupos de E-mail - Como Usar','sei_usar_grupos_email.swf',false);
  $arr[] = array('Renúncia de Credencial','sei_renunciar_credencial.swf',false);
  $arr[] = array('Publicação','sei_publicar.swf',false);
  //$arr[] = array('Bloco de Assinatura','sei_bloco_assinatura.swf',false);
  
  InfraArray::ordenarArray($arr,0,InfraArray::$TIPO_ORDENACAO_ASC);
  
  $numRegistros = count($arr);
      
  foreach($arr as $item){
    $strResultado .= '<tr class="infraTrClara">';
    $strResultado .= '<td>';
    $strResultado .= '<a target="_blank" href="'.'ajuda/'.$item[1].'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'" class="ancoraOpcao">'.$item[0];

    if ($item[2]){
    	$strResultado .= '&nbsp;(<img src="imagens/sei_novo.gif" title="Novo!" alt="Novo!" style="border:0" />)';
    }
    
    $strResultado .= '</a>'."\n";
    
    
    $strResultado .= '</td>';
    $strResultado .= '</tr>';
  }
	$strResultado .= '</table>';
 
}catch(Exception $e){
  PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema().' - '.$strTitulo);
PaginaSEI::getInstance()->montarStyle();
//PaginaSEI::getInstance()->abrirStyle();
//PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){
  infraEfeitoTabelas();
}  
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmParaSaberMais" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
<?
//PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
//PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
//PaginaSEI::getInstance()->montarAreaValidacao();
PaginaSEI::getInstance()->abrirAreaDados(null,'style="width:50%"');
?>
<br />
<br />
<label class="infraLabelObrigatorio" style="font-size:1.6em;">Índice de Vídeos:</label>
<br />
<br />
<?
//PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
PaginaSEI::getInstance()->fecharAreaDados();
PaginaSEI::getInstance()->montarAreaTabela($strResultado,$numRegistros,false,'style="width:51%;"');
?>
<br />
<br />
<p style="font-size:1.2em;text-align:center;width:70%;">Para visualização dos vídeos é necessário que o <a target="_blank" href="http://www.adobe.com/go/getflashplayer">Flash Player</a> esteja habilitado.</p>
<br />
</form>
<?
PaginaSEI::getInstance()->montarAreaDebug();
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>