<?php

	require("../includes/inc.php");
    require("../fpdf153/fpdf.php");
	  require("../includes/class.pdfmaker.php");

    $mode=isset($_REQUEST['mode'])?$_REQUEST['mode']:'pdfliste';

    $db=new Tdb;
    
    switch ($mode) {
    
      case 'pdf':
        _print_pdf($db, $_REQUEST['id_resa']);
      
        break;
    
      case 'pdfliste':
        _print_liste_pdf($db, $_REQUEST['dt_jour']);
        break;
        
      case 'selection':
        _get_liste_reservation($db, $_REQUEST['dt_jour']);
      
        break;
    
      case 'arrivee':
	_get_liste_arrivee($db, $_REQUEST['dt_jour'],$_REQUEST['dt_jour2']);
	break;

    default:
    	
    	break;
    }
    
    $db->close();

function _time_by_datefr($datefr){

  list($j,$m,$a)=explode('/',$datefr);
  
  return strtotime("$a-$m-$j");

}
function _get_liste_arrivee(&$db, $dt_jour1, $dt_jour2) {
	?>
	<html>
	<style>
	table.formcadre {
		border:2px solid #000;
		width:100%;
	}

	table.formcadre td {
		padding:5px;
		border-right:1px dotted #bbb;
	}
	table.formcadre tr.listheader {
		font-weight:bold;
	}
	table.formcadre tr.L0 {
		background-color:#fff;
		
	}
	table.formcadre tr.L0 td {
		border-top:1px solid #000;
	}
	table.formcadre tr.L1 {
		background-color:#ccc;
	}
	table.formcadre tr.L2 {
		background-color:#fff;
	}
	</style>
	<body>
	<h1>Arrivées du <?=$dt_jour1?> au <?=$dt_jour2?></h1>
	<?
	$t=new TTbl;
	$t->beg_tbl('formcadre');
	$t->beg_line('listheader');
	$t->Cell('Chambre');
	$t->Cell('Nom');
	$t->Cell('Arrivée');
	$t->Cell('départ');
	$t->end_line();


	$deb = _time_by_datefr($dt_jour1);
	$fin = _time_by_datefr($dt_jour2);

	$r=new TRequete;
	$TChambre = $r->liste_toute_chambre_par_hotel($db, get_sess_hotel_id());
	
	foreach($TChambre as $id_chambre) {

		$chambre = new TChambre;
		$chambre->load($db, $id_chambre);

		$TResa = $r->liste_toute_reservation_arrivee_par_chambre_par_date($db, $id_chambre, $deb, $fin);
		$class='L0';
		foreach($TResa as $id_resa) {

			$resa=new TReservation;
			$resa->load($db, $id_resa, true);

			$t->beg_line($class);
				$t->Cell($chambre->num);
				$t->Cell($resa->nom_client);
				$t->Cell($resa->get_dtdeb());
				$t->Cell($resa->get_dtfin());
			$t->end_line();

			$class=($class=='L1')?'L2':'L1';
		}

	}

	$t->end_tbl();

	?>

  <script language="javascript">
  window.setTimeout("self.print()", 1000);
  </script>
  

	</body>
	</html>
	<?

}

function _get_liste_reservation(&$db, $dt_jour){

  entete("Liste des réservations",'popup');

  echo '<br />';

  $r=new TRequete;
	$TResa = $r->get_liste_reservation_du_jour($db, $dt_jour);

  $t=new TTbl;
  
  $t->beg_tbl('formcadre','100%',2,'','center');

  $t->beg_line('listheader');
  $t->Cell("Liste des réservations prise le $dt_jour");
  $t->end_line();

   $nb=count($TResa);
	  for($i=0;$i<$nb;$i++){
	    $resa = new TReservation;
	    $resa->load($db, $TResa[$i], true, true, true);
	     
	    $nom = $resa->client->nom.(($resa->client->prenom=='')?'':' '.$resa->client->prenom ); 
	    $dt_deb = $resa->get_dtdeb();
	    $dt_fin = $resa->get_dtfin(true);
       
	    $t->beg_line();
      $t->Cell( $t->link("$nom du $dt_deb au $dt_fin", "?mode=pdf&id_resa=".$resa->id));
      $t->end_line();
    }
    
    $t->end_tbl();

  
  pied_de_page('popup');
  

}
    
