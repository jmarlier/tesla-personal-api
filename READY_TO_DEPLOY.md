# 🚀 Prêt à Déployer - Guide Final

## ✅ Restructuration Terminée

Votre projet a été **nettoyé et restructuré** pour fonctionner parfaitement avec O2Switch.

## 📋 Ce qui a été fait

### 🧹 Nettoyage (12 fichiers supprimés)

- ✅ Doublons supprimés (callback-web.php, index-web.php, login-web.php)
- ✅ Fichiers legacy supprimés (jwt.php, partner*.php, vehicles*.php, etc.)
- ✅ Anciens tokens supprimés (partner.json, debug.json, etc.)

### 🔧 Adaptation

- ✅ Fichiers web déplacés à la racine
- ✅ Tous les chemins corrigés (**DIR** adapté)
- ✅ URLs relatives corrigées (pas de `/`)
- ✅ .htaccess de sécurité créé

### 📁 Structure Finale

```
tesla-app/
├── index.php             # Interface web
├── get-token.php         # API Partner Token
├── login.php             # Login OAuth
├── callback.php          # Callback OAuth
├── dashboard.php         # Dashboard
├── logout.php            # Déconnexion
├── .htaccess             # Sécurité (NOUVEAU)
├── config/               # Clés (protégé)
├── src/                  # Code (protégé)
├── var/                  # Tokens (protégé)
└── .env                  # Config (protégé)
```

## 🚀 Déploiement (3 étapes simples)

### Étape 1: Déployer

```bash
./deploy-improved.sh
```

**Répondre :**

- Ajouter fichiers ? **y**
- Message : **"Restructuration O2Switch - fichiers à la racine avec sécurité .htaccess"**
- Push GitHub ? **y**
- Déployer Cocotier ? **y**

### Étape 2: Configurer le Serveur

```bash
./server-fix.sh
```

Ce script va :

- Créer `config/`, `var/`, `.well-known/`
- Copier la clé privée
- Générer la clé publique
- Corriger les permissions

### Étape 3: Vérifier

```bash
make server-check
```

## ✅ Tests de Validation

### Test 1: Clé Publique (Important pour Tesla)

```bash
curl https://app.jeromemarlier.com/.well-known/appspecific/com.tesla.3p.public-key.pem
```

**✅ Attendu :** Affiche la clé publique

```
-----BEGIN PUBLIC KEY-----
MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE...
-----END PUBLIC KEY-----
```

### Test 2: Partner Token

```bash
curl https://app.jeromemarlier.com/get-token.php
```

**✅ Attendu :** JSON avec access_token

```json
{
  "success": true,
  "data": {
    "access_token": "eyJ...",
    "token_type": "bearer",
    "expires_in": 28800
  }
}
```

### Test 3: Interface Web

```bash
open https://app.jeromemarlier.com
```

**✅ Attendu :**

- Interface moderne gradient violet
- Bouton "Obtenir un Partner Token"
- Bouton "Se connecter avec Tesla"
- Client ID affiché

### Test 4: User OAuth

```bash
open https://app.jeromemarlier.com/login.php
```

**✅ Attendu :**

- Redirection vers auth.tesla.com
- Login Tesla
- Callback vers dashboard
- Liste des véhicules

## 🔒 Sécurité Maintenue

Malgré les fichiers à la racine, tout est sécurisé :

| Élément        | Protection                     |
| -------------- | ------------------------------ |
| `config/`      | ✅ Bloqué par .htaccess        |
| `src/`         | ✅ Bloqué par .htaccess        |
| `var/`         | ✅ Bloqué par .htaccess        |
| `.env`         | ✅ Bloqué par .htaccess        |
| `*.pem`        | ✅ Bloqué par .htaccess        |
| `*.key`        | ✅ Bloqué par .htaccess        |
| `.well-known/` | ✅ Autorisé (requis par Tesla) |

### Vérifier la Sécurité

```bash
# Ces URLs doivent retourner 403 Forbidden
curl -I https://app.jeromemarlier.com/.env
curl -I https://app.jeromemarlier.com/config/private-key.pem
curl -I https://app.jeromemarlier.com/var/tokens.json

# Cette URL doit retourner 200 OK
curl -I https://app.jeromemarlier.com/.well-known/appspecific/com.tesla.3p.public-key.pem
```

## 🐛 Dépannage Rapide

### Erreur 500 après déploiement

```bash
# Voir les logs
ssh duda6304@cocotier.o2switch.net "tail -50 ~/app.jeromemarlier.com/error_log"

# Vérifier les permissions
ssh duda6304@cocotier.o2switch.net "cd ~/app.jeromemarlier.com && ls -la .env config/"
```

### Erreur "Clé privée introuvable"

```bash
# Copier la clé
scp config/private-key.pem duda6304@cocotier.o2switch.net:~/app.jeromemarlier.com/config/
ssh duda6304@cocotier.o2switch.net "chmod 600 ~/app.jeromemarlier.com/config/private-key.pem"
```

### Erreur "Variables d'environnement manquantes"

```bash
# Vérifier .env
ssh duda6304@cocotier.o2switch.net "cat ~/app.jeromemarlier.com/.env"

# Si vide ou incorrect, éditer
ssh duda6304@cocotier.o2switch.net
cd ~/app.jeromemarlier.com
nano .env
```

## 🎯 Checklist Finale

Avant de déclarer victoire :

- [ ] Nettoyage local effectué (12 fichiers supprimés)
- [ ] Fichiers web à la racine
- [ ] .htaccess créé et configuré
- [ ] Chemins corrigés dans tous les PHP
- [ ] Déploiement effectué (`./deploy-improved.sh`)
- [ ] Configuration serveur (`./server-fix.sh`)
- [ ] Vérification serveur (`make server-check`)
- [ ] Test clé publique réussi
- [ ] Test Partner Token réussi
- [ ] Test interface web réussi
- [ ] Test User OAuth réussi

## 📊 Statistiques Finales

- **Fichiers nettoyés** : 12
- **Fichiers web** : 6 (à la racine)
- **Scripts CLI** : 3
- **Scripts de déploiement** : 6
- **Documentation** : 17 fichiers
- **Structure** : Propre et sécurisée

## 🎉 Conclusion

Votre application Tesla Fleet API est maintenant :

- ✅ **Nettoyée** - Plus de doublons ou fichiers legacy
- ✅ **Adaptée** - Structure O2Switch (fichiers à la racine)
- ✅ **Sécurisée** - .htaccess protège tout ce qui est sensible
- ✅ **Prête** - À déployer et utiliser

---

**🚀 Commandes à Lancer :**

```bash
# 1. Déployer
./deploy-improved.sh

# 2. Configurer serveur
./server-fix.sh

# 3. Vérifier
make server-check

# 4. Tester
curl https://app.jeromemarlier.com/get-token.php
open https://app.jeromemarlier.com
```

**🎉 Votre application Tesla est prête à rouler ! 🚗⚡**
