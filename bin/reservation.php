<?
	require("../includes/inc.php");

	is_logged();

	

	if(is_hotel_select() && is_not_popup()){
		entete("Gestion reservations",'online');
	}
	else{
    entete("Gestion reservations",'popup');
  }

	if(isset($_REQUEST['actionform'])){
		$actionform=$_REQUEST['actionform'];
	} else if(isset($_REQUEST['action'])){
		$actionform=$_REQUEST['action'];
	} else {
		$actionform='LIST';
	}

	$sess_name=isset($_REQUEST['sess_name'])?$_REQUEST['sess_name']:gen_sess_name("resa");
	
	$db=new Tdb;
	switch($actionform){
		case 'ADD_PRODUIT':
			$resa = & $_SESSION[$sess_name];
			valider($resa);
			$resa->add_produit($db, $_REQUEST['p1']);
			fiche($resa, $sess_name);
			break;
		case 'DEL_PRODUIT':
			$resa = & $_SESSION[$sess_name];
			valider($resa);
			$resa->del_produit($db, $_REQUEST['p1']);
			fiche($resa, $sess_name);

			break;
		case 'SAVE':
			$resa = & $_SESSION[$sess_name];

			valider($resa);
			
			$resa->get_prix();
			$resa->calcule_prix();
			$resa->calcule_prix_annexe();
			$res = enregistrer($db,$resa);

			if ($res && !is_not_popup()) {
				fermer_popup($resa);
			} else {
				fiche($resa, $sess_name);
			}
			break;

		case 'DELETE':
			$resa = & $_SESSION[$sess_name];
			$resa->delete($db);

			if (!is_not_popup()) {
				fermer_popup($resa);
			} else {
				liste();
			}

			break;
		case 'VIEW':
			$_SESSION[$sess_name]=new TReservation();
			$resa = & $_SESSION[$sess_name];
			$resa->load($db, $_REQUEST['id'],true,true,true);
      
			fiche($resa,$sess_name);
			break;
		case 'SET_PRIX' :
      $resa = & $_SESSION[$sess_name];
      
      valider($resa);
      
      $resa->load_chambre($db);
      $resa->get_prix();
//print_r($resa->TPrix);
      $resa->get_ages();
      fiche($resa,$sess_name);
      break;	
		case 'NEW':
			$_SESSION[$sess_name]=new TReservation();
			$resa = & $_SESSION[$sess_name];
			if (isset($_REQUEST['dt'])) {
				$resa->dt_deb = $_REQUEST['dt'];
				$resa->dt_fin = $_REQUEST['dt'];
			}
			if (isset($_REQUEST['client'])) {
				$resa->id_client = $_REQUEST['client'];
			}
      if (isset($_REQUEST['id_chambre'])) {
				$resa->id_chambre = $_REQUEST['id_chambre'];
				$resa->load_chambre($db);
				$resa->get_prix();
			}
			
			$resa->load_client($db);
			$resa->load_chambre($db);

			fiche($resa,$sess_name);
			break;
		case 'LIST':
			liste();
			break;
		default:
			erreur("inconnu : ".$actionform);
	} // switch
	$db->close();

  if(is_hotel_select() && is_not_popup()){
	 pied_de_page();
	}
	else{
   pied_de_page('popup');
	
  }

