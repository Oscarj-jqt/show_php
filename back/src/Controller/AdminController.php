<?php

/**
 * Cas d'utilisation : ajouter un spectacle (admin seulement)
 */

namespace App\Controller;

use App\Model\Show;
use App\Service\ShowService;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

class AdminController
{
    private ShowService $showService;

    public function __construct(ShowService $showService)
    {
        $this->showService = $showService;
    }

    public function addShow(array $data): void
    {
        // Vérification JWT + rôle admin
        $user = AuthMiddleware::requireAuth();
        RoleMiddleware::requireAdmin($user);
        $show = new Show(
            id: rand(1000, 9999),
            titre: $data['titre'],
            date: new \DateTime($data['date']),
            description: $data['description'],
            seats: $data['seats']
        );
        $this->showService->addShow($show);
        include __DIR__ . '/../View/admin_add_confirmation.php';
    }
}