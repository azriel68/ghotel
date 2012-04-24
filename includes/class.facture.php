<?php
require("../fpdf153/fpdf.php");
class TFacture {
	/**
     * Constructor
     * @access protected
     */

	function TFacture() {
		$this->id=0;
		$this->numero=0;
		$this->dt_facture=time();
		$this->nom_client="";
		$this->adresse_client="";
		$this->taux_tva=0.0;
		$this->total_ht=0.0;
		$this->total_tva=0.0;
		$this->total_ttc=0.0;
		$this->total_negoce=0.0;
		$this->remise=0.0;
		$this->acompte=0.0;
		$this->type="";

		$this->id_client=0;
		$this->id_hotel=0;
		
		$this->TType = array(
			"DEVIS" => "Devis"
			,"FACTURE" => "Facture"
		);

		$this->dt_cre=time();
		$this->dt_maj=time();

		$this->TLigne = array();
	}
	
	function reinit(){
  
      $this->id = 0;
			$this->reinit_id_ligne();
  }
  
  function to_facture($db){
  
      $this->reinit();
      $this->type = "FACTURE";
			/*$this->init_numero($db);*/
  }
	
	function get_numero(){
    return strtr($this->type,$this->TType)." N° ".$this->numero;
  }
	
	function add_ligne_libre () {
		$iLigne = $this->add_ligne();
		$ligne = & $this->TLigne[$iLigne];
	
		$ligne->rang=0;
		$ligne->id_objet=0;
		$ligne->type_objet="DIVERS";
		$ligne->libelle="";
		$ligne->quantite=1;
		$ligne->prix_u=0;
		$ligne->montant=0;
	}
	function add_produit (&$db, $id_produit) {
		$produit = new TProduit();
		$produit->load($db,$id_produit);
		if(!$this->regroupe_produit($produit)) {
			$iLigne = $this->add_ligne();
			$ligne = & $this->TLigne[$iLigne];
	
			$ligne->rang=0;
			$ligne->id_objet=$produit->id;
			$ligne->type_objet="PRODUIT";
			$ligne->libelle=$produit->libelle;
			$ligne->quantite=1;
			$ligne->prix_u=$produit->prix;
			$ligne->montant=$produit->prix;
			$ligne->tva=$produit->tva;
		}
	}

