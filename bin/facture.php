<?
/********************************************************************
 * Alexis ALGOUD													*
 * 08/11/2006 18:43:32												*
 * IHM de gestion des factures										*
 ********************************************************************/

/**
 * Inclusion des classes
 * V�rification du log de l'utilisateur
 * Affichage de l'en-t�te de page et du menu
 * R�cup�ration de l'action � effectuer (LIST par d�faut)
 */
	require("../includes/inc.php");
	is_logged();
	

	if(is_hotel_select() && is_not_popup()){
	   entete("Gestion factures",'online');
	   
	}
	else{
    entete("Gestion factures", 'popup');
  }

	if(isset($_REQUEST['action'])){
		$action=$_REQUEST['action'];
	} else if(isset($_REQUEST['actionform'])){
		$action=$_REQUEST['actionform'];
	}
	else{
		$action='LIST';
	}
/**
 * R�cup�ration ou cr�ation de la session facture
 * Cr�ation d'un acc�s � la base de donn�es
 * Appels aux fonctions suivant l'action
 */
	$sess_name=isset($_REQUEST['sess_name'])?$_REQUEST['sess_name']:gen_sess_name("facture");
	$db=new Tdb();

	switch($action) {
		case 'ADD_LIGNE':
			$f = & $_SESSION[$sess_name];

			valider($f);
			$f->add_ligne_libre();

			$_SESSION[$sess_name]=$f;
			fiche($f,$sess_name);

			break;
		case 'ADD_RESERVATION':
			$f = & $_SESSION[$sess_name];

			valider($f);
			$f->add_reservation($db, $_REQUEST['p1']);

			$_SESSION[$sess_name]=$f;
			fiche($f,$sess_name);

			break;
		case 'ADD_PRODUIT':
			$f = & $_SESSION[$sess_name];
			valider($f);
			$f->add_produit($db, $_REQUEST['p1']);
			
			$_SESSION[$sess_name]=$f;
			fiche($f,$sess_name);

			break;
		case 'DEL_LIGNE':

			$f = & $_SESSION[$sess_name];

			$f->delete_ligne($_REQUEST['p1']);

			valider($f);
			$_SESSION[$sess_name]=$f;
			fiche($f,$sess_name);
			break;
		case 'SAVE':
			$f = & $_SESSION[$sess_name];
//			$f->regroupe_produit();
			valider($f);
			$res = enregistrer($db,$f);
			if ($res && !is_not_popup()) {
				fermer_popup($f);
			} else {
				fiche($f, $sess_name);
			}

			break;
		case 'DELETE':
			$f = & $_SESSION[$sess_name];
			$f->delete($db);

			if (!is_not_popup()) {
				fermer_popup($f);
			} else {
				liste();
			}

			break;
		case 'VIEW':
			$_SESSION[$sess_name]=new TFacture();
			$f = & $_SESSION[$sess_name];
			$f->load($db, $_REQUEST['id'],true);

			fiche($f,$sess_name);

			break;
		case 'NEW':
			$_SESSION[$sess_name]=new TFacture();
			$f = & $_SESSION[$sess_name];
			$f->type=isset($_REQUEST['type'])?$_REQUEST['type']:"FACTURE";
			$f->init_numero($db, true);
			
			if (isset($_REQUEST["p1"])) {
				$f->add_reservation($db, $_REQUEST['p1']);
			/*	$_SESSION[$sess_name]=$f;*/
			}
			
			//isset($_REQUEST['id_client']);

			fiche($f,$sess_name);

			break;
		case 'LIST':
			liste();

			break;
		case 'CONVERT':
			$f = & $_SESSION[$sess_name];
			
      		valider($f);
			
			$f->to_facture($db);
			
			enregistrer($db,$f);
			fiche($f,$sess_name);
			
			break;
		case 'RETURN':	
			$f = & $_SESSION[$sess_name];
			fiche($f,$sess_name);
			
			break;
		case 'PRINT':
			$f = & $_SESSION[$sess_name];
			valider($f);
			
			$c=new TClient;
			if($f->id_client>0){
        $c->load($db, $f->id_client);
      }
			
			_frame_print($f, $c,$sess_name);
      
			break;
		default:
			erreur("L'action ".$action." est inconnue");
	} // switch

/**
 * Fermeture de la connexion � la base de donn�es
 * Affichage du pied de page
 */
	$db->close();
	if(is_hotel_select() && is_not_popup()){
	   pied_de_page();
	   
	}
	else{
    pied_de_page('popup');
  }

