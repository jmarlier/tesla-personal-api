<?php

/**
 * API d'envoi de commandes aux véhicules Tesla
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

// Récupérer les données POST
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['vehicle_id']) || !isset($input['command'])) {
    echo json_encode([
        'success' => false,
        'error' => 'ID du véhicule ou commande manquant(e)'
    ]);
    exit;
}

$vehicleId = $input['vehicle_id'];
$command = $input['command'];
$accessToken = $_SESSION['access_token'];
$apiUrl = $_ENV['TESLA_FLEET_API_URL'] ?? 'https://fleet-api.prd.eu.vn.cloud.tesla.com';

// Liste des commandes valides
$validCommands = [
    'auto_conditioning_start',
    'auto_conditioning_stop',
    'door_lock',
    'door_unlock',
    'charge_start',
    'charge_stop',
    'flash_lights',
    'honk_horn',
    'remote_start_drive',
    'set_temps',
    'set_charge_limit',
];

if (!in_array($command, $validCommands)) {
    echo json_encode([
        'success' => false,
        'error' => 'Commande non valide'
    ]);
    exit;
}

// Envoyer la commande
$ch = curl_init("{$apiUrl}/api/1/vehicles/{$vehicleId}/command/{$command}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer {$accessToken}",
    "Content-Type: application/json"
]);

// Certaines commandes nécessitent des paramètres
$postData = [];
if (isset($input['params'])) {
    $postData = $input['params'];
}

if (!empty($postData)) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
}

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

    if (isset($data['response']['result']) && $data['response']['result'] === true) {
        echo json_encode([
            'success' => true,
            'message' => "Commande '{$command}' exécutée avec succès",
            'data' => $data['response']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'La commande a échoué',
            'details' => $data
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => "Erreur HTTP {$httpCode}",
        'details' => $response
    ]);
}
