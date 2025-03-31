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

# Fonction pour vérifier la connexion internet
check_internet() {
    print_message "Vérification de la connexion internet..." "$YELLOW"
    if ! ping -c 1 google.com &> /dev/null; then
        print_message "ERREUR: Pas de connexion internet" "$RED"
        exit 1
    fi
    print_message "Connexion internet vérifiée." "$GREEN"
}

# Fonction pour vérifier la version de PHP
check_php_version() {
    print_message "Vérification de la version de PHP..." "$YELLOW"
    PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
    if (( $(echo "$PHP_VERSION < 7.4" | bc -l) )); then
        print_message "ERREUR: PHP 7.4 ou supérieur est requis" "$RED"
        exit 1
    fi
    print_message "Version de PHP vérifiée : $PHP_VERSION" "$GREEN"
}

# Fonction pour sauvegarder une configuration
backup_config() {
    if [ -f "$1" ]; then
        print_message "Sauvegarde de $1..." "$YELLOW"
        cp "$1" "$1.bak"
        print_message "Configuration sauvegardée." "$GREEN"
    fi
}

# Fonction pour vérifier les prérequis
check_prerequisites() {
    print_message "Vérification des prérequis..." "$YELLOW"
    
    # Vérifier la connexion internet
    print_message "Vérification de la connexion internet..." "$YELLOW"
    if ping -c 1 google.com &> /dev/null; then
        print_message "Connexion internet vérifiée." "$GREEN"
    else
        print_message "ERREUR: Pas de connexion internet." "$RED"
        exit 1
    fi
    
    # Installer bc si nécessaire
    if ! command -v bc &> /dev/null; then
        print_message "Installation de bc..." "$YELLOW"
        sudo apt-get update
        sudo apt-get install -y bc
    fi
    
    # Vérifier la version de PHP
    print_message "Vérification de la version de PHP..." "$YELLOW"
    PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
    if [ $(echo "$PHP_VERSION >= 7.4" | bc) -eq 1 ]; then
        print_message "Version de PHP vérifiée : $PHP_VERSION" "$GREEN"
    else
        print_message "ERREUR: PHP 7.4 ou supérieur requis." "$RED"
        exit 1
    fi
    
    # Vérifier les commandes requises
    for cmd in php mysql apache2ctl composer git; do
        if ! command -v $cmd &> /dev/null; then
            print_message "ERREUR: $cmd n'est pas installé" "$RED"
            exit 1
        fi
    done
    
    # Vérifier l'espace disque
    DISK_SPACE=$(df -m / | awk 'NR==2 {print $4}')
    if [ "$DISK_SPACE" -lt 1000 ]; then
        print_message "ERREUR: Moins de 1GB d'espace disque disponible" "$RED"
        exit 1
    fi
    
    # Vérifier la mémoire
    MEMORY=$(free -m | awk 'NR==2 {print $2}')
    if [ "$MEMORY" -lt 512 ]; then
        print_message "ERREUR: Moins de 512MB de RAM disponible" "$RED"
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
        python3-pip \
        bc
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
    sudo mkdir -p /etc/environment.d
    sudo tee /etc/environment.d/composer.conf > /dev/null << EOL
COMPOSER_ALLOW_SUPERUSER=1
EOL
    
    print_message "Prérequis installés avec succès." "$GREEN"
}

