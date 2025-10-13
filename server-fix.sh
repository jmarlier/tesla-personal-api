#!/bin/bash

# Script de correction automatique pour le serveur
# Résout tous les problèmes détectés par server-check.sh

# Couleurs
BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
NC='\033[0m'

echo -e "${BLUE}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                                                  ║${NC}"
echo -e "${BLUE}║   🔧 Correction Automatique du Serveur          ║${NC}"
echo -e "${BLUE}║                                                  ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════╝${NC}"
echo ""

SERVER="duda6304@cocotier.o2switch.net"
APP_DIR="~/app.jeromemarlier.com"

# 1. Créer les dossiers manquants sur le serveur
echo -e "${YELLOW}Étape 1: Création des dossiers...${NC}"
ssh $SERVER << 'ENDSSH'
cd ~/app.jeromemarlier.com

# Créer config/
mkdir -p config
echo "✅ Dossier config/ créé"

# Créer var/
mkdir -p var
chmod 755 var
echo "Require all denied" > var/.htaccess
echo "✅ Dossier var/ créé et protégé"

# Créer .well-known (à la racine, pas dans public/)
mkdir -p .well-known/appspecific
chmod 755 .well-known .well-known/appspecific
echo "✅ Dossier .well-known créé"

# Corriger permissions .env
if [ -f .env ]; then
    chmod 600 .env
    echo "✅ Permissions .env corrigées (600)"
fi
ENDSSH

echo ""

# 2. Copier la clé privée
echo -e "${YELLOW}Étape 2: Copie de la clé privée...${NC}"
if [ -f "config/private-key.pem" ]; then
    scp config/private-key.pem $SERVER:$APP_DIR/config/
    ssh $SERVER "chmod 600 $APP_DIR/config/private-key.pem"
    echo -e "${GREEN}✅ Clé privée copiée et sécurisée${NC}"
else
    echo -e "${YELLOW}⚠️  Clé privée locale non trouvée (config/private-key.pem)${NC}"
    echo -e "${YELLOW}   Générez-la avec: make generate-key${NC}"
fi

echo ""

# 3. Créer la clé publique
echo -e "${YELLOW}Étape 3: Création de la clé publique...${NC}"
ssh $SERVER << 'ENDSSH'
cd ~/app.jeromemarlier.com

if [ -f config/private-key.pem ]; then
    openssl ec -in config/private-key.pem -pubout \
        -out .well-known/appspecific/com.tesla.3p.public-key.pem 2>/dev/null
    
    chmod 644 .well-known/appspecific/com.tesla.3p.public-key.pem
    echo "✅ Clé publique créée : .well-known/appspecific/com.tesla.3p.public-key.pem"
else
    echo "⚠️  Clé privée manquante, impossible de créer la clé publique"
fi
ENDSSH

echo ""

# 4. Résumé
echo -e "${BLUE}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║              RÉSUMÉ DES CORRECTIONS              ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${GREEN}✅ Dossiers créés : config/, var/, .well-known/${NC}"
echo -e "${GREEN}✅ Permissions corrigées${NC}"
echo -e "${GREEN}✅ Clé privée copiée (si disponible)${NC}"
echo -e "${GREEN}✅ Clé publique créée (si clé privée présente)${NC}"
echo ""
echo -e "${YELLOW}⚠️  N'oubliez pas de vérifier .env sur le serveur:${NC}"
echo -e "${BLUE}   ssh $SERVER${NC}"
echo -e "${BLUE}   cd $APP_DIR${NC}"
echo -e "${BLUE}   nano .env${NC}"
echo ""
echo -e "${BLUE}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                                                  ║${NC}"
echo -e "${BLUE}║  🎯 Prochaine étape: make server-check           ║${NC}"
echo -e "${BLUE}║                                                  ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════╝${NC}"

