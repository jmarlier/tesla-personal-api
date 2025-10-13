<?php

/**
 * Tesla API Setup Dashboard
 * Dashboard unique pour vérifier étape par étape la connexion à l'API Tesla
 */

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tesla API Setup Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #0a0a0a;
            color: #e0e0e0;
            min-height: 100vh;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        /* Sidebar gauche - Timeline des étapes */
        .sidebar {
            width: 320px;
            background: #1a1a1a;
            border-right: 1px solid #2a2a2a;
            padding: 30px 20px;
            overflow-y: auto;
        }

        .sidebar-header {
            margin-bottom: 30px;
        }

        .sidebar-header h1 {
            color: #e63946;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            color: #888;
            font-size: 14px;
        }

        .step-item {
            margin-bottom: 20px;
            padding: 15px;
            background: #242424;
            border-radius: 10px;
            border-left: 4px solid #3a3a3a;
            cursor: pointer;
            transition: all 0.3s;
        }

        .step-item:hover {
            background: #2a2a2a;
        }

        .step-item.active {
            border-left-color: #e63946;
            background: #2d1f21;
        }

        .step-item.completed {
            border-left-color: #4caf50;
        }

        .step-item.error {
            border-left-color: #f44336;
        }

        .step-header {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .step-icon {
            font-size: 20px;
            margin-right: 10px;
            width: 24px;
            text-align: center;
        }

        .step-title {
            font-weight: 600;
            font-size: 15px;
        }

        .step-desc {
            font-size: 13px;
            color: #888;
            margin-left: 34px;
        }

        /* Panneau principal */
        .main-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .header {
            background: #1a1a1a;
            padding: 25px 40px;
            border-bottom: 1px solid #2a2a2a;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .progress-bar {
            flex: 1;
            max-width: 500px;
            height: 8px;
            background: #2a2a2a;
            border-radius: 10px;
            overflow: hidden;
            margin: 0 30px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #e63946, #ff6b6b);
            transition: width 0.5s ease;
            width: 0%;
        }

        .reset-btn {
            background: #e63946;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .reset-btn:hover {
            background: #d62839;
        }

        .content {
            padding: 40px;
            flex: 1;
        }

        .card {
            background: #1a1a1a;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid #2a2a2a;
        }

        .card-header {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #fff;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #aaa;
            font-size: 14px;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            background: #242424;
            border: 1px solid #3a3a3a;
            border-radius: 8px;
            color: #e0e0e0;
            font-size: 14px;
        }

        .input-group input:focus {
            outline: none;
            border-color: #e63946;
        }

        .btn {
            background: #e63946;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #d62839;
        }

        .btn:disabled {
            background: #3a3a3a;
            cursor: not-allowed;
        }

        .btn-secondary {
            background: #3a3a3a;
            margin-left: 10px;
        }

        .btn-secondary:hover {
            background: #4a4a4a;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .info-item {
            background: #242424;
            padding: 15px;
            border-radius: 8px;
        }

        .info-label {
            color: #888;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .info-value {
            color: #fff;
            font-size: 16px;
            font-weight: 600;
        }

        .vehicle-card {
            background: #242424;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .vehicle-name {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 10px;
        }

        .badge-success {
            background: #4caf50;
            color: white;
        }

        .badge-warning {
            background: #ff9800;
            color: white;
        }

        .badge-error {
            background: #f44336;
            color: white;
        }

        .command-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }

        .command-btn {
            background: #2a2a2a;
            border: 1px solid #3a3a3a;
            color: #e0e0e0;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }

        .command-btn:hover {
            background: #3a3a3a;
            border-color: #e63946;
        }

        /* Logs */
        .logs-container {
            background: #0d0d0d;
            border-radius: 8px;
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }

        .log-entry {
            margin-bottom: 10px;
            padding: 8px;
            border-left: 3px solid #3a3a3a;
            background: #1a1a1a;
        }

        .log-entry.success {
            border-left-color: #4caf50;
        }

        .log-entry.error {
            border-left-color: #f44336;
        }

        .log-entry.info {
            border-left-color: #2196f3;
        }

        .log-time {
            color: #666;
            font-size: 11px;
        }

        .log-message {
            color: #e0e0e0;
            margin-top: 3px;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #1a1a1a;
            border: 1px solid #3a3a3a;
            border-radius: 8px;
            padding: 15px 20px;
            min-width: 300px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            transform: translateX(400px);
            transition: transform 0.3s;
            z-index: 1000;
        }

        .notification.show {
            transform: translateX(0);
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #3a3a3a;
            border-top: 2px solid #e63946;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-left: 10px;
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
        <!-- Sidebar Timeline -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h1>🚗 Tesla API</h1>
                <p>Setup Dashboard</p>
            </div>

            <div class="step-item" data-step="1" onclick="showStep(1)">
                <div class="step-header">
                    <span class="step-icon" id="step1-icon">⚠️</span>
                    <span class="step-title">Authentification</span>
                </div>
                <div class="step-desc">OAuth 2.0 Tesla</div>
            </div>

            <div class="step-item" data-step="2" onclick="showStep(2)">
                <div class="step-header">
                    <span class="step-icon" id="step2-icon">⚠️</span>
                    <span class="step-title">Véhicules</span>
                </div>
                <div class="step-desc">Liste des véhicules</div>
            </div>

            <div class="step-item" data-step="3" onclick="showStep(3)">
                <div class="step-header">
                    <span class="step-icon" id="step3-icon">⚠️</span>
                    <span class="step-title">Données véhicule</span>
                </div>
                <div class="step-desc">État et statistiques</div>
            </div>

            <div class="step-item" data-step="4" onclick="showStep(4)">
                <div class="step-header">
                    <span class="step-icon" id="step4-icon">⚠️</span>
                    <span class="step-title">Commandes</span>
                </div>
                <div class="step-desc">Test des commandes API</div>
            </div>

            <div class="step-item" data-step="5" onclick="showStep(5)">
                <div class="step-header">
                    <span class="step-icon" id="step5-icon">📋</span>
                    <span class="step-title">Logs & Debug</span>
                </div>
                <div class="step-desc">Historique des requêtes</div>
            </div>
        </div>

        <!-- Main Panel -->
        <div class="main-panel">
            <div class="header">
                <div>
                    <h2 style="font-size: 18px; margin-bottom: 5px;">Configuration Tesla API</h2>
                    <p style="font-size: 13px; color: #888;">Vérification étape par étape</p>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progress"></div>
                </div>
                <button class="reset-btn" onclick="resetSetup()">🔄 Réinitialiser</button>
            </div>

            <div class="content">
                <!-- Step 1: Authentification -->
                <div id="step-1" class="step-content">
                    <div class="card">
                        <div class="card-header">🔐 Authentification Tesla</div>
                        <div class="input-group">
                            <label>Access Token Tesla (ou utilisez le bouton ci-dessous)</label>
                            <input type="text" id="access-token" placeholder="Collez votre access token ici...">
                        </div>
                        <button class="btn" onclick="authenticateWithToken()">✅ Valider le token</button>
                        <button class="btn btn-secondary" onclick="window.location.href='login.php'">🔑 Se connecter avec Tesla OAuth</button>

                        <div id="auth-info" style="margin-top: 20px; display: none;">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">Status</div>
                                    <div class="info-value" id="auth-status">-</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Token</div>
                                    <div class="info-value" id="auth-token" style="font-size: 12px; word-break: break-all;">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Véhicules -->
                <div id="step-2" class="step-content" style="display: none;">
                    <div class="card">
                        <div class="card-header">🚗 Mes Véhicules Tesla</div>
                        <button class="btn" onclick="loadVehicles()">🔄 Charger les véhicules</button>
                        <div id="vehicles-list" style="margin-top: 20px;"></div>
                    </div>
                </div>

                <!-- Step 3: Données véhicule -->
                <div id="step-3" class="step-content" style="display: none;">
                    <div class="card">
                        <div class="card-header">📊 Données du véhicule</div>
                        <button class="btn" onclick="loadVehicleData()">📥 Charger les données</button>
                        <label style="margin-left: 10px; color: #888;">
                            <input type="checkbox" id="auto-refresh" onchange="toggleAutoRefresh()"> Rafraîchir toutes les 60s
                        </label>
                        <div id="vehicle-data" style="margin-top: 20px;"></div>
                    </div>
                </div>

                <!-- Step 4: Commandes -->
                <div id="step-4" class="step-content" style="display: none;">
                    <div class="card">
                        <div class="card-header">🎮 Test des commandes</div>
                        <div class="command-grid">
                            <button class="command-btn" onclick="sendCommand('auto_conditioning_start')">❄️ Démarrer clim</button>
                            <button class="command-btn" onclick="sendCommand('auto_conditioning_stop')">🔥 Arrêter clim</button>
                            <button class="command-btn" onclick="sendCommand('door_lock')">🔒 Verrouiller</button>
                            <button class="command-btn" onclick="sendCommand('door_unlock')">🔓 Déverrouiller</button>
                            <button class="command-btn" onclick="sendCommand('charge_start')">🔌 Démarrer charge</button>
                            <button class="command-btn" onclick="sendCommand('charge_stop')">⏹️ Arrêter charge</button>
                            <button class="command-btn" onclick="sendCommand('flash_lights')">💡 Flash lights</button>
                            <button class="command-btn" onclick="sendCommand('honk_horn')">📢 Klaxon</button>
                        </div>
                        <div id="command-result" style="margin-top: 20px;"></div>
                    </div>
                </div>

                <!-- Step 5: Logs -->
                <div id="step-5" class="step-content" style="display: none;">
                    <div class="card">
                        <div class="card-header">📋 Logs & Debug</div>
                        <button class="btn" onclick="clearLogs()">🗑️ Effacer les logs</button>
                        <div class="logs-container" id="logs"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification -->
    <div id="notification" class="notification"></div>

    <script>
        let currentStep = 1;
        let selectedVehicleId = null;
        let autoRefreshInterval = null;
        let logs = [];

        // Initialisation
        document.addEventListener('DOMContentLoaded', () => {
            showStep(1);
            checkExistingSession();
        });

        function checkExistingSession() {
            fetch('api/check-session.php')
                .then(r => r.json())
                .then(data => {
                    if (data.authenticated) {
                        document.getElementById('access-token').value = data.token.substring(0, 20) + '...';
                        updateStepStatus(1, 'completed');
                        addLog('info', 'Session existante détectée');
                        showStep(2);
                    }
                });
        }

        function showStep(step) {
            // Hide all steps
            document.querySelectorAll('.step-content').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.step-item').forEach(el => el.classList.remove('active'));

            // Show selected step
            document.getElementById(`step-${step}`).style.display = 'block';
            document.querySelector(`[data-step="${step}"]`).classList.add('active');
            currentStep = step;
        }

        function updateProgress() {
            const completed = document.querySelectorAll('.step-item.completed').length;
            const total = 4; // 4 étapes principales (sans logs)
            const percentage = (completed / total) * 100;
            document.getElementById('progress').style.width = percentage + '%';
        }

        function updateStepStatus(step, status) {
            const item = document.querySelector(`[data-step="${step}"]`);
            const icon = document.getElementById(`step${step}-icon`);

            item.classList.remove('completed', 'error');

            if (status === 'completed') {
                item.classList.add('completed');
                icon.textContent = '✅';
            } else if (status === 'error') {
                item.classList.add('error');
                icon.textContent = '❌';
            } else {
                icon.textContent = '⚠️';
            }

            updateProgress();
        }

        function addLog(type, message) {
            const now = new Date().toLocaleTimeString('fr-FR');
            const log = {
                time: now,
                type,
                message
            };
            logs.unshift(log);

            const logsContainer = document.getElementById('logs');
            const logEntry = document.createElement('div');
            logEntry.className = `log-entry ${type}`;
            logEntry.innerHTML = `
                <div class="log-time">${now}</div>
                <div class="log-message">${message}</div>
            `;
            logsContainer.insertBefore(logEntry, logsContainer.firstChild);
        }

        function showNotification(message, type = 'info') {
            const notif = document.getElementById('notification');
            notif.innerHTML = `
                <div style="display: flex; align-items: center;">
                    <span style="font-size: 20px; margin-right: 10px;">${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'}</span>
                    <div>${message}</div>
                </div>
            `;
            notif.classList.add('show');

            setTimeout(() => {
                notif.classList.remove('show');
            }, 3000);
        }

        async function authenticateWithToken() {
            const token = document.getElementById('access-token').value.trim();

            if (!token) {
                showNotification('Veuillez entrer un access token', 'error');
                return;
            }

            addLog('info', 'Tentative d\'authentification avec le token...');

            try {
                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        access_token: token
                    })
                });

                const data = await response.json();

                if (data.success) {
                    updateStepStatus(1, 'completed');
                    document.getElementById('auth-info').style.display = 'block';
                    document.getElementById('auth-status').textContent = '✅ Authentifié';
                    document.getElementById('auth-token').textContent = token.substring(0, 30) + '...';
                    addLog('success', 'Authentification réussie !');
                    showNotification('Authentification réussie !', 'success');
                    setTimeout(() => showStep(2), 1000);
                } else {
                    updateStepStatus(1, 'error');
                    addLog('error', 'Échec d\'authentification : ' + (data.error || 'Erreur inconnue'));
                    showNotification('Échec d\'authentification', 'error');
                }
            } catch (error) {
                updateStepStatus(1, 'error');
                addLog('error', 'Erreur : ' + error.message);
                showNotification('Erreur de connexion', 'error');
            }
        }

        async function loadVehicles() {
            addLog('info', 'Chargement des véhicules...');

            try {
                const response = await fetch('api/vehicles.php');
                const data = await response.json();

                if (data.success && data.vehicles) {
                    const vehiclesList = document.getElementById('vehicles-list');
                    vehiclesList.innerHTML = '';

                    if (data.vehicles.length === 0) {
                        vehiclesList.innerHTML = '<p style="color: #888;">Aucun véhicule trouvé</p>';
                        updateStepStatus(2, 'error');
                        addLog('error', 'Aucun véhicule trouvé');
                        return;
                    }

                    data.vehicles.forEach(vehicle => {
                        const card = document.createElement('div');
                        card.className = 'vehicle-card';
                        const badgeClass = vehicle.state === 'online' ? 'badge-success' : vehicle.state === 'asleep' ? 'badge-warning' : 'badge-error';
                        card.innerHTML = `
                            <div class="vehicle-name">
                                ${vehicle.display_name || 'Tesla'}
                                <span class="badge ${badgeClass}">${vehicle.state || 'unknown'}</span>
                            </div>
                            <div style="font-size: 13px; color: #888; margin-top: 5px;">
                                <div>VIN: ${vehicle.vin || 'N/A'}</div>
                                <div>ID: ${vehicle.id || 'N/A'}</div>
                            </div>
                            <button class="btn" style="margin-top: 10px;" onclick="selectVehicle(${vehicle.id}, '${vehicle.display_name}')">
                                Sélectionner ce véhicule
                            </button>
                        `;
                        vehiclesList.appendChild(card);
                    });

                    updateStepStatus(2, 'completed');
                    addLog('success', `${data.vehicles.length} véhicule(s) trouvé(s)`);
                    showNotification(`${data.vehicles.length} véhicule(s) trouvé(s)`, 'success');
                } else {
                    updateStepStatus(2, 'error');
                    addLog('error', 'Erreur lors du chargement des véhicules');
                    showNotification('Erreur lors du chargement', 'error');
                }
            } catch (error) {
                updateStepStatus(2, 'error');
                addLog('error', 'Erreur : ' + error.message);
                showNotification('Erreur de connexion', 'error');
            }
        }

        function selectVehicle(id, name) {
            selectedVehicleId = id;
            addLog('success', `Véhicule sélectionné : ${name} (ID: ${id})`);
            showNotification(`Véhicule sélectionné : ${name}`, 'success');
            setTimeout(() => showStep(3), 500);
        }

        async function loadVehicleData() {
            if (!selectedVehicleId) {
                showNotification('Veuillez d\'abord sélectionner un véhicule', 'error');
                return;
            }

            addLog('info', 'Chargement des données du véhicule...');

            try {
                const response = await fetch(`api/data.php?vehicle_id=${selectedVehicleId}`);
                const data = await response.json();

                if (data.success && data.vehicle_data) {
                    const vd = data.vehicle_data;
                    const vehicleData = document.getElementById('vehicle-data');

                    vehicleData.innerHTML = `
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">🔋 Niveau de charge</div>
                                <div class="info-value">${vd.charge_state?.battery_level || 'N/A'}%</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">🔌 État de charge</div>
                                <div class="info-value">${vd.charge_state?.charging_state || 'N/A'}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">❄️ Climatisation</div>
                                <div class="info-value">${vd.climate_state?.is_climate_on ? '✅ Active' : '❌ Inactive'}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">🚪 Portes</div>
                                <div class="info-value">${vd.vehicle_state?.locked ? '🔒 Verrouillées' : '🔓 Déverrouillées'}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">📍 Localisation</div>
                                <div class="info-value" style="font-size: 12px;">${vd.drive_state?.latitude?.toFixed(4) || 'N/A'}, ${vd.drive_state?.longitude?.toFixed(4) || 'N/A'}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">🏁 Autonomie</div>
                                <div class="info-value">${vd.charge_state?.battery_range?.toFixed(0) || 'N/A'} km</div>
                            </div>
                        </div>
                    `;

                    updateStepStatus(3, 'completed');
                    addLog('success', 'Données du véhicule chargées avec succès');
                    showNotification('Données chargées avec succès', 'success');
                } else {
                    updateStepStatus(3, 'error');
                    addLog('error', 'Erreur : ' + (data.error || 'Impossible de charger les données'));
                    showNotification('Erreur lors du chargement des données', 'error');
                }
            } catch (error) {
                updateStepStatus(3, 'error');
                addLog('error', 'Erreur : ' + error.message);
                showNotification('Erreur de connexion', 'error');
            }
        }

        function toggleAutoRefresh() {
            const checkbox = document.getElementById('auto-refresh');

            if (checkbox.checked) {
                autoRefreshInterval = setInterval(loadVehicleData, 60000);
                addLog('info', 'Rafraîchissement automatique activé (60s)');
                showNotification('Rafraîchissement automatique activé', 'success');
            } else {
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                    autoRefreshInterval = null;
                }
                addLog('info', 'Rafraîchissement automatique désactivé');
            }
        }

        async function sendCommand(command) {
            if (!selectedVehicleId) {
                showNotification('Veuillez d\'abord sélectionner un véhicule', 'error');
                return;
            }

            addLog('info', `Envoi de la commande : ${command}...`);
            showNotification(`Envoi de la commande...`, 'info');

            try {
                const response = await fetch('api/command.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        vehicle_id: selectedVehicleId,
                        command: command
                    })
                });

                const data = await response.json();

                if (data.success) {
                    updateStepStatus(4, 'completed');
                    addLog('success', `Commande "${command}" exécutée avec succès`);
                    showNotification('Commande exécutée avec succès !', 'success');

                    const resultDiv = document.getElementById('command-result');
                    resultDiv.innerHTML = `
                        <div class="info-item" style="background: #1e3a1e; border-left: 3px solid #4caf50;">
                            <div class="info-label">Dernière commande</div>
                            <div class="info-value">${command} ✅</div>
                        </div>
                    `;
                } else {
                    updateStepStatus(4, 'error');
                    addLog('error', `Échec de la commande "${command}" : ${data.error || 'Erreur inconnue'}`);
                    showNotification('Échec de la commande', 'error');
                }
            } catch (error) {
                updateStepStatus(4, 'error');
                addLog('error', 'Erreur : ' + error.message);
                showNotification('Erreur de connexion', 'error');
            }
        }

        function clearLogs() {
            logs = [];
            document.getElementById('logs').innerHTML = '';
            addLog('info', 'Logs effacés');
        }

        function resetSetup() {
            if (confirm('Voulez-vous vraiment réinitialiser le setup ? La session sera effacée.')) {
                fetch('api/reset.php', {
                        method: 'POST'
                    })
                    .then(() => {
                        location.reload();
                    });
            }
        }
    </script>
</body>

</html>