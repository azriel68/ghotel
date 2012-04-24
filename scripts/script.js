function OpenForm(strUrl){
        window.document.location.href=strUrl;
}
function TListview_OrderBy(tblname,orderColumnOld,orderTypOld,orderColumnNew){
        //orderColumnOld : nom de la derni�re colonne � partir de laquelle on a tri�
        //orderTypOld : l'ancien ordre de tri
        //orderColumnNew : nom de la colonne sur laquelle on veut trier
        var orderTypNew='A';
        //var strURL=new String(document.location.href);
        if (orderColumnOld>"" && orderColumnNew>"" && orderColumnOld==orderColumnNew && orderTypOld=='A')
                orderTypNew='D';
        //modification de l'URL afin de passer les param�tres de tri
        document.location.href=modifyUrl(modifyUrl(modifyUrl(document.location.href,"orderColumn",orderColumnNew),"orderTyp",orderTypNew),"tblname",tblname);
}
function TListviewUrl_OrderBy(url,tblname,orderColumnOld,orderTypOld,orderColumnNew){
	    // idem pr�c�dente sauf url fournie
        var orderTypNew='A';
        if (orderColumnOld>"" && orderColumnNew>"" && orderColumnOld==orderColumnNew && orderTypOld=='A')
                orderTypNew='D';
        
		document.location.href=modifyUrl(modifyUrl(modifyUrl(url,"orderColumn",orderColumnNew),"orderTyp",orderTypNew),"tblname",tblname);
}
function TListview_SetIndex(idx){

		document.location.href=modifyUrl(modifyUrl(document.location.href,"charIndex",idx) ,"pageNumber",0);
    

}
function modifyUrl(strURL,paramName,paramNewValue){
        if (strURL.search(paramName+'=')!=-1){
                //on r�cup�re la premi�re partie de l'url
                var strFirstPart=strURL.substring(0,strURL.indexOf(paramName+'=',0))+paramName+'=';
                var strLastPart="";
                if (strURL.indexOf('&',strFirstPart.length-1)>0)
                        strLastPart=strURL.substring(strURL.indexOf('&',strFirstPart.length-1),strURL.length);
                strURL=strFirstPart+paramNewValue+strLastPart;
                }
        else{
                if (strURL.search('=')!=-1) // permet de verifier s'il y a dej� des param�tres dans l'URL
                        strURL+='&'+paramName+'='+paramNewValue;
                else
                        strURL+='?'+paramName+'='+paramNewValue;
                }
        return strURL;
}
function showPopup(strUrl,strFormName,strClickField,w,h,left,top){
        var strAdresse="";
		strAdresse+=strUrl
		
		if((strFormName!='')||(strClickField!='')){
			if(strUrl.indexOf('?')!=-1){
			 strAdresse+="&";
			}
			else {
			 strAdresse+="?";
			} 
			
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
        if(!left)left=10;
        if(!top)top=10;

        window.open(strAdresse,strFormName, "left="+left+", top="+top+", width="+w+", height="+h+", resizable=yes,dependent=yes,scrollbars=yes");
}

function _LinkForm_format(s){
    var chaine = s;

    var reg=new RegExp("(\\\\n)", "g");
    chaine = chaine.replace(reg, "\n");

    var reg=new RegExp("(\\\\r)", "g");
    chaine = chaine.replace(reg, "\r");
//alert(chaine);
    return chaine;
}

function LinkForm(strNomForm,strFormData){

  _linkForm_data(strNomForm,strFormData);
  window.opener=null;
  self.close();
}

function _linkForm_data(strNomForm,strFormData){

  with (window.opener.document){
        for (var i=0;i<forms.length;i++){
            if (forms[i].name==strNomForm){
                                for (var j=0;j<forms[i].elements.length;j++){
                    //on cherche si un champ du formulaire se trouve dans la liste affich�e
                     iIndex=strFormData.indexOf(forms[i].elements[j].name+'=',0);
                    if ((iIndex)>-1){
                        iIndex+=(forms[i].elements[j].name).length+1;
                        if (strFormData.indexOf(';',iIndex)>-1){
                                strValue=strFormData.substring(iIndex,strFormData.indexOf(';',iIndex));
                                forms[i].elements[j].value=_LinkForm_format(strValue);
                        }
                    }
            }
        }
    }
  }
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