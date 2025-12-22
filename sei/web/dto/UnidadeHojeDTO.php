<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 17/11/2014 - criado por mga
 *
 */

require_once dirname(__FILE__).'/../SEI.php';

class UnidadeHojeDTO extends InfraDTO {

  private $numTipoFkProcedimento = null;
  private $numTipoFkDocumento = null;

  public function __construct(){
    $this->numTipoFkProcedimento = InfraDTO::$TIPO_FK_OPCIONAL;
    $this->numTipoFkDocumento = InfraDTO::$TIPO_FK_OPCIONAL;
    parent::__construct();
  }

  public function getStrNomeTabela() {
		return 'unidade_hoje';
	}

	public function montar() {

      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
          'IdUnidadeHoje',
          'id_unidade_hoje');

      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
          'IdProcedimento',
          'id_procedimento');

      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
          'IdDocumento',
          'id_documento');

      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
          'IdBloco',
          'id_bloco');

      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
          'IdUsuarioAtribuicao',
          'id_usuario_atribuicao');

      $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,
          'Snapshot',
          'dth_snapshot');

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DBL,
          'IdProcedimentoProcedimento',
          'id_procedimento',
          'procedimento');

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
          'IdTipoProcedimentoProcedimento',
          'id_tipo_procedimento',
          'procedimento');

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
          'ProtocoloFormatadoProcedimento',
          'p.protocolo_formatado',
          'protocolo p');

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
          'NomeTipoProcedimento',
          'nome',
          'tipo_procedimento');

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
          'IdSerieDocumento',
          'id_serie',
          'documento');

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
          'NomeSerie',
          's.nome',
          'serie s');

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
          'IdDocumentoDocumento',
          'id_documento',
          'documento');

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
          'ProtocoloFormatadoDocumento',
          'd.protocolo_formatado',
          'protocolo d');


      $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'ProcessosTipoQtde');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'ProcessosUsuarioQtde');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'DocumentosAssinaturas');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'BlocosAssinatura');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'RetornosProgramados');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'UltimasAcoes');

      $this->adicionarAtributo(InfraDTO::$PREFIXO_DBL, 'IdUnidadeHojeProcessosTipoQtde');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_DBL, 'IdUnidadeHojeProcessosUsuarioQtde');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_DBL, 'IdUnidadeHojeDocsUnidadeAssinados');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_DBL, 'IdUnidadeHojeDocsUnidadeNaoAssinados');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_DBL, 'IdUnidadeHojeDocsBlocoAssinados');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_DBL, 'IdUnidadeHojeDocsBlocoNaoAssinados');

      $this->configurarFK('IdProcedimento', 'procedimento', 'id_procedimento',$this->getNumTipoFkProcedimento());
      $this->configurarFK('IdDocumento', 'documento', 'id_documento',$this->getNumTipoFkDocumento());
      $this->configurarFK('IdDocumentoDocumento', 'protocolo d', 'd.id_protocolo');
      $this->configurarFK('IdSerieDocumento', 'serie s', 's.id_serie');
      $this->configurarFK('IdProcedimentoProcedimento', 'protocolo p', 'p.id_protocolo');
      $this->configurarFK('IdTipoProcedimentoProcedimento', 'tipo_procedimento', 'id_tipo_procedimento');
	}

  public function getNumTipoFkProcedimento(){
    return $this->numTipoFkProcedimento;
  }

  public function setNumTipoFkProcedimento($numTipoFkProcedimento){
    $this->numTipoFkProcedimento = $numTipoFkProcedimento;
  }

  public function getNumTipoFkDocumento(){
    return $this->numTipoFkDocumento;
  }

  public function setNumTipoFkDocumento($numTipoFkDocumento){
    $this->numTipoFkDocumento = $numTipoFkDocumento;
  }
}
?>