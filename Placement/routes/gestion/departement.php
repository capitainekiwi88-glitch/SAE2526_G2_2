<?php
$dptDAO = new \App\Modele\DAO\DepartementDAO();

if (isset($_GET['suppr'])) {
    $dptDAO->deleteById((int) $_GET['suppr']);
    header("Location: index.php?p=gest_dpt");
    exit;
}

if (isset($_POST['ajouter'])) {
    $nomDpt = trim($_POST['nom_dpt'] ?? '');
    if (!empty($nomDpt)) {
        $nouveauDpt = new \App\Modele\Entity\Departement(0, $nomDpt);
        $dptDAO->insert($nouveauDpt);
    }
    header("Location: index.php?p=gest_dpt");
    exit;
}

if (isset($_POST['validemodif'])) {
    $idDpt = (int) $_POST['id_dpt'];
    $nomDpt = trim($_POST['n_nom_dpt'] ?? '');
    if ($idDpt > 0 && !empty($nomDpt)) {
        $modifDpt = new \App\Modele\Entity\Departement($idDpt, $nomDpt);
        $dptDAO->update($modifDpt);
    }
    header("Location: index.php?p=gest_dpt");
    exit;
}

echo $twig->render('Gestion/departement.html.twig', [
    'page' => $page,
    'departements' => $dptDAO->findAll()
]);
