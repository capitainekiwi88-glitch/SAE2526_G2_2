<?php
session_start();
ob_start();
require_once __DIR__ . '/../vendor/autoload.php';
require('../lib/fpdf186/fpdf.php');

function convertToLatin1($str) {
    return mb_convert_encoding($str, 'ISO-8859-1', 'UTF-8');
}

$idDevoir = isset($_GET['devoir']) ? (int)$_GET['devoir'] : 0;
$varD = isset($_GET['varD']) ? $_GET['varD'] : '1';

if ($idDevoir <= 0) {
    die('Paramètre devoir manquant.');
}

$pdo = \App\Modele\DAO\Connexion::getInstance();

$stmt = $pdo->prepare("SELECT * FROM devoir WHERE id_devoir = :id");
$stmt->execute([':id' => $idDevoir]);
$devoir = $stmt->fetch();
if (!$devoir) {
    die('Devoir introuvable.');
}

$dateStr = date('d/m/Y', strtotime($devoir['date_devoir']));
$heureStr = str_replace(':', 'h', substr($devoir['heure_devoir'], 0, 5));
$dureeStr = str_replace(':', 'h', substr($devoir['duree_devoir'], 0, 5));

if ($varD === '1' || $varD === '2') {
    $roomId = isset($_GET['room']) ? (int)$_GET['room'] : 0;
    if ($roomId <= 0) {
        die('Paramètre room manquant.');
    }

    $stmt = $pdo->prepare("SELECT nom_salle FROM salle WHERE id_salle = :id");
    $stmt->execute([':id' => $roomId]);
    $salle = $stmt->fetch();
    if (!$salle) {
        die('Salle introuvable.');
    }

    $stmt = $pdo->prepare(
        "SELECT e.nom_etudiant, e.prenom_etudiant, e.id_groupe, g.nom_groupe,
                p.place_x, p.place_y,
                CONCAT(d.nom_dpt, ' ', pr.nom_promo, ' ', pr.annee) AS promo_label
         FROM placement p
         JOIN etudiant e ON e.id_etudiant = p.id_etudiant
         JOIN groupe g ON g.id_groupe = e.id_groupe
         JOIN promotion pr ON pr.id_promo = g.id_promo
         JOIN departement d ON d.id_dpt = pr.id_dpt
         WHERE p.id_devoir = :devoir AND p.id_salle = :salle
         ORDER BY e.nom_etudiant, e.prenom_etudiant"
    );
    $stmt->execute([':devoir' => $idDevoir, ':salle' => $roomId]);
    $rows = $stmt->fetchAll();

    $stmt2 = $pdo->prepare(
        "SELECT s.capacite, pl.donnee
         FROM salle s
         LEFT JOIN plan pl ON pl.id_plan = s.id_plan
         WHERE s.id_salle = :id"
    );
    $stmt2->execute([':id' => $roomId]);
    $salleInfo = $stmt2->fetch();

    $placeLabelMap = [];
    if (!empty($salleInfo['donnee'])) {
        $planRows = explode('-', $salleInfo['donnee']);
        $nbRangs = count($planRows);
        $rang = 0;
        for ($i = 0; $i < $nbRangs; $i++) {
            $rang++;
            $col = 0;
            $cells = str_split($planRows[$i]);
            for ($j = 0; $j < count($cells); $j++) {
                if ($cells[$j] === '1' || $cells[$j] === '2') {
                    $col++;
                    $placeLabelMap[$nbRangs -$i . '-' . $j] = $nbRangs - $rang  . '-' . $col;
                }
            }
        }
    }

    $dataTest = [];
    $promoLabels = [];
    foreach ($rows as $row) {
        $placeLabel = $placeLabelMap[$nbRangs -$row['place_x'] . '-' . $row['place_y']] ?? ($nbRangs -$row['place_x'] . '-' . $row['place_y']);
        $dataTest[] = [
            $row['nom_etudiant'],
            $row['prenom_etudiant'],
            $row['promo_label'],
            $row['nom_groupe'],
            $salle['nom_salle'],
            $placeLabel,
        ];
        $promoLabels[] = $row['promo_label'];
    }

    $promoStr = implode(' / ', array_unique($promoLabels));

    $devoirTest = [
        'nomSalle' => $salle['nom_salle'],
        'matiere' => $devoir['nom_devoir'],
        'promotion' => $promoStr,
        'departement' => '',
        'date' => $dateStr,
        'heure' => $heureStr,
        'duree' => $dureeStr,
    ];

} elseif ($varD === '3') {
    $promoId = isset($_GET['promo']) ? (int)$_GET['promo'] : 0;
    if ($promoId <= 0) {
        die('Paramètre promo manquant.');
    }

    $stmt = $pdo->prepare(
        "SELECT CONCAT(d.nom_dpt, ' ', pr.nom_promo, ' ', pr.annee) AS label
         FROM promotion pr
         JOIN departement d ON d.id_dpt = pr.id_dpt
         WHERE pr.id_promo = :id"
    );
    $stmt->execute([':id' => $promoId]);
    $promoRow = $stmt->fetch();
    $promoLabel = $promoRow ? $promoRow['label'] : 'Promotion';

    $stmt = $pdo->prepare(
        "SELECT e.nom_etudiant, e.prenom_etudiant, e.id_groupe, g.nom_groupe,
                p.place_x, p.place_y, p.id_salle, s.nom_salle, s.id_plan,
                CONCAT(d.nom_dpt, ' ', pr.nom_promo, ' ', pr.annee) AS promo_label
         FROM placement p
         JOIN etudiant e ON e.id_etudiant = p.id_etudiant
         JOIN groupe g ON g.id_groupe = e.id_groupe
         JOIN promotion pr ON pr.id_promo = g.id_promo
         JOIN departement d ON d.id_dpt = pr.id_dpt
         JOIN salle s ON s.id_salle = p.id_salle
         WHERE p.id_devoir = :devoir AND g.id_promo = :promo
         ORDER BY e.nom_etudiant, e.prenom_etudiant"
    );
    $stmt->execute([':devoir' => $idDevoir, ':promo' => $promoId]);
    $rows = $stmt->fetchAll();

    $salleIds = array_unique(array_column($rows, 'id_salle'));
    $placeLabelMaps = [];
    foreach ($salleIds as $sid) {
        $stmt2 = $pdo->prepare(
            "SELECT pl.donnee FROM salle s LEFT JOIN plan pl ON pl.id_plan = s.id_plan WHERE s.id_salle = :id"
        );
        $stmt2->execute([':id' => $sid]);
        $info = $stmt2->fetch();
        if (!empty($info['donnee'])) {
            $planRows = explode('-', $info['donnee']);
            $nbRangs = count($planRows);
            $rang = 0;
            for ($i = 0; $i < $nbRangs; $i++) {
                $rang++;
                $col = 0;
                $cells = str_split($planRows[$i]);
                for ($j = 0; $j < count($cells); $j++) {
                    if ($cells[$j] === '1' || $cells[$j] === '2') {
                        $col++;
                        $placeLabelMaps[$sid][$nbRangs - $i . '-' . $j] = $nbRangs - $rang . '-' . $col;
                    }
                }
            }
        }
    }

    $dataTest = [];
    foreach ($rows as $row) {
        $sid = $row['id_salle'];
        $placeLabel = $placeLabelMaps[$sid][$nbRangs - $row['place_x'] . '-' . $row['place_y']] ?? ($nbRangs - $row['place_x'] . '-' . $row['place_y']);
        $dataTest[] = [
            $row['nom_etudiant'],
            $row['prenom_etudiant'],
            $promoLabel,
            $row['nom_groupe'],
            $row['nom_salle'],
            $placeLabel,
        ];
    }

    $devoirTest = [
        'nomSalle' => '',
        'matiere' => $devoir['nom_devoir'],
        'promotion' => $promoLabel,
        'departement' => '',
        'date' => $dateStr,
        'heure' => $heureStr,
        'duree' => $dureeStr,
    ];
} else {
    die('Type de PDF invalide.');
}

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 12);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    function FancyTable($header, $data, $devoirTest, $type)
    {
        $this->SetFillColor(200, 220, 255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B');
        
        if ($type === 'salle' || $type === 'promo') {
            $w = array(40, 40, 20, 45, 45);
        } elseif ($type === 'emarge') {
            $w = array(30, 35, 35, 20, 35, 35); 
        }
        
        for ($i = 0; $i < count($header); $i++)
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
        $this->Ln();
        
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
        
        $fill = false;
        foreach ($data as $row) {
            foreach ($row as $key => $cell) {
                $this->Cell($w[$key], 6, $cell, 'LR', 0, 'L', $fill);
            }
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}

function creaPDFSalle($dataTest, $devoirTest)
{
    ob_end_clean();
    
    $pdf = new PDF();
    $pdf->AddPage();
    
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, convertToLatin1('Liste ' . $devoirTest['nomSalle']), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 10, convertToLatin1($devoirTest['matiere'] . ' (' . $devoirTest['promotion'] . ')'), 0, 1, 'C');
    $pdf->Cell(0, 10, $devoirTest['date'] . ' - ' . $devoirTest['heure'] . ' - ' . convertToLatin1('Durée: ') . $devoirTest['duree'], 0, 1, 'C');

    $header = array('Nom', convertToLatin1('Prénom'), 'Place', 'Promotion', 'Groupe');
    $data = array();
    foreach ($dataTest as $row) {
        $data[] = array(
            convertToLatin1($row[0]),
            convertToLatin1($row[1]),
            $row[5],
            convertToLatin1($row[2]),
            convertToLatin1($row[3])
        );
    }

    $pdf->FancyTable($header, $data, $devoirTest, 'salle');
    $pdf->Output('I', 'liste_salle.pdf');
}

function creaPDFEmarge($dataTest, $devoirTest)
{
    ob_end_clean();
    
    $pdf = new PDF();
    $pdf->AddPage();
    
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 10, 'Surveillant :', 0, 1, 'L');
    $pdf->Cell(0, 10, 'Nombre d\'absents :', 0, 1, 'L');
    $pdf->Cell(0, 10, 'Absents :', 0, 1, 'L');
    $pdf->Ln(10);
    
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, convertToLatin1('FEUILLE D\'EMARGEMENT ' . $devoirTest['nomSalle']), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 10, convertToLatin1($devoirTest['matiere'] . ' (' . $devoirTest['promotion'] . ')'), 0, 1, 'C');
    $pdf->Cell(0, 10, $devoirTest['date'] . ' - ' . $devoirTest['heure'] . ' - ' . convertToLatin1('Durée: ') . $devoirTest['duree'], 0, 1, 'C');

    $header = array('       Signature       ', 'Nom', convertToLatin1('Prénom'), 'Place', 'Promotion', 'Groupe');
    $data = array();
    foreach ($dataTest as $row) {
        $data[] = array(
            '',
            convertToLatin1($row[0]),
            convertToLatin1($row[1]),
            $row[5],
            convertToLatin1($row[2]),
            convertToLatin1($row[3])
        );
    }
    
    $pdf->FancyTable($header, $data, $devoirTest, 'emarge');
    $pdf->Output('I', 'feuille_emargement.pdf');
}

