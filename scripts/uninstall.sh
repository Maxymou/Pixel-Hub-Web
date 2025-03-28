#!/bin/bash

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonction pour afficher les messages
print_message() {
    echo -e "${2}${1}${NC}"
}

# Fonction pour vérifier si une commande existe
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Fonction pour arrêter les services
stop_services() {
    print_message "Arrêt des services..." "$YELLOW"
    
    if command_exists systemctl; then
        sudo systemctl stop apache2
        sudo systemctl stop mysql
    else
        sudo service apache2 stop
        sudo service mysql stop
    fi
    
    print_message "Services arrêtés avec succès." "$GREEN"
}

# Fonction pour supprimer l'application
remove_application() {
    print_message "Suppression de l'application..." "$YELLOW"
    
    # Supprimer le dossier de l'application
    sudo rm -rf /var/www/pixel-hub
    
    print_message "Application supprimée avec succès." "$GREEN"
}

# Fonction pour supprimer la base de données
remove_database() {
    print_message "Suppression de la base de données..." "$YELLOW"
    
    if command_exists mysql; then
        sudo mysql -e "DROP DATABASE IF EXISTS pixel_hub; DROP USER IF EXISTS 'pixel_hub'@'localhost';"
    fi
    
    print_message "Base de données supprimée avec succès." "$GREEN"
}

# Fonction pour supprimer les configurations
remove_configurations() {
    print_message "Suppression des configurations..." "$YELLOW"
    
    # Supprimer les configurations Apache
    sudo rm -f /etc/apache2/sites-available/pixel-hub.conf
    
    # Supprimer les configurations PHP
    sudo rm -f /etc/php/8.1/apache2/conf.d/99-pixel-hub.ini
    
    # Supprimer les limites système
    sudo rm -f /etc/security/limits.d/pixel-hub.conf
    
    print_message "Configurations supprimées avec succès." "$GREEN"
}

# Fonction pour désinstaller les paquets
remove_packages() {
    print_message "Désinstallation des paquets..." "$YELLOW"
    
    # Désinstaller les paquets PHP
    sudo apt-get remove -y \
        php8.1 \
        php8.1-cli \
        php8.1-common \
        php8.1-mysql \
        php8.1-zip \
        php8.1-gd \
        php8.1-mbstring \
        php8.1-curl \
        php8.1-xml \
        php8.1-bcmath \
        php8.1-json \
        php8.1-opcache \
        php8.1-intl \
        php8.1-ldap \
        php8.1-redis \
        php8.1-imagick \
        mysql-server \
        apache2
    
    # Supprimer les fichiers de configuration restants
    sudo apt-get purge -y \
        php8.1 \
        php8.1-cli \
        php8.1-common \
        php8.1-mysql \
        php8.1-zip \
        php8.1-gd \
        php8.1-mbstring \
        php8.1-curl \
        php8.1-xml \
        php8.1-bcmath \
        php8.1-json \
        php8.1-opcache \
        php8.1-intl \
        php8.1-ldap \
        php8.1-redis \
        php8.1-imagick \
        mysql-server \
        apache2
    
    # Nettoyer les paquets non utilisés
    sudo apt-get autoremove -y
    sudo apt-get clean
    
    # Supprimer le dépôt PHP
    sudo rm -f /etc/apt/sources.list.d/php.list
    sudo rm -f /etc/apt/trusted.gpg.d/php.gpg
    
    print_message "Paquets désinstallés avec succès." "$GREEN"
}

# Fonction pour vérifier la désinstallation
verify_uninstallation() {
    print_message "Vérification de la désinstallation..." "$YELLOW"
    
    # Vérifier les services
    if command_exists systemctl; then
        if systemctl is-active --quiet apache2; then
            print_message "ATTENTION: Apache est toujours actif" "$RED"
        else
            print_message "Apache est bien arrêté" "$GREEN"
        fi
        
        if systemctl is-active --quiet mysql; then
            print_message "ATTENTION: MySQL est toujours actif" "$RED"
        else
            print_message "MySQL est bien arrêté" "$GREEN"
        fi
    fi
    
    # Vérifier les fichiers
    if [ -d "/var/www/pixel-hub" ]; then
        print_message "ATTENTION: Le dossier de l'application existe toujours" "$RED"
    else
        print_message "Le dossier de l'application a été supprimé" "$GREEN"
    fi
    
    if [ -f "/etc/apache2/sites-available/pixel-hub.conf" ]; then
        print_message "ATTENTION: La configuration Apache existe toujours" "$RED"
    else
        print_message "La configuration Apache a été supprimée" "$GREEN"
    fi
    
    if [ -f "/etc/php/8.1/apache2/conf.d/99-pixel-hub.ini" ]; then
        print_message "ATTENTION: La configuration PHP existe toujours" "$RED"
    else
        print_message "La configuration PHP a été supprimée" "$GREEN"
    fi
}

# Fonction principale
main() {
    print_message "Démarrage de la désinstallation de Pixel Hub Web..." "$YELLOW"
    
    stop_services
    remove_application
    remove_database
    remove_configurations
    remove_packages
    verify_uninstallation
    
    print_message "Désinstallation terminée !" "$GREEN"
    print_message "Si vous voyez des messages d'ATTENTION en rouge, veuillez vérifier manuellement ces éléments." "$YELLOW"
}

# Exécuter la fonction principale
main 