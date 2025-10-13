<?php

/**
 * API de récupération des données détaillées d'un véhicule Tesla
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

// Vérifier que l'ID du véhicule est fourni
if (!isset($_GET['vehicle_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'ID du véhicule manquant'
    ]);
    exit;
}

$vehicleId = $_GET['vehicle_id'];
$accessToken = $_SESSION['access_token'];
$apiUrl = $_ENV['TESLA_FLEET_API_URL'] ?? 'https://fleet-api.prd.eu.vn.cloud.tesla.com';

// Récupérer les données du véhicule
$ch = curl_init("{$apiUrl}/api/1/vehicles/{$vehicleId}/vehicle_data");
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
            'vehicle_data' => $data['response'],
            'timestamp' => time()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Format de réponse inattendu',
            'raw_response' => $response
        ]);
    }
} elseif ($httpCode === 408) {
    // Le véhicule est probablement endormi
    echo json_encode([
        'success' => false,
        'error' => 'Le véhicule est en veille. Veuillez réessayer dans quelques instants.',
        'code' => 'vehicle_asleep'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => "Erreur HTTP {$httpCode}",
        'details' => $response
    ]);
}
