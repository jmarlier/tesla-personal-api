# 🔐 Guide de Sécurité - Tesla Fleet API

Ce document décrit les mesures de sécurité implémentées dans cette application et les bonnes pratiques à suivre.

## 🛡️ Mesures de sécurité implémentées

### ✅ 1. Stockage sécurisé des secrets

**Problème :** Les clés privées et secrets exposés dans le code source ou le dossier public.

**Solution implémentée :**

- ✅ Clé privée stockée dans `config/` (HORS du dossier `public/`)
- ✅ Variables sensibles dans `.env` (non versionné)
- ✅ `.gitignore` configuré pour bloquer les fichiers sensibles
- ✅ Permissions restrictives sur les fichiers de clés (600)

```bash
# Vérifier les permissions
ls -la config/private-key.pem
# Doit afficher: -rw------- (600)

# Corriger si nécessaire
chmod 600 config/private-key.pem
```

### ✅ 2. Séparation du code public/privé

**Problème :** Tous les fichiers exposés au web, y compris la configuration.

**Solution implémentée :**

- ✅ Seul `public/` est exposé comme document root
- ✅ `config/`, `src/`, `.env` sont inaccessibles depuis le web
- ✅ `.htaccess` bloque l'accès aux fichiers `.pem`, `.key`, `.env`

**Configuration Apache :**

```apache
<VirtualHost *:80>
    DocumentRoot /path/to/tesla-app/public  # ← IMPORTANT

    <Directory /path/to/tesla-app/config>
        Require all denied  # Bloquer l'accès
    </Directory>
</VirtualHost>
```

**Configuration Nginx :**

```nginx
server {
    root /path/to/tesla-app/public;  # ← IMPORTANT

    location ^~ /config/ {
        deny all;  # Bloquer l'accès
    }

    location ~ /\.(env|pem|key) {
        deny all;  # Bloquer les fichiers sensibles
    }
}
```

### ✅ 3. Contrôle de version sécurisé

**Problème :** Secrets commitées dans Git.

**Solution implémentée :**

- ✅ `.gitignore` bloque `.env`, `*.pem`, `*.key`
- ✅ `.env.example` fourni comme template (sans secrets)
- ✅ Instructions claires dans le README

**Vérifier que les secrets ne sont pas trackés :**

```bash
git status --ignored
# Ne doit PAS montrer .env ou *.pem dans "Changes to be committed"
```

### ✅ 4. Validation et gestion des erreurs

**Problème :** Fuites d'informations via les messages d'erreur.

**Solution implémentée :**

- ✅ Try-catch pour toutes les opérations sensibles
- ✅ Messages d'erreur génériques pour l'utilisateur
- ✅ Logs détaillés (mais sécurisés) pour le debugging

### ✅ 5. Sécurité HTTP

**Problème :** Communication non sécurisée avec l'API.

**Solution implémentée :**

- ✅ HTTPS forcé pour les appels API Tesla
- ✅ En-têtes de sécurité dans `.htaccess`
- ✅ Protection XSS, Clickjacking, MIME sniffing

**En-têtes de sécurité (.htaccess) :**

```apache
Header set X-XSS-Protection "1; mode=block"
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
```

## 🚨 Checklist de sécurité pour la production

### Avant le déploiement

- [ ] ✅ Le serveur web expose UNIQUEMENT le dossier `public/`
- [ ] ✅ Les permissions sur `config/private-key.pem` sont 600
- [ ] ✅ Le fichier `.env` n'est PAS versionné (vérifier `.gitignore`)
- [ ] ✅ HTTPS est activé (certificat SSL valide)
- [ ] ✅ Les logs PHP ne montrent pas d'erreurs exposant des chemins
- [ ] ✅ `display_errors` est désactivé en production (`php.ini`)
- [ ] ✅ Les backups de clés (\*.backup) sont supprimés
- [ ] ✅ Le dossier `vendor/` est exclu du contrôle de version

### Configuration PHP pour la production

```ini
; php.ini - Configuration de production
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log
expose_php = Off
```

### Variables d'environnement

```bash
# .env - NE JAMAIS committer ce fichier!
TESLA_CLIENT_ID=votre-client-id-secret
TESLA_PRIVATE_KEY_PATH=config/private-key.pem
TESLA_FLEET_API_URL=https://fleet-api.prd.na.vn.cloud.tesla.com
```

## ⚠️ Risques de sécurité courants

### 🔴 DANGER : Clé privée dans le dépôt Git

**Symptôme :**

```bash
git log --all --full-history -- "*.pem"
# Montre des commits avec des fichiers .pem
```

**Solution :**

```bash
# Supprimer l'historique Git de la clé
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch config/private-key.pem" \
  --prune-empty --tag-name-filter cat -- --all

# Ou utiliser BFG Repo-Cleaner (plus rapide)
bfg --delete-files private-key.pem

# Forcer le push
git push origin --force --all
```

**Prévention :**

- ✅ Toujours vérifier `.gitignore` AVANT de committer
- ✅ Utiliser `git add -p` pour vérifier chaque changement
- ✅ Activer les pre-commit hooks pour scanner les secrets

### 🔴 DANGER : Document root mal configuré

**Symptôme :**

