<?
/**
 * Inclusion et fonction globales
 * --> Notez que j'ai changer ma méthodologie depuis par l'ajonction d'un fichier fonctions
 **/


	require("class.db.php");
	require("class.form.php");
	require("class.tbl.php");
	require("class.listview.php");
	require("class.camp.php");
	require("class.client.php");
	require("class.plan.php");
	require("class.parsefile.php");
	require("define_var.php");

	session_name("cap_pub");
	session_start();





	$_movx = 14;
	$_movy = 185;

?>
<html>
<title><?=(isset($Page))?$Page:"sans titre"?></title>
</html>
<link href="../styles/style.css" rel="stylesheet" type="text/css">
<script language="javascript" src="../scripts/script.js"></script>
<script language="javascript" src="../scripts/XHRConnection.js"></script>
<body>
<?
function entete_batiactu(){
	?>
	<div align=center><a href="http://www.batiactu.com" target="_blank"><img src="http://www.batiactu.com/images/batiactu.com.gif" border=0></a></div>
	<hr style="border:1px solid #BBBBBB">
	<?
}
function pied_de_page(){
	?>
	<p align=center><hr class=hr2></p>
	<p align=center class=txtblack>Gestion de la publicit&eacute; - CAP 2005<br><?=date("d/m/Y H:i:s")?></p>
	</body></html>
	<?
}
function pied_de_page_batiactu(){
	?>
	<p align=center><hr class=hr2></p>
	<p align=center class=txtblack>Contact publicit&eacute; : <a href="mailto:publicite@capinfopro.com">publicite@capinfopro.com</a> - T&eacute;l : 01 53 68 40 20 - Fax : 01 48 56 67 51<br>&copy; Cap information professionnelle 2005-2006<br><?=date("d/m/Y H:i:s")?></p>
	</body></html>
	<?
}

function erreur($s){
global $_movy;
	?>

	<table class=erreur><tr><td>
	<img src="../images/s_error.png" ALIGN="absmiddle"><b>Erreur : <?=$s?></b>
	</td></tr></table>
	<?
	$_movy+=24;
}
function info($s){
global $_movy;
	?>

	<table class=info><tr><td>
	<img src="../images/s_info.png" ALIGN="absmiddle"><b>Information : <?=$s?></b>
	</td></tr></table>
	<?

	$_movy+=24;
}
function makepassword(){

	return strtoupper(substr(md5(time()),0,6));


}
function _httpcomplete($s){

	if(strcmp(substr($s,0,7),"http://") && strcmp(substr($s,0,8),"https://") ){
		return "http://".$s;
	}
	else {

		return $s;
	}



}
function _url_format($s){

	$trans = array(
		" "=>"-"
		,"/"=>"-"
		,"*"=>"-"
		,"?"=>"-"
		,"+"=>"-"
		,"("=>"-"
		,")"=>"-"
		,"é"=>"-"
		,"è"=>"-"
		,"&"=>"-"
		,"à"=>"-"
		,"ç"=>"-"
		,"ê"=>"-"
		,"ë"=>"-"
		,"â"=>"-"
		,"ä"=>"-"
		,"û"=>"-"
		,"ü"=>"-"
		,"î"=>"-"
		,"ï"=>"-"
		,"ù"=>"-"
		,"ô"=>"-"
		,"Ô"=>"o"
		,"ö"=>"-"
		,"<"=>"-"
		,">"=>"-"
		,":"=>"-"
		,";"=>"-"
		,"\\"=>"-"
		,"\""=>"-"
		,"|"=>"-"
	);


	$s = strtolower($s);
	$s = strtr($s,$trans);

	return $s;
}
function login_admin(){
	$t=new TTbl;
	$formname="formlogadmin";
	$form = new TForm("main.php",$formname);
	$t->beg_tbl('formcadre',-1,0,'','center');
	$t->beg_line('listheader');
	$t->Cell("Identification administrateur",-1,'',2);
	$t->end_line();
	$t->beg_line();
	$t->Cell("Login");
	$t->Cell($form->texte("", "login_admin", (isset($_POST['login_admin']))?$_POST['login_admin']:"", 30,255));
	$t->end_line();
	$t->beg_line();
	$t->Cell("Mot de passe");
	$t->Cell($form->password("", "passw_admin", "", 30, 255));
	$t->end_line();
	$t->beg_line();
	$t->Cell($form->btsubmit("Valider", "bt_log"),-1,'',2,'center');
	$t->end_line();
	$t->end_tbl();

	echo $form->end_form();
}
function is_logged(){

	if((isset($_REQUEST['login_admin']))
		&&(isset($_REQUEST['passw_admin']))){

		if((!strcmp($_REQUEST['login_admin'],"administrateur"))
		&&(!strcmp($_REQUEST['passw_admin'],"xiennodag"))){
			$_SESSION['logged']=true;

		}
		else {
			erreur("L'identification administrateur est un echec");

			login_admin();

			pied_de_page();
			exit();
		}

	}
	else if(!isset($_SESSION['logged'])){

		login_admin();

		pied_de_page();
		exit();
	}
	else {
	// user admin loggé sans soucis
		null;
	}


}
function session_existe(){
	if(isset($_SESSION['camp'])){

		return true;
	}
	else{
		return false;

	}

}
function raz_session(){
	if(isset($_SESSION['camp'])){
		unset($_SESSION['camp']);
	}
}
function _sort_by_rang($a,$b){

		$rang1 = $a['rang'];
		$rang2 = $b['rang'];

		if(($rang1>100)&&($rang2<100)){
			return -1;
		}
		elseif(($rang1<100)&&($rang2>100)){
			return 1;
		}
		else if($rang1<$rang2){
			return -1;
		}
		else if($rang1>$rang2){
			return 1;
		}
		else {
			return 0;
		}

	}
?>