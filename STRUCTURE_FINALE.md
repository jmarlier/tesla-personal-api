# 📁 Structure finale du projet Tesla Fleet API

## ⚠️ CHANGEMENT IMPORTANT

Le dossier `/api/` a été **déplacé dans `/public/api/`** pour être accessible via le web.

---

## 📦 Structure complète

```
tesla-app/
│
├── cli/                              📂 Scripts en ligne de commande
│   ├── 01-get-fleet-token.php        → Obtenir Fleet Auth Token
│   └── 02-register-partner.php       → Valider Partner Account
│
├── src/                              📂 Classes PHP
│   └── TeslaFleetClient.php          → Client API Tesla Fleet
│
├── config/                           📂 Configuration
│   └── private-key.pem               → Clé privée EC (secp256r1)
│
├── vendor/                           📂 Dépendances Composer
│   └── (autoload, phpdotenv, etc.)
│
├── var/                              📂 Données générées (hors web)
│   ├── fleet-auth-token.json
│   ├── partner-account.json
│   └── user-tokens/
│
├── public/                           📂 🌐 DOCUMENT ROOT DU SERVEUR
│   │
│   ├── api/                          📂 🆕 NOUVEAU : Endpoints API
│   │   ├── vehicles.php              → Liste des véhicules
│   │   ├── vehicle-data.php          → Données détaillées
│   │   └── send-command.php          → Envoyer des commandes
│   │
│   ├── .htaccess                     → Sécurité Apache
│   ├── index.php                     → Page d'accueil
│   ├── login.php                     → Initiation OAuth2
│   ├── callback.php                  → Callback OAuth2
│   ├── dashboard.php                 → Tableau de bord
│   └── logout.php                    → Déconnexion
│
├── .env                              📄 Configuration (ne pas commiter)
├── .env.example                      📄 Template de configuration
├── .htaccess                         📄 Sécurité racine
├── .gitignore                        📄 Fichiers à ignorer
├── composer.json                     📄 Dépendances
├── README.md                         📄 Documentation
├── DEPLOIEMENT.md                    📄 Guide de déploiement
└── ETAPE_4_API.md                    📄 Documentation API

```

---

## 🌐 URLs accessibles

Si votre serveur a `/public/` comme document root :

| Fichier                       | URL accessible                                       |
| ----------------------------- | ---------------------------------------------------- |
| `public/index.php`            | `https://app.jeromemarlier.com/index.php`            |
| `public/dashboard.php`        | `https://app.jeromemarlier.com/dashboard.php`        |
| `public/api/vehicles.php`     | `https://app.jeromemarlier.com/api/vehicles.php`     |
| `public/api/vehicle-data.php` | `https://app.jeromemarlier.com/api/vehicle-data.php` |
| `public/api/send-command.php` | `https://app.jeromemarlier.com/api/send-command.php` |

---

## 🚀 Déploiement

### Sur votre serveur

```bash
# Structure attendue sur le serveur
/var/www/app.jeromemarlier.com/
├── public/                    ← Document root
│   ├── api/                   ← Nouveau dossier
│   ├── index.php
│   ├── dashboard.php
│   └── ...
├── vendor/
├── src/
├── config/
├── var/
└── .env
```

### Fichiers à copier

1. **Tout le contenu de `/public/`** (y compris le nouveau dossier `/public/api/`)
2. Le dossier `/vendor/`
3. Le dossier `/src/`
4. Le dossier `/config/`
5. Le fichier `.env`

---

## ✅ Vérifications après déploiement

### Test 1 : API directement

```bash
curl https://app.jeromemarlier.com/api/vehicles.php?format=json
```

**Résultat attendu :**

```json
{
  "error": "Unauthorized",
  "message": "Vous devez être authentifié pour accéder à cette ressource"
}
```

### Test 2 : Dashboard

Ouvrir `https://app.jeromemarlier.com/dashboard.php`

**Résultat attendu :**

- Si NON connecté : Message d'erreur clair en JSON
- Si CONNECTÉ : Vos véhicules affichés

---

## 🔧 Chemins des fichiers

### Dans les fichiers de `/public/api/`

```php
// Chargement de l'autoload depuis public/api/
require_once __DIR__ . '/../../vendor/autoload.php';

// Chargement du .env depuis public/api/
$dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
```

### Dans les fichiers de `/public/`

```php
// Chargement de l'autoload depuis public/
require_once __DIR__ . '/../vendor/autoload.php';

// Chargement du .env depuis public/
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
```

---

## 🎯 Points importants

✅ Le dossier `/api/` est maintenant **dans `/public/`** pour être accessible
✅ Tous les chemins ont été ajustés pour pointer vers `/vendor/` et `.env`
✅ Les fichiers retournent toujours du JSON quand `format=json` est demandé
✅ Gestion d'erreurs améliorée avec try/catch

---

## 📝 Prochaines étapes

1. **Déployer** le nouveau dossier `/public/api/` sur le serveur
2. **Tester** les URLs directement
3. **Se connecter** via OAuth2
4. **Tester** les appels API avec vos véhicules

---

**Date de mise à jour** : 14 octobre 2025
