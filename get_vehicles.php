<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Chargement du token
$tokenFile = __DIR__ . '/tokens.json';
if (!file_exists($tokenFile)) {
    die('❌ Fichier tokens.json introuvable. Lance login.php d’abord.');
}

$tokens = json_decode(file_get_contents($tokenFile), true);
if (!isset($tokens['access_token'], $tokens['created_at'], $tokens['expires_in'])) {
    die('❌ Token invalide ou incomplet.');
}

// Vérifie l’expiration
$now = time();
$expiresAt = $tokens['created_at'] + $tokens['expires_in'];
if ($now >= $expiresAt) {
    die('❌ Token expiré. Relance login.php');
}

$accessToken = $tokens['access_token'];

// Appel vers l’API
$url = 'https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/vehicles';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true, // important pour récupérer les en-têtes !
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);

$rawHeaders = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

// Extraction du txid si présent
preg_match('/x-txid:\s*(.*)/i', $rawHeaders, $match);
$txid = $match[1] ?? 'Non trouvé';

header('Content-Type: text/html; charset=utf-8');
echo "<h2>🚗 Appel API : /vehicles</h2>";
echo "<p><strong>HTTP Status:</strong> {$httpCode}</p>";
echo "<p><strong>TXID:</strong> " . htmlspecialchars($txid) . "</p>";

if ($httpCode === 200) {
    $vehicles = json_decode($body, true);
    echo "<h3>Véhicules trouvés :</h3>";
    echo "<pre>" . htmlspecialchars(json_encode($vehicles, JSON_PRETTY_PRINT)) . "</pre>";
} else {
    echo "<p>❌ Erreur :</p>";
    echo "<pre>" . htmlspecialchars($body) . "</pre>";
}