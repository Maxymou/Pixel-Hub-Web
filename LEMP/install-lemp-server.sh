#!/bin/bash

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Variables pour le rapport
REPORT_FILE="/var/log/lemp-installation-report-$(date +%Y%m%d-%H%M%S).log"
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

# Fonction de vérification
check_command() {
    if [ $? -eq 0 ]; then
        print_message "$1"
        ((SUCCESS++))
    else
        print_error "$2"
    fi
}

# Fonction de vérification de service
check_service() {
    if systemctl is-active --quiet "$1"; then
        print_message "Service $1 est en cours d'exécution"
        ((SUCCESS++))
    else
        print_error "Service $1 n'est pas en cours d'exécution"
    fi
}

# Fonction de vérification de port
check_port() {
    if netstat -tuln | grep -q ":$1 "; then
        print_message "Port $1 est ouvert"
        ((SUCCESS++))
    else
        print_error "Port $1 n'est pas ouvert"
    fi
}

# Fonction de vérification de fichier
check_file() {
    if [ -f "$1" ]; then
        print_message "Fichier $1 existe"
        ((SUCCESS++))
    else
        print_error "Fichier $1 n'existe pas"
    fi
}

# Fonction de vérification de module PHP
check_php_module() {
    if php -m | grep -q "$1"; then
        print_message "Module PHP $1 est installé"
        ((SUCCESS++))
    else
        print_error "Module PHP $1 n'est pas installé"
    fi
}

# Création du fichier de rapport
echo "=== Rapport d'installation LEMP - $(date) ===" > "$REPORT_FILE"
echo "Système: $(uname -a)" >> "$REPORT_FILE"
echo "Architecture: $(uname -m)" >> "$REPORT_FILE"
echo "==========================================" >> "$REPORT_FILE"

# Vérification des privilèges root
if [ "$EUID" -ne 0 ]; then 
    print_error "Ce script doit être exécuté en tant que root (sudo)"
    exit 1
fi

# Vérification de l'architecture
if [ "$(uname -m)" != "aarch64" ]; then
    print_error "Ce script est conçu pour Raspberry Pi OS 64 bits uniquement"
    exit 1
fi

# 1. Mise à jour du système
print_message "Mise à jour du système..."
apt-get update
check_command "Mise à jour des paquets réussie" "Échec de la mise à jour des paquets"

apt-get upgrade -y
check_command "Mise à niveau des paquets réussie" "Échec de la mise à niveau des paquets"

# 2. Installation de Nginx
print_message "Installation de Nginx..."
apt-get install -y nginx
check_command "Installation de Nginx réussie" "Échec de l'installation de Nginx"

# 3. Installation de PHP et modules
print_message "Installation de PHP et modules..."
apt-get install -y php-fpm php-mysql php-mbstring php-xml php-json
check_command "Installation de PHP et modules réussie" "Échec de l'installation de PHP"

# Détection de la version de PHP
PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
print_message "Version de PHP détectée: $PHP_VERSION"

# Vérification des modules PHP
check_php_module "mysql"
check_php_module "mbstring"
check_php_module "xml"
check_php_module "json"

