# 📦 Guide de Migration - Tesla Fleet API

Ce guide vous aide à migrer depuis l'ancienne structure (avec `jwt.php`) vers la nouvelle architecture sécurisée.

## 🔄 Changements principaux

### Avant (ancienne structure)

```
tesla-app/
├── jwt.php                    # ❌ Clé en dur, tout dans un fichier
├── private-key.pem           # ❌ Dans le dossier public
└── ...
```

### Après (nouvelle structure)

```
tesla-app/
├── public/                    # ✅ Document root
│   ├── index.php
│   └── get-token.php
├── config/                    # ✅ Fichiers sensibles hors du public
│   └── private-key.pem
├── src/
│   └── TeslaAuth.php         # ✅ Code orienté objet
├── .env                       # ✅ Configuration centralisée
└── cli-get-token.php         # ✅ Script CLI
```

## 🚀 Étapes de migration

### Étape 1: Sauvegarder vos données

```bash
# Sauvegarder votre clé privée actuelle
cp private-key.pem private-key.pem.backup

# Sauvegarder votre configuration
cp jwt.php jwt.php.backup
```

### Étape 2: Exécuter le script d'installation

```bash
php setup.php
```

Ce script va :

- ✅ Créer la structure de dossiers (`config/`, `public/`, `src/`)
- ✅ Vérifier les dépendances PHP
- ✅ Créer le fichier `.env` depuis `.env.example`
- ✅ Vérifier la présence de la clé privée

### Étape 3: Déplacer votre clé privée

Si vous avez déjà une clé privée dans le dossier racine :

```bash
# Déplacer la clé vers le dossier sécurisé
mv private-key.pem config/private-key.pem

# Sécuriser les permissions
chmod 600 config/private-key.pem

# Supprimer l'ancienne clé (si vous avez fait une backup)
# rm private-key.pem.backup  # Optionnel
```

### Étape 4: Configurer le fichier .env

Éditez `.env` et remplissez vos informations :

```bash
nano .env
```

Remplacez les valeurs par celles de votre ancien `jwt.php` :

```env
# Récupérez le client_id depuis jwt.php (ligne ~9)
TESLA_CLIENT_ID=c9c40292-ddb3-4a87-9cc0-5a0193081024

# La clé est maintenant dans config/
TESLA_PRIVATE_KEY_PATH=config/private-key.pem

# URL de l'API (normalement inchangée)
TESLA_FLEET_API_URL=https://fleet-api.prd.na.vn.cloud.tesla.com

# Scopes (récupérez depuis jwt.php ligne ~38)
TESLA_SCOPES=fleet_api:vehicles:read
```

### Étape 5: Tester la nouvelle configuration

#### Test en ligne de commande

```bash
php cli-get-token.php
```

Sortie attendue :

```
🚗 Tesla Fleet API - Authentification OAuth 2.0
==================================================

✅ Access token obtenu avec succès!
```

#### Test via le web

1. Configurez votre serveur web pour pointer vers `public/`
2. Accédez à `http://localhost/`
3. Cliquez sur "Obtenir un Access Token"

### Étape 6: Migrer votre code personnalisé

Si vous avez du code personnalisé dans `jwt.php`, voici comment le migrer :

#### Ancien code (jwt.php)

```php
$jwt = JWT::encode($payload, $privateKey, 'ES256');

$ch = curl_init("{$fleetApiUrl}/oauth/token");
// ... requête cURL ...
$response = curl_exec($ch);
```

#### Nouveau code (utilisation de TeslaAuth)

```php
require 'vendor/autoload.php';

use TeslaApp\TeslaAuth;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$auth = TeslaAuth::fromEnv();
$tokenData = $auth->getAccessToken();

echo "Token: " . $tokenData['access_token'];
```

### Étape 7: Nettoyer les anciens fichiers

Une fois que tout fonctionne, vous pouvez supprimer les anciens fichiers :

```bash
# Supprimer l'ancien script
rm jwt.php

# Supprimer les backups (si tout fonctionne)
rm jwt.php.backup
rm private-key.pem.backup

# Supprimer les autres anciens fichiers si nécessaire
rm callback_old.php
rm com.tesla.3p.public-key.pem.old
```

⚠️ **Attention** : Assurez-vous que la nouvelle version fonctionne avant de supprimer les backups !

## 📋 Checklist de migration

- [ ] ✅ Backup de `private-key.pem` effectué
- [ ] ✅ Backup de `jwt.php` effectué
- [ ] ✅ Script `setup.php` exécuté avec succès
- [ ] ✅ Clé privée déplacée dans `config/private-key.pem`
- [ ] ✅ Permissions 600 appliquées sur la clé
- [ ] ✅ Fichier `.env` configuré avec vos valeurs
- [ ] ✅ Test CLI réussi (`php cli-get-token.php`)
- [ ] ✅ Test web réussi (interface `public/index.php`)
- [ ] ✅ Code personnalisé migré (si applicable)
- [ ] ✅ Anciens fichiers supprimés
- [ ] ✅ Serveur web configuré avec `public/` comme document root

## 🔐 Configuration du serveur web

### Apache

Créez un VirtualHost pointant vers `public/` :

```apache
<VirtualHost *:80>
    ServerName tesla-app.local
    DocumentRoot /chemin/vers/tesla-app/public

    <Directory /chemin/vers/tesla-app/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Bloquer l'accès au dossier config
    <Directory /chemin/vers/tesla-app/config>
        Require all denied
    </Directory>
</VirtualHost>
```

### Nginx

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

    # Bloquer l'accès aux fichiers sensibles
    location ~ /\.(env|pem|key) {
        deny all;
    }

    # Bloquer l'accès au dossier config
    location ^~ /config/ {
        deny all;
    }
}
```

## 🆘 Dépannage

### Problème : "Clé privée introuvable"

**Solution :**

```bash
# Vérifier où est votre clé
find . -name "*.pem" -type f

# Si elle est à la racine, la déplacer
mv private-key.pem config/private-key.pem
```

### Problème : "Variables d'environnement manquantes"

**Solution :**

```bash
# Vérifier que .env existe et contient les bonnes valeurs
cat .env

# Si .env est vide, copier depuis .env.example
cp .env.example .env
nano .env
```

### Problème : "Permission denied" sur la clé

**Solution :**

```bash
# Corriger les permissions
chmod 600 config/private-key.pem

# Vérifier
ls -la config/private-key.pem
# Doit afficher: -rw------- 1 user group ... private-key.pem
```

### Problème : Page blanche dans le navigateur

**Solution :**

```bash
# Vérifier les logs PHP
tail -f /var/log/apache2/error.log
# ou
tail -f /var/log/nginx/error.log

# Activer l'affichage des erreurs temporairement
echo "display_errors = On" >> php.ini
```

## 📚 Ressources

- [README.md](README.md) - Documentation complète
- [Tesla Fleet API](https://developer.tesla.com/docs/fleet-api)
- [OAuth 2.0 JWT Bearer](https://datatracker.ietf.org/doc/html/rfc7523)

---

**✅ Migration terminée !** Votre application est maintenant sécurisée et prête pour la production.
