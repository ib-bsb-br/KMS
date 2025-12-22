<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 26/08/2010 - criado por jonatas_db
*
* Versão do Gerador de Código: 1.30.0
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class RetornoProgramadoDTO extends InfraDTO {

  private $numFiltroFkAtividadeRetorno = null;

  public function __construct(){
    $this->numFiltroFkAtividadeRetorno = InfraDTO::$FILTRO_FK_ON;
    parent::__construct();
  }

  public function getStrNomeTabela() {
  	 return 'retorno_programado';
  }

  public function montar() {

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdRetornoProgramado',
                                   'id_retorno_programado');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdUnidade',
                                   'id_unidade');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdAtividadeEnvio',
                                   'id_atividade_envio');
                                   
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdAtividadeRetorno',
                                   'id_atividade_retorno');

		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdUsuario',
                                   'id_usuario');
                                   
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTA,
                                   'Programada',
                                   'dta_programada');
                                   
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTA,
                                   'Inicial',
                                   'dta_programada');
                                   
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTA,
                                   'Final',
                                   'dta_programada');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,
                                   'Alteracao',
                                   'dth_alteracao');
                                   
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                   'SiglaUnidade',
                                   'u.sigla',
                                   'unidade u');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                   'SiglaUsuario',
                                   'sigla',
                                   'usuario');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                   'IdUnidadeAtividadeEnvio',
                                   'e.id_unidade',
                                   'atividade e');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                   'IdUnidadeOrigemAtividadeEnvio',
                                   'e.id_unidade_origem',
                                   'atividade e');
                                   
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DTH,
                                   'AberturaAtividadeEnvio',
                                   'e.dth_abertura',
                                   'atividade e');                                   

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DBL,
                                   'IdProtocoloAtividadeEnvio',
                                   'e.id_protocolo',
                                   'atividade e');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DTH,
                                   'AberturaAtividadeRetorno',
                                   'r.dth_abertura',
                                   'atividade r');
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,'IdUnidadeAtividadeRetorno','r.id_unidade','atividade r');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                   'SiglaUnidadeOrigemAtividadeEnvio',
                                   'uoe.sigla',
                                   'unidade uoe');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                   'DescricaoUnidadeOrigemAtividadeEnvio',
                                   'uoe.descricao',
                                   'unidade uoe');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                   'SiglaUnidadeAtividadeEnvio',
                                   'ue.sigla',
                                   'unidade ue');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                   'DescricaoUnidadeAtividadeEnvio',
                                   'ue.descricao',
                                   'unidade ue');
    
    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                   'ProtocoloFormatadoAtividadeEnvio',
                                   'protocolo_formatado',
                                   'protocolo');
    
    
    $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM,'DiasPrazo');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_DTA,'DataInicial');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_DTA,'DataFinal');
    
    $this->adicionarAtributo(InfraDTO::$PREFIXO_OBJ,'ProtocoloDTO');
                                   
    $this->configurarPK('IdRetornoProgramado',InfraDTO::$TIPO_PK_NATIVA );
    
		$this->configurarFK('IdUnidade','unidade u','u.id_unidade');    
		$this->configurarFK('IdUsuario','usuario','id_usuario');    
		$this->configurarFK('IdAtividadeEnvio','atividade e','e.id_atividade');
		$this->configurarFK('IdAtividadeRetorno','atividade r','r.id_atividade', InfraDTO::$TIPO_FK_OPCIONAL, $this->getNumFiltroFkAtividadeRetorno());
		$this->configurarFK('IdUnidadeOrigemAtividadeEnvio','unidade uoe','uoe.id_unidade');
		$this->configurarFK('IdUnidadeAtividadeEnvio','unidade ue','ue.id_unidade');
		$this->configurarFK('IdProtocoloAtividadeEnvio','protocolo','id_protocolo');
  }

  public function setNumFiltroFkAtividadeRetorno($numFiltroFkAtividadeRetorno){
    $this->numFiltroFkAtividadeRetorno = $numFiltroFkAtividadeRetorno;
  }

  public function getNumFiltroFkAtividadeRetorno(){
    return $this->numFiltroFkAtividadeRetorno;
  }
}
?>