<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 11/03/2008 - criado por mga
*
*
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
	
  $arrComandos = array();

  
  
  switch($_GET['acao']){
    case 'contato_imprimir_etiquetas':
      $strTitulo = 'Etiquetas de Contato';                               
      $arrComandos[] = '<input type="button" name="btnImprimir" value="Imprimir" onclick="imprimirEtiquetasRI0517();" class="infraButton" />';
      
      PaginaSEI::getInstance()->salvarSelecao($_GET['acao'],$_GET['acao_origem']);

      
      if (isset($_POST['hdnContatos'])){
        $arrNumIdContatos = array();
        $arr = PaginaSEI::getInstance()->getArrItensTabelaDinamica($_POST['hdnContatos']);
        foreach($arr as $item){
          $arrNumIdContatos[] = $item[0];
        }
      }else{
        $arrNumIdContatos = PaginaSEI::getInstance()->getArrStrItensSelecionados();  
      }


      $strAncora = '';
      if (count($arrNumIdContatos)>0){        
         
      	$strContatos = ContatoINT::buscarEtiquetasRI0516($arrNumIdContatos,$_POST['rdoOpcoes']);

  	    $checkedCompleta = '';
  	    $checkedSemNome = '';
  	    $checkedSemEndereco = '';
      	
      	switch($_POST['rdoOpcoes']){
      	  case "1":
      	    $checkedCompleta = 'checked="checked"';
      	    break;
      	    
      	  case "2":
      	    $checkedSemNome = 'checked="checked"';
      	    break;
      	    
      	  case "3":
      	    $checkedSemEndereco = 'checked="checked"';
      	    break;
      	    
      	  default:
      	    $checkedCompleta = 'checked="checked"';
      	}
      	
      	
      	
        //InfraDebug::getInstance()->gravar('%'.$strContatos.'%');    	
        
        //if (count($arrNumIdContatos)>0){
        //  $strAncora = '#ID-'.$arrNumIdContatos[count($arrNumIdContatos)-1];
        //}
    	}
      
      $arrComandos[] = '<input type="button" name="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).$strAncora.'\';" class="infraButton" />';
      break;    
      
    case 'contato_pdf_etiquetas':


      $arr = PaginaSEI::getInstance()->getArrItensTabelaDinamica($_POST['hdnContatos']);

			$pdf = new InfraEtiquetasPDF('contato', 'mm', $_POST['txtColuna'], $_POST['txtLinha']);
			$pdf->Open();

			for($i = 0;$i < count($arr); $i++){
				 $pdf->Add_PDF_Label(str_replace('<br />',"\n", $arr[$i][1]), 0, 'L', 'V');
			}
			$pdf->Output();

      die;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

	$strLinkPesquisar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=contato_imprimir_etiquetas');
	$strLinkPdfEtiquetas = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=contato_pdf_etiquetas');

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
#fldPosicionamento {position:absolute;left:0%;top:10%;width:40%;padding:1em;}

#lblLinha {width:20%;}
#txtLinha {width:10%;}

#lblColuna {width:20%;}
#txtColuna {width:10%;}

#lblAviso {width:100%;}


#fldOpcoes {position:absolute;left:50%;top:10%;width:25%;padding:1em;}

<?
PaginaSEI::getInstance()->fecharStyle();

PaginaSEI::getInstance()->abrirStyle('media="all"');
?>
	.pagina {
      page-break-after:always;
      margin-top: 12pt;             
  }

	.paginas {
      page-break-after:always;
      margin-top: 17pt;
  }

  .etiqueta {
      vertical-align:top;
  }
    
<?
PaginaSEI::getInstance()->fecharStyle();

PaginaSEI::getInstance()->abrirStyleIE('if IE','media="all"');
?>
	.pagina {
	    margin-top: 34.5pt;
	    margin-left: 18pt;
	}
	
	.paginas {
	    margin-top: 34.5pt;
	    margin-left: 18pt;
	}
	
  .etiqueta {
  	vertical-align:top;              
  } 	
<?
PaginaSEI::getInstance()->fecharStyleIE();

PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

var objTabelaContatos;
function inicializar(){
		
	document.getElementById('txtLinha').focus();

  objTabelaContatos = new infraTabelaDinamica('tblContatos','hdnContatos',false,true);
}

function OnSubmitForm() {
	return true;
}

function imprimirEtiquetasRI0517(){

	if (infraTrim(document.getElementById('txtLinha').value)=='') {
		alert('Informe o número da linha.');
		document.getElementById('txtLinha').focus();
		return false;
	}else{
		if(document.getElementById('txtLinha').value>10){
			alert ('Linha não pode ser maior que 10.');
			document.getElementById('txtLinha').focus();
			return false;
		}
	}
	
	if (infraTrim(document.getElementById('txtColuna').value)=='') {
		alert('Informe o número da coluna.');
		document.getElementById('txtColuna').focus();
		return false;
	}else{
		if(document.getElementById('txtColuna').value>2){
			alert ('Coluna não pode ser maior que 2.');
			document.getElementById('txtColuna').focus();
			return false;
		}
	}	
	
	var frm = document.getElementById('frmContatoEtiquetas');
	
	var aWindow = window.open('', 'JanelaEtiquetasContatos',	'scrollbars=yes,menubar=no,resizable=yes,toolbar=no,width=800,height=600');

	var targetAnterior = frm.target;
  var actionAnterior = frm.action; 

	frm.target = 'JanelaEtiquetasContatos';
	frm.action='<?=$strLinkPdfEtiquetas?>';
	
	//alert(document.getElementById('hdnContatos').value);
	
	frm.submit();
	
	frm.target = targetAnterior;
	frm.action = actionAnterior;
	
  return true;
}

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmContatoEtiquetas" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('contato_etiquetas.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
<?
//PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
//PaginaSEI::getInstance()->montarAreaValidacao();
PaginaSEI::getInstance()->abrirAreaDados('15em');
?>
  <fieldset id="fldPosicionamento" class="infraFieldset">
    <legend class="infraLegend">&nbsp;Posicionamento&nbsp;</legend>
    <br />
    &nbsp;&nbsp;
	  <label id="lblLinha" for="txtLinha" accesskey="L" class="infraLabelObrigatorio" ><span class="infraTeclaAtalho">L</span>inha:</label>
	  &nbsp;
	  <input type="text" id="txtLinha" name="txtLinha" class="InfraText" value="1" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	  <label id="lblColuna" for="txtColuna" accesskey="C" class="infraLabelObrigatorio" ><span class="infraTeclaAtalho">C</span>oluna:</label>
	  &nbsp;
	  <input type="text" id="txtColuna" name="txtColuna" class="InfraText" value="1" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />	
    <br /> 	  
    <br />
	  <label id="lblAviso" for="" accesskey="" class="infraLabelOpcional" style="color:red;" >
	  AVISO: configurar no navegador a impressão de página sem cabeçalhos ou rodapés e com margens tamanho zero.
	  </label>
	  
	</fieldset>
	
  <fieldset id="fldOpcoes" class="infraFieldset">
  	<legend class="infraLegend">&nbsp;Opções&nbsp;</legend>
	    <input type="radio" name="rdoOpcoes" id="optCompleta" value="1" onclick="this.form.submit();" <?=$checkedCompleta;?> class="infraRadio"/>
	    <label for="optCompleta" class="infraLabelRadio">Completa</label>
	    <br />
			<input type="radio" name="rdoOpcoes" id="optSemNome" value="2" onclick="this.form.submit();" <?=$checkedSemNome;?> class="infraRadio"/>
	    <label for="optSemNome" class="infraLabelRadio">Sem Nome</label>
	    <br/>
			<input type="radio" name="rdoOpcoes" id="optSemEndereco" value="3" onclick="this.form.submit();" <?=$checkedSemEndereco;?> class="infraRadio"/>
	    <label for="optSemEndereco" class="infraLabelRadio">Sem Endereço</label>
	    <br />
  </fieldset>       
	
<?
PaginaSEI::getInstance()->fecharAreaDados();
PaginaSEI::getInstance()->abrirAreaTabela();
?>
  
  <table width="60%" id="tblContatos" name="tblContatos" class="infraTable">
    <caption class="infraCaption"><?=PaginaSEI::getInstance()->gerarCaptionTabela("Contatos para Impressão",0)?></caption>		
    <tr>
			<th style="display:none;">ID</th>
			<th class="infraTh" align="left">Etiqueta</th>
			<th class="infraTh" width="15%">Ações</th>
		</tr>
  </table>
	
  <input type="hidden" id="hdnContatos" name="hdnContatos" value="<?=$strContatos;?>" />
<?
  PaginaSEI::getInstance()->fecharAreaTabela();  
  //PaginaSEI::getInstance()->montarAreaDebug();
  //PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>