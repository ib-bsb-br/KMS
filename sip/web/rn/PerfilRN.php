<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 06/12/2006 - criado por mga
*
*
*/

require_once dirname(__FILE__).'/../Sip.php';


class PerfilRN extends InfraRN {

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSip::getInstance();
  }

  protected function clonarControlado(ClonarPerfilDTO $objClonarPerfilDTO) {
    try{

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('perfil_clonar',__METHOD__,$objClonarPerfilDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      if (InfraString::isBolVazia($objClonarPerfilDTO->getNumIdOrgaoSistema())){
        $objInfraException->adicionarValidacao('Órgão do Sistema não informado.');
      }

      if (InfraString::isBolVazia($objClonarPerfilDTO->getNumIdSistema())){
        $objInfraException->adicionarValidacao('Sistema não informado.');
      }

      if (InfraString::isBolVazia($objClonarPerfilDTO->getNumIdPerfilOrigem())){
        $objInfraException->adicionarValidacao('Perfil de Origem não informado.');
      }
			
      if (InfraString::isBolVazia($objClonarPerfilDTO->getStrPerfilDestino())){
        $objInfraException->adicionarValidacao('Perfil Destino não informado.');
      }
      
			$dto = new PerfilDTO();
			$dto->setNumIdSistema($objClonarPerfilDTO->getNumIdSistema());
			$dto->setStrNome($objClonarPerfilDTO->getStrPerfilDestino());
			if ($this->contar($dto)>0){
			  $objInfraException->adicionarValidacao('Já existe um perfil neste sistema com este Nome de Destino.');
			}

      $objInfraException->lancarValidacoes();

      //Consulta perfil origem
      $objPerfilDTO = new PerfilDTO();
      $objPerfilDTO->retTodos();
			$objPerfilDTO->setNumIdSistema($objClonarPerfilDTO->getNumIdSistema());
			$objPerfilDTO->setNumIdPerfil($objClonarPerfilDTO->getNumIdPerfilOrigem());

      $objClonarPerfilDTO->setObjPerfilDTO($this->consultar($objPerfilDTO));
			
			//Le dados para o Sistema Origem
      //Recursos do perfil
      $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
      $objRelPerfilRecursoDTO->retTodos();
      $objRelPerfilRecursoDTO->setNumIdSistema($objClonarPerfilDTO->getObjPerfilDTO()->getNumIdSistema());
      $objRelPerfilRecursoDTO->setNumIdPerfil($objClonarPerfilDTO->getObjPerfilDTO()->getNumIdPerfil());
      $objRelPerfilRecursoRN = new RelPerfilRecursoRN();
      $objClonarPerfilDTO->setArrObjRelPerfilRecursoDTO($objRelPerfilRecursoRN->listar($objRelPerfilRecursoDTO));
			
      //Itens de menu do perfil
      $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
      $objRelPerfilItemMenuDTO->retTodos();
      $objRelPerfilItemMenuDTO->setNumIdSistema($objClonarPerfilDTO->getObjPerfilDTO()->getNumIdSistema());
      $objRelPerfilItemMenuDTO->setNumIdPerfil($objClonarPerfilDTO->getObjPerfilDTO()->getNumIdPerfil());
      $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();
      $objClonarPerfilDTO->setArrObjRelPerfilItemMenuDTO($objRelPerfilItemMenuRN->listar($objRelPerfilItemMenuDTO));

      //Coordenadores do perfil
      $objCoordenadorPerfilDTO = new CoordenadorPerfilDTO();
      $objCoordenadorPerfilDTO->retTodos();
      $objCoordenadorPerfilDTO->setNumIdSistema($objClonarPerfilDTO->getObjPerfilDTO()->getNumIdSistema());
      $objCoordenadorPerfilDTO->setNumIdPerfil($objClonarPerfilDTO->getObjPerfilDTO()->getNumIdPerfil());
      $objCoordenadorPerfilRN = new CoordenadorPerfilRN();
      $objClonarPerfilDTO->setArrObjCoordenadorPerfilDTO($objCoordenadorPerfilRN->listar($objCoordenadorPerfilDTO));
      
			//grava dados para Sistema Destino
      $objPerfilDTO = $objClonarPerfilDTO->getObjPerfilDTO();
			$objPerfilDTO->setNumIdSistema($objClonarPerfilDTO->getNumIdSistema());
      $objPerfilDTO->setStrNome($objClonarPerfilDTO->getStrPerfilDestino());
      $objClonarPerfilDTO->setObjPerfilDTO($this->cadastrar($objPerfilDTO));
			
      
      //Clona recursos dos perfis
      $objRelPerfilRecursoRN = new RelPerfilRecursoRN();
      $arrObjRelPerfilRecursoDTO = $objClonarPerfilDTO->getArrObjRelPerfilRecursoDTO();
      if (is_array($arrObjRelPerfilRecursoDTO)){
        foreach($arrObjRelPerfilRecursoDTO as $dto){
          $dto->setNumIdPerfil($objClonarPerfilDTO->getObjPerfilDTO()->getNumIdPerfil());
          $dto->setNumIdSistema($objClonarPerfilDTO->getObjPerfilDTO()->getNumIdSistema());
          $objRelPerfilRecursoRN->cadastrar($dto);
        }
      }

      //Clona itens de menu dos perfis
      $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();
      $arrObjRelPerfilItemMenuDTO = $objClonarPerfilDTO->getArrObjRelPerfilItemMenuDTO();
      if (is_array($arrObjRelPerfilItemMenuDTO)){
        foreach($arrObjRelPerfilItemMenuDTO as $dto){
          $dto->setNumIdPerfil($objClonarPerfilDTO->getObjPerfilDTO()->getNumIdPerfil());
          $dto->setNumIdSistema($objClonarPerfilDTO->getObjPerfilDTO()->getNumIdSistema());
          $objRelPerfilItemMenuRN->cadastrar($dto);
        }
      }
      
      //Clona coordenadores de perfil
      $objCoordenadorPerfilRN = new CoordenadorPerfilRN();
      $arrObjCoordenadorPerfilDTO = $objClonarPerfilDTO->getArrObjCoordenadorPerfilDTO();
      if (is_array($arrObjCoordenadorPerfilDTO)){
        foreach($arrObjCoordenadorPerfilDTO as $dto){
          $dto->setNumIdPerfil($objClonarPerfilDTO->getObjPerfilDTO()->getNumIdPerfil());
          $dto->setNumIdSistema($objClonarPerfilDTO->getObjPerfilDTO()->getNumIdSistema());
          $objCoordenadorPerfilRN->cadastrar($dto);
        }
      }
      
			//Auditoria

      return $objClonarPerfilDTO->getObjPerfilDTO();

    }catch(Exception $e){
      throw new InfraException('Erro clonando Perfil.',$e);
    }
  }

  protected function listarMontarConectado(PerfilDTO $objPerfilDTO){
    try {
  
      $arrObjRecursoDTO = array();

      $objMontarPerfilDTO = new MontarPerfilDTO();
      $objMontarPerfilDTO->retNumIdRecurso();
      $objMontarPerfilDTO->retStrNome();
      $objMontarPerfilDTO->retStrDescricao();
      $objMontarPerfilDTO->retStrSinAtivo();
      
      $objMontarPerfilDTO->setNumIdSistema($objPerfilDTO->getNumIdSistema());
      
      if ($objPerfilDTO->getStrSinVisualizarProprios()=='S'){
        $objMontarPerfilDTO->setNumIdPerfilRelPerfilRecurso($objPerfilDTO->getNumIdPerfil());
      }
      
      
      if ($objPerfilDTO->isSetStrNomeRecurso() && !InfraString::isBolVazia($objPerfilDTO->getStrNomeRecurso())){
        $objMontarPerfilDTO->setStrNome('%'.$objPerfilDTO->getStrNomeRecurso().'%',InfraDTO::$OPER_LIKE);
      }
      
   		$objMontarPerfilDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

   		
      //paginação 
   		$objMontarPerfilDTO->setNumMaxRegistrosRetorno($objPerfilDTO->getNumMaxRegistrosRetorno());
  		$objMontarPerfilDTO->setNumPaginaAtual($objPerfilDTO->getNumPaginaAtual());
   		
      $objRecursoRN = new RecursoRN();
      $arrObjMontarPerfilDTO = $objRecursoRN->listarMontar($objMontarPerfilDTO);

			//paginação
			$objPerfilDTO->setNumTotalRegistros($objMontarPerfilDTO->getNumTotalRegistros());
      $objPerfilDTO->setNumRegistrosPaginaAtual($objMontarPerfilDTO->getNumRegistrosPaginaAtual());
      
      if ($objPerfilDTO->getStrSinVisualizarProprios()=='N'){
        $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
        $objRelPerfilRecursoDTO->retNumIdRecurso();
        $objRelPerfilRecursoDTO->setNumIdSistema($objPerfilDTO->getNumIdSistema());
        $objRelPerfilRecursoDTO->setNumIdPerfil($objPerfilDTO->getNumIdPerfil());
        
        $objRelPerfilRecursoRN = new RelPerfilRecursoRN();
        $arrObjRelPerfilRecursoDTO = $objRelPerfilRecursoRN->listar($objRelPerfilRecursoDTO);
      }else{
        $arrObjRelPerfilRecursoDTO = array();
        foreach($arrObjMontarPerfilDTO as $dto){
          $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
          $objRelPerfilRecursoDTO->setNumIdRecurso($dto->getNumIdRecurso());
          $arrObjRelPerfilRecursoDTO[] = $objRelPerfilRecursoDTO;
        }
      }

      $arrTemp = InfraArray::converterArrInfraDTO($arrObjRelPerfilRecursoDTO,'IdRecurso');
      
      foreach($arrObjMontarPerfilDTO as $dto){
          
    		//Lista recursos do sistema
    	  $objRecursoDTO = new RecursoDTO();
    		$objRecursoDTO->setNumIdRecurso($dto->getNumIdRecurso());
    		$objRecursoDTO->setStrNome($dto->getStrNome());
    		$objRecursoDTO->setStrDescricao($dto->getStrDescricao());
    		$objRecursoDTO->setStrSinAtivo($dto->getStrSinAtivo());
    	
    		if ($objPerfilDTO->getStrSinVisualizarProprios()=='S' || in_array($dto->getNumIdRecurso(),$arrTemp)){
    		  $objRecursoDTO->setStrSinPerfil('S');
    		}else{
     		  $objRecursoDTO->setStrSinPerfil('N');
    		}
    		
    		$arrObjRecursoDTO[] = $objRecursoDTO;
      }
      
      
    		
      //Lista itens de menu do perfil
  		$objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO(true);
      $objRelPerfilItemMenuDTO->retNumIdRecurso();
      $objRelPerfilItemMenuDTO->retNumIdMenu();
      $objRelPerfilItemMenuDTO->retNumIdItemMenu();
      $objRelPerfilItemMenuDTO->setNumIdSistema($objPerfilDTO->getNumIdSistema());
      $objRelPerfilItemMenuDTO->setNumIdPerfil($objPerfilDTO->getNumIdPerfil());				
      $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();
      $arrObjRelPerfilItemMenuDTO = $objRelPerfilItemMenuRN->listar($objRelPerfilItemMenuDTO);

      
  		//Lista hierarquias de menu do sistema, o sistema pode ter mais de um menu
  		$objMenuDTO = new MenuDTO();
  		$objMenuDTO->retNumIdMenu();
  		$objMenuDTO->setNumIdSistema($objPerfilDTO->getNumIdSistema());
  		$objMenuRN = new MenuRN();
  		$arrObjMenuDTO = $objMenuRN->listar($objMenuDTO);
  		$objItemMenuDTO = new ItemMenuDTO();
  		$objItemMenuRN = new ItemMenuRN();
  		
  		$arrRamificacoesItensMenuDTO = array();
  		foreach($arrObjMenuDTO as $objMenuDTO){
  			$objItemMenuDTO->setNumIdMenu($objMenuDTO->getNumIdMenu());
  		  $arrRamificacoesItensMenuDTO[] = $objItemMenuRN->listarHierarquia($objItemMenuDTO);	
  		}

  		$arrTemp = array();
  		foreach($arrRamificacoesItensMenuDTO as $arrRamificacao){
  		  foreach($arrRamificacao as $ramificacao){
    		  $ramificacao->setStrSinPerfil('N');
    		  foreach($arrObjRelPerfilItemMenuDTO as $objRelPerfilItemMenuDTO){
    		    if ($objRelPerfilItemMenuDTO->getNumIdRecurso()==$ramificacao->getNumIdRecurso() && $objRelPerfilItemMenuDTO->getNumIdMenu()==$ramificacao->getNumIdMenu() && $objRelPerfilItemMenuDTO->getNumIdItemMenu()==$ramificacao->getNumIdItemMenu()){
    		      $ramificacao->setStrSinPerfil('S');
    		      break;
    		      //$objRelPerfilItemMenuDTO->setStrRamificacao($ramificacao->getStrRamificacao());
    		    }
    		  }
    		  
    		  if (!isset($arrTemp[$ramificacao->getNumIdRecurso()])){
    		    $arrTemp[$ramificacao->getNumIdRecurso()] = array();
    		  }
    		  
    		  $arrTemp[$ramificacao->getNumIdRecurso()][] = $ramificacao;
  		  }
  		}
  		
  		//atribui itens de menu do recurso
  		foreach($arrObjRecursoDTO as $objRecursoDTO){
  		  if (isset($arrTemp[$objRecursoDTO->getNumIdRecurso()])){
  		    $objRecursoDTO->setArrObjItemMenuDTO($arrTemp[$objRecursoDTO->getNumIdRecurso()]);
  		  }else{
  		    $objRecursoDTO->setArrObjItemMenuDTO(array());
  		  }
  		}
    	
  		return $arrObjRecursoDTO;
    }catch(Exception $e){
      throw new InfraException('Erro listando recursos para montagem de perfil.',$e);
    }
  }  
    
  protected function montarControlado(PerfilDTO $objPerfilDTO){
    try{

      
      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('perfil_montar',__METHOD__,$objPerfilDTO);

      //exclui os recursos que foram exibidos
      $objRelPerfilRecursoRN = new RelPerfilRecursoRN();

      $arrObjRelPerfilRecursoDTO = $objPerfilDTO->getArrObjRelPerfilRecursoDTO();

      //complementa array com o sistema e o perfil (ja tem o recurso)
      foreach($arrObjRelPerfilRecursoDTO as $objRelPerfilRecursoDTO){
        $objRelPerfilRecursoDTO->setNumIdSistema($objPerfilDTO->getNumIdSistema());
        $objRelPerfilRecursoDTO->setNumIdPerfil($objPerfilDTO->getNumIdPerfil());
        
        //exclui se existe no banco
        if ($objRelPerfilRecursoRN->contar($objRelPerfilRecursoDTO)==1){
          $objRelPerfilRecursoRN->excluir(array($objRelPerfilRecursoDTO));
        }
      }
      
      
      //adiciona recursos selecionados
      foreach($arrObjRelPerfilRecursoDTO as $objRelPerfilRecursoDTO){
        if ($objRelPerfilRecursoDTO->getStrSinPerfil()=='S'){
          $objRelPerfilRecursoRN->cadastrar($objRelPerfilRecursoDTO);
        }
      }
      

			if ($objPerfilDTO->isSetArrObjRelPerfilItemMenuDTO()){

			  $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();
			  
			  $arrObjRelPerfilItemMenuDTO = $objPerfilDTO->getArrObjRelPerfilItemMenuDTO();
			  
			  //complementa array com o sistema e o perfil (ja tem o recurso, menu e item de menu)
        foreach($arrObjRelPerfilItemMenuDTO as $objRelPerfilItemMenuDTO){
					$objRelPerfilItemMenuDTO->setNumIdPerfil($objPerfilDTO->getNumIdPerfil());
					$objRelPerfilItemMenuDTO->setNumIdSistema($objPerfilDTO->getNumIdSistema());
					
					//apaga só se existe no banco
					if ($objRelPerfilItemMenuRN->contar($objRelPerfilItemMenuDTO)==1){
			      $objRelPerfilItemMenuRN->excluir(array($objRelPerfilItemMenuDTO));		  
					}
        }

				//adiciona itens de menu selecionados
        foreach($arrObjRelPerfilItemMenuDTO as $objRelPerfilItemMenuDTO){
          if ($objRelPerfilItemMenuDTO->getStrSinPerfil()=='S'){
						$objRelPerfilItemMenuRN->cadastrar($objRelPerfilItemMenuDTO);
          }
        }
			}      
      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro montando perfil.',$e);
    }
  }
  
  protected function cadastrarControlado(PerfilDTO $objPerfilDTO) {
    try{

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('perfil_cadastrar',__METHOD__,$objPerfilDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarNumIdSistema($objPerfilDTO,$objInfraException);
      $this->validarStrNome($objPerfilDTO,$objInfraException);
      $this->validarStrDescricao($objPerfilDTO,$objInfraException);
      $this->validarStrSinAtivo($objPerfilDTO,$objInfraException);

      $objInfraException->lancarValidacoes();

      $objPerfilBD = new PerfilBD($this->getObjInfraIBanco());
      $ret = $objPerfilBD->cadastrar($objPerfilDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando Perfil.',$e);
    }
  }

  protected function alterarControlado(PerfilDTO $objPerfilDTO){
    try {

      //Valida Permissao
  	   SessaoSip::getInstance()->validarAuditarPermissao('perfil_alterar',__METHOD__,$objPerfilDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarNumIdSistema($objPerfilDTO,$objInfraException);
      $this->validarStrNome($objPerfilDTO,$objInfraException);
      $this->validarStrDescricao($objPerfilDTO,$objInfraException);
      $this->validarStrSinAtivo($objPerfilDTO,$objInfraException);

      $objInfraException->lancarValidacoes();

      $objPerfilBD = new PerfilBD($this->getObjInfraIBanco());
      $objPerfilBD->alterar($objPerfilDTO);

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando Perfil.',$e);
    }
  }

  protected function excluirControlado($arrObjPerfilDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('perfil_excluir',__METHOD__,$arrObjPerfilDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $objInfraParametro = new InfraParametro(BancoSip::getInstance());
      $arrReservados = $objInfraParametro->listarValores(array('ID_PERFIL_SIP_ADMINISTRADOR_SISTEMA',
                                                               'ID_PERFIL_SIP_ADMINISTRADOR_SIP',
                                                               'ID_PERFIL_SIP_COORDENADOR_PERFIL',
                                                               'ID_PERFIL_SIP_COORDENADOR_UNIDADE'));

      foreach($arrObjPerfilDTO as $objPerfilDTO){
        if (in_array($objPerfilDTO->getNumIdPerfil(),$arrReservados)){
          
          $objPerfilDTOBanco = new PerfilDTO();
          $objPerfilDTOBanco->retStrSiglaSistema();
          $objPerfilDTOBanco->retStrNome();
          $objPerfilDTOBanco->setBolExclusaoLogica(false);
          $objPerfilDTOBanco->setNumIdPerfil($objPerfilDTO->getNumIdPerfil());
          $objPerfilDTOBanco = $this->consultar($objPerfilDTOBanco);
          
          $objInfraException->lancarValidacao('Não é possível excluir o perfil reservado "'.$objPerfilDTOBanco->getStrNome().'" do sistema '.$objPerfilDTOBanco->getStrSiglaSistema().'.');
        }
      }
      
      $objInfraException->lancarValidacoes();
      
      $objPerfilBD = new PerfilBD($this->getObjInfraIBanco());
      
      foreach($arrObjPerfilDTO as $objPerfilDTO){
        
				//Exclui coordenadores de perfil associados
				$objCoordenadorPerfilDTO = new CoordenadorPerfilDTO();
				$objCoordenadorPerfilDTO->retTodos();
				$objCoordenadorPerfilDTO->setNumIdPerfil($objPerfilDTO->getNumIdPerfil());
				$objCoordenadorPerfilRN = new CoordenadorPerfilRN();
				$objCoordenadorPerfilRN->excluir($objCoordenadorPerfilRN->listar($objCoordenadorPerfilDTO));
				
				//Exclui permissoes associadas
				$objPermissaoDTO = new PermissaoDTO();
				$objPermissaoDTO->retTodos();
				$objPermissaoDTO->setNumIdPerfil($objPerfilDTO->getNumIdPerfil());
				$objPermissaoRN = new PermissaoRN();
				$objPermissaoRN->excluir($objPermissaoRN->listar($objPermissaoDTO));
				
				//Exclui ligacao com recursos
				$objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
				$objRelPerfilRecursoDTO->retTodos();
				$objRelPerfilRecursoDTO->setNumIdPerfil($objPerfilDTO->getNumIdPerfil());
				$objRelPerfilRecursoRN = new RelPerfilRecursoRN();
				$objRelPerfilRecursoRN->excluir($objRelPerfilRecursoRN->listar($objRelPerfilRecursoDTO));
				
				//Exclui ligacao com itens de menu
				$objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
				$objRelPerfilItemMenuDTO->retTodos();
				$objRelPerfilItemMenuDTO->setNumIdPerfil($objPerfilDTO->getNumIdPerfil());
				$objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();
				$objRelPerfilItemMenuRN->excluir($objRelPerfilItemMenuRN->listar($objRelPerfilItemMenuDTO));
				
        $objPerfilBD->excluir($objPerfilDTO);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo Perfil.',$e);
    }
  }

  protected function desativarControlado($arrObjPerfilDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('perfil_desativar',__METHOD__,$arrObjPerfilDTO);

      //Regras de Negocio
      //Regras de Negocio
      $objInfraException = new InfraException();
      
      $objInfraParametro = new InfraParametro(BancoSip::getInstance());
      $arrReservados = $objInfraParametro->listarValores(array('ID_PERFIL_SIP_ADMINISTRADOR_SISTEMA',
                                                               'ID_PERFIL_SIP_ADMINISTRADOR_SIP',
                                                               'ID_PERFIL_SIP_COORDENADOR_PERFIL',
                                                               'ID_PERFIL_SIP_COORDENADOR_UNIDADE'));
      
      foreach($arrObjPerfilDTO as $objPerfilDTO){
        if (in_array($objPerfilDTO->getNumIdPerfil(),$arrReservados)){
          
          $objPerfilDTOBanco = new PerfilDTO();
          $objPerfilDTOBanco->retStrSiglaSistema();
          $objPerfilDTOBanco->retStrNome();
          $objPerfilDTOBanco->setBolExclusaoLogica(false);
          $objPerfilDTOBanco->setNumIdPerfil($objPerfilDTO->getNumIdPerfil());
          $objPerfilDTOBanco = $this->consultar($objPerfilDTOBanco);
          
          $objInfraException->lancarValidacao('Não é possível desativar o perfil reservado "'.$objPerfilDTOBanco->getStrNome().'" do sistema '.$objPerfilDTOBanco->getStrSiglaSistema().'.');
        }
      }
      
      $objInfraException->lancarValidacoes();
            
      $objPerfilBD = new PerfilBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjPerfilDTO);$i++){
        $objPerfilBD->desativar($arrObjPerfilDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando Perfil.',$e);
    }
  }

  protected function reativarControlado($arrObjPerfilDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('perfil_reativar',__METHOD__,$arrObjPerfilDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objPerfilBD = new PerfilBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjPerfilDTO);$i++){
        $objPerfilBD->reativar($arrObjPerfilDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando Perfil.',$e);
    }
  }
  
  protected function consultarConectado(PerfilDTO $objPerfilDTO){
    try {

      //Valida Permissao
			/////////////////////////////////////////////////////////////////
      //SessaoSip::getInstance()->validarAuditarPermissao('perfil_consultar',__METHOD__,$objPerfilDTO);
			/////////////////////////////////////////////////////////////////

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objPerfilBD = new PerfilBD($this->getObjInfraIBanco());
      $ret = $objPerfilBD->consultar($objPerfilDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando Perfil.',$e);
    }
  }

  protected function listarConectado(PerfilDTO $objPerfilDTO) {
    try {

      //Valida Permissao
			/////////////////////////////////////////////////////////////////
      //SessaoSip::getInstance()->validarAuditarPermissao('perfil_listar',__METHOD__,$objPerfilDTO);
			/////////////////////////////////////////////////////////////////

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objPerfilBD = new PerfilBD($this->getObjInfraIBanco());
      $ret = $objPerfilBD->listar($objPerfilDTO);
			
      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Perfis.',$e);
    }
  }

  protected function contarConectado(PerfilDTO $objPerfilDTO) {
    try {

      //Valida Permissao
			/////////////////////////////////////////////////////////////////
      //SessaoSip::getInstance()->validarAuditarPermissao('perfil_contar',__METHOD__,$objPerfilDTO);
			/////////////////////////////////////////////////////////////////

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();
		
			
      $objPerfilBD = new PerfilBD($this->getObjInfraIBanco());
      $ret = $objPerfilBD->contar($objPerfilDTO);
			
      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Perfis.',$e);
    }
  }
  	
  protected function listarAdministradosConectado(PerfilDTO $objPerfilDTO) {
    try {

      //Valida Permissao
			/////////////////////////////////////////////////////////////////
      //SessaoSip::getInstance()->validarAuditarPermissao('perfil_listar',__METHOD__,$objPerfilDTO);
			/////////////////////////////////////////////////////////////////

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

			//Retorna o ID para fechar com os sistemas Administrados
			$objPerfilDTO->retNumIdSistema();
			
      $arrObjPerfilDTO = $this->listar($objPerfilDTO);

			//Obtem sistemas onde o usuario é administrador
			$objAcessoDTO = new AcessoDTO();
			$objAcessoDTO->setNumTipo(AcessoDTO::$ADMINISTRADOR);
			$objAcessoRN = new AcessoRN();
			$arrObjAcessoDTO = $objAcessoRN->obterAcessos($objAcessoDTO);
			
      //Filtra perfis onde 
			$ret = InfraArray::joinArrInfraDTO($arrObjPerfilDTO,'IdSistema',$arrObjAcessoDTO,'IdSistema');
			
      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Perfis administrados.',$e);
    }
  }

  protected function listarCoordenadosConectado(PerfilDTO $parObjPerfilDTO) {
    try {
  
      //Valida Permissao
      /////////////////////////////////////////////////////////////////
      //SessaoSip::getInstance()->validarAuditarPermissao('perfil_listar_coordenados',__METHOD__,$objPerfilDTO);
      /////////////////////////////////////////////////////////////////
  
      //Regras de Negocio
      //$objInfraException = new InfraException();
  
      //$objInfraException->lancarValidacoes();
  
      $ret = array();
      
      //Obtem sistemas onde o usuario é coordenador
      $objSistemaDTO = new SistemaDTO();
      $objSistemaDTO->retNumIdSistema();
      
      $objSistemaRN = new SistemaRN();
      $arrIdSistema = InfraArray::converterArrInfraDTO($objSistemaRN->listarCoordenados($objSistemaDTO),'IdSistema');
      
      if (in_array($parObjPerfilDTO->getNumIdSistema(), $arrIdSistema)){

        $arrObjPerfilDTO = $this->listar($parObjPerfilDTO);
        
        //Obtem perfis onde o usuario é coordenador
        $objCoordenadorPerfilDTO = new CoordenadorPerfilDTO();
        $objCoordenadorPerfilDTO->retNumIdPerfil();
        $objCoordenadorPerfilDTO->setNumIdUsuario(SessaoSip::getInstance()->getNumIdUsuario());
        $objCoordenadorPerfilDTO->setNumIdSistema($parObjPerfilDTO->getNumIdSistema());
        
        $objCoordenadorPerfilRN = new CoordenadorPerfilRN();
        $arrObjCoordenadorPerfilDTO = $objCoordenadorPerfilRN->listar($objCoordenadorPerfilDTO);
  
          
        foreach($arrObjPerfilDTO as $objPerfilDTO){
          $objPerfilDTO->setStrSinCoordenadoPeloUsuario('N');
          foreach($arrObjCoordenadorPerfilDTO as $objCoordenadorPerfilDTO){
            if ($objCoordenadorPerfilDTO->getNumIdPerfil()==$objPerfilDTO->getNumIdPerfil()){
              $objPerfilDTO->setStrSinCoordenadoPeloUsuario('S');
              break;
            }
          }
        }
        
        if ($parObjPerfilDTO->getStrSinCoordenadoPeloUsuario()=='S'){
          foreach($arrObjPerfilDTO as $objPerfilDTO){
            if ($objPerfilDTO->getStrSinCoordenadoPeloUsuario()=='S'){
              $ret[] = $objPerfilDTO;
            }
          }
        }else{
          $ret = $arrObjPerfilDTO;
        }

        //Obtem perfis onde o usuario é coordenador
        $objCoordenadorPerfilDTO = new CoordenadorPerfilDTO();
        $objCoordenadorPerfilDTO->setDistinct(true);
        $objCoordenadorPerfilDTO->retNumIdPerfil();
        $objCoordenadorPerfilDTO->setNumIdSistema($parObjPerfilDTO->getNumIdSistema());
        
        $arrObjCoordenadorPerfilDTO = $objCoordenadorPerfilRN->listar($objCoordenadorPerfilDTO);
        
        foreach($arrObjPerfilDTO as $objPerfilDTO){
          $objPerfilDTO->setStrSinCoordenadoPorAlgumUsuario('N');
          foreach($arrObjCoordenadorPerfilDTO as $objCoordenadorPerfilDTO){
            if ($objCoordenadorPerfilDTO->getNumIdPerfil()==$objPerfilDTO->getNumIdPerfil()){
              $objPerfilDTO->setStrSinCoordenadoPorAlgumUsuario('S');
              break;
            }
          }
        }
      }
      
      //Auditoria
  
      return $ret;
  
    }catch(Exception $e){
      throw new InfraException('Erro listando Perfis coordenados.',$e);
    }
  }
  
	/**
	Recupera os perfis acessados pelo usuario/sistema informado onde carregará:
	(1) todos os perfis do sistema se usuario administrador do sistema
	(2) todos os perfis coordenados pelo usuario no sistema
	(3) todos os perfis diponiveis as coordenadores de unidade se o usuario for coordenador de pelo menos uma unidade do sistema
	*/
	
  protected function obterAutorizadosConectado(SistemaDTO $objSistemaDTO) {
    try {

      //InfraDebug::getInstance()->setBolLigado(false);
      //InfraDebug::getInstance()->setBolDebugInfra(true);
      //InfraDebug::getInstance()->limpar();
      
      $objInfraException = new InfraException();
      
      $ret = array();
      
			if (!InfraString::isBolVazia($objSistemaDTO->getNumIdSistema())){
			
				//Busca todos os perfis do sistema
				$objPerfilDTO = new PerfilDTO();
				$objPerfilDTO->retNumIdPerfil();
				$objPerfilDTO->retStrNome();
				$objPerfilDTO->retStrSinCoordenado();
				$objPerfilDTO->setNumIdSistema($objSistemaDTO->getNumIdSistema());

        $objInfraParametro = new InfraParametro(BancoSip::getInstance());
        if ($objSistemaDTO->getNumIdSistema()==$objInfraParametro->getValor('ID_SISTEMA_SIP')) {
          $arrReservados = $objInfraParametro->listarValores(array('ID_PERFIL_SIP_ADMINISTRADOR_SISTEMA',
              'ID_PERFIL_SIP_ADMINISTRADOR_SIP',
              'ID_PERFIL_SIP_COORDENADOR_PERFIL',
              'ID_PERFIL_SIP_COORDENADOR_UNIDADE'));
          $objPerfilDTO->setNumIdPerfil($arrReservados, InfraDTO::$OPER_NOT_IN);
        }
				
				$objPerfilDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);
				
				$arrObjPerfilDTO = $this->listar($objPerfilDTO);
				
				//Obtem sistemas autorizados (todos os sistemas exceto os acessados via permissoes pessoais) pelo usuario
				$objAcessoDTO = new AcessoDTO();
				$objAcessoDTO->setNumTipo(AcessoDTO::$ADMINISTRADOR | AcessoDTO::$COORDENADOR_PERFIL | AcessoDTO::$COORDENADOR_UNIDADE);
				$objAcessoRN = new AcessoRN();
				$arrObjAcessoDTO = $objAcessoRN->obterAcessos($objAcessoDTO);
	
				/*
				$strSistemas = '';
				foreach($arrObjAcessoDTO as $objAcessoDTO){
					$strSistemas .= $objAcessoDTO->__toString()."\n";
				}
				$objInfraException->lancarValidacao($strSistemas);
				*/
				
				//verifica se o usuario é administrador
				//se afirmativo retorna todos os perfis do sistema
				foreach($arrObjAcessoDTO as $acesso){
					if ($acesso->getNumIdSistema()==$objSistemaDTO->getNumIdSistema() && 
					    $acesso->getNumTipo()==AcessoDTO::$ADMINISTRADOR){
						  return $arrObjPerfilDTO;
					 }
				}

        $numIdUnidade = null;
        $numIdOrgaoUnidade = null;
        if (!InfraString::isBolVazia($objSistemaDTO->getNumIdUnidade())){

          $objUnidadeDTO = new UnidadeDTO();
          $objUnidadeDTO->retNumIdUnidade();
          $objUnidadeDTO->retNumIdOrgao();
          $objUnidadeDTO->setNumIdUnidade($objSistemaDTO->getNumIdUnidade());

          $objUnidadeRN = new UnidadeRN();
          $objUnidadeDTO = $objUnidadeRN->consultar($objUnidadeDTO);

          if ($objUnidadeDTO!=null) {
            $numIdUnidade = $objUnidadeDTO->getNumIdUnidade();
            $numIdOrgaoUnidade = $objUnidadeDTO->getNumIdOrgao();
          }
        }


				foreach($arrObjPerfilDTO as $objPerfilDTO){
  				foreach($arrObjAcessoDTO as $acesso){
  				  if ($acesso->getNumIdSistema()==$objSistemaDTO->getNumIdSistema() && $acesso->getNumIdPerfil()==$objPerfilDTO->getNumIdPerfil()){
  				    if ($acesso->getNumTipo()==AcessoDTO::$COORDENADOR_PERFIL ||
                  ($acesso->getNumTipo()==AcessoDTO::$COORDENADOR_UNIDADE &&
                      (
                          ($acesso->getStrSinGlobalUnidade()=='S' && $acesso->getNumIdOrgaoUnidade()==$numIdOrgaoUnidade) ||
                          ($acesso->getStrSinGlobalUnidade()=='N' && $acesso->getNumIdUnidade()==$numIdUnidade)
                      ))){
   					    $ret[] = $objPerfilDTO;
    					}
    				}
  				}
				} 
			}			
			
      //Auditoria
      return $ret;
		
    }catch(Exception $e){
      throw new InfraException('Erro listando Perfis autorizados.',$e);
    }
  }
	
  private function validarNumIdSistema(PerfilDTO $objPerfilDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objPerfilDTO->getNumIdSistema())){
      $objInfraException->adicionarValidacao('Sistema não informado.');
    }
  }

  private function validarStrNome(PerfilDTO $objPerfilDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objPerfilDTO->getStrNome())){
      $objInfraException->adicionarValidacao('Nome não informado.');
    }

    $objPerfilDTO->setStrNome(trim($objPerfilDTO->getStrNome()));

    if (strlen($objPerfilDTO->getStrNome())>100){
      $objInfraException->adicionarValidacao('Nome possui tamanho superior a 100 caracteres.');
    }

    $dto = new PerfilDTO();
    $dto->setBolExclusaoLogica(false);
    $dto->retStrSinAtivo();
    $dto->setNumIdSistema($objPerfilDTO->getNumIdSistema());
    if ($objPerfilDTO->isSetNumIdPerfil() && $objPerfilDTO->getNumIdPerfil()!=null){
      $dto->setNumIdPerfil($objPerfilDTO->getNumIdPerfil(),InfraDTO::$OPER_DIFERENTE);
    }
    $dto->setStrNome($objPerfilDTO->getStrNome());
    $dto = $this->consultar($dto);

    if ($dto!=null){
      if ($dto->getStrSinAtivo()=='S'){
        $objInfraException->adicionarValidacao('Já existe um perfil com este nome.');
      }else{
        $objInfraException->adicionarValidacao('Existe um perfil inativo com este nome.');
      }
    }
  }
  
  private function validarStrDescricao(PerfilDTO $objPerfilDTO, InfraException $objInfraException){
  }
	
  private function validarStrSinAtivo(PerfilDTO $objPerfilDTO, InfraException $objInfraException){
    if ($objPerfilDTO->getStrSinAtivo()===null || ($objPerfilDTO->getStrSinAtivo()!=='S' && $objPerfilDTO->getStrSinAtivo()!=='N')){
      $objInfraException->adicionarValidacao('Sinalizador de Exclusão Lógica inválido.');
    }
  }

	protected function carregarPerfisConectado(PermissaoDTO $parObjPermissaoDTO){
    try{    
  		$objInfraException = new InfraException();
  		
  		if (InfraString::isBolVazia($parObjPermissaoDTO->getNumIdSistema())){
  			$objInfraException->adicionarValidacao('Sistema não informado.');
  		}
  		
  		$objInfraException->lancarValidacoes();

  		$objPerfilDTO = new PerfilDTO();
  		$objPerfilDTO->setBolExclusaoLogica(false);
  		$objPerfilDTO->retNumIdPerfil();
  		$objPerfilDTO->retStrNome();
  		$objPerfilDTO->retStrDescricao();
  		$objPerfilDTO->retStrSinAtivo();
  		
  		if (InfraString::isBolVazia($parObjPermissaoDTO->getNumIdUsuario()) && InfraString::isBolVazia($parObjPermissaoDTO->getNumIdUnidade())){
  		  
  		  $objPerfilDTO->setNumIdSistema($parObjPermissaoDTO->getNumIdSistema());
  		  
  		}else{
  		  
    		$objPermissaoDTO = new PermissaoDTO();
    		$objPermissaoDTO->setDistinct(true);
    		$objPermissaoDTO->retNumIdPerfil();
    		$objPermissaoDTO->setNumIdSistema($parObjPermissaoDTO->getNumIdSistema());
    		
    		if (!InfraString::isBolVazia($parObjPermissaoDTO->getNumIdUnidade())){
    		  $objPermissaoDTO->setNumIdUnidade($parObjPermissaoDTO->getNumIdUnidade());
    		}
    		
    		if (!InfraString::isBolVazia($parObjPermissaoDTO->getNumIdUsuario())){
    		  $objPermissaoDTO->setNumIdUsuario($parObjPermissaoDTO->getNumIdUsuario());
    		}
    		
    		$objPermissaoDTO->setDtaDataInicio(InfraData::getStrDataAtual(),InfraDTO::$OPER_MENOR_IGUAL);
    		$objPermissaoDTO->adicionarCriterio(array('DataFim','DataFim'),
    		                                    array(InfraDTO::$OPER_MAIOR_IGUAL,InfraDTO::$OPER_IGUAL),
    		                                    array(InfraData::getStrDataAtual(),null),
    		                                    InfraDTO::$OPER_LOGICO_OR);
    		
  
        $objPermissaoRN = new PermissaoRN();
        $arrObjPermissaoDTO = $objPermissaoRN->listar($objPermissaoDTO);

        if (count($arrObjPermissaoDTO)){
          $objPerfilDTO->setNumIdPerfil(InfraArray::converterArrInfraDTO($arrObjPermissaoDTO,'IdPerfil'), InfraDTO::$OPER_IN);
        }else{
          $objPerfilDTO->setNumIdPerfil(null);
        }
  		  
  		}
  		
  		$arrObjPerfilDTO = $this->listar($objPerfilDTO); 
  		

  		$ret = array();
  		foreach($arrObjPerfilDTO as $objPerfilDTO){

  			$ret[] = array(InfraSip::$WS_PERFIL_ID => $objPerfilDTO->getNumIdPerfil(),
										   InfraSip::$WS_PERFIL_NOME => $objPerfilDTO->getStrNome(),
										   InfraSip::$WS_PERFIL_DESCRICAO => $objPerfilDTO->getStrDescricao(),
										   InfraSip::$WS_PERFIL_SIN_ATIVO => $objPerfilDTO->getStrSinAtivo());
       }
  		
       return $ret;
		
    }catch(Exception $e){
      throw new InfraException('Erro carregando perfis.',$e);
    }
	}
}
?>