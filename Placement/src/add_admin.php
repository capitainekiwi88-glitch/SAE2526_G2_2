<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Modele\DAO\EnseignantDAO;
use App\Modele\Entity\Enseignant;

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

try {
    $pdo = placementDatabase();
    if (!$pdo) {
        echo "Connexion locale échouée, tentative avec le serveur universitaire...\n";
        $pdo = null; // Will use Connexion::getInstance()
    }

    $ensDao = new EnseignantDAO($pdo);

    // Créer un utilisateur admin
    $admin = new Enseignant(0, 'Admin', 'System', 'M', 'admin', true);
    $password = 'fatih'; // Changez ce mot de passe

    $success = $ensDao->insert($admin, $password);

    if ($success) {
        echo "Utilisateur admin ajouté avec succès !\n";
        echo "Login: admin\n";
        echo "Mot de passe: $password\n";
    } else {
        echo "Erreur lors de l'ajout de l'utilisateur admin.\n";
    }

} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>