<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Chargement des tokens Fleet API
$tokensPath = __DIR__ . '/tokens.json';
if (!file_exists($tokensPath)) {
    exit('❌ Aucun token trouvé. Lance d’abord l’authentification.');
}

$tokens = json_decode(file_get_contents($tokensPath), true);
$accessToken = $tokens['access_token'] ?? null;

if (!$accessToken) {
    exit('❌ Le token d’accès Fleet API est introuvable.');
}

// Étape 1 : Obtenir l’ID du véhicule
$vehicleList = file_get_contents(
    'https://fleet-api.teslamotors.com/api/1/vehicles',
    false,
    stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Authorization: Bearer $accessToken\r\n",
        ]
    ])
);

$vehicleData = json_decode($vehicleList, true);
$vehicles = $vehicleData['response'] ?? [];

if (empty($vehicles)) {
    exit('🚫 Aucun véhicule trouvé sur ce compte.');
}

$vehicleId = $vehicles[0]['id']; // on prend le premier véhicule pour commencer

// Étape 2 : Récupérer toutes les données du véhicule
$vehicleDetails = file_get_contents(
    "https://fleet-api.teslamotors.com/api/1/vehicles/{$vehicleId}/vehicle_data",
    false,
    stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Authorization: Bearer $accessToken\r\n",
        ]
    ])
);

$data = json_decode($vehicleDetails, true);
$response = $data['response'] ?? null;

if (!$response) {
    exit('❌ Impossible de récupérer les données du véhicule.');
}

// Format HTML de base
echo "<h1>🚘 Données du véhicule Tesla</h1>";

echo "<h2>📋 Informations générales</h2>";
echo "<ul>";
echo "<li><strong>Nom :</strong> " . htmlspecialchars($response['display_name']) . "</li>";
echo "<li><strong>VIN :</strong> " . htmlspecialchars($response['vin']) . "</li>";
echo "<li><strong>Modèle :</strong> " . htmlspecialchars($response['vehicle_config']['car_type']) . "</li>";
echo "<li><strong>Version :</strong> " . htmlspecialchars($response['vehicle_config']['trim_badging']) . "</li>";
echo "<li><strong>État :</strong> " . htmlspecialchars($response['state']) . "</li>";
echo "</ul>";

echo "<h2>🔋 Batterie</h2>";
$charge = $response['charge_state'];
echo "<ul>";
echo "<li><strong>Niveau batterie :</strong> " . $charge['battery_level'] . "%</li>";
echo "<li><strong>Autonomie estimée :</strong> " . $charge['battery_range'] . " km</li>";
echo "<li><strong>État de charge :</strong> " . $charge['charging_state'] . "</li>";
echo "<li><strong>Limite de charge :</strong> " . $charge['charge_limit_soc'] . "%</li>";
echo "</ul>";

echo "<h2🌡️>🌡️ Climatisation</h2>";
$climate = $response['climate_state'];
echo "<ul>";
echo "<li><strong>Temp. intérieure :</strong> " . $climate['inside_temp'] . " °C</li>";
echo "<li><strong>Temp. extérieure :</strong> " . $climate['outside_temp'] . " °C</li>";
echo "<li><strong>Clim active :</strong> " . ($climate['is_climate_on'] ? 'Oui' : 'Non') . "</li>";
echo "</ul>";

echo "<h2>📍 Position actuelle</h2>";
$drive = $response['drive_state'];
echo "<ul>";
echo "<li><strong>Latitude :</strong> " . $drive['latitude'] . "</li>";
echo "<li><strong>Longitude :</strong> " . $drive['longitude'] . "</li>";
echo "<li><strong>Vitesse :</strong> " . ($drive['speed'] ?? 'N/A') . " km/h</li>";
echo "</ul>";

echo "<hr>";
echo "<p style='color:gray'>Affichage simplifié depuis l'API Tesla Fleet 🚗</p>";