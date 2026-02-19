<?php
	session_start();
	
	// ################# Recense nombre de place dans la salle ##################
	function comptePlace()
	{
		$placeOk=0;
		$placeHandi=0;
		
		for($i=0; $i<$_SESSION['rangSalle']; $i++)
		{
			for($j=0; $j<$_SESSION['colSalle']; $j++)
			{
				if($_SESSION['structSalle'][$i][$j]=='1')
				{
					$placeOk++;
				}
				else if($_SESSION['structSalle'][$i][$j]=='2')
				{
					$placeHandi++;
				}
			}
		}
		
		$arrayPlace[0]=$placeOk;
		$arrayPlace[1]=$placeHandi;
		
		return $arrayPlace;
	}
	
	// ############## Sauvegarde du plance dans la base de donnee ##############
	function saveBDD()
	{
		$text='';
		
		for($i=0; $i<$_SESSION['rangSalle']; $i++)
		{
			for($j=0; $j<$_SESSION['colSalle']; $j++)
			{
				$text.=$_SESSION['structSalle'][$i][$j];
			}
			$text.='-';
		}
		
		include("connexion.php");
		
		// Modification du plan
		$stmt = $pdo->prepare('UPDATE plan SET donnee = :donnee WHERE id_plan = :id_plan');
		$stmt->execute(['donnee' => $text, 'id_plan' => $_SESSION['idPlan']]);
		
		// Modification de la salle
		$capacite = comptePlace();
		$_SESSION['capacite'] = $capacite[0] + $capacite[1];
		
		$stmt = $pdo->prepare('UPDATE salle SET nom_salle = :nom_salle, etage = :etage, id_bat = :id_bat, id_dpt = :id_dpt, capacite = :capacite WHERE id_plan = :id_plan');
		$stmt->execute([
			'nom_salle' => $_SESSION['nomSalle'],
			'etage' => $_SESSION['etageSalle'],
			'id_bat' => $_SESSION['batSalle'],
			'id_dpt' => $_SESSION['dptSalle'],
			'capacite' => $_SESSION['capacite'],
			'id_plan' => $_SESSION['idPlan']
		]);
	}
	

	
	// ################# Modifie la classe pour l'affichage ##################
	function modifClasse($val)
	{
		switch ($val)
		{
			case 0: $classe='couloir'; break;
			case 1: $classe='placeOk'; break;
			case 2: $classe='placeHandi'; break;
			case 3: $classe='placeInex'; break;
			default: break;
		}
		return $classe;
	}
	
	// ################# Test l'appuie sur le bouton enregistrer #################
	if(isset($_POST['Enregistrer']))
	{
		saveBDD();
		// echo "<script>alert('Enregistrement effectue')</script>";
		echo "<script>window.top.document.location.href='index.php?p=gest_salle'</script>";
	}
?>

<!-- ################################################################################################ -->
<!-- ########################################## CORPS PAGE ########################################## -->
<!-- ################################################################################################ -->

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="css/s_stage4.css">

<!-- ######################### Titre ######################## -->
<center><h1>Modification : Récapitulatif</h1></center>

<!-- INFORMATIONS SALLE -->
<center>
	<?php
		$array=comptePlace();
		echo '<h3>'.$_SESSION['nomSalle'].'</h3>';
		echo '<b>'.$array[0].'</b> places ';
		if($array[1])
		{
			echo '(+ <b>'.$array[1].'</b> places "handicapé")';
		}
	?>
</center>

<br>

<!-- ################# Affichage structure ################## -->
<center>
	<table id="TAB1">
		<?php
			$nbRang=$_SESSION['rangSalle'];
			$nbCol=$_SESSION['colSalle'];
		
			// Affichage structure salle
			for($i=0; $i<$nbRang; $i++)
			{
				echo '<tr id="'.$i.'">';
				for($j=0; $j<$nbCol; $j++)
				{
					echo '<td class="'.modifClasse($_SESSION['structSalle'][$i][$j]).'" id="'.$i.'-'.$j.'"></td>';	
				}
				echo '</tr>';
			}
		?>
	</table> 
</center>

<!-- ################# Bouton enregistrement ################ -->
<form id="formSave" name="formSave" action="ms_stage6.php" method="POST">
	<input type="hidden" name="Enregistrer" value="Enregistrer">
</form>

<br><center><div class="bureau">BUREAU</div></center>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="javascript/crea_salle_s2.js"></script>
