<?php

declare(strict_types=1);
use App\Models\SessionLogin;
require_once dirname(__DIR__, 3) . '/app/Models/SessionLogin.php';

session_start();

if (!isset($_SESSION['login']) || !($_SESSION['login'] instanceof SessionLogin) || $_SESSION['login']->getUserLoggedIn() !== true) {
    header('Location: /admin.php');
    exit;
}
$login = $_SESSION['login'];
$idUser = $login->getIdUser();
$role = $login->getRole();

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="BackOffice d'un site d'actualite sur la guerre en Iran.">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="/assets/css/Admin/dashboard.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h1>Back-office</h1>
                <p>Site d'actualite</p>
            </div>

            <nav class="sidebar-nav" aria-label="Navigation principale">
                <a class="nav-link active" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="Article/nouvelle.php">Articles</a>
                <a class="nav-link" href="Categorie/gestion_categories.php">Categories</a>
                <a class="nav-link" href="Tag/gestion.php">Tags</a>
                <!-- <a class="nav-link" href="Media/gestion.php">Medias</a> -->
                <a class="nav-link" href="User/gestion.php">Gestion utilisateurs</a>
                <?php
                    if ($role === 'admin') {
                        echo '<a class="nav-link" href="Role/role.php">Roles & permissions</a>';
                    }
                ?>
            </nav>

            <div class="sidebar-footer">
                <a class="footer-link" href="profile.php">Mon profil</a>
                <a class="footer-link logout" href="/logout.php">Se deconnecter</a>
            </div>
        </aside>

        <main class="dashboard-main">
            <header class="main-header card">
                <p class="subtitle">Dashboard admin</p>
                <h2>Bienvenue sur votre espace de gestion</h2>
                <p class="welcome">Utilisateur connecte : #<?= htmlspecialchars((string)$idUser, ENT_QUOTES, 'UTF-8') ?> (role: <?= htmlspecialchars((string)$role, ENT_QUOTES, 'UTF-8') ?>)</p>
            </header>

            <section class="cards-grid">
                <article class="card">
                    <h3>Gestion rapide</h3>
                    <p>Accedez en un clic aux sections importantes du back-office.</p>
                    <a class="action-link" href="User/gestion.php">Ouvrir la gestion des utilisateurs</a>
                </article>

                <article class="card">
                    <h3>Publication</h3>
                    <p>Creez, modifiez et publiez les derniers articles du site.</p>
                    <a class="action-link" href="Article/nouvelle.php">Ajouter un article</a>
                </article>

                <article class="card">
                    <h3>Mediatheque</h3>
                    <p>Organisez les medias utilises par les contenus redactionnels.</p>
                    <a class="action-link" href="Media/gestion.php">Gerer les medias</a>
                </article>
            </section>
        </main>
    </div>
</body>
</html>
