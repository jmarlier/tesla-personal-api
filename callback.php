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

$code = $_GET['code'];
$state = $_GET['state'] ?? '';
$codeVerifier = $_SESSION['code_verifier'] ?? '';

if ($state !== $_SESSION['oauth_state']) {
    exit('❌ Erreur de vérification du state');
}

// 🔐 Token endpoint
$fields = http_build_query([
    'grant_type' => 'authorization_code',
    'client_id' => $_ENV['TESLA_CLIENT_ID'],
    'code' => $code,
    'code_verifier' => $codeVerifier,
    'redirect_uri' => $_ENV['TESLA_REDIRECT_URI']
]);

$ch = curl_init('https://auth.tesla.com/oauth2/v3/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_POSTFIELDS => $fields,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<b>📥 Réponse brute de Tesla :</b>\nHTTP Code: $httpCode\nErreur cURL: $error\n\n$response</pre>";

$data = json_decode($response, true);
if (!isset($data['access_token'])) {
    exit("❌ Aucun token utilisateur");
}

// Affiche le token
echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";

$userToken = $data['access_token'];
$idToken   = $data['id_token'] ?? '';

//
// 🧠 Extraire la région (ou_code) depuis l'id_token
//
$jwtPayload = explode('.', $idToken)[1] ?? '';
$jwtJson = base64_decode(strtr($jwtPayload, '-_', '+/'));
$jwt = json_decode($jwtJson, true);
$ouCode = strtoupper($jwt['ou_code'] ?? 'EU');

$fleetBaseUrl = match ($ouCode) {
    'NA' => 'https://fleet-api.prd.na.vn.cloud.tesla.com',
    'CN' => 'https://fleet-api.prd.cn.vn.cloud.tesla.com',
    default => 'https://fleet-api.prd.eu.vn.cloud.tesla.com',
};

//
// 🌍 /users/region
//
echo "<br><b>🌍 Région détectée :</b><br><pre>";

$regionCurl = curl_init($fleetBaseUrl . '/api/1/users/region');
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

$regionData = json_decode($regionResponse, true);
$accountId = $regionData['account_id'] ?? null;

//
// 🧾 Enregistrement utilisateur
//
if ($regionHttpCode == 412 && $accountId) {
    echo "<b>🧾 Enregistrement de l’utilisateur dans la région :</b><br><pre>";

    $registerCurl = curl_init($fleetBaseUrl . "/api/1/users/$accountId/register");
    curl_setopt_array($registerCurl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $userToken,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode([]),
    ]);
    $registerResponse = curl_exec($registerCurl);
    $registerHttpCode = curl_getinfo($registerCurl, CURLINFO_HTTP_CODE);
    curl_close($registerCurl);

    echo "HTTP Status: $registerHttpCode\n";
    echo $registerResponse . "</pre>";
}

//
// 🚗 Appel /vehicles
//
echo "<b>🚗 /vehicles :</b><br><pre>";
$vehiclesCurl = curl_init($fleetBaseUrl . '/api/1/vehicles');
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