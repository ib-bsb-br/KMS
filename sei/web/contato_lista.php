<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
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

  PaginaSEI::getInstance()->prepararSelecao('contato_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  PaginaSEI::getInstance()->salvarCamposPost(array('txtPalavrasPesquisaContatos', 'hdnIdContatoAssociado', 'txtContatoAssociado', 'selGrupoContato', 'selTipoContato'));
  
	//link de acesso que preenche os critérios
  if (isset($_GET['palavras_pesquisa'])){
    PaginaSEI::getInstance()->salvarCampo('txtPalavrasPesquisaContatos',$_GET['palavras_pesquisa']);
  }
  
  if (isset($_GET['id_tipo_contato'])){
    PaginaSEI::getInstance()->salvarCampo('selTipoContato',$_GET['id_tipo_contato']);
  }

  switch($_GET['acao']){
    case 'contato_excluir':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjContatoDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objContatoDTO = new ContatoDTO();
          $objContatoDTO->setNumIdContato($arrStrIds[$i]);
          $arrObjContatoDTO[] = $objContatoDTO;
        }
        $objContatoRN = new ContatoRN();
        $objContatoRN->excluirRN0326($arrObjContatoDTO);
        PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'contato_desativar':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjContatoDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objContatoDTO = new ContatoDTO();
          $objContatoDTO->setNumIdContato($arrStrIds[$i]);
          $arrObjContatoDTO[] = $objContatoDTO;
        }
        $objContatoRN = new ContatoRN();
        $objContatoRN->desativarRN0451($arrObjContatoDTO);
        PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'contato_reativar':
      $strTitulo = 'Reativar Contato';
      if ($_GET['acao_confirmada']=='sim'){
        try{
          $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
          $arrObjContatoDTO = array();
          for ($i=0;$i<count($arrStrIds);$i++){
            $objContatoDTO = new ContatoDTO();
            $objContatoDTO->setNumIdContato($arrStrIds[$i]);
            $arrObjContatoDTO[] = $objContatoDTO;
          }
          $objContatoRN = new ContatoRN();
          $objContatoRN->reativarRN0452($arrObjContatoDTO);
          PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        } 
        header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
        die;
      } 
      break;      

    case 'contato_selecionar':

      $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Contato','Selecionar Contatos');

      //Se cadastrou alguem
      if ($_GET['acao_origem']=='contato_cadastrar'){
        if (isset($_GET['id_contato'])){
          PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_contato']);
        }
      }
      break;

    case 'contato_listar':
      $strTitulo = 'Contatos';

      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $objTipoContatoRN = new TipoContatoRN();


  $objPesquisaTipoContatoDTO = new PesquisaTipoContatoDTO();
  $objPesquisaTipoContatoDTO->setStrStaAcesso(TipoContatoRN::$TA_CONSULTA_RESUMIDA);
  $arrIdTipoContatoAcessoConsulta = $objTipoContatoRN->pesquisarAcessoUnidade($objPesquisaTipoContatoDTO);

  $objPesquisaTipoContatoDTO = new PesquisaTipoContatoDTO();
  $objPesquisaTipoContatoDTO->setStrStaAcesso(TipoContatoRN::$TA_ALTERACAO);
  $arrIdTipoContatoAcessoAlteracao = $objTipoContatoRN->pesquisarAcessoUnidade($objPesquisaTipoContatoDTO);

  $objContatoDTO = new ContatoDTO();
  $objContatoDTO->retNumIdContato();
  $objContatoDTO->retNumIdContatoAssociado();
  $objContatoDTO->retNumIdTipoContato();
  $objContatoDTO->retNumIdTipoContatoAssociado();
  $objContatoDTO->retStrNomeContatoAssociado();
  $objContatoDTO->retStrSiglaContatoAssociado();
  $objContatoDTO->retStrSinAtivoContatoAssociado();
  $objContatoDTO->retStrNome();
  $objContatoDTO->retStrSigla();
  $objContatoDTO->retStrEmail();
  $objContatoDTO->retStrExpressaoVocativoCargo();
  $objContatoDTO->retStrExpressaoTratamentoCargo();
  $objContatoDTO->retStrExpressaoCargo();
  $objContatoDTO->retStrStaNatureza();
  $objContatoDTO->retStrStaNaturezaContatoAssociado();
  $objContatoDTO->retStrSinSistemaTipoContato();

  $objContatoDTO->adicionarCriterio(array('StaAcessoTipoContato', 'IdTipoContato'),
                                    array(InfraDTO::$OPER_DIFERENTE, InfraDTO::$OPER_IN),
                                    array(TipoContatoRN::$TA_NENHUM, $arrIdTipoContatoAcessoConsulta),
                                    InfraDTO::$OPER_LOGICO_OR);

  $objContatoDTO->setStrSinAtivoTipoContato('S');

  $objContatoDTO->setOrdStrNomeContatoAssociado(InfraDTO::$TIPO_ORDENACAO_ASC);
  $objContatoDTO->setOrdStrStaNaturezaContatoAssociado(InfraDTO::$TIPO_ORDENACAO_DESC);
  $objContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

  $arrComandos = array();
  $arrComandos[] = '<button type="submit" accesskey="P" id="btnPesquisar" name="btnPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

  if (PaginaSEI::getInstance()->isBolPaginaSelecao()){
    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
  }

  if ($_GET['acao'] == 'contato_listar' || PaginaSEI::getInstance()->isBolPaginaSelecao()){
    $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('contato_cadastrar');
    if ($bolAcaoCadastrar){
      $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=contato_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
    }
  }

  if ($_GET['acao'] == 'contato_reativar'){
    //Lista somente inativos
    $objContatoDTO->setBolExclusaoLogica(false);
    $objContatoDTO->setStrSinAtivo('N');
  }

  $objInfraParametro = new InfraParametro(BancoSEI::getInstance());

  $numIdGrupoContato = null;
  if (isset($_GET['id_grupo_contato'])){
    $numIdGrupoContato = $_GET['id_grupo_contato'];
    $objContatoDTO->setNumIdGrupoContato($numIdGrupoContato);
  }else{

    $strPalavrasPesquisa = PaginaSEI::getInstance()->recuperarCampo('txtPalavrasPesquisaContatos');
    if ($strPalavrasPesquisa!=''){
      $objContatoDTO->setStrPalavrasPesquisa($strPalavrasPesquisa);
    }

    $numIdContatoAssociadoPesquisa = PaginaSEI::getInstance()->recuperarCampo('hdnIdContatoAssociado');
    $strNomeContatoAssociadoPesquisa = PaginaSEI::getInstance()->recuperarCampo('txtContatoAssociado');
    if ($numIdContatoAssociadoPesquisa!=''){
      $objContatoDTO->setNumIdContatoAssociado($numIdContatoAssociadoPesquisa);
    }

    $numTipoContato = PaginaSEI::getInstance()->recuperarCampo('selTipoContato');
    if ($numTipoContato!=''){
      $objContatoDTO->setNumIdTipoContato($numTipoContato);
    }

    $numIdGrupoContato = PaginaSEI::getInstance()->recuperarCampo('selGrupoContato');
    if ($numIdGrupoContato!=''){
      $objContatoDTO->setNumIdGrupoContato($numIdGrupoContato);
    }
  }

  PaginaSEI::getInstance()->prepararPaginacao($objContatoDTO, 100);
  
  $objContatoRN = new ContatoRN();
  $arrObjContatoDTO = $objContatoRN->pesquisarRN0471($objContatoDTO);
  
  PaginaSEI::getInstance()->processarPaginacao($objContatoDTO);

  $numRegistros = count($arrObjContatoDTO);

  if ($numRegistros > 0){

    $bolCheck = false;

    if (PaginaSEI::getInstance()->isBolPaginaSelecao()){
      $bolAcaoReativar = false;
      $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('contato_consultar');
      $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('contato_alterar');
      $bolAcaoImprimir = false;
      $bolAcaoEtiquetas = false;
      $bolAcaoExcluir = false;
      $bolAcaoDesativar = false;
      $bolAcaoCadastrar = false;
      $bolCheck = true;
    }else if ($_GET['acao']=='contato_reativar'){
      $bolAcaoReativar = SessaoSEI::getInstance()->verificarPermissao('contato_reativar');
      $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('contato_consultar');
      $bolAcaoAlterar = false;
      $bolAcaoImprimir = true;
      $bolAcaoEtiquetas = false;
      $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('contato_excluir');
      $bolAcaoDesativar = false;
      $bolAcaoCadastrar = false;
    }else{
      $bolAcaoReativar = false;
      $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('contato_consultar');
      $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('contato_alterar');
      $bolAcaoImprimir = true;
      $bolAcaoEtiquetas = SessaoSEI::getInstance()->verificarPermissao('contato_imprimir_etiquetas');
      $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('contato_excluir');
      $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('contato_desativar');
      $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('contato_cadastrar');
    }

    if ($bolAcaoExcluir){
      $bolCheck = true;
      $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=contato_excluir&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoDesativar){
      $bolCheck = true;
      $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=contato_desativar&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoReativar){
      $bolCheck = true;
      $strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=contato_reativar&acao_origem='.$_GET['acao'].'&acao_confirmada=sim');
    }
    
    if ($bolAcaoReativar){
      $bolCheck = true;
      $strLinkReativarContexto = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=contato_reativar&acao_origem='.$_GET['acao'].'&acao_confirmada=sim');
    }
    
    if ($bolAcaoImprimir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
    }

    if ($bolAcaoEtiquetas){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="Q" id="btnEtiquetas" value="Etiquetas" onclick="acaoEtiquetasMultipla();" class="infraButton">Eti<span class="infraTeclaAtalho">q</span>uetas</button>';
      $strLinkEtiquetas = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=contato_imprimir_etiquetas&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']);
    }

    $strCaptionTabela = '';
    if ($_GET['acao']=='contato_reativar'){
      $strSumarioTabela = 'Tabela de Contatos Inativos.';
      $strCaptionTabela .= 'Contatos Inativos';
    }else{
      $strSumarioTabela = 'Tabela de Contatos.';
      $strCaptionTabela .= 'Contatos';
    }
    
    $strResultado = '';
    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';

    $strResultado .= '<tr>';
    if ($bolCheck) {
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
    }
    $strResultado .= '<th class="infraTh" width="50%">'.$strCaptionTabela.'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="15%">Ações</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    
    $arrContatosExibidos = array();
    $n = 0;
    
    for($i=0;$i<$numRegistros;$i++){

      $dto = $arrObjContatoDTO[$i];

      if($_GET['acao']=='contato_listar' && $dto->getNumIdContato()!=$dto->getNumIdContatoAssociado() && !in_array($dto->getNumIdContatoAssociado(),$arrContatosExibidos)){

        $strCssTr = '<tr class="infraTrEscura">';

        $numIdContato = $dto->getNumIdContatoAssociado();
        $numIdContatoAssociado = $dto->getNumIdContatoAssociado();
        $numIdTipoContato = $dto->getNumIdTipoContatoAssociado();
        $strSigla = $dto->getStrSiglaContatoAssociado();
        $strNome = $dto->getStrNomeContatoAssociado();
        $strStaNatureza = $dto->getStrStaNaturezaContatoAssociado();
        $bolTipoContatoUnidadeAlteracao = in_array($dto->getNumIdTipoContatoAssociado(), $arrIdTipoContatoAcessoAlteracao);
        $i--;

      }else {

        if (in_array($dto->getNumIdContato(), $arrContatosExibidos)) {
          continue;
        }

        $numIdContato = $dto->getNumIdContato();
        $numIdContatoAssociado = $dto->getNumIdContatoAssociado();
        $numIdTipoContato = $dto->getNumIdTipoContato();
        $strSigla = $dto->getStrSigla();
        $strNome = $dto->getStrNome();
        $strStaNatureza = $dto->getStrStaNatureza();
        $bolTipoContatoUnidadeAlteracao = in_array($dto->getNumIdTipoContato(), $arrIdTipoContatoAcessoAlteracao);

        if ($strStaNatureza == ContatoRN::$TN_PESSOA_JURIDICA && $numIdContatoAssociado==$numIdContato) {
          $strCssTr = '<tr class="infraTrEscura">';
        } else {
          $strCssTr = '<tr class="infraTrClara">';
        }

      }

      $strResultado .= $strCssTr;

      $strTitle = '';
      
      $strNomeSigla = ContatoINT::formatarNomeSiglaRI1224($strNome, $strSigla);

      $strTitle = $strNomeSigla;

      $strBalao = '';
      if($strStaNatureza==ContatoRN::$TN_PESSOA_FISICA) {

        if (!InfraString::isBolVazia($dto->getStrExpressaoCargo())) {
          $strBalao .= $dto->getStrExpressaoCargo()."\n";
        }

        if (!InfraString::isBolVazia($dto->getStrExpressaoTratamentoCargo())) {
          $strBalao .= $dto->getStrExpressaoTratamentoCargo() ."\n";
        }

        if (!InfraString::isBolVazia($dto->getStrExpressaoVocativoCargo())) {
          $strBalao .= $dto->getStrExpressaoVocativoCargo();
        }
      }

      if ($bolCheck){
        $strResultado .= '<td align="center">'.PaginaSEI::getInstance()->getTrCheck($n,$numIdContato,$strTitle).'</td>';
      }

     	$strResultado .= '<td>';

      if($strStaNatureza==ContatoRN::$TN_PESSOA_JURIDICA && $numIdContatoAssociado==$numIdContato){
        $strResultado .= '<b>';
      }else if ($dto->getNumIdContato() != $dto->getNumIdContatoAssociado() && in_array($dto->getNumIdContatoAssociado(),$arrContatosExibidos)) {
        $strResultado .= '&nbsp;&nbsp;&nbsp;&nbsp;';
      }

      $strResultado .= PaginaSEI::tratarHTML($strNomeSigla);

      if($strStaNatureza==ContatoRN::$TN_PESSOA_JURIDICA && $numIdContatoAssociado==$numIdContato){
        $strResultado .= '</b>';
      }
      
      $strResultado .= '</td>';        
      $strResultado .= '<td align="center">';
      
      $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($n++,$numIdContato);
      
      $strId = $numIdContato;
      $strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript($strNomeSigla);

      //se aceita contatos
      if($bolAcaoCadastrar && $bolTipoContatoUnidadeAlteracao && $dto->getStrSinSistemaTipoContato()=='N' && $strStaNatureza==ContatoRN::$TN_PESSOA_JURIDICA && $numIdContatoAssociado==$numIdContato &&
          ($_GET['acao']=='contato_selecionar' || $_GET['acao']=='contato_listar')){
        $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=contato_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_tipo_contato='.$numIdTipoContato.'&id_contato_associado='.$numIdContato.'&sta_natureza='.ContatoRN::$TN_PESSOA_FISICA.'&sin_endereco_associado=S').'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="/infra_css/imagens/mais.gif" title="Adicionar Contato associado com esta Pessoa Jurídica" alt="Adicionar Contato associado com esta Pessoa Jurídica" class="infraImg" /></a>&nbsp;';
      }

      if($strStaNatureza==ContatoRN::$TN_PESSOA_FISICA && trim($strBalao) != ''){
        $strResultado .= '<a href="javascript:void(0);" '.PaginaSEI::montarTitleTooltip($strBalao).'><img src="/infra_css/imagens/balao.gif" class="infraImg" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'" /></a>&nbsp;';
      }

      if ($bolAcaoConsultar){
        $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=contato_consultar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_tipo_contato='.$numIdTipoContato.'&id_contato='.$numIdContato).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/consultar.gif" title="Consultar Contato" alt="Consultar Contato" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoAlterar && $bolTipoContatoUnidadeAlteracao){
        $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=contato_alterar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_tipo_contato='.$numIdTipoContato.'&id_contato='.$numIdContato).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/alterar.gif" title="Alterar Contato" alt="Alterar Contato" class="infraImg" /></a>&nbsp;';
      }

      if($bolAcaoDesativar && $bolTipoContatoUnidadeAlteracao && $dto->getStrSinSistemaTipoContato()=='N' && $_GET['acao']=='contato_listar'){
        $strResultado .= '<a href="#ID-'.$strId.'"  onclick="acaoDesativar(\''.$strId.'\',\''.$strDescricao.'\',\''.$strLinkDesativar.'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/desativar.gif" title="Desativar Contato" alt="Desativar Contato" class="infraImg" /></a>&nbsp;';
      }

      if($bolAcaoReativar && $bolTipoContatoUnidadeAlteracao && $dto->getStrSinSistemaTipoContato()=='N' && $_GET['acao']=='contato_reativar'){
        $strResultado .= '<a href="#ID-'.$strId.'" onclick="acaoReativar(\''.$strId.'\',\''.$strDescricao.'\',\''.$strLinkReativar.'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/reativar.gif" title="Reativar Contato" alt="Reativar Contato" class="infraImg" /></a>&nbsp;';
      }

      if($bolAcaoExcluir && $bolTipoContatoUnidadeAlteracao && $dto->getStrSinSistemaTipoContato()=='N' && ($_GET['acao']=='contato_listar' || $_GET['acao']=='contato_reativar')){
        $strResultado .= '<a href="#ID-'.$strId.'"  onclick="acaoExcluir(\''.$strId.'\',\''.$strDescricao.'\',\''.$strLinkExcluir.'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/excluir.gif" title="Excluir Contato" alt="Excluir Contato" class="infraImg" /></a>&nbsp;';
      }

      $strResultado .= '</td></tr>'."\n";

      $arrContatosExibidos[] = $numIdContato;
    }
    $strResultado .= '</table>';
  }
  
  if (PaginaSEI::getInstance()->isBolPaginaSelecao()){
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }else{
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }

  $strLinkAjaxAutoCompletarContatoAssociado = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=contato_auto_completar_associado');

  $strItensSelGrupoContato = GrupoContatoINT::ConjuntoPorUnidadeRI0515('null','&nbsp;',$numIdGrupoContato);
  $strItensSelTipoContato = TipoContatoINT::montarSelectNomeRI0518('null','&nbsp;',$numTipoContato);

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

