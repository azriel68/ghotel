function getYScroll(){
 y=0;
 if (document.body)y = document.body.scrollTop;
 return y;
}
function page_scroll_by(y){
	self.scrollBy(0,y);
}
function refresh_template(tag, id_edito){
	refresh_article(tag, id_edito);
	refresh_preview(tag, id_edito);
}
function refresh_preview(tag, id_edito){
	gab = document.getElementById('select_gabari_'+tag).value;
	zone = document.getElementById('select_zone_'+tag).value;
	cat = document.getElementById('select_categorie_'+tag).value;
	if(id_edito==-1){
		id_edito= document.getElementById('select_edito_'+tag).value;
	}
	/*
	var XHR = new XHRConnection();		
	XHR.setRefreshArea('preview_'+tag);
	XHR.sendAndLoad("../scripts/get_preview_tag.php?tag="+tag+"&gabarit="+gab+"&id_edito="+id_edito, "GET");*/
	
	document.getElementById('hidden_frame').contentWindow.location.href="../scripts/get_preview_tag.php?tag="+tag+"&gabarit="+gab+"&id_edito="+id_edito+"&zone="+zone+"&cat="+cat+"&force_retaille=1";
}

function refresh_article_news(name, id_edito_actu){
	cat = document.getElementById('select_categorie_'+name).value;
	zone = document.getElementById('select_zone_'+name).value;

	var XHR = new XHRConnection();		
	XHR.setRefreshArea('div_select_edito_'+name);
	url="../scripts/get_liste_article.php?name="+name+"&categorie="+cat+"&selected="+id_edito_actu+"&zone="+zone;
	XHR.sendAndLoad(url, "GET");

}
function refresh_article(tag, id_edito_actu){

	cat = document.getElementById('select_categorie_'+tag).value;
	zone = document.getElementById('select_zone_'+tag).value;

	var XHR = new XHRConnection();		
	XHR.setRefreshArea('div_select_edito_'+tag);
	//alert("../scripts/get_liste_article.php?categorie="+cat);
	url="../scripts/get_liste_article.php?tag="+tag+"&categorie="+cat+"&selected="+id_edito_actu+"&zone="+zone;
	XHR.sendAndLoad(url, "GET");
	
}

