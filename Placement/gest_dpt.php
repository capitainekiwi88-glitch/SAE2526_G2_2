<?php
include('connexion.php');
	// TEST SI L'USER A APPUYER SUR ENVOYER (CREATION DPT)
	if(isset($_POST['ajouter']) && $_POST['ajouter']=="Ajouter")
	{
		$stmt = $pdo->prepare('SELECT COUNT(*) as a FROM departement WHERE nom_dpt = :nom_dpt');
		$stmt->execute(['nom_dpt' => $_POST['nom_dpt']]);
		$countex = $stmt->fetchColumn();
		// Test si la departement existe deja
		if($countex == 0)
		{
			// Requete d'ajout
			$stmt = $pdo->prepare('INSERT INTO departement (nom_dpt) VALUES (:nom_dpt)');
			$stmt->execute(['nom_dpt' => $_POST['nom_dpt']]);
		}
		else
		{
			echo '<script>alert("D\351partement d\351j\340 \351xistant")</script>';
		}
	}
	
	// TEST SI L'USER A APPUYER SUR SUPPRIMER (DPT)
	if(isset($_GET['suppr']))
	{
		// Requete de suppression
		$stmt = $pdo->prepare('DELETE FROM departement WHERE id_dpt = :id_dpt');
		$stmt->execute(['id_dpt' => $_GET['suppr']]);
	}
	
	// TEST SI L'USER A VALIDER LES MODIFICATIONS (DPT)
	if(isset($_POST['validemodif']) && $_POST['validemodif']=="Valider")
	{
		$stmt = $pdo->prepare('SELECT COUNT(*) FROM departement WHERE nom_dpt = :nom_dpt AND id_dpt != :id_dpt');
		$stmt->execute(['nom_dpt' => $_POST['n_nom_dpt'], 'id_dpt' => $_POST['id_dpt']]);
		$countex = $stmt->fetchColumn();
		// Test si la departement existe deja
		if($countex == 0)
		{
			$stmt = $pdo->prepare('UPDATE departement SET nom_dpt = :nom_dpt WHERE id_dpt = :id_dpt');
			$stmt->execute(['nom_dpt' => $_POST['n_nom_dpt'], 'id_dpt' => $_POST['id_dpt']]);
		}
	else
		{
			echo '<script>alert("D\351partement d\351j\340 \351xistant")</script>';
		}
	}
	
?>

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="css/s_gest_dpt.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">D&eacute;partement</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

	<!-- BOUTON CREATION DEPARTEMENT -->
	<div id="btncrea">Cr&eacute;er d&eacute;partement</div>
	<!-- BLOC CREATION DEPARTEMENT -->
	<div id="bloccreadpt" style="display:none">
		<form action="index.php?p=gest_dpt" method="post">
			<label for="nom_dpt">Nom </label><input type="text" id="nom_dpt" name="nom_dpt"><br>
			<input type="submit" name="ajouter" value="Ajouter">
			<input type="submit" name="annuler" value="Annuler">
		</form>
	</div>
	
	<!-- ######### AFFICHAGE TITRE TABLEAU ######### -->
	<?php	
		$stmt = $pdo->query('SELECT COUNT(*) as a FROM departement');
		$countdpt = $stmt->fetchColumn();
		
		// TEST SI ON AFFICHE
		if($countdpt)
		{
	?>
			<div id="bloctitre"><div class="nomtitredpt">D&eacute;partement</div></div>	
	<?php
		}
	?>
	
	<!-- ######### AFFICHAGE CONTENU TABLEAU ######## -->
	<form action="index.php?p=gest_dpt" method="post">
	<?php
		$stmt = $pdo->query('SELECT * FROM departement ORDER BY nom_dpt');
		while($tab1 = $stmt->fetch(PDO::FETCH_ASSOC))
		{
	?>
			<div class="contenutab">
				<div id="<?php echo 'A'.$tab1['id_dpt'] ?>" class="nomtitredpt"><?php echo $tab1['nom_dpt']; ?></div>
				<a href="#"><div onclick="modif_dpt(<?php echo $tab1['id_dpt']; ?>, '<?php echo $tab1['nom_dpt']; ?>');" class="blocmodif"><img class="imgmodif" src="images/set.png"></div></a>
				<a href="#"><div onclick="suppr_dpt(<?php echo $tab1['id_dpt']; ?>);" class="blocmodif"><img class="imgmodif" src="images/delete.png"></div></a>
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
<script src="javascript/gest_dpt.js"></script>
<script src="javascript/onglet_gest.js"></script>