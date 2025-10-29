<?php

/** Cas d'utilisation : réserver, voir mes réservations */

namespace App\Controller;

use App\Service\ReservationService;


class ReservationController
{
    private ReservationService $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function listUserReservations(int $userId): void
    {
        $reservations = $this->reservationService->getUserReservations($userId);
        include __DIR__ . '/../View/reservation_list.php';
    }

    public function reserve(int $showId, int $userId, int $ticketCount, \DateTime $date): void
    {
        $this->reservationService->reserve($showId, $userId, $ticketCount, $date);
        include __DIR__ . '/../View/reservation_confirmation.php';
    }
}