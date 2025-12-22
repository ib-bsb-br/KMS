<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 30/07/2008 - criado por mga
 *
 * Versão do Gerador de Código: 1.21.0
 *
 * Versão no CVS: $Id$
 */

require_once dirname(__FILE__).'/../SEI.php';

class DocumentoINT extends InfraINT {

	public static $TV_HTML = 'H';
	public static $TV_TEXTO = 'T';

  public static function formatarIdentificacao($objDocumentoDTO){
    return PaginaSEI::tratarHTML($objDocumentoDTO->getStrNomeSerie().' '.$objDocumentoDTO->getStrNumero());
  }

	public static function verificarDocumentoRecebidoDuplicado($dtaElaboracao,$numIdSerie,$numNumero){
		$objDocumentoDTO = new DocumentoDTO();
		$objDocumentoDTO->retDblIdDocumento();
		$objDocumentoDTO->retStrProtocoloDocumentoFormatado();
		$objDocumentoDTO->setDtaGeracaoProtocolo(trim($dtaElaboracao));
		$objDocumentoDTO->setNumIdSerie(trim($numIdSerie));
		$objDocumentoDTO->setStrNumero(trim($numNumero));
		$objDocumentoDTO->setStrStaProtocoloProtocolo(ProtocoloRN::$TP_DOCUMENTO_RECEBIDO);

		$objDocumentoRN = new DocumentoRN();
		$arrObjDocumentoDTO = $objDocumentoRN->listarRN0008($objDocumentoDTO);

		if (count($arrObjDocumentoDTO)){
			$objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();
			$objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_DOCUMENTOS_RECEBIDOS);
			$objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_AUTORIZADO);
			$objPesquisaProtocoloDTO->setDblIdProtocolo($arrObjDocumentoDTO[0]->getDblIdDocumento());

