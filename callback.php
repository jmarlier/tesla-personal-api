<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// 🔐 Auth Code check
if (!isset($_GET['code'])) exit('❌ Code manquant.');
if (!isset($_SESSION['code_verifier']) || !isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    exit('❌ CSRF ou code_verifier manquant.');
}

$code = $_GET['code'];
$codeVerifier = $_SESSION['code_verifier'];
$clientId = $_ENV['TESLA_CLIENT_ID'];
$clientSecret = $_ENV['TESLA_CLIENT_SECRET'];
$redirectUri = $_ENV['TESLA_REDIRECT_URI'];

// 🔁 Etape 1 - Access Token (Tesla Auth)
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
$authTokens = json_decode($authResponse, true);

// Si erreur → enregistrer aussi
if (!isset($authTokens['access_token'])) {
    file_put_contents(__DIR__ . '/debug.json', json_encode([
        'step' => 'auth',
        'request' => $postData,
        'response_raw' => $authResponse,
        'response_json' => $authTokens,
    ], JSON_PRETTY_PRINT));
    header('Location: vehicles.php');
    exit;
}

// 🔁 Etape 2 - Récupération de la région
$regionResponse = file_get_contents(
    'https://fleet-api.teslamotors.com/api/1/users/region',
    false,
    stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Authorization: Bearer {$authTokens['access_token']}\r\n",
            'ignore_errors' => true
        ]
    ])
);
$regionData = json_decode($regionResponse, true);
$fleetApiBase = $regionData['response']['fleet_api_base_url'] ?? null;

// 🔁 Etape 3 - Fleet token
$fleetRequest = [
    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
    'client_id' => 'ownerapi',
    'assertion' => $authTokens['access_token'],
    'scope' => 'openid vehicle_read vehicle_write'
];
$fleetResponse = null;
$fleetData = [];

if ($fleetApiBase) {
    $fleetResponse = file_get_contents($fleetApiBase . '/oauth/token', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode($fleetRequest),
            'ignore_errors' => true
        ]
    ]));
    $fleetData = json_decode($fleetResponse, true);
}

// 💾 Debug complet dans un fichier
file_put_contents(__DIR__ . '/debug.json', json_encode([
    'auth' => [
        'url' => 'https://auth.tesla.com/oauth2/v3/token',
        'request' => $postData,
        'response_raw' => $authResponse,
        'response_json' => $authTokens,
    ],
    'region' => [
        'url' => 'https://fleet-api.teslamotors.com/api/1/users/region',
        'response_raw' => $regionResponse,
        'response_json' => $regionData,
    ],
    'fleet' => [
        'url' => $fleetApiBase . '/oauth/token',
        'request' => $fleetRequest,
        'response_raw' => $fleetResponse,
        'response_json' => $fleetData,
    ],
    'fleet_api_base_url' => $fleetApiBase
], JSON_PRETTY_PRINT));

// ✅ Rediriger vers vehicles.php (qui affichera tout)
header('Location: vehicles.php');
exit;