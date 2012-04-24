<?
/********************************************************************
 * Alexis ALGOUD													*
 * 08/11/2006 18:43:32												*
 * IHM de gestion des utilisateurs									*
 ********************************************************************/

/**
 * Inclusion des classes
 * V�rification du log de l'utilisateur
 * Affichage de l'en-t�te de page et du menu
 * R�cup�ration de l'action � effectuer (LIST par d�faut)
 */
	require("../includes/inc.php");
	is_logged();

	if(isset($_REQUEST['action'])) {
		$action=$_REQUEST['action'];
	}
	else {
		$action='LIST';
	}
		
	if($action!= "VIEW" && !is_admin()) {
		entete("Acc�s refus�");
		erreur ("Vous n'�tes pas autoris� � afficher cette page");
		exit();
	}
	
	entete("Gestion utilisateurs");
	if(is_hotel_select()) {	menu_admin();	}



/**
 * R�cup�ration ou cr�ation de la session utilisateur
 * Cr�ation d'un acc�s � la base de donn�es
 * Appels aux fonctions suivant l'action
 */
	$sess_name=isset($_REQUEST['sess_name'])?$_REQUEST['sess_name']:gen_sess_name("utilisateur");
	$db=new Tdb();
	
	switch($action) {
		case 'SAVE':
			$u = & $_SESSION[$sess_name];

			valider($u);
			enregistrer($db,$u);
			fiche($u, $sess_name);

			break;
		case 'DELETE':
			$u = & $_SESSION[$sess_name];
			$u->delete($db);

			liste();

			break;
		case 'VIEW':
			$_SESSION[$sess_name]=new TUtilisateur();
			$u = & $_SESSION[$sess_name];
			$u->load($db, $_REQUEST['id']);

			fiche($u,$sess_name);

			break;
		case 'NEW':
			$_SESSION[$sess_name]=new TUtilisateur();
			$u = & $_SESSION[$sess_name];

			fiche($u,$sess_name);

			break;
		case 'LIST':
			liste();

			break;
		default:
			erreur("L'action ".$action." est inconnue");
	} // switch

/**
 * Fermeture de la connexion � la base de donn�es
 * Affichage du pied de page
 */
	$db->close();
	pied_de_page();

/************************************
 * FONCTIONS LOCALES				*
 ************************************/

/**
 * R�cup�ration des champs du formulaire envoy�s par POST
 */
function valider(&$u) {
	$u->login = $_POST['login'];
	$u->password = $_POST['password'];
	$u->nom = $_POST['nom'];
	$u->prenom = $_POST['prenom'];
	$u->type = $_POST['type'];
}

/**
 * V�rification des champs saisis
 * Sauvegarde de l'utilisateur dans la base
 */
function enregistrer(&$db, &$u) {
	if($u->login!="" && $u->password!="") {
		$u->save($db);
		info("Utilisateur ".$u->login." enregistr�");
	}
	else {
		erreur("Le nom et le mot de passe sont obligatoires");
	}
}

/**
 * Affichage d'une fiche de consultation / saisie d'un utilisateur
 */
function fiche(&$u,$sess_name) {

	$formname="form_utilisateur";
?>

<script language="javascript">
function go_liste(){
 	document.forms['<?=$formname?>'].elements['action'].value='LIST';
 	document.forms['<?=$formname?>'].submit();
}
function valider(){
 	document.forms['<?=$formname?>'].elements['action'].value='SAVE';
 	document.forms['<?=$formname?>'].submit();
}
function supprimer(){
	if(window.confirm('Voulez-vous vraiment supprimer cette fiche ?')){
	document.forms['<?=$formname?>'].elements['action'].value='DELETE';
 	document.forms['<?=$formname?>'].submit();
	}
}
</script>

<?
	$form=new TForm($_SERVER['PHP_SELF'], $formname);
	echo $form->hidden("sess_name", $sess_name);
	echo $form->hidden("action", "SAVE");

	$t=new TTbl();

	$t->beg_tbl('formcadre',800,2,'','center');
	$t->beg_line("listheader");
	$t->Cell(
		"Fiche utilisateur"
	,-1,'',2);
	$t->end_line();
	$t->beg_line();
	$t->Cell("Login");
	$t->Cell( $form->texte('','login',$u->login, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Mot de passe");
	$t->Cell( $form->texte('','password',$u->password, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Nom");
	$t->Cell( $form->texte('','nom',$u->nom, 30, 30) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Pr�nom");
	$t->Cell( $form->texte('','prenom',$u->prenom, 30, 30) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Type");
	$t->Cell( $form->combo('','type',$u->TType, $u->type));
	$t->end_line();
	$t->beg_line();
	$t->Cell("Modification");
	$t->Cell($u->get_dtmaj());
	$t->end_line();
	$t->beg_line();
	$t->Cell("Cr�ation");
	$t->Cell($u->get_dtcre());
	$t->end_line();

	$t->end_tbl();

	echo "<p align=\"center\">";
	
	if(is_admin()) {
		echo $t->link("Liste","javascript:go_liste()","button");

		if($u->id>0)
			echo $t->link("Supprimer","javascript:supprimer()","button_delete");
	}
	echo $t->link("Valider","javascript:valider()","button_valid");
	echo "</p>";

	echo $form->end_form();
}

/**
 * Affichage de la liste des utilisateurs
 * ID, LOGIN, MOT DE PASSE, TYPE, DATE CREATION
 */
function liste() {
	$listname='dblist1';
	$lst=new TListView($listname);

	echo "<h1>Liste des utilisateurs</h1>";

	$t=new TTbl;

	echo "<p align=\"center\">";
	echo $t->link("Ajouter un utilisateur","?action=NEW","button");
	echo "</p>";

	/**
	 * Requ�te
	 */
	$sql = "SELECT id as 'ID',login as 'login', password as 'Mot de passe',
			CONCAT(CONCAT(prenom,' '),nom)as 'Nom',type as 'Type',dt_cre as 'Cr�ation'
			FROM hot_utilisateur ";
	
	/**
	 * Conditions + index
	 */
	$where="";
	$charIdx=isset($_REQUEST['charIndex'])?$_REQUEST['charIndex']:"all";
     
	switch($charIdx){
     	case "other":
     		$where.="WHERE idx='0'";
			break;
		case "all":
			null;
			break;
		default:
			$where.="WHERE idx='".$charIdx."'";
			break;
	} // switch

	$sql .= $where;

	$ordercolumn = isset($_REQUEST["orderColumn"])?$_REQUEST["orderColumn"]: 'Cr�ation';
	$ordertype = isset($_REQUEST["orderTyp"])?$_REQUEST["orderTyp"]:"D";
	$pagenumber = isset($_REQUEST["pageNumber"])?$_REQUEST["pageNumber"]:0;

	$lst->Set_Query($sql);
	$lst->Load_query($ordercolumn,$ordertype);
	$lst->Set_pagenumber($pagenumber);

	$lst->Set_Key("ID",'id');
	$lst->Set_columnType('Cr�ation','DATE');

	$lst->Set_OnClickAction('OpenForm',$_SERVER['PHP_SELF']."?action=VIEW");
	$lst->Set_nbLinesPerPage(30);

	echo $t->draw_index($_SERVER['PHP_SELF']."?", "hot_utilisateur", "idx", $charIdx);
	echo $lst->Render();
}

?>