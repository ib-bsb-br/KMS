<?

abstract class InfraScriptVersao extends InfraRN {

  private $numSeg = 0;

  private $strNome = null;
  private $strParametroVersao = null;
  private $strVersaoInfra = null;
  private $strVersaoAtual = null;
  private $arrVersoes = null;
  private $bolSqlServer = null;
  private $bolMySql = null;
  private $bolOracle = null;
  private $bolErroVersaoInexistente = null;
  private $strClasseModulo = null;

  /**
   * @return null
   */
  public function getStrClasseModulo()
  {
    return $this->strClasseModulo;
  }

  /**
   * @param null $strClasseModulo
   */
  public function setStrClasseModulo($strClasseModulo)
  {
    $this->strClasseModulo = $strClasseModulo;
  }

  /**
   * @return mixed
   */
  public function getBolErroVersaoInexistente()
  {
    return $this->bolErroVersaoInexistente;
  }

  /**
   * @param mixed $bolErroVersaoInexistente
   */
  public function setBolErroVersaoInexistente($bolErroVersaoInexistente)
  {
    $this->bolErroVersaoInexistente = $bolErroVersaoInexistente;
  }

  /**
   * @return null
   */
  public function getStrVersaoAtual()
  {
    return $this->strVersaoAtual;
  }

  /**
   * @param null $strVersaoAtual
   */
  public function setStrVersaoAtual($strVersaoAtual)
  {
    $this->strVersaoAtual = $strVersaoAtual;
  }

  /**
   * @return mixed
   */
  public function getBolSqlServer()
  {
    return $this->bolSqlServer;
  }

  /**
   * @param mixed $bolSqlServer
   */
  public function setBolSqlServer($bolSqlServer)
  {
    $this->bolSqlServer = $bolSqlServer;
  }

  /**
   * @return mixed
   */
  public function getBolMySql()
  {
    return $this->bolMySql;
  }

  /**
   * @param mixed $bolMySql
   */
  public function setBolMySql($bolMySql)
  {
    $this->bolMySql = $bolMySql;
  }

  /**
   * @return mixed
   */
  public function getBolOracle()
  {
    return $this->bolOracle;
  }

  /**
   * @param mixed $bolOracle
   */
  public function setBolOracle($bolOracle)
  {
    $this->bolOracle = $bolOracle;
  }

  /**
   * @return null
   */
  public function getStrNome()
  {
    return $this->strNome;
  }

  /**
   * @param null $strNome
   */
  public function setStrNome($strNome)
  {
    $this->strNome = $strNome;
  }

  /**
   * @return null
   */
  public function getStrParametroVersao()
  {
    return $this->strParametroVersao;
  }

  /**
   * @param null $strParametroVersao
   */
  public function setStrParametroVersao($strParametroVersao)
  {
    $this->strParametroVersao = $strParametroVersao;
  }

  /**
   * @return null
   */
  public function getStrVersaoInfra()
  {
    return $this->strVersaoInfra;
  }

  /**
   * @param null $strVersaoInfra
   */
  public function setStrVersaoInfra($strVersaoInfra)
  {
    $this->strVersaoInfra = $strVersaoInfra;
  }

  /**
   * @return null
   */
  public function getArrVersoes()
  {
    return $this->arrVersoes;
  }

  /**
   * @param null $arrVersoes
   */
  public function setArrVersoes($arrVersoes)
  {
    $this->arrVersoes = $arrVersoes;
  }

  public function __construct(){
    parent::__construct();
  }

  public function inicializar($strTitulo){

    ini_set('max_execution_time','0');
    ini_set('memory_limit','-1');

    try {
      @ini_set('zlib.output_compression','0');
      @ini_set('implicit_flush', '1');
    }catch(Exception $e){}

    ob_implicit_flush();

    InfraDebug::getInstance()->setBolLigado(true);
    InfraDebug::getInstance()->setBolDebugInfra(true);
    InfraDebug::getInstance()->setBolEcho(true);
    InfraDebug::getInstance()->limpar();

    $this->numSeg = InfraUtil::verificarTempoProcessamento();

    $this->logar($strTitulo);
  }

  public function logar($strMsg){

    if ($this->getStrNome()!=null){
      $strMsg = $this->getStrNome().' - '.$strMsg;
    }

    InfraDebug::getInstance()->gravar($strMsg);

    flush();
  }

