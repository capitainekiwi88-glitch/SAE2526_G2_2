<?php
	session_start();
	ob_end_clean();
	require('../fpdf16/fpdf.php');
	include('../connexion.php');
	include('fct_pdf.php');
	
	$idDevoir=$_GET['idDevoir'];
	$idSalle=$_GET['idSalle'];
	$idPromo=$_GET['idPromo'];

// ########################################################################################
// 										EXPORT LISTE PDF								  #
// ########################################################################################

	class PDF extends FPDF
	{
		function FancyTable($header,$data)
		{
			//Couleurs, épaisseur du trait et police grasse
			$this->SetFillColor(128,128,128); // Entete tableau
			$this->SetTextColor(255); // Couleur titre
			$this->SetDrawColor(0); // Bordure
			$this->SetLineWidth(.3);
			$this->SetFont('','B');
			
			//En-tête
			$w=array(30,30,30,30,30);
			for($i=0;$i<count($header);$i++)
				$this->Cell($w[$i],7,$header[$i],1,0,'C',1);
			$this->Ln();
			
			//Restauration des couleurs et de la police
			$this->SetFillColor(229,229,229); // Couleur interligne
			$this->SetTextColor(0);
			$this->SetFont('');
			
			//Données
			$fill=false;
			
			foreach($data as $row)
			{
				$this->Cell($w[0],6,$row[0],'LR',0,'L',$fill);
				$this->Cell($w[1],6,$row[1],'LR',0,'L',$fill);
				$this->Cell($w[2],6,$row[2],'LR',0,'L',$fill);
				$this->Cell($w[3],6,$row[3],'LR',0,'L',$fill);
				$this->Cell($w[4],6,$row[5],'LR',0,'L',$fill);
				$this->Ln();
				$fill=!$fill;
			}
			
			$this->Cell(array_sum($w),0,'','T');
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


	recupStructSalle($idSalle);
	numeroPlace();
	
	for($i=0; $i<$_SESSION[cpt]; $i++)
 	{
 		$data[$i][0]=$_SESSION['data'][$i][0];
	    $data[$i][1]=$_SESSION['data'][$i][1];
	    $data[$i][2]=$_SESSION['data'][$i][2];
	    $data[$i][3]=$_SESSION['data'][$i][3];
	    $data[$i][4]=$_SESSION['data'][$i][4];
	    $data[$i][5]=$_SESSION['data'][$i][5];
 	}

	$pdf=new PDF();
	
	$header=array('Nom', utf8_decode('Prénom'), 'Promotion', 'Groupe', 'Place');
	$pdf->SetFont('Times','',12);
	$pdf->AddPage();
	$pdf->FancyTable($header,$data);
	$pdf->Output();
	
?>