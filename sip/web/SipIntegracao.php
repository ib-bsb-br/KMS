<?
abstract class SipIntegracao {
  public abstract function getNome();
  public abstract function getVersao();
  public abstract function getInstituicao();
  public function inicializar() {return null;}
  public function processarControlador($strAcao){return null;}
  public function processarControladorAjax($strAcaoAjax){return null;}
  public function processarControladorWebServices($strServico){return null;}
}
?>