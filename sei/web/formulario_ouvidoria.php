<?
  /*
  * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
  * 17/12/2013 - criado por mkr@trf4.jus.br
  * 24/02/2014 - alterado por mga@trf4.jus.br
  */
 
  try {
    require_once dirname(__FILE__).'/SEI.php';

    //session_start();
    
    //SessaoSEI::getInstance(false);

    //////////////////////////////////////////////////////////////////////////////
    InfraDebug::getInstance()->setBolLigado(false);
    InfraDebug::getInstance()->setBolDebugInfra(false);
    InfraDebug::getInstance()->limpar();
    //////////////////////////////////////////////////////////////////////////////

    SessaoSEIExterna::getInstance()->validarLink();

    PaginaSEIExterna::getInstance()->setTipoPagina(PaginaSEIExterna::$TIPO_PAGINA_SEM_MENU);

    
    //$arrComandos = array();

    switch ($_GET['acao']) {
      case 'ouvidoria':
        $strTitulo = 'Ouvidoria';
        $strCodigoParaGeracaoCaptcha = InfraCaptcha::obterCodigo();         
        
        $objWS = new SoapClient(ConfiguracaoSEI::getInstance()->getValor('SEI','URL').'/controlador_ws.php?servico=ouvidoria',array('encoding'=>'ISO-8859-1'));
        
        if (isset($_POST['sbmEnviar'])) {
          try{
            if (hash('SHA512',$_POST['txtCaptcha']) != $_POST['hdnCaptcha']){
              PaginaSEIExterna::getInstance()->setStrMensagem('Código de confirmação inválido.');
            }else{

              if (($_POST['txtNome'] == "") || ($_POST['txtEmail'] == "") || ($_POST['txaMensagem'] == "")) {
                $msg = 'Erro: Os campos obrigatórios não foram todos preenchidos.';
              } else {


                $cidade = $_POST['selCidade'];
                $estado = $_POST['selEstado'];
                if ($estado == 'OUTRO') {
                  $cidade = $_POST['txtCidade'];
                  $estado = '';
                }

                $result_ws = $objWS->registrarOuvidoria($_GET['id_orgao_acesso_externo'],
                                                         $_POST['txtNome'],
                                                         $_POST['txtEmail'],
                                                         $_POST['txtCpf'],
                                                         $_POST['txtRg'],
                                                         $_POST['txtOrgaoExpedidor'],
                                                         $_POST['txtTelefone'],
                                                         $estado,
                                                         $cidade,
                                                         $_POST['selTipo'],
                                                         $_POST['txtProcessos'],
                                                         $_POST['chkRetorno'],
                                                         $_POST['txaMensagem']);

                header('Location: '.SessaoSEIExterna::getInstance()->assinarLink('controlador_externo.php?acao=ouvidoria&processo='.$result_ws));
                die;

              }
            }
          }catch(Exception $e){
            PaginaSEIExterna::getInstance()->processarExcecao($e);
          }
        }

        if (isset($_GET['processo'])){

          $strDisplayMensagem = '';

          $msg = '';
          $msg .= '&nbsp;&nbsp;O seu contato foi recebido e registrado no Processo Administrativo ' . $_GET['processo'] . '.' . "\n\n";
          $msg .= '&nbsp;&nbsp;Um comprovante foi enviado para o e-mail informado.';

        }else {

          $strItensSelEstado = $objWS->listarEstados($_POST['selEstado']);

          if ($_POST['selEstado'] != null && $_POST['selEstado'] != 'OUTRO') {
            $strItensSelCidade = $objWS->listarCidades($_POST['selEstado'], $_POST['selCidade']);
          }

          if ($_GET['tipo'] != '') {
            $_POST['selTipo'] = $_GET['tipo'];
          }

          $strSelTipo = $objWS->listarTiposProcedimento($_POST['selTipo']);

          $numLinhasMensagem = (PaginaSEIExterna::getInstance()->isBolNavegadorFirefox()) ? 5 : 6;

          $strDisplayMensagem = 'display:none';

          if (isset($_POST['hdnFlagFormulario'])) {
            if ($msg != '') {
              $strDisplayMensagem = '';
            } else {
              $strDisplayMensagem = 'display:none;';
            }
          }
        }

        break;

      default:
        throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
    }

    $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
    $numMaxMsg = $objInfraParametro->getValor('SEI_MAX_TAM_MENSAGEM_OUVIDORIA');
    $strTextoFormulario = $objInfraParametro->getValor('SEI_MSG_FORMULARIO_OUVIDORIA');

  } catch(Exception $e) {
    PaginaSEIExterna::getInstance()->processarExcecao($e);
  }

  PaginaSEIExterna::getInstance()->montarDocType();
  PaginaSEIExterna::getInstance()->abrirHtml();
  PaginaSEIExterna::getInstance()->abrirHead();
  PaginaSEIExterna::getInstance()->montarMeta();
  PaginaSEIExterna::getInstance()->montarTitle(PaginaSEIExterna::getInstance()->getStrNomeSistema().' - '.$strTitulo);  
  PaginaSEIExterna::getInstance()->montarStyle();
  PaginaSEIExterna::getInstance()->abrirStyle();
