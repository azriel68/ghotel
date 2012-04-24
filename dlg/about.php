<?php
	require("../includes/inc.php");
	
	entete("A propos...", 'popup');
	echo "<br>";
	$t=new TTbl();
	$t->beg_tbl('formcadre','100%');

	$t->beg_line('listheader');
	$t->Cell("GHOTEL - Logiciel de gestion d'hôtel",'','','2','center');
	$t->end_line();
	$t->beg_line();
	$t->Cell("Auteurs",'','','','center');
	$t->Cell(AUTEUR);
	$t->end_line();
	$t->beg_line();
	$t->Cell("Version",'','','','center');
	$t->Cell(VERSION);
	$t->end_line();
	$t->beg_line();
	$t->Cell(ABOUT,'','','2','center','middle',false,"'100px'");
	$t->end_line();
	
	$t->end_tbl();
	
	echo "<br><center>".$t->img("logo.jpg")."</center>";
	
	pied_de_page('popup');
?>
