<?php

class TProduit{
	/**
     * Constructor
     * @access protected
     */
	function TProduit(){
		/**
		 * Informations relatives au produit 
		 */
		$this->id=0;
		$this->libelle="";
		$this->description="";
		$this->prix="";
		$this->tva='19.60';

		$this->dt_cre=time();
		$this->dt_maj=time();
		
		/*$this->TTaux_tva = array(
			'0.00' => "0.00"
			,'19.60' => "19.60"
			,'5.50' => "5.50"
			,'8.50' => "8.50"
			,'3.50' => "3.50"
		);*/
		$this->TTaux_tva = array(
			'0' => "0.00"
			,'19.6' => "19.60"
			,'5.5' => "5.50"
			,'8.5' => "8.50"
			,'3.5' => "3.50"
		);
		
		
		$this->TLienReservation = array();
		
		$this->TRegle=array(); // tableau des règles automatique pour les inserssions en résa
		$this->TCategorie=array();
		/**
		 * Maxime KOHLHAAS
		 * 05/04/2007 19:05:20
		 * Les produit sont relatif à l'hotel
		 **/
		$this->id_hotel=0;
	}

	function load(&$db,$id, $annexe=false){
		$sql="SELECT id, id_hotel, libelle, description, prix, tva, dt_cre, dt_maj
				FROM hot_produit
				WHERE id=$id ";

		$db->Execute($sql);
		if($db->Get_recordCount()>0){
			$db->Get_line();

			$this->id=$db->Get_field('id');
			$this->id_hotel=$db->Get_field('id_hotel');
			test_hotel_id($this->id_hotel);
			
			$this->libelle=$db->Get_field('libelle');
			$this->description=$db->Get_field('description');
			$this->prix=$db->Get_field('prix');
			$this->tva=$db->Get_field('tva');

			$this->dt_cre=strtotime($db->Get_field('dt_cre'));
			$this->dt_maj=strtotime($db->Get_field('dt_maj'));
		
		}
		
		if($annexe){
			$this->load_reservation($db);
			$this->load_regle($db);
		}
		
		
	}
	
	function add_regle(){
    $i=count( $this->TRegle);
    $this->TRegle[$i]=new TRegle;
    
    return $i;
  }
	function del_regle($i){
    $this->TRegle[$i]->to_delete=true;
  }
	function load_regle(&$db){
    
    $r=new TRequete;
    $Tab = $r->get_regle_for_produit($db, $this->id);
    $this->TRegle=array();
    foreach ($Tab as $i=>$id) {
    	$this->TRegle[$i]=new TRegle;
    	$this->TRegle[$i]->load($db, $id);
    }
  
    $this->load_categorie($db);  
  }
	
	function load_categorie(&$db){
    $r=new TRequete;
    //$this->TCategorie = array_merge(array("0"=>"----------") , $r->liste_toute_categorie($db,$this->id_hotel));
    $this->TCategorie = $r->liste_toute_categorie($db,$this->id_hotel);
  }
	
	function save_regle(&$db){
    $nb=count( $this->TRegle);
    for($i=0; $i<$nb; $i++){
      $this->TRegle[$i]->id_produit = $this->id;
      $this->TRegle[$i]->id_hotel = $this->id_hotel;
      $this->TRegle[$i]->save($db);
    }
  
  }
	
	function load_reservation (&$db) {
		$this->TLienReservation = array();
		
		$r=new TRequete;
		$Tab=$r->liste_toute_reservation_par_produit($db, $this->id);
		
		$nb=count($Tab);
		for ($i = 0; $i < $nb; $i++) {
			$this->TLienReservation[$i]=new TLienReservationProduit;
			$this->TLienReservation[$i]->load($db, $Tab[$i]);
		} // for
		
		
	}
	function save(&$db){

			$query['id']=$this->id;
			$query['id_hotel']=$this->id_hotel;
			$query['libelle']=$this->libelle;
			$query['description']=$this->description;
			$query['prix']=$this->prix;
			$query['tva']=(double)$this->tva;

			$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
			$query['dt_maj']=date("Y-m-d H:i:s");

			$key[0]='id';

			if($this->id==0){
				$this->get_newid($db);
				$query['id']=$this->id;
				$db->dbinsert('hot_produit',$query);
			}
			else {
				$query['id']=$this->id;
				$db->dbupdate('hot_produit',$query,$key);
			}
			
			$this->save_regle($db);
	}

