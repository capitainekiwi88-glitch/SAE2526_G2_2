<?php
session_start();
ob_end_clean();
require_once __DIR__ . '/../vendor/autoload.php';
require('../lib/fpdf186/fpdf.php');

function convertToLatin1($str) {
    return mb_convert_encoding($str, 'ISO-8859-1', 'UTF-8');
}

// --- Lecture des enseignants depuis la base de données ---
$pdo = \App\Modele\DAO\Connexion::getInstance();
$enseignantDao = new \App\Modele\DAO\EnseignantDAO($pdo);
$enseignants = $enseignantDao->findAll();

$data = [];
foreach ($enseignants as $ens) {
    $data[] = [
        convertToLatin1($ens->getNom()),
        convertToLatin1($ens->getPrenom()),
        convertToLatin1($ens->getLogin()),
    ];
}

class PDF extends FPDF
{
    function FancyTable($header, $data)
    {
        $this->SetFillColor(128, 128, 128);
        $this->SetTextColor(255);
        $this->SetDrawColor(0);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B');
        $w = array(64, 64, 62);
        for ($i = 0; $i < count($header); $i++)
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
        $this->Ln();
        $this->SetFillColor(229, 229, 229);
        $this->SetTextColor(0);
        $this->SetFont('');
        $fill = false;
        foreach ($data as $row) {
            $this->Cell($w[0], 6, $row[0], 'LR', 0, 'L', $fill);
            $this->Cell($w[1], 6, $row[1], 'LR', 0, 'L', $fill);
            $this->Cell($w[2], 6, $row[2], 'LR', 0, 'L', $fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}

$pdf = new PDF();
$header = array('Nom', convertToLatin1('Prénom'), 'Login');
$pdf->SetFont('Times', '', 12);
$pdf->AddPage();
$pdf->FancyTable($header, $data);
$pdf->Output();
?>
