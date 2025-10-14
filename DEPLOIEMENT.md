# 🚀 Guide de déploiement - Tesla Fleet API

## 📋 RÉCAPITULATIF DE L'AVANCEMENT

### ✅ CE QUI EST TERMINÉ

#### **ÉTAPE 1 : Fleet Auth Token**

- ✅ Script CLI créé : `cli/01-get-fleet-token.php`
- ✅ Testé avec succès
- ✅ Token obtenu et stocké dans `/var/fleet-auth-token.json`

#### **ÉTAPE 2 : Partner Account Validation**

- ✅ Script CLI créé : `cli/02-register-partner.php`
- ✅ Testé avec succès
- ✅ Configuration validée et stockée dans `/var/partner-account.json`
- ✅ Clé privée EC vérifiée : `config/private-key.pem`

#### **ÉTAPE 3 : OAuth2 User Flow**

- ✅ Interface web complète créée dans `/public/`
- ✅ Tous les fichiers sont prêts à être testés
- ✅ Affichage complet des réponses API pour debug

### 🔧 CONFIGURATION TESLA VALIDÉE

```
✅ Client ID         : c9c40292-ddb3-4a87-9cc0-5a0193081024
✅ Client Secret     : Configuré dans .env
✅ Redirect URI      : https://app.jeromemarlier.com/callback.php
✅ Clé privée EC     : config/private-key.pem (227 octets, format PEM)
✅ Clé publique      : Déployée sur serveur
```

**Emplacement de la clé publique sur le serveur :**

```
https://app.jeromemarlier.com/.well-known/appspecific/com.tesla.3p.public-key.pem
```

---

## 📦 FICHIERS À DÉPLOYER SUR LE SERVEUR

### Structure à déployer sur `app.jeromemarlier.com/` :

```
app.jeromemarlier.com/
├── .well-known/
│   └── appspecific/
│       └── com.tesla.3p.public-key.pem  ✅ DÉJÀ FAIT
│
├── public/                               ⬅️ À DÉPLOYER
│   ├── index.php
│   ├── login.php
│   ├── callback.php
│   ├── dashboard.php
│   └── logout.php
│
├── vendor/                               ⬅️ À DÉPLOYER
│   └── (dépendances Composer)
│
├── config/                               ⬅️ À DÉPLOYER
│   └── private-key.pem
│
├── var/                                  ⬅️ Créé automatiquement
│   ├── fleet-auth-token.json
│   ├── partner-account.json
│   └── user-tokens/
│
└── .env                                  ⬅️ À DÉPLOYER
```

---

## 🚀 ÉTAPES DE DÉPLOIEMENT

### 1. Préparer les fichiers localement

```bash
# Vérifier que tout est à jour
composer install --no-dev --optimize-autoloader

# Vérifier les permissions
chmod 755 public/
chmod 644 public/*.php
chmod 600 config/private-key.pem
chmod 600 .env
```

### 2. Déployer sur le serveur

**Option A : Via Git (recommandé)**

```bash
# Sur votre serveur
cd /path/to/app.jeromemarlier.com
git pull origin master
composer install --no-dev --optimize-autoloader
```

**Option B : Via SFTP/SCP**

```bash
# Depuis votre machine locale
scp -r public/ user@server:/path/to/app.jeromemarlier.com/
scp -r vendor/ user@server:/path/to/app.jeromemarlier.com/
scp -r config/ user@server:/path/to/app.jeromemarlier.com/
scp .env user@server:/path/to/app.jeromemarlier.com/
```

### 3. Créer le dossier var sur le serveur

```bash
# Sur le serveur
cd /path/to/app.jeromemarlier.com
mkdir -p var/user-tokens
chmod 755 var
chmod 755 var/user-tokens
```

### 4. Vérifier la configuration Apache/Nginx

**Pour Apache (.htaccess dans /public/) :**

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
</IfModule>

# Sécurité : empêcher l'accès direct aux fichiers sensibles
<FilesMatch "\.(env|json|pem|key)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

**Pour Nginx :**

```nginx
location ~ \.(env|json|pem|key)$ {
    deny all;
    return 404;
}
```

---

## 🧪 TESTS À EFFECTUER

### TEST 1 : Vérifier la clé publique

Ouvrir dans le navigateur :

```
https://app.jeromemarlier.com/.well-known/appspecific/com.tesla.3p.public-key.pem
```

**Résultat attendu :**

```
-----BEGIN PUBLIC KEY-----
MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE...
-----END PUBLIC KEY-----
```

✅ Vous avez déjà fait cette étape !

---

### TEST 2 : Page d'accueil

