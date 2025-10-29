<?php

/**
 * Service pour créer et vérifier un token JWT
 */

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    // ne faut-il pas créer .env où stocker cette clé secrète on a phpdotenv d'installé
    private string $secret = 'supersecretkey';

    public function generate(array $payload): string
    {
        $payload['iat'] = time();
        $payload['exp'] = time() + 60; // 1 min
        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function verify(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
            return (array)$decoded;
        } catch (\Exception $e) {
            return null;
        }
    }
}