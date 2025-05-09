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

# Vérification de l'existence de install-lemp-server.sh
if [ ! -f "install-lemp-server.sh" ]; then
    print_error "Le fichier install-lemp-server.sh n'est pas trouvé"
    print_error "Veuillez télécharger le script depuis : https://raw.githubusercontent.com/Maxymou/install-lemp-server/main/install-lemp-server.sh"
    exit 1
fi

# Vérification que install-lemp-server.sh est exécutable
if [ ! -x "install-lemp-server.sh" ]; then
    print_message "Rendre install-lemp-server.sh exécutable..."
    chmod +x install-lemp-server.sh
fi

# Exécution de install-lemp-server.sh
print_message "Installation de la stack LEMP..."
./install-lemp-server.sh
if [ $? -ne 0 ]; then
    print_error "L'installation de la stack LEMP a échoué"
    exit 1
fi

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

# Création du dossier Exceptions s'il n'existe pas
print_message "Création du dossier Exceptions..."
mkdir -p /var/www/pixelhub/app/Exceptions

# Correction du fichier Handler.php
print_message "Correction du fichier Handler.php..."
cat > /var/www/pixelhub/app/Exceptions/Handler.php << 'EOL'
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
EOL

# Configuration des permissions pour le fichier Handler.php
print_message "Configuration des permissions pour le fichier Handler.php..."
chown www-data:www-data /var/www/pixelhub/app/Exceptions/Handler.php
chmod 644 /var/www/pixelhub/app/Exceptions/Handler.php

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
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction
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
sudo -u www-data php artisan key:generate

# Exécution des migrations
print_message "Exécution des migrations..."
sudo -u www-data php artisan migrate --force

# Optimisation
print_message "Optimisation de l'application..."
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

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