# Fonction pour configurer l'environnement
configure_environment() {
    print_message "Configuration de l'environnement..." "$YELLOW"
    
    # Sauvegarder les configurations existantes
    backup_config "/etc/apache2/sites-available/pixel-hub.conf"
    
    # Créer le répertoire de configuration PHP s'il n'existe pas
    sudo mkdir -p /etc/php/conf.d
    
    # Configurer le fuseau horaire
    sudo timedatectl set-timezone Europe/Paris
    
    # Configurer les limites système
    cat > /etc/security/limits.d/pixel-hub.conf << EOL
www-data soft nofile 65535
www-data hard nofile 65535
www-data soft nproc 65535
www-data hard nproc 65535
EOL
    
    # Configurer Apache
    sudo a2enmod rewrite
    sudo a2enmod ssl
    sudo a2enmod headers
    
    # Configurer PHP
    cat > /etc/php/conf.d/99-pixel-hub.ini << EOL
memory_limit = 256M
max_execution_time = 60
upload_max_filesize = 64M
post_max_size = 64M
max_input_vars = 3000
date.timezone = "Europe/Paris"
EOL
    
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
        "ext-imagick": "*",
        "vlucas/phpdotenv": "^5.5",
        "monolog/monolog": "^2.9",
        "firebase/php-jwt": "^6.4",
        "symfony/http-foundation": "^5.4",
        "symfony/routing": "^5.4",
        "symfony/security-csrf": "^5.4",
        "symfony/validator": "^5.4",
        "symfony/process": "^5.4",
        "symfony/console": "^5.4",
        "symfony/yaml": "^5.4",
        "symfony/cache": "^5.4",
        "symfony/config": "^5.4",
        "symfony/dependency-injection": "^5.4",
        "symfony/event-dispatcher": "^5.4",
        "symfony/filesystem": "^5.4",
        "symfony/finder": "^5.4",
        "symfony/http-kernel": "^5.4",
        "symfony/mailer": "^5.4",
        "symfony/messenger": "^5.4",
        "symfony/polyfill-ctype": "^1.28",
        "symfony/polyfill-iconv": "^1.28",
        "symfony/polyfill-intl-grapheme": "^1.28",
        "symfony/polyfill-intl-icu": "^1.28",
        "symfony/polyfill-intl-idn": "^1.28",
        "symfony/polyfill-intl-normalizer": "^1.28",
        "symfony/polyfill-mbstring": "^1.28",
        "symfony/polyfill-php72": "^1.28",
        "symfony/polyfill-php73": "^1.28",
        "symfony/polyfill-php74": "^1.28",
        "symfony/polyfill-php80": "^1.28",
        "symfony/polyfill-php81": "^1.28",
        "symfony/security-bundle": "^5.4",
        "symfony/security-guard": "^5.4",
        "symfony/security-http": "^5.4",
        "symfony/service-contracts": "^2.5",
        "symfony/stopwatch": "^5.4",
        "symfony/string": "^5.4",
        "symfony/translation": "^5.4",
        "symfony/translation-contracts": "^2.5",
        "symfony/twig-bridge": "^5.4",
        "symfony/twig-bundle": "^5.4",
        "symfony/var-dumper": "^5.4",
        "symfony/var-exporter": "^5.4",
        "symfony/web-link": "^5.4",
        "symfony/workflow": "^5.4",
        "twig/twig": "^3.0",
        "doctrine/annotations": "^1.13",
        "doctrine/cache": "^1.11",
        "doctrine/collections": "^1.6",
        "doctrine/common": "^2.13",
        "doctrine/dbal": "^2.13",
        "doctrine/deprecations": "^0.5.3",
        "doctrine/doctrine-bundle": "^2.7",
        "doctrine/doctrine-migrations-bundle": "^3.2",
        "doctrine/event-manager": "^1.1",
        "doctrine/inflector": "^1.4",
        "doctrine/instantiator": "^1.4",
        "doctrine/lexer": "^1.2",
        "doctrine/orm": "^2.11",
        "doctrine/persistence": "^2.2",
        "doctrine/reflection": "^1.2",
        "doctrine/sql-formatter": "^1.1"
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

# Fonction pour créer le fichier composer.lock de base
create_composer_lock() {
    print_message "Création du fichier composer.lock de base..." "$YELLOW"
    
    cat > /var/www/pixel-hub/composer.lock << EOL
{
    "_readme": [
        "This file locks the dependencies of your project to a known state",
        "Read more about it at https://getcomposer.org/doc/01-basic-usage.md#installing-dependencies",
        "This file is @generated automatically"
    ],
    "content-hash": "generated",
    "packages": [],
    "packages-dev": [],
    "aliases": [],
    "minimum-stability": "stable",
    "stability-flags": [],
    "prefer-stable": false,
    "prefer-lowest": false,
    "platform": {
        "php": ">=7.4"
    },
    "platform-dev": [],
    "plugin-api-version": "2.0.0"
}
EOL
    
    chown www-data:www-data /var/www/pixel-hub/composer.lock
    chmod 644 /var/www/pixel-hub/composer.lock
    
    check_error "Création du fichier composer.lock"
}

# Fonction pour configurer la base de données
configure_database() {
    print_message "Configuration de la base de données..." "$YELLOW"
    
    # Démarrer MariaDB si ce n'est pas déjà fait
    sudo systemctl start mariadb
    sudo systemctl enable mariadb
    
    # Attendre que MariaDB soit prêt
    sleep 5
    
    # Demander les mots de passe
    read -p "Entrez le mot de passe MySQL root : " MYSQL_ROOT_PASSWORD
    read -p "Entrez le mot de passe pour l'utilisateur pixel_hub : " MYSQL_PASSWORD
    
    # Configurer MariaDB de manière non interactive
    sudo mysql -u root << MYSQL_CONFIG
ALTER USER 'root'@'localhost' IDENTIFIED BY '$MYSQL_ROOT_PASSWORD';
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
MYSQL_CONFIG
    
    # Créer la base de données et l'utilisateur
    sudo mysql -u root -p"$MYSQL_ROOT_PASSWORD" << MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS pixel_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
DROP USER IF EXISTS 'pixel_hub'@'localhost';
CREATE USER 'pixel_hub'@'localhost' IDENTIFIED BY '$MYSQL_PASSWORD';
GRANT ALL PRIVILEGES ON pixel_hub.* TO 'pixel_hub'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT
    
    # Vérifier que la base de données est accessible
    if ! mysql -u pixel_hub -p"$MYSQL_PASSWORD" -e "USE pixel_hub;" &> /dev/null; then
        print_message "ERREUR: Impossible d'accéder à la base de données." "$RED"
        print_message "Vérification des étapes..." "$YELLOW"
        
        # Vérifier si la base de données existe
        if ! sudo mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "SHOW DATABASES LIKE 'pixel_hub';" | grep -q "pixel_hub"; then
            print_message "ERREUR: La base de données pixel_hub n'existe pas." "$RED"
        fi
        
        # Vérifier si l'utilisateur existe
        if ! sudo mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "SELECT User FROM mysql.user WHERE User='pixel_hub';" | grep -q "pixel_hub"; then
            print_message "ERREUR: L'utilisateur pixel_hub n'existe pas." "$RED"
        fi
        
        # Vérifier les privilèges
        if ! sudo mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "SHOW GRANTS FOR 'pixel_hub'@'localhost';" | grep -q "ALL PRIVILEGES"; then
            print_message "ERREUR: L'utilisateur pixel_hub n'a pas les privilèges nécessaires." "$RED"
        fi
        
        # Afficher les logs MariaDB
        print_message "Dernières lignes des logs MariaDB :" "$YELLOW"
        if [ -f "/var/log/mysql/error.log" ]; then
            sudo tail -n 50 /var/log/mysql/error.log
        else
            print_message "Le fichier de log n'existe pas." "$RED"
        fi
        
        exit 1
    fi
    
    # Mettre à jour le fichier .env avec le mot de passe de la base de données
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$MYSQL_PASSWORD/" /var/www/pixel-hub/.env
    
    print_message "Base de données configurée avec succès." "$GREEN"
}

# Fonction pour installer l'application
install_application() {
    echo -e "${BLUE}Installation de l'application...${NC}"
    
    # Supprimer le dossier existant s'il existe
    if [ -d "/var/www/pixel-hub-web" ]; then
        rm -rf /var/www/pixel-hub-web
    fi
    
    # Créer le dossier d'installation
    mkdir -p /var/www/pixel-hub-web
    cd /var/www/pixel-hub-web
    
    # Cloner le repository
    git clone https://github.com/Maxymou/pixel-hub-web.git .
    
    # Créer les dossiers nécessaires
    mkdir -p storage/logs
    mkdir -p storage/framework/cache
    mkdir -p storage/framework/sessions
    mkdir -p storage/framework/views
    mkdir -p bootstrap/cache
    mkdir -p public/uploads
    mkdir -p app/Http
    mkdir -p app/Console
    mkdir -p app/Exceptions
    mkdir -p config
    mkdir -p routes
    mkdir -p resources/views
    mkdir -p database/migrations
    mkdir -p database/seeders
    
    # Créer le fichier composer.json
    cat > composer.json << 'EOL'
{
    "name": "maxymou/pixel-hub-web",
    "description": "Application web responsive pour gérer et lancer des applications et jeux",
    "type": "project",
    "require": {
        "php": ">=7.4",
        "laravel/framework": "^8.0",
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
        "ext-imagick": "*",
        "vlucas/phpdotenv": "^5.5",
        "monolog/monolog": "^2.9",
        "firebase/php-jwt": "^6.4"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.0",
        "mockery/mockery": "^1.4",
        "phpstan/phpstan": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "scripts": {
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi"
        ],
        "post-root-package-install": [
            "@php -r \"file_exists('.env') || file_put_contents('.env', file_get_contents('.env.example'));\""
        ],
        "post-create-project-cmd": [
            "@php artisan key:generate --ansi"
        ]
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
EOL
    
    # Supprimer le fichier composer.lock s'il existe
    rm -f composer.lock
    
    # Installer les dépendances
    composer install --no-interaction --no-dev --optimize-autoloader
    
    # Créer le fichier bootstrap/app.php
    cat > bootstrap/app.php << 'EOL'
<?php

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

return $app;
EOL
    
    # Créer le fichier app/Http/Kernel.php
    cat > app/Http/Kernel.php << 'EOL'
<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}
EOL
    
    # Créer le fichier app/Console/Kernel.php
    cat > app/Console/Kernel.php << 'EOL'
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
EOL
    
    # Créer le fichier app/Exceptions/Handler.php
    cat > app/Exceptions/Handler.php << 'EOL'
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
EOL
    
    # Créer le fichier artisan
    cat > artisan << 'EOL'
#!/usr/bin/env php
<?php

define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArgvInput,
    new Symfony\Component\Console\Output\ConsoleOutput
);

$kernel->terminate($input, $status);

exit($status);
EOL
    chmod +x artisan
    
    # Créer le fichier .env
    cat > .env << EOL
APP_NAME=PixelHub
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://$(get_ip_address)

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pixel_hub
DB_USERNAME=pixel_hub
DB_PASSWORD=1234

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
EOL
    
    # S'assurer que nous sommes dans le bon répertoire
    cd /var/www/pixel-hub-web
    
    # Vérifier que le fichier artisan existe
    if [ ! -f "artisan" ]; then
        echo -e "${RED}ERREUR: Le fichier artisan n'existe pas${NC}"
        exit 1
    fi
    
    # Générer la clé d'application
    php artisan key:generate
    
    # Nettoyer le cache
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    
    # Optimiser l'application
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Définir les permissions
    chown -R www-data:www-data /var/www/pixel-hub-web
    chmod -R 755 /var/www/pixel-hub-web
    chmod -R 775 /var/www/pixel-hub-web/storage
    chmod -R 775 /var/www/pixel-hub-web/bootstrap/cache
    
    # Configurer la base de données
    php artisan migrate --force
    
    # Créer l'utilisateur admin
    php artisan tinker --execute="
        \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@pixel-hub.com',
            'password' => Hash::make('admin123')
        ]);
    "
    
    # Créer le fichier de configuration admin
    cat > config/admin.php << EOL
