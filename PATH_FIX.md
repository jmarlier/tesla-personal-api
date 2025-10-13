# 🔧 Correction des Chemins - Serveur Cocotier

## ✅ Problème Résolu

Les scripts utilisaient un mauvais chemin d'accès pour le serveur.

### ❌ Chemins Incorrects (avant)

```
/var/www/html
```

### ✅ Chemins Corrects (maintenant)

```
/home/duda6304/app.jeromemarlier.com
~/app.jeromemarlier.com           (alias)
```

## 📝 Fichiers Corrigés

| Fichier           | Modification                                            |
| ----------------- | ------------------------------------------------------- |
| `Makefile`        | ✅ Chemins de `server-setup` et `server-check` corrigés |
| `server-check.sh` | ✅ Détection du dossier web mise à jour                 |

## 🚀 Commandes à Utiliser Maintenant

### Configuration Automatique

```bash
# Étape 1: Configurer le serveur
make server-setup
```

Cette commande va :

- Copier `config/private-key.pem` vers `~/app.jeromemarlier.com/config/`
- Sécuriser les permissions (chmod 600)
- Créer le dossier `var/`
- Installer les dépendances Composer

### Créer .env Manuellement

```bash
# Étape 2: Se connecter et créer .env
ssh duda6304@cocotier.o2switch.net
cd ~/app.jeromemarlier.com
cp .env.example .env
nano .env
chmod 600 .env
exit
```

### Vérifier la Configuration

```bash
# Étape 3: Vérifier que tout est OK
make server-check
```

## 📂 Structure sur le Serveur

```
/home/duda6304/app.jeromemarlier.com/
├── public/                      # Document root Apache/Nginx
│   ├── .htaccess
│   ├── index.php
│   ├── get-token.php
│   ├── login.php
│   ├── callback.php
│   ├── dashboard.php
│   └── logout.php
│
├── config/                      # Configuration sensible
│   └── private-key.pem         # À copier
│
├── src/                         # Code métier
│   └── TeslaAuth.php
│
├── var/                         # Tokens utilisateur (à créer)
│   └── tokens.json
│
├── vendor/                      # Dépendances Composer
│
├── .env                         # À créer manuellement
├── .env.example                 # Template
├── composer.json
└── composer.lock
```

## 🔍 Vérification Apache/Nginx

Le document root doit pointer vers le sous-dossier `public/` :

### Apache

```apache
<VirtualHost *:443>
    ServerName app.jeromemarlier.com
    DocumentRoot /home/duda6304/app.jeromemarlier.com/public

    <Directory /home/duda6304/app.jeromemarlier.com/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx

```nginx
server {
    listen 443 ssl;
    server_name app.jeromemarlier.com;
    root /home/duda6304/app.jeromemarlier.com/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## ✅ Checklist de Configuration

- [ ] Chemins corrigés (déjà fait)
- [ ] `make server-setup` exécuté
- [ ] `.env` créé sur le serveur avec les vraies valeurs
- [ ] Clé privée copiée (600)
- [ ] Dossier `var/` créé (755)
- [ ] Dépendances Composer installées
- [ ] `make server-check` réussi
- [ ] Tests de l'application réussis

## 🧪 Tests à Effectuer

```bash
# Test 1: Vérifier les fichiers
ssh duda6304@cocotier.o2switch.net "ls -la ~/app.jeromemarlier.com/"

# Test 2: Page d'accueil
curl -I https://app.jeromemarlier.com/

# Test 3: Partner Token
curl https://app.jeromemarlier.com/get-token.php

# Test 4: Clé publique
curl https://app.jeromemarlier.com/.well-known/appspecific/com.tesla.3p.public-key.pem

# Test 5: Interface web
open https://app.jeromemarlier.com
```

## 🎯 Commande Rapide (Tout en Un)

```bash
# Configuration complète automatique
make server-setup && \
ssh duda6304@cocotier.o2switch.net "cd ~/app.jeromemarlier.com && cp .env.example .env" && \
echo "⚠️  Éditez maintenant .env sur le serveur avec: ssh duda6304@cocotier.o2switch.net 'nano ~/app.jeromemarlier.com/.env'" && \
make server-check
```

## 📚 Documentation

Pour plus de détails, consultez :

- `POST_DEPLOY.md` - Guide complet de configuration serveur
- `DEPLOY.md` - Guide de déploiement
- `Makefile` - Toutes les commandes disponibles

---

**✅ Chemins corrigés - Prêt pour la configuration !**

_Lancez: `make server-setup`_
