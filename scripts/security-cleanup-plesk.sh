#!/bin/bash
# ============================================================
# Le Hiboo - Script de Nettoyage PLESK
# Version adaptee pour serveurs Plesk
# ============================================================

set -e

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

LOG_FILE="/tmp/lehiboo_plesk_cleanup_$(date +%Y%m%d_%H%M%S).log"
exec > >(tee -a "$LOG_FILE") 2>&1

# ============================================================
# CONFIGURATION SPECIFIQUE LE HIBOO
# ============================================================
LEHIBOO_VHOST="/var/www/vhosts/lehiboo.com"
LEHIBOO_PREPROD="$LEHIBOO_VHOST/preprod.lehiboo.com"
LEHIBOO_BACKEND="$LEHIBOO_PREPROD/lehiboo-ai-backend"
LEHIBOO_WP="$LEHIBOO_PREPROD"  # Racine WordPress

echo -e "${BLUE}============================================================${NC}"
echo -e "${BLUE}   LE HIBOO - NETTOYAGE SECURITE PLESK${NC}"
echo -e "${BLUE}   Serveur: ns3843359${NC}"
echo -e "${BLUE}   Chemin: $LEHIBOO_PREPROD${NC}"
echo -e "${BLUE}   Date: $(date)${NC}"
echo -e "${BLUE}============================================================${NC}"

# ============================================================
# DETECTION PLESK
# ============================================================
echo -e "\n${YELLOW}[INFO] Detection de l'environnement Plesk...${NC}"

if [ -d "/usr/local/psa" ]; then
    PLESK_DIR="/usr/local/psa"
    echo -e "${GREEN}[OK] Plesk detecte: $PLESK_DIR${NC}"
elif [ -d "/opt/psa" ]; then
    PLESK_DIR="/opt/psa"
    echo -e "${GREEN}[OK] Plesk detecte: $PLESK_DIR${NC}"
else
    echo -e "${YELLOW}[WARN] Plesk non detecte, utilisation du script standard${NC}"
fi

# Version Plesk
if command -v plesk &> /dev/null; then
    PLESK_VERSION=$(plesk version 2>/dev/null | head -1)
    echo "Version Plesk: $PLESK_VERSION"
fi

# ============================================================
# PHASE 1 : DIAGNOSTIC PROCESSUS
# ============================================================
echo -e "\n${YELLOW}[PHASE 1] DIAGNOSTIC DES PROCESSUS${NC}"
echo "------------------------------------------------------------"

echo -e "\n${BLUE}[1.1] Recherche de processus malveillants...${NC}"
MALWARE_PATTERNS="ePXKKgcl|Wxr5Gtq5|nRvxOjA|BYcNW|xmrig|minerd|kdevtmpfsi|kinsing|cryptominer"

SUSPICIOUS_PROCS=$(ps aux | grep -E "$MALWARE_PATTERNS" | grep -v grep || true)

if [ -n "$SUSPICIOUS_PROCS" ]; then
    echo -e "${RED}[ALERTE] Processus malveillants detectes :${NC}"
    echo "$SUSPICIOUS_PROCS"
    FOUND_MALWARE=true

    # Extraire les PIDs
    MALWARE_PIDS=$(echo "$SUSPICIOUS_PROCS" | awk '{print $2}')
    echo -e "\n${YELLOW}PIDs a tuer: $MALWARE_PIDS${NC}"
else
    echo -e "${GREEN}[OK] Aucun processus suspect connu${NC}"
    FOUND_MALWARE=false
fi

# Processus haute CPU (>80%)
echo -e "\n${BLUE}[1.2] Processus haute consommation CPU...${NC}"
ps aux --sort=-%cpu | awk 'NR<=10 {print}' | head -10

# ============================================================
# PHASE 2 : DIAGNOSTIC DOCKER PLESK
# ============================================================
echo -e "\n${YELLOW}[PHASE 2] DIAGNOSTIC DOCKER PLESK${NC}"
echo "------------------------------------------------------------"

# Docker via Plesk
if command -v docker &> /dev/null; then
    echo -e "\n${BLUE}[2.1] Containers Docker en cours...${NC}"
    docker ps --format "table {{.ID}}\t{{.Image}}\t{{.Status}}\t{{.Names}}" 2>/dev/null || echo "Erreur lecture Docker"

    echo -e "\n${BLUE}[2.2] Recherche de processus suspects dans les containers...${NC}"
    for container in $(docker ps -q 2>/dev/null); do
        CONTAINER_NAME=$(docker inspect --format='{{.Name}}' "$container" | sed 's/\///')
        echo -e "\n--- Container: ${YELLOW}$CONTAINER_NAME${NC} ---"

        # Lister les processus
        CONTAINER_PROCS=$(docker exec "$container" ps aux 2>/dev/null || echo "Impossible de lire")
        echo "$CONTAINER_PROCS" | head -10

        # Chercher les malwares
        if echo "$CONTAINER_PROCS" | grep -qE "$MALWARE_PATTERNS"; then
            echo -e "${RED}[ALERTE] Processus suspect dans $CONTAINER_NAME !${NC}"
        fi
    done

    # Containers lehiboo specifiquement
    echo -e "\n${BLUE}[2.3] Containers Le Hiboo...${NC}"
    docker ps -a --filter "name=lehiboo" --format "table {{.ID}}\t{{.Image}}\t{{.Status}}\t{{.Names}}"
