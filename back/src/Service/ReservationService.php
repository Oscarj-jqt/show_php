<?php

/** Service pour gérer les réservations */

namespace App\Service;

use App\Repository\Reservation\ReservationRepositoryInterface;
use App\Model\Reservation;

class ReservationService
{
    private ReservationRepositoryInterface $reservationRepo;

    public function __construct(ReservationRepositoryInterface $reservationRepo)
    {
        $this->reservationRepo = $reservationRepo;
    }

    public function getUserReservations(int $userId): array
    {
        return $this->reservationRepo->findByUserId($userId);
    }

    public function reserve(int $showId, int $userId, int $ticketCount, \DateTime $date): void
    {
        // id unique
        do {
            $id = rand(1000, 9999);
        } while ($this->reservationRepo->findById($id) !== null);

        $reservation = new Reservation(
            id: $id,
            showId: $showId,
            userId: $userId,
            date: $date,
            ticketCount: $ticketCount
        );
        $this->reservationRepo->save($reservation);
    }
}