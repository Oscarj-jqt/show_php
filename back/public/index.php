<?php
declare(strict_types=1);

$autoload = __DIR__ . '/../../vendor/autoload.php';
if (!file_exists($autoload)) {
    http_response_code(500);
    echo "Le fichier vendor/autoload.php est introuvable. Exécutez `composer install` à la racine du projet.";
    exit;
}

require $autoload;

use Dotenv\Dotenv;

$projectRoot = dirname(__DIR__);
if (file_exists($projectRoot . '/.env')) {
    $dotenv = Dotenv::createImmutable($projectRoot);
    $dotenv->safeLoad();
}

$appEnv = $_ENV['APP_ENV'] ?? ($_SERVER['APP_ENV'] ?? 'prod');
if ($appEnv === 'dev') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('PROJECT_ROOT', $projectRoot);
define('SRC_DIR', PROJECT_ROOT . '/src');
define('VIEW_DIR', SRC_DIR . '/View');
define('DATA_DIR', realpath(SRC_DIR . '/Data') ?: (SRC_DIR . '/Data'));

// Chemin vers Router.php (ton projet : src/Controller/Router.php)
$routerFile = SRC_DIR . '/Controller/Router.php';

// Liste de candidats de classes potentielles (ajoute ici des FQCN si tu connais le namespace)
$routerClassCandidates = [
    'Router',
    'App\\Controller\\Router',
    'Controller\\Router',
    '\\Router',
];

$routerClass = null;
// Si la classe existe déjà via l'autoload, on la prend
foreach ($routerClassCandidates as $candidate) {
    if (class_exists($candidate)) {
        $routerClass = $candidate;
        break;
    }
}

// Si la classe n'existe pas mais le fichier Router.php est présent, on l'inclut et on retente
if ($routerClass === null && file_exists($routerFile)) {
    require_once $routerFile;
    foreach ($routerClassCandidates as $candidate) {
        if (class_exists($candidate)) {
            $routerClass = $candidate;
            break;
        }
    }
}

if ($routerClass === null) {
    http_response_code(500);
    echo "Impossible de trouver la classe Router. Vérifie le fichier src/Controller/Router.php et son namespace.\n";
    echo "Si Router est namespacé, utilise le FQCN (ex: App\\Controller\\Router) dans \$routerClassCandidates.\n";
    exit;
}

try {
    $router = new $routerClass();

    if (method_exists($router, 'run')) {
        $router->run();
    } elseif (method_exists($router, 'dispatch')) {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $router->dispatch($uri, $method);
    } else {
        throw new RuntimeException('La classe Router ne contient ni run() ni dispatch(uri, method). Adapte index.php à ta Router.');
    }
} catch (Throwable $e) {
    http_response_code(500);
    if ($appEnv === 'dev') {
        echo "<h1>Erreur interne</h1><pre>" . htmlspecialchars((string)$e) . "</pre>";
    } else {
        echo "Une erreur interne est survenue.";
    }
    error_log((string)$e);
    exit;
}