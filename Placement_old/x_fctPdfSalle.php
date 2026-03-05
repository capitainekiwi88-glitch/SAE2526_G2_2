<?php

	// ############################ Recuperation structure salle ############################
	function recupStructSalle($idSalle)
	{
		include('connexion.php');
		// Requete
		$stmt = $pdo->prepare('SELECT * FROM plan, salle WHERE plan.id_plan = salle.id_plan AND id_salle = :idSalle');
		$stmt->execute(['idSalle' => $idSalle]);
		
		// Recupere donnees plan
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$text = $row['donnee'];
		
		// Separe les rang dans un tableau
		// $array=split('-',$text);
		$array=explode('-',$text);
		
		// Recupere le nombre de colonne et rang
		$_SESSION['colSalle']=strlen($array[0]);
		$_SESSION['rangSalle']=count($array)-1;
		
		// Recupere les valeurs dans le tableau de structure
		for($i=0; $i<$_SESSION['rangSalle']; $i++)
		{
			for($j=0; $j<$_SESSION['colSalle']; $j++)
			{
				$_SESSION['structSalle'][$i][$j]=$array[$i][$j];
			}
		}
	}
	
	// ############################# Retourne le numero de place #############################
	function numeroPlace()
	{	
		$col=0;
		$rang=0;
		$_SESSION['noPlace']=array(array());
		
		for($i=$_SESSION['rangSalle']-1; $i>=0; $i--)
		{
			if($_SESSION['structSalle'][$i][0]!=0)
			{
				$rang++;
				$col=0;
				for($j=0; $j<$_SESSION['colSalle']; $j++)
				{
					if($_SESSION['structSalle'][$i][$j]!=0)
					{
						$col++;
						if($_SESSION['structSalle'][$i][$j]!=3)
						{
							$_SESSION['noPlace'][$i][$j]=$rang.'-'.$col;
						}
					}
				}
			}
		}
	}
	
	// ############################ Recupere Coordonnees prenom and nom ########################
	/**
	*	L'emplacement de l'élève semble être stocké dans la base de données
	*	Etant donné son emplacement sur la carte, le devoir et la salle, on retrouve son nom et son prénom
	*/
	function returnNom($varX,$varY,$idDevoir, $idSalle)
	{
		include('connexion.php');
		$name=array();
		$stmt = $pdo->prepare('SELECT prenom_etudiant, nom_etudiant, place_x, place_y FROM etudiant, placement WHERE etudiant.id_etudiant = placement.id_etudiant AND id_salle = :idSalle AND placement.id_devoir = :idDevoir AND place_x = :varX AND place_y = :varY');
		$stmt->execute(['idSalle' => $idSalle, 'idDevoir' => $idDevoir, 'varX' => $varX, 'varY' => $varY]);
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		if($res && $res['nom_etudiant']!='')
		{
			$name[0]=$res['nom_etudiant'];
			$name[1]=$res['prenom_etudiant'];
			return $name;
		}		
	}
	
	// ###################### Retourne la couleur en fonction de la place ######################
	function returnValCol($val)
	{
		$valCol=array();
		switch($val)
		{	
			case '0': $valCol[0]=255; $valCol[1]=255; $valCol[2]=255; break;
			case '1': $valCol[0]=220; $valCol[1]=220; $valCol[2]=220; break;
			case '2': $valCol[0]=51; $valCol[1]=204; $valCol[2]=255; break;
			case '3': $valCol[0]=255; $valCol[1]=255; $valCol[2]=255; break;
			default : $valCol=0; break;
		}
		
		return $valCol;
	}

	// ########### Retourne l'orientation en fonction du nombre de colonne et de rang ###########
	function returnOrientation()
	{
		if($_SESSION['rangSalle']*9<$_SESSION['colSalle']*14)
		{
			return 'L';
		}
		else
		{
			return 'P';
		}
	}
	
	// ################ Retourne largeur colonne en fonction du nombre de place ################
	function returnSizeCol($orientation)
	{
		if($orientation=='L')
		{
			$sizeBloc=275;
		}
		else
		{
			$sizeBloc=190;
		}
		
		return $sizeBloc/$_SESSION['colSalle'];
	}
?>