<?php
	// Reinitialisation informations
	$_SESSION['nomSalle']="";
	$_SESSION['rangSalle']="";
	$_SESSION['colSalle']="";
?>

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="css/s_crea_salle.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Création salle</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

	<!-- iFrame pour faire defiler les etapes -->
	<iframe id="myFrame" name="stage1" src="cs_stage1.php" scrolling="no" style="border:none"></iframe>
	
	<!-- Bouton precedent/suivant -->
	<button type="button" id="btnbef" style="display:none; float:left; margin-left:20px;">Précédent</button>
	<button type="button" id="btnnext" style="float: right; margin-right: 20px;">Suivant</button>
	<button type="button" id="btnsave" style="display:none; float: right; margin-right: 20px;">Enregistrer</button>
	
	<br>
	<br>
	<br>
	<center>
		<a href="index.php?p=gest_salle"><button type="button">Quitter</button></a>
	</center>
	
</div>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="javascript/crea_salle.js"></script>