	function delete(&$db){
		if($this->id!=0){
			$db->dbdelete('hot_produit',array("id"=>$this->id),array(0=>'id'));
		}
	}

	function get_newid(&$db){
		$sql="SELECT max(id) as 'maxi' FROM hot_produit";
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

	function set_dtcre($date){
		list($d,$m,$y) = explode("/",$date);
		$this->dt_cre = mktime(0,0,0,$m,$d,$y);
	}

	function set_dtmaj($date){
		list($d,$m,$y) = explode("/",$date);
		$this->dt_maj = mktime(0,0,0,$m,$d,$y);
	}
}


class TLienReservationProduit{
	/**
     * Constructor
     * @access protected
     */


	function TLienReservationProduit(){
		/*** Informations relatives à l'hotel ***/
		$this->id=0;
		$this->id_produit=0;
		$this->id_reservation=0;
		$this->prix=0;
		$this->quantite=1;
		
		$this->produit = new TProduit;
		$this->reservation = new TReservation ;
		
		$this->dt_cre=time();
		$this->dt_maj=time();


		/**
		 * Alexis ALGOUD
		 * 08/10/2006 21:47:39
		 * Les catégories sont lié à 1..1 hotel
		 * Ici il n'y a pas de lien chambre (cf. class chambre)
		 **/
		$this->id_hotel=0;
		
		$this->to_delete=false;
	}


