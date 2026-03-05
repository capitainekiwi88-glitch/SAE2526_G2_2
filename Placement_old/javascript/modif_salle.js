// ############ Gestion boutons Precedent/Suivant ############

// My Frame
var myFrame=document.getElementById('myFrame');
var frCont=myFrame.contentDocument;

// Bouton
var btnBef=document.getElementById('btnBef');
var btnModif=document.getElementById('btnModif');
var btnNext=document.getElementById('btnNext');
var btnSave=document.getElementById('btnSave');

function recupVar()
{
	var myFrame=document.getElementById('myFrame');
	var frCont=myFrame.contentDocument;
	var nomSalle=frCont.getElementById("nomSalle");
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
		btnBef.style.display='none';
		btnModif.style.display='none';
		btnNext.style.display='none';
		btnSave.style.display='none';
	}
	else if(myFrame.name=='stage2')
	{
		btnBef.style.display='';
		btnModif.style.display='';
		btnNext.style.display='none';
		btnSave.style.display='none';
	}
	else if(myFrame.name=='stage6')
	{
		btnBef.style.display='';
		btnModif.style.display='none';
		btnNext.style.display='none';
		btnSave.style.display='';
	}
	else
	{
		btnBef.style.display='';
		btnModif.style.display='none';
		btnNext.style.display='';
		btnSave.style.display='none';
	}
}

// #### Gestion source frame ####

// Bouton precedent
btnBef.addEventListener('click', function(e) {
	var myFrame=document.getElementById('myFrame');
	var frCont=myFrame.contentDocument;
	switch(myFrame.name)
	{
		case "stage2": 	myFrame.name="stage1";
						myFrame.src="ms_stage1.php";
						break;
						
		case "stage3":	myFrame.name="stage2";
						myFrame.src="ms_stage2.php";
						break;
						 
		case "stage4":	myFrame.name="stage3";
						myFrame.src="ms_stage3.php";
						break;
						
		case "stage5":	myFrame.name="stage4";
						myFrame.src="ms_stage4.php";
						break;
						
		case "stage6":	myFrame.name="stage5";
						myFrame.src="ms_stage5.php";
						break;
						
		default: 		break;
	}
	affBtn();
}, false);

// Bouton suivant
btnNext.addEventListener('click', function(e) {
	var myFrame=document.getElementById('myFrame');
	var frCont=myFrame.contentDocument;

	var nomSalle=frCont.getElementById("nomSalle");
	var batSalle=frCont.getElementById("batSalle");
	var dptSalle=frCont.getElementById("dptSalle");
	var etageSalle=frCont.getElementById("etageSalle");
	
	switch(myFrame.name)
	{
		case "stage1":	myFrame.name="stage2";
						myFrame.src="ms_stage2.php";
						break;
						
		case "stage2": 	myFrame.name="stage3";
						myFrame.src="ms_stage3.php";
						break;
						
		case "stage3": 	if(parseInt(checkChamp())==4)
						{
							myFrame.name="stage4";
							myFrame.src="ms_stage4.php?var1="+nomSalle.value+"&var2="+batSalle.value+"&var3="+dptSalle.value+"&var4="+etageSalle.value;
						}
						break;
						
		case "stage4": 	myFrame.name="stage5";
						myFrame.src="ms_stage5.php";
						break;
						
		case "stage5": 	myFrame.name="stage6";
						myFrame.src="ms_stage6.php";
						break;
						
		default: 		break;
	}
	affBtn();
}, false);

// Bouton modifier
btnModif.addEventListener('click', function(e) {

	var myFrame=document.getElementById('myFrame');
	var frCont=myFrame.contentDocument;
	
	switch(myFrame.name)
	{
		case "stage1":	myFrame.name="stage2";
						myFrame.src="ms_stage2.php";
						break;
						
		case "stage2": 	myFrame.name="stage3";
						myFrame.src="ms_stage3.php";
						break;
						
		default: 		break;
	}
	affBtn();
}, false);

// Bouton enregistrer
btnSave.addEventListener('click', function(e) {
	var myFrame=document.getElementById('myFrame');
	var frCont=myFrame.contentDocument;
	var form=frCont.getElementById('formSave');
	form.submit();
}, false);


/*
var btnCrea2=frCont.getElementById('btncrea');

btnCrea2.addEventListener('click', function(e) {
  alert("test");
  //location.replace("index.php?p=crea_salle");
}, false);
*/
