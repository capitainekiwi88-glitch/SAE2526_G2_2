<?php

$pdo = \App\Modele\DAO\Connexion::getInstance();
$promoDAO = new \App\Modele\DAO\PromotionDAO($pdo);

if (isset($_GET['suppr'])) {
    $promo = $promoDAO->getById((int) $_GET['suppr']);
    if ($promo) {
        $promoDAO->delete($promo);
    }
    header("Location: index.php?p=gest_promo");
    exit;
}

if (isset($_POST['ajouter'])) {
    $nom = trim($_POST['nom_promo'] ?? '');
    $annee = trim($_POST['annee'] ?? '');
    $idDpt = (int) $_POST['id_dpt'];
    if (!empty($nom) && !empty($annee) && $idDpt > 0) {
        $nouvellePromo = new \App\Modele\Entity\Promotion(0, $nom, $annee, $idDpt);
        $promoDAO->insert($nouvellePromo);
    }
    header("Location: index.php?p=gest_promo");
    exit;
}

if (isset($_POST['validemodif'])) {
    $idPromo = (int) $_POST['id_promo'];
    $nom = trim($_POST['n_nom_promo'] ?? '');
    $annee = trim($_POST['n_annee'] ?? '');
    $idDpt = (int) $_POST['n_id_dpt'];
    if ($idPromo > 0 && !empty($nom) && !empty($annee) && $idDpt > 0) {
        $modifPromo = new \App\Modele\Entity\Promotion($idPromo, $nom, $annee, $idDpt);
        $promoDAO->update($modifPromo);
    }
    header("Location: index.php?p=gest_promo");
    exit;
}

$stmtDpt = $pdo->query("SELECT id_dpt, nom_dpt FROM departement ORDER BY nom_dpt");
$departements_db = $stmtDpt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT p.id_promo, p.nom_promo, p.annee, p.id_dpt, d.nom_dpt,
               COUNT(DISTINCT g.id_groupe) as nb_groupes,
               COUNT(DISTINCT e.id_etudiant) as nb_etudiants
        FROM promotion p
        LEFT JOIN departement d ON p.id_dpt = d.id_dpt
        LEFT JOIN groupe g ON p.id_promo = g.id_promo
        LEFT JOIN etudiant e ON g.id_groupe = e.id_groupe
        GROUP BY p.id_promo, p.nom_promo, p.annee, p.id_dpt, d.nom_dpt
        ORDER BY p.annee DESC, p.nom_promo ASC";
$promotions_db = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

echo $twig->render('Gestion/promotion.html.twig', [
    'page' => $page,
    'departements' => $departements_db,
    'promotions' => $promotions_db
]);