			$objProtocoloRN = new ProtocoloRN();
			$arrObjProtocoloDTO = $objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO);

			if (count($arrObjProtocoloDTO)) {
				return $arrObjDocumentoDTO[0];
			}
		}
		return null;
	}

	public static function selecionarIconeAnexo($strNomeAnexo){

		$ext = explode('.',$strNomeAnexo);

		if (count($ext)>1){

			$ext = strtolower($ext[count($ext)-1]);

			switch($ext){
				case 'doc':
        case 'docx':
					return 'doc.gif';

				case 'jpeg':	
				case 'jpg':
				case 'gif':
				case 'bmp':
				case 'png':  
					return 'imagem.gif';

				case 'ppt':
					return 'ppt.gif';
					break;

				case 'pps':
					return 'pps.gif';

				case 'xls':
				case 'xlsx':
				  
					return 'xls.gif';

				case 'txt':
					return 'txt.gif';

				case 'pdf':
					return 'pdf.gif';

				case 'exe':
				case 'com':
					return 'aplicativo.gif';

				case 'zip':
					return 'zip.gif';

				case 'rar':
					return 'rar.gif';

				case 'ods':
				  return 'ods.gif';
				  
				case 'odt':
				  return 'odt.gif';

				case 'odp':
				  return 'odp.gif';
				  
				case 'odg':
				  return 'odg.gif';
				  
				case 'html':
				case 'htm':
					return 'html.gif';

				case 'avi':
				case 'swf':
				case 'wmv':
				case 'mp4':  
					return 'video.gif';

				case 'mp3':
				case 'wma':
					return 'audio.gif';
			}
		}
		return null;
	}

	public static function montarIdentificacaoArvore($objDocumentoDTO){
		if ($objDocumentoDTO->getStrStaProtocoloProtocolo()==ProtocoloRN::$TP_DOCUMENTO_GERADO){
			if ($objDocumentoDTO->getStrNumero()!=null){
			  /*
			  if (is_numeric($objDocumentoDTO->getStrNumero())){
			    $strNumero = InfraUtil::formatarMilhares($objDocumentoDTO->getStrNumero());
			  }else{
			    $strNumero = $objDocumentoDTO->getStrNumero();
			  }
			  
			  $strIdentificacaoDocumento = $objDocumentoDTO->getStrNomeSerie().' '.$strNumero.' ('.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().')';
			  */
			  
				$strIdentificacaoDocumento = $objDocumentoDTO->getStrNomeSerie().' '.$objDocumentoDTO->getStrNumero().' ('.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().')';
			}else{
				$strIdentificacaoDocumento = $objDocumentoDTO->getStrNomeSerie().' '.$objDocumentoDTO->getStrSiglaUnidadeGeradoraProtocolo().' '.$objDocumentoDTO->getStrProtocoloDocumentoFormatado();
			}
		}else{
			$strIdentificacaoDocumento = $objDocumentoDTO->getStrNomeSerie(). ' '.$objDocumentoDTO->getStrNumero().' ('.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().')';
		}

		return $strIdentificacaoDocumento;
	}

	public static function montarTooltipEmail($parObjDocumentoDTO, &$bolFlagCCO){

		$strRet = '';
		$bolFlagCCO = false;

    $strConteudo = $parObjDocumentoDTO->getStrConteudo();

		if (!InfraString::isBolVazia($strConteudo) && substr($strConteudo,0,5) == '<?xml'){

			$objXml = new DomDocument('1.0','iso-8859-1');

			$objXml->loadXML($strConteudo);

			$arrAtributos = $objXml->getElementsByTagName('atributo');
			
			foreach($arrAtributos as $atributo){
				if ($atributo->getAttribute('nome') == 'Data'){
					 $strRet .= utf8_decode($atributo->getAttribute('titulo')).': ';
					 $strRet .= self::formatarTagConteudo(self::$TV_TEXTO,$atributo->nodeValue).'\n';
					 break;
				}
			}
			
			foreach($arrAtributos as $atributo){
				if ($atributo->getAttribute('nome') == 'De'){
					 $strRet .= utf8_decode($atributo->getAttribute('titulo')).': ';
					 $strRet .= self::formatarTagConteudo(self::$TV_TEXTO,$atributo->nodeValue).'\n';
					 break;
				}
			}

			foreach($arrAtributos as $atributo){
				if ($atributo->getAttribute('nome') == 'Para'){
					 $strRet .= utf8_decode($atributo->getAttribute('titulo')).': ';
				   $arrDestinatarios = $atributo->getElementsByTagName('valor');
				   $numDestinatarios = 0;
				   foreach($arrDestinatarios as $objDestinatario){
				     if ($numDestinatarios++){
				       $strRet .= '          ';
				     }
				     $strRet .= self::formatarTagConteudo(self::$TV_TEXTO,trim($objDestinatario->nodeValue)).'\n';
				   }
				   break;
				}
			}

      foreach($arrAtributos as $atributo){
        if ($atributo->getAttribute('nome') == 'Cco'){
          $strRet .= utf8_decode($atributo->getAttribute('titulo')).': ';
          $arrDestinatarios = $atributo->getElementsByTagName('valor');
          $numDestinatarios = 0;
          foreach($arrDestinatarios as $objDestinatario){
            if ($numDestinatarios++){
              $strRet .= '          ';
            }
            $strRet .= self::formatarTagConteudo(self::$TV_TEXTO,trim($objDestinatario->nodeValue)).'\n';
          }
          $bolFlagCCO = true;
          break;
        }
      }

			
			foreach($arrAtributos as $atributo){
				if ($atributo->getAttribute('nome') == 'Assunto'){
					 $strRet .= utf8_decode($atributo->getAttribute('titulo')).': ';
					 $strRet .= self::formatarTagConteudo(self::$TV_TEXTO,$atributo->nodeValue).'\n';
					 break;
				}
				
			}
		}
		return $strRet;
	}
	
	public static function montarTooltipAssinatura($parObjDocumentoDTO){
	  $strRet = ($parObjDocumentoDTO->getStrStaDocumento()==DocumentoRN::$TD_EXTERNO) ? 'Autenticado por:'."\n" : 'Assinado por:'."\n";
	  $arrObjAssinaturaDTO = $parObjDocumentoDTO->getArrObjAssinaturaDTO();
	  foreach($arrObjAssinaturaDTO as $objAssinaturaDTO){
	    $strRet .= $objAssinaturaDTO->getStrNome().' / '.$objAssinaturaDTO->getStrTratamento()."\n";
	  }
	  return PaginaSEI::tratarHTML($strRet);
	}

	public static function montarTooltipAndamento($strTexto){
		return str_replace("\r\n", "\\n", str_replace("'", '\'', str_replace('"', '\"', str_replace('\\','\\\\',$strTexto))));
	}
	
	public static function formatarTagConteudo($strTipoVisualizacao, $tag){
    $ret = $tag;
    if ($ret != '') {
      $ret = utf8_decode($tag);
      if ($strTipoVisualizacao == self::$TV_HTML) {
        //$ret = nl2br(str_replace(' ','&nbsp;',InfraPagina::tratarHTML($ret)));
        $ret = nl2br(InfraPagina::tratarHTML($ret));
      }
    }
		return $ret;
	}

	public static function formatarExibicaoConteudo($strTipoVisualizacao, $strConteudo, $objInfraSessao=null, $strLinkDownload=null){

		$strResultado = '';

		if (!InfraString::isBolVazia($strConteudo)){

			if (substr($strConteudo,0,5) != '<?xml'){
				$strResultado = $strConteudo;
			}else{

				//die($strConteudo);

				/*
				 $strConteudo = '<?xml version="1.0"?>
				 <documento>
				 <atributo id="" tipo="" nome="" titulo="Atributo A">nomeA</atributo>
				 <atributo id="" tipo="" nome="" titulo="Atributo B">nomeB</atributo>
				 <atributo id="" tipo="" nome="" titulo="Atributo C">
				 <valores>
				 <valor id="" tipo="" nome="" titulo="Valor C1">nomeC1</valor>
				 <valor id="" tipo="" nome="" titulo="Valor C2">nomeC2</valor>
				 </valores>
				 </atributo>
				 <atributo id="" tipo="" nome="" titulo="Atributo D">
				 <valores id="" tipo="" nome="" titulo="Valores D1">
				 <valor id="" tipo="" nome="" titulo="Valor D1V1">D1V1</valor>
				 <valor id="" tipo="" nome="" titulo="Valor D1V2">D1V2</valor>
				 <valor id="" tipo="" nome="" titulo="Valor D1V3">D1V3</valor>
				 </valores>
				 <valores id="" tipo="" nome="" titulo="Valores D2">
				 <valor id="" tipo="" nome="" titulo="Valor D2V1">D2V1</valor>
				 <valor id="" tipo="" nome="" titulo="Valor D2V2">D2V2</valor>
				 <valor id="" tipo="" nome="" titulo="Valor D2V3">D2V3</valor>
				 </valores>
				 <valores id="" tipo="" nome="" titulo="Valores D3">
				 <valor id="" tipo="" nome="" nome="d3v1" titulo="Valor D3V1">D3V1</valor>
				 <valor id="" tipo="" nome="" titulo="Valor D3V2" ocultar="S">D3V2</valor>
				 <valor id="" tipo="" nome="" titulo="Valor D3V3">D3V3</valor>
				 </valores>
				 </atributo>
				 </documento>';

				$strConteudo = '<?xml version="1.0" encoding="iso-8859-1"?>
        <formulario>
        <atributo id="" nome="" tipo="OPCOES">
        <rotulo>Atributo A</rotulo>
        <dominio id="" valor="">Opção X</dominio>
        </atributo>
        <atributo id="" nome="" tipo="TEXTO_MASCARA">
        <rotulo>Atributo B</rotulo>
				<valor>Valor B</valor>
        </atributo>
        <atributo id="" nome="" tipo="SINALIZADOR">
        <rotulo>Atributo C</rotulo>
        <valor>S</valor>
        </atributo>
        <atributo id="" nome="" tipo="LISTA">
        <rotulo>Atributo D</rotulo>
        <dominio id="" valor="">Item X</dominio>
        </atributo>
        </formulario>';
				*/


        //internamente o DOM utiliza UTF-8 mesmo passando iso-8859-1
        //por isso e necessario usar utf8_decode
        $objXml = new DomDocument('1.0','iso-8859-1');

				$objXml->loadXML($strConteudo);

        if ($strTipoVisualizacao == self::$TV_HTML) {

          $strNovaLinha = '<br />' . "\n";
          $strItemInicio = '<b>';
          $strItemFim = '</b>';
          $strSubitemInicio = '<i>';
          $strSubitemFim = '</i>';
          $strEspaco = '&nbsp;';

        } else {

          $strNovaLinha = "\n";
          $strItemInicio = '';
          $strItemFim = '';
          $strSubitemInicio = '';
          $strSubitemFim = '';
          $strEspaco = ' ';
        }

        if ($objXml->documentElement->nodeName == 'documento') {

          $arrAtributos = $objXml->getElementsByTagName('atributo');

          $strResultado = '';

          if ($objInfraSessao != null) {
            $bolAcaoDownload = $objInfraSessao->verificarPermissao('documento_download_anexo');
          }

          foreach($arrAtributos as $atributo){

            $arrValores = $atributo->getElementsByTagName('valores');

            if ($arrValores->length==0){
              //não mostra item que não possua valor
              if (!InfraString::isBolVazia($atributo->nodeValue) && $atributo->getAttribute('ocultar')!='S'){
                $strResultado .= $strNovaLinha.$strItemInicio.self::formatarTagConteudo($strTipoVisualizacao,$atributo->getAttribute('titulo')).$strItemFim.': '.$strNovaLinha.$strEspaco.$strEspaco.self::formatarTagConteudo($strTipoVisualizacao,$atributo->nodeValue);
                $strResultado .= $strNovaLinha;
              }
            }else{

              if ($atributo->getAttribute('titulo')!=''){
                $strResultado .= $strNovaLinha.$strItemInicio.self::formatarTagConteudo($strTipoVisualizacao,$atributo->getAttribute('titulo')).$strItemFim.':';
              }

              foreach($arrValores as $valores){

                if ($valores->getAttribute('titulo')!=''){
                  $strResultado .= $strNovaLinha.$strEspaco.$strEspaco.$strSubitemInicio.self::formatarTagConteudo($strTipoVisualizacao,$valores->getAttribute('titulo')).':'.$strSubitemFim;
                }

                $arrValor = $valores->getElementsByTagName('valor');

                foreach($arrValor as $valor){

                  if ($valor->getAttribute('ocultar')!='S') {

                    $strResultado .= $strNovaLinha . $strEspaco . $strEspaco . $strEspaco . $strEspaco;

                    if ($valor->getAttribute('titulo') != '') {
                      $strResultado .= self::formatarTagConteudo($strTipoVisualizacao, $valor->getAttribute('titulo')) . ': ';
                    }

                    if ($valor->getAttribute('tipo') == 'ANEXO') {
                      if ($objInfraSessao == null || $strLinkDownload == null) {
                        $strResultado .= self::formatarTagConteudo($strTipoVisualizacao, $valor->nodeValue);
                      } else {
                        if ($bolAcaoDownload) {
                          $objAnexoDTO = new AnexoDTO();
                          $objAnexoDTO->setNumIdAnexo($valor->getAttribute('id'));
                          $objAnexoRN = new AnexoRN();
                          if ($objAnexoRN->contarRN0734($objAnexoDTO) > 0) {
                            $strResultado .= '<a href="' . $objInfraSessao->assinarLink($strLinkDownload . '&id_anexo=' . $valor->getAttribute('id')) . '" target="_blank" class="ancoraVisualizacaoDocumento">' . self::formatarTagConteudo($strTipoVisualizacao, $valor->nodeValue) . '</a>';
                          } else {
                            $strResultado .= '<a href="javascript:void(0);" onclick="alert(\'Este anexo foi excluído.\');"  class="ancoraVisualizacaoDocumento">' . self::formatarTagConteudo($strTipoVisualizacao, $valor->nodeValue) . '</a>';
                          }
                        } else {
                          $strResultado .= self::formatarTagConteudo($strTipoVisualizacao, $valor->nodeValue);
                        }
                      }
                    } else {
                      $strResultado .= self::formatarTagConteudo($strTipoVisualizacao, $valor->nodeValue);
                    }
                  }
                }

                if ($arrValor->length>1){
                  $strResultado .= $strNovaLinha;
                }
              }
              $strResultado .= $strNovaLinha;
            }
          }

        }else if ($objXml->documentElement->nodeName == 'formulario') {

          $arrAtributos = $objXml->getElementsByTagName('atributo');

          $strResultado = '';

          foreach($arrAtributos as $atributo){

            $strStaTipo = $atributo->getAttribute('tipo');

            $strRotulo = utf8_decode($atributo->getElementsByTagName('rotulo')->item(0)->nodeValue);

            if ($strStaTipo==AtributoRN::$TA_INFORMACAO){

              $strResultado .= $strNovaLinha.self::formatarRotulo($strTipoVisualizacao, $strRotulo, false);

            }else {

              $strResultado .= $strNovaLinha . $strItemInicio . self::formatarRotulo($strTipoVisualizacao, $strRotulo) . $strItemFim;

              $strResultado .= $strNovaLinha.$strEspaco.$strEspaco;

              if ($strStaTipo == AtributoRN::$TA_LISTA || $strStaTipo == AtributoRN::$TA_OPCOES) {

                $valor = $atributo->getElementsByTagName('dominio');
                if ($valor->length == 1) {
                  $strResultado .= self::formatarTagConteudo($strTipoVisualizacao, $valor->item(0)->nodeValue);
                } else {
                  $strResultado .= '-';
                }

              } else if ($strStaTipo == AtributoRN::$TA_SINALIZADOR) {

                $valor = $atributo->getElementsByTagName('valor');
                if ($valor->length == 1) {
                  if ($valor->item(0)->nodeValue == 'S') {
                    $strResultado .= 'Sim';
                  } else if ($valor->item(0)->nodeValue == 'N') {
                    $strResultado .= 'Não';
                  } else {
                    $strResultado .= '-';
                  }
                }

              } else {

                $valor = $atributo->getElementsByTagName('valor');
                if ($valor->length == 1) {
                  $strResultado .= self::formatarTagConteudo($strTipoVisualizacao, $valor->item(0)->nodeValue);
                } else {
                  $strResultado .= '-';
                }

              }
            }

            $strResultado .= $strNovaLinha;
          }

        }
			}
		}
		return $strResultado;
	}

	public static function obterAtributoConteudo($strConteudo, $strNomeAtributo){

		if (!InfraString::isBolVazia($strConteudo) && substr($strConteudo,0,5) == '<?xml'){

			$objXml = new DomDocument('1.0','iso-8859-1');

			$objXml->loadXML($strConteudo);

			$arrAtributos = $objXml->getElementsByTagName('atributo');
			foreach($arrAtributos as $atributo){
				if ($atributo->getAttribute('nome') == $strNomeAtributo){
					return self::formatarTagConteudo(self::$TV_TEXTO,$atributo->nodeValue);
				}
			}
		}

		return null;
	}

	public static function montarTitulo($objDocumentoDTO){
	  return SessaoSEI::getInstance()->getStrSiglaSistema().'/'.SessaoSEI::getInstance()->getStrSiglaOrgaoSistema().' - '.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().' - '.$objDocumentoDTO->getStrNomeSerie();
	}
	
	public static function limparHtml($strHtml){


    $substituicoes = array (
        '@<head[^>]*?>.*?</head>@si'                                            => '',       // Strip out javascript
        '@<div class="Micron"[^>]*?>.*?</div>@si'                               => '',       // espaçamento de seção
        '@<div id="divVersao"[^>]*?>.*?</div>@si'                               => '',       // rodape de versão
        '@<span[^<>]*class="rangySelectionBoundary"[^<>]*>[^<]*</span>@i'       => '',       // sujeira do scayt
        '@(<span[^<>]*)(data-scayt-word="[^<>]*")([^<>]*>)([^<]*)</span>@i'     => '$4',     // sujeira do scayt
        '@<[\/\!]*?[^<>]*?>@si'                                                 => '',       // Strip out HTML tags
      //'@([\r\n])[\s]+@'                                                     => '',       // Strip out white space
        '@&(quot|#34);@i'                                                       => '"',      // Replace HTML entities
        '@&(amp|#38);@i'                                                        => '&',      // Ampersand &
        '@&(lt|#60);@i'                                                         => '<',      // Less Than <
        '@&(gt|#62);@i'                                                         => '>',      // Greater Than >
      //'@&(ordf|#170);@i'                                                    => 'ª',
      //'@&(ordm|#186);@i'                                                    => 'º',
      //'@&(sect|#167);@i'                                                    => '§',
        '@&(nbsp|#160);@i'                                                      => ' ',      // Non Breaking Space
        '@&(iexcl|#161);@i'                                                     => chr(161), // Inverted Exclamation point
        '@&(cent|#162);@i'                                                      => chr(162), // Cent
        '@&(pound|#163);@i'                                                     => chr(163), // Pound
        '@&(copy|#169);@i'                                                      => chr(169), // Copyright
        '@&(reg|#174);@i'                                                       => chr(174), // Registered
        //'@&#(d+);@e'                                                            => 'chr()',  // Evaluate as php
        '@<b[^>]*?>.*?</b\s*>@si'                                               => '',       // negrito
        '@<i[^>]*?>.*?</i\s*>@si'                                               => '',       // italico
        '@<br[^>]*?>@si'                                                        => ' '       // espaço
    );
//    $strHtml=preg_replace_callback('@&#(d+);@','self::limparCaracteresHtml',$strHtml);
    return InfraString::removerAcentosHTML(preg_replace(array_keys($substituicoes), array_values($substituicoes), $strHtml));
  }

  private static function limparCaracteresHtml($matches)
  {
    return chr(intval($matches[1]));
  }

  public static function formatarRotulo($strTipoVisualizacao, $strRotulo, $bolFinalizar = true ){
    if ($strRotulo!='') {

      if ($bolFinalizar && !in_array(substr(trim($strRotulo), -1),array('.',':','?','!'))){
				$strRotulo .= ':';
			}

      if ($strTipoVisualizacao == self::$TV_HTML) {

        $strRotulo = PaginaSEI::tratarHTML($strRotulo);

        $tamRotulo = strlen($strRotulo);
        $numEspacos = 0;
        for ($i = 0; $i < $tamRotulo; $i++) {
          if ($strRotulo{$i} == ' ') {
            $numEspacos++;
          } else {
            break;
          }
        }

        $strRotulo = str_repeat('&nbsp;', $numEspacos).trim(nl2br($strRotulo));
      }
    }
    return $strRotulo;
  }
}
?>