function fermer_popup (&$resa) {
	?><script language="JavaScript" type="text/javascript">
  	window.opener.refresh_line(<?=$resa->id_chambre?>);
  	<?
  		if($resa->old_id_chambre>0 && $resa->old_id_chambre!=$resa->id_chambre){
  			?>window.opener.refresh_line(<?=$resa->old_id_chambre?>);<?
  		}
  	?>
  	setTimeout('window.close()',1000);
	</script><?

}
function valider(&$resa){

  $resa->id_hotel =get_sess_hotel_id();

	$resa->id_client = $_POST['id_client'];
	$resa->nom_client =  $_POST['nom_client'];
	$resa->id_chambre = $_POST['id_chambre'];
	$resa->chambre->num = $_POST['lib_chambre'];
	$resa->etat=$_POST['etat'];
	$resa->prix=_fstring($_POST['prix']);
	$resa->acompte=$_POST['acompte'];
/*	$resa->nb_personne_age1= $_POST['nb_personne_age1'];
	$resa->nb_personne_age2=$_POST['nb_personne_age2'];
	$resa->nb_personne_age3=$_POST['nb_personne_age3'];*/
	$resa->nb_animaux=$_POST['nb_animaux'];

/*
	$resa->mt_taxe_sejour=$_POST['mt_taxe_sejour'];
	$resa->mt_personne_suppl=$_POST['mt_personne_suppl'];
	$resa->mt_animaux=$_POST['mt_animaux'];
*/

  $resa->is_prix_negoce = isset($_REQUEST['is_prix_negoce'])?1:0;

  $resa->frais_resa =_fstring($_POST['frais_resa']);

	$resa->set_dtdeb($_POST['dt_deb']);
	$resa->set_dtfin($_POST['dt_fin']);

	if(isset($_POST['TLienProduit'])){

		$TLienProduit = &$_POST['TLienProduit'];
		$keys=array_keys($TLienProduit);
		$nb=count($keys);
		for($i = 0; $i < $nb; $i++){

			$k = $keys[$i];
			$resa->TLienProduit[$k]->quantite =$TLienProduit[$k]['qte'];
			$resa->TLienProduit[$k]->prix =$TLienProduit[$k]['prix'];

		} // for

	}
	if(isset($_POST['TAge'])){
    
    foreach ($_POST['TAge'] as $key=>$value) {
   // print $value['nb']."<br />"; 
    	$resa->TAge[$key]['nb'] = $value['nb'];
    }
  }
}
function enregistrer(&$db, &$resa){
//$db->db->debug=true;

	if($resa->id_client!=0 && $resa->id_chambre!=0 && $resa->dt_deb!="" && $resa->dt_fin!=""){
		if ($resa->check_date_resa($db)) {
			$resa->save($db);
			info("Fiche enregistr�e");
			return true;
		} else {
			erreur("La chambre ".$resa->chambre->num." est d�ja reserv�e pour les dates choisies");
		}
	} else {
		erreur("Tous les champs sont obligatoires !");
//		erreur("CL : ".$resa->id_client." - CH : ".$resa->id_chambre." - DT DEB : ".$resa->dt_deb." - DT FIN : ".$resa->dt_fin);
	}

	return false;
}

function _fiche_produit(&$resa, &$form){

	$t=new TTbl;
	$t->beg_tbl('formcadre','100%');

	$nb=count($resa->TLienProduit);

	$t->beg_line('listheader');
	$t->Cell("Nom");
	$t->Cell("");
	$t->Cell("Qt�");
	$t->Cell("Prix");
	$t->Cell("");
	$t->end_line();

	$class="L1";
	for ($i = 0; $i < $nb; $i++) {
		$l = & $resa->TLienProduit[$i];

		if(!$l->to_delete){
			$t->beg_line($class);
			$t->Cell($l->produit->libelle);
			$t->Cell($l->produit->description);
			$t->Cell($form->texte('','TLienProduit['.$i.'][qte]',$l->quantite,3,255));
			$t->Cell($form->texte('','TLienProduit['.$i.'][prix]',$l->prix,10,255)." �");
			$t->Cell($t->link($t->img('ico_xminus.gif'),"javascript:del_produit(".$i.")"));
			$t->end_line();
			$class=($class=="L2")?"L1":"L2";
		}

	} // for

	$t->end_tbl();
}


