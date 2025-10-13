<?php

/**
 * API de réinitialisation de la session
 */

session_start();
header('Content-Type: application/json');

// Détruire la session
session_unset();
session_destroy();

echo json_encode([
    'success' => true,
    'message' => 'Session réinitialisée'
]);
