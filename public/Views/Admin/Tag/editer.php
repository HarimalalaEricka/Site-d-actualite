<?php

declare(strict_types=1);

use App\Controllers\Admin\TagController;
use App\Models\Tag;
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
    $tagName = trim((string) ($_POST['nom'] ?? ''));

    if ($tagName === '') {
        $errorMessage = 'Le nom du tag est requis.';
    } else {
        try {
            $updatedTag = new Tag();
            $updatedTag->setIdTag($idTag);
            $updatedTag->setNom($tagName);

            $isUpdated = $tagController->updateTag($idTag, $updatedTag);

            if (!$isUpdated) {
                throw new RuntimeException('Impossible de mettre a jour le tag.');
            }

            $_SESSION['flash_success'] = 'Tag modifie avec succes.';
            header('Location: /Views/Admin/Tag/gestion.php');
            exit;
        } catch (\Throwable $e) {
            $errorMessage = 'Impossible de modifier ce tag. Il existe peut-etre deja.';
        }
    }
}

$formTag = ($_SERVER['REQUEST_METHOD'] === 'POST')
    ? trim((string) ($_POST['nom'] ?? ''))
    : $tag->getNom();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Edition d'un tag.">
    <title>Editer Tag</title>
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
                <h2>Editer tag</h2>
                <p class="welcome">Modifiez le nom de votre tag.</p>
            </header>

            <?php if ($errorMessage !== ''): ?>
                <div class="notice error"><?php echo escape($errorMessage); ?></div>
            <?php endif; ?>

            <section class="card entity-form-card">
                <form action="editer.php?idTag=<?php echo (int) $idTag; ?>" method="post" class="entity-form">
                    <div>
                        <label for="nom">Nom du tag</label>
                        <input type="text" id="nom" name="nom" value="<?php echo escape($formTag); ?>" required>
                    </div>
                    <div class="actions-row">
                        <button type="submit" class="action-link">Mettre a jour le tag</button>
                        <a href="gestion.php" class="ghost-link">Annuler</a>
                    </div>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
