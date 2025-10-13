#!/bin/bash

set -euo pipefail

# Couleurs
BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

REMOTE_GITHUB="origin"
REMOTE_SERVER="cocotier"
BRANCH="master"

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

# Charger .env si disponible
if [ -f "$SCRIPT_DIR/.env" ]; then
    set -a
    # shellcheck disable=SC1090
    . "$SCRIPT_DIR/.env"
    set +a
fi

# Fonction pour détecter la clé SSH
detect_ssh_key() {
    local remote="$1"
    
    # Vérifier si défini dans .env
    if [ -n "${DEPLOY_SSH_KEY_PATH:-}" ] && [ -f "$DEPLOY_SSH_KEY_PATH" ]; then
        echo "$DEPLOY_SSH_KEY_PATH"
        return 0
    fi
    
    # Détecter automatiquement selon le remote
    if [ "$remote" = "cocotier" ]; then
        if [ -f "$HOME/.ssh/id_ed25519_cocotier" ]; then
            echo "$HOME/.ssh/id_ed25519_cocotier"
            return 0
        fi
    elif [ "$remote" = "origin" ]; then
        if [ -f "$HOME/.ssh/id_ed25519_github" ]; then
            echo "$HOME/.ssh/id_ed25519_github"
            return 0
        fi
    fi
    
    # Clé par défaut
    if [ -f "$HOME/.ssh/id_ed25519" ]; then
        echo "$HOME/.ssh/id_ed25519"
        return 0
    fi
    
    # Première clé RSA trouvée
    if [ -f "$HOME/.ssh/id_rsa" ]; then
        echo "$HOME/.ssh/id_rsa"
        return 0
    fi
    
    return 1
}

prepare_git_ssh() {
    local remote="$1"
    local key_path
    
    if ! key_path=$(detect_ssh_key "$remote"); then
        echo -e "${RED}❌ Aucune clé SSH trouvée${NC}" >&2
        echo -e "${YELLOW}   Clés disponibles :${NC}" >&2
        ls -1 ~/.ssh/*.pub 2>/dev/null | sed 's/.pub$//' || echo "   Aucune"
        echo -e "${YELLOW}   Ajoutez DEPLOY_SSH_KEY_PATH dans .env${NC}" >&2
        exit 1
    fi
    
    echo -e "${GREEN}🔑 Utilisation de la clé: $key_path${NC}"
    
    local escaped_key
    escaped_key=$(printf '%q' "$key_path")
    
    export GIT_SSH_COMMAND="ssh -i $escaped_key -o IdentitiesOnly=yes -o BatchMode=yes -o StrictHostKeyChecking=accept-new"
}

echo -e "${BLUE}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                                                  ║${NC}"
echo -e "${BLUE}║       🚀 Script de Déploiement Tesla App         ║${NC}"
echo -e "${BLUE}║                                                  ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════╝${NC}"
echo ""

# État du dépôt
echo -e "${BLUE}📦 État du dépôt local :${NC}"
git status
echo ""

# Étape 1 : Ajouter les fichiers ?
read -p "$(echo -e ${YELLOW}Ajouter les fichiers modifiés ? \(git add -A\) [y/n] ${NC})" ADD_FILES
if [ "$ADD_FILES" = "y" ]; then
    git add -A
    echo -e "${GREEN}✅ Fichiers ajoutés.${NC}"
    
    # Étape 2 : Message de commit
    read -p "$(echo -e ${YELLOW}📝 Message de commit : ${NC})" COMMIT_MSG
    if [ -n "$COMMIT_MSG" ]; then
        git commit -m "$COMMIT_MSG"
        echo -e "${GREEN}✅ Commit effectué.${NC}"
    else
        echo -e "${YELLOW}⏭️  Message vide, commit annulé.${NC}"
    fi
else
    echo -e "${YELLOW}⏭️  Aucune modification ajoutée.${NC}"
fi
echo ""

# Étape 3 : Pousser sur GitHub ?
read -p "$(echo -e ${YELLOW}Pousser sur GitHub \($REMOTE_GITHUB\) ? [y/n] ${NC})" PUSH_GITHUB
if [ "$PUSH_GITHUB" = "y" ]; then
    echo -e "${BLUE}🚀 Push vers GitHub...${NC}"
    prepare_git_ssh "$REMOTE_GITHUB"
    
    if git push "$REMOTE_GITHUB" "$BRANCH"; then
        echo -e "${GREEN}✅ Push GitHub réussi.${NC}"
    else
        echo -e "${RED}❌ Le push GitHub a échoué.${NC}" >&2
    fi
else
    echo -e "${YELLOW}⏭️  Push GitHub ignoré.${NC}"
fi
echo ""

# Étape 4 : Pousser sur Cocotier ?
read -p "$(echo -e ${YELLOW}Déployer sur Cocotier \($REMOTE_SERVER\) ? [y/n] ${NC})" PUSH_SERVER
if [ "$PUSH_SERVER" = "y" ]; then
    echo -e "${BLUE}🚀 Déploiement sur Cocotier...${NC}"
    prepare_git_ssh "$REMOTE_SERVER"
    
    if git push "$REMOTE_SERVER" "$BRANCH"; then
        echo -e "${GREEN}✅ Déploiement Cocotier réussi.${NC}"
    else
        echo -e "${RED}❌ Le déploiement a échoué.${NC}" >&2
        echo -e "${YELLOW}   Vérifiez que la clé SSH est autorisée sur le serveur.${NC}" >&2
        exit 1
    fi
else
    echo -e "${YELLOW}⏭️  Déploiement serveur ignoré.${NC}"
fi
echo ""

echo -e "${GREEN}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                                                  ║${NC}"
echo -e "${GREEN}║           🏁 Déploiement terminé !               ║${NC}"
echo -e "${GREEN}║                                                  ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════╝${NC}"

