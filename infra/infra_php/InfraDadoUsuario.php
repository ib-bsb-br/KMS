<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 * 
 * 30/05/2006 - criado por MGA
 *
 * @package infra_php
 */

/*
CREATE TABLE infra_dado_usuario (
	id_usuario            integer  NOT NULL ,
	nome                  varchar(50)  NOT NULL ,
	valor                 varchar(4000)  NULL 
);

ALTER TABLE infra_dado_usuario ADD CONSTRAINT  pk_infra_dado_usuario PRIMARY KEY (id_usuario  ASC,nome  ASC);

*/
 		
class InfraDadoUsuario extends InfraRN {
	private $objInfraIBanco = null;
	private $objInfraSessao = null;
	
	public function __construct(InfraSessao $objInfraSessao){
	  $this->objInfraSessao = $objInfraSessao;
	  $this->objInfraIBanco = $objInfraSessao->getObjInfraIBanco();	
	}
	
  protected function inicializarObjInfraIBanco(){
    return $this->objInfraIBanco;
  }
  
  protected function getValorConectado($strNome, $numIdUsuario = null) {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraDadoUsuario->getStrValor] ' . $strNome);
      }

      if ($numIdUsuario == null){
        $numIdUsuario =  $this->objInfraSessao->getNumIdUsuario();
      }

      if ($numIdUsuario==null){
  	    throw new InfraException('Usuário não configurado na sessão.');
  	  }
  	  	
  	  $sql = '';
			$sql .= ' SELECT valor';
			$sql .= ' FROM infra_dado_usuario ';
			$sql .= ' WHERE nome='.$this->objInfraIBanco->formatarGravacaoStr($strNome);
			$sql .= ' AND id_usuario='.$this->objInfraIBanco->formatarGravacaoNum($numIdUsuario);

			//echo $sql.'<br>';
					
			$rs = $this->objInfraIBanco->consultarSql($sql);

			if (count($rs)==0) {
				return null;
			}

			return $rs[0]['valor'];
  }  

  protected function setValorControlado($strNome, $strValor, $numIdUsuario = null) {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraDadoUsuario->setStrValor] ' . $strNome . ': ' . $strValor);
      }

      if ($numIdUsuario == null){
        $numIdUsuario =  $this->objInfraSessao->getNumIdUsuario();
      }

 	    if ($numIdUsuario == null){
 	      throw new InfraException('Usuário não configurado na sessão.');
 	    }
 	    
 	    if (strlen($strNome)>50){
 	      throw new InfraException('Nome do Dado do Usuário possui tamanho superior a 50 caracteres.');
 	    }
 	    
 	    if (strlen($strValor)>4000){
 	      throw new InfraException('Valor do Dado do Usuário possui tamanho superior a 4000 caracteres.');
 	    }
    
			if (!$this->isSetValor($strNome, $numIdUsuario)){
	  	  $sql = '';
    	  $sql .= ' INSERT INTO infra_dado_usuario (id_usuario, nome, valor)';
    	  $sql .= ' VALUES ';
    	  $sql .= ' ('.$this->objInfraIBanco->formatarGravacaoNum($numIdUsuario).','.$this->objInfraIBanco->formatarGravacaoStr($strNome).','.$this->objInfraIBanco->formatarGravacaoStr($strValor).')';
  			//echo $sql.'<br>';
			}else{
    	  $sql = '';
    	  $sql .= ' UPDATE infra_dado_usuario ';
    	  $sql .= ' SET valor='.$this->objInfraIBanco->formatarGravacaoStr($strValor);
    	  $sql .= ' WHERE nome='.$this->objInfraIBanco->formatarGravacaoStr($strNome);
    	  $sql .= ' AND id_usuario='.$this->objInfraIBanco->formatarGravacaoNum($numIdUsuario);
  			//echo $sql.'<br>';
			}
			
  	  $ret = $this->objInfraIBanco->executarSql($sql);
			
			return $ret;
  }  
  
  protected function isSetValorConectado($strNome, $numIdUsuario = null) {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraDadoUsuario->isSetStrValor] ' . $strNome);
      }

      if ($numIdUsuario == null){
        $numIdUsuario =  $this->objInfraSessao->getNumIdUsuario();
      }

  	  if ($numIdUsuario == null){
  	    throw new InfraException('Usuário não configurado na sessão.');
  	  }
  	
  	  $sql = '';
			$sql .= ' SELECT valor';
			$sql .= ' FROM infra_dado_usuario ';
			$sql .= ' WHERE nome='.$this->objInfraIBanco->formatarGravacaoStr($strNome);
			$sql .= ' AND id_usuario='.$this->objInfraIBanco->formatarGravacaoNum($numIdUsuario);
			//echo $sql.'<br>';

			$rs = $this->objInfraIBanco->consultarSql($sql);

			if (count($rs)==0) {
				return false;
			}

			return true;
  }
  
  protected function removerValorControlado($strNome, $numIdUsuario = null){

    if ($numIdUsuario==null){
      $numIdUsuario =  $this->objInfraSessao->getNumIdUsuario();
    }

    if ($this->isSetValor($strNome, $numIdUsuario)){
      $sql = '';
      $sql .= ' DELETE FROM infra_dado_usuario ';
      $sql .= ' WHERE nome='.$this->objInfraIBanco->formatarGravacaoStr($strNome);
      $sql .= ' AND id_usuario='.$this->objInfraIBanco->formatarGravacaoNum($numIdUsuario);
      //echo $sql.'<br>';
      
      $rs = $this->objInfraIBanco->executarSql($sql);
    }
  }
  
  protected function removerValoresUsuarioControlado($numIdUsuario = null){

    if ($numIdUsuario==null){
      $numIdUsuario =  $this->objInfraSessao->getNumIdUsuario();
    }

		$sql = '';
		$sql .= ' DELETE FROM infra_dado_usuario ';
		$sql .= ' WHERE id_usuario='.$this->objInfraIBanco->formatarGravacaoNum($numIdUsuario);
		//echo $sql.'<br>';

		$rs = $this->objInfraIBanco->executarSql($sql);
  }
}
?>