#!/usr/bin/env php
<?php

/**
 * Script d'installation et de configuration du projet Tesla Fleet API
 * 
 * Usage: php setup.php
 */

echo "\n";
echo "🚗 " . str_repeat("=", 60) . "\n";
echo "   Tesla Fleet API - Installation et Configuration\n";
echo str_repeat("=", 64) . "\n\n";

// Vérifier que nous sommes à la racine du projet
if (!file_exists('composer.json')) {
    echo "❌ Erreur: Ce script doit être exécuté depuis la racine du projet.\n";
    exit(1);
}

// Étape 1: Vérifier PHP
echo "📋 Étape 1/6 : Vérification de l'environnement PHP\n";
echo str_repeat("-", 64) . "\n";

$phpVersion = PHP_VERSION;
echo "✓ Version PHP: $phpVersion\n";

if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    echo "❌ PHP 8.0 ou supérieur est requis.\n";
    exit(1);
}

$requiredExtensions = ['curl', 'openssl', 'json', 'mbstring'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ Extension $ext: installée\n";
    } else {
        echo "❌ Extension $ext: manquante\n";
        $missingExtensions[] = $ext;
    }
}

if (!empty($missingExtensions)) {
    echo "\n❌ Extensions manquantes: " . implode(', ', $missingExtensions) . "\n";
    echo "   Installez-les et relancez ce script.\n";
    exit(1);
}

echo "\n";

// Étape 2: Vérifier Composer
echo "📦 Étape 2/6 : Vérification des dépendances Composer\n";
echo str_repeat("-", 64) . "\n";

if (!file_exists('vendor/autoload.php')) {
    echo "⚠️  Les dépendances ne sont pas installées.\n";
    echo "   Exécution de 'composer install'...\n";
    system('composer install', $exitCode);

    if ($exitCode !== 0) {
        echo "❌ Erreur lors de l'installation des dépendances.\n";
        exit(1);
    }
    echo "✓ Dépendances installées avec succès.\n";
} else {
    echo "✓ Dépendances déjà installées.\n";
}

echo "\n";

// Étape 3: Créer la structure de dossiers
echo "📁 Étape 3/6 : Création de la structure de dossiers\n";
echo str_repeat("-", 64) . "\n";

$directories = ['config', 'public', 'src'];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✓ Dossier créé: $dir/\n";
    } else {
        echo "✓ Dossier existant: $dir/\n";
    }
}

echo "\n";

// Étape 4: Configuration .env
echo "⚙️  Étape 4/6 : Configuration du fichier .env\n";
echo str_repeat("-", 64) . "\n";

if (!file_exists('.env')) {
    if (file_exists('.env.example')) {
        copy('.env.example', '.env');
        echo "✓ Fichier .env créé depuis .env.example\n";
        echo "\n⚠️  IMPORTANT: Éditez le fichier .env avec vos informations:\n";
        echo "   - TESLA_CLIENT_ID\n";
        echo "   - TESLA_PRIVATE_KEY_PATH\n";
        echo "   - TESLA_SCOPES\n\n";
    } else {
        echo "❌ Le fichier .env.example est introuvable.\n";
        exit(1);
    }
} else {
    echo "✓ Fichier .env existe déjà.\n";
}

echo "\n";

// Étape 5: Vérifier la clé privée
echo "🔐 Étape 5/6 : Vérification de la clé privée\n";
echo str_repeat("-", 64) . "\n";

// Charger le .env pour obtenir le chemin de la clé
require 'vendor/autoload.php';
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $privateKeyPath = $_ENV['TESLA_PRIVATE_KEY_PATH'] ?? 'config/private-key.pem';

    if (file_exists($privateKeyPath)) {
        echo "✓ Clé privée trouvée: $privateKeyPath\n";

        // Vérifier les permissions
        $perms = substr(sprintf('%o', fileperms($privateKeyPath)), -3);
        echo "  Permissions actuelles: $perms\n";

        if ($perms !== '600') {
            echo "  ⚠️  Permissions recommandées: 600\n";
            echo "     Exécutez: chmod 600 $privateKeyPath\n";
        } else {
            echo "  ✓ Permissions correctes (600)\n";
        }
    } else {
        echo "❌ Clé privée introuvable: $privateKeyPath\n\n";
        echo "   Pour générer une nouvelle clé EC (secp256r1):\n";
        echo "   $ openssl ecparam -name prime256v1 -genkey -noout -out $privateKeyPath\n";
        echo "   $ openssl ec -in $privateKeyPath -pubout -out config/public-key.pem\n\n";
        echo "   ⚠️  Uploadez uniquement la clé PUBLIQUE sur developer.tesla.com\n\n";
    }
} catch (Exception $e) {
    echo "⚠️  Impossible de charger .env: " . $e->getMessage() . "\n";
}

echo "\n";

// Étape 6: Résumé
echo "✅ Étape 6/6 : Résumé de l'installation\n";
echo str_repeat("-", 64) . "\n";

echo "
Configuration terminée! 

📝 Prochaines étapes:

1. Éditez le fichier .env avec vos informations:
   $ nano .env

2. Si vous n'avez pas de clé privée, générez-la:
   $ openssl ecparam -name prime256v1 -genkey -noout -out config/private-key.pem
   $ openssl ec -in config/private-key.pem -pubout -out config/public-key.pem
   $ chmod 600 config/private-key.pem

3. Uploadez la clé PUBLIQUE (config/public-key.pem) sur:
   https://developer.tesla.com

4. Testez l'authentification:
   $ php cli-get-token.php

5. Pour utiliser l'interface web, configurez votre serveur web avec 
   'public/' comme document root.

📚 Documentation complète: README.md

";

echo str_repeat("=", 64) . "\n";
echo "🚀 Installation terminée!\n\n";

exit(0);
