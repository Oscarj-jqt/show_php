<?php
namespace App\Model;

use DateTime;

class Show
{
    
    public function __construct(
        public int $id,
        public string $titre,
        public DateTime $date,
        public string $description,
        public int $seats = 0,
    ) {}
}