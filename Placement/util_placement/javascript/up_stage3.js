// Adaptation taille frame

function tailleFrame()
{
	const myFrame=window.top.document.getElementById('myFrame');
	const taille=document.body.scrollHeight;
	myFrame.style.height=taille;
}

window.onload=tailleFrame();