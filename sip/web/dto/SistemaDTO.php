<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 21/12/2006 - criado por mga
*
*
*/

require_once dirname(__FILE__).'/../Sip.php';

class SistemaDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return "sistema";
  }

  public function montar() {

  	 $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdSistema',
                                   'id_sistema');

  	 $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdOrgao',
                                   'id_orgao');

  	 $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdHierarquia',
                                   'id_hierarquia');

  	 $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Sigla',
                                   'sigla');

  	 $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Descricao',
                                   'descricao');

  	 $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'PaginaInicial',
                                   'pagina_inicial');
                                   
  	 $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'WebService',
                                   'web_service');

  	 $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Logo',
                                   'logo');
  	 
  	 $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'SinAtivo',
                                   'sin_ativo');

  	 $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'SiglaOrgao',
                                              'sigla',
                                              'orgao');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'DescricaoOrgao',
                                              'descricao',
                                              'orgao');

  	 $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'NomeHierarquia',
                                              'nome',
                                              'hierarquia');

		 //atributo que identifica o tipo de acesso do usuario atual no sistema
		 //preenchido pelo método listar acessados de SistemaRN
		 // 1 = administrador
		 // 2 = coordenador de pelo menos um perfil do sistema
		 // 3 = coordenador de unidade para o sistema em pelo menos uma unidade
  	 $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'TipoAcesso');
  	 
  	 $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'IdUnidade');
		 
  	 $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'NomeArquivo');
  	 
  	 $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'NomeOperacaoReplicacao');
  	 
     $this->configurarPK('IdSistema',InfraDTO::$TIPO_PK_SEQUENCIAL);

     $this->configurarFK('IdOrgao', 'orgao', 'id_orgao');
     $this->configurarFK('IdHierarquia', 'hierarquia', 'id_hierarquia');
     $this->configurarExclusaoLogica('SinAtivo', 'N');

  }
}
?>