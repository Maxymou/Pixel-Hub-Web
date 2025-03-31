#!/bin/bash

# Vérifier le système d'exploitation
check_os() {
    if [[ "$OSTYPE" == "msys" ]] || [[ "$OSTYPE" == "win32" ]] || [[ "$OSTYPE" == "cygwin" ]]; then
        print_error "Ce script doit être exécuté sur Linux"
        print_message "Vous êtes sur Windows. Veuillez utiliser WSL ou une machine virtuelle Linux."
        exit 1
    fi
}

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

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
        exit 1
    fi
}

# Fonction pour vérifier si on est sur un Raspberry Pi
check_raspberry_pi() {
    if [ ! -f "/proc/cpuinfo" ]; then
        print_error "Ce script doit être exécuté sur un Raspberry Pi"
        exit 1
    fi
    
    if ! grep -q "Raspberry Pi" /proc/cpuinfo; then
        print_error "Ce script doit être exécuté sur un Raspberry Pi"
        exit 1
    fi
    
    print_message "Détection du Raspberry Pi réussie"
}

# Fonction pour optimiser le système pour le Raspberry Pi
optimize_raspberry_pi() {
    print_message "Optimisation du système pour le Raspberry Pi..."
    
    # Désactiver les services non essentiels
    sudo systemctl disable bluetooth
    sudo systemctl disable cups
    sudo systemctl disable triggerhappy
    
    # Optimiser la mémoire swap
    if [ -f "/etc/dphys-swapfile" ]; then
        sudo sed -i 's/CONF_SWAPSIZE=100/CONF_SWAPSIZE=2048/' /etc/dphys-swapfile
        sudo dphys-swapfile setup
        sudo dphys-swapfile swapon
    fi
    
    # Optimiser les paramètres système
    sudo tee /etc/sysctl.d/99-raspberry-pi.conf > /dev/null << EOL
vm.swappiness=10
vm.vfs_cache_pressure=50
vm.dirty_ratio=40
vm.dirty_background_ratio=10
EOL
    
    sudo sysctl -p /etc/sysctl.d/99-raspberry-pi.conf
    
    print_message "Optimisation du système terminée"
}

# Fonction pour vérifier les droits d'accès
check_permissions() {
    local dir=$1
    local user=$2
    local group=$3
    local perms=$4
    
    if [ ! -d "$dir" ]; then
        print_error "Le répertoire $dir n'existe pas"
        return 1
    fi
    
    # Vérifier le propriétaire
    if [ "$(stat -c '%U' $dir)" != "$user" ]; then
        print_warning "Le propriétaire de $dir n'est pas $user"
        sudo chown $user:$group $dir
    fi
    
    # Vérifier les permissions
    if [ "$(stat -c '%a' $dir)" != "$perms" ]; then
        print_warning "Les permissions de $dir ne sont pas $perms"
        sudo chmod $perms $dir
    fi
    
    print_message "Vérification des droits de $dir terminée"
}

