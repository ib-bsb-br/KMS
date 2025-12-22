<?
/**
 * Classe abstrata que implementa o protocolo SSH.
 * Esta classe deve ser extendida por cada aplicaчуo.
 *
 * criado em 08/04/2014 - bmy@trf4.gov.br
 * alterado em
 *
 * Observaчѕes:
 *
 */
abstract class InfraSSH implements InfraIProtocoloComunicacao {

  private $objConexao;
  private $objSFTP;
  
	public function __construct() {
	}

	/**
	 * Abrir conexуo com servidor SSH.
	 *
	 * @param boolean $bolConexaoSegura Nуo utilizado por esta implementaчуo.
	 * @param string $strIdConexao ID da conexуo
	 *
	 */
  function abrirConexao($bolConexaoSegura, $strIdConexao) {
		try {
			if ($this->getPorta($strIdConexao) == 0) {
				$objConexao = ssh2_connect($this->getServidor($strIdConexao));
			} else {
				$objConexao = ssh2_connect($this->getServidor($strIdConexao), $this->getPorta($strIdConexao));
			}
			if (!$objConexao) {
				throw new InfraException('Nуo foi possэvel abrir conexуo com o servidor '.$this->getServidor($strIdConexao).':'.$this->getPorta($strIdConexao).'].');
			} else {
				$objValidacao = ssh2_auth_password($objConexao, $this->getUsuario($strIdConexao), $this->getSenha($strIdConexao));
				if (!$objValidacao) {
					throw new InfraException('Combinaчуo usuсrio/senha invсlida [Conexуo '.$this->getServidor($strIdConexao).'].');
				} else {
					$this->objConexao = $objConexao;
					$this->objSFTP = ssh2_sftp($objConexao);
				}
			}
		} catch(Exception $e) {
			throw $e;
		}
	}

	function mostrarDiretorioLocal() {
		try {
			return $this->executarComando('pwd;');
		} catch(Exception $e) {
			throw $e;
		}
	}

	/**
	 * Retorna tamanho do arquivo no servidor remoto.
	 * A implementaчуo atual pode nуo retornar o tamanho correto do arquivo se este tiver mais de 2GB.
	 *
	 * @param string $strArquivoRemoto Caminho completo para o arquivo no servidor remoto.
	 *
	 */
	function mostrarTamanhoArquivo($strArquivoRemoto) {
		try {
			// com stat o tamanho do arquivo pode retornar errado se tiver mais de 2GB
			$statinfo = ssh2_sftp_stat($this->objSFTP, $strArquivoRemoto);
			return $statinfo['size'];
		} catch(Exception $e) {
			throw $e;
		}
	}

	function listarArquivos($strDiretorio) {
		try {
			return $this->executarComando('ls '.$strDiretorio.';');
		} catch(Exception $e) {
			throw $e;
		}
	}

	function listarArquivosDetalhes($strDiretorio) {
		try {
			return $this->executarComando('ls -l '.$strDiretorio.';');
		} catch(Exception $e) {
			throw $e;
		}
	}

	function enviarArquivo($strArquivoLocal, $strArquivoRemoto) {
		try {
			// ssh2_scp_send tem um bug nesta versуo, pode nуo copiar pedaчos do arquivo
			//return ssh2_scp_send($this->objConexao, $strArquivoLocal, $strArquivoRemoto, 0644);

			$numTamanhoArquivo = filesize($strArquivoLocal);
			$objArqLocal = @fopen($strArquivoLocal,'r');
			$objArqRemote = @fopen('ssh2.sftp://'.$this->objSFTP.$strArquivoRemoto, 'w');
			$numTamanhoTransmissao = stream_copy_to_stream($objArqLocal,$objArqRemote);
			fclose($objArqLocal);
			fclose($objArqRemote);
			ssh2_sftp_chmod($this->objSFTP, $strArquivoRemoto, 0644);
			return $numTamanhoTransmissao==$numTamanhoArquivo;
		} catch(Exception $e) {
			throw $e;
		}
	}

	function receberArquivo($strArquivoLocal, $strArquivoRemoto) {
		try {
			return ssh2_scp_recv($this->objConexao, $strArquivoRemoto, $strArquivoLocal);
		} catch(Exception $e) {
			throw $e;
		}
	}

	function renomearArquivo($strNomeArquivo, $strNovoNomeArquivo) {
		try {
			return ssh2_sftp_rename($this->objSFTP, $strNomeArquivo, $strNovoNomeArquivo) ;
		} catch(Exception $e) {
			throw $e;
		}
	}

	function apagarArquivo($strArquivoRemoto) {
		try {
			return ssh2_sftp_unlink($this->objSFTP, $strArquivoRemoto); ;
		} catch(Exception $e) {
			throw $e;
		}
	}

	function criarDiretorio($strDiretorioRemoto) {
		try {
			return ssh2_sftp_mkdir($this->objSFTP, $strDiretorioRemoto); ;
		} catch(Exception $e) {
			throw $e;
		}
	}

	function apagarDiretorio($strDiretorioRemoto) {
		try {
			return ssh2_sftp_rmdir($this->objSFTP, $strDiretorioRemoto); ;
		} catch(Exception $e) {
			throw $e;
		}
	}

	function executarComando($strComando) {
		try {
			$objResult = ssh2_exec($this->objConexao, $strComando);
			stream_set_blocking($objResult, true);
			$objStreamResult = ssh2_fetch_stream($objResult, SSH2_STREAM_STDIO);
			return stream_get_contents($objStreamResult);
		} catch(Exception $e) {
			throw $e;
		}
	}

	function mostrarTipoSistemaRemoto() {
		return '';
	}

	function fecharConexao() {
		try {
			$this->executarComando('exit;');
			return true;
		} catch(Exception $e) {
			throw $e;
		}
	}
}
?>