/*
'*********** 
' Devise=0   aucune 
'       =1   Euro € 
'       =2   Dollar $ 
' Langue=0   Français 
'       =1   Belgique 
'       =2   Suisse 
'*********** 
' Conversion limitée à 999 999 999 999 999 ou 9 999 999 999 999,99 
' si le nombre contient plus de 2 décimales, il est arrondit à 2 décimales 
*/
function ConvNumberLetter_fr(Nombre, bCheckFloat) {	
	var strNombre = new String(Nombre) ;
	var TabNombre = new Array() ;
	var strLetter = new String() ;
	
	if(isNaN(parseFloat(Nombre))) return "";
	
	if(bCheckFloat) {
		TabNombre = strNombre.split(".") ;
		if(TabNombre.length > 2 || TabNombre.length <= 0) return "" ;
		for(var i = 0; i < TabNombre.length; i++) {
			if(i == 0) 
				strLetter = strLetter + ConvNumberLetter(parseFloat(TabNombre[i]), 1, 0) ;
			else
				strLetter = strLetter + ConvNumberLetter(parseFloat(TabNombre[i]), 0, 0) ;
		}
		return strLetter ;
	}
	else {
		strLetter = ConvNumberLetter(Nombre, 1, 0) ;
		return strLetter ;
	}
}

function ConvNumberLetter(Nombre, Devise, Langue) {
    var dblEnt, byDec ; 
    var bNegatif; 
    var strDev = new String();
	var strCentimes = new String();
    
    if( Nombre < 0 ) {
        bNegatif = true;
        Nombre = Math.abs(Nombre);
    }
    dblEnt = parseInt(Nombre) ;
    byDec = parseInt((Nombre - dblEnt) * 100) ;
    if( byDec == 0 ) {
        if (dblEnt > 999999999999999) {
            return "#TropGrand" ;            
        }
	}
    else {
        if (dblEnt > 9999999999999.99) {
            return "#TropGrand" ;            
        }    
	}
	switch(Devise) {
        case 0 :
            if (byDec > 0) strDev = " virgule" ;
			break;
        case 1 :
            strDev = " Euro" ;
            if (byDec > 0) strCentimes = strCentimes + " Cents" ;
			break;
        case 2 :
            strDev = " Dollar" ;
            if (byDec > 0) strCentimes = strCentimes + " Cent" ;
			break;
	}
    if (dblEnt > 1 && Devise != 0) strDev = strDev + "s" ;
    
	var NumberLetter = ConvNumEnt(parseFloat(dblEnt), Langue) + strDev + " " + ConvNumDizaine(byDec, Langue) + strCentimes ;
	return NumberLetter;
}

function ConvNumEnt(Nombre, Langue) {
    var byNum, iTmp, dblReste ;
    var StrTmp = new String();
    var NumEnt ;
    iTmp = Nombre - (parseInt(Nombre / 1000) * 1000) ;
    NumEnt = ConvNumCent(parseInt(iTmp), Langue) ;
    dblReste = parseInt(Nombre / 1000) ;
    iTmp = dblReste - (parseInt(dblReste / 1000) * 1000) ;
    StrTmp = ConvNumCent(parseInt(iTmp), Langue) ;
    switch(iTmp) {
        case 0 :
			break;
        case 1 :
            StrTmp = "mille " ; 
			break;
        default : 
            StrTmp = StrTmp + " mille " ;
    }
    NumEnt = StrTmp + NumEnt ;
    dblReste = parseInt(dblReste / 1000) ;
    iTmp = dblReste - (parseInt(dblReste / 1000) * 1000) ;
    StrTmp = ConvNumCent(parseInt(iTmp), Langue) ;
    switch(iTmp) {
        case 0 :
			break;
        case 1 :
            StrTmp = StrTmp + " million " ;
			break;
        default : 
            StrTmp = StrTmp + " millions " ;
    }
    NumEnt = StrTmp + NumEnt ;
    dblReste = parseInt(dblReste / 1000) ;
    iTmp = dblReste - (parseInt(dblReste / 1000) * 1000) ;
    StrTmp = ConvNumCent(parseInt(iTmp), Langue) ;
	switch(iTmp) {
        case 0 :
			break;
        case 1 :
            StrTmp = StrTmp + " milliard " ;
			break;
        default : 
            StrTmp = StrTmp + " milliards " ;
    }
    NumEnt = StrTmp + NumEnt ;
    dblReste = parseInt(dblReste / 1000) ;
    iTmp = dblReste - (parseInt(dblReste / 1000) * 1000) ;
    StrTmp = ConvNumCent(parseInt(iTmp), Langue) ;
   	switch(iTmp) {
        case 0 :
			break;
        case 1 :
            StrTmp = StrTmp + " billion " ;
			break;
        default : 
            StrTmp = StrTmp + " billions " ;
    }
    NumEnt = StrTmp + NumEnt ;
 	return NumEnt;    
}

