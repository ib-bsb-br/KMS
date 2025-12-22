<?
/*
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 27/11/2006 - criado por mga
 *
 *
 */

require_once dirname(__FILE__).'/../SEI.php';

class PublicacaoWS extends InfraWS {

	public function getObjInfraLog(){
		return LogSEI::getInstance();
	}
 
	public function confirmarDisponibilizacao($Publicacoes){
		try {

			$this->validarAcessoAutorizado(ConfiguracaoSEI::getInstance()->getValor('HostWebService','Publicacao'));
				
			InfraDebug::getInstance()->setBolLigado(true);
			InfraDebug::getInstance()->setBolDebugInfra(false);
			InfraDebug::getInstance()->limpar();

			InfraDebug::getInstance()->gravar(__METHOD__);
			InfraDebug::getInstance()->gravar('ID VEICULO PUBLICAÇÃO: '.$Publicacoes->IdVeiculoPublicacao);
			InfraDebug::getInstance()->gravar('DATA DISPONIBILIZACAO: '.$Publicacoes->DataDisponibilizacao);
			InfraDebug::getInstance()->gravar('DATA PUBLICACAO: '.$Publicacoes->DataPublicacao);
			InfraDebug::getInstance()->gravar('NÚMERO PUBLICAÇÃO EXTERNA: '.$Publicacoes->Numero);
			
			if ($Publicacoes->IdDocumentos != null && !is_array($Publicacoes->IdDocumentos)){
			  $Publicacoes->IdDocumentos = array($Publicacoes->IdDocumentos);
			}
									
			SessaoSEI::getInstance(false)->simularLogin(SessaoSEI::$USUARIO_SEI,SessaoSEI::$UNIDADE_TESTE);
			
			$arrObjPublicacaoDTO = array();
			foreach($Publicacoes->IdDocumentos as $idDocumento){
			
				$objPublicacaoDTO = new PublicacaoDTO();
				$objPublicacaoDTO->setDtaDisponibilizacao($Publicacoes->DataDisponibilizacao);
				$objPublicacaoDTO->setDblIdDocumento($idDocumento);
				$objPublicacaoDTO->setDtaPublicacao($Publicacoes->DataPublicacao);
				$objPublicacaoDTO->setNumNumero($Publicacoes->Numero);
				$arrObjPublicacaoDTO[] = $objPublicacaoDTO;
				
				InfraDebug::getInstance()->gravar('ID DOCUMENTO: '.$idDocumento);
			}
			
			$objVeiculoPublicacao = new VeiculoPublicacaoDTO();
			$objVeiculoPublicacao->setArrObjPublicacaoDTO($arrObjPublicacaoDTO);
			$objVeiculoPublicacao->setNumIdVeiculoPublicacao($Publicacoes->IdVeiculoPublicacao);
			
			$objPublicacaoRN = new PublicacaoRN();
			$objPublicacaoRN->confirmarDisponibilizacaoRN1115($objVeiculoPublicacao);
						
			LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug(),InfraLog::$INFORMACAO);

			return true;

		}catch(Exception $e){
			$this->processarExcecao($e);
		}
	}
}

$servidorSoap = new SoapServer("publicacao.wsdl",array('encoding'=>'ISO-8859-1'));

$servidorSoap->setClass("PublicacaoWS");

//Só processa se acessado via POST
if ($_SERVER['REQUEST_METHOD']=='POST') {
	$servidorSoap->handle();
}
