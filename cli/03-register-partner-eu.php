#!/usr/bin/env php
<?php

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * ÉTAPE 2bis (EU) : Enregistrer le Partner Account en région EU
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * Ce script tente d'enregistrer (ou récupérer) le Partner Account en région EU.
 * Il utilise le Fleet Auth Token EU (audience EU) et appelle l'endpoint EU.
 * 
 * IMPORTANT : La structure exacte du payload peut évoluer côté Tesla.
 * Ce script affiche toujours la réponse complète de l'API pour faciliter le debug.
 * 
 * REQUÊTES :
 *   POST {TESLA_FLEET_API_URL}/api/1/partner_accounts
 *   (avec Authorization: Bearer <fleet_token>)
 * 
 * INPUT REQUIS (.env) :
 *   - TESLA_FLEET_API_URL=https://fleet-api.prd.eu.vn.cloud.tesla.com
 *   - TESLA_AUDIENCE=https://fleet-api.prd.eu.vn.cloud.tesla.com
 *   - TESLA_REDIRECT_URI (ou TESLA_REDIRECT_URIS, virgule-séparé)
 *   - (optionnel) TESLA_PUBLIC_KEY_URL (sinon déduit de l'hôte du redirect_uri)
 *   - (optionnel) TESLA_APP_NAME
 *   - (optionnel) TESLA_CONTACT_EMAIL
 * 
 * OUTPUT :
 *   - Affiche la réponse complète (succès ou erreur)
 *   - Sauvegarde dans var/partner-account-eu.json (payload + réponse)
 * 
 * USAGE :
 *   php cli/03-register-partner-eu.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// ────────────────────────────────────────────────────────────────────────────
// 1) Chargement config et prérequis
// ────────────────────────────────────────────────────────────────────────────

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║     ÉTAPE 2bis : Enregistrement Partner EU (tesla.com)            ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$fleetApiUrl = rtrim($_ENV['TESLA_FLEET_API_URL'] ?? '', '/');
$audience = $_ENV['TESLA_AUDIENCE'] ?? '';
$redirectUriEnv = $_ENV['TESLA_REDIRECT_URIS'] ?? ($_ENV['TESLA_REDIRECT_URI'] ?? '');
$appName = $_ENV['TESLA_APP_NAME'] ?? '';
$contactEmail = $_ENV['TESLA_CONTACT_EMAIL'] ?? '';

if (!$fleetApiUrl) {
    echo "❌ TESLA_FLEET_API_URL manquant dans .env\n";
    exit(1);
}

if (stripos($fleetApiUrl, 'fleet-api.prd.eu.vn.cloud.tesla.com') === false) {
    echo "⚠️  Attention: TESLA_FLEET_API_URL n'est pas en EU: $fleetApiUrl\n";
    echo "    Pour l'EU : https://fleet-api.prd.eu.vn.cloud.tesla.com\n\n";
}

if (!$redirectUriEnv) {
    echo "❌ TESLA_REDIRECT_URI(S) manquant dans .env\n";
    exit(1);
}

$redirectUris = array_filter(array_map('trim', explode(',', $redirectUriEnv)));

// Déduire l'hôte principal depuis la 1ère redirect_uri
$firstRedirect = $redirectUris[0] ?? '';
$host = '';
if ($firstRedirect) {
    $parts = parse_url($firstRedirect);
    $host = $parts['host'] ?? '';
}

// Déduire une URL publique de clé si non fournie
$publicKeyUrl = $_ENV['TESLA_PUBLIC_KEY_URL'] ?? '';
if (!$publicKeyUrl && $host) {
    $publicKeyUrl = 'https://' . $host . '/.well-known/appspecific/com.tesla.3p.public-key.pem';
}

if (!$appName) {
    $appName = $host ? ('Tesla App - ' . $host) : 'Tesla App - EU';
}

echo "✓ Configuration lue\n";
echo "  Fleet API URL     : $fleetApiUrl\n";
echo "  Audience          : $audience\n";
echo "  App Name          : $appName\n";
echo "  Redirect URIs     : " . implode(', ', $redirectUris) . "\n";
echo "  Public Key URL    : $publicKeyUrl\n";
echo "  Contact Email     : " . ($contactEmail ?: '(non fourni)') . "\n\n";

// ────────────────────────────────────────────────────────────────────────────
// 2) Charger Fleet Token EU
// ────────────────────────────────────────────────────────────────────────────

$tokenPath = __DIR__ . '/../var/fleet-auth-token.json';
if (!file_exists($tokenPath)) {
    echo "❌ Fleet token introuvable : $tokenPath\n";
    echo "   Exécutez d'abord : php cli/01-get-fleet-token.php\n";
    exit(1);
}

$tokenJson = json_decode(file_get_contents($tokenPath), true) ?: [];
$accessToken = $tokenJson['access_token'] ?? '';
$expiresAt = (int)($tokenJson['expires_at'] ?? 0);
$tokenAudience = $tokenJson['audience'] ?? '';

if (!$accessToken) {
    echo "❌ Fleet token invalide (access_token manquant)\n";
    exit(1);
}
if (time() >= $expiresAt) {
    echo "❌ Fleet token expiré, regénérez-le : php cli/01-get-fleet-token.php\n";
    exit(1);
}
if (stripos($tokenAudience, 'fleet-api.prd.eu') === false) {
    echo "⚠️  Le token n'est pas pour l'audience EU : $tokenAudience\n";
    echo "   Regénérez-le avec l'audience EU avant de continuer.\n";
    exit(1);
}

echo "✓ Fleet token EU valide\n\n";

// ────────────────────────────────────────────────────────────────────────────
// 3) Construire le payload d'enregistrement
// ────────────────────────────────────────────────────────────────────────────

$endpoint = $fleetApiUrl . '/api/1/partner_accounts';

$payload = [
    // Champs plausibles selon la doc ; la réponse guidera si autre structure requise
    'name' => $appName,
    'redirect_uris' => $redirectUris,
];

if ($publicKeyUrl) {
    $payload['public_key_url'] = $publicKeyUrl;
}
if ($contactEmail) {
    $payload['contact_email'] = $contactEmail;
}

// Inclure aussi le domaine principal si disponible
if ($host) {
    $payload['domain'] = $host;
    $payload['website'] = 'https://' . $host;
}

echo "📤 Payload d'inscription (JSON) :\n";
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

// ────────────────────────────────────────────────────────────────────────────
// 4) Appeler l'endpoint EU
// ────────────────────────────────────────────────────────────────────────────

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📡 Appel POST EU → $endpoint\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken,
        'Accept: application/json',
        'Content-Type: application/json',
    ],
    CURLOPT_TIMEOUT => 45,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo "❌ ERREUR CURL : $curlError\n";
    exit(1);
}

if (!$response) {
    echo "❌ ERREUR : Réponse vide\n";
    exit(1);
}

$data = json_decode($response, true);

echo "📥 Réponse (HTTP $httpCode)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// ────────────────────────────────────────────────────────────────────────────
// 5) Sauvegarde et guidance
// ────────────────────────────────────────────────────────────────────────────

$varDir = __DIR__ . '/../var';
if (!is_dir($varDir)) {
    mkdir($varDir, 0755, true);
}

$outFile = $varDir . '/partner-account-eu.json';
file_put_contents($outFile, json_encode([
    'requested_at' => time(),
    'requested_date' => date('Y-m-d H:i:s'),
    'endpoint' => $endpoint,
    'http_code' => $httpCode,
    'request_payload' => $payload,
    'response' => $data,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "✓ Réponse sauvegardée : $outFile\n\n";

if ($httpCode >= 200 && $httpCode < 300) {
    echo "╔════════════════════════════════════════════════════════════════════╗\n";
    echo "║         ✅ SUCCÈS - Partner EU enregistré/récupéré                ║\n";
    echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

    // Extraire un client_id si présent
    $clientId = $data['client_id'] ?? ($data['data']['client_id'] ?? null);
    if ($clientId) {
        echo "Client ID EU : $clientId\n";
    }

    echo "Mettez à jour .env si un nouveau client_id vous est retourné.\n\n";
} else {
    echo "╔════════════════════════════════════════════════════════════════════╗\n";
    echo "║            ❌ ECHEC - Vérifiez la réponse ci-dessus              ║\n";
    echo "╚════════════════════════════════════════════════════════════════════╝\n\n";
    echo "💡 Si la structure du payload n'est pas acceptée, ajustez :\n";
    echo "   - name, redirect_uris, domain, website, public_key_url, contact_email\n";
    echo "   - ou enregistrez l'app via le Developer Portal (région EU)\n\n";
}
