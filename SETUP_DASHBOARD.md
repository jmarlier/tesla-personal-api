# 🚗 Tesla API Setup Dashboard

Dashboard complet et moderne pour vérifier et tester la connexion à l'API Tesla Fleet.

## 🎯 Fonctionnalités

### ✅ Vérification étape par étape

- **Authentification** : Validation du token d'accès Tesla OAuth 2.0
- **Véhicules** : Récupération et affichage de la liste des véhicules
- **Données véhicule** : Affichage détaillé de l'état du véhicule
- **Commandes** : Test des commandes principales de l'API
- **Logs** : Historique complet de toutes les requêtes

### 🎨 Interface moderne

- Thème sombre professionnel
- Timeline des étapes avec indicateurs visuels (✅/❌/⚠️)
- Barre de progression globale
- Notifications en temps réel
- Design responsive

## 🚀 Utilisation

### 1. Accéder au dashboard

Ouvrez votre navigateur et accédez à :

```
http://your-domain.com/setup-dashboard.php
```

### 2. Authentification

Deux options pour s'authentifier :

**Option A : Token manuel**

1. Obtenez un access token depuis votre compte Tesla
2. Collez-le dans le champ prévu
3. Cliquez sur "✅ Valider le token"

**Option B : OAuth Flow**

1. Cliquez sur "🔑 Se connecter avec Tesla OAuth"
2. Connectez-vous avec vos identifiants Tesla
3. Autorisez l'application
4. Vous serez redirigé automatiquement vers le dashboard

### 3. Parcourir les étapes

Une fois authentifié, parcourez les différentes étapes :

#### Étape 2 : Véhicules

- Cliquez sur "🔄 Charger les véhicules"
- Sélectionnez votre véhicule dans la liste
- Le statut (online/asleep/offline) est affiché

#### Étape 3 : Données du véhicule

- Cliquez sur "📥 Charger les données"
- Visualisez :
  - Niveau de charge de la batterie
  - État de la climatisation
  - Verrouillage des portes
  - Localisation GPS
  - Autonomie restante
- Activez le rafraîchissement automatique (60s)

#### Étape 4 : Commandes

- Testez les commandes disponibles :
  - ❄️ Démarrer/arrêter la climatisation
  - 🔒 Verrouiller/déverrouiller les portes
  - 🔌 Démarrer/arrêter la charge
  - 💡 Flash des lumières
  - 📢 Klaxon

#### Étape 5 : Logs

- Consultez l'historique complet de toutes les requêtes
- Les logs sont classés par type (success/error/info)
- Timestamp pour chaque événement

## 📁 Structure de l'application

```
tesla-app/
├── setup-dashboard.php      # Dashboard principal
├── api/
│   ├── auth.php            # Authentification
│   ├── check-session.php   # Vérification de session
│   ├── vehicles.php        # Liste des véhicules
│   ├── data.php            # Données du véhicule
│   ├── command.php         # Envoi de commandes
│   └── reset.php           # Réinitialisation
├── login.php               # Page de login OAuth
├── callback.php            # Callback OAuth
└── dashboard.php           # Dashboard simple (existant)
```

## 🔧 Configuration requise

### Variables d'environnement (.env)

```env
TESLA_CLIENT_ID=your_client_id
TESLA_FLEET_API_URL=https://fleet-api.prd.eu.vn.cloud.tesla.com
TESLA_REDIRECT_URI=https://your-domain.com/callback.php
TESLA_USER_SCOPES=openid offline_access vehicle_device_data vehicle_cmds vehicle_charging_cmds
```

### Dépendances PHP

```json
{
  "require": {
    "php": ">=8.0",
    "vlucas/phpdotenv": "^5.5",
    "firebase/php-jwt": "^6.4"
  }
}
```

## 🎮 Utilisation des API

### API d'authentification

```bash
POST /api/auth.php
Content-Type: application/json

{
    "access_token": "your_token_here"
}
```

### API des véhicules

```bash
GET /api/vehicles.php
```

### API des données

```bash
GET /api/data.php?vehicle_id=123456789
```

### API des commandes

```bash
POST /api/command.php
Content-Type: application/json

{
    "vehicle_id": 123456789,
    "command": "flash_lights"
}
```

## 🔒 Sécurité

- Les tokens sont stockés en session PHP
- Toutes les requêtes API sont validées
- HTTPS recommandé en production
- Les sessions expirent automatiquement

## 🐛 Dépannage

### Le token est rejeté

- Vérifiez que le token n'a pas expiré
- Utilisez le flow OAuth complet avec `login.php`
- Vérifiez les scopes dans `.env`

### Aucun véhicule trouvé

- Vérifiez que votre compte Tesla a des véhicules
- Vérifiez que le token a les bons scopes
- Consultez les logs pour voir l'erreur exacte

### Le véhicule est en veille (asleep)

- Attendez quelques secondes et réessayez
- L'API réveillera automatiquement le véhicule
- Certaines commandes peuvent échouer si le véhicule est endormi

### Erreur 408 (Timeout)

- Le véhicule est probablement en veille profonde
- Essayez d'ouvrir l'app Tesla mobile pour le réveiller
- Attendez 1-2 minutes et réessayez

## 📊 Indicateurs visuels

| Icône | Signification               |
| ----- | --------------------------- |
| ✅    | Étape complétée avec succès |
| ❌    | Étape échouée / Erreur      |
| ⚠️    | Étape en attente            |
| 📋    | Section informative         |

## 🔄 Rafraîchissement automatique

Le dashboard propose un rafraîchissement automatique des données du véhicule :

- Intervalle : 60 secondes
- Activable/désactivable à tout moment
- Utile pour surveiller l'état de charge

## 🎯 Réinitialisation

Pour recommencer la configuration :

1. Cliquez sur "🔄 Réinitialiser" dans le header
2. La session sera effacée
3. La page sera rechargée

## 📚 Ressources

- [Documentation officielle Tesla Fleet API](https://developer.tesla.com/)
- [GitHub Tesla Vehicle Command](https://github.com/teslamotors/vehicle-command)
- [Tesla OAuth 2.0 Documentation](https://auth.tesla.com/.well-known/openid-configuration)

## 🎨 Personnalisation

Le dashboard utilise un thème sombre moderne. Pour personnaliser les couleurs :

```css
/* Couleur principale (rouge Tesla) */
--primary-color: #e63946;

/* Fond principal */
--bg-dark: #0a0a0a;

/* Cartes et éléments */
--card-bg: #1a1a1a;
```

## 💡 Conseils

1. **Première utilisation** : Utilisez le flow OAuth complet pour obtenir un token valide
2. **Tests fréquents** : Le token peut expirer, rafraîchissez-le régulièrement
3. **Véhicule endormi** : Les véhicules en veille peuvent prendre du temps à répondre
4. **Logs** : Consultez toujours les logs en cas d'erreur pour diagnostiquer le problème
5. **Commandes** : Certaines commandes nécessitent que le véhicule soit en ligne

## ⚡ Performance

- Requêtes optimisées avec caching de session
- Chargement asynchrone des données
- Interface réactive sans rechargement de page
- Logs limités pour éviter la surcharge mémoire

## 🌐 Compatibilité

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ Mobile (iOS/Android)

---

**Créé avec ❤️ pour tester l'API Tesla Fleet**
