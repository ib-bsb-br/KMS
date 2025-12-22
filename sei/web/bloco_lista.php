<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 25/09/2009 - criado por fbv@trf4.gov.br
*
* Versão do Gerador de Código: 1.29.1
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

  PaginaSEI::getInstance()->prepararSelecao('bloco_selecionar_processo');
  PaginaSEI::getInstance()->prepararSelecao('bloco_selecionar_documento');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  PaginaSEI::getInstance()->salvarCamposPost(array('txtPalavrasPesquisaBloco','txtSiglaPesquisaBloco'));

  switch($_GET['acao']){
    case 'bloco_excluir':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjBlocoDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objBlocoDTO = new BlocoDTO();
          $objBlocoDTO->setNumIdBloco($arrStrIds[$i]);
          $arrObjBlocoDTO[] = $objBlocoDTO;
        }
        $objBlocoRN = new BlocoRN();
        $objBlocoRN->excluirRN1275($arrObjBlocoDTO);
        PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'bloco_disponibilizar':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjBlocoDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objBlocoDTO = new BlocoDTO();
          $objBlocoDTO->setNumIdBloco($arrStrIds[$i]);
          $arrObjBlocoDTO[] = $objBlocoDTO;
        }
        $objBlocoRN = new BlocoRN();
        $objBlocoRN->disponibilizar($arrObjBlocoDTO);
        PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($arrStrIds)));
      die;

    case 'bloco_cancelar_disponibilizacao':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjBlocoDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objBlocoDTO = new BlocoDTO();
          $objBlocoDTO->setNumIdBloco($arrStrIds[$i]);
          $arrObjBlocoDTO[] = $objBlocoDTO;
        }
        $objBlocoRN = new BlocoRN();
        $objBlocoRN->cancelarDisponibilizacao($arrObjBlocoDTO);
        PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($arrStrIds)));
      die;
      
    case 'bloco_retornar':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjBlocoDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objBlocoDTO = new BlocoDTO();
          $objBlocoDTO->setNumIdBloco($arrStrIds[$i]);
          $arrObjBlocoDTO[] = $objBlocoDTO;
        }
        $objBlocoRN = new BlocoRN();
        $objBlocoRN->retornar($arrObjBlocoDTO);
        PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'bloco_concluir':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjBlocoDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objBlocoDTO = new BlocoDTO();
          $objBlocoDTO->setNumIdBloco($arrStrIds[$i]);
          $arrObjBlocoDTO[] = $objBlocoDTO;
        }
        $objBlocoRN = new BlocoRN();
        $objBlocoRN->concluir($arrObjBlocoDTO);
        PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;
      
    case 'bloco_reabrir':
    	try {
    		$objBlocoDTO = new BlocoDTO();
    		$objBlocoDTO->setNumIdBloco($_GET['id_bloco']);
    		$objBlocoDTO->retNumIdBloco();
    		$objBlocoDTO->retStrStaEstado();
    		$objBlocoDTO->retStrDescricao();
    		$objBlocoRN = new BlocoRN();
    		$objBlocoDTO = $objBlocoRN->consultarRN1276($objBlocoDTO);
    		
        if ($objBlocoDTO===null){
          throw new InfraException("Registro não encontrado.");
        }
    		
    		$objBlocoRN->reabrir($objBlocoDTO);
    		PaginaSEI::getInstance()->setStrMensagem('Bloco "'.$_GET['id_bloco'].'" reaberto com sucesso.');
    	}catch(Exception $e){
    		PaginaSEI::getInstance()->processarExcecao($e);
    	}
    	header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($_GET['id_bloco'])));
    	die;

