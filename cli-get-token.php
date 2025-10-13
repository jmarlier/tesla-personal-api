#!/usr/bin/env php
<?php

/**
 * Script CLI pour obtenir un access token de l'API Tesla Fleet
 * 
 * Usage: php cli-get-token.php
 */

require __DIR__ . '/vendor/autoload.php';

use TeslaApp\TeslaAuth;
use Dotenv\Dotenv;

// Charger les variables d'environnement depuis .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "🚗 Tesla Fleet API - Authentification OAuth 2.0\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // Créer une instance de TeslaAuth depuis les variables d'environnement
    $auth = TeslaAuth::fromEnv();

    echo "📋 Client ID: " . $_ENV['TESLA_CLIENT_ID'] . "\n";
    echo "🔐 Clé privée: " . $_ENV['TESLA_PRIVATE_KEY_PATH'] . "\n\n";

    echo "⏳ Génération du JWT et requête à l'API...\n\n";

    // Obtenir l'access token
    $tokenData = $auth->getAccessToken();

    // Afficher le résultat
    echo "✅ Access token obtenu avec succès!\n\n";
    echo "🔑 Access Token: " . substr($tokenData['access_token'], 0, 50) . "...\n";
    echo "📝 Type: " . $tokenData['token_type'] . "\n";
    echo "⏱️  Expire dans: " . $tokenData['expires_in'] . " secondes\n\n";

    echo "📄 Réponse complète (JSON):\n";
    echo json_encode($tokenData, JSON_PRETTY_PRINT) . "\n";

    exit(0);
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
