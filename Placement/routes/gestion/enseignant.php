<?php

$pdo = \App\Modele\DAO\Connexion::getInstance();
$ensDAO = new \App\Modele\DAO\EnseignantDAO($pdo);

if (isset($_GET['suppr'])) {
    $ensDAO->deleteById((int) $_GET['suppr']);
    header("Location: index.php?p=gest_ens");
    exit;
}

if (isset($_POST['ajouter'])) {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $sexe = $_POST['sexe'] ?? 'M';
    $login = trim($_POST['login'] ?? '');
    $admin = isset($_POST['admin']) ? (bool) $_POST['admin'] : false;

    if (!empty($nom) && !empty($prenom) && !empty($login)) {
        $exists = $ensDAO->findByLogin($login);
        if ($exists) {
            $error = 'Ce login est déjà utilisé par un autre enseignant.';
        } else {
            try {
                $nouvelEns = new \App\Modele\Entity\Enseignant(0, $nom, $prenom, $sexe, $login, $admin);
                $ensDAO->insert($nouvelEns, $login);
                header("Location: index.php?p=gest_ens");
                exit;
            } catch (\PDOException $e) {
                $error = 'Erreur lors de l’ajout : ' . $e->getMessage();
            }
        }
    } else {
        $error = 'Tous les champs sont obligatoires pour créer un enseignant.';
    }
}

if (isset($_POST['validemodif'])) {
    $id_ens = (int) ($_POST['id_ens'] ?? 0);
    $nom = trim($_POST['n_nom'] ?? '');
    $prenom = trim($_POST['n_prenom'] ?? '');
    $sexe = $_POST['n_sexe'] ?? 'M';
    $login = trim($_POST['n_login'] ?? '');
    $admin = isset($_POST['n_admin']) ? (bool) $_POST['n_admin'] : false;

    if ($id_ens > 0 && !empty($nom) && !empty($prenom) && !empty($login)) {
        $existing = $ensDAO->findByLogin($login);
        if ($existing && (int)$existing['id_ens'] !== $id_ens) {
            $error = 'Ce login est déjà utilisé par un autre enseignant.';
        } else {
            try {
                $modifEns = new \App\Modele\Entity\Enseignant($id_ens, $nom, $prenom, $sexe, $login, $admin);
                $ensDAO->update($modifEns);
                header("Location: index.php?p=gest_ens");
                exit;
            } catch (\PDOException $e) {
                $error = 'Erreur lors de la mise à jour : ' . $e->getMessage();
            }
        }
    } else {
        $error = 'Tous les champs sont obligatoires pour modifier un enseignant.';
    }
}

echo $twig->render('Gestion/enseignant.html.twig', [
    'page' => $page,
    'enseignants' => $ensDAO->findAll(),
    'error' => $error ?? null,
]);
