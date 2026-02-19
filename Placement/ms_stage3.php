<?php
	session_start();
	
	// Recuperation des donnees pour un eventuel retour
	if(isset($_SESSION['nomSalle']))
	{
		$nomSalle=$_SESSION['nomSalle'];
		$batSalle=$_SESSION['batSalle'];
		$dptSalle=$_SESSION['dptSalle'];
		$etageSalle=$_SESSION['etageSalle'];
	}
	
	function estSelec($id1, $id2)
	{
		if($id1==$id2)
		{
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
<center><h1>Modification : Informations générales</h1></center>

<!-- Formulaire structure salle -->
<label for="nomSalle">Nom </label><input id="nomSalle" class="correct" type="text" value="<?php echo $nomSalle; ?>"><span class="tooltip" style="display:none"> Un nom ne peut pas être composé de moins de 2 caractères.</span><br>
<label for="batSalle">Bâtiment </label>
	<select id="batSalle" class="correct">
		<option value="A">Bâtiment </option>
		<?php
			include('connexion.php');
			$query1 = $pdo->query('SELECT * FROM batiment ORDER BY nom_bat');
			while($tab1 = $query1->fetch(PDO::FETCH_ASSOC))
			{
				echo '<option '.estSelec($tab1['id_bat'], $batSalle).' value="'.$tab1['id_bat'].'">'.$tab1['nom_bat'].'</option>';
			}
		?>
	</select>
	<span class="tooltip" style="display:none"> Aucun bâtiment n'est selectionné.</span>

	<select id="dptSalle" style="display:none" class="correct">
		<option value="A">Département </option>
		<?php
			include('connexion.php');
			$query2 = $pdo->query('SELECT * FROM departement ORDER BY nom_dpt');
			while($tab2 = $query2->fetch(PDO::FETCH_ASSOC))
			{
				echo '<option '.estSelec($tab2['id_dpt'], $dptSalle).' value="'.$tab2['id_dpt'].'">'.$tab2['nom_dpt'].'</option>';
			}
		?>
	</select>
	<span class="tooltip" style="display:none"> Aucun département n'est selectionné.</span>
	
	<br>
	
	<label for="etageSalle">Étage </label>
	<select id="etageSalle" class="correct">
		<option value="A">Étage</option>
		<option <?php echo estSelec('0', $etageSalle); ?> value="0">RDC</option>
		<option <?php echo estSelec('1', $etageSalle); ?> value="1">1er</option>
		<option <?php echo estSelec('2', $etageSalle); ?> value="2">2ème</option>
		<option <?php echo estSelec('3', $etageSalle); ?> value="3">3ème</option>
		<option <?php echo estSelec('4', $etageSalle); ?> value="4">4ème</option>
	</select>
	<span class="tooltip" style="display:none"> Aucun étage n'est selectionné.</span>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="javascript/crea_salle_s1.js"></script>