  public function processarErro($strMsg, $e = null){

    $this->logar('ERRO: '.$strMsg);

    InfraDebug::getInstance()->setBolLigado(false);
    InfraDebug::getInstance()->setBolDebugInfra(false);
    InfraDebug::getInstance()->setBolEcho(false);

    throw new InfraException($strMsg, $e);
  }

  public function finalizar($strMsg){

    $this->numSeg = InfraUtil::verificarTempoProcessamento($this->numSeg);
    $this->logar('TEMPO TOTAL DE EXECUCAO: ' . $this->numSeg . ' s');

    $this->logar($strMsg);

    InfraDebug::getInstance()->setBolLigado(false);
    InfraDebug::getInstance()->setBolDebugInfra(false);
    InfraDebug::getInstance()->setBolEcho(false);
  }

  protected function atualizarVersaoConectado(){

    try{

      if (InfraString::isBolVazia($this->getStrNome())){
        $this->processarErro('NOME NAO INFORMADO');
      }

      if (InfraString::isBolVazia($this->getStrVersaoAtual())){
        $this->processarErro('ULTIMA VERSAO NAO INFORMADA');
      }

      if (InfraString::isBolVazia($this->getStrParametroVersao())){
        $this->processarErro('PARAMETRO DE VERSAO NAO INFORMADO');
      }

      if (!is_array($this->getArrVersoes()) || count($this->getArrVersoes())==0){
        $this->processarErro('CONJUNTO DE VERSOES NAO INFORMADO');
      }

      foreach($this->getArrVersoes() as $strVersao => $strFuncao){
        if (!method_exists($this,$strFuncao)){
          $this->processarErro('FUNCAO DE ATUALIZACAO '.$strFuncao.' DA VERSAO '.$strVersao.' NAO ENCONTRADA');
        }
      }

      $bolVersaoAtualEncontrada = false;
      foreach($this->getArrVersoes() as $strVersao => $strFuncao){
        if ($this->getStrVersaoAtual()==$strVersao){
          $bolVersaoAtualEncontrada = true;
          break;
        }
      }

      if (!$bolVersaoAtualEncontrada){
        $this->processarErro('ULTIMA VERSAO '.$this->getStrVersaoAtual().' NAO ENCONTRADA NO CONJUNTO DE VERSOES PARA ATUALIZACAO');
      }

      if (InfraString::isBolVazia($this->getStrVersaoInfra())){
        $this->processarErro('VERSAO DO FRAMEWORK PHP NAO INFORMADO');
      }

      if (!is_bool($this->getBolMySql())){
        $this->processarErro('SINALIZADOR DE BASE MYSQL NAO INFORMADO');
      }

      if (!is_bool($this->getBolOracle())){
        $this->processarErro('SINALIZADOR DE BASE ORACLE NAO INFORMADO');
      }

      if (!is_bool($this->getBolSqlServer())){
        $this->processarErro('SINALIZADOR DE BASE SQL SERVER NAO INFORMADO');
      }

      if (!is_bool($this->getBolErroVersaoInexistente())){
        $this->processarErro('SINALIZADOR DE ERRO PARA VERSAO INEXISTENTE NAO ENCONTRADO');
      }

      if (!InfraString::isBolVazia($this->getStrClasseModulo())) {

        if (!class_exists($this->getStrClasseModulo())) {
          $this->processarErro('CLASSE DE MODULO '.$this->getStrClasseModulo().' NAO ENCONTRADA');
        }

        $reflectionClass = new ReflectionClass($this->getStrClasseModulo());
        $objClasseModulo = $reflectionClass->newInstance();

        if ($objClasseModulo->getVersao() != $this->getStrVersaoAtual()) {
          $this->processarErro('VERSAO DA CLASSE DE MODULO '.$this->getStrClasseModulo().' '.$objClasseModulo->getVersao().' NAO CORRESPONDE A VERSAO DO SCRIPT DE ATUALIZACAO '.$this->getStrVersaoAtual());
        }

      }

      if (InfraUtil::compararVersoes(VERSAO_INFRA, '<', $this->getStrVersaoInfra())){
        $this->processarErro('VERSAO DO FRAMEWORK PHP INCOMPATIVEL (VERSAO ATUAL '.VERSAO_INFRA.', REQUERIDA VERSAO IGUAL OU SUPERIOR A '.$this->getStrVersaoInfra().')');
      }

      $objInfraIBanco = $this->inicializarObjInfraIBanco();

      if ( !(($this->getBolMySql() && $objInfraIBanco instanceof InfraMySql) ||
             ($this->getBolSqlServer() && $objInfraIBanco instanceof InfraSqlServer) ||
             ($this->getBolOracle() && $objInfraIBanco instanceof InfraOracle)) ){
        $this->processarErro('BANCO DE DADOS NAO SUPORTADO: '.get_parent_class($objInfraIBanco));
      }

      $objInfraMetaBD = new InfraMetaBD($objInfraIBanco);

      if (count($objInfraMetaBD->obterTabelas('tab_teste'))==0){
        $objInfraIBanco->executarSql('CREATE TABLE tab_teste (id '.$objInfraMetaBD->tipoNumero().' null)');
      }

      $objInfraIBanco->executarSql('DROP TABLE tab_teste');

      $objInfraParametro = new InfraParametro($objInfraIBanco);

      $strVersaoInstalada = trim($objInfraParametro->getValor($this->getStrParametroVersao(), false));

      if ($strVersaoInstalada=='' && $this->getBolErroVersaoInexistente()){
        $this->processarErro('VERSAO INSTALADA NAO IDENTIFICADA');
      }

      if ($strVersaoInstalada == $this->getStrVersaoAtual()){
        $this->processarErro('ULTIMA VERSAO '.$strVersaoInstalada.' JA ESTA INSTALADA');
      }

      if ($strVersaoInstalada!='') {
        $bolVersaoInstaladaEncontrada = false;
        foreach ($this->getArrVersoes() as $strVersao => $strFuncao) {
          if ($strVersaoInstalada == $strVersao) {
            $bolVersaoInstaladaEncontrada = true;
            break;
          }
        }

        if (!$bolVersaoInstaladaEncontrada){
          $this->processarErro('VERSAO INSTALADA '.$strVersaoInstalada.' NAO ENCONTRADA NO CONJUNTO DE VERSOES');
        }
      }

      $this->inicializar('INICIANDO ATUALIZACAO');

      $bolInstalar = false;
      $strUltimaVersao = $strVersaoInstalada;
      foreach($this->getArrVersoes() as $strVersao => $strFuncao){

        if ($strVersaoInstalada==''){
          $bolInstalar = true;
        }else if ($strVersaoInstalada == $strVersao){
          $bolInstalar = true;
          continue;
        }

        if ($bolInstalar){
          call_user_func(array($this, $strFuncao), $strUltimaVersao);
          $strUltimaVersao = $strVersao;
        }
      }

      if ($strUltimaVersao != ''){

        if ($strUltimaVersao != $this->getStrVersaoAtual()){
          $this->processarErro('VERSAO INSTALADA PELO SCRIPT '.$strVersaoInstalada.' NAO CORRESPONDE COM A ULTIMA VERSAO '.$this->getStrVersaoAtual());
        }

        $rs = $objInfraIBanco->consultarSql('select count(*) as existe from  infra_parametro where nome=\''.$this->getStrParametroVersao().'\'');

        if ($rs[0]['existe']==0){
          $objInfraIBanco->executarSql('insert into infra_parametro (nome, valor) values (\''.$this->getStrParametroVersao().'\', \''.$strUltimaVersao.'\')');
        }else{
          $objInfraIBanco->executarSql('update infra_parametro set valor=\''.$strUltimaVersao.'\' where nome=\''.$this->getStrParametroVersao().'\'');
        }

        $this->finalizar('VERSAO '. $this->getStrVersaoAtual() .' INSTALADA'."\n".'FIM');

      }else{

        if ($strVersaoInstalada != '' && !$bolInstalar){
          $this->processarErro('VERSAO INSTALADA '.$strVersaoInstalada.' DESCONHECIDA');
        }else {
          $this->processarErro('NENHUMA ATUALIZACAO INSTALADA');
        }
      }

    } catch(Exception $e){
      $this->processarErro('ERRO ATUALIZANDO VERSAO', $e);
    }
  }
}
?>