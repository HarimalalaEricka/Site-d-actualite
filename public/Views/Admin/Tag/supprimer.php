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

$idTag = isset($_GET['idTag']) ? (int) $_GET['idTag'] : 0;

if ($idTag <= 0) {
    $_SESSION['flash_error'] = 'Tag invalide.';
    header('Location: /Views/Admin/Tag/gestion.php');
    exit;
}

$tagController = new TagController();
$tag = $tagController->getTagById($idTag);

if ($tag === null) {
    $_SESSION['flash_error'] = 'Tag introuvable.';
    header('Location: /Views/Admin/Tag/gestion.php');
    exit;
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = (string) ($_POST['confirm'] ?? '');

    if ($confirm !== 'yes') {
        header('Location: /Views/Admin/Tag/gestion.php');
        exit;
    }

    try {
        $deleted = $tagController->deleteTag($idTag);

        if (!$deleted) {
            $_SESSION['flash_error'] = 'Suppression impossible: tag introuvable.';
            header('Location: /Views/Admin/Tag/gestion.php');
            exit;
        }

        $_SESSION['flash_success'] = 'Tag supprime avec succes.';
        header('Location: /Views/Admin/Tag/gestion.php');
        exit;
    } catch (\Throwable $e) {
        $errorMessage = 'Suppression impossible. Ce tag est peut-etre utilise par des articles.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Suppression d'un tag.">
    <title>Supprimer Tag</title>
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
                <h2>Supprimer tag</h2>
                <p class="welcome">Cette suppression est irreversible.</p>
            </header>

            <?php if ($errorMessage !== ''): ?>
                <div class="notice error"><?php echo escape($errorMessage); ?></div>
            <?php endif; ?>

            <section class="card delete-card">
                <p class="delete-warning">Voulez-vous vraiment supprimer ce tag ?</p>
                <p class="delete-target">#<?php echo escape($tag->getNom()); ?></p>

                <form action="supprimer.php?idTag=<?php echo (int) $idTag; ?>" method="post" class="actions-row">
                    <input type="hidden" name="confirm" value="yes">
                    <button type="submit" class="danger-btn">Confirmer la suppression</button>
                    <a href="gestion.php" class="ghost-link">Annuler</a>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
