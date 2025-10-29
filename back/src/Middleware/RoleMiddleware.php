<?php

/**
 * Middleware pour contrôler l'accès (utilisateur admin)
 */

namespace App\Middleware;

class RoleMiddleware
{
    public static function requireAdmin(array $user): void
    {
        if (($user['role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo "Admin only";
            exit;
        }
    }
}