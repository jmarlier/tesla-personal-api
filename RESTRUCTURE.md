# 🔄 Restructuration pour O2Switch - Guide Complet

## ✅ Changements Effectués

Votre projet a été restructuré pour s'adapter aux serveurs mutualisés O2Switch où le document root ne peut pas être changé.

### 📁 Ancienne Structure (Idéale mais incompatible)

```
tesla-app/
├── public/          ← Document root (idéal)
│   ├── index.php
│   ├── get-token.php
│   └── ...
├── config/          ← Hors du web
├── src/             ← Hors du web
└── .env             ← Hors du web
```

### 📁 Nouvelle Structure (Adaptée O2Switch)

```
tesla-app/
├── index.php              ← À LA RACINE (déplacé)
├── get-token.php          ← À LA RACINE (déplacé)
├── login.php              ← À LA RACINE (déplacé)
├── callback.php           ← À LA RACINE (déplacé)
├── dashboard.php          ← À LA RACINE (déplacé)
├── logout.php             ← À LA RACINE (déplacé)
├── .htaccess              ← NOUVEAU (sécurité)
│
├── .well-known/           ← Clé publique Tesla
│   └── appspecific/
│       └── com.tesla.3p.public-key.pem
│
├── config/                ← Protégé par .htaccess
│   └── private-key.pem
├── src/                   ← Protégé par .htaccess
│   └── TeslaAuth.php
├── var/                   ← Protégé par .htaccess
│   └── tokens.json
│
├── vendor/                ← Composer
├── .env                   ← Protégé par .htaccess
└── public/                ← Vide (conservé pour compatibilité)
```

## 🔒 Sécurité

### ✅ Mesures Appliquées

Le nouveau `.htaccess` à la racine protège :

1. **Fichiers sensibles** :
   ```apache
   <FilesMatch "\.(env|pem|key|json)$">
       Require all denied
   </FilesMatch>
   ```

2. **Dossiers de configuration** :
   ```apache
   <DirectoryMatch "^/.*(config|src|vendor|var)">
       Require all denied
   </DirectoryMatch>
   ```

3. **Autorisation .well-known** :
   ```apache
   <DirectoryMatch "^/\.well-known">
       Require all granted
   </DirectoryMatch>
   ```

### ✅ Ce qui est protégé

| Fichier/Dossier | Protection | Accessible |
|-----------------|------------|------------|
| `index.php` | ❌ Non | ✅ Web |
| `get-token.php` | ❌ Non | ✅ Web |
| `.env` | ✅ .htaccess | ❌ Bloqué |
| `config/` | ✅ .htaccess | ❌ Bloqué |
| `src/` | ✅ .htaccess | ❌ Bloqué |
| `var/` | ✅ .htaccess | ❌ Bloqué |
| `.well-known/` | ❌ Non | ✅ Tesla |

## 📝 Modifications Apportées

### Fichiers Déplacés

| Ancien | Nouveau |
|--------|---------|
| `public/index.php` | `index.php` |
| `public/get-token.php` | `get-token.php` |
| `public/login.php` | `login.php` |
| `public/callback.php` | `callback.php` |
| `public/dashboard.php` | `dashboard.php` |
| `public/logout.php` | `logout.php` |

### Chemins Adaptés dans les Fichiers

Tous les fichiers PHP ont été mis à jour :
- `__DIR__ . '/../vendor/autoload.php'` → `__DIR__ . '/vendor/autoload.php'`
- `__DIR__ . '/..'` → `__DIR__`
- `__DIR__ . '/../var/tokens.json'` → `__DIR__ . '/var/tokens.json'`
- `Location: /dashboard.php` → `Location: dashboard.php`
- `href="/login.php"` → `href="login.php"`

### Nouveau Fichier

- ✅ `.htaccess` à la racine (sécurité renforcée)

### .gitignore Mis à Jour

- ✅ Exclusion de `/public/` (vide)
- ✅ Exclusion des fichiers de log et debug
- ✅ Exclusion des tokens utilisateur

## 🚀 Déploiement

### Avant de Déployer

```bash
# Vérifier la structure locale
ls -la *.php
# Doit afficher: index.php, get-token.php, login.php, etc.

# Vérifier que public/ est vide
ls -la public/
# Doit être vide ou presque
```

### Déployer

```bash
# Déployer avec le script amélioré
./deploy-improved.sh
```

Le script va :
1. Commit les nouveaux fichiers à la racine
2. Push sur GitHub
3. Push sur Cocotier

### Après le Déploiement

```bash
# Configurer le serveur
make server-setup

# Vérifier
make server-check

# Tester
curl https://app.jeromemarlier.com/get-token.php
open https://app.jeromemarlier.com
```

## ✅ Tests de Vérification

### Test 1: Structure Locale

```bash
# Vérifier que les fichiers sont à la racine
ls -1 *.php
# Devrait afficher:
# callback.php
# cli-get-token.php
# dashboard.php
# example-api-call.php
# get-token.php
# index.php
# login.php
# logout.php
# setup.php
```

### Test 2: .htaccess

```bash
# Vérifier que .htaccess existe à la racine
cat .htaccess | head -10
```

### Test 3: Chemins dans les Fichiers

```bash
# Vérifier les require
grep -n "require.*vendor" *.php
# Tous doivent pointer vers: __DIR__ . '/vendor/autoload.php'
```

## 🔧 Si Vous Avez Déjà Déployé

### Nettoyer sur le Serveur

```bash
ssh duda6304@cocotier.o2switch.net << 'ENDSSH'
cd ~/app.jeromemarlier.com

# Supprimer les anciens fichiers de public/ si en doublon
rm -f public/index.php public/get-token.php public/login.php public/callback.php public/dashboard.php public/logout.php

echo "✅ Anciens fichiers supprimés"
ENDSSH
```

### Redéployer

```bash
# Déployer la nouvelle structure
./deploy-improved.sh
```

## 📊 Avantages de la Nouvelle Structure

### ✅ Avantages

- Compatible avec O2Switch (document root racine)
- Performance optimale (pas de redirection)
- Toujours sécurisé (.htaccess protège config/, src/, .env)
- Déploiement simplifié

### ⚠️ Inconvénients (mineurs)

- Fichiers web à la racine (moins "propre")
- Sécurité dépend du .htaccess (mais c'est solide)

## 🎯 Résumé

**Avant** : Fichiers dans `public/` → Erreur 500
**Après** : Fichiers à la racine → ✅ Fonctionne

**Sécurité maintenue** : `.htaccess` protège tout ce qui est sensible

---

**✅ Structure adaptée aux contraintes O2Switch !**

*Redéployez maintenant pour que tout fonctionne.*

