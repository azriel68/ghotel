<?

require("../includes/inc.php");

entete("S�lectionner la chambre",'popup');

$t=new TTbl;
$listname='dblist1';
$lst=new TListView($listname);

$formname = "formrecherche";

echo "<br />";
echo "<h1>Liste des chambres</h1>";
 
?>
<script language="javascript">
function set_chambre(id_chambre, lib_chambre, prix){

  param = 'id_chambre='+id_chambre+';lib_chambre='+lib_chambre+';prix='+prix+';';
  <?
  if(isset($_REQUEST['dt_deb']) && $_REQUEST['dt_deb']!=""){
    ?>
    param+='dt_deb=<?=$_REQUEST['dt_deb']?>;';
    <?
    if($_REQUEST['dt_fin']==""){
    ?>
    param+='dt_fin=<?=$_REQUEST['dt_deb']?>;';
    <?
    }
    else{
    ?>
    param+='dt_fin=<?=$_REQUEST['dt_fin']?>;';
    <?
    }
  
  
  }
  
     
  
  ?>
  _linkForm_data('<?=$_REQUEST['FORM']?>',param);  
  window.opener.set_prix();
  window.close();
}

</script>
<?
  
  $db=new Tdb;
  /*
  if(isset($_REQUEST['action')){
    switch ($_REQUEST['action']) {
    case 'SET_SET_CHAMBRE': 
    
      $c = new TChambre;
      $c->load($db, $_REQUEST['id_chambre']);
      $prix = $c->get_prix();
    
      ?>
      <script language="javascript">
        LinkForm('<?=$_REQUEST['FORM']?>'
        ,'<?=$_REQUEST['p0']?>=<?=$_REQUEST['id_chambre']?>'
        +';<?=$_REQUEST['p1']?>=<?=$_REQUEST['lib_chambre']?>'
        +';<?=$_REQUEST['p2']?>=<?=$prix?>;');
      </script>
      <?
    
      break;
    default:
    	
    	break;
    }
  
  }
  
*/
  $form =new TForm(null, $formname,"GET");

  $collect_resa_for_date=false;
  $r=new TRequete;
  $TCat = $r->liste_toute_categorie($db, get_sess_hotel_id());

  echo $form->combo('','categorie', $TCat, 'auto' );
  echo $form->texte('', 'dt_deb', 'auto', 12, 10);
  echo $form->texte('', 'dt_fin', 'auto', 12, 10);
  echo $form->hidden('FORM','auto');
  echo $form->hidden('p0','auto');
  echo $form->hidden('p1','auto');
  echo $form->hidden('p2','auto');
  /*
  echo $form->hidden('action','');
  echo $form->hidden('id_chambre','auto');
  echo $form->hidden('lib_chambre','auto');
  echo $form->hidden('prix','auto');
  
  */

  echo $t->link("Trouver", "javascript:document.forms['formrecherche'].submit()", "button");

  echo $form->end_form();

  

  $where="";$jointure="";
  if(isset($_REQUEST['categorie'])){
    $where.="AND a.id_categorie='".$_REQUEST['categorie']."' ";
    
    /*
    	A finir
    */
    if($_REQUEST['dt_deb']!=""){
      $r=new TRequete;
      
      if($_REQUEST['dt_fin']=="")$_REQUEST['dt_fin'] = $_REQUEST['dt_deb'];
      
      list($jj, $mm, $aaaa) = explode("/",$_REQUEST['dt_deb']);
      list($jj2, $mm2, $aaaa2) = explode("/",$_REQUEST['dt_fin']);
      
      $time_deb = mktime(0,0,0,$mm, $jj, $aaaa);
      $time_fin = mktime(0,0,0,$mm2, $jj2, $aaaa2);
      
      $TChambre = $r->liste_toute_chambre_sans_reservation_par_date($db,$time_deb,$time_fin);
    
      $collect_resa_for_date=true;
      
      if (count($TChambre)>0)
      	$where.=" AND a.id IN(".implode(",", $TChambre).") ";
      else
      	$where.=" AND a.id IN('') ";
    }
    else{
     /* print $_REQUEST['dt_deb']." - ".$_REQUEST['dt_fin'];*/
    }
     /* $jointure = "LEFT OUTER JOIN hot_reservation c ON (a.id=c.id_chambre)";*/
    
  }
  

