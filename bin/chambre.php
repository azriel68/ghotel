<?
	require("../includes/inc.php");
	is_logged();

 	if(!is_admin()) {
		entete("Acc�s refus�");
		erreur ("Vous n'�tes pas autoris� � afficher cette page");
		exit();
	}

	entete("Gestion chambres",'online');
	
	if(is_hotel_select()){
		/*menu_admin();*/
	}

	if(isset($_REQUEST['action'])){
		$action=$_REQUEST['action'];
	}
	else{
		$action='LIST';
	}

	$sess_name=isset($_REQUEST['sess_name'])?$_REQUEST['sess_name']:gen_sess_name("chambre");
	$id_hotel=isset($_REQUEST["id_hotel"])?($_REQUEST["id_hotel"]):0;

	$db=new Tdb;

	switch($action){
	  case 'ADD_PRIX':
			$c = & $_SESSION[$sess_name];

			valider($c);
			$c->add_prix();

			fiche($c, $sess_name,$id_hotel, $db);
	  
	   
	     break;
	  case 'DEL_PRIX':
			$c = & $_SESSION[$sess_name];

			valider($c);
			$c->del_prix($_REQUEST['p1']);

			fiche($c, $sess_name,$id_hotel, $db);
	  
	   
	     break;
		case 'SAVE':
			$c = & $_SESSION[$sess_name];

			valider($c);
			enregistrer($db,$c);

			fiche($c, $sess_name,$id_hotel, $db);

			break;

		case 'DELETE':
			$c = & $_SESSION[$sess_name];
			$c->delete($db);

			liste($id_hotel);

			break;
		case 'VIEW':
			$_SESSION[$sess_name]=new TChambre();
			$c = & $_SESSION[$sess_name];
			$c->load($db, $_REQUEST['id']);


			fiche($c,$sess_name);
			break;
		case 'NEW':
			$_SESSION[$sess_name]=new TChambre();
			$c = & $_SESSION[$sess_name];
			$c->id_hotel = get_sess_hotel_id();
			$c->load_categorie($db);

			fiche($c,$sess_name);
			break;
		case 'LIST':
			liste($id_hotel);
			break;
		default:
			erreur("inconnu : ".$action);
	} // switch
	$db->close();

	pied_de_page();


function valider(&$c){



	$c->nb_lit = $_POST['nb_lit'];
	$c->prestation = $_POST['prestation'];
	$c->orientation = $_POST['orientation'];
	$c->situation = $_POST['situation'];
	
	$c->num = $_POST['num'];
	$c->id_categorie = $_POST['id_categorie'];
	$c->id_hotel =get_sess_hotel_id();

	$c->prix_of_categorie = isset($_REQUEST['prix_of_categorie'])?1:0;

  if(!$c->prix_of_categorie){
    $c->prix = _fstring($_POST['prix']);
  
    if(isset($_POST['TPrixSaison'])){

			$TPrixSaison = &$_POST['TPrixSaison'];
			$keys=array_keys($TPrixSaison);
			$nb=count($keys);
			for($i = 0; $i < $nb; $i++){

				$k = $keys[$i];
				$c->TPrixSaison[$k]->set_dtdeb($TPrixSaison[$k]['dt_deb']);
				$c->TPrixSaison[$k]->set_dtfin($TPrixSaison[$k]['dt_fin']);
				$c->TPrixSaison[$k]->prix =_fstring($TPrixSaison[$k]['prix']);

			} // for

		}

  }

	
}
function enregistrer(&$db, &$c){

  if($c->id_categorie==='new'){
    $categorie = new TCategorie;
    $categorie->libelle = $_REQUEST['new_categorie_lib'];
    $categorie->tarif_defaut = _fstring($_REQUEST['new_categorie_prix']);
    $categorie->id_hotel =get_sess_hotel_id();
    $categorie->save($db);
    
    $c->id_categorie = $categorie->id;
    $c->load_categorie($db);
  }


  if($c->id_categorie<=0 && $c->prix_of_categorie){
    erreur("Veuillez sp�cifier la cat�gorie de la chambre");
  }
  else if(($c->prix!="" || $c->prix_of_categorie==1) && $c->num!=""){
  	$c->save($db);
		info("Fiche enregistr�e");
	}
	else{
		erreur("Les champs prix ou cat�gorie ainsi que le num�ro sont obligatoires");
	}


}
function _fiche_prix(&$c, &$form){

	$t=new TTbl;
	$t->beg_tbl('formcadre','100%');

	$nb=count($c->TPrixSaison);

	$t->beg_line('listheader');
	$t->Cell("D�but");
	$t->Cell("Fin");
	$t->Cell("Prix");
	$t->Cell("");
	$t->end_line();

	$class="L1";
	for ($i = 0; $i < $nb; $i++) {
		$p = & $c->TPrixSaison[$i];

		if(!$p->to_delete){
			$t->beg_line($class);
			$t->Cell($form->texte('','TPrixSaison['.$i.'][dt_deb]',$p->get_dtdeb(),12,10));
			$t->Cell($form->texte('','TPrixSaison['.$i.'][dt_fin]',$p->get_dtfin(),12,10));
			$t->Cell($form->texte('','TPrixSaison['.$i.'][prix]',$p->prix,10,255).MONEY);
			$t->Cell($t->link($t->img('ico_xminus.gif'),"javascript:del_prix(".$i.")"));
			$t->end_line();
			$class=($class=="L2")?"L1":"L2";
		}

	} // for
  
	$t->end_tbl();
}