<?php
return [
    'username' => 'admin',
    'password' => password_hash('admin123', PASSWORD_DEFAULT),
    'is_admin' => true
];
EOL
    
    echo -e "${GREEN}Installation de l'application terminée${NC}"
}

# Fonction pour obtenir l'adresse IP
get_ip_address() {
    # D'abord essayer d'obtenir l'adresse IP via hostname -I
    IP=$(hostname -I | awk '{print $1}')
    
    # Si aucune adresse IP n'est trouvée, essayer les interfaces réseau
    if [ -z "$IP" ]; then
        # Essayer eth0
        if ip addr show eth0 &> /dev/null; then
            IP=$(ip addr show eth0 | grep "inet\b" | awk '{print $2}' | cut -d/ -f1)
        # Essayer wlan0
        elif ip addr show wlan0 &> /dev/null; then
            IP=$(ip addr show wlan0 | grep "inet\b" | awk '{print $2}' | cut -d/ -f1)
        # Essayer wlan1
        elif ip addr show wlan1 &> /dev/null; then
            IP=$(ip addr show wlan1 | grep "inet\b" | awk '{print $2}' | cut -d/ -f1)
        fi
    fi
    
    # Si toujours aucune adresse IP, essayer une dernière méthode
    if [ -z "$IP" ]; then
        IP=$(ip route get 1 | awk '{print $7;exit}')
    fi
    
    # Si toujours aucune adresse IP, retourner localhost
    if [ -z "$IP" ]; then
        echo "localhost"
    else
        echo "$IP"
    fi
}

