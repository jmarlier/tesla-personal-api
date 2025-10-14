# ⚡ ÉTAPE 4 : Tesla Fleet API Calls

Documentation complète pour l'utilisation de l'API Tesla Fleet.

---

## 📋 FICHIERS CRÉÉS

### 1. **Classe Helper**

#### `src/TeslaFleetClient.php`

Classe PHP pour faciliter les appels à l'API Tesla Fleet.

**Méthodes disponibles :**

- `getVehicles()` - Liste tous les véhicules
- `getVehicleData($vehicleId)` - Données détaillées d'un véhicule
- `wakeUp($vehicleId)` - Réveiller un véhicule
- `sendCommand($vehicleId, $command, $params)` - Envoyer une commande

**Usage :**

```php
use TeslaApp\TeslaFleetClient;

$client = new TeslaFleetClient($accessToken);
$vehicles = $client->getVehicles();

if ($client->isSuccess()) {
    foreach ($vehicles as $vehicle) {
        echo $vehicle['display_name'];
    }
}
```

---

### 2. **Endpoints API**

#### `api/vehicles.php`

**Récupère la liste de tous les véhicules Tesla**

**Requête vers Tesla :**

```
GET https://fleet-api.prd.na.vn.cloud.tesla.com/api/1/vehicles
Authorization: Bearer <user_access_token>
```

**Usage :**

```
GET /api/vehicles.php              (format HTML avec détails)
GET /api/vehicles.php?format=json  (format JSON pur)
```

**Réponse JSON :**

```json
{
  "success": true,
  "count": 2,
  "vehicles": [
    {
      "id": 123456789,
      "vin": "5YJ3E1EA...",
      "display_name": "Ma Model 3",
      "state": "online",
      "vehicle_name": "Model 3",
      "in_service": false
    }
  ],
  "http_code": 200
}
```

**Affichage :**

- ✅ Réponse complète de l'API Tesla
- ✅ Liste des véhicules avec cartes
- ✅ État (online/asleep/offline)
- ✅ Boutons d'action pour chaque véhicule

---

#### `api/vehicle-data.php`

**Récupère toutes les données d'un véhicule spécifique**

**Requête vers Tesla :**

```
GET https://fleet-api.prd.na.vn.cloud.tesla.com/api/1/vehicles/{id}/vehicle_data
Authorization: Bearer <user_access_token>
```

**Usage :**

```
GET /api/vehicle-data.php?id=123456789
GET /api/vehicle-data.php?id=123456789&format=json
```

**Données récupérées :**

- 🔋 **État de charge** : Niveau batterie, autonomie, état de chargement
- 🌡️ **Climat** : Température intérieure/extérieure, climatisation
- 📍 **Localisation** : GPS (latitude, longitude)
- 🔒 **État du véhicule** : Verrouillé, kilométrage, version firmware
- 🚗 **Informations générales** : Nom, VIN, état

**Réponse (extrait) :**

```json
{
  "success": true,
  "vehicle_data": {
    "id": 123456789,
    "display_name": "Ma Model 3",
    "state": "online",
    "charge_state": {
      "battery_level": 85,
      "battery_range": 245.3,
      "charging_state": "Disconnected"
    },
    "climate_state": {
      "inside_temp": 21.5,
      "outside_temp": 18.0,
      "is_climate_on": false
    },
    "drive_state": {
      "latitude": 48.8566,
      "longitude": 2.3522
    }
  }
}
```

**Affichage :**

- ✅ Réponse complète de l'API
- ✅ Grille de cartes avec toutes les données
- ✅ Lien vers Google Maps pour la localisation
- ✅ Boutons d'action (honk, flash, wake_up)

---

#### `api/send-command.php`

**Envoie une commande à un véhicule**

**Requête vers Tesla :**

```
POST https://fleet-api.prd.na.vn.cloud.tesla.com/api/1/vehicles/{id}/command/{command}
Authorization: Bearer <user_access_token>
```

**Usage :**

```
GET /api/send-command.php?vehicle_id=123&command=honk
POST /api/send-command.php (avec vehicle_id et command dans le body)
```

**Commandes disponibles :**

- ⏰ `wake_up` - Réveiller le véhicule
- 📯 `honk` - Klaxonner
- 💡 `flash_lights` - Flasher les phares
- 🔒 `lock` - Verrouiller
- 🔓 `unlock` - Déverrouiller
- 🌡️ `climate_on` - Activer la climatisation
- ❄️ `climate_off` - Désactiver la climatisation
- 🔌 `charge_start` - Démarrer la charge
- ⏸️ `charge_stop` - Arrêter la charge
- 🚪 `charge_port_door_open` - Ouvrir la trappe de charge
- 🚪 `charge_port_door_close` - Fermer la trappe de charge

**Réponse (succès) :**

```json
{
  "success": true,
  "command": "honk",
  "vehicle_id": "123456789",
  "result": {
    "result": true,
    "reason": ""
  },
  "http_code": 200
}
```

**Affichage :**

- ✅ Réponse complète de l'API
- ✅ Confirmation de l'exécution
- ✅ Grille des autres commandes disponibles

---

### 3. **Interface utilisateur**

#### `public/dashboard.php` (mis à jour)

**Tableau de bord avec chargement dynamique des véhicules**

**Fonctionnalités :**

- ✅ Chargement AJAX des véhicules depuis `api/vehicles.php`
- ✅ Affichage des véhicules en cartes
- ✅ État en temps réel (online/asleep/offline)
- ✅ Lien vers les détails de chaque véhicule
- ✅ Gestion des erreurs avec messages clairs

**Affichage :**

