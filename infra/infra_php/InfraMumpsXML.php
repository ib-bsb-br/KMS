<?
  /**
  * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
  * 21/03/2007 - CRIADO POR cle@trf4.gov.br
  * @package infra_php
  */
  class InfraMumpsXML {
   	private static $instance = null;
  	private $bolFlagSessao = null;
    private $objParser = '';
    private $arrResultado = array();
    private $bolValor = false;
    private $numLinha = -1;
    private $numColuna = -1;
    private $arrCaracteresEspeciaisHTML = array('<', '>', '&', "'", '"');
    private $arrCaracteresEspeciaisCodigo = array('&lt;', '&gt;', '&amp;', '&apos;', '&quot;');
    private $arrCampos = array();
 	 	public static function getInstance() {
	    if (self::$instance == null) {
        self::$instance = new InfraMumpsXML();
	    } 
	    return self::$instance;
  	}
	  private function __construct() {
  		//$this->bolFlagSessao = true;
  	}
    //CONTROLA A MONTAGEM DO VETOR COM BASE NA TAG DE ABERTURA
    function tagAbrir($objParser, $strName, $attrs) {
      if ($strName == 'REGISTRO') {
				$this->numLinha++;
				$this->numColuna = -1;
      }
      if (substr($strName,0,5) == 'CAMPO') {
				$this->bolValor = true;
				$this->numColuna++;
      }
    }
    //QUANDO A TAG FECHA, PÁRA DE CONCATENAR OS DADOS ACENTUADOS
    function tagFechar($objParser, $strName) {
      if (substr($strName,0,5) == 'CAMPO') {
        $this->bolValor = false;
      }
    }
    //ADICIONA O ITEM AO VETOR QUANDO HÁ DADOS
    function adicionarItem($objParser, $strDados) {
      if (($this->numLinha == 1) && (trim($strDados) != '')) {
        $this->arrCampos[] = $strDados;
      }
      /*QUANDO O XML TRANSPORTA MAIS DE 1024 CARACTERES EM UMA TAG, O PARSER "SPLITA" OS DADOS EM VÁRIOS BLOCOS DESSE TAMANHO. 
      POR ESTE MOTIVO, A ADIÇÃO DE VALOR (adicionarItem) PASSOU A CONCATENAR OS DADOS (AO INVÉS DE USAR O tagAbrir E MARCAR true O 
      ATRIBUTO $this->valor; O MESMO PARA O INCREMENTO DO ATRIBUTO $this->coluna).*/
      if (($this->bolValor) && ($this->numLinha > 1)) {
        //OS DOIS PRIMEIROS REGISTROS SÃO O true/false E OS NOMES DAS COLUNAS
        if (!isset($this->arrResultado[$this->numLinha-2])) {
          $this->arrResultado[$this->numLinha-2] = array();
        }
        //InfraDebug::getInstance()->gravarInfra('[InfraMumpsXML->adicionarItem] : '.$this->arrCampos[$this->numColuna]);
				$this->arrResultado[$this->numLinha-2][$this->arrCampos[$this->numColuna]] .= $strDados;
      }
    }
    //EXECUTA O PARSE DOS DADOS XML
    function vetorizar($strDados) {
      $this->objParser = xml_parser_create();
      xml_set_object($this->objParser, $this);
      xml_parser_set_option($this->objParser, XML_OPTION_SKIP_WHITE, true);
      xml_parser_set_option($this->objParser, XML_OPTION_CASE_FOLDING, true);
      xml_parser_set_option($this->objParser, XML_OPTION_TARGET_ENCODING, 'ISO-8859-1');
      xml_set_element_handler($this->objParser, 'tagAbrir', 'tagFechar');
      xml_set_character_data_handler($this->objParser, 'adicionarItem');
      if (!xml_parse($this->objParser, $strDados)) {
        
        throw new InfraException('Erro na interpretação do XML: '.xml_get_error_code($this->objParser).': '.
                                 xml_error_string(xml_get_error_code($this->objParser)).
                                 ' (linha '.xml_get_current_line_number($this->objParser).')');
      }
      xml_parser_free($this->objParser);
      $arrResultado = $this->arrResultado;
      $this->reinicializarPropriedades();
      return $this->substituirCaracteresEspeciais($arrResultado);
    }
    //SUBSTITUI OS CARACTERES ESPECIAIS POR SEUS CÓDIGOS
    function substituirCaracteresEspeciais2($strDados) {
      for ($i=0; $i<count($this->minusculas_acentuadas); $i++) {
				$dados = str_replace($this->minusculas_acentuadas[$i], $this->minusculas[$i], $strDados);
      }
      return $dados;
    }
    function substituirCaracteresEspeciais($arrResultado) {
      for ($numLinha=2; $numLinha<count($arrResultado); $numLinha++) {
				for ($numColuna=0; $numColuna<=$this->numColuna; $numColuna++) {
					for ($i=0; $i<count($this->arrCaracteresEspeciaisHTML); $i++) {
						$arrResultado[$numLinha][$numColuna] = str_replace($this->arrCaracteresEspeciaisCodigo[$i], $this->arrCaracteresEspeciaisHTML[$i], $arrResultado[$numLinha][$numColuna]);
					}
				}
      }
			$this->reinicializarPropriedades();
      return $arrResultado;
    }
    //MOSTRA EM HTML OS DADOS TRANSPORTADOS PELO XML
    function mostrarResultado($arrResultado_resultado) {
      for ($numLinha=0; $numLinha<count($arrResultado_resultado); $numLinha++) {
				for ($numColuna=0; $numColuna<count($arrResultado_resultado[$numLinha]); $numColuna++) {
          echo $arrResultado_resultado[$numLinha][$numColuna].'<br/>';
				}
      }
    }
    //MOSTRA O XML COMO ESTÁ SENDO RECEBIDO
    function mostrarXML($xml) {
      echo '<html><body><center><textarea name=\'textareaXML\' cols=\'100\' rows=\'50\'>'.$xml.'</textarea></center></body></html>';
    }
		function reinicializarPropriedades() {
		  unset($this->arrResultado);
      unset($this->arrCampos);
	    $this->numLinha = -1;
      $this->numColuna = -1;
		}
  }
?>