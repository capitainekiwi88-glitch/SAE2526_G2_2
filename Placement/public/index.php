<?php
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($requestUri !== '/' && $requestUri !== '/index.php' && !file_exists(__DIR__ . $requestUri)) {
    http_response_code(404);
    require_once __DIR__ . '/../vendor/autoload.php';
    $twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates'), ['cache' => false]);
    echo $twig->render('404.html.twig', []);
    exit;
}
require_once __DIR__ . '/../vendor/autoload.php';
use App\Modele\DAO\EnseignantDAO;

session_start();

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

    $pdo = \App\Modele\DAO\Connexion::getInstance();
    // Remove the null check since Connexion::getInstance() throws exception on failure

    $promoDao = new \App\Modele\DAO\PromotionDAO($pdo);
    $groupeDao = new \App\Modele\DAO\GroupeDAO($pdo);
    $matiereDao = new \App\Modele\DAO\MatiereDAO($pdo);
    $salleDao = new \App\Modele\DAO\SalleDAO($pdo);

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

    // Get groupes using DAO
    $groupes = [];
    $groupeEntities = $groupeDao->findAll();
    foreach ($groupeEntities as $groupe) {
        $groupes[] = [
            'id' => $groupe->getIdGroupe(),
            'promo_id' => $groupe->getIdPromo(),
            'label' => placementGroupLabel($groupe->getNomGroupe()),
            'student_count' => $groupe->getNbEtudiant(),
        ];
    }

    // Get matieres using DAO
    $matieres = [];
    $matiereEntities = $matiereDao->findAll();
    foreach ($matiereEntities as $matiere) {
        $promoId = $matiere->getIdPromo();
        $matieres[] = [
            'id' => $matiere->getIdMatiere(),
            'promo_id' => $promoId,
            'nom' => $matiere->getNomMatiere(),
            'promo_label' => $promotionLabels[$promoId] ?? '',
        ];
    }

    // Get salles (keep complex query for now)
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
    try {
        // Test the DAO connection (university server)
        $pdo = \App\Modele\DAO\Connexion::getInstance();
        // Try a simple query to verify connection
        $stmt = $pdo->query("SELECT 1");
        return [];
    } catch (\Exception $e) {
        return ['La base de données de placement est actuellement indisponible.'];
    }
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

function placementSaveToDB(array &$state): int
{
    $pdo = \App\Modele\DAO\Connexion::getInstance();
    $exam = $state['exam'];
    $combinations = $state['combinations'];
    $rooms = $state['placements']['rooms'];

    $matiereLabels = array_unique(array_column($combinations, 'matiere_label'));
    $nomDevoir = implode(' / ', $matiereLabels);

    $dateDevoir = $exam['date'];
    $heureDevoir = ($exam['start_hour'] ?? '08') . ':' . ($exam['start_minute'] ?? '00') . ':00';
    $dureeDevoir = ($exam['duration_hour'] ?? '02') . ':' . ($exam['duration_minute'] ?? '00') . ':00';

    $stmt = $pdo->prepare(
        "INSERT INTO devoir (nom_devoir, date_devoir, heure_devoir, duree_devoir)
         VALUES (:nom, :date, :heure, :duree)"
    );
    $stmt->execute([
        ':nom' => $nomDevoir,
        ':date' => $dateDevoir,
        ':heure' => $heureDevoir,
        ':duree' => $dureeDevoir,
    ]);
    $idDevoir = (int) $pdo->lastInsertId();

    $dgDao = new \App\Modele\DAO\DevoirGroupeDAO();
    $dpDao = new \App\Modele\DAO\DevoirPromoDAO();

    foreach ($combinations as $c) {
        $groupId = (int) $c['group_id'];
        $salleId = (int) $c['salle_id'];
        $matiereId = (int) $c['matiere_id'];

        if ($groupId > 0) {
            $dg = new \App\Modele\Entity\DevoirGroupe($salleId, $idDevoir, $groupId, $matiereId);
            $dgDao->insert($dg);
        } else {
            $dp = new \App\Modele\Entity\DevoirPromo($salleId, $idDevoir, (int) $c['promo_id'], $matiereId);
            $dpDao->insert($dp);
        }
    }

    $placementDao = new \App\Modele\DAO\PlacementDAO();
    foreach ($rooms as $room) {
        $salleId = (int) $room['id'];
        foreach ($room['assignments'] as $seatKey => $student) {
            if ($student === null)
                continue;
            $parts = explode('-', $seatKey);
            $placeX = (int) $parts[0];
            $placeY = (int) $parts[1];
            $placementDao->insert((int) $student['id'], $idDevoir, $salleId, $placeX, $placeY);
        }
    }

    return $idDevoir;
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
    $pdo = \App\Modele\DAO\Connexion::getInstance();

    $etudiantDao = new \App\Modele\DAO\EtudiantDAO($pdo);
    return $etudiantDao->getStudentsForSelection($promoId, $groupId);
}

