<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 26/11/2014 - criado por mga
 *
 */

require_once dirname(__FILE__).'/../SEI.php';

class UnidadeHojeBD extends InfraBD {

  public function __construct(InfraIBanco $objInfraIBanco){
    parent::__construct($objInfraIBanco);
  }

}
?>