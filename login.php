<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
session_start();

$clientId = $_ENV['TESLA_CLIENT_ID'];
$redirectUri = $_ENV['TESLA_REDIRECT_URI'];
$scope = urlencode('openid offline_access vehicle_device_data vehicle_cmds vehicle_charging_cmds');
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$tokenFile = __DIR__ . '/tokens.json';

// 🔍 Vérifie si le fichier de token existe
if (file_exists($tokenFile)) {
    $tokens = json_decode(file_get_contents($tokenFile), true);
    $expiresAt = $tokens['created_at'] + $tokens['expires_in'];

    // ⏳ Si access_token encore valide, on le garde
    if (time() < $expiresAt) {
        $_SESSION['access_token'] = $tokens['access_token'];
        echo "✅ Token valide. Vous êtes connecté.";
        exit;
    }

    // 🔁 Sinon, on tente de rafraîchir
    $refreshToken = $tokens['refresh_token'];
    $newTokens = refreshTeslaToken($clientId, $refreshToken);

    if ($newTokens && isset($newTokens['access_token'])) {
        $newTokens['created_at'] = time();
        file_put_contents($tokenFile, json_encode($newTokens));
        $_SESSION['access_token'] = $newTokens['access_token'];
        echo "🔁 Token rafraîchi avec succès.";
        exit;
    }
}

// 🚪 Sinon, on lance le login Tesla
$redirectUriEncoded = urlencode($redirectUri);
$authUrl = "https://auth.tesla.com/oauth2/v3/authorize?" .
    "client_id={$clientId}&" .
    "redirect_uri={$redirectUriEncoded}&" .
    "response_type=code&" .
    "scope={$scope}&" .
    "state={$state}&" .
    "prompt=login";

header("Location: $authUrl");
exit;

// 🧠 Fonction de rafraîchissement
function refreshTeslaToken($clientId, $refreshToken) {
    $data = http_build_query([
        'grant_type' => 'refresh_token',
        'client_id' => $clientId,
        'refresh_token' => $refreshToken
    ]);

    $ch = curl_init('https://fleet-auth.prd.vn.cloud.tesla.com/oauth2/v3/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) return null;

    return json_decode($response, true);
}