<?php

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * API : Liste des véhicules Tesla
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * Ce fichier récupère la liste de tous les véhicules Tesla associés
 * au compte de l'utilisateur authentifié.
 * 
 * REQUÊTE :
 *   GET https://fleet-api.prd.na.vn.cloud.tesla.com/api/1/vehicles
 * 
 * AUTHENTIFICATION :
 *   - Utilise l'access token de l'utilisateur (depuis la session)
 *   - Header: Authorization: Bearer <user_access_token>
 * 
 * RÉPONSE :
 *   - Liste des véhicules avec leurs informations de base
 *   - ID, VIN, nom, état (online/asleep/offline)
 * 
 * USAGE :
 *   GET /api/vehicles.php
 *   ou
 *   GET /api/vehicles.php?format=json (pour JSON pur)
 * 
 * ═══════════════════════════════════════════════════════════════════════════
 */

// Format de sortie (détecté en premier pour le header JSON)
$format = $_GET['format'] ?? 'html';

// Si format JSON demandé, envoyer le header dès le début
if ($format === 'json') {
    header('Content-Type: application/json');
}

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use TeslaApp\TeslaFleetClient;

// Englober dans un try/catch pour capturer toutes les erreurs
try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
    $dotenv->load();

    session_start();
} catch (Exception $e) {
    if ($format === 'json') {
        echo json_encode([
            'success' => false,
            'error' => 'Configuration error',
            'message' => $e->getMessage()
        ]);
        exit;
    }
    die('Erreur de configuration : ' . $e->getMessage());
}

// ═══════════════════════════════════════════════════════════════════════════
// 1. VÉRIFICATION DE L'AUTHENTIFICATION
// ═══════════════════════════════════════════════════════════════════════════

if (!isset($_SESSION['access_token'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Unauthorized',
        'message' => 'Vous devez être authentifié pour accéder à cette ressource'
    ]);
    exit;
}

$accessToken = $_SESSION['access_token'];
$fleetApiUrl = $_ENV['TESLA_FLEET_API_URL'] ?? 'https://fleet-api.prd.na.vn.cloud.tesla.com';

// ═══════════════════════════════════════════════════════════════════════════
// 2. CRÉATION DU CLIENT TESLA FLEET
// ═══════════════════════════════════════════════════════════════════════════

$client = new TeslaFleetClient($accessToken, $fleetApiUrl);

// ═══════════════════════════════════════════════════════════════════════════
// 3. REQUÊTE VERS L'API TESLA
// ═══════════════════════════════════════════════════════════════════════════

$vehicles = $client->getVehicles();
$httpCode = $client->getLastHttpCode();
$fullResponse = $client->getLastResponse();

// ═══════════════════════════════════════════════════════════════════════════
// 4. FORMAT JSON
// ═══════════════════════════════════════════════════════════════════════════