function creaPDFPromo($dataTest, $devoirTest)
{
    ob_end_clean(); 
    
    $pdf = new PDF();
    $pdf->AddPage();
    
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, convertToLatin1('Liste ' . $devoirTest['promotion']), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 10, convertToLatin1($devoirTest['matiere']), 0, 1, 'C');
    $pdf->Cell(0, 10, $devoirTest['date'] . ' - ' . $devoirTest['heure'] . ' - ' . convertToLatin1('Durée: ') . $devoirTest['duree'], 0, 1, 'C');

    $header = array('Nom', convertToLatin1('Prénom'), 'Place', 'Salle', 'Groupe');
    $data = array();
    foreach ($dataTest as $row) {
        $data[] = array(
            convertToLatin1($row[0]),
            convertToLatin1($row[1]),
            $row[5],
            convertToLatin1($row[4]),
            convertToLatin1($row[3])
        );
    }
    
    $pdf->FancyTable($header, $data, $devoirTest, 'promo');
    $pdf->Output('I', 'liste_promotion.pdf');
}

switch ($varD) {
    case '1':
        creaPDFSalle($dataTest, $devoirTest);
        break;
    case '2':
        creaPDFEmarge($dataTest, $devoirTest);
        break;
    case '3':
        creaPDFPromo($dataTest, $devoirTest);
        break;
    default:
        creaPDFSalle($dataTest, $devoirTest);
        break;
}
?>
