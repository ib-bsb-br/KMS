<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 27/11/2006 - criado por mga
*
*
*/

require_once dirname(__FILE__).'/../SEI.php';

class SEISipWS extends InfraWS {

  public function getObjInfraLog(){
    return LogSEI::getInstance();
  }

  public function replicarUsuario($Usuarios){
    try {

    	$this->validarAcessoAutorizado(ConfiguracaoSEI::getInstance()->getValor('HostWebService','Sip'));

      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->limpar();

      InfraDebug::getInstance()->gravar(__METHOD__);
      InfraDebug::getInstance()->gravar('USUARIOS:'.count($Usuarios));

      SessaoSEI::getInstance(false)->simularLogin(SessaoSEI::$USUARIO_SIP,SessaoSEI::$UNIDADE_TESTE);

      $objUsuarioRN = new UsuarioRN();
      $objInfraException = new InfraException();

      foreach($Usuarios as $Usuario) {

        $StaOperacao = $Usuario['StaOperacao'];
        $IdUsuario = $Usuario['IdUsuario'];
        $IdOrgao = $Usuario['IdOrgao'];
        $IdOrigem = $Usuario['IdOrigem'];
        $Sigla = $Usuario['Sigla'];
        $Nome = $Usuario['Nome'];
        $SinAtivo = $Usuario['SinAtivo'];

        /*
        InfraDebug::getInstance()->gravar(' ');
        InfraDebug::getInstance()->gravar('OPERACAO:'.$StaOperacao);
        InfraDebug::getInstance()->gravar('ID USUARIO:'.$IdUsuario);
        InfraDebug::getInstance()->gravar('ID ORGAO:'.$IdOrgao);
        InfraDebug::getInstance()->gravar('ID ORIGEM:'.$IdOrigem);
        InfraDebug::getInstance()->gravar('SIGLA:'.$Sigla);
        InfraDebug::getInstance()->gravar('NOME:'.$Nome);
        InfraDebug::getInstance()->gravar('SIN ATIVO:'.$SinAtivo);
        */


        try {

          $objUsuarioDTOBanco = new UsuarioDTO();
          $objUsuarioDTOBanco->setBolExclusaoLogica(false);
          $objUsuarioDTOBanco->retNumIdUsuario();
          $objUsuarioDTOBanco->retNumIdOrgao();
          $objUsuarioDTOBanco->retStrIdOrigem();
          $objUsuarioDTOBanco->retStrSigla();
          $objUsuarioDTOBanco->retStrNome();
          $objUsuarioDTOBanco->retStrStaTipo();
          $objUsuarioDTOBanco->retStrSenha();
          $objUsuarioDTOBanco->retStrSinAtivo();
          $objUsuarioDTOBanco->setNumIdUsuario($IdUsuario);
          $objUsuarioDTOBanco = $objUsuarioRN->consultarRN0489($objUsuarioDTOBanco);

          if ($objUsuarioDTOBanco!=null){
            if ($objUsuarioDTOBanco->getStrSinAtivo()=='S' && $SinAtivo=='N' && $StaOperacao!='D'){
              $objUsuarioRN->desativarRN0695(array($objUsuarioDTOBanco));
            }else if ($objUsuarioDTOBanco->getStrSinAtivo()=='N' && $SinAtivo=='S' && $StaOperacao!='R'){
              $objUsuarioRN->reativarRN0696(array($objUsuarioDTOBanco));
            }
          }

          if ($StaOperacao == 'C' || $StaOperacao == 'A') {
            if ($objUsuarioDTOBanco == null) {

              $objUsuarioDTO = new UsuarioDTO();
              $objUsuarioDTO->setNumIdUsuario($IdUsuario);
              $objUsuarioDTO->setNumIdOrgao($IdOrgao);
              $objUsuarioDTO->setStrIdOrigem($IdOrigem);
              $objUsuarioDTO->setStrSigla($Sigla);
              $objUsuarioDTO->setStrNome($Nome);
              $objUsuarioDTO->setStrStaTipo(UsuarioRN::$TU_SIP);
              $objUsuarioDTO->setStrSenha(null);
              $objUsuarioDTO->setStrSinAcessibilidade('N');
              $objUsuarioDTO->setStrSinAtivo($SinAtivo);
              $objUsuarioRN->cadastrarRN0487($objUsuarioDTO);
            } else {

              $objUsuarioDTO = new UsuarioDTO();
              $objUsuarioDTO->setNumIdUsuario($IdUsuario);
              $objUsuarioDTO->setNumIdOrgao($IdOrgao);
              $objUsuarioDTO->setStrIdOrigem($IdOrigem);
              $objUsuarioDTO->setStrSigla($Sigla);
              $objUsuarioDTO->setStrNome($Nome);
              $objUsuarioDTO->setStrStaTipo(UsuarioRN::$TU_SIP);
              $objUsuarioDTO->setStrSenha(null);
              $objUsuarioDTO->setStrSinAtivo($SinAtivo);

              if ($objUsuarioDTOBanco->__toString() != $objUsuarioDTO->__toString()) {
                $objUsuarioRN->alterarRN0488($objUsuarioDTO);
              }
            }

          } else if ($StaOperacao == 'E') {

            if ($objUsuarioDTOBanco != null) {

              try {
                $objUsuarioRN->excluirRN0491(array($objUsuarioDTOBanco));
              } catch (Exception $e) {
                //erro de integridade então desativa
                $objUsuarioRN->desativarRN0695(array($objUsuarioDTOBanco));
              }
            }

          } else if ($StaOperacao == 'D') {
            if ($objUsuarioDTOBanco != null) {
              $objUsuarioRN->desativarRN0695(array($objUsuarioDTOBanco));
            }
          } else if ($StaOperacao == 'R') {
            if ($objUsuarioDTOBanco != null) {
              $objUsuarioRN->reativarRN0696(array($objUsuarioDTOBanco));
            }
          } else {
            throw new InfraException('Operação ' . $StaOperacao . ' inválida.');
          }

        }catch(Exception $e){

          $objInfraException->adicionarValidacao("\n * ".$Sigla.' ('.$IdUsuario.'): '.$e->__toString()."\n");

          if (!($e instanceof InfraException && $e->contemValidacoes())){
            try {
              LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            }catch(Exception $e2){}
          }
        }
      }

      $objInfraException->lancarValidacoes();

      CacheSEI::getInstance()->setAtributoVersao('SEI_U');

      //LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());
      
     	return true;

    }catch(Exception $e){
      $this->processarExcecao($e);
    }
    
    return false;
  }
  
