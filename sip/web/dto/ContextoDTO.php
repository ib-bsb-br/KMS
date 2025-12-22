<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 29/11/2006 - criado por mga
*
*
*/

require_once dirname(__FILE__).'/../Sip.php';

class ContextoDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return "contexto";
  }

  public function montar() {

  	 $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdContexto',
                                   'id_contexto');

  	 $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdOrgao',
                                   'id_orgao');

  	 $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Nome',
                                   'nome');
                                   
  	 $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Descricao',
                                   'descricao');

  	 $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'BaseDnLdap',
                                   'base_dn_ldap');

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
  	 
    $this->configurarPK('IdContexto',InfraDTO::$TIPO_PK_SEQUENCIAL);

    $this->configurarFK('IdOrgao', 'orgao', 'id_orgao');
    $this->configurarExclusaoLogica('SinAtivo', 'N');

  }
}
?>