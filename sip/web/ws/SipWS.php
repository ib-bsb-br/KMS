<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 27/11/2006 - criado por mga
*
*
*/

require_once dirname(__FILE__).'/../Sip.php';

class SipWS extends InfraWS {

  public function getObjInfraLog(){
    return LogSip::getInstance();
  }

  private function simularLogin(){
    try{

      SessaoSip::getInstance(false);

      $objInfraParametro = new InfraParametro(BancoSip::getInstance());

      $objUsuarioDTO = new UsuarioDTO();
      $objUsuarioDTO->retNumIdUsuario();
      $objUsuarioDTO->retNumIdOrgao();
      $objUsuarioDTO->retStrSiglaOrgao();
      $objUsuarioDTO->retStrSigla();
      $objUsuarioDTO->retStrNome();

      $objUsuarioDTO->setNumIdUsuario($objInfraParametro->getValor('ID_USUARIO_SIP'));

      $objUsuarioRN = new UsuarioRN();
      $objUsuarioDTO = $objUsuarioRN->consultar($objUsuarioDTO);

      if ($objUsuarioDTO==null){
        throw new InfraException('Usuário ID_USUARIO_SIP não encontrado.');
      }

      //Sistema
      $objSistemaDTO = new SistemaDTO();
      $objSistemaDTO->retNumIdSistema();
      $objSistemaDTO->retNumIdOrgao();
      $objSistemaDTO->setNumIdSistema($objInfraParametro->getValor('ID_SISTEMA_SIP'));

      $objSistemaRN = new SistemaRN();
      $objSistemaDTO = $objSistemaRN->consultar($objSistemaDTO);

      if ($objSistemaDTO==null){
        throw new InfraException('Sistema ID_SISTEMA_SIP não encontrado.');
      }

      SessaoSip::getInstance()->setNumIdOrgaoSistema($objSistemaDTO->getNumIdOrgao());
      SessaoSip::getInstance()->setNumIdSistema($objSistemaDTO->getNumIdSistema());

      //Usuário
      SessaoSip::getInstance()->setNumIdOrgaoUsuario($objUsuarioDTO->getNumIdOrgao());
      SessaoSip::getInstance()->setStrSiglaOrgaoUsuario($objUsuarioDTO->getStrSiglaOrgao());
      SessaoSip::getInstance()->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
      SessaoSip::getInstance()->setStrSiglaUsuario($objUsuarioDTO->getStrSigla());
      SessaoSip::getInstance()->setStrNomeUsuario($objUsuarioDTO->getStrNome());

    }catch(Exception $e){
      throw new InfraException('Erro simulando login.',$e);
    }
  }

  public function validarLogin($IdLogin,$IdSistema,$IdUsuario,$HashAgente){
    try{

      /*
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->limpar();

      InfraDebug::getInstance()->gravar(__METHOD__);
      InfraDebug::getInstance()->gravar('ID LOGIN:'.$IdLogin);
      InfraDebug::getInstance()->gravar('ID SISTEMA:'.$IdSistema);
      InfraDebug::getInstance()->gravar('ID USUARIO:'.$IdUsuario);
      InfraDebug::getInstance()->gravar('HASH AGENTE:'.$HashAgente);
      */

      $this->simularLogin();

      $objLoginDTO = new LoginDTO();
      $objLoginDTO->setStrIdLogin($IdLogin);
      $objLoginDTO->setNumIdSistema($IdSistema);
      $objLoginDTO->setNumIdUsuario($IdUsuario);
      $objLoginDTO->setStrHashAgente($HashAgente);

      $objLoginRN = new LoginRN();
      $objInfraSessaoDTO = $objLoginRN->logar($objLoginDTO);

      //LogSip::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());

      return $objInfraSessaoDTO;

    }catch(Exception $e){
      $this->processarExcecao($e);
    }
  }

