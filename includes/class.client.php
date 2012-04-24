<?php


class TClient{
	/**
     * Constructor
     * @access protected
     */

	function TClient(){
		$this->id=0;
		$this->id_hotel=0;
		$this->civilite="";
		$this->nom="";
		$this->prenom="";
		$this->num_passport="";
		$this->nationalite="";
		$this->adresse="";
		$this->tel="";
		$this->email="";
		$this->type="IND";
		$this->observation="";
		$this->tarif_neg="";
		$this->ref_bank="";
		
		$this->source="";

		$this->TCivilite=array(
			"Mlle"=>"Mademoiselle"
			,"Mme"=>"Madame"
			,"M"=>"Monsieur"
			,"Autre"=>"Autre"
		);

		$this->TNationalite=_get_langue();

		$this->TType=array(
			"IND"=>"Individuel"
			,"SOC"=>"Société"
			,"AGC"=>"Agence"
			,"GRP"=>"Groupe"
			,""=>"Autre"
    );

		$this->dt_cre=time();
		$this->dt_maj=time();

	}

	function load(&$db,$id){

		$db->Execute("SELECT id,id_hotel,civ,nom,prenom,num_passport,nationalite,adresse,tel,
					  email,type,observation,tarif_neg,ref_bank,source,dt_cre,dt_maj
					  ,data_import
					  FROM hot_client WHERE id=$id");

		if($db->Get_recordCount()>0){
			$db->Get_line();

			$this->id=$id;
			$this->id_hotel=$db->Get_field('id_hotel');
			test_hotel_id($this->id_hotel);
			
			$this->civilite=$db->Get_field('civ');
			$this->nom=$db->Get_field('nom');
			$this->prenom=$db->Get_field('prenom');
			$this->num_passport=$db->Get_field('num_passport');
			$this->nationalite=$db->Get_field('nationalite');
			$this->adresse=$db->Get_field('adresse');
			$this->tel=$db->Get_field('tel');
			$this->email=$db->Get_field('email');
			$this->type=$db->Get_field('type');
			$this->observation=$db->Get_field('observation');
			$this->tarif_neg=$db->Get_field('tarif_neg');
			$this->ref_bank=$db->Get_field('ref_bank');
      $this->source=$db->Get_field('source');
      $this->data_import=$db->Get_field('data_import');

			$this->dt_cre=strtotime($db->Get_field('dt_cre'));
			$this->dt_maj=strtotime($db->Get_field('dt_maj'));
		}
	}

	function save(&$db){

			$query['id_hotel']=$this->id_hotel;
			$query['civ']=$this->civilite;
			$query['nom']=$this->nom;
			$query['prenom']=$this->prenom;
			$query['num_passport']=$this->num_passport;
			$query['nationalite']=$this->nationalite;
			$query['adresse']=$this->adresse;
			$query['tel']=$this->tel;
			$query['email']=$this->email;
			$query['type']=$this->type;
			$query['observation']=$this->observation;
			$query['tarif_neg']=$this->tarif_neg;
			$query['ref_bank']=$this->ref_bank;
			$query['source']=$this->source;
      $query['data_import']=$this->data_import;


			if($this->nom){
	   			$query["idx"]=(ctype_alpha(strtoupper($this->nom[0]))?strtoupper($this->nom[0]):'0');
			}
			else {
			   	$query["idx"]="0";
			}

			$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
			$query['dt_maj']=date("Y-m-d H:i:s");

			$key[0]='id';

			if($this->id==0){
				$this->get_newid($db);
				$query['id']=$this->id;
				$db->dbinsert('hot_client',$query);
			}
			else {
				$query['id']=$this->id;
				$db->dbupdate('hot_client',$query,$key);
			}



	}

	function delete(&$db){
		if($this->id!=0){
			$db->dbdelete('hot_client',array("id"=>$this->id),array(0=>'id'));
		}
	}

	function get_newid(&$db){

		$sql="SELECT max(id) as 'maxi' FROM hot_client";
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
	
	function get_client_name () {
			
		if($this->civilite!='Autre') {
			$nom = $this->civilite." "
				.$this->prenom." "
				.$this->nom;
		} else {
			$nom = $this->prenom." "
				.$this->nom;
		}
				
		return $nom;
	}
}

?>
