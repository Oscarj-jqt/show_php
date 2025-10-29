<?php

/**
 * Middleware pour protéger les routes nécessitant une authentification
 */

namespace App\Middleware;

use App\Service\JwtService;

class AuthMiddleware
{
    public static function requireAuth(): ?array
    {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo "Token manquant";
            exit;
        }
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $jwtService = new JwtService();
        $user = $jwtService->verify($token);
        if (!$user) {
            http_response_code(401);
            echo "Token invalide ou expiré";
            exit;
        }
        return $user;
    }
}