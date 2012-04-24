<?
/**
 * Permet la prise de r?servation
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
		$this->TPrix=array();
		$this->montant=0.0;
		$this->acompte=0.0;
		$this->nb_jour=0;
		
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

    $this->TAge=array(); // contient les ages, montant suppl?mentaire, pourcentage, taxe s?jour, pourcentage
    
    $this->auto_import_produit = 0; // si des produits ont d?j? ?t? import? sur r?gle
    
    
    $this->age_pour_taxe_sejour ="";
    $this->age_pour_supplement ="";
    $this->nb_limite_personne = 0; // personne non sup
    
    
    $this->is_prix_negoce = 0; // montant n?goci? ?
	}
	
	function get_auto_produit(&$db){
  //$db->db->debug=true;
    $r=new TRequete;
    $TProduit = $r->get_produit_by_regle($db, $this->chambre->id_categorie
    , $this->get_nb_personne(), $this->dt_deb, $this->dt_fin, $this->get_nbJour());
    
    $nb=count($TProduit);
    for ($i=0; $i<$nb; $i++) {
    	$this->add_produit($db, $TProduit[$i]);
    }
  
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
				mt_personne_suppl,mt_taxe_sejour,mt_animaux,dt_cre,dt_maj,TAge
				,frais_resa,id_hotel, auto_import_produit
				,age_pour_supplement,age_pour_taxe_sejour
				,is_prix_negoce, TPrix
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

      $this->is_prix_negoce = $db->Get_field('is_prix_negoce');

      $this->age_pour_taxe_sejour = $db->Get_field('age_pour_taxe_sejour');
      $this->age_pour_supplement = $db->Get_field('age_pour_supplement');
      $this->nb_limite_personne = $db->Get_field('nb_limite_personne');
      
      $this->auto_import_produit = $db->Get_field('auto_import_produit');

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
			
			
			$res = unserialize($db->Get_field('TAge'));
			if(is_array($res)){
				$this->TAge=$res;
			}
			$res = unserialize($db->Get_field('TPrix'));
			if(is_array($res)){
				$this->TPrix=$res;
			}


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
	 * D?place Une r?servation vers une chambre et un jour
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
	  $this->libelle='';
	
	  if($this->chambre->id!=0) {
      $this->libelle.='('.$this->chambre->num.') ';
    }
	
		$this->libelle.=$this->nom_client.' du '.$this->get_dtdeb().' au '.$this->get_dtfin(true);
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
    
	    if($this->is_prix_negoce==0) {

	      $this->frais_resa = $this->chambre->categorie->frais_resa;
	      $this->TPrix=$this->chambre->get_prix_for_resa($this->dt_deb, $this->dt_fin);
	      /*$this->prix = $this->chambre->get_prix($this->dt_deb);*/
	      // Le prix est une composante complexe calculé à partir du tableau
	      $this->prix = $this->give_prix_by_TPrix();
	      $this->calcule_prix();
	    }
	    
	 }
	 function give_prix_by_TPrix(){
		$montant=0;
		$nb_jour = 0;
		foreach($this->TPrix as $key=>$row){
			// time_deb, time_fin, nb_jour, prix
			$nb_jour+=$row['nb_jour'];
			$montant+=$row['prix'] * $row['nb_jour'];
		}
		$prix = $montant / $nb_jour;

		return $prix;
	 }	
  function get_ages(){
  
    $h = & $_SESSION[SESS_HOTEL];
    
    $ts = $h->get_parameter('taxe_sejour_mt');
    $ts_age1 = $h->get_parameter('taxe_sejour_age1');
    $ts_age2 = $h->get_parameter('taxe_sejour_age2');
    $ts_tx1 = $h->get_parameter('taxe_sejour_taux1')/100;
    $ts_tx2 = $h->get_parameter('taxe_sejour_taux2')/100;
    
    $ts_mt1 = $h->get_parameter('taxe_sejour_mt1');
    $ts_mt2 = $h->get_parameter('taxe_sejour_mt2');

    $this->nb_limite_personne = $this->chambre->categorie->nb_limite_personne;
    $this->age_pour_taxe_sejour=$ts_age1."|".$ts_age2;

    
  /*print "$ts_tx1 - $ts_tx2";*/
    $Tab = array(); 
    $Tab[$ts_age1]=true;
    $Tab[$ts_age2]=true;
    
    $this->age_pour_supplement="";
    $TAgeCat = &$this->chambre->categorie->TAge;
    foreach ($TAgeCat as $key=>$value) {
    	
    	if(!is_null($value['min'])){
        $Tab[$value['min']]=true;
      }
    	if(!is_null($value['max'])){
        $Tab[$value['max']]=true;
      }
     
      if($this->age_pour_supplement!="")$this->age_pour_supplement.="|";
      
      @$this->age_pour_supplement.=$value['min']."-".$value['max'];
          	
    }
    $supplement = $this->chambre->categorie->montant_personne;
    
    ksort($Tab);
    
    $this->TAge = array();$old_age = null;
    foreach ($Tab as $age=>$value) {
    	
    	$row['min'] = $old_age;
    	$row['max'] = $age;
    	
      $row['ts'] = $ts;
      
      $row['ts_mt'] = $this->_get_age_tsmt($row, $ts_age1, $ts_age2, $ts_mt1, $ts_mt2, $ts);
    	$row['ts_taux'] = $this->_get_age_tstaux($row, $ts_age1, $ts_age2, $ts_tx1, $ts_tx2);
    	$row['supplement'] = $supplement;
    	$row['supplement_taux'] = $this->_get_age_suppltaux($row, $TAgeCat);
    	$row['supplement_mt'] = $this->_get_age_supplmt($row, $TAgeCat);
    	$row['nb'] = 0;
    	
    	
    	$this->TAge[]=$row;
    	
    	$old_age = $age;
    }
    
    $row['min'] = $age;
  	$row['max'] = null;
  	
    $row['ts'] = $ts;
    $row['ts_mt'] = $this->_get_age_tsmt($row, $ts_age1, $ts_age2, $ts_mt1, $ts_mt2, $ts);
    $row['ts_taux'] = $this->_get_age_tstaux($row, $ts_age1, $ts_age2, $ts_tx1, $ts_tx2);
    $row['supplement'] = $supplement;
  	$row['supplement_taux'] =$this->_get_age_suppltaux($row, $TAgeCat);
  	$row['supplement_mt'] = $this->_get_age_supplmt($row, $TAgeCat);
    $row['nb'] = 0;
  	  	
  	$this->TAge[]=$row;
    /*
  print "<pre>";
    print_r($this->TAge);
  print "</pre>";*/
  
  }
  
  function _get_age_suppltaux(&$row, &$TAgeCat){
  // assigne taux suppl en fct de l'?ge
    $tx = 1;
  
    $min = (double)$row['min'];
    $max = (double)$row['max'];
    if($max==0)$max=9999;
  
    foreach ($TAgeCat as $key=>$age) {
      
      $cat_min = (double)$age['min'];
      $cat_max = (double)$age['max'];
      if($cat_max==0)$cat_max=9999;
      
    //  print "$min & $max contre ".$cat_min." & ".$cat_max."<br />";
    if(
         ($min>=$cat_min)
      && ($max<=$cat_max)
      ){
     // print "!!!<br />";
        $tx = $age['percent'] / 100;
        break;
      }
    	
    }
  
    return $tx;
  
  }
  function _get_age_supplmt(&$row, &$TAgeCat){
  // assigne taux suppl en fct de l'?ge
    $mt = 0;
  
    $min = (double)$row['min'];
    $max = (double)$row['max'];
    if($max==0)$max=9999;
  
    foreach ($TAgeCat as $key=>$age) {
      
      $cat_min = (double)$age['min'];
      $cat_max = (double)$age['max'];
      if($cat_max==0)$cat_max=9999;
      
    //  print "$min & $max contre ".$cat_min." & ".$cat_max."<br />";
    if(
         ($min>=$cat_min)
      && ($max<=$cat_max)
      ){
     // print "!!!<br />";
        $mt = $age['montant'];
        break;
      }
    	
    }
  
    return $mt;
  
  }
  
  function _get_age_tstaux(&$row, $ts_age1, $ts_age2, $ts_tx1, $ts_tx2){
  // assigne les taux ts en fct de l'age
    $tx = 1;
  
    $min = $row['min'];
    $max = $row['max'];
    
    if($max<=$ts_age1 && !is_null($max))$tx = $ts_tx1;
    else if($max<=$ts_age2 && !is_null($max))$tx = $ts_tx2;
    
    return $tx;
  
  }
  function _get_age_tsmt(&$row, $ts_age1, $ts_age2, $ts_mt1, $ts_mt2, $ts){
  // assigne les taux ts en fct de l'age
    $mt = $ts;
  
    $min = $row['min'];
    $max = $row['max'];
    
    if($max<=$ts_age1 && !is_null($max))$mt = $ts_mt1;
    else if($max<=$ts_age2 && !is_null($max))$mt = $ts_mt2;
    
    return $mt;
  
  }
  
  function get_supplement_unit(&$nb_personne, $age_min=0, $age_max=9999){
  /*
    trouve le suppl?nt de la tranche et le nombre de personne concern?e
  */
    $supplement = 0;
    $nb=count($this->TAge);
    $nb_personne= 0; 
    
    //print "Test pour $age_min && $age_max<br />";
    
    for ($i=$nb-1; $i>=0; $i--) {
    // on prends les + vieux pour d?compter
    
      $age = & $this->TAge[$i];
      
      $test_age_min = (int)$age['min'];
      $test_age_max = ((int)$age['max']==0)?9999:(int)$age['max'];
      
      if($test_age_min>=$age_min 
      && $test_age_max<=$age_max ){
      
          //print "Ok pour $test_age_min && $test_age_max<br />";
         
          // calcul du nombre ? suppl? x/x au pr?sent
          $nb_no_supp = (isset($age['nb_no_supp']))?$age['nb_no_supp']:0; 
          $nb_for_supp = (double)$age['nb'] - $nb_no_supp;
        
          $nb_personne+=$nb_for_supp;
         // print "Nombre de personne trouv?e : $nb_personne+=$nb_for_supp<br />";
        
          if((double)$age['supplement_mt']!=0){
            $supplement+= $nb_for_supp * (double)$age['supplement_mt'];
          }
          else{
            $supplement+= $nb_for_supp * (double)$age['supplement']* (double)$age['supplement_taux'];
          }
        
      }
      else{
      // print "<b>Non</b> $test_age_min && $test_age_max<br />";
       
      }
    }
   
    $nb_jour = $this->get_nbJour();
//  print "$supplement pour $nb_personne x $nb_jour<br />";   
    
    $supplement=$supplement * $nb_jour;
    $nb_personne=$nb_personne * $nb_jour;
    
    return $supplement;
    
  }
  
  function get_supplement($jour=false, $age_min=0, $age_max=9999){
  // retourne le suppl?ment (-) nombre personne incluse (prise parmis les plus ?g?e) 
    $supplement = 0;
    $nb=count($this->TAge);
    
    /*$nb_limite_personne = $this->chambre->categorie->nb_limite_personne;
    $nb_no_add=$nb_limite_personne;*/
    
    //print "limite : $nb_limite_personne<br />";
    for ($i=$nb-1; $i>=0; $i--) {
    // on prends les + vieux pour d?compter
    
      $age = & $this->TAge[$i];
      
      $test_age_min = (int)$age['min'];
      $test_age_max = ((int)$age['max']==0)?9999:(int)$age['max'];
      
      
      if($test_age_min>=$age_min 
      && $test_age_max<=$age_max ){
      
       
        
        
        
        //if($nb_no_add==0){
        /*
          On rajoute simplement le montant
          aucune exclusion n'est faite
        */
        
          
          // calcul du nombre ? suppl? x/x au pr?sent
          $nb_no_supp = (isset($age['nb_no_supp']))?$age['nb_no_supp']:0; 
          $nb_for_supp = (double)$age['nb'] - $nb_no_supp;
        
         /*print "$supplement == ".$test_age_min.">=$age_min && ".$test_age_max
        ."<=$age_max ($nb_for_supp(".$age['nb'].") * ".$age['supplement']." * "
        .$age['supplement_taux'].")<br />";*/        
        
          if($age['supplement_mt']!=0){
            $supplement+= $nb_for_supp * (double)$age['supplement_mt'];
          }
          else{
            $supplement+= $nb_for_supp * (double)$age['supplement']* (double)$age['supplement_taux'];
          }
          
        /*}
        else if($nb_no_add-(double)$age['nb']>=0){
        *//*
          on d?compte, il ne sont pas "suppl?ment?"
        */
         /* $nb_no_add -=(double)$age['nb'];
        }
        else{
        /* cas ou on commence le d?compte
          ? Parfois je ne sais plus trop
            -> Cas o? nb_no_add > nb personne ? suppl?
        */
          /*$nb_to_count = (double)$age['nb'] - $nb_no_add;
          $nb_no_add=0;
        
          if($age['supplement_mt']!=0){
            $supplement+=$nb_to_count * (double)$age['supplement_mt'];
          }
          else{
            $supplement+=$nb_to_count * (double)$age['supplement']* (double)$age['supplement_taux'];
          }
        }*/
        
      }
      
     	
    }
      
      if($jour){
        return $supplement;
      }
      else{
    //  print "nbJ : ".$this->get_nbJour()."<br />";
        return $supplement * $this->get_nbJour();
      }
  }
  function _get_good_ts(&$age){
    
    if((double)$age['ts_mt']>0) return (double)$age['ts_mt']; 
    else return (double)$age['ts']* (double)$age['ts_taux'];
  
  }
  function get_taxe_sejour($jour=false, $age_min=0, $age_max=9999){
  // retourne la taxe de s?jour selon l'age annonc? et le choix jour ou total
  
    $ts = 0;
      
    $nb=count($this->TAge);
    for ($i=0; $i<$nb; $i++) {
      $age = & $this->TAge[$i];
      
      if((int)$age['min']>=$age_min 
      && (int)$age['max']<=$age_max ){
      /*
        print "$ts == ".$age['min'].">=$age_min && ".$age['max']
        ."<=$age_max (".$age['nb']." * ".$age['ts']." * "
        .$age['ts_taux'].")<br />";
        */
        $ts+=(double)$age['nb'] * $this->_get_good_ts($age); 
        
      
      }
      
     	
    }
    
    if($jour){
      return $ts;
    }
    else{
  //  print "nbJ : ".$this->get_nbJour()."<br />";
      return $ts * $this->get_nbJour();
    }
  
  }
  function get_taxe_sejour_unit($age_min=0, $age_max=9999){
  // retourne la taxe de s?jour selon l'age annonc? 
  // afin de l'ajout? ? une facture
  /*
  age :: min = age d?but, max = age fin
  nb = Nombre de personne de la tranche
  _get_good_ts donne le bon montant de la TS
  */
    $ts = 0;
      
    $cpt = 0;  
    $nb=count($this->TAge);
    for ($i=0; $i<$nb; $i++) {
      $age = & $this->TAge[$i];
      
      $test_age_min = (int)$age['min'];
      $test_age_max = ((int)$age['max']==0)?9999:(int)$age['max'];
      
      if($test_age_min>=$age_min 
      && $test_age_max<=$age_max ){
      
       
        
        $ts+=/*(double)$age['ts']* */ $this->_get_good_ts($age);
        
      /*   print "$ts == ".$test_age_min.">=$age_min && ".$test_age_max
        ."<=$age_max (".$this->_get_good_ts($age).")<br />";
       
        */
        $cpt++;
      }
    }
    
    if($cpt==0) return 0;
    else return $ts/$cpt; // sinon sur multiplication
  }
  
  function get_nb_personne_by_date($time_deb, $time_fin){
  // nombre de personne sur dates
  
        $nbp = $this->get_nb_personne(true);
        
        $nb_jour = (($time_fin-$time_deb)/86400)+1;
       // print "$time_fin-$time_deb $nbp * $nb_jour<br />";
        return $nbp * $nb_jour;
        
  }
  
  function get_nb_personne($jour=false, $age_min=0, $age_max=9999){
  // Nombre de personne selon l'age x jour ou non
  
    $nbp = 0;
      
    $nb=count($this->TAge);
    for ($i=0; $i<$nb; $i++) {
      $age = & $this->TAge[$i];
      
      $test_age_min = (int)$age['min'];
      $test_age_max = ((int)$age['max']==0)?9999:(int)$age['max'];
      
      if($test_age_min>=$age_min 
      && $test_age_max<=$age_max ){
      /*
        print "$ts == ".$age['min'].">=$age_min && ".$age['max']
        ."<=$age_max (".$age['nb']." * ".$age['ts']." * "
        .$age['ts_taux'].")<br />";
        */
        $nbp+=(double)$age['nb'];
      
      }
      
     	
    }
    
    if($jour){
      return $nbp;
    }
    else{
  //  print "nbJ : ".$this->get_nbJour()."<br />";
      return $nbp * $this->get_nbJour();
    }
  
  }
  
  
  function get_all_lignes_for_fact(){
  /*
    retourne un tableau contenant tous les ?l?ments pour la facturation
    type / Libell? / qt? / prix_u / tva 
  */
  
    $Tab=array();
  
    /*
    principal selon prix négocie ou prix obtenu depuis la chambre
    */
	
	$nb_in_tprix=count($this->TPrix); 
	if($this->is_prix_negoce==1 || $nb_in_tprix==0){

	    $ligne=array();
	    $ligne['id'] = $this->id;
	    $ligne['type']="RESERVATION";
	    $ligne['libelle'] = $this->libelle;
	    $ligne['qte'] = $this->nb_jour;
	    $ligne['prix_u'] = $this->prix;
	    $ligne['tva'] = 5.5;
	    $Tab[] = $ligne;   
        }
	else{
	// on ne négocie pas, il faut donc ajouter une par une les différentes période tarifaire
	//this->libelle=$this->nom_client." du ".$this->get_dtdeb()." au ".$this->get_dtfin(true)

	    
	    for($i=0;$i<$nb_in_tprix;$i++){
		   $row = & $this->TPrix[$i];
	
		    $ligne=array();
		    $ligne['id'] = $this->id;
		    $ligne['type']="RESERVATION";

		    $dt_deb = date('d/m/Y', $row['time_deb']);
		   // $dt_fin = ($i==$nb-1)?date('d/m/Y', $row['time_fin']+86400):date('d/m/Y', $row['time_fin']);
		    $dt_fin = date('d/m/Y', $row['time_fin']+86400);
		    
		    $libelle='';
		    if($this->chambre->id!=0) {
          $libelle.='('.$this->chambre->num.') ';
        }
		    $libelle .= $this->nom_client." du ".$dt_deb." au ".$dt_fin;
	
		    $ligne['libelle'] = $libelle;
			
		    
		    $ligne['qte'] = $row['nb_jour'];
		    $ligne['prix_u'] = $row['prix'];
		    $ligne['tva'] = 5.5;
		    $Tab[] = $ligne;   
	    }	

	}

    
    /*
    Frais de réservation
    */
    if($this->frais_resa>0){
      $ligne=array();
      $ligne['type']="FRAIS_RESA";
      $ligne['libelle'] = "Frais de réservation";
      $ligne['qte'] = 1;
      $ligne['prix_u'] = $this->frais_resa;
      $ligne['tva'] = 5.5;
      $Tab[] = $ligne;   
    }
    /*
    suppl?ments par personnes (en fonction age & nb personnes)  
    */
    //mis de c?t?, cela ne fonctionne pas
    // impossible de d?terminer, qui des personnes n'est pas supp
    // probl?me ? re analyser ! :o(
    
    //bon sang ca marche ou pas ? // AA 03/2009
    $var_age_supp = explode("|", $this->age_pour_supplement);
    foreach ($var_age_supp as $ages) {
    	list($supp_age1, $supp_age2)=explode("-", $ages); 
    	$ligne=array();
      $ligne['type']="PERSONNE_SUPPL";
      
      if($supp_age2=="")$ligne['libelle'] = "Supplément personne de plus de $supp_age1 ans";
      else if($supp_age1=="")$ligne['libelle'] = "Supplément personne de moins de $supp_age2 ans";
      else $ligne['libelle'] = "Supp. personne supplémentaire entre $supp_age1 ans et $supp_age2 ans";
      
      if($supp_age1=="")$supp_age1=0;
      if($supp_age2=="")$supp_age2=9999;
      
      $supplement = $this->get_supplement_unit($nb_personne, $supp_age1, $supp_age2);
      
      $ligne['qte'] = $nb_personne;
      if($nb_personne>0) $ligne['prix_u'] = $supplement/$nb_personne;
      $ligne['tva'] = 5.5;
      
      if($nb_personne>0 && $ligne['prix_u']>0) $Tab[] = $ligne;   
    
    }
   
   /* $ligne=array();
    $ligne['type']="PERSONNE_SUPPL";
    $ligne['libelle'] = "Suppl?ment personne(s) suppl?mentaire(s)";
    $ligne['qte'] = $this->get_nb_personne(true);
    $ligne['prix_u'] = $this->mt_personne_suppl / $ligne['qte'];
    $ligne['tva'] = 19.6;
    $Tab[] = $ligne;   
*/
   
    /*
    animaux
    */
    if($this->nb_animaux>0){
      $ligne=array();
      $ligne['type']="ANIMAUX";
      $ligne['libelle'] = "Supplément animaux";
      $ligne['qte'] = $this->nb_jour * $this->nb_animaux;
      $ligne['prix_u'] = $this->mt_animaux / ($this->nb_animaux*$this->nb_jour);
      $ligne['tva'] = 5.5;
      $Tab[] = $ligne;   
      
    }
    
    /*
    produits
    */
    $nb=count($this->TLienProduit);

		for ($i = 0; $i < $nb; $i++) {
				$l = & $this->TLienProduit[$i];
        
        if(!$l->to_delete){
          $produit = &$l->produit;
          $ligne=array();
          $ligne['id']=$produit->id;
          $ligne['type']="PRODUIT";
          $ligne['libelle'] = $produit->libelle;
          $ligne['qte'] = $l->quantite;
          $ligne['prix_u'] = $l->prix;
          $ligne['tva'] = $l->tva;
          $Tab[] = $ligne;
        }

				   
		}
    
    /*
    taxe de s?jour (en fonction age & nb personnes)  
    */
    list($age1, $age2)= explode("|", $this->age_pour_taxe_sejour);
    $nbp1 = $this->get_nb_personne(false, 0, $age1);
		$mts1 = $this->get_taxe_sejour_unit(0, $age1);
		if ($nbp1 > 0 && $mts1>0){
        $ligne=array();
        $ligne['type']="TAXE_SEJOUR";
        $ligne['libelle'] = "Taxe de séjour (moins de $age1 ans)";
        $ligne['qte'] = $nbp1;
        $ligne['prix_u'] = $mts1;
        $ligne['tva'] = 0;
        $Tab[] = $ligne;   
    }
    
    $nbp2 = $this->get_nb_personne(false, $age1, $age2);
		$mts2 = $this->get_taxe_sejour_unit($age1, $age2);
		if ($nbp2 > 0 && $mts2>0) {
        $ligne=array();
        $ligne['type']="TAXE_SEJOUR";
        $ligne['libelle'] = "Taxe de séjour (entre $age1 et $age2 ans)";
        $ligne['qte'] = $nbp2;
        $ligne['prix_u'] = $mts2;
        $ligne['tva'] = 0;
        $Tab[] = $ligne;  
    }

		$nbp3 = $this->get_nb_personne(false, $age2);
		$mts3 = $this->get_taxe_sejour_unit($age2);
		if ($nbp3 > 0 && $mts3>0){
        $ligne=array();
        $ligne['type']="TAXE_SEJOUR";
        $ligne['libelle'] = "Taxe de séjour (plus de $age2 ans)";
        $ligne['qte'] = $nbp3;
        $ligne['prix_u'] = $mts3;
        $ligne['tva'] = 0;
        $Tab[] = $ligne;  
    }

    
    
  
    return $Tab;
  }
  
  function _set_nosupp(){
  /*
    Permet de d?finir quelles personnes (parmis les plus ch?res)
    ne seront pas incluses dans le calcul des suppl?ments
  */
    $TAge = & $this ->TAge;
  
    $nb=count($TAge);
    
    $to_limit = $this->nb_limite_personne; // nombre de personne non sup
    
    //print "limite : $nb_limite_personne<br />";
    for ($i=$nb-1; $i>=0; $i--) {
    // on prends les + vieux pour d?compter
    
      if($to_limit==0)break; // on se parcours que ce qui est neccessaire
    
      $age = & $TAge[$i];
      
      $nb_personne = (double)$age['nb']; // nombre de persoone
  
      $nb_no_supp = $nb_personne;
      if($nb_no_supp>$to_limit)$nb_no_supp=$to_limit; // nb pers non sup?
      
      $age['nb_no_supp'] = $nb_no_supp;
      $to_limit-=$nb_no_supp;
  
    }
  
    return true;
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
			$query['TPrix']=serialize($this->TPrix);
			$query['montant']=$this->montant;
			
			$query['is_prix_negoce'] = $this->is_prix_negoce;
			
			$query['nb_personne_age1']=$this->nb_personne_age1;
			$query['nb_personne_age2']=$this->nb_personne_age2;
			$query['nb_personne_age3']=$this->nb_personne_age3;
			$query['nb_animaux']=$this->nb_animaux;
			
			$query['frais_resa']=$this->frais_resa;
			
			$this->_set_nosupp();
			$query['TAge']=serialize($this->TAge);
			/*print 	$query['TAge'];*/
			$query['mt_personne_suppl']=$this->mt_personne_suppl;
			$query['mt_animaux']=$this->mt_animaux;
			$query['mt_taxe_sejour']=$this->mt_taxe_sejour;


      $query['auto_import_produit'] = $this->auto_import_produit;
        
        
      $query['nb_limite_personne'] = $this->nb_limite_personne;
      $query['age_pour_taxe_sejour'] = $this->age_pour_taxe_sejour;
      $query['age_pour_supplement'] = $this->age_pour_supplement;

			$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
			$query['dt_maj']=date("Y-m-d H:i:s");

			$key[0]='id';
//print "auto_import_produit ".$this->auto_import_produit."<br />";
      if($this->auto_import_produit==0/* || true*/) {
            $this->get_auto_produit($db);
            $this->auto_import_produit=1;
            $query['auto_import_produit'] = $this->auto_import_produit;
      }

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

  function get_dtfin($reel = false){
	 
	  if($reel) return date("d/m/Y",$this->dt_fin+86400);
	  
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
		 * Ajoute une jour ? la r?servation
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
		 * el?ve une jour ? la r?servation
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
		 * Ajoute une jour ? la r?servation
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
		 * el?ve une jour ? la r?servation
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
	 * Fonction qui v?rifie si la r?servation n'est pas en conflit
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
	 * Fonction qui calcule le prix de la r?servation
	 * ? partir des dates et du prix de la chambre
	 */
	function calcule_prix () {
		//$day = $this->dt_fin - $this->dt_deb;
		//$this->nb_jour = ($day / 86400)+1;
		$this->nb_jour = $this->get_nbJour();
		
		$this->montant = $this->prix * $this->nb_jour;
//		echo "DT DEB : ".$this->dt_deb;
//		echo "<br>DT FIN : ".$this->dt_fin;
//		echo "<br>DIFF: ".$day;
//		echo "<br>NB: ".$nb_jour;

	}
	
	
	function calcule_prix_annexe () {

		/*
		 * @todo load_categorie doit r?cup?rer les donn?es de la cat?gorie
		 * et non pas les libell? de toutes les cat?gories
		 * Classe requete ? revoir (utilisation de $id_hotel...)
		 */
		$this->mt_animaux = $this->nb_animaux * $this->chambre->categorie->montant_animaux * $this->get_nbJour();
		$this->mt_personne_suppl = $this->get_supplement();
		/*
		$nb_limite_personne = $this->chambre->categorie->nb_limite_personne;
		$nb_personne = $this->nb_personne_age1 + $this->nb_personne_age2 + $this->nb_personne_age3;
		
		if ($nb_personne >= $nb_limite_personne) {
			$this->mt_personne_suppl = ($nb_personne - $nb_limite_personne + 1) * $this->chambre->categorie->montant_personne;
		}*/
		/*
		$mt_taxe = $_SESSION[SESS_HOTEL]->get_parameter("taxe_sejour_mt");
		$taux1 = $_SESSION[SESS_HOTEL]->get_parameter("taxe_sejour_taux1") / 100;
		$taux2 = $_SESSION[SESS_HOTEL]->get_parameter("taxe_sejour_taux2") / 100;*/
  /*
		$this->mt_taxe_sejour = $this->nb_personne_age1 * $mt_taxe * $taux1;
		$this->mt_taxe_sejour += $this->nb_personne_age2 * $mt_taxe * $taux2;
		$this->mt_taxe_sejour += $this->nb_personne_age3 * $mt_taxe;*/
		
		$this->mt_taxe_sejour = $this->get_taxe_sejour();
	}
}

?>
