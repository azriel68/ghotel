<?php
  
/**
 * Gestion du template accueil
 *
 * @version $Id$
 * @copyright 2006 
 */

	require("../includes/inc.php");

 	is_logged();
	
	  switch($_REQUEST['code_page']){
	  	case 'ACC_GEN': 
	  		$_source_template="accueil_general.html"; // fichier source servant de template
			$_destination_file="index.php"; // fichier final une fois généré
	  		$titre="Maison à part : Décoration, Travaux, Immobilier";
			$zone='ACC';
			break;
	  	case 'ACC_DECO': 
	  		$_source_template="accueil_deco.html"; // fichier source servant de template
			$_destination_file="decoration.php"; // fichier final une fois généré
	  		$titre="Décoration - Maison à part";
			$zone='DECO';
			break;
	  	case 'ACC_TRVX': 
	  		$_source_template="accueil_travaux.html"; // fichier source servant de template
			$_destination_file="travaux.php"; // fichier final une fois généré
	  		$titre="Travaux - Maison à part";
			$zone='TRVX';
			break;
	  	case 'ACC_IMMO': 
	  		$_source_template="accueil_immo.html"; // fichier source servant de template
			$_destination_file="immobilier.php"; // fichier final une fois généré
	  		$titre="Immobilier - Maison à part";
			$zone='IMMO';
			break;
	  	case 'ACC_DOSS': 
	  		$_source_template="accueil_dossier.html"; // fichier source servant de template
			$_destination_file="dossiers.php"; // fichier final une fois généré
	  		$titre="Dossiers - Maison à part";
			$zone='DOSS';
			break;
	  	default:
	  		erreur("Code page inconnu : ".$_REQUEST['code_page']);
			exit();
	  } // switch
  
	$db=new Tdb;
	
	$t=new TTemplate($db, $_REQUEST['code_page']);
	
	
	if(isset($_POST['bt_pub'])){
	/**
	 * Enregistrement du format stocké 
	 * 24/11/2006 15:04:53 Alexis ALGOUD
	 **/
		//print_r($_POST);
		
		valider($db,$t);
		
		$body = $t->charge_fichier($_source_template,"WRITE");

		$f1 = fopen(DIR_FILE.$_destination_file,"w");
		fputs($f1, req_haut($titre,null,false,$zone).$body.req_bas("",null,$zone,null,'accueil','accueil '+$zone));
		fclose($f1);		
		
		print "<b>Fichier $_destination_file publié </b>";

	}
	elseif(isset($_POST['bt_maj'])){
	/**
	 * Met à jour les articles cochés 
	 * 24/11/2006 17:04:10 Alexis ALGOUD
	 **/
		valider($db,$t);
		
		
	}
	
	$body = $t->charge_fichier($_source_template);
	
	switch($zone){
		case 'DECO': 
			require DIR_FILE."start_frame_deco.php";
			break;
		case 'TRVX': 
			require DIR_FILE."start_frame_trvx.php";
			break;
		case 'IMMO': 
			require DIR_FILE."start_frame_immo.php";
			break;
		case 'DOSS': 
			require DIR_FILE."start_frame_doss.php";
			break;
		default: 
			require DIR_FILE."start_frame.php";
	} // switch
	
?>
<script language="javascript" src="../scripts/XHRConnection.js"></script>
<script language="javascript" src="../scripts/script.js"></script>
<style>
.work_zone{
	border: thin dashed #AAAAAA;
	background-color: #EEEEEE;
}
.tools_zone{
	border: medium dotted #00CC33;
	background-color: #66FF99;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
}
.lien_zone{
	color: #00aa11;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	text-decoration:underline;
	font-weight:bold;
}
</style>
<div id="hidden_div" style="position:absolute;visibility:hidden;">
<iframe name="hidden_frame" id="hidden_frame">
</iframe>
</div>
<?	
	
	$form=new TForm("pub_acc.php","formpub");
	
	print $body;

	
	$interface  = "";
	$interface .= $form->hidden("code_page", $_REQUEST['code_page']);
	$interface .= "<p align=center>";
	$interface .= $form->btsubmit('Tout mettre à jour', 'bt_maj');
	$interface .= "&nbsp;".$form->btsubmit('Publier la page', 'bt_pub');
	$interface .= "&nbsp;".$form->bt('Retour', 'bt_ret','onClick="document.location.href=\'publication.php\'"');
	$interface .= "</p>";
	
	print $interface;

	echo $form->end_form();
	
	switch($zone){
		case 'DECO': 
			require DIR_FILE."end_frame_deco.php";
			break;
		case 'TRVX': 
			require DIR_FILE."end_frame_trvx.php";
			break;
		case 'IMMO': 
			require DIR_FILE."end_frame_immo.php";
			break;
		case 'DOSS': 
			require DIR_FILE."end_frame_doss.php";
			break;
		default: 
			require DIR_FILE."end_frame.php";
			break;
	} // switch
	
  	$db->close();
	
	
function valider(&$db, &$t){
/**
 * Enregistre toute les infos 
 * 24/11/2006 15:06:18 Alexis ALGOUD
 **/
	$TTag=& $_POST['TTag'];
 
 	$keys=array_keys($TTag);
	$nb=count($keys);
	for($i = 0; $i < $nb; $i++){
		$tag=$keys[$i];
		
		$gab = $TTag[$tag]['select_gabari'];
		$cat = $TTag[$tag]['select_categorie'];
		$zone = $TTag[$tag]['select_zone'];
		$type ="" /*$TTag[$tag]['select_type']*/;
		$id_edito = $TTag[$tag]['select_edito'];
		
		$t->maj_tag($tag, $gab, $cat, $type,$zone, $id_edito);
		
	} // for
	$t->save_tag($db);
	
}
?>