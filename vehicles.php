<?php
session_start();

// Vérifie que le token est bien en session
if (!isset($_SESSION['access_token'])) {
    die('❌ Aucun token trouvé. Veuillez vous connecter via login.php');
}

$accessToken = $_SESSION['access_token'];

$ch = curl_init('https://owner-api.teslamotors.com/api/1/vehicles');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $vehicles = json_decode($response, true);
    echo "<h2>🚗 Liste des véhicules :</h2>";
    echo "<pre>" . htmlspecialchars(json_encode($vehicles, JSON_PRETTY_PRINT)) . "</pre>";
} else {
    echo "❌ Erreur API (HTTP $httpCode) :<br><pre>$response</pre>";
}