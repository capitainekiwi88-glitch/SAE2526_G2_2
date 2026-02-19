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
		
		// Enregistrement du plan
		$stmt = $pdo->prepare('INSERT INTO plan (donnee) VALUES (:donnee)');
		$stmt->execute(['donnee' => $text]);
		
		// Enregistrement de la salle
		$capacite = comptePlace();
		$_SESSION['capacite'] = $capacite[0] + $capacite[1];
		$idPlan = $pdo->lastInsertId();
		$_SESSION['idPlan'] = $idPlan;
		$stmt = $pdo->prepare('INSERT INTO salle (nom_salle, etage, id_bat, id_dpt, id_plan, capacite) VALUES (:nom_salle, :etage, :id_bat, :id_dpt, :id_plan, :capacite)');
		$stmt->execute([
			'nom_salle' => $_SESSION['nomSalle'],
			'etage' => $_SESSION['etageSalle'],
			'id_bat' => $_SESSION['batSalle'],
			'id_dpt' => $_SESSION['dptSalle'],
			'id_plan' => $idPlan,
			'capacite' => $_SESSION['capacite']
		]);
/*
		mysql_query('INSERT INTO plan (donnee) VALUES ("'.$text.'")');
		
		// Enregistrement de la salle
		$capacite=comptePlace();
		$_SESSION['capacite']=$capacite[0]+$capacite[1];
		$idPlan=mysql_insert_id();
		$_SESSION['idPlan']=$idPlan;
		mysql_query('INSERT INTO salle (nom_salle, etage, id_bat, id_dpt, id_plan, capacite) VALUES (\''.$_SESSION[nomSalle].'\',\''.$_SESSION[etageSalle].'\',\''.$_SESSION[batSalle].'\',\''.$_SESSION[dptSalle].'\',\''.$idPlan.'\',\''.$_SESSION[capacite].'\')');
		*/
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
		// echo "<script>alert('Enregistrement effectué')</script>";
		echo "<script>window.top.document.location.href='index.php?p=gest_salle'</script>";
	}
?>

<!-- ################################################################################################ -->
<!-- ########################################## CORPS PAGE ########################################## -->
<!-- ################################################################################################ -->

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="css/s_stage4.css">

<!-- ######################### Titre ######################## -->
<center><h1>Étape 4 : Récapitulatif</h1></center>

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
<form id="formSave" name="formSave" action="cs_stage4.php" method="POST">
	<input type="hidden" name="Enregistrer" value="Enregistrer">
</form>

<center><div class="bureau">BUREAU</div></center>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="javascript/crea_salle_s2.js"></script>
