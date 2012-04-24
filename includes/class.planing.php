<?

/**
 * CREATION 05/03/2007
 * AUTEUR Maxime KOHLHAAS
 *
 *
 * MKO 07/03/2007
 * Constructeur :
 *  - ajout de dt_lundi (pour garder le jour courant dans dt_current
 *  et mettre le lundi de la semaine que l'on affiche dans dt_lundi)
 *  - ajout de l'appel à init() (que je n'avait pas utilisé lundi...)
 * init($time,$week) :
 *  la méthode initialise dt_lundi avec le lundi de la semaine
 *  spécifiée dans week (0 = lundi de la semaine courante)
 *  $week est récupéré dans le bin ($_REQUEST) puis passé dans
 *  le constructeur (pour init()) et dans la méthode affiche()
 *  pour créer les boutons de navigation
 * liste_jour() :
 *  initialise la liste des jours de la semaine du lundi de dt_lundi
 *
 * Je n'ai pas utilisé les méthode semaine_suivante () et
 * semaine_precedente () ...
 *
 **/
class TPlaning{
	/**
	 * Constructor
	 * @access protected
	 */
	function TPlaning($id_hotel=0,$week=0){

		$this->dt_current=strtotime(date('Y-m-d', time()));
		$this->dt_lundi=$this->dt_current;
		$this->TJour=array();
		$this->nb_jour = 0;
		$this->nb_semaine = 1;
		$this->TChambre=array();
		$this->nb_chambre = 0;
		$this->id_hotel=$id_hotel;

		$this->currentResa="";
	}

	function liste_chambre (&$db, $TParam=array()) {
/**
 * Retroune la liste des chambres valides pour le pl
 * Maxime KOHLHAAS 05/03/2007 20:44:35
 **/

 		$req = new TRequete();
 		
 		$Tab=array();
 		$this->TChambre=array();
/*
    if(isset($TParam['categorie'])){
    $where.="AND a.id_categorie='".$TParam['categorie']."' ";
   
    if($_REQUEST['dt_deb']!=""){
      $r=new TRequete;
      
      if($_REQUEST['dt_fin']=="")$_REQUEST['dt_fin'] = $_REQUEST['dt_deb'];
      
      list($jj, $mm, $aaaa) = explode("/",$_REQUEST['dt_deb']);
      list($jj2, $mm2, $aaaa2) = explode("/",$_REQUEST['dt_fin']);
      
      $time_deb = mktime(0,0,0,$mm, $jj, $aaaa);
      $time_fin = mktime(0,0,0,$mm2, $jj2, $aaaa2);
      
      $TChambre = $r->liste_toute_chambre_sans_reservation_par_date($db,$time_deb,$time_fin);
    
      $collect_resa_for_date=true;
      
      $where.=" AND a.id IN(".implode(",", $TChambre).") ";
    }
   
    
  }*/

		
 		$Tab=$req->liste_toute_chambre_par_hotel($db,$this->id_hotel);
 		$this->nb_chambre = count($Tab);
		for ($i = 0; $i < $this->nb_chambre; $i++) {
			$this->TChambre[$i]=new TChambre;
			$this->TChambre[$i]->dt_deb_for_resa=$this->dt_lundi;
			$this->TChambre[$i]->dt_fin_for_resa=$this->dt_lundi+(86400*((7*$this->nb_semaine)-1));
			
			$this->TChambre[$i]->load($db, $Tab[$i],false,true);
			//, $dt_deb, $dt_fin
		} // for



 		return $this->TChambre;
	}

	function liste_jour ($nb_semaine="") {
		/**
		 * Retoune liste des jours à afficher dans la planing
		 * Maxime KOHLHAAS 05/03/2007 20:44:55
		 **/
		 if($nb_semaine!=""){
       $this->nb_semaine=$nb_semaine; 
     }
		 
		 
		 $this->TJour=array();

     $nb_jour =  $this->nb_semaine * 7;

		 for($i = 0; $i < $nb_jour; $i++){
			$this->TJour[$i] = new TJour;
			$this->TJour[$i]->init($this->dt_lundi + (86400 * $i));
		 } // for
		 
		$this->nb_jour = count($this->TJour);

		return $this->TJour;
	}

	function init ($date="") {
		/**
		 * Date jj/mm/aaaa => permet de trouver le 1er lundi de la semaine
		 * initalise le jour courant du planing et les réservation associées
		 * Maxime KOHLHAAS 05/03/2007 20:45:38
		 **/
		if ($date != "") {
			list($jour, $mois, $annee)=explode("/", $date);
			$time = mktime(0,0,0,$mois,$jour,$annee);
		} else {
			$time = $this->dt_current;
		}

		$numero_jour = date('w', $time);
		$this->dt_lundi=$this->get_lundi($time, $numero_jour);
	}
	function get_dtcurrent(){
		
		return date("d/m/Y", $this->dt_current);
	}
	function get_dtlundi(){
		
		return date("d/m/Y", $this->dt_lundi);
	}
	function get_lundi ($time, $numero_jour) {
		switch($numero_jour){
		 	case 0:
		 		return $time-(86400 * 6);
		 		break;
		 	case 1:
		 		return $time;
		 		break;
		 	case 2:
		 		return $time-(86400 * 1);
		 		break;
		 	case 3:
		 		return $time-(86400 * 2);
		 		break;
		 	case 4:
		 		return $time-(86400 * 3);
		 		break;
		 	case 5:
		 		return $time-(86400 * 4);
		 		break;
		 	case 6:
		 		return $time-(86400 * 5);
		 		break;
		 } // switch
	}
	function semaine_suivante($x = 1) {
/**
 * Positionne la date courante à la s suivante
 * Maxime KOHLHAAS 05/03/2007 20:49:31
 **/
 		$this->dt_lundi += 86400 * 7 * $x;
	}
	function semaine_precedente ($x = 1) {
/**
 * Positionne la date courante à la s précédente
 * Maxime KOHLHAAS 05/03/2007 20:49:50
 **/
 		$this->dt_lundi -= 86400 * 7 * $x;
	}
	//function charge_reservation ($date) {
	/**
		 * Charge les réservation du jour donné
		 * Maxime KOHLHAAS 05/03/2007 20:51:48
		 **/
		
		
		
		//return false;
	//}
}

class TJour {
	function TJour() {
		$this->time = 0;
		$this->date = "";
		$this->jour = "";
		
		$this->TJourTrans=array(
			"Mon"=>"Lundi"
			,"Tue"=>"Mardi"
			,"Wed"=>"Mercredi"
			,"Thu"=>"Jeudi"
			,"Fri"=>"Vendredi"
			,"Sat"=>"Samedi"
			,"Sun"=>"Dimanche"
		);
	}

	function init ($time) {
		
		$time=strtotime(date('Y-m-d', $time));	
	
	  	$this->time = $time;
	  	$this->date = date('d/m/Y', $time);
	  	$this->jour = strtr(date('D', $time), $this->TJourTrans);
	  	
	  	$this->nom = $this->jour." ".$this->date;
	  	
	}
} // class 

?>