  public function replicarUnidade($Unidades){
    try {

      $this->validarAcessoAutorizado(ConfiguracaoSEI::getInstance()->getValor('HostWebService', 'Sip'));

      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->limpar();

      InfraDebug::getInstance()->gravar(__METHOD__);
      InfraDebug::getInstance()->gravar('UNIDADES:' . count($Unidades));

      SessaoSEI::getInstance(false)->simularLogin(SessaoSEI::$USUARIO_SIP, SessaoSEI::$UNIDADE_TESTE);

      $objUnidadeRN = new UnidadeRN();
      $objInfraException = new InfraException();


      foreach ($Unidades as $Unidade) {

        $StaOperacao = $Unidade['StaOperacao'];
        $IdUnidade = $Unidade['IdUnidade'];
        $IdOrigem = $Unidade['IdOrigem'];
        $IdOrgao = $Unidade['IdOrgao'];
        $Sigla = $Unidade['Sigla'];
        $Descricao = $Unidade['Descricao'];
        $SinAtivo = $Unidade['SinAtivo'];

        /*
        InfraDebug::getInstance()->gravar('OPERACAO:' . $StaOperacao);
        InfraDebug::getInstance()->gravar('ID Unidade:' . $IdUnidade);
        InfraDebug::getInstance()->gravar('ID Origem:' . $IdOrigem);
        InfraDebug::getInstance()->gravar('ID ORGAO:' . $IdOrgao);
        InfraDebug::getInstance()->gravar('SIGLA:' . $Sigla);
        InfraDebug::getInstance()->gravar('DESCRICAO:' . $Descricao);
        InfraDebug::getInstance()->gravar('SIN ATIVO:' . $SinAtivo);
        */

        try {

          $objUnidadeDTOBanco = new UnidadeDTO();
          $objUnidadeDTOBanco->setBolExclusaoLogica(false);
          $objUnidadeDTOBanco->retNumIdUnidade();
          $objUnidadeDTOBanco->retNumIdOrgao();
          $objUnidadeDTOBanco->retStrIdOrigem();
          $objUnidadeDTOBanco->retStrSigla();
          $objUnidadeDTOBanco->retStrDescricao();
          $objUnidadeDTOBanco->retStrSinAtivo();
          $objUnidadeDTOBanco->setNumIdUnidade($IdUnidade);

          $objUnidadeDTOBanco = $objUnidadeRN->consultarRN0125($objUnidadeDTOBanco);

          if ($objUnidadeDTOBanco!=null){
            if ($objUnidadeDTOBanco->getStrSinAtivo()=='S' && $SinAtivo=='N' && $StaOperacao!='D'){
              $objUnidadeRN->desativarRN0484(array($objUnidadeDTOBanco));
            }else if ($objUnidadeDTOBanco->getStrSinAtivo()=='N' && $SinAtivo=='S' && $StaOperacao!='R'){
              $objUnidadeRN->reativarRN0485(array($objUnidadeDTOBanco));
            }
          }

          if ($StaOperacao == 'C' || $StaOperacao == 'A') {

            if ($objUnidadeDTOBanco == null) {

              $objUnidadeDTO = new UnidadeDTO();
              $objUnidadeDTO->setNumIdUnidade($IdUnidade);
              $objUnidadeDTO->setStrIdOrigem($IdOrigem);
              $objUnidadeDTO->setNumIdOrgao($IdOrgao);
              $objUnidadeDTO->setStrSigla($Sigla);
              $objUnidadeDTO->setStrDescricao($Descricao);
              $objUnidadeDTO->setStrSinAtivo($SinAtivo);
              $objUnidadeRN->cadastrarRN0078($objUnidadeDTO);

            } else {

              $objUnidadeDTO = new UnidadeDTO();
              $objUnidadeDTO->setNumIdUnidade($IdUnidade);
              $objUnidadeDTO->setStrIdOrigem($IdOrigem);
              $objUnidadeDTO->setNumIdOrgao($IdOrgao);
              $objUnidadeDTO->setStrSigla($Sigla);
              $objUnidadeDTO->setStrDescricao($Descricao);
              $objUnidadeDTO->setStrSinAtivo($SinAtivo);

              if ($objUnidadeDTOBanco->__toString() != $objUnidadeDTO->__toString()) {
                $objUnidadeRN->alterarRN0132($objUnidadeDTO);
              }
            }

          } else if ($StaOperacao == 'E') {

            if ($objUnidadeDTOBanco != null) {
              try {
                $objUnidadeRN->excluirRN0126(array($objUnidadeDTOBanco));
              } catch (Exception $e) {
                //erro de integridade então desativa
                $objUnidadeRN->desativarRN0484(array($objUnidadeDTOBanco));
              }
            }

          } else if ($StaOperacao == 'D') {
            if ($objUnidadeDTOBanco != null) {
              $objUnidadeRN->desativarRN0484(array($objUnidadeDTOBanco));
            }
          } else if ($StaOperacao == 'R') {
            if ($objUnidadeDTOBanco != null) {
              $objUnidadeRN->reativarRN0485(array($objUnidadeDTOBanco));
            }

          } else {
            throw new InfraException('Operação ' . $StaOperacao . ' inválida.');
          }

        } catch (Exception $e) {

          $objInfraException->adicionarValidacao("\n * ".$Sigla.' ('.$IdUnidade.'): ' . $e->__toString()."\n");

          if (!($e instanceof InfraException && $e->contemValidacoes())) {
            try {
              LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            } catch (Exception $e2) {}
          }

        }
      }

      CacheSEI::getInstance()->setAtributoVersao('SEI_H');

      $objInfraException->lancarValidacoes();

      //LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());
      
     	return true;
      
      
    }catch(Exception $e){
      $this->processarExcecao($e);
    } 
    
    return false;
  }

