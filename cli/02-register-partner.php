#!/usr/bin/env php
<?php

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * ÉTAPE 2 : Récupération et validation des informations Partner Account
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * Ce script récupère les informations de votre Partner Account depuis .env
 * (puisque vous avez déjà créé votre application via le Tesla Developer Portal)
 * 
 * INPUT :
 *   - TESLA_CLIENT_ID (depuis .env)
 *   - TESLA_CLIENT_SECRET (depuis .env)
 *   - TESLA_REDIRECT_URI (depuis .env)
 *   - TESLA_PRIVATE_KEY_PATH (depuis .env)
 * 
 * OUTPUT :
 *   - Affiche toutes les informations du Partner Account
 *   - Stocke les infos dans /var/partner-account.json
 *   - Valide la présence de la clé privée
 * 
 * USAGE :
 *   php cli/02-register-partner.php
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
echo "║      ÉTAPE 2 : Validation des informations Partner Account        ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Vérification des variables requises
$requiredVars = [
    'TESLA_CLIENT_ID',
    'TESLA_CLIENT_SECRET',
    'TESLA_REDIRECT_URI',
    'TESLA_FLEET_API_URL',
    'TESLA_AUTH_URL',
    'TESLA_TOKEN_URL'
];

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

echo "✓ Configuration .env chargée\n";
echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// 2. RÉCUPÉRATION DES INFORMATIONS
// ═══════════════════════════════════════════════════════════════════════════

$partnerInfo = [
    'client_id' => $_ENV['TESLA_CLIENT_ID'],
    'client_secret' => $_ENV['TESLA_CLIENT_SECRET'],
    'redirect_uri' => $_ENV['TESLA_REDIRECT_URI'],
    'private_key_path' => $_ENV['TESLA_PRIVATE_KEY_PATH'] ?? null,
    'fleet_api_url' => $_ENV['TESLA_FLEET_API_URL'],
    'auth_url' => $_ENV['TESLA_AUTH_URL'],
    'token_url' => $_ENV['TESLA_TOKEN_URL'],
    'audience' => $_ENV['TESLA_AUDIENCE'] ?? null,
    'scopes' => $_ENV['TESLA_SCOPES'] ?? null,
    'user_scopes' => $_ENV['TESLA_USER_SCOPES'] ?? null,
];

// ═══════════════════════════════════════════════════════════════════════════
// 3. AFFICHAGE DES INFORMATIONS
// ═══════════════════════════════════════════════════════════════════════════

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📋 INFORMATIONS DU PARTNER ACCOUNT\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🔑 IDENTIFIANTS DE L'APPLICATION :\n";
echo "  Client ID       : " . $partnerInfo['client_id'] . "\n";
echo "  Client Secret   : " . substr($partnerInfo['client_secret'], 0, 15) . "..." . substr($partnerInfo['client_secret'], -5) . "\n";
echo "  Redirect URI    : " . $partnerInfo['redirect_uri'] . "\n";
echo "\n";

echo "🌐 ENDPOINTS TESLA :\n";
echo "  Fleet API URL   : " . $partnerInfo['fleet_api_url'] . "\n";
echo "  Auth URL        : " . $partnerInfo['auth_url'] . "\n";
echo "  Token URL       : " . $partnerInfo['token_url'] . "\n";
echo "\n";

if ($partnerInfo['audience']) {
    echo "🎯 AUDIENCE :\n";
    echo "  " . $partnerInfo['audience'] . "\n";
    echo "\n";
}

if ($partnerInfo['scopes']) {
    echo "🔐 SCOPES (Partner) :\n";
    echo "  " . $partnerInfo['scopes'] . "\n";
    echo "\n";
}

if ($partnerInfo['user_scopes']) {
    echo "👤 SCOPES (User OAuth) :\n";
    echo "  " . $partnerInfo['user_scopes'] . "\n";
    echo "\n";
}

