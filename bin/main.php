<?
	require("../includes/inc.php");

  if(isset($_REQUEST['action'])){
		$action=$_REQUEST['action'];
	}
	else{
		$action='VIEW';
	}

  if($action=='VIEW' && is_logged(true)){
      header("location:hotel.php");
  }





	$db=new Tdb;
	
	
//$db->db->debug=true;
	switch($action){
	
	  case 'NEWACCOUNT':
	   $utilisateur = new TUtilisateur;
	   $utilisateur->login = $_REQUEST['email'];
	   $utilisateur->email = $_REQUEST['email']; 
	   
	   $chklogin = $utilisateur->check_login($db);
//	   $chkemail = $utilisateur->check_email($db);
	   
	   if($_REQUEST['email']!="" && $_REQUEST['group_name']!="" 
            && $chklogin){
      
      $groupe=new TGroupe;
      $groupe->nom = $_REQUEST['group_name'];
      $groupe->save($db);
      
      $utilisateur->id_groupe = $groupe->id;
      $utilisateur->nom = $groupe->nom;
      $utilisateur->make_password();
      
      $utilisateur->send_mail_account();
      
      $utilisateur->save($db);

      // Cr�ation automatique de l'h�tel
      $hotel = new THotel;
      $hotel->id_groupe = $groupe->id;
      $hotel->nom = $_REQUEST['hotel_name'];
      $hotel->adresse = $_REQUEST['hotel_adresse'];
      $hotel->cp = $_REQUEST['hotel_cp'];
      $hotel->ville = $_REQUEST['hotel_ville'];
      
      if ($_REQUEST['hotel_name'] != "") {
      	$hotel->save($db);
      	$msg = "Votre �tablissement a �t� automatiquement cr��, vous  pouvez commencer � cr�er vos chambres, vos clients et vos r�servations en vous connectant";
      } else {
      	$msg = "Vous n'avez pas renseign� le nom de votre �tablissement, vous devrez donc le cr�er manuellement avec le bouton \"Cr�er un h�tel\"";
      }
            
      entete("Cr�ation d'un nouveau compte termin�"); 
      //menu_off();        
      ?>
      <div class="information">
      Votre inscription a bien �t� prise en compte. Un email de confirmation avec vos
      informations d'identification va vous parvenir dans quelques minutes. Vous pourrez
      ensuite vous identifier grace au formulaire ci-dessous.<br><br><?=$msg ?>
      </div>
      <?     
      
      fiche();
     }
     else{
      $TErr=array();
      if($_REQUEST['email']=="")$TErr['email']='vide';
      if($_REQUEST['group_name']=="")$TErr['groupe']='vide';
      if(!$chklogin)$TErr['login']='erreur';
//      if(!$chkemail)$TErr['email']='erreur';
      entete("Cr�ation d'un nouveau compte");
      //menu_off();
      inscription($TErr);
     }
	  
	   
	   break;
	
	  case 'ERROR_ID':
	   entete("Echec session - R�identifiez-vous");
	     ?>
      <div class="information">
      Vous �tes rest� trop lontemps inactif, pour plus de s�curit� votre session a
      �t� automatiquement ferm�e. Merci de vous r�identifier.
      </div>
      <?   
      fiche();
      
	   break;
	
		case 'DECONNEXION':
			deconnexion();
		  	entete("Identifiez-vous");
			//menu_off();
			info("Vous �tes maintenant d�connect�");
			
			fiche();
			break;
		case 'LOGIN':
//$db->db->debug=true;
			$_SESSION[SESS_USER]=new TUtilisateur;
			$u=& $_SESSION[SESS_USER];

			$login = isset($_GET['login'])?$_GET['login']: $_POST['login'] ;
			$password = isset($_GET['password'])?$_GET['password']: $_POST['password'] ;

			if ($u->login($db,$login, $password)) {
			  $_SESSION[SESS_GRP]=new TGroupe;
			  $_SESSION[SESS_GRP]->load($db, $u->id_groupe);  
			   
				$u->save_connexion($db);
				$_SESSION[SESS_GRP]->save_connexion($db);

				$time30d = time()+86400*30;

				if($_REQUEST['login']!='demo'){
				        setcookie("infos","Ceci permet � GHotel de vous identifier automatiquement", $time30d);
			  				setcookie("autologin","on", $time30d);
			  				setcookie("login_auto",$_REQUEST['login'],$time30d);
			  				setcookie("password_auto",$_REQUEST['password'], $time30d);

				}
				
				

				header("location:hotel.php");
			}
			else{
			  deconnexion();
			
				entete("Identifiez-vous");
				//menu_off();
				erreur("Login ou mot de passe incorrect");

				
				fiche();
			}

			break;
		case 'FOR_LOGIN':
			entete("Identifiez-vous/Inscrivez-vous");
			fiche();
			inscription();
			break;
		case 'VIEW':
    
			if(!_autologin($db)) {

				entete("Identifiez-vous/Inscrivez-vous");
		
				//menu_off();
				fiche();
				inscription();
			}
			break;
		default:
			erreur("inconnu : ".$action);
	} // switch

	$db->close();
	pied_de_page();

