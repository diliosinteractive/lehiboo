#!/bin/bash

###############################################################################
# Deploy Local Script - Le Hiboo AI Backend
#
# Ce script est à exécuter DIRECTEMENT sur le serveur Plesk
# (pas besoin de SSH depuis votre machine locale)
#
# Usage:
#   1. Transférez les fichiers sur le serveur (FTP, Git, etc.)
#   2. SSH vers le serveur: ssh user@serveur.com
#   3. Allez dans le dossier: cd /chemin/vers/lehiboo-ai-backend
#   4. Lancez: ./scripts/deploy-local.sh
#
# Options:
#   --clean    Nettoie les anciennes images Docker avant le build
#              (utile si problème d'espace disque)
#
# Exemples:
#   ./scripts/deploy-local.sh           # Déploiement normal (rapide)
#   ./scripts/deploy-local.sh --clean   # Avec nettoyage (plus lent)
#
###############################################################################

set -e  # Exit on error

# Paramètres
CLEAN_MODE=false

# Parser les arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --clean)
            CLEAN_MODE=true
            shift
            ;;
        *)
            echo "Option inconnue: $1"
            echo "Usage: $0 [--clean]"
            exit 1
            ;;
    esac
done

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}    Le Hiboo AI Backend - Déploiement Local${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# Vérifier qu'on est dans le bon dossier
if [ ! -f "package.json" ] || [ ! -f "Dockerfile" ]; then
    echo -e "${RED}❌ ERREUR: Vous n'êtes pas dans le bon dossier${NC}"
    echo ""
    echo "Ce script doit être exécuté depuis le dossier lehiboo-ai-backend"
    echo ""
    echo "Exemple:"
    echo "  cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend"
    echo "  ./scripts/deploy-local.sh"
    exit 1
fi

echo "📂 Dossier actuel: $(pwd)"
echo ""

# Vérifier que Docker est installé
if ! command -v docker &> /dev/null; then
    echo -e "${RED}❌ Docker n'est pas installé${NC}"
    echo ""
    echo "Installez Docker depuis Plesk:"
    echo "  Extensions → Docker → Installer"
    exit 1
fi

echo -e "${GREEN}✅ Docker détecté: $(docker --version)${NC}"
echo ""

# Vérifier que docker-compose est installé
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}❌ docker-compose n'est pas installé${NC}"
    echo ""
    echo "Installation:"
    echo "  curl -L https://github.com/docker/compose/releases/latest/download/docker-compose-\$(uname -s)-\$(uname -m) -o /usr/local/bin/docker-compose"
    echo "  chmod +x /usr/local/bin/docker-compose"
    exit 1
fi

echo -e "${GREEN}✅ docker-compose détecté: $(docker-compose --version)${NC}"
echo ""

# Vérifier que .env.production existe
if [ ! -f ".env.production" ]; then
    echo -e "${YELLOW}⚠️  Fichier .env.production non trouvé${NC}"
    echo ""
    echo "Création depuis .env.example..."

    if [ -f ".env.example" ]; then
        cp .env.example .env.production
        echo -e "${GREEN}✅ Fichier .env.production créé${NC}"
        echo ""
        echo -e "${RED}⚠️  IMPORTANT: Éditez .env.production avec vos vraies clés API !${NC}"
        echo ""
        echo "Éditez maintenant:"
        echo "  nano .env.production"
        echo ""
        echo "Puis relancez ce script:"
        echo "  ./scripts/deploy-local.sh"
        echo ""
        exit 0
    else
        echo -e "${RED}❌ .env.example non trouvé${NC}"
        exit 1
    fi
fi

echo -e "${GREEN}✅ Fichier .env.production trouvé${NC}"
echo ""

# Vérifier les variables obligatoires dans .env.production
echo "🔍 Vérification des variables d'environnement..."

MISSING_VARS=()

# Source le fichier .env.production
set -a
source .env.production
set +a

# Vérifier les clés API
if [ -z "$OPENROUTER_API_KEY" ] || [ "$OPENROUTER_API_KEY" = "your-openrouter-api-key-here" ]; then
    MISSING_VARS+=("OPENROUTER_API_KEY")
fi

if [ -z "$API_KEY" ] || [ "$API_KEY" = "your-api-key-here" ]; then
    MISSING_VARS+=("API_KEY")
fi

if [ -z "$WORDPRESS_URL" ] || [ "$WORDPRESS_URL" = "https://lehiboo.dilios.me" ]; then
    echo -e "${YELLOW}⚠️  WORDPRESS_URL utilise la valeur par défaut${NC}"
fi

