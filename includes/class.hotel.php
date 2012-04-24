<?php

class THotel{
	/**
     * Constructor
     * @access protected
     */

	function THotel(){
		/*** Informations relatives à l'hotel ***/
		$this->id=0;
		$this->id_groupe=0;
		$this->nom="";
		$this->nom_gestion="";
		$this->adresse="";
		$this->cp="";
		$this->ville="";
		$this->email="";
		$this->site="";
		$this->tva="";
		$this->siren="";
		$this->ape="";
		$this->siret="";
		$this->capital=0.0;
		$this->telephone="";
		$this->fax="";
		$this->responsable="";
		$this->rcs="";
		$this->banque="";
		$this->etab="";
		$this->guichet="";
		$this->compte="";
		$this->cle="";
		$this->note="";

    /*
    	Ajout des paramètres... Comme ils ne feront jamais l'objet de recherche ;
    	Je vais faire un truc tout nul
    */	
    	
    $this->TParam=array();	
    	
    $this->dt_cre=time();
		$this->dt_maj=time();
	}

  function get_count_reservation(&$db){
    $db->Execute("SELECT count(*) as 'nb' 
    FROM hot_reservation
    WHERE id_hotel=".$this->id);
    $db->Get_line();
    return (int)$db->Get_field('nb');
  
  }
  function get_count_chambre(&$db){
    $db->Execute("SELECT count(*) as 'nb' 
    FROM hot_chambre
    WHERE id_hotel=".$this->id);
    $db->Get_line();
    return (int)$db->Get_field('nb');
  
  }
  function get_count_client(&$db){
    $db->Execute("SELECT count(*) as 'nb' 
    FROM hot_client
    WHERE id_hotel=".$this->id);
    $db->Get_line();
    return (int)$db->Get_field('nb');
  
  }
  
  
  function set_parameter($name, $value){
    
      $this->TParam[$name] = $value;
    
  }
  function get_parameter($name){
     return $this->TParam[$name];
  }

	function load(&$db,$id){

		$db->Execute("SELECT id,id_groupe,nom,adresse,cp,ville,email,site,tva
		,siren,ape,siret,capital,telephone,fax,responsable,rcs,banque,etab,guichet,compte,cle,note,dt_cre,dt_maj
		,TParam,nom_gestion
		FROM hot_hotel
		WHERE id=$id ");
		if($db->Get_recordCount()>0){
			$db->Get_line();

			$this->id=$db->Get_field('id');
			$this->id_groupe=$db->Get_field('id_groupe');
			test_group_id($this->id_groupe);
			
			$this->nom=$db->Get_field('nom');
			$this->nom_gestion=$db->Get_field('nom_gestion');
			
			$this->email=$db->Get_field('email');
			$this->adresse=$db->Get_field('adresse');
			$this->cp=$db->Get_field('cp');
			$this->ville=$db->Get_field('ville');
			$this->site=$db->Get_field('site');
			$this->tva=$db->Get_field('tva');
			$this->siren=$db->Get_field('siren');
			$this->ape=$db->Get_field('ape');
			$this->siret=$db->Get_field('siret');
			$this->capital=$db->Get_field('capital');
			$this->telephone=$db->Get_field('telephone');
			$this->fax=$db->Get_field('fax');
			$this->responsable=$db->Get_field('responsable');
			$this->rcs=$db->Get_field('rcs');
			$this->banque=$db->Get_field('banque');
			$this->etab=$db->Get_field('etab');
			$this->guichet=$db->Get_field('guichet');
			$this->compte=$db->Get_field('compte');
			$this->cle=$db->Get_field('cle');
			$this->note=$db->Get_field('note');
			
			$this->TParam=unserialize($db->Get_field('TParam'));
			
			$this->dt_cre=strtotime($db->Get_field('dt_cre'));
			$this->dt_maj=strtotime($db->Get_field('dt_maj'));

			return true;
		}
		else {
			return false;
		}
	}

	function save(&$db){

			$query['id_groupe']=$this->id_groupe;
			$query['nom']=$this->nom;
			$query['nom_gestion']=$this->nom_gestion;
			
			$query['adresse']=$this->adresse;
			$query['cp']=$this->cp;
			$query['ville']=$this->ville;
			$query['email']=$this->email;
			$query['site']=$this->site;
			$query['tva']=$this->tva;
			$query['siren']=$this->siren;
			$query['ape']=$this->ape;
			$query['siret']=$this->siret;
			$query['capital']=$this->capital;
			$query['telephone']=$this->telephone;
			$query['fax']=$this->fax;
			$query['responsable']=$this->responsable;
			$query['rcs']=$this->rcs;
			$query['banque']=$this->banque;
			$query['etab']=$this->etab;
			$query['guichet']=$this->guichet;
			$query['compte']=$this->compte;
			$query['cle']=$this->cle;
			$query['note']=$this->note;

      $query['TParam'] = serialize($this->TParam);

			$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
			$query['dt_maj']=date("Y-m-d H:i:s");

			$key[0]='id';

			if($this->id==0){
				$this->get_newid($db);
				$query['id']=$this->id;
				$db->dbinsert('hot_hotel',$query);
			}
			else {
				$query['id']=$this->id;
				$db->dbupdate('hot_hotel',$query,$key);
			}
	}


	function delete(&$db){
		if($this->id!=0){
			$db->dbdelete('hot_hotel',array("id"=>$this->id),array(0=>'id'));
		}
	}

	function get_newid(&$db){

		$sql="SELECT max(id) as 'maxi' FROM hot_hotel";
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

?>