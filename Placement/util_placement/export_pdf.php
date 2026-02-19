<?php
	session_start();
	include('../connexion.php');
	include('../ezpdf/class.ezpdf.php');
	include('fct_pdf.php');
	
	$idDevoir=$_GET['idDevoir'];
	$idSalle=$_GET['idSalle'];
	$idPromo=$_GET['idPromo'];


// ########################################################################################
// 						EXPORT LISTE PDF								  #
// ########################################################################################

	// ######################### LISTE PAR SALLE #########################
	
	function creaPDFSalle($idDevoir, $idSalle)
	{
	include('../connexion.php');

		foreach($pdo->query("SELECT nom_salle FROM salle WHERE id_salle=$idSalle") as $querySalle) {
			$nomSalle=$querySalle['nom_salle'];
		}
		recupStructSalle($idSalle);
		numeroPlace();
		
		$pdf= new Cezpdf('a4','portrait');
		$pdf->selectFont('../ezpdf/fonts/Helvetica.afm');
		
		$cols[0]=mb_convert_encoding('Nom', 'ISO-8859-1', 'UTF-8');
		$cols[1]=mb_convert_encoding('Prénom', 'ISO-8859-1', 'UTF-8');
		$cols[2]='Place';
		$cols[3]='Promotion';
		$cols[4]='Groupe';
		//$cols[5]='Salle';
		//$cols[6]='Signature';
		
	 	recupListeSalle($idDevoir, $idSalle);
	 	
	 	for($i=0; $i<$_SESSION['cpt']; $i++)
	 	{
		    $data[$i][0]=$_SESSION['data'][$i][0];
		    $data[$i][1]=$_SESSION['data'][$i][1];
		    $data[$i][2]=$_SESSION['data'][$i][5];
		    $data[$i][3]=$_SESSION['data'][$i][2];
		    $data[$i][4]=$_SESSION['data'][$i][3];
		}
		
		// ######################### OPTIONS ####################
		$options=array(
			// Ligne
			'showLines' => 1,
			
			// Afficher titre (Non)
			'show Headings' => 1,
			
			// Couleurs ligne
			'shaded' => 1, // gris une ligne sur 2 seulement
			'shadeCol' => array(0.95,0.95,0.95),
			'shadeCol2' => array(0.8,0.8,0.8),
			
			// Couleurs texte
			'textCol' => array(0,0,0),
	
			// Equivalent padding
			'rowGap' => 1, // hauteur des lignes
			'colGap' => 10,
			
			// Couleur ligne (transparente//blanche)
			'lineCol' => array(1,1,1),
			
			// Positions
			'xPos' => 'center',
			//'xOrientation' => 'center',
			
			// Taille
			'width' => 90,
			//'maxWidth' => 400
			);
		
		
		
		// ##################### TITRE - LISTE PAR SALLE ########################################
		
		// ###################### RECUP MATIERE #################
		
		$nbMat=0;
		$mat=array(array());
		
		for($i=0; $i<$_SESSION['nbCombi']; $i++)
		{
			if($_SESSION['infoCombi'][$i][2]==$idSalle)
			{
				$query1=$pdo->prepare('SELECT nom_mat, nom_promo 
					FROM matiere, promotion 
					WHERE matiere.id_promo = promotion.id_promo 
					AND id_mat=:id_mat');
				$query1->execute(['id_mat'=>$_SESSION['infoCombi'][$i][3]]);
				$res=$query1->fetch(PDO::FETCH_ASSOC);
				$mat[$nbMat][0]=mb_convert_encoding($res['nom_mat'], 'ISO-8859-1', 'UTF-8');
				$mat[$nbMat][1]=mb_convert_encoding($res['nom_promo'], 'ISO-8859-1', 'UTF-8');

				$nbMat++;
			}
		}
		
		// ########################## TITRE #####################
		$titleSalle='Liste '.$nomSalle;
		$titleMat='';
		
		for($i=0; $i<$nbMat; $i++)
		{
			if($i==0)
				$delim='';
			else
				$delim=' - ';
				
			$titleMat=$titleMat.$delim.$mat[$i][0]." (".$mat[$i][1].')';
		}
		
		$conf=array(
				'justification' => 'center',
				);
		
		// Nom salle
		$pdf->ezText(mb_convert_encoding($titleSalle, 'ISO-8859-1', 'UTF-8'), 14, $conf);
		
		// Matieres
		$pdf->ezText($titleMat, 10, $conf);
		
		// Date format europeen
		$date=$_SESSION['dateDevoir'];
		
		// Date heure
		$pdf->ezText($date.' - '.$_SESSION['hDevoir']."h".$_SESSION['mDevoir']. mb_convert_encoding(' - Durée: ', 'ISO-8859-1', 'UTF-8').$_SESSION['hDuree']."h".$_SESSION['mDuree'] , 10, $conf);
		
	//echo "PLD ";print_r($data);echo " /PLD";
	//echo "PLC ";print_r($cols);echo " /PLC";
	//echo "PLO ";print_r($options);echo " /PLO";	
		// ########################## TABLEAU ####################
		$pdf->ezTable($data,$cols,' ',$options);

		// ########################## EXPORT #####################
		$pdf->ezStream();
	}
	
	// ######################### LISTE D'EMARGEMENT PAR SALLE #########################
	function creaPDFEmarge($idDevoir, $idSalle)
	{
include('../connexion.php');
		$querySalle=$pdo->query("SELECT nom_salle FROM salle WHERE id_salle=$idSalle");
		$nomSalle=$querySalle->fetch()['nom_salle'];
		
		recupStructSalle($idSalle);
		numeroPlace();
		
		$pdf= new Cezpdf('a4','portrait');
		$pdf->selectFont('../ezpdf/fonts/Helvetica.afm');

		$cols[0]='       Signature       ';		
		$cols[1]=mb_convert_encoding('Nom', 'ISO-8859-1', 'UTF-8');
		$cols[2]=mb_convert_encoding('Prénom', 'ISO-8859-1', 'UTF-8');
		$cols[3]='Place';
		$cols[4]='Promotion';
		$cols[5]='Groupe';
		// $cols[4]='Salle';
		
		// ---- original de W -----
		// $cols[0]=mb_convert_encoding('Nom');
		// $cols[1]=mb_convert_encoding('Prénom');
		// $cols[2]='Promotion';
		// $cols[3]='Groupe';
		// $cols[4]='Salle';
		// $cols[5]='Place';
		// $cols[6]='Signature';
		
	 	recupListeSalle($idDevoir, $idSalle);
	 	
	 	for($i=0; $i<$_SESSION['cpt']; $i++)
	 	{
		    $data[$i][0]="";
		    $data[$i][1]=$_SESSION['data'][$i][0];
		    $data[$i][2]=$_SESSION['data'][$i][1];
		    $data[$i][3]=$_SESSION['data'][$i][5];
		    $data[$i][4]=$_SESSION['data'][$i][2];
		    $data[$i][5]=$_SESSION['data'][$i][3];
	 	}
		
		// ############## TITRE - FEUILLE D'EMARGEMENT PAR SALLE ###########################
		
		// ###################### RECUP MATIERE #################
		
		$nbMat=0;
		$mat=array(array());
		
		for($i=0; $i<$_SESSION['nbCombi']; $i++)
		{
			if($_SESSION['infoCombi'][$i][2]==$idSalle)
			{
				$query1=$pdo->prepare('SELECT nom_mat, nom_promo 
					FROM matiere, promotion 
					WHERE matiere.id_promo = promotion.id_promo 
					AND id_mat=:id_mat');
				$query1->execute(['id_mat'=>$_SESSION['infoCombi'][$i][3]]);
				$res=$query1->fetch(PDO::FETCH_ASSOC);
				$mat[$nbMat][0]=mb_convert_encoding($res['nom_mat'], 'ISO-8859-1', 'UTF-8');
				$mat[$nbMat][1]=mb_convert_encoding($res['nom_promo'], 'ISO-8859-1', 'UTF-8');

				$nbMat++;
			}
		}
		
		// ########################## TITRE #####################
		$title='FEUILLE D\'EMARGEMENT '.$nomSalle;

		$titleMat='';
		
		for($i=0; $i<$nbMat; $i++)
		{
			if($i==0)
				$delim='';
			else
				$delim=' - ';
				
			$titleMat=$titleMat.$delim.$mat[$i][0]." (".$mat[$i][1].')';
		}
		
		$conf=array(
				'justification' => 'center',
				);
				
		$confLeft=array(
				'justification' => 'left',
				);

		$pdf->ezText('Surveillant :',10,  $confLeft);
		$pdf->ezText('Nombre d\'absents :',10,  $confLeft);
		$pdf->ezText('Absents :',10,  $confLeft);
		$pdf->ezText(' ',20,  $confLeft);

		// Nom salle
		$pdf->ezText(mb_convert_encoding($title, 'ISO-8859-1', 'UTF-8'), 14, $conf);
		
		// Matieres
		$pdf->ezText($titleMat, 10, $conf);
		
		// Date format europeen
		$date=$_SESSION['dateDevoir'];
		
		// Date heure
		$pdf->ezText($date.' - '.$_SESSION['hDevoir']."h".$_SESSION['mDevoir']. mb_convert_encoding(' - Durée: ', 'ISO-8859-1', 'UTF-8').$_SESSION['hDuree']."h".$_SESSION['mDuree'] , 10, $conf);

		
		$options=array(
			// Ligne
			'showLines' => 1,
			
			// Afficher titre (Non)
			'show Headings' => 1,
			
			// Couleurs ligne
			'shaded' => 1,
			'shadeCol' => array(0.95,0.95,0.95),
			'shadeCol2' => array(0.9,0.9,0.9),
			
			// Couleurs texte
			'textCol' => array(0,0,0),
	
			// Equivalent padding
			'rowGap' => 3,
			'colGap' => 10,
			
			// Couleur ligne (transparente//blanche)
			'lineCol' => array(1,1,1),
			
			// Positions
			'xPos' => 'center',
			'xOrientation' => 'center',
			
			// Taille
			'width' => 50,
			'maxWidth' => 300
			);
		$pdf->ezTable($data,$cols,' ',$options);
		$pdf->ezStream();
	
	}
	
	// ######################### LISTE PAR PROMOTION #########################
	function creaPDFPromo($idDevoir, $idPromo)
	{
include('../connexion.php');
		$queryPromo=$pdo->query("SELECT nom_promo, nom_dpt 
					FROM promotion, departement 
					WHERE promotion.id_dpt=departement.id_dpt 
					AND id_promo=$idPromo");
		$nomPromo=$queryPromo[0]['nom_promo'];
		$nomDpt=$queryPromo[0]['nom_dpt'];
		
		$pdf= new Cezpdf('a4','portrait');
		$pdf->selectFont('../ezpdf/fonts/Helvetica.afm');
		
		$cols[0]=mb_convert_encoding('Nom', 'ISO-8859-1', 'UTF-8');
		$cols[1]=mb_convert_encoding('Prénom', 'ISO-8859-1', 'UTF-8');
		$cols[2]='Place';
		$cols[3]='Salle';
		$cols[4]='Groupe';

		// -- l'affichage original de W
		// $cols[2]='Promotion';
		// $cols[3]='Groupe';
		// $cols[4]='Salle';
		// $cols[5]='Place';
		//$cols[6]='Signature';
		
	 	recupListePromo($idDevoir, $idPromo);
	 	
	 	for($i=0; $i<$_SESSION['cpt']; $i++)
	 	{
		    $data[$i][0]=$_SESSION['data'][$i][0];
		    $data[$i][1]=$_SESSION['data'][$i][1];
		    $data[$i][2]=$_SESSION['data'][$i][5];
		    $data[$i][3]=$_SESSION['data'][$i][4];
		    $data[$i][4]=$_SESSION['data'][$i][3];
		    // $data[$i][5]=$_SESSION[data][$i][5];
	 	}	
				
		// ###################### RECUP MATIERE #################
		
		/**
		* Ca plantait la page, je commente et ça passe, je ne vais pas essayer de comprendre
		*/
		// $query2=mysql_query("SELECT COUNT(DISTINCT promotion.id_promo) nbPromo
		// 		FROM matiere, promotion
		// 		WHERE matiere.id_promo = promotion.id_promo
		// 		AND promotion.id_promo = $idPromo
		// 		AND id_mat='".$_SESSION['infoCombi'][$i][3]."'");

		$nbMat=0;
		$mat=array();
		
		for($i=0; $i<$_SESSION['nbCombi']; $i++)
		{
			//if($_SESSION['infoCombi'][$i][2]==$idSalle) -- pas besoin ici apparemment
			//{
				$query1=$pdo->prepare("SELECT nom_mat 
				FROM matiere, promotion
				WHERE matiere.id_promo = promotion.id_promo
				AND promotion.id_promo = $idPromo
				AND id_mat=:id_mat");
				$query1->execute(['id_mat'=>$_SESSION['infoCombi'][$i][3]]);
				$res=$query1->fetch(PDO::FETCH_ASSOC);	
				$mat[$nbMat]=mb_convert_encoding($res['nom_mat'], 'ISO-8859-1', 'UTF-8');
				$nbMat++;
			//}
		}
		
		// ########################## TITRE #####################
		$titlePromo='Liste '.$nomDpt.' '.$nomPromo;

		// On affiche juste la matière de la promo; si par ex. 2 groupes différentes avec 2 matières différentes, on affiche les 2

		$titleMat='';
	
		for($i=0; $i<$nbMat; $i++)
		{
			if($i==0)
				$delim='';
			else if ($query2->fetchColumn() == 1)
				$delim=' - ';
				
			$titleMat=$titleMat.$delim.$mat[$i];
		}

		$conf=array(
				'justification' => 'center',
				);

		// Nom promo
		$pdf->ezText(mb_convert_encoding($titlePromo, 'ISO-8859-1', 'UTF-8'), 14, $conf);

		// Nom matière
		$pdf->ezText($titleMat, 10, $conf);
		
		// Date format europeen
		$date=$_SESSION['dateDevoir'];
		
		// Date heure
		$pdf->ezText($date.' - '.$_SESSION['hDevoir']."h".$_SESSION['mDevoir']. mb_convert_encoding(' - Durée: ', 'ISO-8859-1', 'UTF-8').$_SESSION['hDuree']."h".$_SESSION['mDuree'] , 10, $conf);

		$options=array(
			// Ligne
			'showLines' => 1,
			
			// Afficher titre (Non)
			'show Headings' => 1,
			
			// Couleurs ligne
			'shaded' => 1,
			'shadeCol' => array(0.95,0.95,0.95),
			'shadeCol2' => array(0.9,0.9,0.9),
			
			// Couleurs texte
			'textCol' => array(0,0,0),
	
			// Equivalent padding
			'rowGap' => 1,
			'colGap' => 10,
			
			// Couleur ligne (transparente//blanche)
			'lineCol' => array(1,1,1),
			
			// Positions
			'xPos' => 'center',
			'xOrientation' => 'center',
			
			// Taille
			'width' => 50,
			'maxWidth' => 300
			);
			
		$pdf->ezTable($data,$cols,' ',$options);
		$pdf->ezStream();
	}

// ########################################################################################
// 		PGME PRINCIPAL									  #
// ########################################################################################
//header('Content-Disposition: attachment;; filename="file.pdf"');
	
	// CHOIX LISTE PDF
	switch($_GET['varD'])
	{
		case '1' : creaPDFSalle($idDevoir, $idSalle); break;
		case '2' : creaPDFEmarge($idDevoir, $idSalle); break;
		case '3' : creaPDFPromo($idDevoir, $idPromo); break;
		default: break;
	}


	