function placementStudentsForPromotion(int $promoId): array
{
    $pdo = \App\Modele\DAO\Connexion::getInstance();

    $etudiantDao = new \App\Modele\DAO\EtudiantDAO($pdo);
    return $etudiantDao->getStudentsForPromotion($promoId);
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
                $state['devoir_id'] = placementSaveToDB($state);
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
            'devoir_id' => $state['devoir_id'] ?? null,
        ]);
        break;
    // -------------------------------------------------------------------
    //Ici on rentre dans les différentes pages de la partie gestion

    //Page Matière dans la partie Gestion
    case 'gest_mat':
        $pdo = \App\Modele\DAO\Connexion::getInstance();
        $matiereDAO = new \App\Modele\DAO\MatiereDAO($pdo);

        if (isset($_GET['suppr'])) {
            $matASupprimer = new \App\Modele\Entity\Matiere((int) $_GET['suppr'], "temp", 1);
            $matiereDAO->delete($matASupprimer);
            header("Location: index.php?p=gest_mat");
            exit;
        }

        if (isset($_POST['ajouter'])) {
            $idPromo = (int) $_POST['prom'];
            if ($idPromo > 0) {
                $nouvelleMat = new \App\Modele\Entity\Matiere(0, $_POST['nom_mat'], $idPromo);
                $matiereDAO->insert($nouvelleMat);
            }
            header("Location: index.php?p=gest_mat");
            exit;
        }

        if (isset($_POST['validemodif'])) {
            $idPromo = (int) $_POST['n_promo_mat'];
            if ($idPromo > 0) {
                $modifMat = new \App\Modele\Entity\Matiere((int) $_POST['id_mat'], $_POST['n_nom_mat'], $idPromo);
                $matiereDAO->update($modifMat);
            }
            header("Location: index.php?p=gest_mat");
            exit;
        }

        $stmtPromo = $pdo->query("SELECT p.id_promo, p.nom_promo, p.annee, d.nom_dpt FROM promotion p LEFT JOIN departement d ON p.id_dpt = d.id_dpt ORDER BY p.nom_promo, p.annee");
        $promotions_db = $stmtPromo->fetchAll(PDO::FETCH_ASSOC);


        echo $twig->render('Gestion/matiere.html.twig', [
            'page' => $page,
            'matieres' => $matiereDAO->findAllWithPromo(),
            'promotions' => $promotions_db
        ]);
        break;

    //Page Enseignant dans la partie Gestion
    case 'gest_ens':
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
                $nouvelEns = new \App\Modele\Entity\Enseignant(0, $nom, $prenom, $sexe, $login, $admin);
                $ensDAO->insert($nouvelEns, $login);
            }
            header("Location: index.php?p=gest_ens");
            exit;
        }

        if (isset($_POST['validemodif'])) {
            $id_ens = (int) $_POST['id_ens'];
            $nom = trim($_POST['n_nom'] ?? '');
            $prenom = trim($_POST['n_prenom'] ?? '');
            $sexe = $_POST['n_sexe'] ?? 'M';
            $login = trim($_POST['n_login'] ?? '');
            $admin = isset($_POST['n_admin']) ? (bool) $_POST['n_admin'] : false;

            if ($id_ens > 0 && !empty($nom) && !empty($prenom) && !empty($login)) {
                $modifEns = new \App\Modele\Entity\Enseignant($id_ens, $nom, $prenom, $sexe, $login, $admin);
                $ensDAO->update($modifEns);
            }
            header("Location: index.php?p=gest_ens");
            exit;
        }

        $enseignants_db = $ensDAO->findAll();

        echo $twig->render('Gestion/enseignant.html.twig', [
            'page' => $page,
            'enseignants' => $enseignants_db
        ]);
        break;

    //Page Enseignement dans la partie Gestion
    case 'gest_ensmat':
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
                $nouvelEnseignement = new \App\Modele\Entity\Enseignement($idEns, $idMat);
                $enseigneDAO->insert($nouvelEnseignement);
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
                $modifEnseignement = new \App\Modele\Entity\Enseignement($newIdEns, $newIdMat);
                $enseigneDAO->update($oldIdEns, $oldIdMat, $modifEnseignement);
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
        break;

    //page Salle dans la partie Gestion
    case 'gest_salle':
        $pdo = \App\Modele\DAO\Connexion::getInstance();
        $salleDAO = new \App\Modele\DAO\SalleDAO($pdo);
        if (isset($_GET['suppr'])) {
            $salleDAO->deleteById((int) $_GET['suppr']);
            header("Location: index.php?p=gest_salle");
            exit;
        }
        if (isset($_POST['validemodif'])) {
            $idSalle = (int) $_POST['id_salle'];
            $nomSalle = trim($_POST['n_nom_salle'] ?? '');
            $capacite = (int) $_POST['n_capacite'];
            $etage = (int) $_POST['n_etage'];
            $idBat = (int) $_POST['n_id_bat'];
            $idDpt = (int) $_POST['n_id_dpt'];

            if ($idSalle > 0 && !empty($nomSalle) && $capacite > 0 && $idBat > 0 && $idDpt > 0) {
                $oldSalle = $salleDAO->getById($idSalle);
                if ($oldSalle) {
                    $modifSalle = new \App\Modele\Entity\Salle($idSalle, $nomSalle, $capacite, $etage, $oldSalle->getIdPlan(), $idDpt, $idBat);
                    $salleDAO->update($modifSalle);
                }
            }
            header("Location: index.php?p=gest_salle");
            exit;
        }
        $stmtBat = $pdo->query("SELECT id_bat AS idBatiment, nom_bat AS nom FROM batiment ORDER BY nom_bat");
        $batiments_db = $stmtBat->fetchAll(PDO::FETCH_ASSOC);

        $stmtDpt = $pdo->query("SELECT id_dpt AS idDpt, nom_dpt AS nom FROM departement ORDER BY nom_dpt");
        $departements_db = $stmtDpt->fetchAll(PDO::FETCH_ASSOC);

        echo $twig->render('Gestion/salle.html.twig', [
            'page' => $page,
            'salles' => $salleDAO->findAllWithDetails(),
            'batiments' => $batiments_db,
            'departements' => $departements_db
        ]);
        break;

    //Page Salle(création) dans la partie Gestion
    case 'ajout_salle':
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
        break;

    //Page Département dans la partie Gestion
    case 'gest_dpt':
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

        $departements_db = $dptDAO->findAll();

        echo $twig->render('Gestion/departement.html.twig', [
            'page' => $page,
            'departements' => $departements_db
        ]);
        break;

    //Page Batiment dans la partie Gestion
    case 'gest_bat':
        $batDAO = new \App\Modele\DAO\BatimentDAO();

        if (isset($_GET['suppr'])) {
            $batDAO->deleteById((int) $_GET['suppr']);
            header("Location: index.php?p=gest_bat");
            exit;
        }

        if (isset($_POST['ajouter'])) {
            $nomBat = trim($_POST['nom_bat'] ?? '');
            $adBat = trim($_POST['ad_bat'] ?? '');

            if (!empty($nomBat) && !empty($adBat)) {
                $nouveauBat = new \App\Modele\Entity\Batiment(0, $nomBat, $adBat);
                $batDAO->insert($nouveauBat);
            }
            header("Location: index.php?p=gest_bat");
            exit;
        }

        if (isset($_POST['validemodif'])) {
            $idBat = (int) $_POST['id_bat'];
            $nomBat = trim($_POST['n_nom_bat'] ?? '');
            $adBat = trim($_POST['n_ad_bat'] ?? '');

            if ($idBat > 0 && !empty($nomBat) && !empty($adBat)) {
                $modifBat = new \App\Modele\Entity\Batiment($idBat, $nomBat, $adBat);
                $batDAO->update($modifBat);
            }
            header("Location: index.php?p=gest_bat");
            exit;
        }
        $batiments_db = $batDAO->findAll();

        echo $twig->render('Gestion/batiment.html.twig', [
            'page' => $page,
            'batiments' => $batiments_db
        ]);
        break;

    //Page Promotion dans la partie Gestion
    case 'gest_promo':
        $pdo = \App\Modele\DAO\Connexion::getInstance();
        $promoDAO = new \App\Modele\DAO\PromotionDAO($pdo);
        $groupeDAO = new \App\Modele\DAO\GroupeDAO($pdo);

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
        $stmtPromo = $pdo->query($sql);
        $promotions_db = $stmtPromo->fetchAll(PDO::FETCH_ASSOC);

        echo $twig->render('Gestion/promotion.html.twig', [
            'page' => $page,
            'departements' => $departements_db,
            'promotions' => $promotions_db
        ]);
        break;


    //Page Groupe qui est dans Promotion qui est dans Gestion
    case 'gest_groupe':
        $pdo = \App\Modele\DAO\Connexion::getInstance();
        $groupeDAO = new \App\Modele\DAO\GroupeDAO($pdo);

        $idPromo = (int) ($_GET['id_promo'] ?? $_POST['id_promo'] ?? 0);

        if ($idPromo === 0) {
            header("Location: index.php?p=gest_promo");
            exit;
        }

        if (isset($_GET['suppr'])) {
            $groupe = $groupeDAO->getById((int) $_GET['suppr']);
            if ($groupe) {
                $groupeDAO->delete($groupe);
            }
            header("Location: index.php?p=gest_groupe&id_promo=" . $idPromo);
            exit;
        }

        if (isset($_POST['ajouter'])) {
            $nom = trim($_POST['nom_groupe'] ?? '');
            if (!empty($nom)) {
                $nouveauGroupe = new \App\Modele\Entity\Groupe(0, $nom, 0, $idPromo);
                $groupeDAO->insert($nouveauGroupe);
            }
            header("Location: index.php?p=gest_groupe&id_promo=" . $idPromo);
            exit;
        }

        if (isset($_POST['validemodif'])) {
            $idG = (int) $_POST['id_groupe'];
            $nom = trim($_POST['n_nom_groupe'] ?? '');
            if ($idG > 0 && !empty($nom)) {
                $g = $groupeDAO->getById($idG);
                if ($g) {
                    $g->setNomGroupe($nom);
                    $groupeDAO->update($g);
                }
            }
            header("Location: index.php?p=gest_groupe&id_promo=" . $idPromo);
            exit;
        }

        $stmtP = $pdo->prepare("SELECT p.*, d.nom_dpt FROM promotion p LEFT JOIN departement d ON p.id_dpt = d.id_dpt WHERE p.id_promo = ?");
        $stmtP->execute([$idPromo]);
        $promoDetails = $stmtP->fetch(PDO::FETCH_ASSOC);

        $stmtG = $pdo->prepare("
            SELECT g.id_groupe, g.nom_groupe, g.id_promo, COUNT(e.id_etudiant) as nb_etud 
            FROM groupe g 
            LEFT JOIN etudiant e ON g.id_groupe = e.id_groupe 
            WHERE g.id_promo = ? 
            GROUP BY g.id_groupe, g.nom_groupe, g.id_promo 
            ORDER BY g.nom_groupe
        ");
        $stmtG->execute([$idPromo]);
        $groupes = $stmtG->fetchAll(PDO::FETCH_ASSOC);

        echo $twig->render('Gestion/groupe.html.twig', [
            'page' => 'gest_promo',
            'promotion' => $promoDetails,
            'groupes' => $groupes
        ]);
        break;

    //Page Etudiant qui est dans Promotion qui est dans Gestion
    case 'gest_etudiant':
        $pdo = \App\Modele\DAO\Connexion::getInstance();
        $etudiantDAO = new \App\Modele\DAO\EtudiantDAO($pdo);

        $idPromo = (int) ($_GET['id_promo'] ?? $_POST['id_promo'] ?? 0);

        if ($idPromo === 0) {
            header("Location: index.php?p=gest_promo");
            exit;
        }

        if (isset($_GET['suppr'])) {
            $etud = $etudiantDAO->getById((int) $_GET['suppr']);
            if ($etud) {
                $etudiantDAO->delete($etud);
            }
            header("Location: index.php?p=gest_etudiant&id_promo=" . $idPromo);
            exit;
        }

        if (isset($_POST['ajouter'])) {
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $tt = isset($_POST['tiers_temps']) ? 1 : 0;
            $pmr = isset($_POST['mob_reduite']) ? 1 : 0;
            $idG = (int) $_POST['id_groupe'];

            if (!empty($nom) && !empty($prenom) && $idG > 0) {
                $stmt = $pdo->prepare("INSERT INTO etudiant (nom_etudiant, prenom_etudiant, tiers_temps, mob_reduite, id_groupe) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([strtoupper($nom), $prenom, $tt, $pmr, $idG]);
            }
            header("Location: index.php?p=gest_etudiant&id_promo=" . $idPromo);
            exit;
        }

        if (isset($_POST['validemodif'])) {
            $idE = (int) $_POST['id_etudiant'];
            $nom = trim($_POST['n_nom'] ?? '');
            $prenom = trim($_POST['n_prenom'] ?? '');
            $tt = isset($_POST['n_tiers_temps']) ? 1 : 0;
            $pmr = isset($_POST['n_mob_reduite']) ? 1 : 0;
            $idG = (int) $_POST['n_id_groupe'];

            if ($idE > 0 && !empty($nom)) {
                $stmt = $pdo->prepare("UPDATE etudiant SET nom_etudiant=?, prenom_etudiant=?, tiers_temps=?, mob_reduite=?, id_groupe=? WHERE id_etudiant=?");
                $stmt->execute([strtoupper($nom), $prenom, $tt, $pmr, $idG, $idE]);
            }
            header("Location: index.php?p=gest_etudiant&id_promo=" . $idPromo);
            exit;
        }

        $stmtP = $pdo->prepare("SELECT p.*, d.nom_dpt FROM promotion p LEFT JOIN departement d ON p.id_dpt = d.id_dpt WHERE p.id_promo = ?");
        $stmtP->execute([$idPromo]);
        $promoDetails = $stmtP->fetch(PDO::FETCH_ASSOC);

        $stmtG = $pdo->prepare("SELECT * FROM groupe WHERE id_promo = ? ORDER BY nom_groupe");
        $stmtG->execute([$idPromo]);
        $groupes = $stmtG->fetchAll(PDO::FETCH_ASSOC);

        $idGroupeFilter = (int) ($_GET['id_groupe'] ?? 0);

        if ($idGroupeFilter > 0) {
            $stmtE = $pdo->prepare("
                SELECT e.*, g.nom_groupe 
                FROM etudiant e 
                JOIN groupe g ON e.id_groupe = g.id_groupe 
                WHERE g.id_promo = ? AND e.id_groupe = ?
                ORDER BY e.nom_etudiant, e.prenom_etudiant
            ");
            $stmtE->execute([$idPromo, $idGroupeFilter]);
        } else {
            $stmtE = $pdo->prepare("
                SELECT e.*, g.nom_groupe 
                FROM etudiant e 
                JOIN groupe g ON e.id_groupe = g.id_groupe 
                WHERE g.id_promo = ?
                ORDER BY g.nom_groupe ASC, e.nom_etudiant ASC, e.prenom_etudiant ASC
            ");
            $stmtE->execute([$idPromo]);
        }
        $etudiants = $stmtE->fetchAll(PDO::FETCH_ASSOC);

        echo $twig->render('Gestion/etudiant.html.twig', [
            'page' => 'gest_promo',
            'promotion' => $promoDetails,
            'groupes' => $groupes,
            'etudiants' => $etudiants
        ]);
        break;
    // -------------------------------------------------------------------


    case 'login_verify':
        $login = $_POST['text'] ?? '';
        $password = $_POST['password'] ?? '';
        $ensDao = new EnseignantDAO();
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
    case 'login':
        echo $twig->render('login.html.twig');
        break;
    case 'home':
        echo $twig->render('index.html.twig', [
            'nom_projet' => 'Gestion de Placement',
            'etudiants' => ['Alice', 'Bob', 'Charlie'],
            'message' => 'Ton installation Twig est un succès !'
        ]);
        break;
    default:
        http_response_code(404);
        echo $twig->render('404.html.twig', []);
        break;
}
