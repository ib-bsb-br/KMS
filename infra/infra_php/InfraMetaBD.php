<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 19/03/2015 - criado por MGA
 *
 * @package infra_php
 */

class InfraMetaBD {

  private $objInfraIBanco;

  public function __construct($objInfraIBanco){
    $this->objInfraIBanco = $objInfraIBanco;
  }

  public function getObjInfraIBanco(){
    return $this->objInfraIBanco;
  }

  public function obterTabelas($strNomeTabela = null, $arrTabelasIgnorar = null){
    $ret = '';
    if ($this->objInfraIBanco instanceof InfraSqlServer){

      $sql = 'select lower(name) as table_name from sys.tables where lower(name) not in (\'dtproperties\')';

      if ($strNomeTabela!=null){
        $sql .= ' and lower(name)=\''.strtolower($strNomeTabela).'\'';
      }

      $sql .= $this->formatarTabelasIgnorar('lower(name)',$arrTabelasIgnorar);

      $sql .= ' order by name asc';

      $ret = $this->objInfraIBanco->consultarSql($sql);

    }elseif ($this->objInfraIBanco instanceof InfraMySql){

      $sql = 'select lower(table_name) as table_name from information_schema.tables where lower(table_type) = \'base table\' AND lower(table_schema)=\''.strtolower($this->objInfraIBanco->getBanco()).'\'';

      if ($strNomeTabela!=null) {
        $sql .= ' and lower(table_name)=\'' . strtolower($strNomeTabela) . '\'';
      }

      $sql .= $this->formatarTabelasIgnorar('lower(table_name)',$arrTabelasIgnorar);

      $sql .= ' order by table_name asc;';

      $ret = $this->objInfraIBanco->consultarSql($sql);

    }else if ($this->objInfraIBanco instanceof InfraOracle){

      $sql = 'select lower(table_name) as table_name from all_tables where lower(owner)=\''.strtolower($this->objInfraIBanco->getUsuario()).'\'';

      if ($strNomeTabela!=null) {
        $sql .= ' and lower(table_name)=\'' . strtolower($strNomeTabela) . '\'';
      }

      $sql .= $this->formatarTabelasIgnorar('lower(table_name)', $arrTabelasIgnorar);

      $sql .= ' order by table_name asc';

      $ret = $this->objInfraIBanco->consultarSql($sql);
    }

    return $ret;
  }

  public function obterColunasTabela($strNomeTabela, $strNomeColuna = null){
    $ret = '';

    if ($this->objInfraIBanco instanceof InfraMySql ||  $this->objInfraIBanco instanceof InfraSqlServer){

      $sql = 'SELECT lower(column_name) as column_name, is_nullable, lower(data_type) as data_type, character_maximum_length, numeric_precision, numeric_scale
          FROM INFORMATION_SCHEMA.COLUMNS
          where lower(table_name)=\''.strtolower($strNomeTabela).'\'';

      if ($this->objInfraIBanco instanceof InfraMySql){
        $sql .= ' AND lower(table_schema)=\''.strtolower($this->objInfraIBanco->getBanco()).'\'';
      }

      if ($strNomeColuna != null){
        $sql .= ' and lower(column_name)=\''.strtolower($strNomeColuna).'\'';
      }

      $sql .= ' order by ordinal_position asc';

      $ret = $this->objInfraIBanco->consultarSql($sql);

    }else if ($this->objInfraIBanco instanceof InfraOracle){


      $sql = 'SELECT lower(column_name) as column_name, nullable as is_nullable, lower(data_type) as data_type, char_length as character_maximum_length, data_precision as numeric_precision, data_scale as numeric_scale
          FROM all_tab_columns
          WHERE lower(table_name)=\''.strtolower($strNomeTabela).'\' AND lower(owner)=\''.strtolower($this->objInfraIBanco->getUsuario()).'\'';


      if ($strNomeColuna != null){
        $sql .= ' AND lower(column_name)=\''.strtolower($strNomeColuna).'\'';
      }

      //$sql .= ' order by ordinal_position asc';

      $ret = $this->objInfraIBanco->consultarSql($sql);

      $numRegistros = count($ret);
      for($i=0;$i < $numRegistros;$i++){
        if ($ret[$i]['is_nullable']=='Y'){
          $ret[$i]['is_nullable'] = 'YES';
        }else{
          $ret[$i]['is_nullable'] = 'NO';
        }
      }

    }

    return $ret;
  }

