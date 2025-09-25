<?php
require __DIR__ . '/vendor/autoload.php';

// Option : charger depuis session
session_start();
if (isset($_SESSION['access_token'])) {
    $accessToken = $_SESSION['access_token'];
} else {
    // Sinon, charger depuis fichier tokens.json
    $tokenPath = __DIR__ . '/partner.json';
    if (!file_exists($tokenPath)) {
        die('❌ Aucun token trouvé. Lance login.php d’abord.');
    }

    $tokens = json_decode(file_get_contents($tokenPath), true);
    if (!isset($tokens['access_token'])) {
        die('❌ Token invalide ou incomplet.');
    }

    $accessToken = $tokens['access_token'];
}

// Requête vers /vehicles
$ch = curl_init('https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/vehicles');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Affichage du résultat
header('Content-Type: text/html; charset=utf-8');
echo "<h2>📡 Requête API : /vehicles</h2>";
echo "<p><strong>HTTP Status:</strong> $httpCode</p>";

if ($httpCode === 200) {
    $vehicles = json_decode($response, true);
    echo "<pre>" . htmlspecialchars(json_encode($vehicles, JSON_PRETTY_PRINT)) . "</pre>";
} else {
    echo "<p>❌ Erreur API :</p><pre>" . htmlspecialchars($response) . "</pre>";
}
?>