<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 18/05/2011 - criado por mga
*
* Versão do Gerador de Código: 1.31.0
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class PesquisaProtocoloSolrDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return null;
  }
  
  public function montar() {
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'PalavrasChave');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'SinProcessosTramitacao');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'SinDocumentosGerados');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'SinDocumentosRecebidos');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR,'NumIdOrgao');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM,'IdContato');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'SinInteressado');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'SinRemetente');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'SinDestinatario');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM,'IdAssinante');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'Descricao');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'Observacao');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM,'IdAssunto');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM,'IdUnidadeGeradora');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'ProtocoloPesquisa');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM,'IdTipoProcedimento');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM,'IdSerie');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'Numero');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'StaTipoData');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_DTA,'Inicio');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_DTA,'Fim');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM,'IdUsuarioGerador1');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM,'IdUsuarioGerador2');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM,'IdUsuarioGerador3');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM,'InicioPaginacao');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_DBL,'IdProcedimento');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_BOL,'Arvore');
  }
}
?>