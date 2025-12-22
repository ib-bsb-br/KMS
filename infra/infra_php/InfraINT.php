<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 * 
 * 01/06/2006 - criado por MGA
 *
 * @package infra_php
 */

abstract class InfraINT {

  private static function montarItensIniciais($strPrimeiroItemValor, $strPrimeiroItemDescricao, $varValorItemSelecionado){
    $strRet = '';
    
		if ($strPrimeiroItemValor!==null && $strPrimeiroItemDescricao!==null){
			
			//Se for TODOS adiciona um item vazio antes
			if ($strPrimeiroItemValor===''){
				$strRet .= '<option value="null" ';
				if ($varValorItemSelecionado===null){
				  $strRet .= 'selected="selected"';
				}
				$strRet .= '>&nbsp;</option>'."\n";
			}
			
			$strRet .= '<option value="'.$strPrimeiroItemValor.'"';
			
			if ($varValorItemSelecionado===null){ //se $varValorItemSelecionado é null, o primeiro item é necessariamente o selecionado							
				$strRet .= ' selected="selected"';
			} else { //senão, verificar se é um dos selecionados
				foreach($varValorItemSelecionado as $strValorItemSelecionado){
					if ($strValorItemSelecionado===$strPrimeiroItemValor){
						$strRet .= ' selected="selected"';
						break;
					}
				}
			}

			//if (trim($strPrimeiroItemDescricao)==''){
			//  $strPrimeiroItemDescricao = '&nbsp;';
			//}
			$strRet .= '>'.$strPrimeiroItemDescricao.'</option>'."\n";
		}
    
		return $strRet;
		
  }

	public static function montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $varValorItemSelecionado, $arrObjInfraDTO, $varAtributoChave, $strAtributoDescricao){
		$strRet = '';

		$varValorItemSelecionado = (!is_array($varValorItemSelecionado) && $varValorItemSelecionado!==null)? array($varValorItemSelecionado) : $varValorItemSelecionado; //se não for array e não for null: transforma em array

		if (count($arrObjInfraDTO)>0){
		  
		  $strRet .= InfraINT::montarItensIniciais($strPrimeiroItemValor, $strPrimeiroItemDescricao, $varValorItemSelecionado);
	
			foreach($arrObjInfraDTO as $dto){
			  $strAtributoChave = '';   
			  if (!is_array($varAtributoChave)){
			    $strAtributoChave = $dto->get($varAtributoChave);
			  }else{
			    foreach ($varAtributoChave as $strChave){
			      if ($strAtributoChave!=''){
			        $strAtributoChave .= '#';
			      }
			      $strAtributoChave .= $dto->get($strChave);
			    }
			  }
			  
				$strRet .= '<option value="'.$strAtributoChave.'"';
				if ($varValorItemSelecionado!==null){
				  //no eproc numeros grandes quando comparados davam problema sem o cast forçado
					foreach($varValorItemSelecionado as $strValorItemSelecionado){
						if ('#'.$strValorItemSelecionado.'#' == '#'.$strAtributoChave.'#'){
							$strRet .= ' selected="selected"';
							break;
						}
					}
				}
				$strRet .= '>'.InfraString::formatarXML($dto->get($strAtributoDescricao)).'</option>'."\n";
			}
		}
    return $strRet;  
	}

	public static function montarSelectArray($strPrimeiroItemValor, $strPrimeiroItemDescricao, $varValorItemSelecionado, $arr){
	  
		$varValorItemSelecionado = (!is_array($varValorItemSelecionado) && $varValorItemSelecionado!==null)? array($varValorItemSelecionado) : $varValorItemSelecionado; //se não for array e não for null: transforma em array
		$strRet = InfraINT::montarItensIniciais($strPrimeiroItemValor, $strPrimeiroItemDescricao, $varValorItemSelecionado);
			
		foreach($arr as $valor=>$descricao){
			$bolSelecionado = false;
			if($varValorItemSelecionado !== null){
				foreach($varValorItemSelecionado as $strValorItemSelecionado){ //verifica se o item é um dos selecionados
					if ($strValorItemSelecionado!==null && '#'.$strValorItemSelecionado.'#'=='#'.$valor.'#'){
						$bolSelecionado = true;
						break;
					}
				}				
			}

		  $strRet .= InfraINT::montarItemSelect($valor,$descricao,$bolSelecionado);
		}
    return $strRet;
	}
	
	public static function  montarItemSelect($strValor, $strDescricao, $bolSelecionado){
	  $strRet = '<option value="'.$strValor.'"';
	  if ($bolSelecionado){
	    $strRet .= ' selected="selected"';
	  }
	  $strRet .= '>'.InfraString::formatarXML($strDescricao).'</option>'."\n";
	  return $strRet;
	}
	
	public static function montarItemCheckbox($strValor, $strDescricao, $bolSelecionado){
		$checked = ($bolSelecionado ? ' checked="checked"' : '');
		//$strItem = '<item name="selPrazo[]" value="' . $strValor . '" ' . $checked . '>' . $strDescricao . '</item>';
		$strItem = '<item name="selPrazo[]" value="' . $strValor . '" ' . $checked . '>' . htmlspecialchars($strDescricao, ENT_COMPAT , 'ISO-8859-1') . '</item>';
		return $strItem;
	}
	
  public static function montarCheckboxArray($arr, $strValorItensSelecionados){
    $strRet = '';
    if (is_array($arr)) {
     foreach($arr as $valor=>$descricao){
        $strRet .= InfraINT::montarItemCheckbox($valor,$descricao,InfraUtil::inArray($valor, $strValorItensSelecionados));
     }
    }
    return $strRet;
  }	
} 
?>