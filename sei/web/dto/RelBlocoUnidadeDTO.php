<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 05/10/2009 - criado por fbv@trf4.gov.br
*
* Versão do Gerador de Código: 1.29.1
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class RelBlocoUnidadeDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return 'rel_bloco_unidade';
  }

  public function montar() {

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdUnidade',
                                   'id_unidade');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdBloco',
                                   'id_bloco');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'SinRetornado',
                                   'sin_retornado');
    
		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
					                                   'SiglaUnidade',
					                                   'u1.sigla',
					                                   'unidade u1');

		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
					                                   'DescricaoUnidade',
					                                   'u1.descricao',
					                                   'unidade u1');
					                                   
		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
					                                   'StaEstadoBloco',
					                                   'b.sta_estado',
					                                   'bloco b');

		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
					                                   'StaTipoBloco',
					                                   'b.sta_tipo',
					                                   'bloco b');
					                                   
		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
					                                   'IdUnidadeBloco',
					                                   'b.id_unidade',
					                                   'bloco b');
					                                   
		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
					                                   'SiglaUnidadeBloco',
					                                   'u2.sigla',
					                                   'unidade u2');

		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
					                                   'DescricaoUnidadeBloco',
					                                   'u2.descricao',
					                                   'unidade u2');
					                                   
    $this->configurarPK('IdUnidade',InfraDTO::$TIPO_PK_INFORMADO);
    $this->configurarPK('IdBloco',InfraDTO::$TIPO_PK_INFORMADO);

    $this->configurarFK('IdBloco', 'bloco b', 'b.id_bloco');
    $this->configurarFK('IdUnidade','unidade u1','u1.id_unidade');
    $this->configurarFK('IdUnidadeBloco','unidade u2','u2.id_unidade');
    
  }
}
?>