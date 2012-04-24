<?php


	require("../includes/inc.php");
	
	
	//$p = &$_SESSION[$_REQUEST['p_sess_name']];
	//$chambre = &$p->TChambre[$_REQUEST['i_chambre']];
	
	$db=new Tdb;
	
	$p=new TPlaning();
	$p->init($_REQUEST['date']);
	
	$h = & $_SESSION[SESS_HOTEL];
	$nb_semaine=((double)$h->get_parameter('nb_semaine_planning')!=0)?$h->get_parameter('nb_semaine_planning'):1;
			
	$p->liste_jour($nb_semaine);
	$p->liste_chambre($db);

	$chambre = new TChambre;
	$chambre->load($db, $_REQUEST['id_chambre']);

	$db->close();


	_affiche_dispo2($p, $chambre,$_REQUEST['date'], isset($_REQUEST['id_resa_exclude'])?$_REQUEST['id_resa_exclude']:"");
?>
