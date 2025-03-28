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

# Fonction pour vérifier si on est sur un Raspberry Pi
is_raspberry_pi() {
    if [ -f /proc/cpuinfo ]; then
        if grep -q "Raspberry Pi" /proc/cpuinfo; then
            return 0
        fi
    fi
    return 1
}

# Fonction pour vérifier si une commande existe
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Fonction pour installer les prérequis
install_prerequisites() {
    print_message "Installation des prérequis..." "$YELLOW"
    
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
    
    # Ajouter le dépôt PHP
    if is_raspberry_pi; then
        # Pour Raspberry Pi, on utilise le dépôt Sury
        curl -sSL https://packages.sury.org/php/apt.gpg -o /etc/apt/trusted.gpg.d/php.gpg
        echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/php.list
    else
        sudo add-apt-repository ppa:ondrej/php -y
    fi
    
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
    
    # Installer MySQL
    sudo apt-get install -y mysql-server
    sudo systemctl start mysql
    sudo systemctl enable mysql
    
    # Installer Apache
    sudo apt-get install -y apache2
    sudo systemctl start apache2
    sudo systemctl enable apache2
    
    # Installer Composer
    curl -sS https://getcomposer.org/installer -o composer-setup.php
    HASH=$(curl -sS https://composer.github.io/installer.sig)
    php -r "if (hash_file('SHA384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm composer-setup.php
    
    print_message "Prérequis installés avec succès." "$GREEN"
}

# Fonction pour configurer l'environnement
configure_environment() {
    print_message "Configuration de l'environnement..." "$YELLOW"
    
    # Configurer le fuseau horaire
    sudo timedatectl set-timezone Europe/Paris
    
    # Configurer les limites système
    sudo tee /etc/security/limits.d/pixel-hub.conf > /dev/null << EOL
www-data soft nofile 65535
www-data hard nofile 65535
www-data soft nproc 65535
www-data hard nproc 65535
EOL
    
    # Configurer Apache
    sudo a2enmod rewrite
    sudo a2enmod ssl
    sudo a2enmod headers
    
    # Configurer PHP avec des paramètres optimisés pour Raspberry Pi
    if is_raspberry_pi; then
        # Optimisations spécifiques pour Raspberry Pi
        sudo tee /etc/php/8.1/apache2/conf.d/99-pixel-hub.ini > /dev/null << EOL
memory_limit = 128M
upload_max_filesize = 32M
post_max_size = 32M
max_execution_time = 300
max_input_time = 300
date.timezone = Europe/Paris
opcache.enable = 1
opcache.memory_consumption = 64
opcache.interned_strings_buffer = 4
opcache.max_accelerated_files = 2000
opcache.revalidate_freq = 60
opcache.fast_shutdown = 1
opcache.enable_cli = 0
EOL
    else
        # Configuration standard pour autres systèmes
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
    fi
    
    print_message "Environnement configuré avec succès." "$GREEN"
}

# Fonction pour installer l'application
install_application() {
    print_message "Installation de l'application..." "$YELLOW"
    
    # Créer le répertoire de l'application
    sudo mkdir -p /var/www/pixel-hub
    sudo chown -R $USER:$USER /var/www/pixel-hub
    
    # Cloner le dépôt
    git clone https://github.com/Maxymou/pixel-hub-web.git /var/www/pixel-hub
    
    # Aller dans le répertoire
    cd /var/www/pixel-hub
    
    # Installer les dépendances
    composer install --no-dev --optimize-autoloader
    
    # Copier le fichier .env
    cp .env.example .env
    
    # Configurer la base de données
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
    
    # Configurer les permissions
    sudo chown -R www-data:www-data .
    sudo chmod -R 755 .
    sudo chmod -R 775 storage bootstrap/cache
    sudo chmod -R 775 public/uploads
    
    # Générer la clé d'application
    php bin/console key:generate
    
    # Exécuter les migrations
    php bin/console migrate
    
    # Créer l'utilisateur administrateur
    read -p "Entrez le nom d'utilisateur admin : " ADMIN_USERNAME
    read -s -p "Entrez le mot de passe admin : " ADMIN_PASSWORD
    echo
    
    php bin/console user:create "$ADMIN_USERNAME" "$ADMIN_PASSWORD" --admin
    
    print_message "Application installée avec succès." "$GREEN"
}

# Fonction pour configurer Apache
configure_apache() {
    print_message "Configuration d'Apache..." "$YELLOW"
    
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
    sudo a2dissite 000-default.conf
    
    print_message "Apache configuré avec succès." "$GREEN"
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
    print_message "Démarrage de l'installation automatique de Pixel Hub Web..." "$YELLOW"
    
    install_prerequisites
    configure_environment
    install_application
    configure_apache
    restart_services
    
    print_message "Installation terminée avec succès !" "$GREEN"
    print_message "Vous pouvez maintenant accéder à Pixel Hub Web à l'adresse : http://localhost" "$GREEN"
}

# Exécuter la fonction principale
main 