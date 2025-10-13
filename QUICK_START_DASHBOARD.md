# 🚀 Guide de démarrage rapide - Tesla Setup Dashboard

## 📋 Prérequis

1. **Serveur PHP 8.0+** avec les extensions :

   - curl
   - json
   - session

2. **Compte Tesla Developer** configuré avec :

   - Client ID
   - Redirect URI
   - Scopes appropriés

3. **Dépendances installées** :
   ```bash
   composer install
   ```

## ⚙️ Configuration en 3 étapes

### Étape 1 : Configurer les variables d'environnement

Créez un fichier `.env` à la racine du projet :

```env
TESLA_CLIENT_ID=your_client_id_here
TESLA_FLEET_API_URL=https://fleet-api.prd.eu.vn.cloud.tesla.com
TESLA_REDIRECT_URI=https://your-domain.com/callback.php
TESLA_AUTH_URL=https://auth.tesla.com/oauth2/v3/authorize
TESLA_TOKEN_URL=https://fleet-auth.prd.vn.cloud.tesla.com/oauth2/v3/token
TESLA_USER_SCOPES=openid offline_access vehicle_device_data vehicle_cmds vehicle_charging_cmds
```

### Étape 2 : Vérifier les permissions du dossier

```bash
# Créer le dossier var pour les sessions si nécessaire
mkdir -p var
chmod 755 var
```

### Étape 3 : Démarrer le serveur

```bash
# Serveur de développement PHP
php -S localhost:8000

# Ou configurez Apache/Nginx pour pointer vers le dossier
```

## 🎯 Utilisation

### Méthode 1 : Setup Dashboard (Recommandé)

1. Ouvrez votre navigateur : `http://localhost:8000/setup-dashboard.php`

2. **Authentification** :

   - Option A : Coller un access token directement
   - Option B : Utiliser le flow OAuth complet

3. **Suivez les étapes** dans l'ordre :
   - ✅ Authentification
   - 🚗 Récupération des véhicules
   - 📊 Données du véhicule
   - 🎮 Test des commandes
   - 📋 Logs et debug

### Méthode 2 : Flow OAuth classique

1. Accédez à `http://localhost:8000/login.php`
2. Connectez-vous avec votre compte Tesla
3. Autorisez l'application
4. Vous serez redirigé vers le dashboard

## 🔑 Comment obtenir un Access Token

### Option A : Via l'application (recommandé)

1. Allez sur `setup-dashboard.php`
2. Cliquez sur "🔑 Se connecter avec Tesla OAuth"
3. Le token sera automatiquement sauvegardé

### Option B : Manuellement

Si vous avez déjà un token d'une autre source :

1. Ouvrez `setup-dashboard.php`
2. Collez votre token dans le champ
3. Cliquez sur "✅ Valider le token"

### Option C : Via CLI (avancé)

```bash
php cli-get-token.php
```

## 🎨 Interface du Dashboard

### Timeline des étapes (Sidebar gauche)

- **⚠️ Jaune** : Étape en attente
- **✅ Vert** : Étape complétée
- **❌ Rouge** : Étape échouée

### Panneau principal

- **Header** : Barre de progression globale + bouton reset
- **Contenu** : Cartes interactives pour chaque étape
- **Notifications** : Messages de succès/erreur en temps réel

### Section Logs

- Historique complet de toutes les actions
- Timestamp pour chaque événement
- Classification par type (success/error/info)

## 🔧 Commandes disponibles

Dans l'étape 4 "Commandes", vous pouvez tester :

| Commande           | Description                 |
| ------------------ | --------------------------- |
| ❄️ Démarrer clim   | Active la climatisation     |
| 🔥 Arrêter clim    | Désactive la climatisation  |
| 🔒 Verrouiller     | Verrouille les portes       |
| 🔓 Déverrouiller   | Déverrouille les portes     |
| 🔌 Démarrer charge | Lance la charge             |
| ⏹️ Arrêter charge  | Stoppe la charge            |
| 💡 Flash lights    | Fait clignoter les lumières |
| 📢 Klaxon          | Active le klaxon            |

## 📊 Données affichées

Pour chaque véhicule :

- 🔋 **Niveau de charge** : Pourcentage de batterie
- 🔌 **État de charge** : Charging/Complete/Disconnected
- ❄️ **Climatisation** : Active/Inactive
- 🚪 **Portes** : Verrouillées/Déverrouillées
- 📍 **Localisation** : Coordonnées GPS
- 🏁 **Autonomie** : Kilomètres restants

