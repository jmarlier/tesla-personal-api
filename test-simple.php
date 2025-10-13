<?php
// Test ultra-simple - retourne toujours du JSON
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'PHP fonctionne', 'php_version' => PHP_VERSION]);

