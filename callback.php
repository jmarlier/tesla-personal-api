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

// 🔒 Vérification anti-CSRF
if ($_GET['state'] !== ($_SESSION['oauth_state'] ?? null)) {
    exit('❌ État CSRF invalide.');
}

$code = $_GET['code'] ?? null;
if (!$code) {
    exit('❌ Aucun code reçu.');
}

// 🔐 Paramètres
$clientId     = $_ENV['TESLA_CLIENT_ID'];
$clientSecret = $_ENV['TESLA_CLIENT_SECRET'];
$redirectUri  = $_ENV['TESLA_REDIRECT_URI'];
$codeVerifier = $_SESSION['code_verifier'] ?? null;

// === ÉTAPE 1 : Échange code ↔ token utilisateur
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

// === Affichage du token brut
header('Content-Type: text/html; charset=utf-8');
echo "<h2>🔑 Token utilisateur reçu :</h2><pre>";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";

// === Vérif access_token
if (!isset($data['access_token'])) {
    exit("❌ Token non reçu ou invalide.");
}

$accessToken = $data['access_token'];

// === ÉTAPE 2 : Enregistrement utilisateur dans la région (obligatoire !)
$chRegister = curl_init('https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/partner_accounts/register');
curl_setopt_array($chRegister, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode([]) // Rien à envoyer
]);

$responseRegister = curl_exec($chRegister);
$httpCodeRegister = curl_getinfo($chRegister, CURLINFO_HTTP_CODE);
curl_close($chRegister);

// === Affiche le résultat de l'enregistrement
echo "<h2>🧾 Enregistrement de l’utilisateur dans la région :</h2><pre>";
echo "HTTP Status: $httpCodeRegister\n";
echo json_encode(json_decode($responseRegister, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";

// === ÉTAPE 3 : Appel API /vehicles
$chVehicles = curl_init('https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/vehicles');
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

// === Affichage véhicules
echo "<h2>🚗 /vehicles :</h2><pre>";
echo "HTTP Status: $httpCodeVehicles\n";
echo json_encode(json_decode($responseVehicles, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";