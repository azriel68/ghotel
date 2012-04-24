<?php
  
/**
 * Gestion des campagnes
 *
 * @version $Id$
 * @copyright 2005 
 */
	$Page = "Clients";
	require("../includes/inc.php");
	is_logged();
  	require("../includes/menu.php");

	raz_session();
	
	if(!isset($_REQUEST['action'])){
		$mod_action="LIST";
	
	}
	else if($_REQUEST['action']=="SAVE") {
		if(isset($_REQUEST['bt_valid'])){
			$mod_action="SAVE";
		}
		else if(isset($_REQUEST['bt_valid2'])){
			$mod_action="SAVE2";
		}
		else if (isset($_REQUEST['bt_new'])) {
		 	$mod_action="NEW";        
		}
		else if (isset($_REQUEST['bt_stat'])) {
		 	$mod_action="STAT";       
			
			
			
		}
		else if (isset($_REQUEST['bt_delete'])) {
		 	$mod_action="DELETE";        
		}
		else if (isset($_REQUEST['bt_retour'])) {
		 	$mod_action="LIST";        
		}
		
	}
	else {
		$mod_action = strtoupper($_REQUEST['action']);
	
	}
	
	
	switch($mod_action){
		case 'STAT':
			$cli = &$_SESSION['client'];
			?>
			<script LANGUAGE="javascript">
				document.location.href="../bin/statistique.php?action=CLI&id=<?=$cli->id?>";	
			</script>
			<?
			exit();
	
			break;
		case 'CAMP':
			$_SESSION['client']=new TClient;
			$cli = &$_SESSION['client'];
		
			$db=new Tdb;
			
			$db->Execute("SELECT id_cli FROM pub_camp WHERE id=".$_REQUEST['id']);
			$db->Get_line();
			$id_cli = $db->Get_field('id_cli');
			
			$cli->load($db,$id_cli);
			
			fiche($cli);
			
			$db->close();
		
			break;
		case 'DELETE':
			
			$cli = &$_SESSION['client'];
		
			$db=new Tdb;
			
			supprimer($db,$cli);
			
			liste();
			
			$db->close();

			break;
		case 'SAVE':
		
			$cli = &$_SESSION['client'];
		
			$db=new Tdb;
			
			valider($cli);
			if(!enregistrer($db,$cli)){
				fiche($cli);
			}
			else{
				liste();
			}
			
			
			
			$db->close();
		
			break;
		case 'SAVE2':
		
			$cli = &$_SESSION['client'];
		
			$db=new Tdb;
			
			valider($cli);
			enregistrer($db,$cli);
			
			fiche($cli);
			
			$db->close();
		
			break;
		case 'LINKF_REFRESH':
			$cli = &$_SESSION['client'];
			valider($cli);
			
			fiche($cli,"LINKF");
		
			break;
		case 'LINK_REFRESH':
			$cli = &$_SESSION['client'];
			valider($cli);
			
			fiche($cli,"LINK");
		
			break;	
		case 'LINK_PROD':
			$cli = &$_SESSION['client'];
			valider($cli);
			
			fiche($cli,"LINK");
		
			break;
		case 'LINK_PRODF':
			$cli = &$_SESSION['client'];
			valider($cli);
			
			fiche($cli,"LINKF");
		
			break;
		case 'LIST' :
	
			liste();
	
			break;
		
		case 'NEW':
			$_SESSION['client']=new TClient;
			$cli = &$_SESSION['client'];
		
			$db=new Tdb;
			
			fiche($cli);
			
			$db->close();
		
			break;
		case 'VIEW' :
			$_SESSION['client']=new TClient;
			$cli = &$_SESSION['client'];
		
			$db=new Tdb;
			$cli->load($db,$_REQUEST['id']);
			
			fiche($cli);
			
			$db->close();
		
			break;
		default :
			print "Action inconnue : ".$mod_action."<br>";
	
	}
	  
  
  
  
  
  
  	pied_de_page();
	
	
