function liste_etud(promo) {
	document.location.href='index.php?p=etudiant&promo='+promo;
}
	
function suppr_promo(idpromo) {
	
	if(confirm('Êtes-vous sûr de vouloir supprimer cette promotion ?')) {
		document.location.href='index.php?p=gest_promo&suppr='+idpromo;	
	}
}
	

function affiche_etud(idpromo) {
	document.location.href='index.php?p=gest_grp&promo='+idpromo;
}

 // ################# MISE EN PLACE BLOC CREATION PROMOTION #################
/*	var btncreapromo=document.getElementById('btncreapromo');
	var bloccreapromo=document.getElementById('bloccreapromo');
	
	btncreapromo.addEventListener('click', function(e) {
		bloccreapromo.style.display='';
	}, false);

// ################# MISE EN PLACE BLOC CREATION GROUPE #################
	var btncreagrp=document.getElementById('btncreagrp');
	var bloccreagrp=document.getElementById('bloccreagrp');
	
	btncreagrp.addEventListener('click', function() {
		bloccreagrp.style.display='';
	}, false);
*/	
