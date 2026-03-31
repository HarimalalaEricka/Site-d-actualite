<?php
    
declare(strict_types=1);

use App\Controllers\Admin\ArticleController;
use App\Controllers\Admin\CategorieController;
use App\Controllers\Admin\CollaborationController;
use App\Controllers\Admin\HitoPublicationController;
use App\Controllers\Admin\UserController;
use App\Controllers\Admin\TagController;
use App\Controllers\Admin\MediaController;
use App\Models\SessionLogin;
use App\Models\Categorie;
use App\Models\Article;
use App\Models\Collaboration;
use App\Models\User;
use App\Models\Tag;

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
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/HitoPublicationController.php';
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

$categorieController = new CategorieController();
$categories = $categorieController->getAllCategories();
$userController = new UserController();
$usersExept = $userController->getAllUsersExept($idUser);
$tagController = new TagController();
$tags = $tagController->getAllTags();
$selectedCollaborators = [];
$selectedTags = [];
$errorMessage = '';
$successMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $titre = trim((string) ($_POST['titre'] ?? ''));
    $datepub = date('Y-m-d H:i:s');
    $content = trim((string) ($_POST['content'] ?? ''));
    $nbrvues = 0;
    $idUserPrincipal = $idUser;
    $idStatusArticle = 1;
    $idcategorie = isset($_POST['categorie']) ? (int) $_POST['categorie'] : null;
    $selectedCollaborators = array_values(array_unique(array_filter(
        array_map('intval', (array) ($_POST['collaborators'] ?? [])),
        static fn (int $value): bool => $value > 0
    )));
    $selectedTags = array_values(array_unique(array_filter(
        array_map('intval', (array) ($_POST['tags'] ?? [])),
        static fn (int $value): bool => $value > 0
    )));
    $lang = 'fr';
    if ($titre === '' || $content === '' || $idcategorie === null) {
        $errorMessage = 'Veuillez remplir tous les champs.';
    } else {
        try {
            $articleController = new ArticleController();
            $article = new Article(null, $titre, $datepub, $content, $nbrvues, $idUserPrincipal, $idStatusArticle, $idcategorie, $lang);
            $collaborationController = new CollaborationController();
            $hitoPublicationController = new HitoPublicationController();
            $idArticle = $articleController->createArticle($article);

            if ($idArticle === null) {
                throw new \RuntimeException('Impossible de recuperer l\'id de l\'article cree.');
            }

            $hitoId = $hitoPublicationController->createHitoPublication('creation', $idArticle, $idUser);
            if ($hitoId === null) {
                throw new \RuntimeException('Impossible de creer l\'historique de publication.');
            }

            $allCollaboratorIds = array_values(array_unique(array_merge([$idUser], $selectedCollaborators)));

            foreach ($allCollaboratorIds as $collaboratorId) {
                $collab = new Collaboration($collaboratorId, $idArticle);
                $collaborationController->createCollaboration($collab);
            }

            foreach ($selectedTags as $tagId) {
                $tagController->assignTagToArticle($idArticle, $tagId);
            }

            $mediaController = new MediaController();
            $mediaController->syncArticleMediaFromContent($idArticle, $content);

            $_SESSION['flash_success'] = 'Article cree avec succes.';
            header('Location: /Views/Admin/profile.php');
            exit;
        } catch (Throwable $e) {
            $errorMessage = 'Erreur lors de la création de l\'article: ' . $e->getMessage();
        }
    }
}
function escape(string $value): string
{
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Ajout d'une nouvelle article sur la guerre en Iran.">
    <title>Nouvel article</title>
    <link rel="stylesheet" href="/assets/css/Admin/dashboard.css">
    <link rel="stylesheet" href="/assets/css/Admin/article-form.css">
    <script src="/assets/js/tinymce/tinymce.min.js"></script>
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
                <h2>Nouvel article</h2>
                <p class="welcome">Redigez et publiez un nouvel article avec collaborateurs, tags et media.</p>
            </header>

            <?php if ($errorMessage !== ''): ?>
                <div class="notice error"><?php echo escape($errorMessage); ?></div>
            <?php endif; ?>

            <?php if ($successMessage !== ''): ?>
                <div class="notice success"><?php echo escape($successMessage); ?></div>
            <?php endif; ?>

            <section class="card article-form-card">
                <form action="nouvelle.php" id="myForm" method="post" class="article-form">
                    <div class="form-grid">
                        <div>
                            <label for="titre">Titre</label>
                            <input type="text" id="titre" name="titre" value="<?php echo escape((string) ($_POST['titre'] ?? '')); ?>" required>
                        </div>

                        <div>
                            <label for="categorie">Categorie</label>
                            <select name="categorie" id="categorie" required>
                                <option value="">Selectionner la categorie</option>
                                <?php foreach ($categories as $categorie): ?>
                                    <?php $catId = $categorie->getIdCategorie(); ?>
                                    <option value="<?php echo $catId; ?>" <?php echo (isset($_POST['categorie']) && (int) $_POST['categorie'] === (int) $catId) ? 'selected' : ''; ?>>
                                        <?php echo escape((string) $categorie->getCategorie()); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="collaborators">Collaborateurs</label>
                            <select name="collaborators[]" id="collaborators" multiple size="6">
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
                            <select name="tags[]" id="tags" multiple size="6">
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
                        <label for="myEditor">Contenu</label>
                        <textarea id="myEditor" name="content"><?php echo escape((string) ($_POST['content'] ?? '')); ?></textarea>
                    </div>

                    <div class="actions-row">
                        <button type="submit" class="action-link">Enregistrer</button>
                        <a href="../profile.php" class="ghost-link">Annuler</a>
                    </div>
                </form>
            </section>
        </main>
    </div>

    <script>
    // Initialisation de TinyMCE
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
        selector: '#myEditor',
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
</body>
</html>