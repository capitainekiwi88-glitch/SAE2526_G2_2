<?php

$pdo = \App\Modele\DAO\Connexion::getInstance();
$salleDAO = new \App\Modele\DAO\SalleDAO($pdo);

$salleExistante = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT s.*, p.donnee FROM salle s JOIN plan p ON s.id_plan = p.id_plan WHERE s.id_salle = :id");
    $stmt->execute([':id' => (int) $_GET['id']]);
    $salleExistante = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_POST['save_salle_complete'])) {
    $nom = trim($_POST['nomSalle'] ?? '');
    $idBat = (int) $_POST['batSalle'];
    $idDpt = (int) $_POST['dptSalle'];
    $etage = (int) $_POST['etageSalle'];
    $donnee = trim($_POST['donneePlan'] ?? '');
    $capacite = (int) $_POST['capacite'];
    $idSalle = (int) ($_POST['idSalleExistante'] ?? 0);

    if ($idSalle > 0) {
        $salleObj = $salleDAO->getById($idSalle);
        $stmtUpdPlan = $pdo->prepare("UPDATE plan SET donnee = :donnee WHERE id_plan = :idp");
        $stmtUpdPlan->execute([':donnee' => $donnee, ':idp' => $salleObj->getIdPlan()]);

        $salleUpd = new \App\Modele\Entity\Salle($idSalle, $nom, $capacite, $etage, $salleObj->getIdPlan(), $idDpt, $idBat);
        $salleDAO->update($salleUpd);
    } else {
        $stmtPlan = $pdo->prepare("INSERT INTO plan (donnee) VALUES (:donnee)");
        $stmtPlan->execute([':donnee' => $donnee]);
        $idPlan = (int) $pdo->lastInsertId();
        $nouvelleSalle = new \App\Modele\Entity\Salle(0, $nom, $capacite, $etage, $idPlan, $idDpt, $idBat);
        $salleDAO->insert($nouvelleSalle);
    }
    header("Location: index.php?p=gest_salle");
    exit;
}

$stmtBat = $pdo->query("SELECT id_bat AS idBatiment, nom_bat AS nom FROM batiment ORDER BY nom_bat");
$stmtDpt = $pdo->query("SELECT id_dpt AS idDpt, nom_dpt AS nom FROM departement ORDER BY nom_dpt");

echo $twig->render('Gestion/ajoutsalle.html.twig', [
    'page' => 'gest_salle',
    'batiments' => $stmtBat->fetchAll(PDO::FETCH_ASSOC),
    'departements' => $stmtDpt->fetchAll(PDO::FETCH_ASSOC),
    'salleModif' => $salleExistante
]);
