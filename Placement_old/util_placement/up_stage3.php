<?php
	session_start();
	

	// Enregistrement devoir
	function saveDevoir()
	{
	include('../connexion.php');

		$datefr = $_SESSION['dateDevoir'];
		$datebdd = $datefr;
		$datebdd = substr($datefr, 6  , 4)."-".substr($datefr, 4  , 2)."-".substr($datefr, 0  , 2);

		$stmt=$pdo->prepare('INSERT INTO devoir(nom_devoir, date_devoir, heure_devoir, duree_devoir) VALUES (?,?,?,?)');
		$stmt->execute([$_SESSION['mDevoir']." ".$datefr, $datebdd, $_SESSION['hDevoir'].':'.$_SESSION['mDevoir'], $_SESSION['hDuree'].':'.$_SESSION['mDuree']]);
		
		$idDevoir=$pdo->lastInsertId();
		
		return $idDevoir;
	}
		
	// Enregistrement BDD
	function savePlacementDB($w, $idSalle, $idDevoir)
	{
	include('../connexion.php');
		foreach($_SESSION['placement'][$w] as $value)
		{
			$stmt=$pdo->prepare('INSERT INTO placement(id_etudiant, id_devoir, id_salle, place_x, place_y) VALUES (?, ?, ?, ?, ?)');
			$stmt->execute([$value[2], $idDevoir, $idSalle, $value[0], $value[1]]);
		}
	}
	
	// Enregistrement des devoirs groupe / devoirs promo
	function saveProgDevoir($idDevoir)
	{
	include('../connexion.php');
		for($i=0; $i<$_SESSION['nbCombi']; $i++)
		{
			if($_SESSION['infoCombi'][$i][1]==0)
			{
				$stmt=$pdo->prepare('INSERT INTO devoir_promo(id_salle, id_devoir, id_promo, id_mat) VALUES (?, ?, ?, ?)');
				$stmt->execute([$_SESSION['infoCombi'][$i][2],$idDevoir,$_SESSION['infoCombi'][$i][0],$_SESSION['infoCombi'][$i][3]]);
			}
			else
			{
				$stmt=$pdo->prepare('INSERT INTO devoir_groupe(id_salle, id_devoir, id_groupe, id_mat) VALUES (?, ?, ?, ?)');
				$stmt->execute([$_SESSION['infoCombi'][$i][2], $idDevoir, $_SESSION['infoCombi'][$i][1], $_SESSION['infoCombi'][$i][3]]);
			}
		}
	}
		
?>

<!-- ################################################################################################ -->
<!-- ########################################## CORPS PAGE ########################################## -->
<!-- ################################################################################################ -->

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="../css/s_generique.css">
<link rel="stylesheet" type="text/css" href="../css/s_up_stage1.css">
<link rel="stylesheet" type="text/css" href="../css/s_stage4.css">
<link rel="stylesheet" type="text/css" href="../css/s_up_stage3.css">

<!-- ######################### Titre ######################## -->
<center><h1>Étape 3 : Exportation</h1></center>

<?php
	// ############ Enregistrement DB ############
	$idDevoir=saveDevoir();
	saveProgDevoir($idDevoir);
	
	for($i=0; $i<$_SESSION['nbSalle']; $i++)
	{
		savePlacementDB($i, $_SESSION['salleUtil'][$i], $idDevoir);
	}
	include('../connexion.php');
?>


<br><br>

<!-- TABLEAU PDF VERSION 2 -->
<table id="tabPDF">
	<!-- TITRE -->
	<tr>
		<td style="background: transparent"></td>
		<td>Liste</td>
		<td>Feuille d'émargement</td>
		<td>Plan</td>
	</tr>

	<!-- LISTE SALLE -->	
	<?php
		for($i=0; $i<$_SESSION['nbSalle']; $i++)
		{
			echo '<tr>';
			
			$stmt=$pdo->prepare("SELECT nom_salle FROM salle WHERE id_salle=:id_salle");
			$stmt->execute(['id_salle'=>$_SESSION['salleUtil'][$i]]);
			$res=$stmt->fetch(PDO::FETCH_ASSOC);
			echo '<td>'.$res['nom_salle'].'</td>';
			
			// Liste
			echo '<td class="btnPDF"><a target="_blank" href="export_pdf.php?varD=1&idDevoir='.$idDevoir.'&idSalle='.$_SESSION['infoCombi'][$i][2].'&idPromo='.$_SESSION['infoCombi'][$i][0].'"><img class="imgPDF" src="../images/loupe.png"></a></td>';
			// Emargement
			echo '<td class="btnPDF"><a target="_blank" href="export_pdf.php?varD=2&idDevoir='.$idDevoir.'&idSalle='.$_SESSION['infoCombi'][$i][2].'&idPromo='.$_SESSION['infoCombi'][$i][0].'"><img class="imgPDF" src="../images/loupe.png"></a></td>';
			// Plan
			echo '<td class="btnPDF"><a target="_blank" href="../x_salle.php?idDevoir='.$idDevoir.'&idSalle='.$_SESSION['infoCombi'][$i][2].'"><img class="imgPDF" src="../images/loupe.png"></a></td>';
			
			echo '</tr>';
		}

	?>
</table>

<br>

<center>
<?php
	// ############ Sortie PDF liste promo #############
	echo '<br>';
	
	if ($_SESSION['nbPromo'] > 1) {
		echo '<h3>Liste par promo</h3>';
		for($i=0; $i<$_SESSION['nbPromo']; $i++)
		{
			$stmt=$pdo->prepare("SELECT nom_promo, nom_dpt FROM promotion, departement WHERE promotion.id_dpt=departement.id_dpt AND id_promo= :id_promo");
			$stmt->execute(['id_promo'=>$_SESSION['infoCombi'][$i][0]]);
			$info=$stmt->fetch(PDO::FETCH_ASSOC);
			$nomSalle=$infos['nom_dpt'].' '.$infos['nom_promo'];
			echo '<a target="_blank" href="export_pdf.php?varD=3&idDevoir='.$idDevoir.'&idSalle='.
							$_SESSION['infoCombi'][$i][2].'&idPromo='.
							$_SESSION['infoCombi'][$i][0].'">'.$nomSalle.'</a><br>'; 
		}
	}
?>
</center>

<!-- ##################### IMPORT JAVASCRIPT ################## -->
<script src="javascript/up_stage2.js"></script>
<script src="javascript/onglet_salle.js"></script>