# Si des variables manquent
if [ ${#MISSING_VARS[@]} -gt 0 ]; then
    echo -e "${RED}❌ Variables manquantes dans .env.production:${NC}"
    for var in "${MISSING_VARS[@]}"; do
        echo "   - $var"
    done
    echo ""
    echo "Éditez .env.production:"
    echo "  nano .env.production"
    echo ""
    exit 1
fi

echo -e "${GREEN}✅ Variables d'environnement OK${NC}"
echo ""

# Afficher un résumé de la config
echo "📊 Configuration:"
echo "  - Node Env: ${NODE_ENV:-production}"
echo "  - Port: ${PORT:-3000}"
echo "  - WordPress: ${WORDPRESS_URL}"
echo "  - Model: ${DEFAULT_MODEL:-anthropic/claude-3.5-sonnet}"
echo ""

# Demander confirmation
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}Êtes-vous prêt à déployer ? (y/N)${NC}"
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
read -r CONFIRM

if [[ ! "$CONFIRM" =~ ^[Yy]$ ]]; then
    echo "❌ Déploiement annulé"
    exit 0
fi

echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}    Démarrage du Déploiement${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# 1. Arrêter l'ancien container (si existe)
if [ "$CLEAN_MODE" = true ]; then
    echo "📦 Étape 1/5: Arrêt de l'ancien container..."
else
    echo "📦 Étape 1/4: Arrêt de l'ancien container..."
fi

if docker-compose ps | grep -q "Up"; then
    docker-compose down
    echo -e "${GREEN}✅ Container arrêté${NC}"
else
    echo "ℹ️  Aucun container en cours d'exécution"
fi
echo ""

# 2. Nettoyage optionnel (seulement si --clean)
if [ "$CLEAN_MODE" = true ]; then
    echo "🧹 Étape 2/5: Nettoyage des anciennes images..."
    echo "   (Mode --clean activé)"

    BEFORE_SIZE=$(docker system df | grep 'Images' | awk '{print $4}')
    docker system prune -f > /dev/null 2>&1 || true
    AFTER_SIZE=$(docker system df | grep 'Images' | awk '{print $4}')

    echo -e "${GREEN}✅ Nettoyage terminé${NC}"
    echo "   Espace libéré: $BEFORE_SIZE → $AFTER_SIZE"
    echo ""
    STEP_BUILD="3/5"
    STEP_START="4/5"
    STEP_CHECK="5/5"
else
    STEP_BUILD="2/4"
    STEP_START="3/4"
    STEP_CHECK="4/4"
fi

# 3. Arrêter tous les containers sur le port 3004
echo "🛑 Arrêt des containers utilisant le port 3004..."

# Arrêter via docker-compose
docker-compose down > /dev/null 2>&1 || true

# Trouver et arrêter tout container utilisant le port 3004
CONTAINER_ID=$(docker ps -q --filter "publish=3004")
if [ ! -z "$CONTAINER_ID" ]; then
    echo "   Container trouvé: $CONTAINER_ID"
    docker stop $CONTAINER_ID > /dev/null 2>&1 || true
    docker rm $CONTAINER_ID > /dev/null 2>&1 || true
fi

# Vérifier aussi par nom de container
docker stop lehiboo-ai-backend > /dev/null 2>&1 || true
docker rm lehiboo-ai-backend > /dev/null 2>&1 || true

echo -e "${GREEN}✅ Port 3004 libéré${NC}"
echo ""

# 4. Build la nouvelle image
echo "🔨 Étape $STEP_BUILD: Build de la nouvelle image Docker..."
echo "   (Cela peut prendre 1-2 minutes la première fois...)"
echo ""

docker-compose build --no-cache

if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✅ Image Docker buildée avec succès${NC}"
else
    echo ""
    echo -e "${RED}❌ Échec du build Docker${NC}"
    exit 1
fi
echo ""

# 5. Démarrer le nouveau container
echo "🚀 Étape $STEP_START: Démarrage du nouveau container..."
docker-compose up -d

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Container démarré${NC}"
else
    echo -e "${RED}❌ Échec du démarrage${NC}"
    exit 1
fi
echo ""

# 6. Vérifications
echo "🔍 Étape $STEP_CHECK: Vérifications..."
echo ""

# Attendre que le service démarre
echo "⏳ Attente du démarrage (10 secondes)..."
sleep 10
echo ""

# Status des containers
echo "📊 Status des containers:"
docker-compose ps
echo ""

# Logs récents
echo "📄 Logs récents (dernières 20 lignes):"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
docker-compose logs --tail 20
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Test health check local
echo "🧪 Test du health check local..."
if curl -f -s http://localhost:3000/health > /dev/null; then
    echo -e "${GREEN}✅ Backend opérationnel !${NC}"
    echo ""
    echo "Réponse:"
    curl -s http://localhost:3000/health | python3 -m json.tool 2>/dev/null || curl -s http://localhost:3000/health
    echo ""
else
    echo -e "${RED}❌ Health check échoué${NC}"
    echo ""
    echo "Vérifiez les logs:"
    echo "  docker-compose logs -f"
    echo ""
    exit 1
fi

# Résumé final
echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}✅ Déploiement Terminé avec Succès !${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "📍 Backend accessible localement:"
echo "   http://localhost:3000"
echo ""
echo "🌐 Si reverse proxy configuré, accessible via:"
echo "   https://ai.lehiboo.dilios.me"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📚 Commandes utiles:"
echo ""
echo "  Voir les logs en temps réel:"
echo "    docker-compose logs -f"
echo ""
echo "  Redémarrer le backend:"
echo "    docker-compose restart"
echo ""
echo "  Arrêter le backend:"
echo "    docker-compose down"
echo ""
echo "  Rebuild et redémarrer:"
echo "    docker-compose down && docker-compose build && docker-compose up -d"
echo ""
echo "  Status des containers:"
echo "    docker-compose ps"
echo ""
echo "  Test health check:"
echo "    curl http://localhost:3000/health"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🎉 Le backend est prêt à recevoir des requêtes !"
echo ""
