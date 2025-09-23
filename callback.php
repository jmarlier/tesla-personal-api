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

// 📥 Variables
$code = $_GET['code'];
$codeVerifier = $_SESSION['code_verifier'];
$clientId = $_ENV['TESLA_CLIENT_ID'];
$clientSecret = $_ENV['TESLA_CLIENT_SECRET'];
$redirectUri = $_ENV['TESLA_REDIRECT_URI'];

// 🔁 Étape 1 : Récupération du token d’auth
$postData = http_build_query([
    'grant_type' => 'authorization_code',
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'code' => $code,
    'code_verifier' => $codeVerifier,
    'redirect_uri' => $redirectUri,
]);

$authResponse = file_get_contents('https://auth.tesla.com/oauth2/v3/token', false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $postData,
        'ignore_errors' => true
    ]
]));
$tokens = json_decode($authResponse, true);

// ⚠️ Vérif
if ($authResponse === false || isset($tokens['error'])) {
    echo "<h3>❌ Erreur Auth Tesla</h3><pre>";
    echo "Réponse brute :\n" . htmlspecialchars($authResponse);
    echo "\n\nErreur JSON :\n";
    print_r($tokens);
    echo "</pre>";
    exit;
}

// 🧭 Étape 2 : Déterminer l’URL régionale Fleet
$regionResponse = file_get_contents(
    'https://fleet-api.teslamotors.com/api/1/users/region',
    false,
    stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Authorization: Bearer {$tokens['access_token']}\r\n",
            'ignore_errors' => true
        ]
    ])
);
$regionData = json_decode($regionResponse, true);
$fleetApiBase = $regionData['response']['fleet_api_base_url'] ?? null;

if (!$fleetApiBase) {
    echo "<h3>❌ Impossible de déterminer la région Fleet API</h3><pre>";
    echo "Réponse brute :\n" . htmlspecialchars($regionResponse);
    echo "\n\nJSON :\n";
    print_r($regionData);
    echo "</pre>";
    exit;
}

// 🔁 Étape 3 : Échange vers Fleet API token
$fleetRequest = [
    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
    'client_id' => 'ownerapi',
    'assertion' => $tokens['access_token'],
    'scope' => 'openid vehicle_read vehicle_write'
];

$fleetContext = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => json_encode($fleetRequest),
        'ignore_errors' => true
    ]
];

$fleetResponse = file_get_contents(
    $fleetApiBase . '/oauth/token',
    false,
    stream_context_create($fleetContext)
);
$fleetTokens = json_decode($fleetResponse, true);

// ⚠️ Vérification Fleet API
if ($fleetResponse === false || !isset($fleetTokens['access_token'])) {
    echo "<h3>❌ Erreur lors de l’échange vers Fleet API</h3><pre>";
    echo "Fleet API URL utilisée : $fleetApiBase\n";
    echo "\nRequête envoyée :\n";
    print_r($fleetRequest);
    echo "\n\nRéponse brute :\n" . htmlspecialchars($fleetResponse ?: 'Aucune réponse');
    echo "\n\nJSON :\n";
    print_r($fleetTokens);
    echo "</pre>";
    exit;
}

// ➕ Ajouter l’URL utilisée
$fleetTokens['fleet_api_base_url'] = $fleetApiBase;

// 💾 Sauvegarder le token dans un fichier
file_put_contents(__DIR__ . '/tokens.json', json_encode($fleetTokens, JSON_PRETTY_PRINT));

// ✅ Redirection
header('Location: vehicles.php');
exit;