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


//Partie importation csv
if (isset($_POST['importer']) && isset($_FILES['fichier_csv'])) {
    $idPromo = (int) $_POST['promo_liee'];
    $file = $_FILES['fichier_csv']['tmp_name'];

    if ($idPromo > 0 && is_uploaded_file($file)) {
        ini_set('auto_detect_line_endings', TRUE);

        $handle = fopen($file, "r");
        if ($handle !== FALSE) {
            fgetcsv($handle, 1000, ";");

            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if (count($data) >= 4) {
                    $nomGroupe = trim($data[1]);
                    $nomEtud = strtoupper(trim($data[2]));
                    $prenomEtud = trim($data[3]);

                    if (!empty($nomGroupe) && !empty($nomEtud)) {
                        $stmtFindGroup = $pdo->prepare("SELECT id_groupe FROM groupe WHERE nom_groupe = ? AND id_promo = ?");
                        $stmtFindGroup->execute([$nomGroupe, $idPromo]);
                        $idGroupe = $stmtFindGroup->fetchColumn();

                        if (!$idGroupe) {
                            $stmtCreateGroup = $pdo->prepare("INSERT INTO groupe (nom_groupe, nb_etud, id_promo) VALUES (?, 0, ?)");
                            $stmtCreateGroup->execute([$nomGroupe, $idPromo]);
                            $idGroupe = $pdo->lastInsertId();
                        }

                        $stmtCheckEtud = $pdo->prepare("SELECT id_etudiant FROM etudiant WHERE nom_etudiant = ? AND prenom_etudiant = ? AND id_groupe = ?");
                        $stmtCheckEtud->execute([$nomEtud, $prenomEtud, $idGroupe]);

                        if (!$stmtCheckEtud->fetchColumn()) {
                            $stmtInsertEtud = $pdo->prepare("INSERT INTO etudiant (nom_etudiant, prenom_etudiant, tiers_temps, mob_reduite, id_groupe) VALUES (?, ?, 0, 0, ?)");
                            $stmtInsertEtud->execute([$nomEtud, $prenomEtud, $idGroupe]);
                        }
                    }
                }
            }
            fclose($handle);
        }
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
    'page' => $page ?? 'gest_promo',
    'departements' => $departements_db,
    'promotions' => $promotions_db
]);