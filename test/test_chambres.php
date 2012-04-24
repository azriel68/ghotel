<?
	require("../includes/inc.php");

	entete("Gestion chambres");

	if(isset($_REQUEST['action'])){
		$action=$_REQUEST['action'];
	}
	else{
		$action='LIST';
	}

	$sess_name=isset($_REQUEST['sess_name'])?$_REQUEST['sess_name']:gen_sess_name("user");
	$id_hotel=isset($_REQUEST["id_hotel"])?($_REQUEST["id_hotel"]):0;

	$db=new Tdb;

	switch($action){
		case 'SAVE':
			$u = & $_SESSION[$sess_name];

			valider($u);
			enregistrer($db,$u);

			fiche($u, $sess_name,$id_hotel, $db);

			break;

		case 'DELETE':
			$u = & $_SESSION[$sess_name];
			$u->delete($db);

			liste($id_hotel);

			break;
		case 'VIEW':
			$_SESSION[$sess_name]=new TChambre();
			$u = & $_SESSION[$sess_name];
			$u->load($db, $_REQUEST['id']);


			fiche($u,$sess_name,$id_hotel, $db);
			break;
		case 'NEW':
			$_SESSION[$sess_name]=new TChambre();
			$u = & $_SESSION[$sess_name];

			fiche($u,$sess_name,$id_hotel, $db);
			break;
		case 'LIST':
			liste($id_hotel);
			break;
		default:
			erreur("inconnu : ".$action);
	} // switch
	$db->close();

	pied_de_page();


function valider(&$u){

	$u->nb_lit = $_POST['nb_lit'];
	$u->prestation = $_POST['prestation'];
	$u->orientation = $_POST['orientation'];
	$u->situation = $_POST['situation'];
	$u->prix = $_POST['prix'];
	$u->num = $_POST['num'];
	$u->id_categorie = $_POST['id_categorie'];
	$u->id_hotel = $_POST['id_hotel'];

}
function enregistrer(&$db, &$u){

	if($u->nb_lit!="" && $u->prestation!="" && $u->orientation!="" && $u->situation!="" && $u->prix!="" && $u->num!=""){
		$u->save($db);
		info("Fiche enregistrée");
	}
	else{
		erreur("Tous les champs sont obligatoires");
	}


}
function fiche(&$u,$sess_name,$id_hotel, $db){

	$formname="form_chambres";
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
	echo $form->hidden("id_hotel", $id_hotel);

	$t=new TTbl;
	$req = new TRequete;

	$sql = "SELECT * FROM hot_field ";
	$sql .= "WHERE hot_field.table_nom = 'hot_chambre'";

	$db->Execute($sql);

	$t->beg_tbl('formcadre',800,2,'','center');
	$t->beg_line("listheader");
	$t->Cell(
		"Fiche chambre"
	,-1,'',2);
	$t->end_line();

	while($db->Get_Line()) {
		$t->beg_line();
		$t->Cell($db->Get_field('champ_nom'));

		$type=$db->Get_field('type');



		$t->Cell($form->texte('',$db->Get_field('champ_nom'),$u->$db->Get_field('champ_nom'),80,255));
		$t->end_line();
	}


	$t->beg_line();
	$t->Cell("Numéro");
	$t->Cell( $form->texte('','num',$u->num, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Nombre de lit");
	$t->Cell( $form->texte('','nb_lit',$u->nb_lit, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Prestation");
	$t->Cell( $form->texte('','prestation',$u->prestation, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Orientation");
	$t->Cell( $form->texte('','orientation',$u->orientation, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Situation");
	$t->Cell( $form->texte('','situation',$u->situation, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Tarif");
	$t->Cell( $form->texte('','prix',$u->prix, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Catégorie");
	$t->Cell( $form->combo('','id_categorie',$req->liste_toute_categorie($db,$id_hotel),$u->id_categorie) );
	$t->end_line();
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
/*	echo $form->bt("Liste","bt_retour"," onClick=\"go_liste()\"");
	echo $form->bt("Valider","bt_valid"," onClick=\"Valider()\"");*/

	echo $t->link("Liste","javascript:go_liste()","button");

	if($u->id>0)echo $t->link("Supprimer","javascript:supprimer()","button_delete");
	echo $t->link("Valider","javascript:valider()","button_valid");

	echo "</p>";

	echo $form->end_form();
}

function liste($id_hotel){
	$listname='dblist1';
	$lst=new TListView($listname);

	//titre du référentiel
	echo "<h1>Liste des chambres de l'hotel ".$id_hotel."</h1>";

	$t=new TTbl;

	echo "<p align=\"center\">".$t->link("Ajouter une chambre","?action=NEW&id_hotel=".$id_hotel,"button")."</p>";


		 $where="";
	     $charIdx="all";
	     if(isset($_REQUEST['charIndex'])){
	             $charIdx=$_REQUEST['charIndex'];
	             switch($_REQUEST['charIndex']){
	                     case "other":
	                            $where.=" WHERE idx='0'";
	                            break;
	                     case "all":
	                             null;
	                             break;
	                     default:
	                             $where.=" WHERE idx='".$_REQUEST['charIndex']."'";
	             } // switch

	     }

	if ($where=="")
		$where=" WHERE hot_chambre.id_hotel = ".$id_hotel;
	else
		$where=" AND hot_chambre.id_hotel = ".$id_hotel;

	//requête
	$sql = "SELECT hot_chambre.id as 'ID', num as 'Numéro', hot_categorie.libelle, nb_lit as 'Nombre de lits', ";
	$sql .= "hot_chambre.prestation as 'Prestation', orientation as 'Orientation', situation as 'Situation', ";
	$sql .= "prix as 'Prix', hot_chambre.dt_cre as 'Création' FROM hot_chambre LEFT JOIN hot_categorie ";
	$sql .= "ON hot_chambre.id_categorie = hot_categorie.id".$where;
	$ordercolumn = isset($_REQUEST["orderColumn"])?$_REQUEST["orderColumn"]: 'Création';
	$ordertype = isset($_REQUEST["orderTyp"])?$_REQUEST["orderTyp"]:"D";
	$pagenumber = isset($_REQUEST["pageNumber"])?$_REQUEST["pageNumber"]:0;

	//initialisation de la requête
	$lst->Set_Query($sql);
	//chargement de la requête
	$lst->Load_query($ordercolumn,$ordertype); // on charge la requête
	$lst->Set_pagenumber($pagenumber);

	$lst->Set_Key("ID",'id');
	$lst->Set_columnType('Création','DATE');

	$lst->Set_OnClickAction('OpenForm',"chambre.php?action=VIEW&id_hotel=".$id_hotel);
	$lst->Set_nbLinesPerPage(30);

// MKO 27.02.2007 pas d'index dans la table hot_hotel
//	echo $t->draw_index($_SERVER['PHP_SELF']."?", "hot_hotel", "idx", $charIdx);

	//Affichage de la liste
	echo $lst->Render();

	echo "<p align=\"center\">".$t->link("Retour à l'hotel","hotel.php?action=VIEW&id=".$id_hotel,"button")."</p>";

}
?>