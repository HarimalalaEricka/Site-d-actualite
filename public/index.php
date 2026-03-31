<?php

declare(strict_types=1);

use App\Controllers\Front\HomeController;

require_once dirname(__DIR__) . '/app/Core/Database.php';
require_once dirname(__DIR__) . '/app/Models/Article.php';
require_once dirname(__DIR__) . '/app/Services/SimpleCache.php';
require_once dirname(__DIR__) . '/app/Controllers/Front/HomeController.php';

// Récupère l'URL demandée
$requestedPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$queryString = (string) ($_SERVER['QUERY_STRING'] ?? '');

// Détecte la langue depuis l'URL (/fr/, /en/)
if (preg_match('#^/([a-z]{2})(?:/|$)#', $requestedPath, $matches)) {
    $lang = $matches[1];
} else {
    // Langue par défaut
    $lang = 'fr';
}

// Routage simple du front office
if (preg_match('#^/([a-z]{2})/?$#', $requestedPath)) {
    // Accueil : /fr ou /en
    $homeController = new HomeController();
    $homeData = $homeController->getHomeData($lang);

    require __DIR__ . '/Views/Front/home.php';
    exit;
} elseif (preg_match('#^/([a-z]{2})/search#', $requestedPath)) {
    // Recherche : /fr/search
    require __DIR__ . '/Views/Front/search.php';
    exit;
} elseif (preg_match('#^/([a-z]{2})/archives#', $requestedPath)) {
    // Archives : /fr/archives
    require __DIR__ . '/Views/Front/archives.php';
    exit;
} elseif (preg_match('#^/([a-z]{2})/([^/]+)/?$#', $requestedPath, $matches)) {
    // Catégorie : /fr/categorie-slug
    require __DIR__ . '/Views/Front/category.php';
    exit;
}

// Si aucune route ne correspond, afficher l'accueil
$homeController = new HomeController();
$homeData = $homeController->getHomeData($lang);
require __DIR__ . '/Views/Front/home.php';
