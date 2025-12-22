<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
*/

require_once dirname(__FILE__).'/../SEI.php';

class IndexacaoRN extends InfraRN {

  public static $TO_PROTOCOLO_METADADOS = '1';
  public static $TO_PROTOCOLO_METADADOS_E_CONTEUDO = '2';
  public static $TO_PROCESSO_COM_DOCUMENTOS_METADADOS = '3';
  public static $TO_PROCESSO_COM_DOCUMENTOS_METADADOS_E_CONTEUDO = '4';
  public static $TO_PUBLICACAO = '5';
  public static $TO_BASE_CONHECIMENTO_LIBERAR = '6';
  public static $TO_BASE_CONHECIMENTO_CANCELAR_LIBERACAO = '7';
  
  public static $TAI_PUBLICO = 'P';
  public static $TAI_RESTRITO = 'R';

  public function __construct(){
    parent::__construct(); 
  }

 	protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }

  private function montarAssinantes($arrIdDocumentos){
    try {

      $arrRet = array();

      $objAssinaturaDTO = new AssinaturaDTO();
      $objAssinaturaDTO->retDblIdDocumento();
      $objAssinaturaDTO->retNumIdUsuario();
      $objAssinaturaDTO->setDblIdDocumento($arrIdDocumentos, InfraDTO::$OPER_IN);

      $objAssinaturaRN = new AssinaturaRN();
      $arrObjAssinaturaDTOTodos = InfraArray::indexarArrInfraDTO($objAssinaturaRN->listarRN1323($objAssinaturaDTO), 'IdDocumento', true);

      foreach($arrIdDocumentos as $dblIdDocumento) {

        $strIdAssinante = '';
        if (isset($arrObjAssinaturaDTOTodos[$dblIdDocumento])) {
          foreach ($arrObjAssinaturaDTOTodos[$dblIdDocumento] as $objAssinaturaDTO) {
            $strIdAssinante .= $objAssinaturaDTO->getNumIdUsuario() . ';';
          }
          if ($strIdAssinante != '') {
            $strIdAssinante = ';' . $strIdAssinante;
          }
        }

        if ($strIdAssinante==''){
          $strIdAssinante = 'NULL';
        }

        $arrRet[$dblIdDocumento] = $strIdAssinante;
      }

      return $arrRet;

    }catch (Exception $e){
      throw new InfraException('Erro montando assinantes.',$e);
    }
  }

  private function montarUnidadesAcesso($arrIdProcedimentos){
    try {

      $arrRet = array();

      $objAcessoDTO = new AcessoDTO();
      $objAcessoDTO->setDistinct(true);
      $objAcessoDTO->retDblIdProtocolo();
      $objAcessoDTO->retNumIdUnidade();
      $objAcessoDTO->setDblIdProtocolo($arrIdProcedimentos, InfraDTO::$OPER_IN);
      $objAcessoDTO->setStrStaTipo(array(AcessoRN::$TA_RESTRITO_UNIDADE, AcessoRN::$TA_CONTROLE_INTERNO),InfraDTO::$OPER_IN);

      $objAcessoRN = new AcessoRN();
      $arrObjAcessoDTO = InfraArray::indexarArrInfraDTO($objAcessoRN->listar($objAcessoDTO),'IdProtocolo',true);

      foreach($arrIdProcedimentos as $dblIdProcedimento){
        $strIdUnidadeAcesso = '';
        if (isset($arrObjAcessoDTO[$dblIdProcedimento])) {
          foreach ($arrObjAcessoDTO[$dblIdProcedimento] as $objAcessoDTO) {
            $strIdUnidadeAcesso .= $objAcessoDTO->getNumIdUnidade().';';
          }
          if ($strIdUnidadeAcesso!=''){
            $strIdUnidadeAcesso = ';'.$strIdUnidadeAcesso;
          }
        }

        if ($strIdUnidadeAcesso=='') {
          $strIdUnidadeAcesso = 'NULL';
        }

        $arrRet[$dblIdProcedimento] = $strIdUnidadeAcesso;
      }

      return $arrRet;

    }catch (Exception $e){
      throw new InfraException('Erro montando unidades de acesso.',$e);
    }
  }

  private function montarUnidadesTramitacao($arrIdProcedimentos){
    try {

      $arrRet = array();

      $objAtividadeDTO = new AtividadeDTO();
      $objAtividadeDTO->setDistinct(true);
      $objAtividadeDTO->retDblIdProtocolo();
      $objAtividadeDTO->retNumIdUnidade();
      $objAtividadeDTO->setStrStaNivelAcessoGlobalProtocolo(ProtocoloRN::$NA_SIGILOSO, InfraDTO::$OPER_DIFERENTE);
      $objAtividadeDTO->setDblIdProtocolo($arrIdProcedimentos,InfraDTO::$OPER_IN);
      $objAtividadeDTO->setNumIdTarefa(array(TarefaRN::$TI_GERACAO_PROCEDIMENTO, TarefaRN::$TI_PROCESSO_REMETIDO_UNIDADE), InfraDTO::$OPER_IN);

      $objAtividadeRN = new AtividadeRN();
      $arrObjAtividadeDTOTodos = InfraArray::indexarArrInfraDTO($objAtividadeRN->listarRN0036($objAtividadeDTO),'IdProtocolo',true);

      foreach($arrIdProcedimentos as $dblIdProcedimento){
        $strIdUnidadeTramitacao = '';
        if (isset($arrObjAtividadeDTOTodos[$dblIdProcedimento])) {
          foreach ($arrObjAtividadeDTOTodos[$dblIdProcedimento] as $objAtividadeDTO) {
            $strIdUnidadeTramitacao .= $objAtividadeDTO->getNumIdUnidade().';';
          }
          if ($strIdUnidadeTramitacao!=''){
            $strIdUnidadeTramitacao = ';'.$strIdUnidadeTramitacao;
          }
        }

        if ($strIdUnidadeTramitacao=='') {
          $strIdUnidadeTramitacao = 'NULL';
        }

        $arrRet[$dblIdProcedimento] = $strIdUnidadeTramitacao;
      }

      return $arrRet;

    }catch (Exception $e){
      throw new InfraException('Erro montando unidades de tramitação.',$e);
    }
  }

  protected function indexarProtocoloConectado(IndexacaoDTO $parObjIndexacaoDTO){
  	
  	try {

      if (FeedSEIProtocolos::getInstance()->isBolIgnorarFeeds()){
        return;
      }

      $strStaOperacao = $parObjIndexacaoDTO->getStrStaOperacao();
      $arrIdProtocolos = $parObjIndexacaoDTO->getArrIdProtocolos();

     	if (count($arrIdProtocolos)){

        $objRelProtocoloProtocoloRN 	= new RelProtocoloProtocoloRN();

	  		if ($strStaOperacao==IndexacaoRN::$TO_PROCESSO_COM_DOCUMENTOS_METADADOS ||
            $strStaOperacao==IndexacaoRN::$TO_PROCESSO_COM_DOCUMENTOS_METADADOS_E_CONTEUDO) {

          //buscar processos anexados
          $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
          $objRelProtocoloProtocoloDTO->retDblIdProtocolo2();
          $objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);
          $objRelProtocoloProtocoloDTO->setDblIdProtocolo1($arrIdProtocolos, InfraDTO::$OPER_IN);
          $arrIdProtocolos = array_merge($arrIdProtocolos, InfraArray::converterArrInfraDTO($objRelProtocoloProtocoloRN->listarRN0187($objRelProtocoloProtocoloDTO), 'IdProtocolo2'));

          //buscar documentos dos processos
	      	$objRelProtocoloProtocoloDTO 	= new RelProtocoloProtocoloDTO();
	      	$objRelProtocoloProtocoloDTO->retDblIdProtocolo2();
	      	$objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_DOCUMENTO_ASSOCIADO);
	      	$objRelProtocoloProtocoloDTO->setDblIdProtocolo1($arrIdProtocolos, InfraDTO::$OPER_IN);
	      	$arrIdDocumentos = InfraArray::converterArrInfraDTO($objRelProtocoloProtocoloRN->listarRN0187($objRelProtocoloProtocoloDTO),'IdProtocolo2');

        }else{
          $arrIdDocumentos = array();
        }

        $arrIdProtocolos = array_merge($arrIdProtocolos, $arrIdDocumentos);

	 		  $objProtocoloDTO = new ProtocoloDTO();
		  	$objProtocoloDTO->retStrStaProtocolo();
		  	$objProtocoloDTO->retNumIdTipoProcedimentoProcedimento();
				$objProtocoloDTO->retDblIdProcedimentoDocumento();
		  	$objProtocoloDTO->retNumIdTipoProcedimentoDocumento();
		  	$objProtocoloDTO->retNumIdSerieDocumento();
		  	$objProtocoloDTO->retStrDescricao();
		  	$objProtocoloDTO->retStrProtocoloFormatado();
		  	$objProtocoloDTO->retStrProtocoloFormatadoPesquisa();
		  	$objProtocoloDTO->retNumIdUsuarioGerador();
		  	$objProtocoloDTO->retStrNumeroDocumento();
		  	$objProtocoloDTO->retStrStaProtocolo();
		  	$objProtocoloDTO->retDtaGeracao();
		  	$objProtocoloDTO->retDblIdProtocolo();
        $objProtocoloDTO->retNumIdUnidadeGeradora();
		  	$objProtocoloDTO->retNumIdOrgaoUnidadeGeradora();
		  	$objProtocoloDTO->retStrStaNivelAcessoGlobal();
		  	$objProtocoloDTO->retStrProtocoloFormatadoProcedimentoDocumento();
		  	$objProtocoloDTO->retDblIdProcedimentoDocumento();
        $objProtocoloDTO->retStrStaDocumentoDocumento();
		  	$objProtocoloDTO->retStrStaEstado();
		  	
		  	if ($strStaOperacao==IndexacaoRN::$TO_PROTOCOLO_METADADOS_E_CONTEUDO ||
            $strStaOperacao==IndexacaoRN::$TO_PROCESSO_COM_DOCUMENTOS_METADADOS_E_CONTEUDO){
		  	  $objProtocoloDTO->retStrConteudoDocumento();
		  	}
	
	  	  $objProtocoloDTO->setDblIdProtocolo($arrIdProtocolos,InfraDTO::$OPER_IN);
				
		  	$objProtocoloRN	= new ProtocoloRN();
        $objDocumentoRN = new DocumentoRN();
				$arrObjProtocoloDTO = $objProtocoloRN->listarRN0668($objProtocoloDTO);

        $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
        $numIdSerieOuvidoria = $objInfraParametro->getValor('ID_SERIE_OUVIDORIA');

        $arrIdProcedimentos = array();
				$arrIdDocumentosGerados = array();
				$arrObjProtocoloDTOProcessos = array();
				$arrObjProtocoloDTODocumentos = array();
				foreach($arrObjProtocoloDTO as $objProtocoloDTO){
				  if ($objProtocoloDTO->getStrStaProtocolo()==ProtocoloRN::$TP_PROCEDIMENTO){

            $arrObjProtocoloDTOProcessos[] = $objProtocoloDTO;
            $arrIdProcedimentos[] = $objProtocoloDTO->getDblIdProtocolo();

				  }else if ($objProtocoloDTO->getStrStaProtocolo()==ProtocoloRN::$TP_DOCUMENTO_GERADO || $objProtocoloDTO->getStrStaProtocolo()==ProtocoloRN::$TP_DOCUMENTO_RECEBIDO){

            $arrObjProtocoloDTODocumentos[] = $objProtocoloDTO;

				    if ($objProtocoloDTO->getStrStaProtocolo()==ProtocoloRN::$TP_DOCUMENTO_GERADO){
				      $arrIdDocumentosGerados[] = $objProtocoloDTO->getDblIdProtocolo();
				    }

            $arrIdProcedimentos[] = $objProtocoloDTO->getDblIdProcedimentoDocumento();
				  }
				}

		  	$objObservacaoDTO = new ObservacaoDTO();
		  	$objObservacaoDTO->retDblIdProtocolo();
		  	$objObservacaoDTO->retNumIdUnidade();
		  	$objObservacaoDTO->retStrDescricao();
		  	$objObservacaoDTO->setDblIdProtocolo($arrIdProtocolos,InfraDTO::$OPER_IN);
		  	
		  	$objObservacaoRN 	= new ObservacaoRN();
		  	$arrObservacaoDTO = InfraArray::indexarArrInfraDTO($objObservacaoRN->listarRN0219($objObservacaoDTO),'IdProtocolo', true);

		  	// Monta a string de ids de assunto	  	
		  	$objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
		  	$objRelProtocoloAssuntoDTO->retDblIdProtocolo();
		  	$objRelProtocoloAssuntoDTO->retNumIdAssuntoProxy();
		  	$objRelProtocoloAssuntoDTO->setDblIdProtocolo($arrIdProtocolos,InfraDTO::$OPER_IN);
		  	
		  	$objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();
		  	$arrRelProtocoloAssuntoDTO = InfraArray::indexarArrInfraDTO($objRelProtocoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO),'IdProtocolo',true);
	
		  	$objParticipanteDTO = new ParticipanteDTO();
		  	$objParticipanteDTO->retDblIdProtocolo();
		  	$objParticipanteDTO->retNumIdContato();
        $objParticipanteDTO->retStrStaParticipacao();
		  	$objParticipanteDTO->setStrStaParticipacao(array(ParticipanteRN::$TP_INTERESSADO, ParticipanteRN::$TP_REMETENTE, ParticipanteRN::$TP_DESTINATARIO),InfraDTO::$OPER_IN);
		  	$objParticipanteDTO->setDblIdProtocolo($arrIdProtocolos,InfraDTO::$OPER_IN);
		  	
		  	$objParticipanteRN = new ParticipanteRN();
		  	$arrObjParticipanteDTO = InfraArray::indexarArrInfraDTO($objParticipanteRN->listarRN0189($objParticipanteDTO),'IdProtocolo',true);

        if (count($arrObjProtocoloDTODocumentos)){
          $arrIdAssinaturas = $this->montarAssinantes(InfraArray::converterArrInfraDTO($arrObjProtocoloDTODocumentos,'IdProtocolo'));
        }

	  	  if (count($arrIdDocumentosGerados)){
          $objPublicacaoDTO = new PublicacaoDTO();
          $objPublicacaoDTO->retDblIdDocumento();
          $objPublicacaoDTO->retStrStaEstado();
          $objPublicacaoDTO->setDblIdDocumento($arrIdDocumentosGerados, InfraDTO::$OPER_IN);
            
          $objPublicacaoRN = new PublicacaoRN();
          $arrObjPublicacaoDTO = InfraArray::indexarArrInfraDTO($objPublicacaoRN->listarRN1045($objPublicacaoDTO),'IdDocumento');
            
          foreach($arrObjProtocoloDTO as $objProtocoloDTO){
            //se o documento gerado tem registro de publicacao
            if (isset($arrObjPublicacaoDTO[$objProtocoloDTO->getDblIdProtocolo()]) && $arrObjPublicacaoDTO[$objProtocoloDTO->getDblIdProtocolo()]->getStrStaEstado()==PublicacaoRN::$TE_PUBLICADO){
              $objProtocoloDTO->setStrSinPublicado('S');
            }else{
              $objProtocoloDTO->setStrSinPublicado('N');
            }
          }
	  	  }

        $arrIdUnidadesAcesso =  $this->montarUnidadesAcesso($arrIdProcedimentos);
        $arrIdUnidadesTramitacao =  $this->montarUnidadesTramitacao($arrIdProcedimentos);

	    	$arrIdProtocolosRemocao = array();
	    	
	  		foreach($arrObjProtocoloDTO as $objProtocoloDTO){
	  		  
	  		  //sigilosos e cancelados nao devem ser indexados
	  		  if ($objProtocoloDTO->getStrStaNivelAcessoGlobal()==ProtocoloRN::$NA_SIGILOSO || $objProtocoloDTO->getStrStaEstado()==ProtocoloRN::$TE_DOCUMENTO_CANCELADO){
            $arrIdProtocolosRemocao[] = $objProtocoloDTO->getDblIdProtocolo();
	  		    continue;
	  		  }
	  		  
	  		  $dblIdProtocolo = $objProtocoloDTO->getDblIdProtocolo();
          $strStaProtocolo = $objProtocoloDTO->getStrStaProtocolo();
	  		  
	  		  $arrFormatadoFeed = array();
          $arrFormatadoFeed['id_prot'] = $objProtocoloDTO->getDblIdProtocolo();
	  		  $arrFormatadoFeed['desc'] = $objProtocoloDTO->getStrDescricao();
          $arrFormatadoFeed['id_org_ger']	= $objProtocoloDTO->getNumIdOrgaoUnidadeGeradora();
	  	  	$arrFormatadoFeed['id_uni_ger'] = $objProtocoloDTO->getNumIdUnidadeGeradora();
          $arrFormatadoFeed['prot_pesq']	= $objProtocoloDTO->getStrProtocoloFormatadoPesquisa();
	  	  	$arrFormatadoFeed['id_serie'] = $objProtocoloDTO->getNumIdSerieDocumento();
	  	  	$arrFormatadoFeed['dta_ger'] = $objProtocoloDTO->getDtaGeracao();
	  	  	$arrFormatadoFeed['id_usu_ger'] = $objProtocoloDTO->getNumIdUsuarioGerador();
	  	  	$arrFormatadoFeed['sta_prot'] = $strStaProtocolo;
	  	  	$arrFormatadoFeed['numero']	= $objProtocoloDTO->getStrNumeroDocumento();

	   	    $strIdInteressado = '';
          $strIdRemetente = '';
          $strIdDestinatario = '';
	   	    if (isset($arrObjParticipanteDTO[$dblIdProtocolo])){

	   	      foreach($arrObjParticipanteDTO[$dblIdProtocolo] as $objParticipanteDTO){

              switch($objParticipanteDTO->getStrStaParticipacao()){
                case ParticipanteRN::$TP_INTERESSADO:
                  $strIdInteressado .= $objParticipanteDTO->getNumIdContato().';';
                  break;

                case ParticipanteRN::$TP_REMETENTE:
                  $strIdRemetente .= $objParticipanteDTO->getNumIdContato().';';
                  break;

                case ParticipanteRN::$TP_DESTINATARIO:
                  $strIdDestinatario .= $objParticipanteDTO->getNumIdContato().';';
                  break;
              }
	   	      }

						if ($strIdInteressado!=''){
							$strIdInteressado = ';'.$strIdInteressado;
						}
            if ($strIdRemetente!=''){
              $strIdRemetente = ';'.$strIdRemetente;
            }
            if ($strIdDestinatario!=''){
              $strIdDestinatario = ';'.$strIdDestinatario;
            }
	   	    }

          if ($strIdInteressado==''){
            $strIdInteressado = 'NULL';
          }

          if ($strIdRemetente==''){
            $strIdRemetente = 'NULL';
          }

          if ($strIdDestinatario==''){
            $strIdDestinatario = 'NULL';
          }

	  	  	$arrFormatadoFeed['id_int'] = $strIdInteressado;
          $arrFormatadoFeed['id_rem'] = $strIdRemetente;
          $arrFormatadoFeed['id_dest'] = $strIdDestinatario;
	  	  	
	  	  	if (isset($arrObservacaoDTO[$dblIdProtocolo])){
	    	  	foreach($arrObservacaoDTO[$dblIdProtocolo] as $objObservacaoDTO){
	    			  $arrFormatadoFeed['obs_'.$objObservacaoDTO->getNumIdUnidade()] = $objObservacaoDTO->getStrDescricao();
	    	  	}
	  	  	}
	  	  	
	  	  	$strProtocoloAssunto = '';
	  	  	if (isset($arrRelProtocoloAssuntoDTO[$dblIdProtocolo])){
	  	  	  foreach($arrRelProtocoloAssuntoDTO[$dblIdProtocolo] as $objRelProtocoloAssuntoDTO){
	  	  	    $strProtocoloAssunto .= $objRelProtocoloAssuntoDTO->getNumIdAssuntoProxy().';';
	  	  	  }
						if ($strProtocoloAssunto!=''){
							$strProtocoloAssunto = ';'.$strProtocoloAssunto;
						}
	  	  	}

          if ($strProtocoloAssunto==''){
            $strProtocoloAssunto = 'NULL';
          }

	  	  	$arrFormatadoFeed['id_assun'] = $strProtocoloAssunto;

          //se documento interno sem assinatura ou formulario da ouvidoria entao deixar apenas para a unidade geradora
          if ((($objProtocoloDTO->getStrStaDocumentoDocumento() == DocumentoRN::$TD_EDITOR_INTERNO ||
                $objProtocoloDTO->getStrStaDocumentoDocumento() == DocumentoRN::$TD_FORMULARIO_GERADO ||
                $objProtocoloDTO->getStrStaDocumentoDocumento() == DocumentoRN::$TD_EDITOR_EDOC) && $arrIdAssinaturas[$dblIdProtocolo]=='NULL')
               ||
              ($objProtocoloDTO->getStrStaDocumentoDocumento() == DocumentoRN::$TD_FORMULARIO_AUTOMATICO && $objProtocoloDTO->getNumIdSerieDocumento() == $numIdSerieOuvidoria)) {

            $arrFormatadoFeed['id_uni_aces'] = ';' . $objProtocoloDTO->getNumIdUnidadeGeradora() . ';';
            $arrFormatadoFeed['tipo_aces'] = self::$TAI_RESTRITO;

	        }else{

            if ($strStaProtocolo==ProtocoloRN::$TP_PROCEDIMENTO) {
              $arrFormatadoFeed['id_uni_aces'] = $arrIdUnidadesAcesso[$dblIdProtocolo];
            }else{
              $arrFormatadoFeed['id_uni_aces'] = $arrIdUnidadesAcesso[$objProtocoloDTO->getDblIdProcedimentoDocumento()];
            }

	          if ($objProtocoloDTO->getStrStaNivelAcessoGlobal()==ProtocoloRN::$NA_PUBLICO || ($strStaProtocolo==ProtocoloRN::$TP_DOCUMENTO_GERADO && $objProtocoloDTO->getStrSinPublicado()=='S')){
	        	  $arrFormatadoFeed['tipo_aces'] = self::$TAI_PUBLICO;
	    	  	}else{
	    	  	  $arrFormatadoFeed['tipo_aces'] = self::$TAI_RESTRITO;
	    	  	}
	        }

          if ($strStaProtocolo==ProtocoloRN::$TP_DOCUMENTO_GERADO || $strStaProtocolo==ProtocoloRN::$TP_DOCUMENTO_RECEBIDO) {
            $arrFormatadoFeed['id_assin'] = $arrIdAssinaturas[$dblIdProtocolo];
          }

	  	  	switch ($strStaProtocolo) {
	  	  	  
	  	  		case ProtocoloRN::$TP_PROCEDIMENTO:

							$arrFormatadoFeed['id_proc'] = $objProtocoloDTO->getDblIdProtocolo();
							$arrFormatadoFeed['id_doc'] = null;
							$arrFormatadoFeed['id_anexo'] = null;
	  					$arrFormatadoFeed['id_tipo_proc'] = $objProtocoloDTO->getNumIdTipoProcedimentoProcedimento();
              $arrFormatadoFeed['id_uni_tram'] = $arrIdUnidadesTramitacao[$objProtocoloDTO->getDblIdProtocolo()];
	  					$arrFormatadoFeed['prot_proc'] = $objProtocoloDTO->getStrProtocoloFormatado();
	  					$arrFormatadoFeed['prot_doc'] = null;


	  					$objInfraFeedDTO = new InfraFeedDTO();
	    		  	$objInfraFeedDTO->setStrUrl('P'.$objProtocoloDTO->getDblIdProtocolo());
	  					$objInfraFeedDTO->setStrMimeType('text/plain');
	  					$objInfraFeedDTO->setArrMetaTags($arrFormatadoFeed);
	  					$objInfraFeedDTO->setStrCaminhoArquivo(null);
	  					$objInfraFeedDTO->setBinConteudo(null);
	  					
	  					FeedSEIProtocolos::getInstance()->adicionarFeed($objInfraFeedDTO);
	  
	  					break;	  			
	  					
	  				case ProtocoloRN::$TP_DOCUMENTO_GERADO:

							$arrFormatadoFeed['id_proc'] = $objProtocoloDTO->getDblIdProcedimentoDocumento();
							$arrFormatadoFeed['id_doc'] = $objProtocoloDTO->getDblIdProtocolo();
							$arrFormatadoFeed['id_anexo'] = null;
	  					$arrFormatadoFeed['id_tipo_proc'] = $objProtocoloDTO->getNumIdTipoProcedimentoDocumento();
              $arrFormatadoFeed['id_uni_tram'] = $arrIdUnidadesTramitacao[$objProtocoloDTO->getDblIdProcedimentoDocumento()];
	  					$arrFormatadoFeed['prot_proc']	= $objProtocoloDTO->getStrProtocoloFormatadoProcedimentoDocumento();
	  					$arrFormatadoFeed['prot_doc'] = $objProtocoloDTO->getStrProtocoloFormatado();

	  					$objInfraFeedDTO = new InfraFeedDTO();
	    		  	$objInfraFeedDTO->setStrUrl('D'.$objProtocoloDTO->getDblIdProtocolo());
	  					$objInfraFeedDTO->setStrMimeType('text/html');
	  					$objInfraFeedDTO->setArrMetaTags($arrFormatadoFeed);
	  					$objInfraFeedDTO->setStrCaminhoArquivo(null);

	  					if ($strStaOperacao==IndexacaoRN::$TO_PROTOCOLO_METADADOS_E_CONTEUDO ||
                  $strStaOperacao==IndexacaoRN::$TO_PROCESSO_COM_DOCUMENTOS_METADADOS_E_CONTEUDO){

	  					  if ($objProtocoloDTO->getStrStaDocumentoDocumento()==DocumentoRN::$TD_FORMULARIO_AUTOMATICO){
                  $objDocumentoDTO = new DocumentoDTO();
                  $objDocumentoDTO->setDblIdDocumento($objProtocoloDTO->getDblIdProtocolo());
                  $objProtocoloDTO->setStrConteudoDocumento($objDocumentoRN->consultarHtmlFormulario($objDocumentoDTO));
                }

                $objInfraFeedDTO->setBinConteudo(DocumentoINT::limparHtml($objProtocoloDTO->getStrConteudoDocumento()));

	  					}else{
	  					  $objInfraFeedDTO->setBinConteudo(null);
	  					}

	  					FeedSEIProtocolos::getInstance()->adicionarFeed($objInfraFeedDTO);
	  						  	  			
	  	  			break; 
	  	  			
	  	  		case ProtocoloRN::$TP_DOCUMENTO_RECEBIDO:

							$arrFormatadoFeed['id_proc'] = $objProtocoloDTO->getDblIdProcedimentoDocumento();
							$arrFormatadoFeed['id_doc'] = $objProtocoloDTO->getDblIdProtocolo();
							$arrFormatadoFeed['id_anexo'] = null;
	  					$arrFormatadoFeed['id_tipo_proc']	= $objProtocoloDTO->getNumIdTipoProcedimentoDocumento();
              $arrFormatadoFeed['id_uni_tram'] = $arrIdUnidadesTramitacao[$objProtocoloDTO->getDblIdProcedimentoDocumento()];
	  					$arrFormatadoFeed['prot_proc'] = $objProtocoloDTO->getStrProtocoloFormatadoProcedimentoDocumento();
	  					$arrFormatadoFeed['prot_doc'] = $objProtocoloDTO->getStrProtocoloFormatado();

	  					// Monta os arrays de anexos de documento
	  			  	$objAnexoDTO =	new AnexoDTO();
	  			  	$objAnexoDTO->retNumIdAnexo();
	  			  	$objAnexoDTO->retStrNome();
	  			  	$objAnexoDTO->retDthInclusao();
              $objAnexoDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());

              $objAnexoRN	= new AnexoRN();
	  			  	$arrAnexoDTO = $objAnexoRN->listarRN0218($objAnexoDTO);

	  			  	$objInfraFeedDTO = new InfraFeedDTO();
	  			  	
	  			  	if (count($arrAnexoDTO) == 0){
	  			  	  
	  	  		  	$objInfraFeedDTO->setStrUrl('D'.$objProtocoloDTO->getDblIdProtocolo());
	    					$objInfraFeedDTO->setArrMetaTags($arrFormatadoFeed);
	    					$objInfraFeedDTO->setStrMimeType('text/plain');
	    					$objInfraFeedDTO->setStrCaminhoArquivo(null);
	    					$objInfraFeedDTO->setBinConteudo(null);
	    					
	    				}else {
	
	  				  	foreach ($arrAnexoDTO as $objAnexoDTO) {
									$objInfraFeedDTO->setStrUrl('D'.$objProtocoloDTO->getDblIdProtocolo().'-A'.$objAnexoDTO->getNumIdAnexo());

									$arrFormatadoFeed['id_anexo'] = $objAnexoDTO->getNumIdAnexo();

									$objInfraFeedDTO->setArrMetaTags($arrFormatadoFeed);
									$this->configurarIndexacaoAnexo($parObjIndexacaoDTO, $objInfraFeedDTO, $objAnexoDTO);
	  			  		}
	  		  	  }
	  		  		
	  		  		FeedSEIProtocolos::getInstance()->adicionarFeed($objInfraFeedDTO);	
	  	  		  break;
	  	  	}
	  		}

	  		if (count($arrIdProtocolosRemocao)){
	  		  $objIndexacaoDTO 	= new IndexacaoDTO();
	  		  $objIndexacaoDTO->setArrIdProtocolos($arrIdProtocolosRemocao);
	  		  
	  		  $objIndexacaoRN = new IndexacaoRN();
	  		  $objIndexacaoRN->prepararRemocaoProtocolo($objIndexacaoDTO);	  		  
	  		}
	  		
	  	  FeedSEIProtocolos::getInstance()->indexarFeeds();
	  	  
     	}

    }catch(Exception $e){
      LogSEI::getInstance()->gravar('Erro indexando protocolo.'."\n".InfraException::inspecionar($e));
      //throw new InfraException('Erro indexando protocolo.',$e);
    }  	
  }

  protected function indexarPublicacaoConectado(IndexacaoDTO $parObjIndexacaoDTO){

    try {

      $arrObjPublicacaoDTO = $parObjIndexacaoDTO->getArrObjPublicacaoDTO();

      if (count($arrObjPublicacaoDTO)){

        foreach($arrObjPublicacaoDTO as $objPublicacaoDTO){

          if (!$objPublicacaoDTO->isSetNumIdPublicacaoLegado()){
            $objPublicacaoDTO->setNumIdPublicacaoLegado(null);
          }

          $arrFormatadoFeed = array();

          $arrFormatadoFeed['id_pub'] = $objPublicacaoDTO->getNumIdPublicacao();
          $arrFormatadoFeed['id_pub_leg'] = $objPublicacaoDTO->getNumIdPublicacaoLegado();
          $arrFormatadoFeed['id_doc'] = $objPublicacaoDTO->getDblIdDocumento();
          $arrFormatadoFeed['id_proc'] = $objPublicacaoDTO->getDblIdProcedimentoDocumento();
          $arrFormatadoFeed['id_prot_agrup'] = $objPublicacaoDTO->getDblIdProtocoloAgrupadorProtocolo();
          $arrFormatadoFeed['id_org_resp'] = $objPublicacaoDTO->getNumIdOrgaoUnidadeResponsavelDocumento();
          $arrFormatadoFeed['id_uni_resp'] = $objPublicacaoDTO->getNumIdUnidadeResponsavelDocumento();
          $arrFormatadoFeed['id_serie'] = $objPublicacaoDTO->getNumIdSerieDocumento();
          $arrFormatadoFeed['numero'] = $objPublicacaoDTO->getStrNumeroDocumento();
          $arrFormatadoFeed['prot_proc'] 	= $objPublicacaoDTO->getStrProtocoloProcedimentoFormatado();
          $arrFormatadoFeed['prot_pesq']	= $objPublicacaoDTO->getStrProtocoloFormatadoPesquisaProtocolo();
          $arrFormatadoFeed['prot_doc'] 	= $objPublicacaoDTO->getStrProtocoloFormatadoProtocolo();

          $arrFormatadoFeed['dta_doc'] = $objPublicacaoDTO->getDtaGeracaoProtocolo();
          $arrFormatadoFeed['dta_pub'] = $objPublicacaoDTO->getDtaPublicacao();
          $arrFormatadoFeed['num_pub'] = $objPublicacaoDTO->getNumNumero();
          $arrFormatadoFeed['id_veic_pub'] = $objPublicacaoDTO->getNumIdVeiculoPublicacao();
          $arrFormatadoFeed['resumo'] = $objPublicacaoDTO->getStrResumo();
          $arrFormatadoFeed['id_veic_io'] = $objPublicacaoDTO->getNumIdVeiculoIO();
          $arrFormatadoFeed['dta_pub_io'] = $objPublicacaoDTO->getDtaPublicacaoIO();
          $arrFormatadoFeed['id_sec_io'] = $objPublicacaoDTO->getNumIdSecaoIO();
          $arrFormatadoFeed['pag_io'] = $objPublicacaoDTO->getStrPaginaIO();

          $objInfraFeedDTO = new InfraFeedDTO();

          if ($objPublicacaoDTO->getNumIdPublicacao()!=null){
            $strId = 'P'.$objPublicacaoDTO->getNumIdPublicacao();
          }else{
            $strId = 'L'.$objPublicacaoDTO->getNumIdPublicacaoLegado();
          }

          $objInfraFeedDTO->setStrUrl($strId);
          $objInfraFeedDTO->setStrMimeType('text/html');
          $objInfraFeedDTO->setArrMetaTags($arrFormatadoFeed);
          $objInfraFeedDTO->setStrCaminhoArquivo(null);
          $objInfraFeedDTO->setBinConteudo(DocumentoINT::limparHtml($objPublicacaoDTO->getStrConteudoDocumento()));

          FeedSEIPublicacoes::getInstance()->adicionarFeed($objInfraFeedDTO);
        }

        FeedSEIPublicacoes::getInstance()->indexarFeeds();

        $objPublicacaoRN = new PublicacaoRN();
        $objPublicacaoRN->gravarCamposPesquisa($arrObjPublicacaoDTO);

      }

    }catch(Exception $e){
      LogSEI::getInstance()->gravar('Erro indexando publicação.'."\n".InfraException::inspecionar($e));
      //throw new InfraException('Erro indexando publicação.',$e);
    }
  }

  protected function indexarBaseConhecimentoConectado(IndexacaoDTO $objIndexacaoDTO){
  	
  	try {
  	  
  		$arrObjBaseConhecimentoDTO = $objIndexacaoDTO->getArrObjBaseConhecimentoDTO();
  		
  		if (count($arrObjBaseConhecimentoDTO)){

		 		$objBaseConhecimentoRN = new BaseConhecimentoRN();
		 		
	  		foreach($arrObjBaseConhecimentoDTO as $parObjBaseConhecimentoDTO){
	  		
		 		  $objBaseConhecimentoDTO = new BaseConhecimentoDTO();
		 		  
			  	$objBaseConhecimentoDTO->retNumIdBaseConhecimento();
			  	$objBaseConhecimentoDTO->retStrDescricao();
			  	$objBaseConhecimentoDTO->retStrConteudo();
			  	$objBaseConhecimentoDTO->retNumIdUnidade();
					$objBaseConhecimentoDTO->retDthLiberacao();
					
			  	$objBaseConhecimentoDTO->setNumIdBaseConhecimento($parObjBaseConhecimentoDTO->getNumIdBaseConhecimento());
			  	
					$objBaseConhecimentoDTO = $objBaseConhecimentoRN->consultar($objBaseConhecimentoDTO);
			  	
		  	  $arrFormatadoFeed = array();
		  	  
		  	  $arrFormatadoFeed['id_bc'] = $objBaseConhecimentoDTO->getNumIdBaseConhecimento();
		  	  $arrFormatadoFeed['desc']	= $objBaseConhecimentoDTO->getStrDescricao();
		  	  $arrFormatadoFeed['id_uni'] = $objBaseConhecimentoDTO->getNumIdUnidade();
		  	  $arrFormatadoFeed['dta_ger'] = substr($objBaseConhecimentoDTO->getDthLiberacao(),0,10);
							  
					$objInfraFeedDTO = new InfraFeedDTO();
		  	  
		      $objInfraFeedDTO->setStrUrl('B'.$objBaseConhecimentoDTO->getNumIdBaseConhecimento());
		      $objInfraFeedDTO->setStrMimeType('text/html'); 
		  		$objInfraFeedDTO->setArrMetaTags($arrFormatadoFeed);
		  		$objInfraFeedDTO->setStrCaminhoArquivo(null);
				  $objInfraFeedDTO->setBinConteudo(DocumentoINT::limparHtml($objBaseConhecimentoDTO->getStrConteudo()));
				  
					FeedSEIBasesConhecimento::getInstance()->adicionarFeed($objInfraFeedDTO);					
		  	  			
		  		// Monta os arrays de anexos da Base de Conhecimento (Se Houver)
		    	$objAnexoDTO=	new AnexoDTO();
		    	$objAnexoRN	= new AnexoRN();
		    	$objAnexoDTO->setNumIdBaseConhecimento($objBaseConhecimentoDTO->getNumIdBaseConhecimento());
		    	$objAnexoDTO->retNumIdAnexo();
		    	$objAnexoDTO->retStrNome();
		    	$objAnexoDTO->retDthInclusao();
		    	$arrAnexoDTO = $objAnexoRN->listarRN0218($objAnexoDTO);
		  			  	
		    	if (count($arrAnexoDTO) > 0){
		    		
			    	foreach ($arrAnexoDTO as $objAnexoDTO) {
		
			    		$objInfraFeedDTO = new InfraFeedDTO();  
							$objInfraFeedDTO->setStrUrl('B'.$objBaseConhecimentoDTO->getNumIdBaseConhecimento().'-A'.$objAnexoDTO->getNumIdAnexo());

							$arrFormatadoFeed['id_anexo'] 	= $objAnexoDTO->getNumIdAnexo();
							$arrFormatadoFeed['nome_anexo'] = $objAnexoDTO->getStrNome();
							$objInfraFeedDTO->setArrMetaTags($arrFormatadoFeed);
		  		  	
							$this->configurarIndexacaoAnexo($objIndexacaoDTO, $objInfraFeedDTO, $objAnexoDTO);
		    			
		    			FeedSEIBasesConhecimento::getInstance()->adicionarFeed($objInfraFeedDTO);	
		  	  	}
		    	}
	  		}
	  	  FeedSEIBasesConhecimento::getInstance()->indexarFeeds();
  		}	  	
    }catch(Exception $e){
      LogSEI::getInstance()->gravar('Erro indexando Base de Conhecimento.'."\n".InfraException::inspecionar($e));
      //throw new InfraException('Erro indexando Base de Conhecimento.',$e);
    }  	
  }

  protected function prepararRemocaoProtocoloConectado(IndexacaoDTO $objIndexacaoDTO){
  	try {

      if (FeedSEIProtocolos::getInstance()->isBolIgnorarFeeds()){
        return;
      }

  		$arrIdProtocolos = $objIndexacaoDTO->getArrIdProtocolos();
  		
  		if (count($arrIdProtocolos)){
  			
	    	$objProtocoloDTO 	= new ProtocoloDTO();
	    	$objProtocoloDTO->retDblIdProtocolo();
	    	$objProtocoloDTO->retStrStaProtocolo();
	    	$objProtocoloDTO->setOrdDblIdProtocolo(InfraDTO::$TIPO_ORDENACAO_ASC);
	    	$objProtocoloDTO->setDblIdProtocolo($arrIdProtocolos, InfraDTO::$OPER_IN);
	    	
	    	$objProtocoloRN = new ProtocoloRN();
	    	$arrObjProtocoloDTO = $objProtocoloRN->listarRN0668($objProtocoloDTO);
	    	
	    	//pode já ter sido excluído
	    	if (count($arrObjProtocoloDTO)){
		    	
		    	foreach ($arrObjProtocoloDTO as $objProtocoloDTO){
		    		
		    		switch ($objProtocoloDTO->getStrStaProtocolo()) {
		    			case ProtocoloRN::$TP_PROCEDIMENTO:
					  		$objInfraFeedDTO = new InfraFeedDTO();  
					      $objInfraFeedDTO->setStrUrl('P'.$objProtocoloDTO->getDblIdProtocolo());
								$objInfraFeedDTO->setStrMimeType('text/plain');
								$objInfraFeedDTO->setArrMetaTags(null);
								$objInfraFeedDTO->setBinConteudo(null);
					  		FeedSEIProtocolos::getInstance()->removerFeed($objInfraFeedDTO);
		    				break;
		    				
		    			case ProtocoloRN::$TP_DOCUMENTO_GERADO:
					  		$objInfraFeedDTO = new InfraFeedDTO();  
					      $objInfraFeedDTO->setStrUrl('D'.$objProtocoloDTO->getDblIdProtocolo());
								$objInfraFeedDTO->setStrMimeType('text/plain');
								$objInfraFeedDTO->setArrMetaTags(null);
								$objInfraFeedDTO->setBinConteudo(null);
					  		FeedSEIProtocolos::getInstance()->removerFeed($objInfraFeedDTO);
		    				break;
		    				
		    			case ProtocoloRN::$TP_DOCUMENTO_RECEBIDO:
					    	$objAnexoDTO =	new AnexoDTO();
					    	$objAnexoDTO->retNumIdAnexo();
					    	$objAnexoDTO->retStrNome();
					    	$objAnexoDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
					
					    	$objAnexoRN = new AnexoRN();
					    	$arrAnexoDTO = $objAnexoRN->listarRN0218($objAnexoDTO);
					    	
					    	if (count($arrAnexoDTO)==0){
						  		$objInfraFeedDTO = new InfraFeedDTO();  
						      $objInfraFeedDTO->setStrUrl('D'.$objProtocoloDTO->getDblIdProtocolo());
									$objInfraFeedDTO->setStrMimeType('text/plain');
									$objInfraFeedDTO->setArrMetaTags(null);
									$objInfraFeedDTO->setBinConteudo(null);
						  		FeedSEIProtocolos::getInstance()->removerFeed($objInfraFeedDTO);
						  		
					    	}else{
					    	
						    	foreach ($arrAnexoDTO as $objAnexoDTO) {
							  		$objInfraFeedDTO = new InfraFeedDTO();  
							      $objInfraFeedDTO->setStrUrl('D'.$objProtocoloDTO->getDblIdProtocolo().'-A'.$objAnexoDTO->getNumIdAnexo());
										$objInfraFeedDTO->setStrMimeType(InfraUtil::getStrMimeType($objAnexoDTO->getStrNome()));
										$objInfraFeedDTO->setArrMetaTags(null);
										$objInfraFeedDTO->setBinConteudo(null);
										
							  		FeedSEIProtocolos::getInstance()->removerFeed($objInfraFeedDTO);
						    	}
					    	}
		  				 	break;
		    		}
		    	}
	    	}	    		  		
  		}  		
  	}catch(Exception $e){
  	  LogSEI::getInstance()->gravar('Erro preparando remoção do Protocolo.'."\n".InfraException::inspecionar($e));
      //throw new InfraException('Erro preparando remoção do Protocolo.',$e);
    } 
  }
  
  protected function prepararRemocaoBaseConhecimentoConectado(IndexacaoDTO $objIndexacaoDTO){
  	try {
  		
 			$arrObjBaseConhecimentoDTO = $objIndexacaoDTO->getArrObjBaseConhecimentoDTO();
 			
 			if (count($arrObjBaseConhecimentoDTO)){
 				
	  		foreach($arrObjBaseConhecimentoDTO as $objBaseConhecimentoDTO){
		  		$objInfraFeedDTO = new InfraFeedDTO();
		      $objInfraFeedDTO->setStrUrl('B'.$objBaseConhecimentoDTO->getNumIdBaseConhecimento());
					$objInfraFeedDTO->setStrMimeType('text/plain');
					$objInfraFeedDTO->setArrMetaTags(null);
					$objInfraFeedDTO->setBinConteudo(null);
		  		FeedSEIBasesConhecimento::getInstance()->removerFeed($objInfraFeedDTO);


          $objAnexoDTO =	new AnexoDTO();
          $objAnexoDTO->retNumIdAnexo();
          $objAnexoDTO->retStrNome();
          $objAnexoDTO->setNumIdBaseConhecimento($objBaseConhecimentoDTO->getNumIdBaseConhecimento());

          $objAnexoRN = new AnexoRN();
          $arrAnexoDTO = $objAnexoRN->listarRN0218($objAnexoDTO);
          foreach ($arrAnexoDTO as $objAnexoDTO) {
            $objInfraFeedDTO = new InfraFeedDTO();
            $objInfraFeedDTO->setStrUrl('B'.$objBaseConhecimentoDTO->getNumIdBaseConhecimento().'-A'.$objAnexoDTO->getNumIdAnexo());
            $objInfraFeedDTO->setStrMimeType(InfraUtil::getStrMimeType($objAnexoDTO->getStrNome()));
            $objInfraFeedDTO->setArrMetaTags(null);
            $objInfraFeedDTO->setBinConteudo(null);
            FeedSEIBasesConhecimento::getInstance()->removerFeed($objInfraFeedDTO);
          }
	  		}
 			}
  	}catch(Exception $e){
  	  LogSEI::getInstance()->gravar('Erro preparando remoção da Base de Conhecimento.'."\n".InfraException::inspecionar($e));
      //throw new InfraException('Erro preparando remoção da Base de Conhecimento.',$e);
    } 
  }
  
  protected function prepararRemocaoPublicacaoConectado(IndexacaoDTO $parObjIndexacaoDTO){
    try {
  
   			$arrObjPublicacaoDTO = $parObjIndexacaoDTO->getArrObjPublicacaoDTO();
  
   			if (count($arrObjPublicacaoDTO)){
   			  	   			     			   
   			  foreach($arrObjPublicacaoDTO as $objPublicacaoDTO){
   			    $objInfraFeedDTO = new InfraFeedDTO();   			    
   			    $objInfraFeedDTO->setStrUrl('P'.$objPublicacaoDTO->getNumIdPublicacao());
   			    $objInfraFeedDTO->setStrMimeType('text/html');
   			    $objInfraFeedDTO->setArrMetaTags(null);
   			    $objInfraFeedDTO->setBinConteudo(null);
   			    FeedSEIPublicacoes::getInstance()->removerFeed($objInfraFeedDTO);
   			  }
   			}
   			
    }catch(Exception $e){
      LogSEI::getInstance()->gravar('Erro preparando remoção da Base de Conhecimento.'."\n".InfraException::inspecionar($e));
      //throw new InfraException('Erro preparando remoção da Base de Conhecimento.',$e);
    }
  }

  private function configurarIndexacaoAnexo(IndexacaoDTO $parObjIndexacaoDTO, InfraFeedDTO $objInfraFeedDTO, AnexoDTO $objAnexoDTO){
    
    $objInfraFeedDTO->setStrMimeType('text/plain');
    $objInfraFeedDTO->setStrCaminhoArquivo(null);
    $objInfraFeedDTO->setBinConteudo(null);
    	
    $strMimeType = InfraUtil::getStrMimeType($objAnexoDTO->getStrNome());
    
    $strTipo = substr($strMimeType,0,6);
    
    if ($strTipo!='video/' &&  $strTipo!='audio/' &&  $strTipo!='image/' && $strMimeType!='application/zip' && $strMimeType!='application/rar'){
      
      $objInfraFeedDTO->setStrMimeType($strMimeType);

      $strStaOperacao = $parObjIndexacaoDTO->getStrStaOperacao();

      if ($strStaOperacao==IndexacaoRN::$TO_PROTOCOLO_METADADOS_E_CONTEUDO ||
          $strStaOperacao==IndexacaoRN::$TO_PROCESSO_COM_DOCUMENTOS_METADADOS_E_CONTEUDO ||
          $strStaOperacao==IndexacaoRN::$TO_BASE_CONHECIMENTO_LIBERAR){
        	
				$objAnexoRN = new AnexoRN();
				$objInfraFeedDTO->setStrCaminhoArquivo($objAnexoRN->obterLocalizacao($objAnexoDTO));
      }
    }
  }

  protected function gerarIndexacaoCompletaConectado(IndexacaoDTO $parObjIndexacaoDTO){
    try{

      $objInfraException = new InfraException();

      ini_set('max_execution_time','0');
      ini_set('memory_limit','2048M');

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb = InfraBarraProgresso2::newInstance('IndexacaoCompleta', array('cor_fundo'=>'#5c9ccc','cor_borda'=>'#4297d7'));
      }

      if (!InfraData::validarData($parObjIndexacaoDTO->getDtaIndexacao())){
        $objInfraException->lancarValidacao('Data ['.$parObjIndexacaoDTO->getDtaIndexacao().'] inválida.');
      }

      $strMsg = 'Indexação Completa - iniciando...';

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb->setStrRotulo($strMsg);
      }

      $this->logar($strMsg);

      $numSeg = InfraUtil::verificarTempoProcessamento();

      $objProtocoloRN 	= new ProtocoloRN();

      $objProtocoloDTO 	= new ProtocoloDTO();
      $objProtocoloDTO->setDistinct(true);
      $objProtocoloDTO->retDtaGeracao();

      if (!InfraString::isBolVazia($parObjIndexacaoDTO->getDtaIndexacao())){
        $objProtocoloDTO->setDtaGeracao($parObjIndexacaoDTO->getDtaIndexacao(),InfraDTO::$OPER_MENOR_IGUAL);
      }

      $objProtocoloDTO->setOrdDtaGeracao(InfraDTO::$TIPO_ORDENACAO_DESC);

      $arrObjProtocoloDTOData = $objProtocoloRN->listarRN0668($objProtocoloDTO);

      $objIndexacaoRN = new IndexacaoRN();
      $objIndexacaoDTO = new IndexacaoDTO();

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb->setNumMin(0);
        $prb->setNumMax(count($arrObjProtocoloDTOData));
      }

      $numTotalRegistros = 0;
      foreach($arrObjProtocoloDTOData as $objProtocoloDTOData){

        $dtaGeracao = $objProtocoloDTOData->getDtaGeracao();

        if (!InfraUtil::isBolLinhaDeComando()) {
          $prb->setStrRotulo('Indexação Completa - '.$dtaGeracao.'...');
          $prb->moverProximo();
        }

        $objProtocoloDTO = new ProtocoloDTO();
        $objProtocoloDTO->retDblIdProtocolo();
        $objProtocoloDTO->setDtaGeracao($dtaGeracao);
        $objProtocoloDTO->setOrdDblIdProtocolo(InfraDTO::$TIPO_ORDENACAO_DESC);
        $arrObjProtocoloDTO = $objProtocoloRN->listarRN0668($objProtocoloDTO);

        $numRegistros 			=	count($arrObjProtocoloDTO);
        $numRegistrosPagina = 50;
        $numPaginas 				= ceil($numRegistros/$numRegistrosPagina);

        for ($numPaginaAtual = 0; $numPaginaAtual < $numPaginas; $numPaginaAtual++) {

          if ($numPaginaAtual == ($numPaginas - 1)) {
            $numRegistrosAtual = $numRegistros;
          } else {
            $numRegistrosAtual = ($numPaginaAtual + 1) * $numRegistrosPagina;
          }

          $strMsg = 'Indexação Completa - '.$dtaGeracao . ' [' . $numRegistrosAtual . ' de ' . $numRegistros . ']...';

          if (!InfraUtil::isBolLinhaDeComando()) {
            $prb->setStrRotulo($strMsg);
          }

          $this->logar($strMsg);


          $arrObjProtocoloDTOPagina = array_slice($arrObjProtocoloDTO, ($numPaginaAtual * $numRegistrosPagina), $numRegistrosPagina);

          $objIndexacaoDTO->setArrIdProtocolos(InfraArray::converterArrInfraDTO($arrObjProtocoloDTOPagina,'IdProtocolo'));
          $objIndexacaoDTO->setStrStaOperacao(IndexacaoRN::$TO_PROTOCOLO_METADADOS_E_CONTEUDO);

          $objIndexacaoRN->indexarProtocolo($objIndexacaoDTO);
        }
        $numTotalRegistros += $numRegistros;
      }

      $numSegProtocolos = InfraUtil::verificarTempoProcessamento($numSeg);

      $strMsg = 'Indexação Completa - '.$numTotalRegistros.' protocolos indexados em '.InfraData::formatarTimestamp($numSegProtocolos);

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb->setStrRotulo($strMsg);
      }

      $this->logar($strMsg);

      sleep(2);

      $objInfraException->lancarValidacao('Operação Finalizada.');

    }catch(Exception $e){
      throw new InfraException('Erro executando indexação completa de processos/documentos.',$e);
    }
  }

  protected function gerarIndexacaoParcialConectado(IndexacaoDTO $parObjIndexacaoDTO){
    try{

      $objInfraException = new InfraException();

      ini_set('max_execution_time','0');
      ini_set('memory_limit','2048M');

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb = InfraBarraProgresso2::newInstance('IndexacaoParcial', array('cor_fundo'=>'#5c9ccc','cor_borda'=>'#4297d7'));
      }

      if (InfraString::isBolVazia($parObjIndexacaoDTO->getDthInicio())){
        $objInfraException->lancarValidacao('Data/hora inicial não informada.');
      }

      if (InfraString::isBolVazia($parObjIndexacaoDTO->getDthFim())){
        $objInfraException->lancarValidacao('Data/hora final não informada.');
      }

      if (!InfraData::validarDataHora($parObjIndexacaoDTO->getDthInicio().':00')) {
        $objInfraException->lancarValidacao("Data/Hora inicial [" . $parObjIndexacaoDTO->getDthInicio() . "] inválida.\n");
      }

      if (!InfraData::validarDataHora($parObjIndexacaoDTO->getDthFim().':59')) {
        $objInfraException->lancarValidacao("Data/Hora final [" . $parObjIndexacaoDTO->getDthFim() . "] inválida.\n");
      }

      if (InfraData::compararDataHora($parObjIndexacaoDTO->getDthInicio().':00',$parObjIndexacaoDTO->getDthFim().':59')<0){
        $objInfraException->lancarValidacao("Período inválido.");
      }

      $strMsg = 'Indexação Parcial - iniciando...['.$parObjIndexacaoDTO->getDthInicio().' ate '.$parObjIndexacaoDTO->getDthFim().']';

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb->setStrRotulo($strMsg);
      }

      $this->logar($strMsg);

      $objIndexacaoRN = new IndexacaoRN();
      $objIndexacaoDTO = new IndexacaoDTO();

      $numSeg = InfraUtil::verificarTempoProcessamento();

      $dthIni = $parObjIndexacaoDTO->getDthInicio().':00';
      $dthFim = $parObjIndexacaoDTO->getDthFim().':59';

      $objAtividadeDTO = new AtividadeDTO();
      $objAtividadeDTO->setDistinct(true);
      $objAtividadeDTO->retDblIdProtocolo();

      $objAtividadeDTO->adicionarCriterio(array('Abertura','Abertura'),
          array(InfraDTO::$OPER_MAIOR_IGUAL,InfraDTO::$OPER_MENOR_IGUAL),
          array($dthIni,$dthFim),
          InfraDTO::$OPER_LOGICO_AND,
          'criterioAbertura');

      $objAtividadeDTO->adicionarCriterio(array('Conclusao','Conclusao'),
          array(InfraDTO::$OPER_MAIOR_IGUAL,InfraDTO::$OPER_MENOR_IGUAL),
          array($dthIni,$dthFim),
          InfraDTO::$OPER_LOGICO_AND,
          'criterioConclusao');

      $objAtividadeDTO->agruparCriterios(array('criterioAbertura','criterioConclusao'),
          InfraDTO::$OPER_LOGICO_OR);

      //Erro no SQL Server que não aceita ordenar pelo campo se utilizando distinct com cast as varchar e alias
      //$objAtividadeDTO->setOrdDblIdProtocolo(InfraDTO::$TIPO_ORDENACAO_DESC);

      $objAtividadeRN 	= new AtividadeRN();
      $arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);

      InfraArray::ordenarArrInfraDTO($arrObjAtividadeDTO, 'IdProtocolo', InfraArray::$TIPO_ORDENACAO_DESC);

      $numRegistros 			=	count($arrObjAtividadeDTO);
      $numRegistrosPagina = 10;
      $numPaginas 				= ceil($numRegistros/$numRegistrosPagina);

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb->setNumMin(0);
        $prb->setNumMax($numPaginas);
      }

      for ($numPaginaAtual = 0; $numPaginaAtual < $numPaginas; $numPaginaAtual++){

        if ($numPaginaAtual ==  ($numPaginas-1)){
          $numRegistrosAtual = $numRegistros;
        }else{
          $numRegistrosAtual = ($numPaginaAtual+1)*$numRegistrosPagina;
        }


        $strMsg = 'Indexação Parcial - indexando processos ['.$numRegistrosAtual.' de '.$numRegistros.']...';

        if (!InfraUtil::isBolLinhaDeComando()) {
          $prb->setStrRotulo($strMsg);
          $prb->moverProximo();
        }

        $this->logar($strMsg);


        $arrObjAtividadeDTOPagina = array_slice($arrObjAtividadeDTO, ($numPaginaAtual*$numRegistrosPagina), $numRegistrosPagina);

        $objIndexacaoDTO->setArrIdProtocolos(InfraArray::converterArrInfraDTO($arrObjAtividadeDTOPagina,'IdProtocolo'));
        $objIndexacaoDTO->setStrStaOperacao(IndexacaoRN::$TO_PROCESSO_COM_DOCUMENTOS_METADADOS_E_CONTEUDO);

        $objIndexacaoRN->indexarProtocolo($objIndexacaoDTO);
      }

      $strMsg = 'Indexação Parcial - '.$numRegistros.' processos indexados em '.InfraData::formatarTimestamp(InfraUtil::verificarTempoProcessamento($numSeg));

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb->setStrRotulo($strMsg);
      }

      $this->logar($strMsg);

      sleep(2);

      unset($arrObjAtividadeDTO);

      $numSeg = InfraUtil::verificarTempoProcessamento();

      SessaoInfra::setObjInfraSessao(SessaoSEI::getInstance());
      BancoInfra::setObjInfraIBanco(BancoSEI::getInstance());

      $objInfraAuditoriaDTO = new InfraAuditoriaDTO();
      $objInfraAuditoriaDTO->retStrRequisicao();
      $objInfraAuditoriaDTO->retStrRecurso();
      $objInfraAuditoriaDTO->setStrRecurso(array('procedimento_excluir','documento_excluir'),InfraDTO::$OPER_IN);

      $objInfraAuditoriaDTO->adicionarCriterio(array('Acesso','Acesso'),
          array(InfraDTO::$OPER_MAIOR_IGUAL,InfraDTO::$OPER_MENOR_IGUAL),
          array($dthIni,$dthFim),
          InfraDTO::$OPER_LOGICO_AND);

      $objInfraAuditoriaRN = new InfraAuditoriaRN();
      $arrObjInfraAuditoriaDTO = $objInfraAuditoriaRN->listar($objInfraAuditoriaDTO);

      $numRegistros 			=	count($arrObjInfraAuditoriaDTO);
      $numRegistrosPagina = 10;
      $numPaginas 				= ceil($numRegistros/$numRegistrosPagina);

      $strMsg = 'Indexação Parcial - removendo indexação de protocolos excluídos...';

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb = InfraBarraProgresso2::newInstance('RemocaoParcial', array('cor_fundo'=>'#5c9ccc','cor_borda'=>'#4297d7'));
        $prb->setStrRotulo($strMsg);
        $prb->setNumMin(0);
        $prb->setNumMax($numPaginas);
      }

      $this->logar($strMsg);

      for ($numPaginaAtual = 0; $numPaginaAtual < $numPaginas; $numPaginaAtual++){

        if ($numPaginaAtual ==  ($numPaginas-1)){
          $numRegistrosAtual = $numRegistros;
        }else{
          $numRegistrosAtual = ($numPaginaAtual+1)*$numRegistrosPagina;
        }

        $strMsg = 'Indexação Parcial - removendo indexação ['.$numRegistrosAtual.' de '.$numRegistros.']...';

        if (!InfraUtil::isBolLinhaDeComando()) {
          $prb->setStrRotulo($strMsg);
          $prb->moverProximo();
        }

        $this->logar($strMsg);

        $arrObjInfraAuditoriaDTOPagina = array_slice($arrObjInfraAuditoriaDTO, ($numPaginaAtual*$numRegistrosPagina), $numRegistrosPagina);

        $strPesquisa = '';
        foreach($arrObjInfraAuditoriaDTOPagina as $objInfraAuditoriaDTO){
          $strRequisicao = $objInfraAuditoriaDTO->getStrRequisicao();

          if ($objInfraAuditoriaDTO->getStrRecurso()=='procedimento_excluir'){
            $strChave = '[id_procedimento] => ';
            $strPrefixo = 'P';
          }else{
            $strChave = '[id_documento] => ';
            $strPrefixo = 'D';
          }

          $posIni = strpos($strRequisicao,$strChave);
          if ($posIni !== 0) {
            $posFim = strpos($strRequisicao, "\n", $posIni);
            if ($posFim !== 0) {
              $posIni += strlen($strChave);
              $dblIdProtocoloExcluido = substr($strRequisicao, $posIni, $posFim - $posIni);

              if ($strPesquisa!=''){
                $strPesquisa .= '%20OR%20';
              }

              $strPesquisa .= 'id:'.$strPrefixo.$dblIdProtocoloExcluido . '*';
            }
          }
        }

        if ($strPesquisa!='') {

          $urlBusca = ConfiguracaoSEI::getInstance()->getValor('Solr', 'Servidor').'/'.ConfiguracaoSEI::getInstance()->getValor('Solr', 'CoreProtocolos').'/select?q=(' . $strPesquisa . ')&fl=id&rows=1000';

          $resultados = file_get_contents($urlBusca);

          if ($resultados != '') {

            $xml = simplexml_load_string($resultados);
            $arrRet = $xml->xpath('/response/result/@numFound');

            $itens = array_shift($arrRet);

            if ($itens > 0) {

              $arrRegistros = $xml->xpath('/response/result/doc');

              foreach ($arrRegistros as $registro) {
                $objInfraFeedDTO = new InfraFeedDTO();
                $objInfraFeedDTO->setStrUrl(SolrUtil::obterTag($registro, 'id', 'str'));
                $objInfraFeedDTO->setStrMimeType('text/plain');
                $objInfraFeedDTO->setArrMetaTags(null);
                $objInfraFeedDTO->setBinConteudo(null);
                FeedSEIProtocolos::getInstance()->removerFeed($objInfraFeedDTO);
              }

              FeedSEIProtocolos::getInstance()->indexarFeeds();
            }
          }
        }
      }

      $strMsg = 'Indexação Parcial - '.$numRegistros.' protocolos removidos da indexação em '.InfraData::formatarTimestamp(InfraUtil::verificarTempoProcessamento($numSeg));

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb->setStrRotulo($strMsg);
      }

      $this->logar($strMsg);

      sleep(2);

      $objVeiculoPublicacaoDTO = new VeiculoPublicacaoDTO();
      $objVeiculoPublicacaoDTO->retNumIdVeiculoPublicacao();
      $objVeiculoPublicacaoDTO->adicionarCriterio(array('StaTipo','SinExibirPesquisaInterna'),
          array(InfraDTO::$OPER_IGUAL,InfraDTO::$OPER_IGUAL),
          array(VeiculoPublicacaoRN::$TV_INTERNO,'S'),
          InfraDTO::$OPER_LOGICO_OR);

      $objVeiculoPublicacaoRN = new VeiculoPublicacaoRN();
      $arrObjVeiculoPublicacaoDTO = $objVeiculoPublicacaoRN->listar($objVeiculoPublicacaoDTO);

      if (count($arrObjVeiculoPublicacaoDTO)) {

        $objPublicacaoDTO = new PublicacaoDTO();
        $objPublicacaoDTO->retNumIdPublicacao();
        $objPublicacaoDTO->retStrStaEstado();
        $objPublicacaoDTO->setNumIdVeiculoPublicacao(InfraArray::converterArrInfraDTO($arrObjVeiculoPublicacaoDTO, 'IdVeiculoPublicacao'), InfraDTO::$OPER_IN);
        $objPublicacaoDTO->adicionarCriterio(array('Publicacao','Publicacao'),
                                             array(InfraDTO::$OPER_MAIOR_IGUAL,InfraDTO::$OPER_MENOR_IGUAL),
                                             array(substr($dthIni,0,10),substr($dthFim,0,10)),
                                             InfraDTO::$OPER_LOGICO_AND);
        $objPublicacaoDTO->setOrdDtaPublicacao(InfraDTO::$TIPO_ORDENACAO_DESC);

        $objPublicacaoRN = new PublicacaoRN();
        $arrObjPublicacaoDTOTodas = $objPublicacaoRN->listarRN1045($objPublicacaoDTO);

        $arrObjPublicacaoDTO = array();
        foreach ($arrObjPublicacaoDTOTodas as $objPublicacaoDTO) {
          if ($objPublicacaoDTO->getStrStaEstado() == PublicacaoRN::$TE_PUBLICADO) {
            $arrObjPublicacaoDTO[] = $objPublicacaoDTO;
          }
        }
        $numRegistros = count($arrObjPublicacaoDTO);
        $numRegistrosPagina = 50;
        $numPaginas = ceil($numRegistros / $numRegistrosPagina);


        $strMsg = 'Indexação Parcial - indexando publicações SEI...';

        if (!InfraUtil::isBolLinhaDeComando()) {
          $prb = InfraBarraProgresso2::newInstance('PublicacaoParcial', array('cor_fundo'=>'#5c9ccc','cor_borda'=>'#4297d7'));
          $prb->setStrRotulo($strMsg);
          $prb->setNumMin(0);
          $prb->setNumMax($numPaginas);
        }

        $objIndexacaoDTO = new IndexacaoDTO();

        for ($numPaginaAtual = 0; $numPaginaAtual < $numPaginas; $numPaginaAtual++) {

          if ($numPaginaAtual == ($numPaginas - 1)) {
            $numRegistrosAtual = $numRegistros;
          } else {
            $numRegistrosAtual = ($numPaginaAtual + 1) * $numRegistrosPagina;
          }

          $strMsg = 'Indexação Parcial - indexando publicações SEI ['.$numRegistrosAtual.' de '.$numRegistros.']...';

          if (!InfraUtil::isBolLinhaDeComando()) {
            $prb->setStrRotulo($strMsg);
            $prb->moverProximo();
          }

          $this->logar($strMsg);

          $arrObjPublicacaoDTOPagina = array_slice($arrObjPublicacaoDTO, ($numPaginaAtual * $numRegistrosPagina), $numRegistrosPagina);
          $objPublicacaoDTO = new PublicacaoDTO();
          $objPublicacaoDTO->retTodos(true);
          $objPublicacaoDTO->setNumIdPublicacao(InfraArray::converterArrInfraDTO($arrObjPublicacaoDTOPagina, 'IdPublicacao'), InfraDTO::$OPER_IN);
          $objIndexacaoDTO->setArrObjPublicacaoDTO($objPublicacaoRN->listarRN1045($objPublicacaoDTO));
          $objIndexacaoDTO->setStrStaOperacao(IndexacaoRN::$TO_PUBLICACAO);

          $objIndexacaoRN->indexarPublicacao($objIndexacaoDTO);
        }

        $numSegSei = InfraUtil::verificarTempoProcessamento($numSeg);

        $strMsg = 'Indexação de Publicações - ' . $numRegistros . ' publicações SEI indexadas em ' . InfraData::formatarTimestamp($numSegSei);

        if (!InfraUtil::isBolLinhaDeComando()) {
          $prb->setStrRotulo($strMsg);
        }

        $this->logar($strMsg);
      }


      sleep(2);

      $objInfraException->lancarValidacao('Operação Finalizada.');

    }catch(Exception $e){
      throw new InfraException('Erro executando indexação parcial.',$e);
    }
  }

  protected function gerarIndexacaoProcessoConectado(IndexacaoDTO $parObjIndexacaoDTO){
    try{

      $objInfraException = new InfraException();

      ini_set('max_execution_time','0');
      ini_set('memory_limit','2048M');

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb = InfraBarraProgresso2::newInstance('IndexacaoProcesso', array('cor_fundo'=>'#5c9ccc','cor_borda'=>'#4297d7'));
      }

      if (InfraString::isBolVazia($parObjIndexacaoDTO->getStrProtocoloFormatadoPesquisa())){
        $objInfraException->lancarValidacao('Processo não informado.');
      }

      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->retDblIdProtocolo();
      $objProtocoloDTO->retStrProtocoloFormatado();
      $objProtocoloDTO->setStrProtocoloFormatadoPesquisa(InfraUtil::retirarFormatacao(trim($parObjIndexacaoDTO->getStrProtocoloFormatadoPesquisa()),false));
      $objProtocoloDTO->setStrStaNivelAcessoGlobal(ProtocoloRN::$NA_SIGILOSO,InfraDTO::$OPER_DIFERENTE);
      $objProtocoloDTO->setStrStaProtocolo(ProtocoloRN::$TP_PROCEDIMENTO);

      $objProtocoloRN = new ProtocoloRN();
      $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

      if ($objProtocoloDTO==null){
        $objInfraException->lancarValidacao('Processo não encontrado.');
      }

      $dblIdProcedimento = $objProtocoloDTO->getDblIdProtocolo();
      $strProtocoloFormatado = $objProtocoloDTO->getStrProtocoloFormatado();

      $strMsg = 'Indexação Processo '.$strProtocoloFormatado.' - iniciando...';

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb->setStrRotulo($strMsg);
      }

      $this->logar($strMsg);

      $numSeg = InfraUtil::verificarTempoProcessamento();

      $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
      $objRelProtocoloProtocoloDTO->retDblIdProtocolo1();
      $objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);
      $objRelProtocoloProtocoloDTO->setDblIdProtocolo2($dblIdProcedimento);

      $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
      $objRelProtocoloProtocoloDTO = $objRelProtocoloProtocoloRN->consultarRN0841($objRelProtocoloProtocoloDTO);

      if ($objRelProtocoloProtocoloDTO!=null){
        $dblIdProcessoPai = $objRelProtocoloProtocoloDTO->getDblIdProtocolo1();
      }else{
        $dblIdProcessoPai = $dblIdProcedimento;
      }

      $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
      $objRelProtocoloProtocoloDTO->retDblIdProtocolo2();
      $objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);
      $objRelProtocoloProtocoloDTO->setDblIdProtocolo1($dblIdProcessoPai);

      $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
      $arrIdProtocolos = InfraArray::converterArrInfraDTO($objRelProtocoloProtocoloRN->listarRN0187($objRelProtocoloProtocoloDTO), 'IdProtocolo2');
      $arrIdProtocolos[] = $dblIdProcessoPai;

      $objDocumentoDTO = new DocumentoDTO();
      $objDocumentoDTO->retDblIdDocumento();
      $objDocumentoDTO->setDblIdProcedimento($arrIdProtocolos,InfraDTO::$OPER_IN);

      $objDocumentoRN = new DocumentoRN();
      $arrIdProtocolos = array_merge($arrIdProtocolos, InfraArray::converterArrInfraDTO($objDocumentoRN->listarRN0008($objDocumentoDTO),'IdDocumento'));

      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->retDblIdProtocolo();
      $objProtocoloDTO->retStrProtocoloFormatado();
      $objProtocoloDTO->retStrProtocoloFormatadoProcedimentoDocumento();
      $objProtocoloDTO->retDblIdProcedimentoDocumento();
      $objProtocoloDTO->retStrStaProtocolo();
      $objProtocoloDTO->setDblIdProtocolo($arrIdProtocolos,InfraDTO::$OPER_IN);
      $objProtocoloDTO->setOrdDblIdProcedimentoDocumento(InfraDTO::$TIPO_ORDENACAO_ASC);
      $objProtocoloDTO->setOrdDblIdProtocolo(InfraDTO::$TIPO_ORDENACAO_ASC);

      $arrObjProtocoloDTO = $objProtocoloRN->listarRN0668($objProtocoloDTO);

      $objIndexacaoRN = new IndexacaoRN();
      $objIndexacaoDTO = new IndexacaoDTO();

      $numRegistros	=	count($arrObjProtocoloDTO);

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb->setNumMin(0);
        $prb->setNumMax($numRegistros);
      }

      for ($i = 0; $i < $numRegistros; $i++) {

        $objProtocoloDTO = $arrObjProtocoloDTO[$i];

        $strMsg = 'Indexação Processo '.$strProtocoloFormatado.' - ';

        if ($objProtocoloDTO->getStrStaProtocolo() == ProtocoloRN::$TP_PROCEDIMENTO) {
          $strMsg .= $objProtocoloDTO->getStrProtocoloFormatado();
        }else{

          if ($objProtocoloDTO->getDblIdProcedimentoDocumento()==$dblIdProcedimento){
            $strMsg .= $objProtocoloDTO->getStrProtocoloFormatado();
          }else{
            $strMsg .= $objProtocoloDTO->getStrProtocoloFormatadoProcedimentoDocumento().' '.$objProtocoloDTO->getStrProtocoloFormatado();
          }
        }

        $strMsg .= ' [' . ($i+1) . ' de ' . $numRegistros . ']...';

        if (!InfraUtil::isBolLinhaDeComando()) {
          $prb->setStrRotulo($strMsg);
          $prb->moverProximo();
        }

        $this->logar($strMsg);

        $objIndexacaoDTO->setArrIdProtocolos(array($objProtocoloDTO->getDblIdProtocolo()));
        $objIndexacaoDTO->setStrStaOperacao(IndexacaoRN::$TO_PROTOCOLO_METADADOS_E_CONTEUDO);
        $objIndexacaoRN->indexarProtocolo($objIndexacaoDTO);
      }


      $numSegProtocolos = InfraUtil::verificarTempoProcessamento($numSeg);

      $strMsg = 'Indexação Processo '.$strProtocoloFormatado.' - '.$numRegistros.' protocolos indexados em '.InfraData::formatarTimestamp($numSegProtocolos);

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb->setStrRotulo($strMsg);
      }

      $this->logar($strMsg);

      sleep(2);

      $objInfraException->lancarValidacao('Operação Finalizada.');

    }catch(Exception $e){
      throw new InfraException('Erro executando indexação de processo.',$e);
    }
  }

  protected function gerarIndexacaoPublicacaoConectado(){
    try{

      $objInfraException = new InfraException();

      ini_set('max_execution_time','0');
      ini_set('memory_limit','2048M');

      $strMsg = 'Indexação de Publicações - iniciando...';

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb = InfraBarraProgresso2::newInstance('IndexacaoPublicacaoSei', array('cor_fundo'=>'#5c9ccc','cor_borda'=>'#4297d7'));
        $prb->setStrRotulo($strMsg);
      }

      $this->logar($strMsg);

      $numSeg = InfraUtil::verificarTempoProcessamento();

      $objUnidadePublicacaoDTO = new UnidadePublicacaoDTO();
      $objUnidadePublicacaoDTO->retNumIdUnidadePublicacao();
      $objUnidadePublicacaoRN = new UnidadePublicacaoRN();
      $objUnidadePublicacaoRN->excluir($objUnidadePublicacaoRN->listar($objUnidadePublicacaoDTO));

      $objSeriePublicacaoDTO = new SeriePublicacaoDTO();
      $objSeriePublicacaoDTO->retNumIdSeriePublicacao();
      $objSeriePublicacaoRN = new SeriePublicacaoRN();
      $objSeriePublicacaoRN->excluir($objSeriePublicacaoRN->listar($objSeriePublicacaoDTO));

      $objVeiculoPublicacaoDTO = new VeiculoPublicacaoDTO();
      $objVeiculoPublicacaoDTO->retNumIdVeiculoPublicacao();
      $objVeiculoPublicacaoDTO->adicionarCriterio(array('StaTipo','SinExibirPesquisaInterna'),
          array(InfraDTO::$OPER_IGUAL,InfraDTO::$OPER_IGUAL),
          array(VeiculoPublicacaoRN::$TV_INTERNO,'S'),
          InfraDTO::$OPER_LOGICO_OR);

      $objVeiculoPublicacaoRN = new VeiculoPublicacaoRN();
      $arrObjVeiculoPublicacaoDTO = $objVeiculoPublicacaoRN->listar($objVeiculoPublicacaoDTO);

      if (count($arrObjVeiculoPublicacaoDTO)==0){
        $objInfraException = new InfraException();
        $objInfraException->lancarValidacao('Nenhum veículo de publicação configurado para indexação.');
      }

      $objPublicacaoDTO = new PublicacaoDTO();
      $objPublicacaoDTO->retNumIdPublicacao();
      $objPublicacaoDTO->retStrStaEstado();
      $objPublicacaoDTO->setNumIdVeiculoPublicacao(InfraArray::converterArrInfraDTO($arrObjVeiculoPublicacaoDTO,'IdVeiculoPublicacao'), InfraDTO::$OPER_IN);
      $objPublicacaoDTO->setOrdDtaPublicacao(InfraDTO::$TIPO_ORDENACAO_DESC);

      $objPublicacaoRN = new PublicacaoRN();
      $arrObjPublicacaoDTOTodas = $objPublicacaoRN->listarRN1045($objPublicacaoDTO);

      $arrObjPublicacaoDTO = array();
      foreach($arrObjPublicacaoDTOTodas as $objPublicacaoDTO){
        if ($objPublicacaoDTO->getStrStaEstado()==PublicacaoRN::$TE_PUBLICADO){
          $arrObjPublicacaoDTO[] = $objPublicacaoDTO;
        }
      }
      $numRegistros 			=	count($arrObjPublicacaoDTO);
      $numRegistrosPagina = 50;
      $numPaginas 				= ceil($numRegistros/$numRegistrosPagina);

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb->setNumMin(0);
        $prb->setNumMax($numPaginas);
      }

      $objIndexacaoRN = new IndexacaoRN();
      $objIndexacaoDTO = new IndexacaoDTO();

      for ($numPaginaAtual = 0; $numPaginaAtual < $numPaginas; $numPaginaAtual++){

        if ($numPaginaAtual ==  ($numPaginas-1)){
          $numRegistrosAtual = $numRegistros;
        }else{
          $numRegistrosAtual = ($numPaginaAtual+1)*$numRegistrosPagina;
        }

        $strMsg = 'Indexação de Publicações - SEI ['. $numRegistrosAtual.' de '.$numRegistros.']...';

        if (!InfraUtil::isBolLinhaDeComando()) {
          $prb->setStrRotulo($strMsg);
          $prb->moverProximo();
        }

        $this->logar($strMsg);

        $arrObjPublicacaoDTOPagina = array_slice($arrObjPublicacaoDTO, ($numPaginaAtual*$numRegistrosPagina), $numRegistrosPagina);
        $objPublicacaoDTO = new PublicacaoDTO();
        $objPublicacaoDTO->retTodos(true);
        $objPublicacaoDTO->setNumIdPublicacao(InfraArray::converterArrInfraDTO($arrObjPublicacaoDTOPagina,'IdPublicacao'),InfraDTO::$OPER_IN);
        $objIndexacaoDTO->setArrObjPublicacaoDTO($objPublicacaoRN->listarRN1045($objPublicacaoDTO));
        $objIndexacaoDTO->setStrStaOperacao(IndexacaoRN::$TO_PUBLICACAO);

        $objIndexacaoRN->indexarPublicacao($objIndexacaoDTO);
      }

      $numSegSei = InfraUtil::verificarTempoProcessamento($numSeg);
      $numSeg = InfraUtil::verificarTempoProcessamento();

      $strMsg = 'Indexação de Publicações - '.$numRegistros.' publicações SEI indexadas em '.InfraData::formatarTimestamp($numSegSei);

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb->setStrRotulo($strMsg);
      }

      $this->logar($strMsg);

      $objPublicacaoLegadoRN = new PublicacaoLegadoRN();
      $objPublicacaoLegadoDTO = new PublicacaoLegadoDTO();
      $objPublicacaoLegadoDTO->retNumIdPublicacaoLegado();
      $objPublicacaoLegadoDTO->setNumIdVeiculoPublicacao(InfraArray::converterArrInfraDTO($arrObjVeiculoPublicacaoDTO,'IdVeiculoPublicacao'), InfraDTO::$OPER_IN);
      $objPublicacaoLegadoDTO->setOrdDtaPublicacao(InfraDTO::$TIPO_ORDENACAO_DESC);

      $arrObjPublicacaoLegadoDTO = $objPublicacaoLegadoRN->listar($objPublicacaoLegadoDTO);

      if (count($arrObjPublicacaoLegadoDTO)){
        $numRegistros 			=	count($arrObjPublicacaoLegadoDTO);
        $numRegistrosPagina = 50;
        $numPaginas 				= ceil($numRegistros/$numRegistrosPagina);

        $objIndexacaoRN = new IndexacaoRN();
        $objIndexacaoDTO = new IndexacaoDTO();

        if (!InfraUtil::isBolLinhaDeComando()) {
          $prb = InfraBarraProgresso2::newInstance('IndexacaoPublicacaoLegado', array('cor_fundo'=>'#5c9ccc','cor_borda'=>'#4297d7'));
          $prb->setNumMin(0);
          $prb->setNumMax($numPaginas);
        }

        for ($numPaginaAtual = 0; $numPaginaAtual < $numPaginas; $numPaginaAtual++){
          if ($numPaginaAtual ==  ($numPaginas-1)){
            $numRegistrosAtual = $numRegistros;
          }else{
            $numRegistrosAtual = ($numPaginaAtual+1)*$numRegistrosPagina;
          }

          $strMsg = 'Indexação de Publicações - legado ['.$numRegistrosAtual.' de '.$numRegistros.']...';

          if (!InfraUtil::isBolLinhaDeComando()) {
            $prb->setStrRotulo($strMsg);
            $prb->moverProximo();
          }

          $this->logar($strMsg);

          $arrObjPublicacaoLegadoDTOPagina = array_slice($arrObjPublicacaoLegadoDTO, ($numPaginaAtual*$numRegistrosPagina), $numRegistrosPagina);

          $objPublicacaoLegadoDTO = new PublicacaoLegadoDTO();
          $objPublicacaoLegadoDTO->retTodos(true);
          $objPublicacaoLegadoDTO->setNumIdPublicacaoLegado(InfraArray::converterArrInfraDTO($arrObjPublicacaoLegadoDTOPagina,'IdPublicacaoLegado'),InfraDTO::$OPER_IN);
          $arrObjPublicacaoLegadoDTO_Temp = $objPublicacaoLegadoRN->listar($objPublicacaoLegadoDTO);

          $arrObjPublicacaoDTO_Indexar = array();
          foreach($arrObjPublicacaoLegadoDTO_Temp as $objPublicacaoLegadoDTO){
            $objPublicacaoDTO = new PublicacaoDTO();

            $objPublicacaoDTO->setNumIdPublicacao(null);
            $objPublicacaoDTO->setNumIdPublicacaoLegado($objPublicacaoLegadoDTO->getNumIdPublicacaoLegado());
            $objPublicacaoDTO->setDblIdDocumento($objPublicacaoLegadoDTO->getStrIdDocumento());
            $objPublicacaoDTO->setDblIdProtocoloAgrupadorProtocolo($objPublicacaoLegadoDTO->getNumIdPublicacaoLegadoAgrupador());
            $objPublicacaoDTO->setNumIdOrgaoUnidadeResponsavelDocumento($objPublicacaoLegadoDTO->getNumIdOrgaoUnidade());
            $objPublicacaoDTO->setNumIdUnidadeResponsavelDocumento($objPublicacaoLegadoDTO->getNumIdUnidade());
            $objPublicacaoDTO->setNumIdSerieDocumento($objPublicacaoLegadoDTO->getNumIdSerie());
            $objPublicacaoDTO->setStrNumeroDocumento($objPublicacaoLegadoDTO->getStrNumero());
            $objPublicacaoDTO->setStrProtocoloFormatadoPesquisaProtocolo(InfraUtil::retirarFormatacao($objPublicacaoLegadoDTO->getStrProtocoloFormatado(),false));
            $objPublicacaoDTO->setStrProtocoloFormatadoProtocolo($objPublicacaoLegadoDTO->getStrProtocoloFormatado());
            $objPublicacaoDTO->setDtaGeracaoProtocolo($objPublicacaoLegadoDTO->getDtaGeracao());
            $objPublicacaoDTO->setDtaPublicacao($objPublicacaoLegadoDTO->getDtaPublicacao());
            $objPublicacaoDTO->setNumNumero(null);
            $objPublicacaoDTO->setNumIdVeiculoPublicacao($objPublicacaoLegadoDTO->getNumIdVeiculoPublicacao());
            $objPublicacaoDTO->setStrResumo($objPublicacaoLegadoDTO->getStrResumo());
            $objPublicacaoDTO->setNumIdVeiculoIO($objPublicacaoLegadoDTO->getNumIdVeiculoIO());
            $objPublicacaoDTO->setDtaPublicacaoIO($objPublicacaoLegadoDTO->getDtaPublicacaoIO());
            $objPublicacaoDTO->setNumIdSecaoIO($objPublicacaoLegadoDTO->getNumIdSecaoIO());
            $objPublicacaoDTO->setStrPaginaIO($objPublicacaoLegadoDTO->getStrPaginaIO());
            $objPublicacaoDTO->setStrConteudoDocumento($objPublicacaoLegadoDTO->getStrConteudoDocumento());
            $objPublicacaoDTO->setDblIdProcedimentoDocumento(null);
            $objPublicacaoDTO->setStrProtocoloProcedimentoFormatado(null);

            $arrObjPublicacaoDTO_Indexar[] = $objPublicacaoDTO;
          }
          $objIndexacaoDTO->setArrObjPublicacaoDTO($arrObjPublicacaoDTO_Indexar);
          $objIndexacaoDTO->setStrStaOperacao(IndexacaoRN::$TO_PUBLICACAO);

          $objIndexacaoRN->indexarPublicacao($objIndexacaoDTO);

        }

        $numSegLegado = InfraUtil::verificarTempoProcessamento($numSeg);

        $strMsg = 'Indexação de Publicações - '.$numRegistros.' publicações legadas indexadas em '.InfraData::formatarTimestamp($numSegLegado);

        if (!InfraUtil::isBolLinhaDeComando()) {
          $prb->setStrRotulo($strMsg);
        }

        $this->logar($strMsg);
      }

      sleep(2);

      $objInfraException->lancarValidacao('Operação Finalizada.');

    }catch(Exception $e){
      throw new InfraException('Erro indexando publicações.',$e);
    }
  }

  protected function gerarIndexacaoBasesConhecimentoConectado(){
    try{

      $objInfraException = new InfraException();

      ini_set('max_execution_time','0');
      ini_set('memory_limit','2048M');

      $strMsg = 'Indexação de Bases de Conhecimento - iniciando...';

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb = InfraBarraProgresso2::newInstance('IndexacaoBasesConhecimento', array('cor_fundo'=>'#5c9ccc','cor_borda'=>'#4297d7'));
        $prb->setStrRotulo($strMsg);
      }

      $this->logar($strMsg);

      $numSeg = InfraUtil::verificarTempoProcessamento();

      $objBaseConhecimentoRN 	= new BaseConhecimentoRN();

      $objBaseConhecimentoDTO = new BaseConhecimentoDTO();
      $objBaseConhecimentoDTO->retNumIdBaseConhecimento();
      $objBaseConhecimentoDTO->setStrStaEstado(BaseConhecimentoRN::$TE_LIBERADO);
      $objBaseConhecimentoDTO->setOrdNumIdBaseConhecimento(InfraDTO::$TIPO_ORDENACAO_DESC);

      $arrObjBaseConhecimentoDTO =	$objBaseConhecimentoRN->listar($objBaseConhecimentoDTO);

      $numRegistros 			=	count($arrObjBaseConhecimentoDTO);
      $numRegistrosPagina = 10;
      $numPaginas 				= ceil($numRegistros/$numRegistrosPagina);

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb->setNumMin(0);
        $prb->setNumMax($numPaginas);
      }

      $objIndexacaoRN = new IndexacaoRN();
      $objIndexacaoDTO = new IndexacaoDTO();

      for ($numPaginaAtual = 0; $numPaginaAtual < $numPaginas; $numPaginaAtual++){

        if ($numPaginaAtual ==  ($numPaginas-1)){
          $numRegistrosAtual = $numRegistros;
        }else{
          $numRegistrosAtual = ($numPaginaAtual+1)*$numRegistrosPagina;
        }

        $strMsg = 'Indexação de Bases de Conhecimento - ['.$numRegistrosAtual.' de '.$numRegistros.']...';

        if (!InfraUtil::isBolLinhaDeComando()) {
          $prb->setStrRotulo($strMsg);
          $prb->moverProximo();
        }

        $this->logar($strMsg);


        $offset = ($numPaginaAtual*$numRegistrosPagina);

        if (($offset + $numRegistrosPagina) > $numRegistros) {
          $length = $numRegistros - $offset;
        }else{
          $length = $numRegistrosPagina;
        }

        $objIndexacaoDTO->setArrObjBaseConhecimentoDTO(array_slice($arrObjBaseConhecimentoDTO, $offset, $length));
        $objIndexacaoDTO->setStrStaOperacao(IndexacaoRN::$TO_BASE_CONHECIMENTO_LIBERAR);
        $objIndexacaoRN->indexarBaseConhecimento($objIndexacaoDTO);
      }

      $numSegBasesConhecimento = InfraUtil::verificarTempoProcessamento($numSeg);

      $strMsg = 'Indexação de Bases de Conhecimento - '.$numRegistros.' registros indexados em '.InfraData::formatarTimestamp($numSegBasesConhecimento);

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb->setStrRotulo($strMsg);
      }

      $this->logar($strMsg);

      sleep(2);

      $objInfraException->lancarValidacao('Operação Finalizada.');

    }catch(Exception $e){
      throw new InfraException('Erro executando indexação de bases de conhecimento.',$e);
    }
  }

  protected function gerarIndexacaoControleInternoConectado(){
    try{

      $objInfraException = new InfraException();

      ini_set('max_execution_time','0');
      ini_set('memory_limit','2048M');

      $strMsg = 'Indexação Controle Interno - iniciando...';

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb = InfraBarraProgresso2::newInstance('IndexacaoControleInterno', array('cor_fundo'=>'#5c9ccc','cor_borda'=>'#4297d7'));
        $prb->setStrRotulo($strMsg);
      }

      $this->logar($strMsg);

      $numSeg = InfraUtil::verificarTempoProcessamento();

      $objAcessoDTO = new AcessoDTO();
      $objAcessoDTO->setDistinct(true);
      $objAcessoDTO->retDblIdProtocolo();
      $objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CONTROLE_INTERNO);
      $objAcessoDTO->setOrdDblIdProtocolo(InfraDTO::$TIPO_ORDENACAO_DESC);

      $objAcessoRN = new AcessoRN();
      $arrIdProtocolos = InfraArray::converterArrInfraDTO($objAcessoRN->listar($objAcessoDTO),'IdProtocolo');

      $objIndexacaoRN = new IndexacaoRN();
      $objIndexacaoDTO = new IndexacaoDTO();

      $numRegistros 			=	count($arrIdProtocolos);
      $numRegistrosPagina = 50;
      $numPaginas 				= ceil($numRegistros/$numRegistrosPagina);

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb->setNumMin(0);
        $prb->setNumMax($numPaginas);
      }

      for ($numPaginaAtual = 0; $numPaginaAtual < $numPaginas; $numPaginaAtual++) {

        if ($numPaginaAtual == ($numPaginas - 1)) {
          $numRegistrosAtual = $numRegistros;
        } else {
          $numRegistrosAtual = ($numPaginaAtual + 1) * $numRegistrosPagina;
        }

        $strMsg = 'Indexação Controle Interno [' . $numRegistrosAtual . ' de ' . $numRegistros . ']...';

        if (!InfraUtil::isBolLinhaDeComando()) {
          $prb->setStrRotulo($strMsg);
          $prb->moverProximo();
        }

        $this->logar($strMsg);

        $objIndexacaoDTO->setArrIdProtocolos(array_slice($arrIdProtocolos, ($numPaginaAtual * $numRegistrosPagina), $numRegistrosPagina));
        $objIndexacaoDTO->setStrStaOperacao(IndexacaoRN::$TO_PROCESSO_COM_DOCUMENTOS_METADADOS);
        $objIndexacaoRN->indexarProtocolo($objIndexacaoDTO);
      }

      $numSegProtocolos = InfraUtil::verificarTempoProcessamento($numSeg);

      $strMsg = 'Indexação Controle Interno - '.$numRegistros.' processos indexados em '.InfraData::formatarTimestamp($numSegProtocolos);

      if (!InfraUtil::isBolLinhaDeComando()) {
        $prb->setStrRotulo($strMsg);
      }

      $this->logar($strMsg);

      sleep(2);

      $objInfraException->lancarValidacao('Operação Finalizada.');

    }catch(Exception $e){
      throw new InfraException('Erro executando indexação de criterios de controle internos.',$e);
    }
  }

  private function logar($strTexto){
    InfraDebug::getInstance()->gravar(InfraString::excluirAcentos($strTexto));
    LogSEI::getInstance()->gravar($strTexto,InfraLog::$INFORMACAO);
  }
}