# Fonction pour corriger les permissions de l'application
fix_application_permissions() {
    print_message "\n[Correction des permissions de l'application]"
    
    # Définir le répertoire de l'application
    APP_DIR="/var/www/pixel-hub-web"
    
    # Vérifier si le répertoire existe
    if [ ! -d "$APP_DIR" ]; then
        print_error "Le répertoire $APP_DIR n'existe pas"
        return 1
    fi
    
    # Obtenir l'utilisateur actuel
    CURRENT_USER=$(whoami)
    
    # Correction des permissions du répertoire principal
    print_message "Correction des permissions du répertoire principal..."
    sudo chown -R $CURRENT_USER:www-data "$APP_DIR"
    sudo chmod -R 755 "$APP_DIR"
    
    # Créer les répertoires nécessaires s'ils n'existent pas
    DIRS=(
        "$APP_DIR/storage"
        "$APP_DIR/storage/logs"
        "$APP_DIR/storage/framework/cache"
        "$APP_DIR/storage/framework/sessions"
        "$APP_DIR/storage/framework/views"
        "$APP_DIR/bootstrap/cache"
        "$APP_DIR/public/uploads"
    )
    
    # Créer les répertoires manquants
    for dir in "${DIRS[@]}"; do
        if [ ! -d "$dir" ]; then
            print_message "Création du répertoire : $dir"
            sudo mkdir -p "$dir"
        fi
    done
    
    # Définir les propriétaires et permissions
    print_message "Configuration des permissions..."
    
    # Permissions pour les répertoires de stockage
    for dir in "${DIRS[@]}"; do
        print_message "Configuration des permissions pour : $dir"
        sudo chmod -R 775 "$dir"
        sudo chown -R $CURRENT_USER:www-data "$dir"
        sudo setfacl -R -m u:www-data:rwx "$dir"
        sudo setfacl -R -m u:$CURRENT_USER:rwx "$dir"
    done
    
    # Permissions pour les fichiers de configuration
    print_message "Configuration des permissions des fichiers..."
    CONFIG_FILES=(
        "$APP_DIR/.env"
        "$APP_DIR/composer.json"
        "$APP_DIR/composer.lock"
        "$APP_DIR/config/admin.php"
    )
    
    for file in "${CONFIG_FILES[@]}"; do
        if [ -f "$file" ]; then
            sudo chmod 644 "$file"
            sudo chown $CURRENT_USER:www-data "$file"
        fi
    done
    
    # Permissions pour le répertoire public
    print_message "Configuration des permissions du répertoire public..."
    sudo chmod -R 755 "$APP_DIR/public"
    sudo chown -R $CURRENT_USER:www-data "$APP_DIR/public"
    
    # Permissions pour le répertoire vendor
    if [ -d "$APP_DIR/vendor" ]; then
        print_message "Configuration des permissions du répertoire vendor..."
        sudo chmod -R 755 "$APP_DIR/vendor"
        sudo chown -R $CURRENT_USER:www-data "$APP_DIR/vendor"
        
        # Permissions spécifiques pour les fichiers exécutables dans vendor
        find "$APP_DIR/vendor" -type f -name "*.php" -exec sudo chmod 644 {} \;
        find "$APP_DIR/vendor" -type f -name "*.sh" -exec sudo chmod 755 {} \;
    fi
    
    # Permissions pour les fichiers de logs
    print_message "Configuration des permissions des fichiers de logs..."
    if [ -f "$APP_DIR/storage/logs/laravel.log" ]; then
        sudo chmod 664 "$APP_DIR/storage/logs/laravel.log"
        sudo chown $CURRENT_USER:www-data "$APP_DIR/storage/logs/laravel.log"
    fi
    
    # Vérifier les permissions après correction
    print_message "\nVérification des permissions après correction :"
    for dir in "${DIRS[@]}"; do
        if [ -w "$dir" ]; then
            print_message "✅ $dir : Permissions correctes"
            print_message "  Propriétaire : $(stat -c '%U:%G' $dir)"
            print_message "  Permissions : $(stat -c '%a' $dir)"
            print_message "  ACL : $(getfacl $dir | grep -v '^#' | grep -v '^$')"
        else
            print_error "❌ $dir : Problème de permissions"
            print_message "  Propriétaire actuel : $(stat -c '%U:%G' $dir)"
            print_message "  Permissions actuelles : $(stat -c '%a' $dir)"
            print_message "  ACL actuelles : $(getfacl $dir | grep -v '^#' | grep -v '^$')"
        fi
    done
    
    # Vérifier les permissions des fichiers de configuration
    print_message "\nVérification des permissions des fichiers de configuration :"
    for file in "${CONFIG_FILES[@]}"; do
        if [ -f "$file" ]; then
            if [ -r "$file" ]; then
                print_message "✅ $file : Permissions correctes"
                print_message "  Propriétaire : $(stat -c '%U:%G' $file)"
                print_message "  Permissions : $(stat -c '%a' $file)"
            else
                print_error "❌ $file : Problème de permissions"
                print_message "  Propriétaire actuel : $(stat -c '%U:%G' $file)"
                print_message "  Permissions actuelles : $(stat -c '%a' $file)"
            fi
        else
            print_error "❌ $file : Fichier non trouvé"
        fi
    done
    
    # Vérifier que l'utilisateur www-data peut écrire dans les répertoires
    print_message "\nVérification des droits d'écriture pour www-data :"
    for dir in "${DIRS[@]}"; do
        if sudo -u www-data test -w "$dir"; then
            print_message "✅ $dir : www-data peut écrire"
        else
            print_error "❌ $dir : www-data ne peut pas écrire"
            print_message "Tentative de correction..."
            sudo chmod -R 775 "$dir"
            sudo chown -R $CURRENT_USER:www-data "$dir"
            sudo setfacl -R -m u:www-data:rwx "$dir"
            sudo setfacl -R -m u:$CURRENT_USER:rwx "$dir"
            if sudo -u www-data test -w "$dir"; then
                print_message "✅ Correction réussie"
            else
                print_error "❌ Échec de la correction"
            fi
        fi
    done
}

