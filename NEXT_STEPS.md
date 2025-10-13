# 🎯 Prochaines Étapes - Après Configuration

## ✅ Vous avez terminé server-fix et server-check

Félicitations ! Votre serveur est maintenant configuré. Voici ce qu'il faut faire ensuite.

## 🧪 Phase de Tests (5 tests essentiels)

### Test 1: Clé Publique ⭐ (LE PLUS IMPORTANT)

```bash
curl https://app.jeromemarlier.com/.well-known/appspecific/com.tesla.3p.public-key.pem
```

**✅ Résultat attendu :**
```
-----BEGIN PUBLIC KEY-----
MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE...
-----END PUBLIC KEY-----
```

**❌ Si erreur 404 :**
- La clé n'est pas accessible
- Relancer `./server-fix.sh`
- Vérifier que le fichier existe : `ssh duda6304@cocotier.o2switch.net "ls -la ~/app.jeromemarlier.com/.well-known/appspecific/"`

### Test 2: Partner Token (JWT)

```bash
curl https://app.jeromemarlier.com/get-token.php
```

**✅ Résultat attendu :**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCJ9...",
    "token_type": "bearer",
    "expires_in": 28800
  },
  "message": "Access token obtenu avec succès"
}
```

**❌ Si erreur :**
- Vérifier `.env` sur le serveur : `ssh duda6304@cocotier.o2switch.net "cat ~/app.jeromemarlier.com/.env"`
- Vérifier la clé privée : `ssh duda6304@cocotier.o2switch.net "ls -la ~/app.jeromemarlier.com/config/private-key.pem"`
- Voir les logs : `ssh duda6304@cocotier.o2switch.net "tail ~/app.jeromemarlier.com/error_log"`

### Test 3: Interface Web

```bash
open https://app.jeromemarlier.com
```

**✅ Vous devriez voir :**
- Interface moderne avec gradient violet/bleu
- Titre : "🚗 Tesla Fleet API"
- Client ID affiché : `c9c40292-ddb3-4a87-9cc0-5a0193081024`
- Bouton : "🔑 Obtenir un Partner Token (JWT)"
- Bouton : "👤 Se connecter avec Tesla (OAuth)"

**Test interactif :**
1. Cliquer sur "Obtenir un Partner Token"
2. Un JSON devrait s'afficher avec `access_token`

### Test 4: User OAuth (Authentification)

```bash
open https://app.jeromemarlier.com/login.php
```

**✅ Flux attendu :**
1. Redirection vers `auth.tesla.com`
2. Formulaire de connexion Tesla
3. Entrée de vos identifiants (email/password Tesla)
4. Redirection vers `callback.php` avec le code
5. Page "✅ Authentification réussie"
6. Access token et refresh token affichés

**✅ Après connexion :**
- Vous êtes redirigé vers le dashboard
- Vous voyez vos véhicules Tesla

### Test 5: Dashboard Véhicules

```bash
# Après connexion OAuth
open https://app.jeromemarlier.com/dashboard.php
```

**✅ Vous devriez voir :**
- Liste de vos véhicules Tesla
- Pour chaque véhicule :
  - Nom/Surnom
  - État (online/offline/asleep)
  - VIN (Vehicle Identification Number)
  - ID du véhicule
  - Options configurées

## 🚀 Si Tous les Tests Passent

Félicitations ! 🎉 Votre application Tesla Fleet API est **opérationnelle** !

### Prochaines étapes possibles :

#### 1. Améliorer le Dashboard

Ajoutez des informations supplémentaires :
- État de la batterie
- Portée restante
- État de charge
- Température intérieure
- Verrouillage
- Localisation

#### 2. Ajouter des Commandes

Créez des boutons pour :
- Verrouiller/Déverrouiller
- Klaxonner
- Flasher les phares
- Démarrer/Arrêter la climatisation
- Réveiller le véhicule

#### 3. Automatiser des Tâches

Créez des scripts CRON :
```bash
# Exemple: Réveiller le véhicule chaque matin à 7h
0 7 * * * php /home/duda6304/app.jeromemarlier.com/wake-up-vehicle.php
```

#### 4. Ajouter des Fonctionnalités

- Historique des commandes
- Notifications (email, SMS)
- Statistiques d'utilisation
- Graphiques de charge
- Planning de charge

## 🐛 Dépannage

### Erreur: "Clé privée introuvable"

```bash
# Vérifier sur le serveur
ssh duda6304@cocotier.o2switch.net "ls -la ~/app.jeromemarlier.com/config/private-key.pem"

