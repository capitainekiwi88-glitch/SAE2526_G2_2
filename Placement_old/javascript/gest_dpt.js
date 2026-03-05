// ################# MISE EN PLACE BLOC CREATION DEPARTEMENT #################
	var btncreadpt=document.getElementById('btncrea');
	var bloccreadpt=document.getElementById('bloccreadpt');
	
	btncreadpt.addEventListener('click', function(e) {
		bloccreadpt.style.display='';
	}, false);
	
	
// ########################## SUPPRESSION DEPARTEMENT ##########################
	function suppr_dpt(iddpt)
	{
		if(confirm('Etes vous s\373r de vouloir supprimer ce d\351partement ?'))
		{
			document.location.href='index.php?p=gest_dpt&suppr='+iddpt;	
		}
	}
	
// ########################## MODIFICATION DEPARTEMENT #########################
	function modif_dpt(id,nom)
	{
		// VARIABLE BLOC B,C,D
		var blocA=document.getElementById('A'+id);
		var blocB=document.getElementById('B'+id);
		
		// MISE EN PLACE DU FOND OPAQUE
		var blocFond=document.getElementById('fondOpaque');
		blocFond.style.display='';
		
		// MISE EN PLACE BLOC VALIDATION
		var barreValide=document.getElementById('barreValide');
		barreValide.style.display='';
		
		// MISE EN PLACE DES CHAMPS DE MODIF
		blocA.innerHTML='<input id="blocA" class="newsaisie" type="text" name="n_nom_dpt" value="'+nom+'"><input type="hidden" name="id_dpt" value='+id+'>';
		
				
		// FOCUS SUR CHAMP
		document.getElementById('blocA').focus();
	}