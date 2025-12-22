<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 30/05/2006 - criado por MGA
 *
 * @package infra_php
 */

/*

CREATE TABLE infra_sequencia
(
nome_tabela  VARCHAR(30)  NOT NULL ,
qtd_incremento  bigint  NOT NULL ,
num_atual  bigint  NOT NULL ,
num_maximo  bigint  NOT NULL
);


ALTER TABLE infra_sequencia
ADD CONSTRAINT  pk_infra_sequencia PRIMARY KEY (nome_tabela);

*/

class InfraSequencia {
  private $objInfraIBanco;

  public function __construct($objInfraIBanco){
    $this->objInfraIBanco = $objInfraIBanco;
  }

  public function obterProximaSequencia($strTabela) {
    if ($this->objInfraIBanco instanceof InfraSqlServer) {

      $sql = ' SELECT num_maximo FROM infra_sequencia  WHERE nome_tabela=' . $this->objInfraIBanco->formatarGravacaoStr($strTabela);
      $rsMax = $this->objInfraIBanco->consultarSql($sql);

      if (count($rsMax) == 0) {
        throw new InfraException('Sequência ' . $strTabela . ' não encontrada.');
      }

      $sql = 'update infra_sequencia set num_atual = num_atual + qtd_incremento OUTPUT Inserted.num_atual where nome_tabela = ' . $this->objInfraIBanco->formatarGravacaoStr($strTabela);
      $rsProx = $this->objInfraIBanco->consultarSql($sql);

      if ($rsProx[0]['num_atual'] > $rsMax[0]['num_maximo']) {
        throw new InfraException('Sequência ' . $strTabela . ' tentou ultrapassar o valor máximo.');
      }

      return $rsProx[0]['num_atual'];

    }else{

      $sql = '';
      $sql .= ' SELECT num_atual, qtd_incremento, num_maximo ';
      $sql .= ' FROM infra_sequencia ';
      $sql .= ' WHERE nome_tabela=' . $this->objInfraIBanco->formatarGravacaoStr($strTabela) . ' FOR UPDATE';

      $rs = $this->objInfraIBanco->consultarSql($sql);

      if (count($rs) == 0) {
        throw new InfraException('Sequência ' . $strTabela . ' não encontrada.');
      }

      $numProxSeq = $rs[0]['num_atual'] + $rs[0]['qtd_incremento'];

      if ($numProxSeq > $rs[0]['num_maximo']) {
        throw new InfraException('Sequência ' . $strTabela . ' tentou ultrapassar o valor máximo.');
      }

      $sql = '';
      $sql .= ' UPDATE infra_sequencia ';
      $sql .= ' SET num_atual=' . $this->objInfraIBanco->formatarGravacaoNum($numProxSeq);
      $sql .= ' WHERE nome_tabela=' . $this->objInfraIBanco->formatarGravacaoStr($strTabela);
      //echo $sql.'<br>';

      $this->objInfraIBanco->executarSql($sql);

      return $numProxSeq;
    }
  }

  public function verificarSequencia($strTabela){
 	  $sql = '';
 	  $sql .= ' SELECT count(*) as total';
 	  $sql .= ' FROM infra_sequencia ';
 	  $sql .= ' WHERE nome_tabela='.$this->objInfraIBanco->formatarGravacaoStr($strTabela);
 	  //echo $sql.'<br>';

 	  $rs = $this->objInfraIBanco->consultarSql($sql);

 	  if ( $rs[0]['total'] == 0) {
 	    return false;
 	  }
 	  return true;
  }

  public function reiniciarSequencia($strTabela,$numAtual=0) {

    if (!$this->verificarSequencia($strTabela)){
      throw new InfraException('Sequência '.$strTabela.' não encontrada.');
    }

    $sql = '';
    $sql .= ' UPDATE infra_sequencia ';
    $sql .= ' SET num_atual='.$numAtual;
    $sql .= ' WHERE nome_tabela='.$this->objInfraIBanco->formatarGravacaoStr($strTabela);
    //echo $sql.'<br>';
     
    $this->objInfraIBanco->executarSql($sql);
  }

  public function criarSequencia($strTabela, $numIncremento, $numAtual, $numMaximo){

    if ($this->verificarSequencia($strTabela)){
      throw new InfraException('Sequência '.$strTabela.' já existe.');
    }

 	  $sql = '';
 	  $sql .= ' INSERT INTO infra_sequencia (nome_tabela, qtd_incremento, num_atual, num_maximo) VALUES (';
 	  $sql .= $this->objInfraIBanco->formatarGravacaoStr($strTabela).',';
 	  $sql .= $this->objInfraIBanco->formatarGravacaoNum($numIncremento).',';
 	  $sql .= $this->objInfraIBanco->formatarGravacaoNum($numAtual).',';
 	  $sql .= $this->objInfraIBanco->formatarGravacaoNum($numMaximo).')';

 	  //echo $sql.'<br>';

 	  $this->objInfraIBanco->executarSql($sql);

  }

}

?>