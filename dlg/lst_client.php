<?

require("../includes/inc.php");

entete("S�lectionner le client", 'popup');

$t=new TTbl;
$listname='dblist1';
$lst=new TListView($listname);

$formname="form_search";
?>

<script language="javascript">
function go_liste(){
 	document.forms['<?=$formname?>'].elements['action'].value='LIST';
 	document.forms['<?=$formname?>'].submit();
}
</script>

<?


$form=new TForm($_SERVER['PHP_SELF'],$formname);
echo $form->hidden("action", "LIST");
echo $form->hidden('FORM','auto');
echo $form->hidden('p0','auto');
echo $form->hidden('p1','auto');
echo $form->hidden('p2','auto');
  
echo "<br />";
echo "<h1>Liste des clients</h1>";

  $charIdx=isset($_REQUEST['charIndex'])?$_REQUEST['charIndex']:"all";
	$where="";
	switch($charIdx){
     	case "other":
     		$where.="AND idx='0'";
			break;
		case "all":
			null;
			break;
		default:
			$where.="AND idx='".$charIdx."'";
			break;
	} // switch
	
	

	$TIndex['table']="hot_client";
	$TIndex['condition']="id_hotel=".get_sess_hotel_id();
	$TIndex['char']=$charIdx;
//$_REQUEST['DEBUG']=1;
//requ�te
$sql = "SELECT id as 'ID',civ as 'Civilit�',CONCAT(CONCAT(nom,' '),prenom) as 'Nom'
		,CONCAT(CONCAT(civ,' '),CONCAT(CONCAT(prenom,' '),nom)) as 'CivNom'
		,adresse as 'Adresse'
		,dt_cre as 'Cr�ation'
		FROM hot_client WHERE id_hotel=".get_sess_hotel_id()." ".$where;

$search=isset($_REQUEST['search'])?$_REQUEST['search']:"";

if ($search!="") {
  $mot = strtr(trim($_REQUEST['search']),array(" "=>"%"));

	$where .= "AND (nom like '%".$mot."%' OR prenom LIKE '%$mot%' 
OR adresse LIKE '%$mot%' OR tel  LIKE '%$mot%' OR email  LIKE '%$mot%') ";	
}

$sql .= $where;

$ordercolumn = isset($_REQUEST["orderColumn"])?$_REQUEST["orderColumn"]: 'Cr�ation';
$ordertype = isset($_REQUEST["orderTyp"])?$_REQUEST["orderTyp"]:"D";
$pagenumber = isset($_REQUEST["pageNumber"])?$_REQUEST["pageNumber"]:0;

$lst->Set_Query($sql);

$lst->Load_query($ordercolumn,$ordertype,$TIndex); // on charge la requ�te
$lst->Set_pagenumber($pagenumber);
$lst->set_key('ID',$_REQUEST['p0']);
$lst->set_key('CivNom',$_REQUEST['p1']);
if (isset($_REQUEST['p2'])) {
	$lst->set_key('Adresse',$_REQUEST['p2']);	
}
$lst->Set_hiddenColumn('CivNom',true);
$lst->Set_hiddenColumn('Adresse',true);
$lst->Str_trans('Adresse',array("\r"=>"\\r","\n"=>"\\n"));
$lst->Str_trans('CivNom',array("Autre "=>""));
$lst->Set_columnType('Cr�ation','DATE');
$lst->Set_nbLinesPerPage(35);

if (!is_null($_REQUEST["FORM"]) && $_REQUEST["FORM"]>"")
    $lst->Set_onClickAction("LinkForm","'".$_REQUEST["FORM"]."'"); //on d�finie la fonction javascript appel�e lors du click sur une ligne de la liste

echo "<p align=\"center\">".$form->texte('','search',$search,40,255);
echo $t->link("Recherche","javascript:go_liste()","lien2")."</p>";
//echo "<p align=\"center\">".$t->link("Ajouter un client","?action=NEW","button")."";

echo $lst->Render();
echo $form->end_form();

pied_de_page('popup');

?>
