<?

require_once dirname(__FILE__).'/../Sip.php';

class VersaoSeiRN extends InfraRN {

  private $numSeg = 0;

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSip::getInstance();
  }

  private function inicializar($strTitulo){

    ini_set('max_execution_time','0');
    ini_set('memory_limit','-1');

    InfraDebug::getInstance()->setBolLigado(true);
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

	    $this->inicializar('INICIANDO ATUALIZACAO DE VERSAO SIP '.SIP_VERSAO.' (VERSAO SEI 3.0)');

      $numVersaoInfraRequerida = '1.466';
      if (VERSAO_INFRA != $numVersaoInfraRequerida){
        $this->finalizar('VERSAO DO FRAMEWORK PHP INCOMPATIVEL (VERSAO ATUAL '.VERSAO_INFRA.', VERSAO REQUERIDA '.$numVersaoInfraRequerida.')',true);
      }

      if (!(BancoSip::getInstance() instanceof InfraMySql) &&
          !(BancoSip::getInstance() instanceof InfraSqlServer) &&
          !(BancoSip::getInstance() instanceof InfraOracle)){
	      $this->finalizar('BANCO DE DADOS NAO SUPORTADO: '.get_parent_class(BancoSip::getInstance()),true);
	    }

	    $objInfraParametro = new InfraParametro(BancoSip::getInstance());

      $strVersaoAtual = $objInfraParametro->getValor('SIP_VERSAO', false);

	    if (InfraString::isBolVazia($strVersaoAtual)){
        $this->finalizar('VERSAO ATUAL NAO IDENTIFICADA',true);
      }

	    if ($strVersaoAtual == SIP_VERSAO){
	      $this->finalizar('VERSAO JA CONSTA COMO ATUALIZADA',true);
	    }

      if (substr($strVersaoAtual,0,6) != '1.30.0') {
        $this->finalizar('VERSAO INCOMPATIVEL PARA ATUALIZACAO',true);
      }
      $objInfraMetaBD = new InfraMetaBD(BancoSip::getInstance());
      $objInfraMetaBD->adicionarColuna('infra_log','sta_tipo',$objInfraMetaBD->tipoTextoFixo(1),'null');
      BancoSip::getInstance()->executarSql('update infra_log set sta_tipo=\'E\'');
      $objInfraMetaBD->alterarColuna('infra_log','sta_tipo',$objInfraMetaBD->tipoTextoFixo(1),'not null');
      $objInfraMetaBD->criarIndice('infra_log','i01_infra_log',array('sta_tipo','dth_log'));

      $objSistemaRN = new SistemaRN();
      $objPerfilRN = new PerfilRN();
      $objMenuRN = new MenuRN();
      $objItemMenuRN = new ItemMenuRN();
      $objRecursoRN = new RecursoRN();
      $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();
      $objRelPerfilRecursoRN = new RelPerfilRecursoRN();

      $objSistemaDTO = new SistemaDTO();
      $objSistemaDTO->retNumIdSistema();
      $objSistemaDTO->setStrSigla('SEI');

      $objSistemaDTO = $objSistemaRN->consultar($objSistemaDTO);
      if ($objSistemaDTO == null){
        throw new InfraException('Sistema SEI não encontrado.');
      }
      $numIdSistemaSei = $objSistemaDTO->getNumIdSistema();

      $objPerfilDTO = new PerfilDTO();
      $objPerfilDTO->retNumIdPerfil();
      $objPerfilDTO->setNumIdSistema($numIdSistemaSei);
      $objPerfilDTO->setStrNome('Básico');
      $objPerfilDTO = $objPerfilRN->consultar($objPerfilDTO);
      if ($objPerfilDTO == null){
        throw new InfraException('Perfil Básico do sistema SEI não encontrado.');
      }
      $numIdPerfilSeiBasico = $objPerfilDTO->getNumIdPerfil();

      $objPerfilDTO = new PerfilDTO();
      $objPerfilDTO->retNumIdPerfil();
      $objPerfilDTO->setNumIdSistema($numIdSistemaSei);
      $objPerfilDTO->setStrNome('Administrador');
      $objPerfilDTO = $objPerfilRN->consultar($objPerfilDTO);
      if ($objPerfilDTO == null){
        throw new InfraException('Perfil Administrador do sistema SEI não encontrado.');
      }
      $numIdPerfilSeiAdministrador = $objPerfilDTO->getNumIdPerfil();

      $objPerfilDTO = new PerfilDTO();
      $objPerfilDTO->retNumIdPerfil();
      $objPerfilDTO->setNumIdSistema($numIdSistemaSei);
      $objPerfilDTO->setStrNome('Arquivamento');
      $objPerfilDTO = $objPerfilRN->consultar($objPerfilDTO);
      if ($objPerfilDTO == null){
        throw new InfraException('Perfil Arquivamento do sistema SEI não encontrado.');
      }
      $numIdPerfilSeiArquivamento = $objPerfilDTO->getNumIdPerfil();

      $objPerfilDTO = new PerfilDTO();
      $objPerfilDTO->retNumIdPerfil();
      $objPerfilDTO->setNumIdSistema($numIdSistemaSei);
      $objPerfilDTO->setStrNome('Informática');
      $objPerfilDTO = $objPerfilRN->consultar($objPerfilDTO);
      if ($objPerfilDTO == null){
        throw new InfraException('Perfil Informática do sistema SEI não encontrado.');
      }
      $numIdPerfilSeiInformatica = $objPerfilDTO->getNumIdPerfil();

      $objMenuDTO = new MenuDTO();
      $objMenuDTO->retNumIdMenu();
      $objMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objMenuDTO->setStrNome('Principal');
      $objMenuDTO = $objMenuRN->consultar($objMenuDTO);
      if ($objMenuDTO == null){
        throw new InfraException('Menu do sistema SEI não encontrado.');
      }
      $numIdMenuSei = $objMenuDTO->getNumIdMenu();

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setStrRotulo('Administração');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu Administração do sistema SEI não encontrado.');
      }
      $numIdItemMenuSeiAdministracao = $objItemMenuDTO->getNumIdItemMenu();

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setStrRotulo('Relatórios');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu Relatórios do sistema SEI não encontrado.');
      }
      $numIdItemMenuSeiRelatorios = $objItemMenuDTO->getNumIdItemMenu();

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setStrRotulo('Infra');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu Infra do sistema SEI não encontrado.');
      }
      $numIdItemMenuSeiInfra = $objItemMenuDTO->getNumIdItemMenu();

      $objSistemaDTO = new SistemaDTO();
      $objSistemaDTO->retNumIdSistema();
      $objSistemaDTO->setStrSigla('SIP');

      $objSistemaDTO = $objSistemaRN->consultar($objSistemaDTO);
      if ($objSistemaDTO == null){
        throw new InfraException('Sistema SIP não encontrado.');
      }
      $numIdSistemaSip = $objSistemaDTO->getNumIdSistema();

      $objMenuDTO = new MenuDTO();
      $objMenuDTO->retNumIdMenu();
      $objMenuDTO->setNumIdSistema($numIdSistemaSip);
      $objMenuDTO->setStrNome('Principal');
      $objMenuDTO = $objMenuRN->consultar($objMenuDTO);
      if ($objMenuDTO == null){
        throw new InfraException('Menu do sistema SIP não encontrado.');
      }
      $numIdMenuSip = $objMenuDTO->getNumIdMenu();

      $objPerfilDTO = new PerfilDTO();
      $objPerfilDTO->retNumIdPerfil();
      $objPerfilDTO->setNumIdSistema($numIdSistemaSip);
      $objPerfilDTO->setStrNome('Administrador SIP');
      $objPerfilDTO = $objPerfilRN->consultar($objPerfilDTO);
      if ($objPerfilDTO == null){
        throw new InfraException('Perfil Administrador SIP do sistema SIP não encontrado.');
      }
      $numIdPerfilSipAdministradorSip = $objPerfilDTO->getNumIdPerfil();

      $objPerfilDTO = new PerfilDTO();
      $objPerfilDTO->retNumIdPerfil();
      $objPerfilDTO->setNumIdSistema($numIdSistemaSip);
      $objPerfilDTO->setStrNome('Básico');
      $objPerfilDTO = $objPerfilRN->consultar($objPerfilDTO);
      if ($objPerfilDTO == null){
        throw new InfraException('Perfil Básico do sistema SIP não encontrado.');
      }
      $numIdPerfilSipBasico = $objPerfilDTO->getNumIdPerfil();

      $this->logar('ATUALIZANDO BASE SIP...');

      $objInfraMetaBD->adicionarColuna('orgao','ordem',$objInfraMetaBD->tipoNumero(),'null');
      BancoSip::getInstance()->executarSql('update orgao set ordem=0');
      $objInfraMetaBD->alterarColuna('orgao','ordem',$objInfraMetaBD->tipoNumero(),'not null');

      $objInfraMetaBD->alterarColuna('perfil','nome',$objInfraMetaBD->tipoTextoVariavel(100),'not null');

      if (BancoSip::getInstance() instanceof InfraOracle){
        $objInfraMetaBD->adicionarColuna('perfil','descricao2',$objInfraMetaBD->tipoTextoGrande(),'null');
        BancoSip::getInstance()->executarSql('update perfil set descricao2=descricao');
        $objInfraMetaBD->excluirColuna('perfil','descricao');
        BancoSip::getInstance()->executarSql('alter table perfil rename column descricao2 to descricao');
      }else {
        $objInfraMetaBD->alterarColuna('perfil', 'descricao', $objInfraMetaBD->tipoTextoGrande(), 'null');
      }

      if (BancoSip::getInstance() instanceof InfraOracle){
        BancoSip::getInstance()->executarSql('alter table perfil drop constraint i02_perfil');
        $objInfraMetaBD->excluirIndice('perfil', 'i02_perfil');
      }else {
        $objInfraMetaBD->excluirIndice('perfil', 'i02_perfil');
      }

      $objInfraMetaBD->excluirColuna('perfil','sin_avulso');
      $objInfraMetaBD->adicionarColuna('unidade','id_origem',$objInfraMetaBD->tipoTextoVariavel(50),'null');
      $objInfraMetaBD->adicionarColuna('usuario','id_origem',$objInfraMetaBD->tipoTextoVariavel(50),'null');
      BancoSip::getInstance()->executarSql('update usuario set id_origem=id_pessoa_rh');
      $objInfraMetaBD->excluirColuna('usuario','id_pessoa_rh');

      BancoSip::getInstance()->executarSql('drop table mapeamento_unidade');

      $this->logar('ATUALIZANDO PERFIS SIP...');

      $this->removerRecurso($numIdSistemaSip,'mapeamento_unidade_cadastrar');
      $this->removerRecurso($numIdSistemaSip,'mapeamento_unidade_alterar');
      $this->removerRecurso($numIdSistemaSip,'mapeamento_unidade_consultar');
      $this->removerRecurso($numIdSistemaSip,'mapeamento_unidade_contar');
      $this->removerRecurso($numIdSistemaSip,'mapeamento_unidade_listar');
      $this->removerRecurso($numIdSistemaSip,'mapeamento_unidade_excluir');
      $this->removerRecurso($numIdSistemaSip,'mapeamento_unidade_desativar');
      $this->removerRecurso($numIdSistemaSip,'mapeamento_unidade_selecionar');

      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSip, $numIdPerfilSipBasico, 'rel_orgao_autenticacao_listar');

      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSip, $numIdPerfilSipAdministradorSip, 'sistema_reativar');
      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSip);
      $objItemMenuDTO->setNumIdItemMenuPai(null);
      $objItemMenuDTO->setStrRotulo('Sistemas');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      $this->adicionarItemMenu($numIdSistemaSip,$numIdPerfilSipAdministradorSip,$numIdMenuSip,$objItemMenuDTO->getNumIdItemMenu(),$objRecursoDTO->getNumIdRecurso(),'Reativar', 2);

      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSip, $numIdPerfilSipAdministradorSip, 'hierarquia_reativar');
      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSip);
      $objItemMenuDTO->setNumIdItemMenuPai(null);
      $objItemMenuDTO->setStrRotulo('Hierarquias');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      $this->adicionarItemMenu($numIdSistemaSip,$numIdPerfilSipAdministradorSip,$numIdMenuSip,$objItemMenuDTO->getNumIdItemMenu(),$objRecursoDTO->getNumIdRecurso(),'Reativar', 2);

      $this->logar('ATUALIZANDO PERFIS SEI...');

      $objPerfilDTO = new PerfilDTO();
      $objPerfilDTO->setNumIdPerfil(null);
      $objPerfilDTO->setNumIdSistema($numIdSistemaSei);
      $objPerfilDTO->setStrNome('Acervo de Sigilosos da Unidade');
      $objPerfilDTO->setStrDescricao(null);
      $objPerfilDTO->setStrSinCoordenado('N');
      $objPerfilDTO->setStrSinAtivo('S');

      $objPerfilDTO = $objPerfilRN->cadastrar($objPerfilDTO);
      $numIdPerfilSeiAcervoSigilosos = $objPerfilDTO->getNumIdPerfil();

      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAcervoSigilosos, 'procedimento_credencial_cancelar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAcervoSigilosos, 'procedimento_credencial_ativar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAcervoSigilosos, 'procedimento_acervo_sigilosos');
      $objItemMenuDTO=$this->adicionarItemMenu($numIdSistemaSei,$numIdPerfilSeiAcervoSigilosos,$numIdMenuSei,$numIdItemMenuSeiRelatorios,$objRecursoDTO->getNumIdRecurso(),'Acervo de Sigilosos da Unidade', 0);

      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiInformatica, 'modulo_listar');
      $objItemMenuDTO=$this->adicionarItemMenu($numIdSistemaSei,$numIdPerfilSeiInformatica,$numIdMenuSei,$numIdItemMenuSeiInfra,$objRecursoDTO->getNumIdRecurso(),'Módulos', 0);

      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiInformatica, 'infra_atributo_cache_consultar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiInformatica, 'infra_atributo_cache_excluir');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiInformatica, 'infra_atributo_cache_listar');
      $objItemMenuDTO=$this->adicionarItemMenu($numIdSistemaSei,$numIdPerfilSeiInformatica,$numIdMenuSei,$numIdItemMenuSeiInfra,$objRecursoDTO->getNumIdRecurso(),'Cache em Memória', 0);

      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'contato_selecionar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'contato_alterar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'contexto_cadastrar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'contexto_alterar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'contexto_excluir');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'contato_excluir');

      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'email_utilizado_consultar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'nivel_acesso_permitido_consultar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'tipo_procedimento_escolha_consultar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'serie_escolha_consultar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'acesso_consultar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'procedimento_pesquisar');

      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'acesso_externo_protocolo_selecionar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'acesso_externo_protocolo_detalhe');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'rel_acesso_ext_protocolo_cadastrar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'rel_acesso_ext_protocolo_consultar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'rel_acesso_ext_protocolo_listar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'rel_acesso_ext_protocolo_excluir');

      $objItemMenuDTO=$this->adicionarItemMenu($numIdSistemaSei,$numIdPerfilSeiAdministrador,$numIdMenuSei,$numIdItemMenuSeiAdministracao,null,'Tipos de Formulários', 0);
      $numIdItemMenuPai=$objItemMenuDTO->getNumIdItemMenu();
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'tipo_formulario_listar');
      $this->adicionarItemMenu($numIdSistemaSei,$numIdPerfilSeiAdministrador,$numIdMenuSei,$numIdItemMenuPai,$objRecursoDTO->getNumIdRecurso(),'Listar', 0);
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'tipo_formulario_consultar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'tipo_formulario_cadastrar');
      $this->adicionarItemMenu($numIdSistemaSei,$numIdPerfilSeiAdministrador,$numIdMenuSei,$numIdItemMenuPai,$objRecursoDTO->getNumIdRecurso(),'Novo', 0);
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'tipo_formulario_alterar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'tipo_formulario_excluir');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'tipo_formulario_desativar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'tipo_formulario_reativar');
      $this->adicionarItemMenu($numIdSistemaSei,$numIdPerfilSeiAdministrador,$numIdMenuSei,$numIdItemMenuPai,$objRecursoDTO->getNumIdRecurso(),'Reativar', 0);
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'tipo_formulario_visualizar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'tipo_formulario_clonar');

      $this->removerRecurso($numIdSistemaSei,'base_conhecimento_desbloquear');
      $this->removerRecurso($numIdSistemaSei,'documento_desbloquear');
      $this->removerRecurso($numIdSistemaSei,'usuario_externo_exibir_arquivo');


      $this->removerRecurso($numIdSistemaSei,'texto_padrao_alterar');
      $this->removerRecurso($numIdSistemaSei,'texto_padrao_cadastrar');
      $this->removerRecurso($numIdSistemaSei,'texto_padrao_consultar');
      $this->removerRecurso($numIdSistemaSei,'texto_padrao_desbloquear');
      $this->removerRecurso($numIdSistemaSei,'texto_padrao_excluir');
      $this->removerRecurso($numIdSistemaSei,'texto_padrao_listar');
      $this->removerRecurso($numIdSistemaSei,'texto_padrao_selecionar');


      $this->removerRecurso($numIdSistemaSei,'aplicabilidade_atributo_cadastrar');
      $this->removerRecurso($numIdSistemaSei,'aplicabilidade_atributo_excluir');
      $this->removerRecurso($numIdSistemaSei,'aplicabilidade_atributo_listar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'rel_protocolo_atributo_alterar');

      $this->removerRecurso($numIdSistemaSei,'pesquisa_fts_ajuda');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'documento_gerar_circular');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'documento_email_circular');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'email_enviar_circular');

      $objItemMenuDTO=$this->adicionarItemMenu($numIdSistemaSei,$numIdPerfilSeiAdministrador,$numIdMenuSei,$numIdItemMenuSeiAdministracao,null,'Grupos Institucionais', 0);
      $numIdItemMenuGruposInstitucionais=$objItemMenuDTO->getNumIdItemMenu();
      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setStrRotulo('Grupos de E-mail Institucionais');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu \'Grupos de E-mail Institucionais\' do sistema SEI não encontrado.');
      }

      $objItemMenuDTO->setStrRotulo("E-Mail");
      $objItemMenuDTO->setNumSequencia(0);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuGruposInstitucionais);
      $objItemMenuRN->alterar($objItemMenuDTO);

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setStrRotulo('Grupos de Envio Institucionais');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu \'Grupos de Envio Institucionais\' do sistema SEI não encontrado.');
      }

      $objItemMenuDTO->setStrRotulo("Envio");
      $objItemMenuDTO->setNumSequencia(0);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuGruposInstitucionais);
      $objItemMenuRN->alterar($objItemMenuDTO);

      $objItemMenuDTO=$this->adicionarItemMenu($numIdSistemaSei,$numIdPerfilSeiAdministrador,$numIdMenuSei,null,null,'Grupos', 130);
      $idMenuGrupos=$objItemMenuDTO->getNumIdItemMenu();
      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setStrRotulo('Grupos de E-mail');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu \'Grupos de E-mail\' do sistema SEI não encontrado.');
      }

      $objItemMenuDTO->setStrRotulo("E-Mail");
      $objItemMenuDTO->setNumSequencia(0);
      $objItemMenuDTO->setNumIdItemMenuPai($idMenuGrupos);
      $objItemMenuRN->alterar($objItemMenuDTO);

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setStrRotulo('Grupos de Envio');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu \'Grupos de Envio\' do sistema SEI não encontrado.');
      }
      $objItemMenuDTO->setStrRotulo("Envio");
      $objItemMenuDTO->setNumSequencia(0);
      $objItemMenuDTO->setNumIdItemMenuPai($idMenuGrupos);
      $objItemMenuRN->alterar($objItemMenuDTO);

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setStrRotulo('Contextos/Contatos');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu \'Contextos/Contatos\' do sistema SEI não encontrado.');
      }
      $objItemMenuDTO->setStrRotulo("Contatos");
      $objItemMenuDTO->setNumSequencia(0);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuSeiAdministracao);
      $objItemMenuRN->alterar($objItemMenuDTO);

      $numIdItemMenuContato=$objItemMenuDTO->getNumIdItemMenu();

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuContato);
      $objItemMenuDTO->setStrRotulo('Novo Contexto');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu \'Contatos/Novo Contexto\' do sistema SEI não encontrado.');
      }
      $objItemMenuRN->excluir(array($objItemMenuDTO));

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->retNumIdRecurso();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuContato);
      $objItemMenuDTO->setStrRotulo('Novo Contato');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu \'Novo Contato\' do sistema SEI não encontrado.');
      }
      $objItemMenuDTO->setStrRotulo("Novo");
      $objItemMenuDTO->setNumSequencia(10);
      $objItemMenuRN->alterar($objItemMenuDTO);

      $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
      $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objRelPerfilItemMenuDTO->setNumIdMenu($objItemMenuDTO->getNumIdMenu());
      $objRelPerfilItemMenuDTO->setNumIdItemMenu($objItemMenuDTO->getNumIdItemMenu());
      $objRelPerfilItemMenuDTO->setNumIdRecurso($objItemMenuDTO->getNumIdRecurso());
      $objRelPerfilItemMenuDTO->setNumIdPerfil($numIdPerfilSeiAdministrador);

      if ($objRelPerfilItemMenuRN->contar($objRelPerfilItemMenuDTO)==0) {
        $objRelPerfilItemMenuRN->cadastrar($objRelPerfilItemMenuDTO);
      }


      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->retNumIdRecurso();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuContato);
      $objItemMenuDTO->setStrRotulo('Pesquisar');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu \'Contatos/Pesquisar\' do sistema SEI não encontrado.');
      }
      $objItemMenuDTO->setStrRotulo("Listar");
      $objItemMenuDTO->setNumSequencia(20);
      $objItemMenuRN->alterar($objItemMenuDTO);

      $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
      $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objRelPerfilItemMenuDTO->setNumIdMenu($objItemMenuDTO->getNumIdMenu());
      $objRelPerfilItemMenuDTO->setNumIdItemMenu($objItemMenuDTO->getNumIdItemMenu());
      $objRelPerfilItemMenuDTO->setNumIdRecurso($objItemMenuDTO->getNumIdRecurso());
      $objRelPerfilItemMenuDTO->setNumIdPerfil($numIdPerfilSeiAdministrador);

      if ($objRelPerfilItemMenuRN->contar($objRelPerfilItemMenuDTO)==0) {
        $objRelPerfilItemMenuRN->cadastrar($objRelPerfilItemMenuDTO);
      }

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->retNumIdRecurso();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuContato);
      $objItemMenuDTO->setStrRotulo('Reativar');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu \'Contatos/Reativar\' do sistema SEI não encontrado.');
      }
      $objItemMenuDTO->setNumSequencia(30);
      $objItemMenuRN->alterar($objItemMenuDTO);

      $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
      $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objRelPerfilItemMenuDTO->setNumIdMenu($objItemMenuDTO->getNumIdMenu());
      $objRelPerfilItemMenuDTO->setNumIdItemMenu($objItemMenuDTO->getNumIdItemMenu());
      $objRelPerfilItemMenuDTO->setNumIdRecurso($objItemMenuDTO->getNumIdRecurso());
      $objRelPerfilItemMenuDTO->setNumIdPerfil($numIdPerfilSeiAdministrador);

      if ($objRelPerfilItemMenuRN->contar($objRelPerfilItemMenuDTO)==0) {
        $objRelPerfilItemMenuRN->cadastrar($objRelPerfilItemMenuDTO);
      }


      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->retNumIdRecurso();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuContato);
      $objItemMenuDTO->setStrRotulo('Tipos de Contexto');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu \'Contatos/Tipos de Contexto\' do sistema SEI não encontrado.');
      }
      $objItemMenuDTO->setStrRotulo("Tipos");
      $objItemMenuDTO->setNumSequencia(40);
      $objItemMenuRN->alterar($objItemMenuDTO);


      $objItemMenuDTO2 = new ItemMenuDTO();
      $objItemMenuDTO2->retTodos();
      $objItemMenuDTO2->setNumIdMenuPai($objItemMenuDTO->getNumIdMenu());
      $objItemMenuDTO2->setNumIdItemMenuPai($objItemMenuDTO->getNumIdItemMenu());
      $objItemMenuDTO2->setNumIdSistema($numIdSistemaSei);
      $arrObjItemMenuDTO =  $objItemMenuRN->listar($objItemMenuDTO2);

      foreach($arrObjItemMenuDTO as $objItemMenuDTO){
        $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
        $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistemaSei);
        $objRelPerfilItemMenuDTO->setNumIdMenu($objItemMenuDTO->getNumIdMenu());
        $objRelPerfilItemMenuDTO->setNumIdItemMenu($objItemMenuDTO->getNumIdItemMenu());
        $objRelPerfilItemMenuDTO->setNumIdRecurso($objItemMenuDTO->getNumIdRecurso());
        $objRelPerfilItemMenuDTO->setNumIdPerfil($numIdPerfilSeiAdministrador);

        if ($objRelPerfilItemMenuRN->contar($objRelPerfilItemMenuDTO)==0) {
          $objRelPerfilItemMenuRN->cadastrar($objRelPerfilItemMenuDTO);
        }
      }


      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->retNumIdRecurso();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuContato);
      $objItemMenuDTO->setStrRotulo('Cargos');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu \'Contatos/Cargos\' do sistema SEI não encontrado.');
      }

      $objItemMenuDTO2 = new ItemMenuDTO();
      $objItemMenuDTO2->retTodos();
      $objItemMenuDTO2->setNumIdMenuPai($objItemMenuDTO->getNumIdMenu());
      $objItemMenuDTO2->setNumIdItemMenuPai($objItemMenuDTO->getNumIdItemMenu());
      $objItemMenuDTO2->setNumIdSistema($numIdSistemaSei);
      $arrObjItemMenuDTO =  $objItemMenuRN->listar($objItemMenuDTO2);

      foreach($arrObjItemMenuDTO as $objItemMenuDTO){
        $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
        $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistemaSei);
        $objRelPerfilItemMenuDTO->setNumIdMenu($objItemMenuDTO->getNumIdMenu());
        $objRelPerfilItemMenuDTO->setNumIdItemMenu($objItemMenuDTO->getNumIdItemMenu());
        $objRelPerfilItemMenuDTO->setNumIdRecurso($objItemMenuDTO->getNumIdRecurso());
        $objRelPerfilItemMenuDTO->setNumIdPerfil($numIdPerfilSeiAdministrador);

        if ($objRelPerfilItemMenuRN->contar($objRelPerfilItemMenuDTO)==0) {
          $objRelPerfilItemMenuRN->cadastrar($objRelPerfilItemMenuDTO);
        }
      }

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->retNumIdRecurso();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuContato);
      $objItemMenuDTO->setStrRotulo('Tratamentos');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu \'Contatos/Tratamentos\' do sistema SEI não encontrado.');
      }

      $objItemMenuDTO2 = new ItemMenuDTO();
      $objItemMenuDTO2->retTodos();
      $objItemMenuDTO2->setNumIdMenuPai($objItemMenuDTO->getNumIdMenu());
      $objItemMenuDTO2->setNumIdItemMenuPai($objItemMenuDTO->getNumIdItemMenu());
      $objItemMenuDTO2->setNumIdSistema($numIdSistemaSei);
      $arrObjItemMenuDTO =  $objItemMenuRN->listar($objItemMenuDTO2);

      foreach($arrObjItemMenuDTO as $objItemMenuDTO){
        $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
        $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistemaSei);
        $objRelPerfilItemMenuDTO->setNumIdMenu($objItemMenuDTO->getNumIdMenu());
        $objRelPerfilItemMenuDTO->setNumIdItemMenu($objItemMenuDTO->getNumIdItemMenu());
        $objRelPerfilItemMenuDTO->setNumIdRecurso($objItemMenuDTO->getNumIdRecurso());
        $objRelPerfilItemMenuDTO->setNumIdPerfil($numIdPerfilSeiAdministrador);

        if ($objRelPerfilItemMenuRN->contar($objRelPerfilItemMenuDTO)==0) {
          $objRelPerfilItemMenuRN->cadastrar($objRelPerfilItemMenuDTO);
        }
      }

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->retNumIdRecurso();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuContato);
      $objItemMenuDTO->setStrRotulo('Vocativos');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu \'Contatos/Vocativos\' do sistema SEI não encontrado.');
      }

      $objRecursoDTO = new RecursoDTO();
      $objRecursoDTO->retNumIdRecurso();
      $objRecursoDTO->setNumIdSistema($numIdSistemaSei);
      $objRecursoDTO->setStrNome('contato_listar');
      $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);
      $objItemMenuDTO = $this->adicionarItemMenu($numIdSistemaSei,$numIdPerfilSeiBasico,$numIdMenuSei,null,$objRecursoDTO->getNumIdRecurso(),'Contatos', 55);

      $objItemMenuDTO2 = new ItemMenuDTO();
      $objItemMenuDTO2->retTodos();
      $objItemMenuDTO2->setNumIdMenuPai($objItemMenuDTO->getNumIdMenu());
      $objItemMenuDTO2->setNumIdItemMenuPai($objItemMenuDTO->getNumIdItemMenu());
      $objItemMenuDTO2->setNumIdSistema($numIdSistemaSei);
      $arrObjItemMenuDTO =  $objItemMenuRN->listar($objItemMenuDTO2);

      foreach($arrObjItemMenuDTO as $objItemMenuDTO){
        $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
        $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistemaSei);
        $objRelPerfilItemMenuDTO->setNumIdMenu($objItemMenuDTO->getNumIdMenu());
        $objRelPerfilItemMenuDTO->setNumIdItemMenu($objItemMenuDTO->getNumIdItemMenu());
        $objRelPerfilItemMenuDTO->setNumIdRecurso($objItemMenuDTO->getNumIdRecurso());
        $objRelPerfilItemMenuDTO->setNumIdPerfil($numIdPerfilSeiAdministrador);

        if ($objRelPerfilItemMenuRN->contar($objRelPerfilItemMenuDTO)==0) {
          $objRelPerfilItemMenuRN->cadastrar($objRelPerfilItemMenuDTO);
        }
      }

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdMenuPai(null);
      $objItemMenuDTO->setNumIdItemMenuPai(null);
      $objItemMenuDTO->setStrRotulo('Localizadores');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu \'Localizadores\' do sistema SEI não encontrado.');
      }
      $numIdItemMenuLocalizadoresSei = $objItemMenuDTO->getNumIdItemMenu();

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->retNumIdRecurso();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuSeiAdministracao);
      $objItemMenuDTO->setStrRotulo('Tipos de Suporte');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu \'Administração/Tipos de Suporte\' do sistema SEI não encontrado.');
      }
      $objItemMenuDTO->setStrRotulo("Suportes");
      $objItemMenuDTO->setNumSequencia(50);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuLocalizadoresSei);
      $objItemMenuRN->alterar($objItemMenuDTO);


      $objItemMenuDTO2 = new ItemMenuDTO();
      $objItemMenuDTO2->retTodos();
      $objItemMenuDTO2->setNumIdMenuPai($objItemMenuDTO->getNumIdMenu());
      $objItemMenuDTO2->setNumIdItemMenuPai($objItemMenuDTO->getNumIdItemMenu());
      $objItemMenuDTO2->setNumIdSistema($numIdSistemaSei);
      $arrObjItemMenuDTO =  $objItemMenuRN->listar($objItemMenuDTO2);


      $arrRecursosTipoSuporte = array();
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiArquivamento, 'tipo_suporte_alterar');
      $arrRecursosTipoSuporte[] = $objRecursoDTO->getNumIdRecurso();
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiArquivamento, 'tipo_suporte_cadastrar');
      $arrRecursosTipoSuporte[] = $objRecursoDTO->getNumIdRecurso();
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiArquivamento, 'tipo_suporte_desativar');
      $arrRecursosTipoSuporte[] = $objRecursoDTO->getNumIdRecurso();
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiArquivamento, 'tipo_suporte_excluir');
      $arrRecursosTipoSuporte[] = $objRecursoDTO->getNumIdRecurso();
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiArquivamento, 'tipo_suporte_reativar');
      $arrRecursosTipoSuporte[] = $objRecursoDTO->getNumIdRecurso();
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiArquivamento, 'tipo_suporte_listar');
      $arrRecursosTipoSuporte[] = $objRecursoDTO->getNumIdRecurso();

      $objRecursoDTO = new RecursoDTO;
      $objRecursoDTO->retNumIdRecurso();
      $objRecursoDTO->setStrNome('tipo_suporte_consultar');
      $objRecursoDTO->setNumIdSistema($numIdSistemaSei);
      $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);
      $arrRecursosTipoSuporte[] = $objRecursoDTO->getNumIdRecurso();

      $objRecursoDTO = new RecursoDTO;
      $objRecursoDTO->retNumIdRecurso();
      $objRecursoDTO->setStrNome('tipo_suporte_selecionar');
      $objRecursoDTO->setNumIdSistema($numIdSistemaSei);
      $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);
      $arrRecursosTipoSuporte[] = $objRecursoDTO->getNumIdRecurso();


      foreach($arrObjItemMenuDTO as $objItemMenuDTO){

        $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
        $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistemaSei);
        $objRelPerfilItemMenuDTO->setNumIdMenu($objItemMenuDTO->getNumIdMenu());
        $objRelPerfilItemMenuDTO->setNumIdItemMenu($objItemMenuDTO->getNumIdItemMenu());
        $objRelPerfilItemMenuDTO->setNumIdRecurso($objItemMenuDTO->getNumIdRecurso());
        $objRelPerfilItemMenuDTO->setNumIdPerfil($numIdPerfilSeiAdministrador);

        if ($objRelPerfilItemMenuRN->contar($objRelPerfilItemMenuDTO)) {
          $objRelPerfilItemMenuRN->excluir(array($objRelPerfilItemMenuDTO));
        }

        $objRelPerfilItemMenuDTO->setNumIdPerfil($numIdPerfilSeiArquivamento);

        if ($objRelPerfilItemMenuRN->contar($objRelPerfilItemMenuDTO)==0) {
          $objRelPerfilItemMenuRN->cadastrar($objRelPerfilItemMenuDTO);
        }
      }

      foreach($arrRecursosTipoSuporte as $numIdRecurso){
        $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
        $objRelPerfilRecursoDTO->setNumIdSistema($numIdSistemaSei);
        $objRelPerfilRecursoDTO->setNumIdPerfil($numIdPerfilSeiAdministrador);
        $objRelPerfilRecursoDTO->setNumIdRecurso($numIdRecurso);

        if ($objRelPerfilRecursoRN->contar($objRelPerfilRecursoDTO)) {
          $objRelPerfilRecursoRN->excluir(array($objRelPerfilRecursoDTO));
        }
      }


      $objRecursoDTO = new RecursoDTO();
      $objRecursoDTO->retNumIdRecurso();
      $objRecursoDTO->setNumIdSistema($numIdSistemaSei);
      $objRecursoDTO->setStrNome('grupo_contato_listar');

      $objRecursoRN = new RecursoRN();
      $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuContato);
      $objItemMenuDTO->setStrRotulo('Grupos');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu \'Contatos/Grupos\' do sistema SEI não encontrado.');
      }
      $objItemMenuDTO->setStrRotulo("Contatos");
      $objItemMenuDTO->setNumIdItemMenuPai($idMenuGrupos);
      $objItemMenuDTO->setNumSequencia(0);
      $objItemMenuDTO->setNumIdRecurso($objRecursoDTO->getNumIdRecurso());
      $objItemMenuRN->alterar($objItemMenuDTO);

      $numIdItemMenuGruposContatos=$objItemMenuDTO->getNumIdItemMenu();

      $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
      $objRelPerfilItemMenuDTO->setNumIdPerfil($numIdPerfilSeiBasico);
      $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objRelPerfilItemMenuDTO->setNumIdRecurso($objRecursoDTO->getNumIdRecurso());
      $objRelPerfilItemMenuDTO->setNumIdMenu($numIdMenuSei);
      $objRelPerfilItemMenuDTO->setNumIdItemMenu($numIdItemMenuGruposContatos);

      $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();

      if ($objRelPerfilItemMenuRN->contar($objRelPerfilItemMenuDTO)==0){
        $objRelPerfilItemMenuRN->cadastrar($objRelPerfilItemMenuDTO);
      }

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuGruposContatos);
      $objItemMenuDTO->setStrRotulo('Novo');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      $objItemMenuRN->excluir(array($objItemMenuDTO));

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuGruposContatos);
      $objItemMenuDTO->setStrRotulo('Listar');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      $objItemMenuRN->excluir(array($objItemMenuDTO));

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setStrRotulo('Contextos Temporários');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO == null){
        throw new InfraException('Item de menu \'Contextos Temporários\' do sistema SEI não encontrado.');
      }
      $objItemMenuDTO->setStrRotulo("Contatos Temporários");
      $objItemMenuRN->alterar($objItemMenuDTO);

      $objItemMenuDTO=$this->adicionarItemMenu($numIdSistemaSei,$numIdPerfilSeiAdministrador,$numIdMenuSei,$numIdItemMenuGruposInstitucionais,$objRecursoDTO->getNumIdRecurso(),'Contatos',0);
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'grupo_contato_institucional_alterar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'grupo_contato_institucional_cadastrar');
      $this->adicionarItemMenu($numIdSistemaSei,$numIdPerfilSeiAdministrador,$numIdMenuSei,$objItemMenuDTO->getNumIdItemMenu(),$objRecursoDTO->getNumIdRecurso(),'Novo',10);
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'grupo_contato_institucional_desativar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'grupo_contato_institucional_excluir');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'grupo_contato_institucional_reativar');
      $this->adicionarItemMenu($numIdSistemaSei,$numIdPerfilSeiAdministrador,$numIdMenuSei,$objItemMenuDTO->getNumIdItemMenu(),$objRecursoDTO->getNumIdRecurso(),'Reativar',30);
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'grupo_contato_institucional_consultar');
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'grupo_contato_institucional_listar');
      $this->adicionarItemMenu($numIdSistemaSei,$numIdPerfilSeiAdministrador,$numIdMenuSei,$objItemMenuDTO->getNumIdItemMenu(),$objRecursoDTO->getNumIdRecurso(),'Listar',20);
      $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'grupo_contato_institucional_selecionar');

      $this->removerRecurso($numIdSistemaSei,'contexto_selecionar_usuario');
      $this->removerRecurso($numIdSistemaSei,'contexto_selecionar_unidade');
      $this->removerRecurso($numIdSistemaSei,'contexto_selecionar_email');
      $this->removerRecurso($numIdSistemaSei,'contexto_pesquisar_temporario');
      $this->removerRecurso($numIdSistemaSei,'contexto_cadastrar');
      $this->removerRecurso($numIdSistemaSei,'contexto_alterar');
      $this->removerRecurso($numIdSistemaSei,'contexto_consultar');
      $this->removerRecurso($numIdSistemaSei,'contexto_listar');
      $this->removerRecurso($numIdSistemaSei,'contexto_excluir');
      $this->removerRecurso($numIdSistemaSei,'contexto_desativar');
      $this->removerRecurso($numIdSistemaSei,'contexto_reativar');
      $this->removerRecurso($numIdSistemaSei,'contexto_selecionar');

      $this->renomearRecurso($numIdSistemaSei,'contexto_relatorio_temporarios','contato_relatorio_temporarios');
      $this->renomearRecurso($numIdSistemaSei,'contexto_alterar_temporario','contato_alterar_temporario');
      $this->renomearRecurso($numIdSistemaSei,'contexto_desativar_temporario','contato_desativar_temporario');
      $this->renomearRecurso($numIdSistemaSei,'contexto_excluir_temporario','contato_excluir_temporario');
      $this->renomearRecurso($numIdSistemaSei,'contato_substituir','contato_substituir_temporario');
      $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'contato_alterar');
      $this->removerRecurso($numIdSistemaSei,'contato_visualizar_selecao');
      $this->removerRecurso($numIdSistemaSei,'contato_adicionar_selecao');
      $this->removerRecurso($numIdSistemaSei,'contato_desfazer_selecao');
      $this->removerRecurso($numIdSistemaSei,'grupo_contato_adicionar_escolher');
      $this->removerRecurso($numIdSistemaSei,'grupo_contato_adicionar');
      $this->removerRecurso($numIdSistemaSei,'grupo_contato_criar');

      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'grupo_contato_cadastrar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'grupo_contato_alterar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'grupo_contato_excluir');

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setStrRotulo('Tarjas de Assinatura');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      if ($objItemMenuDTO != null){
        $objItemMenuDTO->setStrRotulo("Tarjas");
        $objItemMenuRN->alterar($objItemMenuDTO);
      }

      $objRecursoDTO=$this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'monitoramento_servico_listar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'monitoramento_servico_excluir');

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuSeiAdministracao);
      $objItemMenuDTO->setStrRotulo('Sistemas');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
      $this->adicionarItemMenu($numIdSistemaSei,$numIdPerfilSeiAdministrador,$numIdMenuSei,$objItemMenuDTO->getNumIdItemMenu(),$objRecursoDTO->getNumIdRecurso(),'Monitoramento de Serviços',40);

      $objItemMenuDTO=$this->adicionarItemMenu($numIdSistemaSei,$numIdPerfilSeiAdministrador,$numIdMenuSei,$numIdItemMenuSeiAdministracao,null,'Países, Estados e Cidades',0);
      $numIdItemMenuPaises=$objItemMenuDTO->getNumIdItemMenu();
      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuSeiAdministracao);
      $objItemMenuDTO->setStrRotulo('Países');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

      $objItemMenuDTO->setNumSequencia(10);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuPaises);
      $objItemMenuRN->alterar($objItemMenuDTO);

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuSeiAdministracao);
      $objItemMenuDTO->setStrRotulo('UFs');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

      $objItemMenuDTO->setStrRotulo("Estados");
      $objItemMenuDTO->setNumSequencia(20);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuPaises);
      $objItemMenuRN->alterar($objItemMenuDTO);

      $objItemMenuDTO2 = new ItemMenuDTO();
      $objItemMenuDTO2->retNumIdItemMenu();
      $objItemMenuDTO2->retNumIdMenu();
      $objItemMenuDTO2->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO2->setNumIdItemMenuPai($objItemMenuDTO->getNumIdItemMenu());
      $objItemMenuDTO2->setStrRotulo('Nova');
      $objItemMenuDTO2 = $objItemMenuRN->consultar($objItemMenuDTO2);

      $objItemMenuDTO2->setStrRotulo("Novo");
      $objItemMenuRN->alterar($objItemMenuDTO2);

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuSeiAdministracao);
      $objItemMenuDTO->setStrRotulo('Cidades');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

      $objItemMenuDTO->setNumSequencia(30);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuPaises);
      $objItemMenuRN->alterar($objItemMenuDTO);

      $this->renomearRecurso($numIdSistemaSei,'tipo_contexto_contato_alterar','tipo_contato_alterar');
      $this->renomearRecurso($numIdSistemaSei,'tipo_contexto_contato_cadastrar','tipo_contato_cadastrar');
      $this->renomearRecurso($numIdSistemaSei,'tipo_contexto_contato_consultar','tipo_contato_consultar');
      $this->renomearRecurso($numIdSistemaSei,'tipo_contexto_contato_desativar','tipo_contato_desativar');
      $this->renomearRecurso($numIdSistemaSei,'tipo_contexto_contato_excluir','tipo_contato_excluir');
      $this->renomearRecurso($numIdSistemaSei,'tipo_contexto_contato_listar','tipo_contato_listar');
      $this->renomearRecurso($numIdSistemaSei,'tipo_contexto_contato_reativar','tipo_contato_reativar');
      $this->renomearRecurso($numIdSistemaSei,'tipo_contexto_contato_selecionar','tipo_contato_selecionar');

      $this->renomearRecurso($numIdSistemaSei,'rel_unidade_tipo_cont_contato_cadastrar','rel_unidade_tipo_contato_cadastrar');
      $this->renomearRecurso($numIdSistemaSei,'rel_unidade_tipo_cont_contato_listar','rel_unidade_tipo_contato_listar');
      $this->renomearRecurso($numIdSistemaSei,'rel_unidade_tipo_cont_contato_excluir','rel_unidade_tipo_contato_excluir');

      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'contato_definir');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'contato_cadastrar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'contato_alterar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'contato_desativar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'contato_excluir');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'contato_reativar');

      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'formulario_gerar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'formulario_alterar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'formulario_consultar');

      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'atributo_cadastrar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'atributo_alterar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'atributo_desativar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'atributo_excluir');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'atributo_reativar');

      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'dominio_cadastrar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'dominio_alterar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'dominio_desativar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'dominio_excluir');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'dominio_reativar');

      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'marcador_cadastrar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'marcador_alterar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'marcador_consultar');
      $objRecursoDTO=$this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'marcador_listar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'marcador_excluir');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'marcador_desativar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'marcador_reativar');

      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'andamento_marcador_gerenciar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'andamento_marcador_consultar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'andamento_marcador_listar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'andamento_marcador_excluir');

      $this->adicionarItemMenu($numIdSistemaSei,$numIdPerfilSeiBasico,$numIdMenuSei,null,$objRecursoDTO->getNumIdRecurso(),'Marcadores',76);

      $this->renomearRecurso($numIdSistemaSei,'controle_unidade_gerenciar','andamento_situacao_gerenciar');

      $this->removerRecurso($numIdSistemaSei,'atributo_andamento_situacao_cadastrar');
      $this->removerRecurso($numIdSistemaSei,'atributo_andamento_situacao_consultar');
      $this->removerRecurso($numIdSistemaSei,'atributo_andamento_situacao_excluir');
      $this->removerRecurso($numIdSistemaSei,'atributo_andamento_situacao_listar');
      $this->removerRecurso($numIdSistemaSei,'rel_proced_situacao_unidade_cadastrar');
      $this->removerRecurso($numIdSistemaSei,'rel_proced_situacao_unidade_consultar');
      $this->removerRecurso($numIdSistemaSei,'rel_proced_situacao_unidade_excluir');
      $this->removerRecurso($numIdSistemaSei,'rel_proced_situacao_unidade_listar');

      $this->removerRecurso($numIdSistemaSei,'procedimento_acessar');
      $this->removerRecurso($numIdSistemaSei,'credencial_acessar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'usuario_validar_acesso');

      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'serie_restricao_cadastrar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'serie_restricao_consultar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'serie_restricao_listar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'serie_restricao_excluir');

      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'tipo_proced_restricao_cadastrar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'tipo_proced_restricao_consultar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'tipo_proced_restricao_listar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'tipo_proced_restricao_excluir');

      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'assunto_proxy_cadastrar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'assunto_proxy_alterar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'assunto_proxy_excluir');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'assunto_proxy_consultar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'assunto_proxy_listar');

      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'tabela_assuntos_ativar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'tabela_assuntos_cadastrar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'tabela_assuntos_alterar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'tabela_assuntos_excluir');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'tabela_assuntos_consultar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'tabela_assuntos_listar');

      $this->renomearRecurso($numIdSistemaSei,'protocolo_arquivamento_receber','arquivamento_receber');
      $this->renomearRecurso($numIdSistemaSei,'protocolo_arquivamento_listar','arquivamento_listar');
      $this->renomearRecurso($numIdSistemaSei,'protocolo_arquivamento_cancelar_recebimento','arquivamento_cancelar_recebimento');
      $this->renomearRecurso($numIdSistemaSei,'protocolo_arquivar','arquivamento_arquivar');
      $this->renomearRecurso($numIdSistemaSei,'protocolo_desarquivar','arquivamento_desarquivar');
      $this->renomearRecurso($numIdSistemaSei,'protocolo_solicitar_desarquivamento','arquivamento_solicitar_desarquivamento');
      $this->renomearRecurso($numIdSistemaSei,'protocolo_cancelar_solicitacao_desarquivamento','arquivamento_cancelar_solicitacao_desarquivamento');
      $this->renomearRecurso($numIdSistemaSei,'protocolo_desarquivamento_listar','arquivamento_desarquivamento_listar');
      $this->renomearRecurso($numIdSistemaSei,'protocolo_migrar_localizador','arquivamento_migrar_localizador');
      $this->renomearRecurso($numIdSistemaSei,'protocolo_arquivamento_pesquisar','arquivamento_pesquisar');

      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'procedimento_bloquear');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'procedimento_desbloquear');

      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'unidade_selecionar_orgao');

      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'rel_protocolo_assunto_consultar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'rel_tipo_procedimento_assunto_consultar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'rel_serie_assunto_consultar');

      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'mapeamento_assunto_gerenciar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'mapeamento_assunto_cadastrar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'mapeamento_assunto_excluir');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'mapeamento_assunto_consultar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'mapeamento_assunto_listar');

      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiArquivamento,'arquivamento_excluir');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'arquivamento_consultar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'arquivamento_listar');

      $objRecursoDTO=$this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiArquivamento,'gerar_estatisticas_arquivamento');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiArquivamento,'estatisticas_detalhar_arquivamento');

      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'documento_cancelar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'documento_versao_comparar');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiBasico,'ajuda_variaveis_secao_modelo');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'ajuda_variaveis_tarjas');
      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'ajuda_variaveis_email_sistema');

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai(null);
      $objItemMenuDTO->setStrRotulo('Estatísticas');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

      $this->adicionarItemMenu($numIdSistemaSei,$numIdPerfilSeiArquivamento,$numIdMenuSei,$objItemMenuDTO->getNumIdItemMenu(),$objRecursoDTO->getNumIdRecurso(),'Arquivamento',0);

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuSeiAdministracao);
      $objItemMenuDTO->setStrRotulo('Assuntos');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

      $objItemMenuDTO2 = new ItemMenuDTO();
      $objItemMenuDTO2->retNumIdItemMenu();
      $objItemMenuDTO2->retNumIdMenu();
      $objItemMenuDTO2->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO2->setNumIdItemMenuPai($objItemMenuDTO->getNumIdItemMenu());
      $objItemMenuRN->excluir($objItemMenuRN->listar($objItemMenuDTO2));

      $objRecursoDTO=new RecursoDTO();
      $objRecursoDTO->retNumIdRecurso();
      $objRecursoDTO->setStrNome('tabela_assuntos_listar');
      $objRecursoDTO=$objRecursoRN->consultar($objRecursoDTO);
      $objItemMenuDTO->setStrRotulo('Tabela de Assuntos');
      $objItemMenuDTO->setNumIdRecurso($objRecursoDTO->getNumIdRecurso());
      $objItemMenuRN->alterar($objItemMenuDTO);

      $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
      $objRelPerfilRecursoDTO->setNumIdSistema($numIdSistemaSei);
      $objRelPerfilRecursoDTO->setNumIdPerfil($numIdPerfilSeiAdministrador);
      $objRelPerfilRecursoDTO->setNumIdRecurso($objRecursoDTO->getNumIdRecurso());

      $objRelPerfilRecursoRN = new RelPerfilRecursoRN();

      if ($objRelPerfilRecursoRN->contar($objRelPerfilRecursoDTO)==0){
        $objRelPerfilRecursoRN->cadastrar($objRelPerfilRecursoDTO);
      }

      $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
      $objRelPerfilItemMenuDTO->setNumIdPerfil($numIdPerfilSeiAdministrador);
      $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objRelPerfilItemMenuDTO->setNumIdRecurso($objRecursoDTO->getNumIdRecurso());
      $objRelPerfilItemMenuDTO->setNumIdMenu($numIdMenuSei);
      $objRelPerfilItemMenuDTO->setNumIdItemMenu($objItemMenuDTO->getNumIdItemMenu());

      $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();

      if ($objRelPerfilItemMenuRN->contar($objRelPerfilItemMenuDTO)==0){
        $objRelPerfilItemMenuRN->cadastrar($objRelPerfilItemMenuDTO);
      }

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuContato);
      $objItemMenuDTO->setStrRotulo('Títulos');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

      $objItemMenuDTO2 = new ItemMenuDTO();
      $objItemMenuDTO2->retNumIdItemMenu();
      $objItemMenuDTO2->retNumIdMenu();
      $objItemMenuDTO2->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO2->setNumIdItemMenuPai($objItemMenuDTO->getNumIdItemMenu());
      $objItemMenuRN->excluir($objItemMenuRN->listar($objItemMenuDTO2));
      $objItemMenuRN->excluir(array($objItemMenuDTO));

      $this->removerRecurso($numIdSistemaSei,'titulo_alterar');
      $this->removerRecurso($numIdSistemaSei,'titulo_cadastrar');
      $this->removerRecurso($numIdSistemaSei,'titulo_consultar');
      $this->removerRecurso($numIdSistemaSei,'titulo_desativar');
      $this->removerRecurso($numIdSistemaSei,'titulo_excluir');
      $this->removerRecurso($numIdSistemaSei,'titulo_listar');
      $this->removerRecurso($numIdSistemaSei,'titulo_reativar');
      $this->removerRecurso($numIdSistemaSei,'titulo_selecionar');

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai(null);
      $objItemMenuDTO->setStrRotulo('Arquivo');
      $objItemMenuRN->excluir($objItemMenuRN->listar($objItemMenuDTO));


      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai(null);
      $objItemMenuDTO->setStrRotulo('Modelos');
      $objItemMenuDTO=$objItemMenuRN->consultar($objItemMenuDTO);

      $objItemMenuDTO->setStrRotulo('Modelos Favoritos');
      $objItemMenuRN->alterar($objItemMenuDTO);


      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuSeiAdministracao);
      $objItemMenuDTO->setStrRotulo('Unidades');
      $objItemMenuDTO=$objItemMenuRN->consultar($objItemMenuDTO);

      $objRecursoDTO=$this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiAdministrador,'unidade_migrar');
      $this->adicionarItemMenu($numIdSistemaSei,$numIdPerfilSeiAdministrador,$numIdMenuSei,$objItemMenuDTO->getNumIdItemMenu(),$objRecursoDTO->getNumIdRecurso(),'Migrar Dados',30);

      $this->adicionarRecursoPerfil($numIdSistemaSei,$numIdPerfilSeiArquivamento,'estatisticas_grafico_exibir');

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSip);
      $objItemMenuDTO->setNumIdItemMenuPai(null);
      $objItemMenuDTO->setStrRotulo('Unidades');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

      $objItemMenuDTO2 = new ItemMenuDTO();
      $objItemMenuDTO2->retNumIdItemMenu();
      $objItemMenuDTO2->retNumIdMenu();
      $objItemMenuDTO2->setNumIdSistema($numIdSistemaSip);
      $objItemMenuDTO2->setNumIdItemMenuPai($objItemMenuDTO->getNumIdItemMenu());
      $objItemMenuDTO2->setStrRotulo('Mapeamentos');
      $objItemMenuRN->excluir($objItemMenuRN->listar($objItemMenuDTO2));

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuSeiAdministracao);
      $objItemMenuDTO->setStrRotulo('Publicação');
      $objItemMenuDTOPublicacao = $objItemMenuRN->consultar($objItemMenuDTO);

      $objItemMenuDTOPublicacao->setStrRotulo('Veículos de Publicação');
      $objItemMenuRN->alterar($objItemMenuDTOPublicacao);

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($objItemMenuDTOPublicacao->getNumIdItemMenu());
      $objItemMenuDTO->setStrRotulo('Feriados');
      $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

      $objItemMenuDTO->setNumSequencia(0);
      $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuSeiAdministracao);
      $objItemMenuRN->alterar($objItemMenuDTO);

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($objItemMenuDTOPublicacao->getNumIdItemMenu());
      $objItemMenuDTO->setStrRotulo('Veículos');
      $objItemMenuDTOVeiculos = $objItemMenuRN->consultar($objItemMenuDTO);

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
      $objItemMenuDTO->setNumIdItemMenuPai($objItemMenuDTOVeiculos->getNumIdItemMenu());
      $arrObjItemMenuDTO = $objItemMenuRN->listar($objItemMenuDTO);

      foreach($arrObjItemMenuDTO as $objItemMenuDTO){
        $objItemMenuDTO->setNumIdItemMenuPai($objItemMenuDTOPublicacao->getNumIdItemMenu());
        $objItemMenuRN->alterar($objItemMenuDTO);
      }
      $this->removerItemMenu($numIdSistemaSei, $objItemMenuDTOVeiculos->getNumIdMenu(), $objItemMenuDTOVeiculos->getNumIdItemMenu());


      $objRegraAuditoriaDTO = new RegraAuditoriaDTO();
      $objRegraAuditoriaDTO->retNumIdRegraAuditoria();
      $objRegraAuditoriaDTO->setNumIdSistema($numIdSistemaSei);
      $objRegraAuditoriaDTO->setStrDescricao('Geral');

      $objRegraAuditoriaRN = new RegraAuditoriaRN();
      $objRegraAuditoriaDTO = $objRegraAuditoriaRN->consultar($objRegraAuditoriaDTO);

      /** @noinspection SqlResolve */
      $rs = BancoSip::getInstance()->consultarSql('select id_recurso from recurso where id_sistema='.$numIdSistemaSei.' and nome in (
      \'mapeamento_assunto_gerenciar\',
      \'mapeamento_assunto_cadastrar\',
      \'mapeamento_assunto_excluir\',
      \'arquivamento_migrar_localizador\',
      \'tipo_formulario_cadastrar\',
      \'grupo_contato_institucional_alterar\',
      \'grupo_contato_institucional_cadastrar\',
      \'grupo_contato_institucional_desativar\',
      \'grupo_contato_institucional_excluir\',
      \'grupo_contato_institucional_reativar\',
      \'monitoramento_servico_excluir\',
      \'formulario_gerar\',
      \'formulario_alterar\',
      \'atributo_cadastrar\',
      \'atributo_alterar\',
      \'atributo_desativar\',
      \'atributo_excluir\',
      \'atributo_reativar\',
      \'dominio_cadastrar\',
      \'dominio_alterar\',
      \'dominio_desativar\',
      \'dominio_excluir\',
      \'dominio_reativar\',
      \'marcador_cadastrar\',
      \'marcador_alterar\',
      \'marcador_excluir\',
      \'marcador_desativar\',
      \'marcador_reativar\',
      \'tabela_assuntos_ativar\',
      \'tabela_assuntos_cadastrar\',
      \'tabela_assuntos_alterar\',
      \'tabela_assuntos_excluir\',
      \'tipo_formulario_alterar\',
      \'tipo_formulario_clonar\',
      \'tipo_formulario_desativar\',
      \'tipo_formulario_excluir\',
      \'tipo_formulario_reativar\',
      \'documento_gerar_circular\',
      \'procedimento_duplicar\',
      \'documento_cancelar\',
      \'procedimento_bloquear\',
      \'procedimento_desbloquear\',
      \'procedimento_credencial_ativar\',
      \'procedimento_credencial_cancelar\',
      \'unidade_migrar\',
      \'secao_modelo_cadastrar\',
      \'secao_modelo_alterar\',
      \'secao_modelo_excluir\',
      \'secao_modelo_desativar\',
      \'secao_modelo_reativar\',
      \'usuario_validar_acesso\')');

      foreach($rs as $recurso){
        $rs2 = BancoSip::getInstance()->consultarSql('select count(*) as total from rel_regra_auditoria_recurso where id_regra_auditoria='.$objRegraAuditoriaDTO->getNumIdRegraAuditoria().' and id_sistema='.$numIdSistemaSei.' and id_recurso='.$recurso['id_recurso']);
        if ($rs2[0]['total']==0) {
          BancoSip::getInstance()->executarSql('insert into rel_regra_auditoria_recurso (id_regra_auditoria, id_sistema, id_recurso) values (' . $objRegraAuditoriaDTO->getNumIdRegraAuditoria() . ', ' . $numIdSistemaSei . ', ' . $recurso['id_recurso'] . ')');
        }
      }

      $objReplicacaoRegraAuditoriaDTO = new ReplicacaoRegraAuditoriaDTO();
      $objReplicacaoRegraAuditoriaDTO->setStrStaOperacao('A');
      $objReplicacaoRegraAuditoriaDTO->setNumIdRegraAuditoria($objRegraAuditoriaDTO->getNumIdRegraAuditoria());

      $objSistemaRN = new SistemaRN();
      $objSistemaRN->replicarRegraAuditoria($objReplicacaoRegraAuditoriaDTO);

      BancoSip::getInstance()->executarSql('update infra_parametro set valor=\''.SIP_VERSAO.'\' where nome=\'SIP_VERSAO\'');

      $this->finalizar('FIM',false);

		}catch(Exception $e){
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->setBolEcho(false);
		  throw new InfraException('Erro atualizando versão.', $e);
		}
	}

	private function adicionarRecursoPerfil($numIdSistema, $numIdPerfil, $strNome, $strCaminho = null){

	   $objRecursoDTO = new RecursoDTO();
	   $objRecursoDTO->retNumIdRecurso();
     $objRecursoDTO->setNumIdSistema($numIdSistema);
     $objRecursoDTO->setStrNome($strNome);

     $objRecursoRN = new RecursoRN();
     $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);

     if ($objRecursoDTO==null){

       $objRecursoDTO = new RecursoDTO();
       $objRecursoDTO->setNumIdRecurso(null);
       $objRecursoDTO->setNumIdSistema($numIdSistema);
       $objRecursoDTO->setStrNome($strNome);
       $objRecursoDTO->setStrDescricao(null);

       if ($strCaminho == null){
         $objRecursoDTO->setStrCaminho('controlador.php?acao='.$strNome);
       }else{
         $objRecursoDTO->setStrCaminho($strCaminho);
       }

       $objRecursoDTO->setStrSinAtivo('S');
       $objRecursoDTO = $objRecursoRN->cadastrar($objRecursoDTO);
     }

     if ($numIdPerfil!=null){
       $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
       $objRelPerfilRecursoDTO->setNumIdSistema($numIdSistema);
       $objRelPerfilRecursoDTO->setNumIdPerfil($numIdPerfil);
       $objRelPerfilRecursoDTO->setNumIdRecurso($objRecursoDTO->getNumIdRecurso());

       $objRelPerfilRecursoRN = new RelPerfilRecursoRN();

       if ($objRelPerfilRecursoRN->contar($objRelPerfilRecursoDTO)==0){
         $objRelPerfilRecursoRN->cadastrar($objRelPerfilRecursoDTO);
       }
     }

     return $objRecursoDTO;
	}

  private function removerRecursoPerfil($numIdSistema, $strNome, $numIdPerfil){

    $objRecursoDTO = new RecursoDTO();
    $objRecursoDTO->setBolExclusaoLogica(false);
    $objRecursoDTO->retNumIdRecurso();
    $objRecursoDTO->setNumIdSistema($numIdSistema);
    $objRecursoDTO->setStrNome($strNome);

    $objRecursoRN = new RecursoRN();
    $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);

    if ($objRecursoDTO!=null){
      $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
      $objRelPerfilRecursoDTO->retTodos();
      $objRelPerfilRecursoDTO->setNumIdSistema($numIdSistema);
      $objRelPerfilRecursoDTO->setNumIdRecurso($objRecursoDTO->getNumIdRecurso());
      $objRelPerfilRecursoDTO->setNumIdPerfil($numIdPerfil);

      $objRelPerfilRecursoRN = new RelPerfilRecursoRN();
      $objRelPerfilRecursoRN->excluir($objRelPerfilRecursoRN->listar($objRelPerfilRecursoDTO));

      $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
      $objRelPerfilItemMenuDTO->retTodos();
      $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistema);
      $objRelPerfilItemMenuDTO->setNumIdRecurso($objRecursoDTO->getNumIdRecurso());
      $objRelPerfilItemMenuDTO->setNumIdPerfil($numIdPerfil);

      $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();
      $objRelPerfilItemMenuRN->excluir($objRelPerfilItemMenuRN->listar($objRelPerfilItemMenuDTO));
    }
  }

  private function desativarRecurso($numIdSistema, $strNome){
    $objRecursoDTO = new RecursoDTO();
    $objRecursoDTO->retNumIdRecurso();
    $objRecursoDTO->setNumIdSistema($numIdSistema);
    $objRecursoDTO->setStrNome($strNome);

    $objRecursoRN = new RecursoRN();
    $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);

    if ($objRecursoDTO!=null){
      $objRecursoRN->desativar(array($objRecursoDTO));
    }
  }

  private function removerRecurso($numIdSistema, $strNome){

    $objRecursoDTO = new RecursoDTO();
    $objRecursoDTO->setBolExclusaoLogica(false);
    $objRecursoDTO->retNumIdRecurso();
    $objRecursoDTO->setNumIdSistema($numIdSistema);
    $objRecursoDTO->setStrNome($strNome);

    $objRecursoRN = new RecursoRN();
    $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);

    if ($objRecursoDTO!=null){
      $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
      $objRelPerfilRecursoDTO->retTodos();
      $objRelPerfilRecursoDTO->setNumIdSistema($numIdSistema);
      $objRelPerfilRecursoDTO->setNumIdRecurso($objRecursoDTO->getNumIdRecurso());

      $objRelPerfilRecursoRN = new RelPerfilRecursoRN();
      $objRelPerfilRecursoRN->excluir($objRelPerfilRecursoRN->listar($objRelPerfilRecursoDTO));

      $objItemMenuDTO = new ItemMenuDTO();
      $objItemMenuDTO->retNumIdMenu();
      $objItemMenuDTO->retNumIdItemMenu();
      $objItemMenuDTO->setNumIdSistema($numIdSistema);
      $objItemMenuDTO->setNumIdRecurso($objRecursoDTO->getNumIdRecurso());

      $objItemMenuRN = new ItemMenuRN();
      $arrObjItemMenuDTO = $objItemMenuRN->listar($objItemMenuDTO);

      $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();

      foreach($arrObjItemMenuDTO as $objItemMenuDTO){
        $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
        $objRelPerfilItemMenuDTO->retTodos();
        $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistema);
        $objRelPerfilItemMenuDTO->setNumIdItemMenu($objItemMenuDTO->getNumIdItemMenu());

        $objRelPerfilItemMenuRN->excluir($objRelPerfilItemMenuRN->listar($objRelPerfilItemMenuDTO));
      }

      $objItemMenuRN->excluir($arrObjItemMenuDTO);

      $objRelRegraAuditoriaRecursoDTO = new RelRegraAuditoriaRecursoDTO();
      $objRelRegraAuditoriaRecursoRN=new RelRegraAuditoriaRecursoRN();
      $objRelRegraAuditoriaRecursoDTO->retNumIdSistema();
      $objRelRegraAuditoriaRecursoDTO->retNumIdRecurso();
      $objRelRegraAuditoriaRecursoDTO->retNumIdRegraAuditoria();
      $objRelRegraAuditoriaRecursoDTO->setNumIdRecurso($objRecursoDTO->getNumIdRecurso());

      $arrObjRelRegraAuditoriaRecursoDTO = $objRelRegraAuditoriaRecursoRN->listar($objRelRegraAuditoriaRecursoDTO);
      if (count($arrObjRelRegraAuditoriaRecursoDTO)>0) {
        $objRelRegraAuditoriaRecursoRN->excluir($arrObjRelRegraAuditoriaRecursoDTO);
      }

      $objRecursoRN->excluir(array($objRecursoDTO));
    }
  }

  private function renomearRecurso($numIdSistema, $strNomeAtual, $strNomeNovo){

    $objRecursoDTO = new RecursoDTO();
    $objRecursoDTO->setBolExclusaoLogica(false);
    $objRecursoDTO->retNumIdRecurso();
    $objRecursoDTO->retStrCaminho();
    $objRecursoDTO->setNumIdSistema($numIdSistema);
    $objRecursoDTO->setStrNome($strNomeAtual);

    $objRecursoRN = new RecursoRN();
    $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);

    if ($objRecursoDTO!=null){
      $objRecursoDTO->setStrNome($strNomeNovo);
      $objRecursoDTO->setStrCaminho(str_replace($strNomeAtual,$strNomeNovo,$objRecursoDTO->getStrCaminho()));
      $objRecursoRN->alterar($objRecursoDTO);
    }
  }

  private function adicionarItemMenu($numIdSistema, $numIdPerfil, $numIdMenu, $numIdItemMenuPai, $numIdRecurso, $strRotulo, $numSequencia ){

     $objItemMenuDTO = new ItemMenuDTO();
     $objItemMenuDTO->retNumIdItemMenu();
     $objItemMenuDTO->setNumIdMenu($numIdMenu);

     if ($numIdItemMenuPai==null){
       $objItemMenuDTO->setNumIdMenuPai(null);
       $objItemMenuDTO->setNumIdItemMenuPai(null);
     }else{
       $objItemMenuDTO->setNumIdMenuPai($numIdMenu);
       $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuPai);
     }

     $objItemMenuDTO->setNumIdSistema($numIdSistema);
     $objItemMenuDTO->setNumIdRecurso($numIdRecurso);
     $objItemMenuDTO->setStrRotulo($strRotulo);

     $objItemMenuRN = new ItemMenuRN();
     $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

     if ($objItemMenuDTO==null){

       $objItemMenuDTO = new ItemMenuDTO();
       $objItemMenuDTO->setNumIdItemMenu(null);
       $objItemMenuDTO->setNumIdMenu($numIdMenu);

       if ($numIdItemMenuPai==null){
         $objItemMenuDTO->setNumIdMenuPai(null);
         $objItemMenuDTO->setNumIdItemMenuPai(null);
       }else{
         $objItemMenuDTO->setNumIdMenuPai($numIdMenu);
         $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuPai);
       }

       $objItemMenuDTO->setNumIdSistema($numIdSistema);
       $objItemMenuDTO->setNumIdRecurso($numIdRecurso);
       $objItemMenuDTO->setStrRotulo($strRotulo);
       $objItemMenuDTO->setStrDescricao(null);
       $objItemMenuDTO->setNumSequencia($numSequencia);
       $objItemMenuDTO->setStrSinNovaJanela('N');
       $objItemMenuDTO->setStrSinAtivo('S');
       $objItemMenuDTO = $objItemMenuRN->cadastrar($objItemMenuDTO);
     }


     if ($numIdPerfil!=null && $numIdRecurso!=null){

       $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
       $objRelPerfilRecursoDTO->setNumIdSistema($numIdSistema);
       $objRelPerfilRecursoDTO->setNumIdPerfil($numIdPerfil);
       $objRelPerfilRecursoDTO->setNumIdRecurso($numIdRecurso);

       $objRelPerfilRecursoRN = new RelPerfilRecursoRN();

       if ($objRelPerfilRecursoRN->contar($objRelPerfilRecursoDTO)==0){
         $objRelPerfilRecursoRN->cadastrar($objRelPerfilRecursoDTO);
       }

    	 $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
       $objRelPerfilItemMenuDTO->setNumIdPerfil($numIdPerfil);
       $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistema);
       $objRelPerfilItemMenuDTO->setNumIdRecurso($numIdRecurso);
       $objRelPerfilItemMenuDTO->setNumIdMenu($numIdMenu);
       $objRelPerfilItemMenuDTO->setNumIdItemMenu($objItemMenuDTO->getNumIdItemMenu());

       $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();

       if ($objRelPerfilItemMenuRN->contar($objRelPerfilItemMenuDTO)==0){
         $objRelPerfilItemMenuRN->cadastrar($objRelPerfilItemMenuDTO);
       }
     }

     return $objItemMenuDTO;
	}

  private function removerItemMenu($numIdSistema, $numIdMenu, $numIdItemMenu){

    $objItemMenuDTO = new ItemMenuDTO();
    $objItemMenuDTO->retNumIdMenu();
    $objItemMenuDTO->retNumIdItemMenu();
    $objItemMenuDTO->setNumIdSistema($numIdSistema);
    $objItemMenuDTO->setNumIdMenu($numIdMenu);
    $objItemMenuDTO->setNumIdItemMenu($numIdItemMenu);

    $objItemMenuRN = new ItemMenuRN();
    $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

    if ($objItemMenuDTO!=null) {

      $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
      $objRelPerfilItemMenuDTO->retTodos();
      $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistema);
      $objRelPerfilItemMenuDTO->setNumIdMenu($objItemMenuDTO->getNumIdMenu());
      $objRelPerfilItemMenuDTO->setNumIdItemMenu($objItemMenuDTO->getNumIdItemMenu());

      $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();
      $objRelPerfilItemMenuRN->excluir($objRelPerfilItemMenuRN->listar($objRelPerfilItemMenuDTO));

      $objItemMenuRN->excluir(array($objItemMenuDTO));
    }
  }

	private function removerPerfil($numIdSistema, $strNome){

	  $objPerfilDTO = new PerfilDTO();
	  $objPerfilDTO->retNumIdPerfil();
	  $objPerfilDTO->setNumIdSistema($numIdSistema);
	  $objPerfilDTO->setStrNome($strNome);

	  $objPerfilRN = new PerfilRN();
	  $objPerfilDTO = $objPerfilRN->consultar($objPerfilDTO);

	  if ($objPerfilDTO!=null){

	    $objPermissaoDTO = new PermissaoDTO();
	    $objPermissaoDTO->retNumIdSistema();
	    $objPermissaoDTO->retNumIdUsuario();
	    $objPermissaoDTO->retNumIdPerfil();
	    $objPermissaoDTO->retNumIdUnidade();
	    $objPermissaoDTO->setNumIdSistema($numIdSistema);
	    $objPermissaoDTO->setNumIdPerfil($objPerfilDTO->getNumIdPerfil());

	    $objPermissaoRN = new PermissaoRN();
	    $objPermissaoRN->excluir($objPermissaoRN->listar($objPermissaoDTO));

	    $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
	    $objRelPerfilItemMenuDTO->retTodos();
	    $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistema);
	    $objRelPerfilItemMenuDTO->setNumIdPerfil($objPerfilDTO->getNumIdPerfil());

	    $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();
	    $objRelPerfilItemMenuRN->excluir($objRelPerfilItemMenuRN->listar($objRelPerfilItemMenuDTO));

	    $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
	    $objRelPerfilRecursoDTO->retTodos();
	    $objRelPerfilRecursoDTO->setNumIdSistema($numIdSistema);
	    $objRelPerfilRecursoDTO->setNumIdPerfil($objPerfilDTO->getNumIdPerfil());

	    $objRelPerfilRecursoRN = new RelPerfilRecursoRN();
	    $objRelPerfilRecursoRN->excluir($objRelPerfilRecursoRN->listar($objRelPerfilRecursoDTO));

	    $objCoordenadorPerfilDTO = new CoordenadorPerfilDTO();
	    $objCoordenadorPerfilDTO->retTodos();
	    $objCoordenadorPerfilDTO->setNumIdSistema($numIdSistema);
	    $objCoordenadorPerfilDTO->setNumIdPerfil($objPerfilDTO->getNumIdPerfil());

	    $objCoordenadorPerfilRN = new CoordenadorPerfilRN();
	    $objCoordenadorPerfilRN->excluir($objCoordenadorPerfilRN->listar($objCoordenadorPerfilDTO));

	    $objPerfilRN->excluir(array($objPerfilDTO));
	  }
	}

}
?>