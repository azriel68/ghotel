<?

require("fonction_droit.php");

/**
 * Alexis ALGOUD
 * 07/10/2006 14:43:48
 * Collection de fonctions
 **/

function getHotelName () {
	return $_SESSION[SESS_HOTEL]->nom;
}
function getHotelAdresse () {
	return $_SESSION[SESS_HOTEL]->adresse;
}
function getHotelVille () {
	return $_SESSION[SESS_HOTEL]->cp." ".$_SESSION[SESS_HOTEL]->ville;
}
function getHotelTel () {
	return $_SESSION[SESS_HOTEL]->telephone;
}
function getHotelFax () {
	return $_SESSION[SESS_HOTEL]->fax;
}
function get_sess_user_group_id(){
	return $_SESSION[SESS_USER]->id_groupe;
}
function test_group_id($id){
  if( get_sess_user_group_id()==$id || isset($_REQUEST['no_hack_detection'])){
    return true;
  }
  else{
    erreur_hacking();
  }
}
function test_hotel_id($id){
  if( get_sess_hotel_id()==$id  || isset($_REQUEST['no_hack_detection'])){
    return true;
  }
  else{
    erreur_hacking();
  }
}
function erreur_hacking(){

    erreur("Tentative d'intrusion détectée");
    deconnexion();
    exit();
}
function get_sess_user_id(){
	return $_SESSION[SESS_USER]->id;
}
function get_sess_hotel_id(){
	return $_SESSION[SESS_HOTEL]->id;
}
function get_sess_hotel_tva () {
	return $_SESSION[SESS_HOTEL]->tva;
}
function getUserName () {
	return $_SESSION[SESS_USER]->prenom." ".$_SESSION[SESS_USER]->nom;
}

function get_hotel_access(){

  if( is_admin() || $_SESSION[SESS_USER]->is_Lien(get_sess_hotel_id()) ){
    return true;
  }
  else{
    return false;
  }

}
function _get_langue(){
//français, belges, néerlandais,
//allemand, danois, suisse, anglais, espagnol et italien 


  return array(
			"FR"=>"Française"
			,"BE"=>"Belge Fr"
			,"BEf"=>"Belge Flamands"
			,"NE"=>"Néerlandaise"
     	,"GZ"=>"Allemande"
    	,"DN"=>"Danois"
			,"CH"=>"Suisse"
			
      ,"EN"=>"Anglaise"
			,"ES"=>"Espagnol"
			,"IT"=>"Italien"
		  ,"Autre"=>"Autre"
	);

}

function _url_format($s){
  $s=strtolower($s);
  $s = _url_format_verif_format($s);

  return $s;
}
function _url_format_verif_format($s){

	$r="";
	$nb=strlen($s);
	for($i = 0; $i < $nb; $i++){
		//print "$i : ".$s[$i]." ".ctype_alnum($s[$i])."<br>";
		if(ctype_alnum($s[$i]) || $s[$i]=='-'){
			$r.=$s[$i];			
		}
		
	} // for
	return $r;
}

function get_info_hotel(){
/*
 * Affiche le nom de l'hotel
 * Alexis ALGOUD 25 juin 07 20:18:47
 */

	$h=&$_SESSION[SESS_HOTEL];

	$r= '<a href="../bin/hotel.php?action=VIEW&id='.$h->id.'">'
  .mb_strtoupper($h->nom,'latin1');
  
  if( $h->nom_gestion!="" ){
  
    $r.=" - ".$h->nom_gestion;
  
  }
  
  $r.='</a>';

  return $r;

}

function is_admin () {
/*
 * Détermine si l'utilisateur est administrateur
 */
	$u = &$_SESSION[SESS_USER];
	if (isset($u) && $u->type == "MASTER") {
		return true;
	}
	return false;
}

function is_user () {
/*
 * Détermine si l'utilisateur est standard
 */
	$u = &$_SESSION[SESS_USER];
	if (isset($u) && $u->type == "SLAVE") {
		return true;
	}
	return false;
}

