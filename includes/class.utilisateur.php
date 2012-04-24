<?php


class TUtilisateur{
	/**
     * Constructor
     * @access protected
     */


	function TUtilisateur(){
		$this->id=0;
		$this->id_groupe=0;
		$this->login="";
		$this->nom="";
		$this->prenom="";
		$this->email="";
		$this->type="MASTER"; // MASTER, SLAVE

		$this->TType=array(
			"MASTER"=>"Utilisateur principal"
			,"SLAVE"=>"Utilisateur secondaire"
		);

		$this->TLien=array();

		$this->password="";
		$this->dt_cre=time();
		$this->dt_maj=time();

		$this->dt_last_connexion = 0;

	}
	function get_count_hotel(&$db){
  
    $db->Execute("SELECT count(*) as 'nb' FROM
    hot_hotel WHERE id_groupe=".$this->id_groupe);
    $db->Get_line();
    return (int)$db->Get_field('nb');
    
  }
  function make_password(){
    $this->password = substr(md5(time().$this->login),0,8);
  }
	function login(&$db, $login, $password, $load=true){

		$r=new TRequete();
		$id = $r->get_id_by_login($db,$login, $password);

		if($id!=-1){
			$this->load($db, $id);
			

			return $id;
		}
		else{
			return false;
		}

	}
	function save_connexion(&$db){
			$query['dt_last_connexion']=date("Y-m-d H:i:s");
			$query['id']=$this->id;
			$key[0]='id';

			$db->dbupdate('hot_utilisateur',$query,$key);
	}
	function load(&$db,$id){

		$db->Execute("SELECT id,id_groupe,login,email,password,nom,prenom,type
				  ,dt_cre,dt_maj, dt_last_connexion
				  FROM hot_utilisateur WHERE id=$id");
		if($db->Get_recordCount()>0){
			$db->Get_line();
			

			$this->id=$id;
			$this->id_groupe=$db->Get_field('id_groupe');
			test_group_id($this->id_groupe);
			
			$this->login=$db->Get_field('login');
			$this->email=$db->Get_field('email');
			
			$this->password=$db->Get_field('password');
			$this->nom=$db->Get_field('nom');
			$this->prenom=$db->Get_field('prenom');
			$this->type=$db->Get_field('type');

			$this->dt_cre=strtotime($db->Get_field('dt_cre'));
			$this->dt_maj=strtotime($db->Get_field('dt_maj'));

			$this->dt_last_connexion = strtotime($db->Get_field('dt_last_connexion'));	

			$this->load_lien_hotel($db);

			
		}
	}
	
		function load_from_email(&$db,$email){

		$db->Execute("SELECT id,id_groupe,login,email,password,nom,prenom,type
				  ,dt_cre,dt_maj, dt_last_connexion
				  FROM hot_utilisateur WHERE email='$email'");
		if($db->Get_recordCount()>0){
			$db->Get_line();
			

			$this->id=$db->Get_field('id');;
			$this->id_groupe=$db->Get_field('id_groupe');
						
			$this->login=$db->Get_field('login');
			$this->email=$email;
			
			$this->password=$db->Get_field('password');
			$this->nom=$db->Get_field('nom');
			$this->prenom=$db->Get_field('prenom');
			$this->type=$db->Get_field('type');

			$this->dt_cre=strtotime($db->Get_field('dt_cre'));
			$this->dt_maj=strtotime($db->Get_field('dt_maj'));

			$this->dt_last_connexion = strtotime($db->Get_field('dt_last_connexion'));
		}
	}
	
	function load_lien_hotel(&$db){
		$r=new TRequete;
		$Tab = $r->liste_lien_hotel_utilisateur($db, $this->id);
		$nb=count($Tab);
		
		$this->TLien=array();
		for ($i = 0; $i < $nb; $i++) {
			$this->TLien[$i]=new TLien_utilisateur_hotel;
			$this->TLien[$i]->load($db, $Tab[$i]);
		}
	
		
	}
	
	function check_login (&$db) {
			$r=new TRequete;
			if(trim($this->login)!="" && strlen($this->login)>=3)return $r->check_login($db, $this->id, $this->login);
			else return false;
			
	}
	
	function check_email (&$db) {
			$r=new TRequete;
			if(trim($this->email)!="" && valide_email($this->email)) return $r->check_email($db, $this->id, $this->email);
			else return false;
			
	}
	function send_mail_account(){
  
    $rep=new TReponse;
    $rep->emailto = $this->email;
    $rep->emailfrom = "noreply@gestion-hotel.com";
    $rep->reply_to = "support@gestion-hotel.com";
    $rep->titre = "[Gestion-Hotel.com] Information de connexion pour votre compte GHôtel";
    $rep->corps ="
Bonjour,
    
  Vous avez récemment créé un compte utilisateur sur Gestion-Hotel.com afin d’utiliser le programme GHôtel de gestion hôtelière en ligne. 
	
  Voici vos informations de connexion :
	
  Identifiant : ".$this->login."
	
  Mot de passe : ".$this->password."
	
  http://ghotel.gestion-hotel.com/bin/main.php?login=".$this->login."
	
  Si vous avez des questions relatives au fonctionnement de l’application, vous pouvez nous envoyer un mail à support@gestion-hotel.com

Nous vous remercions de la confiance que vous nous portez,

Cordialement,

L’équipe de GHôtel.
 
    ";
    
    $rep->send(false);
  
  }
	function send_mail_account_lost(){
  
    $rep=new TReponse;
    $rep->emailto = $this->email;
    $rep->emailfrom = "noreply@gestion-hotel.com";
    $rep->reply_to = "support@gestion-hotel.com";
    $rep->titre = "[Gestion-Hotel.com] Information de connexion pour votre compte GHôtel";
    $rep->corps ="
Bonjour,
    
  Vous avez récemment demandé le renvoi de vos identifiants pour le logiciel GHôtel. 
	
  Voici vos informations de connexion :
	
  Identifiant : ".$this->login."
	
  Mot de passe : ".$this->password."
	
  http://ghotel.gestion-hotel.com/bin/main.php?login=".$this->login."
	
  Si vous avez des questions relatives au fonctionnement de l’application, vous pouvez nous envoyer un mail à support@gestion-hotel.com

Nous vous remercions de la confiance que vous nous portez,

Cordialement,

L’équipe de GHôtel.
 
    ";
    
    $rep->send(false);
  
  }
	function save(&$db){

		if($this->check_login($db)){


			$query['id_groupe']=$this->id_groupe;
			$query['login']=$this->login;
			$query['email']=$this->email;
			
			$query['password']=$this->password;
			$query['nom']=$this->nom;
			$query['prenom']=$this->prenom;
			$query['type']=$this->type;
			
			if($this->login!=""){
	   			$query["idx"]=(ctype_alpha(strtoupper($this->login[0]))?strtoupper($this->login[0]):'0');
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
				$db->dbinsert('hot_utilisateur',$query);
			}
			else {
				$query['id']=$this->id;
				$db->dbupdate('hot_utilisateur',$query,$key);
			}
			
			$this->save_lien($db);
			return true;
		}
		else {
			
			return false;
		}

	}
	function save_lien(&$db){
		$nb = count($this->TLien);
		for ($i = 0; $i < $nb; $i++) {
			$this->TLien[$i]->id_utilisateur=$this->id;
			$this->TLien[$i]->save($db);
		}
		
	}
	function delete(&$db){
		if($this->id!=0){
			$db->dbdelete('hot_utilisateur',array("id"=>$this->id),array(0=>'id'));
		}
	}

	function get_newid(&$db){

		$sql="SELECT max(id) as 'maxi' FROM hot_utilisateur";
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
	
	function is_Lien ($id_hotel, $forced=false) {
		
		$nb = count($this->TLien);
		for ($i = 0; $i < $nb; $i++) {
			if ((!$this->TLien[$i]->to_delete || $forced) 
			&& $this->TLien[$i]->id_hotel == $id_hotel) {
				return true;
			}
		}
		
		return false;
	}
	function set_lien_hotel(&$Tab){
		$nb = count($this->TLien);
		for ($i = 0; $i < $nb; $i++) {
			$this->TLien[$i]->to_delete=true;
		}
		
		
		$keys=array_keys($Tab);
		$nb=count($Tab);
		for ($i = 0; $i < $nb; $i++) {
			$id_hotel = $keys[$i];
			
			if(!$this->is_Lien($id_hotel, true)){
				$this->add_lien_hotel($id_hotel);
			}
			else{
				$this->TLien[$i]->to_delete=false;
			}
			
		}	
		
	}
	function add_lien_hotel($id_hotel){
		$i = count($this->TLien);
		$this->TLien[$i]=new TLien_utilisateur_hotel;
		$this->TLien[$i]->id_utilisateur=$this->id;
		$this->TLien[$i]->id_hotel=$id_hotel;
	
	}
	
}

class TLien_utilisateur_hotel {
  function TLien_utilisateur_hotel() {
	  
  		$this->id=0;
		$this->id_hotel=0;
		$this->id_utilisateur=0;
		$this->dt_cre=time();
		$this->dt_maj=time();
		
		$this->to_delete=false;

	}
	
	function load(&$db,$id){

		$db->Execute("SELECT id,id_hotel,id_utilisateur
				  ,dt_cre,dt_maj
				  FROM hot_lien_utilisateur_hotel WHERE id=$id");
		if($db->Get_recordCount()>0){
			$db->Get_line();
			

			$this->id=$id;
			$this->id_hotel=$db->Get_field('id_hotel');
			$this->id_utilisateur=$db->Get_field('id_utilisateur');
			
			$this->dt_cre=strtotime($db->Get_field('dt_cre'));
			$this->dt_maj=strtotime($db->Get_field('dt_maj'));
		}
	}
	
	function save(&$db){
		
		if (!$this->to_delete) {
			$query['id_hotel']=$this->id_hotel;
			$query['id_utilisateur']=$this->id_utilisateur;
			

			$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
			$query['dt_maj']=date("Y-m-d H:i:s");

			$key[0]='id';

			if($this->id==0){
				$this->get_newid($db);
				$query['id']=$this->id;
				$db->dbinsert('hot_lien_utilisateur_hotel',$query);
			}
			else {
				$query['id']=$this->id;
				$db->dbupdate('hot_lien_utilisateur_hotel',$query,$key);
			}
		} else {
			
			$this->delete($db);
		}
	}

	function delete(&$db){
		if($this->id!=0){
			$db->dbdelete('hot_lien_utilisateur_hotel',array("id"=>$this->id),array(0=>'id'));
		}
	}

	function get_newid(&$db){

		$sql="SELECT max(id) as 'maxi' FROM hot_lien_utilisateur_hotel";
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
