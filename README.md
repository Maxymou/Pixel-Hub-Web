# Pixel Hub Web

Pixel Hub Web est une application web pour gérer vos pixels et vos projets.

## Prérequis

- PHP 8.0 ou supérieur
- Composer
- MySQL 5.7 ou supérieur
- Apache 2.4 ou supérieur

## Installation

1. Clonez le dépôt :
```bash
git clone https://github.com/Maxymou/pixel-hub-web.git
cd pixel-hub-web
```

2. Installez les dépendances :
```bash
composer install
```

3. Copiez le fichier .env.example en .env :
```bash
cp .env.example .env
```

4. Générez la clé d'application :
```bash
php artisan key:generate
```

5. Configurez votre base de données dans le fichier .env :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pixel_hub
DB_USERNAME=pixel_hub
DB_PASSWORD=1234
```

6. Créez la base de données et l'utilisateur :
```bash
mysql -u root -p
```

```sql
CREATE DATABASE pixel_hub;
CREATE USER 'pixel_hub'@'localhost' IDENTIFIED BY '1234';
GRANT ALL PRIVILEGES ON pixel_hub.* TO 'pixel_hub'@'localhost';
FLUSH PRIVILEGES;
```

7. Exécutez les migrations :
```bash
php artisan migrate
```

8. Configurez Apache :
```bash
sudo cp pixel-hub.conf /etc/apache2/sites-available/
sudo a2ensite pixel-hub
sudo systemctl restart apache2
```

## Utilisation

1. Accédez à l'application via votre navigateur :
```
http://localhost
```

2. Connectez-vous avec les identifiants par défaut :
- Email : admin@example.com
- Mot de passe : password

## Contribution

Les contributions sont les bienvenues ! N'hésitez pas à ouvrir une issue ou à soumettre une pull request.

## Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## Fonctionnalités

- 🎮 Gestion des applications et jeux
- 📊 Tableau de bord personnalisable
- 🔄 Mises à jour automatiques
- 💾 Système de sauvegarde
- 🌓 Thème clair/sombre
- 📱 Interface responsive
- 🔒 Sécurité renforcée
- ⚡ Performance optimisée

## Configuration

### Base de données
Modifiez le fichier `.env` pour configurer votre base de données :
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=pixel_hub
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe
```

### SSL
Pour activer SSL en production :
```bash
sudo ./scripts/ssl-setup.sh
```

## Développement

### Structure du projet
```
pixel-hub-web/
├── config/             # Fichiers de configuration
├── public/             # Fichiers publics
│   ├── css/           # Styles CSS
│   ├── js/            # Scripts JavaScript
│   └── img/           # Images
├── src/               # Code source
│   ├── Controllers/   # Contrôleurs
│   ├── Models/        # Modèles
│   ├── Views/         # Vues
│   └── Core/          # Classes principales
├── tests/             # Tests unitaires et d'intégration
└── scripts/           # Scripts d'installation et maintenance
```

### Tests
```bash
# Exécuter tous les tests
./scripts/run_tests.sh

# Exécuter une suite de tests spécifique
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Integration
./vendor/bin/phpunit --testsuite Security
```

## Sécurité

- Toutes les routes sont protégées par authentification
- Protection contre les injections SQL
- Protection XSS
- Protection CSRF
- Validation des entrées
- Gestion sécurisée des sessions

## Maintenance

### Mise à jour
```bash
git pull
composer update
php artisan migrate
```

### Sauvegarde
```bash
./scripts/backup.sh
```

### Désinstallation
```bash
curl -s https://raw.githubusercontent.com/Maxymou/pixel-hub-web/main/scripts/uninstall.sh | sudo bash
```

Cette commande va :
1. Arrêter les services Apache et MySQL
2. Supprimer l'application et ses fichiers
3. Supprimer la base de données
4. Supprimer les configurations Apache
5. Supprimer les configurations PHP
6. Supprimer les dépendances installées

### Désinstallation manuelle

Si vous préférez désinstaller manuellement, exécutez ces commandes :

```bash
# Arrêter les services
sudo systemctl stop apache2
sudo systemctl stop mysql

# Supprimer l'application
sudo rm -rf /var/www/pixel-hub

# Supprimer la base de données
sudo mysql -e "DROP DATABASE IF EXISTS pixel_hub; DROP USER IF EXISTS 'pixel_hub'@'localhost';"

# Supprimer les configurations
sudo rm -f /etc/apache2/sites-available/pixel-hub.conf
sudo rm -f /etc/php/8.1/apache2/conf.d/99-pixel-hub.ini
sudo rm -f /etc/security/limits.d/pixel-hub.conf

# Désinstaller les paquets
sudo apt-get remove -y \
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
    php8.1-imagick \
    mysql-server \
    apache2

# Supprimer les fichiers de configuration restants
sudo apt-get purge -y \
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
    php8.1-imagick \
    mysql-server \
    apache2

# Nettoyer les paquets non utilisés
sudo apt-get autoremove -y
sudo apt-get clean

# Supprimer le dépôt PHP
sudo rm -f /etc/apt/sources.list.d/php.list
sudo rm -f /etc/apt/trusted.gpg.d/php.gpg
```

### Vérification de la désinstallation

Pour vérifier que tout a été correctement supprimé :

```bash
# Vérifier que les services sont arrêtés
sudo systemctl status apache2
sudo systemctl status mysql

# Vérifier que les fichiers sont supprimés
ls -la /var/www/pixel-hub 2>/dev/null || echo "Le dossier de l'application a été supprimé"
ls -la /etc/apache2/sites-available/pixel-hub.conf 2>/dev/null || echo "La configuration Apache a été supprimée"
ls -la /etc/php/8.1/apache2/conf.d/99-pixel-hub.ini 2>/dev/null || echo "La configuration PHP a été supprimée"
```

## Contribution

1. Fork le projet
2. Créez une branche (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

## Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## Support

Pour toute question ou problème :
- Ouvrez une issue sur GitHub
- Contactez l'équipe de développement
- Consultez la documentation

## Crédits

- Développé par [Votre Nom/Organisation]
- Utilise Bootstrap 5 pour l'interface
- Utilise Font Awesome pour les icônes
- Inspiré par [Projets similaires] 