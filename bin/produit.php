<?
	require("../includes/inc.php");
	is_logged();

	if(!is_admin()) {
		entete("Acc�s refus�");
		erreur ("Vous n'�tes pas autoris� � afficher cette page");
		exit();
	}

	entete("Gestion produits",'online');


	if(is_hotel_select() && is_admin()){
		menu_admin();
	}

	if(isset($_REQUEST['action'])){
		$action=$_REQUEST['action'];
	}
	else{
		$action='LIST';
	}

	$sess_name=isset($_REQUEST['sess_name'])?$_REQUEST['sess_name']:gen_sess_name("produit");
	$id_hotel=isset($_REQUEST["id_hotel"])?($_REQUEST["id_hotel"]):get_sess_hotel_id();

	$db=new Tdb;
	switch($action){
	   case 'ADD_REGLE':
    	$p = & $_SESSION[$sess_name];
  
      $p->add_regle();
			valider($p);
		
			fiche($p, $sess_name);

	     break;
	   case 'DEL_REGLE':
    	$p = & $_SESSION[$sess_name];
  
      $p->del_regle($_REQUEST['p1']);
			valider($p);
		
			fiche($p, $sess_name);

	     break;
		case 'SAVE':
			$p = & $_SESSION[$sess_name];

			valider($p);
			enregistrer($db,$p);

			fiche($p, $sess_name);

			break;

		case 'DELETE':
			$p = & $_SESSION[$sess_name];
			$p->delete($db);
			
			info("Fiche supprim�e");
			liste($id_hotel);

			break;
		case 'VIEW':
			$_SESSION[$sess_name]=new TProduit();
			$p = & $_SESSION[$sess_name];
			$p->load($db, $_REQUEST['id'], true);

			fiche($p,$sess_name,$id_hotel);
			break;
		case 'NEW':
			$_SESSION[$sess_name]=new TProduit();
			$p = & $_SESSION[$sess_name];
			$p->id_hotel = get_sess_hotel_id();
      $p->load_categorie($db);

			fiche($p,$sess_name,$id_hotel);
			break;
		case 'LIST':
			liste($id_hotel);
			break;
		default:
			erreur("inconnu : ".$action);
	} // switch
	$db->close();

	pied_de_page();


function valider(&$p){

	$p->id_hotel = get_sess_hotel_id();
	$p->libelle = $_POST['libelle'];
	$p->description = $_POST['description'];
	$p->prix = $_POST['prix'];
	$p->tva = $_POST['tva'];
	
	if(isset($_REQUEST['TRegle'])){
  
    foreach ($_REQUEST['TRegle'] as $k=>$regle) {
    	
    	$p->TRegle[$k]->nb_personne = $regle['nb_personne'];
    	$p->TRegle[$k]->id_categorie = $regle['id_categorie'];
    	$p->TRegle[$k]->set_dtdeb($regle['dt_deb']);
    	$p->TRegle[$k]->set_dtfin($regle['dt_fin']);
    	$p->TRegle[$k]->nuit_min=$regle['nuit_min'];
    	$p->TRegle[$k]->nuit_max=$regle['nuit_max'];
    	
    }
  
  }
	
}

function enregistrer(&$db, &$p){
//$db->db->debug=true;
	if($p->libelle!="" && $p->description!="" && $p->prix!=""){
		$p->save($db);
		info("Fiche enregistr�e");
	}
	else{
		erreur("Tous les champs sont obligatoires");
	}
}

function _fiche_regle(&$o, &$form){

	$t=new TTbl;
	
	$t->beg_line("listheader");
	$t->Cell(
		"Les r�gles d'insertions automatiques ".$t->link("ajouter","javascript:add_regle()","lien2","","ico_xplus.gif")
	,-1,'',2);
	$t->end_line();
	$t->beg_line();
	$t->beg_Cell(-1,'',2);
	
	
	
	$t->beg_tbl('formcadre','100%');

	$nb=count($o->TRegle);

	$t->beg_line('listheader');
	$t->Cell("Cat�gorie");
	$t->Cell("D�but");
	$t->Cell("Fin");
	$t->Cell("Nb. personnes");
	$t->Cell("Nuits min.");
	$t->Cell("Nuits max.");
	$t->Cell("");
	$t->end_line();

	$class="L1";
	for ($i = 0; $i < $nb; $i++) {
		$l = & $o->TRegle[$i];

		if(!$l->to_delete){
			$t->beg_line($class);
			$t->Cell( $form->combo("","TRegle[$i][id_categorie]", $o->TCategorie, $l->id_categorie,1,'','',array(0=>"--------------"))  );
			$t->Cell( $form->texte("","TRegle[$i][dt_deb]", $l->get_dtdeb(), 12, 10 ) );
			$t->Cell($form->texte("","TRegle[$i][dt_fin]", $l->get_dtfin(), 12, 10 ));
			$t->Cell($form->texte('','TRegle['.$i.'][nb_personne]',$l->nb_personne,3));
			$t->Cell($form->texte('','TRegle['.$i.'][nuit_min]',$l->nuit_min,3));
			$t->Cell($form->texte('','TRegle['.$i.'][nuit_max]',$l->nuit_max,3));
			$t->Cell($t->link($t->img('ico_xminus.gif'),"javascript:del_regle(".$i.")"));
			$t->end_line();
			$class=($class=="L2")?"L1":"L2";
		}

	} // for

	$t->end_tbl();
	
	$t->end_cell();
	$t->end_line();
	
	
}



