<?

class TModel{

  function TModel(){
		$this->id=0;
		$this->id_hotel=0;
		$this->libelle="";
		$this->src="";
		$this->langue="FR";
		$this->type="RESA";
	
		$this->dt_cre=time();
		$this->dt_maj=time();
		
		$this->TType=array(
      "FACTURE"=>"Facture"
      ,"RESA"=>"Réservation"
      ,"TAXE_SEJOUR"=>"Taxe de séjour mensuelle"
    );
    $this->TLangue=_get_langue();
  }
  
  function load_defaut($id){
    
        $this->id=$id;
  			
  			switch ($id) {
        case -101;
         $this->libelle="Confirmation de réservation, modèle par défaut";
  			 $this->src="lettre_reservation.txt";
  			 $this->type="RESA";
         
         break;
        case -102;
         $this->libelle="Facture, modèle par défaut";
  			 $this->src="facture.txt";
  			 $this->type="FACTURE";
         
         break;
        case -103;
         $this->libelle="Taxe de séjour mensuelle, modèle par défaut";
  			 $this->src="taxe_sejour.txt";
  			 $this->type="TAXE_SEJOUR";
         
         break;
         default:
         	
         	break;
         }
  			
  			
        $this->langue="FR";
  			
  
  }
  function get_src() {
  
    if($this->id<0) return DIR_MODEL.$this->src;
    else  return DIR_MODEL_USER.$this->src;
    
  }
	function load(&$db,$id){

    if($id<0){
    
      $this->load_defaut($id);
    }
    else{
    
    
      $db->Execute("SELECT id,id_hotel,libelle,src,langue,type,dt_cre,dt_maj
  					  FROM hot_model WHERE id=$id");
  
  		if($db->Get_recordCount()>0){
  			$db->Get_line();
  
  			$this->id=$id;
  			$this->id_hotel=$db->Get_field('id_hotel');
  			test_hotel_id($this->id_hotel);
  			
  			$this->libelle=$db->Get_field('libelle');
  			$this->src=$db->Get_field('src');
  			$this->langue=$db->Get_field('langue');
  			$this->type=$db->Get_field('type');
  			
  
  			$this->dt_cre=strtotime($db->Get_field('dt_cre'));
  			$this->dt_maj=strtotime($db->Get_field('dt_maj'));
  		}
		
		}
	}

  function make_file_model(){
  
    $file = get_sess_hotel_id()."-".md5(time())."-".rand(10,1000).".txt";
    if(is_file(DIR_MODEL_USER.$file)){
      return $this->make_file_model();   
    }
    else{
      return $file;
    }
  
  }

	function save(&$db){
    
    if($this->id>=0){
    
    	$query['id_hotel']=$this->id_hotel;
			$query['libelle']=$this->libelle;
			$query['src']=$this->src;
			$query['langue']=$this->langue;
			$query['type']=$this->type;
			
			$query['dt_cre']=date("Y-m-d H:i:s",$this->dt_cre);
			$query['dt_maj']=date("Y-m-d H:i:s");

			$key[0]='id';

			if($this->id==0){
				$this->get_newid($db);
				$query['id']=$this->id;
				$db->dbinsert('hot_model',$query);
			}
			else {
				$query['id']=$this->id;
				$db->dbupdate('hot_model',$query,$key);
			}

    }
		

	}

	function delete(&$db){
		if($this->id!=0){
			$db->dbdelete('hot_model',array("id"=>$this->id),array(0=>'id'));
		}
	}

	function get_newid(&$db){

		$sql="SELECT max(id) as 'maxi' FROM hot_model";
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

class TPdf extends FPDF {
 	function Header()
	{
	
	
    $this->SetFont('Arial','',12);	
	}

	function Footer()
	{
	  
	}
}



class TBlocPDFM {

  function TBlocPDFM() {
    
    $this->padding_x=20;
    $this->padding_y=20;
    
    $this->h=5;
    
    $this->x=0;
    $this->y=0;
    
    $this->border=0;
    $this->align="J";
    $this->fill=0;
    
    
    $this->max_width = 170;
    $this->w=$this->max_width;
    
    $this->type="TEXTE";
  
    $this->value="";
  
    $this->TTrans=array();
    
    $this->Tab=array();
   
  }
  function _trans($s){
  
    return strtr($s , $this->TTrans);
  }
  function add_var($name, $value=""){
    
    if(is_array($name)){
      
      foreach($name as $k=>$v){
        $this->add_var($k, $v);
      }
      
    }
    else{
      $this->TTrans['#'.$name.'#']=$value;
    }
    
  
  }
  function _get_bloc_date(){
  
    
      return $this->Tab['ville'].", ".(isset($this->Tab['date'])?$this->Tab['date']:date("d/m/Y") );
  
  }
  function _write_tab_line(&$pdf, &$decalage_x, &$decalage_y, &$row){
  	
    $next_height=0;
    foreach ($row as $key=>$val) {
    	
    	 //print $val;
    	 @list($value, $width, $align, $border, $fill, $color, $fill_color, $height)
                 = explode("||", $val);
    	
    	 if($align=="")$align="L";
    	 if($border=="")$border=0;
    	 if($fill=="")$fill=0;
    	 
    	 
    	if($fill_color==""){
          $pdf->SetFillColor(255);
       }
       else {
          $pdf->SetFillColor((int)$fill_color);
       }
       if($color==""){
          $pdf->SetTextColor(0);
       }
       else {
          $pdf->SetTextColor((int)$color);
       }
       if($width=="")$width=30;

       if($height=="") $height=5;

	$texte =  $this->_trans($value);
	$w_texte = $pdf->GetStringWidth($texte);
	$next_height_tmp = ceil($w_texte/$width) * $height;	
	if($next_height<$next_height_tmp)$next_height = $next_height_tmp;
    	
       $pdf->SetXY($this->padding_x+$decalage_x, $this->padding_y+$decalage_y);
       $pdf->MultiCell($width, $height,$texte, $border, $align, $fill);
      
       $decalage_x+=$width;
    }
   // print $next_height.'<br>';
    $this->h=$next_height;
    //$this->h = $height;
  
  }
  function _write_tab(&$pdf){
  
    $pdf->SetLineWidth(0.1);
    
    $decalage_y = $this->y;
    
    $headers=array();
    /*
    $headers = explode("|",$this->TabParam);
    foreach($headers as $name=>$value){
      
    }*/
  
    $nb=count($this->Tab);
    for($i=0;$i<$nb;$i++){
      $decalage_x = $this->x;
      
      $row = & $this->Tab[$i];
      $this->_write_tab_line($pdf, $decalage_x, $decalage_y, $row, $headers);
      $decalage_y+=$this->h;
    }
  
    return $decalage_x.",".$decalage_y;
  
  }
  function _get_bloc_client(){
    
    $r="";
      
    $r.=$this->Tab['nom']."\n".$this->Tab['adresse'];
    
    return $r;
    
  }
  function _get_bloc_signature(){
    
    $r="";
      
    $r.=$this->Tab['signature'];
    
    return $r;
    
  }
  function _get_bloc_entete(&$pdf){
  //print_r($this->Tab); exit();
    $pdf->SetLeftMargin($this->padding_x);
  
    $telfax="";
    if($this->Tab['tel'])$telfax.="Tél : ".$this->Tab['tel'];
    if($this->Tab['fax']){
      if($telfax!="")$telfax.=" - ";
      $telfax.="Fax : ".$this->Tab['fax'];
    }
    
 	  $pdf->SetXY($this->padding_x+$this->x, $this->padding_y+$this->y);
    
    $pdf->SetFont("arial","b","26");
    
    $pdf->Write($this->h*1.7, $this->_trans($this->Tab['nom'])."\n");
    $pdf->SetFont("arial","","12");
    
    $pdf->Write($this->h*.75, $this->_trans($this->Tab['nom_gestion'])."\n");
    $pdf->Write($this->h*.75, $this->_trans($this->Tab['adresse']." - ".$this->Tab['cp']." ".$this->Tab['ville'])."\n");
    $pdf->Write($this->h*.75, $this->_trans($this->Tab['commercial'])."\n");
    $pdf->Write($this->h*.75, $this->_trans($telfax));
           
    $pdf->SetFont("arial","","12");
    
    $pdf->SetLineWidth(0.1);
    $x = $this->padding_x ; $y = $this->padding_y+$this->y+$this->h*4.5+1; 
    $pdf->line($x,$y, $x+130, $y);
    
    $pdf->SetLineWidth(0.5);
    $pdf->line($x,$y+2, $x+130, $y+2);
    
    
  }
  function _get_bloc_pied(&$pdf){
  
    $pdf->SetLeftMargin($this->padding_x);
  
	$pdf->SetXY($this->padding_x+$this->x, $this->padding_y+$this->y);
    
    $nom=(isset($this->Tab['nom_gestion']) && $this->Tab['nom_gestion']!='')?$this->Tab['nom_gestion']:$this->Tab['nom']; 
    
    $txt = $nom." - ";
    $txt .= $this->Tab['forme_juridique']." au capital de ".$this->Tab['capital']." € ";
    $txt .= "RCS ".$this->Tab['rcs']." Siret ".$this->Tab['siret']." APE ".$this->Tab['ape'];

    $pdf->SetFont("arial","","8");
    $pdf->line($this->x+20,$this->y+20, $this->x+190, $this->y+20);
    $pdf->Write($this->h, $this->_trans($txt));
  }
  function write(&$pdf){
    
    if($this->x+$this->w>$this->max_width)$this->w = $this->max_width-$this->x;
    
    switch ($this->type) {
    default:
      case "TABLEAU":
        return $this->_write_tab($pdf);
    
        break;
    	case "PAGE":
    	 $pdf->AddPage();
    	 break;
    	
    	case 'ENTETE':
    	 $this->_get_bloc_entete($pdf);
       
    	
    	 break;
    	case 'PIED_DE_PAGE':
    	 $this->_get_bloc_pied($pdf);
       
    	
    	 break;
    	case 'DATE':
    	 $pdf->SetXY($this->padding_x+$this->x, $this->padding_y+$this->y);
       $pdf->MultiCell($this->w, $this->h, $this->_trans($this->_get_bloc_date()), $this->border, $this->align, $this->fill);
       
    	
    	 break;
    	case 'CLIENT':
    	 $pdf->SetXY($this->padding_x+$this->x, $this->padding_y+$this->y);
       $pdf->MultiCell($this->w, $this->h, $this->_trans($this->_get_bloc_client()), $this->border, $this->align, $this->fill);
       break;
    	case 'SIGNATURE':
    	 $pdf->SetXY($this->padding_x+$this->x, $this->padding_y+$this->y);
       $pdf->MultiCell($this->w, $this->h, $this->_trans($this->_get_bloc_signature()), $this->border, $this->align, $this->fill);
      
    	 break;
    	default:
    	   
         if($this->value!=""){
           $pdf->SetXY($this->padding_x+$this->x, $this->padding_y+$this->y);
      	   $pdf->MultiCell($this->w, $this->h, $this->_trans($this->value), $this->border, $this->align, $this->fill);
         }
    	   
    }
    
  
  }

}

class TPDFMaker{

  function TPDFMaker(){
      
      $this->type="";
      $this->model="";
  
      $this->TBloc=array();
      
      
      $this->TType=array(
        "RESERVATION"=>"Réservations"
        ,"FACTURE"=>"Facture"
        ,"DEVIS"=>"Devis"
      );
      
      
  }
  
  function load ($db, $id){
  
  
  }
  
  function set_best_model_for(&$db, $langue, $type){
  
    $r=new TRequete;
    $id = $r->get_model_for($db, get_sess_hotel_id(), $langue, $type);
    $m = new TModel;
    $m->load($db , $id);
    
    $this->model = $m->get_src();
  
  }
  
  function parse_file(&$Tab){
  
    $body = file_get_contents($this->model);
  
    $i = 0;
  
    $this->TBloc=array();
    while($body!="" && $i<300){
    
      $this->TBloc[$i]=new TBlocPDFM;
      
      $body = $this->_get_entete($body, $this->TBloc[$i]);
  //  print $body."<br />";
      $bloc = & $this->TBloc[$i];
      
      switch($bloc->type){
        case "TEXTE":
          $body = $this->_get_value($body, $bloc);
          break;
      
        case "TABLEAU":
          $bloc->Tab = $Tab[$bloc->nom_tab];
          break;
        case "CLIENT":
          $bloc->Tab = $Tab['client'];
          break;
        case "ENTETE":
        case "PIED_DE_PAGE":
        case "SIGNATURE":
        case "DATE":
          $bloc->Tab = $Tab['hotel'];
          break;
      }
    
    
      $i++;
    }
    
    /*
  print "<pre>";
    print_r($this->TBloc);
  print "</pre>";
  */
  
  }
  
  function _get_value($body, &$bloc){
  
    $pos = strpos($body,"[/".$bloc->type."]");
    if($pos===false){
      return $body;
    }
    else{
      $bloc->value = substr($body, 0, $pos);
      
      return substr($body, $pos+1);
    }
    
  } 
  
  function _get_entete($body, & $bloc){
  
      $pos = strpos($body, "[")+1;
      $pos2 = strpos($body,"]", $pos)-1;
  
      $len = $pos2 - $pos +1;
      $tag = substr($body, $pos, $len);
  
      if(trim($tag!="")){
        //$entete = $tag;
        if(strpos($tag, ",")!==false){
          @list($type, $x, $y , $w, $align) = explode(",", $tag); 
          
          $bloc->type = $type;
          $bloc->x = (double)$x;
          $bloc->y = (double)$y;
          
          if($type=="TABLEAU"){
            $bloc->nom_tab = $w;
          }
          else if($w!="" && $w!="défaut"){
            $bloc->w = (double)$w;
          }
          if($align!=""){
            $bloc->align = $align;
          }
          
          
        }
        else{
          $bloc->type = $tab;
        }
      }
  
      
  
  
      return substr($body, $pos2+2);
  }
  
  function write(&$pdf, $trans=array()){
  
    $pdf->addPage();
    
    $nb=count($this->TBloc);
    for($i=0;$i<$nb;$i++){
      
      $bloc = & $this->TBloc[$i];
      
      $bloc->add_var($trans);
      $bloc->write($pdf);
      
    }
    
   
		
  }
}


?>
