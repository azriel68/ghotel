<?
  require("../includes/inc.php");
  
  entete("Achat de 30 jours d'utilisation suppl�mentaires");
  menu_off();
  
?>

<h1>Achat de 30 jours d'utilisation suppl�mentaires</h1>

<div align="center">
<div style="width:500px;">

<div class="information">
La premi�re �tape consiste � obtenir un code afin de cr�diter votre compte. 
Il vous en co�tera 19,99� pour 31 jours suppl�mentaires d'utilisation (nombre divis�
- arrondi au sup�rieur - par le nombre d'h�tel que vous g�rez dans l'interface).
L'interface de paiement et pleinement s�curis�e et vos informations ne seront pas
conserv�es. 
</div>

<table border="0" cellpadding="0" cellspacing="0" width="149" height="80">
 <tr>
  <td width="149" height="80">
   <form name="cben" action="https://payment.allopass.com/subscription/subscribe.apu" method="POST" target="DisplaySub">
    <input type="hidden" name="idd" value="445132">
    <input type="hidden" name="ids" value="162222">
    <input type="hidden" name="lang" value="fr">
       <input type="image" src="http://www.allopass.com/imgweb/script/fr/cb_subscribe_os.gif" alt="Ticket d'acc�s" onClick="window.open('','DisplaySub','toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=1,resizable=1,width=600,height=570');" border = 0>
      </form>
  </td>
 </tr>
</table>
			  
<div class="information">
En second lieu, saisissez le code obtenu dans le champs "Ticket d'acc�s" ci-dessous
et validez. Vous serez alors redirig� sur une page de confirmation et un email
vous parviendra pour confirmer votre nouveau cr�dit.
</div>			  

<form action="http://payment.allopass.com/subscription/access.apu" method="POST">
 <input type="hidden" name="idd" value="445132">
 <input type="hidden" name="ids" value="162222">
 <input type="hidden" name="lang" value="fr">
 <table border="0" cellpadding="0" cellspacing="0" width="300">
  <tr>
   <td width="300" height="68" colspan="3">
         <img src="http://payment.allopass.com/imgweb/script/fr/cb_os_top.gif" alt="">
       </td>
  </tr>
  <tr>
   <td width="157" valign="middle" bgcolor="White">
    <font face="Arial,Helvetica" color="Black" size="11" Style="font-size: 12px;">
     <b>Entrez votre ticket d'acc�s</b>
    </font>
   </td>
   <td width="80" bgcolor="White">
    <input type="text" size="10" maxlength="10" value="Ticket" name="code" onFocus="if (this.form.code.value=='Ticket') this.form.code.value=''" style="BACKGROUND-COLOR: #E7E7E7; BORDER-BOTTOM: #000080 1px solid; BORDER-LEFT: #000080 1px solid; BORDER-RIGHT: #000080 1px solid; BORDER-TOP: #000080 1px solid; COLOR: #000080; CURSOR: text; FONT-FAMILY: Arial; FONT-SIZE: 10pt; FONT-WEIGHT:bold; LETTER-SPACING: normal; WIDTH:85; TEXT-ALIGN=center;">
   </td>
   <td width="58" align="center" bgcolor="White">
    <input type="button" name="APsub" value="" onClick="this.form.submit(); this.form.APsub.disabled=true;" style="border:0px;margin:0px;padding:0px;width:48px; height:18px; background:url('http://www.allopass.com/img/bt_ok.png');">
   </td>
  </tr>
  <tr><td colspan="3" width="300" height="13"><img src="http://payment.allopass.com/img/cb_bot.gif" alt=""></td></tr>
 </table>
</form>

</div>
</div>
			  			  
<?  
  
  pied_de_page();

?>
