<?php

namespace App\Core\Auth;

use App\Core\Application;
use App\Core\Database;
use App\Core\Validation\Validator;
use App\Models\User;

class AuthManager
{
    private Database $db;
    private JWTManager $jwtManager;
    private ?array $user = null;

    public function __construct()
    {
        $this->db = Application::getInstance()->getDatabase();
        $this->jwtManager = new JWTManager();
    }

    public function login(string $username, string $password): ?array
    {
        // Valider les entrées
        $validator = new Validator(['username' => $username, 'password' => $password]);
        if (!$validator->validate([
            'username' => ['required', 'min:3', 'max:50'],
            'password' => ['required', 'min:8']
        ])) {
            throw new \InvalidArgumentException($validator->getFirstError());
        }

        // Vérifier les tentatives de connexion
        if ($this->isAccountLocked($username)) {
            throw new \Exception('Compte temporairement verrouillé. Veuillez réessayer plus tard.');
        }

        // Récupérer l'utilisateur
        $user = $this->db->query(
            "SELECT * FROM users WHERE username = ? AND is_active = TRUE",
            [$username]
        )->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $this->logFailedAttempt($username);
            throw new \Exception('Identifiants invalides');
        }

        // Réinitialiser les tentatives de connexion
        $this->resetLoginAttempts($username);

        // Mettre à jour la dernière connexion
        $this->db->query(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$user['id']]
        );

        // Générer le token JWT
        $token = $this->jwtManager->generateToken($user);

        $this->user = $user;
        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function register(array $data): ?array
    {
        // Valider les données
        $validator = new Validator($data);
        if (!$validator->validate([
            'username' => ['required', 'min:3', 'max:50', 'alphanum'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'min:8', 'max:255'],
            'role' => ['in:admin,user']
        ])) {
            throw new \InvalidArgumentException($validator->getFirstError());
        }

        // Vérifier si l'utilisateur existe déjà
        if ($this->userExists($data['username'], $data['email'])) {
            throw new \Exception('Un utilisateur avec ce nom ou cet email existe déjà.');
        }

        // Hasher le mot de passe
        $data['password'] = password_hash(
            $data['password'],
            PASSWORD_BCRYPT,
            ['cost' => 12]
        );

        // Insérer l'utilisateur
        $userId = $this->db->query(
            "INSERT INTO users (username, email, password, role, is_active) VALUES (?, ?, ?, ?, TRUE)",
            [$data['username'], $data['email'], $data['password'], $data['role'] ?? 'user']
        );

        if (!$userId) {
            throw new \Exception('Erreur lors de la création du compte.');
        }

        $user = $this->db->query(
            "SELECT * FROM users WHERE id = ?",
            [$userId]
        )->fetch();

        return $user;
    }

    public function validateToken(string $token): ?array
    {
        try {
            $payload = $this->jwtManager->validateToken($token);
            if (!$payload) {
                return null;
            }

            $user = $this->db->query(
                "SELECT * FROM users WHERE id = ? AND is_active = TRUE",
                [$payload['user_id']]
            )->fetch();

            if (!$user) {
                return null;
            }

            $this->user = $user;
            return $user;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getCurrentUser(): ?array
    {
        return $this->user;
    }

    public function hasRole(string $role): bool
    {
        return $this->user && $this->user['role'] === $role;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    private function isAccountLocked(string $username): bool
    {
        $result = $this->db->query(
            "SELECT COUNT(*) as count, MAX(created_at) as last_attempt 
             FROM login_attempts 
             WHERE username = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)",
            [$username]
        )->fetch();

        return $result['count'] >= 5;
    }

    private function logFailedAttempt(string $username): void
    {
        $this->db->query(
            "INSERT INTO login_attempts (username, ip_address) VALUES (?, ?)",
            [$username, $_SERVER['REMOTE_ADDR'] ?? 'unknown']
        );
    }

    private function resetLoginAttempts(string $username): void
    {
        $this->db->query(
            "DELETE FROM login_attempts WHERE username = ?",
            [$username]
        );
    }

    private function userExists(string $username, string $email): bool
    {
        $result = $this->db->query(
            "SELECT COUNT(*) as count FROM users WHERE username = ? OR email = ?",
            [$username, $email]
        )->fetch();

        return $result['count'] > 0;
    }
} 