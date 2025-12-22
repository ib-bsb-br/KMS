<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 11/11/2015 - criado por mga
*
* Versão do Gerador de Código: 1.36.0
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class MarcadorINT extends InfraINT {

  public static function montarSelectNome($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdUnidade=''){
    $objMarcadorDTO = new MarcadorDTO();
    $objMarcadorDTO->retNumIdMarcador();
    $objMarcadorDTO->retStrNome();

    if ($numIdUnidade!==''){
      $objMarcadorDTO->setNumIdUnidade($numIdUnidade);
    }

    if ($strValorItemSelecionado!=null){
      $objMarcadorDTO->setBolExclusaoLogica(false);
      $objMarcadorDTO->adicionarCriterio(array('SinAtivo','IdMarcador'),array(InfraDTO::$OPER_IGUAL,InfraDTO::$OPER_IGUAL),array('S',$strValorItemSelecionado),InfraDTO::$OPER_LOGICO_OR);
    }

    $objMarcadorDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objMarcadorRN = new MarcadorRN();
    $arrObjMarcadorDTO = $objMarcadorRN->listar($objMarcadorDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjMarcadorDTO, 'IdMarcador', 'Nome');
  }

  public static function montarSelectMarcador($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
    $ret = '';

    $objMarcadorDTO = new MarcadorDTO();
    $objMarcadorDTO->retNumIdMarcador();
    $objMarcadorDTO->retStrNome();
    $objMarcadorDTO->retStrStaIcone();
    $objMarcadorDTO->retStrSinAtivo();
    $objMarcadorDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

    if ($strValorItemSelecionado!=null){

      $objMarcadorDTO->setBolExclusaoLogica(false);
      $objMarcadorDTO->adicionarCriterio(array('SinAtivo','IdMarcador'),
          array(InfraDTO::$OPER_IGUAL,InfraDTO::$OPER_IGUAL),
          array('S',$strValorItemSelecionado),
          InfraDTO::$OPER_LOGICO_OR);
    }

    $objMarcadorDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objMarcadorRN = new MarcadorRN();
    $arrObjMarcadorDTO = $objMarcadorRN->listar($objMarcadorDTO);

    foreach($arrObjMarcadorDTO as $dto){
      $dto->setStrNome(self::formatarMarcadorDesativado($dto->getStrNome(),$dto->getStrSinAtivo()));
    }

    $arrObjIconeMarcadorDTO = InfraArray::indexarArrInfraDTO($objMarcadorRN->listarValoresIcone(),'StaIcone');

    $ret .= '<option value="null" '.($strValorItemSelecionado===null?'selected="selected"':'').'>'.$strPrimeiroItemDescricao.'</option>'."\n";

    foreach ($arrObjMarcadorDTO as $objMarcadorDTO) {
      $ret .= '<option '.(($objMarcadorDTO->getNumIdMarcador()==$strValorItemSelecionado)?'selected="selected"':'').' value="' .$objMarcadorDTO->getNumIdMarcador() . '" data-imagesrc="imagens/'.$arrObjIconeMarcadorDTO[$objMarcadorDTO->getStrStaIcone()]->getStrArquivo().'">'.$objMarcadorDTO->getStrNome().'</option>'."\n";
    }

    return $ret;
  }

  public static function montarSelectStaIcone($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
    $ret = '';

    $objMarcadorRN = new MarcadorRN();
    $arrObjIconeMarcadorDTO = $objMarcadorRN->listarValoresIcone();

    $ret .= '<option value="null" '.($strValorItemSelecionado===null?'selected="selected"':'').'>&nbsp;</option>'."\n";

    foreach ($arrObjIconeMarcadorDTO as $objIconeMarcadorDTO) {
      $ret .= '<option '.(($objIconeMarcadorDTO->getStrStaIcone()==$strValorItemSelecionado)?'selected="selected"':'').' value="' .$objIconeMarcadorDTO->getStrStaIcone() . '" data-imagesrc="imagens/'.$objIconeMarcadorDTO->getStrArquivo().'">&nbsp;</option>'."\n";
    }

    return $ret;
  }

  public static function formatarMarcadorDesativado($strNomeMarcador, $strSinAtivoMarcador){
    return $strNomeMarcador.(($strSinAtivoMarcador == 'N')?' - DESATIVADO':'');
  }

}
?>