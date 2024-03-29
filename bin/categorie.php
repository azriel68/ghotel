<?
	require("../includes/inc.php");
	is_logged();

	if(!is_admin()) {
		entete("Acc�s refus�");
		erreur ("Vous n'�tes pas autoris� � afficher cette page");
		exit();
	}

	entete("Gestion cat�gories",'online');

	
	if(is_hotel_select() && is_admin()){
		menu_admin();
	}

	if(isset($_REQUEST['action'])){
		$action=$_REQUEST['action'];
	}
	else{
		$action='LIST';
	}

	$sess_name=isset($_REQUEST['sess_name'])?$_REQUEST['sess_name']:gen_sess_name("cat");
	$id_hotel=isset($_REQUEST["id_hotel"])?($_REQUEST["id_hotel"]):get_sess_hotel_id();

	$db=new Tdb;
	switch($action){
	  case 'PRIX_IN_CHAMBRE':
    
      $cat = & $_SESSION[$sess_name];
      valider($cat);
      enregistrer($db, $cat);
    
      appliquer_prix($db, $cat);
    
      fiche($cat, $sess_name);
      break;	
	  case 'ADD_PRIX':
			$cat = & $_SESSION[$sess_name];

			valider($cat);
			$cat->add_prix();

			fiche($cat, $sess_name);
	  
	   
	     break;
	  case 'DEL_PRIX':
			$cat = & $_SESSION[$sess_name];

			valider($cat);
			$cat->del_prix($_REQUEST['p1']);

			fiche($cat, $sess_name);
	  
	   
	     break;
	
		case 'SAVE':
			$cat = & $_SESSION[$sess_name];

			valider($cat);
			enregistrer($db,$cat);

			fiche($cat, $sess_name);

			break;

		case 'DELETE':
			$cat = & $_SESSION[$sess_name];
			$cat->delete($db);

			liste($id_hotel);

			break;
		case 'VIEW':
			$_SESSION[$sess_name]=new TCategorie();
			$cat = & $_SESSION[$sess_name];
			$cat->load($db, $_REQUEST['id']);


			fiche($cat,$sess_name,$id_hotel);
			break;
		case 'NEW':
			$_SESSION[$sess_name]=new TCategorie();
			$cat = & $_SESSION[$sess_name];

			fiche($cat,$sess_name,$id_hotel);
			break;
		case 'LIST':
			liste($id_hotel);
			break;
		default:
			erreur("inconnu : ".$action);
	} // switch
	$db->close();

	pied_de_page();


function valider(&$cat){

  $trans=array("\\"=>"");

	$cat->id_hotel = get_sess_hotel_id();
	$cat->libelle = strtr($_POST['libelle'], $trans);
	$cat->type = $_POST['type'];
	$cat->prestation = strtr($_POST['prestation'], $trans);
	$cat->definition = strtr($_POST['definition'], $trans);
	$cat->tarif_defaut = $_POST['tarif_defaut'];
	$cat->nb_limite_personne = $_POST['nb_limite_personne'];
	$cat->montant_personne = _fstring($_POST['montant_personne']);
	$cat->montant_animaux = _fstring($_POST['montant_animaux']);
	
	$cat->frais_resa = _fstring($_POST['frais_resa']);
	
	if(isset($_POST['TPrixSaison'])){

		$TPrixSaison = &$_POST['TPrixSaison'];
		$keys=array_keys($TPrixSaison);
		$nb=count($keys);
		for($i = 0; $i < $nb; $i++){

			$k = $keys[$i];
			$cat->TPrixSaison[$k]->set_dtdeb($TPrixSaison[$k]['dt_deb']);
			$cat->TPrixSaison[$k]->set_dtfin($TPrixSaison[$k]['dt_fin']);
			$cat->TPrixSaison[$k]->prix =$TPrixSaison[$k]['prix'];

		} // for

	}
	if(isset($_POST['TAge'])){
    
    foreach ($_POST['TAge'] as $key=>$value) {
    	$cat->TAge[$key]['percent'] = $value['percent'];
      $cat->TAge[$key]['montant'] = _fstring($value['montant']);
      if(isset($value['min'])) $cat->TAge[$key]['min'] = $value['min'];
      if(isset($value['max'])) $cat->TAge[$key]['max'] = $value['max'];
    }
  
  
  }
	
	
}

