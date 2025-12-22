<?
require_once 'Infra.php';

ini_set('session.gc_maxlifetime','28800');
ini_set('memory_limit','256M');

define('SEI_VERSAO','3.0.11');

define('DIR_SEI_CONFIG', __DIR__.'/../config');
define('DIR_SEI_TEMP', __DIR__.'/../temp');
define('DIR_SEI_BIN',__DIR__.'/../bin');

define('TAM_SENHA_USUARIO_EXTERNO', 8);
define('TAM_BLOCO_LEITURA_ARQUIVO', 10485760);

require_once DIR_SEI_CONFIG.'/ConfiguracaoSEI.php';

$objConfiguracaoSEI = ConfiguracaoSEI::getInstance();

define('DIGITOS_DOCUMENTO', $objConfiguracaoSEI->getValor('SEI', 'DigitosDocumento',false, 7));
//ini_set('session.cookie_secure', $objConfiguracaoSEI->getValor('SessaoSEI', 'https'));

$INFRA_PATHS[] = __DIR__;
$INFRA_PATHS[] = __DIR__.'/api';
$INFRA_PATHS[] = __DIR__.'/editor';
$INFRA_PATHS[] = __DIR__.'/solr';
$INFRA_PATHS[] = __DIR__.'/publicacoes';

$SEI_MODULOS = array();

if ($objConfiguracaoSEI->isSetValor('SEI','Modulos')){

  foreach($objConfiguracaoSEI->getValor('SEI','Modulos') as $strModulo => $strPathModulo){

    infraAdicionarPath(__DIR__.'/modulos/'.$strPathModulo);

    if (!file_exists(__DIR__.'/modulos/'.$strPathModulo . '/' . $strModulo .'.php')) {
      die('Classe de Integraзгo do mуdulo "'.$strModulo.'" nгo encontrada.');
    }

    $reflectionClass = new ReflectionClass($strModulo);
    $SEI_MODULOS[$strModulo] = $reflectionClass->newInstance();

  }

  foreach($SEI_MODULOS as $strModulo => $objModulo){

    if (trim($objModulo->getNome())==''){
      die('Nome do mуdulo "'.$strModulo.'" nгo informado.');
    }

    if (trim($objModulo->getVersao())==''){
      die('Versгo do mуdulo "'.$strModulo.'" nгo informada.');
    }

    if (trim($objModulo->getInstituicao())==''){
      die('Instituiзгo do mуdulo "'.$strModulo.'" nгo informada.');
    }

    $objModulo->executar('inicializar', SEI_VERSAO);
  }
}
?>