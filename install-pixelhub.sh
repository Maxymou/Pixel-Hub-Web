#!/bin/bash

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Variables pour le rapport
REPORT_FILE="/var/log/pixelhub-installation-report-$(date +%Y%m%d-%H%M%S).log"
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

# Création du fichier de rapport
echo "=== Rapport d'installation PixelHub - $(date) ===" > "$REPORT_FILE"
echo "Système: $(uname -a)" >> "$REPORT_FILE"
echo "Architecture: $(uname -m)" >> "$REPORT_FILE"
echo "==========================================" >> "$REPORT_FILE"

# Vérification des privilèges root
if [ "$EUID" -ne 0 ]; then 
    print_error "Ce script doit être exécuté en tant que root (sudo)"
    exit 1
fi

# Vérification de Nginx
if ! command -v nginx &> /dev/null; then
    print_error "Nginx n'est pas installé. Veuillez installer la stack LEMP d'abord."
    exit 1
fi

# Vérification de MariaDB
if ! command -v mysql &> /dev/null; then
    print_error "MariaDB n'est pas installé. Veuillez installer la stack LEMP d'abord."
    exit 1
fi

# Vérification de PHP
if ! command -v php &> /dev/null; then
    print_error "PHP n'est pas installé. Veuillez installer la stack LEMP d'abord."
    exit 1
fi

# Vérification des versions
NGINX_VERSION=$(nginx -v 2>&1 | cut -d'/' -f2)
MARIADB_VERSION=$(mysql --version | cut -d' ' -f4)
PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1-2)

print_message "Versions détectées :"
print_message "Nginx : $NGINX_VERSION"
print_message "MariaDB : $MARIADB_VERSION"
print_message "PHP : $PHP_VERSION"

# Installation des dépendances manquantes
print_message "Vérification des dépendances..."

# Vérification de Git
if ! command -v git &> /dev/null; then
    print_message "Installation de Git..."
    apt-get update && apt-get install -y git
fi

# Vérification de Composer
if ! command -v composer &> /dev/null; then
    print_message "Installation de Composer..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# Vérification des extensions PHP manquantes
print_message "Vérification des extensions PHP..."
PHP_MODULES=("php8.2-zip" "php8.2-bcmath" "php8.2-curl")
for module in "${PHP_MODULES[@]}"; do
    if ! dpkg -l | grep -q "^ii  $module "; then
        print_message "Installation de $module..."
        apt-get install -y $module
    fi
done

# Création du dossier de l'application
print_message "Création du dossier de l'application..."
rm -rf /var/www/pixelhub
mkdir -p /var/www/pixelhub
cd /var/www/pixelhub

# Configuration de Git pour permettre le dépôt
print_message "Configuration de Git..."
git config --global --add safe.directory /var/www/pixelhub

# Clonage du dépôt Git
print_message "Clonage du dépôt Git..."
git clone https://github.com/Maxymou/pixel-hub-web.git .
if [ $? -ne 0 ]; then
    print_error "Échec du clonage du dépôt Git"
    print_error "Vérifiez votre connexion internet et les permissions du dossier"
    exit 1
fi

# Vérification de la présence du fichier composer.json
if [ ! -f /var/www/pixelhub/composer.json ]; then
    print_error "Le fichier composer.json n'a pas été trouvé après le clonage"
    print_error "Vérifiez que le dépôt Git est correctement configuré"
    exit 1
fi

# Création des dossiers nécessaires
print_message "Création des dossiers nécessaires..."
mkdir -p /var/www/pixelhub/storage/framework/{sessions,views,cache}
mkdir -p /var/www/pixelhub/bootstrap/cache
mkdir -p /var/www/pixelhub/storage/logs
mkdir -p /var/www/pixelhub/vendor

# Configuration des permissions
print_message "Configuration des permissions..."
chown -R www-data:www-data /var/www/pixelhub
chmod -R 755 /var/www/pixelhub
chmod -R 775 /var/www/pixelhub/storage
chmod -R 775 /var/www/pixelhub/bootstrap/cache
chmod -R 775 /var/www/pixelhub/vendor

