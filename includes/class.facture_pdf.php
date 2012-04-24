<?php


class facture_pdf extends FPDF {
	
	function Header()
	{
		$this->SetFont('Arial','BI',15);
	    $this->SetDrawColor(0,0,0);
		$this->SetFillColor(220,220,220);
		$this->SetTextColor(0,0,0);
	    $this->Cell(0,10,'FACTURE','LRTB',1,'C',1);
	    
	    $this->Ln(10);
	}

	function Footer()
	{
	    $this->SetY(-15);
	    $this->SetFont('Arial','I',8);
	    $this->Cell(0,0,'',1,1,'C');
	    $this->Cell(0,10,'Page '.$this->PageNo().' / {nb}',0,0,'C');
	}
	
	
	function zoneHotel () {
		$this->SetFont('Arial','',10);
	    $this->SetDrawColor(0,0,0);
		$this->SetFillColor(220,220,220);
		$this->SetTextColor(0,0,0);
		
		$hotel = getHotelName()."\n".
				getHotelAdresse()."\n".
				getHotelVille();

		$this->setY(30);
		$this->setX(15);
		$this->Cell(85,10,"HOTEL",'LRTB',1,'C',1);

		$this->setX(15);
		$this->MultiCell(85,7,$hotel,1);
//		$this->Cell(95,5,"TEL : ".getHotelTel(),0,1);
//		$this->Cell(95,5,"FAX : ".getHotelFax(),0,1);
	}
	
	function zoneClient ($f) {
		$this->SetFont('Arial','',10);
	    $this->SetDrawColor(0,0,0);
		$this->SetFillColor(220,220,220);
		$this->SetTextColor(0,0,0);

		$client = $f->nom_client."\n".
		$f->adresse_client;
		
		$this->setY(30);
		$this->setX(110);
		$this->Cell(85,10,"ADRESSE FACTURATION",'LRTB',1,'C',1);

		$this->setX(110);
		$this->MultiCell(85,7,$client,1);
	}
	
	function zoneFacture ($f) {
		$this->Ln(10);
		$this->SetFont('Arial','B',10);
		$this->SetDrawColor(0,0,0);
		$this->SetFillColor(220,220,220);
		$this->SetTextColor(0,0,0);
		$this->Cell(0,10,$f->get_numero(),'LRTB',1,'C',1);
		
		$Tab = $f->TLigne;
		$nb = count($Tab);
		
		if ($nb > 0) {
			$this->afficheEnTete();
			$this->afficheLigne($Tab,$nb);
			$this->afficheTotaux($f);				
		} else {
			$this->Cell(0,10,"CETTE FACTURE NE CONTIENT AUCUNE LIGNE",1,0,'C');
		}
	}
	
	function afficheEnTete () {
		
		$this->SetFont('Arial','B',10);
		$this->Cell(20,10,"REF",1,0,'C');
		$this->Cell(110,10,"DESIGNATION",1,0,'L');
		$this->Cell(20,10,"QTE",1,0,'R');
		$this->Cell(20,10,"PRIX U",1,0,'R');
		$this->Cell(20,10,"TOTAL",1,1,'R');
	}
	
	function afficheLigne ($Tab,$nb) {
		$this->SetFont('Arial','',10);
		
		for ($i = 0; $i < $nb; $i++) {
			$l = $Tab[$i];
			$this->Cell(20,10,substr($l->type_objet,0,3)."_".$l->id_objet,1,0,'C');
			$this->Cell(110,10,$l->libelle,1,0,'L');
			$this->Cell(20,10,_fnumber($l->quantite,2),1,0,'R');
			$this->Cell(20,10,_fnumber($l->prix_u,2)." €",1,0,'R');
			$this->Cell(20,10,_fnumber($l->montant,2)." €",1,1,'R');
		} // for
	}
	
	function afficheTotaux ($f) {
		$this->SetFont('Arial','',10);
		$this->Cell(130,10);
		$this->Cell(40,10,"TOTAL TTC",1,0,'R');
		$this->Cell(20,10,_fnumber($f->total_ttc,2)." €",1,1,'R');

		if ($f->total_negoce > 0 && $f->remise > 0) {
			$this->Cell(130,10,"REMISE "._fnumber($f->remise,2)." %",1,0,'R');
			$this->Cell(40,10,"TOTAL REM",1,0,'R');
			$this->Cell(20,10,_fnumber($f->total_negoce,2)." €",1,1,'R');
		} else if ($f->total_negoce > 0) {
			$this->Cell(130,10);
			$this->Cell(40,10,"TOTAL NEG",1,0,'R');
			$this->Cell(20,10,_fnumber($f->total_negoce,2)." €",1,1,'R');
		}
		
		$this->Cell(130,10);
		$this->Cell(40,10,"DONT TVA",1,0,'R');
		$this->Cell(20,10,_fnumber($f->total_tva,2)." €",1,1,'R');
		$this->Cell(130,10);		
		$this->Cell(40,10,"SOIT HT",1,0,'R');
		$this->Cell(20,10,_fnumber($f->total_ht,2)." €",1,1,'R');
	}
	
	function createFacturePDF ($f) {
		$this->AliasNbPages();
		$this->AddPage();
		$this->zoneHotel();
		$this->zoneClient($f);
		$this->zoneFacture($f);
//		$this->SetFont('Arial','B',16);
//		$this->Cell(40,10,"FACTURE NUMERO : ".$f->numero,0,1);
//		$this->Cell(40,10,"CLIENT : ".$f->nom_client,0,1);
//		$this->Cell(40,10,"ADRESSE : ".$f->adresse_client,0,1);
//		$this->Cell(160,10,"MONTANT : ".$f->total_ttc." €",1,1,'R');
//		$this->Cell(160,10,"NEGOCE : ".$f->total_negoce." €",1,1,'R');
		return $this->Output($f->numero,'I');
	}
} // class

?>