	function regroupe_produit ($produit) {
		$nb=count($this->TLigne);
		for ($i = 0; $i < $nb; $i++) {
			$l = &$this->TLigne[$i];
//			print("TVA LIGNE : ".$l->tva."<br>");
//			print("TVA PRODUIT : ".$produit->tva);
			if ($l->type_objet == "PRODUIT" &&
				$l->id_objet == $produit->id &&
				floatval($l->tva) == floatval($produit->tva)) {
				$l->quantite = $l->quantite+1;
				$l->montant=$l->prix_u*$l->quantite;
				return true;
			}
			
//			echo "<pre>";
//			print_r ($this->TLigne[$i]);
//			echo "</pre>";
		} // for
		
		return false;
	}
	function add_reservation(&$db, $id_reservation){

		$resa = new TReservation;
		$resa->load($db, $id_reservation, true, true, true);
		$resa->calcule_prix();
		$this->acompte += $resa->acompte;

    /*
    AA Mise en place d'un tableau côté réservation 
    pour simplifier et réguler les transactions
    */

    $TResaLigne = $resa->get_all_lignes_for_fact();
   /* print "<pre>";
    print_r($resa->TAge);
    print_r($TResaLigne);
    print "</pre>";*/
    foreach ($TResaLigne as $key=>$TLigne) {
    	$iLigne = $this->add_ligne();
		  $ligne = & $this->TLigne[$iLigne];
		  
		  $ligne->rang=0;
  		$ligne->id_objet=(isset($TLigne['id']))?$TLigne['id']:0;
  		$ligne->type_objet=(isset($TLigne['type']))?$TLigne['type']:"DIVERS";
  		$ligne->libelle=$TLigne['libelle'];
  
  		$ligne->quantite=round($TLigne['qte'],2);
  		$ligne->prix_u=round($TLigne['prix_u'],2);
  		$ligne->montant=round($ligne->prix_u*$ligne->quantite,2);
  		$ligne->tva=(isset($TLigne['tva']))?$TLigne['tva']:0;
    }
    
/*
		$iLigne = $this->add_ligne();
		$ligne = & $this->TLigne[$iLigne];

		$ligne->rang=0;
		$ligne->id_objet=$resa->id;
		$ligne->type_objet="RESERVATION";
		$ligne->libelle=$resa->libelle;

		$ligne->quantite=$resa->nb_jour;
		$ligne->prix_u=$resa->prix;
		$ligne->montant=$ligne->prix_u*$ligne->quantite;
		$ligne->tva=19.6;*/
/*
		$nb=count($resa->TLienProduit);

		for ($i = 0; $i < $nb; $i++) {
				$l = & $resa->TLienProduit[$i];
				$produit = &$l->produit;

				$iLigne = $this->add_ligne();
				$ligne = & $this->TLigne[$iLigne];
				$ligne->rang=0;
				$ligne->id_objet=$produit->id;
				$ligne->type_objet="PRODUIT";
				$ligne->libelle=$produit->libelle;
				$ligne->quantite=$l->quantite;
				$ligne->prix_u=$l->prix;
				$ligne->montant=$l->quantite * $l->prix;
				$ligne->tva=$l->tva;
		}*/
		/*
		$age1 = $_SESSION[SESS_HOTEL]->get_parameter("taxe_sejour_age1");
		$nbp1 = $resa->get_nb_personne(false, 0, $age1);
		$mts1 = $resa->get_taxe_sejour_unit(0, $age1);
		$lib1 = "Taxe de séjour (moins de $age1 ans)";
		if ($nbp1 != "" && $nbp1 > 0) $this->_add_ligne_taxe_sejour($nbp1, $mts1, $lib1);
//echo "<hr>$age1 - $nbp1 - $mts1";
		$age2 = $_SESSION[SESS_HOTEL]->get_parameter("taxe_sejour_age2");
		$nbp2 = $resa->get_nb_personne(false, $age1, $age2);
		$mts2 = $resa->get_taxe_sejour_unit($age1, $age2);
		$lib2 = "Taxe de séjour (entre $age1 et $age2 ans)";
		if ($nbp2 != "" && $nbp2 > 0) $this->_add_ligne_taxe_sejour($nbp2, $mts2, $lib2);
//echo "<hr>$age2 - $nbp2 - $mts2";		
		$nbp3 = $resa->get_nb_personne(false, $age2);
		$mts3 = $resa->get_taxe_sejour_unit($age2);
		$lib3 = "Taxe de séjour (plus de $age2 ans)";
		if ($nbp3 != "" && $nbp3 > 0) $this->_add_ligne_taxe_sejour($nbp3, $mts3, $lib3);

*/
//echo "<hr>$age3 - $nbp3 - $mts3";		
		// Ajout d'une ligne Taxe de séjour
//		if ($resa->mt_taxe_sejour != 0) {
//			$iLigne = $this->add_ligne();
//			$ligne = & $this->TLigne[$iLigne];
//			$ligne->rang=0;
//			$ligne->id_objet=0;
//			$ligne->type_objet="TAXE_SEJOUR";
//			$ligne->libelle="Taxe de sejour";
//			$ligne->quantite=1;
//			$ligne->prix_u=$resa->mt_taxe_sejour;
//			$ligne->montant=$resa->mt_taxe_sejour;
//			$ligne->tva=0.0;
//		}

		// Ajout d'une ligne montant animaux
	/*	if ($resa->mt_animaux != 0) {
			$iLigne = $this->add_ligne();
			$ligne = & $this->TLigne[$iLigne];
			$ligne->rang=0;
			$ligne->id_objet=0;
			$ligne->type_objet="ANIMAUX";
			$ligne->libelle="Supplément animaux";
			$ligne->quantite=1;
			$ligne->prix_u=$resa->mt_animaux;
			$ligne->montant=$resa->mt_animaux;
			$ligne->tva=19.6;
		}
		*/
		// Ajout d'une ligne montant personne supplémentaire
		/*if ($resa->mt_personne_suppl != 0) {
			$iLigne = $this->add_ligne();
			$ligne = & $this->TLigne[$iLigne];
			$ligne->rang=0;
			$ligne->id_objet=0;
			$ligne->type_objet="PERSONNE_SUPPL";
			$ligne->libelle="Supplément personne supplémentaire";
			$ligne->quantite=1;
			$ligne->prix_u=$resa->mt_personne_suppl;
			$ligne->montant=$resa->mt_personne_suppl;
			$ligne->tva=19.6;
		}*/
/*
    if ($resa->frais_resa != 0) {
			$iLigne = $this->add_ligne();
			$ligne = & $this->TLigne[$iLigne];
			$ligne->rang=0;
			$ligne->id_objet=0;
			$ligne->type_objet="FRAIS_RESA";
			$ligne->libelle="Frais de réservation";
			$ligne->quantite=1;
			$ligne->prix_u=$resa->frais_resa;
			$ligne->montant=$resa->frais_resa;
			$ligne->tva=19.6;
		}
*/
		if($this->id_client==0){
			
			$this->id_client=$resa->id_client;
			$this->nom_client=$resa->client->get_client_name();
			$this->adresse_client=$resa->client->adresse;

		}
	}
	function _add_ligne_taxe_sejour ($qte, $mt, $lib) {
		$iLigne = $this->add_ligne();
		$ligne = & $this->TLigne[$iLigne];
		$ligne->rang=0;
		$ligne->id_objet=0;
		$ligne->type_objet="TAXE_SEJOUR";
		$ligne->libelle=$lib;
		$ligne->quantite=$qte;
		$ligne->prix_u=$mt;
		$ligne->montant=$ligne->quantite * $ligne->prix_u;
	}
	
	
	function delete_ligne($iTab){
		$this->TLigne[$iTab]->to_delete=true;
	//	print $iTab;
		//print_r($this->TLigne);
	}
	function add_ligne(){
		$nb=count($this->TLigne);
		$this->TLigne[$nb]=new TFactureLigne;
		$this->TLigne[$nb]->id_facture = $this->id;

		return $nb;
	}

