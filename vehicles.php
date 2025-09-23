<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// 🔐 Charger le token Fleet
$tokenPath = __DIR__ . '/tokens.json';
if (!file_exists($tokenPath)) {
    exit('❌ Aucune authentification trouvée. Retourne au <a href="login.php">login</a>.');
}

$tokens = json_decode(file_get_contents($tokenPath), true);
$accessToken = $tokens['access_token'] ?? null;

if (!$accessToken) {
    exit('❌ Token introuvable dans tokens.json.');
}

// 🌐 URL complète saisie par l'utilisateur (ou défaut vide)
$submittedUrl = $_GET['url'] ?? '';
$responseBody = '';
$httpHeaders = [];
$statusCode = 0;

if ($submittedUrl) {
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => "Authorization: Bearer $accessToken\r\n",
            'ignore_errors' => true
        ]
    ];

    $context = stream_context_create($opts);
    $responseBody = @file_get_contents($submittedUrl, false, $context);

    $httpHeaders = $http_response_header ?? [];
    $statusCode = 0;
    if (isset($httpHeaders[0]) && preg_match('#HTTP/\d+\.\d+ (\d+)#', $httpHeaders[0], $m)) {
        $statusCode = (int)$m[1];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🔍 Debug Fleet API - Tesla</title>
    <style>
        body { font-family: sans-serif; padding: 2rem; background: #f4f4f4; }
        input[type="text"] { width: 90%; padding: 0.5rem; }
        button { padding: 0.5rem 1rem; }
        pre { background: #eee; padding: 1rem; overflow-x: auto; white-space: pre-wrap; word-wrap: break-word; }
        .section { margin-top: 2rem; }
    </style>
</head>
<body>

<h1>🚘 Debug Fleet API - Tesla</h1>

<p><strong>Token actuel :</strong></p>
<pre><?= htmlspecialchars(substr($accessToken, 0, 100)) ?>...</pre>

<div class="section">
    <h2>🧪 Tester une URL Fleet API</h2>
    <form method="get">
        <input type="text" name="url" placeholder="Ex: https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/vehicles" value="<?= htmlspecialchars($submittedUrl) ?>" required />
        <button type="submit">Tester</button>
    </form>
</div>

<?php if ($submittedUrl): ?>
    <div class="section">
        <h2>📡 Réponse de Tesla (HTTP <?= $statusCode ?>)</h2>

        <h3>📍 URL appelée</h3>
        <pre><?= htmlspecialchars($submittedUrl) ?></pre>

        <h3>🧾 En-têtes HTTP</h3>
        <pre><?= htmlspecialchars(implode("\n", $httpHeaders)) ?></pre>

        <h3>📦 Corps brut</h3>
        <pre><?= htmlspecialchars($responseBody ?: '❌ Aucune réponse') ?></pre>

        <h3>🧠 JSON décodé</h3>
        <pre><?php
            $decoded = json_decode($responseBody, true);
            if ($decoded === null) {
                echo "⚠️ JSON non valide ou vide.";
            } else {
                print_r($decoded);
            }
        ?></pre>
    </div>
<?php endif; ?>

</body>
</html>