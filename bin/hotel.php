<?
	require("../includes/inc.php");

	is_logged();

	if(isset($_REQUEST['action'])){
		$action=$_REQUEST['action'];
	}
	else{
		$action='LIST';
	}

	$sess_name=isset($_REQUEST['sess_name'])?$_REQUEST['sess_name']:gen_sess_name("hotel");

  
	$db=new Tdb;
	
  switch($action){
    case 'VIEW':
      unset_session('hotel');
			
			$_SESSION[$sess_name]=new THotel();
			$h = & $_SESSION[$sess_name];
			$h->load($db, $_REQUEST['id']);

			$_SESSION[SESS_HOTEL] = $h;
			
      break;
  }
  

	if(!isset($_REQUEST['url_go']))entete("Gestion hotels" 
      , (is_hotel_select() && get_hotel_access())?'online':'offline'
  );


	switch($action){
		case 'SAVE':
			$h = & $_SESSION[$sess_name];
			
			valider($h);
			if(enregistrer($db,$h)){
        /*if(is_hotel_select()){
  				menu();
  			} else { menu_off(); }*/
  			?>
			<div class="information">
			F�licitation, vous venez d'enregistrer les informations concernant votre h�tel. 
      Vous pouvez maintenant le g�rer	� l'aide des boutons ci-dessus.
			</div>
			<?
      }

			fiche($h, $sess_name);

			break;

		case 'DELETE':
			$h = & $_SESSION[$sess_name];
			$h->delete($db);
			
			/*if(is_hotel_select()){
				menu();
			} else { menu_off(); } */
			
			liste($db);

			break;
		case 'VIEW':
			

      if(get_hotel_access()) {
        if(isset($_REQUEST['url_go'])){
          header("location:".$_REQUEST['url_go']);
          exit();
        }
      
        /*menu();
        menu_admin(false);*/
			  fiche($h,$sess_name);
      }
      else{
        /*menu_off();*/
      
        erreur("Vous n'avez pas acc�s � cet h�tel");
        $t=new TTbl;
        print "<p align=\"center\">";
        print $t->link("Retour au choix de l'h�tel", $_SERVER['PHP_SELF']."?action=LIST","button");
        print "</p>";
      }

			
			break;
		case 'NEW':
			$_SESSION[$sess_name]=new THotel();
			$h = & $_SESSION[$sess_name];
			
			/*
			if(is_hotel_select()){
				menu();
			} else { menu_header(); }*/
			
			?>
			<div class="information">
			Une fois votre h�tel cr��, vous pourrez ajouter rapidement et facilement 
      vos chambres, cat�gorie, produits et clients et commencer � utiliser votre 
      gestion h�teli�re.
			</div>
			<?
			
			fiche($h,$sess_name);
			break;
		case 'LIST':
					
			/*if(is_hotel_select()){
				menu();
			} else { menu_header(); }
			*/
			$u = & $_SESSION['utilisateur_session_active'];
			if($u->get_count_hotel($db)==0){
  			?>
  			<div class="information">
  			Pour g�rer votre h�tel, vous devez pr�alablement le cr�er. Pour se faire cliquez 
        sur le bouton "Ajouter un hotel" ci-dessous et laissez-vous guider par l'interface...
  			</div>
  			<?
      
      }
			
			liste($db);
			break;
		default:
			erreur("inconnu : ".$action);
	} // switch
	$db->close();

	pied_de_page();


