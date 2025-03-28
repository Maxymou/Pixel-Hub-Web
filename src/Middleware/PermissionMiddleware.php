<?php

namespace App\Middleware;

use App\Core\Auth\AuthManager;
use App\Core\Application;

class PermissionMiddleware
{
    private AuthManager $auth;
    private array $requiredRoles;

    public function __construct(array $roles)
    {
        if (empty($roles)) {
            throw new \InvalidArgumentException('Les rôles requis ne peuvent pas être vides');
        }
        $this->auth = new AuthManager();
        $this->requiredRoles = array_unique($roles);
    }

    public function handle(): void
    {
        try {
            $token = $this->getBearerToken();
            if (!$token) {
                $this->sendError('Token manquant', 401);
                return;
            }

            $user = $this->auth->validateToken($token);
            if (!$user) {
                $this->sendError('Token invalide', 401);
                return;
            }

            // Vérifier les rôles requis
            if (!in_array($user['role'], $this->requiredRoles)) {
                $this->sendError('Accès non autorisé', 403);
                return;
            }

            // Stocker l'utilisateur dans la requête
            $_REQUEST['user'] = $user;
        } catch (\Exception $e) {
            $this->sendError('Erreur d\'authentification: ' . $e->getMessage(), 401);
        }
    }

    private function getBearerToken(): ?string
    {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            return null;
        }

        if (!preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return null;
        }

        return $matches[1];
    }

    private function sendError(string $message, int $statusCode): void
    {
        header('Content-Type: application/json');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        
        http_response_code($statusCode);
        echo json_encode([
            'error' => $message,
            'status' => $statusCode
        ]);
        exit;
    }
} 