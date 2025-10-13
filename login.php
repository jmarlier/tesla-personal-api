<?php

/**
 * Point d'entrée pour l'authentification utilisateur Tesla OAuth 2.0
 * Flux: Authorization Code avec PKCE
 */

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();

$clientId = $_ENV['TESLA_CLIENT_ID'];
$redirectUri = $_ENV['TESLA_REDIRECT_URI'];
$authUrl = $_ENV['TESLA_AUTH_URL'] ?? 'https://auth.tesla.com/oauth2/v3/authorize';
$scope = $_ENV['TESLA_USER_SCOPES'] ?? 'openid offline_access vehicle_device_data vehicle_cmds vehicle_charging_cmds';

// Générer un state pour la sécurité CSRF
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

// Chemin vers le fichier de tokens
$tokenFile = __DIR__ . '/var/tokens.json';

// 🔍 Vérifier si un token existe et est valide
if (file_exists($tokenFile)) {
    $tokens = json_decode(file_get_contents($tokenFile), true);

    if (isset($tokens['created_at']) && isset($tokens['expires_in'])) {
        $expiresAt = $tokens['created_at'] + $tokens['expires_in'];

        // ⏳ Si access_token encore valide
        if (time() < $expiresAt) {
            $_SESSION['access_token'] = $tokens['access_token'];
            header('Location: dashboard.php');
            exit;
        }

        // 🔁 Tenter de rafraîchir le token
        if (isset($tokens['refresh_token'])) {
            $newTokens = refreshTeslaToken($clientId, $tokens['refresh_token']);

            if ($newTokens && isset($newTokens['access_token'])) {
                $newTokens['created_at'] = time();

                // Créer le dossier var si nécessaire
                $varDir = dirname($tokenFile);
                if (!is_dir($varDir)) {
                    mkdir($varDir, 0755, true);
                }

                file_put_contents($tokenFile, json_encode($newTokens, JSON_PRETTY_PRINT));
                $_SESSION['access_token'] = $newTokens['access_token'];
                header('Location: dashboard.php');
                exit;
            }
        }
    }
}

// 🚪 Rediriger vers Tesla pour l'authentification
$authParams = http_build_query([
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => $scope,
    'state' => $state,
    'prompt' => 'login'
]);

header("Location: {$authUrl}?{$authParams}");
exit;

/**
 * Rafraîchir le token Tesla
 */
function refreshTeslaToken(string $clientId, string $refreshToken): ?array
{
    $tokenUrl = $_ENV['TESLA_TOKEN_URL'] ?? 'https://fleet-auth.prd.vn.cloud.tesla.com/oauth2/v3/token';

    $data = http_build_query([
        'grant_type' => 'refresh_token',
        'client_id' => $clientId,
        'refresh_token' => $refreshToken
    ]);

    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return null;
    }

    return json_decode($response, true);
}
