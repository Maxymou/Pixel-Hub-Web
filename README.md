# Pixel Hub Web

Pixel Hub Web est une application web pour gérer vos pixels et vos projets.

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- MySQL 5.7 ou supérieur
- Apache 2.4 ou supérieur

## Installation Automatique

Pour une installation rapide et automatique, utilisez le script d'installation :

```bash
wget https://raw.githubusercontent.com/Maxymou/pixel-hub-web/main/install-auto.sh
chmod +x install-auto.sh
./install-auto.sh
```

Ce script va :
- Installer tous les prérequis nécessaires
- Configurer Apache et PHP
- Créer la base de données
- Configurer les permissions
- Installer les dépendances
- Lancer l'application

## Installation Manuelle

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
sudo rm -rf /var/www/pixel-hub-web

# Supprimer la base de données
sudo mysql -e "DROP DATABASE IF EXISTS pixel_hub;"
sudo mysql -e "DROP USER IF EXISTS 'pixel_hub'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Supprimer les configurations
sudo rm -f /etc/apache2/sites-available/pixel-hub.conf
sudo rm -f /etc/php/conf.d/99-pixel-hub.ini
sudo rm -f /etc/security/limits.d/pixel-hub.conf

# Désinstaller les paquets
sudo apt remove -y apache2 php8.2* mysql-server
sudo apt autoremove -y
sudo apt clean

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

# Pixel Hub Web - Installation

Ce guide vous permettra d'installer ou de désinstaller Pixel Hub Web sur votre Raspberry Pi.

## Prérequis

- Un Raspberry Pi avec Raspberry Pi OS (Debian)
- Une connexion Internet
- Les droits d'administration (sudo)

## Installation

1. Téléchargez le script d'installation :
```bash
wget https://raw.githubusercontent.com/Maxymou/pixel-hub-web/main/install-auto.sh
```

2. Rendez le script exécutable :
```bash
chmod +x install-auto.sh
```

3. Exécutez le script d'installation :
```bash
sudo ./install-auto.sh
```

Le script va :
- Mettre à jour votre système
- Installer Apache
- Configurer les permissions Apache
- Installer PHP
- Installer MySQL
- Installer PHPMyAdmin
- Configurer tous les services

Une fois l'installation terminée, vous pourrez accéder à :
- Site web : http://127.0.0.1
- PHPMyAdmin : http://127.0.0.1/phpmyadmin

## Désinstallation

Pour désinstaller complètement l'application, exécutez :
```bash
sudo ./install-auto.sh uninstall
```

Cette commande va :
- Arrêter les services (Apache, MySQL)
- Supprimer la base de données
- Supprimer les fichiers de l'application
- Supprimer les configurations Apache et PHP
- Désinstaller les paquets
- Nettoyer les logs et le cache

## Vérification de l'installation

Pour vérifier que tout fonctionne correctement :

1. Vérifiez Apache :
```bash
wget -O check_apache.html http://127.0.0.1
cat ./check_apache.html
```

2. Vérifiez PHP :
```bash
php -v
```

3. Vérifiez MySQL :
```bash
sudo mysql --user=root -p
```

4. Vérifiez PHPMyAdmin :
- Ouvrez votre navigateur
- Accédez à http://127.0.0.1/phpmyadmin
- Connectez-vous avec :
  - Utilisateur : root
  - Mot de passe : password

## En cas de problème

Si vous rencontrez des problèmes :

1. Vérifiez les logs Apache :
```bash
sudo tail -f /var/log/apache2/error.log
```

2. Vérifiez les logs MySQL :
```bash
sudo tail -f /var/log/mysql/error.log
```

3. Vérifiez l'état des services :
```bash
sudo systemctl status apache2
sudo systemctl status mysql
```

## Support

Pour toute question ou problème, n'hésitez pas à ouvrir une issue sur GitHub. 