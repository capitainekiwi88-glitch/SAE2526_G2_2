<?php
// Variables disponibles : $twig, $page

$pdo = \App\Modele\DAO\Connexion::getInstance();
$matiereDAO = new \App\Modele\DAO\MatiereDAO($pdo);

if (isset($_GET['suppr'])) {
    $matASupprimer = new \App\Modele\Entity\Matiere((int) $_GET['suppr'], "temp", 1);
    $matiereDAO->delete($matASupprimer);
    header("Location: index.php?p=gest_mat");
    exit;
}

if (isset($_POST['ajouter'])) {
    $idPromo = (int) $_POST['prom'];
    if ($idPromo > 0) {
        $nouvelleMat = new \App\Modele\Entity\Matiere(0, $_POST['nom_mat'], $idPromo);
        $matiereDAO->insert($nouvelleMat);
    }
    header("Location: index.php?p=gest_mat");
    exit;
}

if (isset($_POST['validemodif'])) {
    $idPromo = (int) $_POST['n_promo_mat'];
    if ($idPromo > 0) {
        $modifMat = new \App\Modele\Entity\Matiere((int) $_POST['id_mat'], $_POST['n_nom_mat'], $idPromo);
        $matiereDAO->update($modifMat);
    }
    header("Location: index.php?p=gest_mat");
    exit;
}

$stmtPromo = $pdo->query("SELECT p.id_promo, p.nom_promo, p.annee, d.nom_dpt FROM promotion p LEFT JOIN departement d ON p.id_dpt = d.id_dpt ORDER BY p.nom_promo, p.annee");
$promotions_db = $stmtPromo->fetchAll(PDO::FETCH_ASSOC);

echo $twig->render('Gestion/matiere.html.twig', [
    'page' => $page,
    'matieres' => $matiereDAO->findAllWithPromo(),
    'promotions' => $promotions_db
]);
