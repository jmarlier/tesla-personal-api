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
    exit("❌ Aucun access_token utilisateur trouvé dans tokens.json");
}

// Étape 1 : Déterminer la bonne région
echo "<h2>🌍 /users/region</h2><pre>";

$regionCurl = curl_init('https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/users/region');
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
$fleetBaseUrl = $regionData['fleet_api_base_url'] ?? null;

if (!$fleetBaseUrl) {
    exit("❌ Impossible de détecter l’URL de base de la Fleet API.");
}

// Étape 2 : Appel à /vehicles (via token utilisateur)
echo "<h2>🚗 /vehicles (via access_token utilisateur)</h2><pre>";

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