	function load_ligne (&$db) {
		$this->TLigne = array();

		$r=new TRequete;
		$Tab=$r->liste_toute_ligne_par_facture($db, $this->id);

		$nb=count($Tab);
		for ($i = 0; $i < $nb; $i++) {
			$this->TLigne[$i]=new TFactureLigne;
			$this->TLigne[$i]->load($db, $Tab[$i]);
		} // for
	}
	function load(&$db,$id, $ligne){
		$sql = "SELECT id,numero,type,dt_facture,nom_client,adresse_client
				,taux_tva,total_ht,total_tva,total_ttc,total_negoce
				,remise,acompte,id_client,id_hotel,dt_cre,dt_maj
				FROM hot_facture WHERE id=$id";

		$db->Execute($sql);

		if($db->Get_recordCount()>0){
			$db->Get_line();

			$this->id=$id;
			$this->numero=$db->Get_field('numero');
			$this->type=$db->Get_field('type');
			$this->dt_facture=strtotime($db->Get_field('dt_facture'));
			$this->nom_client=$db->Get_field('nom_client');
			$this->adresse_client=$db->Get_field('adresse_client');
			$this->taux_tva=$db->Get_field('taux_tva');
			$this->total_ht=$db->Get_field('total_ht');
			$this->total_tva=$db->Get_field('total_tva');
			$this->total_ttc=$db->Get_field('total_ttc');
			$this->total_negoce=$db->Get_field('total_negoce');
			$this->remise=$db->Get_field('remise');
			$this->acompte=$db->Get_field('acompte');
			$this->id_client=$db->Get_field('id_client');
			$this->id_hotel=$db->Get_field('id_hotel');
			test_hotel_id($this->id_hotel);

			$this->dt_cre=strtotime($db->Get_field('dt_cre'));
			$this->dt_maj=strtotime($db->Get_field('dt_maj'));

			if($ligne) {
				$this->load_ligne($db);
			}
		}
	}

