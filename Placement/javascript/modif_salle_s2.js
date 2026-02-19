// ########################## ADAPTATION TAILLE FRAME #########################
function tailleFrame()
{
	var myFrame=window.top.document.getElementById('myFrame');
	var taille=document.body.scrollHeight;
	myFrame.style.height=taille;
}

window.onload=tailleFrame();