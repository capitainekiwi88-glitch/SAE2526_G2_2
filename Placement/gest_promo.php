<?php

	// ****************************************************
	//			Création d'une promo (nom - année - dpt)
	// ****************************************************
		
	// TEST SI L'USER A APPUYE SUR ENVOYER (CREATION PROMO)

	if(isset($_POST['envoyer']) && $_POST['envoyer']=="Ajouter promotion")
	{
			$stmt = $pdo->prepare('SELECT COUNT(*) as a 
								   FROM promotion 
								   WHERE nom_promo = :nom_promo 
								   AND annee = :annee 
								   AND id_dpt = :id_dpt');
			$stmt->execute([
				'nom_promo' => $_POST['nom_promo'],
				'annee' => $_POST['annee'],
				'id_dpt' => $_POST['id_dpt']
			]);

			$result = $stmt->fetch(PDO::FETCH_ASSOC);

			// Test si la promotion existe deja
			if ($result['a'] == 0) {
				// Requete d'ajout
				$stmt = $pdo->prepare('INSERT INTO promotion (nom_promo, annee, id_dpt)  
									   VALUES (:nom_promo, :annee, :id_dpt)');
				$stmt->execute([
					'nom_promo' => $_POST['nom_promo'],
					'annee' => $_POST['annee'],
					'id_dpt' => $_POST['id_dpt']
				]);
			}
		else
		{
			echo '<script>alert("Promotion déjà existante")</script>';
		}
	}

	// ****************************************************
	//			Création d'un groupe (nom - promo)
	// => modif et suppression de groupe : gest_groupe.php
	// ****************************************************	
	
   // TEST SI L'USER A APPUYE SUR ENVOYER (CREATION GROUPE)

	if(isset($_POST['envoyer']) && $_POST['envoyer']=="Ajouter groupe")
	{
		$stmt = $pdo->prepare('SELECT COUNT(*) as a 
							   FROM groupe 
							   WHERE nom_groupe = :nom_groupe 
							   AND id_promo = :id_promo');
		$stmt->execute([
			'nom_groupe' => $_POST['nom_groupe'],
			'id_promo' => $_POST['id_promo']
		]);

		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		// Test si le groupe existe deja
		if ($result['a'] == 0) {
			// Requete d'ajout
			$stmt = $pdo->prepare('INSERT INTO groupe (nom_groupe, id_promo, nb_etud)  
								   VALUES (:nom_groupe, :id_promo, 0)');
			$stmt->execute([
				'nom_groupe' => $_POST['nom_groupe'],
				'id_promo' => $_POST['id_promo']
			]);
		}
		else
		{
			echo '<script>alert("Groupe déjà existant")</script>';
		}
	}
	
	// ****************************************************
	//			Suppression d'une promo
	// ****************************************************	
	
	// TEST SI L'USER A APPUYE SUR SUPPRIMER (Promo)
	
	if(isset($_GET['suppr']))
	{
		// Requete de suppression
		$stmt = $pdo->prepare('DELETE FROM promotion WHERE id_promo = :id_promo');
		$stmt->execute(['id_promo' => $_GET['suppr']]);
	}
		// ****************************************************
	//			Modification d'une promo
	// ****************************************************
	
	// TEST SI L'USER A VALIDE LES MODIFICATIONS (Promo)
	
	if(isset($_POST['validemodif']) && $_POST['validemodif']=="Valider")
	{
		$stmt = $pdo->prepare('SELECT COUNT(*) as a 
							   FROM promotion 
							   WHERE nom_promo = :n_nom_promo 
							   AND annee = :n_annee 
							   AND id_dpt = :n_id_dpt 
							   AND NOT EXISTS 
									(SELECT * 
									 FROM departement 
									 WHERE id_promo = :id_promo)');
		$stmt->execute([
			'n_nom_promo' => $_POST['n_nom_promo'],
			'n_annee' => $_POST['n_annee'],
			'n_id_dpt' => $_POST['n_id_dpt'],
			'id_promo' => $_POST['id_promo']
		]);

		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		// Test si la promotion existe deja
		if ($result['a'] == 0) {
			$stmt = $pdo->prepare('UPDATE promotion 
								   SET nom_promo = :n_nom_promo, id_dpt = :n_id_dpt, annee = :n_annee 
								   WHERE id_promo = :id_promo');
			$stmt->execute([
				'n_nom_promo' => $_POST['n_nom_promo'],
				'n_id_dpt' => $_POST['n_id_dpt'],
				'n_annee' => $_POST['n_annee'],
				'id_promo' => $_POST['id_promo']
			]);
		}
		else
		{
			echo '<script>alert("Promotion déjà existante")</script>';
		}
	}
	
	// ****************************************************************
	//			Importation d'une promo (à partir d'un fichier .csv)
	// ****************************************************************
	
	// TEST SI L'USER A VALIDE L'IMPORTATION (Promo)

	if(isset($_POST['envoiefic']) && $_POST['envoiefic']=="Importer promotion")
	{
		
		$id_promo = $_POST['id_promo'];	
		
		// on supprime les étudiants de la promo
		// ************ ATTENTION : faire une demande de confirmation !!!! ************						
		$reqNbEtud = $pdo->prepare('SELECT COUNT(*) as a 
								FROM groupe, etudiant 
								WHERE groupe.id_groupe = etudiant.id_groupe 
								AND id_promo = :id_promo');	
		$reqNbEtud->execute(['id_promo' => $id_promo]);
		$nbEtudResult = $reqNbEtud->fetch(PDO::FETCH_ASSOC);
								
		if ($nbEtudResult['a']) 
		{
			$deleteEtud = $pdo->prepare('DELETE 
						FROM etudiant 
						WHERE id_groupe IN
						(SELECT id_groupe
							FROM groupe
							WHERE id_promo = :id_promo)');
			$deleteEtud->execute(['id_promo' => $id_promo]);
		}
		
		// on récupère le nb de groupes dans la promo
		$reqCountgrp = $pdo->prepare('SELECT COUNT(*) as a 
							FROM groupe, promotion 
							WHERE groupe.id_promo = promotion.id_promo 
							AND promotion.id_promo = :id_promo');
		$reqCountgrp->execute(['id_promo' => $id_promo]);
		$nbgrpResult = $reqCountgrp->fetch(PDO::FETCH_ASSOC);
							
		$nbgrp = $nbgrpResult['a'];

		// on récupère les identifiants des groupes correspondants 
		$idGroupes = array();
		
		$reqTabGroupes = $pdo->prepare('SELECT id_groupe, nom_groupe 
								FROM groupe, promotion 
								WHERE groupe.id_promo = promotion.id_promo 
								AND promotion.id_promo = :id_promo 
								ORDER BY nom_groupe');				
		$reqTabGroupes->execute(['id_promo' => $id_promo]);

		$groupes = 0;
		while ($row = $reqTabGroupes->fetch(PDO::FETCH_ASSOC)) 
		// on ne garde que l'identifiant du groupe pour chaque numéro (1,2, ...) pour la comparaison avec le fichier CSV		
		{
			$idGroupes[substr($row["nom_groupe"], -1)] = $row["id_groupe"];
			$groupes++;
		}
		if ($groupes==0) {
			echo '<script>alert("Pas de groupe pour cette promotion, import impossible !")</script>';

		}	else {
			ini_set("auto_detect_line_endings", true);    // pour mac

			$ok = 0;

			// on ouvre le fichier CSV
			rename($_FILES['myFile']['tmp_name'], $_FILES['myFile']['name']);
			$fp = fopen($_FILES['myFile']['tmp_name'], "r");

			// on purge le début de fichier (nom promo, groupe etc.)
			/*for ($i = 1; $i < 10; $i++) {
				$ligne = fgets($fp, 100);
			}*/
			// on attaque la liste des étudiants
			$ligne = 1; // compteur de ligne

			while ($tab = fgetcsv($fp, 200, ';')) {
				//echo '<script>alert("in'.count($tab).'")</script>';

				$champs = count($tab);//nombre de champs dans la ligne en question
				$ligne++;
				// on saute la ligne d'entête
				if ($ligne==2) {
					continue;
				}
				// on prend ligne par ligne, à partir de la colonne contenant le 1e num groupe
				for ($i = 1; $i < $champs; $i++) {
					// on récupère l'id_groupe de l'étudiant dans la BdD
					if ($i % 4 == 1 && $tab[$i] != '') {
						$groupe = $idGroupes[substr($tab[$i], 0, 1)];
					} // on récupère le nom
					elseif ($i % 4 == 2 && $tab[$i] != '')
						$nom = mb_convert_encoding($tab[$i], 'UTF-8', 'ISO-8859-1');
					// on récupère le prénom, ensuite on insère tout dans la BdD et incrémente le nb_etud
					elseif ($i % 4 == 3 && $tab[$i] != '') {
						$prenom = mb_convert_encoding($tab[$i], 'UTF-8', 'ISO-8859-1');

						$stmt = $pdo->prepare('INSERT INTO etudiant (nom_etudiant, prenom_etudiant, id_groupe)  
									VALUES (:nom, :prenom, :groupe)');
						$stmt->execute([
							'nom' => $nom,
							'prenom' => $prenom,
							'groupe' => $groupe
						]);

						$stmt = $pdo->prepare('SELECT nb_etud 
											FROM groupe 
											WHERE id_groupe = :groupe');
						$stmt->execute(['groupe' => $groupe]);
						$nb_etud = $stmt->fetch(PDO::FETCH_ASSOC);
						$nb_etudiant = $nb_etud['nb_etud'] + 1;
						$ok++;

						$stmt = $pdo->prepare('UPDATE groupe 
									SET nb_etud = :nb_etudiant 
									WHERE id_groupe = :groupe');
						$stmt->execute([
							'nb_etudiant' => $nb_etudiant,
							'groupe' => $groupe
						]);
					}

				}
			}


			fclose($fp);
			if ($ok > 0)
				echo '<script>alert("La promotion a bien été importée : ' . $ok . ' étudiants importés.")</script>';
			else
				echo '<script>alert("Aucun étudiant importé : fichier non conforme !")</script>';
		}
	}
?>

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="css/s_gest_promo.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Promotion</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

<div id="blocOnglet">
	<div id="onglet1" class="onglet" style="background: gray">Créer promotion</div>
	<div id="onglet2" class="onglet2" style="background: gray">Créer groupe</div>
	<div id="onglet3" class="onglet2" style="background: gray">Importer promotion</div>
</div>

<div id="blocOnglet1" class="contenuonglet" style="display: none">
	<form action="index.php?p=gest_promo" method="post">
			<label for="nom_promo">Nom  &nbsp;&nbsp; </label><input type="text" id="nom_promo" name="nom_promo"><br>
			<label for="annee">Année </label><input type="text" id="annee" name="annee"><br>			
			<label for='nom_dpt'>Département </label><select id='id_dpt' name='id_dpt'>
				<?php 
				$stmt = $pdo->query('SELECT * FROM departement');
				while($array = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					echo '<option value='.$array['id_dpt'].'>'.$array['nom_dpt'].'</option>';
				} 
				?>
				?>
			</select><br>
			<input type="submit" name="envoyer" value="Ajouter promotion">
			<input type="submit" name="annuler" value="Annuler">
		</form>
</div>

<div id="blocOnglet2" class="contenuonglet" style="display: none">
		<form action="index.php?p=gest_promo" method="post">
			<label for="nom_promo">Nom </label><input type="text" id="nom_groupe" name="nom_groupe"><br>
			<label for='nom_dpt'>Promotion </label><select id='id_promo' name='id_promo'>
				<?php 
				$stmt = $pdo->query('SELECT * FROM promotion p, departement d WHERE p.id_dpt=d.id_dpt ORDER BY nom_promo');
				while($array = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					echo '<option value='.$array['id_promo'].'>'.$array['nom_promo'].' '.$array['annee'].' '.$array['nom_dpt'].'</option>';
				} 
				?>
				?>
			</select><br>
			<input type="submit" name="envoyer" value="Ajouter groupe">
			<input type="submit" name="annuler" value="Annuler">
		</form>
</div>

<div id="blocOnglet3" class="contenuonglet" style="display: none">

<p style="text-align:left;margin-left:15px">
	Le fichier doit correspondre au schéma suivant, à partir de la ligne 1 :<br/>
	Num;Groupe;Nom;Prenom<br/>
	1;1;ALLARD;Martin<br/>
	2;1;BASSAN;Bastien<br/>
	<br/>
	avec répétition éventuelle (groupe en colonne 2(B), 7(G), 12(L), etc.)<br/>

</p>
	<!-- IMPORT promotion VIA FICHIER -->
		<form action="index.php?p=gest_promo" method="post" enctype="multipart/form-data">
		<label for='nom_dpt'>Département </label><select id='id_dpt' name='id_dpt'>
		
			<?php 
			$stmt = $pdo->query('SELECT * FROM departement ORDER BY nom_dpt');
			while($array = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				echo '<option value='.$array['id_dpt'].'>'.$array['nom_dpt'].'</option>';
			} 
			?>
		</select><br>
			
		<label for='nom_dpt'>Promotion </label><select id='id_promo' name='id_promo'>
			<?php 
			$stmt = $pdo->query('SELECT * 
								 FROM promotion p, departement d 
								 WHERE p.id_dpt=d.id_dpt 
								 ORDER BY nom_promo');
			while($array = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				echo '<option value='.$array['id_promo'].'>'.$array['nom_promo'].' '.$array['annee'].'</option>';
			} 
			?>
		</select><br>
			
			<input type="file" name="myFile"><br><br>
			<input type="submit" name="envoiefic" value="Importer promotion">
			<input type="submit" name="annuler" value="Annuler">
		</form>
</div>

<div>
	<!-- BOUTON CREATION PROMOTION -->

	
</div>	
	
	<!-- ######### AFFICHAGE TITRE TABLEAU ######### -->
	<?php	
		$stmt = $pdo->query('SELECT COUNT(*) as a FROM promotion');
		$countpromo = $stmt->fetch(PDO::FETCH_ASSOC);
		
		// TEST SI ON AFFICHE
		if($countpromo['a'])
		{
	?>
			<div id="bloctitrepromo"><div class="nomtitrepromo">Promotion</div><div class="nomtitrepromo">Département</div><div class="nomtitrepromo">Année</div></div>
	<?php
		}
	?>
	
	<!-- ######### AFFICHAGE CONTENU TABLEAU ######## -->
	<form action="index.php?p=gest_promo" method="post">
	<?php
		$stmt = $pdo->query('SELECT * FROM promotion p, departement d WHERE p.id_dpt = d.id_dpt ORDER BY nom_promo');
		while($tab1 = $stmt->fetch(PDO::FETCH_ASSOC))
		{
	?>
			<div class="contenutabpromo">
				<div id="<?php echo 'A'.$tab1['id_promo'] ?>" class="nomtitrepromo"><?php echo $tab1['nom_promo']; ?></div>
				<div id="<?php echo 'B'.$tab1['id_promo'] ?>" class="nomtitrepromo"><?php echo $tab1['nom_dpt']; ?></div>
				<div id="<?php echo 'C'.$tab1['id_promo'] ?>" class="nomtitrepromo"><?php echo $tab1['annee']; ?></div>
				<a href="#"><div onclick="modif_promo(<?php echo $tab1['id_promo']; ?>, '<?php echo $tab1['nom_promo']; ?>');" class="blocmodif" title="Modifier la promotion"><img class="imgmodif" src="images/set.png"></div></a>
				<a href="#"><div onclick="suppr_promo(<?php echo $tab1['id_promo']; ?>);" class="blocmodif" title="Supprimer la promotion"><img class="imgmodif" src="images/delete.png"></div></a>			
				<a href="#"><div onclick="affiche_etud(<?php echo $tab1['id_promo']; ?>);" class="blocmodif" title="Afficher les groupes"><img class="imgmodif" src="images/loupe.png"></div></a>
				<a href="#"><div onclick="liste_etud(<?php echo $tab1['id_promo']; ?>);" class="blocmodif" title="Afficher les étudiants"><img class="imgmodif" src="images/loupe.png"></div></a>
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
<script src="javascript/gest_promo.js"></script>
<script src="javascript/onglet_gest.js"></script>
<script>
	function modif_promo(id,nom)
	{
		// VARIABLE BLOC B,C,D
		var blocA=document.getElementById('A'+id);
		var blocB=document.getElementById('B'+id);
		var blocC=document.getElementById('C'+id);
		
		// MISE EN PLACE DU FOND OPAQUE
		var blocFond=document.getElementById('fondOpaque');
		blocFond.style.display='';
		
		// MISE EN PLACE BLOC VALIDATION
		var barreValide=document.getElementById('barreValide');
		barreValide.style.display='';
		
		// MISE EN PLACE DES CHAMPS DE MODIF
		blocA.innerHTML='<input id="blocA" class="newsaisie" type="text" name="n_nom_promo" value="'+nom+'"><input type="hidden" name="id_promo" value='+id+'>';
		blocB.innerHTML="<div style='width: 219px' id='listeDer'><select style='width: 219px' id='listeDer2' name='n_id_dpt'><?php $stmt = $pdo->query('SELECT * FROM departement'); while($tabg = $stmt->fetch(PDO::FETCH_ASSOC)){ echo '<option value='.$tabg['id_dpt'].'>'.$tabg['nom_dpt'].'</option>'; } ?></select></div>";		
		blocC.innerHTML='<input id="blocC" class="newsaisie" type="text" name="n_annee" value="'+blocC.innerHTML+'"><input type="hidden" id="iddpt" name="iddpt" value='+id+'>';
				
		// FOCUS SUR CHAMP
		document.getElementById('blocA').focus();
	}
</script>
