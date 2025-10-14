<?php

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * API : Données détaillées d'un véhicule Tesla
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * Ce fichier récupère toutes les données d'un véhicule spécifique :
 * - État de charge
 * - Autonomie
 * - Localisation GPS
 * - État des portes, fenêtres, coffre
 * - Température
 * - etc.
 * 
 * REQUÊTE :
 *   GET https://fleet-api.prd.na.vn.cloud.tesla.com/api/1/vehicles/{id}/vehicle_data
 * 
 * AUTHENTIFICATION :
 *   - Utilise l'access token de l'utilisateur (depuis la session)
 *   - Header: Authorization: Bearer <user_access_token>
 * 
 * USAGE :
 *   GET /api/vehicle-data.php?id=123456789
 *   ou
 *   GET /api/vehicle-data.php?id=123456789&format=json
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

// ═══════════════════════════════════════════════════════════════════════════
// 2. VÉRIFICATION DES PARAMÈTRES
// ═══════════════════════════════════════════════════════════════════════════

$vehicleId = $_GET['id'] ?? null;

if (!$vehicleId) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Bad Request',
        'message' => 'Le paramètre "id" est requis'
    ]);
    exit;
}

$accessToken = $_SESSION['access_token'];
$fleetApiUrl = $_ENV['TESLA_FLEET_API_URL'] ?? 'https://fleet-api.prd.na.vn.cloud.tesla.com';

// ═══════════════════════════════════════════════════════════════════════════
// 3. REQUÊTE VERS L'API TESLA
// ═══════════════════════════════════════════════════════════════════════════

$client = new TeslaFleetClient($accessToken, $fleetApiUrl);
$vehicleData = $client->getVehicleData($vehicleId);
$httpCode = $client->getLastHttpCode();
$fullResponse = $client->getLastResponse();

// ═══════════════════════════════════════════════════════════════════════════
// 4. FORMAT JSON
// ═══════════════════════════════════════════════════════════════════════════

