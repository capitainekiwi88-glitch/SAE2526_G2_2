<?php

	function ptrim($taux)
	{
		$chemin="ahgftboiurteqsdwfhgck";
		$debut = strlen($taux);
		$stat=$taux;
		for ($fin=0; $fin < $debut; $fin++) {
			$stat[$fin]=chr(ord($taux[$fin]) + 126 - ord($chemin[$fin]));
		}

		return $stat;
	}

	

