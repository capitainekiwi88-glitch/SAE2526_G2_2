<?php
ob_end_clean();
require_once __DIR__ . '/../vendor/autoload.php';
require('../lib/fpdf186/fpdf.php');

function toLatin1($str) {
    return mb_convert_encoding($str, 'ISO-8859-1', 'UTF-8');
}

$idDevoir = isset($_GET['devoir']) ? (int)$_GET['devoir'] : 0;
$roomId = isset($_GET['room']) ? (int)$_GET['room'] : 0;

if ($idDevoir <= 0 || $roomId <= 0) {
    die('Paramètres devoir et room requis.');
}

$pdo = \App\Modele\DAO\Connexion::getInstance();

$stmt = $pdo->prepare("SELECT * FROM devoir WHERE id_devoir = :id");
$stmt->execute([':id' => $idDevoir]);
$devoir = $stmt->fetch();
if (!$devoir) {
    die('Devoir introuvable.');
}

$stmt = $pdo->prepare(
    "SELECT s.nom_salle, s.capacite, pl.donnee
     FROM salle s
     LEFT JOIN plan pl ON pl.id_plan = s.id_plan
     WHERE s.id_salle = :id"
);
$stmt->execute([':id' => $roomId]);
$salleInfo = $stmt->fetch();
if (!$salleInfo) {
    die('Salle introuvable.');
}

$stmt = $pdo->prepare(
    "SELECT DISTINCT CONCAT(d.nom_dpt, ' ', pr.nom_promo, ' ', pr.annee) AS label
     FROM placement p
     JOIN etudiant e ON e.id_etudiant = p.id_etudiant
     JOIN groupe g ON g.id_groupe = e.id_groupe
     JOIN promotion pr ON pr.id_promo = g.id_promo
     JOIN departement d ON d.id_dpt = pr.id_dpt
     WHERE p.id_devoir = :devoir AND p.id_salle = :salle"
);
$stmt->execute([':devoir' => $idDevoir, ':salle' => $roomId]);
$promoLabels = array_column($stmt->fetchAll(), 'label');
$promoStr = implode(' / ', $promoLabels);

$dateStr = date('d/m/Y', strtotime($devoir['date_devoir']));
$heureStr = str_replace(':', 'h', substr($devoir['heure_devoir'], 0, 5));
$dureeStr = str_replace(':', 'h', substr($devoir['duree_devoir'], 0, 5));

$devoirTest = [
    'nomSalle' => $salleInfo['nom_salle'],
    'matiere' => $devoir['nom_devoir'],
    'promotion' => $promoStr,
    'date' => $dateStr,
    'heure' => $heureStr,
    'duree' => $dureeStr,
];

$planData = $salleInfo['donnee'] ?? '';
if (empty($planData)) {
    die('Aucun plan défini pour cette salle.');
}

$planRows = explode('-', $planData);
$nbRangs = count($planRows);
$nbCols = strlen($planRows[0]);

$structSalle = [];
for ($i = 0; $i < $nbRangs; $i++) {
    $cells = str_split($planRows[$i]);
    $structSalle[$i] = [];
    for ($j = 0; $j < $nbCols; $j++) {
        $structSalle[$i][$j] = $cells[$j] ?? '0';
    }
}

$noPlace = [];
$rang = 0;
for ($i = $nbRangs - 1; $i >= 0; $i--) {
    $rang++;
    $col = 0;
    for ($j = 0; $j < $nbCols; $j++) {
        if ($structSalle[$i][$j] === '1' || $structSalle[$i][$j] === '2') {
            $col++;
            $noPlace[$i][$j] = $rang . '-' . $col;
        }
    }
}

$stmt = $pdo->prepare(
    "SELECT e.nom_etudiant, e.prenom_etudiant, p.place_x, p.place_y
     FROM placement p
     JOIN etudiant e ON e.id_etudiant = p.id_etudiant
     WHERE p.id_devoir = :devoir AND p.id_salle = :salle"
);
$stmt->execute([':devoir' => $idDevoir, ':salle' => $roomId]);
$placements = $stmt->fetchAll();

$placementTest = [];
foreach ($placements as $p) {
    $placementTest[(int)$p['place_x']][(int)$p['place_y']] = [
        'nom' => $p['nom_etudiant'],
        'prenom' => $p['prenom_etudiant'],
    ];
}


