<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 04/10/2012 - CRIADO POR MKR
*
*
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
  
  $arrComandos = array();
  
  switch($_GET['acao']){
    case 'indexar':
      
      $strTitulo = 'Indexação';

      $objIndexacaoRN = new IndexacaoRN();

      if (isset($_GET['acao_interna'])){
        
        switch($_GET['acao_interna']){
          
          case 'gerar_indexacao_completa':
            PaginaSEI::getInstance()->prepararBarraProgresso2($strTitulo);
            try{

              $objIndexacaoDTO = new IndexacaoDTO();
              $objIndexacaoDTO->setDtaIndexacao($_POST['txtDtaInicialCompleta']);


              $objIndexacaoRN->gerarIndexacaoCompleta($objIndexacaoDTO);

            }catch(Exception $e){
              PaginaSEI::getInstance()->processarExcecao($e);
            }
            PaginaSEI::getInstance()->finalizarBarraProgresso2(null, false);
            break;
            
          case 'gerar_indexacao_parcial':
            PaginaSEI::getInstance()->prepararBarraProgresso2($strTitulo);
            try{

              $objIndexacaoDTO = new IndexacaoDTO();
              $objIndexacaoDTO->setDthInicio($_POST['txtDthInicial']);
              $objIndexacaoDTO->setDthFim($_POST['txtDthFinal']);

              $objIndexacaoRN->gerarIndexacaoParcial($objIndexacaoDTO);

            }catch(Exception $e){
              PaginaSEI::getInstance()->processarExcecao($e);
            }
            PaginaSEI::getInstance()->finalizarBarraProgresso2(null, false);
            break;

          case 'gerar_indexacao_processo':
            PaginaSEI::getInstance()->prepararBarraProgresso2($strTitulo);
            try{

              $objIndexacaoDTO = new IndexacaoDTO();
              $objIndexacaoDTO->setStrProtocoloFormatadoPesquisa($_POST['txtProtocoloFormatado']);
              $objIndexacaoRN->gerarIndexacaoProcesso($objIndexacaoDTO);

            }catch(Exception $e){
              PaginaSEI::getInstance()->processarExcecao($e);
            }
            PaginaSEI::getInstance()->finalizarBarraProgresso2(null, false);
            break;

            case 'gerar_indexacao_bases_conhecimento':
              PaginaSEI::getInstance()->prepararBarraProgresso2($strTitulo);
              try{

                $objIndexacaoRN->gerarIndexacaoBasesConhecimento();

              }catch(Exception $e){
                PaginaSEI::getInstance()->processarExcecao($e);
              }
              PaginaSEI::getInstance()->finalizarBarraProgresso2(null, false);
              break;
            
            case 'gerar_indexacao_publicacao':
              PaginaSEI::getInstance()->prepararBarraProgresso2($strTitulo);
              try{

                $objIndexacaoRN->gerarIndexacaoPublicacao();

              }catch(Exception $e){
                PaginaSEI::getInstance()->processarExcecao($e);
              }
              PaginaSEI::getInstance()->finalizarBarraProgresso2(null, false);
              break;

          case 'gerar_indexacao_controle_interno':
            PaginaSEI::getInstance()->prepararBarraProgresso2($strTitulo);
            try{

              $objIndexacaoRN->gerarIndexacaoControleInterno();

            }catch(Exception $e){
              PaginaSEI::getInstance()->processarExcecao($e);
            }
            PaginaSEI::getInstance()->finalizarBarraProgresso2(null, false);
            break;

            default:
              throw new InfraException("Ação interna '".$_GET['acao_interna']."' não reconhecida.");
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
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema().' - Indexação');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
?>


<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function OnSubmitForm() {
  return validarForm();
}

function validarForm() {
  return true;
}

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo);
?>
<form id="frmIndexacao" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?
  //PaginaSEI::getInstance()->montarBarraLocalizacao('Importar Sistema');
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->abrirAreaDados('85em');
  ?>
  <label class="infraLabelOpcional">Data inicial:</label><br />
  <input type="text" id="txtDtaInicialCompleta" name="txtDtaInicialCompleta" value="<?=PaginaSEI::tratarHTML($_POST['txtDtaInicialCompleta'])?>" onkeypress="return infraMascaraData(this, event)" class="infraText" /> (dd/mm/aaaa) <br /><br />
	<button type="button" name="btnGerarIndexacaoCompleta" onclick="infraAbrirBarraProgresso(this.form,'<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_interna=gerar_indexacao_completa')?>', 600, 250);" value="Gerar Indexação Completa de Processos e Documentos" class="infraButton">Gerar Indexação Completa de Processos e Documentos</button><br /><br />

  <hr /><br />

  <label class="infraLabelOpcional">Processo:</label><br />
  <input type="text" id="txtProtocoloFormatado" name="txtProtocoloFormatado" value="<?=PaginaSEI::tratarHTML($_POST['txtProtocoloFormatado'])?>" class="infraText" style="width:200px" /><br /><br />
  <button type="button" name="btnGerarIndexacaoProcesso" onclick="infraAbrirBarraProgresso(this.form,'<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_interna=gerar_indexacao_processo')?>', 600, 200);" value="Gerar Indexação de Processo e Documentos" class="infraButton">Gerar Indexação de Processo e Documentos</button><br /><br />

	<hr /><br />

  <button type="button" name="btnGerarIndexacaoPublicacao" onclick="infraAbrirBarraProgresso(this.form,'<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_interna=gerar_indexacao_publicacao')?>', 600, 250);" value="Gerar Indexação Publicações" class="infraButton" style="visibility:visible">Gerar Indexação de Publicações</button><br /><br />

  <hr /><br />

  <button type="button" name="btnGerarIndexacaoBasesConhecimento" onclick="infraAbrirBarraProgresso(this.form,'<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_interna=gerar_indexacao_bases_conhecimento')?>', 600, 200);" value="Gerar Indexação Bases de Conhecimento" class="infraButton" style="visibility:visible">Gerar Indexação de Bases de Conhecimento</button><br /><br />

  <hr /><br />

  <button type="button" name="btnGerarIndexacaoControleInterno" onclick="infraAbrirBarraProgresso(this.form,'<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_interna=gerar_indexacao_controle_interno')?>', 600, 200);" value="Gerar Indexação Controle Interno" class="infraButton" style="visibility:visible">Gerar Indexação Controle Interno</button><br /><br />

  <hr /><br />

  <label class="infraLabelOpcional">Data/Hora inicial:</label><br />
  <input type="text" id="txtDthInicial" name="txtDthInicial" value="<?=PaginaSEI::tratarHTML($_POST['txtDthInicial'])?>" onkeypress="return infraMascara(this, event, '##/##/#### ##:##')" class="infraText" /> (dd/mm/aaaa hh::mm) <br /><br />
  <label class="infraLabelOpcional">Data/Hora final:</label> <br />
  <input type="text" id="txtDthFinal" name="txtDthFinal" value="<?=PaginaSEI::tratarHTML($_POST['txtDthFinal'])?>" onkeypress="return infraMascara(this, event, '##/##/#### ##:##')" class="infraText" /> (dd/mm/aaaa hh::mm) <br /><br />
  <button type="button" name="btnGerarIndexacaoParcial" onclick="infraAbrirBarraProgresso(this.form,'<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_interna=gerar_indexacao_parcial')?>', 600, 200);" value="Gerar Indexação Parcial de Processos, Documentos e Publicações" class="infraButton">Gerar Indexação Parcial de Processos, Documentos e Publicações</button><br /><br />


  <?
  PaginaSEI::getInstance()->fecharAreaDados();
  PaginaSEI::getInstance()->montarAreaDebug();
  //PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>