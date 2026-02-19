<?php
	session_start();
	
	$_SESSION['idCombi']=$_POST['idCombi'];

	$_SESSION['nbCombi']--;

	for($i=$_SESSION['idCombi']; $i<$_SESSION['nbCombi']; $i++)
	{
		$_SESSION['infoCombi'][$i]=$_SESSION['infoCombi'][$i+1];
	}

?>