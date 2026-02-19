<?php
	include('connexion.php');
	// TEST SI L'USER A APPUYER SUR ENVOYER (CREATION BAT)
	if(isset($_POST['ajouter']) && $_POST['ajouter']=="Ajouter")
	{
		
			$countex = $pdo->prepare('SELECT COUNT(*) as a FROM batiment WHERE nom_bat = :nom_bat');
			$countex->execute(['nom_bat' => $_POST['nom_bat']]);
			$countResult = $countex->fetch(PDO::FETCH_ASSOC);
			
			// Test si le bâtiment existe déjà
			if ($countResult['a'] == 0) {
				// Requête d'ajout
				$insert = $pdo->prepare('INSERT INTO batiment (nom_bat, ad_bat) VALUES (:nom_bat, :ad_bat)');
				$insert->execute(['nom_bat' => $_POST['nom_bat'], 'ad_bat' => $_POST['ad_bat']]);
			} else {
				echo '<script>alert("Bâtiment déjà existant")</script>';
			}
		
	}
	
	// TEST SI L'USER A APPUYER SUR SUPPRIMER (BAT)
	if(isset($_GET['suppr']))
	{
		// Requete de suppression
		$suppr = $pdo->prepare('DELETE FROM batiment WHERE id_bat = :id_bat');
		$suppr->execute(['id_bat' => $_GET['suppr']]);
	}
	
	// TEST SI L'USER A VALIDER LES MODIFICATIONS (BAT)
	if(isset($_POST['validemodif']) && $_POST['validemodif']=="Valider")
	{
		$update = $pdo->prepare('UPDATE batiment SET nom_bat = :nom_bat, ad_bat = :ad_bat WHERE id_bat = :id_bat');
		$update->execute([
			'nom_bat' => $_POST['n_nom_bat'],
			'ad_bat' => $_POST['n_ad_bat'],
			'id_bat' => $_POST['id_bat']
		]);
	}
	
?>

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="css/s_gest_bat.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Bâtiment</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

	<!-- BOUTON CREATION BATIMENT -->
	<div id="btncrea">Créer bâtiment</div>
	<!-- BLOC CREATION BATIMENT -->
	<div id="bloccreabat" style="display:none">
		<form action="index.php?p=gest_bat" method="post">
			<label for="nom_bat">Nom &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </label><input type="text" id="nom_bat" name="nom_bat"><br>
			<label for="ad_bat">Adresse </label><input type="text" id="ad_bat" name="ad_bat"><br>
			<input type="submit" name="ajouter" value="Ajouter">
			<input type="submit" name="annuler" value="Annuler">
		</form>
	</div>
	
	<!-- ######### AFFICHAGE TITRE TABLEAU ######### -->
	<?php	
		$countbat = $pdo->query('SELECT COUNT(*) as a FROM batiment');
		$countResult = $countbat->fetch(PDO::FETCH_ASSOC);
		
		// TEST SI ON AFFICHE
		if ($countResult['a'])
		{
	?>
			<div id="bloctitre"><div class="nomtitre">B&acirc;timent</div><div class="nomtitre">Adresse</div></div>
	<?php
		}
	?>
	
	<!-- ######### AFFICHAGE CONTENU TABLEAU ######## -->
	<form action="index.php?p=gest_bat" method="post">
	<?php
		$query1 = $pdo->query('SELECT * FROM batiment ORDER BY nom_bat');
		while ($tab1 = $query1->fetch(PDO::FETCH_ASSOC))
		{
	?>
			<div class="contenutab">
				<div id="<?php echo 'A'.$tab1['id_bat'] ?>" class="nomtitre"><?php echo $tab1['nom_bat']; ?></div>
				<div id="<?php echo 'B'.$tab1['id_bat'] ?>" class="nomtitre"><?php echo $tab1['ad_bat']; ?></div>
				<div onclick="modif_bat(<?php echo $tab1['id_bat']; ?>, '<?php echo utf8_decode($tab1['nom_bat']); ?>');" class="blocmodif"><img class="imgmodif" src="images/set.png"></div>
				<a href="#"><div onclick="suppr_bat(<?php echo $tab1['id_bat']; ?>);" class="blocmodif"><img class="imgmodif" src="images/delete.png"></div></a>
			</div>
	<?php
		}
	?>
	
		<div id="barreValide" style="display:none">
			<input id="bouttonmodif" type="submit" name="validemodif" value="Valider">
			<input id="bouttonmodif" type="submit" name="annuler" value="Annuler">
		</div>
		
	</form>	
	
</div>

<div id="fondOpaque" style="display:none"></div>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="javascript/gest_bat.js"></script>
<script src="javascript/onglet_gest.js"></script>