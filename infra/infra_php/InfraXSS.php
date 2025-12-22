<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 05/04/2018 - criado por MGA
 *
 * @package infra_php
 */


class InfraXSS {

  private $arrImagens = array();
  private $strDiferenca = '';

  public function __construct(){

  }

  public function getStrDiferenca(){
    return $this->strDiferenca;
  }

  public function verificacaoBasica($strConteudo, $arrValoresNaoPermitidos = null){

    $ret = null;

    if ($arrValoresNaoPermitidos == null) {
      $arrValoresNaoPermitidos = array('XMLHttpRequest',
          'setRequestHeader',
          'onload',
          'decodeURIComponent',
          'document.cookie',
          'document.write',
          'parentNode',
          'innerHTML',
          'appendChild');
    }

    $strConteudoVerificacao = strtolower($strConteudo);
    foreach($arrValoresNaoPermitidos as $strNaoPermitido) {
      if (strpos($strConteudoVerificacao, strtolower(trim($strNaoPermitido))) !== false) {

        if ($ret == null){
          $ret = array();
        }

        $ret[] = $strNaoPermitido;
      }
    }

    return $ret;
  }

  public function verificacaoAvancada(&$strConteudo, $arrTagsPermitidas=null, $arrTagsAtributosPermitidos=null){


    if ($arrTagsPermitidas == null){
      $arrTagsPermitidas = array('html', 'body', 'head', 'style', 'meta', 'link', 'title', 'input');
    }

    if ($arrTagsAtributosPermitidos == null){
      $arrTagsAtributosPermitidos = array('style');
    }


    $objAntiXSS = new voku\helper\AntiXSS();
    $objAntiXSS->removeEvilHtmlTags($arrTagsPermitidas);
    $objAntiXSS->removeEvilAttributes($arrTagsAtributosPermitidos);
    //$objAntiXSS->setReplacement('[removed]');

    $strConteudo = str_replace('<!--/*--><![CDATA[/*><!--*/','',$strConteudo);
    $strConteudo = str_replace('/*]]>*/-->','',$strConteudo);
    $strConteudo = str_replace('<!--[if-->','',$strConteudo);

    $strConteudo = str_replace('href="javascript:void(0);"','',$strConteudo);
    $strConteudo = str_replace('href="javascript:void(0)"','',$strConteudo);
    $strConteudo = str_replace('href="javascript:;"','',$strConteudo);
    $strConteudo = str_replace('href="javascript:"','',$strConteudo);
    $strConteudo = str_replace('xmlns="http://www.w3.org/1999/xhtml"','',$strConteudo);

    //remove href de telefones
    $strConteudo = preg_replace_callback('#href="callto:([^"]*)"#', array($this,'validarTelefone'),$strConteudo);

    //remove comentarios condicionais
    $strConteudo = preg_replace('/<!(--)\s*\[if(?>(?!\]\1>).|\s)*\]\s*\1>/', '', $strConteudo);

    //remove comentarios simples
    $strConteudo = preg_replace('/<!(--)([\s\S]*?)-->/', '', $strConteudo);

    //retirar imagens base64 antes do filtro
    $strConteudo = preg_replace_callback('#data:\s*image/[a-z\-\+]+\s*;base64,[a-zA-Z0-9\/\+]+=*#',
                                         array($this,'substituirConteudoHash'),$strConteudo);

    $strConteudo = $objAntiXSS->xss_clean($strConteudo);

    //recolocar imagens base64 após filtro
    $strConteudo = preg_replace_callback('#data-infra-hash-([a-f0-9]{32}).jpg#',
                                         array($this,'substituirHashConteudo'),$strConteudo);

    //ini_set('default_charset','ISO-8859-1');

    if ($objAntiXSS->isXssFound()) {
      $this->strDiferenca=$objAntiXSS->getXssDiff();
       return true;
    }

    return false;
  }

  private function substituirConteudoHash($match){
    $strHash=hash('md5',$match[0]);
    $this->arrImagens[$strHash]=$match[0];
    return 'data-infra-hash-'.$strHash.'.jpg';
  }

  private function substituirHashConteudo($match){
    return $this->arrImagens[$match[1]];
  }

  private function validarTelefone($match){
    $str=urldecode($match[1]);
    if (preg_match('/[\(\)0-9\-+ ]*/',$str)===1){
      return 'href=""';
    }
    return $match[0];
  }
}
?>