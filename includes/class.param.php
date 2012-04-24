<?php

class TParam{
	/**
     * Constructor
     * @access protected
     */

	function TParam() {
		$this->id=0;
		$this->clef="";
		$this->description="";
		$this->type="";
		$this->valeur_n=0;
		$this->valeur_f=0.0;
		$this->valeur_s="";
		$this->valeur_t="";
		$this->valeur_d=time();
		
		$this->dt_cre=time();
		$this->dt_maj=time();
		
//		$this->id_hotel=0;
	}

	function load(&$db,$id){
		if(is_int($id)){
			$sql = "SELECT id,clef,description,type,valeur_n,valeur_f,valeur_s
				,valeur_t,valeur_d,dt_cre,dt_maj
				FROM hot_param WHERE id=$id";
		}	
		else{
			$sql = "SELECT id,clef,description,type,valeur_n,valeur_f,valeur_s
				,valeur_t,valeur_d,dt_cre,dt_maj
				FROM hot_param WHERE clef='$id'";
		}
	
		$db->Execute($sql);

		if($db->Get_recordCount()>0){
			$db->Get_line();

			$this->id=$db->Get_field('id');
			$this->clef=$db->Get_field('clef');
			$this->description=$db->Get_field('description');
			$this->type=$db->Get_field('type');
			$this->valeur_n=$db->Get_field('valeur_n');
			$this->valeur_f=$db->Get_field('valeur_f');
			$this->valeur_s=$db->Get_field('valeur_s');
			$this->valeur_t=$db->Get_field('valeur_t');
			$this->valeur_d=strtotime($db->Get_field('valeur_d'));
			
//			$this->id_hotel=$db->Get_field('id_hotel');

			$this->dt_cre=strtotime($db->Get_field('dt_cre'));
			$this->dt_maj=strtotime($db->Get_field('dt_maj'));
		}
	}

	function save(&$db){
		$query['id']=$this->id;
		$query['clef']=$this->clef;
		$query['description']=$this->description;
		$query['type']=$this->type;
		$query['valeur_n']=$this->valeur_n;
		$query['valeur_f']=$this->valeur_f;
		$query['valeur_s']=$this->valeur_s;
		$query['valeur_t']=$this->valeur_t;
		$query['valeur_d']=date("Y-m-d H:i:s",$this->valeur_d);
		
//		$query['id_hotel']=$this->id_hotel;

		$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
		$query['dt_maj']=date("Y-m-d H:i:s");

		$key[0]='id';

		if($this->id==0){
			$this->get_newid($db);
			$query['id']=$this->id;
			$db->dbinsert('hot_param',$query);
		}
		else {
			$query['id']=$this->id;
			$db->dbupdate('hot_param',$query,$key);
		}
	}

	function delete(&$db) {
		if($this->id!=0){
			$db->dbdelete('hot_param',array("id"=>$this->id),array(0=>'id'));
		}
	}

	function get_newid(&$db) {
		$sql="SELECT max(id) as 'maxi' FROM hot_param";
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

	function get_valeurd(){
		return date("d/m/Y",$this->valeur_d);
	}

	function set_dtcre($date) {
		list($d,$m,$y) = explode("/",$date);
		$this->dt_cre = mktime(0,0,0,$m,$d,$y);
	}

	function set_dtmaj($date) {
		list($d,$m,$y) = explode("/",$date);
		$this->dt_maj = mktime(0,0,0,$m,$d,$y);
	}
	
	function set_valeurd($date) {
		list($d,$m,$y) = explode("/",$date);
		$this->valeur_d = mktime(0,0,0,$m,$d,$y);
	}
	
	function get_valeur(){
		switch ($this->type) {
			case "VALEUR_N":
				return $this->valeur_n;
				break;
			case "VALEUR_F":
				return $this->valeur_f;
				break;
			case "VALEUR_S":
				return $this->valeur_s;
				break;
			case "VALEUR_T":
				return $this->valeur_t;
				break;
			case "VALEUR_D":
				return $this->get_valeurd();
				break;	
			default:
				return null;
				break;
			
		}
	}
	function set_valeur($valeur){
		switch ($this->type) {
			case "VALEUR_N":
				$this->valeur_n=$valeur;
				break;
			case "VALEUR_F":
				$this->valeur_f=$valeur;
				break;
			case "VALEUR_S":
				$this->valeur_s=$valeur;
				break;
			case "VALEUR_T":
				$this->valeur_t=$valeur;
				break;
			case "VALEUR_D":
				$this->set_valeurd($valeur);
				break;	
			default:
				return null;
				break;
			
		}
	}
}

?>