	function save(&$db){
		$query['id']=$this->id;
		$query['numero']=$this->numero;
		$query['type']=$this->type;
		$query['dt_facture']=date("Y-m-d H:i:s",$this->dt_facture);
		$query['nom_client']=$this->nom_client;
		$query['adresse_client']=$this->adresse_client;
		$query['taux_tva']=$this->taux_tva;
		$query['total_ht']=$this->total_ht;
		$query['total_tva']=$this->total_tva;
		$query['total_ttc']=$this->total_ttc;
		$query['total_negoce']=$this->total_negoce;
		$query['remise']=$this->remise;
		$query['acompte']=$this->acompte;
		$query['id_client']=$this->id_client;
		$query['id_hotel']=$this->id_hotel;

		$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
		$query['dt_maj']=date("Y-m-d H:i:s");

		$key[0]='id';

		if($this->id==0){
			$n=new TNumerotation;
			$this->numero = $n->get_numero($db, $this->type);
      $query['numero']=$this->numero;
      
      $this->get_newid($db);
			$query['id']=$this->id;
			$db->dbinsert('hot_facture',$query);
			//$this->inc_param_num($db);
			
			
		}
		else {
			$query['id']=$this->id;
			$db->dbupdate('hot_facture',$query,$key);
		}
		$this->save_ligne_facture($db);

	}
	function save_ligne_facture(&$db){
		$nb=count($this->TLigne);
		for ($i = 0; $i < $nb; $i++) {
			//$this->TLigne[$i] = new TFactureLigne;
			$this->TLigne[$i]->rang = $i+1;
			$this->TLigne[$i]->id_facture = $this->id;
			$this->TLigne[$i]->save($db);
			
			if ($this->TLigne[$i]->type_objet == "RESERVATION" && $this->type=='FACTURE' ) {
				$r = new TReservation ();
				$r->load($db, $this->TLigne[$i]->id_objet);
				$r->etat = "FACTUREE";
				$r->save($db);
			}
			
//			echo "<pre>";
//			print_r ($this->TLigne[$i]);
//			echo "</pre>";
		} // for
	}

	function delete(&$db) {
		if($this->id!=0){
			$db->dbdelete('hot_facture',array("id"=>$this->id),array(0=>'id'));
			$db->dbdelete('hot_facture_ligne',array("id_facture"=>$this->id),array(0=>'id_facture'));
		}
	}

	function get_newid(&$db) {
		$sql="SELECT max(id) as 'maxi' FROM hot_facture";
		$db->Execute($sql);
		$db->Get_line();
		$this->id = (double)$db->Get_field('maxi')+1;
	}

	function get_dtcre(){
		return date("d/m/Y",$this->dt_cre);
	}

	function get_dtmaj(){
		return date("d/m/Y",$this->dt_maj);
	}

	function get_dtfacture(){
		return date("d/m/Y",$this->dt_facture);
	}

	function set_dtcre($date) {
		list($d,$m,$y) = explode("/",$date);
		$this->dt_cre = mktime(0,0,0,$m,$d,$y);
	}

	function set_dtmaj($date) {
		list($d,$m,$y) = explode("/",$date);
		$this->dt_maj = mktime(0,0,0,$m,$d,$y);
	}

	function set_dtfacture($date) {
		list($d,$m,$y) = explode("/",$date);
		$this->dt_facture = mktime(0,0,0,$m,$d,$y);
	}

	function init_numero(&$db, $no_inc=false) {
		/*$r = new TRequete();
		
		$param=new TParam();
		$param->load($db,$this->type."_PREFIXE");

		$prefixe = $param->get_valeur();

		//$param=new TParam();
		$param->load($db,$this->type."_NB_CAR");

		$nbcar = $param->get_valeur();

		//$param=new TParam();
		$param->load($db,$this->type."_NUM");

		$num = $param->get_valeur();
		
    */
    
    $n=new TNumerotation;
    
    $this->numero = $n->get_numero($db, $this->type,$no_inc);
	}

	function check_num_facture (&$db) {
		$req = new TRequete();
		$TFacture = $req->liste_toute_facture_par_numero($db,$this->numero);

		if ($req->nb_resultat > 0 && $TFacture[0]!=$this->id) {
			return false;
		}

		return true;
	}
	
	function reinit_id_ligne() {
		$nb = count($this->TLigne);
		for ($i = 0; $i < $nb; $i++) {
			$this->TLigne[$i]->id = 0;
		}
	}

	function inc_param_num (&$db) {
		$param=new TParam();
		$param->load($db,$this->type."_NUM");
		$param->set_valeur($param->get_valeur()+1);
		$param->save($db);
	}
}

class TFactureLigne{
	/**
     * Constructor
     * @access protected
     */

