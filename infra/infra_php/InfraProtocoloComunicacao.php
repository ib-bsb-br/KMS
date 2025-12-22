<?
/**
 * Classe abstrata a ser implementada por classes que implementem um protocolo.
 *
 * criado em 08/04/2014 - bmy@trf4.gov.br
 * alterado em
 *
 * Observações:
 *
 */
interface InfraIProtocoloComunicacao {
	 
	public abstract function abrirConexao($bolConexaoSegura);
	public abstract function mostrarDiretorioLocal();
	public abstract function mostrarTamanhoArquivo($strArquivoRemoto);
	public abstract function listarArquivos($strDiretorio);
	public abstract function listarArquivosDetalhes($strDiretorio);
	public abstract function enviarArquivo($strArquivoLocal, $strArquivoRemoto);
	public abstract function receberArquivo($strArquivoLocal, $strArquivoRemoto);
	public abstract function apagarArquivo($strArquivoRemoto);
	public abstract function criarDiretorio($strDiretorioRemoto);
	public abstract function apagarDiretorio($strDiretorioRemoto);
	public abstract function executarComando($strComando);
	public abstract function mostrarTipoSistemaRemoto();
	public abstract function fecharConexao();
	public function getServidor();
	public function getPorta();
	public function getUsuario();
	public function getSenha();
	public function setServidor($strServidor);
	public function setPorta($strPorta);
	public function setUsuario($strUsuario);
	public function setSenha($strSenha);
}
?>