function fiche(&$c,$sess_name){

	$formname="form_chambres";
?>

<link href="../styles/chambre.css" rel="stylesheet" type="text/css">
<script language="javascript" src="../scripts/chambre.js"></script>

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
function add_prix () {
	document.forms['<?=$formname?>'].elements['action'].value='ADD_PRIX';
 	document.forms['<?=$formname?>'].submit();

}
function del_prix (p1) {
	document.forms['<?=$formname?>'].elements['action'].value='DEL_PRIX';
 	document.forms['<?=$formname?>'].elements['p1'].value=p1;
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
		"Fiche chambre"
	,-1,'',2);
	$t->end_line();
	$t->beg_line();
	$t->Cell("Num�ro", 120);
	$t->Cell( $form->texte('','num',$c->num, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Nombre de lit");
	$t->Cell( $form->texte('','nb_lit',$c->nb_lit, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Prestation");
	$t->Cell( $form->texte('','prestation',$c->prestation, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Orientation");
	$t->Cell( $form->texte('','orientation',$c->orientation, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Situation");
	$t->Cell( $form->texte('','situation',$c->situation, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Cat�gorie");
	/*$t->Cell($form->hidden("id_categorie",$c->id_categorie)
	.$form->texteRO("", "lib_categorie", isset($c->TCategorie[$c->id_categorie])?$c->TCategorie[$c->id_categorie]:"", 60)
	.$t->link("choisir","javascript:showPopup('../dlg/lst_categorie.php','$formname','id_categorie;lib_categorie;prix')","lien","","ico_browse.gif")
	);*/
	
	//
	$t->Cell(
    $form->combo('','id_categorie', $c->TCategorie, $c->id_categorie,1,'chambre.show_new_categorie(this.value);','',array('0'=>"-- Choisir une cat�gorie --",'new'=>"-- Nouvelle cat�gorie --"))
    ."
    <div id=\"nouvelle_categorie\">
    <div class=\"titre\">Libell�</div><div class=\"chp\">".$form->texte('','new_categorie_lib','auto',70)."</div>
    <div class=\"titre\">Prix</div><div class=\"chp\">".$form->texte('','new_categorie_prix','auto',30)."</div>
    </div>
    "
  );
	
	
	$t->end_line();

	$t->beg_line();
	$t->Cell( $form->checkbox1('','prix_of_categorie',1,$c->prix_of_categorie,' onClick="chambre.show_tarif(this.checked==false)"') ,-1,'','','right');
	$t->Cell("<label for=\"prix_of_categorie\">Utiliser le tarif d�fini dans la cat�gorie ?</label><br><small>(ou d�finissez les prix ci-apr�s)</small>");
	$t->end_line();

  $t->end_tbl();
  
  echo '<div id="tarif_chambre">';
  $t->beg_tbl('formcadre',800,2,'','center');
	$t->beg_line("listheader");
	$t->Cell(
		"Les tarifs de la chambre"
	,-1,'',2);
	$t->beg_line(null, 'ligne_tarif_global');
	$t->Cell("Tarif en &euro;<br><small> (ou tarif par d�faut)</small>",120);
	$t->Cell( ajout_aide('chambre_tarif').$form->texte('','prix',$c->prix, 30, 255) );
	$t->end_line();
	
  $t->beg_line("listheader", 'ligne_tarif_saison_titre');
	$t->Cell(
		"Les tarifs saisonniers ".$t->link("ajouter","javascript:add_prix()","lien","","ico_xplus.gif")
	,800,'',2);
	$t->end_line();
	$t->beg_line(null, 'ligne_tarif_saison');
	$t->beg_Cell(800,'',2);
	_fiche_prix($c, $form);
	$t->end_cell();
	$t->end_line();

	$t->end_line();

/*	$t->beg_line();
	$t->Cell("Modification");
	$t->Cell($c->get_dtmaj());
	$t->end_line();
	$t->beg_line();
	$t->Cell("Cr�ation");
	$t->Cell($c->get_dtcre());
	$t->end_line();
*/
	$t->end_tbl();
	echo '</div>';

	echo "<p align=\"center\">";
/*	echo $form->bt("Liste","bt_retour"," onClick=\"go_liste()\"");
	echo $form->bt("Valider","bt_valid"," onClick=\"Valider()\"");*/

	echo $t->link("Liste","javascript:go_liste()","button");

	if($c->id>0)echo $t->link("Supprimer","javascript:supprimer()","button_delete");
	
  if(have_droit_sess_groupe()){
    echo $t->link("Valider","javascript:valider()","button_valid");
  }
  else{
    echo get_right_erreur_msg($t->link("Valider","#","button_valid"));  
  }  
  

	echo "</p>";

	echo $form->end_form();
	
	?>
	<script language="javascript">
    chambre=new TChambre;
    chambre.show_tarif(<?=(($c->prix_of_categorie)?'false':'true')?>);
    chambre.show_new_categorie("<?=$c->id_categorie?>");
	</script><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
	<?
	
}

function liste($id_hotel){
	$listname='dblist1';
	$lst=new TListView($listname);

	//titre du r�f�rentiel
	echo "<h1>Liste des chambres</h1>";

	$t=new TTbl;

	echo "<p align=\"center\">";
  
  if(have_droit_sess_groupe()){
    echo $t->link("Ajouter une chambre","?action=NEW","button");
  }
  else{
  
    echo get_right_erreur_msg($t->link("Ajouter une chambre","#","button"));  
  }  
  
  echo "</p>";

	//requ�te
	$sql = "SELECT a.id as 'ID', LPAD(a.num,20,' ') as 'Num�ro', b.libelle as 'Cat�gorie', a.nb_lit as 'Nombre de lits'
	, a.prestation as 'Prestation', a.orientation as 'Orientation', a.situation as 'Situation'
	, a.prix as 'Prix', a.dt_cre as 'Cr�ation'
	FROM hot_chambre a LEFT JOIN hot_categorie b ON (a.id_hotel=b.id_hotel AND a.id_categorie=b.id)
	WHERE a.id_hotel=".get_sess_hotel_id()."";
	
	
	$where="";
	$charIdx=isset($_REQUEST['charIndex'])?$_REQUEST['charIndex']:"all";
     
	switch($charIdx){
     	case "all":
     		null;
			break;
			
		default:
			$where.=" AND a.num LIKE '$charIdx%' ";
			break;
	} // switch

	$sql .= $where;

	
	
	$ordercolumn = isset($_REQUEST["orderColumn"])?$_REQUEST["orderColumn"]: 'Num�ro';
	$ordertype = isset($_REQUEST["orderTyp"])?$_REQUEST["orderTyp"]:"A";
	$pagenumber = isset($_REQUEST["pageNumber"])?$_REQUEST["pageNumber"]:0;

	//initialisation de la requ�te
	$lst->Set_Query($sql);
	//chargement de la requ�te
	
	$TIndex['table']="hot_chambre";
	$TIndex['idx']="substr(num,1,1)";
	$TIndex['condition']="id_hotel=".get_sess_hotel_id();
	$TIndex['char']=$charIdx;
	
	$lst->Load_query($ordercolumn,$ordertype, $TIndex); // on charge la requ�te
	$lst->Set_pagenumber($pagenumber);

	$lst->Set_Key("ID",'id');
	$lst->Set_columnType('Cr�ation','DATE');
  $lst->Set_hiddenColumn('ID',true);

	//$lst->Set_hiddenColumn('numeroOrder',true);
	$lst->Set_OnClickAction('OpenForm',"?action=VIEW");
	$lst->Set_nbLinesPerPage(30);

	//Affichage de la liste
	echo $lst->Render();

}
?>
