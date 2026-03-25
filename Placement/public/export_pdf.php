<?php
session_start();
ob_end_clean();
require_once __DIR__ . '/../vendor/autoload.php';
require('../lib/ezpdf/class.ezpdf.php');

function toLatin1($str) {
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
        for ($i = $nbRangs - 1; $i >= 0; $i--) {
            $rang++;
            $col = 0;
            $cells = str_split($planRows[$i]);
            for ($j = 0; $j < count($cells); $j++) {
                if ($cells[$j] === '1' || $cells[$j] === '2') {
                    $col++;
                    $placeLabelMap[$i . '-' . $j] = $rang . '-' . $col;
                }
            }
        }
    }

    $dataTest = [];
    $promoLabels = [];
    foreach ($rows as $row) {
        $placeLabel = $placeLabelMap[$row['place_x'] . '-' . $row['place_y']] ?? ($row['place_x'] . '-' . $row['place_y']);
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
            for ($i = $nbRangs - 1; $i >= 0; $i--) {
                $rang++;
                $col = 0;
                $cells = str_split($planRows[$i]);
                for ($j = 0; $j < count($cells); $j++) {
                    if ($cells[$j] === '1' || $cells[$j] === '2') {
                        $col++;
                        $placeLabelMaps[$sid][$i . '-' . $j] = $rang . '-' . $col;
                    }
                }
            }
        }
    }

    $dataTest = [];
    foreach ($rows as $row) {
        $sid = $row['id_salle'];
        $placeLabel = $placeLabelMaps[$sid][$row['place_x'] . '-' . $row['place_y']] ?? ($row['place_x'] . '-' . $row['place_y']);
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


function creaPDFSalle($dataTest, $devoirTest)
{
    $pdf = new Cezpdf('a4', 'portrait');
    $pdf->selectFont('../lib/ezpdf/fonts/Helvetica.afm');

    $cols = array();
    $cols[0] = toLatin1('Nom');
    $cols[1] = toLatin1('Prenom');
    $cols[2] = 'Place';
    $cols[3] = 'Promotion';
    $cols[4] = 'Groupe';

    $data = array();
    for ($i = 0; $i < count($dataTest); $i++) {
        $data[$i][0] = toLatin1($dataTest[$i][0]);
        $data[$i][1] = toLatin1($dataTest[$i][1]);
        $data[$i][2] = $dataTest[$i][5];
        $data[$i][3] = $dataTest[$i][2];
        $data[$i][4] = $dataTest[$i][3];
    }

    $options = array(
        'showLines' => 1,
        'show Headings' => 1,
        'shaded' => 1,
        'shadeCol' => array(0.95, 0.95, 0.95),
        'shadeCol2' => array(0.8, 0.8, 0.8),
        'textCol' => array(0, 0, 0),
        'rowGap' => 1,
        'colGap' => 10,
        'lineCol' => array(1, 1, 1),
        'xPos' => 'center',
        'width' => 90,
    );

    $conf = array('justification' => 'center');

    $pdf->ezText(toLatin1('Liste ' . $devoirTest['nomSalle']), 14, $conf);
    $pdf->ezText(toLatin1($devoirTest['matiere'] . ' (' . $devoirTest['promotion'] . ')'), 10, $conf);
    $pdf->ezText($devoirTest['date'] . ' - ' . $devoirTest['heure'] . toLatin1(' - Duree: ') . $devoirTest['duree'], 10, $conf);

    $pdf->ezTable($data, $cols, ' ', $options);
    $pdf->ezStream();
}

function creaPDFEmarge($dataTest, $devoirTest)
{
    $pdf = new Cezpdf('a4', 'portrait');
    $pdf->selectFont('../lib/ezpdf/fonts/Helvetica.afm');

    $cols = array();
    $cols[0] = '       Signature       ';
    $cols[1] = toLatin1('Nom');
    $cols[2] = toLatin1('Prenom');
    $cols[3] = 'Place';
    $cols[4] = 'Promotion';
    $cols[5] = 'Groupe';

    $data = array();
    for ($i = 0; $i < count($dataTest); $i++) {
        $data[$i][0] = '';
        $data[$i][1] = toLatin1($dataTest[$i][0]);
        $data[$i][2] = toLatin1($dataTest[$i][1]);
        $data[$i][3] = $dataTest[$i][5];
        $data[$i][4] = $dataTest[$i][2];
        $data[$i][5] = $dataTest[$i][3];
    }

    $options = array(
        'showLines' => 1,
        'show Headings' => 1,
        'shaded' => 1,
        'shadeCol' => array(0.95, 0.95, 0.95),
        'shadeCol2' => array(0.9, 0.9, 0.9),
        'textCol' => array(0, 0, 0),
        'rowGap' => 3,
        'colGap' => 10,
        'lineCol' => array(1, 1, 1),
        'xPos' => 'center',
        'xOrientation' => 'center',
        'width' => 50,
        'maxWidth' => 300,
    );

    $conf = array('justification' => 'center');
    $confLeft = array('justification' => 'left');

    $pdf->ezText('Surveillant :', 10, $confLeft);
    $pdf->ezText('Nombre d\'absents :', 10, $confLeft);
    $pdf->ezText('Absents :', 10, $confLeft);
    $pdf->ezText(' ', 20, $confLeft);

    $pdf->ezText(toLatin1('FEUILLE D\'EMARGEMENT ' . $devoirTest['nomSalle']), 14, $conf);
    $pdf->ezText(toLatin1($devoirTest['matiere'] . ' (' . $devoirTest['promotion'] . ')'), 10, $conf);
    $pdf->ezText($devoirTest['date'] . ' - ' . $devoirTest['heure'] . toLatin1(' - Duree: ') . $devoirTest['duree'], 10, $conf);

    $pdf->ezTable($data, $cols, ' ', $options);
    $pdf->ezStream();
}

function creaPDFPromo($dataTest, $devoirTest)
{
    $pdf = new Cezpdf('a4', 'portrait');
    $pdf->selectFont('../lib/ezpdf/fonts/Helvetica.afm');

    $cols = array();
    $cols[0] = toLatin1('Nom');
    $cols[1] = toLatin1('Prenom');
    $cols[2] = 'Place';
    $cols[3] = 'Salle';
    $cols[4] = 'Groupe';

    $data = array();
    for ($i = 0; $i < count($dataTest); $i++) {
        $data[$i][0] = toLatin1($dataTest[$i][0]);
        $data[$i][1] = toLatin1($dataTest[$i][1]);
        $data[$i][2] = $dataTest[$i][5];
        $data[$i][3] = $dataTest[$i][4];
        $data[$i][4] = $dataTest[$i][3];
    }

    $options = array(
        'showLines' => 1,
        'show Headings' => 1,
        'shaded' => 1,
        'shadeCol' => array(0.95, 0.95, 0.95),
        'shadeCol2' => array(0.9, 0.9, 0.9),
        'textCol' => array(0, 0, 0),
        'rowGap' => 1,
        'colGap' => 10,
        'lineCol' => array(1, 1, 1),
        'xPos' => 'center',
        'xOrientation' => 'center',
        'width' => 50,
        'maxWidth' => 300,
    );

    $conf = array('justification' => 'center');

    $pdf->ezText(toLatin1('Liste ' . $devoirTest['promotion']), 14, $conf);
    $pdf->ezText(toLatin1($devoirTest['matiere']), 10, $conf);
    $pdf->ezText($devoirTest['date'] . ' - ' . $devoirTest['heure'] . toLatin1(' - Duree: ') . $devoirTest['duree'], 10, $conf);

    $pdf->ezTable($data, $cols, ' ', $options);
    $pdf->ezStream();
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
