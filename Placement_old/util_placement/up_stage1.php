<?php
	session_start();
	require_once('../connexion.php');
	
	// Remise a zero du nombre de combinaison
	$_SESSION['nbCombi']=0;
	unset($_SESSION['salleUtil']);
	unset($_SESSION['infoCombi']);
	$_SESSION['infoCombi']=array(array());
		
	// Recuperation des donnees pour un eventuel retour
	if($_SESSION['dateDevoir'])
	{
		$dateDevoir=$_SESSION['dateDevoir'];
		$hDev=$_SESSION['hDevoir'];
		$mDev=$_SESSION['mDevoir'];
		$hDur=$_SESSION['hDuree'];
		$mDur=$_SESSION['mDuree'];
	}else{
		$dateDevoir="";
		$hDev="";
		$mDev="";
		$hDur="";
		$mDur="";
	}
	
	// Fonction pour tester valeur liste
	function estSelec($id1, $id2)
	{
		if($id1==$id2)
		{
			return 'selected';
		}
	}
?>

<!-- ################################################################################################ -->
<!-- ########################################## CORPS PAGE ########################################## -->
<!-- ################################################################################################ -->

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="../css/s_up_stage1.css">
<link rel="stylesheet" type="text/css" href="../css/s_cal.css">

<!-- ######################### Titre ######################## -->
<center><h1>Étape 1 : Informations devoir</h1></center>

<!-- ##### DATE ##### -->
<table class="ds_box" cellpadding="0" cellspacing="0" id="ds_conclass" style="display: none;">
	<tr><td id="ds_calclass"></td></tr>
</table>
<label for="d">Date</label>
<input onclick="ds_sh(this);" name="d" id="date_dev" value="<?php echo $dateDevoir; ?>" readonly="readonly" style="cursor: text" /><span class="tooltip" style="display:none"> Aucune date s&eacute;lectionn&eacute;e.</span><br>

<!-- ##### HEURE ##### -->
<br />
<label for="h_deb">Début du devoir</label>
<select name="h_deb" id="h_deb" class="correct">
 	<option value="A">Heure </option>
	<option <?php echo estSelec('8', $hDev); ?> value="8">8</option>
	<option <?php echo estSelec('9', $hDev); ?> value="9">9</option>
	<option <?php echo estSelec('10', $hDev); ?> value="10">10</option>
	<option <?php echo estSelec('11', $hDev); ?> value="11">11</option>
	<option <?php echo estSelec('12', $hDev); ?> value="12">12</option>
	<option <?php echo estSelec('13', $hDev); ?> value="13">13</option>
	<option <?php echo estSelec('14', $hDev); ?> value="14">14</option>
	<option <?php echo estSelec('15', $hDev); ?> value="15">15</option>
	<option <?php echo estSelec('16', $hDev); ?> value="16">16</option>
	<option <?php echo estSelec('17', $hDev); ?> value="17">17</option>
	<option <?php echo estSelec('18', $hDev); ?> value="18">18</option>
 </select>

 <select name="m_deb" id="m_deb" class="correct">
  	<option value="A">Minute </option>
 	<option <?php echo estSelec('00', $mDev); ?> value="00">00</option>
 	<option <?php echo estSelec('15', $mDev); ?> value="15">15</option>
 	<option <?php echo estSelec('30', $mDev); ?> value="30">30</option>
 	<option <?php echo estSelec('45', $mDev); ?> value="45">45</option>
 </select>

<span class="tooltip" style="display:none"> Heure de début incorrecte.</span>


<!-- ##### DUREE ##### -->
&nbsp;&nbsp;<label for="h_duree" style="display:inline;">Durée </label>
<select name="h_duree" id="h_duree" class="correct">
 	<option value="A">Heure</option>
 	<option <?php echo estSelec('0', $hDur); ?> value="0">0</option>
	<option <?php echo estSelec('1', $hDur); ?> value="1">1</option>
	<option <?php echo estSelec('2', $hDur); ?> value="2">2</option>
	<option <?php echo estSelec('3', $hDur); ?> value="3">3</option>
	<option <?php echo estSelec('4', $hDur); ?> value="4">4</option>
	<option <?php echo estSelec('5', $hDur); ?> value="5">5</option>
	<option <?php echo estSelec('6', $hDur); ?> value="6">6</option>
 </select>

 <select name="m_duree" id="m_duree" class="correct">
  	<option value="A">Minute</option>
 	<option <?php echo estSelec('00', $mDur); ?> value="00">00</option>
 	<option <?php echo estSelec('15', $mDur); ?> value="15">15</option>
 	<option <?php echo estSelec('30', $mDur); ?> value="30">30</option>
 	<option <?php echo estSelec('45', $mDur); ?> value="45">45</option>
 </select>
 
<span class="tooltip" style="display:none"> Durée incorrecte.</span><br>

<!-- ###################### COMBINAISON PROMO/GROUPE/SALLE ###################### -->

<!-- #### LISTE PROMOTION ### -->
<br />
<label for="promo">Promotion </label>
<select name="promo" id="promo" class="correct" onchange="grDynamique()">
	<option value="A">Promotion</option>
	<?php
	    	$sql1 = 'SELECT * FROM promotion, departement WHERE promotion.id_dpt = departement.id_dpt ORDER BY nom_dpt, nom_promo';
    		$stmt1 = $pdo->query($sql1);
		//$query1=mysql_query('SELECT * FROM promotion, departement WHERE promotion.id_dpt=departement.id_dpt ORDER BY nom_dpt, nom_promo');
		//while($res1=mysql_fetch_array($query1))
		while ($res1 = $stmt1->fetch())
		{
			echo '<option value=\''.$res1['id_promo'].'\'>'.$res1['nom_dpt'].' '.$res1['nom_promo'].'</option>';
		}
	?>
</select>

<!-- #### LISTE GROUPE ### -->
<select name="groupe" id="groupe" class="correct" style="display: none">
</select>

<!-- #### LISTE MATIERE ### -->
<select name="matiere" id="matiere" class="correct" style="display: none" onchange="affBtn()">
</select>

<!-- #### LISTE SALLE ### -->
<select name="salle" id="salle" style="display: none" class="correct" onchange="affBtn()">
	<option value="A">Salle </option>
	<?php
		$sql2 = 'SELECT * FROM salle ORDER BY nom_salle';
    		$stmt2 = $pdo->query($sql2);
		//$query1=mysql_query('SELECT * FROM salle ORDER BY nom_salle');
		//while($res1=mysql_fetch_array($query1))
		 while ($res2 = $stmt2->fetch())
		{
			echo '<option value=\''.$res2['id_salle'].'\'>'.$res2['nom_salle'].'</option>';
		}
	?>
</select>

<!-- #### BOUTON AJOUT PROMO ### -->
<button type="button" id="btnAddCombi" style="display:none" onclick="recupCombi()">+</button>
<br>
<br>

<!-- ### TABLEAU RECAPITULATIF ### -->
<div id="tabRecap"></div>
<br>

<div id="blocBar" class="blocBar" style="display: none"><img id="progressBar" src="../images/ajLoader.gif"></div>


<!-- ##################### IMPORT JAVASCRIPT ################## -->
<script src="javascript/up_stage1.js"></script>
<script src="javascript/js_calendrier.js"></script>
<script src="../javascript/jquery-1.7.1.js"></script>
