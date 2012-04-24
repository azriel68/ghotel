<?
  require("../includes/inc.php");
  
  /*
  /jour (de 1 � 31)
- Nombre de chambre occup�e
- Personne/Nationalit�
- Nombre de personne arriv�e

/mois
- Personne/Nationalit�
- Nombre de personne arriv�e
- Nombre de nuit�e / personne / nationalit� (nombre de nuit, ex : 220 nuit�e, 30 arriv�e)
  
  */

  _parcours_mois($_REQUEST['date_deb'], $_REQUEST['date_fin']);
  



function  _parcours_mois($date_deb, $date_fin){
  $time_debut = _time_by_datefr($date_deb);
  $time_fin = _time_by_datefr($date_fin);

  $db=new Tdb;
  $r=new TRequete;
  $t=new TTbl;
  
  $Tab=array();
  
  for($time = $time_debut; $time<=$time_fin; $time+=86400){
  
    $TResa = $r->liste_toute_reservation_par_date($db, $time,$time);
    $nb_resa = $r->nb_resultat;
    $nb_chambre = $nb_resa;
  
    $date = date('d/m/Y', $time);
    
    $row = & $Tab[$date];
    
    $row['nb_chambre']= $nb_chambre;
  
  }
  print '<pre>';
  print_r($Tab);
  print '</pre>';
  
  $db->close();

}
  
  

function _time_by_datefr($datefr){

  list($j,$m,$a)=explode('/',$datefr);
  
  return strtotime("$a-$m-$j");

}
?>