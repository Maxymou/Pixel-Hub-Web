<?php

namespace App\Middleware;

use App\Core\Application;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApiAuthMiddleware
{
    public function handle(): void
    {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        if (!$token) {
            $this->sendError('Token manquant', 401);
            return;
        }

        // Supprimer le préfixe "Bearer " si présent
        $token = str_replace('Bearer ', '', $token);

        try {
            $app = Application::getInstance();
            $config = $app->getConfig();
            
            $decoded = JWT::decode($token, new Key($config['JWT_SECRET'], 'HS256'));
            
            // Vérifier si le token est expiré
            if ($decoded->exp < time()) {
                $this->sendError('Token expiré', 401);
                return;
            }

            // Stocker l'ID de l'utilisateur dans la requête
            $_REQUEST['user_id'] = $decoded->user_id;
            
        } catch (\Exception $e) {
            $app->getLogger()->error('Erreur d\'authentification API', [
                'error' => $e->getMessage(),
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
            
            $this->sendError('Token invalide', 401);
        }
    }

    private function sendError(string $message, int $statusCode): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode([
            'error' => $message
        ]);
        exit;
    }
} 