  public function replicarOrgao($Orgaos){
    try {
      
      $this->validarAcessoAutorizado(ConfiguracaoSEI::getInstance()->getValor('HostWebService','Sip'));
      
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->limpar();

      InfraDebug::getInstance()->gravar(__METHOD__);
      InfraDebug::getInstance()->gravar('ORGAOS:'.count($Orgaos));

      SessaoSEI::getInstance(false)->simularLogin(SessaoSEI::$USUARIO_SIP,SessaoSEI::$UNIDADE_TESTE);

      $objOrgaoRN = new OrgaoRN();
      $objInfraException = new InfraException();

      foreach($Orgaos as $Orgao) {

        $StaOperacao = $Orgao['StaOperacao'];
        $IdOrgao = $Orgao['IdOrgao'];
        $Sigla = $Orgao['Sigla'];
        $Descricao = $Orgao['Descricao'];
        $SinAtivo = $Orgao['SinAtivo'];

        InfraDebug::getInstance()->gravar('OPERACAO:' . $StaOperacao);
        InfraDebug::getInstance()->gravar('ID ORGAO:' . $IdOrgao);
        InfraDebug::getInstance()->gravar('SIGLA:' . $Sigla);
        InfraDebug::getInstance()->gravar('DESCRICAO:' . $Descricao);
        InfraDebug::getInstance()->gravar('SIN ATIVO:' . $SinAtivo);

        try {

          $objOrgaoDTOBanco = new OrgaoDTO();
          $objOrgaoDTOBanco->setBolExclusaoLogica(false);
          $objOrgaoDTOBanco->retNumIdOrgao();
          $objOrgaoDTOBanco->retStrSigla();
          $objOrgaoDTOBanco->retStrDescricao();
          $objOrgaoDTOBanco->retStrSinAtivo();
          $objOrgaoDTOBanco->setNumIdOrgao($IdOrgao);
          $objOrgaoDTOBanco = $objOrgaoRN->consultarRN1352($objOrgaoDTOBanco);

          if ($objOrgaoDTOBanco!=null){
            if ($objOrgaoDTOBanco->getStrSinAtivo()=='S' && $SinAtivo=='N' && $StaOperacao!='D'){
              $objOrgaoRN->desativarRN1355(array($objOrgaoDTOBanco));
            }else if ($objOrgaoDTOBanco->getStrSinAtivo()=='N' && $SinAtivo=='S' && $StaOperacao!='R'){
              $objOrgaoRN->reativarRN1356(array($objOrgaoDTOBanco));
            }
          }

          if ($StaOperacao == 'C' || $StaOperacao == 'A') {

            if ($objOrgaoDTOBanco == null) {

              $objOrgaoDTO = new OrgaoDTO();
              $objOrgaoDTO->setNumIdOrgao($IdOrgao);
              $objOrgaoDTO->setStrSigla($Sigla);
              $objOrgaoDTO->setStrDescricao($Descricao);
              $objOrgaoDTO->setStrSinAtivo($SinAtivo);
              $objOrgaoDTO->setStrSinEnvioProcesso('S');
              $objOrgaoDTO->setStrSinPublicacao('N');
              $objOrgaoDTO->setStrNumeracao(null);
              $objOrgaoDTO->setStrServidorCorretorOrtografico(null);
              $objOrgaoDTO->setStrCodigoSei(null);
              $objOrgaoRN->cadastrarRN1349($objOrgaoDTO);

            } else {

              $objOrgaoDTO = new OrgaoDTO();
              $objOrgaoDTO->setNumIdOrgao($IdOrgao);
              $objOrgaoDTO->setStrSigla($Sigla);
              $objOrgaoDTO->setStrDescricao($Descricao);
              $objOrgaoDTO->setStrSinAtivo($SinAtivo);

              if ($objOrgaoDTOBanco->__toString() != $objOrgaoDTO->__toString()) {
                $objOrgaoRN->alterarRN1350($objOrgaoDTO);
              }
            }

          } else if ($StaOperacao == 'E') {

            if ($objOrgaoDTOBanco != null) {
              try {
                $objOrgaoRN->excluirRN1351(array($objOrgaoDTOBanco));
              } catch (Exception $e) {
                $objOrgaoRN->desativarRN1355(array($objOrgaoDTOBanco));
              }
            }

          } else if ($StaOperacao == 'D') {
            if ($objOrgaoDTOBanco != null) {
              $objOrgaoRN->desativarRN1355(array($objOrgaoDTOBanco));
            }
          } else if ($StaOperacao == 'R') {
            if ($objOrgaoDTOBanco != null) {
              $objOrgaoRN->reativarRN1356(array($objOrgaoDTOBanco));
            }
          } else {
            throw new InfraException('Operação ' . $StaOperacao . ' inválida.');
          }

        } catch (Exception $e) {

          $objInfraException->adicionarValidacao("\n * " . $Sigla . ' (' . $IdOrgao . '): ' . $e->__toString() . "\n");

          if (!($e instanceof InfraException && $e->contemValidacoes())) {
            try {
              LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            } catch (Exception $e2) {
            }
          }
        }
      }

      $objInfraException->lancarValidacoes();

     	//LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());
      
     	return true;

    }catch(Exception $e){
      $this->processarExcecao($e);
    } 
    
    return false;
  }

