<?php
require_once __DIR__ . '/../vendor/autoload.php';

session_start();

// Valeurs fictives pour tester l'affichage du placement, à remplacer par des données réelles provenant de la base de données via les DAO
function placementBaseData(): array
{
    return [
        'promotions' => [
            ['id' => 1, 'label' => 'INFO BUT1'],
            ['id' => 2, 'label' => 'INFO BUT2'],
            ['id' => 3, 'label' => 'INFO BUT2 Passerelle'],
            ['id' => 4, 'label' => 'INFO BUT3'],
        ],
        'groupes' => [
            ['id' => 1, 'promo_id' => 1, 'label' => 'Groupe 1'],
            ['id' => 2, 'promo_id' => 1, 'label' => 'Groupe 2'],
            ['id' => 3, 'promo_id' => 1, 'label' => 'Groupe 3'],
            ['id' => 4, 'promo_id' => 1, 'label' => 'Groupe 4'],
            ['id' => 5, 'promo_id' => 2, 'label' => 'Groupe 1'],
            ['id' => 6, 'promo_id' => 2, 'label' => 'Groupe 2'],
            ['id' => 7, 'promo_id' => 2, 'label' => 'Groupe 3'],
            ['id' => 8, 'promo_id' => 3, 'label' => 'Groupe 1'],
            ['id' => 9, 'promo_id' => 4, 'label' => 'Groupe 1'],
            ['id' => 10, 'promo_id' => 4, 'label' => 'Groupe 2'],
            ['id' => 11, 'promo_id' => 4, 'label' => 'Groupe 3'],

        ],
        'matieres' => [
            ['id' => 1, 'promo_id' => 1, 'nom' => 'Eco-Gestion-Droit', 'promo_label' => 'INFO BUT1'],
            ['id' => 2, 'promo_id' => 1, 'nom' => 'Informatique', 'promo_label' => 'INFO BUT1'],
            ['id' => 3, 'promo_id' => 1, 'nom' => 'Math', 'promo_label' => 'INFO BUT1'],

            ['id' => 4, 'promo_id' => 2, 'nom' => 'Eco-Gestion-Droit', 'promo_label' => 'INFO BUT2'],
            ['id' => 5, 'promo_id' => 2, 'nom' => 'Informatique', 'promo_label' => 'INFO BUT2'],
            ['id' => 6, 'promo_id' => 2, 'nom' => 'Math', 'promo_label' => 'INFO BUT2'],

            ['id' => 7, 'promo_id' => 4, 'nom' => 'Eco-Gestion-Droit', 'promo_label' => 'INFO BUT2 Passerelle'],
            ['id' => 8, 'promo_id' => 4, 'nom' => 'Informatique', 'promo_label' => 'INFO BUT2 Passerelle'],
            ['id' => 9, 'promo_id' => 4, 'nom' => 'Math', 'promo_label' => 'INFO BUT2 Passerelle'],

            ['id' => 10, 'promo_id' => 3, 'nom' => 'Eco-Gestion-Droit', 'promo_label' => 'INFO BUT3'],
            ['id' => 11, 'promo_id' => 3, 'nom' => 'Informatique', 'promo_label' => 'INFO BUT3'],
            ['id' => 12, 'promo_id' => 3, 'nom' => 'Math', 'promo_label' => 'INFO BUT3'],


        ],
        'salles' => [
            ['id' => 1, 'nom' => 'Amphi A', 'batiment' => 'IUT', 'capacite' => 150],
            ['id' => 2, 'nom' => 'Amphi B', 'batiment' => 'IUT', 'capacite' => 150],
            ['id' => 3, 'nom' => 'B09', 'batiment' => 'IUT', 'capacite' => 56],
            ['id' => 4, 'nom' => 'E23', 'batiment' => 'IUT', 'capacite' => 32],
            
        ],
    ];
}

// au cas ou, pourrait etre supprimée bientot
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

// informations par défaut pour le placement, peut être réinitialisé par l'utilisateur
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
        'form' => [
            'promo_id' => '',
            'group_id' => '0',
            'matiere_id' => '',
            'salle_id' => '',
        ],
        'combinations' => [],
        'placements' => [
            'rooms' => [],
            'promo_exports' => [],
        ],
        'date_error' => '',
        'form_error' => '',
        'next_combination_id' => 1,
    ];
}