function unset_session ($mask) {
/*
 * Détruit toutes les variables de session en 
 * fonction du masque
 * Alexis ALGOUD 25 juin 07 20:20:25
 */

	$mask.="_";

	$keys=array_keys($_SESSION);
	$nb=count($keys);
	for ($i = 0; $i < $nb; $i++) {
		$k = $keys[$i];
		$k_mask = substr($k,0,strlen($mask));
		if(!strcmp($k_mask, $mask)){
			//$_SESSION[$k]=null;
			unset($_SESSION[$k]);
			//print "ss : $k<br>";
		}
	} // for
}

function is_not_popup () {
/*
 * Détermine si la fenêtre est en mode popup
 * Alexis ALGOUD 25 juin 07 20:20:58
 */
	if (isset($_REQUEST['mode']) && !strcmp($_REQUEST['mode'],"POPUP"))
		return false;

	return true;
}

function is_popup_var () {
/*
 * Ajoute le champ formulaire permettant
 * de propager l'information sur le mode popup
 * Alexis ALGOUD 25 juin 07 20:21:24
 */

	if (isset($_REQUEST['mode'])) {
		$form = new TForm;
		echo $form->hidden("mode", $_REQUEST['mode']);
	}

}
function menu_off(){
/*
 * Menu avant identification
 * Alexis ALGOUD 25 juin 07 20:22:09
 */

	$t=new TTbl;
echo "<br>";
	$t->beg_tbl('','100%','','','top');

	$t->beg_line('title');
	$t->Cell($t->link($t->img("logo-2-all.png","Accueil"),"../bin/main.php"),'20%','','','left');
	$t->Cell(
		/*$t->link("A propos","javascript:showPopup('../dlg/about.php','close','',500,250)","lien")
		." | ".*/$t->link("Retour au site",DIR_HTTP_SITE,"lien")
	,'80%','lien','','right','top');
	$t->end_line();

	$t->end_tbl();
}

/**
 * Affichage du logo de l'application et des liens d'environnement
 * a si administrateur
 * u si user
 * o si offline
 */
function menu_header () {
/*
 * Menu haut
 * Alexis ALGOUD 25 juin 07 20:22:28
 */
	

	$t=new TTbl;
	$link_user =$t->link("Aide",DIR_HTTP_SITE."?q=node/9","lien")
	//	." | ".$t->link("A propos","javascript:showPopup('../dlg/about.php','close','',500,250)","lien")
		." | ".$t->link("Votre profil","../bin/utilisateur.php?action=VIEW","lien")
		." | ".$t->link("Vous déconnecter","../bin/main.php?action=DECONNEXION","lien")
    ." | ".$t->link("Retour au site",DIR_HTTP_SITE,"lien");

	$link_admin =$t->link("Administrer votre hotel","../bin/administration.php","lien")
		." | ".$t->link("Aide",DIR_HTTP_SITE."?q=node/9","lien")
		//." | ".$t->link("A propos","javascript:showPopup('../dlg/about.php','close','',500,250)","lien")
		." | ".$t->link("Votre profil","../bin/utilisateur.php?action=VIEW&id=".$_SESSION[SESS_USER]->id,"lien")
		." | ".$t->link("Vous déconnecter","../bin/main.php?action=DECONNEXION","lien")
    ." | ".$t->link("Retour au site",DIR_HTTP_SITE,"lien");

	$link_offline =/*$t->link("A propos","javascript:showPopup('../dlg/about.php','close','',500,250)","lien")
  ." | ".*/$t->link("Vous déconnecter","../bin/main.php?action=DECONNEXION","lien")
  ." | ".$t->link("Retour au site",DIR_HTTP_SITE,"lien");

echo "<br>";
	$t->beg_tbl('','100%','','','top');

	$t->beg_line('title');
	$t->Cell($t->link($t->img("logo-2-all.png","Accueil"),"../bin/hotel.php"),'20%','','','left');
	$t->Cell("<b>Bienvenue ".getUserName()."<b>",'30%','','','center','top');
	
	if (is_hotel_select() && is_admin()) {
		$t->Cell($link_admin,'50%','lien','','right','top');
	} else if (is_hotel_select() && is_user()) {
		$t->Cell($link_user,'50%','lien','','right','top');
	} else {
		$t->Cell($link_offline,'80%','lien','','right','top');
	}
	$t->end_line();

	$t->end_tbl();

}
function menu($header=true){
/**
 * Menu principal qui s'affiche en haut d'une session valide une fois l'hotel
 * Sélectionné
 * Alexis ALGOUD 03/03/2007 19:58:23
 **/
	$t=new TTbl;
	if($header) {
    menu_header();
	  get_info_hotel();
  }

	echo "<div align=\"center\">";
	echo $t->link($t->img("home.gif","Retour à la liste des hôtels")." Retour hotels","../bin/hotel.php","button");
	echo $t->link($t->img("planning.gif","Accédez au planning")." Le planning","../bin/planing.php","button");
	echo $t->link($t->img("contact.gif","Les clients de l'hôtel")." Les clients","../bin/client.php","button");
	echo $t->link($t->img("resa.gif","Liste des réservation")." Les reservations","../bin/reservation.php","button");
	echo $t->link($t->img("calc.gif","Vos devis et factures")." Devis et factures","../bin/facture.php","button");
	echo "<br><br>";
	echo "</div>";

}