/************************************
 * FONCTIONS LOCALES				*
 ************************************/

function fermer_popup (&$f) {
	?><script language="JavaScript" type="text/javascript">
  	setTimeout('window.close()',1000);
	</script><?
}


/**
 * R�cup�ration des champs du formulaire envoy�s par POST
 */
function valider(&$f) {
$trans=array("\\"=>"");

	$f->numero=$_POST['numero'];
	$f->type=$_POST['type'];
	$f->set_dtfacture($_POST['dt_facture']);
	$f->id_client=$_POST['id_client'];
	$f->id_hotel=get_sess_hotel_id();
	$f->nom_client=strtr($_POST['nom_client'], $trans);
	$f->adresse_client=strtr($_POST['adresse_client'], $trans);
	$f->taux_tva=isset($_POST['taux_tva'])?$_POST['taux_tva']:0.00;
	$f->total_ht=isset($_POST['total_ht'])?_fstring($_POST['total_ht']):0.00;
	$f->total_tva=isset($_POST['total_tva'])?_fstring($_POST['total_tva']):0.00;
	$f->total_ttc=isset($_POST['total_ttc'])?_fstring($_POST['total_ttc']):0.00;
	$f->total_negoce=isset($_POST['total_negoce'])?_fstring($_POST['total_negoce']):0.00;
	$f->remise=isset($_POST['remise'])?_fstring($_POST['remise']):0.00;
	$f->acompte=isset($_POST['acompte'])?_fstring($_POST['acompte']):0.00;
	
	if(isset($_POST['TLigne'])){

		$TLigne = &$_POST['TLigne'];
		$keys=array_keys($TLigne);
		$nb=count($keys);
		for($i = 0; $i < $nb; $i++){
			$k = $keys[$i];
			$f->TLigne[$k]->libelle =$TLigne[$k]['libelle'];
			$f->TLigne[$k]->quantite =_fstring($TLigne[$k]['quantite']);
			$f->TLigne[$k]->prix_u = _fstring($TLigne[$k]['prix_u']);
			$f->TLigne[$k]->montant = _fstring($TLigne[$k]['montant']);
			$f->TLigne[$k]->tva =$TLigne[$k]['tva'];
		} // for
	}
}

/**
 * V�rification des champs saisis
 * Sauvegarde du client dans la base
 */
function enregistrer(&$db, &$f) {
//$db->db->debug=true;

	if($f->numero!="" && $f->dt_facture!="" && $f->nom_client!='') {
		if($f->id==0 || $f->check_num_facture($db)) {
			$f->save($db);
			info($f->get_numero()." ".(($f->type=='DEVIS')?"enregistr�":"enregistr�e"));
			return true;
		}
		else {
			erreur($f->get_numero()." existe d�j�");
		}
	}
	else {
		erreur("Le num�ro, la date et le client sont obligatoire");
	}
}

/**
 * Affichage d'une fiche de consultation / saisie d'un client
 */
