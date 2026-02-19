<?php
	include('connexion.php');
	// TEST SI L'USER A APPUYE SUR ENVOYER (CREATION ENS)
	if(isset($_POST['ajouter']) && $_POST['ajouter']=="Ajouter")
	{
			$countex = $pdo->prepare('SELECT COUNT(*) as a FROM enseigne WHERE id_ens = :id_ens AND id_mat = :id_mat');
			$countex->execute(['id_ens' => $_POST['ens'], 'id_mat' => $_POST['mat']]);
			$countResult = $countex->fetch(PDO::FETCH_ASSOC);

			// Test if it already exists
			if ($countResult['a'] == 0) {
				// Insert query
				$insert = $pdo->prepare('INSERT INTO enseigne (id_mat, id_ens) VALUES (:id_mat, :id_ens)');
				$insert->execute(['id_mat' => $_POST['mat'], 'id_ens' => $_POST['ens']]);
			} else {
				echo '<script>alert("Couple enseignant/matière déjà existant")</script>';
			}
	}
	
	// TEST SI L'USER A APPUYER SUR SUPPRIMER (ENS)
	if(isset($_GET['suppr']))
	{
		// Requete de suppression
		$suppr = $pdo->prepare('DELETE FROM enseigne WHERE id_mat = :id_mat AND id_ens = :id_ens');
		$suppr->execute(['id_mat' => $_GET['suppr'], 'id_ens' => $_GET['idEns']]);
	}
	
	// TEST SI L'USER A VALIDER LES MODIFICATIONS (ENS)
	if(isset($_POST['validemodif']) && $_POST['validemodif']=="Valider")
	{
		$update = $pdo->prepare('UPDATE enseigne SET nom_ens = :nom_ens, prenom_ens = :prenom_ens, sexe = :sexe WHERE id_ens = :id_ens');
		$update->execute([
			'nom_ens' => $_POST['n_nom_ens'],
			'prenom_ens' => $_POST['n_prenom_ens'],
			'sexe' => $_POST['n_sexe'],
			'id_ens' => $_POST['id_ens']
		]);
	}
	
?>

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="css/s_gest_ensmat.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Enseignement</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

	<!-- BOUTON CREATION ENSEIGNEMENT -->
	<div id="btncrea">Créer Enseignement</div>
	<!-- BLOC CREATION ENSEIGNEMENT -->
	<div id="bloccreaens" style="display:none">
		<form action="index.php?p=gest_ensmat" method="post">
			
			<label for="ens">Nom </label><select id="ens" name="ens">
			<?php
				$nom_ens = $pdo->query('SELECT * FROM enseignant ORDER BY nom_ens');
				while ($array = $nom_ens->fetch(PDO::FETCH_ASSOC)) {
					echo '<option value='.$array['id_ens'].'>'.$array['nom_ens'].' '.$array['prenom_ens'].'</option>';
				}
			?>
			</select><br>
			
			<label for="mat">Matière </label><select id="mat" name="mat">
			<?php
				$nom_mat = $pdo->query('SELECT * FROM matiere, promotion, departement WHERE matiere.id_promo=promotion.id_promo AND promotion.id_dpt=departement.id_dpt ORDER BY nom_promo, nom_mat');
				while ($array = $nom_mat->fetch(PDO::FETCH_ASSOC))
				{
					echo '<option value='.$array['id_mat'].'>'.$array['nom_promo'].' '.$array['nom_dpt'].' - '.$array['nom_mat'].'</option>';
				}
			?>
			</select><br>
			
			<input type="submit" name="ajouter" value="Ajouter">
			<input type="submit" name="annuler" value="Annuler">
		</form>
	</div>
	
	
	<!-- ######### AFFICHAGE TITRE TABLEAU ######### -->
	<?php	
		$countens = $pdo->query('SELECT COUNT(*) as a FROM enseignant');
		$countResult = $countens->fetch(PDO::FETCH_ASSOC);

		// TEST SI ON AFFICHE
		if ($countResult['a'])
		{
	?>
	       	<!-- <div id="bloctitre" style="width: 740px"><div style="width: 349px" class="nomtitreens">Nom Prénom</div><div style="width: 386px" class="nomtitreens">Matière</div></div>-->
		<div id="bloctitre" style="width: 740px"><div style="width: 349px" class="nomtitreens">Nom Prénom</div><div style="width: 300px" class="nomtitreens">Matière</div>
		<div style="width: 85px" class="nomtitreens">Promotion</div></div>

	<?php
		}
	?>
	

	<!-- ######### AFFICHAGE CONTENU TABLEAU ######## -->
	<form action="index.php?p=gest_ensmat" method="post">
		
	<?php
		$query1 = $pdo->query('SELECT * FROM enseigne, enseignant, matiere, promotion WHERE enseigne.id_ens=enseignant.id_ens AND matiere.id_mat=enseigne.id_mat AND promotion.id_promo = matiere.id_promo 
			ORDER BY nom_ens, nom_promo, nom_mat');
		while ($tab1 = $query1->fetch(PDO::FETCH_ASSOC))
		{
	?>
			<div class="contenutab">
				<div style="width: 349px" id="<?php echo 'A'.$tab1['id_ens'] ?>" class="nomtitreens"><?php echo $tab1['nom_ens'].' '.$tab1['prenom_ens'];?></div>
				<div style="width: 300px" id="<?php echo 'B'.$tab1['id_ens'] ?>" class="nomtitreens"><?php echo $tab1['nom_mat'] ?></div> 
				<div style="width: 85px" id="<?php echo 'B'.$tab1['id_ens'] ?>" class="nomtitreens"><?php echo $tab1['nom_promo']; ?></div> 

				<a href="#"><div onclick="suppr_ensmat(<?php echo $tab1['id_mat']; ?>, <?php echo $tab1['id_ens']; ?>);" class="blocmodif"><img class="imgmodif" src="images/delete.png"></div></a>
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
<script src="javascript/gest_ensmat.js"></script>
