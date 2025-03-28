<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Update\UpdateManager;

// Charger les variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Initialiser le gestionnaire de mises à jour
$updateManager = new UpdateManager();

try {
    // Exécuter les mises à jour planifiées
    $updateManager->runScheduledUpdates();
    
    echo "Mises à jour planifiées exécutées avec succès\n";
} catch (\Exception $e) {
    echo "Erreur lors de l'exécution des mises à jour planifiées: " . $e->getMessage() . "\n";
    exit(1);
} 