<?php
/**
 * Created on 10 avr. 07
 */

require("../includes/inc.php");

entete("S�lectionner la r�servation",'popup');
?>
<script language="javascript">
function selection_resa(id_reservation){
	window.opener.add_reservation(id_reservation);
}
</script>
<?
$t=new TTbl;
$listname='dblist1';
$lst=new TListView($listname);
$where = "";

echo "<h1>Liste des r�servation</h1>";

	$charIdx=isset($_REQUEST['charIndex'])?$_REQUEST['charIndex']:"CONFIRM";
	switch($charIdx){
		case "all":
			null;
			break;
		default:
			$where.=" AND a.etat = '".$charIdx."'";
			break;
	} // switch

//requ�te
	$sql = "SELECT a.id as 'ID', b.num as 'Chambre', CONCAT(CONCAT(c.civ,' '),c.nom) as 'Client'
	, a.etat as 'Etat',a.dt_deb as 'Date arriv�e', a.dt_fin as 'Date d�part'
	FROM hot_reservation a LEFT JOIN hot_chambre b ON a.id_chambre=b.id
			LEFT JOIN hot_client c ON a.id_client=c.id
	WHERE b.id_hotel=".get_sess_hotel_id()." AND  a.etat!='PAYEE'";

	$ordercolumn = isset($_REQUEST["orderColumn"])?$_REQUEST["orderColumn"]: 'Date arriv�e';
	$ordertype = isset($_REQUEST["orderTyp"])?$_REQUEST["orderTyp"]:"D";
	$pagenumber = isset($_REQUEST["pageNumber"])?$_REQUEST["pageNumber"]:0;

$sql .= $where;

$lst->Set_Query($sql);

$resa=new TReservation;
$TIndex['table']="hot_reservation";
$TIndex['idx']="etat";
$TIndex['char']=$charIdx;
$TIndex['TTrans'] = &$resa->TEtat;
$lst->Load_query($ordercolumn,$ordertype,$TIndex); // on charge la requ�te
$lst->Set_pagenumber($pagenumber);
$lst->set_key('ID',$_REQUEST['p0']);
$lst->Set_nbLinesPerPage(35);
$lst->Set_hiddenColumn('Prix', true);
$lst->Str_trans('Etat',$resa->TEtat);

$lst->Set_onClickAction("selection_resa",''); //on d�finie la fonction javascript appel�e lors du click sur une ligne de la liste


echo $lst->Render();

pied_de_page('popup');

?>
