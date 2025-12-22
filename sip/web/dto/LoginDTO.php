<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 06/06/2007 - criado por mga
*
*
* Versão do Gerador de Código:1.2.3
*/

require_once dirname(__FILE__).'/../Sip.php';

class LoginDTO extends InfraDTO {

  public function getStrNomeTabela() {
    return 'login';
  }

  public function montar() {

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'IdLogin',
                                   'id_login');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdSistema',
                                   'id_sistema');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,
                                   'Login',
                                   'dth_login');
                                   
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdUsuario',
                                   'id_usuario');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdContexto',
                                   'id_contexto');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdGrupoRede',
                                   'id_grupo_rede');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdUsuarioEmulador',
                                   'id_usuario_emulador');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'HashInterno',
                                   'hash_interno');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'HashUsuario',
                                   'hash_usuario');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'HashAgente',
                                   'hash_agente');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'DnUsuario',
                                   'dn_usuario');
    
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'SinValidado',
                                   'sin_validado');

    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'SiglaUsuario',
                                              'u.sigla',
                                              'usuario u');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                              'IdOrgaoUsuario',
                                              'u.id_orgao',
                                              'usuario u');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'SiglaOrgaoUsuario',
                                              'ou.sigla',
                                              'orgao ou');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'DescricaoOrgaoUsuario',
                                              'ou.descricao',
                                              'orgao ou');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'NomeUsuario',
                                              'u.nome',
                                              'usuario u');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'IdOrigemUsuario',
                                              'u.id_origem',
                                              'usuario u');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'SiglaSistema',
                                              'sigla',
                                              'sistema');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'PaginaInicialSistema',
                                              'pagina_inicial',
                                              'sistema');
                                              
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                              'IdOrgaoSistema',
                                              'id_orgao',
                                              'sistema');
																							
 	  $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'SiglaOrgaoSistema',
                                              's.sigla',
                                              'orgao s');

 	  $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'DescricaoOrgaoSistema',
                                              's.descricao',
                                              'orgao s');
 	  
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                              'IdOrgaoContexto',
                                              'id_orgao',
                                              'contexto');
																							
 	  $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'SiglaOrgaoContexto',
                                              'c.sigla',
                                              'orgao c');

 	  $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'DescricaoOrgaoContexto',
                                              'c.descricao',
                                              'orgao c');
 	  
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'SiglaUsuarioEmulador',
                                              'ue.sigla',
                                              'usuario ue');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'NomeUsuarioEmulador',
                                              'ue.nome',
                                              'usuario ue');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                              'IdOrgaoUsuarioEmulador',
                                              'ue.id_orgao',
                                              'usuario ue');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'SiglaOrgaoUsuarioEmulador',
                                              'oue.sigla',
                                              'orgao oue');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'DescricaoOrgaoUsuarioEmulador',
                                              'oue.descricao',
                                              'orgao oue');
    
    //Utilizado na carga do menu
    //$this->adicionarAtributo(InfraDTO::$PREFIXO_NUM,'IdUnidade'); 

    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'SenhaUsuario');

    //Utilizado em permissoes aplicadas nas subunidades
    $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR,'Hierarquia'); 
    
    
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'Link'); 

    
    //Utilizado quando efetuando login
    $this->adicionarAtributo(InfraDTO::$PREFIXO_OBJ,'InfraSessaoDTO'); 

    $this->configurarPK('IdLogin',InfraDTO::$TIPO_PK_INFORMADO);
    $this->configurarPK('IdUsuario',InfraDTO::$TIPO_PK_INFORMADO);
    $this->configurarPK('IdSistema',InfraDTO::$TIPO_PK_INFORMADO);
		
    $this->configurarFK('IdSistema', 'sistema', 'id_sistema');
    $this->configurarFK('IdUsuario', 'usuario u', 'u.id_usuario');
    $this->configurarFK('IdContexto', 'contexto', 'id_contexto', InfraDTO::$TIPO_FK_OPCIONAL);
		
		$this->configurarFK('IdOrgaoSistema', 'orgao s', 's.id_orgao');
		$this->configurarFK('IdOrgaoUsuario', 'orgao ou', 'ou.id_orgao');
		$this->configurarFK('IdOrgaoContexto', 'orgao c', 'c.id_orgao');
		
		$this->configurarFK('IdUsuarioEmulador', 'usuario ue', 'ue.id_usuario', InfraDTO::$TIPO_FK_OPCIONAL);
    $this->configurarFK('IdOrgaoUsuarioEmulador', 'orgao oue', 'oue.id_orgao');
		

  }
}