// ═══════════════════════════════════════════════════════════════════════════
// 4. VÉRIFICATION DE LA CLÉ PRIVÉE
// ═══════════════════════════════════════════════════════════════════════════

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🔐 VÉRIFICATION DE LA CLÉ PRIVÉE\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if ($partnerInfo['private_key_path']) {
    $privateKeyPath = __DIR__ . '/../' . $partnerInfo['private_key_path'];

    echo "  Chemin configuré : " . $partnerInfo['private_key_path'] . "\n";
    echo "  Chemin absolu    : " . $privateKeyPath . "\n";

    if (file_exists($privateKeyPath)) {
        $keyContent = file_get_contents($privateKeyPath);
        $keySize = filesize($privateKeyPath);

        echo "  ✅ Fichier trouvé (" . $keySize . " octets)\n";

        // Vérifier que c'est bien une clé PEM
        if (strpos($keyContent, '-----BEGIN') !== false) {
            echo "  ✅ Format PEM valide\n";

            // Détecter le type de clé
            if (strpos($keyContent, 'EC PRIVATE KEY') !== false) {
                echo "  ✅ Type : EC Private Key (recommandé pour Tesla)\n";
            } elseif (strpos($keyContent, 'PRIVATE KEY') !== false) {
                echo "  ✅ Type : Private Key\n";
            } elseif (strpos($keyContent, 'RSA PRIVATE KEY') !== false) {
                echo "  ⚠️  Type : RSA Private Key (Tesla recommande EC)\n";
            }
        } else {
            echo "  ⚠️  ATTENTION : Le format ne semble pas être PEM\n";
        }
    } else {
        echo "  ❌ ERREUR : Fichier introuvable\n";
        echo "\n";
        echo "  💡 Vous devez créer la clé privée EC (secp256r1) :\n";
        echo "     openssl ecparam -name prime256v1 -genkey -noout -out " . $partnerInfo['private_key_path'] . "\n";
        echo "\n";
    }
} else {
    echo "  ⚠️  ATTENTION : TESLA_PRIVATE_KEY_PATH non configuré dans .env\n";
    echo "\n";
    echo "  💡 Si vous utilisez le client_secret, la clé privée n'est pas nécessaire\n";
    echo "     Mais Tesla recommande l'utilisation de clés EC pour plus de sécurité\n";
}

echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// 5. AFFICHAGE DU JSON COMPLET
// ═══════════════════════════════════════════════════════════════════════════

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📋 CONFIGURATION COMPLÈTE (format JSON) :\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo json_encode($partnerInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// ═══════════════════════════════════════════════════════════════════════════
// 6. SAUVEGARDE DES INFORMATIONS
// ═══════════════════════════════════════════════════════════════════════════

$varDir = __DIR__ . '/../var';
if (!is_dir($varDir)) {
    mkdir($varDir, 0755, true);
}

$partnerFile = $varDir . '/partner-account.json';

$saveData = [
    'validated_at' => time(),
    'validated_date' => date('Y-m-d H:i:s'),
    'partner_info' => $partnerInfo,
];

file_put_contents($partnerFile, json_encode($saveData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "✓ Informations sauvegardées dans : $partnerFile\n";
echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// 7. RÉSUMÉ ET PROCHAINES ÉTAPES
// ═══════════════════════════════════════════════════════════════════════════

echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║           ✅ VALIDATION TERMINÉE AVEC SUCCÈS                       ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "📊 RÉSUMÉ :\n";
echo "  ✅ Client ID configuré\n";
echo "  ✅ Client Secret configuré\n";
echo "  ✅ Redirect URI configuré\n";
echo "  ✅ Endpoints Tesla configurés\n";

if ($partnerInfo['private_key_path'] && file_exists(__DIR__ . '/../' . $partnerInfo['private_key_path'])) {
    echo "  ✅ Clé privée présente\n";
} else {
    echo "  ⚠️  Clé privée non configurée (utilisation du client_secret)\n";
}

echo "\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📝 PROCHAINE ÉTAPE :\n";
echo "   Créer l'interface OAuth2 pour authentifier les utilisateurs\n";
echo "   (Étape 3 : OAuth2 Authorization Code Flow)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\n";