function valider(&$h){

  $trans=array("\\"=>"");

	$h->id_utilisateur = get_sess_user_id();
	$h->id_groupe = get_sess_user_group_id();
	$h->nom = strtr($_POST['nom'], $trans);
	$h->nom_gestion = strtr($_POST['nom_gestion'], $trans);
	$h->adresse = strtr($_POST['adresse'], $trans);
	$h->cp = $_POST['cp'];
	$h->ville = strtr($_POST['ville'], $trans);
	$h->email = $_POST['email'];
	$h->site = $_POST['site'];
	$h->tva = $_POST['tva'];
	$h->siren = $_POST['siren'];
	$h->siret = $_POST['siret'];
	$h->ape = $_POST['ape'];
	$h->capital = _fstring($_POST['capital']);
	$h->telephone = $_POST['telephone'];
	$h->fax = $_POST['fax'];
	$h->responsable = $_POST['responsable'];
	$h->rcs = $_POST['rcs'];
	$h->banque = $_POST['banque'];
	$h->etab = $_POST['etab'];
	$h->guichet = $_POST['guichet'];
	$h->compte = $_POST['compte'];
	$h->cle = $_POST['cle'];
	$h->note = $_POST['note'];

}
function enregistrer(&$db, &$h){

	if($h->nom!="" && $h->adresse!="" && $h->cp!="" && $h->ville!=""){
		$h->save($db);
		//info("Fiche enregistr�e");

		$_SESSION[SESS_HOTEL] = $h;
		
		return true;
	}
	else{
		erreur("Le nom, l'adresse, le code postal et la ville sont obligatoires");
		
		return false;
	}


}
function fiche(&$h,$sess_name){

	$formname="form_hotel";
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

	$t=new TTbl;
	$t->beg_tbl('formcadre',800,2,'','center');
	$t->beg_line("listheader");
	$t->Cell(
		"Fiche hotel"
	,-1,'',2);
	$t->end_line();
	$t->beg_line();
	$t->Cell("Nom");
	$t->Cell( $form->texte('','nom',$h->nom, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Nom de gestion");
	$t->Cell( $form->texte('','nom_gestion',$h->nom_gestion, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Adresse");
	$t->Cell( $form->texte('','adresse',$h->adresse, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Code postal");
	$t->Cell( $form->texte('','cp',$h->cp, 10, 10) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Ville");
	$t->Cell( $form->texte('','ville',$h->ville, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("E-mail");
	$t->Cell( $form->texte('','email',$h->email, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Site");
	$t->Cell( $form->texte('','site',$h->site, 80, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("TVA");
	$t->Cell( $form->texte('','tva',$h->tva, 5, 5,'','textfloat')." %" );
	$t->end_line();
	$t->beg_line();
	$t->Cell("SIREN");
	$t->Cell( $form->texte('','siren',$h->siren, 20, 20) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("APE");
	$t->Cell( $form->texte('','ape',$h->ape, 5, 10) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("SIRET");
	$t->Cell( $form->texte('','siret',$h->siret, 20, 20) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Capital");
	$t->Cell( $form->texte('','capital',_fnumber($h->capital,0), 20, 20,'','textfloat')." �" );
	$t->end_line();
	$t->beg_line();
	$t->Cell("T�l�phone");
	$t->Cell( $form->texte('','telephone',$h->telephone, 20, 20) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Fax");
	$t->Cell( $form->texte('','fax',$h->fax, 20, 20) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Responsable");
	$t->Cell( $form->texte('','responsable',$h->responsable, 60, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("RCS");
	$t->Cell( $form->texte('','rcs',$h->rcs, 20, 30) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Banque");
	$t->Cell( $form->texte('','banque',$h->banque, 60, 255) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("RIB");
	$t->Cell(
		$form->texte('','etab',$h->etab, 10, 10)
		.$form->texte('','guichet',$h->guichet, 10, 10)
		.$form->texte('','compte',$h->compte, 30, 50)
		.$form->texte('','cle',$h->cle, 2, 2) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Note");
	$t->Cell( $form->zonetexte('','note',$h->note, 77) );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Modification");
	$t->Cell($h->get_dtmaj());
	$t->end_line();
	$t->beg_line();
	$t->Cell("Cr�ation");
	$t->Cell($h->get_dtcre());
	$t->end_line();

	$t->end_tbl();

	echo "<p align=\"center\">";
/*	echo $form->bt("Liste","bt_retour"," onClick=\"go_liste()\"");
	echo $form->bt("Valider","bt_valid"," onClick=\"Valider()\"");*/

	/*echo $t->link("Liste","javascript:go_liste()","button");

	if($h->id>0)echo $t->link("Supprimer","javascript:supprimer()","button_delete");
	echo $t->link("Valider","javascript:valider()","button_valid");
*/
  echo $t->link( $t->img("bt_liste.jpg", "Liste") ,"javascript:go_liste()");

	if($h->id>0)echo $t->link( $t->img("bt_supprimer.jpg", "Supprimer") ,"javascript:supprimer()");
	echo $t->link( $t->img("bt_valider.jpg", "Valider") ,"javascript:valider()");

	echo "</p>";

	echo $form->end_form();
}

function liste(&$db){
	$listname='dblist1';
	$lst=new TListView($listname);

	//titre du r�f�rentiel
	echo "<h1>Liste des hotels</h1>";

	$t=new TTbl;

	echo "<p align=\"center\">"
  .$t->link( $t->img("bt_ajouterunhotel.jpg", "Ajouter un hotel") ,"?action=NEW")
  ."</p>";
/*$t->link("Ajouter un hotel","?action=NEW","button")*/
	/**
	 * A pr�voir cette liste va devenir un tableau de controle
	 * abandon de l'objet liste
	 * Alexis ALGOUD 03/03/2007 20:41:37
	 **/


	//requ�te
	$sql = "SELECT id as 'ID', nom as 'Nom', nom_gestion as 'Nom Gestion', adresse as 'Adresse', cp as 'Code postal'
	, ville as 'Ville', email as 'E-Mail', site as 'Site', tva as 'TVA', dt_cre as 'Cr�ation'
	FROM hot_hotel WHERE id_groupe=".get_sess_user_group_id();

/*	$ordercolumn = isset($_REQUEST["orderColumn"])?$_REQUEST["orderColumn"]: 'Nom';
	$ordertype = isset($_REQUEST["orderTyp"])?$_REQUEST["orderTyp"]:"A";
	$pagenumber = isset($_REQUEST["pageNumber"])?$_REQUEST["pageNumber"]:0;

	//initialisation de la requ�te
	$lst->Set_Query($sql);
	//chargement de la requ�te
	$lst->Load_query($ordercolumn,$ordertype); // on charge la requ�te
	$lst->Set_pagenumber($pagenumber);

	$lst->Set_Key("ID",'id');
	$lst->Set_columnType('Cr�ation','DATE');

	$lst->Set_OnClickAction('OpenForm',"?action=VIEW");
	$lst->Set_nbLinesPerPage(30);

	//Affichage de la liste
	echo $lst->Render();
*/

  $db->Execute($sql);
  
  if($db->Get_recordCount()>0){
  
    $THotel=array();
    while($db->Get_line()){
      $THotel[]=$db->Get_field('ID');
    }
    
    $t->beg_tbl('list',800,0,'','center');
    $t->beg_line('listheader');
    $t->Cell("Hotel");
    $t->Cell("Planning");
    if(is_admin())$t->Cell("Chambres");
    $t->Cell("Clients");
    if(is_admin())$t->Cell("Administration");
    $t->end_line();
    $class='L1';
    foreach ($THotel as $key=>$id_hotel) {
    	
    	$hotel=new THotel;
    	$hotel->load($db, $id_hotel);
    	
    	$url_ref = '../bin/hotel.php?action=VIEW&id='.$hotel->id;
    	
    	$t->beg_line($class);
    	$t->Cell(
        '<h1><a href="'.$url_ref.'">'.$hotel->nom.'</a></h1>'
        .$hotel->nom_gestion.'<br />'
        .$hotel->adresse.'<br />'
        .$hotel->cp.' '.$hotel->ville.'<br />'
        .'<a href="mailto:'.$hotel->email.'">'.$hotel->email.'</a>'
      );
    	$t->Cell(
    	   '<a href="'.$url_ref.'&url_go='.urlencode('../bin/planing.php').'"> '
         .$t->img("planning.gif","Acc�dez au planning")
         .' </a>'
         .'<br /><span class="mini">R�servation(s) : '.$hotel->get_count_reservation($db).'</span>'
         .'<br /><a href="'.$url_ref.'&url_go='.urlencode('../bin/reservation.php?action=NEW').'" class="mini">Nouvelle r�servation</a>'
    	,-1,'','','center');
    	if(is_admin())$t->Cell(
    	   '<a href="'.$url_ref.'&url_go='.urlencode('../bin/chambre.php').'"> '
         .$t->img("chambre.gif","G�rer les chambres")
         .' </a>'
         .'<br /><span class="mini">Chambre(s) : '.$hotel->get_count_chambre($db).'</span>'
         .'<br /><a href="'.$url_ref.'&url_go='.urlencode('../bin/chambre.php?action=NEW').'" class="mini">Nouvelle chambre</a>'
    	,-1,'','','center');
    	$t->Cell(
    	   '<a href="'.$url_ref.'&url_go='.urlencode('../bin/client.php').'"> '
         .$t->img("contact.gif","Les clients de l'h�tel")
         .' </a>'
          .'<br /><span class="mini">Client(s) : '.$hotel->get_count_client($db).'</span>'
         .'<br /><a href="'.$url_ref.'&url_go='.urlencode('../bin/client.php?action=NEW').'" class="mini">Nouveau client</a>'
    	,-1,'','','center');
    	if(is_admin())$t->Cell(
    	   '<a href="'.$url_ref.'&url_go='.urlencode('../bin/administration.php').'" class="mini">Administration/Editions</a>'
    	   .'<br /><a href="'.$url_ref.'&url_go='.urlencode('../bin/model.php').'" class="mini">Les mod�les de documents</a>'
    	   
         .'<br /><a href="'.$url_ref.'&url_go='.urlencode('../bin/produit.php').'" class="mini">Les produits/services</a>'
    	   .'<br /><a href="'.$url_ref.'&url_go='.urlencode('../bin/categorie.php').'" class="mini">Les cat�gories de chambres</a>'
    	   .'<br /><a href="'.$url_ref.'&url_go='.urlencode('../bin/utilisateur.php').'" class="mini">Les utilisateurs</a>'
    	   .'<br /><a href="'.$url_ref.'&url_go='.urlencode('../bin/param.php').'" class="mini">Les param�tres</a>'
    	,-1,'','','left');
    	
    	$t->end_line();
    	
    	$class=($class=='L1')?'L2':'L1';
    }
    $t->end_tbl();

  }
  
}
?>
