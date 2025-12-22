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

require_once dirname(__FILE__).'/../SEI.php';

class BlocoRN extends InfraRN {

  public static $TB_ASSINATURA = 'A';
  public static $TB_REUNIAO = 'R';
  public static $TB_INTERNO = 'I';
  
  public static $TE_ABERTO = 'A';
  public static $TE_DISPONIBILIZADO = 'D';
  public static $TE_RETORNADO = 'R';
  public static $TE_CONCLUIDO = 'C';
  
  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }

  public function listarValoresEstadoRN1265(){
    try {

      $objArrEstadoBlocoDTO = array();

      $objEstadoBlocoDTO = new EstadoBlocoDTO();
      $objEstadoBlocoDTO->setStrStaEstado(self::$TE_ABERTO);
      $objEstadoBlocoDTO->setStrDescricao('Aberto');
      $objArrEstadoBlocoDTO[] = $objEstadoBlocoDTO;

      $objEstadoBlocoDTO = new EstadoBlocoDTO();
      $objEstadoBlocoDTO->setStrStaEstado(self::$TE_DISPONIBILIZADO);
      $objEstadoBlocoDTO->setStrDescricao('Disponibilizado');
      $objArrEstadoBlocoDTO[] = $objEstadoBlocoDTO;

      $objEstadoBlocoDTO = new EstadoBlocoDTO();
      $objEstadoBlocoDTO->setStrStaEstado(self::$TE_RETORNADO);
      $objEstadoBlocoDTO->setStrDescricao('Retornado');
      $objArrEstadoBlocoDTO[] = $objEstadoBlocoDTO;
      
      $objEstadoBlocoDTO = new EstadoBlocoDTO();
      $objEstadoBlocoDTO->setStrStaEstado(self::$TE_CONCLUIDO);
      $objEstadoBlocoDTO->setStrDescricao('Concluído');
      $objArrEstadoBlocoDTO[] = $objEstadoBlocoDTO;

      return $objArrEstadoBlocoDTO;

    }catch(Exception $e){
      throw new InfraException('Erro listando valores de Estado.',$e);
    }
  }
  
  public function listarValoresTipo(){
    try {

      $arrObjTipoDTO = array();

      $objTipoDTO = new TipoDTO();
      $objTipoDTO->setStrStaTipo(self::$TB_ASSINATURA);
      $objTipoDTO->setStrDescricao('Assinatura');
      $arrObjTipoDTO[] = $objTipoDTO;

      $objTipoDTO = new TipoDTO();
      $objTipoDTO->setStrStaTipo(self::$TB_REUNIAO);
      $objTipoDTO->setStrDescricao('Reunião');
      $arrObjTipoDTO[] = $objTipoDTO;
      
      $objTipoDTO = new TipoDTO();
      $objTipoDTO->setStrStaTipo(self::$TB_INTERNO);
      $objTipoDTO->setStrDescricao('Interno');
      $arrObjTipoDTO[] = $objTipoDTO;

      
      return $arrObjTipoDTO;

    }catch(Exception $e){
      throw new InfraException('Erro listando valores de Tipo.',$e);
    }
  }  

  private function validarStrStaTipoRN1266(BlocoDTO $objBlocoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objBlocoDTO->getStrStaTipo())){
      $objInfraException->adicionarValidacao('Tipo não informado.');
    }else{
      if (!in_array($objBlocoDTO->getStrStaTipo(),InfraArray::converterArrInfraDTO($this->listarValoresTipo(),'StaTipo'))){
        $objInfraException->adicionarValidacao('Tipo inválido.');
      }
    }
  }

  private function validarNumIdUnidadeRN1267(BlocoDTO $objBlocoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objBlocoDTO->getNumIdUnidade())){
      $objInfraException->adicionarValidacao('Unidade não informada.');
    }
  }

  private function validarNumIdUsuarioRN1268(BlocoDTO $objBlocoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objBlocoDTO->getNumIdUsuario())){
      $objInfraException->adicionarValidacao('Usuário não informado.');
    }
  }

  private function validarStrDescricaoRN1269(BlocoDTO $objBlocoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objBlocoDTO->getStrDescricao())){
      $objBlocoDTO->setStrDescricao(null);
    }else{
      $objBlocoDTO->setStrDescricao(trim($objBlocoDTO->getStrDescricao()));
      $objBlocoDTO->setStrDescricao(InfraUtil::filtrarISO88591($objBlocoDTO->getStrDescricao()));
      if (strlen($objBlocoDTO->getStrDescricao())>$this->getNumMaxTamanhoDescricao()){
        $objInfraException->adicionarValidacao('Descrição possui tamanho superior a '.$this->getNumMaxTamanhoDescricao().' caracteres.');
      }
    }
  }

  public function getNumMaxTamanhoDescricao(){
    return 250;
  }

  private function validarStrAnotacaoRN1270(BlocoDTO $objBlocoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objBlocoDTO->getStrAnotacao())){
      $objBlocoDTO->setStrAnotacao(null);
    }else{
      $objBlocoDTO->setStrAnotacao(trim($objBlocoDTO->getStrAnotacao()));
    }
  }

  private function validarStrIdxBlocoRN1271(BlocoDTO $objBlocoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objBlocoDTO->getStrIdxBloco())){
      $objBlocoDTO->setStrIdxBloco(null);
    }else{
      $objBlocoDTO->setStrIdxBloco(trim($objBlocoDTO->getStrIdxBloco()));
      if (strlen($objBlocoDTO->getStrIdxBloco()) > 500){
        $objInfraException->adicionarValidacao('Indexação possui tamanho superior a 500 caracteres.');
      }
    }
  }

  private function validarStrStaEstadoRN1272(BlocoDTO $objBlocoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objBlocoDTO->getStrStaEstado())){
      $objInfraException->adicionarValidacao('Estado não informado.');
    }else{
      if (!in_array($objBlocoDTO->getStrStaEstado(),InfraArray::converterArrInfraDTO($this->listarValoresEstadoRN1265(),'StaEstado'))){
        $objInfraException->adicionarValidacao('Estado inválido.');
      }
    }
  }

  private function validarArrObjRelBlocoUnidadeDTO(BlocoDTO $objBlocoDTO, InfraException $objInfraException){
    if (!is_array($objBlocoDTO->getArrObjRelBlocoUnidadeDTO())){
      $objInfraException->adicionarValidacao('Conjunto de unidades para disponibilização inválido.');
    }
    
    if (count($objBlocoDTO->getArrObjRelBlocoUnidadeDTO())>0 && $objBlocoDTO->getStrStaTipo()==self::$TB_INTERNO){
      $objInfraException->adicionarValidacao('Bloco interno não pode ser disponibilizado para outras unidades.');
    }
  }
  
  protected function cadastrarRN1273Controlado(BlocoDTO $objBlocoDTO) {
    try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('bloco_cadastrar',__METHOD__,$objBlocoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();
      
      
      $this->validarStrStaTipoRN1266($objBlocoDTO, $objInfraException);
      $this->validarNumIdUnidadeRN1267($objBlocoDTO, $objInfraException);
      $this->validarNumIdUsuarioRN1268($objBlocoDTO, $objInfraException);
      $this->validarStrDescricaoRN1269($objBlocoDTO, $objInfraException);
      //$this->validarStrAnotacaoRN1270($objBlocoDTO, $objInfraException);
      $this->validarStrIdxBlocoRN1271($objBlocoDTO, $objInfraException);
      $this->validarStrStaEstadoRN1272($objBlocoDTO, $objInfraException);
      $this->validarArrObjRelBlocoUnidadeDTO($objBlocoDTO, $objInfraException);
      
      
      $objInfraException->lancarValidacoes();

      $objBlocoBD = new BlocoBD($this->getObjInfraIBanco());
      $ret = $objBlocoBD->cadastrar($objBlocoDTO);

      $this->montarIndexacao($ret);
      
        
      $arrObjRelBlocoUnidadeDTO = $objBlocoDTO->getArrObjRelBlocoUnidadeDTO();
      
      $objRelBlocoUnidadeRN = new RelBlocoUnidadeRN();
      foreach($arrObjRelBlocoUnidadeDTO as $objRelBlocoUnidadeDTO){
        $objRelBlocoUnidadeDTO->setNumIdBloco($ret->getNumIdBloco());
        $objRelBlocoUnidadeDTO->setStrSinRetornado('N');
        $objRelBlocoUnidadeRN->cadastrarRN1300($objRelBlocoUnidadeDTO);
      }
      
      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando Bloco.',$e);
    }
  }

  protected function alterarRN1274Controlado(BlocoDTO $objBlocoDTO){
    try {

      //Valida Permissao
  	   SessaoSEI::getInstance()->validarAuditarPermissao('bloco_alterar',__METHOD__,$objBlocoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $dto = new BlocoDTO();
      $dto->retStrStaTipo();
      $dto->setNumIdBloco($objBlocoDTO->getNumIdBloco());
      
      $dto = $this->consultarRN1276($dto);
      
      if ($objBlocoDTO->isSetStrStaTipo() && $objBlocoDTO->getStrStaTipo()!=$dto->getStrStaTipo()){
        $objInfraException->lancarValidacao('Não é possível alterar o tipo do bloco.');
      }
      
      $objBlocoDTO->setStrStaTipo($dto->getStrStaTipo());
      
      if ($objBlocoDTO->isSetStrStaTipo()){
        $this->validarStrStaTipoRN1266($objBlocoDTO, $objInfraException);
      }
      if ($objBlocoDTO->isSetNumIdUnidade()){
        $this->validarNumIdUnidadeRN1267($objBlocoDTO, $objInfraException);
      }
      if ($objBlocoDTO->isSetNumIdUsuario()){
        $this->validarNumIdUsuarioRN1268($objBlocoDTO, $objInfraException);
      }
      if ($objBlocoDTO->isSetStrDescricao()){
        $this->validarStrDescricaoRN1269($objBlocoDTO, $objInfraException);
      }
      /*if ($objBlocoDTO->isSetStrAnotacao()){
        $this->validarStrAnotacaoRN1270($objBlocoDTO, $objInfraException);
      }*/
      if ($objBlocoDTO->isSetStrIdxBloco()){
        $this->validarStrIdxBlocoRN1271($objBlocoDTO, $objInfraException);
      }
      if ($objBlocoDTO->isSetStrStaEstado()){
        $this->validarStrStaEstadoRN1272($objBlocoDTO, $objInfraException);
      }
      if ($objBlocoDTO->isSetArrObjRelBlocoUnidadeDTO()){
        $this->validarArrObjRelBlocoUnidadeDTO($objBlocoDTO, $objInfraException);
      }

      $objInfraException->lancarValidacoes();

      $objBlocoBD = new BlocoBD($this->getObjInfraIBanco());
      $objBlocoBD->alterar($objBlocoDTO);
      
      $this->montarIndexacao($objBlocoDTO);
      
      if ($objBlocoDTO->isSetArrObjRelBlocoUnidadeDTO()){
        
        $objRelBlocoUnidadeDTO = new RelBlocoUnidadeDTO();
        $objRelBlocoUnidadeDTO->retNumIdBloco();
        $objRelBlocoUnidadeDTO->retNumIdUnidade();
        $objRelBlocoUnidadeDTO->setNumIdBloco($objBlocoDTO->getNumIdBloco());
        
        $objRelBlocoUnidadeRN = new RelBlocoUnidadeRN();
        $objRelBlocoUnidadeRN->excluirRN1302($objRelBlocoUnidadeRN->listarRN1304($objRelBlocoUnidadeDTO));
        
        $arrObjRelBlocoUnidadeDTO = $objBlocoDTO->getArrObjRelBlocoUnidadeDTO();
        foreach($arrObjRelBlocoUnidadeDTO as $objRelBlocoUnidadeDTO){
          $objRelBlocoUnidadeDTO->setNumIdBloco($objBlocoDTO->getNumIdBloco());
          $objRelBlocoUnidadeDTO->setStrSinRetornado('N');
          $objRelBlocoUnidadeRN->cadastrarRN1300($objRelBlocoUnidadeDTO);
        }
      }      

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando Bloco.',$e);
    }
  }

  protected function excluirRN1275Controlado($arrObjBlocoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('bloco_excluir',__METHOD__,$arrObjBlocoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objBlocoBD = new BlocoBD($this->getObjInfraIBanco());
      
      for($i=0;$i<count($arrObjBlocoDTO);$i++){
        
      	$objBlocoDTO = new BlocoDTO();
      	$objBlocoDTO->retNumIdBloco();
      	$objBlocoDTO->retNumIdUnidade();
      	$objBlocoDTO->retStrStaEstado();
      	$objBlocoDTO->retStrStaTipo();
      	$objBlocoDTO->setNumIdBloco($arrObjBlocoDTO[$i]->getNumIdBloco());
      	$objBlocoDTO = $this->consultarRN1276($objBlocoDTO);
      	
      	if ($objBlocoDTO==null){
      		$objInfraException->lancarValidacao('Bloco '.$arrObjBlocoDTO[$i]->getNumIdBloco().' não encontrado.');
      	}
      	
      	if($objBlocoDTO->getNumIdUnidade()!=SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
      		$objInfraException->lancarValidacao('Bloco '.$arrObjBlocoDTO[$i]->getNumIdBloco().' não pertence à unidade '.SessaoSEI::getInstance()->getStrSiglaUnidadeAtual().'.');
      	}else{
      		/* verifica se há processos/documentos associados */
      		$objRelBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
          $objRelBlocoProtocoloDTO->retDblIdProtocolo();
      		$objRelBlocoProtocoloDTO->setNumIdBloco($arrObjBlocoDTO[$i]->getNumIdBloco());
          $objRelBlocoProtocoloDTO->setNumMaxRegistrosRetorno(1);
      		
      		$objRelBlocoProtocoloRN = new RelBlocoProtocoloRN();
      		if ($objRelBlocoProtocoloRN->consultarRN1290($objRelBlocoProtocoloDTO) != null){
      		  if ($objBlocoDTO->getStrStaTipo()==BlocoRN::$TB_ASSINATURA){
      		    $objInfraException->lancarValidacao('Bloco '.$arrObjBlocoDTO[$i]->getNumIdBloco().' possui documentos.');
      		  }else{
      		    $objInfraException->lancarValidacao('Bloco '.$arrObjBlocoDTO[$i]->getNumIdBloco().' possui processos.');
      		  }
      		}

      		if ($objBlocoDTO->getStrStaEstado()==self::$TE_DISPONIBILIZADO){
      		  $objInfraException->lancarValidacao('Bloco '.$arrObjBlocoDTO[$i]->getNumIdBloco().' não pode estar disponibilizado.');
      		}
      		
      		/* verifica se há disponibilizações para outras unidades */
      		$objRelBlocoUnidadeDTO = new RelBlocoUnidadeDTO();
      		$objRelBlocoUnidadeDTO->retNumIdUnidade();
      		$objRelBlocoUnidadeDTO->retNumIdBloco();
      		$objRelBlocoUnidadeDTO->setNumIdBloco($arrObjBlocoDTO[$i]->getNumIdBloco());
      		
      		$objRelBlocoUnidadeRN = new RelBlocoUnidadeRN();
      		$objRelBlocoUnidadeRN->excluirRN1302($objRelBlocoUnidadeRN->listarRN1304($objRelBlocoUnidadeDTO));
      		
      		/* excluir o bloco propriamente dito */
        	$objBlocoBD->excluir($arrObjBlocoDTO[$i]);
      	}
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo Bloco.',$e);
    }
  }

  protected function consultarRN1276Conectado(BlocoDTO $objBlocoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('bloco_consultar',__METHOD__,$objBlocoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      if ($objBlocoDTO->isRetStrTipoDescricao()) {
        $objBlocoDTO->retStrStaTipo();
      }
      
      if ($objBlocoDTO->isRetStrStaEstadoDescricao()) {
        $objBlocoDTO->retStrStaEstado();
      }
      
      $objBlocoBD = new BlocoBD($this->getObjInfraIBanco());
      $ret = $objBlocoBD->consultar($objBlocoDTO);

      if ($ret != null){
        if ($objBlocoDTO->isRetStrTipoDescricao()) {
        	$arrObjTipoDTO = $this->listarValoresTipo();
      		foreach ($arrObjTipoDTO as $objTipoDTO) {
      			if ($ret->getStrStaTipo() == $objTipoDTO->getStrStaTipo()){
      				$ret->setStrTipoDescricao($objTipoDTO->getStrDescricao());
      				break;
      			}
      		}
        }
        
        if ($objBlocoDTO->isRetStrStaEstadoDescricao()) {
        	$arrObjEstadoBlocoDTO = $this->listarValoresEstadoRN1265();
      		foreach ($arrObjEstadoBlocoDTO as $objEstadoBlocoDTO) {
      			if ($ret->getStrStaEstado() == $objEstadoBlocoDTO->getStrStaEstado()){
      				$ret->setStrStaEstadoDescricao($objEstadoBlocoDTO->getStrDescricao());
      				break;
      			}
      		}
        }
      }   
      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando Bloco.',$e);
    }
  }

  protected function listarRN1277Conectado(BlocoDTO $objBlocoDTO) {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('bloco_listar',__METHOD__,$objBlocoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();
      
      //$objInfraException->lancarValidacoes();

      if ($objBlocoDTO->isRetStrTipoDescricao()) {
        $objBlocoDTO->retStrStaTipo();
      }
      
      if ($objBlocoDTO->isRetStrStaEstadoDescricao()) {
        $objBlocoDTO->retStrStaEstado();
      }
      
      $objBlocoBD = new BlocoBD($this->getObjInfraIBanco());
      $ret = $objBlocoBD->listar($objBlocoDTO);

      if ($objBlocoDTO->isRetStrTipoDescricao()) {
      	$arrObjTipoDTO = $this->listarValoresTipo();
      	foreach ($ret as $dto) {
      		foreach ($arrObjTipoDTO as $objTipoDTO) {
      			if ($dto->getStrStaTipo() == $objTipoDTO->getStrStaTipo()){
      				$dto->setStrTipoDescricao($objTipoDTO->getStrDescricao());
      				break;
      			}
      		}
      	}
      }
      
      if ($objBlocoDTO->isRetStrStaEstadoDescricao()) {
      	$arrObjEstadoBlocoDTO = $this->listarValoresEstadoRN1265();
      	foreach ($ret as $dto) {
      		foreach ($arrObjEstadoBlocoDTO as $objEstadoBlocoDTO) {
      			if ($dto->getStrStaEstado() == $objEstadoBlocoDTO->getStrStaEstado()){
      				$dto->setStrStaEstadoDescricao($objEstadoBlocoDTO->getStrDescricao());
      				break;
      			}
      		}
      	}
      }

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Blocos.',$e);
    }
  }

  protected function contarRN1278Conectado(BlocoDTO $objBlocoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('bloco_listar',__METHOD__,$objBlocoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objBlocoBD = new BlocoBD($this->getObjInfraIBanco());
      $ret = $objBlocoBD->contar($objBlocoDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro contando Blocos.',$e);
    }
  }

  protected function reabrirControlado(BlocoDTO $objBlocoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('bloco_reabrir',__METHOD__,$objBlocoDTO);
			
      //Regras de Negocio
      $objInfraException = new InfraException();

      $dto = new BlocoDTO();
      $dto->retNumIdBloco();
      $dto->retNumIdUnidade();
      $dto->retStrStaEstado();
      $dto->setNumIdBloco($objBlocoDTO->getNumIdBloco());
      
      $dto = $this->consultarRN1276($dto);

      if($dto->getNumIdUnidade()!=SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
      	$objInfraException->adicionarValidacao('Bloco '.$dto->getNumIdBloco().' não pertencte a esta unidade.');
      }
      
      if($dto->getStrStaEstado()!=BlocoRN::$TE_CONCLUIDO){
      	$objInfraException->adicionarValidacao('Bloco '.$dto->getNumIdBloco().' não está concluído.');
      } 
      
      $objInfraException->lancarValidacoes();

      $this->lancarAndamentoBloco($objBlocoDTO->getNumIdBloco(),TarefaRN::$TI_BLOCO_REABERTURA);
      
      $dto = new BlocoDTO();
     	$dto->setStrStaEstado(BlocoRN::$TE_ABERTO);
      $dto->setNumIdBloco($objBlocoDTO->getNumIdBloco());
     	
      $objBlocoBD = new BlocoBD($this->getObjInfraIBanco());
      $objBlocoBD->alterar($dto);      

      //Auditoria

      return $dto;
      
    }catch(Exception $e){
      throw new InfraException('Erro reabrindo Bloco.',$e);
    }
  }

  protected function pesquisarConectado(BlocoDTO $objBlocoDTO) {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('bloco_listar',__METHOD__,$objBlocoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();
      
      //$objInfraException->lancarValidacoes();
      
			if ($objBlocoDTO->isSetStrPalavrasPesquisa()){
				if (!InfraString::isBolVazia($objBlocoDTO->getStrPalavrasPesquisa())){
					$strPalavrasPesquisa = InfraString::prepararIndexacao($objBlocoDTO->getStrPalavrasPesquisa());
					
					$arrPalavrasPesquisa = explode(' ',$strPalavrasPesquisa);	    						
					
					$numPalavrasPesquisa = count($arrPalavrasPesquisa);
					
					if ($numPalavrasPesquisa){
					  
       			for($i=0;$i<$numPalavrasPesquisa;$i++){
       			  $arrPalavrasPesquisa[$i] = '%'.$arrPalavrasPesquisa[$i].'%';
       			}
     			
      			if ($numPalavrasPesquisa==1){
      				$objBlocoDTO->setStrIdxBloco($arrPalavrasPesquisa[0],InfraDTO::$OPER_LIKE);
      			}else{
      				$a = array_fill(0,$numPalavrasPesquisa,'IdxBloco');
      				$b = array_fill(0,$numPalavrasPesquisa,InfraDTO::$OPER_LIKE);
      				$d = array_fill(0,$numPalavrasPesquisa-1,InfraDTO::$OPER_LOGICO_AND);
      				$objBlocoDTO->adicionarCriterio($a,$b,$arrPalavrasPesquisa,$d);
      			}
     			}
    		}
			}

			if ($objBlocoDTO->isRetStrSinVazio() || $objBlocoDTO->isRetArrObjRelBlocoUnidadeDTO()){
			  $objBlocoDTO->retNumIdBloco();
			  $objBlocoDTO->retNumIdUnidade();
			}

      if (!$objBlocoDTO->isSetNumIdBloco()) {
        //lista blocos fechados que foram disponibilizados para esta unidade e ainda não foram retornados
        $objRelBlocoUnidadeDTO = new RelBlocoUnidadeDTO();
        $objRelBlocoUnidadeDTO->retNumIdBloco();
        //$objRelBlocoUnidadeDTO->retStrSiglaUnidadeBloco();
        //$objRelBlocoUnidadeDTO->retStrDescricaoUnidadeBloco();
        $objRelBlocoUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objRelBlocoUnidadeDTO->setStrStaEstadoBloco(BlocoRN::$TE_DISPONIBILIZADO);
        $objRelBlocoUnidadeDTO->setStrSinRetornado('N');

        $objRelBlocoUnidadeRN = new RelBlocoUnidadeRN();
        $arrObjRelBlocoUnidadeDTO = $objRelBlocoUnidadeRN->listarRN1304($objRelBlocoUnidadeDTO);

        //lista todos os blocos da unidade ou que foram disponibilizados para ela
        if (count($arrObjRelBlocoUnidadeDTO) > 0) {
          $objBlocoDTO->adicionarCriterio(array('IdUnidade', 'IdBloco'),
              array(InfraDTO::$OPER_IGUAL, InfraDTO::$OPER_IN),
              array(SessaoSEI::getInstance()->getNumIdUnidadeAtual(), InfraArray::converterArrInfraDTO($arrObjRelBlocoUnidadeDTO, 'IdBloco')),
              array(InfraDTO::$OPER_LOGICO_OR));
        } else {
          $objBlocoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        }
      }
    	
      $ret = $this->listarRN1277($objBlocoDTO);

			if ($objBlocoDTO->isRetStrSinVazio()){
      
        $objRelBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
        $objRelBlocoProtocoloRN = new RelBlocoProtocoloRN();
        
        foreach ($ret as $dto){
        	$objRelBlocoProtocoloDTO->setNumIdBloco($dto->getNumIdBloco());
        	if ($objRelBlocoProtocoloRN->contarRN1292($objRelBlocoProtocoloDTO)>0){
        		$dto->setStrSinVazio('N');
        	}else{
        		$dto->setStrSinVazio('S');
        	}
        }
			}
			
			if ($objBlocoDTO->isRetArrObjRelBlocoUnidadeDTO()){
      
        $objRelBlocoUnidadeDTO = new RelBlocoUnidadeDTO();
        $objRelBlocoUnidadeDTO->retNumIdBloco();
        $objRelBlocoUnidadeDTO->retNumIdUnidade();
        $objRelBlocoUnidadeDTO->retStrSiglaUnidade();
        $objRelBlocoUnidadeDTO->retStrDescricaoUnidade();
        $objRelBlocoUnidadeDTO->setOrdStrSiglaUnidade(InfraDTO::$TIPO_ORDENACAO_ASC);
        
        $objRelBlocoUnidadeRN = new RelBlocoUnidadeRN();
        
        foreach ($ret as $dto){
          //se o bloco é da unidade atual
        	if ($dto->getNumIdUnidade()==SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
        	  
        	  //lista todas as unidades para as quais ele foi disponilizado por esta unidade
        	  $objRelBlocoUnidadeDTO->setNumIdUnidadeBloco(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        	  $objRelBlocoUnidadeDTO->setNumIdBloco($dto->getNumIdBloco());
        	  $dto->setArrObjRelBlocoUnidadeDTO($objRelBlocoUnidadeRN->listarRN1304($objRelBlocoUnidadeDTO));
        	}else{
        	  $dto->setArrObjRelBlocoUnidadeDTO(array());
        	}
        }
			}
			      
      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro pesquisando Blocos.',$e);
    }
  }
  
  protected function montarIndexacaoControlado(BlocoDTO $objBlocoDTO){
  	try{

      //Regras de Negocio
	  		  	
	  	$dto = new BlocoDTO();
	  	$dto->retTodos();
	  	$dto->setNumIdBloco($objBlocoDTO->getNumIdBloco());

	  	$dto = $this->consultarRN1276($dto);

	  	$strIndexacao = $dto->getNumIdBloco();
	  	$strIndexacao .= ' ';
	  	$strIndexacao .= $dto->getStrDescricao();

	  	$strIndexacao = InfraString::prepararIndexacao($strIndexacao);

	  	$objBlocoDTO = new BlocoDTO();
	  	$objBlocoDTO->setNumIdBloco($dto->getNumIdBloco());
	  	$objBlocoDTO->setStrIdxBloco($strIndexacao);

      $objInfraException = new InfraException();
      $this->validarStrIdxBlocoRN1271($objBlocoDTO, $objInfraException);
      $objInfraException->lancarValidacoes();

	  	$objBlocoBD = new BlocoBD($this->getObjInfraIBanco());
	  	$objBlocoBD->alterar($objBlocoDTO);

    }catch(Exception $e){
      throw new InfraException('Erro montando indexação de bloco.',$e);
    }
  }
  
/* 
  protected function desativarRN1279Controlado($arrObjBlocoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('bloco_desativar');

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objBlocoBD = new BlocoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjBlocoDTO);$i++){
        $objBlocoBD->desativar($arrObjBlocoDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando Bloco.',$e);
    }
  }

  protected function reativarRN1280Controlado($arrObjBlocoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('bloco_reativar');

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objBlocoBD = new BlocoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjBlocoDTO);$i++){
        $objBlocoBD->reativar($arrObjBlocoDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando Bloco.',$e);
    }
  }

  protected function bloquearRN1281Controlado(BlocoDTO $objBlocoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('bloco_consultar');

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objBlocoBD = new BlocoBD($this->getObjInfraIBanco());
      $ret = $objBlocoBD->bloquear($objBlocoDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro bloqueando Bloco.',$e);
    }
  }

 */

  protected function disponibilizarControlado($arrObjBlocoDTO) {
    try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('bloco_disponibilizar',__METHOD__,$arrObjBlocoDTO);

      
      //Regras de Negocio
      
      $objInfraException = new InfraException();
      
      if (count($arrObjBlocoDTO)==0){
      	$objInfraException->lancarValidacao('Nenhum bloco informado para disponibilização.');
      }

      $objBlocoDTO = new BlocoDTO();
      $objBlocoDTO->retStrStaTipo();
      $objBlocoDTO->retNumIdBloco();
      $objBlocoDTO->retNumIdUnidade();
      $objBlocoDTO->retStrStaEstado();
      $objBlocoDTO->retStrSinVazio();
      $objBlocoDTO->retArrObjRelBlocoUnidadeDTO();
      $objBlocoDTO->setNumIdBloco(InfraArray::converterArrInfraDTO($arrObjBlocoDTO,'IdBloco'),InfraDTO::$OPER_IN);
      
      $arr = $this->pesquisar($objBlocoDTO);
      
      foreach($arr as $objBlocoDTO){
        
        if ($objBlocoDTO->getNumIdUnidade()!=SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
          $objInfraException->adicionarValidacao('Bloco '.$objBlocoDTO->getNumIdBloco().' não pertence à unidade '.SessaoSEI::getInstance()->getStrSiglaUnidadeAtual().'.');
        }
        
        if ($objBlocoDTO->getStrStaEstado()==BlocoRN::$TE_DISPONIBILIZADO){
          $objInfraException->adicionarValidacao('Bloco '.$objBlocoDTO->getNumIdBloco().' já foi disponibilizado.');
        }

        if ($objBlocoDTO->getStrStaEstado()==BlocoRN::$TE_CONCLUIDO){
          $objInfraException->adicionarValidacao('Bloco '.$objBlocoDTO->getNumIdBloco().' está concluído.');
        }
        
        if ($objBlocoDTO->getStrStaTipo()==self::$TB_INTERNO){
          $objInfraException->adicionarValidacao('Bloco interno '.$objBlocoDTO->getNumIdBloco().' não pode ser disponibilizado.');
        }

        if ($objBlocoDTO->getStrSinVazio()=='S'){
          if ($objBlocoDTO->getStrStaTipo()==BlocoRN::$TB_ASSINATURA){
            $objInfraException->adicionarValidacao('Bloco '.$objBlocoDTO->getNumIdBloco().' não possui documentos.');  
          }else{
            $objInfraException->adicionarValidacao('Bloco '.$objBlocoDTO->getNumIdBloco().' não possui processos.');
          }
        }
                
        if (count($objBlocoDTO->getArrObjRelBlocoUnidadeDTO())==0){
          $objInfraException->adicionarValidacao('Bloco '.$objBlocoDTO->getNumIdBloco().' não possui unidades configuradas para disponibilização.');
        }
      }
      
      $objInfraException->lancarValidacoes();
      
      $objBlocoBD = new BlocoBD($this->getObjInfraIBanco());
      
      $objRelBlocoUnidadeRN = new RelBlocoUnidadeRN();
      
      foreach($arr as $objBlocoDTO){

	      foreach($objBlocoDTO->getArrObjRelBlocoUnidadeDTO() as $objRelBlocoUnidadeDTO){
	      	$objRelBlocoUnidadeDTO->setStrSinRetornado('N');
	      	$objRelBlocoUnidadeRN->alterarRN1301($objRelBlocoUnidadeDTO);
	      }
      	
        $dto = new BlocoDTO();
        $dto->setStrStaEstado(self::$TE_DISPONIBILIZADO);
        $dto->setNumIdBloco($objBlocoDTO->getNumIdBloco());

        $objBlocoBD->alterar($dto);
        
        if (count($objBlocoDTO->getArrObjRelBlocoUnidadeDTO())){
          $this->lancarAndamentoBloco($objBlocoDTO->getNumIdBloco(),TarefaRN::$TI_BLOCO_DISPONIBILIZACAO);
        }
      }
      
      //Auditoria

      return true;

    }catch(Exception $e){
      throw new InfraException('Erro disponibilizando bloco.',$e);
    }
  }
  
  protected function cancelarDisponibilizacaoControlado($arrObjBlocoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('bloco_cancelar_disponibilizacao',__METHOD__,$arrObjBlocoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();
      
      if (count($arrObjBlocoDTO)==0){
      	$objInfraException->lancarValidacao('Nenhum bloco informado para cancelamento de disponibilização.');
      }
      
      
      $objBlocoDTO = new BlocoDTO();
      $objBlocoDTO->retStrStaTipo();
      $objBlocoDTO->retNumIdBloco();
      $objBlocoDTO->retNumIdUnidade();
      $objBlocoDTO->retStrStaEstado();
      $objBlocoDTO->retArrObjRelBlocoUnidadeDTO();
      $objBlocoDTO->setNumIdBloco(InfraArray::converterArrInfraDTO($arrObjBlocoDTO,'IdBloco'),InfraDTO::$OPER_IN);
      
      $arr = $this->pesquisar($objBlocoDTO);
      
      foreach($arr as $objBlocoDTO){
        if ($objBlocoDTO->getNumIdUnidade()!=SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
          $objInfraException->adicionarValidacao('Bloco '.$objBlocoDTO->getNumIdBloco().' não pertence à unidade '.SessaoSEI::getInstance()->getStrSiglaUnidadeAtual().'.');
        }
        
        if ($objBlocoDTO->getStrStaEstado()!=BlocoRN::$TE_DISPONIBILIZADO){
          $objInfraException->adicionarValidacao('Bloco '.$objBlocoDTO->getNumIdBloco().' não está disponibilizado.');
        }
      }
      
      $objInfraException->lancarValidacoes();
      
      
      $objBlocoBD = new BlocoBD($this->getObjInfraIBanco());
      
      foreach($arr as $objBlocoDTO){
        
        $dto = new BlocoDTO();
        $dto->setStrStaEstado(self::$TE_ABERTO);
        $dto->setNumIdBloco($objBlocoDTO->getNumIdBloco());

        $objBlocoBD->alterar($dto);
        
        if (count($objBlocoDTO->getArrObjRelBlocoUnidadeDTO())){
          $this->lancarAndamentoBloco($objBlocoDTO->getNumIdBloco(),TarefaRN::$TI_BLOCO_CANCELAMENTO_DISPONIBILIZACAO);
        }
      }
      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro cancelando disponibilização.',$e);
    }
  }

  protected function retornarControlado($arrObjBlocoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('bloco_retornar',__METHOD__,$arrObjBlocoDTO);
			
      //Regras de Negocio
      $objInfraException = new InfraException();

      if (count($arrObjBlocoDTO)==0){
        $objInfraException->lancarValidacao('Nenhum bloco informado.');
      }
      
      $objBlocoDTO = new BlocoDTO();
      $objBlocoDTO->retStrStaTipo();
      $objBlocoDTO->retNumIdBloco();
      $objBlocoDTO->retNumIdUnidade();
      $objBlocoDTO->retStrStaEstado();
      $objBlocoDTO->setNumIdBloco(InfraArray::converterArrInfraDTO($arrObjBlocoDTO,'IdBloco'),InfraDTO::$OPER_IN);
      
      $arr = $this->pesquisar($objBlocoDTO);
      
      foreach($arr as $objBlocoDTO){
        if ($objBlocoDTO->getNumIdUnidade()==SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
          $objInfraException->adicionarValidacao('Bloco '.$objBlocoDTO->getNumIdBloco().' pertence a esta unidade.');
        }

        if ($objBlocoDTO->getStrStaEstado()!=BlocoRN::$TE_DISPONIBILIZADO){
          $objInfraException->adicionarValidacao('Bloco '.$objBlocoDTO->getNumIdBloco().' não está disponibilizado.');
        }
      }
      
      $objInfraException->lancarValidacoes();
            
      $objBlocoBD = new BlocoBD($this->getObjInfraIBanco());
      
      $objRelBlocoUnidadeRN = new RelBlocoUnidadeRN();
      foreach($arrObjBlocoDTO as $objBlocoDTO){
        
      	$objRelBlocoUnidadeDTO = new RelBlocoUnidadeDTO();
      	$objRelBlocoUnidadeDTO->setNumIdBloco($objBlocoDTO->getNumIdBloco());
      	$objRelBlocoUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      	$objRelBlocoUnidadeDTO->setStrSinRetornado('S');
      	
      	$objRelBlocoUnidadeRN->alterarRN1301($objRelBlocoUnidadeDTO);

        $this->lancarAndamentoBloco($objBlocoDTO->getNumIdBloco(),TarefaRN::$TI_BLOCO_RETORNO);
      	
      	$objRelBlocoUnidadeDTO = new RelBlocoUnidadeDTO();
      	$objRelBlocoUnidadeDTO->setNumIdBloco($objBlocoDTO->getNumIdBloco());
      	$objRelBlocoUnidadeDTO->setStrSinRetornado('N');
      	
      	if ($objRelBlocoUnidadeRN->contarRN1305($objRelBlocoUnidadeDTO)==0){
	        $dto = new BlocoDTO();
	        $dto->setStrStaEstado(self::$TE_RETORNADO);
	        $dto->setNumIdBloco($objBlocoDTO->getNumIdBloco());
	        $objBlocoBD->alterar($dto);
      	}
      }
            
      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro retornando bloco.',$e);
    }
  }

  protected function concluirControlado($arrObjBlocoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('bloco_concluir',__METHOD__,$arrObjBlocoDTO);
			
      $objInfraException = new InfraException();
      
      if (count($arrObjBlocoDTO)==0){
      	$objInfraException->lancarValidacao('Nenhum bloco informado para conclusão.');
      }
      
      $objBlocoDTO = new BlocoDTO();
      $objBlocoDTO->retStrStaTipo();
      $objBlocoDTO->retNumIdBloco();
      $objBlocoDTO->retNumIdUnidade();
      $objBlocoDTO->retStrStaEstado();
      $objBlocoDTO->retArrObjRelBlocoUnidadeDTO();
      $objBlocoDTO->setNumIdBloco(InfraArray::converterArrInfraDTO($arrObjBlocoDTO,'IdBloco'),InfraDTO::$OPER_IN);
      
      $arr = $this->pesquisar($objBlocoDTO);
      
      foreach($arr as $objBlocoDTO){
        if ($objBlocoDTO->getNumIdUnidade()!=SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
          $objInfraException->adicionarValidacao('Bloco '.$objBlocoDTO->getNumIdBloco().' não pertence à unidade '.SessaoSEI::getInstance()->getStrSiglaUnidadeAtual().'.');
        }
        
        if ($objBlocoDTO->getStrStaEstado()==BlocoRN::$TE_DISPONIBILIZADO && count($objBlocoDTO->getArrObjRelBlocoUnidadeDTO())>0){
          $objInfraException->adicionarValidacao('Bloco '.$objBlocoDTO->getNumIdBloco().' não pode estar disponibilizado para outras unidades.');
        }
        
        if ($objBlocoDTO->getStrStaEstado()==BlocoRN::$TE_CONCLUIDO){
          $objInfraException->adicionarValidacao('Bloco '.$objBlocoDTO->getNumIdBloco().' já foi concluído.');
        }
      }
      
      $objInfraException->lancarValidacoes();
            
      $objBlocoBD = new BlocoBD($this->getObjInfraIBanco());

      for($i=0;$i<count($arrObjBlocoDTO);$i++){

        $this->lancarAndamentoBloco($arrObjBlocoDTO[$i]->getNumIdBloco(),TarefaRN::$TI_BLOCO_CONCLUSAO);
        
        $dto = new BlocoDTO();
        $dto->setStrStaEstado(self::$TE_CONCLUIDO);
        $dto->setNumIdBloco($arrObjBlocoDTO[$i]->getNumIdBloco());

        $objBlocoBD->alterar($dto);
      }
            
      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro concluindo bloco.',$e);
    }
  }
  
  private function lancarAndamentoBloco($numIdBloco,  $numIdTarefa){
    try{

      $objRelBlocoProtocoloRN = new RelBlocoProtocoloRN();
      $objRelBlocoUnidadeRN = new RelBlocoUnidadeRN();
    
      //obtem protocolos do bloco
      $objRelBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
      $objRelBlocoProtocoloDTO->retDblIdProtocolo();
      $objRelBlocoProtocoloDTO->retStrStaProtocoloProtocolo();
      $objRelBlocoProtocoloDTO->setNumIdBloco($numIdBloco);
      
      $objRelBlocoProtocoloRN = new RelBlocoProtocoloRN();
      $arrObjRelBlocoProtocoloDTO = $objRelBlocoProtocoloRN->listarRN1291($objRelBlocoProtocoloDTO);  
      
      $arrIdProcessos = array();
      $arrIdDocumentos = array();
      foreach($arrObjRelBlocoProtocoloDTO as $objRelBlocoProtocoloDTO){
        if ($objRelBlocoProtocoloDTO->getStrStaProtocoloProtocolo()==ProtocoloRN::$TP_PROCEDIMENTO){
          $arrIdProcessos[] = $objRelBlocoProtocoloDTO->getDblIdProtocolo();
        }else{
          $arrIdDocumentos[] = $objRelBlocoProtocoloDTO->getDblIdProtocolo();
        }
      }
      
      //obtem processos dos documentos do bloco
      if (count($arrIdDocumentos)>0){
        $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
        $objRelProtocoloProtocoloDTO->setDistinct(true);
        $objRelProtocoloProtocoloDTO->retDblIdProtocolo1();
        $objRelProtocoloProtocoloDTO->setDblIdProtocolo2($arrIdDocumentos,InfraDTO::$OPER_IN);
        $objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_DOCUMENTO_ASSOCIADO);
        
        $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
        $arrObjRelProtocoloProtocoloDTO = $objRelProtocoloProtocoloRN->listarRN0187($objRelProtocoloProtocoloDTO);          
        
        $arrIdProcessos = array_unique(array_merge($arrIdProcessos, InfraArray::converterArrInfraDTO($arrObjRelProtocoloProtocoloDTO,'IdProtocolo1')));
      }    

      $objAtividadeRN = new AtividadeRN();

      //lançar andamento somente para a unidade atual
      if ($numIdTarefa == TarefaRN::$TI_BLOCO_RETORNO || $numIdTarefa == TarefaRN::$TI_BLOCO_CONCLUSAO || $numIdTarefa == TarefaRN::$TI_BLOCO_REABERTURA){

        $objBlocoDTO = new BlocoDTO();
        $objBlocoDTO->retNumIdUnidade();
        $objBlocoDTO->retStrSiglaUnidade();
        $objBlocoDTO->retStrDescricaoUnidade();
        $objBlocoDTO->setNumIdBloco($numIdBloco);
        
        $objBlocoDTO = $this->consultarRN1276($objBlocoDTO);
        
        foreach($arrIdProcessos as $dblIdProcesso){

          $arrObjAtributoAndamentoDTO = array();
          $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
          $objAtributoAndamentoDTO->setStrNome('BLOCO');
          $objAtributoAndamentoDTO->setStrValor($numIdBloco);
          $objAtributoAndamentoDTO->setStrIdOrigem($numIdBloco);
          $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;
  
          $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
          $objAtributoAndamentoDTO->setStrNome('UNIDADE');
          $objAtributoAndamentoDTO->setStrValor($objBlocoDTO->getStrSiglaUnidade().'¥'.$objBlocoDTO->getStrDescricaoUnidade());
          $objAtributoAndamentoDTO->setStrIdOrigem($objBlocoDTO->getNumIdUnidade());
          $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;
          
          $objAtividadeDTO = new AtividadeDTO();
          $objAtividadeDTO->setDblIdProtocolo($dblIdProcesso);
          $objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
          $objAtividadeDTO->setNumIdTarefa($numIdTarefa);
          $objAtividadeDTO->setArrObjAtributoAndamentoDTO($arrObjAtributoAndamentoDTO);
          
          
          $objAtividadeDTO = $objAtividadeRN->gerarInternaRN0727($objAtividadeDTO);
        }
                 
      }else{

        //obtem unidades de disponibilizacao do bloco
        $objRelBlocoUnidadeDTO = new RelBlocoUnidadeDTO();
        $objRelBlocoUnidadeDTO->retNumIdUnidade();
        $objRelBlocoUnidadeDTO->retNumIdBloco();
        $objRelBlocoUnidadeDTO->setNumIdBloco($numIdBloco);
        
        if ($numIdTarefa == TarefaRN::$TI_BLOCO_CANCELAMENTO_DISPONIBILIZACAO){
          $objRelBlocoUnidadeDTO->setStrSinRetornado('N');
        }
        
        $arrObjRelBlocoUnidadeDTO = $objRelBlocoUnidadeRN->listarRN1304($objRelBlocoUnidadeDTO);
        
        //lança um andamento em cada processo para cada unidade
        foreach($arrIdProcessos as $dblIdProcesso){
          foreach($arrObjRelBlocoUnidadeDTO as $objRelBlocoUnidadeDTO){
        
            $objUnidadeDTO = new UnidadeDTO();
            $objUnidadeDTO->setBolExclusaoLogica(false);
            $objUnidadeDTO->retNumIdUnidade();
            $objUnidadeDTO->retStrSigla();
            $objUnidadeDTO->retStrDescricao();
            $objUnidadeDTO->setNumIdUnidade($objRelBlocoUnidadeDTO->getNumIdUnidade());
            
            $objUnidadeRN = new UnidadeRN();
            $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);
          
            $arrObjAtributoAndamentoDTO = array();
            $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
            $objAtributoAndamentoDTO->setStrNome('BLOCO');
            $objAtributoAndamentoDTO->setStrValor($objRelBlocoUnidadeDTO->getNumIdBloco());
            $objAtributoAndamentoDTO->setStrIdOrigem($objRelBlocoUnidadeDTO->getNumIdBloco());
            $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;
    
            $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
            $objAtributoAndamentoDTO->setStrNome('UNIDADE');
            $objAtributoAndamentoDTO->setStrValor($objUnidadeDTO->getStrSigla().'¥'.$objUnidadeDTO->getStrDescricao());
            $objAtributoAndamentoDTO->setStrIdOrigem($objUnidadeDTO->getNumIdUnidade());
            $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;
            
            $objAtividadeDTO = new AtividadeDTO();
            $objAtividadeDTO->setDblIdProtocolo($dblIdProcesso);
            $objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $objAtividadeDTO->setNumIdTarefa($numIdTarefa);
            $objAtividadeDTO->setArrObjAtributoAndamentoDTO($arrObjAtributoAndamentoDTO);
            
            $objAtividadeDTO = $objAtividadeRN->gerarInternaRN0727($objAtividadeDTO);
          }        
        }
     }
            
    }catch(Exception $e){
      throw new InfraException('Erro lançando andamento para processos do bloco.',$e);
    }
  }
  
}
?>