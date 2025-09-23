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

// Initialiser les variables
$response = '';
$jsonDecoded = null;
$httpHeaders = [];
$partnerResponse = '';
$partnerDecoded = null;
$partnerHeaders = [];

// ▶️ Requête GET (test d'URL)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['api_url'])) {
    $context = [
        'http' => [
            'method' => 'GET',
            'header' => "Authorization: Bearer $accessToken\r\n",
            'ignore_errors' => true,
        ]
    ];

    stream_context_set_default($context);
    $response = @file_get_contents($apiUrl, false, stream_context_create($context));
    $jsonDecoded = json_decode($response, true);
    $httpHeaders = $http_response_header ?? [];
}

// ▶️ Requête POST pour /partner_accounts
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_domain'])) {
    $domain = trim($_POST['register_domain']);
    $partnerUrl = $fleetBaseUrl . '/api/1/partner_accounts';
    $partnerPayload = json_encode(['domain' => $domain]);

    $partnerContext = [
        'http' => [
            'method' => 'POST',
            'header' => "Authorization: Bearer $accessToken\r\nContent-Type: application/json\r\n",
            'content' => $partnerPayload,
            'ignore_errors' => true
        ]
    ];

    $partnerResponse = @file_get_contents($partnerUrl, false, stream_context_create($partnerContext));
    $partnerDecoded = json_decode($partnerResponse, true);
    $partnerHeaders = $http_response_header ?? [];
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
        h1 { color: #007bff; }
        textarea, pre {
            width: 100%;
            font-family: monospace;
            font-size: 0.9em;
            background: #f1f1f1;
            padding: 10px;
            overflow-x: auto;
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
    </style>
</head>
<body>

<h1>🔧 Debug API Tesla Fleet</h1>

<div class="section">
    <h2>🔐 Token actuel (tronqué)</h2>
    <code><?= htmlspecialchars(substr($accessToken, 0, 80)) ?>... (<?= strlen($accessToken) ?> caractères)</code>
</div>

<div class="section">
    <h2>🔗 Tester une URL Tesla Fleet API (GET)</h2>
    <form method="post">
        <label for="api_url">URL API à tester :</label>
        <input type="text" id="api_url" name="api_url" value="<?= htmlspecialchars($apiUrl) ?>">
        <input type="submit" value="📡 Lancer la requête GET">
    </form>
</div>

<?php if (!empty($response)): ?>
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

<div class="section">
    <h2>🧾 Enregistrer un domaine partenaire (POST /partner_accounts)</h2>
    <form method="post">
        <label for="register_domain">Nom de domaine :</label>
        <input type="text" id="register_domain" name="register_domain" value="app.jeromemarlier.com" required>
        <input type="submit" value="📤 Enregistrer domaine">
    </form>
</div>

<?php if (!empty($partnerResponse)): ?>
    <div class="section">
        <h2>✅ Résultat /partner_accounts</h2>
        <h3>📨 Réponse brute</h3>
        <textarea readonly><?= htmlspecialchars($partnerResponse) ?></textarea>

        <h3>📦 JSON décodé</h3>
        <pre><?= $partnerDecoded ? htmlspecialchars(json_encode($partnerDecoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) : '❌ JSON non valide' ?></pre>

        <h3>📡 En-têtes HTTP</h3>
        <pre><?= htmlspecialchars(implode("\n", $partnerHeaders)) ?></pre>
    </div>
<?php endif; ?>

</body>
</html>