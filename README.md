# Pixel Hub Web

Pixel Hub Web est une application web responsive permettant de gérer et lancer des applications et jeux depuis une interface unifiée. Elle est conçue pour fonctionner sur tous les appareils (desktop, tablette, smartphone) et offre une expérience utilisateur optimisée.

## Installation rapide

Pour installer Pixel Hub Web en une seule commande, exécutez :

```bash
curl -s https://raw.githubusercontent.com/Maxymou/pixel-hub-web/main/install-auto.sh | sudo bash
```

Cette commande va :
1. Installer tous les prérequis nécessaires
2. Configurer l'environnement
3. Installer l'application
4. Configurer la base de données
5. Créer un utilisateur administrateur

Suivez les instructions à l'écran pour compléter l'installation.

## Installation sur Raspberry Pi

### Prérequis
- Raspberry Pi 3 ou supérieur (recommandé)
- Raspberry Pi OS Lite (recommandé)
- Carte microSD de 16GB minimum
- Alimentation adaptée (5V/2.5A minimum)

### Étapes d'installation

1. **Préparation de la carte microSD**
   - Téléchargez Raspberry Pi OS Lite depuis [raspberrypi.org](https://www.raspberrypi.org/software/operating-systems/)
   - Utilisez Raspberry Pi Imager pour graver l'image
   - Activez SSH dans les options avancées de Raspberry Pi Imager

2. **Configuration initiale**
   ```bash
   # Connectez-vous à votre Raspberry Pi via SSH
   ssh pi@<adresse_ip_raspberry_pi>
   
   # Changez le mot de passe par défaut
   passwd
   
   # Mettez à jour le système
   sudo apt update && sudo apt upgrade -y
   ```

3. **Installation de Pixel Hub Web**
   ```bash
   curl -s https://raw.githubusercontent.com/Maxymou/pixel-hub-web/main/install-auto.sh | sudo bash
   ```

4. **Configuration du réseau**
   - Notez l'adresse IP de votre Raspberry Pi
   - Vous pourrez accéder à Pixel Hub Web depuis n'importe quel appareil sur votre réseau en utilisant cette adresse IP

### Optimisations pour Raspberry Pi
Le script d'installation détecte automatiquement si vous êtes sur un Raspberry Pi et applique les optimisations suivantes :
- Réduction de la consommation mémoire
- Optimisation des paramètres PHP
- Configuration adaptée pour les ressources limitées

### Dépannage
Si vous rencontrez des problèmes de performance :
1. Vérifiez la température du Raspberry Pi : `vcgencmd measure_temp`
2. Surveillez l'utilisation mémoire : `free -m`
3. Vérifiez les logs : `sudo journalctl -u apache2`

## Installation manuelle

Si vous préférez une installation manuelle, suivez ces étapes :

## Fonctionnalités

- 🎮 Gestion des applications et jeux
- 📊 Tableau de bord personnalisable
- 🔄 Mises à jour automatiques
- 💾 Système de sauvegarde
- 🌓 Thème clair/sombre
- 📱 Interface responsive
- 🔒 Sécurité renforcée
- ⚡ Performance optimisée

## Prérequis

- PHP 8.1 ou supérieur
- MySQL 5.7 ou supérieur
- Apache 2.4 ou supérieur
- Composer
- Git

## Installation

1. **Cloner le dépôt**
```bash
git clone https://github.com/Maxymou/pixel-hub-web.git
cd pixel-hub-web
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Configurer l'environnement**
```bash
cp .env.example .env
```

4. **Exécuter le script d'installation**
```bash
chmod +x install.sh scripts/preinstall.sh scripts/uninstall.sh
sudo ./scripts/preinstall.sh
sudo ./install.sh
```

5. **Configurer les permissions**
```bash
sudo chown -R www-data:www-data public/uploads
sudo chmod -R 755 public/uploads
```

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

## Utilisation

1. **Accéder à l'application**
- Ouvrez votre navigateur
- Accédez à `http://votre-domaine.com`
- Connectez-vous avec les identifiants par défaut :
  - Utilisateur : admin
  - Mot de passe : admin123

2. **Première connexion**
- Changez immédiatement le mot de passe administrateur
- Configurez les paramètres de base
- Ajoutez vos premières applications

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