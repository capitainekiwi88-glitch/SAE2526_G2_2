/* MODIF PLACE */

function modifPlace(x,y,val)
{	

		// ############## Modification de la valeur dans la variable PHP ##############
		
		// Objet XHR
		var xhr=new XMLHttpRequest();
		
		// Encodage des variables
		var varX=encodeURIComponent(x);
		var varY=encodeURIComponent(y);
		var varVal=encodeURIComponent(val);
		
		// Modification de la valeur dans la variable php $_SESSION[structSalle]
		xhr.open('POST', 'request_s3.php', true);
		xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xhr.send('coX='+varX+'&coY='+varY+'&val='+val);
	
		// ########### Modification de la classe de la case pour affichage immediat ###########
		
		// Recuperation element
		var caseTab=document.getElementById(x+'-'+y);
		
		// Modification classe
		var classe;
		switch(val)
		{
			case 1: classe='placeOk'; break;
			case 2: classe='placeHandi'; break;
			case 3: classe='placeInex'; break;
			default: break;
		}
		caseTab.className=classe;
	
}

function modifEtat(id,classe)
{
	var xy=id.split('-');
	var x=xy[0];
	var y=xy[1];
	
	if(classe!='couloir')
	{
		modifPlace(x,y,recupChoix());
	}
}

function recupChoix()
{
	var delPlace=document.getElementById('delPlace');
	var addPlace=document.getElementById('addPlace');
	var handiPlace=document.getElementById('handiPlace');
	
	if(delPlace.checked)
	{
		return 3;
	}
	else if(addPlace.checked)
	{
		return 1;
	}
	else if(handiPlace.checked)
	{
		return 2;
	}
}
