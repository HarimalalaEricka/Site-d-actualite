<?php 
declare(strict_types=1);

use App\Models\SessionLogin;
use App\Models\Article;
use App\Controllers\Admin\ArticleController;
use App\Controllers\Admin\UserController;

require_once dirname(__DIR__, 4) . '/app/Core/Database.php';
require_once dirname(__DIR__, 4) . '/app/Models/Article.php';
require_once dirname(__DIR__, 4) . '/app/Models/SessionLogin.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/ArticleController.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/UserController.php';

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
    header('Location: /Views/Admin/profile.php');
    exit;
}

$articleController = new ArticleController();
$article = $articleController->getArticleById($idArticle);
if ($article === null || $article->getIdUserPrincipal() !== $idUser) {
    header('Location: /Views/Admin/profile.php');
    exit;
}
else
{
    try {
        $isUpdated = $articleController->deleteArticle($idArticle, $idUser);

        if ($isUpdated) {
            $_SESSION['flash_success'] = 'Article retire avec succes.';
        } else {
            $_SESSION['flash_error'] = 'Article introuvable ou deja traite.';
        }
    } catch (Throwable $e) {
        $_SESSION['flash_error'] = 'Erreur lors du retrait: ' . $e->getMessage();
    }

    header('Location: /Views/Admin/profile.php');
    exit;
}
?>