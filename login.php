<?php

require_once __DIR__ . '/vendor/autoload.php'; // Composer pour dotenv

use Symfony\Component\Dotenv\Dotenv;

// === CHARGER LES VARIABLES D’ENV ===
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$clientId     = $_ENV['TESLA_CLIENT_ID'];
$clientSecret = $_ENV['TESLA_CLIENT_SECRET'];
$domain       = $_ENV['TESLA_DOMAIN'];
$audience     = 'https://fleet-api.prd.eu.vn.cloud.tesla.com';
$tokenUrl     = 'https://fleet-auth.prd.vn.cloud.tesla.com/oauth2/v3/token';
$apiBaseUrl   = 'https://fleet-api.prd.eu.vn.cloud.tesla.com';

// === ÉTAPE 1 : Authentification OAuth2
$tokenResponse = fetchToken($clientId, $clientSecret, $audience, $tokenUrl);

if (!$tokenResponse || !isset($tokenResponse['access_token'])) {
    exit("❌ Échec login OAuth2 :\n" . json_encode($tokenResponse, JSON_PRETTY_PRINT));
}

$accessToken = $tokenResponse['access_token'];
saveToFile('tokens.json', $tokenResponse);
echo "✅ Access token obtenu et enregistré dans tokens.json\n\n";

// === ÉTAPE 2 : Enregistrement du domaine partenaire
$partnerResponse = registerPartnerAccount($accessToken, $apiBaseUrl, $domain);

echo "✅ Résultat partner_account :\n";
echo json_encode($partnerResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

// === Fonctions ===

function fetchToken($clientId, $clientSecret, $audience, $tokenUrl): ?array {
    $fields = http_build_query([
        'grant_type' => 'client_credentials',
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'scope' => 'openid vehicle_device_data vehicle_cmds vehicle_charging_cmds',
        'audience' => $audience
    ]);

    $ch = curl_init($tokenUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_POSTFIELDS => $fields
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status !== 200) {
        echo "❌ Erreur HTTP lors de la récupération du token ($status)\n";
        return json_decode($response, true);
    }

    return json_decode($response, true);
}

function registerPartnerAccount(string $accessToken, string $apiBaseUrl, string $domain): array {
    $url = $apiBaseUrl . '/api/1/partner_accounts';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode(['domain' => $domain])
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'http_status' => $status,
        'response' => json_decode($response, true),
    ];
}

function saveToFile(string $filename, array $data): void {
    file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}