else
    echo "Docker non disponible"
fi

# ============================================================
# PHASE 3 : VERIFICATION FICHIERS MALVEILLANTS
# ============================================================
echo -e "\n${YELLOW}[PHASE 3] VERIFICATION FICHIERS${NC}"
echo "------------------------------------------------------------"

# Repertoires Le Hiboo specifiques
echo -e "\n${BLUE}[3.1] Verification du projet Le Hiboo...${NC}"
echo "Chemin WordPress: $LEHIBOO_WP"
echo "Chemin Backend AI: $LEHIBOO_BACKEND"

if [ -d "$LEHIBOO_WP" ]; then
    echo -e "\n${BLUE}[3.2] Fichiers PHP modifies recemment (7 jours) dans Le Hiboo...${NC}"
    find "$LEHIBOO_WP" -name "*.php" -mtime -7 -type f 2>/dev/null | head -30 || true

    echo -e "\n${BLUE}[3.3] Fichiers suspects dans wp-content/uploads...${NC}"
    find "$LEHIBOO_WP/wp-content/uploads" -name "*.php" -type f 2>/dev/null || echo "RAS"

    echo -e "\n${BLUE}[3.4] Fichiers caches suspects...${NC}"
    find "$LEHIBOO_WP" -name "*.php" -path "*cache*" -type f 2>/dev/null | head -10 || true

    echo -e "\n${BLUE}[3.5] Verification wp-config.php (non modifie recemment ?)...${NC}"
    ls -la "$LEHIBOO_WP/wp-config.php" 2>/dev/null || echo "wp-config.php non trouve"
fi

if [ -d "$LEHIBOO_BACKEND" ]; then
    echo -e "\n${BLUE}[3.6] Fichiers suspects dans le backend AI...${NC}"
    find "$LEHIBOO_BACKEND" -type f -perm +x -name "*" ! -name "*.sh" 2>/dev/null | head -10 || true

    echo -e "\n${BLUE}[3.7] Verification .env (secrets)...${NC}"
    if [ -f "$LEHIBOO_BACKEND/.env" ]; then
        echo -e "${YELLOW}[WARN] Fichier .env present - verifier qu'il n'est pas dans git${NC}"
        ls -la "$LEHIBOO_BACKEND/.env"
    fi
fi

# Dans /tmp
echo -e "\n${BLUE}[3.4] Executables dans /tmp...${NC}"
find /tmp /var/tmp -type f -perm +x 2>/dev/null | head -20

# ============================================================
# PHASE 4 : NETTOYAGE (INTERACTIF)
# ============================================================
echo -e "\n${YELLOW}[PHASE 4] NETTOYAGE${NC}"
echo "------------------------------------------------------------"

if [ "$FOUND_MALWARE" = true ]; then
    echo -e "${RED}Processus malveillants detectes !${NC}"
    read -p "Tuer ces processus maintenant ? (o/N) : " CONFIRM_KILL

    if [[ "$CONFIRM_KILL" =~ ^[Oo]$ ]]; then
        echo -e "${YELLOW}Arret des processus...${NC}"

        for pattern in "ePXKKgcl" "Wxr5Gtq5" "nRvxOjA" "BYcNW" "xmrig" "minerd" "kdevtmpfsi"; do
            if pgrep -f "$pattern" > /dev/null 2>&1; then
                pkill -9 -f "$pattern" && echo "- $pattern tue" || echo "- $pattern: erreur"
            fi
        done

        echo -e "${GREEN}Processus arretes${NC}"

        # Verification
        sleep 2
        REMAINING=$(ps aux | grep -E "$MALWARE_PATTERNS" | grep -v grep || true)
        if [ -n "$REMAINING" ]; then
            echo -e "${RED}[WARN] Certains processus ont redemarre !${NC}"
            echo "$REMAINING"
            echo -e "${YELLOW}Verifier les crontabs et services systemd${NC}"
        fi
    fi
fi

# Nettoyage Docker Le Hiboo
echo -e "\n${BLUE}[4.1] Nettoyage container Docker Le Hiboo...${NC}"
echo "Chemin backend: $LEHIBOO_BACKEND"

read -p "Arreter le container lehiboo-ai-backend ? (o/N) : " CONFIRM_DOCKER

if [[ "$CONFIRM_DOCKER" =~ ^[Oo]$ ]]; then
    # Arreter via docker-compose dans le bon repertoire
    if [ -f "$LEHIBOO_BACKEND/docker-compose.yml" ]; then
        echo "Arret via docker-compose dans $LEHIBOO_BACKEND..."
        cd "$LEHIBOO_BACKEND" && docker-compose down || true
    fi

    # Arret direct si docker-compose n'a pas fonctionne
    docker stop lehiboo-ai-backend 2>/dev/null && echo "Container arrete" || echo "Container deja arrete"
    docker rm lehiboo-ai-backend 2>/dev/null && echo "Container supprime" || true

    # Arreter aussi postgres si present
    docker stop lehiboo-postgres 2>/dev/null && echo "Container postgres arrete" || true

    read -p "Supprimer les images Docker pour rebuild propre ? (o/N) : " CONFIRM_IMAGE
    if [[ "$CONFIRM_IMAGE" =~ ^[Oo]$ ]]; then
        docker image rm lehiboo-ai-backend:1.0.0 2>/dev/null || true
        docker image rm lehiboo-ai-backend:secure 2>/dev/null || true
        docker image prune -f
        echo -e "${GREEN}Images nettoyees${NC}"
    fi
