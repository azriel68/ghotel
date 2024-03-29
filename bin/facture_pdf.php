<?
  require("../includes/inc.php");
  require("../fpdf153/fpdf.php");
  require("../includes/class.pdfmaker.php");
  
  $sess_name = $_REQUEST["sess_name"];
  $hotel = & $_SESSION[SESS_HOTEL];
  $facture = & $_SESSION[$sess_name];
  $facture_ligne = & $facture->TLigne;


  

  $Tableau['client']=array(
    "nom"=>$facture->nom_client
    ,"adresse"=>$facture->adresse_client
//    ,"ville"=>"VERSAILLES"
//    ,"cp"=>"78000"
  );
  
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
    ,"forme_juridique"=>$hotel->get_parameter("forme_juridique")
    ,"rcs"=>$hotel->rcs
    ,"siret"=>$hotel->siret
    ,"ape"=>$hotel->ape
    ,"capital"=>$hotel->capital
    ,'date'=>$facture->get_dtfacture()
  );
  
  if (count($facture_ligne) > 0) {
	  // En-t�te tableau
	  $Tableau['lignes']=array(
	    0=>array(
	      0=>"Description||110||C||1||1||0||230||10"
	      ,1=>"Quantit�||20||C||1||1||0||230||10"
	      ,2=>"Prix U||20||C||1||1||0||230||10"
	      ,3=>"Total||20||C||1||1||0||230||10"
	    )
	  );
	
	  // Lignes
	  $nb_lignes = count($facture_ligne);
	  
	  $total_taxe_sejour = 0;
	  
	  for ($i = 0; $i < $nb_lignes; $i++) {
  		$tab = array();
  		
  		if(!$facture_ligne[$i]->to_delete){
  		
    		$tab[] = $facture_ligne[$i]->libelle."||110||L||0||0||0||0||8";
    		$tab[] = $facture_ligne[$i]->quantite."||20||C||0||0||0||0||8";
    		$tab[] = $facture_ligne[$i]->prix_u."||20||R||0||0||0||0||8";
    		$tab[] = $facture_ligne[$i]->montant."||20||R||0||0||0||0||8";
    		$Tableau['lignes'][] = $tab;
    		
    		if($facture_ligne[$i]->type_objet=='TAXE_SEJOUR')$total_taxe_sejour+=$facture_ligne[$i]->montant;
    		
  		}
  		
	  }
	  
	  // Total
	  $tab = array();
	  $tab[] = "TOTAL||150||R||1||1||0||230||8";
	  $tab[] = _fnumber($facture->total_ttc,2)."||20||R||1||1||0||230||8";
	  $Tableau['lignes'][] = $tab;
	  if($total_taxe_sejour>0) {
  	  // Total - TAX
  	  $tab = array();
  	  $tab[] = "||90||L";
  	  $tab[] = "avant Taxe de s�jour||60||R||1||1||0||||6";
  	  $tab[] = _fnumber($facture->total_ttc-$total_taxe_sejour,2)."||20||R||1||0||0||0||6";
  	  $Tableau['lignes'][] = $tab;
    }
	  
	  // TVA
	  $tab = array();
	  $tab[] = "||90||L";
	  $tab[] = "DONT TVA||60||R||1||1||0||230||8";
	  $tab[] = _fnumber($facture->total_tva,2)."||20||R||1||0||0||0||8";
	  $Tableau['lignes'][] = $tab;
	  
	  if($total_taxe_sejour>0) {
	  // TAX
  	  $tab = array();
  	  $tab[] = "||90||L";
  	  $tab[] = "dont Taxe de s�jour||60||R||1||1||0||||6";
  	  $tab[] = _fnumber($total_taxe_sejour,2)."||20||R||1||0||0||0||6";
  	  $Tableau['lignes'][] = $tab;
	  }
	  // HT
	  $tab = array();
	  $tab[] = "||90||L";
	  $tab[] = "SOIT HT||60||R||1||1||0||230||8";
	  $tab[] = _fnumber($facture->total_ht,2) ."||20||R||1||0||0||0||8";
	  $Tableau['lignes'][] = $tab;
	  
	  if ($facture->total_remise != 0) {
		  // REMISE
		  $tab = array();
		  $tab[] = "||90||L";
		  $tab[] = "REMISE||60||R||1||1||0||230||8";
		  $tab[] = _fnumber($facture->total_remise,2)."||20||R||1||0||0||0||8";
		  $Tableau['lignes'][] = $tab;
		  $total_du = $facture->total_remise;
	  } else if ($facture->total_negoce != 0) {
		  // REMISE
		  $tab = array();
		  $tab[] = "||90||L";
		  $tab[] = "TOTAL NEGOCIE||60||R||1||1||0||230||8";
		  $tab[] = _fnumber($facture->total_negoce,2)."||20||R||1||0||0||0||8";
		  $Tableau['lignes'][] = $tab;
		  $total_du = $facture->total_negoce;  	
	  } else if ($facture->acompte != 0) {
	  	  // ACOMPTE
		  $tab = array();
		  $tab[] = "||90||L";
		  $tab[] = "ACOMPTE||60||R||1||1||0||230||8";
		  $tab[] = _fnumber($facture->acompte,2)."||20||R||1||0||0||0||8";
		  $Tableau['lignes'][] = $tab;
		  $total_du = $facture->total_ttc - $facture->acompte;
	  }
	  else{
      $total_du = $facture->total_ttc;
    }
	
	  // TOTAL DU
	  $tab = array();
	  $tab[] = "||90||L";
	  $tab[] = "TOTAL DU||60||R||1||1||0||230||8";
	  $tab[] = _fnumber($total_du,2)."||20||R||1||1||0||230||8";
	  $Tableau['lignes'][] = $tab;
  } else {
  	  $tab = array();
	  $tab[] = "AUCUNE LIGNE SUR CE DOCUMENT||170||C||1||1||0||230||8";
	  $Tableau['lignes'][] = $tab;
  }
  
    
  $p = new TPDFMaker;
  $db = new Tdb;
  
  $p->set_best_model_for($db, $facture->client->nationalite,"FACTURE");		
  $p->parse_file($Tableau);
  
  
  //header('Content-Type: application/pdf');
  //header('Content-Disposition: inline; filename=fichier.pdf');

  $pdf = new TPdf;
  $pdf->AliasNbPages();
    
  $trans["ref_doc"] = $facture->get_numero();
  $p->write($pdf, $trans);
  
  $fichierpdf =$facture->type."-".$facture->numero."-".substr(md5(time().rand(0,1000)),0,10).".pdf";
  
  $pdf->Output("../tmp/".$fichierpdf,'F');

  header('Content-Type: application/pdf');
  header('Content-Disposition: inline; filename='.urlencode($facture->get_numero()).'.pdf');
  
 print file_get_contents("../tmp/".$fichierpdf)

//  print "<a href=\"../tmp/$fichierpdf\">test</a>";

?>
