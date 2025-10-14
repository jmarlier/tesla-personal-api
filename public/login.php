<?php

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * ÉTAPE 3.1 : Initiation du flux OAuth2
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * Ce fichier génère l'URL d'autorisation OAuth2 et redirige l'utilisateur
 * vers auth.tesla.com pour qu'il autorise l'application.
 * 
 * FLOW :
 *   1. Génère un state (token CSRF pour la sécurité)
 *   2. Construit l'URL d'autorisation OAuth2
 *   3. Redirige vers auth.tesla.com
 *   4. Tesla demandera à l'utilisateur de se connecter et d'autoriser
 *   5. Tesla redirigera vers callback.php avec le code d'autorisation
 * 
 * PARAMÈTRES ENVOYÉS :
 *   - client_id : ID de votre application
 *   - redirect_uri : URL de callback
 *   - response_type : code (pour Authorization Code Flow)
 *   - scope : permissions demandées
 *   - state : token CSRF pour sécurité
 * 
 * ═══════════════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// ═══════════════════════════════════════════════════════════════════════════
// 1. CHARGEMENT DE LA CONFIGURATION
// ═══════════════════════════════════════════════════════════════════════════

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

session_start();

// Vérifier la configuration
if (empty($_ENV['TESLA_CLIENT_ID']) || empty($_ENV['TESLA_AUTH_URL']) || empty($_ENV['TESLA_REDIRECT_URI'])) {
    die('❌ ERREUR : Configuration OAuth2 incomplète dans .env');
}

$clientId = $_ENV['TESLA_CLIENT_ID'];
$authUrl = $_ENV['TESLA_AUTH_URL'];
$redirectUri = $_ENV['TESLA_REDIRECT_URI'];
$scopes = $_ENV['TESLA_USER_SCOPES'] ?? 'openid offline_access vehicle_device_data vehicle_cmds vehicle_charging_cmds';

// ═══════════════════════════════════════════════════════════════════════════
// 2. GÉNÉRATION DU STATE (Protection CSRF)
// ═══════════════════════════════════════════════════════════════════════════

// Générer un state aléatoire pour protéger contre les attaques CSRF
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

// ═══════════════════════════════════════════════════════════════════════════
// 3. CONSTRUCTION DE L'URL D'AUTORISATION
// ═══════════════════════════════════════════════════════════════════════════

$authParams = [
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => $scopes,
    'state' => $state,
];

$authorizationUrl = $authUrl . '?' . http_build_query($authParams);

// ═══════════════════════════════════════════════════════════════════════════
// 4. MODE DEBUG (optionnel) - Affichage de l'URL au lieu de rediriger
// ═══════════════════════════════════════════════════════════════════════════

// Si vous voulez voir l'URL avant de rediriger, décommentez cette section :
$debug = isset($_GET['debug']) && $_GET['debug'] === '1';

if ($debug) {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Debug OAuth2</title>";
    echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;} .container{background:white;padding:30px;border-radius:10px;max-width:800px;margin:0 auto;} h1{color:#333;} pre{background:#f8f9fa;padding:15px;border-left:4px solid #3E6AE1;overflow-x:auto;} .param{margin:10px 0;} .param strong{color:#3E6AE1;}</style>";
    echo "</head><body><div class='container'>";
    echo "<h1>🔍 Mode Debug - OAuth2 Authorization</h1>";
    echo "<h2>Paramètres de la requête :</h2>";

    foreach ($authParams as $key => $value) {
        echo "<div class='param'><strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($value) . "</div>";
    }

    echo "<h2>URL complète :</h2>";
    echo "<pre>" . htmlspecialchars($authorizationUrl) . "</pre>";

    echo "<br><a href='$authorizationUrl' style='display:inline-block;background:#3E6AE1;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;'>🚀 Continuer vers Tesla</a>";
    echo " <a href='index.php' style='display:inline-block;background:#6c757d;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;margin-left:10px;'>← Retour</a>";
    echo "</div></body></html>";
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════
// 5. REDIRECTION VERS TESLA
// ═══════════════════════════════════════════════════════════════════════════

// Log de la redirection (pour debug)
error_log("OAuth2 Redirect: Redirecting to Tesla auth.tesla.com");
error_log("State: $state");
error_log("Redirect URI: $redirectUri");

// Redirection vers Tesla
header('Location: ' . $authorizationUrl);
exit;
