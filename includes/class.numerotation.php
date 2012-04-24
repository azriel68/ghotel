<?


class TNumerotation{
	/**
     * Constructor
     * @access protected
     */

	function TNumerotation(){
	
	    $this->id=0;
	
	    $this->prefixe="";
      $this->type="DOC";
      $this->postfixe="";
      $this->numero=1;
      $this->longueur=5;
      
      $this->dt_cre=time();
	    $this->dt_maj=time();
	
	}
	
	function load($db, $type){
	   $this->type=$type;
	
      $db->Execute("SELECT id,prefixe, numero, longueur, postfixe 
      ,dt_cre,dt_maj
      FROM hot_numerotation WHERE type='".$this->type."' AND id_hotel=".get_sess_hotel_id());
      if($db->Get_recordcount()>0){
      
        $db->Get_line();
      
        $this->id = $db->Get_field('id');
        $this->prefixe = $db->Get_field('prefixe');
        $this->numero = $db->Get_field('numero');
        $this->longueur = $db->Get_field('longueur');
        $this->postfixe = $db->Get_field('postfixe');
      
        $this->dt_cre = strtotime($db->Get_field('dt_cre'));
        $this->dt_maj = strtotime($db->Get_field('dt_maj'));
        
      }
      else{
        
        if($this->type=="FACTURE")$this->prefixe="FC";
        elseif($this->type=="DEVIS")$this->prefixe="DV";
        else $this->prefixe="";
        
      }
  
  }
	
	function get_numero($db, $type="FACTURE", $no_inc = false){
  
    //print "get_numero($db, $type, ".((double)$no_inc).")";
  
    $this->load($db, $type);
   
    $numero_doc = $this->prefixe.str_pad($this->numero,$this->longueur,"0",STR_PAD_LEFT).$this->postfixe;
  
    if(!$no_inc){
      $this->numero++;
      $this->save($db);
    }
    
    return $numero_doc;
  
  }
  
  function save(&$db){
  
      $query['id_hotel']=get_sess_hotel_id();
      $query['type']=$this->type;
      $query['prefixe']=$this->prefixe;
      $query['numero']=$this->numero;
      $query['longueur']=$this->longueur;
      $query['postfixe']=$this->postfixe;
      
      $query['dt_cre'] = date("Y-m-d H:i:s", $this->dt_cre);
      $query['dt_maj'] = date("Y-m-d H:i:s");
      
      if($this->id==0){
        $this->get_newid($db);
        $query['id'] = $this->id;
        $db->dbinsert('hot_numerotation', $query);
      }
      else{
        $query['id'] = $this->id;
        $db->dbupdate('hot_numerotation',$query, array(0=>'id'));
      }
      
      
  }
  
	function get_newid(&$db){

		$sql="SELECT max(id) as 'maxi' FROM hot_numerotation";
		$db->Execute($sql);
		$db->Get_line();
		$this->id = (double)$db->Get_field('maxi')+1;
	}
}

  

?>