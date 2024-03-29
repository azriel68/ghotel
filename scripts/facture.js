function calcul_montant_ligne (formname, idligne) {
    var qte = document.forms[formname].elements['TLigne['+idligne+'][quantite]'].value;
    var prix_u = document.forms[formname].elements['TLigne['+idligne+'][prix_u]'].value;
    
    qte = _to_float(qte);
    prix_u = _to_float(prix_u);
    
    if (isNaN(qte)) {
        qte = _to_float('0');
    }

    var montant = qte * prix_u; //_to_float(String(qte * prix_u));
    document.forms[formname].elements['TLigne['+idligne+'][montant]'].value = _to_string(montant);
    calcul_total_ttc(formname);
}


// MKO

function calcul_total_ttc (formname) {

    i = 0;
    var montant = 0;
    var ht = 0;
    var tva = 0;

    while(document.getElementById('TLigne_montant_'+i)){
        m = document.getElementById('TLigne_montant_'+i).value;
        t = document.forms[formname].elements['TLigne['+i+'][tva]'].value;

        montant = montant + _to_float(m);
		//alert(montant+"+"+_to_float(m));
        tva_ligne = _to_float(t) * _to_float(m) / 100;

        tva = tva + tva_ligne;

        i++;
    }
//	alert('TTC : '+montant+' - TVA : '+tva+' - HT : '+(montant - tva));
    document.forms[formname].elements['total_ttc'].value = _to_string(montant);
    document.forms[formname].elements['total_tva'].value = _to_string(tva);
    document.forms[formname].elements['total_ht'].value = _to_string(montant - tva);
}

// MKO






function calcul_total_ttc_old (formname) {
    
    i = 0;
    var montant = 0;
    while(document.getElementById('TLigne_montant_'+i)){
        m = document.getElementById('TLigne_montant_'+i).value;
        montant = montant + _to_float(m);
        i++;
        
    }
    /*
    while (document.forms[formname].elements['TLigne['+i+'][montant]']) {
        m = document.forms[formname].elements['TLigne['+i+'][montant]'].value;
        montant = montant + _to_float(m);
        i++;
    }
*/
    document.forms[formname].elements['total_ttc'].value = _to_string(montant);
    calcul_tva_total_ht(formname);
}

function calcul_tva_total_ht_old (formname) {
    var taux_tva = document.forms[formname].elements['taux_tva'].value;
    var total_ttc = document.forms[formname].elements['total_ttc'].value;
    var total_negocie = document.forms[formname].elements['total_negoce'].value;
    
    taux_tva = _to_float(taux_tva);
    total_ttc = _to_float(total_ttc);
    total_negocie = _to_float(total_negocie);

    if (isNaN(total_negocie) || total_negocie == 0) {
        var total_tva = _to_float(String(total_ttc * taux_tva/100));
        var total_ht = _to_float(String(total_ttc - total_tva));
    } else {
        var total_tva = _to_float(String(total_negocie * taux_tva/100));
        var total_ht = _to_float(String(total_negocie - total_tva));
    }

    document.forms[formname].elements['total_tva'].value = _to_string(total_tva);
    document.forms[formname].elements['total_ht'].value = _to_string(total_ht);
}



function calcul_total_remise (formname, montant_negocie) {
//span_totalremise
    var remise = document.forms[formname].elements['remise'].value;
    var total_ttc = document.forms[formname].elements['total_ttc'].value;
    
    remise = _to_float(remise);
    total_ttc = _to_float(total_ttc);
    
    if (isNaN(remise) || remise == 0) {
        var total_negocie = _to_float(montant_negocie);
        document.forms[formname].elements['total_negoce'].disabled = false;
        document.forms[formname].elements['total_negoce'].className='textfloat';
        document.getElementById('span_totalremise').innerHTML="TOTAL NEG.";
    } else {
        var total_negocie = total_ttc - (total_ttc*remise/100);
        document.getElementById('span_totalremise').innerHTML="TOTAL REM.";
        document.forms[formname].elements['total_negoce'].className='text_readonly';
        document.forms[formname].elements['total_negoce'].disabled=true;
        document.forms[formname].elements['remise'].className='textfloat';
    }
    
    document.forms[formname].elements['total_negoce'].value = _to_string(total_negocie);
    
    
    
}


