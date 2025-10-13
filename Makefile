.PHONY: help install setup test clean generate-key secure deploy

# Couleurs pour l'affichage
BLUE := \033[0;34m
GREEN := \033[0;32m
YELLOW := \033[0;33m
RED := \033[0;31m
NC := \033[0m # No Color

help: ## Afficher cette aide
	@echo "$(BLUE)🚗 Tesla Fleet API - Commandes disponibles$(NC)"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(GREEN)%-20s$(NC) %s\n", $$1, $$2}'
	@echo ""

install: ## Installer les dépendances Composer
	@echo "$(BLUE)📦 Installation des dépendances...$(NC)"
	composer install
	@echo "$(GREEN)✅ Dépendances installées!$(NC)"

setup: install ## Configuration initiale du projet
	@echo "$(BLUE)⚙️  Configuration du projet...$(NC)"
	php setup.php
	@echo "$(GREEN)✅ Configuration terminée!$(NC)"

test: ## Tester l'obtention d'un token (CLI)
	@echo "$(BLUE)🔑 Test d'authentification...$(NC)"
	php cli-get-token.php

test-api: ## Tester les appels à l'API Tesla
	@echo "$(BLUE)🚙 Test des appels API...$(NC)"
	php example-api-call.php

generate-key: ## Générer une nouvelle paire de clés EC (secp256r1)
	@echo "$(BLUE)🔐 Génération d'une nouvelle paire de clés EC...$(NC)"
	@mkdir -p config
	openssl ecparam -name prime256v1 -genkey -noout -out config/private-key.pem
	openssl ec -in config/private-key.pem -pubout -out config/public-key.pem
	chmod 600 config/private-key.pem
	@echo "$(GREEN)✅ Clés générées:$(NC)"
	@echo "  - Clé privée: config/private-key.pem (600)"
	@echo "  - Clé publique: config/public-key.pem"
	@echo ""
	@echo "$(YELLOW)⚠️  Uploadez uniquement la clé PUBLIQUE sur developer.tesla.com$(NC)"

secure: ## Vérifier et sécuriser les permissions
	@echo "$(BLUE)🔒 Vérification de la sécurité...$(NC)"
	@if [ -f "config/private-key.pem" ]; then \
		chmod 600 config/private-key.pem; \
		echo "$(GREEN)✅ Permissions de la clé privée: 600$(NC)"; \
	else \
		echo "$(YELLOW)⚠️  Clé privée introuvable: config/private-key.pem$(NC)"; \
	fi
	@if [ -f ".env" ]; then \
		chmod 600 .env; \
		echo "$(GREEN)✅ Permissions de .env: 600$(NC)"; \
	else \
		echo "$(YELLOW)⚠️  Fichier .env introuvable$(NC)"; \
	fi
	@echo ""
	@echo "$(BLUE)🔍 Fichiers sensibles:$(NC)"
	@ls -la config/*.pem 2>/dev/null || echo "  Aucune clé trouvée"
	@ls -la .env 2>/dev/null || echo "  Fichier .env introuvable"

check-config: ## Vérifier la configuration
	@echo "$(BLUE)📋 Vérification de la configuration...$(NC)"
	@if [ ! -f ".env" ]; then \
		echo "$(RED)❌ Fichier .env manquant!$(NC)"; \
		echo "   Exécutez: make setup"; \
		exit 1; \
	fi
	@echo "$(GREEN)✅ Fichier .env présent$(NC)"
	@if [ ! -f "config/private-key.pem" ]; then \
		echo "$(RED)❌ Clé privée manquante!$(NC)"; \
		echo "   Exécutez: make generate-key"; \
		exit 1; \
	fi
	@echo "$(GREEN)✅ Clé privée présente$(NC)"
	@if [ ! -d "vendor" ]; then \
		echo "$(RED)❌ Dépendances non installées!$(NC)"; \
		echo "   Exécutez: make install"; \
		exit 1; \
	fi
	@echo "$(GREEN)✅ Dépendances installées$(NC)"
	@echo ""
	@echo "$(GREEN)🎉 Configuration OK!$(NC)"

clean: ## Nettoyer les fichiers temporaires
	@echo "$(BLUE)🧹 Nettoyage...$(NC)"
	rm -f *.log
	rm -f *.backup
	rm -f *~
	@echo "$(GREEN)✅ Nettoyage terminé!$(NC)"

clean-all: clean ## Nettoyer tout (y compris vendor)
	@echo "$(BLUE)🧹 Nettoyage complet...$(NC)"
	rm -rf vendor/
	@echo "$(GREEN)✅ Nettoyage complet terminé!$(NC)"

dev: ## Lancer le serveur PHP de développement
	@echo "$(BLUE)🚀 Démarrage du serveur de développement...$(NC)"
	@echo "$(GREEN)➜ Application disponible sur: http://localhost:8000$(NC)"
	@echo "$(YELLOW)Appuyez sur Ctrl+C pour arrêter$(NC)"
	@echo ""
	php -S localhost:8000 -t public/

env: ## Créer un fichier .env depuis .env.example
	@if [ -f ".env" ]; then \
		echo "$(YELLOW)⚠️  .env existe déjà!$(NC)"; \
		read -p "Écraser? [y/N] " confirm; \
		if [ "$$confirm" != "y" ] && [ "$$confirm" != "Y" ]; then \
			echo "$(RED)Annulé$(NC)"; \
			exit 1; \
		fi; \
	fi
	cp .env.example .env
	@echo "$(GREEN)✅ Fichier .env créé$(NC)"
	@echo "$(YELLOW)⚠️  Éditez .env avec vos informations:$(NC)"
	@echo "   - TESLA_CLIENT_ID"
	@echo "   - TESLA_PRIVATE_KEY_PATH"
	@echo "   - TESLA_SCOPES"

show-structure: ## Afficher la structure du projet
	@echo "$(BLUE)📁 Structure du projet:$(NC)"
	@echo ""
	@tree -L 2 -I 'vendor|node_modules' --dirsfirst || ls -R

audit: ## Audit de sécurité
	@echo "$(BLUE)🔍 Audit de sécurité...$(NC)"
	@echo ""
	@echo "$(BLUE)1. Vérification Git:$(NC)"
	@git check-ignore -v .env >/dev/null 2>&1 && echo "  $(GREEN)✅ .env ignoré par Git$(NC)" || echo "  $(RED)❌ .env NON ignoré!$(NC)"
	@git check-ignore -v config/private-key.pem >/dev/null 2>&1 && echo "  $(GREEN)✅ Clé privée ignorée par Git$(NC)" || echo "  $(RED)❌ Clé privée NON ignorée!$(NC)"
	@echo ""
	@echo "$(BLUE)2. Permissions des fichiers:$(NC)"
	@if [ -f "config/private-key.pem" ]; then \
		perms=$$(stat -f "%Lp" config/private-key.pem 2>/dev/null || stat -c "%a" config/private-key.pem 2>/dev/null); \
		if [ "$$perms" = "600" ]; then \
			echo "  $(GREEN)✅ Clé privée: $$perms (sécurisé)$(NC)"; \
		else \
			echo "  $(RED)❌ Clé privée: $$perms (devrait être 600!)$(NC)"; \
		fi; \
	fi
	@echo ""
	@echo "$(BLUE)3. Fichiers sensibles dans le dossier public:$(NC)"
	@found=0; \
	for file in public/*.pem public/*.key public/.env; do \
		if [ -f "$$file" ]; then \
			echo "  $(RED)❌ Fichier sensible trouvé: $$file$(NC)"; \
			found=1; \
		fi; \
	done; \
	if [ $$found -eq 0 ]; then \
		echo "  $(GREEN)✅ Aucun fichier sensible dans public/$(NC)"; \
	fi
	@echo ""
	@echo "$(BLUE)4. Dépendances:$(NC)"
	@composer audit 2>/dev/null || echo "  $(YELLOW)⚠️  composer audit non disponible$(NC)"

update: ## Mettre à jour les dépendances
	@echo "$(BLUE)⬆️  Mise à jour des dépendances...$(NC)"
	composer update
	@echo "$(GREEN)✅ Dépendances mises à jour!$(NC)"

logs: ## Afficher les logs d'erreur PHP
	@echo "$(BLUE)📋 Logs PHP récents:$(NC)"
	@tail -n 50 /var/log/php_errors.log 2>/dev/null || \
	 tail -n 50 /var/log/apache2/error.log 2>/dev/null || \
	 tail -n 50 /var/log/nginx/error.log 2>/dev/null || \
	 echo "$(YELLOW)⚠️  Aucun fichier de log trouvé$(NC)"

migrate: ## Migrer depuis l'ancienne structure
	@echo "$(BLUE)🔄 Migration depuis l'ancienne structure...$(NC)"
	@if [ -f "private-key.pem" ] && [ ! -f "config/private-key.pem" ]; then \
		echo "$(YELLOW)➜ Déplacement de private-key.pem vers config/$(NC)"; \
		mv private-key.pem config/private-key.pem; \
		chmod 600 config/private-key.pem; \
		echo "$(GREEN)✅ Clé déplacée et sécurisée$(NC)"; \
	elif [ -f "config/private-key.pem" ]; then \
		echo "$(GREEN)✅ Clé déjà dans config/$(NC)"; \
	else \
		echo "$(YELLOW)⚠️  Aucune clé à migrer$(NC)"; \
	fi
	@echo ""
	@echo "$(BLUE)Voir MIGRATION.md pour plus de détails$(NC)"

deploy: ## Déployer l'application (script interactif)
	@echo "$(BLUE)🚀 Lancement du déploiement...$(NC)"
	@./deploy-improved.sh

deploy-github: ## Push uniquement sur GitHub
	@echo "$(BLUE)🚀 Push sur GitHub...$(NC)"
	git push origin master
	@echo "$(GREEN)✅ Push GitHub terminé!$(NC)"

deploy-server: ## Push uniquement sur Cocotier
	@echo "$(BLUE)🚀 Déploiement sur Cocotier...$(NC)"
	git push cocotier master
	@echo "$(GREEN)✅ Déploiement Cocotier terminé!$(NC)"

# Alias
run: dev ## Alias pour 'make dev'
start: dev ## Alias pour 'make dev'

