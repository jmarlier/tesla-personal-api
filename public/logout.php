<?php

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * DÉCONNEXION - Suppression de la session utilisateur
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * Ce fichier déconnecte l'utilisateur en détruisant sa session.
 * 
 * ═══════════════════════════════════════════════════════════════════════════
 */

session_start();

// Détruire toutes les variables de session
$_SESSION = [];

// Détruire le cookie de session
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Détruire la session
session_destroy();

// Rediriger vers la page d'accueil
header('Location: index.php');
exit;
