<?php


class TCategorie{
	/**
     * Constructor
     * @access protected
     */


	function TCategorie(){
		/*** Informations relatives à l'hotel ***/
		$this->id=0;
		$this->libelle="";
		$this->type="";
		$this->prestation="";
		$this->definition="";
		$this->tarif_defaut="";
		
		$this->nb_limite_personne=0;
		$this->montant_personne=0.0;
		$this->montant_animaux=0.0;

		    $this->TAge=array(
		      0=>array("min"=>null,"max"=>2,"percent"=>0,"montant"=>0)
		      ,1=>array("min"=>2,"max"=>8,"percent"=>50,"montant"=>0)
		      ,2=>array("min"=>8,"max"=>null,"percent"=>100,"montant"=>0,"hidden"=>true)
		    );

		$this->dt_cre=time();
		$this->dt_maj=time();

    		$this->frais_resa = 0;

		/**
		 * Alexis ALGOUD
		 * 08/10/2006 21:47:39
		 * Les catégories sont lié à 1..1 hotel
		 * Ici il n'y a pas de lien chambre (cf. class chambre)
		 **/
		$this->id_hotel=0;
		$this->TPrixSaison=array();
	}

	function get_prix($time=0){
	    /*
	      Alexis ALGOUD
	      récupère le prix selon la saison  	
	    */
	    $this->prix = $this->tarif_defaut;

	    if($time>0){
	      $nb=count($this->TPrixSaison);
	      for($i=0;$i<$nb;$i++){
		  if($this->TPrixSaison[$i]->time_in($time)){
		    return $this->TPrixSaison[$i]->prix;
		  }
		      
	      }
	    }
	    return $this->prix;
	}

  function add_prix(){
    $i = count($this->TPrixSaison);
    $this->TPrixSaison[$i]=new TPrixsaison;
    $this->TPrixSaison[$i]->id_chambre = $this->id;
  }
  function del_prix($i){
    $this->TPrixSaison[$i]->to_delete=true;
  }
	function load(&$db,$id){

		$db->Execute("SELECT id,id_hotel,libelle,type,prestation,definition,tarif_defaut,
				nb_limite_personne,montant_personne,montant_animaux,dt_cre,dt_maj
				,frais_resa,TAge
		FROM hot_categorie
		WHERE id=$id ");
		if($db->Get_recordCount()>0){
			$db->Get_line();

			$this->id=$db->Get_field('id');
			$this->id_hotel=$db->Get_field('id_hotel');
			test_hotel_id($this->id_hotel);
			
			$this->libelle=$db->Get_field('libelle');
			$this->type=$db->Get_field('type');
			$this->prestation=$db->Get_field('prestation');
			$this->definition=$db->Get_field('definition');
			$this->tarif_defaut=$db->Get_field('tarif_defaut');
			$this->frais_resa=$db->Get_field('frais_resa');
			
			$res = unserialize($db->Get_field('TAge'));
			if(is_array($res)){
				$this->TAge=$res;
			}
			
    	$this->nb_limite_personne=$db->Get_field('nb_limite_personne');
			$this->montant_personne=$db->Get_field('montant_personne');
			$this->montant_animaux=$db->Get_field('montant_animaux');

			$this->dt_cre=strtotime($db->Get_field('dt_cre'));
			$this->dt_maj=strtotime($db->Get_field('dt_maj'));

      $this->load_prixsaison($db);

			return true;
		}
		else {
			return false;
		}


	}
  function load_prixsaison(&$db){
    $this->TPrixSaison=array();
    $r=new TRequete;
    $Tab = $r->get_prixsaison_for_categorie($db, $this->id);
    $nb = count($Tab);
		for ($i = 0; $i < $nb; $i++) {
			$this->TPrixSaison[$i]=new TPrixsaison;
			$this->TPrixSaison[$i]->load($db, $Tab[$i]);
		} // for
  }
	function save(&$db){

			$query['id']=$this->id;
			$query['id_hotel']=$this->id_hotel;
			$query['libelle']=$this->libelle;
			$query['type']=$this->type;
			$query['prestation']=$this->prestation;
			$query['definition']=$this->definition;
			$query['tarif_defaut']=$this->tarif_defaut;
			//$query['id_chambre']=$this->id_chambre;
			$query['frais_resa']=$this->frais_resa;
			
			
			if(isset($this->TAge[2]) && $this->TAge[2]['hidden']==true){
          if($this->TAge[1]['max']<$this->TAge[1]['min'])$this->TAge[1]['max']=99;
          
          $this->TAge[2]['min']=$this->TAge[1]['max'];
      }
			
			$query['TAge']=serialize($this->TAge);
			
			
			$query['nb_limite_personne']=$this->nb_limite_personne;
			$query['montant_personne']=$this->montant_personne;
			$query['montant_animaux']=$this->montant_animaux;
			

			/*if($this->libelle){
	   			$query["idx"]=(ctype_alpha(strtoupper($this->libelle[0]))?strtoupper($this->libelle[0]):'0');
			}
			else {
			   		$query["idx"]="0";
			}*/

			$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
			$query['dt_maj']=date("Y-m-d H:i:s");

			$key[0]='id';

			if($this->id==0){
				$this->get_newid($db);
				$query['id']=$this->id;
				$db->dbinsert('hot_categorie',$query);
			}
			else {
				$query['id']=$this->id;
				$db->dbupdate('hot_categorie',$query,$key);
			}

      $this->save_prixsaison($db);

	}
  function save_prixsaison(&$db){
      $nb=count($this->TPrixSaison);
      for($i=0;$i<$nb;$i++){
        $this->TPrixSaison[$i]->id_categorie=$this->id;
        $this->TPrixSaison[$i]->save($db);
      }
    
  }

	function delete(&$db){
		if($this->id!=0){
			$db->dbdelete('hot_categorie',array("id"=>$this->id),array(0=>'id'));
		}
	}

	function get_newid(&$db){
		$sql="SELECT max(id) as 'maxi' FROM hot_categorie";
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

/*
  Alexis ALGOUD
	Prix par saison pour une chambre, idem cat
*/
class TPrixsaison{

	function TPrixsaison(){
		$this->id=0;
		$this->id_categorie=0;
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

		$db->Execute("SELECT id,id_categorie,prix,dt_deb,dt_fin,dt_cre,dt_maj
		FROM hot_categorie_prixsaison
		WHERE id=$id ");
		if($db->Get_recordCount()>0){
			$db->Get_line();

			$this->id=$db->Get_field('id');
			$this->id_categorie=$db->Get_field('id_categorie');
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
			$query['id_categorie']=$this->id_categorie;
			$query['prix']=$this->prix;
			
      $query['dt_deb']=date("Y-m-d H:i:s",$this->dt_deb);
      $query['dt_fin']=date("Y-m-d H:i:s",$this->dt_fin);

			$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
			$query['dt_maj']=date("Y-m-d H:i:s");

			$key[0]='id';

			if($this->id==0){
				$this->get_newid($db);
				$query['id']=$this->id;
				$db->dbinsert('hot_categorie_prixsaison',$query);
			}
			else {
				$query['id']=$this->id;
				$db->dbupdate('hot_categorie_prixsaison',$query,$key);
			}


    }
		
	}


	function delete(&$db){
		if($this->id!=0){
			$db->dbdelete('hot_categorie_prixsaison',array("id"=>$this->id),array(0=>'id'));
		}
	}

	function get_newid(&$db){

		$sql="SELECT max(id) as 'maxi' FROM hot_categorie_prixsaison";
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


?>
