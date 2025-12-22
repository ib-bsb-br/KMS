<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 14/11/2014 - criado por mga
*
*/

try {
  require_once dirname(__FILE__).'/SEI.php';

  session_start();

  
	//PaginaSEI::getInstance()->setBolAutoRedimensionar(false);
  //////////////////////////////////////////////////////////////////////////////
  
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(false);
  InfraDebug::getInstance()->limpar();
  
  //////////////////////////////////////////////////////////////////////////////
  
  SessaoSEI::getInstance()->validarLink();

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $arrComandos = array();  
  $arrComandos[] = '<button type="submit" accesskey="P" id="sbmPesquisar" name="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

	$objUnidadeHojeDTORet = null;

  switch($_GET['acao']){

  	case 'unidade_hoje_gerar':

			$strTitulo = SessaoSEI::getInstance()->getStrSiglaUnidadeAtual().' Hoje';

      if (isset($_POST['sbmPesquisar'])) {
        try {
          $objUnidadeHojeRN = new UnidadeHojeRN();
          $objUnidadeHojeDTORet = $objUnidadeHojeRN->gerar();
        } catch (Exception $e) {
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  
  if ($objUnidadeHojeDTORet != null) {

		$arrProcessosTipoQtde = $objUnidadeHojeDTORet->getArrProcessosTipoQtde();
		$arrProcessosUsuarioQtde = $objUnidadeHojeDTORet->getArrProcessosUsuarioQtde();
		$arrDocumentosAssinaturas = $objUnidadeHojeDTORet->getArrDocumentosAssinaturas();
		$arrBlocosAssinatura = $objUnidadeHojeDTORet->getArrBlocosAssinatura();
		$arrRetornosProgramados = $objUnidadeHojeDTORet->getArrRetornosProgramados();
		$arrRetornosUltimasAcoes = $objUnidadeHojeDTORet->getArrRetornosProgramados();

		$bolAcaoImprimir = true;

		if ($bolAcaoImprimir) {
			$bolCheck = true;
			$arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirDiv(\'divTabelas\');" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Tipo Processo X Quantidade
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		$strCssTr = '';
		$totalTipoProcesso = 0;
		$strTabProcessosTipoQtde = '';

		foreach ($arrProcessosTipoQtde as $arr) {

			$quantidade = $arr[0];
			$numIdTipoProcedimento = $arr[1];
			$strNomeTipoProcedimento = $arr[2];

			$strLink = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_hoje_detalhar&id_unidade_hoje='.$objUnidadeHojeDTORet->getDblIdUnidadeHojeProcessosTipoQtde().'&tipo_unidade_hoje='.UnidadeHojeRN::$TIPO_UNIDADE_HOJE_PROCESSOS.'&id_tipo_procedimento='.$numIdTipoProcedimento);
			$strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
			$strTabProcessosTipoQtde .= $strCssTr;
			$strTabProcessosTipoQtde .= '<td align="left">'.PaginaSEI::tratarHTML($strNomeTipoProcedimento).'</td>';
			$strTabProcessosTipoQtde .= '<td align="center"><a href="javascript:void(0);" onclick="abrirDetalhe(\'' . $strLink . '\');" class="ancoraPadraoAzul">' . InfraUtil::formatarMilhares($quantidade) . '</a></td>';
			$strTabProcessosTipoQtde .= '</tr>' . "\n";
			$totalTipoProcesso += $quantidade;
		}

		$strTabProcessosTipoQtde = '<table width="70%" class="infraTable" summary="Tabela de ' . UnidadeHojeRN::$TITULO_UNIDADE_HOJE_PROCESSOS . '">' . "\n"
		                           .'<caption class="infraCaption">' . UnidadeHojeRN::$TITULO_UNIDADE_HOJE_PROCESSOS . ':</caption>'
		                           .'<tr>'
		                           .'<th class="infraTh" width="80%">Tipo do Processo</th>' . "\n"
		                           .'<th class="infraTh" width="">Quantidade</th>' . "\n"
		                           .'</tr>' . "\n"
		                           .$strTabProcessosTipoQtde
		                           .'<tr class="totalUnidadeHoje"><td align="right"><b>TOTAL:</b></td><td align="center"><a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_hoje_detalhar&id_unidade_hoje=' . $objUnidadeHojeDTORet->getDblIdUnidadeHojeProcessosTipoQtde() . '&tipo_unidade_hoje=' . UnidadeHojeRN::$TIPO_UNIDADE_HOJE_PROCESSOS) . '\');" class="ancoraPadraoAzul">' . ($totalTipoProcesso?InfraUtil::formatarMilhares($totalTipoProcesso):'&nbsp;') . '</a></td></tr>'
		                           .'</table>';


		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Documentos da unidade assinados x não assinados

		$strCssTr = '';
		$totalGeralNaoAssinados = 0;
		$totalGeralAssinados = 0;
		$strTabDocumentosAssinatura = '';

		foreach ($arrDocumentosAssinaturas as $arr) {

			$numNaoAssinados = $arr[0];
			$numAssinados = $arr[1];
			$numIdSerie = $arr[2];
			$strNomeSerie = $arr[3];

			$strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
			$strTabDocumentosAssinatura .= $strCssTr;
			$strTabDocumentosAssinatura .= '<td align="left">'.PaginaSEI::tratarHTML($strNomeSerie).'</td>';

			$strTabDocumentosAssinatura .= '<td align="center">';
			if ($numNaoAssinados) {
				$strTabDocumentosAssinatura .= '<a href="javascript:void(0);" onclick="abrirDetalhe(\'' .SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_hoje_detalhar&id_unidade_hoje=' . $objUnidadeHojeDTORet->getDblIdUnidadeHojeDocsUnidadeNaoAssinados() . '&tipo_unidade_hoje=' . UnidadeHojeRN::$TIPO_UNIDADE_HOJE_DOCUMENTOS_UNIDADE_NAO_ASSINADOS . '&id_serie=' . $numIdSerie). '\');" class="ancoraPadraoAzul">' . InfraUtil::formatarMilhares($numNaoAssinados) . '</a>';
				$totalGeralNaoAssinados += $numNaoAssinados;
			}else{
				$strTabDocumentosAssinatura .= '&nbsp;';
			}
			$strTabDocumentosAssinatura .= '</td>';

			$strTabDocumentosAssinatura .= '<td align="center">';
			if ($numAssinados){
				$strTabDocumentosAssinatura .= '<a href="javascript:void(0);" onclick="abrirDetalhe(\'' .SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_hoje_detalhar&id_unidade_hoje=' . $objUnidadeHojeDTORet->getDblIdUnidadeHojeDocsUnidadeAssinados() . '&tipo_unidade_hoje=' . UnidadeHojeRN::$TIPO_UNIDADE_HOJE_DOCUMENTOS_UNIDADE_ASSINADOS . '&id_serie=' . $numIdSerie). '\');" class="ancoraPadraoAzul">' . InfraUtil::formatarMilhares($numAssinados) . '</a>';
				$totalGeralAssinados += $numAssinados;
			}else{
				$strTabDocumentosAssinatura .= '&nbsp;';
			}
			$strTabDocumentosAssinatura .= '</td>';
			$strTabDocumentosAssinatura .= '</tr>' . "\n";
		}

		$strTabDocumentosAssinatura = '<table width="70%" class="infraTable" summary="Tabela de ' . UnidadeHojeRN::$TITULO_UNIDADE_HOJE_DOCUMENTOS_ASSINATURA . '">' . "\n"
				.'<caption class="infraCaption">' . UnidadeHojeRN::$TITULO_UNIDADE_HOJE_DOCUMENTOS_ASSINATURA . ':</caption>'
				.'<tr>'
				.'<th class="infraTh">Tipo do Documento</th>' . "\n"
				.'<th class="infraTh" width="15%">Não Assinados</th>' . "\n"
				.'<th class="infraTh" width="15%">Assinados</th>' . "\n"
				.'</tr>' . "\n"
				.$strTabDocumentosAssinatura
				.'<tr class="totalUnidadeHoje"><td align="right"><b>TOTAL:</b></td>'
		    .'<td align="center"><a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_hoje_detalhar&id_unidade_hoje=' . $objUnidadeHojeDTORet->getDblIdUnidadeHojeDocsUnidadeNaoAssinados() . '&tipo_unidade_hoje=' . UnidadeHojeRN::$TIPO_UNIDADE_HOJE_DOCUMENTOS_UNIDADE_NAO_ASSINADOS) . '\');" class="ancoraPadraoAzul">' . ($totalGeralNaoAssinados?InfraUtil::formatarMilhares($totalGeralNaoAssinados):'&nbsp;') . '</a></td>'
				.'<td align="center"><a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_hoje_detalhar&id_unidade_hoje=' . $objUnidadeHojeDTORet->getDblIdUnidadeHojeDocsUnidadeAssinados() . '&tipo_unidade_hoje=' . UnidadeHojeRN::$TIPO_UNIDADE_HOJE_DOCUMENTOS_UNIDADE_ASSINADOS) . '\');" class="ancoraPadraoAzul">' . ($totalGeralAssinados?InfraUtil::formatarMilhares($totalGeralAssinados):'&nbsp;') . '</a></td>'
				.'</tr>'
				.'</table>';


		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Usuário X Quantidade
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		$strCssTr = '';
		$totalUsuarioAtribuicao = 0;
		$strTabProcessosUsuarioQtde = '';

		foreach ($arrProcessosUsuarioQtde as $arr) {

			$quantidade = $arr[0];
			$numIdUsuarioAtribuicao = $arr[1];
			$strSiglaUsuarioAtribuicao = $arr[2];
			$strNomeUsuarioAtribuicao = $arr[3];

			$strLink = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_hoje_detalhar&id_unidade_hoje='.$objUnidadeHojeDTORet->getDblIdUnidadeHojeProcessosUsuarioQtde().'&tipo_unidade_hoje='.UnidadeHojeRN::$TIPO_UNIDADE_HOJE_USUARIO_ATRIBUICAO.'&id_usuario_atribuicao='.$numIdUsuarioAtribuicao);

			$strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
			$strTabProcessosUsuarioQtde .= $strCssTr;
			$strTabProcessosUsuarioQtde .= '<td align="left"><a alt="'.PaginaSEI::tratarHTML($strNomeUsuarioAtribuicao).'" title="'.PaginaSEI::tratarHTML($strNomeUsuarioAtribuicao).'" class="ancoraSigla">'.PaginaSEI::tratarHTML($strSiglaUsuarioAtribuicao).'</a></td>';
			$strTabProcessosUsuarioQtde .= '<td align="center"><a href="javascript:void(0);" onclick="abrirDetalhe(\'' . $strLink . '\');" class="ancoraPadraoAzul">' . InfraUtil::formatarMilhares($quantidade) . '</a></td>';
			$strTabProcessosUsuarioQtde .= '</tr>' . "\n";
			$totalUsuarioAtribuicao += $quantidade;
		}

		$strTabProcessosUsuarioQtde = '<table width="70%" class="infraTable" summary="Tabela de ' . UnidadeHojeRN::$TITULO_UNIDADE_HOJE_USUARIO_ATRIBUICAO . '">' . "\n"
				.'<caption class="infraCaption">' . UnidadeHojeRN::$TITULO_UNIDADE_HOJE_USUARIO_ATRIBUICAO . ':</caption>'
				.'<tr>'
				.'<th class="infraTh" width="80%">Usuário</th>' . "\n"
				.'<th class="infraTh" width="">Quantidade</th>' . "\n"
				.'</tr>' . "\n"
				.$strTabProcessosUsuarioQtde
				.'<tr class="totalUnidadeHoje"><td align="right"><b>TOTAL:</b></td><td align="center"><a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_hoje_detalhar&id_unidade_hoje=' . $objUnidadeHojeDTORet->getDblIdUnidadeHojeProcessosUsuarioQtde() . '&tipo_unidade_hoje=' . UnidadeHojeRN::$TIPO_UNIDADE_HOJE_USUARIO_ATRIBUICAO) . '\');" class="ancoraPadraoAzul">' . ($totalUsuarioAtribuicao?InfraUtil::formatarMilhares($totalUsuarioAtribuicao):'&nbsp;') . '</a></td></tr>'
				.'</table>';


		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Documentos disponibilizados em bloco para a unidade assinados x não assinados

		$strCssTr = '';
		$totalBlocosNaoAssinados = 0;
		$totalBlocosAssinados = 0;
		$strTabBlocosAssinatura = '';

		foreach ($arrBlocosAssinatura as $arr) {

			$numNaoAssinados = $arr[0];
			$numAssinados = $arr[1];
			$numIdBloco = $arr[2];
			$strSiglaUnidade = $arr[3];
			$strDescricaoUnidade = $arr[4];

			$strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
			$strTabBlocosAssinatura .= $strCssTr;
			$strTabBlocosAssinatura .= '<td align="center">'.$numIdBloco.'</a></td>';
			$strTabBlocosAssinatura .= '<td align="center"><a alt="'.PaginaSEI::tratarHTML($strDescricaoUnidade).'" title="'.PaginaSEI::tratarHTML($strDescricaoUnidade).'" class="ancoraSigla">'.PaginaSEI::tratarHTML($strSiglaUnidade).'</a></td>';

			$strTabBlocosAssinatura .= '<td align="center">';
			if ($numNaoAssinados) {
				$strTabBlocosAssinatura .= '<a href="javascript:void(0);" onclick="abrirDetalhe(\'' .SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_hoje_detalhar&id_unidade_hoje=' . $objUnidadeHojeDTORet->getDblIdUnidadeHojeDocsBlocoNaoAssinados() . '&tipo_unidade_hoje=' . UnidadeHojeRN::$TIPO_UNIDADE_HOJE_DOCUMENTOS_BLOCO_NAO_ASSINADOS . '&id_bloco=' . $numIdBloco). '\');" class="ancoraPadraoAzul">' . InfraUtil::formatarMilhares($numNaoAssinados) . '</a>';
				$totalBlocosNaoAssinados += $numNaoAssinados;
			}else{
				$strTabBlocosAssinatura .= '&nbsp;';
			}
			$strTabBlocosAssinatura .= '</td>';

			$strTabBlocosAssinatura .= '<td align="center">';
			if ($numAssinados){
				$strTabBlocosAssinatura .= '<a href="javascript:void(0);" onclick="abrirDetalhe(\'' .SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_hoje_detalhar&id_unidade_hoje=' . $objUnidadeHojeDTORet->getDblIdUnidadeHojeDocsBlocoAssinados() . '&tipo_unidade_hoje=' . UnidadeHojeRN::$TIPO_UNIDADE_HOJE_DOCUMENTOS_BLOCO_ASSINADOS . '&id_bloco=' . $numIdBloco). '\');" class="ancoraPadraoAzul">' . InfraUtil::formatarMilhares($numAssinados) . '</a>';
				$totalBlocosAssinados += $numAssinados;
			}else{
				$strTabBlocosAssinatura .= '&nbsp;';
			}
			$strTabBlocosAssinatura .= '</td>';
			$strTabBlocosAssinatura .= '</tr>' . "\n";
		}

		$strTabBlocosAssinatura = '<table width="70%" class="infraTable" summary="Tabela de ' . UnidadeHojeRN::$TITULO_UNIDADE_HOJE_DOCUMENTOS_BLOCO . '">' . "\n"
				.'<caption class="infraCaption">' . UnidadeHojeRN::$TITULO_UNIDADE_HOJE_DOCUMENTOS_BLOCO . ':</caption>'
				.'<tr>'
				.'<th class="infraTh">Bloco</th>' . "\n"
				.'<th class="infraTh">Unidade</th>' . "\n"
				.'<th class="infraTh" width="15%">Não Assinados</th>' . "\n"
				.'<th class="infraTh" width="15%">Assinados</th>' . "\n"
				.'</tr>' . "\n"
				.$strTabBlocosAssinatura
				.'<tr class="totalUnidadeHoje"><td colspan="2" align="right"><b>TOTAL:</b></td>'
				.'<td align="center"><a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_hoje_detalhar&id_unidade_hoje=' . $objUnidadeHojeDTORet->getDblIdUnidadeHojeDocsBlocoNaoAssinados() . '&tipo_unidade_hoje=' . UnidadeHojeRN::$TIPO_UNIDADE_HOJE_DOCUMENTOS_BLOCO_NAO_ASSINADOS) . '\');" class="ancoraPadraoAzul">' . ($totalBlocosNaoAssinados?InfraUtil::formatarMilhares($totalBlocosNaoAssinados):'&nbsp;') . '</a></td>'
				.'<td align="center"><a href="javascript:void(0);" onclick="abrirDetalhe(\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_hoje_detalhar&id_unidade_hoje=' . $objUnidadeHojeDTORet->getDblIdUnidadeHojeDocsBlocoAssinados() . '&tipo_unidade_hoje=' . UnidadeHojeRN::$TIPO_UNIDADE_HOJE_DOCUMENTOS_BLOCO_ASSINADOS) . '\');" class="ancoraPadraoAzul">' . ($totalBlocosAssinados?InfraUtil::formatarMilhares($totalBlocosAssinados):'nbsp;') . '</a></td>'
				.'</tr>'
				.'</table>';

	}

}catch(Exception $e){
  PaginaSEI::getInstance()->processarExcecao($e);
} 

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema().' - '.$strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
?>
	tr.totalUnidadeHoje {
	background-color:#ffffdd;
	}

	td.totalUnidadeHoje {
	background-color:#ffffdd;
	}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){
  infraAdicionarEvento(window,'resize',seiRedimensionarGraficos);
  infraProcessarResize();
  infraAviso();
  infraEfeitoTabelas();
  seiRedimensionarGraficos();
}