  public function obterRegistrosTabela($strNomeTabela){
    $sql = 'select count(*) as total from ';

    if ($this->objInfraIBanco instanceof InfraOracle){
      $sql .= $this->objInfraIBanco->getUsuario().'.';
    }

    $sql .= $strNomeTabela;

    return $this->objInfraIBanco->consultarSql($sql);
  }

  public function obterNomeConstraint($strNomeTabela, $strNomeConstraint, $strTipoConstraint){
    $ret = '';

    if ($this->objInfraIBanco instanceof InfraSqlServer){

      $sql = 'SELECT lower(constraint_name) as constraint_name FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
              WHERE lower(table_name)=\''.strtolower($strNomeTabela).'\'';

      if (strtolower($strTipoConstraint)=='primary key' && strtolower($strNomeConstraint)=='primary'){
        $sql .= ' AND lower(constraint_name)=\'pk_'.strtolower($strNomeTabela).'\'';
      }else{
        $sql .= ' AND lower(constraint_name)=\''.strtolower($strNomeConstraint).'\'';
      }

      $ret = $this->objInfraIBanco->consultarSql($sql);

    }elseif ($this->objInfraIBanco instanceof InfraMySql){

      $sql = 'select lower(constraint_name) as constraint_name from information_schema.table_constraints where
              lower(constraint_schema) =\''.strtolower($this->objInfraIBanco->getBanco()) .'\'
              and lower(table_name)=\''.strtolower($strNomeTabela).'\' AND lower(table_schema)=\''.strtolower($this->objInfraIBanco->getBanco()).'\'';

      if (strtolower($strTipoConstraint)=='primary key'){
        $sql .= ' and lower(constraint_name)=\'primary\'';
      }else{
        $sql .= ' and lower(constraint_name)=\''.strtolower($strNomeConstraint).'\'';
      }

      $ret = $this->objInfraIBanco->consultarSql($sql);

    }elseif ($this->objInfraIBanco instanceof InfraOracle){

      $sql = 'select lower(constraint_name) as constraint_name from all_constraints where
              lower(owner) =\''.strtolower($this->objInfraIBanco->getUsuario()) .'\'
              and lower(table_name)=\''.strtolower($strNomeTabela).'\'';

      if (strtolower($strTipoConstraint)=='primary key' && strtolower($strNomeConstraint)=='primary'){
        $sql .= ' AND lower(constraint_name)=\'pk_'.strtolower($strNomeTabela).'\'';
      }else{
        $sql .= ' AND lower(constraint_name)=\''.strtolower($strNomeConstraint).'\'';
      }

      $ret = $this->objInfraIBanco->consultarSql($sql);

    }

    return $ret;
  }

