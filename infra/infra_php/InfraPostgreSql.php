<?
/**
 * @package infra_php
 *
 */
abstract class InfraPostgreSql implements InfraIBanco {
    private $conexao;
    private $id;
    private $transacao;
    
		public abstract function getServidor();
		public abstract function getPorta();
		public abstract function getBanco();
		public abstract function getUsuario();
		public abstract function getSenha();
		
		public function __construct(){
			$this->conexao = null;
			$this->id = null;
			$this->transacao = false;
		}
		
		public function __destruct(){
		  if ($this->getIdConexao()!=null){
		    try{ 
		      $this->fecharConexao(); 
		    }catch(Exception $e){}  
		  }
		}
						
		public function getIdBanco(){
			return __CLASS__.'-'.$this->getServidor().'-'.$this->getPorta().'-'.$this->getBanco().'-'.$this->getUsuario();
		}
		
		public function getIdConexao(){
		  return $this->id;
		}
		
		public function getValorSequencia($sequencia){
		  $rs = $this->consultarSql('SELECT NEXTVAL(\''.$sequencia.'\') AS sequencia');
		  return $rs[0]['sequencia'];
		}
		
    public function isBolProcessandoTransacao(){
		  return $this->transacao;
		}		
		
		public function isBolForcarPesquisaCaseInsensitive(){
			return true;
		}

		public function isBolManterConexaoAberta(){
			return false;
		}

		public function isBolValidarISO88591(){
		  return false;
		}
		
				
//SELECAO		
    private function formatarSelecaoGenerico($tabela,$campo,$alias){
      $ret = '';
      if ($tabela!==null){
        $ret .= $tabela.'.';
      }
      
      $ret .= $campo;
      
      if ($alias!=null) {
        $ret .= ' AS '.$alias;
      }
      return $ret;
    }
    
    private function formatarSelecaoAsVarchar($tabela,$campo,$alias){
      $ret = 'CAST(';
      if ($tabela!==null){
        $ret .= $tabela.'.';
      }
      $ret .= $campo.' as varchar) AS ';
      
		  if ($alias!==null){
		    $ret .= $alias;
		  }else{
		    $ret .= $campo;
		  }
      return $ret;
    }

		public function formatarSelecaoDta($tabela,$campo,$alias){
		  return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
		}
		
		public function formatarSelecaoDth($tabela,$campo,$alias){
		  return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
		}
		
		public function formatarSelecaoStr($tabela,$campo,$alias){
		  return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
		}
		
		public function formatarSelecaoBol($tabela,$campo,$alias){
		  return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
		}
		
		public function formatarSelecaoNum($tabela,$campo,$alias){
		  return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
		}

		public function formatarSelecaoDin($tabela,$campo,$alias){
		  return $this->formatarSelecaoAsVarchar($tabela,$campo,$alias);
		}
		
		public function formatarSelecaoDbl($tabela,$campo,$alias){
		  return $this->formatarSelecaoAsVarchar($tabela,$campo,$alias);
		}
		
    public function formatarSelecaoBin($tabela,$campo,$alias){
		  return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
		}
		
		
//GRAVACAO     
		public function formatarGravacaoDta($dta){
			return $this->gravarData(substr($dta,0,10));
		}
		
		public function formatarGravacaoDth($dth){
      return $this->gravarData($dth);		
    }
		
		public function formatarGravacaoStr($str){
		  if ($str===null || $str===''){
		    return 'NULL';
		  }

		  if ($this->isBolValidarISO88591() && InfraUtil::filtrarISO88591($str) != $str){
		    throw new InfraException('Detectado caracter inválido.');
		  }
		  
			$str = str_replace("\'",'\'',$str);
			$str = str_replace("'",'\'\'',$str);
				
			return '\''.$str.'\'';
		}
		
		public function formatarGravacaoBol($bol){
			if ( $bol===true ) {
				return 'true';
			} 
			return 'false';
		}
		
		public function formatarGravacaoNum($num){
			if (trim($num)===''){
				return 'NULL';
			}
			return '\''.$num.'\'';
		}

		public function formatarGravacaoDin($din){
			if (trim($din)===''){
				return 'NULL';
			}
		  return '\''.InfraUtil::prepararDin($din).'\'';
		}
		
		public function formatarGravacaoDbl($dbl){
			if (trim($dbl)===''){
				return 'NULL';
			}
			return '\''.InfraUtil::prepararDbl($dbl).'\'';
		}
		
		public function formatarGravacaoBin($bin){
		  if ($bin===null || $bin===''){
		    return 'NULL';
		  }
		  return '\''.pg_escape_bytea($bin).'\'::bytea';
		}
		
//LEITURA		

    public function converterStr($tabela,$campo){
      $ret = 'CAST(';
      if ($tabela!==null){
        $ret .= $tabela.'.';
      }
      $ret .= $campo.' as varchar)';
      return $ret;
    }

