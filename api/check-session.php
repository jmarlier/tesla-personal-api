<?php

/**
 * Vérifie si une session active existe
 */

session_start();
header('Content-Type: application/json');

if (isset($_SESSION['access_token']) && isset($_SESSION['authenticated'])) {
    echo json_encode([
        'authenticated' => true,
        'token' => $_SESSION['access_token']
    ]);
} else {
    echo json_encode([
        'authenticated' => false
    ]);
}