function set_view(e_work,e_zone,v){
	if(v==1){
		if (document.getElementById(e_work).style['visibility']=='hidden'){
			document.getElementById(e_work).style['visibility']='visible';
		//	document.getElementById(e_work).style['position']='fixe';
			/*if(e_zone!=''){
				setTimeout('document.getElementById(\''+e_zone+'\').className=\'work_zone\'',100);
			}*/
		}
	}
	else{
		if (document.getElementById(e_work).style['visibility']=='visible'){
			document.getElementById(e_work).style['visibility']='hidden';
		//	setTimeout('document.getElementById(\''+e_work+'\').style[\'visibility\']=\'hidden\'',1000);
		//	document.getElementById(e_work).style['position']='absolute';
		/*	if(e_zone!=''){
				setTimeout('document.getElementById(\''+e_zone+'\').className=\'\'',100);
			}*/
		}
	}
}
function popup_color_picker(form,tag)
{
	var width=400;
	var height=300;
	window.open('../scripts/color_choice.php?FORM='+form+'&tag='+tag, 'cp', 'resizable=no, location=no, width='+width+', height='+height+', menubar=no, status=yes, scrollbars=no, menubar=no');
}
function writeZone(e_zone,e_preview){
	text_src = document.getElementById(e_zone).value;
	text_src = parseStr(text_src);
	document.getElementById(e_preview).innerHTML = text_src;
	document.getElementById(e_preview).className='';
}
function add_cadre(e_zone,e_preview,e_color1,e_color2){
	deb = 0;
	fin = 0;
	
	color1 = document.getElementById(e_color1).value;
	color2 = document.getElementById(e_color2).value;
	
	giveSelect(e_zone);
	
	text_src = document.getElementById(e_zone).value;
	var l = text_src.length;
	var s = new String;
	
	s = text_src.substring(0,deb);
	
	s=s+'<div style="border:1px solid '+color1+';background-color:'+color2+';padding:5px;">'+text_src.substring(deb,fin)+'</div>';
	
	s=s+text_src.substring(fin,l);
	
	document.getElementById(e_zone).value=s;
	//writeZone(e_zone,e_preview);

}
function add_color(e_zone,e_preview,e_color1){
	deb = 0;
	fin = 0;
	
	color1 = document.getElementById(e_color1).value;
	
	giveSelect(e_zone);
	
	text_src = document.getElementById(e_zone).value;
	var l = text_src.length;
	var s = new String;
	
	s = text_src.substring(0,deb);
	
	s=s+'<span style="color:'+color1+';">'+text_src.substring(deb,fin)+'</span>';
	
	s=s+text_src.substring(fin,l);
	
	document.getElementById(e_zone).value=s;
	//writeZone(e_zone,e_preview);

}
function addBalise(e_zone,e_preview,bal){
	deb = 0;
	fin = 0;
	
	giveSelect(e_zone);
	
	text_src = document.getElementById(e_zone).value;
	var l = text_src.length;
	var s = new String;
	
	s = text_src.substring(0,deb);
	
	if (bal=='a'){
		lien = window.prompt('Lien ?','http://');
		
		if (lien!=null) {
			s=s+'<a href="'+lien+'" target="_blank">'+text_src.substring(deb,fin)+'</a>';
		}
		else {
			s=s+text_src.substring(deb,fin);
		}
	}
	else if (bal=='divc') {
		s=s+'<div align=center>'+text_src.substring(deb,fin)+'</div>';
	}
	else if (bal=='divl') {
		s=s+'<div align=left>'+text_src.substring(deb,fin)+'</div>';
	}
	else if (bal=='divr') {
		s=s+'<div align=right>'+text_src.substring(deb,fin)+'</div>';
	}
	else if (bal=='divj') {
		s=s+'<div align=justify>'+text_src.substring(deb,fin)+'</div>';
	}
	else if (bal=='Quot') {
		s=s+'&quot;'+text_src.substring(deb,fin)+'&quot;';
	}
	else if (bal=='br') {
		s=s+text_src.substring(deb,fin)+'<br>';
	}
	else {
		s=s+'<'+bal+'>'+text_src.substring(deb,fin)+'</'+bal+'>';
	}
	
	
	s=s+text_src.substring(fin,l);
	
	document.getElementById(e_zone).value=s;
	//writeZone(e_zone,e_preview);
}
function giveSelect(zone){
	
	if (document.selection) {
		getPosCurseur(document.getElementById(zone));	
	}
	else {
		deb = document.getElementById(zone).selectionStart;
		fin = document.getElementById(zone).selectionEnd;
	}
	
}
function getPosCurseur(oTextArea) {
   var sAncienTexte = oTextArea.value;

   var oRange = document.selection.createRange();
   var sAncRangeTexte = oRange.text;
   var sMarquer = "{.*";

   oRange.text = sMarquer + sAncRangeTexte + sMarquer; 
   oRange.moveStart('character', (0 - sAncRangeTexte.length - (sMarquer.length*2)));

   var sNouvTexte = oTextArea.value;

   oRange.text = sAncRangeTexte;

   fl_first_curseur = 1;
   for (i=0; i <= sNouvTexte.length; i++) {
     var sTemp = sNouvTexte.substring(i, i + sMarquer.length);
     
	 if (sTemp == sMarquer) {
	    	 
       
       if(fl_first_curseur==1){
	   	   	deb = i;
			fl_first_curseur=0;
	   }
	   else {
	   		fin = i-3;
	   }
	   
     }
   }
}
  
