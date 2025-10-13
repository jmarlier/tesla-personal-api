<?php
// Test autoloader
header('Content-Type: application/json');

try {
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require __DIR__ . '/vendor/autoload.php';
        echo json_encode(['success' => true, 'message' => 'Autoloader chargé']);
    } else {
        echo json_encode(['success' => false, 'error' => 'vendor/autoload.php manquant']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

