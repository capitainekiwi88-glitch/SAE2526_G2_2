<?php
	session_start();
	include('../connexion.php');
	
	// Initialisation variables
	$_SESSION['colSalle']=array();
	$_SESSION['rangSalle']=array();
	$_SESSION['structSalle']=array(array(array()));
	
	// ########################## Fonctions utiles ############################
	include('fct_placement.php');
	
	// ################# Recuperation salle + salle/matiere ###################
	recupCombiS();
	recupPromo();
?>

<!-- ################################################################################################ -->
<!-- ########################################## CORPS PAGE ########################################## -->
<!-- ################################################################################################ -->

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="../css/s_generique.css">
<link rel="stylesheet" type="text/css" href="../css/s_up_stage1.css">
<link rel="stylesheet" type="text/css" href="../css/s_up_stage2.css">

<!-- ######################### Titre ######################## -->
<center><h1>Étape 2 : Gestion places</h1></center>

<!-- ##################### Barre onglets #################### -->
<div id="barreOnglet" class="barreOnglet">
	<?php 
		for($i=0; $i<$_SESSION['nbSalle']; $i++)
		{
			echo "<a href='#' id='linkdec'><div id='onglet".($i+1)."' class='onglet".$_SESSION['nbSalle']."' style='background:";
			if($i==0){echo '#BEBEBE';};
			$query=$pdo->prepare('SELECT nom_salle, intercal 
								FROM salle 
								WHERE id_salle= :id_salle');
			$query->execute(['id_salle'=>$_SESSION['salleUtil'][$i]]);
			$res = $query->fetch(PDO::FETCH_ASSOC);
			$intercal = $res['intercal']; // pour savoir s'il faut intercaler dans la salle (1 oui 0 non)
			echo "'>".$res['nom_salle']."</div></a>";
			
		}
	?>
</div>

<!-- ###################### Bloc onglets ##################### -->
<?php
	// Declaration tableau : x | y | id_etud
	$_SESSION['placement']=array(array(array()));
	$_SESSION['placeUtil']=array(array(array()));
	$_SESSION['placeHandi']=array(array(array()));			
	for($l=0; $l<$_SESSION['nbSalle']; $l++)
	{	
		// ############################################ DIV BLOC ONGLET L+1 ############################################
		echo '<div id="blocOnglet'.($l+1).'" class="blocOnglet" style="display:'; if($l==1){ echo 'none';} echo '"><br>';
			
			// ##################### Recuperation structure salle #####################
			recupStructSalle($_SESSION['salleUtil'][$l], $l);
				
			// ############### Algo qui recupere les places utilisables ###############
			recupPlace($l, $intercal);
			
			// ############################ Genere noPlace ############################
			numeroPlace($l);
			
			// ########################## Recupere etudiants ##########################
			$_SESSION['nbEtud']=array();
			$_SESSION['etudUtil']=array(array());
			$_SESSION['nbEtud'][$l]=0;
			for($k=0; $k<$_SESSION['nbCombi']; $k++)
			{
				if($_SESSION['infoCombi'][$k][2]==$_SESSION['salleUtil'][$l])
				{
					recupEtud($k, $l);
				}
			}
			
			
			
			placementEtud($l);

			//<!-- ################# Affichage structure ################## -->
			echo	'<center>
					 <table id="TAB1" style="table-layout: fixed; width: 850px">';
					
					$nbRang=$_SESSION['rangSalle'][$l];
					$nbCol=$_SESSION['colSalle'][$l];
					
					// Affichage structure salle avec noms etudiants
					for($i=0; $i<$nbRang; $i++)
					{
						echo '<tr id="'.$i.'">';
						for($j=0; $j<$nbCol; $j++)
						{
							echo '<td id="'.$l.'.'.$i.'-'.$j.'" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size: 9px; width: '.
									returnTaille($i,$j,$l).'" onclick="selecTwo(this.id, '.$i.','.$j.')" class="'.
									modifClasse($_SESSION['structSalle'][$l][$i][$j]).'" id="'.$i.'-'.$j.'">'.returnNom($i,$j,$l).
									'<br>'.(isset($_SESSION['noPlace'][$l][$i][$j]) ? $_SESSION['noPlace'][$l][$i][$j] : '' ).'</td>';	
						}
						echo '</tr>';
					}
			
			echo	'</table> 
					 <br><div class="bureau">BUREAU</div><br>
					 </center>
				</div>';
			
	}	
?>
<center>En cliquant sur les places, vous avez la possibilité d'intervertir les étudiants si vous voulez aider le hasard à bien faire les choses...</center>
<center><button id="btnInter" style="display:none" onclick="intervertir()">Intervertir</button></center>


<!-- ##################### IMPORT JAVASCRIPT ################## -->
<script src="../javascript/jquery-1.7.1.js"></script>
<script src="javascript/up_stage2.js"></script>
<script src="javascript/onglet_salle.js"></script>