function abrirDetalhe(link){
 infraAbrirJanela(link,'janelaUnidadeHoje',750,550,'location=0,status=1,resizable=1,scrollbars=1');
}

function validarFormulario(){
	infraExibirAviso();
	return true;
}

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmUnidadeHoje" onsubmit="return validarFormulario();" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?
  //PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  ?>
  <div id="divTabelas">
  <div id="divSeparador" style="float:left;padding:1em"></div>
  <?

  if (count($arrProcessosTipoQtde)) {
  	echo '<br /><br />';
		PaginaSEI::getInstance()->montarAreaTabela($strTabProcessosTipoQtde, count($arrProcessosTipoQtde));
  }

  if (count($arrDocumentosAssinaturas)) {
  	echo '<br /><br />';
		PaginaSEI::getInstance()->montarAreaTabela($strTabDocumentosAssinatura,count($arrDocumentosAssinaturas));
  }

	if (count($arrProcessosUsuarioQtde)) {
		echo '<br /><br />';
		PaginaSEI::getInstance()->montarAreaTabela($strTabProcessosUsuarioQtde, count($arrProcessosUsuarioQtde));
	}

  if (count($arrBlocosAssinatura)) {
		echo '<br /><br />';
		PaginaSEI::getInstance()->montarAreaTabela($strTabBlocosAssinatura,count($arrBlocosAssinatura));
	}

  ?>
  </div>
  <?
  PaginaSEI::getInstance()->montarAreaDebug();
  //PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);

  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>