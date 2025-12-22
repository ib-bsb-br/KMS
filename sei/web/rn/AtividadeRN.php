<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 05/06/2008 - criado por fbv
*
* Versão do Gerador de Código: 1.17.0
*
* Versão no CVS: $Id$ 
*/

require_once dirname(__FILE__).'/../SEI.php';

class AtividadeRN extends InfraRN {

  public static $TV_VISUALIZADO = 0;
  public static $TV_NAO_VISUALIZADO = 1;
  public static $TV_ATENCAO = 2;
  public static $TV_REMOCAO_SOBRESTAMENTO = 4;
  public static $TV_PUBLICACAO = 8;
  //16 - Retorno Programado
  
  public static $TA_TODAS = 'T';
  public static $TA_MINHAS = 'M';
  public static $TA_DEFINIDAS = 'D';
  public static $TA_ESPECIFICAS = 'E';
  
  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }
  
  public function enviarRN0023(EnviarProcessoDTO $parObjEnviarProcessoDTO){
     
    if ($this->enviarRN0023Interno($parObjEnviarProcessoDTO)){
       
       $arrObjAtividadeDTO = $parObjEnviarProcessoDTO->getArrAtividades();
    
    	 $objIndexacaoDTO = new IndexacaoDTO();
    	 $objIndexacaoDTO->setArrIdProtocolos(array_unique(InfraArray::converterArrInfraDTO($arrObjAtividadeDTO,'IdProtocolo')));
       $objIndexacaoDTO->setStrStaOperacao(IndexacaoRN::$TO_PROCESSO_COM_DOCUMENTOS_METADADOS);
      	
       $objIndexacaoRN = new IndexacaoRN();
       $objIndexacaoRN->indexarProtocolo($objIndexacaoDTO);     
     }
  }

  protected function enviarRN0023InternoControlado(EnviarProcessoDTO $parObjEnviarProcessoDTO) {

  	try{

      global $SEI_MODULOS;

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('atividade_enviar',__METHOD__,$parObjEnviarProcessoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      //verifica se não houve mudança nas atividades abertas
      $arrObjAtividadeDTOOrigem = $parObjEnviarProcessoDTO->getArrAtividadesOrigem();
      $arrIdAtividadesOrigem = InfraArray::converterArrInfraDTO($arrObjAtividadeDTOOrigem,'IdAtividade');
      $arrObjAtividadeDTO = $parObjEnviarProcessoDTO->getArrAtividades();
      $arrIdProtocolosOrigem = array_unique(InfraArray::converterArrInfraDTO($arrObjAtividadeDTO,'IdProtocolo'));
      $arrIdUnidadesEnvio = InfraArray::converterArrInfraDTO($arrObjAtividadeDTO,'IdUnidade');

      $this->validarAndamentosAtuais($arrIdProtocolosOrigem, $arrIdAtividadesOrigem, $objInfraException);
      $this->validarStrSinConluirOriginaisRN0826($parObjEnviarProcessoDTO, $objInfraException);
      $this->validarStrSinRemoverAnotacoes($parObjEnviarProcessoDTO, $objInfraException);
      $this->validarStrSinEnviarEmailNotificacao($parObjEnviarProcessoDTO, $objInfraException);

			if ($parObjEnviarProcessoDTO->isSetDtaPrazo()) {
				$this->validarDtaPrazo($parObjEnviarProcessoDTO, $objInfraException);
			}else{
				$parObjEnviarProcessoDTO->setDtaPrazo(null);
			}

			if ($parObjEnviarProcessoDTO->isSetNumDias()) {
				$this->validarNumDias($parObjEnviarProcessoDTO, $objInfraException);
			}else{
				$parObjEnviarProcessoDTO->setNumDias(null);
			}

			if ($parObjEnviarProcessoDTO->isSetStrSinDiasUteis()) {
				$this->validarStrSinDiasUteis($parObjEnviarProcessoDTO, $objInfraException);
			}else{
				$parObjEnviarProcessoDTO->setStrSinDiasUteis('N');
			}

			if (!InfraString::isBolVazia($parObjEnviarProcessoDTO->getDtaPrazo()) && !InfraString::isBolVazia($parObjEnviarProcessoDTO->getNumDias())){
        $objInfraException->adicionarValidacao('Não é possível informar simultaneamente uma data específica e um número de dias para o Retorno Programado.');
			}

			$objInfraException->lancarValidacoes();


			$objRetornoProgramadoRN = new RetornoProgramadoRN();
      if ($parObjEnviarProcessoDTO->getStrSinManterAberto()=='N'){
				foreach($arrIdProtocolosOrigem as $dblIdProtocoloOrigem){
					$objRetornoProgramadoDTO 	= new RetornoProgramadoDTO();
          $objRetornoProgramadoDTO->setDblIdProtocoloAtividadeEnvio($dblIdProtocoloOrigem);
					$objRetornoProgramadoDTO->setNumIdUnidadeOrigemAtividadeEnvio($arrIdUnidadesEnvio);
					$objRetornoProgramadoRN->validarExistencia($objRetornoProgramadoDTO,$objInfraException);
				}
      }
      $objInfraException->lancarValidacoes();
      
      //recupera dados dos processos
    	$objProtocoloDTO = new ProtocoloDTO();
    	$objProtocoloDTO->retDblIdProtocolo();
    	$objProtocoloDTO->retStrStaNivelAcessoGlobal();
      $objProtocoloDTO->retStrStaEstado();
    	$objProtocoloDTO->retStrProtocoloFormatado();
    	$objProtocoloDTO->retStrNomeTipoProcedimentoProcedimento();
      $objProtocoloDTO->retNumIdTipoProcedimentoProcedimento();
      $objProtocoloDTO->retNumIdUnidadeGeradora();
    	$objProtocoloDTO->setDblIdProtocolo($arrIdProtocolosOrigem,InfraDTO::$OPER_IN);
    		
    	$objProtocoloRN = new ProtocoloRN();
   		$arrObjProtocoloDTO = InfraArray::indexarArrInfraDTO($objProtocoloRN->listarRN0668($objProtocoloDTO),'IdProtocolo');

      $objProcedimentoRN = new ProcedimentoRN();
      foreach($arrObjProtocoloDTO as $objProtocoloDTO){
        if ($objProtocoloDTO->getStrStaNivelAcessoGlobal()==ProtocoloRN::$NA_SIGILOSO){
          $objInfraException->adicionarValidacao('Processo '.$objProtocoloDTO->getStrProtocoloFormatado().' não pode ser enviado.');
        }

        $objProcedimentoRN->verificarEstadoProcedimento($objProtocoloDTO);
      }

   		$arrUnidadesAtividades = array_unique(array_merge($arrIdUnidadesEnvio,InfraArray::converterArrInfraDTO($arrObjAtividadeDTO,'IdUnidadeOrigem')));

   		//dados de unidades
			$objUnidadeDTO = new UnidadeDTO();
      $objUnidadeDTO->setBolExclusaoLogica(false);
			$objUnidadeDTO->retNumIdUnidade();
			$objUnidadeDTO->retNumIdOrgao();
			$objUnidadeDTO->retStrSigla();
			$objUnidadeDTO->retStrDescricao();
			$objUnidadeDTO->retStrSiglaOrgao();
			$objUnidadeDTO->retStrDescricaoOrgao();
			$objUnidadeDTO->retStrSinMailPendencia();
      $objUnidadeDTO->retStrSinEnvioProcessoOrgao();
      $objUnidadeDTO->retStrSinEnvioProcesso();
      $objUnidadeDTO->retStrSinAtivo();

			$objUnidadeDTO->setNumIdUnidade($arrUnidadesAtividades,InfraDTO::$OPER_IN);
			
			$objUnidadeRN = new UnidadeRN();
			$arrObjUnidadeDTO = InfraArray::indexarArrInfraDTO($objUnidadeRN->listarRN0127($objUnidadeDTO),'IdUnidade');

			$arrUnidadesConsultadas = InfraArray::converterArrInfraDTO($arrObjUnidadeDTO,'IdUnidade');
			
			foreach($arrUnidadesAtividades as $numIdUnidadeAtividade){
			  if (!in_array($numIdUnidadeAtividade,$arrUnidadesConsultadas)){
			    throw new InfraException('Unidade ['.$numIdUnidadeAtividade.'] não encontrada.');
			  }else if ($arrObjUnidadeDTO[$numIdUnidadeAtividade]->getStrSinAtivo()=='N'){
          throw new InfraException('Unidade '.$arrObjUnidadeDTO[$numIdUnidadeAtividade]->getStrSigla().' desativada.');
        }
			}

      foreach($arrObjUnidadeDTO as $objUnidadeDTO){
        if (in_array($objUnidadeDTO->getNumIdUnidade(),$arrIdUnidadesEnvio)) {
          if ($objUnidadeDTO->getStrSinEnvioProcessoOrgao() == 'N') {
            $objInfraException->adicionarValidacao('Órgão da unidade ' . $objUnidadeDTO->getStrSigla() . ' não pode receber processos.');
          } else if ($objUnidadeDTO->getStrSinEnvioProcesso() == 'N') {
            $objInfraException->adicionarValidacao('Unidade ' . $objUnidadeDTO->getStrSigla() . ' não pode receber processos.');
          }
        }
      }

      $objInfraException->lancarValidacoes();

			$strPrazo = null;

			if (!InfraString::isBolVazia($parObjEnviarProcessoDTO->getDtaPrazo())){

				foreach($arrObjAtividadeDTO as $objAtividadeDTO) {
					$objAtividadeDTO->setDtaPrazo($parObjEnviarProcessoDTO->getDtaPrazo());
				}

			}else if (!InfraString::isBolVazia($parObjEnviarProcessoDTO->getNumDias())){

				if ($parObjEnviarProcessoDTO->getStrSinDiasUteis() == 'N'){

					$strPrazo = InfraData::calcularData($parObjEnviarProcessoDTO->getNumDias(),InfraData::$UNIDADE_DIAS, InfraData::$SENTIDO_ADIANTE);

					foreach($arrObjAtividadeDTO as $objAtividadeDTO) {
						$objAtividadeDTO->setDtaPrazo($strPrazo);
					}

				}else{

					$arrIdOrgaoEnvio = array();

					//filtra orgaos das unidades de destino
					foreach($arrIdUnidadesEnvio as $numIdUnidadeEnvio){
						if (!in_array($arrObjUnidadeDTO[$numIdUnidadeEnvio]->getNumIdOrgao(), $arrIdOrgaoEnvio)){
							$arrIdOrgaoEnvio[] = $arrObjUnidadeDTO[$numIdUnidadeEnvio]->getNumIdOrgao();
						}
					}

					$strDataInicial = InfraData::getStrDataAtual();

					//busca feriados ate 1 ano a frente do periodo corrido solicitado
					$strDataFinal = InfraData::calcularData(($parObjEnviarProcessoDTO->getNumDias() + 365), InfraData::$UNIDADE_DIAS, InfraData::$SENTIDO_ADIANTE, $strDataInicial);

					$objPublicacaoRN = new PublicacaoRN();
					$arrDataPrazo = array();

					//pega todos os feriados cadastrados por órgão
					foreach($arrIdOrgaoEnvio as $numIdOrgaoEnvio) {

						$objFeriadoDTO = new FeriadoDTO();
						$objFeriadoDTO->setNumIdOrgao($numIdOrgaoEnvio);
						$objFeriadoDTO->setDtaInicial($strDataInicial);
						$objFeriadoDTO->setDtaFinal($strDataFinal);


						$arrFeriados = InfraArray::simplificarArr($objPublicacaoRN->listarFeriados($objFeriadoDTO), 'Data');

						$numDias = $parObjEnviarProcessoDTO->getNumDias();
						$strPrazo = $strDataInicial;

						while($numDias){

							do{
							  $strPrazo = InfraData::calcularData(1, InfraData::$UNIDADE_DIAS, InfraData::$SENTIDO_ADIANTE, $strPrazo);
							}while (InfraData::obterDescricaoDiaSemana($strPrazo) == 'sábado' ||	InfraData::obterDescricaoDiaSemana($strPrazo) == 'domingo' ||	in_array($strPrazo, $arrFeriados));

							$numDias--;
						}

						$arrDataPrazo[$numIdOrgaoEnvio] = $strPrazo;
					}


					foreach($arrObjAtividadeDTO as $objAtividadeDTO) {
						$objAtividadeDTO->setDtaPrazo($arrDataPrazo[$arrObjUnidadeDTO[$objAtividadeDTO->getNumIdUnidade()]->getNumIdOrgao()]);
					}
				}
			}else{

				foreach($arrObjAtividadeDTO as $objAtividadeDTO) {
					$objAtividadeDTO->setDtaPrazo(null);
				}

			}

  		$bolFlagEnviouParaOutraUnidade = false;
  		
  		$arrObjEmailDTOUnidades = array();
  		
			$objInfraParametro = new InfraParametro(BancoSEI::getInstance());
			$strEmailSistema = $objInfraParametro->getValor('SEI_EMAIL_SISTEMA');
  		
			$objEmailSistemaDTO = new EmailSistemaDTO();
			$objEmailSistemaDTO->retStrDe();
			$objEmailSistemaDTO->retStrPara();
			$objEmailSistemaDTO->retStrAssunto();
			$objEmailSistemaDTO->retStrConteudo();
			$objEmailSistemaDTO->setNumIdEmailSistema(EmailSistemaRN::$ES_ENVIO_PROCESSO_PARA_UNIDADE);
			
			$objEmailSistemaRN = new EmailSistemaRN();
			$objEmailSistemaDTO = $objEmailSistemaRN->consultar($objEmailSistemaDTO);
			
			$objAnotacaoRN = new AnotacaoRN();
			$objDocumentoRN = new DocumentoRN();
			
      foreach($arrObjAtividadeDTO as $objAtividadeDTO){
        
      	$objProtocoloDTO = $arrObjProtocoloDTO[$objAtividadeDTO->getDblIdProtocolo()];
      	$objUnidadeDTO = $arrObjUnidadeDTO[$objAtividadeDTO->getNumIdUnidade()];
      	$objUnidadeDTOOrigem = $arrObjUnidadeDTO[$objAtividadeDTO->getNumIdUnidadeOrigem()];
      	
        $this->validarDblIdProtocoloRN0704($objAtividadeDTO, $objInfraException);
        $this->validarNumIdUnidadeRN0705($objAtividadeDTO, $objInfraException);
        $this->validarNumIdUnidadeOrigemRN0707($objAtividadeDTO, $objInfraException);
        $this->validarNumIdUsuario($objAtividadeDTO, $objInfraException);
        $this->validarNumIdUsuarioOrigemRN0708($objAtividadeDTO, $objInfraException);
        $this->validarDtaPrazoRN0714($objAtividadeDTO, $objInfraException);
        
        $objInfraException->lancarValidacoes();

        
        // Filtra campos do DTO
        $dto = new AtividadeDTO();
        $dto->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
        $dto->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade());
        $dto->setNumIdUnidadeOrigem($objAtividadeDTO->getNumIdUnidadeOrigem());
        $dto->setNumIdUsuario($objAtividadeDTO->getNumIdUsuario());
        $dto->setNumIdUsuarioOrigem($objAtividadeDTO->getNumIdUsuarioOrigem());
        $dto->setDtaPrazo($objAtividadeDTO->getDtaPrazo());
        $objAtividadeDTO = $dto;


				if ($objProtocoloDTO->getStrStaNivelAcessoGlobal()==ProtocoloRN::$NA_SIGILOSO){
					$objInfraException->lancarValidacao('Processo sigiloso '.$objProtocoloDTO->getStrProtocoloFormatado().' não pode ser enviado para outra unidade.');
				}
    		
    		$arrObjAtributoAndamentoDTO = array();

    		$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
    		$objAtributoAndamentoDTO->setStrNome('UNIDADE');
    		$objAtributoAndamentoDTO->setStrValor($objUnidadeDTOOrigem->getStrSigla().'¥'.$objUnidadeDTOOrigem->getStrDescricao());
    		$objAtributoAndamentoDTO->setStrIdOrigem($objAtividadeDTO->getNumIdUnidadeOrigem());
    		$arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;

    		$objAtividadeDTO->setArrObjAtributoAndamentoDTO($arrObjAtributoAndamentoDTO);
    		$objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_PROCESSO_REMETIDO_UNIDADE);
    		 
    		
    		$ret = $this->gerarInternaRN0727($objAtividadeDTO);      
  	  		  
	      //enviando para outra unidade 
        if ($objAtividadeDTO->getNumIdUnidade()!=SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
        	
        	
        	$bolFlagEnviouParaOutraUnidade = true;
        	
        	//se informou uma data de retorno programado
  	  		if ($objAtividadeDTO->getDtaPrazo() != null){

  	  			//verifica se já não existe um retorno programado para a unidade
						$objRetornoProgramadoDTO 	= new RetornoProgramadoDTO();
						$objRetornoProgramadoDTO->retNumIdRetornoProgramado();
						$objRetornoProgramadoDTO->setNumIdUnidadeOrigemAtividadeEnvio($objAtividadeDTO->getNumIdUnidadeOrigem());
						$objRetornoProgramadoDTO->setNumIdUnidadeAtividadeEnvio($objAtividadeDTO->getNumIdUnidade());
						$objRetornoProgramadoDTO->setDblIdProtocoloAtividadeEnvio($objAtividadeDTO->getDblIdProtocolo());
						$objRetornoProgramadoDTO->setNumIdAtividadeRetorno(null);
						$objRetornoProgramadoDTO = $objRetornoProgramadoRN->consultar($objRetornoProgramadoDTO);					

						if ($objRetornoProgramadoDTO!=null){
							$objInfraException->lancarValidacao('Já existe um Retorno Programado em aberto para a unidade '.$objUnidadeDTO->getStrSigla().'/'.$objUnidadeDTO->getStrSiglaOrgao().' no processo '.$objProtocoloDTO->getStrProtocoloFormatado().'.');
						}
						
						// cadastrar como Retorno Programado
						$objRetornoProgramadoDTO = new RetornoProgramadoDTO();
						$objRetornoProgramadoDTO->setNumIdRetornoProgramado(null);
						$objRetornoProgramadoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
						$objRetornoProgramadoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
						$objRetornoProgramadoDTO->setNumIdAtividadeEnvio($ret->getNumIdAtividade());
						$objRetornoProgramadoDTO->setNumIdAtividadeRetorno(null);
						$objRetornoProgramadoDTO->setDtaProgramada($objAtividadeDTO->getDtaPrazo());
						$objRetornoProgramadoDTO->setDthAlteracao(null);
						$objRetornoProgramadoRN->cadastrar($objRetornoProgramadoDTO);
  	  		}
  	  		
  	  		//verifica se esta respondendo um retorno programado existente para esta unidade e protocolo
					$objRetornoProgramadoDTO 	= new RetornoProgramadoDTO();
					$objRetornoProgramadoDTO->retNumIdRetornoProgramado();
					$objRetornoProgramadoDTO->setNumIdUnidadeAtividadeEnvio($objAtividadeDTO->getNumIdUnidadeOrigem());
					$objRetornoProgramadoDTO->setNumIdUnidadeOrigemAtividadeEnvio($objAtividadeDTO->getNumIdUnidade());
					$objRetornoProgramadoDTO->setDblIdProtocoloAtividadeEnvio($objAtividadeDTO->getDblIdProtocolo());
					$objRetornoProgramadoDTO->setNumIdAtividadeRetorno(null);
					$objRetornoProgramadoDTO = $objRetornoProgramadoRN->consultar($objRetornoProgramadoDTO);					
					
					if ($objRetornoProgramadoDTO!=null){
						$objRetornoProgramadoDTO->setNumIdAtividadeRetorno($ret->getNumIdAtividade());
						$objRetornoProgramadoRN->alterar($objRetornoProgramadoDTO);
					}

          //Associar o processo e seus documentos com esta unidade
    			$objAssociarDTO = new AssociarDTO();	  	
    			$objAssociarDTO->setDblIdProcedimento($objAtividadeDTO->getDblIdProtocolo());
    			$objAssociarDTO->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade());
    			$objAssociarDTO->setNumIdUsuario(null);
    			$objAssociarDTO->setStrStaNivelAcessoGlobal($objProtocoloDTO->getStrStaNivelAcessoGlobal());
					$objProtocoloRN->associarRN0982($objAssociarDTO); 					  

					
					if ($parObjEnviarProcessoDTO->getStrSinManterAberto()=='N'){
						
						$dto = new AtividadeDTO();
						$dto->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
						$dto->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
						$dto->setNumIdTarefa(TarefaRN::$TI_CONCLUSAO_AUTOMATICA_UNIDADE);

						$this->gerarInternaRN0727($dto);
					}
						
		      if ($parObjEnviarProcessoDTO->getStrSinRemoverAnotacoes()=='S'){
		   		  $objAnotacaoDTO = new AnotacaoDTO();
		   		  $objAnotacaoDTO->retNumIdAnotacao();
		   		  $objAnotacaoDTO->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
		     		$objAnotacaoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
	        	$objAnotacaoDTO->setStrStaAnotacao(AnotacaoRN::$TA_UNIDADE);
		   	  	$objAnotacaoRN->excluir($objAnotacaoRN->listar($objAnotacaoDTO));
		      }
					
		      //bloqueia assinaturas dos documentos gerados e assinados na unidade
		      $objProcedimentoDTO = new ProcedimentoDTO(); 
		      $objProcedimentoDTO->setDblIdProcedimento($objAtividadeDTO->getDblIdProtocolo());
	        $objDocumentoRN->bloquearTramitacaoConclusao($objProcedimentoDTO);
		      
					//mail
					if ($objEmailSistemaDTO!=null && ($parObjEnviarProcessoDTO->getStrSinEnviarEmailNotificacao()=='S' || $objUnidadeDTO->getStrSinMailPendencia()=='S')){

					  $objEmailUnidadeDTO = new EmailUnidadeDTO();
					  $objEmailUnidadeDTO->retStrEmail();
					  $objEmailUnidadeDTO->retStrDescricao();
					  $objEmailUnidadeDTO->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade());
					  
					  $objEmailUnidadeRN = new EmailUnidadeRN();
					  $arrObjEmailUnidadeDTO = $objEmailUnidadeRN->listar($objEmailUnidadeDTO);
					  
					  if (count($arrObjEmailUnidadeDTO)==0){
					  	$objInfraException->lancarValidacao('Unidade '.$objUnidadeDTO->getStrSigla().'/'.$objUnidadeDTO->getStrSiglaOrgao().' não possui email cadastrado.');
					  }
					  
					  $strDe = $objEmailSistemaDTO->getStrDe();
					  $strDe = str_replace('@email_sistema@',$strEmailSistema,$strDe);
					  $strDe = str_replace('@sigla_sistema@',SessaoSEI::getInstance()->getStrSiglaSistema(),$strDe);
					  
					  $strEmailsUnidade = '';
					  foreach($arrObjEmailUnidadeDTO as $objEmailUnidadeDTO){
					  	$strEmailsUnidade .= $objEmailUnidadeDTO->getStrDescricao().' <'.$objEmailUnidadeDTO->getStrEmail().'> ;';			  	
					  }
					  $strEmailsUnidade = substr($strEmailsUnidade,0,-1);
					  
					  $strPara = $objEmailSistemaDTO->getStrPara();
					  $strPara = str_replace('@emails_unidade@',$strEmailsUnidade,$strPara);
					  
					  $strAssunto = $objEmailSistemaDTO->getStrAssunto();
					  $strAssunto = str_replace('@processo@',$objProtocoloDTO->getStrProtocoloFormatado(),$strAssunto);
					  
					  $strConteudo = $objEmailSistemaDTO->getStrConteudo();
					  $strConteudo = str_replace('@processo@',$objProtocoloDTO->getStrProtocoloFormatado(),$strConteudo);
					  $strConteudo = str_replace('@tipo_processo@',$objProtocoloDTO->getStrNomeTipoProcedimentoProcedimento(),$strConteudo);
					  $strConteudo = str_replace('@sigla_unidade_remetente@',$objUnidadeDTOOrigem->getStrSigla(),$strConteudo);
					  $strConteudo = str_replace('@descricao_unidade_remetente@',$objUnidadeDTOOrigem->getStrDescricao(),$strConteudo);
					  $strConteudo = str_replace('@sigla_orgao_unidade_remetente@',$objUnidadeDTOOrigem->getStrSiglaOrgao(),$strConteudo);
					  $strConteudo = str_replace('@descricao_orgao_unidade_remetente@',$objUnidadeDTOOrigem->getStrDescricaoOrgao(),$strConteudo);
					  $strConteudo = str_replace('@sigla_unidade_destinataria@',$objUnidadeDTO->getStrSigla(),$strConteudo);
					  $strConteudo = str_replace('@descricao_unidade_destinataria@',$objUnidadeDTO->getStrDescricao(),$strConteudo);
					  $strConteudo = str_replace('@sigla_orgao_unidade_destinataria@',$objUnidadeDTO->getStrSiglaOrgao(),$strConteudo);
					  $strConteudo = str_replace('@descricao_orgao_unidade_destinataria@',$objUnidadeDTO->getStrDescricaoOrgao(),$strConteudo);

            $objEmailDTO = new EmailDTO();
            $objEmailDTO->setStrDe($strDe);
            $objEmailDTO->setStrPara($strPara);
            $objEmailDTO->setStrAssunto($strAssunto);
            $objEmailDTO->setStrMensagem($strConteudo);
            $arrObjEmailDTOUnidades[] = $objEmailDTO;

          }
        }
      }

      if (count($arrObjEmailDTOUnidades)) {
        EmailRN::processar($arrObjEmailDTOUnidades);
      }

      if (count($SEI_MODULOS)){

        $arrObjProcedimentoAPI = array();
        foreach ($arrObjProtocoloDTO as $objProtocoloDTO){
          $objProcedimentoAPI = new ProcedimentoAPI();
          $objProcedimentoAPI->setIdProcedimento($objProtocoloDTO->getDblIdProtocolo());
          $objProcedimentoAPI->setNumeroProtocolo($objProtocoloDTO->getStrProtocoloFormatado());
          $objProcedimentoAPI->setIdTipoProcedimento($objProtocoloDTO->getNumIdTipoProcedimentoProcedimento());
          $objProcedimentoAPI->setNomeTipoProcedimento($objProtocoloDTO->getStrNomeTipoProcedimentoProcedimento());
          $objProcedimentoAPI->setIdUnidadeGeradora($objProtocoloDTO->getNumIdUnidadeGeradora());
          $arrObjProcedimentoAPI[] = $objProcedimentoAPI;
        }


        $arrObjUnidadeAPI = array();
        foreach($arrIdUnidadesEnvio as $numIdUnidadeEnvio){

          $objUnidadeDTO = $arrObjUnidadeDTO[$numIdUnidadeEnvio];

          $objUnidadeAPI = new UnidadeAPI();
          $objUnidadeAPI->setIdUnidade($objUnidadeDTO->getNumIdUnidade());
          $objUnidadeAPI->setSigla($objUnidadeDTO->getStrSigla());
          $objUnidadeAPI->setDescricao($objUnidadeDTO->getStrDescricao());

          $objOrgaoAPI = new OrgaoAPI();
          $objOrgaoAPI->setIdOrgao($objUnidadeDTO->getNumIdOrgao());
          $objOrgaoAPI->setSigla($objUnidadeDTO->getStrSiglaOrgao());
          $objOrgaoAPI->setDescricao($objUnidadeDTO->getStrDescricaoOrgao());
          $objUnidadeAPI->setOrgao($objOrgaoAPI);

          $arrObjUnidadeAPI[] = $objUnidadeAPI;
        }

        foreach ($SEI_MODULOS as $seiModulo) {
          $seiModulo->executar('enviarProcesso', $arrObjProcedimentoAPI, $arrObjUnidadeAPI);
        }
      }

      return $bolFlagEnviouParaOutraUnidade;
     
     
    }catch(Exception $e){
      throw new InfraException('Erro gerando andamento.',$e);
    }
  }
  
  protected function atualizarAndamentoControlado(AtualizarAndamentoDTO $objAtualizarAndamentoDTO){
    try {

			$objInfraException = new InfraException();

    	SessaoSEI::getInstance()->validarAuditarPermissao('atividade_gerar',__METHOD__,$objAtualizarAndamentoDTO);
    	
      if (InfraString::isBolVazia($objAtualizarAndamentoDTO->getStrDescricao())){
        $objInfraException = new InfraException();
        $objInfraException->lancarValidacao('Descrição não informada.');
      }
      
      $this->validarAndamentosAtuais(InfraArray::converterArrInfraDTO($objAtualizarAndamentoDTO->getArrObjProtocoloDTO(),'IdProtocolo'), 
                                     InfraArray::converterArrInfraDTO($objAtualizarAndamentoDTO->getArrObjAtividadeDTO(),'IdAtividade'),
					                           $objInfraException);

			$objInfraException->lancarValidacoes();
      
      $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
      $objAtributoAndamentoDTO->setStrNome('DESCRICAO');
      $objAtributoAndamentoDTO->setStrValor($objAtualizarAndamentoDTO->getStrDescricao());
      $objAtributoAndamentoDTO->setStrIdOrigem(null);
      $arrObjAtributoAndamentoDTO = array($objAtributoAndamentoDTO);
      
      foreach($objAtualizarAndamentoDTO->getArrObjProtocoloDTO() as $objProtocoloDTO){
      	
        $objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
        $objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_ATUALIZACAO_ANDAMENTO);
        $objAtividadeDTO->setArrObjAtributoAndamentoDTO($arrObjAtributoAndamentoDTO);
        
        $this->gerarInternaRN0727($objAtividadeDTO);
      }      	
      
      $this->concluirRN0726($objAtualizarAndamentoDTO->getArrObjAtividadeDTO());
      
    }catch(Exception $e){
      throw new InfraException('Erro atualizando andamento.',$e);
    }
  }

  protected function configurarVisualizadaControlado($arrObjAtividadeDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('atividade_alterar', __METHOD__, $arrObjAtividadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      $objAtividadeBD = new AtividadeBD($this->getObjInfraIBanco());

      foreach($arrObjAtividadeDTO as $objAtividadeDTO){
			  $dto = new AtividadeDTO();
			  $dto->setNumIdUsuarioVisualizacao(SessaoSEI::getInstance()->getNumIdUsuario());
			  $dto->setNumTipoVisualizacao(AtividadeRN::$TV_VISUALIZADO);
			  $dto->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
        $objAtividadeBD->alterar($dto);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro configurando atividade como visualizada.',$e);
    }
  }

  protected function excluirRN0034Controlado($arrObjAtividadeDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('atividade_excluir',__METHOD__,$arrObjAtividadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();


      $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
      $objAtributoAndamentoDTO->retNumIdAtributoAndamento();
      $objAtributoAndamentoRN = new AtributoAndamentoRN();
      
      $objAtividadeBD = new AtividadeBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjAtividadeDTO);$i++){
        
        $objAtributoAndamentoDTO->setNumIdAtividade($arrObjAtividadeDTO[$i]->getNumIdAtividade());
        $objAtributoAndamentoRN->excluirRN1365($objAtributoAndamentoRN->listarRN1367($objAtributoAndamentoDTO));
        
        $objAtividadeBD->excluir($arrObjAtividadeDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo Atividade.',$e);
    }
  }

  protected function consultarRN0033Conectado(AtividadeDTO $objAtividadeDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('atividade_consultar',__METHOD__,$objAtividadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAtividadeBD = new AtividadeBD($this->getObjInfraIBanco());
      $ret = $objAtividadeBD->consultar($objAtividadeDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando Atividade.',$e);
    }
  }

  protected function listarRN0036Conectado(AtividadeDTO $objAtividadeDTO) {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('atividade_listar',__METHOD__,$objAtividadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();


      $objAtividadeBD = new AtividadeBD($this->getObjInfraIBanco());
      $ret = $objAtividadeBD->listar($objAtividadeDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Atividades.',$e);
    }
  }

  protected function contarRN0035Conectado(AtividadeDTO $objAtividadeDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('atividade_listar',__METHOD__,$objAtividadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAtividadeBD = new AtividadeBD($this->getObjInfraIBanco());
      $ret = $objAtividadeBD->contar($objAtividadeDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro contando Atividades.',$e);
    }
  }
  
  protected function bloquearControlado(AtividadeDTO $objAtividadeDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('atividade_consultar',__METHOD__,$objAtividadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAtividadeBD = new AtividadeBD($this->getObjInfraIBanco());
      $ret = $objAtividadeBD->bloquear($objAtividadeDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro bloqueando Atividade.',$e);
    }
  }

  private function validarDblIdProtocoloRN0704(AtividadeDTO $objAtividadeDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAtividadeDTO->getDblIdProtocolo())){
      $objInfraException->adicionarValidacao('Protocolo não informado.');
    }
  }

  private function validarNumIdUnidadeRN0705(AtividadeDTO $objAtividadeDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAtividadeDTO->getNumIdUnidade())){
      $objInfraException->adicionarValidacao('Unidade não informada.');
    }
  }

  private function validarNumIdUnidadeOrigemRN0707(AtividadeDTO $objAtividadeDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAtividadeDTO->getNumIdUnidadeOrigem())){
      $objAtividadeDTO->setNumIdUnidadeOrigem(null);
    }
  }

  private function validarNumIdUsuario(AtividadeDTO $objAtividadeDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAtividadeDTO->getNumIdUsuario())){
      $objAtividadeDTO->setNumIdUsuario(null);
    }
  }

  private function validarNumIdTarefaRN0706(AtividadeDTO $objAtividadeDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAtividadeDTO->getNumIdTarefa())){
      $objInfraException->adicionarValidacao('Tarefa não informada.');
    }
  }

  private function validarNumIdUsuarioOrigemRN0708(AtividadeDTO $objAtividadeDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAtividadeDTO->getNumIdUsuarioOrigem())){
      $objInfraException->adicionarValidacao('Usuário origem não informado.');
    }
  }
  
  private function validarNumIdUsuarioVisualizacao(AtividadeDTO $objAtividadeDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAtividadeDTO->getNumIdUsuarioVisualizacao())){
      $objAtividadeDTO->setNumIdUsuarioVisualizacao(null);
    }
  }
  
  private function validarNumIdUsuarioAtribuicao(AtividadeDTO $objAtividadeDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAtividadeDTO->getNumIdUsuarioAtribuicao())){
      $objAtividadeDTO->setNumIdUsuarioAtribuicao(null);
    }
  }
    
  private function validarStrSinInicial(AtividadeDTO $objAtividadeDTO, InfraException $objInfraException){
    
    if (InfraString::isBolVazia($objAtividadeDTO->getStrSinInicial())){
    		$objInfraException->adicionarValidacao('Sinalizador de andamento inicial não informado.');
  	}else{
      if (!InfraUtil::isBolSinalizadorValido($objAtividadeDTO->getStrSinInicial())){
        $objInfraException->adicionarValidacao('Sinalizador de andamento inicial inválido.');
      }
  	}    
  }

  private function validarDtaPrazoRN0714(AtividadeDTO $objAtividadeDTO, InfraException $objInfraException){

    if (InfraString::isBolVazia($objAtividadeDTO->getDtaPrazo())){
      $objAtividadeDTO->setDtaPrazo(null);
    }else{
      if (!InfraData::validarData($objAtividadeDTO->getDtaPrazo())){
        $objInfraException->adicionarValidacao('Data de retorno programado da atividade inválida.');
      }

      if (InfraData::compararDatas(InfraData::getStrDataAtual(),$objAtividadeDTO->getDtaPrazo())<0){
        $objInfraException->adicionarValidacao('Data de retorno programado da atividade não pode estar no passado.');
      }
    }
  }

  protected function concluirRN0726Controlado($arrObjAtividadeDTO){
    try {
      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('procedimento_concluir',__METHOD__,$arrObjAtividadeDTO);

      $objAtividadeBD = new AtividadeBD($this->getObjInfraIBanco());
      
      $strDataHoraAtual = InfraData::getStrDataHoraAtual();

      foreach($arrObjAtividadeDTO as $objAtividadeDTO){
        $dto = new AtividadeDTO();
        $dto->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
        $dto->setDthConclusao($strDataHoraAtual);
        $dto->setNumIdUsuarioConclusao(SessaoSEI::getInstance()->getNumIdUsuario());
        $objAtividadeBD->alterar($dto);
      }
      
      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro concluindo andamento.',$e);
    }
  }

  private function permitirAndamentoConcluidoModulos(AtividadeDTO $objAtividadeDTO){
    global $SEI_MODULOS;

    $objAndamentoAPI = new AndamentoAPI();
    $objAndamentoAPI->setIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
    $objAndamentoAPI->setIdTarefa($objAtividadeDTO->getNumIdTarefa());

    //verifica se algum módulo permite lançar este andamento mesmo com o processo fechado
    foreach ($SEI_MODULOS as $seiModulo) {
      if ($seiModulo->executar('permitirAndamentoConcluido', $objAndamentoAPI)===true) {
        return true;
      }
    }
    
    return false;
  }

  protected function gerarInternaRN0727Controlado(AtividadeDTO $objAtividadeDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('atividade_gerar',__METHOD__,$objAtividadeDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarDblIdProtocoloRN0704($objAtividadeDTO, $objInfraException);
      $this->validarNumIdUnidadeRN0705($objAtividadeDTO, $objInfraException);

      if ($objAtividadeDTO->isSetNumIdTarefa()) {
        $this->validarNumIdTarefaRN0706($objAtividadeDTO, $objInfraException);
        $numIdTarefa = $objAtividadeDTO->getNumIdTarefa(); //otimizacao de acesso

      }else if ($objAtividadeDTO->isSetStrIdTarefaModuloTarefa()) {


        $objTarefaDTO = new TarefaDTO();
        $objTarefaDTO->retNumIdTarefa();
        $objTarefaDTO->setStrIdTarefaModulo($objAtividadeDTO->getStrIdTarefaModuloTarefa());

        $objTarefaRN = new TarefaRN();
        $objTarefaDTO = $objTarefaRN->consultar($objTarefaDTO);

        if ($objTarefaDTO == null){
          throw new InfraException('Identificador da tarefa no módulo não encontrado ['.$objAtividadeDTO->getStrIdTarefaModuloTarefa().'].');
        }

        $numIdTarefa = $objTarefaDTO->getNumIdTarefa();
        $objAtividadeDTO->setNumIdTarefa($numIdTarefa);

      }else{
        throw new InfraException('Tarefa não informada.');
      }

      
      if ($numIdTarefa == TarefaRN::$TI_GERACAO_PROCEDIMENTO){
      	$objAtividadeDTO->setStrSinInicial('S');
      }else{
      	$objAtividadeDTO->setStrSinInicial('N');
      }
      
      if ($objAtividadeDTO->isSetDtaPrazo()){
        $this->validarDtaPrazoRN0714($objAtividadeDTO, $objInfraException);
      }else{
        $objAtividadeDTO->setDtaPrazo(null);
      }
      
      if ($objAtividadeDTO->isSetNumIdUsuarioAtribuicao()){
        $this->validarNumIdUsuarioAtribuicao($objAtividadeDTO, $objInfraException);
      }else{
        $objAtividadeDTO->setNumIdUsuarioAtribuicao(null); 
      }

			if ($objAtividadeDTO->isSetNumIdUsuarioVisualizacao()){
				$this->validarNumIdUsuarioVisualizacao($objAtividadeDTO, $objInfraException);
			}else{
				$objAtividadeDTO->setNumIdUsuarioVisualizacao(null);
			}

      $objInfraException->lancarValidacoes();
      
      $objTarefaDTO = new TarefaDTO();
      $objTarefaDTO->retStrSinFecharAndamentosAbertos();
      $objTarefaDTO->retStrSinLancarAndamentoFechado();
      $objTarefaDTO->retStrSinPermiteProcessoFechado();
      $objTarefaDTO->setNumIdTarefa($numIdTarefa);
      
      $objTarefaRN = new TarefaRN();
      $objTarefaDTO = $objTarefaRN->consultar($objTarefaDTO); 
      
      $objUnidadeDTO = new UnidadeDTO();
      $objUnidadeDTO->setBolExclusaoLogica(false);
      $objUnidadeDTO->retStrSinProtocolo();
      $objUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      
      $objUnidadeRN = new UnidadeRN();
      $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);
      
      $bolFlagReaberturaAutomaticaProtocolo = false;
      if ($objUnidadeDTO->getStrSinProtocolo()=='S' &&
          $objAtividadeDTO->getNumIdUnidade() != SessaoSEI::getInstance()->getNumIdUnidadeAtual() &&
          $numIdTarefa == TarefaRN::$TI_REABERTURA_PROCESSO_UNIDADE){
        $bolFlagReaberturaAutomaticaProtocolo = true;
      }
      
      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->retStrStaNivelAcessoGlobal();
      $objProtocoloDTO->retStrProtocoloFormatado();
      $objProtocoloDTO->retStrStaEstado();
      $objProtocoloDTO->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
        
      $objProtocoloRN = new ProtocoloRN();
      $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

      if ($objProtocoloDTO==null){
       	throw new InfraException('Processo não encontrado.');
      }
        
      $strStaNivelAcessoGlobal = $objProtocoloDTO->getStrStaNivelAcessoGlobal();
      
     	//alterando nível de acesso
     	if ($numIdTarefa == TarefaRN::$TI_ALTERACAO_NIVEL_ACESSO_GLOBAL){
     	  if ($objAtividadeDTO->getNumIdUsuario()!=null){ //se alterando para sigiloso IdUsuario estará preenchido
     	    $objAtividadeDTO->setNumIdUsuarioAtribuicao($objAtividadeDTO->getNumIdUsuario()); 
     	  }    	  
     	}else{

       	//concedendo credencial, transferindo credencial ou concedendo credencial de assinatura
       	if ($strStaNivelAcessoGlobal == ProtocoloRN::$NA_SIGILOSO){
       	   if (in_array($numIdTarefa, TarefaRN::getArrTarefasConcessaoCredencial(true))){
             //atribui para o usuario "destino"    
             $objAtividadeDTO->setNumIdUsuarioAtribuicao($objAtividadeDTO->getNumIdUsuario());    
           }else if ($numIdTarefa == TarefaRN::$TI_GERACAO_PROCEDIMENTO || $numIdTarefa == TarefaRN::$TI_REABERTURA_PROCESSO_USUARIO){
             //atribui para o usuario atual
             $objAtividadeDTO->setNumIdUsuarioAtribuicao(SessaoSEI::getInstance()->getNumIdUsuario());
             $objAtividadeDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());   
           }else{
             
  	    		//verifica se o usuário atual tem acesso ao processo na unidade atual
  	    		//se tiver acesso então preenche o IdUsuario automaticamente
  	     		$objAcessoDTO = new AcessoDTO();
            $objAcessoDTO->retNumIdAcesso();
  	     		$objAcessoDTO->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
  	     		$objAcessoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
  	     		$objAcessoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $objAcessoDTO->setNumMaxRegistrosRetorno(1);
  	      		
  	     		$objAcessoRN = new AcessoRN();
  	      		
  	     		if ($objAcessoRN->consultar($objAcessoDTO) != null){
  	     			$objAtividadeDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
  	     		}else{
  	     			$objAtividadeDTO->setNumIdUsuario(null);
  	     		}
          }
       	}else{
       	  
       	  $objAtividadeDTO->setNumIdUsuario(null);
       	  
       	  if (SessaoSEI::getInstance()->isBolHabilitada()){
       	    
       	    if ($bolFlagReaberturaAutomaticaProtocolo || $numIdTarefa == TarefaRN::$TI_PROCESSO_REMETIDO_UNIDADE){

       	      //atribui para a última pessoa que trabalhou com o processo na unidade
       	      $dto = new AtividadeDTO();
       	      $dto->retNumIdUsuarioAtribuicao();
       	      $dto->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
       	      $dto->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade());

							//se remetendo verifica usuario de atribuicao apenas se o processo ja esta aberto na unidade
							if ($numIdTarefa == TarefaRN::$TI_PROCESSO_REMETIDO_UNIDADE){
								$dto->setDthConclusao(null);
							}

       	      $dto->setNumMaxRegistrosRetorno(1);
       	      $dto->setOrdNumIdAtividade(InfraDTO::$TIPO_ORDENACAO_DESC);

       	      $dto = $this->consultarRN0033($dto);
       	      if ($dto!=null){
       	        $objAtividadeDTO->setNumIdUsuarioAtribuicao($dto->getNumIdUsuarioAtribuicao());
       	      }
  
       	    }else if (/*$numIdTarefa == TarefaRN::$TI_GERACAO_PROCEDIMENTO ||*/ $numIdTarefa == TarefaRN::$TI_REABERTURA_PROCESSO_UNIDADE){
       	      $objAtividadeDTO->setNumIdUsuarioAtribuicao(SessaoSEI::getInstance()->getNumIdUsuario());
       	    }
       	  }
        }
     	}
     	
      $strDataHoraAtual = InfraData::getStrDataHoraAtual();

			$objAtividadeBD = new AtividadeBD($this->getObjInfraIBanco());

      $objAtividadeDTO->setDthAbertura($strDataHoraAtual);
      $objAtividadeDTO->setNumIdUnidadeOrigem(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      $objAtividadeDTO->setNumIdUsuarioOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
      $objAtividadeDTO->setNumTipoVisualizacao(self::$TV_VISUALIZADO);

      $arrObjAtividadeDTO = array();
      
     	if (!$bolFlagReaberturaAutomaticaProtocolo && $objTarefaDTO->getStrSinFecharAndamentosAbertos()=='S'){

				$dto = new AtividadeDTO();
				$dto->retNumIdAtividade();
				$dto->retNumIdTarefa();
				$dto->retNumIdUsuarioVisualizacao();
				$dto->retNumIdUsuarioAtribuicao();
				$dto->retNumTipoVisualizacao();
				$dto->retStrSinInicial();
				$dto->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
				$dto->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade());

				if ($strStaNivelAcessoGlobal == ProtocoloRN::$NA_SIGILOSO){
					$dto->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
				}

				$dto->setDthConclusao(null);
				$dto->setOrdNumIdAtividade(InfraDTO::$TIPO_ORDENACAO_DESC);

				$arrObjAtividadeDTO = $this->listarRN0036($dto);
     	}
     	
      $bolFlagConcluiu = false;
     		
      //se tem andamentos em aberto
      if (count($arrObjAtividadeDTO)){
      	
      	$n = 0;
				$bolFlagReceber = false;

	      foreach($arrObjAtividadeDTO as $dto){

          if ($dto->getStrSinInicial()=='N'){
            $n++;
          }

	      	//copia usuário que visualizou e o respectivo status de visualização
	      	if ($dto->getNumIdUsuarioVisualizacao()!=null &&
							$objAtividadeDTO->getNumIdUsuarioVisualizacao()==null){ //nao foi configurado na chamada
	      		$objAtividadeDTO->setNumIdUsuarioVisualizacao($dto->getNumIdUsuarioVisualizacao());
	      		$objAtividadeDTO->setNumTipoVisualizacao($dto->getNumTipoVisualizacao());
	      	}
	      	 
	      	//copia usuário de atribuição
	      	if ($dto->getNumIdUsuarioAtribuicao()!=null && //último andamento tem atribuição
	      	    $objAtividadeDTO->getNumIdUsuarioAtribuicao()==null && //nao foi configurado na chamada
	      	    $numIdTarefa != TarefaRN::$TI_REMOCAO_ATRIBUICAO){  //removendo atribuicao manualmente
	      		$objAtividadeDTO->setNumIdUsuarioAtribuicao($dto->getNumIdUsuarioAtribuicao());
	      	}


					if ($strStaNivelAcessoGlobal == ProtocoloRN::$NA_SIGILOSO){
						if ($numIdTarefa != TarefaRN::$TI_PROCESSO_RECEBIMENTO_CREDENCIAL && in_array($dto->getNumIdTarefa(), TarefaRN::getArrTarefasConcessaoCredencial(false))) {
							$bolFlagReceber = true;
						}
					}else{
						if ($numIdTarefa != TarefaRN::$TI_PROCESSO_RECEBIDO_UNIDADE && $dto->getNumIdTarefa() == TarefaRN::$TI_PROCESSO_REMETIDO_UNIDADE) {
							$bolFlagReceber = true;
						}
					}

	      }

        //se nao possui andamento fora da unidade
        if ($n == 0){
          $objAtividadeDTO->setStrSinInicial('S');
        }

				//lançar recebimento automático do processo
			  if ($bolFlagReceber) {
					$dto = clone($objAtividadeDTO);
					$dto->setNumIdTarefa(($strStaNivelAcessoGlobal == ProtocoloRN::$NA_SIGILOSO) ? TarefaRN::$TI_PROCESSO_RECEBIMENTO_CREDENCIAL : TarefaRN::$TI_PROCESSO_RECEBIDO_UNIDADE);
					$arrObjAtividadeDTO[] = $objAtividadeBD->cadastrar($dto);
				}

	      $this->concluirRN0726($arrObjAtividadeDTO);

	      $bolFlagConcluiu = true;

	    //quando reabrindo não tinha andamentos abertos e pode não ter tramitado
	    //a verificação evita que na reabertura de um processo gerado que não tramitou ele 
	    //fique na coluna de recebidos  
      }else if ($numIdTarefa == TarefaRN::$TI_REABERTURA_PROCESSO_UNIDADE ||
                $numIdTarefa == TarefaRN::$TI_REABERTURA_PROCESSO_USUARIO){
      	
      	  //verifica se o processo não tramitou fora da unidade
      		$dto = new AtividadeDTO();
          $dto->retNumIdAtividade();
      		$dto->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      		$dto->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
      		$dto->setStrSinInicial('N');
          $dto->setNumMaxRegistrosRetorno(1);
      		
      		if ($this->consultarRN0033($dto) == null){
      			$objAtividadeDTO->setStrSinInicial('S');
      		}
      }
      
	    //Lança andamento inicial:
	    //- quando reabrindo automaticamente devido ao protocolo
	    //- quando o processo esta sendo remetido para outra unidade
	    //- quando esta sendo dada credencial de acesso ao processo para alguem em outra unidade
	    //- quando esta sendo transferida credencial de acesso de ao processo na mesma unidade
	    //- quanto esta sendo dada credencial de assinatura para alguem em outra unidade
	    
      if ($bolFlagReaberturaAutomaticaProtocolo ||
          $numIdTarefa == TarefaRN::$TI_PROCESSO_REMETIDO_UNIDADE ||
          in_array($numIdTarefa, TarefaRN::getArrTarefasConcessaoCredencial(true))){

        $objAtividadeDTO->setNumIdUsuarioVisualizacao(null);
        $objAtividadeDTO->setNumIdUsuarioConclusao(null);
        $objAtividadeDTO->setDthConclusao(null);              	
        $objAtividadeDTO->setStrSinInicial('N');
        $objAtividadeDTO->setNumTipoVisualizacao(self::$TV_NAO_VISUALIZADO);

				//concluir atividades iniciais do processo (se existirem)
				$dto = new AtividadeDTO();
				$dto->retNumIdAtividade();
				$dto->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
				$dto->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade());
				$dto->setNumIdUsuario($objAtividadeDTO->getNumIdUsuario());
				$dto->setDthConclusao(null);

				$arrObjAtividadeDTOIniciais = $this->listarRN0036($dto);

				foreach($arrObjAtividadeDTOIniciais as $dto){
					$dto->setDthConclusao($strDataHoraAtual);
					$objAtividadeBD->alterar($dto);
				}

      }else if ($objTarefaDTO->getStrSinLancarAndamentoFechado()=='S'
                 ||
                 (!$bolFlagConcluiu && //não estava com o processo aberto na unidade
                 
		                ($objTarefaDTO->getStrSinPermiteProcessoFechado()=='S'

                     ||
                     
                     //incluindo documento ou recebendo documento externo em processo por web-services
                     (!SessaoSEI::getInstance()->isBolHabilitada() && 
                       $objProtocoloDTO->getStrStaEstado()==ProtocoloRN::$TE_NORMAL &&
                      ($numIdTarefa==TarefaRN::$TI_GERACAO_DOCUMENTO || 
                       $numIdTarefa==TarefaRN::$TI_RECEBIMENTO_DOCUMENTO || 
                       $numIdTarefa==TarefaRN::$TI_ARQUIVO_ANEXADO))   
		                 
                     ||
                     
                     //unidade PROTOCOLO pode lançar andamentos em processos que não estão abertos com ela, exceto nos casos onde a unidade PROTOCOLO 
                     //esteja realmente gerando ou reabrindo um processo ou alguma unidade esteja remetendo o processo para o PROTOCOLO
		                ($objUnidadeDTO->getStrSinProtocolo()=='S' &&
		                 $numIdTarefa != TarefaRN::$TI_GERACAO_PROCEDIMENTO &&
		                 $numIdTarefa != TarefaRN::$TI_REABERTURA_PROCESSO_UNIDADE &&  
		                 $numIdTarefa != TarefaRN::$TI_REABERTURA_PROCESSO_USUARIO &&
		                 $numIdTarefa != TarefaRN::$TI_PROCESSO_REMETIDO_UNIDADE &&
                     !in_array($numIdTarefa, TarefaRN::getArrTarefasConcessaoCredencial(true)))

                     ||

                     ($objProtocoloDTO->getStrStaEstado()==ProtocoloRN::$TE_PROCEDIMENTO_ANEXADO &&
                         ($numIdTarefa == TarefaRN::$TI_CANCELAMENTO_DOCUMENTO ||
                          $numIdTarefa == TarefaRN::$TI_GERACAO_DOCUMENTO ||
                          $numIdTarefa == TarefaRN::$TI_ARQUIVO_ANEXADO ||
                          $numIdTarefa == TarefaRN::$TI_ENVIO_EMAIL ||
                          $numIdTarefa == TarefaRN::$TI_ALTERACAO_NIVEL_ACESSO_GLOBAL ||
                          $numIdTarefa == TarefaRN::$TI_ALTERACAO_NIVEL_ACESSO_PROCESSO ||
                          $numIdTarefa == TarefaRN::$TI_ALTERACAO_NIVEL_ACESSO_DOCUMENTO ||
                          $numIdTarefa == TarefaRN::$TI_ALTERACAO_HIPOTESE_LEGAL_PROCESSO ||
                          $numIdTarefa == TarefaRN::$TI_ALTERACAO_HIPOTESE_LEGAL_DOCUMENTO ||
                          $numIdTarefa == TarefaRN::$TI_ALTERACAO_TIPO_CONFERENCIA_DOCUMENTO))
                      ||

                     $this->permitirAndamentoConcluidoModulos($objAtividadeDTO)
                    )
                 )
              ){

				//lança andamento fechado		                 	
        $objAtividadeDTO->setNumIdUsuarioConclusao(SessaoSEI::getInstance()->getNumIdUsuario());
        $objAtividadeDTO->setDthConclusao($strDataHoraAtual);
        
      }else {
      	
      	if (!$bolFlagConcluiu && 
      	    $numIdTarefa != TarefaRN::$TI_GERACAO_PROCEDIMENTO &&
      	    $numIdTarefa != TarefaRN::$TI_REABERTURA_PROCESSO_UNIDADE &&
      	    $numIdTarefa != TarefaRN::$TI_REABERTURA_PROCESSO_USUARIO &&
      	    $numIdTarefa != TarefaRN::$TI_CANCELAMENTO_AGENDAMENTO &&
      	    $numIdTarefa != TarefaRN::$TI_REMOCAO_SOBRESTANDO_PROCESSO){

          //throw new InfraException('Processo '.$objProtocoloDTO->getStrProtocoloFormatado().' não possui andamento aberto na unidade '.SessaoSEI::getInstance()->getStrSiglaUnidadeAtual().' ['.$numIdTarefa.'].');
          $objInfraException->lancarValidacao('Processo ' . $objProtocoloDTO->getStrProtocoloFormatado() . ' não possui andamento aberto na unidade ' . SessaoSEI::getInstance()->getStrSiglaUnidadeAtual() . '.');
        }
      	      	      	
      	//lança andamento em aberto mas não altera outros dados como usuário de visualização e atribuição
        $objAtividadeDTO->setNumIdUsuarioConclusao(null);
        $objAtividadeDTO->setDthConclusao(null);              	
      }

      $ret = $objAtividadeBD->cadastrar($objAtividadeDTO);

      //lança ícone de atenção para o processo em todas as unidades que possuam andamento aberto e já tenham visualizado
      if ($numIdTarefa == TarefaRN::$TI_ASSINATURA_DOCUMENTO || $numIdTarefa == TarefaRN::$TI_RECEBIMENTO_DOCUMENTO || $numIdTarefa == TarefaRN::$TI_DOCUMENTO_MOVIDO_DO_PROCESSO){

        $dto = new AtividadeDTO();
        $dto->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
         
        if ($strStaNivelAcessoGlobal==ProtocoloRN::$NA_SIGILOSO){
          $dto->setNumIdUsuario($objAtividadeDTO->getNumIdUsuario()); //em todos menos no atual
        }else{
          $dto->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade()); //em todas menos na atual
        }
         
        $dto->setNumTipoVisualizacao(self::$TV_ATENCAO);
        $this->atualizarVisualizacao($dto);
          
      }else if ($bolFlagReaberturaAutomaticaProtocolo){
      
        $dto = new AtividadeDTO();
        $dto->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
        $dto->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade());
        $dto->setNumTipoVisualizacao(self::$TV_ATENCAO);
        $this->atualizarVisualizacaoUnidade($dto);
          
      }else if ($numIdTarefa == TarefaRN::$TI_REMOCAO_SOBRESTAMENTO){

        //atualiza atividade de sobrestamento se existir em aberto
        $dto = new AtividadeDTO();
        $dto->retNumIdAtividade();
        $dto->retNumIdUnidade();
        $dto->retNumTipoVisualizacao();
        $dto->setNumIdTarefa(TarefaRN::$TI_SOBRESTAMENTO);
        $dto->setDthConclusao(null);
        $dto->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
    
        $dto = $this->consultarRN0033($dto);
         
        if ($dto != null){
          $dto->setNumTipoVisualizacao($dto->getNumTipoVisualizacao() | self::$TV_REMOCAO_SOBRESTAMENTO);
          $objAtividadeBD->alterar($dto);
        }
        
      }
      
      if (SessaoSEI::getInstance()->getNumIdUsuarioEmulador()!=null){

      	if ($objAtividadeDTO->isSetArrObjAtributoAndamentoDTO()){
      		$arrObjAtributoAndamentoDTO = $objAtividadeDTO->getArrObjAtributoAndamentoDTO();
      	}else{
      	  $arrObjAtributoAndamentoDTO = array();	
      	}
      	
        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('USUARIO_EMULADOR');
        $objAtributoAndamentoDTO->setStrValor(SessaoSEI::getInstance()->getStrSiglaUsuarioEmulador().'¥'.SessaoSEI::getInstance()->getStrNomeUsuarioEmulador().'±'.SessaoSEI::getInstance()->getStrSiglaOrgaoUsuarioEmulador().'¥'.SessaoSEI::getInstance()->getStrDescricaoOrgaoUsuarioEmulador());
        $objAtributoAndamentoDTO->setStrIdOrigem(SessaoSEI::getInstance()->getNumIdUsuarioEmulador().'/'.SessaoSEI::getInstance()->getNumIdOrgaoUsuarioEmulador());
        $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;
        
        $objAtividadeDTO->setArrObjAtributoAndamentoDTO($arrObjAtributoAndamentoDTO);
      }
      
      if ($objAtividadeDTO->isSetArrObjAtributoAndamentoDTO()){
        $objAtributoAndamentoRN = new AtributoAndamentoRN();
        $arrObjAtributoAndamentoDTO = $objAtividadeDTO->getArrObjAtributoAndamentoDTO();
        foreach($arrObjAtributoAndamentoDTO as $objAtributoAndamentoDTO){
          $objAtributoAndamentoDTO->setNumIdAtividade($ret->getNumIdAtividade());
          $objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);
        }
      }
     
      return $ret;

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando andamento do SEI.',$e);
    }
  }
  
  protected function validarStrSinConluirOriginaisRN0826(EnviarProcessoDTO $objEnviarProcessoDTO, InfraException $objInfraException){
		if (InfraString::isBolVazia($objEnviarProcessoDTO->getStrSinManterAberto())){
			$objInfraException->adicionarValidacao('Sinalizador de manutenção de processo aberto na unidade não informado.');
		}else{
			if (!InfraUtil::isBolSinalizadorValido($objEnviarProcessoDTO->getStrSinManterAberto())){
				$objInfraException->adicionarValidacao('Sinalizador de manutenção de processo aberto na unidade inválido.');
			}else{
				if ($objEnviarProcessoDTO->getStrSinManterAberto()=='N'){
					if (!$objEnviarProcessoDTO->isSetArrAtividadesOrigem()){
						$objInfraException->adicionarValidacao('Conjunto de andamentos originais não informado.');
					}
					if (!is_array($objEnviarProcessoDTO->getArrAtividadesOrigem())){
						$objInfraException->adicionarValidacao('Conjunto de andamentos originais inválido.');
					}
				}
			}
		}
  }

  protected function validarStrSinRemoverAnotacoes(EnviarProcessoDTO $objEnviarProcessoDTO, InfraException $objInfraException){
		if (InfraString::isBolVazia($objEnviarProcessoDTO->getStrSinRemoverAnotacoes())){
			$objInfraException->adicionarValidacao('Sinalizador de remoção de anotações não informado.');
		}else{
			if (!InfraUtil::isBolSinalizadorValido($objEnviarProcessoDTO->getStrSinRemoverAnotacoes())){
				$objInfraException->adicionarValidacao('Sinalizador de remoção de anotações inválido.');
			}
		}
  }

  protected function validarStrSinEnviarEmailNotificacao(EnviarProcessoDTO $objEnviarProcessoDTO, InfraException $objInfraException){
		if (InfraString::isBolVazia($objEnviarProcessoDTO->getStrSinEnviarEmailNotificacao())){
			$objInfraException->adicionarValidacao('Sinalizador de envio de e-mail de notificação não informado.');
		}else{
			if (!InfraUtil::isBolSinalizadorValido($objEnviarProcessoDTO->getStrSinEnviarEmailNotificacao())){
				$objInfraException->adicionarValidacao('Sinalizador de envio de e-mail de notificação inválido.');
			}
		}
  }

	protected function validarStrSinDiasUteis(EnviarProcessoDTO $objEnviarProcessoDTO, InfraException $objInfraException){
		if (InfraString::isBolVazia($objEnviarProcessoDTO->getStrSinDiasUteis())){
			$objInfraException->adicionarValidacao('Sinalizador de dias úteis não informado.');
		}else{
			if (!InfraUtil::isBolSinalizadorValido($objEnviarProcessoDTO->getStrSinDiasUteis())){
				$objInfraException->lancarValidacao('Sinalizador de dias úteis inválido.');
			}
		}
	}

	private function validarDtaPrazo(EnviarProcessoDTO $objEnviarProcessoDTO, InfraException $objInfraException){
		if (InfraString::isBolVazia($objEnviarProcessoDTO->getDtaPrazo())){
			$objEnviarProcessoDTO->setDtaPrazo(null);
		}else{
			if (!InfraData::validarData($objEnviarProcessoDTO->getDtaPrazo())){
				$objInfraException->adicionarValidacao('Data de retorno programado inválida.');
			}

			if (InfraData::compararDatas(InfraData::getStrDataAtual(),$objEnviarProcessoDTO->getDtaPrazo())<0){
				$objInfraException->adicionarValidacao('Data de retorno programado não pode estar no passado.');
			}
		}
	}

	private function validarNumDias(EnviarProcessoDTO $objEnviarProcessoDTO, InfraException $objInfraException){
		if (InfraString::isBolVazia($objEnviarProcessoDTO->getNumDias())){
			$objEnviarProcessoDTO->setNumDias(null);
		}else{

			$objEnviarProcessoDTO->setNumDias(trim($objEnviarProcessoDTO->getNumDias()));

			if (!is_numeric($objEnviarProcessoDTO->getNumDias()) ||	$objEnviarProcessoDTO->getNumDias() < 1){
				$objInfraException->adicionarValidacao('Número de dias para retorno programado inválido.');
			}
		}
	}

  protected function listarPendenciasRN0754Conectado(PesquisaPendenciaDTO $objPesquisaPendenciaDTO){

    try {
      //if (!$objPesquisaPendenciaDTO->isSetStrStaEstadoProcedimento()) {
      // $objPesquisaPendenciaDTO->setStrStaEstadoProcedimento(ProtocoloRN::$TE_NORMAL);
      //}

      if (!$objPesquisaPendenciaDTO->isSetStrStaTipoAtribuicao()) {
        $objPesquisaPendenciaDTO->setStrStaTipoAtribuicao(self::$TA_TODAS);
      }

      if (!$objPesquisaPendenciaDTO->isSetNumIdUsuarioAtribuicao()) {
        $objPesquisaPendenciaDTO->setNumIdUsuarioAtribuicao(null);
      }

      if (!$objPesquisaPendenciaDTO->isSetStrSinMontandoArvore()) {
        $objPesquisaPendenciaDTO->setStrSinMontandoArvore('N');
      }

      if (!$objPesquisaPendenciaDTO->isSetStrSinAnotacoes()) {
        $objPesquisaPendenciaDTO->setStrSinAnotacoes('N');
      }

      if (!$objPesquisaPendenciaDTO->isSetStrSinSituacoes()) {
        $objPesquisaPendenciaDTO->setStrSinSituacoes('N');
      }

      if (!$objPesquisaPendenciaDTO->isSetStrSinMarcadores()) {
        $objPesquisaPendenciaDTO->setStrSinMarcadores('N');
      }

      if (!$objPesquisaPendenciaDTO->isSetStrSinInteressados()) {
        $objPesquisaPendenciaDTO->setStrSinInteressados('N');
      }

      if (!$objPesquisaPendenciaDTO->isSetStrSinRetornoProgramado()) {
        $objPesquisaPendenciaDTO->setStrSinRetornoProgramado('N');
      }

      if (!$objPesquisaPendenciaDTO->isSetStrSinCredenciais()) {
        $objPesquisaPendenciaDTO->setStrSinCredenciais('N');
      }

      if (!$objPesquisaPendenciaDTO->isSetStrSinHoje()) {
        $objPesquisaPendenciaDTO->setStrSinHoje('N');
      }


      $objAtividadeDTO = new AtividadeDTO();
      $objAtividadeDTO->retNumIdAtividade();
      $objAtividadeDTO->retNumIdTarefa();
      $objAtividadeDTO->retNumIdUsuarioAtribuicao();
      $objAtividadeDTO->retNumIdUsuarioVisualizacao();
      $objAtividadeDTO->retNumTipoVisualizacao();
      $objAtividadeDTO->retNumIdUnidade();
      $objAtividadeDTO->retDthConclusao();
      $objAtividadeDTO->retDblIdProtocolo();
      $objAtividadeDTO->retStrSiglaUnidade();
      $objAtividadeDTO->retStrSinInicial();
      $objAtividadeDTO->retNumIdUsuarioAtribuicao();
      $objAtividadeDTO->retStrSiglaUsuarioAtribuicao();
      $objAtividadeDTO->retStrNomeUsuarioAtribuicao();

      $objAtividadeDTO->setNumIdUnidade($objPesquisaPendenciaDTO->getNumIdUnidade());

      if ($objPesquisaPendenciaDTO->getStrSinHoje() == 'N') {
        $objAtividadeDTO->setDthConclusao(null);
      } else {
        $objAtividadeDTO->adicionarCriterio(array('Conclusao', 'Conclusao'),
            array(InfraDTO::$OPER_IGUAL, InfraDTO::$OPER_MAIOR_IGUAL),
            array(null, InfraData::getStrDataAtual() . ' 00:00:00'),
            array(InfraDTO::$OPER_LOGICO_OR));
      }

      $objAtividadeDTO->setStrStaProtocoloProtocolo(ProtocoloRN::$TP_PROCEDIMENTO);

      if ($objPesquisaPendenciaDTO->getNumIdUsuario() == null) {
        $objAtividadeDTO->setStrStaNivelAcessoGlobalProtocolo(ProtocoloRN::$NA_SIGILOSO, InfraDTO::$OPER_DIFERENTE);
      } else {
        $objAtividadeDTO->adicionarCriterio(array('StaNivelAcessoGlobalProtocolo','IdUsuario'),
                                            array(InfraDTO::$OPER_DIFERENTE, InfraDTO::$OPER_IGUAL),
                                            array(ProtocoloRN::$NA_SIGILOSO, $objPesquisaPendenciaDTO->getNumIdUsuario()),
                                            array(InfraDTO::$OPER_LOGICO_OR));
      }

      if ($objPesquisaPendenciaDTO->getStrStaTipoAtribuicao() == self::$TA_MINHAS) {
        $objAtividadeDTO->setNumIdUsuarioAtribuicao($objPesquisaPendenciaDTO->getNumIdUsuario());
      } else if ($objPesquisaPendenciaDTO->getStrStaTipoAtribuicao() == self::$TA_DEFINIDAS) {
        $objAtividadeDTO->setNumIdUsuarioAtribuicao(null, InfraDTO::$OPER_DIFERENTE);
      } else if ($objPesquisaPendenciaDTO->getStrStaTipoAtribuicao() == self::$TA_ESPECIFICAS) {
        $objAtividadeDTO->setNumIdUsuarioAtribuicao($objPesquisaPendenciaDTO->getNumIdUsuarioAtribuicao());
      }

      if ($objPesquisaPendenciaDTO->isSetDblIdProtocolo()) {
        if (!is_array($objPesquisaPendenciaDTO->getDblIdProtocolo())) {
          $objAtividadeDTO->setDblIdProtocolo($objPesquisaPendenciaDTO->getDblIdProtocolo());
        } else {
          $objAtividadeDTO->setDblIdProtocolo($objPesquisaPendenciaDTO->getDblIdProtocolo(), InfraDTO::$OPER_IN);
        }
      }

      if ($objPesquisaPendenciaDTO->isSetStrStaEstadoProcedimento()) {
        if (is_array($objPesquisaPendenciaDTO->getStrStaEstadoProcedimento())) {
          $objAtividadeDTO->setStrStaEstadoProtocolo($objPesquisaPendenciaDTO->getStrStaEstadoProcedimento(), InfraDTO::$OPER_IN);
        } else {
          $objAtividadeDTO->setStrStaEstadoProtocolo($objPesquisaPendenciaDTO->getStrStaEstadoProcedimento());
        }
      }

      if ($objPesquisaPendenciaDTO->isSetStrSinInicial()) {
        $objAtividadeDTO->setStrSinInicial($objPesquisaPendenciaDTO->getStrSinInicial());
      }

      if ($objPesquisaPendenciaDTO->isSetNumIdMarcador()) {
        $objAtividadeDTO->setNumTipoFkAndamentoMarcador(InfraDTO::$TIPO_FK_OBRIGATORIA);
        $objAtividadeDTO->setNumIdMarcador($objPesquisaPendenciaDTO->getNumIdMarcador());
        $objAtividadeDTO->setStrSinUltimoAndamentoMarcador('S');
      }

      //ordenar pela data de abertura descendente
      //$objAtividadeDTO->setOrdDthAbertura(InfraDTO::$TIPO_ORDENACAO_DESC);
      $objAtividadeDTO->setOrdNumIdAtividade(InfraDTO::$TIPO_ORDENACAO_DESC);


      //paginação
      $objAtividadeDTO->setNumMaxRegistrosRetorno($objPesquisaPendenciaDTO->getNumMaxRegistrosRetorno());
      $objAtividadeDTO->setNumPaginaAtual($objPesquisaPendenciaDTO->getNumPaginaAtual());

      $arrAtividadeDTO = $this->listarRN0036($objAtividadeDTO);

      //paginação
      $objPesquisaPendenciaDTO->setNumTotalRegistros($objAtividadeDTO->getNumTotalRegistros());
      $objPesquisaPendenciaDTO->setNumRegistrosPaginaAtual($objAtividadeDTO->getNumRegistrosPaginaAtual());

      $arrProcedimentos = array();

      //Se encontrou pelo menos um registro
      if (count($arrAtividadeDTO) > 0) {

        $objProcedimentoDTO = new ProcedimentoDTO();

        //$objProcedimentoDTO->retDblIdProcedimento();
        //$objProcedimentoDTO->retStrProtocoloProcedimentoFormatado();
        //$objProcedimentoDTO->retStrNomeTipoProcedimento();
        //$objProcedimentoDTO->retNumIdUnidadeGeradoraProtocolo();
        //$objProcedimentoDTO->retStrStaEstadoProtocolo();
        $objProcedimentoDTO->retStrDescricaoProtocolo();

        $arrProtocolosAtividades = array_unique(InfraArray::converterArrInfraDTO($arrAtividadeDTO, 'IdProtocolo'));
        $objProcedimentoDTO->setDblIdProcedimento($arrProtocolosAtividades, InfraDTO::$OPER_IN);

        if ($objPesquisaPendenciaDTO->getStrSinMontandoArvore() == 'S') {
          $objProcedimentoDTO->setStrSinMontandoArvore('S');
        }

        if ($objPesquisaPendenciaDTO->getStrSinAnotacoes() == 'S') {
          $objProcedimentoDTO->setStrSinAnotacoes('S');
        }

        if ($objPesquisaPendenciaDTO->getStrSinSituacoes() == 'S') {
          $objProcedimentoDTO->setStrSinSituacoes('S');
        }

        if ($objPesquisaPendenciaDTO->getStrSinMarcadores() == 'S') {
          $objProcedimentoDTO->setStrSinMarcadores('S');
        }

        if ($objPesquisaPendenciaDTO->isSetDblIdDocumento()) {
          $objProcedimentoDTO->setArrDblIdProtocoloAssociado(array($objPesquisaPendenciaDTO->getDblIdDocumento()));
        }

        $objProcedimentoRN = new ProcedimentoRN();

        $arr = $objProcedimentoRN->listarCompleto($objProcedimentoDTO);

        $arrObjParticipanteDTO = null;
        if ($objPesquisaPendenciaDTO->getStrSinInteressados() == 'S') {

          $arrObjParticipanteDTO = array();

          $objParticipanteDTO = new ParticipanteDTO();
          $objParticipanteDTO->retDblIdProtocolo();
          $objParticipanteDTO->retStrSiglaContato();
          $objParticipanteDTO->retStrNomeContato();
          $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_INTERESSADO);
          $objParticipanteDTO->setDblIdProtocolo($arrProtocolosAtividades, InfraDTO::$OPER_IN);

          $objParticipanteRN = new ParticipanteRN();
          $arrTemp = $objParticipanteRN->listarRN0189($objParticipanteDTO);

          foreach ($arrTemp as $objParticipanteDTO) {
            if (!isset($arrObjParticipanteDTO[$objParticipanteDTO->getDblIdProtocolo()])) {
              $arrObjParticipanteDTO[$objParticipanteDTO->getDblIdProtocolo()] = array($objParticipanteDTO);
            } else {
              $arrObjParticipanteDTO[$objParticipanteDTO->getDblIdProtocolo()][] = $objParticipanteDTO;
            }
          }
        }

        $arrObjRetornoProgramadoDTO = null;
        if ($objPesquisaPendenciaDTO->getStrSinRetornoProgramado() == 'S') {
          $objRetornoProgramadoDTO = new RetornoProgramadoDTO();
          $objRetornoProgramadoDTO->retDblIdProtocoloAtividadeEnvio();
          $objRetornoProgramadoDTO->retStrSiglaUnidadeOrigemAtividadeEnvio();
          $objRetornoProgramadoDTO->retDtaProgramada();
          $objRetornoProgramadoDTO->setNumIdUnidadeAtividadeEnvio($objPesquisaPendenciaDTO->getNumIdUnidade());
          $objRetornoProgramadoDTO->setDblIdProtocoloAtividadeEnvio($arrProtocolosAtividades, InfraDTO::$OPER_IN);
          $objRetornoProgramadoDTO->setNumIdAtividadeRetorno(null);

          $objRetornoProgramadoRN = new RetornoProgramadoRN();
          $arrObjRetornoProgramadoDTO = InfraArray::indexarArrInfraDTO($objRetornoProgramadoRN->listar($objRetornoProgramadoDTO), 'IdProtocoloAtividadeEnvio', true);
        }


        //Manter ordem obtida na listagem das atividades
        $arrAdicionados = array();
        $arrIdProcedimentoSigiloso = array();

        $arr = InfraArray::indexarArrInfraDTO($arr, 'IdProcedimento');

        foreach ($arrAtividadeDTO as $objAtividadeDTO) {

          $objProcedimentoDTO = $arr[$objAtividadeDTO->getDblIdProtocolo()];

          //pode não existir se o procedimento foi excluído
          if ($objProcedimentoDTO != null) {

            $dblIdProcedimento = $objProcedimentoDTO->getDblIdProcedimento();

            if ($objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_SIGILOSO) {

              $objProcedimentoDTO->setStrSinCredencialProcesso('N');
              $objProcedimentoDTO->setStrSinCredencialAssinatura('N');

              $arrIdProcedimentoSigiloso[] = $dblIdProcedimento;
            }

            if (!isset($arrAdicionados[$dblIdProcedimento])) {

              $objProcedimentoDTO->setArrObjAtividadeDTO(array($objAtividadeDTO));

              if (is_array($arrObjParticipanteDTO)) {
                if (isset($arrObjParticipanteDTO[$dblIdProcedimento])) {
                  $objProcedimentoDTO->setArrObjParticipanteDTO($arrObjParticipanteDTO[$dblIdProcedimento]);
                } else {
                  $objProcedimentoDTO->setArrObjParticipanteDTO(null);
                }
              }

              if (is_array($arrObjRetornoProgramadoDTO)) {
                if (isset($arrObjRetornoProgramadoDTO[$dblIdProcedimento])) {
                  $objProcedimentoDTO->setArrObjRetornoProgramadoDTO($arrObjRetornoProgramadoDTO[$dblIdProcedimento]);
                } else {
                  $objProcedimentoDTO->setArrObjRetornoProgramadoDTO(null);
                }
              }

              $arrProcedimentos[] = $objProcedimentoDTO;
              $arrAdicionados[$dblIdProcedimento] = 0;
            } else {
              $arrAtividadeDTOProcedimento = $objProcedimentoDTO->getArrObjAtividadeDTO();
              $arrAtividadeDTOProcedimento[] = $objAtividadeDTO;
              $objProcedimentoDTO->setArrObjAtividadeDTO($arrAtividadeDTOProcedimento);
            }
          }
        }


        if ($objPesquisaPendenciaDTO->getStrSinCredenciais() == 'S' && count($arrIdProcedimentoSigiloso)) {

          $objAcessoDTO = new AcessoDTO();
          $objAcessoDTO->retDblIdProtocolo();
          $objAcessoDTO->retStrStaTipo();
          $objAcessoDTO->setNumIdUsuario($objPesquisaPendenciaDTO->getNumIdUsuario());
          $objAcessoDTO->setNumIdUnidade($objPesquisaPendenciaDTO->getNumIdUnidade());
          $objAcessoDTO->setStrStaTipo(array(AcessoRN::$TA_CREDENCIAL_PROCESSO, AcessoRN::$TA_CREDENCIAL_ASSINATURA_PROCESSO), InfraDTO::$OPER_IN);
          $objAcessoDTO->setDblIdProtocolo($arrIdProcedimentoSigiloso, InfraDTO::$OPER_IN);

          $objAcessoRN = new AcessoRN();
          $arrObjAcessoDTO = $objAcessoRN->listar($objAcessoDTO);

          /*
          foreach($arr as $objProcedimentoDTO){
            $objProcedimentoDTO->setStrSinCredencialProcesso('N');
            $objProcedimentoDTO->setStrSinCredencialAssinatura('N');
          }
          */

          foreach ($arrObjAcessoDTO as $objAcessoDTO) {
            if ($objAcessoDTO->getStrStaTipo() == AcessoRN::$TA_CREDENCIAL_PROCESSO) {
              $arr[$objAcessoDTO->getDblIdProtocolo()]->setStrSinCredencialProcesso('S');
            } else if ($objAcessoDTO->getStrStaTipo() == AcessoRN::$TA_CREDENCIAL_ASSINATURA_PROCESSO) {
              $arr[$objAcessoDTO->getDblIdProtocolo()]->setStrSinCredencialAssinatura('S');
            }
          }

        }
      }

      return $arrProcedimentos;

    }catch(Exception $e){
      throw new InfraException('Erro recuperando processos abertos.',$e);
    }
  }

  protected function atribuirRN0985Controlado(AtribuirDTO $objAtribuirDTO){
    try {

    	$objInfraException = new InfraException();

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('procedimento_atribuicao_cadastrar',__METHOD__,$objAtribuirDTO);

      if (InfraString::isBolVazia($objAtribuirDTO->getNumIdUsuarioAtribuicao())){
      	
      	foreach($objAtribuirDTO->getArrObjProtocoloDTO() as $objProtocoloDTO){
      		
		  		$objAtividadeDTO = new AtividadeDTO();
          $objAtividadeDTO->retNumIdAtividade();
		  		$objAtividadeDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
		  		$objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
		  		$objAtividadeDTO->setNumIdUsuarioAtribuicao(null,InfraDTO::$OPER_DIFERENTE);
      		$objAtividadeDTO->setDthConclusao(null);
          $objAtividadeDTO->setNumMaxRegistrosRetorno(1);
      		
      		if ($this->consultarRN0033($objAtividadeDTO) != null){
			  		$objAtividadeDTO = new AtividadeDTO();
			  		$objAtividadeDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
			  		$objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
			  		$objAtividadeDTO->setNumIdUsuarioAtribuicao(null);
			  		$objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_REMOCAO_ATRIBUICAO);
			  		
			  		$this->gerarInternaRN0727($objAtividadeDTO);
      		}
      	}
		  		
      }else{
      
	      $objUsuarioDTO = new UsuarioDTO();
	      $objUsuarioDTO->retNumIdUsuario();
	      $objUsuarioDTO->retStrNome();
	      $objUsuarioDTO->retStrSigla();
	      $objUsuarioDTO->setNumIdUsuario($objAtribuirDTO->getNumIdUsuarioAtribuicao());
	      
	      $objUsuarioRN	 = new UsuarioRN();
	      $objUsuarioDTO = $objUsuarioRN->consultarRN0489($objUsuarioDTO);

	      $objProtocoloDTO = new ProtocoloDTO();
	      $objProtocoloDTO->retDblIdProtocolo();
	      $objProtocoloDTO->retStrProtocoloFormatado();
	      $objProtocoloDTO->retStrStaNivelAcessoGlobal();
	      $objProtocoloDTO->setDblIdProtocolo(InfraArray::converterArrInfraDTO($objAtribuirDTO->getArrObjProtocoloDTO(),'IdProtocolo'),InfraDTO::$OPER_IN);
	      
	      $objProtocoloRN = new ProtocoloRN();
	      $arrObjProtocoloDTO = InfraArray::indexarArrInfraDTO($objProtocoloRN->listarRN0668($objProtocoloDTO),'IdProtocolo');

	      $numEnviados = count($objAtribuirDTO->getArrObjProtocoloDTO());
	      $numEncontrados = count($arrObjProtocoloDTO);
	      
	      if ($numEnviados==1 && $numEncontrados==0){
	        
          $objInfraException->lancarValidacao('Processo não encontrado para atribuição.');
          
	      }else if ($numEnviados>1){
	      
  	      if ($numEnviados != $numEncontrados){
  	        if ($numEncontrados==0){
  	          $objInfraException->lancarValidacao('Não foi possível realizar a atribuição pois nenhum processo da lista foi encontrado.');
  	        }else if (($numEnviados-$numEncontrados)==1){
  	          $objInfraException->lancarValidacao('Não foi possível realizar a atribuição pois um processo da lista não foi encontrado.');
  	        }else {
  	          $objInfraException->lancarValidacao('Não foi possível realizar a atribuição pois '.($numEnviados-$numEncontrados).' processos da lista não foram encontrados.');
  	        }
  	      }
	      }
	      
	      foreach($objAtribuirDTO->getArrObjProtocoloDTO() as $objProtocoloDTO){
	      	if ($arrObjProtocoloDTO[$objProtocoloDTO->getDblIdProtocolo()]->getStrStaNivelAcessoGlobal()==ProtocoloRN::$NA_SIGILOSO){
	      		$objInfraException->adicionarValidacao('Processo sigiloso '.$arrObjProtocoloDTO[$objProtocoloDTO->getDblIdProtocolo()]->getStrProtocoloFormatado().' não pode receber atribuição.');
		      }
	      }
	      
	      $objInfraException->lancarValidacoes();
	      
	      foreach($objAtribuirDTO->getArrObjProtocoloDTO() as $objProtocoloDTO){
	      	
		      $arrObjAtributoAndamentoDTO = array();
	        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
	        $objAtributoAndamentoDTO->setStrNome('USUARIO');
	        $objAtributoAndamentoDTO->setStrValor($objUsuarioDTO->getStrSigla().'¥'.$objUsuarioDTO->getStrNome());
	        $objAtributoAndamentoDTO->setStrIdOrigem($objUsuarioDTO->getNumIdUsuario());
	        $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;
		      
		  		$objAtividadeDTO = new AtividadeDTO();
		  		$objAtividadeDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
		  		$objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
		  		$objAtividadeDTO->setNumIdUsuarioAtribuicao($objUsuarioDTO->getNumIdUsuario());
		  		$objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_PROCESSO_ATRIBUIDO);
		  		$objAtividadeDTO->setArrObjAtributoAndamentoDTO($arrObjAtributoAndamentoDTO);
		  		
		  		$this->gerarInternaRN0727($objAtividadeDTO);
	       }
      }
      
      
      
      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro atribuindo processo.',$e);
    }
  }
  
  protected function atualizarVisualizacaoControlado(AtividadeDTO $parObjAtividadeDTO){
    try{


      $objAtividadeDTO = new AtividadeDTO();
      $objAtividadeDTO->retNumIdAtividade();
      $objAtividadeDTO->retNumTipoVisualizacao();
      $objAtividadeDTO->setDthConclusao(null);
      $objAtividadeDTO->setDblIdProtocolo($parObjAtividadeDTO->getDblIdProtocolo());

      //se algum usuário não deve ser atualizado
      if ($parObjAtividadeDTO->isSetNumIdUsuario() && !InfraString::isBolVazia($parObjAtividadeDTO->getNumIdUsuario())){
        $objAtividadeDTO->setNumIdUsuario($parObjAtividadeDTO->getNumIdUsuario(),InfraDTO::$OPER_DIFERENTE);
      }
      
      //se alguma unidade que não deve ser atualizada
      if ($parObjAtividadeDTO->isSetNumIdUnidade() && !InfraString::isBolVazia($parObjAtividadeDTO->getNumIdUnidade())){
        $objAtividadeDTO->setNumIdUnidade($parObjAtividadeDTO->getNumIdUnidade(),InfraDTO::$OPER_DIFERENTE);
      }
      
      $arrObjAtividadeDTO = $this->listarRN0036($objAtividadeDTO);
      
      $objAtividadeBD = new AtividadeBD($this->getObjInfraIBanco());
      
      foreach($arrObjAtividadeDTO as $objAtividadeDTO){
        $objAtividadeDTO->setNumTipoVisualizacao($objAtividadeDTO->getNumTipoVisualizacao() | $parObjAtividadeDTO->getNumTipoVisualizacao());
        $objAtividadeBD->alterar($objAtividadeDTO);
      }

      $objAcompanhamentoDTO = new AcompanhamentoDTO();
      $objAcompanhamentoDTO->setDblIdProtocolo($parObjAtividadeDTO->getDblIdProtocolo());

      if ($parObjAtividadeDTO->isSetNumIdUnidade() && !InfraString::isBolVazia($parObjAtividadeDTO->getNumIdUnidade())){
        $objAcompanhamentoDTO->setNumIdUnidade($parObjAtividadeDTO->getNumIdUnidade());
      }

      $objAcompanhamentoDTO->setNumTipoVisualizacao($parObjAtividadeDTO->getNumTipoVisualizacao());

      $objAcompanhamentoRN = new AcompanhamentoRN();
      $objAcompanhamentoRN->atualizarVisualizacao($objAcompanhamentoDTO);

            
    }catch(Exception $e){
      throw new InfraException('Erro atualizando visualização do andamento.',$e);
    }
  }

  protected function atualizarVisualizacaoUnidadeControlado(AtividadeDTO $parObjAtividadeDTO){
    try{
  
      $objAtividadeDTO = new AtividadeDTO();
      $objAtividadeDTO->retNumIdAtividade();
      $objAtividadeDTO->retNumTipoVisualizacao();
      $objAtividadeDTO->setDthConclusao(null);
      $objAtividadeDTO->setDblIdProtocolo($parObjAtividadeDTO->getDblIdProtocolo());
      $objAtividadeDTO->setNumIdUnidade($parObjAtividadeDTO->getNumIdUnidade());
      $arrObjAtividadeDTO = $this->listarRN0036($objAtividadeDTO);
  
      $objAtividadeBD = new AtividadeBD($this->getObjInfraIBanco());
  
      foreach($arrObjAtividadeDTO as $objAtividadeDTO){
        $objAtividadeDTO->setNumTipoVisualizacao($objAtividadeDTO->getNumTipoVisualizacao() | $parObjAtividadeDTO->getNumTipoVisualizacao());
        $objAtividadeBD->alterar($objAtividadeDTO);
      }

      $objAcompanhamentoDTO = new AcompanhamentoDTO();
      $objAcompanhamentoDTO->setDblIdProtocolo($parObjAtividadeDTO->getDblIdProtocolo());
      $objAcompanhamentoDTO->setNumIdUnidade($parObjAtividadeDTO->getNumIdUnidade());
      $objAcompanhamentoDTO->setNumTipoVisualizacao($parObjAtividadeDTO->getNumTipoVisualizacao());

      $objAcompanhamentoRN = new AcompanhamentoRN();
      $objAcompanhamentoRN->atualizarVisualizacaoUnidade($objAcompanhamentoDTO);

    }catch(Exception $e){
      throw new InfraException('Erro atualizando visualização do andamento na unidade.',$e);
    }
  }

  protected function alterarCondicaoGeradoRecebidoControlado(AtividadeDTO $parObjAtividadeDTO){
    try{

    	$objInfraException = new InfraException();
    	
    	$this->validarStrSinInicial($parObjAtividadeDTO, $objInfraException);
    	
    	$objInfraException->lancarValidacoes();
    	
      $objAtividadeDTO = new AtividadeDTO();
      $objAtividadeDTO->setStrSinInicial($parObjAtividadeDTO->getStrSinInicial());
      $objAtividadeDTO->setNumIdAtividade($parObjAtividadeDTO->getNumIdAtividade());
      
      $objAtividadeBD = new AtividadeBD($this->getObjInfraIBanco());
      $objAtividadeBD->alterar($objAtividadeDTO);
            
    }catch(Exception $e){
      throw new InfraException('Erro alterando condição de Gerado/Recebido.',$e);
    }
  }
  
  public function validarAndamentosAtuais($arrIdProtocolo, $arrIdAtividadesOrigem, InfraException $objInfraException){

		$objPesquisaPendenciaDTO = new PesquisaPendenciaDTO();
		$objPesquisaPendenciaDTO->setDblIdProtocolo($arrIdProtocolo);
		$objPesquisaPendenciaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
		$objPesquisaPendenciaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
		$arrObjProcedimentoDTO = InfraArray::indexarArrInfraDTO($this->listarPendenciasRN0754($objPesquisaPendenciaDTO),'IdProcedimento');

		$arrObjProtocoloDTO = null;

		foreach($arrIdProtocolo as $dblIdProtocolo){

			if (!isset($arrObjProcedimentoDTO[$dblIdProtocolo])){

				//só busca para o primeiro que não encontrar
				if ($arrObjProtocoloDTO==null){
					$objProtocoloDTO = new ProtocoloDTO();
					$objProtocoloDTO->retDblIdProtocolo();
					$objProtocoloDTO->retStrProtocoloFormatado();
					$objProtocoloDTO->setDblIdProtocolo($arrIdProtocolo,InfraDTO::$OPER_IN);

					$objProtocoloRN = new ProtocoloRN();
					$arrObjProtocoloDTO = InfraArray::indexarArrInfraDTO($objProtocoloRN->listarRN0668($objProtocoloDTO),'IdProtocolo');
				}

				if (!isset($arrObjProtocoloDTO[$dblIdProtocolo])){
					throw new InfraException('Processo não encontrado.');
				}

				$objInfraException->adicionarValidacao('Processo '.$arrObjProtocoloDTO[$dblIdProtocolo]->getStrProtocoloFormatado().' não possui andamento aberto na unidade '.SessaoSEI::getInstance()->getStrSiglaUnidadeAtual().'.');
			}
		}

		$objInfraException->lancarValidacoes();
  }
  
  protected function concederCredencialControlado(ConcederCredencialDTO $parObjConcederCredencialDTO) {

  	try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('procedimento_credencial_conceder',__METHOD__,$parObjConcederCredencialDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();
      
      //verifica se não houve mudança nas atividades abertas
      
      $arrObjAtividadeDTOOrigem = $parObjConcederCredencialDTO->getArrAtividadesOrigem();
      $arrIdAtividadesOrigem = InfraArray::converterArrInfraDTO($arrObjAtividadeDTOOrigem,'IdAtividade');
      
      //$arrObjAtividadeDTO = $parObjConcederCredencialDTO->getArrAtividades();
      //$arrIdProtocolosOrigem = array_unique(InfraArray::converterArrInfraDTO($arrObjAtividadeDTO,'IdProtocolo'));
      //$arrIdUnidades = array_unique(InfraArray::converterArrInfraDTO($arrObjAtividadeDTO,'IdUnidade'));
      
      $this->validarAndamentosAtuais(array($parObjConcederCredencialDTO->getDblIdProcedimento()), $arrIdAtividadesOrigem, $objInfraException);

			$objInfraException->lancarValidacoes();

      //recupera dados do processo
    	$objProtocoloDTO = new ProtocoloDTO();
    	$objProtocoloDTO->retDblIdProtocolo();
    	$objProtocoloDTO->retStrStaNivelAcessoGlobal();
    	$objProtocoloDTO->retStrProtocoloFormatado();
    	$objProtocoloDTO->retStrNomeTipoProcedimentoProcedimento();
    	$objProtocoloDTO->setDblIdProtocolo($parObjConcederCredencialDTO->getDblIdProcedimento());
    		
    	$objProtocoloRN = new ProtocoloRN();
   		$objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);
  		
  		//dado do usuário
  		$objUsuarioDTO = new UsuarioDTO();
  		$objUsuarioDTO->retNumIdUsuario();
  		$objUsuarioDTO->retStrSigla();
  		$objUsuarioDTO->retStrNome();
  		$objUsuarioDTO->setNumIdUsuario($parObjConcederCredencialDTO->getNumIdUsuario());

  		$objUsuarioRN = new UsuarioRN();
  		$objUsuarioDTO = $objUsuarioRN->consultarRN0489($objUsuarioDTO);

  		//dados de unidades  		
  		$objUnidadeDTO = new UnidadeDTO();
  		$objUnidadeDTO->retNumIdUnidade();
  		$objUnidadeDTO->retStrSigla();
  		$objUnidadeDTO->retStrDescricao();
  		$objUnidadeDTO->retStrSiglaOrgao();
  		$objUnidadeDTO->retStrDescricaoOrgao();
  		$objUnidadeDTO->setNumIdUnidade($parObjConcederCredencialDTO->getNumIdUnidade());
  		
  		$objUnidadeRN = new UnidadeRN();
  		$objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);

		  if ($objProtocoloDTO->getStrStaNivelAcessoGlobal()!=ProtocoloRN::$NA_SIGILOSO){
				$objInfraException->adicionarValidacao('Não é possível conceder credencial de acesso para um processo não sigiloso ('.$objProtocoloDTO->getStrProtocoloFormatado().').');
			}

			$dto = new AtividadeDTO();
      $dto->retNumIdAtividade();
			$dto->setNumIdTarefa(TarefaRN::getArrTarefasConcessaoCredencial(false), InfraDTO::$OPER_IN);
			$dto->setDblIdProtocolo($parObjConcederCredencialDTO->getDblIdProcedimento());
			$dto->setNumIdUsuario($parObjConcederCredencialDTO->getNumIdUsuario());
			$dto->setNumIdUnidade($parObjConcederCredencialDTO->getNumIdUnidade());
			$dto->setNumIdUsuarioOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
      $dto->setNumMaxRegistrosRetorno(1);

			//se tem outra credencial
			if ($this->consultarRN0033($dto) != null){
				$objInfraException->adicionarValidacao('Usuário atual já concedeu acesso ao usuário '.$objUsuarioDTO->getStrSigla().' no processo '.$objProtocoloDTO->getStrProtocoloFormatado().' na unidade '.$objUnidadeDTO->getStrSigla().'.');
			}

			$objEmailSistemaDTO = new EmailSistemaDTO();
			$objEmailSistemaDTO->retStrDe();
			$objEmailSistemaDTO->retStrPara();
			$objEmailSistemaDTO->retStrAssunto();
			$objEmailSistemaDTO->retStrConteudo();
			$objEmailSistemaDTO->setNumIdEmailSistema(EmailSistemaRN::$ES_CONCESSAO_CREDENCIAL);
				
			$objEmailSistemaRN = new EmailSistemaRN();
			$objEmailSistemaDTO = $objEmailSistemaRN->consultar($objEmailSistemaDTO);

			$arrObjEmailUnidadeDTO = array();

     	if ($objEmailSistemaDTO!=null && $parObjConcederCredencialDTO->getNumIdUnidade()!=SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
     	  
     	  $objEmailUnidadeDTO = new EmailUnidadeDTO();
     	  $objEmailUnidadeDTO->retNumIdUnidade();
     	  $objEmailUnidadeDTO->retStrEmail();
     	  $objEmailUnidadeDTO->retStrDescricao();
     	  $objEmailUnidadeDTO->setNumIdUnidade($parObjConcederCredencialDTO->getNumIdUnidade());
     	  
     	  $objEmailUnidadeRN = new EmailUnidadeRN();
     	  $arrObjEmailUnidadeDTO = $objEmailUnidadeRN->listar($objEmailUnidadeDTO);
     	  
			  if (count($arrObjEmailUnidadeDTO)==0){
				  $objInfraException->adicionarValidacao('Unidade '.$objUnidadeDTO->getStrSigla().'/'.$objUnidadeDTO->getStrSiglaOrgao().' não possui email cadastrado.');
				}
     	}
      
      $objInfraException->lancarValidacoes();
      	
      $objAtividadeDTO = new AtividadeDTO();
      $objAtividadeDTO->setDblIdProtocolo($parObjConcederCredencialDTO->getDblIdProcedimento());
      $objAtividadeDTO->setNumIdUnidade($parObjConcederCredencialDTO->getNumIdUnidade());
      $objAtividadeDTO->setNumIdUnidadeOrigem(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      $objAtividadeDTO->setNumIdUsuario($parObjConcederCredencialDTO->getNumIdUsuario());
      $objAtividadeDTO->setNumIdUsuarioOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
      $objAtividadeDTO->setDtaPrazo(null);
       

   		//Associar o processo e seus documentos com este usuário
   		$objAssociarDTO = new AssociarDTO();
   		$objAssociarDTO->setDblIdProcedimento($parObjConcederCredencialDTO->getDblIdProcedimento());
   		$objAssociarDTO->setNumIdUnidade($parObjConcederCredencialDTO->getNumIdUnidade());
   		$objAssociarDTO->setNumIdUsuario($parObjConcederCredencialDTO->getNumIdUsuario());
   		$objAssociarDTO->setStrStaNivelAcessoGlobal($objProtocoloDTO->getStrStaNivelAcessoGlobal());
   		$objProtocoloRN->associarRN0982($objAssociarDTO);


   		$arrObjAtributoAndamentoDTO = array();
    		 
   		$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
   		$objAtributoAndamentoDTO->setStrNome('USUARIO');
   		$objAtributoAndamentoDTO->setStrValor($objUsuarioDTO->getStrSigla().'¥'.$objUsuarioDTO->getStrNome());
   		$objAtributoAndamentoDTO->setStrIdOrigem($parObjConcederCredencialDTO->getNumIdUsuario());
   		$arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;

   		$objAtividadeDTO->setArrObjAtributoAndamentoDTO($arrObjAtributoAndamentoDTO);
   		$objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_PROCESSO_CONCESSAO_CREDENCIAL);
    		 
   		$ret = $this->gerarInternaRN0727($objAtividadeDTO);

      $objAtividadeDTO = new AtividadeDTO();
      $objAtividadeDTO->setNumIdAtividade($ret->getNumIdAtividade());
      $objAtividadeDTO->setDblIdProtocolo($parObjConcederCredencialDTO->getDblIdProcedimento());
      $objAtividadeDTO->setNumIdUsuario($parObjConcederCredencialDTO->getNumIdUsuario());
      $objAtividadeDTO->setNumIdUnidade($parObjConcederCredencialDTO->getNumIdUnidade());

      $this->anularRenuncia($objAtividadeDTO);

   		if ($objEmailSistemaDTO!=null && $parObjConcederCredencialDTO->getNumIdUnidade()!=SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
   		     		  
   		  $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
   		  $strEmailSistema = $objInfraParametro->getValor('SEI_EMAIL_SISTEMA');
   		  	
			  $strDe = $objEmailSistemaDTO->getStrDe();
			  $strDe = str_replace('@email_sistema@',$strEmailSistema,$strDe);
			  $strDe = str_replace('@sigla_sistema@',SessaoSEI::getInstance()->getStrSiglaSistema(),$strDe);
					  
			  $strEmailsUnidade = '';
			  foreach($arrObjEmailUnidadeDTO as $objEmailUnidadeDTO){
			  	$strEmailsUnidade .= $objEmailUnidadeDTO->getStrDescricao().' <'.$objEmailUnidadeDTO->getStrEmail().'> ;';			  	
			  }
			  $strEmailsUnidade = substr($strEmailsUnidade,0,-1);
					  
			  $strPara = $objEmailSistemaDTO->getStrPara();
			  $strPara = str_replace('@emails_unidade@',$strEmailsUnidade,$strPara);
					  
			  
				$strAssunto = $objEmailSistemaDTO->getStrAssunto();
				$strAssunto = str_replace('@processo@',$objProtocoloDTO->getStrProtocoloFormatado(),$strAssunto);
			  
					  
			  $strConteudo = $objEmailSistemaDTO->getStrConteudo();
			  $strConteudo = str_replace('@processo@',$objProtocoloDTO->getStrProtocoloFormatado(),$strConteudo);
			  $strConteudo = str_replace('@sigla_usuario_credencial@',$objUsuarioDTO->getStrSigla(),$strConteudo);
			  $strConteudo = str_replace('@nome_usuario_credencial@',$objUsuarioDTO->getStrNome(),$strConteudo);
			  $strConteudo = str_replace('@sigla_unidade_credencial@',$objUnidadeDTO->getStrSigla(),$strConteudo);
			  $strConteudo = str_replace('@descricao_unidade_credencial@',$objUnidadeDTO->getStrDescricao(),$strConteudo);
			  $strConteudo = str_replace('@sigla_orgao_unidade_credencial@',$objUnidadeDTO->getStrSiglaOrgao(),$strConteudo);
			  $strConteudo = str_replace('@descricao_orgao_unidade_credencial@',$objUnidadeDTO->getStrDescricaoOrgao(),$strConteudo);
			  $strConteudo = str_replace('@sigla_sistema@',SessaoSEI::getInstance()->getStrSiglaSistema(),$strConteudo);

        $objEmailDTO = new EmailDTO();
        $objEmailDTO->setStrDe($strDe);
        $objEmailDTO->setStrPara($strPara);
        $objEmailDTO->setStrAssunto($strAssunto);
        $objEmailDTO->setStrMensagem($strConteudo);

        EmailRN::processar(array($objEmailDTO));
  	 }

     return $ret;
     
    }catch(Exception $e){
      throw new InfraException('Erro concedendo credencial.',$e);
    }
  }

  protected function transferirCredencialControlado(TransferirCredencialDTO $parObjTransferirCredencialDTO) {

  	try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('procedimento_credencial_transferir',__METHOD__,$parObjTransferirCredencialDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();
      
      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->retDblIdProtocolo();
      $objProtocoloDTO->retStrProtocoloFormatado();
      $objProtocoloDTO->retStrStaNivelAcessoGlobal();
      $objProtocoloDTO->setDblIdProtocolo(InfraArray::converterArrInfraDTO($parObjTransferirCredencialDTO->getArrObjProtocoloDTO(),'IdProtocolo'),InfraDTO::$OPER_IN);
      
      $objProtocoloRN = new ProtocoloRN();
      $arrObjProtocoloDTO = InfraArray::indexarArrInfraDTO($objProtocoloRN->listarRN0668($objProtocoloDTO),'IdProtocolo');
      

  		//dado do usuário
  		$objUsuarioDTO = new UsuarioDTO();
  		$objUsuarioDTO->retNumIdUsuario();
  		$objUsuarioDTO->retStrSigla();
  		$objUsuarioDTO->retStrNome();
  		$objUsuarioDTO->setNumIdUsuario($parObjTransferirCredencialDTO->getNumIdUsuario());

  		$objUsuarioRN = new UsuarioRN();
  		$objUsuarioDTO = $objUsuarioRN->consultarRN0489($objUsuarioDTO);
      
      
      $objAcessoRN = new AcessoRN();
      
      foreach($arrObjProtocoloDTO as $objProtocoloDTO){
      	
      	$objAcessoDTO = new AcessoDTO();
        $objAcessoDTO->retNumIdAcesso();
      	$objAcessoDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
      	$objAcessoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
      	$objAcessoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      	$objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_PROCESSO);
        $objAcessoDTO->setNumMaxRegistrosRetorno(1);
      	
      	if ($objAcessoRN->consultar($objAcessoDTO) == null){
      		$objInfraException->adicionarValidacao('Usuário atual não possui credencial de acesso ao processo '.$objProtocoloDTO->getStrProtocoloFormatado().' nesta unidade.');
      	}

    		if ($objProtocoloDTO->getStrStaNivelAcessoGlobal()!=ProtocoloRN::$NA_SIGILOSO){
    			
					$objInfraException->adicionarValidacao('Não é possível transferir credencial de acesso para um processo não sigiloso ('.$objProtocoloDTO->getStrProtocoloFormatado().').');
					
				}else{
      	
	    		$dto = new AtividadeDTO();
          $dto->retNumIdAtividade();
	    		$dto->setNumIdTarefa(TarefaRN::getArrTarefasConcessaoCredencial(false), InfraDTO::$OPER_IN);
	    		$dto->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
	    		$dto->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
	    		$dto->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
	    		$dto->setNumIdUsuarioOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
          $dto->setNumMaxRegistrosRetorno(1);
	
	    		//se tem outra credencial
	    		if ($this->consultarRN0033($dto) != null){
						$objInfraException->adicionarValidacao('Usuário atual já concedeu acesso ao usuário '.$objUsuarioDTO->getStrSigla().' no processo '.$objProtocoloDTO->getStrProtocoloFormatado().' nesta unidade.');
					}
				}      	
      }

      $objInfraException->lancarValidacoes();
      
  		foreach($arrObjProtocoloDTO as $objProtocoloDTO){

        // Filtra campos do DTO
        $objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
        $objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objAtividadeDTO->setNumIdUnidadeOrigem(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objAtividadeDTO->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
        $objAtividadeDTO->setNumIdUsuarioOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
        $objAtividadeDTO->setDtaPrazo(null);
        	
 
    		//Associar o processo e seus documentos com este usuário
    		$objAssociarDTO = new AssociarDTO();
    		$objAssociarDTO->setDblIdProcedimento($objProtocoloDTO->getDblIdProtocolo());
    		$objAssociarDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
    		$objAssociarDTO->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
    		$objAssociarDTO->setStrStaNivelAcessoGlobal($objProtocoloDTO->getStrStaNivelAcessoGlobal());
    		$objProtocoloRN->associarRN0982($objAssociarDTO);
    		
    		
    		$arrObjAtributoAndamentoDTO = array();
    		 
    		$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
    		$objAtributoAndamentoDTO->setStrNome('USUARIO');
    		$objAtributoAndamentoDTO->setStrValor($objUsuarioDTO->getStrSigla().'¥'.$objUsuarioDTO->getStrNome());
    		$objAtributoAndamentoDTO->setStrIdOrigem($objUsuarioDTO->getNumIdUsuario());
    		$arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;

    		$objAtividadeDTO->setArrObjAtributoAndamentoDTO($arrObjAtributoAndamentoDTO);
    		$objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_PROCESSO_TRANSFERENCIA_CREDENCIAL);
    		 
    		$ret = $this->gerarInternaRN0727($objAtividadeDTO);
    		
		    $objAtividadeDTO = new AtividadeDTO();
		    $objAtividadeDTO->setNumIdAtividade($ret->getNumIdAtividade());
		    $objAtividadeDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
		    $objAtividadeDTO->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
		    $objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
		     
		    $this->anularRenuncia($objAtividadeDTO);
    		
      }     
    }catch(Exception $e){
      throw new InfraException('Erro transferindo credencial.',$e);
    }
  }

  protected function ativarCredencialControlado(AtivarCredencialDTO $parObjAtivarCredencialDTO) {

    try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('procedimento_credencial_ativar',__METHOD__,$parObjAtivarCredencialDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $objUsuarioDTO = new UsuarioDTO();
      $objUsuarioDTO->retNumIdUsuario();
      $objUsuarioDTO->retStrSigla();
      $objUsuarioDTO->retStrNome();
      $objUsuarioDTO->setNumIdUsuario($parObjAtivarCredencialDTO->getNumIdUsuario());

      $objUsuarioRN = new UsuarioRN();
      $objUsuarioDTO = $objUsuarioRN->consultarRN0489($objUsuarioDTO);

      $arrIdProcedimento = InfraArray::converterArrInfraDTO($parObjAtivarCredencialDTO->getArrObjProcedimentoDTO(),'IdProcedimento');

      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->retDblIdProtocolo();
      $objProtocoloDTO->retStrProtocoloFormatado();
      $objProtocoloDTO->retStrStaNivelAcessoGlobal();
      $objProtocoloDTO->setDblIdProtocolo($arrIdProcedimento, InfraDTO::$OPER_IN);

      $objProtocoloRN = new ProtocoloRN();
      $arrObjProtocoloDTO = InfraArray::indexarArrInfraDTO($objProtocoloRN->listarRN0668($objProtocoloDTO),'IdProtocolo');

      foreach($arrObjProtocoloDTO as $objProtocoloDTO) {

        if ($objProtocoloDTO->getStrStaNivelAcessoGlobal() != ProtocoloRN::$NA_SIGILOSO) {

          $objInfraException->adicionarValidacao('Processo ' . $objProtocoloDTO->getStrProtocoloFormatado() . ' não é sigiloso.');

        } else {

          $dto = new AtividadeDTO();
          $dto->retNumIdAtividade();
          $dto->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
          $dto->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
          $dto->setNumIdUsuario(null, InfraDTO::$OPER_DIFERENTE);
          $dto->setNumMaxRegistrosRetorno(1);

          if ($this->consultarRN0033($dto) == null) {
            $objInfraException->adicionarValidacao('Processo ' . $objProtocoloDTO->getStrProtocoloFormatado() . ' não tramitou na unidade.');
          }
        }
      }

      $objInfraException->lancarValidacoes();

      foreach($arrObjProtocoloDTO as $objProtocoloDTO) {

        $objAssociarDTO = new AssociarDTO();
        $objAssociarDTO->setDblIdProcedimento($objProtocoloDTO->getDblIdProtocolo());
        $objAssociarDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objAssociarDTO->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
        $objAssociarDTO->setStrStaNivelAcessoGlobal($objProtocoloDTO->getStrStaNivelAcessoGlobal());
        $objProtocoloRN->associarRN0982($objAssociarDTO);

        $objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
        $objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objAtividadeDTO->setNumIdUnidadeOrigem(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objAtividadeDTO->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
        $objAtividadeDTO->setNumIdUsuarioOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
        $objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_PROCESSO_ATIVACAO_CREDENCIAL);
        $objAtividadeDTO->setDtaPrazo(null);

        $arrObjAtributoAndamentoDTO = array();
        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('USUARIO');
        $objAtributoAndamentoDTO->setStrValor($objUsuarioDTO->getStrSigla() . '¥' . $objUsuarioDTO->getStrNome());
        $objAtributoAndamentoDTO->setStrIdOrigem($objUsuarioDTO->getNumIdUsuario());
        $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;
        $objAtividadeDTO->setArrObjAtributoAndamentoDTO($arrObjAtributoAndamentoDTO);

        $ret = $this->gerarInternaRN0727($objAtividadeDTO);

        $objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->setNumIdAtividade($ret->getNumIdAtividade());
        $objAtividadeDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
        $objAtividadeDTO->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
        $objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

        $this->anularRenuncia($objAtividadeDTO);

      }

    }catch(Exception $e){
      throw new InfraException('Erro ativando credencial.',$e);
    }
  }

  protected function listarCredenciaisConectado(ProcedimentoDTO $objProcedimentoDTO) {
    try{
    	
    	$objInfraException = new InfraException(); 
    	
      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->retDblIdProtocolo();
      $objProtocoloDTO->retStrProtocoloFormatado();
      $objProtocoloDTO->retStrStaNivelAcessoGlobal();
      $objProtocoloDTO->setDblIdProtocolo($objProcedimentoDTO->getDblIdProcedimento());
      
      $objProtocoloRN = new ProtocoloRN();
      $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);
    	
    	$objAcessoDTO = new AcessoDTO();
      $objAcessoDTO->retNumIdAcesso();
     	$objAcessoDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
     	$objAcessoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
     	$objAcessoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
     	$objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_PROCESSO);
      $objAcessoDTO->setNumMaxRegistrosRetorno(1);
      	
     	$objAcessoRN = new AcessoRN();
     	
     	if ($objAcessoRN->consultar($objAcessoDTO) == null){
     		$objInfraException->adicionarValidacao('Usuário atual não possui credencial de acesso ao processo '.$objProtocoloDTO->getStrProtocoloFormatado().' nesta unidade.');
     	}

    	if ($objProtocoloDTO->getStrStaNivelAcessoGlobal()!=ProtocoloRN::$NA_SIGILOSO){
				$objInfraException->adicionarValidacao('Não é possível listar credenciais de acesso para um processo não sigiloso ('.$objProtocoloDTO->getStrProtocoloFormatado().').');
			}    	
    	
			$objInfraException->lancarValidacoes();
    	
			$objAtividadeDTO = new AtividadeDTO();
			$objAtividadeDTO->retNumIdAtividade();
			$objAtividadeDTO->retStrSiglaUsuario();
			$objAtividadeDTO->retStrNomeUsuario();
			$objAtividadeDTO->retStrSiglaUnidade();
			$objAtividadeDTO->retStrDescricaoUnidade();
			$objAtividadeDTO->retDthAbertura();
			$objAtividadeDTO->retNumIdTarefa();
			$objAtividadeDTO->setNumIdUsuarioOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
			$objAtividadeDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
      $objAtividadeDTO->setNumIdTarefa(array_merge(TarefaRN::getArrTarefasConcessaoCredencial(false), TarefaRN::getArrTarefasCassacaoCredencial(false)), InfraDTO::$OPER_IN);

      $objAtividadeDTO->setOrdNumIdAtividade(InfraDTO::$TIPO_ORDENACAO_DESC);			                                       
			$arrObjAtividadeDTO = $this->listarRN0036($objAtividadeDTO);
			
			if (count($arrObjAtividadeDTO)){
				
				$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
				$objAtributoAndamentoDTO->retNumIdAtividade();
				$objAtributoAndamentoDTO->retStrNome();
				$objAtributoAndamentoDTO->retStrValor();
				$objAtributoAndamentoDTO->setNumIdAtividade(InfraArray::converterArrInfraDTO($arrObjAtividadeDTO,'IdAtividade'), InfraDTO::$OPER_IN);
				
				$objAtributoAndamentoRN = new AtributoAndamentoRN();
				$arrObjAtributoAndamentoDTO = InfraArray::indexarArrInfraDTO($objAtributoAndamentoRN->listarRN1367($objAtributoAndamentoDTO),'IdAtividade',true);
				
				foreach($arrObjAtividadeDTO as $objAtividadeDTO){
					if (isset($arrObjAtributoAndamentoDTO[$objAtividadeDTO->getNumIdAtividade()])){
						$objAtividadeDTO->setArrObjAtributoAndamentoDTO($arrObjAtributoAndamentoDTO[$objAtividadeDTO->getNumIdAtividade()]);
					}else{
						$objAtividadeDTO->setArrObjAtributoAndamentoDTO(array());
					}
				}
			} 
			
			return $arrObjAtividadeDTO;
			
    }catch(Exception $e){
      throw new InfraException('Erro listando credenciais.',$e);
    }
  }

  protected function cassarCredenciaisControlado($parArrObjAtividadeDTO) {
    try{
    	
    	$objInfraException = new InfraException();
    	
    	$objAtividadeDTO = new AtividadeDTO();
    	$objAtividadeDTO->retNumIdAtividade();
    	$objAtividadeDTO->retDblIdProtocolo();
    	$objAtividadeDTO->retNumIdTarefa();
    	$objAtividadeDTO->retStrProtocoloFormatadoProtocolo();
    	$objAtividadeDTO->retStrStaNivelAcessoGlobalProtocolo();
    	$objAtividadeDTO->retNumIdUsuarioOrigem();
    	$objAtividadeDTO->retNumIdUnidade();
    	$objAtividadeDTO->retNumIdUsuario();
    	$objAtividadeDTO->retStrSiglaUsuario();
    	$objAtividadeDTO->retDthConclusao();
    	$objAtividadeDTO->retStrNomeUsuario();
    	$objAtividadeDTO->setNumIdAtividade(InfraArray::converterArrInfraDTO($parArrObjAtividadeDTO,'IdAtividade'), InfraDTO::$OPER_IN);
    	
    	$arrObjAtividadeDTO = $this->listarRN0036($objAtividadeDTO);

	    $objAcessoRN = new AcessoRN();
			$objAtributoAndamentoRN = new AtributoAndamentoRN();

    	foreach($arrObjAtividadeDTO as $objAtividadeDTO){

    	  if ($objAtividadeDTO->getStrStaNivelAcessoGlobalProtocolo()!=ProtocoloRN::$NA_SIGILOSO){
				  $objInfraException->adicionarValidacao('Não é possível cassar credencial de acesso em um processo não sigiloso ('.$objAtividadeDTO->getStrProtocoloFormatadoProtocolo().').');
			  }
    		
    		if (!in_array($objAtividadeDTO->getNumIdTarefa(), TarefaRN::getArrTarefasConcessaoCredencial(false))){
    			$objInfraException->adicionarValidacao('Andamento do processo '.$objAtividadeDTO->getStrProtocoloFormatadoProtocolo().' não é uma concessão ou transferência de credencial.');
    		}
    		
    		if ($objAtividadeDTO->getNumIdUsuarioOrigem()!=SessaoSEI::getInstance()->getNumIdUsuario()){
    			$objInfraException->adicionarValidacao('Credencial do processo '.$objAtividadeDTO->getStrProtocoloFormatadoProtocolo().' não foi concedida pelo usuário atual.');
    		}
    		
	    	$objAcessoDTO = new AcessoDTO();
        $objAcessoDTO->retNumIdAcesso();
	     	$objAcessoDTO->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
	     	$objAcessoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
	     	$objAcessoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
	     	$objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_PROCESSO);
        $objAcessoDTO->setNumMaxRegistrosRetorno(1);
	     	
	     	if ($objAcessoRN->consultar($objAcessoDTO) == null){
	     		$objInfraException->adicionarValidacao('Usuário atual não possui credencial de acesso ao processo '.$objAtividadeDTO->getStrProtocoloFormatadoProtocolo().' nesta unidade.');
	     	}


				$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
				$objAtributoAndamentoDTO->retStrValor();
				$objAtributoAndamentoDTO->setStrNome('DOCUMENTO');
				$objAtributoAndamentoDTO->setDblIdProtocoloAtividade($objAtividadeDTO->getDblIdProtocolo());
				$objAtributoAndamentoDTO->setNumIdUsuarioOrigemAtividade(SessaoSEI::getInstance()->getNumIdUsuario());
				$objAtributoAndamentoDTO->setNumIdUnidadeOrigemAtividade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
				$objAtributoAndamentoDTO->setNumIdTarefaAtividade(TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA);
				$objAtributoAndamentoDTO->setNumIdUsuarioAtividade($objAtividadeDTO->getNumIdUsuario());
				$objAtributoAndamentoDTO->setNumIdUnidadeAtividade($objAtividadeDTO->getNumIdUnidade());

				$arrObjAtributoAndamentoDTO = $objAtributoAndamentoRN->listarRN1367($objAtributoAndamentoDTO);

				foreach($arrObjAtributoAndamentoDTO as $objAtributoAndamentoDTO){
					$objInfraException->adicionarValidacao('Não foi possível cassar a credencial no processo porque o documento '.$objAtributoAndamentoDTO->getStrValor().' possui Credencial para Assinatura ativa concedida pelo usuário atual.');
				}
    	}
    	
    	$objInfraException->lancarValidacoes();
    	
      
    	$objAtributoAndamentoRN = new AtributoAndamentoRN();
    	$objAtividadeBD = new AtividadeBD($this->getObjInfraIBanco());

    	$strDataHoraAtual = InfraData::getStrDataHoraAtual();
    	
    	foreach($arrObjAtividadeDTO as $objAtividadeDTO){
    		
	  		//recupera outros andamentos que fornecem acesso ao processo sigiloso para o usuário na unidade
	  	 	$dto = new AtividadeDTO();
        $dto->retNumIdAtividade();
	    	$dto->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade(),InfraDTO::$OPER_DIFERENTE);
	    	$dto->setNumIdTarefa(TarefaRN::getArrTarefasConcessaoCredencial(false), InfraDTO::$OPER_IN);
	    	$dto->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
	    	$dto->setNumIdUsuario($objAtividadeDTO->getNumIdUsuario());
	    	$dto->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade());
        $dto->setNumMaxRegistrosRetorno(1);
	
	    	//se não tem outra credencial
	    	if ($this->consultarRN0033($dto) == null){
	    		
	   			//recupera andamentos abertos no processo para o usuario na unidade
		      $objPesquisaPendenciaDTO = new PesquisaPendenciaDTO();
		      $objPesquisaPendenciaDTO->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
		      $objPesquisaPendenciaDTO->setNumIdUsuario($objAtividadeDTO->getNumIdUsuario());
		      $objPesquisaPendenciaDTO->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade());
			      
			    $arrObjProcedimentoDTO = $this->listarPendenciasRN0754($objPesquisaPendenciaDTO);
			      
			    if (count($arrObjProcedimentoDTO)==1){
			      	
			     	$arr = $arrObjProcedimentoDTO[0]->getArrObjAtividadeDTO();
		    			
		    		//se tem andamentos em aberto
		    		if (count($arr)){
		    			  
		    			//conclui andamentos
		    			$this->concluirRN0726($arr);
		    				
		    			//lanca andamento registrando conclusao automatica
		    			$dto = new AtividadeDTO();
				      $dto->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
				      $dto->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade());
				      $dto->setNumIdUsuario($objAtividadeDTO->getNumIdUsuario());
				      $dto->setNumIdTarefa(TarefaRN::$TI_CONCLUSAO_AUTOMATICA_USUARIO);

              $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
              $objAtributoAndamentoDTO->setStrNome('USUARIO');
              $objAtributoAndamentoDTO->setStrValor($objAtividadeDTO->getStrSiglaUsuario().'¥'.$objAtividadeDTO->getStrNomeUsuario());
              $objAtributoAndamentoDTO->setStrIdOrigem($objAtividadeDTO->getNumIdUsuario());
              $dto->setArrObjAtributoAndamentoDTO(array($objAtributoAndamentoDTO));

              $this->gerarInternaRN0727($dto);
		    		}
			    }

					$objAcessoDTO = new AcessoDTO();
					$objAcessoDTO->retNumIdAcesso();
					$objAcessoDTO->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
					$objAcessoDTO->setNumIdUsuario($objAtividadeDTO->getNumIdUsuario());
					$objAcessoDTO->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade());
					$objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_PROCESSO);
					$objAcessoRN->excluir($objAcessoRN->listar($objAcessoDTO));
	    	}
    		
    		//lança andamento para o usuário atual registrando a cassação de credencial
        $dto = new AtividadeDTO();
        $dto->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
        $dto->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $dto->setNumIdUnidadeOrigem(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $dto->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
        $dto->setNumIdUsuarioOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
        $dto->setDtaPrazo(null);
        	
    		$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
    		$objAtributoAndamentoDTO->setStrNome('USUARIO');
    		$objAtributoAndamentoDTO->setStrValor($objAtividadeDTO->getStrSiglaUsuario().'¥'.$objAtividadeDTO->getStrNomeUsuario());
    		$objAtributoAndamentoDTO->setStrIdOrigem($objAtividadeDTO->getNumIdUsuario());

    		$dto->setArrObjAtributoAndamentoDTO(array($objAtributoAndamentoDTO));
    		$dto->setNumIdTarefa(TarefaRN::$TI_PROCESSO_CASSACAO_CREDENCIAL);
    		 
    		$ret = $this->gerarInternaRN0727($dto);

    		//altera andamento original de concessão ou transferência
    		$dto = new AtividadeDTO();

        $numIdTarefaCassacao = null;
        if ($objAtividadeDTO->getNumIdTarefa()==TarefaRN::$TI_PROCESSO_CONCESSAO_CREDENCIAL){
          $numIdTarefaCassacao = TarefaRN::$TI_PROCESSO_CONCESSAO_CREDENCIAL_CASSADA;
        }else if ($objAtividadeDTO->getNumIdTarefa()==TarefaRN::$TI_PROCESSO_TRANSFERENCIA_CREDENCIAL){
          $numIdTarefaCassacao = TarefaRN::$TI_PROCESSO_TRANSFERENCIA_CREDENCIAL_CASSADA;
        }else if ($objAtividadeDTO->getNumIdTarefa()==TarefaRN::$TI_PROCESSO_ATIVACAO_CREDENCIAL){
          $numIdTarefaCassacao = TarefaRN::$TI_PROCESSO_ATIVACAO_CREDENCIAL_CASSADA;
        }
   		  $dto->setNumIdTarefa($numIdTarefaCassacao);

    		$dto->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
    		$objAtividadeBD->alterar($dto);

    		//lanca atributo de cassacao no andamento original
    		$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
    		$objAtributoAndamentoDTO->setStrNome('DATA_HORA');
    		$objAtributoAndamentoDTO->setStrValor($strDataHoraAtual);
    		$objAtributoAndamentoDTO->setStrIdOrigem($ret->getNumIdAtividade()); //relaciona com o andamento de cassação
    		$objAtributoAndamentoDTO->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
    		$objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);
    	}
    	
    	return InfraArray::gerarArrInfraDTO('ProtocoloDTO','IdProtocolo',array_unique(InfraArray::converterArrInfraDTO($arrObjAtividadeDTO,'IdProtocolo')));
    	
    }catch(Exception $e){
      throw new InfraException('Erro cassando credenciais.',$e);
    }
  }

  protected function cancelarCredenciaisControlado($arrObjProcedimentoDTO) {
    try{

      $objInfraException = new InfraException();

      $arrIdProcedimento = InfraArray::converterArrInfraDTO($arrObjProcedimentoDTO,'IdProcedimento');

      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->retDblIdProtocolo();
      $objProtocoloDTO->retStrProtocoloFormatado();
      $objProtocoloDTO->retStrStaNivelAcessoGlobal();
      $objProtocoloDTO->setDblIdProtocolo($arrIdProcedimento,InfraDTO::$OPER_IN);

      $objProtocoloRN = new ProtocoloRN();
      $arrObjProtocoloDTO = InfraArray::indexarArrInfraDTO($objProtocoloRN->listarRN0668($objProtocoloDTO),'IdProtocolo');

      $objPesquisaSigilosoDTO = new PesquisaSigilosoDTO();
      $objPesquisaSigilosoDTO->setStrStaAcessoUnidade(ProtocoloRN::$TASU_SIM);
      $objPesquisaSigilosoDTO->setDblIdProtocolo($arrIdProcedimento, InfraDTO::$OPER_IN);
      $arrObjProtocoloDTOSigiloso = InfraArray::indexarArrInfraDTO($objProtocoloRN->pesquisarAcervoSigilosos($objPesquisaSigilosoDTO),'IdProtocolo');

      $arrObjAcessoDTO = array();
      $arrIdUsuarios = array();

      foreach($arrObjProtocoloDTO as $objProtocoloDTO) {

        $dblIdProcedimento = $objProtocoloDTO->getDblIdProtocolo();

        if ($objProtocoloDTO->getStrStaNivelAcessoGlobal() != ProtocoloRN::$NA_SIGILOSO) {

          $objInfraException->adicionarValidacao('Processo '. $objProtocoloDTO->getStrProtocoloFormatado() . ' não é sigiloso.');

        }else {

          if (!isset($arrObjProtocoloDTOSigiloso[$dblIdProcedimento])){
            $objInfraException->adicionarValidacao('Processo ' . $objProtocoloDTO->getStrProtocoloFormatado() . ' não possui credencial ativa na unidade.');
          }else {

            $arrTemp = $arrObjProtocoloDTOSigiloso[$dblIdProcedimento]->getArrObjAcessoDTO();
            $arrObjAcessoDTO[$dblIdProcedimento] = array();
            foreach ($arrTemp as $objAcessoDTO) {
              if ($objAcessoDTO->getStrStaCredencialUnidade() == ProtocoloRN::$TCU_INATIVA) {
                $arrObjAcessoDTO[$dblIdProcedimento][] = $objAcessoDTO;
                $arrIdUsuarios[$objAcessoDTO->getNumIdUsuario()] = 0;
              }
            }

            if (count($arrObjAcessoDTO[$dblIdProcedimento]) == 0) {
              $objInfraException->adicionarValidacao('Processo ' . $objProtocoloDTO->getStrProtocoloFormatado() . ' não possui credencial inativa para cancelamento na unidade.');
            }
          }
        }
      }

      $objInfraException->lancarValidacoes();

      $objUsuarioDTO = new UsuarioDTO();
      $objUsuarioDTO->setBolExclusaoLogica(false);
      $objUsuarioDTO->retNumIdUsuario();
      $objUsuarioDTO->retStrSigla();
      $objUsuarioDTO->retStrNome();
      $objUsuarioDTO->setNumIdUsuario(array_keys($arrIdUsuarios), InfraDTO::$OPER_IN);

      $objUsuarioRN = new UsuarioRN();
      $arrObjUsuarioDTO = InfraArray::indexarArrInfraDTO($objUsuarioRN->listarRN0490($objUsuarioDTO), 'IdUsuario');

      $objAtributoAndamentoRN = new AtributoAndamentoRN();
      $objAcessoRN = new AcessoRN();
      $objDocumentoRN = new DocumentoRN();
      $objAtividadeBD = new AtividadeBD($this->getObjInfraIBanco());
      $strDataHoraAtual = InfraData::getStrDataHoraAtual();

      foreach($arrObjAcessoDTO as $dblIdProcedimento => $arrObjAcessoDTOProcesso) {

        $objDocumentoDTO = new DocumentoDTO();
        $objDocumentoDTO->retDblIdDocumento();
        $objDocumentoDTO->setDblIdProcedimento($dblIdProcedimento);
        $arrProtocolos = InfraArray::converterArrInfraDTO($objDocumentoRN->listarRN0008($objDocumentoDTO), 'IdDocumento');
        $arrProtocolos[] = $dblIdProcedimento;

        $arrIdUsuario = InfraArray::converterArrInfraDTO($arrObjAcessoDTOProcesso,'IdUsuario');

        $arrIdAtividadeCancelamento = array();

        foreach ($arrIdUsuario as $numIdUsuario) {

          $objUsuarioDTO = $arrObjUsuarioDTO[$numIdUsuario];

          $objPesquisaPendenciaDTO = new PesquisaPendenciaDTO();
          $objPesquisaPendenciaDTO->setDblIdProtocolo($dblIdProcedimento);
          $objPesquisaPendenciaDTO->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
          $objPesquisaPendenciaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

          $arrObjProcedimentoDTO = $this->listarPendenciasRN0754($objPesquisaPendenciaDTO);

          if (count($arrObjProcedimentoDTO) == 1) {

            $arr = $arrObjProcedimentoDTO[0]->getArrObjAtividadeDTO();

            //se tem andamentos em aberto
            if (count($arr)) {

              //conclui andamentos
              $this->concluirRN0726($arr);

              //lanca andamento registrando conclusao automatica
              $dto = new AtividadeDTO();
              $dto->setDblIdProtocolo($dblIdProcedimento);
              $dto->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
              $dto->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
              $dto->setNumIdTarefa(TarefaRN::$TI_CONCLUSAO_AUTOMATICA_USUARIO);

              $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
              $objAtributoAndamentoDTO->setStrNome('USUARIO');
              $objAtributoAndamentoDTO->setStrValor($objUsuarioDTO->getStrSigla() . '¥' . $objUsuarioDTO->getStrNome());
              $objAtributoAndamentoDTO->setStrIdOrigem($objUsuarioDTO->getNumIdUsuario());
              $dto->setArrObjAtributoAndamentoDTO(array($objAtributoAndamentoDTO));

              $this->gerarInternaRN0727($dto);
            }
          }


          $objAcessoDTO = new AcessoDTO();
          $objAcessoDTO->retNumIdAcesso();
          $objAcessoDTO->setDblIdProtocolo($arrProtocolos, InfraDTO::$OPER_IN);
          $objAcessoDTO->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
          $objAcessoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
          $objAcessoDTO->setStrStaTipo(array(AcessoRN::$TA_CREDENCIAL_PROCESSO, AcessoRN::$TA_CREDENCIAL_ASSINATURA_PROCESSO, AcessoRN::$TA_CREDENCIAL_ASSINATURA_DOCUMENTO), InfraDTO::$OPER_IN);
          $objAcessoRN->excluir($objAcessoRN->listar($objAcessoDTO));

          $dto = new AtividadeDTO();
          $dto->setDblIdProtocolo($dblIdProcedimento);
          $dto->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
          $dto->setNumIdUnidadeOrigem(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
          $dto->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
          $dto->setNumIdUsuarioOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
          $dto->setNumIdTarefa(TarefaRN::$TI_PROCESSO_CANCELAMENTO_CREDENCIAL);
          $dto->setDtaPrazo(null);

          $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
          $objAtributoAndamentoDTO->setStrNome('USUARIO');
          $objAtributoAndamentoDTO->setStrValor($objUsuarioDTO->getStrSigla() . '¥' . $objUsuarioDTO->getStrNome());
          $objAtributoAndamentoDTO->setStrIdOrigem($objUsuarioDTO->getNumIdUsuario());
          $dto->setArrObjAtributoAndamentoDTO(array($objAtributoAndamentoDTO));

          $arrIdAtividadeCancelamento[$objUsuarioDTO->getNumIdUsuario()] = $this->gerarInternaRN0727($dto);
        }

        $objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->retDblIdProtocolo();
        $objAtividadeDTO->retNumIdAtividade();
        $objAtividadeDTO->retNumIdUsuario();
        $objAtividadeDTO->retNumIdUnidade();
        $objAtividadeDTO->retNumIdTarefa();
        $objAtividadeDTO->setDblIdProtocolo($dblIdProcedimento);
        $objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objAtividadeDTO->setNumIdUsuario($arrIdUsuario, InfraDTO::$OPER_IN);
        $objAtividadeDTO->setNumIdTarefa(TarefaRN::getArrTarefasConcessaoCredencial(true), InfraDTO::$OPER_IN);

        $arrObjAtividadeDTO = $this->listarRN0036($objAtividadeDTO);

        foreach ($arrObjAtividadeDTO as $objAtividadeDTO) {

          //altera andamento original de concessão ou transferência
          $dto = new AtividadeDTO();

          $numIdTarefaAnulacao = null;
          if ($objAtividadeDTO->getNumIdTarefa() == TarefaRN::$TI_PROCESSO_CONCESSAO_CREDENCIAL) {
            $numIdTarefaAnulacao = TarefaRN::$TI_PROCESSO_CONCESSAO_CREDENCIAL_ANULADA;
          } else if ($objAtividadeDTO->getNumIdTarefa() == TarefaRN::$TI_PROCESSO_TRANSFERENCIA_CREDENCIAL) {
            $numIdTarefaAnulacao = TarefaRN::$TI_PROCESSO_TRANSFERENCIA_CREDENCIAL_ANULADA;
          } else if ($objAtividadeDTO->getNumIdTarefa() == TarefaRN::$TI_PROCESSO_ATIVACAO_CREDENCIAL) {
            $numIdTarefaAnulacao = TarefaRN::$TI_PROCESSO_ATIVACAO_CREDENCIAL_ANULADA;
          } else if ($objAtividadeDTO->getNumIdTarefa() == TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA) {
            $numIdTarefaAnulacao = TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA_ANULADA;
          }
          $dto->setNumIdTarefa($numIdTarefaAnulacao);

          $dto->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
          $objAtividadeBD->alterar($dto);

          $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
          $objAtributoAndamentoDTO->setStrNome('USUARIO_ANULACAO');
          $objAtributoAndamentoDTO->setStrValor(SessaoSEI::getInstance()->getStrSiglaUsuario() . '¥' . SessaoSEI::getInstance()->getStrNomeUsuario());
          $objAtributoAndamentoDTO->setStrIdOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
          $objAtributoAndamentoDTO->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
          $objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);

          //lanca atributo de cancelamento no andamento original
          $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
          $objAtributoAndamentoDTO->setStrNome('DATA_HORA');
          $objAtributoAndamentoDTO->setStrValor($strDataHoraAtual);
          $objAtributoAndamentoDTO->setStrIdOrigem($arrIdAtividadeCancelamento[$objAtividadeDTO->getNumIdUsuario()]->getNumIdAtividade()); //relaciona com o andamento de cancelamento
          $objAtributoAndamentoDTO->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
          $objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);
        }
      }

      //$objInfraException->lancarValidacao('FIM');

    }catch(Exception $e){
      throw new InfraException('Erro cancelando credenciais.',$e);
    }
  }

  protected function mudarTarefaControlado(AtividadeDTO $parObjAtividadeDTO) {
    try{
      
      SessaoSEI::getInstance()->validarAuditarPermissao('atividade_alterar',__METHOD__,$parObjAtividadeDTO);
      
      $objAtividadeDTO = new AtividadeDTO();
      $objAtividadeDTO->setNumIdTarefa($parObjAtividadeDTO->getNumIdTarefa());
      $objAtividadeDTO->setNumIdAtividade($parObjAtividadeDTO->getNumIdAtividade());
      
      $objAtividadeBD = new AtividadeBD($this->getObjInfraIBanco());
      $objAtividadeBD->alterar($objAtividadeDTO);
      
    }catch(Exception $e){
      throw new InfraException('Erro alterando tarefa do andamento.',$e);
    }
  }

  protected function anularCredenciaisProcessoControlado(AtividadeDTO $parObjAtividadeDTO) {
    try{
    	
    	$objAtividadeDTO = new AtividadeDTO();
    	$objAtividadeDTO->retNumIdAtividade();
    	$objAtividadeDTO->retNumIdTarefa();
      $objAtividadeDTO->setNumIdTarefa(TarefaRN::getArrTarefasConcessaoCredencial(true), InfraDTO::$OPER_IN);

    	$objAtividadeDTO->setDblIdProtocolo($parObjAtividadeDTO->getDblIdProtocolo());
    	
    	$arrObjAtividadeDTO = $this->listarRN0036($objAtividadeDTO);
      
    	$objAtributoAndamentoRN = new AtributoAndamentoRN();

    	$strDataHoraAtual = InfraData::getStrDataHoraAtual();
    	
    	$objAtividadeBD = new AtividadeBD($this->getObjInfraIBanco());
    	
    	foreach($arrObjAtividadeDTO as $objAtividadeDTO){
    		
    		$dto = new AtividadeDTO();

        $numIdTarefaAnulacao = null;
        if ($objAtividadeDTO->getNumIdTarefa()==TarefaRN::$TI_PROCESSO_CONCESSAO_CREDENCIAL) {
          $numIdTarefaAnulacao = TarefaRN::$TI_PROCESSO_CONCESSAO_CREDENCIAL_ANULADA;
        }else if ($objAtividadeDTO->getNumIdTarefa()==TarefaRN::$TI_PROCESSO_TRANSFERENCIA_CREDENCIAL) {
          $numIdTarefaAnulacao = TarefaRN::$TI_PROCESSO_TRANSFERENCIA_CREDENCIAL_ANULADA;
        }else if ($objAtividadeDTO->getNumIdTarefa()==TarefaRN::$TI_PROCESSO_ATIVACAO_CREDENCIAL) {
          $numIdTarefaAnulacao = TarefaRN::$TI_PROCESSO_ATIVACAO_CREDENCIAL_ANULADA;
        }else if ($objAtividadeDTO->getNumIdTarefa()==TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA) {
          $numIdTarefaAnulacao = TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA_ANULADA;
        }

        $dto->setNumIdTarefa($numIdTarefaAnulacao);
    		$dto->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
    		
    		$objAtividadeBD->alterar($dto);

    		$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
    		$objAtributoAndamentoDTO->setStrNome('USUARIO_ANULACAO');
    		$objAtributoAndamentoDTO->setStrValor(SessaoSEI::getInstance()->getStrSiglaUsuario().'¥'.SessaoSEI::getInstance()->getStrNomeUsuario());
    		$objAtributoAndamentoDTO->setStrIdOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
				$objAtributoAndamentoDTO->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
				$objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);
    		
    		$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
    		$objAtributoAndamentoDTO->setStrNome('DATA_HORA');
    		$objAtributoAndamentoDTO->setStrValor($strDataHoraAtual);
    		$objAtributoAndamentoDTO->setStrIdOrigem($parObjAtividadeDTO->getNumIdAtividade()); //id do andamento que causou a anulação 
    		$objAtributoAndamentoDTO->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
    		$objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);
    	}

    	//remove atribuições dos usuários nas unidades
    	$objAtividadeDTO = new AtividadeDTO();
    	$objAtividadeDTO->retNumIdAtividade();
    	$objAtividadeDTO->setDthConclusao(null);
    	$objAtividadeDTO->setDblIdProtocolo($parObjAtividadeDTO->getDblIdProtocolo());
    	
    	$arrObjAtividadeDTO = $this->listarRN0036($objAtividadeDTO);
      
    	foreach($arrObjAtividadeDTO as $objAtividadeDTO){
    	  $dto = new AtividadeDTO();
    	  $dto->setNumIdUsuarioAtribuicao(null);
    	  $dto->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
    		
    		$objAtividadeBD->alterar($dto);
    	}
    	
    }catch(Exception $e){
      throw new InfraException('Erro anulando credenciais do processo.',$e);
    }
  }

  private function anularRenuncia(AtividadeDTO $parObjAtividadeDTO) {
    try{
    	
    	//$objInfraException = new InfraException();
    	
    	$objAtividadeDTO = new AtividadeDTO();
    	$objAtividadeDTO->retNumIdAtividade();
    	$objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_PROCESSO_RENUNCIA_CREDENCIAL);
    	$objAtividadeDTO->setDblIdProtocolo($parObjAtividadeDTO->getDblIdProtocolo());
    	$objAtividadeDTO->setNumIdUsuario($parObjAtividadeDTO->getNumIdUsuario());                       
    	$objAtividadeDTO->setNumIdUnidade($parObjAtividadeDTO->getNumIdUnidade());
    	
    	$arrObjAtividadeDTO = $this->listarRN0036($objAtividadeDTO);
      
    	$objAtributoAndamentoRN = new AtributoAndamentoRN();

    	foreach($arrObjAtividadeDTO as $objAtividadeDTO){
    		
    		$dto = new AtividadeDTO();
 				$dto->setNumIdTarefa(TarefaRN::$TI_PROCESSO_RENUNCIA_CREDENCIAL_ANULADA);
    		$dto->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
    		
    		$objAtividadeBD = new AtividadeBD($this->getObjInfraIBanco());
    		$objAtividadeBD->alterar($dto);

    		$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
    		$objAtributoAndamentoDTO->setStrNome('USUARIO_ANULACAO');
    		$objAtributoAndamentoDTO->setStrValor(SessaoSEI::getInstance()->getStrSiglaUsuario().'¥'.SessaoSEI::getInstance()->getStrNomeUsuario());
    		$objAtributoAndamentoDTO->setStrIdOrigem(SessaoSEI::getInstance()->getNumIdUsuario()); 
    		$objAtributoAndamentoDTO->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
    		$objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);
    		
    		$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
    		$objAtributoAndamentoDTO->setStrNome('DATA_HORA');
    		$objAtributoAndamentoDTO->setStrValor(InfraData::getStrDataHoraAtual());
    		$objAtributoAndamentoDTO->setStrIdOrigem($parObjAtividadeDTO->getNumIdAtividade()); //id do andamento que causou a anulação 
    		$objAtributoAndamentoDTO->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
    		$objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);
    	}
    	
    }catch(Exception $e){
      throw new InfraException('Erro anulando renúncia de credenciais.',$e);
    }
  }

  protected function listarCredenciaisAssinaturaConectado(DocumentoDTO $parObjDocumentoDTO) {
    try{
    	
    	$objInfraException = new InfraException(); 
    	
    	$objDocumentoDTO = new DocumentoDTO();
    	$objDocumentoDTO->retDblIdProcedimento();
    	$objDocumentoDTO->setDblIdDocumento($parObjDocumentoDTO->getDblIdDocumento());
    	
    	$objDocumentoRN = new DocumentoRN();
    	$objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
    	
      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->retStrProtocoloFormatado();
      $objProtocoloDTO->retStrStaNivelAcessoGlobal();
      $objProtocoloDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdProcedimento());
      
      $objProtocoloRN = new ProtocoloRN();
      $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);
    	
    	$objAcessoDTO = new AcessoDTO();
      $objAcessoDTO->retNumIdAcesso();
     	$objAcessoDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdProcedimento());
     	$objAcessoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
     	$objAcessoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
     	$objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_PROCESSO);
      $objAcessoDTO->setNumMaxRegistrosRetorno(1);
      	
     	$objAcessoRN = new AcessoRN();
     	
     	if ($objAcessoRN->consultar($objAcessoDTO) == null){
     		$objInfraException->adicionarValidacao('Usuário atual não possui credencial de acesso ao processo '.$objProtocoloDTO->getStrProtocoloFormatado().' nesta unidade.');
     	}

    	if ($objProtocoloDTO->getStrStaNivelAcessoGlobal()!=ProtocoloRN::$NA_SIGILOSO){
				$objInfraException->adicionarValidacao('Não é possível listar credenciais de assinatura para um processo não sigiloso ('.$objProtocoloDTO->getStrProtocoloFormatado().').');
			}    	
    	
			$objInfraException->lancarValidacoes();
			
			//recupera andamentos relativos ao documento
			$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
			$objAtributoAndamentoDTO->retNumIdAtividade();
			$objAtributoAndamentoDTO->setStrIdOrigem($parObjDocumentoDTO->getDblIdDocumento());
			$objAtributoAndamentoDTO->setDblIdProtocoloAtividade($objDocumentoDTO->getDblIdProcedimento());
			$objAtributoAndamentoDTO->setNumIdUsuarioOrigemAtividade(SessaoSEI::getInstance()->getNumIdUsuario());
			$objAtributoAndamentoDTO->setNumIdUnidadeOrigemAtividade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
			$objAtributoAndamentoDTO->setNumIdTarefaAtividade(array(TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA,
                                                              TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA_CASSADA,
                                                              TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA_UTILIZADA),InfraDTO::$OPER_IN);
			
			$objAtributoAndamentoRN = new AtributoAndamentoRN();
			$arrObjAtributoAndamentoDTO = $objAtributoAndamentoRN->listarRN1367($objAtributoAndamentoDTO);

    	if (count($arrObjAtributoAndamentoDTO)){
    		
    		$arrIdAtividade = InfraArray::converterArrInfraDTO($arrObjAtributoAndamentoDTO,'IdAtividade');
    		
				$objAtividadeDTO = new AtividadeDTO();
				$objAtividadeDTO->retNumIdAtividade();
				$objAtividadeDTO->retStrSiglaUsuario();
				$objAtividadeDTO->retStrNomeUsuario();
				$objAtividadeDTO->retStrSiglaUnidade();
				$objAtividadeDTO->retStrDescricaoUnidade();
				$objAtividadeDTO->retDthAbertura();
				$objAtividadeDTO->retNumIdTarefa();
				$objAtividadeDTO->setNumIdAtividade($arrIdAtividade,InfraDTO::$OPER_IN);
	
				$objAtividadeDTO->setOrdNumIdAtividade(InfraDTO::$TIPO_ORDENACAO_DESC);
				
				$arrObjAtividadeDTO = $this->listarRN0036($objAtividadeDTO);


				//busca todos os atributos de andamento das atividades
				$objAtributoAndamentoDTO2 = new AtributoAndamentoDTO();
				$objAtributoAndamentoDTO2->retNumIdAtividade();
				$objAtributoAndamentoDTO2->retStrNome();
				$objAtributoAndamentoDTO2->retStrValor();
				$objAtributoAndamentoDTO2->setNumIdAtividade($arrIdAtividade,InfraDTO::$OPER_IN);

				$arr = InfraArray::indexarArrInfraDTO($objAtributoAndamentoRN->listarRN1367($objAtributoAndamentoDTO2),'IdAtividade',true);
				
				foreach($arrObjAtividadeDTO as $objAtividadeDTO){
					if (isset($arr[$objAtividadeDTO->getNumIdAtividade()])){
						$objAtividadeDTO->setArrObjAtributoAndamentoDTO($arr[$objAtividadeDTO->getNumIdAtividade()]);
					}else{
						$objAtividadeDTO->setArrObjAtributoAndamentoDTO(array());
					}
				}
				
    	}else{
    		$arrObjAtividadeDTO = array();
    	}			
    	
			return $arrObjAtividadeDTO;
			
    }catch(Exception $e){
      throw new InfraException('Erro listando credenciais de assinatura.',$e);
    }
  }

  protected function concederCredencialAssinaturaControlado(ConcederCredencialAssinaturaDTO $parObjConcederCredencialAssinaturaDTO) {

  	try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('credencial_assinatura_conceder',__METHOD__,$parObjConcederCredencialAssinaturaDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();
      
      //verifica se não houve mudança nas atividades abertas
      $arrObjAtividadeDTOOrigem = $parObjConcederCredencialAssinaturaDTO->getArrAtividadesOrigem();
      $arrIdAtividadesOrigem = InfraArray::converterArrInfraDTO($arrObjAtividadeDTOOrigem,'IdAtividade');
      
      $this->validarAndamentosAtuais(array($parObjConcederCredencialAssinaturaDTO->getDblIdProcedimento()), $arrIdAtividadesOrigem, $objInfraException);

			$objInfraException->lancarValidacoes();

      //dados do processo
    	$objProcedimentoDTO = new ProcedimentoDTO();
    	$objProcedimentoDTO->retDblIdProcedimento();
    	$objProcedimentoDTO->retStrStaNivelAcessoGlobalProtocolo();
    	$objProcedimentoDTO->retStrProtocoloProcedimentoFormatado();
    	$objProcedimentoDTO->retStrNomeTipoProcedimento();
    	$objProcedimentoDTO->setDblIdProcedimento($parObjConcederCredencialAssinaturaDTO->getDblIdProcedimento());
    		
    	$objProcedimentoRN = new ProcedimentoRN();
   		$objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);

      //dados do documento
    	$objDocumentoDTO = new DocumentoDTO();
    	$objDocumentoDTO->retDblIdDocumento();
    	$objDocumentoDTO->retDblIdProcedimento();
    	$objDocumentoDTO->retStrProtocoloDocumentoFormatado();
    	$objDocumentoDTO->retStrNomeSerie();
    	$objDocumentoDTO->setDblIdDocumento($parObjConcederCredencialAssinaturaDTO->getDblIdDocumento());
    		
    	$objDocumentoRN = new DocumentoRN();
   		$objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
   		
  		//dados do usuário
  		$objUsuarioDTO = new UsuarioDTO();
  		$objUsuarioDTO->retNumIdUsuario();
  		$objUsuarioDTO->retStrSigla();
  		$objUsuarioDTO->retStrNome();
  		$objUsuarioDTO->setNumIdUsuario($parObjConcederCredencialAssinaturaDTO->getNumIdUsuario());

  		$objUsuarioRN = new UsuarioRN();
  		$objUsuarioDTO = $objUsuarioRN->consultarRN0489($objUsuarioDTO);

  		//dados da unidade  		
  		$objUnidadeDTO = new UnidadeDTO();
  		$objUnidadeDTO->retNumIdUnidade();
  		$objUnidadeDTO->retStrSigla();
  		$objUnidadeDTO->retStrDescricao();
  		$objUnidadeDTO->retStrSiglaOrgao();
  		$objUnidadeDTO->retStrDescricaoOrgao();
  		$objUnidadeDTO->setNumIdUnidade($parObjConcederCredencialAssinaturaDTO->getNumIdUnidade());
  		
  		$objUnidadeRN = new UnidadeRN();
  		$objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);

  		$objAssinaturaDTO = new AssinaturaDTO();
      $objAssinaturaDTO->retNumIdAssinatura();
  		$objAssinaturaDTO->setDblIdDocumento($parObjConcederCredencialAssinaturaDTO->getDblIdDocumento());
  		$objAssinaturaDTO->setNumIdUsuario($parObjConcederCredencialAssinaturaDTO->getNumIdUsuario());
      $objAssinaturaDTO->setNumMaxRegistrosRetorno(1);
  		
  		$objAssinaturaRN = new AssinaturaRN();
  		if ($objAssinaturaRN->consultarRN1322($objAssinaturaDTO) != null){
  		  $objInfraException->lancarValidacao('Usuário já assinou o documento.');
  		}
  		
		  if ($objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo()!=ProtocoloRN::$NA_SIGILOSO){
				$objInfraException->adicionarValidacao('Não é possível conceder credencial de assinatura para um processo não sigiloso ('.$objProcedimentoDTO->getStrProtocoloProcedimentoFormatado().').');
		  }

		  if ($objDocumentoDTO->getDblIdProcedimento()!=$parObjConcederCredencialAssinaturaDTO->getDblIdProcedimento()){
		  	$objInfraException->adicionarValidacao('Documento não pertence ao processo informado.');
		  }
		  
	    $objAcessoDTO = new AcessoDTO();
      $objAcessoDTO->retNumIdAcesso();
	    $objAcessoDTO->setDblIdProtocolo($parObjConcederCredencialAssinaturaDTO->getDblIdProcedimento());
	    $objAcessoDTO->setNumIdUsuario($parObjConcederCredencialAssinaturaDTO->getNumIdUsuario());
	    $objAcessoDTO->setNumIdUnidade($parObjConcederCredencialAssinaturaDTO->getNumIdUnidade());
	    $objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_PROCESSO);
      $objAcessoDTO->setNumMaxRegistrosRetorno(1);
	    
	    $objAcessoRN = new AcessoRN();
	    if ($objAcessoRN->consultar($objAcessoDTO) == null){
	    	$objConcederCredencialDTO = new ConcederCredencialDTO();
	      $objConcederCredencialDTO->setDblIdProcedimento($parObjConcederCredencialAssinaturaDTO->getDblIdProcedimento());
	      $objConcederCredencialDTO->setNumIdUsuario($parObjConcederCredencialAssinaturaDTO->getNumIdUsuario());
	      $objConcederCredencialDTO->setNumIdUnidade($parObjConcederCredencialAssinaturaDTO->getNumIdUnidade());
	      $objConcederCredencialDTO->setArrAtividadesOrigem($arrObjAtividadeDTOOrigem);
	      
	      $this->concederCredencial($objConcederCredencialDTO);
	    }

			$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
      $objAtributoAndamentoDTO->retNumIdAtributoAndamento();
			$objAtributoAndamentoDTO->setNumIdTarefaAtividade(TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA);
			$objAtributoAndamentoDTO->setDblIdProtocoloAtividade($parObjConcederCredencialAssinaturaDTO->getDblIdProcedimento());
			$objAtributoAndamentoDTO->setNumIdUsuarioAtividade($parObjConcederCredencialAssinaturaDTO->getNumIdUsuario());
			$objAtributoAndamentoDTO->setNumIdUnidadeAtividade($parObjConcederCredencialAssinaturaDTO->getNumIdUnidade());
			$objAtributoAndamentoDTO->setNumIdUsuarioOrigemAtividade(SessaoSEI::getInstance()->getNumIdUsuario());
			$objAtributoAndamentoDTO->setStrIdOrigem($parObjConcederCredencialAssinaturaDTO->getDblIdDocumento());
      $objAtributoAndamentoDTO->setNumMaxRegistrosRetorno(1);

			$objAtributoAndamentoRN = new AtributoAndamentoRN();
				
			if ($objAtributoAndamentoRN->consultarRN1366($objAtributoAndamentoDTO) != null){
				$objInfraException->adicionarValidacao('Usuário atual já concedeu credencial de assinatura neste documento ao usuário '.$objUsuarioDTO->getStrSigla().' na unidade '.$objUnidadeDTO->getStrSigla().'.');
    	}

    	$objEmailSistemaDTO = new EmailSistemaDTO();
    	$objEmailSistemaDTO->retStrDe();
    	$objEmailSistemaDTO->retStrPara();
    	$objEmailSistemaDTO->retStrAssunto();
    	$objEmailSistemaDTO->retStrConteudo();
    	$objEmailSistemaDTO->setNumIdEmailSistema(EmailSistemaRN::$ES_CONCESSAO_CREDENCIAL_ASSINATURA);
    		
    	$objEmailSistemaRN = new EmailSistemaRN();
    	$objEmailSistemaDTO = $objEmailSistemaRN->consultar($objEmailSistemaDTO);

			$arrObjEmailUnidadeDTO = array();
      if ($objEmailSistemaDTO!=null && $parObjConcederCredencialAssinaturaDTO->getNumIdUnidade() != SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
        
        //emails da unidade
        $objEmailUnidadeDTO = new EmailUnidadeDTO();
        $objEmailUnidadeDTO->retStrEmail();
        $objEmailUnidadeDTO->retStrDescricao();
        $objEmailUnidadeDTO->setNumIdUnidade($parObjConcederCredencialAssinaturaDTO->getNumIdUnidade());
        
        $objEmailUnidadeRN = new EmailUnidadeRN();
        $arrObjEmailUnidadeDTO = $objEmailUnidadeRN->listar($objEmailUnidadeDTO);
        
      	if (count($arrObjEmailUnidadeDTO)==0){
      		$objInfraException->adicionarValidacao('Unidade '.$objUnidadeDTO->getStrSigla().'/'.$objUnidadeDTO->getStrSiglaOrgao().' não possui email cadastrado.');
				}
      }
      
      $objInfraException->lancarValidacoes();

      	
      // Filtra campos do DTO
      $objAtividadeDTO = new AtividadeDTO();
      $objAtividadeDTO->setDblIdProtocolo($parObjConcederCredencialAssinaturaDTO->getDblIdProcedimento());
      $objAtividadeDTO->setNumIdUnidade($parObjConcederCredencialAssinaturaDTO->getNumIdUnidade());
      $objAtividadeDTO->setNumIdUnidadeOrigem(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      $objAtividadeDTO->setNumIdUsuario($parObjConcederCredencialAssinaturaDTO->getNumIdUsuario());
      $objAtividadeDTO->setNumIdUsuarioOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
      $objAtividadeDTO->setDtaPrazo(null);
      $objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA);

    	$arrObjAtributoAndamentoDTO = array();
    		 
    	$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
    	$objAtributoAndamentoDTO->setStrNome('USUARIO');
    	$objAtributoAndamentoDTO->setStrValor($objUsuarioDTO->getStrSigla().'¥'.$objUsuarioDTO->getStrNome());
    	$objAtributoAndamentoDTO->setStrIdOrigem($objAtividadeDTO->getNumIdUsuario());
    	$arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;

    	$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
    	$objAtributoAndamentoDTO->setStrNome('DOCUMENTO');
    	$objAtributoAndamentoDTO->setStrValor($objDocumentoDTO->getStrProtocoloDocumentoFormatado());
    	$objAtributoAndamentoDTO->setStrIdOrigem($objDocumentoDTO->getDblIdDocumento());
    	$arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;

    	$objAtividadeDTO->setArrObjAtributoAndamentoDTO($arrObjAtributoAndamentoDTO);

    	$objAcessoDTO = new AcessoDTO();
    	$objAcessoDTO->retNumIdAcesso();
    	$objAcessoDTO->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
    	$objAcessoDTO->setNumIdUsuario($objAtividadeDTO->getNumIdUsuario());
    	$objAcessoDTO->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade());
      $objAcessoDTO->setNumIdControleInterno(null);
    	$objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_ASSINATURA_PROCESSO);
      $objAcessoDTO->setNumMaxRegistrosRetorno(1);

    	$objAcessoRN = new AcessoRN();

    	//se o usuário ainda não possui acesso ao processo
    	if ($objAcessoRN->consultar($objAcessoDTO)==null){

        $objAcessoDTO->setNumIdAcesso(null);
    		$objAcessoRN->cadastrar($objAcessoDTO);

				$objAcessoDTO = new AcessoDTO();
        $objAcessoDTO->setNumIdAcesso(null);
				$objAcessoDTO->setDblIdProtocolo($parObjConcederCredencialAssinaturaDTO->getDblIdDocumento());
				$objAcessoDTO->setNumIdUsuario($objAtividadeDTO->getNumIdUsuario());
				$objAcessoDTO->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade());
				$objAcessoDTO->setNumIdControleInterno(null);
			  $objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_ASSINATURA_DOCUMENTO);
				$objAcessoRN->cadastrar($objAcessoDTO);

      }else{

	    	$objAcessoDTO = new AcessoDTO();
        $objAcessoDTO->retNumIdAcesso();
	    	$objAcessoDTO->setDblIdProtocolo($parObjConcederCredencialAssinaturaDTO->getDblIdDocumento());
	    	$objAcessoDTO->setNumIdUsuario($parObjConcederCredencialAssinaturaDTO->getNumIdUsuario());
	    	$objAcessoDTO->setNumIdUnidade($parObjConcederCredencialAssinaturaDTO->getNumIdUnidade());
				$objAcessoDTO->setNumIdControleInterno(null);
	    	$objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_ASSINATURA_DOCUMENTO);
        $objAcessoDTO->setNumMaxRegistrosRetorno(1);

	    	if ($objAcessoRN->consultar($objAcessoDTO)==null){
          $objAcessoDTO->setNumIdAcesso(null);
	    		$objAcessoRN->cadastrar($objAcessoDTO);
        }
      }
    	
   		$ret = $this->gerarInternaRN0727($objAtividadeDTO);

    	if ($objEmailSistemaDTO!=null && $objAtividadeDTO->getNumIdUnidade()!=SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
    	  
    	  $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
    	  $strEmailSistema = $objInfraParametro->getValor('SEI_EMAIL_SISTEMA');
    	   
			  $strDe = $objEmailSistemaDTO->getStrDe();
			  $strDe = str_replace('@email_sistema@',$strEmailSistema,$strDe);
			  $strDe = str_replace('@sigla_sistema@',SessaoSEI::getInstance()->getStrSiglaSistema(),$strDe);
					  
			  $strEmailsUnidade = '';
			  foreach($arrObjEmailUnidadeDTO as $objEmailUnidadeDTO){
			  	$strEmailsUnidade .= $objEmailUnidadeDTO->getStrDescricao().' <'.$objEmailUnidadeDTO->getStrEmail().'> ;';			  	
			  }
			  $strEmailsUnidade = substr($strEmailsUnidade,0,-1);
					  
			  $strPara = $objEmailSistemaDTO->getStrPara();
			  $strPara = str_replace('@emails_unidade@',$strEmailsUnidade,$strPara);
					  
			  $strAssunto = $objEmailSistemaDTO->getStrAssunto();
			  $strAssunto = str_replace('@processo@',$objProcedimentoDTO->getStrProtocoloProcedimentoFormatado(),$strAssunto);
					  
			  $strConteudo = $objEmailSistemaDTO->getStrConteudo();
			  $strConteudo = str_replace('@processo@',$objProcedimentoDTO->getStrProtocoloProcedimentoFormatado(),$strConteudo);
			  $strConteudo = str_replace('@documento@',$objDocumentoDTO->getStrProtocoloDocumentoFormatado(),$strConteudo);
			  $strConteudo = str_replace('@sigla_usuario_credencial@',$objUsuarioDTO->getStrSigla(),$strConteudo);
			  $strConteudo = str_replace('@nome_usuario_credencial@',$objUsuarioDTO->getStrNome(),$strConteudo);
			  $strConteudo = str_replace('@sigla_unidade_credencial@',$objUnidadeDTO->getStrSigla(),$strConteudo);
			  $strConteudo = str_replace('@descricao_unidade_credencial@',$objUnidadeDTO->getStrDescricao(),$strConteudo);
			  $strConteudo = str_replace('@sigla_orgao_unidade_credencial@',$objUnidadeDTO->getStrSiglaOrgao(),$strConteudo);
			  $strConteudo = str_replace('@descricao_orgao_unidade_credencial@',$objUnidadeDTO->getStrDescricaoOrgao(),$strConteudo);
			  $strConteudo = str_replace('@sigla_sistema@',SessaoSEI::getInstance()->getStrSiglaSistema(),$strConteudo);
			  
        $objEmailDTO = new EmailDTO();
        $objEmailDTO->setStrDe($strDe);
        $objEmailDTO->setStrPara($strPara);
        $objEmailDTO->setStrAssunto($strAssunto);
        $objEmailDTO->setStrMensagem($strConteudo);

        EmailRN::processar(array($objEmailDTO));
    	}

      return $ret;
     
    }catch(Exception $e){
      throw new InfraException('Erro concedendo credencial de assinatura.',$e);
    }
  }

  protected function cassarCredencialAssinaturaControlado($parArrObjAtividadeDTO) {
    try{

      $objFinalizarCredencialDTO = new FinalizarCredencialDTO();
      $objFinalizarCredencialDTO->setNumIdTarefa(TarefaRN::$TI_CASSACAO_CREDENCIAL_ASSINATURA);
      $objFinalizarCredencialDTO->setArrObjAtividadeDTO($parArrObjAtividadeDTO);

      $this->finalizarCredencialAssinatura($objFinalizarCredencialDTO);

    }catch(Exception $e){
      throw new InfraException('Erro cassando credencial de assinatura.',$e);
    }
  }

  protected function concluirCredencialAssinaturaControlado($arrObjDocumentoDTO) {
  	try{

			$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
			$objAtributoAndamentoDTO->retNumIdAtividade();
			$objAtributoAndamentoDTO->setStrIdOrigem(InfraArray::converterArrInfraDTO($arrObjDocumentoDTO,'IdDocumento'),InfraDTO::$OPER_IN);
			//$objAtributoAndamentoDTO->setDblIdProtocoloAtividade($objDocumentoDTO->getDblIdProcedimento());
			$objAtributoAndamentoDTO->setNumIdUsuarioAtividade(SessaoSEI::getInstance()->getNumIdUsuario());
			$objAtributoAndamentoDTO->setNumIdUnidadeAtividade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
			$objAtributoAndamentoDTO->setNumIdTarefaAtividade(TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA);
			
			$objAtributoAndamentoRN = new AtributoAndamentoRN();
			$arrObjAtributoAndamentoDTO = $objAtributoAndamentoRN->listarRN1367($objAtributoAndamentoDTO);

			$arrObjAtividadeDTO = InfraArray::gerarArrInfraDTO('AtividadeDTO','IdAtividade',InfraArray::converterArrInfraDTO($arrObjAtributoAndamentoDTO,'IdAtividade'));
			
	  	$objFinalizarCredencialDTO = new FinalizarCredencialDTO();
	  	$objFinalizarCredencialDTO->setNumIdTarefa(null);
	  	$objFinalizarCredencialDTO->setArrObjAtividadeDTO($arrObjAtividadeDTO);
	  	
	  	$this->finalizarCredencialAssinatura($objFinalizarCredencialDTO);

  	}catch(Exception $e){
  		throw new InfraException('Erro concluindo credencial de assinatura.',$e);
  	}
  }

  private function finalizarCredencialAssinatura(FinalizarCredencialDTO $objFinalizarCredencialDTO) {
    try{
    	
    	$objInfraException = new InfraException();
    	
    	//recupera dados das atividades passadas
    	$objAtividadeDTO = new AtividadeDTO();
    	$objAtividadeDTO->retNumIdAtividade();
    	$objAtividadeDTO->retDblIdProtocolo();
    	$objAtividadeDTO->retNumIdTarefa();
    	$objAtividadeDTO->retStrProtocoloFormatadoProtocolo();
    	$objAtividadeDTO->retStrStaNivelAcessoGlobalProtocolo();
    	$objAtividadeDTO->retNumIdUsuarioOrigem();
    	$objAtividadeDTO->retNumIdUsuario();
    	$objAtividadeDTO->retNumIdUnidade();
    	$objAtividadeDTO->retStrSiglaUsuario();
    	$objAtividadeDTO->retStrNomeUsuario();
    	
    	$objAtividadeDTO->setNumIdAtividade(InfraArray::converterArrInfraDTO($objFinalizarCredencialDTO->getArrObjAtividadeDTO(),'IdAtividade'),InfraDTO::$OPER_IN);
    	
    	$arrObjAtividadeDTO = $this->listarRN0036($objAtividadeDTO);

    	$objAcessoRN = new AcessoRN();
    	
    	foreach($arrObjAtividadeDTO as $objAtividadeDTO){
	    	if ($objAtividadeDTO->getNumIdTarefa() != TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA){
	    		$objInfraException->adicionarValidacao('Andamento do processo '.$objAtividadeDTO->getStrProtocoloFormatadoProtocolo().' não é uma concessão de credencial de assinatura.');
	    	}
	
	    	//se cassando credencial de assinatura
	    	if ($objFinalizarCredencialDTO->getNumIdTarefa()==TarefaRN::$TI_CASSACAO_CREDENCIAL_ASSINATURA){
		    	if ($objAtividadeDTO->getNumIdUsuarioOrigem()!=SessaoSEI::getInstance()->getNumIdUsuario()){
		    		$objInfraException->adicionarValidacao('Credencial de assinatura no processo '.$objAtividadeDTO->getStrProtocoloFormatadoProtocolo().' não foi concedida pelo usuário atual.');
		    	}
		
		    	if ($objAtividadeDTO->getStrStaNivelAcessoGlobalProtocolo()!=ProtocoloRN::$NA_SIGILOSO){
		    		$objInfraException->adicionarValidacao('Não é possível cassar credencial de assinatura em um processo não sigiloso ('.$objAtividadeDTO->getStrProtocoloFormatadoProtocolo().').');
				  }
		    	
		    	$objAcessoDTO = new AcessoDTO();
          $objAcessoDTO->retNumIdAcesso();
		    	$objAcessoDTO->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
		    	$objAcessoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
		    	$objAcessoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
		    	$objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_PROCESSO);
          $objAcessoDTO->setNumMaxRegistrosRetorno(1);
		
		    	if ($objAcessoRN->consultar($objAcessoDTO) == null){
		    		$objInfraException->adicionarValidacao('Usuário atual não possui credencial de acesso ao processo '.$objAtividadeDTO->getStrProtocoloFormatadoProtocolo().' nesta unidade.');
		    	}
	    	}
	  	}
    	
    	$objInfraException->lancarValidacoes();

    	$strDataHoraAtual = InfraData::getStrDataHoraAtual();
    	$objAtividadeBD = new AtividadeBD($this->getObjInfraIBanco());
    	$objAtributoAndamentoRN = new AtributoAndamentoRN();
    	
    	foreach($arrObjAtividadeDTO as $objAtividadeDTO){

    		$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
    		$objAtributoAndamentoDTO->retStrNome();
    		$objAtributoAndamentoDTO->retStrValor();
    		$objAtributoAndamentoDTO->retStrIdOrigem();
    		$objAtributoAndamentoDTO->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
    		$objAtributoAndamentoDTO->setStrNome('DOCUMENTO');
    		$objAtributoAndamentoDTOCassado = $objAtributoAndamentoRN->consultarRN1366($objAtributoAndamentoDTO);
    		
    		//recupera outras atividades de concessao de credencial de assinatura no processo para o usuario e unidade
	  	 	$dto = new AtividadeDTO();
	  	 	$dto->retNumIdAtividade();
	    	$dto->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade(),InfraDTO::$OPER_DIFERENTE);
	    	$dto->setNumIdTarefa(TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA);
	    	$dto->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
	    	$dto->setNumIdUsuario($objAtividadeDTO->getNumIdUsuario());
	    	$dto->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade());
	
	    	$arr = $this->listarRN0036($dto);
	    	
	    	//se não tem outra credencial de assinatura no processo
	    	if (count($arr)==0){
	    		
	   			//recupera andamentos abertos no processo para o usuario na unidade
		      $objPesquisaPendenciaDTO = new PesquisaPendenciaDTO();
		      $objPesquisaPendenciaDTO->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
		      $objPesquisaPendenciaDTO->setNumIdUsuario($objAtividadeDTO->getNumIdUsuario());
		      $objPesquisaPendenciaDTO->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade());
			      
			    $arrObjProcedimentoDTO = $this->listarPendenciasRN0754($objPesquisaPendenciaDTO);
			      
			    if (count($arrObjProcedimentoDTO)==1){
			      	
			     	$arr = $arrObjProcedimentoDTO[0]->getArrObjAtividadeDTO();
		    			
		    		//se tem andamentos em aberto
		    		if (count($arr)){

			    		//recupera acesso por credencial no processo
					    $objAcessoDTO = new AcessoDTO();
              $objAcessoDTO->retNumIdAcesso();
					    $objAcessoDTO->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
					    $objAcessoDTO->setNumIdUsuario($objAtividadeDTO->getNumIdUsuario());
					    $objAcessoDTO->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade());
					    $objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_PROCESSO);
              $objAcessoDTO->setNumMaxRegistrosRetorno(1);

					    //se não tem credencial no processo
		    			if ($objAcessoRN->consultar($objAcessoDTO) == null){
		    			
			    			//conclui andamentos
			    			$this->concluirRN0726($arr);
			    				
			    			//lanca andamento registrando conclusao automatica
			    			$dto = new AtividadeDTO();
					      $dto->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
					      $dto->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade());
					      $dto->setNumIdUsuario($objAtividadeDTO->getNumIdUsuario());
					      $dto->setNumIdTarefa(TarefaRN::$TI_CONCLUSAO_AUTOMATICA_USUARIO);

                $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
                $objAtributoAndamentoDTO->setStrNome('USUARIO');
                $objAtributoAndamentoDTO->setStrValor($objAtividadeDTO->getStrSiglaUsuario().'¥'.$objAtividadeDTO->getStrNomeUsuario());
                $objAtributoAndamentoDTO->setStrIdOrigem($objAtividadeDTO->getNumIdUsuario());
                $dto->setArrObjAtributoAndamentoDTO(array($objAtributoAndamentoDTO));

	              $this->gerarInternaRN0727($dto);
		    			}
		    		}
			    }

			    //obter documentos do processo
			    $objDocumentoDTO = new DocumentoDTO();
			    $objDocumentoDTO->retDblIdDocumento();
			    $objDocumentoDTO->setDblIdProcedimento($objAtividadeDTO->getDblIdProtocolo());
	
			    $objDocumentoRN = new DocumentoRN();
			    $arrProtocolos = InfraArray::converterArrInfraDTO($objDocumentoRN->listarRN0008($objDocumentoDTO),'IdDocumento');
			    $arrProtocolos[] = $objAtividadeDTO->getDblIdProtocolo();

			    //exclui acessos disponibilizados pela credencial de assinatura
			    $objAcessoDTO = new AcessoDTO();
			    $objAcessoDTO->retNumIdAcesso();
			    $objAcessoDTO->setDblIdProtocolo($arrProtocolos,InfraDTO::$OPER_IN);
			    $objAcessoDTO->setNumIdUsuario($objAtividadeDTO->getNumIdUsuario());
			    $objAcessoDTO->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade());
			    $objAcessoDTO->setStrStaTipo(array(AcessoRN::$TA_CREDENCIAL_ASSINATURA_PROCESSO,AcessoRN::$TA_CREDENCIAL_ASSINATURA_DOCUMENTO),InfraDTO::$OPER_IN);
	
	     	  $objAcessoRN->excluir($objAcessoRN->listar($objAcessoDTO));

	    	}else{
	    		
          //recupera documentos disponibilizados pelas OUTRAS atividades de concessão de credencial de assinatura deste processo
	    		$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
	    		$objAtributoAndamentoDTO->retStrIdOrigem();
	    		$objAtributoAndamentoDTO->setNumIdAtividade(InfraArray::converterArrInfraDTO($arr,'IdAtividade'),InfraDTO::$OPER_IN);
	    		$objAtributoAndamentoDTO->setStrNome('DOCUMENTO');
	    		
	    		$arrIdDocumentoAssinaturaOutros = InfraArray::indexarArrInfraDTO($objAtributoAndamentoRN->listarRN1367($objAtributoAndamentoDTO),'IdOrigem');

    			//se não existe mais nenhuma credencial de assinatura no mesmo documento
    			if (!isset($arrIdDocumentoAssinaturaOutros[$objAtributoAndamentoDTOCassado->getStrIdOrigem()])){  

    				//excluir o acesso especial devido a credencial de assinatura
				    $objAcessoDTO = new AcessoDTO();
				    $objAcessoDTO->retNumIdAcesso();
				    $objAcessoDTO->setDblIdProtocolo($objAtributoAndamentoDTOCassado->getStrIdOrigem());
				    $objAcessoDTO->setNumIdUsuario($objAtividadeDTO->getNumIdUsuario());
				    $objAcessoDTO->setNumIdUnidade($objAtividadeDTO->getNumIdUnidade());
				    $objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_ASSINATURA_DOCUMENTO);
				    
				    $objAcessoRN->excluir($objAcessoRN->listar($objAcessoDTO));
    			}
	    	}
    		
	    	$numIdAtividadeRelacionada = null;
	    	
	    	//se cassando credencial de assinatura
	    	if ($objFinalizarCredencialDTO->getNumIdTarefa()==TarefaRN::$TI_CASSACAO_CREDENCIAL_ASSINATURA){

	    		//lança andamento para o usuário atual registrando a cassação de credencial
	        $dto = new AtividadeDTO();
	        $dto->setDblIdProtocolo($objAtividadeDTO->getDblIdProtocolo());
	        $dto->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
	        $dto->setNumIdUnidadeOrigem(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
	        $dto->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
	        $dto->setNumIdUsuarioOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
	        $dto->setDtaPrazo(null);
	        	
	    		$arrObjAtributoAndamentoDTO = array();
	    		 
	    		$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
	    		$objAtributoAndamentoDTO->setStrNome('USUARIO');
	    		$objAtributoAndamentoDTO->setStrValor($objAtividadeDTO->getStrSiglaUsuario().'¥'.$objAtividadeDTO->getStrNomeUsuario());
	    		$objAtributoAndamentoDTO->setStrIdOrigem($objAtividadeDTO->getNumIdUsuario());
	    		$arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;
	        
	    		//adiciona atributo DOCUMENTO
	    		$arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTOCassado;
	    		
	    		$dto->setArrObjAtributoAndamentoDTO($arrObjAtributoAndamentoDTO);
	    		$dto->setNumIdTarefa($objFinalizarCredencialDTO->getNumIdTarefa());
	    		 
	    		$ret = $this->gerarInternaRN0727($dto);
	    		
	    		$numIdAtividadeRelacionada = $ret->getNumIdAtividade();
          $numIdTarefaLancamento = TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA_CASSADA;

	    	}else{
          $numIdTarefaLancamento = TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA_UTILIZADA;
        }

    		//substitui andamento original
    		$dto = new AtividadeDTO();
   		  $dto->setNumIdTarefa($numIdTarefaLancamento);
    		$dto->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
    		$objAtividadeBD->alterar($dto);
    		
    		//lanca atributo no andamento original
    		$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
    		$objAtributoAndamentoDTO->setStrNome('DATA_HORA');
    		$objAtributoAndamentoDTO->setStrValor($strDataHoraAtual);
    		$objAtributoAndamentoDTO->setStrIdOrigem($numIdAtividadeRelacionada);
    		$objAtributoAndamentoDTO->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
    		$objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);
    	}
    	
    	return InfraArray::gerarArrInfraDTO('ProtocoloDTO','IdProtocolo',array_unique(InfraArray::converterArrInfraDTO($arrObjAtividadeDTO,'IdProtocolo')));
    	
    }catch(Exception $e){
      throw new InfraException('Erro finalizando credenciais de assinatura.',$e);
    }
  }
  
  protected function renunciarCredenciaisControlado(ProcedimentoDTO $objProcedimentoDTO) {
    try{
    	
    	$objInfraException = new InfraException();


      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->retStrProtocoloFormatado();
      $objProtocoloDTO->retStrStaNivelAcessoGlobal();
      $objProtocoloDTO->setDblIdProtocolo($objProcedimentoDTO->getDblIdProcedimento());
      
      $objProtocoloRN = new ProtocoloRN();
      $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

    	if ($objProtocoloDTO->getStrStaNivelAcessoGlobal()!=ProtocoloRN::$NA_SIGILOSO){
				$objInfraException->adicionarValidacao('Não é possível renunciar credenciais para um processo não sigiloso ('.$objProtocoloDTO->getStrProtocoloFormatado().').');
			}    	
      
    	$objAcessoDTO = new AcessoDTO();
      $objAcessoDTO->retNumIdAcesso();
     	$objAcessoDTO->setDblIdProtocolo($objProcedimentoDTO->getDblIdProcedimento());
     	$objAcessoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
     	$objAcessoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
     	$objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_PROCESSO);
      $objAcessoDTO->setNumMaxRegistrosRetorno(1);

     	$objAcessoRN = new AcessoRN();
     	if ($objAcessoRN->consultar($objAcessoDTO) == null){
     		$objInfraException->lancarValidacao('Usuário atual não possui credencial de acesso ao processo '.$objProtocoloDTO->getStrProtocoloFormatado().' nesta unidade.');
     	}

     	$objAcessoDTO = new AcessoDTO();
      $objAcessoDTO->retNumIdAcesso();
     	$objAcessoDTO->setDblIdProtocolo($objProcedimentoDTO->getDblIdProcedimento());
     	$objAcessoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario(),InfraDTO::$OPER_DIFERENTE);
     	$objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_PROCESSO);
      $objAcessoDTO->setNumMaxRegistrosRetorno(1);
     	
     	if ($objAcessoRN->consultar($objAcessoDTO) == null){
     		$objInfraException->lancarValidacao('Não é possível renunciar a credencial porque o usuário atual é o único com acesso ao processo.');
     	}
     	
     	$objDocumentoDTO = new DocumentoDTO();
     	$objDocumentoDTO->retDblIdDocumento();
     	$objDocumentoDTO->retStrProtocoloDocumentoFormatado();
     	$objDocumentoDTO->retStrStaDocumento();
      $objDocumentoDTO->retNumIdTipoFormulario();
     	$objDocumentoDTO->retDblIdDocumentoEdoc();
     	$objDocumentoDTO->setStrStaProtocoloProtocolo(ProtocoloRN::$TP_DOCUMENTO_GERADO);
     	$objDocumentoDTO->setStrStaEstadoProtocolo(ProtocoloRN::$TE_DOCUMENTO_CANCELADO,InfraDTO::$OPER_DIFERENTE);  
     	//$objDocumentoDTO->setNumIdUsuarioGeradorProtocolo(SessaoSEI::getInstance()->getNumIdUsuario());
     	$objDocumentoDTO->setNumIdUnidadeGeradoraProtocolo(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
     	$objDocumentoDTO->setDblIdProcedimento($objProcedimentoDTO->getDblIdProcedimento());
     	
     	$objDocumentoRN = new DocumentoRN();
     	$arrObjDocumentoDTO = $objDocumentoRN->listarRN0008($objDocumentoDTO);

			if (count($arrObjDocumentoDTO)) {

				$objAcessoDTO = new AcessoDTO();
        $objAcessoDTO->retNumIdAcesso();
				$objAcessoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
				$objAcessoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario(),InfraDTO::$OPER_DIFERENTE);
				$objAcessoDTO->setDblIdProtocolo($objProcedimentoDTO->getDblIdProcedimento());
				$objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_PROCESSO);
        $objAcessoDTO->setNumMaxRegistrosRetorno(1);

				if ($objAcessoRN->consultar($objAcessoDTO) == null){

					$objAssinaturaRN = new AssinaturaRN();

					foreach ($arrObjDocumentoDTO as $objDocumentoDTO) {

						if ($objDocumentoRN->verificarConteudoGerado($objDocumentoDTO)) {

							$objAssinaturaDTO = new AssinaturaDTO();
              $objAssinaturaDTO->retNumIdAssinatura();
							$objAssinaturaDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
              $objAssinaturaDTO->setNumMaxRegistrosRetorno(1);

							if ($objAssinaturaRN->consultarRN1322($objAssinaturaDTO) == null) {
								$objInfraException->lancarValidacao('Não é possível renunciar a credencial porque o documento ' . $objDocumentoDTO->getStrProtocoloDocumentoFormatado() . ' foi gerado na unidade e ainda não foi assinado.\n\nÉ necessário antes realizar uma das operações abaixo:\n1) assinar o documento\n2) excluir o documento\n3) conceder credencial no processo para outro usuário na unidade');
							}
						}
					}
				}
			}
     	
    	$objInfraException->lancarValidacoes();

    	$strDataHoraAtual = InfraData::getStrDataHoraAtual();

    	//lanca andamento concluindo processo na unidade
    	$objAtividadeDTO = new AtividadeDTO();
    	$objAtividadeDTO->setDblIdProtocolo($objProcedimentoDTO->getDblIdProcedimento());
    	$objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
    	$objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_PROCESSO_RENUNCIA_CREDENCIAL);
       
      $ret = $this->gerarInternaRN0727($objAtividadeDTO);

      //atualiza andamentos de concessão ou transferência para renúncia
    	$objAtividadeDTO = new AtividadeDTO();
    	$objAtividadeDTO->retNumIdAtividade();
    	$objAtividadeDTO->retNumIdTarefa();
    	$objAtividadeDTO->setDblIdProtocolo($objProcedimentoDTO->getDblIdProcedimento());
   		$objAtividadeDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
   		$objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      $objAtividadeDTO->setNumIdTarefa(TarefaRN::getArrTarefasConcessaoCredencial(false), InfraDTO::$OPER_IN);
    	
    	$arrObjAtividadeDTO = $this->listarRN0036($objAtividadeDTO);
      
    	$objAtributoAndamentoRN = new AtributoAndamentoRN();

      $objAtividadeBD = new AtividadeBD($this->getObjInfraIBanco());

    	foreach($arrObjAtividadeDTO as $objAtividadeDTO){
    		
    		$dto = new AtividadeDTO();

        $numIdTarefaRenuncia = null;
    		if ($objAtividadeDTO->getNumIdTarefa()==TarefaRN::$TI_PROCESSO_CONCESSAO_CREDENCIAL){
          $numIdTarefaRenuncia = TarefaRN::$TI_PROCESSO_CONCESSAO_CREDENCIAL_RENUNCIADA;
    		}else if ($objAtividadeDTO->getNumIdTarefa()==TarefaRN::$TI_PROCESSO_TRANSFERENCIA_CREDENCIAL){
          $numIdTarefaRenuncia = TarefaRN::$TI_PROCESSO_TRANSFERENCIA_CREDENCIAL_RENUNCIADA;
    		}else if ($objAtividadeDTO->getNumIdTarefa()==TarefaRN::$TI_PROCESSO_ATIVACAO_CREDENCIAL){
          $numIdTarefaRenuncia = TarefaRN::$TI_PROCESSO_ATIVACAO_CREDENCIAL_RENUNCIADA;
        }
        $dto->setNumIdTarefa($numIdTarefaRenuncia);

    		$dto->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
    		$objAtividadeBD->alterar($dto);

    		$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
    		$objAtributoAndamentoDTO->setStrNome('DATA_HORA');
    		$objAtributoAndamentoDTO->setStrValor($strDataHoraAtual);
    		$objAtributoAndamentoDTO->setStrIdOrigem($ret->getNumIdAtividade()); //id do andamento que causou a anulação 
    		$objAtributoAndamentoDTO->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
    		$objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);
    	}

			$objAcessoDTO = new AcessoDTO();
			$objAcessoDTO->retNumIdAcesso();
			$objAcessoDTO->setDblIdProtocolo($objProcedimentoDTO->getDblIdProcedimento());
			$objAcessoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
			$objAcessoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
			$objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_PROCESSO);

			$objAcessoRN->excluir($objAcessoRN->listar($objAcessoDTO));

      //anula credenciais de assinatura (se existirem)
      $objAtividadeDTO = new AtividadeDTO();
      $objAtividadeDTO->retNumIdAtividade();
      $objAtividadeDTO->setDblIdProtocolo($objProcedimentoDTO->getDblIdProcedimento());
      $objAtividadeDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
      $objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      $objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA);

      $arrObjAtividadeDTO = $this->listarRN0036($objAtividadeDTO);

      if (count($arrObjAtividadeDTO)) {

        //atualiza andamentos de concessão de credencial de assinatura para anulada
        foreach ($arrObjAtividadeDTO as $objAtividadeDTO) {

          $dto = new AtividadeDTO();
          $dto->setNumIdTarefa(TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA_ANULADA);
          $dto->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
          $objAtividadeBD->alterar($dto);

          $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
          $objAtributoAndamentoDTO->setStrNome('USUARIO_ANULACAO');
          $objAtributoAndamentoDTO->setStrValor(SessaoSEI::getInstance()->getStrSiglaUsuario() . '¥' . SessaoSEI::getInstance()->getStrNomeUsuario());
          $objAtributoAndamentoDTO->setStrIdOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
          $objAtributoAndamentoDTO->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
          $objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);

          $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
          $objAtributoAndamentoDTO->setStrNome('DATA_HORA');
          $objAtributoAndamentoDTO->setStrValor($strDataHoraAtual);
          $objAtributoAndamentoDTO->setStrIdOrigem($ret->getNumIdAtividade()); //id do andamento que causou a anulação
          $objAtributoAndamentoDTO->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
          $objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);
        }

				//obter documentos do processo
				$objDocumentoDTO = new DocumentoDTO();
				$objDocumentoDTO->retDblIdDocumento();
				$objDocumentoDTO->setDblIdProcedimento($objProcedimentoDTO->getDblIdProcedimento());

				$objDocumentoRN = new DocumentoRN();
				$arrProtocolos = InfraArray::converterArrInfraDTO($objDocumentoRN->listarRN0008($objDocumentoDTO),'IdDocumento');
				$arrProtocolos[] = $objProcedimentoDTO->getDblIdProcedimento();

        $objAcessoDTO = new AcessoDTO();
        $objAcessoDTO->retNumIdAcesso();
        $objAcessoDTO->setDblIdProtocolo($arrProtocolos, InfraDTO::$OPER_IN);
        $objAcessoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
        $objAcessoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objAcessoDTO->setStrStaTipo(array(AcessoRN::$TA_CREDENCIAL_ASSINATURA_PROCESSO, AcessoRN::$TA_CREDENCIAL_ASSINATURA_DOCUMENTO), InfraDTO::$OPER_IN);
        $objAcessoRN->excluir($objAcessoRN->listar($objAcessoDTO));

      }

      return $ret;
    	
    }catch(Exception $e){
      throw new InfraException('Erro renunciando credenciais na unidade.',$e);
    }
  }

	protected function listarUnidadesTramitacaoControlado(ProcedimentoDTO $objProcedimentoDTO){
		try{

			$objAtividadeDTO = new AtividadeDTO();
			$objAtividadeDTO->setDistinct(true);
			$objAtividadeDTO->retNumIdUnidade();
			$objAtividadeDTO->retStrSiglaUnidade();
			$objAtividadeDTO->retStrDescricaoUnidade();

			$objAtividadeDTO->setNumIdTarefa(TarefaRN::getArrTarefasTramitacao(), InfraDTO::$OPER_IN);

			$objAtividadeDTO->setDblIdProtocolo($objProcedimentoDTO->getDblIdProcedimento());

			$objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual(),InfraDTO::$OPER_DIFERENTE);

			$objAtividadeDTO->setOrdStrSiglaUnidade(InfraDTO::$TIPO_ORDENACAO_ASC);

			$arrObjAtividadeDTO = $this->listarRN0036($objAtividadeDTO);

			foreach($arrObjAtividadeDTO as $objAtividadeDTO){
				$objAtividadeDTO->setDtaPrazo(null);
			}

			if (count($arrObjAtividadeDTO)>0){

				$arrObjAtividadeDTO = InfraArray::indexarArrInfraDTO($arrObjAtividadeDTO,'IdUnidade');

				$arrIdUnidade=InfraArray::converterArrInfraDTO($arrObjAtividadeDTO,'IdUnidade');

				//Acessar os retornos programados para a unidade atual
				$objRetornoProgramadoDTO = new RetornoProgramadoDTO();
				$objRetornoProgramadoDTO->setNumFiltroFkAtividadeRetorno(InfraDTO::$FILTRO_FK_WHERE);
				$objRetornoProgramadoDTO->retNumIdUnidade();
				$objRetornoProgramadoDTO->retDtaProgramada();
				$objRetornoProgramadoDTO->setNumIdUnidade($arrIdUnidade,InfraDTO::$OPER_IN);
				$objRetornoProgramadoDTO->setDblIdProtocoloAtividadeEnvio($objProcedimentoDTO->getDblIdProcedimento());
				$objRetornoProgramadoDTO->setNumIdUnidadeAtividadeEnvio(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
				$objRetornoProgramadoDTO->setNumIdUnidadeAtividadeRetorno(null);

				$objRetornoProgramadoRN = new RetornoProgramadoRN();
				$arrObjRetornoProgramadoDTO = $objRetornoProgramadoRN->listar($objRetornoProgramadoDTO);

				foreach ($arrObjRetornoProgramadoDTO as $objRetornoProgramadoDTO) {
					$arrObjAtividadeDTO[$objRetornoProgramadoDTO->getNumIdUnidade()]->setDtaPrazo($objRetornoProgramadoDTO->getDtaProgramada());
				}
			}

			return $arrObjAtividadeDTO;

		}catch(Exception $e){
			throw new InfraException('Erro listando unidades de tramitação.',$e);
		}
	}

  protected function listarPendenciasPorMarcadoresConectado(PesquisaPendenciaDTO $objPesquisaPendenciaDTO){

    try{

      $objAtividadeDTO = new AtividadeDTO();
      $objAtividadeDTO->setDistinct(true);
      $objAtividadeDTO->retDblIdProtocolo();
      $objAtividadeDTO->retNumIdMarcador();

      $objAtividadeDTO->setNumIdUnidade($objPesquisaPendenciaDTO->getNumIdUnidade());
      $objAtividadeDTO->setDthConclusao(null);
      $objAtividadeDTO->setStrStaProtocoloProtocolo(ProtocoloRN::$TP_PROCEDIMENTO);
      $objAtividadeDTO->setStrStaEstadoProtocolo(ProtocoloRN::$TE_NORMAL);

      $objAtividadeDTO->adicionarCriterio(array('StaNivelAcessoGlobalProtocolo','IdUsuario'),
                                          array(InfraDTO::$OPER_DIFERENTE, InfraDTO::$OPER_IGUAL),
                                          array(ProtocoloRN::$NA_SIGILOSO, $objPesquisaPendenciaDTO->getNumIdUsuario()),
                                          array(InfraDTO::$OPER_LOGICO_OR));

      if ($objPesquisaPendenciaDTO->getStrStaTipoAtribuicao()==self::$TA_MINHAS){
        $objAtividadeDTO->setNumIdUsuarioAtribuicao($objPesquisaPendenciaDTO->getNumIdUsuario());
      }

      $objAtividadeDTO->setNumTipoFkAndamentoMarcador(InfraDTO::$TIPO_FK_OBRIGATORIA);
      $objAtividadeDTO->setNumIdUnidadeMarcador($objPesquisaPendenciaDTO->getNumIdUnidade());
      $objAtividadeDTO->setStrSinUltimoAndamentoMarcador('S');

      $arrObjAtividadeDTO = $this->listarRN0036($objAtividadeDTO);

      $arrMarcadores = array();
      foreach($arrObjAtividadeDTO as $objAtividadeDTO){

        if (!isset($arrMarcadores[$objAtividadeDTO->getNumIdMarcador()])){
          $arrMarcadores[$objAtividadeDTO->getNumIdMarcador()] = 1;
        }else{
          $arrMarcadores[$objAtividadeDTO->getNumIdMarcador()]++;
        }
      }

      $arrObjMarcadorDTO = array();

      if (count($arrMarcadores)){

        $objMarcadorDTO = new MarcadorDTO();
        $objMarcadorDTO->setBolExclusaoLogica(false);
        $objMarcadorDTO->retNumIdMarcador();
        $objMarcadorDTO->retStrNome();
        $objMarcadorDTO->retStrStaIcone();
        $objMarcadorDTO->retStrSinAtivo();
        $objMarcadorDTO->setNumIdMarcador(array_keys($arrMarcadores), InfraDTO::$OPER_IN);
        $objMarcadorDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

        $objMarcadorRN = new MarcadorRN();
        $arrObjMarcadorDTO = $objMarcadorRN->listar($objMarcadorDTO);

        $arrObjIconeMarcadorDTO = InfraArray::indexarArrInfraDTO($objMarcadorRN->listarValoresIcone(),'StaIcone');

        foreach($arrObjMarcadorDTO as $dto){
          $dto->setStrNome(MarcadorINT::formatarMarcadorDesativado($dto->getStrNome(),$dto->getStrSinAtivo()));
          $dto->setNumQuantidade($arrMarcadores[$dto->getNumIdMarcador()]);
          $dto->setStrArquivoIcone($arrObjIconeMarcadorDTO[$dto->getStrStaIcone()]->getStrArquivo());
        }
      }

      return $arrObjMarcadorDTO;

    }catch(Exception $e){
      throw new InfraException('Erro recuperando processos abertos por marcadores.',$e);
    }
  }

}
?>