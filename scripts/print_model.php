<?php

	  require("../includes/inc.php");
    require("../fpdf153/fpdf.php");
	  require("../includes/class.pdfmaker.php");

	  $db=new Tdb;
	  $r=new TRequete;
	  $TResa = $r->get_liste_reservation_du_jour($db, $_REQUEST['dt_jour']);

    $hotel = & $_SESSION[SESS_HOTEL];
    
    $Tableau['hotel']=array(
      "nom"=>$hotel->nom
      ,"adresse"=>$hotel->adresse
      ,"ville"=>$hotel->ville
      ,"cp"=>$hotel->cp
      ,"signature"=>$hotel->responsable
      ,"commercial"=>$hotel->banque
      ,"tel"=>$hotel->telephone
      ,"fax"=>$hotel->fax
    );

    $Tableau['client']=array(
        "nom"=>"Client de l'hôtel"
        ,"adresse"=>"Son adresse\nCode postal et Ville"
      );

	  $p = new TPDFMaker;
	  $m = new TModel;
    $m->load($db, $_REQUEST['id_model']);
	  
	  $p->model=$m->get_src();
	  
	  
	  $pdf = new TPdf;
	  $pdf->AliasNbPages();
	
    $p->parse_file($Tableau);
	  
	  $p->write($pdf, $trans);  
	  

	  $pdf->Output("visualisation.pdf","I");





?>