function menu_admin ($header=false) {
/*
 * Menu dédié pour l'administrateur
 * Alexis ALGOUD 25 juin 07 20:23:22
 */


	$t=new TTbl;
	if($header){
    menu_header();
	 get_info_hotel();
  }
  
	echo "<div align=\"center\">";
	if($header) echo "<br />";
  echo "<br />";
	if($header)echo $t->link("Retour hotels","../bin/hotel.php","button_grey");
/*	echo $t->link("Les chambres","../bin/chambre.php","button_grey");*/
	echo $t->link("Les éditions","../bin/administration.php","button_grey");
	echo $t->link("Les catégories","../bin/categorie.php","button_grey");
	echo $t->link("Les produits","../bin/produit.php","button_grey");
	echo $t->link("Les utilisateurs","../bin/utilisateur.php","button_grey");
	echo $t->link("Les paramètres","../bin/param.php","button_grey");
	echo $t->link("Les modèles","../bin/model.php","button_grey");
	echo "<br><br>";
	echo "</div>";
}

function entete($Page="sans titre", $menu='offline', $TMore_params=array()){
/**
 * Alexis ALGOUD
 * 07/10/2006 14:47:53
 * Focntion affichant le code d'entête
 **/
 	
 $_TPL_VARS=array();
 $_TPL_VARS['menu']=$menu;
 $_TPL_VARS['titre']=$Page;
 $_TPL_VARS['bgimage'] = '../templates/ja_purity/images/header/header'.(ceil(rand(1,3))).'.jpg';

 if(is_logged(true)){
  //print "test$Page";exit();
  $_TPL_VARS['welcome_msg']='Bienvenue <a href="../bin/utilisateur.php?action=VIEW&id='.get_sess_user_id().'">'.getUserName().'</a>';
  $_TPL_VARS['dt_end_rights']=get_droit_sess_groupe();
  $_TPL_VARS['have_rights']= have_droit_sess_groupe();
 }
 if(is_hotel_select()){
  $_TPL_VARS['hotel_name']=get_info_hotel();
  
 }
 
 $_TPL_VARS = array_merge($_TPL_VARS, $TMore_params); 
 
 
 if($menu=='popup'){
   ?>
   
  	<html>
  	<head>
  	<title><?=$_TPL_VARS['titre']?> - GHotel </title>
  	<meta http-equiv="Content-Type" content="text/html; charset=LATIN-1">
  	<link href="../styles/style.css" rel="stylesheet" type="text/css">
  	<script language="javascript" src="../scripts/script.js"></script>
  	
    <link rel="stylesheet" type="text/css" media="all" href="../scripts/jscalendar-1.0/calendar-win2k-cold-1.css" title="win2k-cold-1" />
    <script type="text/javascript" src="../scripts/jscalendar-1.0/calendar.js"></script>
    <script type="text/javascript" src="../scripts/jscalendar-1.0/lang/calendar-en.js"></script>
    <script type="text/javascript" src="../scripts/jscalendar-1.0/calendar-setup.js"></script>
  	</head>
  	<body>
   <?
 }
 else{
  require("../templates/start.php");
 }
 
 
 
}
function pied_de_page($menu='offline'){
/**
 * Alexis ALGOUD
 * 07/10/2006 14:48:03
 * Html de pied de page
 **/
  $_TPL_VARS=array();
  $_TPL_VARS['version']=VERSION;
  $_TPL_VARS['date']=date("d/m/Y H:i:s");
  
  if($menu=='popup'){
    ?>
    
      </body></html>

    <?  
  }
  else{
    require("../templates/end.php");
  }
  
  
}
function erreur($s){
/**
 * Alexis ALGOUD
 * 07/10/2006 14:46:07
 * affiche une erreur standart
 **/

	?>
	<center>
	<table class=erreur><tr><td>
	<img src="../images/s_error.png" ALIGN="absmiddle"><b>Erreur : <?=$s?></b>
	</td></tr></table>
	</center>
	<?
}
function info($s){
/**
 * Alexis ALGOUD
 * 07/10/2006 14:46:07
 * affiche une info standart
 **/
	?>
	<center>
	<table class=info><tr><td>
	<img src="../images/s_info.png" ALIGN="absmiddle"><b>Information : <?=$s?></b>
	</td></tr></table>
	</center>
	<?
}
function makepassword(){
/**
 * Alexis ALGOUD
 * 07/10/2006 14:46:48
 * Création aléatoire d'un password
 **/

	return strtoupper(substr(md5(time()),0,6));


}
function _httpcomplete($s){
/**
 * Alexis ALGOUD
 * 07/10/2006 14:47:00
 * Complétion d'URL
 **/

	if(strcmp(substr($s,0,7),"http://")){
		return "http://".$s;
	}
	else {

		return $s;
	}
}

