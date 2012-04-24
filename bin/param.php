<?
/********************************************************************
 * Alexis ALGOUD													*
 * 08/11/2006 18:43:32												*
 * IHM de gestion des param�tres										*
 ********************************************************************/

/**
 * Inclusion des classes
 * V�rification du log de l'utilisateur
 * Affichage de l'en-t�te de page et du menu
 * R�cup�ration de l'action � effectuer (LIST par d�faut)
 */
	require("../includes/inc.php");
	is_logged();

	if(!is_admin()) {
		entete("Acc�s refus�");
		erreur ("Vous n'�tes pas autoris� � afficher cette page");
		exit();
	}
	
	entete("Gestion param�tres",'online');

	
	if(is_hotel_select() && is_admin()){
		menu_admin();
	}

	if(isset($_REQUEST['action'])){
		$action=$_REQUEST['action'];
	}
	else{
		$action='LIST';
	}

/**
 * R�cup�ration ou cr�ation de la session param�tre
 * Cr�ation d'un acc�s � la base de donn�es
 * Appels aux fonctions suivant l'action
 */
	$sess_name=isset($_REQUEST['sess_name'])?$_REQUEST['sess_name']:gen_sess_name("param");
	$db=new Tdb();

	
	
	switch($action) {
		case 'SAVE':
			$TParam=& $_SESSION[$sess_name];
			
			valider($db,$TParam);
			enregistrer($db,$TParam);
			affiche($db, $TParam, $sess_name);

			break;
		case 'LIST':
	
			/*$req = new TRequete();
			$Tab = array();
			$Tab = $req->liste_tous_param($db);
			*/
			
      $_SESSION[$sess_name]=array();
			$TParam=& $_SESSION[$sess_name];
			
      load_all_param($db, $TParam);
			
      affiche($db, $TParam, $sess_name);

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
function load_all_param(&$db,  &$TParam){
	$TParam[0]=new TNumerotation;
  $TParam[0]->load($db, "FACTURE");
	
	$TParam[1]=new TNumerotation;
  $TParam[1]->load($db, "DEVIS");

}
/**
 * R�cup�ration des champs du formulaire envoy�s par POST
 */
function valider(&$db, &$TParam) {
	
	$n = & $TParam[0];
	$Tab = & $_REQUEST['TParam'][0];
	
  $n->prefixe = $Tab['prefixe'];
  $n->postfixe = $Tab['postfixe'];
  $n->numero = $Tab['numero'];
  $n->longueur = $Tab['longueur'];

	$n = & $TParam[1];
	$Tab = & $_REQUEST['TParam'][1];
	
  $n->prefixe = $Tab['prefixe'];
  $n->postfixe = $Tab['postfixe'];
  $n->numero = $Tab['numero'];
  $n->longueur = $Tab['longueur'];
}


/**
 * V�rification des champs saisis
 * Sauvegarde du param�tre dans la base
 */
function enregistrer(&$db, &$TParam) {
		
		info("Param�tres mis � jour");
		
		foreach($TParam as $k=>$v){
      
      $p = & $TParam[$k];
			$p->save($db);
			
    }
    
    $h = & $_SESSION[SESS_HOTEL];
    $h->set_parameter('nb_semaine_planning', abs((int)$_REQUEST['nb_semaine_planning']));
    $h->set_parameter('taxe_sejour_mt', _fstring($_REQUEST['taxe_sejour_mt'], 2));
    $h->set_parameter('taxe_sejour_age1', $_REQUEST['taxe_sejour_age1']);
    $h->set_parameter('taxe_sejour_age2', $_REQUEST['taxe_sejour_age2']);
    $h->set_parameter('taxe_sejour_taux1', $_REQUEST['taxe_sejour_taux1']);
    $h->set_parameter('taxe_sejour_taux2', $_REQUEST['taxe_sejour_taux2']);
    
    $h->set_parameter('taxe_sejour_mt1', _fstring($_REQUEST['taxe_sejour_mt1']));
    $h->set_parameter('taxe_sejour_mt2', _fstring($_REQUEST['taxe_sejour_mt2']));
    
    $h->set_parameter('signature_doc', strtr($_REQUEST['signature_doc'],array("\\"=>"")));
    $h->set_parameter('texte_commercial', strtr($_REQUEST['texte_commercial'],array("\\"=>"")));
    $h->save($db);
}

/**
 * Affichage des param�tres
 */
function affiche(&$db, &$TParam, $sess_name) {
	$formname="form_param";
?>

<script language="javascript">
function valider(){
 	document.forms['<?=$formname?>'].elements['action'].value='SAVE';
 	document.forms['<?=$formname?>'].submit();
}
</script>

<?
	$form=new TForm($_SERVER['PHP_SELF'], $formname);
	echo $form->hidden("action", "SAVE");
	echo $form->hidden("sess_name",$sess_name);
	
	$t=new TTbl();

	$t->beg_tbl('formcadre',800,2,'','center');
	$t->beg_line("listheader");
	$t->Cell("Param�tres",-1,'',4);
	$t->end_line();
	

	$n = & $TParam[0];
	$t->beg_line("listheader");
	$t->Cell("Num�ro de facture",-1,'',4);
	$t->end_line();
	$t->beg_line();
	$t->Cell($form->texte('Pr�fixe', 'TParam[0][prefixe]', $n->prefixe, 5,10) );
	$t->Cell($form->texte('Prochain num�ro', 'TParam[0][numero]', $n->numero, 10,20) );
	$t->Cell($form->texte('Longueur (sans pr�fixe) : ', 'TParam[0][longueur]', $n->longueur, 3,10) );
	$t->Cell($form->texte('Posfixe : ', 'TParam[0][postfixe]', $n->postfixe, 3,10) );
	$t->end_line();

	$n = & $TParam[1];
	$t->beg_line("listheader");
	$t->Cell("Num�ro de devis",-1,'',4);
	$t->end_line();
	$t->beg_line();
	$t->Cell($form->texte('Pr�fixe', 'TParam[1][prefixe]', $n->prefixe, 5,10) );
	$t->Cell($form->texte('Prochain num�ro', 'TParam[1][numero]', $n->numero, 10,20) );
	$t->Cell($form->texte('Longueur (sans pr�fixe) : ', 'TParam[1][longueur]', $n->longueur, 3,10) );
	$t->Cell($form->texte('Posfixe : ', 'TParam[1][postfixe]', $n->postfixe, 3,10) );
	$t->end_line();


	$h = & $_SESSION[SESS_HOTEL];
	$t->beg_line("listheader");
	$t->Cell("Planning",-1,'',4);
	$t->end_line();
	$t->beg_line();
	$t->Cell($form->texte('Nombre de semaine � afficher', 'nb_semaine_planning', $h->get_parameter('nb_semaine_planning'), 3,10) );
	$t->Cell("");
	$t->Cell("");
	$t->Cell("");
	$t->end_line();
  
	$t->beg_line("listheader");
	$t->Cell("Taxe de s�jour",-1,'',4);
	$t->end_line();
	$t->beg_line();
	$t->Cell($form->texte('Montant de la taxe de s�jour', 'taxe_sejour_mt', _f_prix($h->get_parameter('taxe_sejour_mt')), 5,10));
	$t->Cell("");
	$t->Cell("");
	$t->Cell("");
	$t->end_line();
	$t->beg_line();
	$t->Cell($form->texte('Personne de moins de ', 'taxe_sejour_age1', $h->get_parameter('taxe_sejour_age1') ? $h->get_parameter('taxe_sejour_age1'):"4", 3,10)." ans :");
	$t->Cell(
    $form->texte('Taux : ', 'taxe_sejour_taux1', $h->get_parameter('taxe_sejour_taux1') ? $h->get_parameter('taxe_sejour_taux1'):"", 3,10)." %"
    ." ou ".$form->texte('Montant : ', 'taxe_sejour_mt1', _fnumber($h->get_parameter('taxe_sejour_mt1'),2), 3,10)."�"
    , -1,'',2);
	$t->Cell("");
	$t->Cell("");
	$t->end_line();
	$t->beg_line();
	$t->Cell($form->texte('Personne de moins de ', 'taxe_sejour_age2', $h->get_parameter('taxe_sejour_age2') ? $h->get_parameter('taxe_sejour_age2'):"13", 3,10)." ans :");
	$t->Cell($form->texte('Taux : ', 'taxe_sejour_taux2', $h->get_parameter('taxe_sejour_taux2') ? $h->get_parameter('taxe_sejour_taux2'):"50" , 3,10)." %"
  ." ou ".$form->texte('Montant : ', 'taxe_sejour_mt2', _fnumber($h->get_parameter('taxe_sejour_mt2'),2), 3,10)."�", -1,'',2);
	$t->end_line();
	
	$t->beg_line("listheader");
	$t->Cell("Informations",-1,'',4);
	$t->end_line();
	$t->beg_line();
	$t->Cell($form->texte('Signature des documents', 'signature_doc', $h->get_parameter('signature_doc'), 80,255),-1,'',4);
	$t->end_line();
	$t->beg_line();
	$t->Cell($form->texte('Texte commercial', 'texte_commercial', $h->get_parameter('texte_commercial'), 80,255),-1,'',4);
	$t->end_line();


	$t->end_tbl();

	echo "<p align=\"center\">";
	echo $t->link("Valider","javascript:valider()","button_valid");
	echo "</p>";

	echo $form->end_form();
}

function _affiche_param (&$p, &$form) {
	$t = new TTbl();
	
	$t->beg_line();
	$t->Cell($p->id);
	$t->Cell($p->description);
	//print_r($p);
	switch ($p->type) {
		case "VALEUR_N":
		case "VALEUR_F":
			$t->Cell($form->texte('','param['.$p->id.']',$p->get_valeur(),20,20));
			break;
		case "VALEUR_S":
			$t->Cell($form->texte('','param['.$p->id.']',$p->get_valeur(),20,255));
			break;
		case "VALEUR_T":
			$t->Cell($form->zonetexte('','param['.$p->id.']',$p->get_valeur(),50));
			break;
		case "VALEUR_D":
			$t->Cell($form->texte('','param['.$p->id.']',$p->get_valeur(),10,10));
			break;	
		default:
			$t->Cell("Erreur de chargement du param�tre");
			break;
	}

	$t->end_line();
}

?>