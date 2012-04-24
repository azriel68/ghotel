<?php

/*
 * Classe permettant la création d'élément formulaire
 * Alexis ALGOUD 25 juin 07 20:30:54
 */

Class TForm{
var $type_aff='FORM'; //Type d'affichage du formulaire (FORM / VIEW )

var $trans=array("\""=>'&quot;');
 /**
  * @return Form
  * @param pMethod String
  * @param pAction String
  * @param pName String
  * @desc constructeur de la classe form
  */
  
Function TForm($pAction=null,$pName=null,$pMethod="POST",$pTransfert=FALSE,$plus=""){
/*
 * Déclaration du formaulaire
 * Alexis ALGOUD 25 juin 07 20:31:20
 */

	if ($pName!="") {
	
	    if($pAction==null)$pAction=$_SERVER['PHP_SELF'];
	
	    echo "\n<FORM METHOD='".$pMethod."'" ;
	    if ($pTransfert)
	      echo " ENCTYPE = 'multipart/form-data'"; 
		if($plus)  
		  echo " $plus ";
	    echo " ACTION='".$pAction."'";
	    echo " NAME='".$pName."'>\n";
	}
}

function Set_typeaff($pType='FORM'){
  if (($pType=='FORM') || ($pType=='NEW'))
    $this->type_aff='FORM';
  else
    $this->type_aff='VIEW';  
}

function hidden($pName,$pVal,$plus=""){
  if($pVal=='auto'){
    $pVal = isset($_REQUEST[$pName])?$_REQUEST[$pName]:"";
  }

  $field = "<INPUT TYPE='HIDDEN' NAME='$pName' VALUE=\"$pVal\" ".$plus.">\n ";
  return $field;
} 

function texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $more_options=array()){
  if($pVal=='auto'){
    $pVal = isset($_REQUEST[$pName])?$_REQUEST[$pName]:"";
  }

  $lib="";
  $field="";
  if ($pLib!="")
    $lib   = "<b> $pLib </b>";
  if ($pTailleMax==0) 
     $pTailleMax=$pTaille;
  if ($this->type_aff!='VIEW'){
    $field = "<INPUT class='$class' TYPE='TEXT' NAME='$pName' ID='$pName' VALUE=\"".strtr($pVal,$this->trans)."\" SIZE='$pTaille' MAXLENGTH='$pTailleMax' $plus>\n ";
 
    if(substr($pName,0,3)=="dt_"){
      $field .="<script type=\"text/javascript\">\n";
      $field .="  Calendar.setup({\n";
      $field .="  inputField     :    \"$pName\",   // id of the input field\n";
      $field .="  ifFormat       :    \"%d/%m/%Y\",       // format of the input field\n";
      $field .="  showsTime      :    false,\n";
      //$field .="  timeFormat     :    \"24\",\n";
     
      $field .=" align          :    \"bR\",\n";
      $field .=" singleClick    :    true";
      
      if(isset($more_options['js_flatCallback'])) $field .=",\n flatCallback : ".$more_options['js_flatCallback']."";
       
      $field .="});";
      $field .= "</script>";
    }
 
  }
  else
    $field = "<INPUT class='text_view' TYPE='TEXT' READONLY TABINDEX=-1 NAME='$pName' VALUE=\"".strtr($pVal,$this->trans)."\" SIZE='$pTaille' MAXLENGTH='$pTailleMax'>\n ";
//    $field = $pVal;
  if ($lib != '')
    return $lib." ".$field;
  else
    return $field;
}

function zonetexte($pLib,$pName,$pVal,$pTaille,$pHauteur=5,$plus='',$class='text'){
  $lib="";
  $field="";
  if ($pLib!="")
    $lib   = "<b> $pLib </b>";
  if ($this->type_aff!='VIEW')
  	$field = "<textarea class='$class' name=\"$pName\" cols=\"$pTaille\" rows=\"$pHauteur\" $plus>$pVal</textarea>\n";
  else
  	$field = "<textarea class='text_view' name=\"$pName\" cols=\"$pTaille\" rows=\"$pHauteur\" READONLY TABINDEX=-1 $plus>$pVal</textarea>\n";
//    $field = $pVal;
  if ($lib != '')
    return $lib." ".$field;
  else
    return $field;
}

