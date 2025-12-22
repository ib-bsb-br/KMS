<?
  /*
  * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
  * 21/03/2007 - CRIADO POR cle@trf4.gov.br
  * 11/12/2012 - ALTERADO POR cle@trf4.gov.br (buscarInformacoesLocal RETORNA NOMES EM VEZ DOS IPs DAS MÁQUINAS MUMPS)
  * 26/08/2014 - ALTERADO POR dgn@trf4.gov.br (buscarInformacoesLocal consulta parâmetros da classe específica que a estente)
  */ 
  abstract class InfraMumps implements InfraIBanco {
    private $id;   
    private $strResultadoXML;
    private $transacao;
    
    //NADA A EXECUTAR NO CONSTRUTOR DA CLASSE
    public function __construct() {
      $this->id = null;
      $this->transacao = false;
    }

		public abstract function getCodigoLocal($strIdOrgao);
  	public abstract function getUf($strIdOrgao);
  	public abstract function getIp($strIdOrgao);
  	public abstract function getUrl($strIdOrgao);
  	public abstract function getCaminho1($strIdOrgao);
  	public abstract function getCaminho2($strIdOrgao);
  	public abstract function getUsuario($strIdOrgao);

    public function getIdBanco(){
			return __CLASS__;
		}
		
		public function getIdConexao(){
		  return $this->id;
		}
		
		public function isBolProcessandoTransacao(){
		  return $this->transacao;
		}
		
		public function getResultadoXML(){
		  return $this->strResultadoXML;
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
		
    public function getValorPK($tabela){
		  return null;
		}
		
    //PREPARA AS VARIÁVEIS DE AMBIENTE DA CSRH
    public function inicializarVariaveisMumps() {
      $arrInformacoesLocal = $this->buscarInformacoesLocal($_SESSION['numIdEstado']);
      $strComando = 'echo \''.$_SESSION['strSiglaUsuario'].';'.$arrInformacoesLocal[0].';'.session_id().'\' | rsh '.
                    $arrInformacoesLocal[2].' -l '.$arrInformacoesLocal[5].' '.$arrInformacoesLocal[3].' '.$arrInformacoesLocal[4].
                    ' \^WWMATRI';
      $strResultadoXML = shell_exec($strComando);
      $arrResultado = InfraMumpsXML::getInstance()->vetorizar($strResultadoXML);
      $_SESSION['numMatriculaUsuario'] = $arrResultado[0][Matricula];
		  $_SESSION['strNomeUsuario'] = $arrResultado[0][Nome];
    }
    
    //BUSCA OS ITENS DE MENUS DO USUÁRIO
    public function buscarItensMenu() {
      return $this->executarSql('SELECT * FROM usuario.ativo WHERE sigla="'.$_SESSION['strSiglaUsuario'].'"');
    }
    
    //EXECUTA UM SQL NO MUMPS (OS DOIS MÉTODOS SÃO NECESSÁRIOS PARA COMPATIBILIDADE COM A INFRA-ESTRUTURA)
    public function executarSql($strSQL, $arrCamposBind = null) {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraMumps->executarSql] ' . $strSQL);
      }

      $arrInformacoesLocal = $this->buscarInformacoesLocal($_SESSION['numIdEstado']);
      if ($this->procurarPrograma($_GET['acao'])) {
        $arrInformacoesLocal = $this->buscarInformacoesLocal(1);
      }
      $strComando = 'echo \''.$strSQL.'\' | rsh '.$arrInformacoesLocal[2].' -l '.$arrInformacoesLocal[5].' '.$arrInformacoesLocal[3].
                    ' '.$arrInformacoesLocal[4].' \^XMLMUMPS';
      $this->strResultadoXML = shell_exec($strComando);
      /*if (substr($strSQL, 0, 16) == 'x@proximodiautil') {
        InfraMail::enviar('<intranet@trf4.gov.br>', 'cle@trf4.gov.br', 'Sucesso na atualização Mumps/SIP', $strComando.' '.$this->strResultadoXML);
      }*/
      //InfraDebug::getInstance()->gravarInfra($this->strResultadoXML);
      return InfraMumpsXML::getInstance()->vetorizar($this->strResultadoXML);
    }
    
    public function consultarSql($strSQL) {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraMumps->consultarSql]');
      }

      return $this->executarSql($strSQL);
    }
    
    public function paginarSql($sql,$ini,$qtd){

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraMumps->paginarSql]');
      }

      $rs = $this->executarSql($sql);
      return array('totalRegistros'=>count($rs),'registrosPagina'=>array_slice($rs,$ini,$qtd));
    }
    
    public function limitarSql($sql,$qtd) {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraMumps->limitarSql]');
      }

      $rs = $this->executarSql($sql);
      return array_slice($rs,0,$qtd);
    }
    
    //EXECUTA UM PROGRAMA MUMPS (K-MUMPS)
    public function executarProgramaMumps($strParametros) {
      $arrInformacoesLocal = $this->buscarInformacoesLocal($_SESSION['numIdEstado']);
      $strUF = $arrInformacoesLocal[1];
      //AVALIA SE ESSE É UM DOS PROGRAMAS QUE RODAM NO TRF (SE É, MUDA AS INFORMAÇÕES DO LOCAL DEPOIS DE CONCATENAR A SIGLA COM A UF)
      if ($this->procurarPrograma($_GET['PROG'])) {
        $arrInformacoesLocal = $this->buscarInformacoesLocal(1);
				$strSiglaUsuario = "SJ".$strUF.$_SESSION['strSiglaUsuario'];
        $strComando = 'echo \''.$strSiglaUsuario.';'.$arrInformacoesLocal[0].';'.session_id().';'.$_SESSION['numMatriculaUsuario'].
                      '\' | rsh '.$arrInformacoesLocal[2].' -l '.$arrInformacoesLocal[5].' '.$arrInformacoesLocal[3].
                      ' '.$arrInformacoesLocal[4].' \^WWMATRI';
        shell_exec($strComando);
      }
      $strComando = 'echo \''.session_id().';'.$strParametros.';'.SessaoCSRH::getInstance()->getNumIdContextoUsuario().'\' '.
                    '| rsh '.$arrInformacoesLocal[2].' -l '.$arrInformacoesLocal[5].' '.$arrInformacoesLocal[3].' '.
                    $arrInformacoesLocal[4].' \^WWWEB';
      //LogCSRH::getInstance()->gravar($strComando);
      return shell_exec($strComando);
    }
    
    //VERIFICA SE UMA DAS POSIÇÕES DO VETOR FAZ PARTE DA STRING
		function procurarPrograma($strProg) {
      if (($_SESSION['numMatriculaUsuario'] >= 2000) && ($_SESSION['numMatriculaUsuario'] <= 9999)) {
        $strTipoServidor = "JUIZ";
      } else {
        $strTipoServidor = "SERV";
      }
			for ($i=0; $i<count($_SESSION['arrProgramasTRF']); $i++) {
				if (!(strpos($strProg, $_SESSION['arrProgramasTRF'][$i]["nomeprog"]) === False)) {
          if ($_SESSION['arrProgramasTRF'][$i]["servjuiz"] == $strTipoServidor) {
            return True;
          }
				}
			}
      return False;
		}
		
    //BUSCA AS INFORMAÇÕES DE CADA LOCAL
		public function buscarInformacoesLocal($numLocal) {
			$numCodigoLocal = $this->getCodigoLocal($numLocal);
			$strUF = $this->getUf($numLocal);
			$strIP = $this->getIp($numLocal);
			$strURL = $this->getUrl($numLocal);
			$strCaminho1 = $this->getCaminho1($numLocal);
			$strCaminho2 = $this->getCaminho2($numLocal);
			$strUsuario = $this->getUsuario($numLocal);
			return array($numCodigoLocal, $strUF, $strURL, $strCaminho1, $strCaminho2, $strUsuario);
		}
    
    public function abrirConexao(){ $this->id = $this->getIdBanco(); }
    public function fecharConexao(){ $this->id = null; }
    public function abrirTransacao(){$this->transacao = true;}
    public function confirmarTransacao(){$this->transacao = false;}
    public function cancelarTransacao(){$this->transacao = false;}
    
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
    
		public function formatarSelecaoDta($tabela,$campo,$alias){return $this->formatarSelecaoGenerico($tabela,$campo,$alias);}
		public function formatarSelecaoDth($tabela,$campo,$alias){return $this->formatarSelecaoGenerico($tabela,$campo,$alias);}
		public function formatarSelecaoStr($tabela,$campo,$alias){return $this->formatarSelecaoGenerico($tabela,$campo,$alias);}
		public function formatarSelecaoBol($tabela,$campo,$alias){return $this->formatarSelecaoGenerico($tabela,$campo,$alias);}
		public function formatarSelecaoNum($tabela,$campo,$alias){return $this->formatarSelecaoGenerico($tabela,$campo,$alias);}
		public function formatarSelecaoDin($tabela,$campo,$alias){return $this->formatarSelecaoGenerico($tabela,$campo,$alias);}
		public function formatarSelecaoDbl($tabela,$campo,$alias){return $this->formatarSelecaoGenerico($tabela,$campo,$alias);}
    public function formatarSelecaoBin($tabela,$campo,$alias){return $this->formatarSelecaoGenerico($tabela,$campo,$alias);}
		
		//DD/MM/AAAA -> DDMMAAAA
  	public function formatarGravacaoDta($dta){
		  return '"'.str_replace('/','',$dta).'"';
		}
		
  	public function formatarGravacaoDth($dth){return $dth;}
	  
  	public function formatarGravacaoStr($str){
  	  
  	  if ($this->isBolValidarISO88591() && InfraUtil::filtrarISO88591($str) != $str){
  	    throw new InfraException('Detectado caracter inválido.');
  	  }
  	  	
      return '"'.$str.'"';
    }
  	
    public function formatarGravacaoBol($bol){return $bol;}
  	public function formatarGravacaoNum($num){return $num;}
  	public function formatarGravacaoDin($din){return $din;}
  	public function formatarGravacaoDbl($dbl){return $dbl;}
		public function formatarGravacaoBin($bin){return $bin;}
  	
		//DDMMAAAA -> DD/MM/AAAA
  	public function formatarLeituraDta($dta){
		  return substr($dta,0,2).'/'.substr($dta,2,2).'/'.substr($dta,4);
		}
		
  	public function formatarLeituraDth($dth){return $dth;}
  	public function formatarLeituraStr($str){return trim($str);}
  	public function formatarLeituraBol($bol){return $bol;}
  	public function formatarLeituraNum($num){return $num;}
  	public function formatarLeituraDin($din){return $din;}
  	public function formatarLeituraDbl($dbl){return $dbl;}
  	public function formatarLeituraBin($bin){return $bin;}
  	
  	public function converterStr($tabela,$campo){      
  	  $ret = '';
      if ($tabela!==null){
        $ret .= $tabela.'.';
      }
      $ret .= $campo;
      return $ret;
    }
  	public function formatarPesquisaStr($strTabela, $strCampo,$strValor,$strOperador,$bolCaseInsensitive){return $strCampo.'="'.$strValor.'"';}
  	public function criarSequencialNativa($strSequencia, $numInicial){}
  }
	
?>