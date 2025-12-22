<?
/*
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 * 19/04/2007 - CRIADO POR cle@trf4.gov.br
 */
try {  
	require_once dirname(__FILE__).'/../SEI.php';
	session_start();
	//////////////////////////////////////////////////////////////////////////////
	InfraDebug::getInstance()->setBolLigado(false);
	InfraDebug::getInstance()->setBolDebugInfra(true);
	InfraDebug::getInstance()->limpar();
	//////////////////////////////////////////////////////////////////////////////		
	
	SessaoPublicacoes::getInstance()->validarLink();
	
	SessaoPublicacoes::getInstance()->validarPermissao($_GET['acao']);
	
	PaginaPublicacoes::getInstance()->setTipoPagina(PaginaPublicacoes::$TIPO_PAGINA_SIMPLES);
	
	PaginaPublicacoes::getInstance()->setBolAutoRedimensionar(false);
	
	switch($_GET['acao']){
	  case 'publicacao_visualizar':

	    $arrComandos = array();
	    	   
	    //$arrComandos[] = '<button type="button" accesskey="E" id="btnEnviarPorEmail" value="Enviar por E-mail" onclick="enviarPorEmail();" class="infraButton"><span class="infraTeclaAtalho">E</span>nviar por E-mail</button>';
	    $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="imprimir();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
	     
	    /*$strCabecalho = '';
	    
	    $objPublicacaoRN = new PublicacaoRN();
	    $objPublicacaoDTO = new PublicacaoDTO();
	    
	    if ($_GET['id_publicacao_legado'] != null){
	      $objPublicacaoDTO->setNumIdPublicacaoLegado($_GET['id_publicacao_legado']);
	      $arrObjPublicacaoDTO = $objPublicacaoRN->listarPublicacaoLegado(array($objPublicacaoDTO));
	    }else{
	      
	      $objPublicacaoDTO->retNumIdPublicacao();
	      $objPublicacaoDTO->retStrProtocoloFormatadoProtocolo();
	      $objPublicacaoDTO->retStrNomeVeiculoPublicacao();
	      $objPublicacaoDTO->retNumNumero();
	      $objPublicacaoDTO->retStrSiglaVeiculoImprensaNacional();
	      $objPublicacaoDTO->retStrDescricaoVeiculoImprensaNacional();
	      $objPublicacaoDTO->retDtaPublicacaoIO();
	      $objPublicacaoDTO->retStrNomeSecaoImprensaNacional();
	      $objPublicacaoDTO->retStrPaginaIO();
	      $objPublicacaoDTO->retStrSiglaOrgaoUnidadeResponsavelDocumento();
	      $objPublicacaoDTO->retDtaPublicacao();
	      $objPublicacaoDTO->setDblIdDocumento($_GET['id_documento']);
	      
	      $objPublicacaoDTO = $objPublicacaoRN->consultarRN1044($objPublicacaoDTO);
	      
	      $objPublicacaoDTO->setNumIdPublicacaoLegado(null);
	      $arrObjPublicacaoDTO = array($objPublicacaoDTO);
	    }
	    
	    $strCabecalho = '<div id="divCabecalho">'."\n";
	    
	    $strCabecalho .= $arrObjPublicacaoDTO[0]->getStrNomeVeiculoPublicacao();
	    
	    if (!InfraString::isBolVazia($arrObjPublicacaoDTO[0]->getNumNumero())){
	      $strCabecalho .= ' '.$arrObjPublicacaoDTO[0]->getNumNumero();
	    }
	    $strCabecalho .= '; ';
	    
	    $strCabecalho .= 'Publicação '.$arrObjPublicacaoDTO[0]->getDtaPublicacao().'; ';
	    
	    $strDadosIO = PublicacaoINT::montarDadosImprensaNacional($arrObjPublicacaoDTO[0]->getStrSiglaVeiculoImprensaNacional(),
                                                     	         $arrObjPublicacaoDTO[0]->getStrDescricaoVeiculoImprensaNacional(),
                                                      	       $arrObjPublicacaoDTO[0]->getDtaPublicacaoIO(),
                                                      	       $arrObjPublicacaoDTO[0]->getStrNomeSecaoImprensaNacional(),
                                                      	       $arrObjPublicacaoDTO[0]->getStrPaginaIO());
	    if ($strDadosIO!=''){
	      $strCabecalho .= $strDadosIO.'; ';
	    }
	    
	    
	    
	    if ($arrObjPublicacaoDTO[0]->getNumIdPublicacaoLegado()!=null){
	      $strCabecalho .= 'Documento '.$arrObjPublicacaoDTO[0]->getStrProtocoloFormatadoProtocolo();
	    }else{
	      $strCabecalho .= 'SEI '.$arrObjPublicacaoDTO[0]->getStrProtocoloFormatadoProtocolo();
	    }
	    	    
	    $strCabecalho .= '</div>';
	    */
	    break;
	  
	  default:
	    throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
	}
	
} catch(Exception $e) {
	PaginaPublicacoes::getInstance()->processarExcecao($e);
}
//MONTAGEM DA PÁGINA
PaginaPublicacoes::getInstance()->montarDocType();
PaginaPublicacoes::getInstance()->abrirHtml();
PaginaPublicacoes::getInstance()->abrirHead();
PaginaPublicacoes::getInstance()->montarMeta();
PaginaPublicacoes::getInstance()->montarTitle(PaginaPublicacoes::getInstance()->getStrNomeSistema().' - Publicações Eletrônicas');
PaginaPublicacoes::getInstance()->montarStyle();
PaginaPublicacoes::getInstance()->abrirStyle();
?>
#divCabecalho {
display:none;
border:0;
border-bottom:1px solid #ccc;
padding:.1em;
}

#ifrDocumento {padding-top:.2em;width:99.5%;}
<?
PaginaPublicacoes::getInstance()->fecharStyle();
PaginaPublicacoes::getInstance()->montarJavaScript();
PaginaPublicacoes::getInstance()->abrirJavaScript();
?>

function imprimir() {   
	ifrDocumento.focus();
	ifrDocumento.print();   
}


function enviarPorEmail() {    
  infraAbrirJanela("<?=SessaoPublicacoes::getInstance()->assinarLink('controlador_publicacoes.php?acao=email_publicacao_enviar')?>","janelaEnviarPorEmail",400,200)   
}

function redimensionar(){
  if (document.getElementById('ifrDocumento')!=null){          	
     document.getElementById('ifrDocumento').style.height = (infraClientHeight()-80)+'px';    
  }
}

function inicializar(){ 
  redimensionar();  
  infraAdicionarEvento(window,'resize',redimensionar);
}
<?
PaginaPublicacoes::getInstance()->fecharJavaScript();
PaginaPublicacoes::getInstance()->fecharHead();
PaginaPublicacoes::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
PaginaPublicacoes::getInstance()->montarBarraComandosSuperior($arrComandos); 
PaginaPublicacoes::getInstance()->abrirAreaDados();
//echo $strCabecalho;
?>
 <iframe id="ifrDocumento" name="ifrDocumento" frameborder="no" src="<?=SessaoPublicacoes::getInstance()->assinarLink('controlador_publicacoes.php?acao=iframe_documento_visualizar&id_publicacao_legado='.$_GET['id_publicacao_legado'].'&id_documento='.$_GET['id_documento']);?>"></iframe>
<?
PaginaPublicacoes::getInstance()->montarAreaDebug();
PaginaPublicacoes::getInstance()->fecharAreaDados();
PaginaPublicacoes::getInstance()->fecharBody();
PaginaPublicacoes::getInstance()->fecharHtml();
?>