  public function loginUnificado($SiglaOrgaoSistema, $SiglaSistema, $Link, $HashAgente){
    try{

      /*
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->limpar();

      InfraDebug::getInstance()->gravar(__METHOD__);
      InfraDebug::getInstance()->gravar('SIGLA ORGAO SISTEMA:'.$SiglaOrgaoSistema);
      InfraDebug::getInstance()->gravar('SIGLA SISTEMA:'.$SiglaSistema);
      InfraDebug::getInstance()->gravar('LINK:'.$Link);
      InfraDebug::getInstance()->gravar('HASH AGENTE:'.$HashAgente);
      */

      $this->simularLogin();

      $objLoginDTO = new LoginDTO();
      $objLoginDTO->setStrSiglaOrgaoSistema($SiglaOrgaoSistema);
      $objLoginDTO->setStrSiglaSistema($SiglaSistema);
      $objLoginDTO->setStrLink($Link);
      $objLoginDTO->setStrHashAgente($HashAgente);

      $objLoginRN = new LoginRN();
      $objInfraSessaoDTO = $objLoginRN->loginUnificado($objLoginDTO);

      //LogSip::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());

      return $objInfraSessaoDTO;

    }catch(Exception $e){
      $this->processarExcecao($e);
    }
  }

  public function removerLogin($SiglaOrgaoSistema, $SiglaSistema, $Link, $IdUsuario){
    try{

      /*
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->limpar();

      InfraDebug::getInstance()->gravar(__METHOD__);
      InfraDebug::getInstance()->gravar('SIGLA ORGAO SISTEMA:'.$SiglaOrgaoSistema);
      InfraDebug::getInstance()->gravar('SIGLA SISTEMA:'.$SiglaSistema);
      InfraDebug::getInstance()->gravar('LINK:'.$Link);
      InfraDebug::getInstance()->gravar('HASH AGENTE:'.$HashAgente);
      */

      $this->simularLogin();

      $objLoginDTO = new LoginDTO();
      $objLoginDTO->setStrSiglaOrgaoSistema($SiglaOrgaoSistema);
      $objLoginDTO->setStrSiglaSistema($SiglaSistema);
      $objLoginDTO->setStrLink($Link);
      $objLoginDTO->setNumIdUsuario($IdUsuario);

      $objLoginRN = new LoginRN();
      $objInfraSessaoDTO = $objLoginRN->removerLogin($objLoginDTO);

      //LogSip::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());

      return $objInfraSessaoDTO;

    }catch(Exception $e){
      $this->processarExcecao($e);
    }
  }

  public function carregarUnidades($IdSistema,$IdUsuario,$IdUnidade){
    try{

      $this->validarAcessoAutorizado(ConfiguracaoSip::getInstance()->getValor('HostWebService','Pesquisa'));

      //InfraDebug::getInstance()->setBolLigado(false);
      //InfraDebug::getInstance()->setBolDebugInfra(true);
      //InfraDebug::getInstance()->limpar();

      $this->simularLogin();

      if (InfraString::isBolVazia($IdUsuario)){

        $objSistemaDTO = new SistemaDTO();
        $objSistemaDTO->setNumIdSistema($IdSistema);
        $objSistemaDTO->setNumIdUnidade($IdUnidade);

        $objSistemaRN = new SistemaRN();
        $ret = $objSistemaRN->listarUnidades($objSistemaDTO);

      }else{

        $objPermissaoDTO = new PermissaoDTO();
        $objPermissaoDTO->setNumIdSistema($IdSistema);
        $objPermissaoDTO->setNumIdUsuario($IdUsuario);

        $objPermissaoRN = new PermissaoRN();
        $ret = $objPermissaoRN->listarUnidades($objPermissaoDTO);

      }

      //LogSip::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());

      return $ret;

    }catch(Exception $e){
      $this->processarExcecao($e);
    }
  }

  public function carregarUsuarios($IdSistema, $IdUnidade, $Recurso, $Perfil){
    try{

      $this->validarAcessoAutorizado(ConfiguracaoSip::getInstance()->getValor('HostWebService','Pesquisa'));

      /*
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(true);
      InfraDebug::getInstance()->limpar();
      */

      $this->simularLogin();

      $objPermissaoDTO = new PermissaoDTO();
      $objPermissaoDTO->setNumIdSistema($IdSistema);

      if (!InfraString::isBolVazia($IdUnidade)){
        $objPermissaoDTO->setNumIdUnidade($IdUnidade);
      }

      if (!InfraString::isBolVazia($Recurso)){
        $objPermissaoDTO->setStrNomeRecurso($Recurso);
      }

      if (!InfraString::isBolVazia($Perfil)){
        $objPermissaoDTO->setStrNomePerfil($Perfil);
      }

      $objPermissaoRN = new PermissaoRN();
      $ret = $objPermissaoRN->carregarUsuarios($objPermissaoDTO);

      //LogSip::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());

      return $ret;


    }catch(Exception $e){
      $this->processarExcecao($e);
    }
  }

