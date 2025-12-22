<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 27/11/2006 - criado por mga
*
*
*/

require_once dirname(__FILE__).'/../Sip.php';

class UsuarioRN extends InfraRN {

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSip::getInstance();
  }

  protected function replicarControlado(ReplicarUsuarioRhDTO $objReplicarUsuarioRhDTO){
    try {
    	
      //formata sigla
      $objReplicarUsuarioRhDTO->setStrSigla(InfraString::transformarCaixaBaixa($objReplicarUsuarioRhDTO->getStrSigla()));        
      $objReplicarUsuarioRhDTO->setStrNome(InfraString::formatarNome($objReplicarUsuarioRhDTO->getStrNome()));

      //busca usuário no SIP
      $dto = new UsuarioDTO();
      $dto->setBolExclusaoLogica(false);
      $dto->retStrIdOrigem();
      $dto->retNumIdOrgao();
      $dto->retNumIdUsuario();
      $dto->retStrSigla();
      $dto->retStrNome();
      $dto->retStrSinAtivo();
      
      if ($objReplicarUsuarioRhDTO->getStrIdOrigem()!=null){
        $dto->setStrIdOrigem($objReplicarUsuarioRhDTO->getStrIdOrigem());
      }else{
        $dto->setStrSigla($objReplicarUsuarioRhDTO->getStrSigla());
      }

      $dto->setNumIdOrgao($objReplicarUsuarioRhDTO->getNumIdOrgao());
      
      $arrObjUsuarioDTO = $this->listar($dto);
      
      if (count($arrObjUsuarioDTO)>1){
        throw new InfraException($objReplicarUsuarioRhDTO->getStrIdOrigem().'/'.$objReplicarUsuarioRhDTO->getStrSigla().'/'.$objReplicarUsuarioRhDTO->getNumIdOrgao().': Mais de uma pessoa encontrada no SIP.');
      }else if (count($arrObjUsuarioDTO)==0){
        $objUsuarioDTO = null;
      }else{
        $objUsuarioDTO = $arrObjUsuarioDTO[0];
      }
      
      if ($objUsuarioDTO!=null && $objUsuarioDTO->getStrSinAtivo()=='N' && $objReplicarUsuarioRhDTO->getStrStaOperacao()=='C'){
        $this->reativar(array($objUsuarioDTO));
      }
      
      //cadastrando ou alterando e não existe no SIP
      if ($objUsuarioDTO==null){
      
        $dto = new UsuarioDTO();
        $dto->setNumIdUsuario(null);
        $dto->setNumIdOrgao($objReplicarUsuarioRhDTO->getNumIdOrgao());
        $dto->setStrSigla($objReplicarUsuarioRhDTO->getStrSigla());
        $dto->setStrNome($objReplicarUsuarioRhDTO->getStrNome());
        $dto->setStrIdOrigem($objReplicarUsuarioRhDTO->getStrIdOrigem());
        $dto->setStrSinAtivo('S');
        $objUsuarioDTO = $this->cadastrar($dto);
         
      }else {
      
        //se alterou algum campo
        if ($objUsuarioDTO->getNumIdOrgao()!=$objReplicarUsuarioRhDTO->getNumIdOrgao() ||
            $objUsuarioDTO->getStrSigla()!=$objReplicarUsuarioRhDTO->getStrSigla() || 
            $objUsuarioDTO->getStrNome()!=$objReplicarUsuarioRhDTO->getStrNome()){
      
          $dto = new UsuarioDTO();
          $dto->setNumIdOrgao($objReplicarUsuarioRhDTO->getNumIdOrgao());
          $dto->setStrSigla($objReplicarUsuarioRhDTO->getStrSigla());
          $dto->setStrNome($objReplicarUsuarioRhDTO->getStrNome());
          $dto->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
          $this->alterar($dto);
          
        }
      }      
       
    }catch(Exception $e){
      throw new InfraException('Erro replicando Usuário.',$e);
    }
  }

  protected function pesquisarConectado(UsuarioDTO $objUsuarioDTO) {
    try {

      //Valida Permissao
			/////////////////////////////////////////////////////////////////
      SessaoSip::getInstance()->validarAuditarPermissao('usuario_listar',__METHOD__,$objUsuarioDTO);
			/////////////////////////////////////////////////////////////////

			if ($objUsuarioDTO->isSetStrSigla()){
			  $objUsuarioDTO->setStrSigla('%'.$objUsuarioDTO->getStrSigla().'%',InfraDTO::$OPER_LIKE);
			}
			
  		if ($objUsuarioDTO->isSetStrNome()){
  		  if (trim($objUsuarioDTO->getStrNome())!=''){
    			$strPalavrasPesquisa = InfraString::transformarCaixaAlta($objUsuarioDTO->getStrNome());
    			$arrPalavrasPesquisa = explode(' ',$strPalavrasPesquisa);
    
     			for($i=0;$i<count($arrPalavrasPesquisa);$i++){
     			  $arrPalavrasPesquisa[$i] = '%'.$arrPalavrasPesquisa[$i].'%';
     			}
     			
    			if (count($arrPalavrasPesquisa)==1){
    				$objUsuarioDTO->setStrNome($arrPalavrasPesquisa[0],InfraDTO::$OPER_LIKE);
    			}else{
    			  $objUsuarioDTO->unSetStrNome();
    				$a = array_fill(0,count($arrPalavrasPesquisa),'Nome');
    				$b = array_fill(0,count($arrPalavrasPesquisa),InfraDTO::$OPER_LIKE);
    				$d = array_fill(0,count($arrPalavrasPesquisa)-1,InfraDTO::$OPER_LOGICO_AND);
    				$objUsuarioDTO->adicionarCriterio($a,$b,$arrPalavrasPesquisa,$d);
    			}
    			
  		  }			
  		}

      if ($objUsuarioDTO->isSetStrIdOrigem()){
        $objUsuarioDTO->setStrIdOrigem($objUsuarioDTO->getStrIdOrigem());
      }

  		return $this->listar($objUsuarioDTO);

    }catch(Exception $e){
      throw new InfraException('Erro pesquisando Usuários.',$e);
    }
  }
    
  protected function cadastrarControlado(UsuarioDTO $objUsuarioDTO) {
    try{

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('usuario_cadastrar',__METHOD__,$objUsuarioDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarNumIdOrgao($objUsuarioDTO,$objInfraException);
      $this->validarStrIdOrigem($objUsuarioDTO,$objInfraException);
      $this->validarStrSigla($objUsuarioDTO,$objInfraException);
      $this->validarStrNome($objUsuarioDTO,$objInfraException);
			$this->validarStrSinAtivo($objUsuarioDTO,$objInfraException);

      $objInfraException->lancarValidacoes();

      $objUsuarioBD = new UsuarioBD($this->getObjInfraIBanco());
      $ret = $objUsuarioBD->cadastrar($objUsuarioDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando Usuário.',$e);
    }
  }

  protected function alterarControlado(UsuarioDTO $objUsuarioDTO){
    try {

      //Valida Permissao
  	   SessaoSip::getInstance()->validarAuditarPermissao('usuario_alterar',__METHOD__,$objUsuarioDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $objUsuarioDTOBanco = new UsuarioDTO();
      $objUsuarioDTOBanco->setBolExclusaoLogica(false);
      $objUsuarioDTOBanco->retNumIdOrgao();
      $objUsuarioDTOBanco->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
      $objUsuarioDTOBanco = $this->consultar($objUsuarioDTOBanco);

      if ($objUsuarioDTO->isSetNumIdOrgao()){
        $this->validarNumIdOrgao($objUsuarioDTO,$objInfraException);
      }else{
        $objUsuarioDTO->setNumIdOrgao($objUsuarioDTOBanco->getNumIdOrgao());
      }

      if ($objUsuarioDTO->isSetStrIdOrigem()){
        $this->validarStrIdOrigem($objUsuarioDTO,$objInfraException);
      }
      
      if ($objUsuarioDTO->isSetStrSigla()){
        $this->validarStrSigla($objUsuarioDTO,$objInfraException);
      }
      
      if ($objUsuarioDTO->isSetStrNome()){
        $this->validarStrNome($objUsuarioDTO,$objInfraException);
      }
      
      if ($objUsuarioDTO->isSetStrSinAtivo()){
			  $this->validarStrSinAtivo($objUsuarioDTO,$objInfraException);
      }

      $objInfraException->lancarValidacoes();

      $objUsuarioBD = new UsuarioBD($this->getObjInfraIBanco());
      $objUsuarioBD->alterar($objUsuarioDTO);
      
      //Atualiza usuario nos sistemas
      $objPermissaoDTO = new PermissaoDTO();
      $objPermissaoDTO->setDistinct(true);
      $objPermissaoDTO->retNumIdSistema();
      $objPermissaoDTO->retNumIdUsuario();
      $objPermissaoDTO->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
      
      $objPermissaoRN = new PermissaoRN();
      $arrObjPermissaoDTO = $objPermissaoRN->listar($objPermissaoDTO);
      
      $objSistemaRN = new SistemaRN();
      
      foreach($arrObjPermissaoDTO as $objPermissaoDTO){
        $objReplicacaoUsuarioDTO = new ReplicacaoUsuarioDTO();
        $objReplicacaoUsuarioDTO->setStrStaOperacao('A');
        $objReplicacaoUsuarioDTO->setNumIdSistema($objPermissaoDTO->getNumIdSistema());
        $objReplicacaoUsuarioDTO->setNumIdUsuario($objPermissaoDTO->getNumIdUsuario());
        $objSistemaRN->replicarUsuario($objReplicacaoUsuarioDTO);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando Usuário.',$e);
    }
  }

  protected function excluirControlado($arrObjUsuarioDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('usuario_excluir',__METHOD__,$arrObjUsuarioDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();
      
      for($i=0;$i<count($arrObjUsuarioDTO);$i++){
        $objPermissaoDTO = new PermissaoDTO();
        $objPermissaoDTO->setDistinct(true);
        $objPermissaoDTO->retStrSiglaUsuario();
        $objPermissaoDTO->retStrSiglaOrgaoUsuario();
  			$objPermissaoDTO->retStrSiglaSistema();
  			$objPermissaoDTO->setNumIdUsuario($arrObjUsuarioDTO[$i]->getNumIdUsuario());
  			
  			$objPermissaoRN = new PermissaoRN();
			
    		$arrObjPermissaoDTO = $objPermissaoRN->listar($objPermissaoDTO);
    		
    		if (count($arrObjPermissaoDTO)>0){
    		  $objInfraException->adicionarValidacao('Usuário '.$arrObjPermissaoDTO[0]->getStrSiglaUsuario().'/'.$arrObjPermissaoDTO[0]->getStrSiglaOrgaoUsuario().' possui permissões no(s) sistema(s): '.implode(', ',InfraArray::converterArrInfraDTO($arrObjPermissaoDTO,'SiglaSistema')));
    		}
      }

      $objInfraException->lancarValidacoes();
			
			
      $objUsuarioBD = new UsuarioBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjUsuarioDTO);$i++){
        
				//Exclui usuario administrador do sistema
        $objAdministradorSistemaDTO = new AdministradorSistemaDTO();
				$objAdministradorSistemaDTO->retTodos();
				$objAdministradorSistemaRN = new AdministradorSistemaRN();
				$objAdministradorSistemaDTO->setNumIdUsuario($arrObjUsuarioDTO[$i]->getNumIdUsuario());
				$objAdministradorSistemaRN->excluir($objAdministradorSistemaRN->listar($objAdministradorSistemaDTO));

				//Exclui usuario coordenador de perfil
        $objCoordenadorPerfilDTO = new CoordenadorPerfilDTO();
				$objCoordenadorPerfilDTO->retTodos();
				$objCoordenadorPerfilRN = new CoordenadorPerfilRN();
				$objCoordenadorPerfilDTO->setNumIdUsuario($arrObjUsuarioDTO[$i]->getNumIdUsuario());
				$objCoordenadorPerfilRN->excluir($objCoordenadorPerfilRN->listar($objCoordenadorPerfilDTO));

				//Exclui usuario coordenador de unidade
        $objCoordenadorUnidadeDTO = new CoordenadorUnidadeDTO();
				$objCoordenadorUnidadeDTO->retTodos();
				$objCoordenadorUnidadeRN = new CoordenadorUnidadeRN();
				$objCoordenadorUnidadeDTO->setNumIdUsuario($arrObjUsuarioDTO[$i]->getNumIdUsuario());
				$objCoordenadorUnidadeRN->excluir($objCoordenadorUnidadeRN->listar($objCoordenadorUnidadeDTO));

	  		$objLoginDTO = new LoginDTO();
	  		$objLoginDTO->retStrIdLogin();
	  		$objLoginDTO->retNumIdUsuario();
	  		$objLoginDTO->retNumIdContexto();
	  		$objLoginDTO->retNumIdSistema();
	  		$objLoginDTO->adicionarCriterio(array('IdUsuario', 'IdUsuarioEmulador'),
	  		                                array(InfraDTO::$OPER_IGUAL,InfraDTO::$OPER_IGUAL),
	  		                                array($arrObjUsuarioDTO[$i]->getNumIdUsuario(), $arrObjUsuarioDTO[$i]->getNumIdUsuario()),
                                        InfraDTO::$OPER_LOGICO_OR);
	  		$objLoginRN = new LoginRN();
	  		$objLoginRN->excluir($objLoginRN->listar($objLoginDTO));
				
        $objUsuarioBD->excluir($arrObjUsuarioDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo Usuário.',$e);
    }
  }

  protected function desativarControlado($arrObjUsuarioDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('usuario_desativar',__METHOD__,$arrObjUsuarioDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      for($i=0;$i<count($arrObjUsuarioDTO);$i++){
        //Atualiza usuario nos sistemas
        $objPermissaoDTO = new PermissaoDTO();
        $objPermissaoDTO->setDistinct(true);
        $objPermissaoDTO->retNumIdSistema();
        $objPermissaoDTO->retNumIdUsuario();
        $objPermissaoDTO->setNumIdUsuario($arrObjUsuarioDTO[$i]->getNumIdUsuario());
        
        $objPermissaoRN = new PermissaoRN();
        $arrObjPermissaoDTO = $objPermissaoRN->listar($objPermissaoDTO);
        
        $objSistemaRN = new SistemaRN();
        
        foreach($arrObjPermissaoDTO as $objPermissaoDTO){
          $objReplicacaoUsuarioDTO = new ReplicacaoUsuarioDTO();
          $objReplicacaoUsuarioDTO->setStrStaOperacao('D');
          $objReplicacaoUsuarioDTO->setNumIdSistema($objPermissaoDTO->getNumIdSistema());
          $objReplicacaoUsuarioDTO->setNumIdUsuario($objPermissaoDTO->getNumIdUsuario());
          
          $objSistemaRN->replicarUsuario($objReplicacaoUsuarioDTO);
        }
      }
      
      $objUsuarioBD = new UsuarioBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjUsuarioDTO);$i++){
        $objUsuarioBD->desativar($arrObjUsuarioDTO[$i]);
      }

      
      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando Usuário.',$e);
    }
  }

  protected function reativarControlado($arrObjUsuarioDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('usuario_reativar',__METHOD__,$arrObjUsuarioDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objUsuarioBD = new UsuarioBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjUsuarioDTO);$i++){
        $objUsuarioBD->reativar($arrObjUsuarioDTO[$i]);
      }

      for($i=0;$i<count($arrObjUsuarioDTO);$i++){
        //Atualiza usuario nos sistemas
        $objPermissaoDTO = new PermissaoDTO();
        $objPermissaoDTO->setDistinct(true);
        $objPermissaoDTO->retNumIdSistema();
        $objPermissaoDTO->retNumIdUsuario();
        $objPermissaoDTO->setNumIdUsuario($arrObjUsuarioDTO[$i]->getNumIdUsuario());
        
        $objPermissaoRN = new PermissaoRN();
        $arrObjPermissaoDTO = $objPermissaoRN->listar($objPermissaoDTO);
        
        $objSistemaRN = new SistemaRN();
        
        foreach($arrObjPermissaoDTO as $objPermissaoDTO){
          $objReplicacaoUsuarioDTO = new ReplicacaoUsuarioDTO();
          $objReplicacaoUsuarioDTO->setStrStaOperacao('R');
          $objReplicacaoUsuarioDTO->setNumIdSistema($objPermissaoDTO->getNumIdSistema());
          $objReplicacaoUsuarioDTO->setNumIdUsuario($objPermissaoDTO->getNumIdUsuario());
          
          $objSistemaRN->replicarUsuario($objReplicacaoUsuarioDTO);
        }
      }
      
      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando Usuário.',$e);
    }
  }
  
  protected function consultarConectado(UsuarioDTO $objUsuarioDTO){
    try {

       //Não valida permissão porque é acessado pelo procedimento de login
			 /////////////////////////////////////////////////////////////////
      //SessaoSip::getInstance()->validarAuditarPermissao('usuario_consultar',__METHOD__,$objUsuarioDTO);
			/////////////////////////////////////////////////////////////////

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objUsuarioBD = new UsuarioBD($this->getObjInfraIBanco());
      $ret = $objUsuarioBD->consultar($objUsuarioDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando Usuário.',$e);
    }
  }

  protected function listarConectado(UsuarioDTO $objUsuarioDTO) {
    try {

      //Não valida permissão porque é acessado pelo procedimento de login

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();
      $objUsuarioBD = new UsuarioBD($this->getObjInfraIBanco());
      $ret = $objUsuarioBD->listar($objUsuarioDTO);
      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Usuários.',$e);
    }
  }

  protected function contarConectado(UsuarioDTO $objUsuarioDTO) {
    try {
      ////////////////////////////////////////////////////////////////////// 
      //SessaoSip::getInstance()->validarAuditarPermissao('usuario_contar',__METHOD__,$objUsuarioDTO);
			//////////////////////////////////////////////////////////////////////


      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objUsuarioBD = new UsuarioBD($this->getObjInfraIBanco());
      $ret = $objUsuarioBD->contar($objUsuarioDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro contando Usuários.',$e);
    }
  }
  
  private function validarNumIdOrgao(UsuarioDTO $objUsuarioDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objUsuarioDTO->getNumIdOrgao())){
      $objInfraException->adicionarValidacao('Órgão não informado.');
    }
  }

  private function validarStrIdOrigem(UsuarioDTO $objUsuarioDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objUsuarioDTO->getStrIdOrigem())) {
      $objUsuarioDTO->setStrIdOrigem(null);
    }else {

      $objUsuarioDTO->setStrIdOrigem(trim($objUsuarioDTO->getStrIdOrigem()));

      if (strlen($objUsuarioDTO->getStrIdOrigem()) > 50) {
        $objInfraException->adicionarValidacao('Identificador de origem possui tamanho superior a 50 caracteres.');
      }

      if ($objUsuarioDTO->getNumIdUsuario()==null) {
        $dto = new UsuarioDTO();
        $dto->retStrSigla();
        $dto->retStrIdOrigem();
        $dto->retStrSiglaOrgao();
        $dto->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario(), InfraDTO::$OPER_DIFERENTE);
        $dto->setNumIdOrgao($objUsuarioDTO->getNumIdOrgao());
        $dto->setStrIdOrigem($objUsuarioDTO->getStrIdOrigem());
        $arr = $this->listar($dto);
        foreach ($arr as $dto) {
          $objInfraException->adicionarValidacao('Existe outro usuário no órgão '.$dto->getStrSigla().'/'.$dto->getStrSiglaOrgao().' com o mesmo identificador de origem ['.$dto->getStrIdOrigem().'].');
        }
      }
    }
  }
  
  private function validarStrSigla(UsuarioDTO $objUsuarioDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objUsuarioDTO->getStrSigla())){
      $objInfraException->adicionarValidacao('Sigla não informada.');
    }
    $objUsuarioDTO->setStrSigla(trim($objUsuarioDTO->getStrSigla()));

    if (strlen($objUsuarioDTO->getStrSigla())>100){
      $objInfraException->adicionarValidacao('Sigla possui tamanho superior a 100 caracteres.');
    }

    $dto = new UsuarioDTO();
    $dto->retStrSigla();
    $dto->retStrSiglaOrgao();
    $dto->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario(),InfraDTO::$OPER_DIFERENTE);
    $dto->setNumIdOrgao($objUsuarioDTO->getNumIdOrgao());
    $dto->setStrSigla($objUsuarioDTO->getStrSigla());
    $dto = $this->consultar($dto);
    if ($dto != null){
      $objInfraException->adicionarValidacao('Existe outro usuário neste órgão com esta sigla ['.$dto->getStrSigla().'/'.$dto->getStrSiglaOrgao().'].');
    }
  }
	
  private function validarStrNome(UsuarioDTO $objUsuarioDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objUsuarioDTO->getStrNome())){
      $objInfraException->adicionarValidacao('Nome não informado.');
    }

    if (strlen($objUsuarioDTO->getStrNome())>100){
      $objInfraException->adicionarValidacao('Nome possui tamanho superior a 100 caracteres.');
    }
  }
	
  private function validarStrSinAtivo(UsuarioDTO $objUsuarioDTO, InfraException $objInfraException){
    if ($objUsuarioDTO->getStrSinAtivo()===null || ($objUsuarioDTO->getStrSinAtivo()!=='S' && $objUsuarioDTO->getStrSinAtivo()!=='N')){
      $objInfraException->adicionarValidacao('Sinalizador de Exclusão Lógica inválido.');
    }
  }
}
?>