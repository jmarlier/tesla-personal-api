<?php

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * TABLEAU DE BORD - Interface utilisateur
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * Cette page affiche les informations de l'utilisateur connecté
 * et permettra d'interagir avec l'API Tesla Fleet (à venir : étape 4)
 * 
 * ═══════════════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

session_start();

// Vérifier si l'utilisateur est authentifié
if (!isset($_SESSION['access_token'])) {
    header('Location: index.php');
    exit;
}

$accessToken = $_SESSION['access_token'];
$expiresAt = $_SESSION['token_expires_at'] ?? 0;
$hasRefreshToken = isset($_SESSION['refresh_token']);

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Tesla Fleet API</title>
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
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 1000px;
            margin: 0 auto;
        }

        h1 {
            color: #333;
            margin-bottom: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #3E6AE1;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .info-box h3 {
            color: #3E6AE1;
            margin-bottom: 15px;
        }

        .param {
            margin: 10px 0;
            font-size: 14px;
        }

        .param strong {
            color: #3E6AE1;
            min-width: 150px;
            display: inline-block;
        }

        .token-value {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 10px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-wrap: break-word;
            margin-top: 10px;
        }

        .button {
            background: #3E6AE1;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .button:hover {
            background: #2E5AC7;
            transform: translateY(-2px);
        }

        .button.logout {
            background: #dc3545;
        }

        .button.logout:hover {
            background: #c82333;
        }

        .status {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .status.valid {
            background: #d4edda;
            color: #28a745;
        }

        .status.expired {
            background: #f8d7da;
            color: #dc3545;
        }

        .coming-soon {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
            text-align: center;
            padding: 40px;
        }

        .coming-soon h2 {
            color: #856404;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>🚗 Tableau de bord Tesla</h1>
                <?php
                $isExpired = time() >= $expiresAt;
                if ($isExpired) {
                    echo '<span class="status expired">❌ Token expiré</span>';
                } else {
                    $remainingHours = round(($expiresAt - time()) / 3600, 1);
                    echo '<span class="status valid">✅ Connecté (expire dans ' . $remainingHours . 'h)</span>';
                }
                ?>
            </div>
            <div>
                <a href="index.php" class="button">← Accueil</a>
                <a href="logout.php" class="button logout">🚪 Déconnexion</a>
            </div>
        </div>

        <div class="info-box">
            <h3>🔑 Informations de connexion</h3>
            <div class="param"><strong>Access Token :</strong></div>
            <div class="token-value"><?= htmlspecialchars(substr($accessToken, 0, 100)) ?>...</div>

            <div class="param" style="margin-top: 15px;">
                <strong>Expire le :</strong>
                <?= date('Y-m-d H:i:s', $expiresAt) ?>
            </div>

            <div class="param">
                <strong>Refresh Token :</strong>
                <?= $hasRefreshToken ? '✅ Disponible' : '❌ Non disponible' ?>
            </div>
        </div>

        <div class="info-box coming-soon">
            <h2>🚧 Fonctionnalités à venir</h2>
            <p style="margin-top: 10px;">
                <strong>Étape 4 : Intégration de l'API Fleet Tesla</strong>
            </p>
            <p style="margin-top: 20px; color: #666;">
                Cette section affichera bientôt :<br>
                • Liste de vos véhicules Tesla<br>
                • État de charge et autonomie<br>
                • Localisation GPS<br>
                • Envoi de commandes (honk, flash, etc.)
            </p>
        </div>

        <div class="info-box">
            <h3>🔧 Actions disponibles</h3>
            <p style="color: #666; margin-bottom: 15px;">
                Pour le moment, vous pouvez tester votre authentification :
            </p>
            <a href="test-api.php" class="button">🧪 Tester l'API Tesla</a>
            <p style="color: #999; font-size: 12px; margin-top: 10px;">
                (À créer à l'étape 4)
            </p>
        </div>
    </div>
</body>

</html>