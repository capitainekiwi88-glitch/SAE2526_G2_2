// ################# MISE EN PLACE BLOC CREATION DEPARTEMENT #################
	var btncrea=document.getElementById('btncrea');
	var bloccreabat=document.getElementById('bloccreabat');
	
	btncrea.addEventListener('click', function(e) {
		bloccreabat.style.display='';
	}, false);
	
// ########################## SUPPRESSION DEPARTEMENT ##########################
	function suppr_bat(idbat)
	{
		if(confirm('Etes vous sûr de vouloir supprimer ce bâtiment ?'))
		{
			document.location.href='index.php?p=gest_bat&suppr='+idbat;	
		}
	}
	
// ########################## MODIFICATION DEPARTEMENT #########################
	function modif_bat(id,nom)
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
		blocA.innerHTML='<input id="blocA" class="newsaisie" type="text" name="n_nom_bat" value="'+nom+'"><input type="hidden" name="id_bat" value='+id+'>';
		blocB.innerHTML='<input id="blocB" class="newsaisie" type="text" name="n_ad_bat" value="'+blocB.innerHTML+'">';
				
		// FOCUS SUR CHAMP
		document.getElementById('blocA').focus();
	}