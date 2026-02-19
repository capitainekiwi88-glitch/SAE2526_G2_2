<?php
	session_start();
	include('connexion.php');
	
	// Recuperation id plan
	if(isset($_GET['var1']))
	{
		$_SESSION['idPlan']=$_GET['var1'];
	}
	
	// Requete
	
		$stmt = $pdo->prepare('SELECT * FROM plan, salle WHERE plan.id_plan = salle.id_plan AND plan.id_plan = :idPlan');
		$stmt->execute(['idPlan' => $_SESSION['idPlan']]);
		$salle = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($salle) {
			// Recupere donnees plan
			$text = $salle['donnee'];

			// Recupere infos salle
			$_SESSION['nomSalle'] = $salle['nom_salle'];
			$_SESSION['batSalle'] = $salle['id_bat'];
			$_SESSION['dptSalle'] = $salle['id_dpt'];
			$_SESSION['etageSalle'] = $salle['etage'];
		} else {
			// Handle case where no data is found
			echo "No data found for the given idPlan.";
			exit;
		}
	
	
	// Separe les rang dans un tableau
	//$array=split('-',$text);
	$array=explode('-',$text);
	
	// Recupere le nombre de colonne et rang
	$_SESSION['colSalle']=strlen($array[0]);
	$_SESSION['rangSalle']=count($array)-1;
	
	// Recupere les valeurs dans le tableau de structure
	for($i=0; $i<$_SESSION['rangSalle']; $i++)
	{
		for($j=0; $j<$_SESSION['colSalle']; $j++)
		{
			$_SESSION['structSalle'][$i][$j]=$array[$i][$j];
		}
	}
	
	// #################### Fonction qui modifie la classe ####################
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
?>

<!-- ################################################################################################ -->
<!-- ########################################## CORPS PAGE ########################################## -->
<!-- ################################################################################################ -->

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="css/s_ms_stage2.css">
<link rel="stylesheet" type="text/css" href="css/s_generique.css">

<!-- ############### Affichage donnees salle ################ -->

<!-- INFORMATIONS SALLE -->
<center>
	<?php
		$array=comptePlace();
		echo '<h3>'.$_SESSION['nomSalle'].'</h3>';
		echo '<b>'.$array[0].'</b> places ';
		if($array[1])
		{
			echo '(+ <b>'.$array[1].'</b> place(s) "handicapé")';
		}
	?>
</center>

<br>

<!-- AFFICHE VISUEL SALLE -->
<center>
	<table id='TAB1'>
		<?php
			$nbRang=$_SESSION['rangSalle'];
			$nbCol=$_SESSION['colSalle'];
		
			// Affichage structure salle
			for($i=0; $i<$nbRang; $i++)
			{
				echo '<tr id=\''.$i.'\'>';
		
				for($j=0; $j<$nbCol; $j++)
				{
					echo '<td class=\''.modifClasse($_SESSION['structSalle'][$i][$j]).'\' id=\''.$i.'-'.$j.'\'></td>';	
				}
				echo '</tr>';
			}
		?>
	</table> 
</center>

<br><center><div class="bureau">BUREAU</div></center>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="javascript/modif_salle_s2.js"></script>
