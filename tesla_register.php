<?php

require_once __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;

// === CONFIGURATION ===
$clientId = 'beeaef23-0147-41b2-a4a5-870463390983'; // Remplace avec ton vrai client ID
$privateKeyPath = __DIR__ . '/private-key.pem'; // Clé privée EC (ES256)
$regionUrl = 'https://fleet-api.prd.eu.vn.cloud.tesla.com/oauth2/v3/partner/register'; // Europe (change si besoin)

// === Générer le JWT (partner_auth_token) ===
$privateKey = file_get_contents($privateKeyPath);
if (!$privateKey) {
    die("❌ Impossible de lire la clé privée : $privateKeyPath\n");
}

$now = time();
$payload = [
    'iss' => $clientId,
    'sub' => $clientId,
    'aud' => 'https://fleet-api.prd.eu.vn.cloud.tesla.com',
    'iat' => $now,
    'exp' => $now + 300, // Token valable 5 min
];

$jwt = JWT::encode($payload, $privateKey, 'ES256');

// === Appel de l’endpoint /partner/register ===
$ch = curl_init($regionUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $jwt,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => '{}',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// === Résultat ===
echo "Code HTTP : $httpCode\n";
if ($error) {
    echo "Erreur cURL : $error\n";
} else {
    echo "Réponse Tesla :\n$response\n";
}