<?php
// callback.php

ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Chargement des variables d'environnement
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Vérification des paramètres obligatoires
if (!isset($_GET['code'])) {
    exit('❌ Code d’autorisation manquant.');
}

if (!isset($_SESSION['code_verifier'])) {
    exit('❌ Le code_verifier est introuvable en session.');
}

$code = $_GET['code'];
$codeVerifier = $_SESSION['code_verifier'];

// Récupération des infos sensibles depuis .env
$clientId = $_ENV['TESLA_CLIENT_ID'];
$clientSecret = $_ENV['TESLA_CLIENT_SECRET'];
$redirectUri = $_ENV['TESLA_REDIRECT_URI'];

// Préparation de la requête POST vers Tesla
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
    echo "<h3>❌ Erreur lors de la récupération des tokens Tesla</h3>";
    echo "<pre>";
    print_r(error_get_last());
    echo "</pre>";
    exit;
}

// Traitement de la réponse
$tokens = json_decode($response, true);

if (isset($tokens['error'])) {
    echo "<h3>❌ Erreur retournée par Tesla :</h3>";
    echo "<pre>";
    print_r($tokens);
    echo "</pre>";
    exit;
}

// Affichage (temporaire — à supprimer en production)
echo "<h3>✅ Tokens reçus :</h3>";
echo "<pre>";
print_r($tokens);
echo "</pre>";

// TODO : ici tu peux stocker les tokens (en DB ou fichier sécurisé) et rediriger vers ton interface