<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 03/12/2009 - criado por mga
*
* Versão do Gerador de Código: 1.29.1
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class ContextoINT extends InfraINT {

  public static function montarSelectNome($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdOrgao=''){
    $objContextoDTO = new ContextoDTO();
    $objContextoDTO->retNumIdContexto();
    $objContextoDTO->retStrNome();

    if ($numIdOrgao!==''){
      $objContextoDTO->setNumIdOrgao($numIdOrgao);
    }

    if ($strValorItemSelecionado!=null){
      $objContextoDTO->setBolExclusaoLogica(false);
      $objContextoDTO->adicionarCriterio(array('SinAtivo','IdContexto'),array(InfraDTO::$OPER_IGUAL,InfraDTO::$OPER_IGUAL),array('S',$strValorItemSelecionado),InfraDTO::$OPER_LOGICO_OR);
    }

    $objContextoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objContextoRN = new ContextoRN();
    $arrObjContextoDTO = $objContextoRN->listar($objContextoDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjContextoDTO, 'IdContexto', 'Nome');
  }
}
?>