# PixelHub

Application web basée sur la stack LEMP (Nginx, MariaDB, PHP).

## Prérequis

- Une installation fonctionnelle de la stack LEMP (Nginx, MariaDB, PHP)
- Git
- Composer

## Installation

Pour installer PixelHub sur une stack LEMP existante, exécutez la commande suivante :

```bash
curl -s https://raw.githubusercontent.com/Maxymou/pixel-hub-web/main/install-pixelhub.sh | sudo bash
```

Ce script va :
1. Vérifier que la stack LEMP est installée et fonctionnelle
2. Vérifier les versions de Nginx, MariaDB et PHP
3. Vérifier que les services sont en cours d'exécution
4. Installer PixelHub et toutes ses dépendances
5. Configurer Nginx pour PixelHub
6. Créer la base de données
7. Configurer l'environnement
8. Optimiser l'application

## Installation manuelle

Si vous préférez installer manuellement :

1. Clonez le repository
2. Copiez le fichier `pixelhub.conf` dans le dossier `/etc/nginx/sites-available/`
3. Créez un lien symbolique dans `/etc/nginx/sites-enabled/`
4. Créez la base de données :
   ```sql
   CREATE DATABASE pixelhub;
   ```
5. Configurez les variables d'environnement dans le fichier `.env`
6. Exécutez les migrations :
   ```bash
   php artisan migrate
   ```
7. Générez la clé d'application :
   ```bash
   php artisan key:generate
   ```

## Configuration

### Nginx
Le fichier de configuration Nginx est fourni dans `pixelhub.conf`. Il doit être placé dans :
- `/etc/nginx/sites-available/pixelhub.conf`
- `/etc/nginx/sites-enabled/pixelhub.conf` (lien symbolique)

### MariaDB
La base de données est configurée avec les paramètres suivants :
- Host: 127.0.0.1
- Port: 3306
- Database: pixelhub
- Username: root
- Password: (vide par défaut)

### PHP
Les extensions PHP suivantes sont requises :
- php8.2-mysql
- php8.2-xml
- php8.2-curl
- php8.2-mbstring
- php8.2-zip
- php8.2-bcmath

## Désinstallation

Pour désinstaller l'application, exécutez le fichier `uninstall.sh`.

## Support

Pour toute question ou problème, veuillez créer une issue dans le repository.

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