  public function carregarUsuario($IdSistema, $TipoServidorAutenticacao, $IdOrgaoUsuario, $SiglaUsuario){
    try{

      $this->validarAcessoAutorizado(ConfiguracaoSip::getInstance()->getValor('HostWebService','Pesquisa'));

      /*
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->limpar();

      InfraDebug::getInstance()->gravar(__METHOD__);
      InfraDebug::getInstance()->gravar('ID SISTEMA:'.$IdSistema);
      InfraDebug::getInstance()->gravar('TIPO SERVIDOR AUTENTICACAO:'.$TipoServidorAutenticacao);
      InfraDebug::getInstance()->gravar('ID ORGAO USUARIO:'.$IdOrgaoUsuario);
      InfraDebug::getInstance()->gravar('SIGLA USUARIO:'.$SiglaUsuario);
      */

      $this->simularLogin();

      $objPermissaoDTO = new PermissaoDTO();
      $objPermissaoDTO->setNumIdSistema($IdSistema);
      $objPermissaoDTO->setNumIdOrgaoUsuario($IdOrgaoUsuario);
      $objPermissaoDTO->setStrSiglaUsuario($SiglaUsuario);
      $objPermissaoDTO->setStrTipoServidorAutenticacao($TipoServidorAutenticacao);

      $objPermissaoRN = new PermissaoRN();
      $ret = $objPermissaoRN->carregarUsuario($objPermissaoDTO);

      //LogSip::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());

      return $ret;

    }catch(Exception $e){
      $this->processarExcecao($e);
    }
  }

  public function replicarUsuario($Usuarios){
    try {

      $this->validarAcessoAutorizado(ConfiguracaoSip::getInstance()->getValor('HostWebService','Replicacao'));

      /*
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->limpar();

      InfraDebug::getInstance()->gravar(__METHOD__);
      InfraDebug::getInstance()->gravar('USUARIOS:'.count($Usuarios));
      */

      $this->simularLogin();

      $objUsuarioRN = new UsuarioRN();

      $objInfraException = new InfraException();

      foreach($Usuarios as $Usuario) {

        $StaOperacao = $Usuario['StaOperacao'];
        $IdOrgao = $Usuario['IdOrgao'];
        $IdOrigem = $Usuario['IdOrigem'];
        $Sigla = $Usuario['Sigla'];
        $Nome = $Usuario['Nome'];

        /*
        InfraDebug::getInstance()->gravar(' ');
        InfraDebug::getInstance()->gravar('OPERACAO:'.$StaOperacao);
        InfraDebug::getInstance()->gravar('ID ORGAO:'.$IdOrgao);
        InfraDebug::getInstance()->gravar('ID ORIGEM:'.$IdOrigem);
        InfraDebug::getInstance()->gravar('SIGLA:'.$Sigla);
        InfraDebug::getInstance()->gravar('NOME:'.$Nome);
        */

        try{

          if (InfraString::isBolVazia($IdOrigem)){
            throw new InfraException('Identificador do sistema de origem não informado.');
          }

          $objUsuarioDTOBanco = new UsuarioDTO();
          $objUsuarioDTOBanco->setBolExclusaoLogica(false);
          $objUsuarioDTOBanco->retNumIdUsuario();
          $objUsuarioDTOBanco->retNumIdOrgao();
          $objUsuarioDTOBanco->retStrIdOrigem();
          $objUsuarioDTOBanco->retStrSigla();
          $objUsuarioDTOBanco->retStrNome();
          $objUsuarioDTOBanco->setStrIdOrigem($IdOrigem);
          $objUsuarioDTOBanco = $objUsuarioRN->consultar($objUsuarioDTOBanco);

          if ($StaOperacao == 'A'){

            $objReplicarUsuarioRhDTO = new ReplicarUsuarioRhDTO();
            $objReplicarUsuarioRhDTO->setStrStaOperacao($StaOperacao);
            $objReplicarUsuarioRhDTO->setNumIdOrgao($IdOrgao);
            $objReplicarUsuarioRhDTO->setStrIdOrigem($IdOrigem);
            $objReplicarUsuarioRhDTO->setStrSigla($Sigla);
            $objReplicarUsuarioRhDTO->setStrNome($Nome);
            $objUsuarioRN->replicar($objReplicarUsuarioRhDTO);

          }else if ($StaOperacao=='E'){
            if ($objUsuarioDTOBanco!=null){
              try{
                $objUsuarioRN->excluir(array($objUsuarioDTOBanco));
              }catch(Exception $e){
                //erro de integridade então desativa
                $objUsuarioRN->desativar(array($objUsuarioDTOBanco));
              }
            }

          }else if ($StaOperacao=='D'){
            if ($objUsuarioDTOBanco!=null){
              $objUsuarioRN->desativar(array($objUsuarioDTOBanco));
            }
          }else if ($StaOperacao=='R'){
            if ($objUsuarioDTOBanco!=null){
              $objUsuarioRN->reativar(array($objUsuarioDTOBanco));
            }
          }else{
            throw new InfraException('Operação '.$StaOperacao.' inválida.');
          }
        }catch(Exception $e){
          $objInfraException->adicionarValidacao("\n * ".$Sigla.' ('.$IdOrigem.'): '.$e->__toString()."\n");

          if (!($e instanceof InfraException && $e->contemValidacoes())){
            try {
              LogSip::getInstance()->gravar(InfraException::inspecionar($e));
            }catch(Exception $e2){}
          }

        }
      }

      if ($objInfraException->contemValidacoes()){
        $objInfraException->lancarValidacoes();
      }

      //LogSip::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());

      return true;

    }catch(Exception $e){
      $this->processarExcecao($e);
    }

    return false;
  }

