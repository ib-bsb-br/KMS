<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 27/11/2006 - criado por mga
*
*
*/

require_once dirname(__FILE__).'/../Sip.php';

class UsuarioINT extends InfraINT {

  public static function montarSelectSigla($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdOrgao=''){
    $objUsuarioDTO = new UsuarioDTO();
    $objUsuarioDTO->retNumIdUsuario();

		if ($numIdOrgao!==''){
      $objUsuarioDTO->setNumIdOrgao($numIdOrgao);
		}
		
    $objUsuarioDTO->retStrSigla();
    $objUsuarioDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objUsuarioRN = new UsuarioRN();
    $arrObjUsuarioDTO = $objUsuarioRN->listar($objUsuarioDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado,$arrObjUsuarioDTO, 'IdUsuario', 'Sigla');
  }
  
  public static function autoCompletarSiglaNome($strSigla,$numIdOrgao){
    if ($strSigla == ''){
      return null;
    }
    $objUsuarioDTO = new UsuarioDTO();
    $objUsuarioDTO->retNumIdUsuario();
    $objUsuarioDTO->retStrSigla();
    $objUsuarioDTO->retStrNome();
    $objUsuarioDTO->setNumIdOrgao($numIdOrgao);
    
    $objUsuarioDTO->adicionarCriterio(array('Sigla','Nome'),
                                      array(InfraDTO::$OPER_LIKE,InfraDTO::$OPER_LIKE),
                                      array('%'.$strSigla.'%','%'.$strSigla.'%'),
                                      InfraDTO::$OPER_LOGICO_OR);
    
    $objUsuarioDTO->setNumMaxRegistrosRetorno(50);

    $objUsuarioDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);
    $objUsuarioRN = new UsuarioRN();
    return $objUsuarioRN->listar($objUsuarioDTO);
  }

  public static function autoCompletar($strSigla,$numIdOrgao){

    if ($strSigla == ''){
      return null;
    }

    $objUsuarioDTO = new UsuarioDTO();
    $objUsuarioDTO->retNumIdUsuario();
    $objUsuarioDTO->retStrSigla();
    $objUsuarioDTO->retStrNome();
    $objUsuarioDTO->setNumIdOrgao($numIdOrgao);

    $objUsuarioDTO->adicionarCriterio(array('Sigla','Nome'),
        array(InfraDTO::$OPER_LIKE,InfraDTO::$OPER_LIKE),
        array('%'.$strSigla.'%','%'.$strSigla.'%'),
        InfraDTO::$OPER_LOGICO_OR);

    $objUsuarioDTO->setNumMaxRegistrosRetorno(50);

    $objUsuarioDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);


    $objUsuarioRN = new UsuarioRN();
    $arrObjUsuarioDTO = $objUsuarioRN->listar($objUsuarioDTO);

    foreach($arrObjUsuarioDTO as $objUsuarioDTO){
      $objUsuarioDTO->setStrSigla($objUsuarioDTO->getStrNome().' ('.$objUsuarioDTO->getStrSigla().')');
    }
    return $arrObjUsuarioDTO;
  }
}
?>