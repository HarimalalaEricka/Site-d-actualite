<?php

declare(strict_types=1);

use App\Controllers\Admin\TagController;
use App\Models\SessionLogin;

require_once dirname(__DIR__, 4) . '/app/Core/Database.php';
require_once dirname(__DIR__, 4) . '/app/Models/SessionLogin.php';
require_once dirname(__DIR__, 4) . '/app/Models/Tag.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/TagController.php';

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

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$tagController = new TagController();
$tags = $tagController->getAllTags();

$flashSuccess = (string) ($_SESSION['flash_success'] ?? '');
$flashError = (string) ($_SESSION['flash_error'] ?? '');
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Gestion des tags.">
    <title>Gestion Tags</title>
    <link rel="stylesheet" href="/assets/css/Admin/dashboard.css">
    <link rel="stylesheet" href="/assets/css/Admin/tag.css">
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
                <a class="nav-link" href="../Article/nouvelle.php">Articles</a>
                <a class="nav-link" href="../Categorie/gestion_categories.php">Categories</a>
                <a class="nav-link active" href="gestion.php">Tags</a>
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

        <main class="dashboard-main tag-main">
            <header class="card main-header">
                <p class="subtitle">Tags</p>
                <h2>Liste des tags</h2>
                <p class="welcome">Organisez les mots-cles pour mieux structurer vos articles.</p>
                <a class="action-link" href="nouvelle.php">Nouveau tag</a>
            </header>

            <?php if ($flashSuccess !== ''): ?>
                <div class="notice success"><?php echo escape($flashSuccess); ?></div>
            <?php endif; ?>

            <?php if ($flashError !== ''): ?>
                <div class="notice error"><?php echo escape($flashError); ?></div>
            <?php endif; ?>

            <section class="card">
                <?php if ($tags === []): ?>
                    <p class="empty-state">Aucun tag trouve.</p>
                <?php else: ?>
                    <ul class="entity-list">
                        <?php foreach ($tags as $tag): ?>
                            <li class="entity-item">
                                <div class="entity-head">
                                    <h3>#<?php echo escape($tag->getNom()); ?></h3>
                                    <span class="entity-id">#<?php echo (int) $tag->getIdTag(); ?></span>
                                </div>
                                <div class="actions-row">
                                    <a class="ghost-link" href="editer.php?idTag=<?php echo (int) $tag->getIdTag(); ?>">Modifier</a>
                                    <a class="ghost-link danger-link" href="supprimer.php?idTag=<?php echo (int) $tag->getIdTag(); ?>">Supprimer</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
