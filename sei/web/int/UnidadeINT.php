<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 14/04/2008 - criado por mga
 *
 * Versão do Gerador de Código: 1.14.0
 *
 * Versão no CVS: $Id$
 */

require_once dirname(__FILE__).'/../SEI.php';

class UnidadeINT extends InfraINT {

  public static function montarSelectSiglaDescricaoPesquisaPublicacao($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrNumIdOrgao=''){
     
    $objUnidadePublicacaoDTO = new UnidadePublicacaoDTO();
    $objUnidadePublicacaoDTO->retNumIdUnidade();
    $objUnidadePublicacaoDTO->retStrSiglaUnidade();
    $objUnidadePublicacaoDTO->retStrDescricaoUnidade();
    $objUnidadePublicacaoDTO->retNumIdOrgaoUnidade();
    
    
    if ($arrNumIdOrgao!=''){    
      $objUnidadePublicacaoDTO->setNumIdOrgaoUnidade($arrNumIdOrgao,InfraDTO::$OPER_IN);
    }

    $objUnidadePublicacaoDTO->setOrdStrSiglaUnidade(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objUnidadePublicacaoRN = new UnidadePublicacaoRN();
    $arrObjUnidadePublicacaoDTO = $objUnidadePublicacaoRN->listar($objUnidadePublicacaoDTO);

    foreach($arrObjUnidadePublicacaoDTO as $objUnidadePublicacaoDTO){
      $objUnidadePublicacaoDTO->setStrSiglaUnidade(UnidadeINT::formatarSiglaDescricao($objUnidadePublicacaoDTO->getStrSiglaUnidade(),$objUnidadePublicacaoDTO->getStrDescricaoUnidade()));
    }

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjUnidadePublicacaoDTO, 'IdUnidade', 'SiglaUnidade');
  }
  
	public static function montarSelectSiglaDescricao($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdOrgao=''){
		$objUnidadeDTO = new UnidadeDTO();
		$objUnidadeDTO->retNumIdUnidade();
		$objUnidadeDTO->retStrSigla();
		$objUnidadeDTO->retStrDescricao();

		if (!InfraString::isBolVazia($numIdOrgao)){
			$objUnidadeDTO->setNumIdOrgao($numIdOrgao);
		}

		$objUnidadeDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

		$objUnidadeRN = new UnidadeRN();
		$arrObjUnidadeDTO = $objUnidadeRN->listarTodasComFiltro($objUnidadeDTO);

		foreach($arrObjUnidadeDTO as $objUnidadeDTO){
			$objUnidadeDTO->setStrSigla(UnidadeINT::formatarSiglaDescricao($objUnidadeDTO->getStrSigla(),$objUnidadeDTO->getStrDescricao()));
		}

		return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjUnidadeDTO, 'IdUnidade', 'Sigla');
	}

	public static function montarSelectSigla($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdOrgao=''){
		$objUnidadeDTO = new UnidadeDTO();
		$objUnidadeDTO->retNumIdUnidade();
		$objUnidadeDTO->retStrSigla();

		if (!InfraString::isBolVazia($numIdOrgao)){
			$objUnidadeDTO->setNumIdOrgao($numIdOrgao);
		}

		$objUnidadeDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

		$objUnidadeRN = new UnidadeRN();
		$arrObjUnidadeDTO = $objUnidadeRN->listarTodasComFiltro($objUnidadeDTO);

		return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjUnidadeDTO, 'IdUnidade', 'Sigla');
	}

	public static function montarSelectSiglaMigracao($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdOrigem){
		
	  $objUnidadeDTO = new UnidadeDTO();
		$objUnidadeDTO->retNumIdUnidade();
		$objUnidadeDTO->retStrSigla();
		$objUnidadeDTO->retStrDescricao();

		$objUnidadeDTO->setNumIdUnidade($numIdOrigem,InfraDTO::$OPER_DIFERENTE);

		$objUnidadeDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

		$objUnidadeRN = new UnidadeRN();
		$arrObjUnidadeDTO = $objUnidadeRN->listarRN0127($objUnidadeDTO);
		
		foreach($arrObjUnidadeDTO as $objUnidadeDTO){
		  $objUnidadeDTO->setStrSigla(self::formatarSiglaDescricao($objUnidadeDTO->getStrSigla(),$objUnidadeDTO->getStrDescricao()));
		}

		return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjUnidadeDTO, 'IdUnidade', 'Sigla');
	}
	
	public static function autoCompletarUnidades($strPalavrasPesquisa, $bolTodas, $numIdOrgao = ''){

		$objUnidadeDTO = new UnidadeDTO();
		$objUnidadeDTO->retNumIdUnidade();
		$objUnidadeDTO->retStrSigla();
		$objUnidadeDTO->retStrDescricao();
		$objUnidadeDTO->setNumMaxRegistrosRetorno(50);
		$objUnidadeDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

    if ($strPalavrasPesquisa!=''){
      $objUnidadeDTO->setStrPalavrasPesquisa($strPalavrasPesquisa);
    }

		if ($numIdOrgao!= ""){
			$objUnidadeDTO->setNumIdOrgao(explode(',',$numIdOrgao),InfraDTO::$OPER_IN);
		}

		$objUnidadeRN = new UnidadeRN();
		if ($bolTodas){
			$arrObjUnidadeDTO = $objUnidadeRN->listarTodasComFiltro($objUnidadeDTO);
		}else{
			$arrObjUnidadeDTO = $objUnidadeRN->listarOutrasComFiltro($objUnidadeDTO);
		}

		foreach($arrObjUnidadeDTO as $objUnidadeDTO){
			$objUnidadeDTO->setStrSigla(UnidadeINT::formatarSiglaDescricao($objUnidadeDTO->getStrSigla(),$objUnidadeDTO->getStrDescricao()));
		}

    return $arrObjUnidadeDTO;
	}

	public static function autoCompletarEnvioProcesso($strPalavrasPesquisa){

		$objUnidadeDTO = new UnidadeDTO();
		$objUnidadeDTO->retNumIdUnidade();
		$objUnidadeDTO->retStrSigla();
		$objUnidadeDTO->retStrDescricao();
		$objUnidadeDTO->setNumMaxRegistrosRetorno(50);
		$objUnidadeDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objUnidadeDTO->setStrPalavrasPesquisa($strPalavrasPesquisa);

		$objUnidadeRN = new UnidadeRN();
		$arrObjUnidadeDTO = $objUnidadeRN->listarEnvioProcesso($objUnidadeDTO);

		foreach($arrObjUnidadeDTO as $objUnidadeDTO){
			$objUnidadeDTO->setStrSigla(UnidadeINT::formatarSiglaDescricao($objUnidadeDTO->getStrSigla(),$objUnidadeDTO->getStrDescricao()));
		}

    return $arrObjUnidadeDTO;
	}
	
	public static function formatarSiglaDescricao($strSigla, $strDescricao){
		return $strSigla.' - '.$strDescricao;
	}
}
?>