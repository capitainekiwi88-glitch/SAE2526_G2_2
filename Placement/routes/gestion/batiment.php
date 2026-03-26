<?php
$batDAO = new \App\Modele\DAO\BatimentDAO();

if (isset($_GET['suppr'])) {
    $batDAO->deleteById((int) $_GET['suppr']);
    header("Location: index.php?p=gest_bat");
    exit;
}

if (isset($_POST['ajouter'])) {
    $nomBat = trim($_POST['nom_bat'] ?? '');
    $adBat = trim($_POST['ad_bat'] ?? '');
    if (!empty($nomBat) && !empty($adBat)) {
        $nouveauBat = new \App\Modele\Entity\Batiment(0, $nomBat, $adBat);
        $batDAO->insert($nouveauBat);
    }
    header("Location: index.php?p=gest_bat");
    exit;
}

if (isset($_POST['validemodif'])) {
    $idBat = (int) $_POST['id_bat'];
    $nomBat = trim($_POST['n_nom_bat'] ?? '');
    $adBat = trim($_POST['n_ad_bat'] ?? '');
    if ($idBat > 0 && !empty($nomBat) && !empty($adBat)) {
        $modifBat = new \App\Modele\Entity\Batiment($idBat, $nomBat, $adBat);
        $batDAO->update($modifBat);
    }
    header("Location: index.php?p=gest_bat");
    exit;
}

echo $twig->render('Gestion/batiment.html.twig', [
    'page' => $page,
    'batiments' => $batDAO->findAll()
]);
