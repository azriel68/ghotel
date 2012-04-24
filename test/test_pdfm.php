<?
  require("../fpdf153/fpdf.php");
  require("../includes/class.pdfmaker.php");
  
  $Tableau['client']=array(
    "nom"=>"Alexis ALGOUD"
    ,"adresse"=>"28 rue Henri de Regnier"
    ,"ville"=>"VERSAILLES"
    ,"cp"=>"78000"
  );
  
  $Tableau['hotel']=array(
    "nom"=>"Hotel bla bla"
    ,"adresse"=>"30 r HR"
    ,"ville"=>"VERSAILLES"
    ,"cp"=>"78000"
    ,"signature"=>"Alexis ALGOUD"
    ,"commercial"=>"Hôtel plein air - camping caravaning ***NN"
    ,"tel"=>"(33) 475 22 17 77"
    ,"tel"=>"(33) 475 22 17 73"
    
  );
  
  $Tableau['test']=array(
    0=>array(
      0=>"Libellé||100||C||1||1||0||230"
      ,1=>"Qté||20||C||1||1||0||230"
      ,2=>"Total||50||C||1||1||0||230"
    )
  
    ,1=>array(
      "lib"=>"Mon blabla1"
      ,"qte"=>3
      ,"total"=>"17||50||R||1||1||0||230"
    )
    ,2=>array(
      "lib"=>"Mon blabla2"
      ,"qte"=>4
      ,"total"=>16
    )
    ,3=>array(
      "lib"=>"Mon blabla3"
      ,"qte"=>34
      ,"total"=>2789
    )
  
  );
  
  $p = new TPDFMaker;
  $p->model="lettre_reservation.txt";
  
  $p->parse_file($Tableau);
  
  
  //header('Content-Type: application/pdf');
  //header('Content-Disposition: inline; filename=fichier.pdf');

  $pdf = new TPdf;
  $pdf->AliasNbPages();
    
  $nb=10;
  for($i=0;$i<$nb;$i++){
    
    $trans["titre_categorie"] = "titre_categorie $i !!!";
  
    $p->write($pdf, $trans);  
  }
  

  $pdf->Output("test.pdf","I");

?>
