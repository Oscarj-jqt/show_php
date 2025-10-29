<?php

/**
 * Cas d'utilisation : liste, détail d'un spectacle
 */
namespace App\Controller;

use App\Service\ShowService;

class ShowController
{
    private ShowService $showService;

    public function __construct(ShowService $showService)
    {
        $this->showService = $showService;
    }

    public function list(): void
    {
        $shows = $this->showService->listShows();
        include __DIR__ . '/../View/show_list.php';
    }

    public function detail(int $id): void
    {
        $show = $this->showService->getShow($id);
        include __DIR__ . '/../View/show_detail.php';
    }
}