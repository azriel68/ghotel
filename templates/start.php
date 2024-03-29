<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr-fr" lang="fr-fr">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <meta name="robots" content="index, follow" />
  <meta name="keywords" content="gestion, h�tel, hotel, camping, chambre, r�servation, client" />
  <meta name="description" content="Ghotel - Logiciel de gestion hotelli�re" />
  <meta name="generator" content="Joomla! 1.5 - Open Source Content Management" />
  <title><?=$_TPL_VARS['titre']?> - GH�tel - GESTION HOTELLIERE - www.gestion-hotel.com</title>
<!-- SCRIPT GHOTEL -->
  <link href="../styles/style.css" rel="stylesheet" type="text/css">
	<script language="javascript" src="../scripts/script.js"></script>
	
  <link rel="stylesheet" type="text/css" media="all" href="../scripts/jscalendar-1.0/calendar-win2k-cold-1.css" title="win2k-cold-1" />
  <script type="text/javascript" src="../scripts/jscalendar-1.0/calendar.js"></script>
  <script type="text/javascript" src="../scripts/jscalendar-1.0/lang/calendar-en.js"></script>
  <script type="text/javascript" src="../scripts/jscalendar-1.0/calendar-setup.js"></script>
<!-- SCRIPT GHOTEL -->

  <script type="text/javascript" src="../templates/media/system/js/mootools.js"></script>
  <script type="text/javascript" src="../templates/media/system/js/caption.js"></script>


<link rel="stylesheet" href="../templates/system/css/system.css" type="text/css" />
<link rel="stylesheet" href="../templates/system/css/general.css" type="text/css" />
<link rel="stylesheet" href="../templates/ja_purity/css/template.css" type="text/css" />

<script language="javascript" type="text/javascript" src="../templates/ja_purity/js/ja.script.js"></script>

<script language="javascript" type="text/javascript">
var rightCollapseDefault='show';
var excludeModules='38';
</script>
<script language="javascript" type="text/javascript" src="../templates/templates/ja_purity/js/ja.rightcol.js"></script>

<link rel="stylesheet" href="../templates/ja_purity/css/menu.css" type="text/css" />

<link rel="stylesheet" href="../templates/ja_purity/css/ja-sosdmenu.css" type="text/css" />
<script language="javascript" type="text/javascript" src="../templates/templates/ja_purity/js/ja.cssmenu.js"></script>

<link rel="stylesheet" href="../templates/ja_purity/styles/background/purewhite/style.css" type="text/css" />
<link rel="stylesheet" href="../templates/ja_purity/styles/elements/red/style.css" type="text/css" />

<!--[if gte IE 7.0]>
<style type="text/css">
.clearfix {display: inline-block;}
</style>
<![endif]-->

<style type="text/css">
#ja-header,#ja-mainnav,#ja-container,#ja-botsl,#ja-footer {width: 97%;margin: 0 auto;}
#ja-wrapper {min-width: 100%;}
</style>
</head>

<body id="bd" class="fs3 FF" >


<div id="ja-wrapper">

<!-- BEGIN: HEADER -->
<div id="ja-headerwrap">
	<div id="ja-header" class="clearfix" style="background: url(<?=$_TPL_VARS['bgimage']?>) no-repeat top right;">

			<h1 class="logo">
			<a href="/" title="GESTION HOTELLIERE - www.gestion-hotel.com"><span>GESTION HOTELLIERE - www.gestion-hotel.com</span></a>

		</h1>
	
	<div class="ja-headermask">&nbsp;
  	<div id="info_hotel_user">
    <?  
    if($_TPL_VARS['menu']=='online'){
      print isset($_TPL_VARS['welcome_msg'])?$_TPL_VARS['welcome_msg'].'<br />':"";
      print isset($_TPL_VARS['hotel_name'])?$_TPL_VARS['hotel_name']:"";
    
      ?><br /><?
      if($_TPL_VARS['have_rights']){
        print "Votre abonnement est valable jusqu'au <a href=\"".get_right_link_add()."\">".$_TPL_VARS['dt_end_rights']."</a>";
      }
      else{
        print "<span style=\"color:red;\">Votre abonnement n'est plus valable depuis le ".$_TPL_VARS['dt_end_rights']." <a href=\"".get_right_link_add()."\">Cliquez ici pour vous r�abonner</a></span>";
      }
    }
    
    
    ?>
  	</div>
	</div>		
	
	</div>

</div>
<!-- END: HEADER -->

<!-- BEGIN: MAIN NAVIGATION
 id="current"  class="active
 -->
<div id="ja-mainnavwrap">
	<div id="ja-mainnav" class="clearfix">
	<ul class="menu">
	<?
  
    if($_TPL_VARS['menu']=='online'){
      ?>
        <li><a href="../bin/main.php"><span>Mes h�tels</span></a></li>
        <li><a href="../bin/planing.php"><span>Le planning</span></a></li>
        <li><a href="../bin/client.php"><span>Les clients</span></a></li>
        <?
        if(is_admin()){
        ?>
        <li><a href="../bin/chambre.php"><span>Les chambres</span></a></li>
        <?
        }
        ?>
        <li><a href="../bin/reservation.php"><span>Les r�servations</span></a></li>
        <li><a href="../bin/facture.php"><span>Les devis &amp; factures</span></a></li>
        <?
          if(is_admin()){
              ?>
                <li><a href="../bin/administration.php"><span>Administration</span></a>
                <ul>
                  <li><a href="../bin/administration.php"><span>Editions</span></a></li>
                  <li><a href="../bin/produit.php"><span>Les produits &amp; services</span></a></li>
                  <li><a href="../bin/categorie.php"><span>Les cat�gories de chambres</span></a></li>
                  <li><a href="../bin/utilisateur.php"><span>Les utilisateurs</span></a></li>
                  <li><a href="../bin/model.php"><span>Les mod�les de documents</span></a></li>
                  <li><a href="../bin/param.php"><span>Les param�tres</span></a></li>
                  <li><a href="../bin/main.php?action=DECONNEXION"><span>Me d�connecter</span></a></li>
                  <li><a href="http://www.gestion-hotel.com"><span>Retour au site</span></a></li>
                
                </ul> 
              
              </li>
              <?
          }
          else{
            ?>
            <li><a href="../bin/main.php?action=DECONNEXION"><span>Me d�connecter</span></a></li>
            <li><a href="http://www.gestion-hotel.com"><span>Retour au site</span></a></li>
                
            <?
          }
        ?>
        
      
      <?  
    }
    else{
    ?>
        <li><a href="../bin/main.php?action=FOR_LOGIN"><span>M'identifier</span></a></li>
        <li><a href="../bin/main.php?action=DECONNEXION"><span>Me d�connecter</span></a></li>
        <li><a href="http://www.gestion-hotel.com"><span>Retour au site</span></a></li>
        
      <?  
    }
  
  ?>
         
  </ul>
	</div>

</div>
<!-- END: MAIN NAVIGATION -->

<div id="ja-containerwrap">
<div id="ja-containerwrap2">
	<div id="ja-container">
	



<!-- BEGIN: CONTENT -->
		<div id="ja-contentwrap">

		<div id="ja-content">