function placementResetSetupForm(array &$state): void
{
    $defaultState = placementDefaultState();
    $state['exam'] = $defaultState['exam'];
    $state['form'] = $defaultState['form'];
    $state['date_error'] = '';
    $state['form_error'] = '';
}

function placementResetCombinationForm(array &$state): void
{
    $state['form']['group_id'] = '0';
    $state['date_error'] = '';
    $state['form_error'] = '';
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

function placementStudentFixtures(): array
{
    return [
        2 => [
            5 => [
                ['ADAM', 'Mathis'],
                ['ADOLPH', 'Noa'],
                ['BAILLY', 'Clement'],
                ['BARETH', 'Aymeric'],
                ['BAYIHA-MBARGA', 'Dieudonne'],
                ['BOUGUET', 'Thomas'],
                ['BONKOSI', 'Ange'],
                ['CHAOUCHI', 'Aksel'],
                ['DARCIAUX', 'Yoann'],
                ['DOBOS', 'Leo'],
                ['FEIPEL', 'Aurelien'],
                ['FUSCIELLO', 'Baptiste'],
                ['MALLINGER', 'Robin'],
                ['MARCHAL', 'Aurelien'],
                ['PERON', 'Ethan'],
                ['ALICI', 'Teoman'],
                ['BENDEKKICHE', 'Sajad'],
                ['BOSCAROL', 'Francis'],
                ['EL-AAMERY', 'Ilyas'],
                ['GRAINE', 'Yanis'],
                ['ISHAK', 'Yacine'],
                ['ILSER', 'Maxence'],
                ['JOLY', 'Aurelien'],
                ['PRANDATO', 'Jade'],
                ['QUENTIN', 'Bastien'],
                ['ROUMILI', 'Rabah'],
                ['SWIATOKA', 'Maximilien'],
                ['TAVERNI', 'Alexandre'],
                ['TUNC', 'Yilmaz'],
                ['WADDELL', 'Simon'],
            ],
            6 => [
                ['ARSLAN', 'Aedem'],
                ['BARRE', 'Alexandre'],
                ['BENMOUEFFEK', 'Anais'],
                ['COLASSE', 'Nathan'],
                ['FADILY', 'Ikrame'],
                ['FEISTHAUER', 'Simon'],
                ['GOEPFERT-BROUTTA', 'Maé'],
                ['LAURI', 'Matteo'],
                ['LEONARD--RUBECK', 'Enzo'],
                ['ROUX', 'Mathieu'],
                ['SCHUMACHER', 'Theo'],
                ['SIMON', 'Leopold'],
                ['TOK', 'Mikail'],
                ['TOPAL', 'Fatih'],
                ['BOUHOUCH', 'Chouaib'],
                ['CORONA', 'Nikola'],
                ['DENOYELLE', 'Victorien'],
                ['FREY', 'Thomas'],
                ['GAUZERE', 'Julien'],
                ['GINOT', 'Auguste'],
                ['HEYTENNE', 'Mathis'],
                ['KHOUCHI', 'Inas'],
                ['LUPO', 'Loic'],
                ['MEDOU-AMOUGUI', 'Sam-Dylan'],
                ['SCHOU', 'Lilian'],
                ['STILLE', 'Evan'],
                ['THIS', 'Esteban'],
                ['TIRONI', 'Baptiste'],
            ],
            7 => [
                ['GIORDANI', 'Enzo'],
                ['GIRRES', 'Alice'],
                ['HUET', 'Severin'],
                ['KIRCHER', 'Nino'],
                ['LADURELLE', 'Alexis'],
                ['LAMBERT', 'Quentin'],
                ['OUKARA', 'Anass'],
                ['PETROVIC', 'Hugo'],
                ['PIZETTE', 'Bartholome'],
                ['PLANCHER', 'Mattéo'],
                ['RICHET', 'Fabien'],
                ['SALVADORI', 'Théo'],
                ['SCHAAL', 'Olivier'],
                ['TRIBUT', 'Enzo'],
                ['ZOLLER', 'Mathieu'],
            ],
        ],
    ];
}

function placementStudentRecord(int $promoId, int $groupId, int $index, string $nom, string $prenom): array
{
    return [
        'id' => sprintf('%d-%d-%d', $promoId, $groupId, $index),
        'promo_id' => $promoId,
        'group_id' => $groupId,
        'last_name' => $nom,
        'first_name' => $prenom,
        'display_name' => $nom . ' ' . $prenom,
    ];
}

function placementGeneratedStudents(int $promoId, int $groupId): array
{
    $count = $groupId === 0 ? 16 : 8;
    $students = [];
    for ($i = 1; $i <= $count; $i++) {
        $students[] = placementStudentRecord($promoId, $groupId, $i, 'Etudiant', $promoId . '-' . $groupId . '-' . $i);
    }

    return $students;
}

function placementStudentsForSelection(int $promoId, int $groupId): array
{
    $fixtures = placementStudentFixtures();

    if ($groupId === 0) {
        $students = [];
        foreach (($fixtures[$promoId] ?? []) as $fixtureGroupId => $groupStudents) {
            foreach ($groupStudents as $index => [$nom, $prenom]) {
                $students[] = placementStudentRecord($promoId, (int) $fixtureGroupId, $index + 1, $nom, $prenom);
            }
        }

        if (!empty($students)) {
            return $students;
        }
    }

    if (isset($fixtures[$promoId][$groupId])) {
        return array_map(
            static fn(array $student, int $index): array => placementStudentRecord($promoId, $groupId, $index + 1, $student[0], $student[1]),
            $fixtures[$promoId][$groupId],
            array_keys($fixtures[$promoId][$groupId])
        );
    }

    return placementGeneratedStudents($promoId, $groupId);
}

function placementStudentsForPromotion(int $promoId): array
{
    $fixtures = placementStudentFixtures();

    if (!isset($fixtures[$promoId])) {
        return [];
    }

    $students = [];
    foreach ($fixtures[$promoId] as $groupId => $groupStudents) {
        foreach ($groupStudents as $index => [$nom, $prenom]) {
            $students[] = placementStudentRecord($promoId, (int) $groupId, $index + 1, $nom, $prenom);
        }
    }

    usort($students, static function (array $a, array $b): int {
        return strcmp($a['display_name'], $b['display_name']);
    });

    return $students;
}

function placementStudentCount(int $promoId, int $groupId): int
{
    return count(placementStudentsForSelection($promoId, $groupId));
}

function placementCombinationsForView(array $combinations, array $salles): array
{
    $roomUsage = [];
    $view = [];

    foreach ($combinations as $combination) {
        $roomId = (int) $combination['salle_id'];
        $salle = placementFindById($salles, $roomId);
        $capacity = (int) ($salle['capacite'] ?? 0);
        $studentCount = (int) ($combination['student_count'] ?? 0);

        if (!isset($roomUsage[$roomId])) {
            $roomUsage[$roomId] = 0;
        }

        $roomUsage[$roomId] += $studentCount;
        $freeSeats = max($capacity - $roomUsage[$roomId], 0);

        $view[] = $combination + [
            'free_seats' => $freeSeats,
            'total_seats' => $capacity,
        ];
    }

    return $view;
}

function placementHasDuplicateStudents(array $combinations, int $promoId, int $groupId): bool
{
    foreach ($combinations as $combination) {
        if ((int) $combination['promo_id'] !== $promoId) {
            continue;
        }

        $existingGroupId = (int) $combination['group_id'];
        if ($groupId === 0 || $existingGroupId === 0 || $existingGroupId === $groupId) {
            return true;
        }
    }

    return false;
}

function placementExceedsDistinctLimit(array $combinations, string $key, int $currentId, int $maxDistinct): bool
{
    $ids = [];
    foreach ($combinations as $combination) {
        $value = (int) ($combination[$key] ?? 0);
        if ($value > 0 && !in_array($value, $ids, true)) {
            $ids[] = $value;
        }
    }

    if (count($ids) < $maxDistinct) {
        return false;
    }

    return !in_array($currentId, $ids, true);
}

function placementRoomOverCapacity(array $combinations, array $salles, int $salleId, int $studentCount): bool
{
    $salle = placementFindById($salles, $salleId);
    $capacity = (int) ($salle['capacite'] ?? 0);
    $usedSeats = $studentCount;

    foreach ($combinations as $combination) {
        if ((int) $combination['salle_id'] === $salleId) {
            $usedSeats += (int) ($combination['student_count'] ?? 0);
        }
    }

    return $usedSeats > $capacity;
}

function placementCombinationError(array $combinations, array $salles, int $promoId, int $groupId, int $matiereId, int $salleId): string
{
    if (placementExceedsDistinctLimit($combinations, 'matiere_id', $matiereId, 2)) {
        return 'Le nombre maximum de matière est atteint !';
    }

    if (placementExceedsDistinctLimit($combinations, 'salle_id', $salleId, 2)) {
        return 'Le nombre maximum de salle est atteint !';
    }

    if (placementHasDuplicateStudents($combinations, $promoId, $groupId)) {
        return 'Vous essayez de placer les mêmes élèves plusieurs fois ou à des endroits différents !';
    }

    if (placementRoomOverCapacity($combinations, $salles, $salleId, placementStudentCount($promoId, $groupId))) {
        return 'Il n\'y a pas assez de place disponible dans cette salle.';
    }

    return '';
}

function placementStudentCountMap(array $promotions, array $groupes): array
{
    $counts = [];

    foreach ($promotions as $promotion) {
        $promoId = (int) $promotion['id'];
        $counts[$promoId] = [
            0 => placementStudentCount($promoId, 0),
        ];
    }

    foreach ($groupes as $groupe) {
        $promoId = (int) $groupe['promo_id'];
        $groupId = (int) $groupe['id'];
        $counts[$promoId][$groupId] = placementStudentCount($promoId, $groupId);
    }

    return $counts;
}

function placementAddCombination(array &$state, array $data): void
{
    $state['date_error'] = placementDateError($state['exam']['date']);
    $state['form_error'] = '';

    if ($state['date_error'] !== '') {
        return;
    }

    $promoId = (int) ($state['form']['promo_id'] !== '' ? $state['form']['promo_id'] : 0);
    $groupId = (int) ($state['form']['group_id'] !== '' ? $state['form']['group_id'] : 0);
    $matiereId = (int) ($state['form']['matiere_id'] !== '' ? $state['form']['matiere_id'] : 0);
    $salleId = (int) ($state['form']['salle_id'] !== '' ? $state['form']['salle_id'] : 0);

    $promo = placementFindById($data['promotions'], $promoId);
    $matiere = placementFindById($data['matieres'], $matiereId);
    $salle = placementFindById($data['salles'], $salleId);
    $groupe = $groupId === 0 ? null : placementFindById($data['groupes'], $groupId);

    if ($promo === null || $matiere === null || $salle === null) {
        $state['form_error'] = 'La combinaison sélectionnée est invalide.';
        return;
    }

    $state['form_error'] = placementCombinationError(
        $state['combinations'],
        $data['salles'],
        $promoId,
        $groupId,
        $matiereId,
        $salleId
    );

    if ($state['form_error'] !== '') {
        return;
    }

    $state['combinations'][] = [
        'id' => $state['next_combination_id']++,
        'promo_id' => $promoId,
        'group_id' => $groupId,
        'matiere_id' => $matiereId,
        'salle_id' => $salleId,
        'promo_label' => $promo['label'],
        'group_label' => $groupe['label'] ?? 'Toute la promotion',
        'matiere_label' => $matiere['nom'],
        'salle_label' => $salle['nom'],
        'student_count' => placementStudentCount($promoId, $groupId),
    ];

    placementResetCombinationForm($state);
}

function placementRemoveCombination(array &$state, int $removeId): void
{
    $state['combinations'] = array_values(array_filter(
        $state['combinations'],
        static fn(array $combination): bool => (int) $combination['id'] !== $removeId
    ));
}

function placementSortedSeatKeys(array $assignments): array
{
    $seatKeys = array_keys($assignments);
    usort($seatKeys, static function (string $a, string $b): int {
        [$rowA, $colA] = array_map('intval', explode('-', $a));
        [$rowB, $colB] = array_map('intval', explode('-', $b));
        if ($rowA !== $rowB) {
            return $rowB <=> $rowA;
        }

        return $colA <=> $colB;
    });

    return $seatKeys;
}

function placementBuildPlacements(array $combinations, array $salles): array
{
    $rooms = [];
    $promoExports = [];
    $buildLayout = static function (int $capacity): array {
        $layout = [];
        $rows = max(1, (int) ceil($capacity / 15));
        $remaining = $capacity;
        for ($row = 0; $row < $rows; $row++) {
            $seatsInRow = min(15, $remaining);
            $layout[$row] = array_fill(0, $seatsInRow, 'seat');
            $remaining -= $seatsInRow;
        }

        return $layout;
    };

    foreach ($combinations as $combination) {
        $roomId = (int) $combination['salle_id'];
        $salle = placementFindById($salles, $roomId);
        if ($salle === null) {
            continue;
        }

        if (!isset($rooms[$roomId])) {
            $layout = $buildLayout((int) $salle['capacite']);
            $seatMeta = [];
            $assignments = [];

            foreach ($layout as $rowIndex => $row) {
                foreach ($row as $colIndex => $cell) {
                    if ($cell === 'seat') {
                        $key = $rowIndex . '-' . $colIndex;
                        $seatMeta[$key] = [
                            'row' => $rowIndex,
                            'col' => $colIndex,
                            'row_label' => count($layout) - $rowIndex,
                            'col_label' => $colIndex + 1,
                        ];
                        $assignments[$key] = null;
                    }
                }
            }

            $rooms[$roomId] = [
                'id' => $roomId,
                'name' => $salle['nom'],
                'building' => $salle['batiment'],
                'student_count' => 0,
                'capacity' => (int) $salle['capacite'],
                'supervisor' => 'Surveillant test',
                'combination_labels' => [],
                'layout' => $layout,
                'seat_meta' => $seatMeta,
                'assignments' => $assignments,
                'promo_ids' => [],
                'selected_student_ids' => [],
                'available_students' => [],
            ];
        }

        $rooms[$roomId]['combination_labels'][] = $combination['matiere_label'];
        $rooms[$roomId]['promo_ids'][(int) $combination['promo_id']] = true;
        $promoExports[$combination['promo_id']] = [
            'id' => $combination['promo_id'],
            'label' => $combination['promo_label'],
        ];

        $students = placementStudentsForSelection((int) $combination['promo_id'], (int) $combination['group_id']);
        foreach ($students as $student) {
            $rooms[$roomId]['selected_student_ids'][$student['id']] = true;
        }
        shuffle($students);

        $seatKeys = placementSortedSeatKeys($rooms[$roomId]['assignments']);

        foreach ($seatKeys as $seatKey) {
            if ($rooms[$roomId]['assignments'][$seatKey] === null && !empty($students)) {
                $rooms[$roomId]['assignments'][$seatKey] = array_shift($students);
                $rooms[$roomId]['student_count']++;
            }
        }
    }

    foreach ($rooms as &$room) {
        $room['combination_labels'] = array_values(array_unique($room['combination_labels']));
        $availableStudents = [];
        foreach (array_keys($room['promo_ids']) as $promoId) {
            foreach (placementStudentsForPromotion((int) $promoId) as $student) {
                if (!isset($room['selected_student_ids'][$student['id']])) {
                    $availableStudents[] = $student;
                }
            }
        }
        $room['available_students'] = $availableStudents;
        unset($room['promo_ids'], $room['selected_student_ids']);
    }
    unset($room);

    return [
        'rooms' => array_values($rooms),
        'promo_exports' => array_values($promoExports),
    ];
}

function placementAddStudentsToRoom(array &$state, int $roomId, array $studentIds): array
{
    foreach ($state['placements']['rooms'] as &$room) {
        if ((int) $room['id'] !== $roomId) {
            continue;
        }

        $requestedIds = array_values(array_unique(array_filter(array_map('strval', $studentIds))));
        if (empty($requestedIds)) {
            return ['ok' => false, 'message' => 'Aucun étudiant sélectionné.'];
        }

        $availableById = [];
        foreach ($room['available_students'] ?? [] as $student) {
            $availableById[$student['id']] = $student;
        }

        $studentsToAdd = [];
        foreach ($requestedIds as $studentId) {
            if (isset($availableById[$studentId])) {
                $studentsToAdd[] = $availableById[$studentId];
            }
        }

        if (empty($studentsToAdd)) {
            return ['ok' => false, 'message' => 'Aucun étudiant disponible à ajouter.'];
        }

        $emptySeatKeys = array_values(array_filter(
            placementSortedSeatKeys($room['assignments']),
            static fn(string $seatKey): bool => $room['assignments'][$seatKey] === null
        ));

        if (count($studentsToAdd) > count($emptySeatKeys)) {
            return ['ok' => false, 'message' => 'Il n’y a pas assez de places libres dans ce plan.'];
        }

        $updatedSeats = [];
        foreach ($studentsToAdd as $index => $student) {
            $seatKey = $emptySeatKeys[$index];
            $room['assignments'][$seatKey] = $student;
            $room['student_count']++;
            $updatedSeats[] = [
                'key' => $seatKey,
                'name' => $student['display_name'],
                'empty' => false,
            ];
            unset($availableById[$student['id']]);
        }

        $room['available_students'] = array_values($availableById);

        return [
            'ok' => true,
            'room_id' => $roomId,
            'student_count' => $room['student_count'],
            'updated_seats' => $updatedSeats,
            'available_students' => array_map(
                static fn(array $student): array => [
                    'id' => $student['id'],
                    'display_name' => $student['display_name'],
                ],
                $room['available_students']
            ),
        ];
    }
    unset($room);

    return ['ok' => false, 'message' => 'Salle introuvable.'];
}

function placementSwapSeats(array &$state, int $roomId, string $seatA, string $seatB): array
{
    foreach ($state['placements']['rooms'] as &$room) {
        if ((int) $room['id'] !== $roomId) {
            continue;
        }

        if (!array_key_exists($seatA, $room['assignments']) || !array_key_exists($seatB, $room['assignments'])) {
            return ['ok' => false, 'message' => 'Places introuvables.'];
        }

        $studentA = $room['assignments'][$seatA];
        $studentB = $room['assignments'][$seatB];

        $room['assignments'][$seatA] = $studentB;
        $room['assignments'][$seatB] = $studentA;

        return [
            'ok' => true,
            'seatA' => [
                'name' => $studentB['display_name'] ?? '',
                'empty' => $studentB === null,
            ],
            'seatB' => [
                'name' => $studentA['display_name'] ?? '',
                'empty' => $studentA === null,
            ],
        ];
    }
    unset($room);

    return ['ok' => false, 'message' => 'Salle introuvable.'];
}

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader, [
    'cache' => false,
]);

