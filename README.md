# 🚗 Tesla Fleet API - Application Complète

> Application PHP moderne pour tester et utiliser l'API Tesla Fleet avec un dashboard interactif complet

[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

---

## 🎯 Qu'est-ce que c'est ?

Une **application web complète** qui permet de :

✅ **S'authentifier** avec Tesla OAuth 2.0  
✅ **Lister vos véhicules** Tesla automatiquement  
✅ **Consulter les données en temps réel** (batterie, charge, climat, localisation, etc.)  
✅ **Envoyer des commandes** (climatisation, verrouillage, charge, etc.)  
✅ **Débugger facilement** avec des logs complets  
✅ **Utiliser une interface moderne** avec thème sombre professionnel

---

## 🚀 Démarrage rapide (5 minutes)

### 1️⃣ Installation

```bash
# Cloner le projet
git clone <url-du-repo>
cd tesla-app

# Installer les dépendances
composer install
```

### 2️⃣ Configuration

Créez un fichier `.env` avec vos identifiants Tesla :

```env
TESLA_CLIENT_ID=your_client_id
TESLA_FLEET_API_URL=https://fleet-api.prd.eu.vn.cloud.tesla.com
TESLA_REDIRECT_URI=https://your-domain.com/callback.php
TESLA_USER_SCOPES=openid offline_access vehicle_device_data vehicle_cmds vehicle_charging_cmds
```

### 3️⃣ Lancement

```bash
# Test de configuration
php test-dashboard.php

# Lancer le serveur
./run-dashboard.sh    # macOS/Linux
# OU
run-dashboard.bat     # Windows

# Ouvrir dans le navigateur
http://localhost:8000/setup-dashboard.php
```

**C'est tout ! 🎉**

---

## 📱 Dashboard Moderne

L'application inclut un **dashboard complet** avec 5 étapes interactives :

### Étape 1 : 🔐 Authentification

- Coller un token directement OU
- Utiliser le flow OAuth complet
- Validation automatique en temps réel

### Étape 2 : 🚗 Véhicules

- Liste de tous vos véhicules Tesla
- Statut en direct (online/asleep/offline)
- Sélection interactive

### Étape 3 : 📊 Données du véhicule

- 🔋 Niveau de batterie
- 🔌 État de charge
- ❄️ Climatisation
- 🚪 Verrouillage des portes
- 📍 Localisation GPS
- 🏁 Autonomie restante
- ✅ Rafraîchissement auto (60s)

### Étape 4 : 🎮 Commandes

- ❄️ Climatisation (start/stop)
- 🔒 Verrouillage/déverrouillage
- 🔌 Charge (start/stop)
- 💡 Flash des lumières
- 📢 Klaxon
- Et plus encore...

### Étape 5 : 📋 Logs & Debug

- Historique complet de toutes les actions
- Classification par type (success/error/info)
- Timestamps précis

---

## 📚 Documentation

### 🎯 Pour démarrer

| Document                                             | Description                      | Temps  |
| ---------------------------------------------------- | -------------------------------- | ------ |
| **[START_HERE.md](START_HERE.md)** ⭐                | **Commencez ici** - Guide rapide | 5 min  |
| [README_DASHBOARD.md](README_DASHBOARD.md)           | Vue d'ensemble du dashboard      | 5 min  |
| [QUICK_START_DASHBOARD.md](QUICK_START_DASHBOARD.md) | Guide de démarrage détaillé      | 15 min |

### 📖 Documentation complète

| Document                                                 | Contenu                    |
| -------------------------------------------------------- | -------------------------- |
| [DASHBOARD_INDEX.md](DASHBOARD_INDEX.md)                 | Index de navigation        |
| [SETUP_DASHBOARD.md](SETUP_DASHBOARD.md)                 | Documentation technique    |
| [DASHBOARD_SUMMARY.md](DASHBOARD_SUMMARY.md)             | Résumé de création         |
| [DASHBOARD_PREVIEW.md](DASHBOARD_PREVIEW.md)             | Aperçu visuel              |
| [API_EXAMPLES.md](API_EXAMPLES.md)                       | Exemples d'utilisation API |
| [PROJECT_FILES_INVENTORY.md](PROJECT_FILES_INVENTORY.md) | Inventaire des fichiers    |

---

## 📂 Structure du projet

```
tesla-app/
│
├── 📱 DASHBOARD PRINCIPAL
│   ├── setup-dashboard.php              ⭐ Dashboard complet
│   ├── START_HERE.md                    🎯 Commencez ici
│   ├── test-dashboard.php               🧪 Test de config
│   └── run-dashboard.sh/bat             🚀 Scripts de lancement
│
├── 🔌 API ENDPOINTS
│   ├── api/auth.php                     Authentification
│   ├── api/vehicles.php                 Liste véhicules
│   ├── api/data.php                     Données véhicule
│   ├── api/command.php                  Commandes
│   ├── api/check-session.php            Vérif. session
│   └── api/reset.php                    Reset session
│
├── 🔑 AUTHENTIFICATION OAUTH
│   ├── login.php                        Page de login
│   ├── callback.php                     Callback OAuth
│   └── logout.php                       Déconnexion
│
├── 💻 CODE SOURCE
│   └── src/TeslaAuth.php               Classe d'authentification
│
├── ⚙️ CONFIGURATION
│   ├── .env                            Variables d'environnement
│   ├── composer.json                   Dépendances
│   └── .htaccess                       Config Apache
│
└── 📚 DOCUMENTATION
    ├── README.md                       Ce fichier
    ├── START_HERE.md                   Point de départ
    └── (voir section Documentation)
```

---

## ✨ Fonctionnalités principales

### 🎨 Interface moderne

- Thème sombre professionnel
- Design responsive (desktop, tablet, mobile)
- Timeline des étapes avec indicateurs ✅/❌/⚠️
- Notifications en temps réel
- Barre de progression globale

### 🔐 Sécurité

- Authentification OAuth 2.0
- Sessions PHP sécurisées
- Validation des tokens
- Protection CSRF
- HTTPS recommandé en production

### 🧪 Tests & Debug

- Script de test de configuration
- Logs complets de toutes les requêtes
- Gestion d'erreurs détaillée
- Interface de debug intégrée

### 🔌 API complète

- 6 endpoints RESTful
- Authentification
- Gestion des véhicules
- Récupération de données
- Envoi de commandes
- Gestion de session

---

## 🔧 Configuration requise

### Serveur

- **PHP 8.0+** avec extensions :
  - curl
  - json
  - session
  - mbstring

### Composer

- `vlucas/phpdotenv` - Variables d'environnement
- `firebase/php-jwt` - JWT (optionnel)

### Tesla Developer

- Compte développeur Tesla
- Client ID configuré
- Redirect URI enregistré
- Scopes appropriés

---

## 🎮 Utilisation

### Via le Dashboard Web (Recommandé)

1. **Test de configuration** :

   ```bash
   php test-dashboard.php
   ```

2. **Lancement** :

   ```bash
   ./run-dashboard.sh
   ```

3. **Accès** :

   ```
   http://localhost:8000/setup-dashboard.php
   ```

4. **Suivre les 5 étapes** dans le dashboard

### Via CLI

```bash
# Obtenir un token en ligne de commande
php cli-get-token.php

# Test simple
php test-simple.php
```

### Via API directement

```bash
# Authentification
curl -X POST http://localhost:8000/api/auth.php \
  -H "Content-Type: application/json" \
  -d '{"access_token":"YOUR_TOKEN"}'

# Liste des véhicules
curl http://localhost:8000/api/vehicles.php

# Données d'un véhicule
curl http://localhost:8000/api/data.php?vehicle_id=123456

# Envoyer une commande
curl -X POST http://localhost:8000/api/command.php \
  -H "Content-Type: application/json" \
  -d '{"vehicle_id":123456,"command":"flash_lights"}'
```

Plus d'exemples dans [API_EXAMPLES.md](API_EXAMPLES.md)

---

## 🐛 Dépannage

### Token invalide

**Solution :** Utilisez le bouton "Se connecter avec Tesla OAuth" dans le dashboard

### Aucun véhicule trouvé

**Solution :** Vérifiez que vos scopes incluent `vehicle_device_data`

### Véhicule en veille (408)

**Solution :** Attendez 30-60 secondes et réessayez

### Erreur de connexion

**Solution :** Vérifiez `TESLA_FLEET_API_URL` dans `.env`

👉 **Plus de solutions dans [QUICK_START_DASHBOARD.md](QUICK_START_DASHBOARD.md)**

---

## 📊 Statistiques

```
📄 Fichiers créés       : 60+
💻 Code PHP             : ~3000 lignes
📚 Documentation        : ~5000 lignes
🔌 Endpoints API        : 6
🎯 Étapes dashboard     : 5
⚡ Commandes testables  : 8+
```

---

## 🔐 Sécurité

### En développement

- ✅ HTTP localhost acceptable
- ✅ Sessions PHP

### En production

- ⚠️ **HTTPS obligatoire**
- ⚠️ `.env` hors du webroot
- ⚠️ Sessions sécurisées :
  ```php
  session.cookie_secure = 1
  session.cookie_httponly = 1
  session.cookie_samesite = "Strict"
  ```

### Fichiers à ne JAMAIS versionner

```gitignore
.env
*.pem
*.key
/config/private-key.pem
/var/tokens.json
```

---

## 🌐 API Tesla Fleet

### Documentation officielle

- [Tesla Fleet API](https://developer.tesla.com/docs/fleet-api)
- [OAuth 2.0 Flow](https://developer.tesla.com/docs/fleet-api/authentication/oauth)
- [GitHub Vehicle Command](https://github.com/teslamotors/vehicle-command)

### Endpoints disponibles

```bash
# Liste véhicules
GET /api/1/vehicles

# Données véhicule
GET /api/1/vehicles/{id}/vehicle_data

# Commandes
POST /api/1/vehicles/{id}/command/{command_name}
```

---

## 📝 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

## 🤝 Contribution

Les contributions sont les bienvenues !

1. Fork le projet
2. Créez une branche (`git checkout -b feature/amélioration`)
3. Commit vos changements (`git commit -m 'Ajout fonctionnalité'`)
4. Push sur la branche (`git push origin feature/amélioration`)
5. Ouvrez une Pull Request

---

## 🎉 Démarrage rapide

**Pour commencer immédiatement :**

```bash
# 1. Installer
composer install

# 2. Configurer .env
cp .env.example .env
nano .env

# 3. Tester
php test-dashboard.php

# 4. Lancer
./run-dashboard.sh

# 5. Ouvrir
open http://localhost:8000/setup-dashboard.php
```

**Ensuite, lisez [START_HERE.md](START_HERE.md) ! 🚀**

---

## 📞 Support

- 📖 **Documentation** : Voir section Documentation ci-dessus
- 🐛 **Issues** : Ouvrez une issue sur GitHub
- 💬 **Questions** : Consultez [QUICK_START_DASHBOARD.md](QUICK_START_DASHBOARD.md)
- 🌐 **Tesla Support** : [developer.tesla.com](https://developer.tesla.com)

---

**⚡ Propulsé par Tesla Fleet API**

_Créé avec ❤️ pour faciliter l'intégration de l'API Tesla_
