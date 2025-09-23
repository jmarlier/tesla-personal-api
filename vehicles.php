<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Lecture du token partenaire
$tokenPath = __DIR__ . '/partner.json';
if (!file_exists($tokenPath)) {
    exit("❌ Aucun fichier partner.json trouvé");
}

$tokens = json_decode(file_get_contents($tokenPath), true);
$accessToken = $tokens['access_token'] ?? null;

if (!$accessToken) {
    exit("❌ Aucun token d'accès partenaire trouvé");
}

// 🔥 Requête vers /vehicles (en NA, pour partenaires)
echo "<h2>🚗 /vehicles (via Partner Token)</h2><pre>";

$vehiclesCurl = curl_init('https://owner-api.teslamotors.com/api/1/vehicles');
curl_setopt_array($vehiclesCurl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]
]);
$vehiclesResponse = curl_exec($vehiclesCurl);
$vehiclesHttpCode = curl_getinfo($vehiclesCurl, CURLINFO_HTTP_CODE);
curl_close($vehiclesCurl);

echo "HTTP Status: $vehiclesHttpCode\n";
echo $vehiclesResponse . "</pre>";