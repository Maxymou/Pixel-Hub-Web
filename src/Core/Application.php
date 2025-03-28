<?php

namespace App\Core;

class Application
{
    private static ?Application $instance = null;
    private array $config = [];
    private Router $router;
    private Database $database;
    private Logger $logger;

    private function __construct()
    {
        $this->loadConfig();
        $this->initializeComponents();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfig(): void
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
        $this->config = $_ENV;
    }

    private function initializeComponents(): void
    {
        $this->router = new Router();
        $this->database = new Database($this->config);
        $this->logger = new Logger($this->config);
    }

    public function run(): void
    {
        try {
            $this->router->dispatch();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            // Gérer l'erreur de manière appropriée
        }
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }
} 