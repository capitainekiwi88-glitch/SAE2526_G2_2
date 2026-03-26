<?php

$pdo = \App\Modele\DAO\Connexion::getInstance();
$etudiantDAO = new \App\Modele\DAO\EtudiantDAO($pdo);

$idPromo = (int) ($_GET['id_promo'] ?? $_POST['id_promo'] ?? 0);

if ($idPromo === 0) {
    header("Location: index.php?p=gest_promo");
    exit;
}

if (isset($_GET['suppr'])) {
    $etud = $etudiantDAO->getById((int) $_GET['suppr']);
    if ($etud) {
        $etudiantDAO->delete($etud);
    }
    header("Location: index.php?p=gest_etudiant&id_promo=" . $idPromo);
    exit;
}

if (isset($_POST['ajouter'])) {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $tt = isset($_POST['tiers_temps']) ? 1 : 0;
    $pmr = isset($_POST['mob_reduite']) ? 1 : 0;
    $idG = (int) $_POST['id_groupe'];

    if (!empty($nom) && !empty($prenom) && $idG > 0) {
        $stmt = $pdo->prepare("INSERT INTO etudiant (nom_etudiant, prenom_etudiant, tiers_temps, mob_reduite, id_groupe) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([strtoupper($nom), $prenom, $tt, $pmr, $idG]);
    }
    header("Location: index.php?p=gest_etudiant&id_promo=" . $idPromo);
    exit;
}

if (isset($_POST['validemodif'])) {
    $idE = (int) $_POST['id_etudiant'];
    $nom = trim($_POST['n_nom'] ?? '');
    $prenom = trim($_POST['n_prenom'] ?? '');
    $tt = isset($_POST['n_tiers_temps']) ? 1 : 0;
    $pmr = isset($_POST['n_mob_reduite']) ? 1 : 0;
    $idG = (int) $_POST['n_id_groupe'];

    if ($idE > 0 && !empty($nom)) {
        $stmt = $pdo->prepare("UPDATE etudiant SET nom_etudiant=?, prenom_etudiant=?, tiers_temps=?, mob_reduite=?, id_groupe=? WHERE id_etudiant=?");
        $stmt->execute([strtoupper($nom), $prenom, $tt, $pmr, $idG, $idE]);
    }
    header("Location: index.php?p=gest_etudiant&id_promo=" . $idPromo);
    exit;
}

$stmtP = $pdo->prepare("SELECT p.*, d.nom_dpt FROM promotion p LEFT JOIN departement d ON p.id_dpt = d.id_dpt WHERE p.id_promo = ?");
$stmtP->execute([$idPromo]);
$promoDetails = $stmtP->fetch(PDO::FETCH_ASSOC);

$stmtG = $pdo->prepare("SELECT * FROM groupe WHERE id_promo = ? ORDER BY nom_groupe");
$stmtG->execute([$idPromo]);
$groupes = $stmtG->fetchAll(PDO::FETCH_ASSOC);

$idGroupeFilter = (int) ($_GET['id_groupe'] ?? 0);

if ($idGroupeFilter > 0) {
    $stmtE = $pdo->prepare("
        SELECT e.*, g.nom_groupe
        FROM etudiant e
        JOIN groupe g ON e.id_groupe = g.id_groupe
        WHERE g.id_promo = ? AND e.id_groupe = ?
        ORDER BY e.nom_etudiant, e.prenom_etudiant
    ");
    $stmtE->execute([$idPromo, $idGroupeFilter]);
} else {
    $stmtE = $pdo->prepare("
        SELECT e.*, g.nom_groupe
        FROM etudiant e
        JOIN groupe g ON e.id_groupe = g.id_groupe
        WHERE g.id_promo = ?
        ORDER BY g.nom_groupe ASC, e.nom_etudiant ASC, e.prenom_etudiant ASC
    ");
    $stmtE->execute([$idPromo]);
}

echo $twig->render('Gestion/etudiant.html.twig', [
    'page' => 'gest_promo',
    'promotion' => $promoDetails,
    'groupes' => $groupes,
    'etudiants' => $stmtE->fetchAll(PDO::FETCH_ASSOC)
]);
