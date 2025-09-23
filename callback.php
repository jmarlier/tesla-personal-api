<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// 🔐 Vérification des paramètres
if (!isset($_GET['code'])) {
    exit('❌ Code d’autorisation manquant.');
}

if (!isset($_SESSION['code_verifier']) || !isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    exit('❌ Vérification CSRF échouée ou code_verifier manquant.');
}

// 📥 Récupération des variables
$code = $_GET['code'];
$codeVerifier = $_SESSION['code_verifier'];
$clientId = $_ENV['TESLA_CLIENT_ID'];
$clientSecret = $_ENV['TESLA_CLIENT_SECRET'];
$redirectUri = $_ENV['TESLA_REDIRECT_URI'];

// 🔁 Préparer la requête POST
$postData = http_build_query([
    'grant_type' => 'authorization_code',
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'code' => $code,
    'code_verifier' => $codeVerifier,
    'redirect_uri' => $redirectUri,
]);

$context = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $postData,
        'ignore_errors' => true
    ]
];

$response = file_get_contents('https://auth.tesla.com/oauth2/v3/token', false, stream_context_create($context));

if ($response === false) {
    echo "<h3>❌ Erreur lors de la récupération des tokens Tesla</h3><pre>";
    print_r(error_get_last());
    echo "</pre>";
    exit;
}

$tokens = json_decode($response, true);

if (isset($tokens['error'])) {
    echo "<h3>❌ Erreur retournée par Tesla :</h3><pre>";
    print_r($tokens);
    echo "</pre>";
    exit;
}

// ✅ Sauvegarder les tokens dans un fichier
file_put_contents(__DIR__ . '/tokens.json', json_encode($tokens, JSON_PRETTY_PRINT));

// ✅ Redirection ou affichage temporaire
header('Location: vehicles.php');
exit;