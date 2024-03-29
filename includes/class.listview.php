<?php

Class TColumn{
    var $key="";
    var $keyname="";
    var $type="C";
    var $hidden=false;
}

Class TListview
{
/**
* @Access private
* @Desc determine l'affichage de l'entete
**/
Var $showheader=true;

/**
* @Access private
* @Desc tableau repr�sentant une ligne de la liste (DATA)
**/
var $line=array();

/**
* @Access private
* @Desc tableau de lignes (liste de DATA)
**/
var $lines=array();


/**
* @Access private
* @Desc nombre de lignes (cf tableau de lignes)
**/
Var $rowcount;

var $noMaxPage;

/**
*@Access private
*@Desc tableau repr�sentant une ent�te de la liste
**/
var $column=array(); // Tableau d'objet column

/**
* @Acces private
* @Desc contient le nom de la colonne sur laquelle on fait le tri
**/
var $orderColumn;

/**
* @Acces private
* @Desc contient le type tri ('A' pour ASC, 'D' pour DESC)
**/
var $orderTyp='A';

/**
* @Acces private
* @Desc nombre de lignes � afficher par page
**/
var $nbLinesPerPage;

/**
* @Acces private
* @Desc num�ro de la page courrante
**/
var $currentPage;

/**
* @Access private
* @Desc determine l'affichage du pied de liste (liste de lien repr�sentant l'index des pages)
**/
Var $showfooter=true;

/* Nom de la fonction javascript appel�e lors d'un click sur 1 ligne de la liste */
var $onClickAction;
/* liste des param�tres associ�s � la fonction onClickAction; string dans laquel les param�tres sont s�par�s par des , */
var $onClickParam;
/* Nom de la fonction javascript appel�e lors d'un dblclick sur 1 ligne de la liste */
var $onDblClickAction;
/* liste des param�tres associ�s � la fonction onDblClickAction; string dans laquel les param�tres sont s�par�s par des , */
var $onDblClickParam;

/**
* @Access private
* @Desc Nom du formulaire associ�
**/
//var $formname;

/**
* @Access private
* @Desc Nom du tableau
**/
var $name;

/**
* @Access private
* @Desc requ�te associ� au tableau (facultatif)
**/
var $query;


var $repImg;

/**
 * ALGOUD Alexis 27/10/2004 13:34:17
 * Permet de rajouter l'action column avant le tableau
 **/
var $actioncolumn=false;
var $actioncolvalue=array();

var $totalCol = array();

var $navUrl="";
var $line_focus = "";

/**
* @return Listview
* @desc Constructeur de la classe
**/
function TListview($pName){
        $this->rowcount=0;
        $this->nbLinesPerPage=5;
        $this->currentPage=0;
        $this->name=$pName;
        $this->repImg="../images/";
        
        $this->index_onglets="";
        
}
function Set_focus($col,$val,$class){

	$this->TFocus[$col][$val]=$class;

}
function Str_trans($col,$str_rep){
/**
 * ALGOUD Alexis 09/02/2005 21:11:41
 * Applique les mod lib
 **/
	$nb=count($this->lines);

	for($i = 0; $i < $nb; $i++){

		$line = &$this->lines[$i];

		$line[$col] = strtr($line[$col],$str_rep);

	} // for


}

function highlight_search($col, $search){

    $pattern = '('. quotemeta($search) .')';
    $replacement = '<span class="search">\\1</span>';
    
    $nb=count($this->lines);

  	for($i = 0; $i < $nb; $i++){
  
  		$line = &$this->lines[$i];
  
  		$line[$col] = eregi_replace($pattern, $replacement, $line[$col]); 
  
  	} // for

}

function Set_navUrl($s=""){
		$this->navUrl=$s;
}
function Set_maxPage($i){

	$this->maximum_nombre_page = $i;

}
function set_actionColumn($mode,$keycol,$formname="",$cheked=array()){
	switch($mode){
		case 'DELETE':
			$this->actioncolumn=true;
			$this->actioncolvalue['titre']="<span title=\"S�lectionner la ligne\">S.</span>";
			$this->actioncolvalue['var']="Tcheckdelete";
			$this->actioncolvalue['checked']=$cheked;
			$this->actioncolvalue['key']=$keycol;
			$this->actioncolvalue['action']="<a class=lien_menusec href=\"javascript:submitform('$formname','DELETE');\">Supprimer</a>";

			break;
		case 'ADD':
			$this->actioncolumn=true;
			$this->actioncolvalue['titre']="<span title=\"S�lectionner la ligne\">S.</span>";
			$this->actioncolvalue['var']="Tcheckadd";
			$this->actioncolvalue['checked']=$cheked;
			$this->actioncolvalue['key']=$keycol;
			$this->actioncolvalue['action']="<a class=lien_menusec href=\"javascript:submitform('$formname','ADD');\">Ajouter</a>";
			break;
		case 'FACT':
			$this->actioncolumn=true;
			$this->actioncolvalue['titre']="<span title=\"S�lectionner la ligne\">S.</span>";
			$this->actioncolvalue['var']="Tcheckfact";
			$this->actioncolvalue['checked']=$cheked;
			$this->actioncolvalue['key']=$keycol;
			$this->actioncolvalue['action']="<a class=lien_menusec href=\"javascript:submitform('$formname','FACT');\">Facturer</a> / <a class=lien_menusec href=\"javascript:submitform('$formname','FACTMULT');\">G�n�rer les factures</a>";
			break;
		case 'PRINT_FP':
			$this->actioncolumn=true;
			$this->actioncolvalue['titre']="<span title=\"S�lectionner la ligne\">S.</span>";
			$this->actioncolvalue['var']="Tcheckprint";
			$this->actioncolvalue['checked']=$cheked;
			$this->actioncolvalue['key']=$keycol;
			$this->actioncolvalue['action']="<a class=lien_menusec href=\"javascript:submitform('$formname','PRINT_FP');\">Imprimer les feuilles de pr�sence</a>";
			break;
		case 'STAT':
			$this->actioncolumn=true;
			$this->actioncolvalue['titre']="";
			$this->actioncolvalue['key']=$keycol;
			$this->actioncolvalue['action']="<a href=\"javascript:document.location.href='admin_indusstat.php?id=#INT_KEY#&eraselist=1';\">Statistiques</a>";
			break;
		case 'EMAIL':
			$this->actioncolumn=true;
			$this->actioncolvalue['titre']="";
			$this->actioncolvalue['key']=$keycol;
			$this->actioncolvalue['action']="<a href=\"javascript:showPopup('../dlg/send_lost_email.php?id=#INT_KEY#','$formname','');\" title=\"Envoyer ses identifiants � ce candidat\"><img src=\"../images/mail.gif\" border=\"0\"></a>";
			break;
		case 'EMAIL_RECRUT':
			$this->actioncolumn=true;
			$this->actioncolvalue['titre']="";
			$this->actioncolvalue['key']=$keycol;
			$this->actioncolvalue['action']="<a href=\"javascript:showPopup('../dlg/send_lost_email_recrut.php?id=#INT_KEY#','$formname','');\" title=\"Envoyer ses identifiants � ce recruteur\"><img src=\"../images/mail.gif\" border=\"0\"></a>";
			break;

		default:
			echo "Mode d'action inconnu : ".$mode;
	} // switch

}
function Set_nbLinesPerPage($nLines){
        if (!is_null($nLines) && is_int($nLines) && $nLines>0)
                $this->nbLinesPerPage=$nLines;
}

function Set_showfooter($pShow){
        if (is_bool($pShow)){
                  $this->showfooter=$pShow;
        }
}

/**
* Set_header permet de construire la ligne d'ent�te
* @Access Public
* @param $pheader array()
**/
function Set_header($pHeader){

        if (is_array($pHeader)){
                foreach($pHeader as $key=>$val){
                        $this->column[$key]=new TColumn();
                        $this->column[$key]->key = $val;
                }
        }
}

/**
* @desc permet d'initialiser les colonnes � masquer � l'affichage
* @Access Public
* @Param :
*        $pCol : nom de la colonne � masquer ou non
*        $pBool : true : on masque la colonne, false : on l'affiche
**/
function Set_hiddenColumn($pCol,$pBool){
        if (array_key_exists($pCol,$this->column) && is_bool($pBool))
                $this->column[$pCol]->hidden = $pBool;
}

/**
* @desc intialise 1 colonne en cl�
* @Access Public
**/
function Set_key($pKey,$pKeyName=''){
        if ($pKeyName=='') $pKeyName=$pKey;
        if (array_key_exists($pKey,$this->column)){
                $this->column[$pKey]->key =1;
                $this->column[$pKey]->keyname=$pKeyName;
        }
}

/**
* Set_showheader positionne le booleen d'affichage d'entete (titre des colonnes)
* @Access Public
* @param $pShow booleen
**/
function Set_showheader($pShow){
        if (is_bool($pShow)){
                $this->showheader=$pShow;
        }
}

function Set_Query($pQuery){
        if (!is_null($pQuery) && is_string($pQuery)){
                $this->query=$pQuery;
        }
}

function set_pagenumber($pageNumber){
        if (!is_null($pageNumber))
                $this->currentPage = $pageNumber;
        else
                $this->currentPage = 0;
}

function _make_index(&$db, &$TIndex){

  $table = $TIndex['table'];
  $idxchamps = isset($TIndex['idx'])?$TIndex['idx']:"idx";
  $selChar = isset($TIndex['char'])?$TIndex['char']:"all";
  $condition = isset($TIndex['condition'])?" WHERE ".$TIndex['condition']:"";
  
  
  $Tindexexist=array();
	$db->Execute("SELECT DISTINCT(".$idxchamps.") as 'indexfor' FROM ".$table." $condition ORDER BY `indexfor` ");
	
  $Tindexexist['all']="Tous";
  while($db->Get_line()){
		$Tindexexist[$db->Get_field('indexfor')]=$db->Get_field('indexfor');
	}


  $t=new TTbl;
  $body = "";
  foreach($Tindexexist as $k=>$v){
    if(isset($TIndex['TTrans'])) $v = strtr($v, $TIndex['TTrans']);
  
    $body.=$t->link("$v","javascript:TListview_SetIndex('$k')",($k==$selChar)?"list_index_onglet_sel":"list_index_onglet");
  }
	
	$this->index_onglets = $body;
	
  
}

function Load_query($orderColumn="",$orderTyp="", $TIndex=null){

    
        //initialisation des variables de la classe :
		$this->lines=array();

        $db =& new TDB();
    
        if($TIndex!=null){
          $this->_make_index($db, $TIndex);

        }
        
    
        $this->Set_Order($orderColumn,$orderTyp);
        if (!is_null($this->query) && is_string($this->query)){
                //on ajoute la clause order by si necessaire
				$query_order=null;
                if (!is_null($this->orderColumn) && ($this->orderColumn!='')){
                        if ($this->orderTyp=='A') $query_order=" ORDER BY `".$this->orderColumn."` ASC";
                        else if ($this->orderTyp=='D') $query_order=" ORDER BY `".$this->orderColumn."` DESC";
                }

				if(isset($this->limit_line)){
					$query_order.=" LIMIT ".$this->limit_line;
				}

                if(isset($query_order))$db->execute($this->query.$query_order);
				else $db->execute($this->query);

                //on charge les donn�es d'ent�te
                $this->Set_header($db->Get_lineHeader());

                $this->currentLine=$db->rs->currentRow();
                //on charge les datatypes des colonnes
                for ($i=0;$i<$db->rs->FieldCount();$i++){
                        $fld=$db->rs->FetchField($i);
                        //echo $fld->name." ".$fld->type."<BR>";
                        switch ($db->rs->MetaType($fld->type)){
                                case 'D':
                                        $this->column[$fld->name]->type='DATE';
                                        break;
                                case 'T':
                                        $this->column[$fld->name]->type='TIME';
                                        break;
                                default:
                                        $this->column[$fld->name]->type=$db->rs->MetaType($fld->type);
                                        break;
                        }
                }
                $this->currentLine=$db->currentLine;
                if (is_array($this->currentLine)){
                        foreach ($this->currentLine as $key=>$val)
                              /*  $ret="";
                                $ret= array_merge($ret,array("$key" => "0"));*/
                                //s'il y a des lignes de resultat on charge la liste sinon on ins�re une ligne vide ....
                                $nbre=$db->get_Recordcount();
                                //on charge la liste avec les donn�es issues de la requete
                                if ($nbre>0){
                                        for ($i=1;$i<=$nbre;$i++)
                                        $this->Add_line($db->Get_line());
                                }
                        }
                }
}

/**
* Get_rowcount pour connaitre le nombre de lignes contenues dans la liste
* @Access Public
* @return $this->rowcount le nb de lignes DATA
**/
function Get_rowcount(){
    return $this->rowcount;
}

function Add_line($pline){
        if (is_array($pline)){
            $this->line=$pline;
            $this->rowcount=array_push($this->lines,$this->line);
        }
}

/**
* @desc D�finie l'action lors d'un Click sur une ligne de la liste
* @Access Public
* @param :
        $pFunctionName : nom de la fonction javascript � execut�
        $pParam : liste des param�tres de la fonction javascript s�par�s par des ,
                (celle-ci peut-�tre compl�t� dans la fonction Render en fonction de la fonction � executer)
**/
function Set_OnClickAction($pFunctionName,$pParam='',$razParam=false){
/**
 * Alexis ALGOUD 25/09/2004 23:51:54
 * Ajout de la remise � z�ro du param
 **/

        if (!is_null($pFunctionName)){
                $this->onClickAction=$pFunctionName;
                if ($pParam!=''){
					if($razParam)$this->onClickParam="$pParam";
					else $this->onClickParam.="$pParam";
				}

        }
}

/**
* @desc D�finie l'action lors d'un DblClick sur une ligne de la liste
* @Access Public
* @param :
        $pFunctionName : nom de la fonction javascript � execut�
        $pParam : liste des param�tres de la fonction javascript s�par�s par des ,
                (celle-ci peut-�tre compl�t� dans la fonction Render en fonction de la fonction � executer)
**/
function Set_OnDblClickAction($pFunctionName,$pParam=''){
        if (!is_null($pFunctionName)){
                $this->onDblClickAction=$pFunctionName;
                if ($pParam!='')
                    $this->onDblClickParam.="$pParam";
        }
}

/**
* Affichage de l'ent�te de liste
* @access private
**/
function display_header(){

//print "!DEBUG!";
        $r= "<TR CLASS='listheader' style=\"cursor:hand\">";

		if($this->actioncolumn==true){
			$r.="<td align=center width=15>".$this->actioncolvalue['titre']."</td>";
		}

        foreach($this->column as $key=>$obj){
            if (!$obj->hidden){
               $r .="<TD id='".$this->name."_".$key."' ";
/*print "$key = ";
print_r($obj);
print "<br />";*/
               if($obj->type=="none"){
                $r.="onClick=\"alert('Colonne non triable')\">";
               }
      			   else if($this->navUrl==""){
      					$r.="onClick=\"TListview_OrderBy('$this->name','$this->orderColumn','$this->orderTyp','$key')\">";
      			   }
      			   else {
      					$r.="onClick=\"TListviewUrl_OrderBy('".$this->navUrl."','$this->name','$this->orderColumn','$this->orderTyp','$key')\">";
      			   }
      
//print "$key==".$this->orderColumn."<br />";
			        if ($key==$this->orderColumn){
                        if ($this->orderTyp=='A')
                            $r .= "<IMG SRC='".$this->repImg."/tridown.gif'>".$key;
                        else
                             $r .= "<IMG SRC='".$this->repImg."/triup.gif'>".$key;
                    }
                    else
                        $r .= $key;
                $r .="</td>\n";
            }
        }
        $r .= "</TR>";
        return $r;
}
function display_header_prt(){
        $r= "<TR>";

		$flag = false;
		foreach($this->column as $key=>$obj){
            if (!$obj->hidden){

               $r .="<TD style=\"";
			   if($flag)$r.="border-left : 1px solid #000000;";
			   else $flag=true;
			   $r.="border-bottom : 1px solid #000000\"><b>";
                    $r .= $key;
               $r .="</b></td>\n";
            }
        }
        $r .= "</TR>";
        return $r;
}

function _getparam_openform(&$lstParam,&$pline){
        foreach ($this->column as $k1=>$obj){
            if ($obj->key)
                    $lstParam.="&$obj->keyname=$pline[$k1]";
        }
        $lstParam=str_replace(",","','",$lstParam);
        $lstParam="'$lstParam'";
}

function _getparam_listlinkform(&$lstParam,&$pline){
        //r�cup�ration des diff�rentes valeurs pour les passer dans la fonction javascript
        $js="";
        foreach ($this->column as $k1=>$obj){
        switch (strtoupper($obj->type)){
            case 'DATE':
                $js.="$obj->keyname=".Date('d/m/Y',strtotime($pline[$k1])).";";
                break;
            case 'DATETIME':
                $js.="$obj->keyname=".Date('d/m/Y H:i:s',strtotime($pline[$k1])).";";
                break;
            case 'TIME':
                $js.="$obj->keyname=".Date('H:i',strtotime($pline[$k1])).";";
                break;
            case 'TIMESTAMP':
                $js.="$obj->keyname=".Date('d/m/Y H:i:s',$pline[$k1]).";";
                break;
            case 'IP':
                $js.="$obj->keyname=".long2ip($pline[$k1]).";";
                break;
            default:
                $js.="$obj->keyname=".trim($pline[$k1]).";";
                break;
        }
        }
        $js=addslashes(htmlentities($js));
        if (!is_null($js) && $js>"")
            $lstParam.=",'$js'";
}

function _getparam_linkform(&$lstParam,&$pline){
        //r�cup�ration des diff�rentes valeurs pour les passer dans la fonction javascript
        $js="";
        foreach ($this->column as $k1=>$obj){
            if ($obj->key)
                    $js.="$obj->keyname=".trim($pline[$k1]).";";
        }
        $js=addslashes(htmlentities($js));
        if (!is_null($js) && $js>"")
            $lstParam.=",'$js'";
}
function _getparam_default(&$lstParam,&$pline){
		$js="";
        foreach ($this->column as $k1=>$obj){
            if ($obj->key) {
            		if($js!="")$js.=",";
                    $js.="'".trim($pline[$k1])."'";
            }
        }

        $lstParam=$js;

}

/**
* Affichage d'une ligne
* @access private
* @param $pline une ligne
**/
function display_line($pline,$pNumRow){
     $event = '';$r='';
	$this->line_focus=false;
        //affichage des datas
    // pr�paration affecttion des �v�nements click et dblClick
        if ($this->onClickAction!=''){
              $lstParam=$this->onClickParam;
                switch ($this->onClickAction){
                        Case 'OpenForm': //fonction javascript d�finie dans function.js qui ouvre 1 formulaire avec la liste des cl�s dans l'url
                		$this->_getparam_openform($lstParam,$pline);
                                break;

                        Case 'TListView_linkForm':
                                $this->_getparam_listlinkform($lstParam,$pline);
                                break;

                        Case 'LinkForm': //fonction javascript d�finie dans function.js qui lie les donn�es d'une liste � un formulaire (attention le nom des champs du formulaires doivent correspondre aves les ent�tes de la liste)
                                $this->_getparam_linkform($lstParam,$pline);
                                break;


                        Case 'LinkForm2': //en plus valid le formulaire d'origine
                                $this->_getparam_linkform($lstParam,$pline);
                                break;
                        default :
                         	$this->_getparam_default($lstParam,$pline);
                        	break;
                 }

                $event .= " onclick=\"$this->onClickAction($lstParam)\"";
        }

        if ($this->onDblClickAction!=''){
                $lstParam=$this->onDblClickParam;
                switch ($this->onDblClickAction){
                        Case 'OpenForm': //fonction javascript d�finie dans function.js qui ouvre 1 formulaire avec la liste des cl�s dans l'url
                				$this->_getparam_openform($lstParam,$pline);
                                break;

                        Case 'TListView_linkForm':
                                $this->_getparam_listlinkform($lstParam,$pline);
                                break;

                        Case 'LinkForm': //fonction javascript d�finie dans function.js qui lie les donn�es d'une liste � un formulaire (attention le nom des champs du formulaires doivent correspondre aves les ent�tes de la liste)
                                $this->_getparam_linkform($lstParam,$pline);
                                break;
                        }

                $event .= " ondblclick=\"$this->onDblClickAction($lstParam)\"";
        }

	if($this->actioncolumn==true){
	/**
	 * ALGOUD Alexis 27/10/2004 14:19:38
	 * Affichage du check
	 **/
		if(isset($this->actioncolvalue['var'])){
			$r.="<td><input type=\"checkbox\" name=\"".$this->actioncolvalue['var']."[$pNumRow]\" value=\"".$pline[$this->actioncolvalue['key']]."\" ".$this->_get_checkedaction('')."></td>";
		}
		else {
			$r.="<td>".strtr($this->actioncolvalue['action'],array("#INT_KEY#"=>$pline[$this->actioncolvalue['key']]))."</td>";
		}
	}

    //affichage des datas".".."."

    foreach ($pline as $key=>$val){
        if (!$this->column[$key]->hidden){
			if(isset($this->TFocus[$key][$val])){
				$this->line_focus=$this->TFocus[$key][$val];
			}

            switch (strtoupper($this->column[$key]->type)){
               case 'DATE':
			   		if($val=="0000-00-00 00:00:00")$r.="<td> --- </td>";
                  	else $r .="<TD ".$event.">".Date('d/m/Y',strtotime($val))."</TD>";
                      break;
               case 'DATEN':
			   		if($val==0)$r.="<td> --- </td>";
                  	else $r .="<TD>".Date('d/m/Y',$val)."</TD>";
                      break;
               case 'DATETIME':
                         $r .="<TD>".Date('d/m/Y H:i:s',strtotime($val))."</TD>";
                      break;
               case 'TIME':
                         $r .="<TD>".Date('H:i',strtotime($val))."</TD>";
                      break;
               case 'TIMESTAMP':
                  $r .="<TD>".Date('d/m/Y H:i:s',$val)."</TD>";
                      break;
               case 'IP':
                  $r .="<TD>".long2ip($val)."</TD>";
                      break;
			   case 'IMAGE':
			      $r .="<TD><img src=\"".$val."\" border=\"0\"></TD>";
                      break;
			   case 'NUMBER':
			   case 'MOY':
			   		$r.="<TD>".(double)$val."</TD>";
					//$this->totalCol[$nbColAff]+=(double)$val;
					break;
               case 'MONEY':
                  $r .="<TD align=right>"._f_prix($val)."</TD>";
                      break;
               default:
                      $r .="<TD ".$event."><a href=\"#\" ".$event.">".$val."</a></TD>";
                      break;
               }
            }

			//$nbColAff++;
    }

	if($this->line_focus!=""){
		$pClass='Class='.$this->line_focus;
	}
	elseif (fmod($pNumRow,2)==0) {
        $pClass='Class=L1';
	}
    else{
	    $pClass='Class=L2';
	}

    $line_r ="<tr ".$pClass." id='".$this->name."_".$pNumRow."' style=\"cursor:hand\">";

	$line_r.=$r;

    $line_r .= "</TR>";

        return $line_r;
}
function display_line_prt($pline,$pNumRow){
    $r ="<tr>";

	$flag=false;
    foreach ($pline as $key=>$val){

	    if (!$this->column[$key]->hidden){
    	$r.="<td style=\"";


		   if($flag){
		   		$r.="border-left : 1px solid #000000;";
		   }
		   else {
		   		$flag=true;
		   }
		   $r.="border-bottom : 1px solid #000000\">";


	        switch (strtoupper($this->column[$key]->type)){
               case 'DATE':
                  $r .=Date('d/m/Y',strtotime($val));
                      break;
               case 'DATETIME':
                         $r .=Date('d/m/Y H:i:s',strtotime($val));
                      break;
               case 'TIME':
                         $r .=Date('H:i',strtotime($val));
                      break;
               case 'TIMESTAMP':
                  $r .=Date('d/m/Y H:i:s',$val);
                      break;
               case 'IP':
                  $r .=long2ip($val);
                      break;
			   case 'NUMBER':
			   		$r.=$val;
					break;
               default:
                      if($val=="")$val="&nbsp;";

					  $r .=$val;
                      break;
               }
		$r.="</td>";
		}

    }
    $r .= "</TR>";
     return $r;
}

/**
* Affichage des lignes
* @access public
**/
function display_lines(){
         $r="";
         $Num_Row=1;
        for ($i=$this->currentPage*$this->nbLinesPerPage ;
            ($i < ($this->currentPage*$this->nbLinesPerPage)+$this->nbLinesPerPage) && ($i < $this->rowcount);
            $i++)
        {
          $r .=$this->display_line($this->lines[$i],$Num_Row++);
        }


		if($this->actioncolumn==true){

			if(isset($this->actioncolvalue['var'])){

				$r.="<tr CLASS='listheader'>";
				$this->column++;
				$nb=count($this->column);
				$r.="<td colspan=$nb>";

				$r.="&nbsp;<img src=\"../images/fleche_sel.gif\"> ".$this->actioncolvalue['action'];

				$r.="</td>";
				$r.="</tr>";
			}
			else {
				null;
			}

		}


		$this->set_totalCol();

		if(count($this->totalCol)>0){
			$r.="<tr CLASS='listheader'>";
			for($i = 0; $i < count($this->column); $i++){
					$r.="<td colspan=$nb>";
					if(isset($this->totalCol[$i])){
						$r.=round($this->totalCol[$i],2);
					}
					else {
						if($i==0){
							$r.="total cumul�, toutes pages";
						}
						else {
							$r.="&nbsp;";
						}

					}
					$r.="</td>";
			} // for
			$r.="</tr>";
		}

        return $r;
}
function set_totalCol(){
	$nb=count($this->lines);

	for($i = 0; $i < $nb; $i++){

		$pline = $this->lines[$i];

		$nbColAff=0;

    	foreach ($pline as $key=>$val){
        if (!$this->column[$key]->hidden){
            switch (strtoupper($this->column[$key]->type)){
			   case 'NUMBER':
			   		$this->totalCol[$nbColAff]+=$val;
					break;
               case 'MOY':
			   		$this->totalCol[$nbColAff]+=$val/$nb;
					/*if($i==0){
						$col_div[]=$nbColAff;
					}*/

					break;

               default:
                      null;
                      break;
               }
            }
			$nbColAff++;
    	}
	} // for

	/*
	$nbm=count($col_div);
	for($i = 0; $i < $nbm; $i++){
		$this->totalCol[$col_div[$i]] = $this->totalCol[$col_div[$i]]/$nb;
	} // for
*/
}
function display_lines_prt(){
         $r="";
         $Num_Row=1;
        for ($i=$this->currentPage*$this->nbLinesPerPage ;
			$i < $this->rowcount;
            $i++)
        {
          $r .=$this->display_line_prt($this->lines[$i],$Num_Row++);
        }
        return $r;
}


function Render_prt(){
        $r = "\n<DIV ID='$this->name' style=\"visibility:visible\">\n";
        $r .="<TABLE CLASS='list' CELLPADDING=2 CELLSPACING=0 WIDTH='100%' style=\"border : 1px solid #000000\">";

		if ($this->rowcount > 0){
            if ($this->showheader){
                       $r.= $this->display_header_prt();
             }
            $r.= $this->display_lines_prt();

		}else{
			$r .= "<TR><TD>Il n'y a pas de ligne � afficher</TD></TR>";
		}


        $r.= "</TABLE>";
		$r .= "</DIV>\n";
        return $r;
}
function Render(){
        //on affiche le tableau
    $this->noMaxPage = ceil($this->rowcount / $this->nbLinesPerPage)-1;

        $r = "\n<DIV ID='$this->name' style=\"visibility:visible\">\n";
        $r.=$this->index_onglets;
        $r .="<TABLE CLASS=\"list\" CELLPADDING=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\">";
    if ($this->rowcount > 0){
            if ($this->showheader){
                       $r.= $this->display_header();
             }
            $r.= $this->display_lines();
    }else
        $r .= "<TR><TD>Il n'y a pas de ligne � afficher</TD></TR>";
        $r.= "</TABLE>";

        //affichage du pied de la liste
        if ($this->showfooter){
                if ($this->noMaxPage>0){
                        $r.= "<TABLE CELLSPACING='0' CELLPADDING='0' WIDTH=100% >";
                        $r.= "<TR ALIGN='center'>";
            			if($this->navUrl==""){
							$r.="<TD><IMG class='nav1' ALIGN='left' SRC='".$this->repImg."/left_arrows.gif' ALT='pr�c�dent' onClick=TDBListview_PreviousPage($this->currentPage,'".$this->name."')> </TD>";
						}
						else {
							$r.="<TD><IMG class='nav1' ALIGN='left' SRC='".$this->repImg."/left_arrows.gif' ALT='pr�c�dent' onClick=TDBListviewUrl_PreviousPage('".$this->navUrl."',$this->currentPage,'".$this->name."')> </TD>";
						}

						if(isset($this->maximum_nombre_page))$max_page = $this->maximum_nombre_page;
						else $max_page = $this->noMaxPage;
						$r.="<TD>";
                        for ($i=0;$i<=$max_page;$i++){
                                /*if ($i%50==0){
                                        $r.="<TD>";
                                        }*/
                                if ($i==$this->currentPage)
                                        $r.="<span class='lien_actif'> ".($i+1)." </span>";
                                else
                                        if($this->navUrl==""){
											$r.="<A class='lien' href=\"javascript:TDBListview_GoToPage(".($i).",$this->noMaxPage,'".$this->name."')\"> ".($i+1)." </A>";
										}
										else {
											$r.="<A class='lien' href=\"javascript:TDBListviewUrl_GoToPage('".$this->navUrl."',".($i).",$this->noMaxPage,'".$this->name."')\"> ".($i+1)." </A>";
										}
                                }
                  		if($this->navUrl==""){
							$r.="</TD><TD><IMG class='nav1' ALIGN='right' SRC='".$this->repImg."/right_arrows.gif' ALT='suivant' OnClick=TDBListview_NextPage($this->currentPage,$this->noMaxPage,'".$this->name."')></TD>";
						}
						else {
							$r.="</TD><TD><IMG class='nav1' ALIGN='right' SRC='".$this->repImg."/right_arrows.gif' ALT='suivant' OnClick=TDBListviewUrl_NextPage('".$this->navUrl."',$this->currentPage,$this->noMaxPage,'".$this->name."')></TD>";
						}
                        $r.= "</TR>";
                        $r.= "</TABLE>";
                }
        }
        $r .= "</DIV>\n";
        return $r;
}

/**
* initialise les variables de tri et effectue le tri de la liste
* @access public
* @param $pCol=colonne sur laquelle doit �tre eff�ctu� le tri, $pTyp=type de tri ('A' croissant, 'D' d�croissant)
**/
function Set_order($pCol,$pTyp){
        //v�rification tu type d'ordre
        $this->orderTyp='A'; // par d�faut Ascendant
        if (!is_null($pTyp) && ($pTyp=='A' or $pTyp=='D')){
                $this->orderTyp=$pTyp;
                }
        $this->orderColumn=$pCol;
}

/*function Set_formname($pFormName){
        $this->formname=$pFormName;
}

function Get_formname(){
        return $this->formname;
}
*/
function Set_repImage($strDir){
        if (is_dir($strDir))
                $this->repImg=$strDir;
}

function Set_columnType($pCol,$pType){
        if (array_key_exists($pCol,$this->column))
                $this->column[$pCol]->type=$pType;
}

function _get_checkedaction($value){
	if(in_array($value.";",$this->actioncolvalue['checked'])){
		return "checked";
	}
	else {
		return '';
	}

}

}
?>
