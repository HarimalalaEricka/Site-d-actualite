<?php

declare(strict_types=1);

use App\Controllers\Admin\ArticleController;
use App\Controllers\Admin\CategorieController;
use App\Controllers\Admin\CollaborationController;
use App\Controllers\Admin\TagController;
use App\Controllers\Admin\UserController;
use App\Controllers\Admin\MediaController;
use App\Models\Article;
use App\Models\Collaboration;
use App\Models\SessionLogin;

require_once dirname(__DIR__, 4) . '/app/Core/Database.php';
require_once dirname(__DIR__, 4) . '/app/Models/Article.php';
require_once dirname(__DIR__, 4) . '/app/Models/User.php';
require_once dirname(__DIR__, 4) . '/app/Models/SessionLogin.php';
require_once dirname(__DIR__, 4) . '/app/Models/Collaboration.php';
require_once dirname(__DIR__, 4) . '/app/Models/Categorie.php';
require_once dirname(__DIR__, 4) . '/app/Models/Tag.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/ArticleController.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/CategorieController.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/CollaborationController.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/UserController.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/TagController.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/MediaController.php';

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

$idArticle = isset($_GET['idArticle']) ? (int) $_GET['idArticle'] : 0;

if ($idArticle <= 0) {
    $_SESSION['flash_error'] = 'Article invalide.';
    header('Location: /Views/Admin/profile.php');
    exit;
}

$articleController = new ArticleController();
$article = $articleController->getArticleById($idArticle);

if ($article === null) {
    $_SESSION['flash_error'] = 'Article introuvable ou acces refuse.';
    header('Location: /Views/Admin/profile.php');
    exit;
}

$collaborationController = new CollaborationController();
$currentCollabs = $collaborationController->getCollaborationsByArticle($idArticle);
$currentCollabIds = array_values(array_unique(array_filter(
    array_map(static fn ($collab): ?int => $collab instanceof Collaboration ? $collab->getIdUser() : null, $currentCollabs),
    static fn ($id): bool => is_int($id) && $id > 0
)));

$isPrincipalAuthor = $article->getIdUserPrincipal() === $idUser;
$isCollaborator = in_array($idUser, $currentCollabIds, true);

if (!$isPrincipalAuthor && !$isCollaborator) {
    $_SESSION['flash_error'] = 'Article introuvable ou acces refuse.';
    header('Location: /Views/Admin/profile.php');
    exit;
}

$categorieController = new CategorieController();
$categories = $categorieController->getAllCategories();

$userController = new UserController();
$usersExept = $userController->getAllUsersExept($idUser);

$tagController = new TagController();
$tags = $tagController->getAllTags();
$currentTags = $tagController->getTagsByArticle($idArticle);
$currentTagIds = array_values(array_unique(array_filter(
    array_map(static fn ($tag): ?int => $tag->getIdTag(), $currentTags),
    static fn ($id): bool => is_int($id) && $id > 0
)));

