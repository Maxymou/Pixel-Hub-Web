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

# Fonction pour vérifier les erreurs
check_error() {
    if [ $? -ne 0 ]; then
        print_message "ERREUR: $1" "$RED"
        exit 1
    fi
}

# Fonction pour vérifier les prérequis
check_prerequisites() {
    print_message "Vérification des prérequis..." "$YELLOW"
    
    # Vérifier si le script est exécuté en tant que root
    if [ "$EUID" -ne 0 ]; then 
        print_message "Ce script doit être exécuté en tant que root (sudo)" "$RED"
        exit 1
    fi
    
    # Vérifier l'espace disque
    FREE_SPACE=$(df -m / | awk 'NR==2 {print $4}')
    if [ "$FREE_SPACE" -lt 1000 ]; then
        print_message "ERREUR: Espace disque insuffisant. Au moins 1GB est requis." "$RED"
        exit 1
    fi
    
    # Vérifier la mémoire
    TOTAL_MEM=$(free -m | awk '/^Mem:/{print $2}')
    if [ "$TOTAL_MEM" -lt 512 ]; then
        print_message "ERREUR: Mémoire insuffisante. Au moins 512MB est requis." "$RED"
        exit 1
    fi
    
    print_message "Prérequis vérifiés avec succès." "$GREEN"
}

