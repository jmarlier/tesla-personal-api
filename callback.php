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

$response = file_get_contents('https://auth.tesla.com/oauth2/v3/token', false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $postData,
        'ignore_errors' => true
    ]
]));

$tokens = json_decode($response, true);

if ($response === false || isset($tokens['error'])) {
    echo "<h3>❌ Erreur auth.tesla.com</h3><pre>";
    echo htmlspecialchars($response);
    echo "\n\n";
    print_r($tokens);
    echo "</pre>";
    exit;
}

// 🧭 Étape 2 : Demander la région de l'utilisateur
$defaultRegionUrl = 'https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/users/region';

$regionResponse = file_get_contents(
    $defaultRegionUrl,
    false,
    stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Authorization: Bearer " . $tokens['access_token'] . "\r\n",
            'ignore_errors' => true
        ]
    ])
);

$regionData = json_decode($regionResponse, true);

if (!isset($regionData['response']['fleet_api_base_url'])) {
    echo "<h3>❌ Impossible de déterminer la région Fleet API</h3><pre>";
    echo "Réponse brute :\n" . htmlspecialchars($regionResponse);
    echo "\n\nJSON :\n";
    print_r($regionData);
    echo "</pre>";
    exit;
}

$fleetApiBase = $regionData['response']['fleet_api_base_url'];

// 🔁 Étape 3 : Échanger le token contre un token Fleet API (régional)
$fleetRequest = [
    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
    'client_id' => 'ownerapi',
    'assertion' => $tokens['access_token'],
    'scope' => 'openid vehicle_read vehicle_write'
];

$fleetResponse = file_get_contents(
    $fleetApiBase . '/oauth/token',
    false,
    stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode($fleetRequest),
            'ignore_errors' => true
        ]
    ])
);

$fleetTokens = json_decode($fleetResponse, true);

if (!isset($fleetTokens['access_token'])) {
    echo "<h3>❌ Erreur lors de l’échange vers Fleet API</h3><pre>";
    echo "Fleet URL : $fleetApiBase\n";
    echo "Réponse brute :\n" . htmlspecialchars($fleetResponse);
    echo "\n\nJSON :\n";
    print_r($fleetTokens);
    echo "</pre>";
    exit;
}

// ➕ Ajouter l’URL Fleet API utilisée
$fleetTokens['fleet_api_base_url'] = $fleetApiBase;

// 💾 Sauvegarde dans un fichier JSON
file_put_contents(__DIR__ . '/tokens.json', json_encode($fleetTokens, JSON_PRETTY_PRINT));

// ✅ Redirection finale
header('Location: vehicles.php');
exit;