function appliquer_prix(&$db, &$cat){

  $r=new TRequete;
  $TChambre = $r->liste_toute_chambre_par_categorie($db, $cat->id);
  //$db->db->debug=true;
  $nb=count($TChambre);
  for ($i=0; $i<$nb; $i++) {
   	
   	$c = new TChambre;
   	$c->load($db, $TChambre[$i]);
   	
   	_appliquer_prix_chambre($db, $cat, $c);


    
    $c->save($db);   	
  }
  
  info("Ces prix ont �t� appliqu� sur les chambres de la cat�gorie");
}
function _appliquer_prix_chambre(&$db, &$cat, &$c){

  

  $c->delete_all_prixsaison();
  
  $nb=count($cat->TPrixSaison);
  for ($i=0; $i<$nb; $i++) {
   	
   	  $iPrix = $c->add_prix();
   	  $c->TPrixSaison[$iPrix]->set_dtdeb( $cat->TPrixSaison[$i]->get_dtdeb() );
   	  $c->TPrixSaison[$iPrix]->set_dtfin( $cat->TPrixSaison[$i]->get_dtfin() );
   	  $c->TPrixSaison[$iPrix]->prix = $cat->TPrixSaison[$i]->prix;
  
    /*  print_r($c->TPrixSaison);*/
  
  }
  
  
}


function enregistrer(&$db, &$cat){

	if($cat->libelle!="" && $cat->tarif_defaut!=""){
		$cat->save($db);
		info("Fiche enregistr�e");
	}
	else{
		erreur("Tous les champs sont obligatoires");
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
			$t->Cell($form->texte('','TPrixSaison['.$i.'][prix]',$p->prix,10,255)." &euro;");
			$t->Cell($t->link($t->img('ico_xminus.gif'),"javascript:del_prix(".$i.")"));
			$t->end_line();
			$class=($class=="L2")?"L1":"L2";
		}

	} // for
  
	$t->end_tbl();
}


