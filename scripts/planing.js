/**
 * Fonction javascript dédiée au planning
 * Alexis ALGOUD
 **/

document.pl_set_move=false;
document.pl_id_resa_move=-1;
document.pl_id_chambre=-1;
document.pl_date='';

var IE = document.all?true:false;
if (!IE) document.captureEvents(Event.MOUSEMOVE);
document.onmousemove = getMouseXY;
var tempX = 0;
var tempY = 0;


function pl_move_div(){
	//alert(tempX+","+tempY);
	if(document.pl_set_move){


		//alert(tempX+","+tempY);
		document.getElementById('mouse_scroll_div').style['top']=tempY+50;
		document.getElementById('mouse_scroll_div').style['left']=tempX+50;

		//document.getElementById('mouse_scroll_div').innerHTML = tempX+","+tempY;

		window.setTimeout('pl_move_div()',100);
	}
}
function getMouseXY(e) {
  if (IE) { // grab the x-y pos.s if browser is IE
    tempX = event.clientX + document.body.scrollLeft;
    tempY = event.clientY + document.body.scrollTop;
  } else {  // grab the x-y pos.s if browser is NS
    tempX = e.pageX;
    tempY = e.pageY;
  }
  // catch possible negative values in NS4
  if (tempX < 0){tempX = 0}
  if (tempY < 0){tempY = 0}
  // show the position values in the form named Show
  // in the text fields named MouseX and MouseY

  return true;
}

function pl_set_move_to(id_cell, id_chambre, date, id_resa){

	if(document.pl_set_move==false){
		document.pl_set_move=true;
		document.pl_id_resa_move=id_resa;
		document.pl_id_chambre=id_chambre;
		document.pl_date=date;

		if(id_cell=="pl_body_div"){
			document.getElementById('mouse_scroll_div').innerHTML = document.forms['planingForm'].elements['pl_body_div'].value;		
		}
		else{
			document.getElementById('mouse_scroll_div').innerHTML = document.getElementById(id_cell).innerHTML;
		}
		document.getElementById('mouse_scroll_div').style.visibility="visible";

		window.setTimeout('pl_move_div()',200);

		pl_info_message('Sélectionnez la plage de destination <a href="javascript:pl_cancel_move()">Annuler le déplacement</a>',true);
		pl_reload_line(id_chambre, date, id_resa);

	}
	else{
		window.alert('Vous devez d\'abord annuler ou terminer le déplacement précédent.');
	}

}
function pl_set_over(id_cell, focus_cell, class_name){
	if(focus_cell){
		if(document.getElementById(id_cell).className!='Cell_planing'){
			document.getElementById('info_'+id_cell).className='Cell_info_hover';
		}


		document.getElementById(id_cell).className=class_name;
		document.getElementById('tool_'+id_cell).style['display']='block';

	

	}
	else{
		document.getElementById(id_cell).className=class_name;
		document.getElementById('tool_'+id_cell).style['display']='none';

	}

}

function pl_new_resa(url,id_cellule, id_chambre, jour_time, date){
	if(document.pl_set_move==false){
		showPopup(url,id_cellule,'',600);
	}
	else{
		//alert('dépose resa!');

		var XHR = new XHRConnection();
		XHR.setRefreshArea('hidden_div');
		url="../dlg/action.php?action=MOVE_RESA&p1="+document.pl_id_resa_move+"&p2="+id_chambre+"&p3="+jour_time;
	//	pl_info_message(url, true);
		XHR.sendAndLoad(url, "GET");

//	alert(document.getElementById('hidden_div').innerHTML);
		if (document.getElementById('hidden_div').innerHTML == "erreur") {

			pl_info_message('Modification impossible',false,true);
		}
		else{
			pl_info_message_hide();
		}
    
    pl_reload_line(id_chambre, date);
		//window.setTimeout("pl_reload_line("+id_chambre+", '"+date+"')",100);
		
		pl_cancel_move();

	}
}

function pl_cancel_move() {
  pl_reload_line(document.pl_id_chambre, document.pl_date);
  //window.setTimeout("pl_reload_line("+document.pl_id_chambre+", '"+document.pl_date+"')",200);
	
	document.pl_set_move=false;
	document.pl_id_resa_move=-1;
	document.pl_id_chambre=-1;
	document.pl_date='';
	document.getElementById('mouse_scroll_div').style.visibility="hidden";
	document.getElementById('mouse_scroll_div').innerHTML = '';
}