# Création du fichier de configuration Nginx
print_message "Création de la configuration Nginx..."
cat > /etc/nginx/sites-available/pixelhub.conf << 'EOL'
server {
    listen 80;
    server_name localhost;
    root /var/www/pixelhub/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOL

# Création du lien symbolique
print_message "Création du lien symbolique pour la configuration Nginx..."
ln -sf /etc/nginx/sites-available/pixelhub.conf /etc/nginx/sites-enabled/

# Vérification de la configuration Nginx
print_message "Vérification de la configuration Nginx..."
if ! nginx -t; then
    print_error "La configuration Nginx est invalide"
    print_error "Vérifiez les logs avec : tail -f /var/log/nginx/error.log"
    exit 1
fi

# Vérification et démarrage des services
print_message "Vérification des services..."

# Nginx
if ! systemctl is-active nginx >/dev/null 2>&1; then
    print_message "Démarrage du service Nginx..."
    systemctl start nginx
    sleep 2
    if ! systemctl is-active nginx >/dev/null 2>&1; then
        print_error "Impossible de démarrer le service Nginx"
        print_error "Vérifiez les logs avec : tail -f /var/log/nginx/error.log"
        print_error "Vérifiez le statut avec : systemctl status nginx"
        exit 1
    fi
    print_message "Service Nginx démarré avec succès"
fi

# MariaDB
if ! systemctl is-active mysql >/dev/null 2>&1; then
    print_message "Démarrage du service MariaDB..."
    systemctl start mysql
    sleep 2
    if ! systemctl is-active mysql >/dev/null 2>&1; then
        print_error "Impossible de démarrer le service MariaDB"
        print_error "Vérifiez les logs avec : tail -f /var/log/mysql/error.log"
        print_error "Vérifiez le statut avec : systemctl status mysql"
        exit 1
    fi
    print_message "Service MariaDB démarré avec succès"
fi

# Vérification des ports
if ! netstat -tuln | grep -q ":80 "; then
    print_error "Le port 80 n'est pas ouvert (Nginx)"
    print_error "Vérifiez les logs avec : tail -f /var/log/nginx/error.log"
    print_error "Vérifiez le statut avec : systemctl status nginx"
    exit 1
fi

if ! netstat -tuln | grep -q ":3306 "; then
    print_error "Le port 3306 n'est pas ouvert (MariaDB)"
    print_error "Vérifiez les logs avec : tail -f /var/log/mysql/error.log"
    print_error "Vérifiez le statut avec : systemctl status mysql"
    exit 1
fi

# Création de la base de données
print_message "Création de la base de données..."
mysql -u root -e "CREATE DATABASE IF NOT EXISTS pixelhub;"

# Création du fichier .env
print_message "Création du fichier .env..."
cat > /var/www/pixelhub/.env << 'EOL'
APP_NAME=PixelHub
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pixelhub
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
FILESYSTEM_DISK=public
SESSION_DRIVER=file
SESSION_LIFETIME=120
EOL

# Installation des dépendances
print_message "Installation des dépendances..."
cd /var/www/pixelhub

# Suppression du fichier composer.lock s'il existe
if [ -f composer.lock ]; then
    print_message "Suppression du fichier composer.lock..."
    rm composer.lock
fi

# Configuration de Composer
print_message "Configuration de Composer..."
export COMPOSER_ALLOW_SUPERUSER=1
export COMPOSER_MEMORY_LIMIT=-1

# Installation des dépendances en tant que www-data
print_message "Installation des dépendances avec Composer..."
sudo -u www-data composer update --no-dev --optimize-autoloader --no-interaction
if [ $? -ne 0 ]; then
    print_error "Échec de l'installation des dépendances Composer"
    print_error "Vérifiez les logs avec : tail -f /var/log/composer.log"
    exit 1
fi

# Vérification de l'installation des dépendances
if [ ! -f /var/www/pixelhub/vendor/autoload.php ]; then
    print_error "Les dépendances n'ont pas été installées correctement"
    print_error "Vérifiez les logs avec : tail -f /var/log/composer.log"
    exit 1
fi

# Vérification des permissions du dossier vendor
print_message "Vérification des permissions du dossier vendor..."
chown -R www-data:www-data /var/www/pixelhub/vendor
chmod -R 755 /var/www/pixelhub/vendor

# Génération de la clé d'application
print_message "Génération de la clé d'application..."
php artisan key:generate

# Exécution des migrations
print_message "Exécution des migrations..."
php artisan migrate --force

# Optimisation
print_message "Optimisation de l'application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Redémarrage de Nginx
print_message "Redémarrage de Nginx..."
systemctl restart nginx

# Vérification finale
if [ $ERRORS -eq 0 ]; then
    print_message "Installation terminée avec succès !"
    print_message "Vous pouvez accéder à PixelHub à l'adresse : http://votre_ip"
else
    print_warning "Installation terminée avec $ERRORS erreur(s)"
fi

if [ $WARNINGS -gt 0 ]; then
    print_warning "Installation terminée avec $WARNINGS avertissement(s)"
fi

print_message "Le rapport d'installation a été sauvegardé dans : $REPORT_FILE" 