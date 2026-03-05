// MENU DEROULANT GESTION
var btngest=document.getElementById('btngest');
var blocgest=document.getElementById('blocgest');

btngest.addEventListener('mouseover', function(e) {
	blocgest.style.display='';
	bloccompte.style.display='none';
}, false);

// MENU DEROULANT COMPTE
var btncpt=document.getElementById('btncpt');
var bloccompte=document.getElementById('bloccompte');

btncpt.addEventListener('mouseover', function(e) {
	blocgest.style.display='none';
	bloccompte.style.display='';
}, false);

// FERMETURE MENU DEROULANT
function killbloc()
{
	blocgest.style.display='none';
	bloccompte.style.display='none';
}

document.onclick=killbloc;