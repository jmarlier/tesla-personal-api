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

        <!-- Chargement des véhicules via AJAX -->
        <div class="info-box">
            <h3>🚗 Mes véhicules Tesla</h3>
            <div id="vehicles-container">
                <p style="text-align: center; color: #666;">
                    Chargement des véhicules...
                </p>
            </div>
        </div>

        <div class="info-box">
            <h3>🔧 Actions disponibles</h3>
            <a href="../api/vehicles.php" class="button">📋 Liste complète des véhicules</a>
            <p style="color: #999; font-size: 12px; margin-top: 10px;">
                Affichage avec toutes les informations détaillées et réponses API
            </p>
        </div>

        <script>
        // Chargement des véhicules via l'API
        fetch('../api/vehicles.php?format=json')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('vehicles-container');
                
                if (data.success && data.vehicles && data.vehicles.length > 0) {
                    let html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">';
                    
                    data.vehicles.forEach(vehicle => {
                        const stateClass = vehicle.state === 'online' ? 'valid' : 'expired';
                        const stateEmoji = vehicle.state === 'online' ? '✅' : '😴';
                        
                        html += `
                            <div style="background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 10px; padding: 20px;">
                                <h4 style="color: #333; margin-bottom: 10px;">
                                    🚗 ${vehicle.display_name || vehicle.vin}
                                </h4>
                                <p style="margin: 8px 0; font-size: 14px;">
                                    <strong>État :</strong> 
                                    <span class="status ${stateClass}">${stateEmoji} ${vehicle.state}</span>
                                </p>
                                <p style="margin: 8px 0; font-size: 14px; color: #666;">
                                    <strong>VIN :</strong> ${vehicle.vin}
                                </p>
                                <div style="margin-top: 15px;">
                                    <a href="../api/vehicle-data.php?id=${vehicle.id}" class="button" style="font-size: 12px; padding: 8px 16px;">
                                        📊 Voir détails
                                    </a>
                                </div>
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    html += `<p style="margin-top: 15px; color: #666; font-size: 14px;">Total : ${data.count} véhicule(s)</p>`;
                    container.innerHTML = html;
                } else if (data.success && data.vehicles && data.vehicles.length === 0) {
                    container.innerHTML = '<p style="color: #666;">Aucun véhicule trouvé sur votre compte Tesla.</p>';
                } else {
                    container.innerHTML = `
                        <div style="color: #dc3545;">
                            <p><strong>❌ Erreur lors du chargement des véhicules</strong></p>
                            <p style="margin-top: 10px; font-size: 14px;">
                                ${data.error || 'Erreur inconnue'}
                            </p>
                            <a href="../api/vehicles.php" class="button" style="margin-top: 15px; background: #dc3545;">
                                Voir les détails de l'erreur
                            </a>
                        </div>
                    `;
                }
            })
            .catch(error => {
                document.getElementById('vehicles-container').innerHTML = `
                    <div style="color: #dc3545;">
                        <p><strong>❌ Erreur de connexion</strong></p>
                        <p style="margin-top: 10px; font-size: 14px;">
                            Impossible de charger les véhicules : ${error.message}
                        </p>
                    </div>
                `;
            });
        </script>
    </div>
</body>

</html>