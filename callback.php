<?php

/**
 * Callback OAuth 2.0 pour l'authentification utilisateur Tesla
 * Ce fichier reçoit le code d'autorisation et l'échange contre un access token
 */

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();

// Configuration
$clientId = $_ENV['TESLA_CLIENT_ID'];
$clientSecret = $_ENV['TESLA_CLIENT_SECRET'] ?? null;
$redirectUri = $_ENV['TESLA_REDIRECT_URI'];
$tokenUrl = $_ENV['TESLA_TOKEN_URL'] ?? 'https://fleet-auth.prd.vn.cloud.tesla.com/oauth2/v3/token';
$audience = $_ENV['TESLA_AUDIENCE'] ?? null;

// Vérifier le code d'autorisation
$code = $_GET['code'] ?? null;
$state = $_GET['state'] ?? null;

if (!$code) {
    http_response_code(400);
    die('❌ Code d\'autorisation manquant');
}

// Vérifier le state pour la sécurité CSRF
if (!isset($_SESSION['oauth_state']) || $state !== $_SESSION['oauth_state']) {
    http_response_code(400);
    die('❌ État OAuth invalide (possible attaque CSRF)');
}

// Préparer les données pour l'échange de code
$postData = [
    'grant_type' => 'authorization_code',
    'client_id' => $clientId,
    'code' => $code,
    'redirect_uri' => $redirectUri,
];

// Ajouter client_secret si disponible
if ($clientSecret) {
    $postData['client_secret'] = $clientSecret;
}

// Ajouter audience si disponible
if ($audience) {
    $postData['audience'] = $audience;
}

$data = http_build_query($postData);

// Échanger le code contre un access token
$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!$response) {
    http_response_code(500);
    die('❌ Erreur lors de la requête token');
}

$tokens = json_decode($response, true);

if ($httpCode !== 200 || !isset($tokens['access_token'])) {
    http_response_code(500);
    echo '<h2>❌ Erreur de récupération du token</h2>';
    echo '<pre>' . htmlspecialchars($response) . '</pre>';
    die();
}

// Sauvegarder les tokens
$_SESSION['access_token'] = $tokens['access_token'];
$_SESSION['refresh_token'] = $tokens['refresh_token'] ?? null;

// Sauvegarder dans un fichier JSON
$tokenFile = __DIR__ . '/var/tokens.json';
$varDir = dirname($tokenFile);

if (!is_dir($varDir)) {
    mkdir($varDir, 0755, true);
}

$tokenData = [
    'access_token' => $tokens['access_token'],
    'refresh_token' => $tokens['refresh_token'] ?? null,
    'expires_in' => $tokens['expires_in'] ?? 28800,
    'created_at' => time(),
];

if (isset($tokens['id_token'])) {
    $tokenData['id_token'] = $tokens['id_token'];
}

file_put_contents($tokenFile, json_encode($tokenData, JSON_PRETTY_PRINT));

// Nettoyer le state OAuth
unset($_SESSION['oauth_state']);

// Affichage du succès
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification Réussie - Tesla</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 800px;
            width: 100%;
        }

        h1 {
            color: #28a745;
            margin-bottom: 20px;
            font-size: 28px;
        }

        .success-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .token-info {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .token-info strong {
            color: #28a745;
            display: block;
            margin-bottom: 5px;
        }

        .token-value {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 10px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-wrap: break-word;
            margin-top: 5px;
        }

        .button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }

        .button:hover {
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="success-icon">✅</div>
        <h1>Authentification réussie !</h1>
        <p>Vous êtes maintenant connecté à l'API Tesla Fleet.</p>

        <div class="token-info">
            <strong>Access Token:</strong>
            <div class="token-value"><?= htmlspecialchars(substr($tokens['access_token'], 0, 100)) ?>...</div>
        </div>

        <?php if (isset($tokens['refresh_token'])): ?>
            <div class="token-info">
                <strong>Refresh Token:</strong>
                <div class="token-value"><?= htmlspecialchars(substr($tokens['refresh_token'], 0, 100)) ?>...</div>
            </div>
        <?php endif; ?>

        <div class="token-info">
            <strong>Expire dans:</strong>
            <?= htmlspecialchars($tokens['expires_in'] ?? 'N/A') ?> secondes
        </div>

        <?php if (isset($tokens['id_token'])): ?>
            <div class="token-info">
                <strong>ID Token (JWT):</strong>
                <div class="token-value"><?= htmlspecialchars(substr($tokens['id_token'], 0, 100)) ?>...</div>
            </div>
        <?php endif; ?>

        <a href="index.php" class="button">← Retour à l'accueil</a>
        <a href="dashboard.php" class="button">Tableau de bord →</a>
    </div>
</body>

</html>