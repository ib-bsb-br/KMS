<?
  require_once dirname(__FILE__).'/../SEI.php';
  
  class PaginaPublicacoes extends InfraPaginaEsquema2 {
    
    private static $instance = null;
    private static $strMenu = null;
    
    public static function getInstance() {
      if (self::$instance == null) {
        self::$instance = new PaginaPublicacoes();
      }
      return self::$instance;
    }
    
    public function __construct(){
      SeiINT::validarHttps();
      parent::__construct();
    }
    
    public function getStrNomeSistema() {
      return 'Publicações Eletrônicas';
    }
    
    public function isBolProducao() {
      return ConfiguracaoSEI::getInstance()->getValor('SEI','Producao');
    }

    public function getNumVersao(){
      return str_replace(' ','-',SEI_VERSAO . '-' . parent::getNumVersao());
    }

    public function validarHashTabelas(){
      return true;
    }

    public function getStrEsquemaCores(){
      return 'azul_celeste';
    }
    
    public function getStrMenuSistema() {

      if (self::$strMenu === null) {

        global $SEI_MODULOS;

        $arrMenu = array();

        foreach ($SEI_MODULOS as $seiModulo) {
          if (($arrMenuIntegracao = $seiModulo->executar('montarMenuPublicacoes')) != null) {
            foreach ($arrMenuIntegracao as $strMenuIntegracao) {
              $arrMenu[] = $strMenuIntegracao;
            }
          }
        }

        if (count($arrMenu)) {
          self::$strMenu = parent::montarSmartMenuArray($arrMenu);
        }
      }

      return self::$strMenu;
    }
    
    public function getArrStrAcoesSistema() {
      $arrStrAcoes = null;
      if ($this->getStrMenuSistema()!=null) {
        $arrStrAcoes = array();
        $arrStrAcoes[] = '<a id="lnkInfraMenuSistema" href="#" target="_self" onclick="infraMenuSistemaEsquema();" title="Exibir/Ocultar Menu do Sistema" tabindex="' . $this->getProxTabBarraSistema() . '" style="font-size:1.3em;padding-right:.2em;">Menu</a>';
      }
      return $arrStrAcoes;
    }
    
    public function permitirXHTML() {
			return false;
		}

    public function adicionarJQuery(){
      return true;
    }

    public function obterTipoMenu(){
      return self::$MENU_SMART;
    }

    public function getObjInfraSessao() {
      return SessaoPublicacoes::getInstance();
    }
    
    public function getObjInfraLog(){
	    return LogSEI::getInstance();
	  }
        
    public function montarLinkMenu(){
  	  return '';
  	}    
  	
  	public function getStrLogoSistema(){
	  return '<img src="../imagens/sei_logo_'.$this->getStrEsquemaCores().'.jpg" title="Sistema Eletrônico de Informações - Versão '.SEI_VERSAO.'"/>';
	}
  	  	
  	public function abrirHead($strAtributos=''){
  	  parent::abrirHead($strAtributos);
  	  echo '<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />'."\n";
  	}

  	public function getStrTextoBarraSuperior(){
  	  try{
  	
  	    $strDescricaoOrgao = '';
  	
  	    if (isset($_GET['id_orgao_publicacao'])){
  	
  	      $objOrgaoDTO = new OrgaoDTO();
  	      $objOrgaoDTO->retStrDescricao();
  	      $objOrgaoDTO->setNumIdOrgao($_GET['id_orgao_publicacao']);
  	       
  	      $objOrgaoRN = new OrgaoRN();
  	      $objOrgaoDTO = $objOrgaoRN->consultarRN1352($objOrgaoDTO);
  	       
  	      if ($objOrgaoDTO!=null){
  	        $strDescricaoOrgao = $objOrgaoDTO->getStrDescricao();
  	      }
  	    }
  	
  	    return $strDescricaoOrgao;
  	    	
  	  }catch(Exception $e){
  	    LogSEI::getInstance()->gravar('Erro montando página de publicação: '.$e->__toString()."\n".$e->getTraceAsString());
  	  }
  	  return null;
  	}  	
  	
	  public function getDiretorioCssLocal(){
		  return '../css';
	  }
  }
?>