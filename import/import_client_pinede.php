<?

  require("../includes/inc.php");
  
  $db=new Tdb;
  $f1= fopen("client_pinede2.txt", "r");
  while(!feof($f1)){
    set_time_limit(30);
  
    $ligne = trim(fgets($f1));
    $var = explode("&", $ligne);
  //id&Nom&Prénom&extra&rue&cp&ville&tél&fac&Laatste&Note&Taal&PNG&Bron&Doc&Gewijzigd&Passant&Email&Lbl&Kies&Png1&Png2&Png3&Png4&Png5&
  
    $c = new TClient;
  
    $c->id_hotel=2;
    
    $c->nom = $var[1];
    $c->prenom = $var[2];
    $c->adresse = $var[4]."\n".$var[5]." ".$var[6];
    $c->tel = $var[7];
    if($var[8]!="")$c->tel.=" - fax : ".$var[8];
    $c->observation = $var[10];
    $c->email = $var[17];
    
    $c->data_import = $ligne;
    /*print "<br />";
    print_r($c);exit();*/
    $c->save($db);
  }

  $db->close();

?>