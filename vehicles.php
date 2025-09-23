<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 🔄 Charger les tokens
$tokensPath = __DIR__ . '/tokens.json';
if (!file_exists($tokensPath)) {
    exit('❌ Aucun token trouvé. Lance d’abord le processus d’authentification.');
}

$tokens = json_decode(file_get_contents($tokensPath), true);
$accessToken = $tokens['access_token'] ?? null;

if (!$accessToken) {
    exit('❌ Le access_token est introuvable.');
}

// 🔌 Appel API Fleet : GET /vehicles
$apiUrl = 'https://fleet-api.teslamotors.com/api/1/vehicles';
$context = [
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer $accessToken\r\n",
        'ignore_errors' => true,
    ]
];

$response = file_get_contents($apiUrl, false, stream_context_create($context));

if ($response === false) {
    echo "<h3>❌ Erreur lors de l’appel à l’API Tesla</h3><pre>";
    print_r(error_get_last());
    echo "</pre>";
    exit;
}

$data = json_decode($response, true);

if (isset($data['error'])) {
    echo "<h3>❌ Erreur retournée par l’API :</h3><pre>";
    print_r($data);
    echo "</pre>";
    exit;
}

// ✅ Affichage des véhicules
echo "<h2>🚘 Véhicules liés à ton compte Tesla</h2><pre>";
print_r($data);
echo "</pre>";