# 📑 Index des Fichiers - Tesla Fleet API

Guide de référence rapide pour tous les fichiers du projet.

## 📂 Structure Complète

### 🌐 Public (exposé au web)

| Fichier                | Type   | Description                     | Usage                     |
| ---------------------- | ------ | ------------------------------- | ------------------------- |
| `public/index.php`     | Web    | Interface web principale        | Ouvrir dans le navigateur |
| `public/get-token.php` | API    | Endpoint pour obtenir un token  | Appelé via AJAX/fetch     |
| `public/.htaccess`     | Config | Configuration Apache + sécurité | Auto (serveur Apache)     |

### 🔐 Configuration (sécurisé, hors public)

| Fichier                  | Type | Description               | Permissions                      |
| ------------------------ | ---- | ------------------------- | -------------------------------- |
| `config/private-key.pem` | Clé  | Clé privée EC (secp256r1) | 600 (lecture seule propriétaire) |
| `config/public-key.pem`  | Clé  | Clé publique EC           | 644 (à uploader sur Tesla)       |

### 💻 Code Source

| Fichier             | Type   | Description                | Namespace            |
| ------------------- | ------ | -------------------------- | -------------------- |
| `src/TeslaAuth.php` | Classe | Authentification OAuth 2.0 | `TeslaApp\TeslaAuth` |

### 🛠️ Scripts CLI

| Fichier                | Type | Description             | Usage                      |
| ---------------------- | ---- | ----------------------- | -------------------------- |
| `cli-get-token.php`    | CLI  | Obtenir un access token | `php cli-get-token.php`    |
| `example-api-call.php` | CLI  | Exemples d'appels API   | `php example-api-call.php` |
| `setup.php`            | CLI  | Installation guidée     | `php setup.php`            |
| `verify-setup.sh`      | Bash | Vérification du setup   | `./verify-setup.sh`        |

### ⚙️ Configuration

| Fichier         | Type     | Description                       | Versionné       |
| --------------- | -------- | --------------------------------- | --------------- |
| `.env`          | Env      | Variables d'environnement         | ❌ NON (secret) |
| `.env.example`  | Env      | Template de configuration         | ✅ OUI          |
| `.gitignore`    | Git      | Protection des fichiers sensibles | ✅ OUI          |
| `composer.json` | Composer | Dépendances PHP                   | ✅ OUI          |
| `composer.lock` | Composer | Versions verrouillées             | ✅ OUI          |
| `Makefile`      | Make     | Commandes utiles                  | ✅ OUI          |

### 📖 Documentation

| Fichier                | Type | Description              | Contenu                                   |
| ---------------------- | ---- | ------------------------ | ----------------------------------------- |
| `README.md`            | Doc  | Documentation principale | Guide complet, API, config                |
| `QUICKSTART.md`        | Doc  | Démarrage rapide (5 min) | Installation express                      |
| `SECURITY.md`          | Doc  | Guide de sécurité        | Bonnes pratiques, audit                   |
| `MIGRATION.md`         | Doc  | Migration legacy         | Passer de jwt.php à la nouvelle structure |
| `PROJECT_STRUCTURE.md` | Doc  | Architecture             | Structure, flux, dépendances              |
| `INDEX.md`             | Doc  | Index des fichiers       | Ce fichier                                |
| `LICENSE`              | Doc  | Licence du projet        | Termes et conditions                      |

### 🗂️ Générés (ne pas modifier)

| Fichier/Dossier       | Type     | Description            |
| --------------------- | -------- | ---------------------- |
| `vendor/`             | Composer | Dépendances installées |
| `vendor/autoload.php` | Composer | Autoloader PSR-4       |

## 📋 Par Catégorie

### ✅ Fichiers ESSENTIELS (à créer/configurer)

1. ✅ `.env` - Créer depuis `.env.example`
2. ✅ `config/private-key.pem` - Générer avec `make generate-key`
3. ✅ `config/public-key.pem` - Uploader sur Tesla Developer

### ⚠️ Fichiers LEGACY (à migrer/supprimer)

| Fichier                           | Action            | Commande                             |
| --------------------------------- | ----------------- | ------------------------------------ |
| `jwt.php`                         | Supprimer         | `rm jwt.php`                         |
| `private-key.pem` (racine)        | Déplacer          | `mv private-key.pem config/`         |
| `public-key.pem` (racine)         | Déplacer          | `mv public-key.pem config/`          |
| `callback_old.php`                | Évaluer/Supprimer | `rm callback_old.php`                |
| `com.tesla.3p.public-key.pem.old` | Supprimer         | `rm com.tesla.3p.public-key.pem.old` |

### 🚫 Fichiers à NE JAMAIS VERSIONNER

| Pattern         | Raison                |
| --------------- | --------------------- |
| `.env`          | Contient des secrets  |
| `*.pem`         | Clés cryptographiques |
| `*.key`         | Clés privées          |
| `/vendor/`      | Généré par Composer   |
| `/config/*.pem` | Clés cryptographiques |

