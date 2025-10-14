# 🚗 Tesla Fleet API - Application d'authentification

Application complète pour l'authentification et l'utilisation de l'API Tesla Fleet.

## 📋 Architecture du projet

```
tesla-app/
├── cli/                          # Scripts en ligne de commande
│   ├── 01-get-fleet-token.php   # Étape 1 : Obtention du Fleet Auth Token
│   └── 02-register-partner.php  # Étape 2 : Validation du Partner Account
├── src/                          # Classes PHP
│   └── TeslaFleetClient.php     # Client API Tesla Fleet
├── api/                          # Endpoints API
│   ├── vehicles.php             # Liste des véhicules
│   ├── vehicle-data.php         # Données détaillées d'un véhicule
│   └── send-command.php         # Envoyer des commandes
├── config/                       # Configuration
│   └── private-key.pem          # Clé privée EC (secp256r1)
├── public/                       # Interface web
│   ├── index.php                # Page d'accueil
│   ├── login.php                # Initiation OAuth2
│   ├── callback.php             # Callback OAuth2
│   ├── dashboard.php            # Tableau de bord (avec AJAX)
│   └── logout.php               # Déconnexion
├── var/                          # Données générées
│   ├── fleet-auth-token.json    # Fleet Auth Token
│   ├── partner-account.json     # Infos Partner Account
│   └── user-tokens/             # Tokens utilisateurs
├── .env                          # Configuration (à ne pas commiter)
├── .env.example                 # Template de configuration
├── composer.json                 # Dépendances PHP
├── README.md                     # Ce fichier
├── DEPLOIEMENT.md               # Guide de déploiement
└── ETAPE_4_API.md               # Documentation API complète
```

---

## 🎯 Flow d'authentification

```
┌────────────────────────────────────────────────────────────────────┐
│                   ① Création du Partner (Fleet Auth)               │
├────────────────────────────────────────────────────────────────────┤
│ POST https://fleet-auth.prd.vn.cloud.tesla.com/oauth2/v3/token     │
│    ↓                                                               │
│ → Donne un access_token "fleet" temporaire                         │
│                                                                    │
│ Validation des infos Partner depuis .env                           │
│    ↓                                                               │
│ → Stocke client_id, client_secret, public_key, etc.               │
└────────────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌────────────────────────────────────────────────────────────────────┐
│              ② Authentification utilisateur Tesla                  │
│                   "Third-party Tokens" (OAuth2)                    │
├────────────────────────────────────────────────────────────────────┤
│ FRONTEND : l'utilisateur clique sur "Connecter mon compte Tesla"   │
│   ↓                                                                │
│ Redirection vers Tesla :                                           │
│   GET https://auth.tesla.com/oauth2/v3/authorize                   │
│   ?client_id=<client_id_du_partner>                                │
│   &redirect_uri=<callback.php>                                     │
│   &scope=openid vehicle_device_data vehicle_cmds                   │
│   &response_type=code                                              │
│                                                                    │
│ Tesla demande à l'utilisateur de se connecter et d'autoriser       │
│   ↓                                                                │
│ Tesla redirige vers :                                              │
│   https://app.jeromemarlier.com/callback.php?code=XXX&state=YYY   │
└────────────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌────────────────────────────────────────────────────────────────────┐
│           ③ callback.php → Échange du code contre token            │
├────────────────────────────────────────────────────────────────────┤
│ POST https://auth.tesla.com/oauth2/v3/token                        │
│   {                                                                │
│     "grant_type": "authorization_code",                            │
│     "client_id": "<client_id>",                                    │
│     "client_secret": "<client_secret>",                            │
│     "code": "<code_reçu>",                                         │
│     "redirect_uri": "<callback_url>"                               │
│   }                                                                │
│   ↓                                                                │
│ → Réponse : access_token, refresh_token, expires_in               │
│                                                                    │
│ 💾 Stockage en session et dans /var/user-tokens/                   │
└────────────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌────────────────────────────────────────────────────────────────────┐
│               ④ Appels vers la Fleet API Tesla                     │
├────────────────────────────────────────────────────────────────────┤
│ Tous les appels se font avec :                                     │
│   Authorization: Bearer <access_token_utilisateur>                 │
│                                                                    │
│ Exemple :                                                          │
│   GET /api/1/vehicles                                              │
│   POST /api/1/vehicles/{id}/wake_up                                │
│                                                                    │
│ → Actions au nom de l'utilisateur Tesla connecté                   │
└────────────────────────────────────────────────────────────────────┘
```

---

## 🚀 Installation

### 1. Cloner le projet