		public function formatarPesquisaStr($strTabela,$strCampo,$strValor,$strOperador,$bolCaseInsensitive){
			if ($bolCaseInsensitive){
			  return 'upper('.$strCampo.') '.$strOperador.' \''.str_replace('\'','\'\'',InfraString::transformarCaixaAlta($strValor)).'\' ';	
			}else{
				return $strCampo.' '.$strOperador.' \''.str_replace('\'','\'\'',$strValor).'\' ';
			}
		}
		
		public function formatarLeituraDta($dta){
			$ret = $this->lerData($dta);
			if ($ret != null){
				return substr($ret,0,10); 
			}
			return null;
		}
		
		public function formatarLeituraDth($dth){
			return $this->lerData($dth);
		}
		
		public function formatarLeituraStr($str){
			return $str;
		}
		
		public function formatarLeituraBol($bol){
			if ( $bol === 't' ) {
				return true;
			} else {
			  return false;
			}	
		}
		
		public function formatarLeituraNum($num){
			return $num;
		}

		public function formatarLeituraDin($din){
		  return InfraUtil::formatarDin($din);
		}
		
		public function formatarLeituraDbl($dbl){
 		  return InfraUtil::formatarDbl($dbl);
		}
		
  	public function formatarLeituraBin($bin){
  	  return pg_unescape_bytea($bin);
		}
		

