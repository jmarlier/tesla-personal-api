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

if ($_GET['state'] !== $_SESSION['oauth_state']) {
    exit('❌ État CSRF invalide.');
}

$code = $_GET['code'] ?? null;
if (!$code) {
    exit('❌ Aucun code reçu.');
}

$clientId = $_ENV['TESLA_CLIENT_ID'];
$clientSecret = $_ENV['TESLA_CLIENT_SECRET'];
$redirectUri = $_ENV['TESLA_REDIRECT_URI'];
$domain = $_ENV['TESLA_DOMAIN'] ?? 'app.jeromemarlier.com';
$codeVerifier = $_SESSION['code_verifier'];

$tokenUrl = 'https://auth.tesla.com/oauth2/v3/token';

$fields = http_build_query([
    'grant_type' => 'authorization_code',
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'code' => $code,
    'code_verifier' => $codeVerifier,
    'redirect_uri' => $redirectUri,
]);

$ch = curl_init($tokenUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_POSTFIELDS => $fields,
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
header('Content-Type: application/json; charset=utf-8');

echo "🔑 <b>Token utilisateur reçu :</b><br><pre>";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre><br>";

if (!isset($data['access_token'])) {
    exit("❌ Token utilisateur non reçu.");
}

$userToken = $data['access_token'];

// === 🔐 PARTNER TOKEN
$partnerFields = http_build_query([
    'grant_type' => 'client_credentials',
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'scope' => 'openid vehicle_device_data vehicle_cmds vehicle_charging_cmds',
    'audience' => 'https://fleet-api.prd.eu.vn.cloud.tesla.com'
]);

$ch2 = curl_init('https://fleet-auth.prd.vn.cloud.tesla.com/oauth2/v3/token');
curl_setopt_array($ch2, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_POSTFIELDS => $partnerFields
]);
$partnerResponse = curl_exec($ch2);
curl_close($ch2);
$partnerData = json_decode($partnerResponse, true);
$partnerToken = $partnerData['access_token'] ?? null;

if (!$partnerToken) {
    exit("❌ Partner access_token non reçu.");
}

// === 🧠 Extraire account_id depuis userToken (JWT)
$parts = explode('.', $userToken);
$payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
$accountId = $payload['account_id'] ?? null;

if (!$accountId) {
    exit("❌ Impossible d’extraire l’account_id du token utilisateur.");
}

// === 🌍 Vérifier la région
$regionCheck = curl_init('https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/users/region');
curl_setopt_array($regionCheck, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $userToken
    ]
]);
$regionResponse = curl_exec($regionCheck);
$regionHttpCode = curl_getinfo($regionCheck, CURLINFO_HTTP_CODE);
curl_close($regionCheck);

echo "<b>🌍 Région détectée :</b><br><pre>";
echo "HTTP Status: $regionHttpCode\n";
echo json_encode(json_decode($regionResponse, true), JSON_PRETTY_PRINT);
echo "</pre>";

// === 🧾 Enregistrement utilisateur
$registerUser = curl_init('https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/partner_accounts/register');
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

echo "<b>🧾 Enregistrement de l’utilisateur dans la région :</b><br><pre>";
echo "HTTP Status: $registerUserCode\n";
echo json_encode(json_decode($registerUserResponse, true), JSON_PRETTY_PRINT);
echo "</pre>";

// === 🚗 Appel /vehicles
$vehicles = curl_init('https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/vehicles');
curl_setopt_array($vehicles, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $userToken
    ]
]);
$vehiclesResponse = curl_exec($vehicles);
$vehiclesCode = curl_getinfo($vehicles, CURLINFO_HTTP_CODE);
curl_close($vehicles);

echo "<b>🚗 /vehicles :</b><br><pre>";
echo "HTTP Status: $vehiclesCode\n";
echo json_encode(json_decode($vehiclesResponse, true), JSON_PRETTY_PRINT);
echo "</pre>";