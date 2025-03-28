<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'Pixel-Hub',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => (bool)($_ENV['APP_DEBUG'] ?? false),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'timezone' => 'Europe/Paris',
    'locale' => 'fr_FR',
    'key' => $_ENV['APP_KEY'] ?? '',
    'cipher' => 'AES-256-CBC',

    'providers' => [
        // Providers de base
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\EventServiceProvider::class,
    ],

    'aliases' => [
        'App' => App\Core\Application::class,
        'Config' => App\Core\Config::class,
        'DB' => App\Core\Database::class,
        'Log' => App\Core\Logger::class,
        'Route' => App\Core\Router::class,
    ],

    'paths' => [
        'app' => __DIR__ . '/../src',
        'config' => __DIR__,
        'database' => __DIR__ . '/../database',
        'public' => __DIR__ . '/../public',
        'resources' => __DIR__ . '/../resources',
        'routes' => __DIR__ . '/../routes',
        'storage' => __DIR__ . '/../storage',
        'tests' => __DIR__ . '/../tests',
    ],

    'cache' => [
        'driver' => 'file',
        'path' => __DIR__ . '/../storage/cache',
    ],

    'session' => [
        'driver' => 'file',
        'path' => __DIR__ . '/../storage/sessions',
    ],

    'view' => [
        'path' => __DIR__ . '/../src/Views',
        'cache' => __DIR__ . '/../storage/views',
        'layout' => 'layouts/main',
    ],
]; 