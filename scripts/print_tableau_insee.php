<?
  require("../includes/inc.php");

  entete('tableau insee','popup');
?>
<link href="../styles/insee.css" rel="stylesheet" type="text/css">
<h1>Statistiques <?=$_SESSION[SESS_HOTEL]->nom_gestion."  - ".date('d/m/Y H:i') ?></h1>
<?  
  _parcours_insee($_REQUEST['date_deb'], $_REQUEST['date_fin']);
  echo '<br />';
  _parcours_arrive($_REQUEST['date_deb'], $_REQUEST['date_fin']);

  ?>
  <script language="javascript">
  window.setTimeout("self.print()", 1000);
  </script>
  <?

  pied_de_page('popup');

function  _parcours_arrive($date_deb, $date_fin){
  $time_debut = _time_by_datefr($date_deb);
  $time_fin = _time_by_datefr($date_fin);

  $time_debut_mois = _time_deb_mois($time_debut);
  $time_fin_mois = _time_fin_mois($time_fin);

  $db=new Tdb;
  $r=new TRequete;
  $t=new TTbl;
  
  $Tab=array();
  
  $TLangue = _get_langue();
  
  $time = $time_debut_mois;
  while($time<$time_fin_mois){
    
    $nb_seconde_mois_en_cours = (date('t', $time)-1) * 86400;
    $time_fin = $time+$nb_seconde_mois_en_cours;
   
    
    _parcours_arrive_mois ($db, $r, $t, $TLangue, $time, $time_fin);
    echo '<br />';
    
    $time+=$nb_seconde_mois_en_cours+86400;
  
  }
  
  $db->close(); 
}
  
function _parcours_arrive_mois (&$db, &$r, &$t,&$TLangue, $time_debut_mois, $time_fin_mois) {  

  $TResa = $r->liste_toute_reservation_par_date($db, $time_debut_mois,$time_fin_mois);
  
  $t->beg_tbl('insee');
  $t->beg_line('titre');
  $t->cell("Nombre d'arrivé/Occupation du ".date('d/m/Y',$time_debut_mois)." au ".date('d/m/Y',$time_fin_mois),-1,'', count($TLangue)*2+2 );
  $t->end_line();
  
  //entete tableau
  $t->beg_line('titre2'); $t->Cell('');$t->Cell('');
  foreach ($TLangue as $code=>$lib) {
  
    $t->Cell($lib, -1, 'cell',2);
  
  }
  $t->end_line();
  $t->beg_line('titre2'); $t->Cell('n°');$t->Cell('Lib.');
  foreach ($TLangue as $code=>$lib) {
  
  
    $t->Cell('arr.', -1, 'cell');
    $t->Cell('occ.', -1, 'cell');
  
  }
  $t->end_line();
  
  // lignes
  $Total=array();
  $nombre = 1;
  foreach ($TResa as $id_resa) {
  	$resa = new TReservation;
  	$resa->load($db, $id_resa);
  	$resa->load_client($db);
  	$t->beg_line('ligne'); 
  	$t->cell($nombre, -1, 'cell'); 
  	$t->Cell($resa->client->nationalite
    .' x '.$resa->get_nb_personne(true)
    .' '.$resa->libelle, -1, 'libelle');
  	foreach ($TLangue as $code=>$lib) {
   
      if($resa->client->nationalite==$code){
        $nb_arr = $resa->get_nb_personne(true);
        $nb_occ = $resa->get_nb_personne_by_date(
          ($time_debut_mois>$resa->dt_deb)?$time_debut_mois:$resa->dt_deb
          , ($time_fin_mois<$resa->dt_fin)?$time_fin_mois:$resa->dt_fin
        );
      
        if($resa->dt_deb>=$time_debut_mois && $resa->dt_deb<=$time_fin_mois){
          $t->Cell($nb_arr, -1, 'cell');
          (isset($Total[$code]['arr']))?$Total[$code]['arr']+=$nb_arr:$Total[$code]['arr']=$nb_arr;
        }
        else{
          $t->Cell('0', -1, 'cell');
          $Total[$code]['arr']=0;
        }
        $t->Cell($nb_occ, -1, 'cell');
        
        (isset($Total[$code]['occ']))?$Total[$code]['occ']+=$nb_occ:$Total[$code]['occ']=$nb_occ;
        
      }
      else{
         $t->Cell('', -1, 'cell',2);
       /* $Total[$code]['arr']=0;
         $Total[$code]['occ']=0;*/
      }
    }
  	
  	
  	$t->end_line();
  	
  	$nombre++;
  }
  
  //totaux
  $t->beg_line('titre2'); $t->Cell('');$t->Cell('');
  foreach ($TLangue as $code=>$lib) {
  
        if(isset($Total[$code]['arr'])){
          $t->Cell($Total[$code]['arr'], -1, 'cell');
        }
        else{
          $t->Cell(0, -1, 'cell');
        }
        
        if(isset($Total[$code]['occ'])){
          $t->Cell($Total[$code]['occ'], -1, 'cell');
        }
        else{
          $t->Cell(0, -1, 'cell');
        }
        
    }
  	
  $t->end_line();
  
  
  $t->end_tbl();
  
  

}

