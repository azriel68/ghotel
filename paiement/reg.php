<?
  $f1 = fopen("log_paiement.txt","a");
  
  fputs($f1, $_REQUEST['AUTH']
  ."\t".$_REQUEST['CODE']
  ."\t".$_REQUEST['TRANS']
  ."\t".$_REQUEST['AMOUNT']
  ."\t".$_REQUEST['COUNTRY']
  ."\t".$_REQUEST['DATAS']
  
  fclose($f1);

?>