```bash
git clone <repo>
cd tesla-app
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer l'environnement

Le fichier `.env` est déjà configuré avec vos informations.

### 4. Vérifier la clé privée

```bash
ls -la config/private-key.pem
```

---

## 📖 Utilisation

### ÉTAPE 1 : Obtenir le Fleet Auth Token

```bash
php cli/01-get-fleet-token.php
```

**Ce que ça fait :**

- Se connecte à l'API Tesla avec vos credentials (client_id + client_secret)
- Obtient un Fleet Auth Token (valide 8 heures)
- Sauvegarde dans `/var/fleet-auth-token.json`

**Réponse attendue :**

```json
{
  "access_token": "eyJhbGci...",
  "expires_in": 28800,
  "token_type": "Bearer"
}
```

### ÉTAPE 2 : Valider le Partner Account

```bash
php cli/02-register-partner.php
```

**Ce que ça fait :**

- Lit les informations depuis `.env`
- Valide la configuration (client_id, client_secret, redirect_uri, etc.)
- Vérifie la présence de la clé privée EC
- Sauvegarde tout dans `/var/partner-account.json`

**Informations validées :**

- ✅ Client ID
- ✅ Client Secret
- ✅ Redirect URI
- ✅ Clé privée EC (secp256r1)
- ✅ Endpoints Tesla configurés

### ÉTAPE 3 : Authentification utilisateur (Interface Web)

1. **Ouvrir dans le navigateur :**

   ```
   https://app.jeromemarlier.com/index.php
   ```

2. **Cliquer sur "Se connecter avec Tesla"**

3. **Le flow OAuth2 démarre :**

   - `login.php` génère l'URL d'autorisation
   - Redirection vers `auth.tesla.com`
   - L'utilisateur se connecte et autorise l'app
   - Tesla redirige vers `callback.php?code=XXX`
   - `callback.php` échange le code contre un access token
   - Affichage de la **réponse complète de l'API Tesla**

4. **Résultat :**
   - Token utilisateur sauvegardé en session
   - Token sauvegardé dans `/var/user-tokens/user_xxxxx.json`
   - Redirection vers le tableau de bord

---

## 🔍 Debug & Logs

### Mode debug pour login.php

Pour voir l'URL OAuth2 avant la redirection :

```
https://app.jeromemarlier.com/login.php?debug=1
```

### Affichage des réponses API

Tous les scripts affichent **la réponse complète de l'API Tesla** pour faciliter le debug :

- ✅ `01-get-fleet-token.php` → Affiche le JSON complet du Fleet Token
- ✅ `02-register-partner.php` → Affiche la configuration complète
- ✅ `callback.php` → Affiche la réponse d'échange du token avec tous les détails

---

## 📁 Fichiers générés

### `/var/fleet-auth-token.json`

```json
{
  "access_token": "eyJhbGci...",
  "token_type": "Bearer",
  "expires_in": 28800,
  "created_at": 1760432586,
  "expires_at": 1760461386,
  "audience": "https://fleet-api.prd.na.vn.cloud.tesla.com"
}
```

### `/var/partner-account.json`

```json
{
    "validated_at": 1760433192,
    "validated_date": "2025-10-14 09:13:12",
    "partner_info": {
        "client_id": "c9c40292-...",
        "client_secret": "ta-secret...",
        "redirect_uri": "https://app.jeromemarlier.com/callback.php",
        ...
    }
}
```

### `/var/user-tokens/user_xxxxx.json`

```json
{
  "access_token": "eyJhbGci...",
  "refresh_token": "eyJhbGci...",
  "expires_in": 28800,
  "created_at": 1760433500,
  "expires_at": 1760462300
}
```

---

## 🔐 Sécurité

### Protection CSRF

- Génération d'un `state` aléatoire lors de l'initiation OAuth2
- Vérification du `state` dans le callback
- Stockage en session pour validation

### Stockage des tokens

- Tokens utilisateurs stockés dans `/var/user-tokens/` (hors Git)
- Sessions PHP pour les tokens actifs
- Clé privée EC protégée dans `/config/` (hors Git)

### .gitignore

```gitignore
/vendor/
.env
/var/
*.pem
*.key
```

---

## ✅ Étapes complétées

### ÉTAPE 4 : Intégration de l'API Fleet Tesla

- ✅ Créé `src/TeslaFleetClient.php` - Classe helper pour l'API
- ✅ Créé `api/vehicles.php` - Lister les véhicules
- ✅ Créé `api/vehicle-data.php` - Obtenir les données d'un véhicule
- ✅ Créé `api/send-command.php` - Envoyer des commandes
- ✅ Mis à jour `dashboard.php` avec chargement dynamique AJAX

**📚 Voir la documentation complète : [ETAPE_4_API.md](ETAPE_4_API.md)**

---

## 🛠️ Technologies utilisées

- **PHP 8.0+** - Langage backend
- **Composer** - Gestionnaire de dépendances
- **phpdotenv** - Gestion des variables d'environnement
- **cURL** - Requêtes HTTP vers l'API Tesla
- **Sessions PHP** - Gestion de l'authentification

---

## 📚 Documentation Tesla

- [Tesla Fleet API](https://developer.tesla.com/docs/fleet-api)
- [OAuth 2.0 Documentation](https://developer.tesla.com/docs/fleet-api#authentication)

---

## ✅ État actuel du projet

- ✅ **Étape 1** - Fleet Auth Token : Fonctionnel
- ✅ **Étape 2** - Partner Account Validation : Fonctionnel
- ✅ **Étape 3** - OAuth2 User Flow : Fonctionnel
- ✅ **Étape 4** - Fleet API Calls : **TERMINÉ**

**🎉 APPLICATION COMPLÈTE ET FONCTIONNELLE 🎉**

---

## 🎯 Comment tester l'authentification complète

1. Exécutez `php cli/01-get-fleet-token.php` pour obtenir le Fleet Token
2. Exécutez `php cli/02-register-partner.php` pour valider la configuration
3. Ouvrez votre navigateur sur `https://app.jeromemarlier.com/`
4. Cliquez sur "Se connecter avec Tesla"
5. Connectez-vous avec votre compte Tesla
6. Autorisez l'application
7. Vérifiez que vous êtes redirigé vers le callback avec le token
8. Accédez au tableau de bord

---

**Projet créé le 14 octobre 2025** 🚀
