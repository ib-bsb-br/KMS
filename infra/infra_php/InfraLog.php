<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 * 
 * 14/06/2006 - criado por MGA
 *
 * @package infra_php
 */

 		
abstract class InfraLog extends InfraRN {

	public static $ERRO = 'E';
	public static $AVISO = 'A';
	public static $INFORMACAO = 'I';
	public static $DEBUG = 'D';

	private $objInfraIBanco = null;

	public function __construct(InfraIBanco $objInfraIBanco) {
		$this->objInfraIBanco = $objInfraIBanco;
	}
	
	public function getNumTipoPK(){
		return InfraDTO::$TIPO_PK_SEQUENCIAL;
	}

	public function isBolTratarTipos(){
		return false;
	}

	protected function inicializarObjInfraIBanco(){
    return $this->objInfraIBanco;
  }
	 
  protected function gravarControlado($str, $strStatipo = 'E') {
		try {  	
		  
		  if (InfraString::isBolVazia($str)){
		    throw new InfraException('Texto do Log não informado.');
		  }

			if (!in_array($strStatipo,array_keys(self::getArrTipos()))){
				throw new InfraException('Tipo do log inválido.');
			}

		  if ($this->getNumTipoPK()==InfraDTO::$TIPO_PK_SEQUENCIAL){
	      $objInfraSequencia = new InfraSequencia($this->getObjInfraIBanco());
	  		$numProxSeq = $objInfraSequencia->obterProximaSequencia('infra_log');
		  }else if ($this->getNumTipoPK()==InfraDTO::$TIPO_PK_NATIVA){
  		  $numProxSeq = $this->getObjInfraIBanco()->getValorSequencia('seq_infra_log');
		  }else{
		  	throw new InfraException('Tipo PK inválida para infra_log.');
		  }

    	//TRE-TO
  	  $arrCampos = array();
  		
  	  $sql = ' INSERT INTO infra_log (id_infra_log, dth_log,';

			if ($this->isBolTratarTipos()){
				$sql .= 'sta_tipo,';
			}

			$sql .= 'texto_log, ip) ';

  	  $sql .= ' VALUES (';
  	  $sql .= $numProxSeq.',';
  	  $sql .= $this->getObjInfraIBanco()->formatarGravacaoDth(InfraData::getStrDataHoraAtual()).',';

			if ($this->isBolTratarTipos()){
			  $sql .= 	$this->getObjInfraIBanco()->formatarGravacaoStr($strStatipo).',';
			}

  	  if($this->getObjInfraIBanco() instanceof InfraOracle){
  	    $strNomeCampo = ':texto_log';
  	    $sql .= $strNomeCampo.',';
  	    $arrCampos[$strNomeCampo] = $this->getObjInfraIBanco()->formatarGravacaoStr(InfraUtil::filtrarISO88591($str));
  	  }else{
  	    $sql .= $this->getObjInfraIBanco()->formatarGravacaoStr(InfraUtil::filtrarISO88591($str)).',';
  	  }
  	  
  	  $sql .= $this->getObjInfraIBanco()->formatarGravacaoStr(InfraUtil::getStrIpUsuario()).')';
  	  
  	  $this->getObjInfraIBanco()->executarSql($sql, $arrCampos /*TRE-TO*/);
  	  
  	  return $numProxSeq;
  	  
		} catch(Exception $e){
      InfraDebug::getInstance()->gravarInfra($e->__toString());
			throw new InfraException('Erro gravando log.',$e);
		}	
  }

	public static function getArrTipos(){
		return array(self::$ERRO => 'Erro',
				         self::$AVISO => 'Aviso',
				         self::$INFORMACAO => 'Informação',
				         self::$DEBUG => 'Debug');
	}

  public function gerarTelaListagem($objInfraPagina,$objInfraSessao, $objInfraIBanco){
    PaginaInfra::setObjInfraPagina($objInfraPagina);
    SessaoInfra::setObjInfraSessao($objInfraSessao);
    BancoInfra::setObjInfraIBanco($objInfraIBanco);
    require_once dirname(__FILE__).'/formularios/infra_log_lista.php';
  }
}
?>