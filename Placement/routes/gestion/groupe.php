<?php

$pdo = \App\Modele\DAO\Connexion::getInstance();
$groupeDAO = new \App\Modele\DAO\GroupeDAO($pdo);

$idPromo = (int) ($_GET['id_promo'] ?? $_POST['id_promo'] ?? 0);

if ($idPromo === 0) {
    header("Location: index.php?p=gest_promo");
    exit;
}

if (isset($_GET['suppr'])) {
    $groupe = $groupeDAO->getById((int) $_GET['suppr']);
    if ($groupe) {
        $groupeDAO->delete($groupe);
    }
    header("Location: index.php?p=gest_groupe&id_promo=" . $idPromo);
    exit;
}

if (isset($_POST['ajouter'])) {
    $nom = trim($_POST['nom_groupe'] ?? '');
    if (!empty($nom)) {
        $nouveauGroupe = new \App\Modele\Entity\Groupe(0, $nom, 0, $idPromo);
        $groupeDAO->insert($nouveauGroupe);
    }
    header("Location: index.php?p=gest_groupe&id_promo=" . $idPromo);
    exit;
}

if (isset($_POST['validemodif'])) {
    $idG = (int) $_POST['id_groupe'];
    $nom = trim($_POST['n_nom_groupe'] ?? '');
    if ($idG > 0 && !empty($nom)) {
        $g = $groupeDAO->getById($idG);
        if ($g) {
            $g->setNomGroupe($nom);
            $groupeDAO->update($g);
        }
    }
    header("Location: index.php?p=gest_groupe&id_promo=" . $idPromo);
    exit;
}

$stmtP = $pdo->prepare("SELECT p.*, d.nom_dpt FROM promotion p LEFT JOIN departement d ON p.id_dpt = d.id_dpt WHERE p.id_promo = ?");
$stmtP->execute([$idPromo]);
$promoDetails = $stmtP->fetch(PDO::FETCH_ASSOC);

$stmtG = $pdo->prepare("
    SELECT g.id_groupe, g.nom_groupe, g.id_promo, COUNT(e.id_etudiant) as nb_etud
    FROM groupe g
    LEFT JOIN etudiant e ON g.id_groupe = e.id_groupe
    WHERE g.id_promo = ?
    GROUP BY g.id_groupe, g.nom_groupe, g.id_promo
    ORDER BY g.nom_groupe
");
$stmtG->execute([$idPromo]);

echo $twig->render('Gestion/groupe.html.twig', [
    'page' => 'gest_promo',
    'promotion' => $promoDetails,
    'groupes' => $stmtG->fetchAll(PDO::FETCH_ASSOC)
]);
