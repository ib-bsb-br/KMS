<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 20/08/2009 - criado por mga
*
* Versão do Gerador de Código: 1.28.0
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../Sip.php';

class RelGrupoRedeUnidadeDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return 'rel_grupo_rede_unidade';
  }

  public function montar() {

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdGrupoRede',
                                   'id_grupo_rede');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdUnidade',
                                   'id_unidade');
                                   
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                             'IdOrgaoGrupoRede',
                                             'id_orgao',
                                             'grupo_rede');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                             'OuLdapGrupoRede',
                                             'ou_ldap',
                                             'grupo_rede');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                             'SiglaOrgaoGrupoRede',
                                             'sigla',
                                             'orgao');
                                             
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                             'IdOrgaoUnidade',
                                             'id_orgao',
                                             'unidade');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                             'SiglaUnidade',
                                             'sigla',
                                             'unidade');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                             'DescricaoUnidade',
                                             'descricao',
                                             'unidade');
    
    $this->configurarPK('IdGrupoRede',InfraDTO::$TIPO_PK_INFORMADO);
    $this->configurarPK('IdUnidade',InfraDTO::$TIPO_PK_INFORMADO);

    $this->configurarFK('IdGrupoRede', 'grupo_rede', 'id_grupo_rede');
    $this->configurarFK('IdOrgaoGrupoRede','orgao','id_orgao');
    $this->configurarFK('IdUnidade','unidade','id_unidade');
  }
}
?>