  public function replicarPermissao($Permissoes){
    try {

      $this->validarAcessoAutorizado(ConfiguracaoSip::getInstance()->getValor('HostWebService','Replicacao'));

      /*
      InfraDebug::getInstance()->setBolLigado(true);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->limpar();

      InfraDebug::getInstance()->gravar(__METHOD__);
      InfraDebug::getInstance()->gravar('PERMISSOES:'.count($Permissoes));
      */

      $this->simularLogin();

      $objUsuarioRN = new UsuarioRN();
      $objUnidadeRN = new UnidadeRN();
      $objPermissaoRN = new PermissaoRN();

      $objInfraException = new InfraException();

      foreach($Permissoes as $Permissao) {

        $StaOperacao = $Permissao['StaOperacao'];
        $IdSistema = $Permissao['IdSistema'];
        $IdUsuario = $Permissao['IdUsuario'];
        $IdOrigemUsuario = $Permissao['IdOrigemUsuario'];
        $IdOrgaoUsuario = $Permissao['IdOrgaoUsuario'];
        $IdUnidade = $Permissao['IdUnidade'];
        $IdOrigemUnidade = $Permissao['IdOrigemUnidade'];
        $IdOrgaoUnidade = $Permissao['IdOrgaoUnidade'];
        $IdPerfil = $Permissao['IdPerfil'];
        $DataInicial = $Permissao['DataInicial'];
        $DataFinal = $Permissao['DataFinal'];
        $SinSubunidades = $Permissao['SinSubunidades'];

        /*
        InfraDebug::getInstance()->gravar(' ');
        InfraDebug::getInstance()->gravar('OPERACAO:'.$StaOperacao);
        InfraDebug::getInstance()->gravar('ID SISTEMA:'.$IdSistema);
        InfraDebug::getInstance()->gravar('ID USUARIO:'.$IdUsuario);
        InfraDebug::getInstance()->gravar('ID ORIGEM USUARIO:'.$IdOrigemUsuario);
        InfraDebug::getInstance()->gravar('ID ORGAO USUARIO:'.$IdOrgaoUsuario);
        InfraDebug::getInstance()->gravar('ID UNIDADE:'.$IdUnidade);
        InfraDebug::getInstance()->gravar('ID ORIGEM UNIDADE:'.$IdOrigemUnidade);
        InfraDebug::getInstance()->gravar('ID ORGAO UNIDADE:'.$IdOrgaoUnidade);
        InfraDebug::getInstance()->gravar('ID PERFIL:'.$IdPerfil);
        InfraDebug::getInstance()->gravar('DATA INICIAL:'.$DataInicial);
        InfraDebug::getInstance()->gravar('DATA FINAL:'.$DataFinal);
        InfraDebug::getInstance()->gravar('SIN SUBUNIDADES:'.$SinSubunidades);
        */

        if (InfraString::isBolVazia($IdOrgaoUsuario)) {
          throw new InfraException('Órgão do usuário não informado.');
        }

        if (InfraString::isBolVazia($IdOrgaoUnidade)) {
          throw new InfraException('Órgão da unidade não informado.');
        }

        if (InfraString::isBolVazia($IdUsuario) && InfraString::isBolVazia($IdOrigemUsuario)) {
          throw new InfraException('Nenhum identificador de usuário informado.');
        }

        if (InfraString::isBolVazia($IdUnidade) && InfraString::isBolVazia($IdOrigemUnidade)) {
          throw new InfraException('Nenhum identificador de unidade informado.');
        }

        try {

          $objUsuarioDTO = new UsuarioDTO();
          $objUsuarioDTO->retNumIdUsuario();
          $objUsuarioDTO->retStrSinAtivo();
          $objUsuarioDTO->setNumIdOrgao($IdOrgaoUsuario);

          if (!InfraString::isBolVazia($IdUsuario)){
            $objUsuarioDTO->setNumIdUsuario($IdUsuario);
          }

          if (!InfraString::isBolVazia($IdOrigemUsuario)){
            $objUsuarioDTO->setStrIdOrigem($IdOrigemUsuario);
          }

          $arrObjUsuarioDTO = $objUsuarioRN->listar($objUsuarioDTO);

          if (count($arrObjUsuarioDTO)==0) {
            throw new InfraException('Nenhum usuário encontrado [IdUsuario='.$IdUsuario.', IdOrigemUsuario=' . $IdOrigemUsuario . ', IdOrgaoUsuario=' . $IdOrgaoUsuario . '].');
          }

          $objUnidadeDTO = new UnidadeDTO();
          $objUnidadeDTO->retNumIdUnidade();
          $objUnidadeDTO->retStrSinAtivo();
          $objUnidadeDTO->setNumIdOrgao($IdOrgaoUnidade);

          if (!InfraString::isBolVazia($IdUnidade)){
            $objUnidadeDTO->setNumIdUnidade($IdUnidade);
          }

          if (!InfraString::isBolVazia($IdOrigemUnidade)){
            $objUnidadeDTO->setStrIdOrigem($IdOrigemUnidade);
          }

          $arrObjUnidadeDTO = $objUnidadeRN->listar($objUnidadeDTO);

          if (count($arrObjUnidadeDTO)==0) {
            throw new InfraException('Nenhuma unidade encontrada [IdUnidade='.$IdUnidade.', IdOrigemUnidade=' . $IdOrigemUnidade . ', IdOrgaoUnidade=' . $IdOrgaoUnidade . '].');
          }

          foreach($arrObjUsuarioDTO as $objUsuarioDTO) {

            foreach ($arrObjUnidadeDTO as $objUnidadeDTO) {

              $objPermissaoDTO = new PermissaoDTO();
              $objPermissaoDTO->setNumIdSistema($IdSistema);
              $objPermissaoDTO->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
              $objPermissaoDTO->setNumIdUnidade($objUnidadeDTO->getNumIdUnidade());
              $objPermissaoDTO->setNumIdPerfil($IdPerfil);
              $bolExiste = $objPermissaoRN->contar($objPermissaoDTO);

              if ($StaOperacao == 'A') {

                $objPermissaoDTO->setDtaDataInicio($DataInicial);
                $objPermissaoDTO->setDtaDataFim($DataFinal);
                $objPermissaoDTO->setStrSinSubunidades($SinSubunidades);
                $objPermissaoDTO->setNumIdTipoPermissao(PermissaoRN::$TIPO_NAO_DELEGAVEL);

                if (!$bolExiste) {
                  $objPermissaoRN->cadastrar($objPermissaoDTO);
                } else {
                  $objPermissaoRN->alterar($objPermissaoDTO);
                }

              } else if ($StaOperacao == 'E') {

                if ($bolExiste) {
                  $objPermissaoRN->excluir(array($objPermissaoDTO));
                }

              } else {
                throw new InfraException('Operação ' . $StaOperacao . ' inválida.');
              }
            }
          }

        }catch(Exception $e){

          $objInfraException->adicionarValidacao("\n * ".$e->__toString());

          if (!($e instanceof InfraException && $e->contemValidacoes())){
            try {
              LogSip::getInstance()->gravar(InfraException::inspecionar($e));
            }catch(Exception $e2){}
          }
        }
      }

      $objInfraException->lancarValidacoes();

      //LogSip::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());

      return true;

    }catch(Exception $e){
      $this->processarExcecao($e);
    }

    return false;
  }

