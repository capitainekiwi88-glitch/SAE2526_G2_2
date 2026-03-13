<?php
require_once __DIR__ . '/../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader, [
    'cache' => false,
]);

echo $twig->render('index.html.twig', [
    'nom_projet' => 'Gestion de Placement',
    'etudiants' => ['Alice', 'Bob', 'Charlie'],
    'message' => 'Ton installation Twig est un succès !'
]);