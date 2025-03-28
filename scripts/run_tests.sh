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

# Fonction pour vérifier si une commande existe
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Vérifier les prérequis
print_message "Vérification des prérequis..." "$YELLOW"

if ! command_exists php; then
    print_message "PHP n'est pas installé. Veuillez installer PHP 8.1 ou supérieur." "$RED"
    exit 1
fi

# Vérifier la version de PHP
PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1)
if [ "$PHP_VERSION" -lt 8 ]; then
    print_message "PHP 8.1 ou supérieur est requis. Version actuelle : $PHP_VERSION" "$RED"
    exit 1
fi

# Vérifier Xdebug
if ! php -m | grep -q xdebug; then
    print_message "Xdebug n'est pas installé. Installation en cours..." "$YELLOW"
    pecl install xdebug
    echo "zend_extension=xdebug.so" > /etc/php/8.1/cli/conf.d/20-xdebug.ini
fi

if ! command_exists composer; then
    print_message "Composer n'est pas installé. Veuillez installer Composer." "$RED"
    exit 1
fi

if ! command_exists phpunit; then
    print_message "PHPUnit n'est pas installé. Installation en cours..." "$YELLOW"
    composer require --dev phpunit/phpunit ^10.0
fi

# Installer les dépendances si nécessaire
if [ ! -d "vendor" ]; then
    print_message "Installation des dépendances..." "$YELLOW"
    composer install
fi

# Créer le dossier de couverture si nécessaire
if [ ! -d "tests/coverage" ]; then
    mkdir -p tests/coverage
fi

# Configurer l'environnement de test
print_message "Configuration de l'environnement de test..." "$YELLOW"
cp .env.example .env.testing
sed -i 's/APP_ENV=local/APP_ENV=testing/' .env.testing
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env.testing
sed -i 's/DB_DATABASE=pixel_hub/DB_DATABASE=:memory:/' .env.testing
export APP_ENV=testing

# Exécuter les tests unitaires
print_message "Exécution des tests unitaires..." "$YELLOW"
phpunit --testsuite Unit

# Exécuter les tests d'intégration
print_message "Exécution des tests d'intégration..." "$YELLOW"
phpunit --testsuite Integration

# Exécuter les tests de sécurité
print_message "Exécution des tests de sécurité..." "$YELLOW"
phpunit --testsuite Security

# Exécuter les tests de performance
print_message "Exécution des tests de performance..." "$YELLOW"
phpunit --testsuite Performance

# Exécuter les tests de fonctionnalités
print_message "Exécution des tests de fonctionnalités..." "$YELLOW"
phpunit --testsuite Feature

# Générer le rapport de couverture
print_message "Génération du rapport de couverture..." "$YELLOW"
phpunit --coverage-html tests/coverage --coverage-text=tests/coverage.txt

# Vérifier si tous les tests ont réussi
if [ $? -eq 0 ]; then
    print_message "Tous les tests ont réussi !" "$GREEN"
    print_message "Rapport de couverture généré dans tests/coverage/" "$GREEN"
else
    print_message "Certains tests ont échoué. Veuillez vérifier les résultats ci-dessus." "$RED"
    exit 1
fi

# Nettoyer l'environnement de test
rm -f .env.testing 