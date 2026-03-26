<?php
// Dispatcher gestion — inclus depuis index.php
// Variables disponibles : $twig, $page

$gestionRoutes = [
    'gest_mat'      => 'matiere.php',
    'gest_ens'      => 'enseignant.php',
    'gest_ensmat'   => 'enseignement.php',
    'gest_salle'    => 'salle.php',
    'ajout_salle'   => 'ajoutsalle.php',
    'gest_dpt'      => 'departement.php',
    'gest_bat'      => 'batiment.php',
    'gest_promo'    => 'promotion.php',
    'gest_groupe'   => 'groupe.php',
    'gest_etudiant' => 'etudiant.php',
];

if (isset($gestionRoutes[$page])) {
    require __DIR__ . '/gestion/' . $gestionRoutes[$page];
}
