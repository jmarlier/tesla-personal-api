<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
session_start();

// Token utilisateur
$accessToken = $_SESSION['access_token'] ?? null;
if (!$accessToken) exit("❌ Aucun token utilisateur");

// 🔑 Affiche token utilisateur
echo "<b>🔑 Token utilisateur reçu :</b><br><pre>" . json_encode([
    'access_token' => $accessToken,
    'refresh_token' => $_SESSION['refresh_token'] ?? null,
    'id_token' => $_SESSION['id_token'] ?? null,
    'expires_in' => $_SESSION['expires_in'] ?? null,
    'state' => $_SESSION['state'] ?? null,
    'token_type' => $_SESSION['token_type'] ?? null
], JSON_PRETTY_PRINT) . "</pre>";

// 1️⃣ Demande la région de l’utilisateur
$regionCurl = curl_init('https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/users/region');
curl_setopt_array($regionCurl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]
]);
$regionResponse = curl_exec($regionCurl);
$regionCode = curl_getinfo($regionCurl, CURLINFO_HTTP_CODE);
curl_close($regionCurl);

echo "<b>🌍 Région détectée :</b><br><pre>HTTP Status: $regionCode\n$regionResponse</pre>";

$regionData = json_decode($regionResponse, true);
$baseUrl = $regionData['response']['fleet_api_base_url'] ?? null;
$accountId = $regionData['response']['account_id'] ?? null;

if (!$baseUrl || !$accountId) {
    exit("❌ Impossible de détecter la région ou l'account_id");
}

// 2️⃣ Récupère un PARTNER token
$fields = http_build_query([
    'grant_type' => 'client_credentials',
    'client_id' => $_ENV['TESLA_CLIENT_ID'],
    'client_secret' => $_ENV['TESLA_CLIENT_SECRET'],
    'scope' => 'openid vehicle_device_data vehicle_cmds vehicle_charging_cmds',
    'audience' => $baseUrl
]);

$partnerCurl = curl_init('https://fleet-auth.prd.vn.cloud.tesla.com/oauth2/v3/token');
curl_setopt_array($partnerCurl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_POSTFIELDS => $fields
]);
$partnerResponse = curl_exec($partnerCurl);
curl_close($partnerCurl);
$partnerData = json_decode($partnerResponse, true);
$partnerToken = $partnerData['access_token'] ?? null;

if (!$partnerToken) {
    exit("❌ Partner token non reçu");
}

// 3️⃣ Enregistrement de l’utilisateur dans sa région
$registerUser = curl_init("$baseUrl/api/1/users/{$accountId}/register");
curl_setopt_array($registerUser, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $partnerToken,
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode(['account_id' => $accountId])
]);
$registerUserResponse = curl_exec($registerUser);
$registerUserCode = curl_getinfo($registerUser, CURLINFO_HTTP_CODE);
curl_close($registerUser);

echo "<b>🧾 Enregistrement de l’utilisateur dans la région :</b><br><pre>HTTP Status: $registerUserCode\n";
echo json_encode(json_decode($registerUserResponse, true), JSON_PRETTY_PRINT) . "</pre>";

// 4️⃣ Appelle /vehicles
$vehicles = curl_init("$baseUrl/api/1/vehicles");
curl_setopt_array($vehicles, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken
    ]
]);
$vehiclesResponse = curl_exec($vehicles);
$vehiclesCode = curl_getinfo($vehicles, CURLINFO_HTTP_CODE);
curl_close($vehicles);

echo "<b>🚗 /vehicles :</b><br><pre>HTTP Status: $vehiclesCode\n$vehiclesResponse</pre>";