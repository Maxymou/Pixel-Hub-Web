<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Backup\BackupScheduler;

try {
    // Initialiser le planificateur
    $scheduler = new BackupScheduler();

    // Exécuter les sauvegardes planifiées
    $scheduler->runScheduledBackups();

    echo "Sauvegardes planifiées exécutées avec succès\n";

} catch (\Exception $e) {
    echo "Erreur lors de l'exécution des sauvegardes planifiées: " . $e->getMessage() . "\n";
    exit(1);
} 