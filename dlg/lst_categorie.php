<?

require("../includes/inc.php");

entete("S�lectionner la cat�gorie",'popup');

$t=new TTbl;
$listname='dblist1';
$lst=new TListView($listname);


echo "<br /><h1>Liste des cat�gories</h1>";

//requ�te
$sql = "SELECT id as 'ID',libelle as 'Libell�', tarif_defaut as 'prix' FROM hot_categorie WHERE id_hotel=".get_sess_hotel_id();
$ordercolumn = isset($_REQUEST["orderColumn"])?$_REQUEST["orderColumn"]: 'Libell�';
$ordertype = isset($_REQUEST["orderTyp"])?$_REQUEST["orderTyp"]:"A";
$pagenumber = isset($_REQUEST["pageNumber"])?$_REQUEST["pageNumber"]:0;

$lst->Set_Query($sql);

$lst->Load_query($ordercolumn,$ordertype); // on charge la requ�te
$lst->Set_pagenumber($pagenumber);
$lst->set_key('ID',$_REQUEST['p0']);
$lst->set_key('Libell�',$_REQUEST['p1']);
$lst->set_key('prix',$_REQUEST['p2']);
$lst->Set_nbLinesPerPage(35);
$lst->Set_hiddenColumn('prix', true);

if (!is_null($_REQUEST["FORM"]) && $_REQUEST["FORM"]>"")
    $lst->Set_onClickAction("LinkForm","'".$_REQUEST["FORM"]."'"); //on d�finie la fonction javascript appel�e lors du click sur une ligne de la liste


echo $lst->Render();

pied_de_page('popup');

?>