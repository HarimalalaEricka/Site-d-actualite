<?php

declare(strict_types=1);

use App\Controllers\Admin\UserController;
use App\Models\SessionLogin;

require_once dirname(__DIR__, 4) . '/app/Core/Database.php';
require_once dirname(__DIR__, 4) . '/app/Models/SessionLogin.php';
require_once dirname(__DIR__, 4) . '/app/Models/User.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/UserController.php';

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

$idUserTarget = isset($_GET['idUser']) ? (int) $_GET['idUser'] : 0;

if ($idUserTarget <= 0) {
    $_SESSION['flash_error'] = 'Utilisateur invalide.';
    header('Location: /Views/Admin/User/gestion.php');
    exit;
}

$userController = new UserController();
$user = $userController->getUserById($idUserTarget);

if ($user === null) {
    $_SESSION['flash_error'] = 'Utilisateur introuvable.';
    header('Location: /Views/Admin/User/gestion.php');
    exit;
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = (string) ($_POST['confirm'] ?? '');

    if ($confirm !== 'yes') {
        header('Location: /Views/Admin/User/gestion.php');
        exit;
    }

    try {
        $deleted = $userController->deleteUser($idUserTarget);

        if (!$deleted) {
            $_SESSION['flash_error'] = 'Suppression impossible: utilisateur introuvable.';
            header('Location: /Views/Admin/User/gestion.php');
            exit;
        }

        $_SESSION['flash_success'] = 'Utilisateur supprime avec succes.';
        header('Location: /Views/Admin/User/gestion.php');
        exit;
    } catch (\Throwable $e) {
        $errorMessage = 'Suppression impossible. Cet utilisateur est peut-etre utilise par des articles ou articles.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Suppression d'un utilisateur.">
    <title>Supprimer Utilisateur</title>
    <link rel="stylesheet" href="/assets/css/Admin/dashboard.css">
    <link rel="stylesheet" href="/assets/css/Admin/user.css">
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
                <a class="nav-link" href="../Tag/gestion.php">Tags</a>
                <a class="nav-link active" href="gestion.php">Gestion utilisateurs</a>
                <?php if ($role === 'admin'): ?>
                    <a class="nav-link" href="../Role/role.php">Roles & permissions</a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <a class="footer-link" href="../profile.php">Mon profil</a>
                <a class="footer-link logout" href="/logout.php">Se deconnecter</a>
            </div>
        </aside>

        <main class="dashboard-main user-main">
            <header class="card main-header">
                <p class="subtitle">Utilisateurs</p>
                <h2>Supprimer utilisateur</h2>
                <p class="welcome">Cette suppression est irreversible.</p>
            </header>

            <?php if ($errorMessage !== ''): ?>
                <div class="notice error"><?php echo escape($errorMessage); ?></div>
            <?php endif; ?>

            <section class="card delete-card">
                <p class="delete-warning">Voulez-vous vraiment supprimer cet utilisateur ?</p>
                <p class="delete-target"><?php echo escape($user->getNom()); ?> <?php echo escape($user->getPrenom()); ?></p>
                <p class="delete-email">Email: <strong><?php echo escape($user->getEmail()); ?></strong></p>

                <form action="supprimer.php?idUser=<?php echo (int) $idUserTarget; ?>" method="post" class="actions-row">
                    <input type="hidden" name="confirm" value="yes">
                    <button type="submit" class="danger-btn">Confirmer la suppression</button>
                    <a href="gestion.php" class="ghost-link">Annuler</a>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
