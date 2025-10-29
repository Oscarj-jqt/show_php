<?php
/**
 * Interface pour accéder aux données des réservations
 */
namespace App\Repository;

use App\Model\Reservation;

interface ReservationRepositoryInterface
{
    public function findAll(): array;

    public function findById(int $id): ?Reservation;

    public function findByUserId(int $userId): array;

    public function findByShowId(int $showId): array;

    public function save(Reservation $reservation): void;
}