function _fnumber($nombre,$decimal=0){
/*
 * Format un nombre pour l'affichage
 * Alexis ALGOUD 25 juin 07 20:24:23
 */

	return number_format($nombre, $decimal,',',' ');
}
function _f_prix($nombre,$decimal=2){
/*
 * Format un nombre pour l'affichage du prix
 * Alexis ALGOUD 25 juin 07 20:25:40
 */
	
	return _fnumber($nombre,$decimal).MONEY;
}
function _fstring ($string) {
/*
 * Format une chaine en nombre
 * Alexis ALGOUD 25 juin 07 20:26:19
 */

	$number=strtr($string, array(' '=>'',','=>'.'));

	return number_format($number,2,'.','');
}

function gen_sess_name($prefixe="sess"){
/**
 * Génére un nom de session unique
 * Permet d'éviter les écrasement de variables de session en cas de multi ouverture
 * de fenêtre d'application sous la même session
 * Alexis ALGOUD 03/03/2007 19:55:54
 **/

	return $prefixe."_".substr(md5(time()),0,20);

}
function is_logged($no_crash=false){
/**
 * Si cette variable existe alors l'utilisateur est loggué
 * Alexis ALGOUD 03/03/2007 19:56:31
 **/

	if(!isset($_SESSION[SESS_USER])){

    if(!$no_crash){ //print "is_logged($no_crash=false)";exit();
        //entete("Session expirée");
    		//menu_off();
    	/*	erreur("Votre session a expiré");
   
    		$t=new TTbl;
    
    		echo "<div align=\"center\">";
    		echo $t->link("M'identifier","main.php","button");
    		echo "</div>";
    
    		pied_de_page();
    */
        header("location:../bin/main.php?action=ERROR_ID");
    
    		exit();
    }
		
		return false;
	}
	else{
		return true;
	}

}
function is_hotel_select(){
/*
 * Un hotel est sélectionné pour la gestion
 * Alexis ALGOUD 25 juin 07 20:26:43
 */
	if(isset($_SESSION[SESS_HOTEL])){
		return true;
	}
	else{
		return false;
	}
}
function deconnexion(){
/**
 * Détruit la session utilisateur
 * Alexis ALGOUD 03/03/2007 19:57:02
 **/
	$_SESSION=array();
        setcookie("autologin","off", time());
	setcookie("login_auto","", time());
	setcookie("password_auto","", time());


	return true;
}
function debug_session(){
/**
 * Il est toujours utile de pouvoir visualiser ce que l'on stocke en session
 * Alexis ALGOUD 03/03/2007 20:48:05
 **/
	print "<pre>";
	print_r($_SESSION);
	print "</pre>";
}

