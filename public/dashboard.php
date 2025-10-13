<?php

/**
 * Tableau de bord après authentification
 * Affiche les véhicules et permet d'envoyer des commandes
 */

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

session_start();

// Vérifier l'authentification
if (!isset($_SESSION['access_token'])) {
    header('Location: /login.php');
    exit;
}

$accessToken = $_SESSION['access_token'];
$apiUrl = $_ENV['TESLA_FLEET_API_URL'];

// Récupérer la liste des véhicules
function getVehicles($apiUrl, $accessToken)
{
    $ch = curl_init("{$apiUrl}/api/1/vehicles");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$accessToken}",
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return $data['response'] ?? [];
    }

    return [];
}

$vehicles = getVehicles($apiUrl, $accessToken);

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tesla Fleet API</title>
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
            padding: 40px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        h1 {
            color: #333;
            font-size: 28px;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }

        .vehicle-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .vehicle-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .vehicle-name {
            font-size: 22px;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
        }

        .vehicle-info {
            margin: 10px 0;
            color: #666;
        }

        .vehicle-info strong {
            color: #333;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }

        .status-online {
            background: #d4edda;
            color: #155724;
        }

        .status-offline {
            background: #f8d7da;
            color: #721c24;
        }

        .status-asleep {
            background: #fff3cd;
            color: #856404;
        }

        .empty-state {
            background: white;
            border-radius: 20px;
            padding: 60px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .empty-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>🚗 Mes Véhicules Tesla</h1>
                <p style="color: #666; margin-top: 5px;">Tesla Fleet API Dashboard</p>
            </div>
            <a href="/logout.php" class="logout-btn">Déconnexion</a>
        </div>

        <?php if (empty($vehicles)): ?>
            <div class="empty-state">
                <div class="empty-icon">🚙</div>
                <h2 style="color: #333; margin-bottom: 10px;">Aucun véhicule trouvé</h2>
                <p style="color: #666;">Assurez-vous que votre compte Tesla possède des véhicules associés.</p>
            </div>
        <?php else: ?>
            <div class="vehicle-grid">
                <?php foreach ($vehicles as $vehicle): ?>
                    <div class="vehicle-card">
                        <div class="vehicle-name">
                            <?= htmlspecialchars($vehicle['display_name'] ?? 'Tesla') ?>
                            <?php
                            $state = $vehicle['state'] ?? 'unknown';
                            $badgeClass = $state === 'online' ? 'status-online' : ($state === 'asleep' ? 'status-asleep' : 'status-offline');
                            ?>
                            <span class="status-badge <?= $badgeClass ?>">
                                <?= htmlspecialchars($state) ?>
                            </span>
                        </div>

                        <div class="vehicle-info">
                            <strong>VIN:</strong> <?= htmlspecialchars($vehicle['vin'] ?? 'N/A') ?>
                        </div>

                        <div class="vehicle-info">
                            <strong>ID:</strong> <?= htmlspecialchars($vehicle['id'] ?? 'N/A') ?>
                        </div>

                        <?php if (isset($vehicle['vehicle_id'])): ?>
                            <div class="vehicle-info">
                                <strong>Vehicle ID:</strong> <?= htmlspecialchars($vehicle['vehicle_id']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($vehicle['option_codes'])): ?>
                            <div class="vehicle-info" style="margin-top: 15px; font-size: 12px; color: #999;">
                                Options: <?= htmlspecialchars(substr($vehicle['option_codes'], 0, 50)) ?>...
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>