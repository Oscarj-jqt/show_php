<?php

/**
 * Cas d'utiliation : inscription, connexion, gestion du profil et rafraîchissement du token
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->userService->register($data);
            echo "Inscription réussie !";
        } else {
            include __DIR__ . '/../View/register.php';
        }
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

    public function refreshToken(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $refreshToken = $data['refresh_token'] ?? '';
        $accessToken = $this->userService->refreshToken($refreshToken);
        if ($accessToken) {
            echo json_encode(['access_token' => $accessToken]);
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'Refresh token invalide ou expiré']);
        }
    }
}