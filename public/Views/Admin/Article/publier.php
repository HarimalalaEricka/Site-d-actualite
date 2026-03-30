<?php

declare(strict_types=1);

use App\Controllers\Admin\ArticleController;
use App\Models\SessionLogin;

require_once dirname(__DIR__, 4) . '/app/Core/Database.php';
require_once dirname(__DIR__, 4) . '/app/Models/Article.php';
require_once dirname(__DIR__, 4) . '/app/Models/SessionLogin.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/ArticleController.php';

session_start();

if (!isset($_SESSION['login']) || !($_SESSION['login'] instanceof SessionLogin) || $_SESSION['login']->getUserLoggedIn() !== true) {
	header('Location: /admin.php');
	exit;
}

$login = $_SESSION['login'];
$idUser = $login->getIdUser();

if ($idUser === null) {
	header('Location: /logout.php');
	exit;
}

$idArticle = isset($_GET['idArticle']) ? (int) $_GET['idArticle'] : 0;

if ($idArticle <= 0) {
	$_SESSION['flash_error'] = 'Article invalide.';
	header('Location: /Views/Admin/Article/brouillon.php');
	exit;
}

$articleController = new ArticleController();
$article = $articleController->getArticleById($idArticle);

if ($article === null || $article->getIdUserPrincipal() !== $idUser) {
	$_SESSION['flash_error'] = 'Article introuvable ou acces refuse.';
	header('Location: /Views/Admin/Article/brouillon.php');
	exit;
}

try {
	$isPublished = $articleController->publishArticle($idArticle, $idUser);

	if ($isPublished) {
		$_SESSION['flash_success'] = 'Brouillon publie avec succes.';
	} else {
		$_SESSION['flash_error'] = 'Impossible de publier ce brouillon.';
	}
} catch (Throwable $e) {
	$_SESSION['flash_error'] = 'Erreur lors de la publication: ' . $e->getMessage();
}

header('Location: /Views/Admin/Article/brouillon.php');
exit;
