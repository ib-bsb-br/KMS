<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 05/09/2008 - criado por mga
*
* Versão do Gerador de Código: 1.23.0
*
* Versão no CVS: $Id$
*/

try {
  require_once dirname(__FILE__).'/SEI.php'; 

  session_start();

  //////////////////////////////////////////////////////////////////////////////
  //InfraDebug::getInstance()->setBolLigado(false);
  //InfraDebug::getInstance()->setBolDebugInfra(false);
  //InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  SessaoSEI::getInstance()->setArrParametrosRepasseLink(array('arvore','id_localizador'));

  if(isset($_GET['arvore'])){
    PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
  }

	if ($_GET['acao_origem']=='estatisticas_detalhar_arquivamento'){
		PaginaSEI::getInstance()->setTipoPagina(PaginaSEI::$TIPO_PAGINA_SIMPLES);
	}

  $objLocalizadorDTO = new LocalizadorDTO();

  switch($_GET['acao']){
     
    case 'localizador_protocolos_listar':

    	$strTitulo = 'Documentos do Localizador';
    	
			//Recuperar a Identificacao do Localizador recebido
			$objLocalizadorDTO = new LocalizadorDTO();
			$objLocalizadorDTO->retStrIdentificacao();
			$objLocalizadorDTO->retNumIdUnidade();
		  $objLocalizadorDTO->setNumIdLocalizador($_GET['id_localizador']);
		  
		  $objLocalizadorRN = new LocalizadorRN();
		  $objLocalizadorDTO = $objLocalizadorRN->consultarRN0619($objLocalizadorDTO);	   
		  
		  if ($objLocalizadorDTO==null){
		    throw new InfraException('Registro não encontrado.');
		  }
		  
		  if ($objLocalizadorDTO->getNumIdUnidade()!=SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
		  	throw new InfraException('Localizador não pertence à unidade '.SessaoSEI::getInstance()->getStrSiglaUnidadeAtual().'.');
		  }
		  
		  
		  $strTitulo .= ' '.$objLocalizadorDTO->getStrIdentificacao();
    	
  		$objArquivamentoDTO = new ArquivamentoDTO();
  		$objArquivamentoDTO->setNumTipoFkLocalizador(InfraDTO::$TIPO_FK_OBRIGATORIA);
  		$objArquivamentoDTO->retDblIdProtocolo();
  		$objArquivamentoDTO->retStrProtocoloFormatadoProcedimento();
  		$objArquivamentoDTO->retStrNomeTipoProcedimento();
  		$objArquivamentoDTO->retDblIdProcedimentoDocumento();
  		$objArquivamentoDTO->retStrStaArquivamento();
      $objArquivamentoDTO->retNumSeqLocalizadorLocalizador();
  		$objArquivamentoDTO->retStrSiglaTipoLocalizador();
  		$objArquivamentoDTO->retStrNomeTipoLocalizador();
  		$objArquivamentoDTO->retNumIdUnidadeLocalizador();
  		$objArquivamentoDTO->retNumIdTipoLocalizador();
  		$objArquivamentoDTO->retStrProtocoloFormatadoDocumento();
  		$objArquivamentoDTO->retStrNomeSerieDocumento();
  		$objArquivamentoDTO->retStrNumeroDocumento();
  		$objArquivamentoDTO->setNumIdLocalizador($_GET['id_localizador']);

      PaginaSEI::getInstance()->prepararOrdenacao($objArquivamentoDTO, 'IdProtocolo', InfraDTO::$TIPO_ORDENACAO_ASC);

      PaginaSEI::getInstance()->prepararPaginacao($objArquivamentoDTO, 100);

		  $objArquivamentoRN = new ArquivamentoRN();
  		$arrObjArquivamentoDTO = $objArquivamentoRN->listar($objArquivamentoDTO);

      PaginaSEI::getInstance()->processarPaginacao($objArquivamentoDTO);
      
		  $numRegistros = count($arrObjArquivamentoDTO);

		  $arrComandos = array();  
		
		  if ($numRegistros > 0){
		    
		    $bolCheck = true;
		    $bolAcaoImprimir = true;

			  $bolAcaoProcedimentoTrabalhar = SessaoSEI::getInstance()->verificarPermissao('procedimento_trabalhar');
				$bolAcaoDocumentoVisualizar = SessaoSEI::getInstance()->verificarPermissao('documento_visualizar');
		    
		    if ($bolAcaoImprimir){
		      $bolCheck = true;
		      $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
		    }

	    	$strCaptionTabela = 'Documentos: ';
	      $strSumarioTabela = '';          		 
		    
		    $strResultado = '';    
		    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
		    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
		    $strResultado .= '<tr>';
		    if ($bolCheck) {
		      $strResultado .= '<th class="infraTh" width="1%">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
		    }

        $strResultado .= '<th class="infraTh" width="20%">'.PaginaSEI::getInstance()->getThOrdenacao($objArquivamentoDTO,'Processo','IdProcedimentoDocumento',$arrObjArquivamentoDTO).'</th>'."\n";
        $strResultado .= '<th class="infraTh" width="20%">'.PaginaSEI::getInstance()->getThOrdenacao($objArquivamentoDTO,'Documento','IdProtocolo',$arrObjArquivamentoDTO).'</th>'."\n";
        $strResultado .= '<th class="infraTh" width="20%">'.PaginaSEI::getInstance()->getThOrdenacao($objArquivamentoDTO,'Tipo','NomeSerieDocumento',$arrObjArquivamentoDTO).'</th>'."\n";
        $strResultado .= '<th class="infraTh" width="15%">'.PaginaSEI::getInstance()->getThOrdenacao($objArquivamentoDTO,'Número','NumeroDocumento',$arrObjArquivamentoDTO).'</th>'."\n";
        $strResultado .= '<th class="infraTh" width="15%">'.PaginaSEI::getInstance()->getThOrdenacao($objArquivamentoDTO,'Estado','StaArquivamento',$arrObjArquivamentoDTO).'</th>'."\n";
		    $strResultado .= '</tr>'."\n";
		    $strCssTr='';

        $objArquivamentoRN = new ArquivamentoRN();
		    $arrObjArquivamentoProtocoloDTO = InfraArray::indexarArrInfraDTO($objArquivamentoRN->listarValoresArquivamentoRN1119(),'StaArquivamento');
		    
		    for($i = 0;$i < $numRegistros; $i++){
		    	
		      $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
		      $strResultado .= $strCssTr;
		      
		      if ($bolCheck){
		        $strResultado .= '<td valign="top">'.PaginaSEI::getInstance()->getTrCheck($i,$arrObjArquivamentoDTO[$i]->getDblIdProtocolo(),$arrObjArquivamentoDTO[$i]->getStrProtocoloFormatadoDocumento()).'</td>';
		      }
		
		      $strResultado .= '<td valign="top" align="center">';
		      if ($bolAcaoProcedimentoTrabalhar){                 
		      	$strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_procedimento='.$arrObjArquivamentoDTO[$i]->getDblIdProcedimentoDocumento().'&id_documento='.$arrObjArquivamentoDTO[$i]->getDblIdProcedimentoDocumento()).'" target="_blank" class="protocoloNormal" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'" title="'.PaginaSEI::tratarHTML($arrObjArquivamentoDTO[$i]->getStrNomeTipoProcedimento()).'">'.PaginaSEI::tratarHTML($arrObjArquivamentoDTO[$i]->getStrProtocoloFormatadoProcedimento()).'</a>';
		      }else{
		      	$strResultado .= PaginaSEI::tratarHTML($arrObjArquivamentoDTO[$i]->getStrProtocoloFormatadoProcedimento());
		      }  
		      $strResultado .= '</td>';
		
		      //DOCUMENTO
		      $strResultado .= '<td valign="top" align="center">';              
	        if ($bolAcaoDocumentoVisualizar){
	          $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_visualizar&acao_origem='.$_GET['acao'].'&id_documento='.$arrObjArquivamentoDTO[$i]->getDblIdProtocolo()) .'" target="_blank" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'" class="protocoloNormal">'.PaginaSEI::tratarHTML($arrObjArquivamentoDTO[$i]->getStrProtocoloFormatadoDocumento()).'</a>';
	        }else{
	          $strResultado .= PaginaSEI::tratarHTML($arrObjArquivamentoDTO[$i]->getStrProtocoloFormatadoDocumento());
		      }
		      $strResultado .= '</td>';
		      
		      $strResultado .= '<td valign="top" align="center">';
		      $strResultado .= PaginaSEI::tratarHTML($arrObjArquivamentoDTO[$i]->getStrNomeSerieDocumento());
		      $strResultado .= '</td>';
	
		      $strResultado .= '<td valign="top" align="center">';
		      $strResultado .= PaginaSEI::tratarHTML($arrObjArquivamentoDTO[$i]->getStrNumeroDocumento());
		      $strResultado .= '</td>';
		      		      
		      $strResultado .= '<td valign="top" align="center">';
		      $strResultado .= PaginaSEI::tratarHTML($arrObjArquivamentoProtocoloDTO[$arrObjArquivamentoDTO[$i]->getStrStaArquivamento()]->getStrDescricao());
		      $strResultado .= '</td>';

		      $strResultado .= '</tr>'."\n";
		    }
		    $strResultado .= '</table>';
		  }

      if (!PaginaSEI::getInstance()->isBolArvore() && PaginaSEI::getInstance()->getTipoPagina()!=PaginaSEI::$TIPO_PAGINA_SIMPLES) {
        $arrComandos[] = '<button type="button" accesskey="V" id="btnVoltar" value="Voltar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($_GET['id_localizador'])) . '\'" class="infraButton"><span class="infraTeclaAtalho">V</span>oltar</button>';
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


<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){
  if ('<?=PaginaSEI::getInstance()->isBolPaginaSelecao()?>'!=''){
    infraReceberSelecao();
  }
  infraEfeitoTabelas();
}

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmProtocoloLista" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?
  //PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  //PaginaSEI::getInstance()->fecharAreaDados();
  PaginaSEI::getInstance()->montarAreaTabela($strResultado,$numRegistros);
  //PaginaSEI::getInstance()->montarAreaDebug();
  PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>