## 🔄 Rafraîchissement automatique

Le dashboard propose un rafraîchissement automatique :

1. Allez à l'étape 3 "Données véhicule"
2. Cochez "Rafraîchir toutes les 60s"
3. Les données seront mises à jour automatiquement

Pour désactiver : décochez simplement la case.

## 🐛 Résolution de problèmes

### "Token invalide"

**Causes possibles :**

- Token expiré
- Scopes insuffisants
- Token pour la mauvaise région

**Solution :**

1. Utilisez le bouton "Se connecter avec Tesla OAuth"
2. Vérifiez vos scopes dans `.env`
3. Vérifiez que l'URL de l'API correspond à votre région

### "Aucun véhicule trouvé"

**Causes possibles :**

- Compte Tesla sans véhicule
- Token sans les bons scopes
- Problème de région (US vs EU)

**Solution :**

1. Vérifiez que votre compte a des véhicules
2. Vérifiez les scopes : doit inclure `vehicle_device_data`
3. Consultez les logs pour plus de détails

### "Véhicule en veille (408)"

**Causes possibles :**

- Le véhicule est endormi (normal)
- Timeout de connexion

**Solution :**

1. Attendez 30-60 secondes
2. Réessayez la requête
3. Ouvrez l'app Tesla mobile pour réveiller le véhicule

### "Erreur de connexion"

**Causes possibles :**

- Problème réseau
- URL d'API incorrecte
- CORS (en développement local)

**Solution :**

1. Vérifiez votre connexion internet
2. Vérifiez `TESLA_FLEET_API_URL` dans `.env`
3. Utilisez HTTPS en production

## 📁 Structure des fichiers

```
tesla-app/
├── setup-dashboard.php          # ⭐ DASHBOARD PRINCIPAL
├── api/                         # Endpoints API
│   ├── auth.php                # Authentification
│   ├── check-session.php       # Vérification session
│   ├── vehicles.php            # Liste véhicules
│   ├── data.php                # Données véhicule
│   ├── command.php             # Commandes
│   └── reset.php               # Reset session
├── login.php                    # Page login OAuth
├── callback.php                 # Callback OAuth
├── logout.php                   # Déconnexion
├── dashboard.php                # Dashboard simple
└── .env                         # Configuration (à créer)
```

## 🔐 Sécurité

### En développement

```bash
# Utilisez HTTP localhost
http://localhost:8000/setup-dashboard.php
```

### En production

1. **Utilisez HTTPS** obligatoirement
2. **Configurez le CORS** si nécessaire
3. **Sécurisez .env** (hors du webroot si possible)
4. **Configurez les sessions** :
   ```php
   // Dans php.ini ou code
   session.cookie_secure = 1
   session.cookie_httponly = 1
   session.cookie_samesite = "Strict"
   ```

## 🚀 Déploiement

### Sur un serveur Apache

1. Activez `mod_rewrite` si nécessaire
2. Créez un `.htaccess` (déjà présent)
3. Pointez le DocumentRoot vers le dossier du projet
4. Configurez `.env` avec vos vraies valeurs

### Sur Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/tesla-app;
    index setup-dashboard.php;

    location / {
        try_files $uri $uri/ /setup-dashboard.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index setup-dashboard.php;
        include fastcgi_params;
    }
}
```

## 📚 Ressources utiles

- [Documentation Tesla Developer](https://developer.tesla.com/)
- [Tesla Fleet API](https://developer.tesla.com/docs/fleet-api)
- [OAuth 2.0 Guide](https://auth.tesla.com/.well-known/openid-configuration)
- [GitHub Vehicle Command](https://github.com/teslamotors/vehicle-command)

## 💡 Astuces

1. **Première utilisation** : Préférez le flow OAuth complet
2. **Token expiré** : Le dashboard détecte automatiquement et propose de se reconnecter
3. **Tests** : Utilisez les logs (étape 5) pour debugger
4. **Performance** : Le rafraîchissement auto désactive automatiquement en cas d'erreur
5. **Mobile** : L'interface est responsive, utilisable sur smartphone

## 📞 Support

En cas de problème :

1. **Consultez les logs** dans le dashboard (étape 5)
2. **Vérifiez la console** du navigateur (F12)
3. **Testez avec curl** pour isoler le problème :
   ```bash
   curl -X GET "https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1/vehicles" \
        -H "Authorization: Bearer YOUR_TOKEN"
   ```

---

**Bon setup ! 🚗⚡**
