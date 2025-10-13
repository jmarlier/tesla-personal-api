<?php

/**
 * Script pour obtenir un access token de l'API Tesla Fleet
 * 
 * Ce fichier peut être appelé via le web ou en ligne de commande
 */

require __DIR__ . '/../vendor/autoload.php';

use TeslaApp\TeslaAuth;
use Dotenv\Dotenv;

// Charger les variables d'environnement depuis .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// En-têtes pour JSON si appelé via le web
header('Content-Type: application/json; charset=utf-8');

try {
    // Créer une instance de TeslaAuth depuis les variables d'environnement
    $auth = TeslaAuth::fromEnv();

    // Obtenir l'access token
    $tokenData = $auth->getAccessToken();

    // Réponse en JSON
    echo json_encode([
        'success' => true,
        'data' => $tokenData,
        'message' => 'Access token obtenu avec succès',
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ], JSON_PRETTY_PRINT);
}
