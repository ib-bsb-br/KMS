<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 * 
 * 30/05/2006 - criado por MGA
 *
 * @package infra_php
 */

/*
CREATE TABLE infra_parametro
(
	nome  varchar(100)  NOT NULL ,
	valor  varchar(max)  NULL ,
	CONSTRAINT  pk_infra_parametro PRIMARY KEY (nome)
);

*/
 		
class InfraParametro extends InfraRN {
	private $objInfraIBanco = null;
	
	public function __construct(InfraIBanco $objInfraIBanco){
	  $this->objInfraIBanco = $objInfraIBanco;	
	}
	
  protected function inicializarObjInfraIBanco(){
    return $this->objInfraIBanco;
  }
  
  protected function getValorConectado($strNome, $bolErroNaoEncontrado=true) {

    if (InfraDebug::isBolProcessar()) {
      InfraDebug::getInstance()->gravarInfra('[InfraParametro->getStrValor] ' . $strNome);
    }

  	
  	  $sql = '';
			$sql .= ' SELECT valor';
			$sql .= ' FROM infra_parametro ';
			$sql .= ' WHERE nome='.$this->objInfraIBanco->formatarGravacaoStr($strNome);
			//echo $sql.'<br>';

			$rs = $this->objInfraIBanco->consultarSql($sql);

			if (count($rs)==0) {
				if ($bolErroNaoEncontrado){
				  throw new InfraException('Parâmetro '.$strNome.' não encontrado.');
				}else{
					return null;
				}
			}

			return $rs[0]['valor'];
  }  

  protected function listarValoresConectado($arrNomes=null, $bolErroNaoEncontrado=true) {

    if (InfraDebug::isBolProcessar()) {
      InfraDebug::getInstance()->gravarInfra('[InfraParametro->listarValores] ');
    }

  	
  	  $sql = '';
			$sql .= ' SELECT nome, valor';
			$sql .= ' FROM infra_parametro ';
			
			if (is_array($arrNomes) && count($arrNomes) > 0){
				$sql .= ' WHERE nome IN (';
	
				$strSeparador = '';
				foreach($arrNomes as $strNome){
				  $sql .= $strSeparador.$this->objInfraIBanco->formatarGravacaoStr($strNome);
				  $strSeparador = ',';
				}
				
				$sql .= ')';
			}
						
			//echo $sql.'<br>';

			$rs = $this->objInfraIBanco->consultarSql($sql);

			$ret = array();
			foreach($rs as $registro){
				$ret[$registro['nome']] = ($registro['valor']==null)?'':$registro['valor'];
			}
			
			if ( $bolErroNaoEncontrado && is_array($arrNomes)){
				foreach($arrNomes as $strNome){
					if (!isset($ret[$strNome])){
						throw new InfraException('Parâmetro '.$strNome.' não encontrado.');
					}					
				}
			}

			return $ret;
  }  
  
  protected function setValorControlado($strNome,$strValor) {

    if (InfraDebug::isBolProcessar()) {
      InfraDebug::getInstance()->gravarInfra('[InfraParametro->setStrValor] ' . $strNome . ': ' . $strValor);
    }


    
  	  $sql = '';
			$sql .= ' SELECT count(*) as existe';
			$sql .= ' FROM infra_parametro ';
			$sql .= ' WHERE nome='.$this->objInfraIBanco->formatarGravacaoStr($strNome);
			//echo $sql.'<br>';
			
			$rs = $this->objInfraIBanco->consultarSql($sql);
    
			if ($rs[0]['existe']==0){
			  
			  if (strlen($strNome)>100){
			    throw new InfraException('Nome do parâmetro possui tamanho superior a 100 caracteres.');
			  }
			  
	  	  $sql = '';
    	  $sql .= ' INSERT INTO infra_parametro (nome,valor)';
    	  $sql .= ' VALUES ';
    	  $sql .= ' ('.$this->objInfraIBanco->formatarGravacaoStr($strNome).','.$this->objInfraIBanco->formatarGravacaoStr($strValor).')';
  			//echo $sql.'<br>';
			}else{
    	  $sql = '';
    	  $sql .= ' UPDATE infra_parametro ';
    	  $sql .= ' SET valor='.$this->objInfraIBanco->formatarGravacaoStr($strValor);
    	  $sql .= ' WHERE nome='.$this->objInfraIBanco->formatarGravacaoStr($strNome);
  			//echo $sql.'<br>';
			}
			
  	  $ret = $this->objInfraIBanco->executarSql($sql);
			
			return $ret;
  }  
  
  protected function isSetValorConectado($strNome) {

    if (InfraDebug::isBolProcessar()) {
      InfraDebug::getInstance()->gravarInfra('[InfraParametro->isSetStrValor] ' . $strNome);
    }

  	
  	  $sql = '';
			$sql .= ' SELECT valor';
			$sql .= ' FROM infra_parametro ';
			$sql .= ' WHERE nome='.$this->objInfraIBanco->formatarGravacaoStr($strNome);
			//echo $sql.'<br>';

			$rs = $this->objInfraIBanco->consultarSql($sql);

			if (count($rs)==0) {
				return false;
			}

			return true;
  }  
  
  
}
 
?>