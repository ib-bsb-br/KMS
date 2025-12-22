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
  InfraDebug::getInstance()->setBolDebugInfra(true);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  
  SessaoSEI::getInstance()->validarLink();

  //PaginaSEI::getInstance()->prepararSelecao('procedimento_selecionar');
  
  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  PaginaSEI::getInstance()->setBolAutoRedimensionar(false);

  $strLinkMontarArvore = null;
  
  switch($_GET['acao']){  	     
        
    case 'procedimento_trabalhar':
    	//Título
      $strTitulo = 'Processo';
      
      $dblIdProcedimento = '';
      $dblIdDocumento = '';
      $dblIdProcedimentoAnexado = '';
      
      if (isset($_GET['id_procedimento']) && isset($_GET['id_documento'])){
        
        $dblIdProcedimento = $_GET['id_procedimento'];
        $dblIdDocumento = $_GET['id_documento'];
        
      }else if (isset($_GET['id_procedimento'])){
        
        $dblIdProcedimento = $_GET['id_procedimento'];
        
      }else if (isset($_GET['id_documento'])){
        
        $objDocumentoDTO = new DocumentoDTO();
        $objDocumentoDTO->retDblIdProcedimento();
        $objDocumentoDTO->setDblIdDocumento($_GET['id_documento']);
        
        $objDocumentoRN = new DocumentoRN();
        $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);

        if ($objDocumentoDTO==null){
          throw new InfraException('Documento não encontrado.',null,null,false);
        }
        
        $dblIdProcedimento = $objDocumentoDTO->getDblIdProcedimento();
        $dblIdDocumento = $_GET['id_documento'];
        
      }else if (isset($_GET['id_protocolo'])){
        
        $objProtocoloDTO = new ProtocoloDTO();
        $objProtocoloDTO->retDblIdProtocolo();
        $objProtocoloDTO->retStrStaProtocolo();
        $objProtocoloDTO->setDblIdProtocolo($_GET['id_protocolo']);
        
        $objProtocoloRN = new ProtocoloRN();
        $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);
      
        if ($objProtocoloDTO==null){
          throw new InfraException('Registro não encontrado.', null, null, false);
        }
          
        if ($objProtocoloDTO->getStrStaProtocolo()==ProtocoloRN::$TP_PROCEDIMENTO){
          $dblIdProcedimento = $objProtocoloDTO->getDblIdProtocolo();
        }else{
          $dblIdDocumento = $objProtocoloDTO->getDblIdProtocolo();
          
          $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
          $objRelProtocoloProtocoloDTO->retDblIdProtocolo1();
          $objRelProtocoloProtocoloDTO->setDblIdProtocolo2($dblIdDocumento);
          $objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_DOCUMENTO_ASSOCIADO);
          
          $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
          $objRelProtocoloProtocoloDTO = $objRelProtocoloProtocoloRN->consultarRN0841($objRelProtocoloProtocoloDTO);
          
          $dblIdProcedimento = $objRelProtocoloProtocoloDTO->getDblIdProtocolo1();
        }        
      }
      
      if (isset($_GET['id_procedimento_anexado'])){
        $dblIdProcedimentoAnexado = $_GET['id_procedimento_anexado'];
      }
      
      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->retStrStaNivelAcessoGlobal();
      $objProtocoloDTO->setDblIdProtocolo($dblIdProcedimento);
      
      $objProtocoloRN = new ProtocoloRN();
			$objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

			if ($objProtocoloDTO==null){
			  throw new InfraException('Processo não encontrado.',null,null,false);
			}
			
			if ($objProtocoloDTO->getStrStaNivelAcessoGlobal()==ProtocoloRN::$NA_SIGILOSO && $_GET['acesso']!='1' && $_GET['acao_origem']!='procedimento_gerar'){

        //verifica permissão de acesso ao processo
        $objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();
        $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_PROCEDIMENTOS);
        $objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_AUTORIZADO);
        $objPesquisaProtocoloDTO->setDblIdProtocolo($dblIdProcedimento);
        
        $objProtocoloRN = new ProtocoloRN();
        $arrObjProtocoloDTO = $objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO);

        if (count($arrObjProtocoloDTO)==0){
     			header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_controlar&acao_origem='.$_GET['acao']));
					die;     			
        }
				
				$bolAcesso = false;
				$strLinkMontarArvore = '';
				$strLinkAcesso = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=usuario_validar_acesso&acao_origem='.$_GET['acao'].'&acao_destino=procedimento_trabalhar&id_procedimento='.$dblIdProcedimento.'&id_documento='.$dblIdDocumento.'&id_procedimento_anexado='.$dblIdProcedimentoAnexado);
      }else{
        $bolAcesso = true;
        $strLinkMontarArvore = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_visualizar&acao_origem='.$_GET['acao'].'&acao_retorno='.PaginaSEI::getInstance()->getAcaoRetorno().'&id_procedimento='.$dblIdProcedimento.'&id_documento='.$dblIdDocumento.'&id_procedimento_anexado='.$dblIdProcedimentoAnexado);
        $strLinkAcesso = '';	
      }
      
      break;    	
 
    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $numPercentualArvore = '23';
  $numPercentualVisualizacao = '73';
  $bolNavegadorSafariIPad = PaginaSEI::getInstance()->isBolNavegadorSafariIpad();

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
#divInfraBarraLocalizacao {display:none;}
#divConteudo {height:50em;width:100%;}
#ifrArvore {float:left;width:<?=$numPercentualArvore?>%;height:99%;}
#ifrVisualizacao {float:left;width:<?=$numPercentualVisualizacao?>%;height:99%;}


