<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 05/06/2008 - criado por fbv
*
* Versão do Gerador de Código: 1.17.0
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class AtividadeDTO extends InfraDTO {

  private $numTipoFkAndamentoMarcador;

  public function __construct(){
    $this->numTipoFkAndamentoMarcador = InfraDTO::$TIPO_FK_OPCIONAL;
    parent::__construct();
  }

  public function getStrNomeTabela() {
  	 return 'atividade';
  }

  public function montar() {

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdAtividade',
                                   'id_atividade');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
                                   'IdProtocolo',
                                   'id_protocolo');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdUnidade',
                                   'id_unidade');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdUnidadeOrigem',
                                   'id_unidade_origem');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdUsuario',
                                   'id_usuario');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdUsuarioOrigem',
                                   'id_usuario_origem');
    
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,
                                   'Abertura',
                                   'dth_abertura');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,
                                   'Conclusao',
                                   'dth_conclusao');
                                   
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdTarefa',
                                   'id_tarefa');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdUsuarioAtribuicao',
                                   'id_usuario_atribuicao');
                                   
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdUsuarioConclusao',
                                   'id_usuario_conclusao');
                                   
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdUsuarioVisualizacao',
                                   'id_usuario_visualizacao');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'TipoVisualizacao',
                                   'tipo_visualizacao');
                                   
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTA,
                                   'Prazo',
                                   'dta_prazo');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'SinInicial',
                                   'sin_inicial');
                                   
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'SiglaUnidadeOrigem',
                                              'uo.sigla',
                                              'unidade uo');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'DescricaoUnidadeOrigem',
                                              'uo.descricao',
                                              'unidade uo');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                              'IdOrgaoUnidadeOrigem',
                                              'uo.id_orgao',
                                              'unidade uo');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'SiglaUnidade',
                                              'u.sigla',
                                              'unidade u');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'DescricaoUnidade',
                                              'u.descricao',
                                              'unidade u');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                              'IdOrgaoUnidade',
                                              'u.id_orgao',
                                              'unidade u');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'SiglaUsuario',
                                              'ua.sigla',
                                              'usuario ua');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'NomeUsuario',
                                              'ua.nome',
                                              'usuario ua');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'SiglaUsuarioAtribuicao',
                                              'uat.sigla',
                                              'usuario uat');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'NomeUsuarioAtribuicao',
                                              'uat.nome',
                                              'usuario uat');
                                              
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'SiglaUsuarioOrigem',
                                              'ug.sigla',
                                              'usuario ug');
                                              
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'NomeUsuarioOrigem',
                                              'ug.nome',
                                              'usuario ug');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'SiglaUsuarioConclusao',
                                              'uc.sigla',
                                              'usuario uc');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'NomeUsuarioConclusao',
                                              'uc.nome',
                                              'usuario uc');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'IdProtocoloProtocolo',
                                              'id_protocolo',
                                              'protocolo');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'StaProtocoloProtocolo',
                                              'sta_protocolo',
                                              'protocolo');
                                              
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'StaEstadoProtocolo',
                                              'sta_estado',
                                              'protocolo');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'StaNivelAcessoGlobalProtocolo',
                                              'sta_nivel_acesso_global',
                                              'protocolo');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'ProtocoloFormatadoProtocolo',
                                              'protocolo_formatado',
                                              'protocolo');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'NomeTarefa',
                                              'nome',
                                              'tarefa');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'IdTarefaModuloTarefa',
                                              'id_tarefa_modulo',
                                              'tarefa');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 
                                              'SinHistoricoCompletoTarefa', 
                                              'sin_historico_completo', 
                                              'tarefa');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 
                                              'SinHistoricoResumidoTarefa', 
                                              'sin_historico_resumido', 
                                              'tarefa');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DBL,
                                              'IdProcedimentoProtocolo',
                                              'id_procedimento',
                                              'procedimento');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                              'IdMarcador',
                                              'id_marcador',
                                              'andamento_marcador');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'SinUltimoAndamentoMarcador',
                                              'sin_ultimo',
                                              'andamento_marcador');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                              'IdUnidadeMarcador',
                                              'id_unidade',
                                              'marcador');

    $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'ObjAtributoAndamentoDTO');
    
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'SinUltimaUnidadeHistorico');
                                              
    
    $this->configurarPK('IdAtividade', InfraDTO::$TIPO_PK_NATIVA);
    
    $this->configurarFK('IdProtocolo', 'protocolo', 'id_protocolo');
    $this->configurarFK('IdUnidade', 'unidade u', 'u.id_unidade');
    $this->configurarFK('IdUnidadeOrigem', 'unidade uo', 'uo.id_unidade');
    $this->configurarFK('IdUsuario', 'usuario ua', 'ua.id_usuario');
    $this->configurarFK('IdUsuarioOrigem', 'usuario ug', 'ug.id_usuario');
    $this->configurarFK('IdProtocoloProtocolo', 'procedimento', 'id_procedimento');
    $this->configurarFK('IdProcedimentoProtocolo', 'andamento_marcador', 'id_procedimento', $this->getNumTipoFkAndamentoMarcador());
    $this->configurarFK('IdUsuarioAtribuicao', 'usuario uat', 'uat.id_usuario', InfraDTO::$TIPO_FK_OPCIONAL);
    $this->configurarFK('IdUsuarioConclusao', 'usuario uc', 'uc.id_usuario', InfraDTO::$TIPO_FK_OPCIONAL);    
    $this->configurarFK('IdTarefa', 'tarefa', 'id_tarefa');
    $this->configurarFK('IdMarcador','marcador','id_marcador');
    
  }

  public function getNumTipoFkAndamentoMarcador(){
    return $this->numTipoFkAndamentoMarcador;
  }

  public function setNumTipoFkAndamentoMarcador($numTipoFkAndamentoMarcador){
    $this->numTipoFkAndamentoMarcador = $numTipoFkAndamentoMarcador;
  }

}
?>