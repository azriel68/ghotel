<?php
	
	
	
	require("../includes/inc.php");
	require("../fpdf153/fpdf.php");

	is_logged();

	$sess_name=isset($_REQUEST['sess_name'])?$_REQUEST['sess_name']:gen_sess_name("facture");
	$f = $_SESSION[$sess_name];
	
	header('Content-Type: application/pdf');
	header('Content-Disposition: inline; filename=downloaded.pdf');

	$pdf = new facture_pdf;
	$pdf->createFacturePDF($f);
	
?>
