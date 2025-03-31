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

# Fonction principale
main() {
    print_message "Début de l'installation..."
    
    # 1. Mise à jour du système
    print_message "Mise à jour du système..."
    sudo apt update
    sudo apt upgrade -y
    sudo apt update
    check_command "Mise à jour du système réussie" "Échec de la mise à jour du système"
    
    # 2. Installation d'Apache
    print_message "Installation d'Apache..."
    sudo apt install -y apache2
    check_command "Installation d'Apache réussie" "Échec de l'installation d'Apache"
    
    # 3. Configuration des permissions Apache
    print_message "Configuration des permissions Apache..."
    sudo chmod -R 770 /var/www/html/
    check_command "Configuration des permissions Apache réussie" "Échec de la configuration des permissions"
    
    # 4. Vérification d'Apache
    print_message "Vérification du fonctionnement d'Apache..."
    wget -O check_apache.html http://127.0.0.1
    if [ -f "check_apache.html" ]; then
        print_message "Apache fonctionne correctement"
        cat ./check_apache.html
    else
        print_error "Apache ne répond pas"
    fi
    
    # 5. Installation de PHP
    print_message "Installation de PHP..."
    sudo apt install -y php php-mbstring
    check_command "Installation de PHP réussie" "Échec de l'installation de PHP"
    
    # 6. Configuration de PHP
    print_message "Configuration de PHP..."
    sudo rm /var/www/html/index.html
    echo "<?php phpinfo(); ?>" > /var/www/html/index.php
    check_command "Configuration de PHP réussie" "Échec de la configuration de PHP"
    
    # 7. Installation de MySQL
    print_message "Installation de MySQL..."
    sudo apt install -y mariadb-server php-mysql
    check_command "Installation de MySQL réussie" "Échec de l'installation de MySQL"
    
    # 8. Configuration de MySQL
    print_message "Configuration de MySQL..."
    sudo mysql --user=root << EOF
DROP USER 'root'@'localhost';
CREATE USER 'root'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION;
EOF
    check_command "Configuration de MySQL réussie" "Échec de la configuration de MySQL"
    
    # 9. Installation de PHPMyAdmin
    print_message "Installation de PHPMyAdmin..."
    sudo apt install -y phpmyadmin
    check_command "Installation de PHPMyAdmin réussie" "Échec de l'installation de PHPMyAdmin"
    
    # 10. Configuration de PHPMyAdmin
    print_message "Configuration de PHPMyAdmin..."
    sudo ln -s /usr/share/phpmyadmin /var/www/html/phpmyadmin
    check_command "Configuration de PHPMyAdmin réussie" "Échec de la configuration de PHPMyAdmin"
    
    print_message "Installation terminée avec succès !"
    print_message "Vous pouvez accéder à :"
    print_message "- Site web : http://127.0.0.1"
    print_message "- PHPMyAdmin : http://127.0.0.1/phpmyadmin"
}

# Exécution du script
main "$@" 