?>

#frmOuvidoria {padding-left:1em}

#divFormularioDados1 {height:9em;}
#lblNome{position:absolute;left:0;top:0%;width:20%;}
#txtNome{position:absolute;left:0;top:20%;width:60%;}

#lblEmail{position:absolute;left:0;top:50%;}
#txtEmail{position:absolute;left:0;top:70%;width:35%;}

#lblTelefone{position:absolute;left:37%;top:50%;width:23%;}
#txtTelefone{position:absolute;left:37%;top:70%;width:23%;}

#divFormularioCPF{height:4.5em;}
#lblCpf{position:absolute;left:0;top:0%;}
#txtCpf{position:absolute;left:0;top:40%;width:21%;}

#divFormularioOpcao{height:2em;}
#spnOpcao{position:absolute;left:0;top:0%}

#divFormularioRG{height:4.5em;;display:none;}
#lblRg{position:absolute;left:0;top:0%;}
#txtRg{position:absolute;left:0;top:40%;width:21%;}

#lblOrgaoExpedidor{position:absolute;top:0%;left:23%;width:20%;}
#txtOrgaoExpedidor{position:absolute;top:40%;left:23%;width:12%;}

#divFormularioDados2 {height:38em;}

#lblEstado{position:absolute;left:0;top:2%;width:7%;}
#selEstado{position:absolute;left:0;top:7%;width:7%;}

#lblCidade{position:absolute;left:8%;top:2%;}
#selCidade{position:absolute;left:8%;top:7%;width:27%;}
#txtCidade{position:absolute;left:8%;top:7%;width:27%;}

#lblTipo{position:absolute;left:0;top:14%;width:35%;}
#selTipo{position:absolute;left:0;top:19%;width:35%;}

#lblProcessos{position:absolute;left:37%;top:14%;width:23%;}
#txtProcessos{position:absolute;left:37%;top:19%;width:23%;}

#lblMensagem{position:absolute;left:0;top:26%;width:30%;}
#txaMensagem{position:absolute;left:0;top:31%;width:60%;}

#chkRetorno{position:absolute;left:0;top:56%;}
#lblRetorno{position:absolute;left:2.5%;top:56%;}

#numStrRestante{position:absolute;left:47%;top:56%;font-size:1.1em;}

#lblCaptcha {position:absolute;left:0;top:67%;}
#txtCaptcha {position:absolute;left:15%;top:66%;width:12%;height:12%;font-size:3em;text-align:center;}
#lblCodigo  {position:absolute;left:29%;top:70%;}

#sbmEnviar{position:absolute;left:0;top:85%;width:10em;}

#lblCamposObrigatorios{position:absolute;left:0;top:94%;}