function _print_pdf(&$db, $id_resa){

  $hotel = & $_SESSION[SESS_HOTEL];
    
    $Tableau['hotel']=array(
      "nom"=>$hotel->nom
      ,"nom_gestion"=>$hotel->nom_gestion
      ,"adresse"=>$hotel->adresse
      ,"ville"=>$hotel->ville
      ,"cp"=>$hotel->cp
      ,"signature"=>$hotel->get_parameter("signature_doc")
      ,"commercial"=>$hotel->get_parameter("texte_commercial")
      ,"tel"=>$hotel->telephone
      ,"fax"=>$hotel->fax
    );
    
    $p = new TPDFMaker;
	  
	  $pdf = new TPdf;
	  $pdf->AliasNbPages();
	    
    $resa = new TReservation;
	    $resa->load($db, $id_resa, true, true, true);
	     
	    $trans["titre_categorie"] = $resa->chambre->categorie->libelle;
	    $trans["datedujour"] = date("d/m/Y");
	    $trans["reservation_date_debut"] = $resa->get_dtdeb();
	    $trans["reservation_date_fin"] = $resa->get_dtfin(true);
	    $trans["numero_chambre"] = $resa->chambre->num;
	    $trans["description_categorie"] = $resa->chambre->categorie->definition;
	    $trans["reservation_acompte"] = _fnumber($resa->acompte);
	    $trans["frais_de_reservation"]=_fnumber($resa->frais_resa);
      $trans["ville"]=$hotel->ville;

    	$Tableau['client']=array(
        "nom"=>$resa->client->nom.(($resa->client->prenom=='')?'':' '.$resa->client->prenom )
        ,"adresse"=>$resa->client->adresse
      );
      
      

      
      $p->set_best_model_for($db, $resa->client->nationalite,"RESA") ;//DIR_MODEL."lettre_reservation.txt";
      
      $p->parse_file($Tableau);
	  
	    $p->write($pdf, $trans);  
	  
	  

	  $pdf->Output("reservation.pdf","I");

}    
    
function _print_liste_pdf(&$db, $dt_jour){



    $r=new TRequete;
	  $TResa = $r->get_liste_reservation_du_jour($db, $dt_jour);

    $hotel = & $_SESSION[SESS_HOTEL];
    
    $Tableau['hotel']=array(
      "nom"=>$hotel->nom
      ,"nom_gestion"=>$hotel->nom_gestion
      
      ,"adresse"=>$hotel->adresse
      ,"ville"=>$hotel->ville
      ,"cp"=>$hotel->cp
      ,"signature"=>$hotel->get_parameter("signature_doc")
      ,"commercial"=>$hotel->get_parameter("texte_commercial")
      ,"tel"=>$hotel->telephone
      ,"fax"=>$hotel->fax
    );



	  $p = new TPDFMaker;
	  
	  
	  
	  $pdf = new TPdf;
	  $pdf->AliasNbPages();
	    
	    //$db->db->debug=true;
	    
	  $nb=count($TResa);
	  for($i=0;$i<$nb;$i++){
	    $resa = new TReservation;
	    $resa->load($db, $TResa[$i], true, true, true);
	     
	    $trans["titre_categorie"] = $resa->chambre->categorie->libelle;
	    $trans["datedujour"] = date("d/m/Y");
	    $trans["reservation_date_debut"] = $resa->get_dtdeb();
	    $trans["reservation_date_fin"] = $resa->get_dtfin(true);
	    $trans["numero_chambre"] = $resa->chambre->num;
	    $trans["description_categorie"] = $resa->chambre->categorie->definition;
	    $trans["reservation_acompte"] = _fnumber($resa->acompte);
	    $trans["frais_de_reservation"]=_fnumber($resa->frais_resa);
      $trans["ville"]=$hotel->ville;

    	$Tableau['client']=array(
        "nom"=>$resa->client->nom.(($resa->client->prenom=='')?'':' '.$resa->client->prenom )
        ,"adresse"=>$resa->client->adresse
      );
      
      

      
      $p->set_best_model_for($db, $resa->client->nationalite,"RESA") ;//DIR_MODEL."lettre_reservation.txt";
      
      $p->parse_file($Tableau);
	  
	    $p->write($pdf, $trans);  
	  }
	  

	  $pdf->Output("liste_reservations.pdf","I");


}




?>
