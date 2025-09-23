<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// 🔐 Vérification des paramètres d'auth
if (!isset($_GET['code'])) {
    exit('❌ Code d’autorisation manquant.');
}

if (!isset($_SESSION['code_verifier']) || !isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    exit('❌ Vérification CSRF échouée ou code_verifier manquant.');
}

// 📥 Récupération des variables
$code = $_GET['code'];
$codeVerifier = $_SESSION['code_verifier'];
$clientId = $_ENV['TESLA_CLIENT_ID'];
$clientSecret = $_ENV['TESLA_CLIENT_SECRET'];
$redirectUri = $_ENV['TESLA_REDIRECT_URI'];

// 🔁 Étape 1 : Obtenir le token via auth.tesla.com
$postData = http_build_query([
    'grant_type' => 'authorization_code',
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'code' => $code,
    'code_verifier' => $codeVerifier,
    'redirect_uri' => $redirectUri,
]);

$context = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $postData,
        'ignore_errors' => true
    ]
];

$response = file_get_contents('https://auth.tesla.com/oauth2/v3/token', false, stream_context_create($context));
$tokens = json_decode($response, true);

if ($response === false || isset($tokens['error']) || !isset($tokens['access_token'])) {
    echo "<h3>❌ Erreur lors de l’authentification Tesla</h3><pre>";
    print_r($tokens ?: error_get_last());
    echo "</pre>";
    exit;
}

// ✅ Utiliser directement le access_token avec /users/region
$accessToken = $tokens['access_token'];

$regionContext = [
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer " . $accessToken . "\r\n",
        'ignore_errors' => true
    ]
];

$regionResponse = file_get_contents('https://fleet-api.teslamotors.com/api/1/users/region', false, stream_context_create($regionContext));
$regionData = json_decode($regionResponse, true);

$fleetApiBase = $regionData['response']['fleet_api_base_url'] ?? 'https://fleet-api.teslamotors.com';

// 💾 Stocker access_token + base API
file_put_contents(__DIR__ . '/tokens.json', json_encode([
    'access_token' => $accessToken,
    'fleet_api_base_url' => $fleetApiBase
], JSON_PRETTY_PRINT));

// ✅ Redirection vers la page des véhicules
header('Location: vehicles.php');
exit;