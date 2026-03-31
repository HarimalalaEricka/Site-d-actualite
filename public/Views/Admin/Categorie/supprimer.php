<?php

declare(strict_types=1);

use App\Controllers\Admin\CategorieController;
use App\Models\SessionLogin;

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

$idCategorie = isset($_GET['idCat']) ? (int) $_GET['idCat'] : 0;

if ($idCategorie <= 0) {
    $_SESSION['flash_error'] = 'Categorie invalide.';
    header('Location: /Views/Admin/Categorie/gestion_categories.php');
    exit;
}

$categorieController = new CategorieController();
$categorie = $categorieController->getCategorieById($idCategorie);

if ($categorie === null) {
    $_SESSION['flash_error'] = 'Categorie introuvable.';
    header('Location: /Views/Admin/Categorie/gestion_categories.php');
    exit;
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = (string) ($_POST['confirm'] ?? '');

    if ($confirm !== 'yes') {
        header('Location: /Views/Admin/Categorie/gestion_categories.php');
        exit;
    }

    try {
        $deleted = $categorieController->deleteCategorie($idCategorie);

        if (!$deleted) {
            $_SESSION['flash_error'] = 'Suppression impossible: categorie introuvable.';
            header('Location: /Views/Admin/Categorie/gestion_categories.php');
            exit;
        }

        $_SESSION['flash_success'] = 'Categorie supprimee avec succes.';
        header('Location: /Views/Admin/Categorie/gestion_categories.php');
        exit;
    } catch (\Throwable $e) {
        $errorMessage = 'Suppression impossible. Cette categorie est peut-etre utilisee par des articles.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Suppression d'une categorie d'articles.">
    <title>Supprimer Categorie</title>
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
                <h2>Supprimer categorie</h2>
                <p class="welcome">Cette action est irreversible.</p>
            </header>

            <?php if ($errorMessage !== ''): ?>
                <div class="notice error"><?php echo escape($errorMessage); ?></div>
            <?php endif; ?>

            <section class="card delete-card">
                <p class="delete-warning">Voulez-vous vraiment supprimer cette categorie ?</p>
                <p class="delete-target"><?php echo escape($categorie->getCategorie()); ?></p>

                <form action="supprimer.php?idCat=<?php echo (int) $idCategorie; ?>" method="post" class="actions-row">
                    <input type="hidden" name="confirm" value="yes">
                    <button type="submit" class="danger-btn">Confirmer la suppression</button>
                    <a href="gestion_categories.php" class="ghost-link">Annuler</a>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
