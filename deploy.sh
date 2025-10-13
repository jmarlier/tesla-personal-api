#!/bin/bash

# ================================
# Script de déploiement - Tesla App
# Serveur: Cocotier (app.jeromemarlier.com)
# ================================

set -e  # Arrêter en cas d'erreur

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SERVER_USER="duda6304"
SERVER_HOST="cocotier.o2switch.net"
SERVER_PATH="/home/duda6304/app.jeromemarlier.com"
GIT_REMOTE="cocotier"
GIT_BRANCH="master"

echo -e "${BLUE}╔════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   🚀 Déploiement Tesla App → Cocotier     ║${NC}"
echo -e "${BLUE}╔════════════════════════════════════════════╗${NC}"
echo ""

# ================================
# Étape 1 : Vérifications locales
# ================================
echo -e "${YELLOW}📋 Étape 1/6 : Vérifications locales...${NC}"

# Vérifier qu'on est dans le bon dossier
if [ ! -f "setup-dashboard.php" ]; then
    echo -e "${RED}❌ Erreur : fichier setup-dashboard.php non trouvé${NC}"
    echo -e "${RED}   Exécutez ce script depuis la racine du projet${NC}"
    exit 1
fi

# Vérifier Git
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo -e "${RED}❌ Erreur : Ce n'est pas un repository Git${NC}"
    exit 1
fi

# Vérifier qu'il n'y a pas de fichiers non commités
if ! git diff-index --quiet HEAD --; then
    echo -e "${YELLOW}⚠️  Vous avez des modifications non commitées${NC}"
    echo ""
    git status --short
    echo ""
    read -p "Voulez-vous continuer le déploiement ? (o/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Oo]$ ]]; then
        echo -e "${RED}❌ Déploiement annulé${NC}"
        exit 1
    fi
fi

echo -e "${GREEN}✅ Vérifications locales OK${NC}"
echo ""

# ================================
# Étape 2 : Git Push
# ================================
echo -e "${YELLOW}📤 Étape 2/6 : Push vers Git...${NC}"

# Récupérer le dernier commit
LAST_COMMIT=$(git log -1 --pretty=format:"%h - %s")

# Push vers GitHub (origin)
echo "📦 Push vers GitHub (origin)..."
if git push origin $GIT_BRANCH; then
    echo -e "${GREEN}✅ Push GitHub réussi${NC}"
else
    echo -e "${YELLOW}⚠️  Erreur lors du push GitHub (non bloquant)${NC}"
fi

# Push vers Cocotier (serveur de production)
echo "🚀 Push vers Cocotier (serveur)..."
if git push $GIT_REMOTE $GIT_BRANCH; then
    echo -e "${GREEN}✅ Push Cocotier réussi${NC}"
    echo -e "   ${BLUE}Dernier commit : $LAST_COMMIT${NC}"
else
    echo -e "${RED}❌ Erreur lors du push vers Cocotier${NC}"
    exit 1
fi
echo ""

# ================================
# Étape 3 : Connexion au serveur
# ================================
echo -e "${YELLOW}🔌 Étape 3/6 : Connexion au serveur cocotier...${NC}"

# Vérifier la connexion SSH
if ! ssh -o BatchMode=yes -o ConnectTimeout=5 $SERVER_USER@$SERVER_HOST exit 2>/dev/null; then
    echo -e "${RED}❌ Impossible de se connecter à cocotier${NC}"
    echo -e "${RED}   Vérifiez votre connexion SSH${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Connexion SSH établie${NC}"
echo ""

# ================================
# Étape 4 : Déploiement sur le serveur
# ================================
echo -e "${YELLOW}🚀 Étape 4/6 : Déploiement sur cocotier...${NC}"