  public function obterConstraints($strNomeTabela = null, $arrTabelasIgnorar = null){
    $ret = '';
    if ($this->objInfraIBanco instanceof InfraMySql || $this->objInfraIBanco instanceof InfraSqlServer){

      $sql = 'select lower(constraint_name) as constraint_name, lower(constraint_type) as constraint_type, lower(table_name) as table_name from INFORMATION_SCHEMA.TABLE_CONSTRAINTS where lower(constraint_type) <> \'unique\'';

      if ($this->objInfraIBanco instanceof InfraMySql){
        $sql .= ' AND lower(table_schema)=\''.strtolower($this->objInfraIBanco->getBanco()).'\'';
      }

      if ($strNomeTabela!=null){
        $sql .= ' and lower(table_name)=\''.strtolower($strNomeTabela).'\'';
      }

      $sql .= $this->formatarTabelasIgnorar('lower(table_name)', $arrTabelasIgnorar);

      $sql .= ' order by table_name';

      $ret = $this->objInfraIBanco->consultarSql($sql);

    }else if ($this->objInfraIBanco instanceof InfraOracle){

      $sql = 'select lower(constraint_name) as constraint_name, decode(constraint_type, \'P\', \'primary key\', \'R\',\'foreign key\') as constraint_type, lower(table_name) as table_name from all_constraints where lower(constraint_type) in (\'p\',\'r\')
              AND lower(owner)=\''.strtolower($this->objInfraIBanco->getUsuario()).'\'';

      if ($strNomeTabela!=null){
        $sql .= ' and lower(table_name)=\''.strtolower($strNomeTabela).'\'';
      }

      $sql .= $this->formatarTabelasIgnorar('lower(table_name)', $arrTabelasIgnorar);

      $sql .= ' order by table_name';

      $ret = $this->objInfraIBanco->consultarSql($sql);

    }
    return $ret;
  }

  public function obterNomeColunasConstraint($strNomeTabela, $strNomeConstraint, $strTipoConstraint){
    $ret = '';
    if ($this->objInfraIBanco instanceof InfraSqlServer){

      $sql = 'SELECT lower(table_name) as table_name, lower(constraint_name) as constraint_name, lower(column_name) as column_name FROM INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE
          where lower(table_name)=\''.strtolower($strNomeTabela).'\'';

      if (strtolower($strTipoConstraint)=='primary key' && strtolower($strNomeConstraint)=='primary'){
        $sql .= ' AND lower(constraint_name)=\'pk_'.strtolower($strNomeTabela).'\'';
      }else{
        $sql .= ' AND lower(constraint_name)=\''.strtolower($strNomeConstraint).'\'';
      }

      $ret = $this->objInfraIBanco->consultarSql($sql);

    }elseif ($this->objInfraIBanco instanceof InfraMySql){

      $sql = 'select lower(table_name) as table_name, lower(constraint_name) as constraint_name, lower(column_name) as column_name
          from information_schema.key_column_usage
          where lower(constraint_schema) =\''.strtolower($this->objInfraIBanco->getBanco()) .'\'
          and lower(table_name)=\''.strtolower($strNomeTabela).'\' AND lower(table_schema)=\''.strtolower($this->objInfraIBanco->getBanco()).'\'';

      if (strtolower($strTipoConstraint)=='primary key'){
        $sql .= ' and lower(constraint_name)=\'primary\'';
      }else{
        $sql .= ' and lower(constraint_name)=\''.strtolower($strNomeConstraint).'\'';
      }

      $ret = $this->objInfraIBanco->consultarSql($sql);

    }elseif ($this->objInfraIBanco instanceof InfraOracle){

      $sql = 'select lower(table_name) as table_name, lower(constraint_name) as constraint_name, lower(column_name) as column_name
          from all_cons_columns
          where lower(owner) =\''.strtolower($this->objInfraIBanco->getUsuario()) .'\'
          and lower(table_name)=\''.strtolower($strNomeTabela).'\'';

      if (strtolower($strTipoConstraint)=='primary key' && strtolower($strNomeConstraint)=='primary'){
        $sql .= ' AND lower(constraint_name)=\'pk_'.strtolower($strNomeTabela).'\'';
      }else{
        $sql .= ' AND lower(constraint_name)=\''.strtolower($strNomeConstraint).'\'';
      }


      $ret = $this->objInfraIBanco->consultarSql($sql);

      //die(print_r($ret,true));

    }
    return $ret;
  }

