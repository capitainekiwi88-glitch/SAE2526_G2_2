// ############ Gestion boutons Precedent/Suivant ############
var btnbef=document.getElementById('btnbef');
var btnnext=document.getElementById('btnnext');
var btnsave=document.getElementById('btnsave');

function recupVar()
{
	var myFrame=document.getElementById('myFrame');
	var frCont=myFrame.contentDocument;
	var nomSalle=frCont.getElementById("nomSalle");
	var rangSalle=frCont.getElementById("nbRang");
	var colSalle=frCont.getElementById("nbCol");
	var batSalle=frCont.getElementById("batSalle");
	var dptSalle=frCont.getElementById("dptSalle");
	var etageSalle=frCont.getElementById("etageSalle");
}


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
	var myFrame=document.getElementById('myFrame');
	var frCont=myFrame.contentDocument;
	var nomSalle=frCont.getElementById("nomSalle");
	var rangSalle=frCont.getElementById("nbRang");
	var colSalle=frCont.getElementById("nbCol");
	var batSalle=frCont.getElementById("batSalle");
	var dptSalle=frCont.getElementById("dptSalle");
	var etageSalle=frCont.getElementById("etageSalle");
	
	var ok=0;
	
	// ################ Test nom ################
	tooltipStyle=getTooltip(nomSalle).style;
	if(nomSalle.value.length<2)
	{
		nomSalle.className="incorrect";
		tooltipStyle.display='inline-block';
	}
	else
	{
		nomSalle.className="correct";
		tooltipStyle.display='none';
		ok++;
	}

	// ################ Test nbRang ################
	tooltipStyle=getTooltip(rangSalle).style;
	if(parseInt(rangSalle.value)>1 && parseInt(rangSalle.value)<30)
	{
		rangSalle.className="correct";
		tooltipStyle.display='none';
		ok++;
	}
	else
	{
		rangSalle.className="incorrect";
		tooltipStyle.display='inline-block';
	}

	// ################ Test nbColonne ################
	tooltipStyle=getTooltip(colSalle).style;
	if(parseInt(colSalle.value)>1 && parseInt(colSalle.value)<30)
	{
		colSalle.className="correct";
		tooltipStyle.display='none';
		ok++;
	}
	else
	{
		colSalle.className="incorrect";
		tooltipStyle.display='inline-block';
	}

	// ################ Test etage ################
	tooltipStyle=getTooltip(etageSalle).style;
	if(etageSalle.value=='A')
	{
		etageSalle.className="incorrect";
		tooltipStyle.display='inline-block';
	}
	else
	{
		etageSalle.className="correct";
		tooltipStyle.display='none';
		ok++;
	}

	// ################ Test batiment ################
	tooltipStyle=getTooltip(batSalle).style;
	if(batSalle.value=='A')
	{
		batSalle.className="incorrect";
		tooltipStyle.display='inline-block';
	}
	else
	{
		batSalle.className="correct";
		tooltipStyle.display='none';
		ok++;
	}

	// ################ Test departement ################
	tooltipStyle=getTooltip(dptSalle).style;
	if(dptSalle.value=='A' && batSalle.value=='3')
	{
		dptSalle.className="incorrect";
		tooltipStyle.display='inline-block';
	}
	else
	{
		dptSalle.className="correct";
		tooltipStyle.display='none';
		ok++;
	}
	
	return ok;
	
}


// ##### Affichage boutons #####

function affBtn()
{
	if(myFrame.name=='stage1')
	{
		btnbef.style.display='none';
		btnnext.style.display='';
		btnsave.style.display='none';
	}
	else if(myFrame.name=='stage4')
	{
		btnbef.style.display='';
		btnnext.style.display='none';
		btnsave.style.display='';
	}
	else
	{
		btnbef.style.display='';
		btnnext.style.display='';
		btnsave.style.display='none';
	}
}

// #### Gestion source frame ####


// Bouton precedent
btnbef.addEventListener('click', function(e) {
	var myFrame=document.getElementById('myFrame');
	var frCont=myFrame.contentDocument;
	var nomSalle=frCont.getElementById("nomSalle");
	var rangSalle=frCont.getElementById("nbRang");
	var colSalle=frCont.getElementById("nbCol");
	switch(myFrame.name)
	{
		case "stage2": 	myFrame.name="stage1";
						myFrame.src="cs_stage1.php";
						break;
						
		case "stage3":	myFrame.name="stage2";
						myFrame.src="cs_stage2.php";
						break;
						 
		case "stage4":	myFrame.name="stage3";
						myFrame.src="cs_stage3.php";
						break;
						
		default: 		break;
	}
	affBtn();
}, false);

// Bouton suivant
btnnext.addEventListener('click', function(e) {
	var myFrame=document.getElementById('myFrame');
	var frCont=myFrame.contentDocument;
	
	// Informations salles
	var nomSalle=frCont.getElementById("nomSalle");
	var rangSalle=frCont.getElementById("nbRang");
	var colSalle=frCont.getElementById("nbCol");
	var batSalle=frCont.getElementById("batSalle");
	var dptSalle=frCont.getElementById("dptSalle");
	var etageSalle=frCont.getElementById("etageSalle");
	
	switch(myFrame.name)
	{
		case "stage1":	if(parseInt(checkChamp())==6)
						{
							myFrame.name="stage2";
							myFrame.src="cs_stage2.php?var1="+nomSalle.value+"&var2="+rangSalle.value+"&var3="+colSalle.value+"&var4="+batSalle.value+"&var5="+dptSalle.value+"&var6="+etageSalle.value;
						}
						break;
						
		case "stage2": 	myFrame.name="stage3";
						myFrame.src="cs_stage3.php";
						break;
						
		case "stage3": 	myFrame.name="stage4";
						myFrame.src="cs_stage4.php";
						break;
						
		default: 		break;
	}
	affBtn();
}, false);

// Bouton enregistrer
btnsave.addEventListener('click', function(e) {
	var myFrame=document.getElementById('myFrame');
	var frCont=myFrame.contentDocument;
	var form=frCont.getElementById('formSave');
	form.submit();
}, false);