function fiche(&$p,$sess_name){

	$formname="form_produit";
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
function add_regle(){
  document.forms['<?=$formname?>'].elements['action'].value='ADD_REGLE';
 	document.forms['<?=$formname?>'].submit();

}
function del_regle(i){
  document.forms['<?=$formname?>'].elements['action'].value='DEL_REGLE';
 	document.forms['<?=$formname?>'].elements['p1'].value=i;
 	document.forms['<?=$formname?>'].submit();

}
</script>
<?
	$form=new TForm($_SERVER['PHP_SELF'], $formname);
	echo $form->hidden("sess_name", $sess_name);
	echo $form->hidden("action", "SAVE");
	echo $form->hidden("p1", "");

	$t=new TTbl;

	$t->beg_tbl('formcadre',800,2,'','center');
	$t->beg_line("listheader");
	$t->Cell(
		"Fiche produit"
	,-1,'',2);
	$t->end_line();
	$t->beg_line();
	$t->Cell("Libelle");
	$t->Cell( $form->texte('','libelle',$p->libelle, 50, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Description");
	$t->Cell( $form->zonetexte('','description',$p->description, 40, 3) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Prix");
	$t->Cell( $form->texte('','prix',$p->prix, 10, 10) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("TVA");
	$t->Cell( $form->combo('','tva',$p->TTaux_tva, $p->tva, 1)." %");
	$t->end_line();
	
	_fiche_regle($p, $form);
	
	$t->beg_line();
	$t->Cell("Modification");
	$t->Cell($p->get_dtmaj());
	$t->end_line();
	$t->beg_line();
	$t->Cell("Cr�ation");
	$t->Cell($p->get_dtcre());
	$t->end_line();

	$t->end_tbl();

	echo "<p align=\"center\">";

	echo $t->link("Liste","javascript:go_liste()","button");

	if($p->id>0)echo $t->link("Supprimer","javascript:supprimer()","button_delete");
	echo $t->link("Valider","javascript:valider()","button_valid");

	echo "</p>";

	echo $form->end_form();
}

function liste($id_hotel){
	$listname='dblist1';
	$lst=new TListView($listname);

	//titre du r�f�rentiel
	echo "<h1>Liste des produits</h1>";

	$t=new TTbl;

	echo "<p align=\"center\">".$t->link("Ajouter un produit","?action=NEW","button")."</p>";


	//requ�te
	$sql = "SELECT id as 'ID', libelle as 'Libell�', description as 'Description',
			prix as 'Prix' ,dt_cre as 'Cr�ation'
			FROM hot_produit
			WHERE id_hotel=".get_sess_hotel_id().""; 
			
	
	$where="";
	$charIdx=isset($_REQUEST['charIndex'])?$_REQUEST['charIndex']:"all";
     
	switch($charIdx){
     	
		case "all":
			null;
			break;
		default:
			$where.=" AND libelle LIKE '".$charIdx."%'";
			break;
	} // switch

	$sql .= $where;


	$ordercolumn = isset($_REQUEST["orderColumn"])?$_REQUEST["orderColumn"]: 'Cr�ation';
	$ordertype = isset($_REQUEST["orderTyp"])?$_REQUEST["orderTyp"]:"D";
	$pagenumber = isset($_REQUEST["pageNumber"])?$_REQUEST["pageNumber"]:0;

	//initialisation de la requ�te
	$lst->Set_Query($sql);
	//chargement de la requ�te
	
	$TIndex['table']="hot_produit";
	$TIndex['idx']="substr(libelle,1,1)";
	$TIndex['condition']="id_hotel=".get_sess_hotel_id();
	$TIndex['char']=$charIdx;

	
	
	$lst->Load_query($ordercolumn,$ordertype,$TIndex); // on charge la requ�te
	$lst->Set_pagenumber($pagenumber);

	$lst->Set_Key("ID",'id');
	$lst->Set_columnType('Cr�ation','DATE');

	$lst->Set_OnClickAction('OpenForm',"?action=VIEW&id_hotel=".$id_hotel);
	$lst->Set_nbLinesPerPage(30);


	//Affichage de la liste
	echo $lst->Render();

	//echo "<p align=\"center\">".$t->link("Retour � l'hotel","hotel.php?action=VIEW&id=".$id_hotel,"button")."</p>";

}

?>
