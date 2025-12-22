
function seiExibirOcultarGrafico(id) {
	if (document.getElementById('div' + id).style.display != "block") {

		document.getElementById('div' + id).style.display = "block";
		document.getElementById('btnVer' + id).style.display = "none";
		document.getElementById('btnOcultar' + id).style.display = "block";

	} else {
		document.getElementById('div' + id).style.display = "none";
		document.getElementById('btnVer' + id).style.display = "block";
		document.getElementById('btnOcultar' + id).style.display = "none";
	}
  seiRedimensionarGraficos();
}

function seiRedimensionarGraficos(){
  var i=1;
  var divTela = document.getElementById('divInfraAreaTelaD');
  if (divTela!=null){
		var w = divTela.offsetWidth - 10;
		if (w < 0) w = 0;
		while (true) {
			var grf=document.getElementById('divGrf'+i);
			if (grf==null) break;
			grf.style.width = w + 'px';
			i++;
		}

		var arrDiv = document.getElementsByTagName('div');
		for (i = 0; i < arrDiv.length; i++) {
			if (arrDiv[i].className == 'divAreaGrafico') {
			arrDiv[i].style.width = w + 'px';
			}
		}
  }
}

if ( INFRA_FF>0 ) { // Correção para problema do Enter no confirm
  (function(window){
    var _confirm = window.confirm;
    window.confirm = function(msg){
      var keyupCanceler = function(ev){
        ev.stopPropagation();
        return false;
      };
      document.addEventListener("keyup", keyupCanceler, true);
      var retVal = _confirm(msg);
      setTimeout(function(){
        document.removeEventListener("keyup", keyupCanceler, true);
      }, 150); // Giving enough time to fire event
      return retVal;
    };
  })(window);
}

function seiAlterarContato(idContato, idObject, idFrm, link) {
  var frm = document.getElementById(idFrm);

  document.getElementById('hdnContatoObject').value = idObject;
  document.getElementById('hdnContatoIdentificador').value = idContato;

  var actionAnterior = frm.action;

  infraAbrirJanela('', 'janelaAlterarContato', 700, 600, 'location=0,status=1,resizable=1,scrollbars=1');

  frm.target = 'janelaAlterarContato';
  frm.action = link;
  frm.submit();

  frm.target = '_self';
  frm.action = actionAnterior;
}

function seiConsultarAssunto(idAssunto, idObject, idFrm, link) {

  if (infraTrim(idAssunto)!='') {

    var frm = document.getElementById(idFrm);

    document.getElementById('hdnAssuntoIdentificador').value = idAssunto;

    var actionAnterior = frm.action;

    infraAbrirJanela('', 'janelaConsultarAssunto', 700, 600, 'location=0,status=1,resizable=1,scrollbars=1');

    frm.target = 'janelaConsultarAssunto';
    frm.action = link;
    frm.submit();

    frm.target = '_self';
    frm.action = actionAnterior;

  }
}

function seiFiltrarTabela(event){
  var tbl= $(event.data).find('tbody');
  var filtro=$(this).val();

  if (filtro.length>0){
    $('.infraTrSelecionada:hidden').removeClass('infraTrSelecionada');
    filtro=infraRetirarAcentos(filtro).toLowerCase();
    tbl.find('tr').each(function(){
      var ancora=$(this).find('.ancoraOpcao');
      var descricao=$(this).attr('data-desc');
      var i=descricao.indexOf(filtro);
      if(i==-1)
        $(this).hide();
      else {
        $(this).show();
        $(this).val();
        var text=ancora.text();
        var html='';
        var ini=0;
        while (i!=-1) {
          html+=text.substring(ini,i);
          html+='<span class="infraSpanRealce">';
          html+=text.substr(i,filtro.length);
          html+='</span>';
          ini=i+filtro.length;
          i=descricao.indexOf(filtro,ini);
        }
        html+=text.substr(ini);
        ancora.html(html);
      }
    });
  } else {
    tbl.find('tr').show();
    tbl.find('.ancoraOpcao').each(function(){$(this).html($(this).text());});
  }
}

function seiPrepararFiltroTabela(objTabela,objInput){
  $(objInput).on('keyup',objTabela,seiFiltrarTabela);
  $(objInput).focus();
  var tbody=$(objTabela).find('tbody');
  tbody.find('tr').each(function(){
    $(this).removeAttr('onmouseover').removeAttr('onmouseout');
  });
  tbody.on('mouseenter','tr',function(e){
    $('.infraTrSelecionada').removeClass('infraTrSelecionada');
    $(e.currentTarget).addClass('infraTrSelecionada').find('.ancoraOpcao').focus();
  });
  $(document).on('keydown',function(e){
    if(e.which!=40 && e.which!=38) return;
    var sel=$('.infraTrSelecionada');
    if(sel.length==0) {
      sel=tbody.find('tr:visible:first').addClass('infraTrSelecionada');
    } else if(e.which==40) {
      if (sel.nextAll('tr:visible').length != 0) {
        sel.removeClass('infraTrSelecionada');
        sel=sel.nextAll('tr:visible:first').addClass('infraTrSelecionada');
      }
    } else {
      if (sel.prevAll('tr:visible').length != 0) {
        sel.removeClass('infraTrSelecionada');
        sel=sel.prevAll('tr:visible:first').addClass('infraTrSelecionada');
      }
    }
    sel.find('.ancoraOpcao').focus();
    e.preventDefault();
  })
}