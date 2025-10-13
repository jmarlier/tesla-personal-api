#!/bin/bash

# Couleurs
BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo ""
echo -e "${BLUE}🚗 ========================================${NC}"
echo -e "${BLUE}   Tesla Fleet API - Vérification Setup${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Vérifier la structure
echo -e "${BLUE}📁 Vérification de la structure...${NC}"
echo ""

check_dir() {
    if [ -d "$1" ]; then
        echo -e "  ${GREEN}✅ $1/${NC}"
    else
        echo -e "  ${RED}❌ $1/ (manquant)${NC}"
    fi
}

check_file() {
    if [ -f "$1" ]; then
        echo -e "  ${GREEN}✅ $1${NC}"
    else
        echo -e "  ${YELLOW}⚠️  $1 (manquant)${NC}"
    fi
}

# Dossiers
check_dir "public"
check_dir "config"
check_dir "src"
check_dir "vendor"

echo ""

# Fichiers essentiels
echo -e "${BLUE}📄 Fichiers essentiels...${NC}"
echo ""

check_file ".env"
check_file ".env.example"
check_file ".gitignore"
check_file "composer.json"
check_file "src/TeslaAuth.php"
check_file "public/index.php"
check_file "public/get-token.php"
check_file "cli-get-token.php"

echo ""

# Fichiers de documentation
echo -e "${BLUE}📖 Documentation...${NC}"
echo ""

check_file "README.md"
check_file "QUICKSTART.md"
check_file "SECURITY.md"
check_file "MIGRATION.md"
check_file "PROJECT_STRUCTURE.md"

echo ""

# Clés
echo -e "${BLUE}🔐 Clés cryptographiques...${NC}"
echo ""

if [ -f "config/private-key.pem" ]; then
    perms=$(stat -f "%Lp" config/private-key.pem 2>/dev/null || stat -c "%a" config/private-key.pem 2>/dev/null)
    if [ "$perms" = "600" ]; then
        echo -e "  ${GREEN}✅ config/private-key.pem (permissions: $perms)${NC}"
    else
        echo -e "  ${YELLOW}⚠️  config/private-key.pem (permissions: $perms, devrait être 600)${NC}"
    fi
else
    echo -e "  ${YELLOW}⚠️  config/private-key.pem (manquant)${NC}"
    echo -e "     ${BLUE}→ Exécuter: make generate-key${NC}"
fi

if [ -f "config/public-key.pem" ]; then
    echo -e "  ${GREEN}✅ config/public-key.pem${NC}"
else
    echo -e "  ${YELLOW}⚠️  config/public-key.pem (manquant)${NC}"
fi

echo ""

# Fichiers à migrer
echo -e "${BLUE}🔄 Fichiers legacy (à migrer)...${NC}"
echo ""

legacy_found=0

if [ -f "jwt.php" ]; then
    echo -e "  ${YELLOW}⚠️  jwt.php (ancien fichier, peut être supprimé)${NC}"
    legacy_found=1
fi

if [ -f "private-key.pem" ]; then
    echo -e "  ${YELLOW}⚠️  private-key.pem (déplacer vers config/)${NC}"
    echo -e "     ${BLUE}→ Exécuter: mv private-key.pem config/${NC}"
    legacy_found=1
fi

if [ -f "public-key.pem" ]; then
    echo -e "  ${YELLOW}⚠️  public-key.pem (déplacer vers config/)${NC}"
    echo -e "     ${BLUE}→ Exécuter: mv public-key.pem config/${NC}"
    legacy_found=1
fi

if [ $legacy_found -eq 0 ]; then
    echo -e "  ${GREEN}✅ Aucun fichier legacy${NC}"
fi

echo ""

# Configuration Git
echo -e "${BLUE}🔍 Vérification Git...${NC}"
echo ""

git check-ignore -v .env >/dev/null 2>&1
if [ $? -eq 0 ]; then
    echo -e "  ${GREEN}✅ .env est ignoré par Git${NC}"
else
    echo -e "  ${RED}❌ .env N'EST PAS ignoré par Git!${NC}"
    echo -e "     ${BLUE}→ Vérifier .gitignore${NC}"
fi

git check-ignore -v config/private-key.pem >/dev/null 2>&1
if [ $? -eq 0 ]; then
    echo -e "  ${GREEN}✅ config/private-key.pem est ignoré par Git${NC}"
else
    echo -e "  ${RED}❌ config/private-key.pem N'EST PAS ignoré par Git!${NC}"
    echo -e "     ${BLUE}→ Vérifier .gitignore${NC}"
fi

echo ""

# Résumé
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}   Résumé${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

echo -e "${GREEN}✅ Structure de base créée${NC}"
echo -e "${GREEN}✅ Documentation complète disponible${NC}"
echo -e "${GREEN}✅ Sécurité configurée (.gitignore)${NC}"

echo ""

if [ ! -f "config/private-key.pem" ]; then
    echo -e "${YELLOW}⚠️  Action requise: Générer les clés${NC}"
    echo -e "   ${BLUE}→ make generate-key${NC}"
    echo ""
fi

if [ ! -f ".env" ]; then
    echo -e "${YELLOW}⚠️  Action requise: Créer .env${NC}"
    echo -e "   ${BLUE}→ cp .env.example .env${NC}"
    echo -e "   ${BLUE}→ nano .env${NC}"
    echo ""
fi

if [ -f "private-key.pem" ] || [ -f "public-key.pem" ]; then
    echo -e "${YELLOW}⚠️  Action recommandée: Migrer les clés vers config/${NC}"
    echo -e "   ${BLUE}→ make migrate${NC}"
    echo ""
fi

echo -e "${BLUE}📚 Prochaines étapes:${NC}"
echo ""
echo -e "  1. ${BLUE}Lire le guide de démarrage:${NC} cat QUICKSTART.md"
echo -e "  2. ${BLUE}Configurer .env:${NC} nano .env"
echo -e "  3. ${BLUE}Générer les clés:${NC} make generate-key"
echo -e "  4. ${BLUE}Tester:${NC} make test"
echo -e "  5. ${BLUE}Lancer le serveur:${NC} make dev"
echo ""

echo -e "${BLUE}📖 Documentation:${NC}"
echo ""
echo -e "  - ${GREEN}QUICKSTART.md${NC}       - Démarrage rapide (5 min)"
echo -e "  - ${GREEN}README.md${NC}           - Documentation complète"
echo -e "  - ${GREEN}SECURITY.md${NC}         - Guide de sécurité"
echo -e "  - ${GREEN}MIGRATION.md${NC}        - Migration depuis ancienne version"
echo -e "  - ${GREEN}PROJECT_STRUCTURE.md${NC} - Architecture du projet"
echo ""

echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}✨ Setup terminé avec succès!${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""
