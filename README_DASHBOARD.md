# 🚗 Tesla API Setup Dashboard

> **Dashboard complet et moderne pour vérifier et tester la connexion à l'API Tesla Fleet**

![Version](https://img.shields.io/badge/version-1.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

---

## 🎯 Qu'est-ce que c'est ?

Un **dashboard web unique** qui vous permet de :

✅ **Vérifier votre authentification** Tesla OAuth 2.0  
✅ **Lister vos véhicules** Tesla automatiquement  
✅ **Consulter les données en temps réel** (batterie, charge, climat, etc.)  
✅ **Tester les commandes** de l'API (climatisation, verrouillage, charge, etc.)  
✅ **Debugger avec des logs** complets de toutes les requêtes

Le tout dans une **interface moderne avec thème sombre** et des **indicateurs visuels** clairs !

---

## 🚀 Démarrage rapide (3 étapes)

### 1️⃣ Installer les dépendances

```bash
composer install
```

### 2️⃣ Configurer .env

```env
TESLA_CLIENT_ID=your_client_id
TESLA_FLEET_API_URL=https://fleet-api.prd.eu.vn.cloud.tesla.com
TESLA_REDIRECT_URI=https://your-domain.com/callback.php
TESLA_USER_SCOPES=openid offline_access vehicle_device_data vehicle_cmds vehicle_charging_cmds
```

### 3️⃣ Lancer le dashboard

```bash
# Test de configuration
php test-dashboard.php

# Démarrer le serveur
php -S localhost:8000

# Ouvrir dans le navigateur
open http://localhost:8000/setup-dashboard.php
```

---

## 📱 Aperçu de l'interface

```
┌─────────────────────────────────────────────────────────────┐
│  🚗 Tesla API Setup Dashboard                               │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                             │
│  Sidebar                      Main Panel                    │
│  ┌─────────────┐             ┌──────────────────────────┐  │
│  │ ⚠️ Auth     │             │  🔐 Authentification     │  │
│  │ ✅ Vehicles │             │  [Token input]           │  │
│  │ ✅ Data     │             │  [✅ Valider]            │  │
│  │ ⚠️ Commands │             └──────────────────────────┘  │
│  │ 📋 Logs     │                                            │
│  └─────────────┘             Progress: ████████░░ 80%      │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## ✨ Fonctionnalités principales

### 🔐 Étape 1 : Authentification

- Coller un token directement OU
- Utiliser le flow OAuth complet
- Validation automatique via l'API Tesla
- Indicateur ✅/❌ en temps réel

### 🚗 Étape 2 : Véhicules

- Liste tous vos véhicules Tesla
- Affiche le statut (online/asleep/offline)
- Informations VIN, ID, nom du véhicule
- Sélection interactive

### 📊 Étape 3 : Données du véhicule

- 🔋 Niveau de batterie
- 🔌 État de charge (charging/complete)
- ❄️ Climatisation (active/inactive)
- 🚪 Verrouillage des portes
- 📍 Localisation GPS
- 🏁 Autonomie restante
- ✅ Rafraîchissement auto (60s)

### 🎮 Étape 4 : Commandes

- ❄️ Démarrer/arrêter la climatisation
- 🔒 Verrouiller/déverrouiller les portes
- 🔌 Démarrer/arrêter la charge
- 💡 Flash des lumières
- 📢 Klaxon
- Résultat instantané

### 📋 Étape 5 : Logs & Debug

- Historique complet de toutes les actions
- Timestamp pour chaque événement
- Classification par type (success/error/info)
- Effacement des logs

---

## 📂 Structure des fichiers

```
tesla-app/
├── setup-dashboard.php          ⭐ DASHBOARD PRINCIPAL
├── test-dashboard.php           🧪 Test de configuration
│
├── api/                         🔌 Endpoints API
│   ├── auth.php                # Authentification
│   ├── check-session.php       # Vérif. session
│   ├── vehicles.php            # Liste véhicules
│   ├── data.php                # Données véhicule
│   ├── command.php             # Commandes
│   └── reset.php               # Reset session
│
└── docs/                        📚 Documentation
    ├── README_DASHBOARD.md      # Ce fichier
    ├── QUICK_START_DASHBOARD.md # Guide de démarrage
    ├── SETUP_DASHBOARD.md       # Doc complète
    ├── DASHBOARD_SUMMARY.md     # Résumé de création
    ├── DASHBOARD_PREVIEW.md     # Aperçu visuel
    └── FILES_CREATED.md         # Liste des fichiers
```

---

## 🎨 Design

### Thème sombre professionnel

- Background : `#0a0a0a` (Noir profond)
- Cartes : `#1a1a1a` (Gris très sombre)
- Accent : `#e63946` (Rouge Tesla)
- Interface moderne et minimaliste

### Responsive

- ✅ Desktop (1920px+)
- ✅ Laptop (1366px+)
- ✅ Tablet (768px+)
- ✅ Mobile (375px+)

---

## 🔧 Configuration requise

### Serveur

- **PHP 8.0+** avec extensions :
  - curl
  - json
  - session
  - mbstring

### Composer

```json
{
  "require": {
    "vlucas/phpdotenv": "^5.5",
    "firebase/php-jwt": "^6.4"
  }
}
```

### Tesla Developer

- Client ID configuré
- Redirect URI enregistré
- Scopes appropriés

---

## 📚 Documentation complète

| Document                                                 | Description                            |
| -------------------------------------------------------- | -------------------------------------- |
| **[QUICK_START_DASHBOARD.md](QUICK_START_DASHBOARD.md)** | Guide de démarrage rapide (recommandé) |
| **[SETUP_DASHBOARD.md](SETUP_DASHBOARD.md)**             | Documentation complète du dashboard    |
| **[DASHBOARD_SUMMARY.md](DASHBOARD_SUMMARY.md)**         | Résumé technique complet               |
| **[DASHBOARD_PREVIEW.md](DASHBOARD_PREVIEW.md)**         | Aperçu visuel ASCII de l'interface     |
| **[FILES_CREATED.md](FILES_CREATED.md)**                 | Liste de tous les fichiers créés       |

---

## 🧪 Test de configuration

Avant de lancer le dashboard, testez votre configuration :

```bash
php test-dashboard.php
```

**Résultat attendu :**

```
🧪 Test de configuration du Tesla Setup Dashboard
============================================================

1️⃣  Vérification de Composer...
   ✅ Vendor autoload trouvé

2️⃣  Vérification du fichier .env...
   ✅ Fichier .env trouvé

[...]

✅ Configuration complète !

🚀 Prochaines étapes :
   1. Démarrez votre serveur web
   2. Accédez à setup-dashboard.php dans votre navigateur
   3. Suivez les étapes d'authentification
```

---

## 🎯 Utilisation

### Via navigateur web

1. **Démarrer le serveur** :

   ```bash
   php -S localhost:8000
   ```

2. **Ouvrir le dashboard** :

   ```
   http://localhost:8000/setup-dashboard.php
   ```

3. **Suivre les étapes** :
   - Étape 1 : S'authentifier
   - Étape 2 : Charger les véhicules
   - Étape 3 : Voir les données
   - Étape 4 : Tester les commandes
   - Étape 5 : Consulter les logs

### Via API directement

Les endpoints sont aussi utilisables directement :

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

---

## 🐛 Dépannage

### ❌ "Token invalide"

**Solution :** Utilisez le bouton "Se connecter avec Tesla OAuth" pour obtenir un token frais.

### ❌ "Aucun véhicule trouvé"

**Solution :** Vérifiez que vos scopes incluent `vehicle_device_data`.

### ⚠️ "Véhicule en veille (408)"

**Solution :** Attendez 30-60 secondes et réessayez. Ouvrez l'app Tesla mobile pour réveiller le véhicule.

### ❌ "Erreur de connexion"

**Solution :** Vérifiez `TESLA_FLEET_API_URL` dans `.env` et votre connexion internet.

👉 **Plus de solutions dans [QUICK_START_DASHBOARD.md](QUICK_START_DASHBOARD.md)**

---

## 🔐 Sécurité

### En développement

- ✅ HTTP localhost acceptable
- ✅ Tokens en session PHP

### En production

- ✅ **HTTPS obligatoire**
- ✅ `.env` hors du webroot
- ✅ Sessions sécurisées :
  ```php
  session.cookie_secure = 1
  session.cookie_httponly = 1
  session.cookie_samesite = "Strict"
  ```

---

## 📊 Statistiques

- **~1100 lignes** de code PHP
- **~1500 lignes** de documentation
- **7 fichiers** PHP (1 dashboard + 6 APIs)
- **6 fichiers** de documentation
- **8 commandes** testables
- **4 étapes** de vérification
- **100%** responsive

---

## 🤝 Contribution

Ce dashboard est un outil de développement. N'hésitez pas à :

- 🐛 Signaler des bugs
- 💡 Proposer des améliorations
- 📝 Améliorer la documentation
- 🎨 Personnaliser le design

---

## 📄 Licence

MIT License - Libre d'utilisation

---

## 🔗 Ressources utiles

- [Tesla Developer Portal](https://developer.tesla.com/)
- [Tesla Fleet API Docs](https://developer.tesla.com/docs/fleet-api)
- [OAuth 2.0 Tesla](https://auth.tesla.com/.well-known/openid-configuration)
- [GitHub Vehicle Command](https://github.com/teslamotors/vehicle-command)

---

## 🎉 C'est prêt !

Votre dashboard est **100% fonctionnel** et prêt à l'emploi.

**Commencez maintenant :**

```bash
php -S localhost:8000
```

Puis ouvrez : **`http://localhost:8000/setup-dashboard.php`**

---

**Happy testing! 🚗⚡**

_Créé avec ❤️ pour faciliter l'intégration de l'API Tesla Fleet_