# Fonction pour vérifier l'état d'installation
check_installation_status() {
    print_message "=== Vérification détaillée de l'installation ==="
    
    # Vérifier Apache
    print_message "\n[Apache]"
    if systemctl is-active --quiet apache2; then
        print_message "État : ✅ En cours d'exécution"
        print_message "Version : $(apache2 -v | head -n 1)"
        
        # Vérifier les modules Apache
        print_message "Modules activés :"
        apache2ctl -M | grep -i "rewrite\|ssl\|headers"
        
        # Vérifier la configuration Apache
        if apache2ctl configtest | grep -q "Syntax OK"; then
            print_message "Configuration : ✅ Valide"
        else
            print_error "Configuration : ❌ Erreur de syntaxe"
        fi
        
        # Vérifier les logs Apache
        if [ -f "/var/log/apache2/error.log" ]; then
            print_message "Dernières erreurs Apache :"
            tail -n 5 /var/log/apache2/error.log
        fi
    else
        print_error "État : ❌ Arrêté"
        print_message "Tentative de démarrage..."
        sudo systemctl start apache2
        if systemctl is-active --quiet apache2; then
            print_message "✅ Apache démarré avec succès"
        else
            print_error "❌ Échec du démarrage d'Apache"
        fi
    fi
    
    # Vérifier PHP
    print_message "\n[PHP]"
    if command -v php &> /dev/null; then
        print_message "État : ✅ Installé"
        print_message "Version : $(php -v | head -n 1)"
        
        # Vérifier les extensions PHP
        print_message "Extensions PHP installées :"
        php -m | grep -i "mysql\|gd\|curl\|xml\|json\|mbstring\|zip\|bcmath\|opcache\|intl\|ldap\|redis\|imagick"
        
        # Vérifier la configuration PHP
        if php -i | grep -q "Configuration File"; then
            print_message "Fichier de configuration : ✅ Trouvé"
            print_message "Emplacement : $(php -i | grep "Configuration File" | cut -d ">" -f 2)"
        else
            print_error "Fichier de configuration : ❌ Non trouvé"
        fi
        
        # Vérifier les permissions PHP
        check_permissions "/etc/php" "root" "root" "755"
        check_permissions "/var/lib/php" "www-data" "www-data" "755"
    else
        print_error "État : ❌ Non installé"
    fi
    
    # Vérifier MySQL
    print_message "\n[MySQL]"
    if systemctl is-active --quiet mysql; then
        print_message "État : ✅ En cours d'exécution"
        print_message "Version : $(mysql --version)"
        
        # Vérifier la connexion MySQL
        if mysql -e "SELECT 1;" &> /dev/null; then
            print_message "Connexion : ✅ OK"
            
            # Vérifier la base de données
            if mysql -e "USE pixel_hub;" &> /dev/null; then
                print_message "Base de données pixel_hub : ✅ Existe"
                
                # Vérifier les tables
                print_message "Tables présentes :"
                mysql -e "SHOW TABLES FROM pixel_hub;"
            else
                print_error "Base de données pixel_hub : ❌ Non trouvée"
            fi
            
            # Vérifier l'utilisateur
            if mysql -e "SELECT User FROM mysql.user WHERE User='pixel_hub';" | grep -q "pixel_hub"; then
                print_message "Utilisateur pixel_hub : ✅ Existe"
            else
                print_error "Utilisateur pixel_hub : ❌ Non trouvé"
            fi
        else
            print_error "Connexion : ❌ Échec"
        fi
        
        # Vérifier les logs MySQL
        if [ -f "/var/log/mysql/error.log" ]; then
            print_message "Dernières erreurs MySQL :"
            tail -n 5 /var/log/mysql/error.log
        fi
    else
        print_error "État : ❌ Arrêté"
        print_message "Tentative de démarrage..."
        sudo systemctl start mysql
        if systemctl is-active --quiet mysql; then
            print_message "✅ MySQL démarré avec succès"
        else
            print_error "❌ Échec du démarrage de MySQL"
        fi
    fi
    
    # Vérifier les répertoires et leurs permissions
    print_message "\n[Permissions des répertoires]"
    check_permissions "/var/www/html" "www-data" "www-data" "755"
    check_permissions "/var/log/apache2" "root" "adm" "755"
    check_permissions "/var/log/mysql" "mysql" "adm" "755"
    check_permissions "/var/lib/mysql" "mysql" "mysql" "755"
    check_permissions "/etc/apache2" "root" "root" "755"
    check_permissions "/etc/mysql" "root" "root" "755"
    
    # Corriger les permissions de l'application
    fix_application_permissions
    
    # Vérifier l'espace disque
    print_message "\n[Espace disque]"
    df -h / | grep -v "Filesystem"
    
    # Vérifier la mémoire
    print_message "\n[Mémoire]"
    free -h
    
    # Vérifier la température du Raspberry Pi
    if [ -f "/sys/class/thermal/thermal_zone0/temp" ]; then
        TEMP=$(cat /sys/class/thermal/thermal_zone0/temp)
        TEMP_C=$(echo "scale=1; $TEMP/1000" | bc)
        print_message "\n[Température du Raspberry Pi]"
        print_message "Température actuelle : ${TEMP_C}°C"
    fi
    
    print_message "\n=== Fin de la vérification détaillée ==="
}

