<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4Є REGIГO
*
* 14/02/2013 - criado por mga
*
*
* Versгo do Gerador de Cуdigo:1.6.1
*/
try {
  require_once dirname(__FILE__).'/SEI.php';
  
  session_start(); 
  
  //////////////////////////////////////////////////////////////////////////////
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(false);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////
      
  SessaoSEI::getInstance()->validarLink(); 
  
  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $strNomeArquivo = '';
  
  switch($_GET['acao']){ 
  	  	
    case 'pesquisa_solr_ajuda':
      $strConteudo = file_get_contents('ajuda/ajuda_solr.html');
      break;

    case 'assinatura_digital_ajuda':
      $strConteudo = file_get_contents('ajuda/assinatura_digital_ajuda.html');
      $strConteudo = str_replace('[servidor]', ConfiguracaoSEI::getInstance()->getValor('SEI','URL'), $strConteudo);
      break;
      
    default:
      throw new InfraException("Aзгo '".$_GET['acao']."' nгo reconhecida.");
  }

  InfraPagina::montarHeaderDownload(null, null, 'Content-Type: text/html; charset=iso-8859-1');
  echo $strConteudo;
  
}catch(Exception $e){
  die('Erro realizando download do anexo:'.$e->__toString());
}
?>