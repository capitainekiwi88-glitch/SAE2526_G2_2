<?php

use function App\Service\placementBaseData;
use function App\Service\placementState;
use function App\Service\placementAddCombination;
use function App\Service\placementRemoveCombination;
use function App\Service\placementSwapSeats;
use function App\Service\placementAddStudentsToRoom;
use function App\Service\placementRemoveStudentFromSeat;
use function App\Service\placementBuildPlacements;
use function App\Service\placementRerollPlacements;
use function App\Service\placementResetSetupForm;
use function App\Service\placementDateError;
use function App\Service\placementCombinationsForView;
use function App\Service\placementStudentCountMap;
use function App\Service\placementWarnings;
use function App\Service\placementSaveToDB;
use function App\Service\placementDeleteDevoir;

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
                // Delete previous devoir if going back and forth
                if (!empty($state['devoir_id'])) {
                    placementDeleteDevoir($state['devoir_id']);
                }
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
}
