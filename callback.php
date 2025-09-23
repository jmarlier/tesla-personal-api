<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();

if ($_GET['state'] !== ($_SESSION['oauth_state'] ?? null)) {
    exit('❌ État CSRF invalide.');
}

$code = $_GET['code'] ?? null;
if (!$code) {
    exit('❌ Aucun code reçu.');
}

$clientId     = $_ENV['TESLA_CLIENT_ID'];
$clientSecret = $_ENV['TESLA_CLIENT_SECRET'];
$redirectUri  = $_ENV['TESLA_REDIRECT_URI'];
$codeVerifier = $_SESSION['code_verifier'] ?? null;

$fields = http_build_query([
    'grant_type'    => 'authorization_code',
    'client_id'     => $clientId,
    'client_secret' => $clientSecret,
    'code'          => $code,
    'code_verifier' => $codeVerifier,
    'redirect_uri'  => $redirectUri,
]);

$ch = curl_init('https://auth.tesla.com/oauth2/v3/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_POSTFIELDS     => $fields,
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

header('Content-Type: text/html; charset=utf-8');
echo "<h2>🔑 Token utilisateur reçu :</h2><pre>";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";

if (!isset($data['access_token'])) {
    exit("❌ Token non reçu ou invalide.");
}

$accessToken = $data['access_token'];

// === ÉTAPE 1 : Découvrir la région réelle de l'utilisateur
$chRegion = curl_init('https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/users/region');
curl_setopt_array($chRegion, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken
    ]
]);
$regionResponse = curl_exec($chRegion);
$regionCode     = curl_getinfo($chRegion, CURLINFO_HTTP_CODE);
curl_close($chRegion);

echo "<h2>🌍 Région détectée :</h2><pre>";
echo "HTTP Status: $regionCode\n";
echo json_encode(json_decode($regionResponse, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";

// === Construire l'URL base dynamique
$regionData = json_decode($regionResponse, true);
$apiBaseUrl = $regionData['response']['fleet_api_base_url'] ?? 'https://fleet-api.prd.eu.vn.cloud.tesla.com';

// === ÉTAPE 2 : Enregistrer le compte utilisateur dans la bonne région
$chRegister = curl_init($apiBaseUrl . '/api/1/partner_accounts/register');
curl_setopt_array($chRegister, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode([])
]);

$responseRegister = curl_exec($chRegister);
$httpCodeRegister = curl_getinfo($chRegister, CURLINFO_HTTP_CODE);
curl_close($chRegister);

echo "<h2>🧾 Enregistrement de l’utilisateur dans la région :</h2><pre>";
echo "HTTP Status: $httpCodeRegister\n";
echo json_encode(json_decode($responseRegister, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";

// === ÉTAPE 3 : Appel API /vehicles
$chVehicles = curl_init($apiBaseUrl . '/api/1/vehicles');
curl_setopt_array($chVehicles, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]
]);
$responseVehicles = curl_exec($chVehicles);
$httpCodeVehicles = curl_getinfo($chVehicles, CURLINFO_HTTP_CODE);
curl_close($chVehicles);

echo "<h2>🚗 /vehicles :</h2><pre>";
echo "HTTP Status: $httpCodeVehicles\n";
echo json_encode(json_decode($responseVehicles, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";