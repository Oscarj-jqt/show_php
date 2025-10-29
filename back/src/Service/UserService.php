<?php

/**
 * Service pour inscription, connexion, récupération profil les utilisateurs
 */

namespace App\Service;

use App\Repository\User\UserRepositoryInterface;
use App\Model\User;

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
}