  public function obterIndices($arrTabelasIgnorar = null){
    $ret = null;

    if ($this->objInfraIBanco instanceof InfraSqlServer){

      $sql = 'SELECT distinct lower(T.name) AS table_name, lower(I.name) AS index_name, lower(AC.name) AS column_name
            FROM sys.tables AS T
              INNER JOIN sys.indexes I ON T.object_id = I.object_id  AND I.is_primary_key = 0
              INNER JOIN sys.index_columns IC ON I.object_id = IC.object_id and I.index_id = IC.index_id
              INNER JOIN sys.all_columns AC ON T.object_id = AC.object_id AND IC.column_id = AC.column_id
            WHERE T.is_ms_shipped = 0 AND I.type_desc <> \'HEAP\'';

      $sql .= $this->formatarTabelasIgnorar('lower(T.name)', $arrTabelasIgnorar);

      $sql .=  ' ORDER BY table_name, index_name, column_name';

      $rsIndices = $this->objInfraIBanco->consultarSql($sql);

      $ret = array();
      $numIndices = count($rsIndices);
      for($i=0;$i<$numIndices;$i++){
        $ret[$rsIndices[$i]['table_name']][$rsIndices[$i]['index_name']][] = $rsIndices[$i]['column_name'];
      }

    }elseif ($this->objInfraIBanco instanceof InfraMySql){

      $rsTabelas = $this->obterTabelas(null,$arrTabelasIgnorar);
      $arrIndices = array();
      foreach($rsTabelas as $tabela){
        $rsIndices = $this->objInfraIBanco->consultarSql('show indexes from '.$tabela['table_name']);
        $arrIndices = array_merge($arrIndices,$rsIndices);
      }

      $rsConstraints = $this->obterConstraints(null,$arrTabelasIgnorar);
      $ret = array();
      for($i=0;$i<count($arrIndices);$i++){
        if (strtolower($arrIndices[$i]['Key_name'])!='primary'){
          for($j=0;$j<count($rsConstraints);$j++){
            if (strtolower($arrIndices[$i]['Key_name'])==strtolower($rsConstraints[$j]['constraint_name'])){
              break;
            }
          }
          if ($j==count($rsConstraints)){
            $ret[strtolower($arrIndices[$i]['Table'])][strtolower($arrIndices[$i]['Key_name'])][] = strtolower($arrIndices[$i]['Column_name']);
          }
        }
      }
    }elseif ($this->objInfraIBanco instanceof InfraOracle){

      $sql = 'select lower(ai.table_name) as table_name, lower(ai.index_name) as index_name, lower(aic.column_name) as column_name
              from all_indexes ai, all_ind_columns aic
              where ai.table_name=aic.table_name and ai.index_name=aic.index_name
      		    and lower(ai.index_name) not like \'pk_%\' and lower(ai.index_name) not like \'fk_%\'
              and lower(ai.owner)=\''.strtolower($this->objInfraIBanco->getUsuario()).'\'';

      $sql .= $this->formatarTabelasIgnorar('lower(ai.table_name)', $arrTabelasIgnorar);

      $rsIndices = $this->objInfraIBanco->consultarSql($sql);

      $ret = array();
      $numIndices = count($rsIndices);
      for($i=0;$i<$numIndices;$i++){
        $ret[$rsIndices[$i]['table_name']][$rsIndices[$i]['index_name']][] = $rsIndices[$i]['column_name'];
      }
    }
    return $ret;
  }

