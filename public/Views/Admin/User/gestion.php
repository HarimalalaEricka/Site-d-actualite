<?php

declare(strict_types=1);

use App\Controllers\Admin\UserController;
use App\Controllers\Admin\RoleController;
use App\Models\SessionLogin;

require_once dirname(__DIR__, 4) . '/app/Core/Database.php';
require_once dirname(__DIR__, 4) . '/app/Models/SessionLogin.php';
require_once dirname(__DIR__, 4) . '/app/Models/User.php';
require_once dirname(__DIR__, 4) . '/app/Models/Role.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/UserController.php';
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

$userController = new UserController();
$users = $userController->getAllUsers();

$flashSuccess = (string) ($_SESSION['flash_success'] ?? '');
$flashError = (string) ($_SESSION['flash_error'] ?? '');
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Gestion des utilisateurs.">
    <title>Gestion Utilisateurs</title>
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
                <h2>Liste des utilisateurs</h2>
                <p class="welcome">Consultez et administrez les comptes du back-office.</p>
                <a class="action-link" href="nouvelle.php">Nouvel utilisateur</a>
            </header>

            <?php if ($flashSuccess !== ''): ?>
                <div class="notice success"><?php echo escape($flashSuccess); ?></div>
            <?php endif; ?>

            <?php if ($flashError !== ''): ?>
                <div class="notice error"><?php echo escape($flashError); ?></div>
            <?php endif; ?>

            <section class="card">
                <?php if ($users === []): ?>
                    <p class="empty-state">Aucun utilisateur trouve.</p>
                <?php else: ?>
                    <ul class="user-list">
                        <?php foreach ($users as $u): ?>
                            <li class="user-item">
                                <div class="user-head">
                                    <h3>
                                        <a class="profile-link" href="/Views/Admin/profile.php?idUser=<?php echo (int) $u->getIdUser(); ?>">
                                            <?php echo escape($u->getNom()); ?> <?php echo escape($u->getPrenom()); ?>
                                        </a>
                                    </h3>
                                    <span class="user-id">#<?php echo (int) $u->getIdUser(); ?></span>
                                </div>
                                <p class="user-meta"><strong>Email:</strong> <?php echo escape($u->getEmail()); ?></p>
                                <p class="user-meta"><strong>Telephone:</strong> <?php echo escape($u->getNumeroTel()); ?></p>
                                <div class="actions-row">
                                    <a class="ghost-link" href="editer.php?idUser=<?php echo (int) $u->getIdUser(); ?>">Modifier</a>
                                    <a class="ghost-link danger-link" href="supprimer.php?idUser=<?php echo (int) $u->getIdUser(); ?>">Supprimer</a>
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
