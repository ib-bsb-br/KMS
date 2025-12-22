<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 11/08/2016 - criado por mga
 *
 */

class PublicacaoAPI {
  private $NomeVeiculo;
  private $Numero;
  private $DataDisponibilizacao;
  private $DataPublicacao;
  private $Estado;
  private $ImprensaNacional;

  /**
   * @return mixed
   */
  public function getNomeVeiculo()
  {
    return $this->NomeVeiculo;
  }

  /**
   * @param mixed $NomeVeiculo
   */
  public function setNomeVeiculo($NomeVeiculo)
  {
    $this->NomeVeiculo = $NomeVeiculo;
  }

  /**
   * @return mixed
   */
  public function getNumero()
  {
    return $this->Numero;
  }

  /**
   * @param mixed $Numero
   */
  public function setNumero($Numero)
  {
    $this->Numero = $Numero;
  }

  /**
   * @return mixed
   */
  public function getDataDisponibilizacao()
  {
    return $this->DataDisponibilizacao;
  }

  /**
   * @param mixed $DataDisponibilizacao
   */
  public function setDataDisponibilizacao($DataDisponibilizacao)
  {
    $this->DataDisponibilizacao = $DataDisponibilizacao;
  }

  /**
   * @return mixed
   */
  public function getDataPublicacao()
  {
    return $this->DataPublicacao;
  }

  /**
   * @param mixed $DataPublicacao
   */
  public function setDataPublicacao($DataPublicacao)
  {
    $this->DataPublicacao = $DataPublicacao;
  }

  /**
   * @return mixed
   */
  public function getEstado()
  {
    return $this->Estado;
  }

  /**
   * @param mixed $Estado
   */
  public function setEstado($Estado)
  {
    $this->Estado = $Estado;
  }

  /**
   * @return mixed
   */
  public function getImprensaNacional()
  {
    return $this->ImprensaNacional;
  }

  /**
   * @param mixed $ImprensaNacional
   */
  public function setImprensaNacional($ImprensaNacional)
  {
    $this->ImprensaNacional = $ImprensaNacional;
  }

}
?>