function fiche(&$f,$sess_name) {

	$formname="form_facture";
?>
<script language="javascript" src="../scripts/facture.js">
</script>
<script language="javascript">
function go_liste(){
 	document.forms['<?=$formname?>'].elements['action'].value='LIST';
 	document.forms['<?=$formname?>'].submit();
}
function valider(){
 	document.forms['<?=$formname?>'].elements['action'].value='SAVE';
 	document.forms['<?=$formname?>'].submit();
}
function imprimer(){
 	document.forms['<?=$formname?>'].elements['action'].value='PRINT';
 	document.forms['<?=$formname?>'].submit();
}
function supprimer(){
	if(window.confirm('Voulez-vous vraiment supprimer cette fiche ?')){
	document.forms['<?=$formname?>'].elements['action'].value='DELETE';
 	document.forms['<?=$formname?>'].submit();
	}
}
function facture(){
 	document.forms['<?=$formname?>'].elements['action'].value='CONVERT';
 	document.forms['<?=$formname?>'].submit();
}
function add_reservation(id_resa){
	document.forms['<?=$formname?>'].elements['action'].value='ADD_RESERVATION';
 	document.forms['<?=$formname?>'].elements['p1'].value=id_resa;
 	document.forms['<?=$formname?>'].submit();
}
function add_produit(id_produit){
	document.forms['<?=$formname?>'].elements['action'].value='ADD_PRODUIT';
 	document.forms['<?=$formname?>'].elements['p1'].value=id_produit;
 	document.forms['<?=$formname?>'].submit();
}
function add_ligne_libre(id_produit){
	document.forms['<?=$formname?>'].elements['action'].value='ADD_LIGNE';
 	document.forms['<?=$formname?>'].submit();
}
function del_ligne(id_ligne){
	document.forms['<?=$formname?>'].elements['action'].value='DEL_LIGNE';
 	document.forms['<?=$formname?>'].elements['p1'].value=id_ligne;
 	document.forms['<?=$formname?>'].submit();
}
function pop_add_reservation(){
	showPopup('../dlg/lst_reservation_for_facture.php','<?=$formname?>','id_reservation',600)
}
function pop_add_produit(){
	showPopup('../dlg/lst_produit.php','<?=$formname?>','id_produit',600)
}
</script>

<?


	$form=new TForm($_SERVER['PHP_SELF'], $formname);
	echo $form->hidden("sess_name", $sess_name);
	echo $form->hidden("action", "SAVE");
	echo $form->hidden("p1", "");
	echo $form->hidden("type", $f->type);

	is_popup_var();

	$t=new TTbl();

?>
<div align="center">
<div style="width:900px;">
<div style="float:none; clear:both; text-align:right;width:800px;">

<?
 echo $t->link( $t->img('bt_impression.jpg', 'Imprimer') ,"javascript:imprimer()")
?>

</div>
<?

	$t->beg_tbl('formcadre',800,0,'','center');
	$t->beg_line("listheader");
	$t->Cell(
		$f->get_numero()
	,-1,'',3	,'center');
	$t->end_line();
	$t->beg_line();
	$t->Cell("Num�ro",'','');
	if($f->id==0){
    $t->Cell($form->texteRO('','numero',$f->numero,50,40),'','');
  }
  else{
    $t->Cell($form->texte('','numero',$f->numero,50,40),'','');
  }
	
	$t->Cell("Date ".$form->texte('','dt_facture',$f->get_dtfacture(),10,10),'','');
	$t->end_line();
	$t->beg_line();
	$t->Cell("Client",'','');
	$t->Cell(
		$form->hidden('id_client', $f->id_client)
		.$form->texte('','nom_client',$f->nom_client, 50,40)
		.$t->link("choisir","javascript:showPopup('../dlg/lst_client.php','$formname','id_client;nom_client;adresse_client')","lien","","ico_browse.gif")
	,'','',2);
	$t->end_line();
	$t->beg_line();
	$t->Cell("Adresse",'','');
	$t->Cell($form->zonetexte('','adresse_client',$f->adresse_client,47,3),'','',2);
	$t->end_line();
	$t->beg_Cell(-1,'',3);
	ligne_facture($form, $f, $formname);
	$t->end_Cell();
	$t->end_line();

	$t->beg_line();
	$t->Cell(
		$t->link("Ajouter une reservation","javascript:pop_add_reservation()","lien2","","ico_xplus.gif")
		." | ".$t->link("Ajouter un produit","javascript:pop_add_produit()","lien2","","ico_xplus.gif")
		." | ".$t->link("Ajouter une ligne libre","javascript:add_ligne_libre()","lien2","","ico_xplus.gif")
	,'','',2);
	$t->end_line();
	$t->beg_line();
	$t->Cell("Modification",'','');
	$t->Cell($f->get_dtmaj(),'','',2);
	$t->end_line();
	$t->beg_line();
	$t->Cell("Cr�ation",'','');
	$t->Cell($f->get_dtcre(),'','',2);
	$t->end_line();

	$t->end_tbl();

	echo "<p align=\"center\">";
	if (is_not_popup()) {
		echo $t->link("Liste","javascript:go_liste()","button");
	}
	if($f->id>0) {
		echo $t->link("Supprimer","javascript:supprimer()","button_delete");
	}
	
	echo $t->link("Valider","javascript:valider()","button_valid");
//	echo $t->link("Imprimer","facture_imprime.php?sess_name=$sess_name","button_valid","_blank");
//	echo $t->link("Imprimer","javascript:imprimer()","button_valid");
	
	if ($f->type == "DEVIS") {
		echo $t->link("Facturer","javascript:facture()","button_valid");
	}
	echo "</p>";
?>
</div>
</div>
<?
	echo $form->end_form();
?>

<script language="JavaScript" type="text/javascript">
if (document.forms['<?=$formname?>'].elements['total_ttc']!=null) {
    calcul_total_ttc('<?=$formname?>');
}

if (document.forms['<?=$formname?>'].elements['remise']!=null) {
	calcul_total_remise('<?=$formname?>','<?=($f->remise!=0)?0:_fnumber($f->total_negoce,2)?>');
}
</script>
  

<?
}


