<?php


class TGroupe{
	/**
     * Constructor
     * @access protected
     */


	function TGroupe(){
		$this->id=0;
		$this->nom="";
		$this->dt_cre=time();
		$this->dt_maj=time();
		$this->dt_end_rights = time() + (86400 * 31 * 6); // six mois offerts

		$this->dt_last_connexion = 0;

	}
	function load(&$db,$id){

		$db->Execute("SELECT id,nom
				  ,dt_cre,dt_maj,dt_end_rights
				  FROM hot_groupe WHERE id=$id");
		if($db->Get_recordCount()>0){
			$db->Get_line();

			$this->id=$id;
			$this->nom=$db->Get_field('nom');
			
			$this->dt_cre=strtotime($db->Get_field('dt_cre'));
			$this->dt_maj=strtotime($db->Get_field('dt_maj'));
			$this->dt_end_rights=strtotime($db->Get_field('dt_end_rights'));

			$this->dt_last_connexion=strtotime($db->Get_field('dt_last_connexion'));
			
		}
	}
	function save_connexion(&$db){
			$query['dt_last_connexion']=date("Y-m-d H:i:s");
			$query['id']=$this->id;
			$key[0]='id';

			$db->dbupdate('hot_groupe',$query,$key);
	}
	function save(&$db){

			$query['nom']=$this->nom;
			
			$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
			$query['dt_end_rights']=date("Y-m-d H:i:s",$this->dt_end_rights);
			$query['dt_maj']=date("Y-m-d H:i:s");

			$key[0]='id';

			if($this->id==0){
				$this->get_newid($db);
				$query['id']=$this->id;
				$db->dbinsert('hot_groupe',$query);
			}
			else {
				$query['id']=$this->id;
				$db->dbupdate('hot_groupe',$query,$key);
			}
	}

	function delete(&$db){
		if($this->id!=0){
			$db->dbdelete('hot_groupe',array("id"=>$this->id),array(0=>'id'));
		}
	}

	function get_newid(&$db){

		$sql="SELECT max(id) as 'maxi' FROM hot_groupe";
		$db->Execute($sql);
		$db->Get_line();
		$this->id = (double)$db->Get_field('maxi')+1;

	}

	function get_dtcre(){
		return date("d/m/Y",$this->dt_cre);
	}
	function get_dtendrights(){
		return date("d/m/Y",$this->dt_end_rights);
	}

	function get_dtmaj(){
		return date("d/m/Y",$this->dt_maj);
	}

	function set_dtcre($date){
		list($d,$m,$y) = explode("/",$date);

		$this->dt_cre = mktime(0,0,0,$m,$d,$y);

	}
	function set_dtendrights($date){
		list($d,$m,$y) = explode("/",$date);

		$this->dt_end_rights = mktime(0,0,0,$m,$d,$y);

	}

	function set_dtmaj($date){

		list($d,$m,$y) = explode("/",$date);

		$this->dt_maj = mktime(0,0,0,$m,$d,$y);

	}

}

?>
