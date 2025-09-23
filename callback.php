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
$codeVerifier = $_SESSION['code_verifier'];

// === ÉTAPE 1 : échange code ↔ token utilisateur
$fields = http_build_query([
    'grant_type' => 'authorization_code',
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'code' => $code,
    'code_verifier' => $codeVerifier,
    'redirect_uri' => $redirectUri,
]);

$ch = curl_init('https://auth.tesla.com/oauth2/v3/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_POSTFIELDS => $fields,
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

// === Affichage token utilisateur
header('Content-Type: text/html; charset=utf-8');
echo "<h2>🔑 Token utilisateur reçu :</h2><pre>";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";

if (!isset($data['access_token'])) {
    exit("❌ Token utilisateur non reçu.");
}

$accessToken = $data['access_token'];

// === Décodage du token utilisateur pour extraire account_id
$tokenParts = explode('.', $accessToken);
$payload = json_decode(base64_decode(strtr($tokenParts[1], '-_', '+/')), true);
$accountId = $payload['account_id'] ?? null;

if (!$accountId) {
    exit("❌ Impossible d’extraire l’account_id du token utilisateur.");
}

// === Récupération d’un partner_access_token
$partnerFields = http_build_query([
    'grant_type' => 'client_credentials',
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'scope' => 'openid vehicle_device_data vehicle_cmds vehicle_charging_cmds',
    'audience' => 'https://fleet-api.prd.eu.vn.cloud.tesla.com'
]);

$chPartner = curl_init('https://fleet-auth.prd.vn.cloud.tesla.com/oauth2/v3/token');
curl_setopt_array($chPartner, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_POSTFIELDS => $partnerFields
]);
$partnerResponse = curl_exec($chPartner);
curl_close($chPartner);

$partnerData = json_decode($partnerResponse, true);
$partnerToken = $partnerData['access_token'] ?? null;

if (!$partnerToken) {
    exit("❌ Impossible d’obtenir le partner_access_token.");
}

// === Enregistrement de l’utilisateur dans la région via le token PARTENAIRE
$chRegister = curl_init('https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/partner_accounts/register');
curl_setopt_array($chRegister, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $partnerToken,
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'account_id' => $accountId
    ])
]);
$responseRegister = curl_exec($chRegister);
$httpCodeRegister = curl_getinfo($chRegister, CURLINFO_HTTP_CODE);
curl_close($chRegister);

echo "<h2>🧾 Enregistrement de l’utilisateur dans la région :</h2><pre>";
echo "HTTP Status: $httpCodeRegister\n";
echo json_encode(json_decode($responseRegister, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";

// === Appel /vehicles
$chVehicles = curl_init('https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/vehicles');
curl_setopt_array($chVehicles, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken
    ]
]);
$responseVehicles = curl_exec($chVehicles);
$httpCodeVehicles = curl_getinfo($chVehicles, CURLINFO_HTTP_CODE);
curl_close($chVehicles);

echo "<h2>🚗 /vehicles :</h2><pre>";
echo "HTTP Status: $httpCodeVehicles\n";
echo json_encode(json_decode($responseVehicles, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";

// === Appel /users/orders
$chOrders = curl_init('https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/users/orders');
curl_setopt_array($chOrders, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken
    ]
]);
$responseOrders = curl_exec($chOrders);
$httpCodeOrders = curl_getinfo($chOrders, CURLINFO_HTTP_CODE);
curl_close($chOrders);

echo "<h2>📦 /users/orders :</h2><pre>";
echo "HTTP Status: $httpCodeOrders\n";
echo json_encode(json_decode($responseOrders, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";