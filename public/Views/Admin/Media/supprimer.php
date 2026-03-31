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

$idMedia = isset($_GET['idMedia']) ? (int) $_GET['idMedia'] : 0;

if ($idMedia <= 0) {
    $_SESSION['flash_error'] = 'Media invalide.';
    header('Location: /Views/Admin/Media/gestion.php');
    exit;
}

$mediaController = new MediaController();
$media = $mediaController->getMediaById($idMedia);

if ($media === null) {
    $_SESSION['flash_error'] = 'Media introuvable.';
    header('Location: /Views/Admin/Media/gestion.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = (string) ($_POST['confirm'] ?? '');

    if ($confirm !== 'yes') {
        header('Location: /Views/Admin/Media/gestion.php');
        exit;
    }

    try {
        $deleted = $mediaController->deleteMedia($idMedia);

        if (!$deleted) {
            $_SESSION['flash_error'] = 'Suppression impossible: media introuvable.';
            header('Location: /Views/Admin/Media/gestion.php');
            exit;
        }

        $_SESSION['flash_success'] = 'Media supprime avec succes.';
        header('Location: /Views/Admin/Media/gestion.php');
        exit;
    } catch (\Throwable $e) {
        $_SESSION['flash_error'] = 'Suppression impossible pour ce media.';
        header('Location: /Views/Admin/Media/gestion.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Suppression d'un media.">
    <title>Supprimer Media</title>
</head>
<body>
    <div class="layout">
        <h1>Medias</h1>
        <h2>Supprimer media</h2>

        <p>Voulez-vous vraiment supprimer ce media ?</p>
        <p><strong><?php echo escape($media->getUrl()); ?></strong></p>

        <form action="supprimer.php?idMedia=<?php echo (int) $idMedia; ?>" method="post">
            <input type="hidden" name="confirm" value="yes">
            <button type="submit">Confirmer la suppression</button>
            <a href="gestion.php">Annuler</a>
        </form>
    </div>
</body>
</html>
