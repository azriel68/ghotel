<?
/********************************************************************
 * Alexis ALGOUD													*
 * 08/11/2006 18:43:32												*
 * IHM de gestion des clients										*
 ********************************************************************/

/**
 * Inclusion des classes
 * V�rification du log de l'utilisateur
 * Affichage de l'en-t�te de page et du menu
 * R�cup�ration de l'action � effectuer (LIST par d�faut)
 */
	require("../includes/inc.php");
	is_logged();
	

	if(is_hotel_select() && is_not_popup()) {	entete("Gestion clients",'online');	}
	else { entete("Gestion clients",'popup');	 }

	if(isset($_REQUEST['action'])){
		$action=$_REQUEST['action'];
	}
	else{
		$action='LIST';
	}

/**
 * R�cup�ration ou cr�ation de la session utilisateur
 * Cr�ation d'un acc�s � la base de donn�es
 * Appels aux fonctions suivant l'action
 */
	$sess_name=isset($_REQUEST['sess_name'])?$_REQUEST['sess_name']:gen_sess_name("client");
	$db=new Tdb();

	switch($action) {
		case 'SAVE':
			$c = & $_SESSION[$sess_name];

			valider($c);
			$cli = enregistrer($db,$c);

			if ($cli && !is_not_popup()) {
				fermer_popup($c);
			} else {
				fiche($c, $sess_name);
			}

			break;
		case 'DELETE':
			$c = & $_SESSION[$sess_name];
			$c->delete($db);

			liste();

			break;
		case 'VIEW':
			$_SESSION[$sess_name]=new TClient();
			$c = & $_SESSION[$sess_name];
			$c->load($db, $_REQUEST['id']);

			fiche($c,$sess_name);

			break;
		case 'NEW':
			$_SESSION[$sess_name]=new TClient();
			$c = & $_SESSION[$sess_name];

			fiche($c,$sess_name);

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
	
  if(is_hotel_select() && is_not_popup()) {	pied_de_page();	}
	else { pied_de_page('popup');	 }

  

/************************************
 * FONCTIONS LOCALES				*
 ************************************/

function fermer_popup (&$c) {
	?><script language="JavaScript" type="text/javascript">
	LinkForm ('<?=$_REQUEST["origine"]?>',
		'id_client=<?=$c->id?>;nom_client=<?=$c->get_client_name()?>;adresse_client=<?=$c->adresse?>;')
	</script><?
}

/**
 * R�cup�ration des champs du formulaire envoy�s par POST
 */
function valider(&$c) {
	$c->civilite=$_POST['civilite'];
	$c->nom=$_POST['nom'];
	$c->prenom=$_POST['prenom'];
	$c->num_passport=$_POST['num_passport'];
	$c->nationalite=$_POST['nationalite'];
	$c->adresse=$_POST['adresse'];
	$c->tel=$_POST['tel'];
	$c->email=$_POST['email'];
	$c->type=$_POST['type'];
	$c->observation=$_POST['observation'];
	$c->tarif_neg=$_POST['tarif_neg'];
	$c->ref_bank=$_POST['ref_bank'];
	$c->source=$_POST['source'];
	
	$c->id_hotel=get_sess_hotel_id();
}

/**
 * V�rification des champs saisis
 * Sauvegarde du client dans la base
 */
function enregistrer(&$db, &$c) {
	if($c->nom!="") {
		$c->save($db);
		info("Client ".$c->nom." enregistr�");
		return true;
	}
	else {
		erreur("Le nom est obligatoire");
	}
	
	return false;
}

/**
 * Affichage d'une fiche de consultation / saisie d'un client
 */
function fiche(&$c,$sess_name) {

	$formname="form_client";
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
	echo $form->hidden("origine", $_REQUEST["origine"]);

	is_popup_var();
	$t=new TTbl();

	$t->beg_tbl('formcadre',800,2,'','center');
	$t->beg_line("listheader");
	$t->Cell(
		"Fiche client"
	,-1,'',2);
	$t->end_line();
	$t->beg_line();
	$t->Cell("Civilit�");
	$t->Cell( $form->combo('','civilite',$c->TCivilite, $c->civilite) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Nom");
	$t->Cell( $form->texte('','nom',$c->nom, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Pr�nom");
	$t->Cell( $form->texte('','prenom',$c->prenom, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("N� Passport");
	$t->Cell( $form->texte('','num_passport',$c->num_passport, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Nationalit�");
	$t->Cell( $form->combo('','nationalite',$c->TNationalite, $c->nationalite) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Adresse");
	$t->Cell( $form->zonetexte('','adresse',$c->adresse, 60, 5) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("T�l�phone");
	$t->Cell( $form->texte('','tel',$c->tel, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Email");
	$t->Cell( $form->texte('','email',$c->email, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Type");
	$t->Cell( $form->combo('','type',$c->TType, $c->type) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Tarif n�goci�");
	$t->Cell( $form->texte('','tarif_neg',$c->tarif_neg, 10, 255)." �" );
	$t->end_line();
	$t->beg_line();
	$t->Cell("R�f. bancaire");
	$t->Cell( $form->texte('','ref_bank',$c->ref_bank, 80, 255) );
	$t->end_line();
	
  $t->beg_line();
	$t->Cell("Source");
	$t->Cell( $form->texte('','source',$c->source, 80, 255) );
	$t->end_line();
  
  if($c->data_import!=""){
  $t->beg_line();
	$t->Cell("Donn�es de l'import externe");
	$t->Cell( $c->data_import );
	$t->end_line();
  
  }
  
  
  $t->beg_line();
	$t->Cell("Observations diverses");
	$t->Cell( $form->zonetexte('','observation',$c->observation, 60, 5) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Modification");
	$t->Cell($c->get_dtmaj());
	$t->end_line();
	$t->beg_line();
	$t->Cell("Cr�ation");
	$t->Cell($c->get_dtcre());
	$t->end_line();

	$t->end_tbl();

	echo "<p align=\"center\">";
	echo $t->link("Liste","javascript:go_liste()","button");

	if($c->id>0)
		echo $t->link("Supprimer","javascript:supprimer()","button_delete");

  if(have_droit_sess_groupe()){
    echo $t->link("Valider","javascript:valider()","button_valid");
  }
  else{
    echo get_right_erreur_msg($t->link("Valider","#","button_valid"));  
  }  
  
	echo "</p>";

	echo $form->end_form();
	
	if($c->id>0){
    liste_reservation_du_client($c);
  	liste_factures_du_client($c);
  }
	
	
}

function liste_reservation_du_client(&$c){
	$listname='dblist1';
	$lst=new TListView($listname);

	//titre du r�f�rentiel
	echo "<br><h1>Liste des reservations de ".$c->civilite." ".$c->prenom." ".$c->nom."</h1>";

	$t=new TTbl;

	//requ�te
	$sql = "SELECT a.id as 'ID', a.etat as 'Etat', b.num as 'Chambre',
	a.dt_deb as 'Date arriv�e', a.dt_fin as 'Date d�part', a.dt_cre as 'Cr�ation'
	FROM hot_reservation a LEFT JOIN hot_chambre b ON a.id_chambre=b.id
	WHERE a.id_client=".$c->id."";

	$ordercolumn = isset($_REQUEST["orderColumn"])?$_REQUEST["orderColumn"]: 'Cr�ation';
	$ordertype = isset($_REQUEST["orderTyp"])?$_REQUEST["orderTyp"]:"D";
	$pagenumber = isset($_REQUEST["pageNumber"])?$_REQUEST["pageNumber"]:0;

	//initialisation de la requ�te
	$lst->Set_Query($sql);
	//chargement de la requ�te
	$lst->Load_query($ordercolumn,$ordertype); // on charge la requ�te
	$lst->Set_pagenumber($pagenumber);

	$lst->Set_Key("ID",'id');
	$lst->Set_columnType('Cr�ation','DATE');
	$lst->Set_columnType('Date d�part','DATE');
	$lst->Set_columnType('Date arriv�e','DATE');
	
	$resa=new TReservation();
	$lst->Str_trans('Etat',$resa->TEtat);

	$lst->Set_OnClickAction('OpenForm',"reservation.php?action=VIEW");
	$lst->Set_nbLinesPerPage(30);


	//Affichage de la liste
	echo "<p align=\"center\">".$t->link("Ajouter une r�servation","reservation.php?action=NEW&client=".$c->id,"button")."</p>";
	echo $lst->Render();

}

function liste_factures_du_client(&$c){
	$listname='dblist1';
	$lst=new TListView($listname);

	//titre du r�f�rentiel
	echo "<br><h1>Liste des factures de ".$c->civilite." ".$c->prenom." ".$c->nom."</h1>";

	$t=new TTbl;

	//requ�te
	$sql = "SELECT id as 'ID', numero as 'Num�ro', dt_facture as 'Date',
			dt_cre as 'Cr�ation'
	FROM hot_facture
	WHERE id_client=".$c->id."";

	$ordercolumn = isset($_REQUEST["orderColumn"])?$_REQUEST["orderColumn"]: 'Cr�ation';
	$ordertype = isset($_REQUEST["orderTyp"])?$_REQUEST["orderTyp"]:"D";
	$pagenumber = isset($_REQUEST["pageNumber"])?$_REQUEST["pageNumber"]:0;

	//initialisation de la requ�te
	$lst->Set_Query($sql);
	//chargement de la requ�te
	$lst->Load_query($ordercolumn,$ordertype); // on charge la requ�te
	$lst->Set_pagenumber($pagenumber);

	$lst->Set_Key("ID",'id');
	$lst->Set_columnType('Cr�ation','DATE');
	$lst->Set_columnType('Date','DATE');

	$lst->Set_OnClickAction('OpenForm',"facture.php?action=VIEW");
	$lst->Set_nbLinesPerPage(30);


	//Affichage de la liste
	echo "<p align=\"center\">".$t->link("Ajouter une facture","facture.php?action=NEW&client=".$c->id,"button")."</p>";
	echo $lst->Render();
}



/**
 * Affichage de la liste des clients
 * ID, LOGIN, MOT DE PASSE, TYPE, DATE CREATION
 */
function liste(){
	
	$formname="form_search";
?>

<script language="javascript">
function go_liste(){
 	document.forms['<?=$formname?>'].elements['action'].value='LIST';
 	document.forms['<?=$formname?>'].submit();
}
</script>

<?

	$listname='dblist1';
	$lst=new TListView($listname);
	$form=new TForm($_SERVER['PHP_SELF'],$formname);
	echo $form->hidden("action", "LIST");

	//titre du r�f�rentiel
	echo "<h1>Liste des clients</h1>";

	$t=new TTbl();

	/**
	 * Requ�te
	 */
	$sql = "SELECT id as 'ID',civ as 'Civilit�',nom as 'Nom', prenom as 'Pr�nom'
			,adresse as 'Adresse',dt_cre as 'Cr�ation'
			FROM hot_client ";

	$where="WHERE id_hotel=".get_sess_hotel_id()." ";
	$search=isset($_REQUEST['search'])?$_REQUEST['search']:"";
	
	if ($search!="") {
	  $mot = strtr(trim($_REQUEST['search']),array(" "=>"%"));
	
		$where .= "AND (nom like '%".$mot."%' OR prenom LIKE '%$mot%' 
    OR adresse LIKE '%$mot%' OR tel  LIKE '%$mot%' OR email  LIKE '%$mot%') ";
    
	}
	
	$charIdx=isset($_REQUEST['charIndex'])?$_REQUEST['charIndex']:"all";
	     
	switch($charIdx){
     	case "other":
     		$where.="AND idx='0'";
			break;
		case "all":
			null;
			break;
		default:
			$where.="AND idx='".$charIdx."'";
			break;
	} // switch
	
	$sql .= $where;

	$TIndex['table']="hot_client";
	$TIndex['condition']="id_hotel=".get_sess_hotel_id();
	$TIndex['char']=$charIdx;

	$ordercolumn = isset($_REQUEST["orderColumn"])?$_REQUEST["orderColumn"]: 'Cr�ation';
	$ordertype = isset($_REQUEST["orderTyp"])?$_REQUEST["orderTyp"]:"D";
	$pagenumber = isset($_REQUEST["pageNumber"])?$_REQUEST["pageNumber"]:0;

	$lst->Set_Query($sql);
	$lst->Load_query($ordercolumn,$ordertype,$TIndex);
	$lst->Set_pagenumber($pagenumber);

	$lst->Set_Key("ID",'id');
	$lst->Set_hiddenColumn("ID",true);
	$lst->Set_columnType('Cr�ation','DATE');

	$lst->Set_OnClickAction('OpenForm',$_SERVER['PHP_SELF']."?action=VIEW");
	$lst->Set_nbLinesPerPage(30);

  if(isset($_REQUEST['search'])){
    /*$trans_s=array($_REQUEST['search']=>'<strong>'.$_REQUEST['search'].'</strong>');
    $lst->Str_trans('Nom',$trans_s);
    $lst->Str_trans('Pr�nom',$trans_s);
    $lst->Str_trans('Adresse',$trans_s);
*/
    $lst->highlight_search('Nom', $_REQUEST['search']);
    $lst->highlight_search('Pr�nom', $_REQUEST['search']);
    $lst->highlight_search('Adresse', $_REQUEST['search']);

  }
  

//	echo $t->draw_index($_SERVER['PHP_SELF']."?", "hot_client;id_hotel=".get_sess_hotel_id(), "idx", $charIdx);
	echo $form->texte('','search',$search,40,255);
	echo $t->link("Recherche (nom, adresse, t�l., e-mail)","javascript:go_liste()","lien2");
	
	echo "<p align=\"center\">";
  if(have_droit_sess_groupe()){
    echo $t->link("Ajouter un client","?action=NEW","button");
  }
  else{
  
    echo get_right_erreur_msg($t->link("Ajouter un client","#","button"));  
  }  
  echo "</p>";
  
	
	echo $form->end_form();
	
	echo $lst->Render();
}

?>