# Fonction pour installer les prérequis
install_prerequisites() {
    print_message "Installation des prérequis..." "$YELLOW"
    
    # Mettre à jour les paquets
    sudo apt-get update
    check_error "Échec de la mise à jour des paquets"
    
    sudo apt-get upgrade -y
    check_error "Échec de la mise à niveau des paquets"
    
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
    check_error "Échec de l'installation des paquets essentiels"
    
    # Ajouter le dépôt PHP
    if is_raspberry_pi; then
        # Pour Raspberry Pi, on utilise les paquets par défaut
        sudo apt-get install -y php php-cli php-common php-mysql php-zip php-gd php-mbstring php-curl php-xml php-bcmath php-json php-opcache php-intl php-ldap php-redis php-imagick
    else
        # Pour les autres systèmes, on utilise le dépôt Sury
        curl -sSL https://packages.sury.org/php/apt.gpg -o /etc/apt/trusted.gpg.d/php.gpg
        echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/php.list
        sudo apt-get update
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
    fi
    
    # Installer MySQL (MariaDB)
    sudo apt-get install -y mariadb-server
    sudo systemctl start mariadb
    sudo systemctl enable mariadb
    
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
    
    # Configurer Composer pour permettre l'exécution en tant que root
    export COMPOSER_ALLOW_SUPERUSER=1
    sudo tee /etc/environment.d/composer.conf > /dev/null << EOL
COMPOSER_ALLOW_SUPERUSER=1
EOL
    
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
        sudo tee /etc/php/conf.d/99-pixel-hub.ini > /dev/null << EOL
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

# Fonction pour créer la structure du projet
create_project_structure() {
    print_message "Création de la structure du projet..." "$YELLOW"
    
    # Créer les répertoires nécessaires
    mkdir -p storage/logs
    mkdir -p storage/framework/cache
    mkdir -p storage/framework/sessions
    mkdir -p storage/framework/views
    mkdir -p bootstrap/cache
    mkdir -p public/uploads
    
    # Créer le fichier .env de base
    cat > .env << EOL
APP_NAME=PixelHub
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pixel_hub
DB_USERNAME=pixel_hub
DB_PASSWORD=

LOG_CHANNEL=stack
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
EOL
    
    # Créer le fichier composer.json de base
    cat > composer.json << EOL
{
    "name": "maxymou/pixel-hub-web",
    "description": "Application web responsive pour gérer et lancer des applications et jeux",
    "type": "project",
    "require": {
        "php": ">=7.4",
        "ext-json": "*",
        "ext-pdo": "*",
        "ext-mbstring": "*",
        "ext-curl": "*",
        "ext-gd": "*",
        "ext-zip": "*",
        "ext-bcmath": "*",
        "ext-xml": "*",
        "ext-intl": "*",
        "ext-ldap": "*",
        "ext-redis": "*",
        "ext-imagick": "*"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
EOL
    
    print_message "Structure du projet créée avec succès." "$GREEN"
}

# Fonction pour installer l'application
install_application() {
    print_message "Installation de l'application..." "$YELLOW"
    
    # Créer le répertoire de l'application
    if [ -d "/var/www/pixel-hub" ]; then
        print_message "Le répertoire /var/www/pixel-hub existe déjà. Suppression..." "$YELLOW"
        sudo rm -rf /var/www/pixel-hub
    fi
    
    sudo mkdir -p /var/www/pixel-hub
    check_error "Échec de la création du répertoire"
    
    sudo chown -R $USER:$USER /var/www/pixel-hub
    check_error "Échec de la modification des permissions"
    
    # Aller dans le répertoire
    cd /var/www/pixel-hub
    
    # Cloner le dépôt
    print_message "Clonage du dépôt..." "$YELLOW"
    git clone https://github.com/Maxymou/pixel-hub-web.git .
    check_error "Échec du clonage du dépôt"
    
    # Installer les dépendances avec Composer
    print_message "Installation des dépendances avec Composer..." "$YELLOW"
    export COMPOSER_ALLOW_SUPERUSER=1
    composer install --no-dev --optimize-autoloader --no-interaction
    check_error "Échec de l'installation des dépendances Composer"
    
    # Configurer la base de données
    read -p "Entrez le mot de passe MySQL root : " MYSQL_ROOT_PASSWORD
    read -p "Entrez le mot de passe pour l'utilisateur pixel_hub : " MYSQL_PASSWORD
    
    # Sécuriser l'installation de MariaDB
    sudo mysql_secure_installation << MYSQL_SECURE
y
$MYSQL_ROOT_PASSWORD
$MYSQL_ROOT_PASSWORD
y
y
y
y
MYSQL_SECURE
    
    # Créer la base de données et l'utilisateur
    sudo mysql -u root -p"$MYSQL_ROOT_PASSWORD" << MYSQL_SCRIPT
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
    php -r "echo bin2hex(random_bytes(32));" > .env.key
    sed -i "s/APP_KEY=.*/APP_KEY=$(cat .env.key)/" .env
    rm .env.key
    
    # Créer l'utilisateur administrateur
    read -p "Entrez le nom d'utilisateur admin : " ADMIN_USERNAME
    read -s -p "Entrez le mot de passe admin : " ADMIN_PASSWORD
    echo
    
    # Créer le fichier de configuration de l'administrateur
    mkdir -p config
    cat > config/admin.php << EOL
<?php
return [
    'username' => '$ADMIN_USERNAME',
    'password' => password_hash('$ADMIN_PASSWORD', PASSWORD_DEFAULT),
    'is_admin' => true
];
EOL
    
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
    sudo systemctl restart mariadb
    
    print_message "Services redémarrés avec succès." "$GREEN"
}

# Fonction principale
main() {
    print_message "Démarrage de l'installation automatique de Pixel Hub Web..." "$YELLOW"
    
    # Vérifier les prérequis
    check_prerequisites
    
    # Installer les prérequis
    install_prerequisites
    
    # Configurer l'environnement
    configure_environment
    
    # Installer l'application
    install_application
    
    # Configurer Apache
    configure_apache
    
    # Redémarrer les services
    restart_services
    
    print_message "Installation terminée avec succès !" "$GREEN"
    print_message "Vous pouvez maintenant accéder à Pixel Hub Web à l'adresse : http://localhost" "$GREEN"
    print_message "Si vous rencontrez des problèmes, consultez les logs :" "$YELLOW"
    print_message "sudo tail -f /var/log/apache2/pixel-hub-error.log" "$YELLOW"
}

# Exécuter la fonction principale
main 