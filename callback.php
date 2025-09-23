<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
session_start();

echo "<h2>🔑 <b>Token utilisateur reçu :</b></h2><pre>";

// Vérifie présence du `code`
if (!isset($_GET['code'])) {
    exit('❌ Code manquant dans le callback');
}

// Récupération du `code` et `state`
$code = $_GET['code'];
$state = $_GET['state'] ?? '';
$codeVerifier = $_SESSION['code_verifier'] ?? '';

// Vérifie le state
if ($state !== $_SESSION['oauth_state']) {
    exit('❌ Erreur de vérification du state');
}

// Appel au token endpoint
$tokenUrl = 'https://auth.tesla.com/oauth2/v3/token';
$fields = http_build_query([
    'grant_type' => 'authorization_code',
    'client_id' => $_ENV['TESLA_CLIENT_ID'],
    'code' => $code,
    'code_verifier' => $codeVerifier,
    'redirect_uri' => $_ENV['TESLA_REDIRECT_URI']
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

if (!isset($data['access_token'])) {
    exit("❌ Aucun token utilisateur");
}

// Affiche token brut
echo json_encode($data, JSON_PRETTY_PRINT);
echo "</pre>";

// Stocke en session
$_SESSION['access_token'] = $data['access_token'];
$_SESSION['refresh_token'] = $data['refresh_token'] ?? null;
$_SESSION['id_token'] = $data['id_token'] ?? null;
$_SESSION['expires_in'] = $data['expires_in'] ?? null;
$_SESSION['token_type'] = $data['token_type'] ?? null;

$userToken = $data['access_token'];

//
// 🌍 1. Appelle /users/region
//
echo "<br><b>🌍 Région détectée :</b><br><pre>";

$regionCurl = curl_init('https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/users/region');
curl_setopt_array($regionCurl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $userToken,
        'Content-Type: application/json'
    ]
]);
$regionResponse = curl_exec($regionCurl);
$regionHttpCode = curl_getinfo($regionCurl, CURLINFO_HTTP_CODE);
curl_close($regionCurl);

echo "HTTP Status: $regionHttpCode\n";
echo $regionResponse . "</pre>";

// Parse region
$regionData = json_decode($regionResponse, true);
$fleetBaseUrl = $regionData['fleet_api_base_url'] ?? 'https://fleet-api.prd.eu.vn.cloud.tesla.com';
$accountId = $regionData['account_id'] ?? null;

// ❗️ Vérifie si déjà enregistré
if ($regionHttpCode == 412 && $accountId) {
    //
    // 🧾 2. Enregistre l’utilisateur
    //
    echo "<b>🧾 Enregistrement de l’utilisateur dans la région :</b><br><pre>";

    $registerUrl = $fleetBaseUrl . '/api/1/users/' . $accountId . '/register';
    $registerCurl = curl_init($registerUrl);
    curl_setopt_array($registerCurl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $userToken,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode([]) // Vide
    ]);
    $registerResponse = curl_exec($registerCurl);
    $registerHttpCode = curl_getinfo($registerCurl, CURLINFO_HTTP_CODE);
    curl_close($registerCurl);

    echo "HTTP Status: $registerHttpCode\n";
    echo $registerResponse . "</pre>";
}

//
// 🚗 3. Appel /vehicles
//
echo "<b>🚗 /vehicles :</b><br><pre>";
$vehiclesUrl = $fleetBaseUrl . '/api/1/vehicles';
$vehiclesCurl = curl_init($vehiclesUrl);
curl_setopt_array($vehiclesCurl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $userToken,
        'Content-Type: application/json'
    ]
]);
$vehiclesResponse = curl_exec($vehiclesCurl);
$vehiclesHttpCode = curl_getinfo($vehiclesCurl, CURLINFO_HTTP_CODE);
curl_close($vehiclesCurl);

echo "HTTP Status: $vehiclesHttpCode\n";
echo $vehiclesResponse . "</pre>";