<?php

/**
 * API d'authentification Tesla
 * Gère la validation et le stockage du token d'accès
 */

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

session_start();
header('Content-Type: application/json');

// Récupérer les données POST
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['access_token'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Access token manquant'
    ]);
    exit;
}

$accessToken = trim($input['access_token']);

// Valider le token en faisant un appel test à l'API Tesla
$apiUrl = $_ENV['TESLA_FLEET_API_URL'] ?? 'https://fleet-api.prd.eu.vn.cloud.tesla.com';

$ch = curl_init("{$apiUrl}/api/1/vehicles");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer {$accessToken}",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    // Token valide - le stocker en session
    $_SESSION['access_token'] = $accessToken;
    $_SESSION['authenticated'] = true;

    echo json_encode([
        'success' => true,
        'message' => 'Authentification réussie',
        'token' => substr($accessToken, 0, 20) . '...'
    ]);
} else {
    // Token invalide
    echo json_encode([
        'success' => false,
        'error' => "Token invalide (HTTP {$httpCode})",
        'details' => $response
    ]);
}
