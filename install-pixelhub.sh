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

# Vérification des services
if ! systemctl is-active --quiet nginx; then
    print_error "Le service Nginx n'est pas en cours d'exécution"
    exit 1
fi

if ! systemctl is-active --quiet mysql; then
    print_error "Le service MariaDB n'est pas en cours d'exécution"
    exit 1
fi

# Vérification des ports
if ! netstat -tuln | grep -q ":80 "; then
    print_error "Le port 80 n'est pas ouvert (Nginx)"
    exit 1
fi

if ! netstat -tuln | grep -q ":3306 "; then
    print_error "Le port 3306 n'est pas ouvert (MariaDB)"
    exit 1
fi

# Installation de PixelHub
print_message "Installation de PixelHub..."

# Création du dossier de l'application
mkdir -p /var/www/pixelhub
cd /var/www/pixelhub

# Clonage du repository
print_message "Clonage du repository..."
git clone https://github.com/Maxymou/pixel-hub-web.git .

# Configuration des permissions
chown -R www-data:www-data /var/www/pixelhub
chmod -R 755 /var/www/pixelhub
chmod -R 775 /var/www/pixelhub/storage
chmod -R 775 /var/www/pixelhub/bootstrap/cache

# Configuration de Nginx
print_message "Configuration de Nginx..."
cp pixelhub.conf /etc/nginx/sites-available/
ln -s /etc/nginx/sites-available/pixelhub.conf /etc/nginx/sites-enabled/

# Création de la base de données
print_message "Création de la base de données..."
mysql -u root -e "CREATE DATABASE IF NOT EXISTS pixelhub;"

# Configuration de l'environnement
print_message "Configuration de l'environnement..."
cp .env.example .env
php artisan key:generate

# Installation des dépendances
print_message "Installation des dépendances..."
composer install --no-dev --optimize-autoloader

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