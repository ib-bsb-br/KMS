<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 15/09/2008 - criado por marcio_db
*
*
*/

try {
  require_once dirname(__FILE__).'/SEI.php';

  
  session_start();
  
  //////////////////////////////////////////////////////////////////////////////
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(false);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEIExterna::getInstance()->validarLink();

  $objAcessoExternoDTO = new AcessoExternoDTO();
  $objAcessoExternoDTO->setNumIdAcessoExterno($_GET['id_acesso_externo']);
  $objAcessoExternoDTO->setDblIdProtocoloConsulta($_GET['id_documento']);

  $objAcessoExternoRN = new AcessoExternoRN();
  $objAcessoExternoDTO = $objAcessoExternoRN->consultarProcessoAcessoExterno($objAcessoExternoDTO);
  $objProcedimentoDTO = $objAcessoExternoDTO->getObjProcedimentoDTO();

  $objDocumentoDTO = null;

  foreach ($objProcedimentoDTO->getArrObjRelProtocoloProtocoloDTO() as $objRelProtocoloProtocoloDTO) {
    if ($objRelProtocoloProtocoloDTO->getStrStaAssociacao() == RelProtocoloProtocoloRN::$TA_DOCUMENTO_ASSOCIADO &&
        $objRelProtocoloProtocoloDTO->getStrSinAcessoExterno()=='S' &&
        $objRelProtocoloProtocoloDTO->getDblIdProtocolo2() == $_GET['id_documento']){

      $objDocumentoDTO = $objRelProtocoloProtocoloDTO->getObjProtocoloDTO2();

		  break;
		}
	}

	if ($objDocumentoDTO == null){
		throw new InfraException('Documento não encontrado.');
	}

  $strTitulo = $objDocumentoDTO->getStrNomeSerie().' '.$objDocumentoDTO->getStrSiglaUnidadeGeradoraProtocolo().' '.$objDocumentoDTO->getStrProtocoloDocumentoFormatado();

  $objDocumentoRN = new DocumentoRN();
  $objDocumentoRN->bloquearConsultado($objDocumentoDTO);

  if ($objDocumentoDTO->getStrStaDocumento()==DocumentoRN::$TD_EDITOR_EDOC){
    if ($objDocumentoDTO->getDblIdDocumentoEdoc()!=null){

      $objEDocRN = new EDocRN();
      //echo EDocINT::montarVisualizacaoDocumento($objDocumentoDTO->getDblIdDocumentoEdoc());
      echo $objEDocRN->consultarHTMLDocumentoRN1204($objDocumentoDTO);
      
    }else{
      echo 'Documento sem conteúdo.';
    }
  }else if ($objDocumentoDTO->getStrStaDocumento()==DocumentoRN::$TD_EDITOR_INTERNO){

    $objEditorDTO = new EditorDTO();
    $objEditorDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
    $objEditorDTO->setNumIdBaseConhecimento(null);
    $objEditorDTO->setStrSinCabecalho('S');
    $objEditorDTO->setStrSinRodape('S');
    $objEditorDTO->setStrSinCarimboPublicacao('S');
    $objEditorDTO->setStrSinIdentificacaoVersao('N');


    $objEditorRN = new EditorRN();

    PaginaSEI::montarHeaderDownload(null, null, 'Content-Type: text/html; charset=iso-8859-1', true);

    echo  $objEditorRN->consultarHtmlVersao($objEditorDTO);

  //links para anexos de documentos de email
  }else if (isset($_GET['id_anexo'])){

    $objAnexoDTO = new AnexoDTO();
    $objAnexoDTO->retNumIdAnexo();
    $objAnexoDTO->retStrNome();
    $objAnexoDTO->retStrHash();
    $objAnexoDTO->retDblIdProtocolo();
    $objAnexoDTO->setNumIdAnexo($_GET['id_anexo']);
    $objAnexoDTO->retDthInclusao();
     
    $objAnexoRN = new AnexoRN();
    $objAnexoDTO = $objAnexoRN->consultarRN0736($objAnexoDTO);

    SeiINT::download($objAnexoDTO, null, null, SeiINT::getContentDisposition($objAnexoDTO->getStrNome()), false, $objAnexoDTO->getStrNome(), $objAnexoDTO->getDblIdProtocolo());

  }else if ($objDocumentoDTO->getStrStaProtocoloProtocolo()==ProtocoloRN::$TP_DOCUMENTO_RECEBIDO){

    $objAnexoDTO = new AnexoDTO();
    $objAnexoDTO->retNumIdAnexo();
    $objAnexoDTO->retStrNome();
    $objAnexoDTO->retNumIdAnexo();
    $objAnexoDTO->retStrHash();
    $objAnexoDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdDocumento());
    $objAnexoDTO->retDblIdProtocolo();
    $objAnexoDTO->retDthInclusao();
    $objAnexoDTO->retStrProtocoloFormatadoProtocolo();
    
    $objAnexoRN = new AnexoRN();
    $arrObjAnexoDTO = $objAnexoRN->listarRN0218($objAnexoDTO);

    if (count($arrObjAnexoDTO)!=1){
      $strResultado = '';
    }else{
      SeiINT::download($arrObjAnexoDTO[0], null, null, SeiINT::getContentDisposition($arrObjAnexoDTO[0]->getStrNome()), false, $arrObjAnexoDTO[0]->getStrProtocoloFormatadoProtocolo(), $arrObjAnexoDTO[0]->getDblIdProtocolo());
    }
    
  }else{

    $dto = new DocumentoDTO();
    $dto->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
    $dto->setObjInfraSessao(SessaoSEIExterna::getInstance());
    $dto->setStrLinkDownload('documento_consulta_externa.php?id_acesso_externo='.$_GET['id_acesso_externo'].'&id_documento='.$_GET['id_documento']);

    PaginaSEI::montarHeaderDownload(null, null, 'Content-Type: text/html; charset=iso-8859-1', true);

    echo $objDocumentoRN->consultarHtmlFormulario($dto);

  }
   
  AuditoriaSEI::getInstance()->auditar('documento_consulta_externa', __FILE__, $objDocumentoDTO);

}catch(Exception $e){

  if (!($e instanceof InfraException) || $e->isBolPermitirGravacaoLog()) {
    try {
      LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
    } catch (Exception $e2) {
    }
  }

  PaginaSEIExterna::getInstance()->processarExcecao($e);
}
?>