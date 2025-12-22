<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 27/11/2006 - criado por mga
*
*
*/

require_once dirname(__FILE__).'/../SEI.php';

class OuvidoriaWS extends InfraWS {

  public function getObjInfraLog(){
    return LogSEI::getInstance();
  }

  public function gerarEstatisticasPortal($numMes, $numAno){
    try{

      $this->validarAcessoAutorizado(ConfiguracaoSEI::getInstance()->getValor('HostWebService','Ouvidoria'));

      /*
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(true);
      InfraDebug::getInstance()->limpar();
      
      InfraDebug::getInstance()->gravar('MES:'.$numMes);
      InfraDebug::getInstance()->gravar('ANO:'.$numAno);
      */

      SessaoSEI::getInstance(false)->simularLogin(SessaoSEI::$USUARIO_INTERNET,SessaoSEI::$UNIDADE_TESTE);

      $objEstatisticasOuvidoriaDTO = new EstatisticasOuvidoriaDTO();
      $objEstatisticasOuvidoriaDTO->setNumMes($numMes);
      $objEstatisticasOuvidoriaDTO->setNumAno($numAno);

      $objOuvidoriaRN = new OuvidoriaRN();
      $arrEstatisticas = $objOuvidoriaRN->gerarEstatisticasPortal($objEstatisticasOuvidoriaDTO);

      //LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());

      return serialize($arrEstatisticas);

    }catch(Exception $e){
      $this->processarExcecao($e);
    }
  }

  public function listarEstados($Uf){
    try{
      SessaoSEI::getInstance(false);
      $this->validarAcessoAutorizado(ConfiguracaoSEI::getInstance()->getValor('HostWebService','Ouvidoria'));
      $listaUfs= UfINT::montarSelectSiglaSigla('null',' ', $Uf);
      return $listaUfs;
    } catch(Exception $e){
      $this->processarExcecao($e);
    }
    return null;
  }

  public function listarCidades($Uf,$Cidade){
    try{

      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->limpar();

      SessaoSEI::getInstance(false);

      $this->validarAcessoAutorizado(ConfiguracaoSEI::getInstance()->getValor('HostWebService','Ouvidoria'));

      InfraDebug::getInstance()->gravar('UF:'.$Uf);
      InfraDebug::getInstance()->gravar('Cidade:'.$Cidade);

      $listaCidades= CidadeINT::montarSelectNomeNome('null', ' ', $Cidade, $Uf);

      //LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());

      return $listaCidades;

    } catch(Exception $e){
      $this->processarExcecao($e);
    }
  }

  public function listarTiposProcedimento($IdTipoProcedimento){
    try{
      SessaoSEI::getInstance(false);
      $this->validarAcessoAutorizado(ConfiguracaoSEI::getInstance()->getValor('HostWebService','Ouvidoria'));

      /*
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(true);
      InfraDebug::getInstance()->limpar();
      */

      $listaTiposProcedimento= TipoProcedimentoINT::montarSelectOuvidoria('null','&nbsp;',$IdTipoProcedimento);

      //LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());

      return $listaTiposProcedimento;

    } catch(Exception $e){
      $this->processarExcecao($e);
    }
  }