  public function obterSequencias($arrTabelasIgnorar = null){
    $ret = '';
    if ($this->objInfraIBanco instanceof InfraSqlServer){

      $sql = 'select lower(name) as table_name, ident_current(name) as current_value
              from sys.tables
              where lower(name) like \'seq_%\'';

      $sql .= $this->formatarTabelasIgnorar('lower(name)', $arrTabelasIgnorar);

      $sql .= ' order by name asc';

      $ret = $this->objInfraIBanco->consultarSql($sql);

    }elseif ($this->objInfraIBanco instanceof InfraMySql){

      $sql = 'select lower(table_name) as table_name, auto_increment as current_value
              from information_schema.tables
              where lower(table_type) = \'base table\'
              and lower(table_schema)=\''.strtolower($this->objInfraIBanco->getBanco()).'\'
              and lower(table_name) like \'seq_%\'';

      $sql .= $this->formatarTabelasIgnorar('lower(table_name)',$arrTabelasIgnorar);

      $sql .= ' order by table_name asc';

      $ret = $this->objInfraIBanco->consultarSql($sql);

    }else if ($this->objInfraIBanco instanceof InfraOracle){

      $sql = 'select lower(sequence_name) as table_name, last_number as current_value
              from all_sequences
              where sequence_owner = \''.$this->objInfraIBanco->getUsuario().'\'
              and lower(sequence_name) like \'seq_%\'';

      $sql .= $this->formatarTabelasIgnorar('lower(sequence_name)',$arrTabelasIgnorar);

      $sql .= ' order by table_name asc';

      $ret = $this->objInfraIBanco->consultarSql($sql);

    }

    return $ret;
  }

  public function obterMaxIdTabelaSequencia($strNomeSequencia){

    $ret = null;

    try{
      if ($this->objInfraIBanco instanceof InfraSqlServer){
        $ret = $this->objInfraIBanco->consultarSql('SELECT IDENT_CURRENT(\''.$strNomeSequencia.'\') as ultimo');
        $ret = $ret[0]['ultimo']+1;
      }elseif ($this->objInfraIBanco instanceof InfraMySql){
        $ret = $this->objInfraIBanco->consultarSql('SHOW TABLE STATUS LIKE \''.$strNomeSequencia.'\'');
        $ret = $ret[0]['Auto_increment'];
      }elseif ($this->objInfraIBanco instanceof InfraOracle){
        $ret = $this->objInfraIBanco->consultarSql('SELECT last_number FROM all_sequences WHERE lower(sequence_owner) = \''.strtolower($this->objInfraIBanco->getUsuario()).'\' AND lower(sequence_name) = \''.$strNomeSequencia.'\'');
        $ret = $ret[0]['last_number'];
      }
    }catch(Exception $e){$ret = '[erro]';}

    return $ret;
  }

  public function obterMaxIdTabela($strNomeTabela){

    $ret = null;

    try{

      $sql = 'select max(id_'.$strNomeTabela.') as maximo from ';

      if ($this->objInfraIBanco instanceof InfraOracle){
        $sql .= $this->objInfraIBanco->getUsuario().'.';
      }

      $sql .= $strNomeTabela;

      $ret = $this->objInfraIBanco->consultarSql($sql);

    }catch(Exception $e){
      //throw new InfraException('Erro obtendo max(id) da tabela: '.$strNomeTabela,$e);
      return array(0 => array('maximo' => 'erro'));
    }

    return $ret;

  }

  private function formatarTabelasIgnorar($strCampo, $arrTabelasIgnorar){
    $ret = '';
    if ($arrTabelasIgnorar != null) {
      foreach ($arrTabelasIgnorar as $strTabela) {

        $strTabela = trim($strTabela);

        if ($strTabela != '') {
          if ($ret != '') {
            $ret .= ',';
          }
          $ret .= '\'' . $strTabela . '\',\'seq_'.$strTabela.'\'';
        }
      }

      if ($ret != ''){
        $ret = ' and '.$strCampo.' not in ('.$ret.')';
      }
    }
    return $ret;
  }

  public function tipoNumero(){
    if ($this->objInfraIBanco instanceof InfraMySql){
      return 'integer';
    }else if ($this->objInfraIBanco instanceof InfraSqlServer){
      return 'integer';
    }else if ($this->objInfraIBanco instanceof InfraOracle){
      return 'number(*,0)';
    }
  }

