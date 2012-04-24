<?php
  
/** 
 * gère les tags de template
 *
 **/
class TTag{
	function TTag(){
		$this->id = 0;
		$this->code_page='';
		$this->tag='';
		$this->gabari='';
		$this->categorie='';
		$this->type='';
		$this->zone='';
		$this->id_edito=0;
		
		$this->dt_cre=time();	
		$this->dt_maj=time();	
	}
	function load(&$db,$id){
		$db->Execute("SELECT code_page, tag, gabari, categorie, type, zone, id_edito, dt_cre, dt_maj
		FROM map_template WHERE id=".$id);
		$db->Get_line();
		
		$this->id=$id;
		$this->code_page=$db->Get_field('code_page');
		$this->tag=$db->Get_field('tag');
		$this->gabari=$db->Get_field('gabari');
		$this->categorie=$db->Get_field('categorie');
		$this->type=$db->Get_field('type');
		$this->zone=$db->Get_field('zone');
		$this->id_edito=$db->Get_field('id_edito');
		$this->dt_cre=strtotime($db->Get_field('dt_cre'));
		$this->dt_maj=strtotime($db->Get_field('dt_maj'));
	}
	function save(&$db){
			$query['code_page']=$this->code_page;
			$query['tag']=$this->tag;
			$query['gabari']=$this->gabari;
			$query['categorie']=$this->categorie;
			$query['type']=$this->type;
			$query['zone']=$this->zone;
			$query['id_edito']=$this->id_edito;
			
			$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
			$query['dt_maj']=date("Y-m-d H:i:s");
			
			$key[0]='id';
			
			if($this->id==0){
				$this->get_newid($db);
				$query['id']=$this->id;
				$db->dbinsert('map_template',$query);
			}
			else {
				$query['id']=$this->id;
				$db->dbupdate('map_template',$query,$key);		
			}
	}
	function delete(&$db){
		if($this->id!=0){
			$db->dbdelete('map_template',array("id"=>$this->id),array(0=>'id'));
		}
	}
	function get_newid(&$db){
		$sql="SELECT max(id) as 'maxi' FROM map_template";
		$db->Execute($sql);
		$db->Get_line();
		$this->id = (double)$db->Get_field('maxi')+1;
	}
	function get_dtcre(){
		return date("d/m/Y",$this->dt_cre);
	}
	function get_dtmaj(){
		return date("d/m/Y",$this->dt_maj);
	}
	function set_dtcre($date){
		list($d,$m,$y) = explode("/",$date);
		$this->dt_cre = mktime(0,0,0,$m,$d,$y);
	}
	function set_dtmaj($date){
		list($d,$m,$y) = explode("/",$date);
		$this->dt_maj = mktime(0,0,0,$m,$d,$y);
	}
} 
 
/** 
 * Gère les templates et leur publication
 *
 **/
class TTemplate{

	function TTemplate($db, $code_page){
		
		$this->fichier_source=""; 	// template
		$this->fichier_edition="";	// fichier pour édition
		$this->fichier_ecriture="";	// fichier pour écriture
		
		$this->code_page = $code_page;
		
		$this->TGabari=array_merge(array(0=>"--- rien ---"),load_gabari($db));
		$this->TCategorie=load_categorie($db);
		$this->TType=load_position($db);
		$this->TZone=load_zone($db);
		$this->TEdito=array(""=>"Aucun article");
		
		$this->TTag=array();
		$this->load_tag($db);
		
		
	}
	
	function load_tag(&$db){
	/**
	 * Charge les tag préchargés 
	 * 24/11/2006 14:36:40 Alexis ALGOUD
	 **/
		$db->Execute("SELECT id FROM map_template WHERE code_page='".$this->code_page."'");
		$Tab=array();
		while($db->Get_line()){
			$Tab[]=$db->Get_field('id');
		}
		
		$nb=count($Tab);
		for($i = 0; $i < $nb; $i++){
			
			$this->TTag[$i]=new TTag;
			$this->TTag[$i]->load($db, $Tab[$i]);
					
		} // for	
		
	}
	
	function save_tag(&$db){
	
		$nb=count($this->TTag);
		for($i = 0; $i < $nb; $i++){
			
			$this->TTag[$i]->code_page = $this->code_page;
			$this->TTag[$i]->save($db);
					
		} // for	
	
	}
	
