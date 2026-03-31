<?php

declare(strict_types=1);

use App\Controllers\Admin\MediaController;
use App\Models\SessionLogin;

require_once dirname(__DIR__, 4) . '/app/Core/Database.php';
require_once dirname(__DIR__, 4) . '/app/Models/SessionLogin.php';
require_once dirname(__DIR__, 4) . '/app/Models/Media.php';
require_once dirname(__DIR__, 4) . '/app/Models/TypeMedia.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/MediaController.php';

session_start();

if (!isset($_SESSION['login']) || !($_SESSION['login'] instanceof SessionLogin) || $_SESSION['login']->getUserLoggedIn() !== true) {
    header('Location: /admin.php');
    exit;
}

$login = $_SESSION['login'];
$idUser = $login->getIdUser();

if ($idUser === null) {
    header('Location: /logout.php');
    exit;
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$mediaController = new MediaController();
$medias = $mediaController->getAllMediaWithDetails();

$flashSuccess = (string) ($_SESSION['flash_success'] ?? '');
$flashError = (string) ($_SESSION['flash_error'] ?? '');
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Gestion des medias.">
    <title>Gestion Medias</title>
</head>
<body>
    <div class="layout">
        <h1>Medias</h1>
        <h2>Liste des medias</h2>

        <a href="nouvelle.php">Nouveau media</a>
        <a href="/Views/Admin/Categorie/gestion_categories.php">Gestion categorie</a>
        <a href="/Views/Admin/Role/role.php">Gestion Role</a>
        <a href="/Views/Admin/User/gestion.php">Gestion Utilisateurs</a>
        <a href="/Views/Admin/Tag/gestion.php">Gestion Tags</a>

        <?php if ($flashSuccess !== ''): ?>
            <p style="color: green;"><?php echo escape($flashSuccess); ?></p>
        <?php endif; ?>

        <?php if ($flashError !== ''): ?>
            <p style="color: red;"><?php echo escape($flashError); ?></p>
        <?php endif; ?>

        <?php if ($medias === []): ?>
            <p>Aucun media trouve.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($medias as $media): ?>
                    <li>
                        <h3><?php echo escape($media['url']); ?></h3>
                        <p><strong>Article:</strong> <?php echo escape($media['articleTitre']); ?> (ID <?php echo (int) $media['idArticle']; ?>)</p>
                        <p><strong>Type:</strong> <?php echo escape($media['typeMedia']); ?></p>
                        <p><strong>Priorite:</strong> <?php echo $media['priorite'] ? 'Oui' : 'Non'; ?></p>
                        <p><strong>Description:</strong> <?php echo escape($media['description']); ?></p>
                        <a href="editer.php?idMedia=<?php echo (int) $media['id']; ?>">Modifier</a>
                        <a href="supprimer.php?idMedia=<?php echo (int) $media['id']; ?>">Supprimer</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