function pl_set_div_select(id_div){

	document.getElementById('select_cell').className='Cell_select';
	document.getElementById('select_cell').style.visibility="visible";
	document.getElementById('select_cell').innerHTML = document.getElementById(id_div).innerHTML;

}

function pl_delete_resa(id_resa,id_chambre, date) {
    if(window.confirm('Supprimer cette réservation ?')){

		var XHR = new XHRConnection();
		XHR.setRefreshArea('hidden_div');
		XHR.sendAndLoad("../dlg/action.php?action=DELETE_RESA&p1="+id_resa, "GET");

		pl_reload_line( id_chambre, date);
    }
}
function pl_reload_line( id_chambre, date, id_resa_exclude){
    var XHR = new XHRConnection();
		XHR.setRefreshArea('chambre_'+id_chambre);
		url="../scripts/planing_line.php?id_chambre="+id_chambre+"&date="+date;
		if(id_resa_exclude!=null){
			url+="&id_resa_exclude="+id_resa_exclude;
		}
		XHR.sendAndLoad(url, "GET");
		
		document.getElementById('div_planning').style['display']='none';
		document.getElementById('div_planning').style['display']='block';
}
function pl_less_resa(id_resa,id_chambre, date){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('hidden_div');
	XHR.sendAndLoad("../dlg/action.php?action=LESS_RESA&p1="+id_resa, "GET");

	pl_reload_line( id_chambre, date);
}
function pl_more_resa(id_resa,id_chambre, date){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('hidden_div');
	XHR.sendAndLoad("../dlg/action.php?action=MORE_RESA&p1="+id_resa, "GET");

	if (document.getElementById('hidden_div').innerHTML == "erreur") {
		pl_info_message('Modification impossible',false,true);
	}
	pl_reload_line( id_chambre, date);
}
function pl_less_resa_left(id_resa,id_chambre, date){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('hidden_div');
	XHR.sendAndLoad("../dlg/action.php?action=LESS_RESA_LEFT&p1="+id_resa, "GET");
	if (document.getElementById('hidden_div').innerHTML == "erreur") {
		pl_info_message('Modification impossible',false,true);
	}
	pl_reload_line( id_chambre, date);
}
function pl_more_resa_left(id_resa,id_chambre, date){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('hidden_div');
	XHR.sendAndLoad("../dlg/action.php?action=MORE_RESA_LEFT&p1="+id_resa, "GET");

	if (document.getElementById('hidden_div').innerHTML == "erreur") {
		pl_info_message('Modification impossible',false,true);
	}
	pl_reload_line( id_chambre, date);
}
function pl_info_message_hide(){
	document.getElementById('info_message').style['visibility']='hidden';
	document.getElementById('info_message').style['position']='absolute';


}
function pl_info_message(msg, no_time, erreur){

	if (erreur) {
		document.getElementById('info_message').className = 'erreur';
	} else {
		document.getElementById('info_message').className = 'info';
	}
	document.getElementById('info_message').innerHTML = msg;

	document.getElementById('info_message').style['visibility']='visible';
	document.getElementById('info_message').style['position']='relative';

	if(no_time==true){
		null;
	}
	else{
		window.setTimeout('pl_info_message_hide()',3000);
	}

}

function _pl_url(action, month){
	if(document.pl_set_move){
		document.forms['planingForm'].elements['pl_set_move'].value=1;
		document.forms['planingForm'].elements['pl_id_resa_move'].value=document.pl_id_resa_move;
		document.forms['planingForm'].elements['pl_id_chambre'].value=document.pl_id_chambre;
		document.forms['planingForm'].elements['pl_date'].value=document.pl_date;
		document.forms['planingForm'].elements['pl_body_div'].value=document.getElementById('mouse_scroll_div').innerHTML;		
	}	
		document.forms['planingForm'].elements['month'].value=month;
		document.forms['planingForm'].elements['action'].value=action;
		
		document.forms['planingForm'].submit();
	
}

function pl_go_next(){
	_pl_url('NEXT',1);
}
function pl_go_previous(){
	_pl_url('PREV',1);

}
function pl_go_next_month(){
	_pl_url('NEXT',4);

}
function pl_go_previous_month(){
	_pl_url('PREV',4);

}
function pl_go_today(){
	_pl_url('VIEW',1);
}

