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
$role = $login->getRole();

if ($idUser === null) {
    header('Location: /logout.php');
    exit;
}

$articleController = new ArticleController();
$articles = $articleController->getArticleByUser($idUser, 'brouillon');
$flashSuccess = (string) ($_SESSION['flash_success'] ?? '');
$flashError = (string) ($_SESSION['flash_error'] ?? '');
unset($_SESSION['flash_success']);
unset($_SESSION['flash_error']);

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
    <meta name="description" content="Gestion des brouillons d'un BackOffice d'un site d'actualite sur la guerre en Iran.">
    <title>Mes brouillons</title>
    <link rel="stylesheet" href="/assets/css/Admin/dashboard.css">
    <link rel="stylesheet" href="/assets/css/Admin/profile.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h1>Back-office</h1>
                <p>Site d'actualite</p>
            </div>

            <nav class="sidebar-nav" aria-label="Navigation principale">
                <a class="nav-link" href="../dashboard.php">Dashboard</a>
                <a class="nav-link active" href="brouillon.php">Articles</a>
                <a class="nav-link" href="../Categorie/gestion_categories.php">Categories</a>
                <a class="nav-link" href="../Tag/gestion.php">Tags</a>
                <a class="nav-link" href="../Media/gestion.php">Medias</a>
                <a class="nav-link" href="../User/gestion.php">Gestion utilisateurs</a>
                <?php if ($role === 'admin'): ?>
                    <a class="nav-link" href="../Role/role.php">Roles & permissions</a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <a class="footer-link" href="../profile.php">Mon profil</a>
                <a class="footer-link logout" href="/logout.php">Se deconnecter</a>
            </div>
        </aside>

        <main class="dashboard-main profile-main">
            <header class="card main-header">
                <p class="subtitle">Articles</p>
                <h2>Mes brouillons</h2>
                <p class="welcome">Retrouvez vos articles en cours et publiez-les quand ils sont prets.</p>
            </header>

            <?php if ($flashSuccess !== ''): ?>
                <div class="notice success"><?php echo escape($flashSuccess); ?></div>
            <?php endif; ?>

            <?php if ($flashError !== ''): ?>
                <div class="notice error"><?php echo escape($flashError); ?></div>
            <?php endif; ?>

            <section class="card">
                <?php if ($articles === []): ?>
                    <p class="empty-state">Aucun brouillon trouve pour cet utilisateur.</p>
                <?php else: ?>
                    <ul class="article-list">
                        <?php foreach ($articles as $article): ?>
                            <li class="article-item">
                                <div class="article-head">
                                    <h4><?php echo escape($article->getTitre()); ?></h4>
                                    <p class="article-date"><?php echo escape($article->getDatePublication()); ?></p>
                                </div>

                                <div class="article-meta-grid">
                                    <div class="meta-item">
                                        <span class="meta-label">ID article</span>
                                        <strong><?php echo escape(nullableIntToString($article->getIdArticle())); ?></strong>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Vues</span>
                                        <strong><?php echo escape((string) $article->getNbrVues()); ?></strong>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Langue</span>
                                        <strong><?php echo escape($article->getLang()); ?></strong>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Categorie</span>
                                        <strong><?php echo escape(nullableIntToString($article->getIdCategorie())); ?></strong>
                                    </div>
                                </div>

                                <div class="actions-row">
                                    <a class="ghost-link" href="editer.php?idArticle=<?php echo $article->getIdArticle(); ?>">Editer</a>
                                    <a class="ghost-link" href="publier.php?idArticle=<?php echo $article->getIdArticle(); ?>">Publier</a>
                                    <a class="ghost-link" href="supprimer.php?idArticle=<?php echo $article->getIdArticle(); ?>">Supprimer</a>
                                </div>

                                <p class="content-label">Contenu</p>
                                <div class="article-content"><?php echo $article->getContenu(); ?></div>

                                <p class="article-footnote">
                                    Auteur principal: <?php echo escape(nullableIntToString($article->getIdUserPrincipal())); ?>
                                    | Statut: <?php echo escape(nullableIntToString($article->getIdStatusArticle())); ?>
                                </p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>