	  //ABRE A CONEXÃO
	  public function abrirConexao() {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->abrirConexao] ' . $this->getIdBanco());
      }


	  	//InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->abrirConexao] 10');
	  	 
		  if ( $this->conexao!=null) {
		  	throw new InfraException('Tentativa de abrir nova conexão sem fechar a anterior.');
		  }
		  
		  //InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->abrirConexao] 20');
		  $strConexao = '';
	    $strConexao .= ' host='.$this->getServidor();
	    $strConexao .= ' port='.$this->getPorta();
	    $strConexao .= ' dbname='.$this->getBanco();
	    $strConexao .= ' user='.$this->getUsuario();
	    $strConexao .= ' password='.$this->getSenha();
	    
	    //InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->abrirConexao] 30');
	    $this->conexao = pg_connect($strConexao, PGSQL_CONNECT_FORCE_NEW);
	    $this->id = $this->getIdBanco();
	    
	    //InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->abrirConexao] 40');
		  if ($this->conexao===FALSE) {
        throw new InfraException(pg_last_error($this->conexao));
		  }
	  }
	
	  //FECHA A CONEXÃO
	  public function fecharConexao() {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->fecharConexao] ' . $this->getIdConexao());
      }


	  	//InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->fecharConexao] 10');
	  	if ($this->conexao==null) {
	  		throw new InfraException('Tentativa de fechar conexão que não foi aberta.');
	  	}
	  	//InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->fecharConexao] 20');
	  	
	    pg_close($this->conexao);
	    
	    $this->conexao = null;
	    $this->id = null;
	  }

    public function abrirTransacao(){

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->abrirTransacao] ' . $this->getIdConexao());
      }


    	//InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->abrirTransacao] 10');
	  	if ($this->conexao==null) {
	  		throw new InfraException('Tentando abrir transação em uma conexão fechada.');
	  	}
    	//InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->abrirTransacao] 20');
    	$this->executarSql('BEGIN');
    	//InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->abrirTransacao] 30');
    	
    	$this->transacao = true;
    }
	    
	  //CONFIRMA A TRANSAÇÃO
	  public function confirmarTransacao() {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->confirmarTransacao] ' . $this->getIdConexao());
      }


	  	//InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->confirmarTransacao] 10');
	  	if ($this->conexao==null) {
	  		throw new InfraException('Tentando confirmar transação em uma conexão fechada.');
	  	}
	  	
	  	//InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->confirmarTransacao] 20');
	    $this->executarSql('COMMIT');
	    //InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->confirmarTransacao] 30');
	    
	    $this->transacao = false;
	  }
	    
	  //CANCELA A TRANSAÇÃO
	  public function cancelarTransacao() {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->cancelarTransacao] ' . $this->getIdConexao());
      }


	    //InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->cancelarTransacao] 10');
	  	if ($this->conexao==null) {
	  		throw new InfraException('Tentando desfazer transação em uma conexão fechada.');
	  	}
	  	//InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->cancelarTransacao] 20');
	    $this->executarSql('ROLLBACK');
	    //InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->cancelarTransacao] 30');
	    
	    $this->transacao = false;
	  }
    
	  //EXECUTA UMA CLÁUSULA SQL
	  public function consultarSql($sql) {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->consultarSql] ' . $sql);
        $numSeg = InfraUtil::verificarTempoProcessamento();
      }

			
	  	//InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->consultarSql] 10 : '.$sql);
	  	if ($this->conexao==null) {
	  		throw new InfraException('Tentando executar uma consulta em uma conexão fechada.');
	  	}
	  	//InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->consultarSql] 20');
	    $resultado = pg_query($this->conexao, $sql);
	    //InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->consultarSql] 30');
	    if ( $resultado === FALSE ) {
	    	throw new InfraException(pg_last_error($this->conexao),null,$sql);
	    }
	    //InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->consultarSql] 40');
	    $vetor_resultado = pg_fetch_all($resultado);
	    if ($vetor_resultado===FALSE){
	    	$vetor_resultado = array();
	    }

      if (InfraDebug::isBolProcessar()) {
        $numSeg = InfraUtil::verificarTempoProcessamento($numSeg);
        InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->consultarSql] ' . $numSeg . ' s');
      }


	    return $vetor_resultado;
	  }
	  
	  public function paginarSql($sql,$ini,$qtd){

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->paginarSql] ' . $sql);
      }


      $arr = explode(' ',$sql);
      $select = '';
      for($i=0;$i<count($arr);$i++){
        if (strtoupper($arr[$i])=='FROM'){
          break;
        }
      }

      $sqlTotal = 'SELECT COUNT(*) as total';
      for(;$i<count($arr);$i++){
        if (strtoupper($arr[$i])=='ORDER'){
          break;
        }
        $sqlTotal .= ' '.$arr[$i];
      }
      $rsTotal = $this->consultarSql($sqlTotal);
      
	    $sql .= ' LIMIT '.$qtd.' OFFSET '.$ini;
	    
	    $rs = $this->consultarSql($sql);
	    
	    return array('totalRegistros'=>$rsTotal[0]['total'],'registrosPagina'=>$rs);
	  }
	    
	  public function limitarSql($sql,$qtd) {

      //if (InfraDebug::isBolProcessar()) {
      //  InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->limitarSql] ' . $sql);
      //}


	    $sql .= ' LIMIT '.$qtd;
	    return $this->consultarSql($sql);
	  }

	  public function executarSql($sql, $arrCamposBind = null) {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->executarSql] ' . substr($sql, 0, INFRA_TAM_MAX_LOG_SQL));
        $numSeg = InfraUtil::verificarTempoProcessamento();
      }

	  	
	  	//InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->executarSql] 10 : '.$sql);
	  	if ($this->conexao==null) {
	  		throw new InfraException('Tentando executar um comando em uma conexão fechada.');
	  	}
	  	//InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->executarSql] 20');
	    $resultado = pg_query($this->conexao,$sql);
	    //InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->executarSql] 30');
	    
	    if ( $resultado === FALSE ) {
	    	throw new InfraException(pg_last_error($this->conexao),null,substr($sql,0,INFRA_TAM_MAX_LOG_SQL));
	    }
	    $numReg = pg_affected_rows($resultado);

      if (InfraDebug::isBolProcessar()) {
        $numSeg = InfraUtil::verificarTempoProcessamento($numSeg);
        InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->executarSql] ' . $numReg . ' registro(s) afetado(s)');
        InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->executarSql] ' . $numSeg . ' s');
      }


	    return $numReg;
	  }
	    
	    
		function lerData($postgresql_date)
		{
			//2006-12-15 00:00:00
			return substr($postgresql_date,8,2).'/'.
			       substr($postgresql_date,5,2).'/'.
			       substr($postgresql_date,0,4).substr($postgresql_date,10);
		}
	
	 
		public function gravarData($brasil_date)
		{
			//December 15, 2006 00:00:00
      if(trim($brasil_date)===''){
      	return 'NULL';
      }
      			
			if (strlen($brasil_date)==10){
			  $brasil_date .= ' 00:00:00';
			}
			
			$mes = substr($brasil_date,3,2);

			switch ($mes)
			{
				case '01':
							$mes = 'January';
							break;
				case '02':
							$mes = 'February';
							break;
				case '03':
							$mes = 'March';
							break;
				case '04':
							$mes = 'April';
							break;
				case '05':
							$mes = 'May';
							break;
				case '06':
							$mes = 'June';
							break;
				case '07':
							$mes = 'July';
							break;
				case '08':
							$mes = 'August';
							break;
				case '09':
							$mes = 'September';
							break;
				case '10':
							$mes = 'October';
							break;
				case '11':
							$mes = 'November';
							break;
				case '12':
							$mes = 'December';
							break;
			}
			return '\''.$mes.' '.substr($brasil_date,0,2).', '.substr($brasil_date,6).'\'';
		}
		
		public function criarSequencialNativa($strSequencia, $numInicial){
		  //InfraDebug::getInstance()->gravarInfra('[InfraPostgreSql->criarSequencialNativa]');
		}
}
?>