<?php
function get_droit_sess_groupe(){
    
    if(isset($_SESSION[SESS_GRP])) {
      return $_SESSION[SESS_GRP]->get_dtendrights();
    }
    else{
      return false;
    }

     
}
function have_droit_sess_groupe(){
    if(get_sess_user_group_id()==99) return true; // utilisateur de d�mo !

    return ($_SESSION[SESS_GRP]->dt_end_rights>time())?true:false; 
}
function get_right_link_add(){

  return "javascript:alert('L\'abonnement sera bient�t disponible.');";
}
function get_right_erreur_msg($fct) {
    $r= '<div class="information_erreur">'
    .$fct
    .'Attention ! Cette fonctionnalit� est d�sactiv�e car votre abonnement est arriv� � expiration. 
    R�abonnez-vous d�s maintenant en cliquant sur ce <a href="'.get_right_link_add().'">lien</a>.
    </div>';

    return strtr($r,array(
      /*'"#"'=>'"'.get_right_link_add().'"'*/
      '"#"'=>"\"javascript:alert('Vous devez d\'abord vous r�abonner. Merci.');\""
    ));

}
?>
