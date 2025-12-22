<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 18/11/2010 - criado por mga
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
  InfraDebug::getInstance()->setBolDebugInfra(true);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  
  $strParametros = '';
  if (isset($_GET['id_unidade_hoje'])){
  	$strParametros .= '&id_unidade_hoje='.$_GET['id_unidade_hoje'];
  }
 
  if (isset($_GET['tipo_unidade_hoje'])){
  	$strParametros .= '&tipo_unidade_hoje='.$_GET['tipo_unidade_hoje'];
  }

  if (isset($_GET['id_tipo_procedimento'])){
  	$strParametros .= '&id_tipo_procedimento='.$_GET['id_tipo_procedimento'];
  }

  if (isset($_GET['id_serie'])){
    $strParametros .= '&id_serie='.$_GET['id_serie'];
  }

  if (isset($_GET['id_bloco'])){
    $strParametros .= '&id_bloco='.$_GET['id_bloco'];
  }

  if (isset($_GET['id_usuario_atribuicao'])){
    $strParametros .= '&id_usuario_atribuicao='.$_GET['id_usuario_atribuicao'];
  }

  switch($_GET['acao']){

    case 'unidade_hoje_detalhar':
    	
    	PaginaSEI::getInstance()->setTipoPagina(PaginaSEI::$TIPO_PAGINA_SIMPLES);

      $objUnidadeHojeDTO = new UnidadeHojeDTO();
      $objUnidadeHojeDTO->retDblIdUnidadeHoje();
      $objUnidadeHojeDTO->retDblIdProcedimento();
      $objUnidadeHojeDTO->retDblIdDocumento();
      $objUnidadeHojeDTO->retStrProtocoloFormatadoProcedimento();
      $objUnidadeHojeDTO->retStrNomeTipoProcedimento();

      $bolDetalhamentoDocumentos = false;

      $objUnidadeHojeDTO->setNumTipoFkProcedimento(InfraDTO::$TIPO_FK_OBRIGATORIA);
      $objUnidadeHojeDTO->setNumTipoFkDocumento(InfraDTO::$TIPO_FK_OBRIGATORIA);

    	switch($_GET['tipo_unidade_hoje']){
    			
  	    case UnidadeHojeRN::$TIPO_UNIDADE_HOJE_PROCESSOS:
  	      $strTitulo = UnidadeHojeRN::$TITULO_UNIDADE_HOJE_PROCESSOS;
          //$objUnidadeHojeDTO->setNumTipoFkProcedimento(InfraDTO::$TIPO_FK_OBRIGATORIA);
  	      break;

        case UnidadeHojeRN::$TIPO_UNIDADE_HOJE_DOCUMENTOS_UNIDADE_NAO_ASSINADOS:
          $strTitulo = UnidadeHojeRN::$TITULO_UNIDADE_HOJE_DOCUMENTOS_UNIDADE_NAO_ASSINADOS;
          //$objUnidadeHojeDTO->setNumTipoFkDocumento(InfraDTO::$TIPO_FK_OBRIGATORIA);

          $objUnidadeHojeDTO->retStrProtocoloFormatadoDocumento();
          $objUnidadeHojeDTO->retStrNomeSerie();
          $bolDetalhamentoDocumentos = true;

          break;

        case UnidadeHojeRN::$TIPO_UNIDADE_HOJE_DOCUMENTOS_UNIDADE_ASSINADOS:
          $strTitulo = UnidadeHojeRN::$TITULO_UNIDADE_HOJE_DOCUMENTOS_UNIDADE_ASSINADOS;
          //$objUnidadeHojeDTO->setNumTipoFkDocumento(InfraDTO::$TIPO_FK_OBRIGATORIA);

          $objUnidadeHojeDTO->retStrProtocoloFormatadoDocumento();
          $objUnidadeHojeDTO->retStrNomeSerie();
          $bolDetalhamentoDocumentos = true;

          break;

        case UnidadeHojeRN::$TIPO_UNIDADE_HOJE_DOCUMENTOS_BLOCO_NAO_ASSINADOS:
          $strTitulo = UnidadeHojeRN::$TITULO_UNIDADE_HOJE_DOCUMENTOS_BLOCO_NAO_ASSINADOS;
          //$objUnidadeHojeDTO->setNumTipoFkDocumento(InfraDTO::$TIPO_FK_OBRIGATORIA);

          $objUnidadeHojeDTO->retStrProtocoloFormatadoDocumento();
          $objUnidadeHojeDTO->retStrNomeSerie();
          $bolDetalhamentoDocumentos = true;

          break;

        case UnidadeHojeRN::$TIPO_UNIDADE_HOJE_DOCUMENTOS_BLOCO_ASSINADOS:
          $strTitulo = UnidadeHojeRN::$TITULO_UNIDADE_HOJE_DOCUMENTOS_BLOCO_ASSINADOS;
          //$objUnidadeHojeDTO->setNumTipoFkDocumento(InfraDTO::$TIPO_FK_OBRIGATORIA);

          $objUnidadeHojeDTO->retStrProtocoloFormatadoDocumento();
          $objUnidadeHojeDTO->retStrNomeSerie();
          $bolDetalhamentoDocumentos = true;

          break;

        case UnidadeHojeRN::$TIPO_UNIDADE_HOJE_USUARIO_ATRIBUICAO:
          $strTitulo = UnidadeHojeRN::$TITULO_UNIDADE_HOJE_USUARIO_ATRIBUICAO;
          //$objUnidadeHojeDTO->setNumTipoFkProcedimento(InfraDTO::$TIPO_FK_OBRIGATORIA);
          break;

    		default:
    		  throw new InfraException('Tipo do detalhe da estatística não informado.');
    	}

      $objUnidadeHojeDTO->setDblIdUnidadeHoje($_GET['id_unidade_hoje']);


      if (isset($_GET['id_tipo_procedimento'])){
        $objUnidadeHojeDTO->setNumIdTipoProcedimentoProcedimento($_GET['id_tipo_procedimento']);
      }

      if (isset($_GET['id_serie'])){
        $objUnidadeHojeDTO->setNumIdSerieDocumento($_GET['id_serie']);
      }

      if (isset($_GET['id_bloco'])){
        $objUnidadeHojeDTO->setNumIdBloco($_GET['id_bloco']);
      }

      if (isset($_GET['id_usuario_atribuicao'])){
        $objUnidadeHojeDTO->setNumIdUsuarioAtribuicao($_GET['id_usuario_atribuicao']);
        $objUsuarioDTO = new UsuarioDTO();
        $objUsuarioDTO->setBolExclusaoLogica(false);
        $objUsuarioDTO->retStrSigla();
        $objUsuarioDTO->setNumIdUsuario($_GET['id_usuario_atribuicao']);

        $objUsuarioRN = new UsuarioRN();
        $objUsuarioDTO = $objUsuarioRN->consultarRN0489($objUsuarioDTO);
        if ($objUsuarioDTO!=null) {
          $strTitulo .= ' ' . $objUsuarioDTO->getStrSigla();
        }
      }

      $objUnidadeHojeDTO->setOrdDblIdProcedimento(InfraDTO::$TIPO_ORDENACAO_DESC);

    	break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $arrComandos = array();


  PaginaSEI::getInstance()->prepararPaginacao($objUnidadeHojeDTO);

  $objUnidadeHojeRN = new UnidadeHojeRN();
  $arrObjUnidadeHojeDTO = $objUnidadeHojeRN->listar($objUnidadeHojeDTO);
  
  PaginaSEI::getInstance()->processarPaginacao($objUnidadeHojeDTO);
  $numRegistros = count($arrObjUnidadeHojeDTO);

  if ($numRegistros > 0){

    $bolCheck = true;
    $bolAcaoImprimir = true;
    $bolAcaoProcedimentoTrabalhar = SessaoSEI::getInstance()->verificarPermissao('procedimento_trabalhar');
    $bolAcaoDocumentoVisualizar = SessaoSEI::getInstance()->verificarPermissao('documento_visualizar');

    if ($bolAcaoImprimir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
    }

    if ($bolAcaoProcedimentoTrabalhar) {
      $objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();
      $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_PROCEDIMENTOS);
      $objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_AUTORIZADO);
      $objPesquisaProtocoloDTO->setDblIdProtocolo(InfraArray::converterArrInfraDTO($arrObjUnidadeHojeDTO, 'IdProcedimento'));

      $objProtocoloRN = new ProtocoloRN();
      $arrObjProtocoloDTO = InfraArray::indexarArrInfraDTO($objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO), 'IdProtocolo');
    }

    $strResultado = '';

    $strSumarioTabela = 'Tabela de Registros de Detalhamento.';
    $strCaptionTabela = 'Registros de Detalhamento';

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    if ($bolCheck) {
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
    }
    
    $strResultado .= '<th class="infraTh" width="30%">Processo</th>'."\n";

    if (!$bolDetalhamentoDocumentos){
      $strResultado .= '<th class="infraTh">Tipo</th>'."\n";
    }else{
      $strResultado .= '<th class="infraTh">Documento</th>'."\n";
      $strResultado .= '<th class="infraTh">Tipo</th>'."\n";
    }

    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistros; $i++){

      $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      $strResultado .= $strCssTr;

      if ($bolCheck){
        $strResultado .= '<td valign="top">'.PaginaSEI::getInstance()->getTrCheck($i,$arrObjUnidadeHojeDTO[$i]->getDblIdUnidadeHoje(),$arrObjUnidadeHojeDTO[$i]->getDblIdUnidadeHoje()).'</td>';
      }
        
      $strResultado .= '<td valign="top" align="center">';
      if ($bolAcaoProcedimentoTrabalhar){

        if (isset($arrObjProtocoloDTO[$arrObjUnidadeHojeDTO[$i]->getDblIdProcedimento()])) {
          $strCorProcesso = ' class="' . ($arrObjProtocoloDTO[$arrObjUnidadeHojeDTO[$i]->getDblIdProcedimento()]->getStrSinAberto() == 'S' ? 'protocoloAberto' : 'protocoloFechado') . '"';
          $strParamIdDocumento = '';
          if ($bolDetalhamentoDocumentos){
            $strParamIdDocumento = '&id_documento='.$arrObjUnidadeHojeDTO[$i]->getDblIdDocumento();
          }
          $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_procedimento='.$arrObjUnidadeHojeDTO[$i]->getDblIdProcedimento().$strParamIdDocumento).'" target="_blank" '.$strCorProcesso.' tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'" alt="'.PaginaSEI::tratarHTML($arrObjUnidadeHojeDTO[$i]->getStrNomeTipoProcedimento()).'" title="'.PaginaSEI::tratarHTML($arrObjUnidadeHojeDTO[$i]->getStrNomeTipoProcedimento()).'">'.PaginaSEI::tratarHTML($arrObjUnidadeHojeDTO[$i]->getStrProtocoloFormatadoProcedimento()).'</a>';
        }else{
          $strResultado .= PaginaSEI::tratarHTML($arrObjUnidadeHojeDTO[$i]->getStrProtocoloFormatadoProcedimento());
        }

      }else{
      	$strResultado .= PaginaSEI::tratarHTML($arrObjUnidadeHojeDTO[$i]->getStrProtocoloFormatadoProcedimento());
      }  
      $strResultado .= '</td>';


      if (!$bolDetalhamentoDocumentos){
        $strResultado .= '<td valign="top" align="center">'.PaginaSEI::tratarHTML($arrObjUnidadeHojeDTO[$i]->getStrNomeTipoProcedimento()).'</td>';
      }else{

        $strResultado .= '<td valign="top" align="center">';
        if ($bolAcaoDocumentoVisualizar){
          $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_visualizar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_documento='.$arrObjUnidadeHojeDTO[$i]->getDblIdDocumento()).'" target="_blank" class="protocoloNormal" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'" alt="'.PaginaSEI::tratarHTML($arrObjUnidadeHojeDTO[$i]->getStrNomeSerie()).'" title="'.PaginaSEI::tratarHTML($arrObjUnidadeHojeDTO[$i]->getStrNomeSerie()).'">'.PaginaSEI::tratarHTML($arrObjUnidadeHojeDTO[$i]->getStrProtocoloFormatadoDocumento()).'</a>';
        }else{
          $strResultado .= PaginaSEI::tratarHTML($arrObjUnidadeHojeDTO[$i]->getStrProtocoloFormatadoDocumento());
        }
        $strResultado .= '</td>';


        $strResultado .= '<td valign="top" align="center">';
        $strResultado .=  PaginaSEI::tratarHTML($arrObjUnidadeHojeDTO[$i]->getStrNomeSerie());
        $strResultado .= '</td>';
      }



      $strResultado .= '</tr>'."\n";
            
    }
    $strResultado .= '</table>';
  }
  
  
  $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="window.close();" class="infraButton" style="width:8em"><span class="infraTeclaAtalho">F</span>echar</button>';

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
<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){
  document.getElementById('btnFechar').focus();
  infraEfeitoTabelas();
}

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmUnidadeHojeDetalhe" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>">
  <?
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  //PaginaSEI::getInstance()->abrirAreaDados('5em');
  //PaginaSEI::getInstance()->fecharAreaDados();
  PaginaSEI::getInstance()->montarAreaTabela($strResultado,$numRegistros);
  PaginaSEI::getInstance()->montarAreaDebug();
  PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>