<?php

/**
 * Cas d'utilisation : ajouter un spectacle (admin seulement)
 */

namespace App\Controller;

use App\Service\ShowService;

class AdminController
{
    private ShowService $showService;

    public function __construct(ShowService $showService)
    {
        $this->showService = $showService;
    }

    public function addShow(array $data): void
    {
        // Vérification du rôle admin
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo "Accès interdit : administrateur uniquement.";
            return;
        }

        $show = new \App\Model\Show(
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