  public function replicarContexto($StaOperacao,$IdContexto, $IdOrgao, $Nome, $Descricao, $BaseDnLdap, $SinAtivo){
    try {
      
      $this->validarAcessoAutorizado(ConfiguracaoSEI::getInstance()->getValor('HostWebService','Sip'));
      
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->limpar();

      InfraDebug::getInstance()->gravar(__METHOD__);
      InfraDebug::getInstance()->gravar('OPERACAO:'.$StaOperacao);
      InfraDebug::getInstance()->gravar('ID Contexto:'.$IdContexto);
      InfraDebug::getInstance()->gravar('ID Orgao:'.$IdOrgao);
      InfraDebug::getInstance()->gravar('NOME:'.$Nome);
      InfraDebug::getInstance()->gravar('DESCRICAO:'.$Descricao);
      InfraDebug::getInstance()->gravar('BASE DN LDAP:'.$BaseDnLdap);
      InfraDebug::getInstance()->gravar('SIN ATIVO:'.$SinAtivo);
      
      SessaoSEI::getInstance(false)->simularLogin(SessaoSEI::$USUARIO_SIP,SessaoSEI::$UNIDADE_TESTE);
      
      $objContextoDTO = new ContextoDTO();
      $objContextoDTO->setNumIdContexto($IdContexto);
      $objContextoDTO->setNumIdOrgao($IdOrgao);
      $objContextoDTO->setStrNome($Nome);
      $objContextoDTO->setStrDescricao($Descricao);
      $objContextoDTO->setStrBaseDnLdap($BaseDnLdap);
      $objContextoDTO->setStrSinAtivo($SinAtivo);
      
      $objContextoRN = new ContextoRN();

      $dto = new ContextoDTO();
      $dto->setBolExclusaoLogica(false);
      $dto->setNumIdContexto($IdContexto);
      $bolExiste = ($objContextoRN->contar($dto)>0);

      if ($StaOperacao=='C' || $StaOperacao == 'A'){
        
        if (!$bolExiste){
          $objContextoRN->cadastrar($objContextoDTO);
        }else{
          $objContextoRN->alterar($objContextoDTO);
        }
        
      }else if ($StaOperacao=='E'){

        if ($bolExiste){
          //se der erro de integridade desativa
          try{
            $objContextoRN->excluir(array($dto));
          }catch(Exception $e){
          	
          	//LogSEI::getInstance()->gravar($e->__toString());
          	
            //desativa Contexto no SEI
            $objContextoRN->desativar(array($dto));
          }
        }
        
      }else if ($StaOperacao=='D'){
        if ($bolExiste){
          $objContextoRN->desativar(array($dto));
        }
      }else if ($StaOperacao=='R'){
        if ($bolExiste){
          $objContextoRN->reativar(array($dto));
        }
     
      }else{
        throw new InfraException('Operação '.$StaOperacao.' inválida.');
      }
      
     	//LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());
      
     	return true;
      
      
    }catch(Exception $e){
      $this->processarExcecao($e);
    } 
    
    return false;
  }

