#!/bin/bash

# Script de vérification de la configuration serveur
# À exécuter sur le serveur après déploiement

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                                                  ║${NC}"
echo -e "${BLUE}║   🔍 Vérification Configuration Serveur          ║${NC}"
echo -e "${BLUE}║                                                  ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════╝${NC}"
echo ""

ERRORS=0
WARNINGS=0

# Fonction de vérification
check() {
    local test_name="$1"
    local command="$2"
    local message_ok="${3:-OK}"
    local message_fail="${4:-FAILED}"
    
    echo -n "  Vérification: $test_name... "
    
    if eval "$command" > /dev/null 2>&1; then
        echo -e "${GREEN}✅ $message_ok${NC}"
        return 0
    else
        echo -e "${RED}❌ $message_fail${NC}"
        ((ERRORS++))
        return 1
    fi
}

check_warning() {
    local test_name="$1"
    local command="$2"
    local message_ok="${3:-OK}"
    local message_warn="${4:-WARNING}"
    
    echo -n "  Vérification: $test_name... "
    
    if eval "$command" > /dev/null 2>&1; then
        echo -e "${GREEN}✅ $message_ok${NC}"
        return 0
    else
        echo -e "${YELLOW}⚠️  $message_warn${NC}"
        ((WARNINGS++))
        return 1
    fi
}

# 1. Vérification de l'emplacement
echo -e "${BLUE}📁 Vérification de l'emplacement...${NC}"
check "Dossier web" "test -d ~/app.jeromemarlier.com || test -d /var/www/html || test -d ~/www" \
    "Dossier trouvé" "Dossier web introuvable"
echo ""

# 2. Vérification des fichiers essentiels
echo -e "${BLUE}📄 Vérification des fichiers...${NC}"
check "composer.json" "test -f composer.json" \
    "Présent" "Manquant"
check "src/TeslaAuth.php" "test -f src/TeslaAuth.php" \
    "Présent" "Manquant"
check ".env.example" "test -f .env.example" \
    "Présent" "Manquant"
check_warning ".env" "test -f .env" \
    "Présent" "À créer (cp .env.example .env)"
echo ""

# 3. Vérification des clés
echo -e "${BLUE}🔐 Vérification des clés...${NC}"
check_warning "Clé privée" "test -f config/private-key.pem" \
    "Présente" "À copier depuis local"

if [ -f config/private-key.pem ]; then
    perms=$(stat -c "%a" config/private-key.pem 2>/dev/null || stat -f "%Lp" config/private-key.pem 2>/dev/null)
    if [ "$perms" = "600" ]; then
        echo -e "  ${GREEN}✅ Permissions clé privée: $perms${NC}"
    else
        echo -e "  ${YELLOW}⚠️  Permissions clé privée: $perms (devrait être 600)${NC}"
        echo -e "     ${BLUE}→ chmod 600 config/private-key.pem${NC}"
        ((WARNINGS++))
    fi
fi

check_warning "Clé publique (.well-known)" \
    "test -f .well-known/appspecific/com.tesla.3p.public-key.pem" \
    "Présente" "À créer"
echo ""

# 4. Vérification PHP
echo -e "${BLUE}🐘 Vérification PHP...${NC}"
check "PHP installé" "command -v php" \
    "$(php -v | head -1)" "PHP non trouvé"

if command -v php > /dev/null; then
    check "Extension curl" "php -m | grep -q curl" \
        "Installée" "Manquante"
    check "Extension openssl" "php -m | grep -q openssl" \
        "Installée" "Manquante"
    check "Extension json" "php -m | grep -q json" \
        "Installée" "Manquante"
fi
echo ""

# 5. Vérification Composer
echo -e "${BLUE}📦 Vérification Composer...${NC}"
check "Composer installé" "command -v composer" \
    "$(composer --version 2>/dev/null | head -1)" "Composer non trouvé"

check_warning "Dépendances installées" "test -d vendor && test -f vendor/autoload.php" \
    "Installées" "À installer (composer install)"
echo ""

# 6. Vérification des dossiers
echo -e "${BLUE}📂 Vérification des dossiers...${NC}"
check "Dossier public/" "test -d public" \
    "Présent" "Manquant"
check "Dossier config/" "test -d config" \
    "Présent" "Manquant"
check "Dossier src/" "test -d src" \
    "Présent" "Manquant"
check_warning "Dossier var/" "test -d var" \
    "Présent" "À créer (mkdir -p var)"
echo ""

# 7. Vérification des permissions
echo -e "${BLUE}🔒 Vérification des permissions...${NC}"
if [ -f .env ]; then
    perms=$(stat -c "%a" .env 2>/dev/null || stat -f "%Lp" .env 2>/dev/null)
    if [ "$perms" = "600" ]; then
        echo -e "  ${GREEN}✅ Permissions .env: $perms${NC}"
    else
        echo -e "  ${YELLOW}⚠️  Permissions .env: $perms (devrait être 600)${NC}"
        echo -e "     ${BLUE}→ chmod 600 .env${NC}"
        ((WARNINGS++))
    fi
fi
echo ""

# 8. Test de connectivité
echo -e "${BLUE}🌐 Tests de connectivité...${NC}"
if command -v curl > /dev/null; then
    check_warning "Accès à Tesla API" \
        "curl -s -I https://fleet-api.prd.na.vn.cloud.tesla.com | head -1 | grep -q '200\|301\|302'" \
        "Accessible" "Restriction firewall (normal sur serveurs mutualisés)"
fi
echo ""

# 9. Résumé
echo -e "${BLUE}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                  RÉSUMÉ                          ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════╝${NC}"
echo ""

if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo -e "${GREEN}✅ Tout est OK ! Votre serveur est prêt.${NC}"
    echo ""
    echo -e "${BLUE}🚀 Prochaines étapes:${NC}"
    echo ""
    echo "  1. Tester l'API:"
    echo "     curl https://app.jeromemarlier.com/get-token.php"
    echo ""
    echo "  2. Tester l'interface:"
    echo "     open https://app.jeromemarlier.com"
    echo ""
elif [ $ERRORS -eq 0 ]; then
    echo -e "${YELLOW}⚠️  $WARNINGS avertissement(s)${NC}"
    echo ""
    echo -e "${BLUE}Actions recommandées:${NC}"
    echo ""
    if [ ! -f .env ]; then
        echo "  → Créer .env:"
        echo "    cp .env.example .env && nano .env && chmod 600 .env"
        echo ""
    fi
    if [ ! -f config/private-key.pem ]; then
        echo "  → Copier la clé privée (depuis votre machine locale):"
        echo "    scp config/private-key.pem user@server:/var/www/html/config/"
        echo "    chmod 600 config/private-key.pem"
        echo ""
    fi
    if [ ! -d vendor ]; then
        echo "  → Installer les dépendances:"
        echo "    composer install --no-dev --optimize-autoloader"
        echo ""
    fi
    if [ ! -d var ]; then
        echo "  → Créer le dossier var:"
        echo "    mkdir -p var && chmod 755 var"
        echo ""
    fi
else
    echo -e "${RED}❌ $ERRORS erreur(s) détectée(s)${NC}"
    echo -e "${YELLOW}⚠️  $WARNINGS avertissement(s)${NC}"
    echo ""
    echo -e "${BLUE}Vérifiez les erreurs ci-dessus et corrigez-les.${NC}"
    echo ""
fi

echo -e "${BLUE}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                                                  ║${NC}"
echo -e "${BLUE}║  📚 Guide complet: POST_DEPLOY.md                ║${NC}"
echo -e "${BLUE}║                                                  ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════╝${NC}"
echo ""

exit $ERRORS

