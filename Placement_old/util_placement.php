<?php
	// Reinitialisation informations
	$_SESSION['dateDevoir']='';
	$_SESSION['hDevoir']='';
	$_SESSION['mDevoir']='';
	$_SESSION['hDuree']='';
	$_SESSION['mDuree']='';
?>
	
	<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="css/s_util_placement.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Placement</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

	<!-- iFrame pour faire defiler les etapes -->
	<iframe id="myFrame" name="stage1" src="util_placement/up_stage1.php" scrolling="yes" style="height:280px;border: none"></iframe>
	
	<!-- Bouton precedent/suivant -->
	<button type="button" id="btnbef" style="display:none; float:left; margin-left:20px;">Précédent</button>
	<button type="button" id="btnnext" style="float: right; margin-right: 20px;">Suivant</button>

</div>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="util_placement/javascript/util_placement.js"></script>
