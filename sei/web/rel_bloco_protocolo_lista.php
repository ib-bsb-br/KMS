<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 02/10/2009 - criado por fbv@trf4.gov.br
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

  if($_GET['acao_origem']=='bloco_selecionar_processo'){
  	PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);
  }
  
  PaginaSEI::getInstance()->prepararSelecao('rel_bloco_protocolo_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  PaginaSEI::getInstance()->salvarCamposPost(array('selProtocolo','selBloco'));

  $strParametros = '';
  if(isset($_GET['arvore'])){
    PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
    $strParametros .= '&arvore='.$_GET['arvore'];
  }
  
  $strParametros .= '&id_bloco='.$_GET['id_bloco'];
  
  switch($_GET['acao']){
    case 'rel_bloco_protocolo_excluir':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjRelBlocoProtocoloDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $arrStrIdComposto = explode('-',$arrStrIds[$i]);
          $objRelBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
          $objRelBlocoProtocoloDTO->setDblIdProtocolo($arrStrIdComposto[0]);
          $objRelBlocoProtocoloDTO->setNumIdBloco($arrStrIdComposto[1]);
          $arrObjRelBlocoProtocoloDTO[] = $objRelBlocoProtocoloDTO;
        }
        $objRelBlocoProtocoloRN = new RelBlocoProtocoloRN();
        $objRelBlocoProtocoloRN->excluirRN1289($arrObjRelBlocoProtocoloDTO);
        PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      }
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao'].$strParametros));
      die;

