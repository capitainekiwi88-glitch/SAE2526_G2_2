<?php
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'), '/');

// Normalize request path so deployments under a subdirectory still resolve correctly.
if ($scriptDir !== '' && str_starts_with($requestUri, $scriptDir)) {
    $localUri = '/' . ltrim(substr($requestUri, strlen($scriptDir)), '/');
} else {
    $localUri = $requestUri;
}

if ($localUri !== '/' && $localUri !== '/index.php' && !file_exists(__DIR__ . $localUri)) {
    http_response_code(404);
    require_once __DIR__ . '/../vendor/autoload.php';
    $twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates'), ['cache' => false]);
    echo $twig->render('404.html.twig', []);
    exit;
}
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Service/PlacementService.php';

use App\Modele\DAO\EnseignantDAO;

session_start();

$indexUrl = ($scriptDir !== '' ? $scriptDir : '') . '/index.php';

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader, [
    'cache' => false,
]);
$twig->addGlobal('base_path', $scriptDir !== '' ? $scriptDir : '');
$twig->addGlobal('username_test', $_SESSION['user_nom'] ?? null);
$twig->addGlobal('user_admin', $_SESSION['user_admin'] ?? false);

$page = $_GET['p'] ?? 'home';

// Auth guard: redirect to login if not authenticated
$publicPages = ['login', 'login_verify', 'logout'];
if (!in_array($page, $publicPages) && !isset($_SESSION['user_id'])) {
    header('Location: ' . $indexUrl . '?p=login');
    exit;
}

// --- Placement routes ---
$placementPages = [
    'placement_add_combination',
    'placement_remove_combination',
    'placement_swap',
    'placement_add_students',
    'placement_remove_student',
    'util_placement',
];
if (in_array($page, $placementPages)) {
    require __DIR__ . '/../routes/placement.php';
    exit;
}

// --- Gestion routes ---
$gestionPages = [
    'gest_mat',
    'gest_ens',
    'gest_ensmat',
    'gest_salle',
    'ajout_salle',
    'gest_dpt',
    'gest_bat',
    'gest_promo',
    'gest_groupe',
    'gest_etudiant',
];
if (in_array($page, $gestionPages)) {
    if (empty($_SESSION['user_admin'])) {
        header('Location: ' . $indexUrl . '?p=home');
        exit;
    }
    require __DIR__ . '/../routes/gestion.php';
    exit;
}

// --- Auth & general routes ---
switch ($page) {
    case 'login_verify':
        $login = $_POST['text'] ?? '';
        $password = $_POST['password'] ?? '';
        $ensDao = new EnseignantDAO();
        $enseignant = $ensDao->getEnseignantByLogin($login);
        if ($enseignant && $ensDao->verifyPassword($login, $password)) {
            $_SESSION['user_id'] = $enseignant->getIdEnseignant();
            $_SESSION['user_nom'] = $enseignant->getNom() . ' ' . $enseignant->getPrenom();
            $_SESSION['user_admin'] = (bool) $enseignant->getAdmin();
            header('Location: ' . $indexUrl . '?p=home');
            exit;
        } else {
            echo $twig->render('login.html.twig', [
                'error' => 'Identifiant ou mot de passe incorrect.',
                'nom_projet' => 'Gestion de Placement'
            ]);
        }
        break;
    case 'login':
        echo $twig->render('login.html.twig', ['nom_projet' => 'Gestion de Placement']);
        break;
    case 'logout':
        session_unset();
        session_destroy();
        header('Location: ' . $indexUrl . '?p=login');
        exit;
    case 'home':
        echo $twig->render('index.html.twig', [
            'nom_projet' => 'Gestion de Placement',
        ]);
        break;
    default:
        http_response_code(404);
        echo $twig->render('404.html.twig', []);
        break;
}