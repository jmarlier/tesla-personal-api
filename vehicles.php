<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 🔐 Charger les tokens
$tokensPath = __DIR__ . '/tokens.json';
if (!file_exists($tokensPath)) {
    exit('❌ Fichier de token introuvable. Lancez d’abord l’authentification.');
}

$tokens = json_decode(file_get_contents($tokensPath), true);
$accessToken = $tokens['access_token'] ?? null;
$apiBase = $tokens['fleet_api_base_url'] ?? 'https://fleet-api.teslamotors.com';

if (!$accessToken || !$apiBase) {
    exit('❌ Token ou base URL manquante.');
}

// 🔍 Étape 1 : Récupérer le véhicule
$vehicleList = file_get_contents(
    $apiBase . '/api/1/vehicles',
    false,
    stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Authorization: Bearer $accessToken\r\n",
            'ignore_errors' => true
        ]
    ])
);

$vehicleData = json_decode($vehicleList, true);
$vehicles = $vehicleData['response'] ?? [];

if (empty($vehicles)) {
    exit('🚫 Aucun véhicule trouvé.');
}

$vehicleId = $vehicles[0]['id'];

// 🔍 Étape 2 : Récupérer toutes les données du véhicule
$vehicleDetails = file_get_contents(
    $apiBase . "/api/1/vehicles/{$vehicleId}/vehicle_data",
    false,
    stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Authorization: Bearer $accessToken\r\n",
            'ignore_errors' => true
        ]
    ])
);

$data = json_decode($vehicleDetails, true);
$response = $data['response'] ?? null;

if (!$response) {
    exit('❌ Impossible de récupérer les données du véhicule.');
}

// 🔧 Affichage des infos
echo "<h1>🚘 Informations Tesla</h1>";

echo "<h2>📋 Général</h2><ul>";
echo "<li>Nom : " . htmlspecialchars($response['display_name']) . "</li>";
echo "<li>VIN : " . htmlspecialchars($response['vin']) . "</li>";
echo "<li>État : " . htmlspecialchars($response['state']) . "</li>";
echo "</ul>";

echo "<h2>🔋 Batterie</h2><ul>";
echo "<li>Niveau : " . $response['charge_state']['battery_level'] . "%</li>";
echo "<li>Autonomie : " . $response['charge_state']['battery_range'] . " km</li>";
echo "<li>Charge : " . $response['charge_state']['charging_state'] . "</li>";
echo "</ul>";

echo "<h2>🌡️ Climatisation</h2><ul>";
echo "<li>Temp. intérieure : " . $response['climate_state']['inside_temp'] . "°C</li>";
echo "<li>Temp. extérieure : " . $response['climate_state']['outside_temp'] . "°C</li>";
echo "<li>Clim en cours : " . ($response['climate_state']['is_climate_on'] ? 'Oui' : 'Non') . "</li>";
echo "</ul>";

echo "<h2>📍 Position</h2><ul>";
echo "<li>Latitude : " . $response['drive_state']['latitude'] . "</li>";
echo "<li>Longitude : " . $response['drive_state']['longitude'] . "</li>";
echo "<li>Vitesse : " . ($response['drive_state']['speed'] ?? 'N/A') . " km/h</li>";
echo "</ul>";