function _affiche_dispo (&$p, &$chambre, $date, $id_resa_exclude=0) {
/*
 * Crée la ligne du planning
 * Alexis ALGOUD 25 juin 07 20:27:23
 */

	$t= new Ttbl();
	//$t->link($chambre->num, "javascript:pl_reload_line(".$_REQUEST['id_chambre'].", '".$_REQUEST['date']."')")
	$t->Cell(
		$chambre->num
	,-1,'Cell_header','','center');

	for($j = 0; $j < $p->nb_jour; $j++){

		$jour = & $p->TJour[$j];

		$reservation = $chambre->get_reservation($jour->time);
		$id_cellule = "cell_".$chambre->id."_".$jour->time;

		if(!is_object($reservation) || $reservation->id==$id_resa_exclude) {
			$url = "reservation.php?action=NEW&mode=POPUP&dt=".$jour->time."&id_chambre=".$chambre->id;
			
			$lien="libre";
			$class_cell=(($p->dt_current==$jour->time)?'Cell_header':'Cell_planing');

			$is_vide=true;

			$nb_jour=1;
			$colspan=1;
		}
		else{
			$url = "reservation.php?action=VIEW&mode=POPUP&id=".$reservation->id;
			
			if($reservation->etat=="CONFIRM"){
				$class_cell="Cell_confirme";
			}
			else{
				$class_cell="Cell_occupe";
			}



			$is_vide=false;

			$nb_jour = $reservation->get_nbJour();
			$nb_jour_restant = $reservation->get_nbJour($jour->time);

			$lien=$reservation->nom_client." ($nb_jour nuit".(($nb_jour>1)?"s":"").")";

			if($j + $nb_jour_restant< $p->nb_jour){
				$colspan = $nb_jour_restant;
			}
			else{
				$colspan = $p->nb_jour - $j;
			}

			$j+=$nb_jour_restant-1;
		}

		?>
		<td class="<?=$class_cell?>"
		<?=($colspan>1)?"colspan=\"$colspan\"":""?>
		id="<?=$id_cellule?>"
		onmouseover="pl_set_over('<?=$id_cellule?>', true,'Cell_to');"
		onmouseout="pl_set_over('<?=$id_cellule?>', false, '<?=$class_cell?>');"
		align="center" >
		<input type="hidden" name="is_vide_<?=$id_cellule?>" id="is_vide_<?=$id_cellule?>" value="<?=($is_vide)?1:0?>">
		<div id="info_<?=$id_cellule?>"
		<?
			if($is_vide){
				?>
				onClick="pl_new_resa('<?=$url?>','<?=$id_cellule?>','<?=$chambre->id?>',<?=$jour->time?>,'<?=$date?>');"
				<?
			}
			else{
				?>
				onMousedown="pl_set_move_to('<?=$id_cellule?>','<?=$chambre->id?>','<?=$date?>','<?=$reservation->id?>')"
		 		title="Cliquez pour d&eacute;placer" style="cursor:move;"
				<?
			}

		 ?>
		 class="Cell_info">
		<?=$lien?>
		</div>
		<div id="tool_<?=$id_cellule?>" style="display:none;">
		<?
		if ($is_vide) {
			null;
		}
		else{
			_affiche_dispo_menu($reservation, $url,$id_cellule,$date, $nb_jour);
		}
		?>
		</div>
		</td>
		<?


	} // for
}