$page = $_GET['p'] ?? 'home';



//j'ai mit un jeu de donnée temporaire pour tester l'affichage.
switch ($page) {
    case 'placement_add_combination':
        header('Content-Type: application/json; charset=utf-8');
        $data = placementBaseData();
        $state = &placementState();
        $state['exam'] = [
            'date' => $_POST['date_exam'] ?? '',
            'start_hour' => $_POST['start_hour'] ?? '08',
            'start_minute' => $_POST['start_minute'] ?? '00',
            'duration_hour' => $_POST['duration_hour'] ?? '02',
            'duration_minute' => $_POST['duration_minute'] ?? '00',
        ];
        $state['form'] = [
            'promo_id' => (string) ($_POST['promo_id'] ?? ''),
            'group_id' => (string) ($_POST['group_id'] ?? '0'),
            'matiere_id' => (string) ($_POST['matiere_id'] ?? ''),
            'salle_id' => (string) ($_POST['salle_id'] ?? ''),
        ];

        placementAddCombination($state, $data);

        echo json_encode([
            'ok' => $state['date_error'] === '' && $state['form_error'] === '',
            'date_error' => $state['date_error'],
            'form_error' => $state['form_error'],
            'form' => $state['form'],
            'combinations' => placementCombinationsForView($state['combinations'], $data['salles']),
        ]);
        exit;

    case 'placement_remove_combination':
        header('Content-Type: application/json; charset=utf-8');
        $data = placementBaseData();
        $state = &placementState();
        placementRemoveCombination($state, (int) ($_POST['combination_id'] ?? 0));
        echo json_encode([
            'ok' => true,
            'combinations' => placementCombinationsForView($state['combinations'], $data['salles']),
        ]);
        exit;

    case 'placement_swap':
        header('Content-Type: application/json; charset=utf-8');
        $state = &placementState();
        echo json_encode(
            placementSwapSeats(
                $state,
                (int) ($_POST['room_id'] ?? 0),
                (string) ($_POST['seat_a'] ?? ''),
                (string) ($_POST['seat_b'] ?? '')
            )
        );
        exit;

    case 'placement_add_students':
        header('Content-Type: application/json; charset=utf-8');
        $state = &placementState();
        echo json_encode(
            placementAddStudentsToRoom(
                $state,
                (int) ($_POST['room_id'] ?? 0),
                (array) ($_POST['student_ids'] ?? [])
            )
        );
        exit;

    case 'util_placement':
        $data = placementBaseData();
        $state = &placementState();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' && (int) ($state['current_stage'] ?? 1) === 1) {
            placementResetSetupForm($state);
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $state['exam'] = [
                'date' => $_POST['date_exam'] ?? '',
                'start_hour' => $_POST['start_hour'] ?? '08',
                'start_minute' => $_POST['start_minute'] ?? '00',
                'duration_hour' => $_POST['duration_hour'] ?? '02',
                'duration_minute' => $_POST['duration_minute'] ?? '00',
            ];
            $state['form'] = [
                'promo_id' => (string) ($_POST['promo_id'] ?? ''),
                'group_id' => (string) ($_POST['group_id'] ?? '0'),
                'matiere_id' => (string) ($_POST['matiere_id'] ?? ''),
                'salle_id' => (string) ($_POST['salle_id'] ?? ''),
            ];
            $state['date_error'] = '';
            $state['form_error'] = '';
            $action = $_POST['action'] ?? '';

            if (in_array($action, ['add_combination', 'generate_placements'], true)) {
                $state['date_error'] = placementDateError($state['exam']['date']);
            }

            if ($action === 'add_combination' && $state['date_error'] === '') {
                placementAddCombination($state, $data);
            }

            if ($action === 'remove_combination') {
                placementRemoveCombination($state, (int) ($_POST['combination_id'] ?? 0));
            }

            if ($action === 'generate_placements' && $state['date_error'] === '' && !empty($state['combinations'])) {
                $state['placements'] = placementBuildPlacements($state['combinations'], $data['salles']);
                $state['current_stage'] = 2;
            }

            if ($action === 'reroll_placements' && !empty($state['combinations'])) {
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

        echo $twig->render('Placement/placement.html.twig', [
            'current_stage' => $state['current_stage'],
            'warnings' => [],
            'exam' => $state['exam'],
            'form' => $state['form'],
            'date_error' => $state['date_error'],
            'form_error' => $state['form_error'],
            'today_iso' => date('Y-m-d'),
            'promotions' => $data['promotions'],
            'groupes' => $data['groupes'],
            'matieres' => $data['matieres'],
            'salles' => $data['salles'],
            'student_counts' => placementStudentCountMap($data['promotions'], $data['groupes']),
            'combinations' => placementCombinationsForView($state['combinations'], $data['salles']),
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
        echo $twig->render('Gestion/matiere.html.twig', [
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

        echo $twig->render('Gestion/enseignant.html.twig', [
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

        echo $twig->render('Gestion/enseignement.html.twig', [
            'enseignants' => $enseignants_test,
            'matieres' => $matieres_test,
            'enseignements' => $enseignements_test
        ]);
        break;
    case 'gest_salle':
        echo $twig->render('Gestion/salle.html.twig', []);
        break;

    case 'gest_dpt':
        $departements_test = [
            ['id_dpt' => 1, 'nom_dpt' => 'INFO'],
            ['id_dpt' => 2, 'nom_dpt' => 'SD']
        ];

        echo $twig->render('Gestion/departement.html.twig', [
            'departements' => $departements_test
        ]);
        break;


    case 'gest_bat':
        $batiments_test = [
            ['id_bat' => 1, 'nom_bat' => 'IUT de Metz', 'ad_bat' => 'Saulcy'],
            ['id_bat' => 2, 'nom_bat' => 'Lettres et Langues', 'ad_bat' => 'Saulcy']
        ];

        echo $twig->render('Gestion/batiment.html.twig', [
            'batiments' => $batiments_test
        ]);
        break;

        
    case 'gest_promo':
        echo $twig->render('Gestion/promotion.html.twig', []);
        break;
    default:
        echo $twig->render('index.html.twig', [
            'nom_projet' => 'Gestion de Placement',
            'etudiants' => ['Alice', 'Bob', 'Charlie'],
            'message' => 'Ton installation Twig est un succès !'
        ]);
        break;
}
