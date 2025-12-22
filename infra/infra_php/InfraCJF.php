<?
/**
* Classe de acesso ao WebService da TJ-RS
*
* criado em 30/04/2008 - mauro_db
* alterado em 19/09/2008 - mauro_db
*             novo link do servidor
* alterado em 20/05/2009 - mauro_db
*             incluída a opção de criar um objeto cliente e passá-lo como parâmetro
*             para otimizar consultas múltiplas
* 
* Observações:
* 1 - habilitado somente nos servidores "bohr" e "krebs"
* 2 - em todos os métodos é obrigatório: usuarioTRF4, senhaTRF4 e sigla do usuário
* 3 - número do processo atual: CCC/S.AA.NNNNNNN-D
*     onde: 
*     CCC - Código da comarca (tabela no banco siapro "comarca"
*     S - seção do processo (1-processos cíveis, 2-processos criminais e JECrime, 3-processos JECível)
*     AA - ano de cadastramento do processo
*     NNNNNNN - número sequencial dentro do ano e seção
*     D - dígito de controle
*/

/*
class SoapClientX extends SoapClient{
  function __doRequest($request, $location, $action, $version, $one_way = null) {
     $response = parent::__doRequest($request, $location, $action, $version);
     InfraDebug::getInstance()->gravar($request);
     InfraDebug::getInstance()->gravar($response);
     return $response;
  }
}
*/

class InfraCJF {
  
  public static $CPF = 0;
  public static $CNPJ = 1;
  public static $CPF_POR_NOME = 2;
  public static $OAB_POR_CPF = 3;
  public static $OAB_POR_NOME_OAB = 4;
	    

  //private static $strLinkWs = 'http://172.3.31.215:7778/'; //novo - erro de acesso
  //private static $strLinkWs = 'http://172.31.3.220:7778/';
  //private static $strLinkWs = 'http://172.31.3.224:8080/';
  //private static $strLinkWs = 'http://187.115.83.180:7778/'; //alterado temporariamente, depois voltou para 172.31.....
  
  //private static $strLinkWs = 'http://172.31.3.224:8080/';
  //private static $strLinkWs = 'http://172.31.3.226:8080/';
  //private static $strLinkWs = 'http://172.31.3.224:8080/';
  private static $strLinkWs = 'http://consultacpfcnpj.cjf.jus.br/';
  
	private function __construct() {
  }
  
  public static function getStrLinkWs() {
   return self::$strLinkWs;
  }   

  /**
   * Retorna um objeto SoapClient
   *
   * @param string $strTipo
   * @return objeto SoapClient
   */
  public static function gerarCliente($strTipo) {
    try {
      switch ($strTipo) {
        case self::$CPF: 
          $ws = self::getStrLinkWs().'wsReceitaApp/wsConsultaCPFService?wsdl';
          break;
          
        case self::$CNPJ: 
          $ws = self::getStrLinkWs().'wsReceitaApp/wsConsultaCNPJService?wsdl';
          break;
          
        case self::$CPF_POR_NOME: 
          $ws = self::getStrLinkWs().'wsReceitaApp/wsConsultaNomeCPFService?wsdl';
          break;
          
        case self::$OAB_POR_CPF:
        case self::$OAB_POR_NOME_OAB;   
          $ws = 'http://172.31.3.220:7778/wsConsultaOAB/wsConsultaOABSoapHttpPort?WSDL';
          break;
          
        default:
          throw new SoapFault('Server', 'Tipo de cliente inválido: ' . $strTipo);
      }
      
      if(!@file_get_contents($ws)) {
         throw new SoapFault('Server', 'WSDL não encontrado em:' . $ws);
      }
      //return new SoapClient($ws, array('style'=> "mime", 'style' => SOAP_RPC, 'use' => SOAP_ENCODED, 'location' => $ws));
      return new SoapClient($ws);
    } catch (SoapFault $soapFault) {
      return null;
    }
  }
  
  /**
   * Consulta dados pelo CPF
   *
   * @param string $cpf
   * @param string $orgao
   * @param string $sistema
   * @param string $usuario
   * @param SoapClient $wsCliente
   * @return array
   */
  public static function consultarDadosCPF($cpf,$orgao,$sistema,$usuario, $wsCliente=null, $numTentativas = 3) {

    while($numTentativas--) {
      try {
        if ($wsCliente == null) {
          $wsCliente = self::gerarCliente(self::$CPF);
        }

        $respostaWS = $wsCliente->getDadosCPFSecurity(array("pNumCPF" => $cpf, "pNomeOrgao" => $orgao, "pLoginUsuario" => $usuario, "pNomeAplicacao" => $sistema));
      } catch (SoapFault $soapFault) {
        return false;
      }

      $retorno = explode(";", $respostaWS->return);

      if (count($retorno) > 1) {
        break;
      }
    }
    return $retorno;
  }

