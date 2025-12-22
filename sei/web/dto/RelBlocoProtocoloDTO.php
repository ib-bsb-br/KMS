<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 02/10/2009 - criado por fbv@trf4.gov.br
*
* Versão do Gerador de Código: 1.29.1
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class RelBlocoProtocoloDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return 'rel_bloco_protocolo';
  }

  public function montar() {

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
                                   'IdProtocolo',
                                   'id_protocolo');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdBloco',
                                   'id_bloco');
                                   
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Anotacao',
                                   'anotacao');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'Sequencia',
                                   'sequencia');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DBL,
                                   'IdProtocoloProtocolo',
                                   'id_protocolo',
                                   'protocolo');
                                   
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                   'ProtocoloFormatadoProtocolo',
                                   'protocolo_formatado',
                                   'protocolo');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                   'StaProtocoloProtocolo',
                                   'sta_protocolo',
                                   'protocolo');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                   'IdUnidadeBloco',
                                   'id_unidade',
                                   'bloco');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                   'StaTipoBloco',
                                   'sta_tipo',
                                   'bloco');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                   'StaEstadoBloco',
                                   'sta_estado',
                                   'bloco');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DBL,
                                   'IdProcedimentoDocumento',
                                   'id_procedimento',
                                   'documento');
                                   
    /* $this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'SinAberto'); */
    $this->adicionarAtributo(InfraDTO::$PREFIXO_OBJ,'ProtocoloDTO');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR,'ObjAssinaturaDTO');
    
                                   
    $this->configurarPK('IdProtocolo',InfraDTO::$TIPO_PK_INFORMADO);
    $this->configurarPK('IdBloco',InfraDTO::$TIPO_PK_INFORMADO);
    
    $this->configurarFK('IdProtocolo', 'protocolo', 'id_protocolo');
		$this->configurarFK('IdBloco', 'bloco', 'id_bloco');
		$this->configurarFK('IdProtocoloProtocolo', 'documento', 'id_documento', InfraDTO::$TIPO_FK_OPCIONAL);
		
  }
}
?>