function ligne_facture ($form, $f, $formname) {
	$Tlignes = &$f->TLigne;
	$nb = count ($Tlignes);

	$t = new TTbl();

	if ($nb > 0) {
	//$t->debug=true;
		$t->beg_tbl('formcadre','100%',0,'','center');
		$t->beg_line('listheader');
		$t->Cell("");
		$t->Cell("Rg.",-1,'','','center');
		$t->Cell("Code",-1,'','','center');
		$t->Cell("Libell�",-1,'','','center');
		$t->Cell("Qt�.",-1,'','','center');
		$t->Cell("P.U.",-1,'','','center');
		$t->Cell("Total",-1,'',2,'center');
		$t->Cell("T.V.A.",-1,'','','center');
		$t->end_line();

		$nb_ligne_facture=0;
		for ($i = 0; $i < $nb; $i++) {
			$l = $Tlignes[$i];
			if(!$l->to_delete){
				$t->beg_line();
				$t->Cell($t->link($t->img("ico_xminus.gif"),"javascript:del_ligne($i)"));
				$t->Cell($l->rang,-1,'','','center');
				$t->Cell(substr($l->type_objet,0,3).($l->id_objet!=0?"_".$l->id_objet:''),-1,'');
				$t->Cell($form->texte('',"TLigne[$i][libelle]",$l->libelle,55,255),-1,'');

				$t->Cell($form->texte('',"TLigne[$i][quantite]",_fnumber($l->quantite,2),5,10,"onKeyUp=calcul_montant_ligne('$formname',$i)",'textfloat'),10,'','','right');
				$t->Cell($form->texte('',"TLigne[$i][prix_u]",_fnumber($l->prix_u,2),10,10,"onKeyUp=calcul_montant_ligne('$formname',$i)",'textfloat'),-1,'','','right');
				$t->Cell($form->texteRO('',"TLigne[$i][montant]",_fnumber($l->montant,2),10,10, 'TLigne_montant_'.$nb_ligne_facture),-1,'','','right');
				$t->Cell(" �",-1,'','','right');
				$t->Cell($form->combo('',"TLigne[$i][tva]",$l->TTaux_tva,$l->tva,1,"calcul_montant_ligne('$formname',$i)")." %"
        ,-1,'','','','',true);
				$t->end_line();
				$nb_ligne_facture++;
			}

		} // for
		
		//$form->hidden('nb_ligne_facture', $nb_ligne_facture);
		$t->beg_line();
		//$t->Cell( $form->hidden('nb_ligne_facture', $nb_ligne_facture) );
		$t->Cell($form->texteRO('TOTAL TTC','total_ttc',_fnumber($f->total_ttc,2),10,10),-1,'',7,'right');
		$t->Cell(" �",-1,'','','right');
		$t->end_line();

		$t->beg_line();
		$t->Cell(
			//$form->combo('TVA','taux_tva',$f->TTaux_tva,$f->taux_tva,'',"calcul_tva_total_ttc('".$formname."');")."&nbsp;".
			$form->texteRO('DONT TVA','total_tva',_fnumber($f->total_tva,2),10,10),-1,'',7,'right');
		$t->Cell(" �",-1,'','','right');			
		$t->end_line();
		$t->beg_line();
		$t->Cell($form->texteRO('SOIT HT','total_ht',_fnumber($f->total_ht,2),10,10),-1,'',7,'right');
		$t->Cell(" �",-1,'','','right');
		$t->end_line();

		$t->beg_line();
		$t->Cell($form->texte('REMISE','remise',_fnumber($f->remise,2),10,10,"onKeyUp=calcul_total_remise('".$formname."','"._fnumber($f->total_negoce,2)."');",'textfloat'),-1,'',7,'right');
		$t->Cell(" %",-1,'','','right');
		$t->end_line();
		$t->beg_line();
		$t->Cell("<b id=\"span_totalremise\">TOTAL NEG. </b>".$form->texte('','total_negoce',_fnumber($f->total_negoce,2),10,10,"onKeyUp=calcul_total_negoce('".$formname."','"._fnumber($f->total_negoce,2)."');",'textfloat'),-1,'',7,'right');
		$t->Cell(" �",-1,'','','right');
		$t->end_line();
		$t->beg_line();
		$t->Cell($form->texte('ACOMPTE','acompte',_fnumber($f->acompte,2),10,10,"",'textfloat'),-1,'',7,'right');
		$t->Cell(" �",-1,'','','right');
		$t->end_line();

		$t->end_tbl();
		$form->end_form();
	}
}

