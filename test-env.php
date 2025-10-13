<?php
// Test .env
header('Content-Type: application/json');

try {
    require __DIR__ . '/vendor/autoload.php';
    
    if (!file_exists(__DIR__ . '/.env')) {
        echo json_encode(['success' => false, 'error' => '.env manquant']);
        exit;
    }
    
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    $clientId = $_ENV['TESLA_CLIENT_ID'] ?? 'NON DÉFINI';
    
    echo json_encode([
        'success' => true, 
        'message' => '.env chargé',
        'client_id' => substr($clientId, 0, 20) . '...'
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

