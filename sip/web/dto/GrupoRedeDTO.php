<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 19/08/2009 - criado por mga
*
* Versão do Gerador de Código: 1.28.0
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../Sip.php';

class GrupoRedeDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return 'grupo_rede';
  }

  public function montar() {

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdGrupoRede',
                                   'id_grupo_rede');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdOrgao',
                                   'id_orgao');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'OuLdap',
                                   'ou_ldap');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Descricao',
                                   'descricao');
                                   
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'SinExcecao',
                                   'sin_excecao');
                                   
                                   
 	 $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                             'SiglaOrgao',
                                             'sigla',
                                             'orgao');
                                             

   $this->configurarPK('IdGrupoRede',InfraDTO::$TIPO_PK_SEQUENCIAL);
   
   $this->configurarFK('IdOrgao','orgao','id_orgao');

  }
}
?>