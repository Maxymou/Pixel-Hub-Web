#!/bin/bash

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Vérification des privilèges root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}[ERREUR]${NC} Ce script doit être exécuté en tant que root (sudo)"
    exit 1
fi

echo -e "${GREEN}[INFO]${NC} Désinstallation de PixelHub..."

# Arrêt des services
echo -e "${GREEN}[INFO]${NC} Arrêt des services..."
systemctl stop nginx

# Suppression des fichiers de l'application
echo -e "${GREEN}[INFO]${NC} Suppression des fichiers de l'application..."
rm -rf /var/www/pixelhub

# Suppression des configurations
echo -e "${GREEN}[INFO]${NC} Suppression des configurations..."
rm -f /etc/nginx/sites-enabled/pixelhub.conf
rm -f /etc/nginx/sites-available/pixelhub.conf

# Suppression de la base de données
echo -e "${GREEN}[INFO]${NC} Suppression de la base de données..."
mysql -u root -e "DROP DATABASE IF EXISTS pixelhub;"

# Suppression des logs
echo -e "${GREEN}[INFO]${NC} Suppression des logs..."
rm -f /var/log/nginx/pixelhub-*.log

# Redémarrage de Nginx
echo -e "${GREEN}[INFO]${NC} Redémarrage de Nginx..."
systemctl restart nginx

echo -e "${GREEN}[INFO]${NC} Désinstallation terminée." 