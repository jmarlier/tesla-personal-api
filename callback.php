<?php
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
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

// === ÉTAPE 1 : échange code ↔ token
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

// === Affichage JSON brut
header('Content-Type: application/json; charset=utf-8');
echo "🔑 <b>partner_access_token reçu :</b><br><pre>";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";

// === Vérif access_token
if (!isset($data['access_token'])) {
    exit("❌ Token non reçu ou invalide.");
}

$accessToken = $data['access_token'];

// === ÉTAPE 2 : Appeler /partner_accounts
$ch2 = curl_init('https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/partner_accounts');
curl_setopt_array($ch2, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode(['domain' => $domain])
]);
$partnerResponse = curl_exec($ch2);
$httpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

// === Affichage retour Tesla Fleet
echo "<br>📡 <b>Réponse /partner_accounts :</b><br><pre>";
echo "HTTP Status: $httpCode\n";
echo json_encode(json_decode($partnerResponse, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";