<?php

/**
 * Script pour obtenir un access token de l'API Tesla Fleet
 * 
 * Ce fichier peut être appelé via le web ou en ligne de commande
 */

// Capturer TOUTES les erreurs et les retourner en JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Buffer de sortie pour capturer les erreurs
ob_start();

// Fonction pour nettoyer et retourner du JSON en cas d'erreur
function sendJsonError($message, $code = 500) {
    // Nettoyer le buffer
    if (ob_get_level()) ob_clean();
    
    // Envoyer les headers
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    
    echo json_encode([
        'success' => false,
        'error' => $message,
    ], JSON_PRETTY_PRINT);
    
    exit;
}

// Gestionnaire d'erreurs fatales
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        sendJsonError('Erreur PHP: ' . $error['message'] . ' dans ' . $error['file'] . ':' . $error['line']);
    }
});

try {
    // En-têtes pour JSON
    header('Content-Type: application/json; charset=utf-8');
    
    // Vérifier vendor/autoload.php
    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        throw new Exception('Composer autoloader manquant. Exécutez: composer install');
    }
    
    require __DIR__ . '/vendor/autoload.php';
    
    use TeslaApp\TeslaAuth;
    use Dotenv\Dotenv;
    
    // Vérifier .env
    if (!file_exists(__DIR__ . '/.env')) {
        throw new Exception('.env manquant. Copiez .env.example vers .env et configurez-le.');
    }
    
    // Charger les variables d'environnement
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    // Créer une instance de TeslaAuth
    $auth = TeslaAuth::fromEnv();
    
    // Obtenir l'access token
    $tokenData = $auth->getAccessToken();
    
    // Nettoyer le buffer et envoyer la réponse
    ob_end_clean();
    
    // Réponse en JSON
    echo json_encode([
        'success' => true,
        'data' => $tokenData,
        'message' => 'Access token obtenu avec succès',
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    sendJsonError($e->getMessage());
} catch (Throwable $e) {
    sendJsonError('Erreur inattendue: ' . $e->getMessage());
}
