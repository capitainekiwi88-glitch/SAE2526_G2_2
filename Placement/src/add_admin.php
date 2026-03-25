<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Modele\DAO\EnseignantDAO;
use App\Modele\Entity\Enseignant;

try {
    $ensDao = new EnseignantDAO();

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