#!/usr/bin/env php
<?php

/**
 * Exemple d'utilisation de l'API Tesla Fleet avec authentification
 * 
 * Ce script montre comment :
 * 1. Obtenir un access token
 * 2. Faire des appels à l'API Tesla
 * 3. Gérer les erreurs
 */

require __DIR__ . '/vendor/autoload.php';

use TeslaApp\TeslaAuth;
use Dotenv\Dotenv;

// Charger la configuration
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "🚗 Exemple d'utilisation de l'API Tesla Fleet\n";
echo str_repeat("=", 60) . "\n\n";

try {
    // Étape 1: Obtenir un access token
    echo "🔑 Étape 1: Authentification...\n";
    $auth = TeslaAuth::fromEnv();
    $tokenData = $auth->getAccessToken();

    $accessToken = $tokenData['access_token'];
    $expiresIn = $tokenData['expires_in'];

    echo "✅ Token obtenu (expire dans {$expiresIn}s)\n\n";

    // Étape 2: Lister les véhicules
    echo "🚙 Étape 2: Récupération de la liste des véhicules...\n";

    $apiUrl = $_ENV['TESLA_FLEET_API_URL'];
    $ch = curl_init("{$apiUrl}/api/1/vehicles");

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$accessToken}",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);

        if (isset($data['response']) && is_array($data['response'])) {
            $vehicles = $data['response'];
            echo "✅ " . count($vehicles) . " véhicule(s) trouvé(s):\n\n";

            foreach ($vehicles as $index => $vehicle) {
                echo "  Véhicule #" . ($index + 1) . ":\n";
                echo "  ├─ ID: " . ($vehicle['id'] ?? 'N/A') . "\n";
                echo "  ├─ VIN: " . ($vehicle['vin'] ?? 'N/A') . "\n";
                echo "  ├─ Nom: " . ($vehicle['display_name'] ?? 'N/A') . "\n";
                echo "  ├─ État: " . ($vehicle['state'] ?? 'N/A') . "\n";
                echo "  └─ En ligne: " . (($vehicle['state'] ?? '') === 'online' ? '✅ Oui' : '❌ Non') . "\n\n";
            }

            // Étape 3: Exemple d'obtention des données d'un véhicule
            if (!empty($vehicles)) {
                $vehicleId = $vehicles[0]['id'];
                echo "📊 Étape 3: Récupération des données du véhicule {$vehicleId}...\n";

                $ch = curl_init("{$apiUrl}/api/1/vehicles/{$vehicleId}/vehicle_data");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: Bearer {$accessToken}",
                    "Content-Type: application/json"
                ]);

                $vehicleResponse = curl_exec($ch);
                $vehicleHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($vehicleHttpCode === 200) {
                    $vehicleData = json_decode($vehicleResponse, true);

                    if (isset($vehicleData['response'])) {
                        $vd = $vehicleData['response'];
                        echo "✅ Données du véhicule:\n";
                        echo "  ├─ Nom: " . ($vd['display_name'] ?? 'N/A') . "\n";
                        echo "  ├─ Batterie: " . ($vd['charge_state']['battery_level'] ?? 'N/A') . "%\n";
                        echo "  ├─ Portée: " . ($vd['charge_state']['battery_range'] ?? 'N/A') . " miles\n";
                        echo "  ├─ Chargement: " . (($vd['charge_state']['charging_state'] ?? '') === 'Charging' ? '✅ Oui' : '❌ Non') . "\n";
                        echo "  └─ Verrouillé: " . (($vd['vehicle_state']['locked'] ?? false) ? '🔒 Oui' : '🔓 Non') . "\n\n";
                    }
                } else {
                    echo "⚠️  Impossible de récupérer les données (HTTP {$vehicleHttpCode})\n";
                    echo "   Le véhicule est peut-être en veille.\n\n";
                }
            }
        } else {
            echo "⚠️  Aucun véhicule trouvé.\n\n";
        }
    } else {
        echo "❌ Erreur HTTP {$httpCode}:\n";
        echo "   {$response}\n\n";
    }

    // Exemple d'autres endpoints disponibles
    echo "📚 Autres endpoints disponibles:\n";
    echo str_repeat("-", 60) . "\n";
    echo "  GET  /api/1/vehicles                  - Liste des véhicules\n";
    echo "  GET  /api/1/vehicles/{id}             - Info d'un véhicule\n";
    echo "  GET  /api/1/vehicles/{id}/vehicle_data - Toutes les données\n";
    echo "  POST /api/1/vehicles/{id}/wake_up     - Réveiller\n";
    echo "  POST /api/1/vehicles/{id}/command/honk_horn - Klaxonner\n";
    echo "  POST /api/1/vehicles/{id}/command/flash_lights - Flasher\n";
    echo "  POST /api/1/vehicles/{id}/command/door_lock - Verrouiller\n";
    echo "  POST /api/1/vehicles/{id}/command/door_unlock - Déverrouiller\n";
    echo "  POST /api/1/vehicles/{id}/command/climate_on - Clim ON\n";
    echo "  POST /api/1/vehicles/{id}/command/climate_off - Clim OFF\n";
    echo "\n";

    echo "✅ Exemple terminé avec succès!\n";
    exit(0);
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
