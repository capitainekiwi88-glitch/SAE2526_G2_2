// ########################## SUPPRESSION Etudiant ##########################
	function suppr_etud(idetud,promo)
	{
		if(confirm('Etes vous sur de vouloir supprimer cet etudiant ?'))
		{
			document.location.href='index.php?p=gest_grp&suppr='+idetud;
		}
	}
	
// ########################## MODIFICATION ETUDIANT ########################