	function analyse_gab($mode){
	/**
	 * Analyse, trouve les tag #[TPL]_____# et les remplace par leur équivalent selon
	 * mode = EDIT	;	WRITE 
	 * 22/11/2006 12:11:43 Alexis ALGOUD
	 **/
		//$this->source_file
		
		$TTag = $this->get_tab_tag();
		//print_r($TTag);
		
		$trans=array();
		$nb=count($TTag);
		for($i = 0; $i < $nb; $i++){
			
			$tag=$TTag[$i];
			
			usleep(100000);
			
			if($mode=="EDIT"){
				$trans['#[TPL]'.$tag.'[/TPL]#']=$this->give_selected_tab($tag,$mode);
			}
			elseif($mode=="WRITE"){
				$trans['#[TPL]'.$tag.'[/TPL]#']=$this->give_selected_tab($tag,$mode);
			}
			
		} // for
		
		$trans['#RENDEZ-VOUS#']='<? require("'.DIR_HTTP.'rendez-vous.php"); ?>';
		$trans['#NEWS#']='<? require("'.DIR_HTTP.'news-acc.php"); ?>';
		$trans['#LISTE_20_DOSSIER#']='<? require("'.DIR_HTTP.'20-dernier-dossier.php"); ?>';
		
		
		$this->fichier_edition=strtr($this->fichier_source,$trans);
		
	}
	function get_nulltag_prev(){
		$r="";
	
		$r.="<div style=\"border:3px solid #c2cdc8; background-color:#8ecdaf; width:100%; height:50px;text-align:center;font-family: Verdana, Arial, Helvetica, sans-serif;font-size: 10px;font-weight: bold;\">";
		$r.="Aucun article/gabarit sélectionné";
		$r.="</div>";
	
		return $r;
	}
	
	function get_info_tag($tag){
		
		$Tab=array(0=>null,1=>null,2=>null,3=>null,4=>-1);
		
		$nb=count($this->TTag);
		for($i = 0; $i < $nb; $i++){
			
			if(!strcmp($this->TTag[$i]->tag,$tag)){
				$Tab[0]=$this->TTag[$i]->gabari;
				$Tab[1]=$this->TTag[$i]->zone;
				$Tab[2]=$this->TTag[$i]->categorie;
				$Tab[3]=$this->TTag[$i]->type;
				$Tab[4]=$this->TTag[$i]->id_edito;
				
				$Tab['gabari']=$this->TTag[$i]->gabari;
				$Tab['zone']=$this->TTag[$i]->zone;
				$Tab['categorie']=$this->TTag[$i]->categorie;
				$Tab['type']=$this->TTag[$i]->type;
				$Tab['id_edito']=$this->TTag[$i]->id_edito;
				
			
				break;
			}
			

		} // for	

		return $Tab;
	}
	
