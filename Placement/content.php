<?php
	$invalide = array('/','/\/',':','.');
	
	if(!isset($p))
	{
	$p="home";
	}

	$p = str_replace($invalide,' ',$p);
	
	if(!file_exists($p.".php")) 
	{
	$p = "home";
	}
	
	include($p.".php");
?>