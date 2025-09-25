<?php
require __DIR__ . '/vendor/autoload.php';

// Chargement des variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$domain = $_ENV['TESLA_DOMAIN'] ?? null;
if (!$domain) {
    die('❌ Domaine non défini dans le .env (TESLA_DOMAIN)');
}

// Chargement du token partenaire
$partnerFile = __DIR__ . '/partner.json';
if (!file_exists($partnerFile)) {
    die('❌ Le fichier partner.json est introuvable. Générez un token via client_credentials.');
}

$partnerData = json_decode(file_get_contents($partnerFile), true);
$partnerToken = $partnerData['access_token'] ?? null;
if (!$partnerToken) {
    die('❌ Token partenaire manquant dans partner.json.');
}

// Appel API pour enregistrer le domaine
$payload = json_encode([
    'domain' => $domain
]);

$ch = curl_init('https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/partner_accounts');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $partnerToken
    ],
    CURLOPT_POSTFIELDS     => $payload
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Affichage du résultat
header('Content-Type: text/html; charset=utf-8');
echo "<h2>🔐 Enregistrement du partenaire</h2>";
echo "<p><strong>Domaine :</strong> {$domain}</p>";
echo "<p><strong>HTTP Status:</strong> {$httpCode}</p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";
?>