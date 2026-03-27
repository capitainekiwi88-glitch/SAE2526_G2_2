<?php
$pdo = \App\Modele\DAO\Connexion::getInstance();
$ensDAO = new \App\Modele\DAO\EnseignantDAO($pdo);
$matDAO = new \App\Modele\DAO\MatiereDAO($pdo);
$enseigneDAO = new \App\Modele\DAO\EnseignementDAO($pdo);

if (isset($_GET['suppr_ens']) && isset($_GET['suppr_mat'])) {
    $enseigneDAO->delete((int) $_GET['suppr_ens'], (int) $_GET['suppr_mat']);
    header("Location: index.php?p=gest_ensmat");
    exit;
}

if (isset($_POST['ajouter'])) {
    $idEns = (int) $_POST['id_ens'];
    $idMat = (int) $_POST['id_mat'];

    if ($idEns > 0 && $idMat > 0) {
        try {
            $nouvelEnseignement = new \App\Modele\Entity\Enseignement($idEns, $idMat);
            $enseigneDAO->insert($nouvelEnseignement);
        } catch (\PDOException $e) {
            if ($e->getCode() != 23000) {
                throw $e;
            }
        }
    }
    header("Location: index.php?p=gest_ensmat");
    exit;
}

if (isset($_POST['validemodif'])) {
    $oldIdEns = (int) $_POST['old_id_ens'];
    $oldIdMat = (int) $_POST['old_id_mat'];
    $newIdEns = (int) $_POST['n_id_ens'];
    $newIdMat = (int) $_POST['n_id_mat'];

    if ($oldIdEns > 0 && $oldIdMat > 0 && $newIdEns > 0 && $newIdMat > 0) {
        try {
            $modifEnseignement = new \App\Modele\Entity\Enseignement($newIdEns, $newIdMat);
            $enseigneDAO->update($oldIdEns, $oldIdMat, $modifEnseignement);
        } catch (\PDOException $e) {
            if ($e->getCode() != 23000) {
                throw $e;
            }
        }
    }
    header("Location: index.php?p=gest_ensmat");
    exit;
}

echo $twig->render('Gestion/enseignement.html.twig', [
    'page' => $page,
    'enseignements' => $enseigneDAO->findAllWithDetails(),
    'enseignants' => $ensDAO->findAll(),
    'matieres' => $matDAO->findAllWithPromo()
]);
