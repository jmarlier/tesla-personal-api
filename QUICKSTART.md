# ⚡ Démarrage Rapide - Tesla Fleet API

Guide ultra-rapide pour démarrer avec l'API Tesla Fleet en **5 minutes**.

## 🚀 Installation express

```bash
# 1. Installer les dépendances
make install

# 2. Configuration automatique
make setup

# 3. Configurer .env
nano .env  # Ajoutez votre TESLA_CLIENT_ID

# 4. Générer une clé EC
make generate-key

# 5. Tester
make test
```

C'est tout ! ✅

## 📋 Prérequis

- ✅ PHP 8.0+
- ✅ Composer
- ✅ Un compte [developer.tesla.com](https://developer.tesla.com)

## 🔑 Configuration en 3 étapes

### Étape 1: Créer une application Tesla

1. Allez sur [developer.tesla.com](https://developer.tesla.com)
2. Cliquez sur "Create Application"
3. Remplissez les informations
4. Récupérez votre **Client ID**

### Étape 2: Générer les clés

```bash
make generate-key
```

Cela crée :

- `config/private-key.pem` (à garder SECRET ✅)
- `config/public-key.pem` (à uploader sur Tesla 📤)

### Étape 3: Configurer .env

```bash
# Éditer .env
nano .env
```

Remplir :

```env
TESLA_CLIENT_ID=votre-client-id-ici
TESLA_PRIVATE_KEY_PATH=config/private-key.pem
TESLA_FLEET_API_URL=https://fleet-api.prd.na.vn.cloud.tesla.com
TESLA_SCOPES=fleet_api:vehicles:read fleet_api:vehicles:write
```

Uploader la clé publique sur Tesla :

```bash
# La clé publique est dans:
cat config/public-key.pem
```

Copier-coller son contenu sur le portail Tesla.

## 🧪 Tester l'authentification

### Option 1: Ligne de commande

```bash
make test
```

Sortie attendue :

```
🚗 Tesla Fleet API - Authentification OAuth 2.0
==================================================

✅ Access token obtenu avec succès!

🔑 Access Token: eyJhbGciOiJ...
📝 Type: bearer
⏱️  Expire dans: 28800 secondes
```

### Option 2: Serveur web local

```bash
make dev
```

Ouvrir : [http://localhost:8000](http://localhost:8000)

### Option 3: Test API complet

```bash
make test-api
```

## 📚 Commandes utiles

| Commande            | Description                       |
| ------------------- | --------------------------------- |
| `make help`         | Afficher toutes les commandes     |
| `make install`      | Installer les dépendances         |
| `make setup`        | Configuration initiale            |
| `make test`         | Tester l'authentification         |
| `make test-api`     | Tester les appels API             |
| `make generate-key` | Générer une paire de clés         |
| `make secure`       | Vérifier les permissions          |
| `make check-config` | Vérifier la configuration         |
| `make dev`          | Serveur de développement          |
| `make audit`        | Audit de sécurité                 |
| `make clean`        | Nettoyer les fichiers temporaires |

## 💻 Utilisation dans votre code

### Exemple basique

```php
<?php
require 'vendor/autoload.php';

use TeslaApp\TeslaAuth;
use Dotenv\Dotenv;

// Charger la config
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// S'authentifier
$auth = TeslaAuth::fromEnv();
$token = $auth->getAccessToken();

echo "Token: " . $token['access_token'];
```

### Lister les véhicules

```php
$apiUrl = $_ENV['TESLA_FLEET_API_URL'];
$ch = curl_init("{$apiUrl}/api/1/vehicles");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer {$token['access_token']}",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$vehicles = json_decode($response, true);

print_r($vehicles);
```

### Envoyer une commande

```php
// Klaxonner
$vehicleId = '1234567890';
$ch = curl_init("{$apiUrl}/api/1/vehicles/{$vehicleId}/command/honk_horn");

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer {$token['access_token']}",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
```

## 🌐 Configuration serveur web

### Apache

```apache
<VirtualHost *:80>
    ServerName tesla-app.local
    DocumentRoot /path/to/tesla-app/public

    <Directory /path/to/tesla-app/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx

```nginx
server {
    listen 80;
    server_name tesla-app.local;
    root /path/to/tesla-app/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## 🔐 Sécurité - Checklist

- [ ] ✅ Le dossier `public/` est le document root
- [ ] ✅ Permissions 600 sur `config/private-key.pem`
- [ ] ✅ `.env` n'est pas versionné
- [ ] ✅ HTTPS activé en production
- [ ] ✅ Fichiers sensibles protégés (`.htaccess`)

Vérifier :

```bash
make audit
```

## 🆘 Dépannage rapide

### ❌ "Clé privée introuvable"

```bash
make generate-key
```

### ❌ "Variables d'environnement manquantes"

```bash
cp .env.example .env
nano .env
```

### ❌ "Permission denied"

```bash
make secure
```

### ❌ "HTTP 401 Unauthorized"

- Vérifiez que le Client ID est correct
- Assurez-vous d'avoir uploadé la bonne clé publique sur Tesla

### ❌ Page blanche

```bash
# Activer les erreurs temporairement
echo "display_errors = On" > php.ini
php -S localhost:8000 -t public/ -c php.ini
```

## 📖 Documentation complète

- 📘 [README.md](README.md) - Documentation complète
- 🔐 [SECURITY.md](SECURITY.md) - Guide de sécurité
- 🔄 [MIGRATION.md](MIGRATION.md) - Migrer depuis l'ancienne version
- 📚 [Tesla API Docs](https://developer.tesla.com/docs/fleet-api)

## 🎯 Prochaines étapes

1. ✅ **Configuration terminée** → Lisez le [README.md](README.md)
2. 🔐 **Sécurité** → Consultez [SECURITY.md](SECURITY.md)
3. 🚀 **Déploiement** → Configurez votre serveur web
4. 🧪 **Tests** → Explorez `example-api-call.php`

## 💡 Exemples de commandes API

```bash
# Lister les véhicules
GET /api/1/vehicles

# Réveiller un véhicule
POST /api/1/vehicles/{id}/wake_up

# Klaxonner
POST /api/1/vehicles/{id}/command/honk_horn

# Flasher les phares
POST /api/1/vehicles/{id}/command/flash_lights

# Verrouiller
POST /api/1/vehicles/{id}/command/door_lock

# Déverrouiller
POST /api/1/vehicles/{id}/command/door_unlock

# Climatisation ON
POST /api/1/vehicles/{id}/command/climate_on

# Climatisation OFF
POST /api/1/vehicles/{id}/command/climate_off
```

---

**🚗 Vous êtes prêt à rouler !**

Pour toute question, consultez la [documentation complète](README.md) ou créez une issue.
