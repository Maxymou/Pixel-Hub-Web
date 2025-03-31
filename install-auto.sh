#!/bin/bash

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
    
    for dir in "${DIRS[@]}"; do
        if [ ! -d "$dir" ]; then
            print_message "Création du répertoire : $dir"
            sudo mkdir -p "$dir"
        fi
    done
    
    # Définir les propriétaires et permissions
    print_message "Configuration des permissions..."
    
    # Propriétaire principal
    sudo chown -R www-data:www-data "$APP_DIR"
    
    # Permissions pour les répertoires de stockage
    for dir in "${DIRS[@]}"; do
        print_message "Configuration des permissions pour : $dir"
        sudo chmod -R 775 "$dir"
        sudo chown -R www-data:www-data "$dir"
    done
    
    # Permissions pour les fichiers de configuration
    sudo chmod 644 "$APP_DIR/.env"
    sudo chmod 644 "$APP_DIR/composer.json"
    sudo chmod 644 "$APP_DIR/composer.lock"
    
    # Vérifier les permissions après correction
    print_message "\nVérification des permissions après correction :"
    for dir in "${DIRS[@]}"; do
        if [ -w "$dir" ]; then
            print_message "✅ $dir : Permissions correctes"
        else
            print_error "❌ $dir : Problème de permissions"
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
    
    print_message "\n=== Fin de la vérification détaillée ==="
}

# Fonction pour installer les prérequis système
install_system_prerequisites() {
    print_message "Installation des prérequis système..."
    sudo apt update
    check_command "Mise à jour des paquets réussie" "Échec de la mise à jour des paquets"
    
    sudo apt install -y curl git unzip
    check_command "Installation des outils de base réussie" "Échec de l'installation des outils de base"
    
    # Vérifier l'état après l'installation des prérequis
    check_installation_status
}

# Fonction pour installer Apache
install_apache() {
    print_message "Installation d'Apache..."
    sudo apt install -y apache2
    check_command "Installation d'Apache réussie" "Échec de l'installation d'Apache"
    
    # Vérifier qu'Apache est en cours d'exécution
    sudo systemctl is-active --quiet apache2
    check_command "Apache est en cours d'exécution" "Apache n'est pas en cours d'exécution"
    
    # Vérifier l'état après l'installation d'Apache
    check_installation_status
}

# Fonction pour installer PHP et ses extensions
install_php() {
    print_message "Installation de PHP et ses extensions..."
    
    # Installation des extensions PHP essentielles
    sudo apt install -y \
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
    
    check_command "Installation de PHP et ses extensions réussie" "Échec de l'installation de PHP"
    
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
    
    # Démarrer MySQL
    sudo systemctl start mysql
    check_command "Démarrage de MySQL réussi" "Échec du démarrage de MySQL"
    
    # Vérifier que MySQL est en cours d'exécution
    sudo systemctl is-active --quiet mysql
    check_command "MySQL est en cours d'exécution" "MySQL n'est pas en cours d'exécution"
    
    # Vérifier l'état après l'installation de MySQL
    check_installation_status
}

# Fonction pour configurer Apache
configure_apache() {
    print_message "Configuration d'Apache..."
    
    # Activer les modules Apache nécessaires
    sudo a2enmod rewrite
    sudo a2enmod headers
    sudo a2enmod ssl
    
    # Redémarrer Apache pour appliquer les changements
    sudo systemctl restart apache2
    check_command "Configuration d'Apache réussie" "Échec de la configuration d'Apache"
    
    # Vérifier l'état après la configuration d'Apache
    check_installation_status
}

# Fonction pour installer Composer
install_composer() {
    print_message "Installation de Composer..."
    curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
    check_command "Installation de Composer réussie" "Échec de l'installation de Composer"
}

# Fonction pour configurer la base de données
configure_database() {
    print_message "Configuration de la base de données..."
    
    # Créer la base de données et l'utilisateur
    sudo mysql -e "CREATE DATABASE IF NOT EXISTS pixel_hub;"
    sudo mysql -e "CREATE USER IF NOT EXISTS 'pixel_hub'@'localhost' IDENTIFIED BY '1234';"
    sudo mysql -e "GRANT ALL PRIVILEGES ON pixel_hub.* TO 'pixel_hub'@'localhost';"
    sudo mysql -e "FLUSH PRIVILEGES;"
    
    check_command "Configuration de la base de données réussie" "Échec de la configuration de la base de données"
}

# Fonction principale
main() {
    print_message "Début de l'installation..."
    
    # Installation des prérequis système
    install_system_prerequisites
    
    # Installation d'Apache
    install_apache
    
    # Installation de PHP et ses extensions
    install_php
    
    # Installation de MySQL
    install_mysql
    
    # Configuration d'Apache
    configure_apache
    
    # Installation de Composer
    install_composer
    
    # Configuration de la base de données
    configure_database
    
    # Vérification finale de l'état d'installation
    print_message "=== Rapport final d'installation ==="
    check_installation_status
    
    print_message "Installation terminée avec succès !"
}

# Exécution du script
main 