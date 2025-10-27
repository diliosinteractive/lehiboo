#!/bin/bash

###############################################################################
# Installation Script - Le Hiboo AI Assistant
#
# Ce script automatise l'installation complète du système:
# - Backend Node.js
# - WordPress Plugin
# - Configuration .env
# - Tests de connexion
#
# Usage: ./install.sh
###############################################################################

set -e  # Exit on error

# Couleurs pour output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonctions utilitaires
print_header() {
    echo ""
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

# Vérifier prérequis
check_prerequisites() {
    print_header "Vérification des Prérequis"

    # Node.js
    if command -v node &> /dev/null; then
        NODE_VERSION=$(node -v)
        print_success "Node.js installé: $NODE_VERSION"

        # Vérifier version >= 18
        MAJOR_VERSION=$(echo $NODE_VERSION | cut -d'v' -f2 | cut -d'.' -f1)
        if [ "$MAJOR_VERSION" -lt 18 ]; then
            print_error "Node.js 18+ requis. Version actuelle: $NODE_VERSION"
            print_info "Télécharger: https://nodejs.org"
            exit 1
        fi
    else
        print_error "Node.js non installé"
        print_info "Télécharger: https://nodejs.org"
        exit 1
    fi

    # npm
    if command -v npm &> /dev/null; then
        NPM_VERSION=$(npm -v)
        print_success "npm installé: v$NPM_VERSION"
    else
        print_error "npm non installé"
        exit 1
    fi

    # Git
    if command -v git &> /dev/null; then
        GIT_VERSION=$(git --version)
        print_success "Git installé: $GIT_VERSION"
    else
        print_warning "Git non installé (optionnel)"
    fi

    # PHP (pour WordPress)
    if command -v php &> /dev/null; then
        PHP_VERSION=$(php -v | head -n 1)
        print_success "PHP installé: $PHP_VERSION"
    else
        print_warning "PHP non détecté (requis pour WordPress)"
    fi

    echo ""
}

# Installer Backend Node.js
install_backend() {
    print_header "Installation Backend Node.js"

    if [ ! -d "lehiboo-ai-backend" ]; then
        print_error "Dossier lehiboo-ai-backend introuvable"
        print_info "Assurez-vous d'exécuter ce script depuis la racine du projet"
        exit 1
    fi

    cd lehiboo-ai-backend

    # Installer dépendances
    print_info "Installation des dépendances npm..."
    npm install

    if [ $? -eq 0 ]; then
        print_success "Dépendances installées"
    else
        print_error "Échec installation dépendances"
        exit 1
    fi

    # Configuration .env
    if [ ! -f ".env" ]; then
        print_info "Création du fichier .env..."
        cp .env.example .env
        print_success "Fichier .env créé"
        print_warning "IMPORTANT: Éditez .env avec vos clés API"
        print_info "  - OPENROUTER_API_KEY"
        print_info "  - WEATHER_API_KEY"
        print_info "  - API_KEY"

        # Ouvrir .env dans l'éditeur par défaut
        if [[ "$OSTYPE" == "darwin"* ]]; then
            open .env
        elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
            xdg-open .env 2>/dev/null || nano .env
        fi
    else
        print_warning "Fichier .env existe déjà, non modifié"
    fi

    cd ..
    echo ""
}

# Configuration WordPress
configure_wordpress() {
    print_header "Configuration WordPress Plugin"

    PLUGIN_PATH="wp-content/plugins/lehiboo-ai-assistant"

    if [ ! -d "$PLUGIN_PATH" ]; then
        print_error "Plugin WordPress introuvable: $PLUGIN_PATH"
        print_info "Vérifiez le chemin du plugin"
        return 1
    fi

    print_success "Plugin WordPress détecté"
    print_info "Activez le plugin dans WP Admin → Plugins"
    print_info "Configurez dans WP Admin → Le Hiboo → Assistant IA"
    echo ""
}

# Configuration EventList API
configure_eventlist() {
    print_header "Configuration EventList REST API"

    EVENTLIST_PATH="wp-content/plugins/eventlist"

    if [ ! -d "$EVENTLIST_PATH" ]; then
        print_warning "Plugin EventList non détecté"
        print_info "Installez EventList si ce n'est pas déjà fait"
        return 1
    fi

    API_FILE="$EVENTLIST_PATH/includes/class-eventlist-rest-api.php"

    if [ -f "$API_FILE" ]; then
        print_success "API EventList configurée"
    else
        print_warning "Fichier API REST manquant"
        print_info "Copiez class-eventlist-rest-api.php dans eventlist/includes/"
    fi

    echo ""
}

# Tester connexions
test_connections() {
    print_header "Tests de Connexion"

    cd lehiboo-ai-backend

    # Vérifier .env existe
    if [ ! -f ".env" ]; then
        print_error "Fichier .env non trouvé"
        print_info "Créez .env avec vos clés API"
        cd ..
        return 1
    fi

    # Source .env pour avoir les variables
    set -a
    source .env
    set +a

    # Test OpenRouter
    if [ -n "$OPENROUTER_API_KEY" ] && [ "$OPENROUTER_API_KEY" != "your-openrouter-api-key-here" ]; then
        print_info "Test connexion OpenRouter..."

        RESPONSE=$(curl -s -w "\n%{http_code}" -H "Authorization: Bearer $OPENROUTER_API_KEY" \
            https://openrouter.ai/api/v1/models)

        HTTP_CODE=$(echo "$RESPONSE" | tail -n1)

        if [ "$HTTP_CODE" == "200" ]; then
            print_success "OpenRouter connecté"
        else
            print_error "OpenRouter non connecté (HTTP $HTTP_CODE)"
            print_info "Vérifiez votre OPENROUTER_API_KEY dans .env"
        fi
    else
        print_warning "OPENROUTER_API_KEY non configurée"
    fi

    # Test Weather API
    if [ -n "$WEATHER_API_KEY" ] && [ "$WEATHER_API_KEY" != "your-openweathermap-api-key-here" ]; then
        print_info "Test connexion OpenWeather..."

        RESPONSE=$(curl -s -w "\n%{http_code}" \
            "https://api.openweathermap.org/data/2.5/weather?q=Paris&appid=$WEATHER_API_KEY")

        HTTP_CODE=$(echo "$RESPONSE" | tail -n1)

        if [ "$HTTP_CODE" == "200" ]; then
            print_success "OpenWeather connecté"
        else
            print_error "OpenWeather non connecté (HTTP $HTTP_CODE)"
            print_info "Vérifiez votre WEATHER_API_KEY dans .env"
        fi
    else
        print_warning "WEATHER_API_KEY non configurée"
    fi

    # Test WordPress API
    if [ -n "$WORDPRESS_API_URL" ]; then
        print_info "Test connexion WordPress API..."

        RESPONSE=$(curl -s -w "\n%{http_code}" "$WORDPRESS_API_URL/eventlist/v1/events?per_page=1")
        HTTP_CODE=$(echo "$RESPONSE" | tail -n1)

        if [ "$HTTP_CODE" == "200" ]; then
            print_success "WordPress EventList API accessible"
        else
            print_warning "WordPress EventList API non accessible (HTTP $HTTP_CODE)"
            print_info "Assurez-vous que l'API REST EventList est activée"
        fi
    else
        print_warning "WORDPRESS_API_URL non configurée"
    fi

    cd ..
    echo ""
}

# Démarrer backend
start_backend() {
    print_header "Démarrage Backend"

    print_info "Voulez-vous démarrer le backend maintenant ? (y/N)"
    read -r START_BACKEND

    if [[ "$START_BACKEND" =~ ^[Yy]$ ]]; then
        cd lehiboo-ai-backend

        print_info "Démarrage du serveur sur http://localhost:3000"
        print_info "Appuyez sur Ctrl+C pour arrêter"
        echo ""

        npm run dev
    else
        print_info "Backend non démarré"
        print_info "Pour démarrer manuellement:"
        print_info "  cd lehiboo-ai-backend"
        print_info "  npm run dev"
    fi

    echo ""
}

# Afficher résumé
print_summary() {
    print_header "Installation Terminée !"

    echo "📋 Prochaines étapes:"
    echo ""
    echo "1. ${GREEN}Configurer les clés API${NC}"
    echo "   Éditez: lehiboo-ai-backend/.env"
    echo "   - OPENROUTER_API_KEY (https://openrouter.ai/keys)"
    echo "   - WEATHER_API_KEY (https://openweathermap.org/api)"
    echo "   - API_KEY (créez une clé secrète)"
    echo ""
    echo "2. ${GREEN}Démarrer le backend${NC}"
    echo "   cd lehiboo-ai-backend"
    echo "   npm run dev"
    echo ""
    echo "3. ${GREEN}Activer le plugin WordPress${NC}"
    echo "   WP Admin → Plugins → Le Hiboo AI Assistant"
    echo ""
    echo "4. ${GREEN}Configurer WordPress${NC}"
    echo "   WP Admin → Le Hiboo → Assistant IA → Paramètres"
    echo "   - URL Backend: http://localhost:3000"
    echo "   - Clé API: [même que .env API_KEY]"
    echo ""
    echo "5. ${GREEN}Tester${NC}"
    echo "   Ouvrez le frontend et testez le chat"
    echo ""
    echo "📚 Documentation:"
    echo "   - Guide rapide: lehiboo-ai-backend/QUICK_START.md"
    echo "   - Tests E2E: INTEGRATION_TESTING.md"
    echo "   - Déploiement: lehiboo-ai-backend/DEPLOYMENT_GUIDE.md"
    echo ""
    print_success "Installation réussie !"
    echo ""
}

# Main script
main() {
    clear

    cat << "EOF"

    ╔═══════════════════════════════════════════════════════╗
    ║                                                       ║
    ║     🦉 LE HIBOO AI ASSISTANT - INSTALLATION 🦉       ║
    ║                                                       ║
    ║     Assistant IA Conversationnel pour WordPress      ║
    ║                  Version 1.0.0                        ║
    ║                                                       ║
    ╚═══════════════════════════════════════════════════════╝

EOF

    echo "Ce script va installer et configurer Le Hiboo AI Assistant."
    echo ""
    print_warning "Assurez-vous d'exécuter ce script depuis la racine du projet"
    echo ""
    print_info "Appuyez sur Entrée pour continuer (Ctrl+C pour annuler)"
    read -r

    # Exécution
    check_prerequisites
    install_backend
    configure_wordpress
    configure_eventlist
    test_connections
    print_summary
    start_backend
}

# Lancer le script
main
