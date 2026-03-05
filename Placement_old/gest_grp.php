<?php
include('connexion.php');
	// TEST SI L'USER A APPUYE SUR ENVOYER (CREATION etudiant)
	if(isset($_POST['envoyer']) && $_POST['envoyer']=="Ajouter étudiant")
	{   
		
		try {
			$countex = $pdo->prepare('SELECT COUNT(*) a FROM etudiant WHERE nom_etudiant = :nom_etudiant AND prenom_etudiant = :prenom_etudiant AND tiers_temps = :tiers_temps AND mob_reduite = :mob_reduite AND id_groupe = :id_groupe');
			$countex->execute([
				'nom_etudiant' => $_POST['nom_etudiant'],
				'prenom_etudiant' => $_POST['prenom_etudiant'],
				'tiers_temps' => $_POST['tiers_temps'],
				'mob_reduite' => $_POST['mob_reduite'],
				'id_groupe' => $_POST['id_groupe']
			]);

			if ($countex->fetchColumn() == 0) {
				if ($_POST['mob_reduite']) {
					$mr = $_POST['mob_reduite'];
				} else {
					$mr = 0;
				}
				if ($_POST['tiers_temps']) {
					$tt = $_POST['tiers_temps'];
				} else {
					$tt = 0;
				}
				$insertEtudiant = $pdo->prepare('INSERT INTO etudiant (nom_etudiant, prenom_etudiant, id_groupe, tiers_temps, mob_reduite) VALUES (:nom_etudiant, :prenom_etudiant, :id_groupe, :tiers_temps, :mob_reduite)');
				$insertEtudiant->execute([
					'nom_etudiant' => strtoupper($_POST['nom_etudiant']),
					'prenom_etudiant' => ucfirst($_POST['prenom_etudiant']),
					'id_groupe' => $_POST['id_groupe'],
					'tiers_temps' => $tt,
					'mob_reduite' => $mr
				]);

				$nbEtudQuery = $pdo->prepare('SELECT nb_etud FROM groupe WHERE id_groupe = :id_groupe');
				$nbEtudQuery->execute(['id_groupe' => $_POST['id_groupe']]);
				$nb_etud = $nbEtudQuery->fetchColumn() + 1;

				$updateGroupe = $pdo->prepare('UPDATE groupe SET nb_etud = :nb_etud WHERE id_groupe = :id_groupe');
				$updateGroupe->execute([
					'nb_etud' => $nb_etud,
					'id_groupe' => $_POST['id_groupe']
				]);
			} else {
				echo '<script>alert("Étudiant déjà existant")</script>';
			}
		} catch (PDOException $e) {
			echo 'Error: ' . $e->getMessage();
		}
	}

	// TEST SI L'USER A APPUYE SUR SUPPRIMER (groupe)

	if(isset($_GET['suppr2']))
	{
		// Requete de suppression
		$deleteGroupe = $pdo->prepare('DELETE FROM groupe WHERE id_groupe = :id_groupe');
		$deleteGroupe->execute(['id_groupe' => $_GET['suppr2']]);
	
	}
	$promo = $_GET['promo'];	

	// TEST SI L'USER A VALIDE LES MODIFICATIONS (Groupe)

	if(isset($_POST['validemodif']) && $_POST['validemodif']=="Modifier")
	{
			$countgrp = $pdo->prepare('SELECT COUNT(*) a FROM groupe WHERE nom_groupe = :nom_groupe AND id_promo = :id_promo AND NOT EXISTS (SELECT * FROM promotion WHERE id_groupe = :id_groupe)');
			$countgrp->execute([
				'nom_groupe' => $_POST['n_nom_groupe'],
				'id_promo' => $promo,
				'id_groupe' => $_POST['id_groupe']
			]);

			// Test si la promotion existe deja
			if ($countgrp->fetchColumn() == 0) {
				$updateGroupe = $pdo->prepare('UPDATE groupe SET nom_groupe = :nom_groupe WHERE id_groupe = :id_groupe');
				$updateGroupe->execute([
					'nom_groupe' => $_POST['n_nom_groupe'],
					'id_groupe' => $_POST['id_groupe']
				]);
			} else {
				echo '<script>alert("Groupe déjà existant")</script>';
			}
	}	

	// TEST SI L'USER A APPUYE SUR SUPPRIMER (etudiant)

	if(isset($_GET['suppr']))
	{
		// Requete de suppression
			$deleteEtudiant = $pdo->prepare('DELETE FROM etudiant WHERE id_etudiant = :id_etudiant');
			$deleteEtudiant->execute(['id_etudiant' => $_GET['suppr']]);
	}
	
	// TEST SI L'USER A VALIDE LES MODIFICATIONS (etudiant)

	if(isset($_POST['validemodif']) && $_POST['validemodif']=="Valider")
	{
	$countex = $pdo->prepare('SELECT COUNT(*) a FROM etudiant WHERE nom_etudiant = :nom_etudiant AND prenom_etudiant = :prenom_etudiant AND tiers_temps = :tiers_temps AND mob_reduite = :mob_reduite AND id_groupe = :id_groupe AND id_etudiant != :id_etudiant');
		$countex->execute([
			'nom_etudiant' => $_POST['n_nom_etudiant'],
			'prenom_etudiant' => $_POST['n_prenom_etudiant'],
			'tiers_temps' => $_POST['n_tiers_temps'],
			'mob_reduite' => $_POST['n_mob_reduite'],
			'id_groupe' => $_POST['n_id_groupe'],
			'id_etudiant' => $_POST['id_etudiant']
		]);

		if ($countex->fetchColumn() == 0) {
			$updateEtudiant = $pdo->prepare('UPDATE etudiant SET nom_etudiant = :nom_etudiant, prenom_etudiant = :prenom_etudiant, tiers_temps = :tiers_temps, mob_reduite = :mob_reduite, id_groupe = :id_groupe WHERE id_etudiant = :id_etudiant');
			$updateEtudiant->execute([
				'nom_etudiant' => strtoupper($_POST['n_nom_etudiant']),
				'prenom_etudiant' => ucfirst($_POST['n_prenom_etudiant']),
				'tiers_temps' => $_POST['n_tiers_temps'],
				'mob_reduite' => $_POST['n_mob_reduite'],
				'id_groupe' => $_POST['n_id_groupe'],
				'id_etudiant' => $_POST['id_etudiant']
			]);
		} else {
			echo '<script>alert("Étudiant déjà existant")</script>';
		}
	}
	
