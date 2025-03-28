<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;

// Définition des constantes
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/src');

// Gestion des erreurs en développement
if ($_ENV['APP_DEBUG'] ?? false) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Démarrage de l'application
$app = Application::getInstance();
$app->run(); 