  /**
   * Consulta dados pelo CNPJ
   *
   * @param string $cnpj
   * @param string $orgao
   * @param string $sistema
   * @param string $usuario
   * @param SoapClient $wsCliente
   * @return array
   */
  public static function consultarDadosCNPJ($cnpj,$orgao,$sistema,$usuario, $wsCliente=null, $numTentativas = 3) {

    while($numTentativas--) {
      try {
        if ($wsCliente == null) {
          $wsCliente = self::gerarCliente(self::$CNPJ);
        }

        $respostaWS = $wsCliente->getDadosCNPJSecurity(array("pNumCNPJ" => $cnpj, "pNomeOrgao" => $orgao, "pLoginUsuario" => $usuario, "pNomeAplicacao" => $sistema));
      } catch (SoapFault $soapFault) {
        return false;
      }
      $retorno = explode(";", $respostaWS->return);

      if (count($retorno) > 1) {
        break;
      }
    }
    return $retorno;
  }
  
  public static function consultarDadosCPFPorNome($nome,$orgao,$sistema,$usuario,$uf='',$tipo=1, $wsCliente=null, $numTentativas = 3) {

    while($numTentativas--) {
      try {
        $nome = strtoupper(InfraString::excluirAcentos(trim($nome)));
        if ($wsCliente == null) {
          $wsCliente = self::gerarCliente(self::$CPF_POR_NOME);
        }

        $respostaWS = $wsCliente->getDadosCPFSecurity($nome, $orgao, $usuario, $sistema, $uf, $tipo);

        $ret = array();
        $itens = $respostaWS->stringArray;
        if (is_array($itens)) {
          foreach ($itens as $dados) {
            $ret[] = explode(";", $dados);
          }
        } else {
          $ret[] = explode(";", $itens);
        }

      } catch (SoapFault $soapFault) {
        throw new InfraException($soapFault->__toString());
        return false;
      }

      $bolFalha = false;
      foreach($ret as $item) {
        if (count($item) == 1){
          $bolFalha = true;
          break;
        }
      }

      if (!$bolFalha){
        break;
      }

    }
    return $ret;
  }
  

  private static function converterXmlArray($strXml) {
    
    //return array($strXml);
    
    $ret = array();

    if ($strXml=='<NewDataSet />'){
      return $ret;
    }
    
    $objXml = new SimpleXMLElement($strXml);
    
    $ret[] = (string) $objXml->Table[0]->NumeroSeguranca;
    $ret[] = (string) $objXml->Table[0]->Uf;
    $ret[] = (string) $objXml->Table[0]->Organizacao;
    $ret[] = (string) $objXml->Table[0]->Nome;
    $ret[] = (string) $objXml->Table[0]->NomePai;
    $ret[] = (string) $objXml->Table[0]->NomeMae;
    $ret[] = (string) $objXml->Table[0]->Inscricao;
    $ret[] = (string) $objXml->Table[0]->Cpf;
    $ret[] = (string) $objXml->Table[0]->TipoInscricao;
    $ret[] = (string) $objXml->Table[0]->Situacao;
    $ret[] = (string) $objXml->Table[0]->Logradouro;
    $ret[] = (string) $objXml->Table[0]->Bairro;
    $ret[] = (string) $objXml->Table[0]->Cidade;
    $ret[] = (string) $objXml->Table[0]->Cep;
    $ret[] = (string) $objXml->Table[0]->DDD;
    $ret[] = (string) $objXml->Table[0]->Telefone;
    return $ret;
  }

  /**
   * Consulta dados da OAB pelo CPF
   *
   * @param string $cpf
   * @param string $orgao
   * @param string $sistema
   * @param string $usuario
   * @return array
   */
  public static function consultarDadosOABPorCPF($cpf, $orgao, $sistema, $usuario, $wsCliente=null) {
    try {
      if ($wsCliente == null) { 
        $wsCliente = self::gerarCliente(self::$OAB_POR_CPF); 
      }
      
      $respostaWS = $wsCliente->getDadosOAB1(array("pNumCpf" => $cpf, "pNomeOrgao" => $orgao,  "pLoginUsuario" => $usuario, "pNomeAplicacao" => $sistema));
      return self::converterXmlArray($respostaWS->return);
    } catch (SoapFault $soapFault) {
      return false;
    }
  }

  /**
   * Consulta dados da OAB pelo número de inscrição e a UF,
   * ou pelo número de inscrição e o nome do profissional
   *
   * @param string $oab
   * @param string $uf
   * @param string $nome
   * @param string $orgao
   * @param string $sistema
   * @param string $usuario
   * @return array
   */
  public static function consultarDadosOABPorNomeOab($oab, $uf='', $nome='', $orgao, $sistema, $usuario, $wsCliente=null) {
    try {
      if (($uf=='') && ($nome=='')) {
        throw new InfraException('Deve ser informado a UF do número da OAB, ou o Nome, para a consulta.');
      }
      if ($wsCliente == null) { 
        $wsCliente = self::gerarCliente(self::$OAB_POR_NOME_OAB); 
      }
      
      $respostaWS = $wsCliente->getDadosOAB2(array("pNumInsc" => $oab, "pUf" => $uf, "pNome" => $nome, "pNomeOrgao" => $orgao,  "pLoginUsuario" => $usuario, "pNomeAplicacao" => $sistema));
      return self::converterXmlArray($respostaWS->return);
    } catch (SoapFault $soapFault) {
      return false;
    }
  }
}
?>