function  _parcours_insee($date_deb, $date_fin){
  $time_debut = _time_by_datefr($date_deb);
  $time_fin = _time_by_datefr($date_fin);

  $db=new Tdb;
  $r=new TRequete;
  $t=new TTbl;
  
  $Tab=array();
  
  $TResa = $r->liste_toute_reservation_par_date($db, $time_debut,$time_fin);
  
  $t->beg_tbl('insee');
  $t->beg_line('titre');
  $t->cell("Taux d'occupation du ".$date_deb." au ".$date_fin,-1,'', ($time_fin-$time_debut)/86400+3 );
  $t->end_line();
  
  //entete tableau
  $t->beg_line('titre2'); $t->Cell('n°');$t->Cell('Lib.');
  for($time = $time_debut; $time<=$time_fin; $time+=86400){
  
    $date_arrive = date('d', $time);
    $date_depart = date('d/m', $time+86400);
  
    $t->Cell($date_arrive." au ".$date_depart, -1, 'cell');
  
  }
  $t->end_line();
  
  // lignes
  $Total=array();
  $nombre = 1;
  foreach ($TResa as $id_resa) {
  	$resa = new TReservation;
  	$resa->load($db, $id_resa);
  	$resa->load_client($db);
  	$t->beg_line('ligne'); 
  	$t->cell($nombre, -1, 'cell'); 
  	$t->Cell($resa->client->nationalite
    .' x '.$resa->get_nb_personne(true)
    .' '.$resa->libelle, -1, 'libelle');
  	for($time = $time_debut; $time<=$time_fin; $time+=86400){
  
        if($time>=$resa->dt_deb && $time<=$resa->dt_fin){
          $t->Cell('1', -1, 'cell occupe');
          (isset($Total[$time]))?$Total[$time]+=1:$Total[$time]=1;
        }
        else{
          $t->Cell('', -1, 'cell');
        }
      
        
    }
  	
  	
  	$t->end_line();
  	
  	$nombre++;
  }
  
  //totaux
  $t->beg_line('titre2'); $t->Cell('');$t->Cell('');
  for($time = $time_debut; $time<=$time_fin; $time+=86400){
  
    
  
    $t->Cell($Total[$time], -1, 'cell');
  
  }
  $t->end_line();
  
  
  $t->end_tbl();
  
  $db->close();

}
   
  
  
function _time_by_datefr($datefr){

  list($j,$m,$a)=explode('/',$datefr);
  
  return strtotime("$a-$m-$j");

}
function _time_deb_mois($time_debut){

  return strtotime(date('Y-m-01',$time_debut));
}
function _time_fin_mois($time_fin){
  $nb_jour = date('t', $time_fin);
//print "$nb_jour<br />";
  return strtotime(date('Y-m-'.$nb_jour.' 23:59:59',$time_fin));

}
?>