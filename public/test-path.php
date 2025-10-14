<?php

/**
 * Fichier de test pour vérifier la structure des chemins
 */

echo "<h1>Test de structure du serveur</h1>";
echo "<pre>";

echo "Document Root : " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script Filename : " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "Script Name : " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "PHP Self : " . $_SERVER['PHP_SELF'] . "\n";
echo "Request URI : " . $_SERVER['REQUEST_URI'] . "\n";

echo "\n--- Structure attendue ---\n";
echo "Ce fichier devrait être : /public/test-path.php\n";
echo "L'API devrait être : /api/vehicles.php\n";

echo "\n--- Vérification des dossiers ---\n";
$publicDir = __DIR__;
$rootDir = dirname($publicDir);
$apiDir = $rootDir . '/api';

echo "Dossier public : $publicDir\n";
echo "Dossier racine : $rootDir\n";
echo "Dossier API : $apiDir\n";

if (is_dir($apiDir)) {
    echo "✅ Le dossier /api existe\n";
    if (file_exists($apiDir . '/vehicles.php')) {
        echo "✅ Le fichier /api/vehicles.php existe\n";
    } else {
        echo "❌ Le fichier /api/vehicles.php n'existe PAS\n";
    }
} else {
    echo "❌ Le dossier /api n'existe PAS\n";
}

echo "</pre>";
