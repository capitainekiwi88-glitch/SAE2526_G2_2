<?php	
	// ########################################## Recuperation no place salle ###########################################
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
	
	// ########################################## Recuperation structure salle ###########################################
	function recupStructSalle($idSalle)
	{
		include('connexion.php');
		// Requete
		$salle=mysql_query('SELECT * FROM plan, salle WHERE plan.id_plan=salle.id_plan AND id_salle=\''.$idSalle.'\'');
		
		// Recupere donnees plan
		$text=mysql_result($salle, 0, 'donnee');
		
		// Separe les rang dans un tableau
		//$array=split('-',$text);
		$array=explode('-',$text);
		// RAZ VARIABLES
		unset($_SESSION['colSalle']);
		unset($_SESSION['rangSalle']);
		unset($_SESSION['structSalle']);
		
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
	
	
	// ############################## Recupere tous les etudiants d'une salle pour un devoir ##############################
	function recupListeSalle($idDevoir, $idSalle)
	{
		
		mysql_query("			CREATE TEMPORARY TABLE R1
								SELECT etudiant.id_etudiant, id_salle
								FROM etudiant, devoir_groupe
								WHERE etudiant.id_groupe=devoir_groupe.id_groupe
								AND devoir_groupe.id_devoir=$idDevoir
								AND devoir_groupe.id_salle=$idSalle
								UNION
								SELECT etudiant.id_etudiant, id_salle
								FROM etudiant, devoir_promo, groupe
								WHERE etudiant.id_groupe=groupe.id_groupe
								AND groupe.id_promo=devoir_promo.id_promo
								AND devoir_promo.id_devoir=$idDevoir
								AND devoir_promo.id_salle=$idSalle
							");
							
		$query1=mysql_query("	SELECT nom_etudiant, prenom_etudiant, nom_groupe, nom_promo, nom_dpt, nom_salle, place_x, place_y
								FROM R1, placement, groupe, etudiant, salle, promotion, departement
								WHERE R1.id_etudiant=placement.id_etudiant
								AND R1.id_etudiant=etudiant.id_etudiant
								AND R1.id_salle=salle.id_salle
								AND etudiant.id_groupe=groupe.id_groupe
								AND groupe.id_promo=promotion.id_promo
								AND promotion.id_dpt=departement.id_dpt
								AND id_devoir=$idDevoir
								ORDER BY nom_etudiant
						   ");
		
		// RAZ Variables
		$_SESSION['data']=array(array());
		$_SESSION['cpt']=0;
		
		while($result=mysql_fetch_array($query1))
		{
			$_SESSION['data'][$_SESSION['cpt']][0]=utf8_decode($result['nom_etudiant']);
			$_SESSION['data'][$_SESSION['cpt']][1]=utf8_decode($result['prenom_etudiant']);
			$_SESSION['data'][$_SESSION['cpt']][2]=$result['nom_dpt'].' '.$result['nom_promo'];
			$_SESSION['data'][$_SESSION['cpt']][3]=$result['nom_groupe'];
			$_SESSION['data'][$_SESSION['cpt']][4]=$result['nom_salle'];
			$_SESSION['data'][$_SESSION['cpt']][5]=$_SESSION['noPlace'][$result['place_x']][$result['place_y']];
			$_SESSION['cpt']++;
		}
		
	}
	
	// ############################## Recupere tout les etudiants d'une promo pour un devoir ##############################
	function recupListePromo($idDevoir, $idPromo)
	{
		mysql_query("			CREATE TEMPORARY TABLE R1
								SELECT id_etudiant, id_salle
								FROM etudiant, devoir_promo, groupe
								WHERE etudiant.id_groupe=groupe.id_groupe
								AND groupe.id_promo=devoir_promo.id_promo
								AND devoir_promo.id_promo=$idPromo
								AND id_devoir=$idDevoir
								UNION
								SELECT id_etudiant, id_salle
								FROM etudiant, devoir_groupe, groupe
								WHERE etudiant.id_groupe=groupe.id_groupe
								AND devoir_groupe.id_groupe=groupe.id_groupe
								AND groupe.id_promo=$idPromo
								AND id_devoir=$idDevoir
					");

		$query1=mysql_query("	SELECT salle.id_salle s, nom_etudiant, prenom_etudiant, nom_groupe, nom_promo, nom_dpt, nom_salle, place_x, place_y
								FROM R1, placement, groupe, etudiant, salle, promotion, departement
								WHERE R1.id_etudiant=placement.id_etudiant
								AND R1.id_etudiant=etudiant.id_etudiant
								AND R1.id_salle=salle.id_salle
								AND etudiant.id_groupe=groupe.id_groupe
								AND groupe.id_promo=promotion.id_promo
								AND promotion.id_dpt=departement.id_dpt
								AND id_devoir=$idDevoir
								ORDER BY nom_salle, nom_promo, nom_etudiant
							");
							
		// RAZ Variables
		$_SESSION['data']=array(array());
		$_SESSION['cpt']=0;
		
		// Recuperation premier tour
		$result=mysql_fetch_array($query1);
		$salle=$result['s'];
		// Recup struct Salle + place
		recupStructSalle($result['s']);
		numeroPlace();
		$_SESSION['data'][$_SESSION['cpt']][0]=utf8_decode($result['nom_etudiant']);
		$_SESSION['data'][$_SESSION['cpt']][1]=utf8_decode($result['prenom_etudiant']);
		$_SESSION['data'][$_SESSION['cpt']][2]=$result['nom_dpt'].' '.$result['nom_promo'];
		$_SESSION['data'][$_SESSION['cpt']][3]=$result['nom_groupe'];
		$_SESSION['data'][$_SESSION['cpt']][4]=$result['nom_salle'];
		$_SESSION['data'][$_SESSION['cpt']][5]=$_SESSION['noPlace'][$result['place_x']][$result['place_y']];
		$_SESSION['cpt']++;
		
		while($result=mysql_fetch_array($query1))
		{
			if($salle!=$result['s'])
			{
				// Recup struct Salle + place
				recupStructSalle($result['s']);
				numeroPlace();
				$salle=$result['s'];
			}
		
			$_SESSION['data'][$_SESSION['cpt']][0]=utf8_decode($result['nom_etudiant']);
			$_SESSION['data'][$_SESSION['cpt']][1]=utf8_decode($result['prenom_etudiant']);
			$_SESSION['data'][$_SESSION['cpt']][2]=$result['nom_dpt'].' '.$result['nom_promo'];
			$_SESSION['data'][$_SESSION['cpt']][3]=$result['nom_groupe'];
			$_SESSION['data'][$_SESSION['cpt']][4]=$result['nom_salle'];
			$_SESSION['data'][$_SESSION['cpt']][5]=$_SESSION['noPlace'][$result['place_x']][$result['place_y']];
			$_SESSION['cpt']++;
		}
		
		
	}
	

?>
