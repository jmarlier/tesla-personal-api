# 🚀 Configuration Post-Déploiement - Tesla Fleet API

## ✅ Vous avez déployé - Et maintenant ?

Guide étape par étape pour configurer votre serveur après le déploiement.

## 📋 Checklist de Configuration Serveur

### 1. Se Connecter au Serveur

```bash
ssh duda6304@cocotier.o2switch.net
```

### 2. Naviguer vers le Dossier Web

```bash
cd /var/www/html
# ou
cd ~/www
```

### 3. Créer le Fichier .env de Production

```bash
# Copier le template
cp .env.example .env

# Éditer avec les vraies valeurs
nano .env
```

**Remplir avec vos vraies valeurs** :
```env
# Configuration Tesla Fleet API

# Client ID de votre application Tesla
TESLA_CLIENT_ID=c9c40292-ddb3-4a87-9cc0-5a0193081024

# Client Secret (si vous en avez un)
TESLA_CLIENT_SECRET=votre-secret-ici

# Chemin vers la clé privée
TESLA_PRIVATE_KEY_PATH=config/private-key.pem

# URL de l'API Tesla Fleet
TESLA_FLEET_API_URL=https://fleet-api.prd.na.vn.cloud.tesla.com

# URLs d'authentification
TESLA_AUTH_URL=https://auth.tesla.com/oauth2/v3/authorize
TESLA_TOKEN_URL=https://fleet-auth.prd.vn.cloud.tesla.com/oauth2/v3/token

# Redirect URI (votre domaine)
TESLA_REDIRECT_URI=https://app.jeromemarlier.com/callback.php

# Audience
TESLA_AUDIENCE=https://fleet-api.prd.na.vn.cloud.tesla.com

# Scopes
TESLA_SCOPES="fleet_api:vehicles:read fleet_api:vehicles:write"
TESLA_USER_SCOPES="openid offline_access vehicle_device_data vehicle_cmds vehicle_charging_cmds"
```

### 4. Copier la Clé Privée sur le Serveur

**Depuis votre machine locale** :

```bash
# Copier la clé privée
scp config/private-key.pem duda6304@cocotier.o2switch.net:/var/www/html/config/

# OU si vous utilisez la clé publique déjà hébergée
# La clé publique est déjà à https://app.jeromemarlier.com/.well-known/appspecific/com.tesla.3p.public-key.pem
```

**Sur le serveur** :

```bash
# Sécuriser les permissions de la clé
chmod 600 config/private-key.pem

# Vérifier
ls -la config/private-key.pem
# Doit afficher: -rw------- (600)
```

### 5. Créer le Dossier var/ et Sécuriser

```bash
# Créer le dossier pour les tokens
mkdir -p var
chmod 755 var

# Créer un .htaccess pour bloquer l'accès
cat > var/.htaccess << 'EOF'
Require all denied
EOF
```

### 6. Installer les Dépendances Composer

```bash
# Installer Composer si nécessaire
# Vérifier si Composer est installé
composer --version

# Installer les dépendances
composer install --no-dev --optimize-autoloader
```

### 7. Vérifier la Configuration Apache/Nginx

#### Si Apache (.htaccess)

Vérifier que `public/.htaccess` existe :
```bash
ls -la public/.htaccess
```

#### Si Nginx

Ajouter dans la config Nginx (`/etc/nginx/sites-available/votre-site`) :

```nginx
server {
    listen 80;
    server_name app.jeromemarlier.com;
    
    # Redirection HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name app.jeromemarlier.com;
    
    root /var/www/html/public;
    index index.php;
    
    # SSL (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/app.jeromemarlier.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/app.jeromemarlier.com/privkey.pem;
    
    # Autoriser .well-known
    location ^~ /.well-known/ {
        alias /var/www/html/public/.well-known/;
        allow all;
    }
    
    # PHP
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
    location ~ /\.(env|git) {
        deny all;
    }
    
    location ^~ /config/ {
        deny all;
    }
}
```

### 8. Sécuriser les Permissions

```bash
# Permissions des fichiers
chmod 644 public/*.php
chmod 644 src/*.php

# Permissions .env
chmod 600 .env

# Permissions clé privée
chmod 600 config/private-key.pem

# Vérifier
ls -la .env config/private-key.pem
```

### 9. Vérifier la Clé Publique

La clé publique DOIT être accessible publiquement :

```bash
# Sur votre machine locale, tester
curl https://app.jeromemarlier.com/.well-known/appspecific/com.tesla.3p.public-key.pem
```

Si elle n'est pas accessible :
```bash
# Sur le serveur
mkdir -p public/.well-known/appspecific

# Copier la clé publique
cp config/public-key.pem public/.well-known/appspecific/com.tesla.3p.public-key.pem

# OU la créer depuis la clé privée
openssl ec -in config/private-key.pem -pubout -out public/.well-known/appspecific/com.tesla.3p.public-key.pem

# Permissions
chmod 644 public/.well-known/appspecific/com.tesla.3p.public-key.pem
```

