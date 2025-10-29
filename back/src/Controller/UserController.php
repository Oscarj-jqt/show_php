<?php

/**
 * Cas d'utiliation : inscription, connexion, gestion du profil
 */

namespace App\Controller;

use App\Service\UserService;


class UserController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(array $data): void
    {
        $this->userService->register($data);
    }

    public function login(string $username, string $password): void
    {
        $user = $this->userService->login($username, $password);
        if ($user) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            header('Location: ?route=user.profile&id=' . $user->id);
            exit;
        } else {
            echo "Identifiants incorrects.";
        }
    }

    public function profile(): void
    {
        $user = \App\Middleware\AuthMiddleware::requireAuth();
        $userId = $user['sub']; 

        $userEntity = $this->userService->getProfile($userId);
        if ($userEntity) {
            include __DIR__ . '/../View/user_profile.php';
        } else {
            echo "Utilisateur non trouvé.";
        }
    }
}