# Fonction pour créer le fichier de configuration PHP
create_php_config() {
    print_message "Création du fichier de configuration PHP..." "$YELLOW"
    
    cat > /etc/php/conf.d/99-pixel-hub.ini << EOF
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 64M
post_max_size = 64M
date.timezone = "Europe/Paris"
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log
EOF
    
    # Créer le répertoire de logs PHP s'il n'existe pas
    mkdir -p /var/log/php
    chown www-data:www-data /var/log/php
    chmod 755 /var/log/php
    
    check_error "Création du fichier de configuration PHP"
}

# Fonction pour créer le fichier .env
create_env_file() {
    print_message "Création du fichier .env..." "$YELLOW"
    
    # Générer la clé d'application
    APP_KEY=$(php -r "echo base64_encode(random_bytes(32));")
    
    # Obtenir l'adresse IP
    IP_ADDRESS=$(get_ip_address)
    
    # Créer le fichier .env
    cat > /var/www/pixel-hub/.env << EOF
APP_NAME=PixelHub
APP_ENV=production
APP_KEY=$APP_KEY
APP_DEBUG=false
APP_URL=http://$IP_ADDRESS

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pixel_hub
DB_USERNAME=pixel_hub
DB_PASSWORD=$MYSQL_PASSWORD

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
EOF
    
    chown www-data:www-data /var/www/pixel-hub/.env
    chmod 644 /var/www/pixel-hub/.env
    
    check_error "Création du fichier .env"
}

