<?php
	session_start();
	
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
	
?>

<!-- ################################################################################################ -->
<!-- ########################################## CORPS PAGE ########################################## -->
<!-- ################################################################################################ -->

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="css/s_stage3.css">

<!-- ######################### Titre ######################## -->
<center><h1>&Eacute;tape 3 : Mod&eacute;lisation places</h1></center>

<center>

	<!-- #################### Choix action ################## -->
	<form>
		<input type="radio" name="choixEtat" id="delPlace" checked="true" >Supprimer place</input>
		<input type="radio" name="choixEtat" id="addPlace">Ajouter place</input>
		<input type="radio" name="choixEtat" id="handiPlace">Place 'handicap&eacute;'</input>
	</form>

	<!-- ############ Affichage structure salle ############# -->
	<table id="TAB1">
		<?php
			for($i=0; $i<$_SESSION['rangSalle']; $i++)
			{
				echo '<tr id="'.$i.'">';
		
				for($j=0; $j<$_SESSION['colSalle']; $j++)
				{
					echo '<td class="'.modifClasse($_SESSION['structSalle'][$i][$j]).'" id="'.$i.'-'.$j.'" onclick="modifEtat(this.id, this.className)"></td>';	
				}
				echo '</tr>';
				
			}
		?>
	</table>
	
</center>

<br><center><div class="bureau">BUREAU</div></center>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="javascript/crea_salle_s3.js"></script>
