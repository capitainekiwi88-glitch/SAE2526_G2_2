<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="css/s_gest_salle.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Salle</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

	<!-- BLOC AFFICHAGE LISTE SALLES -->
	
	<!-- iFrame pour faire defiler les etapes -->
	<iframe id="myFrame" name="stage1" src="ms_stage1.php" scrolling="no" onload="affBtn()" style="border:none"></iframe>
	
	<!-- Bouton precedent/suivant -->
	<button type="button" id="btnBef" style="display:none; float:left; margin-left:20px;">Précédent</button>
	<button type="button" id="btnModif" style="display:none; float: right; margin-right: 20px;">Modifier</button>
	<button type="button" id="btnNext" style="display:none; float: right; margin-right: 20px;">Suivant</button>
	<button type="button" id="btnSave" style="display:none; float: right; margin-right: 20px;">Enregistrer</button>
	<br>
</div>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="javascript/crea_salle.js"></script>
<script src="javascript/modif_salle.js"></script>
<script src="javascript/onglet_gest.js"></script>

</div>

<div id="fondOpaque" style="display:none"></div>


<!-- *********************** CODE ORIGINAL DE WILL ****************** -->

<!-- ##################### CONTENU PAGE ##################### -->
<!--
<div id="blocPrincipal" class="contenu">
	<a href="index.php?p=crea_salle" id="linkdec"><div id="creaSalle" class="btnCreaVisu">Créer salle</div></a>
	<a href="index.php?p=modif_salle" id="linkdec"><div id="visuSalle" class="btnCreaVisu">Visualiser salle</div></a>
</div>
<script src="javascript/gest_salle.js"></script>
<script src="javascript/onglet_gest.js"></script>	
-->

