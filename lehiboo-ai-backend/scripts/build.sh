#!/bin/bash

###############################################################################
# Build Script - Le Hiboo AI Backend Docker
#
# Ce script build l'image Docker localement pour tests
#
# Usage: ./scripts/build.sh
###############################################################################

set -e  # Exit on error

# Couleurs
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}    Le Hiboo AI Backend - Build Docker Image${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# Vérifier que Docker est installé
if ! command -v docker &> /dev/null; then
    echo "❌ Docker n'est pas installé"
    echo "   Télécharger: https://docs.docker.com/get-docker/"
    exit 1
fi

echo "✅ Docker détecté: $(docker --version)"
echo ""

# Aller dans le dossier du script
cd "$(dirname "$0")/.."

# Build l'image
echo "🔨 Build de l'image Docker..."
echo ""

docker build \
    --tag lehiboo-ai-backend:latest \
    --tag lehiboo-ai-backend:1.0.0 \
    --file Dockerfile \
    .

echo ""
echo -e "${GREEN}✅ Image Docker buildée avec succès !${NC}"
echo ""

# Afficher l'image
echo "📦 Images disponibles:"
docker images | grep lehiboo-ai-backend

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Prochaines étapes:"
echo ""
echo "1. Tester localement:"
echo "   docker-compose up -d"
echo ""
echo "2. Voir les logs:"
echo "   docker-compose logs -f"
echo ""
echo "3. Tester l'API:"
echo "   curl http://localhost:3000/health"
echo ""
echo "4. Déployer sur serveur:"
echo "   ./scripts/deploy.sh"
echo ""
