<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 27/11/2006 - criado por mga
*
*
*/

require_once dirname(__FILE__).'/../Sip.php';

class OrgaoINT extends InfraINT {

  //Utilizado na tela importacao de sistemas
  public static function montarSelectSiglaSigla($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
    $objOrgaoDTO = new OrgaoDTO();
    $objOrgaoDTO->retStrSigla();
    $objOrgaoDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);
    $objOrgaoRN = new OrgaoRN();
    $arrObjOrgaoDTO = $objOrgaoRN->listar($objOrgaoDTO);
    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado,$arrObjOrgaoDTO, 'Sigla', 'Sigla');
  }


  //Utilizado na tela de login
  public static function montarSelectDescricao($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
    $objOrgaoDTO = new OrgaoDTO();
    $objOrgaoDTO->retNumIdOrgao();
    $objOrgaoDTO->retStrDescricao();
    $objOrgaoDTO->setOrdStrDescricao(InfraDTO::$TIPO_ORDENACAO_ASC);
    $objOrgaoRN = new OrgaoRN();
    $arrObjOrgaoDTO = $objOrgaoRN->listar($objOrgaoDTO);
    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado,$arrObjOrgaoDTO, 'IdOrgao', 'Descricao');
  }
  
  public static function montarSelectSiglaTodos($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
    $objOrgaoDTO = new OrgaoDTO();
    $objOrgaoDTO->retNumIdOrgao();
    $objOrgaoDTO->retStrSigla();
    $objOrgaoDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);
    $objOrgaoRN = new OrgaoRN();
    $arrObjOrgaoDTO = $objOrgaoRN->listar($objOrgaoDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado,$arrObjOrgaoDTO, 'IdOrgao', 'Sigla');
  }

  public static function montarSelectLogin($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
    $objOrgaoDTO = new OrgaoDTO();
    $objOrgaoDTO->retNumIdOrgao();
    $objOrgaoDTO->retStrSigla();
    $objOrgaoDTO->setOrdNumOrdem(InfraDTO::$TIPO_ORDENACAO_ASC);
    $objOrgaoDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objOrgaoRN = new OrgaoRN();
    $arrObjOrgaoDTO = $objOrgaoRN->listar($objOrgaoDTO);
    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado,$arrObjOrgaoDTO, 'IdOrgao', 'Sigla');
  }

  public static function montarSelectSigla($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
    $objOrgaoDTO = new OrgaoDTO();
    $objOrgaoDTO->retNumIdOrgao();
    $objOrgaoDTO->retStrSigla();
    $objOrgaoDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

    //$objOrgaoDTO->setNumIdOrgao(SessaoSip::getInstance()->getNumIdOrgaoSistema());
    
    $objOrgaoRN = new OrgaoRN();
    $arrObjOrgaoDTO = $objOrgaoRN->listar($objOrgaoDTO);
    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado,$arrObjOrgaoDTO, 'IdOrgao', 'Sigla');
  }

  public static function montarSelectSiglaAdministrados($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
    
    $objOrgaoDTO = new OrgaoDTO();
    $objOrgaoDTO->retNumIdOrgao();
    $objOrgaoDTO->retStrSigla();
    $objOrgaoDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);
    $objOrgaoRN = new OrgaoRN();
    $arrObjOrgaoDTO = $objOrgaoRN->listarAdministrados($objOrgaoDTO);
    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado,$arrObjOrgaoDTO, 'IdOrgao', 'Sigla');
    
    //return '<option value="'.SessaoSip::getInstance()->getNumIdOrgaoSistema().'">'.SessaoSip::getInstance()->getStrSiglaOrgaoSistema().'</option>';     
  }

  public static function montarSelectSiglaCoordenados($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
  
    $objOrgaoDTO = new OrgaoDTO();
    $objOrgaoDTO->retNumIdOrgao();
    $objOrgaoDTO->retStrSigla();
    $objOrgaoDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);
    $objOrgaoRN = new OrgaoRN();
    $arrObjOrgaoDTO = $objOrgaoRN->listarCoordenados($objOrgaoDTO);
    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado,$arrObjOrgaoDTO, 'IdOrgao', 'Sigla');
  
    //return '<option value="'.SessaoSip::getInstance()->getNumIdOrgaoSistema().'">'.SessaoSip::getInstance()->getStrSiglaOrgaoSistema().'</option>';
  }
  
  public static function montarSelectSiglaAutorizados($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
    $objOrgaoRN = new OrgaoRN();
    $arrObjOrgaoDTO = $objOrgaoRN->listarAutorizados();
    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado,$arrObjOrgaoDTO, 'IdOrgao', 'Sigla');
  }

  public static function montarSelectSiglaPessoais($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
    /*
    $objOrgaoDTO = new OrgaoDTO();
    $objOrgaoDTO->retNumIdOrgao();
    $objOrgaoDTO->retStrSigla();
    $objOrgaoDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);
    $objOrgaoRN = new OrgaoRN();
    $arrObjOrgaoDTO = $objOrgaoRN->listarPessoais($objOrgaoDTO);
    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado,$arrObjOrgaoDTO, 'IdOrgao', 'Sigla');
    */
    return '<option value="'.SessaoSip::getInstance()->getNumIdOrgaoSistema().'">'.SessaoSip::getInstance()->getStrSiglaOrgaoSistema().'</option>';     
  }
  
}
?>