	function TFactureLigne() {
		$this->id=0;
		$this->id_facture=0;
		$this->rang=0;
		$this->id_objet=0;
		$this->type_objet="";
		$this->libelle="";
		$this->quantite=0.0;
		$this->prix_u=0.0;
		$this->montant=0.0;
		$this->tva=0.0;
		
//		$this->TTaux_tva = array(
//			"0.00" => "TVA Frontalière - 0.00 %"
//			,"19.6" => "Métropole taux normal - 19.60 %"
//			,"5.50" => "Métropole taux réduit - 5.50 %"
//			,"8.50" => "DOM taux normal - 8.50 %"
//			,"3.50" => "DOM taux réduit - 3.50 %"
//		);
		
		$this->TTaux_tva = array(
			"0.00" => "0.00"
			,"19.6" => "19.60"
			,"5.50" => "5.50"
			,"8.50" => "8.50"
			,"3.50" => "3.50"
		);

		$this->dt_cre=time();
		$this->dt_maj=time();

		$this->objet="";
		$this->to_delete = false;
	}

	function load(&$db,$id,$objet=false){
		$sql = "SELECT id,id_facture,rang,id_objet,type_objet
				,libelle,quantite,prix_u,montant,tva,dt_cre,dt_maj
				FROM hot_facture_ligne WHERE id=$id";

		$db->Execute($sql);

		if($db->Get_recordCount()>0){
			$db->Get_line();

			$this->id=$id;
			$this->id_facture=$db->Get_field('id_facture');
			$this->rang=$db->Get_field('rang');
			$this->id_objet=$db->Get_field('id_objet');
			$this->type_objet=$db->Get_field('type_objet');
			$this->libelle=$db->Get_field('libelle');
			$this->quantite=$db->Get_field('quantite');
			$this->prix_u=$db->Get_field('prix_u');
			$this->montant=$db->Get_field('montant');
			$this->tva=$db->Get_field('tva');

			$this->dt_cre=strtotime($db->Get_field('dt_cre'));
			$this->dt_maj=strtotime($db->Get_field('dt_maj'));

			if($objet) {
				$this->load_objet($db);
			}
		}
	}

	function save(&$db){

		if($this->to_delete){
			$this->delete($db);
		}
		else{


			$query['id']=$this->id;
			$query['id_facture']=$this->id_facture;
			$query['rang']=$this->rang;
			$query['id_objet']=$this->id_objet;
			$query['type_objet']=$this->type_objet;
			$query['libelle']=$this->libelle;
			$query['quantite']=$this->quantite;
			$query['prix_u']=$this->prix_u;
			$query['montant']=$this->montant;
			$query['tva']=$this->tva;

			$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
			$query['dt_maj']=date("Y-m-d H:i:s");

			$key[0]='id';

			if($this->id==0){
				$this->get_newid($db);
				$query['id']=$this->id;
				$db->dbinsert('hot_facture_ligne',$query);
			}
			else {
				$query['id']=$this->id;
				$db->dbupdate('hot_facture_ligne',$query,$key);
			}

		}

	}

	function delete(&$db) {
		if($this->id!=0){
			$db->dbdelete('hot_facture_ligne',array("id"=>$this->id),array(0=>'id'));
		}
	}

	function get_newid(&$db) {
		$sql="SELECT max(id) as 'maxi' FROM hot_facture_ligne";
		$db->Execute($sql);
		$db->Get_line();
		$this->id = (double)$db->Get_field('maxi')+1;
	}

	function get_dtcre(){
		return date("d/m/Y",$this->dt_cre);
	}

	function get_dtmaj(){
		return date("d/m/Y",$this->dt_maj);
	}

	function set_dtcre($date) {
		list($d,$m,$y) = explode("/",$date);
		$this->dt_cre = mktime(0,0,0,$m,$d,$y);
	}

	function set_dtmaj($date) {
		list($d,$m,$y) = explode("/",$date);
		$this->dt_maj = mktime(0,0,0,$m,$d,$y);
	}
/*
	function load_objet (&$db) {
		switch ($this->type_objet) {
			case "RESERVATION":
				$this->objet = new TReservation();
				$this->objet->load($db,$this->id_objet);
				break;
			case "PRODUIT":
				$this->objet = new TProduit();
				$this->objet->load($db,$this->id_objet);
				break;
			default:
				break;
		}

	}*/
}

?>
