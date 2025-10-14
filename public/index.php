<?php

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * PAGE D'ACCUEIL - Interface d'authentification Tesla
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * Cette page affiche l'interface principale permettant aux utilisateurs
 * de se connecter avec leur compte Tesla.
 * 
 * FLOW :
 *   1. L'utilisateur arrive sur cette page
 *   2. Il clique sur "Se connecter avec Tesla"
 *   3. Il est redirigé vers login.php
 *   4. login.php le redirige vers auth.tesla.com
 * 
 * ═══════════════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

session_start();

// Vérifier si l'utilisateur est déjà connecté
$isAuthenticated = isset($_SESSION['access_token']);

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tesla Fleet API - Connexion</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 60px 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .logo {
            font-size: 72px;
            margin-bottom: 20px;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 32px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 40px;
            font-size: 16px;
        }

        .tesla-button {
            background: #3E6AE1;
            color: white;
            border: none;
            padding: 18px 40px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(62, 106, 225, 0.4);
        }

        .tesla-button:hover {
            background: #2E5AC7;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(62, 106, 225, 0.5);
        }

        .tesla-button:active {
            transform: translateY(0);
        }

        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #3E6AE1;
            padding: 20px;
            margin-top: 40px;
            border-radius: 5px;
            text-align: left;
        }

        .info-box h3 {
            color: #3E6AE1;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .info-box ul {
            list-style: none;
            padding: 0;
        }

        .info-box li {
            color: #666;
            padding: 8px 0;
            font-size: 14px;
        }

        .info-box li:before {
            content: "✓ ";
            color: #3E6AE1;
            font-weight: bold;
            margin-right: 8px;
        }

        .authenticated {
            background: #d4edda;
            border-left-color: #28a745;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .authenticated h3 {
            color: #28a745;
            margin-bottom: 10px;
        }

        .dashboard-button {
            background: #28a745;
            margin-top: 20px;
        }

        .dashboard-button:hover {
            background: #218838;
        }

        .logout-button {
            background: #dc3545;
            padding: 12px 30px;
            font-size: 14px;
            margin-top: 10px;
        }

        .logout-button:hover {
            background: #c82333;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">🚗</div>
        <h1>Tesla Fleet API</h1>
        <p class="subtitle">Connectez-vous avec votre compte Tesla</p>

        <?php if ($isAuthenticated): ?>
            <div class="authenticated">
                <h3>✅ Vous êtes connecté !</h3>
                <p style="color: #155724; margin-top: 10px;">
                    Votre compte Tesla est lié avec succès.
                </p>
            </div>

            <a href="dashboard.php" class="tesla-button dashboard-button">
                📊 Accéder au tableau de bord
            </a>

            <br>

            <a href="logout.php" class="tesla-button logout-button">
                🚪 Se déconnecter
            </a>
        <?php else: ?>
            <a href="login.php" class="tesla-button">
                🔐 Se connecter avec Tesla
            </a>

            <div class="info-box">
                <h3>Cette application vous permettra de :</h3>
                <ul>
                    <li>Visualiser vos véhicules Tesla</li>
                    <li>Consulter l'état de charge</li>
                    <li>Voir la localisation de vos véhicules</li>
                    <li>Envoyer des commandes (klaxon, flash, etc.)</li>
                </ul>
            </div>
        <?php endif; ?>

        <div class="info-box" style="margin-top: 20px; border-left-color: #ffc107;">
            <h3 style="color: #856404;">🔒 Sécurité</h3>
            <p style="color: #856404; font-size: 14px;">
                Cette application utilise l'authentification officielle Tesla OAuth2.
                Vos identifiants ne sont jamais stockés sur nos serveurs.
            </p>
        </div>
    </div>
</body>

</html>