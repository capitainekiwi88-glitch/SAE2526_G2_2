// ################# MISE EN PLACE BLOC CREATION DEPARTEMENT #################
	var btncrea=document.getElementById('btncrea');
	var bloccreamat=document.getElementById('bloccreamat');
	
	btncrea.addEventListener('click', function(e) {
		bloccreamat.style.display='';
	}, false);
	
	// ########################## SUPPRESSION DEPARTEMENT ##########################
	function suppr_mat(idmat)
	{
		if(confirm('Etes vous sûr de vouloir supprimer cette matière ?'))
		{
			document.location.href='index.php?p=gest_mat&suppr='+idmat;	
		}
	}
	
