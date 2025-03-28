<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Core\Application;
use App\Core\Database;
use App\Core\Cache\CacheManager;
use App\Controllers\DashboardController;
use App\Core\Stats\StatsCollector;
use App\Core\Process\ProcessManager;
use App\Core\Backup\BackupManager;
use App\Core\Update\UpdateManager;

class DashboardIntegrationTest extends TestCase
{
    private $app;
    private $db;
    private $cache;
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Initialiser l'application
        $this->app = new Application();
        
        // Configurer la base de données de test
        $this->db = new Database([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => ''
        ]);
        
        // Créer les tables nécessaires
        $this->createTestTables();
        
        // Configurer le cache
        $this->cache = new CacheManager();
        
        // Créer le contrôleur avec les dépendances réelles
        $this->controller = new DashboardController(
            new StatsCollector(),
            new ProcessManager(),
            new BackupManager(),
            new UpdateManager(),
            $this->db,
            $this->cache
        );
    }

    private function createTestTables()
    {
        // Créer la table des utilisateurs
        $this->db->query("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                role TEXT NOT NULL,
                last_login DATETIME
            )
        ");

        // Créer la table des sessions
        $this->db->query("
            CREATE TABLE sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                token TEXT NOT NULL,
                expires_at DATETIME NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");

        // Créer la table des notifications
        $this->db->query("
            CREATE TABLE notifications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                message TEXT NOT NULL,
                created_at DATETIME NOT NULL,
                read BOOLEAN DEFAULT 0
            )
        ");

        // Créer la table des applications
        $this->db->query("
            CREATE TABLE apps (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                type TEXT NOT NULL,
                status TEXT NOT NULL,
                last_used DATETIME
            )
        ");

        // Créer la table des sauvegardes
        $this->db->query("
            CREATE TABLE backups (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                created_at DATETIME NOT NULL,
                size INTEGER NOT NULL,
                description TEXT
            )
        ");
    }

    public function testDashboardDataFlow()
    {
        // 1. Insérer des données de test
        $this->insertTestData();

        // 2. Tester la récupération des statistiques
        $stats = $this->controller->getStats();
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('cpu', $stats);
        $this->assertArrayHasKey('memory', $stats);
        $this->assertArrayHasKey('disk', $stats);
        $this->assertArrayHasKey('network', $stats);

        // 3. Tester la récupération des applications récentes
        $apps = $this->controller->getRecentApps();
        $this->assertIsArray($apps);
        $this->assertCount(2, $apps);
        $this->assertEquals('Test App 1', $apps[0]['name']);

        // 4. Tester la récupération des notifications
        $notifications = $this->controller->getNotifications();
        $this->assertIsArray($notifications);
        $this->assertCount(2, $notifications);
        $this->assertEquals('Test Notification 1', $notifications[0]['title']);

        // 5. Tester la récupération des sauvegardes
        $backups = $this->controller->getBackups();
        $this->assertIsArray($backups);
        $this->assertCount(1, $backups);
        $this->assertEquals('Test Backup', $backups[0]['description']);

        // 6. Tester l'installation d'une mise à jour
        $updateResult = $this->controller->installUpdate(1);
        $this->assertEquals(['success' => true], $updateResult);

        // 7. Tester la restauration d'une sauvegarde
        $restoreResult = $this->controller->restoreBackup(1);
        $this->assertEquals(['success' => true], $restoreResult);

        // 8. Tester la suppression d'une sauvegarde
        $deleteResult = $this->controller->deleteBackup(1);
        $this->assertEquals(['success' => true], $deleteResult);
    }

    private function insertTestData()
    {
        // Insérer des notifications
        $this->db->query("
            INSERT INTO notifications (title, message, created_at, read)
            VALUES 
            ('Test Notification 1', 'Test Message 1', datetime('now'), 0),
            ('Test Notification 2', 'Test Message 2', datetime('now'), 1)
        ");

        // Insérer des applications
        $this->db->query("
            INSERT INTO apps (name, type, status, last_used)
            VALUES 
            ('Test App 1', 'game', 'running', datetime('now')),
            ('Test App 2', 'utility', 'stopped', datetime('now'))
        ");

        // Insérer une sauvegarde
        $this->db->query("
            INSERT INTO backups (created_at, size, description)
            VALUES (datetime('now'), 1024, 'Test Backup')
        ");
    }

    public function testCacheIntegration()
    {
        // 1. Récupérer les données une première fois
        $firstCall = $this->controller->getStats();
        
        // 2. Modifier les données sous-jacentes
        $this->db->query("
            INSERT INTO notifications (title, message, created_at, read)
            VALUES ('Cache Test', 'Cache Message', datetime('now'), 0)
        ");
        
        // 3. Récupérer les données une deuxième fois
        $secondCall = $this->controller->getStats();
        
        // 4. Vérifier que les données sont identiques (grâce au cache)
        $this->assertEquals($firstCall, $secondCall);
    }

    public function testErrorHandlingIntegration()
    {
        // 1. Simuler une erreur de base de données
        $this->db->query("DROP TABLE notifications");
        
        // 2. Tester la gestion de l'erreur
        $notifications = $this->controller->getNotifications();
        $this->assertIsArray($notifications);
        $this->assertEmpty($notifications);
    }

    public function testPerformanceIntegration()
    {
        // 1. Mesurer le temps d'exécution
        $startTime = microtime(true);
        
        // 2. Exécuter plusieurs opérations
        for ($i = 0; $i < 10; $i++) {
            $this->controller->getStats();
            $this->controller->getRecentApps();
            $this->controller->getNotifications();
        }
        
        // 3. Calculer le temps total
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // 4. Vérifier que le temps d'exécution est acceptable
        $this->assertLessThan(2.0, $executionTime); // Moins de 2 secondes
    }
} 