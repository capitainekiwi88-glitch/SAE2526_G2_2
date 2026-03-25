<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Modele\DAO\EnseignantDAO;

session_start();
function placementDatabase(): ?\PDO
{
    static $pdo = false;
    if ($pdo !== false) {
        return $pdo;
    }

    $attempts = [
        'mysql:host=127.0.0.1;dbname=placement;charset=utf8',
        'mysql:host=127.0.0.1;dbname=infoplacement;charset=utf8',
        'mysql:host=localhost;dbname=placement;charset=utf8',
        'mysql:host=localhost;dbname=infoplacement;charset=utf8',
    ];

    foreach ($attempts as $dsn) {
        try {
            $pdo = new \PDO($dsn, 'root', '', [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);

            return $pdo;
        } catch (\PDOException $exception) {
            continue;
        }
    }

    $pdo = null;
    return null;
}

function placementGroupLabel(string $rawName): string
{
    $name = trim($rawName);
    if ($name === '') {
        return '--';
    }

    return stripos($name, 'groupe') === 0 ? $name : 'Groupe ' . $name;
}

function placementBaseData(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }

    $pdo = placementDatabase();
    if ($pdo === null) {
        return $data = [
            'promotions' => [],
            'groupes' => [],
            'matieres' => [],
            'salles' => [],
        ];
    }

    $promotions = [];
    $promotionLabels = [];
    $stmt = $pdo->query(
        'SELECT p.id_promo, p.nom_promo, p.annee, d.nom_dpt
         FROM promotion p
         JOIN departement d ON d.id_dpt = p.id_dpt
         ORDER BY p.nom_promo, p.annee'
    );
    foreach ($stmt->fetchAll() as $row) {
        $label = trim($row['nom_dpt'] . ' ' . $row['nom_promo'] . ' ' . $row['annee']);
        $promotions[] = [
            'id' => (int) $row['id_promo'],
            'label' => $label,
        ];
        $promotionLabels[(int) $row['id_promo']] = $label;
    }

    $groupes = [];
    $stmt = $pdo->query(
        'SELECT id_groupe, nom_groupe, id_promo, nb_etud
         FROM groupe
         ORDER BY id_promo, nom_groupe'
    );
    foreach ($stmt->fetchAll() as $row) {
        $groupes[] = [
            'id' => (int) $row['id_groupe'],
            'promo_id' => (int) $row['id_promo'],
            'label' => placementGroupLabel((string) $row['nom_groupe']),
            'student_count' => (int) $row['nb_etud'],
        ];
    }

    $matieres = [];
    $stmt = $pdo->query(
        'SELECT id_mat, nom_mat, id_promo
         FROM matiere
         ORDER BY id_promo, nom_mat'
    );
    foreach ($stmt->fetchAll() as $row) {
        $promoId = (int) $row['id_promo'];
        $matieres[] = [
            'id' => (int) $row['id_mat'],
            'promo_id' => $promoId,
            'nom' => (string) $row['nom_mat'],
            'promo_label' => $promotionLabels[$promoId] ?? '',
        ];
    }

    $salles = [];
    $stmt = $pdo->query(
        'SELECT s.id_salle, s.nom_salle, s.capacite, s.intercal, s.id_plan, b.nom_bat, p.donnee
         FROM salle s
         LEFT JOIN batiment b ON b.id_bat = s.id_bat
         LEFT JOIN plan p ON p.id_plan = s.id_plan
         ORDER BY s.nom_salle'
    );
    foreach ($stmt->fetchAll() as $row) {
        $salles[] = [
            'id' => (int) $row['id_salle'],
            'nom' => (string) $row['nom_salle'],
            'batiment' => (string) ($row['nom_bat'] ?? ''),
            'capacite' => (int) $row['capacite'],
            'intercal' => (int) ($row['intercal'] ?? 0),
            'id_plan' => isset($row['id_plan']) ? (int) $row['id_plan'] : 0,
            'plan_data' => (string) ($row['donnee'] ?? ''),
        ];
    }

    return $data = [
        'promotions' => $promotions,
        'groupes' => $groupes,
        'matieres' => $matieres,
        'salles' => $salles,
    ];
}