#divArvore{
height:100%;
width:100%;
position:absolute; 
margin-left:0; 
left:0; 
top:0; 
display:none;
background-color:yellow; 
opacity:0.0;
filter:alpha(opacity=0);
}

#divRedimensionar { 
float:left;
display:inline;
top:0; 
height:99%;
overflow:auto;
cursor:w-resize;
background-image:url(imagens/barra_redimensionamento.gif);
background-repeat:repeat-y;
background-position:center;
width:5px;
background-color:white;
}

.divIosScroll {
float:left;
overflow: scroll;
-webkit-overflow-scrolling: touch;
display:inline;
}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

var divLeftTamanhoInicial = null;
var divRightTamanhoInicial = null;

function redimensionarMenu(){
  
  var wGlobal = document.getElementById('divInfraAreaGlobal').offsetWidth;
  var wMenu = document.getElementById('divInfraAreaTelaE').offsetWidth;
  var wLivre = wGlobal - wMenu;  
  if (wLivre > 0){
    document.getElementById("ifrArvore").style.width = Math.floor(0.<?=$numPercentualArvore?>*wLivre) + 'px';
    document.getElementById("ifrVisualizacao").style.width = Math.floor(0.<?=$numPercentualVisualizacao?>*wLivre) + 'px';
  }
  
  redimensionar();
}

function redimensionar(){
  
  if (INFRA_IOS){
    if (document.getElementById("ifrArvore").offsetHeight < 100){
      document.getElementById("ifrArvore").style.height = '100px';
    }
    
    if (document.getElementById("divRedimensionar").offsetHeight < 100){
      document.getElementById("divRedimensionar").style.height ='100px';
    }
  }
  
  var hTela = infraClientHeight();
  
  if (hTela > 0){

   	var hDivGlobal = document.getElementById('divInfraAreaGlobal').scrollHeight;
  	var PosYConteudo = infraOffsetTopTotal(document.getElementById('divConteudo'));
  
    var hRedimensionamento = 0;
  	
  	if (hTela > hDivGlobal){
  	  hRedimensionamento = hTela - PosYConteudo;
  	}else{
  	  hRedimensionamento = hDivGlobal - PosYConteudo;
  	}
  	
  	if (hRedimensionamento > 0 && hRedimensionamento < 1920){ //FullHD
  	  
  	  hRedimensionamento = hRedimensionamento - 25;
  	  
      document.getElementById('divConteudo').style.height = hRedimensionamento + 'px';
      document.getElementById("ifrArvore").style.height = hRedimensionamento + 'px';
      document.getElementById('divRedimensionar').style.height = hRedimensionamento + 'px';
      document.getElementById("ifrVisualizacao").style.height = hRedimensionamento + 'px';

      if (!INFRA_IOS){
        document.getElementById("ifrArvore").style.width = '<?=$numPercentualArvore?>%';
        document.getElementById("ifrVisualizacao").style.width = '<?=$numPercentualVisualizacao?>%';
      }else{
        var wConteudo = document.getElementById('divConteudo').offsetWidth;
        document.getElementById("ifrArvore").style.width = Math.floor(0.<?=$numPercentualArvore?>*wConteudo) + 'px';
        document.getElementById("ifrVisualizacao").style.width = Math.floor(0.<?=$numPercentualVisualizacao?>*wConteudo) + 'px';      
      }
    }
  }
}


var ie=document.all;
var nn6=document.getElementById&&!document.all;
var isdrag=false;
var x,y;
var dobj;

