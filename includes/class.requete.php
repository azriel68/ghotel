<?
/**
 * Class faisant des requête sur la base
 * Aucune requete Db n'est autorisée hors base
 **/
class TRequete{
	/**
	 * Constructor
	 * @access protected
	 */
	function TRequete(){
		$this->nb_resultat=0;
		$this->TResultat=array();
		$this->resultat=0;
	}

  function get_model(&$db, $id_hotel){
  
    
  
    $db->Execute("SELECT id FROM hot_model WHERE id_hotel=".$id_hotel);
  
    $this->TResultat=array();
    $this->TResultat[] = -101; // réservation;
    $this->TResultat[] = -102; // facture/devis;
    $this->TResultat[] = -103; // facture/devis;
    
  		while($db->Get_line()){
  
  			$this->TResultat[]=$db->Get_field('id');
  
  		}
  
  		$this->nb_resultat=count($this->TResultat);
  
  		return $this->TResultat;
  }

  function get_model_for(&$db, $id_hotel, $langue, $type){
  
    $db->Execute("SELECT id FROM hot_model WHERE id_hotel=".$id_hotel
    ." AND type='$type' AND langue='$langue' ORDER BY id DESC ");
    if($db->Get_recordCount()>0) {
      $db->Get_line();
      $id = $db->Get_field('id');
    } else {
      
		switch ($type) {
			case "FACTURE":
				$id = -102;
				break;
			case "RESA":
				$id = -101;
				break;
			case "TAXE_SEJOUR":
				$id = -103;
				break;
			default:
				erreur("Fonction : get_model_for -- MODELE INTROUVABLE : $type");
				break;
		}
    }
  
  	return $id;
  }

