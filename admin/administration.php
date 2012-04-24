<?php 
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
	
	entete("Gestion de l'application");

	if(is_hotel_select()){
		menu_admin();
	}
	
	/**
	 * ...
	 */
	
	$r = new TRequete();
	$t = new TTbl();
	$db = new Tdb();
	
	$r->nombre_chambre($db);
	$nb_chambre = $r->nb_resultat;
	$r->nombre_categorie($db);
	$nb_categorie = $r->nb_resultat;
	$r->nombre_produit($db);
	$nb_produit = $r->nb_resultat;
	$r->nombre_client($db);
	$nb_client = $r->nb_resultat;
	
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
	$t->Cell("Nombre de catégories $nb_catégorie ");
	$t->end_line();
	$t->beg_line();
	$t->Cell("Nombre de produits $nb_produit ");
	$t->end_line();
	$t->beg_line();
	$t->Cell("Nombre de clients $nb_client ");
	$t->end_line();


	$t->end_tbl();
	
?>