function _affiche_dispo_menu ($reservation, $url,$id_cellule,$date, $nb_jour) {
/*
 * Affiche les options de chaque réservation pour le planning
 * Alexis ALGOUD 25 juin 07 20:28:16
 */

	$t = new TTbl();
	$t->beg_tbl('','100%');
	$t->beg_line();

	$t->beg_cell(-1,'','','left');

	echo $t->link($t->img("../images/trileft.gif","Arriv&eacute;e un jour plus t&ocirc;t"),"javascript:pl_more_resa_left(".$reservation->id.",".$reservation->id_chambre.", '".$date."')", "lien");
	echo "&nbsp;";

	if ($nb_jour > 1) {
		echo $t->link($t->img("../images/tri.gif","Arriv&eacute;e un jour plus tard"),"javascript:pl_less_resa_left(".$reservation->id.",".$reservation->id_chambre.", '".$date."')", "lien");
		echo "&nbsp;";
	}

	$t->end_Cell();

	$t->Cell(
	$t->link($t->img("../images/edit.gif","Editer cette r&eacute;servation"),"javascript:showPopup('$url','$id_cellule','',600);", "lien").
	"&nbsp;".
	$t->link($t->img("../images/cancel.gif","Supprimer cette r&eacute;servation"),"javascript:pl_delete_resa(".$reservation->id.",".$reservation->id_chambre.", '".$date."')", "lien").
	"&nbsp;",
	-1,'','','center');

	$t->beg_cell(-1,'','','right');

	if ($nb_jour > 1) {
		echo $t->link($t->img("../images/trileft.gif","D&eacute;part un jour plus t&ocirc;t"),"javascript:pl_less_resa(".$reservation->id.",".$reservation->id_chambre.", '".$date."')", "lien");
		echo "&nbsp;";
	}

	echo $t->link($t->img("../images/tri.gif","D&eacute;part un jour plus tard"),"javascript:pl_more_resa(".$reservation->id.",".$reservation->id_chambre.", '".$date."')", "lien");

	$t->end_Cell();

	$t->end_tbl();
}

function _affiche_dispo2 (&$p, &$chambre, $date, $id_resa_exclude=0) {
/*
 * Crée la ligne du planning
 * Alexis ALGOUD 25 juin 07 20:27:23
 */

	$t= new Ttbl();
	//$t->link($chambre->num, "javascript:pl_reload_line(".$_REQUEST['id_chambre'].", '".$_REQUEST['date']."')")
	?>
  <div class="Cell_h"><? echo $t->link($chambre->num,"chambre.php?action=VIEW&id=".$chambre->id,"","",null,"Prix : ".$chambre->prix." - Lits : ".$chambre->nb_lit)?></div>
  <?

	for($j = 0; $j < $p->nb_jour; $j++){

		$jour = & $p->TJour[$j];

		$reservation = $chambre->get_reservation($jour->time);
		$id_cellule = "cell_".$chambre->id."_".$jour->time;

		if(!is_object($reservation) || $reservation->id==$id_resa_exclude) {
			$url = "reservation.php?action=NEW&mode=POPUP&dt=".$jour->time."&id_chambre=".$chambre->id;
			
			$lien="libre";
			$class_cell=(($p->dt_current==$jour->time)?'Cell_h':'Cell');

			$is_vide=true;

			$nb_jour=1;
			$colspan=1;
		}
		else{
			$url = "reservation.php?action=VIEW&mode=POPUP&id=".$reservation->id;
			
			
			
			if($reservation->etat=="PAYEE"){
				$class_cell="Cell_payee";
			}
			elseif($reservation->etat=="FACTUREE"){
				$class_cell="Cell_facturee";
			}
			elseif($reservation->etat=="CONFIRM"){
				$class_cell="Cell_confirme";
			}
			else{
				$class_cell="Cell_occupe";
			}



			$is_vide=false;

			$nb_jour = $reservation->get_nbJour();
			$nb_jour_restant = $reservation->get_nbJour($jour->time);

			$lien=$reservation->nom_client;
      if($nb_jour>1){
        $lien.=" ($nb_jour nuit".(($nb_jour>1)?"s":"").")";
      }
      

			if($j + $nb_jour_restant< $p->nb_jour){
				$colspan = $nb_jour_restant;
			}
			else{
				$colspan = $p->nb_jour - $j;
			}

			$j+=$nb_jour_restant-1;
		}
    
    if($colspan==1)$div_w=100;
    else $div_w = 100 * $colspan + (($colspan-1));

		?>
		<div style="width:<?=$div_w?>px;" class="<?=$class_cell?>" id="<?=$id_cellule?>">
		
	  <div style="padding:2px;"
		onmouseover="pl_set_over('<?=$id_cellule?>', true,'Cell_to');"
		onmouseout="pl_set_over('<?=$id_cellule?>', false, '<?=$class_cell?>');"
		align="center" >
		<input type="hidden" name="is_vide_<?=$id_cellule?>" id="is_vide_<?=$id_cellule?>" value="<?=($is_vide)?1:0?>">
		<div id="info_<?=$id_cellule?>"
		<?
			if($is_vide){
				?>
				onClick="pl_new_resa('<?=$url?>','<?=$id_cellule?>','<?=$chambre->id?>',<?=$jour->time?>,'<?=$date?>');"
				<?
			}
			else{
				?>
				onMousedown="pl_set_move_to('<?=$id_cellule?>','<?=$chambre->id?>','<?=$date?>','<?=$reservation->id?>')"
		 		title="Cliquez pour d&eacute;placer" style="cursor:move;"
				<?
			}

		 ?>
		 class="Cell_info">
		<?=$lien?>
		</div>
		<div id="tool_<?=$id_cellule?>" style="display:none;">
		<?
		if ($is_vide) {
			null;
		}
		else{
			_affiche_dispo_menu($reservation, $url,$id_cellule,$date, $nb_jour);
		}
		?>
		</div>
		</div>
		
		</div>
		<?


	} // for
}

