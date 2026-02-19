<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="css/s_etudiant.css">

<!-- #################### TITRE PRINCIPAL ################### -->

<?php
$promo = $_GET['promo'];

	$stmt = $pdo->prepare('SELECT nom_promo FROM promotion WHERE id_promo = :promo');
	$stmt->execute(['promo' => $promo]);
	$nom_promo = $stmt->fetchColumn();

	$stmt = $pdo->prepare('SELECT nom_dpt FROM promotion p, departement d WHERE p.id_dpt = d.id_dpt AND id_promo = :promo');
	$stmt->execute(['promo' => $promo]);
	$nom_dpt = $stmt->fetchColumn();

/*	$nom_promo = mysql_query('SELECT nom_promo FROM promotion WHERE id_promo="'.$promo.'"');
	$nom_promo = mysql_result($nom_promo,0);
	$nom_dpt = mysql_query('SELECT nom_dpt FROM promotion p, departement d WHERE p.id_dpt = d.id_dpt AND id_promo="'.$promo.'"');
	$nom_dpt = mysql_result($nom_dpt,0);
*/
	?>
<div class="titrecontenu"><a href="index.php?p=gest_promo"><div class="blocretour" title="Retour"><img class="imgretour" src="images/fleche.png"></div></a><?php echo $nom_promo.' '.$nom_dpt?></div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

<div id="bloctitreetud"><div class="nomtitreetud">Nom</div><div class="nomtitreetud">Prénom</div><div class="nomtitreetud">Tiers-temps</div><div class="nomtitreetud">Mobilité réduite</div><div class="nomtitreetud">Groupe</div></div>
		<?php
			$stmt1 = $pdo->prepare('SELECT * FROM groupe WHERE id_promo = :promo ORDER BY nom_groupe');
			$stmt1->execute(['promo' => $promo]);
			while ($tab1 = $stmt1->fetch(PDO::FETCH_ASSOC)) {
				$stmt2 = $pdo->prepare('SELECT * FROM etudiant WHERE id_groupe = :id_groupe ORDER BY nom_etudiant, prenom_etudiant');
				$stmt2->execute(['id_groupe' => $tab1['id_groupe']]);
				while ($tab = $stmt2->fetch(PDO::FETCH_ASSOC)) {
			/*$query1=mysql_query('SELECT * FROM groupe WHERE id_promo="'.$promo.'" ORDER BY nom_groupe');
			while ($tab1=mysql_fetch_array($query1)){
			$query=mysql_query('SELECT * FROM etudiant WHERE id_groupe="'.$tab1[id_groupe].'" ORDER BY nom_etudiant, prenom_etudiant');
			while($tab=mysql_fetch_array($query)){				
			*/
		?>
					<div class="contenutabetud">
						<div id="<?php echo 'A'.$tab['id_etudiant'] ?>" class="nomtitreetud"><?php echo $tab['nom_etudiant']; ?></div>
						<div id="<?php echo 'B'.$tab['id_etudiant'] ?>" class="nomtitreetud"><?php echo $tab['prenom_etudiant']; ?></div>
						<div id="<?php echo 'C'.$tab['id_etudiant'] ?>" class="nomtitreetud"><?php if( $tab['tiers_temps'] == 1){ echo 'Oui';} else{ echo 'Non';}  ?></div>
						<div id="<?php echo 'D'.$tab['id_etudiant'] ?>" class="nomtitreetud"><?php if( $tab['mob_reduite'] == 1){ echo 'Oui';} else{ echo 'Non';}  ?></div>
						<div id="<?php echo 'E'.$tab['id_etudiant'] ?>" class="nomtitreetud"><?php echo $tab1['nom_groupe']; ?></div>
					</div>
		<?php }} ?>		
</div>