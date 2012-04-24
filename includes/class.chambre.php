<?php


class TChambre{
	/**
     * Constructor
     * @access protected
     */


	function TChambre(){
		/*** Informations relatives à l'hotel ***/
		$this->id=0;
		$this->nb_lit="";
		$this->prestation="";
		$this->orientation="";
		$this->situation="";
		$this->prix=""; // ou prix par défaut
		$this->num="";
		$this->TPrixSaison=array();

		$this->dt_cre=time();
		$this->dt_maj=time();

		/*** Informations spécifiques pour chaque chambre
		Elles permettront de lier chaque hotel à des catégories de chambres
		qui elles mêmes sont liées à des numéros de chambre ***/
		$this->id_hotel=0;
		$this->id_categorie=0;
		
		$this->categorie = null;

		$this->dt_deb_for_resa=0;
		$this->dt_fin_for_resa=0;
	
		$this->TCategorie=array();
		$this->TReservation=array();

		$this->prix_of_categorie = 1; // si à 1 ce sont les prix de la catégorie qui seront  exploité pour la chambre
	}
  function add_prix(){
    $i = count($this->TPrixSaison);
    $this->TPrixSaison[$i]=new TPrixsaisonChambre;
    $this->TPrixSaison[$i]->id_chambre = $this->id;
    
    return $i;
  }
  function del_prix($i){
    $this->TPrixSaison[$i]->to_delete=true;
  }
  function delete_all_prixsaison(){
  
      $nb = count($this->TPrixSaison);
      for ($i=0; $i<$nb; $i++) {
        $this->TPrixSaison[$i]->to_delete=true; 	
      }
      
  }
	function load(&$db,$id, $annexe=true, $reservation=true, $prix_saison=true){

		$db->Execute("SELECT id,id_hotel,id_categorie,nb_lit,prestation,orientation,situation,prix,num,dt_cre,dt_maj
		,prix_of_categorie
		FROM hot_chambre
		WHERE id=$id ");
		if($db->Get_recordCount()>0){
			$db->Get_line();

			$this->id=$db->Get_field('id');
			$this->id_hotel=$db->Get_field('id_hotel');
			test_hotel_id($this->id_hotel);
			$this->id_categorie=$db->Get_field('id_categorie');
			$this->nb_lit=$db->Get_field('nb_lit');
			$this->prestation=$db->Get_field('prestation');
			$this->orientation=$db->Get_field('orientation');
			$this->situation=$db->Get_field('situation');
			$this->prix=$db->Get_field('prix');
			$this->num=$db->Get_field('num');
			$this->prix_of_categorie = $db->Get_field('prix_of_categorie');

			$this->dt_cre=strtotime($db->Get_field('dt_cre'));
			$this->dt_maj=strtotime($db->Get_field('dt_maj'));


			if($annexe || $this->prix_of_categorie==1){ // si on a besoin de la catégorie
				$this->load_categorie($db);
			}
			if($reservation){
				$this->load_reservation($db);
			}
		      if ($prix_saison) {
		      	$this->load_prixsaison($db);
		      }
      
      return true;
		}
		else {
			return false;
		}


	}
	
	function get_prix_for_resa($time_deb, $time_end){
	/*
	Pour une réservation, les prix peuvent varier selon le jour
	Il faut donc un tableau récapitulant les prix pour chaque jour
	*/
		$TPrix =array();
		// parcours de chaque jour

		$time_cur = $time_deb;
		$time_end+=3600; // marge à cause de l'heure d'hiver

		$i=0;
		$prix_old = 0;
		$first=true;$old_time=0;
		//print "Début $time_deb ".date("d/m/Y H:i:s", $time_deb)." à $time_end ".date("d/m/Y H:i:s", $time_end)."<br>";
		while($time_cur<=$time_end && $i<1000){ // tant qu'on est dans la plage
			$prix = $this->get_prix($time_cur);
			//print "Temps testé (".($time_cur-$old_time).") $time_cur ".date("d/m/Y H:i:s", $time_cur)." pour $prix<br>\n";
			
			if($prix!=$prix_old){
			// on change de prix
				if(!$first){	
					$row['time_fin'] = $time_cur-86400;
					//$row['dt_fin']=date("d/m/Y", $row['time_fin']);
					$TPrix[]=$row;
				}
				$row=array();
				$prix_old = $prix;
				$first=false;
				$row['time_deb'] = $time_cur;
				//$row['dt_deb']=date("d/m/Y", $row['time_deb']);
				$row['prix'] = $prix;
				$row['nb_jour']=0;

			}
			$row['nb_jour']++;

			$old_time = $time_cur;
			$time_cur+=86400; //+1jour
			$i++;
		}
		$row['time_fin'] = $time_cur-86400;
		//$row['dt_fin']=date("d/m/Y",$row['time_fin']);
		$TPrix[]=$row;

		return $TPrix;
	}

	function get_prix($time=0){
	    /*
	      Alexis ALGOUD
	      récupère le prix selon la saison  	
	    */
	    if( $this->prix_of_categorie==1 ){
		$this->prix = $this->categorie->get_prix($time);
	    }
	    else{

		    if($time>0){
		      $nb=count($this->TPrixSaison);
		      for($i=0;$i<$nb;$i++){
			  if($this->TPrixSaison[$i]->time_in($time)){
			    return $this->TPrixSaison[$i]->prix;
			  }
			      
		      }
		    }
	    }
	    return $this->prix;
	}
	
	
	function load_prixsaison(&$db){
	    $this->TPrixSaison=array();
	    $r=new TRequete;
	    $Tab = $r->get_prixsaison_for_chambre($db, $this->id);
	    $nb = count($Tab);
		for ($i = 0; $i < $nb; $i++) {
			$this->TPrixSaison[$i]=new TPrixsaisonChambre;
			$this->TPrixSaison[$i]->load($db, $Tab[$i]);
		} // for
	  }
	function load_reservation(&$db){
		$this->TReservation=array();
		$r = new TRequete;
		$Tab = $r->liste_toute_reservation_par_chambre($db,$this->id,$this->dt_deb_for_resa,$this->dt_fin_for_resa);
		$nb = count($Tab);
		for ($i = 0; $i < $nb; $i++) {
			$this->TReservation[$i]=new TReservation;
			$this->TReservation[$i]->load($db, $Tab[$i],true);
		} // for
	}
	function load_categorie(&$db){
		$this->TCategorie=array();
		$r=new TRequete;
		$this->TCategorie = $r->liste_toute_categorie($db,$this->id_hotel);
		$this->categorie = new TCategorie;
		$this->categorie->load($db,$this->id_categorie);
	}
	function save(&$db){

			$query['prix']=$this->prix;
			$query['id_hotel']=$this->id_hotel;
			$query['id_categorie']=$this->id_categorie;
			$query['nb_lit']=$this->nb_lit;
			$query['prestation']=$this->prestation;
			$query['orientation']=$this->orientation;
			$query['situation']=$this->situation;
			//$query['prix_categ']=$this->prix_categ;
			$query['num']=$this->num;
			$query['prix_of_categorie']=$this->prix_of_categorie;

			$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
			$query['dt_maj']=date("Y-m-d H:i:s");

			$key[0]='id';

			if($this->id==0){
				$this->get_newid($db);
				$query['id']=$this->id;
				$db->dbinsert('hot_chambre',$query);
			}
			else {
				$query['id']=$this->id;
				$db->dbupdate('hot_chambre',$query,$key);
			}

      $this->save_prixsaison($db);

	}
  function save_prixsaison(&$db){
    $nb=count($this->TPrixSaison);
    for($i=0;$i<$nb;$i++){
      $this->TPrixSaison[$i]->id_chambre=$this->id;
      $this->TPrixSaison[$i]->save($db);
    }
  
  }

	function delete(&$db){
		if($this->id!=0){
			$db->dbdelete('hot_chambre',array("id"=>$this->id),array(0=>'id'));
			$db->dbdelete('hot_chambre_prixsaison',array("id_chambre"=>$this->id),array(0=>'id_chambre'));
		}
	}

	function get_newid(&$db){

		$sql="SELECT max(id) as 'maxi' FROM hot_chambre";
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

	function get_reservation ($time) {

		$nb=count($this->TReservation);
		for ($i = 0; $i < $nb; $i++) {
			//print $this->TReservation[$i]->id." - ".$this->TReservation[$i]->dt_deb."<=$time && ".$this->TReservation[$i]->dt_fin.">=$time<br>";
			//print $this->TReservation[$i]->id." - ".$this->TReservation[$i]->get_dtdeb()."<=".date('d/m/Y',$time)." && ".$this->TReservation[$i]->get_dtfin().">=".date('d/m/Y',$time)."<br>";
			if($this->TReservation[$i]->dt_deb<=$time && $this->TReservation[$i]->dt_fin>=$time){
				//print "true<br>";
				return $this->TReservation[$i];
			}

		} // for

		return "";
	}

}



/*
  Alexis ALGOUD
	Prix par saison pour une chambre, idem cat
*/
class TPrixsaisonChambre{

	function TPrixsaisonChambre(){
		$this->id=0;
		$this->id_chambre=0;
		$this->prix=0.0;
		$this->dt_deb=time();
		$this->dt_fin=time();
		
		$this->dt_cre=time();
		$this->dt_maj=time();
    
    $this->to_delete=false;
	}
  function time_in($time){
    
    if($time>=$this->dt_deb && $time<=$this->dt_fin){
      return true;
    }
    else{
      return false;
    }
  
  }

	function load(&$db,$id){

		$db->Execute("SELECT id,id_chambre,prix,dt_deb,dt_fin,dt_cre,dt_maj
		FROM hot_chambre_prixsaison
		WHERE id=$id ");
		if($db->Get_recordCount()>0){
			$db->Get_line();

			$this->id=$db->Get_field('id');
			$this->id_chambre=$db->Get_field('id_chambre');
			$this->prix=$db->Get_field('prix');
			
			$this->dt_cre=strtotime($db->Get_field('dt_cre'));
			$this->dt_maj=strtotime($db->Get_field('dt_maj'));
      $this->dt_deb=strtotime($db->Get_field('dt_deb'));
			$this->dt_fin=strtotime($db->Get_field('dt_fin'));


			return true;
		}
		else {
			return false;
		}


	}

	function save(&$db){

    if($this->to_delete){
      $this->delete($db);
    }
    else{
    
    	$query['id']=$this->id;
			$query['id_chambre']=$this->id_chambre;
			$query['prix']=$this->prix;
			
      $query['dt_deb']=date("Y-m-d H:i:s",$this->dt_deb);
      $query['dt_fin']=date("Y-m-d H:i:s",$this->dt_fin);

			$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
			$query['dt_maj']=date("Y-m-d H:i:s");

			$key[0]='id';

			if($this->id==0){
				$this->get_newid($db);
				$query['id']=$this->id;
				$db->dbinsert('hot_chambre_prixsaison',$query);
			}
			else {
				$query['id']=$this->id;
				$db->dbupdate('hot_chambre_prixsaison',$query,$key);
			}


    }
		
	}


	function delete(&$db){
		if($this->id!=0){
			$db->dbdelete('hot_chambre_prixsaison',array("id"=>$this->id),array(0=>'id'));
		}
	}

	function get_newid(&$db){

		$sql="SELECT max(id) as 'maxi' FROM hot_chambre_prixsaison";
		$db->Execute($sql);
		$db->Get_line();
		$this->id = (double)$db->Get_field('maxi')+1;

	}

	function get_dtcre(){
		return date("d/m/Y",$this->dt_cre);
	}	
  function get_dtdeb(){
		return date("d/m/Y",$this->dt_deb);
	}	
  function get_dtfin(){
		return date("d/m/Y",$this->dt_fin);
	}
	function get_dtmaj(){
		return date("d/m/Y",$this->dt_maj);
	}

	function set_dtcre($date){
		list($d,$m,$y) = explode("/",$date);
  	$this->dt_cre = mktime(0,0,0,$m,$d,$y);
  }
	function set_dtdeb($date){
		list($d,$m,$y) = explode("/",$date);
  	$this->dt_deb = mktime(0,0,0,$m,$d,$y);
  }
	function set_dtfin($date){
		list($d,$m,$y) = explode("/",$date);
  	$this->dt_fin = mktime(0,0,0,$m,$d,$y);
  }

	function set_dtmaj($date){

		list($d,$m,$y) = explode("/",$date);

		$this->dt_maj = mktime(0,0,0,$m,$d,$y);

	}



}
