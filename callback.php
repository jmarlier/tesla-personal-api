<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
session_start();

$clientId     = $_ENV['TESLA_CLIENT_ID'];
$clientSecret = $_ENV['TESLA_CLIENT_SECRET'];
$redirectUri  = $_ENV['TESLA_REDIRECT_URI'];
$audience = $_ENV['TESLA_AUDIENCE'];

$code = $_GET['code'] ?? null;

if (!$code) {
    die('Code manquant');
}

$data = http_build_query([
    'grant_type' => 'authorization_code',
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'code' => $code,
    'audience' => $audience,
    'redirect_uri' => $redirectUri,
]);

$ch = curl_init('https://fleet-auth.prd.vn.cloud.tesla.com/oauth2/v3/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);
curl_close($ch);

if (!$response) {
    die('Erreur lors de la requête token');
}

$tokens = json_decode($response, true);
if (!isset($tokens['access_token'])) {
    die('Erreur de récupération du token: ' . $response);
}

// Sauvegarder dans ta session ou base de données
$_SESSION['access_token'] = $tokens['access_token'];
$_SESSION['refresh_token'] = $tokens['refresh_token'];

echo "Authentification réussie. Tokens obtenus.";
?>