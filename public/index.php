<?php

/**
 * Point d'entrée principal de l'application Tesla Fleet API
 */

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tesla Fleet API - Authentication</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }

        .button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
            margin-bottom: 15px;
        }

        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .info-box strong {
            color: #667eea;
            display: block;
            margin-bottom: 5px;
        }

        .response {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            display: none;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 400px;
            overflow-y: auto;
        }

        .response.show {
            display: block;
        }

        .success {
            border-left-color: #28a745;
        }

        .success strong {
            color: #28a745;
        }

        .error {
            border-left-color: #dc3545;
        }

        .error strong {
            color: #dc3545;
        }

        .loading {
            display: none;
            text-align: center;
            margin: 20px 0;
        }

        .loading.show {
            display: block;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🚗 Tesla Fleet API</h1>
        <p class="subtitle">Authentification OAuth 2.0 avec JWT ES256</p>

        <div class="info-box">
            <strong>📋 Client ID:</strong>
            <?= htmlspecialchars($_ENV['TESLA_CLIENT_ID'] ?? 'Non configuré') ?>
        </div>

        <button class="button" onclick="getAccessToken()">
            🔑 Obtenir un Partner Token (JWT)
        </button>

        <a href="/login.php" class="button" style="display: block; text-align: center; text-decoration: none; margin-top: 10px;">
            👤 Se connecter avec Tesla (OAuth)
        </a>

        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p style="margin-top: 10px; color: #666;">Authentification en cours...</p>
        </div>

        <div class="response" id="response"></div>
    </div>

    <script>
        async function getAccessToken() {
            const responseDiv = document.getElementById('response');
            const loadingDiv = document.getElementById('loading');

            // Afficher le loading
            loadingDiv.classList.add('show');
            responseDiv.classList.remove('show');

            try {
                const response = await fetch('get-token.php');
                const data = await response.json();

                // Cacher le loading
                loadingDiv.classList.remove('show');

                // Afficher la réponse
                responseDiv.textContent = JSON.stringify(data, null, 2);
                responseDiv.classList.add('show');

            } catch (error) {
                loadingDiv.classList.remove('show');
                responseDiv.textContent = JSON.stringify({
                    success: false,
                    error: error.message
                }, null, 2);
                responseDiv.classList.add('show');
            }
        }
    </script>
</body>

</html>