<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 25/09/2009 - criado por fbv@trf4.gov.br
*
* Versão do Gerador de Código: 1.29.1
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class BlocoINT extends InfraINT {

  public static function montarSelectAssinatura($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
    $objBlocoDTO = new BlocoDTO();
    $objBlocoDTO->retNumIdBloco();
    $objBlocoDTO->retStrDescricao();
    $objBlocoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
    $objBlocoDTO->setStrStaTipo(BlocoRN::$TB_ASSINATURA);
    $objBlocoDTO->setStrStaEstado(array(BlocoRN::$TE_ABERTO,BlocoRN::$TE_RETORNADO),InfraDTO::$OPER_IN);
    $objBlocoDTO->setOrdNumIdBloco(InfraDTO::$TIPO_ORDENACAO_DESC);

    $objBlocoRN = new BlocoRN();
    $arrObjBlocoDTO = $objBlocoRN->listarRN1277($objBlocoDTO);
    
    foreach($arrObjBlocoDTO as $objBlocoDTO){
    	$objBlocoDTO->setStrDescricao($objBlocoDTO->getNumIdBloco().' - '.$objBlocoDTO->getStrDescricao());
    }

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjBlocoDTO, 'IdBloco', 'Descricao');
  }

  public static function montarSelectStaEstadoRI1283($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $strStaEstado=''){
    $objBlocoRN = new BlocoRN();
    $arrObjEstadoBlocoDTO = $objBlocoRN->listarValoresEstadoRN1265();
    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjEstadoBlocoDTO, 'StaEstado', 'Descricao');
  }
  
  public static function montarTexto($strChave, $strConteudo, $numLimite){
    $ret = '';
    if ($strConteudo != ''){
      
      $strTemp = nl2br(InfraString::formatarXML($strConteudo));
      
      if (strlen($strConteudo) > $numLimite){

        $strDivResumo = 'div'.$strChave.'Resumo';
        $strDivTotal = 'div'.$strChave.'Total';
        
        $ret .= '<div id="'.$strDivResumo.'" style="display:inline" class="blocoResumo">';
        $ret .= InfraString::formatarXML(substr($strConteudo,0,$numLimite)).'...';
        $ret .= '<a onclick="document.getElementById(\''.$strDivResumo.'\').style.display=\'none\';document.getElementById(\''.$strDivTotal.'\').style.display=\'inline\';" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="/infra_css/imagens/ver_tudo.gif" title="Ver Tudo" alt="Ver Tudo" class="infraImg" /></a>';
        $ret .= '</div>';
        
        $ret .= '<div id="'.$strDivTotal.'" style="display:none;" class="blocoTotal">';
        $ret .= $strTemp.'&nbsp;&nbsp;';
        $ret .= '<a onclick="document.getElementById(\''.$strDivResumo.'\').style.display=\'inline\';document.getElementById(\''.$strDivTotal.'\').style.display=\'none\';" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="/infra_css/imagens/ver_resumo.gif" title="Ver Resumo" alt="Ver Resumo" class="infraImg" /></a></div>';
      }else{
        $ret = $strTemp;
      }
    }
    return $ret;
  }
}
?>