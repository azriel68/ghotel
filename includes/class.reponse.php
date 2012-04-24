<?php
  
/**
 * Classe gérant les réponses CV & ANNONCE
 *
 * @version $Id$
 * @copyright 2006 
 */

/** 
 * 
 *
 **/
class TReponse{
	/**
     * Constructor
     * @access protected
     */
	function TReponse(){
		$this->id=0;
		$this->id_candidat=0;
		$this->id_recruteur=0;
		$this->id_cv=0;
		$this->id_annonce=0;
		
		$this->lib_recruteur="";
		$this->lib_candidat="";
		$this->lib_cv="";
		$this->lib_annonce="";
		
		$this->emailto="";
		$this->emailfrom="noreply@gestion-hotel.com";
		$this->titre="";
		$this->corps="";
		$this->reply_to="";
		
		$this->emailerror="webmaster@gestion-hotel.com";
		
		
		$this->type="";
		
		$this->dt_cre = time();
		$this->dt_maj = time();
		
	}
	function is_rep_annonce(){
		$this->type="REP_ANN";
	}
	function is_rep_cv(){
		$this->type="REP_CV";
	}
	function load(&$db,$id){
		$db->Execute("SELECT id_candidat, id_recruteur, id_cv,id_annonce
		,emailto,emailfrom, titre, corps, type, dt_cre
		,lib_recruteur,lib_candidat,lib_cv,lib_annonce
		FROM job_reponse WHERE id=".$id);
		$db->Get_line();
		
		$this->id=$id;
		$this->emailto=$db->Get_field('emailto');
		$this->emailfrom=$db->Get_field('emailfrom');
		$this->titre=$db->Get_field('titre');
		$this->corps=$db->Get_field('corps');
		$this->type=$db->Get_field('type');
		$this->id_candidat=$db->Get_field('id_candidat');
		$this->id_recruteur=$db->Get_field('id_recruteur');
		$this->id_cv=$db->Get_field('id_cv');
		$this->id_annonce=$db->Get_field('id_annonce');
		
		$this->lib_recruteur=$db->Get_field('lib_recruteur');
		$this->lib_candidat=$db->Get_field('lib_candidat');
		$this->lib_cv=$db->Get_field('lib_cv');
		$this->lib_annonce=$db->Get_field('lib_annonce');

		$this->dt_cre=strtotime($db->Get_field('dt_cre'));
		$this->dt_maj=strtotime($db->Get_field('dt_maj'));
	}
	function save(&$db){
			$query['emailto']=$this->emailto;
			$query['emailfrom']=$this->emailfrom;
			$query['titre']=$this->titre;
			$query['corps']=$this->corps;
			$query['type']=$this->type;
			
			$query['id_candidat']=$this->id_candidat;
			$query['id_recruteur']=$this->id_recruteur;
			$query['id_cv']=$this->id_cv;
			$query['id_annonce']=$this->id_annonce;
			
			$query['lib_recruteur']=$this->lib_recruteur;
			$query['lib_candidat']=$this->lib_candidat;
			$query['lib_cv']=$this->lib_cv;
			$query['lib_annonce']=$this->lib_annonce;
			
			$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
			$query['dt_maj']=date("Y-m-d H:i:s");
			
			$key[0]='id';
			
			if($this->id==0){
				$this->get_newid($db);
				$query['id']=$this->id;
				$db->dbinsert('job_reponse',$query);
			}
			else {
				$query['id']=$this->id;
				$db->dbupdate('job_reponse',$query,$key);		
			}
	}
	function delete(&$db){
		if($this->id!=0){
			$db->dbdelete('job_reponse',array("id"=>$this->id),array(0=>'id'));
		}
	}
	function get_newid(&$db){
		$sql="SELECT max(id) as 'maxi' FROM job_reponse";
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
	function send($html=true){
	/**
	 * envoi la réponse ainsi générée 
	 * 20/09/2006 11:31:04 Alexis ALGOUD
	 **/
	 	$headers="";
	 
	  
    $headers .= "From:".$this->emailfrom."\n";
		$headers .= "Message-ID: <".time()." ".rand()." TheSystem@".$_SERVER['SERVER_NAME'].">\n";
		$headers .= "X-Mailer: PHP v".phpversion()." \n";
		$headers .= "X-Sender: <gestion-hotel.com>\n";
		$headers .= "X-auth-smtp-user: support@gestion-hotel.com \n";
		$headers .= "X-abuse-contact: support@gestion-hotel.com \n"; 
		
		if($this->reply_to=="")$this->reply_to = $this->emailfrom;
		
		$headers .= "Bcc: ".$this->reply_to." \n"; 
		$headers .= "Reply-To: ".$this->reply_to." \n";
	  $headers .= "Return-path: ".$this->reply_to." \n";
		
    $headers .="MIME-Version: 1.0\n";
	  if($html)$headers .="Content-type: text/html; charset=\"iso-8859-1\" \n";
	  else $headers .="Content-Type: text/plain; charset=\"iso-8859-1\" \n"; 
	  $headers .="Content-Transfer-Encoding: 8bit \n"; 
		
		mail($this->emailto,$this->titre,$this->corps,$headers,  "-f".$this->emailerror);
	
	}
}  
?>
