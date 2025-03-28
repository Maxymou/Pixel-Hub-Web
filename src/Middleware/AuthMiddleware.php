<?php

namespace App\Middleware;

use App\Core\Application;

class AuthMiddleware
{
    public function handle(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $app = Application::getInstance();
            $app->getLogger()->warning('Tentative d\'accès non autorisée', [
                'ip' => $_SERVER['REMOTE_ADDR'],
                'uri' => $_SERVER['REQUEST_URI']
            ]);
            
            header('Location: /login');
            exit;
        }
    }
} 