function copy_chp_to(FORM, chp1, chp2){
	document.forms[FORM].elements[chp2].value= document.forms[FORM].elements[chp1].value;
}
function OpenForm(strUrl){
        window.document.location.href=strUrl;
}
function TListview_OrderBy(tblname,orderColumnOld,orderTypOld,orderColumnNew){
        //orderColumnOld : nom de la dernière colonne à partir de laquelle on a trié
        //orderTypOld : l'ancien ordre de tri
        //orderColumnNew : nom de la colonne sur laquelle on veut trier
        var orderTypNew='A';
        //var strURL=new String(document.location.href);
        if (orderColumnOld>"" && orderColumnNew>"" && orderColumnOld==orderColumnNew && orderTypOld=='A')
                orderTypNew='D';
        //modification de l'URL afin de passer les paramètres de tri
        document.location.href=modifyUrl(modifyUrl(modifyUrl(document.location.href,"orderColumn",orderColumnNew),"orderTyp",orderTypNew),"tblname",tblname);
}
function TListviewUrl_OrderBy(url,tblname,orderColumnOld,orderTypOld,orderColumnNew){
	    // idem précédente sauf url fournie
        var orderTypNew='A';
        if (orderColumnOld>"" && orderColumnNew>"" && orderColumnOld==orderColumnNew && orderTypOld=='A')
                orderTypNew='D';
        
		document.location.href=modifyUrl(modifyUrl(modifyUrl(url,"orderColumn",orderColumnNew),"orderTyp",orderTypNew),"tblname",tblname);
}
function modifyUrl(strURL,paramName,paramNewValue){
        if (strURL.search(paramName+'=')!=-1){
                //on récupère la première partie de l'url
                var strFirstPart=strURL.substring(0,strURL.indexOf(paramName+'=',0))+paramName+'=';
                var strLastPart="";
                if (strURL.indexOf('&',strFirstPart.length-1)>0)
                        strLastPart=strURL.substring(strURL.indexOf('&',strFirstPart.length-1),strURL.length);
                strURL=strFirstPart+paramNewValue+strLastPart;
                }
        else{
                if (strURL.search('=')!=-1) // permet de verifier s'il y a dejà des paramètres dans l'URL
                        strURL+='&'+paramName+'='+paramNewValue;
                else
                        strURL+='?'+paramName+'='+paramNewValue;
                }
        return strURL;
}
function showPopup(strUrl,strFormName,strClickField,w,h){
        var strAdresse="";
		strAdresse+=strUrl
		
		if((strFormName!='')||(strClickField!='')){
			strAdresse+="?";
		}
		
        if(strFormName!=''){
			strAdresse+="FORM="+strFormName;
		}
    l=strClickField.length;
    if (strClickField != '' && l>0){
        param ='';j=0;k=0;
        while (j<l){
            i = strClickField.indexOf(';',j);
            if (i==-1) i=l;
			
			if((param!='')||(strFormName!='')){
				param+='&';
			}
			param +='p'+k+'='+strClickField.substring(j,i);
            j=i+1;
            k++;
        }
        strAdresse+=param;
    }

        if(!w)w=400;
        if(!h)h=700;


        window.open(strAdresse,strFormName, "left=10, top=10, width="+w+", height="+h+", resizable=yes,dependent=yes,scrollbars=yes");
}
function LinkForm(strNomForm,strFormData){

    with (window.opener.document){
        for (var i=0;i<forms.length;i++){
            if (forms[i].name==strNomForm){
                                for (var j=0;j<forms[i].elements.length;j++){
                    //on cherche si un champ du formulaire se trouve dans la liste affichée
                     iIndex=strFormData.indexOf(forms[i].elements[j].name+'=',0);
                    if ((iIndex)>-1){
                        iIndex+=(forms[i].elements[j].name).length+1;
                        if (strFormData.indexOf(';',iIndex)>-1){
                                strValue=strFormData.substring(iIndex,strFormData.indexOf(';',iIndex));
                                forms[i].elements[j].value=strValue;
                        }
                    }
            }
        }
    }
  }
  window.opener=null;
  self.close();
}
function addFam(id) {

	lib = window.prompt("Libellé : ","Nouvelle famille");
	
	if(lib!=null){
		document.location.href="admin_fam.php?action=ADD&id="+id+"&lib="+lib;
	}

}
function supprFam(id){
	
	if(window.confirm("C'est sur ?")){
		document.location.href="admin_fam.php?action=DEL&id="+id;
	}
	
}

