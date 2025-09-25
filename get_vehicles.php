<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$tokenFile = __DIR__ . '/tokens.json';
if (!file_exists($tokenFile)) {
    die('❌ Fichier tokens.json introuvable. Lance login.php d’abord.');
}

$tokens = json_decode(file_get_contents($tokenFile), true);

if (!isset($tokens['access_token']) || !isset($tokens['created_at']) || !isset($tokens['expires_in'])) {
    die('❌ Token invalide ou incomplet.');
}

// Vérifie l’expiration du token
$now = time();
$expiresAt = $tokens['created_at'] + $tokens['expires_in'];

if ($now >= $expiresAt) {
    die('❌ Token expiré. Lance login.php pour le régénérer.');
}

$accessToken = $tokens['access_token'];

// Appel vers l’API Fleet
$ch = curl_init('https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/vehicles');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Affichage HTML
header('Content-Type: text/html; charset=utf-8');
echo "<h2>🚗 Appel API : /vehicles</h2>";
echo "<p><strong>HTTP Status:</strong> {$httpCode}</p>";

if ($httpCode === 200) {
    $vehicles = json_decode($response, true);
    echo "<h3>Véhicules trouvés :</h3>";
    echo "<pre>" . htmlspecialchars(json_encode($vehicles, JSON_PRETTY_PRINT)) . "</pre>";
} else {
    echo "<p>❌ Erreur :</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$responseHeaders = substr($response, 0, $headerSize);
$responseBody = substr($response, $headerSize);

// Recherche du txid dans les headers
preg_match('/x-txid:\s*(.+)/i', $responseHeaders, $matches);
$txid = $matches[1] ?? 'non trouvé';

echo "<p><strong>TXID :</strong> " . htmlspecialchars($txid) . "</p>";
?>