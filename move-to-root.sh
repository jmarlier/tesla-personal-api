#!/bin/bash

# Script pour déplacer les fichiers de public/ vers la racine
# Adapté aux serveurs mutualisés O2Switch

BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
NC='\033[0m'

echo -e "${BLUE}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                                                  ║${NC}"
echo -e "${BLUE}║   📦 Adaptation pour Serveur O2Switch            ║${NC}"
echo -e "${BLUE}║                                                  ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════╝${NC}"
echo ""

SERVER="duda6304@cocotier.o2switch.net"
APP_DIR="~/app.jeromemarlier.com"

echo -e "${YELLOW}🔍 Diagnostic du problème:${NC}"
echo "  Le document root est: ~/app.jeromemarlier.com (racine)"
echo "  Les fichiers web sont dans: ~/app.jeromemarlier.com/public/"
echo "  → Les URLs contiennent /public/ ce qui cause des erreurs"
echo ""

echo -e "${YELLOW}✅ Solution: Déplacer les fichiers à la racine${NC}"
echo ""

read -p "Voulez-vous déplacer les fichiers de public/ vers la racine ? [y/n] " CONFIRM

if [ "$CONFIRM" != "y" ]; then
    echo "Opération annulée."
    exit 0
fi

echo ""
echo -e "${BLUE}📦 Déplacement des fichiers...${NC}"
echo ""

ssh $SERVER << 'ENDSSH'
cd ~/app.jeromemarlier.com

echo "1. Sauvegarde de la structure actuelle..."
# Créer un backup au cas où
mkdir -p backup-public
cp -r public/* backup-public/ 2>/dev/null || true
echo "   ✅ Backup créé dans backup-public/"

echo ""
echo "2. Déplacement des fichiers PHP..."
# Déplacer les fichiers web vers la racine
if [ -f public/index.php ]; then
    mv public/index.php index.php.new
    echo "   ✅ index.php déplacé"
fi

if [ -f public/get-token.php ]; then
    mv public/get-token.php get-token.php
    echo "   ✅ get-token.php déplacé"
fi

if [ -f public/login.php ]; then
    mv public/login.php login.php.new
    echo "   ✅ login.php déplacé"
fi

if [ -f public/callback.php ]; then
    mv public/callback.php callback.php.new
    echo "   ✅ callback.php déplacé"
fi

if [ -f public/dashboard.php ]; then
    mv public/dashboard.php dashboard.php
    echo "   ✅ dashboard.php déplacé"
fi

if [ -f public/logout.php ]; then
    mv public/logout.php logout.php
    echo "   ✅ logout.php déplacé"
fi

echo ""
echo "3. Fusion des fichiers existants..."
# Si des fichiers existent déjà, on garde les nouveaux
if [ -f index.php.new ]; then
    mv index.php.new index.php
    echo "   ✅ index.php mis à jour"
fi

if [ -f login.php.new ]; then
    mv login.php.new login.php
    echo "   ✅ login.php mis à jour"
fi

if [ -f callback.php.new ]; then
    mv callback.php.new callback.php
    echo "   ✅ callback.php mis à jour"
fi

echo ""
echo "4. Configuration .htaccess..."
# Créer un .htaccess à la racine
cat > .htaccess << 'HTACCESS'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Autoriser .well-known (pour la clé publique Tesla)
    RewriteCond %{REQUEST_URI} ^/\.well-known/
    RewriteRule ^ - [L]
    
    # Rediriger vers index.php si le fichier n'existe pas
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L,QSA]
</IfModule>

# Protection des fichiers sensibles
<FilesMatch "\.(env|pem|key)$">
    Require all denied
</FilesMatch>

# Bloquer l'accès aux dossiers de configuration
<DirectoryMatch "^/.*(config|src|vendor)">
    Require all denied
</DirectoryMatch>

# En-têtes de sécurité
<IfModule mod_headers.c>
    Header set X-XSS-Protection "1; mode=block"
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
</IfModule>
HTACCESS

echo "   ✅ .htaccess créé avec sécurité renforcée"

echo ""
echo "5. Vérification finale..."
ls -la *.php | grep -E "(index|get-token|login|callback|dashboard|logout)" | head -10
echo ""
echo "✅ Migration terminée !"
ENDSSH

echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                                                  ║${NC}"
echo -e "${GREEN}║   ✅ Fichiers déplacés à la racine               ║${NC}"
echo -e "${GREEN}║                                                  ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}🧪 Testez maintenant:${NC}"
echo "  curl https://app.jeromemarlier.com/get-token.php"
echo "  open https://app.jeromemarlier.com"
echo ""

