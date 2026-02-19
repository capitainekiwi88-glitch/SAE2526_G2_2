<?php

  ob_end_clean();
  require('fpdf16/fpdf.php');
  include('connexion.php');

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
	$w=array(64,64,62);
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
	    $this->Cell($w[2],6,$row[2],'LR',0,'L',$fill);;
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


  $query1 = $pdo->query('SELECT * FROM enseignant WHERE admin = 0 ORDER BY nom_ens');

  $cpt = 0;

  while ($result = $query1->fetch(PDO::FETCH_ASSOC))
  {
    $data[$cpt][0]=utf8_decode($result['nom_ens']);
    $data[$cpt][1]=utf8_decode($result['prenom_ens']);
    $data[$cpt][2]=utf8_decode($result['login']);
  
    $cpt++;
  }

  $pdf=new PDF();

  $header=array('Nom',utf8_decode('Prénom'),'Login');
  $pdf->SetFont('Times','',12);
  $pdf->AddPage();
  $pdf->FancyTable($header,$data);
  $pdf->Output();
?>