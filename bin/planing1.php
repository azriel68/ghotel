<?
	require("../includes/inc.php");

	entete("Gestion planing");

	is_logged();

	menu();

	if(isset($_REQUEST['action'])){
		$action=$_REQUEST['action'];
	}
	else{
		$action='VIEW';
	}

	$sess_name=isset($_REQUEST['sess_name'])?$_REQUEST['sess_name']:gen_sess_name("planing");
	// MKO 07.03.2007
	$month=isset($_REQUEST['month'])?$_REQUEST['month']:1;

	$db=new Tdb;


	switch($action){
		case 'VIEW';
			unset_session('planing');
			$_SESSION[$sess_name]=new TPlaning(get_sess_hotel_id());
			$p = & $_SESSION[$sess_name];

			$p->init(isset($_REQUEST['dt_lundi'])?$_REQUEST['dt_lundi']:"");
			$p->liste_jour();
			$p->liste_chambre($db);
			affiche($p,$sess_name);

			break;
		case 'NEXT';
			$p = & $_SESSION[$sess_name];
			$p->semaine_suivante($month);
			$p->liste_jour();
			$p->liste_chambre($db);
			affiche($p,$sess_name);

			break;
		case 'PREV';
			$p = & $_SESSION[$sess_name];
			$p->semaine_precedente($month);
			$p->liste_jour();
			$p->liste_chambre($db);
			affiche($p,$sess_name);

			break;
		default:
			erreur("inconnu : ".$action);
	} // switch


	$db->close();

	pied_de_page();


	function affiche (&$p,$sess_name) {
		$form = new TForm();
		
?>
<script language="javascript" src="../scripts/planing.js"></script>
<script language="javascript" src="../scripts/XHRConnection.js"></script>

<script language="javascript" type="text/javascript">
  function refresh() {
  	document.location.href="<?=$_SERVER['PHP_SELF']?>?dt_lundi=<?=date("d/m/Y",$p->dt_lundi)?>";
  }
  function refresh_line( id_chambre){
  	pl_reload_line( id_chambre, '<?=$p->get_dtlundi()?>')
  }
  function go_to_lundi(){
  	var dt_lundi = document.getElementById('dt_lundi').value;
  	document.location.href = '<?=$_SERVER['PHP_SELF']?>?dt_lundi='+dt_lundi;
  }
</script>
<div id="hidden_div" style="visibility:hidden; position:absolute;"></div>
<div id="mouse_scroll_div" style="visibility:hidden; position:absolute;top:0px;left:0px;z-index:9;width:200px;" class="Cell_moving" READONLY></div>
<div class="info" align="center" id="info_message" style="visibility:hidden; position:absolute;z-index:0;"></div>
<div id="div_planning" style="display:block">
<?
		$t = new Ttbl();

		$t->beg_tbl('formcadre',800,2,'','center');
		$t->beg_line("listheader");
		$t->Cell("SEMAINE DU ".$form->texte('','dt_lundi',date('d/m/Y',$p->dt_lundi),12,10).$t->link('go','javascript:go_to_lundi()','lien')
		,-1,'',8,'center');
		$t->end_line();

		$t->beg_line();
		$t->Cell("CHAMBRES",-1,'Cell_header','','center');
		for($i = 0; $i < $p->nb_jour; $i++) {
			$jour = & $p->TJour[$i];
			$t->Cell($jour->nom,-1,($p->dt_current==$jour->time)?'Cell_header':'','','center');
		}
		$t->end_line();

		for($i = 0; $i < $p->nb_chambre; $i++){

			$chambre = &$p->TChambre[$i];
			$t->beg_line('','chambre_'.$chambre->id);
			//$t->Cell($chambre->num,-1,'Cell_header','','center');
//			$url=DIR_SCRIPTS."planing_line.php?id_chambre=".$chambre->id."&date=".$p->get_dtlundi();
//	 	print $url;
//			print file_get_contents($url);
			_affiche_dispo($p, $chambre,$p->get_dtlundi());


			$t->end_line();

		} // for
		$t->end_tbl();
?>
</div>
<?

// MKO 07.03.2007 boutons de navigation
		echo	"<p align='center'>";
		echo	$t->link($t->img("trileft.gif").$t->img("trileft.gif"),"javascript:pl_go_previous_month('".$sess_name."')",'button','','','Mois précédent');
		echo	$t->link($t->img("trileft.gif"),"javascript:pl_go_previous('".$sess_name."')",'button','','','Semaine précédente');
		echo	$t->link("Aujourd'hui","javascript:pl_go_today('".$sess_name."')",'button','','','Aujourd\'hui');
		echo	$t->link($t->img("tri.gif"),"javascript:pl_go_next('".$sess_name."')",'button','','','Semaine suivante');
		echo	$t->link($t->img("tri.gif").$t->img("tri.gif"),"javascript:pl_go_next_month('".$sess_name."')",'button','','','Mois suivant');
		echo	"</p>";
		
		$form=new TForm("planing.php", "planingForm");
		echo $form->hidden("pl_set_move", "0");
		echo $form->hidden("pl_id_resa_move", "");
		echo $form->hidden("pl_id_chambre", "");
		echo $form->hidden("pl_date", "");
		echo $form->zonetexte("","pl_body_div", (isset($_REQUEST['pl_body_div'])?$_REQUEST['pl_body_div']:""),1,1,' style="visibility:hidden;position:absolute;" ');
		echo $form->hidden("action", "");
		echo $form->hidden("month", "");
		echo $form->hidden("sess_name", $sess_name);
		echo $form->end_form();
		//print $_REQUEST['pl_set_move'];
		if(isset($_REQUEST['pl_set_move']) && (double)$_REQUEST['pl_set_move']==1){
			
			?>
			<script language="javascript">
			
			pl_set_move_to('pl_body_div', <?=$_REQUEST['pl_id_chambre']?>, '<?=$p->get_dtlundi()?>', <?=$_REQUEST['pl_id_resa_move']?>);
			</script>
			<?
			
		}
		
	}

?>
