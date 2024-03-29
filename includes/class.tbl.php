<?php
/*
 * Classe permettant la cr�ation de tableau, lien ou image
 * Alexis ALGOUD 25 juin 07 20:30:02
 */

Class TTbl
{
var $direct=TRUE; // les fonctions de tbl font des ECHO si TRUE

var $onmouseoverline;
var $onmouseoutline;
var $onclickline;
var $ondblclickline;

var $debug=false;

/*
* associe une function js avec un ev�nement
*/
function set_action($pAction,$pFunction)
{
switch ($pAction){
  case 'onmouseoverline' :{
          $this->onmouseoverline = $pFunction;
          break;
      }
  case 'onmouseoutline' :{
          $this->onmouseoutline = $pFunction;
          break;
      }
  case 'onclickline' :{
          $this->onclickline = $pFunction;
          break;
      }
  case 'ondblclickline' :{
          $this->ondblclickline = $pFunction;
          break;
      }
}
}

function draw_index($url,$datatable="",$idxchamps="",$post_char="",$title="Index",$opt='std'){
global $app;
	
	if($datatable!=""){
		$var =explode(";", $datatable);
		if((isset($var[1]))&&($var[1]!=""))$condition=$var[1];
		else $condition="";
		
		if($condition!="")$condition=" WHERE ".$condition;
		
		$db=new Tdb();
		$Tindexexist=array();
		$db->Execute("SELECT DISTINCT(".$idxchamps.") as 'indexfor' FROM ".$var[0]." $condition");
		while($db->Get_line()){
			//print "<b>INDEX : ".$db->Get_field('indexfor')."<br></b>";
			$Tindexexist[$db->Get_field('indexfor')]=true;
		}
		$db->close();
	}
	
	
	$nb_col=0;
	$old=$this->direct;
	$r='';
	$this->direct=false;
	
	
	$r.="<br>";
	$r.=$this->beg_cell();
	$r.="<br>";
	$r.=$this->beg_tbl('form');	
	$r.=$this->beg_line();

	
	if($post_char=="all")$r.=$this->cell("<span class=\"lien_inactif\"><font color=red>Tous</font></span>");
	else $r.=$this->cell($this->link('Tous',$url.'&charIndex=all','lien'));
	
	$nb_col++;
	
	if($opt=="rest60"){
		if($post_char=="rest60")$r.=$this->cell("<span class=\"lien_inactif\"><font color=red>(Fin valid � 60j)</font></span>");
		else $r.=$this->cell($this->link('(Fin valid � 60j)',$url.'&charIndex=rest60','lien'));
	
	}
	
	
	if($post_char=="!")$r.=$this->cell("<span class=\"lien_inactif\"><font color=red>#</font></span>");
	else if(($datatable=="")||(isset($Tindexexist['!']))||(isset($Tindexexist['0'])))$r.=$this->cell($this->link('#',$url.'&charIndex=other','lien'));
	else $r.=$this->cell("<span class=\"lien_inactif\">#</span>");
		$nb_col++;
		
		
			$l=ord("A");
         	for($i=$l;$i<$l+26;$i++){
				if($post_char==chr($i))$r.=$this->cell("<span class=\"lien_inactif\"><font color=red>".chr($i)."</font></span>");
				else if(($datatable=="")||(isset($Tindexexist[chr($i)])))$r.=$this->cell($this->link(chr($i),$url.'&charIndex='.chr($i),'lien'));	
				else $r.=$this->cell("<span class=\"lien_inactif\">".chr($i)."</span>");
					$nb_col++;	
			}
			

	$r.=$this->end_line();
	$r.=$this->end_tbl();
	$r.=$this->end_cell();

	if($title){
		$r=$this->end_line().$r;
		$r=$this->Cell($title,-1,'',$nb_col).$r;	
		$r=$this->beg_line('formheader0').$r;
	}

	$r=$this->beg_tbl('formcadre').$r;
	$r.=$this->end_tbl();
	
	$r.="<br>";
	
	$this->direct=$old;
	
	if ($this->direct) echo $r; else return $r;
	
}

function TTbl($pDirect=TRUE){
  $this->direct=$pDirect;
  $this->onmouseoverline     = "";
  $this->onmouseoutline      = "";
  $this->onclickline    = "";
  $this->ondblclickline = "";
}
/**
 * Alexis ALGOUD le 18/09/2004
 * Ajout du param�tre de l'id de la table utile au cadre
 **/

function beg_tbl($pClass,$pWidth=-1,$pColSpace=0,$pIdTable='',$align=""){
    $r = "\n<TABLE 0";
	
	if($this->debug)$r.=" border=1";
	
    if ($pIdTable<>'') {
            $r.=" id=\"$pIdTable\"";
    }
    if ($pClass !='') $r.= " CLASS='$pClass'";
    $r.= " CELLSPACING='$pColSpace'";
    if ($pWidth > -1) $r .= " WIDTH='$pWidth'";
	if($align!="")$r.=" align=\"$align\"";
	
    $r.= ">\n";
    if ($this->direct) echo $r; else return $r;
}


/**
 * Alexis ALGOUD le 18/09/2004
 * Ajout du param id
 **/

function beg_line($pClass='',$pIdLine=null){
  $r = "<TR";
  if ($pClass !='') $r.= " CLASS= '$pClass'";
  if ($pIdLine) {
      $r.=" id=\"$pIdLine\"";
  }
  if ($this->onmouseoverline !='') $r.= " onmouseover=\"$this->onmouseoverline\"";
  if ($this->onmouseoutline !='') $r.= " onmouseout=\"$this->onmouseoutline\"";
  if ($this->onclickline !='') $r.= " onclick=\"$this->onclickline\"";
  if ($this->ondblclickline !='') $r.= " ondblclick=\"$this->ondblclickline\"";
  $r.= ">\n";
  if ($this->direct) echo $r ;else return $r;
}

function end_line(){
  $r = "</TR>\n";
  if ($this->direct) echo $r ;else return $r;
}
// Modifi� par AA le 11/09/04
// ajout des alignement
// ajout du no wrap
// ajout dim
function Cell($pContent,$pWidth=-1,$pClass='',$pSpan='',$align="",$valign="",$nw=false,$h=0,$id_cell=""){
 if ($pContent == '')
   $pContent="&nbsp;";
 $r = "<TD";
 if ($pWidth > -1)
   $r .= " WIDTH='$pWidth'";
 if ($pClass !='')
   $r .= " CLASS= '$pClass'";
 if ($pSpan !='')
   $r .= " COLSPAN=$pSpan";
 if ($align){
        $r.=" align=$align";
 }
 if ($valign){
         $r.=" valign=$valign";
 }
 if ($id_cell) {
    $r.=" id=\"$id_cell\"";
 }
 if($nw){
         $r.=" nowrap";
 }
 if($h)$r.=" height=$h";
 
 $r .= "> $pContent </TD>\n";
 if ($this->direct) echo $r ;else return $r;

}
// modiofi� par AA 11/09/2004
// ajout des alignement
// ajout du no wrap
// ajout dim
function beg_Cell($pWidth=-1,$pClass='',$pSpan='',$align=null,$valign=null,$nw=false,$w=null,$h=null){
 $r = "<TD";
 if ($pWidth > -1)
   $r .= " WIDTH='$pWidth'";
 if ($pClass !='')
   $r .= " CLASS= '$pClass'";
 if ($pSpan !='')
   $r .= " COLSPAN=$pSpan";
 if ($align)$r.=" align=$align";
 if ($valign)$r.=" valign=$valign";
 if($w)$r.=" width=$w";
 if($h)$r.=" height=$h";

 $r .= ">\n";
 if ($this->direct) echo $r ;else return $r;
}

function end_Cell(){
 $r = "</TD>\n";
 if ($this->direct) echo $r ;else return $r;
}

function end_tbl(){
  $r = "</TABLE>\n";
  if ($this->direct) echo $r ;else return $r;
}
/*
      AA 11/09/2004
      Ajout � partir de class perso des image et liens
      Pr�vue pour une untilisation dans des cellule ou via un print
*/
function link($libelle,$link="#",$class="",$target="",$img="",$tit=""){
            $r="<a href=\"";
            $r.=$link;
            $r.="\"";
            if($class) $r.=" class=\"$class\"";
            if($target) $r.=" target=\"$target\"";
            if ($tit)$r.=" title=\"$tit\"";
            $r.=">";
            if ($img)$r.="<img src=\"../images/$img\" border=0> ";
            $r.=$libelle;
            $r.="</a>";

            return $r;
}

function img($src,$alt=null,$w=null,$h=null,$align=null) {
        $r="<img src=\"../images/$src\" border=0";
        if ($alt)$r.=" title=\"$alt\"";
        if ($w)$r.=" width=$w";
        if ($h)$r.=" height=$h";
		if($align)$r.=" align=$align";

        $r.=">";

        return $r;
}

}
?>