if ($format === 'json') {
    header('Content-Type: application/json');

    if ($client->isSuccess() && $vehicleData !== null) {
        echo json_encode([
            'success' => true,
            'vehicle_data' => $vehicleData,
            'http_code' => $httpCode
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } else {
        http_response_code($httpCode);
        echo json_encode([
            'success' => false,
            'error' => 'Erreur lors de la récupération des données',
            'http_code' => $httpCode,
            'response' => $fullResponse
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════
// 5. FORMAT HTML
// ═══════════════════════════════════════════════════════════════════════════

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du véhicule - Tesla</title>
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
            max-width: 1400px;
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

        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .data-card {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
        }

        .data-card h3 {
            color: #3E6AE1;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .data-item {
            margin: 10px 0;
            font-size: 14px;
        }

        .data-item strong {
            color: #333;
            display: block;
            margin-bottom: 5px;
        }

        .data-value {
            color: #666;
            font-size: 16px;
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
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📊 Détails du véhicule</h1>
            <div>
                <a href="vehicles.php" class="button">← Liste des véhicules</a>
                <a href="?id=<?= htmlspecialchars($vehicleId) ?>&format=json" class="button">📄 Format JSON</a>
            </div>
        </div>

        <!-- Affichage de la réponse complète de l'API -->
        <div class="response-box<?= $client->isSuccess() ? ' success' : ' error' ?>">
            <h3>📋 Réponse complète de l'API Tesla (HTTP <?= $httpCode ?>)</h3>
            <pre><?= json_encode($fullResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></pre>
        </div>

        <?php if ($client->isSuccess() && $vehicleData !== null): ?>

            <div class="response-box success">
                <h3>✅ Données du véhicule récupérées</h3>
                <p><strong>Véhicule :</strong> <?= htmlspecialchars($vehicleData['display_name'] ?? $vehicleData['vin'] ?? 'N/A') ?></p>
                <p><strong>État :</strong> <?= htmlspecialchars($vehicleData['state'] ?? 'N/A') ?></p>
            </div>

            <!-- Affichage des données principales -->
            <div class="data-grid">

                <!-- Informations générales -->
                <div class="data-card">
                    <h3>🚗 Informations générales</h3>
                    <div class="data-item">
                        <strong>Nom</strong>
                        <div class="data-value"><?= htmlspecialchars($vehicleData['display_name'] ?? 'N/A') ?></div>
                    </div>
                    <div class="data-item">
                        <strong>VIN</strong>
                        <div class="data-value"><?= htmlspecialchars($vehicleData['vin'] ?? 'N/A') ?></div>
                    </div>
                    <div class="data-item">
                        <strong>État</strong>
                        <div class="data-value"><?= htmlspecialchars($vehicleData['state'] ?? 'N/A') ?></div>
                    </div>
                </div>

                <!-- Charge -->
                <?php if (isset($vehicleData['charge_state'])): $charge = $vehicleData['charge_state']; ?>
                    <div class="data-card">
                        <h3>🔋 État de charge</h3>
                        <div class="data-item">
                            <strong>Niveau de batterie</strong>
                            <div class="data-value"><?= htmlspecialchars($charge['battery_level'] ?? 'N/A') ?>%</div>
                        </div>
                        <div class="data-item">
                            <strong>Autonomie</strong>
                            <div class="data-value"><?= htmlspecialchars($charge['battery_range'] ?? 'N/A') ?> miles</div>
                        </div>
                        <div class="data-item">
                            <strong>Chargement en cours</strong>
                            <div class="data-value"><?= isset($charge['charging_state']) && $charge['charging_state'] === 'Charging' ? 'Oui' : 'Non' ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Climat -->
                <?php if (isset($vehicleData['climate_state'])): $climate = $vehicleData['climate_state']; ?>
                    <div class="data-card">
                        <h3>🌡️ Climat</h3>
                        <div class="data-item">
                            <strong>Température intérieure</strong>
                            <div class="data-value"><?= htmlspecialchars($climate['inside_temp'] ?? 'N/A') ?>°C</div>
                        </div>
                        <div class="data-item">
                            <strong>Température extérieure</strong>
                            <div class="data-value"><?= htmlspecialchars($climate['outside_temp'] ?? 'N/A') ?>°C</div>
                        </div>
                        <div class="data-item">
                            <strong>Climatisation</strong>
                            <div class="data-value"><?= isset($climate['is_climate_on']) && $climate['is_climate_on'] ? 'Activée' : 'Désactivée' ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Localisation -->
                <?php if (isset($vehicleData['drive_state'])): $drive = $vehicleData['drive_state']; ?>
                    <div class="data-card">
                        <h3>📍 Localisation</h3>
                        <div class="data-item">
                            <strong>Latitude</strong>
                            <div class="data-value"><?= htmlspecialchars($drive['latitude'] ?? 'N/A') ?></div>
                        </div>
                        <div class="data-item">
                            <strong>Longitude</strong>
                            <div class="data-value"><?= htmlspecialchars($drive['longitude'] ?? 'N/A') ?></div>
                        </div>
                        <?php if (isset($drive['latitude']) && isset($drive['longitude'])): ?>
                            <div class="data-item">
                                <a href="https://www.google.com/maps?q=<?= $drive['latitude'] ?>,<?= $drive['longitude'] ?>"
                                    target="_blank" class="button" style="margin-top: 10px;">
                                    🗺️ Voir sur Google Maps
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- État du véhicule -->
                <?php if (isset($vehicleData['vehicle_state'])): $state = $vehicleData['vehicle_state']; ?>
                    <div class="data-card">
                        <h3>🔒 État du véhicule</h3>
                        <div class="data-item">
                            <strong>Verrouillé</strong>
                            <div class="data-value"><?= isset($state['locked']) && $state['locked'] ? 'Oui' : 'Non' ?></div>
                        </div>
                        <div class="data-item">
                            <strong>Kilométrage</strong>
                            <div class="data-value"><?= htmlspecialchars($state['odometer'] ?? 'N/A') ?> miles</div>
                        </div>
                        <div class="data-item">
                            <strong>Version firmware</strong>
                            <div class="data-value"><?= htmlspecialchars($state['car_version'] ?? 'N/A') ?></div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Actions disponibles -->
            <div class="response-box">
                <h3>⚡ Actions disponibles</h3>
                <a href="send-command.php?vehicle_id=<?= htmlspecialchars($vehicleId) ?>&command=honk" class="button">
                    📯 Klaxonner
                </a>
                <a href="send-command.php?vehicle_id=<?= htmlspecialchars($vehicleId) ?>&command=flash_lights" class="button">
                    💡 Flasher les phares
                </a>
                <a href="send-command.php?vehicle_id=<?= htmlspecialchars($vehicleId) ?>&command=wake_up" class="button">
                    ⏰ Réveiller
                </a>
            </div>

        <?php else: ?>

            <div class="response-box error">
                <h3>❌ Erreur lors de la récupération des données</h3>
                <p><strong>Code HTTP :</strong> <?= $httpCode ?></p>

                <?php if (isset($fullResponse['error'])): ?>
                    <p><strong>Erreur :</strong> <?= htmlspecialchars($fullResponse['error']) ?></p>
                <?php endif; ?>

                <div style="margin-top: 15px;">
                    <p>💡 <strong>Suggestions :</strong></p>
                    <ul style="margin-left: 20px; margin-top: 10px;">
                        <li>Le véhicule doit être en ligne (état "online")</li>
                        <li>Essayez de réveiller le véhicule d'abord</li>
                        <li>Vérifiez que l'ID du véhicule est correct</li>
                    </ul>
                </div>
            </div>

        <?php endif; ?>
    </div>
</body>

</html>