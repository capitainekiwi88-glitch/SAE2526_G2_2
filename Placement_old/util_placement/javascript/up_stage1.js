// Adaptation taille frame

function recalculeTailleFrame()
{
	const myFrame=window.top.document.getElementById('myFrame');
	//const taille=document.body.scrollHeight;
	myFrame.style.height=280;
}

window.onload=recalculeTailleFrame();

function affPromo()
{
	// Recuperation variables
	const listPromo=document.getElementById('promo');
	const listGr=document.getElementById('groupe');
	const listMat=document.getElementById('matiere');
	const btnAddCombi=document.getElementById('btnAddCombi');
	const listSalle=document.getElementById('salle');

	// Devoile liste groupe
	if(listPromo.value!='A')
	{
		// Devoile liste groupe
		listGr.style.display='';
		// Devoile liste matiere
		listMat.style.display='';
		// Devoile liste salle
		listSalle.style.display='';
		
		if(listSalle.value!='A' && listMat.value!='A')
		{
			btnAddCombi.style.display='';
		}
	}
	else
	{
		// Cache liste groupe
		listGr.style.display='none';
		// Cache liste matiere
		listMat.style.display='none';
		// Cache liste salle
		listSalle.style.display='none';
		// Cache bouton
		btnAddCombi.style.display='none';	
	}
}

// Affichage bouton btnAddCombi
function affBtn()
{
	const btnAddCombi=document.getElementById('btnAddCombi');
	const listSalle=document.getElementById('salle');
	const listMat=document.getElementById('matiere');
	
	if(listSalle.value!='A' && listMat.value!='A')
	{
		btnAddCombi.style.display='';
	}
	else
	{
		btnAddCombi.style.display='none';
	}
}
	

// Fonction de verification methode a utilise.
function getXhr()
{
	let xhr = null;
	if(window.XMLHttpRequest) // Firefox et autres
	   xhr = new XMLHttpRequest(); 
	else if(window.ActiveXObject)
	{ 
		// Internet Explorer 
	   	try
	   	{
	   		xhr = new ActiveXObject("Msxml2.XMLHTTP");
	   	}
	   	catch (e)
	   	{
	   		xhr = new ActiveXObject("Microsoft.XMLHTTP");
	   	}
	}
	else
	{
		// XMLHttpRequest non supporté par le navigateur 
		alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
		xhr = false; 
	} 
	return xhr;
}

// Affichage des groupes en fonction de la promo
function grDynamique()
{
	const xhr=getXhr();
	
	// On définit ce qu'on va faire quand on aura la réponse
	xhr.onreadystatechange = function(){
		// Reception et serveur ok
		if(xhr.readyState == 4 && xhr.status == 200)
		{
			leselect = xhr.responseText;
			document.getElementById('groupe').innerHTML = leselect;
		}
	}
	
	xhr.open("POST","ajaxGroupe.php",false);
	//Choix encodage
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	// Recuperation idPromo choisis
	sel=document.getElementById('promo');
	idpromo=sel.options[sel.selectedIndex].value;
	xhr.send("idPromo="+idpromo);
	// Affichage de la liste
	
	matDynamique();
	
	affPromo();
	
	document.getElementById('btnAddCombi').style.display="none";
}

// Affichage des matieres en fonction de la promo
function matDynamique()
{
	const xhr=getXhr();
	
	// On définit ce qu'on va faire quand on aura la réponse
	xhr.onreadystatechange = function(){
		// Reception et serveur ok
		if(xhr.readyState == 4 && xhr.status == 200)
		{
			leselect = xhr.responseText;
			document.getElementById('matiere').innerHTML = leselect;
		}
	}
	
	xhr.open("POST","ajaxMatiere.php",false);
	//Choix encodage
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	// Recuperation idPromo choisis
	sel=document.getElementById('promo');
	idpromo=sel.options[sel.selectedIndex].value;
	xhr.send("idPromo="+idpromo);
}

function sleep(delay)
{
	const start = new Date().getTime();
	while (new Date().getTime() < start + delay);
}

// Adaptation taille frame

function tailleFrame(plus)
{
	const myFrame=window.top.document.getElementById('myFrame');
	const taille=document.body.scrollHeight;
	if (plus) {
		myFrame.style.height = taille + 60;
	} else {
		myFrame.style.height = taille - 60;
	}

}
	
	
/**
*	On récupère les informations sur le placement courant (promo, groupe, matière et salle)
*	pour les mettre à jour sur le serveur. Si l'étape se passe sans encombre (pas de data), alors
*	on met à jour les combinaisons (partie basse de l'affichage), sinon on affiche data (erreur)
*/
function recupCombi()
{		
	// Variables
	const listPromo=document.getElementById('promo');
	const listGr=document.getElementById('groupe');
	const listMat=document.getElementById('matiere');
	const listSalle=document.getElementById('salle');

	$.post(
			"ajaxAddCombi.php",
			{
				'promo'  : listPromo.value,
				'groupe' : listGr.value,
				'matiere': listMat.value,
				'salle'  : listSalle.value
			}, function(data){
				if(data!="ok"){
					alert("erreur : "+data);
				}else{
					afficheCombi(true);
				}
			}
		);
}

function supprCombi(idCombi)
{	
		// Objet XHR
		const xhr=new XMLHttpRequest();

		// Modification de la valeur dans la variable php $_SESSION[structSalle]
		xhr.open('POST', 'ajaxSupprCombi.php', false);
		xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xhr.send("idCombi="+idCombi);
		
		// Affiche combi
		afficheCombi(false);
}

function afficheCombi(plus)
{			
	// Variables
	const tabRecap=document.getElementById('tabRecap');
	
	// Objet XHR
	const xhr=getXhr();
	
	// On définit ce qu'on va faire quand on aura la réponse
	xhr.onreadystatechange = function(){
		// Reception et serveur ok
		if(xhr.readyState == 4 && xhr.status == 200)
		{
			leselect = xhr.responseText;
			document.getElementById('tabRecap').innerHTML = leselect;
		}
	}
	xhr.open("POST","ajaxAfficheCombi.php",false);
	
	//Choix encodage
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	// Envoie des variables
	xhr.send(null);
	
	tailleFrame(plus);
	affBtnNavigation();
	
}

// ##### Affichage boutons #####

function affBtnNavigation() {

	const btnnext=parent.document.getElementById('btnnext');
		const promoChoisies = document.getElementsByClassName('supprCombi');
		if (promoChoisies.length > 0) {
			btnnext.disabled = '';
		} else {
			btnnext.disabled = 'disabled';
		}
}


// ######## GENERAL ##########

	// Modification automatique des minutes lors du choix de l'heure
	const hDeb=document.getElementById('h_deb');
	const mDeb=document.getElementById('m_deb');
	
	const hDuree=document.getElementById('h_duree');
	const mDuree=document.getElementById('m_duree');
	
	hDeb.addEventListener('change', function(e) {
		mDeb.value='00';
	}, false);
	
	hDuree.addEventListener('change', function(e) {
		mDuree.value='00';
	}, false);
	
	
