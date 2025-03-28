<?php

return [
    // Configuration de la session
    'session' => [
        'lifetime' => 7200, // 2 heures
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ],

    // Configuration JWT
    'jwt' => [
        'secret' => $_ENV['JWT_SECRET'] ?? '',
        'expiration' => $_ENV['JWT_EXPIRATION'] ?? 3600,
        'algorithm' => 'HS256'
    ],

    // Configuration du hachage des mots de passe
    'password' => [
        'algo' => PASSWORD_BCRYPT,
        'options' => [
            'cost' => 12
        ]
    ],

    // Configuration CSRF
    'csrf' => [
        'enabled' => true,
        'token_name' => 'csrf_token',
        'token_length' => 32
    ],

    // Configuration des en-têtes de sécurité
    'headers' => [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-XSS-Protection' => '1; mode=block',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';"
    ],

    // Configuration de la protection contre les attaques par force brute
    'brute_force' => [
        'enabled' => true,
        'max_attempts' => 5,
        'lockout_time' => 900, // 15 minutes
        'reset_time' => 3600 // 1 heure
    ],

    // Configuration des mots de passe
    'password_policy' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_special_chars' => true
    ],

    // Configuration des cookies
    'cookies' => [
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]
]; 