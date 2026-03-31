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

$roleController = new RoleController();
$roles = $roleController->getAllRoles();

$flashSuccess = (string) ($_SESSION['flash_success'] ?? '');
$flashError = (string) ($_SESSION['flash_error'] ?? '');
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Gestion des roles utilisateurs.">
    <title>Gestion Role</title>
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
                <h2>Liste des roles</h2>
                <p class="welcome">Gerez les niveaux d'acces du back-office.</p>
                <a class="action-link" href="nouvelle.php">Nouveau role</a>
            </header>

            <?php if ($flashSuccess !== ''): ?>
                <div class="notice success"><?php echo escape($flashSuccess); ?></div>
            <?php endif; ?>

            <?php if ($flashError !== ''): ?>
                <div class="notice error"><?php echo escape($flashError); ?></div>
            <?php endif; ?>

            <section class="card">
                <?php if ($roles === []): ?>
                    <p class="empty-state">Aucun role trouve.</p>
                <?php else: ?>
                    <ul class="role-list">
                        <?php foreach ($roles as $itemRole): ?>
                            <li class="role-item">
                                <div class="role-head">
                                    <h3><?php echo escape($itemRole->getRole()); ?></h3>
                                    <span class="role-id">#<?php echo (int) $itemRole->getIdRole(); ?></span>
                                </div>
                                <div class="actions-row">
                                    <a class="ghost-link" href="editer.php?idRole=<?php echo (int) $itemRole->getIdRole(); ?>">Modifier</a>
                                    <a class="ghost-link danger-link" href="supprimer.php?idRole=<?php echo (int) $itemRole->getIdRole(); ?>">Supprimer</a>
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
