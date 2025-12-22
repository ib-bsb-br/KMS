<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 15/12/2011 - criado por tamir_db
*
* Versão do Gerador de Código: 1.32.1
*
* Versão no CVS: $Id$
*/

try {
  
  //require_once 'Infra.php';
  
  session_start();

  //////////////////////////////////////////////////////////////////////////////
  //InfraDebug::getInstance()->setBolLigado(false);
  //InfraDebug::getInstance()->setBolDebugInfra(true);
  //InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoInfra::getInstance()->validarLink();

  PaginaInfra::getInstance()->prepararSelecao('infra_agendamento_tarefa_selecionar');

  SessaoInfra::getInstance()->validarPermissao($_GET['acao']);

  PaginaInfra::getInstance()->salvarCamposPost(array('selStaPeriodicidadeExecucao'));
    
  switch($_GET['acao']){
    case 'infra_agendamento_tarefa_executar':
      try {
        $objInfraAgendamentoTarefaDTO = new InfraAgendamentoTarefaDTO();
        $objInfraAgendamentoTarefaDTO->setNumIdInfraAgendamentoTarefa($_GET['id_infra_agendamento_tarefa']);

        $objInfraAgendamentoTarefaRN = new InfraAgendamentoTarefaRN();
        $objInfraAgendamentoTarefaRN->executar($objInfraAgendamentoTarefaDTO);
        PaginaInfra::getInstance()->setStrMensagem('Execução concluída com sucesso.');
      } catch (Exception $e) {
        PaginaInfra::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoInfra::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao'].PaginaInfra::getInstance()->montarAncora($objInfraAgendamentoTarefaDTO->getNumIdInfraAgendamentoTarefa())));
      die;
  		break;
  		
    case 'infra_agendamento_tarefa_excluir':
      try{
        $arrStrIds = PaginaInfra::getInstance()->getArrStrItensSelecionados();
        $arrObjInfraAgendamentoTarefaDTO = array();
        for ($i=0, $iMax = count($arrStrIds); $i<$iMax; $i++){
          $objInfraAgendamentoTarefaDTO = new InfraAgendamentoTarefaDTO();
          $objInfraAgendamentoTarefaDTO->setNumIdInfraAgendamentoTarefa($arrStrIds[$i]);
          $arrObjInfraAgendamentoTarefaDTO[] = $objInfraAgendamentoTarefaDTO;
        }
        $objInfraAgendamentoTarefaRN = new InfraAgendamentoTarefaRN();
        $objInfraAgendamentoTarefaRN->excluir($arrObjInfraAgendamentoTarefaDTO);
        PaginaInfra::getInstance()->adicionarMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaInfra::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoInfra::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;


    case 'infra_agendamento_tarefa_desativar':
      try{
        $arrStrIds = PaginaInfra::getInstance()->getArrStrItensSelecionados();
        $arrObjInfraAgendamentoTarefaDTO = array();
        for ($i=0, $iMax = count($arrStrIds); $i<$iMax; $i++){
          $objInfraAgendamentoTarefaDTO = new InfraAgendamentoTarefaDTO();
          $objInfraAgendamentoTarefaDTO->setNumIdInfraAgendamentoTarefa($arrStrIds[$i]);
          $arrObjInfraAgendamentoTarefaDTO[] = $objInfraAgendamentoTarefaDTO;
        }
        $objInfraAgendamentoTarefaRN = new InfraAgendamentoTarefaRN();
        $objInfraAgendamentoTarefaRN->desativar($arrObjInfraAgendamentoTarefaDTO);
        PaginaInfra::getInstance()->adicionarMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaInfra::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoInfra::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'infra_agendamento_tarefa_reativar':
      $strTitulo = 'Reativar Agendamentos de Tarefas';
      
      if ($_GET['acao_confirmada']==='sim'){
        try{
          $arrStrIds = PaginaInfra::getInstance()->getArrStrItensSelecionados();
          $arrObjInfraAgendamentoTarefaDTO = array();
          for ($i=0, $iMax = count($arrStrIds); $i<$iMax; $i++){
            $objInfraAgendamentoTarefaDTO = new InfraAgendamentoTarefaDTO();
            $objInfraAgendamentoTarefaDTO->setNumIdInfraAgendamentoTarefa($arrStrIds[$i]);
            $arrObjInfraAgendamentoTarefaDTO[] = $objInfraAgendamentoTarefaDTO;
          }
          $objInfraAgendamentoTarefaRN = new InfraAgendamentoTarefaRN();
          $objInfraAgendamentoTarefaRN->reativar($arrObjInfraAgendamentoTarefaDTO);
          PaginaInfra::getInstance()->adicionarMensagem('Operação realizada com sucesso.');
        }catch(Exception $e){
          PaginaInfra::getInstance()->processarExcecao($e);
        } 
        header('Location: '.SessaoInfra::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
        die;
      } 
      break;


    case 'infra_agendamento_tarefa_selecionar':
      $strTitulo = PaginaInfra::getInstance()->getTituloSelecao('Selecionar Agendamento de Tarefa','Selecionar Agendamentos de Tarefas');

      //Se cadastrou alguem
      if ($_GET['acao_origem']==='infra_agendamento_tarefa_cadastrar'){
        if (isset($_GET['id_infra_agendamento_tarefa'])){
          PaginaInfra::getInstance()->adicionarSelecionado($_GET['id_infra_agendamento_tarefa']);
        }
      }
      break;

    case 'infra_agendamento_tarefa_listar':
      $strTitulo = 'Agendamentos de Tarefas';
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $arrComandos = array();
  if ($_GET['acao'] === 'infra_agendamento_tarefa_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
  }

  if ($_GET['acao'] === 'infra_agendamento_tarefa_listar' || $_GET['acao'] === 'infra_agendamento_tarefa_selecionar'){
    $bolAcaoCadastrar = SessaoInfra::getInstance()->verificarPermissao('infra_agendamento_tarefa_cadastrar');
    if ($bolAcaoCadastrar){
      $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\''.SessaoInfra::getInstance()->assinarLink('controlador.php?acao=infra_agendamento_tarefa_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
    }
  }

  $objInfraAgendamentoTarefaDTO = new InfraAgendamentoTarefaDTO();
  $objInfraAgendamentoTarefaDTO->retNumIdInfraAgendamentoTarefa();
  //$objInfraAgendamentoTarefaDTO->retStrDescricao();
  $objInfraAgendamentoTarefaDTO->retStrComando();
  $objInfraAgendamentoTarefaDTO->retStrStaPeriodicidadeExecucao();
  $objInfraAgendamentoTarefaDTO->retStrPeriodicidadeComplemento();
  $objInfraAgendamentoTarefaDTO->retStrParametro();
  $objInfraAgendamentoTarefaDTO->retStrSinAtivo();
  $objInfraAgendamentoTarefaDTO->retDthUltimaExecucao();
  $objInfraAgendamentoTarefaDTO->retStrSinSucesso();
  $strStaPeriodicidadeExecucao = PaginaInfra::getInstance()->recuperarCampo('selStaPeriodicidadeExecucao');

  $objInfraAgendamentoTarefaDTO->setBolExclusaoLogica(false);
  
  /*
  if ($_GET['acao'] == 'infra_agendamento_tarefa_reativar'){
    //Lista somente inativos
    $objInfraAgendamentoTarefaDTO->setBolExclusaoLogica(false);
    $objInfraAgendamentoTarefaDTO->setStrSinAtivo('N');
  }
  */

  PaginaInfra::getInstance()->prepararOrdenacao($objInfraAgendamentoTarefaDTO, 'Comando', InfraDTO::$TIPO_ORDENACAO_ASC);
  //PaginaInfra::getInstance()->prepararPaginacao($objInfraAgendamentoTarefaDTO);

  $objInfraAgendamentoTarefaRN = new InfraAgendamentoTarefaRN();
  $arrObjInfraAgendamentoTarefaDTO = $objInfraAgendamentoTarefaRN->listar($objInfraAgendamentoTarefaDTO);
  
 
  //PaginaInfra::getInstance()->processarPaginacao($objInfraAgendamentoTarefaDTO);
  $numRegistros = count($arrObjInfraAgendamentoTarefaDTO);

  if ($numRegistros > 0){
  	
  	$arrObjInfraAgendamentoPeriodicidadeDTO = $objInfraAgendamentoTarefaRN->listarValoresPeriodicidadeExecucao();
  	$arrObjInfraAgendamentoPeriodicidadeDTO = InfraArray::converterArrInfraDTO($arrObjInfraAgendamentoPeriodicidadeDTO, 'Descricao', 'StaPeriodicidadeExecucao');
  	
    $bolCheck = false;

    if ($_GET['acao']==='infra_agendamento_tarefa_selecionar'){
      $bolAcaoReativar = false;
      $bolAcaoConsultar = SessaoInfra::getInstance()->verificarPermissao('infra_agendamento_tarefa_consultar');
      $bolAcaoAlterar = SessaoInfra::getInstance()->verificarPermissao('infra_agendamento_tarefa_alterar');
      $bolAcaoImprimir = false;
      //$bolAcaoGerarPlanilha = false;
      $bolAcaoExcluir = false;
      $bolAcaoDesativar = false;
      $bolCheck = true;
      $bolAcaoExecutar = false;
    /*  
    }else if ($_GET['acao']=='infra_agendamento_tarefa_reativar'){
      $bolAcaoReativar = SessaoInfra::getInstance()->verificarPermissao('infra_agendamento_tarefa_reativar');
      $bolAcaoConsultar = SessaoInfra::getInstance()->verificarPermissao('infra_agendamento_tarefa_consultar');
      $bolAcaoAlterar = false;
      $bolAcaoImprimir = true;
      //$bolAcaoGerarPlanilha = SessaoInfra::getInstance()->verificarPermissao('infra_gerar_planilha_tabela');
      $bolAcaoExcluir = SessaoInfra::getInstance()->verificarPermissao('infra_agendamento_tarefa_excluir');
      $bolAcaoDesativar = false;
      $bolAcaoExecutar = SessaoInfra::getInstance()->verificarPermissao('infra_agendamento_tarefa_executar');
    */  
    }else{
      $bolAcaoReativar = SessaoInfra::getInstance()->verificarPermissao('infra_agendamento_tarefa_reativar');
      $bolAcaoConsultar = SessaoInfra::getInstance()->verificarPermissao('infra_agendamento_tarefa_consultar');
      $bolAcaoAlterar = SessaoInfra::getInstance()->verificarPermissao('infra_agendamento_tarefa_alterar');
      $bolAcaoImprimir = true;
      //$bolAcaoGerarPlanilha = SessaoInfra::getInstance()->verificarPermissao('infra_gerar_planilha_tabela');
      $bolAcaoExcluir = SessaoInfra::getInstance()->verificarPermissao('infra_agendamento_tarefa_excluir');
      $bolAcaoDesativar = SessaoInfra::getInstance()->verificarPermissao('infra_agendamento_tarefa_desativar');
      $bolAcaoExecutar = SessaoInfra::getInstance()->verificarPermissao('infra_agendamento_tarefa_executar');
    }

    
    if ($bolAcaoDesativar){
      //$bolCheck = true;
      //$arrComandos[] = '<button type="button" accesskey="t" id="btnDesativar" value="Desativar" onclick="acaoDesativacaoMultipla();" class="infraButton">Desa<span class="infraTeclaAtalho">t</span>ivar</button>';
      $strLinkDesativar = SessaoInfra::getInstance()->assinarLink('controlador.php?acao=infra_agendamento_tarefa_desativar&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoReativar){
      //$bolCheck = true;
      //$arrComandos[] = '<button type="button" accesskey="R" id="btnReativar" value="Reativar" onclick="acaoReativacaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">R</span>eativar</button>';
      $strLinkReativar = SessaoInfra::getInstance()->assinarLink('controlador.php?acao=infra_agendamento_tarefa_reativar&acao_origem='.$_GET['acao'].'&acao_confirmada=sim');
    }
    

    if ($bolAcaoExcluir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
      $strLinkExcluir = SessaoInfra::getInstance()->assinarLink('controlador.php?acao=infra_agendamento_tarefa_excluir&acao_origem='.$_GET['acao']);
    }

    /*
    if ($bolAcaoGerarPlanilha){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="P" id="btnGerarPlanilha" value="Gerar Planilha" onclick="infraGerarPlanilhaTabela(\''.SessaoInfra::getInstance()->assinarLink('controlador.php?acao=infra_gerar_planilha_tabela')).'\');" class="infraButton">Gerar <span class="infraTeclaAtalho">P</span>lanilha</button>';
    }
    */

    $strResultado = '';

    if ($_GET['acao']!=='infra_agendamento_tarefa_reativar'){
      $strSumarioTabela = 'Tabela de Agendamentos.';
      $strCaptionTabela = 'Agendamentos';
    }else{
      $strSumarioTabela = 'Tabela de Agendamentos Inativos.';
      $strCaptionTabela = 'Agendamentos Inativos';
    }

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaInfra::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    if ($bolCheck) {
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaInfra::getInstance()->getThCheck().'</th>'."\n";
    }
    //$strResultado .= '<th class="infraTh" width="15%">'.PaginaInfra::getInstance()->getThOrdenacao($objInfraAgendamentoTarefaDTO,'Descrição','Descricao',$arrObjInfraAgendamentoTarefaDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh">'.PaginaInfra::getInstance()->getThOrdenacao($objInfraAgendamentoTarefaDTO,'Comando','Comando',$arrObjInfraAgendamentoTarefaDTO).'</th>'."\n";
    //$strResultado .= '<th class="infraTh" width="10%">'.PaginaInfra::getInstance()->getThOrdenacao($objInfraAgendamentoTarefaDTO,'Parâmetros','Parametro',$arrObjInfraAgendamentoTarefaDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="10%">'.PaginaInfra::getInstance()->getThOrdenacao($objInfraAgendamentoTarefaDTO,'Periodicidade','StaPeriodicidadeExecucao',$arrObjInfraAgendamentoTarefaDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="10%">'.PaginaInfra::getInstance()->getThOrdenacao($objInfraAgendamentoTarefaDTO,'Complemento','PeriodicidadeComplemento',$arrObjInfraAgendamentoTarefaDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="15%">'.PaginaInfra::getInstance()->getThOrdenacao($objInfraAgendamentoTarefaDTO,'Última Execução','UltimaExecucao',$arrObjInfraAgendamentoTarefaDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="10%">'.PaginaInfra::getInstance()->getThOrdenacao($objInfraAgendamentoTarefaDTO,'Status','SinSucesso',$arrObjInfraAgendamentoTarefaDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="15%">Ações</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistros; $i++){

      if ($arrObjInfraAgendamentoTarefaDTO[$i]->getStrSinAtivo()=='S'){
        $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
        $strResultado .= $strCssTr;
      }else{
        $strCssTr = '<tr class="trVermelha">';
        $strResultado .= $strCssTr;
      }

      if ($bolCheck){
        $strResultado .= '<td>'.PaginaInfra::getInstance()->getTrCheck($i,$arrObjInfraAgendamentoTarefaDTO[$i]->getNumIdInfraAgendamentoTarefa(),$arrObjInfraAgendamentoTarefaDTO[$i]->getNumIdInfraAgendamentoTarefa()).'</td>';
      }
      //$strResultado .= '<td>'.$arrObjInfraAgendamentoTarefaDTO[$i]->getStrDescricao().'</td>';
      $strResultado .= '<td>'.str_replace('::',' :: ',PaginaInfra::getInstance()->tratarHTML($arrObjInfraAgendamentoTarefaDTO[$i]->getStrComando())).'</td>';
      //$strResultado .= '<td>'.$arrObjInfraAgendamentoTarefaDTO[$i]->getStrComando().'</td>';
      //$strResultado .= '<td>'.$arrObjInfraAgendamentoTarefaDTO[$i]->getStrParametro().'</td>';
      $strResultado .= '<td align="center">'.PaginaInfra::getInstance()->tratarHTML($arrObjInfraAgendamentoPeriodicidadeDTO[$arrObjInfraAgendamentoTarefaDTO[$i]->getStrStaPeriodicidadeExecucao()]).'</td>';
      $strResultado .= '<td align="center">'.PaginaInfra::getInstance()->tratarHTML(str_replace('  ',' ',str_replace(',',', ',$arrObjInfraAgendamentoTarefaDTO[$i]->getStrPeriodicidadeComplemento()))).'</td>';
      $strResultado .= '<td align="center">'.$arrObjInfraAgendamentoTarefaDTO[$i]->getDthUltimaExecucao().'</td>';
      $strResultado .= '<td align="center">'.($arrObjInfraAgendamentoTarefaDTO[$i]->getStrSinSucesso()=='S'?'Sucesso':'Falha').'</td>';
      $strResultado .= '<td align="center">';

      $strResultado .= PaginaInfra::getInstance()->getAcaoTransportarItem($i,$arrObjInfraAgendamentoTarefaDTO[$i]->getNumIdInfraAgendamentoTarefa());

      $strId = $arrObjInfraAgendamentoTarefaDTO[$i]->getNumIdInfraAgendamentoTarefa();
      $strDescricao = PaginaInfra::getInstance()->formatarParametrosJavaScript($arrObjInfraAgendamentoTarefaDTO[$i]->getStrComando());
      
      if ($bolAcaoExecutar){
      	$strResultado .= '<a onclick="executarAgendamento(\''.$strDescricao.'\',\''.SessaoInfra::getInstance()->assinarLink('controlador.php?acao=infra_agendamento_tarefa_executar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_infra_agendamento_tarefa='.$arrObjInfraAgendamentoTarefaDTO[$i]->getNumIdInfraAgendamentoTarefa()).'\')" tabindex="'.PaginaInfra::getInstance()->getProxTabTabela().'"><img src="'.PaginaInfra::getInstance()->getDiretorioImagensGlobal().'/marcar.gif" title="Executar Agendamento" alt="Executar Agendamento" class="infraImg" /></a>&nbsp;';
      }
            
      if ($bolAcaoConsultar){
        $strResultado .= '<a href="'.SessaoInfra::getInstance()->assinarLink('controlador.php?acao=infra_agendamento_tarefa_consultar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_infra_agendamento_tarefa='.$arrObjInfraAgendamentoTarefaDTO[$i]->getNumIdInfraAgendamentoTarefa()).'" tabindex="'.PaginaInfra::getInstance()->getProxTabTabela().'"><img src="'.PaginaInfra::getInstance()->getDiretorioImagensGlobal().'/consultar.gif" title="Consultar Agendamento" alt="Consultar Agendamento" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoAlterar){
        $strResultado .= '<a href="'.SessaoInfra::getInstance()->assinarLink('controlador.php?acao=infra_agendamento_tarefa_alterar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_infra_agendamento_tarefa='.$arrObjInfraAgendamentoTarefaDTO[$i]->getNumIdInfraAgendamentoTarefa()).'" tabindex="'.PaginaInfra::getInstance()->getProxTabTabela().'"><img src="'.PaginaInfra::getInstance()->getDiretorioImagensGlobal().'/alterar.gif" title="Alterar Agendamento" alt="Alterar Agendamento" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoDesativar && $arrObjInfraAgendamentoTarefaDTO[$i]->getStrSinAtivo()=='S'){
        $strResultado .= '<a href="'.PaginaInfra::getInstance()->montarAncora($strId).'" onclick="acaoDesativar(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaInfra::getInstance()->getProxTabTabela().'"><img src="'.PaginaInfra::getInstance()->getDiretorioImagensGlobal().'/desativar.gif" title="Desativar Agendamento" alt="Desativar Agendamento" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoReativar && $arrObjInfraAgendamentoTarefaDTO[$i]->getStrSinAtivo()=='N'){
        $strResultado .= '<a href="'.PaginaInfra::getInstance()->montarAncora($strId).'" onclick="acaoReativar(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaInfra::getInstance()->getProxTabTabela().'"><img src="'.PaginaInfra::getInstance()->getDiretorioImagensGlobal().'/reativar.gif" title="Reativar Agendamento" alt="Reativar Agendamento" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoExcluir){
        $strResultado .= '<a href="'.PaginaInfra::getInstance()->montarAncora($strId).'" onclick="acaoExcluir(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaInfra::getInstance()->getProxTabTabela().'"><img src="'.PaginaInfra::getInstance()->getDiretorioImagensGlobal().'/excluir.gif" title="Excluir Agendamento" alt="Excluir Agendamento" class="infraImg" /></a>&nbsp;';
      }

      $strResultado .= '</td></tr>'."\n";
    }
    $strResultado .= '</table>';
  }
  if ($_GET['acao'] === 'infra_agendamento_tarefa_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }else{
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\''.SessaoInfra::getInstance()->assinarLink('controlador.php?acao='.PaginaInfra::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }

  $strItensSelStaPeriodicidadeExecucao = InfraAgendamentoTarefaINT::montarSelectStaPeriodicidadeExecucao('','Todos',$strStaPeriodicidadeExecucao);
}catch(Exception $e){
  PaginaInfra::getInstance()->processarExcecao($e);
} 

PaginaInfra::getInstance()->montarDocType();
PaginaInfra::getInstance()->abrirHtml();
PaginaInfra::getInstance()->abrirHead();
PaginaInfra::getInstance()->montarMeta();
PaginaInfra::getInstance()->montarTitle(PaginaInfra::getInstance()->getStrNomeSistema().' - '.$strTitulo);
PaginaInfra::getInstance()->montarStyle();
PaginaInfra::getInstance()->abrirStyle();
?>
#lblHelp {position:absolute;left:0%;top:0%;width:95%;}

#lblStaPeriodicidadeExecucao {position:absolute;left:0%;top:0%;width:25%;}
#selStaPeriodicidadeExecucao {position:absolute;left:0%;top:40%;width:25%;}

tr.trVermelha{
background-color:#f59f9f; 
}

<?
PaginaInfra::getInstance()->fecharStyle();
PaginaInfra::getInstance()->montarJavaScript();
PaginaInfra::getInstance()->abrirJavaScript();
?>

function inicializar(){
  if ('<?=$_GET['acao']?>'=='infra_agendamento_tarefa_selecionar'){
    infraReceberSelecao();
    document.getElementById('btnFecharSelecao').focus();
  }else{
    document.getElementById('btnFechar').focus();
  }
  infraEfeitoTabelas();
}

<? if ($bolAcaoDesativar){ ?>
function acaoDesativar(id,desc){
  if (confirm("Confirma desativação do Agendamento de Tarefa \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmInfraAgendamentoTarefaLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmInfraAgendamentoTarefaLista').submit();
  }
}

function acaoDesativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhuma  selecionada.');
    return;
  }
  if (confirm("Confirma desativação dos Agendamentos de Tarefas selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmInfraAgendamentoTarefaLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmInfraAgendamentoTarefaLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoReativar){ ?>
function acaoReativar(id,desc){
  if (confirm("Confirma reativação do Agendamento de Tarefa  \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmInfraAgendamentoTarefaLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmInfraAgendamentoTarefaLista').submit();
  }
}

function acaoReativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Agendamento de Tarefa selecionado.');
    return;
  }
  if (confirm("Confirma reativação dos Agendamentos de Tarefas selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmInfraAgendamentoTarefaLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmInfraAgendamentoTarefaLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoExcluir){ ?>
function acaoExcluir(id,desc){
  if (confirm("Confirma exclusão de \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmInfraAgendamentoTarefaLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmInfraAgendamentoTarefaLista').submit();
  }
}

function acaoExclusaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhuma  selecionada.');
    return;
  }
  if (confirm("Confirma exclusão dos itens selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmInfraAgendamentoTarefaLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmInfraAgendamentoTarefaLista').submit();
  }
}
<? } ?>

function executarAgendamento(comando, link){
  if (confirm('Confirma execução do comando ' + comando + '?')){
    document.getElementById('frmInfraAgendamentoTarefaLista').action=link;
    document.getElementById('frmInfraAgendamentoTarefaLista').submit();
    infraExibirAviso(false);
  }
}

<?
PaginaInfra::getInstance()->fecharJavaScript();
PaginaInfra::getInstance()->fecharHead();
PaginaInfra::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmInfraAgendamentoTarefaLista" method="post" action="<?=SessaoInfra::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?
  PaginaInfra::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaInfra::getInstance()->montarAreaTabela($strResultado,$numRegistros);
  //PaginaInfra::getInstance()->montarAreaDebug();
  PaginaInfra::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaInfra::getInstance()->fecharBody();
PaginaInfra::getInstance()->fecharHtml();
?>