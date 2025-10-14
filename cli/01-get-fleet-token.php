#!/usr/bin/env php
<?php

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * ÉTAPE 1 : Obtenir le Fleet Auth Token
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * Ce script obtient un Fleet Auth Token (access token partner) en utilisant
 * le client_id et client_secret de votre Partner Account.
 * 
 * REQUÊTE :
 *   POST https://fleet-auth.prd.vn.cloud.tesla.com/oauth2/v3/token
 * 
 * INPUT :
 *   - TESLA_CLIENT_ID (depuis .env)
 *   - TESLA_CLIENT_SECRET (depuis .env)
 *   - Grant type: client_credentials
 *   - Audience: https://fleet-api.prd.na.vn.cloud.tesla.com
 * 
 * OUTPUT :
 *   - Stocke le token dans /var/fleet-auth-token.json
 *   - Affiche le token et sa date d'expiration
 * 
 * USAGE :
 *   php cli/01-get-fleet-token.php
 * 
 * ═══════════════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// ═══════════════════════════════════════════════════════════════════════════
// 1. CHARGEMENT DE LA CONFIGURATION
// ═══════════════════════════════════════════════════════════════════════════

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║         ÉTAPE 1 : Obtention du Fleet Auth Token                   ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Vérification des variables requises
$requiredVars = ['TESLA_CLIENT_ID', 'TESLA_CLIENT_SECRET', 'TESLA_TOKEN_URL', 'TESLA_AUDIENCE'];
$missingVars = [];

foreach ($requiredVars as $var) {
    if (empty($_ENV[$var])) {
        $missingVars[] = $var;
    }
}

if (!empty($missingVars)) {
    echo "❌ ERREUR : Variables d'environnement manquantes dans .env :\n";
    foreach ($missingVars as $var) {
        echo "   - $var\n";
    }
    exit(1);
}

$clientId = $_ENV['TESLA_CLIENT_ID'];
$clientSecret = $_ENV['TESLA_CLIENT_SECRET'];
$tokenUrl = $_ENV['TESLA_TOKEN_URL'];
$audience = $_ENV['TESLA_AUDIENCE'];

echo "✓ Configuration chargée\n";
echo "  Client ID: " . substr($clientId, 0, 20) . "...\n";
echo "  Token URL: $tokenUrl\n";
echo "  Audience: $audience\n";
echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// 2. PRÉPARATION DE LA REQUÊTE
// ═══════════════════════════════════════════════════════════════════════════

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📡 Requête vers Tesla Fleet Auth API...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$postData = [
    'grant_type' => 'client_credentials',
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'audience' => $audience,
];

echo "Paramètres de la requête :\n";
echo "  grant_type: client_credentials\n";
echo "  client_id: " . substr($clientId, 0, 30) . "...\n";
echo "  client_secret: " . str_repeat('*', 15) . "\n";
echo "  audience: $audience\n";
echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// 3. ENVOI DE LA REQUÊTE HTTP
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

echo "🔄 Envoi de la requête POST...\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// ═══════════════════════════════════════════════════════════════════════════
// 4. VÉRIFICATION DE LA RÉPONSE
// ═══════════════════════════════════════════════════════════════════════════

if ($curlError) {
    echo "❌ ERREUR CURL : $curlError\n";
    exit(1);
}

if (!$response) {
    echo "❌ ERREUR : Aucune réponse reçue du serveur\n";
    exit(1);
}

echo "📥 Réponse reçue (HTTP $httpCode)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$data = json_decode($response, true);

// ═══════════════════════════════════════════════════════════════════════════
// AFFICHAGE DE LA RÉPONSE BRUTE COMPLÈTE
// ═══════════════════════════════════════════════════════════════════════════
echo "📋 RÉPONSE COMPLÈTE DE L'API TESLA :\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if ($httpCode !== 200) {
    echo "❌ ERREUR HTTP $httpCode\n\n";
    echo "Réponse complète :\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo "\n\n";

    // Affichage des erreurs communes
    if (isset($data['error'])) {
        echo "Erreur : " . $data['error'] . "\n";
        if (isset($data['error_description'])) {
            echo "Description : " . $data['error_description'] . "\n";
        }
    }

    exit(1);
}

if (!isset($data['access_token'])) {
    echo "❌ ERREUR : Token non présent dans la réponse\n";
    echo "Réponse : " . json_encode($data, JSON_PRETTY_PRINT);
    echo "\n";
    exit(1);
}

// ═══════════════════════════════════════════════════════════════════════════
// 5. SAUVEGARDE DU TOKEN
// ═══════════════════════════════════════════════════════════════════════════

$accessToken = $data['access_token'];
$expiresIn = $data['expires_in'] ?? 3600;
$tokenType = $data['token_type'] ?? 'Bearer';

$tokenData = [
    'access_token' => $accessToken,
    'token_type' => $tokenType,
    'expires_in' => $expiresIn,
    'created_at' => time(),
    'expires_at' => time() + $expiresIn,
    'audience' => $audience,
];

// Créer le dossier /var s'il n'existe pas
$varDir = __DIR__ . '/../var';
if (!is_dir($varDir)) {
    mkdir($varDir, 0755, true);
    echo "✓ Dossier /var créé\n";
}

$tokenFile = $varDir . '/fleet-auth-token.json';
file_put_contents($tokenFile, json_encode($tokenData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "✓ Token sauvegardé dans : $tokenFile\n";
echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// 6. AFFICHAGE DU RÉSULTAT
// ═══════════════════════════════════════════════════════════════════════════

echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║                    ✅ SUCCÈS - TOKEN OBTENU                        ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "📊 INFORMATIONS DU TOKEN :\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  Type         : $tokenType\n";
echo "  Expire dans  : $expiresIn secondes (" . round($expiresIn / 3600, 2) . " heures)\n";
echo "  Créé le      : " . date('Y-m-d H:i:s', $tokenData['created_at']) . "\n";
echo "  Expire le    : " . date('Y-m-d H:i:s', $tokenData['expires_at']) . "\n";
echo "  Audience     : $audience\n";
echo "\n";

echo "  Access Token (extrait) :\n";
echo "  " . substr($accessToken, 0, 80) . "...\n";
echo "\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📝 PROCHAINE ÉTAPE :\n";
echo "   Exécutez : php cli/02-register-partner.php\n";
echo "   (pour créer/mettre à jour votre Partner Account)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\n";
