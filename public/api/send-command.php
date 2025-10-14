<?php

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * API : Envoyer une commande à un véhicule Tesla
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * Ce fichier permet d'envoyer des commandes aux véhicules Tesla :
 * - wake_up : Réveiller le véhicule
 * - honk : Klaxonner
 * - flash_lights : Flasher les phares
 * - lock : Verrouiller
 * - unlock : Déverrouiller
 * - climate_on : Activer la climatisation
 * - climate_off : Désactiver la climatisation
 * - etc.
 * 
 * REQUÊTE :
 *   POST https://fleet-api.prd.na.vn.cloud.tesla.com/api/1/vehicles/{id}/command/{command}
 * 
 * AUTHENTIFICATION :
 *   - Utilise l'access token de l'utilisateur (depuis la session)
 *   - Header: Authorization: Bearer <user_access_token>
 * 
 * USAGE :
 *   GET /api/send-command.php?vehicle_id=123&command=honk
 *   POST /api/send-command.php (avec vehicle_id et command dans le body)
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
// 2. RÉCUPÉRATION DES PARAMÈTRES
// ═══════════════════════════════════════════════════════════════════════════

$vehicleId = $_REQUEST['vehicle_id'] ?? null;
$command = $_REQUEST['command'] ?? null;

if (!$vehicleId || !$command) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Bad Request',
        'message' => 'Les paramètres "vehicle_id" et "command" sont requis'
    ]);
    exit;
}

// Commandes disponibles
$availableCommands = [
    'wake_up' => '⏰ Réveiller le véhicule',
    'honk' => '📯 Klaxonner',
    'flash_lights' => '💡 Flasher les phares',
    'lock' => '🔒 Verrouiller',
    'unlock' => '🔓 Déverrouiller',
    'climate_on' => '🌡️ Activer la climatisation',
    'climate_off' => '❄️ Désactiver la climatisation',
    'charge_start' => '🔌 Démarrer la charge',
    'charge_stop' => '⏸️ Arrêter la charge',
    'charge_port_door_open' => '🚪 Ouvrir la trappe de charge',
    'charge_port_door_close' => '🚪 Fermer la trappe de charge',
];

$accessToken = $_SESSION['access_token'];
$fleetApiUrl = $_ENV['TESLA_FLEET_API_URL'] ?? 'https://fleet-api.prd.na.vn.cloud.tesla.com';

// ═══════════════════════════════════════════════════════════════════════════
// 3. ENVOI DE LA COMMANDE
// ═══════════════════════════════════════════════════════════════════════════

$client = new TeslaFleetClient($accessToken, $fleetApiUrl);

// Cas spécial pour wake_up qui a sa propre méthode
if ($command === 'wake_up') {
    $result = $client->wakeUp($vehicleId);
} else {
    $result = $client->sendCommand($vehicleId, $command);
}

$httpCode = $client->getLastHttpCode();
$fullResponse = $client->getLastResponse();

// ═══════════════════════════════════════════════════════════════════════════
// 4. FORMAT JSON
// ═══════════════════════════════════════════════════════════════════════════

if ($format === 'json') {
    header('Content-Type: application/json');

    if ($client->isSuccess()) {
        echo json_encode([
            'success' => true,
            'command' => $command,
            'vehicle_id' => $vehicleId,
            'result' => $result,
            'http_code' => $httpCode
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } else {
        http_response_code($httpCode);
        echo json_encode([
            'success' => false,
            'command' => $command,
            'vehicle_id' => $vehicleId,
            'error' => 'Erreur lors de l\'exécution de la commande',
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
    <title>Commande envoyée - Tesla</title>
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

        .command-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .command-info h3 {
            color: #3E6AE1;
            margin-bottom: 15px;
        }

        .info-item {
            margin: 10px 0;
            font-size: 14px;
        }

        .info-item strong {
            color: #333;
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
            margin-top: 10px;
        }

        .button:hover {
            background: #2E5AC7;
            transform: translateY(-2px);
        }

        .commands-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin: 20px 0;
        }

        .command-btn {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #333;
            display: block;
        }

        .command-btn:hover {
            border-color: #3E6AE1;
            background: #e7f1ff;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>⚡ Commande Tesla</h1>
            <div>
                <a href="vehicles.php" class="button">← Liste des véhicules</a>
                <a href="vehicle-data.php?id=<?= htmlspecialchars($vehicleId) ?>" class="button">📊 Détails du véhicule</a>
            </div>
        </div>

        <!-- Informations de la commande -->
        <div class="command-info">
            <h3>📋 Commande envoyée</h3>
            <div class="info-item">
                <strong>Véhicule ID :</strong> <?= htmlspecialchars($vehicleId) ?>
            </div>
            <div class="info-item">
                <strong>Commande :</strong> <?= htmlspecialchars($command) ?>
                <?php if (isset($availableCommands[$command])): ?>
                    (<?= $availableCommands[$command] ?>)
                <?php endif; ?>
            </div>
        </div>

        <!-- Affichage de la réponse complète de l'API -->
        <div class="response-box<?= $client->isSuccess() ? ' success' : ' error' ?>">
            <h3>📋 Réponse complète de l'API Tesla (HTTP <?= $httpCode ?>)</h3>
            <pre><?= json_encode($fullResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></pre>
        </div>

        <?php if ($client->isSuccess()): ?>

            <div class="response-box success">
                <h3>✅ Commande exécutée avec succès</h3>

                <?php if ($result): ?>
                    <div style="margin-top: 15px;">
                        <?php if (isset($result['result']) && $result['result']): ?>
                            <p>✅ La commande a été acceptée par le véhicule</p>
                        <?php elseif (isset($result['state'])): ?>
                            <p>État du véhicule : <strong><?= htmlspecialchars($result['state']) ?></strong></p>
                        <?php endif; ?>

                        <?php if (isset($result['reason']) && $result['reason']): ?>
                            <p>Raison : <?= htmlspecialchars($result['reason']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>

            <div class="response-box error">
                <h3>❌ Erreur lors de l'exécution de la commande</h3>
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
                        <li>Le véhicule doit être en ligne (réveillé)</li>
                        <li>Certaines commandes nécessitent des conditions spécifiques</li>
                        <li>Vérifiez que vous avez les permissions nécessaires</li>
                    </ul>
                </div>
            </div>

        <?php endif; ?>

        <!-- Autres commandes disponibles -->
        <div class="response-box">
            <h3>⚡ Autres commandes disponibles</h3>
            <div class="commands-grid">
                <?php foreach ($availableCommands as $cmd => $label): ?>
                    <?php if ($cmd !== $command): ?>
                        <a href="?vehicle_id=<?= htmlspecialchars($vehicleId) ?>&command=<?= htmlspecialchars($cmd) ?>"
                            class="command-btn">
                            <?= $label ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>

</html>