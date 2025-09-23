<?php
ini_set('display_errors', 1);
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); $dotenv->load();
session_start();

if ($_GET['state'] !== $_SESSION['oauth_state']) {
    exit('❌ État CSRF invalide.');
}

$code = $_GET['code'] ?? null;
if (!$code) exit('❌ Aucun code reçu.');

$fields = http_build_query([
    'grant_type' => 'authorization_code',
    'client_id' => $_ENV['TESLA_CLIENT_ID'],
    'client_secret' => $_ENV['TESLA_CLIENT_SECRET'],
    'code' => $code,
    'code_verifier' => $_SESSION['code_verifier'],
    'redirect_uri' => $_ENV['TESLA_REDIRECT_URI'],
]);

$ch = curl_init('https://auth.tesla.com/oauth2/v3/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_POSTFIELDS => $fields,
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
if (!isset($data['access_token'])) exit("❌ Token utilisateur non reçu.");

file_put_contents('tokens.json', json_encode($data, JSON_PRETTY_PRINT));

header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT);