<?php

namespace TeslaApp;

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * Tesla Fleet API Client
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * Classe helper pour effectuer des requêtes vers l'API Tesla Fleet
 * 
 * USAGE :
 *   $client = new TeslaFleetClient($accessToken);
 *   $vehicles = $client->getVehicles();
 *   $data = $client->getVehicleData($vehicleId);
 *   $result = $client->sendCommand($vehicleId, 'honk');
 * 
 * ═══════════════════════════════════════════════════════════════════════════
 */
class TeslaFleetClient
{
    private string $accessToken;
    private string $baseUrl;
    private array $lastResponse = [];
    private int $lastHttpCode = 0;

    /**
     * Constructeur
     * 
     * @param string $accessToken Token d'accès utilisateur
     * @param string|null $baseUrl URL de base de l'API (optionnel)
     */
    public function __construct(string $accessToken, ?string $baseUrl = null)
    {
        $this->accessToken = $accessToken;
        $this->baseUrl = $baseUrl ?? 'https://fleet-api.prd.na.vn.cloud.tesla.com';
    }

    /**
     * Récupérer la liste des véhicules de l'utilisateur
     * 
     * @return array|null Liste des véhicules ou null en cas d'erreur
     */
    public function getVehicles(): ?array
    {
        $endpoint = '/api/1/vehicles';
        $response = $this->makeRequest('GET', $endpoint);

        if ($this->lastHttpCode === 200 && isset($response['response'])) {
            return $response['response'];
        }

        return null;
    }

    /**
     * Récupérer les données d'un véhicule spécifique
     * 
     * @param string $vehicleId ID du véhicule
     * @return array|null Données du véhicule ou null en cas d'erreur
     */
    public function getVehicleData(string $vehicleId): ?array
    {
        $endpoint = "/api/1/vehicles/{$vehicleId}/vehicle_data";
        $response = $this->makeRequest('GET', $endpoint);

        if ($this->lastHttpCode === 200 && isset($response['response'])) {
            return $response['response'];
        }

        return null;
    }

    /**
     * Réveiller un véhicule
     * 
     * @param string $vehicleId ID du véhicule
     * @return array|null Résultat ou null en cas d'erreur
     */
    public function wakeUp(string $vehicleId): ?array
    {
        $endpoint = "/api/1/vehicles/{$vehicleId}/wake_up";
        $response = $this->makeRequest('POST', $endpoint);

        if ($this->lastHttpCode === 200 && isset($response['response'])) {
            return $response['response'];
        }

        return null;
    }

    /**
     * Envoyer une commande à un véhicule
     * 
     * @param string $vehicleId ID du véhicule
     * @param string $command Nom de la commande (honk, flash_lights, etc.)
     * @param array $params Paramètres additionnels (optionnel)
     * @return array|null Résultat ou null en cas d'erreur
     */
    public function sendCommand(string $vehicleId, string $command, array $params = []): ?array
    {
        $endpoint = "/api/1/vehicles/{$vehicleId}/command/{$command}";
        $response = $this->makeRequest('POST', $endpoint, $params);

        if ($this->lastHttpCode === 200 && isset($response['response'])) {
            return $response['response'];
        }

        return null;
    }

    /**
     * Effectuer une requête HTTP vers l'API Tesla
     * 
     * @param string $method Méthode HTTP (GET, POST, etc.)
     * @param string $endpoint Endpoint de l'API
     * @param array $data Données à envoyer (pour POST)
     * @return array Réponse décodée
     */
    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init($url);

        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Accept: application/json',
            'Content-Type: application/json'
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
        ]);

        // Configuration spécifique selon la méthode
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $response = curl_exec($ch);
        $this->lastHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->lastResponse = json_decode($response, true) ?? [];

        return $this->lastResponse;
    }

    /**
     * Obtenir la dernière réponse brute
     * 
     * @return array
     */
    public function getLastResponse(): array
    {
        return $this->lastResponse;
    }

    /**
     * Obtenir le dernier code HTTP
     * 
     * @return int
     */
    public function getLastHttpCode(): int
    {
        return $this->lastHttpCode;
    }

    /**
     * Vérifier si la dernière requête a réussi
     * 
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->lastHttpCode >= 200 && $this->lastHttpCode < 300;
    }
}
