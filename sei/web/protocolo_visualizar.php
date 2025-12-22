<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4Є REGIГO
*
* 22/10/2013 - criado por mga
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
  
  switch($_GET['acao']){ 
  	  	
    case 'protocolo_visualizar':

      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->retStrStaProtocolo();
      $objProtocoloDTO->setDblIdProtocolo($_GET['id_protocolo']);
      
      $objProtocoloRN = new ProtocoloRN();
      $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

      if ($objProtocoloDTO==null){
        throw new InfraException('Protocolo nгo encontrado.');
      }
      
      if ($objProtocoloDTO->getStrStaProtocolo()==ProtocoloRN::$TP_PROCEDIMENTO){
        header('Location:'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=protocolo_visualizar&id_procedimento='.$_GET['id_protocolo']));
        die;
      }else{
        header('Location:'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_visualizar&acao_origem=arvore_visualizar&id_documento='.$_GET['id_protocolo']));
        die;
      }
      
      break;
     
    default:
      throw new InfraException("Aзгo '".$_GET['acao']."' nгo reconhecida.");
  }
  
}catch(Exception $e){
  PaginaSEI::getInstance()->processarExcecao($e);
}
?>