$selectedCollaborators = $currentCollabIds;
$selectedTags = $currentTagIds;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim((string) ($_POST['titre'] ?? ''));
    $content = trim((string) ($_POST['content'] ?? ''));
    $idcategorie = isset($_POST['categorie']) ? (int) $_POST['categorie'] : null;

    $selectedCollaborators = array_values(array_unique(array_filter(
        array_map('intval', (array) ($_POST['collaborators'] ?? [])),
        static fn (int $value): bool => $value > 0
    )));

    $selectedTags = array_values(array_unique(array_filter(
        array_map('intval', (array) ($_POST['tags'] ?? [])),
        static fn (int $value): bool => $value > 0
    )));

    $lang = (string) ($article->getLang() ?? 'fr');

    if ($titre === '' || $content === '' || $idcategorie === null) {
        $errorMessage = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        try {
            $updatedArticle = new Article(
                $idArticle,
                $titre,
                $article->getDatePublication(),
                $content,
                $article->getNbrVues(),
                $article->getIdUserPrincipal(),
                $article->getIdStatusArticle(),
                $idcategorie,
                $lang
            );

            $isUpdated = $articleController->updateArticle($idArticle, $updatedArticle, $idUser);
            if (!$isUpdated) {
                throw new \RuntimeException('Impossible de mettre a jour l\'article.');
            }

            $allCollaboratorIds = array_values(array_unique(array_merge([$idUser], $selectedCollaborators)));

            foreach ($currentCollabIds as $collabId) {
                if (!in_array($collabId, $allCollaboratorIds, true)) {
                    $collaborationController->deleteCollaboration($collabId, $idArticle);
                }
            }

            foreach ($allCollaboratorIds as $collaboratorId) {
                if (!in_array($collaboratorId, $currentCollabIds, true)) {
                    $collaborationController->createCollaboration(new Collaboration($collaboratorId, $idArticle));
                }
            }

            foreach ($currentTagIds as $tagId) {
                if (!in_array($tagId, $selectedTags, true)) {
                    $tagController->removeTagFromArticle($idArticle, $tagId);
                }
            }

            foreach ($selectedTags as $tagId) {
                if (!in_array($tagId, $currentTagIds, true)) {
                    $tagController->assignTagToArticle($idArticle, $tagId);
                }
            }

            $mediaController = new MediaController();
            $mediaController->syncArticleMediaFromContent($idArticle, $content);

            $_SESSION['flash_success'] = 'Article modifie avec succes.';
            header('Location: /Views/Admin/profile.php');
            exit;
        } catch (Throwable $e) {
            $errorMessage = 'Erreur lors de la modification de l\'article: ' . $e->getMessage();
        }
    }
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editer Article</title>
    <link rel="stylesheet" href="/assets/css/Admin/dashboard.css">
    <link rel="stylesheet" href="/assets/css/Admin/article-form.css">
    <script src="/assets/js/tinymce/tinymce.min.js"></script>
    <script>
        function uploadEditorFile(file, onSuccess, onError) {
            const formData = new FormData();
            formData.append('file', file);

            fetch('/upload_tinymce_image.php', {
                method: 'POST',
                credentials: 'include',
                body: formData
            })
                .then(async (response) => {
                    const rawBody = await response.text();
                    let data = null;

                    try {
                        data = JSON.parse(rawBody);
                    } catch (e) {
                        const start = rawBody.indexOf('{');
                        const end = rawBody.lastIndexOf('}');
                        if (start !== -1 && end !== -1 && end > start) {
                            const candidate = rawBody.slice(start, end + 1);
                            try {
                                data = JSON.parse(candidate);
                            } catch (e2) {
                                throw new Error('Reponse serveur invalide');
                            }
                        } else {
                            throw new Error('Reponse serveur invalide');
                        }
                    }

                    if (!response.ok) {
                        throw new Error((data && data.error) ? data.error : 'Erreur serveur');
                    }

                    if (data && data.location) {
                        onSuccess(data.location);
                        return;
                    }
                    throw new Error((data && data.error) ? data.error : 'Upload invalide');
                })
                .catch((error) => onError(error instanceof Error ? error.message : 'Erreur lors de l\'upload'));
        }

        tinymce.init({
            selector: '#content',
            plugins: 'lists link image',
            toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link image',
            height: 300,
            automatic_uploads: true,
            images_upload_url: '/upload_tinymce_image.php',
            file_picker_types: 'image',
            images_file_types: 'jpg,jpeg,png,gif,webp',
            file_picker_callback: function (callback, value, meta) {
                const input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');

                input.onchange = function () {
                    const file = input.files && input.files.length > 0 ? input.files[0] : null;

                    if (!file) {
                        return;
                    }

                    uploadEditorFile(file, function (url) {
                        callback(url, { alt: file.name });
                    }, function (errorMessage) {
                        alert(errorMessage);
                    });
                };

                input.click();
            },
            license_key: 'gpl'
        });
    </script>
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
                <a class="nav-link active" href="nouvelle.php">Articles</a>
                <a class="nav-link" href="../Categorie/gestion_categories.php">Categories</a>
                <a class="nav-link" href="../Tag/gestion.php">Tags</a>
                <a class="nav-link" href="../Media/gestion.php">Medias</a>
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

        <main class="dashboard-main article-main">
            <header class="card main-header">
                <p class="subtitle">Articles</p>
                <h2>Editer l'article</h2>
                <p class="welcome">Mettez a jour le contenu, la categorie, les collaborateurs et les tags.</p>
            </header>

            <?php if ($errorMessage !== ''): ?>
                <div class="notice error"><?php echo escape($errorMessage); ?></div>
            <?php endif; ?>

            <section class="card article-form-card">
                <form method="post" class="article-form">
                    <div class="form-grid">
                        <div>
                            <label for="titre">Titre</label>
                            <input type="text" id="titre" name="titre" value="<?php echo escape($titre ?? $article->getTitre()); ?>" required>
                        </div>

                        <div>
                            <label for="categorie">Categorie</label>
                            <select id="categorie" name="categorie" required>
                                <option value="">Selectionner une categorie</option>
                                <?php foreach ($categories as $cat): ?>
                                    <?php $catId = $cat->getIdCategorie(); ?>
                                    <?php $selectedCatId = isset($idcategorie) ? $idcategorie : $article->getIdCategorie(); ?>
                                    <option value="<?php echo (int) $catId; ?>" <?php echo $catId === $selectedCatId ? 'selected' : ''; ?>>
                                        <?php echo escape((string) $cat->getCategorie()); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="collaborators">Collaborateurs</label>
                            <select id="collaborators" name="collaborators[]" multiple size="6">
                                <?php foreach ($usersExept as $user): ?>
                                    <?php $optionUserId = $user->getIdUser(); ?>
                                    <?php if ($optionUserId !== null): ?>
                                        <option value="<?php echo $optionUserId; ?>" <?php echo in_array($optionUserId, $selectedCollaborators, true) ? 'selected' : ''; ?>>
                                            <?php echo escape(trim($user->getPrenom() . ' ' . $user->getNom()) . ' - ' . $user->getEmail()); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="tags">Tags</label>
                            <select id="tags" name="tags[]" multiple size="6">
                                <?php foreach ($tags as $tag): ?>
                                    <?php $tagId = $tag->getIdTag(); ?>
                                    <?php if ($tagId !== null): ?>
                                        <option value="<?php echo $tagId; ?>" <?php echo in_array($tagId, $selectedTags, true) ? 'selected' : ''; ?>>
                                            #<?php echo escape($tag->getNom()); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="content">Contenu</label>
                        <textarea id="content" name="content" required><?php echo escape($content ?? $article->getContenu()); ?></textarea>
                    </div>

                    <div class="actions-row">
                        <button type="submit" class="action-link">Mettre a jour</button>
                        <a href="../profile.php" class="ghost-link">Annuler</a>
                    </div>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