/* 
    case 'bloco_desativar':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjBlocoDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objBlocoDTO = new BlocoDTO();
          $objBlocoDTO->setNumIdBloco($arrStrIds[$i]);
          $arrObjBlocoDTO[] = $objBlocoDTO;
        }
        $objBlocoRN = new BlocoRN();
        $objBlocoRN->desativarRN1279($arrObjBlocoDTO);
        PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'bloco_reativar':
      $strTitulo = 'Reativar Blocos';
      if ($_GET['acao_confirmada']=='sim'){
        try{
          $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
          $arrObjBlocoDTO = array();
          for ($i=0;$i<count($arrStrIds);$i++){
            $objBlocoDTO = new BlocoDTO();
            $objBlocoDTO->setNumIdBloco($arrStrIds[$i]);
            $arrObjBlocoDTO[] = $objBlocoDTO;
          }
          $objBlocoRN = new BlocoRN();
          $objBlocoRN->reativarRN1280($arrObjBlocoDTO);
          PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        } 
        header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
        die;
      } 
      break;

 */
    case 'bloco_selecionar_processo':
      $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Bloco','Selecionar Blocos');

      //Se cadastrou alguem
      if ($_GET['acao_origem']=='bloco_interno_cadastrar' ||
          $_GET['acao_origem']=='bloco_reuniao_cadastrar'){    
              if (isset($_GET['id_bloco'])){
          PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_bloco']);
        }
      }
      break;
      
    case 'bloco_selecionar_documento':
      $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Bloco de Assinatura','Selecionar Blocos de Assinatura');

      //Se cadastrou alguem
      if ($_GET['acao_origem']=='bloco_assinatura_cadastrar' ||
          $_GET['acao_origem']=='bloco_interno_cadastrar' ||
          $_GET['acao_origem']=='bloco_reuniao_cadastrar'){    
        
        if (isset($_GET['id_bloco'])){
          PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_bloco']);
        }
      }
      break;

    case 'bloco_assinatura_listar':
    	$strTitulo = 'Blocos de Assinatura';
      break;

    case 'bloco_interno_listar':
    	$strTitulo = 'Blocos Internos';
      break;

    case 'bloco_reuniao_listar':
      $strTitulo = 'Blocos de Reunião';
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }
  
  $arrComandos = array();
  
  if (PaginaSEI::getInstance()->isBolPaginaSelecao()){
    $arrComandos[] = '<button type="button" accesskey="O" id="btnTransportarSelecao" value="OK" onclick="selecionar();" class="infraButton" style="width:8em;"><span class="infraTeclaAtalho">O</span>K</button>';
  }
  
  $arrComandos[] = '<button type="button" onclick="pesquisar();" accesskey="P" id="btnPesquisar" name="btnPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';


  $objBlocoDTO = new BlocoDTO();
  $objBlocoDTO->retNumIdBloco();
  $objBlocoDTO->retNumIdUnidade();
  $objBlocoDTO->retStrDescricao();
  $objBlocoDTO->retStrStaTipo();
  //$objBlocoDTO->retStrAnotacao();
  //$objBlocoDTO->retStrIdxBloco();
  $objBlocoDTO->retStrStaEstadoDescricao();
  $objBlocoDTO->retStrTipoDescricao();
  $objBlocoDTO->retStrSiglaUnidade();
  $objBlocoDTO->retStrDescricaoUnidade();
  $objBlocoDTO->retStrSinVazio();
  $objBlocoDTO->retArrObjRelBlocoUnidadeDTO();
    
  if(($_GET['acao']=='bloco_assinatura_listar')){
    $objBlocoDTO->setStrStaTipo(BlocoRN::$TB_ASSINATURA);
  }else if(($_GET['acao']=='bloco_interno_listar')){
    $objBlocoDTO->setStrStaTipo(BlocoRN::$TB_INTERNO);
  }else if(($_GET['acao']=='bloco_reuniao_listar')){
    $objBlocoDTO->setStrStaTipo(BlocoRN::$TB_REUNIAO);
  }else if($_GET['acao']=='bloco_selecionar_processo'){
  	$objBlocoDTO->setStrStaTipo(array(BlocoRN::$TB_REUNIAO,BlocoRN::$TB_INTERNO),InfraDTO::$OPER_IN);
  }else if($_GET['acao']=='bloco_selecionar_documento'){
    $objBlocoDTO->setStrStaTipo(BlocoRN::$TB_ASSINATURA);
  }
  
  if (PaginaSEI::getInstance()->isBolPaginaSelecao()){
  	$objBlocoDTO->setStrStaEstado(array(BlocoRN::$TE_ABERTO,BlocoRN::$TE_RETORNADO),InfraDTO::$OPER_IN);
  }else{
    if (!isset($_POST['hdnPesquisar']) || $_POST['hdnPesquisar']!='1'){
      $objBlocoDTO->setStrStaEstado(BlocoRN::$TE_CONCLUIDO,InfraDTO::$OPER_DIFERENTE);
    }
  }
  
	$strPalavrasPesquisa = PaginaSEI::getInstance()->recuperarCampo('txtPalavrasPesquisaBloco');
	if ($strPalavrasPesquisa!=''){
    $objBlocoDTO->setStrPalavrasPesquisa($strPalavrasPesquisa);
  }
  
	$strSiglaPesquisa = PaginaSEI::getInstance()->recuperarCampo('txtSiglaPesquisaBloco');
	if ($strSiglaPesquisa!=''){
    $objBlocoDTO->setStrSiglaUsuario($strSiglaPesquisa);
  }
  
  //PaginaSEI::getInstance()->prepararOrdenacao($objBlocoDTO, 'IdBloco', InfraDTO::$TIPO_ORDENACAO_ASC);
  
  $objBlocoDTO->setOrdNumIdBloco(InfraDTO::$TIPO_ORDENACAO_DESC);
  
  if (!PaginaSEI::getInstance()->isBolPaginaSelecao()){
    $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('bloco_cadastrar');
    if ($bolAcaoCadastrar){
      if(($_GET['acao']=='bloco_assinatura_listar')){
        $strAcaoNovo = 'bloco_assinatura_cadastrar';
      }else if(($_GET['acao']=='bloco_reuniao_listar')){
        $strAcaoNovo = 'bloco_reuniao_cadastrar';
      }else if(($_GET['acao']=='bloco_interno_listar')){
        $strAcaoNovo = 'bloco_interno_cadastrar';
      }
       
      $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$strAcaoNovo.'&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']).'\'" class="infraButton" style="width:5em;"><span class="infraTeclaAtalho">N</span>ovo</button>';
    }
  }else{
  	if ($_GET['acao']=='bloco_selecionar_documento'){
  	  if (SessaoSEI::getInstance()->verificarPermissao('bloco_assinatura_cadastrar')){
  		  $arrComandos[] = '<button type="button" accesskey="N" id="btnNovoAssinatura" value="Novo" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=bloco_assinatura_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']).'\'" class="infraButton" style="width:8em"><span class="infraTeclaAtalho">N</span>ovo</button>';
  	  }
  	}else{
  	  
  	  if (SessaoSEI::getInstance()->verificarPermissao('bloco_interno_cadastrar')){
  		  $arrComandos[] = '<button type="button" accesskey="I" id="btnNovoInterno" value="Novo Bloco Interno" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=bloco_interno_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']).'\'" class="infraButton" style="width:11em">Novo Bloco <span class="infraTeclaAtalho">I</span>nterno</button>';
  	  }
  	  
  	  if (SessaoSEI::getInstance()->verificarPermissao('bloco_reuniao_cadastrar')){
  		  $arrComandos[] = '<button type="button" accesskey="R" id="btnNovoReuniao" value="Novo Bloco de Reunião" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=bloco_reuniao_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']).'\'" class="infraButton" style="width:15em">Novo Bloco de <span class="infraTeclaAtalho">R</span>eunião</button>';
  	  }
  	}
  }

  PaginaSEI::getInstance()->prepararPaginacao($objBlocoDTO);
   
	$objBlocoRN = new BlocoRN();
	$arrObjBlocoDTO = $objBlocoRN->pesquisar($objBlocoDTO);

  PaginaSEI::getInstance()->processarPaginacao($objBlocoDTO);
  
  $numRegistros = count($arrObjBlocoDTO);

  $arrParaAssinar = array();
  $arrParaConcluir = array();

  if ($numRegistros > 0){

    $bolCheck = false;

    if (PaginaSEI::getInstance()->isBolPaginaSelecao()){
      $bolAcaoReativar = false;
      $bolAcaoDocumentoAssinar = false;
      $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('bloco_consultar');
      $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('bloco_alterar');
      $bolAcaoRelBlocoProtocolListar = false;
      $bolAcaoBlocoDisponibilizar = false;
      $bolAcaoBlocoCancelarDisponibilizacao = false;
      $bolAcaoImprimir = false;
      $bolAcaoExcluir = false;
      $bolAcaoDesativar = false;
      $bolAcaoRetornarBloco = false;
      $bolAcaoBlocoConcluir = false;
      $bolAcaoReabrir = false;
      $bolCheck = true;
    }else{
      $bolAcaoReativar = false;
      $bolAcaoDocumentoAssinar = SessaoSEI::getInstance()->verificarPermissao('documento_assinar');
      $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('bloco_consultar');
      $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('bloco_alterar');
      $bolAcaoRelBlocoProtocolListar = SessaoSEI::getInstance()->verificarPermissao('rel_bloco_protocolo_listar');
      $bolAcaoBlocoDisponibilizar = SessaoSEI::getInstance()->verificarPermissao('bloco_disponibilizar');
      $bolAcaoBlocoCancelarDisponibilizacao = SessaoSEI::getInstance()->verificarPermissao('bloco_cancelar_disponibilizacao');
      $bolAcaoImprimir = true;
      $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('bloco_excluir');
      $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('bloco_desativar');
      $bolAcaoRetornarBloco = SessaoSEI::getInstance()->verificarPermissao('bloco_retornar');
      $bolAcaoConcluir = SessaoSEI::getInstance()->verificarPermissao('bloco_concluir');
      $bolAcaoReabrir = SessaoSEI::getInstance()->verificarPermissao('bloco_reabrir');
    }

    if ($bolAcaoDocumentoAssinar && $_GET['acao']=='bloco_assinatura_listar'){
      array_unshift($arrComandos, '<button type="button" accesskey="A" id="btnAssinar" value="Assinar" onclick="acaoAssinaturaMultipla();" class="infraButton"><span class="infraTeclaAtalho">A</span>ssinar</button>');
      $strLinkAssinar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_assinar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']);
    }

    if ($bolAcaoBlocoDisponibilizar){
      $strLinkDisponibilizar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=bloco_disponibilizar&acao_origem='.$_GET['acao']);
    }
    
    if ($bolAcaoBlocoCancelarDisponibilizacao){
      $strLinkCancelarDisponibilizacao = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=bloco_cancelar_disponibilizacao&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoConcluir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="C" id="btnConcluir" value="Concluir" onclick="acaoConclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">C</span>oncluir</button>';
      $strLinkConcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=bloco_concluir&acao_origem='.$_GET['acao']);
    }
    
    if ($bolAcaoExcluir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
      $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=bloco_excluir&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoImprimir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
    }
    
    $strLinkRetornarBloco = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=bloco_retornar&acao_origem='.$_GET['acao']);

    $strResultado = '';

    $strSumarioTabela = 'Tabela de Blocos.';
    $strCaptionTabela = 'Blocos';

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    if ($bolCheck) {
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
    }
    $strResultado .= '<th class="infraTh" width="10%">Número</th>'."\n";
    
    
    if (PaginaSEI::getInstance()->isBolPaginaSelecao()){
      if ($_GET['acao']=='bloco_selecionar_processo'){
        $strResultado .= '<th class="infraTh" width="15%">Tipo</th>'."\n";
      }
    }else{
      $strResultado .= '<th class="infraTh" width="10%">Estado</th>'."\n";
      $strResultado .= '<th class="infraTh" width="15%">Geradora</th>'."\n";
      
      if ($objBlocoDTO->getStrStaTipo() != BlocoRN::$TB_INTERNO){
        $strResultado .= '<th class="infraTh" width="15%">Disponibilização</th>'."\n";
      }
    }
     
    $strResultado .= '<th class="infraTh">Descrição</th>'."\n";
    $strResultado .= '<th class="infraTh" width="15%">Ações</th>'."\n";

    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistros; $i++){

      if (!($arrObjBlocoDTO[$i]->getNumIdUnidade()==SessaoSEI::getInstance()->getNumIdUnidadeAtual() && $arrObjBlocoDTO[$i]->getStrStaEstado()==BlocoRN::$TE_DISPONIBILIZADO)){
        $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      }else{
        $strCssTr = '<tr class="trVermelha">';
      }
      
      $strResultado .= $strCssTr;
      
      if ($bolCheck){
        $strResultado .= '<td>'.PaginaSEI::getInstance()->getTrCheck($i,$arrObjBlocoDTO[$i]->getNumIdBloco(),$arrObjBlocoDTO[$i]->getNumIdBloco()).'</td>';
      }
            
      if (PaginaSEI::getInstance()->isBolPaginaSelecao()){
        $strResultado .= '<td align="center"><a href="javascript:void(0);" onclick="infraTransportarItem('.$i.',\'Infra\');" class="ancoraPadraoPreta" style="color:'.(($arrObjBlocoDTO[$i]->getStrStaEstado()==BlocoRN::$TE_ABERTO || $arrObjBlocoDTO[$i]->getStrStaEstado()==BlocoRN::$TE_RETORNADO)?'green':'red').';">'.$arrObjBlocoDTO[$i]->getNumIdBloco().'</a></td>';
        
        if ($_GET['acao']=='bloco_selecionar_processo'){
          $strResultado .= '<td align="center">'.PaginaSEI::tratarHTML($arrObjBlocoDTO[$i]->getStrTipoDescricao()).'</td>';
        }
      }else{  
        $strResultado .= '<td align="center"><a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=rel_bloco_protocolo_listar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_bloco='.$arrObjBlocoDTO[$i]->getNumIdBloco()).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'" class="ancoraPadraoPreta" style="color:'.(($arrObjBlocoDTO[$i]->getStrStaEstado()==BlocoRN::$TE_ABERTO || $arrObjBlocoDTO[$i]->getStrStaEstado()==BlocoRN::$TE_RETORNADO)?'green':'red').';">'.$arrObjBlocoDTO[$i]->getNumIdBloco().'</a></td>';
        
        $strResultado .= '<td align="center">'.PaginaSEI::tratarHTML($arrObjBlocoDTO[$i]->getStrStaEstadoDescricao()).'</td>';
        
        $strResultado .= '<td align="center"><a href="javascript:void(0);" alt="'.$arrObjBlocoDTO[$i]->getStrDescricaoUnidade().'" title="'.$arrObjBlocoDTO[$i]->getStrDescricaoUnidade().'" class="ancoraSigla">'.$arrObjBlocoDTO[$i]->getStrSiglaUnidade().'</a></td>';
        
        if ($objBlocoDTO->getStrStaTipo() != BlocoRN::$TB_INTERNO){
          $strResultado .= '<td align="center">';
          $strNovaLinha = '';
          $arrObjRelBlocoUnidadeDTO = $arrObjBlocoDTO[$i]->getArrObjRelBlocoUnidadeDTO();
          foreach($arrObjRelBlocoUnidadeDTO as $objRelBlocoUnidadeDTO){
            $strResultado .= $strNovaLinha.'<a href="javascript:void(0);" alt="'.$objRelBlocoUnidadeDTO->getStrDescricaoUnidade().'" title="'.$objRelBlocoUnidadeDTO->getStrDescricaoUnidade().'" class="ancoraSigla">'.$objRelBlocoUnidadeDTO->getStrSiglaUnidade().'</a>';
            $strNovaLinha = '<br />';
          }
          $strResultado .= '</td>';
        }
      }
      
      $strResultado .= '<td>'.PaginaSEI::tratarHTML($arrObjBlocoDTO[$i]->getStrDescricao()).'</td>';
           
      $strResultado .= '<td align="center">';
      
      $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i,$arrObjBlocoDTO[$i]->getNumIdBloco(),'Infra','','Escolher este Bloco');

      if ($bolAcaoDocumentoAssinar &&
          $arrObjBlocoDTO[$i]->getStrStaTipo()==BlocoRN::$TB_ASSINATURA &&
          !($arrObjBlocoDTO[$i]->getNumIdUnidade()==SessaoSEI::getInstance()->getNumIdUnidadeAtual() && $arrObjBlocoDTO[$i]->getStrStaEstado()==BlocoRN::$TE_DISPONIBILIZADO) &&  
          $arrObjBlocoDTO[$i]->getStrSinVazio()=='N'){
        $arrParaAssinar[] = $arrObjBlocoDTO[$i]->getNumIdBloco();
        $strResultado .= '<a href="javascript:void(0);" onclick="acaoAssinar(\''.$arrObjBlocoDTO[$i]->getNumIdBloco().'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/sei_assinar_pequeno.gif" title="Assinar Documentos do Bloco" alt="Assinar Documentos do Bloco" class="infraImg" /></a>&nbsp;';
      }
        
      if ($bolAcaoRelBlocoProtocolListar){  
        $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=rel_bloco_protocolo_listar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_bloco='.$arrObjBlocoDTO[$i]->getNumIdBloco()).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/documentos_bloco.gif" title="Processos/Documentos do Bloco" alt="Processos/Documentos do Bloco" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoBlocoDisponibilizar && 
          $arrObjBlocoDTO[$i]->getNumIdUnidade()==SessaoSEI::getInstance()->getNumIdUnidadeAtual() && //bloco da unidade
          $arrObjBlocoDTO[$i]->getStrStaTipo()!=BlocoRN::$TB_INTERNO && //não pode ser interno
          ($arrObjBlocoDTO[$i]->getStrStaEstado()==BlocoRN::$TE_ABERTO || $arrObjBlocoDTO[$i]->getStrStaEstado()==BlocoRN::$TE_RETORNADO)){ //deve estar aberto ou retornado
        $strResultado .= '<a href="'.PaginaSEI::getInstance()->montarAncora($arrObjBlocoDTO[$i]->getNumIdBloco()).'" onclick="acaoDisponibilizar(\''.$arrObjBlocoDTO[$i]->getNumIdBloco().'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/sei_disponibilizar_bloco.gif" title="Disponibilizar Bloco" alt="Disponibilizar Bloco" class="infraImg" /></a>&nbsp;';
      }
      
      if ($bolAcaoBlocoCancelarDisponibilizacao && 
          $arrObjBlocoDTO[$i]->getNumIdUnidade()==SessaoSEI::getInstance()->getNumIdUnidadeAtual() && //bloco da unidade
          $arrObjBlocoDTO[$i]->getStrStaEstado()==BlocoRN::$TE_DISPONIBILIZADO){ //deve estar disponibilizado
        $strResultado .= '<a href="'.PaginaSEI::getInstance()->montarAncora($arrObjBlocoDTO[$i]->getNumIdBloco()).'" onclick="acaoCancelarDisponibilizacao(\''.$arrObjBlocoDTO[$i]->getNumIdBloco().'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/sei_cancelar_disponibilizacao.gif" title="Cancelar Disponibilização" alt="Cancelar Disponibilização" class="infraImg" /></a>&nbsp;';     	  
      }

      
      if ($bolAcaoAlterar && $arrObjBlocoDTO[$i]->getNumIdUnidade()==SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
      	if($arrObjBlocoDTO[$i]->getStrStaEstado()==BlocoRN::$TE_ABERTO || $arrObjBlocoDTO[$i]->getStrStaEstado()==BlocoRN::$TE_RETORNADO){
      	  if ($arrObjBlocoDTO[$i]->getStrStaTipo()==BlocoRN::$TB_ASSINATURA){
      	    $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=bloco_assinatura_alterar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_bloco='.$arrObjBlocoDTO[$i]->getNumIdBloco()).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/alterar.gif" title="Alterar Bloco" alt="Alterar Bloco" class="infraImg" /></a>&nbsp;';
      	  }else if ($arrObjBlocoDTO[$i]->getStrStaTipo()==BlocoRN::$TB_REUNIAO){
      	    $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=bloco_reuniao_alterar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_bloco='.$arrObjBlocoDTO[$i]->getNumIdBloco()).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/alterar.gif" title="Alterar Bloco" alt="Alterar Bloco" class="infraImg" /></a>&nbsp;';
      	  }else if ($arrObjBlocoDTO[$i]->getStrStaTipo()==BlocoRN::$TB_INTERNO){
      	    $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=bloco_interno_alterar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_bloco='.$arrObjBlocoDTO[$i]->getNumIdBloco()).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/alterar.gif" title="Alterar Bloco" alt="Alterar Bloco" class="infraImg" /></a>&nbsp;';
      	  }
      	}
      }

      if ($bolAcaoRetornarBloco && $arrObjBlocoDTO[$i]->getNumIdUnidade()!=SessaoSEI::getInstance()->getNumIdUnidadeAtual() && $arrObjBlocoDTO[$i]->getStrStaEstado()==BlocoRN::$TE_DISPONIBILIZADO){
      	$strResultado .= '<a href="'.PaginaSEI::getInstance()->montarAncora($arrObjBlocoDTO[$i]->getNumIdBloco()).'" onclick="acaoRetornarBloco(\''.$arrObjBlocoDTO[$i]->getNumIdBloco().'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/menos.gif" title="Retornar Bloco" alt="Retornar Bloco" class="infraImg" /></a>&nbsp;';
      }
      
      if ($bolAcaoReabrir && $arrObjBlocoDTO[$i]->getNumIdUnidade()==SessaoSEI::getInstance()->getNumIdUnidadeAtual() && $arrObjBlocoDTO[$i]->getStrStaEstado()==BlocoRN::$TE_CONCLUIDO){
        $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=bloco_reabrir&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_bloco='.$arrObjBlocoDTO[$i]->getNumIdBloco()).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/reabrir_bloco.gif" title="Reabrir Bloco" alt="Reabrir Bloco" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoConcluir && 
          $arrObjBlocoDTO[$i]->getNumIdUnidade()==SessaoSEI::getInstance()->getNumIdUnidadeAtual() &&
          ($arrObjBlocoDTO[$i]->getStrStaEstado()!=BlocoRN::$TE_DISPONIBILIZADO || ($arrObjBlocoDTO[$i]->getStrStaEstado()==BlocoRN::$TE_DISPONIBILIZADO && count($arrObjBlocoDTO[$i]->getArrObjRelBlocoUnidadeDTO())==0)) && 
          $arrObjBlocoDTO[$i]->getStrStaEstado()!=BlocoRN::$TE_CONCLUIDO){
        $arrParaConcluir[] = $arrObjBlocoDTO[$i]->getNumIdBloco();
      	$strResultado .= '<a href="'.PaginaSEI::getInstance()->montarAncora($arrObjBlocoDTO[$i]->getNumIdBloco()).'" onclick="acaoConcluir(\''.$arrObjBlocoDTO[$i]->getNumIdBloco().'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/concluir_bloco.gif" title="Concluir Bloco" alt="Concluir Bloco" class="infraImg" /></a>&nbsp;';
      }
      
      if ($bolAcaoExcluir && 
          $arrObjBlocoDTO[$i]->getNumIdUnidade()==SessaoSEI::getInstance()->getNumIdUnidadeAtual()  &&
          $arrObjBlocoDTO[$i]->getStrStaEstado()!=BlocoRN::$TE_DISPONIBILIZADO){
      	$strResultado .= '<a href="'.PaginaSEI::getInstance()->montarAncora($arrObjBlocoDTO[$i]->getNumIdBloco()).'" onclick="acaoExcluir(\''.$arrObjBlocoDTO[$i]->getNumIdBloco().'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/excluir.gif" title="Excluir Bloco" alt="Excluir Bloco" class="infraImg" /></a>&nbsp;';
      }
      $strResultado .= '</td>'."\n";

      $strResultado .= '</tr>'."\n";
    }
    $strResultado .= '</table>';
  }
  
  $strDesabilitarEstado = '';
  if ($_GET['acao']=='bloco_selecionar_processo'){
    $strDesabilitarEstado = 'disabled="disabled"';
  }else if ($_GET['acao']=='bloco_selecionar_documento'){
    $strDesabilitarEstado = 'disabled="disabled"';
  }
  
  if (PaginaSEI::getInstance()->isBolPaginaSelecao()){
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton" style="width:8em"><span class="infraTeclaAtalho">F</span>echar</button>';
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
#lblPalavrasPesquisaBloco {position:absolute;left:0%;top:0%;width:65%;}
#txtPalavrasPesquisaBloco {position:absolute;left:0%;top:18%;width:65%;}

#lblSiglaPesquisaBloco {position:absolute;left:0%;top:50%;width:15%;}
#txtSiglaPesquisaBloco {position:absolute;left:0%;top:68%;width:15%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){

  infraOcultarMenuSistemaEsquema();

  if ('<?=$_GET['acao']?>'=='bloco_selecionar_processo' || '<?=$_GET['acao']?>'=='bloco_selecionar_documento'){
    infraReceberSelecao();
    document.getElementById('btnTransportarSelecao').focus();
  }else{
    document.getElementById('btnPesquisar').focus();
  }
  infraEfeitoTabelas();
}

