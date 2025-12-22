<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 11/11/2015 - criado por mga
*
* Versão do Gerador de Código: 1.36.0
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class AndamentoMarcadorRN extends InfraRN {

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }

  private function validarNumIdMarcador(AndamentoMarcadorDTO $objAndamentoMarcadorDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAndamentoMarcadorDTO->getNumIdMarcador())){
      $objInfraException->adicionarValidacao('Marcador não informado.');
    }
  }

  private function validarDblIdProcedimento(AndamentoMarcadorDTO $objAndamentoMarcadorDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAndamentoMarcadorDTO->getDblIdProcedimento())){
      $objInfraException->adicionarValidacao('Processo não informado.');
    }
  }

  private function validarNumIdUnidade(AndamentoMarcadorDTO $objAndamentoMarcadorDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAndamentoMarcadorDTO->getNumIdUnidade())){
      $objInfraException->adicionarValidacao('Unidade não informada.');
    }
  }

  private function validarNumIdUsuario(AndamentoMarcadorDTO $objAndamentoMarcadorDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAndamentoMarcadorDTO->getNumIdUsuario())){
      $objInfraException->adicionarValidacao('Usuário não informado.');
    }
  }

  private function validarDthExecucao(AndamentoMarcadorDTO $objAndamentoMarcadorDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAndamentoMarcadorDTO->getDthExecucao())){
      $objInfraException->adicionarValidacao('Data/Hora de Execução não informada.');
    }else{
      if (!InfraData::validarDataHora($objAndamentoMarcadorDTO->getDthExecucao())){
        $objInfraException->adicionarValidacao('Data/Hora de Execução inválida.');
      }
    }
  }

  private function validarStrTexto(AndamentoMarcadorDTO $objAndamentoMarcadorDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAndamentoMarcadorDTO->getStrTexto())){
      $objAndamentoMarcadorDTO->setStrTexto(null);
    }else{
      $objAndamentoMarcadorDTO->setStrTexto(trim($objAndamentoMarcadorDTO->getStrTexto()));

      if (strlen($objAndamentoMarcadorDTO->getStrTexto())>250){
        $objInfraException->adicionarValidacao('Texto possui tamanho superior a 250 caracteres.');
      }
    }
  }

  private function validarStrSinUltimo(AndamentoMarcadorDTO $objAndamentoMarcadorDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objAndamentoMarcadorDTO->getStrSinUltimo())){
      $objInfraException->adicionarValidacao('Sinalizador de Sinalizador de Último Registro não informado.');
    }else{
      if (!InfraUtil::isBolSinalizadorValido($objAndamentoMarcadorDTO->getStrSinUltimo())){
        $objInfraException->adicionarValidacao('Sinalizador de Sinalizador de Último Registro inválido.');
      }
    }
  }

  protected function gerenciarControlado(AndamentoMarcadorDTO $parObjAndamentoMarcadorDTO) {
    try{

      $ret = array();

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('andamento_marcador_gerenciar',__METHOD__,$parObjAndamentoMarcadorDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      if (InfraString::isBolVazia($parObjAndamentoMarcadorDTO->getNumIdMarcador())) {
        $parObjAndamentoMarcadorDTO->setNumIdMarcador(null);
      }

      if (is_array($parObjAndamentoMarcadorDTO->getDblIdProcedimento())){
        $arrIdProcedimento = $parObjAndamentoMarcadorDTO->getDblIdProcedimento();
      }else if (!InfraString::isBolVazia($parObjAndamentoMarcadorDTO->getDblIdProcedimento())){
        $arrIdProcedimento = array($parObjAndamentoMarcadorDTO->getDblIdProcedimento());
      }else{
        $arrIdProcedimento = array();
      }

      if (count($arrIdProcedimento)==0){
        $objInfraException->adicionarValidacao('Nenhum processo informado.');
      }

      if ($parObjAndamentoMarcadorDTO->getNumIdMarcador()!=null) {
        $objMarcadorDTO = new MarcadorDTO();
        $objMarcadorDTO->setBolExclusaoLogica(false);
        $objMarcadorDTO->retNumIdUnidade();
        $objMarcadorDTO->setNumIdMarcador($parObjAndamentoMarcadorDTO->getNumIdMarcador());

        $objMarcadorRN = new MarcadorRN();
        $objMarcadorDTO = $objMarcadorRN->consultar($objMarcadorDTO);

        if ($objMarcadorDTO == null) {
          $objInfraException->lancarValidacao('Marcador não encontrado.');
        }

        if ($objMarcadorDTO->getNumIdUnidade() != SessaoSEI::getInstance()->getNumIdUnidadeAtual()) {
          $objInfraException->lancarValidacao('Marcador não pertence à unidade '.SessaoSEI::getInstance()->getStrSiglaUnidadeAtual().'.');
        }
      }

      $this->validarStrTexto($parObjAndamentoMarcadorDTO, $objInfraException);

      $objInfraException->lancarValidacoes();

      $objAndamentoMarcadorBD = new AndamentoMarcadorBD($this->getObjInfraIBanco());

      $objAndamentoMarcadorDTO = new AndamentoMarcadorDTO();
      $objAndamentoMarcadorDTO->retNumIdAndamentoMarcador();
      $objAndamentoMarcadorDTO->retNumIdMarcador();
      $objAndamentoMarcadorDTO->retDblIdProcedimento();
      $objAndamentoMarcadorDTO->retStrTexto();
      $objAndamentoMarcadorDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      $objAndamentoMarcadorDTO->setDblIdProcedimento($arrIdProcedimento,InfraDTO::$OPER_IN);
      $objAndamentoMarcadorDTO->setStrSinUltimo('S');

      $arrObjAndamentoMarcadorDTO = $this->listar($objAndamentoMarcadorDTO);

      $arrIdProcedimentoNaoModificado = array();
      foreach($arrObjAndamentoMarcadorDTO as $objAndamentoMarcadorDTO){

        if ($objAndamentoMarcadorDTO->getNumIdMarcador()!=$parObjAndamentoMarcadorDTO->getNumIdMarcador() ||
            $objAndamentoMarcadorDTO->getStrTexto()!=$parObjAndamentoMarcadorDTO->getStrTexto()) {

          $dto = new AndamentoMarcadorDTO();
          $dto->setStrSinUltimo('N');
          $dto->setNumIdAndamentoMarcador($objAndamentoMarcadorDTO->getNumIdAndamentoMarcador());
          $objAndamentoMarcadorBD->alterar($dto);

        }else{

          $arrIdProcedimentoNaoModificado[$objAndamentoMarcadorDTO->getDblIdProcedimento()] = 0;
        }
      }

      if (count($arrIdProcedimento)!=count($arrIdProcedimentoNaoModificado)) {
        $objAndamentoMarcadorDTO = new AndamentoMarcadorDTO();
        $objAndamentoMarcadorDTO->setNumIdAndamentoMarcador(null);
        $objAndamentoMarcadorDTO->setNumIdMarcador($parObjAndamentoMarcadorDTO->getNumIdMarcador());
        $objAndamentoMarcadorDTO->setDblIdProcedimento(null);
        $objAndamentoMarcadorDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objAndamentoMarcadorDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
        $objAndamentoMarcadorDTO->setDthExecucao(InfraData::getStrDataHoraAtual());
        $objAndamentoMarcadorDTO->setStrTexto($parObjAndamentoMarcadorDTO->getStrTexto());

        if ($parObjAndamentoMarcadorDTO->getNumIdMarcador()!=null) {
          $objAndamentoMarcadorDTO->setStrSinUltimo('S');
        }else{
          $objAndamentoMarcadorDTO->setStrSinUltimo('N');
        }

        $arrObjAndamentoMarcadorDTO = array();
        foreach ($arrIdProcedimento as $dblIdProcedimento) {

          if (!isset($arrIdProcedimentoNaoModificado[$dblIdProcedimento])) {
            $dto = clone($objAndamentoMarcadorDTO);
            $dto->setDblIdProcedimento($dblIdProcedimento);
            $arrObjAndamentoMarcadorDTO[] = $dto;
          }
        }

        $ret = $objAndamentoMarcadorBD->cadastrar($arrObjAndamentoMarcadorDTO);
      }

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro gerenciando Marcador.',$e);
    }
  }

  /*
  protected function cadastrarControlado(AndamentoMarcadorDTO $objAndamentoMarcadorDTO) {
    try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('andamento_marcador_cadastrar',__METHOD__,$objAndamentoMarcadorDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarNumIdMarcador($objAndamentoMarcadorDTO, $objInfraException);
      $this->validarDblIdProcedimento($objAndamentoMarcadorDTO, $objInfraException);
      $this->validarNumIdUsuario($objAndamentoMarcadorDTO, $objInfraException);
      $this->validarDthExecucao($objAndamentoMarcadorDTO, $objInfraException);
      $this->validarStrTexto($objAndamentoMarcadorDTO, $objInfraException);
      $this->validarStrSinUltimo($objAndamentoMarcadorDTO, $objInfraException);

      $objInfraException->lancarValidacoes();

      $objAndamentoMarcadorBD = new AndamentoMarcadorBD($this->getObjInfraIBanco());
      $ret = $objAndamentoMarcadorBD->cadastrar($objAndamentoMarcadorDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando Gerenciar Marcador.',$e);
    }
  }

  protected function alterarControlado(AndamentoMarcadorDTO $objAndamentoMarcadorDTO){
    try {

      //Valida Permissao
  	   SessaoSEI::getInstance()->validarAuditarPermissao('andamento_marcador_alterar',__METHOD__,$objAndamentoMarcadorDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      if ($objAndamentoMarcadorDTO->isSetNumIdMarcador()){
        $this->validarNumIdMarcador($objAndamentoMarcadorDTO, $objInfraException);
      }
      if ($objAndamentoMarcadorDTO->isSetDblIdProcedimento()){
        $this->validarDblIdProcedimento($objAndamentoMarcadorDTO, $objInfraException);
      }
      if ($objAndamentoMarcadorDTO->isSetNumIdUsuario()){
        $this->validarNumIdUsuario($objAndamentoMarcadorDTO, $objInfraException);
      }
      if ($objAndamentoMarcadorDTO->isSetDthExecucao()){
        $this->validarDthExecucao($objAndamentoMarcadorDTO, $objInfraException);
      }
      if ($objAndamentoMarcadorDTO->isSetStrTexto()){
        $this->validarStrTexto($objAndamentoMarcadorDTO, $objInfraException);
      }
      if ($objAndamentoMarcadorDTO->isSetStrSinUltimo()){
        $this->validarStrSinUltimo($objAndamentoMarcadorDTO, $objInfraException);
      }

      $objInfraException->lancarValidacoes();

      $objAndamentoMarcadorBD = new AndamentoMarcadorBD($this->getObjInfraIBanco());
      $objAndamentoMarcadorBD->alterar($objAndamentoMarcadorDTO);

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando Gerenciar Marcador.',$e);
    }
  }
  */

  protected function excluirControlado($arrObjAndamentoMarcadorDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('andamento_marcador_excluir',__METHOD__,$arrObjAndamentoMarcadorDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAndamentoMarcadorBD = new AndamentoMarcadorBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjAndamentoMarcadorDTO);$i++){
        $objAndamentoMarcadorBD->excluir($arrObjAndamentoMarcadorDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo Gerenciar Marcador.',$e);
    }
  }

  protected function consultarConectado(AndamentoMarcadorDTO $objAndamentoMarcadorDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('andamento_marcador_consultar',__METHOD__,$objAndamentoMarcadorDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAndamentoMarcadorBD = new AndamentoMarcadorBD($this->getObjInfraIBanco());
      $ret = $objAndamentoMarcadorBD->consultar($objAndamentoMarcadorDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando Gerenciar Marcador.',$e);
    }
  }

  protected function listarConectado(AndamentoMarcadorDTO $objAndamentoMarcadorDTO) {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('andamento_marcador_listar',__METHOD__,$objAndamentoMarcadorDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAndamentoMarcadorBD = new AndamentoMarcadorBD($this->getObjInfraIBanco());
      $ret = $objAndamentoMarcadorBD->listar($objAndamentoMarcadorDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Gerenciar Marcadores.',$e);
    }
  }

  protected function contarConectado(AndamentoMarcadorDTO $objAndamentoMarcadorDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('andamento_marcador_listar',__METHOD__,$objAndamentoMarcadorDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAndamentoMarcadorBD = new AndamentoMarcadorBD($this->getObjInfraIBanco());
      $ret = $objAndamentoMarcadorBD->contar($objAndamentoMarcadorDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro contando Gerenciar Marcadores.',$e);
    }
  }
/* 
  protected function desativarControlado($arrObjAndamentoMarcadorDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('andamento_marcador_desativar',__METHOD__,$arrObjAndamentoMarcadorDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAndamentoMarcadorBD = new AndamentoMarcadorBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjAndamentoMarcadorDTO);$i++){
        $objAndamentoMarcadorBD->desativar($arrObjAndamentoMarcadorDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando Gerenciar Marcador.',$e);
    }
  }

  protected function reativarControlado($arrObjAndamentoMarcadorDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('andamento_marcador_reativar',__METHOD__,$arrObjAndamentoMarcadorDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAndamentoMarcadorBD = new AndamentoMarcadorBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjAndamentoMarcadorDTO);$i++){
        $objAndamentoMarcadorBD->reativar($arrObjAndamentoMarcadorDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando Gerenciar Marcador.',$e);
    }
  }

  protected function bloquearControlado(AndamentoMarcadorDTO $objAndamentoMarcadorDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('andamento_marcador_consultar',__METHOD__,$objAndamentoMarcadorDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAndamentoMarcadorBD = new AndamentoMarcadorBD($this->getObjInfraIBanco());
      $ret = $objAndamentoMarcadorBD->bloquear($objAndamentoMarcadorDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro bloqueando Gerenciar Marcador.',$e);
    }
  }

 */
}
?>