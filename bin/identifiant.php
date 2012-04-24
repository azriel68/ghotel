<?php

require("../includes/inc.php");

if(isset($_REQUEST['action'])){
	$action=$_REQUEST['action'];
}
else{
	$action='VIEW';
}

$db=new Tdb;
	
	
$db->db->debug=true;
	switch($action){
		case 'VIEW':
			entete("Récupérez vos identifiants");
			fiche();
			break;
		case 'SEND':
			entete("Récupérez vos identifiants");
			
			if (isset($_REQUEST["email"]) && trim($_REQUEST["email"])!="" && valide_email(trim($_REQUEST["email"]))) {
				$u = new TUtilisateur;
				$u->load_from_email($db, $_REQUEST["email"]);
			
				if ($u->id != 0) {
					$u->send_mail_account_lost();
					info("Les information de connexion on été renvoyé à l'adresse ".$_REQUEST["email"]);
				} else {
					erreur("L'e-mail indiqué ne correspond à aucun compte GHôtel");
				}
			} else {
				erreur("Merci d'indiquer un e-mail valide");
			}
			
			fiche();
			break;
		default:
			erreur("inconnu : ".$action);
	} // switch

	$db->close();
	pied_de_page();


function fiche(){

  echo "<h1 class=\"titre\">Vous avez un compte Ghôtel</h1>";

	$formname="formlogin";
	$form=new TForm($_SERVER['PHP_SELF'], $formname);
	echo $form->hidden("action","LOGIN");
?>
<script language="javascript">
function send(){
 	document.forms['<?=$formname?>'].elements['action'].value='SEND';
 	document.forms['<?=$formname?>'].submit();
}

</script>
<?

	$t=new TTbl;
	
	$t->beg_tbl('formcadre',-1,2,'','center');
	$t->beg_line("listheader");
	$t->Cell("Identifiez-vous",-1,'',2);
	$t->end_line();
	$t->beg_line();
	$t->Cell("Votre adresse e-mail ",-1,'','','right');
	$t->Cell( $form->texte("","email","",30,255));
	$t->end_line();
	$t->end_tbl();

	echo "<p align=\"center\">";
	echo $t->link("Envoyer","javascript:send()","button");
	echo "</p>";

	echo $form->btsubmithidden('','btvalidation');

	echo $form->end_form();

}

?>
