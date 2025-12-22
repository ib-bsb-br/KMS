<?
/*
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 * 
 * 12/11/2007 - criado por MGA
 *
 */
 
 require_once dirname(__FILE__).'/SEI.php';
 
 class PaginaSEI extends InfraPaginaEsquema2
 {
   private static $instance = null;
   private $bolArvore = false;
   private $bolJQuery = true;
   private $bolD3 = false;
   private static $strMenu = null;

   public static function getInstance()
   {
     if (self::$instance == null) {
       self::$instance = new PaginaSEI();
     }
     return self::$instance;
   }

   public function __construct()
   {
     SeiINT::validarHttps();
     parent::__construct();
   }

   public function getStrNomeSistema()
   {
     return ConfiguracaoSEI::getInstance()->getValor('PaginaSEI', 'NomeSistema');
   }

   public function isBolProducao()
   {
     return ConfiguracaoSEI::getInstance()->getValor('SEI', 'Producao');
   }

   public function validarHashTabelas(){
     return true;
   }

   public function getStrLogoSistema(){
     $strRet = '<img src="imagens/sei_logo_' . $this->getStrEsquemaCores() . '.jpg" title="Sistema Eletrônico de Informações - Versão ' . SEI_VERSAO . '"/>';
     if (($strComplemento = ConfiguracaoSEI::getInstance()->getValor('PaginaSEI', 'NomeSistemaComplemento',false))!=null){
       $strRet .= '<span class="infraTituloLogoSistema">'.$strComplemento.'</span>';
     }
     return $strRet;
   }

   public function getStrMenuSistema()
   {
     global $SEI_MODULOS;

     if (self::$strMenu === null) {
       $strMenu = parent::montarMenuSessao('Principal');

       if (($strLogo = ConfiguracaoSEI::getInstance()->getValor('PaginaSEI', 'LogoMenu', false)) != null) {
         $strMenu .= $strLogo;
       }

       foreach($SEI_MODULOS as $objModulo){
         if (($strMenuModulo = $objModulo->executar('adicionarElementoMenu', $_GET['acao']))!=null){
           $strMenu .= $strMenuModulo;
         }
       }

       self::$strMenu = $strMenu;
     }

     return self::$strMenu;
   }

   public function getArrStrAcoesSistema()
   {

     $arrStrAcoes = array();
     $strAcoesIcones = '';

     //$arrStrAcoes[] = parent::montarIdentificacaoUsuario();

     if (SessaoSEI::getInstance()->verificarPermissao('para_saber_mais') && !$this->isBolIpad() && !$this->isBolIphone() && !$this->isBolAndroid()) {
       $arrStrAcoes[] = '<a id="lnkAjuda" href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=para_saber_mais') . '" title="Manual do SEI em vídeo" tabindex="' . $this->getProxTabBarraSistema() . '" style="font-size:1.3em;padding-right:.2em;">Para saber+</a>';
     }

     $arrStrAcoes[] = '<a id="lnkInfraMenuSistema" href="#" target="_self" onclick="infraMenuSistemaEsquema();" title="Exibir/Ocultar Menu do Sistema" tabindex="' . $this->getProxTabBarraSistema() . '" style="font-size:1.3em;padding-right:.2em;">Menu</a>';


     if (SessaoSEI::getInstance()->verificarPermissao('protocolo_pesquisa_rapida')) {

       $arrStrAcoes[] = '<a id="lnkPesquisaRapida" href="javascript:void(0);" onclick="document.getElementById(\'txtPesquisaRapida\').focus();" title="Pesquisa" tabindex="' . $this->getProxTabBarraSistema() . '" style="font-size:1.3em;padding-right:.1em;">Pesquisa</a>';

       $arrStrAcoes[] = '<span style="display:inline-block;padding-bottom:.6em;">' .
           '<form id="frmProtocoloPesquisaRapida" method="post" action="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=protocolo_pesquisa_rapida') . '" style="display:inline;">' .
           '<input type="text" id="txtPesquisaRapida" name="txtPesquisaRapida" value="" tabindex="' . $this->getProxTabBarraSistema() . '" style="width:13em;font-size:1.2em;" />' .
           '</form>' .
           '</span>';
     }

     $arrStrAcoes[] = parent::montarSelectUnidades();

     //$strAcoesIcones = '<div style="float:right;padding-top:.3em;padding-right:.8em;">';

     if (SessaoSEI::getInstance()->verificarPermissao('procedimento_controlar')) {
       $strAncora = '';
       if (isset($_GET['acao_origem']) && $_GET['acao_origem']=='procedimento_controlar' && isset($_GET['id_procedimento'])){
         $strAncora = self::montarAncora($_GET['id_procedimento']);
       }
       $strAcoesIcones .= '<a id="lnkControleProcessos" href="#" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_controlar'.$strAncora) . '\'" title="Controle de Processos" tabindex="' . $this->getProxTabBarraSistema() . '"><img src="imagens/sei_controle_processos_barra.gif" title="Controle de Processos" alt="Controle de Processos" class="infraImg" /></a>&nbsp;&nbsp;';
     }

     if (SessaoSEI::getInstance()->verificarPermissao('novidade_mostrar')) {
       $strAcoesIcones .= '<a id="lnkNovidades" target="_blank" href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=novidade_mostrar&mostrar_todas=1') . '" title="Novidades" tabindex="' . $this->getProxTabBarraSistema() . '"><img src="imagens/sei_novidades.gif" title="Novidades" alt="Novidades" class="infraImg" /></a>&nbsp;&nbsp;';
     }

     $strAcoesIcones .= $this->montarLinkUsuario() . '&nbsp;&nbsp;';
     $strAcoesIcones .= parent::montarLinkConfiguracao() . '&nbsp;&nbsp;';
     //$strAcoesIcones .= parent::montarLinkAjuda($this->strLinkAjuda);
     $strAcoesIcones .= parent::montarLinkSair(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=sair'));

     //$strAcoesIcones .= '</div>';

     $arrStrAcoes[] = $strAcoesIcones;


     return $arrStrAcoes;
   }

   public function getObjInfraSessao()
   {
     return SessaoSEI::getInstance();
   }

   public function getObjInfraLog()
   {
     return LogSEI::getInstance();
     //return null;
   }

   public function setBolArvore($bolArvore)
   {
     $this->bolArvore = $bolArvore;
     if ($this->bolArvore) {
       $this->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);
     }
   }

   public function isBolArvore()
   {
     return $this->bolArvore;
   }

   public function abrirHead($strAtributos = '')
   {
     parent::abrirHead($strAtributos);
     echo '<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />' . "\n";
   }

   public function montarLinkMenu()
   {
     return '';
   }

   public function montarBotaoVoltarExcecao()
   {
     if ($this->isBolArvore()) {
       return '';
     } else {
       return parent::montarBotaoVoltarExcecao();
     }
   }

   public function montarBotaoFecharExcecao()
   {
     if ($this->isBolArvore()) {
       return '';
     } else {
       return parent::montarBotaoFecharExcecao();
     }
   }

   public function montarJavaScript(){
     parent::montarJavaScript();
     echo '<script type="text/javascript" charset="iso-8859-1" src="js/sei.js?' . $this->getNumVersao() . '"></script>'."\n";
   }

   public function obterTiposMensagemExibicao()
   {
     return self::$TIPO_MSG_AVISO | self::$TIPO_MSG_ERRO;
   }

   public function getNumVersao(){
     return str_replace(' ','-',SEI_VERSAO . '-'.parent::getNumVersao());
   }

   public function setBolJQuery($bolJQuery){
     $this->bolJQuery = $bolJQuery;
   }

   public function adicionarJQuery(){
     return $this->bolJQuery;
   }

   public function setBolD3($bolD3){
     $this->bolD3 = $bolD3;
   }

   public function adicionarD3(){
     return $this->bolD3;
   }

   /*
   public function getDiretorioJavaScriptGlobal(){
     return '/infra/infra_js';
   }

   public function getDiretorioEsquemas(){
     return '/infra/infra_css/esquemas';
   }

   public function getDiretorioCssGlobal(){
     return '/infra/infra_css';
   }
   */

   public function obterTipoMenu(){
     return self::$MENU_SMART;
   }

   /*
   public function obterSmartMenuClass(){
     return 'clean';
   }
   */

   public function getStrTextoBarraSuperior(){
     $strOrgaoTopo = ConfiguracaoSEI::getInstance()->getValor('PaginaSEI','OrgaoTopoJanela',false,'S');
     if ($strOrgaoTopo=='S') {
       return $this->getObjInfraSessao()->getStrDescricaoOrgaoSistema();
     }else if ($strOrgaoTopo=='U') {
       return $this->getObjInfraSessao()->getStrDescricaoOrgaoUsuario();
     }
     return null;
   }

   public function permitirXHTML(){
     return false;
   }

   public static function montarTitleTooltip($strTexto, $strTitulo = ''){

     $ret = '';

     if (SessaoSEI::getInstance()->getStrSinAcessibilidade()=='S'){
       if ($strTitulo!='' && $strTexto!=''){
         $ret = 'title="'.str_replace("\n",'&#13;',self::tratarHTML($strTitulo)).'&#13;'.str_replace("\n",'&#13;',self::tratarHTML($strTexto)).'" ';
       }else if ($strTitulo!=''){
         $ret = 'title="'.str_replace("\n",'&#13;',self::tratarHTML($strTitulo)).'" ';
       }else if ($strTexto!=''){
         $ret = 'title="'.str_replace("\n",'&#13;',self::tratarHTML($strTexto)).'" ';
       }
     }

     if ($strTitulo!=''){
       $ret .= 'onmouseover="return infraTooltipMostrar(\''.self::tratarHTML(self::formatarParametrosJavaScript($strTexto)).'\',\''.self::tratarHTML(self::formatarParametrosJavaScript($strTitulo)) . '\');"';
     }else{
       $ret .= 'onmouseover="return infraTooltipMostrar(\''.self::tratarHTML(self::formatarParametrosJavaScript($strTexto)).'\');"';
     }

     $ret .= ' onmouseout="return infraTooltipOcultar();"';

     return $ret;
   }

 }
?>