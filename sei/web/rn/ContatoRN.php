<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 09/01/2008 - criado por marcio_db
*
* Versão do Gerador de Código: 1.12.0
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class ContatoRN extends InfraRN {

  public static $TN_PESSOA_FISICA = 'F';
  public static $TN_PESSOA_JURIDICA = 'J';

  public static $TG_MASCULINO = 'M';
  public static $TG_FEMININO = 'F';

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }

  protected function cadastrarRN0322Controlado(ContatoDTO $objContatoDTO) {
    try{

      global $SEI_MODULOS;

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('contato_cadastrar',__METHOD__,$objContatoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarNumIdTipoContatoRN0367($objContatoDTO, $objInfraException);
      $this->validarNumIdContatoAssociadoRN0729($objContatoDTO, $objInfraException);
      $this->validarStrStaNatureza($objContatoDTO, $objInfraException);
      $this->validarStrSinEnderecoAssociadoRN0894($objContatoDTO, $objInfraException);
      $this->validarStrStaGeneroRN0433($objContatoDTO, $objInfraException);
      $this->validarDblCpfRN0435($objContatoDTO, $objInfraException);
      $this->validarDblRg($objContatoDTO, $objInfraException);
      $this->validarStrOrgaoExpedidor($objContatoDTO, $objInfraException);
      $this->validarStrMatriculaRN0436($objContatoDTO, $objInfraException);
      $this->validarStrMatriculaOabRN0434($objContatoDTO, $objInfraException);
      $this->validarDtaNascimentoRN0569($objContatoDTO, $objInfraException);
      $this->validarDblCnpjRN0372($objContatoDTO, $objInfraException);
      $this->validarNumIdCargoRN0427($objContatoDTO, $objInfraException);
      $this->validarStrSiglaRN0430($objContatoDTO, $objInfraException);
      $this->validarStrNomeRN0431($objContatoDTO, $objInfraException);
      $this->validarStrTelefoneFixoRN0437($objContatoDTO, $objInfraException);
      $this->validarStrTelefoneCelular($objContatoDTO, $objInfraException);
      $this->validarStrEmailRN0439($objContatoDTO, $objInfraException);
      $this->validarStrSitioInternetRN0440($objContatoDTO, $objInfraException);
      $this->validarStrEnderecoRN0441($objContatoDTO, $objInfraException);
      $this->validarStrComplemento($objContatoDTO, $objInfraException);
      $this->validarStrBairroRN0442($objContatoDTO, $objInfraException);
      $this->validarStrCepRN0446($objContatoDTO, $objInfraException);
      $this->validarStrObservacaoRN0447($objContatoDTO, $objInfraException);
      $this->validarStrSinAtivoRN0449($objContatoDTO, $objInfraException);

      $this->validarNumIdPais($objContatoDTO, $objInfraException);
      $this->validarNumIdUf($objContatoDTO, $objInfraException);
      $this->validarNumIdCidade($objContatoDTO, $objInfraException);

      
      //$this->validarSiglaNomeUnicosRN1221($objContatoDTO, $objInfraException);

      $objInfraException->lancarValidacoes();

      $objTipoContatoDTO = new TipoContatoDTO();
      $objTipoContatoDTO->setBolExclusaoLogica(false);
      $objTipoContatoDTO->retStrSinSistema();
      $objTipoContatoDTO->setNumIdTipoContato($objContatoDTO->getNumIdTipoContato());

      $objTipoContatoRN = new TipoContatoRN();
      $objTipoContatoDTO = $objTipoContatoRN->consultarRN0336($objTipoContatoDTO);

      if ($objTipoContatoDTO==null){
        $objInfraException->lancarValidacao('Tipo do contato não encontrado.');
      }

      if ($objTipoContatoDTO->getStrSinSistema()=='S' && (!$objContatoDTO->isSetStrStaOperacao() || $objContatoDTO->getStrStaOperacao()!='REPLICACAO')){
        $objInfraException->lancarValidacao('Não é possível cadastrar o contato em um tipo reservado do sistema.');
      }

      $numProxSeq = $this->getObjInfraIBanco()->getValorSequencia('seq_contato');
      
      $objContatoDTO->setNumIdContato($numProxSeq);

      if ($objContatoDTO->getNumIdContatoAssociado()==null){
        $objContatoDTO->setNumIdContatoAssociado($numProxSeq);
      }

      $objContatoDTO->setStrIdxContato(null);

      $objContatoDTO->setNumIdUsuarioCadastro(SessaoSEI::getInstance()->getNumIdUsuario());
      $objContatoDTO->setNumIdUnidadeCadastro(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      $objContatoDTO->setDthCadastro(InfraData::getStrDataHoraAtual());
      
      $objContatoBD = new ContatoBD($this->getObjInfraIBanco());
      $ret = $objContatoBD->cadastrar($objContatoDTO);

      $this->montarIndexacaoRN0450($objContatoDTO);

      $objContatoAPI = new ContatoAPI();
      $objContatoAPI->setIdContato($objContatoDTO->getNumIdContato());
      $objContatoAPI->setIdTipoContato($objContatoDTO->getNumIdTipoContato());
      $objContatoAPI->setIdContatoAssociado($objContatoDTO->getNumIdContatoAssociado()!=$objContatoDTO->getNumIdContato()?$objContatoDTO->getNumIdContatoAssociado():null);
      $objContatoAPI->setStaNatureza($objContatoDTO->getStrStaNatureza());
      $objContatoAPI->setSinEnderecoAssociado($objContatoDTO->getStrSinEnderecoAssociado());
      $objContatoAPI->setStaGenero($objContatoDTO->getStrStaGenero());
      $objContatoAPI->setCpf($objContatoDTO->getDblCpf());
      $objContatoAPI->setRg($objContatoDTO->getDblRg());
      $objContatoAPI->setOrgaoExpedidor($objContatoDTO->getStrOrgaoExpedidor());
      $objContatoAPI->setMatricula($objContatoDTO->getStrMatricula());
      $objContatoAPI->setMatriculaOab($objContatoDTO->getStrMatriculaOab());
      $objContatoAPI->setDataNascimento($objContatoDTO->getDtaNascimento());
      $objContatoAPI->setCnpj($objContatoDTO->getDblCnpj());
      $objContatoAPI->setIdCargo($objContatoDTO->getNumIdCargo());
      $objContatoAPI->setSigla($objContatoDTO->getStrSigla());
      $objContatoAPI->setNome($objContatoDTO->getStrNome());
      $objContatoAPI->setTelefoneFixo($objContatoDTO->getStrTelefoneFixo());
      $objContatoAPI->setTelefoneCelular($objContatoDTO->getStrTelefoneCelular());
      $objContatoAPI->setEmail($objContatoDTO->getStrEmail());
      $objContatoAPI->setSitioInternet($objContatoDTO->getStrSitioInternet());
      $objContatoAPI->setEndereco($objContatoDTO->getStrEndereco());
      $objContatoAPI->setComplemento($objContatoDTO->getStrComplemento());
      $objContatoAPI->setBairro($objContatoDTO->getStrBairro());
      $objContatoAPI->setCep($objContatoDTO->getStrCep());
      $objContatoAPI->setObservacao($objContatoDTO->getStrObservacao());
      $objContatoAPI->setSinAtivo($objContatoDTO->getStrSinAtivo());
      $objContatoAPI->setIdPais($objContatoDTO->getNumIdPais());
      $objContatoAPI->setIdEstado($objContatoDTO->getNumIdUf());
      $objContatoAPI->setIdCidade($objContatoDTO->getNumIdCidade());

      foreach ($SEI_MODULOS as $seiModulo) {
        $seiModulo->executar('cadastrarContato', $objContatoAPI);
      }

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando Contato.',$e);
    }
  }

  protected function alterarRN0323Controlado(ContatoDTO $objContatoDTO){
    try {

      global $SEI_MODULOS;

      //Valida Permissao
 	    SessaoSEI::getInstance()->validarAuditarPermissao('contato_alterar',__METHOD__,$objContatoDTO);

      $objContatoDTO = clone($objContatoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();
  	   
  		$objContatoDTOBanco = new ContatoDTO();
      $objContatoDTOBanco->setBolExclusaoLogica(false);
      $objContatoDTOBanco->retNumIdTipoContato();
      $objContatoDTOBanco->retStrStaNatureza();
      $objContatoDTOBanco->retStrSigla();
      $objContatoDTOBanco->retStrNome();
      $objContatoDTOBanco->retNumIdCargo();

      $objContatoDTOBanco->retStrStaGenero();
      $objContatoDTOBanco->retNumIdCargo();
      $objContatoDTOBanco->retDblCpf();
      $objContatoDTOBanco->retDblRg();
      $objContatoDTOBanco->retStrOrgaoExpedidor();
      $objContatoDTOBanco->retDtaNascimento();
      $objContatoDTOBanco->retStrMatricula();
      $objContatoDTOBanco->retStrMatriculaOab();
      $objContatoDTOBanco->retStrTelefoneFixo();
      $objContatoDTOBanco->retStrTelefoneCelular();
      $objContatoDTOBanco->retStrSitioInternet();
      $objContatoDTOBanco->retDblCnpj();
      $objContatoDTOBanco->retStrSinAtivo();
      $objContatoDTOBanco->retStrEmail();
      $objContatoDTOBanco->retStrEndereco();
      $objContatoDTOBanco->retStrComplemento();
      $objContatoDTOBanco->retStrBairro();
      $objContatoDTOBanco->retNumIdPais();
      $objContatoDTOBanco->retNumIdUf();
      $objContatoDTOBanco->retNumIdCidade();
      $objContatoDTOBanco->retStrCep();
      $objContatoDTOBanco->retStrObservacao();

  		$objContatoDTOBanco->retNumIdContatoAssociado();
      $objContatoDTOBanco->retStrSinEnderecoAssociado();
      $objContatoDTOBanco->retStrSinSistemaTipoContato();
  		$objContatoDTOBanco->setNumIdContato($objContatoDTO->getNumIdContato());
  		$objContatoDTOBanco = $this->consultarRN0324($objContatoDTOBanco);
  		
  		if ($objContatoDTOBanco==null){
  		  throw new InfraException('Contato não encontrado ['.$objContatoDTO->getNumIdContato().'].');
  		}

		  if ($objContatoDTO->isSetNumIdTipoContato()) {
        if ($objContatoDTO->getNumIdTipoContato()!=$objContatoDTOBanco->getNumIdTipoContato() && (!$objContatoDTO->isSetStrStaOperacao() || $objContatoDTO->getStrStaOperacao()!='REPLICACAO')) {
          if ($objContatoDTOBanco->getStrSinSistemaTipoContato()=='S'){
            $objInfraException->lancarValidacao('Não é possível alterar o Tipo deste contato.');
          }else{
           $objTipoContatoDTO = new TipoContatoDTO();
           $objTipoContatoDTO->setBolExclusaoLogica(false);
           $objTipoContatoDTO->retStrSinSistema();
           $objTipoContatoDTO->setNumIdTipoContato($objContatoDTO->getNumIdTipoContato());

           $objTipoContatoRN = new TipoContatoRN();
           $objTipoContatoDTO = $objTipoContatoRN->consultarRN0336($objTipoContatoDTO);

           if ($objTipoContatoDTO->getStrSinSistema()=='S'){
             $objInfraException->lancarValidacao('Não é possível alterar o tipo do contato para um tipo reservado do sistema.');
           }
          }
        }

      }else{
     	  $objContatoDTO->setNumIdTipoContato($objContatoDTOBanco->getNumIdTipoContato());
      }

		  if (!$objContatoDTO->isSetNumIdContatoAssociado()){
     		$objContatoDTO->setNumIdContatoAssociado($objContatoDTOBanco->getNumIdContatoAssociado());
		  }else if ($objContatoDTO->getNumIdContatoAssociado()==null){
        $objContatoDTO->setNumIdContatoAssociado($objContatoDTO->getNumIdContato());
      }

      if ($objContatoDTO->isSetStrStaNatureza() && $objContatoDTO->getStrStaNatureza()!=$objContatoDTOBanco->getStrStaNatureza()) {

        if ($objContatoDTOBanco->getStrSinSistemaTipoContato()=='S'){
          $objInfraException->lancarValidacao('Não é possível alterar a Natureza deste contato.');
        }

        if ($objContatoDTO->getStrStaNatureza()==ContatoRN::$TN_PESSOA_FISICA){

          $dto = new ContatoDTO();
          $dto->setBolExclusaoLogica(false);
          $dto->retNumIdContato();
          $dto->setNumIdContatoAssociado($objContatoDTO->getNumIdContato());
          $dto->setNumIdContato($objContatoDTO->getNumIdContato(),InfraDTO::$OPER_DIFERENTE);
          $dto->setNumMaxRegistrosRetorno(1);

          if ($this->consultarRN0324($dto) != null){
            $objInfraException->lancarValidacao('Não é possível alterar a natureza porque existem contatos associados com esta Pessoa Jurídica.');
          }

        }else{

          $objContatoDTO->setNumIdContatoAssociado($objContatoDTO->getNumIdContato());
          $objContatoDTO->setStrSinEnderecoAssociado('N');
        }
      }else{
        $objContatoDTO->setStrStaNatureza($objContatoDTOBanco->getStrStaNatureza());
      }

      $this->validarStrStaNatureza($objContatoDTO, $objInfraException);

      if (!$objContatoDTO->isSetStrSigla()){
        $objContatoDTO->setStrSigla($objContatoDTOBanco->getStrSigla());
      }

      if (!$objContatoDTO->isSetStrNome()){
        $objContatoDTO->setStrNome($objContatoDTOBanco->getStrNome());
      }

      if (!$objContatoDTO->isSetStrStaGenero()){
        $objContatoDTO->setStrStaGenero($objContatoDTOBanco->getStrStaGenero());
      }

      if (!$objContatoDTO->isSetNumIdCargo()){
        $objContatoDTO->setNumIdCargo($objContatoDTOBanco->getNumIdCargo());
      }

      if (!$objContatoDTO->isSetDblCpf()) {
        $objContatoDTO->setDblCpf($objContatoDTOBanco->getDblCpf());
      }

      if (!$objContatoDTO->isSetDblRg()) {
        $objContatoDTO->setDblRg($objContatoDTOBanco->getDblRg());
      }

      if (!$objContatoDTO->isSetStrOrgaoExpedidor()) {
        $objContatoDTO->setStrOrgaoExpedidor($objContatoDTOBanco->getStrOrgaoExpedidor());
      }

      if (!$objContatoDTO->isSetDtaNascimento()) {
        $objContatoDTO->setDtaNascimento($objContatoDTOBanco->getDtaNascimento());
      }

      if (!$objContatoDTO->isSetStrMatricula()) {
        $objContatoDTO->setStrMatricula($objContatoDTOBanco->getStrMatricula());
      }

      if (!$objContatoDTO->isSetStrMatriculaOab()) {
        $objContatoDTO->setStrMatriculaOab($objContatoDTOBanco->getStrMatriculaOab());
      }

      if (!$objContatoDTO->isSetStrTelefoneCelular()) {
        $objContatoDTO->setStrTelefoneCelular($objContatoDTOBanco->getStrTelefoneCelular());
      }

      if (!$objContatoDTO->isSetStrTelefoneFixo()){
        $objContatoDTO->setStrTelefoneFixo($objContatoDTOBanco->getStrTelefoneFixo());
      }

      if (!$objContatoDTO->isSetDblCnpj()) {
        $objContatoDTO->setDblCnpj($objContatoDTOBanco->getDblCnpj());
      }

      if (!$objContatoDTO->isSetStrSitioInternet()) {
        $objContatoDTO->setStrSitioInternet($objContatoDTOBanco->getStrSitioInternet());
      }

      if (!$objContatoDTO->isSetStrEmail()){
        $objContatoDTO->setStrEmail($objContatoDTOBanco->getStrEmail());
      }

      if (!$objContatoDTO->isSetStrEndereco()){
        $objContatoDTO->setStrEndereco($objContatoDTOBanco->getStrEndereco());
      }

      if (!$objContatoDTO->isSetStrComplemento()){
        $objContatoDTO->setStrComplemento($objContatoDTOBanco->getStrComplemento());
      }

      if (!$objContatoDTO->isSetStrBairro()){
        $objContatoDTO->setStrBairro($objContatoDTOBanco->getStrBairro());
      }

      if (!$objContatoDTO->isSetNumIdUf()){
        $objContatoDTO->setNumIdUf($objContatoDTOBanco->getNumIdUf());
      }

      if (!$objContatoDTO->isSetNumIdCidade()){
        $objContatoDTO->setNumIdCidade($objContatoDTOBanco->getNumIdCidade());
      }

      if (!$objContatoDTO->isSetNumIdPais()){
        $objContatoDTO->setNumIdPais($objContatoDTOBanco->getNumIdPais());
      }

      if (!$objContatoDTO->isSetStrCep()){
        $objContatoDTO->setStrCep($objContatoDTOBanco->getStrCep());
      }

      if (!$objContatoDTO->isSetStrObservacao()){
        $objContatoDTO->setStrObservacao($objContatoDTOBanco->getStrObservacao());
      }

      if (!$objContatoDTO->isSetStrSinAtivo()){
        $objContatoDTO->setStrSinAtivo($objContatoDTOBanco->getStrSinAtivo());
      }

      if (!$objContatoDTO->isSetStrSinEnderecoAssociado()){
        $objContatoDTO->setStrSinEnderecoAssociado($objContatoDTOBanco->getStrSinEnderecoAssociado());
      }

      if ($objContatoDTO->getStrSigla()!=$objContatoDTOBanco->getStrSigla() && $objContatoDTOBanco->getStrSinSistemaTipoContato()=='S' && (!$objContatoDTO->isSetStrStaOperacao() || $objContatoDTO->getStrStaOperacao()!='REPLICACAO')) {
        $objInfraException->lancarValidacao('Não é possível alterar a Sigla deste contato.');
      }

      if ($objContatoDTO->getStrNome()!=$objContatoDTOBanco->getStrNome() && $objContatoDTOBanco->getStrSinSistemaTipoContato()=='S' && (!$objContatoDTO->isSetStrStaOperacao() || $objContatoDTO->getStrStaOperacao()!='REPLICACAO')){
        $objInfraException->lancarValidacao('Não é possível alterar o Nome deste contato.');
      }

      $this->validarNumIdContatoAssociadoRN0729($objContatoDTO, $objInfraException);
      $this->validarNumIdTipoContatoRN0367($objContatoDTO, $objInfraException);
      $this->validarStrSinEnderecoAssociadoRN0894($objContatoDTO, $objInfraException);
      $this->validarStrSiglaRN0430($objContatoDTO, $objInfraException);
      $this->validarStrNomeRN0431($objContatoDTO, $objInfraException);
      $this->validarStrStaGeneroRN0433($objContatoDTO, $objInfraException);
      $this->validarNumIdCargoRN0427($objContatoDTO, $objInfraException);
      $this->validarDblCpfRN0435($objContatoDTO, $objInfraException);
      $this->validarDblRg($objContatoDTO, $objInfraException);
      $this->validarStrOrgaoExpedidor($objContatoDTO, $objInfraException);
      $this->validarDtaNascimentoRN0569($objContatoDTO, $objInfraException);
      $this->validarStrMatriculaRN0436($objContatoDTO, $objInfraException);
      $this->validarStrMatriculaOabRN0434($objContatoDTO, $objInfraException);
      $this->validarStrTelefoneCelular($objContatoDTO, $objInfraException);
      $this->validarDblCnpjRN0372($objContatoDTO, $objInfraException);
      $this->validarStrSitioInternetRN0440($objContatoDTO, $objInfraException);
      $this->validarStrTelefoneFixoRN0437($objContatoDTO, $objInfraException);
      $this->validarStrEmailRN0439($objContatoDTO, $objInfraException);
      $this->validarStrEnderecoRN0441($objContatoDTO, $objInfraException);
      $this->validarStrComplemento($objContatoDTO, $objInfraException);
      $this->validarStrBairroRN0442($objContatoDTO, $objInfraException);

      $this->validarNumIdPais($objContatoDTO, $objInfraException);
      $this->validarNumIdUf($objContatoDTO, $objInfraException);
      $this->validarNumIdCidade($objContatoDTO, $objInfraException);

      $this->validarStrCepRN0446($objContatoDTO, $objInfraException);
      $this->validarStrObservacaoRN0447($objContatoDTO, $objInfraException);
      $this->validarStrSinAtivoRN0449($objContatoDTO, $objInfraException);

      //if ($objContatoDTO->getStrSigla()!=$objContatoDTOBanco->getStrSigla() || $objContatoDTO->getStrNome()!=$objContatoDTOBanco->getStrNome()){
      //  $this->validarSiglaNomeUnicosRN1221($objContatoDTO, $objInfraException);
      //}

      $objInfraException->lancarValidacoes();
      

      $objContatoBD = new ContatoBD($this->getObjInfraIBanco());
      $objContatoBD->alterar($objContatoDTO);

      $this->montarIndexacaoRN0450($objContatoDTO);

      $objContatoAPI = new ContatoAPI();
      $objContatoAPI->setIdContato($objContatoDTO->getNumIdContato());
      $objContatoAPI->setIdTipoContato($objContatoDTO->getNumIdTipoContato());
      $objContatoAPI->setIdContatoAssociado($objContatoDTO->getNumIdContatoAssociado()!=$objContatoDTO->getNumIdContato()?$objContatoDTO->getNumIdContatoAssociado():null);
      $objContatoAPI->setStaNatureza($objContatoDTO->getStrStaNatureza());
      $objContatoAPI->setSinEnderecoAssociado($objContatoDTO->getStrSinEnderecoAssociado());
      $objContatoAPI->setStaGenero($objContatoDTO->getStrStaGenero());
      $objContatoAPI->setCpf($objContatoDTO->getDblCpf());
      $objContatoAPI->setRg($objContatoDTO->getDblRg());
      $objContatoAPI->setOrgaoExpedidor($objContatoDTO->getStrOrgaoExpedidor());
      $objContatoAPI->setMatricula($objContatoDTO->getStrMatricula());
      $objContatoAPI->setMatriculaOab($objContatoDTO->getStrMatriculaOab());
      $objContatoAPI->setDataNascimento($objContatoDTO->getDtaNascimento());
      $objContatoAPI->setCnpj($objContatoDTO->getDblCnpj());
      $objContatoAPI->setIdCargo($objContatoDTO->getNumIdCargo());
      $objContatoAPI->setSigla($objContatoDTO->getStrSigla());
      $objContatoAPI->setNome($objContatoDTO->getStrNome());
      $objContatoAPI->setTelefoneFixo($objContatoDTO->getStrTelefoneFixo());
      $objContatoAPI->setTelefoneCelular($objContatoDTO->getStrTelefoneCelular());
      $objContatoAPI->setEmail($objContatoDTO->getStrEmail());
      $objContatoAPI->setSitioInternet($objContatoDTO->getStrSitioInternet());
      $objContatoAPI->setEndereco($objContatoDTO->getStrEndereco());
      $objContatoAPI->setComplemento($objContatoDTO->getStrComplemento());
      $objContatoAPI->setBairro($objContatoDTO->getStrBairro());
      $objContatoAPI->setCep($objContatoDTO->getStrCep());
      $objContatoAPI->setObservacao($objContatoDTO->getStrObservacao());
      $objContatoAPI->setSinAtivo($objContatoDTO->getStrSinAtivo());
      $objContatoAPI->setIdPais($objContatoDTO->getNumIdPais());
      $objContatoAPI->setIdEstado($objContatoDTO->getNumIdUf());
      $objContatoAPI->setIdCidade($objContatoDTO->getNumIdCidade());

      foreach ($SEI_MODULOS as $seiModulo) {
        $seiModulo->executar('alterarContato', $objContatoAPI);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando Contato.',$e);
    }
  }

  protected function consultarRN0324Conectado(ContatoDTO $objContatoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('contato_consultar',__METHOD__,$objContatoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();
      
      $objContatoBD = new ContatoBD($this->getObjInfraIBanco());
      $ret = $objContatoBD->consultar($objContatoDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando Contato.',$e);
    }
  }

  protected function listarRN0325Conectado(ContatoDTO $objContatoDTO) {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('contato_listar',__METHOD__,$objContatoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

		  $objContatoBD = new ContatoBD($this->getObjInfraIBanco());
			$ret = $objContatoBD->listar($objContatoDTO);
		  
      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Contatos.',$e);
    }
  }

  protected function contarRN0327Conectado(ContatoDTO $objContatoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('contato_listar',__METHOD__,$objContatoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objContatoBD = new ContatoBD($this->getObjInfraIBanco());
      $ret = $objContatoBD->contar($objContatoDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro contando Contatos.',$e);
    }
  }

  protected function excluirRN0326Controlado($arrObjContatoDTO){
    try {

      global $SEI_MODULOS;
      
      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('contato_excluir',__METHOD__,$arrObjContatoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      //complementa ocorrências com sinalizador de contexto
      $dto = new ContatoDTO();
      $dto->setBolExclusaoLogica(false);
      $dto->retNumIdContato();
      $dto->retNumIdTipoContato();
      $dto->retNumIdContatoAssociado();
      $dto->retStrNome();
      $dto->retStrSigla();
      $dto->setNumIdContato(InfraArray::converterArrInfraDTO($arrObjContatoDTO,'IdContato'),InfraDTO::$OPER_IN);

      $arrObjContatoDTO = $this->listarRN0325($dto);
      
      $objRelGrupoContatoRN = new RelGrupoContatoRN();
      $objRelGrupoContatoDTO = new RelGrupoContatoDTO();

      $objParticipanteRN = new ParticipanteRN();
      $objParticipanteDTO = new ParticipanteDTO();
      $objParticipanteDTO->retStrProtocoloFormatadoProtocolo();

      $objUsuarioRN = new UsuarioRN();
      $objUnidadeRN = new UnidadeRN();
      $objOrgaoRN = new OrgaoRN();

      $objContatoDTO2 = new ContatoDTO();
      $objContatoDTO2->setBolExclusaoLogica(false);

      foreach($arrObjContatoDTO as $objContatoDTO){

        $objUsuarioDTO = new UsuarioDTO();
        $objUsuarioDTO->setBolExclusaoLogica(false);
        $objUsuarioDTO->retStrStaTipo();
        $objUsuarioDTO->setNumIdContato($objContatoDTO->getNumIdContato());

        $arrObjUsuarioDTO = $objUsuarioRN->listarRN0490($objUsuarioDTO);

        foreach($arrObjUsuarioDTO as $objUsuarioDTO){
          if ($objUsuarioDTO->getStrStaTipo()==UsuarioRN::$TU_EXTERNO_PENDENTE || $objUsuarioDTO->getStrStaTipo()==UsuarioRN::$TU_EXTERNO){
            $objInfraException->adicionarValidacao('O contato "'.$objContatoDTO->getStrNome().'" está associado com registro de Usuário Externo.');
          }else if ($objUsuarioDTO->getStrStaTipo()==UsuarioRN::$TU_SISTEMA){
            $objInfraException->adicionarValidacao('O contato "'.$objContatoDTO->getStrNome().'" está associado com registro de Usuário de Sistema.');
          }else{
            $objInfraException->adicionarValidacao('O contato "'.$objContatoDTO->getStrNome().'" está associado com registro de Usuário.');
          }
        }

        $objUnidadeDTO = new UnidadeDTO();
        $objUnidadeDTO->setBolExclusaoLogica(false);
        $objUnidadeDTO->retStrSigla();
        $objUnidadeDTO->retStrSiglaOrgao();
        $objUnidadeDTO->setNumIdContato($objContatoDTO->getNumIdContato());

        $arrObjUnidadeDTO = $objUnidadeRN->listarRN0127($objUnidadeDTO);

        foreach($arrObjUnidadeDTO as $objUnidadeDTO){
           $objInfraException->adicionarValidacao('O contato "'.$objContatoDTO->getStrNome().'" está associado com o registro da unidade '.$objUnidadeDTO->getStrSigla().' / '.$objUnidadeDTO->getStrSiglaOrgao().'.');
        }

        $objOrgaoDTO = new OrgaoDTO();
        $objOrgaoDTO->setBolExclusaoLogica(false);
        $objOrgaoDTO->retStrSigla();
        $objOrgaoDTO->setNumIdContato($objContatoDTO->getNumIdContato());

        $arrObjOrgaoDTO = $objOrgaoRN->listarRN1353($objOrgaoDTO);

        foreach($arrObjOrgaoDTO as $objOrgaoDTO){
          $objInfraException->adicionarValidacao('O contato "'.$objContatoDTO->getStrNome().'" está associado com registro de órgao '.$objOrgaoDTO->getStrSigla().'.');
        }
        
      	$objRelGrupoContatoDTO->setNumIdContato($objContatoDTO->getNumIdContato());
      	if ($objRelGrupoContatoRN->contarRN0465($objRelGrupoContatoDTO)>0){
      		$objInfraException->adicionarValidacao('Existem grupos utilizando o contato "'.$objContatoDTO->getStrNome().'".');
      	}
      
      	$objParticipanteDTO->setNumIdContato($objContatoDTO->getNumIdContato());
      	$arrObjParticipanteDTO = $objParticipanteRN->listarRN0189($objParticipanteDTO);
      	if (count($arrObjParticipanteDTO)>0){
      	  
      	  if (count($arrObjParticipanteDTO)==1){
      	    $objInfraException->adicionarValidacao('O contato "'.$objContatoDTO->getStrNome().'" é utilizado no protocolo '.$arrObjParticipanteDTO[0]->getStrProtocoloFormatadoProtocolo().'.');
      	  }else{
      	    $strProtocolos = '';
      	    for($i=0;$i<count($arrObjParticipanteDTO);$i++){
      	      
      	      if ($i==10){
      	        $strProtocolos .= '\n...';
      	        break;
      	      }
      	      
      	      if ($strProtocolos!=''){
      	        $strProtocolos .= '\n';
      	      }
      	      $strProtocolos .= $arrObjParticipanteDTO[$i]->getStrProtocoloFormatadoProtocolo();
      	      
      	    }
      	    
      	    $objInfraException->adicionarValidacao('O contato "'.$objContatoDTO->getStrNome().'" é utilizado em '.count($arrObjParticipanteDTO).' protocolos:\n'.$strProtocolos);
      	  }
      	}


        $objContatoDTO2->setNumIdContato($objContatoDTO->getNumIdContato(),InfraDTO::$OPER_DIFERENTE);
        $objContatoDTO2->setNumIdContatoAssociado($objContatoDTO->getNumIdContato());
        $objContatoDTO2->setStrSinAtivo('S');

        if ($this->contarRN0327($objContatoDTO2)){
          $objInfraException->adicionarValidacao('Existem contatos associados com o com o contato "'.$objContatoDTO->getStrNome().'".');
        }

        $objContatoDTO2->setStrSinAtivo('N');
        if ($this->contarRN0327($objContatoDTO2)){
          $objInfraException->adicionarValidacao('Existem contatos inativos associados com o contato "'.$objContatoDTO->getStrNome().'".');
        }
      }

      $objInfraException->lancarValidacoes();

      $arrObjContatoAPI = array();
      foreach ($arrObjContatoDTO as $objContatoDTO) {
        $objContatoAPI = new ContatoAPI();
        $objContatoAPI->setIdContato($objContatoDTO->getNumIdContato());
        $objContatoAPI->setIdTipoContato($objContatoDTO->getNumIdTipoContato());
        $objContatoAPI->setIdContatoAssociado($objContatoDTO->getNumIdContatoAssociado()!=$objContatoDTO->getNumIdContato()?$objContatoDTO->getNumIdContatoAssociado():null);
        $objContatoAPI->setSigla($objContatoDTO->getStrSigla());
        $objContatoAPI->setNome($objContatoDTO->getStrNome());
        $arrObjContatoAPI[] = $objContatoAPI;
      }

      foreach ($SEI_MODULOS as $seiModulo) {
        $seiModulo->executar('excluirContato', $arrObjContatoAPI);
      }

      $objContatoBD = new ContatoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjContatoDTO);$i++){
        $objContatoBD->excluir($arrObjContatoDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo Contato.',$e);
    }
  }
  
  protected function desativarRN0451Controlado($arrObjContatoDTO){
    try {

      global $SEI_MODULOS;

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('contato_desativar',__METHOD__,$arrObjContatoDTO);

      if (count($arrObjContatoDTO)) {

        //Regras de Negocio
        $objInfraException = new InfraException();

        $dtoRN = new RelGrupoContatoRN();
        $dto = new RelGrupoContatoDTO();
        $dto->retNumIdGrupoContato();
        $dto->retNumIdContato();
        for ($i = 0; $i < count($arrObjContatoDTO); $i++) {
          $dto->setNumIdContato($arrObjContatoDTO[$i]->getNumIdContato());
          $dtoRN->excluirRN0464($dtoRN->listarRN0463($dto));
        }

        $dtoRN = new ContatoRN();
        for ($i = 0; $i < count($arrObjContatoDTO); $i++) {
          $dto = new ContatoDTO();
          $dto->setBolExclusaoLogica(true);
          $dto->setNumIdContato($arrObjContatoDTO[$i]->getNumIdContato(), InfraDTO::$OPER_DIFERENTE);
          $dto->setNumIdContatoAssociado($arrObjContatoDTO[$i]->getNumIdContato());
          if ($dtoRN->contarRN0327($dto)) {
            $objInfraException->adicionarValidacao('Existem contatos associados.');
          }
        }

        $objInfraException->lancarValidacoes();

        $objContatoBD = new ContatoBD($this->getObjInfraIBanco());
        for ($i = 0; $i < count($arrObjContatoDTO); $i++) {
          $objContatoBD->desativar($arrObjContatoDTO[$i]);
        }

        $dto = new ContatoDTO();
        $dto->setBolExclusaoLogica(false);
        $dto->retNumIdContato();
        $dto->retNumIdTipoContato();
        $dto->retNumIdContatoAssociado();
        $dto->retStrNome();
        $dto->retStrSigla();
        $dto->setNumIdContato(InfraArray::converterArrInfraDTO($arrObjContatoDTO, 'IdContato'), InfraDTO::$OPER_IN);

        $arrObjContatoDTO = $this->listarRN0325($dto);

        $arrObjContatoAPI = array();
        foreach ($arrObjContatoDTO as $objContatoDTO) {
          $objContatoAPI = new ContatoAPI();
          $objContatoAPI->setIdContato($objContatoDTO->getNumIdContato());
          $objContatoAPI->setIdTipoContato($objContatoDTO->getNumIdTipoContato());
          $objContatoAPI->setIdContatoAssociado($objContatoDTO->getNumIdContatoAssociado() != $objContatoDTO->getNumIdContato() ? $objContatoDTO->getNumIdContatoAssociado() : null);
          $objContatoAPI->setSigla($objContatoDTO->getStrSigla());
          $objContatoAPI->setNome($objContatoDTO->getStrNome());
          $arrObjContatoAPI[] = $objContatoAPI;
        }

        foreach ($SEI_MODULOS as $seiModulo) {
          $seiModulo->executar('desativarContato', $arrObjContatoAPI);
        }
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando Contato.',$e);
    }
  }

  protected function reativarRN0452Controlado($arrObjContatoDTO){
    try {

      global $SEI_MODULOS;

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('contato_reativar',__METHOD__,$arrObjContatoDTO);

      if (count($arrObjContatoDTO)) {

        //Regras de Negocio
        //$objInfraException = new InfraException();
        //$objInfraException->lancarValidacoes();

        $objContatoBD = new ContatoBD($this->getObjInfraIBanco());
        for ($i = 0; $i < count($arrObjContatoDTO); $i++) {
          $objContatoBD->reativar($arrObjContatoDTO[$i]);
        }

        $dto = new ContatoDTO();
        $dto->setBolExclusaoLogica(false);
        $dto->retNumIdContato();
        $dto->retNumIdTipoContato();
        $dto->retNumIdContatoAssociado();
        $dto->retStrNome();
        $dto->retStrSigla();
        $dto->setNumIdContato(InfraArray::converterArrInfraDTO($arrObjContatoDTO, 'IdContato'), InfraDTO::$OPER_IN);

        $arrObjContatoDTO = $this->listarRN0325($dto);

        $arrObjContatoAPI = array();
        foreach ($arrObjContatoDTO as $objContatoDTO) {
          $objContatoAPI = new ContatoAPI();
          $objContatoAPI->setIdContato($objContatoDTO->getNumIdContato());
          $objContatoAPI->setIdTipoContato($objContatoDTO->getNumIdTipoContato());
          $objContatoAPI->setIdContatoAssociado($objContatoDTO->getNumIdContatoAssociado() != $objContatoDTO->getNumIdContato() ? $objContatoDTO->getNumIdContatoAssociado() : null);
          $objContatoAPI->setSigla($objContatoDTO->getStrSigla());
          $objContatoAPI->setNome($objContatoDTO->getStrNome());
          $arrObjContatoAPI[] = $objContatoAPI;
        }

        foreach ($SEI_MODULOS as $seiModulo) {
          $seiModulo->executar('reativarContato', $arrObjContatoAPI);
        }
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando Contato.',$e);
    }
  }

  public static function listarNaturezas(){
    $arr = array();

    $objTipoDTO = new TipoDTO();
    $objTipoDTO->setStrStaTipo(ContatoRN::$TN_PESSOA_FISICA);
    $objTipoDTO->setStrDescricao('Pessoa Física');
    $arr[] = $objTipoDTO;

    $objTipoDTO = new TipoDTO();
    $objTipoDTO->setStrStaTipo(ContatoRN::$TN_PESSOA_JURIDICA);
    $objTipoDTO->setStrDescricao('Pessoa Jurídica');
    $arr[] = $objTipoDTO;

    return $arr;
  }

  private function validarStrStaNatureza(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getStrStaNatureza())){
      $objInfraException->lancarValidacao('Natureza não informada.');
    }else{
      if (!in_array($objContatoDTO->getStrStaNatureza(),InfraArray::converterArrInfraDTO(ContatoRN::listarNaturezas(),'StaTipo'))){
        $objInfraException->lancarValidacao('Natureza inválida.');
      }
    }
  }

  private function validarNumIdCargoRN0427(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getNumIdCargo())){
      $objContatoDTO->setNumIdCargo(null);
    }else {
      if ($objContatoDTO->getStrStaNatureza() == ContatoRN::$TN_PESSOA_JURIDICA) {
        $objInfraException->adicionarValidacao('Não é possível informar o Cargo para Pessoa Jurídica.');
      }
    }
  }

  private function validarStrSiglaRN0430(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getStrSigla())){
      $objContatoDTO->setStrSigla(null);
    }else{
      $objContatoDTO->setStrSigla(trim($objContatoDTO->getStrSigla()));

      if (strlen($objContatoDTO->getStrSigla())>100){
        $objInfraException->adicionarValidacao('Sigla possui tamanho superior a 100 caracteres.');
      }
    }
  }

  private function validarStrNomeRN0431(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    
    if (InfraString::isBolVazia($objContatoDTO->getStrNome())){      
        $objInfraException->adicionarValidacao('Nome não informado.');
    }else{      
      $objContatoDTO->setStrNome(trim($objContatoDTO->getStrNome()));
  
      if (strlen($objContatoDTO->getStrNome())>250){
        $objInfraException->adicionarValidacao('Nome possui tamanho superior a 250 caracteres.');
      }
    }
  }

  private function validarDtaNascimentoRN0569(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getDtaNascimento())){
      $objContatoDTO->setDtaNascimento(null);
    }else{

      if ($objContatoDTO->getStrStaNatureza()==ContatoRN::$TN_PESSOA_JURIDICA){
        $objInfraException->adicionarValidacao('Não é possível informar a Data de Nascimento para Pessoa Jurídica.');
      }

      if (!InfraData::validarData($objContatoDTO->getDtaNascimento())){
        $objInfraException->adicionarValidacao('Data de Nascimento inválida.');
      }
    }
  }
  
  private function validarStrStaGeneroRN0433(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getStrStaGenero())){
      $objContatoDTO->setStrStaGenero(null);
    }else{

      if ($objContatoDTO->getStrStaNatureza()==ContatoRN::$TN_PESSOA_JURIDICA){
        $objInfraException->adicionarValidacao('Não é possível informar o Gênero para Pessoa Jurídica.');
      }

	    if ($objContatoDTO->getStrStaGenero()!=self::$TG_MASCULINO && $objContatoDTO->getStrStaGenero()!=self::$TG_FEMININO){
	      $objInfraException->adicionarValidacao('Gênero inválido.');
	    }
    }
  }

  private function validarDblCpfRN0435(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getDblCpf())){
      $objContatoDTO->setDblCpf(null);
    }else{

      if ($objContatoDTO->getStrStaNatureza()==ContatoRN::$TN_PESSOA_JURIDICA){
        $objInfraException->adicionarValidacao('Não é possível informar o CPF para Pessoa Jurídica.');
      }

	  	if(!InfraUtil::validarCpf($objContatoDTO->getDblCpf())){
	  		$objInfraException->adicionarValidacao('Número de CPF inválido.');
	  	}
	  	$objContatoDTO->setDblCpf(InfraUtil::retirarFormatacao($objContatoDTO->getDblCpf()));
		}  	
  }

  private function validarDblRg(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getDblRg())){
      $objContatoDTO->setDblRg(null);
    }else{

      if ($objContatoDTO->getStrStaNatureza()==ContatoRN::$TN_PESSOA_JURIDICA){
        $objInfraException->adicionarValidacao('Não é possível informar o RG para Pessoa Jurídica.');
      }

      $objContatoDTO->setDblRg(InfraUtil::retirarFormatacao($objContatoDTO->getDblRg()));
    }
  }

  private function validarStrOrgaoExpedidor(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getStrOrgaoExpedidor())){
      $objContatoDTO->setStrOrgaoExpedidor(null);
    }else {

      if ($objContatoDTO->getStrStaNatureza() == ContatoRN::$TN_PESSOA_JURIDICA) {
        $objInfraException->adicionarValidacao('Não é possível informar o Órgão Expedidor para Pessoa Jurídica.');
      }

      $objContatoDTO->setStrOrgaoExpedidor(trim($objContatoDTO->getStrOrgaoExpedidor()));

      if (strlen($objContatoDTO->getStrOrgaoExpedidor()) > 50) {
        $objInfraException->adicionarValidacao('Órgão Expedidor possui tamanho superior a 50 caracteres.');
      }
    }
  }
      
  private function validarStrMatriculaRN0436(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getStrMatricula())){
      $objContatoDTO->setStrMatricula(null);
    }else{

      if ($objContatoDTO->getStrStaNatureza()==ContatoRN::$TN_PESSOA_JURIDICA){
        $objInfraException->adicionarValidacao('Não é possível informar a Matrícula para Pessoa Jurídica.');
      }

      $objContatoDTO->setStrMatricula(trim($objContatoDTO->getStrMatricula()));

      if (strlen($objContatoDTO->getStrMatricula())>10){
        $objInfraException->adicionarValidacao('Matrícula possui tamanho superior a 10 caracteres.');
      }
    }
  }

  private function validarStrMatriculaOabRN0434(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getStrMatriculaOab())){
      $objContatoDTO->setStrMatriculaOab(null);
    }else{

      if ($objContatoDTO->getStrStaNatureza()==ContatoRN::$TN_PESSOA_JURIDICA){
        $objInfraException->adicionarValidacao('Não é possível informar Matrícula OAB para Pessoa Jurídica.');
      }

      $objContatoDTO->setStrMatriculaOab(trim($objContatoDTO->getStrMatriculaOab()));

      if (strlen($objContatoDTO->getStrMatriculaOab())>10){
        $objInfraException->adicionarValidacao('Número da OAB possui tamanho superior a 10 caracteres.');
      }
    }
  }

  private function validarStrTelefoneFixoRN0437(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getStrTelefoneFixo())){
      $objContatoDTO->setStrTelefoneFixo(null);
    }else{
      $objContatoDTO->setStrTelefoneFixo(trim($objContatoDTO->getStrTelefoneFixo()));

      if (strlen($objContatoDTO->getStrTelefoneFixo())>50){
        $objInfraException->adicionarValidacao('Telefone Fixo possui tamanho superior a 50 caracteres.');
      }
    }
  }

  private function validarStrTelefoneCelular(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getStrTelefoneCelular())){
      $objContatoDTO->setStrTelefoneCelular(null);
    }else{

      $objContatoDTO->setStrTelefoneCelular(trim($objContatoDTO->getStrTelefoneCelular()));

      if (strlen($objContatoDTO->getStrTelefoneCelular())>25){
        $objInfraException->adicionarValidacao('Telefone Celular possui tamanho superior a 25 caracteres.');
      }
    }
  }

  private function validarStrEmailRN0439(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getStrEmail())){
      $objContatoDTO->setStrEmail(null);
    }else{
      $objContatoDTO->setStrEmail(trim($objContatoDTO->getStrEmail()));
      if (strlen($objContatoDTO->getStrEmail())>50){
        $objInfraException->adicionarValidacao('E-mail possui tamanho superior a 50 caracteres.');
      }
      if (!InfraUtil::validarEmail($objContatoDTO->getStrEmail())){
        $objInfraException->adicionarValidacao('E-mail inválido.');
      }
    }
  }

  private function validarStrSitioInternetRN0440(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getStrSitioInternet())){
      $objContatoDTO->setStrSitioInternet(null);
    }else{

      if ($objContatoDTO->getStrStaNatureza()==ContatoRN::$TN_PESSOA_FISICA){
        $objInfraException->adicionarValidacao('Não é possível informar Sítio na Internet para Pessoa Física.');
      }

      $objContatoDTO->setStrSitioInternet(trim($objContatoDTO->getStrSitioInternet()));

      if (strlen($objContatoDTO->getStrSitioInternet())>50){
        $objInfraException->adicionarValidacao('Sítio na Internet possui tamanho superior a 50 caracteres');
      }
    }
  }

  private function validarStrEnderecoRN0441(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getStrEndereco())){
      $objContatoDTO->setStrEndereco(null);
    }else{
      $objContatoDTO->setStrEndereco(trim($objContatoDTO->getStrEndereco()));

      if (strlen($objContatoDTO->getStrEndereco())>130){
        $objInfraException->adicionarValidacao('Endereço possui tamanho superior a 130 caracteres.');
      }
    }
  }

  private function validarStrComplemento(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getStrComplemento())){
      $objContatoDTO->setStrComplemento(null);
    }else{
      $objContatoDTO->setStrComplemento(trim($objContatoDTO->getStrComplemento()));

      if (strlen($objContatoDTO->getStrComplemento())>130){
        $objInfraException->adicionarValidacao('Complemento do endereço possui tamanho superior a 130 caracteres.');
      }
    }
  }

  private function validarStrBairroRN0442(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getStrBairro())){
      $objContatoDTO->setStrBairro(null);
    }else{
      $objContatoDTO->setStrBairro(trim($objContatoDTO->getStrBairro()));

      if (strlen($objContatoDTO->getStrBairro()) > 70){
        $objInfraException->adicionarValidacao('Bairro possui tamanho superior a 70 caracteres.');
      }
    }
  }

  private function validarNumIdUf(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getNumIdUf())){
      $objContatoDTO->setNumIdUf(null);
    }else{

      if ($objContatoDTO->getNumIdPais()==null){
        $objInfraException->lancarValidacao('País associado com o Estado não informado.');
      }

      $objUfDTO = new UfDTO();
      $objUfDTO->setNumIdUf($objContatoDTO->getNumIdUf());
      $objUfDTO->setNumIdPais($objContatoDTO->getNumIdPais());

      $objUfRN = new UfRN();
      if ($objUfRN->contarRN0402($objUfDTO)==0){
        $objInfraException->lancarValidacao('Estado não pertence ao País do contato.');
      }

    }
  }

  private function validarNumIdCidade(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getNumIdCidade())){
      $objContatoDTO->setNumIdCidade(null);
    }else{

      if ($objContatoDTO->getNumIdPais()==null){
        $objInfraException->lancarValidacao('País associado com a Cidade não informado.');
      }

      $objCidadeDTO = new CidadeDTO();
      $objCidadeDTO->setNumIdCidade($objContatoDTO->getNumIdCidade());
      $objCidadeDTO->setNumIdPais($objContatoDTO->getNumIdPais());

      $objCidadeRN = new CidadeRN();
      if ($objCidadeRN->contarRN0414($objCidadeDTO)==0){
        $objInfraException->lancarValidacao('Cidade não pertence ao País do contato.');
      }

      if ($objContatoDTO->getNumIdUf()!=null){
        $objCidadeDTO->setNumIdUf($objContatoDTO->getNumIdUf());
        if ($objCidadeRN->contarRN0414($objCidadeDTO)==0){
          $objInfraException->lancarValidacao('Cidade não pertence ao Estado do contato.');
        }
      }

    }
  }

  private function validarNumIdPais(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getNumIdPais())){
      $objContatoDTO->setNumIdPais(null);
    }
  }

  private function validarStrCepRN0446(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getStrCep())){
      $objContatoDTO->setStrCep(null);
    }else{
      $objContatoDTO->setStrCep(trim($objContatoDTO->getStrCep()));

      if (strlen($objContatoDTO->getStrCep())>15){
        $objInfraException->adicionarValidacao('CEP possui tamanho superior a 15 caracteres.');
      }
    }
  }

  private function validarStrObservacaoRN0447(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getStrObservacao())){
      $objContatoDTO->setStrObservacao(null);
    }else{
      $objContatoDTO->setStrObservacao(trim($objContatoDTO->getStrObservacao()));

      if (strlen($objContatoDTO->getStrObservacao())>250){
        $objInfraException->adicionarValidacao('Observação possui tamanho superior a 250 caracteres.');
      }
    }
  }

  private function validarStrIdxContatoRN0448(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getStrIdxContato())){
      $objContatoDTO->setStrIdxContato(null);
    }else{
      $objContatoDTO->setStrIdxContato(trim($objContatoDTO->getStrIdxContato()));

      if (strlen($objContatoDTO->getStrIdxContato())>1000){
        $objInfraException->adicionarValidacao('Indexação possui tamanho superior a 1000 caracteres.');
      }
    }
  }

  private function validarStrSinEnderecoAssociadoRN0894(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getStrSinEnderecoAssociado())){
      $objInfraException->adicionarValidacao('Sinalizador de uso do endereço do contato associado não informado.');
    }else{
      if (!InfraUtil::isBolSinalizadorValido($objContatoDTO->getStrSinEnderecoAssociado())){
        $objInfraException->adicionarValidacao('Sinalizador de uso do endereço do contato associado inválido.');
      }else{
        if ($objContatoDTO->getStrSinEnderecoAssociado()=='S' && $objContatoDTO->getNumIdContato()==$objContatoDTO->getNumIdContatoAssociado()){
          $objInfraException->adicionarValidacao('Não é possível usar o endereço associado pois não existe Pessoa Jurídica associada.');
        }
      }
    }
  }
  
  private function validarStrSinAtivoRN0449(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getStrSinAtivo())){
      $objInfraException->adicionarValidacao('Sinalizador de Exclusão Lógica não informado.');
    }else{
      if (!InfraUtil::isBolSinalizadorValido($objContatoDTO->getStrSinAtivo())){
        $objInfraException->adicionarValidacao('Sinalizador de Exclusão Lógica inválido.');
      }
    }
  }

  private function validarNumIdContatoAssociadoRN0729(ContatoDTO $objContatoDTO, InfraException $objInfraException){

    if (InfraString::isBolVazia($objContatoDTO->getNumIdContatoAssociado())){
      $objContatoDTO->setNumIdContatoAssociado(null);
    }

    if ($objContatoDTO->getNumIdContato()!=$objContatoDTO->getNumIdContatoAssociado()) {

      $dto = new ContatoDTO();
      $dto->setBolExclusaoLogica(false);
      $dto->retNumIdContato();
      $dto->retNumIdContatoAssociado();
      $dto->retNumIdTipoContato();
      $dto->retStrStaNatureza();
      $dto->setNumIdContato($objContatoDTO->getNumIdContatoAssociado());
      $dto = $this->consultarRN0324($dto);

      if ($dto == null) {
        throw new InfraException('Contato associado não encontrado.');
      }

      if ($dto->getStrStaNatureza() == ContatoRN::$TN_PESSOA_FISICA) {
        $objInfraException->lancarValidacao('Não é possível realizar associação com uma Pessoa Física.');
      }

      //if ($dto->getNumIdContatoAssociado()!=$dto->getNumIdContato()){
      //  $objInfraException->lancarValidacao('Não é possível realizar associação com uma Pessoa Jurídica que já está associada com outra Pessoa Jurídica.');
      //}

      if ($objContatoDTO->getNumIdContato()!=null){

        $objOrgaoDTO = new OrgaoDTO();
        $objOrgaoDTO->setBolExclusaoLogica(false);
        $objOrgaoDTO->retNumIdOrgao();
        $objOrgaoDTO->setNumIdContato($objContatoDTO->getNumIdContato());
        $objOrgaoDTO->setNumMaxRegistrosRetorno(1);

        $objOrgaoRN = new OrgaoRN();
        if ($objOrgaoRN->consultarRN1352($objOrgaoDTO)!=null && $objContatoDTO->getNumIdContatoAssociado()!=null){
          $objInfraException->lancarValidacao('Não é possível associar uma Pessoa Jurídica com um Órgão.');
        }

        $objUnidadeDTO = new UnidadeDTO();
        $objUnidadeDTO->retNumIdUnidade();
        $objUnidadeDTO->setBolExclusaoLogica(false);
        $objUnidadeDTO->setNumIdContatoOrgao($objContatoDTO->getNumIdContatoAssociado(),InfraDTO::$OPER_DIFERENTE);
        $objUnidadeDTO->setNumIdContato($objContatoDTO->getNumIdContato());
        $objUnidadeDTO->setNumMaxRegistrosRetorno(1);

        $objUnidadeRN = new UnidadeRN();
        if ($objUnidadeRN->consultarRN0125($objUnidadeDTO)!=null){
          $objInfraException->lancarValidacao('Não é possível alterar a Pessoa Jurídica associada com uma Unidade.');
        }

        if (!$objContatoDTO->isSetStrStaOperacao() || $objContatoDTO->getStrStaOperacao()!='REPLICACAO') {
          $objUsuarioDTO = new UsuarioDTO();
          $objUsuarioDTO->setBolExclusaoLogica(false);
          $objUsuarioDTO->retNumIdUsuario();
          $objUsuarioDTO->setNumIdContatoOrgao($objContatoDTO->getNumIdContatoAssociado(), InfraDTO::$OPER_DIFERENTE);
          $objUsuarioDTO->setNumIdContato($objContatoDTO->getNumIdContato());
          $objUsuarioDTO->setStrStaTipo(UsuarioRN::$TU_SIP);
          $objUsuarioDTO->setNumMaxRegistrosRetorno(1);

          $objUsuarioRN = new UsuarioRN();
          if ($objUsuarioRN->consultarRN0489($objUsuarioDTO)!=null) {
            $objInfraException->lancarValidacao('Não é possível alterar a Pessoa Jurídica associada com um Usuário.');
          }
        }
      }
    }
  }
  
  protected function montarIndexacaoRN0450Controlado(ContatoDTO $objContatoDTO){

    $dto = new ContatoDTO();
    $dto->setBolExclusaoLogica(false);
    $dto->retNumIdContato();
  	$dto->retStrSigla();
    $dto->retStrNome();
    $dto->retDblCpf();
    $dto->retDblCnpj();
    $dto->retStrMatricula();
  	$dto->setNumIdContato($objContatoDTO->getNumIdContato());  	

  	$dto = $this->consultarRN0324($dto);

    $strCpf = InfraUtil::formatarCpf($dto->getDblCpf());
    $strCnpj = InfraUtil::formatarCnpj($dto->getDblCnpj());

  	$contatoIdxDTO = new ContatoDTO();
  	$contatoIdxDTO->setNumIdContato($dto->getNumIdContato());
  	
  	$strIndexacao = '';
  	$strIndexacao .= ' '.$dto->getStrSigla();
  	$strIndexacao .= ' '.$dto->getStrNome();
  	$strIndexacao .= ' '.InfraUtil::retirarFormatacao($strCpf);
		$strIndexacao .= ' '.InfraUtil::retirarFormatacao($strCnpj);
  	$strIndexacao .= ' '.$dto->getStrMatricula();

  	$strIndexacao = InfraString::prepararIndexacao($strIndexacao,false);

  	$strIndexacao .= ' '.$strCpf;
  	$strIndexacao .= ' '.$strCnpj;

  	$contatoIdxDTO->setStrIdxContato($strIndexacao);

    $objInfraException = new InfraException();
    $this->validarStrIdxContatoRN0448($contatoIdxDTO, $objInfraException);
    $objInfraException->lancarValidacoes();

  	$objContatoBD = new ContatoBD($this->getObjInfraIBanco());
  	$objContatoBD->alterar($contatoIdxDTO);
  }
  
  protected function pesquisarRN0471Conectado(ContatoDTO $objContatoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('contato_listar',__METHOD__,$objContatoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();      

      
 		  if ($objContatoDTO->isSetNumIdGrupoContato()){  		  
  		  if ($objContatoDTO->getNumIdGrupoContato()==null) {
          $objContatoDTO->unSetNumIdGrupoContato();
        }else{
    			$objRelGrupoContatoDTO = new RelGrupoContatoDTO();
    			$objRelGrupoContatoRN = new RelGrupoContatoRN();
    			
    			$objRelGrupoContatoDTO->retNumIdContato();
    			$objRelGrupoContatoDTO->setNumIdGrupoContato($objContatoDTO->getNumIdGrupoContato());
    			$arrRelGrupoContatoDTO = $objRelGrupoContatoRN->listarRN0463($objRelGrupoContatoDTO);
    			$arr = array();
    			
    			for ($i=0;$i<count($arrRelGrupoContatoDTO);$i++){
    				$arr[$i] = $arrRelGrupoContatoDTO[$i]->getNumIdContato();
    			}
    			
    			if (count($arr)>0){
    			  $objContatoDTO->setNumIdContato($arr,InfraDTO::$OPER_IN);
    			}else{
    			  $objContatoDTO->setNumIdContato(null);
    			}
    		}
  		}
  		
  		if ($objContatoDTO->isSetStrPalavrasPesquisa()){  			
  		  if (trim($objContatoDTO->getStrPalavrasPesquisa())!=''){

  		    $strPalavrasPesquisa = InfraString::prepararIndexacao($objContatoDTO->getStrPalavrasPesquisa(),false);

    			$arrPalavrasPesquisa = explode(' ',$strPalavrasPesquisa);
   
    			$numPalavrasPesquisa = count($arrPalavrasPesquisa);
    			
    			if ($numPalavrasPesquisa){
       			for($i=0;$i<$numPalavrasPesquisa;$i++){
     			    $arrPalavrasPesquisa[$i] = '%'.$arrPalavrasPesquisa[$i].'%';
       			}
     			
      			if ($numPalavrasPesquisa==1){
      				$objContatoDTO->setStrIdxContato($arrPalavrasPesquisa[0],InfraDTO::$OPER_LIKE);
      			}else{
      				$a = array_fill(0,$numPalavrasPesquisa,'IdxContato');
      				$b = array_fill(0,$numPalavrasPesquisa,InfraDTO::$OPER_LIKE);
      				$d = array_fill(0,$numPalavrasPesquisa-1,InfraDTO::$OPER_LOGICO_AND);
      				$objContatoDTO->adicionarCriterio($a,$b,$arrPalavrasPesquisa,$d);
      			}
     			}
    		}else{
    			$objContatoDTO->unSetStrPalavrasPesquisa();
    		}
  		}

  		if ($objContatoDTO->isSetNumIdTipoContato()){
  		  if ($objContatoDTO->getNumIdTipoContato()==null) {
          $objContatoDTO->unSetNumIdTipoContato();
        }
  		}

      //Se informou pelo menos uma data  	
      if ($objContatoDTO->isSetDtaNascimentoInicio() || $objContatoDTO->isSetDtaNascimentoFim()){
        
        if (!$objContatoDTO->isSetDtaNascimentoInicio() || InfraString::isBolVazia($objContatoDTO->getDtaNascimentoInicio())){
          $objInfraException->lancarValidacao('Data inicial do período de nascimento não informada.');
        }

        if (!$objContatoDTO->isSetDtaNascimentoFim() || InfraString::isBolVazia($objContatoDTO->getDtaNascimentoFim())){
          $objInfraException->lancarValidacao('Data final do período de nascimento não informada.');
        }

        $strAnoAtual = Date("Y");
        $strDataInicio = $objContatoDTO->getDtaNascimentoInicio().'/'.$strAnoAtual;
        
        if (!InfraData::validarData($strDataInicio)){
          $objInfraException->lancarValidacao('Data inicial do período de nascimento inválida.');
        }

        $strDataFim = $objContatoDTO->getDtaNascimentoFim().'/'.$strAnoAtual;
        if (!InfraData::validarData($strDataFim)){
          $objInfraException->lancarValidacao('Data final do período de nascimento inválida.');
        }

        if (InfraData::compararDatas($strDataInicio,$strDataFim)<0){
          $objInfraException->lancarValidacao('Período de datas de nascimento inválido.');
        }

        $objContatoDTO->setDtaNascimento(null,InfraDTO::$OPER_DIFERENTE);
                
        $dto = new ContatoDTO();
        $dto->setDistinct(true);
        $dto->retDtaNascimento();
        $dto->setDtaNascimento(null,InfraDTO::$OPER_DIFERENTE);
        $arr = $this->listarRN0325($dto);
        
        
        $arrCriterios = array();
        foreach($arr as $dto){
          $strAno = substr($dto->getDtaNascimento(),6,4);
          if (!in_array($strAno,$arrCriterios)){
            //Adiciona critério com o nome igual ao do ano
            
            $strDataIni = $objContatoDTO->getDtaNascimentoInicio().'/'.$strAno;
            $strDataFim = $objContatoDTO->getDtaNascimentoFim().'/'.$strAno;
            
            if (!InfraData::validarData($strDataIni)){
              if (substr($strDataIni,0,5)=='29/02'){
                $strDataIni = '01/03/'.$strAno;
              }else{
                throw new InfraException('Data inicial inválida.');
              }
            }
            if (!InfraData::validarData($strDataFim)){
              if (substr($strDataFim,0,5)=='29/02'){
                $strDataFim = '28/02/'.$strAno;
              }else{
                throw new InfraException('Data final inválida.');
              }
            }
            
            $objContatoDTO->adicionarCriterio(array('Nascimento','Nascimento'),
                                              array(InfraDTO::$OPER_MAIOR_IGUAL,InfraDTO::$OPER_MENOR_IGUAL),
                                              array($strDataIni,$strDataFim),
                                              array(InfraDTO::$OPER_LOGICO_AND),
                                              $strAno);
            $arrCriterios[] = $strAno;
          }
        }
        
        $arrOperadores = array_fill(0,count($arrCriterios)-1,InfraDTO::$OPER_LOGICO_OR);
        $objContatoDTO->agruparCriterios($arrCriterios,$arrOperadores);
      }

      $objInfraException->lancarValidacoes();

      return $this->listarRN0325($objContatoDTO);
      
  		
      //Auditoria
    }catch(Exception $e){
      throw new InfraException('Erro pesquisando Contato.',$e);
    }
  }
  
  protected function listarGrupoRN0566Controlado(ContatoDTO $objContatoDTO){
    $objRelGrupoContatoDTO = new RelGrupoContatoDTO();
    $objRelGrupoContatoDTO->retNumIdContato();
    $objRelGrupoContatoDTO->setNumIdGrupoContato($objContatoDTO->getNumIdGrupoContato());
    
    $objRelGrupoContatoRN = new RelGrupoContatoRN();
    $arr = $objRelGrupoContatoRN->listarRN0463($objRelGrupoContatoDTO);
    
    if (count($arr)==0){
      return array();
    }
    
    $objContatoDTO->setNumIdContato(InfraArray::converterArrInfraDTO($arr,'IdContato'),InfraDTO::$OPER_IN);
    
    $ret = $this->listarRN0325($objContatoDTO);
    
    return $ret;
  }
  
  private function validarDblCnpjRN0372(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getDblCnpj())){  	
      $objContatoDTO->setDblCnpj(null);
    }else{

      if ($objContatoDTO->getStrStaNatureza()==ContatoRN::$TN_PESSOA_FISICA){
        $objInfraException->adicionarValidacao('Não é possível informar CNPJ para Pessoa Física.');
      }

	  	if(!InfraUtil::validarCnpj($objContatoDTO->getDblCnpj())){
	  		$objInfraException->adicionarValidacao('Número de CNPJ inválido.');
	  	}
      $objContatoDTO->setDblCnpj(InfraUtil::retirarFormatacao($objContatoDTO->getDblCnpj()));
		}
  }

  private function validarNumIdTipoContatoRN0367(ContatoDTO $objContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objContatoDTO->getNumIdTipoContato())){
      $objInfraException->adicionarValidacao('Tipo do Contato não informado.');
    }
  }
  
  protected function cadastrarContextoTemporarioControlado(ContatoDTO $parObjContatoDTO){
    try{

      $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
      $numIdTipoContato = $objInfraParametro->getValor('ID_TIPO_CONTATO_TEMPORARIO');
    	
    	$objContatoDTO = new ContatoDTO();
    	$objContatoDTO->retNumIdContato();
      $objContatoDTO->setStrNome(trim($parObjContatoDTO->getStrNome()));
      
      $arrObjContatoDTO = $this->listarRN0325($objContatoDTO);
      
      if (count($arrObjContatoDTO)){
      	return $arrObjContatoDTO[0];
      }
      
      //cadastra contato
      $objContatoDTO = new ContatoDTO();
      $objContatoDTO->setNumIdContato(null);
    	$objContatoDTO->setNumIdTipoContato($numIdTipoContato);
      $objContatoDTO->setNumIdContatoAssociado(null);
      $objContatoDTO->setStrStaNatureza(ContatoRN::$TN_PESSOA_FISICA);
    	$objContatoDTO->setDblCnpj(null);
    	$objContatoDTO->setNumIdCargo(null);

    	if ($parObjContatoDTO->isSetStrSigla()){
    		$objContatoDTO->setStrSigla($parObjContatoDTO->getStrSigla());
    	}else{
    	  $objContatoDTO->setStrSigla(null);	
    	}
    	
    	
      $objContatoDTO->setStrNome($parObjContatoDTO->getStrNome());
      $objContatoDTO->setDtaNascimento(null);
      $objContatoDTO->setStrStaGenero(null);

      if ($parObjContatoDTO->isSetDblCpf()){
        $objContatoDTO->setDblCpf($parObjContatoDTO->getDblCpf());
      }else{
        $objContatoDTO->setDblCpf(null);
      }
      
      $objContatoDTO->setDblRg(null);
      $objContatoDTO->setStrOrgaoExpedidor(null);
      $objContatoDTO->setStrMatricula(null);
      $objContatoDTO->setStrMatriculaOab(null);
      $objContatoDTO->setStrTelefoneFixo(null);
      $objContatoDTO->setStrTelefoneCelular(null);
      $objContatoDTO->setStrEmail(null);
      $objContatoDTO->setStrSitioInternet(null);
      $objContatoDTO->setStrEndereco(null);
      $objContatoDTO->setStrComplemento(null);
      $objContatoDTO->setStrBairro(null);
      $objContatoDTO->setNumIdUf(null);
      $objContatoDTO->setNumIdCidade(null);
      $objContatoDTO->setNumIdPais(null);
      $objContatoDTO->setStrCep(null);
      $objContatoDTO->setStrObservacao(null);
      $objContatoDTO->setStrSinEnderecoAssociado('N');
      $objContatoDTO->setStrSinAtivo('S');
      
      $objContatoDTO = $this->cadastrarRN0322($objContatoDTO);      
      
      return $objContatoDTO;
      
    }catch(Exception $e){
      throw new InfraException('Erro cadastrando contexto temporário.',$e);
    }

  }

  protected function substituirConectado(ContatoSubstituirDTO $objContatoSubstituirDTO){
    try{

      SessaoSEI::getInstance()->validarAuditarPermissao('contato_substituir_temporario',__METHOD__,$objContatoSubstituirDTO);
      
    	// Faz a alteracao de banco para os Contextos
    	$arrIdProtocolos = $this->substituirInterno($objContatoSubstituirDTO);
    	
    	$objIndexacaoRN  = new IndexacaoRN();
    	$objIndexacaoDTO = new IndexacaoDTO();
    	
    	$objIndexacaoDTO->setArrIdProtocolos($arrIdProtocolos);
    	$objIndexacaoDTO->setStrStaOperacao(IndexacaoRN::$TO_PROTOCOLO_METADADOS);
      $objIndexacaoRN->indexarProtocolo($objIndexacaoDTO);
      
    }catch(Exception $e){
      throw new InfraException('Erro substituindo contexto temporário.',$e);
    }

  }

  protected function substituirInternoControlado(ContatoSubstituirDTO $objContatoSubstituirDTO){
    try{
      
			ini_set('max_execution_time','0');
			ini_set('memory_limit','1024M');
    	
    	$objInfraException = new InfraException();
    	
    	
    	$arrIdContato = InfraArray::converterArrInfraDTO($objContatoSubstituirDTO->getArrObjContato(),'IdContato');
    	$numIdContato = $objContatoSubstituirDTO->getNumIdContato(); 
    	
	    if (in_array($numIdContato,$arrIdContato)){
	      $objInfraException->lancarValidacao('Contato consta na lista para substituição.');
	    }
    	
	    $objParticipanteDTO = new ParticipanteDTO();
			$objParticipanteDTO->retNumIdParticipante();
			$objParticipanteDTO->retDblIdProtocolo();
      $objParticipanteDTO->retStrStaParticipacao();
			$objParticipanteDTO->setNumIdContato($arrIdContato,InfraDTO::$OPER_IN);
			
			$objParticipanteRN 	= new ParticipanteRN();
      $arrObjParticipanteDTO = $objParticipanteRN->listarRN0189($objParticipanteDTO);

      foreach ($arrObjParticipanteDTO as $objParticipanteDTO) {
      	
				$dto = new ParticipanteDTO();
				$dto->setNumIdContato($numIdContato);
        $dto->setStrStaParticipacao($objParticipanteDTO->getStrStaParticipacao());
				$dto->setDblIdProtocolo($objParticipanteDTO->getDblIdProtocolo());
				
				if ($objParticipanteRN->contarRN0461($dto)==0){
				  $dto = new ParticipanteDTO();
				  $dto->setNumIdContato($numIdContato);
				  $dto->setNumIdParticipante($objParticipanteDTO->getNumIdParticipante());
				
				  $objParticipanteRN->alterarRN0889($dto);
				}else{
				  $objParticipanteRN->excluirRN0223(array($objParticipanteDTO));
				}
      }

      $objRelGrupoContatoDTO = new RelGrupoContatoDTO();
      $objRelGrupoContatoDTO->retNumIdGrupoContato();
      $objRelGrupoContatoDTO->retNumIdContato();
      $objRelGrupoContatoDTO->setNumIdContato($arrIdContato,InfraDTO::$OPER_IN);

      $objRelGrupoContatoRN 	= new RelGrupoContatoRN();
      $arrObjRelGrupoContatoDTO = $objRelGrupoContatoRN->listarRN0463($objRelGrupoContatoDTO);
      $arrIdGrupoContato = array_unique(InfraArray::converterArrInfraDTO($arrObjRelGrupoContatoDTO,'IdGrupoContato'));

      $objRelGrupoContatoRN->excluirRN0464($arrObjRelGrupoContatoDTO);

      foreach($arrIdGrupoContato as $numIdGrupoContato){

        $objRelGrupoContatoDTO = new RelGrupoContatoDTO();
        $objRelGrupoContatoDTO->setNumIdGrupoContato($numIdGrupoContato);
        $objRelGrupoContatoDTO->setNumIdContato($numIdContato);

        if ($objRelGrupoContatoRN->contarRN0465($objRelGrupoContatoDTO)==0){
          $objRelGrupoContatoRN->cadastrarRN0462($objRelGrupoContatoDTO);
        }
      }

      foreach ($arrIdContato as $numIdContatoAtual) {
      	
      	$objContatoDTO 	= new ContatoDTO();
      	$objContatoDTO->setNumIdContato($numIdContatoAtual);
      	
      	try{
          $this->excluirRN0326(array($objContatoDTO));
      	}catch(Exception $e2){
      	  $this->desativarRN0451(array($objContatoDTO));
      	}
      }
      
      return array_unique(InfraArray::converterArrInfraDTO($arrObjParticipanteDTO,'IdProtocolo'));
      
    }catch(Exception $e){
      throw new InfraException('Erro substituindo internamente contexto temporário.',$e);
    }
  }

  protected function removerDadosPrivadosConectado($arrObjContatoDTO){
    try {

      $bolAcessoContato = true;
      $bolAcessoContatoAssociado = true;

      if (count($arrObjContatoDTO)) {

        $arrIdTipoContato = array();
        foreach($arrObjContatoDTO as $objContatoDTO){
          $arrIdTipoContato[$objContatoDTO->getNumIdTipoContato()] = 0;
          if ($objContatoDTO->getNumIdTipoContatoAssociado()!=null){
            $arrIdTipoContato[$objContatoDTO->getNumIdTipoContatoAssociado()] = 0;
          }
        }

        $objPesquisaTipoContatoDTO = new PesquisaTipoContatoDTO();
        $objPesquisaTipoContatoDTO->setStrStaAcesso(TipoContatoRN::$TA_CONSULTA_COMPLETA);
        $objPesquisaTipoContatoDTO->setArrIdTipoContato(array_keys($arrIdTipoContato));

        $objTipoContatoRN = new TipoContatoRN();
        $arrNumIdTipoContato = $objTipoContatoRN->pesquisarAcessoUnidade($objPesquisaTipoContatoDTO);

        foreach($arrObjContatoDTO as $objContatoDTO) {
          if (!in_array($objContatoDTO->getNumIdTipoContato(),$arrNumIdTipoContato)) {

            $objContatoDTO->setStrEndereco(null);
            $objContatoDTO->setStrComplemento(null);
            $objContatoDTO->setStrBairro(null);
            $objContatoDTO->setNumIdUf(null);
            $objContatoDTO->setStrSiglaUf(null);
            $objContatoDTO->setNumIdCidade(null);
            $objContatoDTO->setStrNomeCidade(null);
            $objContatoDTO->setNumIdPais(null);
            $objContatoDTO->setStrNomePais(null);
            $objContatoDTO->setStrCep(null);

            $objContatoDTO->setDblCpf(null);
            $objContatoDTO->setDblRg(null);
            $objContatoDTO->setStrOrgaoExpedidor(null);
            $objContatoDTO->setDtaNascimento(null);

            $objContatoDTO->setStrObservacao(null);

            $bolAcessoContato = false;
          }

          if (!in_array($objContatoDTO->getNumIdTipoContatoAssociado(),$arrNumIdTipoContato)) {
            $objContatoDTO->setStrEnderecoContatoAssociado(null);
            $objContatoDTO->setStrComplementoContatoAssociado(null);
            $objContatoDTO->setStrBairroContatoAssociado(null);
            $objContatoDTO->setNumIdUfContatoAssociado(null);
            $objContatoDTO->setStrSiglaUfContatoAssociado(null);
            $objContatoDTO->setNumIdCidadeContatoAssociado(null);
            $objContatoDTO->setStrNomeCidadeContatoAssociado(null);
            $objContatoDTO->setNumIdPaisContatoAssociado(null);
            $objContatoDTO->setStrNomePaisContatoAssociado(null);
            $objContatoDTO->setStrCepContatoAssociado(null);

            $bolAcessoContatoAssociado = false;
          }
        }
      }

      if (!$bolAcessoContato && $bolAcessoContatoAssociado){
        $ret = 1;
      }else if ($bolAcessoContato && !$bolAcessoContatoAssociado){
        $ret = 2;
      }else if ($bolAcessoContato && $bolAcessoContatoAssociado){
        $ret = 3;
      }else{
        $ret = 0;
      }

      return $ret;

    } catch (Exception $e) {
      throw new InfraException('Erro removendo dados privados.', $e);
    }
  }

  protected function listarComEnderecoConectado(ContatoDTO $parObjContatoDTO){
    try{

      $objContatoDTO = clone($parObjContatoDTO);
      $objContatoDTO->retNumIdContato();
      $objContatoDTO->retNumIdContatoAssociado();
      $objContatoDTO->retStrSinEnderecoAssociado();
      $objContatoDTO->retStrSinEnderecoAssociadoAssociado();
      $objContatoDTO->retNumIdTipoContato();
      $objContatoDTO->retNumIdTipoContatoAssociado();

      $objContatoDTO->retStrEndereco();
      $objContatoDTO->retStrComplemento();
      $objContatoDTO->retStrBairro();
      $objContatoDTO->retNumIdCidade();
      $objContatoDTO->retStrNomeCidade();
      $objContatoDTO->retNumIdUf();
      $objContatoDTO->retStrSiglaUf();
      $objContatoDTO->retNumIdPais();
      $objContatoDTO->retStrNomePais();
      $objContatoDTO->retStrCep();

      $objContatoDTO->retStrEnderecoContatoAssociado();
      $objContatoDTO->retStrComplementoContatoAssociado();
      $objContatoDTO->retStrBairroContatoAssociado();
      $objContatoDTO->retNumIdCidadeContatoAssociado();
      $objContatoDTO->retStrNomeCidadeContatoAssociado();
      $objContatoDTO->retNumIdUfContatoAssociado();
      $objContatoDTO->retStrSiglaUfContatoAssociado();
      $objContatoDTO->retNumIdPaisContatoAssociado();
      $objContatoDTO->retStrNomePaisContatoAssociado();
      $objContatoDTO->retStrCepContatoAssociado();

      $arrObjContatoDTO = InfraArray::indexarArrInfraDTO($this->listarRN0325($objContatoDTO),'IdContato');

      $this->removerDadosPrivados($arrObjContatoDTO);

      $arrObjContatoDTOAssociado = array();

      foreach ($arrObjContatoDTO as $objContatoDTO) {

        if ($objContatoDTO->getStrSinEnderecoAssociado() == 'S' && $objContatoDTO->getNumIdContatoAssociado() != $objContatoDTO->getNumIdContato()) {

          if ($objContatoDTO->getStrSinEnderecoAssociadoAssociado() == 'N') {

            $objContatoDTO->setStrEndereco($objContatoDTO->getStrEnderecoContatoAssociado());
            $objContatoDTO->setStrComplemento($objContatoDTO->getStrComplementoContatoAssociado());
            $objContatoDTO->setStrBairro($objContatoDTO->getStrBairroContatoAssociado());
            $objContatoDTO->setNumIdCidade($objContatoDTO->getNumIdCidadeContatoAssociado());
            $objContatoDTO->setStrNomeCidade($objContatoDTO->getStrNomeCidadeContatoAssociado());
            $objContatoDTO->setNumIdUf($objContatoDTO->getNumIdUfContatoAssociado());
            $objContatoDTO->setStrSiglaUf($objContatoDTO->getStrSiglaUfContatoAssociado());
            $objContatoDTO->setNumIdPais($objContatoDTO->getNumIdPaisContatoAssociado());
            $objContatoDTO->setStrNomePais($objContatoDTO->getStrNomePaisContatoAssociado());
            $objContatoDTO->setStrCep($objContatoDTO->getStrCepContatoAssociado());


          } else {

            $objContatoDTOAssociado = new ContatoDTO();
            $objContatoDTOAssociado->setNumIdContatoAssociado($objContatoDTO->getNumIdContatoAssociado());

            do {

              $dto = new ContatoDTO();
              $dto->setBolExclusaoLogica(false);
              $dto->retNumIdContatoAssociado();
              $dto->retStrSinEnderecoAssociado();
              $dto->retNumIdTipoContato();
              $dto->retNumIdTipoContatoAssociado();
              $dto->retNumIdContato();
              $dto->retStrNome();
              $dto->retStrEndereco();
              $dto->retStrComplemento();
              $dto->retStrBairro();
              $dto->retNumIdUf();
              $dto->retStrSiglaUf();
              $dto->retNumIdCidade();
              $dto->retStrNomeCidade();
              $dto->retNumIdPais();
              $dto->retStrNomePais();
              $dto->retStrCep();
              $dto->setNumIdContato($objContatoDTOAssociado->getNumIdContatoAssociado());

              $objContatoDTOAssociado = $this->consultarRN0324($dto);

            } while ($objContatoDTOAssociado != null && $objContatoDTOAssociado->getStrSinEnderecoAssociado() == 'S');

            if ($objContatoDTOAssociado != null) {

              $objContatoDTOAssociado->setNumIdTipoContato($objContatoDTO->getNumIdTipoContato());
              $objContatoDTOAssociado->setNumIdTipoContatoAssociado($objContatoDTO->getNumIdTipoContato());

              $arrObjContatoDTOAssociado[$objContatoDTO->getNumIdContato()] = $objContatoDTOAssociado;

            }
          }
        }
      }

      if (count($arrObjContatoDTOAssociado)){

        $this->removerDadosPrivados($arrObjContatoDTOAssociado);

        foreach ($arrObjContatoDTOAssociado as $numIdContato => $objContatoDTOAssociado){

          $objContatoDTO = $arrObjContatoDTO[$numIdContato];

          $objContatoDTO->setStrEndereco($objContatoDTOAssociado->getStrEndereco());
          $objContatoDTO->setStrComplemento($objContatoDTOAssociado->getStrComplemento());
          $objContatoDTO->setStrBairro($objContatoDTOAssociado->getStrBairro());
          $objContatoDTO->setNumIdCidade($objContatoDTOAssociado->getNumIdCidade());
          $objContatoDTO->setStrNomeCidade($objContatoDTOAssociado->getStrNomeCidade());
          $objContatoDTO->setNumIdUf($objContatoDTOAssociado->getNumIdUf());
          $objContatoDTO->setStrSiglaUf($objContatoDTOAssociado->getStrSiglaUf());
          $objContatoDTO->setNumIdPais($objContatoDTOAssociado->getNumIdPais());
          $objContatoDTO->setStrNomePais($objContatoDTOAssociado->getStrNomePais());
          $objContatoDTO->setStrCep($objContatoDTOAssociado->getStrCep());

          $arrObjContatoDTO[$numIdContato] = $objContatoDTO;
        }
      }

      foreach($arrObjContatoDTO as $objContatoDTO){
        $objContatoDTO->unSetStrEnderecoContatoAssociado();
        $objContatoDTO->unSetStrComplementoContatoAssociado();
        $objContatoDTO->unSetStrBairroContatoAssociado();
        $objContatoDTO->unSetNumIdCidadeContatoAssociado();
        $objContatoDTO->unSetStrNomeCidadeContatoAssociado();
        $objContatoDTO->unSetNumIdUfContatoAssociado();
        $objContatoDTO->unSetStrSiglaUfContatoAssociado();
        $objContatoDTO->unSetNumIdPaisContatoAssociado();
        $objContatoDTO->unSetStrNomePaisContatoAssociado();
        $objContatoDTO->unSetStrCepContatoAssociado();
      }

      return array_values($arrObjContatoDTO);

    }catch(Exception $e){
      throw new InfraException('Erro listando com endereço associado.',$e);
    }
  }

}
?>