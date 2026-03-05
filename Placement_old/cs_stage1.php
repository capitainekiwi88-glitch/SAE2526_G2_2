<?php
session_start();

// Recuperation des donnees pour un eventuel retour
if ($_SESSION['rangSalle']) {
	$nomSalle = $_SESSION['nomSalle'];
	$batSalle = $_SESSION['batSalle'];
	$dptSalle = $_SESSION['dptSalle'];
	$etageSalle = $_SESSION['etageSalle'];
	$nbRang = $_SESSION['rangSalle'];
	$nbCol = $_SESSION['colSalle'];
}

function estSelec($id1, $id2)
{
	if ($id1 == $id2) {
		return 'selected';
	}
}

?>

<!-- ################################################################################################ -->
<!-- ########################################## CORPS PAGE ########################################## -->
<!-- ################################################################################################ -->

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="css/s_stage1.css">

<!-- ######################### Titre ######################## -->
<center>
	<h1>Étape 1 : Informations salle</h1>
</center>

<!-- Formulaire structure salle -->
<label for="nomSalle">Nom </label><input id="nomSalle" class="correct" type="text"
	value="<?php echo $nomSalle; ?>"><span class="tooltip" style="display:none"> Un nom ne peut pas faire moins de 2
	caractères.</span><br>

<label for="batSalle">Bâtiment</label>
<select id="batSalle" class="correct">
	<option value="A">Bâtiment </option>
	<?php
	include('connexion.php');
	$sql = 'SELECT * FROM batiment ORDER BY nom_bat';
	$stmt = $pdo->prepare($sql);
	$stmt->execute();

	// Fetch all the rows
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	// Loop through the results
	foreach ($results as $tab1) {
		echo '<option ' . estSelec($tab1['id_bat'], $batSalle) . ' value="' . $tab1['id_bat'] . '">' . $tab1['nom_bat'] . '</option>';
	}
	?>
</select>
<span class="tooltip" style="display:none"> Aucun bâtiment n'est sélectionné;.</span>
<select id="dptSalle" class="correct" style="display:none">
	<option value="A">Département </option>
	<?php
	include('connexion.php');
	/*$query1 = mysql_query('SELECT * FROM departement ORDER BY nom_dpt');
	while ($tab1 = mysql_fetch_array($query1)) {
		echo '<option ' . estSelec($tab1['id_dpt'], $dptSalle) . ' value="' . $tab1['id_dpt'] . '">' . $tab1['nom_dpt'] . '</option>';
	}*/
	$sql = 'SELECT * FROM departement ORDER BY nom_dpt';
	$stmt = $pdo->prepare($sql);
	$stmt->execute();

	// Fetch all the rows
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	// Loop through the results
	foreach ($results as $tab1) {
		echo '<option ' . estSelec($tab1['id_dpt'], $dptSalle) . ' value="' . $tab1['id_dpt'] . '">' . $tab1['nom_dpt'] . '</option>';
	}
	?>
</select>
<span class="tooltip" style="display:none"> Aucun département n'est sélectionné;.</span>
<br>
<label for="etageSalle">Étage</label>
<select id="etageSalle" class="correct">
	<option value="A">Étage</option>
	<option <?php echo estSelec('0', $etageSalle); ?> value="0">RDC</option>
	<option <?php echo estSelec('1', $etageSalle); ?> value="1">1er</option>
	<option <?php echo estSelec('2', $etageSalle); ?> value="2">2ème</option>
	<option <?php echo estSelec('3', $etageSalle); ?> value="3">3ème</option>
	<option <?php echo estSelec('4', $etageSalle); ?> value="4">4ème</option>
</select>
<span class="tooltip" style="display:none"> Aucun &eacute;tage n'est selectionn&eacute;.</span>
<br>
<label for="nbRang">Nombre de rangs </label><input id="nbRang" class="correct" type="text"
	value="<?php echo $nbRang; ?>"><span class="tooltip" style="display:none"> Le nombre de rang doit être compris entre
	1 et 29.</span><br>
<label for="nbCol">Nombre de colonnes </label><input id="nbCol" class="correct" type="text"
	value="<?php echo $nbCol; ?>"><span class="tooltip" style="display:none"> Le nombre de colonne doit être compris
	entre 1 et 29.</span>


<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="javascript/crea_salle_s1.js"></script>