function calcul_total_negoce (formname, montant_negocie) {
//span_totalremise
    var total_negoce = document.forms[formname].elements['total_negoce'].value;
    var total_ttc = document.forms[formname].elements['total_ttc'].value;

    total_negoce = _to_float(total_negoce);
    total_ttc = _to_float(total_ttc);
    
    if (isNaN(total_negoce) || total_negoce == 0) {
      var total_negocie = _to_float(montant_negocie);
        document.forms[formname].elements['remise'].disabled = false;
        document.forms[formname].elements['remise'].className='textfloat';
        document.getElementById('span_totalremise').innerHTML="TOTAL REM.";
    } else {
        var total_negocie = total_negoce;
        document.getElementById('span_totalremise').innerHTML="TOTAL NEG.";
        document.forms[formname].elements['remise'].className='text_readonly';
        document.forms[formname].elements['remise'].disabled=true;
        document.forms[formname].elements['total_negoce'].className='textfloat';
    }
    
    document.forms[formname].elements['total_negoce'].value = _to_string(total_negocie);
    
    
    
}

/*
function round (num,p) {
    var string = String(num);
    var entier = string.substr(0,string.lastIndexOf('.'));
    var decimale = string.substr(string.lastIndexOf('.')+1);
    var tmp = "";
    
    if (decimale.charAt(p) < 5) {
        string = entier + "," + decimale.substr(0,2);
    } else {
        tmp = String(parseFloat(decimale.substr(0,2))+1);
        if (tmp.length == 1) {
            tmp = "0" + tmp;
        } else if (tmp.length == 3) {
            tmp = "00";
            entier = String(parseFloat(entier.substr(0,2))+1);
        }
        string = entier + "," + tmp;
    }
    return string;
}
*/
function _to_float (string) {
	
	var f = Remplacer_dans_chaine(string,',','.');
	f = Remplacer_dans_chaine(f,' ','');

	f = parseFloat(f);
	
    return f;
}
function Remplacer_dans_chaine(chaine,car_from, car_to){
	var reg=new RegExp(car_from, "g");
	tmp = chaine;
	tmp = tmp.replace(reg,car_to);

 	return tmp;
} 
function _to_string (f) {

	if(f!=0) {
		signe = Math.abs(f)/f; 
	}/* 1 ou -1*/
	else { 
		signe=1; 
	}

	f = Math.abs(f);

    f = Math.round(f*100)/100;

    f_partie_entiere = Math.floor(f);
    f_partie_decimale = Math.round((f - f_partie_entiere) * 100);

    if(f_partie_decimale==0){
        var string = f_partie_entiere+',00';    
    }
	else if(f_partie_decimale<10) {
	   var string = f_partie_entiere+',0'+f_partie_decimale;	
	}
    else {
        var string = f_partie_entiere+','+f_partie_decimale;
    }

    if(signe==-1) string = '-'+string;
/*
    var string = String(f);
    if (string.lastIndexOf('.') == -1) {
        string = string + ',00';
    } else if ((string.substr(string.lastIndexOf('.')+1)).length == 0){
        string = string.replace('.',',');
        string = string + '00';
    } else if ((string.substr(string.lastIndexOf('.')+1)).length == 1){
        string = string.replace('.',',');
        string = string + '0';
    } else if ((string.substr(string.lastIndexOf('.')+1)).length == 2){
        string = string.replace('.',',');
    } else {
        string = round(f,2);
        
    }*/
//    alert ("AVANT : "+f+" - APRES : "+string);
    return string;
}