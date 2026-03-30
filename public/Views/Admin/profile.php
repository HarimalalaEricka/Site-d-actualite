<?php

declare(strict_types=1);

use App\Controllers\Admin\ArticleController;
use App\Models\SessionLogin;

require_once dirname(__DIR__, 3) . '/app/Core/Database.php';
require_once dirname(__DIR__, 3) . '/app/Models/Article.php';
require_once dirname(__DIR__, 3) . '/app/Models/SessionLogin.php';
require_once dirname(__DIR__, 3) . '/app/Controllers/Admin/ArticleController.php';

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

$articleController = new ArticleController();
$articles = $articleController->getArticleByUser($idUser, 'publie');
$flashSuccess = (string) ($_SESSION['flash_success'] ?? '');
unset($_SESSION['flash_success']);

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function nullableIntToString(?int $value): string
{
    return $value === null ? 'null' : (string) $value;
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Page de profil d'un BackOffice d'un site d'actualite sur la guerre en Iran.">
    <title>Profil Utilisateur</title>
</head>
<body>
    <div class="layout">
        <h1>Profil</h1>
        <h2>Mes articles</h2>
        <a href="Article/nouvelle.php">Nouvelle Article</a>
        <a href="Article/brouillon.php">Mes Brouillon</a>

        <?php if ($flashSuccess !== ''): ?>
            <div class="notice success"><?php echo escape($flashSuccess); ?></div>
        <?php endif; ?>

        <?php if ($articles === []): ?>
            <p>Aucun article trouve pour cet utilisateur.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($articles as $article): ?>
                    <li>
                        <h3><?php echo escape($article->getTitre()); ?></h3>
                        <a href="Article/editer.php?idArticle=<?php echo $article->getIdArticle(); ?>">Editer</a>
                        <a href="Article/supprimer.php?idArticle=<?php echo $article->getIdArticle(); ?>">Supprimer</a>
                        <p><strong>Id_Article:</strong> <?php echo escape(nullableIntToString($article->getIdArticle())); ?></p>
                        <p><strong>Date de publication:</strong> <?php echo escape($article->getDatePublication()); ?></p>
                        <p><strong>Contenu:</strong></p>
                        <div><?php echo $article->getContenu(); ?></div>
                        <p><strong>Nombre de vues:</strong> <?php echo escape((string) $article->getNbrVues()); ?></p>
                        <p><strong>Id_User_principal:</strong> <?php echo escape(nullableIntToString($article->getIdUserPrincipal())); ?></p>
                        <p><strong>Id_status_article:</strong> <?php echo escape(nullableIntToString($article->getIdStatusArticle())); ?></p>
                        <p><strong>Id_Categorie:</strong> <?php echo escape(nullableIntToString($article->getIdCategorie())); ?></p>
                        <p><strong>Lang:</strong> <?php echo escape($article->getLang()); ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>