# Pixel Hub V2 - Script d'installation LEMP

Ce script automatise l'installation d'un environnement LEMP (Linux, Nginx, MariaDB, PHP) sur Raspberry Pi OS 64 bits.

## Prérequis

- Raspberry Pi avec Raspberry Pi OS 64 bits
- Connexion Internet active
- Accès root (sudo)

## Fonctionnalités

- Installation automatique de Nginx, PHP-FPM et MariaDB
- Configuration optimisée pour Raspberry Pi
- Détection automatique de la version de PHP
- Configuration sécurisée de MariaDB
- Création d'une page de test PHP
- Génération de rapports d'installation/désinstallation
- Script de désinstallation complet

## Installation

### Méthode 1 : Téléchargement direct

```bash
# Téléchargement du script d'installation
curl -O https://raw.githubusercontent.com/Maxymou/Pixel-Hub-V2/main/install-lemp-server.sh

# Rendre le script exécutable
chmod +x install-lemp-server.sh

# Exécuter le script
sudo ./install-lemp-server.sh
```

### Méthode 2 : Installation directe via curl

```bash
curl -s https://raw.githubusercontent.com/Maxymou/Pixel-Hub-V2/main/install-lemp-server.sh | sudo bash
```

## Désinstallation

### Méthode 1 : Désinstallation interactive

```bash
# Téléchargement du script de désinstallation
curl -O https://raw.githubusercontent.com/Maxymou/Pixel-Hub-V2/main/uninstall-lemp-server.sh

# Rendre le script exécutable
chmod +x uninstall-lemp-server.sh

# Exécuter le script
sudo ./uninstall-lemp-server.sh
```

### Méthode 2 : Désinstallation forcée

```bash
curl -s https://raw.githubusercontent.com/Maxymou/Pixel-Hub-V2/main/uninstall-lemp-server.sh | sudo bash -s -- -f
```

## Configuration

### PHP-FPM
- Version : Détectée automatiquement
- Socket : `/var/run/php/php[VERSION]-fpm.sock`
- Configuration : `/etc/php/[VERSION]/fpm/php.ini`
- Pool : `/etc/php/[VERSION]/fpm/pool.d/www.conf`

### Nginx
- Configuration : `/etc/nginx/sites-available/default`
- Document root : `/var/www/html`
- Port : 80

### MariaDB
- Port : 3306
- Utilisateur root : root
- Mot de passe : root_password (à changer après l'installation)

## Vérification

Après l'installation, vous pouvez vérifier que tout fonctionne en accédant à :
```
http://votre_ip/info.php
```

## Rapports

Les scripts génèrent des rapports détaillés dans :
- Installation : `/var/log/lemp-installation-report-[DATE].log`
- Désinstallation : `/var/log/lemp-uninstallation-report-[DATE].log`

## Sécurité

- Le script configure MariaDB avec des paramètres de sécurité de base
- Les permissions des fichiers sont correctement définies
- Les services sont configurés pour écouter uniquement sur les interfaces nécessaires

## Support

Pour toute question ou problème, veuillez ouvrir une issue sur GitHub.

## Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails. 