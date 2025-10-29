<?php

namespace App\Repository\Reservation;

use App\Model\Reservation;
use DateTime;
use App\Repository\Reservation\ReservationRepositoryInterface;
class ReservationJsonRepository implements ReservationRepositoryInterface
{
    private string $dataFile;

    public function __construct(string $dataFile)
    {
        $this->dataFile = $dataFile;
    }

    public function findAll(): array
    {
        $data = json_decode(file_get_contents($this->dataFile), true) ?? [];
        $reservations = [];
        foreach ($data as $item) {
            $reservations[] = new Reservation(
                $item['id'],
                $item['showId'],
                $item['userId'],
                new DateTime($item['date']),
                $item['ticketCount']
            );
        }
        return $reservations;
    }

    public function findById(int $id): ?Reservation
    {
        foreach ($this->findAll() as $reservation) {
            if ($reservation->id === $id) {
                return $reservation;
            }
        }
        return null;
    }

    public function findByUserId(int $userId): array
    {
        return array_values(array_filter(
            $this->findAll(),
            fn($r) => $r->userId === $userId
        ));
    }

    public function findByShowId(int $showId): array
    {
        return array_values(array_filter(
            $this->findAll(),
            fn($r) => $r->showId === $showId
        ));
    }

    public function save(Reservation $reservation): void
    {
        $data = json_decode(file_get_contents($this->dataFile), true) ?? [];
        $found = false;
        foreach ($data as &$item) {
            if ($item['id'] === $reservation->id) {
                $item['showId'] = $reservation->showId;
                $item['userId'] = $reservation->userId;
                $item['date'] = $reservation->date->format('Y-m-d');
                $item['ticketCount'] = $reservation->ticketCount;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $data[] = [
                'id' => $reservation->id,
                'showId' => $reservation->showId,
                'userId' => $reservation->userId,
                'date' => $reservation->date->format('Y-m-d'),
                'ticketCount' => $reservation->ticketCount,
            ];
        }
        file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}