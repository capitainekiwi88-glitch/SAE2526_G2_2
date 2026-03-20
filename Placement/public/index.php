<?php
require_once __DIR__ . '/../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader, [
    'cache' => false,
]);

$page = $_GET['p'] ?? 'home';



//j'ai mit un jeu de donnée temporaire pour tester l'affichage.
switch ($page) {
    case 'gest_mat':
        $promotions_test = [
            ['id_promo' => 1, 'nom_promo' => 'BUT1', 'nom_dpt' => 'INFO', 'annee' => '2025'],
            ['id_promo' => 2, 'nom_promo' => 'BUT2', 'nom_dpt' => 'INFO', 'annee' => '2025'],
            ['id_promo' => 3, 'nom_promo' => 'BUT2 Passerelle', 'nom_dpt' => 'INFO', 'annee' => '2025'],
            ['id_promo' => 4, 'nom_promo' => 'BUT3', 'nom_dpt' => 'INFO', 'annee' => '2025']
        ];
        $matieres_test = [
            ['id_mat' => 101, 'nom_mat' => 'Eco-Gestion-Droit', 'nom_promo' => 'BUT1', 'nom_dpt' => 'INFO', 'annee' => '2025'],
            ['id_mat' => 102, 'nom_mat' => 'Informatique', 'nom_promo' => 'BUT1', 'nom_dpt' => 'INFO', 'annee' => '2025'],
            ['id_mat' => 103, 'nom_mat' => 'Math', 'nom_promo' => 'BUT1', 'nom_dpt' => 'INFO', 'annee' => '2025'],
            ['id_mat' => 104, 'nom_mat' => 'Eco-Gestion-Droit', 'nom_promo' => 'BUT2', 'nom_dpt' => 'INFO', 'annee' => '2025'],
            ['id_mat' => 105, 'nom_mat' => 'Développement Web', 'nom_promo' => 'BUT2', 'nom_dpt' => 'INFO', 'annee' => '2025'],
            ['id_mat' => 106, 'nom_mat' => 'Architecture Réseau', 'nom_promo' => 'BUT3', 'nom_dpt' => 'INFO', 'annee' => '2025']
        ];
        echo $twig->render('matiere.html.twig', [
            'matieres' => $matieres_test,
            'promotions' => $promotions_test
        ]);
        break;
    case 'gest_ens':
        $enseignants_test = [
            ['id_ens' => 1, 'nom' => 'Bougdira', 'prenom' => 'Nathalie', 'sexe' => 'F', 'login' => 'bougnath', 'admin' => 1],
            ['id_ens' => 2, 'nom' => 'Laroche', 'prenom' => 'Pierre', 'sexe' => 'M', 'login' => 'laroche', 'admin' => 0],
            ['id_ens' => 3, 'nom' => 'Roka', 'prenom' => 'Zsuzsanna', 'sexe' => 'F', 'login' => 'roka', 'admin' => 0],
            ['id_ens' => 4, 'nom' => 'Spengler', 'prenom' => 'Anne', 'sexe' => 'F', 'login' => 'spengler', 'admin' => 0]
        ];

        echo $twig->render('enseignant.html.twig', [
            'enseignants' => $enseignants_test
        ]);
        break;
    case 'gest_ensmat':
        $matieres_test = [
            ['id_mat' => 101, 'nom_mat' => 'Eco-Gestion-Droit', 'nom_promo' => 'BUT1 INFO'],
            ['id_mat' => 102, 'nom_mat' => 'Math', 'nom_promo' => 'BUT1 INFO'],
            ['id_mat' => 103, 'nom_mat' => 'Informatique', 'nom_promo' => 'BUT1 INFO']
        ];

        $enseignants_test = [
            ['id_ens' => 1, 'nom' => 'Bougdira', 'prenom' => 'Nathalie'],
            ['id_ens' => 2, 'nom' => 'Laroche', 'prenom' => 'Pierre']
        ];

        $enseignements_test = [
            [
                'id_enseignement' => 1,
                'nom_enseignant' => 'Bougdira',
                'prenom_enseignant' => 'Nathalie',
                'nom_matiere' => 'Eco-Gestion-Droit',
                'nom_promo' => 'BUT1'
            ],
            [
                'id_enseignement' => 2,
                'nom_enseignant' => 'Laroche',
                'prenom_enseignant' => 'Pierre',
                'nom_matiere' => 'test',
                'nom_promo' => 'BUT1'
            ]
        ];

        echo $twig->render('enseignement.html.twig', [
            'enseignants' => $enseignants_test,
            'matieres' => $matieres_test,
            'enseignements' => $enseignements_test
        ]);
        break;
    case 'gest_salle':
        echo $twig->render('salle.html.twig', []);
        break;

    case 'gest_dpt':
        $departements_test = [
            ['id_dpt' => 1, 'nom_dpt' => 'INFO'],
            ['id_dpt' => 2, 'nom_dpt' => 'SD']
        ];

        echo $twig->render('departement.html.twig', [
            'departements' => $departements_test
        ]);
        break;


    case 'gest_bat':
        $batiments_test = [
            ['id_bat' => 1, 'nom_bat' => 'IUT de Metz', 'ad_bat' => 'Saulcy'],
            ['id_bat' => 2, 'nom_bat' => 'Lettres et Langues', 'ad_bat' => 'Saulcy']
        ];

        echo $twig->render('batiment.html.twig', [
            'batiments' => $batiments_test
        ]);
        break;

        
    case 'gest_promo':
        echo $twig->render('promotion.html.twig', []);
        break;
    default:
        echo $twig->render('index.html.twig', [
            'nom_projet' => 'Gestion de Placement',
            'etudiants' => ['Alice', 'Bob', 'Charlie'],
            'message' => 'Ton installation Twig est un succès !'
        ]);
        break;
}