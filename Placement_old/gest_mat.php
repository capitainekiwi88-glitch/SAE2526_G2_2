<?php
	
	// TEST SI L'USER A APPUYE SUR ENVOYER (CREATION MAT)
	if(isset($_POST['ajouter']) && $_POST['ajouter']=="Ajouter")
	{
			$stmt = $pdo->prepare('SELECT COUNT(*) as a FROM matiere WHERE nom_mat = :nom_mat AND id_promo = :id_promo');
			$stmt->execute(['nom_mat' => $_POST['nom_mat'], 'id_promo' => $_POST['prom']]);
			$countex = $stmt->fetch(PDO::FETCH_ASSOC);

			// Test si la departement existe deja
			if ($countex['a'] == 0) {
				// Requete d'ajout
				$stmt = $pdo->prepare('INSERT INTO matiere (nom_mat, id_promo) VALUES (:nom_mat, :id_promo)');
				$stmt->execute(['nom_mat' => $_POST['nom_mat'], 'id_promo' => $_POST['prom']]);
			} else {
				echo '<script>alert("Matière déja existante")</script>';
			}
	
	}
	
	// TEST SI L'USER A APPUYER SUR SUPPRIMER (MAT)
	if(isset($_GET['suppr']))
	{
		// Requete de suppression
		$stmt = $pdo->prepare('DELETE FROM matiere WHERE id_mat = :id_mat');
		$stmt->execute(['id_mat' => $_GET['suppr']]);
	}
	
	// TEST SI L'USER A VALIDER LES MODIFICATIONS (MAT)
	if(isset($_POST['validemodif']) && $_POST['validemodif']=="Valider")
	{
		$stmt = $pdo->prepare('UPDATE matiere SET nom_mat = :nom_mat, id_promo = :id_promo WHERE id_mat = :id_mat');
		$stmt->execute([
			'nom_mat' => $_POST['n_nom_mat'],
			'id_promo' => $_POST['n_promo_mat'],
			'id_mat' => $_POST['id_mat']
		]);
	}
	
?>
<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="css/s_gest_mat.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Matières</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

	<!-- BOUTON CREATION MATIERE -->
	<div id="btncrea">Créer matière</div>
	<!-- BLOC CREATION MATIERE -->
		<div id="bloccreamat" style="display:none">
			<form action="index.php?p=gest_mat" method="post">
				<label for="nom_mat">Nom </label><input type="text" id="nom_mat" name="nom_mat"><br>
				<label for='prom'>Promotion </label><select id='prom' name='prom'>
				<?php
					$stmt = $pdo->query('SELECT * FROM promotion, departement WHERE promotion.id_dpt=departement.id_dpt ORDER BY nom_promo');
					while ($array = $stmt->fetch(PDO::FETCH_ASSOC)) {
						echo '<option value='.$array['id_promo'].'>'.$array['nom_promo'].' '.$array['nom_dpt'].' '.$array['annee'].'</option>';
					}
				?>
				</select><br>
				<input type="submit" name="ajouter" value="Ajouter">
				<input type="submit" name="annuler" value="Annuler">
			</form>
		</div>
		
<!-- ######### AFFICHAGE TITRE TABLEAU ######### -->
			<?php	
			$stmt = $pdo->query('SELECT COUNT(*) as a FROM matiere');
			$countmat = $stmt->fetch(PDO::FETCH_ASSOC);
			?>
			<div id="bloctitre"><div class="nomtitre">Matière</div><div class="nomtitre">Promotion</div></div>	
	
<!-- ######### AFFICHAGE CONTENU TABLEAU ######## -->
			<form action="index.php?p=gest_mat" method="post">
				<?php
					$stmt = $pdo->query('SELECT * FROM matiere, promotion, departement WHERE matiere.id_promo=promotion.id_promo AND promotion.id_dpt=departement.id_dpt ORDER BY nom_promo, nom_mat, nom_dpt');
					while ($tab1 = $stmt->fetch(PDO::FETCH_ASSOC))
					{
				?>
				<div class="contenutab">
					<div id="<?php echo 'A'.$tab1['id_mat'] ?>" class="nomtitre"><?php echo $tab1['nom_mat']; ?></div>
					<div id="<?php echo 'B'.$tab1['id_mat'] ?>" class="nomtitre"><?php echo $tab1['nom_promo'].' '.$tab1['nom_dpt'].' '.$tab1['annee']; ?></div>
					<div onclick="modif_mat(<?php echo $tab1['id_mat']; ?>, '<?php echo addslashes($tab1['nom_mat']); ?>');" class="blocmodif"><img class="imgmodif" src="images/set.png"></div>
					<a href="#"><div onclick="suppr_mat(<?php echo $tab1['id_mat']; ?>);" class="blocmodif"><img class="imgmodif" src="images/delete.png"></div></a>
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
	</div>
	<div id="fondOpaque" style="display:none"></div>
</div>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="javascript/gest_mat.js"></script>
<script src="javascript/onglet_gest.js"></script>

<script>
	// ########################## MODIFICATION MATIERE #########################
	function modif_mat(id,nom)
	{
		// VARIABLE BLOC B,C,D
		var blocA=document.getElementById('A'+id);
		var blocB=document.getElementById('B'+id);
		
		// MISE EN PLACE DU FOND OPAQUE
		var blocFond=document.getElementById('fondOpaque');
		blocFond.style.display='';
		
		// MISE EN PLACE BLOC VALIDATION
		var barreValide=document.getElementById('barreValide');
		barreValide.style.display='';
		
		// MISE EN PLACE DES CHAMPS DE MODIF
		blocA.innerHTML='<input id="blocA" class="newsaisie" type="text" name="n_nom_mat" value="'+nom+'"><input type="hidden" name="id_mat" value='+id+'>';
		// blocB.innerHTML='<input id="blocB" class="newsaisie" type="text" name="n_promo_mat" value="'+blocB.innerHTML+'">';
		
		blocB.innerHTML="<div id='listeDer'><select id='listeDer2' name='n_promo_mat'><?php 
		$stmt = $pdo->query('SELECT * FROM promotion, departement WHERE promotion.id_dpt=departement.id_dpt ORDER BY nom_promo'); 
		while($array = $stmt->fetch(PDO::FETCH_ASSOC)) {
			echo '<option value='.$array['id_promo'].'>'.$array['nom_promo'].' '.$array['nom_dpt'].' '.$array['annee'].'</option>';
		} 
		?></select></div>";
		
		// FOCUS SUR CHAMP
		document.getElementById('blocA').focus();
	}
</script>