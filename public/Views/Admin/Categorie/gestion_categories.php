<?php
    
declare(strict_types=1);

use App\Controllers\Admin\CategorieController;
use App\Models\SessionLogin;
use App\Models\Categorie;

require_once dirname(__DIR__, 4) . '/app/Core/Database.php';
require_once dirname(__DIR__, 4) . '/app/Models/SessionLogin.php';
require_once dirname(__DIR__, 4) . '/app/Models/Categorie.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/CategorieController.php';

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
$categorieController = new CategorieController();
$categories = $categorieController->getAllCategories();

$flashSuccess = (string) ($_SESSION['flash_success'] ?? '');
$flashError = (string) ($_SESSION['flash_error'] ?? '');
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Gestion des categories sur les articles sur la guerre en Iran.">
    <title>Gestion categories</title>
    <link rel="stylesheet" href="/assets/css/Admin/dashboard.css">
    <link rel="stylesheet" href="/assets/css/Admin/categorie.css">
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
                <a class="nav-link active" href="gestion_categories.php">Categories</a>
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

        <main class="dashboard-main category-main">
            <header class="card main-header">
                <p class="subtitle">Categories</p>
                <h2>Liste des categories</h2>
                <p class="welcome">Administrez les categories des articles de votre site.</p>
                <a class="action-link" href="nouvelle.php">Nouvelle categorie</a>
            </header>

            <?php if ($flashSuccess !== ''): ?>
                <div class="notice success"><?php echo escape($flashSuccess); ?></div>
            <?php endif; ?>

            <?php if ($flashError !== ''): ?>
                <div class="notice error"><?php echo escape($flashError); ?></div>
            <?php endif; ?>

            <section class="card">
                <?php if ($categories === []): ?>
                    <p class="empty-state">Aucune categorie trouvee.</p>
                <?php else: ?>
                    <ul class="category-list">
                        <?php foreach ($categories as $cat): ?>
                            <li class="category-item">
                                <div class="category-head">
                                    <h3><?php echo escape($cat->getCategorie()); ?></h3>
                                    <span class="category-id">#<?php echo escape((string) $cat->getIdCategorie()); ?></span>
                                </div>
                                <p class="category-description"><?php echo escape($cat->getDescription()); ?></p>
                                <div class="actions-row">
                                    <a class="ghost-link" href="editer.php?idCat=<?php echo escape((string) $cat->getIdCategorie()); ?>">Modifier</a>
                                    <a class="ghost-link danger-link" href="supprimer.php?idCat=<?php echo escape((string) $cat->getIdCategorie()); ?>">Supprimer</a>
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