# 4. Configuration de Nginx pour PHP
print_message "Configuration de Nginx pour PHP..."
cat > /etc/nginx/sites-available/default << EOL
server {
    listen 80;
    server_name _;
    root /var/www/html;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ =404;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
EOL
check_command "Configuration de Nginx réussie" "Échec de la configuration de Nginx"

# 5. Installation et sécurisation de MariaDB
print_message "Installation de MariaDB..."
apt-get install -y mariadb-server
check_command "Installation de MariaDB réussie" "Échec de l'installation de MariaDB"

# Sécurisation de MariaDB
print_message "Sécurisation de MariaDB..."
mysql_secure_installation << EOF

y
y
root_password
root_password
y
y
y
y
EOF
check_command "Sécurisation de MariaDB réussie" "Échec de la sécurisation de MariaDB"

# 6. Création du fichier de test PHP
print_message "Création du fichier de test PHP..."
cat > /var/www/html/info.php << 'EOL'
<?php
phpinfo();
?>
EOL
check_command "Création du fichier de test PHP réussie" "Échec de la création du fichier de test PHP"

# 7. Configuration et redémarrage des services
print_message "Configuration et redémarrage des services..."

# Vérification et création du répertoire de configuration PHP-FPM si nécessaire
PHP_FPM_CONFIG_DIR="/etc/php/${PHP_VERSION}/fpm"
if [ ! -d "$PHP_FPM_CONFIG_DIR" ]; then
    print_message "Création du répertoire de configuration PHP-FPM..."
    mkdir -p "$PHP_FPM_CONFIG_DIR"
fi

# Configuration de PHP-FPM
cat > "$PHP_FPM_CONFIG_DIR/php.ini" << 'EOL'
[PHP]
memory_limit = 128M
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 600
max_input_time = 600
EOL

# Configuration du pool PHP-FPM
cat > "$PHP_FPM_CONFIG_DIR/pool.d/www.conf" << EOL
[www]
user = www-data
group = www-data
listen = /var/run/php/php${PHP_VERSION}-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
EOL

# Redémarrage des services
systemctl restart nginx
check_command "Redémarrage de Nginx réussi" "Échec du redémarrage de Nginx"

# Vérification et redémarrage de PHP-FPM
PHP_FPM_SERVICE="php${PHP_VERSION}-fpm"
if systemctl list-unit-files | grep -q "${PHP_FPM_SERVICE}.service"; then
    systemctl restart ${PHP_FPM_SERVICE}
    check_command "Redémarrage de PHP-FPM réussi" "Échec du redémarrage de PHP-FPM"
else
    print_error "Service PHP-FPM non trouvé. Tentative de réinstallation..."
    apt-get install --reinstall php-fpm
    systemctl enable ${PHP_FPM_SERVICE}
    systemctl restart ${PHP_FPM_SERVICE}
    check_command "Réinstallation et redémarrage de PHP-FPM réussis" "Échec de la réinstallation de PHP-FPM"
fi

systemctl restart mariadb
check_command "Redémarrage de MariaDB réussi" "Échec du redémarrage de MariaDB"

# 8. Vérifications finales
print_message "Vérifications finales..."

# Vérification des services
check_service "nginx"
check_service "${PHP_FPM_SERVICE}"
check_service "mariadb"

# Vérification des ports
check_port "80"
check_port "3306"

# Vérification des fichiers de configuration
check_file "/etc/nginx/sites-available/default"
check_file "$PHP_FPM_CONFIG_DIR/php.ini"
check_file "$PHP_FPM_CONFIG_DIR/pool.d/www.conf"
check_file "/etc/mysql/my.cnf"

# Vérification de la connexion à MariaDB
if mysql -u root -proot_password -e "SELECT 1;" >/dev/null 2>&1; then
    print_message "Connexion à MariaDB réussie"
    ((SUCCESS++))
else
    print_error "Échec de la connexion à MariaDB"
fi

# Génération du rapport final
echo "==========================================" >> "$REPORT_FILE"
echo "=== Résumé de l'installation ===" >> "$REPORT_FILE"
echo "Succès: $SUCCESS" >> "$REPORT_FILE"
echo "Avertissements: $WARNINGS" >> "$REPORT_FILE"
echo "Erreurs: $ERRORS" >> "$REPORT_FILE"
echo "==========================================" >> "$REPORT_FILE"

# Affichage du résumé
echo -e "\n${GREEN}=== Résumé de l'installation ===${NC}"
echo -e "Succès: ${GREEN}$SUCCESS${NC}"
echo -e "Avertissements: ${YELLOW}$WARNINGS${NC}"
echo -e "Erreurs: ${RED}$ERRORS${NC}"
echo -e "\nLe rapport complet a été généré dans: ${GREEN}$REPORT_FILE${NC}"

if [ $ERRORS -gt 0 ]; then
    print_warning "L'installation est terminée avec des erreurs. Veuillez consulter le rapport pour plus de détails."
    exit 1
else
    print_message "Installation terminée avec succès!"
    print_message "Vous pouvez accéder à la page de test à l'adresse: http://votre_ip/info.php"
    print_message "Mot de passe root MariaDB: root_password"
fi 