  public function replicarAssociacaoUsuarioUnidade($StaOperacao, $IdUsuario, $IdUnidade){
    try {
      
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->limpar();

      SessaoSEI::getInstance(false)->simularLogin(SessaoSEI::$USUARIO_SIP,SessaoSEI::$UNIDADE_TESTE);           
      
      InfraDebug::getInstance()->gravar(__METHOD__);
      InfraDebug::getInstance()->gravar('OPERACAO:'.$StaOperacao);      
      InfraDebug::getInstance()->gravar('ID USUARIO:'.$IdUsuario);
      InfraDebug::getInstance()->gravar('ID UNIDADE:'.$IdUnidade);

      CacheSEI::getInstance()->removerAtributo('SEI_U_'.CacheSEI::getInstance()->getAtributoVersao('SEI_U').'_'.$IdUnidade);

      //LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());
           	
     	return true;
      
    }catch(Exception $e){
      $this->processarExcecao($e);
    } 
    return false;
  }
  
  /*
  public function replicarPermissao($StaOperacao, $IdSistema, $IdUsuario, $IdUnidade, $IdPerfil, $DtaInicio, $DtaFim){
    try {
  
      $this->validarAcessoAutorizado(ConfiguracaoSEI::getInstance()->getValor('HostWebService','Sip'));
  
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->limpar();
  
      InfraDebug::getInstance()->gravar(__METHOD__);
      InfraDebug::getInstance()->gravar('OPERACAO:'.$StaOperacao);
      InfraDebug::getInstance()->gravar('ID Sistema:'.$IdSistema);
      InfraDebug::getInstance()->gravar('ID Usuario:'.$IdUsuario);
      InfraDebug::getInstance()->gravar('ID Unidade:'.$IdUnidade);
      InfraDebug::getInstance()->gravar('ID Perfil:'.$IdPerfil);
      InfraDebug::getInstance()->gravar('DTA INICIO:'.$DtaInicio);
      InfraDebug::getInstance()->gravar('DTA FIM:'.$DtaFim);
  
  
      //LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());
  
      return true;
  
    }catch(Exception $e){
      $this->processarExcecao($e);
    }
  
    return false;
  }
  */
  
