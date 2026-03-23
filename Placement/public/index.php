<?php
require_once __DIR__ . '/../vendor/autoload.php';

session_start();

function placementBaseData(): array
{
    return [
        'promotions' => [
            ['id' => 1, 'label' => 'BUT1 INFO'],
            ['id' => 2, 'label' => 'BUT2 INFO'],
            ['id' => 3, 'label' => 'BUT3 INFO'],
        ],
        'groupes' => [
            ['id' => 1, 'promo_id' => 1, 'label' => 'Groupe A'],
            ['id' => 2, 'promo_id' => 1, 'label' => 'Groupe B'],
            ['id' => 3, 'promo_id' => 2, 'label' => 'Groupe A'],
            ['id' => 4, 'promo_id' => 3, 'label' => 'Groupe A'],
        ],
        'matieres' => [
            ['id' => 1, 'promo_id' => 1, 'nom' => 'Développement Web', 'promo_label' => 'BUT1 INFO'],
            ['id' => 2, 'promo_id' => 1, 'nom' => 'Mathématiques', 'promo_label' => 'BUT1 INFO'],
            ['id' => 3, 'promo_id' => 2, 'nom' => 'Base de données', 'promo_label' => 'BUT2 INFO'],
            ['id' => 4, 'promo_id' => 3, 'nom' => 'Architecture logicielle', 'promo_label' => 'BUT3 INFO'],
        ],
        'salles' => [
            ['id' => 1, 'nom' => 'A101', 'batiment' => 'IUT', 'capacite' => 16],
            ['id' => 2, 'nom' => 'B204', 'batiment' => 'IUT', 'capacite' => 12],
        ],
    ];
}

function placementDateError(string $dateExam): string
{
    if ($dateExam === '') {
        return '';
    }

    $date = \DateTimeImmutable::createFromFormat('Y-m-d', $dateExam);
    $errors = \DateTimeImmutable::getLastErrors();
    if ($date === false || ($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0) {
        return 'La date du devoir est invalide.';
    }

    if ($date < new \DateTimeImmutable('today')) {
        return 'La date du devoir ne peut pas être antérieure à aujourd’hui.';
    }

    return '';
}

function placementDefaultState(): array
{
    return [
        'current_stage' => 1,
        'exam' => [
            'date' => '',
            'start_hour' => '08',
            'start_minute' => '00',
            'duration_hour' => '02',
            'duration_minute' => '00',
        ],
        'combinations' => [],
        'placements' => [
            'rooms' => [],
            'promo_exports' => [],
        ],
        'date_error' => '',
        'next_combination_id' => 1,
    ];
}

function &placementState(): array
{
    if (!isset($_SESSION['placement_state'])) {
        $_SESSION['placement_state'] = placementDefaultState();
    }

    return $_SESSION['placement_state'];
}

function placementFindById(array $items, int $id): ?array
{
    foreach ($items as $item) {
        if ((int) $item['id'] === $id) {
            return $item;
        }
    }

    return null;
}

function placementBuildPlacements(array $combinations, array $salles): array
{
    $layoutByRoom = [
        1 => [
            ['seat', 'seat', 'aisle', 'seat', 'seat'],
            ['seat', 'seat', 'aisle', 'seat', 'seat'],
        ],
        2 => [
            ['seat', 'seat', 'aisle', 'seat'],
            ['seat', 'seat', 'aisle', 'seat'],
        ],
    ];

    $names = [
        ['Alice', 'MARTIN'],
        ['Lucas', 'BERNARD'],
        ['Ines', 'PETIT'],
        ['Noah', 'ROBERT'],
        ['Emma', 'RICHARD'],
        ['Lina', 'DUBOIS'],
        ['Adam', 'THOMAS'],
        ['Zoé', 'MICHEL'],
    ];

    $rooms = [];
    $promoExports = [];

    foreach ($combinations as $combination) {
        $roomId = (int) $combination['salle_id'];
        $salle = placementFindById($salles, $roomId);
        if ($salle === null) {
            continue;
        }

        if (!isset($rooms[$roomId])) {
            $layout = $layoutByRoom[$roomId] ?? $layoutByRoom[1];
            $seatMeta = [];
            $assignments = [];
            $number = 1;

            foreach ($layout as $rowIndex => $row) {
                foreach ($row as $colIndex => $cell) {
                    if ($cell === 'seat') {
                        $key = $rowIndex . '-' . $colIndex;
                        $seatMeta[$key] = ['number' => $number++];
                        $assignments[$key] = null;
                    }
                }
            }

            $rooms[$roomId] = [
                'id' => $roomId,
                'name' => $salle['nom'],
                'building' => $salle['batiment'],
                'student_count' => 0,
                'capacity' => count($seatMeta),
                'supervisor' => 'Surveillant test',
                'combination_labels' => [],
                'layout' => $layout,
                'seat_meta' => $seatMeta,
                'assignments' => $assignments,
            ];
        }

        $rooms[$roomId]['combination_labels'][] = $combination['matiere_label'];
        $promoExports[$combination['promo_id']] = [
            'id' => $combination['promo_id'],
            'label' => $combination['promo_label'],
        ];

        $students = [];
        foreach ($names as $index => [$prenom, $nom]) {
            $students[] = [
                'display_name' => $prenom . ' ' . $nom,
                'groupe' => $combination['group_label'],
                'seed' => crc32($roomId . '|' . $combination['id'] . '|' . $index),
            ];
        }
        usort($students, static fn(array $a, array $b): int => $a['seed'] <=> $b['seed']);

        foreach ($rooms[$roomId]['assignments'] as $seatKey => $occupant) {
            if ($occupant === null && !empty($students)) {
                $rooms[$roomId]['assignments'][$seatKey] = array_shift($students);
                $rooms[$roomId]['student_count']++;
            }
        }
    }

    foreach ($rooms as &$room) {
        $room['combination_labels'] = array_values(array_unique($room['combination_labels']));
    }
    unset($room);

    return [
        'rooms' => array_values($rooms),
        'promo_exports' => array_values($promoExports),
    ];
}

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader, [
    'cache' => false,
]);

