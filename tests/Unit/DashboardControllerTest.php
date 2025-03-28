<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Controllers\DashboardController;
use App\Core\Stats\StatsCollector;
use App\Core\Process\ProcessManager;
use App\Core\Backup\BackupManager;
use App\Core\Update\UpdateManager;
use App\Core\Database;
use App\Core\Cache\CacheManager;
use Mockery;

class DashboardControllerTest extends TestCase
{
    private $controller;
    private $statsCollector;
    private $processManager;
    private $backupManager;
    private $updateManager;
    private $db;
    private $cache;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer les mocks
        $this->statsCollector = Mockery::mock(StatsCollector::class);
        $this->processManager = Mockery::mock(ProcessManager::class);
        $this->backupManager = Mockery::mock(BackupManager::class);
        $this->updateManager = Mockery::mock(UpdateManager::class);
        $this->db = Mockery::mock(Database::class);
        $this->cache = Mockery::mock(CacheManager::class);

        // Créer le contrôleur avec les mocks
        $this->controller = new DashboardController(
            $this->statsCollector,
            $this->processManager,
            $this->backupManager,
            $this->updateManager,
            $this->db,
            $this->cache
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGetStats()
    {
        // Préparer les données de test
        $expectedStats = [
            'cpu' => 45.5,
            'memory' => 60.2,
            'disk' => 75.8,
            'network' => 30.1
        ];

        // Configurer le mock
        $this->statsCollector
            ->shouldReceive('getStats')
            ->once()
            ->andReturn($expectedStats);

        // Exécuter le test
        $response = $this->controller->getStats();

        // Vérifier le résultat
        $this->assertEquals($expectedStats, $response);
    }

    public function testGetRecentApps()
    {
        // Préparer les données de test
        $expectedApps = [
            [
                'id' => 1,
                'name' => 'Test App',
                'type' => 'game',
                'status' => 'running',
                'lastUsed' => '2024-03-20 10:00:00'
            ]
        ];

        // Configurer le mock
        $this->processManager
            ->shouldReceive('getRecentApps')
            ->once()
            ->andReturn($expectedApps);

        // Exécuter le test
        $response = $this->controller->getRecentApps();

        // Vérifier le résultat
        $this->assertEquals($expectedApps, $response);
    }

    public function testGetNotifications()
    {
        // Préparer les données de test
        $expectedNotifications = [
            [
                'id' => 1,
                'title' => 'Test Notification',
                'message' => 'Test Message',
                'created_at' => '2024-03-20 10:00:00',
                'read' => false
            ]
        ];

        // Configurer le mock
        $this->db
            ->shouldReceive('query')
            ->once()
            ->andReturn($expectedNotifications);

        // Exécuter le test
        $response = $this->controller->getNotifications();

        // Vérifier le résultat
        $this->assertEquals($expectedNotifications, $response);
    }

    public function testCheckUpdates()
    {
        // Préparer les données de test
        $expectedUpdates = [
            [
                'id' => 1,
                'name' => 'Test Update',
                'version' => '1.0.1',
                'description' => 'Test Description'
            ]
        ];

        // Configurer le mock
        $this->updateManager
            ->shouldReceive('checkForUpdates')
            ->once()
            ->andReturn($expectedUpdates);

        // Exécuter le test
        $response = $this->controller->checkUpdates();

        // Vérifier le résultat
        $this->assertEquals($expectedUpdates, $response);
    }

    public function testInstallUpdate()
    {
        // Préparer les données de test
        $updateId = 1;

        // Configurer le mock
        $this->updateManager
            ->shouldReceive('installUpdate')
            ->with($updateId)
            ->once()
            ->andReturn(true);

        // Exécuter le test
        $response = $this->controller->installUpdate($updateId);

        // Vérifier le résultat
        $this->assertEquals(['success' => true], $response);
    }

    public function testGetBackups()
    {
        // Préparer les données de test
        $expectedBackups = [
            [
                'id' => 1,
                'created_at' => '2024-03-20 10:00:00',
                'size' => 1024,
                'description' => 'Test Backup'
            ]
        ];

        // Configurer le mock
        $this->backupManager
            ->shouldReceive('listBackups')
            ->once()
            ->andReturn($expectedBackups);

        // Exécuter le test
        $response = $this->controller->getBackups();

        // Vérifier le résultat
        $this->assertEquals($expectedBackups, $response);
    }

    public function testRestoreBackup()
    {
        // Préparer les données de test
        $backupId = 1;

        // Configurer le mock
        $this->backupManager
            ->shouldReceive('restoreBackup')
            ->with($backupId)
            ->once()
            ->andReturn(true);

        // Exécuter le test
        $response = $this->controller->restoreBackup($backupId);

        // Vérifier le résultat
        $this->assertEquals(['success' => true], $response);
    }

    public function testDeleteBackup()
    {
        // Préparer les données de test
        $backupId = 1;

        // Configurer le mock
        $this->backupManager
            ->shouldReceive('deleteBackup')
            ->with($backupId)
            ->once()
            ->andReturn(true);

        // Exécuter le test
        $response = $this->controller->deleteBackup($backupId);

        // Vérifier le résultat
        $this->assertEquals(['success' => true], $response);
    }

    public function testErrorHandling()
    {
        // Configurer le mock pour simuler une erreur
        $this->statsCollector
            ->shouldReceive('getStats')
            ->once()
            ->andThrow(new \Exception('Test error'));

        // Exécuter le test
        $response = $this->controller->getStats();

        // Vérifier le résultat
        $this->assertEquals(['error' => 'Test error'], $response);
    }
} 