<?php
	session_start();
	
	include('../connexion.php');
	include('fct_placement.php');

recupSalle();
if ($_SESSION['nbCombi'] > 0) {
	for ($i = 0; $i < $_SESSION['nbSalle']; $i++) {
		// #################################### NOM SALLE ####################################
		echo '<div class="contenutitre" style="width: 860px; margin-left: 0px;">
		  	<div class="nomtitre" style="width: 650px">';
		$stmt=$pdo->prepare('SELECT nom_salle FROM salle WHERE id_salle= :id_salle');
		$stmt->execute(['id_salle'=> $_SESSION['salleUtil'][$i]]);
		$nomSalle = $stmt->fetch(PDO::FETCH_ASSOC);
		echo $nomSalle['nom_salle'];
		echo '</div>';

		recupStructSalle($_SESSION['salleUtil'][$i], $i);
		recupPlace2($i);

		echo '<div class="nomtitre" style="width: 205px">' . $_SESSION['nbPU1'] . '  /  ' . $_SESSION['nbPlaceTotal'] . '</div>
		 </div>';


		for ($j = 0; $j < $_SESSION['nbCombi']; $j++) {
			if ($_SESSION['infoCombi'][$j][2] == $_SESSION['salleUtil'][$i]) {
				echo '<div class="contenutab" style="width: 888px; margin-left: 0px;">';

				// #################################### NOM PROMO ####################################
				echo '<div class="nomtitre" style="width: 216px">';
				$stmt = $pdo->prepare('SELECT nom_dpt, nom_promo FROM promotion, departement WHERE promotion.id_dpt=departement.id_dpt AND id_promo= :idPromo');
				$stmt->execute(['idPromo'=> $_SESSION['infoCombi'][$j][0]]);
				$nomPromo=$stmt->fetch(PDO::FETCH_ASSOC);
				echo $nomPromo['nom_dpt'] . ' ' . $nomPromo['nom_promo'];
				echo '</div>';

				// #################################### NOM GROUPE ###################################
				echo '<div class="nomtitre" style="width: 215px">';
				$stmt = $pdo->prepare('SELECT nom_groupe FROM groupe WHERE id_groupe= :id_groupe');
				$stmt->execute(['id_groupe' => $_SESSION['infoCombi'][$j][1]]);
				$nomGroupe=$stmt->fetch(PDO::FETCH_ASSOC);
				if ($_SESSION['infoCombi'][$j][1] == 0) {
					echo "Tous les groupes";
				} else {
					echo $nomGroupe['nom_groupe'];
				}
				echo '</div>';

				// #################################### NOM MATIERE ###################################
				echo '<div class="nomtitre" style="width: 215px">';
				$stmt=$pdo->prepare('SELECT nom_mat FROM matiere WHERE id_mat=:id_mat');
				$stmt->execute(['id_mat'=> $_SESSION['infoCombi'][$j][3]]);
				$nomMat = $stmt->fetch(PDO::FETCH_ASSOC);
				echo $nomMat['nom_mat'];
				echo '</div>';

				// ################################# CAPACIT PROMO/GR ################################
				if ($_SESSION['infoCombi'][$j][1] == 0) {
					$stmt=$pdo->prepare('SELECT COUNT(*) a FROM etudiant, groupe WHERE etudiant.id_groupe=groupe.id_groupe AND id_promo=:id_promo');
					$stmt->execute(['id_promo' => $_SESSION['infoCombi'][$j][0]]);
				} else {
					$stmt=$pdo->prepare('SELECT COUNT(*) a FROM etudiant, groupe WHERE etudiant.id_groupe=groupe.id_groupe AND groupe.id_groupe= :id_groupe');
					$stmt->execute(['id_groupe'=>$_SESSION['infoCombi'][$j][1]]);
				}
				$nbEtud=$stmt->fetch(PDO::FETCH_ASSOC);
				echo '<div class="nomtitre" style="width: 205px">';
				echo $nbEtud['a'];
				echo '</div>';

				// ################################## BOUTON SUPPR ##################################
				echo '<div class="supprCombi" onclick="supprCombi(' . $j . ')"><img class="imgsuppr" src="../images/delete.png"></div>';

				echo '</div>';

			}
		}
		echo '<br>';
	}
}
	
?>
