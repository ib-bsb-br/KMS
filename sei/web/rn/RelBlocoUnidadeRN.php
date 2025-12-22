<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 05/10/2009 - criado por fbv@trf4.gov.br
*
* Versão do Gerador de Código: 1.29.1
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class RelBlocoUnidadeRN extends InfraRN {

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }

  private function validarNumIdUnidadeRN1298(RelBlocoUnidadeDTO $objRelBlocoUnidadeDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objRelBlocoUnidadeDTO->getNumIdUnidade())){
      $objInfraException->adicionarValidacao('Unidade não informada.');
    }
  }

  private function validarNumIdBlocoRN1299(RelBlocoUnidadeDTO $objRelBlocoUnidadeDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objRelBlocoUnidadeDTO->getNumIdBloco())){
      $objInfraException->adicionarValidacao('Bloco não informado.');
    }
  }

  private function validarStrSinRetornado(RelBlocoUnidadeDTO $objRelBlocoUnidadeDTO, InfraException $objInfraException){
   if (InfraString::isBolVazia($objRelBlocoUnidadeDTO->getStrSinRetornado())){
      $objInfraException->adicionarValidacao('Sinalizador de bloco retornado não informado.');
    }else{
      if (!InfraUtil::isBolSinalizadorValido($objRelBlocoUnidadeDTO->getStrSinRetornado())){
        $objInfraException->adicionarValidacao('Sinalizador de bloco retornado inválido.');
      }
    }
  }
  
  
  protected function cadastrarRN1300Controlado(RelBlocoUnidadeDTO $objRelBlocoUnidadeDTO) {
    try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('rel_bloco_unidade_cadastrar',__METHOD__,$objRelBlocoUnidadeDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarNumIdUnidadeRN1298($objRelBlocoUnidadeDTO, $objInfraException);
      $this->validarNumIdBlocoRN1299($objRelBlocoUnidadeDTO, $objInfraException);
      $this->validarStrSinRetornado($objRelBlocoUnidadeDTO, $objInfraException);

      $objInfraException->lancarValidacoes();
	      
      $dto = new RelBlocoUnidadeDTO();
      $dto->retTodos(true);
      $dto->setNumIdUnidade($objRelBlocoUnidadeDTO->getNumIdUnidade());
      $dto->setNumIdBloco($objRelBlocoUnidadeDTO->getNumIdBloco());
      $dtoRN = new RelBlocoUnidadeRN();
      $dto = $dtoRN->consultarRN1303($dto);
      if(count($dto)>0){
      	$objInfraException->lancarValidacao('Bloco já consta na unidade "'.$dto->getStrSiglaUnidade().'".');
      }
      
      if($objRelBlocoUnidadeDTO->getNumIdUnidade()==SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
      	$objInfraException->lancarValidacao('Bloco não pode ser disponibilizado para a unidade geradora "'.SessaoSEI::getInstance()->getStrSiglaUnidadeAtual().'".');
      }
      
      $objRelBlocoUnidadeBD = new RelBlocoUnidadeBD($this->getObjInfraIBanco());
      $ret = $objRelBlocoUnidadeBD->cadastrar($objRelBlocoUnidadeDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando Bloco Unidade.',$e);
    }
  }
  
  protected function alterarRN1301Controlado(RelBlocoUnidadeDTO $objRelBlocoUnidadeDTO){
    try {

      //Valida Permissao
  	   SessaoSEI::getInstance()->validarAuditarPermissao('rel_bloco_unidade_alterar',__METHOD__,$objRelBlocoUnidadeDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      if ($objRelBlocoUnidadeDTO->isSetNumIdUnidade()){
        $this->validarNumIdUnidadeRN1298($objRelBlocoUnidadeDTO, $objInfraException);
      }
      
      if ($objRelBlocoUnidadeDTO->isSetNumIdBloco()){
        $this->validarNumIdBlocoRN1299($objRelBlocoUnidadeDTO, $objInfraException);
      }
      
      if ($objRelBlocoUnidadeDTO->isSetStrSinRetornado()){
        $this->validarStrSinRetornado($objRelBlocoUnidadeDTO, $objInfraException);
      }      

      $objInfraException->lancarValidacoes();

      $objRelBlocoUnidadeBD = new RelBlocoUnidadeBD($this->getObjInfraIBanco());
      $objRelBlocoUnidadeBD->alterar($objRelBlocoUnidadeDTO);

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando Bloco Unidade.',$e);
    }
  }

  protected function excluirRN1302Controlado($arrObjRelBlocoUnidadeDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('rel_bloco_unidade_excluir',__METHOD__,$arrObjRelBlocoUnidadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelBlocoUnidadeBD = new RelBlocoUnidadeBD($this->getObjInfraIBanco());
      foreach($arrObjRelBlocoUnidadeDTO as $objRelBlocoUnidadeDTO){
        $objRelBlocoUnidadeBD->excluir($objRelBlocoUnidadeDTO);
      }
      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo Bloco Unidade.',$e);
    }
  }

  protected function consultarRN1303Conectado(RelBlocoUnidadeDTO $objRelBlocoUnidadeDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('rel_bloco_unidade_consultar',__METHOD__,$objRelBlocoUnidadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelBlocoUnidadeBD = new RelBlocoUnidadeBD($this->getObjInfraIBanco());
      $ret = $objRelBlocoUnidadeBD->consultar($objRelBlocoUnidadeDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando Bloco Unidade.',$e);
    }
  }

  protected function listarRN1304Conectado(RelBlocoUnidadeDTO $objRelBlocoUnidadeDTO) {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('rel_bloco_unidade_listar',__METHOD__,$objRelBlocoUnidadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelBlocoUnidadeBD = new RelBlocoUnidadeBD($this->getObjInfraIBanco());
      $ret = $objRelBlocoUnidadeBD->listar($objRelBlocoUnidadeDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Blocos Unidade.',$e);
    }
  }

  protected function contarRN1305Conectado(RelBlocoUnidadeDTO $objRelBlocoUnidadeDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('rel_bloco_unidade_listar',__METHOD__,$objRelBlocoUnidadeDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelBlocoUnidadeBD = new RelBlocoUnidadeBD($this->getObjInfraIBanco());
      $ret = $objRelBlocoUnidadeBD->contar($objRelBlocoUnidadeDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro contando Blocos Unidade.',$e);
    }
  }
/* 
  protected function desativarRN1306Controlado($arrObjRelBlocoUnidadeDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('rel_bloco_unidade_desativar');

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelBlocoUnidadeBD = new RelBlocoUnidadeBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjRelBlocoUnidadeDTO);$i++){
        $objRelBlocoUnidadeBD->desativar($arrObjRelBlocoUnidadeDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando Bloco Unidade.',$e);
    }
  }

  protected function reativarRN1307Controlado($arrObjRelBlocoUnidadeDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('rel_bloco_unidade_reativar');

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelBlocoUnidadeBD = new RelBlocoUnidadeBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjRelBlocoUnidadeDTO);$i++){
        $objRelBlocoUnidadeBD->reativar($arrObjRelBlocoUnidadeDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando Bloco Unidade.',$e);
    }
  }

  protected function bloquearRN1308Controlado(RelBlocoUnidadeDTO $objRelBlocoUnidadeDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('rel_bloco_unidade_consultar');

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRelBlocoUnidadeBD = new RelBlocoUnidadeBD($this->getObjInfraIBanco());
      $ret = $objRelBlocoUnidadeBD->bloquear($objRelBlocoUnidadeDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro bloqueando Bloco Unidade.',$e);
    }
  }

 */
}
?>