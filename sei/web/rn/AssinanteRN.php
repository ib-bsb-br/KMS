<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 13/10/2009 - criado por mga
*
* Versão do Gerador de Código: 1.29.1
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class AssinanteRN extends InfraRN {

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }

  private function validarStrCargoFuncao(AssinanteDTO $objAssinanteDTO, InfraException $objInfraException){
  	if (InfraString::isBolVazia($objAssinanteDTO->getStrCargoFuncao())){
      $objInfraException->adicionarValidacao('Cargo/Função não informado.');
    }else{
    	$objAssinanteDTO->setStrCargoFuncao(trim($objAssinanteDTO->getStrCargoFuncao()));
      
      if (strlen($objAssinanteDTO->getStrCargoFuncao())>100){
        $objInfraException->adicionarValidacao('Cargo/Função possui tamanho superior a 100 caracteres.');
      }
      
      $dto = new AssinanteDTO();
      $dto->setNumIdAssinante($objAssinanteDTO->getNumIdAssinante(),InfraDTO::$OPER_DIFERENTE);
      $dto->setStrCargoFuncao($objAssinanteDTO->getStrCargoFuncao(),InfraDTO::$OPER_IGUAL);
          
      if ($this->contarRN1340($dto)>0){
        $objInfraException->adicionarValidacao('Já existe uma assinatura cadastrada com este cargo/função.');    	
      }
    }
  }

  protected function cadastrarRN1335Controlado(AssinanteDTO $objAssinanteDTO) {
    try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('assinante_cadastrar',__METHOD__,$objAssinanteDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarStrCargoFuncao($objAssinanteDTO, $objInfraException);
      
      $objInfraException->lancarValidacoes();

      $objAssinanteBD = new AssinanteBD($this->getObjInfraIBanco());
      $ret = $objAssinanteBD->cadastrar($objAssinanteDTO);

      $objRelAssinanteUnidadeRN = new RelAssinanteUnidadeRN();
      $arrObjRelAssinanteUnidadeDTO = $objAssinanteDTO->getArrObjRelAssinanteUnidadeDTO();
      foreach($arrObjRelAssinanteUnidadeDTO as $objRelAssinanteUnidadeDTO){
        $objRelAssinanteUnidadeDTO->setNumIdAssinante($ret->getNumIdAssinante());
        $objRelAssinanteUnidadeRN->cadastrarRN1376($objRelAssinanteUnidadeDTO);
      }
      
      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando Assinante da Unidade.',$e);
    }
  }

  protected function alterarRN1336Controlado(AssinanteDTO $objAssinanteDTO){
    try {

      //Valida Permissao
  	   SessaoSEI::getInstance()->validarAuditarPermissao('assinante_alterar',__METHOD__,$objAssinanteDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      if ($objAssinanteDTO->isSetStrCargoFuncao()){
        $this->validarStrCargoFuncao($objAssinanteDTO, $objInfraException);
      }

      $objInfraException->lancarValidacoes();

      $objAssinanteBD = new AssinanteBD($this->getObjInfraIBanco());
      $objAssinanteBD->alterar($objAssinanteDTO);

      
      $objRelAssinanteUnidadeRN = new RelAssinanteUnidadeRN();
      
      $objRelAssinanteUnidadeDTO = new RelAssinanteUnidadeDTO();
      $objRelAssinanteUnidadeDTO->retNumIdAssinante();
      $objRelAssinanteUnidadeDTO->retNumIdUnidade();
      $objRelAssinanteUnidadeDTO->setNumIdAssinante($objAssinanteDTO->getNumIdAssinante());
      $objRelAssinanteUnidadeRN->excluirRN1378($objRelAssinanteUnidadeRN->listarRN1380($objRelAssinanteUnidadeDTO));
      
      $arrObjRelAssinanteUnidadeDTO = $objAssinanteDTO->getArrObjRelAssinanteUnidadeDTO();
      foreach($arrObjRelAssinanteUnidadeDTO as $objRelAssinanteUnidadeDTO){
        $objRelAssinanteUnidadeDTO->setNumIdAssinante($objAssinanteDTO->getNumIdAssinante());
        $objRelAssinanteUnidadeRN->cadastrarRN1376($objRelAssinanteUnidadeDTO);
      }
      
      
      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando Assinante da Unidade.',$e);
    }
  }

  protected function excluirRN1337Controlado($arrObjAssinanteDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('assinante_excluir',__METHOD__,$arrObjAssinanteDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      
      $objRelAssinanteUnidadeDTO = new RelAssinanteUnidadeDTO();
      $objRelAssinanteUnidadeDTO->retNumIdAssinante();
      $objRelAssinanteUnidadeDTO->retNumIdUnidade();
      
      $objRelAssinanteUnidadeRN = new RelAssinanteUnidadeRN();
      
      $objAssinanteBD = new AssinanteBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjAssinanteDTO);$i++){
        
        $objRelAssinanteUnidadeDTO->setNumIdAssinante($arrObjAssinanteDTO[$i]->getNumIdAssinante());
        $objRelAssinanteUnidadeRN->excluirRN1378($objRelAssinanteUnidadeRN->listarRN1380($objRelAssinanteUnidadeDTO));
        
        $objAssinanteBD->excluir($arrObjAssinanteDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo Assinante da Unidade.',$e);
    }
  }

  protected function consultarRN1338Conectado(AssinanteDTO $objAssinanteDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('assinante_consultar',__METHOD__,$objAssinanteDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAssinanteBD = new AssinanteBD($this->getObjInfraIBanco());
      $ret = $objAssinanteBD->consultar($objAssinanteDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando Assinante da Unidade.',$e);
    }
  }

  protected function listarRN1339Conectado(AssinanteDTO $objAssinanteDTO) {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('assinante_listar',__METHOD__,$objAssinanteDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAssinanteBD = new AssinanteBD($this->getObjInfraIBanco());
      $ret = $objAssinanteBD->listar($objAssinanteDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Assinantes da Unidade.',$e);
    }
  }

  protected function contarRN1340Conectado(AssinanteDTO $objAssinanteDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('assinante_listar',__METHOD__,$objAssinanteDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAssinanteBD = new AssinanteBD($this->getObjInfraIBanco());
      $ret = $objAssinanteBD->contar($objAssinanteDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro contando Assinantes da Unidade.',$e);
    }
  }


  protected function pesquisarConectado(AssinanteDTO $objAssinanteDTO){
    try {

      //Valida Permissao
      /////////////////////////////////////////////////////////////////
      SessaoSEI::getInstance()->validarAuditarPermissao('assinante_listar',__METHOD__,$objAssinanteDTO);
      /////////////////////////////////////////////////////////////////

      if ($objAssinanteDTO->isSetStrCargoFuncao()){
        if (trim($objAssinanteDTO->getStrCargoFuncao())!=''){

          $strPalavrasPesquisa = InfraString::prepararIndexacao($objAssinanteDTO->getStrCargoFuncao());
          $arrPalavrasPesquisa = explode(' ',$strPalavrasPesquisa);

          for($i=0;$i<count($arrPalavrasPesquisa);$i++){
            $arrPalavrasPesquisa[$i] = '%'.$arrPalavrasPesquisa[$i].'%';
          }

          if (count($arrPalavrasPesquisa)==1){
            $objAssinanteDTO->setStrCargoFuncao($arrPalavrasPesquisa[0],InfraDTO::$OPER_LIKE);
          }else{
            $objAssinanteDTO->unSetStrCargoFuncao();
            $a = array_fill(0,count($arrPalavrasPesquisa),'CargoFuncao');
            $b = array_fill(0,count($arrPalavrasPesquisa),InfraDTO::$OPER_LIKE);
            $d = array_fill(0,count($arrPalavrasPesquisa)-1,InfraDTO::$OPER_LOGICO_AND);
            $objAssinanteDTO->adicionarCriterio($a,$b,$arrPalavrasPesquisa,$d);
          }
        }
      }

      if ($objAssinanteDTO->isSetNumIdUnidade() && !InfraString::isBolVazia($objAssinanteDTO->getNumIdUnidade())){

        $objRelAssinanteUnidadeDTO = new RelAssinanteUnidadeDTO();
        $objRelAssinanteUnidadeDTO->retNumIdAssinante();
        $objRelAssinanteUnidadeDTO->setNumIdUnidade($objAssinanteDTO->getNumIdUnidade());

        $objRelAssinanteUnidadeRN = new RelAssinanteUnidadeRN();
        $arrObjRelAssinanteUnidadeDTO = $objRelAssinanteUnidadeRN->listarRN1380($objRelAssinanteUnidadeDTO);

        if (count($arrObjRelAssinanteUnidadeDTO)){
          $objAssinanteDTO->setNumIdAssinante(InfraArray::converterArrInfraDTO($arrObjRelAssinanteUnidadeDTO,'IdAssinante'), InfraDTO::$OPER_IN);
        }else{
          $objAssinanteDTO->setNumIdAssinante(null);
        }
      }

      return $this->listarRN1339($objAssinanteDTO);

    }catch(Exception $e){
      throw new InfraException('Erro pesquisando Usuários.',$e);
    }
  }

/* 
  protected function desativarRN1341Controlado($arrObjAssinanteDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('assinante_desativar');

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAssinanteBD = new AssinanteBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjAssinanteDTO);$i++){
        $objAssinanteBD->desativar($arrObjAssinanteDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando Assinante da Unidade.',$e);
    }
  }

  protected function reativarRN1342Controlado($arrObjAssinanteDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('assinante_reativar');

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAssinanteBD = new AssinanteBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjAssinanteDTO);$i++){
        $objAssinanteBD->reativar($arrObjAssinanteDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando Assinante da Unidade.',$e);
    }
  }

  protected function bloquearRN1343Controlado(AssinanteDTO $objAssinanteDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('assinante_consultar');

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAssinanteBD = new AssinanteBD($this->getObjInfraIBanco());
      $ret = $objAssinanteBD->bloquear($objAssinanteDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro bloqueando Assinante da Unidade.',$e);
    }
  }

 */
}
?>