<?php
	session_start();
	ob_end_clean();
	require('fpdf186/fpdf.php');
	include('connexion.php');
	include('x_fctPdfSalle.php');	

	class PDF extends FPDF
	{
		function FancyTable($idSalle, $idDevoir, $orientation, $data)
		{
			// Caracteristiques tableau et entete
			$this->SetFillColor(128,128,128); // Couleur fond entete
			$this->SetTextColor(255); // Couleur titre entete
			$this->SetDrawColor(255); // Couleur bordure tableau
			$this->SetLineWidth(.5); // Epaisseur bordure tableau
			$this->SetFont('','B'); // Style texte entete
			
			// Restauration caracteristiques pour le corps du tableau
			$this->SetFillColor(229,229,229); // Couleur cellule interligne
			$this->SetTextColor(0); // Couleur texte tableau
			$this->SetFont('', '', 8); // Style texte
			
			// Largeur colonne
			$w=returnSizeCol($orientation);
			
			// Hauteur cellule
			$h=9;
			
			$nbCar=$w/2;

			for($i=0; $i<$_SESSION['rangSalle']; $i++)
			{
				for($j=0; $j<$_SESSION['colSalle']; $j++)
				{
					$color=returnValCol($data[$i][$j]);
					$this->SetFillColor($color[0], $color[1], $color[2]);
					
					$name=array();
					$name=returnNom($i,$j,$idDevoir, $idSalle);
					
					$x = $this->GetX();
					$y = $this->GetY(); 
					
					if($name){
						$point='. ';
					}else{
						$point='';
					}
						
						if (isset($_SESSION['noPlace'][$i][$j])){
							$this->MultiCell($w, $h/2,
												 mb_convert_encoding(substr($name[0],0,$nbCar), 'ISO-8859-1', 'UTF-8')."\n".mb_convert_encoding(substr($name[1],0,1), 'ISO-8859-1', 'UTF-8').$point.$_SESSION['noPlace'][$i][$j],
												  1, 'L', 1); 
						}else{
							//Emplacement vide ("couloir")
							$this->MultiCell($w, $h/2, "", 1); 
						}
						
					$this->SetXY(($x+$w),($y));
				}
				// Retour a la ligne
				$this->Ln($h);
			}
	
			$this->Cell($w,0,'','T');
		}
		
		
		function titlePDF($orientation, $idSalle)
		{
			include('connexion.php');
			if($orientation=='L')
			{
				$long=275;
			}
			else
			{
				$long=190;
			}
			
			$querySalle = $pdo->prepare("SELECT nom_salle FROM salle WHERE id_salle = :idSalle");
			$querySalle->execute(['idSalle' => $idSalle]);
			$nomSalle = $querySalle->fetchColumn();

			// ***** recup des données pour affichage dans le titre *****

			$idDevoir=$_GET['idDevoir'];
			$idSalle=$_GET['idSalle'];
//			$idPromo=$_GET['idPromo'];	

			// Date format europeen
			$date=$_SESSION['dateDevoir'];

			// ###################### RECUP MATIERE #################
			$nbMat=0;
			$mat=array();
	
			$nbMat=0;
			$mat=array(array());

			// *********  on récupère aussi la promo correspondante - pour qd il y en a 2  
			// *********  et on l'affichera derrière la matière entre ()  

			for($i=0; $i<$_SESSION['nbCombi']; $i++)
			{
				if($_SESSION['infoCombi'][$i][2]==$idSalle)
				{
					$query1 = $pdo->prepare('SELECT nom_mat, nom_promo 
								FROM matiere, promotion 
								WHERE promotion.id_promo = matiere.id_promo 
								AND id_mat = :idMat');
					$query1->execute(['idMat' => $_SESSION['infoCombi'][$i][3]]);
					$result = $query1->fetch(PDO::FETCH_ASSOC);
					$mat[$nbMat][0] = mb_convert_encoding($result['nom_mat'], 'ISO-8859-1', 'UTF-8');
					$mat[$nbMat][1] = mb_convert_encoding($result['nom_promo'], 'ISO-8859-1', 'UTF-8');
					$nbMat++;
						
				}
			}
	
		
			$titleMat='';
		
			for($i=0; $i<$nbMat; $i++)
			{
				if($i==0)
					$delim='';
				else
					$delim=' - ';
				
				$titleMat=$titleMat.$delim.$mat[$i][0]." (".$mat[$i][1].")";
			}

			
			//								 - Bordure
			$this->Cell($long, 5, $nomSalle, 'LRT', 0, 'C');
			$this->Ln();
			$this->Cell($long, 5, $date." - ".$_SESSION['hDevoir']."h".$_SESSION['mDevoir'].mb_convert_encoding(' - Durée: ', 'ISO-8859-1', 'UTF-8').$_SESSION['hDuree']."h".$_SESSION['mDuree']." - ".$titleMat, 'LRB', 0, 'C');
		}
	}


	function LoadData($file)
	{
	    $lines=file($file);
	    $data=array();
	    foreach($lines as $line)
	    	$data[]=explode(';',chop($line));
	    return $data;
	}

// #############################################################################################
// 				Recuperation de donnees					   #
// #############################################################################################

	// RAZ Variable
	unset($idDevoir);
	unset($idSalle);
	unset($_SESSION['structSalle']);
	unset($_SESSION['noPlace']);
	
	// Recupere structure salle
	$idDevoir=$_GET['idDevoir'];
	$idSalle=$_GET['idSalle'];
	recupStructSalle($idSalle);
	
	// Recup info devoir salle
	
	
	// Recupere numero place en fonction de la salle
	numeroPlace();

// #############################################################################################
// 			Partie PDF							   #
// #############################################################################################

	// Orientation page - L=paysage / P=portrait
	$orientation=returnOrientation();
	
	// Creation du PDF
	$pdf=new PDF($orientation, 'mm', 'A4');
	
	$pdf->SetFont('Courier','',12);
	$pdf->AddPage();
	
	// Titre
	$pdf->titlePDF($orientation, $idSalle);
	
	$pdf->Ln(20);
	
	// Structure
	$pdf->FancyTable($idSalle, $idDevoir, $orientation, $_SESSION['structSalle']);
	
	// Export
	$pdf->Output();
	
?>