# Si manquante, copier
scp config/private-key.pem duda6304@cocotier.o2switch.net:~/app.jeromemarlier.com/config/
ssh duda6304@cocotier.o2switch.net "chmod 600 ~/app.jeromemarlier.com/config/private-key.pem"
```

### Erreur: "Variables d'environnement manquantes"

```bash
# Vérifier .env
ssh duda6304@cocotier.o2switch.net "cat ~/app.jeromemarlier.com/.env"

# Recréer si nécessaire
ssh duda6304@cocotier.o2switch.net
cd ~/app.jeromemarlier.com
cp .env.example .env
nano .env
```

### Erreur HTTP 401 avec l'API Tesla

- Vérifier que le `TESLA_CLIENT_ID` est correct dans `.env`
- Vérifier que la clé publique correspond à la clé privée
- Vérifier sur [developer.tesla.com](https://developer.tesla.com) que votre application est bien configurée

### Erreur: "Redirect URI mismatch"

Vérifier sur Tesla Developer que le Redirect URI est exactement :
```
https://app.jeromemarlier.com/callback.php
```

## 💻 Utilisation Programmatique

### Exemple: Obtenir un Token et Lister les Véhicules

```bash
#!/bin/bash

# 1. Obtenir un access token
TOKEN=$(curl -s https://app.jeromemarlier.com/get-token.php | jq -r '.data.access_token')

# 2. Lister les véhicules
curl -H "Authorization: Bearer $TOKEN" \
  https://fleet-api.prd.na.vn.cloud.tesla.com/api/1/vehicles | jq

# 3. Obtenir les données d'un véhicule
VEHICLE_ID="1234567890"
curl -H "Authorization: Bearer $TOKEN" \
  https://fleet-api.prd.na.vn.cloud.tesla.com/api/1/vehicles/$VEHICLE_ID/vehicle_data | jq
```

### Exemple: Envoyer une Commande

```bash
# Réveiller le véhicule
curl -X POST -H "Authorization: Bearer $TOKEN" \
  https://fleet-api.prd.na.vn.cloud.tesla.com/api/1/vehicles/$VEHICLE_ID/wake_up

# Klaxonner
curl -X POST -H "Authorization: Bearer $TOKEN" \
  https://fleet-api.prd.na.vn.cloud.tesla.com/api/1/vehicles/$VEHICLE_ID/command/honk_horn

# Flasher les phares
curl -X POST -H "Authorization: Bearer $TOKEN" \
  https://fleet-api.prd.na.vn.cloud.tesla.com/api/1/vehicles/$VEHICLE_ID/command/flash_lights
```

## 📚 Documentation

Pour aller plus loin :

| Document | Contenu |
|----------|---------|
| `README.md` | Documentation complète de l'application |
| `OAUTH_FLOWS.md` | Guide détaillé des 2 flux OAuth |
| `example-api-call.php` | Exemples de code pour l'API Tesla |
| [Tesla Fleet API Docs](https://developer.tesla.com/docs/fleet-api) | Documentation officielle |

## 🔄 Maintenance

### Mettre à Jour l'Application

```bash
# 1. Modifier localement
nano src/TeslaAuth.php

# 2. Tester
make test

# 3. Déployer
./deploy-improved.sh

# 4. Vérifier
make server-check
curl https://app.jeromemarlier.com/get-token.php
```

### Surveiller les Logs

```bash
# Logs d'erreur
ssh duda6304@cocotier.o2switch.net "tail -f ~/app.jeromemarlier.com/error_log"

# Logs d'accès (si disponibles)
ssh duda6304@cocotier.o2switch.net "tail -f ~/logs/access.log"
```

## 🎉 Félicitations !

Si tous les tests passent, votre **application Tesla Fleet API est opérationnelle** ! 🚗⚡

Vous pouvez maintenant :
- ✅ Obtenir des access tokens
- ✅ Authentifier des utilisateurs
- ✅ Contrôler vos véhicules Tesla
- ✅ Développer de nouvelles fonctionnalités

---

**🚀 Bonne utilisation de votre application Tesla Fleet API !**

*Pour toute question, consultez la documentation ou les guides dans le projet.*

