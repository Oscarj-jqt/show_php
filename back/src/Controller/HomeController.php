<?php

/**
 * Cas d'utilisation : page d'accueil publique
 */
namespace App\Controller;

class HomeController
{
    public function index(): void
    {
        include __DIR__ . '/../View/home.php';
    }
}