function fichier($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class='text'){
  $lib="";
  $field="";
  if ($pLib!="")
    $lib   = "<b> $pLib </b>";
  if ($pTailleMax==0) 
     $pTailleMax=$pTaille;
  if ($this->type_aff!='VIEW')
    $field = "<INPUT class='$class' TYPE='FILE' NAME='$pName' VALUE=\"$pVal\" SIZE='$pTaille' MAXLENGTH='$pTailleMax' $plus>\n ";
  else
    $field = "<INPUT class='text_view' TYPE='TEXT' READONLY TABINDEX=-1 NAME='$pName' VALUE=\"$pVal\" SIZE='$pTaille' MAXLENGTH='$pTailleMax'>\n ";
//    $field = $pVal;
  if ($lib != '')
    return $lib." ".$field;
  else
    return $field;
}

function texteRO($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$id=''){
  $lib="";
  $field=""; 
  if ($pLib!="")
    $lib   = "<b> $pLib </b>";
  if ($pTailleMax==0) 
     $pTailleMax=$pTaille;
     
  if ($this->type_aff!='VIEW')
      $field = "<INPUT class='text_readonly' TYPE='TEXT' READONLY TABINDEX=-1 NAME='$pName' VALUE=\"".strtr($pVal,$this->trans)."\" SIZE='$pTaille' MAXLENGTH='$pTailleMax' ".(($id=="")?"":" id='$id' ").">\n ";
  else
    $field = "<INPUT class='text_view' TYPE='TEXT' READONLY TABINDEX=-1 NAME='$pName' VALUE=\"".strtr($pVal,$this->trans)."\" SIZE='$pTaille' MAXLENGTH='$pTailleMax' ".(($id=="")?"":" id='$id' ").">\n ";
	
//print htmlentities($field);	

//      $field = $pVal;
  if ($lib != '')
    return $lib." ".$field;
  else
    return $field;
}

function filetexteRO($pFile,$pLib,$pName,$pVal,$pTaille,$pTailleMax=0){
global $app;
  $lib="";
  $field="";
  if ($pLib!="")
    $lib   = "<b> $pLib </b>";
  if ($pTailleMax==0) 
     $pTailleMax=$pTaille;
  if ($this->type_aff!='VIEW'){
      $field = "<INPUT class='text_readonly' TYPE='TEXT' READONLY TABINDEX=-1 NAME='$pName' VALUE=\"$pVal\" SIZE='$pTaille' MAXLENGTH='$pTailleMax'>\n ";
  }
  else if ($pVal=="") {
  		$field = "<b>Aucun fichier lié</b>";
           
  }
  else {
    	$field = "<a class=lienquit href=\"javascript:showPopup('../dlg/get_file.php','','$pFile',400,400);\">".$pVal."</a>";
  }
//      $field = $pVal;
  if ($lib != '')
    return $lib." ".$field;
  else
    return $field;
}


function password($pLib,$pName,$pVal,$pTaille,$pTailleMax=0){
  $lib="";
  $field="";
  if ($pLib!="")
    $lib   = "<b> $pLib </b>";
  if ($pTailleMax==0) 
     $pTailleMax=$pTaille;
  $field = "<INPUT class='text' TYPE='PASSWORD' NAME='$pName' VALUE=\"$pVal\" SIZE='$pTaille' MAXLENGTH='$pTailleMax'>\n";
  return $lib." ".$field;
}

function combo($pLib,$pName,$pListe,$pDefault,$pTaille=1,$onChange='',$plus='', $array_before=array()){
// AA : 16/09/2004
// Ajout du onChange

  if($pDefault=='auto'){
    $pDefault = isset($_REQUEST[$pName])?$_REQUEST[$pName]:"";
  }


  $lib="";
  $field="";
  if ($pLib!="")
    $lib   = "<b> $pLib </b>";

  $field = "<SELECT NAME='$pName'";
  if ($onChange!='') {
      $field.=" onChange=\"$onChange\"";
  }
  if($plus!=''){
  		$field.=" ".$plus;
  }
  
  $field.=">\n";
  
  foreach ($array_before as $val=>$libelle) {
  	   $field .= "<OPTION VALUE=\"$val\" ".((strcmp($val,$pDefault))?'':'SELECTED').">$libelle</OPTION>\n";
  }
  
  while (list($val,$libelle) = each ($pListe))
  {
  	if ($val!=$pDefault)
  	   $field .= "<OPTION VALUE=\"$val\">$libelle</OPTION>\n";
  	else
  	   $field .= "<OPTION VALUE=\"$val\" SELECTED>$libelle</OPTION>\n";
  }
  $field .="</SELECT>";
 
  if ($this->type_aff =='VIEW'){
    if (array_key_exists($pDefault,$pListe)) $val=$pListe[$pDefault]; else $val="";  
    $field = "<INPUT class='text_view' TYPE='TEXT' READONLY TABINDEX=-1 NAME='$pName' VALUE=\"$val\" SIZE='$pTaille'>\n ";
  }
 
  if ($lib != '')
    return $lib." ".$field;
  else
    return $field;
}