function _autologin(&$db) {
//print_r($_COOKIE);
	if(isset($_COOKIE['autologin']) && $_COOKIE['autologin']=="on"){
		
		$_SESSION[SESS_USER]=new TUtilisateur;
		$u=& $_SESSION[SESS_USER];
		$u->login($db,$_COOKIE['login_auto'], $_COOKIE['password_auto']);
		
		$_SESSION[SESS_GRP]=new TGroupe;
		$_SESSION[SESS_GRP]->load($db, $u->id_groupe);  
			   
		$u->save_connexion($db);
		$_SESSION[SESS_GRP]->save_connexion($db);
	
		header("location:hotel.php");

		return true;
	}

	return false;
}
function inscription($TErr=array()){
  echo "<br /><br /><h1 class=\"titre\">Vous souhaitez devenir utilisateur de Gh�tel, inscrivez-vous</h1>";
  $formname="forminscription";
	$form=new TForm($_SERVER['PHP_SELF'], $formname);
	echo $form->hidden("action","NEWACCOUNT");
//	if(isset($TErr['login']) && $TErr['login']=='erreur'){
      ?>
      <!--
      <div class="information_erreur">
      Le login que vous avez sp�cifi� est soit trop court (minimum 3 caract�res), 
      soit d�j� utilis�. Veuillez le modifier en cons�quence. Merci.
      </div>
      -->
      <?  
//  }
  if(isset($TErr['email']) && $TErr['email']=='erreur'){
      ?>
      <div class="information_erreur">
      L'e-mail que vous avez sp�cifi� est soit invalide, 
      soit d�j� utilis�. Veuillez le modifier en cons�quence. Merci.
      </div>
      <?  
  }
	
	if(isset($TErr['email']) && $TErr['email']=='vide'){
      ?>
      <div class="information_erreur">
      Votre adresse mail est obligatoire pour finaliser votre inscription.
      </div>
      <?  
  }
	if(isset($TErr['groupe']) && $TErr['groupe']=='vide'){
      ?>
      <div class="information_erreur">
      Un nom de groupe est obligatoire pour finaliser votre inscription.
      </div>
      <?  
  }
	
	
?>
<script language="javascript">
function inscription(){
  //alert("L'inscription sera disponible d'ici quelques heures :)");
 	document.forms['<?=$formname?>'].elements['action'].value='NEWACCOUNT';
 	document.forms['<?=$formname?>'].submit();
}

</script>

<div class="information">
Pour cr�er un nouveau compte GH�tel, saisissez les informations demand�es ci-apr�s.
Veillez � donner une adresse mail valide. Un mail de confirmation contenant votre mot de passe
(que vous pourrez modifier � votre convenance par la suite) vous sera envoy� � la
fin de cette proc�dure.
</div>
<?	
	
  $t=new TTbl;
  
  $t->beg_tbl('formcadre',800,2,'','center');
  $t->beg_line("listheader");
  $t->Cell("Votre compte GH�tel",-1,'',2);
  $t->end_line();
  
//  $t->beg_line();
//  $t->Cell("Identifiant");
//  $t->Cell( $form->texte("","login",'auto',30,255,"title=\"Choisissez votre identifiant\""));
//  $t->end_line();
  
  $t->beg_line();
  $t->Cell("E-mail");
  $t->Cell( $form->texte("","email",'auto',60,255,"title=\"E-mail valide auquel votre mot de passe sera envoy�\""));
  $t->end_line();
  
  $t->beg_line();
  $t->Cell("Soci�t�/groupe/organisme<br>(� d�faut, votre nom de famille)");
  $t->Cell( $form->texte("","group_name",'auto',60,255,"title=\"Nom du groupe\""));
  $t->end_line();
  
  $t->beg_line("listheader");
  $t->Cell("Votre �tablissement",-1,'',2);
  $t->end_line();
  
  $t->beg_line();
  $t->Cell("Nom");
  $t->Cell( $form->texte("","hotel_name",'auto',30,150,"title=\"Nom de votre �tablissement\""));
  $t->end_line();
  
  $t->beg_line();
  $t->Cell("Adresse");
  $t->Cell( $form->texte("","hotel_adresse",'auto',50,150,"title=\"Adresse de votre �tablissement\""));
  $t->end_line();
  
  $t->beg_line();
  $t->Cell("Code postal");
  $t->Cell( $form->texte("","hotel_cp",'auto',10,10,"title=\"Code postal de votre �tablissement\""));
  $t->end_line();
  
  $t->beg_line();
  $t->Cell("Ville");
  $t->Cell( $form->texte("","hotel_ville",'auto',30,150,"title=\"Ville de votre �tablissement\""));
  $t->end_line();
  
  $t->end_tbl();
    
	echo "<p align=\"center\">";
	echo $t->link("M'inscrire","javascript:inscription()","button");
	echo "</p>";

	echo $form->btsubmithidden('','btvalidation');

}

