<?php
/**
 * Interface pour accéder aux données des utilisateurs
 */

namespace App\Repository\User;

use App\Model\User;

interface UserRepositoryInterface
{
    public function findAll(): array;

    public function findById(int $id): ?User;

    public function findByUsername(string $username): ?User;

    public function save(User $user): void;
}