function ajout_aide($code_aide=''){
  $aide='';

  $trans=array(
    "'"=>"\\'"
    ,"\n"=>'\n'
    ,"\r"=>''
  );
  
  switch ($code_aide) {
    case 'chambre_tarif':
      $msg_aide="Si vous saisissez ce tarif avec des tarifs saisonniers, celui-ci ne sera exploité que si aucun des tarifs saisonniers ne peut être utilisé à la date de la réservation";
    
      break;
    default:
    	$msg_aide="Code inconnu";
    	break;
  }
  $aide.='<div class="puce_aide">';
  $aide.='<a href="javascript:window.alert(\''.strtr($msg_aide, $trans).'\');">(?)</a>';
  $aide.='<div class="content">';
  $aide.= $msg_aide; 
  $aide.='</div>';
  $aide.='</div>';



  return $aide;

}

function valide_email ($email) {
	// Auteur : bobocop (arobase) bobocop (point) cz
	// Traduction des commentaires par mathieu
	
	// Le code suivant est la version du 2 mai 2005 qui respecte les RFC 2822 et 1035
	// http://www.faqs.org/rfcs/rfc2822.html
	// http://www.faqs.org/rfcs/rfc1035.html
	
	$atom   = '[-a-z0-9!#$%&\'*+\\/=?^_`{|}~]';   // caractères autorisés avant l'arobase
	$domain = '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)'; // caractères autorisés après l'arobase (nom de domaine)
	                               
	$regex = '/^' . $atom . '+' .   // Une ou plusieurs fois les caractères autorisés avant l'arobase
	'(\.' . $atom . '+)*' .         // Suivis par zéro point ou plus
	                                // séparés par des caractères autorisés avant l'arobase
	'@' .                           // Suivis d'un arobase
	'(' . $domain . '{1,63}\.)+' .  // Suivis par 1 à 63 caractères autorisés pour le nom de domaine
	                                // séparés par des points
	$domain . '{2,63}$/i';          // Suivi de 2 à 63 caractères autorisés pour le nom de domaine
	
	// test de l'adresse e-mail
	if (preg_match($regex, $email)) {
	    return true;
	} else {
	    return false;
	}
}

?>
