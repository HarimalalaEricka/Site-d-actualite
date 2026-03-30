<?php

declare(strict_types=1);

use App\Controllers\Admin\ArticleController;
use App\Controllers\Admin\CategorieController;
use App\Controllers\Admin\CollaborationController;
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

$idArticle = isset($_GET['idArticle']) ? (int) $_GET['idArticle'] : 0;

if ($idArticle <= 0) {
	$_SESSION['flash_error'] = 'Article invalide.';
	header('Location: /Views/Admin/profile.php');
	exit;
}

$articleController = new ArticleController();
$article = $articleController->getArticleById($idArticle);

if ($article === null || $article->getIdUserPrincipal() !== $idUser) {
	$_SESSION['flash_error'] = 'Article introuvable ou acces refuse.';
	header('Location: /Views/Admin/profile.php');
	exit;
}

$categorieController = new CategorieController();
$categories = $categorieController->getAllCategories();
$userController = new UserController();
$usersExept = $userController->getAllUsersExept($idUser);

$collaborationController = new CollaborationController();
$currentCollabs = $collaborationController->getCollaborationsByArticle($idArticle);
$currentCollabIds = array_map(static fn ($collab) => $collab->getIdUser(), $currentCollabs);

$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$titre = trim((string) ($_POST['titre'] ?? ''));
	$content = trim((string) ($_POST['content'] ?? ''));
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
			$updatedArticle = new Article(
				$idArticle,
				$titre,
				$article->getDatePublication(),
				$content,
				$article->getNbrVues(),
				$idUser,
				$article->getIdStatusArticle(),
				$idcategorie,
				$lang
			);

			$isUpdated = $articleController->updateArticle($idArticle, $updatedArticle, $idUser);

			if (!$isUpdated) {
				throw new \RuntimeException('Impossible de mettre a jour l\'article.');
			}

			// Gestion des collaborations
			$allCollaboratorIds = array_values(array_unique(array_merge([$idUser], $selectedCollaborators)));

			// Supprimer les collaborations qui ne sont plus sélectionnées
			foreach ($currentCollabIds as $collabId) {
				if (!in_array($collabId, $allCollaboratorIds, true)) {
					$collaborationController->deleteCollaboration($collabId, $idArticle);
				}
			}

			// Ajouter les nouvelles collaborations
			foreach ($allCollaboratorIds as $collaboratorId) {
				if (!in_array($collaboratorId, $currentCollabIds, true)) {
					$collab = new Collaboration($collaboratorId, $idArticle);
					$collaborationController->createCollaboration($collab);
				}
			}

			$_SESSION['flash_success'] = 'Article modifie avec succes.';
			header('Location: /Views/Admin/profile.php');
			exit;
		} catch (Throwable $e) {
			$errorMessage = 'Erreur lors de la modifcation de l\'article: ' . $e->getMessage();
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
	<title>Editer Article</title>
	<script src="/assets/js/tinymce/tinymce.min.js"></script>
	<script>
		// Initialisation de TinyMCE
		tinymce.init({
			selector: '#content',
			plugins: 'lists link image',
			toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link image',
			height: 300,
			license_key: 'gpl'
		});
	</script>
</head>
<body>
	<h1>Editer Article</h1>

	<?php if ($errorMessage): ?>
		<div style="color: red; margin: 10px 0;">
			<?php echo escape($errorMessage); ?>
		</div>
	<?php endif; ?>

	<?php if ($successMessage): ?>
		<div style="color: green; margin: 10px 0;">
			<?php echo escape($successMessage); ?>
		</div>
	<?php endif; ?>

	<form method="POST">
		<div>
			<label for="titre">Titre:</label>
			<input type="text" id="titre" name="titre" value="<?php echo escape($article->getTitre()); ?>" required>
		</div>

		<div>
			<label for="categorie">Categorie:</label>
			<select id="categorie" name="categorie" required>
				<option value="">Selectionner une categorie</option>
				<?php foreach ($categories as $cat): ?>
					<option value="<?php echo (int) $cat->getIdCategorie(); ?>"
						<?php echo ($cat->getIdCategorie() === $article->getIdCategorie()) ? 'selected' : ''; ?>>
						<?php echo escape((string) $cat->getCategorie()); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="collaborators">Collaborateurs:</label>
			<select id="collaborators" name="collaborators[]" multiple>
				<?php foreach ($usersExept as $user): ?>
					<option value="<?php echo (int) $user->getIdUser(); ?>"
						<?php echo in_array($user->getIdUser(), $currentCollabIds, true) ? 'selected' : ''; ?>>
						<?php echo escape((string) $user->getNom()) . ' ' . escape((string) $user->getPrenom()); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="content">Contenu:</label>
			<textarea id="content" name="content" required><?php echo escape($article->getContenu()); ?></textarea>
		</div>

		<button type="submit">Mettre a jour</button>
		<a href="/Views/Admin/profile.php">Annuler</a>
	</form>
</body>
</html>