if ($format === 'json') {
    // Header déjà envoyé en début de fichier

    if ($client->isSuccess() && $vehicles !== null) {
        echo json_encode([
            'success' => true,
            'count' => count($vehicles),
            'vehicles' => $vehicles,
            'http_code' => $httpCode
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } else {
        http_response_code($httpCode);
        echo json_encode([
            'success' => false,
            'error' => 'Erreur lors de la récupération des véhicules',
            'http_code' => $httpCode,
            'response' => $fullResponse
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════
// 5. FORMAT HTML (AFFICHAGE DÉTAILLÉ)
// ═══════════════════════════════════════════════════════════════════════════

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes véhicules Tesla</title>
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
            max-width: 1200px;
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

        .response-box {
            background: #f8f9fa;
            border-left: 4px solid #3E6AE1;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .response-box.error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }

        .response-box.success {
            background: #d4edda;
            border-left-color: #28a745;
        }

        .response-box h3 {
            color: #3E6AE1;
            margin-bottom: 15px;
        }

        .response-box.error h3 {
            color: #dc3545;
        }

        .response-box.success h3 {
            color: #28a745;
        }

        pre {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 13px;
            margin: 10px 0;
        }

        .vehicle-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0;
            transition: all 0.3s ease;
        }

        .vehicle-card:hover {
            border-color: #3E6AE1;
            box-shadow: 0 4px 15px rgba(62, 106, 225, 0.2);
        }

        .vehicle-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .vehicle-name {
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }

        .vehicle-state {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .state-online {
            background: #d4edda;
            color: #28a745;
        }

        .state-asleep {
            background: #fff3cd;
            color: #856404;
        }

        .state-offline {
            background: #f8d7da;
            color: #dc3545;
        }

        .vehicle-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }

        .info-item {
            font-size: 14px;
        }

        .info-item strong {
            color: #3E6AE1;
        }

        .button {
            background: #3E6AE1;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin-right: 10px;
        }

        .button:hover {
            background: #2E5AC7;
            transform: translateY(-2px);
        }

        .button.small {
            padding: 8px 16px;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🚗 Mes véhicules Tesla</h1>
            <div>
                <a href="../dashboard.php" class="button">← Tableau de bord</a>
                <a href="?format=json" class="button">📄 Format JSON</a>
            </div>
        </div>

        <!-- Affichage de la réponse complète de l'API -->
        <div class="response-box<?= $client->isSuccess() ? ' success' : ' error' ?>">
            <h3>📋 Réponse complète de l'API Tesla (HTTP <?= $httpCode ?>)</h3>
            <pre><?= json_encode($fullResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></pre>
        </div>

        <?php if ($client->isSuccess() && $vehicles !== null): ?>

            <div class="response-box success">
                <h3>✅ Véhicules récupérés avec succès</h3>
                <p><strong>Nombre de véhicules :</strong> <?= count($vehicles) ?></p>
            </div>

            <?php if (count($vehicles) === 0): ?>
                <div class="response-box">
                    <p>Aucun véhicule trouvé sur votre compte Tesla.</p>
                </div>
            <?php else: ?>
                <?php foreach ($vehicles as $vehicle): ?>
                    <div class="vehicle-card">
                        <div class="vehicle-header">
                            <div class="vehicle-name">
                                🚗 <?= htmlspecialchars($vehicle['display_name'] ?? $vehicle['vin'] ?? 'Véhicule Tesla') ?>
                            </div>
                            <div class="vehicle-state state-<?= htmlspecialchars($vehicle['state'] ?? 'offline') ?>">
                                <?= htmlspecialchars($vehicle['state'] ?? 'unknown') ?>
                            </div>
                        </div>

                        <div class="vehicle-info">
                            <div class="info-item">
                                <strong>ID :</strong> <?= htmlspecialchars($vehicle['id'] ?? 'N/A') ?>
                            </div>
                            <div class="info-item">
                                <strong>VIN :</strong> <?= htmlspecialchars($vehicle['vin'] ?? 'N/A') ?>
                            </div>
                            <div class="info-item">
                                <strong>Modèle :</strong> <?= htmlspecialchars($vehicle['vehicle_name'] ?? 'N/A') ?>
                            </div>
                            <?php if (isset($vehicle['in_service'])): ?>
                                <div class="info-item">
                                    <strong>En service :</strong> <?= $vehicle['in_service'] ? 'Oui' : 'Non' ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="margin-top: 15px;">
                            <a href="vehicle-data.php?id=<?= htmlspecialchars($vehicle['id']) ?>" class="button small">
                                📊 Voir les détails
                            </a>
                            <a href="send-command.php?vehicle_id=<?= htmlspecialchars($vehicle['id']) ?>&command=wake_up" class="button small">
                                ⏰ Réveiller
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php else: ?>

            <div class="response-box error">
                <h3>❌ Erreur lors de la récupération des véhicules</h3>
                <p><strong>Code HTTP :</strong> <?= $httpCode ?></p>

                <?php if (isset($fullResponse['error'])): ?>
                    <p><strong>Erreur :</strong> <?= htmlspecialchars($fullResponse['error']) ?></p>
                <?php endif; ?>

                <?php if (isset($fullResponse['error_description'])): ?>
                    <p><strong>Description :</strong> <?= htmlspecialchars($fullResponse['error_description']) ?></p>
                <?php endif; ?>

                <div style="margin-top: 15px;">
                    <p>💡 <strong>Suggestions :</strong></p>
                    <ul style="margin-left: 20px; margin-top: 10px;">
                        <li>Vérifiez que votre token est toujours valide</li>
                        <li>Assurez-vous d'avoir au moins un véhicule sur votre compte Tesla</li>
                        <li>Vérifiez les scopes OAuth2 configurés</li>
                    </ul>
                </div>
            </div>

        <?php endif; ?>
    </div>
</body>

</html>