function fiche(&$resa,$sess_name){

	$formname="form_reservations";

?>
<script language="javascript">
function go_liste(){
 	document.forms['<?=$formname?>'].elements['actionform'].value='LIST';
 	document.forms['<?=$formname?>'].submit();
}
function valider(){
 	document.forms['<?=$formname?>'].elements['actionform'].value='SAVE';
 	document.forms['<?=$formname?>'].submit();
}
function devis(id){
/* 	document.forms['<?=$formname?>'].elements['actionform'].value='NEW';
 	document.forms['<?=$formname?>'].elements['p1'].value=id;
 	document.forms['<?=$formname?>'].action='facture.php?type=DEVIS';
 	document.forms['<?=$formname?>'].submit();*/
 	
 	document.location.href="facture.php?type=DEVIS&p1="+id+"&action=NEW";
}
function facture(id){
 	/*document.forms['<?=$formname?>'].elements['actionform'].value='NEW';
 	document.forms['<?=$formname?>'].elements['p1'].value=id;
 	document.forms['<?=$formname?>'].action='facture.php';
 	document.forms['<?=$formname?>'].submit();*/
 	document.location.href="facture.php?p1="+id+"&action=NEW";
}
function supprimer(){
	if(window.confirm('Voulez-vous vraiment supprimer cette fiche ?')){
	document.forms['<?=$formname?>'].elements['actionform'].value='DELETE';
 	document.forms['<?=$formname?>'].submit();
	}
}
function add_produit (p1) {
	document.forms['<?=$formname?>'].elements['actionform'].value='ADD_PRODUIT';
 	document.forms['<?=$formname?>'].elements['p1'].value=p1;
 	document.forms['<?=$formname?>'].submit();

}
function del_produit (p1) {
	document.forms['<?=$formname?>'].elements['actionform'].value='DEL_PRODUIT';
 	document.forms['<?=$formname?>'].elements['p1'].value=p1;
 	document.forms['<?=$formname?>'].submit();

}
function show_add_produit(){
  <?
    if(is_not_popup()){
      ?>showPopup('../dlg/lst_produit.php','<?=$formname?>','id_produit',600);<?
    }
    else{
      ?>
      if (document.body) {
        var larg = (document.body.clientWidth);
      }
      else {
        var larg = (window.innerWidth);
      }
      showPopup('../dlg/lst_produit.php','<?=$formname?>','id_produit',600,800,larg/2);<?
    }
  ?>
	
}
function set_prix(){

  document.forms['<?=$formname?>'].elements['actionform'].value='SET_PRIX';
 	document.forms['<?=$formname?>'].submit();
}
function set_dt_fin(){
  
    s_dtdeb = document.forms['<?=$formname?>'].elements['dt_deb'].value;
  
    day = s_dtdeb.substring(0,2);
		month = s_dtdeb.substring(3,5);
		year = s_dtdeb.substring(6,10);
		d_deb = new Date(year, month, day);
		/*d_deb.setDate(day);
		d_deb.setMonth(month);
		d_deb.setFullYear(year);*/ 
		
		s_dtfin = document.forms['<?=$formname?>'].elements['dt_fin'].value;
		day = s_dtfin.substring(0,2);
		month = s_dtfin.substring(3,5);
		year = s_dtfin.substring(6,10);
	
		d_fin = new Date(year, month, day);
		/*d_fin.setDate(day);
		d_fin.setMonth(month);
		d_fin.setFullYear(year); 
		*/
		if((d_fin.getTime()-d_deb.getTime())<0){
		//  alert(d_fin.getTime()+"-"+d_deb.getTime());
        document.forms['<?=$formname?>'].elements['dt_fin'].value = s_dtdeb;
    }

}

</script>
<?
	$form=new TForm($_SERVER['PHP_SELF'], $formname);
	echo $form->hidden("sess_name", $sess_name);
	echo $form->hidden("actionform", "SAVE");
	echo $form->hidden("p1", "");

$age1 = $_SESSION[SESS_HOTEL]->get_parameter("taxe_sejour_age1");
$age2 = $_SESSION[SESS_HOTEL]->get_parameter("taxe_sejour_age2");

	is_popup_var();

	$t=new TTbl;
	$r = new TRequete;

	$t->beg_tbl('formcadre',is_not_popup()?800:'100%',2,'','center');
	$t->beg_line("listheader");
	$t->Cell(
		"Fiche reservation"
	,-1,'',2);
	$t->end_line();
	
	
	$t->beg_line();
	$t->Cell("Chambre");
	$t->Cell(
		$form->hidden('id_chambre', $resa->id_chambre)
		.$form->texteRO('','lib_chambre',$resa->chambre->num, 40)
		.$t->link("choisir","javascript:showPopup('../dlg/lst_chambre.php','$formname','id_chambre;lib_chambre;prix',800)","lien","","ico_browse.gif")
	);

	$t->end_line();
	$t->beg_line();
	$t->Cell("Client");
	$t->Cell(
		$form->hidden('id_client', $resa->id_client)
		.$form->texteRO('','nom_client',$resa->nom_client, 40)
		.$t->link("choisir","javascript:showPopup('../dlg/lst_client.php','$formname','id_client;nom_client')","lien","","ico_browse.gif")
	);

	$t->end_line();
	
	$t->beg_line();
	$t->Cell("Etat");
	$t->Cell( $form->combo('','etat',$resa->TEtat, $resa->etat) );
	$t->end_line();


	$t->beg_line();
	$t->Cell("Date arriv�e");
	$t->Cell( $form->texte('','dt_deb',$resa->get_dtdeb(), 12, 10 /*, "","text", array('js_flatCallback'=>"set_dt_fin")*/  ) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Date d�part");
	$t->Cell( $form->texte('','dt_fin',$resa->get_dtfin(), 12, 10, ' onMouseOver="set_dt_fin()" ') );
	$t->end_line();

	$t->beg_line();
	$t->Cell("Prix unitaire / Montant total HT");
	$t->Cell( $form->texte('','prix',_f_prix($resa->prix), 12, 10) ." / "._f_prix($resa->montant)
  ."<span style=\"font-size:9px;\"> (soit ".$resa->get_nbJour()." nuit�e(s), d�part r�el le ".$resa->get_dtfin(true).")</span>"
   );
	$t->end_line();
	
	$t->beg_line();
	$t->Cell("Frais de r�servation");
	$t->Cell( $form->texte('','frais_resa',_f_prix($resa->frais_resa), 12, 10) );
	$t->end_line();
	
  $t->beg_line();
	$t->Cell( $form->checkbox1("","is_prix_negoce", 1, $resa->is_prix_negoce),-1,'','','right' );
	$t->Cell( "Conserver les prix saisis (n�gociation) ? "
  ."<span style=\"font-size:9px;\">Sinon le prix est celui indiqu� dans la chambre</span>"
  );
	$t->end_line();
	
	$t->beg_line();
	$t->Cell("Accompte vers�");
	$t->Cell( $form->texte('','acompte',$resa->acompte, 12, 10) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Nombre d'animaux / Frais");
	$t->Cell( $form->texte('','nb_animaux',$resa->nb_animaux, 12, 10)." / "._f_prix($resa->mt_animaux)."<span style=\"font-size:9px;\"> (soit ".$resa->chambre->categorie->montant_animaux."&euro; par animal par jour)</span>" );
	$t->end_line();
	/*
	$t->beg_line();
	$t->Cell("Nombre de personne ayant de moins de $age1 ans");
	$t->Cell( $form->texte('','nb_personne_age1',$resa->nb_personne_age1, 12, 10) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Nombre de personne ayant entre $age1 et $age2 ans");
	$t->Cell( $form->texte('','nb_personne_age2',$resa->nb_personne_age2, 12, 10) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Nombre de personne ayant plus de $age2 ans");
	$t->Cell( $form->texte('','nb_personne_age3',$resa->nb_personne_age3, 12, 10) );
	$t->end_line();*/
	
	_fiche_personne($resa->TAge,$sess_name);
	
	$t->beg_line();
	$t->Cell("Montant total de la taxe de s�jour");
	$t->Cell(_f_prix($resa->get_taxe_sejour()));
	$t->end_line();
	$t->beg_line();
	$t->Cell("Frais suppl�mentaires");
	$t->Cell(_f_prix($resa->get_supplement()));
	$t->end_line();
	

	
	$t->beg_line();
	$t->Cell("Modification");
	$t->Cell($resa->get_dtmaj());
	$t->end_line();
	$t->beg_line();
	$t->Cell("Cr�ation");
	$t->Cell($resa->get_dtcre());
	$t->end_line();
	$t->beg_line("listheader");
	$t->Cell(
		"Les produits ".$t->link("ajouter","javascript:show_add_produit()","lien2","","ico_xplus.gif")
	,-1,'',2);
	$t->end_line();
	$t->beg_line();
	$t->beg_Cell(-1,'',2);
	_fiche_produit($resa, $form);
	$t->end_cell();
	$t->end_line();

	$t->end_tbl();

	echo "<p align=\"center\">";
/*	echo $form->bt("Liste","bt_retour"," onClick=\"go_liste()\"");
	echo $form->bt("Valider","bt_valid"," onClick=\"Valider()\"");*/

	if (is_not_popup())
		echo $t->link("Liste","javascript:go_liste()","button");

	if($resa->id>0)echo $t->link("Supprimer","javascript:supprimer()","button_delete");
	//echo $t->link("Valider","javascript:valider()","button_valid");
	if(have_droit_sess_groupe()){
    echo $t->link("Valider","javascript:valider()","button_valid");
  }
  else{
    echo get_right_erreur_msg($t->link("Valider","#","button_valid"));  
  }  
  
	if ($resa->id <> 0) {
		echo $t->link("Cr�er devis","javascript:devis(".$resa->id.")","button_valid");
		echo $t->link("Facturer","javascript:facture(".$resa->id.")","button_valid");
	}

	echo "</p>";

	echo $form->end_form();
}
function _fiche_personne(&$TAge,$sess_name){

  $t=new TTbl;
  $form=new TForm;
  
  $t->beg_line();
  $t->Cell("<b>Nombre de personnes :</b>",-1,'',2);
  $t->end_line();
  
  foreach ($TAge as $key=>$age) {
  	
  	$t->beg_line();
  	if(is_null($age['min'])){
      $titre = "De moins de ".$age['max']." an(s) ";
    }
    else if(is_null($age['max'])){
      $titre = "De ".$age['min']." ans ou plus";
    }
    else{
      $titre = "De ".$age['min']." ans et moins de ".$age['max']." ans";
    }
  	$t->Cell( $titre );
  	$t->Cell( $form->texte("","TAge[$key][nb]",$age['nb'],3) );
  	$t->end_line();
  	
  }

}
function liste(){
	$listname='dblist1';
	$lst=new TListView($listname);

	//titre du r�f�rentiel
	echo "<h1>Liste des reservations</h1>";

	$t=new TTbl;

	echo "<p align=\"center\">";
  if(have_droit_sess_groupe()){
    echo $t->link("Ajouter une r�servation","?actionform=NEW","button");
  }
  else{
    echo get_right_erreur_msg($t->link("Ajouter une r�servation","#","button"));  
  }  
  echo "</p>";
  

  
	$where="";
	$charIdx=isset($_REQUEST['charIndex'])?$_REQUEST['charIndex']:"all";
	switch($charIdx){
		case "all":
			null;
			break;
		default:
			$where.=" AND a.etat = '".$charIdx."'";
			break;
	} // switch

	//requ�te
	$sql = "SELECT a.id as 'ID', a.etat as 'Etat', b.num as 'Chambre', c.civ as 'Civilit�', CONCAT(c.nom,' ',c.prenom) as 'Client'
	, a.dt_deb as 'Date arriv�e', a.dt_fin as 'Date d�part', a.dt_cre as 'Cr�ation'
	FROM hot_reservation a LEFT JOIN hot_chambre b ON a.id_chambre=b.id
			LEFT JOIN hot_client c ON a.id_client=c.id
	WHERE b.id_hotel=".get_sess_hotel_id()." ".$where;

	$ordercolumn = isset($_REQUEST["orderColumn"])?$_REQUEST["orderColumn"]: 'Cr�ation';
	$ordertype = isset($_REQUEST["orderTyp"])?$_REQUEST["orderTyp"]:"D";
	$pagenumber = isset($_REQUEST["pageNumber"])?$_REQUEST["pageNumber"]:0;

  $resa=new TReservation();
	$TIndex['table']="hot_reservation";
	$TIndex['idx']="etat";
	$TIndex['char']=$charIdx;
  $TIndex['TTrans'] = &$resa->TEtat;
  $TIndex['condition']="id_hotel=".get_sess_hotel_id();
  
	//initialisation de la requ�te
	$lst->Set_Query($sql);
	//chargement de la requ�te
	$lst->Load_query($ordercolumn,$ordertype,$TIndex); // on charge la requ�te
	$lst->Set_pagenumber($pagenumber);

	$lst->Set_Key("ID",'id');
	$lst->Set_columnType('Cr�ation','DATE');
	$lst->Set_columnType('Date d�part','DATE');
	$lst->Set_columnType('Date arriv�e','DATE');
  $lst->Set_hiddenColumn('ID',true);
	$lst->Str_trans('Etat',$resa->TEtat);
	
	$lst->Set_OnClickAction('OpenForm',"?actionform=VIEW");
	$lst->Set_nbLinesPerPage(30);


	//Affichage de la liste
	echo $lst->Render();

}
?>
