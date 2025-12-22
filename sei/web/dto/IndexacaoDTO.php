<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 13/07/2010 - criado por jonatas_db
*
* Versão do Gerador de Código: 1.10.1
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class IndexacaoDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return null;
  }

  public function montar() {
    $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR,'IdProtocolos');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'ProtocoloFormatadoPesquisa');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR,'ObjPublicacaoDTO');
  	$this->adicionarAtributo(InfraDTO::$PREFIXO_ARR,'ObjBaseConhecimentoDTO');
  	$this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'StaOperacao');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_DTA,'Indexacao');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_DTH,'Inicio');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_DTH,'Fim');

  }
}
?>