<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 27/11/2006 - criado por mga
*
*
*/

require_once dirname(__FILE__).'/../Sip.php';

class ContextoINT extends InfraINT {

  public static function montarSelectNome($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdOrgao=''){
    $objContextoDTO = new ContextoDTO();
    $objContextoDTO->retNumIdContexto();
    $objContextoDTO->retStrNome();

    if ($numIdOrgao!==''){
			$objContextoDTO->setNumIdOrgao($numIdOrgao);
		}		

    $objContextoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objContextoRN = new ContextoRN();
    $arrObjContextoDTO = $objContextoRN->listar($objContextoDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado,$arrObjContextoDTO, 'IdContexto', 'Nome');
  }
}
?>