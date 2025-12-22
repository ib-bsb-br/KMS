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

class ContatoINT extends InfraINT {
  
  public static function buscarEtiquetasRI0516($arrNumIdContatos,$opcao){

    $objContatoDTO = new ContatoDTO();
    $objContatoDTO->setNumIdContato($arrNumIdContatos,InfraDTO::$OPER_IN);
    
    $objContatoDTO->retNumIdContato();
    $objContatoDTO->retNumIdContatoAssociado();
    $objContatoDTO->retStrStaNaturezaContatoAssociado();
    $objContatoDTO->retStrExpressaoTratamentoCargo();
    $objContatoDTO->retStrExpressaoCargo();
    $objContatoDTO->retStrNome();
    $objContatoDTO->retStrNomeContatoAssociado();

    $objContatoDTO->setOrdStrNomeContatoAssociado(InfraDTO::$TIPO_ORDENACAO_ASC);
    $objContatoDTO->setOrdStrStaNaturezaContatoAssociado(InfraDTO::$TIPO_ORDENACAO_DESC);
    $objContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);
    
    $objContatoRN = new ContatoRN();
    $arrObjContatoDTO = $objContatoRN->listarComEndereco($objContatoDTO);
    
    $arrLinhas = array();

    for ($i=0;$i<count($arrObjContatoDTO);$i++){

	    $tratamento = '';
	    $expNome1 = '';
	    $expNome2 = '';
	    $titulo = '';
	    $contextoContato1 = '';
	    $contextoContato2 = '';
	    $endereco = '';
      $complemento = '';
	    $cidade = '';

	    if ($arrObjContatoDTO[$i]->getNumIdContato() != $arrObjContatoDTO[$i]->getNumIdContatoAssociado()){
	      
  	    if (!InfraString::isBolVazia($arrObjContatoDTO[$i]->getStrExpressaoTratamentoCargo())){
  	    	$tratamento = $arrObjContatoDTO[$i]->getStrExpressaoTratamentoCargo().'<br />';
  	    }
	    
  	    if ($opcao!=2){
    	    if (!InfraString::isBolVazia($arrObjContatoDTO[$i]->getStrNome())){
    	    	$expNome = $arrObjContatoDTO[$i]->getStrNome();
    	    	if (strlen($expNome)>65){
    	    		$expNome1 = substr($expNome,0,60).'<br />';	    		
    	    		$expNome2 = substr($expNome,60).'<br />';
    	    	}else {
    	    		$expNome1 = $expNome.'<br />';
    	    	}
    	    }    
  	    }
		   
  	    if (!InfraString::isBolVazia($arrObjContatoDTO[$i]->getStrExpressaoCargo())){
  	    	$titulo = $arrObjContatoDTO[$i]->getStrExpressaoCargo().'<br />';
  	    }       
	    }
	    
	    if (!InfraString::isBolVazia($arrObjContatoDTO[$i]->getStrNomeContatoAssociado())){
	    	$contextoContato = $arrObjContatoDTO[$i]->getStrNomeContatoAssociado();
	    	if(strlen($contextoContato)>65){
	    		$contextoContato1 = substr($contextoContato,0,60).'<br />';
	    		$contextoContato2 = substr($contextoContato,60).'<br />';
	    	}else{
	    		$contextoContato1 = $contextoContato.'<br />';
	    	}
	    }    

	    if ($opcao!=3){
	      
        $strEndereco = $arrObjContatoDTO[$i]->getStrEndereco();
        $strComplemento = $arrObjContatoDTO[$i]->getStrComplemento();
        $strCep = $arrObjContatoDTO[$i]->getStrCep();
        $strNomeCidade = $arrObjContatoDTO[$i]->getStrNomeCidade();
        $strSiglaUf = $arrObjContatoDTO[$i]->getStrSiglaUf();
        $strNomePais = $arrObjContatoDTO[$i]->getStrNomePais();

        
  	    if (!InfraString::isBolVazia($strEndereco)){
  	    	$endereco = $strEndereco.'<br />';
  	    }

        if (!InfraString::isBolVazia($strComplemento)){
          $complemento = $strComplemento.'<br />';
        }

        //Cep - NomeCidade - SiglaUf - Pais (se diferente de Brasil)
  
  	    $cidade = ''; 
  	    $separador = '';
  	    if (!InfraString::isBolVazia($strCep)){
  	      $cidade .= $separador.$strCep;
  	      $separador = ' - ';	
  	    }
  	    
  	    if (!InfraString::isBolVazia($strNomeCidade)){
  	    	$cidade .= $separador.$strNomeCidade;
  	    	$separador = ' - ';	
  	    }    	       
  
  	    if (!InfraString::isBolVazia($strSiglaUf)){
  	    	$cidade .= $separador.$strSiglaUf;
  	    	$separador = ' - ';	
  	    }
  	    
  	    if (!InfraString::isBolVazia($strNomePais) && strtolower($strNomePais)!='brasil'){
  	    	$cidade .= $separador.$strNomePais;
  	    	$separador = ' - ';	
  	    }
	    }	    
	    

      $arrColunas = array();
      $arrColunas[] = $arrObjContatoDTO[$i]->getNumIdContato();
      $arrColunas[] = PaginaSEI::tratarHTML($tratamento.$expNome1.$expNome2.$titulo.$contextoContato1.$contextoContato2.$endereco.$complemento.$cidade);
      $arrLinhas[] = $arrColunas;
    }

