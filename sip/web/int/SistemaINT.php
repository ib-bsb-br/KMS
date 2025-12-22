<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 04/01/2007 - criado por mga
*
*
*/

require_once dirname(__FILE__).'/../Sip.php';

class SistemaINT extends InfraINT {

  public static function montarSelectSigla($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdOrgao='', $numIdHierarquia=''){
    $objSistemaDTO = new SistemaDTO();
    $objSistemaDTO->retNumIdSistema();

    if ($numIdOrgao!==''){
      $objSistemaDTO->setNumIdOrgao($numIdOrgao);
    }

    if ($numIdHierarquia!==''){
      $objSistemaDTO->setNumIdHierarquia($numIdHierarquia);
    }

    $objSistemaDTO->retStrSigla();
    $objSistemaDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objSistemaRN = new SistemaRN();
    $arrObjSistemaDTO = $objSistemaRN->listar($objSistemaDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado,$arrObjSistemaDTO, 'IdSistema', 'Sigla');
  }

  /** Somente sistemas administrados */
  public static function montarSelectSiglaAdministrados($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdOrgao='', $numIdHierarquia=''){
    $objSistemaDTO = new SistemaDTO();
    $objSistemaDTO->retNumIdSistema();

    if ($numIdOrgao!==''){
      $objSistemaDTO->setNumIdOrgao($numIdOrgao);
    }

    if ($numIdHierarquia!==''){
      $objSistemaDTO->setNumIdHierarquia($numIdHierarquia);
    }

    $objSistemaDTO->retStrSigla();
    $objSistemaDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objSistemaRN = new SistemaRN();
    $arrObjSistemaDTO = $objSistemaRN->listarAdministrados($objSistemaDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado,$arrObjSistemaDTO, 'IdSistema', 'Sigla');
  }

  /** Somente sistemas com perfis coordenados pelo usuário */
  public static function montarSelectSiglaCoordenados($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdOrgao='', $numIdHierarquia=''){
    $objSistemaDTO = new SistemaDTO();
    $objSistemaDTO->retNumIdSistema();
  
    if ($numIdOrgao!==''){
      $objSistemaDTO->setNumIdOrgao($numIdOrgao);
    }
  
    if ($numIdHierarquia!==''){
      $objSistemaDTO->setNumIdHierarquia($numIdHierarquia);
    }
  
    $objSistemaDTO->retStrSigla();
    $objSistemaDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);
  
    $objSistemaRN = new SistemaRN();
    $arrObjSistemaDTO = $objSistemaRN->listarCoordenados($objSistemaDTO);
  
    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado,$arrObjSistemaDTO, 'IdSistema', 'Sigla');
  }
  
	/** Todos os sistemas autorizados - que o usuario tem acesso (exceto via suas permissoes individuais)  */
  public static function montarSelectSiglaAutorizados($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdOrgao='', $numIdHierarquia=''){
    $objSistemaDTO = new SistemaDTO();
    $objSistemaDTO->retNumIdSistema();

    if ($numIdOrgao!==''){
      $objSistemaDTO->setNumIdOrgao($numIdOrgao);
    }

    if ($numIdHierarquia!==''){
      $objSistemaDTO->setNumIdHierarquia($numIdHierarquia);
    }

    $objSistemaDTO->retStrSigla();
    $objSistemaDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objSistemaRN = new SistemaRN();
    $arrObjSistemaDTO = $objSistemaRN->listarAutorizados($objSistemaDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado,$arrObjSistemaDTO, 'IdSistema', 'Sigla');
  }

	/** Sistemas onde o usuario é administrador, se for administrador do SIP em determinado órgão então carrega todos os sistemas deste orgão  */
  public static function montarSelectSiglaSip($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdOrgao='', $numIdHierarquia=''){
    $objSistemaDTO = new SistemaDTO();
    $objSistemaDTO->retNumIdSistema();

    if ($numIdOrgao!==''){
      $objSistemaDTO->setNumIdOrgao($numIdOrgao);
    }

    if ($numIdHierarquia!==''){
      $objSistemaDTO->setNumIdHierarquia($numIdHierarquia);
    }

    $objSistemaDTO->retStrSigla();
    $objSistemaDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objSistemaRN = new SistemaRN();
    $arrObjSistemaDTO = $objSistemaRN->listarSip($objSistemaDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado,$arrObjSistemaDTO, 'IdSistema', 'Sigla');
  }

	
  public static function montarSelectSiglaPessoais($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdOrgao='', $numIdHierarquia=''){
    $objSistemaDTO = new SistemaDTO();
    $objSistemaDTO->retNumIdSistema();

    if ($numIdOrgao!==''){
      $objSistemaDTO->setNumIdOrgao($numIdOrgao);
    }

    if ($numIdHierarquia!==''){
      $objSistemaDTO->setNumIdHierarquia($numIdHierarquia);
    }

    $objSistemaDTO->retStrSigla();
    $objSistemaDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objSistemaRN = new SistemaRN();
    $arrObjSistemaDTO = $objSistemaRN->listarPessoais($objSistemaDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado,$arrObjSistemaDTO, 'IdSistema', 'Sigla');
  }

  public static function montarSelectTipoBanco($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado) {
    return parent::montarSelectArray($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, array(SistemaRN::$TBD_SQLSERVER => 'SqlServer',
        SistemaRN::$TBD_MYSQL => 'MySql',
        SistemaRN::$TBD_ORACLE => 'Oracle'));
  }

}



?>