<?php

declare(strict_types=1);

use App\Controllers\Admin\RoleController;
use App\Models\SessionLogin;

require_once dirname(__DIR__, 4) . '/app/Core/Database.php';
require_once dirname(__DIR__, 4) . '/app/Models/SessionLogin.php';
require_once dirname(__DIR__, 4) . '/app/Models/Role.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/RoleController.php';

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

$idRole = isset($_GET['idRole']) ? (int) $_GET['idRole'] : 0;

if ($idRole <= 0) {
    $_SESSION['flash_error'] = 'Role invalide.';
    header('Location: /Views/Admin/Role/role.php');
    exit;
}

$roleController = new RoleController();
$role = $roleController->getRoleById($idRole);

if ($role === null) {
    $_SESSION['flash_error'] = 'Role introuvable.';
    header('Location: /Views/Admin/Role/role.php');
    exit;
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = (string) ($_POST['confirm'] ?? '');

    if ($confirm !== 'yes') {
        header('Location: /Views/Admin/Role/role.php');
        exit;
    }

    try {
        $deleted = $roleController->deleteRole($idRole);

        if (!$deleted) {
            $_SESSION['flash_error'] = 'Suppression impossible: role introuvable.';
            header('Location: /Views/Admin/Role/role.php');
            exit;
        }

        $_SESSION['flash_success'] = 'Role supprime avec succes.';
        header('Location: /Views/Admin/Role/role.php');
        exit;
    } catch (\Throwable $e) {
        $errorMessage = 'Suppression impossible. Ce role est peut-etre utilise par des utilisateurs.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Suppression d'un role utilisateur.">
    <title>Supprimer Role</title>
    <link rel="stylesheet" href="/assets/css/Admin/dashboard.css">
    <link rel="stylesheet" href="/assets/css/Admin/role.css">
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
                <a class="nav-link" href="../User/gestion.php">Gestion utilisateurs</a>
                <a class="nav-link active" href="role.php">Roles & permissions</a>
            </nav>

            <div class="sidebar-footer">
                <a class="footer-link" href="../profile.php">Mon profil</a>
                <a class="footer-link logout" href="/logout.php">Se deconnecter</a>
            </div>
        </aside>

        <main class="dashboard-main role-main">
            <header class="card main-header">
                <p class="subtitle">Roles</p>
                <h2>Supprimer role</h2>
                <p class="welcome">Cette suppression est irreversible.</p>
            </header>

            <?php if ($errorMessage !== ''): ?>
                <div class="notice error"><?php echo escape($errorMessage); ?></div>
            <?php endif; ?>

            <section class="card delete-card">
                <p class="delete-warning">Voulez-vous vraiment supprimer ce role ?</p>
                <p class="delete-target"><?php echo escape($role->getRole()); ?></p>

                <form action="supprimer.php?idRole=<?php echo (int) $idRole; ?>" method="post" class="actions-row">
                    <input type="hidden" name="confirm" value="yes">
                    <button type="submit" class="danger-btn">Confirmer la suppression</button>
                    <a href="role.php" class="ghost-link">Annuler</a>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