function fiche(){

  echo "<h1 class=\"titre\">Vous avez un compte Gh�tel</h1>";

	$formname="formlogin";
	$form=new TForm($_SERVER['PHP_SELF'], $formname);
	echo $form->hidden("action","LOGIN");
?>
<script language="javascript">
function connexion(){
 	document.forms['<?=$formname?>'].elements['action'].value='LOGIN';
 	document.forms['<?=$formname?>'].submit();
}

</script>
<?

	$t=new TTbl;
	
	$t->beg_tbl('formcadre',-1,2,'','center');
	$t->beg_line("listheader");
	$t->Cell("Identifiez-vous",-1,'',2);
	$t->end_line();
	$t->beg_line();
	$t->Cell("Votre e-mail ",-1,'','','right');
	$t->Cell( $form->texte("","login",isset($_REQUEST['login'])?$_REQUEST['login']:"",30,255)
.(isset($_REQUEST['login'])?"":"<script language=\"javascript\">document.forms['$formname'].elements['login'].focus();</script>") );
	$t->end_line();
	$t->beg_line();
	$t->Cell("Votre mot de passe ");
	$t->Cell( $form->password("","password","",30,255)
.(isset($_REQUEST['login'])?"<script language=\"javascript\">document.forms['$formname'].elements['password'].focus();</script>":"") );
	$t->end_line();
	$t->end_tbl();

	echo "<p align=\"center\">";
	echo $t->link("Me connecter","javascript:connexion()","button");
	echo "</p>";

	echo $form->btsubmithidden('','btvalidation');

	echo $form->end_form();

}
?>
