<?php	
include('connexion.php');
	// à utiliser avec str_replace pour supprimer les accents pendant la création des logins
	
	$search =explode(',','á,à,â,ä,ã,å,ç,é,è,ê,ë,í,ì,î,ï,ñ,ó,ò,ô,ö,õ,ú,ù,û,ü,ý,ÿ');
	$replace=explode(',','a,a,a,a,a,a,c,e,e,e,e,i,i,i,i,n,o,o,o,o,o,u,u,u,u,y,y');

	// TEST SI L'USER A APPUYER SUR ENVOYER (CREATION ENS)
	
	if(isset($_POST['ajouter']) && $_POST['ajouter']=="Ajouter")
	{
	
		$stmt = $pdo->prepare('SELECT COUNT(*) a FROM enseignant WHERE nom_ens = :nom_ens AND prenom_ens = :prenom_ens');
		$stmt->execute(['nom_ens' => $_POST['nom_ens'], 'prenom_ens' => $_POST['prenom_ens']]);
		$countex = $stmt->fetchColumn();
		// Test si l'enseignant existe deja
		if($countex == 0)
		{
			// Requete d'ajout
			$name=str_replace($search,$replace,strtolower($_POST['nom_ens']));
			$surname=str_replace($search,$replace,strtolower($_POST['prenom_ens']));

			$login=substr($name,0,4).substr($surname,0,4);
			
			// Generation MDP
				
				// Chaine de caracteres
				$chaine="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@";
				
				//nombre de caracteres dans le MDP
				$nb_caract=8;
				
				// Variable MDP
				$pass = "declic";
			
			$stmt = $pdo->prepare('INSERT INTO enseignant (nom_ens, prenom_ens, sexe, login, pass) VALUES (:nom_ens, :prenom_ens, :sexe, :login, :pass)');
			$stmt->execute([
				'nom_ens' => $_POST['nom_ens'],
				'prenom_ens' => $_POST['prenom_ens'],
				'sexe' => $_POST['sexe'],
				'login' => $login,
				'pass' => md5($pass)
			]);
		}
		else
		{
			echo '<script>alert("Enseignant déja existant")</script>';
		}
	}
	
	// TEST SI L'USER A APPUYE SUR SUPPRIMER (ENS)
	if(isset($_GET['suppr']))
	{
		// Requete de suppression
		$stmt = $pdo->prepare('DELETE FROM enseignant WHERE id_ens = :id_ens');
		$stmt->execute(['id_ens' => $_GET['suppr']]);
		}
		
		// TEST SI L'USER A VALIDER LES MODIFICATIONS (ENS)
		if(isset($_POST['validemodif']) && $_POST['validemodif']=="Valider")
		{
			$stmt = $pdo->prepare('UPDATE enseignant SET nom_ens = :nom_ens, prenom_ens = :prenom_ens, sexe = :sexe WHERE id_ens = :id_ens');
			$stmt->execute([
				'nom_ens' => $_POST['n_nom_ens'],
				'prenom_ens' => $_POST['n_prenom_ens'],
				'sexe' => $_POST['n_sexe'],
				'id_ens' => $_POST['id_ens']
			]);
		}
	
?>

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="css/s_gest_ens.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Enseignant</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

	<!-- BOUTON CREATION ENSEIGNANT -->
	<div id="btncrea">Créer Enseignant</div>
	<!-- BLOC CREATION ENSEIGNANT -->
	<div id="bloccreaens" style="display:none">
		<form action="index.php?p=gest_ens" method="post">
			<label for="nom_ens">Nom &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label><input type="text" id="nom_ens" name="nom_ens"><br>
			<label for="prenom_ens">Prénom </label><input type="text" id="prenom_dpt" name="prenom_ens"><br>
			<label for="sexe">Sexe </label><select id="sexe" name="sexe">
				<option value="F">Femme</option>
				<option value="M">Homme</option>
			</select><br>
			<input type="submit" name="ajouter" value="Ajouter">
			<input type="submit" name="annuler" value="Annuler">
		</form>
	</div>
	
	<!-- ######### AFFICHAGE TITRE TABLEAU ######### -->
	<?php	
		$stmt = $pdo->query('SELECT COUNT(*) a FROM enseignant');
		$countens = $stmt->fetchColumn();
		
		// TEST SI ON AFFICHE
		if($countens)
		{
	?>
	       	<div id="bloctitre"><div style="width: 210px" class="nomtitreens">Nom</div><div style="width: 210px" class="nomtitreens">Prénom</div><div style="width: 100px" class="nomtitreens">Sexe</div><div style="width: 174px" class="nomtitreens">Login</div></div>
	<?php
		}
	?>
	
	<!-- ######### AFFICHAGE CONTENU TABLEAU ######## -->
	<form action="index.php?p=gest_ens" method="post">
	<?php
		$stmt = $pdo->query('SELECT * FROM enseignant ORDER BY nom_ens');
		while($tab1 = $stmt->fetch(PDO::FETCH_ASSOC))
		{
	?>
			<div class="contenutab">
				<div style="width: 210px" id="<?php echo 'A'.$tab1['id_ens'] ?>" class="nomtitreens"><?php echo $tab1['nom_ens']; ?></div>
				<div style="width: 210px" id="<?php echo 'B'.$tab1['id_ens'] ?>" class="nomtitreens"><?php echo $tab1['prenom_ens']; ?></div>
				<div style="width: 100px" id="<?php echo 'C'.$tab1['id_ens'] ?>" class="nomtitreens"><?php echo $tab1['sexe']; ?></div>
				<div style="width: 174px" id="<?php echo 'D'.$tab1['id_ens'] ?>" class="nomtitreens"><?php echo $tab1['login']; ?></div>
			

				<a href="#"><div onclick="modif_ens(<?php echo $tab1['id_ens']; ?>, '<?php echo $tab1['nom_ens']; ?>');" class="blocmodif"><img class="imgmodif" src="images/set.png"></div></a>
				<a href="#"><div onclick="suppr_ens(<?php echo $tab1['id_ens']; ?>);" class="blocmodif"><img class="imgmodif" src="images/delete.png"></div></a>
			</div>
	<?php
		}
	?>			
				
		<div id="barreValide" style="display:none">
			<input id="bouttonmodif" type="submit" name="validemodif" value="Valider">
			<input id="bouttonmodif" type="submit" name="annuler" value="Annuler">
		</div>
		
	</form>
	<a href="export_pdf_ens.php" target="_blank" style="text-decoration: none; color:black;"><img width="30px" height="30px" src="images/iconpdf.jpg"></img></a>
	
</div>

<div id="fondOpaque" style="display:none"></div>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="javascript/gest_ens.js"></script>
<script src="javascript/onglet_gest.js"></script>