<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();

// Chargement des variables d'environnement
$clientId     = $_ENV['TESLA_CLIENT_ID'];
$clientSecret = $_ENV['TESLA_CLIENT_SECRET'];
$redirectUri  = $_ENV['TESLA_REDIRECT_URI'];
$domain       = $_ENV['TESLA_DOMAIN'] ?? 'app.jeromemarlier.com';
$scope        = 'openid offline_access';

// 🔒 Étape 1 : Enregistrement partenaire (si nécessaire)
$registerLock = __DIR__ . '/.partner_registered.lock';

if (!file_exists($registerLock)) {
    // Étape 1.1 : Obtenir le token partenaire
    $fields = http_build_query([
        'grant_type'    => 'client_credentials',
        'client_id'     => $clientId,
        'client_secret' => $clientSecret,
        'scope'         => 'openid vehicle_device_data vehicle_cmds vehicle_charging_cmds',
        'audience'      => 'https://fleet-api.prd.eu.vn.cloud.tesla.com',
    ]);

    $ch = curl_init('https://fleet-auth.prd.vn.cloud.tesla.com/oauth2/v3/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_POSTFIELDS     => $fields,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if (!isset($data['access_token'])) {
        exit('❌ Impossible d’obtenir le partner token.');
    }

    // Étape 1.2 : Enregistrer le domaine du partenaire
    $ch2 = curl_init('https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/partner_accounts');
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $data['access_token'],
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode(['domain' => $domain]),
    ]);
    $response2 = curl_exec($ch2);
    $httpCode  = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);

if ($httpCode === 200 || $httpCode === 422) {
    file_put_contents($registerLock, 'OK');
    error_log("ℹ️ Enregistrement partenaire déjà existant ou réussi à " . date('c'));
} else {
    exit("❌ Échec de l’enregistrement partenaire ($httpCode).");
}
}

// 🚀 Étape 2 : Préparer la redirection OAuth

// Anti-CSRF
$state = bin2hex(random_bytes(8));
$_SESSION['oauth_state'] = $state;

// PKCE
$codeVerifier           = bin2hex(random_bytes(32));
$_SESSION['code_verifier'] = $codeVerifier;
$codeChallenge          = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

// Construction de l’URL d’authentification
$authorizeUrl = 'https://auth.tesla.com/oauth2/v3/authorize?' . http_build_query([
    'response_type'         => 'code',
    'client_id'             => $clientId,
    'redirect_uri'          => $redirectUri,
    'scope'                 => $scope,
    'state'                 => $state,
    'code_challenge'        => $codeChallenge,
    'code_challenge_method' => 'S256',
]);

// Redirection vers Tesla pour autorisation
header("Location: $authorizeUrl");
exit;