function movemouse(e) {

  if (e == null) { e = window.event } 

  if (e.button <= 1 && isdrag){

    var tamanhoRedimensionamento = null;
  
    tamanhoRedimensionamento = nn6 ? tx + e.clientX - x : tx + event.clientX - x;
    
    var tamanhoLeft = 0;
    var tamanhoRight = 0;
    
    if (tamanhoRedimensionamento > 0){
      tamanhoLeft = (divLeftTamanhoInicial + tamanhoRedimensionamento);
      tamanhoRight = (divRightTamanhoInicial - tamanhoRedimensionamento);
    }else{
      tamanhoLeft = (divLeftTamanhoInicial - Math.abs(tamanhoRedimensionamento));
      tamanhoRight = (divRightTamanhoInicial + Math.abs(tamanhoRedimensionamento));
    }
    
    if (tamanhoLeft < 0 || tamanhoRight < 0){
      if (tamanhoRedimensionamento > 0){
        tamanhoLeft = 0;
        tamanhoRight = (divLeftTamanhoInicial - divRightTamanhoInicial) ;
      }else{
        tamanhoLeft = (divLeftTamanhoInicial - divRightTamanhoInicial);
        tamanhoRight = 0;
      }
    }   
    
    if(tamanhoLeft > 50 && tamanhoRight > 100){
    	document.getElementById("ifrArvore").style.width = tamanhoLeft + 'px';	
    	document.getElementById("ifrVisualizacao").style.width = tamanhoRight + 'px';
    }
  }
  return false;
}

function selectmouse(e){
	
	document.getElementById("divArvore").style.display = 'block';
	
  var fobj       = nn6 ? e.target : event.srcElement;
  var topelement = nn6 ? "HTML" : "BODY";
  while (fobj.tagName != topelement && fobj.className != "dragme") {
    fobj = nn6 ? fobj.parentNode : fobj.parentElement;
  }

  if (fobj.className=="dragme") {
    isdrag = true;
    dobj = fobj;
    tx = parseInt(dobj.style.left+0);
    x = nn6 ? e.clientX : event.clientX;
    divLeftTamanhoInicial = document.getElementById("ifrArvore").offsetWidth;
    divRightTamanhoInicial = document.getElementById("ifrVisualizacao").offsetWidth;
   
    if (!INFRA_IOS){
      document.onmousemove=movemouse;
    } else {
      document.ontouchmove=movemouse;
    }
    return false;
  }

}

function dropmouse(e){
	isdrag=false;
	document.getElementById("divArvore").style.display = 'none';
}
 
function inicializar(){
  infraFlagResize=true;

  infraAdicionarEvento(document.getElementById("lnkInfraMenuSistema"),'click',redimensionarMenu);
  infraAdicionarEvento(window,'resize',redimensionar);
    
  if ('<?=$bolAcesso?>'!='1'){ 
    infraAbrirJanela('<?=$strLinkAcesso?>','janelaAcessoProcedimento',500,350,'location=0,status=1,resizable=1,scrollbars=1');
    return;
  }
    
  if ('<?=$_GET['acao_origem']?>' == 'procedimento_controlar' ||
      '<?=$_GET['acao_origem']?>' == 'procedimento_gerar' ||
      '<?=$_GET['acao_origem']?>' == 'rel_bloco_protocolo_listar' ||
      '<?=$_GET['acao_origem']?>' == 'procedimento_duplicar'){
    infraOcultarMenuSistemaEsquema();
  }
  redimensionar();
  <? if (PaginaSEI::getInstance()->getStrMensagens()==''){?>
  
    if (!INFRA_IOS){
      document.getElementById("divRedimensionar").onmousedown = selectmouse;    
	    document.onmouseup = dropmouse;
	    document.body.onmouseleave = dropmouse;
    }
    
  <? } ?>
}

function verificar(ifr){
  
  //se trocou unidade
  if (window.frames[ifr.id] != null && window.frames[ifr.id].document.getElementById('frmProcedimentoControlar')!=null){
    ifr.style.visibility = 'hidden';
    parent.parent.document.location.href = window.frames[ifr.id].document.location.href;
  }
}

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
if (PaginaSEI::getInstance()->getStrMensagens()==''){
?>
	 
	<div id="divConteudo">
		<div id="divArvore" ></div>
		
		<?if ($bolNavegadorSafariIPad){?>
		<div class="divIosScroll">
		<?}?>
		
	  <iframe id="ifrArvore" name="ifrArvore" onload="verificar(this);" src="<?=$strLinkMontarArvore?>" frameborder="0"  ></iframe>
	  
	  <?if ($bolNavegadorSafariIPad){?>
	  </div>
	  <?}?>
	  
	  <div id="divRedimensionar" class="dragme"></div>
	  
	  <?if ($bolNavegadorSafariIPad){?>
	  <div class="divIosScroll">
	  <?}?>
	  
    <iframe id="ifrVisualizacao" name="ifrVisualizacao" onload="verificar(this);" src="about:blank" frameborder="0"></iframe>
    
    <?if ($bolNavegadorSafariIPad){?>
    </div>
    <?}?>
    
  </div>
  
<?
}
//PaginaSEI::getInstance()->montarAreaDebug();
//PaginaSEI::getInstance()->fecharAreaDados();
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>