#divMensagem {height:35em;padding:1em;font-size:1.2em;<?=$strDisplayMensagem?>}


<?
PaginaSEIExterna::getInstance()->fecharStyle();
PaginaSEIExterna::getInstance()->montarJavaScript();
PaginaSEIExterna::getInstance()->abrirJavaScript();
?>
function inicializar(){

  <? if ($msg=='') {?>

    if(document.getElementById('selEstado').value=="OUTRO") {
      document.getElementById('selCidade').style.display="none";
      document.getElementById('txtCidade').style.display="";
      document.getElementById('lblCidade').innerHTML="Cidade:";
    } else {
      document.getElementById('selCidade').style.display="";
      document.getElementById('txtCidade').style.display="none";
    }

    if ('<?=$_POST['hdnFlagFormulario']?>'=='2'){
      document.getElementById('divFormularioCPF').style.display	= "none";
      document.getElementById('divFormularioRG').style.display	= "block";
      document.getElementById('ancOpcao').innerHTML = 'Se você prefere informar o CPF clique aqui.';
    }

    document.getElementById('txtNome').focus();

  <? } ?>

}

function mostrarRGCPF(){
	
	if (document.getElementById('ancOpcao').innerHTML=='Se você não tem CPF clique aqui.'){
    document.getElementById('hdnFlagFormulario').value = '2';
		document.getElementById('txtCpf').value = '';
		document.getElementById('divFormularioCPF').style.display	= "none";
		document.getElementById('divFormularioRG').style.display	= "block";
		document.getElementById('ancOpcao').innerHTML = 'Se você prefere informar o CPF clique aqui.';
		document.getElementById('txtRg').focus();
	}else{
    document.getElementById('hdnFlagFormulario').value = '1';
		document.getElementById('txtRg').value = '';
		document.getElementById('divFormularioCPF').style.display	= "block";
		document.getElementById('divFormularioRG').style.display	= "none";
		document.getElementById('ancOpcao').innerHTML = 'Se você não tem CPF clique aqui.';
		document.getElementById('txtCpf').focus();
	}
}

function validarFormulario() {
  
  if (document.getElementById('txtNome').value == '') {
    alert('Nome não informado.');
    document.getElementById('txtNome').focus();
    return false;
  } 
  
  if (document.getElementById('txtEmail').value=='') {
		alert('E-mail não informado.');
		document.getElementById('txtEmail').focus();
		return false;
  } 
  
  if (!infraValidarEmail(document.getElementById('txtEmail').value)) {
    alert('E-mail inválido.');
    document.getElementById('txtEmail').focus();
    return false;
  } 
  
  if (document.getElementById('txtCpf').value == ''){
		if (document.getElementById('txtRg').value == '' || document.getElementById('txtOrgaoExpedidor').value == ''){
	  	alert('Pelo menos um dos campos CPF ou RG/Órgão Expedidor deve ser informado.');
	  	return false;
  	}  	  	 
  }else if (!infraValidarCpf(document.getElementById('txtCpf').value)){  			
  	alert('CPF Inválido!');
  	document.getElementById('txtCpf').focus();                        
    return false;                            
  } 
  
  if (document.getElementById('selEstado').value=='null'){    
    alert('Selecione um estado.');
    document.getElementById('selEstado').focus();
    return false;
  } else if (document.getElementById('selEstado').value!='OUTRO'){
    if (document.getElementById('selCidade').value=='null'){    
      alert('Selecione uma cidade.');
      document.getElementById('selCidade').focus();
      return false;
    }
  }
  
  if (document.getElementById('selTipo').value=='null'){    
    alert('Selecione o tipo da mensagem.');
    document.getElementById('selTipo').focus();
    return false;
  } 
  
  if (infraTrim(document.getElementById('txaMensagem').value) == '') {
    alert('Mensagem não informada.');
    document.getElementById('txaMensagem').focus();
    return false;
  }
  
  if (infraTrim(document.getElementById('txtCaptcha').value)=='') {
    alert('Informe o código de confirmação.'); 
    document.getElementById('txtCaptcha').focus();
    return false; 
  }
  
  document.getElementById('sbmEnviar').style.visibility='hidden';  
  
  return true;
}

