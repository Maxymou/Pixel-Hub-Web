<?php

namespace App\Core\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use App\Core\Application;

class JWTManager
{
    private string $secret;
    private int $expiration;
    private string $algorithm;

    public function __construct()
    {
        $config = Application::getInstance()->getConfig();
        $this->secret = $config['JWT_SECRET'] ?? '';
        $this->expiration = (int)($config['JWT_EXPIRATION'] ?? 3600);
        $this->algorithm = 'HS256';

        if (empty($this->secret)) {
            throw new \RuntimeException('JWT_SECRET n\'est pas configuré');
        }
    }

    public function generateToken(array $user): string
    {
        if (empty($user['id']) || empty($user['username']) || empty($user['role'])) {
            throw new \InvalidArgumentException('Données utilisateur invalides pour la génération du token');
        }

        $issuedAt = time();
        $expire = $issuedAt + $this->expiration;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return (array)$decoded;
        } catch (ExpiredException $e) {
            throw new \Exception('Token expiré');
        } catch (SignatureInvalidException $e) {
            throw new \Exception('Signature du token invalide');
        } catch (\Exception $e) {
            throw new \Exception('Token invalide: ' . $e->getMessage());
        }
    }

    public function refreshToken(string $token): ?string
    {
        try {
            $payload = $this->validateToken($token);
            if (!$payload) {
                return null;
            }

            return $this->generateToken([
                'id' => $payload['user_id'],
                'username' => $payload['username'],
                'role' => $payload['role']
            ]);
        } catch (\Exception $e) {
            return null;
        }
    }
} 