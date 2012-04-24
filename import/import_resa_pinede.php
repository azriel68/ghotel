<?
  
  require("../includes/inc.php");  
    
  $TResa = array();

  $f1 = fopen("reserv2008.txt", "r");
  while(!feof($f1)){
    
    $ligne = trim( fgets($f1) );
    
    if($ligne!=""){
    
      $var = explode("&", $ligne);
      $n_resa = (int)$var[0];
      $n_client = (int)$var[1];
      
      if($n_client>0 && $n_resa>0) {
        
        $TResa[$n_resa]['reserv2008'] = $var;
        $TResa[$n_resa]['ligne1']=$ligne;
      }
      
    }
    
    
  
  }
  fclose($f1);

  $f1 = fopen("boekin2008.txt", "r");
  while(!feof($f1)){
    
    $ligne = trim( fgets($f1) );
    
    if($ligne!=""){
    
      $var = explode("&", $ligne);
      $n_resa = (int)$var[1];
      $n_emplacement = (int)$var[2];
      
      if($n_emplacement>0 && $n_resa>0 && isset($TResa[$n_resa]))  {
        
        $TResa[$n_resa]['boekin2008'] = $var;
        $TResa[$n_resa]['ligne2']=$ligne;
      }
      else{
      //  print "erreur : $n_resa , $n_emplacement<br>";
      }
      
    }
    
    
  
  }
  fclose($f1);
/*
  print "<pre>";
  print_r($TResa);
  print "</pre>";
  */
  $db=new Tdb;
  $db->db->debug=true;
  $_SESSION[SESS_USER]->id_groupe = 20;
  $_SESSION[SESS_HOTEL] = new THotel;
  $_SESSION[SESS_HOTEL]->load($db, 2);    
  foreach ($TResa as $n_resa=>$resa) {
  	
  	if(isset($resa['ligne1']) ){ //&& isset($resa['ligne2'])
    
      $TResaF1 = $resa['reserv2008'];
      $TResaF2 = $resa['boekin2008'];
    
      $ligne1 = $resa['ligne1'];
      $ligne2 = $resa['ligne2'];
      
      $r= new TReservation;
      $r->data_import =$ligne1."\r\n".$ligne2;
        
      $n_chambre = (int)$TResaF2[2];  
       
      if($n_chambre>0){
        $id_hotel=2;  
        // donne la chambre ainsi que le n hotel correspondant
        $id_chambre =  _get_id_chambre($db, $n_chambre, $TResaF2, $id_hotel);
        if($id_chambre>0){
            if($_SESSION[SESS_HOTEL]->id!=$id_hotel){
              $_SESSION[SESS_HOTEL] = new THotel;
              $_SESSION[SESS_HOTEL]->load($db, $id_hotel);    
            }
            
            // donne le client, le copie dans le bon hotel si besoin est
            $id_client =  _get_id_client($db, $TResaF1[1],$id_hotel);
            if($id_client>0){
              $r->id_client = $id_client;
              $r->id_hotel = $id_hotel;
              $r->id_chambre = $id_chambre;
              
              $r->set_dtdeb($TResaF1[3]);  
              $r->set_dtfin($TResaF1[5]);
              $r->dt_fin -=86400; // le jour de départ initialement dans le fichier
              
              $r->load_chambre($db);
              $r->get_prix();
              
              $r->calcule_prix();
        			$r->calcule_prix_annexe();
               
              $r->get_ages();
              
                print_r($r);  
                  
              //$r->save($db);
            } 
            
        }
      } 
        
      
      
      
    }
    else{
      print "Réservation $n_resa invalide<br />";
    }
  	
  	
  }
  
    
  $db->close();
  
function _get_id_client($db, $numero_client,$id_hotel){

  $db->Execute("SELECT id FROM hot_client WHERE data_import LIKE '$numero_client&%' ORDER BY id_hotel DESC");
  if($db->Get_line()){
    $id = (int)$db->Get_field('id');
    
    $client=new TClient;
    $client->load($db, $id);
    
    if($client->id_hotel!=$id_hotel){
      
      $client->id=0;
      $client->id_hotel = $id_hotel;
    
      $client->save($db);
      
    }
    
    return $client->id;
    
  }
  else{
  
    return -1;
  
  }


}  
  
function _get_id_chambre(&$db, $numero, &$TResaF2, &$id_hotel){
  $db->Execute("SELECT id, id_hotel FROM hot_chambre WHERE num='$numero' AND id_hotel IN(2,100,101)");
  if($db->Get_line()){
    $id_hotel = (int)$db->Get_field('id_hotel');
    return $db->Get_field('id'); 
  }
  else{
  
    return -1;
  
  }


}  
  
  
  
  
?>