  public function listarPermissao($IdSistema, $IdOrgaoUsuario, $IdUsuario, $IdOrigemUsuario , $IdOrgaoUnidade, $IdUnidade, $IdOrigemUnidade, $IdPerfil){
    try {

      $this->validarAcessoAutorizado(ConfiguracaoSip::getInstance()->getValor('HostWebService','Pesquisa'));

      /*
      InfraDebug::getInstance()->setBolLigado(true);
      InfraDebug::getInstance()->setBolDebugInfra(true);
      InfraDebug::getInstance()->limpar();

      InfraDebug::getInstance()->gravar(__METHOD__);
      InfraDebug::getInstance()->gravar('ID SISTEMA:'.$IdSistema);
      InfraDebug::getInstance()->gravar('ID ORGAO USUARIO:'.$IdOrgaoUsuario);
      InfraDebug::getInstance()->gravar('ID USUARIO:'.$IdUsuario);
      InfraDebug::getInstance()->gravar('ID ORIGEM USUARIO:'.$IdOrigemUsuario);
      InfraDebug::getInstance()->gravar('ID ORGAO UNIDADE:'.$IdOrgaoUnidade);
      InfraDebug::getInstance()->gravar('ID UNIDADE:'.$IdUnidade);
      InfraDebug::getInstance()->gravar('ID ORIGEM UNIDADE:'.$IdOrigemUnidade);
      InfraDebug::getInstance()->gravar('ID PERFIL:'.$IdPerfil);
      */

      $this->simularLogin();

      $objSistemaDTO = new SistemaDTO();
      $objSistemaDTO->setBolExclusaoLogica(false);
      $objSistemaDTO->retStrSigla();
      $objSistemaDTO->retNumIdHierarquia();
      $objSistemaDTO->retStrSinAtivo();
      $objSistemaDTO->setNumIdSistema($IdSistema);

      $objSistemaRN = new SistemaRN();
      $objSistemaDTO = $objSistemaRN->consultar($objSistemaDTO);

      if ($objSistemaDTO == null){
        throw new InfraException('Sistema ['.$IdSistema.'] não encontrado.');
      }

      if ($objSistemaDTO->getStrSinAtivo() == 'N'){
        throw new InfraException('Sistema '.$objSistemaDTO->getStrSigla().' desativado.');
      }

      $objPermissaoDTO = new PermissaoDTO();
      $objPermissaoDTO->retNumIdSistema();
      $objPermissaoDTO->retNumIdUsuario();
      $objPermissaoDTO->retStrIdOrigemUsuario();
      $objPermissaoDTO->retNumIdOrgaoUsuario();
      $objPermissaoDTO->retNumIdUnidade();
      $objPermissaoDTO->retStrIdOrigemUnidade();
      $objPermissaoDTO->retNumIdOrgaoUnidade();
      $objPermissaoDTO->retNumIdPerfil();
      $objPermissaoDTO->retDtaDataInicio();
      $objPermissaoDTO->retDtaDataFim();
      $objPermissaoDTO->retStrSinSubunidades();

      $objPermissaoDTO->setNumIdSistema($IdSistema);

      if (!InfraString::isBolVazia($IdUsuario)){
        $objPermissaoDTO->setNumIdUsuario($IdUsuario);
      }

      if (!InfraString::isBolVazia($IdOrigemUsuario)){
        $objPermissaoDTO->setStrIdOrigemUsuario($IdOrigemUsuario);
      }

      if (!InfraString::isBolVazia($IdOrgaoUsuario)){
        $objPermissaoDTO->setNumIdOrgaoUsuario($IdOrgaoUsuario);
      }

      if (!InfraString::isBolVazia($IdUnidade)){
        $objPermissaoDTO->setNumIdUnidade($IdUnidade);
      }

      if (!InfraString::isBolVazia($IdOrigemUnidade)){
        $objPermissaoDTO->setStrIdOrigemUnidade($IdOrigemUnidade);
      }

      if (!InfraString::isBolVazia($IdOrgaoUnidade)){
        $objPermissaoDTO->setNumIdOrgaoUnidade($IdOrgaoUnidade);
      }

      if (!InfraString::isBolVazia($IdPerfil)){
        $objPermissaoDTO->setNumIdPerfil($IdPerfil);
      }

      $objPermissaoDTO->setOrdNumIdOrgaoUsuario(InfraDTO::$TIPO_ORDENACAO_ASC);
      $objPermissaoDTO->setOrdNumIdUsuario(InfraDTO::$TIPO_ORDENACAO_ASC);
      $objPermissaoDTO->setOrdStrIdOrigemUsuario(InfraDTO::$TIPO_ORDENACAO_ASC);

      $objPermissaoRN = new PermissaoRN();
      $arrObjPermissaoDTO = $objPermissaoRN->listar($objPermissaoDTO);

      $ret = array();
      foreach($arrObjPermissaoDTO as $objPermissaoDTO){
        $ret[] = (object)array(
            'IdSistema' => $objPermissaoDTO->getNumIdSistema(),
            'IdOrgaoUsuario' => $objPermissaoDTO->getNumIdOrgaoUsuario(),
            'IdUsuario' => $objPermissaoDTO->getNumIdUsuario(),
            'IdOrigemUsuario' => $objPermissaoDTO->getStrIdOrigemUsuario(),
            'IdOrgaoUnidade' => $objPermissaoDTO->getNumIdOrgaoUnidade(),
            'IdUnidade' => $objPermissaoDTO->getNumIdUnidade(),
            'IdOrigemUnidade' => $objPermissaoDTO->getStrIdOrigemUnidade(),
            'IdPerfil' => $objPermissaoDTO->getNumIdPerfil(),
            'DataInicial' => $objPermissaoDTO->getDtaDataInicio(),
            'DataFinal' => $objPermissaoDTO->getDtaDataFim(),
            'SinSubunidades' => $objPermissaoDTO->getStrSinSubunidades());
      }

      //LogSip::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());

      return $ret;

    }catch(Exception $e){
      $this->processarExcecao($e);
    }

    return null;
  }

