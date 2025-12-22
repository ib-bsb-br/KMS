<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 25/11/2008 - criado por mga
 *
 * Versão do Gerador de Código: 1.25.0
 *
 * Versão no CVS: $Id$
 */

require_once dirname(__FILE__).'/../SEI.php';

class PublicacaoINT extends InfraINT {
   
  public static function montarSelectStaMotivoRI1061($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $dblIdProtocolo){

    $objPublicacaoRN = new PublicacaoRN();
    $arrObjVeiculoPublicacaoDTO = $objPublicacaoRN->listarValoresMotivoRN1056();

    $objProtocoloDTO = new ProtocoloDTO();
    $objProtocoloDTO->retDblIdProtocoloAgrupador();
    $objProtocoloDTO->setDblIdProtocolo($dblIdProtocolo);

    $objProtocoloRN = new ProtocoloRN();
    $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

    if ($objProtocoloDTO->getDblIdProtocoloAgrupador()!=$dblIdProtocolo){
      $arrTemp = array();
      foreach($arrObjVeiculoPublicacaoDTO as $objVeiculoPublicacaoDTO){
        if ($objVeiculoPublicacaoDTO->getStrStaMotivo()!=PublicacaoRN::$TM_PUBLICACAO || $objVeiculoPublicacaoDTO->getStrStaMotivo()==$strValorItemSelecionado){
          $arrTemp[] = $objVeiculoPublicacaoDTO;
        }
      }
      $arrObjVeiculoPublicacaoDTO = $arrTemp;
    }else{
      $arrTemp = array();
      foreach($arrObjVeiculoPublicacaoDTO as $objVeiculoPublicacaoDTO){
        if ($objVeiculoPublicacaoDTO->getStrStaMotivo()==PublicacaoRN::$TM_PUBLICACAO){
          $arrTemp[] = $objVeiculoPublicacaoDTO;
          break;
        }
      }
      $arrObjVeiculoPublicacaoDTO = $arrTemp;
      $strValorItemSelecionado = PublicacaoRN::$TM_PUBLICACAO;
    }

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjVeiculoPublicacaoDTO, 'StaMotivo', 'Descricao');
  }
   
  public static function sugerirDataDisponibilizacaoRI1054($idOrgao,$idVeiculoPublicacao){
    $objPublicacaoDTO = new PublicacaoDTO();
    $objPublicacaoDTO->setNumIdOrgaoUnidadeResponsavelDocumento($idOrgao);
    $objPublicacaoDTO->setNumIdVeiculoPublicacao($idVeiculoPublicacao);    

    $objPublicacaoRN = new PublicacaoRN();

    return $objPublicacaoRN->obterProximaDataRN1055($objPublicacaoDTO);
  }

  public static function obterTextoInformativoPublicacao(DocumentoDTO $parObjDocumentoDTO) {
    $strResultado = '';
    if ($parObjDocumentoDTO->isSetObjPublicacaoDTO()){
      $objPublicacaoDTO = $parObjDocumentoDTO->getObjPublicacaoDTO();
      if ($objPublicacaoDTO != null) {
        if ($objPublicacaoDTO->getStrStaEstado() == PublicacaoRN::$TE_PUBLICADO) {
          if ($objPublicacaoDTO->getStrStaTipoVeiculoPublicacao() == VeiculoPublicacaoRN::$TV_INTERNO) {
            $strResultado .= $objPublicacaoDTO->getStrNomeVeiculoPublicacao() . ' em ' . $objPublicacaoDTO->getDtaDisponibilizacao()."\n";
          } else {
            $strResultado .= $objPublicacaoDTO->getStrNomeVeiculoPublicacao() . ' nº ' . $objPublicacaoDTO->getNumNumero() . "\n" .
            'Disponibilização: ' . $objPublicacaoDTO->getDtaDisponibilizacao() . "\n" .
            'Publicação: ' . $objPublicacaoDTO->getDtaPublicacao() . "\n";
          }
        }

        if ($objPublicacaoDTO->getNumIdVeiculoIO() != null) {
          $strResultado .=  $objPublicacaoDTO->getStrSiglaVeiculoImprensaNacional(). ' de '.$objPublicacaoDTO->getDtaPublicacaoIO()
              .', Seção '.$objPublicacaoDTO->getStrNomeSecaoImprensaNacional()
              .', Página '.$objPublicacaoDTO->getStrPaginaIO();
        }

      }
    }
    return $strResultado;
  }

  public static function obterTextoInformativoPublicacao2(DocumentoDTO $parObjDocumentoDTO) {
    $strResultado = '';

    if ($parObjDocumentoDTO->isSetObjPublicacaoDTO() && $parObjDocumentoDTO->getObjPublicacaoDTO() != null){
      if ($parObjDocumentoDTO->getObjPublicacaoDTO()->getStrStaEstado() == PublicacaoRN::$TE_PUBLICADO){
        $objVeiculoPublicacaoRN = new VeiculoPublicacaoRN();
        $objVeiculoPublicacaoDTO = new VeiculoPublicacaoDTO();
        $objVeiculoPublicacaoDTO->setNumIdVeiculoPublicacao($parObjDocumentoDTO->getObjPublicacaoDTO()->getNumIdVeiculoPublicacao());
        $objVeiculoPublicacaoDTO->retStrNome();
        $objVeiculoPublicacaoDTO->retStrStaTipo();
        $objVeiculoPublicacaoDTO = $objVeiculoPublicacaoRN->consultar($objVeiculoPublicacaoDTO);

        if ($objVeiculoPublicacaoDTO->getStrStaTipo() == VeiculoPublicacaoRN::$TV_INTERNO){
          $strResultado .= $objVeiculoPublicacaoDTO->getStrNome() .' em '.$parObjDocumentoDTO->getObjPublicacaoDTO()->getDtaDisponibilizacao()."\n";
        }else{
          $strResultado .= $objVeiculoPublicacaoDTO->getStrNome() .' nº '.$parObjDocumentoDTO->getObjPublicacaoDTO()->getNumNumero()."\n".
              'Disponibilização: ' .$parObjDocumentoDTO->getObjPublicacaoDTO()->getDtaDisponibilizacao()."\n".
              'Publicação: ' .$parObjDocumentoDTO->getObjPublicacaoDTO()->getDtaPublicacao()."\n";
        }

        # Verifica se foi publicado no DOU para incluir na tarja de publicação
        if ($parObjDocumentoDTO->getObjPublicacaoDTO()->getNumIdVeiculoIO()!=NULL) {
          $strResultado .= 'DOU de '.$parObjDocumentoDTO->getObjPublicacaoDTO()->getDtaPublicacaoIO()
              .', Seção '.$parObjDocumentoDTO->getObjPublicacaoDTO()->getNumIdSecaoIO()
              .', Página '.$parObjDocumentoDTO->getObjPublicacaoDTO()->getStrPaginaIO();
        }

        return $strResultado;

      }
    }
  }
  public static function montarDadosImprensaNacional($strSiglaVeiculoIO, $strDescricaoVeiculoIO, $dtaPublicacaoIO, $strNomeSecaoIO, $strPaginaIO){
    $strResultado = '';
    
    if (!InfraString::isBolVazia($strSiglaVeiculoIO)) {
      $strResultado .= '<a alt="'.$strDescricaoVeiculoIO.'" title="'.PaginaSEI::tratarHTML($strDescricaoVeiculoIO).'" class="ancoraSigla">'.PaginaSEI::tratarHTML($strSiglaVeiculoIO).'</a>';
    
      if (!InfraString::isBolVazia($dtaPublicacaoIO)) {
        $strResultado .= " de ".$dtaPublicacaoIO;
      }
    
      if (!InfraString::isBolVazia($strNomeSecaoIO)) {
        $strResultado .= ", seção ".PaginaSEI::tratarHTML($strNomeSecaoIO);
      }
    
      if (!InfraString::isBolVazia($strPaginaIO)) {
        $strResultado .= ", página ".$strPaginaIO;
      }
    }
    
    return $strResultado;
  }
}
?>