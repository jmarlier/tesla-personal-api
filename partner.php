<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Chargement des identifiants
$clientId     = $_ENV['TESLA_CLIENT_ID'];
$clientSecret = $_ENV['TESLA_CLIENT_SECRET'];
$audience     = 'https://fleet-api.prd.na.vn.cloud.tesla.com';

// Requête de token partenaire
$fields = http_build_query([
    'grant_type'    => 'client_credentials',
    'client_id'     => $clientId,
    'client_secret' => $clientSecret,
    'scope'         => 'openid vehicle_device_data vehicle_cmds vehicle_charging_cmds',
    'audience'      => $audience,
]);

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

$data = json_decode($response, true);

// Affichage et sauvegarde
echo "<h2>🔑 Partner Token Response</h2><pre>";
echo "HTTP Status: $httpCode\n";
echo json_encode($data, JSON_PRETTY_PRINT);
echo "</pre>";

if (isset($data['access_token'])) {
    file_put_contents(__DIR__ . '/partner.json', json_encode($data, JSON_PRETTY_PRINT));
    echo "<p>✅ Token partenaire enregistré dans <code>partner.json</code></p>";
} else {
    echo "<p>❌ Échec de récupération du token partenaire</p>";
}