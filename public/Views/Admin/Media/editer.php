<?php

declare(strict_types=1);

use App\Controllers\Admin\MediaController;
use App\Models\Media;
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

$typeMediaChoices = $mediaController->getAllTypeMedia();
$articleChoices = $mediaController->getArticleChoices();

$errorMessage = '';

$url = ($_SERVER['REQUEST_METHOD'] === 'POST') ? trim((string) ($_POST['url'] ?? '')) : $media->getUrl();
$description = ($_SERVER['REQUEST_METHOD'] === 'POST') ? trim((string) ($_POST['description'] ?? '')) : $media->getDescription();
$priorite = ($_SERVER['REQUEST_METHOD'] === 'POST')
    ? (isset($_POST['priorite']) && (string) $_POST['priorite'] === '1')
    : $media->isPriorite();
$idTypeMedia = ($_SERVER['REQUEST_METHOD'] === 'POST')
    ? (int) ($_POST['idTypeMedia'] ?? 0)
    : (int) ($media->getIdTypeMedia() ?? 0);
$idArticle = ($_SERVER['REQUEST_METHOD'] === 'POST')
    ? (int) ($_POST['idArticle'] ?? 0)
    : (int) ($media->getIdArticle() ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($url === '' || $idTypeMedia <= 0 || $idArticle <= 0) {
        $errorMessage = 'URL, type media et article sont requis.';
    } else {
        try {
            $updatedMedia = new Media();
            $updatedMedia->setIdMedia($idMedia);
            $updatedMedia->setUrl($url);
            $updatedMedia->setDescription($description);
            $updatedMedia->setPriorite($priorite);
            $updatedMedia->setIdTypeMedia($idTypeMedia);
            $updatedMedia->setIdArticle($idArticle);

            $isUpdated = $mediaController->updateMedia($idMedia, $updatedMedia);

            if (!$isUpdated) {
                throw new RuntimeException('Mise a jour impossible.');
            }

            $_SESSION['flash_success'] = 'Media modifie avec succes.';
            header('Location: /Views/Admin/Media/gestion.php');
            exit;
        } catch (\Throwable $e) {
            $errorMessage = 'Impossible de modifier ce media.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Edition d'un media.">
    <title>Editer Media</title>
</head>
<body>
    <div class="layout">
        <h1>Medias</h1>
        <h2>Editer media</h2>

        <?php if ($errorMessage !== ''): ?>
            <p style="color: red;"><?php echo escape($errorMessage); ?></p>
        <?php endif; ?>

        <form action="editer.php?idMedia=<?php echo (int) $idMedia; ?>" method="post">
            <p>
                <label for="url">URL</label>
                <input type="text" id="url" name="url" value="<?php echo escape($url); ?>" required>
            </p>

            <p>
                <label for="description">Description</label>
                <textarea id="description" name="description"><?php echo escape($description); ?></textarea>
            </p>

            <p>
                <label for="idTypeMedia">Type media</label>
                <select id="idTypeMedia" name="idTypeMedia" required>
                    <option value="">Selectionner le type</option>
                    <?php foreach ($typeMediaChoices as $typeMedia): ?>
                        <?php $typeId = $typeMedia->getIdTypeMedia(); ?>
                        <?php if ($typeId !== null): ?>
                            <option value="<?php echo $typeId; ?>" <?php echo $idTypeMedia === $typeId ? 'selected' : ''; ?>>
                                <?php echo escape($typeMedia->getType()); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label for="idArticle">Article</label>
                <select id="idArticle" name="idArticle" required>
                    <option value="">Selectionner l'article</option>
                    <?php foreach ($articleChoices as $articleChoice): ?>
                        <option value="<?php echo (int) $articleChoice['id']; ?>" <?php echo $idArticle === (int) $articleChoice['id'] ? 'selected' : ''; ?>>
                            <?php echo escape($articleChoice['titre']); ?> (ID <?php echo (int) $articleChoice['id']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label>
                    <input type="checkbox" name="priorite" value="1" <?php echo $priorite ? 'checked' : ''; ?>>
                    Media prioritaire
                </label>
            </p>

            <button type="submit">Mettre a jour</button>
            <a href="gestion.php">Annuler</a>
        </form>
    </div>
</body>
</html>