/* 
    case 'rel_bloco_protocolo_desativar':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjRelBlocoProtocoloDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $arrStrIdComposto = explode('-',$arrStrIds[$i]);
          $objRelBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
          $objRelBlocoProtocoloDTO->setDblIdProtocolo($arrStrIdComposto[0]);
          $objRelBlocoProtocoloDTO->setNumIdBloco($arrStrIdComposto[1]);
          $arrObjRelBlocoProtocoloDTO[] = $objRelBlocoProtocoloDTO;
        }
        $objRelBlocoProtocoloRN = new RelBlocoProtocoloRN();
        $objRelBlocoProtocoloRN->desativarRN1293($arrObjRelBlocoProtocoloDTO);
        PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'rel_bloco_protocolo_reativar':
      $strTitulo = 'Reativar Rel_Bloco_Protocolos';
      if ($_GET['acao_confirmada']=='sim'){
        try{
          $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
          $arrObjRelBlocoProtocoloDTO = array();
          for ($i=0;$i<count($arrStrIds);$i++){
            $arrStrIdComposto = explode('-',$arrStrIds[$i]);
            $objRelBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
            $objRelBlocoProtocoloDTO->setDblIdProtocolo($arrStrIdComposto[0]);
            $objRelBlocoProtocoloDTO->setNumIdBloco($arrStrIdComposto[1]);
            $arrObjRelBlocoProtocoloDTO[] = $objRelBlocoProtocoloDTO;
          }
          $objRelBlocoProtocoloRN = new RelBlocoProtocoloRN();
          $objRelBlocoProtocoloRN->reativarRN1294($arrObjRelBlocoProtocoloDTO);
          PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        } 
        header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
        die;
      } 
      break;

 */
    case 'rel_bloco_protocolo_selecionar':
      $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Processo/Documento','Selecionar Processos/Documentos');

      //Se cadastrou alguem
      if ($_GET['acao_origem']=='rel_bloco_protocolo_cadastrar'){
        if (isset($_GET['id_protocolo']) && isset($_GET['id_bloco'])){
          PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_protocolo'].'-'.$_GET['id_bloco']);
        }
      }
      break;

    case 'rel_bloco_protocolo_listar':
    	
      $objBlocoDTO = new BlocoDTO();
      $objBlocoDTO->retStrStaTipo();
      $objBlocoDTO->retStrStaEstado();
      $objBlocoDTO->retStrTipoDescricao();
      $objBlocoDTO->retNumIdUnidade();
      $objBlocoDTO->setNumIdBloco($_GET['id_bloco']);
      
      $objBlocoRN = new BlocoRN();
      $objBlocoDTO = $objBlocoRN->consultarRN1276($objBlocoDTO);
      
      if ($objBlocoDTO==null){
      	$strTitulo = 'Documentos do Bloco '.$_GET['id_bloco'];
      	
      	if ($_GET['acao'] == 'rel_bloco_protocolo_selecionar'){
      		$arrComandos = array('<button type="button" accesskey="F" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>');
      	}else{
          $arrComandos = array('<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>');
      	}
      	
        $objInfraException =  new InfraException();
        $objInfraException->lancarValidacao('Bloco '.$_GET['id_bloco'].' não encontrado.');
      }
      
      $strTitulo = '';

      
      switch($objBlocoDTO->getStrStaTipo()){
        
        case BlocoRN::$TB_ASSINATURA: 
          $strTitulo = 'Documentos do Bloco de '.$objBlocoDTO->getStrTipoDescricao();    
          break;
          
        case BlocoRN::$TB_REUNIAO:
          $strTitulo = 'Processos do Bloco de '.$objBlocoDTO->getStrTipoDescricao();
          break;
          
        case BlocoRN::$TB_INTERNO:    
          $strTitulo = 'Processos do Bloco '.$objBlocoDTO->getStrTipoDescricao();
          break;
         
      }
      $strTitulo .= ' '.$_GET['id_bloco'];    
      
      
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $arrComandos = array();
  if ($_GET['acao'] == 'rel_bloco_protocolo_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
  }

  /* if ($_GET['acao'] == 'rel_bloco_protocolo_listar' || $_GET['acao'] == 'rel_bloco_protocolo_selecionar'){ */
    //$bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('rel_bloco_protocolo_cadastrar');
    //if ($bolAcaoCadastrar){
    //  $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=rel_bloco_protocolo_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'])).'\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
    //}
  /* } */

  $objRelBlocoUnidadeDTO = new RelBlocoUnidadeDTO();
  $objRelBlocoUnidadeDTO->retNumIdUnidade();
  $objRelBlocoUnidadeDTO->setNumIdBloco($_GET['id_bloco']);

  $objRelBlocoUnidadeRN = new RelBlocoUnidadeRN();
  $arrIdUnidadesBloco = InfraArray::converterArrInfraDTO($objRelBlocoUnidadeRN->listarRN1304($objRelBlocoUnidadeDTO),'IdUnidade');


  $objRelBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
  $objRelBlocoProtocoloDTO->retDblIdProtocolo();
  $objRelBlocoProtocoloDTO->retNumIdBloco();
  $objRelBlocoProtocoloDTO->retNumSequencia();
  $objRelBlocoProtocoloDTO->retNumIdUnidadeBloco();
  $objRelBlocoProtocoloDTO->retStrProtocoloFormatadoProtocolo();
  $objRelBlocoProtocoloDTO->retStrStaProtocoloProtocolo();
  $objRelBlocoProtocoloDTO->retStrAnotacao();
  //$objRelBlocoProtocoloDTO->retStrSinAberto();
  
  /*$dblIdProtocolo = PaginaSEI::getInstance()->recuperarCampo('selProtocolo');
  if ($dblIdProtocolo!==''){
    $objRelBlocoProtocoloDTO->setDblIdProtocolo($dblIdProtocolo);
  }

  $numIdBloco = PaginaSEI::getInstance()->recuperarCampo('selBloco');
  if ($numIdBloco!==''){
    $objRelBlocoProtocoloDTO->setNumIdBloco($numIdBloco);
  }*/
	$objRelBlocoProtocoloDTO->setNumIdBloco($_GET['id_bloco']);
