<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$clientId = $_ENV['TESLA_CLIENT_ID'];
$clientSecret = $_ENV['TESLA_CLIENT_SECRET'];
$audience = 'https://fleet-api.prd.eu.vn.cloud.tesla.com';

// Construction des paramètres POST
$fields = http_build_query([
    'grant_type'    => 'client_credentials',
    'client_id'     => $clientId,
    'client_secret' => $clientSecret,
    'scope'         => 'openid vehicle_device_data vehicle_cmds vehicle_charging_cmds',
    'audience'      => $audience
]);

// Appel CURL
$ch = curl_init('https://fleet-auth.prd.vn.cloud.tesla.com/oauth2/v3/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_POSTFIELDS     => $fields,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Affichage
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'http_status' => $httpCode,
    'response'    => json_decode($response, true),
], JSON_PRETTY_PRINT);