<?php
session_start();

// Génération d'un code_verifier aléatoire (à stocker pour callback.php)
$codeVerifier = bin2hex(random_bytes(32));
$_SESSION['code_verifier'] = $codeVerifier;

// Calcul du code_challenge
$codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

$clientId = 'c9c40292-ddb3-4a87-9cc0-5a0193081024';
$redirectUri = 'https://app.jeromemarlier.com/callback';
$scope = 'openid offline_access';
$state = bin2hex(random_bytes(8));
$_SESSION['oauth_state'] = $state;

// Construction de l'URL d'auth
$url = 'https://auth.tesla.com/oauth2/v3/authorize?' . http_build_query([
    'response_type' => 'code',
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'scope' => $scope,
    'state' => $state,
    'code_challenge' => $codeChallenge,
    'code_challenge_method' => 'S256',
]);

// Redirection vers Tesla
header("Location: $url");
exit;