function checkbox($pLib,$pName,$pListe,$pDefault){
  $lib   = "<b> $pLib </b>";
  $field ="<TABLE class='form' BORDER=0><TR>\n";
  while (list ($val, $libelle) = each ($pListe))
  {
    $field .= "<TD>$libelle</TD>";
    if ($val == $pDefault) 
       $checked = "CHECKED";
    else 
       $checked = " ";
    $field .= "<TD><INPUT TYPE='CHECKBOX' NAME='$pName' VALUE=\"$val\" "
                  . " $checked > </TD>\n";
  }
  $field .= "</TR></TABLE>";
  return $lib." ".$field;
}
function checkbox1($pLib,$pName,$pVal,$checked=false,$plus=''){
  if($checked==true)$checked="CHECKED";
  else $checked=" ";

  $field="";
  
  if ($this->type_aff =='VIEW'){
			if($checked!=" ")$field="<img src=\"../images/croix.gif\" border=0>";
  }
  else {
  		$field = "<INPUT TYPE=\"CHECKBOX\" id=\"$pName\" NAME=\"$pName\" VALUE=\"$pVal\" $checked $plus>\n";
  }

  return $pLib." ".$field;
}
function radio($pLib,$pName,$pListe,$pDefault){
    $lib   = "<b> $pLib </b>";
    $field ="<TABLE class='form' BORDER=0><TR>\n";
    while (list ($val, $libelle) = each ($pListe)){
        $field .= "<TD>$libelle</TD>";
        if ($val == $pDefault) 
            $checked = "CHECKED";
        else 
            $checked = " ";
        $field .= "<TD><INPUT TYPE='RADIO' NAME='$pName' VALUE=\"$val\" ". " $checked> </TD>\n";
    }
    $field .= "</TR></TABLE>";
    if ($this->type_aff =='VIEW')
      $field = $pListe[$pDefault];  
    return $lib." ".$field;
}

function radio1($pLib,$pName,$pVal,$pDefault){
        $field="";
		
		if ($pVal == $pDefault) 
            $checked = "CHECKED";
        else 
            $checked = " ";
		
		if ($this->type_aff =='VIEW'){
			if($checked!=" ")$field="<img src=\"../images/croix.gif\" border=0>";
		}
		else{
			$field = "<INPUT TYPE='RADIO' NAME='$pName' VALUE=\"$pVal\" $checked>\n";
		
		}
		
		
		return $pLib." ".$field;
}

function end_form(){
    return "</FORM>\n";
}


function btImg($pLib,$pName,$pImg){
    $field = "<INPUT TYPE='IMAGE' NAME='$pName' src=\" $pImg \" border='0' alt=\"$pLib\">\n";  
    return $field;
}

function btsubmit($pLib,$pName){
    $field = "<INPUT class='button' TYPE='SUBMIT' NAME='$pName' VALUE=\"$pLib\">\n";
    return $field;
}
function bt($pLib,$pName,$plus=""){
    $field = "<INPUT class='button' TYPE='BUTTON' NAME='$pName' VALUE='$pLib' $plus>\n";
    return $field;
}

function btreset($pLib,$pName){
    $field = "<INPUT class='button' TYPE='RESET' NAME='$pName' VALUE='$pLib'>\n";
    return $field;
}
function btsubmithidden($pLib,$pName){
    $field = "<INPUT class='button_hidden' TYPE='SUBMIT' NAME='$pName' VALUE='$pLib'>\n";
    return $field;
}

}
?>