//requ�te
$sql = "SELECT a.id as 'ID', LPAD(a.num,20,' ') as 'Num�ro', b.libelle as 'Cat�gorie', a.nb_lit as 'Nombre de lits'
, a.prestation as 'Prestation', a.orientation as 'Orientation', a.situation as 'Situation'
, a.prix as 'Prix', a.dt_cre as 'Cr�ation'
FROM ((hot_chambre a LEFT JOIN hot_categorie b ON (a.id_hotel=b.id_hotel AND a.id_categorie=b.id))
         $jointure)
WHERE a.id_hotel=".get_sess_hotel_id()." ".$where;


$ordercolumn = isset($_REQUEST["orderColumn"])?$_REQUEST["orderColumn"]: 'Num�ro';
$ordertype = isset($_REQUEST["orderTyp"])?$_REQUEST["orderTyp"]:"A";
$pagenumber = isset($_REQUEST["pageNumber"])?$_REQUEST["pageNumber"]:0;

$lst->Set_Query($sql);

$lst->Load_query($ordercolumn,$ordertype); // on charge la requ�te

$lst->Set_pagenumber($pagenumber);
$lst->set_key('ID',$_REQUEST['p0']);
$lst->set_key('Num�ro',$_REQUEST['p1']);
$lst->set_key('Prix',$_REQUEST['p2']);
$lst->Set_columnType('Cr�ation','DATE');
$lst->Set_columnType('Prix','MONEY');

$lst->Set_hiddenColumn('Cr�ation', true);
$lst->Set_hiddenColumn('ID', true);

$lst->Set_nbLinesPerPage(35);

if($collect_resa_for_date){
  _collect_resa_for_date($db, $lst,$time_deb, $time_fin);
}

if (!is_null($_REQUEST["FORM"]) && $_REQUEST["FORM"]>"")
    $lst->Set_onClickAction("LinkForm","'".$_REQUEST["FORM"]."'"); //on d�finie la fonction javascript appel�e lors du click sur une ligne de la liste

if (!is_null($_REQUEST["FORM"]) && $_REQUEST["FORM"]>"")
    $lst->Set_onClickAction("set_chambre","'".$_REQUEST["FORM"]."'"); //on d�finie la fonction javascript appel�e lors du click sur une ligne de la liste


echo $lst->Render();

pied_de_page('popup');

  $db->close();

function _collect_resa_for_date(&$db, &$lst,$time_deb, $time_fin){

 /* print_r($lst->lines);*/
  $lst->column['Avant']=new TColumn;
  $lst->column['Apr�s']=new TColumn;

  $lst->column['Avant']->type='none';
  $lst->column['Apr�s']->type='none';

  $nb=count($lst->lines);
  for ($i=0; $i<$nb; $i++) {
   	/*
    	Moche, pr�voir d'ins�rer dans la classe
    */
   	$ligne = & $lst->lines[$i];
   	
   	$sql = "SELECT dt_fin
		FROM hot_reservation
		WHERE id_chambre=".$ligne['ID']."
		AND dt_fin <= '".date("Y-m-d",$time_deb)."'
		ORDER BY dt_fin DESC
		LIMIT 1";
		$db->Execute($sql);//print $sql;
      
	  if($db->Get_recordcount()>0){
      $db->Get_line();
      $time_before_resa = strtotime($db->Get_field('dt_fin'));
      
      $nb_nuit_before = (ceil(($time_deb - $time_before_resa) / 86400)-1)."j";
    }
    else{
      $nb_nuit_before="---";
    }
    
   		
   	$sql = "SELECT dt_deb
		FROM hot_reservation
		WHERE id_chambre=".$ligne['ID']."
		AND dt_deb >= '".date("Y-m-d",$time_fin)."'
		ORDER BY dt_deb ASC
		LIMIT 1";
		$db->Execute($sql);
      
	  if($db->Get_recordcount()>0){
      $db->Get_line();
      $time_after_resa = strtotime($db->Get_field('dt_deb'));
      
      $nb_nuit_after = (ceil(($time_after_resa-$time_fin) / 86400)-1)."j";
    }
    else{
      $nb_nuit_after="---";
    }
    
    $ligne['Avant'] = $nb_nuit_before; 
    $ligne['Apr�s'] = $nb_nuit_after; 
    
   	
  }

	
		
}

?>
