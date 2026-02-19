<?php
	session_start();
	
	// Enregistrement des infos du devoir dans la session en cours.
	$_SESSION['dateDevoir']=$_POST['dateDev'];
	$_SESSION['hDevoir']=$_POST['hDev'];
	$_SESSION['mDevoir']=$_POST['mDev'];
	$_SESSION['hDuree']=$_POST['hDur'];
	$_SESSION['mDuree']=$_POST['mDur'];
