<?php

/**
 * Service pour inscription, connexion, récupération profil les utilisateurs 
 */

namespace App\Service;

use App\Repository\User\UserRepositoryInterface;
use App\Model\User;
use App\Service\JwtService;

class UserService
{
    private UserRepositoryInterface $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function register(array $data): void
    {
        $user = new User(
            id: rand(1000, 9999),
            username: $data['username'],
            name: $data['name'],
            firstname: $data['firstname'],
            lastname: $data['lastname'],
            email: $data['email'],
            password: password_hash($data['password'], PASSWORD_DEFAULT),
            role: $data['role'] ?? 'user'
        );
        $this->userRepo->save($user);
    }

    public function login(string $username, string $password): ?User
    {
        $user = $this->userRepo->findByUsername($username);
        if ($user && password_verify($password, $user->password)) {
            return $user;
        }
        return null;
    }

    public function getProfile(int $userId): ?User
    {
        return $this->userRepo->findById($userId);
    }

    public function refreshToken(string $refreshToken): ?string
    {
        $file = __DIR__ . '/../../Data/refresh_tokens.json';
        $tokens = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
        $hash = hash('sha256', $refreshToken);
        $found = null;
        foreach ($tokens as $row) {
            if ($row['token_hash'] === $hash && $row['expires_at'] > time()) {
                $found = $row;
                break;
            }
        }
        if (!$found) {
            return null;
        }
        // Générer un nouveau JWT
        $jwtService = new JwtService();
        return $jwtService->generate([
            'sub' => $found['user_id']
        ]);
    }
}