<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 16/08/2012 - criado por mkr@trf4.jus.br
*
* Versão do Gerador de Código: 1.33.0
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class ProtocoloModeloDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return 'protocolo_modelo';
  }

  public function montar() {

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
                                   'IdProtocoloModelo',
                                   'id_protocolo_modelo');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdGrupoProtocoloModelo',
                                   'id_grupo_protocolo_modelo');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdUnidade',
                                   'id_unidade');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdUsuario',
                                   'id_usuario');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
                                   'IdProtocolo',
                                   'id_protocolo');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Descricao',
                                   'descricao');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,
                                   'Geracao',
                                   'dth_geracao');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'SiglaUsuario',
                                              'sigla',
                                              'usuario');
                                          
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'NomeUsuario',
                                              'nome',
                                              'usuario');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'NomeGrupoProtocoloModelo',
                                              'nome',
                                              'grupo_protocolo_modelo');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'ProtocoloFormatado',
                                              'protocolo_formatado',
                                              'protocolo');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DBL,
                                              'IdProtocoloProtocolo',
                                              'id_protocolo',
                                              'protocolo');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                              'IdSerieDocumento',
                                              'id_serie',
                                              'documento');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'NomeSerie',
                                              'nome',
                                              'serie');
    
    
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'StaTipoFiltro');
    
    $this->configurarPK('IdProtocoloModelo',InfraDTO::$TIPO_PK_NATIVA);

    $this->configurarFK('IdGrupoProtocoloModelo', 'grupo_protocolo_modelo', 'id_grupo_protocolo_modelo',InfraDTO::$TIPO_FK_OPCIONAL);
    $this->configurarFK('IdUsuario','usuario','id_usuario');
    $this->configurarFK('IdProtocolo', 'protocolo', 'id_protocolo');
    $this->configurarFK('IdProtocoloProtocolo', 'documento', 'id_documento');
    $this->configurarFK('IdSerieDocumento', 'serie', 'id_serie');
  }
}
?>