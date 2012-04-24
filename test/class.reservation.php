<?
/**
 * Permet la prise de réservation
 *
 **/
class TReservation{
	/**
	 * Constructor
	 * @access protected
	 * @todo guess...
	 */
	function TReservation(){
		$this->id=0;
		$this->id_hotel=0;
		$this->id_chambre=0;
		$this->id_client=0;
		$this->dt_deb=time();
		$this->dt_fin=time();
		$this->etat="RESA";
		$this->prix=0.0;
		$this->montant=0.0;
		$this->acompte=0.0;
		
		$this->nb_personne_age1=0;
		$this->nb_personne_age2=0;
		$this->nb_personne_age3=0;
		$this->nb_animaux=0;
		
		$this->mt_personne_suppl=0.0;
		$this->mt_animaux=0.0;
		$this->mt_taxe_sejour=0.0;

    $this->frais_resa=0.0;

		$this->note="";
		$this->old_id_chambre=0;

		$this->libelle="Nouvelle réservation";

		$this->TEtat=array(
			"RESA"=>"Prise"
			,"CONFIRM"=>"Confirmée"
			,"FACTUREE"=>"Facturée"
			,"PAYEE"=>"Payée"
		);

		$this->client = new TClient;
		$this->nom_client="";

		$this->chambre = new TChambre;

		$this->dt_cre=time();
		$this->dt_maj=time();

		$this->TLienProduit = array();

    $this->TAge=array(); // contient les ages, montant supplémentaire, pourcentage, taxe séjour, pourcentage
    
	}
	function add_produit(&$db, $id_produit){
		$nb=count($this->TLienProduit);
		$this->TLienProduit[$nb]=new TLienReservationProduit;
		$this->TLienProduit[$nb]->id_produit = $id_produit;

		$this->TLienProduit[$nb]->load_produit($db);

		$this->TLienProduit[$nb]->prix = $this->TLienProduit[$nb]->produit->prix;

	}
	function del_produit(&$db, $iTab){
		$this->TLienProduit[$iTab]->to_delete = true;

	}
	function get_nbJour($time = 0){

		if($time==0)$time=$this->dt_deb;

		$nb_jour = ceil(($this->dt_fin - $time) / 86400) +1;
		return $nb_jour;
	}

