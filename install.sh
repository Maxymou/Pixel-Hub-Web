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

# Fonction pour vérifier les prérequis
check_prerequisites() {
    print_message "Vérification des prérequis..." "$YELLOW"
    
    # Vérifier PHP
    if ! command_exists php; then
        print_message "PHP n'est pas installé. Veuillez installer PHP 8.1 ou supérieur." "$RED"
        exit 1
    fi
    
    # Vérifier la version de PHP
    PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1)
    if [ "$PHP_VERSION" -lt 8 ]; then
        print_message "PHP 8.1 ou supérieur est requis. Version actuelle : $PHP_VERSION" "$RED"
        exit 1
    fi
    
    # Vérifier MySQL
    if ! command_exists mysql; then
        print_message "MySQL n'est pas installé. Veuillez installer MySQL 5.7 ou supérieur." "$RED"
        exit 1
    fi
    
    # Vérifier Apache
    if ! command_exists apache2ctl; then
        print_message "Apache n'est pas installé. Veuillez installer Apache 2.4 ou supérieur." "$RED"
        exit 1
    fi
    
    # Vérifier Composer
    if ! command_exists composer; then
        print_message "Composer n'est pas installé. Veuillez installer Composer." "$RED"
        exit 1
    fi
    
    print_message "Tous les prérequis sont satisfaits." "$GREEN"
}

# Fonction pour installer les dépendances PHP
install_php_dependencies() {
    print_message "Installation des dépendances PHP..." "$YELLOW"
    
    # Installer les extensions PHP requises
    sudo apt-get update
    sudo apt-get install -y \
        php8.1-mysql \
        php8.1-curl \
        php8.1-gd \
        php8.1-mbstring \
        php8.1-xml \
        php8.1-zip \
        php8.1-bcmath \
        php8.1-intl \
        php8.1-opcache \
        php8.1-ldap \
        php8.1-redis \
        php8.1-imagick
    
    print_message "Dépendances PHP installées avec succès." "$GREEN"
}

# Fonction pour configurer Apache
configure_apache() {
    print_message "Configuration d'Apache..." "$YELLOW"
    
    # Activer les modules Apache nécessaires
    sudo a2enmod rewrite
    sudo a2enmod ssl
    sudo a2enmod headers
    
    # Créer la configuration Apache
    sudo tee /etc/apache2/sites-available/pixel-hub.conf > /dev/null << EOL
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/pixel-hub/public
    
    <Directory /var/www/pixel-hub/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/pixel-hub-error.log
    CustomLog \${APACHE_LOG_DIR}/pixel-hub-access.log combined
</VirtualHost>
EOL
    
    # Activer le site
    sudo a2ensite pixel-hub.conf
    
    # Désactiver le site par défaut
    sudo a2dissite 000-default.conf
    
    print_message "Apache configuré avec succès." "$GREEN"
}

# Fonction pour configurer PHP
configure_php() {
    print_message "Configuration de PHP..." "$YELLOW"
    
    # Optimiser les paramètres PHP
    sudo tee /etc/php/8.1/apache2/conf.d/99-pixel-hub.ini > /dev/null << EOL
memory_limit = 256M
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
max_input_time = 300
date.timezone = Europe/Paris
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 60
opcache.fast_shutdown = 1
opcache.enable_cli = 0
EOL
    
    print_message "PHP configuré avec succès." "$GREEN"
}

# Fonction pour configurer MySQL
configure_mysql() {
    print_message "Configuration de MySQL..." "$YELLOW"
    
    # Créer la base de données et l'utilisateur
    read -p "Entrez le mot de passe MySQL root : " MYSQL_ROOT_PASSWORD
    read -p "Entrez le mot de passe pour l'utilisateur pixel_hub : " MYSQL_PASSWORD
    
    mysql -u root -p"$MYSQL_ROOT_PASSWORD" << MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS pixel_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'pixel_hub'@'localhost' IDENTIFIED BY '$MYSQL_PASSWORD';
GRANT ALL PRIVILEGES ON pixel_hub.* TO 'pixel_hub'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT
    
    # Mettre à jour le fichier .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$MYSQL_PASSWORD/" .env
    
    print_message "MySQL configuré avec succès." "$GREEN"
}

# Fonction pour installer les dépendances Composer
install_composer_dependencies() {
    print_message "Installation des dépendances Composer..." "$YELLOW"
    
    composer install --no-dev --optimize-autoloader
    
    print_message "Dépendances Composer installées avec succès." "$GREEN"
}

# Fonction pour configurer les permissions
configure_permissions() {
    print_message "Configuration des permissions..." "$YELLOW"
    
    # Définir les permissions des dossiers
    sudo chown -R www-data:www-data .
    sudo chmod -R 755 .
    sudo chmod -R 775 storage bootstrap/cache
    sudo chmod -R 775 public/uploads
    
    print_message "Permissions configurées avec succès." "$GREEN"
}

# Fonction pour générer la clé d'application
generate_app_key() {
    print_message "Génération de la clé d'application..." "$YELLOW"
    
    php bin/console key:generate
    
    print_message "Clé d'application générée avec succès." "$GREEN"
}

# Fonction pour exécuter les migrations
run_migrations() {
    print_message "Exécution des migrations..." "$YELLOW"
    
    php bin/console migrate
    
    print_message "Migrations exécutées avec succès." "$GREEN"
}

# Fonction pour créer l'utilisateur administrateur
create_admin_user() {
    print_message "Création de l'utilisateur administrateur..." "$YELLOW"
    
    read -p "Entrez le nom d'utilisateur admin : " ADMIN_USERNAME
    read -s -p "Entrez le mot de passe admin : " ADMIN_PASSWORD
    echo
    
    php bin/console user:create "$ADMIN_USERNAME" "$ADMIN_PASSWORD" --admin
    
    print_message "Utilisateur administrateur créé avec succès." "$GREEN"
}

# Fonction pour redémarrer les services
restart_services() {
    print_message "Redémarrage des services..." "$YELLOW"
    
    sudo systemctl restart apache2
    sudo systemctl restart mysql
    
    print_message "Services redémarrés avec succès." "$GREEN"
}

# Fonction principale
main() {
    print_message "Démarrage de l'installation de Pixel Hub Web..." "$YELLOW"
    
    check_prerequisites
    install_php_dependencies
    configure_apache
    configure_php
    configure_mysql
    install_composer_dependencies
    configure_permissions
    generate_app_key
    run_migrations
    create_admin_user
    restart_services
    
    print_message "Installation terminée avec succès !" "$GREEN"
    print_message "Vous pouvez maintenant accéder à Pixel Hub Web à l'adresse : http://localhost" "$GREEN"
}

# Exécuter la fonction principale
main 