function placementWarnings(): array
{
    if (placementDatabase() !== null) {
        return [];
    }

    return ['La base de données de placement est actuellement indisponible.'];
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
            'date' => (new \DateTimeImmutable('today'))->format('Y-m-d'),
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

function placementStudentsForSelection(int $promoId, int $groupId): array
{
    $pdo = placementDatabase();
    if ($pdo === null) {
        return [];
    }

    if ($groupId === 0) {
        $stmt = $pdo->prepare(
            'SELECT e.id_etudiant, e.nom_etudiant, e.prenom_etudiant, e.id_groupe
             FROM etudiant e
             JOIN groupe g ON g.id_groupe = e.id_groupe
             WHERE g.id_promo = :promo
             ORDER BY e.nom_etudiant, e.prenom_etudiant'
        );
        $stmt->execute(['promo' => $promoId]);
    } else {
        $stmt = $pdo->prepare(
            'SELECT e.id_etudiant, e.nom_etudiant, e.prenom_etudiant, e.id_groupe
             FROM etudiant e
             WHERE e.id_groupe = :groupe
             ORDER BY e.nom_etudiant, e.prenom_etudiant'
        );
        $stmt->execute(['groupe' => $groupId]);
    }

    $students = [];
    foreach ($stmt->fetchAll() as $row) {
        $students[] = [
            'id' => (string) $row['id_etudiant'],
            'promo_id' => $promoId,
            'group_id' => (int) $row['id_groupe'],
            'last_name' => (string) $row['nom_etudiant'],
            'first_name' => (string) $row['prenom_etudiant'],
            'display_name' => $row['nom_etudiant'] . ' ' . $row['prenom_etudiant'],
        ];
    }

    return $students;
}

function placementStudentsForPromotion(int $promoId): array
{
    $pdo = placementDatabase();
    if ($pdo === null) {
        return [];
    }

    $stmt = $pdo->prepare(
        'SELECT e.id_etudiant, e.nom_etudiant, e.prenom_etudiant, e.id_groupe
         FROM etudiant e
         JOIN groupe g ON g.id_groupe = e.id_groupe
         WHERE g.id_promo = :promo
         ORDER BY e.nom_etudiant, e.prenom_etudiant'
    );
    $stmt->execute(['promo' => $promoId]);

    $students = [];
    foreach ($stmt->fetchAll() as $row) {
        $students[] = [
            'id' => (string) $row['id_etudiant'],
            'promo_id' => $promoId,
            'group_id' => (int) $row['id_groupe'],
            'last_name' => (string) $row['nom_etudiant'],
            'first_name' => (string) $row['prenom_etudiant'],
            'display_name' => $row['nom_etudiant'] . ' ' . $row['prenom_etudiant'],
        ];
    }

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

function placementLayoutFromSalle(array $salle): array
{
    $capacity = max(0, (int) ($salle['capacite'] ?? 0));
    $planData = (string) ($salle['plan_data'] ?? '');

    if ($planData === '') {
        return [];
    }

    $rawRows = array_values(array_filter(explode('-', $planData), static fn(string $row): bool => $row !== ''));
    if (empty($rawRows)) {
        return [];
    }

    $layout = [];
    $seatCount = 0;

    foreach ($rawRows as $rawRow) {
        $row = [];
        foreach (str_split($rawRow) as $cell) {
            if ($cell === '0') {
                $row[] = 'aisle';
                continue;
            }

            $row[] = $cell === '3' ? 'accessible' : 'seat';
            $seatCount++;
        }
        $layout[] = $row;
    }

    $excessSeats = max(0, $seatCount - $capacity);
    if ($excessSeats === 0) {
        return $layout;
    }

    // Le template numérote les rangées du bas vers le haut.
    // Si le plan contient plus de places dessinées que la capacité déclarée,
    // on neutralise donc les places du haut d'abord pour conserver la rangée 1 en bas.
    foreach ($layout as $rowIndex => $row) {
        foreach ($row as $colIndex => $cell) {
            if ($excessSeats === 0) {
                break 2;
            }

            if ($cell === 'seat' || $cell === 'accessible') {
                $layout[$rowIndex][$colIndex] = 'aisle';
                $excessSeats--;
            }
        }
    }

    return $layout;
}

function placementBuildPlacements(array $combinations, array $salles): array
{
    $rooms = [];
    $promoExports = [];

    foreach ($combinations as $combination) {
        $roomId = (int) $combination['salle_id'];
        $salle = placementFindById($salles, $roomId);
        if ($salle === null) {
            continue;
        }

        if (!isset($rooms[$roomId])) {
            $layout = placementLayoutFromSalle($salle);
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
                'supervisor' => null,
                'combination_labels' => [],
                'layout' => $layout,
                'seat_meta' => $seatMeta,
                'assignments' => $assignments,
                'promo_ids' => [],
                'devoir_student_ids' => [],
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
            $rooms[$roomId]['devoir_student_ids'][$student['id']] = true;
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
                if (!isset($room['devoir_student_ids'][$student['id']])) {
                    $availableStudents[] = $student;
                }
            }
        }
        $room['available_students'] = $availableStudents;
        unset($room['promo_ids']);
    }
    unset($room);

    return [
        'rooms' => array_values($rooms),
        'promo_exports' => array_values($promoExports),
    ];
}

function placementRerollPlacements(array $placements): array
{
    foreach ($placements['rooms'] as &$room) {
        $students = array_values(array_filter(
            $room['assignments'] ?? [],
            static fn($student): bool => $student !== null
        ));

        shuffle($students);

        $seatKeys = placementSortedSeatKeys($room['assignments'] ?? []);
        foreach ($seatKeys as $seatKey) {
            $room['assignments'][$seatKey] = null;
        }

        foreach ($seatKeys as $seatKey) {
            if (empty($students)) {
                break;
            }

            $room['assignments'][$seatKey] = array_shift($students);
        }

        $room['student_count'] = count(array_filter(
            $room['assignments'] ?? [],
            static fn($student): bool => $student !== null
        ));
    }
    unset($room);

    return $placements;
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

function placementRemoveStudentFromSeat(array &$state, int $roomId, string $seatKey): array
{
    foreach ($state['placements']['rooms'] as &$room) {
        if ((int) $room['id'] !== $roomId) {
            continue;
        }

        if (!array_key_exists($seatKey, $room['assignments'])) {
            return ['ok' => false, 'message' => 'Place introuvable.'];
        }

        $student = $room['assignments'][$seatKey];
        if ($student === null) {
            return ['ok' => true, 'room_id' => $roomId, 'student_count' => $room['student_count'], 'seat' => ['key' => $seatKey, 'name' => '', 'empty' => true], 'available_students' => array_map(static fn(array $item): array => ['id' => $item['id'], 'display_name' => $item['display_name']], $room['available_students'] ?? [])];
        }

        $room['assignments'][$seatKey] = null;
        $room['student_count'] = max(0, (int) $room['student_count'] - 1);

        if (!isset($room['devoir_student_ids'][$student['id']])) {
            $alreadyAvailable = false;
            foreach ($room['available_students'] as $availableStudent) {
                if (($availableStudent['id'] ?? '') === $student['id']) {
                    $alreadyAvailable = true;
                    break;
                }
            }

            if (!$alreadyAvailable) {
                $room['available_students'][] = $student;
                usort($room['available_students'], static function (array $a, array $b): int {
                    return strcmp($a['display_name'], $b['display_name']);
                });
            }
        }

        return [
            'ok' => true,
            'room_id' => $roomId,
            'student_count' => $room['student_count'],
            'seat' => [
                'key' => $seatKey,
                'name' => '',
                'empty' => true,
            ],
            'available_students' => array_map(
                static fn(array $item): array => [
                    'id' => $item['id'],
                    'display_name' => $item['display_name'],
                ],
                $room['available_students'] ?? []
            ),
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

    case 'placement_remove_student':
        header('Content-Type: application/json; charset=utf-8');
        $state = &placementState();
        echo json_encode(
            placementRemoveStudentFromSeat(
                $state,
                (int) ($_POST['room_id'] ?? 0),
                (string) ($_POST['seat_key'] ?? '')
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
            $hasSetupPayload = array_key_exists('date_exam', $_POST)
                || array_key_exists('promo_id', $_POST)
                || array_key_exists('group_id', $_POST)
                || array_key_exists('matiere_id', $_POST)
                || array_key_exists('salle_id', $_POST);

            if ($hasSetupPayload) {
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
            }
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
                if (!empty($state['placements']['rooms'])) {
                    $state['placements'] = placementRerollPlacements($state['placements']);
                } else {
                    $state['placements'] = placementBuildPlacements($state['combinations'], $data['salles']);
                }
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
            'warnings' => placementWarnings(),
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


    case 'login_verify':
        $login = $_POST['text'] ?? '';
        $password = $_POST['password'] ?? '';
        $ensDao = new EnseignantDAO(placementDatabase());
        $enseignant = $ensDao->getEnseignantByLogin($login);
        if ($enseignant && $ensDao->verifyPassword($login, $password)) {
            
            $_SESSION['user_id'] = $enseignant->getIdEnseignant();
            $_SESSION['user_nom'] = $enseignant->getNom() . ' ' . $enseignant->getPrenom();
            
            header('Location: index.php?p=util_placement');
            exit;
        } else {
            echo $twig->render('login.html.twig', [
                'error' => 'Identifiant ou mot de passe incorrect.',
                'nom_projet' => 'Gestion de Placement'
            ]);
        }
        break;
    default:
        echo $twig->render('login.html.twig', [
            'nom_projet' => 'Gestion de Placement'
        ]);
        break;
}
