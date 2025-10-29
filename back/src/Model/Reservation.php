<?php

namespace App\Model;

use DateTime;


class Reservation
{
    
    public function __construct(
        public int $id,
        public int $showId,
        public int $userId,
        public DateTime $date,
        public int $ticketCount,
    ) {}
}