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
  //InfraDebug::getInstance()->setBolLigado(false);
  //InfraDebug::getInstance()->setBolDebugInfra(false);
  //InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  //PaginaSEI::getInstance()->salvarCamposPost(array('selTipoProcedimento'));

  $arrNumIdSerie = array();
  if(isset($_POST['selSerie'])){
    $arrNumIdSerie = $_POST['selSerie'];
    if (!is_array($arrNumIdSerie)){
      $arrNumIdSerie = array($arrNumIdSerie);
    }
  }

  $strParametros = '';
  if(isset($_GET['arvore'])){
    PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
    $strParametros .= '&arvore='.$_GET['arvore'];
  }
  
  if (isset($_GET['id_procedimento'])){
    $dblIdProcedimento=$_GET['id_procedimento'];
    $strParametros .= '&id_procedimento='.$dblIdProcedimento;
  }

  $arrComandos = array();
  switch($_GET['acao']){
    
    case 'procedimento_pesquisar':
      
      $strTitulo = 'Pesquisar no Processo';


      $strPalavrasPesquisa = $_POST['txtPesquisa'];
      $strLinkAjuda = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=pesquisa_solr_ajuda&acao_origem='.$_GET['acao']);

      if (!InfraString::isBolVazia($strPalavrasPesquisa) || count($arrNumIdSerie)) {

        try {

          $objPesquisaProtocoloSolrDTO = new PesquisaProtocoloSolrDTO();
          $objPesquisaProtocoloSolrDTO->setStrPalavrasChave($strPalavrasPesquisa);
          $objPesquisaProtocoloSolrDTO->setStrSinProcessosTramitacao(null);
          $objPesquisaProtocoloSolrDTO->setStrSinDocumentosGerados(null);
          $objPesquisaProtocoloSolrDTO->setStrSinDocumentosRecebidos(null);
          $objPesquisaProtocoloSolrDTO->setArrNumIdOrgao(array());
          $objPesquisaProtocoloSolrDTO->setNumIdContato(null);
          $objPesquisaProtocoloSolrDTO->setStrSinInteressado(null);
          $objPesquisaProtocoloSolrDTO->setStrSinRemetente(null);
          $objPesquisaProtocoloSolrDTO->setStrSinDestinatario(null);
          $objPesquisaProtocoloSolrDTO->setNumIdAssinante(null);
          $objPesquisaProtocoloSolrDTO->setStrDescricao(null);
          $objPesquisaProtocoloSolrDTO->setStrObservacao(null);
          $objPesquisaProtocoloSolrDTO->setNumIdAssunto(null);
          $objPesquisaProtocoloSolrDTO->setNumIdUnidadeGeradora(null);
          $objPesquisaProtocoloSolrDTO->setStrProtocoloPesquisa(null);
          $objPesquisaProtocoloSolrDTO->setNumIdTipoProcedimento(null);

          if (count($arrNumIdSerie)) {
            $objPesquisaProtocoloSolrDTO->setNumIdSerie($arrNumIdSerie);
          }else{
            $objPesquisaProtocoloSolrDTO->setNumIdSerie(null);
          }

          $objPesquisaProtocoloSolrDTO->setStrNumero(null);
          $objPesquisaProtocoloSolrDTO->setStrStaTipoData(null);
          $objPesquisaProtocoloSolrDTO->setDtaInicio(null);
          $objPesquisaProtocoloSolrDTO->setDtaFim(null);
          $objPesquisaProtocoloSolrDTO->setNumIdUsuarioGerador1(null);
          $objPesquisaProtocoloSolrDTO->setNumIdUsuarioGerador2(null);
          $objPesquisaProtocoloSolrDTO->setNumIdUsuarioGerador3(null);
          $objPesquisaProtocoloSolrDTO->setNumInicioPaginacao($_POST['hdnInicio']);
          $objPesquisaProtocoloSolrDTO->setDblIdProcedimento($dblIdProcedimento);
          $objPesquisaProtocoloSolrDTO->setBolArvore(true);

          $strResultado = SolrProtocolo::executar($objPesquisaProtocoloSolrDTO);

        } catch (Exception $e) {
          PaginaSEI::getInstance()->setStrMensagem(SolrUtil::$MSG_ERRO_PESQUISA, InfraPagina::$TIPO_MSG_AVISO);
          LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        }
      }

      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $strOptionsSeries = SerieINT::montarSelectMultiploProcedimento($_GET['id_procedimento'],$arrNumIdSerie);

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
if(0){?><style><?}
?>
  #divGeral {height:7.5em;width:99%;overflow:visible;}

  #lblPesquisa {positsion:absolute;left:0%;top:10%;width:50%;display:none;}
  #txtPesquisa {position:absolute;left:0%;top:10%;width:80%;}
  #ancAjuda {position:absolute;left:82%;top:10%;}
  #sbmPesquisar {position:absolute;left:87%;top:11%;}

  #lblSerie {position:absolute;left:0%;top:45%;width:50%;}
  #selSerie, .multipleSelect {position:absolute;left:0%;top:70%;width:50%;}


  div#conteudo > div.barra {
  border-bottom: .1em solid #909090;
  font-size: 1.2em;
  margin: 0 0 .5em 0;
  padding: 0 0 .5em 0;
  text-align: right;
  }

  div#conteudo > div.paginas {
  border-top: .1em solid #909090;
  margin: 0 0 5em;
  padding: .5em 0 0 0;
  text-align: center;
  font-size: 1.2em;
  }

  div#conteudo > div.sem-resultado {
  font-size:1.2em;
  margin: .5em 0 0 0;
  }

  div#conteudo table {
  border-collapse: collapse;
  border-spacing: 0px;
  }

  div#conteudo > table {
  margin: 0 0 .5em;
  width: 100%;
  }

  table.resultado td {
  background: #f0f0f0;
  padding: .3em .5em;
  }

  div#conteudo > table > tbody > tr:first-child > td {
  background: #e0e0e0;
  }

  tr.resTituloRegistro td {
  background: #e0e0e0;
  }


  div#conteudo a.protocoloAberto,
  div#conteudo a.protocoloNormal{
  font-size:1.1em !important;
  }

  div#conteudo a.protocoloAberto:hover,
  div#conteudo a.protocoloNormal:hover{
  text-decoration:underline !important;
  }


  div#conteudo td.metatag > table {
  border-collapse: collapse;
  margin: 0px auto;
  white-space: nowrap;
  }

  div#conteudo td.metatag > table {
  text-align: left;
  width:75%;
  }

  div#conteudo td.metatag > table > tbody > tr > td {
  color: #333333;
  font-size: .9em;
  padding: 0 2em;
  width:30%;
  }


  div#conteudo td.metatag > table > tbody > tr > td:first-child {
  width:45%;
  }


  div#conteudo td.metatag > table > tbody > tr > td > b {
  color: #006600;
  font-weight: normal;
  }
  span.pequeno {
  font-size: .9em;
  }

  div#mensagem {
  background: #e0e0e0;
  border-color: #c0c0c0;
  border-style: solid;
  border-width: .1em;
  margin: 4em auto 0;
  padding: 2em;
  }

  div#mensagem > span.pequeno {
  color: #909090;
  font-size: .9em;
  }

  td.resTituloEsquerda img.arvore {
  margin: 0px 5px -3px 0px;
  }

  td.resTituloDireita {
  text-align:right;
  width:20%;
  }

  div.paginas, div.paginas * {
  font-size: 12px;
  }

  div.paginas b {
  font-weight: bold;
  }

  div.paginas a {
  border-bottom: 1px solid transparent;
  color: #000080;
  text-decoration: none;
  }

  div.paginas a:hover {
  border-bottom: 1px solid #000000;
  color: #800000;
  }

  td.resSnippet b {
  font-weight:bold;
  }

