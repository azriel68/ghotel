<?php
/**
 * Created on 10 avr. 07
 */

require("../includes/inc.php");

entete("Sélectionner le produit",'popup');
?>
<script language="javascript">
function selection_produit(id_produit){
	window.opener.add_produit(id_produit);
}
</script>
<?
$t=new TTbl;
$listname='dblist1';
$lst=new TListView($listname);

echo "<h1>Liste des produits</h1>";

//requête
$sql = "SELECT id as 'ID',libelle as 'Libellé', prix as 'Prix', tva as 'Tva' FROM hot_produit WHERE id_hotel=".get_sess_hotel_id();
$ordercolumn = isset($_REQUEST["orderColumn"])?$_REQUEST["orderColumn"]: 'Libellé';
$ordertype = isset($_REQUEST["orderTyp"])?$_REQUEST["orderTyp"]:"A";
$pagenumber = isset($_REQUEST["pageNumber"])?$_REQUEST["pageNumber"]:0;

$lst->Set_Query($sql);

$lst->Load_query($ordercolumn,$ordertype); // on charge la requête
$lst->Set_pagenumber($pagenumber);
$lst->set_key('ID',$_REQUEST['p0']);
$lst->Set_nbLinesPerPage(35);
$lst->Set_hiddenColumn('Prix', true);
$lst->Set_hiddenColumn('Tva', true);

$lst->Set_onClickAction("selection_produit",''); //on définie la fonction javascript appelée lors du click sur une ligne de la liste


echo $lst->Render();
echo "<p align=\"center\">".$t->link("Fermer","javascript:window.close()","button")."</p>";
pied_de_page('popup');

?>
