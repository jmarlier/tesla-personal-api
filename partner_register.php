<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); $dotenv->load();

// Re-génère un PARTNER access_token (client_credentials)
$fields = http_build_query([
    'grant_type' => 'client_credentials',
    'client_id' => $_ENV['TESLA_CLIENT_ID'],
    'client_secret' => $_ENV['TESLA_CLIENT_SECRET'],
    'scope' => 'openid vehicle_device_data vehicle_cmds vehicle_charging_cmds',
    'audience' => 'https://fleet-api.prd.eu.vn.cloud.tesla.com'
]);

$ch = curl_init('https://fleet-auth.prd.vn.cloud.tesla.com/oauth2/v3/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_POSTFIELDS => $fields
]);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);

// On vérifie le token
if (!isset($data['access_token'])) {
    exit("❌ Partner access token non reçu");
}

$partnerToken = $data['access_token'];

// Appel à /partner_accounts
$ch2 = curl_init('https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/partner_accounts');
curl_setopt_array($ch2, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $partnerToken,
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'domain' => $_ENV['TESLA_DOMAIN'] ?? 'app.jeromemarlier.com'
    ])
]);

$response2 = curl_exec($ch2);
$httpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

// Affiche le résultat
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'http_code' => $httpCode,
    'response' => json_decode($response2, true)
], JSON_PRETTY_PRINT);