# Fonction pour configurer Apache
configure_apache() {
    print_message "Configuration d'Apache..." "$YELLOW"
    
    # Obtenir l'adresse IP
    IP_ADDRESS=$(get_ip_address)
    
    # Créer la configuration Apache
    sudo tee /etc/apache2/sites-available/pixel-hub.conf > /dev/null << EOL
<VirtualHost *:80>
    ServerName localhost
    ServerAlias $IP_ADDRESS
    DocumentRoot /var/www/pixel-hub-web/public
    
    <Directory /var/www/pixel-hub-web/public>
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

# Fonction pour générer le récapitulatif
generate_summary() {
    print_message "\n=== Récapitulatif de l'installation ===" "$YELLOW"
    
    # Informations système
    print_message "\nInformations système :" "$GREEN"
    echo "Système : $(uname -a)"
    echo "PHP Version : $(php -v | head -n 1)"
    echo "MySQL Version : $(mysql --version)"
    echo "Apache Version : $(apache2 -v | head -n 1)"
    echo "Composer Version : $(composer --version)"
    
    # Adresses d'accès
    print_message "\nAdresses d'accès :" "$GREEN"
    echo "Local : http://localhost"
    echo "IP : http://$(get_ip_address)"
    
    # Vérification des services
    print_message "\nÉtat des services :" "$GREEN"
    if systemctl is-active --quiet apache2; then
        echo "Apache : ✅ En cours d'exécution"
    else
        echo "Apache : ❌ Arrêté"
    fi
    
    if systemctl is-active --quiet mariadb; then
        echo "MariaDB : ✅ En cours d'exécution"
    else
        echo "MariaDB : ❌ Arrêté"
    fi
    
    # Vérification des répertoires
    print_message "\nVérification des répertoires :" "$GREEN"
    DIRS=(
        "/var/www/pixel-hub-web"
        "/var/www/pixel-hub-web/storage"
        "/var/www/pixel-hub-web/storage/logs"
        "/var/www/pixel-hub-web/storage/framework/cache"
        "/var/www/pixel-hub-web/storage/framework/sessions"
        "/var/www/pixel-hub-web/storage/framework/views"
        "/var/www/pixel-hub-web/bootstrap/cache"
        "/var/www/pixel-hub-web/public/uploads"
    )
    
    for dir in "${DIRS[@]}"; do
        if [ -d "$dir" ]; then
            echo "$dir : ✅ Existe"
            if [ -w "$dir" ]; then
                echo "  Permissions : ✅ Écriture autorisée"
            else
                echo "  Permissions : ❌ Pas d'écriture"
            fi
        else
            echo "$dir : ❌ Manquant"
        fi
    done
    
    # Vérification des fichiers de configuration
    print_message "\nVérification des fichiers de configuration :" "$GREEN"
    FILES=(
        "/etc/apache2/sites-available/pixel-hub.conf"
        "/etc/php/conf.d/99-pixel-hub.ini"
        "/var/www/pixel-hub-web/.env"
        "/var/www/pixel-hub-web/composer.json"
        "/var/www/pixel-hub-web/config/admin.php"
    )
    
    for file in "${FILES[@]}"; do
        if [ -f "$file" ]; then
            echo "$file : ✅ Existe"
        else
            echo "$file : ❌ Manquant"
        fi
    done
    
    # Vérification de la base de données
    print_message "\nVérification de la base de données :" "$GREEN"
    if mysql -u pixel_hub -p"$MYSQL_PASSWORD" -e "USE pixel_hub;" &> /dev/null; then
        echo "Base de données pixel_hub : ✅ Accessible"
        echo "Utilisateur pixel_hub : ✅ Accessible"
    else
        echo "Base de données pixel_hub : ❌ Non accessible"
        echo "Utilisateur pixel_hub : ❌ Non accessible"
    fi
    
    # Vérification des dépendances Composer
    print_message "\nVérification des dépendances Composer :" "$GREEN"
    if [ -d "/var/www/pixel-hub-web/vendor" ]; then
        echo "Dossier vendor : ✅ Existe"
        if [ -f "/var/www/pixel-hub-web/composer.lock" ]; then
            echo "Fichier composer.lock : ✅ Existe"
        else
            echo "Fichier composer.lock : ❌ Manquant"
        fi
    else
        echo "Dossier vendor : ❌ Manquant"
    fi
    
    print_message "\n=== Fin du récapitulatif ===" "$YELLOW"
    print_message "\nSi vous rencontrez des problèmes, veuillez fournir ce récapitulatif pour faciliter le diagnostic." "$YELLOW"
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
    
    # Obtenir l'adresse IP
    IP_ADDRESS=$(get_ip_address)
    
    print_message "Installation terminée avec succès !" "$GREEN"
    print_message "Vous pouvez maintenant accéder à l'application via :" "$GREEN"
    print_message "http://localhost" "$YELLOW"
    print_message "ou" "$YELLOW"
    print_message "http://$IP_ADDRESS" "$YELLOW"
    
    # Générer le récapitulatif
    generate_summary
}

# Exécuter la fonction principale
main 