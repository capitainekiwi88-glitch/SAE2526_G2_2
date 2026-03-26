<?php
namespace App\Service;

use App\Modele\DAO\Connexion;
use App\Modele\DAO\EtudiantDAO;
use App\Modele\DAO\GroupeDAO;
use App\Modele\DAO\MatiereDAO;
use App\Modele\DAO\PlacementDAO;
use App\Modele\DAO\PromotionDAO;
use App\Modele\DAO\SalleDAO;
use App\Modele\DAO\DevoirDAO;
use App\Modele\DAO\DevoirGroupeDAO;
use App\Modele\DAO\DevoirPromoDAO;
use App\Modele\Entity\DevoirGroupe;
use App\Modele\Entity\DevoirPromo;

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

    $pdo = Connexion::getInstance();

    $promoDao = new PromotionDAO($pdo);
    $groupeDao = new GroupeDAO($pdo);
    $matiereDao = new MatiereDAO($pdo);
    $salleDao = new SalleDAO($pdo);

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
    $groupeEntities = $groupeDao->findAll();
    foreach ($groupeEntities as $groupe) {
        $groupes[] = [
            'id' => $groupe->getIdGroupe(),
            'promo_id' => $groupe->getIdPromo(),
            'label' => placementGroupLabel($groupe->getNomGroupe()),
            'student_count' => $groupe->getNbEtudiant(),
        ];
    }

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
        $pdo = Connexion::getInstance();
        $stmt = $pdo->query("SELECT 1");
        return [];
    } catch (\Exception $e) {
        return ['La base de données de placement est actuellement indisponible.'];
    }
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
        return 'La date du devoir ne peut pas être antérieure à aujourd\'hui.';
    }

    return '';
}

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
    $pdo = Connexion::getInstance();
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

    $dgDao = new DevoirGroupeDAO();
    $dpDao = new DevoirPromoDAO();

    foreach ($combinations as $c) {
        $groupId = (int) $c['group_id'];
        $salleId = (int) $c['salle_id'];
        $matiereId = (int) $c['matiere_id'];

        if ($groupId > 0) {
            $dg = new DevoirGroupe($salleId, $idDevoir, $groupId, $matiereId);
            $dgDao->insert($dg);
        } else {
            $dp = new DevoirPromo($salleId, $idDevoir, (int) $c['promo_id'], $matiereId);
            $dpDao->insert($dp);
        }
    }

    $placementDao = new PlacementDAO();
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

function placementDeleteDevoir(int $idDevoir): void
{
    $placementDao = new PlacementDAO();
    $placementDao->deleteByDevoir($idDevoir);

    $dgDao = new DevoirGroupeDAO();
    $dgDao->deleteByDevoir($idDevoir);

    $dpDao = new DevoirPromoDAO();
    $dpDao->deleteByDevoir($idDevoir);

    $devoirDao = new DevoirDAO();
    $devoir = $devoirDao->getById($idDevoir);
    if ($devoir) {
        $devoirDao->delete($devoir);
    }
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
    $pdo = Connexion::getInstance();
    $etudiantDao = new EtudiantDAO($pdo);
    return $etudiantDao->getStudentsForSelection($promoId, $groupId);
}

function placementStudentsForPromotion(int $promoId): array
{
    $pdo = Connexion::getInstance();
    $etudiantDao = new EtudiantDAO($pdo);
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
            if ($cell === '0' || $cell === '3') {
                $row[] = 'aisle';
                continue;
            }

            $row[] = $cell === '2' ? 'accessible' : 'seat';
            $seatCount++;
        }
        $layout[] = $row;
    }

    $excessSeats = max(0, $seatCount - $capacity);
    if ($excessSeats === 0) {
        return $layout;
    }

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
                    if ($cell === 'seat' || $cell === 'accessible') {
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

    // Collect all assigned student IDs across all rooms
    $allAssignedIds = [];
    foreach ($rooms as $room) {
        foreach ($room['assignments'] as $seat) {
            if ($seat !== null) {
                $allAssignedIds[$seat['id']] = true;
            }
        }
    }

    foreach ($rooms as &$room) {
        $room['combination_labels'] = array_values(array_unique($room['combination_labels']));
        $availableStudents = [];
        foreach (array_keys($room['promo_ids']) as $promoId) {
            foreach (placementStudentsForPromotion((int) $promoId) as $student) {
                if (!isset($allAssignedIds[$student['id']])) {
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

        // Collect all student IDs already placed in ANY room
        $placedStudentIds = [];
        foreach ($state['placements']['rooms'] as $otherRoom) {
            foreach ($otherRoom['assignments'] as $seat) {
                if ($seat !== null) {
                    $placedStudentIds[$seat['id']] = true;
                }
            }
        }

        $availableById = [];
        foreach ($room['available_students'] ?? [] as $student) {
            if (!isset($placedStudentIds[$student['id']])) {
                $availableById[$student['id']] = $student;
            }
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
            return ['ok' => false, 'message' => 'Il n\'y a pas assez de places libres dans ce plan.'];
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
