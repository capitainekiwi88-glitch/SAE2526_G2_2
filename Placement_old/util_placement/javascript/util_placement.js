// ############ Gestion boutons Precedent/Suivant ############
var btnbef=document.getElementById('btnbef');
var btnnext=document.getElementById('btnnext');
affBtnNavigation();

function getTooltip(element)
{
    while (element=element.nextSibling)
    {
        if (element.className==='tooltip')
        {
            return element;
        }
    }
    return false;
}


function checkChamp()
{
	// Frame
	var myFrame=document.getElementById('myFrame');
	var frCont=myFrame.contentDocument;
	
	// Variables
	var dateDevoir=frCont.getElementById("date_dev");
	var hDevoir=frCont.getElementById("h_deb");
	var mDevoir=frCont.getElementById("m_deb");
	var hDuree=frCont.getElementById("h_duree");
	var mDuree=frCont.getElementById("m_duree");
	
	var ok=0;
	
	// ################ Test date ################
	tooltipStyle=getTooltip(dateDevoir).style;
	if(dateDevoir.value!='')
	{
		dateDevoir.className="correct";
		tooltipStyle.display='none';
		ok++;
	}
	else
	{
		dateDevoir.className="incorrect";
		tooltipStyle.display='inline-block';
	}

	// ################ Test heure debut ################
	tooltipStyle=getTooltip(mDevoir).style;
	if(hDevoir.value=='A')
	{
		hDevoir.className="incorrect";
		tooltipStyle.display='inline-block';
	}
	else if(mDevoir.value=='A')
	{
		mDevoir.className="incorrect";
		tooltipStyle.display='inline-block';
	}
	else
	{
		hDevoir.className="correct";
		mDevoir.className="correct";
		tooltipStyle.display='none';
		ok++;
	}

	// ################ Test duree ################
	tooltipStyle=getTooltip(mDuree).style;
	if(hDuree.value=='A')
	{
		hDuree.className="incorrect";
		tooltipStyle.display='inline-block';
	}
	else if(mDuree.value=='A')
	{
		mDuree.className="incorrect";
		tooltipStyle.display='inline-block';
	}
	else
	{
		hDuree.className="correct";
		mDuree.className="correct";
		tooltipStyle.display='none';
		ok++;
	}
	
	return ok;
	
}

// Enregistrement infos devoir
function recupInfoDev()
{		
	// Frame
	var myFrame=document.getElementById('myFrame');
	var frCont=myFrame.contentDocument;
		
	// Variables
	var dateDevoir=frCont.getElementById("date_dev");
	var hDevoir=frCont.getElementById("h_deb");
	var mDevoir=frCont.getElementById("m_deb");
	var hDuree=frCont.getElementById("h_duree");
	var mDuree=frCont.getElementById("m_duree");
	
		// ############## Modification de la valeur dans la variable PHP ##############
		
		// Objet XHR
		var xhr=new XMLHttpRequest();
		
		// Encodage des variables
		var dateD=encodeURIComponent(dateDevoir.value);
		var hD=encodeURIComponent(hDevoir.value);
		var mD=encodeURIComponent(mDevoir.value);
		var hF=encodeURIComponent(hDuree.value);
		var mF=encodeURIComponent(mDuree.value);
		
		// Modification de la valeur dans la variable php $_SESSION[structSalle]
		xhr.open('POST', './util_placement/ajaxInfoDev.php', false);
		xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xhr.send('dateDev='+dateD+'&hDev='+hD+'&mDev='+mD+'&hDur='+hF+'&mDur='+mF);
	
}

// ##### Affichage boutons #####

function affBtnNavigation()
{
	if(myFrame.name=='stage1')
	{
		btnbef.style.display='none';
		btnnext.style.display = '';
		btnnext.disabled="disabled";
	}
	else if(myFrame.name=='stage3') {
		btnnext.style.display = 'none';
	}
	else
	{
		btnbef.style.display='';
		btnnext.style.display='';
	}
}

// #### Gestion source frame ####


// Bouton precedent
btnbef.addEventListener('click', function(e) {
	var myFrame=document.getElementById('myFrame');
	switch(myFrame.name)
	{
		case "stage2": 	myFrame.name="stage1";
						myFrame.src="util_placement/up_stage1.php";
						break;
						
		case "stage3":	myFrame.name="stage2";
						myFrame.src="util_placement/up_stage2.php";
						break;
						 

		default: 		break;
	}
	affBtnNavigation();
}, false);

// Bouton suivant
btnnext.addEventListener('click', function(e) {
	var myFrame=document.getElementById('myFrame');
	switch(myFrame.name)
	{
		case "stage1":	//if(parseInt(checkChamp())==4)
						//{
							recupInfoDev();
							myFrame.name="stage2";
							myFrame.src="util_placement/up_stage2.php";
						//}
						break;
						
		case "stage2": 	myFrame.name="stage3";
						myFrame.src="util_placement/up_stage3.php";
						break;
						
		default: 		break;
	}
	affBtnNavigation();
}, false);
