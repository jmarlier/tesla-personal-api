# 🚗 Tesla Fleet API - Authentification OAuth 2.0

Application PHP sécurisée pour s'authentifier auprès de l'API Tesla Fleet en utilisant OAuth 2.0 avec des JWT signés ES256 (ECDSA).

## 📋 Table des matières

- [Fonctionnalités](#-fonctionnalités)
- [Architecture sécurisée](#-architecture-sécurisée)
- [Prérequis](#-prérequis)
- [Installation](#-installation)
- [Configuration](#️-configuration)
- [Utilisation](#-utilisation)
- [Sécurité](#-sécurité)
- [API Tesla Fleet](#-api-tesla-fleet)

## ✨ Fonctionnalités

- ✅ Génération de JWT avec signature ES256 (ECDSA + SHA-256)
- ✅ Authentification OAuth 2.0 client credentials flow
- ✅ Stockage sécurisé de la clé privée (hors du dossier public)
- ✅ Configuration via fichier `.env`
- ✅ Interface web moderne et responsive
- ✅ Script CLI pour tests rapides
- ✅ Classe PHP réutilisable et orientée objet

## 🏗 Architecture sécurisée

```
tesla-app/
├── public/                    # ← Document root du serveur web
│   ├── index.php             # Interface web principale
│   └── get-token.php         # Endpoint API pour obtenir le token
│
├── config/                    # ← Configuration sensible (HORS du public)
│   └── private-key.pem       # ← Clé privée EC (secp256r1)
│
├── src/                       # ← Code métier
│   └── TeslaAuth.php         # Classe d'authentification
│
├── .env                       # ← Variables d'environnement (NON versionné)
├── .env.example              # ← Template de configuration (versionné)
├── .gitignore                # ← Protection des fichiers sensibles
├── cli-get-token.php         # ← Script CLI
├── composer.json             # ← Dépendances PHP
└── README.md                 # ← Ce fichier
```

**🔒 Principe de sécurité :** Seul le dossier `public/` doit être exposé au web. Les fichiers sensibles (clés, `.env`) sont stockés en dehors.

## 🔧 Prérequis

- PHP 8.0 ou supérieur
- Composer
- Extension PHP `curl`
- Extension PHP `openssl`
- Une clé privée EC au format PEM (courbe secp256r1 / P-256)
- Un compte développeur Tesla avec une application enregistrée

## 📦 Installation

### 1. Cloner le projet

```bash
git clone <url-du-repo>
cd tesla-app
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Créer la structure de dossiers

```bash
mkdir -p config public
```

### 4. Configurer l'environnement

```bash
# Copier le template de configuration
cp .env.example .env

# Éditer .env avec vos valeurs
nano .env
```

### 5. Installer votre clé privée

```bash
# Copier votre clé privée dans le dossier config
cp /chemin/vers/votre/private-key.pem config/private-key.pem

# Sécuriser les permissions
chmod 600 config/private-key.pem
```

## ⚙️ Configuration

Éditez le fichier `.env` avec vos informations :

```env
# Client ID de votre application Tesla
TESLA_CLIENT_ID=votre-client-id-ici

# Chemin vers la clé privée EC (secp256r1) au format PEM
TESLA_PRIVATE_KEY_PATH=config/private-key.pem

# URL de l'API Tesla Fleet
TESLA_FLEET_API_URL=https://fleet-api.prd.na.vn.cloud.tesla.com

# Scopes demandés (séparés par des espaces)
TESLA_SCOPES=fleet_api:vehicles:read fleet_api:vehicles:write
```

### Obtenir un Client ID Tesla

1. Créez un compte sur [developer.tesla.com](https://developer.tesla.com)
2. Créez une nouvelle application
3. Générez une paire de clés EC (secp256r1)
4. Récupérez votre Client ID

### Générer une clé EC secp256r1

```bash
# Générer la clé privée
openssl ecparam -name prime256v1 -genkey -noout -out private-key.pem

# Générer la clé publique correspondante
openssl ec -in private-key.pem -pubout -out public-key.pem
```

⚠️ **Important :** Uploadez uniquement la clé **publique** sur le portail Tesla Developer.

## 🚀 Utilisation

### Via la ligne de commande (CLI)

```bash
php cli-get-token.php
```

Sortie attendue :

```
🚗 Tesla Fleet API - Authentification OAuth 2.0
==================================================

📋 Client ID: c9c40292-ddb3-4a87-9cc0-5a0193081024
🔐 Clé privée: config/private-key.pem

⏳ Génération du JWT et requête à l'API...

✅ Access token obtenu avec succès!

🔑 Access Token: eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6I...
📝 Type: bearer
⏱️  Expire dans: 28800 secondes
```

### Via le navigateur (Web)

1. Configurez votre serveur web (Apache/Nginx) avec `public/` comme document root
2. Accédez à `http://localhost/` ou votre domaine
3. Cliquez sur "🔑 Obtenir un Access Token"
4. Le token s'affichera dans une interface moderne

### Configuration serveur web

#### Apache (`.htaccess` dans `public/`)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name tesla-app.local;
    root /chemin/vers/tesla-app/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.env {
        deny all;
    }
}
```

### Utilisation programmatique

```php
<?php

require 'vendor/autoload.php';

use TeslaApp\TeslaAuth;
use Dotenv\Dotenv;

// Charger la configuration
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Créer une instance depuis l'environnement
$auth = TeslaAuth::fromEnv();

// Obtenir un access token
try {
    $tokenData = $auth->getAccessToken();

    echo "Access Token: " . $tokenData['access_token'];
    echo "\nExpire dans: " . $tokenData['expires_in'] . " secondes";

} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
```

## 🔐 Sécurité

### ✅ Bonnes pratiques implémentées

1. **Clé privée hors du dossier public** : Stockée dans `config/` qui n'est jamais exposé au web
2. **`.env` non versionné** : Le `.gitignore` empêche de commit les secrets
3. **Permissions strictes** : `chmod 600` sur la clé privée
4. **Pas de secrets en dur** : Tout est dans `.env`
5. **Validation des entrées** : Exceptions claires en cas d'erreur
6. **HTTPS recommandé** : Pour la production, toujours utiliser HTTPS

### ⚠️ Checklist de sécurité

- [ ] La clé privée est dans `config/` et jamais dans `public/`
- [ ] Le fichier `.env` n'est pas versionné
- [ ] Les permissions de `config/private-key.pem` sont 600
- [ ] Le serveur web expose uniquement `public/`
- [ ] HTTPS est activé en production
- [ ] Les logs ne contiennent pas de secrets

### 🚨 Fichiers à ne JAMAIS versionner

```gitignore
.env
*.pem
*.key
/config/private-key.pem
```

## 🌐 API Tesla Fleet

### Endpoints disponibles

Une fois authentifié, vous pouvez utiliser l'access token pour appeler les endpoints Tesla :

```bash
# Lister les véhicules
curl -X GET "https://fleet-api.prd.na.vn.cloud.tesla.com/api/1/vehicles" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"

# Obtenir les infos d'un véhicule
curl -X GET "https://fleet-api.prd.na.vn.cloud.tesla.com/api/1/vehicles/{id}" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"

# Envoyer une commande
curl -X POST "https://fleet-api.prd.na.vn.cloud.tesla.com/api/1/vehicles/{id}/command/wake_up" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### Documentation officielle

- [Tesla Fleet API Docs](https://developer.tesla.com/docs/fleet-api)
- [OAuth 2.0 Flow](https://developer.tesla.com/docs/fleet-api/authentication/oauth)
- [Available Endpoints](https://developer.tesla.com/docs/fleet-api/endpoints)

## 🧪 Dépannage

### Erreur : "Clé privée introuvable"

```bash
# Vérifier que la clé existe
ls -la config/private-key.pem

# Vérifier le chemin dans .env
cat .env | grep TESLA_PRIVATE_KEY_PATH
```

### Erreur : "Variables d'environnement manquantes"

```bash
# Vérifier que .env existe et contient les bonnes valeurs
cat .env
```

### Erreur HTTP 401 Unauthorized

- Vérifiez que votre Client ID est correct
- Assurez-vous que la clé privée correspond à la clé publique uploadée sur Tesla
- Vérifiez que votre application Tesla est bien configurée

### Erreur HTTP 403 Forbidden

- Vérifiez les scopes demandés dans `.env`
- Assurez-vous que votre application a les permissions nécessaires

## 📝 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à ouvrir une issue ou un pull request.

## 📧 Support

Pour toute question ou problème :

- Consultez la [documentation Tesla](https://developer.tesla.com)
- Ouvrez une issue sur GitHub
- Contactez le support Tesla Developer

---

**⚡ Propulsé par Tesla Fleet API**
