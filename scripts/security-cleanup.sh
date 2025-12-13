#!/bin/bash
# ============================================================
# Le Hiboo - Script de Nettoyage et Diagnostic de Securite
# A executer sur le serveur de production
# ============================================================

set -e

# Couleurs pour output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logs
LOG_FILE="/tmp/lehiboo_security_cleanup_$(date +%Y%m%d_%H%M%S).log"
exec > >(tee -a "$LOG_FILE") 2>&1

echo -e "${BLUE}============================================================${NC}"
echo -e "${BLUE}   LE HIBOO - SCRIPT DE NETTOYAGE SECURITE${NC}"
echo -e "${BLUE}   Date: $(date)${NC}"
echo -e "${BLUE}============================================================${NC}"
echo ""

# ============================================================
# PHASE 1 : DIAGNOSTIC
# ============================================================
echo -e "${YELLOW}[PHASE 1] DIAGNOSTIC DU SYSTEME${NC}"
echo "------------------------------------------------------------"

# 1.1 Detecter les processus suspects
echo -e "\n${BLUE}[1.1] Recherche de processus suspects...${NC}"
SUSPICIOUS_PROCS=$(ps aux | grep -E "ePXKKgcl|Wxr5Gtq5|nRvxOjA|BYcNW|cryptominer|xmrig|minerd" | grep -v grep || true)

if [ -n "$SUSPICIOUS_PROCS" ]; then
    echo -e "${RED}[ALERTE] Processus malveillants detectes :${NC}"
    echo "$SUSPICIOUS_PROCS"
    FOUND_MALWARE=true
else
    echo -e "${GREEN}[OK] Aucun processus suspect connu detecte${NC}"
    FOUND_MALWARE=false
fi

# 1.2 Verifier les connexions reseau suspectes
echo -e "\n${BLUE}[1.2] Verification des connexions reseau...${NC}"
echo "Connexions etablies :"
netstat -tulpn 2>/dev/null | grep ESTABLISHED || ss -tulpn 2>/dev/null | grep ESTAB || echo "Impossible de lire les connexions"

# 1.3 Verifier les crontabs
echo -e "\n${BLUE}[1.3] Verification des crontabs...${NC}"
echo "Crontab root :"
crontab -l 2>/dev/null || echo "Pas de crontab pour root"
echo ""
echo "Crontabs systeme :"
cat /etc/crontab 2>/dev/null || echo "Impossible de lire /etc/crontab"
echo ""
echo "Fichiers dans /etc/cron.d/ :"
ls -la /etc/cron.d/ 2>/dev/null || echo "Impossible de lire /etc/cron.d/"

# 1.4 Verifier les containers Docker
echo -e "\n${BLUE}[1.4] Verification des containers Docker...${NC}"
if command -v docker &> /dev/null; then
    echo "Containers en cours d'execution :"
    docker ps --format "table {{.ID}}\t{{.Image}}\t{{.Status}}\t{{.Names}}"
    echo ""
    echo "Processus dans les containers :"
    for container in $(docker ps -q 2>/dev/null); do
        echo "--- Container: $(docker inspect --format='{{.Name}}' $container) ---"
        docker top $container 2>/dev/null || echo "Impossible de lire les processus"
    done
else
    echo "Docker non installe"
fi

# 1.5 Fichiers recemment modifies
echo -e "\n${BLUE}[1.5] Fichiers PHP modifies ces 7 derniers jours...${NC}"
find /var/www -name "*.php" -mtime -7 -type f 2>/dev/null | head -50 || echo "Aucun ou impossible de lire"

# ============================================================
# PHASE 2 : NETTOYAGE (DEMANDE CONFIRMATION)
# ============================================================
echo ""
echo -e "${YELLOW}[PHASE 2] NETTOYAGE${NC}"
echo "------------------------------------------------------------"

if [ "$FOUND_MALWARE" = true ]; then
    echo -e "${RED}Des processus malveillants ont ete detectes !${NC}"
    read -p "Voulez-vous les tuer ? (o/N) : " CONFIRM_KILL

    if [[ "$CONFIRM_KILL" =~ ^[Oo]$ ]]; then
        echo -e "${YELLOW}Arret des processus malveillants...${NC}"

        # Tuer les processus par nom
        pkill -9 -f "ePXKKgcl" 2>/dev/null && echo "- ePXKKgcl tue" || true
        pkill -9 -f "Wxr5Gtq5" 2>/dev/null && echo "- Wxr5Gtq5 tue" || true
        pkill -9 -f "nRvxOjA" 2>/dev/null && echo "- nRvxOjA tue" || true
        pkill -9 -f "BYcNW" 2>/dev/null && echo "- BYcNW tue" || true
        pkill -9 -f "xmrig" 2>/dev/null && echo "- xmrig tue" || true
        pkill -9 -f "minerd" 2>/dev/null && echo "- minerd tue" || true

        echo -e "${GREEN}Processus malveillants arretes${NC}"
    fi
fi

