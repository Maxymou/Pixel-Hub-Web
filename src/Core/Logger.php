<?php

namespace App\Core;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Logger
{
    private MonologLogger $logger;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->initializeLogger();
    }

    private function initializeLogger(): void
    {
        $this->logger = new MonologLogger('pixel-hub');

        // Format de log personnalisé
        $dateFormat = "Y-m-d H:i:s";
        $output = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
        $formatter = new LineFormatter($output, $dateFormat);

        // Handler pour les fichiers de log quotidiens
        $handler = new RotatingFileHandler(
            __DIR__ . '/../../storage/logs/app.log',
            30,
            $this->getLogLevel()
        );
        $handler->setFormatter($formatter);
        $this->logger->pushHandler($handler);

        // Handler pour les erreurs critiques
        $errorHandler = new StreamHandler(
            __DIR__ . '/../../storage/logs/error.log',
            MonologLogger::ERROR
        );
        $errorHandler->setFormatter($formatter);
        $this->logger->pushHandler($errorHandler);
    }

    private function getLogLevel(): int
    {
        return match ($this->config['LOG_LEVEL'] ?? 'debug') {
            'debug' => MonologLogger::DEBUG,
            'info' => MonologLogger::INFO,
            'warning' => MonologLogger::WARNING,
            'error' => MonologLogger::ERROR,
            'critical' => MonologLogger::CRITICAL,
            default => MonologLogger::DEBUG
        };
    }

    public function debug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    public function emergency(string $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }
} 