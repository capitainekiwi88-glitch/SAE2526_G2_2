<?php
	session_start();

	/**
	*	On récupère les identifiants de ligne et de colonne pour les deux places à intervertir
	*/
	$utilSalle = $_GET['utilSalle'];
	$utilAcol = $_GET['utilAcol'];
	$utilAlin = $_GET['utilAlin'];
	$utilBcol = $_GET['utilBcol'];
	$utilBlin = $_GET['utilBlin'];
//echo "entrée";
//print_r($_SESSION['placeUtil'][$utilSalle]);
	/**
	*	On regarde si les places sont déjà attribuées
	*/
	$indexA = array_search(array($utilAcol, $utilAlin), $_SESSION['placeUtil'][$utilSalle]);
	$indexB = array_search(array($utilBcol, $utilBlin), $_SESSION['placeUtil'][$utilSalle]);
/*echo("recherche");print_r(array($utilAcol, $utilAlin));
echo("A:".$indexA);echo("\n");
echo("recherche");print_r(array($utilBcol, $utilBlin));
echo("B:".$indexB);
*/

	/**
	*	Nous avons trois cas:
	*		- L'emplacement B n'était pas attribué
	*		- L'emplacement A n'était pas attribué
	*		- Les deux étaient déjà attribués
	*/
	if (!isset($_SESSION['placement'][$utilSalle][$indexB])){
		$_SESSION['placement'][$utilSalle][$indexB] = array($_SESSION['placeUtil'][$utilSalle][$indexB][0],
												   $_SESSION['placeUtil'][$utilSalle][$indexB][1],
												   $_SESSION['placement'][$utilSalle][$indexA][2]);
		unset($_SESSION['placement'][$utilSalle][$indexA]);


	}else if (!isset($_SESSION['placement'][$utilSalle][$indexA])){
		$_SESSION['placement'][$utilSalle][$indexA] = array($_SESSION['placeUtil'][$utilSalle][$indexA][0],
												   $_SESSION['placeUtil'][$utilSalle][$indexA][1],
												   $_SESSION['placement'][$utilSalle][$indexB][2]);
		unset($_SESSION['placement'][$utilSalle][$indexB]);

	}else{
		$tmp = $_SESSION['placement'][$utilSalle][$indexA][2];

		$_SESSION['placement'][$utilSalle][$indexA][2] = $_SESSION['placement'][$utilSalle][$indexB][2];
		$_SESSION['placement'][$utilSalle][$indexB][2] = $tmp;
	}

	//print_r($_SESSION['placement']);
	//echo "sortie";