?>

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="css/s_gest_grp.css">

<!-- #################### TITRE PRINCIPAL ################### -->
	<?php
	$promo = $_GET['promo'];	
	$nomPromoQuery = $pdo->prepare('SELECT nom_promo FROM promotion WHERE id_promo = :promo');
	$nomPromoQuery->execute(['promo' => $promo]);
	$nom_promo = $nomPromoQuery->fetchColumn();

	$nomDptQuery = $pdo->prepare('SELECT nom_dpt FROM promotion p, departement d WHERE p.id_dpt = d.id_dpt AND id_promo = :promo');
	$nomDptQuery->execute(['promo' => $promo]);
	$nom_dpt = $nomDptQuery->fetchColumn();
	?>
<div class="titrecontenu"><a href="index.php?p=gest_promo"><div class="blocretour" title="Retour"><img class="imgretour" src="images/fleche.png"></div></a><?php echo $nom_promo.' '.$nom_dpt?></div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">
<div id="blocOnglet">
	
	<?php
	$promo = $_GET['promo'];	
	$nomPromoQuery = $pdo->prepare('SELECT nom_promo FROM promotion WHERE id_promo = :promo');
	$nomPromoQuery->execute(['promo' => $promo]);
	$nom_promo = $nomPromoQuery->fetchColumn();
	?>
	<div id="onglet1" class="onglet"  style="background: #BEBEBE">Groupes</div>
	
       <!-- Affichage de la lise des groupes -->

	<?php
		$promo = $_GET['promo'];
		$grp = 3;
		$query1 = $pdo->prepare('SELECT * FROM groupe WHERE id_promo = :promo ORDER BY nom_groupe');
		$query1->execute(['promo' => $promo]);
		while ($tab1 = $query1->fetch(PDO::FETCH_ASSOC))
		{		
	?>

	<div id="<?php echo 'onglet'.$grp?>" class="onglet2"  style="background: gray"><?php echo $tab1['nom_groupe']; ?></div>
	<?php
	$grp = $grp + 1;
	}
	?>
	<div id="onglet2" class="onglet2"  style="background: gray">Ajouter étudiant</div>
	
	</div>	


	<?php $promo = $_GET['promo'];?>
	<div id="blocOnglet1" class="contenuonglet" style="background: #BEBEBE" style="display:">
		<div id="bloctitregrp2"><div class="nomtitregrp2">Nom du groupe</div><div class="nomtitregrp2">Nombre d'étudiants</div></div>
			<form action="<?php echo 'index.php?p=gest_grp&promo='.$promo?>" method="post">
				<?php
					$query = $pdo->prepare('SELECT * FROM groupe WHERE id_promo = :promo');
					$query->execute(['promo' => $promo]);
					while ($tab = $query->fetch(PDO::FETCH_ASSOC))
					{			
				?>
					<div class="contenutabgrp2">
						<div id="<?php echo 'A'.$tab['id_groupe'] ?>" class="nomtitregrp2"><?php echo $tab['nom_groupe']; ?></div>
						<div id="<?php echo 'B'.$tab['id_groupe'] ?>" class="nomtitregrp2">
							<?php
							$nbEtudQuery = $pdo->prepare('SELECT COUNT(*) FROM etudiant WHERE id_groupe = :id_groupe');
							$nbEtudQuery->execute(['id_groupe' => $tab['id_groupe']]);
							$nb_etud = $nbEtudQuery->fetchColumn();
							echo $nb_etud;
							?>
						</div>
						<a href="#"><div onclick="modif_groupe(<?php echo $tab['id_groupe']; ?>, '<?php echo $tab['nom_groupe']; ?>');" class="blocmodif"><img class="imgmodif" src="images/set.png"></div></a>
						<a href="#"><div onclick="suppr_groupe(<?php echo $tab['id_groupe']; ?>);" class="blocmodif"><img class="imgmodif" src="images/delete.png"></div></a>	
					</div>
				<?php
				    
					}
				?>
	
		<div id="barreValide7" style="display:none">
			<input id="bouttonmodif" type="submit" name="validemodif" value="Modifier">
			<input id="bouttonmodif" type="submit" name="annuler" value="Annuler">
		</div>		
			</form>	

	</div>
	
	<?php
		$promo = $_GET['promo'];
		$grp=3;
		$query1 = $pdo->prepare('SELECT * FROM groupe WHERE id_promo = :promo');
		$query1->execute(['promo' => $promo]);
		while ($tab1 = $query1->fetch(PDO::FETCH_ASSOC))
		{
		
  	?>

	<!-- Affichage d'un groupe -->

	<div id="<?php echo 'blocOnglet'.$grp?>" class="contenuonglet" style="display: none">
		<div id="bloctitregrp"><div class="nomtitregrp">Nom</div><div class="nomtitregrp">Prénom</div><div class="nomtitregrp">Tiers-temps</div><div class="nomtitregrp">Mobilité réduite</div><div class="nomtitregrp">Groupe</div></div>
			<form action="<?php echo 'index.php?p=gest_grp&promo='.$promo?>" method="post">
				<?php
					$query2 = $pdo->prepare('SELECT * FROM etudiant WHERE id_groupe = :id_groupe ORDER BY nom_etudiant, prenom_etudiant');
					$query2->execute(['id_groupe' => $tab1['id_groupe']]);
					while ($tab2 = $query2->fetch(PDO::FETCH_ASSOC))
					{			
				?>
					<div class="contenutabgrp">
						<div id="<?php echo 'A'.$tab2['id_etudiant'] ?>" class="nomtitregrp"><?php echo $tab2['nom_etudiant']; ?></div>
						<div id="<?php echo 'B'.$tab2['id_etudiant'] ?>" class="nomtitregrp"><?php echo $tab2['prenom_etudiant']; ?></div>
						<div id="<?php echo 'C'.$tab2['id_etudiant'] ?>" class="nomtitregrp"><?php if( $tab2['tiers_temps'] == 1){ echo 'Oui';} else{ echo 'Non';}  ?></div>
						<div id="<?php echo 'D'.$tab2['id_etudiant'] ?>" class="nomtitregrp"><?php if( $tab2['mob_reduite'] == 1){ echo 'Oui';} else{ echo 'Non';}  ?></div>
						<div id="<?php echo 'E'.$tab2['id_etudiant'] ?>" class="nomtitregrp"><?php echo $tab1['nom_groupe']; ?></div>
						<a href="#"><div onclick="modif_etud(<?php echo $tab2['id_etudiant']; ?>, '<?php echo $tab2['nom_etudiant']; ?>', <?php echo $grp; ?>);" class="blocmodif"><img class="imgmodif" src="images/set.png"></div></a>
						<a href="#"><div onclick="suppr_etud(<?php echo $tab2['id_etudiant']; ?>);" class="blocmodif"><img class="imgmodif" src="images/delete.png"></div></a>	
					</div>
							<?php
				    }

				?>			

		<div id="<?php echo "barreValide".$grp?>" style="display:none">
			<input id="bouttonmodif" type="submit" name="validemodif" value="Valider">
			<input id="bouttonmodif" type="submit" name="annuler" value="Annuler">
		</div>
	<?php
					$grp=$grp+1;
?>
			</form>	
    </div>
	<?php
						
				    				
		}
	?>

	<div id="blocOnglet2" class="contenuonglet" style="display: none">
			<form action="<?php echo 'index.php?p=gest_grp&promo='.$promo?>" method="post">
			<label for="nom_etudiant">Nom &nbsp;&nbsp;&nbsp;&nbsp; </label><input type="text" id="nom_etudiant" name="nom_etudiant"><br>
			<label for="prenom_etudiant">Prénom </label><input type="text" id="prenom_etudiant" name="prenom_etudiant"><br>

			<label for="tiers_temps">Tiers temps </label><input type="checkbox"  name="tiers_temps" value="1" />
			<label for="mob_reduite">Mobilité réduite </label><input type="checkbox" name="mob_reduite" value="1" /><br>
			<label for='id_groupe'>Groupe </label><select id='id_groupe' name='id_groupe'>
				<?php
				$promoQuery = $pdo->prepare('SELECT * FROM groupe WHERE id_promo = :promo');
				$promoQuery->execute(['promo' => $promo]);
				while ($array = $promoQuery->fetch(PDO::FETCH_ASSOC))
				{
					echo '<option value='.$array['id_groupe'].'>'.$array['nom_groupe'].'</option>';
				} 
				?>
			</select><br>
			<input type="submit" name="envoyer" value="Ajouter étudiant">
			<input type="submit" name="annuler" value="Annuler">
		</form>
	</div>
	

