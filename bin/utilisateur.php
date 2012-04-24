<?
/********************************************************************
 * Alexis ALGOUD													*
 * 08/11/2006 18:43:32												*
 * IHM de gestion des utilisateurs									*
 ********************************************************************/

/**
 * Inclusion des classes
 * Vérification du log de l'utilisateur
 * Affichage de l'en-tête de page et du menu
 * Récupération de l'action à effectuer (LIST par défaut)
 */
	require("../includes/inc.php");
	is_logged();

	if(isset($_REQUEST['action'])) {
		$action=$_REQUEST['action'];
	}
	else {
		$action='LIST';
	}
		
	if($action== "LIST" && !is_admin()) {
		entete("Accès refusé");
		erreur ("Vous n'êtes pas autorisé à afficher cette page");
		exit();
	}
	
	entete("Gestion utilisateurs",'online');
	
	
	if(is_hotel_select() && is_admin()){
		menu_admin();
	}
	


/**
 * Récupération ou création de la session utilisateur
 * Création d'un accès à la base de données
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
			
			if(is_user() || !isset($_REQUEST['id'])){
        $id = get_sess_user_id();
      }
      else {
        $id = $_REQUEST['id'];
      }
			
			$u->load($db, $id);

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
 * Fermeture de la connexion à la base de données
 * Affichage du pied de page
 */
	$db->close();
	pied_de_page();

/************************************
 * FONCTIONS LOCALES				*
 ************************************/

/**
 * Récupération des champs du formulaire envoyés par POST
 */
function valider(&$u) {
	$u->login = $_POST['chp_log'];
	$u->password = $_POST['chp_pass'];
	$u->nom = $_POST['nom'];
	$u->prenom = $_POST['prenom'];
	
	if(is_admin()){
    $u->type = $_POST['type'];
  	if(isset($_POST['TLien'])){
  		$u->set_lien_hotel($_POST['TLien']);
  		
  	}
  }
  	
	$u->id_groupe = get_sess_user_group_id();
	
	
}

/**
 * Vérification des champs saisis
 * Sauvegarde de l'utilisateur dans la base
 */
function enregistrer(&$db, &$u) {
	if($u->login!="" && $u->password!="") {
		//$db->db->debug=true;
		if(!$u->save($db)){
			erreur("Login déjà utilisé");
		}	
		else{
			info("Utilisateur ".$u->login." enregistré");	
		}
		
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
function set_display_div(obj){
	if(obj.value!='MASTER'){
		document.getElementById('div_liste_hotel').style['display']='block';			
	}
	else{
		document.getElementById('div_liste_hotel').style['display']='none';			
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
	$t->Cell( $form->texte('','chp_log',$u->login, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Mot de passe");
	$t->Cell( $form->texte('','chp_pass',$u->password, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Nom");
	$t->Cell( $form->texte('','nom',$u->nom, 30, 30) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Prénom");
	$t->Cell( $form->texte('','prenom',$u->prenom, 30, 30) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Type");
	if(is_admin()){
	 $t->Cell( $form->combo('','type',$u->TType, $u->type,50,"set_display_div(this)"));
	}
	else{
    $t->Cell( $u->TType[$u->type] );
  }
	$t->end_line();
	
	_affiche_hotel($u);
	
	$t->beg_line();
	$t->Cell("Modification");
	$t->Cell($u->get_dtmaj());
	$t->end_line();
	$t->beg_line();
	$t->Cell("Création");
	$t->Cell($u->get_dtcre());
	$t->end_line();

	$t->end_tbl();
	
	
	

	echo "<p align=\"center\">";
	
	if(is_admin()) {
		echo $t->link("Liste","javascript:go_liste()","button");

		if($u->id>0 && $u->id!=get_sess_user_id())
			echo $t->link("Supprimer","javascript:supprimer()","button_delete");
	}
	echo $t->link("Valider","javascript:valider()","button_valid");
	echo "</p>";

	echo $form->end_form();
}


function _affiche_hotel (&$u) {
	$t = new TTbl();
	$r = new TRequete();
	$form = new TForm();
	$db = new Tdb();
	
	$Hotel = array();
	$Lien = array();	
	
	$Hotel=$r->liste_tous_hotel($db,get_sess_user_group_id());
	$nb=count($Hotel);
	
	$Lien=$r->liste_lien_hotel_utilisateur($db,$u->id);
	$t->beg_line();
	$t->beg_cell(-1,'',2);
		$display = ($u->type!='MASTER')?"block":"none";
		
		echo "<div id=\"div_liste_hotel\" style=\"display:$display\">";
	
		$t->beg_tbl('formcadre', '100%');	
		$t->beg_line('listheader');
		$t->Cell("Hotels gérés",-1,'',2);
		$t->end_line();
		
		for ($i = 0; $i < $nb; $i++) {
			$h = new THotel();
			$h->load($db,$Hotel[$i]);
			$t->beg_line();
			$t->Cell($h->nom);
			if(is_admin()){
			   $t->Cell($form->checkbox1('','TLien['.$h->id.']',1,$u->is_Lien($h->id)));
			}
			else{
         
         $t->Cell(
            $u->is_Lien($h->id)?$t->img("ok.gif","Vous avez accez à la gestion de cet hôtel"):$t->img("cancel.gif","Vous n'avez pas accès à la gestion de cet hôtel")
         );
         
      }
			$t->end_line();
		}
		$t->end_tbl();
		echo "</div>";
	$t->end_cell();
	$t->end_line();
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
	 * Requête
	 */
	$sql = "SELECT id as 'ID',login as 'login',
			CONCAT(CONCAT(prenom,' '),nom)as 'Nom',type as 'Type',dt_cre as 'Création'
			FROM hot_utilisateur WHERE id_groupe=".get_sess_user_group_id();
	
	/**
	 * Conditions + index
	 */
	$where="";
	$charIdx=isset($_REQUEST['charIndex'])?$_REQUEST['charIndex']:"all";
     
	switch($charIdx){
     	case "other":
     		$where.=" AND idx='0'";
			break;
		case "all":
			null;
			break;
		default:
			$where.=" AND idx='".$charIdx."'";
			break;
	} // switch

	$sql .= $where;

	$ordercolumn = isset($_REQUEST["orderColumn"])?$_REQUEST["orderColumn"]: 'Création';
	$ordertype = isset($_REQUEST["orderTyp"])?$_REQUEST["orderTyp"]:"D";
	$pagenumber = isset($_REQUEST["pageNumber"])?$_REQUEST["pageNumber"]:0;

	$lst->Set_Query($sql);
	
	$TIndex['table']="hot_utilisateur";
	$TIndex['condition']=" id_groupe=".get_sess_user_group_id();
	$TIndex['char']=$charIdx;
	
  $lst->Load_query($ordercolumn,$ordertype,$TIndex);
	$lst->Set_pagenumber($pagenumber);

	$lst->Set_Key("ID",'id');
	$lst->Set_columnType('Création','DATE');
	
	$u=new TUtilisateur;
	$lst->Str_trans('Type', $u->TType);

	$lst->Set_OnClickAction('OpenForm',$_SERVER['PHP_SELF']."?action=VIEW");
	$lst->Set_nbLinesPerPage(30);

	//echo $t->draw_index($_SERVER['PHP_SELF']."?", "hot_utilisateur", "idx", $charIdx);
	
  
  
  echo $lst->Render();
}

?>