```
http://exemple.com/config/private-key.pem
# Télécharge la clé privée!
```

**Solution :**

```apache
# Apache - TOUJOURS pointer vers public/
DocumentRoot /var/www/tesla-app/public

<Directory /var/www/tesla-app/config>
    Require all denied
</Directory>
```

**Test :**

```bash
# Essayer d'accéder à la clé
curl -I https://votre-domaine.com/config/private-key.pem
# Doit retourner 403 Forbidden ou 404 Not Found
```

### 🔴 DANGER : Permissions trop permissives

**Symptôme :**

```bash
ls -la config/private-key.pem
# -rw-r--r-- (644) ← DANGER! Lisible par tous
```

**Solution :**

```bash
# Corriger les permissions
chmod 600 config/private-key.pem
chown www-data:www-data config/private-key.pem  # Propriétaire correct

# Vérifier
ls -la config/private-key.pem
# -rw------- (600) ← Correct
```

### 🔴 DANGER : Logs exposant des secrets

**Problème :**

```php
// ❌ MAUVAIS
error_log("JWT: " . $jwt);
var_dump($privateKey);
```

**Solution :**

```php
// ✅ BON
error_log("JWT généré avec succès");
error_log("Clé privée chargée depuis: " . basename($privateKeyPath));
```

## 🔍 Audit de sécurité

### Scanner les secrets dans le code

```bash
# Rechercher les clés potentiellement exposées
grep -r "BEGIN.*PRIVATE KEY" .
grep -r "-----BEGIN" . --include="*.php"

# Rechercher les secrets en dur
grep -r "client_secret" . --include="*.php"
grep -r "password.*=" . --include="*.php"
```

### Vérifier les fichiers ignorés

```bash
# Lister tous les fichiers ignorés
git status --ignored

# Vérifier que les fichiers sensibles sont bien ignorés
git check-ignore -v .env
git check-ignore -v config/private-key.pem
```

### Test de pénétration basique

```bash
# 1. Tester l'accès aux fichiers sensibles
curl -I https://votre-domaine.com/.env
curl -I https://votre-domaine.com/config/private-key.pem
curl -I https://votre-domaine.com/composer.json
# Tous doivent retourner 403 ou 404

# 2. Vérifier les en-têtes de sécurité
curl -I https://votre-domaine.com/ | grep -E "X-Frame|X-XSS|X-Content"
# Doit afficher les en-têtes de sécurité

# 3. Tester l'accès aux dossiers
curl -I https://votre-domaine.com/config/
curl -I https://votre-domaine.com/src/
# Doivent retourner 403 Forbidden
```

## 📋 Procédure en cas de compromission

### Si la clé privée est exposée

1. **Immédiat :**

   ```bash
   # Révoquer la clé sur Tesla Developer Portal
   # → https://developer.tesla.com

   # Générer une nouvelle paire de clés
   openssl ecparam -name prime256v1 -genkey -noout -out config/private-key-new.pem
   openssl ec -in config/private-key-new.pem -pubout -out config/public-key-new.pem

   # Uploader la nouvelle clé publique sur Tesla
   ```

2. **Nettoyage Git :**

   ```bash
   # Supprimer la clé de l'historique Git
   git filter-branch --force --index-filter \
     "git rm --cached --ignore-unmatch config/private-key.pem" \
     --prune-empty --tag-name-filter cat -- --all

   git push origin --force --all
   ```

3. **Audit complet :**

   ```bash
   # Vérifier les logs d'accès
   grep "private-key.pem" /var/log/apache2/access.log
   grep "private-key.pem" /var/log/nginx/access.log

   # Vérifier les tokens actifs sur Tesla Developer Portal
   ```

### Si le `.env` est exposé

1. **Changer immédiatement :**

   - Révoquer la clé privée (voir ci-dessus)
   - Générer un nouveau Client ID sur Tesla (si possible)
   - Mettre à jour `.env` avec les nouvelles valeurs

2. **Vérifier l'impact :**
   ```bash
   # Chercher dans les logs qui a accédé au .env
   grep "\.env" /var/log/apache2/access.log
   ```

## 🔗 Ressources de sécurité

### Outils recommandés

- **git-secrets** : Prévenir les commits de secrets

  ```bash
  git secrets --install
  git secrets --register-aws  # Ou autres patterns
  ```

- **TruffleHog** : Scanner l'historique Git pour des secrets

  ```bash
  trufflescan --regex --entropy=True https://github.com/user/repo
  ```

- **OWASP Dependency Check** : Scanner les vulnérabilités des dépendances
  ```bash
  composer audit
  ```

### Documentation officielle

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Tesla Security Best Practices](https://developer.tesla.com/docs/fleet-api/security)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)

## 📞 Contact de sécurité

Si vous découvrez une vulnérabilité de sécurité dans cette application :

1. **NE PAS** ouvrir d'issue publique
2. Contacter l'équipe de sécurité : [votre-email-de-securite]
3. Inclure une description détaillée de la vulnérabilité
4. Laisser 90 jours pour corriger avant divulgation publique

---

**🔒 La sécurité est un processus continu, pas une destination.**

Revoyez régulièrement ce guide et adaptez-le aux nouvelles menaces.
