<?
/*
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 16/04/2012 - criado por mkr@trf4.jus.br
 *
 *
 */

require_once dirname(__FILE__).'/../SEI.php';

class AssinadorWS extends InfraWS {

	public function getObjInfraLog(){
		return LogSEI::getInstance();
	}
	
	public function obterDocumentoAssinatura($numIdAssinatura, $dblIdDocumento){
		try {

			InfraDebug::getInstance()->setBolLigado(false);
			InfraDebug::getInstance()->setBolDebugInfra(true);
			InfraDebug::getInstance()->limpar();

			InfraDebug::getInstance()->gravar(__METHOD__);
			InfraDebug::getInstance()->gravar('ID ASSINATURA: '.$numIdAssinatura);
			InfraDebug::getInstance()->gravar('ID DOCUMENTO: '.$dblIdDocumento);			
						
			SessaoSEI::getInstance(false);

			$objAssinaturaDTO = new AssinaturaDTO();

			$objAssinaturaDTO->setNumIdAssinatura($numIdAssinatura);
			$objAssinaturaDTO->setDblIdDocumento($dblIdDocumento);

			$objDocumentoRN = new DocumentoRN();

			$strHash = $objDocumentoRN->obterHashDocumentoAssinatura($objAssinaturaDTO);

			if ($strHash == ''){
			  throw new InfraException('Não foi possível gerar o hash para assinatura do arquivo.');
			}

			InfraDebug::getInstance()->gravar('HASH: '.$strHash);
      
			//LogSEI::getInstance()->gravar('DEBUG: '.InfraDebug::getInstance()->getStrDebug());
			
			return base64_encode($strHash);

		}catch(Exception $e){

			//LogSEI::getInstance()->gravar('DEBUG: '.InfraDebug::getInstance()->getStrDebug());

		  LogSEI::getInstance()->gravar(InfraException::inspecionar($e));

		  return $e->__toString();
		}
	}

	public function enviarAssinaturasDocumentos($objIdsDocumentosAssinados, $strBase64ZipAssinaturas, $strHashPacoteAssinaturas, $objTamanhoAssinaturas, $strIdsAssinaturas){
		try {

			//$this->validarAcessoAutorizado(ConfiguracaoSEI::getInstance()->getValor('HostWebService','Assinador'));

			InfraDebug::getInstance()->setBolLigado(false);
			InfraDebug::getInstance()->setBolDebugInfra(false);
			InfraDebug::getInstance()->limpar();

			InfraDebug::getInstance()->gravar(__METHOD__);
      InfraDebug::getInstance()->gravar('IDs DOCUMENTOS ASSINATURAS: '.print_r($objIdsDocumentosAssinados,true));
      InfraDebug::getInstance()->gravar('BASE 64 ASSINATURAS: '.strlen($strBase64ZipAssinaturas).' bytes');
      InfraDebug::getInstance()->gravar('HASH ASSINATURAS: '.$strHashPacoteAssinaturas);
      InfraDebug::getInstance()->gravar('TAMANHO ASSINATURAS: '.print_r($objTamanhoAssinaturas->TamanhoAssinatura,true));
      InfraDebug::getInstance()->gravar('IDs ASSINATURAS: '.$strIdsAssinaturas);
			
			SessaoSEI::getInstance(false);

			if ($objIdsDocumentosAssinados->IdDocumentoAssinado!=null && !is_array($objIdsDocumentosAssinados->IdDocumentoAssinado)){
			  $objIdsDocumentosAssinados->IdDocumentoAssinado = array($objIdsDocumentosAssinados->IdDocumentoAssinado);
			}
			
			if ($objTamanhoAssinaturas->TamanhoAssinatura!=null &&  !is_array($objTamanhoAssinaturas->TamanhoAssinatura)){
			  $objTamanhoAssinaturas->TamanhoAssinatura = array($objTamanhoAssinaturas->TamanhoAssinatura);
			}
			
			$binZipAssinaturas = base64_decode($strBase64ZipAssinaturas);

			if($binZipAssinaturas == null) {
				throw new InfraException('Bloco de assinaturas inválido.');
			}

			if(hash('sha256',$binZipAssinaturas) != $strHashPacoteAssinaturas){
				throw new InfraException('Codigo de integridade inválido. ' .hash('sha256',$binZipAssinaturas));
			}

			$objAnexoRN = new AnexoRN();
			$nomeZipado = $objAnexoRN->gerarNomeArquivoTemporario();

			if (!$handle = fopen(DIR_SEI_TEMP.'/'.$nomeZipado, 'w')) {
				throw new InfraException('Impossível gravar arquivo temporário em disco: ' .$nomeZipado);
			}
			if (fwrite($handle, $binZipAssinaturas) === FALSE) {
				throw new InfraException('Impossível gravar arquivo temporário em disco: ' .$nomeZipado);
			}
			fclose($handle);

			// descompacta as assinaturas, que já estão em disco
			$zip = zip_open(DIR_SEI_TEMP.'/'.$nomeZipado);

			if (is_resource($zip)){
				//processar o zip e colocar todas as assinaturas em um array indexado
				//nao confio na ordem de leitura do php
				$arrAssinaturas = array();
				while ($zip_entry = zip_read($zip)) {
					if (zip_entry_open($zip, $zip_entry, "r")) {
						$umaAssinatura = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));						
						$arrAssinaturas[zip_entry_name($zip_entry)] = $umaAssinatura;
						zip_entry_close($zip_entry);
					} else {
						try {
							zip_close($zip);
							unlink(DIR_SEI_TEMP.'/'.$nomeZipado);
						} catch (Exception $e) {}
						throw new InfraException('Impossível ler conteúdo do arquivo compactado ' .zip_entry_name($zip_entry));
					}
				}
				zip_close($zip);
				unlink(DIR_SEI_TEMP.'/'.$nomeZipado);
			}else{
				throw new InfraException('Não foi possível ler as assinaturas compactadas recebidas.');
			}
				