fi

# ============================================================
# PHASE 5 : VERIFICATION CRONTABS PLESK
# ============================================================
echo -e "\n${YELLOW}[PHASE 5] VERIFICATION CRONTABS${NC}"
echo "------------------------------------------------------------"

echo -e "\n${BLUE}[5.1] Crontab root...${NC}"
crontab -l 2>/dev/null || echo "Pas de crontab root"

echo -e "\n${BLUE}[5.2] Scheduled tasks Plesk...${NC}"
if [ -d "/var/spool/cron/crontabs" ]; then
    ls -la /var/spool/cron/crontabs/
fi

# Chercher des entrees suspectes
echo -e "\n${BLUE}[5.3] Recherche entrees suspectes...${NC}"
grep -r -E "curl.*\||wget.*\||base64|eval|crypto|miner" /var/spool/cron/ 2>/dev/null || echo "RAS"
grep -r -E "curl.*\||wget.*\||base64|eval|crypto|miner" /etc/cron.d/ 2>/dev/null || echo "RAS"

# ============================================================
# PHASE 6 : SECURISATION PLESK
# ============================================================
echo -e "\n${YELLOW}[PHASE 6] RECOMMANDATIONS SECURITE PLESK${NC}"
echo "------------------------------------------------------------"

echo -e "\n${BLUE}Configuration Firewall Plesk :${NC}"
echo "1. Aller dans Plesk > Tools & Settings > Firewall"
echo "2. Activer le firewall"
echo "3. Bloquer le port 3004 (Docker expose)"
echo "4. Autoriser uniquement 22, 80, 443, 8443 (Plesk)"

echo -e "\n${BLUE}Configuration Fail2ban Plesk :${NC}"
echo "1. Aller dans Plesk > Tools & Settings > IP Address Banning"
echo "2. Activer la protection contre les intrusions"
echo "3. Configurer les jails pour SSH et services web"

echo -e "\n${BLUE}Modsecurity (WAF) :${NC}"
echo "1. Aller dans Plesk > Tools & Settings > Web Application Firewall"
echo "2. Activer ModSecurity"
echo "3. Utiliser le ruleset OWASP"

echo -e "\n${BLUE}Docker via Plesk :${NC}"
echo "1. Aller dans Plesk > Extensions > Docker"
echo "2. Supprimer le container compromis"
echo "3. Reconstruire avec la nouvelle configuration securisee"

# ============================================================
# PHASE 7 : RAPPORT
# ============================================================
echo ""
echo -e "${YELLOW}[PHASE 7] RAPPORT FINAL${NC}"
echo "------------------------------------------------------------"

echo -e "\n${BLUE}Resume :${NC}"
echo "- Log sauvegarde: $LOG_FILE"
echo "- Malware trouve: $FOUND_MALWARE"
echo ""

echo -e "${YELLOW}ACTIONS MANUELLES REQUISES :${NC}"
echo ""
echo "1. [URGENT] Regenerer les cles API OpenAI :"
echo "   https://platform.openai.com/api-keys"
echo ""
echo "2. [URGENT] Changer les secrets dans .env.production :"
echo "   cd $LEHIBOO_BACKEND"
echo "   nano .env.production"
echo "   # Regenerer : JWT_SECRET, API_KEY, POSTGRES_PASSWORD"
echo ""
echo "3. [IMPORTANT] Appliquer le patch EventList :"
echo "   mkdir -p $LEHIBOO_WP/wp-content/mu-plugins/"
echo "   cp scripts/patches/eventlist-ajax-security-patch.php \\"
echo "      $LEHIBOO_WP/wp-content/mu-plugins/"
echo ""
echo "4. [IMPORTANT] Reconstruire le container Docker :"
echo "   cd $LEHIBOO_BACKEND"
echo "   docker-compose -f docker-compose.secure.yml build --no-cache"
echo "   docker-compose -f docker-compose.secure.yml up -d"
echo ""
echo "5. [RECOMMANDE] Configurer le monitoring :"
echo "   sudo cp scripts/security-monitor.sh /usr/local/bin/"
echo "   sudo chmod +x /usr/local/bin/security-monitor.sh"
echo "   sudo crontab -e"
echo "   # Ajouter : */5 * * * * /usr/local/bin/security-monitor.sh"
echo ""
echo -e "${GREEN}============================================================${NC}"
echo -e "${GREEN}Script termine.${NC}"
echo -e "${GREEN}Log sauvegarde: $LOG_FILE${NC}"
echo -e "${GREEN}============================================================${NC}"
