<?php
require_once("ptrim.php");


try {
    // Connexion à la base de données avec PDO
    $dsn = 'mysql:host=VOTRE_SERVEUR;dbname=VOTRE_DB;charset=utf8';
    $username = 'VOTRE USER';
    $password = 'VOTRE_PASSWORD';
    // Options pour gérer les erreurs et s'assurer de la compatibilité UTF-8
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Pour lever une exception en cas d'erreur
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Pour obtenir des résultats sous forme de tableau associatif
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"  // Pour s'assurer que les requêtes sont en UTF-8
    ];

    // Création de la connexion PDO
    $pdo = new PDO($dsn, $username, $password, $options);
    
 //echo "Connexion réussie !";
} catch (PDOException $e) {
    // En cas d'erreur, on attrape l'exception et affiche le message d'erreur
    die("Erreur de connexion : " . $e->getMessage());
}



	