# 2.1 Nettoyer le container Docker
echo -e "\n${BLUE}[2.1] Nettoyage du container Docker compromis...${NC}"
read -p "Voulez-vous arreter et supprimer le container lehiboo-ai-backend ? (o/N) : " CONFIRM_DOCKER

if [[ "$CONFIRM_DOCKER" =~ ^[Oo]$ ]]; then
    if command -v docker &> /dev/null; then
        docker stop lehiboo-ai-backend 2>/dev/null && echo "Container arrete" || echo "Container deja arrete ou inexistant"
        docker rm lehiboo-ai-backend 2>/dev/null && echo "Container supprime" || echo "Container deja supprime ou inexistant"

        read -p "Supprimer les images Docker pour rebuild propre ? (o/N) : " CONFIRM_IMAGES
        if [[ "$CONFIRM_IMAGES" =~ ^[Oo]$ ]]; then
            docker image rm lehiboo-ai-backend:1.0.0 2>/dev/null || true
            docker image prune -f
            echo -e "${GREEN}Images nettoyees${NC}"
        fi
    fi
fi

# 2.2 Supprimer les fichiers malveillants connus
echo -e "\n${BLUE}[2.2] Suppression des fichiers malveillants...${NC}"
MALWARE_FILES=(
    "/app/ePXKKgcl"
    "/app/BYcNW"
    "/app/Wxr5Gtq5"
    "/app/nRvxOjA"
    "/tmp/.X11-unix/.*"
    "/tmp/.ICE-unix/.*"
    "/var/tmp/.crypto*"
)

for file in "${MALWARE_FILES[@]}"; do
    if [ -f "$file" ] || [ -d "$file" ]; then
        echo -e "${RED}Fichier suspect trouve: $file${NC}"
        read -p "Supprimer ? (o/N) : " CONFIRM_DEL
        if [[ "$CONFIRM_DEL" =~ ^[Oo]$ ]]; then
            rm -rf "$file" && echo "Supprime" || echo "Erreur suppression"
        fi
    fi
done

# 2.3 Nettoyer les crontabs suspectes
echo -e "\n${BLUE}[2.3] Verification des crontabs suspectes...${NC}"
CRON_SUSPICIOUS=$(crontab -l 2>/dev/null | grep -E "curl|wget|base64|eval|ePXK|Wxr5|crypto|miner" || true)
if [ -n "$CRON_SUSPICIOUS" ]; then
    echo -e "${RED}Entrees crontab suspectes detectees :${NC}"
    echo "$CRON_SUSPICIOUS"
    read -p "Voulez-vous editer la crontab manuellement ? (o/N) : " CONFIRM_CRON
    if [[ "$CONFIRM_CRON" =~ ^[Oo]$ ]]; then
        crontab -e
    fi
fi

# ============================================================
# PHASE 3 : SECURISATION
# ============================================================
echo ""
echo -e "${YELLOW}[PHASE 3] SECURISATION${NC}"
echo "------------------------------------------------------------"

# 3.1 Verifier les ports ouverts
echo -e "\n${BLUE}[3.1] Ports ouverts (a verifier)...${NC}"
netstat -tulpn 2>/dev/null | grep LISTEN || ss -tulpn 2>/dev/null

# 3.2 Recommandations firewall
echo -e "\n${BLUE}[3.2] Recommandations Firewall...${NC}"
echo "Commandes UFW recommandees :"
echo "  sudo ufw default deny incoming"
echo "  sudo ufw default allow outgoing"
echo "  sudo ufw allow ssh"
echo "  sudo ufw allow 80/tcp"
echo "  sudo ufw allow 443/tcp"
echo "  sudo ufw deny 3004/tcp  # Bloquer le port Docker expose"
echo "  sudo ufw enable"

# 3.3 Verifier fail2ban
echo -e "\n${BLUE}[3.3] Status fail2ban...${NC}"
if command -v fail2ban-client &> /dev/null; then
    fail2ban-client status 2>/dev/null || echo "fail2ban non actif"
else
    echo -e "${YELLOW}fail2ban non installe - Installation recommandee :${NC}"
    echo "  sudo apt install fail2ban"
fi

# ============================================================
# PHASE 4 : RAPPORT FINAL
# ============================================================
echo ""
echo -e "${YELLOW}[PHASE 4] RAPPORT FINAL${NC}"
echo "------------------------------------------------------------"

echo -e "\n${BLUE}Resume des actions :${NC}"
echo "- Log sauvegarde dans : $LOG_FILE"
echo ""
echo -e "${YELLOW}ACTIONS MANUELLES REQUISES :${NC}"
echo "1. Revoquer et regenerer les cles API OpenAI"
echo "2. Changer les mots de passe PostgreSQL"
echo "3. Regenerer JWT_SECRET et API_KEY dans .env"
echo "4. Mettre a jour WordPress et tous les plugins"
echo "5. Scanner avec WordFence ou Sucuri"
echo "6. Analyser les logs d'acces Apache/Nginx"
echo ""
echo -e "${GREEN}Script termine. Verifiez le log : $LOG_FILE${NC}"
