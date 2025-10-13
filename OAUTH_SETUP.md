# 🚀 Configuration OAuth Tesla - Guide Rapide

Ce guide vous aide à configurer les **deux flux OAuth** pour votre application Tesla Fleet API.

## ✅ Ce qui a été configuré

Votre application supporte maintenant :

1. **Partner Token (JWT ES256)** - Machine-to-machine
2. **User OAuth (Authorization Code)** - Authentification utilisateur

## 📋 Configuration sur Tesla Developer Portal

### 1. Accéder au portail

Allez sur [developer.tesla.com](https://developer.tesla.com) et connectez-vous.

### 2. Créer/Modifier votre application

Dans les paramètres de votre application :

#### A. URLs à configurer

| Paramètre          | Valeur                                                                              |
| ------------------ | ----------------------------------------------------------------------------------- |
| **Public Key URL** | `https://app.jeromemarlier.com/.well-known/appspecific/com.tesla.3p.public-key.pem` |
| **Redirect URI**   | `https://app.jeromemarlier.com/callback.php`                                        |

#### B. Récupérer vos identifiants

Notez bien :

- ✅ **Client ID** : `c9c40292-ddb3-4a87-9cc0-5a0193081024` (déjà configuré)
- ✅ **Client Secret** : (si vous en avez un, ajoutez-le au `.env`)

### 3. Uploader votre clé publique

Votre clé publique doit être accessible à l'URL ci-dessus.

Si ce n'est pas déjà fait :

```bash
# Générer les clés
make generate-key

# La clé publique est dans:
cat config/public-key.pem

# Copier son contenu et le placer sur votre serveur à:
# https://app.jeromemarlier.com/.well-known/appspecific/com.tesla.3p.public-key.pem
```

## ⚙️ Configuration Locale (.env)

Votre fichier `.env` a été mis à jour avec :

```env
# Client ID de votre application Tesla
TESLA_CLIENT_ID=c9c40292-ddb3-4a87-9cc0-5a0193081024

# Client Secret (optionnel selon le flux)
TESLA_CLIENT_SECRET=your-client-secret-here

# Chemin vers la clé privée EC (secp256r1)
TESLA_PRIVATE_KEY_PATH=config/private-key.pem

# URL de l'API Tesla Fleet
TESLA_FLEET_API_URL=https://fleet-api.prd.na.vn.cloud.tesla.com

# URLs d'authentification
TESLA_AUTH_URL=https://auth.tesla.com/oauth2/v3/authorize
TESLA_TOKEN_URL=https://fleet-auth.prd.vn.cloud.tesla.com/oauth2/v3/token

# Redirect URI pour le callback OAuth
TESLA_REDIRECT_URI=https://app.jeromemarlier.com/callback.php

# Audience pour le token partenaire
TESLA_AUDIENCE=https://fleet-api.prd.na.vn.cloud.tesla.com

# Scopes pour Partner Token
TESLA_SCOPES=fleet_api:vehicles:read fleet_api:vehicles:write

# Scopes pour User OAuth
TESLA_USER_SCOPES=openid offline_access vehicle_device_data vehicle_cmds vehicle_charging_cmds
```

### ⚠️ À compléter si nécessaire

Si vous avez un **Client Secret**, ajoutez-le :

```bash
nano .env
# Remplacer: TESLA_CLIENT_SECRET=your-client-secret-here
# Par: TESLA_CLIENT_SECRET=votre-vrai-secret
```

## 🗂️ Structure des Nouveaux Fichiers

### Flux User OAuth (Authorization Code)

```
public/
├── login.php          # Point d'entrée OAuth
├── callback.php       # Callback après authentification
├── dashboard.php      # Tableau de bord utilisateur
└── logout.php         # Déconnexion

var/
└── tokens.json       # Stockage des tokens (non versionné)
```

### Documentation

```
OAUTH_FLOWS.md        # Guide complet des flux OAuth
OAUTH_SETUP.md        # Ce fichier (configuration)
```

## 🧪 Tests

### Test 1: Partner Token (JWT)

```bash
# CLI
php cli-get-token.php

# Web API
curl https://app.jeromemarlier.com/get-token.php
```

### Test 2: User OAuth (Navigateur)

1. **Lancer le serveur local** (pour développement) :

   ```bash
   make dev
   ```

2. **Ouvrir dans le navigateur** :

   ```
   http://localhost:8000
   ```

3. **Cliquer sur** "👤 Se connecter avec Tesla (OAuth)"

4. **S'authentifier** avec vos identifiants Tesla

5. **Voir le résultat** sur le dashboard

### Test 3: User OAuth (Production)

1. **Accéder à** : `https://app.jeromemarlier.com/login.php`

2. **S'authentifier** sur Tesla

3. **Redirection automatique** vers `callback.php`

4. **Dashboard** : Voir vos véhicules

## 📊 Flux d'Authentification

### Flux 1: Partner Token (Backend)

```
1. Application génère JWT signé avec clé privée
2. POST /oauth/token avec JWT
3. Reçoit access_token
4. Utilise le token pour les appels API
```

**Usage** : Scripts, CRON, backend automatisé

### Flux 2: User OAuth (Frontend)

```
1. Utilisateur clique "Se connecter"
2. Redirection vers auth.tesla.com
3. Utilisateur entre ses identifiants
4. Tesla redirige vers callback.php?code=xxx
5. Application échange code contre access_token + refresh_token
6. Session authentifiée
```

**Usage** : Interface web, dashboard utilisateur

## 🔐 Sécurité

### Checklist de sécurité

- [x] ✅ `.env` non versionné (dans `.gitignore`)
- [x] ✅ `var/` non versionné (tokens utilisateur)
- [x] ✅ Clé privée dans `config/` (hors du public)
- [x] ✅ State CSRF pour User OAuth
- [x] ✅ HTTPS pour les URLs de callback
- [ ] ⚠️ Vérifier que la clé publique est accessible : https://app.jeromemarlier.com/.well-known/appspecific/com.tesla.3p.public-key.pem

### Vérification de la clé publique

```bash
# Tester l'accès à la clé publique
curl -I https://app.jeromemarlier.com/.well-known/appspecific/com.tesla.3p.public-key.pem

# Doit retourner: HTTP/1.1 200 OK
```

Si la clé n'est pas accessible :

1. **Créer le dossier** sur votre serveur :

   ```bash
   mkdir -p /var/www/html/.well-known/appspecific
   ```

2. **Copier la clé publique** :

   ```bash
   cp config/public-key.pem /var/www/html/.well-known/appspecific/com.tesla.3p.public-key.pem
   chmod 644 /var/www/html/.well-known/appspecific/com.tesla.3p.public-key.pem
   ```

3. **Vérifier** :
   ```bash
   curl https://app.jeromemarlier.com/.well-known/appspecific/com.tesla.3p.public-key.pem
   ```

## 🌐 Configuration Serveur Web

### Apache (.htaccess dans public/)

```apache
# Autoriser l'accès au dossier .well-known
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Ne pas rediriger .well-known
    RewriteCond %{REQUEST_URI} !^/\.well-known/

    # Autres règles...
</IfModule>
```

### Nginx

```nginx
server {
    server_name app.jeromemarlier.com;
    root /var/www/tesla-app/public;

    # Autoriser .well-known
    location ^~ /.well-known/ {
        alias /var/www/tesla-app/public/.well-known/;
        allow all;
    }

    # Callback OAuth
    location = /callback.php {
        try_files $uri =404;
        fastcgi_pass php-fpm;
        include fastcgi_params;
    }
}
```

## 🔄 Workflow Complet

### Développement Local

```bash
# 1. Configuration
cp .env.example .env
nano .env

# 2. Générer les clés
make generate-key

# 3. Uploader la clé publique sur le serveur
scp config/public-key.pem user@app.jeromemarlier.com:/var/www/html/.well-known/appspecific/com.tesla.3p.public-key.pem

# 4. Tester Partner Token
make test

# 5. Tester User OAuth
make dev
# Ouvrir http://localhost:8000
```

### Production

```bash
# 1. Déployer le code
git push origin main

# 2. Vérifier la configuration
ssh user@app.jeromemarlier.com
cd /var/www/tesla-app
./verify-setup.sh

# 3. Tester Partner Token
curl https://app.jeromemarlier.com/get-token.php

# 4. Tester User OAuth
open https://app.jeromemarlier.com/login.php
```

## 📱 Interface Utilisateur

L'interface web propose maintenant deux options :

1. **🔑 Obtenir un Partner Token (JWT)**

   - Utilise la signature JWT
   - Retourne un access_token
   - Pour usage backend

2. **👤 Se connecter avec Tesla (OAuth)**
   - Redirige vers Tesla Auth
   - Session utilisateur
   - Dashboard avec véhicules

## 🐛 Dépannage

### Problème : "Redirect URI mismatch"

**Solution** : Vérifier que l'URL de callback est exactement celle configurée sur Tesla :

```
https://app.jeromemarlier.com/callback.php
```

### Problème : "Invalid client"

**Solution** : Vérifier le Client ID et Client Secret dans `.env`

### Problème : "Public key not found"

**Solution** : Vérifier que la clé publique est accessible :

```bash
curl https://app.jeromemarlier.com/.well-known/appspecific/com.tesla.3p.public-key.pem
```

### Problème : "Invalid state"

**Solution** : Vider les cookies/session et réessayer :

```php
// Dans logout.php, c'est déjà géré
session_destroy();
```

## 📚 Documentation

Pour en savoir plus :

- **[OAUTH_FLOWS.md](OAUTH_FLOWS.md)** - Guide complet des flux OAuth
- **[README.md](README.md)** - Documentation principale
- **[QUICKSTART.md](QUICKSTART.md)** - Démarrage rapide

## ✅ Checklist de déploiement

### Configuration Tesla Developer

- [ ] Application créée sur developer.tesla.com
- [ ] Public Key URL configurée : `https://app.jeromemarlier.com/.well-known/appspecific/com.tesla.3p.public-key.pem`
- [ ] Redirect URI configurée : `https://app.jeromemarlier.com/callback.php`
- [ ] Client ID récupéré et dans `.env`
- [ ] Client Secret récupéré (si nécessaire) et dans `.env`

### Configuration Serveur

- [ ] Clé publique uploadée et accessible
- [ ] HTTPS activé
- [ ] `.env` configuré avec toutes les variables
- [ ] Dossier `var/` créé et permissions OK
- [ ] Serveur web configuré (Apache/Nginx)

### Tests

- [ ] Partner Token fonctionne (CLI)
- [ ] Partner Token fonctionne (Web API)
- [ ] User OAuth fonctionne (Login)
- [ ] Callback OAuth fonctionne
- [ ] Dashboard affiche les véhicules
- [ ] Logout fonctionne

---

**🎉 Configuration OAuth complète !**

_Vous disposez maintenant de deux flux d'authentification pour tous vos besoins._
