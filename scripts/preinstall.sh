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

# Fonction pour installer les paquets système
install_system_packages() {
    print_message "Installation des paquets système..." "$YELLOW"
    
    # Mettre à jour les paquets
    sudo apt-get update
    sudo apt-get upgrade -y
    
    # Installer les paquets essentiels
    sudo apt-get install -y \
        software-properties-common \
        apt-transport-https \
        ca-certificates \
        curl \
        git \
        unzip \
        wget \
        build-essential \
        libssl-dev \
        libffi-dev \
        python3-dev \
        python3-pip
    
    print_message "Paquets système installés avec succès." "$GREEN"
}

# Fonction pour installer PHP 8.1
install_php() {
    print_message "Installation de PHP 8.1..." "$YELLOW"
    
    # Ajouter le dépôt PHP
    sudo add-apt-repository ppa:ondrej/php -y
    sudo apt-get update
    
    # Installer PHP 8.1 et ses extensions
    sudo apt-get install -y \
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
        php8.1-imagick
    
    print_message "PHP 8.1 installé avec succès." "$GREEN"
}

# Fonction pour installer MySQL
install_mysql() {
    print_message "Installation de MySQL..." "$YELLOW"
    
    # Installer MySQL
    sudo apt-get install -y mysql-server
    
    # Démarrer MySQL
    sudo systemctl start mysql
    sudo systemctl enable mysql
    
    # Sécuriser l'installation MySQL
    sudo mysql_secure_installation
    
    print_message "MySQL installé avec succès." "$GREEN"
}

# Fonction pour installer Apache
install_apache() {
    print_message "Installation d'Apache..." "$YELLOW"
    
    # Installer Apache
    sudo apt-get install -y apache2
    
    # Démarrer Apache
    sudo systemctl start apache2
    sudo systemctl enable apache2
    
    print_message "Apache installé avec succès." "$GREEN"
}

# Fonction pour installer Composer
install_composer() {
    print_message "Installation de Composer..." "$YELLOW"
    
    # Télécharger l'installateur Composer
    curl -sS https://getcomposer.org/installer -o composer-setup.php
    
    # Vérifier l'intégrité de l'installateur
    HASH=$(curl -sS https://composer.github.io/installer.sig)
    php -r "if (hash_file('SHA384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    
    # Installer Composer
    sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    
    # Nettoyer
    rm composer-setup.php
    
    print_message "Composer installé avec succès." "$GREEN"
}

# Fonction pour configurer le fuseau horaire
configure_timezone() {
    print_message "Configuration du fuseau horaire..." "$YELLOW"
    
    # Définir le fuseau horaire
    sudo timedatectl set-timezone Europe/Paris
    
    print_message "Fuseau horaire configuré avec succès." "$GREEN"
}

# Fonction pour configurer les limites système
configure_system_limits() {
    print_message "Configuration des limites système..." "$YELLOW"
    
    # Créer le fichier de configuration
    sudo tee /etc/security/limits.d/pixel-hub.conf > /dev/null << EOL
www-data soft nofile 65535
www-data hard nofile 65535
www-data soft nproc 65535
www-data hard nproc 65535
EOL
    
    print_message "Limites système configurées avec succès." "$GREEN"
}

# Fonction principale
main() {
    print_message "Démarrage de la préinstallation..." "$YELLOW"
    
    install_system_packages
    install_php
    install_mysql
    install_apache
    install_composer
    configure_timezone
    configure_system_limits
    
    print_message "Préinstallation terminée avec succès !" "$GREEN"
    print_message "Vous pouvez maintenant exécuter le script d'installation principal." "$GREEN"
}

# Exécuter la fonction principale
main 