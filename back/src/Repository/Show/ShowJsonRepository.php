<?php

namespace App\Repository\Show;

use App\Model\Show;
use DateTime;
use App\Repository\Show\ShowRepositoryInterface;

class ShowJsonRepository implements ShowRepositoryInterface
{
    private string $dataFile;

    public function __construct(string $dataFile)
    {
        $this->dataFile = $dataFile;
    }

    public function findAll(): array
    {
        $data = json_decode(file_get_contents($this->dataFile), true) ?? [];
        $shows = [];
        foreach ($data as $item) {
            $shows[] = new Show(
                $item['id'],
                $item['titre'],
                new DateTime($item['date']),
                $item['description'],
                $item['seats'] ?? 0
            );
        }
        return $shows;
    }

    public function findById(int $id): ?Show
    {
        foreach ($this->findAll() as $show) {
            if ($show->id === $id) {
                return $show;
            }
        }
        return null;
    }

    public function save(Show $show): void
    {
        $data = json_decode(file_get_contents($this->dataFile), true) ?? [];
        $found = false;
        foreach ($data as &$item) {
            if ($item['id'] === $show->id) {
                $item['titre'] = $show->titre;
                $item['date'] = $show->date->format('Y-m-d');
                $item['description'] = $show->description;
                $item['seats'] = $show->seats;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $data[] = [
                'id' => $show->id,
                'titre' => $show->titre,
                'date' => $show->date->format('Y-m-d'),
                'description' => $show->description,
                'seats' => $show->seats,
            ];
        }
        file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}