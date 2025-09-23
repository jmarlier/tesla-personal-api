<?php
// Affiche les erreurs PHP
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Charger le token depuis tokens.json
$tokensFile = __DIR__ . '/tokens.json';

if (!file_exists($tokensFile)) {
    exit('❌ Fichier tokens.json introuvable. Veuillez d’abord vous authentifier via login.php');
}

$tokens = json_decode(file_get_contents($tokensFile), true);
$accessToken = $tokens['access_token'] ?? null;

if (!$accessToken) {
    exit('❌ access_token manquant dans tokens.json');
}

// Appel à /vehicles
$ch = curl_init('https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/vehicles');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
    ],
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Affichage du résultat
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'http_code' => $httpCode,
    'response'  => json_decode($response, true),
], JSON_PRETTY_PRINT);