<?
  require("../includes/inc.php");
  require("../includes/class.pdfmaker.php");
  
  $db=new Tdb;
  $m=new TModel();
  $m->load($db, $_REQUEST['id_model']);

  if($m->id<0 || $m->id_hotel==$_REQUEST['id_hotel']){
  
    if($m->id<0)$f1=file_get_contents(DIR_MODEL.$m->src);
    else $f1=file_get_contents(DIR_MODEL_USER.$m->src);
  
    header("Content-Type: application/octet-stream");
		header("Content-disposition: attachment; filename="._url_format($m->libelle).".txt");
		header("Content-Transfer-Encoding: binary");
   
  
    $f1 = strtr($f1, array("\r"=>""));
    $f1 = strtr($f1, array("\n"=>"\r\n"));
    
    print $f1;
    
  }
  
  $db->close();
    
?>
