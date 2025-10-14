<?php

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * ÉTAPE 3.2 : Callback OAuth2 - Échange du code contre un token
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * Ce fichier reçoit le code d'autorisation depuis Tesla et l'échange
 * contre un access token utilisateur.
 * 
 * FLOW :
 *   1. Tesla redirige ici après que l'utilisateur ait autorisé l'app
 *   2. On reçoit le code d'autorisation dans l'URL (?code=XXX&state=YYY)
 *   3. On vérifie le state (protection CSRF)
 *   4. On échange le code contre un access token via POST
 *   5. On sauvegarde le token en session et dans un fichier JSON
 * 
 * REQUÊTE :
 *   POST https://auth.tesla.com/oauth2/v3/token
 * 
 * PARAMÈTRES :
 *   - grant_type: authorization_code
 *   - client_id: ID de votre application
 *   - client_secret: Secret de votre application
 *   - code: Le code d'autorisation reçu
 *   - redirect_uri: URL de callback (doit correspondre)
 * 
 * RÉPONSE :
 *   - access_token: Token pour accéder à l'API
 *   - refresh_token: Token pour renouveler l'access token
 *   - expires_in: Durée de validité (en secondes)
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

$clientId = $_ENV['TESLA_CLIENT_ID'] ?? '';
$clientSecret = $_ENV['TESLA_CLIENT_SECRET'] ?? '';
$redirectUri = $_ENV['TESLA_REDIRECT_URI'] ?? '';
$tokenUrl = $_ENV['TESLA_TOKEN_URL'] ?? 'https://auth.tesla.com/oauth2/v3/token';

// ═══════════════════════════════════════════════════════════════════════════
// 2. RÉCUPÉRATION DU CODE ET DU STATE
// ═══════════════════════════════════════════════════════════════════════════

$code = $_GET['code'] ?? null;
$state = $_GET['state'] ?? null;
$error = $_GET['error'] ?? null;

