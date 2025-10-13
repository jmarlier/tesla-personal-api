<?php

namespace TeslaApp;

use Firebase\JWT\JWT;
use Exception;

/**
 * Classe pour gérer l'authentification avec l'API Tesla Fleet via OAuth 2.0 et JWT
 */
class TeslaAuth
{
    private string $clientId;
    private string $privateKeyPath;
    private string $fleetApiUrl;
    private string $scopes;

    /**
     * @param string $clientId Client ID de l'application Tesla
     * @param string $privateKeyPath Chemin vers la clé privée EC (secp256r1) PEM
     * @param string $fleetApiUrl URL de l'API Tesla Fleet
     * @param string $scopes Scopes OAuth séparés par des espaces
     */
    public function __construct(
        string $clientId,
        string $privateKeyPath,
        string $fleetApiUrl,
        string $scopes = 'fleet_api:vehicles:read'
    ) {
        $this->clientId = $clientId;
        $this->privateKeyPath = $privateKeyPath;
        $this->fleetApiUrl = rtrim($fleetApiUrl, '/');
        $this->scopes = $scopes;
    }

    /**
     * Charge la clé privée depuis le fichier
     * 
     * @throws Exception Si la clé ne peut pas être lue
     * @return string Contenu de la clé privée
     */
    private function loadPrivateKey(): string
    {
        $basePath = dirname(__DIR__);
        $fullPath = $basePath . '/' . $this->privateKeyPath;

        if (!file_exists($fullPath)) {
            throw new Exception("Clé privée introuvable : {$fullPath}");
        }

        $privateKey = file_get_contents($fullPath);

        if ($privateKey === false) {
            throw new Exception("Erreur de lecture de la clé privée : {$fullPath}");
        }

        return $privateKey;
    }

    /**
     * Génère un JWT signé avec ES256 (ECDSA avec SHA-256)
     * 
     * @param int $expirationSeconds Durée de validité du JWT en secondes (défaut: 1 heure)
     * @return string JWT signé
     * @throws Exception Si la clé ne peut pas être chargée
     */
    public function generateJWT(int $expirationSeconds = 3600): string
    {
        $privateKey = $this->loadPrivateKey();

        $now = time();
        $payload = [
            'iss' => $this->clientId,      // Issuer
            'sub' => $this->clientId,      // Subject
            'aud' => $this->fleetApiUrl,   // Audience
            'iat' => $now,                 // Issued At
            'exp' => $now + $expirationSeconds, // Expiration
        ];

        // ES256 = ECDSA avec la courbe P-256 (secp256r1)
        return JWT::encode($payload, $privateKey, 'ES256');
    }

    /**
     * Obtient un access token depuis l'API Tesla Fleet
     * 
     * @return array{access_token: string, token_type: string, expires_in: int} Réponse de l'API
     * @throws Exception Si la requête échoue
     */
    public function getAccessToken(): array
    {
        $jwt = $this->generateJWT();

        $body = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
            'client_assertion' => $jwt,
            'scope' => $this->scopes,
        ];

        $ch = curl_init("{$this->fleetApiUrl}/oauth/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new Exception("Erreur cURL : {$curlError}");
        }

        if ($httpCode !== 200) {
            throw new Exception("Erreur HTTP {$httpCode} : {$response}");
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Erreur de décodage JSON : " . json_last_error_msg());
        }

        if (!isset($data['access_token'])) {
            throw new Exception("access_token absent de la réponse : {$response}");
        }

        return $data;
    }

    /**
     * Charge la configuration depuis les variables d'environnement
     * 
     * @return self Instance configurée
     * @throws Exception Si les variables d'environnement requises sont manquantes
     */
    public static function fromEnv(): self
    {
        $clientId = $_ENV['TESLA_CLIENT_ID'] ?? null;
        $privateKeyPath = $_ENV['TESLA_PRIVATE_KEY_PATH'] ?? null;
        $fleetApiUrl = $_ENV['TESLA_FLEET_API_URL'] ?? null;
        $scopes = $_ENV['TESLA_SCOPES'] ?? 'fleet_api:vehicles:read';

        if (!$clientId || !$privateKeyPath || !$fleetApiUrl) {
            throw new Exception(
                'Variables d\'environnement manquantes. Vérifiez que TESLA_CLIENT_ID, ' .
                    'TESLA_PRIVATE_KEY_PATH et TESLA_FLEET_API_URL sont définis dans .env'
            );
        }

        return new self($clientId, $privateKeyPath, $fleetApiUrl, $scopes);
    }
}
