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
	function creaPDFSalle($idDevoir, $idSalle, $unFichier)
	{
		$querySalle=mysql_query("SELECT nom_salle FROM salle WHERE id_salle=$idSalle");
		$nomSalle=mysql_result($querySalle, 0);
		
		recupStructSalle($idSalle);
		numeroPlace();
		
		$pdf= new Cezpdf('a4','portrait');
		$pdf->selectFont('../ezpdf/fonts/Helvetica.afm');
		
		$cols[0]=utf8_decode('Nom');
		$cols[1]=utf8_decode('Prénom');
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
			'shaded' => 1,
			'shadeCol' => array(0.8,0.8,0.8),
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
			//'xOrientation' => 'center',
			
			// Taille
			'width' => 90,
			//'maxWidth' => 400
			);
		
		
		
		// ########################################### TITRE ########################################
		// ###################### RECUP MATIERE #################
		
		$nbMat=0;
		$mat=array();
		
		for($i=0; $i<$_SESSION['nbCombi']; $i++)
		{
			if($_SESSION['infoCombi'][$i][2]==$idSalle)
			{
				$query1=mysql_query('SELECT nom_mat FROM matiere WHERE id_mat=\''.$_SESSION['infoCombi'][$i][3].'\'');
				$mat[$nbMat]=utf8_decode(mysql_result($query1, 0, 'nom_mat'));
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
				
			$titleMat=$titleMat.$delim.$mat[$i];
		}
		
		$conf=array(
				'justification' => 'center',
				);
		
		// Nom salle
		$pdf->ezText(utf8_decode($titleSalle), 14, $conf);
		
		// Matieres
		$pdf->ezText($titleMat, 10, $conf);
		
		$date=$_SESSION['dateDevoir'];
		
		// Date heure
		$pdf->ezText($date.' - '.$_SESSION['hDevoir']."h".$_SESSION['mDevoir']. utf8_decode(' - Durée: ').$_SESSION['hDuree']."h".$_SESSION['mDuree'] , 10, $conf);
		
		
		// ########################## TABLEAU ####################
		$pdf->ezTable($data,$cols,' ',$options);
		
		// ########################## EXPORT #####################
		if ($unFichier) {
			$pdf->ezStream();
		} else {
			$pdf->newPage();
			return $pdf;
		}
	}
	
	// ######################### LISTE EMARGEMENT PAR SALLE #########################
	function creaPDFEmarge($idDevoir, $idSalle, $unFichier, $pdf)
	{
		$querySalle=mysql_query("SELECT nom_salle FROM salle WHERE id_salle=$idSalle");
		$nomSalle=mysql_result($querySalle, 0);
		
		recupStructSalle($idSalle);
		numeroPlace();

		if ($pdf==null) {
			$pdf = new Cezpdf('a4', 'portrait');
			$pdf->selectFont('../ezpdf/fonts/Helvetica.afm');
		}
		$cols[0]='       Signature       ';		
		$cols[1]=utf8_decode('Nom');
		$cols[2]=utf8_decode('Prénom');
		$cols[3]='Place';
		$cols[4]='Promotion';
		$cols[5]='Groupe';
		// $cols[4]='Salle';


		
		// ---- original de W -----
		// $cols[0]=utf8_decode('Nom');
		// $cols[1]=utf8_decode('Prénom');
		// $cols[2]='Promotion';
		// $cols[3]='Groupe';
		// $cols[4]='Salle';
		// $cols[5]='Place';
		// $cols[6]='Signature';
		
	 	recupListeSalle($idDevoir, $idSalle);
	 	
	 	for($i=0; $i<$_SESSION[cpt]; $i++)
	 	{
		    $data[$i][0]=$_SESSION['data'][$i][6];
		    $data[$i][1]=$_SESSION['data'][$i][0];
		    $data[$i][2]=$_SESSION['data'][$i][1];
		    $data[$i][3]=$_SESSION['data'][$i][5];
		    $data[$i][4]=$_SESSION['data'][$i][2];
		    $data[$i][5]=$_SESSION['data'][$i][3];
	 	}
		
		// ########################################### TITRE ########################################
		
		// ###################### RECUP MATIERE #################
		
		$nbMat=0;
		$mat=array();
		
		for($i=0; $i<$_SESSION['nbCombi']; $i++)
		{
			if($_SESSION['infoCombi'][$i][2]==$idSalle)
			{
				$query1=mysql_query('SELECT nom_mat FROM matiere WHERE id_mat=\''.$_SESSION['infoCombi'][$i][3].'\'');
				$mat[$nbMat]=utf8_decode(mysql_result($query1, 0, 'nom_mat'));
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
				
			$titleMat=$titleMat.$delim.$mat[$i];
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
		$pdf->ezText(utf8_decode($title), 14, $conf);
		
		// Matieres
		$pdf->ezText($titleMat, 10, $conf);
		
		$date=$_SESSION['dateDevoir'];

		// Date heure
		$pdf->ezText($date.' - '.$_SESSION['hDevoir']."h".$_SESSION['mDevoir']. utf8_decode(' - Durée: ').$_SESSION['hDuree']."h".$_SESSION['mDuree'] , 10, $conf);

		
		$options=array(
			// Ligne
			'showLines' => 1,
			
			// Afficher titre (Non)
			'show Headings' => 1,
			
			// Couleurs ligne
			'shaded' => 1,
			'shadeCol' => array(0.9,0.9,0.9),
			'shadeCol2' => array(0.8,0.8,0.8),
			
			// Couleurs texte
			'textCol' => array(0,0,0),
	
			// Equivalent padding
			'rowGap' => 5,
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

		if ($unFichier) {
			$pdf->ezStream();
		} else {
			$pdf->newPage();
			return $pdf;
		}

	}
	
	// ######################### LISTE PAR PROMOTION #########################
	function creaPDFPromo($idDevoir, $idPromo, $pdf)
	{
		$queryPromo=mysql_query("SELECT nom_promo, nom_dpt FROM promotion, departement WHERE promotion.id_dpt=departement.id_dpt AND id_promo=$idPromo");
		$nomPromo=mysql_result($queryPromo, 0, 'nom_promo');
		$nomDpt=mysql_result($queryPromo, 0, 'nom_dpt');

		if ($pdf==null) {
			$pdf = new Cezpdf('a4', 'portrait');
			$pdf->selectFont('../ezpdf/fonts/Helvetica.afm');
		}

		$cols[0]=utf8_decode('Nom');
		$cols[1]=utf8_decode('Prénom');
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

		// ########################################### TITRE ########################################
		
		// ###################### RECUP MATIERE #################
		
		$nbMat=0;
		$mat=array();
		
		for($i=0; $i<$_SESSION['nbCombi']; $i++)
		{
			if($_SESSION['infoCombi'][$i][2]==$idSalle)
			{
				$query1=mysql_query('SELECT nom_mat FROM matiere WHERE id_mat=\''.$_SESSION['infoCombi'][$i][3].'\'');
				$mat[$nbMat]=utf8_decode(mysql_result($query1, 0, 'nom_mat'));
				$nbMat++;
			}
		}
				
		
		// ########################## TITRE #####################
		$titlePromo='Liste '.$nomDpt.' '.$nomPromo;

		$titleMat='';
		
		for($i=0; $i<$nbMat; $i++)
		{
			if($i==0)
				$delim='';
			else
				$delim=' - ';
				
			$titleMat=$titleMat.$delim.$mat[$i];
		}
		
		$conf=array(
				'justification' => 'center',
				);


		// Nom promo
		$pdf->ezText(utf8_decode($titlePromo), 14, $conf);
		
		// Matieres
		$pdf->ezText($titleMat, 10, $conf);
		
		// Date format europeen
		$date=$_SESSION['dateDevoir'];

		// Date heure
		$pdf->ezText($date.' - '.$_SESSION['hDevoir']."h".$_SESSION['mDevoir']. utf8_decode(' - Durée: ').$_SESSION['hDuree']."h".$_SESSION['mDuree'] , 10, $conf);

		$options=array(
			// Ligne
			'showLines' => 1,
			
			// Afficher titre (Non)
			'show Headings' => 1,
			
			// Couleurs ligne
			'shaded' => 2,
			'shadeCol' => array(0.8,0.8,0.8),
			'shadeCol2' => array(0.9,0.9,0.9),
			
			// Couleurs texte
			'textCol' => array(0,0,0),
	
			// Equivalent padding
			'rowGap' => 5,
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
	
	// CHOIX LISTE PDF
	switch($_GET['varD'])
	{
		case '1' : creaPDFSalle($idDevoir, $idSalle, true); break;
		case '2' : creaPDFEmarge($idDevoir, $idSalle, true, null); break;
		case '3' : creaPDFPromo($idDevoir, $idPromo, null); break;
		case '4' : $pdf = creaPDFSalle($idDevoir, $idSalle, false); $pdf = creaPDFEmarge($idDevoir, $idSalle, false, $pdf); creaPDFPromo($idDevoir, $idPromo, $pdf); break;
		default: break;
	}


	
?>
