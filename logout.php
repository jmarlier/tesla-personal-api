<?php

/**
 * Déconnexion - supprime la session et les tokens
 */

session_start();

// Supprimer toutes les variables de session
$_SESSION = [];

// Détruire le cookie de session
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Détruire la session
session_destroy();

// Optionnel: Supprimer le fichier de tokens
$tokenFile = __DIR__ . '/var/tokens.json';
if (file_exists($tokenFile)) {
    unlink($tokenFile);
}

// Rediriger vers l'accueil
header('Location: index.php');
exit;
