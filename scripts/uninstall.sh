#!/bin/bash

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Fonction pour afficher les messages
print_message() {
    echo -e "${2}${1}${NC}"
}

# Fonction pour demander confirmation
confirm() {
    read -p "Êtes-vous sûr de vouloir continuer ? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
}

# Fonction pour arrêter les services
stop_services() {
    print_message "Arrêt des services..." "$YELLOW"
    
    systemctl stop apache2
    systemctl stop mysql
    
    print_message "Services arrêtés avec succès" "$GREEN"
}

# Fonction pour supprimer les fichiers de configuration
remove_config_files() {
    print_message "Suppression des fichiers de configuration..." "$YELLOW"
    
    # Supprimer la configuration Apache
    rm -f /etc/apache2/sites-available/pixel-hub.conf
    rm -f /etc/apache2/sites-available/pixel-hub-ssl.conf
    rm -f /etc/apache2/sites-enabled/pixel-hub.conf
    rm -f /etc/apache2/sites-enabled/pixel-hub-ssl.conf
    
    # Supprimer la configuration PHP
    rm -f /etc/php/8.1/apache2/conf.d/pixel-hub.ini
    
    # Supprimer les certificats SSL
    rm -rf /etc/ssl/pixel-hub
    
    print_message "Fichiers de configuration supprimés avec succès" "$GREEN"
}

# Fonction pour supprimer la base de données
remove_database() {
    print_message "Suppression de la base de données..." "$YELLOW"
    
    # Supprimer la base de données et l'utilisateur
    mysql -e "DROP DATABASE IF EXISTS pixel_hub;"
    mysql -e "DROP USER IF EXISTS 'pixel_hub'@'localhost';"
    mysql -e "FLUSH PRIVILEGES;"
    
    print_message "Base de données supprimée avec succès" "$GREEN"
}

# Fonction pour supprimer les fichiers de l'application
remove_application_files() {
    print_message "Suppression des fichiers de l'application..." "$YELLOW"
    
    # Supprimer le répertoire de l'application
    rm -rf /var/www/pixel-hub
    
    print_message "Fichiers de l'application supprimés avec succès" "$GREEN"
}

# Fonction pour supprimer les dépendances
remove_dependencies() {
    print_message "Suppression des dépendances..." "$YELLOW"
    
    # Supprimer les paquets PHP
    apt-get remove -y \
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
        php8.1-intl
    
    # Supprimer Composer
    rm -f /usr/local/bin/composer
    
    # Nettoyer les paquets
    apt-get autoremove -y
    apt-get clean
    
    print_message "Dépendances supprimées avec succès" "$GREEN"
}

# Fonction pour restaurer les configurations système
restore_system_config() {
    print_message "Restauration des configurations système..." "$YELLOW"
    
    # Supprimer les limites système
    rm -f /etc/security/limits.d/pixel-hub.conf
    
    # Redémarrer les services
    systemctl restart apache2
    systemctl restart mysql
    
    print_message "Configurations système restaurées avec succès" "$GREEN"
}

# Fonction principale
main() {
    print_message "Démarrage de la désinstallation de Pixel-Hub..." "$YELLOW"
    
    # Vérifier si le script est exécuté en tant que root
    if [ "$EUID" -ne 0 ]; then 
        print_message "Ce script doit être exécuté en tant que root (sudo)" "$RED"
        exit 1
    fi
    
    # Demander confirmation
    print_message "Cette action supprimera définitivement Pixel-Hub et toutes ses données." "$RED"
    confirm
    
    # Arrêter les services
    stop_services
    
    # Supprimer les fichiers de configuration
    remove_config_files
    
    # Supprimer la base de données
    remove_database
    
    # Supprimer les fichiers de l'application
    remove_application_files
    
    # Supprimer les dépendances
    remove_dependencies
    
    # Restaurer les configurations système
    restore_system_config
    
    print_message "Désinstallation terminée avec succès !" "$GREEN"
}

# Exécuter le script
main 