<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
session_start();

echo "<h2>🔑 <b>Token utilisateur reçu :</b></h2><pre>";

// Vérifie présence du `code`
if (!isset($_GET['code'])) {
    exit('❌ Code manquant dans le callback');
}

$code         = $_GET['code'];
$state        = $_GET['state'] ?? '';
$codeVerifier = $_SESSION['code_verifier'] ?? '';

if ($state !== $_SESSION['oauth_state']) {
    exit('❌ Erreur de vérification du state');
}

// Appel à l'endpoint de token
$tokenUrl = 'https://auth.tesla.com/oauth2/v3/token';

$fields = http_build_query([
    'grant_type'    => 'authorization_code',
    'client_id'     => $_ENV['TESLA_CLIENT_ID'],
    'code'          => $code,
    'code_verifier' => $codeVerifier,
    'redirect_uri'  => $_ENV['TESLA_REDIRECT_URI'],
]);

$ch = curl_init($tokenUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_POSTFIELDS     => $fields,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error    = curl_error($ch);
curl_close($ch);

// Affiche réponse brute
echo "<pre><b>📥 Réponse brute de Tesla :</b>\nHTTP Code: $httpCode\nErreur cURL: $error\n\n$response</pre>";

$data = json_decode($response, true);
if (!isset($data['access_token'])) {
    exit("❌ Aucun token utilisateur");
}

echo json_encode($data, JSON_PRETTY_PRINT);
echo "</pre>";

// Stocke en session
$_SESSION['access_token']  = $data['access_token'];
$_SESSION['refresh_token'] = $data['refresh_token'] ?? null;
$_SESSION['id_token']      = $data['id_token'] ?? null;
$_SESSION['expires_in']    = $data['expires_in'] ?? null;
$_SESSION['token_type']    = $data['token_type'] ?? null;

// 👉 À ce stade, tu peux rediriger vers dashboard ou continuer vers étape 5