function limitador() {
  var campoObservacoes = document.getElementById('txaMensagem');
  var caracteresRestantes = document.getElementById('numStrRestante');
  var limite = <?=$numMaxMsg?>;
  if (campoObservacoes.value.length > limite) {
    campoObservacoes.value = campoObservacoes.value.substring(0, limite);
  } else {
  	var numero = limite - campoObservacoes.value.length;
    caracteresRestantes.innerHTML = 'Caracteres restantes: '+numero;
  }
}
<?
  PaginaSEIExterna::getInstance()->fecharJavaScript();  
  PaginaSEIExterna::getInstance()->fecharHead();
    
  PaginaSEIExterna::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');   
    
  ?>	
  <div id="divMensagem">
    <br /><br /><?=nl2br($msg)?><br /><br />
  </div>
  
  <? if ($msg=='') {?>


	<form name="frmOuvidoria" id="frmOuvidoria" class="formulario" method="post" onsubmit="return validarFormulario();" action="<?=SessaoSEIExterna::getInstance()->assinarLink('controlador_externo.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].'')?>">

    <div class="formularioTexto"><?=$strTextoFormulario?></div>

    <div id="divFormularioDados1" class="infraAreaDados">
  		
  			<label id="lblNome" for="txtNome" class="infraLabelObrigatorio">Nome Completo:*</label>
  			<input type="text" id="txtNome" name="txtNome" class="infraText" maxlength="100" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" value="<?=PaginaSEI::tratarHTML($_POST['txtNome']);?>"/>
  			
  			<label id="lblEmail" for="txtEmail" class="infraLabelObrigatorio">E-mail:*</label>
  			<input type="text" id="txtEmail" name="txtEmail" maxlength="100" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" value="<?=PaginaSEI::tratarHTML($_POST['txtEmail']);?>"/>

        <label id="lblTelefone" for="txtTelefone" class="infraLabelOpcional">DDD e Telefone:</label>
        <input type="text" id="txtTelefone" name="txtTelefone" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" value="<?=PaginaSEI::tratarHTML($_POST['txtTelefone']);?>" onkeypress="return infraMascaraTelefone(this,event)" />

      </div>
  
      <div id="divFormularioCPF" class="infraAreaDados">
  			<label id="lblCpf" for="txtCpf" class="infraLabelObrigatorio">CPF:*</label>
  			<input type="text" id="txtCpf" name="txtCpf" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" value="<?=PaginaSEI::tratarHTML($_POST['txtCpf']);?>" maxlength="14" onkeypress="return infraMascara(this,event,'###.###.###-##')" />
      </div>
      
      <div id="divFormularioOpcao" class="infraAreaDados">
        <span id="spnOpcao"><a id="ancOpcao" href="javascript:mostrarRGCPF();" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">Se você não tem CPF clique aqui.</a></span>
      </div>
  
  		<div id="divFormularioRG" class="infraAreaDados">
  		    		
  				<label id="lblRg" for="txtRg" class="infraLabelObrigatorio">RG:*</label>
  				<input type="text" id="txtRg" name="txtRg" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" value="<?=PaginaSEI::tratarHTML($_POST['txtRg']);?>" maxlength="15" size="15" onkeypress="return infraMascaraNumero(this,event);" />
  				 		 
  				<label id="lblOrgaoExpedidor" for="txtOrgaoExpedidor" class="infraLabelObrigatorio">Órgão Expedidor:*</label>
  				<input type="text" id="txtOrgaoExpedidor" name="txtOrgaoExpedidor" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" value="<?=PaginaSEI::tratarHTML($_POST['txtOrgaoExpedidor']);?>" maxlength="6" size="6" />
  				
  		</div>
  
      <div id="divFormularioDados2" class="infraAreaDados">

  			<label id="lblEstado" for="selEstado" class="infraLabelObrigatorio">Estado:*</label>
  			<select id="selEstado" name="selEstado" onchange="this.form.submit()" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
  			  <?=$strItensSelEstado?>
  				<option id="optOutrosEst" name="optOutros" value="OUTRO" <? if ($_POST['selEstado'] == 'OUTRO') echo 'selected="true"'; ?>>Outro</option>
  			</select>
  					
  			<label id="lblCidade" for="selCidade" class="infraLabelObrigatorio">Cidade:*</label>
  			<select id="selCidade" name="selCidade" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
  			<?=$strItensSelCidade?>				
  			</select>
  			
  			<input type="text" id="txtCidade" name="txtCidade" maxlength="100" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" value="<?=$_POST['txtCidade'];?>"/>
  	  			
  			<label id="lblTipo" for="selTipo" class="infraLabelObrigatorio">Tipo:*</label>
  			<select id="selTipo" name="selTipo" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
  			<?=$strSelTipo;?>
  			</select>

        <label id="lblProcessos" for="txtProcessos" class="infraLabelOpcional">Processos Relacionados (se houver):</label>
        <input type="text" id="txtProcessos" name="txtProcessos" maxlength="100" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" value="<?=PaginaSEI::tratarHTML($_POST['txtProcessos']);?>"/>

  			<label id="lblMensagem" for="txaMensagem" class="infraLabelObrigatorio">Mensagem:*</label>
  			<textarea id="txaMensagem" name="txaMensagem" class="infraTextarea" rows="<?=$numLinhasMensagem?>" onkeypress="return infraLimitarTexto(this,event,<?=$numMaxMsg?>);" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" onblur="limitador();" onkeyup="limitador();" onkeydown="limitador();"><?=PaginaSEI::tratarHTML($_POST['txaMensagem']);?></textarea>
  			
  			<span id="numStrRestante">Caracteres restantes: <?=$numMaxMsg?></span>
     		
  			<label id="lblRetorno" for="chkRetorno" class="infraLabelOpcional">Não desejo receber retorno</label>
  			<input type="checkbox" id="chkRetorno" name="chkRetorno" class="infraCheckbox" value="N" <?=($_POST['chkRetorno']?'checked="checked"':'')?> />
  	 
  	   <label id="lblCodigo" for="txtCaptcha" accesskey="" class="infraLabelOpcional">Digite o código da imagem ao lado</label>
  	   <label id="lblCaptcha" accesskey="" class="infraLabelObrigatorio"><img src="/infra_js/infra_gerar_captcha.php?codetorandom=<?=$strCodigoParaGeracaoCaptcha;?>" alt="Não foi possível carregar a imagem de confirmação" /></label>
	     <input type="text" id="txtCaptcha" name="txtCaptcha" class="infraText" maxlength="4" value="" />
     
    	 <input id="sbmEnviar" name="sbmEnviar" type="submit" class="infraButton" title="Enviar" value="Enviar"/>       
    	 <label id="lblCamposObrigatorios" for="selTipo" class="infraLabelObrigatorio">* Campos Obrigatórios</label>
     </div>
		 
		 <input type="hidden" id="hdnCaptcha" name="hdnCaptcha" class="infraText" value="<?=hash('SHA512',InfraCaptcha::gerar($strCodigoParaGeracaoCaptcha));?>" />
		 <input type="hidden" id="hdnFlagFormulario" name="hdnFlagFormulario" class="infraText" value="<?=$_POST['hdnFlagFormulario']?>" />
		 		 
		</form>
<?  }   
  PaginaSEIExterna::getInstance()->montarAreaDebug();
  PaginaSEIExterna::getInstance()->fecharBody();
  PaginaSEIExterna::getInstance()->fecharHtml();
?>