	function give_selected_tab($tag, $mode){
		$r="";
		
		list($gabari, $zone, $categorie, $type, $id_edito)=$this->get_info_tag($tag);
		
		if($mode=="WRITE"){
			$r.=file_get_contents(DIR_SCRIPTS."get_preview_tag.php?gabarit=".$gabari."&id_edito=".$id_edito."&zone=".$zone."&cat=".$categorie."&SHOW=1&NO_NULL=1");
		}
		else{
		
		
			//$r.="<div id=\"zone_".$tag."\" onMouseOver=\"set_view('tools_".$tag."','zone_".$tag."',1);\"  onMouseOut=\"set_view('tools_".$tag."','zone_".$tag."',0);\" style=\"z-index:3;\">";
			$r.="<div id=\"zone_".$tag."\" onMouseOver=\"set_view('tools_aff_".$tag."','zone_".$tag."',1);\"  onMouseOut=\"set_view('tools_aff_".$tag."','zone_".$tag."',0);\" style=\"z-index:3;\">";
			
			$r.="<div id=\"tools_aff_".$tag."\" style=\"visibility:hidden;position:absolute;z-index:1;\" class=\"tools_zone\">";
			$r.="<a href=\"javascript:set_view('tools_".$tag."','zone_".$tag."',1);\" class=\"lien_zone\"><img src=\"../images/edit.gif\" align=\"absmiddle\" border=\"0\"> Modifier cette zone</a>";
			$r.="</div>";
			
			$r.="<div id=\"tools_".$tag."\" style=\"visibility:hidden;position:absolute;z-index:2;\" class=\"tools_zone\">";
			$r.="<table style=\"font-family: Verdana, Arial, Helvetica, sans-serif;	font-size: 10px;font-weight: bold;\">";
			$form=new TForm;
			$r.="<tr><td>Format d'affichage</td><td>".$form->combo('', 'TTag['.$tag.'][select_gabari]', $this->TGabari, $gabari,1,"refresh_preview('".$tag."',-1)"," id=\"select_gabari_".$tag."\"").'</td></tr>';
			$r.="<tr><td>Zone</td><td>".$form->combo('', 'TTag['.$tag.'][select_zone]', $this->TZone, $zone,1,"refresh_article('".$tag."',-1);refresh_preview('".$tag."',-1)"," id=\"select_zone_".$tag."\"").'</td></tr>';
			$r.="<tr><td>Catégorie</td><td>".$form->combo('', 'TTag['.$tag.'][select_categorie]', $this->TCategorie, $categorie,1,"refresh_article('".$tag."',-1);refresh_preview('".$tag."',-1)"," id=\"select_categorie_".$tag."\"").'</td></tr>';
			//$r.=$form->combo('', 'TTag['.$tag.'][select_type]', $this->TType, $type,1,"refresh_article('".$tag."')"," id=\"select_type_".$tag."\"").'<br>';
			$r.="<tr><td>Article</td><td><div id=\"div_select_edito_".$tag."\"></div></td></tr>";
			$r.="</table>";
			$r.="<a href=\"javascript:set_view('tools_".$tag."','zone_".$tag."',0);\" class=\"lien_zone\"><img src=\"../images/cancel.gif\" align=\"absmiddle\" border=\"0\"> Fermer</a>";
			$r.="</div>";
			
			$r.="<div id=\"preview_".$tag."\">";
			//$r.=$this->get_nulltag_prev();
			$r.=file_get_contents(DIR_SCRIPTS."get_preview_tag.php?gabarit=".$gabari."&id_edito=".$id_edito."&zone=".$zone."&cat=".$categorie."&SHOW=1");
			$r.="</div>";
			$r.="</div>";
			$r.='<script language="javascript">
			refresh_article(\''.$tag.'\', '.$id_edito.');
			</script>';
		}
		
		return $r;
	}
	function maj_tag($tag, $gab, $cat, $type, $zone, $id_edito){
	/**
	 * Met à jour ou ajoute un tag si neccesaire 
	 * 24/11/2006 15:19:31 Alexis ALGOUD
	 **/
	 	$flag=false;
		//print "$tag, $gab, $cat, $type, $id_edito<br>";
		$nb=count($this->TTag);
		// print_r($this->TTag)
		// print "Mise à jour ($nb) $tag ...";
		
		for($i = 0; $i < $nb; $i++){
		//	print $this->TTag[$i]->tag.",$tag)<br>";
			if(!strcmp($this->TTag[$i]->tag,$tag)){
		//	print "trouvé en $i<br>";
				$this->TTag[$i]->tag=$tag;		
				$this->TTag[$i]->gabari=$gab;		
				$this->TTag[$i]->categorie=$cat;		
				$this->TTag[$i]->type=$type;		
				$this->TTag[$i]->zone=$zone;		
				$this->TTag[$i]->id_edito=$id_edito;		
				
				$flag=true;	
				
				break;
			}
		}
		
		if(!$flag){
		//	print "Non trouvé<br>";
				$i=count($this->TTag);
				$this->TTag[$i]=new TTag;
				$this->TTag[$i]->tag=$tag;		
				$this->TTag[$i]->gabari=$gab;		
				$this->TTag[$i]->categorie=$cat;		
				$this->TTag[$i]->type=$type;		
				$this->TTag[$i]->zone=$zone;		
				$this->TTag[$i]->id_edito=$id_edito;		
		}
		
		
	}
	function get_tab_tag(){
	/**
	 * Récupère le tableau des tags 
	 * 22/11/2006 12:26:02 Alexis ALGOUD
	 **/
		$cpt=0;
		$f_end=false;
	
		$last_pos=0;
		
		$separator1="#[TPL]";
		$l_sep1 = strlen($separator1);
		$separator2="[/TPL]#";
		
		$Tab=array();
		
		while(!$f_end && $cpt<50){
					
			$pos = strpos($this->fichier_source,$separator1,$last_pos);
		
			if($pos===false){
				//c'est terminé
				$f_end=true;
			}
			else{
				$pos2 = strpos($this->fichier_source,$separator2,$pos);
				
				$Tab[] = substr($this->fichier_source,$pos+$l_sep1,($pos2-($pos+$l_sep1)));
					
				$last_pos=$pos2;
			}
			$cpt++;			
		}
	
		return $Tab;
	}
	
	function charge_fichier($src, $mode="EDIT"){
		$this->fichier_source = file_get_contents("../tpl/".$src); // on charge le fichier source
	
		// Puis on l'analyse
	
		$this->analyse_gab($mode);
	
		return $this->fichier_edition;
	}
	
}
  
?>