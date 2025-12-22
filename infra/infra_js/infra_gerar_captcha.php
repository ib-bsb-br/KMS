<?php
require_once 'Infra.php';
$strFonte = '../infra_php/captcha/century.ttf';
$strCaptcha = InfraCaptcha::gerar($_GET['codetorandom']);
$objImagem = ImageCreateFromPNG("../infra_php/captcha/imagens_fundo/bg".rand(1, 13).".png");

$numTamanho = rand(16, 18);
$numAngulo = rand(-5, 5);
$numTamanhoTexto = imagettfbbox($numTamanho, $numAngulo, $strFonte, $strCaptcha);
$numLargura = abs($numTamanhoTexto[2]-$numTamanhoTexto[0]);
$numAltura = abs($numTamanhoTexto[5]-$numTamanhoTexto[3]);
ImageTTFText($objImagem, $numTamanho, $numAngulo, 
    (imagesx($objImagem)/2) - ($numLargura/2) + (rand(-20, 20)), 
    (imagesy($objImagem))-($numAltura/2), 
    ImageColorAllocate($objImagem, rand(0, 100), rand(0, 100), rand(0, 100)), 
    $strFonte, $strCaptcha[0].' '.$strCaptcha[1].' '.$strCaptcha[2].' '.$strCaptcha[3]);

header('Content-type: image/png');
ImagePNG($objImagem);
ImageDestroy($objImagem);
?>