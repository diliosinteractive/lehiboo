#!/bin/bash

###############################################################################
# Deploy Script - Le Hiboo AI Backend to Plesk Server
#
# Ce script déploie le backend sur le serveur Plesk via SSH
#
# Usage: ./scripts/deploy.sh [serveur] [user]
# Exemple: ./scripts/deploy.sh lehiboo.dilios.me juba
###############################################################################

set -e  # Exit on error

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SERVER=${1:-"lehiboo.dilios.me"}
USER=${2:-"juba"}
REMOTE_PATH="/var/www/vhosts/${SERVER}/lehiboo-ai-backend"

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}    Le Hiboo AI Backend - Déploiement Serveur${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "Serveur: ${SERVER}"
echo "Utilisateur: ${USER}"
echo "Chemin distant: ${REMOTE_PATH}"
echo ""

# Vérifier que rsync est installé
if ! command -v rsync &> /dev/null; then
    echo -e "${RED}❌ rsync n'est pas installé${NC}"
    echo "   macOS: rsync est préinstallé"
    echo "   Linux: sudo apt install rsync"
    exit 1
fi

# Vérifier connexion SSH
echo "🔐 Vérification de la connexion SSH..."
if ! ssh -o ConnectTimeout=5 ${USER}@${SERVER} "echo 'OK'" &> /dev/null; then
    echo -e "${RED}❌ Impossible de se connecter à ${USER}@${SERVER}${NC}"
    echo ""
    echo "Solutions:"
    echo "  1. Vérifier que le serveur est accessible"
    echo "  2. Vérifier vos identifiants SSH"
    echo "  3. Ajouter votre clé SSH: ssh-copy-id ${USER}@${SERVER}"
    exit 1
fi

echo -e "${GREEN}✅ Connexion SSH OK${NC}"
echo ""

# Confirmer le déploiement
echo -e "${YELLOW}⚠️  Êtes-vous sûr de vouloir déployer sur ${SERVER} ? (y/N)${NC}"
read -r CONFIRM

if [[ ! "$CONFIRM" =~ ^[Yy]$ ]]; then
    echo "❌ Déploiement annulé"
    exit 0
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Créer le dossier sur le serveur
echo "📁 Création du dossier distant..."
ssh ${USER}@${SERVER} "mkdir -p ${REMOTE_PATH}"

echo -e "${GREEN}✅ Dossier créé${NC}"
echo ""

# Synchroniser les fichiers
echo "📤 Synchronisation des fichiers..."
echo ""

rsync -avz \
    --progress \
    --exclude 'node_modules' \
    --exclude 'logs' \
    --exclude '.git' \
    --exclude '.env' \
    --exclude '.env.*' \
    --exclude '*.log' \
    --exclude '.DS_Store' \
    --exclude 'coverage' \
    --exclude 'dist' \
    --exclude 'build' \
    ./ ${USER}@${SERVER}:${REMOTE_PATH}/

echo ""
echo -e "${GREEN}✅ Fichiers synchronisés${NC}"
echo ""

# Déployer avec Docker Compose
echo "🐳 Déploiement Docker sur le serveur..."
echo ""

ssh ${USER}@${SERVER} << 'ENDSSH'
    set -e

    # Aller dans le dossier
    cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend

    # Vérifier que .env.production existe
    if [ ! -f ".env.production" ]; then
        echo "❌ ERREUR: .env.production n'existe pas sur le serveur"
        echo "   Créez ce fichier avec vos clés API avant de déployer"
        exit 1
    fi

    # Arrêter l'ancien container (si existe)
    echo "Arrêt de l'ancien container..."
    docker-compose down || true

    # Build la nouvelle image
    echo "Build de la nouvelle image..."
    docker-compose build --no-cache

    # Démarrer le nouveau container
    echo "Démarrage du nouveau container..."
    docker-compose up -d

    # Attendre que le container soit prêt
    echo "Attente du démarrage..."
    sleep 5

    # Vérifier le status
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Status des containers:"
    docker-compose ps

    echo ""
    echo "Logs récents:"
    docker-compose logs --tail 20
ENDSSH

echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}✅ Déploiement terminé avec succès !${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# Tester le health check
echo "🧪 Test du health check..."
echo ""

HEALTH_URL="https://ai.${SERVER}/health"

# Attendre 10 secondes pour que le service démarre
sleep 10

if curl -f -s "${HEALTH_URL}" > /dev/null; then
    echo -e "${GREEN}✅ Backend opérationnel !${NC}"
    echo ""
    echo "URL du backend: https://ai.${SERVER}"
    echo "Health check: ${HEALTH_URL}"
    echo ""
    curl -s "${HEALTH_URL}" | python3 -m json.tool
else
    echo -e "${YELLOW}⚠️  Le health check a échoué${NC}"
    echo "   URL testée: ${HEALTH_URL}"
    echo ""
    echo "Vérifications à faire:"
    echo "  1. Le container tourne: ssh ${USER}@${SERVER} 'docker ps'"
    echo "  2. Les logs: ssh ${USER}@${SERVER} 'docker logs lehiboo-ai-backend'"
    echo "  3. Le reverse proxy est configuré dans Plesk"
    echo "  4. Le SSL est activé"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Commandes utiles:"
echo ""
echo "Voir les logs:"
echo "  ssh ${USER}@${SERVER} 'cd ${REMOTE_PATH} && docker-compose logs -f'"
echo ""
echo "Redémarrer:"
echo "  ssh ${USER}@${SERVER} 'cd ${REMOTE_PATH} && docker-compose restart'"
echo ""
echo "Arrêter:"
echo "  ssh ${USER}@${SERVER} 'cd ${REMOTE_PATH} && docker-compose down'"
echo ""
