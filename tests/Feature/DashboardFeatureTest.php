<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Core\Application;
use App\Core\Database;
use App\Core\Cache\CacheManager;
use App\Controllers\DashboardController;
use App\Core\Auth\AuthManager;
use App\Core\Stats\StatsCollector;
use App\Core\Process\ProcessManager;
use App\Core\Backup\BackupManager;
use App\Core\Update\UpdateManager;

class DashboardFeatureTest extends TestCase
{
    private $app;
    private $db;
    private $cache;
    private $controller;
    private $auth;
    private $statsCollector;
    private $processManager;
    private $backupManager;
    private $updateManager;

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
        
        // Initialiser les composants
        $this->auth = new AuthManager();
        $this->statsCollector = new StatsCollector();
        $this->processManager = new ProcessManager();
        $this->backupManager = new BackupManager();
        $this->updateManager = new UpdateManager();
        
        // Créer le contrôleur avec les dépendances
        $this->controller = new DashboardController(
            $this->statsCollector,
            $this->processManager,
            $this->backupManager,
            $this->updateManager,
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

    public function testUserLoginScenario()
    {
        // 1. Créer un utilisateur test
        $this->createTestUser('test_user', 'password123', 'user');
        
        // 2. Tester la connexion
        $loginResult = $this->auth->login('test_user', 'password123');
        $this->assertTrue($loginResult);
        
        // 3. Vérifier que l'utilisateur peut accéder au tableau de bord
        $stats = $this->controller->getStats();
        $this->assertIsArray($stats);
        
        // 4. Vérifier que l'utilisateur ne peut pas accéder aux fonctionnalités admin
        $this->expectException(\App\Core\Security\SecurityException::class);
        $this->controller->installUpdate(1);
    }

    public function testApplicationManagementScenario()
    {
        // 1. Créer et connecter un utilisateur admin
        $this->createTestUser('admin', 'password123', 'admin');
        $this->auth->login('admin', 'password123');
        
        // 2. Ajouter une nouvelle application
        $appData = [
            'name' => 'Test App',
            'type' => 'game',
            'status' => 'stopped'
        ];
        $addResult = $this->controller->addApplication($appData);
        $this->assertTrue($addResult);
        
        // 3. Vérifier que l'application apparaît dans la liste
        $apps = $this->controller->getRecentApps();
        $this->assertCount(1, $apps);
        $this->assertEquals('Test App', $apps[0]['name']);
        
        // 4. Démarrer l'application
        $startResult = $this->controller->startApplication(1);
        $this->assertTrue($startResult);
        
        // 5. Vérifier le statut mis à jour
        $apps = $this->controller->getRecentApps();
        $this->assertEquals('running', $apps[0]['status']);
        
        // 6. Arrêter l'application
        $stopResult = $this->controller->stopApplication(1);
        $this->assertTrue($stopResult);
        
        // 7. Vérifier le statut final
        $apps = $this->controller->getRecentApps();
        $this->assertEquals('stopped', $apps[0]['status']);
    }

    public function testBackupManagementScenario()
    {
        // 1. Créer et connecter un utilisateur admin
        $this->createTestUser('admin', 'password123', 'admin');
        $this->auth->login('admin', 'password123');
        
        // 2. Créer une sauvegarde
        $backupData = [
            'description' => 'Test Backup',
            'size' => 1024
        ];
        $createResult = $this->controller->createBackup($backupData);
        $this->assertTrue($createResult);
        
        // 3. Vérifier que la sauvegarde apparaît dans la liste
        $backups = $this->controller->getBackups();
        $this->assertCount(1, $backups);
        $this->assertEquals('Test Backup', $backups[0]['description']);
        
        // 4. Restaurer la sauvegarde
        $restoreResult = $this->controller->restoreBackup(1);
        $this->assertTrue($restoreResult);
    }

    public function testUpdateManagementScenario()
    {
        // 1. Créer et connecter un utilisateur admin
        $this->createTestUser('admin', 'password123', 'admin');
        $this->auth->login('admin', 'password123');
        
        // 2. Vérifier les mises à jour disponibles
        $updates = $this->controller->checkUpdates();
        $this->assertIsArray($updates);
        
        // 3. Installer une mise à jour
        if (!empty($updates)) {
            $installResult = $this->controller->installUpdate($updates[0]['id']);
            $this->assertTrue($installResult);
        }
    }

    public function testNotificationManagementScenario()
    {
        // 1. Créer et connecter un utilisateur
        $this->createTestUser('test_user', 'password123', 'user');
        $this->auth->login('test_user', 'password123');
        
        // 2. Créer une notification
        $notificationData = [
            'title' => 'Test Notification',
            'message' => 'Test Message'
        ];
        $createResult = $this->controller->createNotification($notificationData);
        $this->assertTrue($createResult);
        
        // 3. Vérifier que la notification apparaît
        $notifications = $this->controller->getNotifications();
        $this->assertCount(1, $notifications);
        $this->assertEquals('Test Notification', $notifications[0]['title']);
        
        // 4. Marquer la notification comme lue
        $markReadResult = $this->controller->markNotificationAsRead(1);
        $this->assertTrue($markReadResult);
        
        // 5. Vérifier que la notification est marquée comme lue
        $notifications = $this->controller->getNotifications();
        $this->assertTrue($notifications[0]['read']);
    }

    public function testErrorHandlingScenario()
    {
        // 1. Créer et connecter un utilisateur
        $this->createTestUser('test_user', 'password123', 'user');
        $this->auth->login('test_user', 'password123');
        
        // 2. Tester la gestion des erreurs
        try {
            $this->controller->startApplication(999); // ID inexistant
            $this->fail('Une exception aurait dû être levée');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Application not found', $e->getMessage());
        }
    }

    private function createTestUser($username, $password, $role)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $this->db->query("
            INSERT INTO users (username, password, role, last_login)
            VALUES (?, ?, ?, datetime('now'))
        ", [$username, $hashedPassword, $role]);
    }
} 