function returnNomTest($varX, $varY, $placementTest) {
    if (isset($placementTest[$varX][$varY])) {
        return array($placementTest[$varX][$varY]['nom'], $placementTest[$varX][$varY]['prenom']);
    }
    return null;
}

function returnValCol($val) {
    $valCol = array();
    switch ($val) {
        case '0': $valCol = array(255, 255, 255); break;
        case '1': $valCol = array(220, 220, 220); break;
        case '2': $valCol = array(44, 115, 196); break;
        case '3': $valCol = array(255, 255, 255); break;
        default: $valCol = array(255, 255, 255); break;
    }
    return $valCol;
}

function returnOrientation($nbRangs, $nbCols) {
    if ($nbRangs * 9 < $nbCols * 14) {
        return 'L';
    } else {
        return 'P';
    }
}

function returnSizeCol($orientation, $nbCols) {
    if ($orientation == 'L') {
        $sizeBloc = 275;
    } else {
        $sizeBloc = 190;
    }
    return $sizeBloc / $nbCols;
}

class PDF extends FPDF
{
    function FancyTable($placementTest, $orientation, $data, $noPlace, $nbRangs, $nbCols) {
        $this->SetFillColor(128, 128, 128);
        $this->SetTextColor(255);
        $this->SetDrawColor(255);
        $this->SetLineWidth(.5);
        $this->SetFont('', 'B');

        $this->SetFillColor(229, 229, 229);
        $this->SetTextColor(0);
        $this->SetFont('', '', 8);

        $w = returnSizeCol($orientation, $nbCols);
        $h = 9;
        $nbCar = (int)($w / 2);

        for ($i = 0; $i < $nbRangs; $i++) {
            for ($j = 0; $j < $nbCols; $j++) {
                $color = returnValCol($data[$i][$j]);
                $this->SetFillColor($color[0], $color[1], $color[2]);

                $name = returnNomTest($i, $j, $placementTest);

                $x = $this->GetX();
                $y = $this->GetY();

                if ($name) {
                    $point = '. ';
                } else {
                    $point = '';
                }

                if (isset($noPlace[$i][$j])) {
                    $this->MultiCell(
                        $w,
                        $h / 2,
                        toLatin1(substr($name[0] ?? '', 0, $nbCar)) . "\n" . toLatin1(substr($name[1] ?? '', 0, 1)) . $point . $noPlace[$i][$j],
                        1,
                        'L',
                        1
                    );
                } else {
                    $this->MultiCell($w, $h / 2, "", 1);
                }

                $this->SetXY(($x + $w), ($y));
            }
            $this->Ln($h);
        }

        $this->Cell($w, 0, '', 'T');
    }

    function titlePDF($orientation, $devoirTest) {
        if ($orientation == 'L') {
            $long = 275;
        } else {
            $long = 190;
        }

        $titleMat = toLatin1($devoirTest['matiere'] . ' (' . $devoirTest['promotion'] . ')');

        $this->Cell($long, 5, $devoirTest['nomSalle'], 'LRT', 0, 'C');
        $this->Ln();
        $this->Cell($long, 5, $devoirTest['date'] . " - " . $devoirTest['heure'] . toLatin1(' - Duree: ') . $devoirTest['duree'] . " - " . $titleMat, 'LRB', 0, 'C');
    }

    function drawBureau($orientation) {
        if ($orientation == 'L') {
            $long = 275;
        } else {
            $long = 190;
        }

        $this->Ln(5);
        $this->SetFillColor(170, 170, 170);
        $this->SetTextColor(255);
        $this->SetFont('', 'B', 10);

        $bureauWidth = 60;
        $x = ($long - $bureauWidth) / 2 + 10;
        $this->SetX($x);
        $this->Cell($bureauWidth, 8, 'BUREAU', 1, 0, 'C', 1);
    }
}

$orientation = returnOrientation($nbRangs, $nbCols);

$pdf = new PDF($orientation, 'mm', 'A4');
$pdf->SetFont('Courier', '', 12);
$pdf->AddPage();

$pdf->titlePDF($orientation, $devoirTest);
$pdf->Ln(20);

$pdf->FancyTable($placementTest, $orientation, $structSalle, $noPlace, $nbRangs, $nbCols);

$pdf->drawBureau($orientation);

$pdf->Output();
?>
