<?
	require("../includes/inc.php");

	entete("Gestion planing",'online');

	is_logged();

	//menu();

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

  $r=new TRequete;
  $TCat = $r->liste_toute_categorie($db, get_sess_hotel_id());

	switch($action){
		case 'VIEW';
			unset_session('planing');
			$_SESSION[$sess_name]=new TPlaning(get_sess_hotel_id());
			$p = & $_SESSION[$sess_name];

			$p->init(isset($_REQUEST['dt_lundi'])?$_REQUEST['dt_lundi']:"");
			
			$h = & $_SESSION[SESS_HOTEL];
			$nb_semaine=((double)$h->get_parameter('nb_semaine_planning')!=0)?$h->get_parameter('nb_semaine_planning'):1;
			$p->liste_jour($nb_semaine);
			$p->liste_chambre($db);
			affiche($p,$sess_name, $TCat);

			break;
		case 'SEARCH':
		// recherche
			$p = & $_SESSION[$sess_name];
			$p->semaine_suivante($month);
			$p->liste_jour();
			
			$TParam['categorie']=$_REQUEST['categorie'];
			$TParam['dt_deb']=$_REQUEST['dt_deb'];
			$TParam['dt_fin']=$_REQUEST['dt_fin'];
			
			$p->liste_chambre($db, $TParam);
			affiche($p,$sess_name ,$TCat);
		
		  break;
		case 'NEXT';
			$p = & $_SESSION[$sess_name];
			$p->semaine_suivante($month);
			$p->liste_jour();
			$p->liste_chambre($db);
			affiche($p,$sess_name, $TCat);

			break;
		case 'PREV';
			$p = & $_SESSION[$sess_name];
			$p->semaine_precedente($month);
			$p->liste_jour();
			$p->liste_chambre($db);
			affiche($p,$sess_name, $TCat);

			break;
		default:
			erreur("inconnu : ".$action);
	} // switch


	$db->close();

	pied_de_page();


	function affiche (&$p,$sess_name,&$TCat) {
		$form = new TForm();
		
		$width_ligne = $p->nb_semaine * 707 + 101;
		$width_planning = $width_ligne;
		
?>
	<link href="../styles/pl.css" rel="stylesheet" type="text/css">

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
<div id="div_planning" style="display:block;"">
<?
		$t = new Ttbl();
    _bt_nav($sess_name);
    ?>
    <div align="center">
    
    <div style="padding:2px;border: 1px #444 solid;background-color: #ccc;width:<?=($width_planning+4)?>px;">
    <div id="planning_div" style="width:<?=$width_planning?>px;">
    <div class="ligne_h" align="left" style="padding-left:10px;">
    <?="Semaine du ".$form->texte('','dt_lundi',date('d/m/Y',$p->dt_lundi),12,10).$t->link('ok','javascript:go_to_lundi()','lien2')
    /*."- Recherche : ".$form->combo('','categorie', $TCat, 'auto' )." du "
    .$form->texte("","dt_deb","auto",12,10)." au "
    .$form->texte("","dt_fin","auto",12,10)." "
    .$t->link('ok','javascript:launch_search()','lien2')*/
    ?>
    
    </div>
    
    <div class="ligne_h2">
    <div class="cell_h">CHAMBRES</div>
    <?
		for($i = 0; $i < $p->nb_jour; $i++) {
			$jour = & $p->TJour[$i];
			?>
			<div class="<?=($p->dt_current==$jour->time)?'Cell_h':'cell_h2'?>">
			<?
        print $jour->nom;
      ?>
      </div>
      <?
		}
		?>
		</div>
		<?
    
    for($i = 0; $i < $p->nb_chambre; $i++){

			$chambre = &$p->TChambre[$i];
			?>
			<div id="chambre_<?=$chambre->id?>" class="ligne">
			<?
      //$t->Cell($chambre->num,-1,'Cell_header','','center');
//			$url=DIR_SCRIPTS."planing_line.php?id_chambre=".$chambre->id."&date=".$p->get_dtlundi();
//	 	print $url;
//			print file_get_contents($url);
			_affiche_dispo2($p, $chambre,$p->get_dtlundi());

      ?>
      </div>
      <?
			
		} // for
		
?>
      </div>
      </div>
      
      </div>
<?

// MKO 07.03.2007 boutons de navigation
    _bt_nav($sess_name);
		
		
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
  function _bt_nav($sess_name){
  
    $t = new Ttbl();
    
    echo	"<p align='center'>";
    /*
		echo	$t->link($t->img("trileft.gif").$t->img("trileft.gif"),"javascript:pl_go_previous_month('".$sess_name."')",'button','','','Mois précédent');
		echo	$t->link($t->img("trileft.gif"),"javascript:pl_go_previous('".$sess_name."')",'button','','','Semaine précédente');
		echo	$t->link("Aujourd'hui","javascript:pl_go_today('".$sess_name."')",'button','','','Aujourd\'hui');
		echo	$t->link($t->img("tri.gif"),"javascript:pl_go_next('".$sess_name."')",'button','','','Semaine suivante');
		echo	$t->link($t->img("tri.gif").$t->img("tri.gif"),"javascript:pl_go_next_month('".$sess_name."')",'button','','','Mois suivant');*/
		
		echo $t->link( $t->img("bt_precedentbis.jpg", "Mois précédent") ,"javascript:pl_go_previous_month('".$sess_name."')");
		echo $t->link( $t->img("bt_precedent.jpg", "Semaine précédente") ,"javascript:pl_go_previous('".$sess_name."')");

		echo $t->link( $t->img("bt_aujourdhui.jpg", "Aujourd'hui") ,"javascript:pl_go_today('".$sess_name."')");

		echo $t->link( $t->img("bt_suivant.jpg", "Semaine suivante") ,"javascript:pl_go_next('".$sess_name."')");
		echo $t->link( $t->img("bt_suivantbis.jpg", "Mois suivant") ,"javascript:pl_go_next_month('".$sess_name."')");
		
		echo	"</p>";
		
	}
  	

?>