  public function replicarRegraAuditoria($StaOperacao,$IdRegraAuditoria,$Descricao,$SinAtivo,$Recursos){
    try {
      
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->limpar();

      SessaoSEI::getInstance(false)->simularLogin(SessaoSEI::$USUARIO_SIP,SessaoSEI::$UNIDADE_TESTE);           
      
      InfraDebug::getInstance()->gravar(__METHOD__);
      InfraDebug::getInstance()->gravar('OPERACAO:'.$StaOperacao);
      InfraDebug::getInstance()->gravar('ID REGRA AUDITORIA:'.$IdRegraAuditoria);
      InfraDebug::getInstance()->gravar('DESCRICAO:'.$Descricao);
      InfraDebug::getInstance()->gravar('SIN ATIVO:'.$SinAtivo);
      InfraDebug::getInstance()->gravar('RECURSOS:'.implode(',',$Recursos));
      
      AuditoriaSEI::getInstance()->replicarRegra($StaOperacao,$IdRegraAuditoria,$Descricao,$SinAtivo,$Recursos);
      
      //LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());
           	
     	return true;
      
    }catch(Exception $e){
      $this->processarExcecao($e);
    } 
    return false;
  }
  
}

$servidorSoap = new SoapServer("sei_sip.wsdl",array('encoding'=>'ISO-8859-1'));

$servidorSoap->setClass("SEISipWS");

//Só processa se acessado via POST
if ($_SERVER['REQUEST_METHOD']=='POST') {
  $servidorSoap->handle();
} 	
