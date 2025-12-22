<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 19/08/2009 - criado por mga
*
* Versão do Gerador de Código: 1.28.0
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../Sip.php';

class GrupoRedeINT extends InfraINT {

  public static function montarSelectOuLdap($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdOrgao=''){
    $objGrupoRedeDTO = new GrupoRedeDTO();
    $objGrupoRedeDTO->retNumIdGrupoRede();
    $objGrupoRedeDTO->retStrOuLdap();

    if ($numIdOrgao!==''){
      $objGrupoRedeDTO->setNumIdOrgao($numIdOrgao);
    }

    $objGrupoRedeDTO->setOrdStrOuLdap(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objGrupoRedeRN = new GrupoRedeRN();
    $arrObjGrupoRedeDTO = $objGrupoRedeRN->listar($objGrupoRedeDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjGrupoRedeDTO, IdGrupoRede, 'OuLdap');
  }
  
  public static function montarSelectOuLdapNaoExcecao($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdOrgao){

    /*
    $objRelGrupoRedeUnidadeDTO = new RelGrupoRedeUnidadeDTO();
    $objRelGrupoRedeUnidadeDTO->retNumIdGrupoRede();
    $objRelGrupoRedeUnidadeDTO->setNumIdOrgaoGrupoRede($numIdOrgao);
    
    $objRelGrupoRedeUnidadeRN = new RelGrupoRedeUnidadeRN();
    $arrObjRelGrupoRedeUnidadeDTO = $objRelGrupoRedeUnidadeRN->listar($objRelGrupoRedeUnidadeDTO);
    */
    
    $objGrupoRedeDTO = new GrupoRedeDTO();
    $objGrupoRedeDTO->retNumIdGrupoRede();
    $objGrupoRedeDTO->retStrOuLdap();
    $objGrupoRedeDTO->setNumIdOrgao($numIdOrgao);
    
    /*
    if (count($arrObjRelGrupoRedeUnidadeDTO)>0){
      $objGrupoRedeDTO->setNumIdGrupoRede(InfraArray::converterArrInfraDTO($arrObjRelGrupoRedeUnidadeDTO,'IdGrupoRede'),InfraDTO::$OPER_NOT_IN);
    }
    */

    $objGrupoRedeDTO->setStrSinExcecao('N');
    
    $objGrupoRedeDTO->setOrdStrOuLdap(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objGrupoRedeRN = new GrupoRedeRN();
    $arrObjGrupoRedeDTO = $objGrupoRedeRN->listar($objGrupoRedeDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjGrupoRedeDTO, IdGrupoRede, 'OuLdap');
  }
}
?>