#lblPalavrasPesquisaContatos {position:absolute;left:0%;top:0%;width:47%;}
#txtPalavrasPesquisaContatos {position:absolute;left:0%;top:18%;width:47%;}

#lblContatoAssociado {position:absolute;left:50%;top:0%;width:47%;}
#txtContatoAssociado {position:absolute;left:50%;top:18%;width:47%;}

#lblGrupoContato {position:absolute;left:0%;top:50%;width:48%;}
#selGrupoContato {position:absolute;left:0%;top:68%;width:48%;}

#lblTipoContato {position:absolute;left:50%;top:50%;width:48%;}
#selTipoContato {position:absolute;left:50%;top:68%;width:48%;}


<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
//<script>

var objAutoCompletarContatoAssociado = null;

function inicializar(){

  if ('<?=PaginaSEI::getInstance()->isBolPaginaSelecao()?>'!=''){
    infraReceberSelecao();
 	}
 	
	if (infraGetAnchor()==null){
	  try{
 	    document.getElementById('txtPalavrasPesquisaContatos').focus();
	  }catch(controleIndisponivel){}
 	}

  objAutoCompletarContatoAssociado = new infraAjaxAutoCompletar('hdnIdContatoAssociado','txtContatoAssociado','<?=$strLinkAjaxAutoCompletarContatoAssociado?>');
  //objAutoCompletarContatoAssociado.maiusculas = true;
  //objAutoCompletarContatoAssociado.mostrarAviso = true;
  //objAutoCompletarContatoAssociado.tempoAviso = 1000;
  //objAutoCompletarContatoAssociado.tamanhoMinimo = 3;
  objAutoCompletarContatoAssociado.limparCampo = false;
  //objAutoCompletarContatoAssociado.bolExecucaoAutomatica = false;

  objAutoCompletarContatoAssociado.prepararExecucao = function(){
  return 'palavras_pesquisa='+document.getElementById('txtContatoAssociado').value;
  };

  objAutoCompletarContatoAssociado.processarResultado = function(id,descricao,complemento){
    if (id!=''){
      document.getElementById('hdnIdContatoAssociado').value = id;
      document.getElementById('txtContatoAssociado').value = descricao;
    }
  }
  objAutoCompletarContatoAssociado.selecionar('<?=$numIdContatoAssociadoPesquisa?>','<?=PaginaSEI::getInstance()->formatarParametrosJavaScript($strNomeContatoAssociadoPesquisa);?>');

  infraEfeitoTabelas();
}