<?
if(0){?></style><?}
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
if(0){?><script><?}
?>

function inicializar(){

  $("#selSerie").multipleSelect({
    filter: false,
    minimumCountSelected: 1,
    selectAll: false,
  });

  document.getElementById('txtPesquisa').focus();
  infraEfeitoTabelas();
}

function OnSubmitForm() {

  if (infraTrim(document.getElementById('txtPesquisa').value)=='' && document.getElementById('selSerie').value==''){
    alert('Nenhum critério de pesquisa informado.');
    return false;
  }

  return true;
}

function navegar(inicio) {
  document.getElementById('hdnInicio').value = inicio;
  if (typeof(window.onSubmitForm)=='function' && !window.onSubmitForm()) {
    return;
  }
  document.getElementById('frmPesquisaProtocolo').submit();
}
<?
if(0){?></script><?}
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmPesquisaProtocolo" name="frmPesquisaProtocolo" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>">
  <br />
  <br />
  <div id="divGeral" class="infraAreaDados">
  
 	<label id="lblPesquisa" for="txtPesquisa" class="infraLabelObrigatorio">Descrição:</label>
  <input type="text" id="txtPesquisa" name="txtPesquisa" class="infraText" value="<?=PaginaSEI::tratarHTML($strPalavrasPesquisa)?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
  <a id="ancAjuda" href="<?=$strLinkAjuda?>" target="janAjuda" title="Ajuda para Pesquisa" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"><img src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/ajuda.gif" class="infraImg"/></a>

  <label id="lblSerie" for="selSerie" accesskey="" class="infraLabelOpcional">Tipos de documentos disponíveis neste processo:</label>
  <select multiple id="selSerie" name="selSerie[]" class="infraSelect multipleSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
    <?=$strOptionsSeries;?>
  </select>

  <input type="submit" id="sbmPesquisar" name="sbmPesquisar" value="Pesquisar" class="infraButton" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />

  </div>
  <div id="conteudo" style="width:99%;" class="infraAreaTabela">
  <?=$strResultado;?>
  </div>
  <input type="hidden" id="hdnInicio" name="hdnInicio" value="0" />
</form>
<?
PaginaSEI::getInstance()->montarAreaDebug();
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>