function liste(){
	
	echo "<h1>Liste des clients</h1>";
	$t=new TTbl;
	$formname="formlistcli";
	$form = new TForm("client.php",$formname);
	echo $form->hidden("action", "list");

	
	
		$listname = "listcli";
		$lst = new Tlistview($listname);
	
		
		$ordercolumn = isset($_REQUEST["orderColumn"])?$_REQUEST["orderColumn"]: 'Date de création' ;
	    $ordertype = isset($_REQUEST["orderTyp"])?$_REQUEST["orderTyp"]:"D";
	    $pagenumber = isset($_REQUEST["pageNumber"])?$_REQUEST["pageNumber"]:0;
		 	
	 $where="";
     $charIdx="all";
     if(isset($_REQUEST['charIndex'])){
             $charIdx=$_REQUEST['charIndex'];
             switch($_REQUEST['charIndex']){
                     case "other":
                            $where.=" AND idx='0'";
                            break;
                     case "all":
                             null;
                             break;
                     default:
                             $where.=" AND idx='".$_REQUEST['charIndex']."'";
             } // switch

     }

		 $sql="SELECT id as 'ID',nom as 'Nom', mails as 'Emails de contact',dt_cre as 'Date de création'
		 FROM pub_client WHERE suppr=0".$where;   
		      
	 	 $lst->Set_query($sql);
	     $lst->Load_query($ordercolumn,$ordertype);
	     $lst->Set_pagenumber($pagenumber);
	     $lst->Set_Key("ID",'id');
		 $lst->Set_columnType('Date de création',"DATETIME");
		 
	     $lst->Set_OnClickAction('OpenForm',"client.php?action=VIEW");
	     $lst->Set_nbLinesPerPage(30);
		 $lst->set_actionColumn("CLI","ID");	
		
		 echo $t->draw_index("client.php?tblname=$listname", "pub_client", "idx", $charIdx);

	 	 echo $lst->Render();

	 	  echo "<br>".$t->link("Ajouter un client", "client.php?action=NEW",'lien');
	
	 echo $form->end_form();

	
}
function fiche(&$cli,$mode_fiche="CAMP"){
global $_movx,$_movy;

	echo "<h1>Fiche client</h1>";

	$formname="formclient";
	$t=new TTbl;
	echo "<script language=\"javascript\">";
	echo "function sendMail(){";
	echo "	pop=window.open('../dlg/send_mail.php?id=".$cli->id."&typemail=STATCLI','sm','width=600,height=500');";
	echo "	pop.focus();";
	echo "}";
	echo "function goLink(){";
	echo "	document.forms['$formname'].elements['action'].value=\"LINK_PROD\";";
	echo "	document.forms['$formname'].submit();";
	echo "}";
	echo "function goLinkF(){";
	echo "	document.forms['$formname'].elements['action'].value=\"LINK_PRODF\";";
	echo "	document.forms['$formname'].submit();";
	echo "}";
	echo "function refreshlink(){";
	echo "	document.forms['$formname'].elements['action'].value=\"LINK_REFRESH\";";
	echo "	document.forms['$formname'].submit();";
	echo "}";
	echo "function dellink(){";
	echo "	document.forms['$formname'].elements['id_indus'].value=\"\";";
	echo "	document.forms['$formname'].elements['name_indus'].value=\"\";";
	echo "	refreshlink();";
	echo "}";
	echo "function refreshlinkF(){";
	echo "	document.forms['$formname'].elements['action'].value=\"LINKF_REFRESH\";";
	echo "	document.forms['$formname'].submit();";
	echo "}";
	echo "function dellinkF(){";
	echo "	document.forms['$formname'].elements['id_orga'].value=\"\";";
	echo "	document.forms['$formname'].elements['name_orga'].value=\"\";";
	echo "	refreshlinkF();";
	echo "}";
	
	echo "</script>";


	$form = new TForm("client.php",$formname,"POST",true);
	echo $form->hidden("action","SAVE");
	echo $form->hidden("p1","");
	echo $form->hidden("p2","");
	
	$t->beg_tbl('formcadre');	
	$t->beg_line();
	$t->Cell("Identifiant");
	$t->Cell($form->texteRO("", "id", $cli->id, 10));
	$t->end_line();
	$t->beg_line();
	$t->Cell("Nom");
	$t->Cell(
		$form->texte("", "nom", $cli->nom, 100,255)
	);
	$t->end_line();
	
	$t->beg_line();
	$t->Cell("Mot de passe");
	$t->Cell( $form->texte("","password", ($cli->password!="")?$cli->password:makepassword(),20)	);
	$t->end_line();
	
	$t->beg_line();
	$t->Cell("Mails de contact<br>1 mail par ligne ");
	$t->Cell(	$form->zonetexte("", "mails", $cli->mails, 60,3)	);
	$t->end_line();
	
	$t->beg_line();
	$plus="";
	if($cli->dt_mails!=0)$plus.=" (déjà envoyé ".date("d/m/Y H:i:s",$cli->dt_mails).")";
	$t->Cell($t->link($t->img("mail.gif","","","","absmiddle")." Donner au client ses codes d'accès aux statistiques".$plus,"javascript:sendMail()"),-1,'','2');
	$t->end_Cell();
	
	if ($mode_fiche=="LINK") {
		$t->beg_line();
		$t->beg_cell(-1,'',2);
		
			$t->beg_tbl('formcadrepubli');
		    $t->beg_line();
			$t->Cell("Industriel lié",100);
			$t->Cell(
				$form->hidden("id_indus", $cli->id_indus)
				.$form->texteRO("", "name_indus", $cli->name_indus, 50,255)
				.$t->link("choisir","javascript:showPopup('../dlg/lst_indus.php','$formname','id_indus;name_indus')","lien","","ico_browse.gif")
				."&nbsp;&nbsp;&nbsp;"
				.$t->link($t->img("ok.gif","Charger l'indus et spécifier la date de début"),"javascript:refreshlink();")
				.(($cli->id_indus==0)?"":"&nbsp;&nbsp;&nbsp;".$t->link($t->img("b_drop.png","Supprimer la liaison industriel"),"javascript:dellink();") )
			);
			$t->end_line();
			if(($cli->id_indus!=0)&&(strcasecmp($cli->name_indus,$cli->nom))){
			
				$t->beg_line();
				$t->Cell('');
				$t->Cell($t->img("warning.gif",null,null,null,"absmiddle")."Le nom ne correspond pas, faites attention !");
				$t->end_line();
				
			}
			
			
			if($cli->id_indus!=0){
				$t->beg_line();
				$t->Cell("Prendre les produits à partir de ");
				$t->Cell( 
					$form->texte("", "dt_ref", $cli->get_dtref(), 12, 10)
					.$t->link($t->img("ok.gif","Voir les produits concernés"),"javascript:refreshlink();")
				 );
				$t->end_line();
				
				
				if($cli->dt_ref!=0){
					$t->beg_line();
					$t->Cell("Produits concernés");
					$t->beg_Cell();
					
						$t->beg_tbl('formcadre',-1,2);
						$t->beg_line('listheader');
						$t->cell("Date de création");
						$t->cell("Libellé");
						$t->cell("Nb. form.");
						$t->cell("Nb. visu.");
						$t->cell("Nb. clic.");
						$t->end_line();
					
						$class='L1';	
						$Tab = $cli->get_TabLinkedProd(true);
						$nb=count($Tab);
						for($i = 0; $i < $nb; $i++){
						
							$row = &$Tab[$i];
						
							$t->beg_line($class);
							$t->cell(date("d/m/Y",$row['dt_cre']));
							$t->cell($row['nom']);
							$t->cell($row['nb_form']);
							$t->cell($row['nb_visu']);
							$t->cell($row['nb_clic']);
							$t->end_line();
								
							if($class=='L1')$class='L2';
							else $class='L1';	
								
						} // for
						
						$t->end_tbl();
						
						//print_r($Tab);
					
					$t->end_Cell();
					$t->end_line();
				
				}
				
			}
			
			$t->end_tbl();
		
		$t->end_Cell();
		$t->end_line();
	}
	if ($mode_fiche=="LINKF") {
		$t->beg_line();
		$t->beg_cell(-1,'',2);
		
			$t->beg_tbl('formcadrefiche');
		    $t->beg_line();
			$t->Cell("Oragnisme lié",100);
			$t->Cell(
				$form->hidden("id_orga", $cli->id_orga)
				.$form->texteRO("", "name_orga", $cli->name_orga, 50,255)
				.$t->link("choisir","javascript:showPopup('../dlg/lst_orga.php','$formname','id_orga;name_orga')","lien","","ico_browse.gif")
				."&nbsp;&nbsp;&nbsp;"
				.$t->link($t->img("ok.gif","Charger l'organisme et spécifier la date de début"),"javascript:refreshlinkF();")
				.(($cli->id_indus==0)?"":"&nbsp;&nbsp;&nbsp;".$t->link($t->img("b_drop.png","Supprimer la liaison organisme"),"javascript:dellinkF();") )
			);
			$t->end_line();
			if(($cli->id_orga!=0)&&(strcasecmp($cli->name_orga,$cli->nom))){
			
				$t->beg_line();
				$t->Cell('');
				$t->Cell($t->img("warning.gif",null,null,null,"absmiddle")."Le nom ne correspond pas, faites attention !");
				$t->end_line();
				
			}
			
			
			if($cli->id_orga!=0){
				$t->beg_line();
				$t->Cell("Prendre les formations à partir de ");
				$t->Cell( 
					$form->texte("", "dt_ref", $cli->get_dtref(), 12, 10)
					.$t->link($t->img("ok.gif","Voir les formations concernées"),"javascript:refreshlinkF();")
				 );
				$t->end_line();
				
				
				if($cli->dt_ref!=0){
					$t->beg_line();
					$t->Cell("formations concernées");
					$t->beg_Cell();
					
						$t->beg_tbl('formcadre',-1,2);
						$t->beg_line('listheader');
						$t->cell("Date de création");
						$t->cell("Libellé");
						$t->cell("Nb. form.");
						$t->cell("Nb. visu.");
						$t->cell("Nb. clic.");
						$t->end_line();
					
						$class='L1';	
						$Tab = $cli->get_TabLinkedForm(true);
						$nb=count($Tab);
						for($i = 0; $i < $nb; $i++){
						
							$row = &$Tab[$i];
						
							$t->beg_line($class);
							$t->cell(date("d/m/Y",$row['dt_cre']));
							$t->cell($row['nom']);
							$t->cell($row['nb_form']);
							$t->cell($row['nb_visu']);
							$t->cell($row['nb_clic']);
							$t->end_line();
								
							if($class=='L1')$class='L2';
							else $class='L1';	
								
						} // for
						
						$t->end_tbl();
						
						//print_r($Tab);
					
					$t->end_Cell();
					$t->end_line();
				
				}
				
			}
			
			$t->end_tbl();
		
		$t->end_Cell();
		$t->end_line();
	}
	else if ($mode_fiche=="CAMP") {
	    $t->beg_line();
		$t->beg_cell(-1,'',2);
		// Périodes
		
		$t->beg_tbl('formcadredossier');
		$t->beg_line();
		$t->Cell("<b>Périodes de campagne non Terminées</b>");
		$t->end_line();
		$t->beg_line();
		$t->beg_cell();
			
			$t->beg_tbl('form',-1,2);
			$t->beg_line('listheader');
			$t->Cell("");
			$t->Cell("Début");
			$t->Cell("Fin");
			$t->Cell("Nb clic minimum");
			$t->Cell("Nb clic maximum");
			$t->Cell("Nb visualisation minimum");
			$t->Cell("Nb visualisation maximum");
			$t->Cell("Nb clic en cours");
			$t->Cell("Nb visualisation en cours");
			$t->end_line();
			
			$nb=$cli->get_nbCamp();
	
			for($i = 0; $i < $nb; $i++){
				
				if($cli->TCamp[$i]->etat!="Terminée"){
				
					$t->beg_line('listheader');
					$t->Cell($t->link($t->img("stat.gif","Statistique de la campagne"),"statistique.php?action=CAMP&id=".$cli->TCamp[$i]->id));
					$t->Cell($t->link("du ".$cli->TCamp[$i]->get_dtfirst()." au ".$cli->TCamp[$i]->get_dtlast()." ".$cli->TCamp[$i]->lib_format." dans ".$cli->TCamp[$i]->lib_empl,"campagne.php?action=VIEW&id=".$cli->TCamp[$i]->id),-1,'','8');
					$t->end_line();
					
					
					$nbP=$cli->TCamp[$i]->get_nbPeriod();
					$class="L1";
					for($j = 0; $j < $nbP; $j++){
						$p  =&$cli->TCamp[$i]->TPeriod[$j];
					
						$valid = $p->is_valid();
					
						if(($valid)&&($p->etat!="Terminée")){
							
							$t->beg_line($class);
							$t->Cell(
								$t->link($t->img("stat.gif","Statistique de la période"),"statistique.php?action=PERIOD&id=".$p->id."&id_camp=".$cli->TCamp[$i]->id)
							);
							$t->Cell($p->get_dtdeb());
							$t->Cell($p->get_dtfin());
							$t->Cell($p->nb_clicmin);
							$t->Cell($p->nb_clicmax);
							$t->Cell($p->nb_visumin);
							$t->Cell($p->nb_visumax);
							$t->Cell($p->nb_clic);
							$t->Cell($p->nb_visu);
							
							$t->end_line();
							
							if($class=='L1')$class='L2';
							else $class='L1';
						}
						
						
						
					} // for
				}
			
			} // for
			
			$t->end_tbl();
			
		$t->end_cell();
		$t->end_line();
		$t->end_tbl();
		// fin Périodes
	
		$t->end_cell();
		$t->end_line();
	
	
		$t->beg_line();
		$t->beg_cell(-1,'',2);
		
		$t->beg_tbl('formcadrefiche');
		$t->beg_line();
		$t->Cell("<b>Périodes de campagne Terminées</b>");
		$t->end_line();
		$t->beg_line();
		$t->beg_cell();
			
			$t->beg_tbl('form',-1,2);
			$t->beg_line('listheader');
			$t->Cell("");
			$t->Cell("Début");
			$t->Cell("Fin");
			$t->Cell("Nb clic minimum");
			$t->Cell("Nb clic maximum");
			$t->Cell("Nb visualisation minimum");
			$t->Cell("Nb visualisation maximum");
			$t->Cell("Nb clic");
			$t->Cell("Nb visualisation");
			$t->end_line();
			
			$nb=$cli->get_nbCamp();
	
			for($i = 0; $i < $nb; $i++){
				
				if($cli->TCamp[$i]->etat=="Terminée"){
				
					$t->beg_line('listheader');
					$t->Cell($t->link($t->img("stat.gif","Statistique de la campagne"),"statistique.php?action=CAMP&id=".$cli->TCamp[$i]->id));
					$t->Cell($t->link("du ".$cli->TCamp[$i]->get_dtfirst()." au ".$cli->TCamp[$i]->get_dtlast()." ".$cli->TCamp[$i]->lib_format." dans ".$cli->TCamp[$i]->lib_empl,"campagne.php?action=VIEW&id=".$cli->TCamp[$i]->id),-1,'','8');
					$t->end_line();
					
					
					$nbP=$cli->TCamp[$i]->get_nbPeriod();
					$class="L1";
					for($j = 0; $j < $nbP; $j++){
						$p  =&$cli->TCamp[$i]->TPeriod[$j];
					
						$valid = $p->is_valid();
					
						if($valid){
							
							$t->beg_line($class);
							$t->Cell(
								$t->link($t->img("stat.gif","Statistique de la période"),"statistique.php?action=PERIOD&id=".$p->id."&id_camp=".$cli->TCamp[$i]->id)
							);
							$t->Cell($p->get_dtdeb());
							$t->Cell($p->get_dtfin());
							$t->Cell($p->nb_clicmin);
							$t->Cell($p->nb_clicmax);
							$t->Cell($p->nb_visumin);
							$t->Cell($p->nb_visumax);
							$t->Cell($p->nb_clic);
							$t->Cell($p->nb_visu);
							
							$t->end_line();
							
							if($class=='L1')$class='L2';
							else $class='L1';
						}
						
						
						
					} // for
				}
			
			} // for
			$t->end_tbl();
			
		$t->end_cell();
		$t->end_line();
		$t->end_tbl();
		// fin Périodes
	
		$t->end_cell();
		$t->end_line();     
    }// fin mode campagne
	
	
	
	
	$t->beg_line();
	$t->Cell("Date de création");
	$t->Cell( $cli->get_dtcre());
	$t->end_line();
	$t->beg_line();
	$t->Cell("Date de mise à jour");
	$t->Cell( date("d/m/Y") );
	$t->end_line();
	
	$t->beg_line();
	$t->beg_Cell();
	
	if($cli->id_orga==0)echo $form->bt("Liaison produit","bt_link"," onClick=\"goLink()\" style=\"background:#00ac00\"");
	if($cli->id_indus==0)echo $form->bt("Liaison formation","bt_linkf"," onClick=\"goLinkF()\" style=\"background:#a8aac2\"");
	
	$t->end_Cell();
	$t->beg_cell(-1, '', '',"center" );
	echo $form->btsubmit("Retour", "bt_retour")." ";
	if($cli->id!=0){
		echo $form->btsubmit("Statistique", "bt_stat")." ";
		echo $form->btsubmit("Supprimer", "bt_delete")." ";
	}
	
	echo $form->btsubmit("Nouveau", "bt_new")." ";
	echo $form->btsubmit("Valider", "bt_valid");
	echo $form->btsubmit("Valider et revenir", "bt_valid2");
	$t->end_cell();
	$t->end_line();
	
	$t->end_tbl();
	
	
	echo $form->end_form();

}
function enregistrer(&$db,&$c){
//$db->db->debug=true;
	if($c->nom==""){
		erreur("Le nom est obligatoire");
		return false;
	
	}
	elseif(($c->id==0)&&($c->already_exist($db))){
		erreur("Ce client existe déjà");
		return false;
	}
	else {
		$c->save($db);
		info("Client enregistré");
		return true;
	}


}
function supprimer(&$db,&$c){

	$c->delete($db);
	
	info("Client supprimé");

	return true;
}
function valider(&$c){

	$c->nom = 	$_POST['nom'];
	$c->mails =	$_POST['mails'];
	$c->password =	$_POST['password'];
	
	if(isset($_POST['id_indus'])){
		
		$c->id_indus = $_POST['id_indus'];
		$c->name_indus = $_POST['name_indus'];
		if(isset($_POST['dt_ref'])){
			$c->set_dtref($_POST['dt_ref']);
		}
	}
	
	if(isset($_POST['id_orga'])){
		
		$c->id_orga = $_POST['id_orga'];
		$c->name_orga = $_POST['name_orga'];
		if(isset($_POST['dt_ref'])){
			$c->set_dtref($_POST['dt_ref']);
		}
	}
	
}

?>