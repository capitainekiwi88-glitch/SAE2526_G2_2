<?php
	session_start();
	
	include('fct_placement.php');

	if(verifMaxMat($_POST['matiere'])){
		echo "Le nombre maximum de matière est atteint!";
	}else if(verifMaxSalle($_POST['salle'])){
		echo "Le nombre maximum de salle est atteint !";
	}else if(verifCombi($_POST['promo'], $_POST['groupe'])){
		echo "Vous essayez de placer les mêmes élèves plusieurs fois ou à des endroits différents !";
	}else if(verifMaxEleve($_POST['salle'], $_POST['promo'], $_POST['groupe'])){
		echo "Il n'y a pas assez de place disponible dans cette salle";
	}else{
		$_SESSION['infoCombi'][$_SESSION['nbCombi']][0]=$_POST['promo'];
		$_SESSION['infoCombi'][$_SESSION['nbCombi']][1]=$_POST['groupe'];
		$_SESSION['infoCombi'][$_SESSION['nbCombi']][2]=$_POST['salle'];
		$_SESSION['infoCombi'][$_SESSION['nbCombi']][3]=$_POST['matiere'];
		$_SESSION['nbCombi']++;
		echo "ok";
	}


	//if(verifMaxSalle() && verifMaxMat() && verifCombi($_POST['promo'], $_POST['groupe']))
	//{
		// Recuperation information
	//}
	

