<?php
require("../includes/inc.php");

if(isset($_REQUEST['action'])){
	$db=new Tdb;
	switch ($_REQUEST['action']) {
		case 'MOVE_RESA':

			$resa = new TReservation;
			$resa->load($db, $_REQUEST['p1']);

			if($resa->move($db, $_REQUEST['p2'], $_REQUEST['p3'])){
				$resa->save($db);
			}
			else{
				print "erreur";
			}



			break;

		case 'DELETE_RESA':
			$resa = new TReservation;
			$resa->load($db, $_REQUEST['p1']);
			$resa->delete($db);


		//	reload_parent();
			break;
		case 'MORE_RESA':
			$resa = new TReservation;
			$resa->load($db, $_REQUEST['p1']);
			if (!$resa->more_day($db)) {
				print "erreur";
			}
			else{
				$resa->save($db);
			}


			break;
		case 'LESS_RESA':
			$resa = new TReservation;
			$resa->load($db, $_REQUEST['p1']);
			if (!$resa->less_day($db)) {
				print "erreur";
			}
			else{
				$resa->save($db);
			}


			break;
		case 'MORE_RESA_LEFT':
			$resa = new TReservation;
			$resa->load($db, $_REQUEST['p1']);
			if (!$resa->more_day_left($db)) {
				print "erreur";
			}
			else{
				$resa->save($db);
			}


			break;
		case 'LESS_RESA_LEFT':
			$resa = new TReservation;
			$resa->load($db, $_REQUEST['p1']);
			if (!$resa->less_day_left($db)) {
				print "erreur";
			}
			else {
				$resa->save($db);
			}


			break;
		default:
			break;
	}

	$db->close();
}
//
//
//
//$form = new TForm('action.php','faction');
//echo $form->texte('','p1','',10,255);
//echo $form->texte('','p2','',10,255);
//echo $form->texte('','p3','',10,255);
//echo $form->texte('','p4','',10,255);
//echo $form->texte('','action','',10,255);
//echo $form->end_form();

function reload_parent () {
	?><script language="JavaScript" type="text/javascript">
  	parent.refresh();
  	</script><?

}

?>
