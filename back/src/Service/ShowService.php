<?php

/**
 * Service pour lister, afficher un spectacle les spectacles
 */

namespace App\Service;

use App\Repository\Show\ShowRepositoryInterface;
use App\Model\Show;

class ShowService
{
    private ShowRepositoryInterface $showRepo;

    public function __construct(ShowRepositoryInterface $showRepo)
    {
        $this->showRepo = $showRepo;
    }

    public function listShows(): array
    {
        return $this->showRepo->findAll();
    }

    public function getShow(int $id): ?Show
    {
        return $this->showRepo->findById($id);
    }

    public function addShow(Show $show): void
    {
        $this->showRepo->save($show);
    }
}