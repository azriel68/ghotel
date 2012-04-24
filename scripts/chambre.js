function TChambre () {
	 this.show_tarif = function show_tarif (show) {
		
		if(show==true){
			/*document.getElementById('ligne_tarif_saison').style['display']='block';
			document.getElementById('ligne_tarif_global').style['display']='block';
			document.getElementById('ligne_tarif_saison_titre').style['display']='block';*/
			document.getElementById('tarif_chambre').style['display']='block';
		}
		else{
/*			document.getElementById('ligne_tarif_saison').style['display']='none';
			document.getElementById('ligne_tarif_global').style['display']='none';
			document.getElementById('ligne_tarif_saison_titre').style['display']='none';
			*/
			document.getElementById('tarif_chambre').style['display']='none';
		}
		
	}	
	this.show_new_categorie = function show_new_categorie(id_categorie) {
		if(id_categorie=="new"){
			document.getElementById('nouvelle_categorie').style['display']='block';
		}
		else{
			document.getElementById('nouvelle_categorie').style['display']='none';	
		}
	}
}