function renFam(id,lib) {

	lib = window.prompt("Libellé : ",lib);
	
	if(lib!=null){
		document.location.href="admin_fam.php?action=REN&id="+id+"&lib="+lib;
	}

}

function go(sid){
	document.location.href="http://dev.batiactu.com/cap_indus/OperatingSystem/frame.php?page=prod&parent_fam="+document.forms['formprod'].elements['produit'].options[document.forms['formprod'].elements['produit'].selectedIndex].value+"&"+sid;
}
function TDBListview_PreviousPage(pageNumber,tblname) {

	if(tblname==null){
		tblname=".";
	}

        if (pageNumber>0){
                pageNumber--;
               
                document.location.href=modifyUrl(modifyUrl(document.location.href,"pageNumber",pageNumber),"tblname",tblname);
                }
}

function TDBListview_NextPage(pageNumber,nbPage,tblname) {

	if(tblname==null){
		tblname=".";
	}

        if (pageNumber<nbPage){
                pageNumber++;
                
                document.location.href=modifyUrl(modifyUrl(document.location.href,"pageNumber",pageNumber),"tblname",tblname);
        }
}

function TDBListview_GoToPage(pageNumber,nbPage,tblname){
	if(tblname==null){
		tblname=".";
	}
        if (pageNumber<=nbPage && pageNumber>=0){
                
                document.location.href=modifyUrl(modifyUrl(document.location.href,"pageNumber",pageNumber),"tblname",tblname);
        }
}


function TDBListviewUrl_PreviousPage(url,pageNumber,tblname) {

	if(tblname==null){
		tblname=".";
	}

        if (pageNumber>0){
                pageNumber--;
               
                document.location.href=modifyUrl(modifyUrl(url,"pageNumber",pageNumber),"tblname",tblname);
                }
}

function TDBListviewUrl_NextPage(url,pageNumber,nbPage,tblname) {

	if(tblname==null){
		tblname=".";
	}

        if (pageNumber<nbPage){
                pageNumber++;
                
                document.location.href=modifyUrl(modifyUrl(url,"pageNumber",pageNumber),"tblname",tblname);
        }
}

function TDBListviewUrl_GoToPage(url,pageNumber,nbPage,tblname){
	if(tblname==null){
		tblname=".";
	}
        if (pageNumber<=nbPage && pageNumber>=0){
                
                document.location.href=modifyUrl(modifyUrl(url,"pageNumber",pageNumber),"tblname",tblname);
        }
}
function write_preview(){
		document.getElementById('preview').innerHTML = getHTML();
}
function openHelp(h){
	showPopup("../dlg/help_for.php?h="+h,"","",400,400);
}
function write_nbcarrest(obj,wzone,nbcarMax,bloq){
 	nb_actu = obj.value.length;
	nb_rest = nbcarMax-nb_actu;
	if((nb_rest<0)&&(bloq==true)){
		document.getElementById(wzone).innerHTML="<b>Texte plein</b>"; 
		obj.value=obj.value.substr(0,nbcarMax);
	}
	else if(nb_rest<0){
		document.getElementById(wzone).innerHTML="<b>"+nb_rest+" caractères restants - trop de caractères</b>"; 
	} else {
		document.getElementById(wzone).innerHTML=nb_rest+" caractères restants";
	}
}
function LinkForm2(strNomForm,strFormData){

   with (window.opener.document){
        for (var i=0;i<forms.length;i++){
            if (forms[i].name==strNomForm){
                for (var j=0;j<forms[i].elements.length;j++){
                    //on cherche si un champ du formulaire se trouve dans la liste affichée
                    iIndex=strFormData.indexOf(forms[i].elements[j].name+'=',0);
                    if ((iIndex)>-1){
                        iIndex+=(forms[i].elements[j].name).length+1;
                        if (strFormData.indexOf(';',iIndex)>-1){
                                strValue=strFormData.substring(iIndex,strFormData.indexOf(';',iIndex));
                                forms[i].elements[j].value=strValue;
                        }
                    }
                }
				forms[i].submit();
        	}
    	}
		
    }
  
 
  window.opener=null;
  self.close();
}