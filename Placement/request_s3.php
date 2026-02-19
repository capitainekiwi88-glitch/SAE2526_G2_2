<?php
	session_start();
	
	$_SESSION['structSalle'][$_POST['coX']][$_POST['coY']]=$_POST['val'];
?>