/* 
  if ($_GET['acao'] == 'rel_bloco_protocolo_reativar'){
    //Lista somente inativos
    $objRelBlocoProtocoloDTO->setBolExclusaoLogica(false);
    $objRelBlocoProtocoloDTO->setStrSinAtivo('N');
  }
 */
  
  $objRelBlocoProtocoloDTO->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);
  
  PaginaSEI::getInstance()->prepararPaginacao($objRelBlocoProtocoloDTO,100);

  $objRelBlocoProtocoloRN = new RelBlocoProtocoloRN();
  $arrObjRelBlocoProtocoloDTO = $objRelBlocoProtocoloRN->listarProtocolosBloco($objRelBlocoProtocoloDTO);

  PaginaSEI::getInstance()->processarPaginacao($objRelBlocoProtocoloDTO);
  $numRegistros = count($arrObjRelBlocoProtocoloDTO);

  if ($numRegistros > 0){

    $bolCheck = false;

    if ($_GET['acao']=='rel_bloco_protocolo_selecionar'){
      $bolAcaoReativar = false;
      $bolAcaoDocumentoVisualizar = false;
      $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('rel_bloco_protocolo_consultar');
      $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('rel_bloco_protocolo_alterar');
      $bolAcaoDocumentoAssinar = false;
      $bolAcaoImprimir = false;
      $bolAcaoExcluir = false;
      $bolAcaoDesativar = false;
      $bolCheck = true;
/*     }else if ($_GET['acao']=='rel_bloco_protocolo_reativar'){
      $bolAcaoReativar = SessaoSEI::getInstance()->verificarPermissao('rel_bloco_protocolo_reativar');
      $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('rel_bloco_protocolo_consultar');
      $bolAcaoAlterar = false;
      $bolAcaoDocumentoAssinar = false;
      $bolAcaoImprimir = true;
      $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('rel_bloco_protocolo_excluir');
      $bolAcaoDesativar = false;
 */    }else{
      $bolAcaoReativar = false;
      $bolAcaoDocumentoVisualizar = SessaoSEI::getInstance()->verificarPermissao('documento_visualizar');
      $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('rel_bloco_protocolo_consultar');
      $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('rel_bloco_protocolo_alterar');
      $bolAcaoDocumentoAssinar = SessaoSEI::getInstance()->verificarPermissao('documento_assinar');
      $bolAcaoImprimir = true;
      $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('rel_bloco_protocolo_excluir');
      $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('rel_bloco_protocolo_desativar');
    }

    $bolAcessoBlocoUnidade = $objBlocoDTO->getNumIdUnidade()==SessaoSEI::getInstance()->getNumIdUnidadeAtual() ||
                             ($objBlocoDTO->getStrStaEstado()==BlocoRN::$TE_DISPONIBILIZADO && in_array(SessaoSEI::getInstance()->getNumIdUnidadeAtual(),$arrIdUnidadesBloco));

    /* 
    if ($bolAcaoDesativar){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="t" id="btnDesativar" value="Desativar" onclick="acaoDesativacaoMultipla();" class="infraButton">Desa<span class="infraTeclaAtalho">t</span>ivar</button>';
      $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=rel_bloco_protocolo_desativar&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoReativar){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="R" id="btnReativar" value="Reativar" onclick="acaoReativacaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">R</span>eativar</button>';
      $strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=rel_bloco_protocolo_reativar&acao_origem='.$_GET['acao'].'&acao_confirmada=sim');
    }
     */



    if ($bolAcaoDocumentoAssinar && $objBlocoDTO->getStrStaTipo()==BlocoRN::$TB_ASSINATURA && $bolAcessoBlocoUnidade){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="A" id="btnAssinar" value="Assinar" onclick="acaoAssinaturaMultipla();" class="infraButton"><span class="infraTeclaAtalho">A</span>ssinar</button>';
      $strLinkAssinar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_assinar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_bloco='.$_GET['id_bloco']);
    }
    
    if ($bolAcaoExcluir &&
        $objBlocoDTO->getStrStaEstado()!=BlocoRN::$TE_DISPONIBILIZADO && 
        $objBlocoDTO->getNumIdUnidade()==SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="R" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">R</span>etirar do Bloco</button>';
      $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=rel_bloco_protocolo_excluir&acao_origem='.$_GET['acao'].$strParametros);
    }

    if ($bolAcaoImprimir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';

    }

    $strResultado = '';
    $strArrJs='';

    /* if ($_GET['acao']!='rel_bloco_protocolo_reativar'){ */
      $strSumarioTabela = 'Tabela de Processos/Documentos.';
      $strCaptionTabela = 'Processos/Documentos';
    /* }else{
      $strSumarioTabela = 'Tabela de Rel_Bloco_Protocolos Inativos.';
      $strCaptionTabela = 'Rel_Bloco_Protocolos Inativos';
    } */

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    if ($bolCheck) {
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
    }
    //$strResultado .= '<th class="infraTh">Bloco</th>'."\n";
    //$strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThOrdenacao($objRelBlocoProtocoloDTO,'Protocolo','IdProtocolo',$arrObjRelBlocoProtocoloDTO).'</th>'."\n";
    
    $strResultado .= '<th class="infraTh" width="4%">Seq.</th>'."\n";
    $strResultado .= '<th class="infraTh" width="17%">Processo</th>'."\n";
    
    if ($objBlocoDTO->getStrStaTipo()==BlocoRN::$TB_ASSINATURA){
      $strResultado .= '<th class="infraTh" width="10%">Documento</th>'."\n";
    }
    
    $strResultado .= '<th class="infraTh" width="10%">Data</th>'."\n";
    $strResultado .= '<th class="infraTh" width="10%">Tipo</th>'."\n";
    
    if ($objBlocoDTO->getStrStaTipo()==BlocoRN::$TB_ASSINATURA){
      $strResultado .= '<th class="infraTh" width="20%">Assinaturas</th>'."\n";
    }
    
    $strResultado .= '<th class="infraTh">Anotações</th>'."\n";
    
    if ($_GET['acao_origem']!='bloco_listar_disponibilizados') {
    	$strResultado .= '<th class="infraTh" width="10%">Ações</th>'."\n";
    }
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    $n = 0;
    
    foreach($arrObjRelBlocoProtocoloDTO as $objRelBlocoProtocoloDTO){

      $objProtocoloDTO = $objRelBlocoProtocoloDTO->getObjProtocoloDTO();

      $strCssTr = ($strCssTr=='class="infraTrClara"')?'class="infraTrEscura"':'class="infraTrClara"';
      $strResultado .= '<tr id="trSeq'.$objRelBlocoProtocoloDTO->getNumSequencia().'" '.$strCssTr.'>';

      if ($bolCheck){
        $strResultado .= '<td>'.PaginaSEI::getInstance()->getTrCheck($n++,$objRelBlocoProtocoloDTO->getDblIdProtocolo().'-'.$objRelBlocoProtocoloDTO->getNumIdBloco(),$objRelBlocoProtocoloDTO->getStrProtocoloFormatadoProtocolo()).'</td>';
      }
      
      //$strResultado .= '<td valign="top">'.$objRelBlocoProtocoloDTO->getNumIdBloco().'</td>';
      
      $strResultado .= '<td align="center">'.$objRelBlocoProtocoloDTO->getNumSequencia().'</td>';
       
      $strClassProtocolo = '';
      if ($objProtocoloDTO->getStrSinAberto()=='S'){
        $strClassProtocolo = 'protocoloAberto';
      }else{
        $strClassProtocolo = 'protocoloFechado';
      }

      if ($objBlocoDTO->getStrStaTipo()==BlocoRN::$TB_ASSINATURA){
        $strResultado .= '<td align="center"><a onclick="infraLimparFormatarTrAcessada(this.parentNode.parentNode);" href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_procedimento='.$objProtocoloDTO->getDblIdProcedimentoDocumentoProcedimento().'&id_documento='.$objRelBlocoProtocoloDTO->getDblIdProtocolo()).'" target="_blank" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'" class="'.$strClassProtocolo.'" alt="'.PaginaSEI::tratarHTML($objProtocoloDTO->getStrNomeTipoProcedimentoDocumento()).'" title="'.PaginaSEI::tratarHTML($objProtocoloDTO->getStrNomeTipoProcedimentoDocumento()).'">'.PaginaSEI::tratarHTML($objProtocoloDTO->getStrProtocoloFormatadoProcedimentoDocumento()).'</a></td>';
        $strResultado .= "\n".'<td align="center">';

        if ($bolAcaoDocumentoVisualizar){
          $strResultado .= '<a onclick="infraLimparFormatarTrAcessada(this.parentNode.parentNode);infraAbrirJanela(\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=bloco_navegar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_bloco='.$_GET['id_bloco'].'&seq='.$objRelBlocoProtocoloDTO->getNumSequencia()).'\',\'navegacao\',900,650,\'location=0,status=1,resizable=1,scrollbars=1\',true);" href="#" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'" class="'.$strClassProtocolo.'" title="'.PaginaSEI::tratarHTML($objProtocoloDTO->getStrNomeSerieDocumento()).'">'.PaginaSEI::tratarHTML($objRelBlocoProtocoloDTO->getStrProtocoloFormatadoProtocolo()).'</a>';
          $strArrJs .= 'arrLinkDocumentos['.$objRelBlocoProtocoloDTO->getNumSequencia().']="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_visualizar&id_documento='.$objRelBlocoProtocoloDTO->getDblIdProtocolo()) .'";'."\n";
          $strArrJs .= 'arrLinkProcedimentos['.$objRelBlocoProtocoloDTO->getNumSequencia().']="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=bloco_navegar&id_procedimento='.$objProtocoloDTO->getDblIdProcedimentoDocumentoProcedimento(). '&id_documento='.$objRelBlocoProtocoloDTO->getDblIdProtocolo()) .'";'."\n";
          $strArrJs .= 'arrLinkAssinaturas['.$objRelBlocoProtocoloDTO->getNumSequencia().']="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_assinar&acao_origem=bloco_navegar&acao_retorno=bloco_navegar&id_procedimento='.$objProtocoloDTO->getDblIdProcedimentoDocumentoProcedimento().'&id_documento='.$objRelBlocoProtocoloDTO->getDblIdProtocolo()).'";'."\n";
        }else{
          $strResultado .= '<span class="'.$strClassProtocolo.'">'.PaginaSEI::tratarHTML($objRelBlocoProtocoloDTO->getStrProtocoloFormatadoProtocolo()).'</span>';
        }
        
        $strResultado .= '</td>';
      }else{
        $strResultado .= '<td align="center">';
        $strResultado .= '<a onclick="infraLimparFormatarTrAcessada(this.parentNode.parentNode);" href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_procedimento='.$objRelBlocoProtocoloDTO->getDblIdProtocolo()).'" target="_blank" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'" class="'.$strClassProtocolo.'" alt="'.PaginaSEI::tratarHTML($objProtocoloDTO->getStrNomeTipoProcedimentoProcedimento()).'" title="'.PaginaSEI::tratarHTML($objProtocoloDTO->getStrNomeTipoProcedimentoProcedimento()).'">'.PaginaSEI::tratarHTML($objRelBlocoProtocoloDTO->getStrProtocoloFormatadoProtocolo()).'</a>';
        $strResultado .= '</td>';
      }
      
      $strResultado .= '<td align="center">'.PaginaSEI::tratarHTML($objProtocoloDTO->getDtaGeracao()).'</td>';
      
      if ($objRelBlocoProtocoloDTO->getStrStaProtocoloProtocolo()==ProtocoloRN::$TP_PROCEDIMENTO){
        $strResultado .= '<td align="center">'.PaginaSEI::tratarHTML($objProtocoloDTO->getStrNomeTipoProcedimentoProcedimento()).'</td>';
      }else{
        $strResultado .= '<td align="center">'.PaginaSEI::tratarHTML($objProtocoloDTO->getStrNomeSerieDocumento()).'</td>';
      }
      
      if ($objBlocoDTO->getStrStaTipo()==BlocoRN::$TB_ASSINATURA){
        $strResultado .= '<td align="justified">';
        
        $strAssinaturas = AssinaturaINT::montarHtmlAssinaturas($objRelBlocoProtocoloDTO->getArrObjAssinaturaDTO());
        $strResultado .= $strAssinaturas;
        
        $strResultado .= '</td>';
      }
      
      //$strResultado .= '<td>'.BlocoINT::montarTexto($n,$objRelBlocoProtocoloDTO->getStrAnotacao(),250).'</td>';
      $strResultado .= '<td>'.nl2br(InfraString::formatarXML($objRelBlocoProtocoloDTO->getStrAnotacao())).'</td>';

      if ($_GET['acao_origem']!='bloco_listar_disponibilizados'){
      	$strResultado .= '<td align="center">';
      	
      	if ($bolAcaoDocumentoAssinar || $bolAcaoDesativar || $bolAcaoReativar || $bolAcaoExcluir){
      		$strId = $objRelBlocoProtocoloDTO->getDblIdProtocolo().'-'.$objRelBlocoProtocoloDTO->getNumIdBloco();
      		$strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript($objRelBlocoProtocoloDTO->getStrProtocoloFormatadoProtocolo());
      	}
      	
      	$strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($n,$objRelBlocoProtocoloDTO->getDblIdProtocolo().'-'.$objRelBlocoProtocoloDTO->getNumIdBloco());

      	/*if ($bolAcaoConsultar){
      		$strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=rel_bloco_protocolo_consultar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_protocolo='.$objRelBlocoProtocoloDTO->getDblIdProtocolo().'&id_bloco='.$objRelBlocoProtocoloDTO->getNumIdBloco())).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/consultar.gif" title="Consultar Rel_Bloco_Protocolo" alt="Consultar Rel_Bloco_Protocolo" class="infraImg" /></a>&nbsp;';
      	}*/

        if ($bolAcaoDocumentoAssinar && $objBlocoDTO->getStrStaTipo()==BlocoRN::$TB_ASSINATURA && $bolAcessoBlocoUnidade){
      		$strResultado .= '<a href="'.PaginaSEI::getInstance()->montarAncora($strId).'" onclick="acaoAssinar(\''.$strId.'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/sei_assinar_pequeno.gif" title="Assinar Documento" alt="Assinar Documento" class="infraImg" /></a>&nbsp;';
        }
      	
     	  if ($bolAcaoAlterar && $bolAcessoBlocoUnidade){
      		$strResultado .= '<a href="javascript:void(0);" onclick="acaoAlterar(\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=rel_bloco_protocolo_alterar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_documento='.$objRelBlocoProtocoloDTO->getDblIdProtocolo().'&id_bloco='.$objRelBlocoProtocoloDTO->getNumIdBloco()).'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/anotacoes.gif" title="Anotações" alt="Anotações" class="infraImg" /></a>&nbsp;';
      	}
        
      	
      	
      	/*
      	if ($bolAcaoDesativar){
      	$strResultado .= '<a href="'.PaginaSEI::getInstance()->montarAncora($strId).'" onclick="acaoDesativar(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/desativar.gif" title="Desativar Rel_Bloco_Protocolo" alt="Desativar Rel_Bloco_Protocolo" class="infraImg" /></a>&nbsp;';
      	}

      	if ($bolAcaoReativar){
      	$strResultado .= '<a href="'.PaginaSEI::getInstance()->montarAncora($strId).'" onclick="acaoReativar(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/reativar.gif" title="Reativar Rel_Bloco_Protocolo" alt="Reativar Rel_Bloco_Protocolo" class="infraImg" /></a>&nbsp;';
      	}
      	*/

      	
      	if ($bolAcaoExcluir &&
      	    $objBlocoDTO->getStrStaEstado()!=BlocoRN::$TE_DISPONIBILIZADO && 
      	    $objRelBlocoProtocoloDTO->getNumIdUnidadeBloco()==SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
      		$strResultado .= '<a href="'.PaginaSEI::getInstance()->montarAncora($strId).'" onclick="acaoExcluir(\''.$strId.'\',\''.$strDescricao.'\',\''.$objRelBlocoProtocoloDTO->getStrStaProtocoloProtocolo().'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/excluir.gif" title="Retirar Processo/Documento do Bloco" alt="Retirar Processo/Documento do Bloco" class="infraImg" /></a>&nbsp;';
      	}
	      $strResultado .= '</td>'."\n";
      }
      $strResultado .= '</tr>'."\n";
    }
    $strResultado .= '</table>';
  }
  
  if ($_GET['acao'] == 'rel_bloco_protocolo_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }else{
    
    switch($objBlocoDTO->getStrStaTipo()){
      
      case BlocoRN::$TB_ASSINATURA:
        $strAcaoDestino = 'bloco_assinatura_listar';
        break;
        
      case BlocoRN::$TB_REUNIAO:
        $strAcaoDestino = 'bloco_reuniao_listar';
        break;
        
      case BlocoRN::$TB_INTERNO:    
        $strAcaoDestino = 'bloco_interno_listar';
        break;
      
    }
          
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$strAcaoDestino.'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($_GET['id_bloco'])).'\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
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
#lblProtocolo {position:absolute;left:0%;top:0%;width:25%;}
#selProtocolo {position:absolute;left:0%;top:20%;width:25%;}

#lblBloco {position:absolute;left:0%;top:50%;width:25%;}
#selBloco {position:absolute;left:0%;top:70%;width:25%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();

if ($strArrJs!=''){
  echo "arrLinkDocumentos=[];\n";
  echo "arrLinkProcedimentos=[];\n";
  echo "arrLinkAssinaturas=[];\n";
  echo "arrDocumentosVisualizados=[];\n";
  echo $strArrJs;
}
?>

function inicializar(){

  if ('<?=$_GET['acao_origem']?>' != 'rel_bloco_protocolo_listar'){
    infraOcultarMenuSistemaEsquema();
  }

  if ('<?=$_GET['acao']?>'=='rel_bloco_protocolo_selecionar'){
    infraReceberSelecao();
    document.getElementById('btnFecharSelecao').focus();
  }else{
    document.getElementById('btnFechar').focus();
  }
  
  infraEfeitoTabelas();
}

<? if ($bolAcaoDesativar){ ?>
function acaoDesativar(id,desc){
  if (confirm("Confirma desativação do Rel_Bloco_Protocolo \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmRelBlocoProtocoloLista').target = '_self';
    document.getElementById('frmRelBlocoProtocoloLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmRelBlocoProtocoloLista').submit();
  }
}

function acaoDesativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Rel_Bloco_Protocolo selecionado.');
    return;
  }
  if (confirm("Confirma desativação dos Rel_Bloco_Protocolos selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmRelBlocoProtocoloLista').target = '_self';
    document.getElementById('frmRelBlocoProtocoloLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmRelBlocoProtocoloLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoReativar){ ?>
function acaoReativar(id,desc){
  if (confirm("Confirma reativação do Rel_Bloco_Protocolo \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmRelBlocoProtocoloLista').target = '_self';
    document.getElementById('frmRelBlocoProtocoloLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmRelBlocoProtocoloLista').submit();
  }
}

function acaoReativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Rel_Bloco_Protocolo selecionado.');
    return;
  }
  if (confirm("Confirma reativação dos Rel_Bloco_Protocolos selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmRelBlocoProtocoloLista').target = '_self';
    document.getElementById('frmRelBlocoProtocoloLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmRelBlocoProtocoloLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoExcluir){ ?>
function acaoExcluir(id,desc,tipo){

  var descTipo = '';
   
  if (tipo == '<?=ProtocoloRN::$TP_PROCEDIMENTO?>'){
    descTipo = 'processo';
  }else{
    descTipo = 'documento';
  }

  if (confirm("Confirma retirada do " + descTipo + " \"" + desc + "\" do bloco?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmRelBlocoProtocoloLista').target = '_self';
    document.getElementById('frmRelBlocoProtocoloLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmRelBlocoProtocoloLista').submit();
  }
}

function acaoExclusaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum protocolo selecionado.');
    return;
  }
  if (confirm("Confirma retirada dos protocolos selecionados do bloco?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmRelBlocoProtocoloLista').target = '_self';
    document.getElementById('frmRelBlocoProtocoloLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmRelBlocoProtocoloLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoDocumentoAssinar){ ?>
function acaoAssinar(id){

  infraAbrirJanela('<?=$strLinkAssinar?>','janelaAssinatura',700,450,'location=0,status=1,resizable=1,scrollbars=1');

  document.getElementById('hdnInfraItemId').value=id;
  document.getElementById('frmRelBlocoProtocoloLista').target='janelaAssinatura';
  document.getElementById('frmRelBlocoProtocoloLista').action='<?=$strLinkAssinar?>';
  document.getElementById('frmRelBlocoProtocoloLista').submit();
}

function acaoAssinaturaMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum documento selecionado.');
    return;
  }
  
  infraAbrirJanela('<?=$strLinkAssinar?>','janelaAssinatura',700,450,'location=0,status=1,resizable=1,scrollbars=1');
  
  document.getElementById('hdnInfraItemId').value='';
  document.getElementById('frmRelBlocoProtocoloLista').target='janelaAssinatura';
  document.getElementById('frmRelBlocoProtocoloLista').action='<?=$strLinkAssinar?>';
  document.getElementById('frmRelBlocoProtocoloLista').submit();
}
<? } ?>

<? if ($bolAcaoAlterar){ ?>
function acaoAlterar(link){

  infraAbrirJanela(link,'janelaAlterarAnotacoes',500,350,'location=0,status=1,resizable=1,scrollbars=1');
} 
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmRelBlocoProtocoloLista" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>">
  <?
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  /*PaginaSEI::getInstance()->abrirAreaDados('10em');
  PaginaSEI::getInstance()->fecharAreaDados();*/
  PaginaSEI::getInstance()->montarAreaTabela($strResultado,$numRegistros,true);
  PaginaSEI::getInstance()->montarAreaDebug();
  PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>