	function load(&$db,$id, $produit=false, $reservation=false, $regle=true){

		$db->Execute("SELECT id,id_produit,id_reservation,prix,quantite,dt_cre,dt_maj
		FROM hot_lien_produit_reservation
		WHERE id=$id ");
		if($db->Get_recordCount()>0){
			$db->Get_line();

			$this->id=$db->Get_field('id');
			$this->id_produit=$db->Get_field('id_produit');
			$this->id_reservation=$db->Get_field('id_reservation');
			$this->prix=$db->Get_field('prix');
			$this->quantite=$db->Get_field('quantite');
			

			$this->dt_cre=strtotime($db->Get_field('dt_cre'));
			$this->dt_maj=strtotime($db->Get_field('dt_maj'));

			if($produit){
				$this->load_produit($db);
			}
			if($reservation){
				$this->load_reservation($db);
			}
      
			return true;
		}
		else {
			return false;
		}


	}
	

	
	function load_produit(&$db){
		$this->produit->load($db, $this->id_produit);
	}
	function load_reservation(&$db){
		$this->reservation->load($db, $this->id_reservation);
		
	}
	function save(&$db){
		if($this->to_delete){
			$this->delete($db);		
		}
		else{
			
			$query['id']=$this->id;
			$query['id_produit']=$this->id_produit;
			$query['id_reservation']=$this->id_reservation;
			$query['prix']=$this->prix;
			$query['quantite']=$this->quantite;
			
			$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
			$query['dt_maj']=date("Y-m-d H:i:s");

			$key[0]='id';

			if($this->id==0){
				$this->get_newid($db);
				$query['id']=$this->id;
				$db->dbinsert('hot_lien_produit_reservation',$query);
			}
			else {
				$query['id']=$this->id;
				$db->dbupdate('hot_lien_produit_reservation',$query,$key);
			}

      

		}
		
	}


	function delete(&$db){
		if($this->id!=0){
			$db->dbdelete('hot_lien_produit_reservation',array("id"=>$this->id),array(0=>'id'));
		}
	}

	function get_newid(&$db){

		$sql="SELECT max(id) as 'maxi' FROM hot_lien_produit_reservation";
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

	function set_dtcre($date){
		list($d,$m,$y) = explode("/",$date);
		$this->dt_cre = mktime(0,0,0,$m,$d,$y);
	}

	function set_dtmaj($date){
		list($d,$m,$y) = explode("/",$date);
		$this->dt_maj = mktime(0,0,0,$m,$d,$y);
	}
}

class TRegle{
	/**
     * Constructor
     * @access protected
     */


	function TRegle(){
		/*** Informations relatives à l'hotel ***/
		$this->id=0;
		$this->id_produit=0;
		$this->id_hotel=0;
		
    $this->nb_personne=0;
		$this->id_categorie=0;
		
		$this->nuit_min = 0;
		$this->nuit_max = 0;
		
		$this->dt_deb=time();
		$this->dt_fin=time();

		$this->dt_cre=time();
		$this->dt_maj=time();
		
		$this->to_delete=false;
		
	  $this->produit = null;
	}


	function load(&$db,$id, $produit=false){

		$db->Execute("SELECT id,id_hotel,id_produit,id_categorie, nb_personne, dt_deb, dt_fin
    ,nuit_min,nuit_max
    ,dt_cre,dt_maj
		FROM hot_regle
		WHERE id=$id ");
		if($db->Get_recordCount()>0){
			$db->Get_line();

			$this->id=$db->Get_field('id');
			$this->id_produit=$db->Get_field('id_produit');
			$this->id_hotel=$db->Get_field('id_hotel');
			$this->id_categorie=$db->Get_field('id_categorie');
			$this->nb_personne=$db->Get_field('nb_personne');
			$this->nuit_min=$db->Get_field('nuit_min');
			$this->nuit_max=$db->Get_field('nuit_max');
			
      $this->dt_fin=strtotime($db->Get_field('dt_fin'));
			$this->dt_deb=strtotime($db->Get_field('dt_deb'));

			$this->dt_cre=strtotime($db->Get_field('dt_cre'));
			$this->dt_maj=strtotime($db->Get_field('dt_maj'));

			if($produit){
				$this->load_produit($db);
			}
			
			return true;
		}
		else {
			return false;
		}


	}
	function load_produit(&$db){
	  $this->produit=new TProduit;
		$this->produit->load($db, $this->id_produit);
	}
	function save(&$db){
		if($this->to_delete){
			$this->delete($db);		
		}
		else{
			
			$query['id']=$this->id;
			$query['id_produit']=$this->id_produit;
			$query['id_hotel']=$this->id_hotel;
			
			$query['id_categorie']=$this->id_categorie;
			$query['nb_personne']=$this->nb_personne;
			
			$query['nuit_min']=$this->nuit_min;
			$query['nuit_max']=$this->nuit_max;
			
			$query['dt_deb']=date("Y-m-d H:i:s",$this->dt_deb);
			$query['dt_fin']=date("Y-m-d H:i:s",$this->dt_fin);
			
			$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
			$query['dt_maj']=date("Y-m-d H:i:s");

			$key[0]='id';

			if($this->id==0){
				$this->get_newid($db);
				$query['id']=$this->id;
				$db->dbinsert('hot_regle',$query);
			}
			else {
				$query['id']=$this->id;
				$db->dbupdate('hot_regle',$query,$key);
			}


		}
		
	}


	function delete(&$db){
		if($this->id!=0){
			$db->dbdelete('hot_regle',array("id"=>$this->id),array(0=>'id'));
		}
	}

	function get_newid(&$db){

		$sql="SELECT max(id) as 'maxi' FROM hot_regle";
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

	function set_dtcre($date){
		list($d,$m,$y) = explode("/",$date);
		$this->dt_cre = mktime(0,0,0,$m,$d,$y);
	}

	function set_dtmaj($date){
		list($d,$m,$y) = explode("/",$date);
		$this->dt_maj = mktime(0,0,0,$m,$d,$y);
	}


	function get_dtfin(){
		return date("d/m/Y",$this->dt_fin);
	}

	function get_dtdeb(){
		return date("d/m/Y",$this->dt_deb);
	}

	function set_dtfin($date){
		list($d,$m,$y) = explode("/",$date);
		$this->dt_fin = mktime(0,0,0,$m,$d,$y);
	}

	function set_dtdeb($date){
		list($d,$m,$y) = explode("/",$date);
		$this->dt_deb = mktime(0,0,0,$m,$d,$y);
	}


}
?>