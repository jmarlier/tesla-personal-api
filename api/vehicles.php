<?php

/**
 * API de récupération de la liste des véhicules Tesla
 */

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

session_start();
header('Content-Type: application/json');

// Vérifier l'authentification
if (!isset($_SESSION['access_token'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Non authentifié. Veuillez d\'abord vous connecter.'
    ]);
    exit;
}

$accessToken = $_SESSION['access_token'];
$apiUrl = $_ENV['TESLA_FLEET_API_URL'] ?? 'https://fleet-api.prd.eu.vn.cloud.tesla.com';

// Récupérer la liste des véhicules
$ch = curl_init("{$apiUrl}/api/1/vehicles");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer {$accessToken}",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode([
        'success' => false,
        'error' => 'Erreur de connexion : ' . $curlError
    ]);
    exit;
}

if ($httpCode === 200) {
    $data = json_decode($response, true);

    if (isset($data['response'])) {
        echo json_encode([
            'success' => true,
            'vehicles' => $data['response'],
            'count' => count($data['response'])
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Format de réponse inattendu',
            'raw_response' => $response
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => "Erreur HTTP {$httpCode}",
        'details' => $response
    ]);
}
