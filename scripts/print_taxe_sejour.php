<?php

require("../includes/inc.php");
require("../fpdf153/fpdf.php");
require("../includes/class.pdfmaker.php");

$db = new Tdb;
//$db->db->debug=true;
$r = new TRequete;

$hotel = & $_SESSION[SESS_HOTEL];

$age[0] = 0;
$age[1] = $hotel->get_parameter("taxe_sejour_age1");
$age[2] = $hotel->get_parameter("taxe_sejour_age2");


list($j, $m, $a)=explode("/",$_REQUEST['dt_jour']);
$time = mktime(0,0,0,$m,$j,$a);
$nb_jour = date('t',$time);


$mois = date('m',$time);
$annee = date('Y',$time);

$Tableau['lignes']=array(
	0=>array(
		0=>"Date||50||C||1||1||0||230||10"
		,1=>"< ".$age[1]."||40||C||1||1||0||230||10"
		,2=>"De ".$age[1]." � ".$age[2]."||40||C||1||1||0||230||10"
		,3=>"> ".$age[2]."||40||C||1||1||0||230||10"
	)
);

$s[1] = 0;
$s[2] = 0;
$s[3] = 0;


for ($i = 1; $i <= $nb_jour; $i++) {
	$time = mktime(0,0,0,$mois,$i,$annee);
	$TResa = $r->liste_toute_reservation_par_date($db, $time, $time);
	
	$t[1] = 0;
	$t[2] = 0;
	$t[3] = 0;
	
	
	foreach ($TResa as $k => $v) {
		$resa = new TReservation;
		$resa->load($db, $TResa[$k], true, true, true);
		
//		$t[1] += 1; $s[1] += 1;
//		$t[2] += 1; $s[2] += 1;
//		$t[3] += 1; $s[3] += 1;
		
		$t[1] += $resa->get_nb_personne(true,$age[0],$age[1]);
		$t[2] += $resa->get_nb_personne(true,$age[1],$age[2]);
		$t[3] += $resa->get_nb_personne(true,$age[2]);
		
		$s[1] += $resa->get_nb_personne(true,$age[0],$age[1]);
		$s[2] += $resa->get_nb_personne(true,$age[1],$age[2]);
		$s[3] += $resa->get_nb_personne(true,$age[2]);
	}
		$tab = array();
		$tab[] = date('d/m/Y', $time)."||50||C||0||0||0||0";
		$tab[] = $t[1]."||40||C||0||0||0||0";
		$tab[] = $t[2]."||40||C||0||0||0||0";
		$tab[] = $t[3]."||40||C||0||0||0||0";

		$Tableau['lignes'][] = $tab;
}

$tab = array();
$tab[] = "TOTAL||50||C||1||1||0||230||10";
$tab[] = $s[1]."||40||C||1||1||0||230||10";
$tab[] = $s[2]."||40||C||1||1||0||230||10";
$tab[] = $s[3]."||40||C||1||1||0||230||10";

$Tableau['lignes'][] = $tab;




$Tableau['hotel']=array(
	"nom"=>$hotel->nom
	,"nom_gestion"=>$hotel->nom_gestion
	,"adresse"=>$hotel->adresse
	,"ville"=>$hotel->ville
	,"cp"=>$hotel->cp
	,"signature"=>$hotel->get_parameter("signature_doc")
	,"commercial"=>$hotel->get_parameter("texte_commercial")
	,"tel"=>$hotel->telephone
	,"fax"=>$hotel->fax
);


$p = new TPDFMaker;

$pdf = new TPdf;
$pdf->AliasNbPages();

$trans["annee"] = $annee;
$trans["mois"] = $mois;

$p->set_best_model_for($db, $resa->client->nationalite,"TAXE_SEJOUR") ;//DIR_MODEL."lettre_reservation.txt";
$p->parse_file($Tableau);
$p->write($pdf, $trans);

$pdf->Output("taxe_sejour_mois.pdf","I");

?>