</div>	

<div id="fondOpaque" style="display:none"></div>
<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="javascript/gest_grp.js"></script>
<script src="javascript/onglet_gest.js"></script>
<script>

   function suppr_etud(idetud)
	{
		if(confirm('Etes vous sûr de vouloir supprimer cet étudiant ?'))
		{
			document.location.href='index.php?p=gest_grp<?php echo "&promo=".$_GET['promo'];?>&suppr='+idetud;
		}
	}
	
   function suppr_groupe(grp)
	{
		if(confirm('Etes vous sûr de vouloir supprimer ce groupe ?'))
		{
			document.location.href='index.php?p=gest_grp<?php echo "&promo=".$_GET['promo'];?>&suppr2='+grp;
		}
	}	
	function modif_groupe(id,nom)
	{
		// VARIABLE BLOC B,C,D
		var blocA=document.getElementById('A'+id);
		var blocB=document.getElementById('B'+id);
		
		// MISE EN PLACE DU FOND OPAQUE
		var blocFond=document.getElementById('fondOpaque');
		blocFond.style.display='';
		
		// MISE EN PLACE BLOC VALIDATION
		document.getElementById('barreValide7').style.display='';
		
		// MISE EN PLACE DES CHAMPS DE MODIF
		blocA.innerHTML='<input id="blocA" class="newsaisie2" type="text" name="n_nom_groupe" value="'+nom+'"><input type="hidden" name="id_groupe" value='+id+'>';
		
					
		// FOCUS SUR CHAMP
		document.getElementById('blocA').focus();
	}
	function modif_etud(id,nom,grp)
	{
		// VARIABLE BLOC B,C,D
		var blocA=document.getElementById('A'+id);
		var blocB=document.getElementById('B'+id);
		var blocC=document.getElementById('C'+id);
		var blocD=document.getElementById('D'+id);
		var blocE=document.getElementById('E'+id);
		
		// MISE EN PLACE DU FOND OPAQUE
		var blocFond=document.getElementById('fondOpaque');
		blocFond.style.display='';
		
		// MISE EN PLACE BLOC VALIDATION
		document.getElementById('barreValide'+grp).style.display='';
		
		// MISE EN PLACE DES CHAMPS DE MODIF
		blocA.innerHTML='<input id="blocA" class="newsaisie" type="text" name="n_nom_etudiant" value="'+nom+'"><input type="hidden" name="id_etudiant" value='+id+'>';
		blocB.innerHTML='<input id="blocB" class="newsaisie" type="text" name="n_prenom_etudiant" value="'+blocB.innerHTML+'"><input type="hidden" id="id_etudiant" name="id_etudiant" value='+id+'>';
		blocC.innerHTML='<div style="width: 137px" id="listeDer"><select style="width: 137px" id="listeDer2" name="n_tiers_temps"><option value="0">Non</option><option value="1">Oui</option></select></div>';
		blocD.innerHTML='<div style="width: 137px" id="listeDer"><select style="width: 137px" id="listeDer2" name="n_mob_reduite"><option value="0">Non</option><option value="1">Oui</option></select></div>';	
		blocE.innerHTML="<div style='width: 137px' id='listeDer'><select style='width: 137px' id='listeDer2' name='n_id_groupe'><?php $promog = $_GET['promo']; $queryg = $pdo->prepare('SELECT * FROM groupe WHERE id_promo = :promo'); $queryg->execute(['promo' => $promog]); while($tabg = $queryg->fetch(PDO::FETCH_ASSOC)){ echo '<option value='.$tabg['id_groupe'].'>'.$tabg['nom_groupe'].'</option>'; } ?></select></div>";
		
					
		// FOCUS SUR CHAMP
		document.getElementById('blocA').focus();
	}
</script>