ssh $SERVER_USER@$SERVER_HOST << 'ENDSSH'
    set -e
    
    APP_DIR="/home/duda6304/app.jeromemarlier.com"
    REPO_URL="ssh://duda6304@cocotier.o2switch.net/home/duda6304/repos/app.git"
    
    # Vérifier si le dossier existe et est un repo Git
    if [ -d "$APP_DIR/.git" ]; then
        echo "📂 Navigation vers le dossier du projet..."
        cd "$APP_DIR"
        
        echo "📥 Pull des dernières modifications..."
        git pull
    else
        echo "📂 Le dossier n'est pas un repo Git, clonage..."
        
        # Backup de l'ancien dossier si existe
        if [ -d "$APP_DIR" ]; then
            echo "💾 Backup de l'ancien dossier..."
            mv "$APP_DIR" "${APP_DIR}.backup.$(date +%Y%m%d-%H%M%S)"
        fi
        
        # Cloner le repository
        echo "📥 Clonage du repository..."
        git clone "$REPO_URL" "$APP_DIR"
        cd "$APP_DIR"
    fi
    
    echo "📦 Installation des dépendances Composer..."
    composer install --no-dev --optimize-autoloader
    
    echo "🔧 Configuration des permissions..."
    chmod -R 755 .
    chmod -R 775 var/ 2>/dev/null || mkdir -p var && chmod -R 775 var/
    
    echo "✅ Déploiement serveur terminé"
ENDSSH

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Déploiement sur cocotier réussi${NC}"
else
    echo -e "${RED}❌ Erreur lors du déploiement sur cocotier${NC}"
    exit 1
fi
echo ""

# ================================
# Étape 5 : Vérifications post-déploiement
# ================================
echo -e "${YELLOW}🔍 Étape 5/6 : Vérifications post-déploiement...${NC}"

# Vérifier que l'application répond
SITE_URL="https://app.jeromemarlier.com"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" $SITE_URL)

if [ "$HTTP_CODE" -eq 200 ] || [ "$HTTP_CODE" -eq 302 ]; then
    echo -e "${GREEN}✅ Site accessible (HTTP $HTTP_CODE)${NC}"
else
    echo -e "${YELLOW}⚠️  Site répond avec HTTP $HTTP_CODE${NC}"
fi
echo ""

# ================================
# Étape 6 : Résumé
# ================================
echo -e "${YELLOW}📊 Étape 6/6 : Résumé du déploiement${NC}"
echo ""
echo -e "${GREEN}╔════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   ✅ DÉPLOIEMENT RÉUSSI                    ║${NC}"
echo -e "${GREEN}╔════════════════════════════════════════════╗${NC}"
echo ""
echo -e "${BLUE}📍 Serveur     : ${NC}$SERVER_HOST"
echo -e "${BLUE}👤 Utilisateur : ${NC}$SERVER_USER"
echo -e "${BLUE}📂 Chemin      : ${NC}$SERVER_PATH"
echo -e "${BLUE}🌿 Branche     : ${NC}$GIT_BRANCH"
echo -e "${BLUE}📝 Commit      : ${NC}$LAST_COMMIT"
echo -e "${BLUE}🌐 URL         : ${NC}$SITE_URL"
echo ""
echo -e "${GREEN}🎉 Votre application est en ligne !${NC}"
echo ""
echo -e "${BLUE}Prochaines étapes :${NC}"
echo "   1. Vérifiez : $SITE_URL/setup-dashboard.php"
echo "   2. Testez l'authentification"
echo "   3. Vérifiez les logs en cas de problème"
echo ""

# ================================
# Option : Ouvrir dans le navigateur
# ================================
read -p "Voulez-vous ouvrir l'application dans le navigateur ? (o/N) " -n 1 -r
echo
if [[ $REPLY =~ ^[Oo]$ ]]; then
    if command -v open &> /dev/null; then
        open "$SITE_URL/setup-dashboard.php"
    elif command -v xdg-open &> /dev/null; then
        xdg-open "$SITE_URL/setup-dashboard.php"
    else
        echo -e "${BLUE}Ouvrez manuellement : $SITE_URL/setup-dashboard.php${NC}"
    fi
fi

echo ""
echo -e "${GREEN}✨ Déploiement terminé avec succès !${NC}"

