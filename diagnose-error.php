<?php

/**
 * Script de diagnostic pour identifier l'erreur 500
 * À placer à la racine et accéder via navigateur
 */

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Diagnostic Tesla App</h1>";
echo "<hr>";

// Test 1: Version PHP
echo "<h2>1. Version PHP</h2>";
echo "<p>Version: " . PHP_VERSION . "</p>";
echo "<p>✅ OK</p>";

// Test 2: Extensions requises
echo "<h2>2. Extensions PHP</h2>";
$extensions = ['curl', 'openssl', 'json', 'mbstring'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<p>" . ($loaded ? "✅" : "❌") . " $ext: " . ($loaded ? "Installée" : "Manquante") . "</p>";
}

// Test 3: Fichiers
echo "<h2>3. Fichiers</h2>";
$files = [
    'vendor/autoload.php',
    '.env',
    '.env.example',
    'config/private-key.pem',
    'src/TeslaAuth.php',
];

foreach ($files as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    echo "<p>" . ($exists ? "✅" : "❌") . " $file: " . ($exists ? "Présent" : "Manquant") . "</p>";
}

// Test 4: Autoloader
echo "<h2>4. Composer Autoloader</h2>";
try {
    require __DIR__ . '/vendor/autoload.php';
    echo "<p>✅ Autoloader chargé</p>";
} catch (Exception $e) {
    echo "<p>❌ Erreur: " . $e->getMessage() . "</p>";
    exit;
}

// Test 5: Charger .env
echo "<h2>5. Chargement .env</h2>";
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    echo "<p>✅ .env chargé</p>";

    // Vérifier les variables
    $vars = ['TESLA_CLIENT_ID', 'TESLA_PRIVATE_KEY_PATH', 'TESLA_FLEET_API_URL'];
    foreach ($vars as $var) {
        $value = $_ENV[$var] ?? 'NON DÉFINI';
        if ($value === 'NON DÉFINI') {
            echo "<p>❌ $var: NON DÉFINI</p>";
        } else {
            // Masquer les valeurs sensibles
            $display = strlen($value) > 20 ? substr($value, 0, 20) . '...' : $value;
            echo "<p>✅ $var: $display</p>";
        }
    }
} catch (Exception $e) {
    echo "<p>❌ Erreur: " . $e->getMessage() . "</p>";
}

// Test 6: Classe TeslaAuth
echo "<h2>6. Classe TeslaAuth</h2>";
try {
    if (class_exists('TeslaApp\TeslaAuth')) {
        echo "<p>✅ Classe TeslaAuth trouvée</p>";

        // Essayer de créer une instance
        $auth = TeslaApp\TeslaAuth::fromEnv();
        echo "<p>✅ Instance créée depuis .env</p>";
    } else {
        echo "<p>❌ Classe TeslaAuth non trouvée</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erreur: " . $e->getMessage() . "</p>";
}

// Test 7: Permissions
echo "<h2>7. Permissions</h2>";
$checkPerms = [
    '.env' => '600',
    'config/private-key.pem' => '600',
];

foreach ($checkPerms as $file => $expected) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $perms = substr(sprintf('%o', fileperms(__DIR__ . '/' . $file)), -3);
        $ok = $perms === $expected;
        echo "<p>" . ($ok ? "✅" : "⚠️") . " $file: $perms " . ($ok ? "" : "(devrait être $expected)") . "</p>";
    } else {
        echo "<p>❌ $file: Non trouvé</p>";
    }
}

echo "<hr>";
echo "<h2>✅ Diagnostic terminé</h2>";
echo "<p>Si tous les tests sont verts, index.php devrait fonctionner.</p>";
echo "<p><a href='index.php'>Tester index.php</a></p>";