  public function tipoNumeroGrande(){
    if ($this->objInfraIBanco instanceof InfraMySql){
      return 'bigint';
    }else if ($this->objInfraIBanco instanceof InfraSqlServer){
      return 'bigint';
    }else if ($this->objInfraIBanco instanceof InfraOracle){
      return 'number(*,0)';
    }
  }

  public function tipoNumeroDecimal($numDigitosTotal, $numDigitosDecimais){
    if ($this->objInfraIBanco instanceof InfraMySql){
      return 'numeric('.$numDigitosTotal.','.$numDigitosDecimais.')';
    }else if ($this->objInfraIBanco instanceof InfraSqlServer){
      return 'numeric('.$numDigitosTotal.','.$numDigitosDecimais.')';
    }else if ($this->objInfraIBanco instanceof InfraOracle){
      return 'number('.$numDigitosTotal.','.$numDigitosDecimais.')';
    }
  }

  public function tipoTextoFixo($numTamanho){
    if ($this->objInfraIBanco instanceof InfraMySql){
      return 'char('.$numTamanho.')';
    }else if ($this->objInfraIBanco instanceof InfraSqlServer){
      return 'char('.$numTamanho.')';
    }else if ($this->objInfraIBanco instanceof InfraOracle){
      return 'char('.$numTamanho.' byte)';
    }
  }

  public function tipoTextoVariavel($numTamanho){
    if ($this->objInfraIBanco instanceof InfraMySql){
      return 'varchar('.$numTamanho.')';
    }else if ($this->objInfraIBanco instanceof InfraSqlServer){
      return 'varchar('.$numTamanho.')';
    }else if ($this->objInfraIBanco instanceof InfraOracle){
      return 'varchar2('.$numTamanho.' byte)';
    }
  }

  public function tipoTextoGrande(){
    if ($this->objInfraIBanco instanceof InfraMySql){
      return 'longtext';
    }else if ($this->objInfraIBanco instanceof InfraSqlServer){
      return 'varchar(max)';
    }else if ($this->objInfraIBanco instanceof InfraOracle){
      return 'clob';
    }
  }

  public function tipoDataHora(){
    if ($this->objInfraIBanco instanceof InfraMySql){
      return 'datetime';
    }else if ($this->objInfraIBanco instanceof InfraSqlServer){
      return 'datetime';
    }else if ($this->objInfraIBanco instanceof InfraOracle){
      return 'date';
    }
  }

  public function funcSubstring(){
    if ($this->objInfraIBanco instanceof InfraMySql){
      return 'substring';
    }else if ($this->objInfraIBanco instanceof InfraSqlServer){
      return 'substring';
    }else if ($this->objInfraIBanco instanceof InfraOracle){
      return 'substr';
    }
  }

  public function alterarColuna($strTabela, $strColuna, $strTipo, $strNull){
    $sql = 'alter table '.$strTabela.' ';
    if ($this->objInfraIBanco instanceof InfraMySql){
      $sql .= 'modify column';
    }else if ($this->objInfraIBanco instanceof InfraSqlServer){
      $sql .= 'alter column';
    }else if ($this->objInfraIBanco instanceof InfraOracle){
      $sql .= 'modify';
    }
    $sql .= ' '.$strColuna.' '.$strTipo.' '.$this->getOptionNull($strTabela, $strColuna,$strNull);
    return $this->objInfraIBanco->executarSql($sql);
  }

  public function adicionarColuna($strTabela, $strColuna, $strTipo, $strNull){
    $sql = 'alter table '.$strTabela.' ';
    if ($this->objInfraIBanco instanceof InfraMySql){
      $sql .= 'add column';
    }else if ($this->objInfraIBanco instanceof InfraSqlServer){
      $sql .= 'add';
    }else if ($this->objInfraIBanco instanceof InfraOracle){
      $sql .= 'add';
    }
    $sql .= ' '.$strColuna.' '.$strTipo.' '.$strNull;
    return $this->objInfraIBanco->executarSql($sql);
  }

