<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 30/05/2014 - criado por mga
*
* Versão do Gerador de Código: 1.12.0
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class SeiINT extends InfraINT {

  private static $MSG_ERRO_XSS = 'Documento possui conteúdo não permitido';
  private static $NIVEL_VERIFICACAO_ROTINA = null;

  public static function validarHttps(){
    
    $bolHttps = ConfiguracaoSEI::getInstance()->getValor('SessaoSEI','https');
    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on');
    
    if (($bolHttps && !$isHttps) || (!$bolHttps && $isHttps)){
      
      $strServer = ConfiguracaoSEI::getInstance()->getValor('SEI','URL');
    
      $posIni = strpos($strServer, '//');
      if ($posIni!==false){
        $strServer = substr($strServer, $posIni+2);
      }
    
      $posFim = strpos($strServer, '/');
      if ($posFim!==false){
        $strServer = substr($strServer, 0, $posFim);
      }
      
      header('Location: '.($bolHttps?'https':'http').'://'.$strServer.$_SERVER['REQUEST_URI']);
      die;
    }
  }
  
  public static function obterURL(){
    
    $strURL = ConfiguracaoSEI::getInstance()->getValor('SEI','URL');
    
    if (ConfiguracaoSEI::getInstance()->getValor('SessaoSEI','https')){
      $strURL = str_replace('http://','https://',$strURL);
    }else{
      $strURL = str_replace('https://','http://',$strURL);
    }
    return $strURL.'/';
  }

  public static function download($objAnexoDTO = null, $strCaminhoNomeArquivo = null, $strNomeArquivo = null, $strContentDisposition = 'inline', $bolExcluirAutomaticamente = false, $strIdentificacao = '', $dbIdDocumento = null, $bolValidacao = false){

    try {

      ini_set('memory_limit', '1024M');

      if ($objAnexoDTO!=null){

        $objAnexoRN = new AnexoRN();
        $strCaminhoNomeArquivo = $objAnexoRN->obterLocalizacao($objAnexoDTO);

        if ($strNomeArquivo==null) {
          $strNomeArquivo = $objAnexoDTO->getStrNome();
        }
      }

      $numTamanho = filesize($strCaminhoNomeArquivo);

      $binConteudo = null;

      if ($objAnexoDTO!=null){

        $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
        $strVerificacaoHash = $objInfraParametro->getValor('SEI_HABILITAR_VERIFICACAO_REPOSITORIO', false);

        if ($strVerificacaoHash == '1') {
          if ($numTamanho > TAM_BLOCO_LEITURA_ARQUIVO) {

            if (md5_file($strCaminhoNomeArquivo) != $objAnexoDTO->getStrHash()) {
              throw new InfraException('Conteúdo do arquivo corrompido.', null, $strCaminhoNomeArquivo);
            }

          } else {

            $fp = fopen($strCaminhoNomeArquivo, "rb");
            $binConteudo = fread($fp, TAM_BLOCO_LEITURA_ARQUIVO);
            fclose($fp);

            if (md5($binConteudo) != $objAnexoDTO->getStrHash()) {
              throw new InfraException('Conteúdo do arquivo corrompido.', null, $strCaminhoNomeArquivo);
            }
          }
        }
      }

      $strMimeType = InfraUtil::getStrMimeType($strNomeArquivo);

      $strContentType = 'Content-Type: ' . $strMimeType . ';';

      if ($strMimeType == 'text/html' || $strMimeType == 'text/plain'){

        $strCharset = strtolower(InfraUtil::obterCharsetArquivo($strCaminhoNomeArquivo));

        if ($strCharset=='utf-8' || $strCharset=='iso-8859-1') {
          $strContentType .= ' charset='.$strCharset;
        }
      }

      $bolCabecalhoEvitarXSS = false;

      if ($strMimeType == 'text/html'){

        $bolCabecalhoEvitarXSS = true;

        if (!$bolValidacao) {

          if ($binConteudo == null) {
            $binConteudo = file_get_contents($strCaminhoNomeArquivo);
          }

          self::validarXss($binConteudo, $strIdentificacao, false, $strCaminhoNomeArquivo, $dbIdDocumento, $strCharset);
        }
      }

      InfraPagina::montarHeaderDownload($strNomeArquivo, $strContentDisposition, $strContentType, $bolCabecalhoEvitarXSS);

      ob_start();

      if ($binConteudo != null){

        echo $binConteudo;

      }else {

        $fp = fopen($strCaminhoNomeArquivo, "rb");

        while (!feof($fp)) {

          echo fread($fp, TAM_BLOCO_LEITURA_ARQUIVO);

          if (ob_get_length()) {
            ob_flush();
            flush();
            ob_end_flush();
          }
        }

        fclose($fp);
      }

      if (ob_get_length()) {
        @ob_flush();
        @flush();
        @ob_end_flush();
      }

      //@ob_end_clean();

      if ($bolExcluirAutomaticamente && substr(trim($strCaminhoNomeArquivo), 0, strlen(DIR_SEI_TEMP)) == DIR_SEI_TEMP){
        unlink($strCaminhoNomeArquivo);
      }

    }catch(Exception $e){

      if (strpos(strtoupper($e->__toString()),'NO SUCH FILE OR DIRECTORY')!==false){
        throw new InfraException('Erro acessando o repositório de arquivos.', $e);
      }

      throw $e;
    }
  }

  public static function getContentDisposition($strNomeArquivo){

    $ret = 'inline';

    $strMimeType = InfraUtil::getStrMimeType($strNomeArquivo);

    $strTipo = substr($strMimeType, 0, 6);

    if ($strTipo == 'video/' || $strTipo == 'audio/' || $strMimeType == 'application/zip' || $strMimeType == 'application/rar') {
      $ret = 'attachment';
    }

    return $ret;
  }

  public static function validarXss(&$strConteudo, $strIdentificacao='', $bolGravacao = false, $strNomeArquivo = '', $dbIdDocumento = '', $strCharset = ''){
    try {

      $arrXssExcecoes = ConfiguracaoSEI::getInstance()->getValor('XSS', 'ProtocolosExcecoes', false, array());

      if (in_array($strIdentificacao, $arrXssExcecoes)){
        return;
      }

      if ($strIdentificacao!=''){
        $strIdentificacao = ' ('.$strIdentificacao.')';
      }

      if ($strNomeArquivo!=''){
        $strNomeArquivo = ', arquivo '.$strNomeArquivo;
      }

      if (self::$NIVEL_VERIFICACAO_ROTINA == null){
        $strXssNivelValidacao = ConfiguracaoSEI::getInstance()->getValor('XSS', 'NivelVerificacao', false, 'A');
      }else{
        $strXssNivelValidacao = self::$NIVEL_VERIFICACAO_ROTINA;
      }

      if (!in_array($strXssNivelValidacao,array('N','B','A'))){
        throw new InfraException('Nível de verificação de XSS inválido ['.$strXssNivelValidacao.'].');
      }

      if ($strXssNivelValidacao == 'B') {

        $arrXssBasico = ConfiguracaoSEI::getInstance()->getValor('XSS', 'NivelBasico', false, null);

        $arrXssNaoPermitidosBasico = null;
        if ($arrXssBasico !== null){
          if (isset($arrXssBasico['ValoresNaoPermitidos']) && $arrXssBasico['ValoresNaoPermitidos']!==null){
            $arrXssNaoPermitidosBasico = $arrXssBasico['ValoresNaoPermitidos'];
          }
        }

        $objInfraXSS = new InfraXSS();
        $arrRetBasico = $objInfraXSS->verificacaoBasica($strConteudo, $arrXssNaoPermitidosBasico) ;

        if ($arrRetBasico != null){

          if (count($arrRetBasico) == 1) {
            $strEncontrados = ', encontrado '.$arrRetBasico[0];
          }else{
            $strEncontrados = ', encontrados '.implode(' | ',$arrRetBasico);
          }

          throw new InfraException(self::$MSG_ERRO_XSS.$strIdentificacao.'.', null, 'Nível '.$strXssNivelValidacao.$strNomeArquivo.$strEncontrados.'.');
        }

      }else if ($strXssNivelValidacao == 'A') {

        $arrXssAvancadoTagsPermitidas = null;
        $arrXssAvancadoTagsAtributosPermitidos = null;
        $bolXssAvancadoFiltrarConteudoConsulta = false;

        $arrXssAvancado = ConfiguracaoSEI::getInstance()->getValor('XSS', 'NivelAvancado', false, null);

        if ($arrXssAvancado !== null){

          if (isset($arrXssAvancado['TagsPermitidas']) && $arrXssAvancado['TagsPermitidas']!==null){
            $arrXssAvancadoTagsPermitidas = $arrXssAvancado['TagsPermitidas'];
          }

          if (isset($arrXssAvancado['TagsAtributosPermitidos']) && $arrXssAvancado['TagsAtributosPermitidos']!==null){
            $arrXssAvancadoTagsAtributosPermitidos = $arrXssAvancado['TagsAtributosPermitidos'];
          }


          if (self::$NIVEL_VERIFICACAO_ROTINA == null) {
            if (isset($arrXssAvancado['FiltrarConteudoConsulta']) && $arrXssAvancado['FiltrarConteudoConsulta'] !== null) {
              $bolXssAvancadoFiltrarConteudoConsulta = $arrXssAvancado['FiltrarConteudoConsulta'];
            }
          }
        }

        if ($bolGravacao){
          $bolXssAvancadoFiltrarConteudoConsulta = false;
        }

        $bolUtf8 = ($strNomeArquivo != '' && $strCharset == 'utf-8');

        $strConteudoXss = $strConteudo;

        $strConteudoXss = preg_replace('/(<hr style="border:1px solid #c0c0c0;" \/>Criado por\s*<a )onclick="alert\(\'(.*)\'\)" alt/i','$1alt',$strConteudoXss);
        $strConteudoXss = preg_replace('/(<\/a>\s, versão \d* por\s+<a )onclick="alert\(\'(.*)\'\)" alt/i','$1alt',$strConteudoXss);

        if (!$bolUtf8){
          $strConteudoXss = utf8_encode($strConteudoXss);
        }

        $objInfraXSS = new InfraXSS();
        $bolXss = $objInfraXSS->verificacaoAvancada($strConteudoXss, $arrXssAvancadoTagsPermitidas, $arrXssAvancadoTagsAtributosPermitidos);

        if ($bolXss) {

          if ($strConteudoXss!='') {

            $strDiferencas = $objInfraXSS->getStrDiferenca();

            if (!$bolUtf8) {
              $strConteudoXss = utf8_decode($strConteudoXss);
              $strDiferencas = utf8_decode($strDiferencas);
            }

          }else{
            $strDiferencas = "Não foi possível processar o conteúdo.";
          }

          $strDiferencas = "\n\nDiferenças:\n".$strDiferencas;

          $strUsuario = '';
          if (SessaoSEI::getInstance()->getStrSiglaUsuario()!==null){
            $strUsuario .= ", usuário ".SessaoSEI::getInstance()->getStrSiglaUsuario();

            if (SessaoSEI::getInstance()->getStrSiglaOrgaoUsuario()!==null){
              $strUsuario .= '/'.SessaoSEI::getInstance()->getStrSiglaOrgaoUsuario();
            }
          }

          if ($dbIdDocumento!=null){
            $strIdConteudo = ', id_documento '.$dbIdDocumento;

            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->retStrStaNivelAcessoGlobal();
            $objProtocoloDTO->setDblIdProtocolo($dbIdDocumento);

            $objProtocoloRN = new ProtocoloRN();
            $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

            if ($objProtocoloDTO!=null && $objProtocoloDTO->getStrStaNivelAcessoGlobal()!=ProtocoloRN::$NA_PUBLICO){
              $strDiferencas = '';
            }
          }

          $objInfraExceptionXss  = new InfraException(self::$MSG_ERRO_XSS.$strIdentificacao.'.', null, 'Nível '.$strXssNivelValidacao.$strUsuario.$strIdConteudo.$strNomeArquivo.'.'.$strDiferencas);

          if ($bolXssAvancadoFiltrarConteudoConsulta){
            LogSEI::getInstance()->gravar('Descrição:'."\n".$objInfraExceptionXss->getStrDescricao()."\n\nDetalhes:\n".$objInfraExceptionXss->getStrDetalhes());
            $strConteudo = $strConteudoXss;
          }else{
            throw $objInfraExceptionXss;
          }
        }
      }

    }catch(Exception $e){
      throw new InfraException('Erro validando XSS.', $e);
    }

  }
  
  public static function rotinaVerificaoXss($strNivelVerificacao, $dtaInicio, $dtaFim){
    try{

      BancoSEI::getInstance()->abrirConexao();

      $objInfraException = new InfraException();

      ini_set('max_execution_time','0');
      ini_set('memory_limit','2048M');

      $numSeg = InfraUtil::verificarTempoProcessamento();

      self::logar('Verificação XSS - Iniciando análise de documentos...');

      if (InfraString::isBolVazia($strNivelVerificacao)){
        $objInfraException->lancarValidacao('Nível de verificação não informado.');
      }

      if (!in_array($strNivelVerificacao,array('B','A'))){
        throw new InfraException('Nível de verificação de XSS "'.$strNivelVerificacao.'" inválido valores possíveis "A" (Avançado) e "B" (Básico).');
      }

      self::$NIVEL_VERIFICACAO_ROTINA = $strNivelVerificacao;

      $dtaInicio = trim($dtaInicio);
      $dtaFim = trim($dtaFim);

      if ($dtaInicio!='' || $dtaFim!='') {

        if (InfraString::isBolVazia($dtaInicio)){
          $objInfraException->lancarValidacao('Data inicial não informada.');
        }

        if (InfraString::isBolVazia($dtaFim)){
          $objInfraException->lancarValidacao('Data final não informada.');
        }

        if (!InfraData::validarData($dtaInicio)) {
          $objInfraException->lancarValidacao("Data inicial [" . $dtaInicio . "] inválida.\n");
        }

        if (!InfraData::validarData($dtaFim)) {
          $objInfraException->lancarValidacao("Data final [" . $dtaFim . "] inválida.\n");
        }

        if (InfraData::compararDatas($dtaInicio, $dtaFim)<0){
          $objInfraException->lancarValidacao("Período inválido.");
        }
      }

      if ($dtaInicio!=null && $dtaFim!=null) {
        self::logar('Verificação XSS - '.$dtaInicio.' ate '.$dtaFim.'...');
      }

      $arrXssExcecoes = ConfiguracaoSEI::getInstance()->getValor('XSS', 'ProtocolosExcecoes', false, array());

      $numIgnorar = count($arrXssExcecoes);
      if ($numIgnorar==0){
        self::logar('Verificação XSS - Nenhuma exceção configurada...');
      }else if ($numIgnorar==1){
        self::logar('Verificação XSS - 1 exceção configurada...');
      }else{
        self::logar('Verificação XSS - '.$numIgnorar.' exceções configuradas...');
      }

      $strMsgErroXss = InfraString::transformarCaixaBaixa(self::$MSG_ERRO_XSS);


      $objProtocoloRN 	= new ProtocoloRN();

      $objProtocoloDTO 	= new ProtocoloDTO();
      $objProtocoloDTO->setDistinct(true);
      $objProtocoloDTO->retDtaGeracao();
      $objProtocoloDTO->setStrStaProtocolo(ProtocoloRN::$TP_PROCEDIMENTO, InfraDTO::$OPER_DIFERENTE);

      if ($dtaInicio!=null && $dtaFim!=null) {
        $objProtocoloDTO->adicionarCriterio(array('Geracao', 'Geracao'),
            array(InfraDTO::$OPER_MAIOR_IGUAL, InfraDTO::$OPER_MENOR_IGUAL),
            array($dtaInicio, $dtaFim),
            InfraDTO::$OPER_LOGICO_AND);
      }

      $objProtocoloDTO->setOrdDtaGeracao(InfraDTO::$TIPO_ORDENACAO_DESC);

      $arrObjProtocoloDTOData = $objProtocoloRN->listarRN0668($objProtocoloDTO);

      $objEditorRN = new EditorRN();
      $objAnexoRN = new AnexoRN();
      $objDocumentoRN = new DocumentoRN();

      $numRegistrosProcessados = 0;
      $numErros = 0;

      foreach($arrObjProtocoloDTOData as $objProtocoloDTOData){

        $dtaGeracao = $objProtocoloDTOData->getDtaGeracao();

        self::logar('Verificação XSS - Data '.$dtaGeracao.'...');

        $objProtocoloDTO = new ProtocoloDTO();
        $objProtocoloDTO->retDblIdProtocolo();
        $objProtocoloDTO->retStrProtocoloFormatado();
        $objProtocoloDTO->retStrStaProtocolo();
        $objProtocoloDTO->retStrStaDocumentoDocumento();
        $objProtocoloDTO->setDtaGeracao($dtaGeracao);
        $objProtocoloDTO->retStrSiglaUnidadeGeradora();
        $objProtocoloDTO->retStrNomeSerieDocumento();
        $objProtocoloDTO->retStrStaNivelAcessoGlobal();
        $objProtocoloDTO->setOrdDblIdProtocolo(InfraDTO::$TIPO_ORDENACAO_DESC);
        $arrObjProtocoloDTO = $objProtocoloRN->listarRN0668($objProtocoloDTO);

        $numRegistros 			=	count($arrObjProtocoloDTO);
        $numRegistrosPagina = 50;
        $numPaginas 				= ceil($numRegistros/$numRegistrosPagina);

        $arrObjNivelAcessoDTO = InfraArray::indexarArrInfraDTO($objProtocoloRN->listarNiveisAcessoRN0878(),'StaNivel');

        for ($numPaginaAtual = 0; $numPaginaAtual < $numPaginas; $numPaginaAtual++) {

          $arrObjProtocoloDTOPagina = array_slice($arrObjProtocoloDTO, ($numPaginaAtual * $numRegistrosPagina), $numRegistrosPagina);

          foreach($arrObjProtocoloDTOPagina as $objProtocoloDTOPagina){

            if (in_array($objProtocoloDTOPagina->getStrProtocoloFormatado(),$arrXssExcecoes)) {
              self::logar('Verificação XSS - Documento '.$objProtocoloDTOPagina->getStrProtocoloFormatado().' ignorado');
            }else{

              $strComplemento = '[ID='.$objProtocoloDTOPagina->getDblIdProtocolo().', Protocolo='.$objProtocoloDTOPagina->getStrProtocoloFormatado().', Tipo='.$objProtocoloDTOPagina->getStrNomeSerieDocumento().', Unidade='.$objProtocoloDTOPagina->getStrSiglaUnidadeGeradora().', Acesso='.$arrObjNivelAcessoDTO[$objProtocoloDTOPagina->getStrStaNivelAcessoGlobal()]->getStrDescricao().']';

              if ($objProtocoloDTOPagina->getStrStaDocumentoDocumento() == DocumentoRN::$TD_EDITOR_INTERNO) {

                $numRegistrosProcessados++;

                try {

                  $objEditorDTO = new EditorDTO();
                  $objEditorDTO->setDblIdDocumento($objProtocoloDTOPagina->getDblIdProtocolo());
                  $objEditorDTO->setNumIdBaseConhecimento(null);
                  $objEditorDTO->setStrSinCabecalho('S');
                  $objEditorDTO->setStrSinRodape('S');
                  $objEditorDTO->setStrSinCarimboPublicacao('N');
                  $objEditorDTO->setStrSinIdentificacaoVersao('N');
                  $objEditorDTO->setStrSinProcessarLinks('N');

                  $objEditorRN->consultarHtmlVersao($objEditorDTO);

                } catch (Exception $excXss) {
                  $numErros++;

                  if (strpos(InfraString::transformarCaixaBaixa($excXss->__toString()), $strMsgErroXss) !== false) {
                    self::logar('Verificação XSS - '.$excXss->getStrDescricao().' '.$excXss->getStrDetalhes()."\n\n".$strComplemento);
                  }else{
                    self::logar(InfraException::inspecionar($excXss));
                  }
                }


              } else if ($objProtocoloDTOPagina->getStrStaDocumentoDocumento() == DocumentoRN::$TD_EXTERNO ||
                         $objProtocoloDTOPagina->getStrStaDocumentoDocumento() == DocumentoRN::$TD_FORMULARIO_AUTOMATICO ||
                         $objProtocoloDTOPagina->getStrStaDocumentoDocumento() == DocumentoRN::$TD_FORMULARIO_GERADO) {


                if ($objProtocoloDTOPagina->getStrStaDocumentoDocumento() == DocumentoRN::$TD_FORMULARIO_AUTOMATICO || $objProtocoloDTOPagina->getStrStaDocumentoDocumento() == DocumentoRN::$TD_FORMULARIO_GERADO){

                  try{
                    $objDocumentoDTO = new DocumentoDTO();
                    $objDocumentoDTO->setDblIdDocumento($objProtocoloDTOPagina->getDblIdProtocolo());
                    $objDocumentoDTO->setObjInfraSessao(SessaoSEI::getInstance());
                    $objDocumentoDTO->setStrLinkDownload(null);

                    $objDocumentoRN->consultarHtmlFormulario($objDocumentoDTO);
                  } catch (Exception $excXss) {
                    $numErros++;
                    if (strpos(InfraString::transformarCaixaBaixa($excXss->__toString()), $strMsgErroXss) !== false) {
                      self::logar('Verificação XSS - '.$excXss->getStrDescricao().' '.$excXss->getStrDetalhes()."\n\n".$strComplemento);
                    }else{
                      self::logar(InfraException::inspecionar($excXss));
                    }
                  }
                }

                $objAnexoDTO = new AnexoDTO();
                $objAnexoDTO->retNumIdAnexo();
                $objAnexoDTO->retDthInclusao();
                $objAnexoDTO->retStrNome();
                $objAnexoDTO->retDthInclusao();
                $objAnexoDTO->retNumTamanho();
                $objAnexoDTO->retStrHash();
                $objAnexoDTO->setDblIdProtocolo($objProtocoloDTOPagina->getDblIdProtocolo());

                $arrObjAnexoDTO = $objAnexoRN->listarRN0218($objAnexoDTO);

                foreach ($arrObjAnexoDTO as $objAnexoDTO) {

                  if (InfraUtil::getStrMimeType($objAnexoDTO->getStrNome()) == 'text/html') {

                    $numRegistrosProcessados++;

                    $strCaminhoArquivo = $objAnexoRN->obterLocalizacao($objAnexoDTO);

                    $strMsg = '';
                    if (!file_exists($strCaminhoArquivo)) {
                      $strMsg = $strCaminhoArquivo.' não encontrado ';
                    } else if (filesize($strCaminhoArquivo) != $objAnexoDTO->getNumTamanho()) {
                      $strMsg = $strCaminhoArquivo.' tamanho diferente ';
                    } else if (md5_file($strCaminhoArquivo) != $objAnexoDTO->getStrHash()) {
                      $strMsg = $strCaminhoArquivo.' conteúdo corrompido ';
                    }

                    if ($strMsg != '') {

                      $numErros++;
                      self::logar($strMsg.' (documento associado '.$objProtocoloDTOPagina->getStrProtocoloFormatado().')');

                    } else {

                      try {
                        $strConteudo = file_get_contents($objAnexoRN->obterLocalizacao($objAnexoDTO));
                        if ($objProtocoloDTOPagina->getStrStaDocumentoDocumento() == DocumentoRN::$TD_EXTERNO){
                          self::validarXss($strConteudo, $objProtocoloDTOPagina->getStrProtocoloFormatado(), false, $strCaminhoArquivo, $objProtocoloDTOPagina->getDblIdProtocolo());
                        }else{
                          self::validarXss($strConteudo, $objProtocoloDTOPagina->getStrProtocoloFormatado().', anexo '.$objAnexoDTO->getStrNome(), false, $strCaminhoArquivo, $objProtocoloDTOPagina->getDblIdProtocolo());
                        }

                      } catch (Exception $excXss) {
                        $numErros++;
                        if (strpos(InfraString::transformarCaixaBaixa($excXss->__toString()), $strMsgErroXss) !== false) {
                          self::logar('Verificação XSS - '.$excXss->getStrDescricao().' '.$excXss->getStrDetalhes()."\n\n".$strComplemento);
                        }else{
                          self::logar(InfraException::inspecionar($excXss));
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }

      $numSeg = InfraUtil::verificarTempoProcessamento($numSeg);

      self::logar('Verificação XSS - '.InfraUtil::formatarMilhares($numRegistrosProcessados).' documentos verificados em '.InfraData::formatarTimestamp($numSeg). ' ('.InfraUtil::formatarMilhares($numErros).' erros)');

      $numSeg = InfraUtil::verificarTempoProcessamento();

      self::logar('Verificação XSS - Iniciando análise de bases de conhecimento...');

      $objBaseConhecimentoRN 	= new BaseConhecimentoRN();
      $objBaseConhecimentoDTO = new BaseConhecimentoDTO();
      $objBaseConhecimentoDTO->retNumIdBaseConhecimento();
      $objBaseConhecimentoDTO->retStrDescricao();
      $objBaseConhecimentoDTO->retStrSiglaUnidade();
      $objBaseConhecimentoDTO->retStrStaDocumento();
      $objBaseConhecimentoDTO->retDblIdDocumentoEdoc();
      $objBaseConhecimentoDTO->setStrStaEstado(array(BaseConhecimentoRN::$TE_LIBERADO, BaseConhecimentoRN::$TE_RASCUNHO), InfraDTO::$OPER_IN);

      if ($dtaInicio!=null && $dtaFim!=null) {
        $objBaseConhecimentoDTO->adicionarCriterio(array('Geracao', 'Geracao'),
            array(InfraDTO::$OPER_MAIOR_IGUAL, InfraDTO::$OPER_MENOR_IGUAL),
            array($dtaInicio.' 00:00:00', $dtaFim.' 23:59:59'),
            InfraDTO::$OPER_LOGICO_AND);
      }

      $objBaseConhecimentoDTO->setOrdNumIdBaseConhecimento(InfraDTO::$TIPO_ORDENACAO_DESC);

      $arrObjBaseConhecimentoDTO =	$objBaseConhecimentoRN->listar($objBaseConhecimentoDTO);

      $numRegistros 			=	count($arrObjBaseConhecimentoDTO);
      $numRegistrosPagina = 10;
      $numPaginas 				= ceil($numRegistros/$numRegistrosPagina);

      $numRegistrosProcessados = 0;
      $numErros = 0;

      $objEditorRN = new EditorRN();
      $objEdocRN = new EDocRN();

      for ($numPaginaAtual = 0; $numPaginaAtual < $numPaginas; $numPaginaAtual++){

        if ($numPaginaAtual ==  ($numPaginas-1)){
          $numRegistrosAtual = $numRegistros;
        }else{
          $numRegistrosAtual = ($numPaginaAtual+1)*$numRegistrosPagina;
        }

        self::logar('Verificação XSS - Bases de Conhecimento - ['.$numRegistrosAtual.' de '.$numRegistros.']...');

        $offset = ($numPaginaAtual*$numRegistrosPagina);

        if (($offset + $numRegistrosPagina) > $numRegistros) {
          $length = $numRegistros - $offset;
        }else{
          $length = $numRegistrosPagina;
        }

        $arrBasesConhecimentoDTOPagina = array_slice($arrObjBaseConhecimentoDTO, $offset, $length);

        foreach($arrBasesConhecimentoDTOPagina as $objBaseConhecimentoDTOPagina) {

          $numRegistrosProcessados++;

          try {

            if ($objBaseConhecimentoDTOPagina->getStrStaDocumento()==DocumentoRN::$TD_EDITOR_EDOC){

              $objDocumentoDTO = new DocumentoDTO();
              $objDocumentoDTO->setDblIdDocumentoEdoc($objBaseConhecimentoDTOPagina->getDblIdDocumentoEdoc());
              $objEdocRN->consultarHTMLDocumentoRN1204($objDocumentoDTO);

            }else {

              $objEditorDTO = new EditorDTO();
              $objEditorDTO->setDblIdDocumento(null);
              $objEditorDTO->setNumIdBaseConhecimento($objBaseConhecimentoDTOPagina->getNumIdBaseConhecimento());
              $objEditorDTO->setStrSinCabecalho('S');
              $objEditorDTO->setStrSinRodape('S');
              $objEditorDTO->setStrSinCarimboPublicacao('N');
              $objEditorDTO->setStrSinIdentificacaoVersao('N');
              $objEditorDTO->setStrSinProcessarLinks('N');

              $objEditorRN->consultarHtmlVersao($objEditorDTO);
            }

          } catch (Exception $excXss) {
            $numErros++;
            if (strpos(InfraString::transformarCaixaBaixa($excXss->__toString()), $strMsgErroXss) !== false) {
              self::logar('Verificação XSS - '.$excXss->getStrDescricao().' '.$excXss->getStrDetalhes());
            }else{
              self::logar(InfraException::inspecionar($excXss));
            }
          }

          $objAnexoDTO = new AnexoDTO();
          $objAnexoDTO->retNumIdAnexo();
          $objAnexoDTO->retDthInclusao();
          $objAnexoDTO->retStrNome();
          $objAnexoDTO->retDthInclusao();
          $objAnexoDTO->retNumTamanho();
          $objAnexoDTO->retStrHash();
          $objAnexoDTO->setNumIdBaseConhecimento($objBaseConhecimentoDTOPagina->getNumIdBaseConhecimento());

          $arrObjAnexoDTO = $objAnexoRN->listarRN0218($objAnexoDTO);

          foreach ($arrObjAnexoDTO as $objAnexoDTO) {

            if (InfraUtil::getStrMimeType($objAnexoDTO->getStrNome()) == 'text/html') {

              $numRegistrosProcessados++;

              $strCaminhoArquivo = $objAnexoRN->obterLocalizacao($objAnexoDTO);

              $strMsg = '';
              if (!file_exists($strCaminhoArquivo)) {
                $strMsg = $strCaminhoArquivo.' não encontrado ';
              } else if (filesize($strCaminhoArquivo) != $objAnexoDTO->getNumTamanho()) {
                $strMsg = $strCaminhoArquivo.' tamanho diferente ';
              } else if (md5_file($strCaminhoArquivo) != $objAnexoDTO->getStrHash()) {
                $strMsg = $strCaminhoArquivo.' conteúdo corrompido ';
              }

              if ($strMsg != '') {

                $numErros++;
                self::logar($strMsg.' (base de conhecimento associada '.$objBaseConhecimentoDTOPagina->getStrDescricao().'/'.$objBaseConhecimentoDTOPagina->getStrSiglaUnidade().')');

              } else {

                try {
                  $strConteudo = file_get_contents($objAnexoRN->obterLocalizacao($objAnexoDTO));
                  self::validarXss($strConteudo, 'base de conhecimento '.$objBaseConhecimentoDTOPagina->getStrDescricao().'/'.$objBaseConhecimentoDTOPagina->getStrSiglaUnidade().', anexo '.$objAnexoDTO->getStrNome(), false, $strCaminhoArquivo);
                } catch (Exception $excXss) {
                  $numErros++;
                  if (strpos(InfraString::transformarCaixaBaixa($excXss->__toString()), $strMsgErroXss) !== false) {
                    self::logar('Verificação XSS - '.$excXss->getStrDescricao().' '.$excXss->getStrDetalhes());
                  }else{
                    self::logar(InfraException::inspecionar($excXss));
                  }
                }
              }
            }
          }
        }
      }

      $numSeg = InfraUtil::verificarTempoProcessamento($numSeg);

      self::logar('Verificação XSS - '.InfraUtil::formatarMilhares($numRegistrosProcessados).' bases de conhecimento verificadas em '.InfraData::formatarTimestamp($numSeg). ' ('.InfraUtil::formatarMilhares($numErros).' erros)');

      BancoSEI::getInstance()->fecharConexao();
      
    }catch(Exception $e){
      throw new InfraException('Erro na rotina de verificação de XSS.', $e);
    }
  }

  private static function logar($strTexto, $strTipoLog='I'){
    InfraDebug::getInstance()->gravar(InfraString::excluirAcentos($strTexto));
    LogSEI::getInstance()->gravar($strTexto,$strTipoLog);
  }
}
?>