// Mode debug : afficher les paramètres reçus
$showDebug = true; // Mettre à false en production

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Callback OAuth2 - Tesla</title>
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
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 900px;
            margin: 0 auto;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 28px;
        }

        .step {
            background: #f8f9fa;
            border-left: 4px solid #3E6AE1;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .step.error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }

        .step.success {
            background: #d4edda;
            border-left-color: #28a745;
        }

        .step h2 {
            color: #3E6AE1;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .step.error h2 {
            color: #dc3545;
        }

        .step.success h2 {
            color: #28a745;
        }

        pre {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 13px;
            margin: 10px 0;
        }

        .param {
            margin: 8px 0;
            font-size: 14px;
        }

        .param strong {
            color: #3E6AE1;
        }

        .button {
            background: #3E6AE1;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .button:hover {
            background: #2E5AC7;
            transform: translateY(-2px);
        }

        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3E6AE1;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔄 Callback OAuth2 - Authentification Tesla</h1>

        <?php

        // ═══════════════════════════════════════════════════════════════════════════
        // 3. GESTION DES ERREURS
        // ═══════════════════════════════════════════════════════════════════════════

        if ($error) {
            echo '<div class="step error">';
            echo '<h2>❌ Erreur d\'autorisation</h2>';
            echo '<p>Tesla a retourné une erreur : <strong>' . htmlspecialchars($error) . '</strong></p>';
            if (isset($_GET['error_description'])) {
                echo '<p>Description : ' . htmlspecialchars($_GET['error_description']) . '</p>';
            }
            echo '<a href="index.php" class="button">← Retour à l\'accueil</a>';
            echo '</div>';
            echo '</div></body></html>';
            exit;
        }

        if (!$code) {
            echo '<div class="step error">';
            echo '<h2>❌ Code d\'autorisation manquant</h2>';
            echo '<p>Aucun code d\'autorisation n\'a été reçu de Tesla.</p>';
            echo '<a href="index.php" class="button">← Retour à l\'accueil</a>';
            echo '</div>';
            echo '</div></body></html>';
            exit;
        }

        // ═══════════════════════════════════════════════════════════════════════════
        // 4. VÉRIFICATION DU STATE (Protection CSRF)
        // ═══════════════════════════════════════════════════════════════════════════

        echo '<div class="step">';
        echo '<h2>🔍 Étape 1 : Vérification du state (CSRF)</h2>';

        if ($showDebug) {
            echo '<div class="param"><strong>State reçu :</strong> ' . htmlspecialchars($state) . '</div>';
            echo '<div class="param"><strong>State attendu :</strong> ' . htmlspecialchars($_SESSION['oauth_state'] ?? 'N/A') . '</div>';
        }

        if (!isset($_SESSION['oauth_state']) || $state !== $_SESSION['oauth_state']) {
            echo '<p style="color: #dc3545; margin-top: 10px;">❌ État OAuth invalide (possible attaque CSRF)</p>';
            echo '</div>';
            echo '<div class="step error">';
            echo '<h2>❌ Erreur de sécurité</h2>';
            echo '<p>Le state OAuth ne correspond pas. Cela peut indiquer une tentative d\'attaque CSRF.</p>';
            echo '<a href="index.php" class="button">← Retour à l\'accueil</a>';
            echo '</div>';
            echo '</div></body></html>';
            exit;
        }

        echo '<p style="color: #28a745; margin-top: 10px;">✅ State validé avec succès</p>';
        echo '</div>';

        // ═══════════════════════════════════════════════════════════════════════════
        // 5. AFFICHAGE DU CODE D'AUTORISATION REÇU
        // ═══════════════════════════════════════════════════════════════════════════

        echo '<div class="step success">';
        echo '<h2>✅ Étape 2 : Code d\'autorisation reçu</h2>';
        echo '<div class="param"><strong>Code :</strong> ' . htmlspecialchars(substr($code, 0, 50)) . '...</div>';
        echo '<div class="param"><strong>Longueur :</strong> ' . strlen($code) . ' caractères</div>';
        echo '</div>';

        // ═══════════════════════════════════════════════════════════════════════════
        // 6. PRÉPARATION DE LA REQUÊTE D'ÉCHANGE DE TOKEN
        // ═══════════════════════════════════════════════════════════════════════════

        echo '<div class="step">';
        echo '<h2>🔄 Étape 3 : Échange du code contre un access token</h2>';
        echo '<div class="loader"></div>';
        echo '<p style="text-align: center; color: #666;">Requête vers Tesla en cours...</p>';

        $postData = [
            'grant_type' => 'authorization_code',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ];

        if ($showDebug) {
            echo '<h3 style="margin-top: 20px;">Paramètres de la requête :</h3>';
            echo '<div class="param"><strong>URL :</strong> ' . htmlspecialchars($tokenUrl) . '</div>';
            echo '<div class="param"><strong>grant_type :</strong> authorization_code</div>';
            echo '<div class="param"><strong>client_id :</strong> ' . htmlspecialchars(substr($clientId, 0, 30)) . '...</div>';
            echo '<div class="param"><strong>client_secret :</strong> ' . str_repeat('*', 20) . '</div>';
            echo '<div class="param"><strong>code :</strong> ' . htmlspecialchars(substr($code, 0, 30)) . '...</div>';
            echo '<div class="param"><strong>redirect_uri :</strong> ' . htmlspecialchars($redirectUri) . '</div>';
        }

        // ═══════════════════════════════════════════════════════════════════════════
        // 7. ENVOI DE LA REQUÊTE HTTP
        // ═══════════════════════════════════════════════════════════════════════════

        $ch = curl_init($tokenUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        echo '</div>';

        // ═══════════════════════════════════════════════════════════════════════════
        // 8. VÉRIFICATION DE LA RÉPONSE
        // ═══════════════════════════════════════════════════════════════════════════

        if ($curlError) {
            echo '<div class="step error">';
            echo '<h2>❌ Erreur CURL</h2>';
            echo '<p>' . htmlspecialchars($curlError) . '</p>';
            echo '<a href="index.php" class="button">← Retour à l\'accueil</a>';
            echo '</div>';
            echo '</div></body></html>';
            exit;
        }

        if (!$response) {
            echo '<div class="step error">';
            echo '<h2>❌ Aucune réponse reçue</h2>';
            echo '<p>Le serveur Tesla n\'a pas répondu.</p>';
            echo '<a href="index.php" class="button">← Retour à l\'accueil</a>';
            echo '</div>';
            echo '</div></body></html>';
            exit;
        }

        $tokens = json_decode($response, true);

        // ═══════════════════════════════════════════════════════════════════════════
        // 9. AFFICHAGE DE LA RÉPONSE COMPLÈTE DE L'API
        // ═══════════════════════════════════════════════════════════════════════════

        echo '<div class="step">';
        echo '<h2>📋 Réponse de l\'API Tesla (HTTP ' . $httpCode . ')</h2>';
        echo '<pre>' . json_encode($tokens, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</pre>';
        echo '</div>';

        // ═══════════════════════════════════════════════════════════════════════════
        // 10. GESTION DES ERREURS HTTP
        // ═══════════════════════════════════════════════════════════════════════════

        if ($httpCode !== 200 || !isset($tokens['access_token'])) {
            echo '<div class="step error">';
            echo '<h2>❌ Erreur lors de l\'échange du token</h2>';
            echo '<p><strong>Code HTTP :</strong> ' . $httpCode . '</p>';

            if (isset($tokens['error'])) {
                echo '<p><strong>Erreur :</strong> ' . htmlspecialchars($tokens['error']) . '</p>';
            }
            if (isset($tokens['error_description'])) {
                echo '<p><strong>Description :</strong> ' . htmlspecialchars($tokens['error_description']) . '</p>';
            }

            echo '<a href="index.php" class="button">← Retour à l\'accueil</a>';
            echo '</div>';
            echo '</div></body></html>';
            exit;
        }

        // ═══════════════════════════════════════════════════════════════════════════
        // 11. SAUVEGARDE DES TOKENS
        // ═══════════════════════════════════════════════════════════════════════════

        echo '<div class="step success">';
        echo '<h2>✅ Étape 4 : Sauvegarde des tokens</h2>';

        // Sauvegarde en session
        $_SESSION['access_token'] = $tokens['access_token'];
        $_SESSION['refresh_token'] = $tokens['refresh_token'] ?? null;
        $_SESSION['token_expires_at'] = time() + ($tokens['expires_in'] ?? 28800);

        echo '<p>✅ Tokens sauvegardés en session</p>';

        // Sauvegarde dans un fichier JSON
        $varDir = __DIR__ . '/../var/user-tokens';
        if (!is_dir($varDir)) {
            mkdir($varDir, 0755, true);
        }

        $userId = 'user_' . md5($tokens['access_token']); // ID unique basé sur le token
        $tokenFile = $varDir . '/' . $userId . '.json';

        $tokenData = [
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'] ?? null,
            'expires_in' => $tokens['expires_in'] ?? 28800,
            'created_at' => time(),
            'expires_at' => time() + ($tokens['expires_in'] ?? 28800),
        ];

        if (isset($tokens['id_token'])) {
            $tokenData['id_token'] = $tokens['id_token'];
        }

        file_put_contents($tokenFile, json_encode($tokenData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        echo '<p>✅ Tokens sauvegardés dans : <code>' . htmlspecialchars($tokenFile) . '</code></p>';
        echo '</div>';

        // ═══════════════════════════════════════════════════════════════════════════
        // 12. AFFICHAGE DES INFORMATIONS DU TOKEN
        // ═══════════════════════════════════════════════════════════════════════════

        echo '<div class="step success">';
        echo '<h2>🎉 Authentification réussie !</h2>';
        echo '<div class="param"><strong>Access Token :</strong> ' . htmlspecialchars(substr($tokens['access_token'], 0, 50)) . '...</div>';

        if (isset($tokens['refresh_token'])) {
            echo '<div class="param"><strong>Refresh Token :</strong> ' . htmlspecialchars(substr($tokens['refresh_token'], 0, 50)) . '...</div>';
        }

        echo '<div class="param"><strong>Expire dans :</strong> ' . ($tokens['expires_in'] ?? 'N/A') . ' secondes (' . round(($tokens['expires_in'] ?? 28800) / 3600, 2) . ' heures)</div>';

        echo '<a href="index.php" class="button">← Retour à l\'accueil</a>';
        echo '<a href="dashboard.php" class="button" style="background: #28a745; margin-left: 10px;">📊 Tableau de bord →</a>';
        echo '</div>';

        // Nettoyer le state OAuth
        unset($_SESSION['oauth_state']);

        ?>
    </div>
</body>

</html>