<?php

namespace App\Controller;


use App\Controller\HomeController;
use App\Controller\ShowController;
use App\Controller\UserController;
use App\Service\ShowService;
use App\Service\UserService;
use App\Service\ReservationService;
use App\Repository\Show\ShowJsonRepository;
use App\Repository\User\UserJsonRepository;
use App\Repository\Reservation\ReservationJsonRepository;

class Router
{
    public function handle(): void
    {
        $route = $_GET['route'] ?? 'home';

        // Instanciation des repositories et services (injection manuelle)
        $showRepo = new ShowJsonRepository(__DIR__ . '/../../Data/shows.json');
        $userRepo = new UserJsonRepository(__DIR__ . '/../../Data/users.json');
        $reservationRepo = new ReservationJsonRepository(__DIR__ . '/../../Data/reservations.json');

        $showService = new ShowService($showRepo);
        $userService = new UserService($userRepo);
        $reservationService = new ReservationService($reservationRepo);

        // Routing
        switch ($route) {
            case 'home':
                (new HomeController())->index();
                break;
            case 'show.list':
                (new ShowController($showService))->list();
                break;
            case 'show.detail':
                $id = intval($_GET['id'] ?? 0);
                (new ShowController($showService))->detail($id);
                break;
            case 'user.register':
                $data = $_POST;
                (new UserController($userService))->register($data);
                break;
            case 'user.login':
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                (new UserController($userService))->login($username, $password);
                break;
            case 'user.profile':
                $userId = intval($_GET['id'] ?? 0);
                (new UserController($userService))->profile($userId);
                break;
            case 'admin.addShow':
                $data = $_POST;
                (new AdminController($showService))->addShow($data);
                break;
            default:
                http_response_code(404);
                echo "Page non trouvée";
        }
    }
}