  public function carregarPerfis($IdSistema,$IdUsuario,$IdUnidade){
    try {

      $this->validarAcessoAutorizado(ConfiguracaoSip::getInstance()->getValor('HostWebService','Pesquisa'));

      /*
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->limpar();

      InfraDebug::getInstance()->gravar(__METHOD__);
      InfraDebug::getInstance()->gravar('ID SISTEMA:'.$IdSistema);
      InfraDebug::getInstance()->gravar('ID USUARIO:'.$IdUsuario);
      InfraDebug::getInstance()->gravar('ID UNIDADE:'.$IdUnidade);
      */

      $this->simularLogin();

      $objPermissaoDTO = new PermissaoDTO();
      $objPermissaoDTO->setNumIdSistema($IdSistema);
      $objPermissaoDTO->setNumIdUsuario($IdUsuario);
      $objPermissaoDTO->setNumIdUnidade($IdUnidade);

      $objPerfilRN = new PerfilRN();
      $ret = $objPerfilRN->carregarPerfis($objPermissaoDTO);


      //LogSip::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());

      return $ret;

    }catch(Exception $e){
      $this->processarExcecao($e);
    }
  }

  public function autenticar($IdOrgao,$IdContexto,$Sigla,$Senha){
    try {

      $this->validarAcessoAutorizado(ConfiguracaoSip::getInstance()->getValor('HostWebService', 'Autenticacao'));

      /*
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->limpar();

      InfraDebug::getInstance()->gravar('ÓRGÃO:'.$IdOrgao);
      InfraDebug::getInstance()->gravar('CONTEXTO:'.$IdContexto);
      InfraDebug::getInstance()->gravar('SIGLA:'.$Sigla);
      InfraDebug::getInstance()->gravar('SENHA:'.$Senha);
      */

      $Senha = base64_decode($Senha);
      for($i = 0; $i < strlen($Senha); $i++){
        $Senha[$i] = ~$Senha[$i];
      }


      $this->simularLogin();

      $objLoginRN = new LoginRN();

      $objLoginDTO = new LoginDTO();
      $objLoginDTO->setNumIdOrgaoUsuario($IdOrgao);
      $objLoginDTO->setNumIdContexto($IdContexto);
      $objLoginDTO->setStrSiglaUsuario($Sigla);
      $objLoginDTO->setStrSenhaUsuario($Senha);

      $objLoginRN->autenticar($objLoginDTO);

      //LogSip::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());

      return true;

    }catch(Exception $e){
      $this->processarExcecao($e,true);
    }
    return false;
  }