/**
 * Affichage de la liste des factures
 */
function liste(){
	$listname='dblist1';
	$lst=new TListView($listname);

	echo "<h1>Liste des factures</h1>";

	$t=new TTbl();

	echo "<p align=\"center\">".$t->link("Ajouter une facture","?action=NEW","button");
	echo $t->link("Ajouter un devis","?action=NEW&type=DEVIS","button")."</p>";

	/**
	 * Requ�te
	 */
	$sql = "SELECT id as 'ID', numero as 'NUMERO',type as 'TYPE', dt_facture as 'DATE'
			,nom_client as 'CLIENT', adresse_client as 'ADRESSE'
			,total_ttc as 'TOTAL TTC'
			,total_negoce as 'NEGOCE'
			,dt_cre as 'Cr�ation'
			FROM hot_facture ";

	$where="WHERE id_hotel=".get_sess_hotel_id();

	$charIdx=isset($_REQUEST['charIndex'])?$_REQUEST['charIndex']:"all";
	switch($charIdx){
		case "all":
			null;
			break;
		default:
			$where.=" AND type = '".$charIdx."'";
			break;
	} // switch

	$sql .= $where;
	
	$fact = new TFacture;
	$TIndex['table']="hot_facture";
	$TIndex['char']=$charIdx;
	$TIndex['idx']="type";
	$TIndex['condition']="id_hotel=".get_sess_hotel_id();
	$TIndex['TTrans'] = &$fact->TType;
	

	$ordercolumn = isset($_REQUEST["orderColumn"])?$_REQUEST["orderColumn"]: 'Cr�ation';
	$ordertype = isset($_REQUEST["orderTyp"])?$_REQUEST["orderTyp"]:"D";
	$pagenumber = isset($_REQUEST["pageNumber"])?$_REQUEST["pageNumber"]:0;

	$lst->Set_Query($sql);
	$lst->Load_query($ordercolumn,$ordertype,$TIndex);
	$lst->Set_pagenumber($pagenumber);

	$lst->Set_Key("ID",'id');
	$lst->Set_hiddenColumn("ID",true);
	$lst->Set_columnType('Cr�ation','DATE');
	$lst->Set_columnType('DATE','DATE');
	
	$lst->Str_trans('TYPE',$fact->TType);

	$lst->Set_OnClickAction('OpenForm',$_SERVER['PHP_SELF']."?action=VIEW");
	$lst->Set_nbLinesPerPage(30);

	echo $lst->Render();
}
function _frame_print(&$f,&$c, &$sess_name){
	$formname = "formretour";
?>
	<script language="javascript">
	function retour(){
	 	document.forms['<?=$formname?>'].elements['action'].value='RETURN';
	 	document.forms['<?=$formname?>'].submit();
	}
	</script>
<?
	$form=new TForm(null, $formname);
	echo $form->hidden("sess_name", $sess_name);
	echo $form->hidden("action", "SAVE");
	echo $form->hidden("p1", "");
	
	is_popup_var();
	
	echo $form->end_form();
	
?>
	<div align="center">
	<br>
	<?
  /* if($f->id_client>0 && $c->email!=''){
      ?><a href="javascript:retour()" class="button">Envoyer � <?=$c->email?></a><?
    }*/
  ?>
	<a href="javascript:retour()" class="button">Retour � <?=$f->get_numero()?></a>
  &nbsp;&nbsp;&nbsp;
  <a href="http://www.adobe.com/fr/products/acrobat/readstep2.html">
  <img src="http://www.adobe.com/images/shared/download_buttons/get_adobe_reader.png" border="0" align="absmiddle" />
  </a>
  <div id="link_facture"></div>
  <br>
	<iframe width="80%" height="100%" align="center" src="facture_pdf.php?sess_name=<?=$sess_name?>"></iframe>
	</div>
<?
	

			
}
?>
