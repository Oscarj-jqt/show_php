<?php

/**
 * Interface pour accéder aux données des spectacles
 */
namespace App\Repository\Show;

use App\Model\Show;

interface ShowRepositoryInterface
{
    public function findAll(): array;

    public function findById(int $id): ?Show;

    public function save(Show $show): void;
}