<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 29/03/2010 - criado por mga
*
* Versão do Gerador de Código: 1.29.1
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class NovidadeINT extends InfraINT {

  public static function montarSelectTitulo($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdUsuario=''){
    $objNovidadeDTO = new NovidadeDTO();
    $objNovidadeDTO->retNumIdNovidade();
    $objNovidadeDTO->retStrTitulo();

    if ($numIdUsuario!==''){
      $objNovidadeDTO->setNumIdUsuario($numIdUsuario);
    }

    $objNovidadeDTO->setOrdStrTitulo(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objNovidadeRN = new NovidadeRN();
    $arrObjNovidadeDTO = $objNovidadeRN->listar($objNovidadeDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjNovidadeDTO, 'IdNovidade', 'Titulo');
  }
}
?>