```
┌─────────────────────────────────┐
│ 🚗 Ma Model 3                   │
│ État : ✅ online                │
│ VIN : 5YJ3E1EA...               │
│ [ 📊 Voir détails ]             │
└─────────────────────────────────┘
```

---

## 🔍 EXEMPLE DE FLOW COMPLET

### Scénario : Récupérer et réveiller un véhicule

```php
<?php
require_once 'vendor/autoload.php';
use TeslaApp\TeslaFleetClient;

session_start();
$accessToken = $_SESSION['access_token'];

// 1. Créer le client
$client = new TeslaFleetClient($accessToken);

// 2. Récupérer la liste des véhicules
$vehicles = $client->getVehicles();

if ($client->isSuccess() && count($vehicles) > 0) {
    $firstVehicle = $vehicles[0];
    $vehicleId = $firstVehicle['id'];

    echo "Véhicule : " . $firstVehicle['display_name'] . "\n";
    echo "État : " . $firstVehicle['state'] . "\n";

    // 3. Si le véhicule est endormi, le réveiller
    if ($firstVehicle['state'] !== 'online') {
        echo "Réveil du véhicule...\n";
        $result = $client->wakeUp($vehicleId);

        if ($client->isSuccess()) {
            echo "✅ Véhicule réveillé !\n";
        }
    }

    // 4. Récupérer les données détaillées
    $vehicleData = $client->getVehicleData($vehicleId);

    if ($client->isSuccess()) {
        $chargeState = $vehicleData['charge_state'];
        echo "Batterie : " . $chargeState['battery_level'] . "%\n";
        echo "Autonomie : " . $chargeState['battery_range'] . " miles\n";
    }

    // 5. Envoyer une commande (klaxonner)
    $result = $client->sendCommand($vehicleId, 'honk');

    if ($client->isSuccess()) {
        echo "✅ Klaxon activé !\n";
    }
}

// Afficher la dernière réponse complète
echo "\nRéponse complète de l'API :\n";
echo json_encode($client->getLastResponse(), JSON_PRETTY_PRINT);
```

---

## 🐛 DEBUGGING

### Affichage des réponses complètes

Tous les fichiers affichent **la réponse complète de l'API Tesla** :

```html
📋 Réponse complète de l'API Tesla (HTTP 200)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ { "response": [ { "id": 123456789,
"vin": "5YJ3E1EA...", ... } ], "count": 1 }
```

### Erreurs communes

#### Erreur 408 : Vehicle Unavailable

```json
{
  "response": null,
  "error": "vehicle unavailable: vehicle is offline or asleep",
  "error_description": ""
}
```

**Solution :** Réveiller le véhicule avec `wake_up` d'abord.

#### Erreur 401 : Unauthorized

```json
{
  "error": "invalid_token",
  "error_description": "The access token provided is invalid"
}
```

**Solution :** Reconnectez-vous via OAuth2.

#### Erreur 404 : Not Found

```json
{
  "response": null,
  "error": "not_found"
}
```

**Solution :** Vérifiez l'ID du véhicule.

---

## 📊 TESTS

### Test 1 : Liste des véhicules

```bash
# Dans le navigateur
https://app.jeromemarlier.com/api/vehicles.php

# Résultat attendu :
✅ Affichage de tous vos véhicules
✅ Réponse complète de l'API visible
✅ Cartes cliquables pour chaque véhicule
```

### Test 2 : Données d'un véhicule

```bash
# Récupérer l'ID depuis vehicles.php, puis :
https://app.jeromemarlier.com/api/vehicle-data.php?id=YOUR_VEHICLE_ID

# Résultat attendu :
✅ Toutes les données du véhicule
✅ Batterie, localisation, climat, etc.
✅ Lien Google Maps fonctionnel
```

### Test 3 : Envoyer une commande

```bash
# Klaxonner :
https://app.jeromemarlier.com/api/send-command.php?vehicle_id=YOUR_ID&command=honk

# Résultat attendu :
✅ Commande exécutée
✅ Réponse de l'API affichée
✅ Le véhicule klaxonne (si en ligne)
```

### Test 4 : Dashboard

```bash
https://app.jeromemarlier.com/dashboard.php

# Résultat attendu :
✅ Véhicules chargés dynamiquement via AJAX
✅ Affichage en cartes
✅ Boutons fonctionnels
```

---

## 🔐 SÉCURITÉ

### Vérifications automatiques

Tous les endpoints vérifient :

1. ✅ Présence de l'access token en session
2. ✅ Validité des paramètres (vehicle_id, command)
3. ✅ Codes HTTP de retour
4. ✅ Gestion des erreurs

### Exemple de vérification

```php
if (!isset($_SESSION['access_token'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
```

---

## 📝 PROCHAINES AMÉLIORATIONS POSSIBLES

- [ ] Refresh automatique des tokens expirés
- [ ] Cache des données véhicules (pour éviter trop de requêtes)
- [ ] WebSocket pour les mises à jour en temps réel
- [ ] Interface de contrôle de la climatisation avec température
- [ ] Historique des commandes envoyées
- [ ] Notifications push pour l'état de charge

---

## 🎯 RÉSUMÉ

### Ce qui est maintenant fonctionnel :

✅ **Étape 1** : Fleet Auth Token  
✅ **Étape 2** : Partner Account  
✅ **Étape 3** : OAuth2 User Flow  
✅ **Étape 4** : Fleet API Calls

### Fichiers créés pour l'étape 4 :

- `src/TeslaFleetClient.php` - Classe helper
- `api/vehicles.php` - Liste des véhicules
- `api/vehicle-data.php` - Données détaillées
- `api/send-command.php` - Commandes
- `public/dashboard.php` - Mis à jour avec AJAX

**🎉 L'application Tesla Fleet API est complète et fonctionnelle ! 🎉**
