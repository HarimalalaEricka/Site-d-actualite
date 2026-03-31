<?php

declare(strict_types=1);

use App\Controllers\Front\HomeController;

require_once dirname(__DIR__) . '/app/Core/Database.php';
require_once dirname(__DIR__) . '/app/Models/Article.php';
require_once dirname(__DIR__) . '/app/Services/SimpleCache.php';
require_once dirname(__DIR__) . '/app/Services/PaginationService.php';
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
    require_once dirname(__DIR__) . '/app/Controllers/Front/SearchController.php';
    $query = isset($_GET['q']) ? (string) $_GET['q'] : null;
    $selectedCategoryId = isset($_GET['categorie']) ? (int) $_GET['categorie'] : null;
    $selectedTagSlug = isset($_GET['tag']) ? (string) $_GET['tag'] : null;
    $dateFrom = isset($_GET['date_from']) ? (string) $_GET['date_from'] : null;
    $dateTo = isset($_GET['date_to']) ? (string) $_GET['date_to'] : null;
    $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
    $perPage = 10;
    $searchController = new \App\Controllers\Front\SearchController();
    $searchData = $searchController->search($lang, $query, $selectedCategoryId, $selectedTagSlug, $dateFrom, $dateTo, $page, $perPage);
    $searchData['categories'] = $searchController->getCategories();
    $searchData['popularTags'] = $searchController->getPopularTags($lang, 20);
    require __DIR__ . '/Views/Front/search.php';
    exit;
} elseif (preg_match('#^/([a-z]{2})/archives(?:/([0-9]{4}))?(?:/([0-9]{2}))?#', $requestedPath, $matches)) {
    // Archives : /fr/archives, /fr/archives/2024, /fr/archives/2024/03
    require_once dirname(__DIR__) . '/app/Controllers/Front/ArchiveController.php';
    $year = isset($matches[2]) && $matches[2] !== '' ? (int)$matches[2] : null;
    $month = isset($matches[3]) && $matches[3] !== '' ? (int)$matches[3] : null;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = 10;
    $selectedCategorySlug = isset($_GET['categorie']) ? (string)$_GET['categorie'] : null;
    $archiveController = new \App\Controllers\Front\ArchiveController();
    $archiveData = $archiveController->getArchiveData($lang, $year, $month, $page, $perPage, $selectedCategorySlug);
    // Extract variables for the view
    $availableMonths = $archiveData['availableMonths'] ?? [];
    $categories = $archiveData['categories'] ?? [];
    $articles = $archiveData['articles'] ?? [];
    $year = $archiveData['year'] ?? $year;
    $month = $archiveData['month'] ?? $month;
    $selectedCategorySlug = $archiveData['selectedCategorySlug'] ?? $selectedCategorySlug;
    $page = $archiveData['page'] ?? $page;
    $perPage = $archiveData['perPage'] ?? $perPage;
    $total = $archiveData['total'] ?? 0;
    $totalPages = $archiveData['totalPages'] ?? 1;
    require __DIR__ . '/Views/Front/archives.php';
    exit;
} elseif (preg_match('#^/([a-z]{2})/[^/]+/article/(\d{4})/(\d{2})/(\d{2})/(\d+)-.*\.html$#', $requestedPath, $matches)) {
    // Article : /fr/categorie/article/yyyy/mm/dd/id-slug.html
    require __DIR__ . '/article-view.php';
    exit;
} elseif (preg_match('#^/([a-z]{2})/([^/]+)/?$#', $requestedPath, $matches)) {
    // Catégorie : /fr/categorie-slug
    require_once dirname(__DIR__) . '/app/Controllers/Front/CategoryController.php';
    $categorySlug = $matches[2];
    $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
    $perPage = 10;
    $categoryController = new \App\Controllers\Front\CategoryController();
    $category = $categoryController->findBySlug($categorySlug);
    $categoryData = null;
    if ($category !== null) {
        $categoryData = $categoryController->getPublishedByCategory($category['Id_Categorie'], $lang, $page, $perPage);
    }
    require __DIR__ . '/Views/Front/category.php';
    exit;
}

// Si aucune route ne correspond, afficher l'accueil
$homeController = new HomeController();
$homeData = $homeController->getHomeData($lang);
require __DIR__ . '/Views/Front/home.php';
