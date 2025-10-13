<?php

require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// === CONFIGURATION ===
$client_id = 'c9c40292-ddb3-4a87-9cc0-5a0193081024';
$privateKeyPath = 'private-key.pem'; // Clé EC privée au format PEM
$fleetApiUrl = 'https://fleet-api.prd.na.vn.cloud.tesla.com';

// === CHARGER LA CLÉ PRIVÉE ===
$privateKey = file_get_contents($privateKeyPath);
if (!$privateKey) {
    die("Erreur de lecture de la clé privée");
}

// === GÉNÉRER LE JWT ===
$now = time();
$payload = [
    "iss" => $client_id,
    "sub" => $client_id,
    "aud" => $fleetApiUrl,
    "iat" => $now,
    "exp" => $now + 3600, // 1 heure
];

// ES256 = algorithme ECDSA utilisant la courbe P-256
$jwt = JWT::encode($payload, $privateKey, 'ES256');

// === FAIRE LA REQUÊTE VERS /oauth/token ===
$body = [
    "grant_type" => "client_credentials",
    "client_id" => $client_id,
    "client_assertion_type" => "urn:ietf:params:oauth:client-assertion-type:jwt-bearer",
    "client_assertion" => $jwt,
    "scope" => "fleet_api:vehicles:read"
];

$ch = curl_init("{$fleetApiUrl}/oauth/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// === AFFICHER LA RÉPONSE ===
echo "HTTP $httpCode\n";
echo "Réponse :\n$response\n";