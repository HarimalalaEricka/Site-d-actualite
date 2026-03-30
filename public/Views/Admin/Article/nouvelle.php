<?php
    
declare(strict_types=1);

use App\Controllers\Admin\ArticleController;
use App\Controllers\Admin\CategorieController;
use App\Controllers\Admin\CollaborationController;
use App\Controllers\Admin\HitoPublicationController;
use App\Controllers\Admin\UserController;
use App\Models\SessionLogin;
use App\Models\Categorie;
use App\Models\Article;
use App\Models\Collaboration;
use App\Models\User;

require_once dirname(__DIR__, 4) . '/app/Core/Database.php';
require_once dirname(__DIR__, 4) . '/app/Models/Article.php';
require_once dirname(__DIR__, 4) . '/app/Models/User.php';
require_once dirname(__DIR__, 4) . '/app/Models/SessionLogin.php';
require_once dirname(__DIR__, 4) . '/app/Models/Collaboration.php';
require_once dirname(__DIR__, 4) . '/app/Models/Categorie.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/ArticleController.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/CategorieController.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/CollaborationController.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/HitoPublicationController.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/UserController.php';

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

$categorieController = new CategorieController();
$categories = $categorieController->getAllCategories();
$userController = new UserController();
$usersExept = $userController->getAllUsersExept($idUser);
$selectedCollaborators = [];
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
    $lang = 'fr';
    if ($titre === '' || $content === '' || $idcategorie === null) {
        $errorMessage = 'Veuillez remplir tous les champs.';
    } else {
        try {
            $articleController = new ArticleController();
            $article = new Article(
                idArticle: null,
                titre: $titre,
                slug: '',
                datePublication: $datepub,
                contenu: $content,
                nbrVues: $nbrvues,
                idUserPrincipal: $idUserPrincipal,
                idStatusArticle: $idStatusArticle,
                idCategorie: $idcategorie,
                lang: $lang
            );
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Ajout d'une nouvelle article sur la guerre en Iran.">
    <title>Document</title>
    <script src="/assets/js/tinymce/tinymce.min.js"></script>
</head>
<body>
    <div class="layout">
        <h1>Nouvelle Article</h1>
        <?php if ($errorMessage !== ''): ?>
			<div class="notice error"><?php echo escape($errorMessage); ?></div>
		<?php endif; ?>

		<?php if ($successMessage !== ''): ?>
			<div class="notice success"><?php echo escape($successMessage); ?></div>
		<?php endif; ?>
        <form action="nouvelle.php" id="myForm" method="post">
            <p><input type="text" name="titre"></p>
            <p><select name="categorie" id="categorie">
                <option value="">Selectionner la categorie</option>
                <?php foreach ($categories as $categorie): ?>
                    <option value="<?php echo $categorie->getIdCategorie(); ?>"><?php echo $categorie->getCategorie(); ?></option>
                <?php endforeach; ?>
            </select></p>
            <p>
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
            </p>
            <textarea id="myEditor" name="content">
            </textarea>
            <br>
            <!-- <button type="button" onclick="showHTML()">Voir le HTML</button> -->
			<button type="submit">Enregistrer</button>
        </form>
    </div>
    <h2>Code HTML généré :</h2>
    <pre id="htmlOutput" style="background:#f0f0f0;padding:10px;"></pre>

    <script>
    // Initialisation de TinyMCE
    tinymce.init({
        selector: '#myEditor',
        plugins: 'lists link image',
        toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link image',
        height: 300,
        license_key: 'gpl'
    });

    // Fonction pour récupérer et afficher le HTML
    function showHTML() {
        // tinymce.get('myEditor').getContent() récupère le HTML complet
        const html = tinymce.get('myEditor').getContent();
        document.getElementById('htmlOutput').textContent = html;
    }
    </script>
</body>
</html>