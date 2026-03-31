<?php

declare(strict_types=1);

use App\Controllers\Front\ArticleController;
use App\Controllers\Front\ViewCounterController;

require_once dirname(__DIR__) . '/app/Core/Database.php';
require_once dirname(__DIR__) . '/app/Models/Article.php';
require_once dirname(__DIR__) . '/app/Controllers/Front/ArticleController.php';
require_once dirname(__DIR__) . '/app/Controllers/Front/ViewCounterController.php';

$requestedPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Format: /fr/categorie/article/2026/03/31/123-titre.html
// Extraction: lang, id_article
if (preg_match('#^/([a-z]{2})/[^/]+/article/(\d{4})/(\d{2})/(\d{2})/(\d+)-.*\.html$#', $requestedPath, $matches)) {
	$lang = $matches[1];
	$idArticle = (int) $matches[5];

	$articleController = new ArticleController();
	$article = $articleController->getPublishedArticleById($idArticle, $lang);

	if ($article !== null) {
		// Incrémente les vues dans la base de données
		$viewCounterController = new ViewCounterController();
		$viewCounterController->incrementViewCount($idArticle);

		// Récupère le nombre de vues depuis la base de données
		$article['nbr_vues'] = $viewCounterController->getViewCount($idArticle);

		// Construit le chemin canonique
		$canonicalPath = $articleController->buildCanonicalPath($article);

		// Inclut la vue article.php
		require __DIR__ . '/Views/Front/article.php';
		exit;
	}
}

// Si l'article n'existe pas, erreur 404
http_response_code(404);
echo '404 - Article non trouvé';
