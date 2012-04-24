<?php


class TClient{
	/**
     * Constructor
     * @access protected
     */


	function TClient(){
		$this->id=0;
		$this->nom="";
		$this->mails="";

		$this->dt_cre=time();
		$this->dt_maj=time();

		$this->password;
		$this->id_indus;
		$this->id_orga;
		$this->name_indus;
		$this->name_orga;

		$this->id_client_indus;
		$this->passwd_indus;


		$this->dt_mails;

		$this->TCamp=array();
		$this->TProd=array();
		$this->TForm=array();

	}
	function load_indus(&$db){
		if($this->id_indus!=0){

			$Tab=array();
			$sql=" nom FROM indus_main WHERE id=".$this->id_indus;
			$Tab = unserialize(file_get_contents(ROOT_DIR_SD."/scripts/get_request.php?sql=".urlencode($sql)));
			$this->name_indus = $Tab[0]['nom'];
		}
	}
	function load_orga(&$db){
		if($this->id_orga!=0){

			$Tab=array();
			$sql=" nom FROM form_orga WHERE id=".$this->id_orga;
			//print ROOT_DIR_SDF."/scripts/get_request.php?sql=".urlencode($sql);
			$Tab = unserialize(file_get_contents(ROOT_DIR_SDF."/scripts/get_request.php?sql=".urlencode($sql)));
			$this->name_orga = $Tab[0]['nom'];
		}
	}
	function load(&$db,$id){

		$db->Execute("SELECT id,nom,mails,password,dt_cre,dt_maj,dt_mails,id_indus,id_orga,dt_ref
		FROM pub_client WHERE id=$id");
		if($db->Get_recordCount()>0){
			$db->Get_line();

			$this->id=$id;
			$this->nom=$db->Get_field('nom');
			$this->mails=$db->Get_field('mails');
			$this->password=$db->Get_field('password');
			$this->dt_mails=$db->Get_field('dt_mails');
			$this->id_indus=$db->Get_field('id_indus');
			$this->id_orga=$db->Get_field('id_orga');
			$this->dt_ref=strtotime($db->Get_field('dt_ref'));


			$this->dt_cre=strtotime($db->Get_field('dt_cre'));
			$this->dt_maj=strtotime($db->Get_field('dt_maj'));

			if($this->id_indus>0){
				$this->load_indus($db);
			}

			if($this->id_orga>0){
				$this->load_orga($db);
			}

			$this->load_camp($db);

			return true;
		}
		else {
			return false;
		}


	}
	function set_dtmails($time){
		// au format time pendant 1 temps
		$this->dt_mails = $time;

	}

	function load_camp(&$db){

		$db->Execute("SELECT id FROM pub_camp WHERE id_cli=".$this->id);
		if($db->Get_recordCount()>0){
			$Tab=array();
			while($db->Get_line()){
				$Tab[] = $db->Get_field('id');
			}

			$nb=count($Tab);
			for($i = 0; $i < $nb; $i++){
				$idc = $Tab[$i];

				$i = $this->get_nbCamp();
				$this->TCamp[$i]=new TCamp;
				$this->TCamp[$i]->load($db,$idc);
			} // for




		}
	}
	function get_nbCamp(){

		return count($this->TCamp);

	}

	function save(&$db){




			$query['nom']=$this->nom;
			$query['mails']=$this->mails;
			$query['password']=$this->password;
			$query['id_indus']=$this->id_indus;
			$query['id_orga']=$this->id_orga;
			$query['dt_ref']=date("Y-m-d",$this->dt_ref);
			if($this->nom){
	   			$query["idx"]=(ctype_alpha(strtoupper($this->nom[0]))?strtoupper($this->nom[0]):'0');
			}
			else {
			   		$query["idx"]="0";
			}

			$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
			$query['dt_maj']=date("Y-m-d H:i:s");
			$query['dt_mails']=$this->dt_mails;

			$key[0]='id';

			if($this->id==0){
				$this->get_newid($db);
				$query['id']=$this->id;
				$db->dbinsert('pub_client',$query);
			}
			else {
				$query['id']=$this->id;
				$db->dbupdate('pub_client',$query,$key);
			}



	}
	function already_exist(&$db){

		$db->Execute("SELECT count(*) as 'nb' FROM pub_client WHERE nom='".$this->nom."'");
		$db->Get_line();
		$nb=$db->Get_field('nb');

		if($nb>0){
			return true;
		}
		else {
			return false;
		}

	}
	function get_idByLogin(&$db,$login,$mdp){

		$db->Execute("SELECT id FROM pub_client WHERE nom='".$login."' AND password='".$mdp."'");
		if($db->Get_Recordcount()==0){
			return -1;
		}
		else {
			$db->Get_line();
			return $db->Get_field('id');
		}
	}
	function delete(&$db){
		if($this->id!=0){
			//$db->dbdelete('pub_client',array("id"=>$this->id),array(0=>'id'));
			$db->dbupdate('pub_client',array("id"=>$this->id,"suppr"=>1),array(0=>'id'));
		}
	}

	function get_newid(&$db){

		$sql="SELECT max(id) as 'maxi' FROM pub_client";
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
	function get_dtref(){
		if($this->dt_ref<=0)return "";
		else return date("d/m/Y",$this->dt_ref);
	}
	function set_dtref($date){
		if($date!=""){
			list($d,$m,$y) = explode("/",$date);
			$this->dt_ref = mktime(0,0,0,$m,$d,$y);
		}
		else{
			$this->dt_ref=1;
		}

	}

}

?>