<? if ($bolAcaoDesativar){ ?>
function acaoDesativar(id,desc,link){
  if (confirm("Confirma desativação do contato \"" + desc + "\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmContatoLista').action=link;
    document.getElementById('frmContatoLista').submit();
  }
}

function acaoDesativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum item selecionado.');
    return;
  }
  if (confirm("Confirma desativação dos itens selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmContatoLista').action=link;
    document.getElementById('frmContatoLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoReativar){ ?>
function acaoReativar(id,desc,link){
  if (confirm("Confirma reativação do contato \"" + desc + "\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmContatoLista').action=link;
    document.getElementById('frmContatoLista').submit();
  }
}

function acaoReativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum item selecionado.');
    return;
  }
  if (confirm("Confirma reativação dos itens selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmContatoLista').action=link;
    document.getElementById('frmContatoLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoExcluir){ ?>
function acaoExcluir(id,desc,link){
  if (confirm("Confirma exclusão do contato \"" + desc + "\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmContatoLista').action=link;
    document.getElementById('frmContatoLista').submit();
  }
}

function acaoExclusaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum item selecionado.');
    return;
  }
  if (confirm("Confirma exclusão dos itens selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmContatoLista').action=link;
    document.getElementById('frmContatoLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoEtiquetas){ ?>
function acaoEtiquetasMultipla(){
 if (document.getElementById('hdnInfraItensSelecionados').value==''){
   alert('Nenhum item selecionado.');
   return;
 }
 document.getElementById('hdnInfraItemId').value='';
 document.getElementById('frmContatoLista').action='<?=$strLinkEtiquetas?>';
 document.getElementById('frmContatoLista').submit();
}
<? } ?>

function OnSubmitForm() {
  return true;
}

//</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmContatoLista" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?
  //PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->abrirAreaDados('10em');
  //<input type="text" id="txtPalavrasPesquisaContatos" name="txtPalavrasPesquisaContatos" onkeypress="return teste(event.keyCode)" class="infraText" value="<?=$objPesquisaContatoDTO->get
  ?>

  <label id="lblPalavrasPesquisaContatos" for="txtPalavrasPesquisaContatos" accesskey="" class="infraLabelOpcional">Palavras-chave para pesquisa:</label>
  <input type="text" id="txtPalavrasPesquisaContatos" name="txtPalavrasPesquisaContatos" class="infraText" value="<?=PaginaSEI::tratarHTML($strPalavrasPesquisa);?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />

  <label id="lblContatoAssociado" class="infraLabel" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">Pessoa Jurídica Associada:</label>
  <input type="text" id="txtContatoAssociado" name="txtContatoAssociado" class="infraText" value="<?=PaginaSEI::tratarHTML($strNomeContatoAssociadoPesquisa)?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
  <input type="hidden" id="hdnIdContatoAssociado" name="hdnIdContatoAssociado" value="<?=$numIdContatoAssociadoPesquisa?>" />

  <label id="lblGrupoContato" for="selGrupoContato" class="infraLabelOpcional">Grupo:</label>
  <select id="selGrupoContato" name="selGrupoContato" onchange="this.form.submit()" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
    <?=$strItensSelGrupoContato?>
  </select>

  <label id="lblTipoContato" for="selTipoContato" accesskey="" class="infraLabelOpicional">Tipo:</label>
  <select id="selTipoContato" name="selTipoContato" onchange="this.form.submit()" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" >
    <?=$strItensSelTipoContato?>
  </select>

  <?
  PaginaSEI::getInstance()->fecharAreaDados();
  PaginaSEI::getInstance()->montarAreaTabela($strResultado,$numRegistros);
  PaginaSEI::getInstance()->montarAreaDebug();
  PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>