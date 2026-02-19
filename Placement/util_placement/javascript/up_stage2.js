
// Fonction d'interversion

var nbId=0;
var tabId = [];
var tabXY;

var eleveA = [];
var eleveAId;
var eleveB = [];

/**
*	Fait disparaitre le bouton intervertir, relâche les cases sélectionnées et reset les données
*/
function resetSwitch(){
	const btnInter=document.getElementById('btnInter');
	document.getElementById(tabId[0]).className='placeOk';
	document.getElementById(tabId[1]).className='placeOk';
	btnInter.style.display='none';
	nbId=0;
}

/**
*	Mise en mémoire des élèves sélectionnés pour intervertion.
*	Si les deux places sont vides (taille <=10) alors on affiche un message d'erreur et
*	on réinitialise
*/
function selecTwo(id,a,b)
{	
	const case1=document.getElementById(id);
	
	if(nbId>1){
		resetSwitch();
	}else{
		if(case1.className=='placeOk' || case1.className=='placeHandi'){
			tabId[nbId]=id;
			case1.className='placeSelec';
			nbId++;
			if(nbId==1){
				eleveA = [];
				eleveA.push(a);
				eleveA.push(b);
				eleveAId = id;
			}else if (nbId==2){
				const contenuA = document.getElementById(eleveAId);
				const contenuB = document.getElementById(id);

				if (contenuA.innerHTML.length <= 10 &&
					contenuB.innerHTML.length <= 10){

					alert("Il est impossible de déplacer deux places vides !");
					resetSwitch();
				}else{

					eleveB = [];
					eleveB.push(a);
					eleveB.push(b);
					
				}
			}
			
			
			if(nbId==2){
				const btnInter=document.getElementById('btnInter');
				btnInter.style.display='';
			}
			
		}
	}
	
}

/**
*	Mise à jour de l'échange des deux élèves.
*	On envoit les informations au serveur et si tout se passe bien (pas de données en retour), on inverse
*	sinon on affiche le message reçu
*/
function intervertir()
{
	const salleConcernee = blocOnglet1.style.display===""?0:1;
	console.log(salleConcernee)
	$.get('ajaxIntervertion.php?utilSalle='+salleConcernee+'&utilAcol='+eleveA[0]+'&utilAlin='+eleveA[1]+'&utilBcol='+eleveB[0]+'&utilBlin='+eleveB[1], function(data){
		if(data){
			console.log(data);
			console.log(data.length);
			alert(data);
		}else{
			const case0=document.getElementById(tabId[0]).innerHTML.split('<br>');
			const case1=document.getElementById(tabId[1]).innerHTML.split('<br>');
			document.getElementById(tabId[0]).innerHTML=case1[0]+'<br>'+case0[1];
			document.getElementById(tabId[1]).innerHTML=case0[0]+'<br>'+case1[1];
			
			// Cache bouton
			const btnInter=document.getElementById('btnInter');
			btnInter.style.display='none';
			
			// Remise couleur classe
			document.getElementById(tabId[0]).className='placeOk';
			document.getElementById(tabId[1]).className='placeOk';
			
			resetSwitch();
		}
	});
	
	

}