# Fonction pour installer les prérequis système
install_system_prerequisites() {
    print_message "Installation des prérequis système..."
    sudo apt update
    check_command "Mise à jour des paquets réussie" "Échec de la mise à jour des paquets"
    
    sudo apt install -y curl git unzip
    check_command "Installation des outils de base réussie" "Échec de l'installation des outils de base"
    
    # Configuration des permissions pour les outils de base
    print_message "Configuration des permissions pour les outils de base..."
    sudo chmod 755 /usr/bin/curl
    sudo chmod 755 /usr/bin/git
    sudo chmod 755 /usr/bin/unzip
    
    # Vérifier l'état après l'installation des prérequis
    check_installation_status
}

# Fonction pour installer Apache
install_apache() {
    print_message "Installation d'Apache..."
    
    # Installation d'Apache
    sudo apt-get update
    sudo apt-get install -y apache2
    
    # Créer le répertoire de l'application
    print_message "Création du répertoire de l'application..."
    sudo mkdir -p /var/www/pixel-hub-web/public
    sudo chown -R www-data:www-data /var/www/pixel-hub-web
    sudo chmod -R 755 /var/www/pixel-hub-web
    
    # Configuration d'Apache
    print_message "Configuration d'Apache..."
    
    # Sauvegarder la configuration originale
    sudo cp /etc/apache2/apache2.conf /etc/apache2/apache2.conf.backup
    
    # Créer une nouvelle configuration Apache
    sudo tee /etc/apache2/apache2.conf > /dev/null << EOL
# Configuration globale d'Apache
DefaultRuntimeDir ${APACHE_RUN_DIR}
PidFile ${APACHE_PID_FILE}
Timeout 300
KeepAlive On
MaxKeepAliveRequests 100
KeepAliveTimeout 5

# Configuration des répertoires
<Directory />
    Options FollowSymLinks
    AllowOverride None
    Require all denied
</Directory>

<Directory /var/www/>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>

# Configuration des logs
ErrorLog ${APACHE_LOG_DIR}/error.log
LogLevel warn

# Configuration des ports
Listen 80
Listen 443

# Configuration des virtual hosts
IncludeOptional sites-enabled/*.conf
EOL
    
    # Configuration des modules
    sudo a2enmod rewrite
    sudo a2enmod headers
    sudo a2enmod ssl
    
    # Configuration du site
    sudo tee /etc/apache2/sites-available/pixel-hub.conf > /dev/null << EOL
<VirtualHost *:80>
    ServerName localhost
    ServerAlias pixel-hub.local
    DocumentRoot /var/www/pixel-hub-web/public

    <Directory /var/www/pixel-hub-web/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/pixel-hub-error.log
    CustomLog ${APACHE_LOG_DIR}/pixel-hub-access.log combined
</VirtualHost>
EOL
    
    sudo a2ensite pixel-hub
    sudo a2dissite 000-default
    
    # Configuration des permissions Apache
    print_message "Configuration des permissions Apache..."
    sudo chown -R www-data:www-data /var/www/html
    sudo chmod -R 755 /var/www/html
    sudo chown -R root:root /etc/apache2
    sudo chmod -R 755 /etc/apache2
    sudo chown -R root:adm /var/log/apache2
    sudo chmod -R 755 /var/log/apache2
    
    # Vérifier la configuration Apache
    print_message "Vérification de la configuration Apache..."
    sudo apache2ctl configtest
    
    # Démarrer Apache
    print_message "Démarrage d'Apache..."
    sudo service apache2 stop
    sudo rm -f /var/run/apache2/apache2.pid
    sudo rm -f /var/run/apache2/apache2.sock
    sudo service apache2 start
    sleep 2
    
    # Vérifier si Apache est en cours d'exécution
    if pgrep -x "apache2" > /dev/null; then
        print_message "✅ Apache démarré avec succès"
    else
        print_error "❌ Échec du démarrage d'Apache"
        print_message "Vérification des logs Apache..."
        if [ -f "/var/log/apache2/error.log" ]; then
            print_message "Dernières erreurs Apache :"
            tail -n 10 /var/log/apache2/error.log
        fi
        exit 1
    fi
    
    # Vérification finale
    print_message "Vérification finale d'Apache..."
    if pgrep -x "apache2" > /dev/null; then
        print_message "✅ Apache est en cours d'exécution"
        print_message "Version : $(apache2 -v | head -n 1)"
        print_message "Modules activés :"
        apache2ctl -M | grep -i "rewrite\|ssl\|headers"
        
        # Vérifier que le serveur répond
        if curl -s http://localhost > /dev/null; then
            print_message "✅ Apache répond correctement"
        else
            print_warning "⚠️ Apache ne répond pas sur http://localhost"
            print_message "Vérification des ports..."
            netstat -tulpn | grep apache2
        fi
    else
        print_error "❌ Apache n'est pas en cours d'exécution"
        exit 1
    fi
}

# Fonction pour installer PHP et ses extensions
install_php() {
    print_message "Installation de PHP et ses extensions..."
    
    # Installation des extensions PHP essentielles
    sudo apt install -y \
        php8.2 \
        php8.2-cli \
        php8.2-common \
        php8.2-mysql \
        php8.2-zip \
        php8.2-gd \
        php8.2-mbstring \
        php8.2-curl \
        php8.2-xml \
        php8.2-bcmath \
        php8.2-json \
        php8.2-opcache \
        php8.2-intl \
        php8.2-ldap \
        php8.2-redis \
        php8.2-imagick \
        php8.2-fpm
    
    check_command "Installation de PHP et ses extensions réussie" "Échec de l'installation de PHP"
    
    # Configuration des permissions PHP
    print_message "Configuration des permissions PHP..."
    sudo chown -R root:root /etc/php
    sudo chmod -R 755 /etc/php
    sudo chown -R www-data:www-data /var/lib/php
    sudo chmod -R 755 /var/lib/php
    sudo chown -R www-data:www-data /var/run/php
    sudo chmod -R 755 /var/run/php
    
    # Vérifier l'installation de PHP
    php -v
    check_command "PHP est correctement installé" "PHP n'est pas correctement installé"
    
    # Vérifier l'état après l'installation de PHP
    check_installation_status
}

# Fonction pour installer MySQL
install_mysql() {
    print_message "Installation de MySQL..."
    sudo apt install -y mysql-server
    check_command "Installation de MySQL réussie" "Échec de l'installation de MySQL"
    
    # Configuration des permissions MySQL
    print_message "Configuration des permissions MySQL..."
    sudo chown -R mysql:mysql /var/lib/mysql
    sudo chmod -R 755 /var/lib/mysql
    sudo chown -R root:root /etc/mysql
    sudo chmod -R 755 /etc/mysql
    sudo chown -R mysql:adm /var/log/mysql
    sudo chmod -R 755 /var/log/mysql
    
    # Démarrer MySQL
    sudo systemctl start mysql
    check_command "Démarrage de MySQL réussi" "Échec du démarrage de MySQL"
    
    # Vérifier que MySQL est en cours d'exécution
    sudo systemctl is-active --quiet mysql
    check_command "MySQL est en cours d'exécution" "MySQL n'est pas en cours d'exécution"
    
    # Vérifier l'état après l'installation de MySQL
    check_installation_status
}

# Fonction pour installer Composer
install_composer() {
    print_message "Installation de Composer..."
    curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
    check_command "Installation de Composer réussie" "Échec de l'installation de Composer"
    
    # Configuration des permissions Composer
    print_message "Configuration des permissions Composer..."
    sudo chown root:root /usr/local/bin/composer
    sudo chmod 755 /usr/local/bin/composer
    sudo chown -R $SUDO_USER:$SUDO_USER ~/.composer
    sudo chmod -R 755 ~/.composer
}

# Fonction pour configurer la base de données
configure_database() {
    print_message "Configuration de la base de données..."
    
    # Créer la base de données et l'utilisateur
    sudo mysql -e "CREATE DATABASE IF NOT EXISTS pixel_hub;"
    sudo mysql -e "CREATE USER IF NOT EXISTS 'pixel_hub'@'localhost' IDENTIFIED BY '1234';"
    sudo mysql -e "GRANT ALL PRIVILEGES ON pixel_hub.* TO 'pixel_hub'@'localhost';"
    sudo mysql -e "FLUSH PRIVILEGES;"
    
    # Configuration des permissions de la base de données
    print_message "Configuration des permissions de la base de données..."
    sudo chown -R mysql:mysql /var/lib/mysql/pixel_hub
    sudo chmod -R 750 /var/lib/mysql/pixel_hub
    
    check_command "Configuration de la base de données réussie" "Échec de la configuration de la base de données"
}

# Fonction pour définir les permissions de l'application
set_application_permissions() {
    print_message "\n[Définition des permissions de l'application]"
    
    # Obtenir l'utilisateur actuel
    CURRENT_USER=$(whoami)
    APP_DIR="/var/www/pixel-hub-web"
    
    print_message "Définition des permissions pour $APP_DIR..."
    
    # Permissions pour le répertoire principal
    sudo chown -R $CURRENT_USER:www-data "$APP_DIR"
    sudo chmod -R 755 "$APP_DIR"
    
    # Créer et configurer les répertoires de stockage
    STORAGE_DIRS=(
        "$APP_DIR/storage"
        "$APP_DIR/storage/logs"
        "$APP_DIR/storage/framework/cache"
        "$APP_DIR/storage/framework/sessions"
        "$APP_DIR/storage/framework/views"
        "$APP_DIR/bootstrap/cache"
        "$APP_DIR/public/uploads"
    )
    
    for dir in "${STORAGE_DIRS[@]}"; do
        print_message "Configuration du répertoire : $dir"
        sudo mkdir -p "$dir"
        sudo chmod -R 775 "$dir"
        sudo chown -R $CURRENT_USER:www-data "$dir"
        sudo setfacl -R -m u:www-data:rwx "$dir"
        sudo setfacl -R -m u:$CURRENT_USER:rwx "$dir"
        sudo setfacl -R -d -m u:www-data:rwx "$dir"
        sudo setfacl -R -d -m u:$CURRENT_USER:rwx "$dir"
    done
    
    # Permissions pour les fichiers de configuration
    CONFIG_FILES=(
        "$APP_DIR/.env"
        "$APP_DIR/composer.json"
        "$APP_DIR/composer.lock"
        "$APP_DIR/config/admin.php"
    )
    
    for file in "${CONFIG_FILES[@]}"; do
        if [ -f "$file" ]; then
            print_message "Configuration des permissions pour : $file"
            sudo chmod 644 "$file"
            sudo chown $CURRENT_USER:www-data "$file"
        fi
    done
    
    # Permissions pour le répertoire public
    print_message "Configuration des permissions du répertoire public..."
    sudo chmod -R 755 "$APP_DIR/public"
    sudo chown -R $CURRENT_USER:www-data "$APP_DIR/public"
    
    # Permissions pour le répertoire vendor
    if [ -d "$APP_DIR/vendor" ]; then
        print_message "Configuration des permissions du répertoire vendor..."
        sudo chmod -R 755 "$APP_DIR/vendor"
        sudo chown -R $CURRENT_USER:www-data "$APP_DIR/vendor"
    fi
    
    # Vérification des permissions
    print_message "\nVérification des permissions :"
    for dir in "${STORAGE_DIRS[@]}"; do
        if [ -w "$dir" ]; then
            print_message "✅ $dir : Permissions correctes"
            print_message "  Propriétaire : $(stat -c '%U:%G' $dir)"
            print_message "  Permissions : $(stat -c '%a' $dir)"
            print_message "  ACL : $(getfacl $dir | grep -v '^#' | grep -v '^$')"
        else
            print_error "❌ $dir : Problème de permissions"
            print_message "  Propriétaire actuel : $(stat -c '%U:%G' $dir)"
            print_message "  Permissions actuelles : $(stat -c '%a' $dir)"
            print_message "  ACL actuelles : $(getfacl $dir | grep -v '^#' | grep -v '^$')"
            
            # Tentative de correction
            print_message "Tentative de correction..."
            sudo chmod -R 775 "$dir"
            sudo chown -R $CURRENT_USER:www-data "$dir"
            sudo setfacl -R -m u:www-data:rwx "$dir"
            sudo setfacl -R -m u:$CURRENT_USER:rwx "$dir"
            sudo setfacl -R -d -m u:www-data:rwx "$dir"
            sudo setfacl -R -d -m u:$CURRENT_USER:rwx "$dir"
            
            if [ -w "$dir" ]; then
                print_message "✅ Correction réussie"
            else
                print_error "❌ Échec de la correction"
            fi
        fi
    done
    
    # Vérifier que l'utilisateur www-data peut écrire dans les répertoires
    print_message "\nVérification des droits d'écriture pour www-data :"
    for dir in "${STORAGE_DIRS[@]}"; do
        if sudo -u www-data test -w "$dir"; then
            print_message "✅ $dir : www-data peut écrire"
        else
            print_error "❌ $dir : www-data ne peut pas écrire"
            print_message "Tentative de correction..."
            sudo chmod -R 775 "$dir"
            sudo chown -R $CURRENT_USER:www-data "$dir"
            sudo setfacl -R -m u:www-data:rwx "$dir"
            sudo setfacl -R -m u:$CURRENT_USER:rwx "$dir"
            sudo setfacl -R -d -m u:www-data:rwx "$dir"
            sudo setfacl -R -d -m u:$CURRENT_USER:rwx "$dir"
            if sudo -u www-data test -w "$dir"; then
                print_message "✅ Correction réussie"
            else
                print_error "❌ Échec de la correction"
            fi
        fi
    done
}

# Fonction pour installer l'application
install_application() {
    print_message "\n[Installation de l'application]"
    
    # Cloner le dépôt
    if [ ! -d "$APP_DIR" ]; then
        print_message "Clonage du dépôt..."
        git clone "$REPO_URL" "$APP_DIR"
    fi
    
    # Aller dans le répertoire de l'application
    cd "$APP_DIR"
    
    # Installer les dépendances
    print_message "Installation des dépendances..."
    composer install --no-interaction
    
    # Définir les permissions
    set_application_permissions
    
    # Copier le fichier .env
    if [ ! -f "$APP_DIR/.env" ]; then
        print_message "Configuration de l'environnement..."
        cp .env.example .env
        php artisan key:generate
    fi
    
    print_message "Installation de l'application terminée"
}

# Fonction pour désinstaller l'application
uninstall_application() {
    print_message "\n[Désinstallation de l'application]"
    
    # Obtenir l'utilisateur actuel
    CURRENT_USER=$(whoami)
    APP_DIR="/var/www/pixel-hub-web"
    
    # Arrêter les services
    print_message "Arrêt des services..."
    sudo service apache2 stop
    sudo service mysql stop
    
    # Supprimer la base de données
    print_message "Suppression de la base de données..."
    if command -v mysql &> /dev/null; then
        sudo mysql -e "DROP DATABASE IF EXISTS pixel_hub;"
        sudo mysql -e "DROP USER IF EXISTS 'pixel_hub'@'localhost';"
        sudo mysql -e "FLUSH PRIVILEGES;"
        print_message "✅ Base de données supprimée"
    else
        print_warning "MySQL n'est pas installé, pas de base de données à supprimer"
    fi
    
    # Corriger les permissions avant la suppression
    print_message "Correction des permissions avant la suppression..."
    if [ -d "$APP_DIR" ]; then
        # Donner les permissions complètes à l'utilisateur actuel
        sudo chown -R $CURRENT_USER:$CURRENT_USER "$APP_DIR"
        sudo chmod -R 777 "$APP_DIR"
        
        # Supprimer les fichiers
        print_message "Suppression des fichiers de l'application..."
        rm -rf "$APP_DIR"
        print_message "✅ Répertoire de l'application supprimé"
    else
        print_warning "Le répertoire $APP_DIR n'existe pas"
    fi
    
    # Supprimer les configurations Apache
    print_message "Suppression des configurations Apache..."
    if [ -f "/etc/apache2/sites-available/pixel-hub.conf" ]; then
        sudo a2dissite pixel-hub.conf
        sudo rm /etc/apache2/sites-available/pixel-hub.conf
        print_message "✅ Configuration Apache supprimée"
    else
        print_warning "Configuration Apache non trouvée"
    fi
    
    # Supprimer les configurations PHP
    print_message "Suppression des configurations PHP..."
    if [ -f "/etc/php/conf.d/99-pixel-hub.ini" ]; then
        sudo rm /etc/php/conf.d/99-pixel-hub.ini
        print_message "✅ Configuration PHP supprimée"
    else
        print_warning "Configuration PHP non trouvée"
    fi
    
    # Désinstaller les paquets
    print_message "Désinstallation des paquets..."
    sudo apt remove -y apache2 php8.2* mysql-server
    sudo apt autoremove -y
    sudo apt clean
    print_message "✅ Paquets désinstallés"
    
    # Nettoyer les répertoires de logs
    print_message "Nettoyage des logs..."
    if [ -d "/var/log/apache2" ]; then
        sudo rm -rf /var/log/apache2/*
        print_message "✅ Logs Apache nettoyés"
    fi
    if [ -d "/var/log/mysql" ]; then
        sudo rm -rf /var/log/mysql/*
        print_message "✅ Logs MySQL nettoyés"
    fi
    
    # Nettoyer le cache de Composer
    print_message "Nettoyage du cache de Composer..."
    if [ -d "$HOME/.composer/cache" ]; then
        rm -rf "$HOME/.composer/cache/*"
        print_message "✅ Cache de Composer nettoyé"
    fi
    
    # Vérification finale
    print_message "\nVérification de la désinstallation..."
    if [ ! -d "$APP_DIR" ] && [ ! -f "/etc/apache2/sites-available/pixel-hub.conf" ] && [ ! -f "/etc/php/conf.d/99-pixel-hub.ini" ]; then
        print_message "✅ Désinstallation réussie"
    else
        print_warning "Certains éléments n'ont pas été complètement supprimés"
    fi
    
    print_message "Désinstallation terminée !"
}

# Fonction principale
main() {
    # Vérifier le système d'exploitation
    check_os
    
    # Vérifier si c'est une désinstallation
    if [ "$1" = "uninstall" ]; then
        uninstall_application
        exit 0
    fi
    
    print_message "Début de l'installation..."
    
    # Vérifier si on est sur un Raspberry Pi
    check_raspberry_pi
    
    # Optimiser le système pour le Raspberry Pi
    optimize_raspberry_pi
    
    # Installation des prérequis système
    install_system_prerequisites
    
    # Installation de PHP et ses extensions
    install_php
    
    # Installation d'Apache
    install_apache
    
    # Installation de MySQL
    install_mysql
    
    # Configuration de la base de données
    configure_database
    
    # Installation de l'application
    install_application
    
    # Vérification finale de l'état d'installation
    print_message "=== Rapport final d'installation ==="
    check_installation_status
    
    print_message "Installation terminée avec succès !"
}

# Exécution du script
main "$@" 