$page = $_GET['p'] ?? 'home';



//j'ai mit un jeu de donnée temporaire pour tester l'affichage.
switch ($page) {
    case 'util_placement':
        $data = placementBaseData();
        $state = &placementState();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $state['exam'] = [
                'date' => $_POST['date_exam'] ?? '',
                'start_hour' => $_POST['start_hour'] ?? '08',
                'start_minute' => $_POST['start_minute'] ?? '00',
                'duration_hour' => $_POST['duration_hour'] ?? '02',
                'duration_minute' => $_POST['duration_minute'] ?? '00',
            ];
            $state['date_error'] = '';
            $action = $_POST['action'] ?? '';

            if (in_array($action, ['add_combination', 'generate_placements'], true)) {
                $state['date_error'] = placementDateError($state['exam']['date']);
            }

            if ($action === 'add_combination' && $state['date_error'] === '') {
                $promoId = (int) ($_POST['promo_id'] ?? 0);
                $groupId = (int) ($_POST['group_id'] ?? 0);
                $matiereId = (int) ($_POST['matiere_id'] ?? 0);
                $salleId = (int) ($_POST['salle_id'] ?? 0);

                $promo = placementFindById($data['promotions'], $promoId);
                $matiere = placementFindById($data['matieres'], $matiereId);
                $salle = placementFindById($data['salles'], $salleId);
                $groupe = $groupId === 0 ? null : placementFindById($data['groupes'], $groupId);

                if ($promo !== null && $matiere !== null && $salle !== null) {
                    $state['combinations'][] = [
                        'id' => $state['next_combination_id']++,
                        'promo_id' => $promoId,
                        'salle_id' => $salleId,
                        'promo_label' => $promo['label'],
                        'group_label' => $groupe['label'] ?? 'Toute la promotion',
                        'matiere_label' => $matiere['nom'],
                        'salle_label' => $salle['nom'],
                    ];
                }
            }

            if ($action === 'remove_combination') {
                $removeId = (int) ($_POST['combination_id'] ?? 0);
                $state['combinations'] = array_values(array_filter(
                    $state['combinations'],
                    static fn(array $combination): bool => (int) $combination['id'] !== $removeId
                ));
            }

            if ($action === 'generate_placements' && $state['date_error'] === '' && !empty($state['combinations'])) {
                $state['placements'] = placementBuildPlacements($state['combinations'], $data['salles']);
                $state['current_stage'] = 2;
            }

            if ($action === 'go_to_setup') {
                $state['current_stage'] = 1;
            }

            if ($action === 'go_to_exports' && !empty($state['placements']['rooms'])) {
                $state['current_stage'] = 3;
            }

            if ($action === 'go_to_placement' && !empty($state['placements']['rooms'])) {
                $state['current_stage'] = 2;
            }
        }

        echo $twig->render('placement.html.twig', [
            'current_stage' => $state['current_stage'],
            'warnings' => [],
            'exam' => $state['exam'],
            'date_error' => $state['date_error'],
            'today_iso' => date('Y-m-d'),
            'promotions' => $data['promotions'],
            'groupes' => $data['groupes'],
            'matieres' => $data['matieres'],
            'salles' => $data['salles'],
            'combinations' => $state['combinations'],
            'placements' => $state['placements'],
        ]);
        break;
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
