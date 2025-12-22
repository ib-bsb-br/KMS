<?
class InfraCaptcha {

  private function __construct(){
    
  }

  public static function obterCodigo(){
    $arrRand = array(
        array(48,57) //números
        ,array(97,122) //letras maiúsculas
        ,array(65,90) //letras minúsculas
    );

    $strCodeToRandom = '';
    $r = rand(0,2);
    $strCodeToRandom .= rand($arrRand[$r][0],$arrRand[$r][1]);

    $strCodeToRandom .= '-';

    $r = rand(0,2);
    $strCodeToRandom .= rand($arrRand[$r][0],$arrRand[$r][1]);

    return $strCodeToRandom;
  }
  
  public static function gerar($strCodigo){
    $MENOR_COD_CAPTCHA = 48;
    $MAIOR_COD_CAPTCHA = 122;
    $arrCodNaoExistentes = array(58,59,60,61,62,63,64,91,92,93,94,95,96);
    $arrCodigoParaGeracaoCaptcha = explode('-',$strCodigo);

    $strCaptcha = chr($arrCodigoParaGeracaoCaptcha[0]).chr($arrCodigoParaGeracaoCaptcha[1]);
     
    sort($arrCodigoParaGeracaoCaptcha);

    $media = round(($arrCodigoParaGeracaoCaptcha[1]-$arrCodigoParaGeracaoCaptcha[0])/2);

    if (in_array($arrCodigoParaGeracaoCaptcha[0]+$media, $arrCodNaoExistentes) || $arrCodigoParaGeracaoCaptcha[0]+$media > $MAIOR_COD_CAPTCHA){
      $strCaptcha .= chr($arrCodigoParaGeracaoCaptcha[0]);
    }else{
      $strCaptcha .= chr($arrCodigoParaGeracaoCaptcha[0]+$media);
    }

    if (in_array($arrCodigoParaGeracaoCaptcha[1]-$media,$arrCodNaoExistentes) || $arrCodigoParaGeracaoCaptcha[1]-$media < $MENOR_COD_CAPTCHA){
      $strCaptcha .= chr($arrCodigoParaGeracaoCaptcha[1]);
    }else{
      $strCaptcha .= chr($arrCodigoParaGeracaoCaptcha[1]-$media);
    }

    return $strCaptcha;
  }
}
?>