  public function pesquisarUsuario($TipoServidorAutenticacao, $IdOrgao, $Sigla){
    try {

      $this->validarAcessoAutorizado(ConfiguracaoSip::getInstance()->getValor('HostWebService','Pesquisa'));

      /*
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->limpar();

      InfraDebug::getInstance()->gravar(__METHOD__);
      InfraDebug::getInstance()->gravar('TIPO SERVIDOR AUTENTICACAO:'.$TipoServidorAutenticacao);
      InfraDebug::getInstance()->gravar('ÓRGÃO:'.$IdOrgao);
      InfraDebug::getInstance()->gravar('SIGLA:'.$Sigla);
      */

      $this->simularLogin();

      $objUsuarioDTO = new UsuarioDTO();
      $objUsuarioDTO->setNumIdOrgao($IdOrgao);
      $objUsuarioDTO->setStrSigla($Sigla);
      $objUsuarioDTO->setStrTipoServidorAutenticacao($TipoServidorAutenticacao);

      $objSipRN = new SipRN();
      $ret = $objSipRN->pesquisarUsuario($objUsuarioDTO);

      //LogSip::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());

      return $ret;

    }catch(Exception $e){
      $this->processarExcecao($e);
    }

    return false;
  }

  /**
   * Método de autenticação com retorno completo
   * @param $IdOrgao
   * @param $IdContexto
   * @param $Sigla
   * @param $Senha
   * @param $SiglaSistema
   * @param $SiglaOrgaoSistema
   * @return stdClass
   * @throws InfraException
   * @throws SoapFault
   */
  public function autenticarCompleto($IdOrgao,$IdContexto,$Sigla,$Senha, $SiglaSistema, $SiglaOrgaoSistema){
    try {
      $this->validarAcessoAutorizado(ConfiguracaoSip::getInstance()->getValor('HostWebService', 'Autenticacao'));
      $Senha = base64_decode($Senha);
      for($i = 0; $i < strlen($Senha); $i++){
        $Senha[$i] = ~$Senha[$i];
      }
      $this->simularLogin();
      $objLoginRN = new LoginRN();
      $objLoginDTO = new LoginDTO();
      $objLoginDTO->setNumIdOrgaoUsuario($IdOrgao);
      $objLoginDTO->setNumIdContexto($IdContexto);
      $objLoginDTO->setStrSiglaUsuario($Sigla);
      $objLoginDTO->setStrSenhaUsuario($Senha);
      $objLoginDTO->setStrSiglaOrgaoSistema($SiglaOrgaoSistema);
      $objLoginDTO->setStrSiglaSistema($SiglaSistema);
      $objLoginRN->autenticar($objLoginDTO);
      /**
       * Cadastrando Login igual o processo padrão de autenticação do SIP
       */
      $objLoginDTO = $objLoginRN->cadastrar($objLoginDTO);

      /**
       * Retornando mesmos parametros que o SIP passa para autenticar um usuário via POST.
       * Isto é interessante pois são alguns dos dados necessários para usar o metodo do SIP validarLogin,
       * e também é um ganho pois se outro sistema quiser abrir o SEI em uma página especifica com o usuário autenticado
       * basta passar estes parametros pela URL.
       */
      $objResult = new stdClass();
      $objResult->IdSistema = $objLoginDTO->getNumIdSistema();
      $objResult->IdContexto = $objLoginDTO->getNumIdContexto();
      $objResult->IdUsuario = $objLoginDTO->getNumIdUsuario();
      $objResult->IdLogin = $objLoginDTO->getStrIdLogin();
      $objResult->HashAgente = $objLoginDTO->getStrHashAgente();

      return $objResult;

    }catch(Exception $e){
      $this->processarExcecao($e,true);
    }
  }
}

$servidorSoap = new SoapServer("sip.wsdl",array('encoding'=>'ISO-8859-1'));
$servidorSoap->setClass("SipWS");

//Só processa se acessado via POST
if ($_SERVER['REQUEST_METHOD']=='POST') {
  $servidorSoap->handle();
} 	
