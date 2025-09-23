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

// 🔍 Appel de l’API /vehicles
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

$http_response_header = $http_response_header ?? [];

$vehicleData = json_decode($vehicleList, true);
$vehicles = $vehicleData['response'] ?? [];

// 🔧 Affichage complet pour debug
echo "<h1>🚘 DEBUG VÉHICULES</h1>";
echo "<h2>🌐 URL appelée :</h2><pre>{$apiBase}/api/1/vehicles</pre>";
echo "<h2>🔐 Access Token (tronqué) :</h2><pre>" . substr($accessToken, 0, 40) . "...</pre>";
echo "<h2>📨 Réponse brute :</h2><pre>" . htmlspecialchars($vehicleList) . "</pre>";
echo "<h2>📦 JSON décodé :</h2><pre>";
print_r($vehicleData);
echo "</pre>";
echo "<h2>📡 En-têtes HTTP :</h2><pre>";
print_r($http_response_header);
echo "</pre>";

if (empty($vehicles)) {
    echo "<h2>🚫 Aucun véhicule trouvé.</h2>";
    exit;
}

echo "<h2>✅ Véhicule(s) trouvés :</h2><pre>";
print_r($vehicles);
echo "</pre>";