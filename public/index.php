<?php

declare(strict_types=1);

use App\Controllers\Front\ArticleController;
use App\Controllers\Front\ArchiveController;
use App\Controllers\Front\CategoryController;
use App\Controllers\Front\HomeController;
use App\Core\Router;
use App\Services\ViewCounterService;

require_once dirname(__DIR__) . '/app/Core/Database.php';
require_once dirname(__DIR__) . '/app/Core/Router.php';
require_once dirname(__DIR__) . '/app/Models/Article.php';
require_once dirname(__DIR__) . '/app/Controllers/Front/HomeController.php';
require_once dirname(__DIR__) . '/app/Controllers/Front/CategoryController.php';
require_once dirname(__DIR__) . '/app/Controllers/Front/ArticleController.php';
require_once dirname(__DIR__) . '/app/Controllers/Front/ArchiveController.php';
require_once dirname(__DIR__) . '/app/Services/SimpleCache.php';
require_once dirname(__DIR__) . '/app/Services/ViewCounterService.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

function renderNotFound(): void
{
	http_response_code(404);
	echo 'Page introuvable';
}

$router = new Router();

$router->get('#^/$#', static function (): void {
	header('Location: /fr', true, 302);
	exit;
});

$router->get('#^/([a-z]{2})/?$#', static function (string $lang): void {
	$homeController = new HomeController();
	$homeData = $homeController->getHomeData($lang);

	require __DIR__ . '/Views/Front/home.php';
});

$router->get('#^/([a-z]{2})/archives(?:/(\d{4})(?:/(\d{2}))?)?/?$#', static function (string $lang, ?string $year = null, ?string $month = null): void {
	$yearInt = $year !== null ? (int) $year : null;
	$monthInt = $month !== null ? (int) $month : null;

	if ($monthInt !== null && ($monthInt < 1 || $monthInt > 12)) {
		renderNotFound();
		return;
	}

	$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
	$categorySlug = isset($_GET['categorie']) ? (string) $_GET['categorie'] : null;
	$archiveController = new ArchiveController();
	$archiveData = $archiveController->getArchiveData($lang, $yearInt, $monthInt, $page, 10, $categorySlug);

	$availableMonths = $archiveData['availableMonths'];
	$categories = $archiveData['categories'];
	$articles = $archiveData['articles'];
	$year = $archiveData['year'];
	$month = $archiveData['month'];
	$selectedCategorySlug = $archiveData['selectedCategorySlug'];
	$page = $archiveData['page'];
	$totalPages = $archiveData['totalPages'];

	require __DIR__ . '/Views/Front/archives.php';
});

$router->get('#^/([a-z]{2})/([a-z0-9-]+)/article/(\d{4})/(\d{2})/(\d{2})/(\d+)-([a-z0-9-]+)(?:\.html)?/?$#', static function (
	string $lang,
	string $categorySlug,
	string $year,
	string $month,
	string $day,
	string $id,
	string $slug
): void {
	$articleController = new ArticleController();
	$article = $articleController->getPublishedArticleById((int) $id, $lang);

	if ($article === null) {
		renderNotFound();
		return;
	}

	$canonicalPath = $articleController->buildCanonicalPath($article);
	$currentPath = sprintf('/%s/%s/article/%s/%s/%s/%s-%s.html', $lang, $categorySlug, $year, $month, $day, $id, $slug);

	if ($currentPath !== $canonicalPath) {
		header('Location: ' . $canonicalPath, true, 301);
		exit;
	}

	// Enregistrer la vue (anti-duplication 30 min, non bloquant)
	ViewCounterService::recordView((int) $id);

	require __DIR__ . '/Views/Front/article.php';
});

$router->get('#^/([a-z]{2})/([a-z0-9-]+)/?$#', static function (string $lang, string $categorySlug): void {
	$categoryController = new CategoryController();
	$category = $categoryController->findBySlug($categorySlug);

	if ($category === null) {
		renderNotFound();
		return;
	}

	$articles = $categoryController->getPublishedByCategory((int) $category['Id_Categorie'], $lang);

	require __DIR__ . '/Views/Front/category.php';
});

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = (string) parse_url($requestUri, PHP_URL_PATH);
$router->dispatch((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'), $path);
