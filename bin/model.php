<?
	require("../includes/inc.php");
	require("../includes/class.pdfmaker.php");
	is_logged();

 	if(!is_admin()) {
		entete("Accès refusé");
		erreur ("Vous n'êtes pas autorisé à afficher cette page");
		exit();
	}

	entete("Gestion de modèles de document",'online');
	
	
	if(is_hotel_select() && is_admin()){
		menu_admin();
	}

	if(isset($_REQUEST['action'])){
		$action=$_REQUEST['action'];
	}
	else{
		$action='LIST';
	}

	$sess_name=isset($_REQUEST['sess_name'])?$_REQUEST['sess_name']:gen_sess_name("model");
	
	$db=new Tdb;

	switch($action){
		case 'DELETE':
			$m=new TModel;
			$m->load($db, $_REQUEST['p1']);
      $m->delete($db);

			liste($db);

			break;
	
	  case 'SAVE':
	   valider($db);
	   liste($db);
	   break;
		case 'NEW':
		
		  
		  _ajout_model($db);
		
      liste($db);
			break;
		case 'LIST':
			liste($db);
			break;
		default:
			erreur("inconnu : ".$action);
	} // switch
	$db->close();

	pied_de_page();

function _ajout_model(&$db){

  if(isset($_FILES['f_model']) && $_FILES['f_model']['name']!="") {
    //print_r($_FILES['f_model']);
    $f = & $_FILES['f_model'];
    $m=new TModel;
    $m->id_hotel=get_sess_hotel_id();
    $m->libelle = $f['name'];
    
    $ftmp = $m->make_file_model();
    
    copy($f['tmp_name'], DIR_MODEL_USER.$ftmp);
  
    $m->src = $ftmp;
    
    $m->save($db);
  
  }

}

function valider(&$db){

  $TModel = & $_POST['TModel'];
  foreach($TModel as $k=>$row){
    //print_r($row);
    if($row['id']>0){
    
      $m=new TModel;
      $m->load($db, $row['id']);
      
      $m->libelle = $row['libelle'];
      $m->langue = $row['langue'];
      $m->type = $row['type'];
      
      $m->save($db);
    }
    
    
  
  }



}



function liste(&$db){
	$listname='dblist1';
	$lst=new TListView($listname);
	$formname="form_model";
	
?>
<script language="javascript">

function downmodel (id_model) {
	
	document.location.href="../scripts/down_model.php?id_model="+id_model+"&id_hotel=<?=get_sess_hotel_id()?>"
	
}
function delmodel (id_model) {
	
	document.forms['<?=$formname?>'].elements['action'].value="DELETE";
  document.forms['<?=$formname?>'].elements['p1'].value=id_model;
  document.forms['<?=$formname?>'].submit();
}
function viewmodel(id_model){
  
  aff_div("../scripts/print_model.php?id_model="+id_model);

}
function aff_div (url){

  document.getElementById('div_print').style['display']="block";
  document.getElementById('iframe_print').src=url;

}
function add_new(){

  document.forms['<?=$formname?>'].submit();
}
function valid(){
  document.forms['<?=$formname?>'].elements['action'].value="SAVE";
  
  document.forms['<?=$formname?>'].submit();

}
</script><?
	//titre du référentiel
	echo "<h1>Liste des modèles de document disponible</h1>";

	$t=new TTbl;

  $form=new TForm("",$formname,"POST",true);
  echo $form->hidden("action", "NEW");
  echo $form->hidden("p1", "");
  
	echo "<p align=\"center\">"
	.$form->fichier("Votre fichier modèle","f_model","","")
  .$t->link("Ajouter un modèle","javascript:add_new()","button")
  ."</p>";

  $r=new TRequete;
	$TModel = $r->get_model($db, get_sess_hotel_id());
	
	 $nb=count($TModel);
	
	 echo "<div id=\"div_print\" style=\"display:none;\" align=\"center\">
    <iframe id=\"iframe_print\" width=\"800\" height=\"500\"></iframe>
    </div>";
	
	 $t->beg_tbl('formcadre',800,0,'','center');
	 $t->beg_line("listheader");
	 $t->Cell("Libelle");
	 $t->Cell("Langue de référence");
	 $t->Cell("Sur document");
	 $t->Cell("S.");
	 $t->end_line();
	

	 $class="L1";
   for ($i=0; $i<$nb; $i++) {
    	
    	$m=new TModel;
    	$m->load($db, $TModel[$i]);
    	
    	$t->beg_line($class);
    	
    		
      if($m->id<0){
        $t->Cell($t->link($m->libelle,"javascript:downmodel(".$m->id.")"));
        $t->Cell($m->TLangue[$m->langue]);
      	$t->Cell($m->TType[$m->type]);
      	$t->Cell("");
      	
      }
    	else{
    	  $t->Cell(
    	  $form->hidden("TModel[$i][id]", $m->id)
    	  .$form->texte("","TModel[$i][libelle]",$m->libelle,60,255)
        .$t->link($t->img("new.gif","Voir le modèle"),"javascript:viewmodel(".$m->id.")")
        .$t->link($t->img("b_tblexport.png","Télécharger le modèle"),"javascript:downmodel(".$m->id.")")
        );
        
        $t->Cell(
          $form->combo("", "TModel[$i][langue]", $m->TLangue, $m->langue)
        );
      	$t->Cell(
          $form->combo("", "TModel[$i][type]", $m->TType, $m->type)
        );
      	$t->Cell(
          $t->link($t->img("b_drop.png","Supprimer le modèle"),"javascript:delmodel(".$m->id.")")
        );
      
      }
      $t->end_line();
    	
    	$class=($class=="L1")?"L2":"L1";
   }
   
   $t->end_tbl();

  echo "<p align=\"center\">"
  	 .$t->link("Valider","javascript:valid()","button_valid")
    ."</p>";


  echo $form->end_form();
}
?>
