<?php
	include('connexion.php');
	
	// ################## TEST SI L'USER A APPUYE SUR SUPPRIMER (SALLE) ##################
	if(isset($_GET['delSalle']))
	{
			// Prepare the query to get the id_plan
			$stmt = $pdo->prepare("SELECT id_plan FROM salle WHERE id_salle = :id_salle");
			$stmt->execute(['id_salle' => $_GET['delSalle']]);
			$id_plan = $stmt->fetchColumn();

			if ($id_plan) {
				// Prepare the delete query
				$deleteStmt = $pdo->prepare("DELETE FROM plan WHERE id_plan = :id_plan");
				$deleteStmt->execute(['id_plan' => $id_plan]);
			}
	}
?>

<!-- ################################################################################################ -->
<!-- ########################################## CORPS PAGE ########################################## -->
<!-- ################################################################################################ -->

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="css/s_ms_stage1.css">
<link rel="stylesheet" type="text/css" href="css/s_generique.css">

<!-- BOUTON CREATION SALLE -->
	<div id="btncrea">
	  <div id="creaSalle" class="btnCreaVisu">Créer salle</div>
	</div>

<!-- ################ AFFICHAGE TITRE TABLEAU ############### -->
<?php	
	

	$countbat = $pdo->query("SELECT COUNT(*) as a FROM salle ORDER BY nom_salle")->fetchColumn();
	
	// TEST SI ON AFFICHE
	if ($countbat)
	{
?>
		<div id="bloctitre" style="margin-left: 78px">
			<div class="headNom">Nom </div>
			<div class="headBat">Bâtiment </div>
			<div class="headCap">Étage </div>
			<div class="headCap">Capacité </div>
		</div>
<?php
	}
?>

<!-- ############### AFFICHAGE CONTENU TABLEAU ############## -->
<?php
	$query1 = $pdo->query('SELECT * FROM salle 
						   JOIN plan ON salle.id_plan = plan.id_plan 
						   JOIN batiment ON salle.id_bat = batiment.id_bat 
						   JOIN departement ON salle.id_dpt = departement.id_dpt 
						   ORDER BY nom_bat, nom_salle');

	while ($tab1 = $query1->fetch(PDO::FETCH_ASSOC))
	{
?>
		<div class="contenutab">
			<div class="headNom"><?php echo $tab1['nom_salle']; ?></div>
			<div class="headBat"><?php echo $tab1['nom_bat']; ?></div>
			<div class="headCap"><?php echo $tab1['etage']; ?></div>
			<div class="headCap"><?php echo $tab1['capacite']; ?></div>
			<div id="loupe" onclick="modif_salle(<?php echo $tab1['id_plan']; ?>)" class="blocmodif"><img class="imgmodif" src="images/loupe.png"></div>
			<a href="#"><div onclick="suppr_salle(<?php echo $tab1['id_salle']; ?>);" class="blocmodif"><img class="imgmodif" src="images/delete.png"></div></a>
		</div>
<?php
	}
?>

<!-- ##################### IMPORT JAVASCRIPT ################## -->
<script src="javascript/modif_salle_s1.js"></script>
