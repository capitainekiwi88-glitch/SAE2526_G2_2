<?php

$pdo = \App\Modele\DAO\Connexion::getInstance();
$salleDAO = new \App\Modele\DAO\SalleDAO($pdo);

if (isset($_GET['suppr'])) {
    $salleDAO->deleteById((int) $_GET['suppr']);
    header("Location: index.php?p=gest_salle");
    exit;
}

if (isset($_POST['validemodif'])) {
    $idSalle = (int) $_POST['id_salle'];
    $nomSalle = trim($_POST['n_nom_salle'] ?? '');
    $capacite = (int) $_POST['n_capacite'];
    $etage = (int) $_POST['n_etage'];
    $idBat = (int) $_POST['n_id_bat'];
    $idDpt = (int) $_POST['n_id_dpt'];

    if ($idSalle > 0 && !empty($nomSalle) && $capacite > 0 && $idBat > 0 && $idDpt > 0) {
        $oldSalle = $salleDAO->getById($idSalle);
        if ($oldSalle) {
            $modifSalle = new \App\Modele\Entity\Salle($idSalle, $nomSalle, $capacite, $etage, $oldSalle->getIdPlan(), $idDpt, $idBat);
            $salleDAO->update($modifSalle);
        }
    }
    header("Location: index.php?p=gest_salle");
    exit;
}

$stmtBat = $pdo->query("SELECT id_bat AS idBatiment, nom_bat AS nom FROM batiment ORDER BY nom_bat");
$batiments_db = $stmtBat->fetchAll(PDO::FETCH_ASSOC);

$stmtDpt = $pdo->query("SELECT id_dpt AS idDpt, nom_dpt AS nom FROM departement ORDER BY nom_dpt");
$departements_db = $stmtDpt->fetchAll(PDO::FETCH_ASSOC);

echo $twig->render('Gestion/salle.html.twig', [
    'page' => $page,
    'salles' => $salleDAO->findAllWithDetails(),
    'batiments' => $batiments_db,
    'departements' => $departements_db
]);
