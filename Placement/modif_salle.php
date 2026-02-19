<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="css/s_modif_salle.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Visualisation salle</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">
	
	<!-- iFrame pour faire defiler les etapes -->
	<iframe id="myFrame" name="stage1" src="ms_stage1.php" scrolling="no" onload="affBtn()" style="border:none"></iframe>
	
	<!-- Bouton precedent/suivant -->
	<button type="button" id="btnBef" style="display:none; float:left; margin-left:20px;">Précédent</button>
	<button type="button" id="btnModif" style="display:none; float: right; margin-right: 20px;">Modifier</button>
	<button type="button" id="btnNext" style="display:none; float: right; margin-right: 20px;">Suivant</button>
	<button type="button" id="btnSave" style="display:none; float: right; margin-right: 20px;">Enregistrer</button>
	
	<br>
	<br>
	<br>
	<center>
		<a href="index.php?p=gest_salle"><button type="button">Quitter</button></a>
	</center>
	
</div>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="javascript/modif_salle.js"></script>