<?php

 /**
  * Alexis ALGOUD
  * 24/03/2006 13:26:28
  * Script de déclaration des variable globale, session, inclusion
  **/

	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

	$include_path = "../includes/";

 	require($include_path."class.reponse.php");
 	require($include_path."class.groupe.php");
 	require($include_path."class.numerotation.php");
 	require($include_path."class.utilisateur.php");
 	require($include_path."class.planing.php");
 	require($include_path."class.client.php");
 	require($include_path."class.hotel.php");
 	require($include_path."class.chambre.php");
 	require($include_path."class.categorie.php");
 	require($include_path."class.produit.php");
 	require($include_path."class.reservation.php");
	require($include_path."class.facture.php");
	require($include_path."class.facture_pdf.php");
 	require($include_path."class.param.php");
	require($include_path."class.listview.php");
	require($include_path."class.tbl.php");
	require($include_path."class.form.php");
	require($include_path."class.db.php");

	 /**
 	 * Alexis ALGOUD
 	 * 24/03/2006 13:28:49
 	 * Class contenant toute requetes DB non dispo dans les classe
	 * standard
 	 **/

 	require($include_path."class.requete.php");


    /**
      * Déclaration des Globales
      * Alexis ALGOUD 03/03/2007 20:16:14
      **/

	require($include_path."define_var.php");


	/**
	 * Alexis ALGOUD
	 * 24/03/2006 13:29:42
	 * Collection de fonction globale
	 **/
	require($include_path."fonction.php");


 	session_name('G_Hotel');
	session_start();

	if(isset($_REQUEST['DEBUG_SESS'])){
		debug_session();
	}

?>