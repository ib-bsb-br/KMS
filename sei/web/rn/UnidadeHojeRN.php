<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4Є REGIГO
*
* 26/11/2014 - criado por mga
*
* Versгo do Gerador de Cуdigo: 1.13.1
*
* Versгo no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class UnidadeHojeRN extends InfraRN {

  public static $TIPO_UNIDADE_HOJE_PROCESSOS = 1;
  public static $TIPO_UNIDADE_HOJE_DOCUMENTOS_UNIDADE_NAO_ASSINADOS = 2;
  public static $TIPO_UNIDADE_HOJE_DOCUMENTOS_UNIDADE_ASSINADOS = 3;
  public static $TIPO_UNIDADE_HOJE_DOCUMENTOS_BLOCO_NAO_ASSINADOS = 4;
  public static $TIPO_UNIDADE_HOJE_DOCUMENTOS_BLOCO_ASSINADOS = 5;
  public static $TIPO_UNIDADE_HOJE_USUARIO_ATRIBUICAO = 6;

  public static $TITULO_UNIDADE_HOJE_PROCESSOS 		= 'Processos na Unidade';
  public static $TITULO_UNIDADE_HOJE_DOCUMENTOS_ASSINATURA 		= 'Documentos da Unidade';
  public static $TITULO_UNIDADE_HOJE_DOCUMENTOS_UNIDADE_NAO_ASSINADOS 		= 'Documentos da Unidade nгo Assinados';
  public static $TITULO_UNIDADE_HOJE_DOCUMENTOS_UNIDADE_ASSINADOS 		= 'Documentos da Unidade Assinados';
  public static $TITULO_UNIDADE_HOJE_DOCUMENTOS_BLOCO 		= 'Documentos em Bloco';
  public static $TITULO_UNIDADE_HOJE_DOCUMENTOS_BLOCO_NAO_ASSINADOS 		= 'Documentos em Bloco nгo Assinados';
  public static $TITULO_UNIDADE_HOJE_DOCUMENTOS_BLOCO_ASSINADOS 		= 'Documentos em Bloco Assinados';
  public static $TITULO_UNIDADE_HOJE_USUARIO_ATRIBUICAO 		= 'Atribuiзгo de Processos';

  public function __construct(){
    parent::__construct();
  }
 
  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }

  protected function listarConectado(UnidadeHojeDTO $objUnidadeHojeDTO) {
    try {

      //Valida Permissao
      //SessaoSEI::getInstance()->validarAuditarPermissao('unidade_hoje_listar',__METHOD__,$objUnidadeHojeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objUnidadeHojeBD = new UnidadeHojeBD($this->getObjInfraIBanco());
      $ret = $objUnidadeHojeBD->listar($objUnidadeHojeDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando registros de Unidade Hoje.',$e);
    }
  }

  protected function gerarConectado() {
    try{

      SessaoSEI::getInstance()->validarAuditarPermissao('unidade_hoje_gerar', __METHOD__);

      ini_set('max_execution_time','600');
      ini_set('memory_limit','2048M');

      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objUnidadeHojeDTO = new UnidadeHojeDTO();

      $arrProcessosTipoQtde = array();
      $arrProcessosUsuarioQtde = array();
      $arrDocumentosAssinaturas = array();
      $arrBlocosAssinatura = array();
      $arrRetornosProgramados = array();
      $arrUltimasAcoes = array();

      $objUnidadeDTO = new UnidadeDTO();
      $objUnidadeDTO->setBolExclusaoLogica(false);
      $objUnidadeDTO->retStrSinProtocolo();
      $objUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

      $objUnidadeRN = new UnidadeRN();
      $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);

      if ($objUnidadeDTO==null){
        throw new InfraException('Unidade '.SessaoSEI::getInstance()->getStrSiglaUnidadeAtual().' nгo encontrada.');
      }

      $bolFlagProtocolo = ($objUnidadeDTO->getStrSinProtocolo()=='S');

      $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
      $bolHabilitarAutenticacaoDocExterno = $objInfraParametro->getValor('SEI_HABILITAR_AUTENTICACAO_DOCUMENTO_EXTERNO');


      $objAtividadeRN = new AtividadeRN();
      $objPesquisaPendenciaDTO = new PesquisaPendenciaDTO();
      $objPesquisaPendenciaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
      $objPesquisaPendenciaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      $objPesquisaPendenciaDTO->setStrSinHoje('S');
      $arrObjProcedimentoDTO = $objAtividadeRN->listarPendenciasRN0754($objPesquisaPendenciaDTO);

      $objUnidadeHojeDTO->setDblIdUnidadeHojeProcessosTipoQtde(BancoSEI::getInstance()->getValorSequencia('seq_unidade_hoje'));
      $objUnidadeHojeDTO->setDblIdUnidadeHojeProcessosUsuarioQtde(BancoSEI::getInstance()->getValorSequencia('seq_unidade_hoje'));
      $objUnidadeHojeDTO->setDblIdUnidadeHojeDocsUnidadeAssinados(BancoSEI::getInstance()->getValorSequencia('seq_unidade_hoje'));
      $objUnidadeHojeDTO->setDblIdUnidadeHojeDocsUnidadeNaoAssinados(BancoSEI::getInstance()->getValorSequencia('seq_unidade_hoje'));
      $objUnidadeHojeDTO->setDblIdUnidadeHojeDocsBlocoAssinados(BancoSEI::getInstance()->getValorSequencia('seq_unidade_hoje'));
      $objUnidadeHojeDTO->setDblIdUnidadeHojeDocsBlocoNaoAssinados(BancoSEI::getInstance()->getValorSequencia('seq_unidade_hoje'));

      $objUnidadeHojeBD = new UnidadeHojeBD($this->getObjInfraIBanco());

      $dthSnapshot = InfraData::getStrDataHoraAtual();

      foreach($arrObjProcedimentoDTO as $objProcedimentoDTO){

        $numIdTipoProcedimento = $objProcedimentoDTO->getNumIdTipoProcedimento();

        if (!isset($arrProcessosTipoQtde[$numIdTipoProcedimento])) {
          $arrProcessosTipoQtde[$numIdTipoProcedimento][0] = 1;
          $arrProcessosTipoQtde[$numIdTipoProcedimento][1] = $objProcedimentoDTO->getNumIdTipoProcedimento();
          $arrProcessosTipoQtde[$numIdTipoProcedimento][2] = $objProcedimentoDTO->getStrNomeTipoProcedimento();
        }else {
          $arrProcessosTipoQtde[$numIdTipoProcedimento][0]++;
        }

        //snapshot tipos na unidade
        $dto = new UnidadeHojeDTO();
        $dto->setDblIdUnidadeHoje($objUnidadeHojeDTO->getDblIdUnidadeHojeProcessosTipoQtde());
        $dto->setDblIdProcedimento($objProcedimentoDTO->getDblIdProcedimento());
        $dto->setDblIdDocumento(null);
        $dto->setNumIdBloco(null);
        $dto->setNumIdUsuarioAtribuicao(null);
        $dto->setDthSnapshot($dthSnapshot);
        $objUnidadeHojeBD->cadastrar($dto);


        $arrObjAtividadeDTO = $objProcedimentoDTO->getArrObjAtividadeDTO();

        if (count($arrObjAtividadeDTO) == 0 || $arrObjAtividadeDTO[0]->getNumIdUsuarioAtribuicao()==null) {
          $numIdUsuarioAtribuicao = '0';
          $strSiglaUsuarioAtribuicao = '[Sem atribuiзгo]';
          $strNomeUsuarioAtribuicao = '';
        }else{
          $numIdUsuarioAtribuicao = $arrObjAtividadeDTO[0]->getNumIdUsuarioAtribuicao();
          $strSiglaUsuarioAtribuicao = $arrObjAtividadeDTO[0]->getStrSiglaUsuarioAtribuicao();
          $strNomeUsuarioAtribuicao = $arrObjAtividadeDTO[0]->getStrNomeUsuarioAtribuicao();
        }

        if (!isset($arrProcessosUsuarioQtde[$numIdUsuarioAtribuicao])){
          $arrProcessosUsuarioQtde[$numIdUsuarioAtribuicao][0] = 1;
          $arrProcessosUsuarioQtde[$numIdUsuarioAtribuicao][1] = $numIdUsuarioAtribuicao;
          $arrProcessosUsuarioQtde[$numIdUsuarioAtribuicao][2] = $strSiglaUsuarioAtribuicao;
          $arrProcessosUsuarioQtde[$numIdUsuarioAtribuicao][3] = $strNomeUsuarioAtribuicao;
        }else{
          $arrProcessosUsuarioQtde[$numIdUsuarioAtribuicao][0]++;
        }

        $dto = new UnidadeHojeDTO();
        $dto->setDblIdUnidadeHoje($objUnidadeHojeDTO->getDblIdUnidadeHojeProcessosUsuarioQtde());
        $dto->setDblIdProcedimento($objProcedimentoDTO->getDblIdProcedimento());
        $dto->setDblIdDocumento(null);
        $dto->setNumIdBloco(null);
        $dto->setNumIdUsuarioAtribuicao($numIdUsuarioAtribuicao);
        $dto->setDthSnapshot($dthSnapshot);
        $objUnidadeHojeBD->cadastrar($dto);
      }

      if (count($arrObjProcedimentoDTO)) {

        $objDocumentoDTO = new DocumentoDTO();
        $objDocumentoDTO->retDblIdProcedimento();
        $objDocumentoDTO->retDblIdDocumento();
        $objDocumentoDTO->retNumIdSerie();
        $objDocumentoDTO->retStrNomeSerie();
        $objDocumentoDTO->retStrNumero();
        $objDocumentoDTO->setDblIdProcedimento(InfraArray::converterArrInfraDTO($arrObjProcedimentoDTO, 'IdProcedimento'), InfraDTO::$OPER_IN);
        $objDocumentoDTO->setNumIdUnidadeGeradoraProtocolo(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        if (($bolFlagProtocolo && $bolHabilitarAutenticacaoDocExterno=='1') || $bolHabilitarAutenticacaoDocExterno=='2'){
          $objDocumentoDTO->setStrStaProtocoloProtocolo(array(ProtocoloRN::$TP_DOCUMENTO_GERADO,ProtocoloRN::$TP_DOCUMENTO_RECEBIDO),InfraDTO::$OPER_IN);
        }else{
          $objDocumentoDTO->setStrStaProtocoloProtocolo(ProtocoloRN::$TP_DOCUMENTO_GERADO);
        }

        $objDocumentoRN = new DocumentoRN();
        $arrObjDocumentoDTO = $objDocumentoRN->listarRN0008($objDocumentoDTO);

        if (count($arrObjDocumentoDTO)){
          $objAssinaturaDTO = new AssinaturaDTO();
          $objAssinaturaDTO->setDistinct(true);
          $objAssinaturaDTO->retDblIdDocumento();
          $objAssinaturaDTO->setDblIdDocumento(InfraArray::converterArrInfraDTO($arrObjDocumentoDTO,'IdDocumento'),InfraDTO::$OPER_IN);

          $objAssinaturaRN = new AssinaturaRN();
          $arrObjAssinaturaDTO = InfraArray::indexarArrInfraDTO($objAssinaturaRN->listarRN1323($objAssinaturaDTO),'IdDocumento');
        }

        foreach($arrObjDocumentoDTO as $objDocumentoDTO) {

          $dblIdUnidadeHoje = null;

          $numIdSerie = $objDocumentoDTO->getNumIdSerie();

          if (!isset($arrDocumentosAssinaturas[$numIdSerie])){
            $arrDocumentosAssinaturas[$numIdSerie][0] = 0;
            $arrDocumentosAssinaturas[$numIdSerie][1] = 0;
            $arrDocumentosAssinaturas[$numIdSerie][2] = $objDocumentoDTO->getNumIdSerie();
            $arrDocumentosAssinaturas[$numIdSerie][3] = $objDocumentoDTO->getStrNomeSerie();
          }

          if (!isset($arrObjAssinaturaDTO[$objDocumentoDTO->getDblIdDocumento()])){
            $arrDocumentosAssinaturas[$numIdSerie][0]++;
            $dblIdUnidadeHoje = $objUnidadeHojeDTO->getDblIdUnidadeHojeDocsUnidadeNaoAssinados();
          }else{
            $arrDocumentosAssinaturas[$numIdSerie][1]++;
            $dblIdUnidadeHoje = $objUnidadeHojeDTO->getDblIdUnidadeHojeDocsUnidadeAssinados();
          }

          //snapshot documentos da unidade
          $dto = new UnidadeHojeDTO();
          $dto->setDblIdUnidadeHoje($dblIdUnidadeHoje);
          $dto->setDblIdProcedimento($objDocumentoDTO->getDblIdProcedimento());
          $dto->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
          $dto->setNumIdBloco(null);
          $dto->setNumIdUsuarioAtribuicao(null);
          $dto->setDthSnapshot($dthSnapshot);
          $objUnidadeHojeBD->cadastrar($dto);
        }
      }

      /////////
      $objBlocoDTO = new BlocoDTO();
      $objBlocoDTO->retNumIdBloco();
      //$objBlocoDTO->retNumIdUnidade();
      //$objBlocoDTO->retStrDescricao();
      //$objBlocoDTO->retStrStaTipo();
      //$objBlocoDTO->retStrIdxBloco();
      //$objBlocoDTO->retStrStaEstadoDescricao();
      //$objBlocoDTO->retStrTipoDescricao();
      $objBlocoDTO->retStrSiglaUnidade();
      $objBlocoDTO->retStrDescricaoUnidade();
      //$objBlocoDTO->retStrSinVazio();
      //$objBlocoDTO->retArrObjRelBlocoUnidadeDTO();
      $objBlocoDTO->setStrStaTipo(BlocoRN::$TB_ASSINATURA);
      $objBlocoDTO->setStrStaEstado(BlocoRN::$TE_CONCLUIDO, InfraDTO::$OPER_DIFERENTE);

      $objBlocoRN = new BlocoRN();
      $arrObjBlocoDTO = InfraArray::indexarArrInfraDTO($objBlocoRN->pesquisar($objBlocoDTO),'IdBloco');

      if (count($arrObjBlocoDTO)){

        $objRelBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
        $objRelBlocoProtocoloDTO->retNumIdBloco();
        $objRelBlocoProtocoloDTO->retDblIdProtocolo();
        $objRelBlocoProtocoloDTO->retDblIdProcedimentoDocumento();
        $objRelBlocoProtocoloDTO->setNumIdBloco(InfraArray::converterArrInfraDTO($arrObjBlocoDTO,'IdBloco'),InfraDTO::$OPER_IN);

        $objRelBlocoProtocoloRN = new RelBlocoProtocoloRN();
        $arrObjRelBlocoProtocoloDTO = $objRelBlocoProtocoloRN->listarProtocolosBloco($objRelBlocoProtocoloDTO);

        ////
        foreach($arrObjRelBlocoProtocoloDTO as $objRelBlocoProtocoloDTO) {

          $numIdBloco = $objRelBlocoProtocoloDTO->getNumIdBloco();

          if (!isset($arrBlocosAssinatura[$numIdBloco])){
            $arrBlocosAssinatura[$numIdBloco][0] = 0;
            $arrBlocosAssinatura[$numIdBloco][1] = 0;
            $arrBlocosAssinatura[$numIdBloco][2] = $numIdBloco;
            $arrBlocosAssinatura[$numIdBloco][3] = $arrObjBlocoDTO[$numIdBloco]->getStrSiglaUnidade();
            $arrBlocosAssinatura[$numIdBloco][4] = $arrObjBlocoDTO[$numIdBloco]->getStrDescricaoUnidade();
          }

          $dblIdUnidadeHoje = null;

          if (count($objRelBlocoProtocoloDTO->getArrObjAssinaturaDTO())==0){
            $arrBlocosAssinatura[$numIdBloco][0]++;
            $dblIdUnidadeHoje = $objUnidadeHojeDTO->getDblIdUnidadeHojeDocsBlocoNaoAssinados();
          }else{
            $arrBlocosAssinatura[$numIdBloco][1]++;
            $dblIdUnidadeHoje = $objUnidadeHojeDTO->getDblIdUnidadeHojeDocsBlocoAssinados();
          }

          //snapshot documentos da unidade
          $dto = new UnidadeHojeDTO();
          $dto->setDblIdUnidadeHoje($dblIdUnidadeHoje);
          $dto->setDblIdProcedimento($objRelBlocoProtocoloDTO->getDblIdProcedimentoDocumento());
          $dto->setDblIdDocumento($objRelBlocoProtocoloDTO->getDblIdProtocolo());
          $dto->setNumIdBloco($numIdBloco);
          $dto->setNumIdUsuarioAtribuicao(null);
          $dto->setDthSnapshot($dthSnapshot);
          $objUnidadeHojeBD->cadastrar($dto);
        }
      }

      $arrProcessosTipoQtde = array_values($arrProcessosTipoQtde);
      InfraArray::ordenarArray($arrProcessosTipoQtde, 2, InfraArray::$TIPO_ORDENACAO_ASC);

      $arrProcessosUsuarioQtde = array_values($arrProcessosUsuarioQtde);
      InfraArray::ordenarArray($arrProcessosUsuarioQtde, 2, InfraArray::$TIPO_ORDENACAO_ASC);

      $arrDocumentosAssinaturas = array_values($arrDocumentosAssinaturas);
      InfraArray::ordenarArray($arrDocumentosAssinaturas, 3, InfraArray::$TIPO_ORDENACAO_ASC);

      $arrBlocosAssinatura = array_values($arrBlocosAssinatura);
      InfraArray::ordenarArray($arrBlocosAssinatura, 2, InfraArray::$TIPO_ORDENACAO_ASC);

      $objUnidadeHojeDTO->setArrProcessosTipoQtde($arrProcessosTipoQtde);
      $objUnidadeHojeDTO->setArrProcessosUsuarioQtde($arrProcessosUsuarioQtde);
      $objUnidadeHojeDTO->setArrDocumentosAssinaturas($arrDocumentosAssinaturas);
      $objUnidadeHojeDTO->setArrBlocosAssinatura($arrBlocosAssinatura);
      $objUnidadeHojeDTO->setArrRetornosProgramados($arrRetornosProgramados);
      $objUnidadeHojeDTO->setArrUltimasAcoes($arrUltimasAcoes);

      return $objUnidadeHojeDTO;

    }catch(Exception $e){
      throw new InfraException('Erro gerando dados de unidade hoje.',$e);
    }
  }

}
?>