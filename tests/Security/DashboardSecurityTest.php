<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use App\Core\Application;
use App\Core\Database;
use App\Core\Cache\CacheManager;
use App\Controllers\DashboardController;
use App\Core\Auth\AuthManager;
use App\Core\Security\InputValidator;
use App\Core\Security\XSSProtection;
use App\Core\Stats\StatsCollector;
use App\Core\Process\ProcessManager;
use App\Core\Backup\BackupManager;
use App\Core\Update\UpdateManager;

class DashboardSecurityTest extends TestCase
{
    private $app;
    private $db;
    private $cache;
    private $controller;
    private $auth;
    private $validator;
    private $xssProtection;

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
        
        // Initialiser les composants de sécurité
        $this->auth = new AuthManager();
        $this->validator = new InputValidator();
        $this->xssProtection = new XSSProtection();
        
        // Créer le contrôleur avec les dépendances
        $this->controller = new DashboardController(
            new StatsCollector(),
            new ProcessManager(),
            new BackupManager(),
            new UpdateManager(),
            $this->db,
            $this->cache
        );
        
        // Injecter les composants de sécurité
        $this->controller->setAuth($this->auth);
        $this->controller->setValidator($this->validator);
        $this->controller->setXSSProtection($this->xssProtection);
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
    }

    public function testAuthenticationRequired()
    {
        // 1. Tester l'accès sans authentification
        $this->expectException(\App\Core\Security\SecurityException::class);
        $this->controller->getStats();
    }

    public function testRoleBasedAccessControl()
    {
        // 1. Créer un utilisateur avec un rôle limité
        $this->createTestUser('limited_user', 'password123', 'user');
        
        // 2. Authentifier l'utilisateur
        $this->auth->login('limited_user', 'password123');
        
        // 3. Tester l'accès aux fonctionnalités restreintes
        $this->expectException(\App\Core\Security\SecurityException::class);
        $this->controller->installUpdate(1);
    }

    public function testInputValidation()
    {
        // 1. Créer un utilisateur admin
        $this->createTestUser('admin', 'password123', 'admin');
        
        // 2. Authentifier l'utilisateur
        $this->auth->login('admin', 'password123');
        
        // 3. Tester la validation des entrées malveillantes
        $maliciousInput = [
            'name' => '<script>alert("xss")</script>',
            'type' => 'game; DROP TABLE users;',
            'status' => 'running" OR "1"="1'
        ];
        
        // 4. Vérifier que les entrées sont nettoyées
        $cleanedInput = $this->validator->sanitize($maliciousInput);
        $this->assertNotEquals($maliciousInput['name'], $cleanedInput['name']);
        $this->assertNotEquals($maliciousInput['type'], $cleanedInput['type']);
        $this->assertNotEquals($maliciousInput['status'], $cleanedInput['status']);
    }

    public function testXSSProtection()
    {
        // 1. Créer un utilisateur admin
        $this->createTestUser('admin', 'password123', 'admin');
        
        // 2. Authentifier l'utilisateur
        $this->auth->login('admin', 'password123');
        
        // 3. Tester la protection XSS
        $xssPayload = '<script>alert("xss")</script>';
        $protectedOutput = $this->xssProtection->protect($xssPayload);
        
        // 4. Vérifier que le script est échappé
        $this->assertStringNotContainsString('<script>', $protectedOutput);
        $this->assertStringContainsString('&lt;script&gt;', $protectedOutput);
    }

    public function testSessionSecurity()
    {
        // 1. Créer un utilisateur admin
        $this->createTestUser('admin', 'password123', 'admin');
        
        // 2. Authentifier l'utilisateur
        $this->auth->login('admin', 'password123');
        
        // 3. Récupérer le token de session
        $sessionToken = $this->auth->getCurrentSessionToken();
        
        // 4. Tenter d'utiliser un token invalide
        $this->auth->setSessionToken('invalid_token');
        $this->expectException(\App\Core\Security\SecurityException::class);
        $this->controller->getStats();
        
        // 5. Restaurer le token valide
        $this->auth->setSessionToken($sessionToken);
        $stats = $this->controller->getStats();
        $this->assertIsArray($stats);
    }

    public function testRateLimiting()
    {
        // 1. Créer un utilisateur admin
        $this->createTestUser('admin', 'password123', 'admin');
        
        // 2. Authentifier l'utilisateur
        $this->auth->login('admin', 'password123');
        
        // 3. Tester la limitation de taux
        for ($i = 0; $i < 100; $i++) {
            $this->controller->getStats();
        }
        
        // 4. Vérifier que la requête suivante est bloquée
        $this->expectException(\App\Core\Security\RateLimitException::class);
        $this->controller->getStats();
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