<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4Њ REGIУO
 * 
 * 12/03/2013 - criado por MGA
 *
 * @package infra_php
 */


/*
CREATE TABLE infra_agendamento_tarefa
(
   id_infra_agendamento_tarefa int PRIMARY KEY NOT NULL,
   descricao varchar(500) NOT NULL,
   comando varchar(255) NOT NULL,
   sta_periodicidade_execucao char(1) NOT NULL,
   periodicidade_complemento varchar(100) NOT NULL,
   dth_ultima_execucao datetime,
   dth_ultima_conclusao datetime,
   sin_sucesso char(1) NOT NULL,
   parametro varchar(250),
   email_erro varchar(250),
   sin_ativo char(1) NOT NULL
);

CREATE UNIQUE INDEX PRIMARY ON infra_agendamento_tarefa(id_infra_agendamento_tarefa);
*/


abstract class InfraAgendamentoTarefa  {
  
	//public abstract static function getInstance();
	
	public function __construct(InfraConfiguracao $objInfraConfiguracao, InfraSessao $objInfraSessao, InfraIBanco $objInfraIBanco, InfraLog $objInfraLog){
	  ConfiguracaoInfra::setObjInfraConfiguracao($objInfraConfiguracao);
    SessaoInfra::setObjInfraSessao($objInfraSessao);
    BancoInfra::setObjInfraIBanco($objInfraIBanco);
    LogInfra::setObjInfraLog($objInfraLog);
	}
	
  public function executar($strEmailErroRemetente = null, $strEmailErroDestinatario = null) {
    
    try {
    
      //////////////////////////////////////////////////////////////////////////////
      //InfraDebug::getInstance()->setBolLigado(false);
      //InfraDebug::getInstance()->setBolDebugInfra(true);
      //InfraDebug::getInstance()->limpar();
      //////////////////////////////////////////////////////////////////////////////
      
      // busca lista de tarefas ativas
      $objInfraAgendamentoTarefaDTO = new InfraAgendamentoTarefaDTO();
      $objInfraAgendamentoTarefaDTO->retTodos();
      
      $objInfraAgendamentoTarefaDTO->setStrSinAtivo('S');
    
      $objInfraAgendamentoTarefaRN = new InfraAgendamentoTarefaRN();
      $arrObjInfraAgendamentoTarefaDTO = $objInfraAgendamentoTarefaRN->listar($objInfraAgendamentoTarefaDTO);
      
      $arrDataHoraAtual = array('hora' => date('G'),
      													'diaSemana' => date('N'),
      													'diaMes' => date('j'),
      													'Mes' => date('n'));
        
      foreach($arrObjInfraAgendamentoTarefaDTO as $objInfraAgendamentoTarefaDTO){
        
      	// verifica condiчуo de execuчуo
      	$bolExecutar = true;
      	switch($objInfraAgendamentoTarefaDTO->getStrStaPeriodicidadeExecucao()){
      		case InfraAgendamentoTarefaRN::$PERIODICIDADEEXECUCAO_DIA:
      			$arrHoraExecucao = explode(',', $objInfraAgendamentoTarefaDTO->getStrPeriodicidadeComplemento());
      			// se a hora nуo estiver no periodicidade complemento nуo executa a tarefa 
      			if(!in_array($arrDataHoraAtual['hora'], $arrHoraExecucao)){
      				$bolExecutar = false;
      			}
      			break;
      			
     			case InfraAgendamentoTarefaRN::$PERIODICIDADEEXECUCAO_SEMANA:
      			$arrDiaHoraExecucao = explode(',', $objInfraAgendamentoTarefaDTO->getStrPeriodicidadeComplemento());
      			// se a hora nуo estiver no periodicidade complemento nуo executa a tarefa 
      			if(!in_array($arrDataHoraAtual['diaSemana'].'/'.$arrDataHoraAtual['hora'], $arrDiaHoraExecucao)){
      				$bolExecutar = false;
      			}
     				break;
     				
     			case InfraAgendamentoTarefaRN::$PERIODICIDADEEXECUCAO_MES:
      			$arrDiaHoraExecucao = explode(',', $objInfraAgendamentoTarefaDTO->getStrPeriodicidadeComplemento());
      			// se a hora nуo estiver no periodicidade complemento nуo executa a tarefa 
      			if(!in_array($arrDataHoraAtual['diaMes'].'/'.$arrDataHoraAtual['hora'], $arrDiaHoraExecucao)){
      				$bolExecutar = false;
      			}
     				break;
     				
     			case InfraAgendamentoTarefaRN::$PERIODICIDADEEXECUCAO_ANO:
      			$arrDiaMesHoraExecucao = explode(',', $objInfraAgendamentoTarefaDTO->getStrPeriodicidadeComplemento());
      			// se a hora nуo estiver no periodicidade complemento nуo executa a tarefa 
      			if(!in_array($arrDataHoraAtual['diaMes'].'/'.$arrDataHoraAtual['Mes'].'/'.$arrDataHoraAtual['hora'], $arrDiaMesHoraExecucao)){
      				$bolExecutar = false;
      			}
     				break;
     				
    			default:
    				$bolExecutar = false;
    				break;
      	}
      	
      	if($bolExecutar){
      	  
      	  try{
      	    $objInfraAgendamentoTarefaRN->executar($objInfraAgendamentoTarefaDTO);
      	  }catch(Exception $e){
    
      	    $strAssunto = 'Agendamento FALHOU';
      	    
      	    $strErro = '';
      	    $strErro .= 'Servidor: '.gethostname()."\n\n";
      	    $strErro .= 'Data/Hora: '.InfraData::getStrDataHoraAtual()."\n\n";
      	    $strErro .= 'Comando: '.$objInfraAgendamentoTarefaDTO->getStrComando().'('.$objInfraAgendamentoTarefaDTO->getStrParametro().')'."\n\n";
      	    $strErro .= 'Erro: '.InfraException::inspecionar($e);
      	    
            LogInfra::getInstance()->gravar($strAssunto."\n\n".$strErro); 
      	          	
            if(!is_null($strEmailErroRemetente)){
              
              if (!is_null($objInfraAgendamentoTarefaDTO->getStrEmailErro())){
                InfraMail::enviarConfigurado(ConfiguracaoInfra::getInstance(), $strEmailErroRemetente, $objInfraAgendamentoTarefaDTO->getStrEmailErro(), null, null, $strAssunto, $strErro);
              }else if (!is_null($strEmailErroDestinatario)){
                InfraMail::enviarConfigurado(ConfiguracaoInfra::getInstance(), $strEmailErroRemetente, $strEmailErroDestinatario, null, null, $strAssunto, $strErro);
              }
            }
      	  }
      	}
      } 
    }catch(Exception $e){
      
      $strAssunto = 'Erro executando agendamentos.';
      $strErro = InfraException::inspecionar($e);
      
      LogInfra::getInstance()->gravar($strAssunto."\n\n".$strErro); 
      
      if (!is_null($strEmailErroRemetente) && !is_null($strEmailErroDestinatario)){
        InfraMail::enviarConfigurado(ConfiguracaoInfra::getInstance(), $strEmailErroRemetente, $strEmailErroDestinatario, null, null, $strAssunto, $strErro);
      }
    }
  }
}
?>