### 10. Tester l'Application

#### Test 1: Page d'accueil
```bash
curl https://app.jeromemarlier.com/
```

#### Test 2: Partner Token (JWT)
```bash
curl https://app.jeromemarlier.com/get-token.php
```

Devrait retourner du JSON avec `access_token`.

#### Test 3: User OAuth
```bash
# Dans le navigateur
open https://app.jeromemarlier.com/login.php
```

Devrait rediriger vers Tesla pour l'authentification.

### 11. Vérifier les Logs

```bash
# Logs Apache
tail -f /var/log/apache2/error.log

# Logs PHP (si configuré)
tail -f /var/log/php_errors.log

# Logs Nginx
tail -f /var/log/nginx/error.log
```

### 12. Configuration SSL (HTTPS)

Si pas déjà fait, installer Let's Encrypt :

```bash
# Installer Certbot
sudo apt-get update
sudo apt-get install certbot python3-certbot-apache
# ou pour Nginx
sudo apt-get install certbot python3-certbot-nginx

# Obtenir un certificat
sudo certbot --apache -d app.jeromemarlier.com
# ou
sudo certbot --nginx -d app.jeromemarlier.com

# Renouvellement automatique
sudo certbot renew --dry-run
```

## ✅ Vérification Finale

### Checklist Complète

- [ ] `.env` créé avec les vraies valeurs
- [ ] Clé privée copiée dans `config/` avec permissions 600
- [ ] Clé publique accessible : `/.well-known/appspecific/com.tesla.3p.public-key.pem`
- [ ] Dossier `var/` créé et protégé
- [ ] Dépendances Composer installées
- [ ] Permissions correctes (644 PHP, 600 secrets)
- [ ] HTTPS activé
- [ ] Page d'accueil accessible
- [ ] Partner Token fonctionne
- [ ] User OAuth fonctionne

### Tests Fonctionnels

```bash
# Test 1: Accueil
curl -I https://app.jeromemarlier.com/
# Doit retourner: HTTP/2 200

# Test 2: Clé publique
curl https://app.jeromemarlier.com/.well-known/appspecific/com.tesla.3p.public-key.pem
# Doit afficher la clé publique

# Test 3: Partner Token
curl https://app.jeromemarlier.com/get-token.php | jq
# Doit retourner du JSON avec access_token

# Test 4: Interface web
open https://app.jeromemarlier.com
```

## 🐛 Dépannage

### Erreur: "File .env not found"

```bash
# Vérifier que .env existe
ls -la .env

# Si manquant, créer depuis template
cp .env.example .env
nano .env
```

### Erreur: "Private key not found"

```bash
# Vérifier la clé
ls -la config/private-key.pem

# Si manquante, copier depuis local
# (depuis votre machine)
scp config/private-key.pem duda6304@cocotier.o2switch.net:/var/www/html/config/
```

### Erreur: "Public key not accessible"

```bash
# Vérifier l'URL
curl -I https://app.jeromemarlier.com/.well-known/appspecific/com.tesla.3p.public-key.pem

# Si 404, créer le fichier
mkdir -p public/.well-known/appspecific
openssl ec -in config/private-key.pem -pubout -out public/.well-known/appspecific/com.tesla.3p.public-key.pem
chmod 644 public/.well-known/appspecific/com.tesla.3p.public-key.pem
```

### Erreur: "500 Internal Server Error"

```bash
# Vérifier les logs
tail -50 /var/log/apache2/error.log
# ou
tail -50 /var/log/nginx/error.log

# Vérifier les permissions
ls -la .env config/private-key.pem

# Vérifier PHP
php -v
php -m | grep -E 'curl|openssl|json'
```

### Erreur: Composer not found

```bash
# Installer Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Vérifier
composer --version
```

## 🔄 Mettre à Jour l'Application

Pour les prochains déploiements :

```bash
# Depuis votre machine locale
./deploy-improved.sh

# Sur le serveur (si post-receive hook configuré, c'est automatique)
# Sinon :
cd /var/www/html
git pull
composer install --no-dev --optimize-autoloader
```

## 📝 Configuration Tesla Developer Portal

N'oubliez pas de vérifier sur [developer.tesla.com](https://developer.tesla.com) :

- [ ] Public Key URL : `https://app.jeromemarlier.com/.well-known/appspecific/com.tesla.3p.public-key.pem`
- [ ] Redirect URI : `https://app.jeromemarlier.com/callback.php`
- [ ] Domaines autorisés : `app.jeromemarlier.com`

## 🎉 Félicitations !

Si tous les tests passent, votre application Tesla Fleet API est **opérationnelle** ! 🚗⚡

### URLs de Votre Application

- **Accueil** : https://app.jeromemarlier.com
- **Partner Token** : https://app.jeromemarlier.com/get-token.php
- **Login OAuth** : https://app.jeromemarlier.com/login.php
- **Dashboard** : https://app.jeromemarlier.com/dashboard.php

---

**🚀 Votre application est en ligne !**

*Testez-la et développez de nouvelles fonctionnalités !*