    return PaginaSEI::getInstance()->gerarItensTabelaDinamica(array_reverse($arrLinhas));
  }
  
  public static function montarSelectContatosGrupoRI0495($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdGrupoContato){	
    
    
    $objContatoDTO = new ContatoDTO();
    $objContatoDTO->retNumIdContato();
    $objContatoDTO->retStrNome();
    $objContatoDTO->setNumIdGrupoContato($numIdGrupoContato);
    $objContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);


    $objContatoRN = new ContatoRN();
    $arrObjContatoDTO = $objContatoRN->listarGrupoRN0566($objContatoDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjContatoDTO, 'IdContato', 'Nome');
  }	

  public static function montarSelectContatosRI0835($arr,$numIdGrupoContato=null){
    $ret = '';

    if (count($arr)) {
      $objContatoDTO = new ContatoDTO();
      $objContatoDTO->retNumIdContato();
      $objContatoDTO->retStrNome();
      $objContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);
      $objContatoDTO->setNumIdContato($arr, InfraDTO::$OPER_IN);

      $objContatoRN = new ContatoRN();
      $arrObjContatoDTO = $objContatoRN->listarRN0325($objContatoDTO);


      if ($numIdGrupoContato !== null) {
        $objContatoDTO = new ContatoDTO();
        $objContatoDTO->retNumIdContato();
        $objContatoDTO->retStrNome();
        $objContatoDTO->setNumIdGrupoContato($numIdGrupoContato);
        $objContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

        $objContatoRN = new ContatoRN();
        $arrContatosAntigos = $objContatoRN->listarGrupoRN0566($objContatoDTO);

        $arrTemp = InfraArray::indexarArrInfraDTO($arrObjContatoDTO, 'IdContato');
        foreach ($arrContatosAntigos as $dto) {
          if (!isset($arrTemp[$dto->getNumIdContato()])) {
            $arrObjContatoDTO[] = $dto;
          }
        }

      }
      $ret = parent::montarSelectArrInfraDTO(null, null, null, $arrObjContatoDTO, 'IdContato', 'Nome');
    }
    return $ret;
  }

  public static function montarSelectDestinatarios($arr){
    $ret = '';

    if (count($arr)) {

      $objContatoDTO = new ContatoDTO();
      $objContatoDTO->setBolExclusaoLogica(false);
      $objContatoDTO->retNumIdContato();
      $objContatoDTO->retStrSigla();
      $objContatoDTO->retStrNome();
      $objContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

      $objContatoDTO->setNumIdContato($arr, InfraDTO::$OPER_IN);

      $objContatoRN = new ContatoRN();
      $arrObjContatoDTO = $objContatoRN->listarRN0325($objContatoDTO);

      foreach($arrObjContatoDTO as $objContatoDTO){
        $objContatoDTO->setStrNome(ContatoINT::formatarNomeSiglaRI1224($objContatoDTO->getStrNome(),$objContatoDTO->getStrSigla()));
      }

      $ret = parent::montarSelectArrInfraDTO(null, null, null, $arrObjContatoDTO, 'IdContato', 'Nome');
    }
    return $ret;
  }

  public static function formatarNomeSiglaRI1224($strNome, $strSigla){
    $str = $strNome;
    
    if (!InfraString::isBolVazia($strSigla)){
      $str .= ' ('.$strSigla.')';
    }
    
    return $str;
  }

  public static function autoCompletarContextoRI1225($strPalavrasPesquisa,$numIdGrupoContato){

    $arrObjContatoDTO = array();

    $objPesquisaTipoContatoDTO = new PesquisaTipoContatoDTO();
    $objPesquisaTipoContatoDTO->setStrStaAcesso(TipoContatoRN::$TA_CONSULTA_RESUMIDA);

    $objTipoContatoRN = new TipoContatoRN();
    $arrIdTipoContatoAcesso = $objTipoContatoRN->pesquisarAcessoUnidade($objPesquisaTipoContatoDTO);

    if (count($arrIdTipoContatoAcesso)) {

      $objContatoDTO = new ContatoDTO();
      $objContatoDTO->retNumIdContato();
      $objContatoDTO->retStrSigla();
      $objContatoDTO->retStrNome();

      $objContatoDTO->setStrPalavrasPesquisa($strPalavrasPesquisa);

      if ($numIdGrupoContato != '') {
        $objContatoDTO->setNumIdGrupoContato($numIdGrupoContato);
      }

      $objContatoDTO->adicionarCriterio(array('StaAcessoTipoContato', 'IdTipoContato'),
                                        array(InfraDTO::$OPER_DIFERENTE, InfraDTO::$OPER_IN),
                                        array(TipoContatoRN::$TA_NENHUM, $arrIdTipoContatoAcesso),
                                        InfraDTO::$OPER_LOGICO_OR);

      $objContatoDTO->setStrSinAtivoTipoContato('S');
      $objContatoDTO->setNumMaxRegistrosRetorno(50);
      $objContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

      $objContatoRN = new ContatoRN();
      $arrObjContatoDTO = $objContatoRN->pesquisarRN0471($objContatoDTO);

      foreach ($arrObjContatoDTO as $objContatoDTO) {
        $objContatoDTO->setStrNome(ContatoINT::formatarNomeSiglaRI1224($objContatoDTO->getStrNome(), $objContatoDTO->getStrSigla()));
      }
    }

    return $arrObjContatoDTO;
  }

  public static function autoCompletarPesquisa($strPalavrasPesquisa){

    $ret = null;

    $objContatoDTO = new ContatoDTO();
    $objContatoDTO->setBolExclusaoLogica(false);
    $objContatoDTO->retNumIdContato();
    $objContatoDTO->retStrSigla();
    $objContatoDTO->retStrNome();
    $objContatoDTO->retStrSinSistemaTipoContato();

    $objContatoDTO->setStrPalavrasPesquisa($strPalavrasPesquisa);

    $objContatoDTO->setNumMaxRegistrosRetorno(50);
    $objContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objContatoRN = new ContatoRN();
    $ret = InfraArray::indexarArrInfraDTO($objContatoRN->pesquisarRN0471($objContatoDTO),'IdContato');

    $arrIdContatoSistema = array();
    foreach ($ret as $objContatoDTO) {

      if ($objContatoDTO->getStrSinSistemaTipoContato()=='S'){
        $arrIdContatoSistema[] = $objContatoDTO->getNumIdContato();
      }

      $objContatoDTO->setStrNome(ContatoINT::formatarNomeSiglaRI1224($objContatoDTO->getStrNome(), $objContatoDTO->getStrSigla()));
    }


    if (count($arrIdContatoSistema)){

      $objUsuarioDTO = new UsuarioDTO();
      $objUsuarioDTO->setDistinct(true);
      $objUsuarioDTO->setBolExclusaoLogica(false);
      $objUsuarioDTO->retNumIdContato();
      $objUsuarioDTO->retStrIdOrigem();
      $objUsuarioDTO->retStrSigla();
      $objUsuarioDTO->retStrNome();
      $objUsuarioDTO->retDblCpfContato();
      $objUsuarioDTO->setNumIdContato($arrIdContatoSistema, InfraDTO::$OPER_IN);
      $objUsuarioDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);
      $objUsuarioDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

      $objUsuarioRN = new UsuarioRN();
      $arrObjUsuarioDTO = $objUsuarioRN->pesquisar($objUsuarioDTO);

      $arrFiltro = array();
      foreach($arrObjUsuarioDTO as $objUsuarioDTO){
        $arrFiltro[$objUsuarioDTO->getStrIdOrigem().'¥'.$objUsuarioDTO->retDblCpfContato()][$objUsuarioDTO->getStrNome()][] = $objUsuarioDTO;
      }

      foreach($arrFiltro as $arrPorNome) {
        foreach ($arrPorNome as $strNome => $arrObjUsuarioDTO) {

          $ret[$arrObjUsuarioDTO[0]->getNumIdContato()]->setStrNome(ContatoINT::formatarNomeSiglaRI1224($strNome, implode(', ', array_unique(InfraArray::converterArrInfraDTO($arrObjUsuarioDTO,'Sigla')))));

          $numUsuarios = count($arrObjUsuarioDTO);
          for($i=1; $i < $numUsuarios; $i++){
            unset($ret[$arrObjUsuarioDTO[$i]->getNumIdContato()]);
          }
        }
      }
    }

    return array_values($ret);
  }

  public static function autoCompletarUsuariosPesquisa($strPalavrasPesquisa, $strSinUsuariosInternos, $strSinUsuariosExternos){

    $ret = array();

    if ($strSinUsuariosInternos=='S' || $strSinUsuariosExternos=='S'){
      
      $objUsuarioDTO = new UsuarioDTO();
      $objUsuarioDTO->setDistinct(true);
      $objUsuarioDTO->setBolExclusaoLogica(false);
      $objUsuarioDTO->retNumIdContato();
      $objUsuarioDTO->retStrIdOrigem();
      $objUsuarioDTO->retStrSigla();
      $objUsuarioDTO->retStrNome();
      $objUsuarioDTO->retDblCpfContato();
      $objUsuarioDTO->setStrPalavrasPesquisa($strPalavrasPesquisa);

      $arrStaTipo = array();
      if ($strSinUsuariosInternos=='S'){
        $arrStaTipo[] = UsuarioRN::$TU_SIP;
      }

      if ($strSinUsuariosExternos=='S') {
        $arrStaTipo[] = UsuarioRN::$TU_EXTERNO;
      }

      $objUsuarioDTO->setStrStaTipo($arrStaTipo, InfraDTO::$OPER_IN);

      $objUsuarioDTO->setNumMaxRegistrosRetorno(50);

      $objUsuarioDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);
      $objUsuarioDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

      $objUsuarioRN = new UsuarioRN();
      $arrObjUsuarioDTO = $objUsuarioRN->pesquisar($objUsuarioDTO);

      $arrFiltro = array();
      foreach($arrObjUsuarioDTO as $objUsuarioDTO){
        $arrFiltro[$objUsuarioDTO->getStrIdOrigem().'¥'.$objUsuarioDTO->retDblCpfContato()][$objUsuarioDTO->getStrNome()][$objUsuarioDTO->getStrSigla()] = $objUsuarioDTO->getNumIdContato();
      }

      foreach($arrFiltro as $arrPorNome) {
        foreach ($arrPorNome as $strNome => $arrPorSigla) {
          $objContatoDTO = new ContatoDTO();
          $objContatoDTO->setNumIdContato($arrPorSigla[key($arrPorSigla)]);
          $objContatoDTO->setStrNome(ContatoINT::formatarNomeSiglaRI1224($strNome, implode(', ', array_keys($arrPorSigla))));
          $ret[] = $objContatoDTO;
        }
      }
    }

    return $ret;
  }

  public static function autoCompletarContextoSubstituicao($strPalavrasPesquisa){

    $objPesquisaTipoContatoDTO = new PesquisaTipoContatoDTO();
    $objPesquisaTipoContatoDTO->setStrStaAcesso(TipoContatoRN::$TA_CONSULTA_RESUMIDA);

    $objTipoContatoRN = new TipoContatoRN();
    $arrIdTipoContatoAcesso = $objTipoContatoRN->pesquisarAcessoUnidade($objPesquisaTipoContatoDTO);

    $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
    $arrIdTipoContatoAcesso[] = $objInfraParametro->getValor('ID_TIPO_CONTATO_TEMPORARIO');

    $objContatoDTO = new ContatoDTO();
    $objContatoDTO->retNumIdContato();
    $objContatoDTO->retStrSigla();
    $objContatoDTO->retStrNome();

    $objContatoDTO->setStrPalavrasPesquisa($strPalavrasPesquisa);

    $objContatoDTO->adicionarCriterio(array('StaAcessoTipoContato', 'IdTipoContato'),
                                      array(InfraDTO::$OPER_DIFERENTE, InfraDTO::$OPER_IN),
                                      array(TipoContatoRN::$TA_NENHUM, $arrIdTipoContatoAcesso),
                                      InfraDTO::$OPER_LOGICO_OR);

    $objContatoDTO->setStrSinAtivoTipoContato('S');
    $objContatoDTO->setNumMaxRegistrosRetorno(50);
    $objContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objContatoRN = new ContatoRN();
    $arrObjContatoDTO = $objContatoRN->pesquisarRN0471($objContatoDTO);

    foreach ($arrObjContatoDTO as $objContatoDTO) {
      $objContatoDTO->setStrNome(ContatoINT::formatarNomeSiglaRI1224($objContatoDTO->getStrNome(), $objContatoDTO->getStrSigla()));
    }

    return $arrObjContatoDTO;
  }

  public static function montarContatoAssociado($bolSip, $numIdSip, $bolSei, $numIdSei, $bolOrigem, $strIdOrigem, $numIdContato, $strSigla, $strNome, $bolReadOnlySiglaNome, $strForm){

    if (PaginaSEI::getInstance()->isBolNavegadorFirefox()) {

      if ($bolSip){
        if ($bolSei && $bolOrigem) {
          $numAltura = '17em';
          $strTopLabelSip = '5%';
          $strTopTextSip = '18%';
          $strTopLabelSei = '35%';
          $strTopTextSei = '48%';
          $strTopLabelOrigem = '65%';
          $strTopTextOrigem = '78%';
        }else {
          $numAltura = '12em';
          $strTopLabelSip = '6%';
          $strTopTextSip = '25%';
          if ($bolSei) {
            $strTopLabelSei = '51%';
            $strTopTextSei = '70%';
          } else if ($bolOrigem) {
            $strTopLabelOrigem = '51%';
            $strTopTextOrigem = '70%';
          }
        }
      }else{
        $numAltura = '12em';
      }

      if ($numAltura == '17em'){
        $strTopLabelSigla = '5%';
        $strTopTextSigla = '18%';
        $strTopLabelNome = '35%';
        $strTopTextNome = '48%';
      }else{
        $strTopLabelSigla = '6%';
        $strTopTextSigla = '25%';
        $strTopLabelNome = '51%';
        $strTopTextNome = '70%';
      }

    } else {

      if ($bolSip) {
        if ($bolSei && $bolOrigem) {
          $numAltura = '17em';
          $strTopLabelSip = '12%';
          $strTopTextSip = '24%';
          $strTopLabelSei = '40%';
          $strTopTextSei = '52%';
          $strTopLabelOrigem = '68%';
          $strTopTextOrigem = '80%';
        } else {
          $numAltura = '12em';
          $strTopLabelSip = '20%';
          $strTopTextSip = '36%';
          if ($bolSei) {
            $strTopLabelSei = '55%';
            $strTopTextSei = '71%';
          } else if ($bolOrigem) {
            $strTopLabelOrigem = '55%';
            $strTopTextOrigem = '71%';
          }
        }
      }else{
        $numAltura = '12em';
      }

      if ($numAltura == '17em'){
        $strTopLabelSigla = '12%';
        $strTopTextSigla = '24%';
        $strTopLabelNome = '40%';
        $strTopTextNome = '52%';
      }else{
        $strTopLabelSigla = '20%';
        $strTopTextSigla = '36%';
        $strTopLabelNome = '55%';
        $strTopTextNome = '71%';
      }
    }

    $strHtml = '<div id="divContatoAssociado" class="infraAreaDados" style="height:'.$numAltura.'">';

   if ($bolSip || $bolSei || $bolOrigem) {
     $strHtml .= '<fieldset id="fldCodigo" class="infraFieldset" style="position:absolute;left:0%;top:0%;height:85%;width:20%;">
                  <legend class="infraLegend">&nbsp;Códigos&nbsp;</legend>'."\n";

     if ($bolSip) {
       $strHtml .= '<label id = "lblCodigoSip" for="txtCodigoSip" class="infraLabelObrigatorio" style = "position:absolute;left:8%;top:'.$strTopLabelSip.';width:70%;" > SIP:</label >
                    <input type = "text" id = "txtCodigoSip" name = "txtCodigoSip" class="infraText infraReadOnly" style = "position:absolute;left:8%;top:'.$strTopTextSip.';width:70%;" value = "' . PaginaSEI::tratarHTML($numIdSip) . '" tabindex = "' . PaginaSEI::getInstance()->getProxTabDados() . '" readonly = "readonly" />'."\n";
     }

     if ($bolSei) {
       $strHtml .= '<label id="lblCodigoSei" for="txtCodigoSei" class="infraLabelOpcional" style="position:absolute;left:8%;top:' . $strTopLabelSei . ';width:70%;">SEI:</label>
                    <input type="text" id="txtCodigoSei" name="txtCodigoSei" class="infraText" style="position:absolute;left:8%;top:' . $strTopTextSei . ';width:70%;" value="' . PaginaSEI::tratarHTML($numIdSei) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '" onkeypress="return infraMascaraNumero(this, event)" />' . "\n";
     }

     if ($bolOrigem) {
       $strHtml .= '<label id="lblCodigoOrigem" for="txtCodigoOrigem" class="infraLabelOpcional" style="position:absolute;left:8%;top:' . $strTopLabelOrigem . ';width:70%;">Origem:</label>
                    <input type="text" id="txtCodigoOrigem" name="txtCodigoOrigem" class="infraText" style="position:absolute;left:8%;top:' . $strTopTextOrigem . ';width:70%;" value="' . PaginaSEI::tratarHTML($strIdOrigem) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '" readonly="readonly" />' . "\n";
     }

     $strHtml .= '</fieldset>';
   }

    $strHtml .= '<fieldset id="fldContatoAssociado" class="infraFieldset" style="position:absolute;left:'.($bolSip?'25%':'0').';top:0%;height:85%;width:70%;">
    <legend class="infraLegend">&nbsp;Contato Associado&nbsp;</legend>

    <label id="lblSiglaContatoAssociado" for="txtSiglaContatoAssociado" class="infraLabelObrigatorio" style="position:absolute;left:2%;top:'.$strTopLabelSigla.';width:45%">Sigla</span>:</label>
    <input type="text" id="txtSiglaContatoAssociado" name="txtSiglaContatoAssociado" class="infraText' . ($bolReadOnlySiglaNome ? ' infraReadOnly' : '') . '" style="position:absolute;left:2%;top:'.$strTopTextSigla.';width:45%" value="'.PaginaSEI::tratarHTML($strSigla).'" tabindex="'.PaginaSEI::getInstance()->getProxTabDados().'" ' . ($bolReadOnlySiglaNome ? 'readonly="true"' : '') . ' />

    <label id="lblNomeContatoAssociado" for="txtNomeContatoAssociado" class="infraLabelObrigatorio" style="position:absolute;left:2%;top:'.$strTopLabelNome.';width:80%">Nome:</label>
    <input type="text" id="txtNomeContatoAssociado" name="txtNomeContatoAssociado" class="infraText' . ($bolReadOnlySiglaNome ? ' infraReadOnly' : '') . '" style="position:absolute;left:2%;top:'.$strTopTextNome.';width:80%" value="'.PaginaSEI::tratarHTML($strNome).'" tabindex="'.PaginaSEI::getInstance()->getProxTabDados().'" ' . ($bolReadOnlySiglaNome ? 'readonly="true"' : '') . ' />

    <div id="divOpcoesContato" style="position:absolute;left:90%;top:30%;">
      <img id="imgAlterarContato" onclick="seiAlterarContato(\''.$numIdContato.'\', \'txtNomeContatoAssociado\', \''.$strForm.'\',\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=contato_alterar&acao_origem='.$_GET['acao']).'\')" src="'.PaginaSEI::getInstance()->getDiretorioImagensLocal().'/sei_alterar_contato.png" alt="Alterar Dados do Contato Associado" title="Alterar Dados do Contato Associado" class="infraImg" style="width:3.2em;height:3.2em" tabindex="'.PaginaSEI::getInstance()->getProxTabDados().'"/>
    </div>
  </fieldset>

  <input type="hidden" id="hdnContatoObject" name="hdnContatoObject" value="" />
  <input type="hidden" id="hdnContatoIdentificador" name="hdnContatoIdentificador" value="" />
  </div>
  <br />';

    echo $strHtml;
  }

  function alterarContatoAssociado(){
    seiAlterarContato('<?=$objOrgaoDTO->getNumIdContato();?>', 'txtContato', 'frmOrgaoCadastro','<?=$strLinkAlterarContato?>');
  }

  public static function autoCompletarAssociado($strPalavrasPesquisa){

    $arrObjContatoDTO = array();

    $objPesquisaTipoContatoDTO = new PesquisaTipoContatoDTO();
    $objPesquisaTipoContatoDTO->setStrStaAcesso(TipoContatoRN::$TA_CONSULTA_RESUMIDA);

    $objTipoContatoRN = new TipoContatoRN();
    $arrIdTipoContatoAcesso = $objTipoContatoRN->pesquisarAcessoUnidade($objPesquisaTipoContatoDTO);

    if (count($arrIdTipoContatoAcesso)) {
      $objContatoDTO = new ContatoDTO();
      $objContatoDTO->retNumIdContato();
      $objContatoDTO->retStrNome();
      $objContatoDTO->setNumIdContatoAssociado($objContatoDTO->getObjInfraAtributoDTO('IdContato'));
      $objContatoDTO->setStrStaNatureza(ContatoRN::$TN_PESSOA_JURIDICA);

      $objContatoDTO->setStrPalavrasPesquisa($strPalavrasPesquisa);

      $objContatoDTO->adicionarCriterio(array('StaAcessoTipoContato', 'IdTipoContato'),
                                        array(InfraDTO::$OPER_DIFERENTE, InfraDTO::$OPER_IN),
                                        array(TipoContatoRN::$TA_NENHUM, $arrIdTipoContatoAcesso),
                                        InfraDTO::$OPER_LOGICO_OR);

      $objContatoDTO->setStrSinAtivoTipoContato('S');
      $objContatoDTO->setNumMaxRegistrosRetorno(50);
      $objContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

      $objContatoRN = new ContatoRN();
      $arrObjContatoDTO = $objContatoRN->pesquisarRN0471($objContatoDTO);
    }

    return $arrObjContatoDTO;
  }
}
?>