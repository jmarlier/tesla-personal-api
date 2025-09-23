<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();

$clientId = $_ENV['TESLA_CLIENT_ID'];
$redirectUri = $_ENV['TESLA_REDIRECT_URI'];
$scope = 'openid offline_access vehicle_device_data vehicle_cmds';

// État anti-CSRF
$state = bin2hex(random_bytes(8));
$_SESSION['oauth_state'] = $state;

// PKCE
$codeVerifier = bin2hex(random_bytes(32));
$_SESSION['code_verifier'] = $codeVerifier;

$codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

// Construire l’URL d’authentification
$authorizeUrl = 'https://auth.tesla.com/oauth2/v3/authorize?' . http_build_query([
    'response_type' => 'code',
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'scope' => $scope,
    'state' => $state,
    'code_challenge' => $codeChallenge,
    'code_challenge_method' => 'S256',
]);

// Rediriger l’utilisateur vers Tesla
header("Location: $authorizeUrl");
exit;