  function get_prixsaison_for_chambre(&$db, $id_chambre){
  	$db->Execute("SELECT id
		FROM hot_chambre_prixsaison
		WHERE id_chambre=".$id_chambre);

		$this->TResultat=array();
		while($db->Get_line()){

			$this->TResultat[]=$db->Get_field('id');

		}

		$this->nb_resultat=count($this->TResultat);

		return $this->TResultat;
  }
  
  function get_produit_by_regle(&$db, $id_categorie, $nb_personne, $t_deb, $t_fin, $nuit=0){
    
    $dt_deb = date("Y-m-d", $t_deb);
    $dt_fin = date("Y-m-d", $t_fin);
  
  	$db->Execute("SELECT DISTINCT(a.id_produit) as 'id'
		FROM hot_regle a LEFT JOIN hot_produit b ON (a.id_produit=b.id)
		WHERE (a.id_categorie=0 OR a.id_categorie=$id_categorie)
    AND (a.nb_personne=0 OR a.nb_personne<=$nb_personne)
    AND (a.dt_deb='1970-01-01' OR a.dt_deb<='$dt_deb')
    AND (a.dt_fin='1970-01-01' OR a.dt_fin>='$dt_fin')
    AND (a.nuit_min=0 OR a.nuit_min<=$nuit)
    AND (a.nuit_max=0 OR a.nuit_max>=$nuit)
    AND a.id_hotel=".get_sess_hotel_id()."
    AND b.id IS NOT NULL
    ");

		$this->TResultat=array();
		while($db->Get_line()){

			$this->TResultat[]=$db->Get_field('id');

		}

		$this->nb_resultat=count($this->TResultat);

		return $this->TResultat;
  
  
  }
  
  function get_regle_for_produit(&$db, $id_produit){
  	$db->Execute("SELECT id
		FROM hot_regle
		WHERE id_produit=".$id_produit);

		$this->TResultat=array();
		while($db->Get_line()){

			$this->TResultat[]=$db->Get_field('id');

		}

		$this->nb_resultat=count($this->TResultat);

		return $this->TResultat;
  }
  
  function get_prixsaison_for_categorie(&$db, $id_categorie){
  	$db->Execute("SELECT id
		FROM hot_categorie_prixsaison
		WHERE id_categorie=".$id_categorie);

		$this->TResultat=array();
		while($db->Get_line()){

			$this->TResultat[]=$db->Get_field('id');

		}

		$this->nb_resultat=count($this->TResultat);

		return $this->TResultat;
  }
	function _format($s){
		$trans=array("'"=>"''");
		return strtr($s,$trans);
	}
	function check_login(&$db, $id_utilisateur, $login){

		$db->Execute("SELECT id FROM hot_utilisateur " .
					"WHERE login='".$this->_format($login)."' AND id!=".$id_utilisateur);

		
		if($db->Get_Recordcount()>0){
				$this->TResultat=false;
		}
		else{
			$this->TResultat=true;
		}
			

		return $this->TResultat;
	}
	function check_email(&$db, $id_utilisateur, $email){

		$db->Execute("SELECT id FROM hot_utilisateur " .
					"WHERE email='".$this->_format($email)."' AND id!=".$id_utilisateur);

		
		if($db->Get_Recordcount()>0){
				$this->TResultat=false;
		}
		else{
			$this->TResultat=true;
		}
			

		return $this->TResultat;
	}
	function liste_lien_hotel_utilisateur(&$db, $id_utilisateur){

		$db->Execute("SELECT id
		FROM hot_lien_utilisateur_hotel
		WHERE id_utilisateur=".$id_utilisateur);

		$this->TResultat=array();
		while($db->Get_line()){

			$this->TResultat[]=$db->Get_field('id');

		}

		$this->nb_resultat=count($this->TResultat);

		return $this->TResultat;
	}
	function get_id_by_login(&$db,$login,$password){
    /**
     * Retourne l'id utilisateur correspondant au login/pass
     * Alexis ALGOUD 03/03/2007 19:53:14
     **/


		$db->Execute("SELECT id FROM hot_utilisateur
		WHERE login='".$this->_format($login)."' AND password='".$this->_format($password)."'");
		if($db->Get_recordCount()>0){
			$db->Get_line();
			return $db->Get_field('id');
		}
		else{
			return -1;
		}


	}

	function liste_tous_hotel(&$db, $id_groupe){

		$db->Execute("SELECT id
		FROM hot_hotel
		WHERE id_groupe=".$id_groupe);

		$this->TResultat=array();
		while($db->Get_line()){
			$this->TResultat[]=$db->Get_field('id');
		}

		$this->nb_resultat=count($this->TResultat);

		return $this->TResultat;
	}

	function liste_toute_categorie(&$db, &$id_hotel){
	/**
	 * Donne les categorie d'un hotel en particulier
	 * Alexis ALGOUD 03/03/2007 19:53:25
	 **/

		$db->Execute("SELECT id, libelle
		FROM hot_categorie
		WHERE id_hotel=".$id_hotel);

		$this->TResultat=array();
		while($db->Get_line()){

			$this->TResultat[$db->Get_field('id')]=$db->Get_field('libelle');

		}

		$this->nb_resultat=count($this->TResultat);

		return $this->TResultat;
	}
	function liste_toute_chambre_par_hotel(&$db, &$id_hotel, $to_int=true){
		$db->Execute("SELECT id, LPAD(num,20,'0') as 'numero'
		FROM hot_chambre
		WHERE id_hotel=".$id_hotel."
    ORDER BY ".(($to_int)?"numero":"num")."
    ");

		$this->TResultat=array();
		while($db->Get_line()){

			$this->TResultat[]=$db->Get_field('id');

		}

		$this->nb_resultat=count($this->TResultat);

		return $this->TResultat;
	}
	function liste_toute_chambre_par_categorie(&$db, &$id_categorie){
		$db->Execute("SELECT id
		FROM hot_chambre
		WHERE id_categorie=".$id_categorie);

		$this->TResultat=array();
		while($db->Get_line()){

			$this->TResultat[]=$db->Get_field('id');

		}

		$this->nb_resultat=count($this->TResultat);

		return $this->TResultat;
	}
	function liste_toute_produit_par_reservation(&$db, $id_reservation){
		$db->Execute("SELECT id
		FROM hot_lien_produit_reservation
		WHERE id_reservation=".$id_reservation);

		$this->TResultat=array();
		while($db->Get_line()){
			$this->TResultat[]=$db->Get_field('id');
		}

		$this->nb_resultat=count($this->TResultat);

		return $this->TResultat;
	}
	function liste_toute_reservation_par_produit(&$db, $id_produit){

		$db->Execute("SELECT id
		FROM hot_lien_produit_reservation
		WHERE id_produit=".$id_produit);

		$this->TResultat=array();
		while($db->Get_line()){
			$this->TResultat[]=$db->Get_field('id');
		}

		$this->nb_resultat=count($this->TResultat);

		return $this->TResultat;

	}
	function get_liste_reservation_du_jour(&$db, $date){
  
    list($j,$m,$a)=explode("/", $date);
    
  //$db->db->debug=true;
		$sql="SELECT a.id as 'id'
		FROM hot_reservation a LEFT JOIN hot_chambre b ON(a.id_chambre=b.id)
		WHERE b.id_hotel=".get_sess_hotel_id()."  AND  a.dt_cre LIKE '$a-$m-$j%'";	
		
		$db->Execute($sql);

		$this->TResultat=array();
		while($db->Get_line()){
			$this->TResultat[]=$db->Get_field('id');
		}

		$this->nb_resultat=count($this->TResultat);
//print $this->nb_resultat."<br>";
		return $this->TResultat;

  }
	function liste_toute_reservation_par_chambre(&$db, $id_chambre,$dt_deb=0,$dt_fin=0){

		$sql="SELECT id
		FROM hot_reservation
		WHERE id_chambre=".$id_chambre;
		if($dt_deb>0){
			$date_deb = date('Y-m-d',$dt_deb);	
			$date_fin = date('Y-m-d',$dt_fin);	
		
			$sql.=" AND ('$date_deb' BETWEEN dt_deb AND dt_fin" .
				" OR '$date_fin' BETWEEN dt_deb AND dt_fin " .
				" OR dt_deb BETWEEN '$date_deb' AND '$date_fin')";	
			
		}
		
		$db->Execute($sql);

		$this->TResultat=array();
		while($db->Get_line()){
			$this->TResultat[]=$db->Get_field('id');
		}

		$this->nb_resultat=count($this->TResultat);
//print $this->nb_resultat."<br>";
		return $this->TResultat;

	}
	
	function liste_toute_chambre_sans_reservation_par_date(&$db, $deb, $fin){
  
      $TChambre = $this->liste_toute_chambre_par_hotel($db, get_sess_hotel_id());
      $nb=count($TChambre);
     
      $Tab=array();
      for ($i=0; $i<$nb; $i++) {
        
           $id_chambre = $TChambre[$i];
        
           $nb_resa = count($this->liste_toute_reservation_par_chambre_par_date($db, $id_chambre, $deb, $fin)); 
           if($nb_resa==0){
              $Tab[]=$id_chambre;
           }
           else{
           /* print "$nb_resa pour $id_chambre <br /> ";*/
           }
           
      }
  
  		$this->nb_resultat=count($Tab);
  
  		return $Tab;
  }

	function liste_toute_reservation_par_chambre_par_date(&$db, $id_chambre, $deb, $fin,$id_resa=0){

		$sql = "SELECT id
		FROM hot_reservation
		WHERE id_chambre=".$id_chambre."
		AND dt_deb <= '".date("Y-m-d",$fin)."'
		AND dt_fin >= '".date("Y-m-d",$deb)."'";
		
		if ($id_resa!=0) {
			$sql .= " AND id != $id_resa";
		}
		
		$db->Execute($sql);

		$this->TResultat=array();
		while($db->Get_line()){
			$this->TResultat[]=$db->Get_field('id');
		}

		$this->nb_resultat=count($this->TResultat);

		return $this->TResultat;
	}
	function liste_toute_reservation_arrivee_par_chambre_par_date(&$db, $id_chambre, $deb, $fin,$id_resa=0){

		$sql = "SELECT id
		FROM hot_reservation
		WHERE id_chambre=".$id_chambre."
		AND dt_deb >= '".date("Y-m-d",$deb)."'
		AND dt_deb<= '".date("Y-m-d",$fin)."'";
		
		if ($id_resa!=0) {
			$sql .= " AND id != $id_resa";
		}
		
		$sql.=" ORDER BY dt_deb ASC";

		$db->Execute($sql);

		$this->TResultat=array();
		while($db->Get_line()){
			$this->TResultat[]=$db->Get_field('id');
		}

		$this->nb_resultat=count($this->TResultat);

		return $this->TResultat;
	}
	
	function liste_toute_reservation_par_date(&$db, $deb, $fin){

		$sql = "SELECT id
		FROM hot_reservation
		WHERE id_hotel = ".get_sess_hotel_id()."
		AND dt_deb <= '".date("Y-m-d",$fin)."'
		AND dt_fin >= '".date("Y-m-d",$deb)."'";
		
		$db->Execute($sql);

		$this->TResultat=array();
		while($db->Get_line()){
			$this->TResultat[]=$db->Get_field('id');
		}

		$this->nb_resultat=count($this->TResultat);

		return $this->TResultat;
	}

	function get_param_by_key(&$db, $key) {

		$sql = "SELECT id
		FROM hot_param
		WHERE clef='$key'";

		$db->Execute($sql);

		$this->TResultat=array();
		while($db->Get_line()){
			$this->TResultat[]=$db->Get_field('id');
		}

		$this->nb_resultat=count($this->TResultat);

		return $this->TResultat;
	}
	
	function liste_toute_facture_par_numero(&$db, $num){

		$sql = "SELECT id
		FROM hot_facture
		WHERE id_hotel = ".get_sess_hotel_id()."
		AND numero='$num'";
		
		$db->Execute($sql);

		$this->TResultat=array();
		while($db->Get_line()){
			$this->TResultat[]=$db->Get_field('id');
		}

		$this->nb_resultat=count($this->TResultat);

		return $this->TResultat;
	}
	
	function liste_tous_param (&$db) {
		
		$sql = "SELECT id
		FROM hot_param";
		
		$db->Execute($sql);

		$this->TResultat=array();
		while($db->Get_line()){
			$this->TResultat[]=$db->Get_field('id');
		}

		$this->nb_resultat=count($this->TResultat);

		return $this->TResultat;
	}

	function liste_toute_ligne_par_facture (&$db, $id_facture) {
		$sql="SELECT id
		FROM hot_facture_ligne
		WHERE id_facture=$id_facture
		ORDER BY rang";
		
		$db->Execute($sql);

		$this->TResultat=array();
		while($db->Get_line()){
			$this->TResultat[]=$db->Get_field('id');
		}

		$this->nb_resultat=count($this->TResultat);

		return $this->TResultat;
	}
	
	function nombre_chambre ($db) {
		$sql = "SELECT count(*) as nb
				FROM hot_chambre
				WHERE id_hotel = ".get_sess_hotel_id();
				
		$db->Execute($sql);
		$db->Get_line();
		$this->nb_resultat = $db->Get_field('nb');
		
		return $this->nb_resultat;
	}
	
	function nombre_categorie ($db) {
		$sql = "SELECT count(*) as nb
				FROM hot_categorie
				WHERE id_hotel = ".get_sess_hotel_id();
				
		$db->Execute($sql);
		$db->Get_line();
		$this->nb_resultat = $db->Get_field('nb');
		
		return $this->nb_resultat;
	}
	
	function nombre_produit ($db) {
		$sql = "SELECT count(*) as nb
				FROM hot_produit
				WHERE id_hotel = ".get_sess_hotel_id();
				
		$db->Execute($sql);
		$db->Get_line();
		$this->nb_resultat = $db->Get_field('nb');
		
		return $this->nb_resultat;
	}
	
	function nombre_client ($db) {
		$sql = "SELECT count(*) as nb
				FROM hot_client
				WHERE id_hotel = ".get_sess_hotel_id();
				
		$db->Execute($sql);
		$db->Get_line();
		$this->nb_resultat = $db->Get_field('nb');
		
		return $this->nb_resultat;
	}
}

?>
