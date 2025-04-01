#!/bin/bash

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Variables pour le rapport
REPORT_FILE="/var/log/lemp-uninstallation-report-$(date +%Y%m%d-%H%M%S).log"
ERRORS=0
WARNINGS=0
SUCCESS=0

# Fonction pour afficher les messages
print_message() {
    echo -e "${GREEN}[INFO]${NC} $1"
    echo "[INFO] $1" >> "$REPORT_FILE"
}

print_error() {
    echo -e "${RED}[ERREUR]${NC} $1"
    echo "[ERREUR] $1" >> "$REPORT_FILE"
    ((ERRORS++))
}

print_warning() {
    echo -e "${YELLOW}[ATTENTION]${NC} $1"
    echo "[ATTENTION] $1" >> "$REPORT_FILE"
    ((WARNINGS++))
}

# Fonction de vérification
check_command() {
    if [ $? -eq 0 ]; then
        print_message "$1"
        ((SUCCESS++))
    else
        print_error "$2"
    fi
}

# Création du fichier de rapport
echo "=== Rapport de désinstallation LEMP - $(date) ===" > "$REPORT_FILE"
echo "Système: $(uname -a)" >> "$REPORT_FILE"
echo "Architecture: $(uname -m)" >> "$REPORT_FILE"
echo "==========================================" >> "$REPORT_FILE"

# Vérification des privilèges root
if [ "$EUID" -ne 0 ]; then 
    print_error "Ce script doit être exécuté en tant que root (sudo)"
    exit 1
fi

# Vérification de l'architecture
if [ "$(uname -m)" != "aarch64" ]; then
    print_error "Ce script est conçu pour Raspberry Pi OS 64 bits uniquement"
    exit 1
fi

# Vérification du mode force
FORCE=false
if [ "$1" = "-f" ] || [ "$1" = "--force" ]; then
    FORCE=true
fi

# Demande de confirmation si pas en mode force
if [ "$FORCE" = false ]; then
    echo -e "${YELLOW}ATTENTION: Ce script va désinstaller l'environnement LEMP complet.${NC}"
    echo -e "${YELLOW}Cela supprimera:${NC}"
    echo -e "- Nginx et sa configuration"
    echo -e "- PHP-FPM et sa configuration"
    echo -e "- MariaDB et toutes les bases de données"
    echo -e "- Les fichiers de configuration"
    echo -e "- Les fichiers de logs"
    echo -e "- Le contenu du répertoire /var/www/html"
    echo -e "\n${RED}Cette action est irréversible!${NC}"
    read -p "Voulez-vous continuer? (o/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Oo]$ ]]; then
        print_message "Désinstallation annulée"
        exit 0
    fi
fi

# 1. Arrêt des services
print_message "Arrêt des services..."
systemctl stop nginx
check_command "Arrêt de Nginx réussi" "Échec de l'arrêt de Nginx"

# Détection de la version de PHP
PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
PHP_FPM_SERVICE="php${PHP_VERSION}-fpm"
systemctl stop ${PHP_FPM_SERVICE}
check_command "Arrêt de PHP-FPM réussi" "Échec de l'arrêt de PHP-FPM"

systemctl stop mariadb
check_command "Arrêt de MariaDB réussi" "Échec de l'arrêt de MariaDB"

# 2. Désinstallation des paquets
print_message "Désinstallation des paquets..."
apt-get remove -y nginx nginx-common nginx-full
check_command "Désinstallation de Nginx réussie" "Échec de la désinstallation de Nginx"

apt-get remove -y php-fpm php-mysql php-mbstring php-xml php-json
check_command "Désinstallation de PHP et modules réussie" "Échec de la désinstallation de PHP"

apt-get remove -y mariadb-server mariadb-client
check_command "Désinstallation de MariaDB réussie" "Échec de la désinstallation de MariaDB"

# 3. Suppression des fichiers de configuration
print_message "Suppression des fichiers de configuration..."
rm -rf /etc/nginx
check_command "Suppression de la configuration Nginx réussie" "Échec de la suppression de la configuration Nginx"

rm -rf /etc/php
check_command "Suppression de la configuration PHP réussie" "Échec de la suppression de la configuration PHP"

rm -rf /etc/mysql
check_command "Suppression de la configuration MariaDB réussie" "Échec de la suppression de la configuration MariaDB"

# 4. Suppression des fichiers de logs
print_message "Suppression des fichiers de logs..."
rm -rf /var/log/nginx
check_command "Suppression des logs Nginx réussie" "Échec de la suppression des logs Nginx"

rm -rf /var/log/php*
check_command "Suppression des logs PHP réussie" "Échec de la suppression des logs PHP"

rm -rf /var/log/mysql
check_command "Suppression des logs MariaDB réussie" "Échec de la suppression des logs MariaDB"

# 5. Suppression des fichiers de données
print_message "Suppression des fichiers de données..."
rm -rf /var/www/html/*
check_command "Suppression des fichiers web réussie" "Échec de la suppression des fichiers web"

rm -rf /var/lib/mysql
check_command "Suppression des données MariaDB réussie" "Échec de la suppression des données MariaDB"

# 6. Nettoyage des paquets
print_message "Nettoyage des paquets..."
apt-get autoremove -y
check_command "Nettoyage des paquets réussie" "Échec du nettoyage des paquets"

apt-get clean
check_command "Nettoyage du cache apt réussie" "Échec du nettoyage du cache apt"

# Génération du rapport final
echo "==========================================" >> "$REPORT_FILE"
echo "=== Résumé de la désinstallation ===" >> "$REPORT_FILE"
echo "Succès: $SUCCESS" >> "$REPORT_FILE"
echo "Avertissements: $WARNINGS" >> "$REPORT_FILE"
echo "Erreurs: $ERRORS" >> "$REPORT_FILE"
echo "==========================================" >> "$REPORT_FILE"

# Affichage du résumé
echo -e "\n${GREEN}=== Résumé de la désinstallation ===${NC}"
echo -e "Succès: ${GREEN}$SUCCESS${NC}"
echo -e "Avertissements: ${YELLOW}$WARNINGS${NC}"
echo -e "Erreurs: ${RED}$ERRORS${NC}"
echo -e "\nLe rapport complet a été généré dans: ${GREEN}$REPORT_FILE${NC}"

if [ $ERRORS -gt 0 ]; then
    print_warning "La désinstallation est terminée avec des erreurs. Veuillez consulter le rapport pour plus de détails."
    exit 1
else
    print_message "Désinstallation terminée avec succès!"
fi 