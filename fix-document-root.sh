#!/bin/bash

# Script pour diagnostiquer et corriger le document root

echo "╔══════════════════════════════════════════════════════════════════╗"
echo "║                                                                  ║"
echo "║         🔧 CORRECTION DOCUMENT ROOT                              ║"
echo "║                                                                  ║"
echo "╚══════════════════════════════════════════════════════════════════╝"
echo ""

SERVER="duda6304@cocotier.o2switch.net"
APP_DIR="~/app.jeromemarlier.com"

echo "🔍 DIAGNOSTIC"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

echo "Erreur détectée:"
echo "  ❌ GET /public/get-token.php 500 (Internal Server Error)"
echo ""

echo "Cause:"
echo "  Le document root n'est pas configuré correctement."
echo "  Il pointe vers: ~/app.jeromemarlier.com"
echo "  Il devrait pointer vers: ~/app.jeromemarlier.com/public"
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

echo "🔧 SOLUTION"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

echo "Option 1: Créer un .htaccess à la racine pour rediriger vers public/"
echo ""

echo "Voulez-vous appliquer cette correction automatiquement ? [y/n]"
read -r CONFIRM

if [ "$CONFIRM" = "y" ]; then
    echo ""
    echo "📝 Création du .htaccess de redirection..."
    
    ssh $SERVER << 'ENDSSH'
cd ~/app.jeromemarlier.com

# Créer un .htaccess à la racine pour rediriger vers public/
cat > .htaccess << 'EOF'
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Si le fichier existe dans le dossier public, y rediriger
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteCond %{DOCUMENT_ROOT}/public%{REQUEST_FILENAME} -f
    RewriteRule ^(.*)$ /public/$1 [L]
    
    # Rediriger toutes les autres requêtes vers public/index.php
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ /public/index.php [L,QSA]
</IfModule>

# Redirection par défaut
DirectoryIndex public/index.php

# Protection des fichiers sensibles
<FilesMatch "\.(env|pem|key)$">
    Require all denied
</FilesMatch>
EOF

echo "✅ .htaccess créé à la racine"
cat .htaccess
ENDSSH

    echo ""
    echo "✅ Correction appliquée !"
    echo ""
    echo "🧪 Testez maintenant:"
    echo "   curl https://app.jeromemarlier.com/get-token.php"
    echo "   open https://app.jeromemarlier.com"
    
else
    echo ""
    echo "⏭️  Correction annulée."
    echo ""
    echo "📝 Configuration manuelle requise:"
    echo ""
    echo "Vous devez configurer le document root sur le serveur pour pointer vers:"
    echo "  /home/duda6304/app.jeromemarlier.com/public"
    echo ""
    echo "Contactez O2Switch ou modifiez la configuration via leur panel."
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Script terminé"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

