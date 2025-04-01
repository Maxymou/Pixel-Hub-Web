@echo off
echo Desinstallation de PixelHub...

REM Arrêt des services
net stop nginx
net stop mysql

REM Suppression des fichiers de l'application
rmdir /s /q "C:\nginx\html\pixelhub"

REM Suppression des configurations
del /f /q "C:\nginx\conf\sites-enabled\pixelhub.conf"
del /f /q "C:\nginx\conf\sites-available\pixelhub.conf"

REM Suppression de la base de données
mysql -u root -e "DROP DATABASE IF EXISTS pixelhub;"

REM Suppression des logs
del /f /q "C:\nginx\logs\pixelhub-*.log"

echo Desinstallation terminee.
pause 