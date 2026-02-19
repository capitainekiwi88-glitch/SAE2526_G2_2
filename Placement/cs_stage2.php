<?php
	session_start();
	
	// ################## Test si tout les champs sont remplis #####################
	if( isset($_GET['var1']) )
	{
		// Recuperation variables
		$_SESSION['nomSalle']=$_GET['var1'];
		$_SESSION['rangSalle']=$_GET['var2'];
		$_SESSION['colSalle']=$_GET['var3'];
		$_SESSION['batSalle']=$_GET['var4'];
		$_SESSION['dptSalle']=$_SESSION['batSalle']!='3'?'2':$_GET['var5'];
		$_SESSION['etageSalle']=$_GET['var6'];
	
		// Creation structure salle
		$_SESSION['structSalle']=array(array());
	
		for($i=0; $i<$_SESSION['rangSalle']; $i++)
		{
			for($j=0; $j<$_SESSION['colSalle']; $j++)
			{
				// Initialisation
				$_SESSION['structSalle'][$i][$j]=1;
			}		
		}
	}
	
	// ######################## Ajoute couloir verticale ###########################
	function addCouloirVertical($idCouloir)
	{
		$idCouloir+=1;
		$_SESSION['colSalle']+=1;
		
		for($i=0; $i<$_SESSION['rangSalle']; $i++)
		{
			for($j=$_SESSION['colSalle']-1; $j>$idCouloir; $j--)
			{	
				$_SESSION['structSalle'][$i][$j]=$_SESSION['structSalle'][$i][$j-1];
				//$_SESSION[structSalle][$i][$j-1]=0;
			}
			$_SESSION['structSalle'][$i][$idCouloir]=0;
		}
		
	}
	
	// ######################## Ajoute couloir horizontale #########################
	function addCouloirHorizontal($idCouloir)
	{
		$idCouloir+=1;
		$_SESSION['rangSalle']+=1;
		
		for($i=0; $i<$_SESSION['colSalle']; $i++)
		{
			for($j=$_SESSION['rangSalle']-1; $j>$idCouloir; $j--)
			{	
				$_SESSION['structSalle'][$j][$i]=$_SESSION['structSalle'][$j-1][$i];
				//$_SESSION[structSalle][$i][$j-1]=0;
			}
			$_SESSION['structSalle'][$idCouloir][$i]=0;
		}
		
	}
	
	// ######################### Supprime couloir verticale #########################
	function delCouloirVertical($idCouloir)
	{	
		for($i=0; $i<$_SESSION['rangSalle']; $i++)
		{
			for($j=$idCouloir; $j<$_SESSION['colSalle']; $j++)
			{
				$_SESSION['structSalle'][$i][$j]=$_SESSION['structSalle'][$i][$j+1];	
			}
		}
		$_SESSION['colSalle']-=1;
	}
	
	// ######################## Supprime couloir horizontale ########################
	function delCouloirHorizontal($idCouloir)
	{	
		for($i=0; $i<$_SESSION['colSalle']; $i++)
		{
			for($j=$idCouloir; $j<$_SESSION['rangSalle']; $j++)
			{
				$_SESSION['structSalle'][$j][$i]=$_SESSION['structSalle'][$j+1][$i];	
			}
		}
		$_SESSION['rangSalle']-=1;
	}
	
	// ##################### Modifie la classe pour l'affichage ######################
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
	
	// ################# Test appuie sur bouton enregistrer + Verti #################
	if(isset($_GET['addVerti']))
	{
		addCouloirVertical($_GET['addVerti']);
	}
	
	// ################# Test appuie sur bouton enregistrer - Verti #################
	if(isset($_GET['addHoriz']))
	{
		addCouloirHorizontal($_GET['addHoriz']);
	}
	
	// ################# Test appuie sur bouton enregistrer + Horiz #################
	if(isset($_GET['delVerti']))
	{
		delCouloirVertical($_GET['delVerti']);
	}
	
	// ################# Test appuie sur bouton enregistrer - Horiz #################
	if(isset($_GET['delHoriz']))
	{
		delCouloirHorizontal($_GET['delHoriz']);
	}
?>

<!-- ################################################################################################ -->
<!-- ########################################## CORPS PAGE ########################################## -->
<!-- ################################################################################################ -->

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="css/s_stage2.css">
<link rel="stylesheet" type="text/css" href="css/s_stage3.css">

<!-- ######################### Titre ######################## -->
<center>
	<h1>&Eacute;tape 2 : Structure</h1>
	<h3>Nom salle : <?php echo $_SESSION['nomSalle']; ?></h3>
</center>

<!-- Recuperation donnees -->
<?php
	$nbRang=$_SESSION['rangSalle'];
	$nbCol=$_SESSION['colSalle'];
?>
	
	
<!-- ################# Bloc bouton horizontal ############### -->
<div id="blocBtnAddHorizontal">
	<table id="TAB3">
		<?php
			for($i=0; $i<$nbRang-1; $i++)
			{
				// echo '<tr><td class="td3"><a href="cs_stage2.php?addVerti='.$i.'" id="link">+</a></td></tr>';
				
					if($_SESSION['structSalle'][$i][0]==0)
					{
						echo '<tr><td class="td2"><a href="cs_stage2.php?delHoriz='.$i.'" id="link">-</a></td></tr>';
					}
					else
					{
						echo '<tr><td class="td2"><a href="cs_stage2.php?addHoriz='.$i.'" id="link">+</a></td></tr>';
					}
			}	
		?>
	</table>
</div>

			
<div id="BLOCTEST">
	<!-- ################# Bloc bouton horizontal ############### -->
	<div id="blocBtnAddVertical">
		<center>
			<table id="TAB2">
				<?php
					for($i=0; $i<$nbCol+1; $i++)
					{
						if($i==0 || $i==$nbCol)
						{
							echo '<td class="td2"></td>';
						}
						else
						{
							if($_SESSION['structSalle'][0][$i-1]==0)
							{
								echo '<td class="td2"><a href="cs_stage2.php?delVerti='.($i-1).'" id="link">-</a></td>';
							}
							else
							{
								echo '<td class="td2"><a href="cs_stage2.php?addVerti='.($i-1).'" id="link">+</a></td>';
							}
						}
					}	
				?>
			</table>
		</center>
	</div>

	<!-- ################# Affichage structure salle ############### -->
	<div id="blocTabStruct">
		<center>
			<table id="TAB1">	
			<?php
				// Affichage structure salle
				for($i=0; $i<$nbRang; $i++)
				{
					echo '<tr id="'.$i.'">';
					
					for($j=0; $j<$nbCol; $j++)
					{
						echo '<td id="'.$i.'-'.$j.'" class="'.modifClasse($_SESSION['structSalle'][$i][$j]).'"></td>';	
					}
					echo '</tr>';
					
				}
			?>
			</table> 
		</center>
	</div>

</div>

<br><center><div class="bureau">BUREAU</div></center>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="javascript/crea_salle_s2.js"></script>
