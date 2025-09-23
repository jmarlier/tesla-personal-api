<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
session_start();

$clientId     = $_ENV['TESLA_CLIENT_ID'];
$clientSecret = $_ENV['TESLA_CLIENT_SECRET'];
$redirectUri  = $_ENV['TESLA_REDIRECT_URI'];

// Vérifie le code de retour
if (!isset($_GET['code'])) {
    exit('❌ Code manquant dans le callback');
}

$code         = $_GET['code'];
$state        = $_GET['state'] ?? '';
$codeVerifier = $_SESSION['code_verifier'] ?? '';

if ($state !== ($_SESSION['oauth_state'] ?? '')) {
    exit('❌ Erreur de vérification du state');
}

// Appel à l’endpoint /token
$tokenUrl = 'https://auth.tesla.com/oauth2/v3/token';
$postData = http_build_query([
    'grant_type'    => 'authorization_code',
    'client_id'     => $clientId,
    'client_secret' => $clientSecret,
    'code'          => $code,
    'code_verifier' => $codeVerifier,
    'redirect_uri'  => $redirectUri
]);

$ch = curl_init($tokenUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_POSTFIELDS     => $postData,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

// Vérifie que tout est bon
if (!isset($data['access_token'], $data['refresh_token'], $data['id_token'])) {
    echo "<h2>❌ Échec de l’obtention des tokens</h2><pre>";
    echo "HTTP $httpCode\n";
    echo $response;
    echo "</pre>";
    exit;
}

// Sauvegarde dans un fichier tokens.json
file_put_contents(__DIR__ . '/tokens.json', json_encode($data, JSON_PRETTY_PRINT));

echo "<h2>✅ Tokens enregistrés dans <code>tokens.json</code> :</h2><pre>";
echo json_encode($data, JSON_PRETTY_PRINT);
echo "</pre>";

// Affichage final de /vehicles
echo "HTTP Status: $vehiclesHttpCode\n";
echo $vehiclesResponse . "</pre>";

// 🔗 Lien vers vehicles.php
echo '<hr><p>➡️ <a href="vehicles.php">Voir les véhicules via /vehicles</a></p>';