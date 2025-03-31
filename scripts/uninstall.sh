#!/bin/bash

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonction pour afficher les messages
print_message() {
    echo -e "${GREEN}[INFO] $1${NC}"
}

print_error() {
    echo -e "${RED}[ERREUR] $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}[ATTENTION] $1${NC}"
}

# Fonction pour vérifier si une commande a réussi
check_command() {
    if [ $? -eq 0 ]; then
        print_message "$1"
    else
        print_error "$2"
    fi
}

# Fonction pour vérifier si une commande existe
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Fonction pour arrêter les services
stop_services() {
    print_message "Arrêt des services..."
    
    if command_exists systemctl; then
        sudo systemctl stop apache2
        sudo systemctl stop mysql
    else
        sudo service apache2 stop
        sudo service mysql stop
    fi
    
    check_command "Services arrêtés avec succès." "Erreur lors de l'arrêt des services."
}

# Fonction pour supprimer l'application
remove_application() {
    print_message "Suppression de l'application..."
    
    # Supprimer le dossier de l'application
    sudo rm -rf /var/www/pixel-hub-web
    
    check_command "Application supprimée avec succès." "Erreur lors de la suppression de l'application."
}

# Fonction pour supprimer la base de données
remove_database() {
    print_message "Suppression de la base de données..."
    
    if command_exists mysql; then
        sudo mysql -e "DROP DATABASE IF EXISTS pixel_hub;"
        sudo mysql -e "DROP USER IF EXISTS 'pixel_hub'@'localhost';"
        sudo mysql -e "FLUSH PRIVILEGES;"
    fi
    
    check_command "Base de données supprimée avec succès." "Erreur lors de la suppression de la base de données."
}

# Fonction pour supprimer les configurations
remove_configurations() {
    print_message "Suppression des configurations..."
    
    # Supprimer les configurations Apache
    sudo rm -f /etc/apache2/sites-available/pixel-hub.conf
    
    # Supprimer les configurations PHP
    sudo rm -f /etc/php/conf.d/99-pixel-hub.ini
    
    # Supprimer les limites système
    sudo rm -f /etc/security/limits.d/pixel-hub.conf
    
    check_command "Configurations supprimées avec succès." "Erreur lors de la suppression des configurations."
}

# Fonction pour désinstaller les paquets
remove_packages() {
    print_message "Désinstallation des paquets..."
    
    # Désinstaller les paquets PHP
    sudo apt remove -y apache2 php8.2* mysql-server
    
    # Nettoyer les paquets non utilisés
    sudo apt autoremove -y
    sudo apt clean
    
    # Supprimer le dépôt PHP
    sudo rm -f /etc/apt/sources.list.d/php.list
    sudo rm -f /etc/apt/trusted.gpg.d/php.gpg
    
    check_command "Paquets désinstallés avec succès." "Erreur lors de la désinstallation des paquets."
}

# Fonction pour vérifier la désinstallation
verify_uninstallation() {
    print_message "Vérification de la désinstallation..."
    
    # Vérifier les services
    if command_exists systemctl; then
        if ! systemctl is-active --quiet apache2; then
            print_message "Apache est bien arrêté"
        else
            print_warning "Apache n'est pas arrêté"
        fi
        
        if ! systemctl is-active --quiet mysql; then
            print_message "MySQL est bien arrêté"
        else
            print_warning "MySQL n'est pas arrêté"
        fi
    fi
    
    # Vérifier les fichiers
    if [ ! -d "/var/www/pixel-hub-web" ]; then
        print_message "Le dossier de l'application a été supprimé"
    else
        print_warning "Le dossier de l'application n'a pas été supprimé"
    fi
    
    if [ ! -f "/etc/apache2/sites-available/pixel-hub.conf" ]; then
        print_message "La configuration Apache a été supprimée"
    else
        print_warning "La configuration Apache n'a pas été supprimée"
    fi
    
    if [ ! -f "/etc/php/conf.d/99-pixel-hub.ini" ]; then
        print_message "La configuration PHP a été supprimée"
    else
        print_warning "La configuration PHP n'a pas été supprimée"
    fi
}

# Fonction principale
main() {
    print_message "Démarrage de la désinstallation de Pixel Hub Web..."
    
    stop_services
    remove_application
    remove_database
    remove_configurations
    remove_packages
    verify_uninstallation
    
    print_message "Désinstallation terminée !"
    print_warning "Si vous voyez des messages d'ATTENTION en rouge, veuillez vérifier manuellement ces éléments."
}

# Exécuter la fonction principale
main 