#!/bin/bash
# ============================================================
# Le Hiboo - Script de Monitoring Post-Incident
# A executer via cron toutes les 5 minutes
# ============================================================

# Configuration
LOG_DIR="/var/log/lehiboo-security"
ALERT_EMAIL="contact@dilios.fr"
MAX_CPU_THRESHOLD=80
SUSPICIOUS_PATTERNS="ePXKKgcl|Wxr5Gtq5|nRvxOjA|BYcNW|xmrig|minerd|cryptominer|kdevtmpfsi|kinsing"

# Chemins Le Hiboo
LEHIBOO_WP="/var/www/vhosts/lehiboo.com/preprod.lehiboo.com"
LEHIBOO_BACKEND="$LEHIBOO_WP/lehiboo-ai-backend"

# Creer le dossier de logs si necessaire
mkdir -p "$LOG_DIR"

# Fonction d'alerte
send_alert() {
    local message="$1"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$timestamp] ALERTE: $message" >> "$LOG_DIR/alerts.log"

    # Envoyer email si configure
    if command -v mail &> /dev/null && [ -n "$ALERT_EMAIL" ]; then
        echo "$message" | mail -s "[LEHIBOO SECURITY] Alerte detectee" "$ALERT_EMAIL"
    fi

    # Envoyer vers syslog
    logger -t "lehiboo-security" "ALERT: $message"
}

# 1. Verifier les processus suspects
check_suspicious_processes() {
    local suspicious=$(ps aux | grep -E "$SUSPICIOUS_PATTERNS" | grep -v grep)
    if [ -n "$suspicious" ]; then
        send_alert "Processus suspect detecte: $suspicious"
        # Option : tuer automatiquement
        # pkill -9 -f "$SUSPICIOUS_PATTERNS"
        return 1
    fi
    return 0
}

# 2. Verifier la charge CPU anormale
check_cpu_usage() {
    # Processus avec plus de X% CPU
    local high_cpu=$(ps aux --sort=-%cpu | awk -v threshold=$MAX_CPU_THRESHOLD 'NR>1 && $3>threshold {print $0}' | head -5)
    if [ -n "$high_cpu" ]; then
        # Verifier si c'est un processus legitime
        if echo "$high_cpu" | grep -qE "$SUSPICIOUS_PATTERNS"; then
            send_alert "Processus haute CPU suspect: $high_cpu"
            return 1
        fi
    fi
    return 0
}

# 3. Verifier les connexions reseau suspectes
check_network_connections() {
    # Connexions vers des ports de mining connus (3333, 4444, 5555, 7777, 8888, 9999, 14444)
    local mining_ports=$(netstat -tulpn 2>/dev/null | grep -E ":3333|:4444|:5555|:7777|:8888|:9999|:14444" || ss -tulpn 2>/dev/null | grep -E ":3333|:4444|:5555|:7777|:8888|:9999|:14444")
    if [ -n "$mining_ports" ]; then
        send_alert "Connexion vers port de mining detectee: $mining_ports"
        return 1
    fi
    return 0
}

# 4. Verifier les nouveaux fichiers executables dans /tmp
check_temp_executables() {
    local new_exec=$(find /tmp /var/tmp -type f -perm +x -mmin -10 2>/dev/null)
    if [ -n "$new_exec" ]; then
        send_alert "Nouveau fichier executable dans /tmp: $new_exec"
        return 1
    fi
    return 0
}

# 5. Verifier les crontabs modifiees
check_crontab_changes() {
    local cron_hash_file="$LOG_DIR/.crontab_hash"
    local current_hash=$(crontab -l 2>/dev/null | md5sum | cut -d' ' -f1)

    if [ -f "$cron_hash_file" ]; then
        local stored_hash=$(cat "$cron_hash_file")
        if [ "$current_hash" != "$stored_hash" ]; then
            send_alert "Crontab modifiee detectee!"
            echo "$current_hash" > "$cron_hash_file"
            return 1
        fi
    else
        echo "$current_hash" > "$cron_hash_file"
    fi
    return 0
}

# 6. Verifier les fichiers PHP modifies recemment
check_php_modifications() {
    if [ -d "$LEHIBOO_WP" ]; then
        local modified_php=$(find "$LEHIBOO_WP" -name "*.php" -mmin -5 -type f 2>/dev/null | head -10)
        if [ -n "$modified_php" ]; then
            # Verifier si c'est une modification suspecte (eval, base64, etc.)
            for file in $modified_php; do
                if grep -qE "eval\s*\(|base64_decode|gzinflate|str_rot13" "$file" 2>/dev/null; then
                    send_alert "Fichier PHP suspect modifie: $file"
                fi
            done
        fi

        # Verifier les uploads pour fichiers PHP (ne devrait jamais exister)
        local uploads_php=$(find "$LEHIBOO_WP/wp-content/uploads" -name "*.php" -type f 2>/dev/null)
        if [ -n "$uploads_php" ]; then
            send_alert "Fichier PHP dans uploads detecte: $uploads_php"
            return 1
        fi
    fi
    return 0
}

# 7. Verifier les containers Docker
check_docker_containers() {
    if command -v docker &> /dev/null; then
        # Verifier les processus dans les containers
        for container in $(docker ps -q 2>/dev/null); do
            local container_procs=$(docker exec "$container" ps aux 2>/dev/null | grep -E "$SUSPICIOUS_PATTERNS" || true)
            if [ -n "$container_procs" ]; then
                local container_name=$(docker inspect --format='{{.Name}}' "$container")
                send_alert "Processus suspect dans container $container_name: $container_procs"
            fi
        done
    fi
    return 0
}

# ============================================================
# EXECUTION PRINCIPALE
# ============================================================

TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
echo "[$TIMESTAMP] Scan de securite demarre" >> "$LOG_DIR/monitor.log"

ISSUES_FOUND=0

check_suspicious_processes || ((ISSUES_FOUND++))
check_cpu_usage || ((ISSUES_FOUND++))
check_network_connections || ((ISSUES_FOUND++))
check_temp_executables || ((ISSUES_FOUND++))
check_crontab_changes || ((ISSUES_FOUND++))
check_php_modifications || ((ISSUES_FOUND++))
check_docker_containers || ((ISSUES_FOUND++))

if [ $ISSUES_FOUND -gt 0 ]; then
    echo "[$TIMESTAMP] Scan termine - $ISSUES_FOUND probleme(s) detecte(s)" >> "$LOG_DIR/monitor.log"
else
    echo "[$TIMESTAMP] Scan termine - RAS" >> "$LOG_DIR/monitor.log"
fi

exit $ISSUES_FOUND
