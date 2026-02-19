// ################# MISE EN PLACE BLOC CREATION ENSEIGNEMENT #################
	var btncreaens=document.getElementById('btncrea');
	var bloccreaens=document.getElementById('bloccreaens');
	
	btncreaens.addEventListener('click', function(e) {
		bloccreaens.style.display='';
	}, false);
	
	
// ########################## SUPPRESSION ENSEIGNEMENT ##########################
	function suppr_ensmat(idmat, id_ens)
	{
		if(confirm('Etes vous sûr de vouloir supprimer cet enseignement ?'))
		{
			document.location.href='index.php?p=gest_ensmat&suppr='+idmat+'&idEns='+id_ens;	
		}
	}