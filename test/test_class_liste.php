<?
	require("../includes/inc.php");

	$db=new Tdb;

	$l=new TListe('test',"nom>'C'","nom","D");
	$l->ajout_colonne('nom');
	$l->ajout_colonne('type','code type');
	$l->ajout_colonne('dt_maj','date de mise à jour','d');

	print $l->affiche($db);

	$db->close();

?>