## 🔍 Recherche Rapide

### Par Fonction

#### Authentification

- `src/TeslaAuth.php` - Classe principale
- `cli-get-token.php` - Test CLI
- `public/get-token.php` - Endpoint web

#### Configuration

- `.env` - Variables d'environnement
- `config/private-key.pem` - Clé privée
- `composer.json` - Dépendances

#### Documentation

- `README.md` - Pour tout savoir
- `QUICKSTART.md` - Pour démarrer vite
- `SECURITY.md` - Pour sécuriser

#### Scripts Utiles

- `Makefile` - Commandes make
- `setup.php` - Installation
- `verify-setup.sh` - Vérification

### Par Extension

| Extension | Fichiers      | Usage                      |
| --------- | ------------- | -------------------------- |
| `.php`    | Code PHP      | Exécuter avec `php`        |
| `.sh`     | Scripts Bash  | Exécuter avec `./`         |
| `.md`     | Documentation | Lire avec `cat` ou éditeur |
| `.pem`    | Clés crypto   | À protéger (600)           |
| `.env`    | Variables env | À configurer               |
| `.json`   | Config JSON   | Composer, etc.             |

## 🎯 Flux de Travail Typique

### 1. Installation (une fois)

```bash
# Lire
cat QUICKSTART.md

# Installer
make install
make setup

# Configurer
nano .env
make generate-key
```

### 2. Développement (quotidien)

```bash
# Tester
make test

# Développer
make dev

# Vérifier
make audit
```

### 3. Déploiement (production)

```bash
# Lire
cat SECURITY.md

# Vérifier
./verify-setup.sh
make check-config

# Déployer
# (configurer serveur web)
```

## 📊 Tailles de Fichiers

| Catégorie                     | Taille approx. |
| ----------------------------- | -------------- |
| Documentation (\*.md)         | ~50 KB         |
| Code source (src/)            | ~5 KB          |
| Scripts (\*.php CLI)          | ~10 KB         |
| Interface web (public/)       | ~10 KB         |
| Configuration (\*.json, .env) | ~2 KB          |
| Dépendances (vendor/)         | ~5 MB          |
| **Total**                     | **~5 MB**      |

## 🔗 Dépendances Entre Fichiers

```
.env
  └── Chargé par →
      ├── cli-get-token.php
      ├── example-api-call.php
      ├── public/index.php
      └── public/get-token.php

src/TeslaAuth.php
  └── Utilisé par →
      ├── cli-get-token.php
      ├── example-api-call.php
      └── public/get-token.php

config/private-key.pem
  └── Chargé par →
      └── src/TeslaAuth.php

vendor/autoload.php
  └── Requis par →
      └── TOUS les fichiers PHP
```

## 🛡️ Fichiers de Sécurité

### Protection Git

- `.gitignore` - Bloque les fichiers sensibles

### Protection Web

- `public/.htaccess` - Règles Apache
- Configuration Nginx - Dans README.md

### Permissions Recommandées

| Fichier/Dossier          | Permissions | Commande                           |
| ------------------------ | ----------- | ---------------------------------- |
| `config/`                | 755         | `chmod 755 config/`                |
| `config/private-key.pem` | 600         | `chmod 600 config/private-key.pem` |
| `.env`                   | 600         | `chmod 600 .env`                   |
| `*.php`                  | 644         | `chmod 644 *.php`                  |
| `*.sh`                   | 755         | `chmod +x *.sh`                    |

## 📝 Ordre de Lecture Recommandé

### Pour Débutants

1. `QUICKSTART.md` - Démarrage rapide
2. `README.md` - Documentation complète
3. `SECURITY.md` - Sécurité de base

### Pour Développeurs

1. `PROJECT_STRUCTURE.md` - Architecture
2. `src/TeslaAuth.php` - Code source
3. `example-api-call.php` - Exemples

### Pour Migration

1. `MIGRATION.md` - Guide de migration
2. `verify-setup.sh` - Vérification
3. `SECURITY.md` - Audit de sécurité

## 🔎 Commandes de Recherche

### Trouver un fichier

```bash
find . -name "*.php" -type f
find . -name "*config*" -type f
```

### Chercher dans le code

```bash
grep -r "TeslaAuth" --include="*.php"
grep -r "TESLA_CLIENT_ID" .
```

### Lister les fichiers par taille

```bash
du -sh * | sort -h
```

### Fichiers modifiés récemment

```bash
ls -lht | head -20
```

## 🎨 Légende

| Symbole | Signification         |
| ------- | --------------------- |
| ✅      | Essentiel / À faire   |
| ⚠️      | Attention / Migration |
| ❌      | À éviter / Supprimer  |
| 🔒      | Sécurisé / Privé      |
| 🌐      | Public / Exposé       |
| 📖      | Documentation         |
| 🛠️      | Utilitaire            |

---

**📑 Index complet pour navigation rapide dans le projet.**

_Dernière mise à jour: Voir `git log`_
