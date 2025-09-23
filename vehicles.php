<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Lecture du token utilisateur
$tokenPath = __DIR__ . '/tokens.json';
if (!file_exists($tokenPath)) {
    exit("❌ Aucun fichier tokens.json trouvé");
}

$tokens = json_decode(file_get_contents($tokenPath), true);
$accessToken = $tokens['access_token'] ?? null;

if (!$accessToken) {
    exit("❌ Aucun token d'accès valide trouvé");
}

//
// 1. Requête vers /users/region
//
echo "<h2>🌍 /users/region</h2><pre>";

$regionCurl = curl_init('https://fleet-api.prd.na.vn.cloud.tesla.com/api/1/users/region');
curl_setopt_array($regionCurl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]
]);
$regionResponse = curl_exec($regionCurl);
$regionHttpCode = curl_getinfo($regionCurl, CURLINFO_HTTP_CODE);
curl_close($regionCurl);

echo "HTTP Status: $regionHttpCode\n";
echo $regionResponse . "</pre>";

$regionData = json_decode($regionResponse, true);
$fleetBaseUrl = $regionData['fleet_api_base_url'] ?? 'https://fleet-api.prd.na.vn.cloud.tesla.com';

//
// 2. Requête vers /vehicles
//
echo "<h2>🚗 /vehicles</h2><pre>";

$vehiclesCurl = curl_init($fleetBaseUrl . '/api/1/vehicles');
curl_setopt_array($vehiclesCurl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]
]);
$vehiclesResponse = curl_exec($vehiclesCurl);
$vehiclesHttpCode = curl_getinfo($vehiclesCurl, CURLINFO_HTTP_CODE);
curl_close($vehiclesCurl);

echo "HTTP Status: $vehiclesHttpCode\n";
echo $vehiclesResponse . "</pre>";