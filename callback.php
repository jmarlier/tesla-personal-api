<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();

if ($_GET['state'] !== $_SESSION['oauth_state']) {
    exit('❌ État CSRF invalide.');
}

$code = $_GET['code'] ?? null;
if (!$code) {
    exit('❌ Aucun code reçu.');
}

$clientId = $_ENV['TESLA_CLIENT_ID'];
$redirectUri = $_ENV['TESLA_REDIRECT_URI'];
$codeVerifier = $_SESSION['code_verifier'];

$tokenUrl = 'https://auth.tesla.com/oauth2/v3/token';

$fields = http_build_query([
    'grant_type' => 'authorization_code',
    'client_id' => $clientId,
    'code' => $code,
    'code_verifier' => $codeVerifier,
    'redirect_uri' => $redirectUri,
]);

$ch = curl_init($tokenUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_POSTFIELDS => $fields,
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (!isset($data['access_token'])) {
    exit("❌ Erreur lors de l’échange token:\n$response");
}

// Enregistrer les tokens pour l’utilisateur
file_put_contents('user_tokens.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "✅ Utilisateur connecté. Token stocké dans user_tokens.json";