			$objDocumentoRN = new DocumentoRN();

			$arrIdsAssinaturas = explode('|',$strIdsAssinaturas);
			for ($i=0;$i<count($objIdsDocumentosAssinados->IdDocumentoAssinado);$i++) {				
				if (strlen($arrAssinaturas[$objIdsDocumentosAssinados->IdDocumentoAssinado[$i]]) != $objTamanhoAssinaturas->TamanhoAssinatura[$i]) {
				  throw new InfraException('Tamanho inválido para o arquivo '.$objIdsDocumentosAssinados->IdDocumentoAssinado[$i]. ' Real: ' . strlen($arrAssinaturas[$objIdsDocumentosAssinados->IdDocumentoAssinado[$i]]) .' - Informado: ' . $objTamanhoAssinaturas->TamanhoAssinatura[$i]);
				}					
				// gerar assinatura (fazer via sigla/senha; ou smartcard: obtenção dos dados e criação do p7s neste caso) (buscar dos dados informados pelo usuário no SEI)
				$objAssinaturaDTO = new AssinaturaDTO();				
				$objAssinaturaDTO->setNumIdAssinatura($arrIdsAssinaturas[$i]);				
				$objAssinaturaDTO->setStrP7sBase64(base64_encode($arrAssinaturas[$objIdsDocumentosAssinados->IdDocumentoAssinado[$i]]));				
				$objDocumentoRN->confirmarAssinatura($objAssinaturaDTO);			
			}

			//LogSEI::getInstance()->gravar('DEBUG: '.InfraDebug::getInstance()->getStrDebug());

			return "OK";

		}catch(Exception $e){

			//LogSEI::getInstance()->gravar('DEBUG: '.InfraDebug::getInstance()->getStrDebug());

		  LogSEI::getInstance()->gravar(InfraException::inspecionar($e));

			return $e->__toString();
		}
	}
}

$servidorSoap = new SoapServer("assinador.wsdl",array('encoding'=>'ISO-8859-1'));

$servidorSoap->setClass("AssinadorWS");

//Só processa se acessado via POST
if ($_SERVER['REQUEST_METHOD']=='POST') {
	$servidorSoap->handle();
}