function fiche(&$cat,$sess_name){

	$formname="form_categories";
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
function add_prix () {
	document.forms['<?=$formname?>'].elements['action'].value='ADD_PRIX';
 	document.forms['<?=$formname?>'].submit();

}
function del_prix (p1) {
	document.forms['<?=$formname?>'].elements['action'].value='DEL_PRIX';
 	document.forms['<?=$formname?>'].elements['p1'].value=p1;
 	document.forms['<?=$formname?>'].submit();

}
function apply_prix(){
  if(window.confirm('Appliquer ces prix sur toutes les chambres de la cat�gorie ?')){
  
    document.forms['<?=$formname?>'].elements['action'].value='PRIX_IN_CHAMBRE';
 	  document.forms['<?=$formname?>'].submit();
  
  }

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
		"Fiche cat�gories"
	,-1,'',2);
	$t->end_line();
	$t->beg_line();
	$t->Cell("Libelle");
	$t->Cell( $form->texte('','libelle',$cat->libelle, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Type");
	$t->Cell( $form->texte('','type',$cat->type, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Prestation");
	$t->Cell( $form->texte('','prestation',$cat->prestation, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("D�finition");
	$t->Cell( $form->texte('','definition',$cat->definition, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Tarif");
	$t->Cell( $form->texte('','tarif_defaut',$cat->tarif_defaut, 80, 255) );
	$t->end_line();
	
	
	$t->beg_line();
	$t->Cell("Modification");
	$t->Cell($cat->get_dtmaj());
	$t->end_line();
	$t->beg_line();
	$t->Cell("Cr�ation");
	$t->Cell($cat->get_dtcre());
	$t->end_line();
	$t->beg_line("listheader");
	$t->Cell("Suppl�ments",-1,'',2);
	$t->end_line();
	$t->beg_line();
	$t->Cell("Au del� de (strictement sup�rieur)");
	$t->Cell( $form->texte('','nb_limite_personne',$cat->nb_limite_personne, 3, 10)." personne(s)" );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Montant par personne suppl�mentaire");
	$t->Cell( 
    $form->texte('','montant_personne',_f_prix($cat->montant_personne), 8, 10) 
  );
	$t->end_line();
	
	_fiche_personne($cat,$sess_name);
	
	$t->beg_line();
	$t->Cell("Montant par animal");
	$t->Cell( $form->texte('','montant_animaux',_f_prix($cat->montant_animaux), 8, 10) );
	$t->end_line();

  $t->beg_line();
	$t->Cell("Frais de r�servation");
	$t->Cell( $form->texte('','frais_resa',_f_prix($cat->frais_resa), 8, 10) );
	$t->end_line();


  $t->beg_line("listheader");
	$t->Cell(
		"Les tarifs saisonniers ".$t->link("ajouter","javascript:add_prix()","lien","","ico_xplus.gif")
	,-1,'',2);
	$t->end_line();
	$t->beg_line();
	$t->beg_Cell(-1,'',2);
	_fiche_prix($cat, $form);
	$t->end_cell();
	$t->end_line();

	

	$t->end_tbl();

	echo "<p align=\"center\">";
/*	echo $form->bt("Liste","bt_retour"," onClick=\"go_liste()\"");
	echo $form->bt("Valider","bt_valid"," onClick=\"Valider()\"");*/

	echo $t->link("Liste","javascript:go_liste()","button");

	if($cat->id>0)echo $t->link("Supprimer","javascript:supprimer()","button_delete");
	echo $t->link("Valider","javascript:valider()","button_valid");

  if($cat->id>0){
    echo "&nbsp;&nbsp;&nbsp;";
    echo $t->link("Appliquer ces prix","javascript:apply_prix()","button_valid");
  }
	

	echo "</p>";

	echo $form->end_form();
}
function _fiche_personne(&$cat,$sess_name){

  $t=new TTbl;
  $form=new TForm;
  
  $TAge = & $cat->TAge;
  foreach ($TAge as $key=>$age) {
  	
  	if(isset($age['hidden']) && $age['hidden']==true){
  	 null;
  	}
  	else {
      $t->beg_line();
    	if(is_null($age['min'])){
        $titre = "Moins de ".$form->texte('',"TAge[$key][max]",$age['max'],3)." an(s)";
      }
      else if(is_null($age['max'])){
        $titre = "De ".$form->texte('',"TAge[$key][min]",$age['min'],3)." ans ou plus";
      }
      else{
        $titre = "De ".$form->texte('',"TAge[$key][min]",$age['min'],3)." et moins de ".$form->texte('',"TAge[$key][max]",$age['max'],3)." ans";
      }
    	$t->Cell( $titre );
    	$t->Cell( $form->texte("","TAge[$key][percent]",$age['percent'],3)." % ou " 
        .$form->texte("","TAge[$key][montant]",$age['montant'],3,20)." �"
      );
    	$t->end_line();
    }
  	
  }

}
function liste($id_hotel){
	$listname='dblist1';
	$lst=new TListView($listname);

	//titre du r�f�rentiel
	echo "<h1>Liste des cat�gories</h1>";

	$t=new TTbl;

	echo "<p align=\"center\">".$t->link("Ajouter une cat�gorie","?action=NEW","button")."</p>";


	//requ�te
	$sql = "SELECT id as 'ID', libelle as 'Libell�', type as 'Type'
	,prestation as 'Prestation', definition as 'D�finition', tarif_defaut as 'Tarif'
	,dt_cre as 'Cr�ation'
	FROM hot_categorie
	WHERE id_hotel=".$id_hotel.""; 
	
	$where="";
	$charIdx=isset($_REQUEST['charIndex'])?$_REQUEST['charIndex']:"all";
	switch($charIdx){
    case "all":
			null;
			break;
		default:
			$where.=" AND type='".$charIdx."'";
			break;
	} // switch
	
	$sql .= $where;


	$ordercolumn = isset($_REQUEST["orderColumn"])?$_REQUEST["orderColumn"]: 'Cr�ation';
	$ordertype = isset($_REQUEST["orderTyp"])?$_REQUEST["orderTyp"]:"D";
	$pagenumber = isset($_REQUEST["pageNumber"])?$_REQUEST["pageNumber"]:0;

 	$TIndex['table']="hot_categorie";
	$TIndex['idx']="type";
	$TIndex['condition']="id_hotel=".get_sess_hotel_id();
	$TIndex['char']=$charIdx;


	//initialisation de la requ�te
	$lst->Set_Query($sql);
	//chargement de la requ�te
	$lst->Load_query($ordercolumn,$ordertype,$TIndex); // on charge la requ�te
	$lst->Set_pagenumber($pagenumber);

	$lst->Set_Key("ID",'id');
	$lst->Set_columnType('Cr�ation','DATE');

	$lst->Set_OnClickAction('OpenForm',"?action=VIEW&id_hotel=".$id_hotel);
	$lst->Set_nbLinesPerPage(30);


	//Affichage de la liste
	echo $lst->Render();

//	echo "<p align=\"center\">".$t->link("Retour � l'hotel","hotel.php?action=VIEW&id=".$id_hotel,"button")."</p>";

}
?>