<? if ($bolAcaoRetornarBloco){ ?>
function acaoRetornarBloco(id){
  if (confirm("Confirma a devolução do Bloco \""+id+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmBlocoLista').action='<?=$strLinkRetornarBloco?>';
    document.getElementById('frmBlocoLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoDesativar){ ?>
function acaoDesativar(id){
  if (confirm("Confirma desativação do Bloco \""+id+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmBlocoLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmBlocoLista').submit();
  }
}

function acaoDesativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Bloco selecionado.');
    return;
  }
  if (confirm("Confirma desativação dos Blocos selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmBlocoLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmBlocoLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoReativar){ ?>
function acaoReativar(id){
  if (confirm("Confirma reativação do Bloco \""+id+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmBlocoLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmBlocoLista').submit();
  }
}

function acaoReativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Bloco selecionado.');
    return;
  }
  if (confirm("Confirma reativação dos Blocos selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmBlocoLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmBlocoLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoConcluir){ ?>
function acaoConcluir(id){
  if (confirm("Confirma conclusão do Bloco \""+id+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmBlocoLista').action='<?=$strLinkConcluir?>';
    document.getElementById('frmBlocoLista').submit();
  }
}

function acaoConclusaoMultipla(){

  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Bloco selecionado.');
    return;
  }

  if (!verificarSelecionados([<?=implode(',',$arrParaConcluir)?>], 'Nenhum bloco selecionado pode ser concluído.', 'Os blocos a seguir não podem ser concluídos e serão ignorados: ')){
    return;
  }

  if (confirm("Confirma conclusão dos Blocos selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmBlocoLista').action='<?=$strLinkConcluir?>';
    document.getElementById('frmBlocoLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoExcluir){ ?>
function acaoExcluir(id){
  if (confirm("Confirma exclusão do Bloco \""+id+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmBlocoLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmBlocoLista').submit();
  }
}

function acaoExclusaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Bloco selecionado.');
    return;
  }
  if (confirm("Confirma exclusão dos Blocos selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmBlocoLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmBlocoLista').submit();
  }
}
<? } ?>


<? if ($bolAcaoBlocoDisponibilizar){ ?>
function acaoDisponibilizar(id){
  //if (confirm("Confirma disponibilização do bloco \""+id+"\" para assinatura?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmBlocoLista').action='<?=$strLinkDisponibilizar?>';
    document.getElementById('frmBlocoLista').submit();
  //}
}

function acaoDisponibilizacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Bloco selecionado.');
    return;
  }
  //if (confirm("Confirma disponibilização para assinatura dos blocos selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmBlocoLista').action='<?=$strLinkDisponibilizar?>';
    document.getElementById('frmBlocoLista').submit();
  //}
}
<? } ?>


<? if ($bolAcaoBlocoCancelarDisponibilizacao){ ?>
function acaoCancelarDisponibilizacao(id){
  if (confirm("Confirma cancelamento de disponibilização do Bloco \""+id+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmBlocoLista').action='<?=$strLinkCancelarDisponibilizacao?>';
    document.getElementById('frmBlocoLista').submit();
  }
}

function acaoCancelarDisponibilizacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Bloco selecionado.');
    return;
  }
  if (confirm("Confirma cancelamento de disponibilização dos Blocos selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmBlocoLista').action='<?=$strLinkCancelarDisponibilizacao?>';
    document.getElementById('frmBlocoLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoDocumentoAssinar){ ?>
function acaoAssinar(id){

  infraAbrirJanela('<?=$strLinkAssinar?>','janelaAssinatura',700,450,'location=0,status=1,resizable=1,scrollbars=1');

  document.getElementById('hdnInfraItemId').value=id;
  document.getElementById('frmBlocoLista').target='janelaAssinatura';
  document.getElementById('frmBlocoLista').action='<?=$strLinkAssinar?>';
  document.getElementById('frmBlocoLista').submit();
}

function acaoAssinaturaMultipla(){

  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Bloco selecionado.');
    return;
  }

  if (!verificarSelecionados([<?=implode(',',$arrParaAssinar)?>], 'Nenhum bloco selecionado pode ser assinado.', 'Os blocos a seguir não podem ser assinados e serão ignorados: ')){
    return;
  }

  infraAbrirJanela('<?=$strLinkAssinar?>','janelaAssinatura',700,450,'location=0,status=1,resizable=1,scrollbars=1');

  document.getElementById('hdnInfraItemId').value='';
  document.getElementById('frmBlocoLista').target='janelaAssinatura';
  document.getElementById('frmBlocoLista').action='<?=$strLinkAssinar?>';
  document.getElementById('frmBlocoLista').submit();
}
<? } ?>

function tratarDigitacao(ev){
  if (infraGetCodigoTecla(ev) == 13){
    document.getElementById('frmBlocoLista').submit();
  }
  return true;
}

function selecionar(){
  objInput = document.getElementsByTagName('input');
  for (var i = 0; i < objInput.length; i++) {  
    if (objInput[i].type == 'radio' && objInput[i].checked) {
      break;
    }
  }
  
  if (i==objInput.length){
    alert('Nenhum Bloco selecionado.');
    return;
  }
  
  infraTransportarSelecao();
}

function pesquisar(){
  document.getElementById('hdnPesquisar').value = '1';
  document.getElementById('frmBlocoLista').submit();
}

function verificarSelecionados(blocosValidos, msgNenhum, msgIgnorados){
  var i = 0;
  var j = 0;

  var selecionados = document.getElementById('hdnInfraItensSelecionados').value.split(',');
  var erros = [];
  var blocosProcessamento = [];

  for (i = 0; i < selecionados.length; i++) {
    if (!infraInArray(selecionados[i], blocosValidos)){
      erros.push(selecionados[i]);
    }else{
      blocosProcessamento.push(selecionados[i]);
    }
  }

  if (blocosProcessamento.length == 0){
    alert(msgNenhum);
    return false;
  }

  if (erros.length){
    alert(msgIgnorados + erros.join(', '));
    var nroItens = document.getElementById('hdnInfraNroItens').value;
    for(i = 0; i < erros.length; i++){
      for(j = 0; j < nroItens; j++){
        chk = document.getElementById('chkInfraItem'+j);
        if (chk.value == erros[i]){
          chk.checked = false;
          infraSelecionarItens(chk);
        }
      }
    }
  }

  document.getElementById('hdnInfraItensSelecionados').value = blocosProcessamento.join(',');

  return true;
}

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmBlocoLista" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].'&id_documento='.$_GET['id_documento'])?>">
  <?
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->abrirAreaDados('10em');
  ?>

  <label id="lblPalavrasPesquisaBloco" for="txtPalavrasPesquisaBloco" accesskey="" class="infraLabelOpcional">Palavras-chave para pesquisa:</label>
  <input type="text" id="txtPalavrasPesquisaBloco" name="txtPalavrasPesquisaBloco" class="infraText" value="<?=PaginaSEI::tratarHTML($strPalavrasPesquisa)?>" onkeypress="return tratarDigitacao(event);" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />

  <label id="lblSiglaPesquisaBloco" for="txtSiglaPesquisaBloco" accesskey="" class="infraLabelOpcional">Sigla:</label>
  <input type="text" id="txtSiglaPesquisaBloco" name="txtSiglaPesquisaBloco" class="infraText" value="<?=PaginaSEI::tratarHTML($strSiglaPesquisa)?>" onkeypress="return tratarDigitacao(event);" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
  
  <input type="hidden" id="hdnPesquisar" name="hdnPesquisar" value="<?=$_POST['hdnPesquisar']?>" />
  
  <?
  PaginaSEI::getInstance()->fecharAreaDados();
  PaginaSEI::getInstance()->montarAreaTabela($strResultado,$numRegistros,true);
  PaginaSEI::getInstance()->montarAreaDebug();
  PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>