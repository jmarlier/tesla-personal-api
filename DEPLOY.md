# 🚀 Guide de Déploiement - Tesla Fleet API

## 📋 Problème Résolu

Votre script `deploy.sh` ne fonctionnait pas car il cherchait la clé SSH par défaut `~/.ssh/id_ed25519` qui n'existe pas sur votre système.

## ✅ Solution

### Option 1: Utiliser le script amélioré (Recommandé)

Le nouveau script `deploy-improved.sh` détecte automatiquement vos clés SSH :

```bash
./deploy-improved.sh
```

**Avantages** :

- ✅ Détecte automatiquement `id_ed25519_cocotier` pour Cocotier
- ✅ Détecte automatiquement `id_ed25519_github` pour GitHub
- ✅ Interface colorée et plus claire
- ✅ Gestion d'erreurs améliorée

### Option 2: Configurer deploy.sh original

Ajoutez dans votre `.env` :

```env
# Configuration pour deploy.sh
DEPLOY_SSH_KEY_PATH=$HOME/.ssh/id_ed25519_cocotier
```

Puis lancez :

```bash
./deploy.sh
```

## 🔑 Vos Clés SSH Disponibles

```
~/.ssh/id_ed25519_alwaysdata     → AlwaysData
~/.ssh/id_ed25519_cocotier       → Cocotier (serveur de déploiement)
~/.ssh/id_ed25519_github         → GitHub
~/.ssh/jmarlier-GitHub           → GitHub (ancienne)
```

## 📝 Workflow de Déploiement

### 1. Développement Local

```bash
# Faire vos modifications
nano src/TeslaAuth.php

# Tester
make test
make dev
```

### 2. Commit et Push

**Avec le script amélioré** :

```bash
./deploy-improved.sh
```

Le script vous demandera :

1. ✅ Ajouter les fichiers ? (git add -A)
2. ✅ Message de commit ?
3. ✅ Push sur GitHub ?
4. ✅ Déployer sur Cocotier ?

**Manuellement** :

```bash
git add -A
git commit -m "Votre message"
git push origin master
git push cocotier master
```

### 3. Vérifier le Déploiement

```bash
# Vérifier sur le serveur
curl https://app.jeromemarlier.com/get-token.php

# Tester le login OAuth
open https://app.jeromemarlier.com/login.php
```

## 🔧 Configuration Serveur Cocotier

### Remotes Git Configurés

```
origin    → https://github.com/jmarlier/tesla-personal-api.git
cocotier  → ssh://duda6304@cocotier.o2switch.net/home/duda6304/repos/app.git
```

### Fichiers sur le Serveur

Après déploiement, votre code est dans :

```
/home/duda6304/repos/app.git        # Repo Git bare
/var/www/html/                      # Document root
```

### Post-receive Hook

Assurez-vous d'avoir un hook post-receive sur Cocotier :

```bash
# Sur le serveur Cocotier
cat > ~/repos/app.git/hooks/post-receive << 'EOF'
#!/bin/bash
TARGET="/var/www/html"
GIT_DIR="/home/duda6304/repos/app.git"
BRANCH="master"

while read oldrev newrev ref
do
    if [[ $ref =~ .*/master$ ]]; then
        echo "Déploiement de master vers $TARGET..."
        git --work-tree=$TARGET --git-dir=$GIT_DIR checkout -f $BRANCH

        cd $TARGET

        # Installer les dépendances Composer
        composer install --no-dev --optimize-autoloader

        # Permissions
        chmod 600 .env 2>/dev/null || true
        chmod 600 config/private-key.pem 2>/dev/null || true

        echo "✅ Déploiement terminé"
    fi
done
EOF

chmod +x ~/repos/app.git/hooks/post-receive
```

## 🐛 Dépannage

### Problème : "command not found" dans .env

**Erreur** :

```
.env: line 27: fleet_api:vehicles:write: command not found
```

**Cause** : Valeurs avec espaces sans guillemets dans `.env`

**Solution** :

```bash
# ❌ Incorrect
TESLA_SCOPES=fleet_api:vehicles:read fleet_api:vehicles:write

# ✅ Correct
TESLA_SCOPES="fleet_api:vehicles:read fleet_api:vehicles:write"
```

Toutes les valeurs avec espaces doivent être entre guillemets dans `.env`.

### Problème : "Permission denied (publickey)"

**Solution** : Vérifier que la clé SSH est ajoutée sur le serveur :

```bash
# Afficher votre clé publique
cat ~/.ssh/id_ed25519_cocotier.pub

# L'ajouter sur le serveur dans ~/.ssh/authorized_keys
```

### Problème : "fatal: 'cocotier' does not appear to be a git repository"

**Solution** : Vérifier le remote :

```bash
git remote -v

# Si manquant, ajouter :
git remote add cocotier ssh://duda6304@cocotier.o2switch.net/home/duda6304/repos/app.git
```

### Problème : "Could not resolve hostname"

**Solution** : Vérifier votre connexion réseau et le nom d'hôte :

```bash
ping cocotier.o2switch.net
```

### Problème : Script interactif bloqué

Si le script attend une interaction :

```bash
# Utiliser expect ou répondre automatiquement
echo -e "y\nMon message\ny\ny" | ./deploy-improved.sh
```

## 📦 Déploiement en Une Commande

Créez un alias dans votre `.bashrc` ou `.zshrc` :

```bash
alias deploy-tesla='cd ~/Workspace/tesla-app && ./deploy-improved.sh'
```

Puis :

```bash
deploy-tesla
```

## 🔐 Sécurité

### Fichiers à NE PAS Déployer

Le `.gitignore` protège déjà :

- `.env` (secrets)
- `*.pem` (clés)
- `var/` (tokens utilisateur)
- `tokens.json`

### Fichiers à Configurer sur le Serveur

Sur Cocotier, créez manuellement :

```bash
# .env avec les vraies valeurs
nano /var/www/html/.env

# Clé privée
scp config/private-key.pem duda6304@cocotier.o2switch.net:/var/www/html/config/
ssh duda6304@cocotier.o2switch.net "chmod 600 /var/www/html/config/private-key.pem"
```

## ✅ Checklist de Déploiement

### Avant de Déployer

- [ ] Tests locaux passent (`make test`)
- [ ] Code lint sans erreur
- [ ] `.env.example` à jour
- [ ] Documentation mise à jour

### Pendant le Déploiement

- [ ] Commit avec message clair
- [ ] Push GitHub réussi
- [ ] Push Cocotier réussi

### Après le Déploiement

- [ ] Vérifier le site : https://app.jeromemarlier.com
- [ ] Tester Partner Token
- [ ] Tester User OAuth
- [ ] Vérifier les logs serveur

## 🚀 Commandes Rapides

```bash
# Déploiement complet
./deploy-improved.sh

# Seulement GitHub
git push origin master

# Seulement Cocotier
git push cocotier master

# Déploiement forcé (⚠️ Attention)
git push cocotier master --force

# Voir les différences avant push
git diff origin/master..HEAD
```

## 📊 Monitoring

### Logs Serveur

```bash
# Connexion SSH
ssh duda6304@cocotier.o2switch.net

# Logs Apache/PHP
tail -f /var/log/apache2/error.log
tail -f /var/log/php_errors.log

# Vérifier le déploiement
cd /var/www/html
git log -1
```

### Status de Déploiement

```bash
# Vérifier le dernier commit déployé
ssh duda6304@cocotier.o2switch.net "cd /var/www/html && git log -1 --oneline"
```

---

**🚀 Déploiement simplifié et sécurisé !**

_Utilisez `deploy-improved.sh` pour un déploiement sans soucis._
