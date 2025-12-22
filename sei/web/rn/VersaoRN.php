<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 07/05/2013 - criado por mga
*
*/

require_once dirname(__FILE__).'/../SEI.php';

class VersaoRN extends InfraRN {

  private $numSeg = 0;

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }

  private function inicializar($strTitulo){

    ini_set('max_execution_time','0');
    ini_set('memory_limit','-1');
    ini_set('mssql.timeout','0');

    InfraDebug::getInstance()->setBolLigado(false);
    InfraDebug::getInstance()->setBolDebugInfra(true);
    InfraDebug::getInstance()->setBolEcho(true);
    InfraDebug::getInstance()->limpar();

    $this->numSeg = InfraUtil::verificarTempoProcessamento();

    $this->logar($strTitulo);
  }

  private function logar($strMsg){
    InfraDebug::getInstance()->gravar($strMsg);
  }

  private function finalizar($strMsg=null, $bolErro){

    if (!$bolErro) {
      $this->numSeg = InfraUtil::verificarTempoProcessamento($this->numSeg);
      $this->logar('TEMPO TOTAL DE EXECUCAO: ' . $this->numSeg . ' s');
    }else{
      $strMsg = 'ERRO: '.$strMsg;
    }

    if ($strMsg!=null){
      $this->logar($strMsg);
    }

    InfraDebug::getInstance()->setBolLigado(false);
    InfraDebug::getInstance()->setBolDebugInfra(false);
    InfraDebug::getInstance()->setBolEcho(false);
    $this->numSeg = 0;
    die;
  }

  protected function atualizarVersaoConectado(){
	  try{

      $this->inicializar('INICIANDO ATUALIZACAO VERSAO SEI '.SEI_VERSAO);

      $objInfraParametro = new InfraParametro(BancoSEI::getInstance());

      $numVersaoInfraRequerida = '1.466';
      if (VERSAO_INFRA != $numVersaoInfraRequerida){
        $this->finalizar('VERSAO DO FRAMEWORK PHP INCOMPATIVEL (VERSAO ATUAL '.VERSAO_INFRA.', VERSAO REQUERIDA '.$numVersaoInfraRequerida.')',true);
      }

      if (!(BancoSEI::getInstance() instanceof InfraMySql) &&
          !(BancoSEI::getInstance() instanceof InfraSqlServer) &&
          !(BancoSEI::getInstance() instanceof InfraOracle)){
        $this->finalizar('BANCO DE DADOS NAO SUPORTADO: '.get_parent_class(BancoSEI::getInstance()),true);
      }

      $strVersaoAtual = $objInfraParametro->getValor('SEI_VERSAO', false);

      if (InfraString::isBolVazia($strVersaoAtual)){
        $this->finalizar('VERSAO ATUAL NAO IDENTIFICADA',true);
      }

      if (substr($strVersaoAtual,0,3) == '3.0') {
        $this->finalizar('VERSAO JA CONSTA COMO ATUALIZADA',true);
      }

      if (substr($strVersaoAtual,0,3) != '2.6') {
        $this->finalizar('VERSAO INCOMPATIVEL PARA ATUALIZACAO',true);
      }

      $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());

      if (count($objInfraMetaBD->obterTabelas('sei_teste'))==0){
        BancoSEI::getInstance()->executarSql('CREATE TABLE sei_teste (id '.$objInfraMetaBD->tipoNumero().' null)');
      }

      BancoSEI::getInstance()->executarSql('DROP TABLE sei_teste');

      if (BancoSEI::getInstance() instanceof InfraMySql){
        $this->atualizarSequenciasMySql();
      }

      $objInfraMetaBD->adicionarColuna('infra_log','sta_tipo',$objInfraMetaBD->tipoTextoFixo(1),'null');
      BancoSEI::getInstance()->executarSql('update infra_log set sta_tipo=\'E\'');
      $objInfraMetaBD->alterarColuna('infra_log','sta_tipo',$objInfraMetaBD->tipoTextoFixo(1),'not null');
      $objInfraMetaBD->criarIndice('infra_log','i01_infra_log',array('sta_tipo','dth_log'));

      $this->logar('ATUALIZANDO PARAMETROS...');
      $rs = BancoSEI::getInstance()->consultarSql('select nome from infra_parametro where nome like \'%ID_TIPO_CONTEXTO%\'');
      foreach($rs as $item){
        BancoSEI::getInstance()->executarSql('update infra_parametro set nome=\''.str_replace('ID_TIPO_CONTEXTO','ID_TIPO_CONTATO',$item['nome']).'\' where nome=\''.$item['nome'].'\'');
      }

      $rs = BancoSEI::getInstance()->consultarSql('select nome, valor from infra_parametro where nome like \'%_ID_TIPO_CONTATO_UNIDADES\'');
      foreach($rs as $item){
        BancoSEI::getInstance()->executarSql('update tipo_contexto_contato set nome=\'Unidades '.str_replace('_ID_TIPO_CONTATO_UNIDADES','',$item['nome']).'\', descricao=\'Unidades '.str_replace('_ID_TIPO_CONTATO_UNIDADES','',$item['nome']).'\' where id_tipo_contexto_contato='.$item['valor']);
      }

      $rs = BancoSEI::getInstance()->consultarSql('select nome, valor from infra_parametro where nome like \'%_ID_TIPO_CONTATO_USUARIOS\'');
      foreach($rs as $item){
        BancoSEI::getInstance()->executarSql('update tipo_contexto_contato set nome=\'Usuários '.str_replace('_ID_TIPO_CONTATO_USUARIOS','',$item['nome']).'\',descricao=\'Usuários '.str_replace('_ID_TIPO_CONTATO_USUARIOS','',$item['nome']).'\' where id_tipo_contexto_contato='.$item['valor']);
      }

      $rs = BancoSEI::getInstance()->consultarSql('select nome, valor from infra_parametro where nome like \'%_ID_TIPO_CONTATO_USUARIOS_EXTERNOS\'');
      foreach($rs as $item){
        BancoSEI::getInstance()->executarSql('update tipo_contexto_contato set nome=\'Usuários Externos '.str_replace('_ID_TIPO_CONTATO_USUARIOS_EXTERNOS','',$item['nome']).'\',descricao=\'Usuários Externos '.str_replace('_ID_TIPO_CONTATO_USUARIOS_EXTERNOS','',$item['nome']).'\' where id_tipo_contexto_contato='.$item['valor']);
      }

      BancoSEI::getInstance()->executarSql('update infra_parametro set nome=\'SEI_HABILITAR_AUTENTICACAO_DOCUMENTO_EXTERNO\' where nome=\'SEI_HABILITAR_ASSINATURA_DOCUMENTO_EXTERNO\'');


      $rs = BancoSEI::getInstance()->consultarSql('select count(*) as total from infra_parametro where nome = \'SEI_MSG_FORMULARIO_OUVIDORIA\'');
      if ($rs[0]['total']==0) {
        BancoSEI::getInstance()->executarSql('insert into infra_parametro (nome, valor) values (\'SEI_MSG_FORMULARIO_OUVIDORIA\',\'\')');
      }

      $rs = BancoSEI::getInstance()->consultarSql('select count(*) as total from infra_parametro where nome = \'SEI_MAX_TAM_MENSAGEM_OUVIDORIA\'');
      if ($rs[0]['total']==0) {
        BancoSEI::getInstance()->executarSql('INSERT INTO infra_parametro (nome,valor) VALUES (\'SEI_MAX_TAM_MENSAGEM_OUVIDORIA\',\'2000\')');
      }

      $rs = BancoSEI::getInstance()->consultarSql('select count(*) as total from infra_parametro where nome = \'SEI_NUM_PAGINACAO_CONTROLE_PROCESSOS\'');
      if ($rs[0]['total']==0) {
        BancoSEI::getInstance()->executarSql('INSERT INTO infra_parametro (nome,valor) VALUES (\'SEI_NUM_PAGINACAO_CONTROLE_PROCESSOS\',\'100\')');
      }

      BancoSEI::getInstance()->executarSql('INSERT INTO infra_parametro (nome,valor) VALUES (\'SEI_TIPO_ASSINATURA_INTERNA\',\'1\')');
      BancoSEI::getInstance()->executarSql('INSERT INTO infra_parametro (nome,valor) VALUES (\'SEI_TIPO_AUTENTICACAO_INTERNA\',\'1\')');

      BancoSEI::getInstance()->executarSql('delete from infra_parametro where nome=\'ID_MODELO_BASE_CONHECIMENTO\'');
      BancoSEI::getInstance()->executarSql('delete from infra_parametro where nome=\'SEI_TAM_MB_ANEXO_EMAIL\'');


      $this->logar('ATUALIZANDO TAREFAS E EMAILS DO SISTEMA...');
      $objInfraMetaBD->adicionarColuna('tarefa','id_tarefa_modulo',$objInfraMetaBD->tipoTextoVariavel(50),'null');

      if (BancoSEI::getInstance() instanceof InfraSqlServer){
        $objInfraMetaBD->criarIndice('tarefa', 'i01_tarefa', array('id_tarefa_modulo'));
      }else{
        $objInfraMetaBD->criarIndice('tarefa', 'i01_tarefa', array('id_tarefa_modulo'),true);
      }


      $rs = BancoSEI::getInstance()->consultarSql('select max(id_tarefa) as total from tarefa where id_tarefa > 999');
      $numMaxId = $rs[0]['total'];
      if ($numMaxId==null){
        $numMaxId = 1000;
      }
      BancoSEI::getInstance()->criarSequencialNativa('seq_tarefa', $numMaxId+1);

      $objInfraMetaBD->adicionarColuna('email_sistema','id_email_sistema_modulo',$objInfraMetaBD->tipoTextoVariavel(50),'null');

      if (BancoSEI::getInstance() instanceof InfraSqlServer){
        $objInfraMetaBD->criarIndice('email_sistema','i01_email_sistema',array('id_email_sistema_modulo'));
      }else{
        $objInfraMetaBD->criarIndice('email_sistema','i01_email_sistema',array('id_email_sistema_modulo'),true);
      }


      $rs = BancoSEI::getInstance()->consultarSql('select max(id_email_sistema) as total from email_sistema where id_email_sistema > 999');
      $numMaxId = $rs[0]['total'];
      if ($numMaxId==null){
        $numMaxId = 1000;
      }
      BancoSEI::getInstance()->criarSequencialNativa('seq_email_sistema', $numMaxId+1);

      $this->logar('ATUALIZANDO E-MAIL...');
      $numIdSerieEmail = $objInfraParametro->getValor('ID_SERIE_EMAIL', false);

      if (!InfraString::isBolVazia($numIdSerieEmail)){
        BancoSEI::getInstance()->executarSql('update documento set conteudo = replace(conteudo,'.BancoSEI::getInstance()->formatarGravacaoStr('nome="Para" tipo="CCO"').','.BancoSEI::getInstance()->formatarGravacaoStr('nome="Cco"').') where id_serie='.$numIdSerieEmail.' and sin_formulario=\'S\' and conteudo like '.BancoSEI::getInstance()->formatarGravacaoStr('%nome="Para" tipo="CCO"%'));
        BancoSEI::getInstance()->executarSql('update documento set conteudo = replace(conteudo,'.BancoSEI::getInstance()->formatarGravacaoStr('nome="Para" tipo="NORMAL"').','.BancoSEI::getInstance()->formatarGravacaoStr('nome="Para"').') where id_serie='.$numIdSerieEmail.' and sin_formulario=\'S\' and conteudo like '.BancoSEI::getInstance()->formatarGravacaoStr('%nome="Para" tipo="NORMAL"%'));
      }

      $this->logar('MIGRANDO DADOS DE DOCUMENTOS...');

      BancoSEI::getInstance()->executarSql('CREATE TABLE documento_conteudo (
 id_documento         '.$objInfraMetaBD->tipoNumeroGrande().'  NOT NULL ,
 conteudo             '.$objInfraMetaBD->tipoTextoGrande().'  NULL ,
 conteudo_assinatura  '.$objInfraMetaBD->tipoTextoGrande().'  NULL ,
 crc_assinatura       '.$objInfraMetaBD->tipoTextoFixo(8).'  NULL ,
 qr_code_assinatura   '.$objInfraMetaBD->tipoTextoGrande().'  NULL
)');

      $objInfraMetaBD->adicionarChavePrimaria('documento_conteudo','pk_documento_conteudo',array('id_documento'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_doc_conteudo_documento','documento_conteudo',array('id_documento'),'documento',array('id_documento'));

      $this->migrarDadosDocumentos();

      if (BancoSEI::getInstance() instanceof InfraMySql) {
        BancoSEI::getInstance()->executarSql('optimize table documento');
      }else if (BancoSEI::getInstance() instanceof InfraOracle){
        BancoSEI::getInstance()->executarSql('alter table documento enable row movement');
        BancoSEI::getInstance()->executarSql('alter table documento shrink space');
      }else if (BancoSEI::getInstance() instanceof InfraSqlServer){
        BancoSEI::getInstance()->executarSql('alter index all on documento rebuild');
      }

      $objInfraMetaBD->excluirColuna('documento','conteudo');
      $objInfraMetaBD->excluirColuna('documento','conteudo_assinatura');
      $objInfraMetaBD->excluirColuna('documento','crc_assinatura');
      $objInfraMetaBD->excluirColuna('documento','qr_code_assinatura');

      $this->logar('ADICIONANDO ID_ORIGEM EM USUARIO E UNIDADE...');
      $objInfraMetaBD->adicionarColuna('usuario','id_origem', $objInfraMetaBD->tipoTextoVariavel(50), 'null');
      BancoSEI::getInstance()->executarSql('update usuario set id_origem=id_pessoa_rh');
      $objInfraMetaBD->excluirColuna('usuario','id_pessoa_rh');
      $objInfraMetaBD->adicionarColuna('unidade','id_origem', $objInfraMetaBD->tipoTextoVariavel(50), 'null');

      $this->fixCredencialAssinatura();
      $this->fixSenhaBcrypt();

      $this->logar('OTMIZANDO TABELA DE ACESSOS...');
      if (BancoSEI::getInstance() instanceof InfraMySql) {
        $objInfraMetaBD->excluirChaveEstrangeira('acesso','fk_acesso_usuario');
        $objInfraMetaBD->excluirChaveEstrangeira('acesso','fk_acesso_unidade');
        $objInfraMetaBD->excluirChaveEstrangeira('acesso','fk_acesso_protocolo');
      }

      $objInfraMetaBD->excluirIndice('acesso','i01_acesso');
      $objInfraMetaBD->excluirIndice('acesso','i02_acesso');
      $objInfraMetaBD->excluirIndice('acesso','i03_acesso');
      $objInfraMetaBD->excluirIndice('acesso','i04_acesso');

      if (BancoSEI::getInstance() instanceof InfraMySql) {
        $objInfraMetaBD->adicionarChaveEstrangeira('fk_acesso_usuario','acesso',array('id_usuario'),'usuario',array('id_usuario'));
        $objInfraMetaBD->adicionarChaveEstrangeira('fk_acesso_unidade','acesso',array('id_unidade'),'unidade',array('id_unidade'));
        $objInfraMetaBD->adicionarChaveEstrangeira('fk_acesso_protocolo','acesso',array('id_protocolo'),'protocolo',array('id_protocolo'));
      }

      $objInfraMetaBD->criarIndice('acesso','i01_acesso',array('id_unidade','id_usuario','id_protocolo','sta_tipo'));

      BancoSEI::getInstance()->executarSql('delete from acesso where sta_tipo in (\'R\',\'S\',\'A\') and exists (select id_protocolo from protocolo where acesso.id_protocolo=protocolo.id_protocolo and protocolo.sta_protocolo <> \'P\')');

      $this->logar('ATUALIZANDO TABELA DE TAREFAS...');
      BancoSEI::getInstance()->executarSql('update tarefa set sin_permite_processo_fechado=\'S\' where id_tarefa='.TarefaRN::$TI_ALTERACAO_NIVEL_ACESSO_GLOBAL);


      $this->logar('ALTERANDO TABELA DE AUDITORIA PARA IPv6...');
      $objInfraMetaBD->alterarColuna('infra_auditoria','ip',$objInfraMetaBD->tipoTextoVariavel(39),'null');

      $this->logar('REMOVENDO COLUNAS VERSAO_LOCK...');
      $objInfraMetaBD->excluirColuna('procedimento','versao_lock');
      $objInfraMetaBD->excluirColuna('documento','versao_lock');

      $this->logar('CRIANDO TABELAS PARA FORMULARIOS...');
      BancoSEI::getInstance()->executarSql('CREATE TABLE tipo_formulario (
  id_tipo_formulario '.$objInfraMetaBD->tipoNumero().'  NOT NULL ,
  nome '.$objInfraMetaBD->tipoTextoVariavel(50).' NOT NULL ,
  descricao '.$objInfraMetaBD->tipoTextoVariavel(250).' NULL ,
  sin_ativo '.$objInfraMetaBD->tipoTextoFixo(1).' NOT NULL
)');

      $objInfraMetaBD->adicionarChavePrimaria('tipo_formulario','pk_tipo_formulario',array('id_tipo_formulario'));

      $objInfraMetaBD->adicionarColuna('serie','id_tipo_formulario',$objInfraMetaBD->tipoNumero(),'null');

      $objInfraMetaBD->adicionarChaveEstrangeira('fk_serie_tipo_formulario','serie', array('id_tipo_formulario'), 'tipo_formulario', array('id_tipo_formulario'));

      BancoSEI::getInstance()->criarSequencialNativa('seq_tipo_formulario',1);

      $this->logar('ATUALIZANDO BASES DE CONHECIMENTO...');
      $objInfraMetaBD->adicionarColuna('base_conhecimento','sta_documento',$objInfraMetaBD->tipoTextoFixo(1),'null');
      BancoSEI::getInstance()->executarSql('update base_conhecimento set sta_documento=sta_editor');
      $objInfraMetaBD->alterarColuna('base_conhecimento','sta_documento',$objInfraMetaBD->tipoTextoFixo(1),'not null');
      $objInfraMetaBD->excluirColuna('base_conhecimento','sta_editor');

      $this->logar('ATUALIZANDO CAMPOS DE DOCUMENTO...');
      $objInfraMetaBD->adicionarColuna('documento','id_tipo_formulario',$objInfraMetaBD->tipoNumero(),'null');
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_documento_tipo_formulario','documento', array('id_tipo_formulario'), 'tipo_formulario', array('id_tipo_formulario'));

      $objInfraMetaBD->adicionarColuna('documento','sta_documento',$objInfraMetaBD->tipoTextoFixo(1),'null');
      BancoSEI::getInstance()->executarSql('update documento set sta_documento=sta_editor');
      $objInfraMetaBD->alterarColuna('documento','sta_documento',$objInfraMetaBD->tipoTextoFixo(1),'not null');

      $objInfraMetaBD->excluirColuna('documento','sta_editor');

      BancoSEI::getInstance()->executarSql('update documento set sta_documento=\'A\' where sin_formulario=\'S\'');

      $objInfraMetaBD->excluirColuna('documento','sin_formulario');

      $this->logar('ATUALIZANDO INDICES DE DOCUMENTO...');
      $objInfraMetaBD->excluirIndice('documento','i07_documento');
      $objInfraMetaBD->excluirIndice('documento','ie1_documento');
      $objInfraMetaBD->excluirIndice('documento','ie2_documento');
      $objInfraMetaBD->excluirIndice('documento','ie4_documento');

      $objInfraMetaBD->criarIndice('documento','i02_documento',array('id_documento', 'id_documento_edoc'));
      $objInfraMetaBD->criarIndice('documento','i03_documento',array('id_documento', 'id_serie', 'id_tipo_formulario', 'sta_documento'));

      BancoSEI::getInstance()->executarSql('update documento set sta_documento=\'X\' where exists (select id_protocolo from protocolo where protocolo.id_protocolo=documento.id_documento and protocolo.sta_protocolo=\'R\')');
      BancoSEI::getInstance()->executarSql('update documento set sta_documento=\'E\' where sta_documento=\'N\'');

      $this->logar('CRIANDO TABELAS DE ATRIBUTOS E DOMINIOS...');

      BancoSEI::getInstance()->executarSql('delete from rel_protocolo_atributo');

      $objInfraMetaBD->alterarColuna('rel_protocolo_atributo','valor',$objInfraMetaBD->tipoTextoVariavel(4000),'null');

      BancoSEI::getInstance()->executarSql('drop table aplicabilidade_atributo');

      if (BancoSEI::getInstance() instanceof InfraOracle){
        BancoSEI::getInstance()->executarSql('drop sequence ' . BancoSEI::getInstance()->getUsuario() . '.seq_aplicabilidade_atributo');
      }else {
        BancoSEI::getInstance()->executarSql('drop table seq_aplicabilidade_atributo');
      }

      BancoSEI::getInstance()->executarSql('drop table dominio');

      $objInfraMetaBD->excluirChaveEstrangeira('rel_protocolo_atributo','fk_rel_prot_atributo_atributo');

      BancoSEI::getInstance()->executarSql('drop table atributo');

      BancoSEI::getInstance()->executarSql('CREATE TABLE atributo(
	id_atributo '.$objInfraMetaBD->tipoNumero().' NOT NULL ,
	id_tipo_formulario '.$objInfraMetaBD->tipoNumero().' NOT NULL ,
	nome  '.$objInfraMetaBD->tipoTextoVariavel(50).' NOT NULL ,
	rotulo '.$objInfraMetaBD->tipoTextoVariavel(4000).' NOT NULL ,
	ordem '.$objInfraMetaBD->tipoNumero().' NOT NULL ,
	sta_tipo '.$objInfraMetaBD->tipoTextoVariavel(20).' NOT NULL ,
	tamanho '.$objInfraMetaBD->tipoNumero().' NULL ,
	linhas '.$objInfraMetaBD->tipoNumero().' NULL ,
	decimais '.$objInfraMetaBD->tipoNumero().' NULL ,
	mascara '.$objInfraMetaBD->tipoTextoVariavel(50).' NULL ,
	valor_minimo '.$objInfraMetaBD->tipoTextoVariavel(20).' NULL ,
	valor_maximo '.$objInfraMetaBD->tipoTextoVariavel(20).' NULL ,
	valor_padrao '.$objInfraMetaBD->tipoTextoVariavel(4000).' NULL ,
	sin_obrigatorio '.$objInfraMetaBD->tipoTextoFixo(1).' NOT NULL ,
	sin_ativo '.$objInfraMetaBD->tipoTextoFixo(1).' NOT NULL
)');

      $objInfraMetaBD->adicionarChavePrimaria('atributo','pk_atributo',array('id_atributo'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_atributo_tipo_formulario','atributo', array('id_tipo_formulario'), 'tipo_formulario', array('id_tipo_formulario'));

      BancoSEI::getInstance()->executarSql('CREATE TABLE dominio(
	id_dominio '.$objInfraMetaBD->tipoNumero().' NOT NULL ,
	id_atributo '.$objInfraMetaBD->tipoNumero().' NOT NULL ,
	valor '.$objInfraMetaBD->tipoTextoVariavel(50).' NOT NULL ,
	rotulo '.$objInfraMetaBD->tipoTextoVariavel(100).' NOT NULL ,
	ordem '.$objInfraMetaBD->tipoNumero().' NOT NULL ,
	sin_padrao '.$objInfraMetaBD->tipoTextoFixo(1).' NOT NULL ,
	sin_ativo '.$objInfraMetaBD->tipoTextoFixo(1).' NOT NULL
)');

      $objInfraMetaBD->adicionarChavePrimaria('dominio','pk_dominio',array('id_dominio'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_dominio_atributo','dominio', array('id_atributo'), 'atributo', array('id_atributo'));

      $objInfraMetaBD->adicionarChaveEstrangeira('fk_rel_prot_atributo_atributo','rel_protocolo_atributo', array('id_atributo'), 'atributo', array('id_atributo'));

      $this->logar('ALTERANDO TARJAS DE ASSINATURA...');
      $objInfraMetaBD->adicionarColuna('tarja_assinatura','sin_ativo',$objInfraMetaBD->tipoTextoFixo(1),'null');
      BancoSEI::getInstance()->executarSql('update tarja_assinatura set sin_ativo=\'S\'');
      $objInfraMetaBD->alterarColuna('tarja_assinatura','sin_ativo',$objInfraMetaBD->tipoTextoFixo(1),'not null');

      BancoSEI::getInstance()->criarSequencialNativa('seq_tarja_assinatura',4);

      $objInfraMetaBD->adicionarColuna('tarja_assinatura','sta_tarja_assinatura',$objInfraMetaBD->tipoTextoFixo(1),'null');
      BancoSEI::getInstance()->executarSql('update tarja_assinatura set sta_tarja_assinatura=sta_forma_autenticacao');
      BancoSEI::getInstance()->executarSql('update tarja_assinatura set sta_tarja_assinatura=\'V\' where sta_tarja_assinatura is null');
      $objInfraMetaBD->alterarColuna('tarja_assinatura','sta_tarja_assinatura',$objInfraMetaBD->tipoTextoFixo(1),'not null');
      $objInfraMetaBD->excluirColuna('tarja_assinatura','sta_forma_autenticacao');
      $objInfraMetaBD->excluirColuna('tarja_assinatura','descricao');

      $objTarjaAssinaturaDTO = new TarjaAssinaturaDTO();
      $objTarjaAssinaturaDTO->setNumIdTarjaAssinatura(null);
      $objTarjaAssinaturaDTO->setStrStaTarjaAssinatura('A');
      $objTarjaAssinaturaDTO->setStrTexto('<hr style="margin: 0 0 4px 0;" /><table><tr><td>@logo_assinatura@</td><td><p style="margin:0;text-align: left; font-size:11pt;font-family: Times New Roman;">Autenticado eletronicamente por <b>@nome_assinante@</b>, <b>@tratamento_assinante@</b>, em @data_assinatura@, às @hora_assinatura@, conforme art. 1º, III, "a", da Lei 11.419/2006, a partir de @tipo_conferencia@.<br />Nº de Série do Certificado: @numero_serie_certificado_digital@</p></td></tr></table>');
      $objTarjaAssinaturaDTO->setStrLogo('iVBORw0KGgoAAAANSUhEUgAAAFkAAAA8CAMAAAA67OZ0AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAADTtpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+Cjx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDQuMi4yLWMwNjMgNTMuMzUyNjI0LCAyMDA4LzA3LzMwLTE4OjEyOjE4ICAgICAgICAiPgogPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4KICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIgogICAgeG1sbnM6eG1wUmlnaHRzPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvcmlnaHRzLyIKICAgIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyIKICAgIHhtbG5zOklwdGM0eG1wQ29yZT0iaHR0cDovL2lwdGMub3JnL3N0ZC9JcHRjNHhtcENvcmUvMS4wL3htbG5zLyIKICAgeG1wUmlnaHRzOldlYlN0YXRlbWVudD0iIgogICBwaG90b3Nob3A6QXV0aG9yc1Bvc2l0aW9uPSIiPgogICA8ZGM6cmlnaHRzPgogICAgPHJkZjpBbHQ+CiAgICAgPHJkZjpsaSB4bWw6bGFuZz0ieC1kZWZhdWx0Ii8+CiAgICA8L3JkZjpBbHQ+CiAgIDwvZGM6cmlnaHRzPgogICA8ZGM6Y3JlYXRvcj4KICAgIDxyZGY6U2VxPgogICAgIDxyZGY6bGk+QWxiZXJ0byBCaWdhdHRpPC9yZGY6bGk+CiAgICA8L3JkZjpTZXE+CiAgIDwvZGM6Y3JlYXRvcj4KICAgPGRjOnRpdGxlPgogICAgPHJkZjpBbHQ+CiAgICAgPHJkZjpsaSB4bWw6bGFuZz0ieC1kZWZhdWx0Ii8+CiAgICA8L3JkZjpBbHQ+CiAgIDwvZGM6dGl0bGU+CiAgIDx4bXBSaWdodHM6VXNhZ2VUZXJtcz4KICAgIDxyZGY6QWx0PgogICAgIDxyZGY6bGkgeG1sOmxhbmc9IngtZGVmYXVsdCIvPgogICAgPC9yZGY6QWx0PgogICA8L3htcFJpZ2h0czpVc2FnZVRlcm1zPgogICA8SXB0YzR4bXBDb3JlOkNyZWF0b3JDb250YWN0SW5mbwogICAgSXB0YzR4bXBDb3JlOkNpQWRyRXh0YWRyPSIiCiAgICBJcHRjNHhtcENvcmU6Q2lBZHJDaXR5PSIiCiAgICBJcHRjNHhtcENvcmU6Q2lBZHJSZWdpb249IiIKICAgIElwdGM0eG1wQ29yZTpDaUFkclBjb2RlPSIiCiAgICBJcHRjNHhtcENvcmU6Q2lBZHJDdHJ5PSIiCiAgICBJcHRjNHhtcENvcmU6Q2lUZWxXb3JrPSIiCiAgICBJcHRjNHhtcENvcmU6Q2lFbWFpbFdvcms9IiIKICAgIElwdGM0eG1wQ29yZTpDaVVybFdvcms9IiIvPgogIDwvcmRmOkRlc2NyaXB0aW9uPgogPC9yZGY6UkRGPgo8L3g6eG1wbWV0YT4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgIAo8P3hwYWNrZXQgZW5kPSJ3Ij8+RO84nQAAAwBQTFRFamts+fn5mp6hc3Nz9fX1U1NTS0tKnaGk6unqzM3P7e3u8fHxuLm7/Pz8lZmc2dnZxcXGWlpavr29wsLCp6eniYmKhYaGZWZmkpaZ0dHS5eXlkZGSrq2utbW2XV1d4uHhfX1+sbGy1dXW3d3dqampgYGCjY2OyMnKYWJi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');
      $objTarjaAssinaturaDTO->setStrSinAtivo('S');

      $objTarjaAssinaturaBD = new TarjaAssinaturaBD(BancoSEI::getInstance());
      $objTarjaAssinaturaBD->cadastrar($objTarjaAssinaturaDTO);


      $objTarjaAssinaturaDTO = new TarjaAssinaturaDTO();
      $objTarjaAssinaturaDTO->setNumIdTarjaAssinatura(null);
      $objTarjaAssinaturaDTO->setStrStaTarjaAssinatura('H');
      $objTarjaAssinaturaDTO->setStrTexto('<hr style="margin: 0 0 4px 0;" /><table><tr><td>@logo_assinatura@</td><td><p style="margin:0;text-align: left; font-size:11pt;font-family: Times New Roman;">Autenticado eletronicamente por <b>@nome_assinante@</b>, <b>@tratamento_assinante@</b>, em @data_assinatura@, às @hora_assinatura@, conforme art. 1º, III, "b", da Lei 11.419/2006, a partir de @tipo_conferencia@.</p></td></tr></table>');
      $objTarjaAssinaturaDTO->setStrLogo ('iVBORw0KGgoAAAANSUhEUgAAAFkAAAA8CAMAAAA67OZ0AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAADTtpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+Cjx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDQuMi4yLWMwNjMgNTMuMzUyNjI0LCAyMDA4LzA3LzMwLTE4OjEyOjE4ICAgICAgICAiPgogPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4KICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIgogICAgeG1sbnM6eG1wUmlnaHRzPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvcmlnaHRzLyIKICAgIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyIKICAgIHhtbG5zOklwdGM0eG1wQ29yZT0iaHR0cDovL2lwdGMub3JnL3N0ZC9JcHRjNHhtcENvcmUvMS4wL3htbG5zLyIKICAgeG1wUmlnaHRzOldlYlN0YXRlbWVudD0iIgogICBwaG90b3Nob3A6QXV0aG9yc1Bvc2l0aW9uPSIiPgogICA8ZGM6cmlnaHRzPgogICAgPHJkZjpBbHQ+CiAgICAgPHJkZjpsaSB4bWw6bGFuZz0ieC1kZWZhdWx0Ii8+CiAgICA8L3JkZjpBbHQ+CiAgIDwvZGM6cmlnaHRzPgogICA8ZGM6Y3JlYXRvcj4KICAgIDxyZGY6U2VxPgogICAgIDxyZGY6bGk+QWxiZXJ0byBCaWdhdHRpPC9yZGY6bGk+CiAgICA8L3JkZjpTZXE+CiAgIDwvZGM6Y3JlYXRvcj4KICAgPGRjOnRpdGxlPgogICAgPHJkZjpBbHQ+CiAgICAgPHJkZjpsaSB4bWw6bGFuZz0ieC1kZWZhdWx0Ii8+CiAgICA8L3JkZjpBbHQ+CiAgIDwvZGM6dGl0bGU+CiAgIDx4bXBSaWdodHM6VXNhZ2VUZXJtcz4KICAgIDxyZGY6QWx0PgogICAgIDxyZGY6bGkgeG1sOmxhbmc9IngtZGVmYXVsdCIvPgogICAgPC9yZGY6QWx0PgogICA8L3htcFJpZ2h0czpVc2FnZVRlcm1zPgogICA8SXB0YzR4bXBDb3JlOkNyZWF0b3JDb250YWN0SW5mbwogICAgSXB0YzR4bXBDb3JlOkNpQWRyRXh0YWRyPSIiCiAgICBJcHRjNHhtcENvcmU6Q2lBZHJDaXR5PSIiCiAgICBJcHRjNHhtcENvcmU6Q2lBZHJSZWdpb249IiIKICAgIElwdGM0eG1wQ29yZTpDaUFkclBjb2RlPSIiCiAgICBJcHRjNHhtcENvcmU6Q2lBZHJDdHJ5PSIiCiAgICBJcHRjNHhtcENvcmU6Q2lUZWxXb3JrPSIiCiAgICBJcHRjNHhtcENvcmU6Q2lFbWFpbFdvcms9IiIKICAgIElwdGM0eG1wQ29yZTpDaVVybFdvcms9IiIvPgogIDwvcmRmOkRlc2NyaXB0aW9uPgogPC9yZGY6UkRGPgo8L3g6eG1wbWV0YT4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgIAo8P3hwYWNrZXQgZW5kPSJ3Ij8+RO84nQAAAwBQTFRFamts+fn5mp6hc3Nz9fX1U1NTS0tKnaGk6unqzM3P7e3u8fHxuLm7/Pz8lZmc2dnZxcXGWlpavr29wsLCp6eniYmKhYaGZWZmkpaZ0dHS5eXlkZGSrq2utbW2XV1d4uHhfX1+sbGy1dXW3d3dqampgYGCjY2OyMnKYWJi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');
      $objTarjaAssinaturaDTO->setStrSinAtivo('S');

      $objTarjaAssinaturaBD->cadastrar($objTarjaAssinaturaDTO);

      BancoSEI::getInstance()->executarSql('insert into tarefa (id_tarefa,nome,sin_historico_resumido,sin_historico_completo,sin_fechar_andamentos_abertos,sin_lancar_andamento_fechado,sin_permite_processo_fechado) values (\'115\',\'Autenticado Documento @DOCUMENTO@ por @USUARIO@\',\'N\',\'S\',\'S\',\'N\',\'N\')');
      BancoSEI::getInstance()->executarSql('insert into tarefa (id_tarefa,nome,sin_historico_resumido,sin_historico_completo,sin_fechar_andamentos_abertos,sin_lancar_andamento_fechado,sin_permite_processo_fechado) values (\'116\',\'Cancelamento de autenticação do documento @DOCUMENTO@\',\'N\',\'S\',\'S\',\'N\',\'N\')');
      BancoSEI::getInstance()->executarSql('insert into tarefa (id_tarefa,nome,sin_historico_resumido,sin_historico_completo,sin_fechar_andamentos_abertos,sin_lancar_andamento_fechado,sin_permite_processo_fechado) values (\'117\',\'Cancelamento de credencial por Coordenador de Acervo do usuário @USUARIO@ na unidade\',\'S\',\'S\',\'N\',\'S\',\'S\')');
      BancoSEI::getInstance()->executarSql('insert into tarefa (id_tarefa,nome,sin_historico_resumido,sin_historico_completo,sin_fechar_andamentos_abertos,sin_lancar_andamento_fechado,sin_permite_processo_fechado) values (\'118\',\'Ativação de credencial por Coordenador de Acervo para o usuário @USUARIO@\',\'S\',\'S\',\'N\',\'N\',\'S\')');
      BancoSEI::getInstance()->executarSql('insert into tarefa (id_tarefa,nome,sin_historico_resumido,sin_historico_completo,sin_fechar_andamentos_abertos,sin_lancar_andamento_fechado,sin_permite_processo_fechado) values (\'119\',\'Ativação de credencial por Coordenador de Acervo para o usuário @USUARIO@ (cassada em @DATA_HORA@)\',\'S\',\'S\',\'S\',\'N\',\'N\')');
      BancoSEI::getInstance()->executarSql('insert into tarefa (id_tarefa,nome,sin_historico_resumido,sin_historico_completo,sin_fechar_andamentos_abertos,sin_lancar_andamento_fechado,sin_permite_processo_fechado) values (\'120\',\'Ativação de credencial por Coordenador de Acervo para o usuário @USUARIO@ (anulada em @DATA_HORA@)\',\'S\',\'S\',\'S\',\'N\',\'N\')');
      BancoSEI::getInstance()->executarSql('insert into tarefa (id_tarefa,nome,sin_historico_resumido,sin_historico_completo,sin_fechar_andamentos_abertos,sin_lancar_andamento_fechado,sin_permite_processo_fechado) values (\'121\',\'Ativação de credencial por Coordenador de Acervo para o usuário @USUARIO@ (renunciada em @DATA_HORA@)\',\'S\',\'S\',\'S\',\'N\',\'N\')');
      BancoSEI::getInstance()->executarSql('insert into tarefa (id_tarefa,nome,sin_historico_resumido,sin_historico_completo,sin_fechar_andamentos_abertos,sin_lancar_andamento_fechado,sin_permite_processo_fechado) values (\'122\',\'Processo bloqueado\',   \'N\',\'S\',\'S\',\'N\',\'N\')');
      BancoSEI::getInstance()->executarSql('insert into tarefa (id_tarefa,nome,sin_historico_resumido,sin_historico_completo,sin_fechar_andamentos_abertos,sin_lancar_andamento_fechado,sin_permite_processo_fechado) values (\'123\',\'Processo desbloqueado\',\'N\',\'S\',\'S\',\'N\',\'N\')');

      $this->logar('ASSOCIANDO ASSINATURAS COM AS TARJAS DE ASSINATURA...');
      $objInfraMetaBD->adicionarColuna('assinatura','id_tarja_assinatura',$objInfraMetaBD->tipoNumero(),'null');
      BancoSEI::getInstance()->executarSql('update assinatura set id_tarja_assinatura=1 where sta_forma_autenticacao=\'C\'');
      BancoSEI::getInstance()->executarSql('update assinatura set id_tarja_assinatura=2 where sta_forma_autenticacao=\'S\'');
      $objInfraMetaBD->alterarColuna('assinatura','id_tarja_assinatura',$objInfraMetaBD->tipoNumero(),'not null');
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_assinatura_tarja_assinatura','assinatura', array('id_tarja_assinatura'), 'tarja_assinatura', array('id_tarja_assinatura'));

      $this->logar('REMOVENDO TABELAS DE PESQUISA EM BANCO...');
      BancoSEI::getInstance()->executarSql('drop table indexacao_base_conhecimento');
      BancoSEI::getInstance()->executarSql('drop table indexacao_protocolo');
      BancoSEI::getInstance()->executarSql('drop table indexacao_publicacao');

      $this->logar('AJUSTANDO INDICES DE ATRIBUTO_ANDAMENTO...');
      $objInfraMetaBD->excluirIndice('atributo_andamento','ie1_atributo_andamento');

      if (BancoSEI::getInstance() instanceof InfraSqlServer){
        $objInfraMetaBD->excluirIndice('atributo_andamento','i01_atributo_andamento');
        $objInfraMetaBD->criarIndice('atributo_andamento','i01_atributo_andamento',array('id_atividade','nome','id_origem'));
      }

      $this->logar('ATUALIZANDO DADOS DE NAVEGADORES...');
      BancoSEI::getInstance()->executarSql('update infra_navegador set versao = \'11.0\' where versao like \'11.0;%\'');
      BancoSEI::getInstance()->executarSql('update infra_navegador set versao = \'4.0\' where versao like \'4.0 %\'');
      BancoSEI::getInstance()->executarSql('update infra_navegador set versao = \'5.1\' where versao like \'5.1 %\'');
      BancoSEI::getInstance()->executarSql('update infra_navegador set versao = \'6.0\' where versao like \'6.0 %\'');

      $this->logar('CRIANDO TABELA DE MONITORAMENTO DE SERVICOS...');
      BancoSEI::getInstance()->executarSql('CREATE TABLE monitoramento_servico (
          id_monitoramento_servico '.$objInfraMetaBD->tipoNumeroGrande().' NOT NULL ,
	id_servico         '.$objInfraMetaBD->tipoNumero().' NOT NULL ,
	operacao             '.$objInfraMetaBD->tipoTextoVariavel(100).'  NOT NULL ,
	tempo_execucao       '.$objInfraMetaBD->tipoNumeroGrande().'  NOT NULL ,
	ip_acesso            '.$objInfraMetaBD->tipoTextoVariavel(39).'  NULL ,
	dth_acesso           '.$objInfraMetaBD->tipoDataHora().'  NOT NULL ,
	servidor             '.$objInfraMetaBD->tipoTextoVariavel(250).'  NULL ,
	user_agent           '.$objInfraMetaBD->tipoTextoVariavel(250).'  NULL
)');

      $objInfraMetaBD->adicionarChavePrimaria('monitoramento_servico','pk_monitoramento_servico',array('id_monitoramento_servico'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_monitoram_servico_servico','monitoramento_servico',array('id_servico'),'servico',array('id_servico'));

      $objInfraMetaBD->criarIndice('monitoramento_servico','i01_monitoramento_servico',array('dth_acesso'));
      $objInfraMetaBD->criarIndice('monitoramento_servico','i02_monitoramento_servico',array('operacao'));

      if (BancoSEI::getInstance() instanceof InfraMySql){
        BancoSEI::getInstance()->executarSql('create table seq_monitoramento_servico (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = 1');
      }else if (BancoSEI::getInstance() instanceof InfraSqlServer){
        BancoSEI::getInstance()->executarSql('create table seq_monitoramento_servico (id bigint identity(1,1), campo char(1) null)');
      }else if (BancoSEI::getInstance() instanceof InfraOracle){
        BancoSEI::getInstance()->criarSequencialNativa('seq_monitoramento_servico', 1);
      }

      $this->logar('ATUALIZANDO GRUPOS DE CONTATOS...');
      $objInfraMetaBD->adicionarColuna('grupo_contato','sin_ativo',$objInfraMetaBD->tipoTextoFixo(1),'null');
      BancoSEI::getInstance()->executarSql('update grupo_contato set sin_ativo=\'S\'');
      $objInfraMetaBD->alterarColuna('grupo_contato','sin_ativo',$objInfraMetaBD->tipoTextoFixo(1),'not null');

      $objInfraMetaBD->adicionarColuna('grupo_contato','sta_tipo',$objInfraMetaBD->tipoTextoFixo(1),'null');
      BancoSEI::getInstance()->executarSql('update grupo_contato set sta_tipo=\'I\' where sin_publico=\'S\'');
      BancoSEI::getInstance()->executarSql('update grupo_contato set sta_tipo=\'U\' where sin_publico=\'N\'');
      $objInfraMetaBD->alterarColuna('grupo_contato','sta_tipo',$objInfraMetaBD->tipoTextoFixo(1),'not null');

      $objInfraMetaBD->excluirColuna('grupo_contato','sin_publico');

      $objInfraMetaBD->alterarColuna('grupo_contato','descricao',$objInfraMetaBD->tipoTextoVariavel(250),'null');

      $this->logar('ATUALIZANDO TABELAS DE CARGOS, TRATAMENTOS E VOCATIVOS...');
      $objInfraMetaBD->alterarColuna('cargo','expressao',$objInfraMetaBD->tipoTextoVariavel(100),'not null');
      $objInfraMetaBD->alterarColuna('tratamento','expressao',$objInfraMetaBD->tipoTextoVariavel(100),'not null');
      $objInfraMetaBD->alterarColuna('vocativo','expressao',$objInfraMetaBD->tipoTextoVariavel(100),'not null');

      $this->logar('ATUALIZANDO CONTATOS E TIPOS DE CONTATOS...');
      $objInfraMetaBD->excluirColuna('contato','palavras_chave');
      $objInfraMetaBD->alterarColuna('contato','endereco',$objInfraMetaBD->tipoTextoVariavel(130),'null');
      $objInfraMetaBD->adicionarColuna('contato','complemento',$objInfraMetaBD->tipoTextoVariavel(130),'null');
      $objInfraMetaBD->alterarColuna('contato','matricula',$objInfraMetaBD->tipoTextoVariavel(10),'null');
      $objInfraMetaBD->alterarColuna('contato','bairro',$objInfraMetaBD->tipoTextoVariavel(70),'null');

      $objInfraMetaBD->adicionarColuna('contato','sta_natureza',$objInfraMetaBD->tipoTextoFixo(1),'null');
      BancoSEI::getInstance()->executarSql('update contato set sta_natureza=\'J\' where id_tipo_contexto_contato in (select id_tipo_contexto_contato from tipo_contexto_contato where sin_contatos=\'S\' or nome like \'Unidades %\' or nome=\'Sistemas\')');
      BancoSEI::getInstance()->executarSql('update contato set sta_natureza=\'F\' where id_tipo_contexto_contato in (select id_tipo_contexto_contato from tipo_contexto_contato where nome like \'Usuários %\' or nome=\'Temporário\' or nome=\'Ouvidoria\')');
      BancoSEI::getInstance()->executarSql('update contato set sta_natureza=\'F\' where sta_natureza is null');
      $objInfraMetaBD->alterarColuna('contato','sta_natureza',$objInfraMetaBD->tipoTextoFixo(1),'not null');

      $objInfraMetaBD->excluirColuna('tipo_contexto_contato','sin_contatos');
      $objInfraMetaBD->excluirColuna('contato','sin_contexto');
      $objInfraMetaBD->excluirColuna('contato','id_pessoa_rh');

      $objInfraMetaBD->adicionarColuna('contato','sin_endereco_associado',$objInfraMetaBD->tipoTextoFixo(1),'null');
      BancoSEI::getInstance()->executarSql('update contato set sin_endereco_associado=sin_endereco_contexto');
      $objInfraMetaBD->alterarColuna('contato','sin_endereco_associado',$objInfraMetaBD->tipoTextoFixo(1),'not null');
      $objInfraMetaBD->excluirColuna('contato','sin_endereco_contexto');

      BancoSEI::getInstance()->executarSql('update contato set sin_endereco_associado=\'N\' where sta_natureza=\'J\'');

      $objInfraMetaBD->adicionarColuna('contato','telefone_fixo',$objInfraMetaBD->tipoTextoVariavel(50),'null');
      BancoSEI::getInstance()->executarSql('update contato set telefone_fixo=telefone');
      $objInfraMetaBD->excluirColuna('contato','telefone');
      $objInfraMetaBD->adicionarColuna('contato','telefone_celular',$objInfraMetaBD->tipoTextoVariavel(25),'null');

      $objInfraMetaBD->excluirColuna('contato','fax');

      $objInfraMetaBD->excluirChaveEstrangeira('contato','fk_contato_carreia');
      $objInfraMetaBD->excluirColuna('contato','id_carreira');
      BancoSEI::getInstance()->executarSql('drop table carreira');

      $objInfraMetaBD->excluirChaveEstrangeira('contato','fk_contato_nivel_funcao');
      $objInfraMetaBD->excluirColuna('contato','id_nivel_funcao');
      BancoSEI::getInstance()->executarSql('drop table nivel_funcao');

      $objInfraMetaBD->excluirIndice('contato','ie1_contato');
      $objInfraMetaBD->excluirIndice('contato','ie2_contato');
      $objInfraMetaBD->excluirIndice('contato','ie3_contato');
      $objInfraMetaBD->excluirIndice('contato','i04_contato');

      if (BancoSEI::getInstance() instanceof InfraSqlServer){
        $objInfraMetaBD->excluirIndice('contato','if7_contato');
        $objInfraMetaBD->excluirIndice('contato','if8_contato');
      }

      $objInfraMetaBD->excluirChaveEstrangeira('contato','fk_contato_titulo');
      $objInfraMetaBD->excluirColuna('contato','id_titulo');
      BancoSEI::getInstance()->executarSql('drop table titulo');

      if (BancoSEI::getInstance() instanceof InfraOracle){
        BancoSEI::getInstance()->executarSql('drop sequence ' . BancoSEI::getInstance()->getUsuario() . '.seq_titulo');
      }else {
        BancoSEI::getInstance()->executarSql('drop table seq_titulo');
      }

      $objInfraMetaBD->excluirChaveEstrangeira('contato','fk_contato_tratamento');
      $objInfraMetaBD->excluirColuna('contato','id_tratamento');

      $objInfraMetaBD->excluirChaveEstrangeira('contato','fk_contato_vocativo');
      $objInfraMetaBD->excluirColuna('contato','id_vocativo');

      $objInfraMetaBD->adicionarColuna('cargo','id_tratamento',$objInfraMetaBD->tipoNumero(),'null');
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_cargo_tratamento','cargo',array('id_tratamento'),'tratamento',array('id_tratamento'));

      $objInfraMetaBD->adicionarColuna('cargo','id_vocativo',$objInfraMetaBD->tipoNumero(),'null');
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_cargo_vocativo','cargo',array('id_vocativo'),'vocativo',array('id_vocativo'));

      $objInfraMetaBD->adicionarColuna('cargo','sta_genero',$objInfraMetaBD->tipoTextoFixo(1),'null');

      $this->fixPopularCargoTratamentoVocativo();

      $objInfraMetaBD->adicionarColuna('contato','id_cidade',$objInfraMetaBD->tipoNumero(),'null');
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_contato_cidade','contato', array('id_cidade'), 'cidade', array('id_cidade'));

      $objInfraMetaBD->adicionarColuna('contato','id_uf',$objInfraMetaBD->tipoNumero(),'null');
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_contato_uf','contato', array('id_uf'), 'uf', array('id_uf'));

      $objInfraMetaBD->adicionarColuna('contato','id_pais',$objInfraMetaBD->tipoNumero(),'null');
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_contato_pais','contato', array('id_pais'), 'pais', array('id_pais'));

      if (BancoSEI::getInstance() instanceof InfraOracle){

        BancoSEI::getInstance()->executarSql('alter table uf disable constraint ak1_uf');
        BancoSEI::getInstance()->executarSql('alter table uf disable constraint ak2_uf');

        $objInfraMetaBD->excluirIndice('uf','ak1_uf');
        $objInfraMetaBD->excluirIndice('uf','ak2_uf');

      }else if (BancoSEI::getInstance() instanceof InfraSqlServer){

        BancoSEI::getInstance()->executarSql('alter table uf drop constraint ak1_uf');
        BancoSEI::getInstance()->executarSql('alter table uf drop constraint ak2_uf');

      }else if (BancoSEI::getInstance() instanceof InfraMySql){

        $objInfraMetaBD->excluirIndice('uf','ak1_uf');
        $objInfraMetaBD->excluirIndice('uf','ak2_uf');

      }

      $this->fixContatoCidadeUfPais();

      $objInfraMetaBD->excluirColuna('contato','sigla_estado');
      $objInfraMetaBD->excluirColuna('contato','nome_cidade');
      $objInfraMetaBD->excluirColuna('contato','nome_pais');

      $objInfraMetaBD->adicionarColuna('contato','id_contato_associado',$objInfraMetaBD->tipoNumero(),'null');
      BancoSEI::getInstance()->executarSql('update contato set id_contato_associado=id_contexto_contato');
      $objInfraMetaBD->alterarColuna('contato','id_contato_associado',$objInfraMetaBD->tipoNumero(),'not null');
      $objInfraMetaBD->excluirColuna('contato','id_contexto_contato');

      $objInfraMetaBD->adicionarColuna('contato','sta_genero',$objInfraMetaBD->tipoTextoFixo(1),'null');
      BancoSEI::getInstance()->executarSql('update contato set sta_genero=genero');
      $objInfraMetaBD->excluirColuna('contato','genero');


      BancoSEI::getInstance()->executarSql('update contato set email=sigla where id_contato in (select id_contato from usuario where sta_tipo in (2,3))');


      $objInfraMetaBD->excluirChaveEstrangeira('contato','fk_contato_tipo_contexto_conta');
      $objInfraMetaBD->excluirChaveEstrangeira('rel_unidade_tipo_cont_contato','fk_rel_unidade_tipo_cont_conta');
      $objInfraMetaBD->excluirChaveEstrangeira('rel_unidade_tipo_cont_contato','fk_rel_unidade_tipo_cont_unida');

      BancoSEI::getInstance()->executarSql('CREATE TABLE tipo_contato (
   id_tipo_contato '.$objInfraMetaBD->tipoNumero().' NOT NULL,
   nome '.$objInfraMetaBD->tipoTextoVariavel(50).' NOT NULL,
   descricao '.$objInfraMetaBD->tipoTextoVariavel(250).' NULL,
   sin_ativo '.$objInfraMetaBD->tipoTextoFixo(1).' NOT NULL,
   sin_liberado '.$objInfraMetaBD->tipoTextoFixo(1).' NOT NULL)');
      $objInfraMetaBD->adicionarChavePrimaria('tipo_contato','pk_tipo_contato',array('id_tipo_contato'));

      $rs = BancoSEI::getInstance()->consultarSql('select '.
          BancoSEI::getInstance()->formatarSelecaoNum(null,'id_tipo_contexto_contato',null).','.
          BancoSEI::getInstance()->formatarSelecaoStr(null,'nome',null).','.
          BancoSEI::getInstance()->formatarSelecaoStr(null,'descricao',null).','.
          BancoSEI::getInstance()->formatarSelecaoStr(null,'sin_liberado',null).','.
          BancoSEI::getInstance()->formatarSelecaoStr(null,'sin_ativo',null).' from tipo_contexto_contato order by id_tipo_contexto_contato');

      foreach($rs as $item){
        BancoSEI::getInstance()->executarSql('insert into tipo_contato (
id_tipo_contato, nome, descricao, sin_liberado, sin_ativo)
values ('.
            BancoSEI::getInstance()->formatarGravacaoNum(BancoSEI::getInstance()->formatarLeituraNum($item['id_tipo_contexto_contato'])).','.
            BancoSEI::getInstance()->formatarGravacaoStr(BancoSEI::getInstance()->formatarLeituraStr($item['nome'])).','.
            BancoSEI::getInstance()->formatarGravacaoStr(BancoSEI::getInstance()->formatarLeituraStr($item['descricao'])).','.
            BancoSEI::getInstance()->formatarGravacaoStr(BancoSEI::getInstance()->formatarLeituraStr($item['sin_liberado'])).','.
            BancoSEI::getInstance()->formatarGravacaoStr(BancoSEI::getInstance()->formatarLeituraStr($item['sin_ativo'])).')');
      }

      if (count($rs)){
        $numInicial = $rs[count($rs)-1]['id_tipo_contexto_contato'];
      }else{
        $numInicial = '1';
      }

      BancoSEI::getInstance()->criarSequencialNativa('seq_tipo_contato',$numInicial+1);

      BancoSEI::getInstance()->executarSql('CREATE TABLE rel_unidade_tipo_contato (
  id_rel_unidade_tipo_contato '.$objInfraMetaBD->tipoNumero().'  NOT NULL ,
	id_unidade           '.$objInfraMetaBD->tipoNumero().'  NOT NULL ,
	id_tipo_contato      '.$objInfraMetaBD->tipoNumero().'  NOT NULL,
	sta_acesso           '.$objInfraMetaBD->tipoTextoFixo(1).'  NOT NULL )');

      $objInfraMetaBD->adicionarChavePrimaria('rel_unidade_tipo_contato','pk_rel_unidade_tipo_contato',array('id_rel_unidade_tipo_contato'));

      $objInfraMetaBD->criarIndice('rel_unidade_tipo_contato','i01_rel_unidade_tipo_contato',array('id_unidade','id_tipo_contato','sta_acesso'));

      $rs = BancoSEI::getInstance()->consultarSql('select '.
          BancoSEI::getInstance()->formatarSelecaoNum(null,'id_tipo_contexto_contato',null).','.
          BancoSEI::getInstance()->formatarSelecaoNum(null,'id_unidade',null).' from rel_unidade_tipo_cont_contato');

      $numRelUnidadeTipoContato = 1;
      foreach($rs as $item){
        BancoSEI::getInstance()->executarSql('insert into rel_unidade_tipo_contato (
id_rel_unidade_tipo_contato, id_unidade, id_tipo_contato, sta_acesso)
values ('.
            BancoSEI::getInstance()->formatarGravacaoNum($numRelUnidadeTipoContato++).','.
            BancoSEI::getInstance()->formatarGravacaoNum(BancoSEI::getInstance()->formatarLeituraNum($item['id_unidade'])).','.
            BancoSEI::getInstance()->formatarGravacaoNum(BancoSEI::getInstance()->formatarLeituraNum($item['id_tipo_contexto_contato'])).','.
            BancoSEI::getInstance()->formatarGravacaoStr(TipoContatoRN::$TA_ALTERACAO).')');
      }

      BancoSEI::getInstance()->criarSequencialNativa('seq_rel_unidade_tipo_contato',$numRelUnidadeTipoContato+1);

      $objInfraMetaBD->adicionarColuna('contato','id_tipo_contato',$objInfraMetaBD->tipoNumero(),'null');
      BancoSEI::getInstance()->executarSql('update contato set id_tipo_contato=id_tipo_contexto_contato');
      $objInfraMetaBD->excluirColuna('contato','id_tipo_contexto_contato');

      $objInfraMetaBD->adicionarChaveEstrangeira('fk_rel_unid_tip_cont_unid','rel_unidade_tipo_contato',array('id_unidade'),'unidade',array('id_unidade'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_rel_unid_tip_cont_tip_cont','rel_unidade_tipo_contato',array('id_tipo_contato'),'tipo_contato',array('id_tipo_contato'));

      BancoSEI::getInstance()->executarSql('drop table tipo_contexto_contato');


      if (BancoSEI::getInstance() instanceof InfraOracle){
        BancoSEI::getInstance()->executarSql('drop sequence ' . BancoSEI::getInstance()->getUsuario() . '.seq_tipo_contexto_contato');
      }else {
        BancoSEI::getInstance()->executarSql('drop table seq_tipo_contexto_contato');
      }

      BancoSEI::getInstance()->executarSql('drop table rel_unidade_tipo_cont_contato');

      $objContatoBD = new ContatoBD(BancoSEI::getInstance());
      $rs = BancoSEI::getInstance()->consultarSql('select id_contato from contato where id_contato_associado is not null and id_contato_associado not in (select id_contato from contato)');
      InfraDebug::getInstance()->setBolDebugInfra(false);
      foreach($rs as $item){
        $dto = new ContatoDTO();
        $dto->setNumIdContatoAssociado($item['id_contato']);
        $dto->setNumIdContato($item['id_contato']);
        $objContatoBD->alterar($dto);
      }
      InfraDebug::getInstance()->setBolDebugInfra(true);

      $objContatoBD = new ContatoBD(BancoSEI::getInstance());

      $objContatoDTO = new ContatoDTO();
      $objContatoDTO->setBolExclusaoLogica(false);
      $objContatoDTO->retNumIdContato();
      $objContatoDTO->retNumIdTipoContatoAssociado();
      $objContatoDTO->setNumIdTipoContato(null);

      $objContatoRN = new ContatoRN();
      $arrObjContatoDTO = $objContatoRN->listarRN0325($objContatoDTO);

      $numIdTipoContatoTemporario = $objInfraParametro->getValor('ID_TIPO_CONTATO_TEMPORARIO');
      InfraDebug::getInstance()->setBolDebugInfra(false);
      foreach($arrObjContatoDTO as $objContatoDTO){
        $dto = new ContatoDTO();
        if ($objContatoDTO->getNumIdTipoContatoAssociado()==null){
          $dto->setNumIdTipoContato($numIdTipoContatoTemporario);
        }else{
          $dto->setNumIdTipoContato($objContatoDTO->getNumIdTipoContatoAssociado());
        }
        $dto->setNumIdContato($objContatoDTO->getNumIdContato());
        $objContatoBD->alterar($dto);
      }
      InfraDebug::getInstance()->setBolDebugInfra(true);

      $objInfraMetaBD->alterarColuna('contato','id_tipo_contato',$objInfraMetaBD->tipoNumero(),'not null');
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_contato_tipo_contato','contato',array('id_tipo_contato'),'tipo_contato',array('id_tipo_contato'));

      $objInfraMetaBD->adicionarColuna('tipo_contato','sin_sistema',$objInfraMetaBD->tipoTextoFixo(1),'null');
      BancoSEI::getInstance()->executarSql('update tipo_contato set sin_sistema=\'N\'');
      $objInfraMetaBD->alterarColuna('tipo_contato','sin_sistema',$objInfraMetaBD->tipoTextoFixo(1),'not null');
      $this->fixSinalizadorSistema();


      $objInfraMetaBD->adicionarColuna('tipo_contato','sta_acesso',$objInfraMetaBD->tipoTextoFixo(1),'null');
      BancoSEI::getInstance()->executarSql('update tipo_contato set sta_acesso=\''.TipoContatoRN::$TA_NENHUM.'\' where sin_liberado=\'N\'');
      BancoSEI::getInstance()->executarSql('update tipo_contato set sta_acesso=\''.TipoContatoRN::$TA_CONSULTA_RESUMIDA.'\' where sin_liberado=\'S\'');
      $objInfraMetaBD->alterarColuna('tipo_contato','sta_acesso',$objInfraMetaBD->tipoTextoFixo(1),'not null');
      $objInfraMetaBD->excluirColuna('tipo_contato','sin_liberado');
      $this->fixSinalizadorPesquisa();

      $objInfraMetaBD->criarIndice('contato','i01_contato',array('id_tipo_contato','sigla', 'nome', 'sin_ativo'));

      $this->fixUsuariosSemContato();


      if (BancoSEI::getInstance() instanceof InfraSqlServer) {
        $objInfraMetaBD->excluirIndice('usuario', 'if1_usuario');
      }

      $objInfraMetaBD->excluirIndice('usuario','i01_usuario');
      $objInfraMetaBD->excluirIndice('usuario','i02_usuario');
      $objInfraMetaBD->excluirIndice('usuario','i03_usuario');
      $objInfraMetaBD->excluirIndice('usuario','i04_usuario');

      $objInfraMetaBD->excluirChaveEstrangeira('usuario','fk_usuario_contato');
      $objInfraMetaBD->alterarColuna('usuario','id_contato',$objInfraMetaBD->tipoNumero(), 'not null');
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_usuario_contato','usuario',array('id_contato'),'contato',array('id_contato'));

      if (BancoSEI::getInstance() instanceof InfraSqlServer) {
        $objInfraMetaBD->criarIndice('usuario', 'if1_usuario', array('id_contato'));
      }

      $objInfraMetaBD->criarIndice('usuario','i01_usuario',array('id_orgao','sta_tipo','sigla','idx_usuario','sin_ativo'));


      $rs = BancoSEI::getInstance()->consultarSql('select '.
          BancoSEI::getInstance()->formatarSelecaoNum('usuario', 'id_contato', 'idcontato').','.
          BancoSEI::getInstance()->formatarSelecaoDbl('usuario', 'cpf', 'cpfusuario') .
          ' from usuario where cpf is not null');

      InfraDebug::getInstance()->setBolDebugInfra(false);
      foreach($rs as $usuario){
        BancoSEI::getInstance()->executarSql('update contato set cpf='.BancoSEI::getInstance()->formatarGravacaoDbl(BancoSEI::getInstance()->formatarLeituraDbl($usuario['cpfusuario'])).' where id_contato='.BancoSEI::getInstance()->formatarLeituraNum($usuario['idcontato']));
      }
      InfraDebug::getInstance()->setBolDebugInfra(true);

      $objInfraMetaBD->excluirColuna('usuario','cpf');

      $this->fixAtualizarContatosUnidades();

      $objInfraMetaBD->excluirColuna('unidade','endereco');
      $objInfraMetaBD->excluirColuna('unidade','complemento');
      $objInfraMetaBD->excluirColuna('unidade','bairro');
      $objInfraMetaBD->excluirColuna('unidade','cep');
      $objInfraMetaBD->excluirColuna('unidade','telefone');
      $objInfraMetaBD->excluirColuna('unidade','fax');
      $objInfraMetaBD->excluirColuna('unidade','sitio_internet');
      $objInfraMetaBD->excluirColuna('unidade','observacao');
      $objInfraMetaBD->excluirChaveEstrangeira('unidade','fk_unidade_uf');
      $objInfraMetaBD->excluirChaveEstrangeira('unidade','fk_unidade_cidade');
      $objInfraMetaBD->excluirColuna('unidade','id_uf');
      $objInfraMetaBD->excluirColuna('unidade','id_cidade');


      $objInfraMetaBD->excluirChaveEstrangeira('contato','fk_contato_orgao');
      $objInfraMetaBD->excluirColuna('contato','id_orgao');


      $objInfraMetaBD->adicionarColuna('orgao','idx_orgao',$objInfraMetaBD->tipoTextoVariavel(500),'null');
      $this->fixIndexacaoOrgaos();
      
      $objInfraMetaBD->adicionarColuna('orgao','id_contato',$objInfraMetaBD->tipoNumero(),'null');
      $this->fixCriarOrgaosContatos();
      
      $objInfraMetaBD->alterarColuna('orgao','id_contato',$objInfraMetaBD->tipoNumero(),'not null');
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_orgao_contato','orgao',array('id_contato'),'contato',array('id_contato'));
      $objInfraMetaBD->excluirColuna('orgao','sin_pagamento_viagens');
      $objInfraMetaBD->excluirColuna('orgao','endereco');
      $objInfraMetaBD->excluirColuna('orgao','complemento');
      $objInfraMetaBD->excluirColuna('orgao','bairro');
      $objInfraMetaBD->excluirColuna('orgao','cep');
      $objInfraMetaBD->excluirColuna('orgao','telefone');
      $objInfraMetaBD->excluirColuna('orgao','fax');
      $objInfraMetaBD->excluirColuna('orgao','sitio_internet');
      $objInfraMetaBD->excluirColuna('orgao','email');

      if (BancoSEI::getInstance() instanceof InfraSqlServer){
        $objInfraMetaBD->excluirIndice('orgao', 'xif1orgao');
      }

      $objInfraMetaBD->excluirChaveEstrangeira('orgao','fk_orgao_cidade');
      $objInfraMetaBD->excluirColuna('orgao','id_cidade');
      $this->fixAssociarUnidadesOrgaos();
      $this->fixAssociarUsuariosOrgaos();

      $this->logar('ATUALIZANDO SINALIZADORES DE EXCLUSAO LOGICA...');
      $this->fixSinAtivoContatos();

      $this->logar('ADICIONANDO REFERENCIA PARA CONTROLE INTERNO NA TABELA DE ACESSOS...');
      $objInfraMetaBD->adicionarColuna('acesso','id_controle_interno',$objInfraMetaBD->tipoNumero(),'null');
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_acesso_controle_interno','acesso',array('id_controle_interno'),'controle_interno',array('id_controle_interno'));

      $this->fixControleInterno();

      $this->logar('ATUALIZANDO PONTOS DE CONTROLE...');
      $objInfraMetaBD->adicionarColuna('andamento_situacao','id_situacao',$objInfraMetaBD->tipoNumero(),'null');
      $objInfraMetaBD->adicionarColuna('andamento_situacao','sin_ultimo',$objInfraMetaBD->tipoTextoFixo(1),'null');
      BancoSEI::getInstance()->executarSql('update andamento_situacao set sin_ultimo=\'N\'');
      $objInfraMetaBD->alterarColuna('andamento_situacao','sin_ultimo',$objInfraMetaBD->tipoTextoFixo(1),'not null');

      $this->fixPontoControle();

      BancoSEI::getInstance()->executarSql('drop table rel_proced_situacao_unidade');
      BancoSEI::getInstance()->executarSql('drop table atributo_andamento_situacao');
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_andam_situacao_situacao','andamento_situacao',array('id_situacao'),'situacao',array('id_situacao'));
      $objInfraMetaBD->criarIndice('andamento_situacao','i01_andamento_situacao',array('id_situacao','id_procedimento','id_unidade','sin_ultimo'));

      BancoSEI::getInstance()->executarSql('delete from controle_unidade');
      $objInfraMetaBD->adicionarColuna('controle_unidade','id_usuario',$objInfraMetaBD->tipoNumero(),'not null');
      $objInfraMetaBD->adicionarColuna('controle_unidade','dth_execucao',$objInfraMetaBD->tipoDataHora(),'not null');
      $objInfraMetaBD->alterarColuna('controle_unidade','id_situacao', $objInfraMetaBD->tipoNumero(),'not null');

      BancoSEI::getInstance()->executarSql('CREATE TABLE marcador (
id_marcador          '.$objInfraMetaBD->tipoNumero().'  NOT NULL ,
nome                 '.$objInfraMetaBD->tipoTextoVariavel(50).'  NOT NULL ,
descricao            '.$objInfraMetaBD->tipoTextoVariavel(250).'  NULL ,
sin_ativo            '.$objInfraMetaBD->tipoTextoFixo(1).'  NULL ,
sta_icone            '.$objInfraMetaBD->tipoTextoFixo(1).'  NOT NULL ,
id_unidade           '.$objInfraMetaBD->tipoNumero().'  NOT NULL)');

      $objInfraMetaBD->adicionarChavePrimaria('marcador','pk_marcador',array('id_marcador'));
      BancoSEI::getInstance()->criarSequencialNativa('seq_marcador',1);

      $objInfraMetaBD->adicionarChaveEstrangeira('fk_marcador_unidade','marcador',array('id_unidade'),'unidade',array('id_unidade'));

      BancoSEI::getInstance()->executarSql('CREATE TABLE andamento_marcador  (
id_andamento_marcador '.$objInfraMetaBD->tipoNumero().'  NOT NULL ,
id_marcador          '.$objInfraMetaBD->tipoNumero().'  NULL ,
id_unidade           '.$objInfraMetaBD->tipoNumero().'  NOT NULL ,
id_usuario           '.$objInfraMetaBD->tipoNumero().'  NOT NULL ,
dth_execucao         '.$objInfraMetaBD->tipoDataHora().'  NOT NULL ,
id_procedimento      '.$objInfraMetaBD->tipoNumeroGrande().'  NOT NULL ,
sin_ultimo           '.$objInfraMetaBD->tipoTextoFixo(1).'  NOT NULL ,
texto                '.$objInfraMetaBD->tipoTextoVariavel(250).'  NULL)');


      $objInfraMetaBD->adicionarChavePrimaria('andamento_marcador','pk_andamento_marcador',array('id_andamento_marcador'));
      BancoSEI::getInstance()->criarSequencialNativa('seq_andamento_marcador',1);

      $objInfraMetaBD->adicionarChaveEstrangeira('fk_andamento_marcador_marcador','andamento_marcador',array('id_marcador'),'marcador',array('id_marcador'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_andamento_marcador_unidade','andamento_marcador',array('id_unidade'),'unidade',array('id_unidade'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_andamento_marcador_usuario','andamento_marcador',array('id_usuario'),'usuario',array('id_usuario'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_andamento_marcador_proced','andamento_marcador',array('id_procedimento'),'procedimento',array('id_procedimento'));

      $objInfraMetaBD->criarIndice('andamento_marcador','i01_andamento_marcador',array('id_marcador','id_procedimento','id_unidade','sin_ultimo'));

      $this->logar('ATUALIZANDO ASSUNTOS...');
      //$objInfraMetaBD->excluirIndice('assunto','ie1_assunto');
      $objInfraMetaBD->excluirIndice('assunto','i02_assunto');
      $objInfraMetaBD->adicionarColuna('assunto','prazo_intermediario',$objInfraMetaBD->tipoNumero(),'null');
      $objInfraMetaBD->adicionarColuna('assunto','prazo_corrente',$objInfraMetaBD->tipoNumero(),'null');
      $objInfraMetaBD->adicionarColuna('assunto','sta_destinacao',$objInfraMetaBD->tipoTextoFixo(1),'null');
      $objInfraMetaBD->adicionarColuna('assunto','sin_estrutural',$objInfraMetaBD->tipoTextoFixo(1),'null');
      $objInfraMetaBD->criarIndice('assunto','i02_assunto',array('sin_estrutural','sin_ativo'));

      $this->fixAssuntos();

      $objInfraMetaBD->excluirColuna('assunto','maior_tempo_corrente');
      $objInfraMetaBD->excluirColuna('assunto','menor_tempo_corrente');
      $objInfraMetaBD->excluirColuna('assunto','sin_elimina_maior_corrente');
      $objInfraMetaBD->excluirColuna('assunto','sin_elimina_menor_corrente');
      $objInfraMetaBD->excluirColuna('assunto','maior_tempo_intermediario');
      $objInfraMetaBD->excluirColuna('assunto','menor_tempo_intermediario');
      $objInfraMetaBD->excluirColuna('assunto','sin_elimina_maior_intermed');
      $objInfraMetaBD->excluirColuna('assunto','sin_elimina_menor_intermed');
      $objInfraMetaBD->excluirColuna('assunto','sin_suficiente');

      BancoSEI::getInstance()->executarSql('update assunto set observacao=null where observacao=\'null\'');

      $objInfraMetaBD->excluirChaveEstrangeira('rel_protocolo_assunto','fk_rel_protocolo_assunto_proto');
      $objInfraMetaBD->excluirChaveEstrangeira('rel_protocolo_assunto','fk_rel_protocolo_assunto_assun');
      $objInfraMetaBD->excluirChavePrimaria('rel_protocolo_assunto','pk_rel_protocolo_assunto');

      $objInfraMetaBD->excluirChaveEstrangeira('rel_tipo_procedimento_assunto','fk_rel_tipo_procedimento_assun');
      $objInfraMetaBD->excluirChaveEstrangeira('rel_tipo_procedimento_assunto','fk_rel_tipo_proc_assun_assunto');
      $objInfraMetaBD->excluirChavePrimaria('rel_tipo_procedimento_assunto','pk_rel_tipo_proced_assunto');

      $objInfraMetaBD->excluirChaveEstrangeira('rel_serie_assunto','fk_rel_serie_assunto_assunto');
      $objInfraMetaBD->excluirChaveEstrangeira('rel_serie_assunto','fk_rel_serie_assunto_serie');
      $objInfraMetaBD->excluirChavePrimaria('rel_serie_assunto','pk_rel_serie_assunto');

      BancoSEI::getInstance()->executarSql('CREATE TABLE assunto_proxy (
                id_assunto_proxy '.$objInfraMetaBD->tipoNumero().'  NOT NULL ,
                id_assunto           '.$objInfraMetaBD->tipoNumero().'  NOT NULL)');

      $objAssuntoDTO = new AssuntoDTO();
      $objAssuntoDTO->setBolExclusaoLogica(false);
      $objAssuntoDTO->retNumIdAssunto();
      $objAssuntoDTO->setStrSinEstrutural('N');
      $objAssuntoDTO->setOrdNumIdAssunto(InfraDTO::$TIPO_ORDENACAO_ASC);

      $objAssuntoRN = new AssuntoRN();
      $arrObjAssuntoDTO = $objAssuntoRN->listarRN0247($objAssuntoDTO);

      InfraDebug::getInstance()->setBolDebugInfra(false);
      foreach ($arrObjAssuntoDTO as $objAssuntoDTO) {
         BancoSEI::getInstance()->executarSql('insert into assunto_proxy (id_assunto_proxy,id_assunto) values ('.$objAssuntoDTO->getNumIdAssunto().','.$objAssuntoDTO->getNumIdAssunto().')');
      }
      InfraDebug::getInstance()->setBolDebugInfra(true);

      $numSeqAssuntoProxy = 1;
      if (count($arrObjAssuntoDTO)){
        $numSeqAssuntoProxy = ($arrObjAssuntoDTO[count($arrObjAssuntoDTO)-1]->getNumIdAssunto() + 1);
      }
      BancoSEI::getInstance()->criarSequencialNativa('seq_assunto_proxy',$numSeqAssuntoProxy);

      $objInfraMetaBD->adicionarChavePrimaria('assunto_proxy','pk_assunto_proxy',array('id_assunto_proxy'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_assunto_proxy_assunto','assunto_proxy',array('id_assunto'),'assunto',array('id_assunto'));

      $objInfraMetaBD->adicionarColuna('rel_protocolo_assunto','id_assunto_proxy',$objInfraMetaBD->tipoNumero(),'null');
      BancoSEI::getInstance()->executarSql('update rel_protocolo_assunto set id_assunto_proxy=id_assunto');
      $objInfraMetaBD->alterarColuna('rel_protocolo_assunto','id_assunto_proxy',$objInfraMetaBD->tipoNumero(),'not null');

      $objInfraMetaBD->excluirColuna('rel_protocolo_assunto','id_assunto');

      $objInfraMetaBD->adicionarColuna('rel_tipo_procedimento_assunto','id_assunto_proxy',$objInfraMetaBD->tipoNumero(),'null');
      BancoSEI::getInstance()->executarSql('update rel_tipo_procedimento_assunto set id_assunto_proxy=id_assunto');
      $objInfraMetaBD->alterarColuna('rel_tipo_procedimento_assunto','id_assunto_proxy',$objInfraMetaBD->tipoNumero(),'not null');
      $objInfraMetaBD->excluirColuna('rel_tipo_procedimento_assunto','id_assunto');

      $objInfraMetaBD->adicionarColuna('rel_serie_assunto','id_assunto_proxy',$objInfraMetaBD->tipoNumero(),'null');
      BancoSEI::getInstance()->executarSql('update rel_serie_assunto set id_assunto_proxy=id_assunto');
      $objInfraMetaBD->alterarColuna('rel_serie_assunto','id_assunto_proxy',$objInfraMetaBD->tipoNumero(),'not null');
      $objInfraMetaBD->excluirColuna('rel_serie_assunto','id_assunto');

      $objInfraMetaBD->adicionarChavePrimaria('rel_protocolo_assunto','pk_rel_protocolo_assunto',array('id_protocolo','id_assunto_proxy'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_rel_prot_assunto_protocolo','rel_protocolo_assunto',array('id_protocolo'),'protocolo',array('id_protocolo'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_rel_prot_assunto_assunto','rel_protocolo_assunto',array('id_assunto_proxy'),'assunto_proxy',array('id_assunto_proxy'));
      $objInfraMetaBD->adicionarChavePrimaria('rel_tipo_procedimento_assunto','pk_rel_tipo_proced_assunto',array('id_tipo_procedimento','id_assunto_proxy'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_rel_tipo_proc_assu_tip_proc','rel_tipo_procedimento_assunto',array('id_tipo_procedimento'),'tipo_procedimento',array('id_tipo_procedimento'));


      $objInfraMetaBD->adicionarChaveEstrangeira('fk_rel_tipo_proc_assu_assunto','rel_tipo_procedimento_assunto',array('id_assunto_proxy'),'assunto_proxy',array('id_assunto_proxy'));

      $objInfraMetaBD->adicionarChavePrimaria('rel_serie_assunto','pk_rel_serie_assunto',array('id_serie','id_assunto_proxy'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_rel_serie_assunto_serie','rel_serie_assunto',array('id_serie'),'serie',array('id_serie'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_rel_serie_assunto_assunto','rel_serie_assunto',array('id_assunto_proxy'),'assunto_proxy',array('id_assunto_proxy'));

      BancoSEI::getInstance()->executarSql('CREATE TABLE tabela_assuntos (
	id_tabela_assuntos   '.$objInfraMetaBD->tipoNumero().'  NOT NULL ,
	nome                 '.$objInfraMetaBD->tipoTextoVariavel(50).'  NOT NULL ,
	descricao            '.$objInfraMetaBD->tipoTextoVariavel(250).'  NULL ,
	sin_atual            '.$objInfraMetaBD->tipoTextoFixo(1).'  NOT NULL
)');
      $objInfraMetaBD->adicionarChavePrimaria('tabela_assuntos','pk_tabela_assuntos',array('id_tabela_assuntos'));

      BancoSEI::getInstance()->executarSql('insert into tabela_assuntos (id_tabela_assuntos,nome,descricao,sin_atual) values (1,'.BancoSEI::getInstance()->formatarGravacaoStr('Tabela de Assuntos').',null,'.BancoSEI::getInstance()->formatarGravacaoStr('S').')');

      BancoSEI::getInstance()->criarSequencialNativa('seq_tabela_assuntos',2);

      $objInfraMetaBD->adicionarColuna('assunto','id_tabela_assuntos',$objInfraMetaBD->tipoNumero(),'null');
      BancoSEI::getInstance()->executarSql('update assunto set id_tabela_assuntos=1');
      $objInfraMetaBD->alterarColuna('assunto','id_tabela_assuntos',$objInfraMetaBD->tipoNumero(),'not null');
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_assunto_tabela_assuntos','assunto',array('id_tabela_assuntos'),'tabela_assuntos',array('id_tabela_assuntos'));

      if (BancoSEI::getInstance() instanceof InfraSqlServer) {

        BancoSEI::getInstance()->executarSql('alter table assunto drop constraint ak1_assunto');
        BancoSEI::getInstance()->executarSql('alter table assunto drop constraint ak2_assunto');

      }else if (BancoSEI::getInstance() instanceof InfraOracle){

        BancoSEI::getInstance()->executarSql('alter table assunto drop constraint ak1_assunto');
        BancoSEI::getInstance()->executarSql('alter table assunto drop constraint ak2_assunto');

        $objInfraMetaBD->excluirIndice('assunto', 'ak1_assunto');
        $objInfraMetaBD->excluirIndice('assunto', 'ak2_assunto');

      }else {

        $objInfraMetaBD->excluirIndice('assunto', 'ak1_assunto');
        $objInfraMetaBD->excluirIndice('assunto', 'ak2_assunto');

      }

      $objInfraMetaBD->excluirIndice('assunto','i01_assunto');
      $objInfraMetaBD->excluirIndice('assunto','i02_assunto');

      $objInfraMetaBD->criarIndice('assunto','i01_assunto',array('id_tabela_assuntos','codigo_estruturado', 'sin_estrutural', 'sin_ativo'));

      BancoSEI::getInstance()->executarSql('CREATE TABLE mapeamento_assunto (
	id_assunto_origem    '.$objInfraMetaBD->tipoNumero().'  NOT NULL ,
	id_assunto_destino   '.$objInfraMetaBD->tipoNumero().'  NOT NULL
)');

      $objInfraMetaBD->adicionarChavePrimaria('mapeamento_assunto','pk_mapeamento_assunto',array('id_assunto_origem','id_assunto_destino'));

      $objInfraMetaBD->adicionarChaveEstrangeira('fk_assunto_map_assunto_origem','mapeamento_assunto',array('id_assunto_origem'),'assunto',array('id_assunto'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_assunto_map_assunto_destino','mapeamento_assunto',array('id_assunto_destino'),'assunto',array('id_assunto'));

      BancoSEI::getInstance()->executarSql('CREATE TABLE arquivamento (
        id_protocolo   '.$objInfraMetaBD->tipoNumeroGrande().'  NOT NULL ,
        id_localizador       '.$objInfraMetaBD->tipoNumero().'  NULL ,
        id_atividade_arquivamento '.$objInfraMetaBD->tipoNumero().'  NULL ,
        id_atividade_desarquivamento '.$objInfraMetaBD->tipoNumero().'  NULL ,
        id_atividade_recebimento '.$objInfraMetaBD->tipoNumero().'  NULL ,
        id_atividade_solicitacao '.$objInfraMetaBD->tipoNumero().'  NULL ,
        sta_arquivamento     '.$objInfraMetaBD->tipoTextoFixo(1).'  NOT NULL
      )');

      $objInfraMetaBD->adicionarChavePrimaria('arquivamento','pk_arquivamento',array('id_protocolo'));

      $objInfraMetaBD->criarIndice('arquivamento','i07_arquivamento',array('id_localizador','sta_arquivamento'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_arquivamento_protocolo','arquivamento',array('id_protocolo'),'protocolo',array('id_protocolo'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_arquiv_ativ_arquiv','arquivamento',array('id_atividade_arquivamento'),'atividade',array('id_atividade'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_arquiv_ativ_desarquiv','arquivamento',array('id_atividade_desarquivamento'),'atividade',array('id_atividade'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_arquiv_ativ_receb','arquivamento',array('id_atividade_recebimento'),'atividade',array('id_atividade'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_arquiv_ativ_solic_desarq','arquivamento',array('id_atividade_solicitacao'),'atividade',array('id_atividade'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_arquivamento_localizador','arquivamento',array('id_localizador'),'localizador',array('id_localizador'));

      $this->fixArquivamento();

      $objInfraMetaBD->excluirChaveEstrangeira('protocolo','fk_protocolo_localizador');
      $objInfraMetaBD->excluirChaveEstrangeira('protocolo','fk_protocolo_usuario_arquivo');
      $objInfraMetaBD->excluirChaveEstrangeira('protocolo','fk_protocolo_unidade_arquivo');

      if (BancoSEI::getInstance() instanceof InfraSqlServer){
        $objInfraMetaBD->excluirIndice('protocolo', 'if5_protocolo');
        $objInfraMetaBD->excluirIndice('protocolo', 'i07_protocolo');
      }

      $objInfraMetaBD->excluirIndice('protocolo', 'i01_protocolo');
      $objInfraMetaBD->excluirIndice('protocolo', 'i03_protocolo');
      $objInfraMetaBD->excluirIndice('protocolo', 'i04_protocolo');
      $objInfraMetaBD->excluirIndice('protocolo', 'i05_protocolo');
      $objInfraMetaBD->excluirIndice('protocolo', 'i08_protocolo');

      $objInfraMetaBD->criarIndice('protocolo','i03_protocolo', array('sta_nivel_acesso_global', 'id_protocolo', 'id_unidade_geradora', 'sta_protocolo', 'sta_estado'));

      $objInfraMetaBD->excluirColuna('protocolo','id_localizador');
      $objInfraMetaBD->excluirColuna('protocolo','id_usuario_arquivamento');
      $objInfraMetaBD->excluirColuna('protocolo','id_unidade_arquivamento');
      $objInfraMetaBD->excluirColuna('protocolo','dth_arquivamento');
      $objInfraMetaBD->excluirColuna('protocolo','sta_arquivamento');

      $this->fixProtocoloFormatado();

      $objInfraMetaBD->adicionarColuna('observacao','idx_observacao',$objInfraMetaBD->tipoTextoGrande(),'null');
      $this->fixIndexacaoObservacoes();

      $objInfraMetaBD->excluirChaveEstrangeira('texto_padrao','fk_texto_padrao_unidade');
      BancoSEI::getInstance()->executarSql('drop table texto_padrao');

      $objInfraMetaBD->excluirChaveEstrangeira('atividade','fk_atividade_unidade');
      $objInfraMetaBD->excluirChaveEstrangeira('atividade','fk_atividade_usuario');
      $objInfraMetaBD->excluirChaveEstrangeira('atividade','fk_atividade_tarefa');

      $objInfraMetaBD->excluirIndice('atividade','i01_atividade');
      $objInfraMetaBD->excluirIndice('atividade','i02_atividade');
      $objInfraMetaBD->excluirIndice('atividade','i03_atividade');
      $objInfraMetaBD->excluirIndice('atividade','i05_atividade');
      $objInfraMetaBD->excluirIndice('atividade','i08_atividade');

      $objInfraMetaBD->criarIndice('atividade','i01_atividade',array('id_atividade', 'id_protocolo', 'id_unidade', 'id_usuario', 'dth_conclusao', 'sin_inicial', 'id_usuario_atribuicao'));
      $objInfraMetaBD->criarIndice('atividade','i02_atividade',array('id_atividade', 'id_protocolo', 'id_unidade', 'id_usuario', 'id_tarefa'));

      $objInfraMetaBD->adicionarChaveEstrangeira('fk_atividade_unidade','atividade',array('id_unidade'),'unidade',array('id_unidade'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_atividade_usuario','atividade',array('id_usuario'),'usuario',array('id_usuario'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_atividade_tarefa','atividade',array('id_tarefa'),'tarefa',array('id_tarefa'));


      $objInfraSequencia = new InfraSequencia(BancoSEI::getInstance());
      BancoSEI::getInstance()->executarSql('insert into infra_agendamento_tarefa (
                            id_infra_agendamento_tarefa, descricao, comando, sta_periodicidade_execucao,
                            periodicidade_complemento, dth_ultima_execucao, dth_ultima_conclusao,
                            sin_sucesso, parametro, email_erro, sin_ativo)
                            values ('.$objInfraSequencia->obterProximaSequencia('infra_agendamento_tarefa').',\'Remove arquivos com mais de 24 horas criados pelo serviço adicionarArquivo e que ainda não foram utilizados.\',\'AgendamentoRN::removerAquivosNaoUtilizados\',\'D\',\'5\',null,null,\'N\',null,null,\'S\')');


      $this->fixAtividadeConclusaoAutomaticaUsuario();

      BancoSEI::getInstance()->executarSql('update veiculo_publicacao set sin_fonte_feriados=\'N\' where sta_tipo=\'I\'');

      $objInfraMetaBD->adicionarColuna('arquivo_extensao','tamanho_maximo', $objInfraMetaBD->tipoNumero(), 'null');
      $objInfraMetaBD->alterarColuna('texto_padrao_interno','nome',$objInfraMetaBD->tipoTextoVariavel(50),'not null');

      BancoSEI::getInstance()->executarSql('CREATE TABLE serie_restricao (
                                      id_serie_restricao   '.$objInfraMetaBD->tipoNumero().'  NOT NULL ,
                                      id_serie             '.$objInfraMetaBD->tipoNumero().'  NOT NULL ,
                                      id_orgao             '.$objInfraMetaBD->tipoNumero().'  NOT NULL ,
                                      id_unidade           '.$objInfraMetaBD->tipoNumero().'  NULL)');

      $objInfraMetaBD->adicionarChavePrimaria('serie_restricao','pk_serie_restricao', array('id_serie_restricao'));
      BancoSEI::getInstance()->criarSequencialNativa('seq_serie_restricao',1);

      BancoSEI::getInstance()->executarSql('CREATE TABLE tipo_proced_restricao (
                                        id_tipo_proced_restricao '.$objInfraMetaBD->tipoNumero().'  NOT NULL ,
                                        id_tipo_procedimento '.$objInfraMetaBD->tipoNumero().'  NOT NULL ,
                                        id_orgao             '.$objInfraMetaBD->tipoNumero().'  NOT NULL ,
                                        id_unidade           '.$objInfraMetaBD->tipoNumero().'  NULL 
                                      )');

      $objInfraMetaBD->adicionarChavePrimaria('tipo_proced_restricao','pk_tipo_proced_restricao', array('id_tipo_proced_restricao'));
      BancoSEI::getInstance()->criarSequencialNativa('seq_tipo_proced_restricao',1);

      $objInfraMetaBD->adicionarChaveEstrangeira('fk_serie_restricao_serie','serie_restricao',array('id_serie'),'serie',array('id_serie'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_serie_restricao_orgao','serie_restricao',array('id_orgao'),'orgao',array('id_orgao'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_serie_restricao_unidade','serie_restricao',array('id_unidade'),'unidade',array('id_unidade'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_tipo_proced_restr_tipo_proc','tipo_proced_restricao',array('id_tipo_procedimento'),'tipo_procedimento',array('id_tipo_procedimento'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_tipo_proced_restr_orgao','tipo_proced_restricao',array('id_orgao'),'orgao',array('id_orgao'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_tipo_proced_restr_unidade','tipo_proced_restricao',array('id_unidade'),'unidade',array('id_unidade'));

      BancoSEI::getInstance()->executarSql('CREATE TABLE rel_acesso_ext_protocolo (
                                                      id_acesso_externo    '.$objInfraMetaBD->tipoNumero().'  NOT NULL ,
                                                      id_protocolo         '.$objInfraMetaBD->tipoNumeroGrande().'  NOT NULL 
                                                    )');

      $objInfraMetaBD->adicionarChavePrimaria('rel_acesso_ext_protocolo','pk_rel_acesso_ext_protocolo', array('id_acesso_externo','id_protocolo'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_rel_aces_ext_prot_aces_ext','rel_acesso_ext_protocolo',array('id_acesso_externo'),'acesso_externo',array('id_acesso_externo'));
      $objInfraMetaBD->adicionarChaveEstrangeira('fk_rel_aces_ext_prot_protocolo','rel_acesso_ext_protocolo',array('id_protocolo'),'protocolo',array('id_protocolo'));

      BancoSEI::getInstance()->executarSql('update tarefa set nome=\'Disponibilizado acesso externo para @DESTINATARIO_NOME@ (@DESTINATARIO_EMAIL@) até @DATA_VALIDADE@ (@DIAS_VALIDADE@).@VISUALIZACAO@\r\n@MOTIVO@\' where id_tarefa=50');
      BancoSEI::getInstance()->executarSql('update tarefa set nome=\'Liberada assinatura externa para o usuário @USUARIO_EXTERNO_NOME@ (@USUARIO_EXTERNO_SIGLA@) no documento @DOCUMENTO@.@VISUALIZACAO@\' where id_tarefa=86');
      BancoSEI::getInstance()->executarSql('update tarefa set nome=\'Liberada assinatura externa para o usuário @USUARIO_EXTERNO_NOME@ (@USUARIO_EXTERNO_SIGLA@) no documento @DOCUMENTO@.@VISUALIZACAO@\r\n(cancelada por @USUARIO@ em @DATA_HORA@)\' where id_tarefa=88');
      BancoSEI::getInstance()->executarSql('update tarefa set nome=\'Disponibilizado acesso externo para @DESTINATARIO_NOME@ (@DESTINATARIO_EMAIL@) até @DATA_VALIDADE@ (@DIAS_VALIDADE@).@VISUALIZACAO@\r\n@MOTIVO@\r\n(cancelada por @USUARIO@ em @DATA_HORA@)\' where id_tarefa=89');
      $this->fixAcessoExterno();

	    $this->fixIndexacaoContatos();

      BancoSEI::getInstance()->executarSql('update infra_parametro set valor=\''.SEI_VERSAO.'\' where nome=\'SEI_VERSAO\'');

      $this->finalizar('FIM',false);

		}catch(Exception $e){
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->setBolEcho(false);
		  throw new InfraException('Erro atualizando versão.', $e);
		}
	}

  protected function fixCredencialAssinaturaControlado() {
    try {
      InfraDebug::getInstance()->gravar('ATUALIZANDO CREDENCIAIS DE ASSINATURA...');

      //corrige situações onde o processo continua no Controle de Processos após renúncia ou conclusão

      //busca atividades de concessão de credencial para assinatura onde não existem mais os documentos
      $sql = 'select atividade.id_atividade, protocolo.protocolo_formatado,' . BancoSEI::getInstance()->formatarSelecaoDbl('atividade', 'id_protocolo', null) . ',atividade.id_usuario,atividade.id_unidade, atributo_andamento.id_origem
              from atividade, atributo_andamento, protocolo, acesso
              where atividade.id_atividade=atributo_andamento.id_atividade
              and atividade.id_tarefa=' . TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA . '
              and atributo_andamento.nome=' . BancoSEI::getInstance()->formatarGravacaoStr('DOCUMENTO') . '
              and not exists (select documento.id_documento from documento where documento.id_documento=atributo_andamento.id_origem)
              and atividade.id_protocolo=protocolo.id_protocolo
              and protocolo.sta_nivel_acesso_global=' . BancoSEI::getInstance()->formatarGravacaoStr(ProtocoloRN::$NA_SIGILOSO) . '
              and acesso.id_protocolo=atividade.id_protocolo
              and acesso.id_unidade=atividade.id_unidade
              and acesso.id_usuario=atividade.id_usuario
              and acesso.sta_tipo=' . BancoSEI::getInstance()->formatarGravacaoStr(AcessoRN::$TA_CREDENCIAL_ASSINATURA_PROCESSO);

      $rs1 = BancoSEI::getInstance()->consultarSql($sql);

      InfraDebug::getInstance()->setBolDebugInfra(false);

      if (count($rs1)) {

        foreach ($rs1 as $item) {

          $numIdAtividade = $item['id_atividade'];
          $dblIdProtocolo = $item['id_protocolo'];
          $numIdUsuario = $item['id_usuario'];
          $numIdUnidade = $item['id_unidade'];
          $strIdOrigem = $item['id_origem'];

          //verificando se o usuario/unidade possui outras credenciais de assinatura em documentos existentes do processo

          $sql = 'select count(*) as total
                  from atividade, atributo_andamento
                  where atividade.id_atividade=atributo_andamento.id_atividade
                  and atividade.id_tarefa=' . TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA . '
                  and atributo_andamento.nome=' . BancoSEI::getInstance()->formatarGravacaoStr('DOCUMENTO') . '
                  and exists (select documento.id_documento from documento where documento.id_documento=atributo_andamento.id_origem)
                  and atividade.id_protocolo=' . $dblIdProtocolo . '
                  and atividade.id_usuario=' . $numIdUsuario . '
                  and atividade.id_unidade=' . $numIdUnidade;

          $rs2 = BancoSEI::getInstance()->consultarSql($sql);

          if ($rs2[0]['total'] == 0) {

            InfraDebug::getInstance()->gravar($item['protocolo_formatado'].', credencial de assinatura '.$numIdAtividade);

            //remove registros de acesso associados com credencial de assinatura para o usuario/unidade/processo


            BancoSEI::getInstance()->executarSql('delete from acesso
                                              where id_usuario=' . $numIdUsuario . '
                                              and id_unidade=' . $numIdUnidade . '
                                              and (id_protocolo=' . $dblIdProtocolo . ' or id_protocolo in (select id_documento from documento where id_procedimento=' . $dblIdProtocolo . '))
                                              and sta_tipo=' . BancoSEI::getInstance()->formatarGravacaoStr(AcessoRN::$TA_CREDENCIAL_ASSINATURA_PROCESSO));


            //busca identificador da última atividade do usuário no processo
            $rs3 = BancoSEI::getInstance()->consultarSql('select max(id_atividade) as ultima
                                                      from atividade
                                                      where id_usuario=' . $numIdUsuario . '
                                                      and id_unidade=' . $numIdUnidade . '
                                                      and id_protocolo=' . $dblIdProtocolo);

            if ($rs3[0]['ultima']) {

              //busca dados da ultima atividade
              $rs4 = BancoSEI::getInstance()->consultarSql('select id_atividade, id_tarefa, dth_abertura, dth_conclusao, id_usuario, id_unidade
                                                        from atividade
                                                        where id_atividade=' . $rs3[0]['ultima']);

              //se a última ação do usuário foi renúnciar ao processo mas ele continua aberto no Controle de Processos
              if ($rs4[0]['id_tarefa'] == TarefaRN::$TI_PROCESSO_RENUNCIA_CREDENCIAL && BancoSEI::getInstance()->formatarLeituraDth($rs4[0]['dth_conclusao']) == null) {

                //finaliza pendencia para sumir do controle de processos
                BancoSEI::getInstance()->executarSql('update atividade
                    set dth_conclusao=' . BancoSEI::getInstance()->formatarGravacaoDth(BancoSEI::getInstance()->formatarLeituraDth($rs4[0]['dth_abertura'])) . '
                    where id_atividade=' . $rs4[0]['id_atividade']);
              }
            }
          }

          //busca atividade de exclusão do documento
          $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
          $objAtributoAndamentoDTO->retNumIdAtividade();
          $objAtributoAndamentoDTO->retDthAberturaAtividade();
          $objAtributoAndamentoDTO->retNumIdUsuarioOrigemAtividade();
          $objAtributoAndamentoDTO->retStrSiglaUsuarioOrigemAtividade();
          $objAtributoAndamentoDTO->retStrNomeUsuarioOrigemAtividade();
          $objAtributoAndamentoDTO->retNumIdUnidadeOrigemAtividade();
          $objAtributoAndamentoDTO->setStrNome('DOCUMENTO');
          $objAtributoAndamentoDTO->setStrIdOrigem($strIdOrigem);
          $objAtributoAndamentoDTO->setNumIdTarefaAtividade(TarefaRN::$TI_EXCLUSAO_DOCUMENTO);
          $objAtributoAndamentoDTO->setDblIdProtocoloAtividade($dblIdProtocolo);

          $objAtributoAndamentoRN = new AtributoAndamentoRN();
          $objAtributoAndamentoDTOExclusao = $objAtributoAndamentoRN->consultarRN1366($objAtributoAndamentoDTO);

          if ($objAtributoAndamentoDTOExclusao != null) {

            //anular a credencial de assinatura no documento que foi excluído
            $objAtividadeDTO = new AtividadeDTO();
            $objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA_ANULADA);
            $objAtividadeDTO->setNumIdAtividade($numIdAtividade);
            $objAtividadeBD = new AtividadeBD(BancoSEI::getInstance());
            $objAtividadeBD->alterar($objAtividadeDTO);

            $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
            $objAtributoAndamentoDTO->setStrNome('USUARIO_ANULACAO');
            $objAtributoAndamentoDTO->setStrValor($objAtributoAndamentoDTOExclusao->getStrSiglaUsuarioOrigemAtividade() . '¥' . $objAtributoAndamentoDTOExclusao->getStrNomeUsuarioOrigemAtividade());
            $objAtributoAndamentoDTO->setStrIdOrigem($objAtributoAndamentoDTOExclusao->getNumIdUsuarioOrigemAtividade());
            $objAtributoAndamentoDTO->setNumIdAtividade($numIdAtividade);
            $objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);

            $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
            $objAtributoAndamentoDTO->setStrNome('DATA_HORA');
            $objAtributoAndamentoDTO->setStrValor($objAtributoAndamentoDTOExclusao->getDthAberturaAtividade());
            $objAtributoAndamentoDTO->setStrIdOrigem($objAtributoAndamentoDTOExclusao->getNumIdAtividade()); //id do andamento que causou a anulação
            $objAtributoAndamentoDTO->setNumIdAtividade($numIdAtividade);
            $objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);
          }
        }
      }

      unset($rs1);

      InfraDebug::getInstance()->setBolDebugInfra(true);


      $objAtividadeRN = new AtividadeRN();
      $objAtributoAndamentoRN = new AtributoAndamentoRN();

      //busca atividades de concessão de credencial para assinatura onde o usuário não tem credencial no processo
      $sql = 'select atividade.id_atividade, protocolo.protocolo_formatado,' . BancoSEI::getInstance()->formatarSelecaoDbl('atividade', 'id_protocolo', null) . ',atividade.id_usuario,atividade.id_unidade
              from atividade, protocolo, acesso
              where atividade.id_tarefa=' . TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA . '
              and not exists (select acesso.id_protocolo from acesso where acesso.id_protocolo=atividade.id_protocolo and acesso.id_usuario=atividade.id_usuario and acesso.id_unidade=atividade.id_unidade and acesso.sta_tipo='.BancoSEI::getInstance()->formatarGravacaoStr(AcessoRN::$TA_CREDENCIAL_PROCESSO).')
              and atividade.id_protocolo=protocolo.id_protocolo
              and protocolo.sta_nivel_acesso_global=' . BancoSEI::getInstance()->formatarGravacaoStr(ProtocoloRN::$NA_SIGILOSO) . '
              and acesso.id_protocolo=atividade.id_protocolo
              and acesso.id_unidade=atividade.id_unidade
              and acesso.id_usuario=atividade.id_usuario
              and acesso.sta_tipo=' . BancoSEI::getInstance()->formatarGravacaoStr(AcessoRN::$TA_CREDENCIAL_ASSINATURA_PROCESSO);

      $rs1 = BancoSEI::getInstance()->consultarSql($sql);

      InfraDebug::getInstance()->setBolDebugInfra(false);

      if (count($rs1)) {

        foreach ($rs1 as $item) {

          $numIdAtividade = $item['id_atividade'];
          $dblIdProtocolo = $item['id_protocolo'];
          $numIdUsuario = $item['id_usuario'];
          $numIdUnidade = $item['id_unidade'];

          //verificando se o usuario/unidade possui outras credenciais de assinatura em documentos existentes do processo

          //busca identificador da última atividade do usuário no processo
          $rs2 = BancoSEI::getInstance()->consultarSql('select max(id_atividade) as ultima
                                                      from atividade
                                                      where id_usuario=' . $numIdUsuario . '
                                                      and id_unidade=' . $numIdUnidade . '
                                                      and id_protocolo=' . $dblIdProtocolo);

          if ($rs2[0]['ultima']) {

            $objAtividadeDTOUltima = new AtividadeDTO();
            $objAtividadeDTOUltima->retNumIdAtividade();
            $objAtividadeDTOUltima->retDthAbertura();
            $objAtividadeDTOUltima->retDthConclusao();
            $objAtividadeDTOUltima->retNumIdUsuario();
            $objAtividadeDTOUltima->retStrSiglaUsuario();
            $objAtividadeDTOUltima->retStrNomeUsuario();
            $objAtividadeDTOUltima->retNumIdUnidade();
            $objAtividadeDTOUltima->retNumIdTarefa();
            $objAtividadeDTOUltima->retStrNomeTarefa();
            $objAtividadeDTOUltima->setNumIdAtividade($rs2[0]['ultima']);

            $objAtividadeDTOUltima = $objAtividadeRN->consultarRN0033($objAtividadeDTOUltima);

            //se a última ação do usuário foi renúnciar ao processo mas ele continua aberto no Controle de Processos
            if ($objAtividadeDTOUltima->getNumIdTarefa() == TarefaRN::$TI_PROCESSO_RENUNCIA_CREDENCIAL && $objAtividadeDTOUltima->getDthConclusao() == null) {

              InfraDebug::getInstance()->gravar($item['protocolo_formatado'].', credencial de assinatura '.$objAtividadeDTOUltima->getNumIdAtividade());

              //finaliza pendencia para sumir do controle de processos
              BancoSEI::getInstance()->executarSql('update atividade
                    set dth_conclusao=' . BancoSEI::getInstance()->formatarGravacaoDth($objAtividadeDTOUltima->getDthAbertura()) . '
                    where id_atividade=' . $objAtividadeDTOUltima->getNumIdAtividade());


              //remove registros de acesso associados com credencial de assinatura para o usuario/unidade/processo
              BancoSEI::getInstance()->executarSql('delete from acesso
                                              where id_usuario=' . $numIdUsuario . '
                                              and id_unidade=' . $numIdUnidade . '
                                              and (id_protocolo=' . $dblIdProtocolo . ' or id_protocolo in (select id_documento from documento where id_procedimento=' . $dblIdProtocolo . '))
                                              and sta_tipo=' . BancoSEI::getInstance()->formatarGravacaoStr(AcessoRN::$TA_CREDENCIAL_ASSINATURA_PROCESSO));


              //anular a credencial de assinatura
              $objAtividadeDTO = new AtividadeDTO();
              $objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_CONCESSAO_CREDENCIAL_ASSINATURA_ANULADA);
              $objAtividadeDTO->setNumIdAtividade($numIdAtividade);
              $objAtividadeBD = new AtividadeBD(BancoSEI::getInstance());
              $objAtividadeBD->alterar($objAtividadeDTO);

              $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
              $objAtributoAndamentoDTO->setStrNome('USUARIO_ANULACAO');
              $objAtributoAndamentoDTO->setStrValor($objAtividadeDTOUltima->getStrSiglaUsuario() . '¥' . $objAtividadeDTOUltima->getStrNomeUsuario());
              $objAtributoAndamentoDTO->setStrIdOrigem($objAtividadeDTOUltima->getNumIdUsuario());
              $objAtributoAndamentoDTO->setNumIdAtividade($numIdAtividade);
              $objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);

              $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
              $objAtributoAndamentoDTO->setStrNome('DATA_HORA');
              $objAtributoAndamentoDTO->setStrValor($objAtividadeDTOUltima->getDthAbertura());
              $objAtributoAndamentoDTO->setStrIdOrigem($objAtividadeDTOUltima->getNumIdAtividade()); //id do andamento que causou a anulação
              $objAtributoAndamentoDTO->setNumIdAtividade($numIdAtividade);
              $objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);
            }
          }
        }
      }

      unset($rs1);

      InfraDebug::getInstance()->setBolDebugInfra(true);

    }catch(Exception $e){
      throw new InfraException('Erro corrigindo credenciais de assinatura.', $e);
    }
  }

  protected function fixSenhaBcryptControlado(){
    try {
      InfraDebug::getInstance()->gravar('ATUALIZANDO SENHAS DE USUARIOS EXTERNOS...');


      InfraDebug::getInstance()->setBolDebugInfra(false);
      $objUsuarioDTO = new UsuarioDTO();
      $objUsuarioRN = new UsuarioRN();
      $objUsuarioBD = new UsuarioBD(BancoSEI::getInstance());
      $objUsuarioDTO->setBolExclusaoLogica(false);
      $objUsuarioDTO->setStrStaTipo(array(2, 3), InfraDTO::$OPER_IN);
      $objUsuarioDTO->retNumIdUsuario();
      $objUsuarioDTO->retStrSenha();
      $arrObjUsuarioDTO = $objUsuarioRN->listarRN0490($objUsuarioDTO);

      $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());
      $objInfraMetaBD->alterarColuna('usuario','senha',$objInfraMetaBD->tipoTextoFixo(60),'null');

      $bcrypt = new InfraBcrypt();

      $numRegistros = count($arrObjUsuarioDTO);
      $n = 0;
      foreach ($arrObjUsuarioDTO as $objUsuarioDTO) {

        if ((++$n >=100 && $n%100==0) || $n==$numRegistros){
          InfraDebug::getInstance()->gravar('USUARIOS: '.$n.' DE '.$numRegistros);
        }

        $objUsuarioDTO->setStrSenha($bcrypt->hash($objUsuarioDTO->getStrSenha()));
        $objUsuarioBD->alterar($objUsuarioDTO);
      }
      InfraDebug::getInstance()->setBolDebugInfra(true);

    }catch(Exception $e){
      throw new InfraException('Erro migrando senhas de usuários externos.', $e);
    }
  }

  protected function fixUsuariosSemContatoControlado(){
    try {
      InfraDebug::getInstance()->gravar('ATUALIZANDO USUÁRIOS SEM CONTATO...');

      $rs = BancoSEI::getInstance()->consultarSql('select '.
BancoSEI::getInstance()->formatarSelecaoNum('usuario','id_usuario ','idusuario') .','.
BancoSEI::getInstance()->formatarSelecaoStr('usuario','sigla','siglausuario') .','.
BancoSEI::getInstance()->formatarSelecaoStr('usuario','nome','nomeusuario') .','.
BancoSEI::getInstance()->formatarSelecaoDbl('usuario', 'cpf', 'cpfusuario') .','.
BancoSEI::getInstance()->formatarSelecaoStr('usuario','sta_tipo','statipousuario') .','.
BancoSEI::getInstance()->formatarSelecaoStr('orgao','sigla','siglaorgao') .
' from usuario, orgao where usuario.id_orgao=orgao.id_orgao and usuario.id_contato is null');

      $objInfraParametro = new InfraParametro(BancoSEI::getInstance());

      foreach($rs as $usuario) {

        $numIdUsuario = BancoSEI::getInstance()->formatarLeituraNum($usuario['idusuario']);
        $strSiglaUsuario = BancoSEI::getInstance()->formatarLeituraStr($usuario['siglausuario']);
        $strNomeUsuario = BancoSEI::getInstance()->formatarLeituraStr($usuario['nomeusuario']);
        $dblCpfUsuario = BancoSEI::getInstance()->formatarLeituraDbl($usuario['cpfusuario']);
        $strStaTipo = BancoSEI::getInstance()->formatarLeituraStr($usuario['statipousuario']);
        $strSiglaOrgao = BancoSEI::getInstance()->formatarLeituraStr($usuario['siglaorgao']);


        if ($strStaTipo == UsuarioRN::$TU_SISTEMA) {

          $numIdTipoContato = $objInfraParametro->getValor('ID_TIPO_CONTATO_SISTEMAS');

        } else if ($strStaTipo == UsuarioRN::$TU_EXTERNO || $strStaTipo == UsuarioRN::$TU_EXTERNO_PENDENTE) {

          if (!$objInfraParametro->isSetValor($strSiglaOrgao . '_ID_TIPO_CONTATO_USUARIOS_EXTERNOS')) {
            $objTipoContatoDTO = new TipoContatoDTO();
            $objTipoContatoDTO->setNumIdTipoContato(null);
            $objTipoContatoDTO->setStrNome('Usuários Externos ' . $strSiglaOrgao);
            $objTipoContatoDTO->setStrDescricao('Usuários Externos ' . $strSiglaOrgao);
            $objTipoContatoDTO->setStrStaAcesso(TipoContatoRN::$TA_CONSULTA_RESUMIDA);
            $objTipoContatoDTO->setStrSinSistema('S');
            $objTipoContatoDTO->setStrSinAtivo('S');

            $objTipoContatoRN = new TipoContatoRN();
            $objTipoContatoDTO = $objTipoContatoRN->cadastrarRN0334($objTipoContatoDTO);

            $objRelUnidadeTipoContatoDTO = new RelUnidadeTipoContatoDTO();
            $objRelUnidadeTipoContatoDTO->setNumIdTipoContato($objTipoContatoDTO->getNumIdTipoContato());
            $objRelUnidadeTipoContatoDTO->setNumIdUnidade($objInfraParametro->getValor('ID_UNIDADE_TESTE'));
            $objRelUnidadeTipoContatoDTO->setStrStaAcesso(TipoContatoRN::$TA_ALTERACAO);

            $objRelUnidadeTipoContatoRN = new RelUnidadeTipoContatoRN();
            $objRelUnidadeTipoContatoRN->cadastrarRN0545($objRelUnidadeTipoContatoDTO);

            $objInfraParametro->setValor($strSiglaOrgao . '_ID_TIPO_CONTATO_USUARIOS_EXTERNOS', $objTipoContatoDTO->getNumIdTipoContato());
          }

          $numIdTipoContato = $objInfraParametro->getValor($strSiglaOrgao . '_ID_TIPO_CONTATO_USUARIOS_EXTERNOS');

        } else {
          if (!$objInfraParametro->isSetValor($strSiglaOrgao . '_ID_TIPO_CONTATO_USUARIOS')) {
            $objTipoContatoDTO = new TipoContatoDTO();
            $objTipoContatoDTO->setNumIdTipoContato(null);
            $objTipoContatoDTO->setStrNome('Usuários ' . $strSiglaOrgao);
            $objTipoContatoDTO->setStrDescricao('Usuários ' . $strSiglaOrgao);
            $objTipoContatoDTO->setStrStaAcesso(TipoContatoRN::$TA_CONSULTA_RESUMIDA);
            $objTipoContatoDTO->setStrSinSistema('S');
            $objTipoContatoDTO->setStrSinAtivo('S');

            $objTipoContatoRN = new TipoContatoRN();
            $objTipoContatoDTO = $objTipoContatoRN->cadastrarRN0334($objTipoContatoDTO);

            $objRelUnidadeTipoContatoDTO = new RelUnidadeTipoContatoDTO();
            $objRelUnidadeTipoContatoDTO->setNumIdTipoContato($objTipoContatoDTO->getNumIdTipoContato());
            $objRelUnidadeTipoContatoDTO->setNumIdUnidade($objInfraParametro->getValor('ID_UNIDADE_TESTE'));
            $objRelUnidadeTipoContatoDTO->setStrStaAcesso(TipoContatoRN::$TA_ALTERACAO);

            $objRelUnidadeTipoContatoRN = new RelUnidadeTipoContatoRN();
            $objRelUnidadeTipoContatoRN->cadastrarRN0545($objRelUnidadeTipoContatoDTO);

            $objInfraParametro->setValor($strSiglaOrgao . '_ID_TIPO_CONTATO_USUARIOS', $objTipoContatoDTO->getNumIdTipoContato());
          }

          $numIdTipoContato = $objInfraParametro->getValor($strSiglaOrgao . '_ID_TIPO_CONTATO_USUARIOS');
        }

        $objContatoDTO = new ContatoDTO();
        $objContatoDTO->retNumIdContato();
        $objContatoDTO->setStrSigla($strSiglaUsuario);
        $objContatoDTO->setStrNome($strNomeUsuario);
        $objContatoDTO->setNumIdTipoContato($numIdTipoContato);

        $objContatoRN = new ContatoRN();
        $objContatoDTO = $objContatoRN->consultarRN0324($objContatoDTO);

        if ($objContatoDTO == null) {

          $objContatoDTO = new ContatoDTO();

          $objContatoDTO->setNumIdContato(null);
          $objContatoDTO->setNumIdTipoContato($numIdTipoContato);
          $objContatoDTO->setNumIdContatoAssociado(null);
          $objContatoDTO->setStrStaNatureza(ContatoRN::$TN_PESSOA_FISICA);
          $objContatoDTO->setDblCnpj(null);
          $objContatoDTO->setNumIdCargo(null);
          $objContatoDTO->setStrSigla($strSiglaUsuario);
          $objContatoDTO->setStrNome($strNomeUsuario);
          $objContatoDTO->setDtaNascimento(null);
          $objContatoDTO->setStrStaGenero(null);
          $objContatoDTO->setDblCpf($dblCpfUsuario);
          $objContatoDTO->setDblRg(null);
          $objContatoDTO->setStrOrgaoExpedidor(null);
          $objContatoDTO->setStrMatricula(null);
          $objContatoDTO->setStrMatriculaOab(null);
          $objContatoDTO->setStrEndereco(null);
          $objContatoDTO->setStrComplemento(null);

          if ($strStaTipo == UsuarioRN::$TU_EXTERNO || $strStaTipo == UsuarioRN::$TU_EXTERNO_PENDENTE) {
            $objContatoDTO->setStrEmail($strSiglaUsuario);
          } else {
            $objContatoDTO->setStrEmail(null);
          }

          $objContatoDTO->setStrSitioInternet(null);
          $objContatoDTO->setStrTelefoneFixo(null);
          $objContatoDTO->setStrTelefoneCelular(null);
          $objContatoDTO->setStrBairro(null);
          $objContatoDTO->setNumIdUf(null);
          $objContatoDTO->setNumIdCidade(null);
          $objContatoDTO->setNumIdPais(null);
          $objContatoDTO->setStrCep(null);
          $objContatoDTO->setStrObservacao(null);
          $objContatoDTO->setStrSinEnderecoAssociado('N');
          $objContatoDTO->setStrSinAtivo('S');
          $objContatoDTO->setStrStaOperacao('REPLICACAO');

          $objContatoDTO = $objContatoRN->cadastrarRN0322($objContatoDTO);
        }

        BancoSEI::getInstance()->executarSql('update usuario set id_contato='.$objContatoDTO->getNumIdContato().' where id_usuario='.$numIdUsuario);
      }

    }catch(Exception $e){
      throw new InfraException('Erro tratando usuários sem contato.', $e);
    }
  }

  protected function fixSinalizadorSistemaControlado(){
    try {
      InfraDebug::getInstance()->gravar('ATUALIZANDO SINALIZADOR DE SISTEMA PARA TIPOS DE CONTATO...');

      $objInfraParametro = new InfraParametro(BancoSEI::getInstance());

      $objTipoContatoRN = new TipoContatoRN();

      $numIdTipoContato = $objInfraParametro->getValor('ID_TIPO_CONTATO_SISTEMAS', false);
      if (!InfraString::isBolVazia($numIdTipoContato)) {
        $objTipoContatoDTO = new TipoContatoDTO();
        $objTipoContatoDTO->setBolExclusaoLogica(false);
        $objTipoContatoDTO->setNumIdTipoContato($numIdTipoContato);
        if ($objTipoContatoRN->contarRN0353($objTipoContatoDTO)) {
          BancoSEI::getInstance()->executarSql('update tipo_contato set sin_sistema=\'S\' where id_tipo_contato=' . $numIdTipoContato);
        }
      }

      $rs = BancoSEI::getInstance()->consultarSql('select sigla from orgao order by sigla');

      $arrChavesTiposContatos = array('_ID_TIPO_CONTATO_USUARIOS','_ID_TIPO_CONTATO_UNIDADES','_ID_TIPO_CONTATO_USUARIOS_EXTERNOS');

      foreach($rs as $orgao){
        foreach($arrChavesTiposContatos as $strChaveTipoContato) {
          $numIdTipoContato = $objInfraParametro->getValor(BancoSEI::getInstance()->formatarLeituraStr($orgao['sigla']) . $strChaveTipoContato, false);
          if (!InfraString::isBolVazia($numIdTipoContato)) {
            $objTipoContatoDTO = new TipoContatoDTO();
            $objTipoContatoDTO->setBolExclusaoLogica(false);
            $objTipoContatoDTO->setNumIdTipoContato($numIdTipoContato);
            if ($objTipoContatoRN->contarRN0353($objTipoContatoDTO)) {
              BancoSEI::getInstance()->executarSql('update tipo_contato set sin_sistema=\'S\' where id_tipo_contato=' . $numIdTipoContato);
            }
          }
        }
      }

    }catch(Exception $e){
      throw new InfraException('Erro sinalizando tipos de contato de sistemas.', $e);
    }
  }

  protected function fixSinalizadorPesquisaControlado(){
    try {
      InfraDebug::getInstance()->gravar('ATUALIZANDO SINALIZADOR DE PESQUISA PARA TIPOS DE CONTATO...');

      $objInfraParametro = new InfraParametro(BancoSEI::getInstance());

      $numIdTipoContato = $objInfraParametro->getValor('ID_TIPO_CONTATO_SISTEMAS', false);
      if (!InfraString::isBolVazia($numIdTipoContato)) {
        BancoSEI::getInstance()->executarSql('update tipo_contato set sta_acesso=\''.TipoContatoRN::$TA_NENHUM.'\' where id_tipo_contato=' . $numIdTipoContato);
      }

      $numIdTipoContato = $objInfraParametro->getValor('ID_TIPO_CONTATO_TEMPORARIO', false);
      if (!InfraString::isBolVazia($numIdTipoContato)) {
        BancoSEI::getInstance()->executarSql('update tipo_contato set sta_acesso=\''.TipoContatoRN::$TA_CONSULTA_RESUMIDA.'\' where id_tipo_contato=' . $numIdTipoContato);
      }

      $numIdTipoContato = $objInfraParametro->getValor('ID_TIPO_CONTATO_OUVIDORIA', false);
      if (!InfraString::isBolVazia($numIdTipoContato)) {
        BancoSEI::getInstance()->executarSql('update tipo_contato set sta_acesso=\''.TipoContatoRN::$TA_NENHUM.'\' where id_tipo_contato=' . $numIdTipoContato);
      }

      $rs = BancoSEI::getInstance()->consultarSql('select sigla from orgao order by sigla');

      foreach($rs as $orgao){

        $strSiglaOrgao = BancoSEI::getInstance()->formatarLeituraStr($orgao['sigla']);

        $numIdTipoContato = $objInfraParametro->getValor($strSiglaOrgao . '_ID_TIPO_CONTATO_USUARIOS', false);
        if (!InfraString::isBolVazia($numIdTipoContato)) {
          BancoSEI::getInstance()->executarSql('update tipo_contato set sta_acesso=\''.TipoContatoRN::$TA_CONSULTA_RESUMIDA.'\' where id_tipo_contato=' . $numIdTipoContato);
        }

        $numIdTipoContato = $objInfraParametro->getValor($strSiglaOrgao . '_ID_TIPO_CONTATO_USUARIOS_EXTERNOS', false);
        if (!InfraString::isBolVazia($numIdTipoContato)) {
          BancoSEI::getInstance()->executarSql('update tipo_contato set sta_acesso=\''.TipoContatoRN::$TA_CONSULTA_RESUMIDA.'\' where id_tipo_contato=' . $numIdTipoContato);
        }

        $numIdTipoContato = $objInfraParametro->getValor($strSiglaOrgao . '_ID_TIPO_CONTATO_UNIDADES', false);
        if (!InfraString::isBolVazia($numIdTipoContato)) {
          BancoSEI::getInstance()->executarSql('update tipo_contato set sta_acesso=\''.TipoContatoRN::$TA_CONSULTA_COMPLETA.'\' where id_tipo_contato=' . $numIdTipoContato);
        }
      }

    }catch(Exception $e){
      throw new InfraException('Erro sinalizando tipo de pesquisa de contatos.', $e);
    }
  }

  protected function fixAtualizarContatosUnidadesControlado(){
    try {
      InfraDebug::getInstance()->gravar('ATUALIZANDO CONTATOS DE UNIDADES...');

      $rs = BancoSEI::getInstance()->consultarSql('select '.
          BancoSEI::getInstance()->formatarSelecaoNum('unidade', 'id_contato', 'idcontatounidade').','.
          BancoSEI::getInstance()->formatarSelecaoStr('unidade', 'endereco', 'enderecounidade').','.
          BancoSEI::getInstance()->formatarSelecaoStr('unidade', 'complemento', 'complementounidade').','.
          BancoSEI::getInstance()->formatarSelecaoStr('unidade', 'bairro', 'bairrounidade').','.
          BancoSEI::getInstance()->formatarSelecaoNum('unidade', 'id_uf', 'idufunidade').','.
          BancoSEI::getInstance()->formatarSelecaoNum('unidade', 'id_cidade', 'idcidadeunidade').','.
          BancoSEI::getInstance()->formatarSelecaoStr('unidade', 'cep', 'cepunidade').','.
          BancoSEI::getInstance()->formatarSelecaoStr('unidade', 'telefone', 'telefoneunidade').','.
          BancoSEI::getInstance()->formatarSelecaoStr('unidade', 'sitio_internet', 'sitiointernetunidade').','.
          BancoSEI::getInstance()->formatarSelecaoStr('unidade', 'observacao', 'observacaounidade').
          ' from unidade');

        $objPaisDTO = new PaisDTO();
        $objPaisDTO->retNumIdPais();
        $objPaisDTO->retStrNome();

        $objPaisRN = new PaisRN();
        $arrObjPaisDTO = $objPaisRN->listar($objPaisDTO);

        $numIdPaisBrasil = null;
        foreach($arrObjPaisDTO as $objPaisDTO){
          if (InfraString::transformarCaixaAlta($objPaisDTO->getStrNome())=='BRASIL'){
            $numIdPaisBrasil = $objPaisDTO->getNumIdPais();
            break;
          }
        }

        InfraDebug::getInstance()->setBolDebugInfra(false);
        foreach($rs as $unidade){
          BancoSEI::getInstance()->executarSql('update contato set '.
  'endereco='.BancoSEI::getInstance()->formatarGravacaoStr(BancoSEI::getInstance()->formatarLeituraStr($unidade['enderecounidade'])).','.
  'complemento='.BancoSEI::getInstance()->formatarGravacaoStr(BancoSEI::getInstance()->formatarLeituraStr($unidade['complementounidade'])).','.
  'bairro='.BancoSEI::getInstance()->formatarGravacaoStr(BancoSEI::getInstance()->formatarLeituraStr($unidade['bairrounidade'])).','.
  'id_uf='.BancoSEI::getInstance()->formatarGravacaoNum(BancoSEI::getInstance()->formatarLeituraNum($unidade['idufunidade'])).','.
  'id_cidade='.BancoSEI::getInstance()->formatarGravacaoNum(BancoSEI::getInstance()->formatarLeituraNum($unidade['idcidadeunidade'])).','.
  'id_pais='.BancoSEI::getInstance()->formatarGravacaoNum($numIdPaisBrasil).','.
  'cep='.BancoSEI::getInstance()->formatarGravacaoStr(BancoSEI::getInstance()->formatarLeituraStr($unidade['cepunidade'])).','.
  'telefone_fixo='.BancoSEI::getInstance()->formatarGravacaoStr(BancoSEI::getInstance()->formatarLeituraStr($unidade['telefoneunidade'])).','.
  'sitio_internet='.BancoSEI::getInstance()->formatarGravacaoStr(BancoSEI::getInstance()->formatarLeituraStr($unidade['sitiointernetunidade'])).','.
  'observacao='.BancoSEI::getInstance()->formatarGravacaoStr(BancoSEI::getInstance()->formatarLeituraStr($unidade['observacaounidade'])).
              ' where id_contato='.BancoSEI::getInstance()->formatarLeituraNum($unidade['idcontatounidade']));
        }
        InfraDebug::getInstance()->setBolDebugInfra(true);

    }catch(Exception $e){
      throw new InfraException('Erro atualizando contatos de unidades.', $e);
    }
  }

  protected function fixCriarOrgaosContatosControlado(){
    try {
      InfraDebug::getInstance()->gravar('CRIANDO CONTATOS PARA ORGAOS...');

      $rs = BancoSEI::getInstance()->consultarSql('select '.
          BancoSEI::getInstance()->formatarSelecaoNum('orgao', 'id_orgao', 'idorgao').','.
          BancoSEI::getInstance()->formatarSelecaoStr('orgao', 'sigla', 'siglaorgao').','.
          BancoSEI::getInstance()->formatarSelecaoStr('orgao', 'descricao', 'descricaoorgao').','.
          BancoSEI::getInstance()->formatarSelecaoStr('orgao', 'endereco', 'enderecoorgao').','.
          BancoSEI::getInstance()->formatarSelecaoStr('orgao', 'complemento', 'complementoorgao').','.
          BancoSEI::getInstance()->formatarSelecaoStr('orgao', 'bairro', 'bairroorgao').','.
          BancoSEI::getInstance()->formatarSelecaoNum('uf', 'id_uf', 'iduforgao').','.
          BancoSEI::getInstance()->formatarSelecaoNum('cidade', 'id_cidade', 'idcidadeorgao').','.
          BancoSEI::getInstance()->formatarSelecaoStr('orgao', 'cep', 'ceporgao').','.
          BancoSEI::getInstance()->formatarSelecaoStr('orgao', 'telefone', 'telefoneorgao').','.
          BancoSEI::getInstance()->formatarSelecaoStr('orgao', 'email', 'emailorgao').','.
          BancoSEI::getInstance()->formatarSelecaoStr('orgao', 'sitio_internet', 'sitiointernetorgao').
          ' from orgao left join (cidade left join uf on cidade.id_uf=uf.id_uf) on orgao.id_cidade=cidade.id_cidade');

      $objInfraParametro = new InfraParametro(BancoSEI::getInstance());

      $objTipoContatoDTO = new TipoContatoDTO();
      $objTipoContatoDTO->setNumIdTipoContato(null);
      $objTipoContatoDTO->setStrNome('Órgãos');
      $objTipoContatoDTO->setStrDescricao('Órgãos');
      $objTipoContatoDTO->setStrStaAcesso(TipoContatoRN::$TA_CONSULTA_COMPLETA);
      $objTipoContatoDTO->setStrSinSistema('S');
      $objTipoContatoDTO->setStrSinAtivo('S');

      $objTipoContatoRN = new TipoContatoRN();
      $objTipoContatoDTO = $objTipoContatoRN->cadastrarRN0334($objTipoContatoDTO);

      $numIdTipoContato = $objTipoContatoDTO->getNumIdTipoContato();

      $objRelUnidadeTipoContatoDTO = new RelUnidadeTipoContatoDTO();
      $objRelUnidadeTipoContatoDTO->setNumIdTipoContato($objTipoContatoDTO->getNumIdTipoContato());
      $objRelUnidadeTipoContatoDTO->setNumIdUnidade($objInfraParametro->getValor('ID_UNIDADE_TESTE'));
      $objRelUnidadeTipoContatoDTO->setStrStaAcesso(TipoContatoRN::$TA_ALTERACAO);

      $objRelUnidadeTipoContatoRN = new RelUnidadeTipoContatoRN();
      $objRelUnidadeTipoContatoRN->cadastrarRN0545($objRelUnidadeTipoContatoDTO);

      BancoSEI::getInstance()->executarSql('insert into infra_parametro (nome,valor) values (\'ID_TIPO_CONTATO_ORGAOS\',\''.$numIdTipoContato.'\')');

      $objPaisDTO = new PaisDTO();
      $objPaisDTO->retNumIdPais();
      $objPaisDTO->retStrNome();

      $objPaisRN = new PaisRN();
      $arrObjPaisDTO = $objPaisRN->listar($objPaisDTO);

      $numIdPaisBrasil = null;
      foreach($arrObjPaisDTO as $objPaisDTO){
        if (InfraString::transformarCaixaAlta($objPaisDTO->getStrNome())=='BRASIL'){
          $numIdPaisBrasil = $objPaisDTO->getNumIdPais();
          break;
        }
      }

      $objContatoRN = new ContatoRN();

      foreach($rs as $orgao) {

        $objContatoDTO = new ContatoDTO();

        $objContatoDTO->setNumIdContato(null);
        $objContatoDTO->setNumIdTipoContato($numIdTipoContato);
        $objContatoDTO->setNumIdContatoAssociado(null);
        $objContatoDTO->setStrStaNatureza(ContatoRN::$TN_PESSOA_JURIDICA);
        $objContatoDTO->setDblCnpj(null);
        $objContatoDTO->setNumIdCargo(null);
        $objContatoDTO->setStrSigla(BancoSEI::getInstance()->formatarLeituraStr($orgao['siglaorgao']));
        $objContatoDTO->setStrNome(BancoSEI::getInstance()->formatarLeituraStr($orgao['descricaoorgao']));
        $objContatoDTO->setDtaNascimento(null);
        $objContatoDTO->setStrStaGenero(null);
        $objContatoDTO->setDblCpf(null);
        $objContatoDTO->setDblRg(null);
        $objContatoDTO->setStrOrgaoExpedidor(null);
        $objContatoDTO->setStrMatricula(null);
        $objContatoDTO->setStrMatriculaOab(null);
        $objContatoDTO->setStrEndereco(BancoSEI::getInstance()->formatarLeituraStr($orgao['enderecoorgao']));
        $objContatoDTO->setStrComplemento(BancoSEI::getInstance()->formatarLeituraStr($orgao['complementoorgao']));
        $objContatoDTO->setStrEmail(BancoSEI::getInstance()->formatarLeituraStr($orgao['emailorgao']));
        $objContatoDTO->setStrSitioInternet(BancoSEI::getInstance()->formatarLeituraStr($orgao['sitiointernetorgao']));
        $objContatoDTO->setStrTelefoneFixo(BancoSEI::getInstance()->formatarLeituraStr($orgao['telefoneorgao']));
        $objContatoDTO->setStrTelefoneCelular(null);
        $objContatoDTO->setStrBairro(BancoSEI::getInstance()->formatarLeituraStr($orgao['bairroorgao']));
        $objContatoDTO->setNumIdUf(BancoSEI::getInstance()->formatarLeituraNum($orgao['iduforgao']));
        $objContatoDTO->setNumIdCidade(BancoSEI::getInstance()->formatarLeituraNum($orgao['idcidadeorgao']));
        $objContatoDTO->setNumIdPais($numIdPaisBrasil);
        $objContatoDTO->setStrCep(BancoSEI::getInstance()->formatarLeituraStr($orgao['ceporgao']));
        $objContatoDTO->setStrObservacao(null);
        $objContatoDTO->setStrSinEnderecoAssociado('N');
        $objContatoDTO->setStrSinAtivo('S');
        $objContatoDTO->setStrStaOperacao('REPLICACAO');

        $objContatoDTO = $objContatoRN->cadastrarRN0322($objContatoDTO);

        BancoSEI::getInstance()->executarSql('update orgao set id_contato='.$objContatoDTO->getNumIdContato().' where id_orgao='.BancoSEI::getInstance()->formatarLeituraNum($orgao['idorgao']));
      }

    }catch(Exception $e){
      throw new InfraException('Erro criando contatos para órgãos.', $e);
    }
  }

  public function fixContatoCidadeUfPais(){
    try {
      InfraDebug::getInstance()->gravar('ATUALIZANDO CIDADE, UF E PAIS EM CONTATOS...');

      $rs = BancoSEI::getInstance()->consultarSql('select '.
          BancoSEI::getInstance()->formatarSelecaoNum('contato', 'id_contato', 'idcontato').','.
          BancoSEI::getInstance()->formatarSelecaoStr('contato', 'nome_cidade', 'nomecidadecontato').','.
          BancoSEI::getInstance()->formatarSelecaoStr('contato', 'sigla_estado', 'siglaestadocontato').','.
          BancoSEI::getInstance()->formatarSelecaoStr('contato', 'nome_pais', 'nomepaiscontato').
          ' from contato');

      $objPaisDTO = new PaisDTO();
      $objPaisDTO->retNumIdPais();
      $objPaisDTO->retStrNome();

      $objPaisRN = new PaisRN();
      $arrObjPaisDTO = $objPaisRN->listar($objPaisDTO);

      $numIdPaisBrasil = null;
      foreach($arrObjPaisDTO as $objPaisDTO){
        $objPaisDTO->setStrNome(InfraString::transformarCaixaAlta($objPaisDTO->getStrNome()));

        if ($objPaisDTO->getStrNome()=='BRASIL'){
          $numIdPaisBrasil = $objPaisDTO->getNumIdPais();
        }
      }

      $arrObjPaisDTO = InfraArray::indexarArrInfraDTO($arrObjPaisDTO,'Nome');

      $objUfDTO = new UfDTO();
      $objUfDTO->retNumIdPais();
      $objUfDTO->retNumIdUf();
      $objUfDTO->retStrSigla();

      $objUfRN = new UfRN();
      $arrObjUfDTO = $objUfRN->listarRN0401($objUfDTO);

      foreach($arrObjUfDTO as $objUfDTO){
        $objUfDTO->setStrSigla(InfraString::transformarCaixaAlta($objUfDTO->getStrSigla()));
      }

      $arrObjUfDTO = InfraArray::indexarArrInfraDTO($arrObjUfDTO,'Sigla',true);

      $objCidadeDTO = new CidadeDTO();
      $objCidadeDTO->retNumIdPais();
      $objCidadeDTO->retNumIdUf();
      $objCidadeDTO->retNumIdCidade();
      $objCidadeDTO->retStrNome();

      $objCidadeRN = new CidadeRN();
      $arrObjCidadeDTO = $objCidadeRN->listarRN0410($objCidadeDTO);

      foreach($arrObjCidadeDTO as $objCidadeDTO){
        $objCidadeDTO->setStrNome(InfraString::transformarCaixaAlta($objCidadeDTO->getStrNome()));
      }

      $arrObjCidadeDTO = InfraArray::indexarArrInfraDTO($arrObjCidadeDTO,'Nome',true);

      $numRegistros = count($rs);
      $n = 0;
      InfraDebug::getInstance()->setBolDebugInfra(false);
      foreach($rs as $contato) {

        if ((++$n >=1000 && $n%1000==0) || $n==$numRegistros){
          InfraDebug::getInstance()->gravar('CONTATOS: '.$n.' DE '.$numRegistros);
        }

        $numIdContato = BancoSEI::getInstance()->formatarLeituraNum($contato['idcontato']);
        $strNomeCidade = trim(InfraString::transformarCaixaAlta(BancoSEI::getInstance()->formatarLeituraStr($contato['nomecidadecontato'])));
        $strSiglaEstado = trim(InfraString::transformarCaixaAlta(BancoSEI::getInstance()->formatarLeituraStr($contato['siglaestadocontato'])));
        $strNomePais = trim(InfraString::transformarCaixaAlta(BancoSEI::getInstance()->formatarLeituraStr($contato['nomepaiscontato'])));

        $numIdPais = $numIdPaisBrasil;
        if ($strNomePais!=null && isset($arrObjPaisDTO[$strNomePais])){
          $numIdPais = $arrObjPaisDTO[$strNomePais]->getNumIdPais();
        }

        $numIdUf = null;
        if ($strSiglaEstado!=null && isset($arrObjUfDTO[$strSiglaEstado])){
          foreach($arrObjUfDTO[$strSiglaEstado] as $objUfDTO){
            if ($objUfDTO->getNumIdPais()==$numIdPais){
              $numIdUf = $objUfDTO->getNumIdUf();
              break;
            }
          }
        }

        $numIdCidade = null;
        if ($strNomeCidade!=null && isset($arrObjCidadeDTO[$strNomeCidade])){
          foreach($arrObjCidadeDTO[$strNomeCidade] as $objCidadeDTO){
            if ($objCidadeDTO->getNumIdPais()==$numIdPais && ($numIdUf==null || $numIdUf==$objCidadeDTO->getNumIdUf())){
              $numIdCidade = $objCidadeDTO->getNumIdCidade();
              break;
            }
          }
        }

        BancoSEI::getInstance()->executarSql('update contato set id_pais='.BancoSEI::getInstance()->formatarGravacaoNum($numIdPais).', id_uf='.BancoSEI::getInstance()->formatarGravacaoNum($numIdUf).',id_cidade='.BancoSEI::getInstance()->formatarGravacaoNum($numIdCidade).' where id_contato='.BancoSEI::getInstance()->formatarGravacaoNum($numIdContato));
      }
      InfraDebug::getInstance()->setBolDebugInfra(true);

    }catch(Exception $e){
      throw new InfraException('Erro atualizando cidade, uf e pais em contatos.', $e);
    }
  }

  protected function fixAssociarUsuariosOrgaosControlado(){
    try {
      InfraDebug::getInstance()->gravar('ASSOCIANDO CONTATOS DE ORGAOS E USUARIOS...');

      $objUsuarioDTO = new UsuarioDTO();
      $objUsuarioDTO->setBolExclusaoLogica(false);
      $objUsuarioDTO->retNumIdOrgao();
      $objUsuarioDTO->retNumIdContato();
      $objUsuarioDTO->setStrStaTipo(UsuarioRN::$TU_SIP);

      $objUsuarioRN = new UsuarioRN();
      $arrObjUsuarioDTO = $objUsuarioRN->listarRN0490($objUsuarioDTO);

      $objOrgaoDTO = new OrgaoDTO();
      $objOrgaoDTO->setBolExclusaoLogica(false);
      $objOrgaoDTO->retNumIdOrgao();
      $objOrgaoDTO->retNumIdContato();

      $objOrgaoRN = new OrgaoRN();
      $arrObjOrgaoDTO = InfraArray::indexarArrInfraDTO($objOrgaoRN->listarRN1353($objOrgaoDTO),'IdOrgao');

      $objContatoBD = new ContatoBD(BancoSEI::getInstance());

      $numRegistros = count($arrObjUsuarioDTO);
      $n = 0;

      InfraDebug::getInstance()->setBolDebugInfra(false);
      foreach($arrObjUsuarioDTO as $objUsuarioDTO) {

        if ((++$n >=500 && $n%500==0) || $n==$numRegistros){
          InfraDebug::getInstance()->gravar('USUARIOS: '.$n.' DE '.$numRegistros);
        }

        $dto = new ContatoDTO();
        $dto->setNumIdContatoAssociado($arrObjOrgaoDTO[$objUsuarioDTO->getNumIdOrgao()]->getNumIdContato());
        $dto->setStrSinEnderecoAssociado('S');
        $dto->setNumIdContato($objUsuarioDTO->getNumIdContato());
        $objContatoBD->alterar($dto);
      }
      InfraDebug::getInstance()->setBolDebugInfra(true);

    }catch(Exception $e){
      throw new InfraException('Erro associando contatos de usuários com órgãos.', $e);
    }
  }

  protected function fixAssociarUnidadesOrgaosControlado(){
        try {
            InfraDebug::getInstance()->gravar('ASSOCIANDO CONTATOS DE ORGAOS E UNIDADES...');

            $objUnidadeDTO = new UnidadeDTO();
            $objUnidadeDTO->setBolExclusaoLogica(false);
            $objUnidadeDTO->retNumIdOrgao();
            $objUnidadeDTO->retNumIdContato();

            $objUnidadeRN = new UnidadeRN();
            $arrObjUnidadeDTO = $objUnidadeRN->listarRN0127($objUnidadeDTO);

            $objOrgaoDTO = new OrgaoDTO();
            $objOrgaoDTO->setBolExclusaoLogica(false);
            $objOrgaoDTO->retNumIdOrgao();
            $objOrgaoDTO->retNumIdContato();

            $objOrgaoRN = new OrgaoRN();
            $arrObjOrgaoDTO = InfraArray::indexarArrInfraDTO($objOrgaoRN->listarRN1353($objOrgaoDTO),'IdOrgao');

            $objContatoBD = new ContatoBD(BancoSEI::getInstance());

            $numRegistros = count($arrObjUnidadeDTO);
            $n = 0;

            InfraDebug::getInstance()->setBolDebugInfra(false);
            foreach($arrObjUnidadeDTO as $objUnidadeDTO) {

                if ((++$n >=100 && $n%100==0) || $n==$numRegistros){
                    InfraDebug::getInstance()->gravar('UNIDADES: '.$n.' DE '.$numRegistros);
                }

                $dto = new ContatoDTO();
                $dto->setNumIdContatoAssociado($arrObjOrgaoDTO[$objUnidadeDTO->getNumIdOrgao()]->getNumIdContato());
                $dto->setStrSinEnderecoAssociado('N');
                $dto->setNumIdContato($objUnidadeDTO->getNumIdContato());
                $objContatoBD->alterar($dto);
            }
            InfraDebug::getInstance()->setBolDebugInfra(true);

        }catch(Exception $e){
            throw new InfraException('Erro associando contatos de unidades com órgãos.', $e);
        }
    }

  public function fixControleInterno(){
    try {

      InfraDebug::getInstance()->gravar('PROCESSANDO DADOS DE CONTROLE INTERNO...');

      //obtem processos restritos com acesso automatico
      $objAtividadeDTO = new AtividadeDTO();
      $objAtividadeDTO->setDistinct(true);
      $objAtividadeDTO->retDblIdProtocolo();
      $objAtividadeDTO->setStrStaNivelAcessoGlobalProtocolo(ProtocoloRN::$NA_RESTRITO);
      $objAtividadeDTO->setNumIdTarefa(49);

      $objAtividadeRN = new AtividadeRN();
      $arrIdProcessos = InfraArray::converterArrInfraDTO($objAtividadeRN->listarRN0036($objAtividadeDTO),'IdProtocolo');

      $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();

      $numRegistros = count($arrIdProcessos);
      $n = 0;
      InfraDebug::getInstance()->setBolDebugInfra(false);

      InfraDebug::getInstance()->gravar('REMOVENDO DADOS DE CONTROLE INTERNO ANTIGOS DE PROCESSOS...');

      foreach($arrIdProcessos as $dblIdProcesso) {

        if ((++$n >=100 && $n%100==0) || $n==$numRegistros){
          InfraDebug::getInstance()->gravar($n.' DE '.$numRegistros);
        }

        //busca processos anexados
        $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
        $objRelProtocoloProtocoloDTO->retDblIdProtocolo2();
        $objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);
        $objRelProtocoloProtocoloDTO->setDblIdProtocolo1($dblIdProcesso);
        $arrObjRelProtocoloProtocoloDTO = $objRelProtocoloProtocoloRN->listarRN0187($objRelProtocoloProtocoloDTO);

        $arrIdProtocolos = InfraArray::converterArrInfraDTO($arrObjRelProtocoloProtocoloDTO, 'IdProtocolo2');
        $arrIdProtocolos[] = $dblIdProcesso;

        //busca unidades de controle com acesso neste processo
        $objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->retNumIdUnidade();
        $objAtividadeDTO->setNumIdTarefa(49);
        $objAtividadeDTO->setDblIdProtocolo($dblIdProcesso);
        $arrIdUnidades = InfraArray::converterArrInfraDTO($objAtividadeRN->listarRN0036($objAtividadeDTO), 'IdUnidade');

        //remove andamentos de acesso automatico do processo
        BancoSEI::getInstance()->executarSql('delete from atividade where id_protocolo='.$dblIdProcesso.' and id_tarefa=49');

        //busca outros andamentos das unidades (se existirem)
        $objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->setDistinct(true);
        $objAtividadeDTO->retNumIdUnidade();
        $objAtividadeDTO->setDblIdProtocolo($dblIdProcesso);
        $objAtividadeDTO->setNumIdUnidade($arrIdUnidades,InfraDTO::$OPER_IN);
        $arrIdUnidadesOutroAndamento = InfraArray::converterArrInfraDTO($objAtividadeRN->listarRN0036($objAtividadeDTO), 'IdUnidade');

        //remove acessos das unidades que nao possuem outros andamentos
        foreach ($arrIdUnidades as $numIdUnidade) {
          if (!in_array($numIdUnidade,$arrIdUnidadesOutroAndamento)){
            BancoSEI::getInstance()->executarSql('delete from acesso where sta_tipo=\'R\' and id_unidade=' . $numIdUnidade . ' and id_protocolo='.$dblIdProcesso);
          }
        }
      }
      InfraDebug::getInstance()->setBolDebugInfra(true);

      //remove andamentos de acesso automatico de processos com nivel de acesso publico/sigiloso
      BancoSEI::getInstance()->executarSql('delete from atividade where id_tarefa=49');

      BancoSEI::getInstance()->executarSql('delete from tarefa where id_tarefa=49');

      $objControleInternoDTO = new ControleInternoDTO();
      $objControleInternoDTO->setDistinct(true);
      $objControleInternoDTO->retNumIdUnidadeControle();
      $objControleInternoDTO->retNumIdOrgaoControlado();
      $objControleInternoDTO->retNumIdTipoProcedimentoControlado();
      $objControleInternoDTO->setNumIdTipoProcedimentoControlado(null, InfraDTO::$OPER_DIFERENTE);

      $objControleInternoRN = new ControleInternoRN();
      $arrObjControleInternoDTO = InfraArray::indexarArrInfraDTO($objControleInternoRN->listar($objControleInternoDTO),'IdUnidadeControle',true);


      $objProcedimentoRN = new ProcedimentoRN();
      $objDocumentoRN = new DocumentoRN();
      $objAcessoRN = new AcessoRN();

      $arrProcessosControleInterno = array();

      InfraDebug::getInstance()->setBolDebugInfra(false);

      foreach($arrObjControleInternoDTO as $numIdUnidade => $arrObjControleInternoDTOUnidade) {

        foreach ($arrObjControleInternoDTOUnidade as $objControleInternoDTO) {

          InfraDebug::getInstance()->gravar('CRITERIO TIPO DE PROCESSO: '.$numIdUnidade.' / '.$objControleInternoDTO->getNumIdOrgaoControlado().' / '.$objControleInternoDTO->getNumIdTipoProcedimentoControlado());

          $objProcedimentoDTO = new ProcedimentoDTO();
          $objProcedimentoDTO->retDblIdProcedimento();
          $objProcedimentoDTO->setNumIdTipoProcedimento($objControleInternoDTO->getNumIdTipoProcedimentoControlado());
          $objProcedimentoDTO->setNumIdOrgaoUnidadeGeradoraProtocolo($objControleInternoDTO->getNumIdOrgaoControlado());
          $objProcedimentoDTO->setStrStaNivelAcessoGlobalProtocolo(ProtocoloRN::$NA_RESTRITO);
          $arrIdProcedimentoPartes = array_chunk(InfraArray::converterArrInfraDTO($objProcedimentoRN->listarRN0278($objProcedimentoDTO), 'IdProcedimento'), 100);

          if (count($arrIdProcedimentoPartes)) {

            $dto = new ControleInternoDTO();
            $dto->retNumIdControleInterno();
            $dto->setNumIdOrgaoControlado($objControleInternoDTO->getNumIdOrgaoControlado());
            $dto->setNumIdTipoProcedimentoControlado($objControleInternoDTO->getNumIdTipoProcedimentoControlado());
            $dto->setNumIdUnidadeControle($numIdUnidade);
            $arr = $objControleInternoRN->listar($dto);

            foreach($arr as $dto) {
              foreach ($arrIdProcedimentoPartes as $arrIdProcedimento) {
                $arrObjAcessoDTO = array();
                foreach ($arrIdProcedimento as $dblIdProcedimento) {
                  if (!isset($arrProcessosControleInterno[$dblIdProcedimento][$numIdUnidade][$dto->getNumIdControleInterno()])) {
                    $objAcessoDTO = new AcessoDTO();
                    $objAcessoDTO->setNumIdAcesso(null);
                    $objAcessoDTO->setNumIdUnidade($numIdUnidade);
                    $objAcessoDTO->setNumIdUsuario(null);
                    $objAcessoDTO->setDblIdProtocolo($dblIdProcedimento);
                    $objAcessoDTO->setNumIdControleInterno($dto->getNumIdControleInterno());
                    $objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CONTROLE_INTERNO);
                    $arrObjAcessoDTO[] = $objAcessoDTO;

                    $arrProcessosControleInterno[$dblIdProcedimento][$numIdUnidade][$dto->getNumIdControleInterno()] = 0;
                  }
                }
                $objAcessoRN->cadastrarMultiplo($arrObjAcessoDTO);
              }
            }
          }
        }
      }

      $objControleInternoDTO = new ControleInternoDTO();
      $objControleInternoDTO->setDistinct(true);
      $objControleInternoDTO->retNumIdUnidadeControle();
      $objControleInternoDTO->retNumIdOrgaoControlado();
      $objControleInternoDTO->retNumIdSerieControlada();
      $objControleInternoDTO->setNumIdSerieControlada(null,InfraDTO::$OPER_DIFERENTE);

      $objControleInternoRN = new ControleInternoRN();
      $arrObjControleInternoDTO = InfraArray::indexarArrInfraDTO($objControleInternoRN->listar($objControleInternoDTO),'IdUnidadeControle',true);

      foreach($arrObjControleInternoDTO as $numIdUnidade => $arrObjControleInternoDTOUnidade) {

        foreach ($arrObjControleInternoDTOUnidade as $objControleInternoDTO) {

          InfraDebug::getInstance()->gravar('CRITERIO TIPO DE DOCUMENTO: '.$numIdUnidade.' / '.$objControleInternoDTO->getNumIdOrgaoControlado().' / '.$objControleInternoDTO->getNumIdSerieControlada());

          $objDocumentoDTO = new DocumentoDTO();
          $objDocumentoDTO->setDistinct(true);
          $objDocumentoDTO->retDblIdProcedimento();
          $objDocumentoDTO->setNumIdSerie($objControleInternoDTO->getNumIdSerieControlada());
          $objDocumentoDTO->setNumIdOrgaoUnidadeGeradoraProtocolo($objControleInternoDTO->getNumIdOrgaoControlado());
          $objDocumentoDTO->setStrStaNivelAcessoGlobalProtocolo(ProtocoloRN::$NA_RESTRITO);
          $arrIdProcedimentoPartes = array_chunk(InfraArray::converterArrInfraDTO($objDocumentoRN->listarRN0008($objDocumentoDTO),'IdProcedimento'),100);

          if (count($arrIdProcedimentoPartes)) {

            $dto = new ControleInternoDTO();
            $dto->retNumIdControleInterno();
            $dto->setNumIdOrgaoControlado($objControleInternoDTO->getNumIdOrgaoControlado());
            $dto->setNumIdSerieControlada($objControleInternoDTO->getNumIdSerieControlada());
            $dto->setNumIdUnidadeControle($numIdUnidade);
            $arr = $objControleInternoRN->listar($dto);

            foreach($arr as $dto) {

              foreach ($arrIdProcedimentoPartes as $arrIdProcedimento) {
                $arrObjAcessoDTO = array();
                foreach ($arrIdProcedimento as $dblIdProcedimento) {
                  if (!isset($arrProcessosControleInterno[$dblIdProcedimento][$numIdUnidade][$dto->getNumIdControleInterno()])) {
                    $objAcessoDTO = new AcessoDTO();
                    $objAcessoDTO->setNumIdAcesso(null);
                    $objAcessoDTO->setNumIdUnidade($numIdUnidade);
                    $objAcessoDTO->setNumIdUsuario(null);
                    $objAcessoDTO->setDblIdProtocolo($dblIdProcedimento);
                    $objAcessoDTO->setNumIdControleInterno($dto->getNumIdControleInterno());
                    $objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CONTROLE_INTERNO);
                    $arrObjAcessoDTO[] = $objAcessoDTO;

                    $arrProcessosControleInterno[$dblIdProcedimento][$numIdUnidade][$dto->getNumIdControleInterno()] = 0;
                  }
                }
                $objAcessoRN->cadastrarMultiplo($arrObjAcessoDTO);
              }
            }
          }
        }
      }

      $numRegistros = count($arrProcessosControleInterno);

      $n = 0;
      foreach($arrProcessosControleInterno as $dblIdProcedimento => $arrIdUnidades) {

        if ((++$n >=500 && $n%500==0) || $n==$numRegistros){
          InfraDebug::getInstance()->gravar('VERIFICANDO PROCESSOS ANEXADOS: '.$n.' DE '.$numRegistros);
        }

        $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
        $objRelProtocoloProtocoloDTO->retDblIdProtocolo1();
        $objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);
        $objRelProtocoloProtocoloDTO->setDblIdProtocolo2($dblIdProcedimento);
        $objRelProtocoloProtocoloDTO = $objRelProtocoloProtocoloRN->consultarRN0841($objRelProtocoloProtocoloDTO);

        if ($objRelProtocoloProtocoloDTO!=null){
          $dblIdProcessoPai = $objRelProtocoloProtocoloDTO->getDblIdProtocolo1();
        }else{
          $dblIdProcessoPai = $dblIdProcedimento;
        }

        $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
        $objRelProtocoloProtocoloDTO->retDblIdProtocolo2();
        $objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);
        $objRelProtocoloProtocoloDTO->setDblIdProtocolo1($dblIdProcessoPai);

        $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
        $arrIdProcessos = InfraArray::converterArrInfraDTO($objRelProtocoloProtocoloRN->listarRN0187($objRelProtocoloProtocoloDTO), 'IdProtocolo2');

        $arrIdProcessos[] = $dblIdProcessoPai;

        foreach($arrIdProcessos as $dblIdProcessosAnexosOuAnexados) {
          foreach (array_keys($arrIdUnidades) as $numIdUnidade) {
            foreach(array_keys($arrIdUnidades[$numIdUnidade]) as $numIdControleInterno) {
              if (!isset($arrProcessosControleInterno[$dblIdProcessosAnexosOuAnexados][$numIdUnidade][$numIdControleInterno])) {

                $objAcessoDTO = new AcessoDTO();
                $objAcessoDTO->setNumIdAcesso(null);
                $objAcessoDTO->setNumIdUnidade($numIdUnidade);
                $objAcessoDTO->setNumIdUsuario(null);
                $objAcessoDTO->setDblIdProtocolo($dblIdProcessosAnexosOuAnexados);
                $objAcessoDTO->setNumIdControleInterno($numIdControleInterno);
                $objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CONTROLE_INTERNO);
                $objAcessoRN->cadastrar($objAcessoDTO);
                $arrProcessosControleInterno[$dblIdProcessosAnexosOuAnexados][$numIdUnidade][$numIdControleInterno] = 0;
              }
            }
          }
        }
      }

      InfraDebug::getInstance()->setBolDebugInfra(true);

    }catch(Exception $e){
      throw new InfraException('Erro processando dados de Controle Interno.', $e);
    }
  }

  protected function fixPopularCargoTratamentoVocativoControlado(){

    try{

      InfraDebug::getInstance()->gravar('POPULANDO CARGOS, TRATAMENTOS E VOCATIVOS...');

      InfraDebug::getInstance()->setBolDebugInfra(false);

    $strConteudo = 'A Sua Excelência o Senhor;Almirante da Marinha do Brasil;Senhor Almirante;M
A Sua Excelência o Senhor;Brigadeiro da Força Aérea Brasileira;Senhor Brigadeiro;M
Ao Senhor;Chefe de Gabinete;Senhor Chefe de Gabinete;M
Ao Senhor;Cidadão;Senhor;M
A Senhora;Cidadão;Senhora;F
A Sua Excelência o Senhor;Cônsul;Senhor Cônsul;M
A Sua Excelência a Senhora;Consulesa;Senhora Consulesa;F
Ao Senhor;Coordenador;Senhor Coordenador;M
A Senhora;Coordenadora;Senhora Coordenadora;F
A Senhora;Coordenadora-Geral;Senhora Coordenadora-Geral;F
Ao Senhor;Coordenador-Geral;Senhor Coordenador-Geral;M
A Sua Excelência a Senhora;Delegada de Polícia;Senhora Delegada;F
A Sua Excelência a Senhora;Delegada de Polícia Federal;Senhora Delegada;F
A Sua Excelência o Senhor;Delegado de Polícia;Senhor Delegado;M
A Sua Excelência o Senhor;Delegado de Polícia Federal;Senhor Delegado;M
A Sua Excelência a Senhora;Deputada Estadual;Senhora Deputada;F
A Sua Excelência a Senhora;Deputada Federal;Senhora Deputada;F
A Sua Excelência o Senhor;Deputado Estadual;Senhor Deputado;M
A Sua Excelência o Senhor;Deputado Federal;Senhor Deputado;M
A Sua Excelência o Senhor;Desembargador de Justiça;Senhor Desembargador;M
A Sua Excelência o Senhor;Desembargador Federal;Senhor Desembargador;M
A Sua Excelência a Senhora;Desembargadora de Justiça;Senhora Desembargadora;F
A Sua Excelência a Senhora;Desembargadora Federal;Senhora Desembargadora;F
Ao Senhor;Diretor;Senhor Diretor;M
A Senhora;Diretora;Senhora Diretora;F
A Sua Excelência o Senhor;Embaixador;Senhor Embaixador;M
A Sua Excelência a Senhora;Embaixadora;Senhora Embaixadora;F
A Sua Excelência o Senhor;General do Exército Brasileiro;Senhor General;M
A Sua Excelência o Senhor;Governador;Senhor Governador;M
A Sua Excelência a Senhora;Governadora;Senhora Governadora;F
A Sua Excelência o Senhor;Juiz de Direito;Senhor Juiz;M
A Sua Excelência o Senhor;Juiz Federal;Senhor Juiz;M
A Sua Excelência a Senhora;Juíza de Direito;Senhora Juíza;F
A Sua Excelência a Senhora;Juíza Federal;Senhora Juíza;F
A Sua Excelência o Senhor;Marechal do Exército Brasileiro;Senhor Marechal;M
A Sua Excelência a Senhora;Ministra de Estado;Senhora Ministra;F
A Sua Excelência o Senhor;Ministro de Estado;Senhor Chefe da Casa Militar;M
A Sua Excelência o Senhor;Ministro de Estado;Senhor Ministro;M
A Sua Excelência a Senhora;Prefeita Municipal;Senhora Prefeita;F
A Sua Excelência o Senhor;Prefeito Municipal;Senhor Prefeito;M
A Senhora;Presidenta;Senhora Presidenta;F
A Sua Excelência a Senhora;Presidenta da Assembleia Legislativa;Senhora Presidenta da Assembleia Legislativa;F
A Sua Excelência a Senhora;Presidenta da Câmara Legislativa;Senhora Presidenta da Câmara Legislativa;F
A Sua Excelência a Senhora;Presidenta da Câmara Municipal;Senhora Presidenta da Câmara Municipal;F
A Sua Excelência a Senhora;Presidenta da República;Excelentíssima Senhora Presidenta da República;F
A Sua Excelência a Senhora;Presidenta do Congresso Nacional;Excelentíssima Senhora Presidenta;F
A Sua Excelência a Senhora;Presidenta do Supremo Tribunal Federal;Excelentíssima Senhora Presidenta;F
Ao Senhor;Presidente;Senhor Presidente;M
A Sua Excelência o Senhor;Presidente da Assembleia Legislativa;Senhor Presidente da Assembleia Legislativa;M
A Sua Excelência o Senhor;Presidente da Câmara Legislativa;Senhor Presidente da Câmara Legislativa;M
A Sua Excelência o Senhor;Presidente da Câmara Municipal;Senhor Presidente da Câmara Municipal;M
A Sua Excelência o Senhor;Presidente da República;Excelentíssimo Senhor Presidente da República;M
A Sua Excelência o Senhor;Presidente do Congresso Nacional;Excelentíssimo Senhor Presidente;M
A Sua Excelência o Senhor;Presidente do Supremo Tribunal Federal;Excelentíssimo Senhor Presidente;M
A Sua Excelência o Senhor;Procurador da República;Senhor Procurador;M
A Sua Excelência a Senhora;Procuradora da República;Senhora Procuradora;F
A Sua Excelência o Senhor;Procurador do Estado;Senhor Procurador;M
A Sua Excelência a Senhora;Procuradora do Estado;Senhora Procuradora;F
A Sua Excelência o Senhor;Promotor de Justiça;Senhor Promotor;M
A Sua Excelência a Senhora;Promotora de Justiça;Senhora Promotora;F
Ao Senhor;Reitor;Magnífico Reitor;M
A Senhora;Reitora;Magnífica Reitora;F
A Senhora;Secretária;Senhora Secretária;F
A Sua Excelência a Senhora;Secretária de Estado;Senhora Secretária;F
Ao Senhor;Secretário;Senhor Secretário;M
A Sua Excelência o Senhor;Secretário de Estado;Senhor Secretário;M
A Sua Excelência o Senhor;Secretário-Adjunto;Senhor Secretário;M
A Sua Excelência o Senhor;Secretário-Executivo;Senhor Secretário;M
A Sua Excelência o Senhor;Secretário-Executivo Adjunto;Senhor Secretário;M
A Sua Excelência o Senhor;Secretário-Executivo Substituto;Senhor Secretário;M
A Sua Excelência o Senhor;Senador da República;Senhor Senador;M
A Sua Excelência a Senhora;Senadora da República;Senhora Senadora;F
Ao Senhor;Superintendente;Senhor Superintendente;M
A Senhora;Superintendente;Senhora Superintendente;F
Ao Senhor;Vereador;Senhor Vereador;M
A Senhora;Vereadora;Senhora Vereadora;F
Ao Senhor;Vice-Presidente;Senhor Vice-Presidente;M
A Sua Excelência o Senhor;Vice-Presidente da República;Senhor Vice-Presidente da República;M
Ao Senhor;Vice-Reitor;Senhor Vice-Reitor;M
A Senhora;Vice-Reitora;Senhora Vice-Reitora;F
Ao Senhor;Gerente;Senhor Gerente;M
A Senhora;Gerente;Senhora Gerente;F';

      $arrLinhas = explode("\n",$strConteudo);

      $objTratamentoRN = new TratamentoRN();
      $objCargoRN = new CargoRN();
      $objVocativoRN = new VocativoRN();
      
      $arrIdTratamento = array();
      $arrIdVocativo = array();
      
      foreach($arrLinhas as $strLinha){
        $arrColunas = explode(';', $strLinha);

        $arrColunas[0] = trim($arrColunas[0]);
        $arrColunas[1] = trim($arrColunas[1]);
        $arrColunas[2] = trim($arrColunas[2]);
        $arrColunas[3] = trim($arrColunas[3]);

        $objCargoDTO = new CargoDTO();
        $objCargoDTO->setBolExclusaoLogica(false);
        $objCargoDTO->retNumIdCargo();
        $objCargoDTO->setStrExpressao($arrColunas[1]);

        $objCargoDTO->adicionarCriterio(array('StaGenero','StaGenero'),
                                        array(InfraDTO::$OPER_IGUAL, InfraDTO::$OPER_IGUAL),
                                        array(null, $arrColunas[3]),
                                        InfraDTO::$OPER_LOGICO_OR);

        $objCargoDTO->setNumMaxRegistrosRetorno(1);

        if ($objCargoRN->consultarRN0301($objCargoDTO) == null) {

          if (!isset($arrIdTratamento[$arrColunas[0]])) {

            $objTratamentoDTO = new TratamentoDTO();
            $objTratamentoDTO->setBolExclusaoLogica(false);
            $objTratamentoDTO->retNumIdTratamento();
            $objTratamentoDTO->setStrExpressao($arrColunas[0]);
            $objTratamentoDTO->setNumMaxRegistrosRetorno(1);
            $objTratamentoDTO = $objTratamentoRN->consultarRN0317($objTratamentoDTO);

            if ($objTratamentoDTO == null) {
              $objTratamentoDTO = new TratamentoDTO();
              $objTratamentoDTO->setNumIdTratamento(null);
              $objTratamentoDTO->setStrExpressao($arrColunas[0]);
              $objTratamentoDTO->setStrSinAtivo('S');
              $objTratamentoDTO = $objTratamentoRN->cadastrarRN0315($objTratamentoDTO);
            }

            $arrIdTratamento[$arrColunas[0]] = $objTratamentoDTO->getNumIdTratamento();
          }

          if (!isset($arrIdVocativo[$arrColunas[2]])) {

            $objVocativoDTO = new VocativoDTO();
            $objVocativoDTO->setBolExclusaoLogica(false);
            $objVocativoDTO->retNumIdVocativo();
            $objVocativoDTO->setStrExpressao($arrColunas[2]);
            $objVocativoDTO->setNumMaxRegistrosRetorno(1);
            $objVocativoDTO = $objVocativoRN->consultarRN0309($objVocativoDTO);

            if ($objVocativoDTO == null) {
              $objVocativoDTO = new VocativoDTO();
              $objVocativoDTO->setNumIdVocativo(null);
              $objVocativoDTO->setStrExpressao($arrColunas[2]);
              $objVocativoDTO->setStrSinAtivo('S');
              $objVocativoDTO = $objVocativoRN->cadastrarRN0307($objVocativoDTO);
            }

            $arrIdVocativo[$arrColunas[2]] = $objVocativoDTO->getNumIdVocativo();
          }

          $objCargoDTO = new CargoDTO();
          $objCargoDTO->setNumIdCargo(null);
          $objCargoDTO->setStrExpressao($arrColunas[1]);
          $objCargoDTO->setNumIdTratamento($arrIdTratamento[$arrColunas[0]]);
          $objCargoDTO->setNumIdVocativo($arrIdVocativo[$arrColunas[2]]);
          $objCargoDTO->setStrStaGenero($arrColunas[3]);
          $objCargoDTO->setStrSinAtivo('S');
          $objCargoRN->cadastrarRN0299($objCargoDTO);
        }
      }

      InfraDebug::getInstance()->setBolDebugInfra(true);

    }catch(Exception $e){
      throw new InfraException('Erro populando dados de Cargo, Tratamento e Vocativo.', $e);
    }
  }

  public function migrarDadosDocumentos(){

    try{

      $rsProtocolos = BancoSEI::getInstance()->consultarSql('select '.BancoSEI::getInstance()->formatarSelecaoDbl('protocolo', 'id_protocolo', 'idprotocolo').' from protocolo where sta_protocolo='.BancoSEI::getInstance()->formatarGravacaoStr(ProtocoloRN::$TP_DOCUMENTO_GERADO));

      $rsAssinaturas = BancoSEI::getInstance()->consultarSql('select distinct '.BancoSEI::getInstance()->formatarSelecaoDbl('assinatura', 'id_documento', 'idprotocolo').' from assinatura inner join protocolo on assinatura.id_documento=protocolo.id_protocolo and protocolo.sta_protocolo='.BancoSEI::getInstance()->formatarGravacaoStr(ProtocoloRN::$TP_DOCUMENTO_RECEBIDO));

      $rsProtocolos = array_merge($rsProtocolos, $rsAssinaturas);

      $numRegistros = count($rsProtocolos);
      $n = 0;

      $objDocumentoConteudoBD = new DocumentoConteudoBD(BancoSEI::getInstance());


      InfraDebug::getInstance()->setBolDebugInfra(false);
      foreach($rsProtocolos as $item){

        $dblIdProtocolo = BancoSEI::getInstance()->formatarLeituraDbl($item['idprotocolo']);

        if ((++$n >=1000 && $n%1000==0) || $n==$numRegistros){
          InfraDebug::getInstance()->gravar('MIGRANDO DADOS DE DOCUMENTOS: '.$n.' DE '.$numRegistros);
        }

        $rs = BancoSEI::getInstance()->consultarSql('select '.
            BancoSEI::getInstance()->formatarSelecaoNum('documento', 'conteudo', 'documentoconteudo').','.
            BancoSEI::getInstance()->formatarSelecaoStr('documento', 'conteudo_assinatura', 'conteudoassinatura').','.
            BancoSEI::getInstance()->formatarSelecaoStr('documento', 'crc_assinatura', 'crcassinatura').','.
            BancoSEI::getInstance()->formatarSelecaoStr('documento', 'qr_code_assinatura', 'qrcodeassinatura').
            ' from documento where id_documento='.BancoSEI::getInstance()->formatarGravacaoDbl($dblIdProtocolo));


        if (count($rs)==1) {

          $objDocumentoConteudoDTO = new DocumentoConteudoDTO();
          $objDocumentoConteudoDTO->setStrConteudo(BancoSEI::getInstance()->formatarLeituraStr($rs[0]['documentoconteudo']));
          $objDocumentoConteudoDTO->setStrConteudoAssinatura(BancoSEI::getInstance()->formatarLeituraStr($rs[0]['conteudoassinatura']));
          $objDocumentoConteudoDTO->setStrCrcAssinatura(BancoSEI::getInstance()->formatarLeituraStr($rs[0]['crcassinatura']));
          $objDocumentoConteudoDTO->setStrQrCodeAssinatura(BancoSEI::getInstance()->formatarLeituraStr($rs[0]['qrcodeassinatura']));
          $objDocumentoConteudoDTO->setDblIdDocumento($dblIdProtocolo);
          $objDocumentoConteudoBD->cadastrar($objDocumentoConteudoDTO);

          BancoSEI::getInstance()->executarSql('update documento set conteudo=null,conteudo_assinatura=null,crc_assinatura=null,qr_code_assinatura=null where id_documento=' . BancoSEI::getInstance()->formatarGravacaoDbl($dblIdProtocolo));
        }
      }
      InfraDebug::getInstance()->setBolDebugInfra(true);
    }catch(Exception $e){
      throw new InfraException('Erro migrando conteúdo de documentos internos.', $e);
    }
  }

  public function fixSinAtivoContatos(){

    try{

      InfraDebug::getInstance()->setBolDebugInfra(false);

      $objContatoDTO = new ContatoDTO();
      $objContatoDTO->setBolExclusaoLogica(false);
      $objContatoDTO->retNumIdContato();
      $objContatoDTO->retStrSinAtivo();

      $objContatoRN = new ContatoRN();
      $arrObjContatoDTO = InfraArray::indexarArrInfraDTO($objContatoRN->listarRN0325($objContatoDTO),'IdContato');

      $objContatoBD = new ContatoBD(BancoSEI::getInstance());

      InfraDebug::getInstance()->gravar('ORGAOS...');

      $objOrgaoDTO = new OrgaoDTO();
      $objOrgaoDTO->setBolExclusaoLogica(false);
      $objOrgaoDTO->retNumIdContato();
      $objOrgaoDTO->retStrSinAtivo();

      $objOrgaoRN = new OrgaoRN();
      $arrObjOrgaoDTO = InfraArray::indexarArrInfraDTO($objOrgaoRN->listarRN1353($objOrgaoDTO),'IdContato');

      $n = 0;
      foreach($arrObjOrgaoDTO as $numIdContato => $objOrgaoDTO){
        if ($arrObjContatoDTO[$numIdContato]->getStrSinAtivo()!=$objOrgaoDTO->getStrSinAtivo()){
          $objContatoDTO = new ContatoDTO();
          $objContatoDTO->setStrSinAtivo($objOrgaoDTO->getStrSinAtivo());
          $objContatoDTO->setNumIdContato($numIdContato);
          $objContatoBD->alterar($objContatoDTO);
          $n++;
        }
      }
      InfraDebug::getInstance()->gravar($n.' REGISTROS ATUALIZADOS');


      InfraDebug::getInstance()->gravar('UNIDADES...');

      $objUnidadeDTO = new UnidadeDTO();
      $objUnidadeDTO->setBolExclusaoLogica(false);
      $objUnidadeDTO->retNumIdContato();
      $objUnidadeDTO->retStrSinAtivo();

      $objUnidadeRN = new UnidadeRN();
      $arrObjUnidadeDTO = InfraArray::indexarArrInfraDTO($objUnidadeRN->listarRN0127($objUnidadeDTO),'IdContato');

      $n = 0;
      foreach($arrObjUnidadeDTO as $numIdContato => $objUnidadeDTO){
        if ($arrObjContatoDTO[$numIdContato]->getStrSinAtivo()!=$objUnidadeDTO->getStrSinAtivo()){
          $objContatoDTO = new ContatoDTO();
          $objContatoDTO->setStrSinAtivo($objUnidadeDTO->getStrSinAtivo());
          $objContatoDTO->setNumIdContato($numIdContato);
          $objContatoBD->alterar($objContatoDTO);
          $n++;
        }
      }
      InfraDebug::getInstance()->gravar($n.' REGISTROS ATUALIZADOS');

      InfraDebug::getInstance()->gravar('USUARIOS...');

      $objUsuarioDTO = new UsuarioDTO();
      $objUsuarioDTO->setBolExclusaoLogica(false);
      $objUsuarioDTO->retNumIdContato();

      $objUsuarioRN = new UsuarioRN();
      $arrIdContatoUsuarios = InfraArray::converterArrInfraDTO($objUsuarioRN->listarRN0490($objUsuarioDTO),'IdContato');

      $n = 0;
      foreach($arrIdContatoUsuarios as $numIdContato){
        if ($arrObjContatoDTO[$numIdContato]->getStrSinAtivo()=='N') {
          $objContatoDTO = new ContatoDTO();
          $objContatoDTO->setStrSinAtivo('S');
          $objContatoDTO->setNumIdContato($numIdContato);
          $objContatoBD->alterar($objContatoDTO);
          $n++;
        }
      }
      InfraDebug::getInstance()->gravar($n.' REGISTROS ATUALIZADOS');

      InfraDebug::getInstance()->setBolDebugInfra(true);

    }catch(Exception $e){
      throw new InfraException('Erro sincronizando sinalizadores de exclusão lógica.', $e);
    }
  }

  public function fixPontoControle(){

    try{

      InfraDebug::getInstance()->setBolDebugInfra(false);

      $rs = BancoSEI::getInstance()->consultarSql('select '.BancoSEI::getInstance()->formatarSelecaoNum('atributo_andamento_situacao', 'id_andamento_situacao', null).','.BancoSEI::getInstance()->formatarSelecaoStr('atributo_andamento_situacao', 'id_origem', null).' from atributo_andamento_situacao where id_origem is not null');


      $objSituacaoDTO = new SituacaoDTO();
      $objSituacaoDTO->setBolExclusaoLogica(false);
      $objSituacaoDTO->retNumIdSituacao();

      $objSituacaoRN = new SituacaoRN();
      $arrIdSituacoes = InfraArray::converterArrInfraDTO($objSituacaoRN->listar($objSituacaoDTO),'IdSituacao');

      $numRegistros = count($rs);
      $n = 0;
      $objAndamentoSituacaoBD = new AndamentoSituacaoBD(BancoSEI::getInstance());
      foreach($rs as $item){

        if ((++$n >=100 && $n%100==0) || $n==$numRegistros){
          InfraDebug::getInstance()->gravar('PONTOS DE CONTROLE [ATRIBUTOS]: '.$n.' DE '.$numRegistros);
        }

        if (in_array(BancoSEI::getInstance()->formatarLeituraStr($item['id_origem']),$arrIdSituacoes)) {
          $objAndamentoSituacaoDTO = new AndamentoSituacaoDTO();
          $objAndamentoSituacaoDTO->setNumIdAndamentoSituacao(BancoSEI::getInstance()->formatarLeituraNum($item['id_andamento_situacao']));
          $objAndamentoSituacaoDTO->setNumIdSituacao(BancoSEI::getInstance()->formatarLeituraStr($item['id_origem']));
          $objAndamentoSituacaoBD->alterar($objAndamentoSituacaoDTO);
        }
      }

      $rs = BancoSEI::getInstance()->consultarSql('select '.BancoSEI::getInstance()->formatarSelecaoDbl('rel_proced_situacao_unidade', 'id_procedimento', null).','.BancoSEI::getInstance()->formatarSelecaoDbl('rel_proced_situacao_unidade', 'id_unidade', null).' from rel_proced_situacao_unidade');

      $numRegistros = count($rs);
      $n = 0;

      $objAndamentoSituacaoRN = new AndamentoSituacaoRN();
      foreach($rs as $item){

        if ((++$n >=100 && $n%100==0) || $n==$numRegistros){
          InfraDebug::getInstance()->gravar('PONTOS DE CONTROLE [SITUACAO]: '.$n.' DE '.$numRegistros);
        }

        $objAndamentoSituacaoDTO = new AndamentoSituacaoDTO();
        $objAndamentoSituacaoDTO->retNumIdAndamentoSituacao();
        $objAndamentoSituacaoDTO->setDblIdProcedimento(BancoSEI::getInstance()->formatarLeituraDbl($item['id_procedimento']));
        $objAndamentoSituacaoDTO->setNumIdUnidade(BancoSEI::getInstance()->formatarLeituraNum($item['id_unidade']));
        $objAndamentoSituacaoDTO->setOrdNumIdAndamentoSituacao(InfraDTO::$TIPO_ORDENACAO_DESC);
        $objAndamentoSituacaoDTO->setNumMaxRegistrosRetorno(1);

        $objAndamentoSituacaoDTO = $objAndamentoSituacaoRN->consultar($objAndamentoSituacaoDTO);
        if ($objAndamentoSituacaoDTO!=null) {
          $objAndamentoSituacaoDTO->setStrSinUltimo('S');
          $objAndamentoSituacaoBD->alterar($objAndamentoSituacaoDTO);
        }
      }

      InfraDebug::getInstance()->setBolDebugInfra(true);

    }catch(Exception $e){
      throw new InfraException('Erro atualizando Pontos de Controle.', $e);
    }
  }

  public function fixAssuntos(){

    try{

      InfraDebug::getInstance()->setBolDebugInfra(false);

      InfraDebug::getInstance()->gravar('ATUALIZANDO ASSUNTOS...');

      $rs = BancoSEI::getInstance()->consultarSql('select '.
          BancoSEI::getInstance()->formatarSelecaoNum('assunto', 'id_assunto', null).','.
          BancoSEI::getInstance()->formatarSelecaoNum('assunto', 'maior_tempo_corrente', null).','.
          BancoSEI::getInstance()->formatarSelecaoNum('assunto', 'menor_tempo_corrente', null).','.
          BancoSEI::getInstance()->formatarSelecaoStr('assunto', 'sin_elimina_maior_corrente', null).','.
          BancoSEI::getInstance()->formatarSelecaoStr('assunto', 'sin_elimina_menor_corrente', null).','.
          BancoSEI::getInstance()->formatarSelecaoNum('assunto', 'maior_tempo_intermediario', null).','.
          BancoSEI::getInstance()->formatarSelecaoNum('assunto', 'menor_tempo_intermediario', null).','.
          BancoSEI::getInstance()->formatarSelecaoStr('assunto', 'sin_elimina_menor_intermed', null).','.
          BancoSEI::getInstance()->formatarSelecaoStr('assunto', 'sin_elimina_maior_intermed', null).','.
          BancoSEI::getInstance()->formatarSelecaoStr('assunto', 'sin_suficiente', null).
          ' from assunto');

      $numRegistros = count($rs);
      $n = 0;

      $objAssuntoBD = new AssuntoBD(BancoSEI::getInstance());
      foreach($rs as $item){

        if ((++$n >=100 && $n%100==0) || $n==$numRegistros){
          InfraDebug::getInstance()->gravar($n.' DE '.$numRegistros);
        }

        $numIdAssunto = BancoSEI::getInstance()->formatarLeituraNum($item['id_assunto']);
        $numMaiorTempoCorrente = BancoSEI::getInstance()->formatarLeituraNum($item['maior_tempo_corrente']);
        $numMenorTempoCorrente = BancoSEI::getInstance()->formatarLeituraNum($item['menor_tempo_corrente']);
        $strSinEliminaMaiorCorrente = BancoSEI::getInstance()->formatarLeituraStr($item['sin_elimina_maior_corrente']);
        $strSinEliminaMenorCorrente = BancoSEI::getInstance()->formatarLeituraStr($item['sin_elimina_menor_corrente']);
        $numMaiorTempoIntermediario = BancoSEI::getInstance()->formatarLeituraNum($item['maior_tempo_intermediario']);
        $numMenorTempoIntermediario = BancoSEI::getInstance()->formatarLeituraNum($item['menor_tempo_intermediario']);
        $strSinEliminaMaiorIntermediario = BancoSEI::getInstance()->formatarLeituraStr($item['sin_elimina_maior_intermed']);
        $strSinEliminaMenorIntermediario = BancoSEI::getInstance()->formatarLeituraStr($item['sin_elimina_menor_intermed']);
        $strSinSuficiente = BancoSEI::getInstance()->formatarLeituraStr($item['sin_suficiente']);


        $objAssuntoDTO = new AssuntoDTO();

        if ($numMaiorTempoCorrente > $numMenorTempoCorrente){
          $objAssuntoDTO->setNumPrazoCorrente($numMaiorTempoCorrente);
        }else{
          $objAssuntoDTO->setNumPrazoCorrente($numMenorTempoCorrente);
        }


        if ($numMaiorTempoIntermediario > $numMenorTempoIntermediario) {
          $objAssuntoDTO->setNumPrazoIntermediario($numMaiorTempoIntermediario);
        }else{
          $objAssuntoDTO->setNumPrazoIntermediario($numMenorTempoIntermediario);
        }

        $strStaDestinacao = 'G';
        if ($strSinEliminaMaiorIntermediario=='S' && $strSinEliminaMenorIntermediario=='S'){
          $strStaDestinacao = 'E';
        }else if ($strSinEliminaMaiorCorrente=='S' && $strSinEliminaMenorCorrente=='S'){
          $strStaDestinacao = 'E';
        }

        $objAssuntoDTO->setStrStaDestinacao($strStaDestinacao);

        if ($strSinSuficiente=='S'){
          $objAssuntoDTO->setStrSinEstrutural('N');
        }else{

          $rsTiposProcesso = BancoSEI::getInstance()->consultarSql('select count(*) as total from rel_tipo_procedimento_assunto where id_assunto='.BancoSEI::getInstance()->formatarGravacaoNum($numIdAssunto));

          if ($rsTiposProcesso[0]['total']==0) {

            $rsTiposDocumento = BancoSEI::getInstance()->consultarSql('select count(*) as total from rel_serie_assunto where id_assunto=' . BancoSEI::getInstance()->formatarGravacaoNum($numIdAssunto));

            if ($rsTiposDocumento[0]['total']==0) {
              $rsProtocolos = BancoSEI::getInstance()->consultarSql('select count(*) as total from rel_protocolo_assunto where id_assunto=' . BancoSEI::getInstance()->formatarGravacaoNum($numIdAssunto));

              if ($rsProtocolos[0]['total']==0) {

                $objAssuntoDTO->setStrSinEstrutural('S');

              }else{
                $objAssuntoDTO->setStrSinEstrutural('N');
              }

            }else{
              $objAssuntoDTO->setStrSinEstrutural('N');
            }
          }else{
            $objAssuntoDTO->setStrSinEstrutural('N');
          }
        }

        $objAssuntoDTO->setNumIdAssunto($numIdAssunto);

        $objAssuntoBD->alterar($objAssuntoDTO);

      }

      InfraDebug::getInstance()->setBolDebugInfra(true);

    }catch(Exception $e){
      throw new InfraException('Erro atualizando Assuntos.', $e);
    }
  }

  public function fixArquivamento() {
    try{

      InfraDebug::getInstance()->setBolDebugInfra(false);

      InfraDebug::getInstance()->gravar('ATUALIZANDO ANDAMENTOS DE LOCALIZADORES...');

      $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
      $objAtributoAndamentoDTO->retNumIdAtributoAndamento();
      $objAtributoAndamentoDTO->retStrIdOrigem();
      $objAtributoAndamentoDTO->setStrNome('LOCALIZADOR');

      $objAtributoAndamentoRN = new AtributoAndamentoRN();
      $arrObjAtributoAndamentoDTO = $objAtributoAndamentoRN->listarRN1367($objAtributoAndamentoDTO);

      $numRegistros = count($arrObjAtributoAndamentoDTO);
      $n = 0;

      $objAtributoAndamentoBD = new AtributoAndamentoBD(BancoSEI::getInstance());
      foreach($arrObjAtributoAndamentoDTO as $dto){

        if ((++$n >=1000 && $n%1000==0) || $n==$numRegistros){
          InfraDebug::getInstance()->gravar('ANDAMENTO: '.$n.' DE '.$numRegistros);
        }

        $arr = explode('¥',$dto->getStrIdOrigem());
        $dto->setStrIdOrigem($arr[0]);

        $objAtributoAndamentoBD->alterar($dto);
      }


      $objAtributoAndamentoRN = new AtributoAndamentoRN();
      $objArquivamentoRN = new ArquivamentoRN();
      $objArquivamentoBD = new ArquivamentoBD(BancoSEI::getInstance());

      InfraDebug::getInstance()->gravar('ATUALIZANDO DOCUMENTOS COM ARQUIVAMENTO...');

      $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
      $objAtributoAndamentoDTO->retNumIdAtividade();
      $objAtributoAndamentoDTO->retStrIdOrigem();
      $objAtributoAndamentoDTO->setStrNome('DOCUMENTO');
      $objAtributoAndamentoDTO->setNumIdTarefaAtividade(TarefaRN::$TI_ARQUIVAMENTO);
      $objAtributoAndamentoDTO->setOrdNumIdAtividade(InfraDTO::$TIPO_ORDENACAO_ASC);

      $arrObjAtributoAndamentoDTO = InfraArray::indexarArrInfraDTO($objAtributoAndamentoRN->listarRN1367($objAtributoAndamentoDTO),'IdOrigem');

      $n = 0;
      $numRegistros = count($arrObjAtributoAndamentoDTO);

      foreach($arrObjAtributoAndamentoDTO as $objAtributoAndamentoDTO){

        if ((++$n >=1000 && $n%1000==0) || $n==$numRegistros){
          InfraDebug::getInstance()->gravar($n.' DE '.$numRegistros);
        }

        $rs = BancoSEI::getInstance()->consultarSql('select '.BancoSEI::getInstance()->formatarSelecaoStr(null,'sta_arquivamento',null).','.BancoSEI::getInstance()->formatarSelecaoNum(null,'id_localizador',null).' from protocolo where id_protocolo='.BancoSEI::getInstance()->formatarGravacaoDbl($objAtributoAndamentoDTO->getStrIdOrigem()));

        if (count($rs)) {

          $strStaArquivamento = BancoSEI::getInstance()->formatarLeituraStr($rs[0]['sta_arquivamento']);
          $numIdLocalizador = BancoSEI::getInstance()->formatarLeituraNum($rs[0]['id_localizador']);

          $objArquivamentoDTO = new ArquivamentoDTO();
          $objArquivamentoDTO->setDblIdProtocolo($objAtributoAndamentoDTO->getStrIdOrigem());
          $objArquivamentoDTO->setNumIdLocalizador($numIdLocalizador);

          if ($strStaArquivamento == ArquivamentoRN::$TA_NAO_ARQUIVADO) {
            $objArquivamentoDTO->setStrStaArquivamento(ArquivamentoRN::$TA_DESARQUIVADO);
          } else {
            $objArquivamentoDTO->setStrStaArquivamento($strStaArquivamento);
          }

          $objArquivamentoDTO->setNumIdAtividadeArquivamento($objAtributoAndamentoDTO->getNumIdAtividade());
          $objArquivamentoDTO->setNumIdAtividadeRecebimento(null);
          $objArquivamentoDTO->setNumIdAtividadeSolicitacao(null);
          $objArquivamentoDTO->setNumIdAtividadeDesarquivamento(null);
          $objArquivamentoBD->cadastrar($objArquivamentoDTO);
        }
      }

      unset($arrObjAtributoAndamentoDTO);

      InfraDebug::getInstance()->gravar('ATUALIZANDO DOCUMENTOS RECEBIDOS...');

      $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
      $objAtributoAndamentoDTO->retNumIdAtividade();
      $objAtributoAndamentoDTO->retStrIdOrigem();
      $objAtributoAndamentoDTO->setStrNome('DOCUMENTO');
      $objAtributoAndamentoDTO->setNumIdTarefaAtividade(TarefaRN::$TI_RECEBIMENTO_ARQUIVO);
      $objAtributoAndamentoDTO->setOrdNumIdAtividade(InfraDTO::$TIPO_ORDENACAO_ASC);

      $arrObjAtributoAndamentoDTO = InfraArray::indexarArrInfraDTO($objAtributoAndamentoRN->listarRN1367($objAtributoAndamentoDTO),'IdOrigem');

      $n = 0;
      $numRegistros = count($arrObjAtributoAndamentoDTO);

      foreach($arrObjAtributoAndamentoDTO as $objAtributoAndamentoDTO){

        if ((++$n >=1000 && $n%1000==0) || $n==$numRegistros){
          InfraDebug::getInstance()->gravar($n.' DE '.$numRegistros);
        }

        $rs = BancoSEI::getInstance()->consultarSql('select '.BancoSEI::getInstance()->formatarSelecaoStr(null,'sta_arquivamento',null).' from protocolo where id_protocolo='.BancoSEI::getInstance()->formatarGravacaoDbl($objAtributoAndamentoDTO->getStrIdOrigem()));

        if (count($rs)) {

          $strStaArquivamento = BancoSEI::getInstance()->formatarLeituraStr($rs[0]['sta_arquivamento']);

          $objArquivamentoDTO = new ArquivamentoDTO();
          $objArquivamentoDTO->retDblIdProtocolo();
          $objArquivamentoDTO->setDblIdProtocolo($objAtributoAndamentoDTO->getStrIdOrigem());
          $objArquivamentoDTO = $objArquivamentoRN->consultar($objArquivamentoDTO);

          if ($objArquivamentoDTO == null) {

            if ($strStaArquivamento == ArquivamentoRN::$TA_RECEBIDO) {
              $objArquivamentoDTO = new ArquivamentoDTO();
              $objArquivamentoDTO->setDblIdProtocolo($objAtributoAndamentoDTO->getStrIdOrigem());
              $objArquivamentoDTO->setStrStaArquivamento(ArquivamentoRN::$TA_RECEBIDO);
              $objArquivamentoDTO->setNumIdLocalizador(null);
              $objArquivamentoDTO->setNumIdAtividadeArquivamento(null);
              $objArquivamentoDTO->setNumIdAtividadeRecebimento($objAtributoAndamentoDTO->getNumIdAtividade());
              $objArquivamentoDTO->setNumIdAtividadeSolicitacao(null);
              $objArquivamentoDTO->setNumIdAtividadeDesarquivamento(null);
              $objArquivamentoBD->cadastrar($objArquivamentoDTO);
            }

          } else {

            $objArquivamentoDTO->setNumIdAtividadeRecebimento($objAtributoAndamentoDTO->getNumIdAtividade());
            $objArquivamentoBD->alterar($objArquivamentoDTO);

          }
        }
      }

      unset($arrObjAtributoAndamentoDTO);

      InfraDebug::getInstance()->gravar('ATUALIZANDO DOCUMENTOS COM SOLICITACAO DE DESARQUIVAMENTO...');

      $rs = BancoSEI::getInstance()->consultarSql('select '.BancoSEI::getInstance()->formatarSelecaoDbl(null,'id_protocolo',null).' from protocolo where sta_arquivamento='.BancoSEI::getInstance()->formatarGravacaoStr(ArquivamentoRN::$TA_SOLICITADO_DESARQUIVAMENTO));

      $n = 0;
      $numRegistros = count($rs);

      foreach($rs as $item){

        if ((++$n >=100 && $n%100==0) || $n==$numRegistros){
          InfraDebug::getInstance()->gravar($n);
        }

        $dblIdProtocolo = BancoSEI::getInstance()->formatarLeituraDbl($item['id_protocolo']);

        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->retNumIdAtividade();
        $objAtributoAndamentoDTO->setStrIdOrigem($dblIdProtocolo);
        $objAtributoAndamentoDTO->setStrNome('DOCUMENTO');
        $objAtributoAndamentoDTO->setNumIdTarefaAtividade(TarefaRN::$TI_SOLICITADO_DESARQUIVAMENTO);
        $objAtributoAndamentoDTO->setNumMaxRegistrosRetorno(1);
        $objAtributoAndamentoDTO->setOrdNumIdAtividade(InfraDTO::$TIPO_ORDENACAO_DESC);

        $objAtributoAndamentoDTO = $objAtributoAndamentoRN->consultarRN1366($objAtributoAndamentoDTO);

        $objArquivamentoDTO = new ArquivamentoDTO();
        $objArquivamentoDTO->retDblIdProtocolo();
        $objArquivamentoDTO->setDblIdProtocolo($dblIdProtocolo);
        $objArquivamentoDTO = $objArquivamentoRN->consultar($objArquivamentoDTO);

        if ($objArquivamentoDTO != null) {
          $objArquivamentoDTO->setStrStaArquivamento(ArquivamentoRN::$TA_SOLICITADO_DESARQUIVAMENTO);
          $objArquivamentoDTO->setNumIdAtividadeSolicitacao($objAtributoAndamentoDTO->getNumIdAtividade());
          $objArquivamentoBD->alterar($objArquivamentoDTO);
        }
      }

      InfraDebug::getInstance()->gravar('ATUALIZANDO DOCUMENTOS COM DESARQUIVAMENTO...');

      $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
      $objAtributoAndamentoDTO->setDistinct(true);
      $objAtributoAndamentoDTO->retStrIdOrigem();
      $objAtributoAndamentoDTO->setStrNome('DOCUMENTO');
      $objAtributoAndamentoDTO->setNumIdTarefaAtividade(TarefaRN::$TI_DESARQUIVAMENTO);

      $arrIdOrigem = InfraArray::converterArrInfraDTO($objAtributoAndamentoRN->listarRN1367($objAtributoAndamentoDTO),'IdOrigem');

      $n = 0;
      $numRegistros = count($arrIdOrigem);

      foreach($arrIdOrigem as $strIdOrigem){

        if ((++$n >=100 && $n%100==0) || $n==$numRegistros){
          InfraDebug::getInstance()->gravar($n);
        }

        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->retNumIdAtividade();
        $objAtributoAndamentoDTO->setStrIdOrigem($strIdOrigem);
        $objAtributoAndamentoDTO->setStrNome('DOCUMENTO');
        $objAtributoAndamentoDTO->setNumIdTarefaAtividade(TarefaRN::$TI_DESARQUIVAMENTO);
        $objAtributoAndamentoDTO->setNumMaxRegistrosRetorno(1);
        $objAtributoAndamentoDTO->setOrdNumIdAtividade(InfraDTO::$TIPO_ORDENACAO_DESC);

        $objAtributoAndamentoDTO = $objAtributoAndamentoRN->consultarRN1366($objAtributoAndamentoDTO);

        $objArquivamentoDTO = new ArquivamentoDTO();
        $objArquivamentoDTO->retDblIdProtocolo();
        $objArquivamentoDTO->setDblIdProtocolo($strIdOrigem);
        $objArquivamentoDTO = $objArquivamentoRN->consultar($objArquivamentoDTO);

        if ($objArquivamentoDTO != null) {
          $objArquivamentoDTO->setNumIdAtividadeDesarquivamento($objAtributoAndamentoDTO->getNumIdAtividade());
          $objArquivamentoBD->alterar($objArquivamentoDTO);
        }

      }

      InfraDebug::getInstance()->setBolDebugInfra(true);

    }catch(Exception $e){
      throw new InfraException('Erro atualizando dados de arquivamento.', $e);
    }
  }

  public function atualizarSequenciasMySql(){
    $arrSequencias = array(
        'seq_acesso',
        'seq_acesso_externo',
        'seq_acompanhamento',
        'seq_anexo',
        'seq_anotacao',
        'seq_arquivo_extensao',
        'seq_assinante',
        'seq_assinatura',
        'seq_assunto',
        'seq_atividade',
        'seq_atributo_andamento',
        'seq_base_conhecimento',
        'seq_bloco',
        'seq_cargo',
        'seq_cidade',
        'seq_conjunto_estilos',
        'seq_conjunto_estilos_item',
        'seq_contato',
        'seq_controle_interno',
        'seq_email_grupo_email',
        'seq_email_unidade',
        'seq_estilo',
        'seq_feed',
        'seq_feriado',
        'seq_grupo_acompanhamento',
        'seq_grupo_contato',
        'seq_grupo_email',
        'seq_grupo_protocolo_modelo',
        'seq_grupo_serie',
        'seq_hipotese_legal',
        'seq_imagem_formato',
        'seq_localizador',
        'seq_lugar_localizador',
        'seq_modelo',
        'seq_nivel_acesso_permitido',
        'seq_novidade',
        'seq_numeracao',
        'seq_observacao',
        'seq_operacao_servico',
        'seq_ordenador_despesa',
        'seq_pais',
        'seq_participante',
        'seq_protocolo_modelo',
        'seq_publicacao',
        'seq_rel_protocolo_protocolo',
        'seq_retorno_programado',
        'seq_secao_documento',
        'seq_secao_imprensa_nacional',
        'seq_secao_modelo',
        'seq_serie',
        'seq_serie_publicacao',
        'seq_servico',
        'seq_texto_padrao_interno',
        'seq_tipo_conferencia',
        'seq_tipo_contexto_contato',
        'seq_tipo_localizador',
        'seq_tipo_procedimento',
        'seq_tipo_suporte',
        'seq_tratamento',
        'seq_uf',
        'seq_unidade_publicacao',
        'seq_veiculo_imprensa_nacional',
        'seq_veiculo_publicacao',
        'seq_vocativo',
        'seq_grupo_unidade',
        'seq_email_utilizado',
        'seq_andamento_situacao',
        'seq_situacao',
        'seq_auditoria_protocolo',
        'seq_estatisticas',
        'seq_infra_auditoria',
        'seq_infra_log',
        'seq_infra_navegador',
        'seq_protocolo',
        'seq_versao_secao_documento',
        'seq_controle_unidade');

    foreach($arrSequencias as $strSequencia){

      if ($strSequencia=='seq_atributo_andamento_situaca'){
        $strIdOrigem = 'id_atributo_andamento_situacao';
        $strTabelaOrigem = 'atributo_andamento_situacao';
      }else{
        $strIdOrigem = str_replace('seq_','id_',$strSequencia);
        $strTabelaOrigem = str_replace('seq_','',$strSequencia);
      }

      $rsTab = BancoSEI::getInstance()->consultarSql('select max('.$strIdOrigem.') as ultimo from '.$strTabelaOrigem);

      if ($rsTab[0]['ultimo'] !== null){

        $rsSeq = BancoSEI::getInstance()->consultarSql('select max(id) as ultimo from '.$strSequencia);

        if ($rsSeq[0]['ultimo'] === null) {

          BancoSEI::getInstance()->executarSql('INSERT INTO ' . $strSequencia . ' (campo) VALUES (null)');

          $rsSeq = BancoSEI::getInstance()->consultarSql('select max(id) as ultimo from ' . $strSequencia);

        }

        if ($rsTab[0]['ultimo'] > $rsSeq[0]['ultimo']) {
          BancoSEI::getInstance()->executarSql('alter table ' . $strSequencia . ' AUTO_INCREMENT = ' . ($rsTab[0]['ultimo'] + 1));
        }
      }
    }
  }

  protected function fixAtividadeConclusaoAutomaticaUsuarioControlado(){

    BancoSEI::getInstance()->executarSql('update tarefa set nome=\'Conclusão Automática de Processo do Usuário @USUARIO@\' where id_tarefa='.TarefaRN::$TI_CONCLUSAO_AUTOMATICA_USUARIO);

    InfraDebug::getInstance()->setBolDebugInfra(false);

    InfraDebug::getInstance()->gravar('COMPLEMENTANDO ANDAMENTOS DE CONCLUSAO AUTOMATICA DO USUARIO...');

    $objAtividadeDTO = new AtividadeDTO();
    $objAtividadeDTO->retNumIdAtividade();
    $objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_CONCLUSAO_AUTOMATICA_USUARIO);

    $objAtividadeRN = new AtividadeRN();
    $arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);

    $objAtributoAndamentoRN = new AtributoAndamentoRN();

    $numRegistros = count($arrObjAtividadeDTO);
    $n = 0;

    foreach($arrObjAtividadeDTO as $objAtividadeDTO){

      $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
      $objAtributoAndamentoDTO->retNumIdAtributoAndamento();
      $objAtributoAndamentoDTO->setStrNome('USUARIO');
      $objAtributoAndamentoDTO->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());

      if ($objAtributoAndamentoRN->consultarRN1366($objAtributoAndamentoDTO)==null) {

        $objAtributoAndamentoDTO->setStrValor(null);
        $objAtributoAndamentoDTO->setStrIdOrigem(null);

        $objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);
      }

      if ((++$n >= 100 && $n % 100 == 0) || $n == $numRegistros) {
        InfraDebug::getInstance()->gravar('ANDAMENTO: ' . $n . ' DE ' . $numRegistros);
      }
    }

    InfraDebug::getInstance()->setBolDebugInfra(true);
  }

  protected function fixIndexacaoObservacoesConectado(){

    InfraDebug::getInstance()->setBolDebugInfra(false);

    InfraDebug::getInstance()->gravar('INDEXANDO OBSERVACOES...');

    $objObservacaoBD = new ObservacaoBD(BancoSEI::getInstance());

    $objObservacaoDTO = new ObservacaoDTO();
    $objObservacaoDTO->retNumIdObservacao();
    $objObservacaoDTO->setStrDescricao(null,InfraDTO::$OPER_DIFERENTE);
    $objObservacaoDTO->setStrIdxObservacao(null);
    $arrIdObservacao = array_chunk(InfraArray::converterArrInfraDTO($objObservacaoBD->listar($objObservacaoDTO),'IdObservacao'),1000);

    $numRegistrosAtual = 0;
    foreach($arrIdObservacao as $arrIdObservacaoParte){

      $numRegistrosAtual += count($arrIdObservacaoParte);

      InfraDebug::getInstance()->gravar($numRegistrosAtual);

      $objObservacaoDTO = new ObservacaoDTO();
      $objObservacaoDTO->retNumIdObservacao();
      $objObservacaoDTO->retStrDescricao();
      $objObservacaoDTO->setNumIdObservacao($arrIdObservacaoParte, InfraDTO::$OPER_IN);
      $arrObjObservacaoDTO = $objObservacaoBD->listar($objObservacaoDTO);

      foreach ($arrObjObservacaoDTO as $objObservacaoDTO){
        $objObservacaoDTO->setStrIdxObservacao(InfraString::prepararIndexacao($objObservacaoDTO->getStrDescricao(),false));
        $objObservacaoBD->alterar($objObservacaoDTO);
      }
    }

    InfraDebug::getInstance()->setBolDebugInfra(true);
  }

  protected function fixIndexacaoOrgaosConectado(){

    InfraDebug::getInstance()->setBolDebugInfra(false);

    InfraDebug::getInstance()->gravar('INDEXANDO ORGAOS...');

    $objOrgaoDTO = new OrgaoDTO();
    $objOrgaoDTO->retNumIdOrgao();
    $objOrgaoDTO->retStrSigla();

    $objOrgaoRN = new OrgaoRN();
    $arrObjOrgaoDTO = $objOrgaoRN->listarRN1353($objOrgaoDTO);

    foreach($arrObjOrgaoDTO as $objOrgaoDTO){
      InfraDebug::getInstance()->gravar($objOrgaoDTO->getStrSigla());
      $objOrgaoRN->montarIndexacao($objOrgaoDTO);
    }

    InfraDebug::getInstance()->setBolDebugInfra(true);
  }

  protected function fixAcessoExternoConectado(){

    InfraDebug::getInstance()->setBolDebugInfra(false);

    InfraDebug::getInstance()->gravar('COMPLEMENTANDO ANDAMENTOS DE ACESSOS EXTERNOS...');

    $objAcessoExternoDTO = new AcessoExternoDTO();
    $objAcessoExternoDTO->setBolExclusaoLogica(false);
    $objAcessoExternoDTO->retNumIdAcessoExterno();
    $objAcessoExternoDTO->retNumIdAtividade();
    $objAcessoExternoDTO->retStrSinProcesso();
    $objAcessoExternoDTO->setStrStaTipo(AcessoExternoRN::$TA_ASSINATURA_EXTERNA);

    $objAcessoExternoRN = new AcessoExternoRN();
    $arrObjAcessoExternoDTO = $objAcessoExternoRN->listar($objAcessoExternoDTO);

    $numRegistros = count($arrObjAcessoExternoDTO);

    $objAtributoAndamentoBD = new AtributoAndamentoBD(BancoSEI::getInstance());

    $n = 0;
    foreach($arrObjAcessoExternoDTO as $objAcessoExternoDTO){
      
      $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
      $objAtributoAndamentoDTO->setNumIdAtributoAndamento(null);
      $objAtributoAndamentoDTO->setNumIdAtividade($objAcessoExternoDTO->getNumIdAtividade());
      $objAtributoAndamentoDTO->setStrNome('VISUALIZACAO');
      $objAtributoAndamentoDTO->setStrValor(null);

      if ($objAcessoExternoDTO->getStrSinProcesso()=='S'){
        $objAtributoAndamentoDTO->setStrIdOrigem(AcessoExternoRN::$TV_INTEGRAL);
      }else{
        $objAtributoAndamentoDTO->setStrIdOrigem(AcessoExternoRN::$TV_NENHUM);
      }

      $objAtributoAndamentoBD->cadastrar($objAtributoAndamentoDTO);

      if ((++$n >= 100 && $n % 100 == 0) || $n == $numRegistros) {
        InfraDebug::getInstance()->gravar('ANDAMENTO: ' . $n . ' DE ' . $numRegistros);
      }
    }
    InfraDebug::getInstance()->setBolDebugInfra(true);
  }

  protected function fixProtocoloFormatadoConectado(){

    InfraDebug::getInstance()->setBolDebugInfra(false);

    InfraDebug::getInstance()->gravar('CORRIGINDO NUMERACAO DE PESQUISA DE PROCESSOS...');

    $objProtocoloDTO = new ProtocoloDTO();
    $objProtocoloDTO->retStrProtocoloFormatado();
    $objProtocoloDTO->retStrProtocoloFormatadoPesquisa();
    $objProtocoloDTO->retDblIdProtocolo();
    $objProtocoloDTO->setStrStaProtocolo(ProtocoloRN::$TP_PROCEDIMENTO);

    $objProtocoloRN = new ProtocoloRN();
    $arrObjProtocoloDTO = $objProtocoloRN->listarRN0668($objProtocoloDTO);

    $numRegistros = count($arrObjProtocoloDTO);

    $objProtocoloBD = new ProtocoloBD(BancoSEI::getInstance());
    $n = 0;
    foreach($arrObjProtocoloDTO as $objProtocoloDTO){

      $strProtocoloPesquisa = preg_replace("/[^0-9a-zA-Z]+/", '',$objProtocoloDTO->getStrProtocoloFormatado());

      if ($objProtocoloDTO->getStrProtocoloFormatadoPesquisa()!=$strProtocoloPesquisa){
        $dto = new ProtocoloDTO();
        $dto->setStrProtocoloFormatadoPesquisa($strProtocoloPesquisa);

        if ($objProtocoloBD->contar($dto)==0) {
          $dto->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
          $objProtocoloBD->alterar($dto);
        }
      }

      if ((++$n >= 1000 && $n % 1000 == 0) || $n == $numRegistros) {
        InfraDebug::getInstance()->gravar($n . ' DE ' . $numRegistros);
      }
    }
    InfraDebug::getInstance()->setBolDebugInfra(true);
  }

  protected function fixIndexacaoContatosConectado(){

    InfraDebug::getInstance()->setBolDebugInfra(false);

    InfraDebug::getInstance()->gravar('REINDEXANDO CONTATOS...');

    $rs = BancoSEI::getInstance()->consultarSql('select id_contato from contato where cpf is not null or cnpj is not null');

    InfraDebug::getInstance()->setBolDebugInfra(false);

    $objContatoDTO = new ContatoDTO();
    $objContatoDTO->setNumIdContato(null);

    $objContatoRN = new ContatoRN();

    $numRegistros = count($rs);

    $n = 0;
    foreach($rs as $item){

      $objContatoDTO->setNumIdContato($item['id_contato']);

      $objContatoRN->montarIndexacaoRN0450($objContatoDTO);

      if ((++$n >= 1000 && $n % 1000 == 0) || $n == $numRegistros) {
        InfraDebug::getInstance()->gravar($n . ' DE ' . $numRegistros);
      }
    }

    InfraDebug::getInstance()->setBolDebugInfra(true);
  }

  protected function atualizarSequenciasControlado(){

    try{

      ini_set('max_execution_time','0');
      ini_set('mssql.timeout','0');

      InfraDebug::getInstance()->setBolLigado(true);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->setBolEcho(true);
      InfraDebug::getInstance()->limpar();

      $numSeg = InfraUtil::verificarTempoProcessamento();

      InfraDebug::getInstance()->gravar('Atualizar Sequencias - Iniciando...');

      $arrSequencias = array(
          'seq_acesso',
          'seq_acesso_externo',
          'seq_acompanhamento',
          'seq_anexo',
          'seq_anotacao',
          'seq_arquivo_extensao',
          'seq_assinante',
          'seq_assinatura',
          'seq_assunto',
          'seq_atividade',
          'seq_atributo',
          'seq_atributo_andamento',
          'seq_base_conhecimento',
          'seq_bloco',
          'seq_cargo',
          'seq_cidade',
          'seq_conjunto_estilos',
          'seq_conjunto_estilos_item',
          'seq_contato',
          'seq_controle_interno',
          'seq_dominio',
          'seq_email_grupo_email',
          'seq_email_unidade',
          'seq_estilo',
          'seq_feed',
          'seq_feriado',
          'seq_grupo_acompanhamento',
          'seq_grupo_contato',
          'seq_grupo_email',
          'seq_grupo_protocolo_modelo',
          'seq_grupo_serie',
          'seq_hipotese_legal',
          'seq_imagem_formato',
          'seq_localizador',
          'seq_lugar_localizador',
          'seq_modelo',
          'seq_nivel_acesso_permitido',
          'seq_novidade',
          'seq_numeracao',
          'seq_observacao',
          'seq_operacao_servico',
          'seq_ordenador_despesa',
          'seq_pais',
          'seq_participante',
          'seq_protocolo_modelo',
          'seq_publicacao',
          'seq_rel_protocolo_protocolo',
          'seq_retorno_programado',
          'seq_secao_documento',
          'seq_secao_imprensa_nacional',
          'seq_secao_modelo',
          'seq_serie',
          'seq_serie_publicacao',
          'seq_servico',
          'seq_texto_padrao_interno',
          'seq_tipo_conferencia',
          'seq_tipo_localizador',
          'seq_tipo_procedimento',
          'seq_tipo_suporte',
          'seq_tratamento',
          'seq_uf',
          'seq_unidade_publicacao',
          'seq_veiculo_imprensa_nacional',
          'seq_veiculo_publicacao',
          'seq_vocativo',
          'seq_grupo_unidade',
          'seq_email_utilizado',
          'seq_andamento_situacao',
          'seq_situacao',
          'seq_tarefa',
          'seq_email_sistema',
          'seq_tipo_formulario',
          'seq_tarja_assinatura',
          'seq_monitoramento_servico',
          'seq_tipo_contato',
          'seq_rel_unidade_tipo_contato',
          'seq_marcador',
          'seq_andamento_marcador',
          'seq_assunto_proxy',
          'seq_tabela_assuntos',
          'seq_serie_restricao',
          'seq_tipo_proced_restricao');

      foreach($arrSequencias as $strSequencia){

        if (BancoSEI::getInstance() instanceof InfraSqlServer || BancoSEI::getInstance() instanceof InfraMySql){
          BancoSEI::getInstance()->executarSql('drop table '.$strSequencia);
        }else{
          BancoSEI::getInstance()->executarSql('drop sequence '.$strSequencia);
        }

        $strIdOrigem = str_replace('seq_','id_',$strSequencia);
        $strTabelaOrigem = str_replace('seq_','',$strSequencia);

        $rs = BancoSEI::getInstance()->consultarSql('select max('.$strIdOrigem.') as ultimo from '.$strTabelaOrigem);

        if ($rs[0]['ultimo'] == null){
          $numInicial = 1;
        }else{
          $numInicial = $rs[0]['ultimo'] + 1;
        }

        BancoSEI::getInstance()->criarSequencialNativa($strSequencia, $numInicial);

        InfraDebug::getInstance()->gravar($strSequencia.': '.$numInicial);

      }

      $arrSequencias = array(
          'seq_auditoria_protocolo',
          'seq_estatisticas',
          'seq_infra_auditoria',
          'seq_infra_log',
          'seq_infra_navegador',
          'seq_protocolo',
          'seq_versao_secao_documento',
          'seq_controle_unidade',
          'seq_monitoramento_servico');

      foreach($arrSequencias as $strSequencia){

        if (BancoSEI::getInstance() instanceof InfraSqlServer || BancoSEI::getInstance() instanceof InfraMySql){
          BancoSEI::getInstance()->executarSql('drop table '.$strSequencia);
        }else{
          BancoSEI::getInstance()->executarSql('drop sequence '.$strSequencia);
        }

        $rs = BancoSEI::getInstance()->consultarSql('select '.BancoSEI::getInstance()->formatarSelecaoDbl(null,'max('.str_replace('seq_','id_',$strSequencia).')','ultimo').' from '.str_replace('seq_','',$strSequencia));

        if ($rs[0]['ultimo'] == null){
          $numInicial = 1;
        }else{
          $numInicial = $rs[0]['ultimo'] + 1;
        }

        if (BancoSEI::getInstance() instanceof InfraMySql){
          BancoSEI::getInstance()->executarSql('create table '.$strSequencia.' (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = '.$numInicial);
        }else if (BancoSEI::getInstance() instanceof InfraSqlServer){
          BancoSEI::getInstance()->executarSql('create table '.$strSequencia.' (id bigint identity('.$numInicial.',1), campo char(1) null)');
        }else if (BancoSEI::getInstance() instanceof InfraOracle){
          BancoSEI::getInstance()->criarSequencialNativa($strSequencia, $numInicial);
        }

        InfraDebug::getInstance()->gravar($strSequencia.': '.$numInicial);
      }

      $numSeg = InfraUtil::verificarTempoProcessamento($numSeg);

      InfraDebug::getInstance()->gravar('Atualizar Sequencias - Finalizado em '.InfraData::formatarTimestamp($numSeg));

    }catch(Exception $e){
      throw new InfraException('Erro atualizando sequencias da base de dados.',$e);
    }
  }
}
?>