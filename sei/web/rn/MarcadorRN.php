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

class MarcadorRN extends InfraRN {

  public static $TI_PRETO = '0';
  public static $TI_BRANCO = '1';
  public static $TI_CINZA = '2';
  public static $TI_VERMELHO = '3';
  public static $TI_AMARELO = '4';
  public static $TI_VERDE = '5';
  public static $TI_AZUL = '6';
  public static $TI_ROSA = '7';
  public static $TI_ROXO = '8';
  public static $TI_CIANO = '9';

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }

  public function listarValoresIcone(){
    try {

      $arr = array();

      $objIconeMarcadorDTO = new IconeMarcadorDTO();
      $objIconeMarcadorDTO->setStrStaIcone(self::$TI_PRETO);
      $objIconeMarcadorDTO->setStrDescricao('Preto');
      $objIconeMarcadorDTO->setStrArquivo('marcador_preto.png');
      $arr[] = $objIconeMarcadorDTO;

      $objIconeMarcadorDTO = new IconeMarcadorDTO();
      $objIconeMarcadorDTO->setStrStaIcone(self::$TI_BRANCO);
      $objIconeMarcadorDTO->setStrDescricao('Branco');
      $objIconeMarcadorDTO->setStrArquivo('marcador_branco.png');
      $arr[] = $objIconeMarcadorDTO;

      $objIconeMarcadorDTO = new IconeMarcadorDTO();
      $objIconeMarcadorDTO->setStrStaIcone(self::$TI_CINZA);
      $objIconeMarcadorDTO->setStrDescricao('Cinza');
      $objIconeMarcadorDTO->setStrArquivo('marcador_cinza.png');
      $arr[] = $objIconeMarcadorDTO;

      $objIconeMarcadorDTO = new IconeMarcadorDTO();
      $objIconeMarcadorDTO->setStrStaIcone(self::$TI_VERMELHO);
      $objIconeMarcadorDTO->setStrDescricao('Vermelho');
      $objIconeMarcadorDTO->setStrArquivo('marcador_vermelho.png');
      $arr[] = $objIconeMarcadorDTO;

      $objIconeMarcadorDTO = new IconeMarcadorDTO();
      $objIconeMarcadorDTO->setStrStaIcone(self::$TI_AMARELO);
      $objIconeMarcadorDTO->setStrDescricao('Amarelo');
      $objIconeMarcadorDTO->setStrArquivo('marcador_amarelo.png');
      $arr[] = $objIconeMarcadorDTO;

      $objIconeMarcadorDTO = new IconeMarcadorDTO();
      $objIconeMarcadorDTO->setStrStaIcone(self::$TI_VERDE);
      $objIconeMarcadorDTO->setStrDescricao('Verde');
      $objIconeMarcadorDTO->setStrArquivo('marcador_verde.png');
      $arr[] = $objIconeMarcadorDTO;

      $objIconeMarcadorDTO = new IconeMarcadorDTO();
      $objIconeMarcadorDTO->setStrStaIcone(self::$TI_AZUL);
      $objIconeMarcadorDTO->setStrDescricao('Azul');
      $objIconeMarcadorDTO->setStrArquivo('marcador_azul.png');
      $arr[] = $objIconeMarcadorDTO;

      $objIconeMarcadorDTO = new IconeMarcadorDTO();
      $objIconeMarcadorDTO->setStrStaIcone(self::$TI_ROSA);
      $objIconeMarcadorDTO->setStrDescricao('Rosa');
      $objIconeMarcadorDTO->setStrArquivo('marcador_rosa.png');
      $arr[] = $objIconeMarcadorDTO;

      $objIconeMarcadorDTO = new IconeMarcadorDTO();
      $objIconeMarcadorDTO->setStrStaIcone(self::$TI_ROXO);
      $objIconeMarcadorDTO->setStrDescricao('Roxo');
      $objIconeMarcadorDTO->setStrArquivo('marcador_roxo.png');
      $arr[] = $objIconeMarcadorDTO;

      $objIconeMarcadorDTO = new IconeMarcadorDTO();
      $objIconeMarcadorDTO->setStrStaIcone(self::$TI_CIANO);
      $objIconeMarcadorDTO->setStrDescricao('Ciano');
      $objIconeMarcadorDTO->setStrArquivo('marcador_ciano.png');
      $arr[] = $objIconeMarcadorDTO;

      return $arr;

    }catch(Exception $e){
      throw new InfraException('Erro listando valores de Icone.',$e);
    }
  }

  private function validarNumIdUnidade(MarcadorDTO $objMarcadorDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objMarcadorDTO->getNumIdUnidade())){
      $objInfraException->adicionarValidacao('Unidade não informada.');
    }
  }

  private function validarStrNome(MarcadorDTO $objMarcadorDTO, InfraException $objInfraException){

    if (InfraString::isBolVazia($objMarcadorDTO->getStrNome())){
      $objInfraException->adicionarValidacao('Nome não informado.');
    }else{
      $objMarcadorDTO->setStrNome(trim($objMarcadorDTO->getStrNome()));

      if (strlen($objMarcadorDTO->getStrNome())>50){
        $objInfraException->adicionarValidacao('Nome possui tamanho superior a 50 caracteres.');
      }

      if ($objMarcadorDTO->getStrNome()=='[REMOVIDO]'){
        $objInfraException->adicionarValidacao('Nome informado reservado do sistema.');
        return;
      }

      $dto = new MarcadorDTO();
      $dto->setBolExclusaoLogica(false);
      $dto->retStrSinAtivo();

      $dto->setNumIdMarcador($objMarcadorDTO->getNumIdMarcador(),InfraDTO::$OPER_DIFERENTE);
      $dto->setNumIdUnidade($objMarcadorDTO->getNumIdUnidade());
      $dto->setStrNome($objMarcadorDTO->getStrNome());

      $dto = $this->consultar($dto);

      if ($dto!=null) {
        if ($dto->getStrSinAtivo()=='S') {
          $objInfraException->adicionarValidacao('Existe outro Marcador com este Nome.');
        } else {
          $objInfraException->adicionarValidacao('Existe ocorrência inativa de Marcador com este Nome.');
        }
      }
    }    
  }

  private function validarStrDescricao(MarcadorDTO $objMarcadorDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objMarcadorDTO->getStrDescricao())){
      $objMarcadorDTO->setStrDescricao(null);
    }else{
      $objMarcadorDTO->setStrDescricao(trim($objMarcadorDTO->getStrDescricao()));

      if (strlen($objMarcadorDTO->getStrDescricao())>250){
        $objInfraException->adicionarValidacao('Descrição possui tamanho superior a 250 caracteres.');
      }
    }
  }

  private function validarStrStaIcone(MarcadorDTO $objMarcadorDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objMarcadorDTO->getStrStaIcone())){
      $objInfraException->adicionarValidacao('Ícone não informado.');
    }else{
      if (!in_array($objMarcadorDTO->getStrStaIcone(),InfraArray::converterArrInfraDTO($this->listarValoresIcone(),'StaIcone'))){
        $objInfraException->adicionarValidacao('Ícone inválido.');
      }
    }
  }

  private function validarStrSinAtivo(MarcadorDTO $objMarcadorDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objMarcadorDTO->getStrSinAtivo())){
      $objInfraException->adicionarValidacao('Sinalizador de Exclusão Lógica não informado.');
    }else{
      if (!InfraUtil::isBolSinalizadorValido($objMarcadorDTO->getStrSinAtivo())){
        $objInfraException->adicionarValidacao('Sinalizador de Exclusão Lógica inválido.');
      }
    }
  }

  protected function cadastrarControlado(MarcadorDTO $objMarcadorDTO) {
    try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('marcador_cadastrar',__METHOD__,$objMarcadorDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarNumIdUnidade($objMarcadorDTO, $objInfraException);
      $this->validarStrNome($objMarcadorDTO, $objInfraException);
      $this->validarStrDescricao($objMarcadorDTO, $objInfraException);
      $this->validarStrStaIcone($objMarcadorDTO, $objInfraException);
      $this->validarStrSinAtivo($objMarcadorDTO, $objInfraException);

      $objInfraException->lancarValidacoes();

      $objMarcadorBD = new MarcadorBD($this->getObjInfraIBanco());
      $ret = $objMarcadorBD->cadastrar($objMarcadorDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando Marcador.',$e);
    }
  }

  protected function alterarControlado(MarcadorDTO $objMarcadorDTO){
    try {

      //Valida Permissao
  	   SessaoSEI::getInstance()->validarAuditarPermissao('marcador_alterar',__METHOD__,$objMarcadorDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      if ($objMarcadorDTO->isSetNumIdUnidade()){
        $this->validarNumIdUnidade($objMarcadorDTO, $objInfraException);
      }
      if ($objMarcadorDTO->isSetStrNome()){
        $this->validarStrNome($objMarcadorDTO, $objInfraException);
      }
      if ($objMarcadorDTO->isSetStrDescricao()){
        $this->validarStrDescricao($objMarcadorDTO, $objInfraException);
      }
      if ($objMarcadorDTO->isSetStrStaIcone()){
        $this->validarStrStaIcone($objMarcadorDTO, $objInfraException);
      }
      if ($objMarcadorDTO->isSetStrSinAtivo()){
        $this->validarStrSinAtivo($objMarcadorDTO, $objInfraException);
      }

      $objInfraException->lancarValidacoes();

      $objMarcadorBD = new MarcadorBD($this->getObjInfraIBanco());
      $objMarcadorBD->alterar($objMarcadorDTO);

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando Marcador.',$e);
    }
  }

  protected function excluirControlado($arrObjMarcadorDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('marcador_excluir',__METHOD__,$arrObjMarcadorDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $objAndamentoMarcadorRN = new AndamentoMarcadorRN();

      foreach($arrObjMarcadorDTO as $objMarcadorDTO){
        $objAndamentoMarcadorDTO = new AndamentoMarcadorDTO();
        $objAndamentoMarcadorDTO->retStrNomeMarcador();
        $objAndamentoMarcadorDTO->setNumIdMarcador($objMarcadorDTO->getNumIdMarcador());
        $objAndamentoMarcadorDTO->setNumMaxRegistrosRetorno(1);
        $objAndamentoMarcadorDTO = $objAndamentoMarcadorRN->consultar($objAndamentoMarcadorDTO);

        if ($objAndamentoMarcadorDTO!=null){
          $objInfraException->adicionarValidacao('Marcador "'.$objAndamentoMarcadorDTO->getStrNomeMarcador().'" já foi utilizado.');
        }
      }

      $objInfraException->lancarValidacoes();


      $objMarcadorBD = new MarcadorBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjMarcadorDTO);$i++){
        $objMarcadorBD->excluir($arrObjMarcadorDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo Marcador.',$e);
    }
  }

  protected function consultarConectado(MarcadorDTO $objMarcadorDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('marcador_consultar',__METHOD__,$objMarcadorDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objMarcadorBD = new MarcadorBD($this->getObjInfraIBanco());
      $ret = $objMarcadorBD->consultar($objMarcadorDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando Marcador.',$e);
    }
  }

  protected function listarConectado(MarcadorDTO $objMarcadorDTO) {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('marcador_listar',__METHOD__,$objMarcadorDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objMarcadorBD = new MarcadorBD($this->getObjInfraIBanco());
      $ret = $objMarcadorBD->listar($objMarcadorDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Marcadores.',$e);
    }
  }

  protected function contarConectado(MarcadorDTO $objMarcadorDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('marcador_listar',__METHOD__,$objMarcadorDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objMarcadorBD = new MarcadorBD($this->getObjInfraIBanco());
      $ret = $objMarcadorBD->contar($objMarcadorDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro contando Marcadores.',$e);
    }
  }

  protected function desativarControlado($arrObjMarcadorDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('marcador_desativar',__METHOD__,$arrObjMarcadorDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objMarcadorBD = new MarcadorBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjMarcadorDTO);$i++){
        $objMarcadorBD->desativar($arrObjMarcadorDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando Marcador.',$e);
    }
  }

  protected function reativarControlado($arrObjMarcadorDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('marcador_reativar',__METHOD__,$arrObjMarcadorDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objMarcadorBD = new MarcadorBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjMarcadorDTO);$i++){
        $objMarcadorBD->reativar($arrObjMarcadorDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando Marcador.',$e);
    }
  }

  protected function bloquearControlado(MarcadorDTO $objMarcadorDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('marcador_consultar',__METHOD__,$objMarcadorDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objMarcadorBD = new MarcadorBD($this->getObjInfraIBanco());
      $ret = $objMarcadorBD->bloquear($objMarcadorDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro bloqueando Marcador.',$e);
    }
  }


}
?>