  public function excluirColuna($strTabela, $strColuna){
    $sql = 'alter table '.$strTabela.' drop column '.$strColuna;
    return $this->objInfraIBanco->executarSql($sql);
  }

  public function adicionarChavePrimaria($strTabela, $strNomePK, $arrCampos){
    $this->objInfraIBanco->executarSql('alter table '.$strTabela.' add constraint '.$strNomePK.' primary key ('.implode(',',$arrCampos).')');
  }

  public function adicionarChaveEstrangeira($strNomeFK,$strTabela,$arrCampos,$strTabelaOrigem, $arrCamposOrigem){
    $this->objInfraIBanco->executarSql('alter table '.$strTabela.' add constraint '.$strNomeFK.' foreign key ('.implode(',',$arrCampos).') references '.$strTabelaOrigem.' ('.implode(',',$arrCamposOrigem).')');
  }

  public function excluirChavePrimaria($strTabela, $strPk){
    $sql = 'alter table '.$strTabela.' ';
    if ($this->objInfraIBanco instanceof InfraMySql){
      $sql .= 'drop primary key';
    }else if ($this->objInfraIBanco instanceof InfraSqlServer){
      $sql .= 'drop constraint '.$strPk;
    }else if ($this->objInfraIBanco instanceof InfraOracle){
      $sql .= 'drop constraint '.$strPk;
    }
    $this->objInfraIBanco->executarSql($sql);
  }

  public function excluirChaveEstrangeira($strTabela, $strFk){
    $sql = 'alter table '.$strTabela.' ';
    if ($this->objInfraIBanco instanceof InfraMySql){
      $sql .= 'drop foreign key';
    }else if ($this->objInfraIBanco instanceof InfraSqlServer){
      $sql .= 'drop constraint';
    }else if ($this->objInfraIBanco instanceof InfraOracle){
      $sql .= 'drop constraint';
    }
    $sql .= ' '.$strFk;
    $this->objInfraIBanco->executarSql($sql);
  }

  public function criarIndice($strTabela, $strIndex, $arrColunas, $bolUnique = false){
    $this->objInfraIBanco->executarSql('create '.($bolUnique?'unique':'').' index '.$strIndex.' on '.$strTabela.' ('.implode(',',$arrColunas).')');
  }

  public function excluirIndice($strTabela, $strIndex){
    $sql = 'drop index ';
    if ($this->objInfraIBanco instanceof InfraMySql){
      $sql .= $strIndex.' on '.$strTabela;
    }else if ($this->objInfraIBanco instanceof InfraSqlServer){
      $sql .= $strIndex.' on '.$strTabela;
    }else if ($this->objInfraIBanco instanceof InfraOracle){
      $sql .= $strIndex;
    }
    $this->objInfraIBanco->executarSql($sql);
  }

  private function getOptionNull($strTabela, $strColuna, $strOption){
    $ret = '';
    if ($this->objInfraIBanco instanceof InfraSqlServer || $this->objInfraIBanco instanceof InfraMySql){
      $ret = $strOption;
    }elseif ($this->objInfraIBanco instanceof InfraOracle){
      $strOption = strtolower(str_replace('  ', ' ',trim($strOption)));
      $rs = $this->objInfraIBanco->consultarSql('SELECT nullable  FROM all_tab_columns WHERE lower(table_name)=\''.strtolower($strTabela).'\' AND lower(owner)=\''.strtolower($this->objInfraIBanco->getUsuario()).'\' and lower(column_name)=\''.strtolower($strColuna).'\'');
      if (($strOption=='not null' && $rs[0]['nullable']=='Y') || ($strOption=='null' && $rs[0]['nullable']=='N')){
        $ret = $strOption;
      }
    }
    return $ret;
  }
}
?>