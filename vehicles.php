<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Charger les tokens Fleet depuis le fichier
$tokensFile = __DIR__ . '/tokens.json';
if (!file_exists($tokensFile)) {
    exit('<h2 style="color: red;">❌ Aucun token trouvé. Connecte-toi d’abord via login.php</h2>');
}

$fleetData = json_decode(file_get_contents($tokensFile), true);
$accessToken = $fleetData['access_token'] ?? null;
$fleetBaseUrl = $fleetData['fleet_api_base_url'] ?? '';

// Vérifications
if (!$accessToken || !$fleetBaseUrl) {
    exit('<h2 style="color: red;">❌ Token ou URL manquant dans tokens.json</h2><pre>' . htmlspecialchars(json_encode($fleetData, JSON_PRETTY_PRINT)) . '</pre>');
}

// URL par défaut
$defaultUrl = $fleetBaseUrl . '/api/1/vehicles';
$apiUrl = $_POST['api_url'] ?? $defaultUrl;

// Initialiser la réponse
$response = '';
$jsonDecoded = null;
$httpHeaders = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $context = [
        'http' => [
            'method' => 'GET',
            'header' => "Authorization: Bearer $accessToken\r\n",
            'ignore_errors' => true,
        ]
    ];

    // Capture les en-têtes HTTP
    stream_context_set_default($context);
    $response = @file_get_contents($apiUrl, false, stream_context_create($context));
    $jsonDecoded = json_decode($response, true);
    $httpHeaders = $http_response_header ?? [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🔧 Test API Tesla Fleet</title>
    <style>
        body {
            font-family: sans-serif;
            background: #f8f9fa;
            padding: 2rem;
            color: #333;
        }
        h1 {
            color: #007bff;
        }
        textarea {
            width: 100%;
            height: 150px;
            font-family: monospace;
            font-size: 0.9em;
            background: #f1f1f1;
            padding: 10px;
        }
        .section {
            margin-bottom: 2rem;
            border: 1px solid #ddd;
            padding: 1rem;
            background: white;
        }
        .section h2 {
            margin-top: 0;
            font-size: 1.2em;
            color: #555;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            font-family: monospace;
            font-size: 0.95em;
        }
        input[type="submit"] {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        pre {
            background: #f3f3f3;
            padding: 10px;
            overflow-x: auto;
        }
    </style>
</head>
<body>

<h1>🔧 Debug API Tesla Fleet</h1>

<div class="section">
    <h2>🔐 Token actuel (tronqué)</h2>
    <code><?= htmlspecialchars(substr($accessToken, 0, 80)) ?>... (<?= strlen($accessToken) ?> caractères)</code>
</div>

<div class="section">
    <h2>🔗 Tester une URL Tesla Fleet API</h2>
    <form method="post">
        <label for="api_url">URL API à tester :</label>
        <input type="text" id="api_url" name="api_url" value="<?= htmlspecialchars($apiUrl) ?>">
        <input type="submit" value="📡 Lancer la requête GET">
    </form>
</div>

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="section">
        <h2>🌐 URL appelée</h2>
        <pre><?= htmlspecialchars($apiUrl) ?></pre>
    </div>

    <div class="section">
        <h2>📨 Réponse brute</h2>
        <textarea readonly><?= htmlspecialchars($response ?: '❌ Aucune réponse reçue') ?></textarea>
    </div>

    <div class="section">
        <h2>📦 JSON décodé</h2>
        <pre><?= $jsonDecoded ? htmlspecialchars(json_encode($jsonDecoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) : '❌ JSON non valide' ?></pre>
    </div>

    <div class="section">
        <h2>📡 En-têtes HTTP</h2>
        <pre><?= htmlspecialchars(implode("\n", $httpHeaders)) ?></pre>
    </div>
<?php endif; ?>

</body>
</html>