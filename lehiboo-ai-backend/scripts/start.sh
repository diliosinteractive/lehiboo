#!/bin/bash

###############################################################################
# Start Script - Le Hiboo AI Backend Docker (Local)
#
# Ce script démarre le backend en local avec Docker Compose
#
# Usage: ./scripts/start.sh
###############################################################################

set -e  # Exit on error

# Couleurs
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}    Le Hiboo AI Backend - Démarrage Local${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# Aller dans le dossier du script
cd "$(dirname "$0")/.."

# Vérifier que .env.production existe
if [ ! -f ".env.production" ]; then
    echo "⚠️  Fichier .env.production non trouvé"
    echo ""
    echo "Création depuis .env.example..."
    cp .env.example .env.production
    echo ""
    echo "⚠️  IMPORTANT: Éditez .env.production avec vos vraies clés API !"
    echo ""

    # Ouvrir l'éditeur
    if [[ "$OSTYPE" == "darwin"* ]]; then
        open .env.production
    elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
        xdg-open .env.production 2>/dev/null || nano .env.production
    fi

    echo "Appuyez sur Entrée quand vous avez configuré .env.production..."
    read -r
fi

# Démarrer avec Docker Compose
echo "🚀 Démarrage du backend..."
echo ""

docker-compose up -d

echo ""
echo -e "${GREEN}✅ Backend démarré !${NC}"
echo ""

# Attendre que le service soit prêt
echo "⏳ Attente du démarrage (5 secondes)..."
sleep 5

# Afficher le status
echo ""
echo "📊 Status des containers:"
docker-compose ps

# Tester le health check
echo ""
echo "🧪 Test du health check..."

if curl -f -s http://localhost:3000/health > /dev/null; then
    echo -e "${GREEN}✅ Backend opérationnel !${NC}"
    echo ""
    curl -s http://localhost:3000/health | python3 -m json.tool || curl -s http://localhost:3000/health
else
    echo "⚠️  Health check échoué"
    echo ""
    echo "Vérifiez les logs:"
    echo "  docker-compose logs -f"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Backend accessible sur: http://localhost:3000"
echo ""
echo "Commandes utiles:"
echo ""
echo "  Logs:       docker-compose logs -f"
echo "  Redémarrer: docker-compose restart"
echo "  Arrêter:    docker-compose down"
echo "  Status:     docker-compose ps"
echo ""
echo "Test l'API:"
echo '  curl http://localhost:3000/health'
echo ""
