// ################# MISE EN PLACE BLOC CREATION DEPARTEMENT #################
	var btncreaens=document.getElementById('btncrea');
	var bloccreaens=document.getElementById('bloccreaens');
	
	btncreaens.addEventListener('click', function(e) {
		bloccreaens.style.display='';
	}, false);
	
	
// ########################## SUPPRESSION DEPARTEMENT ##########################
	function suppr_ens(idens)
	{
		if(confirm('Etes vous sûr de vouloir supprimer cet enseignant ?'))
		{
			document.location.href='index.php?p=gest_ens&suppr='+idens;	
		}
	}
	
// ########################## MODIFICATION DEPARTEMENT #########################
	function modif_ens(id,nom)
	{
		// VARIABLE BLOC B,C,D
		var blocA=document.getElementById('A'+id);
		var blocB=document.getElementById('B'+id);
		var blocC=document.getElementById('C'+id);
		
		// MISE EN PLACE DU FOND OPAQUE
		var blocFond=document.getElementById('fondOpaque');
		blocFond.style.display='';
		
		// MISE EN PLACE BLOC VALIDATION
		var barreValide=document.getElementById('barreValide');
		barreValide.style.display='';
		
		// MISE EN PLACE DES CHAMPS DE MODIF
		blocA.innerHTML='<input style="width: 210px" id="blocA" class="newsaisie" type="text" name="n_nom_ens" value="'+nom+'"><input type="hidden" name="id_ens" value='+id+'>';
		blocB.innerHTML='<input style="width: 210px" id="blocB" class="newsaisie" type="text" name="n_prenom_ens" value="'+blocB.innerHTML+'"><input type="hidden" id="iddpt" name="iddpt" value='+id+'>';
	    blocC.innerHTML='<div style="width: 100px" id="listeDer"><select style="width: 100px" id="listeDer2" name="n_sexe"><option value="M">Masculin</option><option value="F">F&eacute;minin</option></select></div>';
				
		// FOCUS SUR CHAMP
		document.getElementById('blocA').focus();
	}