Ouvrir dans le navigateur :

```
https://app.jeromemarlier.com/index.php
```

**Résultat attendu :**

- Page d'accueil avec le bouton "Se connecter avec Tesla"
- Design moderne avec gradient violet/bleu
- Informations sur les fonctionnalités

---

### TEST 3 : Flux OAuth2 complet (LE TEST PRINCIPAL)

1. **Étape 1 : Cliquer sur "Se connecter avec Tesla"**

   - Vous devriez être redirigé vers `auth.tesla.com`

2. **Étape 2 : Connexion Tesla**

   - Entrez vos identifiants Tesla
   - Autorisez l'application

3. **Étape 3 : Callback**

   - Tesla vous redirige vers `callback.php`
   - Vous devriez voir :
     ```
     ✅ État validé avec succès
     ✅ Code d'autorisation reçu
     🔄 Échange du code contre un access token
     📋 Réponse de l'API Tesla (HTTP 200)
     ```

4. **Résultat attendu dans callback.php :**

   ```json
   {
     "access_token": "eyJhbGci...",
     "refresh_token": "eyJhbGci...",
     "expires_in": 28800,
     "token_type": "Bearer"
   }
   ```

5. **Étape 4 : Redirection vers le dashboard**
   - Cliquez sur "Tableau de bord"
   - Vous devriez voir vos informations de connexion
   - Token affiché avec la date d'expiration

---

### TEST 4 : Mode Debug (optionnel)

Pour voir l'URL OAuth2 avant la redirection :

```
https://app.jeromemarlier.com/login.php?debug=1
```

**Vous verrez :**

- Tous les paramètres OAuth2
- L'URL complète vers Tesla
- Possibilité de continuer ou revenir

---

## 🐛 DEBUGGING

### En cas d'erreur lors du callback

**Erreur : "État OAuth invalide (CSRF)"**

- Cause : Le state ne correspond pas
- Solution : Vider les cookies et réessayer

**Erreur : "Code HTTP 400"**

- Cause : Paramètres OAuth2 incorrects
- Solution : Vérifier le `.env` (client_id, client_secret, redirect_uri)

**Erreur : "Code HTTP 401"**

- Cause : Client ID ou Secret invalide
- Solution : Vérifier dans le Tesla Developer Portal

**Erreur : "Code HTTP 404"**

- Cause : URL de callback incorrecte
- Solution : Vérifier que le redirect_uri dans `.env` correspond exactement à celui configuré sur Tesla

---

## 📊 LOGS & MONITORING

### Vérifier les logs PHP (sur le serveur)

```bash
# Logs Apache
tail -f /var/log/apache2/error.log

# Logs Nginx
tail -f /var/log/nginx/error.log

# Logs PHP
tail -f /var/log/php8.x-fpm.log
```

### Vérifier les fichiers JSON générés

```bash
# Sur le serveur
cat var/fleet-auth-token.json
cat var/partner-account.json
ls -la var/user-tokens/
```

---

## 📝 CHECKLIST DE DÉPLOIEMENT

- [ ] Fichiers déployés dans `/public/`
- [ ] Dépendances Composer installées (`vendor/`)
- [ ] Fichier `.env` configuré sur le serveur
- [ ] Clé privée présente dans `config/private-key.pem`
- [ ] Clé publique accessible via `.well-known/appspecific/com.tesla.3p.public-key.pem` ✅
- [ ] Dossier `var/` créé avec permissions 755
- [ ] Test de la page d'accueil
- [ ] Test du flux OAuth2 complet
- [ ] Vérification des tokens générés

---

## 🎯 PROCHAINE ÉTAPE (Après tests réussis)

Une fois que l'authentification OAuth2 fonctionne, nous passerons à :

### **ÉTAPE 4 : Fleet API Calls**

Fichiers à créer :

- `api/vehicles.php` - Lister les véhicules de l'utilisateur
- `api/vehicle-data.php` - Obtenir les données d'un véhicule
- `api/send-command.php` - Envoyer des commandes (wake_up, honk, flash, etc.)
- Mise à jour de `dashboard.php` pour afficher les véhicules

---

## ❓ EN CAS DE PROBLÈME

1. **Vérifier les logs** (voir section Debugging ci-dessus)
2. **Tester en mode debug** : `login.php?debug=1`
3. **Vérifier la configuration** : `php cli/02-register-partner.php`
4. **Regénérer le Fleet Token** si expiré : `php cli/01-get-fleet-token.php`

---

**Prêt pour le déploiement ! 🚀**

Une fois déployé, testez l'authentification et tenez-moi informé du résultat !
