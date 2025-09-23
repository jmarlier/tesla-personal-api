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

// 🔁 Requête POST vers auth.tesla.com pour récupérer le token utilisateur
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

// ✅ Vérification étape 1 : Auth Tesla
if ($response === false || isset($tokens['error'])) {
    echo "<h3>❌ Erreur lors de l’authentification Tesla (auth.tesla.com)</h3><pre>";
    echo "Réponse brute :\n";
    echo htmlspecialchars($response);
    echo "\n\nErreur PHP :\n";
    print_r(error_get_last());
    echo "\n\nErreur JSON :\n";
    print_r($tokens);
    echo "</pre>";
    exit;
}

// 🔁 Étape 2 : Échange vers un token Fleet API (obligatoire)
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
    'https://fleet-api.teslamotors.com/oauth/token',
    false,
    stream_context_create($fleetContext)
);

$fleetTokens = json_decode($fleetResponse, true);

echo "<h3>🐞 DEBUG FLEET API</h3><pre>";
echo "🔐 Token utilisé :\n" . htmlspecialchars($tokens['access_token']) . "\n\n";
echo "📤 Corps envoyé :\n";
print_r($fleetRequest);
echo "\n📨 Réponse brute :\n" . htmlspecialchars($fleetResponse);
echo "\n\n📦 Réponse JSON :\n";
print_r($fleetTokens);
echo "</pre>";
exit;

// ✅ Vérification étape 2 : échange Fleet API
if ($fleetResponse === false || !isset($fleetTokens['access_token'])) {
    echo "<h3>❌ Erreur lors de l’échange vers Fleet API (fleet-api.teslamotors.com)</h3><pre>";
    echo "Requête envoyée :\n";
    print_r($fleetRequest);
    echo "\nRéponse brute :\n";
    echo htmlspecialchars($fleetResponse ?: 'Aucune réponse');
    echo "\n\nErreur PHP :\n";
    print_r(error_get_last());
    echo "\n\nRéponse JSON :\n";
    print_r($fleetTokens);
    echo "</pre>";
    exit;
}

// 💾 Stocker le token Fleet API uniquement
file_put_contents(__DIR__ . '/tokens.json', json_encode($fleetTokens, JSON_PRETTY_PRINT));

// ✅ Redirection finale vers interface
header('Location: vehicles.php');
exit;