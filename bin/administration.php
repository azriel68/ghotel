<?
/********************************************************************
 * Alexis ALGOUD													*
 * 08/11/2006 18:43:32												*
 * IHM d'administration de l'application							*
 ********************************************************************/

/**
 * Inclusion des classes
 * V�rification du log de l'utilisateur
 * Affichage de l'en-t�te de page et du menu administration
 */
	require("../includes/inc.php");
	is_logged();
	
	if(!is_admin()) {
		entete("Acc�s refus�");
		erreur ("Vous n'�tes pas autoris� � afficher cette page");
		exit();
	}
	
	entete("Gestion de l'application",'online');

	if(is_hotel_select() && is_admin()){
		menu_admin();
	}
	
	/**
	 * ...
	 */
	
	
	$db = new Tdb();
	
	fiche($db);
	
	$db->close();
	
	
function fiche(&$db){

  $formname="form_edition";
?>
<script language="javascript">
function liste_resa_jour(){
	dt_jour = document.forms['<?=$formname?>'].elements['dt_jour'].value;
	aff_div("../scripts/print_liste_resa.php?dt_jour="+dt_jour);
}
function liste_resa1_jour(){
	dt_jour = document.forms['<?=$formname?>'].elements['dt_jour'].value;
	aff_div("../scripts/print_liste_resa.php?dt_jour="+dt_jour+"&mode=selection");
}
function liste_resa1_jour_courrier(){
  window.alert('Disponible sous peu');
	/*dt_jour = document.forms['<?=$formname?>'].elements['dt_jour'].value;
	aff_div("../scripts/print_liste_resa.php?dt_jour="+dt_jour+"&mode=selection_mail");*/
}

function taxe_sejour_mois() {
	dt_jour = document.forms['<?=$formname?>'].elements['dt_jour_2'].value;
	aff_div("../scripts/print_taxe_sejour.php?dt_jour="+dt_jour);
}
function stat_insee(){
	date_deb = document.forms['<?=$formname?>'].elements['dt_jour_3'].value;
	date_fin = document.forms['<?=$formname?>'].elements['dt_jour_fin_3'].value;
  aff_div("../scripts/print_tableau_insee.php?date_deb="+date_deb+"&date_fin="+date_fin);
}

function arrivees(){
	date_deb = document.forms['<?=$formname?>'].elements['dt_jour_4'].value;
	date_fin = document.forms['<?=$formname?>'].elements['dt_jour_fin_4'].value;
	aff_div("../scripts/print_liste_resa.php?mode=arrivee&dt_jour="+date_deb+"&dt_jour2="+date_fin);
}

function aff_div (url){
  document.getElementById('div_print').style['display']="block";
  document.getElementById('iframe_print').src=url;
}
</script>
<?

	$r = new TRequete();
	$t = new TTbl();
	
	$r->nombre_chambre($db);
	$nb_chambre = $r->nb_resultat;
	$r->nombre_categorie($db);
	$nb_cat�gorie = $r->nb_resultat;
	$r->nombre_produit($db);
	$nb_produit = $r->nb_resultat;
	$r->nombre_client($db);
	$nb_client = $r->nb_resultat;
	
	$form=new TForm("",$formname);
	
	$t->beg_tbl('formcadre2',800,2,'','center');
	$t->beg_line("listheader");
	/*$t->Cell(
		"Editions de l'hotel ".$form->texte('','dt_jour', date("d/m/Y"),12,10 )
	,-1,'',2);*/
	$t->Cell(
		"Editions de l'hotel "
	,-1,'',3);
	
	$t->end_line();
	
	$t->beg_line();
	$t->Cell($form->texte('','dt_jour', date("d/m/Y"),12,10 ),100);
  $t->Cell("Edition des confirmations de r�servation du jour",300);
  $t->Cell(
    $t->link( $t->img('bt_impression.jpg', 'Imprimer toutes les confirmations') ,"javascript:liste_resa_jour()")
    .'&nbsp;'.$t->link( $t->img('bt_impression1.jpg', 'Imprimer une confirmation') ,"javascript:liste_resa1_jour()")
    .'&nbsp;'.$t->link( $t->img('bt_courrier.jpg', 'Envoyer une confirmation par mail') ,"javascript:liste_resa1_jour_courrier()")
  );
	$t->end_line();
	$t->beg_line();
	
	$t->Cell($form->texte('','dt_jour_2', date("d/m/Y"),12,10 ));
  $t->Cell("Taxe de s�jour mensuelle");
  $t->Cell(
    $t->link($t->img('bt_impression.jpg', 'Imprimer les taxes de s�jour du mois'),"javascript:taxe_sejour_mois()")
  );
	$t->end_line();
	
		$t->beg_line();
	
	$t->Cell($form->texte('','dt_jour_3', date("d/m/Y"),12,10 )
  .'<br />'.$form->texte('','dt_jour_fin_3', date("d/m/Y"),12,10 ));
  $t->Cell("Statistiques Insee");
  $t->Cell(
    $t->link($t->img('bt_impression.jpg', 'Imprimer les statistiques Insee'),"javascript:stat_insee()")
  );
	$t->end_line();

	
	$t->beg_line();
	
		$t->Cell($form->texte('','dt_jour_4', date("d/m/Y"),12,10 )
	  .'<br />'.$form->texte('','dt_jour_fin_4', date("d/m/Y"),12,10 ));
	  $t->Cell("Arriv�es sur p�riode");
	  $t->Cell(
	    $t->link($t->img('bt_impression.jpg', 'Imprimer les arriv�es'),"javascript:arrivees()")
	  );
		$t->end_line();


	$t->beg_line();
	$t->Cell(
    "<div id=\"div_print\" style=\"display:none; height:500px\">
    <iframe id=\"iframe_print\" width=\"100%\" height=\"100%\"></iframe>
    </div>"
  ,-1,'',3);
	$t->end_line();
	$t->end_tbl();
	echo "<br />";
	$t->beg_tbl('formcadre',800,2,'','center');
	$t->beg_line("listheader");
	$t->Cell(
		"Statistiques de l'hotel"
	,-1,'',2);
	$t->end_line();
	$t->beg_line();
	$t->Cell("Nombre de chambres $nb_chambre ");
	$t->end_line();
	$t->beg_line();
	$t->Cell("Nombre de cat�gories $nb_cat�gorie ");
	$t->end_line();
	$t->beg_line();
	$t->Cell("Nombre de produits $nb_produit ");
	$t->end_line();
	$t->beg_line();
	$t->Cell("Nombre de clients $nb_client ");
	$t->end_line();


	$t->end_tbl();
	
}	
	
	
?>
