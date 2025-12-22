<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 26/10/2009 - criado por mga
*
* Versão do Gerador de Código: 1.29.1
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class AssinaturaINT extends InfraINT {

  public static function montarSelectNome($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $dblIdDocumento=''){
    $objAssinaturaDTO = new AssinaturaDTO();
    $objAssinaturaDTO->retNumIdAssinatura();
    $objAssinaturaDTO->retStrNome();

    if ($dblIdDocumento!==''){
      $objAssinaturaDTO->setDblIdDocumento($dblIdDocumento);
    }

    $objAssinaturaDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objAssinaturaRN = new AssinaturaRN();
    $arrObjAssinaturaDTO = $objAssinaturaRN->listarRN1323($objAssinaturaDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjAssinaturaDTO, 'IdAssinatura', 'Nome');
  }

  public static function montarHtmlAssinaturas($arrObjAssinaturaDTO)
  {
    $strAssinaturas = '';
    foreach($arrObjAssinaturaDTO as $objAssinaturaDTO){
      $strAssinaturas .= '<div class="divItemCelula"><div class="divDiamante">&diams;&nbsp;&nbsp;</div><div>'.PaginaSEI::tratarHTML($objAssinaturaDTO->getStrNome()).'&nbsp;/&nbsp;'.PaginaSEI::tratarHTML($objAssinaturaDTO->getStrTratamento()).'</div></div>';
    }

    return $strAssinaturas;
  }
}
?>