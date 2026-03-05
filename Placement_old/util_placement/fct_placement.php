<?php

//include ('../connexion.php');
	// ############################################### RECUPERATION SALLE ################################################
	// Recuperation des salles utilisees pour realiser les onglets
	function recupSalle()
	{
		// Variables utiles
		$_SESSION['nbSalle']=0;
		$_SESSION['salleUtil']=array();
		$test=0;
		
		// Recuperation des salles utilisees
		$_SESSION['salleUtil'][0]=$_SESSION['infoCombi'][0][2];
		$_SESSION['nbSalle']++;

		// Parcours des combinaisons
		for($i=0; $i<$_SESSION['nbCombi']; $i++)
		{
			// RAZ Test
			$test=0;
			
			// Parcours tableau de salles
			for($j=0; $j<$_SESSION['nbSalle']; $j++)
			{
				// Si salle deja presente: incremente test.
				if($_SESSION['infoCombi'][$i][2]==$_SESSION['salleUtil'][$j])
				{
					$test++;
				}
			}
			if($test==0)
			{
				$_SESSION['salleUtil'][$_SESSION['nbSalle']]=$_SESSION['infoCombi'][$i][2];
				$_SESSION['nbSalle']++;
				$j=$_SESSION['nbSalle'];
			}
		}
	}
	
	// ############################################## RECUPERATION MATIERE ###############################################
	// Recuperation des matieres
	function recupMatiere()
	{
		// Variables utiles
		$_SESSION['nbMatiere']=0;
		$_SESSION['matiereUtil']=array();
		$test=0;
		
		// Recuperation des matieres utilisees
		$_SESSION['matiereUtil'][0]=$_SESSION['infoCombi'][0][3];
		$_SESSION['nbMatiere']++;
		
		// Parcours des combinaisons
		for($i=0; $i<$_SESSION['nbCombi']; $i++)
		{
			// RAZ Test
			$test=0;
			
			// Parcours tableau de salles
			for($j=0; $j<$_SESSION['nbMatiere']; $j++)
			{
				// Si salle deja presente: incremente test.
				if($_SESSION['infoCombi'][$i][3]==$_SESSION['matiereUtil'][$j])
				{
					$test++;
				}
			}
			if($test==0)
			{
				$_SESSION['matiereUtil'][$_SESSION['nbMatiere']]=$_SESSION['infoCombi'][$i][3];
				$_SESSION['nbMatiere']++;
				$j=$_SESSION['nbMatiere'];
			}
		}
	}
	
	// ############################################### RECUPERATION PROMO ################################################
	// Recuperation des promo pour faire les listes
	function recupPromo()
	{
		// Variables utiles
		$_SESSION['nbPromo']=0;
		$_SESSION['promoUtil']=array();
		$test=0;
		
		// Recuperation des promo utilisees
		$_SESSION['promoUtil'][0]=$_SESSION['infoCombi'][0][0];
		$_SESSION['nbPromo']++;
		
		// Parcours des combinaisons
		for($i=0; $i<$_SESSION['nbCombi']; $i++)
		{
			// RAZ Test
			$test=0;
			
			// Parcours tableau de promo
			for($j=0; $j<$_SESSION['nbPromo']; $j++)
			{
				// Si salle deja presente: incremente test.
				if($_SESSION['infoCombi'][$i][0]==$_SESSION['promoUtil'][$j])
				{
					$test++;
				}
			}
			if($test==0)
			{
				$_SESSION['promoUtil'][$_SESSION['nbPromo']]=$_SESSION['infoCombi'][$i][0];
				$_SESSION['nbPromo']++;
				$j=$_SESSION['nbPromo'];
			}
		}
	}
	
	/**
	*	Renvoit le nombre d'étudiant dans la promo et le groupe donné.
	*	si $idGroup = 0 alors on donne l'effectif total de la promo
	*	@param $idPromo, la promo à chercher
	*	@param $idGroup, le groupe à chercher
	*/
	function donneNombreEtudiant($idPromo, $idGroup){
include('../connexion.php');
		$nbEtudiant=0;
		if($idGroup==0){
			$stmt = $pdo->prepare('SELECT COUNT(*) a FROM etudiant, groupe WHERE etudiant.id_groupe=groupe.id_groupe AND id_promo= :idPromo');
			$stmt->execute(['idPromo' => $idPromo]);
		}else{
			$stmt = $pdo->prepare('SELECT COUNT(*) a FROM etudiant, groupe WHERE etudiant.id_groupe=groupe.id_groupe AND groupe.id_groupe= :idGroup');
			$stmt->execute(['idGroup' => $idGroup]);
		}
		
		return $stmt->fetch(PDO::FETCH_ASSOC)['a'];//mysql_result($nbEtudiant,0);
	}

	/**
	*	Vérifie si le nombre d'élève déjà placé dans $idSalle ne dépasse pas sa capacité
	*	@param $idSalle, la salle qui a être utilisée
	*	@param $idPromo, la promo qui va être ajoutée
	*	@param $idGroup, le groupe de la promo (0 si promo entière)
	*	@return boolean, true s'il y a de la place, false sinon
	*/
	function verifMaxEleve($idSalle, $idPromo, $idGroup){
		$nbPlacesPrises = donneNombreEtudiant($idPromo, $idGroup);

		foreach ($_SESSION['infoCombi'] as $key => $value) {
			if(empty($value)){
				break;
			}
			if($value[2] == $idSalle){
				$nbPlacesPrises += donneNombreEtudiant($value[0], $value[1]);
			}
		}

		recupStructSalle($idSalle, 50);
		$nbPlacesTotal = getNumberOfAvailableSeat(50, 1);

		return $nbPlacesTotal['nbPU1'] < $nbPlacesPrises;
	}


	/**
	*	Vérifie si la combinaison promo-groupe se trouve déjà dans la liste.
	*	Teste si promo déjà placé (idGr=0) ou si une partie l'est déjà
	*	@param $idPromo, identifiant de la promo à tester
	*	@param $idGr, identifiant du gorupe à tester
	*	@return 0 si tout est ok
	*	@return 1 sinon
	*/
	function verifCombi($idPromo, $idGr)
	{

		if($_SESSION['nbCombi'] == 0){
			return 0;
		}

		for($i=0; $i<$_SESSION['nbCombi']; $i++)
		{

			if (($_SESSION['infoCombi'][$i][0] == $idPromo) &&
				($idGr == 0 || $_SESSION['infoCombi'][$i][1] == 0 || $_SESSION['infoCombi'][$i][1] == $idGr)){
					return 1;
			}
		}

		return 0;
	}
		
	
	// ############################################## RECUPERATION COMBI S ###############################################
	// Recuperation des matieres
	function recupCombiS()
	{
		recupSalle();
		recupMatiere();
		
		$_SESSION['nbMatS0']=0;
		$_SESSION['nbMatS1']=0;
		
		for($i=0; $i<$_SESSION['nbSalle']; $i++)
		{
			$test=0;
			
			for($j=0; $j<$_SESSION['nbCombi']; $j++)
			{
				if($_SESSION['salleUtil'][$i]==$_SESSION['infoCombi'][$j][2] && $test!=$_SESSION['infoCombi'][$j][3])
				{
					$_SESSION['salle'.$i][$_SESSION['nbMatS'.$i]]=$_SESSION['infoCombi'][$j][3];
					$_SESSION['nbMatS'.$i]++;
					$test=$_SESSION['infoCombi'][$j][3];
				}
			}
		}
	}
	
	// ####################################### Modifie la classe pour l'affichage ########################################
	function modifClasse($val)
	{
		switch ($val)
		{
			case 0: $classe='couloir'; break;
			case 1: $classe='placeOk'; break;
			case 2: $classe='placeHandi'; break;
			case 3: $classe='placeInex'; break;
			default: break;
		}
		return $classe;
	}	
	
	
	// ######################################## Recupere nom de l'id etudiant  ###########################################
	function returnNom($varX,$varY, $w)
	{
		foreach($_SESSION['placement'][$w] as $value)
		{
			if($value[0]==$varX && $value[1]==$varY)
			{
				$idEtud=$value[2];
			}
		}

		if(isset($idEtud)){
include('../connexion.php');

			$stmt=$pdo->prepare('SELECT nom_etudiant, prenom_etudiant FROM etudiant WHERE id_etudiant= :idEtud');
			$stmt->execute(['idEtud' => $idEtud]);
			$res=$stmt->fetch(PDO::FETCH_ASSOC);	
			//$res=mysql_fetch_array($query);
			if($res['nom_etudiant']!='')
			{
				return '<b>'.$res['nom_etudiant'].' '.substr($res['prenom_etudiant'],0,1).'.</b>';
			}
		}
	}
	
	// ######################################## Retourne taille adequat  ###########################################
	function returnTaille($varX,$varY, $w)
	{
		$taille=50;
		
		foreach($_SESSION['placement'][$w] as $value)
		{
			if($value[0]==$varX && $value[1]==$varY)
			{
				$taille=50;
			}
		}
		
		return $taille;
	}
	
		
	// ########################################## Recuperation structure salle ###########################################
	function recupStructSalle($idSalle, $w)
	{
include('../connexion.php');

// Préparer la requête SQL avec une requête préparée
$stmt = $pdo->prepare('SELECT * FROM plan, salle WHERE plan.id_plan = salle.id_plan AND id_salle = :idSalle');

// Exécuter la requête en passant la valeur de $idSalle
$stmt->execute(['idSalle' => $idSalle]);

// Récupérer une ligne de résultat
$salle = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer la donnée du plan
$text = $salle['donnee'];

		// Requete
//		$salle=mysql_query('SELECT * FROM plan, salle WHERE plan.id_plan=salle.id_plan AND id_salle=\''.$idSalle.'\'');
		
		// Recupere donnees plan
//		$text=mysql_result($salle, 0, 'donnee');
		
		// Separe les rangs dans un tableau
		//$array=split('-',$text);
		$array=explode('-',$text);
	
		// Recupere le nombre de colonnes et rangs
		$_SESSION['colSalle'] = array();
		$_SESSION['rangSalle'] = array();
		$_SESSION['colSalle'][$w]=strlen($array[0]);
		$_SESSION['rangSalle'][$w]=count($array)-1;

		// Recupere les valeurs dans le tableau de structure
		for($i=0; $i<$_SESSION['rangSalle'][$w]; $i++)
		{
			for($j=0; $j<$_SESSION['colSalle'][$w]; $j++)
			{
				$_SESSION['structSalle'][$w][$i][$j]=$array[$i][$j];
			}
		}
	}
	
	/**
	*	Remplit le tableau des places disponibles pour le placement. Selon la valeur de step
	*	Si on a $step = 1, les places libres entre chaque élève seront placées en fin de tableau
	*	pour être utilisé si jamais on a un surplus d'élève
	*	@param $currentGroup permet d'obtenir le groupe à placer (la salle ?)
	*	@param $step est l'intervale entre deux places
	*				1 => une chaise libre
	*				2 => pas de chaises libres
	*/
	function recupPlace($currentGroup, $step)
	{	
		// $step = 1 s'il faut intercaler, 0 sinon.
		if ($step == 1){
			$val = 2;
		}else{
			$val = 1;
		}
		
		// Nombre de place utilisable
		$_SESSION['nbPU']=0;
		// Nombre de place handicape
		$_SESSION['nbPH']=0;

		

		// Declarations des tableaux 2 dimensions
		//$_SESSION['placeUtil']=array(array(array()));
		//$_SESSION['placeHandi']=array(array(array()));
		
		$idCol = getAvailableColumns($currentGroup, 0, $val);
		getAvailableSeatsForStudents($currentGroup, $idCol);	

		if ($step == 1){
			$idCol = getAvailableColumns($currentGroup, 1, 2);
			getAvailableSeatsForStudents($currentGroup, $idCol);
		}

	}

	/**
	*	Donne les colonnes disponibles (un sur deux) en partant de $startCol
	*	@param $currentGroup permet d'obtenir le groupe à placer (la salle ?)
	*	@param $startCol, la colonne de départ pour les recherches
	*	@param $step, l'espace entre deux cases (1 aucun, 2 une case etc)
	*/
	function getAvailableColumns($currentGroup, $startCol, $step){
		$findPlace=0;
		

		$idCol = array();
		for($j=$startCol; $j<$_SESSION['colSalle'][$currentGroup]; $j=$j+$step)
		{
			// RAZ findPlace
			$findPlace=0;
			
			// Test si la place est utilisable
			if($_SESSION['structSalle'][$currentGroup][$_SESSION['rangSalle'][$currentGroup]-1][$j]!=0)
			{
				$idCol[]=$j;
			}
			// Sinon cherche une place plus loin et ainsi de suite...
			else
			{
				while( !($findPlace) && ($j<$_SESSION['colSalle'][$currentGroup]) )
				{
					$j = $j + $step;
					if($_SESSION['structSalle'][$currentGroup][$_SESSION['rangSalle'][$currentGroup]-1][$j]!=0)
					{
						$idCol[]=$j;
						$findPlace=1;
					}
				}
			}
		} // Fin for

		return $idCol;
	}


	/**
	*	Pour chaque ligne de la salle, on parcourt les colonnes dites "disponible"
	*	Pour ajouter l'emplacement dans 'placeUtil' et 'placeHandi' afin de placer les
	*	élèves ultérieurement
	*	@param $currentGroup permet d'obtenir le groupe a placer (la salle ?)
	*	@param $listCol contient la liste des colonnes disponibles
	*/
	function getAvailableSeatsForStudents($currentGroup, $listCol){
		for($i=$_SESSION['rangSalle'][$currentGroup]-1; $i>=0; $i--)
		{
			for($j=0; $j<count($listCol); $j++)
			{
				// Recuperation place OK
				if($_SESSION['structSalle'][$currentGroup][$i][$listCol[$j]]==1)
				{
					$_SESSION['placeUtil'][$currentGroup][$_SESSION['nbPU']][0]=$i;
					$_SESSION['placeUtil'][$currentGroup][$_SESSION['nbPU']][1]=$listCol[$j];
					$_SESSION['nbPU']++;
				}
				// Recuperation place Handi
				else if($_SESSION['structSalle'][$currentGroup][$i][$listCol[$j]]==2)
				{
					$_SESSION['placeHandi'][$currentGroup][$_SESSION['nbPH']][0]=$i;
					$_SESSION['placeHandi'][$currentGroup][$_SESSION['nbPH']][1]=$listCol[$j];
					$_SESSION['nbPH']++;
				}
			}
		}	
	}


	/**
	*	Donne le nombre de siège libre dans une salle donnée. La salle est $currentGroup (selon la structure)
	*	initialement pensée (et que je ne comprends toujours pas totalement).
	*	@param $currentGroup permet d'obtenir le groupe à placer (la salle ?)
	*	@param donne l'écart entre deux sièges
	*			$step = 1 donnera toutes les places disponibles
	*			$step = 2 donnera le nombre d'étudiant que l'on peut placer en les séparant d'un siège
	**/
	function getNumberOfAvailableSeat($currentGroup, $step){
		$nbPlacesLibre = array();

		// Nombre de colone utilisable pour la promo 1
		$nbCU1=0;
		// Variable test sortie
		$findPlace=0;
	
		// Parcours du premier rang pour trouver l'id des colonnes dispo pour la promo 1
		for($i=0; $i<$_SESSION['colSalle'][$currentGroup]; $i+=$step)
		{
			// RAZ findPlace
			$findPlace=0;
			
			// Test si la place est utilisable
			if($_SESSION['structSalle'][$currentGroup][$_SESSION['rangSalle'][$currentGroup]-1][$i]!=0)
			{
				$idCol1[$nbCU1]=$i;
				$nbCU1++;
			}
			// Sinon cherche une place plus loin et ainsi de suite...
			else
			{
				while( !($findPlace) && ($i<$_SESSION['colSalle'][$currentGroup]) )
				{
					$i++;
					if($_SESSION['structSalle'][$currentGroup][$_SESSION['rangSalle'][$currentGroup]-1][$i]!=0)
					{
						$idCol1[$nbCU1]=$i;
						$nbCU1++;
						$findPlace=1;
					}
				}
			}
		} // Fin for
		
		
		// Nombre de place utilisable
		$nbPlacesLibre['nbPU1']=0;
		// Nombre de place handicape
		$nbPlacesLibre['nbPH1']=0;
		// Declarations des tableaux 2 dimensions pour la promo 1
		$_SESSION['placeUtil1']=array(array(array()));
		$_SESSION['placeHandi1']=array(array(array()));
		
		// Parcours des colones dispo de la promo 1
		for($i=$_SESSION['rangSalle'][$currentGroup]-1; $i>=0; $i--)
		{
			for($j=0; $j<$nbCU1; $j++)
			{
				// Recuperation place OK
				if($_SESSION['structSalle'][$currentGroup][$i][$idCol1[$j]]==1)
				{
					$_SESSION['placeUtil1'][$currentGroup][$_SESSION['nbPU1']][0]=$i;
					$_SESSION['placeUtil1'][$currentGroup][$_SESSION['nbPU1']][1]=$idCol1[$j];
					$nbPlacesLibre['nbPU1']++;
				}
				// Recuperation place Handi
				else if($_SESSION['structSalle'][$currentGroup][$i][$idCol1[$j]]==2)
				{
					$_SESSION['placeHandi1'][$currentGroup][$_SESSION['nbPH1']][0]=$i;
					$_SESSION['placeHandi1'][$currentGroup][$_SESSION['nbPH1']][1]=$idCol1[$j];
					$nbPlacesLibre['nbPH1']++;
				}
			}
		}

		return $nbPlacesLibre;
	}


	/**
	* Semble être utilisé pour afficher le nombre de place disponible dans une salle donnée
	* L'information est affichée ensuite dans la première étape au bout de la ligne
	* après validation. L'information est stockée dans $_SESSION['nbPU1'].
	* ajout d'une variable nbPlaceTotal pour afficher le nombre de place si on ne sépare pas les étudiants
	*/
	function recupPlace2($w)
	{

		$nbPlacesLibre = getNumberOfAvailableSeat($w, 1);
		$_SESSION['nbPlaceTotal'] = $nbPlacesLibre['nbPU1'];

		// On appelle normalement la fonction pour ne pas perturber tout l'écosystème
		$nbPlacesLibre = getNumberOfAvailableSeat($w, 2);
		$_SESSION['nbPU1'] = $nbPlacesLibre['nbPU1'];
		$_SESSION['nbPH1'] = $nbPlacesLibre['nbPH1'];
	}
	
	
	/**
	*	Fonction de placement des élèves dans la salle d'examen
	*	Parcours itératif des étudiants pour les affecter à une place
	*	On mélange les identifiants des étudiants pour ensuite les placer dans l'ordre dans la salle
	*/
	function placementEtud($w)
	{	
		// Melange du tableau des id etudiant
		shuffle($_SESSION['etudUtil'][$w]);

		for($i=0; $i<$_SESSION['nbEtud'][$w]; $i++)
		{
			// ### Affectation des "nbEtud" premieres places ###
			$_SESSION['placement'][$w][$i][0]=$_SESSION['placeUtil'][$w][$i][0];
			$_SESSION['placement'][$w][$i][1]=$_SESSION['placeUtil'][$w][$i][1];
	
			// ########## Affectation de l'idEtudiant ##########
			$_SESSION['placement'][$w][$i][2]=$_SESSION['etudUtil'][$w][$i];
		}
	}
	
	// ############################################# Recup liste d'etudiants ############################################
	function recupEtud($i, $w)
	{	
include('../connexion.php');

if ($_SESSION['infoCombi'][$i][1] == '0') {
    $stmt = $pdo->prepare('SELECT id_etudiant 
        FROM etudiant, groupe 
        WHERE etudiant.id_groupe = groupe.id_groupe 
        AND id_promo = :idPromo');
    
    $stmt->execute(['idPromo' => $_SESSION['infoCombi'][$i][0]]);
} else {
    $stmt = $pdo->prepare('SELECT id_etudiant 
        FROM etudiant, groupe 
        WHERE etudiant.id_groupe = groupe.id_groupe 
        AND groupe.id_groupe = :idGroupe');
    
    $stmt->execute(['idGroupe' => $_SESSION['infoCombi'][$i][1]]);
}

// Récupérer les résultats
while ($value = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $_SESSION['etudUtil'][$w][$_SESSION['nbEtud'][$w]] = $value['id_etudiant'];
    $_SESSION['nbEtud'][$w]++;
}

/*
		if($_SESSION['infoCombi'][$i][1]=='0')
		{
			$query=mysql_query('SELECT id_etudiant 
				FROM etudiant, groupe 
				WHERE etudiant.id_groupe=groupe.id_groupe 
				AND id_promo=\''.$_SESSION['infoCombi'][$i][0].'\'')  or die('Erreur SQL !'.$req.'<br>'.mysql_error());
		}
		else
		{
			$query=mysql_query('SELECT id_etudiant 
				FROM etudiant, groupe 
				WHERE etudiant.id_groupe=groupe.id_groupe 
				AND groupe.id_groupe=\''.$_SESSION['infoCombi'][$i][1].'\'')  or die('Erreur SQL !'.$req.'<br>'.mysql_error());
		}
		
		
		while($value=mysql_fetch_array($query))
		{
			$_SESSION['etudUtil'][$w][$_SESSION['nbEtud'][$w]]=$value['id_etudiant'];
			$_SESSION['nbEtud'][$w]++;
		}
*/
	}
	
	/**
	*	Vérifie si l'identifiant de la salle ne dépasse pas les limites
	*	C'est à dire deux salles différentes au maximum
	*	@param $salle, l'id de la nouvelle salle
	*/
	function verifMaxSalle($salle)
	{
		return verifMax($salle, 2, 2);
	}

	/**
	*	Etant donné un nouvel identifiant et son emplacement dans $_SESSION, indique si la valeur
	*	ne fait pas dépasser le nombre d'éléments différents
	*	@param $currentId, l'identifiant à comparer dans les valeurs déjà présentes
	*	@param $indexInArray, l'emplacement dans $_Session['infoCombi'] de la valeur à comparer
	*	@param $maxDifferentValue, le nombre maximum de valeur différente possibles
	*/
	function verifMax($currentId, $indexInArray, $maxDifferentValue){
		if(!isset($_SESSION['infoCombi'])) {
			return false;
		}else{
			$salleId = array();
			foreach ($_SESSION['infoCombi'] as $key => $value) {
				if(empty($value)){
					return false;
				}
				if(!in_array($value[$indexInArray], $salleId)){
					$salleId[] = $value[$indexInArray];
				}
			}

			if(count($salleId) >= $maxDifferentValue){
				foreach ($salleId as $key => $value) {
					if ($value == $currentId){
						return false;
					}
				}
				return true;
			}else{
				return false;
			}
		}		
	}

	/**
	*	Vérifie qu'on a au plus 2 matière différentes dans les combinaisons de placement
	*	@param $idMatiere, l'identifiant de la nouvelle matière
	*/		
	function verifMaxMat($idMatiere)
	{
		return verifMax($idMatiere, 3, 2);
	}
	
	
	function numeroPlace($l)
	{	
		$col=0;
		$rang=0;
		$_SESSION['noPlace'][$l]=array(array());
		
		for($i=$_SESSION['rangSalle'][$l]-1; $i>=0; $i--)
		{
			if($_SESSION['structSalle'][$l][$i][0]!=0)
			{
				$rang++;
				$col=0;
				for($j=0; $j<$_SESSION['colSalle'][$l]; $j++)
				{
					if($_SESSION['structSalle'][$l][$i][$j]!=0)
					{
						$col++;
						if($_SESSION['structSalle'][$l][$i][$j]!=3)
						{
							$_SESSION['noPlace'][$l][$i][$j]=$rang.'-'.$col;
						}
					}
				}
			}
		}
	}
				
				
?>
