// ########################## ADAPTATION TAILLE FRAME #########################
function tailleFrame()
{
	var myFrame=window.top.document.getElementById('myFrame');
	var taille=document.body.scrollHeight;
	myFrame.style.height=taille;
}

// ############################# APPUIE SUR LOUPE ############################
function modif_salle(id)
{
	var myFrame=window.top.document.getElementById("myFrame");
	
	myFrame.name='stage2';
	myFrame.src='ms_stage2.php?var1='+id;
}
	
// ########################## SUPPRESSION DEPARTEMENT ##########################
function suppr_salle(idSalle)
{
	if(confirm('Etes vous sûr de vouloir supprimer cette salle ?'))
	{
		document.location.href='ms_stage1.php?delSalle='+idSalle;	
	}
}

window.onload=tailleFrame();

var frame=window.top.document.getElementById('myFrame');
var btnCrea=document.getElementById('btncrea');

btnCrea.addEventListener('click', function(e) {
  window.top.document.location="index.php?p=crea_salle";
}, false);