function ConvNumDizaine(Nombre, Langue) {
    var TabUnit, TabDiz ;
    var byUnit, byDiz ;
    var strLiaison = new String() ;
    
    TabUnit = Array("", "un", "deux", "trois", "quatre", "cinq", "six", "sept",
        "huit", "neuf", "dix", "onze", "douze", "treize", "quatorze", "quinze",
        "seize", "dix-sept", "dix-huit", "dix-neuf") ;
    TabDiz = Array("", "", "vingt", "trente", "quarante", "cinquante",
        "soixante", "soixante", "quatre-vingt", "quatre-vingt") ;
    if (Langue == 1) {
        TabDiz[7] = "septante" ;
        TabDiz[9] = "nonante" ;
	}
    else if (Langue == 2) {
        TabDiz[7] = "septante" ;
        TabDiz[8] = "huitante" ;
        TabDiz[9] = "nonante" ;
    }
    byDiz = parseInt(Nombre / 10) ;
    byUnit = Nombre - (byDiz * 10) ;
    strLiaison = "-" ;
    if (byUnit == 1) strLiaison = " et " ;
    switch(byDiz) {
        case 0 :
            strLiaison = "" ;
			break;
        case 1 :
            byUnit = byUnit + 10 ;
            strLiaison = "" ;
			break;
        case 7 :
            if (Langue == 0) byUnit = byUnit + 10 ;
			break;
        case 8 :
            if (Langue != 2) strLiaison = "-" ;
			break;
        case 9 :
            if (Langue == 0) {
                byUnit = byUnit + 10 ;
                strLiaison = "-" ;
            }
			break;
    }
    var NumDizaine = TabDiz[byDiz] ;
    if (byDiz == 8 && Langue != 2 && byUnit == 0) NumDizaine = NumDizaine + "s" ;
    if (TabUnit[byUnit] != "") {
        NumDizaine = NumDizaine + strLiaison + TabUnit[byUnit] ;
	}
    else {
        NumDizaine = NumDizaine ;
    } 
	return NumDizaine;
}

function ConvNumCent(Nombre, Langue) {
    var TabUnit ;
    var byCent, byReste ;
    var strReste = new String() ;
    var NumCent;
    TabUnit = Array("", "un", "deux", "trois", "quatre", "cinq", "six", "sept","huit", "neuf", "dix") ;
    
    byCent = parseInt(Nombre / 100) ;
    byReste = Nombre - (byCent * 100) ; 
    strReste = ConvNumDizaine(byReste, Langue) 
    switch(byCent) {
        case 0 :
            NumCent = strReste ;
			break;
        case 1 :
            if (byReste == 0)
                NumCent = "cent" ;
            else 
                NumCent = "cent " + strReste ;
            break;
        default :
            if (byReste == 0)
                NumCent = TabUnit[byCent] + " cents" ;
            else 
                NumCent = TabUnit[byCent] + " cent " + strReste ;
	}
	return NumCent;
}