  public function registrarOuvidoria($IdOrgao, $Nome, $Email, $Cpf, $Rg, $OrgaoExpedidor, $Telefone, $Estado, $Cidade, $IdTipoProcedimento, $Processos, $SinRetorno, $Mensagem, $AtributosAdicionais, $IdProcedimentoOrigem){

    try{

      $this->validarAcessoAutorizado(ConfiguracaoSEI::getInstance()->getValor('HostWebService','Ouvidoria'));


      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->limpar();

      InfraDebug::getInstance()->gravar(__METHOD__);
      InfraDebug::getInstance()->gravar('ID_ORGAO:'.$IdOrgao);
      InfraDebug::getInstance()->gravar('NOME:'.$Nome);
      InfraDebug::getInstance()->gravar('EMAIL:'.$Email);
      InfraDebug::getInstance()->gravar('CPF:'.$Cpf);
      InfraDebug::getInstance()->gravar('RG:'.$Rg);
      InfraDebug::getInstance()->gravar('ORGAO_EXPEDIDOR:'.$OrgaoExpedidor);
      InfraDebug::getInstance()->gravar('TELEFONE:'.$Telefone);
      InfraDebug::getInstance()->gravar('ESTADO:'.$Estado);
      InfraDebug::getInstance()->gravar('CIDADE:'.$Cidade);
      InfraDebug::getInstance()->gravar('ID TIPO PROCEDIMENTO:'.$IdTipoProcedimento);
      InfraDebug::getInstance()->gravar('PROCESSOS:'.$Processos);
      InfraDebug::getInstance()->gravar('SIN_RETORNO:'.$SinRetorno);
      InfraDebug::getInstance()->gravar('MENSAGEM:'.$Mensagem);
      InfraDebug::getInstance()->gravar('ATRIBUTOS ADICIONAIS:'.$AtributosAdicionais);

      SessaoSEI::getInstance(false)->simularLogin(SessaoSEI::$USUARIO_INTERNET,SessaoSEI::$UNIDADE_TESTE);

      $objProcedimentoOuvidoriaDTO = new ProcedimentoOuvidoriaDTO();
      $objProcedimentoOuvidoriaDTO->setNumIdOrgao($IdOrgao);
      $objProcedimentoOuvidoriaDTO->setStrNome($Nome);
      $objProcedimentoOuvidoriaDTO->setStrEmail($Email);
      $objProcedimentoOuvidoriaDTO->setDblCpf($Cpf);
      $objProcedimentoOuvidoriaDTO->setDblRg($Rg);
      $objProcedimentoOuvidoriaDTO->setStrOrgaoExpedidor($OrgaoExpedidor);
      $objProcedimentoOuvidoriaDTO->setStrTelefone($Telefone);
      $objProcedimentoOuvidoriaDTO->setStrEstado($Estado);
      $objProcedimentoOuvidoriaDTO->setStrCidade($Cidade);
      $objProcedimentoOuvidoriaDTO->setNumIdTipoProcedimento($IdTipoProcedimento);
      $objProcedimentoOuvidoriaDTO->setStrProcessos($Processos);
      $objProcedimentoOuvidoriaDTO->setStrSinRetorno($SinRetorno);
      $objProcedimentoOuvidoriaDTO->setStrMensagem($Mensagem);

      $arrAtributos = array();
      if (is_array($AtributosAdicionais)){
        foreach($AtributosAdicionais as $atributo){
          $arrAtributos[] = (array)$atributo;
        }
      }

      $objProcedimentoOuvidoriaDTO->setArrAtributosAdicionais($arrAtributos);
      $objProcedimentoOuvidoriaDTO->setDblIdProcedimentoOrigem($IdProcedimentoOrigem);

      FeedSEIProtocolos::getInstance()->setBolAcumularFeeds(true);

      $objOuvidoriaRN = new OuvidoriaRN();
      $objProcedimentoDTO = $objOuvidoriaRN->registrarOuvidoriaRN1148($objProcedimentoOuvidoriaDTO);

      FeedSEIProtocolos::getInstance()->setBolAcumularFeeds(false);
      FeedSEIProtocolos::getInstance()->indexarFeeds();

      //LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());

      if (trim($IdProcedimentoOrigem)=='') {
        return $objProcedimentoDTO->getStrProtocoloProcedimentoFormatado();
      }else{
        return $objProcedimentoDTO->getDblIdProcedimento().'|'.$objProcedimentoDTO->getStrProtocoloProcedimentoFormatado();
      }

    }catch(Exception $e){
      //LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());

      $this->processarExcecao($e);
    }
  }
}

$servidorSoap = new SoapServer("ouvidoria.wsdl",array('encoding'=>'ISO-8859-1'));

$servidorSoap->setClass("OuvidoriaWS");

//Só processa se acessado via POST
if ($_SERVER['REQUEST_METHOD']=='POST') {
  $servidorSoap->handle();
} 
	
