<?php
	session_start();
	include('../connexion.php');
	include('fct_placement.php');
?>

<!-- ################################################################################################ -->
<!-- ########################################## CORPS PAGE ########################################## -->
<!-- ################################################################################################ -->

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="../css/s_generique.css">
<link rel="stylesheet" type="text/css" href="../css/s_up_stage1.css">
<link rel="stylesheet" type="text/css" href="../css/s_up_stage2.css">
<link rel="stylesheet" type="text/css" href="../css/s_stage4.css">

<!-- ######################### Titre ######################## -->
<center><h1>&Eacute;tape 2 : Gestion places</h1></center>

<!-- ##################### Barre onglets #################### -->
<div class="barreOnglet">
	<?php 
		
		for($i=0; $i<$_SESSION[nbSalle]; $i++)
		{
			echo "<a href='#' id='linkdec'><div id='onglet".($i+1)."' class='onglet".$_SESSION[nbSalle]."' style='background:";
			if($i==0){echo '#BEBEBE';};
			$query=mysql_query('SELECT nom_salle FROM salle WHERE id_salle=\''.$_SESSION[salleUtil][$i].'\'');
			echo "'>".mysql_result($query, 0, 'nom_salle')."</div></a>";
		}
	?>
</div>

<!-- ###################### Bloc onglets ##################### -->

<div class="blocOnglet">
	<br>
	<center>
	[__] : Intevertir deux etudiants<br>
	[__] : Intercaler deux promos<br>
	</center>
	<br>
<?php

	// #################### Informations relatives a la saisie ####################
	//	echo "Nom devoir: ".$_SESSION[nomDevoir];
	//	echo "<br>Date devoir: ".$_SESSION[dateDevoir];
	//	echo "<br>D&eacute;but: ".$_SESSION[hDevoir]."h".$_SESSION[mDevoir];
	//	echo "<br>Dur&eacute;e: ".$_SESSION[hDuree]."h".$_SESSION[mDuree];
	//	
	//	echo "<br><br>Nb combi: ".$_SESSION[nbCombi]; // Valeur reel de combinaison: nbCombi+1
	//	
	//	echo "<br>IdPromo: ".$_SESSION[infoCombi][0][0];
	//	echo "<br>IdGroupe: ".$_SESSION[infoCombi][0][1];
	//	echo "<br>IdSalle: ".$_SESSION[infoCombi][0][2]."<br>";
	
	// #################### Recuperation structure salle ####################
	
		// Requete
		$salle=mysql_query('SELECT * FROM plan, salle WHERE plan.id_plan=salle.id_plan AND id_salle=\''.$_SESSION[infoCombi][0][2].'\'');
		
		// Recupere donnees plan
		$text=mysql_result($salle, 0, 'donnee');
		
		// Separe les rang dans un tableau
		$array=split('-',$text);
		
		// Recupere le nombre de colonne et rang
		$_SESSION[colSalle]=strlen($array[0]);
		$_SESSION[rangSalle]=count($array)-1;
		
		// Recupere les valeurs dans le tableau de structure
		for($i=0; $i<$_SESSION[rangSalle]; $i++)
		{
			for($j=0; $j<$_SESSION[colSalle]; $j++)
			{
				$_SESSION[structSalle][$i][$j]=$array[$i][$j];
			}
		}
	
	// #################################################
	// #################### OBJECTIF ###################
	// #################################################
	
	
	// ############### Algo qui recupere les places utilisables ###############
	
	// Nombre de place utilisable
	$nbPU=0;
	// Nombre de place handicape
	$nbPH=0;
	// Nombre de colone utilisable
	$nbCU=0;
	// Variable test sortie
	$findPlace=0;
	
	// Parcours du premier rang pour trouver l'id des colonnes dispo
	for($j=0; $j<$_SESSION[colSalle]; $j+=2)
	{
		// RAZ findPlace
		$findPlace=0;
		
		// Test si la place est utilisable
		if($_SESSION[structSalle][$_SESSION[rangSalle]-1][$j]!=0)
		{
			$_SESSION[idCol][$nbCU]=$j;
			$nbCU++;
		}
		// Sinon cherche une place plus loin et ainsi de suite...
		else
		{
			while( !($findPlace) && ($j<$_SESSION[colSalle]) )
			{
				$j++;
				if($_SESSION[structSalle][$_SESSION[rangSalle]-1][$j]!=0)
				{
					$_SESSION[idCol][$nbCU]=$j;
					$nbCU++;
					$findPlace=1;
				}
			}
		}
	} // Fin for
	
	// Declarations des tableaux 2 dimensions
	$_SESSION[placeUtil]=array(array());
	$_SESSION[placeHandi]=array(array());
	
	// Parcours des colones dispo 
	for($i=$_SESSION[rangSalle]-1; $i>=0; $i--)
	{
		for($j=0; $j<$nbCU; $j++)
		{
			// Recuperation place OK
			if($_SESSION[structSalle][$i][$_SESSION[idCol][$j]]==1)
			{
				$_SESSION[placeUtil][$nbPU][0]=$i;
				$_SESSION[placeUtil][$nbPU][1]=$_SESSION[idCol][$j];
				$nbPU++;
			}
			// Recuperation place Handi
			else if($_SESSION[structSalle][$i][$_SESSION[idCol][$j]]==2)
			{
				$_SESSION[placeHandi][$nbPH][0]=$i;
				$_SESSION[placeHandi][$nbPH][1]=$_SESSION[idCol][$j];
				$nbPH++;
			}
		}
	}	
	
	// ################ LISTE ID ETUDIANT (AU PIF) ##########################
	$nbEtud=40;
	for($i=0; $i<$nbEtud; $i++)
	{
		$listeIdEtud[$i]=$i+1;
	}
	
	// Melange du tableau des id etudiant
	shuffle($listeIdEtud);
	
	// Declaration tableau : x | y | id_etud
	$_SESSION[placement]=array(array());
	
	for($i=0; $i<$nbEtud; $i++)
	{
		// ### Affectation des "nbEtud" premieres places ###
		$_SESSION[placement][$i][0]=$_SESSION[placeUtil][$i][0];
		$_SESSION[placement][$i][1]=$_SESSION[placeUtil][$i][1];

		// ########## Affectation de l'idEtudiant ##########
		$_SESSION[placement][$i][2]=$listeIdEtud[$i];
		
	}
	
?>


<!-- ################# Affichage structure ################## -->
<center>
	<table id="TAB1">
		<?php
			$nbRang=$_SESSION[rangSalle];
			$nbCol=$_SESSION[colSalle];
		
			// Affichage structure salle
			for($i=0; $i<$nbRang; $i++)
			{
				echo '<tr id="'.$i.'">';
				for($j=0; $j<$nbCol; $j++)
				{
					echo '<td style="font-size: 10px" class="'.modifClasse($_SESSION[structSalle][$i][$j]).'" id="'.$i.'-'.$j.'">'.returnNom($i,$j).'</td>';	
				}
				echo '</tr>';
			}
		?>
	</table> 
</center>
	

</div>



<!-- ##################### IMPORT JAVASCRIPT ################## -->
<script src="javascript/up_stage2.js"></script>
<script src="javascript/onglet_salle.js"></script>