  function load(&$db,$id, $client=false, $chambre=false, $produit=false){

		$db->Execute("SELECT id_chambre, id_client, etat, note, dt_deb, dt_fin, prix, montant,acompte,
				nb_personne_age1,nb_personne_age2,nb_personne_age3,nb_animaux,
				mt_personne_suppl,mt_taxe_sejour,mt_animaux,dt_cre,dt_maj
				,frais_resa,id_hotel
		FROM hot_reservation
		WHERE id=$id ");
		if($db->Get_recordCount()>0){
			$db->Get_line();

			$this->id=$id;
			$this->id_hotel=$db->Get_field('id_hotel');
			$this->id_chambre=$db->Get_field('id_chambre');
			$this->old_id_chambre=$this->id_chambre;
			$this->id_client=$db->Get_field('id_client');
			$this->etat=$db->Get_field('etat');
			$this->note=$db->Get_field('note');

      $this->acompte=$db->Get_field('acompte');

			$this->dt_deb=strtotime($db->Get_field('dt_deb'));
			$this->dt_fin=strtotime($db->Get_field('dt_fin'));
			$this->prix=$db->Get_field('prix');
			$this->montant=$db->Get_field('montant');
			
			$this->nb_personne_age1=$db->Get_field('nb_personne_age1');
			$this->nb_personne_age2=$db->Get_field('nb_personne_age2');
			$this->nb_personne_age3=$db->Get_field('nb_personne_age3');
			$this->nb_animaux=$db->Get_field('nb_animaux');
			
      $this->frais_resa=$db->Get_field('frais_resa');
			
			
			
			$this->mt_personne_suppl=$db->Get_field('mt_personne_suppl');
			$this->mt_animaux=$db->Get_field('mt_animaux');
			$this->mt_taxe_sejour=$db->Get_field('mt_taxe_sejour');

			$this->dt_cre=strtotime($db->Get_field('dt_cre'));
			$this->dt_maj=strtotime($db->Get_field('dt_maj'));

			if($client) {
				$this->load_client($db);
			}
			if($chambre) {
				$this->load_chambre($db);
			}
			if($produit){
				$this->load_produit($db);
			}

			$this->set_libelle();

			return true;
		}
		else {
			return false;
		}


	}
	/**
	 * Déplace Une réservation vers une chambre et un jour
	 * Alexis ALGOUD 21/05/2007 00:18:35
	 **/
	function move(&$db, $id_chambre , $jour_time){

		$ecart = $this->dt_fin - $this->dt_deb;

		$old_dt_deb = $this->dt_deb;
		$old_dt_fin = $this->dt_fin;
		$old_id_chambre = $this->id_chambre;

		$this->dt_deb = $jour_time;
		$this->dt_fin = $this->dt_deb + $ecart;
		$this->id_chambre = $id_chambre;


		if(!$this->check_date_resa($db)){
			$this->dt_deb = $old_dt_deb;
			$this->dt_fin = $old_dt_fin;
			$this->id_chambre = $old_id_chambre;
			return false;
		}
		else{
			$this->load_chambre($db);
			return true;
		}
	}
	function set_libelle(){
		$this->libelle=$this->nom_client." du ".$this->get_dtdeb()." au ".$this->get_dtfin();
	}

	function load_produit (&$db) {
		$this->TLienProduit = array();

		$r=new TRequete;
		$Tab=$r->liste_toute_produit_par_reservation($db, $this->id);

		$nb=count($Tab);
		for ($i = 0; $i < $nb; $i++) {
			$this->TLienProduit[$i]=new TLienReservationProduit;
			$this->TLienProduit[$i]->load($db, $Tab[$i], true, false);
		} // for


	}
	function load_client(&$db){
		if($this->id_client>0){
			$this->client->load($db, $this->id_client);
			//$this->nom_client=$this->client->civilite." ".$this->client->prenom." ".$this->client->nom;
			$this->nom_client=$this->client->nom;
			$this->set_libelle();
		}
	}
	function load_chambre(&$db){
		if($this->id_chambre>0){
			$this->chambre->load($db, $this->id_chambre);
		}
	}
	function get_prix(){
    
    $this->prix = $this->chambre->get_prix($this->dt_deb);
  
    $this->frais_resa = $this->chambre->categorie->frais_resa;
  
  }
  function get_ages(){
  
    $h = & $_SESSION[SESS_HOTEL];
    
    $ts = $h->get_parameter('taxe_sejour_mt');
    $ts_age1 = $h->get_parameter('taxe_sejour_age1');
    $ts_age2 = $h->get_parameter('taxe_sejour_age2');
    $ts_tx1 = $h->get_parameter('taxe_sejour_taux1');
    $ts_tx2 = $h->get_parameter('taxe_sejour_taux2');
  
    $Tab = array(); 
    $Tab[$ts_age1]=true;
    $Tab[$ts_age2]=true;
    
    $TAgeCat = &$this->chambre->categorie->TAge;
    foreach ($TAgeCat as $key=>$value) {
    	
    	if(!is_null($value['min']))$Tab[$value['min']]=true;
    	if(!is_null($value['max']))$Tab[$value['max']]=true;
    	
    }
    
    ksort($Tab);
    
    $this->TAge = array();$old_age = null;
    foreach ($Tab as $age=>$value) {
    	
    	$row['min'] = $old_age;
    	$row['max'] = $age;
    	
      $row['ts'] = $ts;
    	$row['ts_percent'] = $ts_percent;
    	$row['supplement'] = $supplement;
    	$row['supplement_percent'] = $supplement_percent;
    	
    	
    	$this->TAge[]=$row;
    	
    	$old_age = $age;
    }
  
    print_r($this->TAge);
  
  
  }
  function get_taxe_sejour($jour=true, $age1=null, $age2=null){
  // retourne la taxe de séjour selon le montant annoncé
  
  
  }
	function save(&$db){

			$query['id_hotel']=$this->id_hotel;
			$query['id_chambre']=$this->id_chambre;
			$query['id_client']=$this->id_client;
			$query['etat']=$this->etat;
			$query['note']=$this->note;
    	$query['acompte']=$this->acompte;

			$query['dt_deb']=date("Y-m-d H:i:s",$this->dt_deb);
			$query['dt_fin']=date("Y-m-d H:i:s",$this->dt_fin);
			$query['prix']=$this->prix;
			$query['montant']=$this->montant;
			
			$query['nb_personne_age1']=$this->nb_personne_age1;
			$query['nb_personne_age2']=$this->nb_personne_age2;
			$query['nb_personne_age3']=$this->nb_personne_age3;
			$query['nb_animaux']=$this->nb_animaux;
			
			$query['frais_resa']=$this->frais_resa;
			
			
			
			$query['mt_personne_suppl']=$this->mt_personne_suppl;
			$query['mt_animaux']=$this->mt_animaux;
			$query['mt_taxe_sejour']=$this->mt_taxe_sejour;


			$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
			$query['dt_maj']=date("Y-m-d H:i:s");

			$key[0]='id';

			if($this->id==0){
				$this->get_newid($db);
				$query['id']=$this->id;
				$db->dbinsert('hot_reservation',$query);
			}
			else {
				$query['id']=$this->id;
				$db->dbupdate('hot_reservation',$query,$key);
			}

			$this->save_lien_produit($db);

	}
	function save_lien_produit(&$db){
		$nb=count($this->TLienProduit);
		for ($i = 0; $i < $nb; $i++) {
			$this->TLienProduit[$i]->id_reservation = $this->id;
			$this->TLienProduit[$i]->save($db);
		} // for
	}

	function delete(&$db){
		if($this->id!=0){
			$db->dbdelete('hot_reservation',array("id"=>$this->id),array(0=>'id'));
			$db->dbdelete('hot_lien_produit_reservation',array("id_reservation"=>$this->id),array(0=>'id_reservation'));
		}
	}

	function get_newid(&$db){

		$sql="SELECT max(id) as 'maxi' FROM hot_reservation";
		$db->Execute($sql);
		$db->Get_line();
		$this->id = (double)$db->Get_field('maxi')+1;

	}
	function get_dtdeb(){
		return date("d/m/Y",$this->dt_deb);
	}

	function get_dtfin(){
		return date("d/m/Y",$this->dt_fin);
	}

	function set_dtdeb($date){
		list($d,$m,$y) = explode("/",$date);
		$this->dt_deb = mktime(0,0,0,$m,$d,$y);
	}

	function set_dtfin($date){
		list($d,$m,$y) = explode("/",$date);
		$this->dt_fin = mktime(0,0,0,$m,$d,$y);
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
	function more_day($db){
		/**
		 * Ajoute une jour à la réservation
		 * Alexis ALGOUD 02/04/2007 21:14:01
		 **/
		$old_dtfin = $this->dt_fin;

		$this->dt_fin += 86400;
		if(!$this->check_date_resa($db)){
			$this->dt_fin = $old_dtfin;
			return false;
		}
		else{
			return true;
		}

	}
	function less_day($db){
		/**
		 * elève une jour à la réservation
		 * Alexis ALGOUD 02/04/2007 21:14:01
		 **/

		$this->dt_fin -= 86400;

		if($this->dt_fin<$this->dt_deb){
			$this->dt_fin = $this->dt_deb;
			return false;
		}

		return true;
	}
	function more_day_left($db){
		/**
		 * Ajoute une jour à la réservation
		 * Alexis ALGOUD 02/04/2007 21:14:01
		 **/
		$old_dtdeb = $this->dt_deb;

		$this->dt_deb -= 86400;
		if(!$this->check_date_resa($db)){
			$this->dt_deb = $old_dtdeb;
			return false;
		}
		else{
			return true;
		}

	}
	function less_day_left($db){
		/**
		 * elève une jour à la réservation
		 * Alexis ALGOUD 02/04/2007 21:14:01
		 **/

		$this->dt_deb += 86400;

		if($this->dt_fin<$this->dt_deb){
			$this->dt_deb = $this->dt_fin;
			return false;
		}

		return true;
	}

	/**
	 * Fonction qui vérifie si la réservation n'est pas en conflit
	 * avec une autre au niveau des dates
	 */
	function check_date_resa (&$db) {

		$req = new TRequete;

		$TResa = $req->liste_toute_reservation_par_chambre_par_date($db, $this->id_chambre, $this->dt_deb, $this->dt_fin, $this->id);

		if ($req->nb_resultat > 0) {
			return false;
		}

		return true;
	}

	/**
	 * Fonction qui calcule le prix de la réservation
	 * à partir des dates et du prix de la chambre
	 */
	function calcule_prix () {
		$day = $this->dt_fin - $this->dt_deb;
		$nb_jour = ($day / 86400)+1;
		$this->montant = $this->prix * $nb_jour;
//		echo "DT DEB : ".$this->dt_deb;
//		echo "<br>DT FIN : ".$this->dt_fin;
//		echo "<br>DIFF: ".$day;
//		echo "<br>NB: ".$nb_jour;

	}
	
	
	function calcule_prix_annexe () {

		/*
		 * @todo load_categorie doit récupérer les données de la catégorie
		 * et non pas les libellé de toutes les catégories
		 * Classe requete à revoir (utilisation de $id_hotel...)
		 */
		$this->mt_animaux = $this->nb_animaux * $this->chambre->categorie->montant_animaux;
		
		$nb_limite_personne = $this->chambre->categorie->nb_limite_personne;
		$nb_personne = $this->nb_personne_age1 + $this->nb_personne_age2 + $this->nb_personne_age3;
		
		if ($nb_personne >= $nb_limite_personne) {
			$this->mt_personne_suppl = ($nb_personne - $nb_limite_personne + 1) * $this->chambre->categorie->montant_personne;
		}
		
		$mt_taxe = $_SESSION[SESS_HOTEL]->get_parameter("taxe_sejour_mt");
		$taux1 = $_SESSION[SESS_HOTEL]->get_parameter("taxe_sejour_taux1") / 100;
		$taux2 = $_SESSION[SESS_HOTEL]->get_parameter("taxe_sejour_taux2") / 100;

		$this->mt_taxe_sejour = $this->nb_personne_age1 * $mt_taxe * $taux1;
		$this->mt_taxe_sejour += $this->nb_personne_age2 * $mt_taxe * $taux2;
		$this->mt_taxe_sejour += $this->nb_personne_age3 * $mt_taxe;
	}
}

?>
